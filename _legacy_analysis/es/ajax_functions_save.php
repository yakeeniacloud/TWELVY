<?php
if(isset($_POST['action']) && !empty($_POST['action'])) {
	
    $action = $_POST['action'];
	
    switch($action) {
        case 'update_etape':
			$id_stage = $_POST['id_stage'];
			$etape = $_POST['etape'];
			update_etape($id_stage, $etape);
		break;
		
        case 'espace_stagiaire':
			$id_stagiaire = $_POST['id_stagiaire'];
			espace_stagiaire($id_stagiaire);
		break;
		
        case 'editable':
			$id = intval($_POST['pk']);
			$name = $_POST['name'];
			$value = addslashes(utf8_decode($_POST['value']));
			editable($id, $name, $value);
		break;
		
		case 'update_cas':
			$id = intval($_POST['pk']);
			$name = $_POST['name'];
			$selects = $_POST['selects'];
			$value = addslashes(utf8_decode($_POST['value']));
			update_cas($id, $name, $value, $selects);
		break;
		
		case 'download_file':
			$url = $_POST['url'];
			$name = $_POST['name'];
			$ext = $_POST['ext'];
			download_file($url, $name, $ext);
		break;
		
		case 'dossier_verifie':
			$id_stagiaire = $_POST['id_stagiaire'];
			dossier_verifie($id_stagiaire);
		break;
		
		case 'update_horaires_pedagogiques':
			$id_stage = $_POST['id_stage'];
			$horaires_pedagogiques = $_POST['horaires_pedagogiques'];
			update_horaires_pedagogiques($id_stage, $horaires_pedagogiques);
		break;
		
		case 'affiche_horaires_pedagogiques':
			$id_stage = $_POST['id_stage'];
			affiche_horaires_pedagogiques($id_stage);
		break;
		
		case 'indisponible':
			$id_stage = $_POST['id_stage'];
			$id_formateur = $_POST['id_formateur'];
			indisponible($id_stage, $id_formateur);
		break;
		
		case 'send_message_contact':
			$id_stagiaire = $_POST['id_interlocuteur'];
			$message = $_POST['message'];
			send_message_contact($id_stagiaire, $message);
		break;
		
		case 'confirmation_animation':
			$id_stage = $_POST['id_stage'];
			$formation = $_POST['formation'];
			confirmation_animation($id_stage, $formation);
		break;
		
		case 'espace_stagiaire_telechargement_documents':
			$id_stagiaire = $_POST['id_stagiaire'];
			espace_stagiaire_telechargement_documents($id_stagiaire, true);
		break;
		
		case 'supprime_lieu_intervention':
			$id_site = $_POST['id_site'];
			$id_formateur = $_POST['id_formateur'];
			supprime_lieu_intervention($id_site, $id_formateur);
		break;
			
		case 'ajout_animateur':
			$id_formateur = $_POST['id_formateur'];
			$nom = $_POST['nom'];
			$prenom = $_POST['prenom'];
			$ville = $_POST['ville'];
			$tel = $_POST['tel'];
			$email = $_POST['email'];
			$prefere = $_POST['prefere'];
			ajout_animateur($id_formateur, $nom, $prenom, $ville, $tel, $email, $prefere);
		break;
		
		case 'supprime_prefere':
			$id_formateur = $_POST['id_formateur'];
			$id_prefere = $_POST['id_prefere'];
			$prefere = $_POST['prefere'];
			supprime_prefere($id_formateur, $id_prefere, $prefere);
		break;
		
		case 'update_sous_cat_etat_permis':
			$id_stagiaire = $_POST['id_stagiaire'];
			$etat_permis = $_POST['etat_permis'];
			update_sous_cat_etat_permis($etat_permis, $id_stagiaire, true);
		break;
	
		case 'update_sous_cat_cas':
			$cas = $_POST['cas'];
			$selects = $_POST['selects'];
			update_sous_cat_cas($cas, $selects, true);
		break;
		
		case 'update_validations_stagiaire':
			$id_stagiaire = $_POST['id_stagiaire'];
			$accordeon = $_POST['accordeon'];
			update_validations_stagiaire($id_stagiaire, $accordeon);
		break;
		
		case 'annulation_inscription':
			$id_stagiaire = $_POST['id_stagiaire'];
			annulation_inscription($id_stagiaire);
		break;
		
		case 'send_notification':
			$type_interlocuteur = $_POST['type_interlocuteur'];
			$id_interlocuteur = $_POST['id_interlocuteur'];
			$notifie = $_POST['notifie'];
			$message = $_POST['message'];
			$id_notification = send_notification($type_interlocuteur, $id_interlocuteur, $notifie, $message);
			if (isset($_POST['id_centre']) && !is_null($_POST['id_centre']))
				update_notification($id_notification, $_POST['id_centre']);
		break;
		
		case 'do_md5':
			$val = intval($_POST['val']);
			$md5 = 	md5($val . '!psp#13');
			echo $md5;
		break;
		
		case 'do_md5_mix':
			$val1 = intval($_POST['val1']);
			$val2 = intval($_POST['val2']);
			$md5 = md5($val1."psp1330#".$val2);
			echo $md5;
		break;
		
		case 'formulaire_paiement':
			$id_stage = $_POST['id_stage'];
			$id_stagiaire = $_POST['id_stagiaire'];
			$prix = intval($_POST['prix']);
			$stage_price = isset($_POST['stage_price']) ? (int)$_POST['stage_price'] : $prix;
			affiche_formulaire_paiement($id_stage, $id_stagiaire, $prix, $stage_price);
		break;
		
		case 'transfert':
			$id_transaction = $_POST['id_transaction'];
			$old_stage = $_POST['old_stage'];
			$new_stage = $_POST['new_stage'];
			$prix = $_POST['prix'];
			$stage_price = isset($_POST['stage_price']) ? $_POST['stage_price'] : 0;
			transfert($id_transaction, $old_stage, $new_stage, $prix, $stage_price);
		break;
		
		case 'remboursement_transfert_stage':
			$id_transaction = $_POST['id_transaction'];
			$old_stage = $_POST['old_stage'];
			$new_stage = $_POST['new_stage'];
			$numappel = $_POST['numappel'];
			$numtrans = $_POST['numtrans'];
			$reste_a_payer = $_POST['reste_a_payer'];
			$id_stagiaire = $_POST['id_stagiaire'];
			$ret = remboursement_transfert_stage($id_transaction, $old_stage, $new_stage, $numappel, $numtrans, $reste_a_payer, $id_stagiaire);
			echo $ret;
		break;
		
		case 'paiement_cb':
			$reference 		= $_POST['reference'];
			$montant 		= $_POST['montant'];
			$cardNumber 	= $_POST['cardNumber'];
			$cardExpiry 	= $_POST['cardExpiry'];
			$cardCVC 		= $_POST['cardCVC'];
			$k 				= $_POST['k'];
			$old_stage 		= $_POST['old_stage'];
			$new_stage 		= $_POST['new_stage'];
			$prix 			= $_POST['prix'];
			$stage_price    = isset($_POST['stage_price']) ? (int) $_POST['stage_price'] : 0;
			paiement_cb($reference, $montant, $cardNumber, $cardExpiry, $cardCVC, $k, $old_stage, $new_stage, $prix, $stage_price);
		break;
		
		case 'demande_remboursement':
			$id_stagiaire = $_POST['id_stagiaire'];
			$motif = $_POST['motif'];
			demande_remboursement($id_stagiaire, $motif);
		break;
		
		case 'status_stagiaire':
			$id_stagiaire = $_POST['id_stagiaire'];
			status_stagiaire($id_stagiaire);
		break;

		case 'rappel_identifiants':
			$email = $_POST['email'];
			$ret = rappel_identifiants($email);
			echo $ret;
		break;

		case 'status_paiement':
			$id_stagiaire = $_POST['id_stagiaire'];
			status_paiement($id_stagiaire);
		break;			
		
		case 'enregistre_iban':
			$id_stagiaire = $_POST['id_stagiaire'];
			$iban = $_POST['iban'];
			$bic = $_POST['bic'];
			enregistre_iban($id_stagiaire, $iban, $bic);
		break;			
	}
}

function send_message_contact($id_stagiaire, $message) {
	
	require_once ("/home/prostage/www/mails_v3/mail_message_contact_stagiaire.php");
	mail_message_contact_stagiaire($id_stagiaire, $message);
}

function enregistre_iban($id_stagiaire, $iban, $bic) {
	
	include ("/home/prostage/connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	$iban = strtoupper($iban);
	$bic = strtoupper($bic);
	
	$sql = "UPDATE stagiaire SET iban='$iban', bic='$bic' WHERE id='$id_stagiaire'";
	$rs = mysql_query($sql, $stageconnect) or die(mysql_error());

	mysql_close($stageconnect);
}

function status_paiement($id_stagiaire) {
	
	include ("/home/prostage/connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);
	
	$sql = "SELECT 
				stagiaire.numtrans,
				stagiaire.numappel,
				stagiaire.ajout_paiement,
				transaction.id
			FROM
				stagiaire, transaction
			WHERE
				transaction.id_stagiaire = stagiaire.id AND
				stagiaire.id = $id_stagiaire";
	
	$rs = mysql_query($sql, $stageconnect);
    $row = mysql_fetch_assoc($rs);
	mysql_close($stageconnect);
	
	$reference = $row['id'];
	$numtrans = $row['numtrans'];
	$numappel = $row['numappel'];
	$ajout_paiement = intval($row['ajout_paiement']);
	
	if ($ajout_paiement) 
		echo "0";
	else {
		require_once("../../gae/functions.php");
		$ret = retour_consultation($reference, $numtrans, $numappel);
		echo $ret[0];
	}
}

function status_stagiaire($id_stagiaire) {
	
	include ("/home/prostage/connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);
	
	$sql = "SELECT 
				stagiaire.supprime,
				stagiaire.paiement,
				stagiaire.numtrans,
				stagiaire.remboursement,
				stagiaire.attente,
				stagiaire.attente_remboursement,
				stagiaire.presence_au_stage,
				stagiaire.validations_stagiaire,
				stagiaire.motif_annulation,
				stagiaire.opposition_cb,
				stage.date1
			FROM
				stagiaire, stage
			WHERE
				stagiaire.id_stage = stage.id AND
				stagiaire.id = $id_stagiaire";
	
	$rs = mysql_query($sql, $stageconnect);
    $row = mysql_fetch_assoc($rs);
    $total = mysql_num_rows($rs);
	mysql_close($stageconnect);
	
	$now = date('Y-m-d');
	$date1 = $row['date1'];
	$date2 = date('Y-m-d', strtotime($date1. ' + 1 days'));
	$supprime = $row['supprime'];
	$paiement = $row['paiement'];
	$remboursement = $row['remboursement'];
	$opposition_cb = intval($row['opposition_cb']);
	$motif_annulation = $row['motif_annulation'];
	$attente = $row['attente'];
	$attente_remboursement = $row['attente_remboursement'];
	$presence_au_stage = intval($row['presence_au_stage']);
	$numtrans = $row['numtrans'];
	$validations_stagiaire = $row['validations_stagiaire'];
	$validations_stagiaire_array = explode('|', $validations_stagiaire);
	$complet = 1;
	foreach($validations_stagiaire_array AS $val) 
		$complet *= intval($val);
	
	$enabled = false;
	
	$days_left = strtotime($now) - strtotime($date2);
	$days_left = number_format($days_left/86400 ,0);
	
	if ($presence_au_stage == 2 && $date2 < $now)
		$texte = "<span style='color:red'>Inscription annulée pour cause d'absence le jour du stage</span>";
	else if ($presence_au_stage == 3 && $date2 < $now)
		$texte = "<span style='color:red'>Inscription annulée - Vous êtes arrivé(e) en retard au stage</span>";
	else if ($presence_au_stage == 4 && $date2 < $now)
		$texte = "<span style='color:red'>Inscription annulée - Vous avez été exclu(e) du stage</span>";
	else if ($supprime == 0 && $paiement > 0 && $remboursement == 0 && $date2 >= $now) {
		$enabled = true;
		if (intval($complet))
			$texte = "<span style='color:green'>Dossier complet</span>";
		else
			$texte = "<span style='color:green'>Dossier en cours: inscrit ".$paiement." €</span>";
	}
	else if ($supprime == 1 && $paiement > 0 && $remboursement == 0 && strlen($numtrans) && !$attente_remboursement) {
		$enabled = true;
		$texte = "<span style='color:red'>Vous disposez d'un avoir de $paiement €. Utilisez cet avoir pour réserver une autre date en cliquant  sur le bouton “Réserver un stage“</span>";
	}
	else if ($supprime == 1 && $paiement > 0 && $opposition_cb == 1)
		$texte = "<span style='color:red'>Inscription annulée - Opposition CB (justifiée)</span>";
	else if ($supprime == 1 && $paiement > 0 && $opposition_cb == 2)
		$texte = "<span style='color:red'>Inscription annulée - Opposition CB (injustifiée)</span>";
	else if ($supprime == 1 && $paiement > 0 && $opposition_cb == 3)
		$texte = "<span style='color:red'>Inscription annulée - Opposition CB (volée)</span>";
	else if ($supprime == 1 && $paiement > 0 && $remboursement > 0)
		$texte = "<span style='color:red'>Inscription annulée - Remboursement effectué</span>";
	else if ($paiement > 0 && $remboursement == 0 && $attente_remboursement) {
		//$texte = "<span style='color:red'>Inscription annulée - Demande de remboursement en cours de traitement</span>";
		$texte = "<span style='color:red'>Inscription annulée</span>";
		$texte .= strlen($motif_annulation) ? "<span style='color:red'> (".$motif_annulation.")</span>" : "";
	}
	else if ($supprime > 0 && $paiement > 0 && $attente > 0) {
		$texte = "<span style='color:red'>Inscription annulée - Dossier en attente - Avoir de ".$paiement."€</span>";
		$texte .= strlen($motif_annulation) ? "<span style='color:red'> (".$motif_annulation.")</span>" : "";
	}
	else if ($supprime == 0 && $paiement > 0 && $remboursement == 0 && $days_left > 56) //56 = 8 semaines
		$texte = "<span style='color:green'>Dossier terminé : points visibles sur télépoints</span>";	
	else if ($supprime == 0 && $paiement > 0 && $remboursement == 0 && $date2 < $now)
		$texte = "<span style='color:green'>Dossier Terminé: points visibles sur télépoints prochainement</span>";
	else
		$texte = "";
	
	echo json_encode(array('enabled' => $enabled, 'texte' => $texte, 'attente_remboursement' => $attente_remboursement));
	
	//return json_encode($row);
}



function demande_remboursement($id_stagiaire, $select_motif_annulation) {
	
	include ("/home/prostage/connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);
	
	$today = date("Y-m-d H:i:s");
	
	$select_motif_annulation = htmlentities($select_motif_annulation);
	
	$sql = "SELECT
				stagiaire.supprime,
				stagiaire.id_externe AS id_stagiaire_externe,
				stage.id_externe AS id_stage_externe,
				stage.date1,
				stage.id,
				stage.id_membre
			FROM 
				stagiaire, stage 
			WHERE 
				stagiaire.id = '$id_stagiaire' AND
				stagiaire.id_stage = stage.id";
	$rs = mysql_query($sql, $stageconnect);
	$row = mysql_fetch_assoc($rs);
	$id_stagiaire_externe = $row['id_stagiaire_externe'];
	$id_stage_externe = $row['id_stage_externe'];
	$supprime = intval($row['supprime']);
	$date1 = $row['date1'];
	$id_stage = intval($row['id']);
	$id_membre = intval($row['id_membre']);
	
	$sql = "UPDATE 
				stagiaire 
			SET 
				attente_remboursement=1, 
				motif_annulation=\"$select_motif_annulation\",
				supprime = 1,
				date_suppression = '$today',
				date_demande_remboursement = '$today'
			WHERE 
				id=$id_stagiaire";
	mysql_query($sql, $stageconnect) or die(mysql_error());
	
	if ($supprime == 0) {
		$sql = "UPDATE stage SET nb_places_allouees = (nb_places_allouees + 1) WHERE id = $id_stage";
		mysql_query($sql, $stageconnect) or die(mysql_error());		
	}	
	
	mysql_close($stageconnect);
	
	if (($id_membre == 793) && ($supprime == 0)) {
		$resp = rppcAnnulation($id_stagiaire_externe, $id_stage_externe);
		if (intval($resp) == 0) {
			$message = "Rppc annulation simpligestion"; 
			$err_id1 = $id_stagiaire_externe;
			$err_id2 = $id_stage_externe." Date:".$date1;
			sendErrors($message, $err_id1, $err_id2);
		}
	}
	
	$type_interlocuteur = 1; //stagiaire
	$notifie = 1;
	$message = "Demande de remboursement. Motif: ".$select_motif_annulation;
	send_notification($type_interlocuteur, $id_stagiaire, $notifie, $message);
}

function rappel_identifiants($email) {
		
	//require_once("includes/functions.php");
	//require_once('class.phpmailer.php');
	
	include ("/home/prostage/connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);
	
	$email = trim($email);
	
	$sql = "SELECT id FROM stagiaire WHERE stagiaire.email LIKE '$email' AND paiement > 0 ORDER BY id DESC LIMIT 1";
	$rs = mysql_query($sql, $stageconnect) or die(mysql_error());
	$total = mysql_num_rows($rs);
	$row = mysql_fetch_assoc($rs);
	$id_stagiaire = $row['id'];	
	mysql_close($stageconnect);
	
	$key = 	md5($id_stagiaire .'!psp13#');
	$key = substr($key, 0, 5);
	
	$headers = "From: PROStagesPermis <contact@prostagespermis.fr>\n";
	$headers .= "Reply-To: \n";
	$headers .= 'MIME-version: 1.0'."\n";
	$headers .= 'Content-type: text/html; charset=utf-8'."\n";	
	
	$subject = "Rappel de vos identifiants";
	
	$msg = "Bonjour,<br><br>Voici un rappel de vos identifiants de connexion à votre <a href='https://www.prostagespermis.fr/es/login.php'>espace stagiaire</a>:<br>
	Identifiant: $id_stagiaire<br>
	Mot de passe: ".$key."
	
	<br><br>
	A bientôt.
	";

	if ($total) {
		mail($email, $subject, $msg, $headers);
		return 1;
	}
	else
		return 0;
}

function paiement_cb($reference, $montant, $cardNumber, $cardExpiry, $cardCVC, $k, $old_stage, $new_stage, $prix, $stage_price = 0) {
	
	require_once("/home/prostage/gae/functions.php");
	
	$md5 = md5($reference."psp1330#".$montant);
	//echo $old_stage."|".$new_stage;
	if ($md5 != $k) {
		$made = 0;
		$error_msg = "Problème de montant: contactez le service technique";
		echo json_encode(array('made'=>$made, 'error_msg'=>$error_msg));
	}
	else
	{
		$ret = autorisation_and_debit_no_dejainscrit($reference, $montant, $cardNumber, $cardExpiry, $cardCVC, $new_stage);
		
		if (intval($ret[0]) != 0)
			transfert($reference, $old_stage, $new_stage, $prix, $stage_price);
	
		echo json_encode(array('made'=>$ret[0], 'error_msg'=>utf8_encode($ret[1])));
	}
}

function remboursement_transfert_stage($reference, $old_stage, $new_stage, $numappel, $numtrans, $montant, $id_stagiaire) {
	
	require_once("/home/prostage/gae/functions.php");

	$reference 		= $_POST['reference'];
	$montant 		= intval($_POST['montant']);
	$numappel 		= $_POST['numappel'];
	$numtrans 		= $_POST['numtrans'];

	$ret = annulation_remboursement($reference, $montant, $numtrans, $numappel);
	
	if (intval($ret[0]) == 0) //erreur
		return 0;
	else {
		
		$type_interlocuteur = 1;
		$notifie = 1;
		$message = "Remboursement avant transfert de ".$montant." euros";
		send_notification($type_interlocuteur, $id_stagiaire, $notifie, $message);
		
		transfert($reference, $old_stage, $new_stage);
		
		return 1;
	}
	
	//echo json_encode(array('made'=>$ret[0], 'error_msg'=>utf8_encode($ret[1])));
}

function transfert($reference, $old_stage, $new_stage, $prix, $stage_price = 0) {
    include ("/home/prostage/connections/stageconnect.php");
    include '../modules/module.php';

    $log    =   new \Core\Log();

    $today = date('Y-m-d');
	$error = 0;
	$msg = "";

	mysql_select_db($database_stageconnect, $stageconnect);
	
	//recuperation donnnées du nouveau stage
	$sql = "SELECT 
				stage.id_membre,
				stage.id_externe,
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
	$new_prix = $prix;
	$new_membre = intval($row['id_membre']);
	$new_date = $row['date1'];
	$new_cp = $row['code_postal'];
	$new_ville = $row['ville'];
	$id_new_stage_externe = $row['id_externe'];
	
	$sql = "SELECT id_stagiaire, autorisation FROM transaction WHERE id = '$reference'";
	$rs = mysql_query($sql, $stageconnect) or die(mysql_error());
	$row = mysql_fetch_assoc($rs);
	$id_stagiaire = $row['id_stagiaire'];
	$autorisation = $row['autorisation'];

	//recuperation donnnées ancien stage et stagiaire
	$sql = "SELECT 
				stage.id_membre, 
				stage.id_externe AS stage_externe,
				stage.prix,
				stage.date1,
				site.code_postal,
				site.ville,
				stagiaire.paiement, 
				stagiaire.remboursement,
				stagiaire.supprime,
				stagiaire.attente,
				stagiaire.id_externe AS stagiaire_externe,
				stagiaire.numappel, 
				stagiaire.numtrans, 
				stagiaire.numero_cb,
				stagiaire.provenance_suppression
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
	$id_stagiaire_externe = $row['stagiaire_externe'];
	$numappel = $row['numappel'];
	$numtrans = $row['numtrans'];
	$numero_cb = $row['numero_cb'];
	$attente = $row['attente'];
	$provenance_suppression = $row['provenance_suppression'];
	$id_old_stage_externe = $row['stage_externe'];
	
	if ((intval($old_stage) == intval($new_stage)) && (intval($old_supprime) == 0)) {
		$error = 1;
		$msg = "Vous êtes déjà inscrit à ce stage";
		return array($error, $msg);
	}
	
	
	$sql = "SELECT id FROM transaction WHERE id_stagiaire = '$id_stagiaire' ORDER BY id DESC LIMIT 1";
	$rs = mysql_query($sql, $stageconnect) or die(mysql_error());
	$total = mysql_num_rows($rs);
	if (!$total) {
		$error = 1;
		$msg = "Transaction introuvable: contactez le service technique";
		return array($error, $msg);
	}	
	
	if ($old_membre != $new_membre) {
		require_once ("/home/prostage/www/mails_v3/mail_annulation_centre.php");
		
		if (!$attente && intval($provenance_suppression) == 0)
			mail_annulation_centre($id_stagiaire);
		
		if ($old_membre == 793) {
			$ret = rppcAnnulation($id_stagiaire_externe, $id_old_stage_externe);
			if (intval($ret) == 0) {
				$message = "Rppc annulation"; 
				$err_id1 = $id_stagiaire_externe;
				$err_id2 = $id_old_stage_externe;
				$err_id3 = $old_date;
				sendErrors($message, $err_id1, $err_id2, $err_id3);
			}
		}
	}
	
	//update transaction
	$sql = "UPDATE 
				transaction 
			SET 
				id_stage = '$new_stage', 
				id_membre = '$new_membre', 
				type_paiement = 'CB_OK'
			WHERE 
				id = '$reference'";
	mysql_query($sql, $stageconnect) or die(mysql_error());
	
	
	$ajout_paiement = 0;
	if ($old_prix < $new_prix)
		$ajout_paiement = intval($new_prix) - intval($old_prix);

	$price_transfer = 0;

	if ($stage_price > 0 && $stage_price < $new_prix) {
        $price_transfer = $new_prix - $stage_price;
    }

	//update stagiaire
	$sql = "UPDATE 
				stagiaire 
			SET 
				id_stage = '$new_stage',
				status = 'inscrit',				
				supprime = 0,
				attente = 0,		
				remboursement = 0, 
				date_demande_remboursement = NULL,
				attente_remboursement = 0,
				provenance_suppression = 0,
				paiement = '$new_prix',
			    price_transfer = '$price_transfer',
				ajout_paiement = '$ajout_paiement'
			WHERE 
				id = '$id_stagiaire'";
	mysql_query($sql, $stageconnect) or die(mysql_error());
	
	if ($provenance_suppression == 1) //supprimé par le centre
		mysql_query("UPDATE stagiaire SET date_inscription = '$today' WHERE id = '$id_stagiaire'", $stageconnect) or die(mysql_error());
	
	//gestion du dossier des documents téléchargés
	//if ($old_membre == 837 && $new_membre == 837)
	move_files($old_date, $new_date, $old_stage, $new_stage, $id_stagiaire);

	if ($old_membre != $new_membre) {
		
		require_once("/home/prostage/gae/functions.php");
		validation_inscription($reference, $autorisation, $numappel, $numtrans, $numero_cb); //decompte de place intégré
		
		$sql = "UPDATE stage SET nb_places_allouees = (nb_places_allouees + 1) WHERE id = '$old_stage'";
		mysql_query($sql, $stageconnect) or die(mysql_error());		
	}
	else {
		require_once ("/home/prostage/www/mails_v3/mail_transfert_stagiaire.php");
		mail_transfert_stagiaire($id_stagiaire); //le centre recoit une copie

		$sql = "UPDATE stage SET nb_places_allouees = (nb_places_allouees + 1) WHERE id = '$old_stage'";
		mysql_query($sql, $stageconnect) or die(mysql_error());	

		$sql = "UPDATE stage SET nb_places_allouees = (nb_places_allouees - 1) WHERE id = '$new_stage'";
		mysql_query($sql, $stageconnect) or die(mysql_error());	

		if ($new_membre == 793) {
			$ret = rppcTransfert($id_stagiaire_externe, $id_new_stage_externe, $new_prix);
			//sendErrors($id_stagiaire_externe, $id_new_stage_externe, $new_prix);
			if (intval($ret) == 0) {
				$message = "Rppc transfert"; 
				$err_id1 = $id_stagiaire_externe;
				$err_id2 = $id_new_stage_externe;
				sendErrors($message, $err_id1, $err_id2);
			}
		}
	}

    $log->info('Transfert de stage', 'transfer',LOGS . DS . 'transfer.log', false,
        [
            'id_stagiaire' => $id_stagiaire,
            'old_stage' => $old_stage,
            'new_stage' => $new_stage,
            'prix' => $new_prix,
            'prix_transfert' => $price_transfer,
            'ajout_paiement' => $ajout_paiement
        ]
    );


    mysql_close($stageconnect);
	
	//envoie du mail de transfert
	//mail_transfert($reference, $new_stage);

	//archivage historique
	$type_interlocuteur = 1; //stagiaire
	$notifie = 0;
	$old_date_text = date("d-m-Y", strtotime($old_date));
	$new_date_text = date("d-m-Y", strtotime($new_date));
	$message = "Transfert de stage: ";
	$message .= "Ancien: $old_stage - $old_date_text $old_cp $old_ville - Prix:$old_paiement € <br> => Nouveau: $new_stage - $new_date_text $new_cp $new_ville - Prix:$new_prix €";
	send_notification($type_interlocuteur, $id_stagiaire, $notifie, $message);	

}

function rppcTransfert($id_stagiaire_externe, $id_stage_externe, $new_prix) {
	
	$url = 'https://www.recupererpoint.com/prostagespermis/changement_stagiaire.php';
	$api_key = "PSP-RPPC-12011967";
	
	$description_client = array("id_stagiaire" => $id_stagiaire_externe, "id_stage"=> $id_stage_externe, "prix" => $new_prix);	
	
	$post_data = object_to_params("changement", $description_client);
	$post_data["api_key"] = $api_key;
	$response = post_to_remote($url, $post_data, "POST");
	
	return intval($response);
}

function rppcAnnulation($id_stagiaire_externe, $id_stage_externe) {
	
	$url = 'https://www.recupererpoint.com/prostagespermis/annulation_stagiaire.php';
	$api_key = "PSP-RPPC-12011967";
	
	$description_client = array("id_stagiaire" => $id_stagiaire_externe, "id_stage"=> $id_stage_externe);	
	
	$post_data = object_to_params("annulation", $description_client);
	$post_data["api_key"] = $api_key;
	$response = post_to_remote($url, $post_data, "POST");

	return intval($response);
}

function object_to_params($object_name, $fields)
{
	$o = array();

	foreach($fields as $k => $v) {
		if (is_array($v)) {
			foreach($v as $k1=>$v1) {
				$f = $object_name."[".$k."][$k1]";
				$o[$f] = $v1;
			}
		} else {
			$f = $object_name."[".$k."]";
			$o[$f] = $v;
		}
	}
	return $o;
}

function post_to_remote($url, $params, $method="GET")
{
		if ($method=="GET") {
	    	$curl = curl_init($url."?".http_build_query($params));
		} else {
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
		}
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	    curl_setopt($curl, CURLOPT_TIMEOUT, 10);

	    $contents = curl_exec($curl);
	    $returnInfo = curl_getinfo($curl);

	    if($returnInfo['http_code'] === 200)
	    {
	        return $contents;
	    }

	    return false;
}

function sendErrors($message, $err_id1=NULL, $err_id2=NULL, $err_id3=NULL)
{
	$dateLocal = date("d-m-Y");

	$contact = "hakayari@yahoo.fr";
	$subject = "Alerte: Erreur technique";

	$headers = "From: PROStagesPermis <contact@prostagespermis.fr>\n";
	$headers .= "Reply-To: ".$contact."\n";
	$headers .= 'MIME-version: 1.0'."\n";
	$headers .= 'Content-type: text/html; charset= iso-8859-1'."\n";

	$contenu  = "<pre style=\"font-size:12px\">";
	$contenu .= "Erreur: ".$message;
	$contenu .= "<br><br>";
	$contenu .= $err_id1."<br>";
	$contenu .= $err_id2."<br>";
	$contenu .= $err_id3."<br>";
	$contenu .= "</pre>";

	mail($contact, $subject, $contenu, $headers);
}

function mail_transfert($reference, $new_stage) {

	require_once("includes/functions.php");
	require_once('class.phpmailer.php');
	
	include ("/home/prostage/connections/stageconnect.php");
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
	
	$stagiaire_status 		= $row['stagiaire_status'];
	$stagiaire_civilite 	= $row['stagiaire_civilite'];
	$stagiaire_nom 			= utf8_encode($row['stagiaire_nom']);
	$stagiaire_prenom 		= utf8_encode($row['stagiaire_prenom']);
	$stagiaire_tel 			= $row['stagiaire_tel'];
	$stagiaire_mobile 		= $row['stagiaire_mobile'];
	$stagiaire_email 		= $row['stagiaire_email'];
	$stagiaire_jeune_fille 	= utf8_encode($row['stagiaire_jeune_fille']);
	$stagiaire_date_naissance = $row['stagiaire_date_naissance'];
	$stagiaire_lieu_naissance = utf8_encode($row['stagiaire_lieu_naissance']);
	$stagiaire_adresse 		= utf8_encode($row['stagiaire_adresse']);
	$stagiaire_code_postal 	= $row['stagiaire_code_postal'];
	$stagiaire_ville 		= utf8_encode($row['stagiaire_ville']);
	$stagiaire_num_permis 	= $row['stagiaire_num_permis'];
	$stagiaire_date_permis 	= $row['stagiaire_date_permis'];
	$stagiaire_lieu_permis 	= utf8_encode($row['stagiaire_lieu_permis']);
	$stagiaire_cas 			= $row['stagiaire_cas'];
	$stagiaire_paiement 	= $row['stagiaire_paiement'];
	$stagiaire_motif_infraction = utf8_encode($row['stagiaire_motif_infraction']);
	$stagiaire_date_infraction = $row['stagiaire_date_infraction'];
	$stagiaire_date_lettre 	= $row['stagiaire_date_lettre'];	
	$membre_id 				= $row['membre_id'];
	$membre_nom 			= utf8_encode($row['membre_nom']);
	$membre_adresse 		= utf8_encode($row['membre_adresse']);
	$membre_tel 			= $row['membre_tel'];
	$membre_mobile 			= $row['membre_mobile'];
	$membre_fax 			= $row['membre_fax'];
	$membre_email 			= $row['membre_email'];
	$type_paiement 			= $row['type_paiement'];
	$stage_date1 			= $row['stage_date1'];
	$stage_date2 			= date('Y-m-d', strtotime($stage_date1. ' + 1 days'));
	$stage_debut_am			= $row['stage_debut_am'];
	$stage_fin_am 			= $row['stage_fin_am'];
	$stage_debut_pm 		= $row['stage_debut_pm'];
	$stage_fin_pm 			= $row['stage_fin_pm'];	
	$site_nom 				= utf8_encode($row['site_nom']);
	$site_adresse 			= utf8_encode($row['site_adresse']);
	$site_code_postal 		= $row['site_code_postal'];
	$site_ville 			= utf8_encode($row['site_ville']);
	$contact 				= "contact@prostagespermis.fr";
        
	$subject = "Transfert de stage: ".$stagiaire_nom." ".$stagiaire_prenom;
	
	$msg = "<h1 style=\"font-size: 22px;font-family:'MV Boli',Arial;color: #E95B61;line-height: 20px;padding: 40px 5px 40px 150px;margin: 0;text-align: center;\">Détails de votre nouveau stage !</h1>";

	$msg .= "<p>Vous venez d'être transféré sur un nouveau stage de récupération de points dont les informations
	complètes figurent ci-dessous. Nous sommes désolés pour l'éventuelle gène occasionnée</p>.";


	$msg .= "<h3>CENTRE ORGANISATEUR:</h3>";
	$msg .= "<p>".$membre_nom."</p>";
	$msg .= "<p>".$membre_adresse."</p>";
	$msg .= "<p>".$membre_tel." ".$membre_mobile."</p>";
	$msg .= "<p>".$membre_email." ".$membre_fax."</p>";
	
	$msg .= "<h3>STAGIAIRE:</h3>";
	$msg .= "<p>".$stagiaire_civilite." ".$stagiaire_nom." ".$stagiaire_prenom."</p>";
	if (isset($stagiaire_jeune_fille) && strlen($stagiaire_jeune_fille))
		$msg .= "<p>Nom jeune fille: ".$stagiaire_jeune_fille."</p>";
	$msg .= "<p>".$stagiaire_adresse."</p>";
	$msg .= "<p>".$stagiaire_code_postal." ".$stagiaire_ville."</p>";
	$msg .= "<p>".$stagiaire_status."</p>";
	$msg .= "<p>".$stagiaire_tel." ".$stagiaire_mobile." ".$stagiaire_email."</p>";
	$msg .= "<p>Né(e) le ".$stagiaire_date_naissance." à ".$stagiaire_lieu_naissance."</p>";
	$msg .= "<p>Permis N° ".$stagiaire_num_permis." le ".$stagiaire_date_permis." à ".$stagiaire_lieu_permis."</p>";
	$msg .= "<p>Paiement: ".$stagiaire_paiement."</p>";

	$msg .= "<h3>DETAILS DU NOUVEAU STAGE:</h3>";
	$msg .= "<p>".MySQLDateToExplicitDate($stage_date1)." et ".MySQLDateToExplicitDate($stage_date2)."</p>";
	$msg .= "<p>".$stage_debut_am." ".$stage_fin_am." et ".$stage_debut_pm." ".$stage_fin_pm."</p>";
	$msg .= "<p>".$site_adresse."</p>";
	$msg .= "<p>".$site_code_postal." ".$site_code_ville."</p>";
	$msg .= "<p>Prix: ".$stagiaire_paiement." euros</p>";
	
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

function move_files($date_old, $date_new, $id_stage_old, $id_stage_new, $id_stagiaire)
{
	require_once ("/home/prostage/www/stages/functions.php");

	$dossier_old = "/home/prostage/www/stages/mois/".date('Ym', strtotime($date_old))."/".$id_stage_old;
	$dossier_new = "/home/prostage/www/stages/mois/".date('Ym', strtotime($date_new))."/".$id_stage_new;
	
	if(!is_dir($dossier_new))
		mkdir($dossier_new, 0777, true);

	$documents = listDocumentsStagiaire($id_stage_old, $date_old, $id_stagiaire);

	foreach($documents AS $document) 
	{	
		$old = $dossier_old."/".$document;
		$new = $dossier_new."/".$document;
		
		rename($old, $new);
	}
}

function affiche_formulaire_paiement($id_stage, $id_stagiaire, $prix, $stage_price = 0) {
	include "../modules/module.php";
	$form   =   new \App\Components\PaymentForm($id_stage, $id_stagiaire, $prix, $stage_price);
	echo $form->display();
}

function send_notification($type_interlocuteur, $id_interlocuteur, $notifie, $message) {
	
	require_once ("/home/prostage/common_bootstrap2/notifications.php");
	
	$type_destinataire = 1;
	$id = notification($type_interlocuteur, $id_interlocuteur, $type_destinataire, $notifie, $message);
	return $id;
}

function annulation_inscription($id_stagiaire) {
	
	include ("/home/prostage/connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);
	
	$sql = "SELECT
				stagiaire.supprime,
				stagiaire.id_externe AS id_stagiaire_externe,
				stage.id_externe AS id_stage_externe,
				stage.date1,
				stage.id,
				stage.id_membre
			FROM 
				stagiaire, stage 
			WHERE 
				stagiaire.id = '$id_stagiaire' AND
				stagiaire.id_stage = stage.id";
	$rs = mysql_query($sql, $stageconnect);
	$row = mysql_fetch_assoc($rs);
	$id_stagiaire_externe = $row['id_stagiaire_externe'];
	$id_stage_externe = $row['id_stage_externe'];
	$supprime = intval($row['supprime']);
	$date1 = $row['date1'];
	$id_stage = intval($row['id']);
	$id_membre = intval($row['id_membre']);
	
	$sql = "UPDATE stagiaire SET supprime=1, attente=1, attente_remboursement=0 WHERE id = $id_stagiaire";
	mysql_query($sql, $stageconnect) or die(mysql_error());	
	
	mysql_close($stageconnect);
	
	if (($id_membre == 793) && ($supprime == 0)) {
		$resp = rppcAnnulation($id_stagiaire_externe, $id_stage_externe);
		if (intval($resp) == 0) {
			$message = "Rppc annulation simpligestion"; 
			$err_id1 = $id_stagiaire_externe;
			$err_id2 = $id_stage_externe." Date:".$date1;
			sendErrors($message, $err_id1, $err_id2);
		}
	}
	
	$type_interlocuteur = 1; //stagiaire
	$notifie = 1;
	$message = "Inscription mise en attente";
	send_notification($type_interlocuteur, $id_stagiaire, $notifie, $message);	
}

function update_validations_stagiaire($id_stagiaire, $accordeon) {

	require_once ("/home/prostage/www/stages/functions.php");
	
	include ("/home/prostage/connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);
	
	$sql = "SELECT * FROM stagiaire WHERE id = $id_stagiaire";
	$rs = mysql_query($sql, $stageconnect) or die(mysql_error());
	$row_stagiaire = mysql_fetch_assoc($rs);
	
	$sql = "SELECT stage.id, stage.date1 FROM stage, stagiaire WHERE stagiaire.id_stage = stage.id AND stagiaire.id = $id_stagiaire";
	$rs_stage = mysql_query($sql, $stageconnect) or die(mysql_error());
	$row_stage = mysql_fetch_assoc($rs_stage);
	$id_stage = $row_stage['id'];
	$date_stage = $row_stage['date1'];
	
	$error = false;
	//verification
	if ($accordeon == 1) {
		if (!strlen($row_stagiaire['civilite']) ||
			!strlen($row_stagiaire['nom']) ||
			!strlen($row_stagiaire['prenom']) ||
			!strlen($row_stagiaire['adresse']) ||
			!strlen($row_stagiaire['code_postal']) ||
			!strlen($row_stagiaire['ville']) ||
			!strlen($row_stagiaire['date_naissance']) ||
			!strlen($row_stagiaire['lieu_naissance']) ||
			(!strlen($row_stagiaire['tel']) && !strlen($row_stagiaire['mobile'])) ||
			!strlen($row_stagiaire['email']))
			
			$error = true;
	}
	else if ($accordeon == 2) {
		
		if (intval($row_stagiaire['etat_permis']) == 1) { //valide
			if (!intval($row_stagiaire['type_permis']) ||
				!strlen($row_stagiaire['num_permis']) ||
				//!strlen($row_stagiaire['date_obtention_permis']) ||
				!strlen($row_stagiaire['date_permis']) ||
				!strlen($row_stagiaire['lieu_permis']) ||
				!strlen($row_stagiaire['points_restant']))
				
				$error = true;			
		}
		
		else if (intval($row_stagiaire['etat_permis']) == 2) { //retention
			if (!strlen($row_stagiaire['num_permis']) ||
				!strlen($row_stagiaire['date_permis']) ||
				!strlen($row_stagiaire['lieu_permis']) ||
				!intval($row_stagiaire['permis_probatoire']) ||
				!strlen($row_stagiaire['points_restant']))
				
				$error = true;			
		}
		
		else if (intval($row_stagiaire['etat_permis']) == 3) { //suspension
			if (!strlen($row_stagiaire['num_permis']) ||
				!strlen($row_stagiaire['date_permis']) ||
				!strlen($row_stagiaire['lieu_permis']) ||
				!intval($row_stagiaire['permis_probatoire']) ||
				!strlen($row_stagiaire['points_restant']))
				
				$error = true;			
		}
		
		else if (intval($row_stagiaire['etat_permis']) == 4) { //annule ou invalide
				
			if (!intval($row_stagiaire['reception_48SI']))				
				$error = true;			
		}
		
		else if (intval($row_stagiaire['etat_permis']) == 5) { //jamais obtenu
				
				$error = false;			
		}
		
		else if (intval($row_stagiaire['etat_permis']) == 6) { //perdu
				
			if (!strlen($row_stagiaire['num_permis']) ||
				!strlen($row_stagiaire['date_permis']) ||
				!strlen($row_stagiaire['lieu_permis']) ||
				!intval($row_stagiaire['permis_probatoire']) ||
				!strlen($row_stagiaire['points_restant']))
				
				$error = true;			
		}
		
	}
	else if ($accordeon == 3) {
		/*if (($row_stagiaire['etat_permis'] == 4 || $row_stagiaire['etat_permis'] == 3) &&
			($row_stagiaire['cas'] == 1 || $row_stagiaire['cas'] == 2))
			
			$error = true;*/
		1;
	}
	else if ($accordeon == 4) {
		
		$missings = documentsMissing($id_stage, $date_stage, $id_stagiaire, $row_stagiaire['cas'], $row_stagiaire['etat_permis'], $row_stagiaire['solde_nul'], $row_stagiaire['date_48n']);
		
		if (count($missings))
			$error = true;
	}
	
	$validations_stagiaire = $row_stagiaire['validations_stagiaire'];
	$tab = explode("|", $validations_stagiaire);
	
	if (!strlen($row_stagiaire['validations_stagiaire'])) {
		$tab[0] = 0;
		$tab[1] = 0;
		$tab[2] = 0;
		$tab[3] = 0;
	}
	
	$tab[$accordeon - 1] = $error ? 0 : 1;
	$validations_stagiaire = implode("|", $tab);
	
	$sql = "UPDATE stagiaire SET validations_stagiaire = '$validations_stagiaire' WHERE id = $id_stagiaire";
	$rs = mysql_query($sql, $stageconnect) or die(mysql_error());
	
	mysql_close($stageconnect);
	
	if ($error)	echo "0";
	else echo "1";
}

function update_sous_cat_etat_permis($new_etat_permis, $id_stagiaire, $echo) {
	
	include ("/home/prostage/connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);
	$sql = "SELECT 
				type_permis,
				num_permis,
				date_permis,
				date_obtention_permis,
				lieu_permis,
				points_restant,
				permis_probatoire,
				reception_48SI
			FROM 
				stagiaire 
			WHERE 
				id = '$id_stagiaire'";
	$rs = mysql_query($sql, $stageconnect) or die(mysql_error());
	$row = mysql_fetch_assoc($rs);
	mysql_close($stageconnect);
	
	$type_permis = $row['type_permis'];
	$num_permis = $row['num_permis'];
	$date_permis = $row['date_permis'];
	$date_permis_format = date("d-m-Y", strtotime($date_permis));
	$date_permis_format = $date_permis_format == '01-01-1970' ? "" : $date_permis_format;
	$date_obtention_permis = $row['date_obtention_permis'];
	$date_obtention_permis = $date_obtention_permis == '01-01-1970' ? "" : $date_obtention_permis;
	$lieu_permis = $row['lieu_permis'];
	$points_restant = $row['points_restant'];
	$permis_probatoire = $row['permis_probatoire'];
	$reception_48SI = $row['reception_48SI'];
		
	$c = "<table class='infos_stagiaire'>";
	switch ($new_etat_permis) {
		case 1:
			$c .= "<tr>";
			$c .= "<th>Type de permis</th>";
			$c .= "<td>";
			$c .= "<a href='#' data-name='stagiaire|type_permis' id='type_permis' data-type='select' data-pk='$id_stagiaire' data-value='$type_permis'></a>";
			$c .= "</td>";
			$c .= "</tr>";
			
			$c .= "<tr>";
			$c .= "<th>Numero de permis</th>";
			$c .= "<td>";
			$c .= "<a href='#' data-name='stagiaire|num_permis' class='editable_text' data-pk='$id_stagiaire'>".$num_permis."</a>";
			$c .= "</td>";
			$c .= "</tr>";
			
			/*
			$c .= "<tr>";
			$c .= "<th>Date d'obtention</th>";
			$c .= "<td>";
			$c .= "<a href='#' data-value='<?php echo $date_obtention_permis; ?>' data-type='combodate' data-name='stagiaire|date_obtention_permis' class='editable_date' data-pk='$id_stagiaire'>".$date_obtention_permis_format."</a>";
			$c .= "</td>";
			$c .= "</tr>";
			*/
			
			$c .= "<tr>";
			$c .= "<th>Date de delivrance</th>";
			$c .= "<td>";
			$c .= "<a href='#' data-value='<?php echo $date_permis; ?>' data-type='combodate' data-name='stagiaire|date_permis' class='editable_date' data-pk='$id_stagiaire'>".$date_permis_format."</a>";
			$c .= "</td>";
			$c .= "</tr>";
			
			$c .= "<tr>";
			$c .= "<th>Prefecture de delivrance</th>";
			$c .= "<td>";
			$c .= "<a href='#' data-value='$lieu_permis' data-name='stagiaire|lieu_permis' class='editable_text' data-pk='$id_stagiaire'>".$lieu_permis."</a>";
			$c .= "</td>";
			$c .= "</tr>";
			
			$c .= "<tr>";
			$c .= "<th>Nombre de points restant</th>";
			$c .= "<td>";
			$c .= "<a href='#' data-name='stagiaire|points_restant' id='points_restant' data-type='select' data-pk='$id_stagiaire' data-value='$points_restant'></a>";
			$c .= "</td>";
			$c .= "</tr>";
		break;
		
		case 2:
		case 3:
		case 6:
			$c .= "<tr>";
			$c .= "<th>Numero de permis</th>";
			$c .= "<td>";
			$c .= "<a href='#' data-name='stagiaire|num_permis' class='editable_text' data-pk='$id_stagiaire'>".$num_permis."</a>";
			$c .= "</td>";
			$c .= "</tr>";
			
			$c .= "<tr>";
			$c .= "<th>Date de delivrance du permis</th>";
			$c .= "<td>";
			$c .= "<a href='#' data-value='<?php echo $date_permis; ?>' data-type='combodate' data-name='stagiaire|date_permis' class='editable_date' data-pk='$id_stagiaire'>".$date_permis."</a>";
			$c .= "</td>";
			$c .= "</tr>";
			
			$c .= "<tr>";
			$c .= "<th>Prefecture de delivrance</th>";
			$c .= "<td>";
			$c .= "<a href='#' data-value='$lieu_permis' data-name='stagiaire|lieu_permis' class='editable_text' data-pk='$id_stagiaire'>".$lieu_permis."</a>";
			$c .= "</td>";
			$c .= "</tr>";
			
			$c .= "<tr>";
			$c .= "<th>Etes vous conducteur en permis probatoire ?</th>";
			$c .= "<td>";
			$c .= "<a href='#' data-name='stagiaire|permis_probatoire' id='permis_probatoire' data-type='select' data-pk='$id_stagiaire' data-value='$permis_probatoire'></a>";
			$c .= "</td>";
			$c .= "</tr>";
			
			$c .= "<tr>";
			$c .= "<th>Nombre de points restant</th>";
			$c .= "<td>";
			$c .= "<a href='#' data-name='stagiaire|points_restant' id='points_restant' data-type='select' data-pk='$id_stagiaire' data-value='$points_restant'></a>";
			$c .= "</td>";
			$c .= "</tr>";
		break;
		
		case 4:
			$c .= "<tr>";
			$c .= "<th>Avez-vous receptionne la lettre recommandee reference 48SI<br>(permis invalide pour solde nul) ?</th>";
			$c .= "<td>";
			$c .= "<a href='#' data-name='stagiaire|reception_48SI' id='reception_48SI' data-type='select' data-pk='$id_stagiaire' data-value='$reception_48SI'></a>";
			$c .= "</td>";
			$c .= "</tr>";		
		break;
		
		case 5:
		break;
	}
	$c .= "</table>";
	
	if ($echo)
		echo $c;
	else
		return $c;
}

function supprime_prefere($id_formateur, $id_prefere, $prefere) {

	include ("/home/prostage/connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	$sql = "DELETE FROM formateur_prefere WHERE id = $id_prefere";
	$rs = mysql_query($sql, $stageconnect) or die(mysql_error());
	
	$sql = "SELECT formateur_prefere, formateur_a_eviter FROM formateur WHERE id = $id_formateur";
	$rs = mysql_query($sql, $stageconnect) or die(mysql_error());	
	$row = mysql_fetch_assoc($rs);	
	$formateur_prefere = $row['formateur_prefere'];
	$formateur_a_eviter = $row['formateur_a_eviter'];
	
	$formateurs = $prefere ? $formateur_prefere : $formateur_a_eviter;
	
	$liste = array();
	if (strlen($formateurs)) {
		$splits = explode("|", $formateurs);
		if (count($splits)) {		
			foreach ($splits AS $split) {
				if (intval($split) != intval($id_prefere))
					$liste[] = $split;
			}
		}
		
		$formateurs = implode("|", $liste);
		if ($prefere)
			$sql = "UPDATE formateur SET formateur_prefere = '$formateurs' WHERE id = $id_formateur";
		else
			$sql = "UPDATE formateur SET formateur_a_eviter = '$formateurs' WHERE id = $id_formateur";
		$rs = mysql_query($sql, $stageconnect) or die(mysql_error());
	}

	mysql_close($stageconnect);	
}

function ajout_animateur($id_formateur, $nom, $prenom, $ville, $tel, $email, $prefere) {
	
	include ("/home/prostage/connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	$sql = "INSERT INTO formateur_prefere (nom, prenom, ville, tel, email) VALUES ('$nom', '$prenom', '$ville', '$tel', '$email')";
	echo $sql;
	$rs = mysql_query($sql, $stageconnect) or die(mysql_error());	
	$id = mysql_insert_id();
	
	if ($prefere)
		$sql = "UPDATE formateur SET formateur_prefere = concat(formateur_prefere, '|$id') WHERE id = $id_formateur";
	else
		$sql = "UPDATE formateur SET formateur_a_eviter = concat(formateur_a_eviter, '|$id') WHERE id = $id_formateur";
	$rs = mysql_query($sql, $stageconnect) or die(mysql_error());	

	mysql_close($stageconnect);		
}

function supprime_lieu_intervention($id_site, $id_formateur) {

	include ("/home/prostage/connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	$sql = "SELECT perimetre_intervention FROM formateur WHERE id = $id_formateur";
	$rs = mysql_query($sql, $stageconnect) or die(mysql_error());	
	$row = mysql_fetch_assoc($rs);	
	$perimetre_intervention = $row['perimetre_intervention'];
	
	$liste = array();
	if (strlen($perimetre_intervention)) {
		$splits = explode("|", $perimetre_intervention);
		if (count($splits)) {		
			foreach ($splits AS $split) {
				$tab = explode(",", $split);
				if (intval($tab[0]) != intval($id_site))
					$liste[] = $tab[0].",".$tab[1];
			}
		}
		
		$perimetre_intervention = implode("|", $liste);
		$sql = "UPDATE formateur SET perimetre_intervention = '$perimetre_intervention' WHERE id = $id_formateur";
		$rs = mysql_query($sql, $stageconnect) or die(mysql_error());
	}

	mysql_close($stageconnect);	
}

function update_etape($id_stage, $etape) {
	
	include ("/home/prostage/connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	$item = "etape".$etape;
	$sql = "UPDATE stage SET ".$item."=1 WHERE id=$id_stage";
	$rs = mysql_query($sql, $stageconnect) or die(mysql_error());

	mysql_close($stageconnect);
}

function update_notification($id_notification, $id_centre) {
	
	include ("/home/prostage/connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	$sql = "UPDATE notifications SET id_centre = '$id_centre' WHERE id = $id_notification";
	$rs = mysql_query($sql, $stageconnect) or die(mysql_error());

	mysql_close($stageconnect);
}

function indisponible($id_stage, $id_formateur) {
		
	$url = "http://www.prostagespermis.fr/planificateur_tache/confirmation_formateur.php?annule&id_stage=".$id_stage."&id_formateur=".$id_formateur;
	
	$username = "prostagespermis";
	$pass = "maxin4567";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_USERPWD, "$username:$pass");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_NOBODY, 0);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	$result = curl_exec($ch);

	curl_close($ch);
}

function confirmation_animation($id_stage, $formation) {

	include ("/home/prostage/connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	if ($formation == "bafm") {	
		
		$sql = "UPDATE stage SET confirmation_bafm = 1 WHERE id = $id_stage";
		mysql_query($sql, $stageconnect);				
	}
	else if ($formation == "psy") {
		
		$sql = "UPDATE stage SET confirmation_psy = 1 WHERE id = $id_stage";
		mysql_query($sql, $stageconnect);
	}

	mysql_close($stageconnect);
}

function update_horaires_pedagogiques($id_stage, $horaires_pedagogiques) {
	
	include ("/home/prostage/connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	$sql = "UPDATE stage SET horaires_pedagogiques = '$horaires_pedagogiques' WHERE id=$id_stage";
	$rs = mysql_query($sql, $stageconnect) or die(mysql_error());

	mysql_close($stageconnect);
}

function editable($id, $name, $value) {
	
	//header('Content-Type: text/html; utf-8');

	$split = explode("|", $name);
	$table = $split[0];
	$field = $split[1];

	include ("/home/prostage/connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	if ($value == "NULL") //pas de quotes pour une valeur à NULL
		$sql = "UPDATE ".$table." SET ".$field."=".$value." WHERE id = ".$id;
	else {
		$value = utf8_encode($value);
		$sql = "UPDATE ".$table." SET ".$field."='".$value."' WHERE id = ".$id;
	}

	mysql_query($sql);
	mysql_close($stageconnect);
	
	if ($field == 'etat_permis')
		echo update_sous_cat_etat_permis($value, $id, false);

	if ($table == 'stagiaire') {
		require ("/home/prostage/soap/rppc/update_stagiaire_rppc.php");
		update_stagiaire_rppc($id, $field, $value);
	}
}

function update_cas($id, $name, $value, $selects) {
	
	header('Content-Type: text/html; charset=ISO-8859-15');

	$split = explode("|", $name);
	$table = $split[0];
	$field = $split[1];

	include ("/home/prostage/connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	if ($value == "NULL") //pas de quotes pour une valeur à NULL
		$sql = "UPDATE ".$table." SET ".$field."=".$value." WHERE id = ".$id;
	else
		$sql = "UPDATE ".$table." SET ".$field."='".$value."' WHERE id = ".$id;

	mysql_query($sql);
	mysql_close($stageconnect);
	
	echo update_sous_cat_cas($value, $selects, $id);
}

function espace_stagiaire_documents_a_recuperer($id_stagiaire) {

	$c = "<div class=\"row\"><input type='checkbox' disabled> Enveloppe</div>";
	$c .= "<div class=\"row\"><input type='checkbox' checked disabled> <strong>Photocopie permis recto</strong></div>";
	$c .= "<div class=\"row\"><input type='checkbox' checked disabled> <strong>Photocopie permis verso</strong></div>";
	$c .= "<div class=\"row\"><input type='checkbox' checked disabled> <strong>Photocopie recto lettre 48N</strong></div>";
	$c .= "<div class=\"row\"><input type='checkbox' disabled> RII (copie ou original)</div>";
	
	return $c;
}

function espace_stagiaire_donnees_perso($id_stagiaire) {

	include ("/home/prostage/connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);
	
	$sql = "SELECT 
				nom,
				prenom,
				civilite,
				adresse,
				code_postal,
				ville,
				date_naissance,
				lieu_naissance,
				email,
				tel,
				mobile,
				validations_stagiaire
			FROM
				stagiaire
			WHERE	
				id = $id_stagiaire";
	
	$rs = mysql_query($sql, $stageconnect) or die(mysql_error());
	mysql_close($stageconnect);	
	$row = mysql_fetch_assoc($rs);
	
	$nom = utf8_encode($row['nom']);
	$prenom = utf8_encode($row['prenom']);
	$civilite = $row['civilite'] == 'Mr' ? '1':'2';
	$adresse = utf8_encode($row['adresse']);
	$code_postal = $row['code_postal'];
	$ville = utf8_encode($row['ville']);
	$date_naissance = $row['date_naissance'];
	$lieu_naissance = utf8_encode($row['lieu_naissance']);
	$email = $row['email'];
	$tel = $row['tel'];
	$mobile = $row['mobile'];
	$tel = strlen($tel) > 5 ? $tel : $mobile;
	$validations_stagiaire = $row['validations_stagiaire'];
	
	$c = "<div class=\"row\">
		<div class=\"legend col-md-3\">Civilite:</div>
		<div class=\"col-md-9\">
		<a href=\"#\" data-value=\"$civilite\" data-source=\"[{'1':'Mr'}, {'2':'Mme'}]\" data-type=\"select\" class=\"editable_civilite\" data-pk=\"$id_stagiaire\" data-name=\"stagiaire|civilite\"></a>
		</div>
		</div>";
		
	$c .= "<div class=\"row\">
		<div class=\"legend col-md-3\">Nom:</div>
		<div class=\"col-md-9\">
		<a href=\"#\" class=\"editable_text\" data-pk=\"$id_stagiaire\" data-name=\"stagiaire|nom\">$nom</a>
		</div>
		</div>";
		
	$c .= "<div class=\"row\">
		<div class=\"legend col-md-3\">Prénom:</div>
		<div class=\"col-md-9\">
		<a href=\"#\" class=\"editable_text\" data-pk=\"$id_stagiaire\" data-name=\"stagiaire|prenom\">$prenom</a>
		</div>
		</div>";
		
	$c .= "<div class=\"row\">
		<div class=\"legend col-md-3\">Adresse:</div>
		<div class=\"col-md-9\">
		<a href=\"#\" class=\"editable_text\" data-pk=\"$id_stagiaire\" data-name=\"stagiaire|adresse\">$adresse</a>
		</div>
		</div>";
		
	$c .= "<div class=\"row\">
		<div class=\"legend col-md-3\">Code postal:</div>
		<div class=\"col-md-9\">
		<a href=\"#\" class=\"editable_text\" data-pk=\"$id_stagiaire\" data-name=\"stagiaire|code_postal\">$code_postal</a>
		</div>
		</div>";
		
	$c .= "<div class=\"row\">
		<div class=\"legend col-md-3\">Date naissance:</div>
		<div class=\"col-md-9\">
		<a href=\"#\" data-type='combodate' data-value='$date_naissance' class=\"editable_date\" data-pk=\"$id_stagiaire\" data-name=\"stagiaire|date_naissance\"></a>
		</div>
		</div>";
		
	$c .= "<div class=\"row\">
		<div class=\"legend col-md-3\">Lieu naissance:</div>
		<div class=\"col-md-9\">
		<a href=\"#\" class=\"editable_text\" data-pk=\"$id_stagiaire\" data-name=\"stagiaire|lieu_naissance\">$lieu_naissance</a>
		</div>
		</div>";
		
	$c .= "<div class=\"row\">
		<div class=\"legend col-md-3\">Email:</div>
		<div class=\"col-md-9\">
		<a href=\"#\" class=\"editable_text\" data-pk=\"$id_stagiaire\" data-name=\"stagiaire|email\">$email</a>
		</div>
		</div>";
	
	$c .= "<div class=\"row\">
		<div class=\"legend col-md-3\">Tél:</div>
		<div class=\"col-md-9\">
		<a href=\"#\" class=\"editable_text\" data-pk=\"$id_stagiaire\" data-name=\"stagiaire|tel\">$tel</a>
		</div>
		</div>";
		
	return $c;
}

function espace_stagiaire_telechargement_documents($id_stagiaire, $reload=false) {

	$time = time();
	$md5 = 	md5($id_stagiaire .'!psp#');
	$md5 = substr($md5, 0, 10);
	$src = "https://www.prostagespermis.fr/stages/telechargement_documents.php?id=".$id_stagiaire."&k=".$md5."&preventcache=".$time;
	
	$c = "";
	
	$c .= "<div style='overflow: hidden;'>
		<iframe id='iframe_telechargement_documents' scrolling='no' src='$src' style='border: 0px none; margin-left: 0px; height: 240px; margin-top: 0px; width: 100%;'>
		</iframe>
		</div>";
	
	if ($reload)
		echo $c;
	else
		return $c;
}

function espace_stagiaire_donnees_permis($id_stagiaire) {

	include ("/home/prostage/connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);
	
	$sql = "SELECT 
				num_permis,
				date_permis,
				lieu_permis,
				cas,
				date_infraction,
				heure_infraction,
				lieu_infraction,
				date_48n,
				etat_permis,
				permis_ancien_plus_trois_an,
				solde_nul,
				validations_stagiaire
			FROM
				stagiaire
			WHERE	
				id = $id_stagiaire";
	
	$rs = mysql_query($sql, $stageconnect) or die(mysql_error());
	mysql_close($stageconnect);	
	$row = mysql_fetch_assoc($rs);
	
	$num_permis = $row['num_permis'];
	$date_permis = $row['date_permis'];
	$lieu_permis = utf8_encode($row['lieu_permis']);
	$cas = $row['cas'];
	$date_infraction = $row['date_infraction'];
	$heure_infraction = $row['heure_infraction'];
	$lieu_infraction = utf8_encode($row['lieu_infraction']);
	$date_48n = $row['date_48n'];
	$etat_permis = $row['etat_permis'];
	$permis_ancien_plus_trois_an = $row['permis_ancien_plus_trois_an'];
	$solde_nul = $row['solde_nul'];
	$validations_stagiaire = $row['validations_stagiaire'];
	
	$list_cas = array('1' => '1', '2' => '2', '3' => '3', '4' => '4');
	$list_cas = json_encode($list_cas); 
		
	$c .= "<div class=\"row\">
		<div class=\"legend col-md-3\">Num permis:</div>
		<div class=\"col-md-9\">
		<a href=\"#\" class=\"editable_text\" data-pk=\"$id_stagiaire\" data-name=\"stagiaire|num_permis\">$num_permis</a>
		</div>
		</div>";
		
	$c .= "<div class=\"row\">
		<div class=\"legend col-md-3\">Date permis:</div>
		<div class=\"col-md-9\">
		<a href=\"#\" data-type='combodate' class=\"editable_date\" data-value=\"$date_permis\" data-pk=\"$id_stagiaire\" data-name=\"stagiaire|date_permis\">$date_permis</a>
		</div>
		</div>";
		
	$c .= "<div class=\"row\">
		<div class=\"legend col-md-3\">Lieu permis:</div>
		<div class=\"col-md-9\">
		<a href=\"#\" class=\"editable_text\" data-pk=\"$id_stagiaire\" data-name=\"stagiaire|lieu_permis\">$lieu_permis</a>
		</div>
		</div>";
		
	$c .= "<div class=\"row\">
		<div class=\"legend col-md-3\">Cas:</div>
		<div class=\"col-md-9\">
		<a href=\"#\" id=\"editable_cas\" data-type=\"select\" data-value=\"$cas\" data-source='$list_cas' data-pk=\"$id_stagiaire\" data-name=\"stagiaire|cas\">$cas</a>
		</div>
		</div>";
		
	$c .= "<div class=\"row\">
		<div class=\"legend col-md-3\">Date infraction:</div>
		<div class=\"col-md-9\">
		<a href=\"#\" data-type='combodate' class=\"editable_date\" data-value=\"$date_infraction\" data-pk=\"$id_stagiaire\" data-name=\"stagiaire|date_infraction\">$date_infraction</a>
		</div>
		</div>";
		
	$c .= "<div class=\"row\">
		<div class=\"legend col-md-3\">Heure infraction:</div>
		<div class=\"col-md-9\">
		<a href=\"#\" data-type='combodate' class=\"editable_date\" data-value=\"$heure_infraction\" data-pk=\"$id_stagiaire\" data-name=\"stagiaire|heure_infraction\">$heure_infraction</a>
		</div>
		</div>";
		
	$c .= "<div class=\"row\">
		<div class=\"legend col-md-3\">Lieu infraction:</div>
		<div class=\"col-md-9\">
		<a href=\"#\" class=\"editable_text\" data-pk=\"$id_stagiaire\" data-name=\"stagiaire|lieu_infraction\">$lieu_infraction</a>
		</div>
		</div>";
		
	return $c;
}

function espace_stagiaire($id_stagiaire) {
	
	$c = "<div class='row'>";
	
	$c .= "<div class='col-md-6'>";
	$c .= "<div class='col-md-12 col_esapce_stagiaire'>";
	$c .= "<h4>1. Données personnelles</h4>";
	$c .= espace_stagiaire_donnees_perso($id_stagiaire);
	$c .= "</div>";
	$c .= "</div>";

	$c .= "<div class='col-md-6'>";
	$c .= "<div class='col_esapce_stagiaire col-md-12'>";
	$c .= "<h4>2. Permis de conduire</h4>";
	$c .= espace_stagiaire_donnees_permis($id_stagiaire);
	$c .= "</div>";
	$c .= "</div>";
	
	$c .= "</div>";

	$c .= "<div class='row' style='margin-top:25px'>";
	
	$c .= "<div class='col-md-6'>";
	$c .= "<div class='col-md-12 col_esapce_stagiaire'>";
	$c .= "<h4>3. Documents à télécharger</h4>";
	$c .= "<div id='div_espace_stagiaire_telechargement_documents'>";
	$c .= espace_stagiaire_telechargement_documents($id_stagiaire);
	$c .= "</div>";
	$c .= "</div>";
	$c .= "</div>";

	$c .= "<div class='col-md-6'>";
	$c .= "<div class='col_esapce_stagiaire col-md-12'>";
	$c .= "<h4>4. Documents à récupérer</h4>";
	$c .= espace_stagiaire_documents_a_recuperer($id_stagiaire);
	$c .= "</div>";
	$c .= "</div>";
	
	$c .= "</div>";	
	
	$c .= "<div class='row' style='margin-top:30px'>";
	$c .= "<button type=\"button\" class=\"dossier_verifie btn btn-w-m btn-success pull-right\" style=\"margin-right:15px;\">Enregistrer les modifications</button>";	
	$c .= "</div>";	
	
	echo $c;
	
	/*$md5 = md5($id_stagiaire .'!psp#');
	$md5 = substr($md5, 0, 10);
	$src = "http://www.prostagespermis.fr/monstage.php?s=".$id_stagiaire."&k=".$md5;
	
	$c = "";
	
	$c .= "<div style='overflow: hidden;'>
		<iframe scrolling='no' src='$src' style='border: 0px none; margin-left: 0px; height: 1194px; margin-top: -380px; width: 100%;'>
		</iframe>
		</div>";
		
	$c .= "<button type='button' class='dossier_verifie btn btn-w-m btn-success'>Dossier vérifié !</button>";
	
	echo $c;*/
}

function dossier_verifie($id_stagiaire)
{
	include ("/home/prostage/connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	$sql = "UPDATE stagiaire SET dossier_verifie=1 WHERE id=$id_stagiaire";
	$rs = mysql_query($sql, $stageconnect) or die(mysql_error());

	mysql_close($stageconnect);
}

function affiche_horaires_pedagogiques($id_stage)
{
	include ("/home/prostage/connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	$sql = "SELECT horaires_pedagogiques FROM stage WHERE id = $id_stage";	
	
	$rs = mysql_query($sql, $stageconnect) or die(mysql_error());
	mysql_close($stageconnect);
	
	$row = mysql_fetch_assoc($rs);
	$horaires_pedagogiques = $row['horaires_pedagogiques'];
	$tab = explode('|', $horaires_pedagogiques);
	$debut1 = $tab[0];
	$fin1 = $tab[1];
	$debut2 = $tab[2];
	$fin2 = $tab[3];

	$c = "<table class='horaires'>";
	$c .= "<tr>";
		$c .= "<td>Première journée</td>";
		$c .= "<td>";										
			$c .= "<select class='horaires_pedagogiques'>";
				$c .= "<option".((($debut1 == 'Début jour 1') || (strlen($debut1) < 2)) ? " selected" :"").">Début jour 1</option>";
				$c .= "<option".(($debut1 == '07:00') ? " selected" :"").">07:00</option>";
				$c .= "<option".(($debut1 == '07:15') ? " selected" :"").">07:15</option>";
				$c .= "<option".(($debut1 == '07:30') ? " selected" :"").">07:30</option>";
				$c .= "<option".(($debut1 == '07:45') ? " selected" :"").">07:45</option>";
				$c .= "<option".(($debut1 == '08:00') ? " selected" :"").">08:00</option>";
				$c .= "<option".(($debut1 == '08:15') ? " selected" :"").">08:15</option>";
				$c .= "<option".(($debut1 == '08:30') ? " selected" :"").">08:30</option>";
				$c .= "<option".(($debut1 == '08:45') ? " selected" :"").">08:45</option>";
				$c .= "<option".(($debut1 == '09:00') ? " selected" :"").">09:00</option>";
				$c .= "<option".(($debut1 == '09:15') ? " selected" :"").">09:15</option>";
				$c .= "<option".(($debut1 == '09:30') ? " selected" :"").">09:30</option>";
				$c .= "<option".(($debut1 == '09:45') ? " selected" :"").">09:45</option>";
				$c .= "<option".(($debut1 == '10:00') ? " selected" :"").">10:00</option>";
			$c .= "</select>";
		$c .= "</td>";
		
		$c .= "<td>";											
			$c .= "<select class='horaires_pedagogiques'>";
				$c .= "<option".((($fin1 == 'Fin jour 1') || (strlen($fin1) < 2)) ? " selected" :"").">Fin jour 1</option>";
				$c .= "<option".(($fin1 == '16:00') ? " selected" :"").">16:00</option>";
				$c .= "<option".(($fin1 == '16:15') ? " selected" :"").">16:15</option>";
				$c .= "<option".(($fin1 == '16:30') ? " selected" :"").">16:30</option>";
				$c .= "<option".(($fin1 == '16:45') ? " selected" :"").">16:45</option>";
				$c .= "<option".(($fin1 == '17:00') ? " selected" :"").">17:00</option>";
				$c .= "<option".(($fin1 == '17:15') ? " selected" :"").">17:15</option>";
				$c .= "<option".(($fin1 == '17:30') ? " selected" :"").">17:30</option>";
				$c .= "<option".(($fin1 == '17:45') ? " selected" :"").">17:45</option>";
				$c .= "<option".(($fin1 == '18:00') ? " selected" :"").">18:00</option>";
				$c .= "<option".(($fin1 == '18:15') ? " selected" :"").">18:15</option>";
				$c .= "<option".(($fin1 == '18:30') ? " selected" :"").">18:30</option>";
				$c .= "<option".(($fin1 == '18:45') ? " selected" :"").">18:45</option>";
				$c .= "<option".(($fin1 == '19:00') ? " selected" :"").">19:00</option>";
			$c .= "</select>";
		$c .= "</td>";
		$c .= "</tr>";
		
		$c .= "<tr>";
		$c .= "<td>Deuxième journée</td>";
		$c .= "<td>";											
			$c .= "<select class='horaires_pedagogiques'>";
				$c .= "<option".((($debut2 == 'Début jour 2') || (strlen($debut2) < 2)) ? " selected" :"").">Début jour 2</option>";
				$c .= "<option".(($debut2 == '07:00') ? " selected" :"").">07:00</option>";
				$c .= "<option".(($debut2 == '07:15') ? " selected" :"").">07:15</option>";
				$c .= "<option".(($debut2 == '07:30') ? " selected" :"").">07:30</option>";
				$c .= "<option".(($debut2 == '07:45') ? " selected" :"").">07:45</option>";
				$c .= "<option".(($debut2 == '08:00') ? " selected" :"").">08:00</option>";
				$c .= "<option".(($debut2 == '08:15') ? " selected" :"").">08:15</option>";
				$c .= "<option".(($debut2 == '08:30') ? " selected" :"").">08:30</option>";
				$c .= "<option".(($debut2 == '08:45') ? " selected" :"").">08:45</option>";
				$c .= "<option".(($debut2 == '09:00') ? " selected" :"").">09:00</option>";
				$c .= "<option".(($debut2 == '09:15') ? " selected" :"").">09:15</option>";
				$c .= "<option".(($debut2 == '09:30') ? " selected" :"").">09:30</option>";
				$c .= "<option".(($debut2 == '09:45') ? " selected" :"").">09:45</option>";
				$c .= "<option".(($debut2 == '10:00') ? " selected" :"").">10:00</option>";
			$c .= "</select>";
		$c .= "</td>";
		
		$c .= "<td>";											
			$c .= "<select class='horaires_pedagogiques'>";
				$c .= "<option".((($fin2 == 'Fin jour 2') || (strlen($fin2) < 2)) ? " selected" :"").">Fin jour 2</option>";
				$c .= "<option".(($fin2 == '16:00') ? " selected" :"").">16:00</option>";
				$c .= "<option".(($fin2 == '16:15') ? " selected" :"").">16:15</option>";
				$c .= "<option".(($fin2 == '16:30') ? " selected" :"").">16:30</option>";
				$c .= "<option".(($fin2 == '16:45') ? " selected" :"").">16:45</option>";
				$c .= "<option".(($fin2 == '17:00') ? " selected" :"").">17:00</option>";
				$c .= "<option".(($fin2 == '17:15') ? " selected" :"").">17:15</option>";
				$c .= "<option".(($fin2 == '17:30') ? " selected" :"").">17:30</option>";
				$c .= "<option".(($fin2 == '17:45') ? " selected" :"").">17:45</option>";
				$c .= "<option".(($fin2 == '18:00') ? " selected" :"").">18:00</option>";
				$c .= "<option".(($fin2 == '18:15') ? " selected" :"").">18:15</option>";
				$c .= "<option".(($fin2 == '18:30') ? " selected" :"").">18:30</option>";
				$c .= "<option".(($fin2 == '18:45') ? " selected" :"").">18:45</option>";
				$c .= "<option".(($fin2 == '19:00') ? " selected" :"").">19:00</option>";
			$c .= "</select>";
		$c .= "</td>";
		$c .= "</tr>";
	$c .= "</table>";
	
	echo $c;
	
}

function update_sous_cat_cas($value, $selects, $id_stagiaire) {
	
	$c = "";
	switch ($value) {
		case 1:
			if (strlen($selects) == 0)
				$selects = "2,1,2";
			$selects = explode(",", $selects);
			$stage_recent = intval($selects[0]);
			$permis_recent = intval($selects[1]);
			$infraction_recente = intval($selects[2]);
			
			$c .= "<ol>";
			$c .= "<li>Avez-vous effectué un stage permettant la récupération de points au cours des 12 derniers mois ? 
						<select cas='1' id_stagiaire='$id_stagiaire' name='stage_recent' class='select_sous_cas'>
							<option value='1'>OUI</option>
							<option value='2' ";
							$c .= ($stage_recent == 2) ? "selected" : "";
							$c .= ">NON</option>
						</select>
					</li>";
			$c .= "<li>Avez-vous votre permis depuis plus de 3 ans ?  
						<select cas='1' id_stagiaire='$id_stagiaire' name='permis_recent' class='select_sous_cas'>
							<option value='1'>OUI</option>
							<option value='2' ";
							$c .= ($permis_recent == 2) ? "selected" : "";
							$c .= ">NON</option>
						</select>
					</li>";
			$c .= "<li>Avez-vous commis une infraction récemment ? 
						<select cas='1' id_stagiaire='$id_stagiaire' name='infraction_recente' class='select_sous_cas'>
							<option value='1'>OUI</option>
							<option value='2' ";
							$c .= ($infraction_recente == 2) ? "selected" : "";
							$c .= ">NON</option>
						</select>
					</li>";
			$c .= "</ol>";
			
		break;
		
		case 2:
			if (strlen($selects) == 0)
				$selects = "1,1,1";
			$selects = explode(",", $selects);
			$reception_48n = intval($selects[0]);
			$conservation_48n = intval($selects[1]);
			$infraction_recente = intval($selects[2]);
			
			$c .= "<ol>";
			$c .= "<li>Avez-vous reçu la lettre recommandée référence 48N ? 
						<select cas='2' id_stagiaire='$id_stagiaire' name='reception_48n' class='select_sous_cas'>
							<option value='1'>OUI</option>
							<option value='2' ";
							$c .= ($reception_48n == 2) ? "selected" : "";
							$c .= ">NON</option>
						</select>
					</li>";
			$c .= "<li>Avez-vous conservé votre lettre 48N ?  
						<select cas='2' id_stagiaire='$id_stagiaire' name='conservation_48n' class='select_sous_cas'>
							<option value='1' selected>OUI</option>
							<option value='2' ";
							$c .= ($conservation_48n == 2) ? "selected" : "";
							$c .= ">NON</option>
						</select>
					</li>";
			$c .= "<li>Votre infraction date-t-elle de moins de 4 mois ? 
						<select cas='2' id_stagiaire='$id_stagiaire' name='infraction_recente' class='select_sous_cas'>
							<option value='1'>OUI</option>
							<option value='2' ";
							$c .= ($infraction_recente == 2) ? "selected" : "";
							$c .= ">NON</option>
						</select>
					</li>";
			$c .= "</ol>";
			
		break;
		
		case 3:
		case 4:
		break;
		
	}
	
	$selects = implode(",", $selects);
	$c .= "<div class='text_cas'>";
	$c .= get_text_cas($value, $selects);
	$c .= "</div>";
	
	$c = utf8_decode($c);
	
	return $c;
}

function get_text_cas($cas, $selects) {
	
	switch ($cas) {
		case 1:
				
			$selects = explode(",", $selects);
			$stage_recent = intval($selects[0]);
			$permis_recent = intval($selects[1]);
			$infraction_recente = intval($selects[2]);

			
			if ($stage_recent == 1 && $permis_recent == 1 && $infraction_recente == 1)
				
				$text = "Attention ! Si vous avez effectué un stage au cours des 12 derniers mois, vous ne récupérerez pas de points. Il faut respecter un délai d'un an et un jour entre deux stages.<br><br>
				
				Article L223-6 du Code de la Route
				“Le titulaire du permis de conduire qui a commis une infraction ayant donné lieu à retrait de points peut obtenir une récupération de points s'il suit un stage de sensibilisation à la sécurité routière qui peut être effectué dans la limite d'une fois par an.”
				<br><br>
				Par exemple, si vous avez effectué un stage les 9 et 10 décembre 2016, vous pourrez suivre votre prochain stage au plus tôt les 11 et 12 décembre 2017.
				<br><br>
				Si vous avez commis une infraction récemment, vous avez 45 jours pour la payer avant que vos points ne soient retirés automatiquement. Jouez si possible sur ce délai pour décaler au maximum votre stage avant de payer votre amende. Si ce n'est pas possible, inutile de s'inscrire. 
				<br><br>
				Exception ! Si le stage que vous avez réalisé ne vous a pas permis de récupérer de points (par exemple un stage obligatoire imposé par le Juge suite à un grosse infraction), vous pouvez vous inscrire. En effet, participer à plusieurs stages la même année n'est pas interdit, mais la récupération de points n'est possible qu'une seule fois par an. 
				<br><br>
				<strong>Résultat</strong><br> 
				A moins que le dernier stage réalisé soit obligatoire, attendez que le délai des 12 mois soit écoulé avant de vous inscrire !";
				
			else if ($stage_recent == 1 && $permis_recent == 1 && $infraction_recente == 2)
				
				$text = "Attention ! Si vous avez effectué un stage au cours des 12 derniers mois, vous ne récupérerez pas de points. Il faut respecter un délai d'un an et un jour entre deux stages. 
				<br><br>
				Article L223-6 du Code de la Route: 
				“Le titulaire du permis de conduire qui a commis une infraction ayant donné lieu à retrait de points peut obtenir une récupération de points s'il suit un stage de sensibilisation à la sécurité routière qui peut être effectué dans la limite d'une fois par an.”
				<br><br>
				Par exemple, si vous avez effectué un stage les 9 et 10 décembre 2016, vous pourrez suivre votre prochain stage au plus tôt les 11 et 12 décembre 2017.
				<br><br>
				Exception ! Si le stage que vous avez réalisé ne vous a pas permis de récupérer de points (par exemple un stage obligatoire imposé par le Juge suite à un grosse infraction), vous pouvez vous inscrire. En effet, participer à plusieurs stages la même année n'est pas interdit, mais la récupération de points n'est possible qu'une seule fois par an. 
				<br><br>
				<strong>Résultat</strong><br>
				A moins que le dernier stage réalisé soit obligatoire, attendez que le délai des 12 mois soit écoulé avant de vous inscrire !";
				
			else if ($stage_recent == 1 && $permis_recent == 2 && $infraction_recente == 1)
				
				$text = "<u>Délai entre deux stages</u><br>
				Attention ! Si vous avez effectué un stage au cours des 12 derniers mois, vous ne récupérerez pas de points. Il faut respecter un délai d'un an et un jour entre deux stages.
				<br><br>			
				Article L223-6 du Code de la Route: “Le titulaire du permis de conduire qui a commis une infraction ayant donné lieu à retrait de points peut obtenir une récupération de points s'il suit un stage de sensibilisation à la sécurité routière qui peut être effectué dans la limite d'une fois par an.”
				<br><br>
				Par exemple, si vous avez effectué un stage les 9 et 10 décembre 2016, vous pourrez suivre votre prochain stage au plus tôt les 11 et 12 décembre 2017.
				<br><br>
				Exception ! Si le stage que vous avez réalisé ne vous a pas permis de récupérer de points (par exemple un stage obligatoire imposé par le Juge suite à un grosse infraction), vous pouvez vous inscrire. En effet, participer à plusieurs stages la même année n'est pas interdit, mais la récupération de points n'est possible qu'une seule fois par an. 
				<br><br>
				<u>Infraction en permis probatoire</u>
				<ul>
				<li>Votre infraction va vous coûter 3 points ou plus<br>
				Un stage peut-être imposé à un jeune conducteur en permis probatoire s'il commet une infraction de 3 points ou plus. Dans ce cas, une lettre recommandée (référence 48N) est envoyée au conducteur dans un délai de 1 à 12 mois après le paiement de l'amende. Ce courrier impose au jeune conducteur de suivre un stage de sensibilisation à la sécurité routière dans une délai de 4 mois après la réception de la lettre. Si vous êtes dans cette situation, vous devez vous inscrire en cas n°2 : stage obligatoire en période probatoire avec lettre 48N. Ce stage permet de récupérer jusqu'à 4 points (dans la limite du plafond maximum de points). En revanche, si vous avez effectué un stage au cours des 12 derniers mois, vous ne pourrez pas récupérer de points. Le stage reste cependant obligatoire !
				</li>
				<li>Votre infraction va vous coûter 1 ou 2 points<br>
				Le stage n'est donc pas obligatoire, mais vous aurez la possibilité de suivre un stage volontaire une fois le délai des 12 mois écoulés.
				</li>			
				<li>Votre infraction va vous coûter 6 points<br>  
				Attention, si votre plafond maximum de points est bloqué à 6 (par exemple en première année de permis probatoire), un stage est inutile et ne vous permettra pas de sauver votre permis !
				</li>
				<br>
				<strong>Résultat</strong><br>
				<ol>
				<li>Vous avez reçu la lettre 48N : inscrivez-vous en <strong>cas n°2</strong> : stage obligatoire en période probatoire avec lettre 48N. Si le délai des 12 mois est écoulé au moment du stage, vous pourrez récupérer des points. Si le délai n'est pas écoulé, le stage reste obligatoire mais ne vous permet pas de récupérer de points.</li>
				<li>Votre infraction va vous coûter 3 points ou plus : payez votre amende, attendez la lettre 48N, puis inscrivez-vous en <strong>cas n°2</strong> : stage obligatoire en période probatoire avec lettre 48N. Si le délai des 12 mois est écoulé au moment du stage, vous pourrez récupérer des points. Si le délai n'est pas écoulé, le stage reste obligatoire mais ne vous permet pas de récupérer de points.</li>
				<li>Votre infraction va vous coûter moins de 3 points : attendez le délai des 12 mois, puis si vous souhaitez effectuer un stage pour récupérer des points inscrivez-vous en <strong>cas n°1</strong> : stage de récupération de points volontaire.</li>
				<li>Votre infraction va vous coûter 6 points : si votre plafond est également bloqué à 6, le stage est inutile (demandez le remboursement). Sinon, attendez la réception de la lettre 48N puis inscrivez-vous en <strong>cas n°2</strong>.</li>
				</ol>";
			
			else if ($stage_recent == 1 && $permis_recent == 2 && $infraction_recente == 2)
				
				$text = "Même en permis probatoire, vous avez la possibilité de suivre un stage volontaire pour récupérer des points. 
				<br><br>
				<u>Délai entre deux stages</u><br>
				Attention ! Si vous avez effectué un stage au cours des 12 derniers mois, vous ne récupérerez pas de points. Il faut respecter un délai d'un an et un jour entre deux stages. 
				<br><br>
				Article L223-6 du Code de la Route: “Le titulaire du permis de conduire qui a commis une infraction ayant donné lieu à retrait de points peut obtenir une récupération de points s'il suit un stage de sensibilisation à la sécurité routière qui peut être effectué dans la limite d'une fois par an.”
				<br><br>
				Par exemple, si vous avez effectué un stage les 9 et 10 décembre 2016, vous pourrez suivre votre prochain stage au plus tôt les 11 et 12 décembre 2017.
				<br><br>
				Exception ! Si le stage que vous avez réalisé ne vous a pas permis de récupérer de points (par exemple un stage obligatoire imposé par le Juge suite à un grosse infraction), vous pouvez vous inscrire. En effet, participer à plusieurs stages la même année n'est pas interdit, mais la récupération de points n'est possible qu'une seule fois par an. 
				<br><br>
				<strong>Résultat</strong><br>
				A moins que le dernier stage réalisé soit obligatoire, attendez que le délai des 12 mois soit écoulé avant de vous inscrire !";
				
			else if ($stage_recent == 2 && $permis_recent == 2 && $infraction_recente == 2)
			
				$text = "Même en permis probatoire, vous avez la possibilité de suivre un stage volontaire pour récupérer des points. 
				<br><br>
				Attention ! Si vous avez commis une infraction qui vous a fait perdre 3 points ou plus, vous recevrez la lettre 48N. Le délai de réception de cette lettre est compris entre 1 et  12 mois après le paiement de l'amende. Si vous êtes dans cette situation, attendez de recevoir la lettre pour vous inscrire. 
				<br><br>
				<strong>Résultat</strong><br>
				Vous pouvez vous inscrire en cas n°1 : stage de récupération de points volontaire, à moins que vous ne soyez concerné par la lettre 48N. Dans ce cas, attendez de la recevoir puis inscrivez-vous en cas n°2 : stage obligatoire en période probatoire avec lettre 48N.";
				
			else if ($stage_recent == 2 && $permis_recent == 1 && $infraction_recente == 2)
				
				$text = "Vous avez la possibilité de suivre un stage de récupération de points volontaire. Vérifiez bien votre solde de points. Il doit être compris entre 1 et 8 points si vous voulez récupérer 4 points sur votre permis.
				<br><br>
				Moins de 1 point ? Si votre solde est à zéro et que vous n'avez pas encore reçu la lettre 48SI, vous pouvez suivre un stage. Il faut tout de même vérifier sur votre Relevé Intégral d'Information (disponible en préfecture), que votre permis est encore valide. 
				<br><br>
				Plus de 8 points ? Vous pouvez suivre le stage, mais vous ne récupérerez que 1, 2 ou 3 points, car vous ne pouvez pas dépasser le plafond maximum de 12 points.";
				
			else if ($stage_recent == 2 && $permis_recent == 2 && $infraction_recente == 1)
				
				$text = "<u>Infraction en permis probatoire</u><br>
				<ul>
				<li>Votre infraction va vous coûter 3 points ou plus<br>
				Un stage peut-être imposé à un jeune conducteur en permis probatoire s'il commet une infraction de 3 points ou plus. Dans ce cas, une lettre recommandée (référence 48N) est envoyée au conducteur dans un délai de 1 à 12 mois après le paiement de l'amende. Ce courrier impose au jeune conducteur de suivre un stage de sensibilisation à la sécurité routière dans une délai de 4 mois après la réception de la lettre. Si vous êtes dans cette situation, vous devez vous inscrire en cas n°2 : stage obligatoire en période probatoire avec lettre 48N. Ce stage permet de récupérer jusqu'à 4 points (dans la limite du plafond maximum de points).
				</li>
				<li>Votre infraction va vous coûter 1 ou 2 points<br>
				Le stage n'est donc pas obligatoire, mais vous avez la possibilité de suivre un stage volontaire pour récupérer des points (cas n°1).
				</li>
				<li>Votre infraction va vous coûter 6 points<br>   
				Attention, si votre plafond maximum de points est bloqué à 6 (par exemple en première année de permis probatoire), un stage est inutile et ne vous permettra pas de sauver votre permis !
				</li>
				</ul>
				<strong>Résultat</strong><br>
				<ol>
				<li>
				Vous avez reçu la lettre 48N : inscrivez-vous en cas n°2 : stage obligatoire en période probatoire avec lettre 48N.
				</li>				
				<li>Votre infraction va vous coûter 3 points ou plus : payez votre amende, attendez la lettre 48N, puis inscrivez-vous en cas n°2 : stage obligatoire en période probatoire avec lettre 48N.</li>
				<li>Votre infraction va vous coûter moins de 3 points : inscrivez-vous en cas n°1 : stage de récupération de points volontaire si vous souhaitez récupérer des points.</li>
				<li>Votre infraction va vous coûter 6 points et votre plafond est bloqué à 6 : demandez le remboursement.</li>
				</ol>
				Votre infraction va vous coûter moins de 3 points : inscrivez-vous en cas n°1 : stage de récupération de points volontaire si vous souhaitez récupérer des points.";
				
			else if ($stage_recent == 2 && $permis_recent == 1 && $infraction_recente == 1)
				
				$text = "Vous avez la possibilité de suivre un stage de récupération de points volontaire. Vérifiez bien votre solde de points. Il doit être compris entre 1 et 8 points si vous voulez récupérer 4 points sur votre permis.
				<br><br>
				Attention ! Les points ne sont retirés qu'au paiement de l'amende (ou à défaut à 45 jours). Vérifiez donc que vos points ont bien été retirés avant de participer au stage. 
				<br><br>
				Moins de 1 point ? Si votre solde est à zéro et que vous n'avez pas encore reçu la lettre 48SI, vous pouvez suivre un stage. Il faut tout de même vérifier sur votre Relevé Intégral d'Information (disponible en préfecture), que votre permis est encore valide.";
				
		break;
		
		case 2:
				
			$selects = explode(",", $selects);
			$reception_48n = intval($selects[0]);
			$conservation_48n = intval($selects[1]);
			$infraction_recente = intval($selects[2]);

			if ($reception_48n == 2)
				
				$text = "Vous ne devez vous inscrire en cas n°2 que si vous avez déjà reçu la lettre 48N. Ce courrier recommandé concerne uniquement les jeunes conducteurs ayant commis une infraction de 3 points ou plus au cours de leur période probatoire. 
				<br><br>
				Si vous n'êtes pas dans cette situation, et même si vous êtes en période probatoire, vous pouvez suivre un stage pour récupérer des points en vous inscrivant en cas n°1 : stage de récupération de points volontaire.
				<br><br>
				Si vous êtes dans cette situation mais que vous n'avez pas encore reçu la lettre 48N, vous devez impérativement attendre ce courrier avant de participer au stage (à moins que votre permis ne soit en danger). Autrement, votre stage ne sera pas validé comme répondant à cette obligation, et vous serez obligé de suivre un deuxième stage après la réception de la lettre, sans toutefois pouvoir récupérer des points.
				<br><br>
				<strong>Résultat</strong><br>
				Vous allez recevoir la lettre 48N : mettez votre dossier en attente et positionnez-vous sur une date dès que vous recevez le courrier. 
				<br><br>
				Vous n'êtes pas concerné par la lettre 48N mais souhaitez récupérer des points ? Inscrivez-vous en cas n°1 : stage de récupération de points volontaire.";
				
			else if ($reception_48n == 1) {
				
				$text = "Attention ne pas confondre avec la lettre 48M ! 
				<ul>
				<li>La lettre 48N concerne uniquement les jeunes conducteurs et les oblige à suivre un stage de sensibilisation à la sécurité routière dans un délai de 4 mois.</li>
				<li>La lettre 48M concerne tous les conducteurs et les informe que leur solde est de 6 points ou moins. Le stage est recommandé mais pas obligatoire !</li>
				</ul>";
				
				if ($conservation_48n == 2) {
					if ($infraction_recente == 1)
						$text .= "Fournissez une copie de la lettre 48N";
					else
						$text .= "Le délai étant dépassé, vous devez vous inscrire en cas n°1 et fournir une copie de la lettre 48N";
				}
				else {
					if ($infraction_recente == 1)
						$text .= "Fournissez un Relevé Intégral d'Information à la place de la lettre 48N";
					else
						$text .= "Le délai étant dépassé, vous devez vous inscrire en cas n°1 et fournir un Relevé Intégral d'Information à la place de la lettre 48N";				
				}
			}
				
			else if ($reception_48n == 2) 
				$text = " Le stage reste obligatoire. Vous devez vous rendre en préfecture pour demander un Relevé d'Information Intégral qui mentionne la réception de cette lettre 48N (tous les détails figurent sur ce document). Ce document est nécessaire au traitement de votre dossier en préfecture.";
			
		break;
		
		case 3:
			$text = "<u>Stage en alternative aux poursuites judiciaires</u>
			<br><br>
			Comme son nom l'indique, ce stage est une alternative ! Suite à une infraction, le Procureur de la République vous laisse le choix entre deux options :
			<ul>
			<li>soit vous refusez le stage : dans ce cas vous devrez payer une amende et vos points seront retirés. Vous pouvez en plus faire l'objet de poursuites administratives ou judiciaires et écoper de peines complémentaires, comme une suspension de permis.</li>
			<li>soit vous acceptez le stage, qui remplace et efface toutes les peines : pas de perte de points, pas d'amende, pas de poursuites, etc. Ce stage ne permet toutefois pas de récupérer des points !</li>
			</ul>
	
			Pour le traitement de votre dossier, il faut fournir une copie de l'ordonnance pénale correspondante.
			<br><br>
			Attention ! Tous nos stages ont une durée de deux jours. Si le stage qui vous est proposé ne dure qu'une seule journée, vous devez vous adresser à un centre spécialisé (prendre contact avec les autorités pour obtenir la liste). 
			<br><br>
			<u>Stage en composition pénale</u><br>
			Ce stage vous est également proposé par le Procureur de la République, mais contrairement au cas précédent, il peut s'accompagner d'autres peines (amende, suspension, etc.). Une fois le stage effectué, le nombre de points correspondants à votre infraction seront retirés de votre permis de conduire. En revanche, il vous évite une comparution au tribunal ! 
			<br><br>
			En bref !<br>
			Quel que soit le type de stage qui vous concerne :
			<ul>
			<li>vous n'avez pas fait l'objet de poursuites administratives ou judiciaires (sinon cas n°4)</li>
			<li>vous devez fournir une copie de l'ordonnance pénale</li>
			<li>vous pouvez en plus suivre un stage de récupération de points volontaire si nécessaire</li>
			</ul>";
		break;
		
		case 4:
			$text = "<u>Stage en peine complémentaire</u><br>
			Si vous avez commis un délit prévu par le Code de la Route, le juge peut vous imposer de suivre un stage de sensibilisation à la sécurité routière dans un délai de 6 mois. Comme son nom l'indique, la peine est “complémentaire”, c'est-à-dire qu'elle vient s'ajouter à l'ensemble des autres peines (perte de points, paiement d'une amende, suspension ou annulation de permis). Le stage ne permet pas la récupération de points. 
			<br><br>
			<u>Stage de mise à l'épreuve avec sursis</u><br>
			En cas de délit grave, le juge peut remplacer la peine de prison ferme par un sursis avec mise à l'épreuve. Dans ce cadre, un stage de sensibilisation peut être imposé au contrevenant. Ce stage ne permet pas la récupération de points. 
			<br><br>
			En bref !<br>
			Quel que soit le type de stage qui vous concerne :
			<ul>
			<li>ce stage vous est imposé par le juge suite à votre passage au tribunal (si vous n'êtes pas passé au tribunal, inscrivez-vous en cas n°3)</li>
			<li>vous devez fournir une copie de l'ordonnance pénale</li>
			<li>pas de récupération de points possible...</li>
			<li>...mais vous pouvez en plus suivre un stage de récupération de points volontaire si nécessaire</li>
			</ul>";
		break;
	}

	return $text;	
}

function getTexte($message) {
	
	switch($message) {
		case 1: //remboursement
			$objet = "Votre demande de remboursement";
			$texte = "Bonjour,<br><br>Nous accusons réception de votre demande de remboursement de votre stage de récupération de points. Votre demande sera traitée dans les 48H.<br><br>Cordialement,<br>L'équipe planning IDStages.";
		break;
	}
	
	return array($objet, $texte);
}
?>