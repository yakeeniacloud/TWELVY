<?php

function transfert($reference, $old_stage, $new_stage, $prix, $webservice=true)
{
    require_once("/home/prostage/connections/config.php");
    require_once("/home/prostage/connections/stageconnect.php");
    require_once "/home/prostage/www/modules/module.php";
    require_once "/home/prostage/www/params.php";
    require_once "/home/prostage/www/debug.php";
    //require_once "../display_error.php";
    require_once APP . "logging/LogCommission.php";
    require_once APP . 'transfert/services/RetrieveStageTransfert.php';
    require_once APP . 'transfert/services/RetrieveStudentTransfert.php';
    require_once APP . 'transfert/services/UpdateTransfertData.php';
    require_once APP . 'payment/services/RetrieveTransactionById.php';
    require_once ROOT . "/mails_v3/mail_transfert_stagiaire.php";
    require_once ROOT . "/mails_v3/mail_transfert_centre.php";
    require_once APP . "stage/services/UpdateStageAfterTransfert.php";
    require_once ROOT . "/mails_v3/mail_inscription.php";
    require_once ROOT . "/mails_v3/mail_inscription_centre.php";
   
    if($webservice == false){
        $log = new \Core\Log();
        $logCommission = new LogCommission();
    }
    $today = date('Y-m-d');
    $error = 0;
    $msg = "";

    //recuperation donnnées du nouveau stage
    $newStage = (new RetrieveStageTransfert())->__invoke($new_stage, $mysqli);
    $transaction = (new RetrieveTransactionById())->__invoke($reference, $mysqli);
    
    $id_stagiaire = $transaction->id_stagiaire;
    $autorisation = $transaction->autorisation;

    //recuperation donnnées ancien stage et stagiaire
    $oldStageStudent = (new RetrieveStudentTransfert())->__invoke($id_stagiaire, $mysqli);
    
    if ((intval($old_stage) == intval($new_stage)) && (intval($oldStageStudent->supprime) == 0)) {
        $error = 1;
        $msg = "Vous êtes déjà inscrit à ce stage";
        return array($error, $msg);
    }
    
    if ($oldStageStudent->id_membre != $newStage->id_membre) {
        require_once ROOT . "/mails_v3/mail_annulation_centre2.php";
        if (
            !$oldStageStudent->attente &&
            $oldStageStudent->supprime == 0 &&
            intval($oldStageStudent->provenance_suppression) == 0
        ) {
            mail_annulation_centre($id_stagiaire, 'Transfert sur un autre stage');
        }
    }

    $lastPaiement       =   $oldStageStudent->paiement;
    $newStagePrice      =   $newStage->prix;
    $ajout_paiement     =   0;
    $price_transfer     =   0;
    $paiement = $oldStageStudent->paiement;
    
    (new UpdateTransfertData())->__invoke(
        $oldStageStudent,
        $newStage,
        $reference,
        $paiement,
        $price_transfer,
        $ajout_paiement,
        $mysqli
    );
   
    move_files($oldStageStudent->date1, $newStage->date1, $old_stage, $new_stage, $id_stagiaire);
    
    if ($oldStageStudent->id_membre != $newStage->id_membre) {
        mail_inscription($id_stagiaire, true);
        if ($newStage->id_membre != 837) {
            mail_inscription_centre($id_stagiaire);
        }
    } else {
        mail_transfert_stagiaire($id_stagiaire,$ajout_paiement); //le centre recoit une copie
        mail_transfert_centre($id_stagiaire, [
            'date1' => $oldStageStudent->date1,
            'date2' => $oldStageStudent->date2,
            'ville' => $oldStageStudent->ville
        ]);
    }
    
    (new UpdateStageAfterTransfert())->__invoke($old_stage, $new_stage, $mysqli);

    $type_interlocuteur = 1; //stagiaire
    $notifie = 0;
    $old_date_text = date("d-m-Y", strtotime($oldStageStudent->date1));
    $new_date_text = date("d-m-Y", strtotime($newStage->date1));
    $message = "Transfert de stage: ";
   /*
    $log->info('Transfert de stage', 'transfer', LOGS . DS . 'transfer.log', false,
        [
            'stagiaire' => $id_stagiaire,
            'Ancien Stage' => $old_stage,
            'Nouveau Stage' => $new_stage,
            'Prix Ancien Stage' => $oldStageStudent->paiement,
            'Date Ancien Stage' => $old_date_text,
            'Prix Nouveau Stage' => $paiement,
            'Date Nouveau Stage' => $new_date_text,
            'Price Transfert' => $price_transfer,
            'Ajout Paiement' => $ajout_paiement
        ]
    );
    $logCommission->loggingCommission($id_stagiaire, $new_stage, $newStage->partenariat, $newStage->commission_ht);
    */
    $message .= "Ancien: $old_stage - $old_date_text $oldStageStudent->code_postal $oldStageStudent->ville - Prix: $oldStageStudent->paiement € <br> => Nouveau: $new_stage - $new_date_text $newStage->code_postal $newStage->ville - Prix: $newStage->prix €";

    if ($price_transfer > 0) {
        $message .= " -> Price Transfert : $price_transfer €";
    }

    if (!DEBUG) {
        $logfile = "/home/prostage/www/logs/log_price_transfert.txt";
    } else {
        $logfile = ROOT . "/logs/log_price_transfert.txt";
    }

    $msg = date('d-m-Y H:i:s') . ' ' . $id_stagiaire . ' ' . $oldStageStudent->nom . ' ' . $oldStageStudent->prenom . ' ' . $oldStageStudent->email . ' : stage du ' . $old_date_text . ' a ' . $oldStageStudent->ville . ' a ' . $oldStageStudent->paiement . ' chez ' . $oldStageStudent->membre_nom . '  -> transfere le ' . date('d/m/Y') . ' sur stage du ' . $new_date_text . ' a ' . $newStage->ville . ' a ' . $newStage->prix . ' chez ' . $newStage->membre_nom . ' ' . $newStage->id_membre . ' -> Price Transfert = ' . $price_transfer;
    $tmpfile = file_get_contents($logfile);
    file_put_contents($logfile, $msg . "\n" . $tmpfile);

    send_notification($type_interlocuteur, $id_stagiaire, $notifie, $message);
}
function send_notification($type_interlocuteur, $id_interlocuteur, $notifie, $message)
{

    require_once("/home/prostage/common_bootstrap2/notifications.php");

    $type_destinataire = 1;
    $id = notification($type_interlocuteur, $id_interlocuteur, $type_destinataire, $notifie, $message);
    return $id;
}
function move_files($date_old, $date_new, $id_stage_old, $id_stage_new, $id_stagiaire)
{
    require_once("/home/prostage/www/stages/functions.php");

    $dossier_old = "/home/prostage/www/stages/mois/" . date('Ym', strtotime($date_old)) . "/" . $id_stage_old;
    $dossier_new = "/home/prostage/www/stages/mois/" . date('Ym', strtotime($date_new)) . "/" . $id_stage_new;

    if (!is_dir($dossier_new))
        mkdir($dossier_new, 0777, true);

    $documents = listDocumentsStagiaire($id_stage_old, $date_old, $id_stagiaire);

    foreach ($documents as $document) {
        $old = $dossier_old . "/" . $document;
        $new = $dossier_new . "/" . $document;

        rename($old, $new);
    }
}

?>