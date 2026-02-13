<?php

session_start();

if(!isset($_SESSION['membre']))
	header('Location: ../login_ep.php');

$membre 		= $_SESSION['membre'];
$id_virement 	= $_GET['id_virement'];

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

include ("../../connections/stageconnect.php");
mysql_select_db($database_stageconnect, $stageconnect);

//requete membre
$sql = "SELECT nom, email, tel, mobile, adresse, siret FROM membre WHERE id=$membre";
$rsMembre = mysql_query($sql, $stageconnect) or die(mysql_error());
$row_membre = mysql_fetch_assoc($rsMembre);

$sql = "SELECT date, commentaire, total FROM virement WHERE id=$id_virement";
$rs = mysql_query($sql, $stageconnect) or die(mysql_error());
$row = mysql_fetch_assoc($rs);
$commentaire = utf8_decode($row['commentaire']);
$virement_effectue = $row['total'];
$date_virement = date('Ymd', strtotime($row['date']));

$sql = "SELECT
				stagiaire.id,
				stagiaire.nom,
				stagiaire.prenom,
				stagiaire.paiement,
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
			FACTURE N° ".$num_facture."<br><br>
			<strong>".strtoupper($row_membre['nom'])."</strong><br>".
			$row_membre['adresse']."<br>".
			"Tél: ".$row_membre['tel']." ".$row_membre['mobile']."<br>".
			"Email: ".$row_membre['email']."<br>".
			"Siret: ".$row_membre['siret']."&nbsp;&nbsp;&nbsp;TVA: ".$row_membre['tva']."
			</td>

			<td style='width:300px;padding-top:100px'>
			<strong>PROStagesPermis</strong><br>Oxydium Concept - Bat A<br>
			190 Rue Marcelle Isoard, CD9<br>
			13290 Aix en Provence Les Milles<br>
			Tél: 04.86.31.80.70<br>
			Email: contact@prostagespermis.fr<br>
			<em>Siret: 50420905700026 - TVA: FR65504209057</em></td>
			</tr>";
$content .= "</table>";

$content .= "<table cellspacing='0' style='width: 100%; font-size:13px; margin-top:20;border:2;padding:3'>";

$content .= "<tr><td style='width:240px;border-width:1px; border-style:solid; background-color:#C2C8C8'>Candidat</td>
			  <td style='width:240px;border-width:1px; border-style:solid; background-color:#C2C8C8'>Stage</td>
			  <td style='width:70px;border-width:1px; border-style:solid; background-color:#C2C8C8;text-align:center'>Paiement</td>
			  <td style='width:70px;border-width:1px; border-style:solid; background-color:#C2C8C8;text-align:center'>Virement</td>
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
	$paiement 		= $row['paiement'];
	$code_postal 	= $row['code_postal'];
	$ville 			= $row['ville'];
	$reduction		= $row['reduction'];
	$comm_autoecole = $row['comm_autoecole'];
	$commission		 = $row['commission'];
	$provenance_site = $row['provenance_site'];
	
	//calcul commission
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
		
	$paiement_total += $paiement;
	$virement = $paiement - $commission_ttc;

	$content .= "<tr>
			<td style='border-width:1px solid grey;'>$nom<br>$prenom</td>
			<td style='border-width:1px solid grey;font-size:12px'>$date1<br>$code_postal $ville</td>
			<td style='border-width:1px solid grey;text-align:center'>$paiement</td>
			<td style='border-width:1px solid grey;text-align:center'>$virement</td>
			<td style='border-width:1px solid grey;text-align:right'>$commission_ht</td>
			</tr>";
	
	$virement_total += $virement;
	$commission_total += $commission_ht;
}

$content .= "</table>";

$content .= "<table style='width:730px;padding-top:20px'>";
$content .= "<tr><td style='width:730px;text-align:right;'>Virement total TTC:\t ".$virement_effectue." euros</td></tr>";
$content .= "<tr><td style='width:730px;text-align:right;'>Commission totale HT:\t ".round((($paiement_total - $virement_effectue)/1.2), 2)." euros</td></tr>";
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

require_once('../../html2pdf_v4.02/html2pdf.class.php');
$html2pdf = new HTML2PDF('P','A4','fr', false, 'ISO-8859-1');
$html2pdf->WriteHTML($content);

$name_pdf = $num_facture.".pdf";

$html2pdf->Output($name_pdf, 'D');


?>