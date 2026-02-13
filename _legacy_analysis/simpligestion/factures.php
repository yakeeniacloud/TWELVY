<?php
require_once('../../common_bootstrap2/config.php');


$type_page = TYPE_PAGE_FACTURES;


?><!DOCTYPE html>
<html lang="fr">
<head>
    <title>Factures</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php header('Access-Control-Allow-Origin: *'); ?>
    <?php include("includes/header.php"); ?>
	
	<link href="font-awesome-4.6.3/css/font-awesome.css" rel="stylesheet">
	<style>
	th, td {
		text-align:center;
	}
	.fa-2x {
		font-size: 1.5em;
	}
	</style>

</head>

<body class="contentspage">

    <?php include("includes/topbar.php"); ?>

	<div id="loading-overlay"></div>

    <div id="content">

        <?php include("includes/search_bar.php"); ?>

        <div class="contentwhite">
			<div class="col-md-8 col-sm-8 col-md-offset-2" style="margin-bottom:10px;">
				<select id='select_facture' style='padding:5px;float:left'>
					<option value='0'>Toutes les factures</option>";
					<option value='1'>Factures en attente de virement</option>
				</select>
				
				<span id="lien_telechargement" style="float:right;padding-top:5px"></span>
				<select id="date" name="date" onchange="changedate(this.value)" style="float:right;margin-right:10px;padding:5px">
					<?php
					echo "<option value=''>Téléchargement</option>";
					for ($annee=2016; $annee<=2018; $annee++) {
						for ($mois=1; $mois<=12; $mois++) {
							$val = $annee.$mois;
							echo "<option value='$val'>".sprintf("%02d",$mois)." / ".$annee."</option>";
						}
					}			
					?>
				</select>
				
			</div>
			
            <div class="container" id="contenu_facture">			
			</div>
	</div>
</body>
</html>

<script LANGUAGE="JavaScript">

$(document).ready(function() {
	affiche();
});

function changedate(mois)
{
	document.getElementById('lien_telechargement').innerHTML = '<a href="ajax_telecharge_factures.php?date='+mois+'">Zip</a>';
}

function virement(id_facture, id_stage, id_formateur)
{
	var xhr = getXhr();

	if (confirm('Etes vous sur de vouloir effectuer le virement ?')) {
		$('#loading-overlay').show();
		xhr.onreadystatechange = function()
		{
			// On ne fait quelque chose que si on a tout reçu et que le serveur est ok
			if(xhr.readyState == 4 && xhr.status == 200)
			{
				$('#loading-overlay').hide();
				affiche();
				alert(xhr.responseText);
			}
		}

		xhr.open("POST","ajax_virement_formateur.php", true);
		xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		xhr.send('id_facture='+id_facture+'&id_stage='+id_stage+'&id_formateur='+id_formateur);
	}
}

function affiche()
{	
	var xhr = getXhr();
	
	var filtre = document.getElementById('select_facture').value;

	$('#loading-overlay').show();
	xhr.onreadystatechange = function()
	{
		// On ne fait quelque chose que si on a tout reçu et que le serveur est ok
		if(xhr.readyState == 4 && xhr.status == 200)
		{
			$('#loading-overlay').hide();
			document.getElementById('contenu_facture').innerHTML = xhr.responseText;
		}
	}

	xhr.open("POST","ajax_affiche_facture_formateur.php", true);
	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	xhr.send('filtre='+filtre);			
}
</script>