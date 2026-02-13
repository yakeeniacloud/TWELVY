<?php


function verif_boost($id_stage, $row_stage = NULL, $update = false) {

    // TODO : on a un gros probleme d'include path
	@include_once ("../common/common_boost_config.php");
	@include_once ("../../common/common_boost_config.php");
	@include ("../connections/stageconnect.php");
	@include ("../../connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

    $boost_possible = false;
    $boost_accessible = false;
    $option_reversement = ($row_stage && !empty($row_stage['option_reversement']))? $row_stage['option_reversement'] : 0;

    if (! $row_stage || !isset($row_stage['cb_actif']) || !isset($row_stage['types_paiement'])) {
        $query_boost = "SELECT stage.*, membre.cb_actif, membre.types_paiement FROM stage inner join membre on stage.id_membre = membre.id WHERE stage.id = '$id_stage'";

        $rsStage = mysql_query($query_boost, $stageconnect);
        $row_stage = mysql_fetch_assoc($rsStage);
    }

    $datetime_date1 = new DateTime($row_stage['date1']);
    $datetime_now = new DateTime();

    $nb_jours_restants = -1;
    if ($datetime_date1 > $datetime_now) {
        $datetime_diff = date_diff($datetime_now, $datetime_date1);
        $nb_jours_restants = $datetime_diff->format('%a') +1;
    }

    if (!empty($option_reversement))
        $row_stage['option_reversement'] = $option_reversement;

    if (empty($row_stage['nb_boost']))
        $row_stage['nb_boost'] = 0;

    if (empty($row_stage['nb_boost_allouees']))
        $row_stage['nb_boost_allouees'] = 0;

    $types_paiement = explode(',', $row_stage['types_paiement']);
    $cb_actif = !empty($row_stage['cb_actif']) && !empty($types_paiement[0]);

//    $boost_nb_places_max = min(BOOST_NB_PLACES_MAX, $row_stage['nb_places_allouees']);
//    $nb_boost_possible = $boost_nb_places_max - $row_stage['nb_boost'];
    $nb_boost_possible = min(BOOST_NB_PLACES_MAX - $row_stage['nb_boost'], $row_stage['nb_places_allouees']);

    // intval
    $row_stage['nb_boost_allouees'] *= 1;


    if (!empty($cb_actif) || $row_stage['id_membre'] == '188') {
        $boost_accessible = true;
    }

    if (    !empty($row_stage['nb_places_allouees'])
        &&  $nb_boost_possible > 0
        &&  (empty($row_stage['annule']))
        &&  $boost_accessible
        &&  $nb_jours_restants <= BOOST_DELAI_AVANT_STAGE_MAX
        &&  $nb_jours_restants >= BOOST_DELAI_AVANT_STAGE_MIN
    )
    {
        $boost_possible = true;
    }

    if ($row_stage['option_reversement'] == 2)
    {
        if (!$boost_possible) {
            $row_stage['option_reversement'] = 0;

            if ($update) {
//                $rsStage = mysql_query("update stage set option_reversement = 0, option_visibilite = 0 where id = '$id_stage'", $stageconnect);
            }
        }
        else if ($nb_boost_possible < $row_stage['nb_boost_allouees']) {
            $row_stage['nb_boost_allouees'] = $nb_boost_possible;

            if ($update) {
//                $rsStage = mysql_query("update stage set nb_boost_allouees = $nb_boost_possible where id = '$id_stage'", $stageconnect);
            }
        }

    }

    return array('boost_possible' =>$boost_possible, 'boost_accessible' => $boost_accessible, 'is_boost'=> $row_stage['option_reversement'] == 2, 'option_reversement' =>$row_stage['option_reversement'], 'annule'=>$row_stage['annule'], 'cb_actif' =>$cb_actif, 'nb_jours_restants'=>$nb_jours_restants, 'nb_places_allouees'=>$row_stage['nb_places_allouees'], 'nb_boost_possible'=>$nb_boost_possible, 'nb_boost'=>$row_stage['nb_boost'], 'nb_boost_allouees'=>$row_stage['nb_boost_allouees']);
}


function getPrixBarreStage($prix) {
    return $prix + ($prix % 2 == 0 ? 15 : 10);
}


function get_filtrage_villes($departement = NULL, $id_groupe_ville = NULL, $ville = NULL, $code_postal = NULL)
{
	require_once ("../common/functions.php");
	require ("../connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	$query_stage_add = "";


    if (!empty($departement)){	$query_stage_add = " AND departement.code_departement = $departement ";}
    else if (!empty($id_groupe_ville)){	$query_stage_add = " AND ville.id_groupe_ville = $id_groupe_ville ";}
    else if (!empty($code_postal)){	$query_stage_add = " AND code_postal = $code_postal ";}
    else if (!empty($ville)) {	$query_stage_add = " AND ville = '$ville' ";}


    /*
    $query_villes = sprintf("SELECT
                                    groupe_ville.*
                            FROM
                                    stage
                                    inner join site on stage.id_site = site.id
                                    inner join ville on site.id_ville = ville.id_ville
                                    inner join groupe_ville on groupe_ville.id_groupe_ville = ville.id_groupe_ville
                                    inner join departement on site.departement = departement.code_departement
                            WHERE
                                    date1 > now() AND
                                    stage.visible = 1 AND
                                    date1 < (CURDATE() + INTERVAL 90 DAY) AND
                                    stage.visible = 1 AND
                                    annule = 0
                                    %s
                            GROUP BY ville.id_groupe_ville
                            ORDER BY ville.id_groupe_ville", $query_stage_add);*/

    $query_villes = sprintf("SELECT
                                    groupe_ville.*
                            FROM
                                    stage
                                    inner join site on stage.id_site = site.id
                                    inner join ville on site.id_ville = ville.id_ville
                                    inner join groupe_ville on groupe_ville.id_groupe_ville = ville.id_groupe_ville
                                    inner join departement on site.departement = departement.code_departement
                            WHERE
                                    1 = 1
                                    %s
                            GROUP BY ville.id_groupe_ville
                            ORDER BY ville.id_groupe_ville", $query_stage_add);

//    var_dump($query_villes);
	$rsVilles = mysql_query($query_villes, $stageconnect) or die(mysql_error());

//	mysql_close($stageconnect);

    $alt_row = false;

    if (empty($ville))
        while ($row_ville = mysql_fetch_assoc($rsVilles))
        {
            $alt_row = !$alt_row;
            $alias_ville = strtoupper($row_ville['alias_groupe_ville']);
            $nom_ville = firstToUpper($row_ville['nom_groupe_ville']);
            echo '<li>
                    <input type="checkbox" class="filtrage_villes_checkbox" autocomplete="off" id_groupe_ville="'.$row_ville['id_groupe_ville'].'"/>
                    <a title="points permis '.$nom_ville.'" href="stage-de-recuperation-de-points-'.$alias_ville.'_'.$row_ville['id_groupe_ville'].'.html">
                        <b>'.$nom_ville.'</b>
                    </a>
                </li>';
        }
    else
        while ($row_ville = mysql_fetch_assoc($rsVilles))
        {
            $alt_row = !$alt_row;
            $alias_ville = strtoupper($row_ville['alias_groupe_ville']);
            $nom_ville = firstToUpper($row_ville['nom_groupe_ville']);
            echo '<li>
                    <a title="points permis '.$nom_ville.'" href="stage-de-recuperation-de-points-'.$alias_ville.'_'.$row_ville['id_groupe_ville'].'.html">
                        <b>'.$nom_ville.'</b>
                    </a>
                </li>';
        }

}

function get_filtrage_departements($departement)
{
	require_once ("../common/functions.php");

    // TODO : à remplacer quand les nouvelles fiches villes seront toutes activées
    $liste_dep_voisin = getDepartementVoisin($departement);
    //$liste_dep_voisin = '22, 29, 56';
    $split = explode(',', $liste_dep_voisin);
    $url_dep_prefix = 'recuperer-points-';
    $title_dep = 'Stage recuperation de points';
    $alt_row = false;

    foreach($split as $dep)
    {
        $texte = getDepartement($dep);
        $dest = getUrlDepartement($dep);
        $urlDepartement = $url_dep_prefix.$dest.'.html';

        $alt_row = !$alt_row;

        echo '<li>
                <a title="'.$title_dep.' '.$texte.'" href="'.$urlDepartement.'">
                    <b>'.$texte.'</b>
                </a>
            </li>';
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

        $order = "date1 ASC, prix ASC";

        if (isset($_POST['tri']))
        {
                if ($_POST['tri'] == "tri_prix")
                {
                        $order = "prix ASC, date1 ASC";
                }
                else if ($_POST['tri'] == "tri_date")
                {
                        $order = "date1 ASC, prix ASC";
                }
        }


//        $query_stage = sprintf("SELECT
//                                        date1,
//                                        date2,
//                                        nb_places_allouees,
//                                        nb_boost_allouees,
//                                        nb_inscrits,
//                                        nb_preinscrits,
//                                        prix,
//                                        prix_boost,
//                                        prix_privilege,
//                                        boost_actif,
//                                        membre.boost_possible,
//                                        stage.id,
//                                        stage.id_externe,
//                                        stage.id_membre,
//                                        motCle,
//                                        site.nom,
//                                        site.ville,
//                                        site.adresse,
//                                        plan,
//                                        code_postal,
//                                        departement,
//                                        membre.types_paiement
//
//                                FROM
//                                        stage, site, membre, departement
//
//                                WHERE
//                                        id_site = site.id AND
//                                        site.departement = departement.code_departement AND
//                                        date1 > now() AND
//                                        stage.visible = 1 AND
//                                        nb_places_allouees > 0 AND
//                                        annule = 0 AND
//                                        (
//                                            ( prix < prix_boost_max )
//                                            OR
//                                            (   nb_boost_allouees > 0 AND
//                                                boost_possible = 1 AND
//                                                boost_actif = 1
//                                            )
//                                        )
//                                        AND
//                                        stage.id_membre = membre.id AND
//                                        site.id_membre = membre.id
//                                        %s
//
//                                ORDER BY %s
//                                LIMIT 10", $query_stage_add, $order);

        $query_stage = sprintf("SELECT
                                        date1,
                                        date2,
                                        nb_places_allouees,
                                        nb_boost_allouees,
                                        nb_boost,
                                        nb_inscrits,
                                        nb_preinscrits,
                                        prix,
                                        prix_barre,
                                        prix_privilege,
                                        stage.id,
                                        stage.id_externe,
                                        stage.id_membre,
                                        option_visibilite,
                                        option_reversement,
                                        motCle,
                                        site.nom,
                                        site.ville,
                                        site.adresse,
                                        plan,
                                        code_postal,
                                        departement,
                                        membre.cb_actif,
                                        membre.types_paiement

                                FROM
                                        stage, site, membre, departement

                                WHERE
                                        id_site = site.id AND
                                        site.departement = departement.code_departement AND
                                        date1 > now() AND
                                        stage.visible = 1 AND
                                        nb_places_allouees > 0 AND
                                        annule = 0 AND
                                        (
                                            prix < prix_boost_max OR option_reversement in (1,2)
                                        )
                                        AND
                                        stage.id_membre = membre.id AND
                                        stage.id_membre <> 188 AND
                                        site.id_membre = membre.id
                                        %s

                                ORDER BY option_reversement desc, date1 asc, prix asc
                                LIMIT 20", $query_stage_add, $order);

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
        if (is_sniffer())
            $row_rsStage['prix'] = $row_rsStage['prix'] + rand(10,15);

        $alt_row = !$alt_row;

        $is_boost = false;
        $prix_non_boost = '';

        if ($row_rsStage['prix_barre'] > $row_rsStage['prix'])
            $prix_non_boost = $row_rsStage['prix_barre'] . '  &#128;';

//        if (!empty($row_rsStage['boost_possible']) && $row_rsStage['boost_possible'] == 1 && !empty($row_rsStage['boost_actif']) && !empty($row_rsStage['prix_boost']) && ($row_rsStage['nb_boost_allouees'] + $row_rsStage['nb_boost']) <= 2) {
//            $prix_non_boost = $row_rsStage['prix'] . '  &#128;';
//            $row_rsStage['prix'] = $row_rsStage['prix_boost'];
//            $is_boost = true;
//        }

        if($row_rsStage['option_reversement'] == '2') {

            $result_boost = verif_boost($row_rsStage['id'], $row_rsStage, true);
            $is_boost = $result_boost['is_boost'];
//            $row_rsStage['option_reversement'] = $result_boost['option_reversement'];
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
//                $urlVille = 'http://stage-de-recuperation-de-points-pas-cher.prostagespermis.fr/' . $urlVille . '#' . $idStage;
                $urlVille = $urlVille . '#' . $idStage;

                echo '<li itemprop="event" itemscope itemtype="http://schema.org/Event" ';
                if ($alt_row)
                    echo ' class="alt_row"';
                echo '>';

//		$places = $row_rsStage['nb_places_allouees'] - ($row_rsStage['nb_inscrits'] + $row_rsStage['nb_preinscrits']);
		if ($row_rsStage['nb_places_allouees'] < 5)
		{
			$text = "- DE 5 PLACES";
			$col = "red";
		}
		else
		{
//			$text ="";
			$col = "green";

//                        if (isset($_GET['afficher_boost']))
                            $text ="PLACES DISPO";
		}

//                if (isset($_GET['afficher_boost']) && $is_boost && !empty($row_rsStage['nb_boost_allouees'])) {
                if ($is_boost && !empty($row_rsStage['nb_boost_allouees'])) {
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

                //ajout_modif_privilege
				if (isset($_SESSION['privilege']))
				{
					if ($row_rsStage['prix_privilege'] != NULL &&
						$row_rsStage['prix_privilege'] > 0 &&
						($row_rsStage['prix_privilege'] < $row_rsStage['prix']))

							$prix = $row_rsStage['prix_privilege'];  //>

					echo '<span class="col_1">
							<span itemprop="location" itemscope itemtype="http://schema.org/EventVenue">
								<span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
									<a href="'.$urlVille.'" title="'.$titleVille.'">
										<b><span itemprop="addressLocality">'.substr($row_rsStage['ville'],0,18).'</span></b>
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
						<span class="col_2" itemprop="offers" itemscope itemtype="http://schema.org/AggregateOffer">';

						if ($prix != $row_rsStage['prix'])
						{
							echo '<span class="old_prix" itemprop="highPrice">'.$row_rsStage['prix'].' &#128;</span>
							<span class="prix_privilege" itemprop="lowPrice"><b>'.$prix.' &#128;</b></span>';
						}
						else
						{
							echo '<span><b>'.$row_rsStage['prix'].' &#128;</b></span>';
						}
						echo '<span style="display:none">
								<span itemprop="offerCount" style="display:none">'.$row_rsStage['nb_places_allouees'].'</span> places
							</span>
						</span>';
				}
				else
				{
				//!ajout_modif_privilege

                	echo '<span class="col_1">
                        <span itemprop="location" itemscope itemtype="http://schema.org/EventVenue">
                            <span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
                                <a href="'.$urlVille.'" title="'.$titleVille.'">
                                    <b><span itemprop="addressLocality">'.substr($row_rsStage['ville'],0,18).'</span></b>
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
                        <span class="old_prix" itemprop="highPrice">'.$prix_non_boost.'</span>
                        <span class="prix" itemprop="lowPrice"><b>'.$prix.' &#128;</b></span>
                        <span style="display:none">
                            <span itemprop="offerCount" style="display:none">'.$row_rsStage['nb_places_allouees'].'</span> places
                        </span>
                    </span>';
                }

                $strPad = ' .';
                $len_prix = 11;
                $len_ville = 25;

//                $prefix_ancre = 'STAGE . à . . '. str_pad($row_rsStage['prix'] . ' &#128;', $len_prix, $strPad, STR_PAD_RIGHT) . ' . . sur . ' . strtoupper($row_rsStage['ville']) . (empty($index_microdata)? ($index_microdata_ancre? ' - Profitez-en !' : " - Plus d'infos") : '');

                $prefix_ancre = 'STAGE à '. $row_rsStage['prix'] . ' &#128; sur ' . strtoupper($row_rsStage['ville']);

                echo "<a class='col_3' href=\"$urlVille\" itemprop='url' class=\"bt_reserver\" title=\"$titleReservation\"><span style='display:none' itemprop='name'>$prefix_ancre </span><span class='bt_voir'>voir</span></a>";

                echo "</li>";

            if ($index_microdata == 2) {
                $index_microdata = 0;
                $index_microdata_ancre = !$index_microdata_ancre;
            }
            else
                $index_microdata++;

	}

}



function tableau_promos_departements($site, $departement)
{
	require_once ("../common/functions.php");

    $dep_voisins = getDepartementVoisin($departement);

    $dep_voisins_arr = explode(',', $dep_voisins);
    $liste_dep_voisins = $sep_liste_dep_voisins = '';
    foreach($dep_voisins_arr as $dep_voisin) {
        $liste_dep_voisins .= "$sep_liste_dep_voisins'$dep_voisin'";
        $sep_liste_dep_voisins = ',';
    }

//        if ($code_postal != NULL){	$query_stage_add = " AND code_postal = $code_postal ";}
//        else if ($ville != NULL) {	$query_stage_add = " AND ville = '$ville' ";}
//
//        if ($site == psp || $site == pap){	$query_site_add = " code_postal, ville ";}
//        else {								$query_site_add = " ville ";}
//
//        $order = "date1 ASC, prix ASC";
//
//        if (isset($_POST['tri']))
//        {
//                if ($_POST['tri'] == "tri_prix")
//                {
//                        $order = "prix ASC, date1 ASC";
//                }
//                else if ($_POST['tri'] == "tri_date")
//                {
//                        $order = "date1 ASC, prix ASC";
//                }
//        }


        $query_stage = "SELECT
                                        date1,
                                        date2,
                                        nb_places_allouees,
                                        nb_boost_allouees,
                                        nb_inscrits,
                                        nb_preinscrits,
                                        prix,
                                        prix_barre,
                                        prix_boost,
                                        prix_privilege,
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
                                        stage, site, membre, departement

                                WHERE
                                        id_site = site.id AND
                                        site.departement = departement.code_departement AND
                                        date1 > now() AND
                                        stage.visible = 1 AND
                                        nb_places_allouees > 0 AND
                                        annule = 0 AND
                                        stage.id_membre = membre.id AND
                                        stage.id_membre <> 188 AND
                                        site.id_membre = membre.id
                                        and departement in ($liste_dep_voisins)

                                ORDER BY prix, date1";


                    // TODO : enlever le or id_membre = 188  à la mise en production

        affiche_promos_departements($site, $departement, $query_stage);
}


function tableau_premium_departements($site, $departement, $ville = NULL, $code_postal = NULL, $id_groupe_ville = NULL)
{
	require_once ("../common/functions.php");

    $query_stage_select = '';
    $query_stage_where = '';
    $query_stage_from = '';

    if ($id_groupe_ville != NULL) {
        $query_stage_select = ", alias_groupe_ville, id_groupe_ville";
        $query_stage_where = " AND id_groupe_ville = '$id_groupe_ville' ";
        $query_stage_from = " inner join ville on site.id_ville = ville.id_ville inner join groupe_ville on ville.id_groupe_ville = groupe_ville.id_groupe_ville";
    }
    else if ($code_postal != NULL){	$query_stage_where = " AND code_postal = $code_postal ";}
    else if ($ville != NULL) {	$query_stage_where = " AND ville = '$ville' ";}

    $query_stage = "SELECT
                            date1,
                            date2,
                            nb_places_allouees,
                            nb_boost_allouees,
                            nb_inscrits,
                            nb_preinscrits,
                            prix,
                            prix_avant_premium,
                            prix_barre,
                            prix_boost,
                            prix_privilege,
                            boost_actif,
                            membre.boost_possible,
                            stage.id,
                            stage.id_externe,
                            stage.id_membre,
                            option_visibilite,
                            option_reversement,
                            motCle,
                            site.nom,
                            site.ville,
                            site.adresse,
                            plan,
                            code_postal,
                            departement,
                            prix_boost_max,
                            membre.cb_actif,
                            membre.types_paiement
                            $query_stage_select

                    FROM
                            stage, membre, departement, site $query_stage_from

                    WHERE
                            id_site = site.id AND
                            site.departement = departement.code_departement AND
                            date1 > now() AND
                            date1 < (CURDATE() + INTERVAL 15 DAY) AND
                            stage.visible = 1 AND
                            nb_places_allouees > 0 AND
                            annule = 0 AND
                            stage.id_membre = membre.id AND
                            site.id_membre = membre.id AND
                            stage.option_reversement in (1,2) AND
                            departement = $departement
                            $query_stage_where

                    ORDER BY Rand(), prix, date1
                    LIMIT 3";

    return affiche_premium_departements($site, $departement, $query_stage);
}



function affiche_premium_departements($site, $departement, $query_stage)
{
	//random du département si pas de stages dans une ville
	//-----------------------------------------------------
	$random = false;
	if ($site == "psp_no_stage_ville")
	{
		$nb_stages_aleatoires = 5;
		$site = psp;
		$random = true;
	}

	require_once ("../common/functions.php");
	require ("../connections/stageconnect.php");

	mysql_select_db($database_stageconnect, $stageconnect);

	$rsStage = mysql_query($query_stage, $stageconnect) or die(mysql_error());
	$row_rsStage = mysql_fetch_assoc($rsStage);
	$totalRows_rsStage = mysql_num_rows($rsStage);

	if ($totalRows_rsStage == 0)
		return -1;


	mysql_close($stageconnect);

    $tri = 'tri_date';
    if (!empty($_POST['tri']) && $_POST['tri'] == "tri_prix")
        $tri = 'tri_prix';


	if ($totalRows_rsStage > 0)
	{

//        if ($ville != NULL)
//        {
//                echo "<h2>";
//                echo "Récupération de points de permis sur $ville:";
//                echo "</h2>";
//        }
//        else if ($departement != NULL)
//        {
//
//                echo "<h2>";
//                echo "Récupération de points de permis ".getDepartement($departement).":";
//                echo "</h2>";
//        }
//        else if ($region != NULL)
//        {
//
//                echo "<h2>";
//                echo "Récupération de points de permis ".getRegion($region).":";
//                echo "</h2>";
//        }
//        else
//        {

                echo "<h2 class='title_premium'>";
                echo "Notre sélection de stages";
                echo "</h2>";
//        }

        //affichage tableau de stages
        //---------------------------
        $monUrl = $_SERVER['REQUEST_URI'];
        $monUrl = substr($monUrl, 0, strpos($monUrl, "?"));

        echo '<div id="tableau-stages-container">';

        echo '<table id="tableau-stages" cellspacing="0"';

        if ($chemin == CHEMIN_PAS_CHER)
            echo ' class="boost" ';

        echo '>';

        $class_tri_prix = '';
        $class_tri_date = 'tri_selected';

        if ($tri == 'tri_prix') {
            $class_tri_prix = 'tri_selected';
            $class_tri_date = '';
        }

        $index_microdata = 0;
        $index_microdata_ancre = true;

        do
        {
            //S'il n'existe pas de stage sur une ville, on affiche de manière aléatoire
            //les stages du département sur cette ville pour éviter le duplicate.
            if ($random == true)
            {
                if ($nb_stages_aleatoires <= 0)
                    break;

                if (rand(0,1) == 0)
                {
                    $row_rsStage = mysql_fetch_assoc($rsStage);
                    continue;
                }
                else
                {
                    $nb_stages_aleatoires --;
                }
            }

			if (is_sniffer())
			    $row_rsStage['prix'] = $row_rsStage['prix'] + rand(10,15);

            $tr_boost = '';
//            $ancien_prix = getPrixBarreStage($row_rsStage['prix']);//rand(5,10); // TODO : voir ce qu'on met en ancien prix
            $ancien_prix = $row_rsStage['prix_barre'];//rand(5,10); // TODO : voir ce qu'on met en ancien prix

            $is_boost = $is_premium = $is_flash = 0;

            if ($row_rsStage['option_reversement'] == '1') {
                $is_premium = 1;
            }
            else if ($row_rsStage['option_reversement'] == '2') {
                $result_boost = verif_boost($row_rsStage['id'], $row_rsStage, true);
                $is_boost = $result_boost['is_boost'];
                $row_rsStage['option_reversement'] = $result_boost['option_reversement'];

                if ($is_boost) {

                    if ($row_rsStage['nb_places_allouees'] > 0)
                        $tr_boost = ' boost ';
//                    else
//                        $tr_boost = ' boost complet ';
                }
                else {
                    continue;
                }
            }

            $dateLocal2 = datefr($row_rsStage['date1']);

            //redirection automobile club
            if ($row_rsStage['id_membre'] != 110)
            {

                $tabUrl = getUrl($site, $row_rsStage['departement'], $ville, $code_postal, $row_rsStage['ville'],
                            $row_rsStage['code_postal'], $row_rsStage['motCle'], $row_rsStage['id'], $row_rsStage['id_membre'], $dateLocal2);
            }
            else
            {
                $tabUrl = getUrl($site, $row_rsStage['departement'], $ville, $code_postal, $row_rsStage['ville'],
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

            if (in_array($departement, array(22, 29, 56, 03))) {

                $idStage = 'stage-' . $row_rsStage['id'] . '-du-' . $row_rsStage['date1'] . '-au-' . $row_rsStage['date2'];
                $alias_ville = strtoupper($row_rsStage['alias_groupe_ville']);
                $urlVille = 'stage-de-recuperation-de-points-'.$alias_ville.'_'.$row_rsStage['id_groupe_ville'].'.html';
//                if (empty($ville))
//                    $urlReservation = $urlVille . '#' . $idStage;
            }

            echo '<tr id="_'.$idStage.'" class="'.$tr_boost.' ville_'.$row_rsStage['id_groupe_ville'].'" itemprop="event" itemscope itemtype="http://schema.org/Event">';

            $places = $row_rsStage['nb_places_allouees'] - ($row_rsStage['nb_inscrits'] + $row_rsStage['nb_preinscrits']);
            if ($row_rsStage['nb_places_allouees'] < 5)
            {
                    $text = "- DE 5 PLACES";
                    $col = "red";
            }
            else
            {
//                    $text ="PLACES DISPO";
                    $text ="";
                    $col = "green";
            }

//                    if (isset($_GET['afficher_boost']) && $is_boost && !empty($row_rsStage['nb_boost_allouees'])) {
            if ($is_boost && !empty($row_rsStage['nb_boost_allouees'])) {
                    $col = "red";
                if ($row_rsStage['nb_boost_allouees'] > 1)
                    $text = 'PLUS QUE<br />'.$row_rsStage['nb_boost_allouees'].' PLACES !';
                else
                    $text = "DERNIÈRE PLACE !";
            }

            echo "<td class=\"first\" style=\"text-align:left;\" width=\"40%\">";
            echo "<div style=\"float:left;\">";

            //ajout_modif_privilege
            if (isset($_SESSION['privilege']) &&
                $row_rsStage['prix_privilege'] != NULL &&
                $row_rsStage['prix_privilege'] > 0 &&
                ($row_rsStage['prix_privilege'] < $row_rsStage['prix'])
            )
            {
                echo '<img src="images/vip211.png" class="privilege" />';
            }

//                          if (isset($_GET['afficher_boost']) && $is_boost)
            else if ($is_boost)
                echo '<img src="template/images/decoupes/chrono_reel.png" class="chrono" />';

            echo '<span itemprop="location" itemscope itemtype="http://schema.org/EventVenue">
                    <span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">';
            echo "<a href='$urlVille' title='$titleVille' class='ville_link'>";
            echo '<b><span itemprop="addressLocality">'.$row_rsStage['ville'].'</span> <span itemprop="postalCode">'.sprintf("%05d",$row_rsStage['code_postal']).'</span></b></a>';

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
                </span></div>';


            $tmp_id_stage3 = $row_rsStage['id'];

            $title_agrement = "Stage agréé par la préfecture: ".getDepartement($departement);

            echo "<div style=\"text-align:right\">";
            echo "<a title=\"$title_agrement\" style=\"cursor: default\"><img class=\"testdiv\" style=\"cursor: default; width:15px;\" src=\"images/icone_info2.png\"
            onclick=\"parent.TINY.box.show({iframe:'infoagrement.php?id_stage=$tmp_id_stage3', width:630, height:430, fixed:false, maskopacity:20})\"></a>";
            echo "</div>";

            echo "</td>";

            echo "<td width=\"20%\">";
            echo '<meta itemprop="startDate" content="' . $row_rsStage['date1'] . '" />';
            echo MySQLDateToExplicitDate3($row_rsStage['date1'],1,0)."<br>".MySQLDateToExplicitDate3($row_rsStage['date2'],1,0);
            echo "</td>";

            //ajout_modif_privilege
            if (isset($_SESSION['privilege']) &&
                $row_rsStage['prix_privilege'] != NULL &&
                $row_rsStage['prix_privilege'] > 0 &&
                ($row_rsStage['prix_privilege'] < $row_rsStage['prix'])
            )
            {
                echo '<td itemprop="offers" width="11%" itemscope itemtype="http://schema.org/AggregateOffer">';

                echo '<span class="prix" itemprop="lowPrice" style="color:#BF9A22;text-shadow: 2px 2px 2px #99999;">'.$row_rsStage['prix_privilege'].' &#128;</span>
                        <br><span class="old_prix" itemprop="highPrice"><strike>'.$row_rsStage['prix'].'&#128;</strike></span>
                        <span style="display:none">
                            <span itemprop="offerCount">
                                '.$row_rsStage['nb_places_allouees'].'
                            </span> places
                        </span>
                    </td>';
            }
            else
            {

                $prix = $row_rsStage['prix'];

                echo '<td itemprop="offers" width="11%" itemscope itemtype="http://schema.org/AggregateOffer">';

                echo '<span class="prix" itemprop="lowPrice">'.$prix.' &#128;</span>
                        <br /><span class="old_prix" itemprop="highPrice">'.(!empty($ancien_prix) && $ancien_prix > $prix ? $ancien_prix.' &#128;' : '').'</span>
                        <span style="display:none">
                            <span itemprop="offerCount">
                                '.$row_rsStage['nb_places_allouees'].'
                            </span> places
                        </span>
                    </td>';
            }

            $pay = explode(",", $row_rsStage['types_paiement']);

//            if ($pay[0] == "on")
//            {
//                    echo "<td width=\"8%\"><img align=\"center\" width=\"30\" src=\"template/images/icones_transaction/cb2.gif\"/></td>";
//            }
//            else
//            {
//                    echo "<td width=\"8%\"></td>";
//            }

            if ($pay[0] == "on")
            {
                if ($is_boost)
                    echo "<td width='8%'><img align=\"center\" width=\"31\" src=\"template/images/icones_transaction/cb2.gif\"/></td>";
                else
                    echo "<td width='8%'><img align=\"center\" width=\"31\" src=\"template/images/icones_transaction/cb2.gif\"/><img align=\"center\" style=\"opacity:0.8\" width=\"31\" src=\"template/images/icones_transaction/cheque.gif\"/></td>";
            }
            else
            {
                    echo "<td width='8%'><img align=\"center\" style=\"opacity:0.8\" width=\"31\" src=\"template/images/icones_transaction/cheque.gif\"/></td>";
            }


//                            $texte .= ($pay[0] == "on") ? "<img src='images/icones_transaction/cb.gif'/>&nbsp;&nbsp;&nbsp;" : "";
//                            $texte .= ($pay[1] == "on") ? "<img src='images/icones_transaction/cheque.gif'/>&nbsp;&nbsp;&nbsp;" : "";

            $strPad = ' .';
            $len_prix = 11;
            $len_ville = 25;

            echo "<td width=\"15%\" class=\"last\">";
//                            $prefix_ancre = 'STAGE . à . . '. str_pad($row_rsStage['prix'] . ' &#128;', $len_prix, $strPad, STR_PAD_RIGHT) . ' . . sur . ' . strtoupper($row_rsStage['ville']) . (empty($index_microdata)? ($index_microdata_ancre? ' - Profitez-en !' : " - Plus d'infos") : '');
//                            $prefix_ancre = 'STAGE . à . . '. str_pad($row_rsStage['prix'] . ' &#128;', $len_prix, $strPad, STR_PAD_RIGHT) . ' . . sur . ' . strtoupper($row_rsStage['ville']);

            $prefix_ancre = 'STAGE à '. $row_rsStage['prix'] . ' &#128; sur ' . strtoupper($row_rsStage['ville']);

            //Fiches villes dans entenoir de convertion
            //-----------------------------------------
//             if (in_array($departement, array(35,14,79,85,57,63,87,21,72,80,76,49,42,66,59,33,31,6,34,30,67)) && empty($ville))
//             {
//                $urlReservation = $urlVille.'#'.$idStage;
//             }


                if ($is_boost) {
                    echo '<span class="compteur"><span class="glyphicon glyphicon-time"></span> Promotion terminée</span>';
                }

//                            if (isset($_GET['afficher_boost'])) {
                if ($row_rsStage['nb_places_allouees'] > 0) {
                    echo "<a href=\"$urlReservation\" itemprop='url' class=\"bt_reserver\" title=\"$titleReservation\"><span style='display:none' itemprop='name'>$prefix_ancre </span>RÉSERVER</a>";
                    if ($text != "")
                        echo "<span style=\"color:$col\" class=\"places\">$text</span>";
                }
                else {
                        echo "<span class=\"bt_reserver bt_complet\" title=\"$titleReservation\"><span style='display:none' itemprop='name'>$prefix_ancre - </span>COMPLET</span>";
                }
//                            }
//                            else {
//                                echo "<a href=\"$urlReservation\" itemprop='url' title=\"$titleReservation\"><img border=\"0\" src=\"template/images/decoupes/reserver.png\" align=\"center\"/><span style='display:none' itemprop='name'>$prefix_ancre</span></a>";
//                                if ($text != "")
//                                    echo "<br><font color=$col>$text</font>";
//                            }

            echo "</td>";
            echo "</tr>";


            if ($index_microdata == 2) {
                $index_microdata = 0;
                $index_microdata_ancre = !$index_microdata_ancre;
            }
            else
                $index_microdata++;

        }
        while ($row_rsStage = mysql_fetch_assoc($rsStage));

//        echo "</tbody>";

        // TODO : exemple d'offre flash
/*
        echo '<tr id="_stage-83224-du-2014-04-17-au-2014-04-18" class="boost ville_" itemprop="event" itemscope="" itemtype="http://schema.org/Event"><td class="first" style="text-align:left;">
                <img src="template/images/decoupes/chrono_reel.png" class="chrono">
                <div style="float:left;"><span itemprop="location" itemscope="" itemtype="http://schema.org/EventVenue">
                                    <span itemprop="address" itemscope="" itemtype="http://schema.org/PostalAddress"><a href="recuperation-points-AUBAGNE-13400-13.html" title="stage permis conduire  AUBAGNE (13400)"><b><span itemprop="addressLocality">AUBAGNE</span> <span itemprop="postalCode">13400</span></b></a><br>      <span class="adr" itemprop="streetAddress">
                                            ZI des Paluds
                                        </span>
                                        <span style="display:none">
                                            <span itemprop="addressRegion">Bouches du Rhone 13</span>
                                            <span itemprop="name">AUBAGNE 13400</span>
                                        </span>
                                    </span>
                                </span></div><div style="text-align:right"><a href="#" title="Stage agréé par la préfecture: Bouches du Rhone 13" style="cursor: default"><img class="testdiv" style="cursor: default;width:15px;" src="images/icone_info2.png" onclick="TINY.box.show({iframe:\'infoagrement.php?id_stage=83224\', width:630, height:430, fixed:false, maskopacity:20})"></a></div></td><td width="21%"><meta itemprop="startDate" content="2014-04-17">Jeu 17 Avril <br>Ven 18 Avril </td><td itemprop="offers" width="11%" itemscope="" itemtype="http://schema.org/AggregateOffer"><span class="prix" itemprop="lowPrice">187 &euro;</span> <span class="old_prix">200 &euro;</span>
										<span style="display:none">
											<span itemprop="offerCount">
												20
											</span> places
										</span>
									</td>
                                    <td width="9%"><img align="center" width="30" src="template/images/icones_transaction/cb2.gif"></td>
                                    <td class="last">
                                        <span class="compteur" enddate="2014/04/17"><span class="glyphicon glyphicon-time"></span> Plus que<br />2h 00min 00s</span>
                                        <a href="http://www.prostagespermis.fr/stage-point-83224-44-AUBAGNE.html" itemprop="url" class="bt_reserver" title="stage permis de conduire  AUBAGNE (13400) 44"><span style="display:none" itemprop="name">STAGE à 187 &euro; sur AUBAGNE </span>RÉSERVER</a></td></tr>';
*/

        echo "</table>";
        echo "</div>";
        echo "</br></br>";
        echo "<h2 class='title_normal'>";

        if ($ville != NULL)
        {
            echo "Tous les stages sur $ville";
        }
        else if ($departement != NULL)
        {
            echo "Tous les stages sur ".getDepartement($departement);
        }
        else if ($region != NULL)
        {
            echo "Tous les stages sur ".getRegion($region);
        }

        echo "</h2>";

	}

    return $totalRows_rsStage;
}



function affiche_promos_departements($site, $departement, $query_rsStage)
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

                if (!empty($row_rsStage['boost_possible']) && $row_rsStage['boost_possible'] == 1 && !empty($row_rsStage['boost_actif']) && !empty($row_rsStage['prix_boost']) && ($row_rsStage['nb_boost_allouees'] + $row_rsStage['nb_boost']) <= 2) {
                    $prix_non_boost = $row_rsStage['prix'] . '  &#128;';
                    $row_rsStage['prix'] = $row_rsStage['prix_boost'];
                    $is_boost = true;
                }

		$dateLocal2 = datefr($row_rsStage['date1']);

		if (is_sniffer())
		    $row_rsStage['prix'] = $row_rsStage['prix'] + rand(10,15);

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

		$urlVille = $tabUrl[4];
		$titleReservation = $tabUrl[6]." ".$row_rsStage['ville']." (".sprintf('%05d',$row_rsStage['code_postal']).") ".$row_rsStage['id_membre'];
		$titleVille = $tabUrl[8]." ".$row_rsStage['ville']." (".sprintf('%05d',$row_rsStage['code_postal']).")";


                $idStage = 'stage-' . $row_rsStage['id'] . '-du-' . $row_rsStage['date1'] . '-au-' . $row_rsStage['date2'];
//                $urlVille = 'http://stage-de-recuperation-de-points-pas-cher.prostagespermis.fr/' . $urlVille . '#' . $idStage;
                $urlVille = $urlVille . '#' . $idStage;

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
//                            $text ="PLACES DISPO";
		}

//                if (isset($_GET['afficher_boost']) && $is_boost && !empty($row_rsStage['nb_boost_allouees'])) {
                if ($is_boost && !empty($row_rsStage['nb_boost_allouees'])) {
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

                //ajout_modif_privilege
				if (isset($_SESSION['privilege']))
				{
					if ($row_rsStage['prix_privilege'] != NULL &&
						$row_rsStage['prix_privilege'] > 0 &&
						($row_rsStage['prix_privilege'] < $row_rsStage['prix']))

							$prix = $row_rsStage['prix_privilege'];  //>

					echo '<span class="col_1">
							<span itemprop="location" itemscope itemtype="http://schema.org/EventVenue">
								<span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
									<a href="'.$urlVille.'" title="'.$titleVille.'">
										<b><span itemprop="addressLocality">'.substr($row_rsStage['ville'],0,18).'</span></b>
									</a>
									<span style="display:none">
										<span itemprop="postalCode">'.sprintf("%05d",$row_rsStage['code_postal']).'</span>
										<span class="adr" itemprop="streetAddress">
											'.$adr_sans_nom.'
										</span>
										<span itemprop="addressRegion">'.getDepartement($row_rsStage['departement']).'</span>
										<span itemprop="name">' . strtoupper($row_rsStage['ville']) . ' ' . $row_rsStage['code_postal'] . '</span>
									</span>
								</span>
							</span>
							<br />
							<span class="date"><meta itemprop="startDate" content="' . $row_rsStage['date1'] . '" />'.
								MySQLDateToExplicitDate2($row_rsStage['date1']).'
							</span>
						</span>
						<span class="col_2" itemprop="offers" itemscope itemtype="http://schema.org/AggregateOffer">';

						if ($prix != $row_rsStage['prix'])
						{
							echo '<span class="old_prix" itemprop="highPrice">'.$row_rsStage['prix'].' &#128;</span>
							<span class="prix_privilege" itemprop="lowPrice"><b>'.$prix.' &#128;</b></span>';
						}
						else
						{
							echo '<span><b>'.$row_rsStage['prix'].' &#128;</b></span>';
						}
						echo '<span style="display:none">
								<span itemprop="offerCount" style="display:none">'.$row_rsStage['nb_places_allouees'].'</span> places
							</span>
						</span>';
				}
				else
				{
				//!ajout_modif_privilege

                	echo '<span class="col_1">
                        <span itemprop="location" itemscope itemtype="http://schema.org/EventVenue">
                            <span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
                                <a href="'.$urlVille.'" title="'.$titleVille.'">
                                    <b><span itemprop="addressLocality">'.substr($row_rsStage['ville'],0,18).'</span></b>
                                </a>
                                <span style="display:none">
                                    <span itemprop="postalCode">'.sprintf("%05d",$row_rsStage['code_postal']).'</span>
                                    <span class="adr" itemprop="streetAddress">
                                        '.$adr_sans_nom.'
                                    </span>
                                    <span itemprop="addressRegion">'.getDepartement($row_rsStage['departement']).'</span>
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
                        <span class="old_prix" itemprop="highPrice">'.$prix_non_boost.'</span>
                        <span class="prix" itemprop="lowPrice"><b>'.$prix.' &#128;</b></span>
                        <span style="display:none">
                            <span itemprop="offerCount" style="display:none">'.$row_rsStage['nb_places_allouees'].'</span> places
                        </span>
                    </span>';
                }

                $strPad = ' .';
                $len_prix = 11;
                $len_ville = 25;

//                $prefix_ancre = 'STAGE . à . . '. str_pad($row_rsStage['prix'] . ' &#128;', $len_prix, $strPad, STR_PAD_RIGHT) . ' . . sur . ' . strtoupper($row_rsStage['ville']) . (empty($index_microdata)? ($index_microdata_ancre? ' - Profitez-en !' : " - Plus d'infos") : '');

                $prefix_ancre = 'STAGE à '. $row_rsStage['prix'] . ' &#128; sur ' . strtoupper($row_rsStage['ville']);

                echo "<a class='col_3' href=\"$urlVille\" itemprop='url' class=\"bt_reserver\" title=\"$titleReservation\"><span style='display:none' itemprop='name'>$prefix_ancre </span><span class='bt_voir'>voir</span></a>";
                echo '<div style="clear:both"></div>';
                echo "</li>";

            if ($index_microdata == 2) {
                $index_microdata = 0;
                $index_microdata_ancre = !$index_microdata_ancre;
            }
            else
                $index_microdata++;

	}

}



function tableau_stages_groupe_ville($site, $departement = NULL, $ville = NULL, $code_postal = NULL, $region = NULL, $chemin = NULL, $id_groupe_ville = NULL)
{
	require_once ("../common/functions.php");

	$query_stage_where = "";
	$query_stage_from = "";
	$query_stage_limit = "";
	$query_site_select = "";
	$query_site_from = "";
	$query_site_where = "";
	$query_avis_where = "";


    if ($departement != NULL){
        $query_stage_where = " AND departement = $departement ";
        $query_site_where = " AND departement = $departement ";
        $query_avis_where = " AND departement = $departement ";
    }
    if ($id_groupe_ville != NULL) {
        $query_stage_where .= " AND ville.id_groupe_ville = $id_groupe_ville ";
        $query_avis_where .= " AND ville.id_groupe_ville = $id_groupe_ville ";
    }
    else if ($ville != NULL) {
        $query_stage_where .= " AND ville = '$ville' ";
        $query_avis_where .= " AND site.ville = '$ville' ";
    }
    if ($code_postal != NULL){
        $query_stage_where .= " AND code_postal = $code_postal ";
        $query_avis_where .= " AND site.code_postal = $code_postal ";
    }
    if ($region != NULL) {
        $query_stage_where .= " AND id_region = $region ";
        $query_avis_where .= " AND id_region = $region ";

        $query_site_from .= ", departement";
        $query_site_where .= " AND site.departement = departement.code_departement AND id_region = '$region' ";
    }

    if (!empty($chemin)) {
        switch($chemin) {
            case CHEMIN_PAS_CHER :
                $query_stage_where_temp = ' ( stage.boost_actif = 1 and membre.boost_possible = 1 ) ';

                if (!empty($departement) || !empty($ville) || !empty($code_postal) || !empty($region)) {
                     $query_stage_where_temp .= ' OR ( prix < prix_boost_max ) ';
                }
                else
                     $query_stage_limit = ' LIMIT 5';

                $query_stage_where .= " AND ($query_stage_where_temp) ";

                break;
        }
    }

    if ($site == psp || $site == pap){
        $query_site_select = " code_postal, ville ";
    }
    else { $query_site_select = " ville ";}

    $order = "date1 ASC, prix_final ASC";
    if (isset($_POST['tri']) && $_POST['tri'] == "tri_prix")
    {
        $order = "prix_final ASC, date1 ASC";
    }


    $query_stage = sprintf("
                            SELECT
                                    date1,
                                    date2,
                                    nb_places_allouees,
                                    nb_boost_allouees,
                                    nb_boost,
                                    nb_inscrits,
                                    nb_preinscrits,
                                    prix,
                                    prix_boost,
                                    prix_privilege,
                                    boost_actif,
                                    membre.boost_possible,
                                    stage.id,
                                    stage.id_externe,
                                    stage.id_membre,
                                    motCle,
                                    site.nom,
                                    site.ville,
                                    site.adresse,
                                    site.departement,
                                    ville.id_ville,
                                    groupe_ville.id_groupe_ville,
                                    groupe_ville.alias_groupe_ville,
                                    plan,
                                    code_postal,
                                    departement.prix_boost_max,
                                    membre.types_paiement,
                                    case when boost_actif then prix_boost else prix end as prix_final

                            FROM
                                    stage, site, membre, departement, ville, groupe_ville %s

                            WHERE
                                    id_site = site.id AND
                                    site.id_ville = ville.id_ville AND
                                    groupe_ville.id_groupe_ville = ville.id_groupe_ville AND
                                    site.departement = departement.code_departement AND
                                    date1 > now() AND
                                    date1 < (CURDATE() + INTERVAL 90 DAY) AND
                                    annule = 0 AND
                                    stage.visible = 1 AND
                                    stage.id_membre <> 188 AND
                                    stage.id_membre = membre.id AND
                                    site.id_membre = membre.id
                                    %s

                            ORDER BY %s
                            %s", $query_stage_from, $query_stage_where, $order, $query_stage_limit);
//    var_dump($query_stage);
    $query_site = sprintf(
                "SELECT
                    DISTINCT %s

                 FROM
                    site, stage %s

                 WHERE
                    id_site = site.id AND
                    date1 > now() AND
                    date1 < (CURDATE() + INTERVAL 90 DAY) AND
                    nb_places_allouees > 0 AND
                    stage.visible = 1 AND
                    annule = 0
                                            %s

                 GROUP BY code_postal, ville ORDER BY ville ASC", $query_site_select, $query_site_from, $query_site_where);

    $query_avis = sprintf(
                "SELECT
                    avis_stagiaire.*,
                    stagiaire.prenom
                 FROM
                    avis_stagiaire
                        inner join stage on avis_stagiaire.id_stage = stage.id
                        inner join site on site.id = stage.id_site
                        inner join ville on site.id_ville = ville.id_ville
                        inner join stagiaire on stage.id = stagiaire.id_stage
                 WHERE
                    true
                    %s
                 ORDER BY avis_timestamp desc", $query_avis_where);

//    echo '<div style="display:none">'.$query_avis.'</div>';
    return affiche_stages($site, $departement, $ville, $code_postal, $region, $chemin, $query_site, $query_stage);
}




function tableau_stages_groupe_ville_nouveau($site, $departement = NULL, $ville = NULL, $code_postal = NULL, $region = NULL, $chemin = NULL, $id_groupe_ville = NULL)
{
	require_once ("../common/functions.php");

	$query_stage_where = "";
	$query_stage_from = "";
	$query_stage_limit = "";
	$query_site_select = "";
	$query_site_from = "";
	$query_site_where = "";
	$query_avis_where = "";


    if ($departement != NULL){
        $query_stage_where = " AND departement = $departement ";
        $query_site_where = " AND departement = $departement ";
        $query_avis_where = " AND departement = $departement ";
    }
    if ($id_groupe_ville != NULL) {
        $query_stage_where .= " AND ville.id_groupe_ville = $id_groupe_ville ";
        $query_avis_where .= " AND ville.id_groupe_ville = $id_groupe_ville ";
    }
    else if ($ville != NULL) {
        $query_stage_where .= " AND ville = '$ville' ";
        $query_avis_where .= " AND site.ville = '$ville' ";
    }
    if ($code_postal != NULL){
        $query_stage_where .= " AND code_postal = $code_postal ";
        $query_avis_where .= " AND site.code_postal = $code_postal ";
    }
    if ($region != NULL) {
        $query_stage_where .= " AND id_region = $region ";
        $query_avis_where .= " AND id_region = $region ";

        $query_site_from .= ", departement";
        $query_site_where .= " AND site.departement = departement.code_departement AND id_region = '$region' ";
    }

    if ($site == psp || $site == pap){
        $query_site_select = " code_postal, ville ";
    }
    else { $query_site_select = " ville ";}

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
                                    nb_boost_allouees,
                                    nb_boost,
                                    nb_inscrits,
                                    nb_preinscrits,
                                    prix,
                                    prix_avant_premium,
                                    prix_barre,
                                    prix_privilege,
                                    stage.id,
                                    stage.id_externe,
                                    stage.id_membre,
                                    option_visibilite,
                                    option_reversement,
                                    motCle,
                                    site.nom,
                                    site.ville,
                                    site.adresse,
                                    site.departement,
                                    ville.id_ville,
                                    groupe_ville.id_groupe_ville,
                                    groupe_ville.alias_groupe_ville,
                                    plan,
                                    code_postal,
                                    departement.prix_boost_max,
                                    membre.types_paiement

                            FROM
                                    stage, site, membre, departement, ville, groupe_ville %s

                            WHERE
                                    id_site = site.id AND
                                    site.id_ville = ville.id_ville AND
                                    groupe_ville.id_groupe_ville = ville.id_groupe_ville AND
                                    site.departement = departement.code_departement AND
                                    date1 > now() AND
                                    date1 < (CURDATE() + INTERVAL 90 DAY) AND
                                    annule = 0 AND
                                    stage.visible = 1 AND
                                    stage.id_membre = membre.id AND
                                    site.id_membre = membre.id
                                    %s

                            ORDER BY %s
                            %s", $query_stage_from, $query_stage_where, $order, $query_stage_limit);

    $query_site = sprintf(
                "SELECT
                    DISTINCT %s

                 FROM
                    site, stage %s

                 WHERE
                    id_site = site.id AND
                    date1 > now() AND
                    date1 < (CURDATE() + INTERVAL 90 DAY) AND
                    nb_places_allouees > 0 AND
                    stage.visible = 1 AND
                    annule = 0
                                            %s

                 GROUP BY code_postal, ville ORDER BY ville ASC", $query_site_select, $query_site_from, $query_site_where);

    return affiche_stages_nouveau($site, $departement, $ville, $code_postal, $region, $chemin, $query_site, $query_stage, false, $id_groupe_ville);
}


function tableau_stages_autour($site, $departement)
{
	require_once ("../common/functions.php");

	$query_stage_where = "";
	$query_stage_from = "";
	$query_stage_limit = " limit 20";
	$query_site_select = "";
	$query_site_from = "";
	$query_site_where = "";

    $dep_voisins = getDepartementVoisin($departement);

    $dep_voisins_arr = explode(',', $dep_voisins);
    $liste_dep_voisins = $sep_liste_dep_voisins = '';
    foreach($dep_voisins_arr as $dep_voisin) {
        $liste_dep_voisins .= "$sep_liste_dep_voisins'$dep_voisin'";
        $sep_liste_dep_voisins = ',';
    }

//	if ($departement != NULL)
//	{
		if ($departement != NULL){
            $query_stage_where = " AND departement in ($liste_dep_voisins) ";
            $query_site_where = " AND departement in ($liste_dep_voisins) ";
        }

		if ($site == psp || $site == pap || $site == "psp_no_stage_ville"){
            $query_site_select = " code_postal, ville ";
//            if ($region != NULL)
//                $query_site_add = ", region ";
        }
		else { $query_site_select = " ville ";}


        $order = "date1 ASC, prix_final ASC";
        if (isset($_POST['tri']) && $_POST['tri'] == "tri_prix")
        {
            $order = "prix_final ASC, date1 ASC";
        }

        $query_stage = sprintf("
                                                SELECT
                                                        date1,
                                                        date2,
                                                        nb_places_allouees,
                                                        nb_boost_allouees,
                                                        nb_boost,
                                                        nb_inscrits,
                                                        nb_preinscrits,
                                                        prix,
                                                        prix_boost,
                                                        prix_privilege,
                                                        boost_actif,
                                                        membre.boost_possible,
                                                        stage.id,
                                                        stage.id_externe,
                                                        stage.id_membre,
                                                        motCle,
                                                        site.nom,
                                                        site.ville,
                                                        site.id_ville,
                                                        site.adresse,
                                                        site.departement,
                                                        plan,
                                                        code_postal,
                                                        departement.prix_boost_max,
                                                        membre.types_paiement,
                                                        case when boost_actif then prix_boost else prix end as prix_final

                                                FROM
                                                        stage, site, membre, departement %s

                                                WHERE
                                                        id_site = site.id AND
                                                        site.departement = departement.code_departement AND
                                                        date1 > now() AND
                                                        date1 < (CURDATE() + INTERVAL 90 DAY) AND
                                                        annule = 0 AND
                                                        stage.visible = 1 AND
                                                        stage.id_membre = membre.id AND
                                                        site.id_membre = membre.id
                                                        %s

                ORDER BY %s
                                            %s", $query_stage_from, $query_stage_where, $order, $query_stage_limit);
        if (isset($_GET['debug']))
            echo '<div style="display:none">query_stage : '.$query_stage.'</div>';

		$query_site = sprintf(
					"SELECT
						DISTINCT %s

					 FROM
					 	site, stage %s

					 WHERE
						id_site = site.id AND
						date1 > now() AND
						date1 < (CURDATE() + INTERVAL 90 DAY) AND
						nb_places_allouees > 0 AND
						stage.visible = 1 AND
						annule = 0
                                                %s

					 GROUP BY code_postal, ville ORDER BY ville ASC", $query_site_select, $query_site_from, $query_site_where);

		return affiche_stages_nouveau($site, $departement, null, null, null, null, $query_site, $query_stage, true);
//	}
}


function tableau_stages_groupe_ville_autour($site, $departement)
{

	require_once ("../common/functions.php");

	$query_stage_where = "";
	$query_stage_from = "";
	$query_stage_limit = " limit 20";
	$query_site_select = "";
	$query_site_from = "";
	$query_site_where = "";


    $dep_voisins = getDepartementVoisin($departement);

    $dep_voisins_arr = explode(',', $dep_voisins);
    $liste_dep_voisins = $sep_liste_dep_voisins = '';
    foreach($dep_voisins_arr as $dep_voisin) {
        $liste_dep_voisins .= "$sep_liste_dep_voisins'$dep_voisin'";
        $sep_liste_dep_voisins = ',';
    }

    if ($departement != NULL){
        $query_stage_where = " AND departement in ($liste_dep_voisins) ";
        $query_site_where = " AND departement in ($liste_dep_voisins) ";
    }


    if ($site == psp || $site == pap){
        $query_site_select = " code_postal, ville ";
    }
    else { $query_site_select = " ville ";}

    $order = "date1 ASC, prix_final ASC";
    if (isset($_POST['tri']) && $_POST['tri'] == "tri_prix")
    {
        $order = "prix_final ASC, date1 ASC";
    }


    $query_stage = sprintf("
                            SELECT
                                    date1,
                                    date2,
                                    nb_places_allouees,
                                    nb_boost_allouees,
                                    nb_boost,
                                    nb_inscrits,
                                    nb_preinscrits,
                                    prix,
                                    prix_boost,
                                    prix_privilege,
                                    boost_actif,
                                    membre.boost_possible,
                                    stage.id,
                                    stage.id_externe,
                                    stage.id_membre,
                                    motCle,
                                    site.nom,
                                    site.ville,
                                    site.adresse,
                                    site.departement,
                                    ville.id_ville,
                                    groupe_ville.id_groupe_ville,
                                    groupe_ville.alias_groupe_ville,
                                    plan,
                                    code_postal,
                                    departement.prix_boost_max,
                                    membre.types_paiement,
                                    case when boost_actif then prix_boost else prix end as prix_final

                            FROM
                                    stage, site, membre, departement, ville, groupe_ville %s

                            WHERE
                                    id_site = site.id AND
                                    site.id_ville = ville.id_ville AND
                                    groupe_ville.id_groupe_ville = ville.id_groupe_ville AND
                                    site.departement = departement.code_departement AND
                                    date1 > now() AND
                                    date1 < (CURDATE() + INTERVAL 90 DAY) AND
                                    annule = 0 AND
                                    stage.visible = 1 AND
                                    stage.id_membre = membre.id AND
                                    site.id_membre = membre.id
                                    %s

                            ORDER BY %s
                            %s", $query_stage_from, $query_stage_where, $order, $query_stage_limit);
//    var_dump($query_stage);
    $query_site = sprintf(
                "SELECT
                    DISTINCT %s

                 FROM
                    site, stage %s

                 WHERE
                    id_site = site.id AND
                    date1 > now() AND
                    date1 < (CURDATE() + INTERVAL 90 DAY) AND
                    nb_places_allouees > 0 AND
                    stage.visible = 1 AND
                    annule = 0
                                            %s

                 GROUP BY code_postal, ville ORDER BY ville ASC", $query_site_select, $query_site_from, $query_site_where);


//    echo '<div style="display:none">'.$query_avis.'</div>';
    return affiche_stages($site, $departement, null, null, null, null, $query_site, $query_stage, true);
}


function tableau_stages($site, $departement = NULL, $ville = NULL, $code_postal = NULL, $region = NULL, $chemin = NULL, $id_groupe_ville = NULL)
{
	require_once ("../common/functions.php");

	$query_stage_where = "";
	$query_stage_from = "";
	$query_stage_limit = "";
	$query_site_select = "";
	$query_site_from = "";
	$query_site_where = "";

//	if ($departement != NULL)
//	{
		if ($departement != NULL){
            $query_stage_where = " AND departement = $departement ";
            $query_site_where = " AND departement = $departement ";
        }
		if ($ville != NULL) {	$query_stage_where .= " AND ville = '$ville' ";}

		//if ($code_postal != NULL){	$query_stage_where .= " AND code_postal = $code_postal ";}
		if ($code_postal != NULL)
		{
			$tab = groupeCodePostaux($code_postal);
			$query_stage_where .= $tab[0];
		}

		if ($region != NULL) {
//            $query_stage_from = ", departement";
//            $query_stage_where = " AND site.departement = departement.code_departement AND id_region = '$region' ";
            $query_stage_where .= " AND id_region = $region ";

            $query_site_from .= ", departement";
            $query_site_where .= " AND site.departement = departement.code_departement AND id_region = '$region' ";
        }

        if (!empty($chemin)) {
            switch($chemin) {
                case CHEMIN_PAS_CHER :
                    $query_stage_where_temp = ' ( stage.boost_actif = 1 and membre.boost_possible = 1 ) ';

                    if (!empty($departement) || !empty($ville) || !empty($code_postal) || !empty($region)) {
                         $query_stage_where_temp .= ' OR ( prix < prix_boost_max ) ';
                    }
                    else
                         $query_stage_limit = ' LIMIT 5';

                    $query_stage_where .= " AND ($query_stage_where_temp) ";

                    break;
            }
        }

		if ($site == psp || $site == pap || $site == "psp_no_stage_ville"){
            $query_site_select = " code_postal, ville ";
//            if ($region != NULL)
//                $query_site_add = ", region ";
        }
		else { $query_site_select = " ville ";}


        $order = "date1 ASC, prix_final ASC";
        if (isset($_POST['tri']) && $_POST['tri'] == "tri_prix")
        {
            $order = "prix_final ASC, date1 ASC";
        }


//		$query_stage = sprintf("
//							SELECT
//								date1,
//								date2,
//								nb_places_allouees,
//								nb_inscrits,
//								nb_preinscrits,
//								prix,
//								prix_boost,
//								boost_actif,
//								membre.boost_possible,
//								stage.id,
//								stage.id_externe,
//								stage.id_membre,
//								motCle,
//								site.nom,
//								site.ville,
//								site.adresse,
//								site.departement,
//								plan,
//								code_postal,
//								membre.types_paiement
//
//							FROM
//								stage, site, membre, departement %s
//
//							WHERE
//								id_site = site.id AND
//								site.departement = departement.code_departement AND
//								date1 > now() AND
//								date1 < (CURDATE() + INTERVAL 90 DAY) AND
//								annule = 0 AND
//								stage.id_membre = membre.id AND
//								nb_places_allouees > 0
//								%s
//
//							ORDER BY %s ASC", $query_stage_from, $query_stage_where, $order);
//
//                if (isset($_GET['afficher_boost'])) {

                    $query_stage = sprintf("
                                                            SELECT
                                                                    date1,
                                                                    date2,
                                                                    nb_places_allouees,
                                                                    nb_boost_allouees,
                                                                    nb_boost,
                                                                    nb_inscrits,
                                                                    nb_preinscrits,
                                                                    prix,
                                                                    prix_boost,
                                                                    prix_privilege,
                                                                    boost_actif,
                                                                    membre.boost_possible,
                                                                    stage.id,
                                                                    stage.id_externe,
                                                                    stage.id_membre,
                                                                    motCle,
                                                                    site.nom,
                                                                    site.ville,
                                                                    site.id_ville,
                                                                    site.adresse,
                                                                    site.departement,
                                                                    plan,
                                                                    code_postal,
                                                                    departement.prix_boost_max,
                                                                    membre.types_paiement,
                                                                    case when boost_actif then prix_boost else prix end as prix_final

                                                            FROM
                                                                    stage, site, membre, departement %s

                                                            WHERE
                                                                    id_site = site.id AND
                                                                    site.departement = departement.code_departement AND
                                                                    date1 > now() AND
                                                                    date1 < (CURDATE() + INTERVAL 90 DAY) AND
                                                                    annule = 0 AND
                                                                    stage.visible = 1 AND
                                                                    stage.id_membre = membre.id AND
                                                                    stage.id_membre <> 188 AND
                                                                    site.id_membre = membre.id
                                                                    %s

							ORDER BY %s
                                                        %s", $query_stage_from, $query_stage_where, $order, $query_stage_limit);
                    if (isset($_GET['debug']))
                        echo '<div style="display:none">query_stage : '.$query_stage.'</div>';
//                }

		$query_site = sprintf(
					"SELECT
						DISTINCT %s

					 FROM
					 	site, stage %s

					 WHERE
						id_site = site.id AND
						date1 > now() AND
						date1 < (CURDATE() + INTERVAL 90 DAY) AND
						nb_places_allouees > 0 AND
						stage.visible = 1 AND
						annule = 0
                                                %s

					 GROUP BY code_postal, ville ORDER BY ville ASC", $query_site_select, $query_site_from, $query_site_where);

		return affiche_stages($site, $departement, $ville, $code_postal, $region, $chemin, $query_site, $query_stage);
//	}
}

function getAvis($departement = NULL, $ville = NULL, $code_postal = NULL, $region = NULL, $chemin = NULL, $id_groupe_ville = NULL, $id_site = NULL, $id_membre = NULL) {
	require_once ("../common/functions.php");
	require ("../connections/stageconnect.php");

	mysql_select_db($database_stageconnect, $stageconnect);

	$query_avis_where = "";

    if ($id_site != NULL && $id_membre != NULL) {
        $query_avis_where = " AND site.id = $id_site AND site.id_membre = $id_membre ";
    }
    else {
        if ($departement != NULL){
            $query_avis_where = " AND site.departement = $departement ";
        }
        if ($id_groupe_ville != NULL) {
            $query_avis_where .= " AND ville.id_groupe_ville = $id_groupe_ville ";
        }
        else if ($ville != NULL) {
            $query_avis_where .= " AND site.ville = '$ville' ";
        }
        /*
        if ($code_postal != NULL){
            $query_avis_where .= " AND site.code_postal = $code_postal ";
        }*/

		if ($code_postal != NULL)
		{
			$tab = groupeCodePostaux($code_postal);
			$query_avis_where .= $tab[0];
		}

        if ($region != NULL) {
            $query_avis_where .= " AND id_region = $region ";
        }
    }

    $query_avis = sprintf(
                "SELECT
                    avis_stagiaire.*
                 FROM
                    avis_stagiaire
                        inner join site on avis_stagiaire.id_site = site.id
                        inner join ville on site.id_ville = ville.id_ville
                 WHERE
                    avis_stagiaire.note > 2
                    %s
                 ORDER BY avis_timestamp desc", $query_avis_where);

//    echo '<div style="display:none">$query_avis : '.$query_avis.'</div>';
    $rsAvis = mysql_query($query_avis, $stageconnect) or die(mysql_error());

    $h3_avis = 'Avis stage de récupération de points';
    $empty_avis = "Il n'y a aucun avis stage de récupération";

    if ($ville != NULL)
    {
        $h3_avis = 'Avis stage de récupération de points '.$ville;
        $empty_avis = "Il n'y a aucun avis stage de récupération sur $ville";
    }
    else if ($departement != NULL)
    {
        $h3_avis = 'Avis stage de récupération de points '.$departement;
        $empty_avis = "Il n'y a aucun avis stage de récupération sur $departement";
    }
    else if ($region != NULL)
    {
        $h3_avis = 'Avis stage de récupération de points '.$region;
        $empty_avis = "Il n'y a aucun avis stage de récupération sur $region";
    }

    $total_note = 0;
    $nb_note = 0;
    $avis = '';

    while($row_avis = mysql_fetch_assoc($rsAvis)) {
        $date = date('d/m/Y', $row_avis['avis_timestamp'] *1);
        $avis .= '<li>
                    <span class="avis_msg">
                        <span class="avis_etoiles_group avis_etoiles_group_0">
                            <span class="avis_etoiles_group avis_etoiles_group_'.$row_avis['note'].'"></span>
                        </span>
                        "'.$row_avis['avis'].'"
                    </span>
                    <br />
                    <span class="avis_footer">
                        Noté <span class="avis_note">'.$row_avis['note'].'/5</span>
                        - le <span class="avis_date">'.$date.'</span>
                        par <span class="avis_pseudo">'.firstToUpper($row_avis['prenom']).'</span>
                    </span>
                </li>';

        $total_note += $row_avis['note'];
        $nb_note++;
    }

    if ($avis == '')
        $avis = '<li>'.$empty_avis.'</li>';


    $width_avg_note = 0;

    if ($total_note) {
        $width_avg_note = (int) $total_note / $nb_note * 20;
//        $total_note = round($total_note / $nb_note, 1);
    }

    $avg_note = '<span class="avis_etoiles_group_cut avis_etoiles_group_0">
                        <span class="avis_etoiles_group_cut" style="width:'.$width_avg_note.'px"></span>
                    </span>';

    $avis = '<h3>'.$avg_note.'<span class="avis_h3_content">'.$nb_note.' '.$h3_avis.'</span></h3><ul>'.$avis. '</ul>';

    return $avis;
}



function tableau_stages_nouveau($site, $departement = NULL, $ville = NULL, $code_postal = NULL, $region = NULL, $chemin = NULL, $id_groupe_ville = NULL)
{
	require_once ("../common/functions.php");

	$query_stage_select = "";
	$query_stage_where = "";
	$query_stage_from = "";
	$query_stage_limit = "";
	$query_site_select = "";
	$query_site_from = "";
	$query_site_where = "";

//	if ($departement != NULL)
//	{
		if ($departement != NULL){
            $query_stage_where = " AND departement = $departement ";
            $query_site_where = " AND departement = $departement ";

            if (in_array($departement, array(22, 29, 56, 03))) {
                $query_stage_select .= ", ville.id_groupe_ville, groupe_ville.alias_groupe_ville";
                $query_stage_from .= " inner join ville on site.id_ville = ville.id_ville inner join groupe_ville on ville.id_groupe_ville = groupe_ville.id_groupe_ville";
            }
        }
		if ($ville != NULL) {	$query_stage_where .= " AND ville = '$ville' ";}


        if ($id_groupe_ville != NULL) {
            $query_stage_where .= " AND ville.id_groupe_ville = $id_groupe_ville ";
        }

		//if ($code_postal != NULL){	$query_stage_where .= " AND code_postal = $code_postal ";}
		if ($code_postal != NULL)
		{
			$tab = groupeCodePostaux($code_postal);
			$query_stage_where .= $tab[0];
		}

		if ($region != NULL) {
//            $query_stage_from = ", departement";
//            $query_stage_where = " AND site.departement = departement.code_departement AND id_region = '$region' ";
            $query_stage_where .= " AND id_region = $region ";

            $query_site_from .= ", departement";
            $query_site_where .= " AND site.departement = departement.code_departement AND id_region = '$region' ";
        }

		if ($site == psp || $site == pap || $site == "psp_no_stage_ville"){
            $query_site_select = " code_postal, ville ";
//            if ($region != NULL)
//                $query_site_add = ", region ";
        }
		else { $query_site_select = " ville ";}


        $order = "date1 ASC, prix ASC";
        if (isset($_POST['tri']) && $_POST['tri'] == "tri_prix")
        {
            $order = "prix ASC, date1 ASC";
        }


//		$query_stage = sprintf("
//							SELECT
//								date1,
//								date2,
//								nb_places_allouees,
//								nb_inscrits,
//								nb_preinscrits,
//								prix,
//								prix_boost,
//								boost_actif,
//								membre.boost_possible,
//								stage.id,
//								stage.id_externe,
//								stage.id_membre,
//								motCle,
//								site.nom,
//								site.ville,
//								site.adresse,
//								site.departement,
//								plan,
//								code_postal,
//								membre.types_paiement
//
//							FROM
//								stage, site, membre, departement %s
//
//							WHERE
//								id_site = site.id AND
//								site.departement = departement.code_departement AND
//								date1 > now() AND
//								date1 < (CURDATE() + INTERVAL 90 DAY) AND
//								annule = 0 AND
//								stage.id_membre = membre.id AND
//								nb_places_allouees > 0
//								%s
//
//							ORDER BY %s ASC", $query_stage_from, $query_stage_where, $order);
//
//                if (isset($_GET['afficher_boost'])) {

                    $query_stage = sprintf("
                                                            SELECT
                                                                    date1,
                                                                    date2,
                                                                    nb_places_allouees,
                                                                    nb_boost_allouees,
                                                                    nb_boost,
                                                                    nb_inscrits,
                                                                    nb_preinscrits,
                                                                    prix,
                                                                    prix_avant_premium,
                                                                    prix_barre,
                                                                    prix_privilege,
                                                                    stage.id,
                                                                    stage.id_externe,
                                                                    stage.id_membre,
                                                                    option_visibilite,
                                                                    option_reversement,
                                                                    motCle,
                                                                    site.nom,
                                                                    site.ville,
                                                                    site.id_ville,
                                                                    site.adresse,
                                                                    site.departement,
                                                                    plan,
                                                                    code_postal,
                                                                    departement.prix_boost_max,
                                                                    membre.cb_actif,
                                                                    membre.types_paiement
                                                                    %s

                                                            FROM
                                                                    stage, membre, departement, site %s

                                                            WHERE
                                                                    id_site = site.id AND
                                                                    site.departement = departement.code_departement AND
                                                                    date1 > now() AND
                                                                    date1 < (CURDATE() + INTERVAL 90 DAY) AND
                                                                    annule = 0 AND
                                                                    stage.id_membre <> 188 AND
                                                                    stage.visible = 1 AND
                                                                    stage.id_membre = membre.id AND
                                                                    site.id_membre = membre.id
                                                                    %s

							ORDER BY %s
                                                        %s", $query_stage_select, $query_stage_from, $query_stage_where, $order, $query_stage_limit);
                    if (isset($_GET['debug']))
                        echo '<div style="display:none">query_stage : '.$query_stage.'</div>';
//                }

                    // TODO : enlever le or id_membre = 188  à la mise en production

		$query_site = sprintf(
					"SELECT
						DISTINCT %s

					 FROM
					 	site, stage %s

					 WHERE
						id_site = site.id AND
						date1 > now() AND
						date1 < (CURDATE() + INTERVAL 90 DAY) AND
						nb_places_allouees > 0 AND
						stage.visible = 1 AND
						annule = 0
                                                %s

					 GROUP BY code_postal, ville ORDER BY ville ASC", $query_site_select, $query_site_from, $query_site_where);

		return affiche_stages_nouveau($site, $departement, $ville, $code_postal, $region, $chemin, $query_site, $query_stage);
//	}
}

function affiche_dynamic_villes($code_postal, $ville, $site)
{

	require ("../connections/stageconnect.php");

	mysql_select_db($database_stageconnect, $stageconnect);

	$sql = "SELECT h2, contenu, code_postal FROM villes_dynamic_content WHERE code_postal = \"$code_postal\"";
	$rs_dynamic = mysql_query($sql, $stageconnect);
	$row_dynamic = mysql_fetch_assoc($rs_dynamic);
	$total_dynamic = mysql_num_rows($rs_dynamic);

	mysql_close($stageconnect);

    if ($total_dynamic == 1 && $ville != NULL && $site == psp)
    {
       	echo "<br /><br /><h2>".$row_dynamic['h2']."</h2>";
       	echo $row_dynamic['contenu'];
    }
}


function affiche_stages_nouveau($site, $departement, $ville, $code_postal, $region, $chemin, $query_site, $query_stage, $iframe = false, $id_groupe_ville = NULL)
{
	//random du département si pas de stages dans une ville
	//-----------------------------------------------------
	$random = false;
	if ($site == "psp_no_stage_ville")
	{
		$nb_stages_aleatoires = 5;
		$site = psp;
		$random = true;
	}

	require_once ("../common/functions.php");
	require ("../connections/stageconnect.php");

	mysql_select_db($database_stageconnect, $stageconnect);

	$rsSite = mysql_query($query_site, $stageconnect) or die(mysql_error());
	$row_rsSite	= mysql_fetch_assoc($rsSite);
	$totalRows_rsSite = mysql_num_rows($rsSite);

	$rsStage = mysql_query($query_stage, $stageconnect) or die(mysql_error());
	$row_rsStage = mysql_fetch_assoc($rsStage);
	$totalRows_rsStage = mysql_num_rows($rsStage);

	/*
	//selection du contenu dynamique
	//-----------------------------
	$sql = "SELECT h2, contenu, code_postal FROM villes_dynamic_content WHERE code_postal = \"$code_postal\"";
	$rs_dynamic = mysql_query($sql, $stageconnect);
	$row_dynamic = mysql_fetch_assoc($rs_dynamic);
	$total_dynamic = mysql_num_rows($rs_dynamic);
	*/

	mysql_close($stageconnect);

	if ($totalRows_rsStage == 0)
		return -1;

    if (false && empty($iframe)) {
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
        else if ($region != NULL)
        {

                echo "<h2>";
                echo "Récupération de points de permis ".getRegion($region).":";
                echo "</h2>";
        }
        else
        {

                echo "<h2>";
                echo "Récupération de points de permis sur toute la france :";
                echo "</h2>";
        }
    }

    $tri = 'tri_date';
    if (!empty($_POST['tri']) && $_POST['tri'] == "tri_prix")
        $tri = 'tri_prix';


	//affichage menu déroulant
	//------------------------
    /*
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


				if (($i % 4) == 0)
				{
					echo "<tr height='40px'>";
				}

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

	else */if ($totalRows_rsSite > 0 && $site != psp)
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
		echo "<a href='http://www.prostagespermis.fr/$url_departement'><strong><< AFFICHER TOUS LES STAGES PERMIS DU DEPARTEMENT ".getDepartement($departement)."</strong></a><br><br>";
	}

	if ($totalRows_rsStage > 0)
	{

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

                    echo '<table id="tableau-stages" cellspacing="0"';

//                    if ($chemin == CHEMIN_PAS_CHER)
//                        echo ' class="boost" ';

                    echo '>';

                    $class_tri_prix = '';
                    $class_tri_date = 'tri_selected';

                    if ($tri == 'tri_prix') {
                            $class_tri_prix = 'tri_selected';
                            $class_tri_date = '';
                    }

//                    echo "<thead>";
//                            echo "<tr>";
//                                    echo "<th class=\"titre-arrondi\" scope=\"col\" width=\"42%\">Villes</th>";
//                                    echo "<form method=\"post\" action=\"$monUrl\" name=\"tri_date\">";
//                                    echo "<input type=\"hidden\" VALUE=\"tri_date\" name=\"tri\" />";
//                                    echo "<th class=\"arrondi-c1\" scope=\"col\" width=\"18%\">
//                                            <a class=\"$class_tri_date\" style=\"cursor:pointer;\" title=\"Trier par dates\" onclick=\"window.document.tri_date.submit()\">Dates<br><img src=\"images/fleche_bas.gif\"></a></th>";
//                                    echo "</form>";
//                                    echo "<form method=\"post\" action=\"$monUrl\" name=\"tri_prix\">";
//                                    echo "<input type=\"hidden\" VALUE=\"tri_prix\" name=\"tri\" />";
//                                    echo "<th class=\"arrondi-c2\" scope=\"col\" width=\"11%\">
//                                            <a class=\"$class_tri_prix\" style=\"cursor:pointer;\" title=\"Trier par prix\" onclick=\"window.document.tri_prix.submit()\">Prix<br><img src=\"images/fleche_bas.gif\"></a></th>";
//                                    echo "</form>";
//                                    echo "<th class=\"arrondi-c3\" scope=\"col\" width=\"15%\">Paiement</th>";
//                                    echo "<th class=\"arrondi-c4\" scope=\"col\" width=\"15%\">Inscription</th>";
//                            echo "</tr>";
//                    echo "</thead>";
//                    echo "<tbody>";


                    echo "<div style=\"display:none\">";
                        echo "<form method=\"post\" action=\"$monUrl\" name=\"tri_date\">";
                        echo "<input type=\"hidden\" VALUE=\"tri_date\" name=\"tri\" />";
                        echo "</form>";
                        echo "<form method=\"post\" action=\"$monUrl\" name=\"tri_prix\">";
                        echo "<input type=\"hidden\" VALUE=\"tri_prix\" name=\"tri\" />";
                        echo "</form>";
                    echo "</div>";
            }

            $index_microdata = 0;
            $index_microdata_ancre = true;
            $nb_complet = 0;


        // TODO : exemple d'offre flash
/*
        echo '<tr id="_stage-83224-du-2014-04-17-au-2014-04-18" class="boost ville_" itemprop="event" itemscope="" itemtype="http://schema.org/Event"><td class="first" style="text-align:left;">
                <img src="template/images/decoupes/chrono_reel.png" class="chrono">
                <div style="float:left;"><span itemprop="location" itemscope="" itemtype="http://schema.org/EventVenue">
                                    <span itemprop="address" itemscope="" itemtype="http://schema.org/PostalAddress"><a href="recuperation-points-AUBAGNE-13400-13.html" title="stage permis conduire  AUBAGNE (13400)"><b><span itemprop="addressLocality">AUBAGNE</span> <span itemprop="postalCode">13400</span></b></a><br>      <span class="adr" itemprop="streetAddress">
                                            ZI des Paluds
                                        </span>
                                        <span style="display:none">
                                            <span itemprop="addressRegion">Bouches du Rhone 13</span>
                                            <span itemprop="name">AUBAGNE 13400</span>
                                        </span>
                                    </span>
                                </span></div><div style="text-align:right"><a href="#" title="Stage agréé par la préfecture: Bouches du Rhone 13" style="cursor: default"><img class="testdiv" style="cursor: default;width:15px;" src="images/icone_info2.png" onclick="TINY.box.show({iframe:\'infoagrement.php?id_stage=83224\', width:630, height:430, fixed:false, maskopacity:20})"></a></div></td><td width="21%"><meta itemprop="startDate" content="2014-04-17">Jeu 17 Avril <br>Ven 18 Avril </td><td itemprop="offers" width="11%" itemscope="" itemtype="http://schema.org/AggregateOffer"><span class="prix" itemprop="lowPrice">187 &euro;</span> <span class="old_prix">200 &euro;</span>
										<span style="display:none">
											<span itemprop="offerCount">
												20
											</span> places
										</span>
									</td>
                                    <td width="9%"><img align="center" width="30" src="template/images/icones_transaction/cb2.gif"></td>
                                    <td class="last">
                                        <span class="compteur" enddate="2014/04/17"><span class="glyphicon glyphicon-time"></span> Plus que<br />2h 00min 00s</span>
                                        <a href="http://www.prostagespermis.fr/stage-point-83224-44-AUBAGNE.html" itemprop="url" class="bt_reserver" title="stage permis de conduire  AUBAGNE (13400) 44"><span style="display:none" itemprop="name">STAGE à 187 &euro; sur AUBAGNE </span>RÉSERVER</a></td></tr>';
*/

            do
            {
				//S'il n'existe pas de stage sur une ville, on affiche de manière aléatoire
				//les stages du département sur cette ville pour éviter le duplicate.
				if ($random == true)
				{
            		if ($nb_stages_aleatoires <= 0)
            			break;

					if (rand(0,1) == 0)
					{
						$row_rsStage = mysql_fetch_assoc($rsStage);
            			continue;
            		}
            		else
            		{
            			$nb_stages_aleatoires --;
            		}
            	}

            	if (is_sniffer())
			    	$row_rsStage['prix'] = $row_rsStage['prix'] + rand(10,15);

                if ($row_rsStage['nb_places_allouees'] <= 0)
                    $nb_complet++;
                else
                    $nb_complet = 0;

                if ($nb_complet < 2) {

                    $tr_boost = '';
//                    $prix_non_boost = $row_rsStage['prix_boost_max'];
//                    $prix_non_boost = getPrixBarreStage($row_rsStage['prix']);
                    $prix_non_boost = $row_rsStage['prix_barre'];

                    $is_boost = $is_premium = $is_flash = 0;

                    if($row_rsStage['option_reversement'] == '1') {
                        if ($row_rsStage['option_visibilite'] == '2')
                            $is_flash = 1;
                        else
                            $is_premium = 1;
                    }
                    else if($row_rsStage['option_reversement'] == '2') {
                        $result_boost = verif_boost($row_rsStage['id'], $row_rsStage, true);
                        $is_boost = $result_boost['is_boost'];
                        $row_rsStage['option_reversement'] = $result_boost['option_reversement'];
                    }


//                    if (isset($_GET['a_saisir'])) {
//                    if (!empty($row_rsStage['boost_possible']) && $row_rsStage['boost_possible'] == 1 && $is_boost && ($row_rsStage['nb_boost_allouees'] + $row_rsStage['nb_boost']) <= 2) {
                    if ($row_rsStage['nb_places_allouees'] > 0) {
                        if ($is_boost || $is_flash) {
    //                        $prix_non_boost = getPrixBarreStage($row_rsStage['prix']);
    //                        $row_rsStage['prix'] = $row_rsStage['prix_boost'];
    //                        $is_boost = true;
                                $tr_boost = ' boost pas_cher_leger';
                        }
                        else if ($is_premium) {
                            $tr_boost = ' pas_cher_leger ';
                        }
    //                    else if($row_rsStage['prix'] <= $row_rsStage['prix_boost_max'] || $is_premium) {
    //                        $is_pas_cher = true;
    //
    //                            $tr_boost = ' pas_cher_leger ';
    //                    }
                    }

                    $dateLocal2 = datefr($row_rsStage['date1']);

                    //redirection automobile club
                    if ($row_rsStage['id_membre'] != 110)
                    {

                            $tabUrl = getUrl($site, $row_rsStage['departement'], $ville, $code_postal, $row_rsStage['ville'],
                                    $row_rsStage['code_postal'], $row_rsStage['motCle'], $row_rsStage['id'], $row_rsStage['id_membre'], $dateLocal2);
                    }
                    else
                    {
                            if ($site == psp)
                            {
                                    $tabUrl = getUrl($site, $row_rsStage['departement'], $ville, $code_postal, $row_rsStage['ville'],
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

                    $idStage = 'stage-' . $row_rsStage['id'] . '-du-' . $row_rsStage['date1'] . '-au-' . $row_rsStage['date2'];

                    if (in_array($departement, array(22, 29, 56, 03))) {

                        $idStage = 'stage-' . $row_rsStage['id'] . '-du-' . $row_rsStage['date1'] . '-au-' . $row_rsStage['date2'];
                        $alias_ville = strtoupper($row_rsStage['alias_groupe_ville']);
                        $urlVille = 'stage-de-recuperation-de-points-'.$alias_ville.'_'.$row_rsStage['id_groupe_ville'].'.html';
                        if (empty($ville))
                            $urlReservation = $urlVille . '#' . $idStage;
                    }

                    if ($site != psp)
                    {
                            // echo "<table width='100%' border='1'>";
                            // echo "<tr bgcolor=\""; echo switchcolor($site); echo "\">";
                            echo '<tr id="'.$idStage.'" class="'.switchClass($site). $tr_boost.' ville_'.$row_rsStage['id_groupe_ville'].'" itemprop="event" itemscope itemtype="http://schema.org/Event">';
                    }
                    else
                    {

                        echo '<tr id="_'.$idStage.'" class="'.$tr_boost.' ville_'.$row_rsStage['id_groupe_ville'].'" itemprop="event" itemscope itemtype="http://schema.org/Event">';
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

//                            if (isset($_GET['afficher_boost']))
//                                $text ="PLACES DISPO";
                    }

//                    if (isset($_GET['afficher_boost']) && $is_boost && !empty($row_rsStage['nb_boost_allouees'])) {
                    if ($is_boost && !empty($row_rsStage['nb_boost_allouees'])) {
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
                            echo "<td class=\"first\" style=\"text-align:left;\" width=\"42%\">";
                            echo "<div style=\"float:left;\">";

							//ajout_modif_privilege
							if (isset($_SESSION['privilege']) &&
								$row_rsStage['prix_privilege'] != NULL &&
								$row_rsStage['prix_privilege'] > 0 &&
								($row_rsStage['prix_privilege'] < $row_rsStage['prix'])
							)
							{
								echo '<img src="images/vip211.png" class="privilege" />';
							}

//                          if (isset($_GET['afficher_boost']) && $is_boost)
                            else if ($row_rsStage['nb_places_allouees'] > 0 && ($is_boost || $is_flash))
                                echo '<img src="template/images/decoupes/chrono_reel.png" class="chrono" />';

//                            else if ($is_pas_cher && $tri == 'tri_date')
//                            {
//                                if ($row_rsStage['nb_places_allouees'] > 0)
////                                    echo '<span class="pas_cher_saisir_bt">A&nbsp;&nbsp;saisir</span>';
//                                    echo '<span class="pas_cher_saisir_bt">
//                                                A  saisir
//                                                <span class="pointe">
//                                                    <span class="dot">.</span>
//                                                </span>
//                                            </span>';
//                                else
////                                    echo '<span class="pas_cher_epuise_bt">Épuisé</span>';
//                                    echo '<span class="pas_cher_epuise_bt">
//                                                Épuisé
//                                                <span class="pointe">
//                                                    <span class="dot">.</span>
//                                                </span>
//                                            </span>';
//                            }
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
                                </span></div>';


                            //if ($row_rsStage['id'] == 73541)
                            if (1) //if(0)
                            {
                            	$tmp_id_stage3 = $row_rsStage['id'];

                            	$title_agrement = "Stage agréé par la préfecture: ".getDepartement($departement);

                            	echo "<div style=\"text-align:right\">";
								echo "<a href=\"#\" title=\"$title_agrement\" style=\"cursor: default\"><img class=\"testdiv\" style=\"cursor: default;width:15px;\" src=\"images/icone_info2.png\"
								onclick=\"TINY.box.show({iframe:'infoagrement.php?id_stage=$tmp_id_stage3', width:630, height:430, fixed:false, maskopacity:20})\"></a>";
								echo "</div>";
                            }

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
                            echo "<td width='21%'>";
                            echo '<meta itemprop="startDate" content="' . $row_rsStage['date1'] . '" />';
                            echo MySQLDateToExplicitDate3($row_rsStage['date1'],1,0)."<br>".MySQLDateToExplicitDate3($row_rsStage['date2'],1,0);
                            echo "</td>";

							//ajout_modif_privilege
							if (isset($_SESSION['privilege']) &&
								$row_rsStage['prix_privilege'] != NULL &&
								$row_rsStage['prix_privilege'] > 0 &&
								($row_rsStage['prix_privilege'] < $row_rsStage['prix'])
							)
							{
								echo '<td itemprop="offers" width="11%" itemscope itemtype="http://schema.org/AggregateOffer">';

								echo '<span class="old_prix" itemprop="highPrice"><strike>'.$row_rsStage['prix'].'&#128;</strike><br></span>';

								echo '<span class="prix" itemprop="lowPrice" style="color:#BF9A22;text-shadow: 2px 2px 2px #99999;">'.$row_rsStage['prix_privilege'].' &#128;</span>
										<span style="display:none">
											<span itemprop="offerCount">
												'.$row_rsStage['nb_places_allouees'].'
											</span> places
										</span>
									</td>';
							}
							else
							{

								$prix = $row_rsStage['prix'];

								echo '<td itemprop="offers" width="11%" itemscope itemtype="http://schema.org/AggregateOffer">';

								echo '<span class="prix" itemprop="lowPrice">'.$prix.' &#128;</span>';

								if (($is_boost || $is_flash || $is_premium) && $prix_non_boost > $prix)
									echo ' <span class="old_prix" itemprop="highPrice">'.$prix_non_boost.' &#128;</span>';

								echo '  <span style="display:none">
											<span itemprop="offerCount">
												'.$row_rsStage['nb_places_allouees'].'
											</span> places
										</span>
									</td>';
							}

                            $pay = explode(",", $row_rsStage['types_paiement']);

                            if ($pay[0] == "on")
                            {
                                if ($is_boost)
                                    echo "<td width='8%'><img align=\"center\" width=\"31\" src=\"template/images/icones_transaction/cb2.gif\"/></td>";
                                else
                                    echo "<td width='8%'><img align=\"center\" width=\"31\" src=\"template/images/icones_transaction/cb2.gif\"/><img align=\"center\" style=\"opacity:0.8\" width=\"31\" src=\"template/images/icones_transaction/cheque.gif\"/></td>";
                            }
                            else
                            {
                                    echo "<td width='8%'><img align=\"center\" style=\"opacity:0.8\" width=\"31\" src=\"template/images/icones_transaction/cheque.gif\"/></td>";
                            }


//                            $texte .= ($pay[0] == "on") ? "<img src='images/icones_transaction/cb.gif'/>&nbsp;&nbsp;&nbsp;" : "";
//                            $texte .= ($pay[1] == "on") ? "<img src='images/icones_transaction/cheque.gif'/>&nbsp;&nbsp;&nbsp;" : "";

                            $strPad = ' .';
                            $len_prix = 11;
                            $len_ville = 25;

                            echo "<td class='last'>";
//                            $prefix_ancre = 'STAGE . à . . '. str_pad($row_rsStage['prix'] . ' &#128;', $len_prix, $strPad, STR_PAD_RIGHT) . ' . . sur . ' . strtoupper($row_rsStage['ville']) . (empty($index_microdata)? ($index_microdata_ancre? ' - Profitez-en !' : " - Plus d'infos") : '');
//                            $prefix_ancre = 'STAGE . à . . '. str_pad($row_rsStage['prix'] . ' &#128;', $len_prix, $strPad, STR_PAD_RIGHT) . ' . . sur . ' . strtoupper($row_rsStage['ville']);

                            $prefix_ancre = 'STAGE à '. $row_rsStage['prix'] . ' &#128; sur ' . strtoupper($row_rsStage['ville']);

							//Fiches villes dans entenoir de convertion
							//-----------------------------------------
							 if (in_array($departement, array(35,14,79,85,57,63,87,21,72,80,76,49,42,66,59,33,31,6,34,30,67,51,62,50,32,82,1,4,5,7,8,9,2,10,11,12,15,16,17,18,19,20,23,24,25,26,27)) && empty($ville))
							 {
							 	$urlReservation = $urlVille.'#'.$idStage;
							 }


//                            if (isset($_GET['afficher_boost'])) {
                                if ($row_rsStage['nb_places_allouees'] > 0) {

                                    if ($is_boost || $is_flash) {
                                        echo '<span class="compteur"><span class="glyphicon glyphicon-time"></span> Promotion terminée</span>';
                                    }

                                    echo "<a href=\"$urlReservation\" itemprop='url' class=\"bt_reserver\" title=\"$titleReservation\"><span style='display:none' itemprop='name'>$prefix_ancre </span>RÉSERVER</a>";
                                    if ($text != "")
                                        echo "<span style=\"color:$col\" class=\"places\">$text</span>";
                                }
                                else {
                                        echo "<span class=\"bt_reserver bt_complet\" title=\"$titleReservation\"><span style='display:none' itemprop='name'>$prefix_ancre - </span>COMPLET</span>";
                                }
//                            }
//                            else {
//                                echo "<a href=\"$urlReservation\" itemprop='url' title=\"$titleReservation\"><img border=\"0\" src=\"template/images/decoupes/reserver.png\" align=\"center\"/><span style='display:none' itemprop='name'>$prefix_ancre</span></a>";
//                                if ($text != "")
//                                    echo "<br><font color=$col>$text</font>";
//                            }

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
                            echo "<span class='prix'><b>".$prix." ?</b></span>";
                            echo "</td>";

                            $pay = explode(",", $row_rsStage['types_paiement']);

                            if ($pay[0] == "on")
                            {
                                    echo "<td><b>CB / Chèque</b><br/><img align=\"center\" src=\"Templates/sources/images/cb.png\"/></td>";
                            }
                            else
                            {
                                    echo "<td><b>Chèque</b><br/><img align=\"center\" style=\"opacity:0.8\" src=\"Templates/sources/images/cheque1.jpg\"/></td>";
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

            }
            while ($row_rsStage = mysql_fetch_assoc($rsStage));

            if (true || $site == psp)
            {
                    echo "</tbody>";
                    echo "</table>";
            }

	}
	else {
//            if (!$chemin) {
                if ($ville != NULL)
                {
                        $dest = getUrlDepartement($departement);
                        echo "<br>";
                        echo "<font color='red'>Plus de stages disponibles sur $ville</font>";
                        echo "<br><br>";
                        echo "Cliquez sur le lien suivant pour accéder aux stages permis à points près de $ville:";
                        echo "<br>";
                        echo "<a href='recuperer-points-$dest.html'>LISTE DES STAGES A PROXIMITE</a>";
                }
                else if ($departement != NULL)
                {
                        $nom_dep = getDepartement($departement);
                        $dest = getUrlDepartement($departement);
                        echo "<br>";
                        echo "<font color='red'>Plus de stages pas cher disponibles sur $nom_dep</font>";
                        echo "<br><br>";
                        echo "Cliquez sur le lien suivant pour accéder aux stages permis à points pas cher près de $nom_dep:";
                        echo "<br>";
                        echo "<a href='recuperer-points-$dest.html'>LISTE DES STAGES A PROXIMITE</a>";
                }
                else if ($region != NULL)
                {
                        $dest = getUrlRegion($region);
                        $nom_region = getRegion($region);
                        echo "<br>";
                        echo "<font color='red'>Plus de stages disponibles sur $nom_region</font>";
                        echo "<br><br>";
                        echo "Cliquez sur le lien suivant pour accéder aux stages permis à points près de $nom_region:";
                        echo "<br>";
                        echo "<a href='recuperer-points-$dest.html'>LISTE DES STAGES A PROXIMITE</a>";
                }
                else
                {
                        echo "<br>";
                        echo "<font color='red'>Plus de stages disponibles</font>";
                        echo "<br>";
                }

//            }
//            else if ($chemin == CHEMIN_PAS_CHER) {
//                if ($ville != NULL)
//                {
//                        $dest = getUrlDepartement($departement);
//                        echo "<br>";
//                        echo "<font color='red'>Plus de stages pas cher disponibles sur $ville</font>";
//                        echo "<br><br>";
//                        echo "Cliquez sur le lien suivant pour accéder aux stages permis à points pas cher près de $ville:";
//                        echo "<br>";
//                        echo "<a href='recuperer-points-$dest.html'>LISTE DES STAGES MOINS CHER A PROXIMITE</a>";
//                }
//                else if ($departement != NULL)
//                {
//                        $nom_dep = getDepartement($departement);
//                        $dest = getUrlDepartement($departement);
//                        echo "<br>";
//                        echo "<font color='red'>Plus de stages pas cher disponibles sur $nom_dep</font>";
//                        echo "<br><br>";
//                        echo "Cliquez sur le lien suivant pour accéder aux stages permis à points pas cher près de $nom_dep:";
//                        echo "<br>";
//                        echo "<a href='recuperer-points-$dest.html'>LISTE DES STAGES MOINS CHER A PROXIMITE</a>";
//                }
//                else if ($region != NULL)
//                {
//                        $dest = getUrlRegion($region);
//                        $nom_region = getRegion($region);
//                        echo "<br>";
//                        echo "<font color='red'>Plus de stages pas cher disponibles sur $nom_region</font>";
//                        echo "<br><br>";
//                        echo "Cliquez sur le lien suivant pour accéder aux stages permis à points pas cher près de $nom_region:";
//                        echo "<br>";
//                        echo "<a href='recuperer-points-$dest.html'>LISTE DES STAGES MOINS CHER A PROXIMITE</a>";
//                }
//                else
//                {
//                        echo "<br>";
//                        echo "<font color='red'>Plus de stages pas cher disponibles</font>";
//                        echo "<br>";
//                }
//            }
        }

        /*
        if ($total_dynamic == 1 && $ville != NULL && $site == psp)
        {
        	echo "<h2>".$row_dynamic['h2']."</h2>";
        	echo $row_dynamic['contenu'];
        }*/

        return $totalRows_rsStage;
}

function affiche_stages($site, $departement, $ville, $code_postal, $region, $chemin, $query_site, $query_stage, $iframe = false)
{
	//random du département si pas de stages dans une ville
	//-----------------------------------------------------
	$random = false;
	if ($site == "psp_no_stage_ville")
	{
		$nb_stages_aleatoires = 5;
		$site = psp;
		$random = true;
	}

	require_once ("../common/functions.php");
	require ("../connections/stageconnect.php");

	mysql_select_db($database_stageconnect, $stageconnect);

	$rsSite = mysql_query($query_site, $stageconnect) or die(mysql_error());
	$row_rsSite	= mysql_fetch_assoc($rsSite);
	$totalRows_rsSite = mysql_num_rows($rsSite);

	$rsStage = mysql_query($query_stage, $stageconnect) or die(mysql_error());
	$row_rsStage = mysql_fetch_assoc($rsStage);
	$totalRows_rsStage = mysql_num_rows($rsStage);

	/*
	//selection du contenu dynamique
	//-----------------------------
	$sql = "SELECT h2, contenu, code_postal FROM villes_dynamic_content WHERE code_postal = \"$code_postal\"";
	$rs_dynamic = mysql_query($sql, $stageconnect);
	$row_dynamic = mysql_fetch_assoc($rs_dynamic);
	$total_dynamic = mysql_num_rows($rs_dynamic);
	*/

	mysql_close($stageconnect);

	if ($totalRows_rsStage == 0)
		return -1;

    if (empty($iframe)) {
        if (!$chemin) {
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
            else if ($region != NULL)
            {

                    echo "<h2>";
                    echo "Récupération de points de permis ".getRegion($region).":";
                    echo "</h2>";
            }
            else
            {

                    echo "<h2>";
                    echo "Récupération de points de permis sur toute la france :";
                    echo "</h2>";
            }
        }
        else if ($chemin == CHEMIN_PAS_CHER) {
            if ($ville != NULL)
            {
                    echo "<h2>";
                    echo "Prix stage recuperation de points $ville<br />Stage permis à points pas cher $ville :";
                    echo "</h2>";
            }
            else if ($departement != NULL)
            {

                    echo "<h2>";
                    echo "Prix stage recuperation de points ".getDepartement($departement)."<br />Stage permis à points pas cher ".getDepartement($departement)." :";
                    echo "</h2>";
            }
            else if ($region != NULL)
            {

                    echo "<h2>";
                    echo "Prix stage recuperation de points ".getRegion($region)."<br />Stage permis à points pas cher ".getRegion($region)." :";
                    echo "</h2>";
            }
            else
            {

                    echo "<h2>";
                    echo "Prix stage recuperation de points sur toute la France<br />Stage permis à points pas cher : ";
                    echo "</h2>";
            }
        }
    }

    $tri = 'tri_date';
    if (!empty($_POST['tri']) && $_POST['tri'] == "tri_prix")
        $tri = 'tri_prix';


	//affichage menu déroulant
	//------------------------
    /*
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


				if (($i % 4) == 0)
				{
					echo "<tr height='40px'>";
				}

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

	else */if ($totalRows_rsSite > 0 && $site != psp)
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
		echo "<a href='http://www.prostagespermis.fr/$url_departement'><strong><< AFFICHER TOUS LES STAGES PERMIS DU DEPARTEMENT ".getDepartement($departement)."</strong></a><br><br>";
	}



	if ($totalRows_rsStage > 0)
	{

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

                    echo '<table id="tableau-stages" cellspacing="0"';

                    if ($chemin == CHEMIN_PAS_CHER)
                        echo ' class="boost" ';

                    echo '>';

                    $class_tri_prix = '';
                    $class_tri_date = 'tri_selected';

                    if ($tri == 'tri_prix') {
                            $class_tri_prix = 'tri_selected';
                            $class_tri_date = '';
                    }

                    echo "<thead>";
                            echo "<tr>";
                                    echo "<th class=\"titre-arrondi\" scope=\"col\" width=\"42%\">Villes</th>";
                                    echo "<form method=\"post\" action=\"$monUrl\" name=\"tri_date\">";
                                    echo "<input type=\"hidden\" VALUE=\"tri_date\" name=\"tri\" />";
                                    echo "<th class=\"arrondi-c1\" scope=\"col\" width=\"18%\">
                                            <a class=\"$class_tri_date\" style=\"cursor:pointer;\" title=\"Trier par dates\" onclick=\"window.document.tri_date.submit()\">Dates<br><img src=\"images/fleche_bas.gif\"></a></th>";
                                    echo "</form>";
                                    echo "<form method=\"post\" action=\"$monUrl\" name=\"tri_prix\">";
                                    echo "<input type=\"hidden\" VALUE=\"tri_prix\" name=\"tri\" />";
                                    echo "<th class=\"arrondi-c2\" scope=\"col\" width=\"11%\">
                                            <a class=\"$class_tri_prix\" style=\"cursor:pointer;\" title=\"Trier par prix\" onclick=\"window.document.tri_prix.submit()\">Prix<br><img src=\"images/fleche_bas.gif\"></a></th>";
                                    echo "</form>";
                                    echo "<th class=\"arrondi-c3\" scope=\"col\" width=\"15%\">Paiement</th>";
                                    echo "<th class=\"arrondi-c4\" scope=\"col\" width=\"15%\">Inscription</th>";
                            echo "</tr>";
                    echo "</thead>";
                    echo "<tbody>";
            }

            $index_microdata = 0;
            $index_microdata_ancre = true;
            $nb_complet = 0;

            do
            {
				//S'il n'existe pas de stage sur une ville, on affiche de manière aléatoire
				//les stages du département sur cette ville pour éviter le duplicate.
				if ($random == true)
				{
            		if ($nb_stages_aleatoires <= 0)
            			break;

					if (rand(0,1) == 0)
					{
						$row_rsStage = mysql_fetch_assoc($rsStage);
            			continue;
            		}
            		else
            		{
            			$nb_stages_aleatoires --;
            		}
            	}

 				if (is_sniffer())
			    	$row_rsStage['prix'] = $row_rsStage['prix'] + rand(10,15);
			    	

//                echo '<div style="display:none">';
//                echo 'nb_boost_allouees : ';
//                var_dump($row_rsStage['nb_boost_allouees']);
//                echo ' nb_boost : ';
//                var_dump($row_rsStage['nb_boost']);
//                echo '</div>';

                if ($row_rsStage['nb_places_allouees'] <= 0)
                    $nb_complet++;
                else
                    $nb_complet = 0;

                if ($nb_complet < 2) {

                    $is_boost = false;
                    $is_pas_cher = false;
                    $tr_boost = '';
                    $prix_non_boost = '';

//                    if (isset($_GET['a_saisir'])) {
                    if (!empty($row_rsStage['boost_possible']) && $row_rsStage['boost_possible'] == 1 && !empty($row_rsStage['boost_actif']) && !empty($row_rsStage['prix_boost']) && ($row_rsStage['nb_boost_allouees'] + $row_rsStage['nb_boost']) <= 2) {
                        $prix_non_boost = $row_rsStage['prix'];
                        $row_rsStage['prix'] = $row_rsStage['prix_boost'];
                        $is_boost = true;
                        if ($row_rsStage['nb_places_allouees'] > 0)
                            $tr_boost = ' boost ';
                        else
                            $tr_boost = ' boost complet ';
                    }
                    else if($row_rsStage['prix'] <= $row_rsStage['prix_boost_max']) {
                        $is_pas_cher = true;

//                        if ($tri == 'tri_date')
//                            $tr_boost = ' pas_cher ';
//                        else
                            $tr_boost = ' pas_cher_leger ';
                    }
//                    }

                    $dateLocal2 = datefr($row_rsStage['date1']);

                    //redirection automobile club
                    if ($row_rsStage['id_membre'] != 110)
                    {

//                        if ($chemin == CHEMIN_PAS_CHER)
//                            $tabUrl = getUrlPasCher($site, $row_rsStage['departement'], $ville, $code_postal, $row_rsStage['ville'],
//                                    $row_rsStage['code_postal'], $row_rsStage['motCle'], $row_rsStage['id'], $row_rsStage['id_membre'], $dateLocal2);
//                        else
                            $tabUrl = getUrl($site, $row_rsStage['departement'], $ville, $code_postal, $row_rsStage['ville'],
                                    $row_rsStage['code_postal'], $row_rsStage['motCle'], $row_rsStage['id'], $row_rsStage['id_membre'], $dateLocal2);
                    }
                    else
                    {
                            if ($site == psp)
                            {
//                                if ($chemin == CHEMIN_PAS_CHER)
//                                    $tabUrl = getUrlPasCher($site, $row_rsStage['departement'], $ville, $code_postal, $row_rsStage['ville'],
//                                            $row_rsStage['code_postal'], $row_rsStage['motCle'], $row_rsStage['id'], $row_rsStage['id_membre'], $dateLocal2);
//                                else
                                    $tabUrl = getUrl($site, $row_rsStage['departement'], $ville, $code_postal, $row_rsStage['ville'],
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

                    $idStage = 'stage-' . $row_rsStage['id'] . '-du-' . $row_rsStage['date1'] . '-au-' . $row_rsStage['date2'];

                    if (in_array($departement, array(22, 29, 56, 03))) {

                        $idStage = 'stage-' . $row_rsStage['id'] . '-du-' . $row_rsStage['date1'] . '-au-' . $row_rsStage['date2'];
                        $alias_ville = strtoupper($row_rsStage['alias_groupe_ville']);
                        $urlVille = 'stage-de-recuperation-de-points-'.$alias_ville.'_'.$row_rsStage['id_groupe_ville'].'.html';
                        if (empty($ville))
                            $urlReservation = $urlVille . '#' . $idStage;
                    }

                    if ($site != psp)
                    {
                            // echo "<table width='100%' border='1'>";
                            // echo "<tr bgcolor=\""; echo switchcolor($site); echo "\">";
                            echo '<tr id="'.$idStage.'" class="'.switchClass($site). $tr_boost.' ville_'.$row_rsStage['id_groupe_ville'].'" itemprop="event" itemscope itemtype="http://schema.org/Event">';
                    }
                    else
                    {

                        echo '<tr id="_'.$idStage.'" class="'.$tr_boost.' ville_'.$row_rsStage['id_groupe_ville'].'" itemprop="event" itemscope itemtype="http://schema.org/Event">';
                    }

                    $places = $row_rsStage['nb_places_allouees'] - ($row_rsStage['nb_inscrits'] + $row_rsStage['nb_preinscrits']);
                    if ($row_rsStage['nb_places_allouees'] < 5)
                    {
                            $text = "- DE 5 PLACES";
                            $col = "red";
                    }
                    else
                    {
//                            $text ="";
                            $col = "green";

//                            if (isset($_GET['afficher_boost']))
                                $text ="PLACES DISPO";
                    }

//                    if (isset($_GET['afficher_boost']) && $is_boost && !empty($row_rsStage['nb_boost_allouees'])) {
                    if ($is_boost && !empty($row_rsStage['nb_boost_allouees'])) {
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
                            echo "<td style=\"text-align:left;padding-left:20px\">";
                            echo "<div style=\"float:left;\">";

							//ajout_modif_privilege
							if (isset($_SESSION['privilege']) &&
								$row_rsStage['prix_privilege'] != NULL &&
								$row_rsStage['prix_privilege'] > 0 &&
								($row_rsStage['prix_privilege'] < $row_rsStage['prix'])
							)
							{
								echo '<img src="images/vip211.png" class="privilege" />';
							}

//                          if (isset($_GET['afficher_boost']) && $is_boost)
                            else if ($is_boost)
                                echo '<img src="template/images/decoupes/chrono_reel.png" class="chrono" />';

                            else if ($is_pas_cher && $tri == 'tri_date')
                            {
                                if ($row_rsStage['nb_places_allouees'] > 0)
//                                    echo '<span class="pas_cher_saisir_bt">A&nbsp;&nbsp;saisir</span>';
                                    echo '<span class="pas_cher_saisir_bt">
                                                A  saisir
                                                <span class="pointe">
                                                    <span class="dot">.</span>
                                                </span>
                                            </span>';
                                else
//                                    echo '<span class="pas_cher_epuise_bt">Épuisé</span>';
                                    echo '<span class="pas_cher_epuise_bt">
                                                Épuisé
                                                <span class="pointe">
                                                    <span class="dot">.</span>
                                                </span>
                                            </span>';
                            }
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
                                </span></div>';


                            //if ($row_rsStage['id'] == 73541)
                            if (1) //if(0)
                            {
                            	$tmp_id_stage3 = $row_rsStage['id'];

                            	$title_agrement = "Stage agréé par la préfecture: ".getDepartement($departement);

                            	echo "<div style=\"text-align:right\">";
								echo "<a href=\"#\" title=\"$title_agrement\" style=\"cursor: default\"><img class=\"testdiv\" style=\"cursor: default\" src=\"images/icone_info2.png\"
								onclick=\"TINY.box.show({iframe:'infoagrement.php?id_stage=$tmp_id_stage3', width:630, height:430, fixed:false, maskopacity:20})\"></a>";
								echo "</div>";
                            }

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
                            echo MySQLDateToExplicitDate3($row_rsStage['date1'],1,0)."<br>".MySQLDateToExplicitDate3($row_rsStage['date2'],1,0);
                            echo "</td>";

							//ajout_modif_privilege
							if (isset($_SESSION['privilege']) &&
								$row_rsStage['prix_privilege'] != NULL &&
								$row_rsStage['prix_privilege'] > 0 &&
								($row_rsStage['prix_privilege'] < $row_rsStage['prix'])
							)
							{
								echo '<td itemprop="offers" itemscope itemtype="http://schema.org/AggregateOffer">';

								echo '<span class="old_prix" itemprop="highPrice"><strike>'.$row_rsStage['prix'].'&#128;</strike><br></span>';

								echo '<span class="prix" itemprop="lowPrice" style="color:#BF9A22;text-shadow: 2px 2px 2px #99999;"><b>'.$row_rsStage['prix_privilege'].' &#128;</b></span>
										<span style="display:none">
											<span itemprop="offerCount">
												'.$row_rsStage['nb_places_allouees'].'
											</span> places
										</span>
									</td>';
							}
							else
							{

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
							}

                            $pay = explode(",", $row_rsStage['types_paiement']);

                            if ($pay[0] == "on")
                            {
                                    echo "<td><b>CB / Chèque</b><br/><img align=\"center\" src=\"template/images/decoupes/cb.png\"/></td>";
                            }
                            else
                            {
                                    echo "<td><b>Chèque</b><br/><img align=\"center\" src=\"template/images/decoupes/cheque1.jpg\"/></td>";
                            }


//                            $texte .= ($pay[0] == "on") ? "<img src='images/icones_transaction/cb.gif'/>&nbsp;&nbsp;&nbsp;" : "";
//                            $texte .= ($pay[1] == "on") ? "<img src='images/icones_transaction/cheque.gif'/>&nbsp;&nbsp;&nbsp;" : "";

                            $strPad = ' .';
                            $len_prix = 11;
                            $len_ville = 25;

                            echo "<td>";
//                            $prefix_ancre = 'STAGE . à . . '. str_pad($row_rsStage['prix'] . ' &#128;', $len_prix, $strPad, STR_PAD_RIGHT) . ' . . sur . ' . strtoupper($row_rsStage['ville']) . (empty($index_microdata)? ($index_microdata_ancre? ' - Profitez-en !' : " - Plus d'infos") : '');
//                            $prefix_ancre = 'STAGE . à . . '. str_pad($row_rsStage['prix'] . ' &#128;', $len_prix, $strPad, STR_PAD_RIGHT) . ' . . sur . ' . strtoupper($row_rsStage['ville']);

                            $prefix_ancre = 'STAGE à '. $row_rsStage['prix'] . ' &#128; sur ' . strtoupper($row_rsStage['ville']);

							//Fiches villes dans entenoir de convertion
							//-----------------------------------------
							 if (in_array($departement, array(35,14,79,85,57,63,87,21,72,80,76,49,42,66,59,33,31,6,34,30,67,51,62,50,32,82,1,4,5,7,8,9,2,10,11,12,15,16,17,18,19,20,23,24,25,26,27)) && empty($ville))
							 {
							 	$urlReservation = $urlVille.'#'.$idStage;
							 }

//                            if (isset($_GET['afficher_boost'])) {
                                if ($row_rsStage['nb_places_allouees'] > 0) {
                                    echo "<a href=\"$urlReservation\" itemprop='url' class=\"bt_reserver\" title=\"$titleReservation\"><span style='display:none' itemprop='name'>$prefix_ancre </span>RÉSERVER</a>";
                                    if ($text != "")
                                        echo "<span style=\"color:$col\" class=\"places\">$text</span>";
                                }
                                else {
                                        echo "<span class=\"bt_reserver bt_complet\" title=\"$titleReservation\"><span style='display:none' itemprop='name'>$prefix_ancre - </span>COMPLET</span>";
                                }
//                            }
//                            else {
//                                echo "<a href=\"$urlReservation\" itemprop='url' title=\"$titleReservation\"><img border=\"0\" src=\"template/images/decoupes/reserver.png\" align=\"center\"/><span style='display:none' itemprop='name'>$prefix_ancre</span></a>";
//                                if ($text != "")
//                                    echo "<br><font color=$col>$text</font>";
//                            }

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
                            echo "<span class='prix'><b>".$prix." ?</b></span>";
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

            }
            while ($row_rsStage = mysql_fetch_assoc($rsStage));

            if (true || $site == psp)
            {
                    echo "</tbody>";
                    echo "</table>";
            }

	}
	else {
            if (!$chemin) {
                if ($ville != NULL)
                {
                        $dest = getUrlDepartement($departement);
                        echo "<br>";
                        echo "<font color='red'>Plus de stages disponibles sur $ville</font>";
                        echo "<br><br>";
                        echo "Cliquez sur le lien suivant pour accéder aux stages permis à points près de $ville:";
                        echo "<br>";
                        echo "<a href='recuperer-points-$dest.html'>LISTE DES STAGES A PROXIMITE</a>";
                }
                else if ($departement != NULL)
                {
                        $nom_dep = getDepartement($departement);
                        $dest = getUrlDepartement($departement);
                        echo "<br>";
                        echo "<font color='red'>Plus de stages pas cher disponibles sur $nom_dep</font>";
                        echo "<br><br>";
                        echo "Cliquez sur le lien suivant pour accéder aux stages permis à points pas cher près de $nom_dep:";
                        echo "<br>";
                        echo "<a href='recuperer-points-$dest.html'>LISTE DES STAGES A PROXIMITE</a>";
                }
                else if ($region != NULL)
                {
                        $dest = getUrlRegion($region);
                        $nom_region = getRegion($region);
                        echo "<br>";
                        echo "<font color='red'>Plus de stages disponibles sur $nom_region</font>";
                        echo "<br><br>";
                        echo "Cliquez sur le lien suivant pour accéder aux stages permis à points près de $nom_region:";
                        echo "<br>";
                        echo "<a href='recuperer-points-$dest.html'>LISTE DES STAGES A PROXIMITE</a>";
                }
                else
                {
                        echo "<br>";
                        echo "<font color='red'>Plus de stages disponibles</font>";
                        echo "<br>";
                }

            }
            else if ($chemin == CHEMIN_PAS_CHER) {
                if ($ville != NULL)
                {
                        $dest = getUrlDepartement($departement);
                        echo "<br>";
                        echo "<font color='red'>Plus de stages pas cher disponibles sur $ville</font>";
                        echo "<br><br>";
                        echo "Cliquez sur le lien suivant pour accéder aux stages permis à points pas cher près de $ville:";
                        echo "<br>";
                        echo "<a href='recuperer-points-$dest.html'>LISTE DES STAGES MOINS CHER A PROXIMITE</a>";
                }
                else if ($departement != NULL)
                {
                        $nom_dep = getDepartement($departement);
                        $dest = getUrlDepartement($departement);
                        echo "<br>";
                        echo "<font color='red'>Plus de stages pas cher disponibles sur $nom_dep</font>";
                        echo "<br><br>";
                        echo "Cliquez sur le lien suivant pour accéder aux stages permis à points pas cher près de $nom_dep:";
                        echo "<br>";
                        echo "<a href='recuperer-points-$dest.html'>LISTE DES STAGES MOINS CHER A PROXIMITE</a>";
                }
                else if ($region != NULL)
                {
                        $dest = getUrlRegion($region);
                        $nom_region = getRegion($region);
                        echo "<br>";
                        echo "<font color='red'>Plus de stages pas cher disponibles sur $nom_region</font>";
                        echo "<br><br>";
                        echo "Cliquez sur le lien suivant pour accéder aux stages permis à points pas cher près de $nom_region:";
                        echo "<br>";
                        echo "<a href='recuperer-points-$dest.html'>LISTE DES STAGES MOINS CHER A PROXIMITE</a>";
                }
                else
                {
                        echo "<br>";
                        echo "<font color='red'>Plus de stages pas cher disponibles</font>";
                        echo "<br>";
                }
            }
        }

		/*
        if ($total_dynamic == 1 && $ville != NULL && $site == psp)
        {
        	echo "<h2>".$row_dynamic['h2']."</h2>";
        	echo $row_dynamic['contenu'];
        }
        */

        return $totalRows_rsStage;
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

function getTitleMotclePasCher($texte)
{
		switch ($texte)
		{
			case 1:
				$texte1 = "stage permis a points pas cher";
				$texte2 = "stage permis points pas cher";
			break;

			case 5:
				$texte1 = "stage permis de conduire pas cher";
				$texte2 = "stage permis conduire pas cher";
			break;

			case 2:
				$texte1 = "stage de recuperation de points pas cher";
				$texte2 = "stage recup point pas cher";
			break;

			case 6:
				$texte1 = "permis a point pas cher";
				$texte2 = "points permis pas cher";
			break;

			case 3:
				$texte1 = "stage de rattrapage de points pas cher";
				$texte2 = "rattraper points permis pas cher";
			break;

			case 4:
				$texte1 = "stage de sensibilisation à la sécurité routière pas cher";
				$texte2 = "stage sensibilisation securite routiere pas cher";
			break;

			default:
				$texte1 = "stage permis a points pas cher";
				$texte2 = "stage permis points pas cher";
			break;
		}
		return array($texte1, $texte2);
}

function getUrlPasCher($site, $departement, $get_ville, $get_code_postal, $ville,
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
		case psp: //psp
			//url du departement
			$departement = sprintf("%02d",$departement);
			$dest = getUrlDepartement($departement);
			$urlDepartement = "recuperation-de-points-pas-cher-$dest.html";

			//options de choix dans liste deroulante
			$code_postal = sprintf("%05d",$code_postal);
			$urlVille = "stage-de-recuperation-de-points-pas-cher-$ville-$code_postal-$departement.html";
			$option = "<a href=$urlVille>";
			$option = $option."Stage recuperation de points pas cher <br>".$ville." (".$code_postal.")";
			$option = $option."</a>";

			//balises titles
			$title = getTitleMotclePasCher($motCle);
			$title1 = $title[0];
			$title2 = $title[1];

			//url de destination sur reservation du stage
			$urlReservation = "http://www.prostagespermis.fr/stage-point-$id_stage-$id_membre-$ville.html";
			$titleReservation = $title1;
			$texteReservation = "<strong>Stage recuperation de points pas cher <br>$ville le $tewt_date</strong>";

			//balise title pour la ville
			$titleVille = $title2;
		break;
	}

	return array($option, $title1, $title2, $urlDepartement, $urlVille,
		$urlReservation, $titleReservation, $texteReservation, $titleVille);
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
			$urlReservation = "http://www.prostagespermis.fr/stage-point-$id_stage-$id_membre-$ville.html";
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

function is_sniffer()
{
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	//$http_referer = $_SERVER['HTTP_REFERER'];
	$ip = $_SERVER['REMOTE_ADDR'];
	$ip_pap = "212.198.238.48";

	$pos1 = stripos($user_agent, 'docs.google.com');
	$pos2 = stripos($user_agent, 'YahooCacheSystem');
	$pos3 = stripos($user_agent, 'Google-Apps-Script');
	$pos4 = stripos($user_agent, 'Exabot');
	$pos5 = stripos($user_agent, 'rogerbot');
	$pos6 = stripos($user_agent, 'Ahrefs');
	$pos7 = stripos($user_agent, 'Anonymouse.org');
	
	//$pos5 = stripos($http_referer, 'app_dev.php');

	if ($ip != $ip_pap && $pos1 === false && $pos2 === false && $pos3 === false && $pos4 === false && $pos5 === false && $pos6 === false && $pos7 === false)
		return 0;
	else
		return 1;
}
?>