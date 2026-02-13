<?php
if (isset($_POST['action']) && !empty($_POST['action'])) {

    $action = $_POST['action'];

    switch ($action) {

        case 'none':
            $ret = array("1", "");
            echo json_encode(array('made' => $ret[0], 'error_msg' => utf8_encode($ret[1])));
            break;

        case 'messages':
            echo messages();
            break;

        case 'affiche_messages_centres':
            echo affiche_messages_centres();
            break;

        case 'affiche_stagiaires_bloques':
            affiche_stagiaires_bloques();
            break;

        case 'active_notification_message_centre':
            active_notification_message_centre();
            break;

        case 'delete_message_centre':
            $id = $_POST['id'];
            delete_message_centre($id);
            break;

        case 'get_message_centre':
            $id = $_POST['id'];
            get_message_centre($id);
            break;

        case 'blackliste_centre':
            $id_membre = $_POST['id_membre'];
            blackliste_centre($id_membre);
            break;

        case 'suppression_centre':
            $id_membre = $_POST['id_membre'];
            suppression_centre($id_membre);
            break;

        case 'lieux_stages':
            $id_membre = $_POST['id_membre'];
            lieux_stages($id_membre);
            break;

        case 'statistiques':
            $id_membre = $_POST['id_membre'];
            statistiques($id_membre);
            break;

        case 'affiche_factures_centre':
            $id_membre = $_POST['id_membre'];
            affiche_factures_centre($id_membre);
            break;

        case 'annulation_centre':
            $id_stagiaire = intval($_POST['id_stagiaire']);
            annulation_centre($id_stagiaire);
            break;

        case 'blocage_virement':
            $id_stagiaire = intval($_POST['id_stagiaire']);
            $motif_virement_bloque = $_POST['motif_virement_bloque'];

            $forceValue = (isset($_POST['force_value']) && strlen($_POST['force_value']) > 0) ? intval($_POST['force_value']) : NULL;
            $onlyRecordText = (isset($_POST['only_record_text'])) ? $_POST['only_record_text'] : false;
            $blocked_alltime = (isset($_POST['blocked_alltime'])) ? $_POST['blocked_alltime'] : false;

            blocage_virement($id_stagiaire, $motif_virement_bloque, $onlyRecordText, $forceValue, $blocked_alltime);
            break;

        case 'inscription_rppc':
            $id_stagiaire = intval($_POST['id_stagiaire']);
            inscription_rppc($id_stagiaire);
            break;

        case 'update_message_centre':
            $id = $_POST['id'];
            $objet = $_POST['objet'];
            update_message_centre($id, $objet);
            break;

        case 'add_message_centre':
            $objet = $_POST['objet'];
            add_message_centre($objet);
            break;

        case 'confirm_assujetissement_tva':
            $id_membre = $_POST['id_centre'];
            echo confirm_assujetissement_tva($id_membre);
            break;

        case 'change_commission':
            $id_membre = $_POST['id_centre'];
            $new_comm = $_POST['new_comm'];
            change_commission($id_membre, $new_comm);
            break;

        case 'change_reversement':
            $id_membre = $_POST['id_centre'];
            $new_reversement = $_POST['new_reversement'];
            change_reversement($id_membre, $new_reversement);
            break;

        case 'nouveau_modele_commission':
            $id_membre = $_POST['id_membre'];
            $status = $_POST['status'];
            nouveau_modele_commission($id_membre, $status);
            break;

        case 'commission_enregistrer':
            commission_enregistrer($_POST["id_membre"], $_POST["prix_vente_ht"], $_POST["montant_commission_ht"], $_POST["tranche_commission_ht"], $_POST["augmentation_commission_ht"], $_POST["reduction_commission_premium_ht"]);
            break;

        case 'change_comm_range':
            $id_membre = $_POST['id_centre'];
            $new_val = $_POST['new_val'];
            $type = $_POST['type'];
            change_comm_range($id_membre, $new_val, $type);
            break;

        case 'change_etat_nonlu':
            $id = $_POST['id'];
            change_etat_nonlu($id);
            break;

        case 'activation_salle':
            $id_site = $_POST['id_site'];
            activation_salle($id_site);
            break;

        case 'get_consignes_virement_centre':
            $id_membre = $_POST['id_centre'];
            get_consignes_virement_centre($id_membre);
            break;

        case 'get_commentaire_centre':
            $id_membre = $_POST['id_centre'];
            get_commentaire_centre($id_membre);
            break;

        case 'send_message':
            $type_interlocuteur = $_POST['type_interlocuteur'];
            $id_interlocuteur = $_POST['id_interlocuteur'];
            $type_destinataire = $_POST['type_destinataire'];
            $notifie = $_POST['notifie'];
            $message = $_POST['message'];
            $id_notification = send_notification($type_interlocuteur, $id_interlocuteur, $type_destinataire, $notifie, $message);

            if (isset($_POST['id_centre']) && !empty($_POST['id_centre']))
                update_notification_centre($id_notification, $_POST['id_centre']);

            echo $id_notification;
            break;

        case 'do_md5':
            $val = intval($_POST['val']);
            $md5 = md5($val . '!psp#13');
            echo $md5;
            break;

        case 'do_md5_mix':
            $val1 = intval($_POST['val1']);
            $val2 = intval($_POST['val2']);
            $md5 = md5($val1 . "psp1330#" . $val2);
            echo $md5;
            break;

        case 'remboursement':
            $reference = intval($_POST['reference']);
            $montant = $_POST['montant'];
            $numappel = $_POST['numappel'];
            $numtrans = $_POST['numtrans'];
            $type = $_POST['type'];
            remboursement($reference, $montant, $numappel, $numtrans, $type);
            break;

        case 'status_paiement':
            $reference = intval($_POST['reference']);
            $numappel = $_POST['numappel'];
            $numtrans = $_POST['numtrans'];
            $ret = status_paiement($reference, $numappel, $numtrans);
            echo json_encode($ret);
            break;

        case 'formulaire_paiement':
            $id_stage = $_POST['id_stage'];
            $id_stagiaire = $_POST['id_stagiaire'];
            affiche_formulaire_paiement($id_stage, $id_stagiaire);
            break;

        case 'mise_en_attente':
            $id_stagiaire = $_POST['id_stagiaire'];
            mise_en_attente($id_stagiaire);
            break;

        case 'infos_stagiaire':
            $id_stagiaire = $_POST['id_stagiaire'];
            infos_stagiaire($id_stagiaire);
            break;

        case 'infos_centre':
            $id_membre = $_POST['id_membre'];
            infos_centre($id_membre);
            break;

        case 'commission_centre':
            $id_membre = $_POST['id_membre'];
            commission_centre($id_membre);
            break;

        case 'note_interne':
            $id_membre = $_POST['id_membre'];
            $note = $_POST['note'];
            note_interne($id_membre, $note);
            break;

        case 'historic_stagiaire':
            $id_stagiaire = $_POST['id_stagiaire'];
            historic_stagiaire($id_stagiaire);
            break;

        case 'get_historic_centre':
            get_historic_centre($_POST);
            break;

        case 'save_consigne_virement_centre':
            save_consigne_virement_centre($_POST);
            break;

        case 'save_commentaire_centre':
            save_commentaire_centre($_POST);
            break;

        case 'update_partenariat':
            $data = $_POST;
            update_partenariat($data);
            break;

        case 'update_tmc':
            $data = $_POST;
            updateTauxMargeCommerciale($data);
            break;

        case 'paiement_cb':
            $reference = $_POST['reference'];
            $montant = $_POST['montant'];
            $cardNumber = $_POST['cardNumber'];
            $cardExpiry = $_POST['cardExpiry'];
            $cardCVC = $_POST['cardCVC'];
            $k = $_POST['k'];
            $old_stage = $_POST['old_stage'];
            $new_stage = $_POST['new_stage'];
            paiement_cb($reference, $montant, $cardNumber, $cardExpiry, $cardCVC, $k, $old_stage, $new_stage);
            break;

        case 'editable':
            $id = intval($_POST['pk']);
            $name = $_POST['name'];
            $value = addslashes(($_POST['value']));
            editable($id, $name, $value);
            break;

        case 'espace_telechargement_documents':
            $id_stagiaire = $_POST['id_stagiaire'];
            espace_telechargement_documents($id_stagiaire);
            break;

        case 'slide_formateurs':
            $data = $_POST['data'];
            slide_formateurs($data);
            break;

        case 'slide_dossier_stage':
            $data = $_POST['data'];
            slide_dossier_stage($data);
            break;

        case 'slide_status_stage':
            $data = $_POST['data'];
            slide_status_stage($data);
            break;

        case 'slide_other_actions':
            $data = $_POST['data'];
            slide_other_actions($data);
            break;

        case 'slide_modif_formateur':
            $data = $_POST['data'];
            slide_modif_formateur($data);
            break;

        case 'slide_modif_salle':
            $data = $_POST['data'];
            slide_modif_salle($data);
            break;

        case 'update_dossier_stage':
            $id = intval($_POST['id']);
            $val = intval($_POST['val']);
            update_dossier_stage($id, $val);
            break;

        case 'update_status_stage':
            $id = intval($_POST['id']);
            $val = intval($_POST['val']);
            $motif = intval($_POST['motif']);
            update_status_stage($id, $val, $motif);
            break;

        case 'set_gta':
            $id_stage = intval($_POST['id_stage']);
            $gta = $_POST['gta'];
            set_gta($id_stage, $gta);
            break;

        case 'delete_formateur':
            $id = intval($_POST['id']);
            delete_formateur($id);
            break;

        case 'delete_centre':
            $id = intval($_POST['id']);
            delete_centre($id);
            break;

        case 'update_formateur':
            $params = $_POST['params'];
            update_formateur($params);
            break;

        case 'update_salle':
            $params = $_POST['params'];
            update_salle($params);
            break;

        case 'select_ville_france_free_referente':
            $id_ville = intval($_POST['id_ville']);
            select_ville_france_free_referente($id_ville);
            break;

        case 'change_ville_france_free_referente':
            $id_site = intval($_POST['id_site']);
            $new_val = intval($_POST['new_val']);
            change_ville_france_free_referente($id_site, $new_val);
            break;

        case 'ajout_nouveau_formateur':
            $nom = $_POST['nom'];
            $prenom = $_POST['prenom'];
            $formation = $_POST['formation'];
            ajout_nouveau_formateur($nom, $prenom, $formation);
            break;

        case 'ajout_nouveau_centre':
            ajout_nouveau_centre($_POST);
            break;

        case 'ajout_nouveau_lieu':
            $nom = $_POST['nom'];
            $code_postal = $_POST['code_postal'];
            $ville = $_POST['ville'];
            ajout_nouveau_lieu($nom, $code_postal, $ville);
            break;

        case 'delete_salle':
            $id = intval($_POST['id']);
            delete_salle($id);
            break;

        case 'delete_notification':
            $id = intval($_POST['id']);
            delete_notification($id);
            break;
        case 'getTownByPostalCode':
            $code = $_POST['postalCode'];
            getTownByPostalCode($code);
            break;
        case 'addFreeTown':
            $townSelected = $_POST['townSelected'];
            addFreeTown($townSelected);
            break;
        case 'update_display_phone':
            updateDisplayPhone($_POST);
            break;
        case 'update_display_email':
            updateDisplayEmail($_POST);
            break;
        case 'set_remboursement_priority':
            $data = $_POST;
            set_remboursement_priority($data);
            break;
        case 'update_status_up2pay':
            $data = $_POST;
            update_status_up2pay($data);
            break;
        case 'create_virement_listing_stagiaires':
            $data = $_POST;
            create_virement_listing_stagiaires($data);
            break;
    }
}

function get_historic_centre($params)
{

    $id_centre = intval($params['id_centre']);

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $messages = array();
    $sql = "SELECT message, timestamp FROM notifications WHERE id_centre = '$id_centre' AND id_interlocuteur = '0' ORDER BY id DESC";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
    while ($row = mysql_fetch_assoc($rs))
        $messages[] = $row;

    $c = "<textarea id='message' name='message' style='width:100%;height:200px;padding:5px' placeholder='Nouveau message'></textarea>";
    $c .= "<p style='margin-top:20px;margin-bottom:5px;font-size:14px'>Historique des échanges</p>";
    $c .= "<div style='border-radius:5px;text-align:left;border:1px solid #ccc;width:100%;padding:5px;height:200px;max-height:200px;font-size:13px;color:grey;overflow-y: scroll;'>";

    foreach ($messages as $message) {

        if (intval($message['type_interlocuteur']) == 3)
            $c .= "<p style='font-weight:bold'>";
        else
            $c .= "<p>";
        $c .= date('d-m-Y H:i', strtotime($message['timestamp'])) . ": ";
        $c .= $message['message'];
        $c .= "</p>";
    }

    $c .= "</div>";

    echo $c;
}

function affiche_stagiaires_bloques()
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT
				stagiaire.id AS stagiaire_id,
				stagiaire.nom AS stagiaire_nom,
				stagiaire.prenom AS stagiaire_prenom,
				stagiaire.email AS stagiaire_email,
				stagiaire.paiement,
				stagiaire.comm_autoecole,
				stagiaire.provenance_site,
				stagiaire.date_inscription,
				stagiaire.reduction AS stagiaire_reduction,
				stagiaire.commission AS stagiaire_commission,
				stagiaire.motif_virement_bloque,
				stage.date1,
				site.code_postal AS site_code_postal,
				site.ville AS site_ville,
				membre.id AS membre_id,
				membre.nom AS membre_nom,
				membre.iban AS membre_iban,
				membre.bic AS membre_bic,
				membre.assujetti_tva AS membre_tva,
				membre.assujetti_tva_confirme AS membre_tva_confirme,
				membre.commision2 AS membre_commision2,
				membre.min_comm AS min_comm,
				membre.max_comm AS max_comm,
				membre.consignes_virement

			FROM
				stagiaire, stage, site, transaction, membre

			WHERE
				stagiaire.virement_bloque = 1 AND
				stagiaire.id_stage = stage.id AND
				stagiaire.supprime = 0 AND
				stagiaire.paiement > 0 AND
				stagiaire.numtrans != '' AND
				stage.id_site = site.id AND
				transaction.id_stagiaire = stagiaire.id AND
				transaction.paiement_interne = 1 AND
				transaction.id_membre = membre.id AND
				transaction.id_membre != 837 AND
				transaction.virement = 0 AND
				(DATE_ADD(stage.date1, INTERVAL 3 DAY) <= CURDATE())
			
			ORDER BY
				membre.id ASC";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $total = mysql_num_rows($rs);
    mysql_close($testconnect);

    $counter = 0;

    $iFilteredTotal = $total;
    $iTotal = $total;

    //Output
    $output = array(
        "sEcho" => intval($_GET['sEcho']),
        "iTotalRecords" => $iTotal,
        "iTotalDisplayRecords" => $iFilteredTotal,
        "aaData" => array()
    );

    while ($aRow = mysql_fetch_array($rs)) {

        $stagiaire_id = $aRow['stagiaire_id'];
        $paiement = $aRow['paiement'];
        $membre_id = $aRow['membre_id'];
        $membre_nom = $aRow['membre_nom'];
        $nom = $aRow['stagiaire_nom'];
        $prenom = $aRow['stagiaire_prenom'];
        $email = $aRow['stagiaire_email'];
        $min_comm = vide($aRow['min_comm']) ? "36.8" : $aRow['min_comm'];
        $max_comm = vide($aRow['max_comm']) ? "43" : $aRow['max_comm'];
        $date1 = $aRow['date1'];
        $ville = $aRow['site_code_postal'] . " " . $aRow['site_ville'];
        $membre_comm = $aRow['membre_commision2'];
        $motif_virement_bloque = is_null($aRow['motif_virement_bloque']) ? "" : $aRow['motif_virement_bloque'];

        $commission_ht = $paiement * ($membre_comm / 100);
        if ($membre_id == 793) {
            if ($paiement <= 155)
                $commission_ht = $paiement * 0.15;
            else
                $commission_ht = 30;
        }

        if ($commission_ht < $min_comm) $commission_ht = $min_comm;
        else if ($commission_ht > $max_comm) $commission_ht = $max_comm;

        $row['stagiaire_id'] = $aRow['stagiaire_id'];
        $row['identity'] = $nom . ' ' . $prenom;
        $row['email'] = $email;
        $row['city'] = $date1 . ' ' . $ville;
        $row['centre'] = $aRow['membre_nom'];
        $row['paiement'] = $aRow['paiement'];
        $row['commission'] = $commission_ht;
        $row['motif'] = $motif_virement_bloque;

        $output['aaData'][] = $row;
    }

    echo json_encode($output);
}

function vide($val)
{

    if (is_null($val) || empty($val) || $val == '' || strlen($val) == 0)
        return 1;
    else
        return 0;
}

function get_consignes_virement_centre($id_membre)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT consignes_virement FROM membre WHERE id = '$id_membre'";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $row = mysql_fetch_assoc($rs);
    mysql_close($stageconnect);
    $consigne = $row['consignes_virement'];

    $c = "<textarea id='consigne' name='consigne' style='width:100%;height:200px;padding:5px' placeholder='Nouvelle consigne de virement'>" . $consigne . "</textarea>";

    echo $c;
}


function get_commentaire_centre($id_membre)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT commentaire FROM membre WHERE id = '$id_membre'";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $row = mysql_fetch_assoc($rs);
    mysql_close($stageconnect);
    $commentaire = $row['commentaire'];

    $c = "<textarea id='commentaire' name='commentaire' style='width:100%;height:200px;padding:5px' placeholder='Nouveau commentaire'>" . $commentaire . "</textarea>";

    echo $c;
}

function change_commission($id_membre, $new_comm)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "UPDATE membre SET commision2 = \"$new_comm\" WHERE id = '$id_membre'";
    mysql_query($sql, $stageconnect) or die(mysql_error());

    mysql_close($stageconnect);
}

function change_reversement($id_membre, $new_reversement)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "UPDATE membre SET reversement = \"$new_reversement\" WHERE id = '$id_membre'";
    mysql_query($sql, $stageconnect) or die(mysql_error());

    mysql_close($stageconnect);
}

function nouveau_modele_commission($id_membre, $status)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "UPDATE membre SET nouveau_modele_commission = " . $status . " WHERE id = '$id_membre'";
    $logfile = "/home/prostage/www/logs/trace_nouveau_modele_commission.txt";
    $msg = $sql . "_" . date('d-m-Y H:i:s');
    $tmpfile = file_get_contents($logfile);
    file_put_contents($logfile, $msg . "\n" . $tmpfile);
    mysql_query($sql, $stageconnect) or die(mysql_error());

    mysql_close($stageconnect);
}

function commission_enregistrer($id_membre, $prix_vente_ht, $montant_commission_ht, $tranche_commission_ht, $augmentation_commission_ht, $reduction_commission_premium_ht)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "UPDATE membre SET prix_vente_ht = '" . $prix_vente_ht . "',montant_commission_ht = '" . $montant_commission_ht . "',tranche_commission_ht = '" . $tranche_commission_ht . "',augmentation_commission_ht = '" . $augmentation_commission_ht . "',reduction_commission_premium_ht = '" . $reduction_commission_premium_ht . "' WHERE id = " . $id_membre;
    $logfile = "/home/prostage/www/logs/trace_nouveau_modele_commission.txt";
    $msg = $sql . "_" . date('d-m-Y H:i:s');
    $tmpfile = file_get_contents($logfile);
    file_put_contents($logfile, $msg . "\n" . $tmpfile);
    mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
}

function annulation_centre($id_stagiaire)
{
    include("/home/prostage/connections/stageconnect.php");
    require_once("/home/prostage/connections/config.php");
    require_once "/home/prostage/www/params.php";
    require_once '/home/prostage/www/src/stage/services/SingleUpdateStagePlace.php';

    require_once APP . "notification/services/SendNotification.php";

    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "UPDATE stagiaire SET provenance_suppression = '1', supprime='1' WHERE id = '$id_stagiaire'";
    mysql_query($sql, $stageconnect) or die(mysql_error());

    $sql = "SELECT id_stage FROM stagiaire WHERE id = '$id_stagiaire'";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $row = mysql_fetch_assoc($rs);

    $idNotification = (new SendNotification())->execute(0, $id_stagiaire, 0, 0, "Annulation Centre");

    (new SingleUpdateStagePlace())->__invoke($row["id_stage"], $mysqli);
    mysql_close($stageconnect);
}

function blocage_virement($id_stagiaire, $motif_virement_bloque, $onlyRecordText = false,  $forceValeur = NULL, $blocked_alltime = false)
{
    include("/home/prostage/connections/stageconnect.php");
    require_once("/home/prostage/connections/config.php");
    require_once "/home/prostage/www/params.php";
    require_once APP . 'student/services/RetrieveStudentById.php';
    require_once APP . "notification/services/SendNotification.php";

    mysql_select_db($database_stageconnect, $stageconnect);

    $student    =   (new RetrieveStudentById())->__invoke($id_stagiaire, $mysqli);
    $now = date('Y-m-d H:i:s');

    $motif_virement_bloque = addslashes($motif_virement_bloque);

    $newValueBlocage = intval(($forceValeur == NULL) ? !$student->virement_bloque : $forceValeur);
    $blocked_alltime = filter_var($blocked_alltime, FILTER_VALIDATE_BOOLEAN);

    if ($onlyRecordText == 'false') {
        if ($newValueBlocage == 0) {
            $sql = "UPDATE stagiaire SET virement_bloque=0, virement_bloque_definitif=0, motif_virement_bloque=\"$motif_virement_bloque\", date_virement_debloque='$now' WHERE id = '$id_stagiaire'";
        } else {
            $sql = "UPDATE stagiaire SET virement_bloque=1, virement_bloque_definitif=0, motif_virement_bloque=\"$motif_virement_bloque\", date_virement_bloque='$now' WHERE id = '$id_stagiaire'";
        }

        if ($blocked_alltime) {
            $sql = "UPDATE stagiaire SET virement_bloque=1, virement_bloque_definitif=1, motif_virement_bloque=\"$motif_virement_bloque\", date_virement_bloque='$now' WHERE id = '$id_stagiaire'";

            $messageNotification = "Blocage Reversement Centre Définitif";
        } else {
            $messageNotification = ($newValueBlocage == 1) ? "Blocage Reversement Centre" : "Déblocage Reversement Centre";
        }
    } else {
        $sql = "UPDATE stagiaire SET motif_virement_bloque=\"$motif_virement_bloque\" WHERE id = '$id_stagiaire'";
        $messageNotification = "Motif Blocage/Déblocage Reversement Centre modifié";
    }

    mysql_query($sql, $stageconnect) or die(mysql_error());

    $idNotification = (new SendNotification())->execute(0, $id_stagiaire, 0, 0, $messageNotification);

    mysql_close($stageconnect);
}

function inscription_rppc($id_stagiaire)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT 
				transaction.id
			FROM 
				transaction, stagiaire
			WHERE
				stagiaire.id = transaction.id_stagiaire AND
				transaction.id_membre = 793 AND
				stagiaire.id = '$id_stagiaire'";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $row = mysql_fetch_array($rs);
    $reference = intval($row['id']);

    $ret = 0;
    if ($reference) {
        require("/home/prostage/soap/rppc/inscriptionRppc.php");
        $ret = inscriptionRppc($id_stagiaire, $reference);
    }

    mysql_close($stageconnect);

    echo $ret;
}

function change_comm_range($id_membre, $new_val, $type)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    if (intval($type) == 1)
        $sql = "UPDATE membre SET min_comm = \"$new_val\" WHERE id = '$id_membre'";
    else
        $sql = "UPDATE membre SET max_comm = \"$new_val\" WHERE id = '$id_membre'";

    echo $sql;

    mysql_query($sql, $stageconnect) or die(mysql_error());

    mysql_close($stageconnect);
}

function save_consigne_virement_centre($params)
{

    $id_centre = intval($params['id_centre']);

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $consigne = mysql_real_escape_string($params['consigne']);
    $sql = "UPDATE membre SET consignes_virement = \"$consigne\" WHERE id = '$id_centre'";
    mysql_query($sql, $stageconnect) or die(mysql_error());

    mysql_close($stageconnect);
}

function save_commentaire_centre($params)
{

    $id_centre = intval($params['id_centre']);
    $commentaire = $params['commentaire'];

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $commentaire = mysql_real_escape_string($commentaire);

    $sql = "UPDATE membre SET commentaire = \"$commentaire\" WHERE id = '$id_centre'";

    mysql_query($sql, $stageconnect) or die(mysql_error());

    mysql_close($stageconnect);
}

function select_ville_france_free_referente($id_ville)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT id, ville_code_postal, ville_nom FROM villes_france_free ORDER BY ville_code_postal ASC";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);

    $c = "<select id='sel' nom='sel'>";
    $c .= "<option value='NULL'>Non renseigné</option>";
    $c .= "<option value='65530'>Ville</option>";
    $c .= "<option value='65531'>Département</option>";
    while ($row = mysql_fetch_assoc($rs)) {

        $id = intval($row['id']);
        $ville_nom = $row['ville_nom'];
        $ville_cp = explode("-", $row['ville_code_postal']);

        $selected = intval($id_ville) == $id ? "selected" : "";
        $c .= "<option value='$id' " . $selected . ">" . $ville_cp[0] . " " . $ville_nom . "</option>";
    }
    $c .= "</select>";

    echo $c;
}

function change_ville_france_free_referente($id_site, $new_val)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "UPDATE site SET ville_france_free_referente = '$new_val' WHERE id = '$id_site'";
    echo $sql;
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
}

function update_salle($params)
{

    $id = $params[0];
    $nom = addslashes($params[1]);
    $adresse = addslashes($params[2]);
    $code_postal = $params[3];
    $ville = addslashes($params[4]);
    $tel = $params[5];
    $email = $params[6];
    $agrement = $params[7];
    $delais_annulation = addslashes($params[8]);
    $modalites_reglement = addslashes($params[9]);
    $couts = $params[10];

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "UPDATE 
				site 
			SET 
				nom = '$nom',
				adresse = '$adresse',
				code_postal = '$code_postal',
				ville = '$ville',
				tel = '$tel',
				email = '$email',
				agrement = '$agrement',
				delais_annulation = '$delais_annulation',
				modalites_reglement = '$modalites_reglement',
				prix_jour_ht = '$couts'
			WHERE 
				id='$id'";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
}

function ajout_nouveau_lieu($nom, $code_postal, $ville)
{

    $nom = addslashes($nom);
    $ville = addslashes($ville);
    $id_membre = 837;

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "INSERT INTO site (nom, code_postal, ville, id_membre) VALUES (\"$nom\", \"$code_postal\", \"$ville\", \"$id_membre\")";

    mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
}

function active_notification_message_centre()
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "UPDATE membre SET message_centre_lu = 0";
    mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
}

function add_message_centre($objet)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "INSERT INTO message_centre (objet) VALUES (\"$objet\")";

    mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
}

function ajout_nouveau_formateur($nom, $prenom, $formation)
{

    $nom = addslashes($nom);
    $prenom = addslashes($prenom);
    $id_membre = 837;

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "INSERT INTO formateur (nom, prenom, formation, id_membre) VALUES (\"$nom\", \"$prenom\", \"$formation\", \"$id_membre\")";

    mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
}

function ajout_nouveau_centre($params)
{

    require_once "/home/prostage/www/params.php";
    //require_once('functions.php');
    require_once APP . 'mail/Mail.php';

    $nom = ($params['nom']);
    $code_postal = ($params['code_postal']);
    $ville = ($params['ville']);
    $nom_contact = ($params['nom_contact']);
    $tel = ($params['tel']);
    $email = trim(($params['email']));
    $login = $email;
    $pass_md5 = generateRandomString(5);
    $date_inscription = date('Y-m-d');
    $tva = '';
    $validTva = 0;

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "INSERT INTO membre (nom, code_postal, ville, nom_contact, tel, email, login, pass_md5, date_inscription, tva, assujetti_tva) VALUES (\"$nom\", \"$code_postal\", \"$ville\", \"$nom_contact\", \"$tel\", \"$email\", \"$login\", \"$pass_md5\", \"$date_inscription\",  \"$tva\",  \"$validTva\")";

    mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);

    $subject = "Bienvenue chez ProStagesPermis !";

    $msg = "";
    $msg .= "<h3 style='color:#E95B61;margin-top:30px;margin-bottom:7px'>Bienvenue chez ProStagesPermis,</h3>";
    $msg .= "<p>Cher Partenaire,</p>";

    $msg .= "<p>Je vous souhaite la bienvenue chez ProStagesPermis ! </p>";

    $msg .= "<p style='margin-top:20px'>Votre <a href='https://www.prostagespermis.fr/partenaire'>Espace Centre</a> est cr&eacute;&eacute;. Pour mettre vos stages en ligne d&egrave;s maintenant, c'est tr&egrave;s simple ! </p>";

    $msg .= "<p style='margin-top:20px'>Il vous suffit de vous connecter &agrave; votre Espace et de suivre le guide dans la page Aide et Contact. Tout est expliqu&eacute; !</p>";

    $msg .= "<p style='margin-top:20px'>Pour vous connecter &agrave; votre Espace Centre <a href='https://www.prostagespermis.fr/partenaire' target='_blank'>cliquez ici.</a></p>";

    $msg .= "<p style='margin-top:20px'><u>Voici vos identifiants:</u><br>";
    $msg .= "Login: " . $login . "<br>";
    $msg .= "Mot de passe: " . $pass_md5 . "<br>";
    $msg .= "</p>";

    $msg .= "<p style='margin-top:20px'>Je suis ravie de vous compter d&eacute;sormais parmi nos partenaires !</p>";
    $msg .= "<p style='margin-top:20px'>Pour toutes demandes &agrave; venir, merci de nous adresser un mail &agrave; contact@prostagespermis.fr</p>";
    $msg .= "<p style='margin-top:20px'>Un num&eacute;ro de t&eacute;l&eacute;phone est également mis &agrave; votre disposition : 04 65 84 13 99</p>";

    $template_footer = "&Agrave; tr&egrave;s bient&ocirc;t
				<br><br>	
				<span style='font-style:italic;color:grey'>
					<hr>
					Julie,<br>
					Equipe partenariat<br>
					contact@prostagespermis.fr<br>
					<img src='https://www.prostagespermis.fr/mails_v3/img/logo.jpg'>
				</span>";

    $msg .= $template_footer;

    $msg = utf8_decode($msg);

    $mail = new Mail();
    try {
        $mail->to($email)
            //->bbc(['contact@prostagespermis.fr'])
            ->subject($subject)
            ->body($msg)
            ->send();
    } catch (\Exception $e) {
    }
}

function update_formateur($params)
{

    $id = $params[0];
    $nom = addslashes($params[1]);
    $prenom = addslashes($params[2]);
    $formation = $params[3];
    $gta = $params[4];
    $status_societe = $params[5];
    $tel = $params[6];
    $mobile = $params[7];
    $email = $params[8];
    $password = $params[9];
    $code_postal = $params[10];
    $ville = addslashes($params[11]);
    $divers = addslashes($params[12]);

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "UPDATE 
				formateur 
			SET 
				nom = '$nom',
				prenom = '$prenom',
				formation = '$formation',
				gta = '$gta',
				status_societe = '$status_societe',
				tel = '$tel',
				mobile = '$mobile',
				email = '$email',
				password = '$password',
				code_postal = '$code_postal',
				ville = '$ville',
				divers = '$divers'
			WHERE 
				id='$id'";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
}

function slide_modif_formateur($id)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "SELECT * FROM formateur WHERE id='$id'";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $row = mysql_fetch_assoc($rs);
    mysql_close($stageconnect);

    $id = $row['id'];
    $nom = $row['nom'];
    $prenom = $row['prenom'];
    $formation = $row['formation'];
    $gta = $row['gta'];
    $status_societe = intval($row['status_societe']);
    $mobile = $row['mobile'];
    $tel = $row['tel'];
    $email = $row['email'];
    $password = $row['password'];
    $code_postal = $row['code_postal'];
    $ville = $row['ville'];
    $commentaire = $row['divers'];

    $c = "<div class='row'>";
    $c .= "<div class='col-md-4'>";
    $c .= "<p><input type=\"text\" name=\"nom\" value=\"$nom\" placeholder=\"nom\"> <input type=\"text\" name=\"prenom\" placeholder=\"prenom\" value=\"$prenom\"></p>";
    $c .= "<p>
			<select name='metier'>
				<option value='bafm' " . ($formation == "bafm" ? "selected" : "") . ">bafm</option>
				<option value='psy' " . ($formation == "psy" ? "selected" : "") . ">psy</option>
			</select>
			 <select name='gta'>
				<option value='1' " . ($gta == 1 ? "selected" : "") . ">gta: oui</option>
				<option value='0' " . ($gta == 0 ? "selected" : "") . ">gta: non</option>
			</select>
		   </p>";

    $c .= "<p>
			<select name='status_societe'>
				<option value='0' " . ($status_societe == 0 ? "selected" : "") . ">auto-entrepreneur</option>
				<option value='1' " . ($status_societe == 1 ? "selected" : "") . ">société</option>
				<option value='2' " . ($status_societe == 2 ? "selected" : "") . ">portage</option>
			</select>			
		   </p>";
    $c .= "</div>";

    $c .= "<div class='col-md-4'>";
    $c .= "<p><input type=\"text\" name=\"mobile\" value=\"$mobile\" placeholder=\"mobile\"> <input type=\"text\" name=\"tel\" value=\"$tel\" placeholder=\"Tel fixe\"></p>";
    $c .= "<p><input type=\"text\" name=\"email\" value=\"$email\" placeholder=\"email\"> <input type=\"text\" name=\"password\" value=\"$password\" placeholder=\"mot de passe\"></p>";
    $c .= "<p><input style=\"width:30%;float:left\" type=\"text\" name=\"code_postal\" value=\"$code_postal\" placeholder=\"code postal\"> <input style=\"width:50%\" type=\"text\" name=\"ville\" value=\"$ville\" placeholder=\"ville\"></p>";
    $c .= "</div>";

    $c .= "<div class='col-md-4'>";
    $c .= "<p><textarea name=\"commentaire\" rows=\"10\" placeholder=\"commentaire ...\">" . $commentaire . "</textarea></p>";
    $c .= "</div>";

    $c .= "</div>";

    $c .= "<div class='row' style='text-align:center'>";
    $c .= "<button id='$id' type='button' class='valid_modif_formateur btn btn-sm btn-success'>Valider</button>";
    $c .= "</div>";

    echo $c;
}

function slide_modif_salle($id)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "SELECT * FROM site WHERE id='$id'";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $row = mysql_fetch_assoc($rs);
    mysql_close($stageconnect);

    $id = $row['id'];
    $nom = $row['nom'];
    $adresse = $row['adresse'];
    $code_postal = $row['code_postal'];
    $ville = $row['ville'];
    $agrement = $row['agrement'];
    $tel = $row['tel'];
    $email = $row['email'];
    $delais_annulation = $row['delais_annulation'];
    $modalites_reglement = $row['modalites_reglement'];
    $couts = $row['prix_jour_ht'];

    $c = "<div class='row'>";
    $c .= "<div class='col-md-4'>";
    $c .= "<p><input type=\"text\" name=\"nom\" value=\"$nom\" placeholder=\"nom\"> <input type=\"text\" name=\"adresse\" placeholder=\"adresse\" value=\"$adresse\"></p>";
    $c .= "<p><input style=\"width:30%;float:left\" type=\"text\" name=\"code_postal\" value=\"$code_postal\" placeholder=\"cp\"> <input style=\"width:50%\" type=\"text\" name=\"ville\" placeholder=\"ville\" value=\"$ville\"></p>";
    $c .= "</div>";

    $c .= "<div class='col-md-4'>";
    $c .= "<p><input type=\"text\" name=\"agrement\" value=\"$agrement\" placeholder=\"agrément\"></p>";
    $c .= "<p><input type=\"text\" name=\"tel\" value=\"$tel\" placeholder=\"tel\"> <input type=\"text\" name=\"email\" placeholder=\"email\" value=\"$email\"></p>";

    $c .= "</div>";

    $c .= "<div class='col-md-4'>";
    $c .= "<p><input type=\"text\" name=\"delais_annulation\" value=\"$delais_annulation\" placeholder=\"délais annulation\"> <input type=\"text\" name=\"modalites_reglement\" placeholder=\"modalités paiement\" value=\"$modalites_reglement\"></p>";
    $c .= "<p><input type=\"text\" name=\"couts\" value=\"$couts\" placeholder=\"couts\"></p>";
    $c .= "</div>";

    $c .= "</div>";

    $c .= "<div class='row' style='text-align:center'>";
    $c .= "<button id='$id' type='button' class='valid_modif_salle btn btn-sm btn-success'>Valider</button>";
    $c .= "</div>";

    echo $c;
}

function delete_formateur($id)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "DELETE FROM formateur WHERE id = '$id'";
    mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
}

function delete_centre($id)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "DELETE FROM membre WHERE id = '$id'";
    mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
}

function delete_message_centre($id)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "DELETE FROM message_centre WHERE id = '$id'";
    mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);

    $dossier = "/home/prostage/www/messages_centres";
    $file = $dossier . "/" . $id . ".pdf";
    unlink($file);
}

function delete_salle($id)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "DELETE FROM site WHERE id = '$id'";
    mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
}

function delete_notification($id)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "DELETE FROM notifications WHERE id = '$id'";
    mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
}

function update_status_stage($id, $val, $motif)
{

    include("/home/prostage/connections/stageconnect.php");
    include "../modules/module.php";

    $trainingApi = new \App\Actions\Api\TrainingApiAction();

    mysql_select_db($database_stageconnect, $stageconnect);

    if ($val == 0) { //annulé
        annulation_stagiaires($id, $motif);
        $sql = "UPDATE stage SET annule = 1, motif_annulation = '$motif' WHERE id = '$id'";
        mysql_query($sql, $stageconnect) or die(mysql_error());
    } else if ($val == 1) { //hors ligne
        $sql = "UPDATE stage SET annule = 2 WHERE id = '$id'";
        mysql_query($sql, $stageconnect) or die(mysql_error());
    } else if ($val == 2) { //complet
        $sql = "UPDATE stage SET nb_places_allouees = 0, annule = 0 WHERE id = '$id'";
        mysql_query($sql, $stageconnect) or die(mysql_error());
    } else if ($val == 3) { //en ligne
        $sql = "UPDATE stage SET annule = 0 WHERE id = '$id'";
        mysql_query($sql, $stageconnect) or die(mysql_error());
    }

    $trainingApi->updateDataStageApi($id);

    mysql_close($stageconnect);
}

function update_partenariat($data)
{
    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = 'UPDATE stage SET partenariat=' . $data['partenariat'] . ' WHERE date1>=CURRENT_DATE AND id_membre=' . $data['id_membre']; //.' AND id_membre=970'
    if (mysql_query($sql, $stageconnect)) {
        mysql_close($stageconnect);
        include('/home/prostage/www/src/mc24/update.php');
        MargeCommercialeSet('all_stages_current_date', $data['id_membre']);
    } else
        mysql_close($stageconnect);
}

function updateTauxMargeCommerciale($data)
{
    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = 'UPDATE membre SET taux_marge_commerciale=' . $data['mc'] . ' WHERE id=' . $data['id_membre'];
    if (mysql_query($sql, $stageconnect)) {
        mysql_close($stageconnect);
        include('/home/prostage/www/src/mc24/update.php');
        MargeCommercialeSet('all');
    } else
        mysql_close($stageconnect);
}

function annulation_stagiaires($id, $motif)
{

    require_once("/home/prostage/common_bootstrap2/common_liste_emails.php");
    require_once("/home/prostage/www/planificateur_tache/newsletter/functions.php"); //mailchimp
    require_once("/home/prostage/common_bootstrap2/common_formulaire.php"); //fonction sendEmail
    require_once("/home/prostage/common_bootstrap2/functions.php"); //fonction sendSms
    include "../modules/module.php";

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "SELECT 
				transaction.id AS transaction_id,
				stagiaire.id AS stagiaire_id,
				stagiaire.tel,
				stagiaire.mobile,
				stagiaire.email,
				stagiaire.prenom
			FROM 
				stagiaire, transaction
			WHERE 
				transaction.id_stagiaire = stagiaire.id AND
				stagiaire.id_stage = '$id' AND
				stagiaire.supprime = 0 AND
				stagiaire.paiement > 0";
    $rs = mysql_query($sql) or die(mysql_error());

    while ($row = mysql_fetch_assoc($rs)) {

        $id_transaction = $row['transaction_id'];
        $id_stagiaire = $row['stagiaire_id'];
        $tel = $row['tel'];
        $mobile = $row['mobile'];
        $email = $row['email'];
        $prenom = html_entity_decode($row['prenom'], ENT_NOQUOTES, "ISO-8859-1");

        $today = date('Y-m-d');

        $sql = "UPDATE stagiaire SET supprime=1, motif_annulation=\"$motif\", date_suppression=\"$today\" WHERE id='$id_stagiaire'";
        mysql_query($sql);

        $sql = "UPDATE stage SET nb_places_allouees = (nb_places_allouees +1) WHERE id = $id";
        mysql_query($sql);

        sendEmail($id_transaction, '', false, 0, 1, 0, true, $motif, false, false);

        $senderlabel = "IDStages";
        $msg = "Important! " . $prenom . ", votre inscription au stage de récupération de points n'a pas pu être maintenue. Plus de précisions dans votre boîte mail, IdStages";

        ob_start();
        sendSms($senderlabel, $msg, $tel, $mobile);
        ob_get_clean();

        mailchimp_unsubscribe_psp_stagiaire($email);
    }

    $trainingApi = new \App\Actions\Api\TrainingApiAction();
    $trainingApi->updateDataStageApi($id);
}

function set_gta($id_stage, $gta)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "UPDATE stage SET gta = '$gta' WHERE id='$id_stage'";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());

    mysql_close($stageconnect);
}

function slide_other_actions($id)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT date1 FROM stage WHERE id = '$id'";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
    $row = mysql_fetch_assoc($rs);
    $date1 = $row['date1'];

    $c = "";

    $c .= " <a href=\"ajax_recap_stagiaire.php?id=$id&date1=$date1\" class=\"tooltipClass\" title=\"Synthèse infos stagiaires\"><img class=\"testdiv\" width=\"20px\" style=\"margin:3px;\"
		src=\"images/recap.jpeg\"></a>";

    $c .= " <a href=\"feuille_emargement.php?id_stage=$id\" class=\"tooltipClass\" target=\"_blank\"
	title=\"Feuille d'émargement\"><img class=\"testdiv\" width=\"20px\" style=\"margin:3px;\"
	src=\"images/decoupes/icones/pdf.png\"></a>";

    $c .= " <a href=\"feuille_synthese.php?id_stage=$id\" class=\"tooltipClass\" target=\"_blank\"
	title=\"Feuille de synthese\"><img class=\"testdiv\" width=\"20px\" style=\"margin:3px;\"
	src=\"images/decoupes/icones/synthese.png\"></a>";

    $c .= " <a href=\"feuille_prefecture.php?id_stage=$id\" class=\"tooltipClass\" target=\"_blank\"
	title=\"Courrier préfecture\"><img class=\"testdiv\" width=\"20px\" style=\"margin:3px;\"
	src=\"images/decoupes/icones/pdf_pref.gif\"></a>";

    $c .= " <a href=\"ajax_feuille_pieces_manquantes.php?id=$id\" class=\"tooltipClass\" title=\"Télécharger fiche pieces manquantes\"><img class=\"testdiv\" width=\"20px\" style=\"margin:3px;\"
		src=\"images/copyobjects.png\"></a>";

    $c .= " <a href=\"ajax_charte_qualite.php?id=$id\" class=\"tooltipClass\" title=\"Télécharger fiche charte de qualité animateurs\"><img class=\"testdiv\" width=\"20px\" style=\"margin:3px;\"
		src=\"images/charte-qualite.png\"></a>";

    $c .= " <a href=\"ajax_zip_stage.php?id=$id\" class=\"tooltipClass\" title=\"Télécharger dossier documents\"><img class=\"testdiv\" width=\"20px\" style=\"margin:3px;\"
	src=\"images/download-icon.png\"></a>";

    $c .= "<a href=\"ajax_zip_stage_attestations.php?id=$id\" class=\"tooltipClass\" title=\"Télécharger dossier attestations\"><img class=\"testdiv\" width=\"20px\" style=\"margin:3px;\"
	src=\"images/download_bleu.png\"></a>";

    $c .= "<a href=\"#\" title=\"Envoyer un sms de recherche aux animateurs\"><img class=\"testdiv smsButton\" width=\"20px\" style=\"margin:3px;\" src=\"images/sms.png\" id=\"$id\"></a>";

    echo $c;
}

function slide_dossier_stage($id)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT dossier_stage FROM stage WHERE id='$id'";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
    $row = mysql_fetch_assoc($rs);
    $dossier_stage = intval($row['dossier_stage']);

    $c = "<select class='select_design' name='select_dossier_stage'>";
    $c .= "<option value='0' " . ($dossier_stage == 0 ? "selected" : "") . "></option>";
    $c .= "<option value='1' " . ($dossier_stage == 1 ? "selected" : "") . ">Lettre hôtel envoyée</option>";
    $c .= "<option value='2' " . ($dossier_stage == 2 ? "selected" : "") . ">Courrier Pref Envoyé</option>";
    $c .= "</select>";

    $c .= " <button id_stage='$id' type='button' class='valid_dossier_stage btn btn-sm btn-success'>VALIDER</button>";

    echo $c;
}

function slide_status_stage($id)
{

    require_once("includes/functions.php");

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT annule, date1, nb_places_allouees FROM stage WHERE id='$id'";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
    $row = mysql_fetch_assoc($rs);
    $annule = intval($row['annule']);
    $nb_places_allouees = intval($row['nb_places_allouees']);
    $date1 = $row['date1'];

    $index_status = getStageStatus($annule, $date1, $nb_places_allouees);

    $c = "<select class='select_design' name='select_status_stage'>";
    $c .= "<option value='0' " . ($index_status == 0 ? "selected" : "") . ">Annulé</option>";
    $c .= "<option value='1' " . ($index_status == 1 ? "selected" : "") . ">Hors ligne</option>";
    $c .= "<option value='2' " . ($index_status == 2 ? "selected" : "") . ">Complet</option>";
    $c .= "<option value='3' " . ($index_status == 3 ? "selected" : "") . ">En ligne</option>";
    $c .= "</select>";

    $c .= " <button id_stage='$id' type='button' class='valid_status_stage btn btn-sm btn-success'>VALIDER</button>";

    echo $c;
}

function update_dossier_stage($id, $val)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "UPDATE stage SET dossier_stage = '$val' WHERE id='$id'";
    mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
}

function slide_formateurs($id_stage)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    //bafm
    $sql = "SELECT id, nom, prenom FROM formateur WHERE formation = 'bafm' AND id_membre = 837 ORDER BY nom ASC";
    $rs_bafm = mysql_query($sql) or die(mysql_error());
    while ($row_bafm = mysql_fetch_assoc($rs_bafm))
        $bafms[] = $row_bafm;

    //psy
    $sql = "SELECT id, nom, prenom FROM formateur WHERE formation = 'psy' AND id_membre = 837 ORDER BY nom ASC";
    $rs_psy = mysql_query($sql) or die(mysql_error());
    while ($row_psy = mysql_fetch_assoc($rs_psy))
        $psys[] = $row_psy;

    $sql = "SELECT id_bafm, id_psy, gta FROM stage WHERE id = $id_stage";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $row = mysql_fetch_assoc($rs);
    $id_bafm = intval($row['id_bafm']);
    $id_psy = intval($row['id_psy']);
    $gta = $row['gta'];

    mysql_close($stageconnect);

    $c = "<select name='select_bafm' class='select_design'>";
    $c .= "<option value='0'" . ($id_bafm == 0 ? " selected " : "") . "></option>";
    $c .= "<option value='1'" . ($id_bafm == 1 ? " selected " : "") . ">En recherche</option>";
    foreach ($bafms as $bafm) {
        $id = intval($bafm['id']);
        $nom = $bafm['nom'];
        $prenom = $bafm['prenom'];
        if (empty($nom) && empty($prenom))
            continue;

        $selected = $id == $id_bafm ? "selected" : "";
        $c .= "<option value='$id' " . $selected . ">$nom $prenom</option>";
    }
    $c .= "</select>";

    $c .= " <select name='select_psy' class='select_design'>";
    $c .= "<option value='0'" . ($id_psy == 0 ? " selected " : "") . "></option>";
    $c .= "<option value='1'" . ($id_psy == 1 ? " selected " : "") . ">En recherche</option>";
    foreach ($psys as $psy) {
        $id = intval($psy['id']);
        $nom = $psy['nom'];
        $prenom = $psy['prenom'];
        if (empty($nom) && empty($prenom))
            continue;

        $selected = $id == $id_psy ? "selected" : "";
        $c .= "<option value='$id' " . $selected . ">$nom $prenom</option>";
    }
    $c .= "</select>";

    $c .= " <select name='select_gta' class='select_design'>";
    $c .= "<option value=''" . (empty($gta) ? " selected " : "") . ">GTA</option>";
    $c .= "<option value='bafm'" . ($gta == 'bafm' ? " selected " : "") . ">Bafm</option>";
    $c .= "<option value='psy'" . ($gta == 'psy' ? " selected " : "") . ">Psy</option>";
    $c .= "</select>";

    $c .= " <button id_stage='$id_stage' old_bafm='$id_bafm' old_psy='$id_psy' old_gta='$gta' type='button' class='valid_formateurs btn btn-sm btn-success'>VALIDER</button>";

    echo $c;
}

function editable($id, $name, $value)
{

    header('Content-Type: text/html; charset=ISO-8859-15');

    $split = explode("|", $name);
    $table = $split[0];
    $field = $split[1];

    if ($table == "stagiaire" && ($field == "commission" || $field == "commission_ht"))
        $value = str_replace(',', '.', $value);

    $infos = infos_stagiaire($id);

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    if ($value == "NULL") //pas de quotes pour une valeur à NULL
        $sql = "UPDATE " . $table . " SET " . $field . "=" . $value . " WHERE id = " . $id;
    else
        $sql = "UPDATE " . $table . " SET " . $field . "='" . $value . "' WHERE id = " . $id;
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());

    $sql = "SELECT T.id_membre FROM stagiaire as S INNER JOIN stage as T ON T.id=S.id_stage AND S.id=" . $id;
    $rs = mysql_query($sql, $stageconnect);
    if ($row = mysql_fetch_assoc($rs))
        $id_membre = $row['id_membre'];
    else
        $id_membre = 0;
    mysql_close($stageconnect);

    if ($table == "stagiaire" && $field == "supprime") {
        execute_flow_status($id, $infos['supprime'], $value);
    }

    if ($table == 'stagiaire') {
        switch ($id_membre) {
            case 793:
                require("/home/prostage/soap/rppc/update_stagiaire_rppc.php");
                update_stagiaire_rppc($id, $field, $value);
                break;
            case 1060:
                include("/home/prostage/www/ws/prod/fsp/to/inscription/edit.php");
                editInscription($id_membre, $id);
                break;
        }
    }
}

function execute_flow_status($idStagiaire, $oldValue, $newValue)
{
    include("/home/prostage/connections/stageconnect.php");
    require_once("/home/prostage/connections/config.php");
    require_once "../params.php";
    require_once "../debug.php";
    require_once "../display_error.php";
    require_once APP . 'stage/services/SingleUpdateStagePlace.php';
    require_once APP . 'student/services/RetrieveStudentById.php';

    $student    =   (new RetrieveStudentById())->__invoke($idStagiaire, $mysqli);
    (new SingleUpdateStagePlace())->__invoke($student->id_stage, $mysqli);

    $oldValueText = "";
    $oldValueText = ($oldValue == 0) ? "Inscrit" : $oldValueText;
    $oldValueText = ($oldValue == 1) ? "Annulé" : $oldValueText;
    $oldValueText = ($oldValue == 2) ? "En attente" : $oldValueText;

    $newValueText = "";
    $newValueText = ($newValue == 0) ? "Inscrit" : $newValueText;
    $newValueText = ($newValue == 1) ? "Annulé" : $newValueText;
    $newValueText = ($newValue == 2) ? "En attente" : $newValueText;

    send_notification(0, $idStagiaire, 0, 0, "Modification Statut (Interne) - " . $oldValueText . " => " . $newValueText);
}

function espace_telechargement_documents($id_stagiaire, $reload = false)
{

    require_once "/home/prostage/www/params.php";
    $time = time();
    $md5 = md5($id_stagiaire . '!psp#');
    $md5 = substr($md5, 0, 10);
    //$src = "https://www.prostagespermis.fr/stages/telechargement_documents.php?id=" . $id_stagiaire . "&k=" . $md5 . "&preventcache=" . $time;
    $src = HOST . "/stages/telechargement_documentsv2.php?id=" . $id_stagiaire . "&k=" . $md5 . "&preventcache=" . $time . "&toAdmin=aqsdf";

    $c = "";

    $c .= "<div style='overflow: hidden;'>
		<iframe scrolling='no' src='$src' style='border: 0px none; margin-left: 0px; height: 340px; margin-top: 0px; width: 100%;'>
		</iframe>
		</div>";

    /*
	if ($reload)
		echo $c;
	else
		return $c;
	*/

    return $c;
}


function historic_stagiaire($id)
{

    include("/home/prostage/connections/stageconnect.php");
    include "../debug.php";

    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "SELECT * FROM notifications WHERE id_interlocuteur = $id ORDER BY id DESC";
    $rs_conversation = mysql_query($sql, $stageconnect) or die(mysql_error());

    $sqlStudent = "SELECT * FROM stagiaire WHERE id=$id";
    $rsStagiaire = mysql_query($sqlStudent, $stageconnect) or die(mysql_error());
    $row_stagiaire = mysql_fetch_assoc($rsStagiaire);


    mysql_close($stageconnect);

    $historics = array();
    while ($row_conversation = mysql_fetch_assoc($rs_conversation)) {
        $historics[] = $row_conversation;
    }

    $arrInitialePrice = parseInitialPaymentBeforeTransfer($historics);

    $c = "<legend>Historique</legend>";

    foreach ($historics as $historic) {

        $c .= "<p>";

        if ($historic['type_interlocuteur'] == 0)
            $c .= "<button class=' btn-xs btn-info' style='width:80px;'>Prostages </button>";
        else if ($historic['type_interlocuteur'] == 1)
            $c .= "<button class=' btn-xs btn-danger' style='width:80px;'>Stagiaire </button>";
        else if ($historic['type_interlocuteur'] == 3)
            $c .= "<button class=' btn-xs btn-success' type='button' style='width:80px;'>Centre " . $historic['id_centre'] . "</button> ";

        $c .= "<span style='margin-right:10px'></span>";

        $c .= date('d-m-Y H:i', strtotime($historic['timestamp'])) . ": " . $historic['message'];
        $c .= "</p>";
    }

    /*if (!empty($arrInitialePrice)) {
        $c .= "<p>";
        $c .= "<button class=' btn-xs btn-danger' style='width:80px;'>Stagiaire </button>";
        $c .= "<span style='margin-right:10px'></span>";
        $c .= "Achat du stage: " . $arrInitialePrice[2] . " au Prix:" . $arrInitialePrice[1] . " €";
        $c .= "</p>";
    }*/

    if (isset($row_stagiaire['datetime_preinscription'])) {
        $dateI = date('d-m-Y H:i', strtotime($row_stagiaire['datetime_preinscription']));
    } else {
        $dateI = date('d-m-Y', strtotime($row_stagiaire['date_inscription']));
    }

    $c .= "<p>";
    $c .= "<button class=' btn-xs btn-danger' style='width:80px;'>Stagiaire </button>";
    $c .= "<span style='margin-right:10px'></span>";
    $c .= $dateI . " - Achat du stage: " . $row_stagiaire['id_stage'] . " au Prix:" . ($row_stagiaire['paiement']-$row_stagiaire['ajout_paiement']) . " €";
    $c .= "</p>";


    echo $c;
}

function isPdfPresentForMessageCentres($id_message)
{

    /*
	$liste = array();
	$path = "/home/prostage/www/messages_centres";

	if (is_dir($path)) {

		$matche = $id_message.'.pdf';
		$f = new FilesystemIterator($path, FilesystemIterator::KEY_AS_FILENAME);
		$r = new RegexIterator($f, $matche, RegexIterator::MATCH, RegexIterator::USE_KEY);

		foreach ($r as $t)
		{
			if (stripos($t->getFilename(), $matche))
				array_push($liste, $t->getFilename());
		}
	}

	return $liste;
	*/

    $liste = array();
    $search = "/home/prostage/www/messages_centres/" . $id_message . ".pdf";
    foreach (glob($search) as $file) {
        array_push($liste, basename($file));
    }
    return $liste;
}

function affiche_messages_centres()
{

    $dossier = "/home/prostage/www/messages_centres";
    $dossier_url = str_replace("/home/prostage/www", "https://www.prostagespermis.fr", $dossier);

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "SELECT * FROM message_centre ORDER BY id DESC";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);

    $messages = array();
    while ($row = mysql_fetch_assoc($rs)) {
        $messages[] = $row;
    }

    $c = "<table class='tableau_mesages_centres'>";
    $c .= "<th>Date</th><th>Objet</th><th colspan='3'>Actions</th>";

    foreach ($messages as $msg) {

        $c .= "<tr>";

        $id = $msg['id'];
        $objet = $msg['objet'];
        $date = date("d-m-Y H:i", strtotime($msg['date']));

        $c .= "<td>$date</td>";
        $c .= "<td>$objet</td>";

        $c .= "<td width='60px; text-align:left'>";
        $liste = isPdfPresentForMessageCentres($id);
        if (count($liste)) {
            $pdf = $liste[0];
            $url = $dossier_url . "/" . $pdf;
            $c .= "<a href='$url' download><img class='img-thumbnail img_preview' src='http://www.prostagespermis.fr/images/document_pdf_charge.png' width='40px'></a>
			<p>
			<button style='width:40px;margin:0px;padding:0px' onclick='deleteDocument(&apos;" . $pdf . "&apos;, &apos;" . $dossier . "&apos;)' type='button' class='btn btn-xs btn-danger'>X</button></p>
			";
        } else {
            $c .= "<form id='form_pdf' class='imageform' method='post' enctype='multipart/form-data' action='ajax_upload.php' onchange=\"uploadImg(this, 'pdf', '$id')\">
			<input style='display:none' type='file' name='pdf' id='pdf' />
			<a onclick=\"$('#pdf').click()\">
			<button type='button' class='btn btn-warning'><span class='glyphicon glyphicon-open'></span> Pdf</button>
			</a>
			</form>";
        }
        $c .= "</td>";

        $c .= "<td width='50px'>";
        $c .= "<i class='fa fa-edit fa-2x edit_message_centre' style='cursor:pointer' id_message='$id'></i>";
        $c .= "</td>";

        $c .= "<td width='50px; text-align:right'>";
        $c .= "<i class='fa fa-trash-o fa-2x delete_message_centre' style='cursor:pointer' id_message='$id'></i>";
        $c .= "</td>";

        $c .= "</tr>";
    }

    $c .= "</table>";

    return $c;
}

function infos_stagiaire($id)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT 
				stagiaire.id,
				stagiaire.id_stage,
				stagiaire.civilite,
				stagiaire.nom,
				stagiaire.prenom,
       			stagiaire.id_externe,
				stagiaire.jeune_fille,
				stagiaire.date_naissance,
				stagiaire.lieu_naissance,
				stagiaire.adresse,
				stagiaire.code_postal,
				stagiaire.ville,
				stagiaire.tel,
				stagiaire.mobile,
				stagiaire.email,
				stagiaire.cas,
				stagiaire.num_permis,
				stagiaire.lieu_permis,
				stagiaire.date_permis,
				stagiaire.etat_permis,
				stagiaire.commentaire,
				stagiaire.iban,
				stagiaire.bic,
				stagiaire.commission,
				stagiaire.commission_ht,
				stagiaire.partenariat,
       			stagiaire.profession,
       			stagiaire.paiement,
				stagiaire.prix_index_ttc,
				stagiaire.marge_commerciale,
				stagiaire.taux_marge_commerciale,
       			stagiaire.profession_type,	
                stagiaire.total_guarantee,
				transaction.virement,
				membre.id as membre_id,
				membre.nouveau_modele_commission,
                membre.nom member_nom,
                membre.id as member_id,
                membre.tel as member_tel,
                membre.email as member_email,
                stagiaire.supprime,
                stagiaire.numero_cb,
                stagiaire.provenance,
                stagiaire.provenance_site
			FROM
				stagiaire,transaction,stage,membre
			WHERE
				stagiaire.id = transaction.id_stagiaire AND
				stagiaire.id_stage=stage.id AND
				membre.id=stage.id_membre AND
				stagiaire.id = $id";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $row = mysql_fetch_assoc($rs);


    $label_non_renseigne = "<span style='color:red'>Non renseigné</span>";

    $identite = $civilite . " " . utf8_encode($row['nom']) . " " . utf8_encode($row['prenom']);
    $identite .= empty($row['jeune_fille']) ? "" : "(jeune fille: " . utf8_encode($row['prenom']) . ")";

    $id = $row['id'];
    $id_stage = $row['id_stage'];
    $email = $row['email'];
    $pass_md5 = $row['pass_md5'];
    $virement = $row['virement'];

    $sepa = "non";
    $date_virement = "";
    if (intval($virement)) {
        $sql = "SELECT virement.* FROM virement WHERE id = '$virement'";
        $rs2 = mysql_query($sql, $stageconnect) or die(mysql_error());
        $row2 = mysql_fetch_assoc($rs2);
        $sepa = $row2['sepa'];
        $date_virement = date("d-m-Y", strtotime($row2['date']));
    }

    $sql = "SELECT note FROM temoignage WHERE email LIKE '$email'";
    $rs_note = mysql_query($sql, $stageconnect) or die(mysql_error());
    $row_note = mysql_fetch_assoc($rs_note);
    $total_note = mysql_num_rows($rs_note);
    $note = "";
    if ($total_note) {
        $note = $row_note['note'];
    }

    $sqlMetaProfile = "SELECT meta_value FROM profile_participant WHERE idStagiaire = '$id' AND meta_key = 'cas_majeur'";
    $rs_metas = mysql_query($sqlMetaProfile, $stageconnect) or die(mysql_error());
    $row_metas = mysql_fetch_assoc($rs_metas);
    $total_metas = mysql_num_rows($rs_metas);

    $cas_majeur = 0;

    if ($total_metas) {
        $cas_majeur = ($row_metas['meta_value'] != NULL) ? $row_metas['meta_value'] : 0;
    }


    $ask_post_retract_commercial = 0;

    $sqlMetaProfile_ask_com = "SELECT meta_value FROM profile_participant WHERE idStagiaire = '$id' AND meta_key = 'ask_post_retract_commercial'";
    $rs_metas_ask_com = mysql_query($sqlMetaProfile_ask_com, $stageconnect) or die(mysql_error());
    $row_metas_ask_com = mysql_fetch_assoc($rs_metas_ask_com);
    $total_metas_ask_com = mysql_num_rows($rs_metas_ask_com);

    if ($total_metas_ask_com) {
        $ask_post_retract_commercial = ($row_metas_ask_com['meta_value'] != NULL) ? $row_metas_ask_com['meta_value'] : 0;
    }

    $admin_demande_rib = 0;

    $sqlMetaProfile__admin_demande_rib = "SELECT meta_value FROM profile_participant WHERE idStagiaire = '$id' AND meta_key = 'admin_demande_rib'";
    $rs_metas_admin_demande_rib = mysql_query($sqlMetaProfile__admin_demande_rib, $stageconnect) or die(mysql_error());
    $row_metas_admin_demande_rib = mysql_fetch_assoc($rs_metas_admin_demande_rib);
    $total_metas_admin_demande_rib = mysql_num_rows($rs_metas_admin_demande_rib);

    if ($total_metas_admin_demande_rib) {
        $admin_demande_rib = ($row_metas_admin_demande_rib['meta_value'] != NULL) ? $row_metas_admin_demande_rib['meta_value'] : 0;
    }


    $admin_demande_rib_response = 0;

    $sqlMetaProfile__admin_demande_rib_response = "SELECT meta_value FROM profile_participant WHERE idStagiaire = '$id' AND meta_key = 'admin_demande_rib_reponse'";
    $rs_metas_admin_demande_rib_response = mysql_query($sqlMetaProfile__admin_demande_rib_response, $stageconnect) or die(mysql_error());
    $row_metas_admin_demande_rib_response = mysql_fetch_assoc($rs_metas_admin_demande_rib_response);
    $total_metas_admin_demande_rib_response = mysql_num_rows($rs_metas_admin_demande_rib_response);

    if ($total_metas_admin_demande_rib_response) {
        $admin_demande_rib_response = ($row_metas_admin_demande_rib_response['meta_value'] != NULL) ? $row_metas_admin_demande_rib_response['meta_value'] : 0;
    }

    mysql_close($stageconnect);

    $tel = $row['tel'];
    $mobile = $row['mobile'];
    $email = empty($row['email']) ? $label_non_renseigne : $row['email'];
    $date_naissance = empty($row['date_naissance']) ? $label_non_renseigne : date('d-m-Y', strtotime($row['date_naissance']));
    $lieu_naissance = empty($row['lieu_naissance']) ? $label_non_renseigne : utf8_encode($row['lieu_naissance']);
    $naissance = $date_naissance . " à " . $lieu_naissance;

    $adresse = empty($row['adresse']) ? $label_non_renseigne : utf8_encode($row['adresse']);
    $code_postal = empty($row['code_postal']) ? $label_non_renseigne : $row['code_postal'];
    $ville = empty($row['ville']) ? $label_non_renseigne : utf8_encode($row['ville']);
    $adresse_complete = $adresse . " " . $code_postal . " " . $ville;
    $nouvelle_commission_ht = empty($row['commission_ht']) ? $label_non_renseigne : $row['commission_ht'];
    $commission_ht = empty($row['commission']) ? $label_non_renseigne : $row['commission'];
    $cas = empty($row['cas']) ? $label_non_renseigne : $row['cas'];
    $num_permis = empty($row['num_permis']) ? $label_non_renseigne : $row['num_permis'];
    $lieu_permis = empty($row['lieu_permis']) ? $label_non_renseigne : utf8_encode($row['lieu_permis']);

    if ($row['taux_marge_commerciale'] > 0) {
        $marge_commerciale_ht = number_format(round(($row['marge_commerciale'] / 1.2 * ((100 - $row['taux_marge_commerciale']) / 100)), 2), 2, '.', '');
    } else {
        $marge_commerciale_ht = number_format(round(($row['marge_commerciale'] / 1.2), 2), 2, '.', '');
    }

    $date_permis = '';
    if ($row['date_permis'] && count(explode('-', $row['date_permis'])) == 3) {
        $date_permis = empty($row['date_permis']) ? $label_non_renseigne : date('d-m-Y', strtotime($row['date_permis']));
    }

    switch (intval($row['etat_permis'])) {
        case 0:
            $etat_permis = $label_non_renseigne;
            break;
        case 1:
            $etat_permis = "VALIDE";
            break;
        case 2:
            $etat_permis = "RETENTION DE 72H";
            break;
        case 3:
            $etat_permis = "SUSPENSION";
            break;
        case 4:
            $etat_permis = "ANNULE OU INVALIDE";
            break;
        case 5:
            $etat_permis = "JAMAIS OBTENU";
            break;
        case 6:
            $etat_permis = "PERDU";
            break;
    }

    $commentaire = utf8_encode($row['commentaire']);

    $iban = str_replace(' ', '', $row['iban']);
    $bic = str_replace(' ', '', $row['bic']);

    $key = md5($id . '!psp13#');
    $key = substr($key, 0, 5);

    $c = "<legend>Infos</legend>";
    $c .= "<table width='49%' style='float:left'>";
    //$c .= "<tr><th>Id:</th><td>$id</td></tr>";

    //$c .= "<tr><th>Id Ext:</th><td>" . $row['id_externe'] . "</td></tr>";
    $c .= "<tr><th>Identifiants:</th><td>$id / $key</td></tr>";
    $c .= "<tr><th>Porteur:</th><td>". $row['numero_cb'] ."</td></tr>";
    $c .= "<tr><th>Provenance :</th><td>". parseProvenanceSite($row['provenance_site'],$row['provenance']) ."</td></tr>";
    
    $c .= "<tr><th>Nom:</th><td><a href='#' data-type='text' class='editable_text' data-pk='$id' data-name='stagiaire|nom'>" . ($row['nom']) . "</a></td></tr>";
    $c .= "<tr><th>Prénom:</th><td><a href='#' data-type='text' class='editable_text' data-pk='$id' data-name='stagiaire|prenom'>" . ($row['prenom']) . "</a></td></tr>";
    $c .= "<tr><th>Jeune fille:</th><td><a href='#' data-type='text' class='editable_text' data-pk='$id' data-name='stagiaire|jeune_fille'>" . ($row['jeune_fille']) . "</a></td></tr>";
    $c .= "<tr><th>Tél 1:</th><td><a href='#' data-type='text' class='editable_text' data-pk='$id' data-name='stagiaire|tel'>" . ($row['tel']) . "</a></td></tr>";
    $c .= "<tr><th>Tél 2:</th><td><a href='#' data-type='text' class='editable_text' data-pk='$id' data-name='stagiaire|mobile'>" . ($row['mobile']) . "</a></td></tr>";
    $c .= "<tr><th>Email:</th><td><a href='#' data-type='text' class='editable_text' data-pk='$id' data-name='stagiaire|email'>" . ($row['email']) . "</a></td></tr>";
    $c .= "<tr><th>Date naissance:</th><td><a href='#' data-type='text' class='editable_text' data-pk='$id' data-name='stagiaire|date_naissance'>" . $date_naissance . "</a></td></tr>";
    $c .= "<tr><th>Lieu naissance:</th><td><a href='#' data-type='text' class='editable_text' data-pk='$id' data-name='stagiaire|lieu_naissance'>" . ($row['lieu_naissance']) . "</a></td></tr>";

    $c .= "<tr><th>Adresse:</th><td><a href='#' data-type='text' class='editable_text' data-pk='$id' data-name='stagiaire|adresse'>" . ($row['adresse']) . "</a></td></tr>";
    $c .= "<tr><th>Code postal:</th><td><a href='#' data-type='text' class='editable_text' data-pk='$id' data-name='stagiaire|code_postal'>" . ($row['code_postal']) . "</a></td></tr>";
    $c .= "<tr><th>Ville:</th><td><a href='#' data-type='text' class='editable_text' data-pk='$id' data-name='stagiaire|ville'>" . ($row['ville']) . "</a></td></tr>";
    //$c .= "<tr><th>Situation professionnelle :</th><td><a href='#' data-type='text' class='editable_text' data-pk='$id' data-name='stagiaire|profession'>" . ($row['profession_type']) . "</a></td></tr>";
    //$c .= "<tr><th>Profession :</th><td><a href='#' data-type='text' class='editable_text' data-pk='$id' data-name='stagiaire|profession_type'>" . ($row['profession']) . "</a></td></tr>";


    if ($row['membre_id'] > 959 || $row['nouveau_modele_commission'] == 1) {
        $c .= "<tr><th>Commission HT:</th><td><a href='#' data-type='text' class='editable_text' data-pk='$id' data-name='stagiaire|commission_ht'>" . ($row['commission_ht']) . "</a></td></tr>";
        switch ($row['partenariat']) {
            case 1:
                $partenariat = 'Standard';
                break;
            case 2:
                $partenariat = 'Premium';
                break;
            default:
                $partenariat = "-";
                break;
        }
        $c .= "<tr><th>Partenariat:</th><td>" . $partenariat . "</td></tr>";
    } else
        $c .= "<tr><th>Commission HT:</th><td><a href='#' data-type='text' class='editable_text' data-pk='$id' data-name='stagiaire|commission'>" . number_format(($row['commission'] / 100), 2, '.', ' ') . "</a></td></tr>";

    $c .= "<tr><th>Marge commerciale HT</th><td>" . $marge_commerciale_ht . "</td></tr>";
    $c .= "<tr><th>Statut:</th><td><a href='#' data-name='stagiaire|supprime' id='supprime' data-type='select' data-pk='$id' data-value='" . $row['supprime'] . "'></a></td></tr>";

    if ($row['price_transfer'] && $row['price_transfer'] > 0) {
        $c .= "<tr><th>Price transfert:</th><td>" . $row['price_transfer'] . "</td></tr>";
    }

    $c .= "<tr><th>Garantie Sérénité: </th><td>" . (($row['total_guarantee'] == 0) ? 'NON' : ('OUI (<b>' . $row['total_guarantee'] . '€</b>)')) . "</td></tr>";

    // CAS FORCE MAJEURE
    $c .= "<tr><th>Cas Force Majeure :</th><td><a href='#' data-name='stagiaire|cas_majeur' id='cas_majeur' data-type='select' data-pk='$id' data-value='" . $cas_majeur . "'></a></td></tr>";

    $c .= "<tr><th>Cas de force majeure en cours d'analyse :</th><td><a href='#' data-name='stagiaire|ask_com' id='ask_com' data-type='select' data-pk='$id' data-value='" . $ask_post_retract_commercial . "'></a></td></tr>";

    // DEMANDE RIB
    $c .= "<tr><th>Demande RIB :</th><td><a href='#' data-name='stagiaire|admin_demande_rib' id='admin_demande_rib' data-type='select' data-pk='$id' data-value='" . $admin_demande_rib . "'></a></td></tr>";
    $c .= "<tr><th>Demande RIB : Transmis par le stagiaire :</th><td><a href='#' data-name='stagiaire|admin_demande_rib_response' id='admin_demande_rib_response' data-type='select' data-pk='$id' data-value='" . $admin_demande_rib_response . "'></a></td></tr>";

    $c .= "<tr><th>Iban:</th><td><a href='#' data-type='text' class='editable_text' data-pk='$id' data-name='stagiaire|iban'>" . str_replace(' ', '', $row['iban']) . "</a></td></tr>";
    $c .= "<tr><th>Bic:</th><td><a href='#' data-type='text' class='editable_text' data-pk='$id' data-name='stagiaire|bic'>" . str_replace(' ', '', $row['bic']) . "</a></td></tr>";


    $c .= "</table>";

    $c .= "<table width='49%' style='float:right'>";
    $c .= "<tr><th>Id Stage:</th><td>$id_stage</td></tr>";

    // Centre Infos
    $c .= "<tr><th>Nom Centre :</th><td>" . $row['member_nom'] . "(" . $row['member_id'] . ")</td></tr>";
    $c .= "<tr><th>Tel Centre:</th><td>" . $row['member_tel'] . "</td></tr>";
    $c .= "<tr><th>Email Centre:</th><td>" . $row['member_email'] . "</td></tr>";

    $c .= "<tr><th>Cas:</th><td><a href='#' data-type='text' class='editable_text' data-pk='$id' data-name='stagiaire|cas'>" . ($row['cas']) . "</a></td></tr>";



    $c .= "<tr><th>Num permis:</th><td><a href='#' data-type='text' class='editable_text' data-pk='$id' data-name='stagiaire|num_permis'>" . ($row['num_permis']) . "</a></td></tr>";
    $c .= "<tr><th>Date délivrance:</th><td><a href='#' data-type='text' class='editable_text' data-pk='$id' data-name='stagiaire|date_permis'>" . $date_permis . "</a></td></tr>";
    $c .= "<tr><th>Pref délivrance:</th><td><a href='#' data-type='text' class='editable_text' data-pk='$id' data-name='stagiaire|lieu_permis'>" . ($row['lieu_permis']) . "</a></td></tr>";

    $c .= "<tr><th>Etat permis:</th><td><a href='#' data-name='stagiaire|etat_permis' id='etat_permis' data-type='select' data-pk='$id' data-value='" . $row['etat_permis'] . "'></a></td></tr>";

    $c .= "<tr><th>Reversement Centre :</th><td>" . $sepa . "</td></tr>";

    //$c .= "<tr><th>Note:</th><td>$note</td></tr>";

    $c .= "<tr><th>Commentaire:</th><td><a href='#' data-type='textarea' class='editable_text' data-pk='$id' data-name='stagiaire|commentaire'>" . utf8_decode($commentaire) . "</a></td></tr>";

    $c .= "<tr><th>Documents:</th><td>" . espace_telechargement_documents($id) . "</td></tr>";
    $c .= "</table>";

    echo $c;
}

function infos_centre($id)
{

    include("/home/prostage/connections/stageconnect.php");
    require_once "/home/prostage/www/params.php";

    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT * FROM membre WHERE id = '$id'";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $row = mysql_fetch_assoc($rs);

    $id = $row['id'];
    $nom = ($row['nom']);
    $adresse = ($row['adresse']);
    $code_postal = $row['code_postal'];
    $ville = ($row['ville']);
    $siret = $row['siret'];
    $iban = str_replace(' ', '', $row['iban']);
    $bic = str_replace(' ', '', $row['bic']);
    $login = $row['login'];
    $pass_md5 = $row['pass_md5'];
    $commision2 = $row['commision2'];
    $assujetti_tva = $row['assujetti_tva'];
    $assujetti_tva_confirme = $row['assujetti_tva_confirme'] || $row['assujetti_tva'] ? "OUI" : "NON";
    $isTvaValid = '';

    if ($assujetti_tva_confirme == 'OUI') {
        $isTvaValid = $row['is_valid_tva'] ? 'OUI' : 'NON';
    }

    // Ajout récup TVA
    $S_numTVA = $row['tva'];
    $date_inscription = date('d-m-Y', strtotime($row['date_inscription']));
    $nom_gerant = ($row['nom_gerant']);
    $prenom_gerant = ($row['prenom_gerant']);
    $tel = $row['tel'];
    $mobile = $row['mobile'];
    $email = $row['email'];
    $moreEmail = $row['more_email'];
    $nom_facturation = ($row['nom_facturation']);
    $prenom_facturation = ($row['prenom_facturation']);
    $tel_facturation = $row['tel_facturation'];
    $mobile_facturation = $row['mobile_facturation'];
    $email_facturation = $row['email_facturation'];
    $commentaire = $row['commentaire'];
    $note = $row['note'] ? $row['note'] : "A renseigner";
    $display_phone_stagiaire = $row['display_phone_student'];
    $display_email_stagiaire = $row['display_student_email'];
    $automatique_price = $row['automatique_price'];
    $marge_commerciale = $row['taux_marge_commerciale'];
    $partenariat = $row['partenariat'];
    switch ($partenariat) {
        case 0:
            $partenariat0 = 'selected';
            $Standard = '';
            $Premium = '';
            break;
        case 1:
            $partenariat0 = '';
            $Standard = 'selected';
            $Premium = '';
            break;
        case 2:
            $partenariat0 = '';
            $Standard = '';
            $Premium = 'selected';
            break;
    }

    switch ($display_phone_stagiaire) {
        case 1:
            $displayPhoneInscription = 'selected';
            break;
        case 2:
            $displayPhone1erJour = 'selected';
            break;
        case 3:
            $displayPhone_J1 = 'selected';
            break;
        case 4:
            $displayPhone_J2 = 'selected';
            break;
        case 5:
            $displayPhone_J3 = 'selected';
            break;
        case 6:
            $displayPhone_J4 = 'selected';
            break;
        case 7:
            $displayPhone_J5 = 'selected';
            break;
        case 8:
            $displayPhone_J6 = 'selected';
            break;
        case 9:
            $displayPhone_J7 = 'selected';
            break;
        case 10:
            $displayPhone_J8 = 'selected';
            break;
        case 11:
            $displayPhone_J9 = 'selected';
            break;
        case 12:
            $displayPhone_J10 = 'selected';
            break;
        default:
            $displayPhoneNo = 'selected';
            break;
    }
    /*
    if ($display_phone_stagiaire == 1) {
        $displayPhoneOk = 'selected';
        $displayPhoneNoOk = '';
    } else {
        $displayPhoneOk = '';
        $displayPhoneNoOk = 'selected';
    }*/

    switch ($display_email_stagiaire) {
        case 1:
            $displayEmailInscription = 'selected';
            break;
        case 2:
            $displayEmail1erJour = 'selected';
            break;
        case 3:
            $displayEmail_1 = 'selected';
            break;
        case 4:
            $displayEmail_2 = 'selected';
            break;
        case 5:
            $displayEmail_3 = 'selected';
            break;
        case 6:
            $displayEmail_4 = 'selected';
            break;
        case 7:
            $displayEmail_5 = 'selected';
            break;
        case 8:
            $displayEmail_6 = 'selected';
            break;
        case 9:
            $displayEmail_7 = 'selected';
            break;
        case 10:
            $displayEmail_8 = 'selected';
            break;
        case 11:
            $displayEmail_9 = 'selected';
            break;
        case 12:
            $displayEmail_10 = 'selected';
            break;
        default:
            $displayEmailNo = 'selected';
            break;
    }
    /*
    if ($display_email_stagiaire == 1) {
        $displayEmailOk = 'selected';
        $displayEmailNoOk = '';
    } else {
        $displayEmailNoOk = 'selected';
        $displayEmailOk = '';
    }*/

    $c = "<div class='col-md-6' style='padding-right:5px'>";
    $c .= "<legend>Infos</legend>";
    $c .= "<table width='100%'>";
    $c .= "<tr><th width='20%'>Id:</th><td>$id</td></tr>";
    $c .= "<tr><th>Nom:</th><td>$nom</td></tr>";
    $c .= "<tr><th>Adresse:</th><td>$adresse $code_postal $ville</td></tr>";
    $c .= "<tr><th>Siret:</th><td>$siret</td></tr>";
    $c .= "<tr><th>Iban / Bic:</th><td>$iban / $bic</td></tr>";
    $c .= "<tr><th>Identifiant / Mdp:</th><td>$login / $pass_md5</td></tr>";
    $c .= "<tr><th>Assujettissement TVA:</th><td>$assujetti_tva_confirme</td></tr>";

    // Ajout du numéro de TVA
    $c .= "<tr><th>N° TVA:</th><td>$S_numTVA</td></tr>";
    $c .= "<tr><th>Validité TVA:</th><td>$isTvaValid</td></tr>";

    $c .= "<tr><th>Date d'inscription:</th><td>$date_inscription</td></tr>";
    $c .= "<tr><th>Commentaire:</th><td>$commentaire</td></tr>";

    $c .= "<tr><th>API Login:</th><td>" . $row["api_login"] . "</td></tr>";
    $c .= "<tr><th>API Password:</th><td>" . $row["api_password"] . "</td></tr>";
    $c .= "<tr><th>API Accès:</th><td>" . (($row["api_access"] == 1) ? "Oui" : "Non") . "</td></tr>";
    $c .= "</table>";
    $c .= "</div>";

    $c .= "<legend>&nbsp</legend>";
    $c .= "<div class='col-md-6' style='padding-left:5px'>";
    $c .= "<table width='100%'>";
    $c .= "<tr><th width='20%'>Gérant:</th><td>$prenom_gerant $nom_gerant</td></tr>";
    $c .= "<tr><th>Tél:</th><td>$tel $mobile</td></tr>";
    $c .= "<tr><th>Email:</th><td>$email</td></tr>";
    $c .= "<tr><th>Email + :</th><td>" . $moreEmail . "</td></tr>";
    $c .= "<tr><th>Facturation:</th><td>$prenom_facturation $nom_facturation</td></tr>";
    $c .= "<tr><th>Tél:</th><td>$tel_facturation $mobile_facturation</td></tr>";
    $c .= "<tr><th>Email:</th><td>$email_facturation</td></tr>";
    $c .= "<tr><th>Note:</th><td><span style='pointer:cursor' class='note_interne' id_membre='$id' note='$note'>$note</span></td></tr>";

    $c .= "<tr><th>% Marge commerciale :</th><td>";
    $c .= "<select id='marge_commerciale' id_member='$id'>";
    for ($i = 0; $i <= 100; $i++) {
        if ($marge_commerciale == $i)
            $c .= "<option value='$i' selected>$i</option>";
        else
            $c .= "<option value='$i' >$i</option>";
    }
    $c .= "</td></tr>";

    $c .= "<tr><th>Partenariat :</th><td>";
    $c .= "<select id='select_partenariat' id_member='$id'><option value='1' $Standard>Standard</option><option value='2' $Premium>Premium</option></select>";
    $c .= "</td></tr>";

    $c .= "<tr><th>Afficher 'Téléphone Stagiaire' :</th><td>";
    $c .= "
        <select id='select_display_phone' id_member='$id'>
            <option value='0' $displayPhoneNo>Non</option>
            <option value='1' $displayPhoneInscription>Dès l'inscription</option>
            <option value='2' $displayPhone1erJour>A partir du 1er jour de stage</option>
            <option value='3' $displayPhone_J1>J-1 avant le stage</option>
            <option value='4' $displayPhone_J2>J-2 avant le stage</option>
            <option value='5' $displayPhone_J3>J-3 avant le stage</option>
            <option value='6' $displayPhone_J4>J-4 avant le stage</option>
            <option value='7' $displayPhone_J5>J-5 avant le stage</option>
            <option value='8' $displayPhone_J6>J-6 avant le stage</option>
            <option value='9' $displayPhone_J7>J-7 avant le stage</option>
            <option value='10' $displayPhone_J8>J-8 avant le stage</option>
            <option value='11' $displayPhone_J9>J-9 avant le stage</option>
            <option value='12' $displayPhone_J10>J-10 avant le stage</option>
        </select>";
    $c .= "</td></tr>";

    $c .= "<tr><th>Afficher 'Email Stagiaire' :</th><td>";
    $c .= "
        <select id='select_display_email' id_member='$id'>
            <option value='0' $displayEmailNo>Non</option>
            <option value='1' $displayEmailInscription>Dès l'inscription</option>
            <option value='2' $displayEmail1erJour>A partir du 1er jour de stage</option>
            <option value='3' $displayEmail_1>J-1 avant le stage</option>
            <option value='4' $displayEmail_2>J-2 avant le stage</option>
            <option value='5' $displayEmail_3>J-3 avant le stage</option>
            <option value='6' $displayEmail_4>J-4 avant le stage</option>
            <option value='7' $displayEmail_5>J-5 avant le stage</option>
            <option value='8' $displayEmail_6>J-6 avant le stage</option>
            <option value='9' $displayEmail_7>J-7 avant le stage</option>
            <option value='10' $displayEmail_8>J-8 avant le stage</option>
            <option value='11' $displayEmail_9>J-9 avant le stage</option>
            <option value='12' $displayEmail_10>J-10 avant le stage</option>
        </select>";
    $c .= "</td></tr>";

    $c .= "<tr><th>Espace Centre (Accès animateur stage en cours) :</th><td>";
    $c .= "<select id='display_stage_en_cours' id_member='$id'><option value='1' " . ($row['display_animator_current_stage'] == 1 ? 'selected' : '') . ">Oui</option><option value='0' " . ($row['display_animator_current_stage'] == 0 ? 'selected' : '') . ">Non</option></select>";
    $c .= "</td></tr>";

    $c .= "<tr><th>Espace Centre : Stages à pourvoir :</th><td>";
    $c .= "<select id='display_stage_pourvoir' id_member='$id'><option value='1' " . ($row['display_stage_pourvoir'] == 1 ? 'selected' : '') . ">Oui</option><option value='0' " . ($row['display_stage_pourvoir'] == 0 ? 'selected' : '') . ">Non</option></select>";
    $c .= "</td></tr>";

    $c .= "<tr><th>Espace Centre : Afficher stages alentours :</th><td>";
    $c .= "<select id='display_stage_alentour' id_member='$id'><option value='1' " . ($row['display_stage_alentour'] == 1 ? 'selected' : '') . ">Oui</option><option value='0' " . ($row['display_stage_alentour'] == 0 ? 'selected' : '') . ">Non</option></select>";
    $c .= "</td></tr>";

    $c .= "<tr><th>Espace Centre (Accès Ants):</th><td>";
    $c .= "<select id='dysplay_ants' id_member='$id'><option value='1' " . ($row['display_ants'] == 1 ? 'selected' : '') . ">Oui</option><option value='0' " . ($row['display_ants'] == 0 ? 'selected' : '') . ">Non</option></select>";
    $c .= "</td></tr>";

    /*$c .= "<tr><th>Espace Centre (Accès Ants 2):</th><td>";
    $c .= "<select id='dysplay_ants_2' id_member='$id'><option value='1' " . ($row['display_ants2'] == 1 ? 'selected' : '') . ">Oui</option><option value='0' " . ($row['display_ants2'] == 0 ? 'selected' : '') . ">Non</option></select>";
    $c .= "</td></tr>";*/

    $c .= "<tr><th>Espace Centre (Accès Bilans Annuels Stage):</th><td>";
    $c .= "<select id='dysplay_bilan' id_member='$id'><option value='1' " . ($row['display_bilan'] == 1 ? 'selected' : '') . ">Oui</option><option value='0' " . ($row['display_bilan'] == 0 ? 'selected' : '') . ">Non</option></select>";
    $c .= "</td></tr>";

    $c .= "<tr><th>Accès au changement du nombre maximum de stagiaires par stage : </th><td>";
    $c .= "<select id='can_change_nbmax_places' id_member='$id'><option value='1' " . ($row['can_change_nbmax_places'] == 1 ? 'selected' : '') . ">Oui</option><option value='0' " . ($row['can_change_nbmax_places'] == 0 ? 'selected' : '') . ">Non</option></select>";
    $c .= "</td></tr>";

    $c .= "<tr><th>Accès aux transferts stagiaires : </th><td>";
    $c .= "<select id='access_transfert_stagiaire' id_member='$id'><option value='1' " . ($row['access_transfert_stagiaire'] == 1 ? 'selected' : '') . ">Oui</option><option value='0' " . ($row['access_transfert_stagiaire'] == 0 ? 'selected' : '') . ">Non</option></select>";
    $c .= "</td></tr>";

    $c .= "<tr><th>Accès au changement de lieu de stage : </th><td>";
    $c .= "<select id='can_change_site_stage' id_member='$id'><option value='1' " . ($row['can_change_site_stage'] == 1 ? 'selected' : '') . ">Oui</option><option value='0' " . ($row['can_change_site_stage'] == 0 ? 'selected' : '') . ">Non</option></select>";
    $c .= "</td></tr>";

    $c .= "<tr><th>Gestion automatique des prix (Algo) : </th><td>";
    $c .= "<select id='automatique_price' id_member='$id'><option value='1' " . ($row['automatique_price'] == 1 ? 'selected' : '') . ">Oui</option><option value='0' " . ($row['automatique_price'] == 0 ? 'selected' : '') . ">Non</option></select>";
    $c .= "</td></tr>";

    if ($id == 289) {
        $c .= "<tr><th>Attribution Partenariat (API Externe) : </th><td>";
        $c .= "<select id='default_type_partenariat' id_member='$id'><option value='1' " . ($row['default_type_partenariat'] == 1 ? 'selected' : '') . ">Standard</option><option value='2' " . ($row['default_type_partenariat'] == 2 ? 'selected' : '') . ">Premium</option></select>";
        $c .= "</td></tr>";
    }

    $c .= "</table>";
    $c .= "</div>";

    $c .= "<div class='col-md-12' style='margin-top:20px'>";
    $c .= "<legend>Documents</legend>";
    $c .= "<table width='50%'><tr style='display: flex'>";

    $dossier = "/home/prostage/www/documents_centres";
    $dossier_url = HOST_FILE_CENTER;


    $liste_kbis = isKBIS($id);
    if (count($liste_kbis)) {
        $kbis = $liste_kbis[0];
        $url = $dossier_url . "/" . $kbis;
        $c .= "<td style='    display: flex;
    flex-direction: column;
    align-items: center;'>
			<a target='_blank' style='margin:10px' href='$url' download>
			<img class='img-thumbnail img_preview' src='http://www.prostagespermis.fr/images/document_pdf_charge.png' width='80px'>
			</a>
			<h5>Kbis</h5></td>";
    }

    $liste_cgv = isCGV($id);
    if (count($liste_cgv)) {
        $cgv = $liste_cgv[0];
        $url = $dossier_url . "/" . $cgv;
        $c .= "<td style='    display: flex;
    flex-direction: column;
    align-items: center;'>
			<a target='_blank' style='margin:10px' href='$url' download>
			<img class='img-thumbnail img_preview' src='http://www.prostagespermis.fr/images/document_pdf_charge.png' width='80px'>
			</a>
			<h5>CGV signées</h5></td>";
    }

    $listeUrssaf = isUrSsarf($id);
    if (count($listeUrssaf)) {
        $urssaf = $listeUrssaf[0];
        $url = $dossier_url . "/" . $urssaf;
        $c .= "<td style='    display: flex;
    flex-direction: column;
    align-items: center;'>
			<a target='_blank' style='margin:10px' href='$url' download>
			<img class='img-thumbnail img_preview' src='http://www.prostagespermis.fr/images/document_pdf_charge.png' width='80px'>
			</a>
			<h5>URSSAF</h5></td>";
    }

    $c .= "<td></td><td></td></tr></table>";
    $c .= "</div>";

    echo $c;
}

function commission_centre($id_membre)
{
    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT * FROM membre WHERE id = " . $id_membre;
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
    while ($row = mysql_fetch_array($rs)) {
        $prix_vente_ht = str_replace(',', '.', number_format(($row["prix_vente_ht"]), 2, ',', ' '));
        $prix_vente_ttc = str_replace(',', '.', number_format(($row['prix_vente_ht'] * 1.2), 2, ',', ' '));
        $montant_commission_ht = str_replace(',', '.', number_format(($row["montant_commission_ht"]), 2, ',', ' '));
        $montant_commission_ttc = str_replace(',', '.', number_format(($row['montant_commission_ht'] * 1.2), 2, ',', ' '));
        $tranche_commission_ht = str_replace(',', '.', number_format(($row["tranche_commission_ht"]), 2, ',', ' '));
        $augmentation_commission_ht = str_replace(',', '.', number_format(($row["augmentation_commission_ht"]), 2, ',', ' '));
        $reduction_commission_premium_ht = str_replace(',', '.', number_format(($row["reduction_commission_premium_ht"]), 2, ',', ' '));
    }
    $c = "<div class='col-md-12' style='padding:5px'>";
    $c .= "<legend>Commission Sans Exclusivité</legend>";
    $c .= "<table width='100%'>";

    $c .= "<tr>";
    $c .= "<th>Prix de vente TTC</th>";
    $c .= "<th>Prix de vente HT</th>";
    $c .= "<th>Montant commission TTC</th>";
    $c .= "<th>Montant commission Standard HT</th>";
    $c .= "<th>Tranche augmentation Prix de vente TTC</th>";
    $c .= "<th>Montant augmentation commission HT</th>";
    $c .= "<th>Réduction Commission Premium HT</th>";
    $c .= "<th></th>";
    $c .= "</tr>";

    $c .= "<tr>";
    $c .= "<td class='center'>$prix_vente_ttc</td>";
    $c .= "<td class='center'><input type='text' class='input_text' value='$prix_vente_ht' id='prix_vente_ht_" . $id_membre . "' name='prix_vente_ht_" . $id_membre . "' /></td>";
    $c .= "<td class='center'>$montant_commission_ttc</td>";
    $c .= "<td class='center'><input type='text' class='input_text' value='$montant_commission_ht' id='montant_commission_ht_" . $id_membre . "' name='montant_commission_ht_" . $id_membre . "' /></td>";
    $c .= "<td class='center'><input type='text' class='input_text' value='$tranche_commission_ht' id='tranche_commission_ht_" . $id_membre . "' name='tranche_commission_ht_" . $id_membre . "' /></td>";
    $c .= "<td class='center'><input type='text' class='input_text' value='$augmentation_commission_ht' id='augmentation_commission_ht_" . $id_membre . "' name='augmentation_commission_ht_" . $id_membre . "' /></td>";
    $c .= "<td class='center'><input type='text' class='input_text' value='$reduction_commission_premium_ht' id='reduction_commission_premium_ht_" . $id_membre . "' name='reduction_commission_premium_ht_" . $id_membre . "' /></td>";
    $c .= "<td class='center'><a href='#' class='btn btn-w-m btn-success' onClick='CommissionEnregistrer(" . $id_membre . ")'>Enregistrer</a></td>";
    $c .= "</tr>";

    $c .= "</table>";
    $c .= "</div>";

    echo $c;
}

function lieux_stages($id)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT 
				site.id,
				site.nom,
				site.adresse,
				site.code_postal,
				site.ville,
				site.commodites,
				site.agrement,
				site.actif
			FROM
				site
			WHERE
				site.id_membre = '$id'
			ORDER BY
				site.code_postal ASC";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $sites = array();
    while ($row = mysql_fetch_array($rs))
        $sites[] = $row;

    //stages en ligne à J+60
    $sql = "SELECT 
				site.id,
				COUNT(stage.id) AS nb_stages_ligne 
			FROM 
				stage, site
			WHERE
				stage.id_site = site.id AND
				stage.date1 > now() AND
				stage.date1 <= DATE_ADD(NOW(), INTERVAL +60 DAY) AND
				stage.annule = 0 AND
				stage.is_online = 1 AND
				stage.nb_places_allouees > 0 AND
				stage.id_membre = '$id'
			GROUP BY
				site.id";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $stages_lignes = array();
    while ($row = mysql_fetch_array($rs))
        $stages_lignes[] = $row;

    //prix min max
    $sql = "SELECT 
				site.id,
				MIN(stage.prix) AS min_prix,
				MAX(stage.prix) AS max_prix				
			FROM 
				stage, site
			WHERE
				stage.id_site = site.id AND
				stage.date1 > now() AND
				stage.date1 <= DATE_ADD(NOW(), INTERVAL +60 DAY) AND
				stage.annule = 0 AND
				stage.is_online = 1 AND
				stage.nb_places_allouees > 0 AND
				stage.id_membre = '$id'
			GROUP BY
				site.id";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $stages_prix = array();
    while ($row = mysql_fetch_array($rs))
        $stages_prix[] = $row;

    //stagiaires envoyés
    $sql = "SELECT 
				site.id,
				COUNT(archive_inscriptions.id) AS nb_stagiaires	
			FROM 
				archive_inscriptions, stage, site
			WHERE
				
				archive_inscriptions.id_membre = '$id' AND
				stage.id = archive_inscriptions.id_stage AND
				stage.id_site = site.id AND
				archive_inscriptions.date_inscription <= now() AND
				archive_inscriptions.date_inscription >= DATE_ADD(NOW(), INTERVAL -90 DAY)
			GROUP BY
				site.id";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $stagiaires_envoyes = array();
    while ($row = mysql_fetch_array($rs))
        $stagiaires_envoyes[] = $row;

    //annulations
    $sql = "SELECT 
				site.id,
				COUNT(annulations.id) AS nb_annulations	
			FROM 
				annulations, stage, site
			WHERE
				
				annulations.id_membre = '$id' AND
				annulations.id_stage = stage.id AND
				stage.id = annulations.id_stage AND
				stage.id_site = site.id AND
				stage.date1 <= now() AND
				stage.date1 >= DATE_ADD(NOW(), INTERVAL -90 DAY)
			GROUP BY
				site.id";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $annulations = array();
    while ($row = mysql_fetch_array($rs))
        $annulations[] = $row;

    $c = "<div class='col-md-12' style='padding:5px'>";
    $c .= "<legend>Infos</legend>";
    $c .= "<table width='100%'>";

    $c .= "<tr>";
    $c .= "<th>Id</th>";
    $c .= "<th>Nom</th>";
    $c .= "<th>Adresse</th>";
    $c .= "<th>Ville</th>";
    $c .= "<th>Agrément</th>";
    $c .= "<th>Commodités</th>";
    $c .= "<th>Stages ligne (J+60)</th>";
    $c .= "<th>Prix min/max (J-60)</th>";
    $c .= "<th>Stagiaires envoyés (J-90)</th>";
    $c .= "<th>Annulations (J-90)</th>";
    $c .= "<th>Actif</th>";
    $c .= "</tr>";

    foreach ($sites as $site) {

        $id = $site['id'];
        $nom = $site['nom'];
        $adresse = $site['adresse'];
        $code_postal = $site['code_postal'];
        $ville = $site['ville'];
        $commodites = $site['commodites'];
        $agrement = $site['agrement'];
        $actif = $site['actif'];

        $key = array_search($id, array_column($stages_lignes, 'id'));
        if ($key !== false) $nb_stages = $stages_lignes[$key]['nb_stages_ligne'];
        else                    $nb_stages = 0;

        $key = array_search($id, array_column($stages_prix, 'id'));
        if ($key !== false) {
            $min_prix = $stages_prix[$key]['min_prix'];
            $max_prix = $stages_prix[$key]['max_prix'];
        } else {
            $min_prix = 0;
            $max_prix = 0;
        }

        $key = array_search($id, array_column($stagiaires_envoyes, 'id'));
        if ($key !== false) $nb_stagiaires = $stagiaires_envoyes[$key]['nb_stagiaires'];
        else                    $nb_stagiaires = 0;

        $key = array_search($id, array_column($annulations, 'id'));
        if ($key !== false) $nb_annulations = $annulations[$key]['nb_annulations'];
        else                    $nb_annulations = 0;


        $c .= "<tr>";
        $c .= "<td>$id</td>";
        $c .= "<td>$nom</td>";
        $c .= "<td>$adresse</td>";
        $c .= "<td>$code_postal $ville</td>";
        $c .= "<td>$agrement</td>";
        $c .= "<td>$commodites</td>";
        $c .= "<td>$nb_stages</td>";
        $c .= "<td>$min_prix / $max_prix</td>";
        $c .= "<td>$nb_stagiaires</td>";
        $c .= "<td>$nb_annulations</td>";
        $c .= "<td>";
        $c .= "<i class='fa fa-2x fa-circle activation_salle' actif='$actif' aria-hidden='true' style='cursor:pointer;" .
            ($actif ? "color:green" : "color:red")
            . "' id_site='$id'></i>";
        $c .= "</td>";
        $c .= "</tr>";
    }

    $c .= "</table>";
    $c .= "</div>";

    echo $c;
}

function statistiques($id)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    //mois annee
    $sql = "SELECT 
				month(stage.date1) AS mois,
				year(stage.date1) AS annee
			FROM
				stage
			WHERE
				stage.date1 <= DATE_ADD(NOW(), INTERVAL +180 DAY) AND
				stage.date1 >= DATE_ADD(NOW(), INTERVAL -180 DAY)
			GROUP BY
				month(stage.date1)
			ORDER BY
				annee ASC, mois ASC";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $mois = array();
    while ($row = mysql_fetch_array($rs))
        $mois[] = $row;

    //stages créés
    $sql = "SELECT 
				month(stage.date1) AS mois,
				COUNT(stage.id) AS stages_crees
			FROM
				stage
			WHERE
				stage.date1 <= DATE_ADD(NOW(), INTERVAL +180 DAY) AND
				stage.date1 >= DATE_ADD(NOW(), INTERVAL -180 DAY) AND
				stage.id_membre = '$id'
			GROUP BY
				month(stage.date1)";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $stages = array();
    while ($row = mysql_fetch_array($rs))
        $stages[] = $row;

    //stages ligne
    $sql = "SELECT 
				month(stage.date1) AS mois,
				COUNT(stage.id) AS stages_lignes
			FROM
				stage
			WHERE
				stage.date1 <= DATE_ADD(NOW(), INTERVAL +180 DAY) AND
				stage.date1 >= DATE_ADD(NOW(), INTERVAL -180 DAY) AND
				stage.id_membre = '$id' AND
				stage.annule = 0 AND
				stage.is_online = 1 AND
				stage.nb_places_allouees > 0
			GROUP BY
				month(stage.date1)";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $stages_ligne = array();
    while ($row = mysql_fetch_array($rs))
        $stages_ligne[] = $row;

    //stages hors ligne
    $sql = "SELECT 
				month(stage.date1) AS mois,
				COUNT(stage.id) AS stages_hors_ligne
			FROM
				stage
			WHERE
				stage.date1 <= DATE_ADD(NOW(), INTERVAL +180 DAY) AND
				stage.date1 >= DATE_ADD(NOW(), INTERVAL -180 DAY) AND
				stage.id_membre = '$id' AND
				(stage.annule > 0 OR stage.nb_places_allouees <= 0)
			GROUP BY
				month(stage.date1)";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $stages_hors_ligne = array();
    while ($row = mysql_fetch_array($rs))
        $stages_hors_ligne[] = $row;

    //nombre de lieux
    $sql = "SELECT 
				month(stage.date1) AS mois,
				COUNT(DISTINCT site.id) AS nb_lieux
			FROM
				stage, site
			WHERE
				stage.date1 <= DATE_ADD(NOW(), INTERVAL +180 DAY) AND
				stage.date1 >= DATE_ADD(NOW(), INTERVAL -180 DAY) AND
				stage.id_membre = '$id' AND
				stage.id_site = site.id
			GROUP BY
				month(stage.date1)";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $lieux = array();
    while ($row = mysql_fetch_array($rs))
        $lieux[] = $row;


    //stagiaires inscrits
    $sql = "SELECT 
				month(stage.date1) AS mois,
				COUNT(archive_inscriptions.id) AS stagiaires_envoyes	
			FROM 
				archive_inscriptions, stage, stagiaire
			WHERE
				stagiaire.id = archive_inscriptions.id_stagiaire AND
				stagiaire.paiement > 0 AND
				archive_inscriptions.id_membre = '$id' AND
				stage.id = archive_inscriptions.id_stage AND
				stage.date1 <= DATE_ADD(NOW(), INTERVAL +180 DAY) AND
				stage.date1 >= DATE_ADD(NOW(), INTERVAL -180 DAY)
			GROUP BY
				month(stage.date1)";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $stagiaires_envoyes = array();
    while ($row = mysql_fetch_array($rs))
        $stagiaires_envoyes[] = $row;

    //stagiaires facturés
    $sql = "SELECT 
				month(stage.date1) AS mois,
				COUNT(stagiaire.id) AS stagiaires_factures	
			FROM 
				stagiaire, stage, transaction
			WHERE
				stage.id_membre = '$id' AND
				stagiaire.id_stage = stage.id AND
				transaction.id_stagiaire = stagiaire.id AND
				transaction.virement > 0 AND
				stage.date1 <= DATE_ADD(NOW(), INTERVAL +180 DAY) AND
				stage.date1 >= DATE_ADD(NOW(), INTERVAL -180 DAY)
			GROUP BY
				month(stage.date1)";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $stagiaires_factures = array();
    while ($row = mysql_fetch_array($rs))
        $stagiaires_factures[] = $row;

    //annulations
    $sql = "SELECT 
				month(stage.date1) AS mois,
				COUNT(annulations.id) AS nb_annulations	
			FROM 
				annulations, stage
			WHERE
				
				annulations.id_membre = '$id' AND
				annulations.id_stage = stage.id AND
				stage.date1 <= DATE_ADD(NOW(), INTERVAL +180 DAY) AND
				stage.date1 >= DATE_ADD(NOW(), INTERVAL -180 DAY)
			GROUP BY
				month(stage.date1)";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $annulations = array();
    while ($row = mysql_fetch_array($rs))
        $annulations[] = $row;

    //prix min max
    $sql = "SELECT 
				month(stage.date1) AS mois,
				AVG(stage.prix) AS moy_prix,
				MIN(stage.prix) AS min_prix,
				MAX(stage.prix) AS max_prix				
			FROM 
				stage
			WHERE
				stage.date1 <= DATE_ADD(NOW(), INTERVAL +180 DAY) AND
				stage.date1 >= DATE_ADD(NOW(), INTERVAL -180 DAY) AND
				stage.annule = 0 AND
				stage.id_membre = '$id'
			GROUP BY
				month(stage.date1)";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $stages_prix = array();
    while ($row = mysql_fetch_array($rs))
        $stages_prix[] = $row;


    $c = "<div class='col-md-12' style='padding:5px'>";
    $c .= "<legend>Infos</legend>";
    $c .= "<table width='100%'>";

    $c .= "<tr>";
    $c .= "<th>&nbsp</th>";
    $c .= "<th>Stages créés</th>";
    $c .= "<th>Stages en ligne</th>";
    $c .= "<th>Stages hors ligne</th>";
    $c .= "<th>Nb lieux</th>";
    $c .= "<th>Stagiaires envoyés</th>";
    $c .= "<th>Stagiaires facturés</th>";
    $c .= "<th>Annulations</th>";
    $c .= "<th>Prix min</th>";
    $c .= "<th>Prix max</th>";
    $c .= "<th>Prix moy</th>";
    $c .= "</tr>";

    foreach ($mois as $item) {

        $mois = $item['mois'];
        $annee = $item['annee'];

        $key = array_search($mois, array_column($stages, 'mois'));
        if ($key !== false) $stages_crees = $stages[$key]['stages_crees'];
        else                    $stages_crees = 0;

        $key = array_search($mois, array_column($stages_ligne, 'mois'));
        if ($key !== false) $nb_stages_ligne = $stages_ligne[$key]['stages_lignes'];
        else                    $nb_stages_ligne = 0;

        $key = array_search($mois, array_column($stages_hors_ligne, 'mois'));
        if ($key !== false) $nb_stages_hors_ligne = $stages_hors_ligne[$key]['stages_hors_ligne'];
        else                    $nb_stages_hors_ligne = 0;

        $key = array_search($mois, array_column($lieux, 'mois'));
        if ($key !== false) $nb_lieux = $lieux[$key]['nb_lieux'];
        else                    $nb_lieux = 0;

        $key = array_search($mois, array_column($stagiaires_envoyes, 'mois'));
        if ($key !== false) $nb_stagiaires_envoyes = $stagiaires_envoyes[$key]['stagiaires_envoyes'];
        else                    $nb_stagiaires_envoyes = 0;

        $key = array_search($mois, array_column($stagiaires_factures, 'mois'));
        if ($key !== false) $nb_stagiaires_factures = $stagiaires_factures[$key]['stagiaires_factures'];
        else                    $nb_stagiaires_factures = 0;

        $key = array_search($mois, array_column($annulations, 'mois'));
        if ($key !== false) $nb_annulations = $annulations[$key]['nb_annulations'];
        else                    $nb_annulations = 0;

        $key = array_search($mois, array_column($stages_prix, 'mois'));
        if ($key !== false) {
            $moy_prix = round($stages_prix[$key]['moy_prix'], 0);
            $min_prix = $stages_prix[$key]['min_prix'];
            $max_prix = $stages_prix[$key]['max_prix'];
        } else {
            $moy_prix = 0;
            $min_prix = 0;
            $max_prix = 0;
        }

        $c .= "<tr>";
        $c .= "<td>" . $mois . "-" . $annee . "</td>";
        $c .= "<td>$stages_crees</td>";
        $c .= "<td>$nb_stages_ligne</td>";
        $c .= "<td>$nb_stages_hors_ligne</td>";
        $c .= "<td>$nb_lieux</td>";
        $c .= "<td>$nb_stagiaires_envoyes</td>";
        $c .= "<td>$nb_stagiaires_factures</td>";
        $c .= "<td>$nb_annulations</td>";
        $c .= "<td>$min_prix</td>";
        $c .= "<td>$max_prix</td>";
        $c .= "<td>$moy_prix</td>";
        $c .= "</tr>";
    }

    $c .= "</table>";
    $c .= "</div>";

    echo $c;
}

function affiche_factures_centre($id_membre)
{
    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $virements = array();
    $sql = "SELECT * FROM virement WHERE id_membre = '$id_membre' ORDER BY id DESC";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
    while ($row = mysql_fetch_array($rs))
        $virements[] = $row;

    $c = "<div class='col-md-12' style='padding:5px'>";
    $c .= "<legend>Virements</legend>";
    $c .= "<table width='100%'>";

    $c .= "<tr>";
    $c .= "<th>Date</th>";
    $c .= "<th>Total</th>";
    $c .= "<th>Commentaires</th>";
    $c .= "</tr>";

    foreach ($virements as $virement) {

        $date = date('d-m-Y', strtotime($virement['date']));
        $total = $virement['total'];
        $commentaire = $virement['commentaire'];
        $id_virement = $virement['id'];

        $c .= "<tr>";
        $c .= "<td nowrap>$date</td>";
        $c .= "<td>$total</td>";
        $c .= "<td>$commentaire</td>";
        $c .= "<td><a href='aff_facture.php?id_membre=$id_membre&id_virement=$id_virement' target='_blank'><i style='float:right' class='fa fa-file fa-2x'></i></a></td>";
        $c .= "</tr>";
    }

    $c .= "</table>";
    $c .= "</div>";

    echo $c;
}


function paiement_cb($reference, $montant, $cardNumber, $cardExpiry, $cardCVC, $k, $old_stage, $new_stage)
{

    require_once("/home/prostage/gae/functions.php");

    $md5 = md5($reference . "psp1330#" . $montant);

    if ($md5 != $k) {
        $made = 0;
        $error_msg = "Problème de montant: contactez le service technique";
        echo json_encode(array('made' => $made, 'error_msg' => $error_msg));
    } else {
        $ret = autorisation_and_debit_no_dejainscrit($reference, $montant, $cardNumber, $cardExpiry, $cardCVC);

        if (intval($ret[0]) == 1) {
            transfert($reference, $old_stage, $new_stage);
        }

        echo json_encode(array('made' => $ret[0], 'error_msg' => utf8_encode($ret[1])));
    }
}

function transfert($reference, $old_stage, $new_stage)
{
    //supprimer le statut annulé
    //mettre à jour id_stage dans tables stagiaires et transaction
    //archiver le transfert dans une base d'archivage pour acces à historique
    //si remboursement > 0, le remettre à 0
    //gérer le dossier d'emplacement des documents téléchargés
    //envoyer le mail
    //mettre à jour le champ paiement avec le nouveau prix du stage
    //mettre à jour statut CB_OK dans transaction

    $today = date('Y-m-d');
    $error = 0;
    $msg = "";

    if (intval($old_stage) == intval($new_stage)) {
        $error = 1;
        $msg = "Vous êtes déjà inscrit à ce stage";
        return array($error, $msg);
    }

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    //recuperation donnnées du nouveau stage
    $sql = "SELECT 
				stage.id_membre, 
				stage.prix,
				stage.date1,
				site.code_postal,
				site.ville
			FROM 
				stage, site 
			WHERE 
				stage.id = '$new_stage' AND
				stage.id_site = site.id";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $row = mysql_fetch_assoc($rs);
    $new_prix = intval($row['prix']);
    $new_membre = intval($row['id_membre']);
    $new_date = $row['date1'];
    $new_cp = $row['code_postal'];
    $new_ville = $row['ville'];

    $sql = "SELECT id_stagiaire FROM transaction WHERE id = '$reference'";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $row = mysql_fetch_assoc($rs);
    $id_stagiaire = $row['id_stagiaire'];

    //recuperation donnnées ancien stage et stagiaire
    $sql = "SELECT 
				stage.id_membre, 
				stage.prix,
				stage.date1,
				site.code_postal,
				site.ville,
				stagiaire.paiement, 
				stagiaire.remboursement,
				stagiaire.supprime
			FROM 
				stage, site, stagiaire 
			WHERE 
				stagiaire.id_stage = stage.id AND
				stagiaire.id = $id_stagiaire AND
				stage.id_site = site.id";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $row = mysql_fetch_assoc($rs);
    $old_prix = intval($row['prix']);
    $old_membre = intval($row['id_membre']);
    $old_date = $row['date1'];
    $old_cp = $row['code_postal'];
    $old_ville = $row['ville'];
    $old_paiement = $row['paiement'];
    $old_remboursement = $row['remboursement'];
    $old_supprime = $row['supprime'];


    $sql = "SELECT id FROM transaction WHERE id_stagiaire = '$id_stagiaire' ORDER BY id DESC LIMIT 1";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $total = mysql_num_rows($rs);
    if (!$total) {
        $error = 1;
        $msg = "Transaction introuvable: contactez le service technique";
        return array($error, $msg);
    }

    //archivage historique
    $action = "Transfert de stage";
    $description = "Ancien: $old_stage - $old_date $old_cp $old_ville - Paiement:$old_paiement € - Remboursement: $old_remboursement - $old_supprime => Nouveau:$new_stage - $new_date $new_cp $new_ville - Paiement:$new_prix €";
    $sql = "INSERT INTO historique_stagiaire (id_stagiaire, action, description) VALUES ('$id_stagiaire', '$action', '$description')";
    mysql_query($sql, $stageconnect) or die(mysql_error());

    //update transaction
    $row = mysql_fetch_assoc($rs);
    $id_transaction = intval($row['id']);
    $sql = "UPDATE 
				transaction 
			SET 
				id_stage = '$new_stage', 
				id_membre = '$new_membre', 
				type_paiement = 'CB_OK', 
				date_transaction = '$today' 
			WHERE 
				id = $id_transaction";
    mysql_query($sql, $stageconnect) or die(mysql_error());


    //update stagiaire
    $sql = "UPDATE 
				stagiaire 
			SET 
				id_stage = '$new_stage',
				status = 'inscrit',				
				supprime = 0, 
				remboursement = 0, 
				paiement = '$new_prix'
			WHERE 
				id = '$id_stagiaire'";
    mysql_query($sql, $stageconnect) or die(mysql_error());

    //gestion du dossier des documents téléchargés
    if ($old_membre == 837 && $new_membre == 837)
        move_files($old_date, $new_date, $old_stage, $new_stage, $id_stagiaire);

    mysql_close($stageconnect);

    //envoie du mail de transfert
    mail_transfert($reference, $new_stage);
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

function mail_transfert($reference, $new_stage)
{

    require_once("includes/functions.php");
    require_once('class.phpmailer.php');

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT 
				stagiaire.status AS stagiaire_status,
				stagiaire.civilite AS stagiaire_civilite,
				stagiaire.nom AS stagiaire_nom,
				stagiaire.prenom AS stagiaire_prenom,
				stagiaire.tel AS stagiaire_tel,
				stagiaire.mobile AS stagiaire_mobile,
				stagiaire.email AS stagiaire_email,
				stagiaire.jeune_fille AS stagiaire_jeune_fille,
				stagiaire.date_naissance AS stagiaire_date_naissance,
				stagiaire.lieu_naissance AS stagiaire_lieu_naissance,
				stagiaire.adresse AS stagiaire_adresse,
				stagiaire.code_postal AS stagiaire_code_postal,
				stagiaire.ville AS stagiaire_ville,
				stagiaire.num_permis AS stagiaire_num_permis,
				stagiaire.date_permis AS stagiaire_date_permis,
				stagiaire.lieu_permis AS stagiaire_lieu_permis,
				stagiaire.cas AS stagiaire_cas,
				stagiaire.paiement AS stagiaire_paiement,
				stagiaire.motif_infraction AS stagiaire_motif_infraction,
				stagiaire.date_infraction AS stagiaire_date_infraction,
				stagiaire.date_lettre AS stagiaire_date_lettre,
				
				membre.id AS membre_id,
				membre.nom AS membre_nom,
				membre.adresse AS membre_adresse,
				membre.tel AS membre_tel,
				membre.mobile AS membre_mobile,
				membre.fax AS membre_fax,
				membre.email AS membre_email,
				
				transaction.type_paiement,
				
				stage.date1 AS stage_date1,
				stage.debut_am AS stage_debut_am,
				stage.fin_am AS stage_fin_am,
				stage.debut_pm AS stage_debut_pm,
				stage.fin_pm AS stage_fin_pm,
				
				site.nom AS site_nom,
				site.adresse AS site_adresse,
				site.code_postal AS site_code_postal,
				site.ville AS site_ville
			FROM
				stagiaire, membre, transaction, stage, site
			WHERE
				transaction.id = '$reference' AND
				transaction.id_stage = '$new_stage' AND
				transaction.id_stagiaire = stagiaire.id AND	
				transaction.id_stage = stage.id AND
				stage.id_site = site.id AND
				stage.id_membre = membre.id
			";

    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $row = mysql_fetch_assoc($rs);
    $total = mysql_num_rows($rs);

    $stagiaire_status = $row['stagiaire_status'];
    $stagiaire_civilite = $row['stagiaire_civilite'];
    $stagiaire_nom = utf8_encode($row['stagiaire_nom']);
    $stagiaire_prenom = utf8_encode($row['stagiaire_prenom']);
    $stagiaire_tel = $row['stagiaire_tel'];
    $stagiaire_mobile = $row['stagiaire_mobile'];
    $stagiaire_email = $row['stagiaire_email'];
    $stagiaire_jeune_fille = utf8_encode($row['stagiaire_jeune_fille']);
    $stagiaire_date_naissance = $row['stagiaire_date_naissance'];
    $stagiaire_lieu_naissance = utf8_encode($row['stagiaire_lieu_naissance']);
    $stagiaire_adresse = utf8_encode($row['stagiaire_adresse']);
    $stagiaire_code_postal = $row['stagiaire_code_postal'];
    $stagiaire_ville = utf8_encode($row['stagiaire_ville']);
    $stagiaire_num_permis = $row['stagiaire_num_permis'];
    $stagiaire_date_permis = $row['stagiaire_date_permis'];
    $stagiaire_lieu_permis = utf8_encode($row['stagiaire_lieu_permis']);
    $stagiaire_cas = $row['stagiaire_cas'];
    $stagiaire_paiement = $row['stagiaire_paiement'];
    $stagiaire_motif_infraction = utf8_encode($row['stagiaire_motif_infraction']);
    $stagiaire_date_infraction = $row['stagiaire_date_infraction'];
    $stagiaire_date_lettre = $row['stagiaire_date_lettre'];
    $membre_id = $row['membre_id'];
    $membre_nom = utf8_encode($row['membre_nom']);
    $membre_adresse = utf8_encode($row['membre_adresse']);
    $membre_tel = $row['membre_tel'];
    $membre_mobile = $row['membre_mobile'];
    $membre_fax = $row['membre_fax'];
    $membre_email = $row['membre_email'];
    $type_paiement = $row['type_paiement'];
    $stage_date1 = $row['stage_date1'];
    $stage_date2 = date('Y-m-d', strtotime($stage_date1 . ' + 1 days'));
    $stage_debut_am = $row['stage_debut_am'];
    $stage_fin_am = $row['stage_fin_am'];
    $stage_debut_pm = $row['stage_debut_pm'];
    $stage_fin_pm = $row['stage_fin_pm'];
    $site_nom = utf8_encode($row['site_nom']);
    $site_adresse = utf8_encode($row['site_adresse']);
    $site_code_postal = $row['site_code_postal'];
    $site_ville = utf8_encode($row['site_ville']);
    $contact = "contact@prostagespermis.fr";

    $subject = "Transfert de stage: " . $stagiaire_nom . " " . $stagiaire_prenom;

    $msg = "<h1 style=\"font-size: 22px;font-family:'MV Boli',Arial;color: #E95B61;line-height: 20px;padding: 40px 5px 40px 150px;margin: 0;text-align: center;\">Détails de votre nouveau stage !</h1>";

    $msg .= "<p>Vous venez d'être transféré sur un nouveau stage de récupération de points dont les informations
	complètes figurent ci-dessous. Nous sommes désolés pour l'éventuelle gène occasionnée</p>.";


    $msg .= "<h3>CENTRE ORGANISATEUR:</h3>";
    $msg .= "<p>" . $membre_nom . "</p>";
    $msg .= "<p>" . $membre_adresse . "</p>";
    $msg .= "<p>" . $membre_tel . " " . $membre_mobile . "</p>";
    $msg .= "<p>" . $membre_email . " " . $membre_fax . "</p>";

    $msg .= "<h3>STAGIAIRE:</h3>";
    $msg .= "<p>" . $stagiaire_civilite . " " . $stagiaire_nom . " " . $stagiaire_prenom . "</p>";
    if (isset($stagiaire_jeune_fille) && strlen($stagiaire_jeune_fille))
        $msg .= "<p>Nom jeune fille: " . $stagiaire_jeune_fille . "</p>";
    $msg .= "<p>" . $stagiaire_adresse . "</p>";
    $msg .= "<p>" . $stagiaire_code_postal . " " . $stagiaire_ville . "</p>";
    $msg .= "<p>" . $stagiaire_status . "</p>";
    $msg .= "<p>" . $stagiaire_tel . " " . $stagiaire_mobile . " " . $stagiaire_email . "</p>";
    $msg .= "<p>Né(e) le " . $stagiaire_date_naissance . " à " . $stagiaire_lieu_naissance . "</p>";
    $msg .= "<p>Permis N° " . $stagiaire_num_permis . " le " . $stagiaire_date_permis . " à " . $stagiaire_lieu_permis . "</p>";
    $msg .= "<p>Paiement: " . $stagiaire_paiement . "</p>";

    $msg .= "<h3>DETAILS DU NOUVEAU STAGE:</h3>";
    $msg .= "<p>" . MySQLDateToExplicitDate($stage_date1) . " et " . MySQLDateToExplicitDate($stage_date2) . "</p>";
    $msg .= "<p>" . $stage_debut_am . " " . $stage_fin_am . " et " . $stage_debut_pm . " " . $stage_fin_pm . "</p>";
    $msg .= "<p>" . $site_adresse . "</p>";
    $msg .= "<p>" . $site_code_postal . " " . $site_code_ville . "</p>";
    $msg .= "<p>Prix: " . $stagiaire_paiement . " euros</p>";

    $template_footer = '
        <div style="margin-top:30px;background: #6EA2DB;color:white;padding:5px 15px;border-radius:0 0 10px 10px;font-size:12px">
            <p>Toutes les infos sur votre inscription dans votre Espace Client.<br />
            Contact@prostagespermis.fr<br />
            04 34 09 12 67<br />
            De 9h à 18h du lundi au vendredi</p>
        </div>
    </div>
    ';

    $msg .= $template_footer;

    $msg = utf8_decode($msg);

    phpmailer($stagiaire_email, $subject, $msg);
    sleep(1);
    phpmailer($membre_email, $subject, $msg);

    //mailchimp
    require_once("/home/prostage/www/planificateur_tache/newsletter/functions.php");
    mailchimp_update_member($stagiaire_email, $stage_date1, $site_nom, $site_adresse, $site_code_postal, $site_ville, $stage_debut_am, "http://www.prostagespermis.fr");
}

function affiche_formulaire_paiement($id_stage, $id_stagiaire)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT id FROM transaction WHERE id_stagiaire = '$id_stagiaire' ORDER BY id DESC LIMIT 1";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $total = mysql_num_rows($rs);
    if (!$total) {
        return;
    }
    $row = mysql_fetch_assoc($rs);
    $id_transaction = intval($row['id']);

    $sql = "SELECT 
				stagiaire.paiement,
				stagiaire.supprime,
				stagiaire.id_stage,
				stagiaire.remboursement,
				stagiaire.numappel,
				stagiaire.numtrans
			FROM
				stagiaire
			WHERE
				id = '$id_stagiaire'";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $row_stagiaire = mysql_fetch_assoc($rs);
    $old_stage = $row_stagiaire['id_stage'];

    $sql = "SELECT
				stage.prix,
				stage.annule,
				stage.nb_places_allouees,
				stage.date1
			FROM
				stage
			WHERE
				stage.id = '$id_stage'";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $row_stage = mysql_fetch_assoc($rs);

    mysql_close($stageconnect);

    $paiement = intval($row_stagiaire['paiement']);
    $supprime = intval($row_stagiaire['supprime']);
    $remboursement = intval($row_stagiaire['remboursement']);
    $paiement = intval($row_stagiaire['paiement']);
    $numappel = $row_stagiaire['numappel'];
    $numtrans = $row_stagiaire['numtrans'];

    $ret = status_paiement($id_transaction, $numappel, $numtrans);
    $status_paiement_made = intval($ret['made']);
    $status_paiement_status = $ret['status'];

    if (($status_paiement_made == 0) || (stripos($status_paiement_status, "captur") === false) || $remboursement > 0)
        $paiement = 0;

    $prix = intval($row_stage['prix']);
    $annule = intval($row_stage['annule']);
    $nb_places_allouees = intval($row_stage['nb_places_allouees']);
    $date1 = $row_stage['date1'];

    $reste_a_payer = 0;
    $avoir = $paiement;
    $texte1 = "";
    $texte2 = "";

    if (($annule != 0) || ($nb_places_allouees <= 0) || (strtotime($date1) <= strtotime(date('Y-m-d')))) {
        $texte1 = "Ce stage n'est plus disponible à la réservation ! Merci de sélectionner une aure session";
        $texte2 = "Transfert impossible";
        $credit_card_disabled = true;
        $bouton_paiement = "<button disabled class='btn btn-primary'>Payer</button>";
    } else {

        //$reste_a_payer = $prix - ($paiement - $remboursement);
        $reste_a_payer = $prix - $paiement;

        if ($reste_a_payer < 0) {
            $credit_card_disabled = true;
            $bouton_paiement = "";
            //$bouton_paiement = "<button old_stage='$old_stage' new_stage='$id_stage' class='btn btn-primary remboursement_transfert_stage'>Transfert & Remboursement ".(-$reste_a_payer)." €</button>";

            $texte1 = "Le prix du stage est inférier à l'avoir du candidat. Vous devez d'abord rembourser ce candidat pour ensuite utiliser la fonction transfert";

            $texte2 = "Cliquez sur le bouton “Remboursement”";
        } else if ($reste_a_payer == 0) {
            $credit_card_disabled = true;
            $bouton_paiement = "<button old_stage='$old_stage' new_stage='$id_stage' id_transaction='$id_transaction' class='btn btn-primary transfert_stage'>Transfert</button>";

            $texte1 = "Vous n'avez rien à payer pour ce stage. Le transfert est complètement gratuit";

            $texte2 = "Cliquez sur le bouton “Transfert” pour valider votre inscription sur ce nouveau stage.";
        } else if ($reste_a_payer > 0) {
            $credit_card_disabled = false;
            $bouton_paiement = "<button old_stage='$old_stage' new_stage='$id_stage' class='btn btn-primary paiement_cb'>Payer $reste_a_payer €</button>";

            $texte1 = "Pour vous inscrire sur ce stage, vous devez régler un complément de " . $reste_a_payer . " €";

            $texte2 = "Cliquez sur le bouton “Payer” pour valider votre inscription sur ce nouveau stage.";
        }
    }

    $class = $credit_card_disabled ? "div_disabled" : "";

    $c = "";
    $c .= "<div class='panel panel-default'>";
    $c .= "<div class='panel-heading'>";
    $c .= "<div class='pull-right'>";
    $c .= "<i class='fa fa-cc-amex text-success'></i>";
    $c .= "<i class='fa fa-cc-mastercard text-warning'></i>";
    $c .= "<i class='fa fa-cc-discover text-danger'></i>";
    $c .= "</div>";
    $c .= "<h5 class='panel-title'>";
    $c .= "<a data-toggle='collapse' data-parent='#accordion' href='#collapseTwo'><i class='fa fa-2x fa-lock'></i> Paiement en ligne</a>";
    $c .= "</h5>";
    $c .= "</div>";
    $c .= "<div id='collapseTwo' class='panel-collapse collapse in'>";
    $c .= "<div class='panel-body'>";

    $c .= "<div class='row'>";
    $c .= "<div class='col-md-6'>";
    $c .= "<h2>Transfert pour le stage du " . date('d-m-Y', strtotime($date1)) . "</h2>";
    $c .= "<h3>Prix du stage: $prix €</h3>";
    $c .= "<h3>Avoir: <span class='text-navy'>$avoir €</span></h3>";

    $c .= "<p class='m-t'>";
    $c .= $texte1;

    $c .= "</p>";
    $c .= "<p>";
    $c .= $texte2;
    $c .= "</p>";
    $c .= "</div>";


    $c .= "<div class='col-md-6'>";
    $c .= "<input type='hidden' id='hidden_prix' value='" . $reste_a_payer . "'>";
    $c .= "<input type='hidden' id='hidden_transaction' value='$id_transaction'>";
    $c .= "<div class='row $class'>";
    $c .= "<div class='col-xs-12'>";
    $c .= "<div class='form-group'>";
    $c .= "<label>NUMERO CARTE</label>";
    $c .= "<div class='input-group'>";
    $c .= "<input type='text' class='form-control' id='cardNumber' name='cardNumber' placeholder='Numéro de carte' required />";
    $c .= "<span class='input-group-addon'><i class='fa fa-credit-card'></i></span>";
    $c .= "</div>";
    $c .= "</div>";
    $c .= "</div>";
    $c .= "</div>";

    $c .= "<div class='row $class'>";
    $c .= "<div class='col-xs-7 col-md-7'>";
    $c .= "<div class='form-group'>";
    $c .= "<label>DATE EXPIRATION</label><br>";
    $c .= "<select style='padding:7px' id='month_expiration'>";
    for ($i = 1; $i <= 12; $i++) {
        $val = sprintf("%02d", $i);
        $c .= "<option value='$val'>" . $val . "</option>";
    }
    $c .= "</select>";
    $c .= " / ";

    $c .= "<select style='padding:7px;' id='year_expiration'>";
    $current_year = intval(date("Y")) - 2000;
    for ($i = $current_year; $i < $current_year + 20; $i++)
        $c .= "<option value='$i'>" . $i . "</option>";
    $c .= "</select>";
    $c .= "</div>";
    $c .= "</div>";

    $c .= "<div class='col-xs-5 col-md-5 pull-right'>";
    $c .= "<div class='form-group'>";
    $c .= "<label>CODE</label><br>";
    $c .= "<input type='text' class='form-control' id='cardCVC' name='cardCVC' placeholder='Code à 3 chiffres'  required/>";
    $c .= "</div>";
    $c .= "</div>";
    $c .= "</div>";

    $c .= "<div class='row'>";
    $c .= "<div class='col-xs-12'>";
    $c .= $bouton_paiement;
    $c .= "</div>";
    $c .= "</div>";
    $c .= "</div>";


    $c .= "</div>";
    $c .= "</div>";
    $c .= "</div>";
    $c .= "</div>";

    echo $c;
}

function status_paiement($reference, $numappel, $numtrans)
{
    include("/home/prostage/connections/stageconnect.php");
    require_once("/home/prostage/connections/config.php");
    require_once "../params.php";
    require_once "../debug.php";
    require_once "../display_error.php";
    require_once APP . 'order/services/RetrieveFullOrderByStageStudent.php';

    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT 
				transaction.id,
                transaction.id_stage,
       			transaction.id_stagiaire
			FROM
				transaction
			WHERE
				transaction.id = $reference";

    $rs = mysql_query($sql, $stageconnect);
    $row = mysql_fetch_assoc($rs);
    mysql_close($stageconnect);

    $reference = $row['id'];
    $stageId = $row['id_stage'];
    $studentId = $row['id_stagiaire'];

    $stage = (new RetrieveFullOrderByStageStudent())->__invoke($studentId, $stageId, $mysqli);
    if ($stage) {
        $reference = $stage->reference_order;
    }
    require_once("../../gae/functions.php");
    $ret = retour_consultation($reference, $numtrans, $numappel);
    return array('made' => $ret[0], 'status' => utf8_encode($ret[1]));
}

function mise_en_attente($id_stagiaire)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "UPDATE stagiaire SET supprime=1, attente=1 WHERE id = $id_stagiaire";
    mysql_query($sql, $stageconnect) or die(mysql_error());

    mysql_close($stageconnect);
}

function note_interne($id_membre, $note)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "UPDATE membre SET note='$note' WHERE id = $id_membre";
    mysql_query($sql, $stageconnect) or die(mysql_error());

    mysql_close($stageconnect);
}

function blackliste_centre($id_membre)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "UPDATE membre SET blackliste=((blackliste+1)%2) WHERE id = $id_membre";
    mysql_query($sql, $stageconnect) or die(mysql_error());

    mysql_close($stageconnect);
}

function suppression_centre($id_membre)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "DELETE FROM membre WHERE id = $id_membre";
    mysql_query($sql, $stageconnect) or die(mysql_error());

    mysql_close($stageconnect);
}

function do_remboursement($reference, $montant, $type)
{

    include("/home/prostage/connections/stageconnect.php");
    include "../modules/module.php";

    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT id_stagiaire, id_membre, id_stage FROM transaction WHERE id = '$reference'";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $row = mysql_fetch_array($rs);
    $id_stagiaire = $row['id_stagiaire'];
    $id_membre = $row['id_membre'];
    $id_stage = $row['id_stage'];
    $today = date("Y-m-d H:i:s");

    $sql = "SELECT supprime FROM stagiaire WHERE id = '$id_stagiaire'";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $row = mysql_fetch_array($rs);
    $supprime = intval($row['supprime']);

    if ($supprime == 0) {
        /*$sql = "UPDATE stage SET nb_places_allouees = (nb_places_allouees + 1) WHERE id = $id_stage";
        mysql_query($sql, $stageconnect) or die(mysql_error());*/
        require_once("/home/prostage/connections/config.php");
        require_once '/home/prostage/www/src/stage/repositories/StageStateRepository.php';
        $stageRepo  =   new StageStateRepository($mysqli);
        $nbPlacesAlreadyPay = $stageRepo->countCurrentSubscription($id_stage);
        $stageRepo->updateStageStateAfterSellPlace($nbPlacesAlreadyPay, $id_stage);

        $trainingApi = new \App\Actions\Api\TrainingApiAction();
        $trainingApi->updateDataStageApi($id_stage);
    }

    $sql = "UPDATE stagiaire SET 
				supprime = 1,
				attente = 0,
				remboursement = '$montant',
				attente_remboursement = 0,
				type_remboursement = '$type',
				date_suppression = '$today',
				date_remboursement = '$today'
			WHERE 
				id = '$id_stagiaire'";
    mysql_query($sql, $stageconnect) or die(mysql_error());

    $sql = "SELECT
				stagiaire.id_externe AS id_stagiaire_externe,
				stage.id_externe AS id_stage_externe,
				stage.date1
			FROM 
				stagiaire, stage 
			WHERE 
				stagiaire.id = '$id_stagiaire' AND
				stagiaire.id_stage = stage.id";
    $rs = mysql_query($sql, $stageconnect);
    $row = mysql_fetch_assoc($rs);
    $id_stagiaire_externe = $row['id_stagiaire_externe'];
    $id_stage_externe = $row['id_stage_externe'];
    $date1 = $row['date1'];
    mysql_close($stageconnect);

    //require_once ("/home/prostage/common_bootstrap2/notifications.php");
    $type_interlocuteur = 0;
    $id_interlocuteur = $id_stagiaire;
    $notifie = 1;
    $type_destinataire = 1;
    $message = "Bonjour, un remboursement de " . $montant . " euros a été effectué. Cette transaction sera visible sur votre relevé bancaire sous 5 jours (carte à débit immédiat) ou en fin de mois (carte à débit différé).";
    send_notification($type_interlocuteur, $id_interlocuteur, $type_destinataire, $notifie, $message);

    if (($id_membre == 793) && ($supprime == 0)) {
        require_once("includes/functions.php");

        $resp = rppcAnnulation($id_stagiaire_externe, $id_stage_externe);

        if (!$resp) {
            $message = "Rppc annulation simpligestion";
            $err_id1 = $id_stagiaire_externe;
            $err_id2 = $id_stage_externe . " Date:" . $date1;
            sendErrors($message, $err_id1, $err_id2);
        }
    }
    if (($id_membre == 1060) && ($supprime == 0)) {
        include("/home/prostage/www/ws/prod/fsp/to/inscription/cancel.php");
        cancelInscription($id_membre, $id_stagiaire, '_si');
    }
}

function remboursement($reference, $montant, $numappel, $numtrans, $type)
{
    require_once("../../gae/functions.php");
    if (intval($type) == 1) {
        include("/home/prostage/connections/stageconnect.php");
        require_once("/home/prostage/connections/config.php");
        require_once "../params.php";
        require_once "../debug.php";
        require_once APP . 'order/services/RetrieveFullOrderByStageStudent.php';

        mysql_select_db($database_stageconnect, $stageconnect);

        $sql = "SELECT 
				transaction.id,
                transaction.id_stage,
       			transaction.id_stagiaire
			FROM
				stagiaire, transaction
			WHERE
				transaction.id = $reference";

        $rs = mysql_query($sql, $stageconnect);
        $row = mysql_fetch_assoc($rs);
        mysql_close($stageconnect);

        $referenceUpdate = $row['id'];
        $stageId = $row['id_stage'];
        $studentId = $row['id_stagiaire'];

        $stage = (new RetrieveFullOrderByStageStudent())->__invoke($studentId, $stageId, $mysqli);
        if ($stage) {
            $referenceUpdate = $stage->reference_order;
        }
        $ret = annulation_remboursement($referenceUpdate, $montant, $numtrans, $numappel);
    } else {
        $ret[0] = 1;
    }
    if (intval($ret[0]) == 1) { //succes
        do_remboursement($reference, $montant, $type);
    }
    echo json_encode(array('made' => $ret[0], 'error_msg' => utf8_encode($ret[1])));
}

function messages()
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT 
				notifications.id,
				notifications.type_interlocuteur,
				notifications.timestamp,
				notifications.message,
				stagiaire.id AS id_stagiaire,
				stagiaire.nom,
				stagiaire.prenom
			FROM
				notifications, stagiaire
			WHERE
				lu = 0 AND 
				type_interlocuteur > 0 AND 
				notifie = 1 AND 
				stagiaire.id = notifications.id_interlocuteur	
			ORDER BY notifications.id DESC";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);

    $data = array();

    while ($row = mysql_fetch_array($rs)) {
        $row['timestamp'] = date('d-m-Y H:i', strtotime($row['timestamp']));
        $row['identite'] = $row['nom'] . " " . $row['prenom'];
        $data[] = $row;
    }

    header("Content-Type: application/json");
    return json_encode($data);
}

function send_notification($type_interlocuteur, $id_interlocuteur, $type_destinataire, $notifie, $message)
{

    require_once("/home/prostage/common_bootstrap2/notifications.php");
    $id_notification = notification($type_interlocuteur, $id_interlocuteur, $type_destinataire, $notifie, $message);

    return $id_notification;
}

function change_etat_nonlu($id)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "UPDATE notifications SET lu=((lu+1)%2) WHERE id=$id";
    mysql_query($sql, $stageconnect) or die(mysql_error());

    mysql_close($stageconnect);
}

function confirm_assujetissement_tva($id_membre)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "UPDATE membre SET assujetti_tva_confirme=((assujetti_tva_confirme+1)%2) WHERE id=$id_membre";
    mysql_query($sql, $stageconnect) or die(mysql_error());

    $sql = "SELECT assujetti_tva_confirme FROM membre WHERE id=$id_membre";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $row = mysql_fetch_array($rs);
    $assujetti_tva_confirme = intval($row['assujetti_tva_confirme']);

    mysql_close($stageconnect);

    return $assujetti_tva_confirme;
}

function activation_salle($id)
{

    include("/home/prostage/connections/stageconnect.php");
    include "../modules/module.php";

    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "UPDATE site SET actif=((actif+1)%2) WHERE id = '$id'";
    mysql_query($sql, $stageconnect) or die(mysql_error());

    $sql = "SELECT actif FROM site WHERE id = '$id'";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $row = mysql_fetch_array($rs);
    $actif = intval($row['actif']);

    if ($actif == 0) {
        $sql = "UPDATE stage SET nb_places_allouees = 0 WHERE id_site='$id' AND date1 > now()";
        mysql_query($sql, $stageconnect);
    } else if ($actif == 1) {
        $sql = "UPDATE stage SET nb_places_allouees = 20 WHERE id_site='$id' AND date1 > now()";
        mysql_query($sql, $stageconnect);
    }
    mysql_close($stageconnect);

    $trainingApi = new \App\Actions\Api\TrainingApiAction();
    $trainingApi->updateDataStageApi($id);

    echo $actif;
}

function get_message_centre($id)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT * FROM message_centre WHERE id = '$id'";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $row = mysql_fetch_array($rs);
    $objet = $row['objet'];
    mysql_close($stageconnect);

    echo "<textarea id='objet' rows='3'>" . $objet . "</textarea>";
}

function update_message_centre($id, $objet)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "UPDATE message_centre SET objet=\"$objet\" WHERE id = '$id'";
    mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
}

function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function update_notification_centre($id_notification, $membre)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "UPDATE notifications SET id_centre = '$membre' WHERE id = '$id_notification'";
    mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
}

function getTownByPostalCode($postalCode)
{
    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "SELECT villes_france_free_full.ville_nom, villes_france_free_full.ville_id from villes_france_free_full WHERE ville_code_postal = '$postalCode'";
    $rs_code = mysql_query($sql, $stageconnect) or die(mysql_error());
    $options = '';
    while ($row = mysql_fetch_assoc($rs_code)) {
        $options .= '<option value="' . $row['ville_id'] . '">' . $row['ville_nom'] . '</option>';
    }
    echo $options;
}

function addFreeTown($townSelected)
{
    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT * from villes_france_free_full WHERE ville_id = $townSelected";
    $rs_code = mysql_query($sql, $stageconnect) or die(mysql_error());
    $townSelect = mysql_fetch_assoc($rs_code);
    $department = $townSelect['ville_departement'];
    $ville_slug = $townSelect['ville_slug'];
    $ville_nom = $townSelect['ville_nom'];
    $ville_nom_simple = $townSelect['ville_nom_simple'];
    $ville_nom_reel = $townSelect['ville_nom_reel'];
    $ville_code_postal = $townSelect['ville_code_postal'];
    $ville_commune = $townSelect['ville_commune'];
    $ville_code_commune = $townSelect['ville_code_commune'];
    $ville_arrondissement = $townSelect['ville_arrondissement'];
    $ville_population_2012 = $townSelect['ville_population_2012'];
    $ville_longitude_deg = $townSelect['ville_longitude_deg'];
    $ville_latitude_deg = $townSelect['ville_latitude_deg'];
    $ranking_nat = $townSelect['ranking_nat'];
    $ranking_ads = $townSelect['ranking_ads'];
    $ranking_timestamp = $townSelect['ranking_timestamp'];
    $ranking_url = $townSelect['ranking_url'];
    $ranking_param_geo_i_search_from = $townSelect['ranking_param_geo_i_search_from'];

    $sql = "INSERT INTO villes_france_free 
                        (ville_departement, ville_slug, ville_nom, ville_nom_simple, ville_nom_reel, ville_code_postal, ville_commune, ville_code_commune, ville_arrondissement, ville_population_2012, ville_longitude_deg, ville_latitude_deg, ranking_nat, ranking_ads, ranking_timestamp, ranking_url, ranking_param_geo_i_search_from) 
                VALUES  ('$department', '$ville_slug', '$ville_nom', '$ville_nom_simple', '$ville_nom_reel', '$ville_code_postal', '$ville_commune', '$ville_code_commune', '$ville_arrondissement', '$ville_population_2012', '$ville_longitude_deg', '$ville_latitude_deg', '$ranking_nat', '$ranking_ads', '$ranking_timestamp', '$ranking_url', '$ranking_param_geo_i_search_from')
            ";

    mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
}

function isKBIS($id_membre)
{

    $liste = array();
    $path = "/home/prostage/www/documents_centres";
    $name = "kbis";

    if (is_dir($path)) {

        $matche = '#^' . $id_membre . '#ui';
        $f = new FilesystemIterator($path, FilesystemIterator::KEY_AS_FILENAME);
        $r = new RegexIterator($f, $matche, RegexIterator::MATCH, RegexIterator::USE_KEY);

        foreach ($r as $t) {
            if (stripos($t->getFilename(), $name))
                array_push($liste, $t->getFilename());
        }
    }

    return $liste;
}

function isUrSsarf($id_membre)
{
    require_once "/home/prostage/www/params.php";
    $path = "/home/prostage/www/documents_centres";
    $name = "urssaf";
    $liste = [];
    if (is_dir($path)) {
        $matche = '#^' . $id_membre . '#ui';
        $f = new FilesystemIterator($path, FilesystemIterator::KEY_AS_FILENAME);
        $r = new RegexIterator($f, $matche, RegexIterator::MATCH, RegexIterator::USE_KEY);

        foreach ($r as $t) {
            if (stripos($t->getFilename(), $name))
                array_push($liste, $t->getFilename());
        }
    }
    return $liste;
}

function isCGV($id_membre)
{

    $liste = array();
    $path = "/home/prostage/www/documents_centres";
    $name = "cgv";

    if (is_dir($path)) {

        $matche = '#^' . $id_membre . '#ui';
        $f = new FilesystemIterator($path, FilesystemIterator::KEY_AS_FILENAME);
        $r = new RegexIterator($f, $matche, RegexIterator::MATCH, RegexIterator::USE_KEY);

        foreach ($r as $t) {
            if (stripos($t->getFilename(), $name))
                array_push($liste, $t->getFilename());
        }
    }

    return $liste;
}

function parseInitialPaymentBeforeTransfer($historiques)
{
    if (empty($historiques)) {
        return '';
    }
    $first_transfer_message = '';
    sort($historiques);
    foreach ($historiques as $historique) {
        if (strpos(strtolower($historique['message']), strtolower('transfert de stage')) !== false) {
            $first_transfer_message = $historique['message'];
            break;
        }
    }
    $prices = [];
    if ($first_transfer_message != '') {
        $prices = explode(':', substr($first_transfer_message, strpos($first_transfer_message, 'Prix'), 8));
        $lastestIndex = strpos($first_transfer_message, 'Ancien');
        $newCentence = substr($first_transfer_message, 0, strpos($first_transfer_message, '-'));
        $arrLastIndex = explode(':', substr($newCentence, $lastestIndex));
        $prices[2] = $arrLastIndex[1];
    }
    return $prices;
}

function updateDisplayPhone($params)
{
    if (isset($params['value'], $params['memberId'])) {
        $memberId = $params['memberId'];
        $value = $params['value'];

        include("/home/prostage/connections/stageconnect.php");
        mysql_select_db($database_stageconnect, $stageconnect);
        $sql = "UPDATE membre SET display_phone_student = '$value' WHERE id='$memberId'";
        mysql_query($sql, $stageconnect) or die(mysql_error());
        mysql_close($stageconnect);
    }
}

function updateDisplayEmail($params)
{
    if (isset($params['value'], $params['memberId'])) {
        $memberId = $params['memberId'];
        $value = $params['value'];
        include("/home/prostage/connections/stageconnect.php");
        mysql_select_db($database_stageconnect, $stageconnect);
        $sql = "UPDATE membre SET display_student_email = '$value' WHERE id='$memberId'";
        mysql_query($sql, $stageconnect) or die(mysql_error());
        mysql_close($stageconnect);
    }
}

function set_remboursement_priority($data)
{
    $priority = (isset($data['priority']) && $data['priority'] == 'true') ? 1 : 0;
    $idStagiaire = $data['id_stagiaire'];

    if ($idStagiaire != NULL) {
        include("/home/prostage/connections/stageconnect.php");
        mysql_select_db($database_stageconnect, $stageconnect);
        $sql = "UPDATE stagiaire SET remboursement_prioritaire = $priority WHERE id = $idStagiaire";
        var_dump($sql);
        mysql_query($sql, $stageconnect) or die(mysql_error());
        mysql_close($stageconnect);
    }
}

function update_status_up2pay($data)
{
    include("/home/prostage/connections/stageconnect.php");
    require_once("/home/prostage/connections/config.php");
    require_once "/home/prostage/www/params.php";
    require_once("/home/prostage/gae/functions.php");
    require_once("/home/prostage/www/src/order/services/RetrieveFullOrderByStageStudent.php");
    require_once("/home/prostage/www/src/student/repositories/StudentRepository.php");

    $statut_paiement_up2pay = "N/A";

    $orderStage = (new RetrieveFullOrderByStageStudent())->__invoke($data['id_stagiaire'], $data['stage_id'], $mysqli);
    if ($orderStage) {
        $reference = $orderStage->reference_order;
    }

    $ret = retour_consultation($reference, $data['numtrans'], $data['numappel']);

    if ($ret && sizeof($ret) == 2) {
        $statut_paiement_up2pay = utf8_encode($ret[1]);
        (new StudentRepository($mysqli))->updateUp2payStatus($data['id_stagiaire'], $statut_paiement_up2pay);
    }
}

function create_virement_listing_stagiaires($data)
{
    include("/home/prostage/connections/stageconnect.php");
    require_once("/home/prostage/connections/config.php");
    require_once "/home/prostage/www/params.php";
    require_once APP . 'profile/repository/ProfileParticipantRepository.php';
    require_once APP . 'virement/repositories/VirementAutoStagiaireRepository.php';
    require_once APP . 'student/services/RetrieveStudentById.php';

    $profileRepo = new ProfileParticipantRepository($mysqli);
    $virementRepo = new VirementAutoStagiaireRepository($mysqli);

    $stagiaires_ids = $data['stagiaires'];

    $virement_id = $virementRepo->insertRow();

    $amount_total = 0;
    $first_student = NULL;

    foreach ($stagiaires_ids as $key => $id_stagiaire) {
        $student = (new RetrieveStudentById())->__invoke($id_stagiaire, $mysqli);
        $amount_total += $student->paiement;

        $profileRepo->updateOrCreate($id_stagiaire, [
            'idStagiaire' => $id_stagiaire,
            'meta_key' => 'waiting_virement_auto',
            'meta_value' => $virement_id
        ]);

        if ($key == 0) {
            $first_student = $student;
        }
    }

    if (sizeof($stagiaires_ids) > 1) {
        $name = 'sepa_auto_stagiaires_' . $virement_id . '_' . $amount_total . '.xml';
    } else {
        //$name = 'sepa_auto_stagiaires_' . $first_student->id . '_' . $amount_total . '.xml';
        $name = 'sepa_auto_stagiaires_' . $virement_id . '_' . $first_student->id . '_' . $amount_total . '.xml';
    }


    $virementRepo->updateAfterInit($virement_id, $name, sizeof($stagiaires_ids));

    echo json_encode($virement_id);

    return true;
}

function parseProvenanceSite($provenance_site, $provenance)
{
    $label = $provenance == 7 || $provenance == 8 ? '(Adw)' : '(Nat)';

    switch ($provenance_site) {
        case 13:
            return 'COMP ' . $label;
            break;
        case 3:
            return 'SENS ' . $label;
            break;
        case 1:
            return 'PSP ' . $label;
            break;
        default:
            return 'PSP ' . $label;
            break;
    }
}

