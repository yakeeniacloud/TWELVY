<?php

require_once('../../common_bootstrap2/config.php');

$type_page = TYPE_PAGE_POPUP;

?><!DOCTYPE html>
<html lang="fr">
<head>
<title>Envoyer un message au stagiaire</title>
    <meta name="robots" content="noindex,nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="//www.prostagespermis.fr/affilies_bootstrap/assets/css/old-style.css" type="text/css" />
    <?php include("includes/header.php"); ?>
</head>

<body class="popup">
<h3>Envoyer un message au stagiaire</h3>

<?php

if (!empty($_GET['id'])) {
    $id = $_GET['id'];
}
else {
    echo "<div align='center'><strong><font color='red'>Impossible d'identifier le stagiaire. Contactez notre hotline.</strong></font></div>";
    exit;
}

$sql = "SELECT stagiaire.*, stage.date1, stage.date2, site.ville, membre.email as membre_email, membre.nom as  membre_nom, transaction.id as id_transaction
        FROM stagiaire
                inner join transaction on transaction.id_stagiaire = stagiaire.id
                inner join stage on transaction.id_stage = stage.id
                inner join site on stage.id_site = site.id
                inner join membre on transaction.id_membre = membre.id
        WHERE stagiaire.id=$id AND membre.id = $membre";
$rs = mysql_query($sql) or die(mysql_error());
$row_stagiaire = mysql_fetch_assoc($rs);
$total = mysql_num_rows($rs);

if ($total != 1)
{
    echo "Error stagiaire introuvable";
    exit;
}


$texte_date = MySQLDateToExplicitDate($row_stagiaire['date1'],1,0) . ' et '.MySQLDateToExplicitDate($row_stagiaire['date2']);
$row_stagiaire['prenom'] = firstToUpper($row_stagiaire['prenom']);
$row_stagiaire['ville'] = firstToUpper($row_stagiaire['ville']);
$row_stagiaire['membre_nom'] = firstToUpper($row_stagiaire['membre_nom']);

if (!empty($_POST['msg']))
{
	if (strlen($_POST['msg']) > 2)
	{
        $msg = Rec($_POST['msg']);
		$contact = "contact@prostagespermis.fr";

		$headers = "From: PROStagesPermis <contact@prostagespermis.fr>\n";
		$headers .= "Reply-To: ".$row_stagiaire['membre_email']."\n";
		$headers .= 'MIME-version: 1.0'."\n";
		$headers .= 'Content-type: text/html; charset= iso-8859-1'."\n";

//        sendEmail($transactionID, $nom_autoecole, $not_centre, $provenance_pubs, $site , $lien_cb, $send_email , $fiche_jointe , $type_email , $subject , $titre_email , $contenu_email )

        insert_message_centre($membre, '', $msg, 'stagiaire', $_SESSION['nom'], $row_stagiaire['id_utilisateur'], 1, 1);

        insert_message_utilisateur($row_stagiaire['id_utilisateur'], '', $msg, 'bsr', 'centre');

        sendEmail($row_stagiaire['id_transaction'], '', false, 0, $site, 0, true, TYPE_EMAIL_LIBRE, false, false, false, '', '', $msg);

//		mail($row_stagiaire['email'], $objet, $msg, $headers);
//		sleep(1);
//		mail($row_stagiaire['membre_email'], $objet, $msg, $headers);
//		sleep(1);
//		$msg2 = "<b><em>Message de ".$row_stagiaire['membre_nom']." envoyé à ".$row_stagiaire['email'].":</em></b><br><br>:";
//		$msg2 .= $msg;
//		mail($contact, $objet." - ".$row_stagiaire['membre_nom'], $msg2, $headers);

		echo "<br><br>";
		echo "<div align=\"center\" style=\"margin-top:30px\"><b>Votre message a été envoyé.<br>Une copie vous a été adressée.</b>";
		echo "<br><br>";
		echo "</div>";

        echo '<br /><br /><br />
            <div class="text-center">
                <input type="button" value="FERMER" class="btn btn-default" onclick="closeTinyBox();">
            </div>';

		exit;
	}
}

?>

<script type="text/javascript">
    function remplir_mail() {
        var type_email = $('#predefinis').val();
        switch (type_email) {
            case 'relance_candidat_documents' :
                $('#msg').val("<?php echo sprintf($textes_par_defaut['relance_candidat_documents'], $row_stagiaire['prenom'],$texte_date,  $row_stagiaire['ville'], $row_stagiaire['membre_nom']); ?>");
                break;
            case 'relance_candidat_paiement' :
                $('#msg').val("<?php echo sprintf($textes_par_defaut['relance_candidat_paiement'], $row_stagiaire['prenom'],$texte_date,  $row_stagiaire['ville'], $row_stagiaire['membre_nom']); ?>");
                break;
        }
    }
</script>

<form action='' method='post' id='messageStagiaire' name='messageStagiaire'>
    <input name='stagiaireID' type='hidden' id='stagiaireID' value="<?php echo $id; ?>">
    <input name="email_stagiaire" type="hidden" value="<?php echo $row_stagiaire['email']; ?>" />

    <div class="popup-row first">
        <div class="popup-field col-xs-12">
            <span class="title col-xs-4">Type de message </span>
            <select id="predefinis" onchange="remplir_mail()">
                <option value="">Message libre</option>
                <option value="relance_candidat_documents">Relance du stagiaire - Dossier non reçu</option>
                <option value="relance_candidat_paiement">Relance du stagiaire - Paiement non reçu</option>
            </select>
        </div>
    </div>
    <div class="popup-row">
        <div class="popup-field col-xs-12">
            <span class="title col-xs-4">Message </span>
            <textarea id="msg" name="msg" rows="10" class="col-xs-7"></textarea><br />
        </div>
    </div>
    <!--
	<div class="popup-row">
        <div class="popup-field col-xs-12">
            <span class="text text-center col-xs-12"><span class="glyphicon glyphicon-info-sign font-blue"></span> La fiche d'inscription du stagiaire sera ajoutée à la suite de votre message</span>
        </div>
    </div>
	-->
    <div class="popup-row">
        <div class="popup-field col-xs-12 text-center">
            <br />
            <input type="submit" name="submit" value="ENVOYER" class="btn btn-green" onclick="return verifMail();" />
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type="button" value="FERMER" class="btn btn-default" onclick="closeTinyBox();">
        </div>
    </div>
</form>

</body>
</html>