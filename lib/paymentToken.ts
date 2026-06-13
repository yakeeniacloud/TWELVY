import crypto from 'crypto'

/**
 * paymentToken — server-only HMAC helpers for the Option-B payment handoff.
 *
 * SECURITY MODEL
 * - Anonymous funnel → we sign short-lived capability tokens (replaces the legacy
 *   public `md5(id + '!psp#13')`).
 * - Root secret = the shared value on both sides: Vercel env BRIDGE_API_KEY ===
 *   OVH config_secrets BRIDGE_SECRET_TOKEN. NEVER sent to the browser (these helpers
 *   are imported only by server route handlers).
 * - PURPOSE-SEPARATED SUBKEYS: we never sign with the raw bridge credential. The
 *   handoff and confirmation tokens each use a labelled subkey derived from the root
 *   secret, so leaking one capability token's verifier is not the bearer/X-Api-Key.
 *   PHP derives the same subkeys: hash_hmac('sha256', '<label>', $secret).
 * - Both tokens carry their own `exp`; verification is timing-safe.
 */

const HANDOFF_TTL_SECONDS = 15 * 60       // payment must START within 15 min
export const CONF_TTL_SECONDS = 2 * 60 * 60 // status readable for 2h (poll + reload)

function hmacHex(message: string, key: string): string {
  return crypto.createHmac('sha256', key).update(message, 'utf8').digest('hex')
}
// Subkeys derived from the root secret (label-separated, versioned for rotation).
function handoffKey(secret: string): string { return hmacHex('twelvy-handoff-v1', secret) }
function confKey(secret: string): string { return hmacHex('twelvy-conf-v1', secret) }

export function safeEqualHex(a: string, b: string): boolean {
  const ba = Buffer.from(a || '', 'utf8')
  const bb = Buffer.from(b || '', 'utf8')
  if (ba.length !== bb.length) return false
  return crypto.timingSafeEqual(ba, bb)
}

/** Full signed redirect URL to the PSP-copie payment page (handoff token). */
export function buildPaymentRedirectUrl(
  baseUrl: string,
  stagiaireId: number,
  secret: string,
  nowSec: number = Math.floor(Date.now() / 1000)
): string {
  const exp = nowSec + HANDOFF_TTL_SECONDS
  const sig = hmacHex(`${stagiaireId}|${exp}`, handoffKey(secret))
  const u = new URL('/twelvy_payment.php', baseUrl)
  u.searchParams.set('s', String(stagiaireId))
  u.searchParams.set('exp', String(exp))
  u.searchParams.set('sig', sig)
  return u.toString()
}

/**
 * Verify the confirmation/status token. PSP's twelvy_validate.php GENERATES it
 * (hash_hmac over `<id>|conf|<exp>` with the conf subkey) and appends it to the
 * return URL as `&t=<sig>&te=<exp>`. We only verify here (in /api/payment/status).
 */
export function verifyConfToken(
  stagiaireId: number,
  sig: string,
  exp: string | number,
  secret: string,
  nowSec: number = Math.floor(Date.now() / 1000)
): boolean {
  const e = Number(exp)
  if (!Number.isInteger(e) || e < nowSec) return false
  return safeEqualHex(hmacHex(`${stagiaireId}|conf|${e}`, confKey(secret)), sig)
}
