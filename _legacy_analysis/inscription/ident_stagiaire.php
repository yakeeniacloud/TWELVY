<?php
	include ("../Connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);
	$erreur ="";

	if (isset($_POST['identifiant']))
	{
   		if ((isset($_POST['identifiant']) && !empty($_POST['identifiant'])) &&
			(isset($_POST['nom']) && !empty($_POST['nom'])))
		{

			if ($_POST['identifiant']=="PROStagesPermis" && $_POST['nom']=="kadhak13")
			{ 
				mysql_close($stageconnect);
				session_start();
				$_SESSION['id'] = $_POST['nom'];
				header('Location: admin.php');
			}
			
			$ident = $_POST['identifiant'];
			$name = $_POST['nom'];
			
			$query_stagiaire = "SELECT stagiaire.* FROM stagiaire WHERE id='$ident' AND nom='$name'";
			$rsStagiaire = mysql_query($query_stagiaire, $stageconnect) or die(mysql_error());
			$row_Stagiaire = mysql_fetch_assoc($rsStagiaire);
			$totalRows_rsStagiaire = mysql_num_rows($rsStagiaire);

			if ($totalRows_rsStagiaire == 1)
			{
				//requete stagiaire
				$tmp = $row_Stagiaire['id'];
				$squery_transaction = sprintf("SELECT transaction.id FROM transaction WHERE
													transaction.id_stagiaire = $tmp");
				$rsTransaction = mysql_query($squery_transaction, $stageconnect) or die(mysql_error());
				$row_transaction = mysql_fetch_assoc($rsTransaction);
				$totalRows_transaction = mysql_num_rows($rsTransaction);				
				
				if ($totalRows_transaction == 1)
				{
					$tmp = $row_transaction['id'];
					session_start();
					$_SESSION['id'] = $tmp;
					mysql_close($stageconnect);
					header('Location: dossier.php');
				}
				else
				{
					$erreur = 'Erreur veuillez réessayer plus tard ou contacter notre hotline';
				}
			}
			else
			{
				$erreur = 'Compte non reconnu.';
			}
		}
		else
		{
			$erreur = 'Au moins un des champs est vide.';
		}
	}
	mysql_close($stageconnect);
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/stage-permis-points.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Espace stagiaire</title>
<meta name="robots" content="noindex,nofollow" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceEndEditable -->
<script src="../javascript/prototype.js" type="text/javascript"></script>
<script src="../javascript/scriptaculous.js" type="text/javascript"></script>
<!--<script src="javascript/lightbox.js" type="text/javascript"></script>
<script src="javascript/AC_RunActiveContent.js" type="text/javascript"></script>
-->
<link rel="stylesheet" href="../themes/default/style.css" type="text/css" />

<!--[if lt IE 7.]>
	<script defer type="text/javascript" src="javascript/pngfix.js"></script>
	<style type="text/css">
		@import url(themes/default/iefix.css);
		body{behavior:url(themes/default/csshover.htc);}		
	</style>
<![endif]-->
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->

<script type="text/javascript">
<!--
function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
//-->
</script>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /></head>

<body>

	<!-- InstanceBeginEditable name="stage-permis-points-modif" -->
	<div id="recuperation-points-permis">
	<h1>Espace stagiaire</h1><br />
	<br />
<br />
<form action="ident_stagiaire.php" method="post">
<table border="1" width="50%">
  <caption><strong>Acces à votre dossier</strong></caption>

			    <tr>
					<td width="30%"><strong>Identifiant:</strong></td>
					<td><input type="text" name="identifiant"
			             value="<?php if (isset($_POST['identifiant'])) {echo $_POST['identifiant']; }?>" /></td>
			  	</tr>

			    <tr>
					<td><strong>Nom:</strong></td>
					<td><input type="text" name="nom" /></td>
			  	</tr>

				<tr>
					<td>&nbsp;</td>
					<td><strong><input type="submit" name="connexion" value="Connexion" class="bouton100" />
					</strong></td>
			  	</tr>
</table>
</form>
<br />
<?php
if ($erreur != "")
{
	echo "<font color=#FF0000><strong>$erreur</strong></font>";
}
?>

<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-7628545-1");
pageTracker._trackPageview();
} catch(err) {}</script>

	</div>
	<!-- InstanceEndEditable -->
  
	<div id="bloc_header">	  
	<img src="../images/logo.jpg" class="logo" alt="PROStagesPermis.fr"/>
	<div id="Layer3" style="border-style:outset; border-width: 7px;">
	  <div align="center" class="Style9">Portail National du Permis &agrave; Points.<br />
	    Stages de 
      Récupération de Points de Permis.</div>
	</div>
</div>

	<div id="Layer4">
	  <div align="center"><img src="../images/prefecture2.jpg" alt="agréments préfectures" width="130" height="62"/><br />
	      <span class="Style2 Style17">Centre de Réservation Agréé</span></div>
</div>
	<ul id="menu1" name="menu1">
		<li><a href="../index.php"><span class="menu1_centre"  title="stage de récupération de points de permis">LES STAGES PERMIS</span></a></li>
		<li><a href="../nombre-points-restant.php"><span class="menu1_centre"  title="Consulter le nombre de points restants sur le permis"> POINTS RESTANTS  </span></a></li>
		<li><a href="../conseils.php"><span class="menu1_centre"  title="Que faire en cas d'infraction et verbalisation">CONSEILS PRATIQUES </span></a></li>
		<li><a href="../faq.php"><span class="menu1_centre"  title="Foire aux questions sur le permis &agrave; points">QUESTIONS FREQUENTES </span></a></li>
		<li><a href="../conditions_inscription.php"><span class="menu1_centre"  title="Ce que vous devez savoir avant de vous inscrire">LES CONDITIONS</span></a></li>
		<li><a href="../login.php"><span class="menu1_centre"  title="Espace membres">ESPACE AFFILIES</span></a></li>
		<li><a href="../contact.php"><span class="menu1_centre"  title="Contact">CONTACTEZ NOUS </span></a></li>
</ul>  
	
<div id="hotline"><img src="../images/charmante2.jpg" alt="Une question sur les points du permis ?"/></div>

<div id="sous-hotline"><span class="Style15">09 54 150 306</span><br />
  <span class="sous-menu">  (prix d'un appel local)</span></div>	
	
<div id="column_menu_1">
	 
	<h3>STAGES PERMIS</h3>

	<ul id="menu_list">
        <li><a href="../rattrapage-points.php" class="sous-menu" title="Rattrapage de point de permis. Reprise de point permis">Rattrapage de points </a></li>
		<li><a href="../agrements.php"  class="sous-menu" title="centre agréé de recuperation de point permis">Agrément des centres</a></li>
        <li><a href="../programme.php"  class="sous-menu" title="Programme d'un stage point permis">Le programme</a></li>
        <li><a href="../inscrire-stage.php"  class="sous-menu" title="Comment s'inscrire à un stage permis a points">Comment s'inscrire</a></li>
	</ul> 
	   
	<h3>PERMIS A POINTS</h3>
	   
	<ul id="menu_list">
	<li><a href="../permis-a-points.php"  class="sous-menu" title="Le permis &agrave; points">Le permis à points</a></li>
	<li><a href="../bareme-retrait-points.php"  class="sous-menu" title="retrait point permis">Retrait de points </a></li>
	<li><a href="../permis-probatoire.php"  class="sous-menu" title="Permis et p&eacute;riode probatoire. Stage obligatoire">Permis probatoire</a></li>
	<li><a href="../conduite-sans-permis.php"  class="sous-menu" title="Amendes pour conduite sans permis">Conduire sans permis</a></li>
	<li><a href="../suspension-permis.php"  class="sous-menu" title="La suspension du permis de conduire">Permis suspendu</a></li>
	<li><a href="../annulation-permis.php"  class="sous-menu" title="Annulation du permis de conduire">Permis annulé</a></li>
	<li><a href="../repasser-permis.php"  class="sous-menu" title="Obligation de repasser l'examen du permis de conduire">Repasser son permis</a></li>
	<li><a href="../composition-penale.php"  class="sous-menu" title="La composition p&eacute;nale">Composition pénale</a></li>
	<li><a href="../peine-complementaire.php"  class="sous-menu" title="La peine compl&eacute;mentaire">Peine compl&eacute;mentaire</a></li>
	<li><a href="../alternative-poursuite.php"  class="sous-menu" title="L'alternative &agrave; la poursuite">Alternative poursuite</a></li>
	<li><a href="../legalite.php"  class="sous-menu" title="La l&eacute;galit&eacute; sur le permis &agrave; points">L&eacute;galit&eacute;</a></li>
	</ul>   
	
	<h3>LES LETTRES 48</h3>
	   
	<ul id="menu_list">
	<li><a href="../lettre-48N.php"  class="sous-menu" title="La lettre 48N">Lettre 48N</a></li>
	<li><a href="../lettre-48S.php" class="sous-menu" title="La lettre 48S">Lettre 48S</a></li>
	<li><a href="../lettre-48M.php" class="sous-menu" title="La lettre 48M">Lettre 48M</a></li>
	</ul> 
	
	<h3>ALCOOL AU VOLANT</h3>
	   
	<ul id="menu_list">
	<li><a href="../dangers-alcool.php" class="sous-menu" title="Les dangers de l'alcool au volant">Les risques</a></li>
	<li><a href="../sanctions-alcool.php" class="sous-menu" title="Sanctions conduite en etat d'ébriété - conducteur ivre">Les sanctions</a></li>
	</ul> 
	
	<h3>SECURITE ROUTIERE</h3>
	<ul id="menu_list">
	<li><a href="../articles-securite-routiere.php" class="sous-menu" title="Les articles de la s&eacute;curit&eacute; routi&egrave;re">Nos articles</a></li>
	<li><a href="../tests-psychotechniques.php" class="sous-menu" title="Les tests psychotechniques permis de conduire">Tests psychotechniques</a></li>
	<li><a href="../radars.php" class="sous-menu" title="Les controles radars automatiques et mobiles">Les radars</a></li>
	<li><a href="../temoignages-stagiaires.php" class="sous-menu" title="témoignages et avis des stagiaires ayant assisté à un stage recuperation point permis">Les t&eacute;moignages </a></li>
	<li><a href="../partenaires.php" class="sous-menu" title="Partenaires">Partenaires</a></li>
	</ul>
	
	<h3>ESPACES PRIVES</h3>
	<ul id="menu_list">
	<li><a href="../login.php" class="sous-menu" title="Espace privé centres organisateurs stage points permis">Centres affiliés</a></li>
	<li><a href="ident_stagiaire.php" class="sous-menu" title="Espace privé stagiaires stages permis a points">Stagiaires</a></li>
	</ul>
	
</div>
</body>
<!-- InstanceEnd --></html>