<?php

class E_TransactionConfig
{
    const PBX_Url_TEST = 'https://preprod-tpeweb.e-transactions.fr/cgi/RemoteMPI.cgi';
    const PBX_IdMerchant_TEST = '222';

    const PBX_Url = 'https://tpeweb.paybox.com/cgi/RemoteMPI.cgi';
    const PBX_IdMerchant = '651027368';

    public static function getawayConfig()
    {
        $PBX_IdSession = microtime(true);
        $PBX_URLRetour = HOST . '/src/payment/validate/validate_payment.php?d2305_' . session_id();
        $PBX_URLHttpDirect = HOST . '/src/payment/validate/debug_transaction.php?d2305_' . session_id();

        if (DEBUG) {
            $PBX_Url = self::PBX_Url_TEST;
            $PBX_IdMerchant = self::PBX_IdMerchant_TEST;
        } else {
            $PBX_Url = self::PBX_Url;
            $PBX_IdMerchant = self::PBX_IdMerchant;
        }
        return [$PBX_IdMerchant, $PBX_IdSession, $PBX_URLRetour, $PBX_URLHttpDirect, $PBX_Url];
    }

    public static function getawayConfigTransfert()
    {
        $PBX_IdSession = microtime(true);
        $PBX_URLRetour = HOST . '/src/payment/validate/validate_transfert_payment.php?d2305_' . session_id();
        $PBX_URLHttpDirect = HOST . '/src/payment/validate/debug_transaction.php?d2305_' . session_id();

        if (DEBUG) {
            $PBX_Url = self::PBX_Url_TEST;
            $PBX_IdMerchant = self::PBX_IdMerchant_TEST;
        } else {
            $PBX_Url = self::PBX_Url;
            $PBX_IdMerchant = self::PBX_IdMerchant;
        }
        return [$PBX_IdMerchant, $PBX_IdSession, $PBX_URLRetour, $PBX_URLHttpDirect, $PBX_Url];
    }


    public static function getawayUpsellConfig()
    {
        $PBX_IdSession = microtime(true);
        $PBX_URLRetour = HOST . '/src/payment/validate/validate_upsell_payment.php?d2305_' . session_id();
        $PBX_URLHttpDirect = HOST . '/src/payment/validate/debug_transaction.php?d2305_' . session_id();

        if (DEBUG) {
            $PBX_Url = self::PBX_Url_TEST;
            $PBX_IdMerchant = self::PBX_IdMerchant_TEST;
        } else {
            $PBX_Url = self::PBX_Url;
            $PBX_IdMerchant = self::PBX_IdMerchant;
        }
        return [$PBX_IdMerchant, $PBX_IdSession, $PBX_URLRetour, $PBX_URLHttpDirect, $PBX_Url];
    }

    public static function getawayUpsellOneProductConfig()
    {
        $PBX_IdSession = microtime(true);
        $PBX_URLRetour = HOST . '/src/payment/validate/validate_product_upsell_payment.php?d2305_' . session_id();
        $PBX_URLHttpDirect = HOST . '/src/payment/validate/debug_transaction.php?d2305_' . session_id();

        if (DEBUG) {
            $PBX_Url = self::PBX_Url_TEST;
            $PBX_IdMerchant = self::PBX_IdMerchant_TEST;
        } else {
            $PBX_Url = self::PBX_Url;
            $PBX_IdMerchant = self::PBX_IdMerchant;
        }
        return [$PBX_IdMerchant, $PBX_IdSession, $PBX_URLRetour, $PBX_URLHttpDirect, $PBX_Url];
    }
}
