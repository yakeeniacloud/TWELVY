<?php

function getPrixMinAlentours($cp, $distance_max, $date1, $date2)
{

	include ("../../connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);
	
	$sql = "SELECT 
				latitude, longitude 
			FROM 
				site 
			WHERE 
				latitude != '' AND
				latitude is not null AND
				longitude != '' AND
				longitude is not null AND
				code_postal = ".$cp;
				
	$rs = mysql_query($sql, $stageconnect);
	$row = mysql_fetch_assoc($rs);
	$total = mysql_num_rows($rs);
	
	if ($total == 0)
	{
		return 1000;
		exit;
	}
	
	$latitude = $row['latitude'];
	$longitude = $row['longitude'];
	
	$formule = "(6366*acos(cos(radians($latitude))*cos(radians(`latitude`))*cos(radians(`longitude`) - radians($longitude))+sin(radians($latitude))*sin(radians(`latitude`))))";
	
	//$sql = "SELECT DISTINCT ville,code_postal,$formule AS dist FROM site WHERE $formule<=$distance_max ORDER by dist ASC";
	$sql = "SELECT 
				stage.id AS stage_id,
				stage.prix AS prix_min,
				$formule AS dist
			FROM 
				stage, site
			WHERE 
				stage.id_site = site.id AND
				stage.date1 >= '$date1' AND
				stage.date1 <= '$date2' AND
				stage.annule = 0 AND
				stage.nb_places_allouees > 0 AND
				$formule <= $distance_max 
				
			ORDER BY stage.prix ASC
			LIMIT 1";
			
	$rs = mysql_query($sql, $stageconnect);
	$row = mysql_fetch_assoc($rs);
	$total = mysql_num_rows($rs);
	
	mysql_close($stageconnect);
	
	if (($total == 1) && isInteger($row['prix_min']))
	{
		//DEBUG
		$debug = fopen('min_allentours.txt', 'a+');
		$log = $row['stage_id']."\n";
		fputs($debug, $log);
		fclose($debug);
		//!DEBUG		
		return $row['prix_min'];
	}
	else
		return 1000;
}

function isInteger($input){
    return(ctype_digit(strval($input)));
}

function redirect($Str_Location, $Bln_Replace = 1, $Int_HRC = NULL)
{
        if(!headers_sent())
        {
            header('location: ' . urldecode($Str_Location), $Bln_Replace, $Int_HRC);
            exit;
        }

    exit('<meta http-equiv="refresh" content="0; url=' . urldecode($Str_Location) . '"/>'); # | exit('<script>document.location.href=' . urldecode($Str_Location) . ';</script>');
    return;
}

function removeURLParams($url, $params) {
    foreach($params as $param) {
        $url = removeURLParam($url, $param);
    }
    return $url;
}
function removeURLParam($url, $param) {
    return preg_replace('/(\&?'.$param.'(=[^&]*)?)/', '', $url);
}

function annulerStagiaire ($id_stagiaire)
{

	require_once("../../common/functions.php");
	include ("../../connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	$aujourdui = date("Y-m-d");

	//requete
	$query_stagiaire = "SELECT 	stagiaire.*,

								site.nom AS site_nom,
								site.ville AS site_ville,
								site.adresse AS site_adresse,
								site.code_postal AS site_codepostal,

								stage.date1 AS date1,
								stage.date2 AS date2,
								stage.prix AS prix,
								stage.debut_am AS debut_am,
								stage.fin_am AS fin_am,
								stage.debut_pm AS debut_pm,
								stage.fin_pm AS fin_pm,

								membre.nom AS membre_nom,
								membre.adresse AS membre_adresse,
								membre.email AS membre_email,
								membre.tel AS membre_tel,
								membre.mobile AS membre_mobile,
								membre.fax AS membre_fax,
								membre.id AS membre_id

						FROM stagiaire, stage, site, membre

						WHERE
								stagiaire.id = $id_stagiaire AND
								stagiaire.id_stage = stage.id AND
								stage.id_site = site.id AND
								stage.id_membre = membre.id";

	$rsStagiaire = mysql_query($query_stagiaire, $stageconnect) or die(mysql_error());
	$row_stagiaire = mysql_fetch_assoc($rsStagiaire);
	$totalRows_stagiaire = mysql_num_rows($rsStagiaire);

	if ($totalRows_stagiaire != 1)
	{
		echo "Erreur: impossible d'annuler le stagiaire car aucun resultat de requete. Contactez notre hotline !";
		exit;
	}

	//updates
	//-------
	$supprime = 1;
	$sql = "UPDATE stagiaire SET supprime=$supprime, date_suppression=\"$aujourdui\" WHERE id = $id_stagiaire";
	mysql_query($sql);
	//$ret = mysql_query($sql, $stageconnect) or die(mysql_error());

	//envoi du mail
	//-------------
	$autoecole = "";
	if ($row_stagiaire['id_autoecole'] != 0 && $row_stagiaire['id_autoecole'] != NULL)
		$autoecole = "[AUTOECOLE] ";	
	
	$to = $row_stagiaire['email'];
	$contact = "contact@prostagespermis.fr";

	$subject = "Annulation de stage: ".stripslashes($row_stagiaire['nom'])." ".stripslashes($row_stagiaire['prenom']);

	$msg  = "<div align='center'><u><b><font color='red'>ANNULATION DE STAGE (LE ".$aujourdui.")</font></b></u></div>";$msg .= "<br>";

	$msg .= "<div align='justify'>Nous regrettons de n'avoir pu maintenir votre inscription au stage de récupération de points.
Néanmoins, nous pouvons vous proposer d'autres dates de sessions qui pourraient tout à fait vous convenir. Pour cela, n'hésitez pas a contacter
l'un de nos conseillers au 04-86-31-80-70";$msg .= "<br><br>";

	$msg .= "<div align='justify'><b><font color='red'>Motif d'annulation du centre organisateur: ".$row_stagiaire['motif_annulation']."</font></b></div>";$msg .= "<br><br>";

	$msg .= "<u><b>CENTRE ORGANISATEUR:</b></u>";$msg .= "<br>";
	$msg .= "<table  width=\"100%\">";
	$msg .= "<tr>";
	$msg .= "<td width=\"23%\">";$msg .= "<em>Nom: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= stripslashes($row_stagiaire['membre_nom']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Adresse: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= stripslashes($row_stagiaire['membre_adresse']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Tel: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['membre_tel']."  ".$row_stagiaire['membre_mobile'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Email: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['membre_email']."  Fax: ".$row_stagiaire['membre_fax'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "</table>";

	$msg .= "<br>";

	$msg .= "<u><b>STAGE SELECTIONNE:</b></u>";$msg .= "<br>";
	$msg .= "<table  width=\"100%\">";
	$msg .= "<tr>";
	$msg .= "<td width=\"23%\">";$msg .= "<em>Dates: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= MySQLDateToExplicitDate($row_stagiaire['date1'])." et ".MySQLDateToExplicitDate($row_stagiaire['date2']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Horaires: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['debut_am']."-".$row_stagiaire['fin_am']." et ".$row_stagiaire['debut_pm']."-".$row_stagiaire['fin_pm'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Lieu: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= filter($row_stagiaire['site_nom']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Adresse: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= filter($row_stagiaire['site_adresse']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td> </td>";
	$msg .= "<td>";$msg .= $row_stagiaire['site_codepostal']." ".filter($row_stagiaire['site_ville']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Prix: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['paiement']." euros";$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "</table>";

	$msg .= "<br>";

	$msg .= "<u><b>STAGIAIRE:</b></u>";$msg .= "<br>";
	$msg .= "<table  width=\"100%\">";
	$msg .= "<tr>";
	$msg .= "<td width=\"23%\">";$msg .= "<em>Identité: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= stripslashes($row_stagiaire['nom'])." ".stripslashes($row_stagiaire['prenom']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Adresse: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= stripslashes($row_stagiaire['adresse'])."\t\n".$row_stagiaire['code_postal']." ".stripslashes($row_stagiaire['ville']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Tél: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['tel']." ".$row_stagiaire['mobile'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Email: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['email'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Permis: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['num_permis']." (le ".$row_stagiaire['date_permis']." à ".$row_stagiaire['lieu_permis'].")";$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Type de stage: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['cas'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "</table>";

	$msg .= "<br>";
	$msg .= "<br><br>";
	$msg .= "Mailys,<br>";
	$msg .= "Conseillère Client ProStagesPermis<br>";
	$msg .= "04-34-09-12-67 (11h-12h30 et 13h30-14h30 du lundi au vendredi)<br>";
	$msg .= "contact@prostagespermis.fr";
	
	$msg_contact = $msg;
	
	if ($row_stagiaire['id_autoecole'] != 0 && $row_stagiaire['id_autoecole'] != NULL)
	{
		$msg_contact .= "<u><b>AUTOECOLE:</b></u>";$msg_contact .= "<br>";
		$msg_contact .= "<table  width=\"100%\">";
		$msg_contact .= "<tr>";
		$msg_contact .= "<td width=\"23%\">";$msg_contact .= "<em>ID Autoecole: </em>";$msg_contact .= "</td>";
		$msg_contact .= "<td>";$msg_contact .= $row_stagiaire['id_autoecole'];$msg_contact .= "</td>";
		$msg_contact .= "</tr>";
		$msg_contact .= "<tr>";
		$msg_contact .= "<td>";$msg_contact .= "<em>Commission: </em>";$msg_contact .= "</td>";
		$msg_contact .= "<td>";$msg_contact .= $row_stagiaire['comm_autoecole'];$msg_contact .= "</td>";
		$msg_contact .= "</tr>";
		$msg_contact .= "</table>";	
	}


	$headers = "From: PROStagesPermis <contact@prostagespermis.fr>\n";
	$headers .= "Reply-To: ".$contact."\n";
	$headers .= 'MIME-version: 1.0'."\n";
	$headers .= 'Content-type: text/html; charset= iso-8859-1'."\n";

	mail_externe_bootstrap_function_aff($to, $subject, $msg, $headers);
	sleep(1);
	mail_externe_bootstrap_function_aff($row_stagiaire['membre_email'], $subject, $msg, $headers);
	sleep(1);
	mail_externe_bootstrap_function_aff($contact, $autoecole.$subject." - ".stripslashes($row_stagiaire['membre_nom']), $msg_contact, $headers);
}

function mail_externe_bootstrap_function_aff($to, $objet, $content, $headers)
{		
	//mail($to, $objet, $content, $headers);
	$content = str_replace("&", "%26", $content);
	$url = "http://www.prostagespermis.com/mail/mail.php";
	
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, "to=$to&objet=$objet&content=$content&headers=$headers");
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_exec($curl);
	curl_close($curl);
}



function annulerStagiaire_toto ($id_stagiaire)
{

	require_once("../../common/functions.php");
	include ("../../connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	$aujourdui = date("Y-m-d");

	//requete
	$query_stagiaire = "SELECT 	stagiaire.*,

								site.nom AS site_nom,
								site.ville AS site_ville,
								site.adresse AS site_adresse,
								site.code_postal AS site_codepostal,

								stage.date1 AS date1,
								stage.date2 AS date2,
								stage.prix AS prix,
								stage.debut_am AS debut_am,
								stage.fin_am AS fin_am,
								stage.debut_pm AS debut_pm,
								stage.fin_pm AS fin_pm,

								membre.nom AS membre_nom,
								membre.adresse AS membre_adresse,
								membre.email AS membre_email,
								membre.tel AS membre_tel,
								membre.mobile AS membre_mobile,
								membre.fax AS membre_fax,
								membre.id AS membre_id,
								
								transaction.paiement_interne

						FROM stagiaire, stage, site, membre, transaction

						WHERE
								stagiaire.id = $id_stagiaire AND
								stagiaire.id_stage = stage.id AND
								stage.id_site = site.id AND
								transaction.id_stagiaire = stagiaire.id AND
								stage.id_membre = membre.id";

	$rsStagiaire = mysql_query($query_stagiaire, $stageconnect) or die(mysql_error());
	$row_stagiaire = mysql_fetch_assoc($rsStagiaire);
	$totalRows_stagiaire = mysql_num_rows($rsStagiaire);

	if ($totalRows_stagiaire != 1)
	{
		echo "Erreur: impossible d'annuler le stagiaire car aucun resultat de requete. Contactez notre hotline !";
		exit;
	}
	
	$paiement_interne = ($row_stagiaire['paiement_interne'] == 1) ? "PAIEMENT INTERNE " : "";

	//updates
	//-------
	$supprime = 1;
	$sql = "UPDATE stagiaire SET supprime=$supprime, date_suppression=\"$aujourdui\" WHERE id = $id_stagiaire";
	mysql_query($sql);
	//$ret = mysql_query($sql, $stageconnect) or die(mysql_error());

	//envoi du mail
	//-------------
	$autoecole = "";
	if ($row_stagiaire['id_autoecole'] != 0 && $row_stagiaire['id_autoecole'] != NULL)
		$autoecole = "[AUTOECOLE] ";	
		
	$to = $row_stagiaire['email'];
//	$contact = "contact@prostagespermis.fr";
	$contact = "prostagespermis@gmail.com";
        
        $boost_entete = '';
        if ($row_stagiaire['option_reversement'] == '2')
            $boost_entete = 'BOOSTÉ ';

	$subject = "Annulation de stage : ".stripslashes($row_stagiaire['nom'])." ".stripslashes($row_stagiaire['prenom']);

	$subject_pro = "Annulation de stage $boost_entete: ".stripslashes($row_stagiaire['nom'])." ".stripslashes($row_stagiaire['prenom']);

        $msg = "<br>";

	$msg .= "<div align='justify'>Nous regrettons de n'avoir pu maintenir votre inscription au stage de récupération de points.
Néanmoins, nous pouvons vous proposer d'autres dates de sessions qui pourraient tout à fait vous convenir. Pour cela, n'hésitez pas a contacter
l'un de nos conseillers au 04-86-31-80-70";$msg .= "<br><br>";

	$msg .= "<div align='justify'><b><font color='red'>Motif d'annulation du centre organisateur: ".$row_stagiaire['motif_annulation']."</font></b></div>";$msg .= "<br><br>";

	$msg .= "<u><b>CENTRE ORGANISATEUR:</b></u>";$msg .= "<br>";
	$msg .= "<table  width=\"100%\">";
	$msg .= "<tr>";
	$msg .= "<td width=\"23%\">";$msg .= "<em>Nom: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= stripslashes($row_stagiaire['membre_nom']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Adresse: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= stripslashes($row_stagiaire['membre_adresse']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Tel: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['membre_tel']."&nbsp;&nbsp;&nbsp;".$row_stagiaire['membre_mobile'];$msg .= "</td>";
	$msg .= "</tr>";
        
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Fax: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['membre_fax'];$msg .= "</td>";
	$msg .= "</tr>";
        
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Email: </em>";$msg .= "</td>";
        
        $membre_email = $row_stagiaire['membre_email'];
        
        if ($row_stagiaire['membre_id'] == 269 || $row_stagiaire['membre_id'] == 270 || $row_stagiaire['membre_id'] == 271 || $row_stagiaire['membre_id'] == 272 || $row_stagiaire['membre_id'] == 273 || $row_stagiaire['membre_id'] == 274 || $row_stagiaire['membre_id'] == 275 || $row_stagiaire['membre_id'] == 276 || $row_stagiaire['membre_id'] == 277) {// A.N.P.E.R
            
            $arr_email = preg_split("/(,|;)/",$membre_email);
            
            if (!empty($arr_email))
                $membre_email = $arr_email[0];
        }
        
        $msg .= "<td>"; $msg .= $membre_email; $msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "</table>";

	$msg .= "<br>";

	$msg .= "<u><b>STAGE SELECTIONNE:</b></u>";$msg .= "<br>";
	$msg .= "<table  width=\"100%\">";
	$msg .= "<tr>";
	$msg .= "<td width=\"23%\">";$msg .= "<em>Dates: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= MySQLDateToExplicitDate($row_stagiaire['date1'])." et ".MySQLDateToExplicitDate($row_stagiaire['date2']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Horaires: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['debut_am']."-".$row_stagiaire['fin_am']." et ".$row_stagiaire['debut_pm']."-".$row_stagiaire['fin_pm'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Lieu: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= filter($row_stagiaire['site_nom']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Adresse: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= filter($row_stagiaire['site_adresse']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td> </td>";
	$msg .= "<td>";$msg .= $row_stagiaire['site_codepostal']." ".filter($row_stagiaire['site_ville']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Prix: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['paiement']." euros";$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "</table>";

	$msg .= "<br>";

	$msg .= "<u><b>STAGIAIRE:</b></u>";$msg .= "<br>";
	$msg .= "<table  width=\"100%\">";
	$msg .= "<tr>";
	$msg .= "<td width=\"23%\">";$msg .= "<em>Identité: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= stripslashes($row_stagiaire['nom'])." ".stripslashes($row_stagiaire['prenom']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Adresse: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= stripslashes($row_stagiaire['adresse'])."\t\n".$row_stagiaire['code_postal']." ".stripslashes($row_stagiaire['ville']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Tél: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['tel']." ".$row_stagiaire['mobile'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Email: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['email'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Permis: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['num_permis']." (le ".$row_stagiaire['date_permis']." à ".$row_stagiaire['lieu_permis'].")";$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Type de stage: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['cas'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "</table>";

	$msg .= "<br>";
	$msg .= "<br><br>";
	$msg .= "Mailys,<br>";
	$msg .= "Conseillère Client ProStagesPermis<br>";
	$msg .= "04-34-09-12-67 (11h-12h30 et 13h30-14h30 du lundi au vendredi)<br>";
	$msg .= "contact@prostagespermis.fr";

        $msg_pro = "<div align='center'><u><b><font color='red'>ANNULATION DE STAGE $boost_entete(LE ".$aujourdui.")</font></b></u></div>" . $msg;

        $msg = "<div align='center'><u><b><font color='red'>ANNULATION DE STAGE (LE ".$aujourdui.")</font></b></u></div>" . $msg;

	$msg_contact = $msg_pro;
	
	if ($row_stagiaire['id_autoecole'] != 0 && $row_stagiaire['id_autoecole'] != NULL)
	{
		$msg_contact .= "<u><b>AUTOECOLE:</b></u>";$msg_contact .= "<br>";
		$msg_contact .= "<table  width=\"100%\">";
		$msg_contact .= "<tr>";
		$msg_contact .= "<td width=\"23%\">";$msg_contact .= "<em>ID Autoecole: </em>";$msg_contact .= "</td>";
		$msg_contact .= "<td>";$msg_contact .= $row_stagiaire['id_autoecole'];$msg_contact .= "</td>";
		$msg_contact .= "</tr>";
		$msg_contact .= "<tr>";
		$msg_contact .= "<td>";$msg_contact .= "<em>Commission: </em>";$msg_contact .= "</td>";
		$msg_contact .= "<td>";$msg_contact .= $row_stagiaire['comm_autoecole'];$msg_contact .= "</td>";
		$msg_contact .= "</tr>";
		$msg_contact .= "</table>";	
	}	
	
	
	$headers = "From: PROStagesPermis <contact@prostagespermis.fr>\n";
	$headers .= "Reply-To: ".$contact."\n";
	$headers .= 'MIME-version: 1.0'."\n";
	$headers .= 'Content-type: text/html; charset= iso-8859-1'."\n";

	mail_externe_bootstrap_function_aff($to, $subject, $msg, $headers);
	sleep(1);
	mail_externe_bootstrap_function_aff($row_stagiaire['membre_email'], $subject_pro, $msg_pro, $headers);
	sleep(1);
	mail_externe_bootstrap_function_aff($contact, $paiement_interne.$autoecole.$subject_pro." - ".stripslashes($row_stagiaire['membre_nom']) . ' ('.$row_stagiaire['membre_id'].')', $msg_contact, $headers);
}



function transfert_toto($id_stagiaire, $old_stage, $new_stage)
{

	require_once("../../common/functions.php");
	include ("../../connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	$aujourdui = date("d-m-y");
        
	//requete
	$query_stagiaire = "SELECT stagiaire.* FROM stagiaire WHERE stagiaire.id = $id_stagiaire";

	$rsStagiaire = mysql_query($query_stagiaire, $stageconnect) or die(mysql_error());
	$row_stagiaire = mysql_fetch_assoc($rsStagiaire);
//	$totalRows_stagiaire = mysql_num_rows($rsStagiaire);
        
	$sql = "UPDATE stagiaire SET id_stage = $new_stage, supprime=0 WHERE id = $id_stagiaire";
	mysql_query($sql) or die('Erreur SQL !'.$sql.'<br />'.mysql_error());
        
	$row_stagiaire['id_stage'] = $new_stage;
	$row_stagiaire['supprime'] = 0;
        
        $boost_entete = '';
        if ($row_stagiaire['option_reversement'] == '2')
            $boost_entete = 'BOOSTÉ ';

        
	if ($row_stagiaire['status'] == "supprime")
	{
		$sql = "UPDATE stagiaire SET status = \"pre-inscrit\" WHERE id = $id_stagiaire";
		mysql_query($sql) or die('Erreur SQL !'.$sql.'<br />'.mysql_error());
	}
        
	//requete new stage
	$query_stage = "SELECT
                                stage.*, site.nom, site.ville, site.adresse, site.code_postal, site.departement
                        FROM stage, site

                        WHERE stage.id = $new_stage AND stage.id_site = site.id";

	$rsStage = mysql_query($query_stage, $stageconnect) or die(mysql_error());
	$row_stage = mysql_fetch_assoc($rsStage);
	$totalRows_stage = mysql_num_rows($rsStage);

        
//        if ($update_montant) {
//            $sql = "UPDATE stagiaire SET paiement = $row_stage[prix] WHERE id = $id_stagiaire";
//            mysql_query($sql) or die('Erreur SQL !'.$sql.'<br />'.mysql_error());
//        }
        
	//requete membre
	$membreID = $row_stage['id_membre'];

	$query_membre = "SELECT membre.* FROM membre WHERE membre.id = $membreID";

	$rsMembre = mysql_query($query_membre, $stageconnect) or die(mysql_error());
	$row_membre = mysql_fetch_assoc($rsMembre);
	$totalRows_membre = mysql_num_rows($rsMembre);
	
	//changement d'adresse pour centre ICI STAGES FORMA EST (289)
	if ($row_stage['id_membre'] == 289 && ($row_stage['departement'] == 54 || $row_stage['departement'] == 67))
	{
		$row_membre['nom'] = "FORMA EST";
		$row_membre['tel'] = "09.84.37.52.68";
		$row_membre['mobile'] = "03.67.260.350";
		$row_membre['adresse'] = "2 rue Nelly SACHS, 67200 Strasbourg";
	}
        
	//requete transaction
	$query_transaction = "SELECT transaction.* FROM transaction WHERE

							transaction.id_stage = $old_stage AND
							transaction.id_stagiaire = $id_stagiaire";

	$rsTransaction = mysql_query($query_transaction, $stageconnect) or die(mysql_error());
	$row_transaction = mysql_fetch_assoc($rsTransaction);
	$totalRows_transaction = mysql_num_rows($rsTransaction);
	$id_transaction = $row_transaction['id'];

	$sql = "UPDATE transaction SET id_stage = $new_stage WHERE id = $id_transaction AND id_stagiaire=$id_stagiaire";
	mysql_query($sql) or die('Erreur SQL !'.$sql.'<br />'.mysql_error());

        
//        if ($membreID == 188 && $row_stagiaire['supprime'] != 1) {
        if ($row_stagiaire['supprime'] != 1) {
            $update_stage = '';
            $update_sep = '';
            
            if ($old_stage != $new_stage) {
                $update_stage = 'nb_places_allouees = nb_places_allouees +1';
                $update_sep = ',';
            }
            
            // MAJ du compteur boosts désactivée !
            // Parce que le centre peut regagner une place boostable à chaque transfert
            if (FALSE && $row_stagiaire['option_reversement'] == '2')
                $update_stage .= $update_sep.'nb_boost = nb_boost -1, nb_boost_allouees = nb_boost_allouees +1';
            
            if ($update_stage != '') {
                mysql_query("UPDATE stage SET $update_stage WHERE id = $old_stage") or die('Erreur SQL !'.$sql.'<br />'.mysql_error());
            }
        }
        
        
	//envoi du mail
	//-------------
	$to = $row_stagiaire['email'];
	$contact = "contact@prostagespermis.fr";
        
	$subject = "Transfert de stage: ".$row_stagiaire['nom']." ".$row_stagiaire['prenom'];
        
	$subject_pro = "Transfert de stage $boost_entete: ".$row_stagiaire['nom']." ".$row_stagiaire['prenom'];

	$msg = "<br><br>";
        
	$msg .= "Vous venez d'être transféré sur un nouveau stage de récupération de points dont les informations
complètes figurent ci-dessous. Nous sommes désolés pour l'éventuelle gène occasionnée.";$msg .= "<br><br>";


	$msg .= "<u><b>CENTRE ORGANISATEUR:</b></u>";$msg .= "<br>";
	$msg .= "<table  width=\"100%\">";
	$msg .= "<tr>";
	$msg .= "<td width=\"23%\">";$msg .= "<em>Nom: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= stripslashes($row_membre['nom']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Adresse: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= stripslashes($row_membre['adresse']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
        
	$msg .= "<td>";$msg .= "<em>Tel: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_membre['tel']."&nbsp;&nbsp;&nbsp;".$row_membre['mobile'];$msg .= "</td>";
	$msg .= "</tr>";
        
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Fax: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_membre['fax'];$msg .= "</td>";
	$msg .= "</tr>";
        
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Email: </em>";$msg .= "</td>";
        
        $membre_email = $row_membre['email'];
        
        if ($row_membre['id'] == 269 || $row_membre['id'] == 270 || $row_membre['id'] == 271 || $row_membre['id'] == 272 || $row_membre['id'] == 273 || $row_membre['id'] == 274 || $row_membre['id'] == 275 || $row_membre['id'] == 276 || $row_membre['id'] == 277) {// A.N.P.E.R
            
            $arr_email = preg_split("/(,|;)/",$membre_email);
            
            if (!empty($arr_email))
                $membre_email = $arr_email[0];
        }
        
        $msg .= "<td>"; $msg .= $membre_email; $msg .= "</td>";
	$msg .= "</tr>";
        
//	$msg .= "<td>";$msg .= "<em>Tel: </em>";$msg .= "</td>";
//	$msg .= "<td>";$msg .= $row_membre['tel']."  ".$row_membre['mobile'];$msg .= "</td>";
//	$msg .= "</tr>";
//	$msg .= "<tr>";
//	$msg .= "<td>";$msg .= "<em>Email: </em>";$msg .= "</td>";
//	$msg .= "<td>";$msg .= $row_membre['email']."  Fax: ".$row_membre['fax'];$msg .= "</td>";
//	$msg .= "</tr>";
	
        $msg .= "</table>";


	$msg .= "<br><u><b>STAGIAIRE:</b></u>";$msg .= "<br>";
	$msg .= "<table  width=\"100%\">";
	$msg .= "<tr>";
	$msg .= "<td width=\"23%\">";$msg .= "<em>Identite: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['civilite']." ".stripslashes($row_stagiaire['nom']." ".$row_stagiaire['prenom']);$msg .= "</td>";
	$msg .= "</tr>";

	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Statut: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['status'];$msg .= "</td>";
	$msg .= "</tr>";

	if ($row_stagiaire['jeune_fille'] != "")
	{
		$msg .= "<tr>";
		$msg .= "<td>";$msg .= "<em>Nom de jeune fille: </em>";$msg .= "</td>";
		$msg .= "<td>";$msg .= $row_stagiaire['jeune_fille'];$msg .= "</td>";
		$msg .= "</tr>";
	}
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Ne(e) le: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= datefr($row_stagiaire['date_naissance'])." a ".stripslashes($row_stagiaire['lieu_naissance']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Adresse: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= stripslashes($row_stagiaire['adresse'])."<br>".$row_stagiaire['code_postal']." ".stripslashes($row_stagiaire['ville']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Coordonnees: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= "Tel:".$row_stagiaire['tel']." ".$row_stagiaire['mobile']." Email: ".$row_stagiaire['email'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Permis: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['num_permis']." le ".datefr($row_stagiaire['date_permis'])." a ".stripslashes($row_stagiaire['lieu_permis']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Type de stage: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['cas'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Paiement: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['paiement']." euros";
	if ($row_transaction['type_paiement'] == "CB_OK") {$msg .= "<b> (réglé par CB)</b>";}
	else if ($row_stagiaire['status'] == "inscrit") {$msg .= "<b> (réglé par chèque)</b>";}
	else if ($row_stagiaire['status'] == "pre-inscrit") {$msg .= "<b> (règlement en attente)</b>";}
	$msg .= "</td>";
	$msg .= "</tr>";

	if (($row_membre['id'] == 64 || $row_membre['id'] == 38) && $row_stagiaire['cas'] == 2)
	{
		$msg .= "<tr>";
		$msg .= "<td>";$msg .= "<em>Infraction: </em>";$msg .= "</td>";
		$msg .= "<td>";$msg .= $row_stagiaire['motif_infraction']." - ".$row_stagiaire['date_infraction'];$msg .= "</td>";
		$msg .= "</tr>";
		$msg .= "<tr>";
		$msg .= "<td>";$msg .= "<em>Reception lettre 48N: </em>";$msg .= "</td>";
		$msg .= "<td>";$msg .= $row_stagiaire['date_lettre'];$msg .= "</td>";
		$msg .= "</tr>";
	}
	$msg .= "</table>";

	$msg .= "<br><u><b>DETAILS DU NOUVEAU STAGE:</b></u>";$msg .= "<br>";
	$msg .= "<table  width=\"100%\">";
	$msg .= "<tr>";
	$msg .= "<td width=\"23%\">";$msg .= "<em>Dates: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= MySQLDateToExplicitDate($row_stage['date1'])." et ".MySQLDateToExplicitDate($row_stage['date2']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Horaires: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stage['debut_am']." ".$row_stage['fin_am']." et ".$row_stage['debut_pm']." ".$row_stage['fin_pm'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Lieu: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= filter($row_stage['nom'])."<br>".filter($row_stage['adresse'])."<br>".$row_stage['code_postal']." ".filter($row_stage['ville']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Prix: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['paiement']." euros";$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "</table>";
	$msg .= "<br>";



	if ($row_stagiaire['status'] != "inscrit")
	{

		$msg .= "<table  width=\"100%\">";
		$msg .= "<tr>";
		$msg .= "<td>";
		$msg .= "Merci de retourner par courrier votre fiche de pre-inscription (etablie depuis notre site internet) ou ce mail, de le dater, de le signer et d'y joindre imperativement les pieces suivantes:";
		$msg .= "<ul>";
		$msg .= "<li> La photocopie de l'interieur de votre permis de conduire (cote avec votre photo) ou, en cas de suspension, la notification,</li>";


		$msg .= "<li> Un cheque de ".$row_stage['paiement']." euros a l'ordre de ".stripslashes($row_membre['nom']);$msg .= "</li>";

		$msg .= "<li> Attention jeunes conducteurs: si 48 N, joindre la photocopie (recto/verso)";$msg .= "</li>";
		$msg .= "</ul>";
		$msg .= "<br>";

		$msg .= "<div align='center'>";
		$msg .= "<u><b>RETOURNEZ VOTRE DOSSIER COMPLET A L'ADRESSE SUIVANTE:</b></u>";$msg .= "<br>";
		$msg .= "     ".stripslashes($row_membre['nom']);$msg .= "<br>";
		$msg .= "     ".stripslashes($row_membre['adresse']);$msg .= "<br><br>";
		$msg .= "</div>";

		$msg .= "Votre inscription  devient definitive si votre dossier complet est recu dans les ";
		if (strtotime($row_stage['date1']) - strtotime("now") < 518400)
		{
			$msg .= "48H suivants votre pre-inscription (date de ce courrier).";
		}
		else
		{
			$msg .= "4 jours suivants votre pre-inscription (date de ce courrier).";
		}
		$msg .= "A reception de votre dossier complet, vous recevrez un email confirmant votre inscription definitive.";$msg .= "<br><br>";


		$msg .= "BON POUR ACCORD. Date et signature:";
		$msg .= "</td>";
		$msg .= "</tr>";
		$msg .= "</table>";
	}

	$msg .= "<br><br>";
	$msg .= "Mailys,<br>";
	$msg .= "Conseillère Client ProStagesPermis<br>";
	$msg .= "04-34-09-12-67 (11h-12h30 et 13h30-14h30 du lundi au vendredi)<br>";
	$msg .= "contact@prostagespermis.fr";	
	
	$msg_pro  = "<div align='center'><b><font color='red'>TRANSFERT DE STAGE $boost_entete(le ".$aujourdui.")</font></b></div>" . $msg;
        
	$msg  = "<div align='center'><b><font color='red'>TRANSFERT DE STAGE (le ".$aujourdui.")</font></b></div>" . $msg;
        
	$headers = "From: PROStagesPermis <contact@prostagespermis.fr>\n";
	$headers .= "Reply-To: ".$contact."\n";
	$headers .= 'MIME-version: 1.0'."\n";
	$headers .= 'Content-type: text/html; charset= iso-8859-1'."\n";

	mail_externe_bootstrap_function_aff($to, $subject, $msg, $headers);
	sleep(1);
	mail_externe_bootstrap_function_aff($row_membre['email'], $subject_pro, $msg_pro, $headers);
	sleep(1);
	mail_externe_bootstrap_function_aff($contact, $subject_pro." - ".stripslashes($row_membre['nom']), $msg_pro, $headers);

}


function inscrire($id_stagiaire)
{
	require_once("../../common/functions.php");
	include ("../../connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	$aujourdui = date("d-m-y");

	//requete
	$query_stagiaire = "SELECT 	stagiaire.*,

								site.nom AS site_nom,
								site.ville AS site_ville,
								site.adresse AS site_adresse,
								site.code_postal AS site_codepostal,

								stage.date1 AS date1,
								stage.date2 AS date2,
								stage.prix AS prix,
								stage.debut_am AS debut_am,
								stage.fin_am AS fin_am,
								stage.debut_pm AS debut_pm,
								stage.fin_pm AS fin_pm,

								membre.nom AS membre_nom,
								membre.adresse AS membre_adresse,
								membre.email AS membre_email,
								membre.tel AS membre_tel,
								membre.mobile AS membre_mobile,
								membre.fax AS membre_fax,
								membre.id AS membre_id

						FROM stagiaire, stage, site, membre

						WHERE
								stagiaire.id = $id_stagiaire AND
								stagiaire.id_stage = stage.id AND
								stage.id_site = site.id AND
								stage.id_membre = membre.id";

	$rsStagiaire = mysql_query($query_stagiaire, $stageconnect) or die(mysql_error());
	$row_stagiaire = mysql_fetch_assoc($rsStagiaire);
	$totalRows_stagiaire = mysql_num_rows($rsStagiaire);

	if ($totalRows_stagiaire != 1)
	{
		echo "Erreur: impossible d'inscrire le stagiaire car aucun resultat de requete. Contactez notre hotline !";
		exit;
	}


	//envoi du mail
	//-------------
	$to = $row_stagiaire['email'];
	$contact = "contact@prostagespermis.fr";

	$subject = "Validation inscription définitive: ".stripslashes($row_stagiaire['nom'])." ".stripslashes($row_stagiaire['prenom']);

	$msg  = "<div align='center'><u><b><font color='green'>VALIDATION D'INSCRIPTION DEFINITIVE (LE ".$aujourdui.")</font></b></u></div>";$msg .= "<br>";

	$msg .= "<table  width='100%\' style='font-size:12px;border:inset'>";
	$msg .= "<tr>";
	$msg .= "<td width=\"23%\">";$msg .= "<em>Stagiaire: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= stripslashes($row_stagiaire['nom'])." ".stripslashes($row_stagiaire['prenom'])." (".substr($row_stagiaire['cas'],0,5).")";$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Coordonnées: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['tel']."\t".$row_stagiaire['mobile']."\t".$row_stagiaire['email'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Permis: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['num_permis'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Stage: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= MySQLDateToExplicitDate($row_stagiaire['date1'])." ".filter($row_stagiaire['site_nom'])." ".filter($row_stagiaire['site_ville'])." (".$row_stagiaire['paiement']." euros)";$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "</table>";

	$msg .= "<br>Nous accusons réception de votre dossier validant votre inscription définitive à votre stage de récuperation de points
via la centrale de réservation PROStagesPermis.";$msg .= "<br><br>";

	$msg .= "<u><b>CENTRE ORGANISATEUR:</b></u>";$msg .= "<br>";
	$msg .= "<table  width=\"100%\">";
	$msg .= "<tr>";
	$msg .= "<td width=\"23%\">";$msg .= "<em>Nom: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= stripslashes($row_stagiaire['membre_nom']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Adresse: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= stripslashes($row_stagiaire['membre_adresse']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Tel: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['membre_tel']."  ".$row_stagiaire['membre_mobile'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Email: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['membre_email']."  Fax: ".$row_stagiaire['membre_fax'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "</table>";

	$msg .= "<br>";

	$msg .= "<u><b>STAGE SELECTIONNE:</b></u>";$msg .= "<br>";
	$msg .= "<table  width=\"100%\">";
	$msg .= "<tr>";
	$msg .= "<td width=\"23%\">";$msg .= "<em>Dates: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= MySQLDateToExplicitDate($row_stagiaire['date1'])." et ".MySQLDateToExplicitDate($row_stagiaire['date2']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Horaires: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['debut_am']."-".$row_stagiaire['fin_am']." et ".$row_stagiaire['debut_pm']."-".$row_stagiaire['fin_pm'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Lieu: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= filter($row_stagiaire['site_nom']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Adresse: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= filter($row_stagiaire['site_adresse']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td> </td>";
	$msg .= "<td>";$msg .= $row_stagiaire['site_codepostal']." ".filter($row_stagiaire['site_ville']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Prix: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['paiement']." euros";$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "</table>";

	$msg .= "<br>";

	$msg .= "<u><b>STAGIAIRE:</b></u>";$msg .= "<br>";
	$msg .= "<table  width=\"100%\">";
	$msg .= "<tr>";
	$msg .= "<td width=\"23%\">";$msg .= "<em>Identité: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= stripslashes($row_stagiaire['nom'])." ".stripslashes($row_stagiaire['prenom']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Adresse: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= stripslashes($row_stagiaire['adresse'])."\t\n".$row_stagiaire['code_postal']." ".stripslashes($row_stagiaire['ville']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Tél: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['tel']." ".$row_stagiaire['mobile'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Email: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['email'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Permis: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['num_permis']." (le ".$row_stagiaire['date_permis']." à ".$row_stagiaire['lieu_permis'].")";$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Type de stage: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['cas'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "</table>";

	$msg .= "<br>";

	if ($row_stagiaire['membre_id'] == 44)
	{

		$msg .= "<div align='justify'>";
		$msg .= "Rappel des Articles de vente 1 et 6:<br>";
		$msg .= "Article 1 : Capital points: ";
		$msg .= "Afin d’effectuer un stage de «Sécurité Routiere» cas n°1 (récupération de 4 points), le capital points du permis de conduire
doit etre au moins égal a 1 point et inférieur ou égal a 8 points. Dans le cas ou votre solde de points est nul mais que vous n'avez pas
réceptionné de lettre 48S, Il est obligatoire, avant de vous inscrire, de faire valider votre relevé intégral d'information par
un conseiller permis. Si le conducteur n’a jamais reçu de lettre (type 48, 48M ou 48N), il doit demander un relevé intégral
d’information dans une Préfecture ou Sous-Préfecture. En cas de fausse déclaration la responsabilité de PROStagesPermis et de
l’organisateur du stage ne pourra en aucun cas etre engagée et le remboursement du stage sera impossible.<br><br>";

		$msg .= "Article 6 : Annulation inscription: ";
		$msg .= "En cas d’absence (quelque en soit la cause) signalée entre 7 jours et 4 jours ouvrables avant le début du stage,
les frais administratifs occasionnés au centre organisateur seront facturés 50€. Si l’absence est signalée 4 jours ouvrables avant le
stage (quelque en soit la cause), le prix de la formation reste entierement du. Dans tous les cas de remboursement, il sera déduit des
frais de traitement de 5,00€. La validation de la commande vaut acceptation de ces conditions d’annulation. Toute demande d’annulation
devra etre faite par lettre recommandée au centre organisateur.<br><br>";
	}


	$msg .= "<b>A tres bientôt pour votre stage de récupération de points de permis (attention à  bien respecter les horaires sous peine de ne pas
pouvoir assister au stage)</b>";


	$headers = "From: PROStagesPermis <contact@prostagespermis.fr>\n";
	$headers .= "Reply-To: ".$contact."\n";
	$headers .= 'MIME-version: 1.0'."\n";
	$headers .= 'Content-type: text/html; charset= iso-8859-1'."\n";

	mail_externe_bootstrap_function_aff($to, $subject, $msg, $headers);
	sleep(1);
	mail_externe_bootstrap_function_aff($row_stagiaire['membre_email'], $subject, $msg, $headers);
	sleep(1);
	mail_externe_bootstrap_function_aff($contact, $subject." - ".stripslashes($row_stagiaire['membre_nom']), $msg, $headers);
}


function relance($id_stagiaire)
{
	require_once("../../common/functions.php");
	include ("../../connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	$aujourdui = date("d-m-y");

	//requete
	$query_stagiaire = "SELECT 	stagiaire.*,

								site.nom AS site_nom,
								site.ville AS site_ville,
								site.adresse AS site_adresse,
								site.code_postal AS site_codepostal,

								stage.date1 AS date1,
								stage.date2 AS date2,
								stage.prix AS prix,
								stage.debut_am AS debut_am,
								stage.fin_am AS fin_am,
								stage.debut_pm AS debut_pm,
								stage.fin_pm AS fin_pm,

								membre.nom AS membre_nom,
								membre.adresse AS membre_adresse,
								membre.email AS membre_email,
								membre.tel AS membre_tel,
								membre.mobile AS membre_mobile,
								membre.fax AS membre_fax,
								membre.id AS membre_id

						FROM stagiaire, stage, site, membre

						WHERE
								stagiaire.id = $id_stagiaire AND
								stagiaire.id_stage = stage.id AND
								stage.id_site = site.id AND
								stage.id_membre = membre.id";

	$rsStagiaire = mysql_query($query_stagiaire, $stageconnect) or die(mysql_error());
	$row_stagiaire = mysql_fetch_assoc($rsStagiaire);
	$totalRows_stagiaire = mysql_num_rows($rsStagiaire);

	if ($totalRows_stagiaire != 1)
	{
		echo "Erreur: impossible d'inscrire le stagiaire car aucun resultat de requete. Contactez notre hotline !";
		exit;
	}


	//envoi du mail
	//-------------
	$to = $row_stagiaire['email'];
	$contact = "contact@prostagespermis.fr";

	$subject = "Relance stage permis a points: ".stripslashes($row_stagiaire['nom'])." ".stripslashes($row_stagiaire['prenom']);

	$msg  = "<div align='center'><u><b><font color='green'>DOSSIER EN ATTENTE DE RECEPTION (LE ".$aujourdui.")</font></b></u></div>";$msg .= "<br><br>";


	$msg .= "<br><b>Bonjour, nous n'avons toujours pas reçu votre dossier complet pour finaliser votre pré-inscription comme mentionné
dans le premier mail. Merci d'envoyer au plus tôt toutes les pièces nécessaires si vous désirez conserver le bénéfice de votre réservation à l'adresse de
notre centre organisateur ci-dessous. Attention, dans le cas contraire votre place risque d'être rapidement supprimée.</b>";$msg .= "<br><br>";

	$msg .= "<u><b>CENTRE ORGANISATEUR:</b></u>";$msg .= "<br>";
	$msg .= "<table  width=\"100%\">";
	$msg .= "<tr>";
	$msg .= "<td width=\"23%\">";$msg .= "<em>Nom: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= stripslashes($row_stagiaire['membre_nom']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Adresse: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= stripslashes($row_stagiaire['membre_adresse']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Tel: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['membre_tel']."  ".$row_stagiaire['membre_mobile'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Email: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['membre_email']."  Fax: ".$row_stagiaire['membre_fax'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "</table>";

	$msg .= "<br>";

	$msg .= "<u><b>STAGE SELECTIONNE:</b></u>";$msg .= "<br>";
	$msg .= "<table  width=\"100%\">";
	$msg .= "<tr>";
	$msg .= "<td width=\"23%\">";$msg .= "<em>Dates: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= MySQLDateToExplicitDate($row_stagiaire['date1'])." et ".MySQLDateToExplicitDate($row_stagiaire['date2']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Horaires: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['debut_am']."-".$row_stagiaire['fin_am']." et ".$row_stagiaire['debut_pm']."-".$row_stagiaire['fin_pm'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Lieu: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= filter($row_stagiaire['site_nom']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Adresse: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= filter($row_stagiaire['site_adresse']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td> </td>";
	$msg .= "<td>";$msg .= $row_stagiaire['site_codepostal']." ".filter($row_stagiaire['site_ville']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Prix: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['paiement']." euros";$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "</table>";

	$msg .= "<br>";

	$msg .= "<u><b>STAGIAIRE:</b></u>";$msg .= "<br>";
	$msg .= "<table  width=\"100%\">";
	$msg .= "<tr>";
	$msg .= "<td width=\"23%\">";$msg .= "<em>Identité: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= stripslashes($row_stagiaire['nom'])." ".stripslashes($row_stagiaire['prenom']);$msg .= "</td>";
	$msg .= "</tr>";
	/*
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Adresse: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= stripslashes($row_stagiaire['adresse'])."\t\n".$row_stagiaire['code_postal']." ".stripslashes($row_stagiaire['ville']);$msg .= "</td>";
	$msg .= "</tr>";
	*/
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Tél: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['tel']." ".$row_stagiaire['mobile'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Email: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['email'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Permis: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['num_permis']." (le ".$row_stagiaire['date_permis']." à ".$row_stagiaire['lieu_permis'].")";$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Type de stage: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['cas'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "</table>";

	$msg .= "<br>";


	$mail_membre = $row_stagiaire['membre_email'];

	$headers = "From: PROStagesPermis <contact@prostagespermis.fr>\n";
	$headers .= "Reply-To: ".$mail_membre."\n";
	$headers .= 'MIME-version: 1.0'."\n";
	$headers .= 'Content-type: text/html; charset= iso-8859-1'."\n";

	mail_externe_bootstrap_function_aff($to, $subject, $msg, $headers);
	sleep(1);
	mail_externe_bootstrap_function_aff($mail_membre, $subject, $msg, $headers);
	sleep(1);
	mail_externe_bootstrap_function_aff($contact, $subject." - ".stripslashes($row_stagiaire['membre_nom']), $msg, $headers);
}

/*
function transfert($id_stagiaire, $old_stage, $new_stage)
{

	require_once("../../common/functions.php");
	include ("../../connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	$aujourdui = date("d-m-y");

	$sql = "UPDATE stagiaire SET id_stage = $new_stage, supprime=0 WHERE id = $id_stagiaire";
	mysql_query($sql) or die('Erreur SQL !'.$sql.'<br />'.mysql_error());

	//requete
	$query_stagiaire = "SELECT stagiaire.* FROM stagiaire WHERE stagiaire.id = $id_stagiaire";

	$rsStagiaire = mysql_query($query_stagiaire, $stageconnect) or die(mysql_error());
	$row_stagiaire = mysql_fetch_assoc($rsStagiaire);
	$totalRows_stagiaire = mysql_num_rows($rsStagiaire);

	if ($row_stagiaire['status'] == "supprime")
	{
		$sql = "UPDATE stagiaire SET status = \"pre-inscrit\" WHERE id = $id_stagiaire";
		mysql_query($sql) or die('Erreur SQL !'.$sql.'<br />'.mysql_error());
	}

	//requete new stage
	$query_stage = "SELECT
						stage.*, site.nom, site.ville, site.adresse, site.code_postal
					FROM stage, site

					WHERE stage.id = $new_stage AND stage.id_site = site.id";

	$rsStage = mysql_query($query_stage, $stageconnect) or die(mysql_error());
	$row_stage = mysql_fetch_assoc($rsStage);
	$totalRows_stage = mysql_num_rows($rsStage);
        
	//requete membre
	$membreID = $row_stage['id_membre'];

	$query_membre = "SELECT membre.* FROM membre WHERE membre.id = $membreID";

	$rsMembre = mysql_query($query_membre, $stageconnect) or die(mysql_error());
	$row_membre = mysql_fetch_assoc($rsMembre);
	$totalRows_membre = mysql_num_rows($rsMembre);

	//requete transaction
	$query_transaction = "SELECT transaction.* FROM transaction WHERE

							transaction.id_stage = $old_stage AND
							transaction.id_stagiaire = $id_stagiaire";

	$rsTransaction = mysql_query($query_transaction, $stageconnect) or die(mysql_error());
	$row_transaction = mysql_fetch_assoc($rsTransaction);
	$totalRows_transaction = mysql_num_rows($rsTransaction);
	$id_transaction = $row_transaction['id'];

	$sql = "UPDATE transaction SET id_stage = $new_stage WHERE id = $id_transaction AND id_stagiaire=$id_stagiaire";
	mysql_query($sql) or die('Erreur SQL !'.$sql.'<br />'.mysql_error());


        if ($membreID == 188) {
            $sql = "UPDATE stage SET nb_boost = nb_boost -1, nb_boost_allouees = nb_boost_allouees +1 WHERE id = $old_stage";
            mysql_query($sql) or die('Erreur SQL !'.$sql.'<br />'.mysql_error());
        }
        
	//envoi du mail
	//-------------
	$to = $row_stagiaire['email'];
	$contact = "contact@prostagespermis.fr";

	$subject = "Transfert de stage: ".$row_stagiaire['nom']." ".$row_stagiaire['prenom'];

	$msg  = "<div align='center'><b><font color='red'>TRANSFERT DE STAGE (le ".$aujourdui.")</font></b></div>";$msg .= "<br><br>";

	$msg .= "Vous venez d'être transféré sur un nouveau stage de récupération de points dont les informations
complètes figurent ci-dessous. Nous sommes désolés pour l'éventuelle gène occasionnée.";$msg .= "<br><br>";


	$msg .= "<u><b>CENTRE ORGANISATEUR:</b></u>";$msg .= "<br>";
	$msg .= "<table  width=\"100%\">";
	$msg .= "<tr>";
	$msg .= "<td width=\"23%\">";$msg .= "<em>Nom: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= stripslashes($row_membre['nom']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Adresse: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= stripslashes($row_membre['adresse']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Tel: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_membre['tel']."  ".$row_membre['mobile'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Email: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_membre['email']."  Fax: ".$row_membre['fax'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "</table>";


	$msg .= "<br><u><b>STAGIAIRE:</b></u>";$msg .= "<br>";
	$msg .= "<table  width=\"100%\">";
	$msg .= "<tr>";
	$msg .= "<td width=\"23%\">";$msg .= "<em>Identite: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['civilite']." ".stripslashes($row_stagiaire['nom']." ".$row_stagiaire['prenom']);$msg .= "</td>";
	$msg .= "</tr>";

	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Statut: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['status'];$msg .= "</td>";
	$msg .= "</tr>";

	if ($row_stagiaire['jeune_fille'] != "")
	{
		$msg .= "<tr>";
		$msg .= "<td>";$msg .= "<em>Nom de jeune fille: </em>";$msg .= "</td>";
		$msg .= "<td>";$msg .= $row_stagiaire['jeune_fille'];$msg .= "</td>";
		$msg .= "</tr>";
	}
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Ne(e) le: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= datefr($row_stagiaire['date_naissance'])." a ".stripslashes($row_stagiaire['lieu_naissance']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Adresse: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= stripslashes($row_stagiaire['adresse'])."<br>".$row_stagiaire['code_postal']." ".stripslashes($row_stagiaire['ville']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Coordonnees: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= "Tel:".$row_stagiaire['tel']." ".$row_stagiaire['mobile']." Email: ".$row_stagiaire['email'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Permis: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['num_permis']." le ".datefr($row_stagiaire['date_permis'])." a ".stripslashes($row_stagiaire['lieu_permis']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Type de stage: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['cas'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Paiement: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['paiement']." euros";
	if ($row_transaction['type_paiement'] == "CB_OK") {$msg .= "<b> (réglé par CB)</b>";}
	else if ($row_stagiaire['status'] == "inscrit") {$msg .= "<b> (réglé par chèque)</b>";}
	else if ($row_stagiaire['status'] == "pre-inscrit") {$msg .= "<b> (règlement en attente)</b>";}
	$msg .= "</td>";
	$msg .= "</tr>";

	if (($row_membre['id'] == 64 || $row_membre['id'] == 38) && $row_stagiaire['cas'] == 2)
	{
		$msg .= "<tr>";
		$msg .= "<td>";$msg .= "<em>Infraction: </em>";$msg .= "</td>";
		$msg .= "<td>";$msg .= $row_stagiaire['motif_infraction']." - ".$row_stagiaire['date_infraction'];$msg .= "</td>";
		$msg .= "</tr>";
		$msg .= "<tr>";
		$msg .= "<td>";$msg .= "<em>Reception lettre 48N: </em>";$msg .= "</td>";
		$msg .= "<td>";$msg .= $row_stagiaire['date_lettre'];$msg .= "</td>";
		$msg .= "</tr>";
	}
	$msg .= "</table>";

	$msg .= "<br><u><b>DETAILS DU NOUVEAU STAGE:</b></u>";$msg .= "<br>";
	$msg .= "<table  width=\"100%\">";
	$msg .= "<tr>";
	$msg .= "<td width=\"23%\">";$msg .= "<em>Dates: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= MySQLDateToExplicitDate($row_stage['date1'])." et ".MySQLDateToExplicitDate($row_stage['date2']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Horaires: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stage['debut_am']." ".$row_stage['fin_am']." et ".$row_stage['debut_pm']." ".$row_stage['fin_pm'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Lieu: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= filter($row_stage['nom'])."<br>".filter($row_stage['adresse'])."<br>".$row_stage['code_postal']." ".filter($row_stage['ville']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Prix: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stage['prix']." euros";$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "</table>";
	$msg .= "<br>";



	if ($row_stagiaire['status'] != "inscrit")
	{

		$msg .= "<table  width=\"100%\">";
		$msg .= "<tr>";
		$msg .= "<td>";
		$msg .= "Merci de retourner par courrier votre fiche de pre-inscription (etablie depuis notre site internet) ou ce mail, de le dater, de le signer et d'y joindre imperativement les pieces suivantes:";
		$msg .= "<ul>";
		$msg .= "<li> La photocopie de l'interieur de votre permis de conduire (cote avec votre photo) ou, en cas de suspension, la notification,</li>";


		$msg .= "<li> Un cheque de ".$row_stage['prix']." euros a l'ordre de ".stripslashes($row_membre['nom']);$msg .= "</li>";

		$msg .= "<li> Attention jeunes conducteurs: si 48 N, joindre la photocopie (recto/verso)";$msg .= "</li>";
		$msg .= "</ul>";
		$msg .= "<br>";

		$msg .= "<div align='center'>";
		$msg .= "<u><b>RETOURNEZ VOTRE DOSSIER COMPLET A L'ADRESSE SUIVANTE:</b></u>";$msg .= "<br>";
		$msg .= "     ".stripslashes($row_membre['nom']);$msg .= "<br>";
		$msg .= "     ".stripslashes($row_membre['adresse']);$msg .= "<br><br>";
		$msg .= "</div>";

		$msg .= "Votre inscription  devient definitive si votre dossier complet est recu dans les ";
		if (strtotime($row_stage['date1']) - strtotime("now") < 518400)
		{
			$msg .= "48H suivants votre pre-inscription (date de ce courrier).";
		}
		else
		{
			$msg .= "4 jours suivants votre pre-inscription (date de ce courrier).";
		}
		$msg .= "A reception de votre dossier complet, vous recevrez un email confirmant votre inscription definitive.";$msg .= "<br><br>";


		$msg .= "BON POUR ACCORD. Date et signature:";
		$msg .= "</td>";
		$msg .= "</tr>";
		$msg .= "</table>";
	}


	$headers = "From: PROStagesPermis <contact@prostagespermis.fr>\n";
	$headers .= "Reply-To: ".$contact."\n";
	$headers .= 'MIME-version: 1.0'."\n";
	$headers .= 'Content-type: text/html; charset= iso-8859-1'."\n";

	mail($row_membre['email'], $subject, $msg, $headers);
	sleep(1);
	mail($to, $subject, $msg, $headers);
	sleep(1);
	mail($contact, $intitule." ".$subject." - ".stripslashes($row_membre['nom']), $msg, $headers);

}
*/

?>