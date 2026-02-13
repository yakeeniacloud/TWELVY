<?php

class SendPaymentSuccessEmail
{

    public function __invoke(
        $studentId,
        $memberId
    ) {
        //if (intval($studentId) >= 322135) {
            require_once ROOT . '/mails_v3/mail_inscription.php';
            mail_inscription($studentId);
            if ($memberId != 837) {
                require_once ROOT . '/mails_v3/mail_inscription_centre.php';
                mail_inscription_centre($studentId);
            }
        //}
    }
}

