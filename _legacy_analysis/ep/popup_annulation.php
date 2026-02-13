<?php

require_once('../../common_bootstrap2/config.php');

$type_page = TYPE_PAGE_POPUP;

?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta name="robots" content="noindex,nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="//www.prostagespermis.fr/affilies_bootstrap/assets/css/old-style.css" type="text/css" />
    <?php include("includes/header.php"); ?>
    <title>Annulation du stagiaire</title>

</head>
<body class="popup">
<h3>ANNULATION DU STAGIAIRE</h3>

<?php


$types_annulation = array(
    'Ne souhaite plus faire le stage',
	'Dossier non réceptionné / Injoignable',
	'Absent le jour du stage',
	'Souhaite l\'effectuer plus tard',
	'A encore 12/12',
	'En attente de 48N',
	'Stage de moins de un an',
	'Lettre 48 SI',
    'Stage annulé faute de participants',
    'Stage annulé faute d\'animateurs',
    'Stage complet (paiement réceptionné trop tard)',	
    'A réservé auprès d\'un autre prestataire',
    'Fausse inscription', 
    'Coordonnées incorrectes',
    'Doublon'
);

/*
En attente 48N
Dossier non réceptionné / stagiaire injoignable
Absent le jour du stage
Lettre 48 SI (mail)

Centre :
Stage annulé faute de participants
Stage annulé faute d?animateurs
Stage complet (paiement réceptionné trop tard)
*/

$id_stagiaire = null;


if (!empty($_REQUEST['id'])) {
    
    $id_stagiaire = $_REQUEST['id'];
    
    $query_stagiaire = "SELECT  stagiaire.*,
                            membre.*,
                            stage.*

                    FROM stagiaire inner join stage on stagiaire.id_stage = stage.id inner join membre on stage.id_membre = membre.id

                    WHERE   stagiaire.id = $id_stagiaire";

//        echo '$query_stagiaire : '.$query_stagiaire;

    $rsStagiaire = mysql_query($query_stagiaire) or die(mysql_error());
    $row_stagiaire = mysql_fetch_assoc($rsStagiaire);
    
}

if (isset($_POST['motif']))
{
	$id_motif = $_POST['motif'];

	annule1($id_stagiaire, $id_motif, $membre);

    echo "<div class='container'><br /><br /><b>L'annulation de ce stagiaire est confirmée.</b></p><p>Un email (accompagné de son motif) a été envoyé au stagiaire.</p><p>Une copie vous a été adressée.</p>
        </div>";

    echo '<br /><br /><br />
            <div class="text-center">
                <input type="button" value="FERMER" class="btn btn-default" onclick="closeTinyBox();">
            </div>';

    exit();
}

?>

<div class="container">
    <p style="margin: 40px 0;"><b>Vous êtes sur le point d'annuler un stagiaire.</b><br />Veuillez en préciser la raison afin que nos conseillers client puissent recontacter le stagiaire dans les plus brefs délais</p>
</div>

<?php
if (!empty($id_stagiaire))
{
?>
	<form action="" id="popup_annulation" name="popup_annulation" method="post">

        <div class="popup-row first">
            <div class="popup-field col-xs-12">
                <div class="container">
                    <p class="col-xs-12 font-bold">Choisissez un motif d'annulation :</p>
                    <select id="motif" name="motif" class="col-xs-12" style="color:gray" onchange="$('#motif').css('color', 'black')">
                        <option value="">Motifs d'annulation</option>
                        <?php
                            foreach($types_annulation as $id_motif => $type_motif) {
                                echo '<option style="color:black;" value="'.$id_motif.'">'.$type_motif.'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <br /><br />
        <div class="popup-row">
            <div class="popup-field col-xs-12 text-center">
                <br />
                <input type="submit" value="VALIDER" class="btn btn-green" onclick="return verifMotif();"/>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <input type="button" value="FERMER" class="btn btn-default" onclick="closeTinyBox();">
            </div>
        </div>
        
	</form>
<?php
}
?>

<script>
function verifMotif()
{
	if ($("#motif").val() == '')
	{
		alert ("Veuillez choisir un motif");
		return false;
	}

	return true;
}
</script>

<?php
function annule1($id, $id_motif, $membre)
{
    global $types_annulation;
	
	//require_once("/home/prostage/common_bootstrap/sms.php");
	require_once("/home/prostage/www/planificateur_tache/newsletter/functions.php");
	require "../modules/module.php";

	$date_suppr = date("Y-m-d");
	$localId = $id;
    $id_motif = mysql_real_escape_string($id_motif);
    $type_email = null;

    $motif = $types_annulation[$id_motif];
	$str = 'TYPE_EMAIL_ANNULATION_MOTIF_'.($id_motif+1);
    $type_email = constant($str);

	$sql = "UPDATE stagiaire SET motif_annulation=\"$motif\" WHERE id=\"$id\"";
	mysql_query($sql);

    $req1 = "SELECT 
				stage.nb_preinscrits, 
				stage.nb_inscrits, 
				stage.id, stagiaire.email AS stagiaire_email, 
				stagiaire.status AS stagiaire_status,
				stagiaire.prenom,
				stagiaire.tel,
				stagiaire.mobile
			FROM 
				stage, stagiaire
            WHERE 
				stagiaire.id = $localId AND
                stage.id = stagiaire.id_stage AND
                stage.id_membre = $membre";

	$rsReq1 = mysql_query($req1) or die(mysql_error());
	$row_rsReq1 = mysql_fetch_assoc($rsReq1);
	$totalRows_rsReq1 = mysql_num_rows($rsReq1);

    
	if ($totalRows_rsReq1 == 1)
	{
        $ident = $row_rsReq1['id'];
		if ($row_rsReq1['stagiaire_status'] == "pre-inscrit")
		{
			$nb = $row_rsReq1['nb_preinscrits'] - 1;
			$ident = $row_rsReq1['id'];
			mysql_query("UPDATE stage SET nb_preinscrits = $nb, nb_places_allouees = nb_places_allouees +1 WHERE id = $ident AND id_membre = $membre");
		}
		else if ($row_rsReq1['stagiaire_status'] == "inscrit")
		{
			$nb = $row_rsReq1['nb_inscrits'] - 1;
			$ident = $row_rsReq1['id'];
			mysql_query("UPDATE stage SET nb_inscrits = $nb, nb_places_allouees = nb_places_allouees +1 WHERE id = $ident AND id_membre = $membre");
		}

		$trainingApi    =   new \App\Actions\Api\TrainingApiAction();
		$trainingApi->updateDataStageApi($ident);

        // SUPPRESSION DES ANCIENS HORAIRES DU STAGIAIRE S'IL A CHANGE
        /*$sql = "update stage_horaire set id_stagiaire = null where id_stage = $ident and id_stagiaire = $localId";
        $res = mysql_query($sql) or die(mysql_error());*/

        annulerStagiaire($localId, $type_email);
		
		$senderlabel = "IDStages";
		$prenom = html_entity_decode($row_rsReq1['prenom'], ENT_NOQUOTES, "ISO-8859-1");
		$msg = "Important! ".$prenom.", votre inscription au stage de récupération de points n'a pas pu être maintenue. Plus de précisions dans votre boîte mail, IdStages";
		
		
		ob_start();
		sendSms($senderlabel, $msg, $row_rsReq1['tel'], $row_rsReq1['mobile']);
		ob_get_clean();
		
		//mailchimp_unsubscribe_psp_stagiaire($row_rsReq1['stagiaire_email']);
		
		
	}
	else
	{
		echo "<script>alert('Erreur annulation stagiaire - merci de nous contactez rapidement notre hotline');</script>";
	}

}
?>
</body>
</html>