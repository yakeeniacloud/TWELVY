<?php
require_once("/home/prostage/connections/config.php");
include("/home/prostage/connections/stageconnect.php");
require_once '/home/prostage/www/v2/Repository/NotificationRepository.php';
require_once('/home/prostage/www/includes/fonctions.php'); // #115
require_once '../params.php';
require_once '../debug.php';

require_once APP . 'order/services/RetrieveAllUpsellPay.php';
require_once APP . 'order/services/RetrieveFullOrderByStageStudent.php';
require_once APP . 'profile/repository/ProfileParticipantRepository.php';

$notificationRepository = new NotificationRepository();

mysql_select_db($database_stageconnect, $stageconnect) or
    die('Could not select database ' . $database_stageconnect);


$from = $_POST['from'];
$to = $_POST['to'];

$id_stagiaire = intval($_POST['stagiaire']);
$id_stage_filter = intval($_POST['id_stage_filter']);

$email = '';
$firstName = '';
$lastName = '';

if ($id_stagiaire) {
    $sql = "SELECT email, nom, prenom FROM stagiaire WHERE id = '$id_stagiaire'";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $row_rs = mysql_fetch_array($rs);
    $email = trim($row_rs['email']);
    $firstName = trim($row_rs['nom']);
    $lastName = trim($row_rs['prenom']);
}

//notifications stagiaires
if ($id_stagiaire == 0) {
    $sQuery = "SELECT 
                stagiaire.facture_num,
				stagiaire.id AS id_stagiaire,
				stagiaire.id_externe AS stagiaire_id_externe,
				stagiaire.nom,
				stagiaire.prenom,
				stagiaire.tel,
				stagiaire.mobile,
				stagiaire.email,
				stagiaire.paiement,
				stagiaire.remboursement,
				stagiaire.opposition_cb,
				stagiaire.date_remboursement,
				stagiaire.date_demande_remboursement,
				stagiaire.type_remboursement,
				stagiaire.supprime,
				stagiaire.provenance,
				stagiaire.date_inscription,
				stagiaire.date_preinscription,
				stagiaire.datetime_preinscription,
				stagiaire.numappel,
				stagiaire.numtrans,
				stagiaire.attente_remboursement,
				stagiaire.presence_au_stage,
				stagiaire.attente,
				stagiaire.validations_stagiaire,
				stagiaire.commentaire,
				stagiaire.cas,
				stagiaire.heure_retard,
				stagiaire.iban,
				stagiaire.bic,
				stagiaire.numero_cb,
				stagiaire.virement_bloque,
				stagiaire.motif_annulation,
                stagiaire.profession,
                stagiaire.profession_type,
                stagiaire.provenance_site,
                stagiaire.presence_day1_am,
                stagiaire.presence_day1_pm,
                stagiaire.presence_day2_am,
                stagiaire.presence_day2_pm,
                stagiaire.up2pay_code_error,
				stage.id AS id_stage,
				stage.date1,
				stage.annule,
       
				site.ville,
				site.code_postal,
				
				membre.nom AS membre_nom,
				membre.id AS membre_id,

				transaction.virement,
				transaction.type_paiement
       
			FROM stagiaire
			INNER JOIN stage ON stage.id = stagiaire.id_stage AND " . ($id_stage_filter && $id_stage_filter > 0 ? "stage.id = '$id_stage_filter'" : "stagiaire.date_inscription <= '$to' AND stagiaire.date_inscription >= '$from'") . "
			INNER JOIN transaction on transaction.id_stagiaire = stagiaire.id
			INNER JOIN membre ON membre.id = stage.id_membre
			INNER JOIN site ON site.id = stage.id_site
            ORDER BY stage.date1 ASC, id_stage ASC";
    /*	    
			WHERE
				" . ($id_stage_filter && $id_stage_filter > 0 ? "stage.id = '$id_stage_filter'" : "stagiaire.date_inscription <= '$to' AND stagiaire.date_inscription >= '$from'") . "
			ORDER BY 
				stage.date1 ASC, id_stage ASC";*/
} else {
    $whereFilter = $email != ''
        ? "(stagiaire.email LIKE '%" . mysql_real_escape_string($email) . "%' OR (stagiaire.nom = '" . mysql_real_escape_string($firstName) . "' AND stagiaire.prenom = '" . mysql_real_escape_string($lastName) . "'))"
        : "(stagiaire.nom = '" . mysql_real_escape_string($firstName) . "' AND stagiaire.prenom = '" . mysql_real_escape_string($lastName) . "')";

    $sQuery = "SELECT
                stagiaire.facture_num,
				stagiaire.id AS id_stagiaire,
				stagiaire.id_externe AS stagiaire_id_externe,
				stagiaire.nom,
				stagiaire.prenom,
				stagiaire.tel,
				stagiaire.mobile,
				stagiaire.email,
				stagiaire.paiement,
				stagiaire.remboursement,
				stagiaire.opposition_cb,
				stagiaire.date_remboursement,
				stagiaire.date_demande_remboursement,
                stagiaire.type_remboursement,
				stagiaire.supprime,
				stagiaire.provenance,
				stagiaire.date_inscription,
				stagiaire.date_preinscription,
				stagiaire.datetime_preinscription,
				stagiaire.numappel,
				stagiaire.numtrans,
				stagiaire.attente_remboursement,
				stagiaire.presence_au_stage,
				stagiaire.attente,
				stagiaire.validations_stagiaire,
				stagiaire.commentaire,
				stagiaire.cas,
				stagiaire.heure_retard,
				stagiaire.iban,
				stagiaire.bic,
				stagiaire.numero_cb,
				stagiaire.virement_bloque,
				stagiaire.motif_annulation,
                stagiaire.profession,
                stagiaire.provenance_site,
                stagiaire.profession_type, 
				stage.id AS id_stage,
				stage.date1,
				stage.annule,
       stagiaire.presence_day1_am,
                stagiaire.presence_day1_pm,
                stagiaire.presence_day2_am,
                stagiaire.presence_day2_pm,
                stagiaire.up2pay_code_error,
				site.ville,
				site.code_postal,
				membre.nom AS membre_nom,
				membre.id AS membre_id,
				
				transaction.virement,
				transaction.type_paiement

			FROM stagiaire
			INNER JOIN stage ON stage.id = stagiaire.id_stage
			INNER JOIN transaction on transaction.id_stagiaire = stagiaire.id
			INNER JOIN membre ON membre.id = stage.id_membre
			INNER JOIN site ON site.id = stage.id_site
			WHERE
				transaction.id_stagiaire = stagiaire.id AND
				membre.id = stage.id_membre AND
				stage.id_site = site.id AND
				stagiaire.id_stage = stage.id AND
				" . $whereFilter . "
			ORDER BY 
				stage.date1 ASC, id_stage ASC";
}



$rResult = mysql_query($sQuery, $stageconnect) or die(mysql_error());
$total = mysql_num_rows($rResult);

$iFilteredTotal = $total;
$iTotal = $total;

//Output
$sEcho = '0';
if (isset($_GET['sEcho'])) {
    $sEcho = intval($_GET['sEcho']);
}
$output = array(
    "sEcho" => $sEcho,
    "iTotalRecords" => $iTotal,
    "iTotalDisplayRecords" => $iFilteredTotal,
    "aaData" => array()
);


// #115 : Nouvelle numérotation avoirs
$A_listeIdAvoirs = getListeIdAvoirs();


while ($aRow = mysql_fetch_array($rResult)) {
    $is_ask_rectract = false;
    $profileRepo = new ProfileParticipantRepository($mysqli);

    $metaKey = 'ask_post_retract_commercial';
    $resultProfileRepo = $profileRepo->getMeta($aRow['id_stagiaire'], $metaKey);

    $status_resultProfileRepo = $resultProfileRepo->meta_value;
    if ($resultProfileRepo != NULL && $resultProfileRepo->meta_value == 1) {
        $is_ask_rectract = true;
    }

    // Demande RIB ?
    $admin_demande_rib = false;
    $metaKey = 'admin_demande_rib';
    $resultProfileRepoAdminRibDemande = $profileRepo->getMeta($aRow['id_stagiaire'], $metaKey);

    $status_resultProfileRepoAdminRibDemande = $resultProfileRepoAdminRibDemande->meta_value;
    if ($resultProfileRepoAdminRibDemande != NULL && $resultProfileRepoAdminRibDemande->meta_value == 1) {
        $admin_demande_rib = true;
    }

    // Demande RIB : Réponse ?
    $admin_demande_rib_reponse = false;
    $metaKey = 'admin_demande_rib_reponse';
    $resultProfileRepoAdminDemandeRibReponse = $profileRepo->getMeta($aRow['id_stagiaire'], $metaKey);

    $status_resultProfileRepoAdminDemandeRibReponse = $resultProfileRepoAdminDemandeRibReponse->meta_value;
    if ($resultProfileRepoAdminDemandeRibReponse != NULL && $resultProfileRepoAdminDemandeRibReponse->meta_value == 1) {
        $admin_demande_rib_reponse = true;
    }


    $upsells = (new RetrieveAllUpsellPay())->__invoke($aRow['id_stagiaire'], $mysqli);
    $stage = (new RetrieveFullOrderByStageStudent())->__invoke($aRow['id_stagiaire'], $aRow['id_stage'], $mysqli);

    $row = [];
    $row['id_stagiaire'] = $aRow['id_stagiaire'];
    $row['nom'] = strtoupper($aRow['nom']);
    $row['prenom'] = ucfirst($aRow['prenom']);
    $row['email'] = $aRow['email'];

    $row['tel'] =
        implode('-', str_split(str_replace(' ', '', $aRow['mobile']), 2));

    try {
        $row['date_inscription'] = parseRowDateInscription($aRow, $notificationRepository);
    } catch (Exception $e) {
        $row['date_inscription'] = '';
    }
    $row['status_stagiaire'] = parseRowStatutStagiaire($aRow);

    $row['status_stagiaire'] .= ($aRow['up2pay_code_error'] != NULL) ? "<br><span style='white-space:nowrap; color:#a3a3a3'>Echec paiement</span>" : "";

    $detailsPresenceStudent = getDetailsPresenceStudent(
        $aRow['presence_day1_am'],
        $aRow['presence_day1_pm'],
        $aRow['presence_day2_am'],
        $aRow['presence_day2_pm']
    );

    $row['debug_detail'] = $detailsPresenceStudent . " / " . $aRow['presence_day1_am'] . " / " . $aRow['presence_day1_pm'] . " / " . $aRow['presence_day2_am'] . " / " . $aRow['presence_day2_pm'];

    if ($detailsPresenceStudent != "") {

        $row['status_stagiaire']
            .= "<br><span style='white-space:nowrap; color:#a3a3a3'>" . $detailsPresenceStudent . "</span>";
    }

    $row['status_stagiaire'] = addStatusForcingCase($row['status_stagiaire'], $status_resultProfileRepo);

    if ($admin_demande_rib_reponse) {
        $row['status_stagiaire'] .= "<br><span style='white-space:nowrap; color:#a3a3a3'>RIB reçu</span>";
    } else {
        if ($admin_demande_rib) {
            $row['status_stagiaire'] .= "<br><span style='white-space:nowrap; color:#a3a3a3'>En attente RIB</span>";
        }
    }

    $row['ref_commande'] = parseRowRefCommande($aRow, $stage);
    $row['numero_cb'] = $aRow['numero_cb'];
    $row['date_stage'] = date('d-m-Y', strtotime($aRow['date1']));
    $row['paiement'] = ($aRow['numtrans'] == "" || $aRow['type_paiement'] != 'CB_OK') ? '' : $aRow['paiement'];
    $row['ville_stage'] = $aRow['code_postal'] . " " . utf8_encode($aRow['ville']);
    /*
    $row['membre'] = $aRow['membre_nom'].'<br>('.$aRow['membre_id'].')';
    $row['membre'] .= intval($aRow['virement_bloque']) ? "<br><span style='color:red'>Virement bloqué</span>" : "";*/
    $row['membre'] = "<span title='" . utf8_encode($aRow['membre_nom']) . "'>" . utf8_encode(substr($aRow['membre_nom'], 0, 10)) . "<br>(" . $aRow['membre_id'] . ")</span>";
    $row['membre'] .= intval($aRow['virement_bloque']) ? "<br><span style='color:red'>Reversement centre bloqué</span>" : "";

    $row['site'] = parseProvenanceSite($aRow['provenance_site'], $aRow['provenance']);

    $md5 = md5($aRow['id_stagiaire'] . '!psp#');
    $md5 = substr($md5, 0, 10);

    $row['facture'] = parseRowFacture($aRow, $md5, $stage);
    $row['avoir'] = parseRowAvoir($aRow, $A_listeIdAvoirs, $md5);
    $row = parseRowUpsell($row, $aRow, $upsells);

    //$row['remboursement'] = parseRowRemboursement($aRow);
    //$row = parseRowRemboursementUpsell($row, $upsells);
    $row['date_remboursement'] = is_null($aRow['date_remboursement']) ? checkAndUpdateDateRefund($row['id_stagiaire']) : ('Date remb : '. date("d-m-Y", strtotime($aRow['date_remboursement'])));


    $row['remboursement'] = parseRowRemboursement($aRow) .'<br>'.$row['date_remboursement'];

    $row['virement'] = intval($aRow['virement']) ? "Oui" : "Non";
    $row['checkbox_remboursement'] = parseRowCheckRemboursement($aRow);

    $row['status_dossier'] = parseStatusDossier($aRow['validations_stagiaire']);

    //$row = array_map('utf8_encode', $row);

    $output['aaData'][] = $row;
}

echo json_encode($output);


function validateDate($date)
{
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') == $date;
}

function checkAndUpdateDateRefund($idCandidate)
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
				stagiaire.id = notifications.id_interlocuteur AND
			    notifications.id_interlocuteur = $idCandidate
			ORDER BY notifications.id DESC";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $date_remboursement = '';
    while ($row = mysql_fetch_array($rs)) {
        if (strpos($row['message'], 'Bonjour, un remboursement de') !== false) {
            $date_remboursement = $row['timestamp'];
            $sqlUpdate = "UPDATE stagiaire SET date_remboursement = '$date_remboursement' WHERE id = '$idCandidate'";
            mysql_query($sqlUpdate, $stageconnect) or die(mysql_error());
            break;
        }
    }
    mysql_close($stageconnect);
    return $date_remboursement;
}

function parseRowStatutStagiaire($aRow)
{
    $statusAbsence = setPresenceStudent(
        $aRow['presence_day1_am'],
        $aRow['presence_day1_pm'],
        $aRow['presence_day2_am'],
        $aRow['presence_day2_pm']
    );

    // membre_id

    if ($aRow['membre_id'] == 943) {
        if ($aRow['supprime'] == 0) {
            if ($statusAbsence == 'absent') {
                return "<span style='color:green'>inscrit</span><br><span style='white-space:nowrap; color:#a3a3a3'>absent</span>";
            }
            return "<span style='color:green;font-weight:bold'>inscrit</span>";
        }
    }

    if ($aRow['presence_au_stage'] == 2) {
        return "<span style='color:red'>annulé</span><br><span style='white-space:nowrap; color:#a3a3a3'>absent</span>";
    }
    if ($aRow['presence_au_stage'] == 3) {
        return "<span style='color:red'>annulé</span><br><span style='white-space:nowrap; color:#a3a3a3'>cause retard</span>";
    }

    if (intval($aRow['supprime']) != 1) {
        return "<span style='color:green;font-weight:bold'>inscrit</span>";
    }
    if (intval($aRow['opposition_cb']) == 1) {
        return "<span style='color:red'>annulé</span><br><span style='white-space:nowrap; color:#a3a3a3'>Opposition CB (justifiée)</span>";
    }
    if (intval($aRow['opposition_cb']) == 2) {
        return "<span style='color:red'>annulé</span><br><span style='white-space:nowrap; color:#a3a3a3'>Opposition CB (injustifiée)</span>";
    }
    if (intval($aRow['opposition_cb']) == 3) {
        return "<span style='color:red'>annulé</span><br><span style='white-space:nowrap; color:#a3a3a3'>Opposition CB (volée)</span>";
    }
    if (intval($aRow['remboursement']) > 0) {
        return "<span style='color:red'>annulé</span><br><span style='white-space:nowrap; color:#a3a3a3'>remboursé</span>";
    }
    if (intval($aRow['attente_remboursement'])) {
        return "<span style='color:red'>annulé</span><br><span style='white-space:nowrap; color:#a3a3a3'>attente<br>remboursement</span>";
    }
    if (($aRow['numtrans'] == "" || $aRow['type_paiement'] != 'CB_OK')) {
        return "<span style='color:black'>prospect</span>";
    }
    if (intval($aRow['attente']) == 1) {
        return "<span style='white-space:nowrap; color:#DF731D'>en attente</span>";
    }

    return "<span style='color:red'>annulé</span>";
}

function parseRowFacture($aRow, $md5, $stage)
{
    if ((intval($aRow['paiement']) <= 0 || $aRow['numtrans'] == "")) {
        return '';
    }
    if ($stage) {
        $facture_num = "CFPSP1_" . date("Y_m", strtotime($aRow['date_inscription'])) . "_" . $stage->num_suivi;
    } else {
        $facture_num = '-';
        if ($aRow['facture_num'] > 0) {
            $facture_num = "CFPSP2_" . date("Y_m", strtotime($aRow['date_inscription'])) . "_" . ($aRow['facture_num'] + 1000);
        }
    }
    return "<a href='ajax_genere_facture_dev.php?s=" . $aRow['id_stagiaire'] . "&k=" . $md5 . "&r=" . rand() . "' target='_blank'>$facture_num</a>";
}

function parseRowRefCommande($aRow, $stage)
{
    return "CFPSP_" . ($aRow['facture_num'] + 1000);
}

function parseRowAvoir($aRow, $A_listeIdAvoirs, $md5)
{

    if ($aRow['remboursement'] <= 0) {
        return '';
    }
    $facture_num = '-';
    if ($aRow['facture_num'] > 0) {
        $facture_num = "CAPSP_" . date("Y_m", strtotime($aRow['date_inscription'])) . "_" . ($aRow['facture_num'] + 1000);
        if (strtotime($aRow['date_inscription']) >= 1609459200) {
            $facture_num = 'CAPSP_' . date('Y_m', strtotime($aRow['date_remboursement'])) . '_' . ($A_listeIdAvoirs[$aRow['id_stagiaire']] + 400000);
        }
    }
    return "<a href='https://www.prostagespermis.fr/simpligestion/ajax_genere_facture_dev2.php?av=1&s=" . $aRow['id_stagiaire'] . "&k=" . $md5 . "&r=" . rand() . "' target='_blank'>" . $facture_num . "</a>";
}

/**
 * @throws Exception
 */
function parseRowDateInscription($aRow, $notificationRepository)
{
    //$dateDernierPaiement = $notificationRepository->getDateDernierPaiementTransfertDeStageByInterlocuteurId($aRow['id_stagiaire']);
    //if ($dateDernierPaiement == null)
    ///    $dateDernierPaiement = new DateTimeImmutable($aRow['date_preinscription']);
    //$date = date('d-m-Y', $dateDernierPaiement->getTimestamp());
    if (isset($aRow['datetime_preinscription'])) {
        return date('d-m-Y H:i', strtotime($aRow['datetime_preinscription']));
    } else {
        return date('d-m-Y', strtotime($aRow['date_inscription']));
    }
}

function parseRowRemboursement($aRow)
{
    $type_remboursement = intval($aRow['type_remboursement']) == 1 ? "Récrédit CB" : "Virement auto";
    $type_remboursement = intval($aRow['type_remboursement']) == 6 ? "Recrédit CB Paybox" : $type_remboursement;

    if (intval($aRow['remboursement']) > 0) {
        $remboursement = "Demandé le " . (new DateTimeImmutable($aRow['date_demande_remboursement']))->format('d-m-Y');
        $remboursement .= "<br>Remboursé " . $aRow['remboursement'] . " euros - STAGE";
        if (strlen($aRow['motif_remboursement'] > 0)) {
            $remboursement .= "<br>Motif : " . $aRow['motif_remboursement'];
        }

        $remboursement .= "<br><br>$type_remboursement";
        return $remboursement;
    }

    if (intval($aRow['attente_remboursement']) > 0) {
        $remboursement = "<span class='blink' style='color:red;float:right'>En attente de remboursement " . $aRow['paiement'] . " € - STAGE </span>";
        $remboursement .= "<br>Demandé le " . (new DateTimeImmutable($aRow['date_demande_remboursement']))->format('d-m-Y');
        if (strlen($aRow['motif_remboursement'] > 0)) {
            $remboursement .= "<br>Motif " . $aRow['motif_remboursement'];
        }
        return $remboursement;
    }
    return "";
}


function parseRowCheckRemboursement($aRow)
{
    $iban = $aRow['iban'];
    $bic = $aRow['bic'];
    $paiement = $aRow['paiement'];
    if (intval($aRow['attente_remboursement']) && (strlen($iban) > 0) && (strlen($bic) > 0)) {
        return "<input class='checkbox_remboursement' type='checkbox' checked>
		<input class='xml_element' type='hidden' value='$paiement' iban='$iban' bic='$bic' id_stagiaire='" . $aRow['id_stagiaire'] . "'>";
    }
    return "<input class='checkbox_remboursement' type='checkbox' disabled>";
}

function parseRowUpsell(&$row, $aRow, $upsells)
{
    $row['prix_upsell1'] = '';
    $row['facture_upsell1'] = '';
    $row['avoir_upsell1'] = '';

    $row['prix_upsell2'] = '';
    $row['facture_upsell2'] = '';
    $row['avoir_upsell2'] = '';

    if ($upsells) {
        foreach ($upsells as $upsell) {
            $facture_num = "CFCR_" . date("Y_m", strtotime($upsell['date_ajout'])) . "_" . ($upsell['num_suivi']);
            $upsell_price = $upsell['amount'];
            $facture_num_link = '<a href="' . HOST . '/facture_upsell.php?s=' . $aRow['id_stagiaire'] . '&o=' . $upsell['order_id'] . '&up=' . $upsell['upsell_id'] . '" target="_blank">' . $facture_num . '</a>';
            $upsell_avoir = '';

            if ($upsell['status'] == 3) {
                $avoir_num = "CFCR_" . date("Y_m", strtotime($upsell['date_ajout'])) . "_" . ($upsell['num_suivi']);
                $upsell_avoir = '<a href="' . HOST . '/avoir_upsell.php?s=' . $aRow['id_stagiaire'] . '&o=' . $upsell['order_id'] . '&up=' . $upsell['upsell_id'] . '" target="_blank">' . $avoir_num . '</a>';
            }

            if ($upsell['upsell_id'] == 1) {
                $row['prix_upsell1'] = $upsell_price;
                $row['facture_upsell1'] = $facture_num_link;
                $row['avoir_upsell1'] = $upsell_avoir;
            } else {
                $row['prix_upsell2'] = $upsell_price;
                $row['facture_upsell2'] = $facture_num_link;
                $row['avoir_upsell2'] = $upsell_avoir;
            }
        }
    }
    return $row;
}

function parseRowRemboursementUpsell(&$row, $upsells)
{
    if ($upsells) {
        foreach ($upsells as $upsell) {
            if ($upsell['status'] == 3) {
                $row['remboursement'] .= $row['remboursement'] != '' ? '/<br>' : '';
                $remboursement = "<span style='float:right'>Remboursé " . $upsell['amount'] . " € - " . $upsell['titre'] . "</span>";
                $remboursement .= "<br>Le " . (new DateTimeImmutable($upsell['date_remboursement']))->format('d-m-Y h:i:s');
                $row['remboursement'] .= $remboursement;
            }
            if ($upsell['status'] == 2) {
                $row['remboursement'] .= $row['remboursement'] != '' ? '/<br>' : '';
                $remboursement = "<span class='blink' style='color:red;float:right'>En attente de remboursement " . $upsell['amount'] . " € - " . $upsell['titre'] . "</span>";
                $remboursement .= "<br>Demandé le " . (new DateTimeImmutable($upsell['date_demande_remboursement']))->format('d-m-Y h:i:s');
                $row['remboursement'] .= $remboursement;
            }
        }
    }
    return $row;
}

/**
 * @param $validations_stagiaire1
 */
function parseStatusDossier($validations_stagiaire1)
{
    $dossier_incomplet = 1;
    $validations_stagiaire = $validations_stagiaire1;
    if (strlen($validations_stagiaire)) {
        $validations_stagiaire_array = explode('|', $validations_stagiaire);
        if (
            $validations_stagiaire_array[0] == 1 &&
            $validations_stagiaire_array[1] == 1 &&
            $validations_stagiaire_array[2] == 1 &&
            $validations_stagiaire_array[3] == 1
        ) {
            $dossier_incomplet = 0;
        }
    }
    return $dossier_incomplet == 0 ? 'Complet (1)' : 'A Compléter (2)';
}


function parseProvenanceSite($provenance_site, $provenance)
{
    $label = $provenance == 7 || $provenance == 8 ? '(Adw)' : '(Nat)';

    switch ($provenance) {
        case 100:
            return 'STA ' . $label;
            break;
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

function setPresenceStudent(
    $presence_day1_am,
    $presence_day1_pm,
    $presence_day2_am,
    $presence_day2_pm
) {
    if (
        $presence_day1_am > 1 ||
        $presence_day1_pm > 1 ||
        $presence_day2_am > 1 ||
        $presence_day2_pm > 1
    ) {
        return 'absent';
    }
    return 'présent';
}

function addStatusForcingCase($status, $status_resultProfileRepo)
{
    $status .= ($status_resultProfileRepo == 1) ? "<br><span style='white-space:nowrap; color:#a3a3a3'>Cas force majeure</span>" : '';
    $status .= ($status_resultProfileRepo == 2) ? "<br><span style='white-space:nowrap; color:#a3a3a3'>Cas force majeure : Accord</span>" : '';
    $status .= ($status_resultProfileRepo == 3) ? "<br><span style='white-space:nowrap; color:#a3a3a3'>Cas force majeure : Refus</span>" : '';
    $status .= ($status_resultProfileRepo == 4) ? "<br><span style='white-space:nowrap; color:#a3a3a3'>Cas force majeure : Accord Transfert</span>" : '';
    $status .= ($status_resultProfileRepo == 5) ? "<br><span style='white-space:nowrap; color:#a3a3a3'>Cas force majeure : Accord Remboursement</span>" : '';
    $status .= ($status_resultProfileRepo == 6) ? "<br><span style='white-space:nowrap; color:#a3a3a3'>Cas force majeure : Demande Avis - Transfert</span>" : '';
    $status .= ($status_resultProfileRepo == 7) ? "<br><span style='white-space:nowrap; color:#a3a3a3'>Cas force majeure : Demande Avis - Remboursement</span>" : '';
    $status .= ($status_resultProfileRepo == 9) ? "<br><span style='white-space:nowrap; color:#a3a3a3'>Accord Transfert suite avis postés</span>" : '';
    $status .= ($status_resultProfileRepo == 10) ? "<br><span style='white-space:nowrap; color:#a3a3a3'>Accord Remb suite avis postés</span>" : '';

    return $status;
}

function getDetailsPresenceStudent(
    $presence_day1_am,
    $presence_day1_pm,
    $presence_day2_am,
    $presence_day2_pm
) {
    $statut = "";

    if (
        $presence_day1_am == 2 ||
        $presence_day1_pm == 2 ||
        $presence_day2_am == 2 ||
        $presence_day2_pm == 2
    ) {
        $statut .= 'Absent';
    }
    if (
        $presence_day1_am == 3 ||
        $presence_day1_pm == 3 ||
        $presence_day2_am == 3 ||
        $presence_day2_pm == 3
    ) {
        $statut .= (strlen($statut) > 0 ? " / " : "") . 'Refusé';
    }
    if (
        $presence_day1_am == 4 ||
        $presence_day1_pm == 4 ||
        $presence_day2_am == 4 ||
        $presence_day2_pm == 4
    ) {
        $statut .= (strlen($statut) > 0 ? " / " : "") . 'Exclu pour mauvais comportement';
    }

    if (
        $presence_day1_am == 5 ||
        $presence_day1_pm == 5 ||
        $presence_day2_am == 5 ||
        $presence_day2_pm == 5
    ) {
        $statut .= (strlen($statut) > 0 ? " / " : "") . 'Refusé suite retard';
    }
    if (
        $presence_day1_am == 6 ||
        $presence_day1_pm == 6 ||
        $presence_day2_am == 6 ||
        $presence_day2_pm == 6
    ) {
        $statut .= (strlen($statut) > 0 ? " / " : "") . 'Refusé car stage complet';
    }
    if (
        $presence_day1_am == 7 ||
        $presence_day1_pm == 7 ||
        $presence_day2_am == 7 ||
        $presence_day2_pm == 7
    ) {
        $statut .= (strlen($statut) > 0 ? " / " : "") . 'Refusé car pas sur la liste';
    }
    if (
        $presence_day1_am == 8 ||
        $presence_day1_pm == 8 ||
        $presence_day2_am == 8 ||
        $presence_day2_pm == 8
    ) {
        $statut .= (strlen($statut) > 0 ? " / " : "") . 'A quitté volontairement le stage';
    }
    if (
        $presence_day1_am == 9 ||
        $presence_day1_pm == 9 ||
        $presence_day2_am == 9 ||
        $presence_day2_pm == 9
    ) {
        $statut .= (strlen($statut) > 0 ? " / " : "") . 'Stagiaire présent mais le stage n\'a pas lieu';
    }

    return $statut;
}
