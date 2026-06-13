<?php
/**
 * twelvy_env.php — SINGLE SOURCE OF TRUTH for HOST + DEBUG.
 * DEPLOY TO: /home/khapmait/psp-copie/twelvy_env.php
 *
 * Required FIRST (before params.php, whose defines are guarded by if(!defined)) by BOTH
 * twelvy_payment.php AND twelvy_validate.php — so the card form and the 3DS-return handler
 * can NEVER drift on DEBUG. A DEBUG split = the form runs in sandbox but validate charges
 * the REAL bank. Keep them identical here.
 *
 * 🔴 MIGRATION DAY: set DEBUG to false (real Paybox, real merchant, real charges) and HOST
 *    to the production payment domain — in THIS ONE FILE only.
 */
if (!defined('HOST'))  { define('HOST',  'https://psp-copie.twelvy.net'); }
if (!defined('DEBUG')) { define('DEBUG', true); } // true = sandbox (preprod bank, test creds). false = REAL money.
