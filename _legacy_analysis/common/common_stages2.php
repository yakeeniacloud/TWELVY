<?php
function tableau_stages($site, $departement = NULL, $ville = NULL, $code_postal = NULL)
{
	require_once ("../common/functions.php");

	$query_stage_add = "";
	$query_site_add = "";


	if ($departement != NULL)
	{
		if ($code_postal != NULL){	$query_stage_add = " AND code_postal = $code_postal ";}
		else if ($ville != NULL) {	$query_stage_add = " AND ville = '$ville' ";}

		if ($site == psp || $site == pap){	$query_site_add = " code_postal, ville ";}
		else {								$query_site_add = " ville ";}

        $order = "date1 ASC, prix ASC";
        if (isset($_POST['tri']) && $_POST['tri'] == "tri_prix")
        {
            $order = "prix ASC, date1 ASC";
        }

		$query_stage = sprintf("
							SELECT
								date1,
								date2,
								nb_places_allouees,
								nb_inscrits,
								nb_preinscrits,
								prix,
								prix_boost,
								boost_actif,
								membre.boost_possible,
								stage.id,
								stage.id_externe,
								stage.id_membre,
								motCle,
								site.nom,
								site.ville,
								site.adresse,
								plan,
								code_postal,
								membre.types_paiement

							FROM
								stage, site, membre

							WHERE
								departement = $departement AND
								id_site = site.id AND
								date1 > now() AND
								date1 < (CURDATE() + INTERVAL 90 DAY) AND
								annule = 0 AND
								stage.id_membre = membre.id AND
                                                                membre.id != 188 AND
								nb_places_allouees > 0
								%s

							ORDER BY %s", $query_stage_add, $order);

                if (isset($_GET['afficher_boost'])) {

                    $query_stage = sprintf("
                                                            SELECT
                                                                    date1,
                                                                    date2,
                                                                    nb_places_allouees,
                                                                    nb_boost_allouees,
                                                                    nb_inscrits,
                                                                    nb_preinscrits,
                                                                    prix,
                                                                    prix_boost,
                                                                    boost_actif,
                                                                    membre.boost_possible,
                                                                    stage.id,
                                                                    stage.id_externe,
                                                                    stage.id_membre,
                                                                    motCle,
                                                                    site.nom,
                                                                    site.ville,
                                                                    site.adresse,
                                                                    plan,
                                                                    code_postal,
                                                                    membre.types_paiement

                                                            FROM
                                                                    stage, site, membre

                                                            WHERE
                                                                    departement = $departement AND
                                                                    id_site = site.id AND
                                                                    date1 > now() AND
                                                                    annule = 0 AND
                                                                    stage.id_membre = membre.id
                                                                    AND membre.id != 188
                                                                    %s

                                                            ORDER BY %s", $query_stage_add, $order);
                }

		$query_site = sprintf(
					"SELECT
						DISTINCT %s

					 FROM
					 	site, stage

					 WHERE
						departement = $departement AND
						id_site = site.id AND
						date1 > now() AND
						date1 < (CURDATE() + INTERVAL 90 DAY) AND
						nb_places_allouees > 0 AND
						annule = 0

					 GROUP BY code_postal, ville ORDER BY ville ASC", $query_site_add);

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

//		$i = 0;
//
//		echo "<div align='center'>";
//		echo "<table width='100%'>";
//
//		do
//		{
//
//
//				if (($i % 4) == 0)
//				{
//					echo "<tr height='40px'>";
//				}
//
//				if ($site == psp || $site == pap) {	$cp_local = $row_rsSite['code_postal'];}
//				else { $cp_local = NULL;}
//
//				$tabUrl = getUrl($site, $departement, $ville, $code_postal, $row_rsSite['ville'], $cp_local);
//
//				$code_postal = sprintf("%05d",$code_postal);
//				$urlVille = "recuperation-points-$ville-$code_postal-$departement.html";
//
//				//balises titles
//				$title = getTitleMotcle($motCle);
//				$title1 = $title[0];
//				$title2 = $title[1];
//
//				//balise title pour la ville
//				$titleVille = $title2;
//
//				echo "<td style='font-size:10px' align='center' width='25%'><strong>".$tabUrl[0]."</strong></td>"; //valeur option pour liste deroulante
//
//				$i++;
//
//				if (($i % 4) == 0) {echo "</tr>";}
//
//		}
//		while ($row_rsSite = mysql_fetch_assoc($rsSite));
//
//		echo "</table>";
//		echo "</div><br>";
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
		echo "<a href=$url_departement><strong>-> AFFICHER TOUS LES STAGES PERMIS DU DEPARTEMENT ".getDepartement($departement)."</strong></a><br><br>";
	}

	//affichage tableau de stages
	//---------------------------
	if ($site != psp)
	{
		// echo "<table width='100%' border='1' bgcolor='#444847'>";
		// echo "<tr>";
		// echo	"<td width='30%' align='center'><font color='#FFFFFF'><strong>Dates</strong></font></td>";
		// echo	"<td width='22%' align='center'><font color='#FFFFFF'><strong>Ville</strong></font></td>";
		// echo	"<td width='25%' align='center'><font color='#FFFFFF'><strong>Lieu</strong></font></td>";
		// echo	"<td width='15%' align='center'><font color='#FFFFFF'><strong>Réservation</strong></font></td>";
		// echo	"<td width='8%' align='center'><font color='#FFFFFF'><strong>Prix</strong></font></td>";
		// echo "</tr>";
		// echo "</table>";

		$monUrl = $_SERVER['REQUEST_URI'];
		$monUrl = substr($monUrl, 0, strpos($monUrl, "?"));

		echo '<table id="stagesList" cellspacing="1" itemprop="event" itemscope itemtype="http://schema.org/Event">';
		echo '<thead>';
			echo '<tr>';
				echo '<th class="titre-arrondi" scope="col" width="40%">Villes</th>';
				echo '<form method="post" action="'.$monUrl.'" name="tri_date">';
				echo '<input type="hidden" VALUE="tri_date" name="tri" />';
				echo '<th class="arrondi-c1" scope="col" width="19%">
					<a style="cursor:pointer;" title="Trier par dates" onclick="window.document.tri_date.submit()">Dates<img class="fleche_bas" src="Templates/sources/images/fleche-bas.png"></a></th>';
				echo '</form>';
				echo '<form method="post" action="'.$monUrl.'" name="tri_prix">';
				echo '<input type="hidden" VALUE="tri_prix" name="tri" />';
				echo '<th class="arrondi-c2" scope="col" width="10%">
					<a style="cursor:pointer;" title="Trier par prix" onclick="window.document.tri_prix.submit()">Prix<img class="fleche_bas"src="Templates/sources/images/fleche-bas.png"></a></th>';
				echo '</form>';
				echo '<th class="arrondi-c3" scope="col" width="15%">Paiement</th>';
				echo '<th class="arrondi-c4" scope="col" width="16%">Inscription</th>';
			echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
	}

	else
	{
		$monUrl = $_SERVER['REQUEST_URI'];
		$monUrl = substr($monUrl, 0, strpos($monUrl, "?"));

		echo '<table id="tableau-stages" cellspacing="0">';
		echo "<thead>";
			echo "<tr>";
				echo "<th class=\"titre-arrondi\" scope=\"col\" width=\"40%\">Villes</th>";
				echo "<form method=\"post\" action=\"$monUrl\" name=\"tri_date\">";
				echo "<input type=\"hidden\" VALUE=\"tri_date\" name=\"tri\" />";
				echo "<th class=\"arrondi-c1\" scope=\"col\" width=\"20%\">
					<a style=\"cursor:pointer;\" title=\"Trier par dates\" onclick=\"window.document.tri_date.submit()\">Dates<br><img src=\"images/fleche_bas.gif\"></a></th>";
				echo "</form>";
				echo "<form method=\"post\" action=\"$monUrl\" name=\"tri_prix\">";
				echo "<input type=\"hidden\" VALUE=\"tri_prix\" name=\"tri\" />";
				echo "<th class=\"arrondi-c2\" scope=\"col\" width=\"10%\">
					<a style=\"cursor:pointer;\" title=\"Trier par prix\" onclick=\"window.document.tri_prix.submit()\">Prix<br><img src=\"images/fleche_bas.gif\"></a></th>";
				echo "</form>";
				echo "<th class=\"arrondi-c3\" scope=\"col\" width=\"15%\">Paiement</th>";
				echo "<th class=\"arrondi-c4\" scope=\"col\" width=\"15%\">Inscription</th>";
			echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
	}



	if ($totalRows_rsStage > 0)
	{

            $index_microdata = 0;
            $index_microdata_ancre = true;


	do
	{
                $is_boost = false;
                $tr_boost = '';
                $prix_non_boost = '';

                if (!empty($row_rsStage['boost_possible']) && $row_rsStage['boost_possible'] == 1 && !empty($row_rsStage['boost_actif']) && !empty($row_rsStage['prix_boost'])) {
                    $prix_non_boost = $row_rsStage['prix'];
                    $row_rsStage['prix'] = $row_rsStage['prix_boost'];
                    $tr_boost = ' boost ';
                    $is_boost = true;
                }

		$dateLocal2 = datefr($row_rsStage['date1']);

		//redirection automobile club
		if ($row_rsStage['id_membre'] != 110)
		{
			$tabUrl = getUrl($site, $departement, $ville, $code_postal, $row_rsStage['ville'],
				$row_rsStage['code_postal'], $row_rsStage['motCle'], $row_rsStage['id'], $row_rsStage['id_membre'], $dateLocal2);
		}
		else
		{
			if ($site == psp)
			{
				$tabUrl = getUrl($site, $departement, $ville, $code_postal, $row_rsStage['ville'],
					$row_rsStage['code_postal'], $row_rsStage['motCle'], $row_rsStage['id'], $row_rsStage['id_membre'], $dateLocal2);
			}
			else
			{
				$tabUrl = getUrl($site, $departement, $ville, $code_postal, $row_rsStage['ville'],
					$row_rsStage['code_postal'], $row_rsStage['motCle'], $row_rsStage['id_externe'], $row_rsStage['id_membre'], $dateLocal2);
			}
		}

		$option = $tabUrl[0];
		$title1 = $tabUrl[1];
		$title2 = $tabUrl[2];
		$urlDepartement = $tabUrl[3];
		$urlVille = $tabUrl[4];
		$urlReservation = $tabUrl[5];
		$titleReservation = $tabUrl[6]." ".$row_rsStage['ville']." (".sprintf('%05d',$row_rsStage['code_postal']).") ".$row_rsStage['id_membre'];
		$texteReservation = $tabUrl[7];
		$titleVille = $tabUrl[8]." ".$row_rsStage['ville']." (".sprintf('%05d',$row_rsStage['code_postal']).")";

		if ($site != psp)
		{
			// echo "<table width='100%' border='1'>";
			// echo "<tr bgcolor=\""; echo switchcolor($site); echo "\">";
			echo '<tr class="'.switchClass($site). $tr_boost.'" itemprop="event" itemscope itemtype="http://schema.org/Event">';
		}
		else
		{

                    echo '<tr class="'.$tr_boost.'" itemprop="event" itemscope itemtype="http://schema.org/Event">';
		}

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

                        if (isset($_GET['afficher_boost']))
                            $text ="PLACES DISPO";
		}

                if (isset($_GET['afficher_boost']) && $is_boost && !empty($row_rsStage['nb_boost_allouees'])) {
			$col = "red";
                    if ($row_rsStage['nb_boost_allouees'] > 1)
                        $text = 'PLUS QUE<br />'.$row_rsStage['nb_boost_allouees'].' PLACES !';
                    else
                        $text = "DERNIÈRE PLACE !";
                }

		if (false && $site != psp)
		{
			echo "<td width='30%' class='Style_tableau_stages'><div align='center'>";
			echo MySQLDateToExplicitDate($row_rsStage['date1'])."<br>".MySQLDateToExplicitDate($row_rsStage['date2']);
			echo "</div>";
			echo "</td>";
		}

		if (true || $site == psp)
		{
			echo "<td>";

                        if (isset($_GET['afficher_boost']) && $is_boost)
                            echo '<img src="template/images/decoupes/chrono_reel.png" class="chrono" />';

                        echo '<span itemprop="location" itemscope itemtype="http://schema.org/EventVenue">
                                <span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">';
			echo "<a href='$urlVille' title='$titleVille'>";
			echo '<b><span itemprop="addressLocality">'.$row_rsStage['ville'].'</span> <span itemprop="postalCode">'.sprintf("%05d",$row_rsStage['code_postal']).'</span></b></a><br>';

                        $adr_sans_nom = $row_rsStage['adresse'];

                        if (empty($adr_sans_nom))
                            $adr_sans_nom = supprime_nom_centre($row_rsStage['nom']);

			/*if ((supprime_nom_centre($row_rsStage['nom']) != "") && (supprime_nom_centre($row_rsStage['nom']) != " "))
			{
				echo "<br>";
				$parenthese1 = "(";
				$parenthese2 = ")";
			}
			else
			{
				$parenthese1 = "";
				$parenthese2 = "";
			}
			if (!empty($row_rsStage['adresse']))
			{
				echo $parenthese1;
				echo $row_rsStage['adresse'];
				echo $parenthese2;
			}*/

			echo '      <span class="adr" itemprop="streetAddress">
                                        '.$adr_sans_nom.'
                                    </span>
                                    <span style="display:none">
                                        <span itemprop="addressRegion">'.getDepartement($departement).'</span>
                                        <span itemprop="name">' . strtoupper($row_rsStage['ville']) . ' ' . $row_rsStage['code_postal'] . '</span>
                                    </span>
                                </span>
                            </span>';
			echo "</td>";
		}
		else
		{
			echo "<td width='22%' class='Style_tableau_stages'>";
			echo "<div align='center'>";
			echo "<a href='$urlVille' title='$titleVille'>";
			echo "<em>".$row_rsStage['ville']."<br>".sprintf("%05d",$row_rsStage['code_postal'])."</em></a>";
			echo "</div>";
			echo "</td>";

			echo "<td width='25%' class='Style_tableau_stages' style='font-size:11px; color: #535353;'><div align='center'>";
			echo supprime_nom_centre($row_rsStage['nom'])."<br>";

			if (!empty($row_rsStage['adresse']))
			{
				echo "(".$row_rsStage['adresse'].")";
			}
			echo "</div>";
			echo "</td>";
		}


		if ($site == psp)
		{
			echo "<td>";
                        echo '<meta itemprop="startDate" content="' . $row_rsStage['date1'] . '" />';
			echo MySQLDateToExplicitDate2($row_rsStage['date1'])."<br>".MySQLDateToExplicitDate2($row_rsStage['date2']);
			echo "</td>";

			$prix = $row_rsStage['prix'];

			echo '<td itemprop="offers" itemscope itemtype="http://schema.org/AggregateOffer">';

                        if ($is_boost)
                            echo '<span class="old_prix" itemprop="highPrice">'.$prix_non_boost.' &#128;</span>';

                        echo '<span class="prix" itemprop="lowPrice"><b>'.$prix.' &#128;</b></span>
                                <span style="display:none">
                                    <span itemprop="offerCount">
                                        '.$row_rsStage['nb_places_allouees'].'
                                    </span> places
                                </span>
                            </td>';

			$pay = explode(",", $row_rsStage['types_paiement']);

			if ($pay[0] == "on")
			{
				echo "<td><b>CB / Chèque</b><br/><img align=\"center\" src=\"template/images/decoupes/cb.png\"/></td>";
			}
			else
			{
				echo "<td><b>Chèque</b><br/><img align=\"center\" src=\"template/images/decoupes/cheque1.jpg\"/></td>";
			}


			$texte .= ($pay[0] == "on") ? "<img src='images/icones_transaction/cb.gif'/>&nbsp;&nbsp;&nbsp;" : "";
			$texte .= ($pay[1] == "on") ? "<img src='images/icones_transaction/cheque.gif'/>&nbsp;&nbsp;&nbsp;" : "";



                        $strPad = ' .';
                        $len_prix = 11;
                        $len_ville = 25;

			echo "<td>";
                        $prefix_ancre = 'STAGE . à . . '. str_pad($row_rsStage['prix'] . ' &#128;', $len_prix, $strPad, STR_PAD_RIGHT) . ' . . sur . ' . strtoupper($row_rsStage['ville']) . (empty($index_microdata)? ($index_microdata_ancre? ' - Profitez-en !' : " - Plus d'infos") : '');

                        if (isset($_GET['afficher_boost'])) {
                            if ($row_rsStage['nb_places_allouees'] > 0) {
                                echo "<a href=\"$urlReservation\" itemprop='url' class=\"bt_reserver\" title=\"$titleReservation\"><span style='display:none' itemprop='name'>$prefix_ancre - </span>RÉSERVER</a>";
                                if ($text != "")
                                    echo "<span style=\"color:$col\" class=\"places\">$text</span>";
                            }
                            else {
                                echo "<span class=\"bt_reserver\" title=\"$titleReservation\"><span style='display:none' itemprop='name'>$prefix_ancre - </span>COMPLET</span>";
                            }
                        }
                        else {
                            echo "<a href=\"$urlReservation\" itemprop='url' title=\"$titleReservation\"><img border=\"0\" src=\"template/images/decoupes/reserver.png\" align=\"center\"/><span style='display:none' itemprop='name'>$prefix_ancre</span></a>";
                            if ($text != "")
                                echo "<br><font color=$col>$text</font>";
                        }

			echo "</td>";
			echo "</tr>";
		}
		else
		{
			echo "<td>";
			echo MySQLDateToExplicitDate2($row_rsStage['date1'])."<br>".MySQLDateToExplicitDate2($row_rsStage['date2']);
			echo "</td>";

			echo "<td>";
			$prix = $row_rsStage['prix'];
			echo "<span class='prix'><b>".$prix." €</b></span>";
			echo "</td>";

			$pay = explode(",", $row_rsStage['types_paiement']);

			if ($pay[0] == "on")
			{
				echo "<td><b>CB / Chèque</b><br/><img align=\"center\" src=\"Templates/sources/images/cb.png\"/></td>";
			}
			else
			{
				echo "<td><b>Chèque</b><br/><img align=\"center\" src=\"Templates/sources/images/cheque1.jpg\"/></td>";
			}


			// $texte .= ($pay[0] == "on") ? "<img src='images/icones_transaction/cb.gif'/>&nbsp;&nbsp;&nbsp;" : "";
			// $texte .= ($pay[1] == "on") ? "<img src='images/icones_transaction/cheque.gif'/>&nbsp;&nbsp;&nbsp;" : "";




			echo "<td>";
			echo "<a href=\"$urlReservation\" title=\"$titleReservation\"><img border=\"0\" src=\"Templates/sources/images/reserver.png\" align=\"center\"/></a>";
			if ($text != "")
				echo "<br><font color=$col>$text</font>";
			echo "</td>";
			echo "</tr>";
		}


            if ($index_microdata == 2) {
                $index_microdata = 0;
                $index_microdata_ancre = !$index_microdata_ancre;
            }
            else
                $index_microdata++;

	}
	while ($row_rsStage = mysql_fetch_assoc($rsStage));

	if (true || $site == psp)
	{
		echo "</tbody>";
		echo "</table>";
	}

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


function tableau_promos($site, $departement = NULL, $ville = NULL, $code_postal = NULL)
{
	require_once ("../common/functions.php");

	$query_stage_add = "";
	$query_site_add = "";


        if ($code_postal != NULL){	$query_stage_add = " AND code_postal = $code_postal ";}
        else if ($ville != NULL) {	$query_stage_add = " AND ville = '$ville' ";}

        if ($site == psp || $site == pap){	$query_site_add = " code_postal, ville ";}
        else {								$query_site_add = " ville ";}

        $order = "date1";
        if (isset($_POST['tri']))
        {
                if ($_POST['tri'] == "tri_prix")
                {
                        $order = "prix";
                }
                else if ($_POST['tri'] == "tri_date")
                {
                        $order = "date1";
                }
        }


        $query_stage = sprintf("
                                                SELECT
                                                        date1,
                                                        date2,
                                                        nb_places_allouees,
                                                        nb_boost_allouees,
                                                        nb_inscrits,
                                                        nb_preinscrits,
                                                        prix,
                                                        prix_boost,
                                                        boost_actif,
                                                        membre.boost_possible,
                                                        stage.id,
                                                        stage.id_externe,
                                                        stage.id_membre,
                                                        motCle,
                                                        site.nom,
                                                        site.ville,
                                                        site.adresse,
                                                        plan,
                                                        code_postal,
                                                        departement,
                                                        membre.types_paiement

                                                FROM
                                                        stage, site, membre

                                                WHERE
                                                        id_site = site.id AND
                                                        date1 > now() AND
                                                        annule = 0 AND
                                                        nb_boost_allouees > 0 AND
                                                        nb_places_allouees > 0 AND
                                                        boost_possible = 1 AND
                                                        boost_actif = 1 AND
                                                        stage.id_membre = membre.id
                                                        %s

                                                ORDER BY %s ASC
                                                LIMIT 15", $query_stage_add, $order);
        affiche_promos($site, $departement, $ville, $code_postal, $query_stage);
}



function affiche_promos($site, $departement, $ville, $code_postal, $query_rsStage)
{
	require_once ("../common/functions.php");
	require ("../connections/stageconnect.php");

	mysql_select_db($database_stageconnect, $stageconnect);

//	$rsSite = mysql_query($query_site, $stageconnect) or die(mysql_error());
//	$row_rsSite	= mysql_fetch_assoc($rsSite);
//	$totalRows_rsSite = mysql_num_rows($rsSite);

	$rsStage = mysql_query($query_rsStage, $stageconnect) or die(mysql_error());

	mysql_close($stageconnect);


	//affichage du lien sur tous les stages du departement
	//----------------------------------------------------
	//if ($ville != NULL || $code_postal != NULL)
//	if ($ville != NULL)
//	{
//		if ($site == psp || $site == pap) {	$cp_local = $row_rsSite['code_postal'];}
//		else { $cp_local = NULL;}
//
//		$tabUrl = getUrl($site, $departement, $ville, $code_postal, $row_rsSite['ville'], $cp_local);
//		$url_departement = $tabUrl[3];
//		echo "<a href=$url_departement><strong>-> AFFICHER TOUS LES STAGES PERMIS DU DEPARTEMENT ".getDepartement($departement)."</strong></a><br><br>";
//	}


        $index_microdata = 0;
        $index_microdata_ancre = true;
        $alt_row = false;

	while ($row_rsStage = mysql_fetch_assoc($rsStage))
	{
                $alt_row = !$alt_row;

                $is_boost = false;
                $prix_non_boost = '';

                if (!empty($row_rsStage['boost_possible']) && $row_rsStage['boost_possible'] == 1 && !empty($row_rsStage['boost_actif']) && !empty($row_rsStage['prix_boost'])) {
                    $prix_non_boost = $row_rsStage['prix'];
                    $row_rsStage['prix'] = $row_rsStage['prix_boost'];
                    $is_boost = true;
                }

		$dateLocal2 = datefr($row_rsStage['date1']);

		//redirection automobile club
		if ($row_rsStage['id_membre'] != 110)
		{
                    $tabUrl = getUrl($site, $row_rsStage['departement'], $row_rsStage['ville'], $row_rsStage['code_postal'], $row_rsStage['ville'], $row_rsStage['code_postal'], $row_rsStage['motCle'], $row_rsStage['id'], $row_rsStage['id_membre'], $dateLocal2);
		}
		else
		{
                    $tabUrl = getUrl($site, $row_rsStage['departement'], $row_rsStage['ville'], $row_rsStage['code_postal'], $row_rsStage['ville'],
                            $row_rsStage['code_postal'], $row_rsStage['motCle'], $row_rsStage['id'], $row_rsStage['id_membre'], $dateLocal2);
		}

		$option = $tabUrl[0];
		$title1 = $tabUrl[1];
		$title2 = $tabUrl[2];
		$urlDepartement = $tabUrl[3];
		$urlVille = $tabUrl[4];
		$urlReservation = $tabUrl[5];
		$titleReservation = $tabUrl[6]." ".$row_rsStage['ville']." (".sprintf('%05d',$row_rsStage['code_postal']).") ".$row_rsStage['id_membre'];
		$texteReservation = $tabUrl[7];
		$titleVille = $tabUrl[8]." ".$row_rsStage['ville']." (".sprintf('%05d',$row_rsStage['code_postal']).")";


                $idStage = 'stage-' . $row_rsStage['id'] . '-du-' . $row_rsStage['date1'] . '-au-' . $row_rsStage['date2'];
                $urlVille = 'http://stage-de-recuperation-de-points-pas-cher.prostagespermis.fr/' . $urlVille . '#' . $idStage;

                echo '<li itemprop="event" itemscope itemtype="http://schema.org/Event" ';
                if ($alt_row)
                    echo ' class="alt_row"';
                echo '>';

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

                        if (isset($_GET['afficher_boost']))
                            $text ="PLACES DISPO";
		}

                if (isset($_GET['afficher_boost']) && $is_boost && !empty($row_rsStage['nb_boost_allouees'])) {
			$col = "red";
                    if ($row_rsStage['nb_boost_allouees'] > 1)
                        $text = 'PLUS QUE<br />'.$row_rsStage['nb_boost_allouees'].' PLACES !';
                    else
                        $text = "DERNIÈRE PLACE !";
                }

                $adr_sans_nom = $row_rsStage['adresse'];

                if (empty($adr_sans_nom))
                    $adr_sans_nom = supprime_nom_centre($row_rsStage['nom']);

                $prix = $row_rsStage['prix'];

                echo '<span class="col_1">
                        <span itemprop="location" itemscope itemtype="http://schema.org/EventVenue">
                            <span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
                                <a href="'.$urlVille.'" title="'.$titleVille.'">
                                    <b><span itemprop="addressLocality">'.$row_rsStage['ville'].'</span></b>
                                </a>
                                <span style="display:none">
                                    <span itemprop="postalCode">'.sprintf("%05d",$row_rsStage['code_postal']).'</span>
                                    <span class="adr" itemprop="streetAddress">
                                        '.$adr_sans_nom.'
                                    </span>
                                    <span itemprop="addressRegion">'.getDepartement($departement).'</span>
                                    <span itemprop="name">' . strtoupper($row_rsStage['ville']) . ' ' . $row_rsStage['code_postal'] . '</span>
                                </span>
                            </span>
                        </span>
                        <br />
                        <span class="date"><meta itemprop="startDate" content="' . $row_rsStage['date1'] . '" />'.
                            MySQLDateToExplicitDate2($row_rsStage['date1']).'
                        </span>
                    </span>
                    <span class="col_2" itemprop="offers" itemscope itemtype="http://schema.org/AggregateOffer">
                        <span class="old_prix" itemprop="highPrice">'.$prix_non_boost.' &#128;</span>
                        <br />
                        <span class="prix" itemprop="lowPrice"><b>'.$prix.' &#128;</b></span>
                        <span style="display:none">
                            <span itemprop="offerCount" style="display:none">'.$row_rsStage['nb_places_allouees'].'</span> places
                        </span>
                    </span>';

                $strPad = ' .';
                $len_prix = 11;
                $len_ville = 25;

                $prefix_ancre = 'STAGE . à . . '. str_pad($row_rsStage['prix'] . ' &#128;', $len_prix, $strPad, STR_PAD_RIGHT) . ' . . sur . ' . strtoupper($row_rsStage['ville']) . (empty($index_microdata)? ($index_microdata_ancre? ' - Profitez-en !' : " - Plus d'infos") : '');

                echo "<a class='col_3' href=\"$urlVille\" itemprop='url' class=\"bt_reserver\" title=\"$titleReservation\"><span style='display:none' itemprop='name'>$prefix_ancre - </span><span class='bt_voir'>voir</span></a>";

                echo "</li>";

            if ($index_microdata == 2) {
                $index_microdata = 0;
                $index_microdata_ancre = !$index_microdata_ancre;
            }
            else
                $index_microdata++;

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
				$urlReservation = "http://www.automobile-club.org/formulaire-inscription.html?id_stage=$id_stage&referent=prostagespermis";
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
				$urlReservation = "http://www.automobile-club.org/formulaire-inscription.html?id_stage=$id_stage&referent=prostagespermis";
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
				$urlReservation = "http://www.automobile-club.org/formulaire-inscription.html?id_stage=$id_stage&referent=prostagespermis";
			}
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
				$urlReservation = "http://www.automobile-club.org/formulaire-inscription.html?id_stage=$id_stage&referent=prostagespermis";
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
				$urlReservation = "http://www.automobile-club.org/formulaire-inscription.html?id_stage=$id_stage&referent=prostagespermis";
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
			if ($id_membre != 110){ //automobile club
				$urlReservation = "inscription.php?s=$id_stage&m=$id_membre";
			}
			else{
				$urlReservation = "http://www.automobile-club.org/formulaire-inscription.html?id_stage=$id_stage&referent=prostagespermis";
			}
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
				$urlReservation = "http://www.automobile-club.org/formulaire-inscription.html?id_stage=$id_stage&referent=prostagespermis";
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
				$urlReservation = "http://www.automobile-club.org/formulaire-inscription.html?id_stage=$id_stage&referent=prostagespermis";
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
				$urlReservation = "http://www.automobile-club.org/formulaire-inscription.html?id_stage=$id_stage&referent=prostagespermis";
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
				$urlReservation = "http://www.automobile-club.org/formulaire-inscription.html?id_stage=$id_stage&referent=prostagespermis";
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
				$urlReservation = "http://www.automobile-club.org/formulaire-inscription.html?id_stage=$id_stage&referent=prostagespermis";
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