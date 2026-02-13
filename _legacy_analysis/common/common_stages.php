<?php
function tableau_stages($site, $departement = NULL, $ville = NULL, $code_postal = NULL)
{
	require_once ("../common/functions.php");

	if ($site == psp)
	{
		require_once('../common/flux_psp.php');
		process_flux();
	}

	$query_stage_add = "";
	$query_site_add = "";


	if ($departement != NULL)
	{
		if ($code_postal != NULL){	$query_stage_add = " AND code_postal = $code_postal ";}
		else if ($ville != NULL) {	$query_stage_add = " AND ville = '$ville' ";}

		if ($site == psp || $site == pap){	$query_site_add = " code_postal, ville ";}
		else {								$query_site_add = " ville ";}

		$query_stage = sprintf("

							SELECT 	date1,
									date2,
									nb_places_allouees,
									nb_inscrits,
									nb_preinscrits,
									prix,
									stage.id,
									stage.id_membre,
									motCle,
									nom,
									ville,
									adresse,
									plan,
									code_postal
							FROM
									stage,
									site
							WHERE
									departement = $departement AND
									id_site = site.id AND
									date1 > now() AND
									date1 < (CURDATE() + INTERVAL 60 DAY) AND
									annule = 0 AND
									nb_places_allouees > 0 AND
									stage.id_membre != 36
									%s

							UNION

							SELECT
									date1,
									date2,
									nb_places_allouees,
									nb_inscrits,
									nb_preinscrits,
									prix,
									stage_dyn.id,
									stage_dyn.id_membre,
									motCle,
									nom,
									ville,
									adresse,
									plan,
									code_postal,
									site_dyn.id_membre
							FROM
									stage_dyn,
									site_dyn
							WHERE
									departement = $departement AND
									id_site = site_dyn.id_externe AND
									date1 > now() AND
									date1 < (CURDATE() + INTERVAL 60 DAY) AND
									annule = 0 AND
									site_dyn.id_membre = stage_dyn.id_membre AND
									nb_places_allouees > 0
									%s

							ORDER BY date1 ASC", $query_stage_add, $query_stage_add);

		$query_site = sprintf(
					"SELECT DISTINCT %s FROM site, stage WHERE
							departement = $departement AND
							id_site = site.id AND
							date1 > now() AND
							date1 < (CURDATE() + INTERVAL 60 DAY) AND
							nb_places_allouees > 0 AND
							annule = 0 AND
							stage.id_membre != 36

					UNION

					SELECT DISTINCT %s FROM site_dyn, stage_dyn WHERE

							departement = $departement AND
							id_site = site_dyn.id_externe AND
							date1 > now() AND
							date1 < (CURDATE() + INTERVAL 60 DAY) AND
							nb_places_allouees > 0 AND
							annule = 0

					GROUP BY code_postal, ville ORDER BY ville ASC", $query_site_add, $query_site_add);

		affiche_stages($site, $departement, $ville, $code_postal, $query_site, $query_stage);
	}
}

function affiche_stages($site, $departement, $ville, $code_postal, $query_site, $query_rsStage)
{
	require_once ("../common/functions.php");
	require ("../connections/stageconnect.php");

	mysql_select_db($database_stageconnect, $stageconnect);

	$rsSite = mysql_query($query_site, $stageconnect) or die(mysql_error());
	$row_rsSite	= mysql_fetch_assoc($rsSite);
	$totalRows_rsSite = mysql_num_rows($rsSite);

	$rsStage = mysql_query($query_rsStage, $stageconnect) or die(mysql_error());
	$row_rsStage = mysql_fetch_assoc($rsStage);
	$totalRows_rsStage = mysql_num_rows($rsStage);

	mysql_close($stageconnect);


	//message accueil
	/*if ($site == psp && $totalRows_rsStage > 0)
	{
		$aujourdui = date("y-m-d");
		$aujourdui = MySQLDateToExplicitDate($aujourdui);

		echo "<font color='#333333'><div align='justify'>".$aujourdui.", nous proposons $totalRows_rsStage stages permis à points ";

		if ($ville != NULL)
		{
			echo "à ".$ville.". Tous nos stages de points de permis sur $ville sont agréés par la préfecture du $departement
			(".getDepartement($departement).") et vous permettent de récupérer 4 points de permis de conduire en 48H.";
		}
		else if ($departement != NULL)
		{

			echo "dans le département $departement (".getDepartement($departement)."). Réservez un stage de points de permis sur ";

			do
			{
				echo $row_rsSite['ville'].", ";

			}
			while ($row_rsSite = mysql_fetch_assoc($rsSite));

			echo "...etc pour récupérer 4 points de permis de conduire en 2 jours dans un de nos centres agréés par la préfecture du $departement.";

			mysql_data_seek($rsSite, 0);
			$row_rsSite = mysql_fetch_assoc($rsSite);
		}

		echo "</div></font><br>";
	}*/

	if ($ville != NULL)
	{
		echo "<h2>";
		echo "Récupération de points de permis sur $ville:";
		echo "</h2>";
	}
	else if ($departement != NULL)
	{

		echo "<h2>";
		echo "Récupération de points de permis ".getDepartement($departement).":";
		echo "</h2>";
	}



	//affichage menu déroulant
	//------------------------
	if ($totalRows_rsSite > 0 && $site == psp && $ville == NULL)
	{
		if ($site == psp || $site == pap) {	$cp_local = $row_rsSite['code_postal'];}
		else { $cp_local = NULL;}

		$tabUrl = getUrl($site, $departement, $ville, $code_postal, $row_rsSite['ville'], $cp_local);
		$url_departement = $tabUrl[3];

		$i = 0;

		echo "<div align='center'>";
		echo "<table width='100%'>";

		do
		{


				if (($i % 4) == 0) {echo "<tr height='40px'>";}

				if ($site == psp || $site == pap) {	$cp_local = $row_rsSite['code_postal'];}
				else { $cp_local = NULL;}

				$tabUrl = getUrl($site, $departement, $ville, $code_postal, $row_rsSite['ville'], $cp_local);

				$code_postal = sprintf("%05d",$code_postal);
				$urlVille = "recuperation-points-$ville-$code_postal-$departement.html";

				//balises titles
				$title = getTitleMotcle($motCle);
				$title1 = $title[0];
				$title2 = $title[1];

				//balise title pour la ville
				$titleVille = $title2;

				echo "<td style='font-size:10px' align='center' width='25%'><strong>".$tabUrl[0]."</strong></td>"; //valeur option pour liste deroulante

				$i++;

				if (($i % 4) == 0) {echo "</tr>";}

		}
		while ($row_rsSite = mysql_fetch_assoc($rsSite));

		echo "</table>";
		echo "</div><br>";
	}

	else if ($totalRows_rsSite > 0 && $site != psp)
	{
		if ($site == psp || $site == pap) {	$cp_local = $row_rsSite['code_postal'];}
		else { $cp_local = NULL;}

		$tabUrl = getUrl($site, $departement, $ville, $code_postal, $row_rsSite['ville'], $cp_local);
		$url_departement = $tabUrl[3];
		echo "<div align='center' class='Style3'>";
		echo "<select name='ville' onchange=\"MM_jumpMenu('parent',this,0)\">";
		echo "<option value = $url_departement>-=Sélectionnez la ville de votre choix=-</option>";

		do
		{

				if ($site == psp || $site == pap) {	$cp_local = $row_rsSite['code_postal'];}
				else { $cp_local = NULL;}

				$tabUrl = getUrl($site, $departement, $ville, $code_postal, $row_rsSite['ville'], $cp_local);
				echo $tabUrl[0]; //valeur option pour liste deroulante

		}
		while ($row_rsSite = mysql_fetch_assoc($rsSite));

		echo "</select>";
		echo "</div><br>";
	}
	else if ($totalRows_rsSite <= 0)
	{
		echo "Plus de stage disponible pour le moment à cet endroit";
	}

	//affichage du lien sur tous les stages du departement
	//----------------------------------------------------
	//if ($ville != NULL || $code_postal != NULL)
	if ($ville != NULL)
	{
		if ($site == psp || $site == pap) {	$cp_local = $row_rsSite['code_postal'];}
		else { $cp_local = NULL;}

		$tabUrl = getUrl($site, $departement, $ville, $code_postal, $row_rsSite['ville'], $cp_local);
		$url_departement = $tabUrl[3];
		echo "<a href=$url_departement><strong>-> CLIQUEZ ICI POUR AFFICHER TOUS LES STAGES DU DEPARTEMENT <-</strong></a><br><br>";
	}

	//affichage tableau de stages
	//---------------------------
	echo "<table width='100%' border='1' bgcolor='#444847'>";
	echo "<tr>";
	if ($site != psp)
	{
		echo	"<td width='30%' align='center'><font color='#FFFFFF'><strong>Dates</strong></font></td>";
		echo	"<td width='22%' align='center'><font color='#FFFFFF'><strong>Ville</strong></font></td>";
		echo	"<td width='25%' align='center'><font color='#FFFFFF'><strong>Lieu</strong></font></td>";
		echo	"<td width='15%' align='center'><font color='#FFFFFF'><strong>Réservation</strong></font></td>";
		echo	"<td width='8%' align='center'><font color='#FFFFFF'><strong>Prix</strong></font></td>";
	}

	if ($site == psp)
	{
		echo	"<td width='27%' align='center'><font color='#FFFFFF'><strong>Ville</strong></font></td>";
		echo	"<td width='33%' align='center'><font color='#FFFFFF'><strong>Lieu</strong></font></td>";
		echo	"<td width='15%' align='center'><font color='#FFFFFF'><strong>Dates</strong></font></td>";
		echo	"<td width='7%' align='center'><font color='#FFFFFF'><strong>Prix</strong></font></td>";
		echo	"<td width='18%' align='center'><font color='#FFFFFF'><strong>RESERVATION</strong></font></td>";
	}

	echo "</tr>";
	echo "</table>";

	if ($totalRows_rsStage > 0)
	{
	do
	{

		$dateLocal2 = datefr($row_rsStage['date1']);

		$tabUrl = getUrl($site, $departement, $ville, $code_postal, $row_rsStage['ville'],
		$row_rsStage['code_postal'], $row_rsStage['motCle'], $row_rsStage['id'], $row_rsStage['id_membre'], $dateLocal2);

		$option = $tabUrl[0];
		$title1 = $tabUrl[1];
		$title2 = $tabUrl[2];
		$urlDepartement = $tabUrl[3];
		$urlVille = $tabUrl[4];
		$urlReservation = $tabUrl[5];
		$titleReservation = $tabUrl[6]." ".$row_rsStage['ville']." (".sprintf('%05d',$row_rsStage['code_postal']).") ".$row_rsStage['id_membre'];
		$texteReservation = $tabUrl[7];
		$titleVille = $tabUrl[8]." ".$row_rsStage['ville']." (".sprintf('%05d',$row_rsStage['code_postal']).")";

		echo "<table width='100%' border='1'>";
		echo "<tr bgcolor=\""; echo switchcolor($site); echo "\">";
		$places = $row_rsStage['nb_places_allouees'] - ($row_rsStage['nb_inscrits'] + $row_rsStage['nb_preinscrits']);
		if ($row_rsStage['nb_places_allouees'] < 5)
		{
			$text = "- DE 5 PLACES";
			$col = "red";
		}
		else
		{
			$text ="";
			$col = "green";
		}

		if ($site != psp)
		{
			echo "<td width='30%' class='Style_tableau_stages'><div align='center'>";
			echo MySQLDateToExplicitDate($row_rsStage['date1'])."<br>".MySQLDateToExplicitDate($row_rsStage['date2']);
			echo "</div>";
			echo "</td>";
		}

		if ($site == psp)
		{
			echo "<td width='27%' class='Style_tableau_stages'>";
		}
		else
		{
			echo "<td width='22%' class='Style_tableau_stages'>";
		}

		//echo "<td width='22%' class='Style_tableau_stages'>";
		echo "<div align='center'>";
		echo "<a href='$urlVille' title='$titleVille'>";
		echo "<em>".$row_rsStage['ville']."<br>".sprintf("%05d",$row_rsStage['code_postal'])."</em></a>";
		echo "</div>";
		echo "</td>";

		if ($site == psp)
		{
			echo "<td width='33%' class='Style_tableau_stages' style='font-size:11px; color: #535353;'><div align='center'>";
		}
		else
		{
			echo "<td width='25%' class='Style_tableau_stages' style='font-size:11px; color: #535353;'><div align='center'>";
		}

		//echo "<td width='25%' class='Style_tableau_stages' style='font-size:11px; color: #535353;'><div align='center'>";
		echo $row_rsStage['nom']."<br>";

		if (!empty($row_rsStage['adresse']))
		{
			echo "(".$row_rsStage['adresse'].")";
		}
		echo "</div>";
		echo "</td>";

		if ($site == psp)
		{
			echo "<td width='15%' class='Style_tableau_stages'><div align='center'>";
			echo MySQLDateToExplicitDate2($row_rsStage['date1'])."<br>".MySQLDateToExplicitDate2($row_rsStage['date2']);
			echo "</div>";
			echo "</td>";

			echo "<td width='7%' class='Style_tableau_stages'><div align='center'>";
			$prix = $row_rsStage['prix'];
			echo "<b>".$prix." €</b>";
			/*
			if ($row_rsStage['id_membre'] == 44 || $row_rsStage['id_membre'] == 64)
			{
				echo "<br><img src='images/stage-recuperation-cb.bmp'>";
			}*/
			echo "</div></td>";

			echo "<td width='18%'><div align='center'>";
			/*echo "<a href='$urlReservation' title='$titleReservation'><u>$texteReservation</u></a>";
			if ($text != "")
				echo "<br><font color=$col>$text</font>";
			echo "</div></td>";*/

			echo "<a href='$urlReservation' title='$titleReservation'><img src='images/Reservation2.jpg'/></a>";
			if ($text != "")
				echo "<br><font color=$col>$text</font>";
			echo "</div></td>";
		}
		else
		{
			echo "<td width='15%'><div align='center'>";
			echo "<a href='$urlReservation' title='$titleReservation'><u>$texteReservation</u></a>";
			if ($text != "")
				echo "<br><font color=$col>$text</font>";
			echo "</div></td>";

			echo "<td width='8%' class='Style_tableau_stages'><div align='center'>";
			echo $row_rsStage['prix']." €";
			/*
			if ($row_rsStage['id_membre'] == 44 || $row_rsStage['id_membre'] == 64)
			{
				echo "<br><img src='images/stage-recuperation-cb.bmp'>";
			}
			*/
			echo "</div></td>";
		}

		echo "</table>";
		}
		while ($row_rsStage = mysql_fetch_assoc($rsStage));
	}
	else if ($ville != NULL)
	{
		$dest = getUrlDepartement($departement);
		echo "<br>";
		echo "<font color='red'>Plus de stages disponibles sur $ville</font>";
		echo "<br><br>";
		echo "Cliquez sur le lien suivant pour accéder aux stages permis à points près de $ville:";
		echo "<br>";
		echo "<a href='recuperer-points-$dest.html'>LISTE DES STAGES A PROXIMITE</a>";
	}
}

function getTitleMotcle($texte)
{
		switch ($texte)
		{
			case 1:
				$texte1 = "stage permis a points ";
				$texte2 = "stage permis points ";
			break;

			case 5:
				$texte1 = "stage permis de conduire ";
				$texte2 = "stage permis conduire ";
			break;

			case 2:
				$texte1 = "stage de recuperation de points ";
				$texte2 = "stage recup point ";
			break;

			case 6:
				$texte1 = "permis a point ";
				$texte2 = "points permis ";
			break;

			case 3:
				$texte1 = "stage de rattrapage de points ";
				$texte2 = "rattraper points permis ";
			break;

			case 4:
				$texte1 = "stage de sensibilisation à la sécurité routière ";
				$texte2 = "stage sensibilisation securite routiere ";
			break;

			default:
				$texte1 = "stage permis a points ";
				$texte2 = "stage permis points ";
			break;
		}
		return array($texte1, $texte2);
}

function getUrl($site, $departement, $get_ville, $get_code_postal, $ville,
	$code_postal, $motCle = NULL, $id_stage = NULL, $id_membre = NULL, $tewt_date=NULL)
{
	$option = "";
	$title1 = "";
	$title2 = "";
	$urlDepartement = "";
	$urlVille = "";
	$urlReservation = "";
	$titleReservation = "";
	$texteReservation = "";
	$titleVille = "";

	switch($site)
	{
		/*case psp: //psp
			//url du departement
			$departement = sprintf("%02d",$departement);
			$dest = getUrlDepartement($departement);
			$urlDepartement = "recuperer-points-$dest.html";

			//options de choix dans liste deroulante
			$code_postal = sprintf("%05d",$code_postal);
			$urlVille = "recuperation-points-$ville-$code_postal-$departement.html";
			$option = "<option value = $urlVille ";
			if ($get_code_postal == $code_postal)
			{
				$option = $option." selected='selected' ";
			}
			$option = $option.">";
			$option = $option.sprintf('%05d', $code_postal)." - Stages permis à points à ".$ville;
			$option = $option."</option>";

			//balises titles
			$title = getTitleMotcle($motCle);
			$title1 = $title[0];
			$title2 = $title[1];

			//url de destination sur reservation du stage
			$urlReservation = "stage-point-$id_stage-$id_membre-$ville.html";
			$titleReservation = $title1;
			$texteReservation = "<strong>Stage recuperation de points<br>$ville le $tewt_date</strong>";

			//balise title pour la ville
			$titleVille = $title2;
		break;*/

		case psp: //psp
			//url du departement
			$departement = sprintf("%02d",$departement);
			$dest = getUrlDepartement($departement);
			$urlDepartement = "recuperer-points-$dest.html";

			//options de choix dans liste deroulante
			$code_postal = sprintf("%05d",$code_postal);
			$urlVille = "recuperation-points-$ville-$code_postal-$departement.html";
			$option = "<a href=$urlVille>";
			$option = $option."Stage recuperation de points<br>".$ville." (".$code_postal.")";
			$option = $option."</a>";

			//balises titles
			$title = getTitleMotcle($motCle);
			$title1 = $title[0];
			$title2 = $title[1];

			//url de destination sur reservation du stage
			$urlReservation = "stage-point-$id_stage-$id_membre-$ville.html";
			$titleReservation = $title1;
			$texteReservation = "<strong>Stage recuperation de points<br>$ville le $tewt_date</strong>";

			//balise title pour la ville
			$titleVille = $title2;
		break;

		case amf: //amf
			//url du departement
			$urlDepartement = "stage-recuperation-points-".getUrlDepartement($departement).".html";

			//options de choix dans liste deroulante
			$departement = sprintf("%02d",$departement);
			$urlVille = "stage-points-$ville-$departement.html";
			$option = "<option value = $urlVille ";
			if ($get_ville === $ville)
			{
				$option = $option." selected='selected' ";
			}
			$option = $option.">";
			$option = $option.sprintf('%02d', $departement)." - Récupération de points à ".$ville;
			$option = $option."</option>";

			//balises titles
			$title1 = "stage de recuperation de points de permis";
			$title2 = "stage recuperation points";

			//url de destination sur reservation du stage
			if ($id_membre != 110){ //automobile club
				$urlReservation = "inscription.php?s=$id_stage&m=$id_membre";
			}
			else{
				$urlReservation = "http://www.automobile-club.org/formation-securite-routiere/stages-permis-a-points/inscription-stage.html?id=$id_stage&referent=prostagespermis";
			}
			$titleReservation = $title1;
			$texteReservation = "<b>JE RESERVE MON STAGE</b>";

			//balise title pour la ville
			$titleVille = $title2;
		break;

		case sens: //sensibilisation
			//url du departement
			$urlDepartement = "stage-points-permis-".getUrlDepartement($departement).".html";

			//options de choix dans liste deroulante
			$departement = sprintf("%02d",$departement);
			$urlVille = "stage-points-permis-$ville-$departement.html";
			$option = "<option value = $urlVille ";
			if ($get_ville === $ville)
			{
				$option = $option." selected='selected' ";
			}
			$option = $option.">";
			$option = $option.sprintf('%02d', $departement)." - Stages sensibilisation à ".$ville;
			$option = $option."</option>";

			//balises titles
			$title1 = "stage de sensibilisation à la sécurité routière";
			$title2 = "stage securite routiere";

			//url de destination sur reservation du stage
			if ($id_membre != 110){ //automobile club
				$urlReservation = "inscription.php?s=$id_stage&m=$id_membre";
			}
			else{
				$urlReservation = "http://www.automobile-club.org/formation-securite-routiere/stages-permis-a-points/inscription-stage.html?id=$id_stage&referent=prostagespermis";
			}
			$titleReservation = $title1;
			$texteReservation = "RESERVATION DU STAGE";

			//balise title pour la ville
			$titleVille = $title2;
		break;

		case rec: //recuperer
			//url du departement
			$urlDepartement = "stage-recuperation-points-".getUrlDepartement($departement).".html";

			//options de choix dans liste deroulante
			$departement = sprintf("%02d",$departement);
			$urlVille = "stage-recuperation-points-$ville-$departement.html";
			$option = "<option value = $urlVille ";
			if ($get_ville === $ville)
			{
				$option = $option." selected='selected' ";
			}
			$option = $option.">";
			$option = $option.sprintf('%02d', $departement)." - Récupérer des points à ".$ville;
			$option = $option."</option>";

			//balises titles
			$title1 = "stage pour recupérer des points de permis";
			$title2 = "stage de récupération de points";

			//url de destination sur reservation du stage
			if ($id_membre != 110){ //automobile club
				$urlReservation = "inscription.php?s=$id_stage&m=$id_membre";
			}
			else{
				$urlReservation = "http://www.automobile-club.org/formation-securite-routiere/stages-permis-a-points/inscription-stage.html?id=$id_stage&referent=prostagespermis";
			}

			$urlReservation = "inscription.php?s=$id_stage&m=$id_membre";
			$titleReservation = $title1;
			$texteReservation = "JE M'INSCRIS AU STAGE";

			//balise title pour la ville
			$titleVille = $title2;
		break;

		case rat: //rattrapage
			//url du departement
			$urlDepartement = "stage-rattraper-point-".getUrlDepartement($departement).".html";

			//options de choix dans liste deroulante
			$departement = sprintf("%02d",$departement);
			$urlVille = "stage-rattraper-point-$ville-$departement.html";
			$option = "<option value = ".$urlVille;
			if ($get_ville === $ville)
			{
				$option = $option." selected='selected' ";
			}
			$option = $option.">";
			$option = $option.sprintf('%02d', $departement)." - Stage de rattrapage de points à ".$ville;
			$option = $option."</option>";

			//balises titles
			$title1 = "stage de rattrapage de points de permis";
			$title2 = "rattraper des points de permis de conduire";

			//url de destination sur reservation du stage
			if ($id_membre != 110){ //automobile club
				$urlReservation = "inscription.php?s=$id_stage&m=$id_membre";
			}
			else{
				$urlReservation = "http://www.automobile-club.org/formation-securite-routiere/stages-permis-a-points/inscription-stage.html?id=$id_stage&referent=prostagespermis";
			}

			$titleReservation = $title1;
			$texteReservation = "INSCRIPTION AU STAGE";

			//balise title pour la ville
			$titleVille = $title2;

		break;

		case spp: //stagepointpermis
			//url du departement
			$urlDepartement = "recuperation-points-".getUrlDepartement($departement).".html";

			//options de choix dans liste deroulante
			$departement = sprintf("%02d",$departement);
			$urlVille = "recuperation-points-$ville-$departement.html";
			$option = "<option value = $urlVille ";
			if ($get_ville === $ville)
			{
				$option = $option." selected='selected' ";
			}
			$option = $option.">";
			$option = $option.sprintf('%02d', $departement)." - Stages points permis à ".$ville;
			$option = $option."</option>";

			//balises titles
			$title1 = "stage points permis";
			$title2 = "stage points";

			//url de destination sur reservation du stage
			if ($id_membre != 110){ //automobile club
				$urlReservation = "inscription.php?s=$id_stage&m=$id_membre";
			}
			else{
				$urlReservation = "http://www.automobile-club.org/formation-securite-routiere/stages-permis-a-points/inscription-stage.html?id=$id_stage&referent=prostagespermis";
			}
			$titleReservation = $title1;
			$texteReservation = "VALIDATION";

			//balise title pour la ville
			$titleVille = $title2;
		break;

		case pap: //permis a points
			//url du departement
			$urlDepartement = "recuperer-points.php?departement=$departement.html";

			//options de choix dans liste deroulante
			$urlVille = "recuperation-points-$ville-$cp-$departement.html";
			$option = "<option value = $urlVille ";
			if ($get_code_postal == $code_postal)
			{
				$option = $option." selected='selected' ";
			}
			$option = $option.">";
			$option = $option.sprintf('%05d', $code_postal)." - stages a ".$ville;
			$option = $option."</option>";

			//balises titles
			$title = getTitleMotcle($motCle);
			$title1 = $title[0];
			$title2 = $title[1];

			//url de destination sur reservation du stage
			$urlReservation = "stage-point-$id_stage-$id_membre-$ville.html";
			$titleReservation = $title1;
			$texteReservation = "JE RESERVE CE STAGE";

			//balise title pour la ville
			$titleVille = $title2;
		break;

		case paca: //paca
			//url du departement
			$departement = sprintf("%02d",$departement);
			$urlDepartement = "recuperation-point-permis-".getUrlDepartement($departement)."-".$departement.".html";

			//options de choix dans liste deroulante
			$urlVille = "recuperation-points-permis-$ville-$departement.html";
			$option = "<option value = $urlVille ";
			if ($get_ville === $ville)
			{
				$option = $option." selected='selected' ";
			}
			$option = $option.">";
			$option = $option.sprintf('%02d', $departement)." - Stages permis à ".$ville;
			$option = $option."</option>";

			//balises titles
			$title1 = "Inscription au stage de recuperation de points de permis";
			$title2 = "PACA: stages de récupération de points";

			//url de destination sur reservation du stage
			if ($id_membre != 110){ //automobile club
				$urlReservation = "inscription.php?s=$id_stage&m=$id_membre";
			}
			else{
				$urlReservation = "http://www.automobile-club.org/formation-securite-routiere/stages-permis-a-points/inscription-stage.html?id=$id_stage&referent=prostagespermis";
			}
			$titleReservation = $title1;
			$texteReservation = "RESERVEZ !";

			//balise title pour la ville
			$titleVille = $title2;
		break;

		case paris: //paris
			//url du departement
			$urlDepartement = "recuperation-point-permis-".getUrlDepartement($departement)."-".$departement.".html";

			//options de choix dans liste deroulante
			$urlVille = "recuperation-points-permis-$ville-$departement.html";
			$option = "<option value = $urlVille ";
			if ($get_ville === $ville)
			{
				$option = $option." selected='selected' ";
			}
			$option = $option.">";
			$option = $option.sprintf('%02d', $departement)." - Stage recuperation de points ".$ville;
			$option = $option."</option>";

			//balises titles
			$title1 = "Inscription au stage permis à points";
			$title2 = "Ile de France: stages de récupération de points";

			//url de destination sur reservation du stage
			if ($id_membre != 110){ //automobile club
				$urlReservation = "inscription.php?s=$id_stage&m=$id_membre";
			}
			else{
				$urlReservation = "http://www.automobile-club.org/formation-securite-routiere/stages-permis-a-points/inscription-stage.html?id=$id_stage&referent=prostagespermis";
			}
			$titleReservation = $title1;
			$texteReservation = "PRE-INSCRIPTION AU STAGE";

			//balise title pour la ville
			$titleVille = $title2;
		break;

		case rh: //rhone alpes
			//url du departement
			$departement = sprintf("%02d",$departement);
			$urlDepartement = "stage-point-permis-".getUrlDepartement($departement)."-".$departement.".html";

			//options de choix dans liste deroulante
			$urlVille = "stage-point-permis-$ville-$departement.html";
			$option = "<option value = $urlVille ";
			if ($get_ville === $ville)
			{
				$option = $option." selected='selected' ";
			}
			$option = $option.">";
			$option = $option.sprintf('%02d', $departement)." - Stages permis à points à ".$ville;
			$option = $option."</option>";

			//balises titles
			$title1 = "Inscription au stage de récupération de points";
			$title2 = "Rhone-Alpes: - stages permis à points";

			//url de destination sur reservation du stage
			if ($id_membre != 110){ //automobile club
				$urlReservation = "inscription.php?s=$id_stage&m=$id_membre";
			}
			else{
				$urlReservation = "http://www.automobile-club.org/formation-securite-routiere/stages-permis-a-points/inscription-stage.html?id=$id_stage&referent=prostagespermis";
			}
			$titleReservation = $title1;
			$texteReservation = "RESERVER LE STAGE PERMIS";

			//balise title pour la ville
			$titleVille = $title2;
		break;

		case lr: //languedoc
			//url du departement
			$departement = sprintf("%02d",$departement);
			$urlDepartement = "stage-recuperation-points-permis-".getUrlDepartement($departement)."-".$departement.".html";

			//options de choix dans liste deroulante
			$urlVille = "stage-recuperation-points-permis-$ville-$departement.html";
			$option = "<option value = $urlVille ";
			if ($get_ville === $ville)
			{
				$option = $option." selected='selected' ";
			}
			$option = $option.">";
			$option = $option.sprintf('%02d', $departement)." - Permis à points à ".$ville;
			$option = $option."</option>";

			//balises titles
			$title1 = "Inscription au stage permis de conduire";
			$title2 = "Languedoc-Rousillon: - stages points permis";

			//url de destination sur reservation du stage
			if ($id_membre != 110){ //automobile club
				$urlReservation = "inscription.php?s=$id_stage&m=$id_membre";
			}
			else{
				$urlReservation = "http://www.automobile-club.org/formation-securite-routiere/stages-permis-a-points/inscription-stage.html?id=$id_stage&referent=prostagespermis";
			}
			$titleReservation = $title1;
			$texteReservation = "RESERVATION";

			//balise title pour la ville
			$titleVille = $title2;
		break;

		case aqui: //aquitaine
			//url du departement
			$urlDepartement = "stage-permis-a-points-".getUrlDepartement($departement)."-".$departement.".html";

			//options de choix dans liste deroulante
			$urlVille = "stage-permis-a-points-$ville-$departement.html";
			$option = "<option value = $urlVille ";
			if ($get_ville === $ville)
			{
				$option = $option." selected='selected' ";
			}
			$option = $option.">";
			$option = $option.sprintf('%02d', $departement)." - Permis à points à ".$ville;
			$option = $option."</option>";

			//balises titles
			$title1 = "Inscription au stage";
			$title2 = "Aquitaine: - stages de recuperation de points";

			//url de destination sur reservation du stage
			if ($id_membre != 110){ //automobile club
				$urlReservation = "inscription.php?s=$id_stage&m=$id_membre";
			}
			else{
				$urlReservation = "http://www.automobile-club.org/formation-securite-routiere/stages-permis-a-points/inscription-stage.html?id=$id_stage&referent=prostagespermis";
			}
			$titleReservation = $title1;
			$texteReservation = "INSCRIPTION";

			//balise title pour la ville
			$titleVille = $title2;
		break;
	}

	return array($option, $title1, $title2, $urlDepartement, $urlVille,
		$urlReservation, $titleReservation, $texteReservation, $titleVille);
}
?>


<script type="text/javascript">

function MM_jumpMenu(targ,selObj,restore)

{

	eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");

	if (restore) selObj.selectedIndex=0;

}

//-->

</script>