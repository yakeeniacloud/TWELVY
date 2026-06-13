<?php
/**
 * Twelvy psp-copie shim for PSP's modules/module.php.
 * DEPLOY TO: /home/khapmait/psp-copie/www/modules/module.php
 *
 * WHY: PSP's logging layer (\Core\Log) is bootstrapped by the original modules/module.php via
 * the Composer autoloader (vendor/) — none of which is deployed on psp-copie. Logging::__construct()
 * does `require_once ROOT.'/modules/module.php'; $this->log = new \Core\Log();` — so a missing file
 * (or missing class) is a FATAL on the payment-return critical path (every new Logging()/LogPayment()/
 * LogCommission()). That was one of the cascading silent fatals behind the infinite "Veuillez patienter…".
 *
 * Logging is non-critical (debug/audit only) and PSP wraps every log call in try/catch. So here we
 * provide a harmless no-op \Core\Log + the LOGS/DS constants. No Composer, no file writes, no SMTP,
 * no hang risk. In sandbox (DEBUG) the !DEBUG file-write branches in LogPayment/LogCommission are
 * skipped anyway.
 *
 * 🔴 MIGRATION DAY: to restore real PSP logging in prod, deploy the genuine modules/module.php +
 *    vendor/ + Core\Log (or keep this shim and accept logging is disabled on this copy).
 */
namespace {
    if (!defined('DS'))   { define('DS', DIRECTORY_SEPARATOR); }
    if (!defined('LOGS')) { define('LOGS', sys_get_temp_dir()); }
}
namespace Core {
    if (!class_exists('Core\\Log', false)) {
        class Log {
            public function info($a = null, $b = null, $c = null, $d = null, $e = null) { return null; }
            public function error($a = null, $b = null, $c = null, $d = null, $e = null) { return null; }
            public function warning($a = null, $b = null, $c = null, $d = null, $e = null) { return null; }
            public function debug($a = null, $b = null, $c = null, $d = null, $e = null) { return null; }
            public function __call($name, $args) { return null; }
        }
    }
}
