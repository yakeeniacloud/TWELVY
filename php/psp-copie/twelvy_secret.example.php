<?php
/**
 * twelvy_secret.example.php — TEMPLATE.
 * DEPLOY (filled in) TO: /home/khapmait/psp-copie/twelvy_secret.php  (NOT web-served, NOT in git)
 *
 * TWELVY_HANDOFF_SECRET MUST equal the Vercel env BRIDGE_API_KEY (== OVH /www/api
 * config_secrets.php BRIDGE_SECRET_TOKEN). twelvy_payment.php and twelvy_validate.php
 * verify the HMAC handoff/confirmation tokens with it.
 */
define('TWELVY_HANDOFF_SECRET', 'REPLACE_WITH_THE_SAME_64_HEX_AS_BRIDGE_API_KEY');
