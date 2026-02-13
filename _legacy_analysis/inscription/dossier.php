<?php
session_start();

if(!isset($_SESSION['id']))
{
	header('Location: ident_stagiaire.php');
}
else
{
	$id_transaction = $_SESSION['id'];

	include ("../Connections/stageconnect.php");

	mysql_select_db($database_stageconnect, $stageconnect);
	$query_transaction = sprintf("SELECT transaction.* FROM transaction WHERE transaction.id = $id_transaction");
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
	}

	mysql_select_db($database_stageconnect, $stageconnect);

	//requete stagiaire

	$query_stagiaire = sprintf("SELECT stagiaire.* FROM stagiaire WHERE
										stagiaire.id = $stagiaire_ID");

	$rsStagiaire = mysql_query($query_stagiaire, $stageconnect) or die(mysql_error());
	$row_stagiaire = mysql_fetch_assoc($rsStagiaire);
	$totalRows_stagiaire = mysql_num_rows($rsStagiaire);


	//requete stage

	$query_stage = sprintf("SELECT stage.*, site.nom, site.ville, site.adresse, site.code_postal FROM stage, site
							WHERE stage.id = $stageID AND stage.id_site = site.id");

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

	mysql_close($stageconnect);

	$dateLocal = date("y-m-d");

}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="robots" content="noindex,nofollow" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Votre dossier d'inscription</title>
</head>
<body>

<link rel="stylesheet" href="../themes/default/formulaire-inscription.css" type="text/css" />

	<table width="650px" style="font-size:14px">
	<tr>
		<td><img src="../images/logo.jpg" width="190" height="80" align="left"/></td>
		<td align="right"><strong><?php echo $row_stagiaire['nom']." ".$row_stagiaire['prenom']." (".substr($row_stagiaire['cas'],0,5).")";?><br />
					<?php echo $row_stagiaire['tel']."   ".$row_stagiaire['email'];?><br />
					<?php echo MySQLDateToExplicitDate($row_stage['date1'])." (".$row_stage['prix']." eruos)";?><br />
					<?php echo $row_stage['nom']." ".$row_stage['ville'];?>						
			</strong>		</td>
	</tr>
	</table>

	<?php
	if ($row_stagiaire['status'] == "pre-inscrit")
	{
	?>
	<strong>FICHE DE PRE-INSCRIPTION A RETOURNER A L'ADRESSE DU CENTRE ORGANISATEUR</strong>
	<?php
	}
	else
	{
	?>
	<strong>VOTRE FICHE D'INSCRIPTION</strong>
	<?php
	}?>
	<table width="650px" style="font-size:14px">
		<tr>
			<td>
				<fieldset><legend><strong>Le stage:</strong></legend>
				<table>
						<tr>
							<td><p class="texte_contact">Dates:</p></td>
							<td><?php echo MySQLDateToExplicitDate($row_stage['date1']);?><br />
								<?php echo MySQLDateToExplicitDate($row_stage['date2']);?></td>
						</tr>
						
						<tr>
							<td><p class="texte_contact">Lieu:</p></td>
							<td>
							<?php echo $row_stage['nom'];?><br />
							<?php echo $row_stage['adresse'];?><br />
							<?php echo $row_stage['code_postal']." ".html_entity_decode($row_stage['ville']);?></td>
						</tr>

						<tr>
							<td><p class="texte_contact">Horaires:</p></td>
							<td><?php echo $row_stage['debut_am']." ".$row_stage['fin_am']." et ".$row_stage['debut_pm']." ".$row_stage['fin_pm'];?></td>
						</tr>

						<tr>
							<td><p class="texte_contact">Prix:</p></td>
							<td><?php
								if ($row_stagiaire['remise'] > 0)
								{
								echo ($row_stage['prix']-intval($row_stage['prix']*$row_stagiaire['remise']/100))."euros (au lieu de ".$row_stage['prix'].")";}
								else
								{
								echo $row_stage['prix']." euros";	
								}
								?></td>
						</tr>

				</table>
				</fieldset>
			</td>
			
			<td>
				<fieldset><legend><strong>Le centre organisateur:</strong></legend>
				<table>
						<tr>
							<td><p class="texte_contact">Nom:</p></td>
							<td><?php echo $row_membre['nom']; ?></td>
						</tr>

						<tr>
							<td><p class="texte_contact">Adresse:</p></td>
							<td><?php echo $row_membre['adresse']; ?></td>
						</tr>

						<tr>
							<td><p class="texte_contact">Téléphone:</p></td>
							<td><?php echo $row_membre['tel']."  ".$row_membre['mobile']; ?></td>
						</tr>
						<tr>
							<td><p class="texte_contact">Email:</p></td>
							<td><?php echo $row_membre['email']; ?></td>
						</tr>
						<tr>
							<td><p class="texte_contact">Fax:</p></td>
							<td><?php echo $row_membre['fax']; ?></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
				</table>
				</fieldset>
			</td>

			
		</tr>

		<tr>
		<td colspan="2">
			<fieldset><legend><strong>Le stagiaire:</strong></legend>
			<table width="650px">
				<tr>
					<td><p class="texte_contact">Identifiant:</p></td>
					<td><?php echo $row_stagiaire['id'];?></td>
				</tr>
				
				<tr>
					<td width="30%"><p class="texte_contact">Date de pre-inscription:</p></td>
					<td width="70%"><?php echo MySQLDateToExplicitDate($dateLocal);?></td>
				</tr>

				<tr>
					<td><p class="texte_contact">Nom Prénom:</p></td>
					<td><?php echo stripslashes($row_stagiaire['nom']." ".$row_stagiaire['prenom']);?></td>
				</tr>

				<tr>
					<td><p class="texte_contact">Né(e) le:</p></td>
					<td><?php echo $row_stagiaire['date_naissance']." à ".stripslashes($row_stagiaire['lieu_naissance']);?></td>
				</tr>

				<tr>
					<td><p class="texte_contact">Adresse:</p></td>
					<td><?php echo stripslashes($row_stagiaire['adresse']);?></td>
				</tr>

				<tr>
					<td></td>
					<td><?php echo $row_stagiaire['code_postal']." ".stripslashes($row_stagiaire['ville']);?></td>
				</tr>

				<tr>
					<td><p class="texte_contact">Téléphone:</p></td>
					<td><?php echo $row_stagiaire['tel']."    ".$row_stagiaire['mobile'];?></td>
				</tr>

				<tr>
					<td><p class="texte_contact">Email:</p></td>
					<td><?php echo $row_stagiaire['email'];?></td>
				</tr>

				<tr>
					<td><p class="texte_contact">Permis:</p></td>
					<td>N° <?php echo $row_stagiaire['num_permis']." le ".$row_stagiaire['date_permis']." &agrave;&nbsp;".stripslashes($row_stagiaire['lieu_permis']);?></td>
				</tr>
				
				<tr>
					<td><p class="texte_contact">Type de stage:</p></td>
					<td><?php echo $row_stagiaire['cas'];?></td>
				</tr>

				<tr>
					<td colspan="2"><div align="justify"><br />
					Merci d'envoyer au plus tôt votre fiche de pré-inscription par courrier
en imprimant directement cette page ou l'email qui vous sera envoyé dans quelques instants à l'adresse du centre organisateur. N'oubliez pas de signer la fiche et d'y joindre impérativement les pièces suivantes: </div>
					  <ul>
							<li>Photocopie de<strong> l'intérieur de votre permis</strong> (coté avec votre photo) ou, en cas de suspension la notification,</li>
							
							<?php
							if (isset($_POST['type_paiement']) && $_POST['type_paiement'] == "cheque")
							{?>														
							<li>Un chèque de <strong><?php 
								echo $row_stage['prix']-intval($row_stage['prix']*$row_stagiaire['remise']/100)." ";?></strong> euros à l'ordre de <strong><?php echo $row_membre['nom'];?></strong></li>
							<?php
							}
							?>
														
							<li>ATTENTION JEUNES CONDUCTEURS : si <strong>48 N</strong>, nous envoyer la photocopie</li>
					  </ul>
						<br />
						Retournez votre dossier complet par courrier 
						
				  <?php
				  if (strtotime($row_stage['date1']) - strtotime("now") < 518400)
				  {
				  	echo " sous 48H ";
				  }
				  else
				  {
				  	echo " sous 4 jours ";
				  }
				  ?>						
						
						à:<br /><br />
<div align="center">
<?php echo $row_membre['nom'];?><br />
<?php echo $row_membre['adresse'];?>
</div>
						
						<div align="justify"><br /><br />
						<br />
						BON POUR ACCORD<br />
						Date et Signature:
						<br />
						<br />
				        </div></td>
				</tr>
				<tr>
					<td><input type="button" value="Cliquez ici pour imprimer votre fiche" onclick="javascript: window.print()"/></td>
					<td align="right"><input type="button" value="Retour à l'accueil                " onClick='javascript:location="../index.php"'/></td>
				</tr>
		</table>
		    </fieldset>
		</td>
		</tr>

	</table>

<?php
	Function MySQLDateToExplicitDate($MyDate, $WeekDayOn=1, $YearOn=1)
	{
		$MyMonths = array("Janvier", "Fevrier", "Mars", "Avril", "Mai", "Juin",
			"Juillet", "Aout", "Septembre", "Octobre", "Novembre", "Decembre");
		$MyDays = array("Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi",
					  "Vendredi", "Samedi");

		$DF=explode('-',$MyDate);
		$TheDay=getdate(mktime(0,0,0,$DF[1],$DF[2],$DF[0]));

		$MyDate=$DF[2]." ".$MyMonths[$DF[1]-1];
		if($WeekDayOn){$MyDate=$MyDays[$TheDay["wday"]]." ".$MyDate;}
		if($YearOn){$MyDate.=" ".$DF[0];}

		return $MyDate;
	}
	?>

</body>

<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-7628545-1");
pageTracker._trackPageview();
} catch(err) {}</script>

</html>
