<?php

require_once APP . 'mail/Mail.php';
require_once APP . 'mail/EmailTag.php';

class SendTicketPaymentEmail
{
    public function __invoke(
        $reference,
        $autorisation,
        $montant,
        $email
    )
    {
        $objet = "Ticket paiement";
        $msg = $this->buildContentEmail($reference, $autorisation, $montant, $email);
        $mail = new Mail();
        try {
            $mail->to($email)
                ->subject($objet)
                ->body($msg)
                ->send();
        } catch (\Exception $e) {
        }
    }

    /**
     * @param $reference
     * @param $autorisation
     * @param $montant
     * @param $email
     * @return string
     */
    private function buildContentEmail($reference, $autorisation, $montant, $email)
    {
        $today = date('d-m-y H:i:s');
        $msg = "
        <br><br>
        <div style='color:red;font-size:14px'>
        Important: ce mail est envoyé automatiquement. Surtout ne répondez pas à ce mail car votre message ne sera pas traité. 
        Si vous avez une question, rendez-vous dans notre rubrique <a href='https://www.khapeo.com/wp/psp/aide-et-contact-prostagespermis/' target='_blank'>Aide en cliquant ici</a>
        <br><br>
        </div>";
        $msg .= "REF COMMANDE: " . $reference;
        $msg .= "<br><br>";
        $msg .= "Carte bancaire";
        $msg .= "<br><br>";
        $msg .= "Date: " . $today;
        $msg .= "<br>";
        $msg .= "KHAPEO 13290 LES MILLES 0966892";
        $msg .= "<br><br>";
        $msg .= "M DEBIT @";
        $msg .= "<br>";
        $msg .= "AUTO: " . $autorisation;
        $msg .= "<br>";
        $msg .= "MONTANT = " . $montant . " EUR";
        $msg .= "<br><br>";
        $msg .= "TICKET A CONSERVER";
        $msg .= "<br><br>";
        $msg .= "Email client: " . $email;
        return $msg;
    }
}