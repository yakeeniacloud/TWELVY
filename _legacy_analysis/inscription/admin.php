<?php
session_start();

if(!isset($_SESSION['id']))
{
	header('Location: ident_stagiaire.php');
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Administration stagiaires:</title>
</head>
<body>

<?php
	if (isset($_GET['action']))
	{
		if ($_GET['action'] == "inscrit")
		{
			include '../cb_effectuee.php';
			$localId = $_GET['id_transaction'];
			sendEMail($localId,1);	
		}
	}
	
	include ("../Connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);
	
	$query_stagiaire = sprintf("SELECT transaction.id AS transactionID, stagiaire.*, stage.date1, site.ville AS siteVille, membre.nom AS nomMembre FROM transaction, stagiaire, stage, site, membre WHERE transaction.id_stagiaire=stagiaire.id AND stagiaire.id_stage = stage.id AND stage.id_site = site.id AND stage.id_membre = membre.id");
	$rsStagiaire = mysql_query($query_stagiaire, $stageconnect) or die(mysql_error());
	$row_stagiaire = mysql_fetch_assoc($rsStagiaire);
	$totalRows_stagiaire = mysql_num_rows($rsStagiaire);
?>

<table width="100%" border="1">
<tr bgcolor="#333333" style="color:#FFFFFF">
	<td width="5%">Id-S</td>
	<td width="12%">Nom / Prenom</td>
	<td width="25%">Tél / Email</td>
	<td width="6%">Status</td>
	<td width="7%">D. Préinsc</td>
	<td width="7%">D. Inscr</td>
	<td width="10%">Date Stage</td>
	<td width="10%">Lieu Stage</td>
	<td width="4%">Prix</td>
	<td width="9%">Centre</td>
	<td width="5%">Inscrire</td>
</tr></table>

<?php
for ($i=0; $i<$totalRows_stagiaire; $i++)
{
?>
<table width="100%" border="1" style="font-size:12px">
<form id="form1" name="form1" method="post" action="stagiaire.php">
	<td  width="5%"><?php echo $row_stagiaire['id'];?></td>
	<td  width="12%"><?php echo $row_stagiaire['nom']." ".$row_stagiaire['prenom'];?></td>
	<td  width="25%"><?php echo $row_stagiaire['tel']." ".$row_stagiaire['mobile']." ".$row_stagiaire['email'];?></td>
	<td  width="6%"><?php echo $row_stagiaire['status'];?></td>
	<td  width="7%"><?php echo $row_stagiaire['date_preinscription'];?></td>
	<td  width="7%"><?php echo $row_stagiaire['date_inscription'];?></td>
	<td  width="10%"><?php echo $row_stagiaire['date1'];?></td>
	<td  width="10%"><?php echo $row_stagiaire['siteVille'];?></td>
	<td  width="4%"><?php echo $row_stagiaire['paiement'];?></td>
	<td  width="9%"><?php echo $row_stagiaire['nomMembre'];?></td>
	<td  width="5%">
	<?php
	if ($row_stagiaire['status'] == "pre-inscrit")
	{
	?>	
	<a href="admin.php?action=inscrit&id_transaction=<?php echo $row_stagiaire['transactionID'];?>"><img src="../affilies/images/b_props.png" alt="passer le stagiaire en status INSCRIT" onClick='javascript:return(confirm("Etes vous sur de vouloir inscrire definitivement ce stagiaire ? Un mail de validation de son inscription lui sera envoye"))'/>
	<?php
	}?>	
	</a>
	</td>
</form>
</table>
<?php
$row_stagiaire = mysql_fetch_assoc($rsStagiaire);
}?>

</body>
</html>
