<?php

function aff_facture($membre, $id_virement) {
die('FLAG');
	include ("../../connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	//requete membre
	$sql = "SELECT nom, email, tel, mobile, adresse, siret, assujetti_tva_confirme FROM membre WHERE id=$membre";
	$rsMembre = mysql_query($sql, $stageconnect) or die(mysql_error());
	$row_membre = mysql_fetch_assoc($rsMembre);

	$sql = "SELECT date, commentaire, total FROM virement WHERE id=$id_virement";
	$rs = mysql_query($sql, $stageconnect) or die(mysql_error());
	$row = mysql_fetch_assoc($rs);
	$commentaire = utf8_decode($row['commentaire']);
	$virement_effectue = $row['total'];
	$date_virement = date('Ymd', strtotime($row['date']));
	$date_virement2 = date('d-m-Y', strtotime($row['date']));


    setlocale(LC_TIME, "fr_FR");
    $date_facture = ucwords(strftime('%A %d %B %G',strtotime($row['date'])));

	$sql = "SELECT
                    stagiaire.facture_num,
					stagiaire.id,
					stagiaire.nom,
					stagiaire.prenom,
					stagiaire.paiement,
                    stagiaire.price_transfer,
					stagiaire.date_inscription,
					stagiaire.reduction,
					stagiaire.comm_autoecole,
					stagiaire.commission,
					stagiaire.provenance_site,
					stage.date1,
					site.ville,
					site.code_postal,
					transaction.id_membre

				FROM
					stagiaire, stage, site, transaction

				WHERE
					stagiaire.id_stage = stage.id AND
					stage.id_site = site.id AND
					transaction.id_stagiaire = stagiaire.id AND
					transaction.paiement_interne = 1 AND
					transaction.id_membre = $membre AND
					transaction.virement = $id_virement
				ORDER BY nom ASC";

	$rs = mysql_query($sql, $stageconnect) or die(mysql_error());

	mysql_close($stageconnect);

	ob_start();

	$num_facture = $date_virement."_".$membre."_".$id_virement;

	$content = "<body bgcolor='#FFFFFF'>";

	$content .= "<table cellspacing='0' style='width: 100%;padding-top:5px'>";
	$content .= "<tr>
				<td style='width:450px;padding-top:0px'>
				FACTURE ".$num_facture."<br><br>
				<strong>".strtoupper($row_membre['nom'])."</strong><br>".
				$row_membre['adresse']."<br>".
				$row_membre['tel']." ".$row_membre['mobile']."<br>".
				$row_membre['email']."<br>".
				"Siret: ".$row_membre['siret']."&nbsp;&nbsp;&nbsp;TVA: ".$row_membre['tva']."<br><br>".
				"Date facture: ". $date_facture ."
				</td>

				<td style='width:300px;padding-top:100px'>
				<strong>PROStagesPermis</strong><br>Oxydium Concept - Bat A<br>
				190 Rue Marcelle Isoard, CD9<br>
				13290 Aix en Provence Les Milles<br>
				Email: contact@prostagespermis.fr<br>
				<em>Siret: 50420905700026 - TVA: FR65504209057</em></td>
				</tr>";
	$content .= "</table>";

	$content .= "<table cellspacing='0' style='width: 100%; font-size:13px; margin-top:20;border:2;padding:3'>";

	$content .= "<tr><td style='width:240px;border-width:1px; border-style:solid; background-color:#C2C8C8'>Candidat</td>
				  <td style='width:240px;border-width:1px; border-style:solid; background-color:#C2C8C8'>Stage</td>
				  <td style='width:70px;border-width:1px; border-style:solid; background-color:#C2C8C8;text-align:center'>Paiement stagiaire</td>
				  <td style='width:70px;border-width:1px; border-style:solid; background-color:#C2C8C8;text-align:center'>Prix unitaire HT</td>
				  <td style='width:70px;border-width:1px; border-style:solid; background-color:#C2C8C8;text-align:center'>Comm HT</td>
				  </tr>";


	$virement_total = 0;
	$commission_total = 0;
	$paiement_total = 0;

	while ($row = mysql_fetch_assoc($rs))
	{

		$id_stagiaire	  = $row['id'];
		$date_inscription = $row['date_inscription'];
		$id_membre 		= $row['id_membre'];
		$nom 			= utf8_decode($row['nom']);
		$prenom 		= utf8_decode($row['prenom']);
		$date1 			= date("d-m-Y", strtotime($row['date1']));
		$paiement 		= $row['paiement'] - $row['price_transfer'];
		$code_postal 	= $row['code_postal'];
		$ville 			= $row['ville'];
		$reduction		= $row['reduction'];
		$comm_autoecole = $row['comm_autoecole'];
		$commission		 = $row['commission'];
		$provenance_site = $row['provenance_site'];

		//calcul commission
		if ($id_virement <= 6285) {

			$commission_ht = $paiement * 0.2;
			if ($commission_ht < 36.8)
				$commission_ht = 36.8;
			elseif ($commission_ht > 43)
				$commission_ht = 43;

			if ($membre == 496 || $membre == 44) //altera, securoute
				$commission_ht = 30;

			$commission_ttc = $commission_ht * 1.2;

			if ($comm_autoecole > 0)
				$commission_ttc = $comm_autoecole;
			else if ($reduction > 0)
				$commission_ttc = $commission_ttc - $reduction;
			elseif ($provenance_site == 14)
				$commission_ttc = $commission_ttc - 10;

			if ($id_stagiaire > 186950 && $commission != NULL)
			{
				$commission_ht = $commission/100;
				$commission_ttc = $commission_ht * 1.2;
			}

			//rppc
			if ($id_stagiaire >= 299000 && $membre == 793)
			{
				if ($paiement <= 155)
					$commission_ttc = $paiement * 0.18;
				else
					$commission_ttc = 36;
			}
		}
		else {
			$commission_ht = $commission/100;
			$commission_ttc = $commission_ht * 1.2;
		}

		$paiement_total += $paiement;
		$virement = $paiement - $commission_ttc;

		$content .= "<tr>
				<td style='border-width:1px solid grey;'>$nom $prenom</td>
				<td style='border-width:1px solid grey;font-size:12px'>$date1<br>$ville</td>
				<td style='border-width:1px solid grey;text-align:center'>$paiement</td>
				<td style='border-width:1px solid grey;text-align:center'>".round($paiement/1.2, 2)."</td>
				<td style='border-width:1px solid grey;text-align:right'>$commission_ht</td>
				</tr>";

		$virement_total += $virement;
		$commission_total += $commission_ht;
	}

	$content .= "</table>";

	$content .= "<table style='width:730px;padding-top:20px'>";
	$content .= "<tr><td style='width:730px;text-align:right;'>TOTAL VIREMENT HT:\t ".round($virement_effectue/1.2, 2)." euros</td></tr>";
	$content .= "<tr><td style='width:730px;text-align:right;'>TVA:\t ";
	$content .= intval($row_membre['assujetti_tva_confirme']) ? " 20% :".round(0.2*$virement_effectue/1.2, 2)." euros</td></tr>" : " (Non assujetti) 0 euro</td></tr>";
	$content .= "<tr><td style='width:730px;text-align:right;'>TOTAL VIREMENT TTC:\t ".$virement_effectue." euros</td></tr>";
	$content .= "<tr><td style='width:730px;text-align:right;'>VIREMENT DU ".$date_virement2.":\t ".$virement_effectue." euros</td></tr>";
	$content .= "<tr><td style='width:730px;text-align:right;'>RESTE A PAYER:\t 0 euro</td></tr>";
	$content .= "</table>";

	if ($commentaire)
	{
		$content .= "<table cellspacing='0' style='width: 100%; padding-top:20;margin-bottom=35px'>";
		$content .= "<tr><td style='width:100%;text-align:left'>Commentaires:\t ".$commentaire."</td></tr>";
		$content .= "</table>";
	}

	$content .= "</body>";

	echo $content;
	$content = ob_get_clean();
    ob_end_clean();

	require_once('../../html2pdf_v4.02/html2pdf.class.php');
	$html2pdf = new HTML2PDF('P','A4','fr', false, 'ISO-8859-1');
	$html2pdf->WriteHTML($content);

	$name_pdf = $num_facture.".pdf";

	$html2pdf->Output($name_pdf, 'D');

}

function telecharger_facture_centre_v2($membreId, $idFacture, $idVirement=0, $rv=0, $newId = 0) {

    require_once '/home/prostage/www/v2/Repository/MembreRepository.php';
    require_once '/home/prostage/www/v2/Repository/FactureCentreRepository.php';
    require_once '/home/prostage/www/v2/Repository/FactureCentreProduitRepository.php';

    /* #133 : Pour correction factures spécifiques */
    $S_pathDirFact = '/home/prostage/www/facture_centre/';

    $membreRepository               = new MembreRepository();
    $factureCentreRepository        = new FactureCentreRepository();
    $factureCentreProduitRepository = new FactureCentreProduitRepository();

    $membre     = $membreRepository->getMembreByMembreId($membreId);
    $isAssujettiTva = $membre['assujetti_tva_confirme'];

    if ($isAssujettiTva)
      $tvaInformation = '20,00%';
    else
        $tvaInformation = 'exonéré';

    if($idFacture > 0){
        $facture    = $factureCentreRepository->getFactureByFactureId($idFacture);
        $produits   = $factureCentreProduitRepository->findProduitsByFactureId($idFacture);
    }else{
        include ("../../connections/stageconnect.php");
	    mysql_select_db($database_stageconnect, $stageconnect);
        $sql = "SELECT date, commentaire, total FROM virement WHERE id=$idVirement";
    	$rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    	$row = mysql_fetch_assoc($rs);
    	$commentaire = $row['commentaire'];
    	$virement_effectue = $row['total'];
    	$date_virement = date('Ymd', strtotime($row['date']));
    	$date_virement2 = date('d-m-Y', strtotime($row['date']));


        setlocale(LC_TIME, "fr_FR");
        $date_facture = ucwords(strftime('%A %d %B %G',strtotime($row['date'])));


        $sql = "SELECT
                virement.date as virement_date
			FROM
				virement
			WHERE
                virement.id = ".$idVirement;
        $rResult = mysql_query($sql,$stageconnect) or die(mysql_error());
        $row = mysql_fetch_array($rResult);
        $virement_date = $row['virement_date'];

        /*$stages_date_fin = date('d-m-Y', strtotime($virement_date. ' -3 day'));
        $stages_date_debut = date('d-m-Y', strtotime($virement_date. ' -16 day'));*/

        /* Demande #46 : mise en place nouvelle méthode de calcul des dates */
        $S_dateRef = date('Y/m/d', strtotime($virement_date));
		$I_dateRef = strtotime($S_dateRef);
		$S_annee   = date('Y', $I_dateRef);
		$S_mois    = date('m', $I_dateRef);

		$I_temps    = mktime(0, 0, 0, $S_mois, 0, $S_annee);
		$I_deuxMerc = strtotime('second wednesday', $I_temps);
		$I_quatMerc = strtotime('fourth wednesday', $I_temps);

		$I_tempsPrec    = mktime (0, 0, 0, $S_mois - 1, 0, $S_annee ); // Mois précédent
		$I_quatMercPrec = strtotime('fourth wednesday', $I_tempsPrec);

		$S_merc2        = strftime("%d/%m/%Y", $I_deuxMerc);
		$S_merc4        = strftime("%d/%m/%Y", $I_quatMerc);
		$S_merc4Prec    = strftime("%d/%m/%Y", $I_quatMercPrec);

		if( $I_dateRef == $I_deuxMerc )
		{
		    // Il s'agit du premier virement
		    $I_dateDeb = strtotime( '- 2 days', $I_quatMercPrec );  // Lundi précédent le virement précédent
		    $I_dateFin = strtotime( '- 3 days', $I_deuxMerc );  // Dimanche précédent le virement en cours
		    $stages_date_debut = strftime("%d/%m/%Y", $I_dateDeb);
		    $stages_date_fin   = strftime("%d/%m/%Y", $I_dateFin);
		}
		else
		{
		     // Il s'agit du deuxième virement
		    $I_dateDeb = strtotime( '- 2 days', $I_deuxMerc );  // Lundi précédent le virement précédent
		    $I_dateFin = strtotime( '- 3 days', $I_quatMerc );  // Dimanche précédent le virement en cours
		    $stages_date_debut = strftime("%d/%m/%Y", $I_dateDeb);
		    $stages_date_fin   = strftime("%d/%m/%Y", $I_dateFin);
		}
//print('date réf => ' . $virement_date .'<br><br>Date déb : ' .$stages_date_debut . '<br>Date fin : ' . $stages_date_fin);exit;

        $dateFacture = date('d-m-Y',strtotime($virement_date.' -2 days'));
        //$numeroFacture    = "1_" .date('Ym',strtotime($virement_date.' -2 days')). sprintf('%05d', $idVirement + 1);
        $numeroFacture = "1_".date("Y_m", strtotime($virement_date))."_".($idVirement+1000);
    	$sql = "SELECT
                stagiaire.facture_num,
				stagiaire.id AS stagiaire_id,
				stagiaire.nom AS stagiaire_nom,
				stagiaire.prenom AS stagiaire_prenom,
				stagiaire.email AS stagiaire_email,
				stagiaire.paiement, 
                stagiaire.price_transfer,
				stagiaire.comm_autoecole,
				stagiaire.provenance_site,
				stagiaire.date_inscription,
				stagiaire.reduction AS stagiaire_reduction,
				stagiaire.commission AS stagiaire_commission,
				site.ville as stage_ville,
				stage.date1 as stage_date,
				stage.id as stage_id,
				stage.prix as stage_prix,
				membre.id AS membre_id,
				membre.nom AS membre_nom,
				membre.iban AS membre_iban,
				membre.bic AS membre_bic,
				membre.assujetti_tva AS assujetti_tva,
				membre.assujetti_tva_confirme AS membre_tva_confirme,
				membre.commision2 AS membre_commision2,
				membre.min_comm AS min_comm,
				membre.max_comm AS max_comm,
				membre.consignes_virement,
				membre.commission_custum,
				membre.reversement
			FROM
				stagiaire, stage, site, transaction, membre, virement
			WHERE
				stagiaire.virement_bloque = 0 AND
				stagiaire.id_stage = stage.id AND
				stagiaire.supprime = 0 AND
				stagiaire.paiement > 0 AND
				stagiaire.numtrans != '' AND
				stage.id_site = site.id AND
				transaction.id_stagiaire = stagiaire.id AND
				transaction.paiement_interne = 1 AND
				transaction.id_membre = membre.id AND
				transaction.id_membre != 837 AND
				transaction.virement = virement.id AND
                virement.id = ".$idVirement;
        $rResultStagiaire = mysql_query($sql,$stageconnect) or die(mysql_error());
        //mysql_close($stageconnect);
        //$commissions                     = [];
        $totalCommission                 = 0;
        $recapitulatifVirementStagiaires = [];
        $virementMontant                 = 0;
        $encaissement                    = 0;
        $quantite = 0;
        $stages_debut = 0;
        $stages_fin = 0;
        $stages_fin2 = 0;
        $facture['total_ht'] = 0;
        $facture['tva'] = 0;
        $facture['total_ttc'] = 0;
        if($rv == 0){
            while ($stagiaire = mysql_fetch_array($rResultStagiaire)) {
                $quantite++;

                $tmp = strtotime($stagiaire['stage_date']);
                if($stages_debut == 0)
                    $stages_debut = $tmp;
                else{
                    if($tmp < $stages_debut)
                        $stages_debut = $tmp;
                }
                $tmp = strtotime($stagiaire['stage_date']);
                if($stages_fin == 0){
                    $stages_fin = $tmp;
                    $stages_fin2 = strtotime($stagiaire['stage_date'].' +1 days');
                }else{
                    if($tmp > $stages_fin){
                        $stages_fin = $tmp;
                        $stages_fin2 = strtotime($stagiaire['stage_date'].' +1 days');
                    }
                }

                $paiement = $stagiaire['paiement'] - $stagiaire['price_transfer'];

                $min_comm = vide($stagiaire['min_comm']) ? "36.8" : $stagiaire['min_comm'];
                $max_comm = vide($stagiaire['max_comm']) ? "43" : $stagiaire['max_comm'];

                $encaissement += $paiement;

                $commissionHT = get_commission_ht($membreId, $paiement, $stagiaire['membre_commision2'], $min_comm, $max_comm, new DateTimeImmutable($stagiaire['date_inscription']));

                //$commissions["$commissionHT"]++;

                $totalCommission += $commissionHT;

                $commissionTTC          = $commissionHT * 1.2;
                $reversementUnitaireTTC = $paiement - $commissionTTC;
            }
            $produits = array();
            $virement_total = 0;
        	$commission_total = 0;
        	$paiement_total = 0;

            if ($isAssujettiTva == 1) {
                $montantHT  = round($totalCommission, 2);
                $montantTTC = round($totalCommission * 1.2, 2);
                $tva        = $montantTTC - $montantHT;

                $virementMontant = $encaissement - ($totalCommission * 1.2);
            } else {
                $montantHT  = round($totalCommission, 2);
                $montantTTC = round($totalCommission, 2);
                $tva        = 0;

                $virementMontant = ($encaissement / 1.2) - $totalCommission;
            }

            $facture['total_ht'] = $montantHT;
            $facture['tva'] = $tva;
            $facture['total_ttc'] = $montantTTC;

            $produits[0]['quantite'] = $quantite;
            $produits[0]['prix_unitaire_ht'] = $totalCommission/$quantite;
            $produits[0]['total_unitaire_ht'] = $totalCommission;
        }

        if($stages_debut > 0){
            if($stages_fin > 0){
                if($stages_debut == $stages_fin){
                    $stages_fin = date('Y-m-d',$stages_fin2);
                }else
                    $stages_fin = date('Y-m-d',$stages_fin);
            }
             $stages_debut = date('Y-m-d',$stages_debut);
        }

         setlocale(LC_TIME, "fr_FR");
	    $stagesDebut = ucwords(utf8_encode(strftime('%d %B %G',strtotime($facture['stages_debut']))));
	    $stagesFin = ucwords(utf8_encode(strftime('%d %B %G',strtotime($facture['stages_fin']))));

	    // #85 numéro facture différent à partir de Mars 2021
	    $date_year_facture = date('Y', strtotime($virement_date));
	    $date_month_facture = date('m', strtotime($virement_date));

	    // #85 (bis)
		$I_dateToCheck = strtotime($virement_date);
		$I_dateRef     =  1614556800; // Timestamp => 01/03/2021 00:00:00

		if( $I_dateToCheck >= $I_dateRef )
		{
		    // Traitement post mars 2021
		    $S_selectId = 'SELECT fc.id from facture_centre as fc ' .
	    	              'INNER JOIN virement as v ON fc.id_membre=v.id_membre AND ' .
	    	              'DATE_ADD(DATE_FORMAT(fc.date, \'%Y-%m-%d\'), INTERVAL 2 DAY) = DATE_FORMAT(v.date, \'%Y-%m-%d\') ' .
	    	              'WHERE fc.id_membre=' . $membreId . ' AND DATE_FORMAT(v.date, \'%Y-%m-%d\')= DATE_FORMAT(\''. $virement_date . '\',\'%Y-%m-%d\')';

	        $rResult2 = mysql_query( $S_selectId, $stageconnect ) or die(mysql_error());

	        if($aRow2 = mysql_fetch_array($rResult2))
	        {
	            $id_facture = $aRow2['id'];
	        }

		    $A_listNewIdFactures = getNewListeFacturesId($membreId);

		    $facture['numero_facture'] = 'Khapeo_' . $membreId. '_1_' .date("Y_m", strtotime($dateFacture)).'_'. $A_listNewIdFactures[$id_facture];
		    $numeroFacture = $facture['numero_facture'];
		}

        $nomFacture       = "$numeroFacture.pdf";
        $nomRecapitulatif = "$numeroFacture.pdf";

        $facture['numero_facture'] = $numeroFacture;
        if($rv == 0)
            $facture['nom_facture'] = $nomFacture;
        else{
            $facture['nom_facture'] = $nomRecapitulatif;
            $facture['numero_facture'] = $numeroFacture;
        }
        $facture['id_membre'] = $membreId;
        $facture['date'] = $dateFacture;
        //$virementMontant,
        $facture['stages_debut'] = $stages_debut;
        $facture['stages_fin'] = $stages_fin;

        //$nomFacture,
        //$nomRecapitulatif



        /*
        $date1 			= date("d-m-Y", strtotime($row['date1']));
    		$paiement 		= $row['paiement'];
    		$reduction		= $row['reduction'];
    		$comm_autoecole = $row['comm_autoecole'];
    		$commission		 = $row['commission'];

        */
    }
    if($rv > 0){
        $montantAReverserHT = 0;
        $borderBottom = 'margin-bottom: 10px;';
        $montantHT = 0;
        $montantTTC = 0;
        $content2 = '';
        $stages_debut = 0;
        $stages_fin = 0;
        $stages_fin2 = 0;
        while ($stagiaire = mysql_fetch_array($rResultStagiaire)) {
            $min_comm = vide($stagiaire['min_comm']) ? "36.8" : $stagiaire['min_comm'];
            $max_comm = vide($stagiaire['max_comm']) ? "43" : $stagiaire['max_comm'];

            $tmp = strtotime($stagiaire['stage_date']);
                if($stages_debut == 0)
                    $stages_debut = $tmp;
                else{
                    if($tmp < $stages_debut)
                        $stages_debut = $tmp;
                }
                $tmp = strtotime($stagiaire['stage_date']);
                if($stages_fin == 0){
                    $stages_fin = $tmp;
                    $stages_fin2 = strtotime($stagiaire['stage_date'].' +1 days');
                }else{
                    if($tmp > $stages_fin){
                        $stages_fin = $tmp;
                        $stages_fin2 = strtotime($stagiaire['stage_date'].' +1 days');
                    }
                }

            $paiement = $stagiaire['paiement'] - $stagiaire['price_transfer'];

            $commissionHT = get_commission_ht($membreId, $paiement, $stagiaire['membre_commision2'], $min_comm, $max_comm, new DateTimeImmutable($stagiaire['date_inscription']));

            $commissionTTC  = $commissionHT;
            $paiementHT     = $paiement/1.2;
            $reversementUnitaireHt = ($paiementHT - $commissionTTC);

            $montantAReverserHT += $reversementUnitaireHt;

             $montantHT  += $reversementUnitaireHt;
             $montantTTC += $reversementUnitaireHt;
             $ref = "0123456789012345678901234567890123456789";
             //"0123456789012345678901234567890123456789-------";
             $nom_prenom = $stagiaire['stagiaire_nom'] . " " . $stagiaire["stagiaire_prenom"];
             /*if(count($nom_prenom) > count($ref))
                $nom_prenom = substr($nom_prenom,0,count($ref)-1);*/
             $lieu = $stagiaire['stage_ville'];
            $content2 .= "
            <tr style='text-align:center'>
                <td style='font-size:10px;border-right:1px solid black;padding: 5px;$borderBottom'>" . $nom_prenom . "</td>";
            $content2 .= "<td style='font-size:10px;border-right:1px solid black;padding: 5px;$borderBottom'>" . $lieu . "</td>";
            $content2 .= "<td style='font-size:10px;border-right:1px solid black;padding-top: 10px; padding-bottom: 10px;$borderBottom'>" . number_format($paiementHT, 2, ',', ' ') . "</td>";
            $content2 .= "<td style='font-size:10px;border-right:1px solid black;padding-top: 10px; padding-bottom: 10px;$borderBottom'>" . number_format($commissionHT, 2, ',', ' ') . "</td>";
            $content2 .= "
                <td style='font-size:10px;padding-top: 10px; padding-bottom: 10px;$borderBottom'>" . number_format($reversementUnitaireHt, 2, ',', ' ') . "</td>
            </tr>";
        }
        if($stages_debut > 0){
            if($stages_fin > 0){
                if($stages_debut == $stages_fin){
                    $stages_fin = date('Y-m-d',$stages_fin2);
                }else
                    $stages_fin = date('Y-m-d',$stages_fin);
            }
             $stages_debut = date('Y-m-d',$stages_debut);
        }

        $facture['stages_debut'] = $stages_debut;
        $facture['stages_fin'] = $stages_fin;
    }
   
	if( $stageconnect)
	{
		mysql_close($stageconnect);
	}

    ob_start();

    $content = " 

    <body>";

    $content .= "<table cellspacing='0' style='width:100%; padding: 20px;'>";

    $content .= "<tr>";
    $content .= "<td style='padding-top:30px;' colspan=2>";
    $content .= "<b>" . ucwords($membre['nom']) . "</b>";
    $content .= "<br>";
    $content .= $membre['adresse'];
    $content .= "<br>";
    $content .= "Siret : ". $membre['siret'];
    $content .= "<br>";
    $content .= "N° TVA Intracommunauatire : ". $membre['tva'];
    $content .= "<br><br>";

    $content .= "Identifiant du centre : ". $facture['id_membre'];
    $content .= "<br>";
    $facture_name_prefix = "Facture n° ";
    /*if($newId > 0) {
        $facture_name_prefix = "Facture n° Khapeo_";
    }*/
    $content .= $facture_name_prefix . $facture['numero_facture'];
    $content .= "<br>";
    $content .= "Date:  ". $facture['date'];
    $content .= "</td>";
    $content .= "</tr>";

    $content .= "<tr >";
    $content .= "<td style='width:65%;'>&nbsp;</td>";
    $content .= "<td style='width:35%;padding-top:30px;'>";
    $content .= "<b>Khapeo <br> ProstagesPermis</b>";
    $content .= "<br>";
    $content .= "190 rue Marcelle Isoard";
    $content .= "<br>";
    $content .= "13290 Aix En Provence";
    $content .= "</td>";
    $content .= "</tr>";

    $content .= "<tr>";
    $content .= "<td style='padding-top:50px;text-align:center;' colspan=2>";

    $titre = "<span style='font-size: 16px; font-weight: bold'>Facture n° ". $facture['numero_facture'] ."</span>";
    if( $I_dateToCheck >= $I_dateRef )
	{
        $titre = "<span style='font-size: 16px; font-weight: bold'>Facture n° ". $facture['numero_facture'] ."</span>";
    }
    $content .= $titre;
    $content .= "<br>";
    $content .= "<span style='font-size: 11px; font-style: italic;'>(Stagiaires ayant effectué leur stage entre le ".$stages_date_debut." et le ".$stages_date_fin.")</span>";
    $content .= "</td>";
    $content .= "</tr>";
    $content .= "</table>";

    if($rv > 0){
        $content .= "
        <table cellspacing='0' style='width:595pt;margin-top:10px;border:1px solid black;text-align:center;'>
        <tr style='text-align:center'>
          <th style='width:150pt;border-right:1px solid black;border-bottom:1px solid black;padding: 8px;'>Stagiaires</th>
          <th style='width:150pt;border-right:1px solid black;border-bottom:1px solid black;padding: 8px;'>Lieu du stage</th>
          <th style='width:60pt;border-right:1px solid black;border-bottom:1px solid black;padding-top: 8px;padding-bottom: 8px;padding-right: 3px;padding-left: 3px;'><p style='word-break: break-all'>Prix Unitaire Brut HT</p></th>
          <th style='width:60pt;border-right:1px solid black;border-bottom:1px solid black;padding-top: 8px;padding-bottom: 8px;padding-right: 3px;padding-left: 3px;'><p style='word-break: break-all'>Remise Unitaire HT</p></th>
          <th style='width:60pt;border-bottom:1px solid black;padding-top: 8px;padding-bottom: 8px;padding-right: 3px;padding-left: 3px;'><p style='word-break: break-all'>Prix Unitaire Net HT</p></th>
        </tr>";
         $content .= $content2;
        if ($isAssujettiTva == 1) {
            $montantHT  = round($montantHT, 2);
            $montantTTC = round($montantTTC*1.2, 2);
            $tva        = $montantTTC - $montantHT;
        } else {
            $montantHT  = round($montantHT, 2);
            $montantTTC = round($montantTTC, 2);
            $tva        = 0;
        }
        $facture['total_ht'] = $montantHT;
        $facture['tva'] = $tva;
        $facture['total_ttc'] = $montantTTC;
    }else{
      $content .= "
      <table cellspacing='0' style='width:100%;margin-top:5px;border:1px solid black;text-align:center;'>
      <tr style='width:100%;text-align:center'>
        <th style='width:40%;border-right:1px solid black;border-bottom:1px solid black;padding-top:10px;padding-bottom:10px;'>Désignation</th>
        <th style='width:20%;border-right:1px solid black;border-bottom:1px solid black;'>Quantité</th>
        <th style='width:20%;border-right:1px solid black;border-bottom:1px solid black;'>Prix Unitaire HT</th>
        <th style='width:20%;border-bottom:1px solid black;'>Total Unitaire HT</th>
      </tr>";

      $i = 0;
      $len = count($produits);
      foreach ($produits as $produit)
      {
          $borderBottom = '';
          if ($i != $len - 1) {
              $borderBottom = 'border-bottom: 1px solid black;';
          }

          $content .= "
          <tr valign='top'>";

          if ($i == 0) {
              $content .= "
                      <td rowspan='$len' style='width:195px;border-right:1px solid black;padding-top:10px;padding-bottom:10px; text-align: center;'>
                          Commission globale sur inscription internet stagiaire pour stage de sensibilisation à la sécurité routière
                      </td>";
          }

          $content .= "<td style='width:70px;border-right:1px solid black;padding-top:10px;padding-bottom:10px;$borderBottom'>". $produit['quantite'] ."</td>";
          $content .= "<td style='width:70px;border-right:1px solid black;padding-top:10px;padding-bottom:10px;$borderBottom'>". formatPrix($produit['prix_unitaire_ht']) ." €</td>";
          $content .= "<td style='width:70px;padding-top:10px;padding-bottom:10px;$borderBottom'>". formatPrix($produit['total_unitaire_ht']) ." €</td>
          </tr>";

          $i++;
      }

    }



      $content .= "</table>";

    $content .= "<div>
    <table cellspacing='0' style='width:280px;margin-left:385px;margin-top:50px;border:1px solid black;'>
    <tr style='width:100%;text-align:center;'>
        <td style='width:180px;border-right:1px solid black;border-bottom:1px solid black;padding-top:5px;padding-bottom:5px;font-weight:normal'>TOTAL HT</td>
        <td style='width:100px;border-bottom:1px solid black;'>". formatPrix($facture['total_ht']) ." €</td>
    </tr>
    <tr style='width:100%;text-align:center;'>
        <td style='width:180px;border-right:1px solid black;border-bottom:1px solid black;padding-top:5px;padding-bottom:5px;font-weight:normal'>TVA ($tvaInformation)</td>
        <td style='width:100px;border-bottom:1px solid black;'>". formatPrix($facture['tva']) ." €</td>
    </tr>
    <tr style='width:100%;text-align:center;'>
        <td style='width:180px;border-right:1px solid black;border-bottom:1px solid black;padding-top:5px;padding-bottom:5px;font-weight:normal'>TOTAL TTC</td>
        <td style='width:100px;border-bottom:1px solid black;'>". formatPrix($facture['total_ttc']) ." €</td>
    </tr>";
   if ($isAssujettiTva == 1) {
   $content .= "
    <tr style='width:100%;text-align:center;'>
        <td style='width:180px;border-right:1px solid black;border-bottom:1px solid black;padding-top:5px;padding-bottom:5px;font-weight:normal'>Déjà réglé</td>
        <td style='width:100px;border-bottom:1px solid black;'>". formatPrix($facture['total_ttc']) ." €</td>
    </tr>
    <tr style='width:100%;text-align:center;'>
        <td style='width:180px;border-right:1px solid black;padding-top:5px;padding-bottom:5px;font-weight:normal'>Net à Payer</td>
        <td style='width:100px;'>". formatPrix(0) ." €</td>
    </tr>";
    }
    $content .= "</table></div>";

    $content .= "<table cellspacing='0' style='width:100%; padding: 20px;'>";

    $content .= "<tr>";
    $content .= "<td style='padding-top:50px;text-align:left;'>";


    $content .= "<span style='font-size: 11px; font-style: italic'>Facture réglée par virement selon accord des parties.<br />Escompte pour paiement anticipé : néant.</span>";
    $content .= "</td>";
    $content .= "</tr>";
    $content .= "</table>";

	if ($commentaire)
	{
		$content .= "<table cellspacing='0' style='width: 100%; padding-top:20;margin-bottom=35px'>";
		$content .= "<tr><td style='width:100%;text-align:left'><u>Commentaires</u>:<br><br> " . $commentaire . "</td></tr>";
		$content .= "</table><br>";
	}

    $content .= "<page_footer backbottom='20mm' style='color:black;font-size:10px;text-align:center'>";

    // Centre
    $content .= $membre['nom']." – ".$membre['adresse'];
    $content .= "<br />";
    $content .= "Tel : ".$membre['tel'];
    $content .= " / E-mail : ".$membre['email'];
    $content .= " – ".$membre['siret'];
    $content .= " – ".$membre['tva'];

    $content .= "</page_footer>";

    /////// OLD

    $content .= "</body>";

    echo $content;
    $content = ob_get_clean();
    ob_end_clean();

    $dossier = "/home/prostage/www/facture_centre/tmp/";
    $name_pdf = $facture['nom_facture'];
    $file_path = $dossier.$name_pdf;

    require_once('/home/prostage/html2pdf_v4.03/html2pdf.class.php');
    $html2pdf = new HTML2PDF('P','A4','fr', true, 'UTF-8');
    $html2pdf->WriteHTML($content);
    $html2pdf->Output($file_path, 'F');

    /*if($newId > 0) {*/
   /*if( $I_dateToCheck >= $I_dateRef )
    {
        $facture['nom_facture'] = 'Khapeo_' . $membreId . '_1_'. $nomFacture;
    }*/

    /* #133 : Modification factures spécifiques */
    $B_deleteFact = true;
    if( $membreId == 82 || $membreId == 287 || $membreId == 793 )
    {
    	if( $name_pdf == '1_2018_03_6574.pdf' || $name_pdf == '1_2019_07_7638.pdf' || $name_pdf == '1_2018_02_6492.pdf' || $name_pdf == '1_2018_07_6917.pdf')
    	{
    		$S_oldFile_path = $file_path;
    		$file_path = $S_pathDirFact . $membreId . '/' . $name_pdf;
    		$B_deleteFact = false; // Pour ne pas effacer la facture dans le répertoire
    	}
    }

    header('Content-disposition: attachment; filename='. $facture['nom_facture']);
    header('Content-type: application/pdf');
    readfile($file_path);

    if( $B_deleteFact )
    {
    	unlink($file_path);
    }
    else
    {
    	// Pour suppression de la facture générée mais non utilisée (#133)
    	unlink($S_oldFile_path);
    }
}

function telecharger_recapitulatif_virement($membreId, $idFacture, $idVirement=0, $rv=0) {

     include ("../../connections/stageconnect.php");
	    mysql_select_db($database_stageconnect, $stageconnect);
        $sql = "SELECT
                virement.date as virement_date, commentaire
			FROM
				virement
			WHERE
                virement.id = ".$idVirement;
        $rResult = mysql_query($sql,$stageconnect) or die(mysql_error());
        $row = mysql_fetch_array($rResult);
        $virement_date = $row['virement_date'];

        // #45 : ajout commentaires date dans pdf
	    $S_commentaire = $row['commentaire'];

        $numeroFacture = "RV_1_".date("Y_m", strtotime($virement_date))."_".($idVirement+1000);
        $numeroFacture2 = "1_".date("Y_m", strtotime($virement_date))."_".($idVirement+1000);
    require_once '/home/prostage/www/v2/Repository/MembreRepository.php';
    require_once '/home/prostage/www/v2/Repository/FactureCentreRepository.php';
    require_once '/home/prostage/www/v2/Repository/RecapitulatifVirementCentreRepository.php';
    require_once '/home/prostage/www/v2/Repository/RecapitulatifVirementCentreStagiaireRepository.php';

    $membreRepository                                   = new MembreRepository();
    $factureCentreRepository                            = new FactureCentreRepository();
    $recapitulatifVirementCentreRepository              = new RecapitulatifVirementCentreRepository();
    $recapitulatifVirementCentreStagiaireRepository     = new RecapitulatifVirementCentreStagiaireRepository();

    $membre                             = $membreRepository->getMembreByMembreId($membreId);
    if($idFacture > 0){
        $facture                            = $factureCentreRepository->getFactureByFactureId($idFacture);
        $recapitulatifVirement              = $recapitulatifVirementCentreRepository->getRecapitulatifVirementByFactureId($idFacture);
        $recapitulatifVirementStagiaires    = $recapitulatifVirementCentreStagiaireRepository->getStagiairesByRecapitulatifVirementId($idFacture);
    }else{
        $sql = "SELECT * FROM virement WHERE id = ".$idVirement;
    	$rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    	$row = mysql_fetch_assoc($rs);
    	$recapitulatifVirement['date'] = $row['date'];
        $dateRecapitulatifVirement = $row['date'];
        $facture['numero_facture'] = $numeroFacture2;
        $recapitulatifVirementStagiaires = array();
        $sql = "
                SELECT st.date1 as stage_date,sit.ville as stage_ville,s.nom,s.prenom,s.paiement as prix_paye_par_stagiaire,s.commission as commission_ht,0 as id,t.date_transaction as date,s.paiement as total_ttc,v.id AS id_virement, v.date AS date_virement, v.commentaire,v.total as virement_montant  
                    FROM transaction as t INNER JOIN virement AS v ON v.id = t.virement and v.id=".$idVirement."
                    INNER JOIN stagiaire as s ON s.id=t.id_stagiaire
                    INNER JOIN stage as st ON st.id=s.id_stage 
                    INNER JOIN site as sit ON sit.id=st.id_site
                    ORDER BY s.nom,s.prenom DESC ";
        	$rs = mysql_query($sql, $stageconnect) or die(mysql_error());
        	while ($recapitulatifVirementStagiaire = mysql_fetch_assoc($rs)) {
                $recapitulatifVirementStagiaire['commission_ht'] = $recapitulatifVirementStagiaire['commission_ht']/100;
                $recapitulatifVirementStagiaire['reversement_unitaire_ht'] = ($recapitulatifVirementStagiaire['prix_paye_par_stagiaire']/1.2)-$recapitulatifVirementStagiaire['commission_ht'];
        		$recapitulatifVirementStagiaires[] = $recapitulatifVirementStagiaire;
	   }

		// #85 (bis)
		$I_dateToCheck = strtotime($row['date']);
		$I_dateRef     =  1614556800; // Timestamp => 01/03/2021 00:00:00

		if( $I_dateToCheck >= $I_dateRef )
		{
		    // Traitement post mars 2021
		    $S_selectId = 'SELECT fc.id from facture_centre as fc ' .
	    	              'INNER JOIN virement as v ON fc.id_membre=v.id_membre AND ' .
	    	              'DATE_ADD(DATE_FORMAT(fc.date, \'%Y-%m-%d\'), INTERVAL 2 DAY) = DATE_FORMAT(v.date, \'%Y-%m-%d\') ' .
	    	              'WHERE fc.id_membre=' . $membreId . ' AND DATE_FORMAT(v.date, \'%Y-%m-%d\')= DATE_FORMAT(\''. $row['date'] . '\',\'%Y-%m-%d\')';

	        $rResult2 = mysql_query( $S_selectId, $stageconnect ) or die(mysql_error());

	        if($aRow2 = mysql_fetch_array($rResult2))
	        {
	            $id_facture = $aRow2['id'];
	        }

	    	$A_listNewIdFactures = getNewListeFacturesId($membreId);

	        $numeroFacture = 'RV_Khapeo_' . $membreId . '_1_' . date("Y_m", strtotime($virement_date)) . '_' . $A_listNewIdFactures[$id_facture];
        	$numeroFacture2 = 'Khapeo_' . $membreId . '_1_' .  date( "Y_m", strtotime($virement_date)) . '_' . $A_listNewIdFactures[$id_facture];
	        $facture['numero_facture'] = $numeroFacture2;
	    }

    }
    $recapitulatifVirement['reference'] = $numeroFacture;

    setlocale(LC_TIME, "fr_FR");
    $dateRecapitulatifVirement = new DateTimeImmutable($recapitulatifVirement['date']);
    $dateVirement = $dateRecapitulatifVirement->modify('+2 days');
    $dateRecapitulatifVirement = date('d-m-Y', strtotime($recapitulatifVirement['date']));//ucwords(utf8_encode(strftime('%d %B %G', $dateVirement->getTimestamp())));

    ob_start();

    $content = " 

    <body>";

    $content .= "<table cellspacing='0' style='width:100%; padding-top: 20px;padding-right: 20px;padding-left: 20px;'>";

    $content .= "<tr>";
    $content .= "<td style='padding-top:30px;' colspan=2>";
    $content .= "<img src='/home/prostage/www/assets/img/logo.jpg' style='width:150px'>";
    $content .= "<br>";
    $content .= "<b>ProStagesPermis</b>";
    $content .= "<br>";
    $content .= "190 rue Marcelle Isoard";
    $content .= "<br>";
    $content .= "13290 Aix en Provence Les Milles";
    $content .= "</td>";
    $content .= "</tr>";

    $content .= "<tr >";
    $content .= "<td style='width:65%;'>&nbsp;</td>";
    $content .= "<td style='width:35%;padding-top:30px;'>";
    $content .= "<span style='font-size: 16px; font-weight: bold'>Récapitulatif bi-mensuel</span>";
    $content .= "<br><br />";
    $content .= "Référence : " . $recapitulatifVirement['reference'];
    $content .= "<br>";
    $content .= "Date : $dateRecapitulatifVirement";
    $content .= "<br>";
    $content .= "Identifiant partenaire : " . $membreId;
    $content .= "<br>";
    $content .= "<br>";
    $content .= "<span style='font-size: 12px'>" . ucwords($membre['nom']) . "</span>";
    $content .= "<br>";
    $content .= $membre['adresse'];
    $content .= "</td>";
    $content .= "</tr>";

    $content .= "<tr>";
    $content .= "<td style='padding-top:50px;text-align:center;' colspan=2>";
    $content .= "<span style='font-weight: bold'>Récapitulatif du $dateRecapitulatifVirement</span><br />";
    $content .= "<span style='font-size: 10px; font-style: italic'>(concernant les stagiaires de la facture n° " . $facture['numero_facture'] . ")</span><br />";
    $content .= "</td>";
    $content .= "</tr>";
    $content .= "</table>";

    $content .= "
    <table cellspacing='0' style='width:595pt;margin-top:10px;border:1px solid black;text-align:center;'>
    <tr style='text-align:center'>
      <th style='width:110pt;border-right:1px solid black;border-bottom:1px solid black;padding: 8px;'>Stagiaires</th>
      <th style='width:100pt;border-right:1px solid black;border-bottom:1px solid black;padding: 8px;'>Lieu du stage</th>
      <th style='border-right:1px solid black;border-bottom:1px solid black;padding: 8px;'>Date du stage</th>
      <th style='width:12%;border-right:1px solid black;border-bottom:1px solid black;padding-top: 8px;padding-bottom: 8px;padding-right: 3px;padding-left: 3px;'><p style='word-break: break-all'>Prix payé par le stagiaire TTC</p></th>
      <th style='width:13%;border-right:1px solid black;border-bottom:1px solid black;padding-top: 8px;padding-bottom: 8px;padding-right: 3px;padding-left: 3px;'><p style='word-break: break-all'>Commission HT</p></th>
      <th style='width:13%;border-bottom:1px solid black;padding-top: 8px;padding-bottom: 8px;padding-right: 3px;padding-left: 3px;'><p style='word-break: break-all'>Reversement unitaire HT</p></th>
    </tr>";

    $borderBottom = 'margin-bottom: 10px;';

    $montantAReverserHT = 0;

    foreach ($recapitulatifVirementStagiaires as $recapitulatifVirementStagiaire) {

        $montantAReverserHT += $recapitulatifVirementStagiaire['reversement_unitaire_ht'];

        $content .= "
        <tr style='text-align:center'>
            <td style='width:110pt;border-right:1px solid black;padding: 5px;$borderBottom'>" . $recapitulatifVirementStagiaire['nom'] . " " . $recapitulatifVirementStagiaire["prenom"] . "</td>";
        $content .= "<td style='width:100pt;border-right:1px solid black;padding: 5px;$borderBottom'>" . $recapitulatifVirementStagiaire['stage_ville'] . "</td>";
        $content .= "<td style='border-right:1px solid black;padding: 5px;$borderBottom'>" . date("d-m-Y", strtotime($recapitulatifVirementStagiaire['stage_date'])) . "</td>";
        $content .= "<td style='border-right:1px solid black;padding-top: 10px; padding-bottom: 10px;$borderBottom'>" . number_format($recapitulatifVirementStagiaire['prix_paye_par_stagiaire'], 2, ',', ' ') . " €</td>";
        $content .= "<td style='border-right:1px solid black;padding-top: 10px; padding-bottom: 10px;$borderBottom'>" . number_format($recapitulatifVirementStagiaire['commission_ht'], 2, ',', ' ') . " €</td>";
        $content .= "
            <td style='padding-top: 10px; padding-bottom: 10px;$borderBottom'>" . number_format($recapitulatifVirementStagiaire['reversement_unitaire_ht'], 2, ',', ' ') . " €</td>
        </tr>";
    }

    $content .= "</table>";

    $content .= "<table cellspacing='0' style='width:100%;'>";

    $content .= "<tr>";
    $content .= "<td style='padding-top:35px;text-align:left;'>";

    $content .= "<span style='font-size: 11px;'>Virement sur <b>" . ucwords($membre['nom']) . "</b><br>" . $membre['iban'] . "</span>";
    $content .= "</td>";
    $content .= "</tr>";
    $content .= "</table>";

    $exonereTva = '';
    if ($membre['assujetti_tva_confirme'] != 1) {
        $exonereTva = '(exonéré)';
        $tva = 0;
        $montantAReverserTTC = $montantAReverserHT;
    } else {
        $montantAReverserTTC = $montantAReverserHT * 1.2;
        $tva = $montantAReverserTTC - $montantAReverserHT;
    }

    $content .= "<div>
    <table cellspacing='0' style='width:320px;margin-left:420px;margin-top:25px;border:1px solid black;'>
    <tr style='width:100%;text-align:center;'>
        <td style='width:70%;border-right:1px solid black;border-bottom:1px solid black;padding-top:5px;padding-bottom:5px;font-weight:normal'>MONTANT À REVERSER HT</td>
        <td style='width:30%;border-bottom:1px solid black;'>" . number_format($montantAReverserHT, 2, ',', ' ') . " €</td>
    </tr>
    <tr style='width:100%;text-align:center;'>
        <td style='width:70%;border-right:1px solid black;border-bottom:1px solid black;padding-top:5px;padding-bottom:5px;font-weight:normal'>TVA $exonereTva</td>
        <td style='width:30%;border-bottom:1px solid black;'>" . number_format($tva, 2, ',', ' ') . " €</td>
    </tr>
    <tr style='width:100%;text-align:center;'>
        <td style='width:70%;border-right:1px solid black;padding-top:5px;padding-bottom:5px;font-weight:normal'>MONTANT À REVERSER TTC</td>
        <td style='width:30%;'>" . number_format($montantAReverserTTC, 2, ',', ' ') . " €</td>
    </tr>";
    $content .= "</table></div>";

    if($S_commentaire)
	{
		$content .= "<table cellspacing='0' style='width: 100%; padding-top:20;margin-bottom=35px'>";
		$content .= "<tr><td style='width:100%;text-align:left'><u>Commentaires</u>:<br><br>".$S_commentaire."</td></tr>";
		$content .= "</table>";
	}

    $content .= "<page_footer backbottom='20mm' style='color:black;font-size:10px;text-align:center'>";

    // Centre
    $content .= "Khapeo – ProStgagePermis – Oxydium Concet Bât. A – 190 rue Marcelle Isoard – CD9 – 132900 AIX LES MILLES<br />
    E-mail : contact@prostagespermis.fr – SIRET : 504 209 057 00034 – RCS Aix-en-Provence : B 504 209 057 – APE 6312Z
";

    $content .= "</page_footer>";

    /////// OLD

    $content .= "</body>";

    echo $content;
    $content = ob_get_clean();

    $dossier = "/home/prostage/www/facture_centre/tmp/";
    $name_pdf = $numeroFacture.".pdf";
    $file_path = $dossier.$name_pdf;

    require_once('/home/prostage/html2pdf_v4.03/html2pdf.class.php');
    $html2pdf = new HTML2PDF('P','A4','fr', true, 'UTF-8');
    $html2pdf->WriteHTML($content);
    $html2pdf->Output($file_path, 'F');

    header('Content-disposition: attachment; filename='. $name_pdf);
    header('Content-type: application/pdf');
    readfile($file_path);

    unlink($file_path);
    mysql_close($stageconnect);
}

function telecharger_recapitulatif_virement_en_attente($membreId) {

    require_once '/home/prostage/www/v2/Repository/MembreRepository.php';
    require_once '/home/prostage/www/v2/Repository/FactureCentreRepository.php';
    require_once '/home/prostage/www/v2/Repository/StagiaireRepository.php';

    $membreRepository           = new MembreRepository();
    $factureCentreRepository    = new FactureCentreRepository();
    $stagiaireRepository        = new StagiaireRepository();

    $membre         = $membreRepository->getMembreByMembreId($membreId);
    //$facture        = $factureCentreRepository->getFactureByFactureId($idFacture);
    $stagiaires     = $stagiaireRepository->findStagiairesEnAttenteFactureByMembreId($membreId);

    setlocale(LC_TIME, "fr_FR");
    $dateDuJour = new DateTimeImmutable();
    $dateDuJour = ucwords(utf8_encode(strftime('%d %B %G', $dateDuJour->getTimestamp())));

    ob_start();

    $content = " 

    <body>";

    $content .= "<table cellspacing='0' style='width:100%; padding-top: 5px;padding-right: 20px;padding-left: 20px;'>";

    $content .= "<tr>";
    $content .= "<td style='padding-top:30px;' colspan=2>";
    $content .= "<img src='/home/prostage/www/assets/img/logo.jpg' style='width:150px'>";
    $content .= "<br>";
    $content .= "<b>ProStagesPermis</b>";
    $content .= "<br>";
    $content .= "190 rue Marcelle Isoard";
    $content .= "<br>";
    $content .= "13290 Aix en Provence Les Milles";
    $content .= "</td>";
    $content .= "</tr>";

    $content .= "<tr >";
    $content .= "<td style='width:65%;'>&nbsp;</td>";
    $content .= "<td style='width:35%;padding-top:30px;'>";
    $content .= "<span style='font-size: 16px; font-weight: bold'>Récapitulatif stagiaires en attente de virement</span>";
    $content .= "<br><br />";
    $content .= "Référence : En attente";
    $content .= "<br>";
    $content .= "Date : $dateDuJour";
    $content .= "<br>";
    $content .= "Identifiant partenaire : " . $membreId;
    $content .= "</td>";
    $content .= "</tr>";
    $content .= "</table>";

    $content .= "
    <table cellspacing='0' style='width:100%;margin-top:15px;border:1px solid black;text-align:center;'>
    <tr style='text-align:center'>
      <th style='width:20%;border-right:1px solid black;border-bottom:1px solid black;padding: 8px;'>Stagiaires</th>
      <th style='width:20%;border-right:1px solid black;border-bottom:1px solid black;padding: 8px;'>Lieu du stage</th>
      <th style='width:15%;border-right:1px solid black;border-bottom:1px solid black;padding: 8px;'>Date du stage</th>
      <th style='width:15%;border-right:1px solid black;border-bottom:1px solid black;padding-top: 8px;padding-bottom: 8px;padding-right: 3px;padding-left: 3px;'><p style='word-break: break-all'>Prix payé par le stagiaire TTC</p></th>
      <th style='width:15%;border-right:1px solid black;border-bottom:1px solid black;padding-top: 8px;padding-bottom: 8px;padding-right: 3px;padding-left: 3px;'><p style='word-break: break-all'>Commission HT</p></th>
      <th style='width:15%;border-bottom:1px solid black;padding-top: 8px;padding-bottom: 8px;padding-right: 3px;padding-left: 3px;'><p style='word-break: break-all'>Reversement unitaire HT</p></th>
    </tr>";

    $borderBottom = 'margin-bottom: 10px;';

    $montantAReverserHT = 0;

    foreach ($stagiaires as $stagiaire) {

        $min_comm = vide($stagiaire['min_comm']) ? "36.8" : $stagiaire['min_comm'];
        $max_comm = vide($stagiaire['max_comm']) ? "43" : $stagiaire['max_comm'];
        $paiement  = $stagiaire['paiement'] - $stagiaire['price_transfer'];

        $commissionHT = get_commission_ht($membreId, $paiement, $stagiaire['membre_commision2'], $min_comm, $max_comm, new DateTimeImmutable($stagiaire['date_inscription']));

        $commissionTTC          = $commissionHT * 1.2;
        $reversementUnitaireHt = ($paiement - $commissionTTC) / 1.2;

        $montantAReverserHT += $reversementUnitaireHt;

        $content .= "
        <tr style='text-align:center'>
            <td style='width:110pt;border-right:1px solid black;padding: 5px;$borderBottom'><p style='word-break: break-all'>" . $stagiaire['nom'] . " " . $stagiaire["prenom"] . "</p></td>";
        $content .= "<td style='width:100pt;border-right:1px solid black;padding: 5px;$borderBottom'><p style='word-break: break-all'>" . $stagiaire['stage_ville'] . "</p></td>";
        $content .= "<td style='border-right:1px solid black;padding: 5px;$borderBottom'>" . date("d-m-Y", strtotime($stagiaire['stage_date'])) . "</td>";
        $content .= "<td style='border-right:1px solid black;padding-top: 10px; padding-bottom: 10px;$borderBottom'>" . number_format($paiement, 2, ',', ' ') . " €</td>";
        $content .= "<td style='border-right:1px solid black;padding-top: 10px; padding-bottom: 10px;$borderBottom'>" . number_format($commissionHT, 2, ',', ' ') . " €</td>";
        $content .= "
            <td style='padding-top: 10px; padding-bottom: 10px;$borderBottom'>" . number_format($reversementUnitaireHt, 2, ',', ' ') . " €</td>
        </tr>";
    }

    $content .= "</table>";

    $exonereTva = '';
    if ($membre['assujetti_tva_confirme'] != 1) {
        $exonereTva = '(exonéré)';
        $tva = 0;
        $montantAReverserTTC = $montantAReverserHT;
    } else {
        $montantAReverserTTC = $montantAReverserHT * 1.2;
        $tva = $montantAReverserTTC - $montantAReverserHT;
    }

    $content .= "
    <table cellspacing='0' style='width:320px;margin-left:755px;margin-top:60px;border:1px solid black;'>
    <tr style='width:100%;text-align:center;'>
        <td style='width:70%;border-right:1px solid black;border-bottom:1px solid black;padding-top:5px;padding-bottom:5px;font-weight:normal'>MONTANT À REVERSER HT</td>
        <td style='width:30%;border-bottom:1px solid black;'>" . number_format($montantAReverserHT, 2, ',', ' ') . " €</td>
    </tr>
    <tr style='width:100%;text-align:center;'>
        <td style='width:70%;border-right:1px solid black;border-bottom:1px solid black;padding-top:5px;padding-bottom:5px;font-weight:normal'>TVA $exonereTva</td>
        <td style='width:30%;border-bottom:1px solid black;'>" . number_format($tva, 2, ',', ' ') . " €</td>
    </tr>
    <tr style='width:100%;text-align:center;'>
        <td style='width:70%;border-right:1px solid black;padding-top:5px;padding-bottom:5px;font-weight:normal'>MONTANT À REVERSER TTC</td>
        <td style='width:30%;'>" . number_format($montantAReverserTTC, 2, ',', ' ') . " €</td>
    </tr>";
    $content .= "</table>";

    $content .= "<page_footer backbottom='20mm' style='color:black;font-size:10px;text-align:center'>";

    // Centre
    $content .= "Khapeo – ProStgagePermis – Oxydium Concet Bât. A – 190 rue Marcelle Isoard – CD9 – 132900 AIX LES MILLES<br />
    E-mail : contact@prostagespermis.fr – SIRET : 504 209 057 00034 – RCS Aix-en-Provence : B 504 209 057 – APE 6312Z
";

    $content .= "</page_footer>";

    /////// OLD

    $content .= "</body>";

    echo $content;
    $content = ob_get_clean();

    $nomRecapitulatifVirement = "Recapitulatif_Virement_En_Attente.pdf";
    $dossier = "/home/prostage/www/facture_centre/tmp/";
    $name_pdf = $nomRecapitulatifVirement;
    $file_path = $dossier.$name_pdf;

    require_once('/home/prostage/html2pdf_v4.03/html2pdf.class.php');
    $html2pdf = new HTML2PDF('L','A4','fr', true, 'UTF-8');
    $html2pdf->WriteHTML($content);
    $html2pdf->Output($file_path, 'F');

    header('Content-disposition: attachment; filename='. $nomRecapitulatifVirement);
    header('Content-type: application/pdf');
    readfile($file_path);

    unlink($file_path);
}

function get_commission_ht($id_membre, $paiement, $membre_comm, $min_comm, $max_comm, $dateInscription)
{
    $commission_ht = $paiement * ($membre_comm / 100);

    //rppc
    if ($id_membre == 793) {
        $from = new DateTimeImmutable('01-02-2019');
        $to   = new DateTimeImmutable('01-05-2020');

        if ($dateInscription->getTimestamp() > $from->getTimestamp() && $dateInscription->getTimestamp() < $to->getTimestamp()) {
            if ($paiement <= 139)
                $commission_ht = $paiement * 0.15;
            else
                $commission_ht = $paiement * 0.22;
        } else {

            $commission_ttc = $paiement - 220;
            $commission_ht  = $commission_ttc / 1.2;
        }

        return $commission_ht;
    }

    // abc permis
    if ($id_membre == 921) {
        if ($paiement <= 139)
            $commission_ht = $paiement * 0.15;
        else
            $commission_ht = $paiement * 0.22;
    }

    $min_comm = is_numeric($min_comm) ? $min_comm : "36.8";
    $max_comm = is_numeric($max_comm) ? $max_comm : "43";

    if ($commission_ht < $min_comm)
        $commission_ht = $min_comm;
    else if ($commission_ht > $max_comm)
        $commission_ht = $max_comm;

    return $commission_ht;
}

function vide($val)
{
    if (is_null($val) || empty($val) || $val == '' || strlen($val) == 0)
        return 1;
    else
        return 0;
}

function formatPrix($prix)
{
    return number_format($prix, 2, ',', ' ');
}

/**
* Génère la nouvelle numérotation des factures des centres
*
* @author Daniel Bertoni
* @date   13/04/2021
* @param  int id membre (centre)
* @return array tableau indexé sur les anciens id factures
*/

function getNewListeFacturesId( $id_membre )
{
	include("/home/prostage/connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	// Récupération des id de factures post mars 2021

	$S_selectId = 'SELECT fc.id from facture_centre as fc ' .
    	              'INNER JOIN virement as v ON fc.id_membre=v.id_membre AND ' .
    	              'DATE_ADD(DATE_FORMAT(fc.date, \'%Y-%m-%d\'), INTERVAL 2 DAY) = DATE_FORMAT(v.date, \'%Y-%m-%d\') ' .
    	              'WHERE fc.id_membre=' . $id_membre . ' AND fc.date >= "2021-03-01"';

	$R_rs = mysql_query($S_selectId, $stageconnect);

	$i = 0;
	while ($row = mysql_fetch_row($R_rs))
	{
		$tab[++$i] = $row[0];
	}

	// Inversion Clés / Valeurs
	$A_return = array_flip($tab);

	return $A_return;
}
?>