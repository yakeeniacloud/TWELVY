<?php


// TODO : REFACTORISER TOUT LE CODE DES CB
// FONCTION NON UTILISEE POUR L'INSTANT
function update_places_after_inscription($id_stage, $is_supprime) {
    
	@include_once ("../common/common_boost_config.php");
	@include_once ("../../common/common_boost_config.php");
	@include ("../connections/stageconnect.php");
	@include ("../../connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);
    
    $boost_result = verif_boost($id_stage);
    
    //update stage
    $query_stage = "SELECT stage.nb_inscrits, stage.nb_boost, stage.nb_boost_allouees, stage.boost_actif, stage.date1, site.ville FROM stage, site WHERE stage.id = $id_stage AND stage.id_site = site.id";
    $rsStage = mysql_query($query_stage, $stageconnect);// or die(mysql_error());
    $row_stage = mysql_fetch_assoc($rsStage);
    $totalRows_stage = mysql_num_rows($rsStage);

    $nb_places_allouees = $row_stage['nb_places_allouees'];
    $nb_boost = $row_stage['nb_boost'];
    $nb_boost_allouees = $row_stage['nb_boost_allouees'];
    $boost_actif = 0;

    if (!empty($row_stage['boost_actif'])) {
        $boost_actif = 1;
        $nb_boost++;
        $nb_boost_allouees--;
    }
    if ($nb_boost_allouees < 1)
        $boost_actif = 0;

		$sql = "UPDATE stage SET taux_remplissage = taux_remplissage +1, nb_inscrits = nb_inscrits +1, boost_actif = $boost_actif, nb_boost = $nb_boost, nb_boost_allouees = $nb_boost_allouees, nb_places_allouees = nb_places_allouees -1 WHERE id = $stageId";
        $sql = "UPDATE stage SET taux_remplissage = taux_remplissage +1, nb_inscrits = nb_inscrits +1, nb_preinscrits = $nb_preinscrits, nb_places_allouees = $nb_places_allouees $sql_boost WHERE id = $stageId AND stage.id_membre = $id_membre";
		mysql_query($sql, $stageconnect);// or die('Erreur SQL !'.$sql.'<br />'.mysql_error());

}



function fiche_nouveau($tab, $site = 1)
{

    $_SESSION['post'] = $tab;

	$dateLocal = date("y-m-d");

	//anti casse pieds:
//	if (isset($tab['email2']) && !empty($tab['email2']))
//	{
//		echo "Problème technique (email2). Merci de contacter un conseiller";
//		exit;
//	}

	$temps = time();
	if (isset($tab['verifTemps']) && ($temps - $tab['verifTemps']) < 3)
	{
		echo "Problème technique (temps). Merci de contacter un conseiller";
		exit;
	}
	//fin anti casse pieds

	if (!isset($tab['stageId']) 	||
		!isset($tab['membreId']) 	||
		!isset($tab['nom']) 		||
		!isset($tab['prenom']) 	||
		!isset($tab['adresse']) 	||
		!isset($tab['code_postal']) ||
		!isset($tab['ville']) 	||
		!isset($tab['lieu_naissance']) ||
		!isset($tab['email']) 	||
		!isset($tab['num_permis']))
	{
		echo "Erreur sur la page, revenez au menu principal";
		exit;
	}

    $test_mode = 0;

	$stageId_externe = $tab['stageId_externe'];
	$stageId = $tab['stageId'];
	$membreId = $tab['membreId'];
	$nom = $tab['nom'];
	$jeune_fille = $tab['jeune_fille'];
	$nom = strtoupper($nom);
	$prenom = $tab['prenom'];
	$prenom = strtolower($prenom);
	$date_naissance = $tab['annee_naissance']."-".$tab['mois_naissance']."-".$tab['jour_naissance'];
	$lieu_naissance = $tab['lieu_naissance'];
	$lieu_naissance = strtoupper($lieu_naissance);
	$adresse = $tab['adresse'];
	$code_postal = $tab['code_postal'];
	$ville = $tab['ville'];
	$ville = strtoupper($ville);
	$tel1 = $tab['tel1'];
	$tel2 = $tab['tel2'];
	$email = $tab['email'];
        $date_inscription = date("Y-m-d");
	$date_preinscription = date("y-m-d");
	$prix = $tab['prix'];
	$num_permis = $tab['num_permis'];
	$lieu_permis = $tab['lieu_permis'];
	$lieu_permis = strtoupper($lieu_permis);
	$date_permis = $tab['annee_permis']."-".$tab['mois_permis']."-".$tab['jour_permis'];
	$cas = $tab['cas'];

	$provenance = $tab['provenance'];
	if (!empty($tab['provenance_tel']))
            $provenance = 8;

	$civilite = $tab['civilite'];
	$date_infraction = $tab['jour_infraction']." ".$tab['mois_infraction']." ".
		$tab['annee_infraction']." (".$tab['heure_infraction'].":".$tab['minutes_infraction'].") a ".$tab['lieu_infraction'];
	$motif_infraction = $tab['motif_infraction'];
	$date_lettre = $tab['jour_48']." ".$tab['mois_48']." ".$tab['annee_48'];

	if (isset($tab['radio_type_paiement']) && $tab['radio_type_paiement'] == 1)
	{
		$supprime = 1;
		$status = "supprime";
	}
	else
	{
		$supprime = 0;
		$status = "pre-inscrit";
	}
    
	//filtrage inscriptions multiples
	//-------------------------------
	require ("../connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	$sql = "SELECT
                        stagiaire.* FROM stagiaire
                WHERE
                        stagiaire.nom='$nom' AND
                        stagiaire.id_stage=$stageId AND
                        stagiaire.date_naissance='$date_naissance' AND
                        stagiaire.supprime != 1";

	$rs = mysql_query($sql, $stageconnect) or die(mysql_error());
	$row = mysql_fetch_assoc($rs);
	$totalRows = mysql_num_rows($rs);


	if ($totalRows != 0 && $membreId != 188)
	{
		echo "<br><br><strong><font color='red'>VOUS ETES DEJA INSCRIT A CE STAGE, VOTRE INSCRIPTION A BIEN ETE PRISE EN COMPTE PAR NOTRE CENTRALE DE RESERVATION.<BR><BR>
		UN EMAIL VOUS A ETE ENVOYE A L'ADRESSE QUE VOUS NOUS AVEZ INDIQUEE RECAPITULANT LES DETAILS DE VOTRE INSCRIPTION.<br>
		SI TEL N'EST PAS LE CAS, MERCI DE CONTACTER NOTRE HOTLINE AU PLUS VITE AU 04-86-31-80-70<BR><BR>
		<u>L'EQUIPE PROSTAGESPERMIS.</u></font></strong>";

		exit;
	}


	require_once("../common/common_stages4.php");

    /////// VERIF BOOST AVANT DE FAIRE LE SELECT  ///////
    
    $boost_result = verif_boost($stageId, null, true);
    $is_boosted = $boost_result['is_boost'];
    
    // TODO : il manque le controle du choix de la CB : isset($tab['radio_type_paiement']) && $tab['radio_type_paiement'] == 1
    

    // Insertion de la recherche du stage AVANT TOUT LE RESTE


	//requete stage

//	$query_stage = "SELECT  stage.*,
//                                site.nom,
//                                site.ville,
//                                site.adresse,
//                                site.code_postal
//                        FROM stage inner join membre on stage.id_membre = membre.id, site
//                        WHERE 	stage.id = $stageId AND
//                                stage.id_site = site.id AND
//                                stage.id_membre = $membreId";
//

    
    // table  stage_boost  enlevée
    
//    $query_stage = "SELECT  stage_boost.*,
//                            stage.*,
//                            site.nom,
//                            site.ville,
//                            site.adresse,
//                            site.code_postal,
//                            site.departement,
//                            membre.boost_possible
//                    FROM    stage inner join membre on stage.id_membre = membre.id left join stage_boost on stage.id = stage_boost.id_stage and membre.id = stage_boost.id_membre, site
//                    WHERE   stage.id = $stageId AND
//                            stage.id_site = site.id AND
//                            stage.id_membre = $membreId
//                    order by stage_boost.id_boost DESC
//                    limit 1";
    
    $query_stage = "SELECT  stage.*,
                            site.nom,
                            site.ville,
                            site.adresse,
                            site.code_postal,
                            site.departement
                    FROM    stage inner join membre on stage.id_membre = membre.id, site
                    WHERE   stage.id = $stageId AND
                            stage.id_site = site.id AND
                            stage.id_membre = $membreId
                    limit 1";

//            echo '$query_stage : '.$query_stage;


	$rsStage = mysql_query($query_stage, $stageconnect) or die(mysql_error());
	$row_stage = mysql_fetch_assoc($rsStage);


	//Stage complet ou annulé ou cas des stages internes
	if (strtotime($row_stage['date1']) <= strtotime("now") || !empty($row_stage['annule']) || empty($row_stage['nb_places_allouees']) || ($membreId == 38 && !isset($tab['paiement_par_cb'])) || ($membreId == 175 && !isset($tab['paiement_par_cb'])))
	{
		echo "<br><br><br><br><br><br>";
		echo "<div align=\"center\" style=\"font-size:18px\">";
		echo "<font color='red'><b>Désolé, ce stage n'est plus à la vente car la session est complète.</b></font>";
		echo "<br><br>";
		echo "<b>D'autres sessions sont disponibles dans votre département.
			Retournez à l'accueil pour effectuer une nouvelle recherche.<br><br> <a href=\"javascript:window.location='/'\">=> RETOUR ACCUEIL</a></b>";
		echo "</div>";
		exit;
	}

	//requete membre
	$query_membre = sprintf("SELECT membre.* FROM membre
							WHERE membre.id = $membreId");
	$rsMembre = mysql_query($query_membre, $stageconnect) or die(mysql_error());
	$row_membre = mysql_fetch_assoc($rsMembre);


	//archive fichier
	//---------------
	$today_tmp = date("d-m-Y");
	$today_tmp .= " ";
	$today_tmp .= date("H:i:s");
	$file = 'archive-inscriptions-psp.txt';

	$contenu = $today_tmp.": Nom:".$nom." ".$prenom." Status:".$supprime." Coord:".$tel1." ".$tel2." ".$email." StageID:".$stageId." Prix:".$prix." Centre:".$membreId."\n";

	file_put_contents($file, $contenu, FILE_APPEND | LOCK_EX);


    
    
//    $id_boost = 0;
//
//    if (    isset($tab['radio_type_paiement']) && $tab['radio_type_paiement'] == 1
//        && !empty($row_membre['cb_actif']) && !empty($row_stage['boost_possible']) && $row_stage['boost_possible'] == 1
//        && !empty($row_stage['boost_actif']) && !empty($row_stage['id_boost'])
//    ) {
//            $id_boost = $row_stage['id_boost'];
//
//            if (!isset($tab['paiement_par_cb'])) {
//                $prix = $row_stage['prix_boost'];
//                $row_stage['prix'] = $row_stage['prix_boost'];
//            }
//    }

    
    $option_reversement = $row_stage['option_reversement'];
    $reversement = $row_stage['reversement'];
    
    
	//ajout_modif_privilege
	if (isset($_SESSION['privilege']) &&
		$row_stage['prix_privilege'] != NULL &&
		$row_stage['prix_privilege'] > 0 &&
		($row_stage['prix_privilege'] < $row_stage['prix'])
	)
	{
		$prix = $row_stage['prix_privilege'];
		$row_stage['prix'] = $row_stage['prix_privilege'];
		$is_privilege = true;
	}


	//CAS CARTE BLEUE ATOS:
	//--------------------------
	if (isset($tab['radio_type_paiement']) && $tab['radio_type_paiement'] == 1 &&
		($membreId == 64 || $membreId == 51 || $membreId == 87 || $membreId == 80 || $membreId == 82 || $membreId == 56 || $membreId == 70 || $membreId == 198 || $membreId == 199 || $membreId == 200 || $membreId == 201 || $membreId == 202 || $membreId == 182 || $membreId == 145 || $membreId == 240 || $membreId == 151 || $membreId == 215 || $membreId == 207 || $membreId == 299 || $membreId == 298 || $membreId == 300 || $membreId == 302 || $membreId == 282 || $membreId == 322 || $membreId == 327 || $membreId == 235 || $membreId == 285 || $membreId == 304 || $membreId == 318 || $membreId == 186 || $membreId == 350 || $membreId == 178))
	{
		$caddie = array();
        
		$caddie[] =  $stageId;
		$caddie[] =  $membreId;
		$caddie[] =  $civilite;
		$caddie[] =  $nom;
		$caddie[] =  $jeune_fille;
		$caddie[] =  $prenom;
		$caddie[] =  $date_naissance;
		$caddie[] =  $lieu_naissance;
		$caddie[] =  $adresse;
		$caddie[] =  $code_postal;
		$caddie[] =  $ville;
		$caddie[] =  $tel1;
		$caddie[] =  $tel2;
		$caddie[] =  $email;
		$caddie[] =  $date_preinscription;
		$caddie[] =  $prix;
		$caddie[] =  $num_permis;
		$caddie[] =  $lieu_permis;
		$caddie[] =  $date_permis;
		$caddie[] =  $cas;
		$caddie[] =  $date_infraction;
		$caddie[] =  $motif_infraction;
		$caddie[] =  $date_lettre;
		$caddie[] =  $supprime;

		//NumÃ©ro de commande
		$NumCmd      = "" . date("YmdHis");
		$caddie[] =  $NumCmd ;
		$caddie[] =  $site;
		$caddie[] =  $provenance;
//		$caddie[] =  $id_boost;

		$xCaddie = base64_encode(serialize($caddie));

        $paymentmeans = 'CB,2,VISA,2,MASTERCARD,2';

		//FIN CADDIE
		//----------

		print ("<HTML><HEAD><TITLE>SECURED-TRANSACTIONS - Paiement Securise sur Internet</TITLE></HEAD>");
		print ("<BODY bgcolor=#ffffff>");
		print ("<Font color=#000000>");
		print ("<center><H1>PAIEMENT CARTE BLEUE SECURISE</H1></center><br><br>");
        
		if ($membreId == 64) //actiroute
		{
			$parm="merchant_id=039248918300041";
			$parm="$parm pathfile=/home/prostage/bav/actiroute/pathfile";
			$path_bin = "/home/prostage/bav/actiroute/request";
		}
		else if ($membreId == 87) //testa
		{
			$parm="merchant_id=051099357900019";
			$parm="$parm pathfile=/home/prostage/bav/testa/pathfile";
			$path_bin = "/home/prostage/bav/testa/request";
		}
		else if ($membreId == 80) //ecopsycom
		{
			$parm="merchant_id=048496123000014";
			$parm="$parm pathfile=/home/prostage/bav/ecopsycom/pathfile";
			$path_bin = "/home/prostage/bav/ecopsycom/request";
		}
		else if ($membreId == 82) //alerte aux points
		{
			$parm="merchant_id=048748319000020";
			$parm="$parm pathfile=/home/prostage/bav/alerte_aux_points/pathfile";
			$path_bin = "/home/prostage/bav/alerte_aux_points/request";
		}
		else if ($membreId == 51) //ncf
		{
			$parm="merchant_id=050420905700026";
			$parm="$parm pathfile=/home/prostage/bav/ncf/pathfile";
			$path_bin = "/home/prostage/bav/ncf/request";
		}
		else if ($membreId == 56) //sysco
		{
			$parm="merchant_id=034052290300046";
			$parm="$parm pathfile=/home/prostage/bav/sysco/pathfile";
			$path_bin = "/home/prostage/bav/sysco/request";
		}
		else if ($membreId == 182) //flash prevention
		{
			$parm="merchant_id=048507610300012";
			$parm="$parm pathfile=/home/prostage/bav/flashprevention/pathfile";
			$path_bin = "/home/prostage/bav/flashprevention/request";
		}
		else if ($membreId == 70) //abripoints
		{
			$parm="merchant_id=050388066800010";
			$parm="$parm pathfile=/home/prostage/bav/abripoints/pathfile";
			$path_bin = "/home/prostage/bav/abripoints/request";
		}
		else if ($membreId == 198 || $membreId == 199 || $membreId == 200 || $membreId == 201 || $membreId == 202) //automobile club nord
		{
			$parm="merchant_id=077562649200448";
			$parm="$parm pathfile=/home/prostage/bav/autoclubnord/pathfile";
			$path_bin = "/home/prostage/bav/autoclubnord/request";
		}
		else if ($membreId == 145) //aravis
		{
			$parm="merchant_id=034810759000070";
			$parm="$parm pathfile=/home/prostage/bav/aravis/pathfile";
			$path_bin = "/home/prostage/bav/aravis/request";
		}
		else if ($membreId == 240) //id formalys prevention
		{
			$parm="merchant_id=053512099200016";
			$parm="$parm pathfile=/home/prostage/bav/formalys/pathfile";
			$path_bin = "/home/prostage/bav/formalys/request";
		}
		else if ($membreId == 151) //aesr44
		{
			$parm="merchant_id=049798970700029";
			$parm="$parm pathfile=/home/prostage/bav/aesr44/pathfile";
			$path_bin = "/home/prostage/bav/aesr44/request";
		}
		else if ($membreId == 207) //alliance permis
		{
			$parm="merchant_id=075015393400013";
			$parm="$parm pathfile=/home/prostage/bav/alliancepermis/pathfile";
			$path_bin = "/home/prostage/bav/alliancepermis/request";
		}

        /* INUTILE POUR L'INSTANT */
		else if ($membreId == 298) //Promotrans Rennes
		{
			$parm="merchant_id=077568013500826";
			$parm="$parm pathfile=/home/prostage/bav/promotrans-rennes/pathfile";
			$path_bin = "/home/prostage/bav/promotrans-rennes/request";
		}
		else if ($membreId == 299) //Promotrans Rouen
		{
			$parm="merchant_id=077568013500396";
			$parm="$parm pathfile=/home/prostage/bav/promotrans-rouen/pathfile";
			$path_bin = "/home/prostage/bav/promotrans-rouen/request";
		}
		else if ($membreId == 300) //Promotrans Le Havre
		{
			$parm="merchant_id=077568013500818";
			$parm="$parm pathfile=/home/prostage/bav/promotrans-le-havre/pathfile";
			$path_bin = "/home/prostage/bav/promotrans-le-havre/request";
		}
		else if ($membreId == 302) //Promotrans Nantes
		{
			$parm="merchant_id=077568013500214";
			$parm="$parm pathfile=/home/prostage/bav/promotrans-nantes/pathfile";
			$path_bin = "/home/prostage/bav/promotrans-nantes/request";
		}
        /* INUTILE POUR L'INSTANT */

		else if ($membreId == 215) //automobile club sud ouest
		{
			$parm="merchant_id=047934620700018";
			$parm="$parm pathfile=/home/prostage/bav/autoclubsudouest/pathfile";
//			$parm="$parm capture_mode=VALIDATION";
			$path_bin = "/home/prostage/bav/autoclubsudouest/request";
        }
		else if ($membreId == 322) //Agence sécurité routière
		{
			$parm="merchant_id=079363641600018";
			$parm="$parm pathfile=/home/prostage/bav/asr/pathfile";
//			$parm="$parm capture_mode=VALIDATION";
			$path_bin = "/home/prostage/bav/asr/request";
        }
		else if ($membreId == 282) //auto moto permis points
		{
			$parm="merchant_id=045376038100013";
			$parm="$parm pathfile=/home/prostage/bav/automotopermispoints/pathfile";
			$path_bin = "/home/prostage/bav/automotopermispoints/request";
//            $paymentmeans = 'CB,2,VISA,2,MASTERCARD,2,PAYLIB,2';
//			$parm="$parm capture_mode=VALIDATION";
//            $transid = date("His");
//            $transid = substr($transid, 0, 5) . 'p';
//            $parm="$parm transaction_id=999999";
//            $paymentmeans = 'CB,1,VISA,1,MASTERCARD,1';
//            $autorespurl = "http://www.prostagespermis.fr/call_autoresponse.php";
        }
		else if ($membreId == 327) // france points recup
		{
			$parm="merchant_id=079009436100017";
			$parm="$parm pathfile=/home/prostage/bav/francepointsrecup/pathfile";
			$path_bin = "/home/prostage/bav/francepointsrecup/request";
        }
		else if ($membreId == 235) // a points plus
		{
			$parm="merchant_id=048192533700018";
			$parm="$parm pathfile=/home/prostage/bav/apointsplus/pathfile";
			$path_bin = "/home/prostage/bav/apointsplus/request";
        }
		else if ($membreId == 285) // AFCCA
		{
			$parm="merchant_id=043918473000034";
			$parm="$parm pathfile=/home/prostage/bav/afcca/pathfile";
			$path_bin = "/home/prostage/bav/afcca/request";
        }
		else if ($membreId == 304) // Lyon PAP
		{
			$parm="merchant_id=075180840300016";
			$parm="$parm pathfile=/home/prostage/bav/lyonpap/pathfile";
			$path_bin = "/home/prostage/bav/lyonpap/request";
        }
		else if ($membreId == 318) // Cap 12 Points
		{
			$parm="merchant_id=079417200700013";
			$parm="$parm pathfile=/home/prostage/bav/cap12points/pathfile";
			$path_bin = "/home/prostage/bav/cap12points/request";
        }
		else if ($membreId == 186) // CASR PILLON
		{
			$parm="merchant_id=032219550400079";
			$parm="$parm pathfile=/home/prostage/bav/casr/pathfile";
			$path_bin = "/home/prostage/bav/casr/request";
        }
		else if ($membreId == 350) // FOUQUET DAVID
		{
			$parm="merchant_id=051296693800015";
			$parm="$parm pathfile=/home/prostage/bav/fouquet/pathfile";
			$path_bin = "/home/prostage/bav/fouquet/request";
        }
		else if ($membreId == 178) // STRIATUM
		{
			$parm="merchant_id=051389108500021";
			$parm="$parm pathfile=/home/prostage/bav/striatum/pathfile";
			$path_bin = "/home/prostage/bav/striatum/request";
        }
        
        /*
                        $parm="$parm merchant_country=fr";
                        $parm="$parm amount=".$prix*100;
                        $parm="$parm currency_code=978";
//                        $parm="$parm merchant_language=fr";
                        $parm="$parm transaction_id=".date("His");
                        $parm="$parm payment_means=CB,1,VISA,1,MASTERCARD,1";
//                        $parm="$parm caddie=".$xCaddie;

                        $parm="$parm normal_return_url=http://www.prostagespermis.fr/merci.php";
                        $parm="$parm cancel_return_url=http://www.prostagespermis.fr/regret.php";
                        $parm="$parm automatic_response_url=http://www.prostagespermis.fr/call_autoresponse_215.php";

                        $result = exec("$path_bin $parm");

                        //	sortie de la fonction : $result=!code!error!buffer!
                        //	    - code=0	: la fonction gÃ©nÃ¨re une page html contenue dans la variable buffer
                        //	    - code=-1 	: La fonction retourne un message d erreur dans la variable error

                        //On separe les differents champs et on les met dans une variable tableau

                        try {
                            $file = 'test_systempay_url_check.txt';
                            file_put_contents($file, "\n------------- TEST 215 -------------\n", FILE_APPEND | LOCK_EX);
                            file_put_contents($file, "$path_bin $parm", FILE_APPEND | LOCK_EX);
                            file_put_contents($file, "\n$result\n", FILE_APPEND | LOCK_EX);

                        } catch (Exception $exc) {
                        }

                        $tableau = explode ("!", "$result");

                        //	rÃ©cupÃ©ration des paramÃ¨tres
                        $code = $tableau[1];
                        $error = $tableau[2];
                        $message = $tableau[3];

                        echo '<head>
                                    <meta name="robots" content="noindex,nofollow" />
                                    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
                            </head>
                            <body>';

                        //  analyse du code retour
                        if (( $code == "" ) && ( $error == "" ) )
                        {
                                print ("<BR><CENTER>erreur appel request</CENTER><BR>");
                                print ("executable request non trouve $path_bin");
                        }

                        //	Erreur, affiche le message d erreur
                        else if ($code != 0)
                        {
                                print ("<center><b><h2>Erreur appel API de paiement.</h2></center></b>");
                                print ("<br><br><br>");
                                print (" message erreur : $error <br>");
                        }

                        //	OK, affiche le formulaire HTML
                        else
                        {
                                print ("<br><br>");

                                # OK, affichage du mode DEBUG si activÃ©
                                print (" $error <br>");
                                print ("  $message <br>");
                        }

                        print ("</BODY></HTML>");

                        exit;
		}
                */

		$parm="$parm merchant_country=fr";
		$parm="$parm amount=".$prix*100;
		$parm="$parm currency_code=978";
		$parm="$parm language=fr";
		$parm="$parm order_id=PSP_".$NumCmd;
		$parm="$parm payment_means=$paymentmeans";
		$parm="$parm caddie=".$xCaddie;

		$parm="$parm normal_return_url=http://www.prostagespermis.fr/merci.php";
		$parm="$parm cancel_return_url=http://www.prostagespermis.fr/regret.php";
		$parm="$parm automatic_response_url=http://www.prostagespermis.fr/call_autoresponse.php?id_membre=".$membreId;
        
		$result = exec("$path_bin $parm");

//                try {
//                    $file = 'test_systempay_url_check.txt';
//                    file_put_contents($file, "\n------------- common fiche preinscription -------------\n", FILE_APPEND | LOCK_EX);
//                    file_put_contents($file, "$path_bin $parm", FILE_APPEND | LOCK_EX);
//                    file_put_contents($file, "\n$result\n", FILE_APPEND | LOCK_EX);
//                    file_put_contents($file, "\n------------- common fiche preinscription -------------\n", FILE_APPEND | LOCK_EX);
//
//                } catch (Exception $exc) {
//                }

		//	sortie de la fonction : $result=!code!error!buffer!
		//	    - code=0	: la fonction gÃ©nÃ¨re une page html contenue dans la variable buffer
		//	    - code=-1 	: La fonction retourne un message d erreur dans la variable error

		//On separe les differents champs et on les met dans une variable tableau

		$tableau = explode ("!", "$result");

		//	rÃ©cupÃ©ration des paramÃ¨tres
		$code = $tableau[1];
		$error = $tableau[2];
		$message = $tableau[3];

                echo '<head>
                            <meta name="robots" content="noindex,nofollow" />
                            <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
                    </head>
                    <body>';

		//  analyse du code retour
		if (( $code == "" ) && ( $error == "" ) )
 		{
  			print ("<BR><CENTER>erreur appel request</CENTER><BR>");
  			print ("executable request non trouve $path_bin");
 		}

		//	Erreur, affiche le message d erreur
		else if ($code != 0)
		{
			print ("<center><b><h2>Erreur appel API de paiement.</h2></center></b>");
			print ("<br><br><br>");
			print (" message erreur : $error <br>");
		}

		//	OK, affiche le formulaire HTML
		else
		{
			print ("<br><br>");

			# OK, affichage du mode DEBUG si activÃ©
			print (" $error <br>");
			print ("  $message <br>");
		}

		print ("</BODY></HTML>");

		exit;

	}


	//require ("../connections/stageconnect.php");
	//mysql_select_db($database_stageconnect, $stageconnect);

	$id_externe = "";

	//cas securoute
	//-------------
	if ($membreId == 44)
	{
		$num_civilite = $civilite;                    //1=Mr 2=Mme 3=Mlle
		$nom = $nom;
		$prenom = $prenom;
		$nom_jf = $jeune_fille;
		$date_naissance = $date_naissance;
		$lieu_naissance = $lieu_naissance;
		$tel1 = $tel1;
		$tel2 = $tel2;
		$fax="";
		$email = $email;
		$permis_num = $num_permis;
		$date_permis = $date_permis;
		$permis_lieu = $lieu_permis;
		$prov="prostage";
		$dep_naissance="";
		$permis_dep="";
		$ip="";
		$stage_id = $stageId_externe;
		$capital_points="";
		$stage_cas = $cas;
		$infraction_date = "";
		$infraction_lieu = "";
		$infraction_heure = "";
		$infraction_motif = "";
		$infraction_48n_date = "";
		$cp_stagiaire = $code_postal;
		$adresse_stagiaire = $adresse;
		$ville_stagiaire = $ville;
		$prix = $prix;
		$paiement = 0;

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "http://www.gestion.securoute.net/reel/inscription.php");
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, "num_civilite=$num_civilite&nom=$nom&prenom=$prenom&nom_jf=$nom_jf&date_naissance=$date_naissance&lieu_naissance=$lieu_naissance&tel1=$tel1&tel2=$tel2&fax=$fax&email=$email&permis_num=$permis_num&date_permis=$date_permis&permis_lieu=$permis_lieu&prov=$prov&dep_naissance=$dep_naissance&permis_dep=$permis_dep&ip=$ip&stage_id=$stage_id&capital_points=$capital_points&stage_cas=$stage_cas&infraction_date=$infraction_date&infraction_lieu=$infraction_lieu&infraction_heure=$infraction_heure&infraction_motif=$infraction_motif&infraction_48n_date=$infraction_48n_date&cp_stagiaire=$cp_stagiaire&adresse_stagiaire=$adresse_stagiaire&ville_stagiaire=$ville_stagiaire&prix=$prix&paiement=$paiement");
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$id_externe = curl_exec($curl);
		curl_close($curl);

		if ($id_externe < 0)
		{
			echo "Error on response: id_externe=".$id_externe;
			exit;
		}
	}

	//insertion stagiaire
	//-------------------
	$ip = getIp();


//    $sql = "INSERT INTO stagiaire (id_stage,
//                                                                    nom,
//                                                                    jeune_fille,
//                                                                    prenom,
//                                                                    date_naissance,
//                                                                    lieu_naissance,
//                                                                    adresse,
//                                                                    code_postal,
//                                                                    ville,
//                                                                    tel,
//                                                                    mobile,
//                                                                    email,
//                                                                    date_inscription,
//                                                                    date_preinscription,
//                                                                    status,
//                                                                    paiement,
//                                                                    num_permis,
//                                                                    lieu_permis,
//                                                                    date_permis,
//                                                                    cas,
//                                                                    civilite,
//                                                                    date_infraction,
//                                                                    motif_infraction,
//                                                                    date_lettre,
//                                                                    supprime,
//                                                                    id_boost,
//                                                                    id_externe,
//                                                                    provenance,
//                                                                    provenance_site,
//                                                                    ip)
//    VALUES (\"$stageId\",
//                    \"$nom\",
//                    \"$jeune_fille\",
//                    \"$prenom\",
//                    \"$date_naissance\",
//                    \"$lieu_naissance\",
//                    \"$adresse\",
//                    \"$code_postal\",
//                    \"$ville\",
//                    \"$tel2\",
//                    \"$tel1\",
//                    \"$email\",
//                    \"$date_inscription\",
//                    \"$date_preinscription\",
//                    \"$status\",
//                    \"$prix\",
//                    \"$num_permis\",
//                    \"$lieu_permis\",
//                    \"$date_permis\",
//                    \"$cas\",
//                    \"$civilite\",
//                    \"$date_infraction\",
//                    \"$motif_infraction\",
//                    \"$date_lettre\",
//                    \"$supprime\",
//                    \"$id_boost\",
//                    \"$id_externe\",
//                    \"$provenance\",
//                    \"$site\",
//                    \"$ip\"
//                    )";
    
    $sql = "INSERT INTO stagiaire (id_stage,
                                                                    nom,
                                                                    jeune_fille,
                                                                    prenom,
                                                                    date_naissance,
                                                                    lieu_naissance,
                                                                    adresse,
                                                                    code_postal,
                                                                    ville,
                                                                    tel,
                                                                    mobile,
                                                                    email,
                                                                    date_inscription,
                                                                    date_preinscription,
                                                                    status,
                                                                    paiement,
                                                                    num_permis,
                                                                    lieu_permis,
                                                                    date_permis,
                                                                    cas,
                                                                    civilite,
                                                                    date_infraction,
                                                                    motif_infraction,
                                                                    date_lettre,
                                                                    supprime,
                                                                    id_externe,
                                                                    provenance,
                                                                    provenance_site,
                                                                    ip,
                                                                    option_reversement,
                                                                    reversement)
    VALUES (\"$stageId\",
                    \"$nom\",
                    \"$jeune_fille\",
                    \"$prenom\",
                    \"$date_naissance\",
                    \"$lieu_naissance\",
                    \"$adresse\",
                    \"$code_postal\",
                    \"$ville\",
                    \"$tel2\",
                    \"$tel1\",
                    \"$email\",
                    \"$date_inscription\",
                    \"$date_preinscription\",
                    \"$status\",
                    \"$prix\",
                    \"$num_permis\",
                    \"$lieu_permis\",
                    \"$date_permis\",
                    \"$cas\",
                    \"$civilite\",
                    \"$date_infraction\",
                    \"$motif_infraction\",
                    \"$date_lettre\",
                    \"$supprime\",
                    \"$id_externe\",
                    \"$provenance\",
                    \"$site\",
                    \"$ip\",
                    \"$option_reversement\",
                    \"$reversement\"
                    )";
    
//    echo '<div style="display:none">';
//    var_dump($sql);
//    echo '</div>';
    
	$insert_result = mysql_query($sql, $stageconnect);
	if (!$insert_result)
	{
        mysql_close($stageconnect);
		sleep(2);
        require ("../connections/stageconnect.php");
        mysql_select_db($database_stageconnect, $stageconnect);
		$insert_result = mysql_query($sql, $stageconnect);
		if (!$insert_result)
		{
            mysql_close($stageconnect);
            sleep(2);
            require ("../connections/stageconnect.php");
            mysql_select_db($database_stageconnect, $stageconnect);
            $insert_result = mysql_query($sql, $stageconnect);
            if (!$insert_result)
            {
                echo "<b>PROBLEME DE CONNEXION: MERCI DE RETENTER VOTRE RESERVATION OU DE NOUS CONTACTER PAR TELEPHONE</b>";
                exit;
            }
		}
	}

	$id_stagiaire_tmp = mysql_insert_id(); //attention s assurer que l ID n est pas un bigInt


    $_SESSION['id_stagiaire'] = $id_stagiaire_tmp;

	//stagiaire
	//---------
	$query_stagiaire = "SELECT stagiaire.* FROM stagiaire WHERE stagiaire.id = $id_stagiaire_tmp";
	$rsStagiaire = mysql_query($query_stagiaire, $stageconnect) or die(mysql_error());
	$row_stagiaire = mysql_fetch_assoc($rsStagiaire);
	$totalRows_stagiaire = mysql_num_rows($rsStagiaire);

	//insertion transaction
	//---------------------
	$date = date("y-m-d");

	$sql = "INSERT INTO transaction (id_stage,
                                        id_stagiaire,
                                        id_membre,
                                        type_paiement,
                                        date_transaction) VALUES
			('$stageId',
			'$id_stagiaire_tmp',
			'$membreId',
			'cheque_en_attente',
			'$date')";

	$res = mysql_query($sql, $stageconnect);

	if (mysql_affected_rows() <= 0)
	{
		sleep(1);
		mysql_query($sql);
		if (mysql_affected_rows() <= 0)
		{
			echo "<b>PROBLEME DE CONNEXION 2: MERCI DE RETENTER VOTRE RESERVATION OU DE NOUS CONTACTER PAR TELEPHONE</b>";
			exit;
		}
	}

	$id_transaction = mysql_insert_id(); //attention s assurer que l ID n est pas un bigInt

    $_SESSION['id_transaction'] = $id_transaction;

    $lien_cb_code_md5 = md5($stageId .'!' . $id_stagiaire_tmp .'!'.$membreId);
//    if (isset($tab['paiement_par_cb']))
//        echo 'code md5 : '.$lien_cb_code_md5.'<br />';

	//Cas Webservice Actiroute (paiement par cheque)
	//----------------------------------------------
	if ($membreId == 64)
	{
		$sql = "SELECT id_externe FROM stage WHERE stage.id=$stageId AND stage.id_membre=$membreId";
		$rs = mysql_query($sql, $stageconnect) or die(mysql_error());
		$row = mysql_fetch_assoc($rs);
		$total = mysql_num_rows($rs);

		if ($total == 1)
		{
			//on ne fait l inscription par webservice que si le stage a un id_externe
			if ($row['id_externe'] != 0)
			{
				require ("/home/prostage/soap/actiroute/inscription_actiroute.php");
				$ret = inscriptionActiroute($id_stagiaire_tmp, $id_transaction);

				if ($ret == -1)
				{
//					$sql = "DELETE FROM stagiaire WHERE ID=$ID_STAGIAIRE_TMP";
//					MYSQL_QUERY($SQL, $STAGECONNECT);
//
//					$SQL = "DELETE FROM TRANSACTION WHERE ID=$ID_TRANSACTION";
//					MYSQL_QUERY($SQL, $STAGECONNECT);

					echo "<b>ERREUR TECHNIQUE: Une erreur technique s'est produite lors de votre rÃ©servation.
					Merci de nous contacter rapidement pour finalier votre inscription Ã  l'aide d'un de nos conseillers.</b><br /><br />";

                    require ("../common/error.php");

                    sendError('Retour du webservice à -1', 'Ext: '.$row['id_externe'], 'id transaction : '.$id_transaction, "description_client: ".print_r($row_stagiaire, 1));
//					exit;
				}
			}
		}
	}

	//Cas ICSA Formation avant SPPlus
        //
        //   INUTILE ?!!!
        //
	//----------------------------------------------
	if (false && $membreId == 232 && isset($tab['radio_type_paiement']) && $tab['radio_type_paiement'] == 1)
	{

            // INFORMATIONS A MODIFIER POUR CHAQUE COMMERCANT FOURNIES PAR LE SERVICE INTEGRATION SPPLUS
            // cle marchand du commercant au format NT
            //$clent = "58 6d fc 9c 34 91 9b 86 3f fd 64 63 c9 13 4a 26 ba 29 74 1e c7 e9 80 79";
            $clent = "68 73 8e 17 2c 38 2e 35 48 71 c1 2a a4 dc 22 3b 32 d2 32 fc df 27 a5 d0";

            // code siret du commercant
            //$codesiret = "00000000000001-001";
            $codesiret = "43949291900030-001";

            // Montant à récupérer du panier
            $montant=$prix;//"15.00";

            // URL de retour automatique
            $urlretour="";//"15.00";

            // Devise dans laquelle est exprimé la commande : 978 Code pour l'EURO
            $devise="978";

            // Référence de la commande pour le commercant : unique pour chaque paiement effectué, limitée à 20 caractères
            $reference = "spp" . date("YmdHis");

            // L'email de l'internaute : élément fortement conseillé pour identification internaute
            $email= "contact@prostagespermis.fr";//service.installation@spplus.net";

            // Langue choisie pour l'interface de paiement
            $langue="FR";

            // Taxe appliquée
            $taxe="0.00";

            // Moyen de paiement choisi
            $moyen="CBS";

            // Modalité de paiement choisie
            $modalite="1x";

            // la fonction ci dessous permet de charger dynamiquement la librairie SP PLUS si elle n'est pas déclarée dans le fichier php.ini (rubrique extensions)
            if ( !extension_loaded('SPPLUS') ) { dl('php_spplus.so'); }

            // Fonction de calcul calcul_hmac
            $calcul_hmac=calcul_hmac($clent,$codesiret,$reference,$langue,$devise,$montant,$taxe,$validite);
            $url_calcul_hmac = "https://www.spplus.net/paiement/init.do?siret=$codesiret&reference=$reference&langue=$langue&devise=$devise&montant=$montant&taxe=$taxe&hmac=$calcul_hmac&moyen=$moyen&modalite=$modalite";

            // Fonction de calcul calculhmac
            $data="siret=$codesiret&reference=$reference&langue=$langue&devise=$devise&montant=$montant&taxe=$taxe&moyen=$moyen&modalite=$modalite";
            $calculhmac=calculhmac($clent,$data);
            $url_calculhmac = "https://www.spplus.net/paiement/init.do?siret=$codesiret&reference=$reference&langue=$langue&devise=$devise&montant=$montant&taxe=$taxe&moyen=$moyen&modalite=$modalite&hmac=$calculhmac";

            // Fonction de calcul nthmac
            $data= "$codesiret$reference$langue$devise$montant$taxe$moyen$modalite";
            $nthmac=nthmac($clent,$data);
            $url_nthmac = "https://www.spplus.net/paiement/init.do?siret=$codesiret&reference=$reference&langue=$langue&devise=$devise&montant=$montant&taxe=$taxe&moyen=$moyen&modalite=$modalite&hmac=$nthmac";

            // Fonction d'encryptage de l'url SigneUrlPaiement
            // Cryptage en base 64 de la chaîne de paramètres à envoyer au serveur SPPLUS
            $url_signeurlpaiement = "https://www.spplus.net/paiement/init.do?siret=$codesiret&reference=$reference&langue=$langue&devise=$devise&montant=$montant&taxe=$taxe&moyen=$moyen&modalite=$modalite";
            $urlspplus=signeurlpaiement($clent,$url_signeurlpaiement);


            $form = '<html>
                    <head>
                        <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
                        <title>Redirection vers la plateforme de paiement</title>
                    </head>
                    <body onload="window.location = \''.$url_nthmac.'\'">
                        <center>
                            Vous allez être redirigé vers la plateforme de paiement sécurisée<br />dans quelques secondes...<br />
                            <br />
                            <a href="'.$url_nthmac.'" id="form_paiement_cb" name="form_paiement_cb">Accéder à la plateforme de paiement</a>
                        </center>
                    </form>
                    </body>
                    </html>';
            echo utf8_encode($form);
            exit;
	}

	//Cas paiement CM-CIC
	//-------------------
//	if (isset($tab['radio_type_paiement']) && $tab['radio_type_paiement'] == 1 &&
//		($membreId == 223 || $membreId == 119 || $membreId == 96 || $membreId == 269 || $membreId == 270 || $membreId == 271 || $membreId == 272 || $membreId == 273 || $membreId == 274 || $membreId == 275 || $membreId == 276 || $membreId == 277))
	if (isset($tab['radio_type_paiement']) && $tab['radio_type_paiement'] == 1 &&
		($membreId == 223 || $membreId == 119 || $membreId == 96 || $membreId == 190 || $membreId == 318 || $membreId == 290 || $membreId == 283 || $membreId == 184 || $membreId == 417 || $membreId == 78 || $membreId == 122 || $membreId == 217 || $membreId == 382 || $membreId == 256 || $membreId == 255))
	{
                // Initialisation des paramètres
                define ("CMCIC_URLOK", "http://www.prostagespermis.fr/merci.php");
                define ("CMCIC_URLKO", "http://www.prostagespermis.fr/regret.php");
                define ("CMCIC_VERSION", "3.0");

		if ($membreId == 223) // ABC du Dialogue Routier
		{
//                    define ("CMCIC_CLE", "b1b89b308ce9d7be4d16af60435734e8d7086b2c");
                    define ("CMCIC_CLE", "C56BA8751853C9ADEB59DBC615C0BC9C0CBE9095");
                    define ("CMCIC_TPE", "6599828");
                    define ("CMCIC_CODESOCIETE", "abcdudialo");
		}
		else if ($membreId == 119) // Olivier formations
		{
//                    define ("CMCIC_CLE", "ca49ad3c95f3bffa6fc9b454be1a1a07eca5856f");
                    define ("CMCIC_CLE", "4317F30DAC041AE436FE8B5A2C8238079C980APE");
                    define ("CMCIC_TPE", "0350928");
//                    define ("CMCIC_CODESOCIETE", "olivierfor");
                    define ("CMCIC_CODESOCIETE", "prostagepermis");
		}
		else if ($membreId == 96) // Auto's cool
		{
                    define ("CMCIC_CLE", "D688A43616D299975B0FA41D1774F06F0530BD96");
                    define ("CMCIC_TPE", "0355312");
                    define ("CMCIC_CODESOCIETE", "autoscool");
//                    $test_mode = 1;
		}
		else if ($membreId == 190) // A point nommé
		{
                    define ("CMCIC_CLE", "9982B22C5C95BFD4C745AC4CD9DF4CCDECB8FD99");
                    define ("CMCIC_TPE", "0368633");
                    define ("CMCIC_CODESOCIETE", "apointnomm");
//                    $test_mode = 1;
		}
		else if ($membreId == 318) // CAP 12 POINTS
		{
                    define ("CMCIC_CLE", "A853D209B52A6F17E2B2CF5CB999F8A2353169PF");
                    define ("CMCIC_TPE", "0372182");
                    define ("CMCIC_CODESOCIETE", "cap12point");
//                    $test_mode = 1;
		}
		else if ($membreId == 290) // AUTO ECOLE SAINT JACQUES
		{
                    define ("CMCIC_CLE", "53E1019B737C2D7167C9AFF45C32DCB886EF73P2");
                    define ("CMCIC_TPE", "6855472");
//                    define ("CMCIC_CODESOCIETE", "saintjacqu");
//                    define ("CMCIC_CODESOCIETE", "autoecolestjacques");
//                    define ("CMCIC_CODESOCIETE", "aesaintjacques");
                    define ("CMCIC_CODESOCIETE", "autoecoles");
		}
		else if ($membreId == 283) // ECF EAF
		{
                    define ("CMCIC_CLE", "E18ACDEDB9B7F0343CE13214A4CAC2CC99D02D9C");
                    define ("CMCIC_TPE", "0838303");
                    define ("CMCIC_CODESOCIETE", "euroautofo");
//                    $test_mode = 1;
		}
		else if ($membreId == 184) // ABC POINTS
		{
                    define ("CMCIC_CLE", "F32FD839D1EA8BDB422F3E2A6ADCF5B672BDF19B");
                    define ("CMCIC_TPE", "6273807");
                    define ("CMCIC_CODESOCIETE", "abcpoints");
		}
		else if ($membreId == 417) // CESR PRO
		{
                    define ("CMCIC_CLE", "EB9C090F9F246596C5B97F49F711FDA23BE0129D");
                    define ("CMCIC_TPE", "0349792");
                    define ("CMCIC_CODESOCIETE", "CESR14");
		}
		else if ($membreId == 78) // Active points
		{
                    define ("CMCIC_CLE", "175B33AA33D653B14674DDB31B97C2F480E341D");
                    define ("CMCIC_TPE", "6089779");
                    define ("CMCIC_CODESOCIETE", "prostagespermis");
                    $test_mode = 1;
		}
		else if ($membreId == 122) // Asso Route Plus
		{
                    define ("CMCIC_CLE", "DD58FC9D58F077577B55F98B0D7DEED04CF158PD");
                    define ("CMCIC_TPE", "6174495");
                    define ("CMCIC_CODESOCIETE", "assroutepl");
//                    $test_mode = 1;
		}
		else if ($membreId == 217) // Association Police Pilotes Gendarmerie
		{
                    define ("CMCIC_CLE", "B02120B584E5A0E5DEEC33DA5A44FFD6B20E2E9B");
                    define ("CMCIC_TPE", "0384148");
                    define ("CMCIC_CODESOCIETE", "asspilotes");
//                    $test_mode = 1;
		}
		else if ($membreId == 382) // CRIDEF
		{
                    define ("CMCIC_CLE", "A9B7983FAA63DBE25A0511AA02D8E70E07F33C91");
                    define ("CMCIC_TPE", "6273249");
                    define ("CMCIC_CODESOCIETE", "cridefcons");
//                    $test_mode = 1;
		}
		else if ($membreId == 256) // Prelude Formations
		{
                    define ("CMCIC_CLE", "2FFAE610AA6D9BD5FDA4A255067A6BFA7AE1C692");
                    define ("CMCIC_TPE", "0385258");
                    define ("CMCIC_CODESOCIETE", "stagepointsspf");
                    $test_mode = 1;
		}
		else if ($membreId == 255) // Sauv Permis
		{
                    define ("CMCIC_CLE", "692FAC26D641371EA026428B236EA3DA3E5AA29D");
                    define ("CMCIC_TPE", "0371276");
                    define ("CMCIC_CODESOCIETE", "prostages");
//                    $test_mode = 1;
		}
//		else if (in_array($membreId, array(269, 270, 271, 272, 273, 274, 275, 276, 277))) // A.N.P.E.R
//		{
//                    define ("CMCIC_CLE", "47BC5056478356607D4B00A6799074BC43D853AB");
//                    define ("CMCIC_TPE", "6644394");
//                    define ("CMCIC_CODESOCIETE", "anper");
//		}
        
        if ($test_mode)
            define ("CMCIC_SERVEUR", "https://ssl.paiement.cic-banques.fr/test/");
        else
            define ("CMCIC_SERVEUR", "https://ssl.paiement.cic-banques.fr/");

        // PHP implementation of RFC2104 hmac sha1 ---
        require_once("CMCIC_Tpe.inc.php");

        $sOptions = "";

        // ----------------------------------------------------------------------------
        //  CheckOut Stub setting fictious Merchant and Order datas.
        //  That's your job to set actual order fields. Here is a stub.
        // -----------------------------------------------------------------------------

        // Reference: unique, alphaNum (A-Z a-z 0-9), 12 characters max
        $sReference = "ref" . date("His");

        // Amount : format  "xxxxx.yy" (no spaces)
        $sMontant = $prix;

        // Currency : ISO 4217 compliant
        $sDevise  = "EUR";

        // free texte : a bigger reference, session context for the return on the merchant website
        $sTexteLibre = $id_transaction; // Contient maintenant l'identifiant de transaction

        // transaction date : format d/m/y:h:m:s
        $sDate = date("d/m/Y:H:i:s");

        // Language of the company code
        $sLangue = "FR";

        // customer email
        $sEmail = $email;

        // ----------------------------------------------------------------------------

        // between 2 and 4
        //$sNbrEch = "4";
        $sNbrEch = "";

        // date echeance 1 - format dd/mm/yyyy
        //$sDateEcheance1 = date("d/m/Y");
        $sDateEcheance1 = "";

        // montant échéance 1 - format  "xxxxx.yy" (no spaces)
        //$sMontantEcheance1 = "0.26" . $sDevise;
        $sMontantEcheance1 = "";

        // date echeance 2 - format dd/mm/yyyy
        $sDateEcheance2 = "";

        // montant échéance 2 - format  "xxxxx.yy" (no spaces)
        //$sMontantEcheance2 = "0.25" . $sDevise;
        $sMontantEcheance2 = "";

        // date echeance 3 - format dd/mm/yyyy
        $sDateEcheance3 = "";

        // montant échéance 3 - format  "xxxxx.yy" (no spaces)
        //$sMontantEcheance3 = "0.25" . $sDevise;
        $sMontantEcheance3 = "";

        // date echeance 4 - format dd/mm/yyyy
        $sDateEcheance4 = "";

        // montant échéance 4 - format  "xxxxx.yy" (no spaces)
        //$sMontantEcheance4 = "0.25" . $sDevise;
        $sMontantEcheance4 = "";

        // ----------------------------------------------------------------------------

        $oTpe = new CMCIC_Tpe($sLangue);
        $oHmac = new CMCIC_Hmac($oTpe);

        // Control String for support
        $CtlHmac = sprintf(CMCIC_CTLHMAC, $oTpe->sVersion, $oTpe->sNumero, $oHmac->computeHmac(sprintf(CMCIC_CTLHMACSTR, $oTpe->sVersion, $oTpe->sNumero)));

        // Data to certify
        $PHP1_FIELDS = sprintf(CMCIC_CGI1_FIELDS,     $oTpe->sNumero,
                                                    $sDate,
                                                    $sMontant,
                                                    $sDevise,
                                                    $sReference,
                                                    $sTexteLibre,
                                                    $oTpe->sVersion,
                                                    $oTpe->sLangue,
                                                    $oTpe->sCodeSociete,
                                                    $sEmail,
                                                    $sNbrEch,
                                                    $sDateEcheance1,
                                                    $sMontantEcheance1,
                                                    $sDateEcheance2,
                                                    $sMontantEcheance2,
                                                    $sDateEcheance3,
                                                    $sMontantEcheance3,
                                                    $sDateEcheance4,
                                                    $sMontantEcheance4,
                                                    $sOptions);

        // MAC computation
        $sMAC = $oHmac->computeHmac($PHP1_FIELDS);

        // --------------------------------------------------- End Stub ---------------


        // ----------------------------------------------------------------------------
        // Your Page displaying payment button to be customized
        // ----------------------------------------------------------------------------

        $form = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
            <head>
            <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
            <meta http-equiv="cache-control" content="no-store, no-cache, must-revalidate, post-check=0, pre-check=0" />
            <meta http-equiv="Expires" content="Mon, 26 Jul 1997 05:00:00 GMT" />
            <meta http-equiv="pragma" content="no-cache" />
            <title>Connexion au serveur de paiement</title>
            <link type="text/css" rel="stylesheet" href="template/cmcic/CMCIC.css" />
            </head>

            <body>
            <div id="header">
                    <a href="http://www.cmcicpaiement.fr"><img src="logocmcicpaiement.gif" alt="CM-CIC P@iement" title="CM-CIC P@iement" /></a>
            </div>
            <h1>Connexion au serveur de paiement</h1>
            <p>
                    Cliquez sur le bouton ci-dessous pour vous connecter au serveur de paiement.<br />
            </p>
            <!-- FORMULAIRE TYPE DE PAIEMENT / PAYMENT FORM TEMPLATE -->
            <form action="'.$oTpe->sUrlPaiement.'" method="post" id="PaymentRequest">
            <p>
                    <input type="hidden" name="version"             id="version"        value="'.$oTpe->sVersion.'" />
                    <input type="hidden" name="TPE"                 id="TPE"            value="'.$oTpe->sNumero.'" />
                    <input type="hidden" name="date"                id="date"           value="'.$sDate.'" />
                    <input type="hidden" name="montant"             id="montant"        value="'.$sMontant . $sDevise.'" />
                    <input type="hidden" name="reference"           id="reference"      value="'.$sReference.'" />
                    <input type="hidden" name="MAC"                 id="MAC"            value="'.$sMAC.'" />
                    <input type="hidden" name="url_retour"          id="url_retour"     value="'.$oTpe->sUrlKO.'" />
                    <input type="hidden" name="url_retour_ok"       id="url_retour_ok"  value="'.$oTpe->sUrlOK.'" />
                    <input type="hidden" name="url_retour_err"      id="url_retour_err" value="'.$oTpe->sUrlKO.'" />
                    <input type="hidden" name="lgue"                id="lgue"           value="'.$oTpe->sLangue.'" />
                    <input type="hidden" name="societe"             id="societe"        value="'.$oTpe->sCodeSociete.'" />
                    <input type="hidden" name="texte-libre"         id="texte-libre"    value="'.HtmlEncode($sTexteLibre).'" />
                    <input type="hidden" name="mail"                id="mail"           value="'.$sEmail.'" />
                    <!-- Uniquement pour le Paiement fractionné -->
                    <input type="hidden" name="nbrech"              id="nbrech"         value="'.$sNbrEch.'" />
                    <input type="hidden" name="dateech1"            id="dateech1"       value="'.$sDateEcheance1.'" />
                    <input type="hidden" name="montantech1"         id="montantech1"    value="'.$sMontantEcheance1.'" />
                    <input type="hidden" name="dateech2"            id="dateech2"       value="'.$sDateEcheance2.'" />
                    <input type="hidden" name="montantech2"         id="montantech2"    value="'.$sMontantEcheance2.'" />
                    <input type="hidden" name="dateech3"            id="dateech3"       value="'.$sDateEcheance3.'" />
                    <input type="hidden" name="montantech3"         id="montantech3"    value="'.$sMontantEcheance3.'" />
                    <input type="hidden" name="dateech4"            id="dateech4"       value="'.$sDateEcheance4.'" />
                    <input type="hidden" name="montantech4"         id="montantech4"    value="'.$sMontantEcheance4.'" />
                    <!-- -->
                    <input type="submit" name="bouton"              id="bouton"         value="Accèder au paiement" />
            </p>
            </form>
            </body>
            </html>';

        echo $form;
		exit;

	}

	//Cas paiement SystemPay
	//-------------------
	if (isset($tab['radio_type_paiement']) && $tab['radio_type_paiement'] == 1 &&
		($membreId == 127 || $membreId == 108 || $membreId == 111 || $membreId == 232 || $membreId == 241 || $membreId == 213 || $membreId == 331 || $membreId == 219 || $membreId == 310 || $membreId == 408 || $membreId == 375 || $membreId == 231))
	{
                // Initialisation des paramètres
                $spay_params = array(); // tableau des paramètres du formulaire
                $spay_params['vads_ctx_mode'] = "PRODUCTION";

                $key = '';

                // LR Formations
		if ($membreId == 127)
		{
                        $spay_params['vads_site_id']  = "54345654";
                        $key = "5122186779035783";
//                        $key = "3386679894685729";
//                        $spay_params['vads_ctx_mode'] = "TEST";
		}
		elseif ($membreId == 108) // CourOTop
		{
                        $spay_params['vads_site_id']  = "54077782";
                        $key = "2554565349753888";
//                        $key = "3386679894685729";
//                        $spay_params['vads_ctx_mode'] = "TEST";
		}
//		elseif ($membreId == 189) // IFECC
//		{
//                        $spay_params['vads_site_id']  = "78113813";
////                        $key = "7904578183365236";
//                        $key = "9386415771564136";
////                        $spay_params['vads_ctx_mode'] = "TEST";
//		}
		elseif ($membreId == 111) // Automobile club provence
		{
                        $spay_params['vads_site_id']  = "62489559";
//                        $key = "7904578183365236";
                        $key = "6997100820276467";
//                        $spay_params['vads_ctx_mode'] = "TEST";
		}
		elseif ($membreId == 232 || $membreId == 241) // ICSA Permis
		{
                        $spay_params['vads_site_id']  = "39575209";
//                        $key = "7904578183365236";
                        $key = "6434329655404482";
//                        $spay_params['vads_ctx_mode'] = "TEST";
		}
		elseif ($membreId == 213) // Ametys
		{
                        $spay_params['vads_site_id']  = "77193341";
//                        $key = "6016770459938919";
                        $key = "5697728261078307";
//                        $spay_params['vads_ctx_mode'] = "TEST";
		}
		elseif ($membreId == 331) // Permis valide
		{
                        $spay_params['vads_site_id']  = "21881920";
//                        $key = "6016770459938919";
                        $key = "7203754458803742";
//                        $spay_params['vads_ctx_mode'] = "TEST";
		}
		elseif ($membreId == 219) // MLS FSR
		{
                        $spay_params['vads_site_id']  = "25614599";
//                        $key = "1705201579282383";
                        $key = "6680672303902578";
//                        $spay_params['vads_ctx_mode'] = "TEST";
		}
		elseif ($membreId == 310) // Capital Permis
		{
                        $spay_params['vads_site_id']  = "13173936";
//                        $key = "1705201579282383";
                        $key = "9530291179293453";
//                        $spay_params['vads_ctx_mode'] = "TEST";
		}
		elseif ($membreId == 408) // Allo Points
		{
                        $spay_params['vads_site_id']  = "22812019";
//                        $key = "5388755922099540";
                        $key = "4333550706392738";
//                        $spay_params['vads_ctx_mode'] = "TEST";
		}
		elseif ($membreId == 375) // AP2R
		{
                        $spay_params['vads_site_id']  = "16701512";
//                        $key = "5891419723044230";
                        $key = "5324436651777665";
//                        $spay_params['vads_ctx_mode'] = "TEST";
		}
		elseif ($membreId == 231) // Auto Ecole SaintEx
		{
                        $spay_params['vads_site_id']  = "70950168";
//                        $key = "6874592664711761";
                        $key = "3370029704407366";
//                        $spay_params['vads_ctx_mode'] = "TEST";
		}
//		elseif ($membreId == 252) // CEPIM
//		{
//                        $spay_params['vads_site_id']  = "0366417";
//                        $key = "75314925466790";
////                        $key = "3386679894685729";
////                        $spay_params['vads_ctx_mode'] = "TEST";
//		}
        
                // id stagiaire de test : 38022,38021,38020,38017,38015,38014,38013,38012,38011,38007,38006,38004
                // ncf 8 9 oct
                $spay_params['vads_amount'] = 100*$prix; // en cents
                $spay_params['vads_currency'] = "978"; // norme ISO 4217
                $spay_params['vads_page_action'] = "PAYMENT";
                $spay_params['vads_action_mode'] = "INTERACTIVE"; // saisie de carte réalisée par la plateforme
                $spay_params['vads_payment_config']= "SINGLE";
                $spay_params['vads_version']  = "V2";
                $spay_params['vads_trans_date']  = gmdate("YmdHis", time());
                $spay_params['vads_trans_id'] = substr(time(), -6);
                $spay_params['vads_order_id'] = $id_transaction;
                $spay_params['vads_return_mode'] = 'POST';
                $spay_params['vads_url_check'] = "http://www.prostagespermis.fr/call_autoresponse_spay.php?id_membre=".$membreId;
                $spay_params['vads_url_success'] = "http://www.prostagespermis.fr/merci.php";
                $spay_params['vads_url_return'] = "http://www.prostagespermis.fr/regret.php";
//                $spay_params['vads_url_return'] = "http://www.prostagespermis.fr/regret.php?ref=".$id_transaction;

                // Génération de la signature
                ksort($spay_params); // tri des paramètres par ordre alphabétique
                $contenu_signature = "";
                foreach ($spay_params as $nom => $valeur)
                {
                        $contenu_signature .= $valeur."+";
                }
                $contenu_signature .= $key; // On ajoute le certificat à la fin
                $spay_params['signature'] = sha1($contenu_signature);
//                echo 'contenu_signature : '.$contenu_signature;
//                echo '<br />sha1 contenu_signature : '.sha1($contenu_signature);
//                echo "\nkey: ".$key;

//                INTERACTIVE+0+TEST+978+37811+PAYMENT+SINGLE+POST+54345654+20120925150317+585397+http://www.prostagespermis.fr/call_autoresponse_spay.php+http://www.prostagespermis.fr/regret.php+http://www.prostagespermis.fr/merci.php+V2+5122186779035783
//                INTERACTIVE+0+TEST+978+37811+PAYMENT+SINGLE+POST+54345654+20120925150317+585397+http://www.prostagespermis.fr/call_autoresponse_spay.php+http://www.prostagespermis.fr/regret.php+http://www.prostagespermis.fr/merci.php+V2+3386679894685729

//                exit();
                $form = '<html>
                        <head>
                                <title>Redirection vers la plateforme de paiement</title>
                        </head>
                        <body onload="document.form_paiement_cb.submit()">
                        <form method="POST" action="https://systempay.cyberpluspaiement.com/vads-payment/" id="form_paiement_cb" name="form_paiement_cb">';

                foreach($spay_params as $nom => $valeur) $form .= '<input type="hidden" name="' . $nom . '" value="' . $valeur . '" />';

                $form .= '  <center>Vous allez être redirigé vers la plateforme de paiement sécurisée<br />dans quelques secondes...<br /><br /><input type="submit" value="Accéder à la plateforme de paiement" /></center>
                      </form>
                      </body>
                      </html>';
                echo $form;
		exit;

	}
    
    
	//Cas paiement PayPal
	//-------------------
	if (isset($tab['radio_type_paiement']) && $tab['radio_type_paiement'] == 1 &&
		($membreId == 392))
	{
        $email = '';

        // ASR SARL
		if ($membreId == 392)
		{
            $email = 'fdaguenet@alerte-permis.com';
//            $email = 'support@khapeo.com';
		}

        $montant = $prix; // en cents
        $taxe = '19.6';
        $return_url = 'http://www.prostagespermis.fr/merci.php';
        $cancel_url = 'http://www.prostagespermis.fr/regret.php';
        $notify_url = 'http://www.prostagespermis.fr/call_autoresponse_paypal.php';
        
        
        echo '<!DOCTYPE html>
                <html>
                  <body onload="document.paypal_form.submit()" style="width:100%; height:100%">
                    <form id="paypalForm" name="paypal_form" style="display:block;margin:200px auto;width:200px" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                        <input type="hidden" value="_xclick" name="cmd">
                        <input type="hidden" value="'.$email.'" name="business">
                        <input type="hidden" value="Stage de recuperation de points" name="item_name">
                        <input type="hidden" value="'.$montant.'.00" name="amount">
                        <input type="hidden" value="'.$id_transaction.'" name="custom">
                        <input type="hidden" value="0" name="shipping">
                        <input type="hidden" value="0" name="discount_amount">        
                        <input type="hidden" value="0" name="no_shipping">
                        <input type="hidden" value="EUR" name="currency_code">
                        <input type="hidden" value="'.$taxe.'" name="tax_rate">
                        <input type="hidden" value="'.$cancel_url.'" name="cancel_return">
                        <input type="hidden" value="'.$return_url.'" name="return">
                        <input type="hidden" value="'.$notify_url.'" name="notify_url">
                        <input type="hidden" value="1" name="rm">      
                        <input type="hidden" value="1" name="no_note">
                        <input type="hidden" value="FR" name="lc">
                        <input type="image" src="https://www.paypalobjects.com/fr_FR/FR/i/btn/btn_paynowCC_LG.gif" border="0" name="submit" alt="PayPal - la solution de paiement en ligne la plus simple et la plus sécurisée !">
                        <img alt="" border="0" src="https://www.paypalobjects.com/fr_FR/i/scr/pixel.gif" width="1" height="1">
                    </form>
                  </body>
                </html>';
		exit;

	}
    
    
	//Cas paiement Payline
	//-------------------
	if (isset($tab['radio_type_paiement']) && $tab['radio_type_paiement'] == 1 &&
		($membreId == 252 || $membreId == 179 || $membreId == 294))
        {
            $merchantId = $accessKey = $contract_number = $contract_number_list = null;

            if ($membreId == 179) // Anne Samson
            {
                $merchantId = '68832356076353'; // Merchant ID
                $accessKey = 'LBG8zg2Pk0j5IReo6a5q'; // Certificate key
                $contract_number = '0360775'; // Contract type default (ex: 001 = CB, 003 = American Express...)
                $contract_number_list = '0360775'; // Contract type multiple values (separator: ;)
//                $test_mode = true;
            }
            else if ($membreId == 252) // CEPIM
            {
//                $merchantId = '75314925466790'; // Merchant ID TEST
//                $accessKey = 'TENS5Ig2wB0sLXAw5B5q'; // Certificate key TEST
                $merchantId = '50524271220033'; // Merchant ID
                $accessKey = 'ksAutIxJAvMFxvOkTnht'; // Certificate key
                $contract_number = '0366417'; // Contract type default (ex: 001 = CB, 003 = American Express...)
                $contract_number_list = '0366417'; // Contract type multiple values (separator: ;)
//                $test_mode = true;
            }
            else if ($membreId == 294) // A.B.A.C
            {
                
                $merchantId = '70394494030417'; // Merchant ID
//                $accessKey = '0cFGjsb0iKfsoLViRMdI'; // Certificate key TEST
                $accessKey = 'PPjC8v22Oz9mtXFhXrBP'; // Certificate key
                $contract_number = '0372725'; // Contract type default (ex: 001 = CB, 003 = American Express...)
                $contract_number_list = '0372725'; // Contract type multiple values (separator: ;)
//                $test_mode = true;
            }
            
            require_once("../payline/include.php");
            $array = array();
            $payline = new paylineSDK($merchantId, $accessKey, PROXY_HOST, PROXY_PORT, PROXY_LOGIN, PROXY_PASSWORD, !$test_mode);
            $payline->returnURL = 'http://prostagespermis.fr/merci.php?id_membre='.$membreId;
            $payline->cancelURL = 'http://prostagespermis.fr/regret.php?id_membre='.$membreId;
            $payline->notificationURL = 'http://prostagespermis.fr/call_autoresponse_payline.php?id_membre='.$membreId.'&id_transaction='.$id_transaction;
            
            
            // PAYMENT
            $array['payment']['amount'] = $prix*100;
            $array['payment']['currency'] = PAYMENT_CURRENCY;
            $array['payment']['action'] = PAYMENT_ACTION;
            $array['payment']['mode'] = PAYMENT_MODE;

            
            // ORDER
            $array['order']['ref'] = $id_transaction;
            $array['order']['amount'] = $prix*100;
            $array['order']['currency'] = ORDER_CURRENCY;
            

            // CONTRACT NUMBERS
            $array['payment']['contractNumber'] = $contract_number;
            $contracts = explode(";",$contract_number_list);
            $array['contracts'] = $contracts;
//            $secondContracts = explode(";",SECOND_CONTRACT_NUMBER_LIST);
//            $array['secondContracts'] = $secondContracts;

            // EXECUTE
            $result = $payline->doWebPayment($array);
//                var_dump($result);

            // RESPONSE
            /*if($test_mode){
                require('../payline/examples/demos/result/header.html');
                echo '<H3>REQUEST</H3>';
                print_a($array, 0, true);
                echo '<H3>RESPONSE</H3>';
                var_dump($result);
                print_a($result, 0, true);
                require('../payline/examples/demos/result/footer.html');
            }
            else*/ if (isset($result) && is_array($result)) {
                if($result['result']['code'] == '00000'){
                    header("location:".$result['redirectURL']);
                    exit();
                }
                elseif(isset($result)) {
                    echo 'ERROR : '.$result['result']['code']. ' '.$result['result']['longMessage'].' <BR/>';
                }
            }
            else
            exit;
        }

	//Cas paiement PAYBOX
	//-------------------
	if (isset($tab['radio_type_paiement']) && $tab['radio_type_paiement'] == 1 &&
		($membreId == 85 || $membreId == 135 || $membreId == 107 || $membreId == 106 || $membreId == 181
		|| $membreId == 176 || $membreId == 205 || $membreId == 114 || $membreId == 228 || $membreId == 193 || $membreId == 98 || $membreId == 287 || $membreId == 313 || $membreId == 185 || $membreId == 335 || $membreId == 328 || $membreId == 305 || $membreId == 262 || $membreId == 384 || $membreId == 263 || $membreId == 234 || $membreId == 84 || $membreId == 189 || $membreId == 197 || $membreId == 203 || $membreId == 330))
	{
		$PBX_RANG = '01';

		if ($membreId == 85) // acf
		{
			$PBX_IDENTIFIANT = '711828198';
			$PBX_SITE = '0598995';
		}
		else if ($membreId == 135) // etude automobile techno
		{
			$PBX_IDENTIFIANT = '337460632';
			$PBX_SITE = '0932223';
		}
		else if ($membreId == 107) // automobile club ardeche
		{
			$PBX_IDENTIFIANT = '719220631';
			$PBX_SITE = '5132513';
		}
		else if ($membreId == 106) // automobile club vauluse
		{
			$PBX_IDENTIFIANT = '719220631';
			$PBX_SITE = '5132513';
		}
		else if ($membreId == 181) // permis à tous
		{
			$PBX_IDENTIFIANT = '527800087';
			$PBX_SITE = '8700811';
		}
		else if ($membreId == 176) // cours sylvan
		{
			$PBX_IDENTIFIANT = '527853529';
			$PBX_SITE = '1218014';
		}
		else if ($membreId == 205) // aide a l'action points permis
		{
			$PBX_IDENTIFIANT = '316977947';
			$PBX_SITE = '0852614';
		}
		else if ($membreId == 114) // asr formation
		{
			$PBX_IDENTIFIANT = '518032349';
			$PBX_SITE = '0899781';
		}
		else if ($membreId == 228) // edifice
		{
			$PBX_IDENTIFIANT = '200892523';
			$PBX_SITE = '2004278';
		}
		else if ($membreId == 193) // Perfect permis
		{
			$PBX_IDENTIFIANT = '529168618';
			$PBX_SITE = '6246437';
		}
		else if ($membreId == 98) // Adipser
		{
			$PBX_IDENTIFIANT = '373979127';
			$PBX_SITE = '1035502';
		}
		else if ($membreId == 287) // SOS Permis
		{
            /* OLD */
//			$PBX_IDENTIFIANT = '716352318';
//			$PBX_SITE = '4513307';
//            $PBX_RANG = '10';
			$PBX_IDENTIFIANT = '533547705';
			$PBX_SITE = '6605480';
		}
		else if ($membreId == 313) // Recup-points60
		{
			$PBX_IDENTIFIANT = '382945168';
			$PBX_SITE = '1062295';
		}
		else if ($membreId == 185) // automobile club savoie
		{
			$PBX_IDENTIFIANT = '533912697';
			$PBX_SITE = '5436176';
            $PBX_RANG = '16';
		}
		else if ($membreId == 335) // ECSR
		{
			$PBX_IDENTIFIANT = '534952984';
			$PBX_SITE = '5209152';
		}
		else if ($membreId == 328) // Taslite automobiles
		{
			$PBX_IDENTIFIANT = '534964976';
			$PBX_SITE = '0380370';
		}
		else if ($membreId == 305) // CFR D'Alzon
		{
			$PBX_IDENTIFIANT = '217150526';
			$PBX_SITE = '1137371';
		}
		else if ($membreId == 262) // CER Cerov
		{
			$PBX_IDENTIFIANT = '532193664';
			$PBX_SITE = '0585407';
		}
		else if ($membreId == 384) // MARIETTON
		{
			$PBX_IDENTIFIANT = '3';
			$PBX_SITE = '0657691';
		}
		else if ($membreId == 263) // VAUBAN FORMATIONS
		{
			$PBX_IDENTIFIANT = '221762459';
			$PBX_SITE = '1135735';
		}
		else if ($membreId == 234) // CER GENNIGES
		{
			$PBX_IDENTIFIANT = '528207577';
			$PBX_SITE = '6530957';
		}
		else if ($membreId == 84) // EC 40
		{
			$PBX_IDENTIFIANT = '224732652';
			$PBX_SITE = '0895665';
            $PBX_RANG = '02';
		}
		else if ($membreId == 189) // IFECC
		{
			$PBX_IDENTIFIANT = '994648073';
			$PBX_SITE = '1162993';
		}
		else if ($membreId == 197) // CER VOGELGESANG
		{
			$PBX_IDENTIFIANT = '534215454';
			$PBX_SITE = '4149468';
		}
		else if ($membreId == 203) // ASSO TEMPS DE PAROLE
		{
			$PBX_IDENTIFIANT = '226722924';
			$PBX_SITE = '1166055';
		}
		else if ($membreId == 330) // CONFORIS
		{
			$PBX_IDENTIFIANT = '1165823';
			$PBX_SITE = '1165823';
		}
        

        /* PARAMETRES DE TEST */
        /*
			$PBX_SITE = '1999888';
            $PBX_RANG = '99';
         */

//                1035502
//                752772897


		$PBX_MODE = '1';
		$PBX_LANGUE = 'FRA'; // FRA French
		$PBX_TOTAL = $prix*100;
		$PBX_DEVISE = '978';
		$PBX_CMD = $id_transaction;
		$PBX_PORTEUR = $email;
		$PBX_RETOUR = "auto:A;id:R";
		$PBX_REPONDRE_A  = "http://www.prostagespermis.fr/pbx_repondre_a.php";
		$PBX_EFFECTUE    = "http://www.prostagespermis.fr/merci_pbx.php";
		$PBX_REFUSE      = "http://www.prostagespermis.fr/regret.php";
//		$PBX_ANNULE      = "http://www.prostagespermis.fr/regret.php?ref=".$id_transaction;
		$PBX_ANNULE      = "http://www.prostagespermis.fr/regret.php";
		$PBX_ERREUR      = "http://www.prostagespermis.fr/regret.php";

		//bug non compris pour centre 107: la transaction s epasse bien mais echoue a la fin et donc stagiaire supprimer si
		//on utilise $PBX_ANNULE avec le parametre
		if ($membreId == 107 || $membreId == 106)
		{
			$PBX_ANNULE      = "http://www.prostagespermis.fr/regret.php";
		}

		// Contruction des champs du formulaire Â« nom=valeur Â» ou nom est le name de lâ??input et value la valeur de lâ??inputâ?¦
		$PBX = "PBX_MODE=$PBX_MODE&PBX_LANGUE=$PBX_LANGUE&PBX_SITE=$PBX_SITE&PBX_RANG=$PBX_RANG&PBX_IDENTIFIANT=$PBX_IDENTIFIANT&PBX_TOTAL=$PBX_TOTAL&PBX_DEVISE=$PBX_DEVISE&PBX_CMD=$PBX_CMD&PBX_PORTEUR=$PBX_PORTEUR&PBX_REPONDRE_A=$PBX_REPONDRE_A&PBX_EFFECTUE=$PBX_EFFECTUE&PBX_REFUSE=$PBX_REFUSE&PBX_ANNULE=$PBX_ANNULE&PBX_RETOUR=$PBX_RETOUR";


//		if ($membreId == 323) // assocation eco points
//		{
//            $PBX .= "&PBX_HASH=SHA512";
//
//            $keyTest="CCC58CBBE72174E6E189E75664C2BB970A264DA9D04A71AD0CAF8C1E38C6C452B3A6E1E8AC3B1626160A54471DD1D21133EC7C518CA46250868972D4CE9AF7B5";
//            $binKey=pack("H*",$keyTest);
//            $hmac=strtoupper(hash_hmac('sha512',$PBX,$binKey));
//
//            $PBX .= "&PBX_HMAC=$hmac";
//        }

		// Emplacement du cgi sur le serveur
		$MOD = "http://www.prostagespermis.fr/cgi-bin/modulev3.cgi";

		//Initialisation du curl
		$ch = curl_init();

		// DÃ©finition de l action du formulaire
		curl_setopt( $ch, CURLOPT_URL, $MOD );

		// EntÃªte Ã  0
		curl_setopt( $ch, CURLOPT_HEADER, 0 );

		// Autorisation des redirections en retour
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );

		// Le formulaire est envoyÃ© avec la mÃ©thode post
		curl_setopt( $ch, CURLOPT_POST, 1 );

		// Initialisation des champs et de leur valeur du formulaire
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $PBX );

		//Envoi du formulaire
		$data = curl_exec( $ch );

		//Fermeture de la ressource curl
		curl_close( $ch );

		//Affichage du rÃ©sultat

                echo '<head>
                            <meta name="robots" content="noindex,nofollow" />
                            <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
                    </head><body>'.$data;

		exit;

	}


	//NOUVELLE VERSION PAYBOX
	//-------------------
	if (isset($tab['radio_type_paiement']) && $tab['radio_type_paiement'] == 1 &&
		($membreId == 323))
	{

		$PBX_RANG = '01';


//		$PBX_RETOUR = "Mt:M;Ref:R;Auto:A;Erreur:E";
		$PBX_RETOUR = "auto:A;id:R";
		$PBX_REPONDRE_A  = "http://www.prostagespermis.fr/pbx_repondre_a.php";
		$PBX_EFFECTUE    = "http://www.prostagespermis.fr/merci_pbx.php";
		$PBX_REFUSE      = "http://www.prostagespermis.fr/regret.php";
//		$PBX_ANNULE      = "http://www.prostagespermis.fr/regret.php?ref=".$id_transaction;
		$PBX_ANNULE      = "http://www.prostagespermis.fr/regret.php";
		$PBX_ERREUR      = "http://www.prostagespermis.fr/regret.php";

        if ($membreId == 323) // assocation eco points
		{
			$PBX_IDENTIFIANT = '528916425';
			$PBX_SITE = '5135979';
		}

        $serveurs=array('tpeweb.paybox.com','tpeweb1.paybox.com');
        $serveurOK="";
        foreach($serveurs as $serveur)
        {
            $doc=new DOMDocument();
            $doc->loadHTMLFile('https://'.$serveur.'/load.html');
            $server_status="";
            $element=$doc->getElementById('server_status');
            if ($element)
                $server_status=$element->textContent;

            if ($server_status=="OK")
            {
                $serveurOK=$serveur;
                Break;
            }
        }

        if (!$serveurOK)
            die("Erreur : Aucun de serveur de paiement PayBox disponible");

        $dateTime=date("c");
        $msg="PBX_SITE=$PBX_SITE".
        "&PBX_RANG=$PBX_RANG".
        "&PBX_IDENTIFIANT=$PBX_IDENTIFIANT".
        "&PBX_TOTAL=".($prix*100).				// Prix TTC en ?
        "&PBX_DEVISE=978".							//Production
        "&PBX_CMD=".$id_transaction.						// Référence de la commande
        "&PBX_PORTEUR=".$email.						//EMail du client
        "&PBX_RETOUR=auto:A;id:R".
        "&PBX_HASH=SHA512".
        "&PBX_TIME=".$dateTime.
        "&PBX_REPONDRE_A=$PBX_REPONDRE_A&PBX_EFFECTUE=$PBX_EFFECTUE&PBX_REFUSE=$PBX_REFUSE&PBX_ANNULE=$PBX_ANNULE";


        $keyTest = "CCC58CBBE72174E6E189E75664C2BB970A264DA9D04A71AD0CAF8C1E38C6C452B3A6E1E8AC3B1626160A54471DD1D21133EC7C518CA46250868972D4CE9AF7B5";

        $binKey = pack("H*",$keyTest);
        $hmac = strtoupper(hash_hmac('sha512',$msg,$binKey));

        echo '<form method="POST" action="https://'.$serveurOK.'/cgi/MYchoix_pagepaiement.cgi" name="form">
                <input type="hidden" name="PBX_SITE" value="'.$PBX_SITE.'" />
                <input type="hidden" name="PBX_RANG" value="'.$PBX_RANG.'" />
                <input type="hidden" name="PBX_IDENTIFIANT" value="'.$PBX_IDENTIFIANT.'" />
                <input type="hidden" name="PBX_TOTAL" value="'.($prix*100).'" />
                <input type="hidden" name="PBX_DEVISE" value="978" />
                <input type="hidden" name="PBX_CMD" value="'.$id_transaction.'" />
                <input type="hidden" name="PBX_PORTEUR" value="'.$email.'" />
                <input type="hidden" name="PBX_RETOUR" value="'.$PBX_RETOUR.'" />
                <input type="hidden" name="PBX_HASH" value="SHA512" />
                <input type="hidden" name="PBX_TIME" value="'.$dateTime.'" />
                <input type="hidden" name="PBX_HMAC" value="'.$hmac.'" />
                <input type="hidden" name="PBX_REPONDRE_A" value="'.$PBX_REPONDRE_A.'" />
                <input type="hidden" name="PBX_EFFECTUE" value="'.$PBX_EFFECTUE.'" />
                <input type="hidden" name="PBX_REFUSE" value="'.$PBX_REFUSE.'" />
                <input type="hidden" name="PBX_ANNULE" value="'.$PBX_ANNULE.'" />
            </form>
            <script>
                document.form.submit();
            </script>';
        exit();
    }


	//Cas paiement par CB securoute
	//-----------------------------
	if (isset($tab['radio_type_paiement']) && $tab['radio_type_paiement'] == 1 && $membreId == 44)
	{
		$PBX_MODE        = '4'; // appel en ligne de commande
		$PBX_LANGUE      = 'FRA'; // FRA French
		$PBX_SITE        = '5132338';
		$PBX_RANG        = '01';
		$PBX_IDENTIFIANT = '715269714';
		$PBX_TOTAL       = $prix*100;
		$PBX_DEVISE      = '978'; // monaie = euros
		$PBX_CMD         = $id_transaction;
		$PBX_PORTEUR     = $email;
		$PBX_RETOUR      = "tarif:M;ref:R;auto:A;trans:T;erreur:E";
		$PBX_REPONDRE_A = "http://www.prostagespermis.fr/response_cb_securoute.php";
		$PBX_EFFECTUE    = "http://www.prostagespermis.fr/merci.php";
		$PBX_REFUSE      = "http://www.prostagespermis.fr/regret.php";
		$PBX_ANNULE      = "http://www.prostagespermis.fr/regret.php";

		$PBX = "PBX_MODE=$PBX_MODE PBX_SITE=$PBX_SITE PBX_RANG=$PBX_RANG PBX_IDENTIFIANT=$PBX_IDENTIFIANT PBX_TOTAL=$PBX_TOTAL PBX_DEVISE=$PBX_DEVISE PBX_CMD=$PBX_CMD PBX_PORTEUR=$PBX_PORTEUR PBX_RETOUR=$PBX_RETOUR PBX_EFFECTUE=$PBX_EFFECTUE PBX_REFUSE=$PBX_REFUSE PBX_ANNULE=$PBX_ANNULE PBX_REPONDRE_A=$PBX_REPONDRE_A PBX_OUTPUT=D";

		$post_data = "$PBX";

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "http://www.prostagespermis.securoute.net/module_paybox.php");
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$page = curl_exec($curl);
		curl_close($curl);

		echo "<HTML>";
		echo "<HEAD>";
		echo "<TITLE>PAYBOX</TITLE></HEAD>";
		echo "<BODY onload='document.PAYBOX.submit();'><CENTER>";
		echo "<b><br> connexion en cours <br> sur le serveur de paiement sÃ©curisÃ©... </b>";
		echo "<Form name=PAYBOX Action='https://tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi' Method=POST>";
		echo "<INPUT TYPE=hidden NAME=PBX_DATA VALUE=$page>";
		echo "<INPUT TYPE=hidden name=PBX_ANNULE value='http://www.prostagespermis.fr/regret.php'>";
		echo "<INPUT TYPE=hidden name=PBX_EFFECTUE value='http://www.prostagespermis.fr/merci.php'>";
		echo "<INPUT TYPE=hidden name=PBX_REFUSE value='http://www.prostagespermis.fr/regret.php'>";
		echo "<INPUT TYPE=hidden name=PBX_SOURCE value='HTML'>";
		echo "<INPUT TYPE=hidden name=PBX_VERSION value='211-OS INCONNU'>";
		echo "<INPUT TYPE=SUBMIT VALUE='PAYBOX'>";
		echo "</FORM>";
		echo "</CENTER></BODY>";
		echo "</HTML>";

		exit;
	}


	if ($membreId == 65) //allopermis
	{
		require_once("/home/prostage/soap/allopermis/inscription_allopermis.php");
		$ret = inscriptionAllopermis($id_stagiaire_tmp);

		if ($ret == 0)
		{
			echo "<font color='red'><b>Erreur d'inscription: vous êtes probablement déjà  inscrit à  l'un de nos stages</b></font>";
			exit;
		}

		if (isset($tab['radio_type_paiement']) && $tab['radio_type_paiement'] == 1 && $ret!=0)
		{
			//$adresseAP = "http://test.allopermis.com/stages/inscription-cb-diffuseur.php?diffuseur=prostages&siid=".$ret."&idt=".$id_transaction;
			$adresseAP = "http://www.allopermis.com/stages/inscription-cb-diffuseur.php?diffuseur=prostages&siid=".$ret."&idt=".$id_transaction;


                        echo '<head>
                                    <meta name="robots" content="noindex,nofollow" />
                                    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
                            </head><body>';
			echo "<form id='cb_allopermis' name='cb_allopermis' action='$adresseAP' method=POST>";
			echo "</form>";
			?>


			<script>
			document.forms.cb_allopermis.submit();
			</script>
                        </body>
                        </html>

			<?php
			//header("Location: '$adresseAP'");
			//include("'$adresseAP'");
			exit;
		}
	}

	//requete stage en doublon pour éviter une erreur possible

//    $query_stage = "SELECT  stage.*,
//                            site.nom,
//                            site.ville,
//                            site.adresse,
//                            site.code_postal,
//                            membre.boost_possible
//                    FROM    stage inner join membre on stage.id_membre = membre.id, site
//                    WHERE   stage.id = $stageId AND
//                            stage.id_site = site.id AND
//                            stage.id_membre = $membreId";
//
//	$rsStage = mysql_query($query_stage, $stageconnect) or die(mysql_error());
//	$row_stage = mysql_fetch_assoc($rsStage);
//	$totalRows_stage = mysql_num_rows($rsStage);

	//update stage
	$nb = $row_stage['nb_preinscrits']+1;

	$nb2 = $row_stage['nb_places_allouees']-1;

    /*
     * NON APPLIQUE EN PAIEMENT PAR CHEQUE --> DOIT ETRE IMPOSSIBLE DE VALIDER LE PAIEMENT CB BOOST APRES COUP
     *
        $nb_boost = $row_stage['nb_boost'];
        $boost_actif = $row_stage['boost_actif'];

        try {

            $nb_boost = (int) $row_stage['nb_boost'];
            $nb_boost_allouees = (int) $row_stage['nb_boost_allouees'];

            if (!empty($row_stage['boost_possible']) && $row_stage['boost_possible'] == 1 && !empty($row_stage['boost_actif']) && !empty($row_stage['prix_boost']) && !empty($nb_boost_allouees)) {
                // TODO : vérifier que le boost est toujours valable
                $boost_actif = 1;
                $nb_boost++;
                $nb_boost_allouees--;
                $row_stage['prix'] = $row_stage['prix_boost'];
            }
            if ($nb_boost_allouees < 1)
                $boost_actif = 0;

        } catch (Exception $exc) {
        }
    */

    /*
     * LA REQUETE EST FAITE APRES L'ENVOIE D'EMAIL
     *
     */

//    $sql = "UPDATE stage SET nb_preinscrits = $nb, boost_actif = $boost_actif, nb_places_allouees = $nb2, nb_boost_allouees = $nb_boost_allouees, nb_boost = $nb_boost WHERE stage.id = $stageId";
	$sql = "UPDATE stage SET nb_preinscrits = $nb, nb_places_allouees = $nb2, taux_remplissage = taux_remplissage +1 WHERE stage.id = $stageId";


    $row_stage['debut_am'] = str_replace(':', 'h', $row_stage['debut_am']);
    $row_stage['fin_am'] = str_replace(':', 'h', $row_stage['fin_am']);
    $row_stage['debut_pm'] = str_replace(':', 'h', $row_stage['debut_pm']);
    $row_stage['fin_pm'] = str_replace(':', 'h', $row_stage['fin_pm']);


	//FICHE PRE-INSCRIPTION
	//---------------------

        echo '<html>
            <head>
                    <meta name="robots" content="noindex,nofollow" />
                    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
            </head>
            <body>';
	echo "<table width='650px' style='font-size:14px'>";
	echo "<tr>";
		echo "<td><img src='images/logo.jpg' width='190' height='80' align='left'/></td>";
		echo "<td align='right'><strong>Id (".$row_stagiaire['id'].") ".$row_stagiaire['civilite']." ".
				$row_stagiaire['nom']." ".$row_stagiaire['prenom']." (Cas ".$row_stagiaire['cas'].")<br>";
		echo $row_stagiaire['tel']."   ".$row_stagiaire['email'];
		echo"<br>";
		echo "Permis: ".$row_stagiaire['num_permis'];echo "<br>";
		echo MySQLDateToExplicitDate($row_stage['date1'])." (".$row_stage['prix']." euros)"; echo "<br>";
		echo $row_stage['nom']." ".$row_stage['ville'];
		echo "</strong></td>";
	echo "</tr>";
	echo "</table>";
	echo "<br>";

	echo "<strong>FICHE DE PRE-INSCRIPTION A RETOURNER A L'ADRESSE DU CENTRE ORGANISATEUR</strong>";
	echo "<table width='650px' style='font-size:14px'>";
	echo "<tr>";
	echo "<td width='350px'><fieldset>";
	echo "<legend><strong>Le stage:</strong></legend>";
		echo "<table>";
		echo "<tr>";
			echo "<td>Dates:</td>";
			echo "<td>".MySQLDateToExplicitDate($row_stage['date1'])."<br>";
			echo MySQLDateToExplicitDate($row_stage['date2'])."</td>";
		echo "</tr>";

		echo "<tr>";
			echo "<td>Lieu:</td>";
			echo "<td>".$row_stage['nom']."<br>";
			echo $row_stage['adresse']."<br>";
			echo sprintf("%05d", $row_stage['code_postal'])." ".html_entity_decode($row_stage['ville'])."</td>";
		echo "</tr>";

		echo "<tr>";
			echo "<td>Horaires:</td>";
			echo "<td>".$row_stage['debut_am']." ".$row_stage['fin_am']." et ".
				$row_stage['debut_pm']." ".$row_stage['fin_pm']."</td>";
		echo "</tr>";

		echo "<tr>";
			echo "<td>Prix:</td>";
			echo "<td>".$row_stage['prix']." euros </td>";
		echo "</tr>";
		echo "</table>";
	echo "</fieldset></td>";

	echo "<td>";
	echo "<fieldset><legend><strong>Le centre organisateur:</strong></legend>";
	echo "<table>";
	echo "<tr>";
		echo "<td>Nom:</td>";
		echo "<td>".$row_membre['nom']."</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>Adresse: </td>";
	echo "<td>".$row_membre['adresse']."</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>Tel:</td>";
	echo "<td>".$row_membre['tel']."  ".$row_membre['mobile']."   Fax :".$row_membre['fax']."</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>Email: </td>";

        $membre_email = $row_membre['email'];

        if (in_array($row_membre['id'], array(269, 270, 271, 272, 273, 274, 275, 276, 277))) {// A.N.P.E.R

            $arr_email = preg_split("/(,|;)/",$membre_email);

            if (!empty($arr_email))
                $membre_email = $arr_email[0];
        }

	echo "<td>".$membre_email."</td>";
	echo "</tr>";
	echo "</table></fieldset></td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td colspan='2'>";
	echo "<fieldset><legend><strong>Le stagiaire:</strong></legend>";
	echo "<table width='650px'>";
	echo "<tr>";
		echo "<td width='30%'>Nom Prenom:</td>";
		echo "<td>".$row_stagiaire['civilite']." ".stripslashes($row_stagiaire['nom']." ".
				$row_stagiaire['prenom'])." (pre-inscrit le ".dateFr($dateLocal).")</td>";
	echo "</tr>";

	if ($row_stagiaire['jeune_fille'] != "")
	{
		echo "<tr>";
		echo "<td>Nom jeune fille:</td>";
		echo "<td>".$row_stagiaire['jeune_fille']."</td>";
		echo "</tr>";
	}

	echo "<tr>";
	echo "<td>Ne(e) le:</td>";
	echo "<td>".datefr($row_stagiaire['date_naissance'])." a ".stripslashes($row_stagiaire['lieu_naissance'])."</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>Adresse:</td>";
	echo "<td>".stripslashes($row_stagiaire['adresse'])."</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td> </td>";
	echo "<td>".$row_stagiaire['code_postal']." ".stripslashes($row_stagiaire['ville'])."</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>Telephone:</td>";
	echo "<td>".$row_stagiaire['tel']."    ".$row_stagiaire['mobile']."</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>Email:</td>";
	echo "<td>".$row_stagiaire['email']."</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>Permis:</td>";
	echo "<td>".$row_stagiaire['num_permis']." le ".datefr($row_stagiaire['date_permis']).
			" (".stripslashes($row_stagiaire['lieu_permis']).")</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>Type de stage:</td>";
	echo "<td>Cas ".$row_stagiaire['cas']."</td>";
	echo "</tr>";

	if (($row_membre['id'] == 64 || $row_membre['id'] == 38) && $row_stagiaire['cas'] == 2)
	{
		echo "<tr>";
		echo "<td>Infraction:</td>";
		echo "<td>".$row_stagiaire['motif_infraction']." ".$row_stagiaire['date_infraction']."</td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td>Reception lettre 48N:</td>";
		echo "<td>".$row_stagiaire['date_lettre']."</td>";
		echo "</tr>";
	}

	echo "<tr>";
	echo "<td colspan='2'><div align='justify'><br>";

    
    if ($row_membre['id'] == 155 || $row_membre['id'] == 389) {
        echo "Consultez votre relevé intégral d'information pour connaître votre solde de points.<br /><br />";
    }
    
    echo "Merci d'envoyer sous ";

    if ($row_membre['id'] == 294)
        echo "2 jours ";
    else
        echo "4 jours ";

    echo "votre fiche de pre-inscription par courrier
en imprimant directement cette page ou l'email qui vous sera envoye dans quelques instants a l'adresse du centre organisateur. N'oubliez pas de signer la fiche et d'y joindre imperativement les pieces suivantes: </div>";

	echo "<ul>";
    
    if ($row_membre['id'] == 155)
        echo "<li>Photocopie de<strong> l'interieur de votre permis</strong> (cote avec votre photo) ou, en cas de suspension la notification,</li>";
    else
        echo "<li>Photocopie <strong> recto-verso de votre permis</strong> ou, en cas de suspension la notification,</li>";
    
    
    echo "<li>Un cheque de <strong>".$row_stage['prix']."</strong> euros a l'ordre de <strong>";

    if (in_array($row_membre['id'], array(269, 270, 271, 272, 273, 274, 275, 276, 277))) // A.N.P.E.R
        echo 'ANPER';
    else if (in_array($row_membre['id'], array(299, 298, 300, 302))) // Promotrans
        echo 'Promotrans';
    else
        echo $row_membre['nom'];

    echo "</strong></li>";
    
    if ($row_membre['id'] == 145 || $row_membre['id'] == 130 || $row_membre['id'] == 155 || $row_membre['id'] == 169 || $row_membre['id'] == 228 || $row_membre['id'] == 294 || $row_membre['id'] == 260 || $row_membre['id'] == 359 ||
            ($row_membre['id'] == 64
            && in_array($row_stage['departement'], array(2,6,10,14,28,33,34,35,38,47,50,51,54,56,58,67,74,76,77,78,80,89,91,92,93,94,95))
            )
    )
    {
        echo "<li>Une enveloppe timbrée et libellee a votre nom</li>";
    }
    else if ($row_membre['id'] == 229) {
        echo "<li>2 Enveloppes timbrées</li>";
    }

    
    if ($row_membre['id'] == 196) {
		echo "<li>Le courrier du stage obligatoire en cas de condamnation pénale</li>";
    }
    
    echo "<li>ATTENTION JEUNES CONDUCTEURS : si <strong>48 N</strong>, nous envoyer la photocopie(recto/verso)</li>";
	echo "</ul>";
	echo "<br>";
    
	echo "Retournez votre dossier complet par courrier";
	if (strtotime($row_stage['date1']) - strtotime("now") < 518400){echo " sous 48H ";}
	else {
        if ($row_membre['id'] == 294)
            echo " sous 2 jours ";
        else
            echo " sous 4 jours ";
    }

	echo "a:<br><br>";
	echo "<div align='center'>";
		echo $row_membre['nom'];
		echo "<br>";
		echo $row_membre['adresse'];
	echo "</div>";

	echo "<div align='justify'>";
	echo "<br><br>";

	if ($row_membre['id'] == 64)
	{
		echo "Au cas un stagiaire déciderait de ne plus participer à une action, et notifierait son désistement
		A la société moins de 4 jours ouvrables avant le début du stage :";
		echo "<ul>";
		echo "<li> une somme de 60,00 euros sera conservée pour les frais administratifs (ou réclamée si le stage n'a pas été réglé).</li>";
		echo "<li> ATTENTION ! Pour une annulation ou un report la veille de la date du premier jour de stage,
		la somme complète du stage sera conservée (ou réclamée si le stage n'a pas été réglé).</li>";
		echo "<li> tout désistement durant le stage ne fera l'objet d'aucun remboursement. Absence non signalée le jour
		du stage ou abandon par le stagiaire en cours de formation, le montant total du règlement est conservé par la société.</li>";
		echo "</ul>";
	}

	if ($row_membre['id'] == 44)
	{
		echo "<em>Rappel des Articles de vente 1 et 6:</em><br />
		<em>Article 1 : Capital points</em><br />
		Afin d'effectuer un stage de Securite Routiere cas 1 (recuperation de 4 points), le capital points du permis de conduire doit etre au moins egal a 1 point et inferieur ou egal a 8 points. Dans le cas ou votre solde de points est nul mais que vous n'avez pas receptionne de lettre 48S, Il est obligatoire, avant de vous inscrire, de faire valider votre releve integral d'information par un conseiller permis. Si le conducteur n'a jamais recu de lettre (type 48, 48M ou 48N), il doit demander un releve integral d'information dans une Prefecture ou Sous-Prefecture. En cas de fausse declaration la responsabilite de PROStagesPermis et de l'organisateur du stage ne pourra en aucun cas etre engagee et le remboursement du stage sera impossible.<br /><br />
		<em>Article 6 : Annulation d'une inscription</em><br />
		En cas d'absence (quelque en soit la cause) signalee entre 7 jours et 4 jours ouvrables avant le debut du stage, les frais administratifs occasionnes au centre organisateur seront factures 50 euros. Si l'absence est signalee 4 jours ouvrables avant le stage (quelque en soit la cause), le prix de la formation reste entierement du. Dans tous les cas de remboursement, il sera deduit des frais de traitement de 5,00 euros. La validation de la commande vaut acceptation de ces conditions d'annulation. Toute demande d'annulation devra etre faite par lettre recommandee au centre organisateur.";
	}

	if ($row_membre['id'] == 155)
	{
		echo "<em>Rappel de l'article 1 :</em><br />
		<em>Article 1 : Capital points</em><br />
		Afin d'effectuer un stage de Securite Routiere cas 1 (recuperation de 4 points), le capital points du permis de conduire doit etre au moins egal a 1 point et inferieur ou egal a 8 points. Dans le cas ou votre solde de points est nul mais que vous n'avez pas receptionne de lettre 48S, Il est obligatoire, avant de vous inscrire, de faire valider votre releve integral d'information par un conseiller permis. Si le conducteur n'a jamais recu de lettre (type 48, 48M ou 48N), il doit demander un releve integral d'information dans une Prefecture ou Sous-Prefecture. En cas de fausse declaration la responsabilite de PROStagesPermis et de l'organisateur du stage ne pourra en aucun cas etre engagee et le remboursement du stage sera impossible.";
	}
    
	if ($row_membre['id'] == 196)
	{
		echo "<p style=\"font-weight:bold;font-style:italic;color:red;\">Dès lecture de ce message, contactez le centre organisateur au ".$row_membre['tel']." / ".$row_membre['mobile']." pour le paiement, sous peine de non validation de votre place.</p>";
	}

	echo "<br><br>";
	echo "BON POUR ACCORD. Date et Signature:";
	echo "<br><br>";
	echo "</div></td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td><input type='button' value='Cliquez ici pour imprimer votre fiche'
		onclick='javascript: window.print()'/></td>";

    $lien_cb = 'http://www.prostagespermis.fr/lien_cb.php?t='.$id_transaction.'&c='.$lien_cb_code_md5;

    echo "<td align='right'>";

    if (!empty($row_membre['cb_actif'])) {
        echo "  <a href=\"$lien_cb\" style=\"font:14px arial; background-color: #005FE1;color: white;border: 2px solid #0300FF; text-decoration:none;padding:2px 4px\">Valider mon inscription par carte bancaire</a>
                &nbsp;";
    }

    echo "  <input type='button' value='Retour accueil' onclick=\"window.location='/'\"/>
        </td>";

	echo "</tr>";
	echo "</table>";
	echo "</fieldset>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";

    if ($row_membre['id'] == 188) {
        print_r($_SESSION);
    }

    if (isset($tab['paiement_par_cb'])) {
//        echo 'test_mode';
        sendEmail($id_transaction, $site, 0, 1, $provenance);
    }
    else
        sendEmail($id_transaction, $site, 0, 0, $provenance);

    // Faut faire la requete apres l'envoie d'email
	mysql_query($sql, $stageconnect) or die('Erreur SQL !'.$sql.'<br />'.mysql_error());
}

function utf8_encode_deep(&$input) {
    if (is_string($input)) {
        $input = utf8_encode($input);
    } else if (is_array($input)) {
        foreach ($input as &$value) {
            utf8_encode_deep($value);
        }

        unset($value);
    } else if (is_object($input)) {
        $vars = array_keys(get_object_vars($input));

        foreach ($vars as $var) {
            utf8_encode_deep($input->$var);
        }
    }
}

function sendEmail($transactionID, $site, $lien_cb=0, $test_mode = 0, $provenance_pubs = 0)
{
	$dateLocal = date("y-m-d");
	$aujourdui = date("d-m-y");

	require ("../connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	//retrouver la transaction
	$query_transaction = sprintf("SELECT transaction.* FROM transaction WHERE
										transaction.id = $transactionID");

	$rsTransaction = mysql_query($query_transaction, $stageconnect) or die(mysql_error());
	$row_transaction = mysql_fetch_assoc($rsTransaction);
	$totalRows_transaction = mysql_num_rows($rsTransaction);

	if ($totalRows_transaction != 1)
	{
		echo "Erreur: transaction non trouvée";
		exit;
	}
	else
	{
		$stagiaire_ID = $row_transaction['id_stagiaire'];
		$stageID = $row_transaction['id_stage'];
		$membreID = $row_transaction['id_membre'];
	}

	//requete stagiaire
	$query_stagiaire = sprintf("SELECT stagiaire.* FROM stagiaire WHERE
										stagiaire.id = $stagiaire_ID");

	$rsStagiaire = mysql_query($query_stagiaire, $stageconnect) or die(mysql_error());
	$row_stagiaire = mysql_fetch_assoc($rsStagiaire);

	$totalRows_stagiaire = mysql_num_rows($rsStagiaire);

	//requete stage

//	$query_stage = sprintf("SELECT  stage.*,
//                                        site.nom,
//                                        site.ville,
//                                        site.adresse,
//                                        site.code_postal FROM stage, site
//                                WHERE 	stage.id = $stageID AND
//                                        stage.id_site = site.id AND
//                                        stage.id_membre = $membreID");
//
//

            $query_stage = "SELECT  stage.*,
                                    site.nom,
                                    site.ville,
                                    site.adresse,
                                    site.departement,
                                    site.code_postal
                            FROM    stage
                                        inner join membre on stage.id_membre = membre.id,
                                    site
                            WHERE   stage.id = $stageID AND
                                    stage.id_site = site.id AND
                                    stage.id_membre = $membreID";


	$rsStage = mysql_query($query_stage, $stageconnect) or die(mysql_error());
	$row_stage = mysql_fetch_assoc($rsStage);
	$totalRows_stage = mysql_num_rows($rsStage);

	//requete membre
	$membreID = $row_stage['id_membre'];
	$query_membre = sprintf("SELECT membre.* FROM membre
                                 WHERE membre.id = $membreID");
	$rsMembre = mysql_query($query_membre, $stageconnect) or die(mysql_error());
	$row_membre = mysql_fetch_assoc($rsMembre);
	$totalRows_membre = mysql_num_rows($rsMembre);

//	mysql_close($stageconnect);

        // TODO : à uploader !

        $is_boosted = false;
        $boost_entete = '';

        if ($row_stagiaire['option_reversement'] == '2') {
            $is_boosted = true;
            $boost_entete = 'BOOST ';
        }

        // Important, le prix dans l'email doit être celui de son inscription
        $row_stage['prix'] = $row_stagiaire['paiement'];
        

//	if ($membreID == 228 && $test_mode) {
        utf8_encode_deep($row_stagiaire);
        utf8_encode_deep($row_membre);
        utf8_encode_deep($row_stage);
//    }


    $row_stage['debut_am'] = str_replace(':', 'h', $row_stage['debut_am']);
    $row_stage['fin_am'] = str_replace(':', 'h', $row_stage['fin_am']);
    $row_stage['debut_pm'] = str_replace(':', 'h', $row_stage['debut_pm']);
    $row_stage['fin_pm'] = str_replace(':', 'h', $row_stage['fin_pm']);


	//ENVOI DU MAIL
	//-------------
	$to = $row_stagiaire['email'];
	$contact = "contact@prostagespermis.fr";
    $subject = '';
    $subject_pro = '';
    $subject_departement = '';

    if (in_array($row_membre['id'], array(269, 270, 271, 272, 273, 274, 275, 276, 277))) // A.N.P.E.R
    {
        $subject_departement = 'dep '.sprintf('%02d', $row_stage['departement']).' - ';
    }

	if ($row_stagiaire['status'] == "inscrit")
	{
		$subject = "Inscription : ".$row_stagiaire['nom']." ".$row_stagiaire['prenom'];
		$subject_pro = "$boost_entete Inscription: ".$subject_departement.$row_stagiaire['nom']." ".$row_stagiaire['prenom'];
	}
	else
	{
		$subject = "PRE-Inscription : ".$row_stagiaire['nom']." ".$row_stagiaire['prenom'];
		$subject_pro = "$boost_entete PRE-Inscription: ".$subject_departement.$row_stagiaire['nom']." ".$row_stagiaire['prenom'];
	}

	if ($lien_cb == 1)
	{
		$subject = "Validation paiement en ligne: ".$row_stagiaire['nom']." ".$row_stagiaire['prenom'];
		$subject_pro = "$boost_entete Validation paiement en ligne : ".$subject_departement.$row_stagiaire['nom']." ".$row_stagiaire['prenom'];
    }

	$subject = stripslashes($subject);
	$subject_pro = stripslashes($subject_pro);

	$msg  = "<b>FICHE INSCRIPTION PROStagesPermis (le ".$aujourdui.")</b>";$msg .= "<br>";

	if ($lien_cb == 1)
	{
		$msg  = "<b>FICHE PAIEMENT EN LIGNE PROStagesPermis (le ".$aujourdui.")</b>";$msg .= "<br>";
	}


//        if ($membreID == 188) {
//		$msg .= print_r($row_stage, true).'<br>';
//	}

	if ($row_membre['id'] != 64 && $row_membre['id'] != 38)
	{
			$msg .= "==================================================================";$msg .= "<br>";
			$msg .= "   Stagiaire: "."(Id ".$row_stagiaire['id'].") ".stripslashes($row_stagiaire['nom']." ".$row_stagiaire['prenom'])." (Cas ".$row_stagiaire['cas'].")";$msg .= "<br>";
			$msg .= "   Tel/Email: ".$row_stagiaire['tel']." ".$row_stagiaire['mobile']." ".$row_stagiaire['email'];$msg .= "<br>";
			$msg .= "   Permis: ".$row_stagiaire['num_permis'];$msg .= "<br>";
			$msg .= "   Stage: ".MySQLDateToExplicitDate($row_stage['date1'])." ".filter($row_stage['nom'])." ".filter($row_stage['ville'])." (".$row_stagiaire['paiement']." euros)";$msg .= "<br>";
			$msg .= "==================================================================";$msg .= "<br>";$msg .= "<br>";
	}
	else
	{
		$msg .= "<br>";
	}

	$msg .= "<u><b>CENTRE ORGANISATEUR:</b></u>";$msg .= "<br>";
	$msg .= "<table  width=\"100%\" style=\"max-width:800px\">";
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
	$msg .= "<td>";$msg .= "<em>Fax: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_membre['fax'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Email: </em>";$msg .= "</td>";

    $membre_email = $row_membre['email'];

    if (in_array($row_membre['id'], array(269, 270, 271, 272, 273, 274, 275, 276, 277))) { // A.N.P.E.R

        $arr_email = preg_split("/(,|;)/",$membre_email);

        if (!empty($arr_email))
            $membre_email = $arr_email[0];
    }

    $msg .= "<td>"; $msg .= $membre_email; $msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "</table>";

	$msg .= "<br><u><b>STAGIAIRE:</b></u>";$msg .= "<br>";
	$msg .= "<table  width=\"100%\" style=\"max-width:800px\">";
	$msg .= "<tr>";
	$msg .= "<td width=\"23%\">";$msg .= "<em>Identite: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['civilite']." ".stripslashes($row_stagiaire['nom']." ".$row_stagiaire['prenom']);$msg .= "</td>";
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
	$msg .= "<td>";$msg .= "Cas ".$row_stagiaire['cas'];$msg .= "</td>";
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

	$msg .= "<br><u><b>DETAILS DU STAGE:</b></u>";$msg .= "<br>";
	$msg .= "<table  width=\"100%\" style=\"max-width:800px\">";
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
	$prix = $row_stagiaire['paiement'];
	$prix .= " euros";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Prix: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $prix." (Mode paiement: <b>".$row_transaction['type_paiement']."</b>)";$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "</table>";
	$msg .= "<br>";
	$msg .= "<br>";

	$msg2 = $msg;

	$msg .= "<table  width=\"100%\" style=\"max-width:800px\">";
	$msg .= "<tr>";


    // Pour toute question relative au paiement de votre stage, merci de contacter directement [nom centre] au [numero centre]


    $lien_cb_code_md5 = md5($stageID .'!' . $stagiaire_ID .'!'.$membreID);
    $lien_cb_url = 'http://www.prostagespermis.fr/lien_cb.php?t='.$transactionID.'&c='.$lien_cb_code_md5;

//    if (!empty($test_mode) && empty($lien_cb) && $row_stagiaire['status'] != "inscrit" && !empty($row_membre['cb_actif']) && ($row_membre['id'] == 213 || $row_membre['id'] == 127 || $row_membre['id'] == 252 || $row_membre['id'] == 287 || $row_membre['id'] == 96 || $row_membre['id'] == 51)) {

    if (empty($lien_cb) && $row_stagiaire['status'] != "inscrit" && !empty($row_membre['cb_actif'])) {
        $msg .= "<td>";
        $msg .= "<fieldset>
                <legend style=\"color:red\">ATTENTION !</legend> <br />
                Votre place ne sera réservée qu'à réception du paiement par le centre organisateur, <strong>sous réserve de places disponibles</strong>.
                <br />Validez dès maintenant votre paiement par carte bancaire (paiement sécurisé) et <strong>réservez définitivement votre place !</strong>
                <br /><br /><a href=\"$lien_cb_url\" style=\"
                                background: #1B8AE4;
                                border: 1px solid #0052FF;
                                border-radius: 7px;
                                color: white;
                                font: 15px arial;
                                padding: 3px 6px;
                                text-decoration: none;\">PAYER PAR CARTE BANCAIRE</a>
                <br /><br />
                </fieldset>
                </td></tr><tr><td>
                <br /><br /><strong>SINON :</strong><br /><br />
                </td></tr>
                <tr>";
    }

	$msg .= "<td>";

    
    if ($row_membre['id'] == 155 || $row_membre['id'] == 389) {
		$msg .= "Consultez votre relevÃ© intÃ©gral d'information pour connaître votre solde de points.<br /><br />";
    }
    
	$msg .= "Merci de retourner sous ";

    if ($row_membre['id'] == 294)
        $msg .= "2 jours ";
    else
        $msg .= "4 jours ";

	$msg .= "par courrier votre fiche d'inscription (Ã©tablie depuis notre site internet) ou ce mail, de le dater, de le signer et d'y joindre impÃ©rativement les piÃ¨ces suivantes:";

    $msg .= "<ul>";
    
    
    if ($row_membre['id'] == 155)
        $msg .= "<li> La photocopie de l'interieur de votre permis de conduire (cote avec votre photo) ou, en cas de suspension, la notification,</li>";
    
    else
        $msg .= "<li> La photocopie recto-verso de votre permis de conduire ou, en cas de suspension, la notification,</li>";
    
    
	if ($row_stagiaire['status'] != "inscrit")
	{
		$msg .= "<li> Un cheque de ".$row_stagiaire['paiement']." euros a l'ordre de ";

        if (in_array($row_membre['id'], array(269, 270, 271, 272, 273, 274, 275, 276, 277))) // A.N.P.E.R
            $msg .= 'ANPER';
        else if (in_array($row_membre['id'], array(99, 298, 300, 302))) // A.N.P.E.R
            $msg .= 'Promotrans';
        else
            $msg .= stripslashes($row_membre['nom']);

        $msg .= "</li>";
	}
    
	if ($row_membre['id'] == 145 || $row_membre['id'] == 130 || $row_membre['id'] == 155 || $row_membre['id'] == 169 || $row_membre['id'] == 228 || $row_membre['id'] == 294 || $row_membre['id'] == 260 || $row_membre['id'] == 359 ||
                    ($row_membre['id'] == 64
                    && in_array($row_stage['departement'], array(2,6,10,14,28,33,34,35,38,47,50,51,54,56,58,67,74,76,77,78,80,89,91,92,93,94,95))
                    )
    )
	{
		$msg .= "<li>Enveloppe timbrÃ©e et libellÃ©e Ã  votre nom</li>";
	}
    else if ($row_membre['id'] == 229) {
		$msg .= "<li>2 Enveloppes timbrÃ©es</li>";
    }
    
    
    if ($row_membre['id'] == 196) {
		$msg .= "<li>Le courrier du stage obligatoire en cas de condamnation pÃ©nale</li>";
    }

	$msg .= "<li> Attention jeunes conducteurs: si 48 N, joindre la photocopie (recto/verso)";$msg .= "</li>";
	$msg .= "</ul>";
	$msg .= "<br>";
    
    $msg .= "<u><b>Retournez votre dossier complet a l'adresse suivante :</b></u>";$msg .= "<br>";
	$msg .= "     ".stripslashes($row_membre['nom']);$msg .= "<br>";
	$msg .= "     ".stripslashes($row_membre['adresse']);$msg .= "<br><br>";

	if ($row_stagiaire['status'] == "inscrit")
	{
		$msg .= "Votre inscription est d'ores et dÃ©jÃ  dÃ©finitive. Cependant, le centre a besoin de ces documents
		pour commencer les dÃ©marches auprÃ¨s de votre prÃ©fecture.";
		$msg .= "<br><br>";
	}

	if ($row_membre['id'] == 64)
	{
		//$msg .= " En cas d'annulation de votre part moins de 5 jours avant le premier jour du stage pour lequel vous avez reserve votre inscription, une somme de 60 euros sera conservee par acti-ROUTE pour frais de gestion de votre dossier.";$msg .= "<br><br>";

		$msg .= "Au cas oÃ¹ un stagiaire dÃ©ciderait de ne plus participer Ã  une action, et notifierait son dÃ©sistement
		Ã  la sociÃ©tÃ© moins de 4 jours ouvrables avant le dÃ©but du stage :";
		$msg .= "<ul>";
		$msg .= "<li> une somme de 60,00 euros sera conservÃ©e pour les frais administratifs (ou rÃ©clamÃ©e si le stage nâ??a pas Ã©tÃ© rÃ©glÃ©).</li>";
		$msg .= "<li> ATTENTION ! Pour une annulation ou un report la veille de la date du premier jour de stage,
		la somme complÃ¨te du stage sera conservÃ©e (ou rÃ©clamÃ©e si le stage nâ??a pas Ã©tÃ© rÃ©glÃ©).</li>";
		$msg .= "<li> tout dÃ©sistement durant le stage ne fera lâ??objet dâ??aucun remboursement. Absence non signalÃ©e le jour
		du stage ou abandon par le stagiaire en cours de formation, le montant total du rÃ¨glement est conservÃ© par la sociÃ©tÃ©.</li>";
		$msg .= "</ul>";
		$msg .= "<br><br>";
	}

	if ($row_membre['id'] == 44)
	{
		$msg .= "Rappel des Articles de vente 1 et 6:";$msg .= "<br>";
		$msg .= "Article 1 : Capital points:";$msg .= "<br>";
		$msg .= "Afin dâ??effectuer un stage de Â«SÃ©curitÃ© RoutiÃ¨reÂ» cas nÂ°1 (rÃ©cupÃ©ration de 4 points), le capital points du permis de conduire doit Ãªtre au moins Ã©gal Ã  1 point et infÃ©rieur ou Ã©gal Ã  8 points. Dans le cas oÃ¹ votre solde de points est nul mais que vous n'avez pas rÃ©ceptionnÃ© de lettre 48S, Il est obligatoire, avant de vous inscrire, de faire valider votre relevÃ© intÃ©gral d'information par un conseiller permis. Si le conducteur nâ??a jamais reÃ§u de lettre (type 48, 48M ou 48N), il doit demander un relevÃ© intÃ©gral dâ??information dans une PrÃ©fecture ou Sous-PrÃ©fecture. En cas de fausse dÃ©claration la responsabilitÃ© de PROStagesPermis et de lâ??organisateur du stage ne pourra en aucun cas Ãªtre engagÃ©e et le remboursement du stage sera impossible.";$msg .= "<br><br>";
		$msg .= "Article 6 : Annulation inscription:";$msg .= "<br>";
		$msg .= "En cas dâ??absence (quelque en soit la cause) signalÃ©e entre 7 jours et 4 jours ouvrables avant le dÃ©but du stage, les frais administratifs occasionnÃ©s au centre organisateur seront facturÃ©s 50â?¬. Si lâ??absence est signalÃ©e 4 jours ouvrables avant le stage (quelque en soit la cause), le prix de la formation reste entiÃ¨rement dÃ». Dans tous les cas de remboursement, il sera dÃ©duit des frais de traitement de 5,00â?¬. La validation de la commande vaut acceptation de ces conditions dâ??annulation. Toute demande dâ??annulation devra Ãªtre faite par lettre recommandÃ©e au centre organisateur.";
        $msg .= "<br><br>";
	}

	if ($row_membre['id'] == 155)
	{
		$msg .= "Rappel de l'article 1 :";$msg .= "<br>";
		$msg .= "Article 1 : Capital points:";$msg .= "<br>";
		$msg .= "Afin dâ??effectuer un stage de Â«SÃ©curitÃ© RoutiÃ¨reÂ» cas nÂ°1 (rÃ©cupÃ©ration de 4 points), le capital points du permis de conduire doit Ãªtre au moins Ã©gal Ã  1 point et infÃ©rieur ou Ã©gal Ã  8 points. Dans le cas oÃ¹ votre solde de points est nul mais que vous n'avez pas rÃ©ceptionnÃ© de lettre 48S, Il est obligatoire, avant de vous inscrire, de faire valider votre relevÃ© intÃ©gral d'information par un conseiller permis. Si le conducteur nâ??a jamais reÃ§u de lettre (type 48, 48M ou 48N), il doit demander un relevÃ© intÃ©gral dâ??information dans une PrÃ©fecture ou Sous-PrÃ©fecture. En cas de fausse dÃ©claration la responsabilitÃ© de PROStagesPermis et de lâ??organisateur du stage ne pourra en aucun cas Ãªtre engagÃ©e et le remboursement du stage sera impossible.";$msg .= "<br><br>";
        $msg .= "<br><br>";
	}

    if (empty($lien_cb) && $row_stagiaire['status'] != "inscrit" && !empty($row_membre['cb_actif'])) {
        $msg .= "<br /><fieldset><legend>IMPORTANT</legend>Si le stage a lieu dans moins de 5 jours, nous vous conseillons fortement de payer par carte bancaire.
                <br />Autrement, nous vous invitons a contacter le centre organisateur au ".$row_membre['tel']."  ".$row_membre['mobile']." pour définir avec eux les modalites de paiement.
                <br /><br />
                Pour toute autre question relative au reglement de votre stage, merci de contacter ".$row_membre['nom']." au ".$row_membre['tel']."  ".$row_membre['mobile'].".</fieldset><br /><br />";
    }
    
    
	if ($row_membre['id'] == 196)
	{
		$msg .= "<p style=\"font-weight:bold;font-style:italic;color:red;\">Des lecture de cet email, contactez le centre organisateur au ".$row_membre['tel']." / ".$row_membre['mobile']." pour le paiement, sous peine de non validation de votre place.</p>";
	}
    

    if (in_array($row_membre['id'], array(269, 270, 271, 272, 273, 274, 275, 276, 277))) // A.N.P.E.R
        $msg .= "J'ai lu et j'accepte les <a href='http://www.prostagespermis.fr/CGV_RI_2013.pdf'>conditions generales de vente et le reglement interieur du stage</a>.<br /><br />Date et signature:";
    else
        $msg .= "J'ai lu et j'accepte les <a href='http://www.prostagespermis.fr/conditions-generales3.php'>conditions generales de vente</a>.<br /><br />Date et signature:";

	$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "</table>";

	//$headers = "From: ".stripslashes($row_membre['nom'])."\n";
	$headers = "From: PROStagesPermis <contact@prostagespermis.fr>\n";
	$headers .= "Reply-To: ".$contact."\n";
	$headers .= 'MIME-version: 1.0'."\n";
	//$headers .= 'Content-type: text/html; charset= iso-8859-1'."\n";
	$headers .= 'Content-type: text/html; charset=utf-8'."\n";

	$provenance = getIndexUrl($site);
	$intitule_renvoi_email_secours = ' [RENVOI EMAIL] ';
	$intitule = $provenance[1];
        $adwords = '';

        if (!empty($provenance_pubs)) {

            if ($provenance_pubs == 7)
                $adwords = ' [ADWORDS] ';

            else if ($provenance_pubs == 8)
                $adwords = ' [ADWORDS TEL] ';

            else if ($provenance_pubs == 9)
                $adwords = ' [NEWSLETTER] ';
        }
        /*
	if ($row_membre['id'] == 65)
	{
		mail($contact, $intitule." ".$subject." - ".stripslashes($row_membre['nom']), $msg, $headers);
		sleep(1);
		mail("prostages@allopermis.com", $subject, $msg, $headers);
	}
	else
	{
		if ($row_membre['id'] == 44)
		{
			mail($row_membre['email'], $subject, $msg2, $headers);
		}
		else
		{
			mail($row_membre['email'], $subject, $msg, $headers);
		}
		sleep(1);
		mail($to, $subject, $msg, $headers);
		sleep(1);
		mail($contact, $intitule." ".$subject." - ".stripslashes($row_membre['nom']), $msg, $headers);
	}
        */

//        if (empty($test_mode)) {
            if ($row_membre['id'] == 65)
            {
                    // Envoi au centre
                    mail("prostages@allopermis.com", $subject_pro, $msg, $headers);
            }
            else
            {
                    // Envoi au centre
                    if ($row_membre['id'] == 44)
                    {
                            mail($row_membre['email'], $subject_pro, $msg2, $headers);
                    }
                    else
                    {
                            mail($row_membre['email'], $subject_pro, $msg, $headers);
                    }
                    sleep(1);

                    // Envoi au candidat
                    mail($to, $subject, $msg, $headers);
            }
            sleep(1);
//        }
        // Envoi à prostage
        $result_envoi = mail($contact, $adwords . $intitule." ".$subject_pro." - ".stripslashes($row_membre['nom']), $msg, $headers);

        if ($row_membre['id'] == 176 || $row_membre['id'] == 215 || $row_membre['id'] == 185)
        {
            mail($contact, $intitule_renvoi_email_secours .' '. $subject_pro, $msg, $headers);
        }
}

function getIp()
{
	$ip = $_SERVER['REMOTE_ADDR'];

	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {

		$ip = $_SERVER['HTTP_CLIENT_IP'];

	} else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {

		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];

	}

	return $ip;
}

function getIndexUrl($site)
{
	$provenance = "";
	$url ="";

	switch($site)
	{
		case psp: //psp
			$url = "http://www.prostagespermis.fr";
			$provenance = "";
		break;

		case amf: //amf
			$url = "http://www.amf-permis.fr";
			$provenance = "AMF";
		break;

		case sens: //sensibilisation
			$url = "http://www.sensibilisation-securite-routiere.fr";
			$provenance = "SENS";
		break;

		case rec: //recuperer
			$url = "http://www.recuperer-point-permis.fr";
			$provenance = "REC";
		break;

		case rat: //rattrapage
			$url = "http://www.rattrapage-points-permis.fr";
			$provenance = "RAT";
		break;

		case spp: //stagepointpermis
			$url = "http://www.stages-point-permis.fr";
			$provenance = "SPP";
		break;

		case pap: //permis a points
			$url = "http://www.lepermis-a-point.fr";
			$provenance = "PAP";
		break;

		case paca: //paca
			$url = "http://www.prostagespermis-paca.fr";
			$provenance = "PACA";
		break;

		case paris: //paris
			$url = "http://www.prostagespermis-paris.fr";
			$provenance = "PARIS";
		break;

		case rh: //rhone alpes
			$url = "http://www.prostagespermis-rhonealpes.fr";
			$provenance = "RH";
		break;

		case lr: //languedoc
			$url = "http://www.prostagespermis-languedoc.fr";
			$provenance = "LANG";
		break;

		case aqui: //aquitaine
			$url = "http://www.prostagespermis-aquitaine.fr";
			$provenance = "AQUI";
		break;

		case comp: //aquitaine
			$url = "http://www.comparateur-stagespermis.com";
			$provenance = "COMP";
		break;
	}
	return array($url, $provenance);
}
?>