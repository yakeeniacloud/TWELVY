<?php

require_once APP . 'mail/EmailTag.php';

class EmailBuild
{
    public static function buildEmailCandidatFormationGestionDuTemps($firstName, $studentId, $amount)
    {
        $key = md5($studentId . '!psp13#');
        $key = substr($key, 0, 5);
        $url = HOST . "/es/login.php?id=$studentId&k=$key&upsell=formation_temps";

        return "
                <html>
                    <head>
                    <title>Votre inscription ". utf8_decode('à') ." la formation Gestion du Temps</title>
                    </head>
                    <body>
                        <p>Bonjour " . $firstName . ",</p>
                        ". utf8_decode("<p>Votre commande pour notre formation gestion du temps est confirmée (" . $amount . ' €' . "). Merci pour votre confiance !</p>") ."
                    </p>
                     <p>Vous pouvez accéder à la formation directement sur votre espace personnel en 
                       <a href='". $url ."'>cliquant ici !</a>
                    .</p>
                    
                    ". utf8_decode("<p>Si vous avez des questions, envoyez-moi un mail à contact@prostagespermis.fr, je fais tout pour vous répondre au plus vite.<p>") ."
                    <br>
                    " . EmailTag::footerCustomerSuccess() . "
                    </body>
                </html>";
    }

    public static function buildEmailCandidatFormationGestionEmotions($firstName, $studentId, $amount)
    {
        $key = md5($studentId . '!psp13#');
        $key = substr($key, 0, 5);
        $url = HOST . "/es/login.php?id=$studentId&k=$key&upsell=formation_emotion";

        return "
                <html>
                    <head>
                    ". utf8_decode("<title>Votre inscription à la formation Gestion des émotions 12 points</title>") ."
                    </head>
                    <body>
                        <p>Bonjour " . $firstName . ",</p>
                        ". utf8_decode("<p>Votre commande pour notre formation gestion des émotions 12 points est confirmée (" . $amount . ' €' . "). Merci pour votre confiance !</p>") ."
                    </p>
                     ". utf8_decode("<p>Vous pouvez accéder à la formation directement sur votre espace personnel en ") ."
                       <a href='". $url ."'>cliquant ici !</a>
                    .</p>
                    
                    <p>Si vous avez des questions, envoyez-moi un mail à contact@prostagespermis.fr, je fais tout pour vous répondre au plus vite.<p>
                    <br>
                    " . EmailTag::footerCustomerSuccess() . "
                    </body>
                </html>";
    }

    public static function buildEmailCandidatAlertPoint($firstName, $studentId, $amount)
    {
        $key = md5($studentId . '!psp13#');
        $key = substr($key, 0, 5);
        $url = HOST . "/es/login.php?id=$studentId&k=$key&upsell=order_bump";

        return "
                <html>
                    <head>
                    <title>Votre inscription au service Alerte points</title>
                    </head>
                    <body>
                        <p>Bonjour " . $firstName . ",</p>
                        ". utf8_decode("<p>Votre inscription au service Alerte aux points est confirmée (" . $amount . ' €' . "). Merci pour votre confiance !</p>") ."
                    </p>
                     ". utf8_decode("<p>Vous pouvez accéder au service directement sur votre espace personnel en") ." 
                       <a href='". $url ."'>cliquant ici !</a>
                    .</p>
                    
                    <p>Si vous avez des questions, envoyez-moi un mail à contact@prostagespermis.fr, je fais tout pour vous répondre au plus vite.<p>
                    <br>
                    " . EmailTag::footerCustomerSuccess() . "
                    </body>
                </html>";
    }

    public static function buildEmailCandidatAbonnementTwelvy($firstName, $studentId, $amount)
    {

        $key = md5($studentId . '!psp13#');
        $key = substr($key, 0, 5);
        $url = HOST . "/es/login.php?id=$studentId&k=$key&upsell=apply_twelvy";

        return "
                <html>
                    <head>
                    ". utf8_decode("<title>Votre abonnement à l'application Twelvy</title>") ."
                    </head>
                    <body>
                        <p>Bonjour " . $firstName . ",</p>
                        ". utf8_decode("<p>Votre commande pour notre application Twelvy est confirmée (" . $amount . ' €' . "). Merci pour votre confiance !</p>") ."
                    ". utf8_decode("<p>Vous pouvez télécharger l'application directement sur votre espace personnel en") ." 
                       <a href='". $url ."'>cliquant ici !</a>
                    .</p>
                    
                    ". utf8_decode("<p>Si vous avez des questions, envoyez-moi un mail à contact@prostagespermis.fr, je fais tout pour vous répondre au plus vite.<p>") ."
                    <br>
                    " . EmailTag::footerCustomerSuccess() . "
                    </body>
                </html>";
    }

}