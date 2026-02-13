<?php

session_start();
$id_membre = $_SESSION['membre'];

if (isset($_POST['action']) && !empty($_POST['action'])) {

    $action = $_POST['action'];

    switch ($action) {

        #102
        case 'check_if_user_has_accepted_CGP':
            checkIfUserHasAcceptedCGP($id_membre);
            break;
        #102
        case 'validateCGP':
            validateCGP($id_membre);
            break;
        #102
        case 'updateCGP';
            updateCGP();
            break;
        case 'send_message':
            $type_interlocuteur = $_POST['type_interlocuteur'];
            $id_interlocuteur = $_POST['id_interlocuteur'];
            $type_destinataire = $_POST['type_destinataire'];
            $notifie = $_POST['notifie'];
            $message = $_POST['message'];
            $id_notification = send_notification($type_interlocuteur, $id_interlocuteur, $type_destinataire, $notifie, $message);
            update_notification_centre($id_notification, $id_membre);
            echo $id_notification;
            break;

        case 'affiche_stages':
            $first_date = $_POST['first_date'];
            $end_date = $_POST['end_date'];
            $stagiaires = intval($_POST['stagiaires']);
            $departement = intval($_POST['departement']);
            $site = intval($_POST['site']);
            $status = intval($_POST['status']);
            $display_animator_current_stage = $_POST['display_animator_current_stage'];
            affiche_stages($id_membre, $first_date, $end_date, $stagiaires, $departement, $status, $site, $display_animator_current_stage);
            break;

        case 'delete_stage':
            delete_stage($_POST);
            break;

        case 'pass_sanitaire':
            pass_sanitaire($_POST);
            break;

        case 'partenariat':
            StageSet($_POST['id_membre'], $_POST['id_stage'], $_POST['prix_index_ttc'], $_POST['partenariat']);
            break;

        case 'envoie_msg_centre':
            envoie_msg_centre($_POST, $id_membre);
            break;

        case 'update_fiche_stagiaire':
            update_fiche_stagiaire($_POST);
            break;

        case 'liste_bafm':
            liste_bafm($id_membre);
            break;

        case 'liste_psy':
            liste_psy($id_membre);
            break;

        case 'affiche_lieux':
            affiche_lieux($_POST, $id_membre);
            break;

        case 'affiche_formateurs':
            affiche_formateurs($_POST, $id_membre);
            break;

        case 'ajout_lieu':
            ajout_lieu($_POST, $id_membre);
            break;

        case 'ajout_formateur':
            ajout_formateur($_POST, $id_membre);
            break;

        case 'modifier_lieu':
            modifier_lieu($_POST);
            break;

        case 'get_historic_notifications':
            get_historic_notifications($_POST, $id_membre);
            break;

        case 'modifier_formateur':
            modifier_formateur($_POST);
            break;

        case 'enregistrer_lieu':
            enregistrer_lieu($_POST, $id_membre);
            break;

        case 'enregistrer_formateur':
            enregistrer_formateur($_POST, $id_membre);
            break;

        case 'delete_lieu':
            delete_lieu($_POST, $id_membre);
            break;

        case 'delete_formateur':
            delete_formateur($_POST, $id_membre);
            break;

        case 'update_prix':
            $id_membre = intval($_POST['id_membre']);
            $id_stage = intval($_POST['id_stage']);
            $prix = intval($_POST['prix']);
            $partenaire = intval($_POST['partenaire']);
            update_prix($id_membre, $id_stage, $prix, $partenaire);
            break;

        case 'update_receipt':
            $id_stagiaire = intval($_POST['id_stagiaire']);
            $receipt = intval($_POST['receipt']);
            update_receipt($id_stagiaire, $receipt);
            break;

        case 'update_places':
            $id_stage = intval($_POST['id_stage']);
            $place = intval($_POST['place']);
            update_places($id_stage, $place);
            break;

        case 'update_max_places':
            $id_stage = intval($_POST['id_stage']);
            $place = intval($_POST['place']);
            $nb_inscrits = intval($_POST['nb_inscrits']);
            update_max_places($id_stage, $place, $nb_inscrits);
            break;

        case 'update_bafm':
            $id_stage = intval($_POST['id_stage']);
            $bafm = intval($_POST['bafm']);
            $ants_numero = $_POST['ants_numero'];
            update_bafm($id_stage, $bafm, $ants_numero);
            break;

        case 'update_psy':
            $id_stage = intval($_POST['id_stage']);
            $psy = intval($_POST['psy']);
            $ants_numero = $_POST['ants_numero'];
            update_psy($id_stage, $psy, $ants_numero);
            break;

        case 'update_hour':
            $id_stage = intval($_POST['id_stage']);
            $hour = $_POST['hour'];
            $hour_type = $_POST['hour_type'];
            $ants_numero = isset($_POST['ants_numero']) ? $_POST['ants_numero'] : null;
            update_hour($id_stage, $hour, $hour_type, $ants_numero);
            break;

        case 'update_diffusion':
            $id_stage = intval($_POST['id_stage']);
            update_diffusion($id_stage, $_POST['online']);
            break;

        case 'ajout_stagiaire_externe':
            ajout_stagiaire_externe($_POST);
            break;

        case 'ajout_stage':
            ajout_stage($_POST, $id_membre);
            break;

        case 'ajout_stage2':
            ajout_stage2($_POST, $id_membre);
            break;

        case 'stage_mc24':
            stage_mc24($_POST);
            break;

        case 'affiche_stagiaires':
            affiche_stagiaires($_POST, $id_membre, $_SESSION['id_stage']);
            break;

        case 'affiche_stagiaires_stage':
            affiche_stagiaires_stage($_POST, $id_membre, $_SESSION['id_stage']);
            break;

        case 'affiche_stagiaires_erreur':
            affiche_stagiaires_erreur($_POST, $id_membre, $_SESSION['id_stage']);
            break;

        case 'annulation_inscription':
            annulation_inscription($_POST);
            break;

        case 'fiche_stagiaire':
            $ret = fiche_stagiaire($_POST);
            echo $ret;
            break;

        case 'dossier_stage':
            dossier_stage($_POST);
            break;

        case 'modifier_compte':
            $ret = modifier_compte($_POST, $id_membre);
            echo $ret;
            break;

        case 'enregistre_compte':
            enregistre_compte($_POST, $id_membre);
            break;

        case 'affiche_factures':
            affiche_factures($_POST, $id_membre);
            break;

        case 'delete_stagiaire_externe':
            delete_stagiaire_externe($_POST);
            break;
    }
}

function envoie_msg_centre($params, $membre)
{

    $type_interlocuteur = 3;
    $id_interlocuteur = 0;
    $type_destinataire = 0;
    $notifie = 1;
    $message = $params['msg'];

    $id_notification = send_notification($type_interlocuteur, $id_interlocuteur, $type_destinataire, $notifie, $message);
    update_notification_centre($id_notification, $membre);
}

function update_fiche_stagiaire($params)
{

    $id_stagiaire = intval($params['id_stagiaire']);
    $nom = $params['nom'];
    $prenom = $params['prenom'];
    $adresse = $params['adresse'];
    $code_postal = $params['code_postal'];
    $ville = $params['ville'];
    $cas = $params['cas'];
    $date_naissance = $params['date_naissance'];
    $lieu_naissance = $params['lieu_naissance'];
    $num_permis = $params['num_permis'];
    $date_permis = $params['date_permis'];
    $lieu_permis = $params['lieu_permis'];
    $date_infraction = $params['date_infraction'];
    $heure_infraction = $params['heure_infraction'];
    $lieu_infraction = $params['lieu_infraction'];
    $table = $params['table'];
    $departement_naissance = $params['departement_naissance'];
    $pays_naissance = $params['pays_naissance'];
    $civilite = $params['civilite'];
    $prenom2 = isset($params['prenom2']) ? $params['prenom2'] : '';
    $prenom3 = isset($params['prenom3']) ? $params['prenom3'] : '';

    $table = intval($table) == 2 ? "stagiaire_externe" : "stagiaire";

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "UPDATE " . $table . " SET 
				nom = \"$nom\",
				prenom = \"$prenom\",
				adresse = \"$adresse\",
				code_postal = \"$code_postal\",
				ville = \"$ville\",
				cas = \"$cas\",
				date_naissance = \"$date_naissance\",
				lieu_naissance = \"$lieu_naissance\",
				num_permis = \"$num_permis\",
				date_permis = \"$date_permis\",
				lieu_permis = \"$lieu_permis\",
				date_infraction = \"$date_infraction\",
				heure_infraction = \"$heure_infraction\",
				lieu_infraction = \"$lieu_infraction\",
				departement_naissance = \"$departement_naissance\",
				pays_naissance = \"$pays_naissance\",
				prenom2 = \"$prenom2\",
				prenom3 = \"$prenom3\",
				civilite = \"$civilite\"
			WHERE
				id = '$id_stagiaire'";
    mysql_query($sql, $stageconnect);
    mysql_close($stageconnect);
}

function delete_stagiaire_externe($params)
{

    include("/home/prostage/connections/stageconnect.php");
    require_once("/home/prostage/connections/config.php");
    require_once "/home/prostage/www/params.php";
    require_once '/home/prostage/www/src/stage/services/SingleUpdateStagePlace.php';

    $id_stagiaire = intval($params['id_stagiaire']);
    $id_stage = intval($params['id_stage']);

    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "DELETE FROM stagiaire_externe WHERE id = '$id_stagiaire'";
    mysql_query($sql, $stageconnect);
    mysql_close($stageconnect);

    (new SingleUpdateStagePlace())->__invoke($id_stage, $mysqli);

    echo '1';
}

function liste_bafm($id_membre)
{

    $rows = array();

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "SELECT id, nom, prenom FROM formateur WHERE formation='bafm' and id_membre=$id_membre";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);

    while ($r = mysql_fetch_assoc($rs)) {
        $r['nom'] = ($r['nom']);
        $r['prenom'] = utf8_decode($r['prenom']);
        $rows[] = $r;
    }

    echo json_encode($rows);
}

function liste_psy($id_membre)
{

    $rows = array();

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "SELECT id, nom, prenom FROM formateur WHERE formation='psy' and id_membre=$id_membre";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);

    while ($r = mysql_fetch_assoc($rs)) {
        $r['nom'] = utf8_decode($r['nom']);
        $r['prenom'] = utf8_decode($r['prenom']);
        $rows[] = $r;
    }

    echo json_encode($rows);
}

function enregistre_compte($params, $id_membre)
{

    $sql = "UPDATE membre SET ";

    foreach ($params as $key => $val) {

        if (stripos($key, 'action') !== false)
            continue;

        $sql .= $key . " = \"" . $val . "\",";
    }

    $sql = substr($sql, 0, -1);

    $sql .= " WHERE id = " . $id_membre;

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    mysql_query($sql, $stageconnect);
    mysql_close($stageconnect);
}

function modifier_compte($params, $id_membre)
{

    $encart = intval($params['encart']);

    require_once('includes/functions.php');
    $compte = get_infos_membre($id_membre);

    if ($encart == 1) {
        $c = "<table class='tab_modif'>";
        $c .= "<tr><th>Entreprise</th><td><input type='text' id='nom' value=\"" . $compte['nom'] . "\"></td></tr>";
        $c .= "<tr><th>Siret</th><td><input type='text' id='siret' value=\"" . $compte['siret'] . "\"></td></tr>";
        $c .= "<tr><th>Ape</th><td><input type='text' id='ape' value=\"" . $compte['ape'] . "\"></td></tr>";
        $c .= "<tr><th>N° TVA</th><td><input type='text' id='tva' value=\"" . $compte['tva'] . "\" placeholder='FRXXXXXXXXXXX'></td></tr>";
        $c .= "<tr><th>Assujetti TVA</th>";
        $c .= "<td style='text-align:left'>";
        $c .= "<select id='assujetti_tva' style='font-size:13px;padding:3px'>";
        $c .= "<option value='-1'" . (intval($compte['assujetti_tva']) == -1 ? " selected " : "") . ">Non renseigné</option>";
        $c .= "<option value='1'" . (intval($compte['assujetti_tva']) == 1 ? " selected " : "") . ">OUI</option>";
        $c .= "<option value='0'" . (intval($compte['assujetti_tva']) == 0 ? " selected " : "") . ">NON</option>";
        $c .= "</select>";
        $c .= "</td>";
        $c .= "</tr>";
        $c .= "<tr><th>Adresse</th><td><input type='text' id='adresse' value=\"" . $compte['adresse'] . "\"></td></tr>";
        $c .= "<tr><th>Email</th><td><input type='text' id='email' value=\"" . $compte['email'] . "\"></td></tr>";
        $c .= "<tr><th>Tél</th><td><input type='text' id='tel' value=\"" . $compte['tel'] . "\"></td></tr>";
        $c .= "<tr><th>Mobile</th><td><input type='text' id='mobile' value=\"" . $compte['mobile'] . "\"></td></tr>";
        $c .= "</table>";
    } else if ($encart == 2) {
        $c = "<table class='tab_modif'>";
        $c .= "<tr><th>Nom</th><td><input type='text' id='nom_gerant' value=\"" . $compte['nom_gerant'] . "\"></td></tr>";
        $c .= "<tr><th>Prénom</th><td><input type='text' id='prenom_gerant' value=\"" . $compte['prenom_gerant'] . "\"></td></tr>";
        $c .= "<tr><th>Email</th><td><input type='text' id='email_gerant' value=\"" . $compte['email_gerant'] . "\"></td></tr>";
        $c .= "<tr><th>Tél</th><td><input type='text' id='tel_gerant' value=\"" . $compte['tel_gerant'] . "\"></td></tr>";
        $c .= "<tr><th>Mobile</th><td><input type='text' id='mobile_gerant' value=\"" . $compte['mobile_gerant'] . "\"></td></tr>";
        $c .= "</table>";
    } else if ($encart == 3) {
        $c = "<table class='tab_modif'>";
        $c .= "<tr><th>Nom</th><td><input type='text' id='nom_facturation' value=\"" . $compte['nom_facturation'] . "\"></td></tr>";
        $c .= "<tr><th>Prénom</th><td><input type='text' id='prenom_facturation' value=\"" . $compte['prenom_facturation'] . "\"></td></tr>";
        $c .= "<tr><th>Email</th><td><input type='text' id='email_facturation' value=\"" . $compte['email_facturation'] . "\"></td></tr>";
        $c .= "<tr><th>Tél</th><td><input type='text' id='tel_facturation' value=\"" . $compte['tel_facturation'] . "\"></td></tr>";
        $c .= "<tr><th>Mobile</th><td><input type='text' id='mobile_facturation' value=\"" . $compte['mobile_facturation'] . "\"></td></tr>";
        $c .= "<tr><th>Iban</th><td><input type='text' id='iban' value=\"" . $compte['iban'] . "\"></td></tr>";
        $c .= "<tr><th>Bic</th><td><input type='text' id='bic' value=\"" . $compte['bic'] . "\"></td></tr>";
        $c .= "</table>";
    } else if ($encart == 4) {
        $c = "<table class='tab_modif'>";
        $c .= "<tr><th>Ancien mot de passe</th><td><input type='text' id='pass_md5' value=\"" . get_starred($compte['pass_md5']) . "\"></td></tr>";
        $c .= "<tr><th>Nouveau mot de passe</th><td><input type='password' id='pass_md5_new' value=\"\"></td></tr>";
        $c .= "<tr><th>Confirmez nouveau mot de passe</th><td><input type='password' id='pass_md5_new_confirm' value=\"\"></td></tr>";
        $c .= "</table>";
    } else if ($encart == 5) {
        $c = "<table class='tab_modif'>";
        $c .= "<tr>
		<th>Nom</th>
		<td><input placeholder'Nom' type='text' id='nom_directeur'></td>
		</tr>
		<tr>
		<th>Prénom</th>
		<td><input placeholder'Prénom' type='text' id='prenom_directeur'></td>
		</tr>";
        $c .= "</table>";
    }

    return $c;
}

function modifier_lieu($params)
{

    $id_site = $params['id_site'];

    require_once('includes/functions.php');
    $site = get_infos_site($id_site);

    $commodites = explode(",", $site['commodites']);
    $c .= ($commodites[0] == "on") ? "<i class='fas fa-parking'></i>" : "";
    $c .= ($commodites[1] == "on") ? "<i class='fas fa-utensils'></i>" : "";
    $c .= ($commodites[2] == "on") ? "<i class='fas fa-wheelchair'></i>" : "";

    $c = "<table class='tab_modif'>";
    $c .= "<tr><th>Nom</th><td><input type='text' id='nom' value=\"" . $site['nom'] . "\"></td></tr>";
    $c .= "<tr><th>Adresse</th><td><input type='text' id='adresse' value=\"" . $site['adresse'] . "\"></td></tr>";
    $c .= "<tr><th>Commodités</th>";
    $c .= "<td>";

    $checked = ($commodites[0] == "on") ? "checked" : "";
    $c .= "<input type='checkbox' id='parking' $checked style='width:5%;text-align:left'> <span style='font-size:13px'>Parking</span>";
    $c .= "<br>";

    $checked = ($commodites[1] == "on") ? "checked" : "";
    $c .= "<input type='checkbox' id='dejeuner' $checked style='width:5%;text-align:left'> <span style='font-size:13px'>Déjeuner</span>";
    $c .= "<br>";

    $checked = ($commodites[2] == "on") ? "checked" : "";
    $c .= "<input type='checkbox' id='handicap' $checked style='width:5%;text-align:left'> <span style='font-size:13px'>Accès handicapé</span>";

    $c .= "</td>";
    $c .= "</tr>";
    $c .= "<tr><th>Agrément</th><td><input type='text' id='agrement' value=\"" . $site['agrement'] . "\"></td></tr>";
    $c .= "<tr><th>Commentaire</th><td><input type='text' id='divers' value=\"" . $site['divers'] . "\"></td></tr>";
    $c .= "</table>";

    echo $c;
}

function modifier_formateur($params)
{

    $id_formateur = $params['id_formateur'];

    require_once('includes/functions.php');
    $formateur = get_infos_formateur($id_formateur);

    $c = '<h3 style="margin-bottom: 20px; margin-top: 10px; text-align: center">Modifier un formateur</h3>';
    $c .= "<table class='tab_modif'>";
    $c .= "<tr><th>Métier</th><td>";
    $c .= "<select id='formation' name='formation' class='form-control'>";
    $c .= "<option value='bafm' " . ($formateur['formation'] == "bafm" ? "selected" : "") . ">Bafm</option>";
    $c .= "<option value='psy' " . ($formateur['formation'] == "psy" ? "selected" : "") . ">Psy</option>";
    $c .= "</select>";
    $c .= "</td></tr>";

    $c .= "<tr><th>Gta</th><td>";
    $c .= "<select id='gta' name='gta' class='form-control'>";
    $c .= "<option value='1' " . (intval($formateur['gta']) ? "selected" : "") . ">OUI</option>";
    $c .= "<option value='0' " . (!intval($formateur['gta']) ? "selected" : "") . ">NON</option>";
    $c .= "</select>";
    $c .= "</td></tr>";

    $c .= "<tr><th>Nom</th><td><input type='text' id='nom' value=\"" . utf8_decode($formateur['nom']) . "\"></td></tr>";
    $c .= "<tr><th>Prénom</th><td><input type='text' id='prenom' value=\"" . utf8_decode($formateur['prenom']) . "\"></td></tr>";
    $c .= "<tr><th>Numéro autorisation</th><td><span>B-</span><input style='display: inline-block; width: 94%; margin-left: 3px' type='number' id='num_auto' required='required' max='10' value=\"" . utf8_decode($formateur['num_autorisation']) . "\"></td></tr>";
    $c .= "<tr><th>Adresse</th><td><input type='text' id='adresse' value=\"" . utf8_decode($formateur['adresse']) . "\"></td></tr>";
    $c .= "<tr><th>Code postal</th><td><input type='text' id='code_postal' value=\"" . $formateur['code_postal'] . "\"></td></tr>";
    $c .= "<tr><th>Ville</th><td><input type='text' id='ville' value=\"" . utf8_decode($formateur['ville']) . "\"></td></tr>";
    $c .= "<tr><th>Tél</th><td><input type='text' id='tel' value=\"" . $formateur['tel'] . "\"></td></tr>";
    $c .= "<tr><th>Email</th><td><input type='text' id='email' value=\"" . $formateur['email'] . "\"></td></tr>";
    $c .= "<tr><th>Commentaire</th><td><input type='text' id='divers' value=\"" . utf8_decode($formateur['divers']) . "\"></td></tr>";

    $c .= "</table>";

    echo $c;
}

function dossier_stage($params)
{

    ob_start();
    $content = "TOTO";
    echo $content;
    $content = ob_get_clean();

    require_once('../../html2pdf_v4.02/html2pdf.class.php');
    $html2pdf = new HTML2PDF('P', 'A4', 'fr', false, 'ISO-8859-1');
    $html2pdf->WriteHTML($content);

    $name_pdf = "Emargement.pdf";

    $html2pdf->Output($name_pdf, 'D');
}

function FormaterDate($date)
{
    $date = str_replace('/', '-', $date);
    $date = explode('-', $date);
    if (count($date) == 3) {
        if ($date[0] > 1000) {
            $new_date['annee'] = $date[0];
            $new_date['mois'] = $date[1];
            $new_date['jour'] = $date[2];
        } else {
            $new_date['annee'] = $date[2];
            $new_date['mois'] = $date[1];
            $new_date['jour'] = $date[0];
        }
        if ($new_date['jour'] < 10)
            $new_date['jour'] = '0' . $new_date['jour'];
        if ($new_date['mois'] < 10)
            $new_date['mois'] = '0' . $new_date['mois'];
    } else {
        $new_date['annee'] = '';
        $new_date['mois'] = '';
        $new_date['jour'] = '';
    }
    return $new_date;
}

function fiche_stagiaire($params)
{

    $id_stagiaire = intval($params['id_stagiaire']);
    $id_member = intval($params['id_member']);
    $table = intval($params['table']);
    $table_text = $table == 1 ? "stagiaire" : "stagiaire_externe";

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT * FROM membre WHERE id = '$id_member'";
    $rs = mysql_query($sql, $stageconnect);
    $row = mysql_fetch_assoc($rs);
    $display_phone_stagiaire = $row['display_phone_student'];
    $display_email_stagiaire = $row['display_student_email'];

    $sql = "SELECT * FROM " . $table_text . " WHERE id = '$id_stagiaire'";
    $rs = mysql_query($sql, $stageconnect);
    $row = mysql_fetch_assoc($rs);


    mysql_close($stageconnect);
    $nom = $row['nom'];
    $nom_usage = $row['nom_usage'];
    $prenom = $row['prenom'];
    $prenom2 = $row['prenom2'];
    $prenom3 = $row['prenom3'];
    $adresse = $row['adresse'];
    $code_postal = $row['code_postal'];
    $ville = $row['ville'];
    $civility = $row['civilite'];
    $departement_naissance = $row['departement_naissance'];
    $pays_naissance = $row['pays_naissance'];
    $date_naissance = FormaterDate($row['date_naissance']); // ? date('d-m-Y', strtotime($row['date_naissance'])) : "";
    $date_infraction = FormaterDate($row['date_infraction']); // ? date('d-m-Y', strtotime($row['date_infraction'])) : "";
    $lieu_naissance = ($row['lieu_naissance']);
    $heure_infraction = $row['heure_infraction'];
    $email = $row['email'];
    $phone = $row['tel'];

    if ($heure_infraction != '') {
        $heure_infraction = explode(':', $heure_infraction);
        if (count($heure_infraction) == 2) {
            $heure_infraction_heure = $heure_infraction[0];
            $heure_infraction_minutes = $heure_infraction[1];
        }
    } else {
        $heure_infraction_heure = '';
        $heure_infraction_minutes = '';
    }

    $lieu_infraction = $row['lieu_infraction'];
    $cas = $row['cas'];
    $num_permis = $row['num_permis'];
    $date_permis = FormaterDate($row['date_permis']); // ? date('d-m-Y', strtotime($row['date_permis'])) : "";
    $lieu_permis = $row['lieu_permis'];
    $date_inscription = date('d-m-Y', strtotime($row['date_inscription']));

    if ($table == 1)
        $provenance = "Prostagespermis";
    else if (intval($row['provenance']) == 2)
        $provenance = "Autre plateforme";
    else if (intval($row['provenance']) == 3)
        $provenance = "Réseau propre";

    $nbLastName = 1;
    if ($prenom2 != '') $nbLastName += 1;
    if ($prenom3 != '') $nbLastName += 1;

    $ret = "<table class='fiche_stagiaire'>";
    $ret .= "<tr><th>Civilité</th><td>";
    $ret .= "<select id='civility' class='form-control'>";
    $ret .= "<option value='Mr' " . ($civility == 'Mr' ? "selected" : "") . ">Monsieur</option>";
    $ret .= "<option value='Mme' " . ($civility == 'Mme' ? "selected" : "") . ">Madame</option>";
    $ret .= "</select>";
    $ret .= "</td></tr>";
    $ret .= "<tr><th>Nom de naissance</th><td><input class=\"form-control\" id=\"nom\" value=\"$nom\"></td></tr>";
    $ret .= "<tr><th>Nom d'usage (Ex: nom d'épouse)</th><td><input class=\"form-control\" id=\"nom_usage\" value=\"$nom_usage\"></td></tr>";

    $ret .= "<tr id='tr_prenom'><th>Prénom</th><td style='display: flex;align-items: center;'><input class=\"form-control\" id=\"prenom\" value=\"$prenom\" style='width: 90%'> <i style='cursor: pointer' class='fa fa-plus fa-2x' id='add_lastname' nblastname='" . $nbLastName . "'></i></td></tr>";

    if ($prenom2 != '') {
        $ret .= "<tr id='tr_prenom2'><th>Prénom 2</th><td><input class=\"form-control\" id=\"prenom2\" value=\"$prenom2\"></td></tr>";
    }
    if ($prenom3 != '') {
        $ret .= "<tr id='tr_prenom3'><th>Prénom 3</th><td><input class=\"form-control\" id=\"prenom3\" value=\"$prenom3\"></td></tr>";
    }

    $ret .= "<tr><th>Adresse</th><td><input class=\"form-control\" id=\"adresse\" value=\"$adresse\"></td></tr>";
    $ret .= "<tr><th>Code postal</th><td><input class=\"form-control\" id=\"code_postal\" value=\"$code_postal\"></td></tr>";
    $ret .= "<tr><th>Ville</th><td><input class=\"form-control\" id=\"ville\" value=\"$ville\"></td></tr>";
    $ret .= "<tr><th>Date naissance</th><td><input type='text' maxlength=2 placeHolder='JJ' class=\"form-control\" id=\"date_naissance_jour\" value=\"" . $date_naissance['jour'] . "\" style='margin-right:5px;width:50px;float:left'><input type='text' maxlength=2 placeHolder='MM' class=\"form-control\" id=\"date_naissance_mois\" value=\"" . $date_naissance['mois'] . "\" style='margin-right:5px;width:50px;float:left'><input type='text' maxlength=4 placeHolder='AAAA' class=\"form-control\" id=\"date_naissance_annee\" value=\"" . $date_naissance['annee'] . "\" style='margin-right:5px;width:70px;float:left'></td></tr>";
    $ret .= "<tr><th>Ville de naissance</th><td><input class=\"form-control\" id=\"lieu_naissance\" value=\"$lieu_naissance\"></td></tr>";
    $ret .= "<tr><th>Département de naissance</th><td><input class=\"form-control\" id=\"departement_naissance\" value=\"$departement_naissance\"></td></tr>";
    $ret .= "<tr><th>Pays de naissance</th><td><input class=\"form-control\" id=\"pays_naissance\" value=\"$pays_naissance\"></td></tr>";
    $ret .= "<tr><th>Cas de stage</th><td>";
    $ret .= "<select id=\"cas\" class=\"form-control\">";
    $ret .= "<option value='1' " . ($cas == 1 ? "selected" : "") . ">Cas 1</option>";
    $ret .= "<option value='2' " . ($cas == 2 ? "selected" : "") . ">Cas 2</option>";
    $ret .= "<option value='3' " . ($cas == 3 ? "selected" : "") . ">Cas 3</option>";
    $ret .= "<option value='4' " . ($cas == 4 ? "selected" : "") . ">Cas 4</option>";
    $ret .= "</select>";
    $ret .= "<tr><th>Numéro permis</th><td><input class=\"form-control\" id=\"num_permis\" value='" . $num_permis . "'></td></tr>";
    $ret .= "<tr><th>Date permis</th><td><input type='text' maxlength=2 placeHolder='JJ' class='form-control' id='date_permis_jour' value='" . $date_permis['jour'] . "' style='margin-right:5px;width:50px;float:left'><input type='text' maxlength=2 placeHolder='MM' class='form-control' id='date_permis_mois' value='" . $date_permis['mois'] . "' style='margin-right:5px;width:50px;float:left'><input type='text' maxlength=4 placeHolder='AAAA' class='form-control' id='date_permis_annee' value='" . $date_permis['annee'] . "' style='margin-right:5px;width:70px;float:left'></td></tr>";
    $ret .= "<tr><th>Lieu permis</th><td><input class=\"form-control\" id=\"lieu_permis\" value=\"$lieu_permis\"></td></tr>";
    $ret .= "<tr><th>Date inscription</th><td>$date_inscription</td></tr>";
    $ret .= "<tr><th>Date infraction</th><td><input type='text' maxlength=2 placeHolder='JJ' class=\"form-control\" id=\"date_infraction_jour\" value=\"" . $date_infraction['jour'] . "\" style='margin-right:5px;width:50px;float:left'><input type='text' maxlength=2 placeHolder='MM' class=\"form-control\" id=\"date_infraction_mois\" value=\"" . $date_infraction['mois'] . "\" style='margin-right:5px;width:50px;float:left'><input type='text' maxlength=4 placeHolder='AAAA' class=\"form-control\" id=\"date_infraction_annee\" value=\"" . $date_infraction['annee'] . "\" style='margin-right:5px;width:70px;float:left'></td></tr>";
    $ret .= "<tr><th>Heure infraction</th><td><input class=\"form-control\" id=\"heure_infraction\" value=\"$heure_infraction_heure\" style='width:50px;margin-right:10px;float:left'><input class=\"form-control\" id=\"minutes_infraction\" value=\"$heure_infraction_minutes\" style='width:50px;float:left'></td></tr>";
    $ret .= "<tr><th>Lieu infraction</th><td><input class=\"form-control\" id=\"lieu_infraction\" value=\"$lieu_infraction\"></td></tr>";

    $ret .= "<tr><th>Provenance</th><td>$provenance</td></tr>";
    if ($display_email_stagiaire) {
        $ret .= "<tr><th>Email Stagiaire</th><td><input class=\"form-control\" id=\"email_stagiaire\" disabled value=\"$email\"></td></tr>";
    }
    if ($display_phone_stagiaire) {
        $ret .= "<tr><th>Téléphone Stagiaire</th><td><input class=\"form-control\" id=\"phone_stagiaire\" disabled value=\"$phone\"></td></tr>";
    }

    $ret .= "</table>";

    return $ret;
}

function annulation_inscription($params)
{

    require_once "/home/prostage/www/debug.php";
    require_once("/home/prostage/connections/config.php");
    require_once "/home/prostage/www/params.php";
    require_once '/home/prostage/www/src/stage/services/SingleUpdateStagePlace.php';
    require_once APP . 'Api/Sms/sendSMS.php';

    $id_stagiaire = $params['id_stagiaire'];
    $id_stage = $params['id_stage'];
    $motif = $params['motif'];
    $table = intval($params['table']);

    $types_annulation = array(
        'Annulation faute de participants, veuillez nous en excuser',
        'Annulation faute d\'animateurs, veuillez nous en excuser',
        'Annulation faute de salle, veuillez nous en excuser',
        'Stage déjà complet au moment de la réservation, veuillez nous en excuser'
    );

    if ($table == 1) {

        include("/home/prostage/connections/stageconnect.php");
        mysql_select_db($database_stageconnect, $stageconnect);

        $sql = "SELECT tel, mobile, id_stage, date1, id_membre, supprime FROM stagiaire, stage WHERE stagiaire.id='$id_stagiaire' AND stagiaire.id_stage = stage.id";
        $rs = mysql_query($sql, $stageconnect); // or die(mysql_error());
        $row = mysql_fetch_assoc($rs);
        $tel = $row['tel'];
        $id_stage = $row['id_stage'];
        $date1 = $row['date1'];
        $id_membre = $row['id_membre'];
        $supprime = $row['$supprime'];

        if ($id_membre == 1060)
            $motif_text = "Annulation faute d'animateurs, veuillez nous en excuser";
        else
            $motif_text = $types_annulation[$motif];
        $sql = "UPDATE stagiaire SET supprime=1, provenance_suppression = 1, motif_annulation=\"" . $motif_text . "\" WHERE id = '$id_stagiaire'";
        mysql_query($sql, $stageconnect);

        if ($id_membre == 1060)
            $motif2 = "Annulation faute d'animateurs, veuillez nous en excuser";
        else
            $motif2 = $types_annulation[$motif];
        $sql = "INSERT INTO annulations (id_stagiaire, id_stage, id_membre, motif) VALUES(\"$id_stagiaire\", \"$id_stage\", \"$id_membre\", \"$motif2\")";
        mysql_query($sql, $stageconnect);
        mysql_close($stageconnect);

        $key = md5($id_stagiaire . '!psp13#');
        $key = substr($key, 0, 5);
        $url = "https://prostagespermis.fr/es/login.php?id=$id_stagiaire&k=$key";

        $arrPhones[] = $tel;
        $resp = (new sendSMS())->sendSMS(
            "URGENT: votre stage a du être annulé par le centre organisateur. Retrouvez tous les détails par mail (VÉRIFIEZ VOS SPAMS). Réservez une nouvelle date de stage ou demandez le remboursement via votre Espace Stagiaire en CLIQUANT ICI: %RICHURL________%. Avec nos excuses sincères. ProStagesPermis",
            $arrPhones,
            $url
        );
        if ($resp) {
            include("/home/prostage/connections/stageconnect.php");
            mysql_select_db($database_stageconnect, $stageconnect);
            $sql = 'UPDATE stagiaire SET is_sms_annulation_send=1 WHERE id=' . $id_stagiaire;
            $rs = mysql_query($sql, $stageconnect);
            mysql_close($stageconnect);
        }

        $type_interlocuteur = 3;
        $id_interlocuteur = $id_stagiaire;
        $type_destinataire = 1;
        $notifie = 1;
        $message = "Annulation de candidat: " . $motif2;
        $id_notification = send_notification($type_interlocuteur, $id_interlocuteur, $type_destinataire, $notifie, $message);
        update_notification_centre($id_notification, $id_membre);

        if (($id_membre == 1060) && ($supprime == 0)) {
            include("/home/prostage/www/ws/prod/fsp/to/inscription/cancel.php");
            cancelInscription($id_membre, $id_stagiaire, '_ep');
        }
        //if (($id_membre == 970) && ($supprime == 0)) {
        if (($supprime == 0)) {
            include("/home/prostage/www/ws/prod/psp/to/inscription/cancel.php");
            cancelInscription($id_membre, $id_stagiaire);
        }
    } else if ($table == 2) {
        include("/home/prostage/connections/stageconnect.php");
        mysql_select_db($database_stageconnect, $stageconnect);
        $sql = "DELETE FROM stagiaire_externe WHERE id='$id_stagiaire'";
        mysql_query($sql, $stageconnect);
        mysql_close($stageconnect);
    }

    (new SingleUpdateStagePlace())->__invoke($id_stage, $mysqli);
}

function ajout_stage($params, $id_membre)
{

    $ret = 0;

    $id_site = $params['id_site'];
    $date1 = $params['date1'];
    $date2 = (date('Y-m-d', strtotime($date1 . ' + 1 days')));
    $prix = $params['prix'];
    $debut_am = $params['debut_am'];
    $fin_am = $params['fin_am'];
    $debut_pm = $params['debut_pm'];
    $fin_pm = $params['fin_pm'];
    $bafm = $params['bafm'];
    $psy = $params['psy'];
    $annule = 0;
    $nb_places_allouees = 20;

    include("/home/prostage/connections/stageconnect.php");
    include "../modules/module.php";

    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT stage.id FROM stage WHERE id_membre = '$id_membre' AND date1 = '$date1' AND id_site='$id_site'";
    $rs = mysql_query($sql, $stageconnect); // or die(mysql_error());
    $row = mysql_fetch_assoc($rs);
    $total = mysql_num_rows($rs);

    if (!$total) {
        $sql = "INSERT INTO stage(id_site, id_membre, date1, date2, prix, debut_am, fin_am, debut_pm, fin_pm, annule, nb_places_allouees, id_bafm, id_psy) VALUES(\"$id_site\", \"$id_membre\", \"$date1\", \"$date2\", \"$prix\", \"$debut_am\", \"$fin_am\", \"$debut_pm\", \"$fin_pm\", \"$annule\", \"$nb_places_allouees\", \"$bafm\", \"$psy\")";
        mysql_query($sql, $stageconnect) or die(mysql_error());
        $ret = 1;
        $last_inserted_id = mysql_insert_id();
        $trainingApi = new \App\Actions\Api\TrainingApiAction();
        $trainingApi->addStageApi($last_inserted_id);
    }
    mysql_close($stageconnect);

    echo $ret;
}

function ajout_stage2($params, $id_membre)
{

    include("/home/prostage/connections/stageconnect.php");

    $ret = 0;

    $id_site = $params['id_site'];
    $date1 = $params['date1'];
    $date2 = (date('Y-m-d', strtotime($date1 . ' + 1 days')));
    $prix = $params['prix'];
    $debut_am = $params['debut_am'];
    $fin_am = $params['fin_am'];
    $debut_pm = $params['debut_pm'];
    $fin_pm = $params['fin_pm'];
    $bafm = $params['bafm'];
    $psy = $params['psy'];
    $annule = 0;
    $nb_places_allouees = 20;
    $partenariat = $params['partenariat'];
    $prix_vente_ht = $params['prix_vente_ht'];
    $montant_commission_ht = $params['montant_commission_ht'];
    $tranche_commission_ht = $params['tranche_commission_ht'];
    $augmentation_commission_ht = $params['augmentation_commission_ht'];
    $reduction_commission_premium_ht = $params["reduction_commission_premium_ht"];
    $commission_ht = $params['commission_ht'];

    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT stage.id FROM stage WHERE id_membre = '$id_membre' AND date1 = '$date1' AND id_site='$id_site' AND annule = 0";
    //var_dump($sql);
    $rs = mysql_query($sql, $stageconnect); // or die(mysql_error());
    $row = mysql_fetch_assoc($rs);
    $total = mysql_num_rows($rs);

    if (!$total) {
        $sql = "INSERT INTO stage(id_site, id_membre, date1, date2, prix, debut_am, fin_am, debut_pm, fin_pm, annule, nb_places_allouees, id_bafm, id_psy, partenariat, prix_vente_ht, montant_commission_ht, tranche_commission_ht, augmentation_commission_ht, commission_ht,reduction_commission_premium_ht) VALUES(\"$id_site\", \"$id_membre\", \"$date1\", \"$date2\", \"$prix\", \"$debut_am\", \"$fin_am\", \"$debut_pm\", \"$fin_pm\", \"$annule\", \"$nb_places_allouees\", \"$bafm\", \"$psy\", \"$partenariat\", \"$prix_vente_ht\", \"$montant_commission_ht\", \"$tranche_commission_ht\", \"$augmentation_commission_ht\", \"$commission_ht\", \"$reduction_commission_premium_ht\")";
        mysql_query($sql, $stageconnect) or die(mysql_error());
        $ret = 1;
    }
    mysql_close($stageconnect);

    echo $ret;
}


function stage_mc24($data)
{
    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $params = json_decode($data['params']);
    $params = get_object_vars($params);
    $ret = 0;
    $prix_index_min = 0;

    $stageInitTemp = NULL;
    $sqlStageInit = "SELECT * FROM stage WHERE id=" . $params['ist'];
    $rsStageInit = mysql_query($sqlStageInit, $stageconnect);
    if ($rowStageInit = mysql_fetch_assoc($rsStageInit)) {
        $stageInitTemp = $rowStageInit;
    }

    switch ($params['op']) {
        case 'add':
            $params['d2'] = (date('Y-m-d', strtotime($params['d'] . ' + 1 days')));
            $annule = 0;
            $nb_places_allouees = 20;
            $sql = 'SELECT stage.id FROM stage WHERE id_membre = ' . $params['ime'] . ' AND date1 = "' . $params['d'] . '" AND id_site=' . $params['isi'] . ' AND annule=0 AND is_online=1 AND visible=1';
            $rs = mysql_query($sql, $stageconnect);
            $row = mysql_fetch_assoc($rs);
            if (mysql_num_rows($rs) > 0)
                $ret = 2;
            else {
                $sql = 'INSERT INTO stage(prix_index_min,delta_prix_index,marge_commerciale,taux_marge_commerciale,prix_ancien, id_site, id_membre, date1, date2, prix, debut_am, fin_am, debut_pm, fin_pm, annule, nb_places_allouees, id_bafm, id_psy, partenariat, prix_vente_ht,  commission_ht, tranche_commission_ht, augmentation_commission_ht, reduction_commission_premium_ht, montant_commission_ht) VALUES(' . $params['p10'] . ',' . $params['p9'] . ',' . $params['p15'] . ',' . $params['p13'] . ',' . $params['pic'] . ',' . $params['isi'] . ',' . $params['ime'] . ',"' . $params['d'] . '","' . $params['d2'] . '",' . $params['p1'] . ',"' . $params['dam'] . '","' . $params['fam'] . '","' . $params['dpm'] . '","' . $params['fpm'] . '",' . $annule . ',' . $nb_places_allouees . ',' . $params['bafm'] . ',' . $params['psy'] . ',' . $params['p2'] . ',' . $params['p3'] . ',' . $params['p4'] . ',' . $params['p5'] . ',' . $params['p6'] . ',' . $params['p7'] . ',' . $params['p8'] . ')';
                if (mysql_query($sql, $stageconnect)) {
                    $insertId = mysql_insert_id();
                } else {
                    $insertId = 0;
                    $ret = 0;
                }
            }
            break;
        case 'update':
            $insertId = $params['ist'];
            //- Backup du stage
            $sql = "INSERT INTO mc24_stage_backup SELECT * FROM stage WHERE id=" . $params['ist'];
            mysql_query($sql, $stageconnect);

            $sql = 'UPDATE stage SET
                prix_index_min=' . $params['p10'] . ',
                delta_prix_index=' . $params['p9'] . ',
                marge_commerciale=' . $params['p15'] . ',
                taux_marge_commerciale=' . $params['p13'] . ',
                prix_ancien=' . $params['pic'] . ', 
                id_site=' . $params['isi'] . ', 
                id_membre=' . $params['ime'] . ', 
                prix=' . $params['p1'] . ', 
                prix_vente_ht=' . $params['p3'] . ',  
                commission_ht=' . $params['p4'] . ', 
                tranche_commission_ht=' . $params['p5'] . ', 
                augmentation_commission_ht=' . $params['p6'] . ', 
                reduction_commission_premium_ht=' . $params['p7'] . ', 
                montant_commission_ht=' . $params['p8'] . '
            WHERE id=' . $params['ist'] . ' AND date1>=CURRENT_DATE AND annule=0 AND is_online=1 AND visible=1';
            if (mysql_query($sql, $stageconnect)) {
                $ret = 1;
            } else {
                $ret = 0;
            }

            include('/home/prostage/www/src/stage/repositories/TrackingPrice.php');
            require_once("/home/prostage/connections/config.php");

            $addTracking = (new TrackingPriceRepository($mysqli))->insert([
                'id_membre' => $params['ime'],
                'id_stage' => $params['ist'],
                'updated_at' => date('Y-m-d H:i:s'),
                'old_price_index' => $stageInitTemp['prix_ancien'],
                'new_price_index' => $params['pic'],
                'extra_datas' => json_encode($params)
            ]);

            break;
    }
    if ($insertId > 0 && $ret == 1) {
        // Prix Index Min sur la ville référente (si existe)
        $sql = 'SELECT S.id,MIN(S.prix) as prix_min, MIN(S.prix_ancien) as prix_ancien, I.ville_france_free_referente AS ville_referente
        FROM stage AS S 
        INNER JOIN site AS I ON I.id=S.id_site AND S.date1>=CURRENT_DATE AND S.annule=0 AND S.is_online=1 AND S.visible=1 AND I.ville_france_free_referente IN (SELECT ville_france_free_referente FROM site where id=' . $params['isi'] . ')';
        $rs = mysql_query($sql, $stageconnect);
        if ($row = mysql_fetch_assoc($rs)) {
            $ville_referente = $row['ville_referente'];
            if ($row['prix_ancien'] > 0) {
                $prix_index_min = $row['prix_ancien'];
            } else {
                $prix_index_min = $row['prix_min'];
            }
        }
        // Si le Prix Index Centre est < au Prix Index Min
        $prix_index_centre = $params['pic'];

        // Recalcule des Prix Vente de la ville référente
        include('/home/prostage/www/src/mc24/update.php');
        MargeCommercialeSet('all', $params['ime'], 0, $ville_referente);
        mysql_close($stageconnect);
    } else {
        mysql_close($stageconnect);
    }
    /*
    if($prix_index_min > 0 && ($prix_index_centre < $prix_index_min)){
        // Recalcule des Prix Vente de la ville référente
        include('/home/prostage/www/src/mc24/update.php'); 
        MargeCommercialeSet('all',$params['ime'],0,$ville_referente);
        $ret = 1;
    }else{
        $sql = 'INSERT INTO mc24_stage(
            id_stage, 
            id_membre,
            date1, 
            prix_index_ttc, 
            prix_vente_ttc, 
            marge_commerciale_ttc,
            taux_marge_commerciale,
            prix_index_min,
            delta_prix_index,
            commission_ht, 
            partenariat,
            algo_prix_plancher,
            algo_plage_temporelle,
            algo_prix_min_temporelle,
            algo_prix_min_degressive,
            algo_prix_max_degressive,
            algo_delta_degressive,
            algo_departement,
            algo_prix_index_ht,
            algo_commission_standard_ht,
            algo_augmentation_tranche_ttc,
            algo_augmentation_montant_ttc,
            algo_commission_premium_redution_ht) 
            VALUES('.$row['id_stage'].', 
            '.$row['id_membre'].',
            "'.$row['date_stage'].'",
            '.$prix_index_centre_ttc_initial.', 
            '.$params['p3'].', 
            '.$params['p15'].', 
            '.$params['p13'].', 
            '.$params['p10'].',
            '.$params['p9'].',
            '.$params['p4'].', 
            '.$params['p2'].', 
            '.$params['p14'].', 
            '.$params['p16'].', 
            '.$params['p17'].', 
            '.$params['p18'].', 
            '.$params['p19'].', 
            '.$params['p20'].', 
            '.$params['departement'].', 
            '.$params['p3'].', 
            '.$params['p8'].', 
            '.$params['p5'].', 
            '.$params['p6'].', 
            '.$params['p7'].')'; 
        if(mysql_query($sql, $stageconnect)){
            $ret = 1;
        }
        $ret = 1;
    }*/



    /*include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $query_site = "SELECT *, membre.nom as member_name FROM stage, membre WHERE membre.id = stage.id_membre AND stage.id = " . $params['ist'];

    $rs = mysql_query($query_site, $stageconnect) or die(mysql_error());

    if ($row = mysql_fetch_assoc($rs)) {
        $logfile = "/home/prostage/www/logs/update_pricing_stages.txt";
        $msg = date('d-m-Y H:i:s').' - MG - Stage : '. $params['ist'] .' ('.$row['date1'].') - Membre : '. $row["member_name"] .'('. $params['ime'] .') - Ancien Prix PSP : '. $stageInitTemp['prix'] .' - Nouveau Prix PSP : '. $params['p1'].' - Ancien Prix Centre : '. $stageInitTemp['prix_ancien'].' - Nouveau Prix Centre : '. $params['pic'];
        $tmpfile = file_get_contents($logfile);
        file_put_contents($logfile, $msg . "\n" . $tmpfile);
    }
    mysql_close($stageconnect);*/

    echo $ret;
}


function ajout_lieu($params, $id_membre)
{

    $nom = $params['nom'];
    $adresse = $params['adresse'];
    $code_postal = $params['code_postal'];
    $departement = intval(substr($code_postal, 0, 2));
    $ville = $params['ville'];
    $agrement = $params['agrement'];
    $commodites = $params['commodites'];
    $divers = $params['divers'];

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "INSERT INTO site(id_membre, nom, adresse, code_postal, departement, ville, agrement, commodites, divers) VALUES(\"$id_membre\", \"$nom\", \"$adresse\", \"$code_postal\", \"$departement\", \"$ville\", \"$agrement\", \"$commodites\", \"$divers\")";
    mysql_query($sql, $stageconnect) or die(mysql_error());

    mysql_close($stageconnect);
}

function ajout_formateur($params, $id_membre)
{

    $nom = utf8_encode($params['nom']);
    $prenom = utf8_encode($params['prenom']);
    $adresse = utf8_encode($params['adresse']);
    $code_postal = $params['code_postal'];
    $ville = utf8_encode($params['ville']);
    $tel = $params['tel'];
    $email = $params['email'];
    $formation = $params['formation'];
    $gta = intval($params['gta']);
    $divers = utf8_encode($params['divers']);

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "INSERT INTO formateur(id_membre, nom, prenom, adresse, code_postal, ville, tel, email, formation, gta, divers) VALUES(\"$id_membre\", \"$nom\", \"$prenom\", \"$adresse\", \"$code_postal\", \"$ville\", \"$tel\", \"$email\", \"$formation\", \"$gta\", \"$divers\")";
    mysql_query($sql, $stageconnect) or die(mysql_error());

    mysql_close($stageconnect);
}

function get_historic_notifications($params, $id_membre)
{

    $id_stagiaire = intval($params['id_stagiaire']);

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $messages = array();
    $sql = "SELECT message, timestamp, type_interlocuteur FROM notifications WHERE id_centre = '$id_membre' AND id_interlocuteur = '$id_stagiaire' ORDER BY id DESC";

    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
    while ($row = mysql_fetch_assoc($rs))
        $messages[] = $row;

    $c = "<p style='margin-top:20px;margin-bottom:5px;font-size:14px'>Historique des échanges</p>";
    $c .= "<div style='border-radius:5px;text-align:left;border:1px solid #ccc;width:100%;padding:5px;height:200px;max-height:200px;font-size:13px;color:grey;overflow-y: scroll;'>";

    foreach ($messages as $message) {

        if (intval($message['type_interlocuteur']) == 1)
            $c .= "<p style='color:#e94949'>";
        else
            $c .= "<p>";
        $c .= date('d-m-Y H:i', strtotime($message['timestamp'])) . ": ";
        $c .= $message['message'];
        $c .= "</p>";
    }

    $c .= "</div>";
    $c .= "<textarea id='message' name='message' style='width:100%;height:200px;padding:5px;margin-top: 30px' placeholder='Nouveau message'></textarea>";

    echo $c;
}

function enregistrer_lieu($params, $id_membre)
{

    $id_site = $params['id_site'];
    $nom = $params['nom'];
    $adresse = $params['adresse'];
    $agrement = $params['agrement'];
    $commodites = $params['commodites'];
    $divers = $params['divers'];

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "UPDATE 
				site 
			SET 
				nom = \"$nom\", 
				adresse=\"$adresse\", 
				agrement=\"$agrement\", 
				commodites=\"$commodites\", 
				divers=\"$divers\" 
			WHERE 
				id = '$id_site' AND 
				id_membre = " . $id_membre;
    echo $sql;
    mysql_query($sql, $stageconnect) or die(mysql_error());

    mysql_close($stageconnect);
}

function enregistrer_formateur($params, $id_membre)
{

    $id_formateur = $params['id_formateur'];
    $nom = utf8_encode($params['nom']);
    $prenom = utf8_encode($params['prenom']);
    $adresse = utf8_encode($params['adresse']);
    $code_postal = $params['code_postal'];
    $ville = utf8_encode($params['ville']);
    $divers = utf8_encode($params['divers']);
    $tel = $params['tel'];
    $email = $params['email'];
    $gta = $params['gta'];
    $formation = utf8_encode($params['formation']);
    $num_auto = $params['num_auto'];

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "UPDATE 
				formateur 
			SET 
				nom = \"$nom\", 
				prenom = \"$prenom\",
				adresse=\"$adresse\", 
				code_postal=\"$code_postal\", 
				ville=\"$ville\", 
				tel=\"$tel\", 
				email=\"$email\", 
				gta=\"$gta\", 
				formation=\"$formation\", 
				divers=\"$divers\",
                num_autorisation=\"$num_auto\"
			WHERE 
				id = '$id_formateur' AND 
				id_membre = " . $id_membre;
    mysql_query($sql, $stageconnect) or die(mysql_error());

    mysql_close($stageconnect);
}

function delete_lieu($params, $id_membre)
{

    $id_site = $params['id'];

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT stage.id FROM stage, site WHERE stage.id_site = site.id AND site.id = '$id_site'";
    $rs = mysql_query($sql, $stageconnect); // or die(mysql_error());
    $row = mysql_fetch_assoc($rs);
    $total = mysql_num_rows($rs);

    if (!$total) {
        $sql = "DELETE FROM site WHERE id = '$id_site' AND id_membre = " . $id_membre;
        mysql_query($sql, $stageconnect);
    }

    mysql_close($stageconnect);
}

function delete_formateur($params, $id_membre)
{

    $id_formateur = $params['id'];

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT stage.id FROM stage WHERE stage.id_bafm = '$id_formateur' OR stage.id_psy = '$id_formateur'";
    $rs = mysql_query($sql, $stageconnect); // or die(mysql_error());
    $row = mysql_fetch_assoc($rs);
    $total = mysql_num_rows($rs);

    if (!$total) {
        //$sql = "DELETE FROM formateur WHERE id = '$id_formateur' AND id_membre = " . $id_membre;
        //mysql_query($sql, $stageconnect);

        $sql = "DELETE FROM membre_formateur WHERE id_formateur = '$id_formateur' AND id_membre = " . $id_membre;
        mysql_query($sql, $stageconnect);
    }

    mysql_close($stageconnect);
}

function delete_stage($params)
{
    require_once("/home/prostage/connections/config.php");
    include("/home/prostage/connections/stageconnect.php");
    include "../modules/module.php";
    require_once "/home/prostage/www/params.php";
    require_once "/home/prostage/www/debug.php";
    require_once APP . 'stage/services/RetrieveStageByIdWithOutCondition.php';
    require_once APP . 'animator/email/SendCancelStageToAnimatorEmail.php';
    require_once APP . 'animator/services/RetrieveAnimatorById.php';
    require_once APP . 'member/services/RetrieveMember.php';

    $id_stage = $params['id_stage'];
    $motifIndex = isset($params['motif']) ? $params['motif'] : '';
    $motif = '';

    switch ($motifIndex) {
        case 0:
            $motif = 'Annulation faute de participants, veuillez nous en excuser';
            break;
        case 1:
            $motif = 'Annulation faute d\'animateurs, veuillez nous en excuser';
            break;
        case 2:
            $motif = 'Annulation faute de salle, veuillez nous en excuser';
            break;
    }

    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "UPDATE stage SET annule = 1, motif_annulation = '" . addslashes($motif) . "' WHERE id = $id_stage";

    mysql_query($sql, $stageconnect);
    mysql_close($stageconnect);

    $stage = (new RetrieveStageByIdWithOutCondition())->__invoke($id_stage, $mysqli);
    $animatorBafm = $stage->id_bafm ? (new RetrieveAnimatorById())->__invoke($stage->id_bafm, $mysqli) : null;
    $animatorPsy = $stage->id_psy ? (new RetrieveAnimatorById())->__invoke($stage->id_psy, $mysqli) : null;
    $member = (new RetrieveMember())->__invoke($stage->id_membre, $mysqli);

    if ($member->display_stage_pourvoir) {

        if ($animatorPsy) {
            (new SendCancelStageToAnimatorEmail())->execute(
                $animatorPsy->email,
                $animatorPsy->firstName,
                $stage->date1,
                $stage->date2,
                $stage->ville,
                $member->nom,
                $motif
            );
        }

        if ($animatorBafm) {
            (new SendCancelStageToAnimatorEmail())->execute(
                $animatorBafm->email,
                $animatorBafm->firstName,
                $stage->date1,
                $stage->date2,
                $stage->ville,
                $member->nom,
                $motif
            );
        }
    }
}

function pass_sanitaire($params)
{
    $id_stage = $params['id_stage'];
    $id_statut = $params['id_statut'];
    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "UPDATE stage SET pass_sanitaire = " . $id_statut . " WHERE id = " . $id_stage;
    mysql_query($sql, $stageconnect);
    mysql_close($stageconnect);
}

function CommissionParametres($id_membre)
{
    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $query_site = "SELECT * FROM membre WHERE id = " . $id_membre;
    $rs = mysql_query($query_site) or die(mysql_error());
    mysql_close($stageconnect);
    $row = mysql_fetch_assoc($rs);
    return $row;
}

function StageSet($id_membre, $id_stage, $prix_index_ttc, $partenariat = 0)
{
    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $query_site = "SELECT * FROM membre WHERE id = " . $id_membre;
    $rs = mysql_query($query_site) or die(mysql_error());
    if ($row = mysql_fetch_assoc($rs)) {
        if ($row['nouveau_modele_commission'] == 1 || $row['id'] >= 959) {
            if ($partenariat == 0)
                $partenariat = $row['partenariat'];
            $prix_vente_ht = $row["prix_vente_ht"];
            $montant_commission_ht = $row["montant_commission_ht"];
            $tranche_commission_ht = $row["tranche_commission_ht"];
            $augmentation_commission_ht = $row["augmentation_commission_ht"];
            $reduction_commission_premium_ht = $row["reduction_commission_premium_ht"];

            $prix_vente_ttc = round($prix_vente_ht * 1.2, 2);
            $com_difference_ht = (($prix_index_ttc - $prix_vente_ttc) / $tranche_commission_ht) * $augmentation_commission_ht;
            $com_standard_ht = $montant_commission_ht + $com_difference_ht;
            switch ($partenariat) {
                case 1:
                    $commission_ht = round($com_standard_ht, 2);
                    break;
                case 2:
                    $com_prenium_ht = $com_standard_ht - $reduction_commission_premium_ht;
                    $commission_ht = round($com_prenium_ht, 2);
                    break;
            }
        } else {
            $partenariat = 0;
            $prix_vente_ht = 0;
            $montant_commission_ht = 0;
            $tranche_commission_ht = 0;
            $augmentation_commission_ht = 0;
            $reduction_commission_premium_ht = 0;
            $commission_ht = 0;
        }
        $sql = "UPDATE stage SET partenariat=" . $partenariat . ",prix=" . $prix_index_ttc . ",prix_vente_ht=" . $prix_vente_ht . ",montant_commission_ht=" . $montant_commission_ht . ",tranche_commission_ht=" . $tranche_commission_ht . ",augmentation_commission_ht=" . $augmentation_commission_ht . ",reduction_commission_premium_ht=" . $reduction_commission_premium_ht . ",commission_ht=" . $commission_ht . " WHERE id = " . $id_stage;
        $logfile = "/home/prostage/www/logs/trace_nouveau_modele_commission.txt";
        $msg = $sql . "_" . date('d-m-Y H:i:s');
        $tmpfile = file_get_contents($logfile);
        file_put_contents($logfile, $msg . "\n" . $tmpfile);
    }
    $res = mysql_query($sql, $stageconnect);
    mysql_close($stageconnect);
    echo $commission_ht;
}

function ajout_stagiaire_externe($params)
{
    require_once "/home/prostage/www/debug.php";
    include("/home/prostage/connections/stageconnect.php");
    require_once("/home/prostage/connections/config.php");
    require_once "/home/prostage/www/params.php";
    require_once '/home/prostage/www/src/stage/services/UpdateStageAfterPayment.php';

    $id_stage = $params['id_stage'];
    $nom = $params['nom'];
    $prenom = $params['prenom'];
    $adresse = $params['adresse'];
    $code_postal = $params['code_postal'];
    $ville = $params['ville'];
    $date_naissance = $params['date_naissance'];
    $lieu_naissance = $params['lieu_naissance'];
    $tel = $params['tel'];
    $email = $params['email'];
    $cas = $params['cas'];
    $num_permis = $params['num_permis'];
    $date_permis = $params['date_permis'];
    $lieu_permis = $params['lieu_permis'];
    $provenance = $params['provenance'];
    $paiement = $params['paiement'];
    $receipt = $params['receipt'];
    $date_infraction = $params['date_infraction'];
    $heure_infraction = $params['heure_infraction'];
    $lieu_infraction = $params['lieu_infraction'];
    $civilite = $params['civilite'];
    $nom_usage = $params['nom_usage'];
    $prenom2 = $params['prenom2'];
    $prenom3 = $params['prenom3'];
    $adresse_complement = $params['adresse_complement'];
    $pays_naissance = $params['pays_naissance'];

    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "SELECT site.departement,stage.commission_ht,stage.partenariat FROM stage, site WHERE stage.id_site = site.id AND stage.id = '$id_stage'";
    $rs_dep = mysql_query($sql, $stageconnect);
    $row_dep = mysql_fetch_assoc($rs_dep);
    $dep = intval($row_dep['departement']);
    $partenariat = $row_dep['partenariat'];
    $commission_ht = $row_dep['commission_ht'];

    $sql = "INSERT INTO stagiaire_externe(id_stage, nom, prenom, adresse, code_postal, ville, date_naissance, lieu_naissance, tel, email, cas, num_permis, date_permis, lieu_permis, provenance, paiement, receipt, date_infraction, heure_infraction, lieu_infraction, partenariat, commission_ht, civilite, prenom2, prenom3, pays_naissance, adresse_complement, nom_usage) VALUES(\"$id_stage\", \"$nom\", \"$prenom\", \"$adresse\", \"$code_postal\", \"$ville\", \"$date_naissance\", \"$lieu_naissance\", \"$tel\", \"$email\", \"$cas\", \"$num_permis\", \"$date_permis\", \"$lieu_permis\", \"$provenance\", \"$paiement\", \"$receipt\", \"$date_infraction\", \"$heure_infraction\", \"$lieu_infraction\", \"$partenariat\", \"$commission_ht\", \"$civilite\",\"$prenom2\", \"$prenom3\", \"$pays_naissance\" ,\"$adresse_complement\" , \"$nom_usage\")";
    mysql_query($sql, $stageconnect) or die(mysql_error());

    (new UpdateStageAfterPayment())->__invoke($id_stage, $mysqli);

    mysql_close($stageconnect);
}

function update_diffusion($id_stage, $online)
{
    include("/home/prostage/connections/stageconnect.php");
    require_once "/home/prostage/www/debug.php";
    require_once("/home/prostage/connections/config.php");
    require_once "/home/prostage/www/params.php";
    //require_once "/home/prostage/www/display_error.php";
    require_once APP . 'logging/LogStage.php';

    $logStage = new LogStage($mysqli);

    mysql_select_db($database_stageconnect, $stageconnect);
    $is_manual_hide = $online == 0 ? 1 : 0;
    $sql = "UPDATE stage SET is_online = $online, is_manuel_hide=$is_manual_hide WHERE id = '$id_stage'";
    mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
    $logStage->logOnlineStage($id_stage, $online);
}

function update_prix($id_membre, $id_stage, $prix, $partenaire)
{
    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $query_site = "SELECT *, membre.nom as member_name FROM stage, membre WHERE membre.id = stage.id_membre AND stage.id = " . $id_stage;
    $rs = mysql_query($query_site) or die(mysql_error());
    if ($row = mysql_fetch_assoc($rs)) {

        $logfile = "/home/prostage/www/logs/update_pricing_stages.txt";
        $msg = date('d-m-Y H:i:s') . ' - Stage : ' . $id_stage . ' (' . $row["date1"] . ') - Membre : ' . $row["member_name"] . '(' . $id_membre . ') - Ancien Prix : ' . $row["prix"] . ' - Nouveau Prix : ' . $prix;
        $tmpfile = file_get_contents($logfile);
        file_put_contents($logfile, $msg . "\n" . $tmpfile);
    }
    $return = StageSet($id_membre, $id_stage, $prix, $partenaire);
    echo $return;
}

function update_receipt($id_stagiaire, $receipt)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "UPDATE stagiaire_externe SET receipt = '$receipt' WHERE id = '$id_stagiaire'";
    mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
}

function update_places($id_stage, $place)
{

    include("/home/prostage/connections/stageconnect.php");
    include "../modules/module.php";

    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "UPDATE stage SET nb_places_allouees = '$place' WHERE id = '$id_stage'";
    mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);

    $trainingApi = new \App\Actions\Api\TrainingApiAction();
    $trainingApi->updateDataStageApi($id_stage);
}

function update_max_places($id_stage, $place, $nb_inscrits)
{

    include("/home/prostage/connections/stageconnect.php");
    include "../modules/module.php";

    mysql_select_db($database_stageconnect, $stageconnect);

    $nbPlacesRestantes = ($place - $nb_inscrits);

    if ($nbPlacesRestantes <= 0) {
        $sql = "UPDATE stage SET nb_max_places = '$place', nb_places_allouees = '$nbPlacesRestantes', is_online = 0 WHERE id = '$id_stage'";
    } else {
        $sql = "UPDATE stage SET nb_max_places = '$place', nb_places_allouees = '$nbPlacesRestantes' WHERE id = '$id_stage'";
    }
    mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
}

function update_bafm($id_stage, $bafm, $ants_numero)
{
    require_once("/home/prostage/connections/config.php");
    require_once "/home/prostage/www/params.php";
    require_once ROOT . '/debug.php';

    require_once APP . 'search_animator/services/UpdateOneFieldCandidature.php';
    require_once APP . 'search_animator/email/SendValidateApplyEmail.php';
    require_once APP . 'search_animator/email/SendCancelApplyEmail.php';
    require_once APP . 'animator/services/RetrieveAnimatorById.php';
    require_once APP . 'Api/ants/services/UpdateAntsFormateurSequence.php';

    include("/home/prostage/connections/stageconnect.php");

    $sql = "SELECT id_bafm FROM stage WHERE id = $id_stage";
    $stage = mysqli_fetch_object($mysqli->query($sql));
    $animatorId = $stage->id_bafm;
    $animator = (new RetrieveAnimatorById())->__invoke($animatorId, $mysqli);

    if ($bafm == '') {
        $sql = "SELECT id FROM recherche_formateur WHERE id_stage = $id_stage AND type='bafm'";
        $search = mysqli_fetch_object($mysqli->query($sql));
        $id_search = $search->id;

        $sql = "SELECT id FROM candidature_formateur WHERE id_formateur = $animatorId AND id_recherche_formateur = $id_search";
        $apply = mysqli_fetch_object($mysqli->query($sql));

        (new UpdateOneFieldCandidature())->execute($apply->id, 0, 'status', $mysqli);
        //(new SendCancelApplyEmail())->execute($animator->email);
    }

    if ($ants_numero) {
        (new UpdateAntsFormateurSequence())->execute(
            $id_stage,
            $ants_numero,
            $animatorId,
            $bafm,
            $mysqli
        );
    }

    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "UPDATE stage SET id_bafm = '$bafm' WHERE id = '$id_stage'";
    mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
}

function update_psy($id_stage, $psy, $ants_numero)
{
    require_once("/home/prostage/connections/config.php");
    require_once "/home/prostage/www/params.php";
    require_once ROOT . '/debug.php';

    require_once APP . 'search_animator/services/UpdateOneFieldCandidature.php';
    require_once APP . 'search_animator/email/SendValidateApplyEmail.php';
    require_once APP . 'search_animator/email/SendCancelApplyEmail.php';
    require_once APP . 'animator/services/RetrieveAnimatorById.php';
    require_once APP . 'Api/ants/services/UpdateAntsFormateurSequence.php';

    include("/home/prostage/connections/stageconnect.php");

    $sql = "SELECT id_psy FROM stage WHERE id = $id_stage";
    $stage = mysqli_fetch_object($mysqli->query($sql));
    $animatorId = $stage->id_psy;
    $animator = (new RetrieveAnimatorById())->__invoke($animatorId, $mysqli);

    if ($psy == '') {

        $sql = "SELECT id FROM recherche_formateur WHERE id_stage = $id_stage AND type='psy'";
        $search = mysqli_fetch_object($mysqli->query($sql));
        $id_search = $search->id;

        $sql = "SELECT id FROM candidature_formateur WHERE id_formateur = $animatorId AND id_recherche_formateur = $id_search";
        $apply = mysqli_fetch_object($mysqli->query($sql));

        (new UpdateOneFieldCandidature())->execute($apply->id, 0, 'status', $mysqli);
        //(new SendCancelApplyEmail())->execute($animator->email);
    }

    if ($ants_numero) {
        (new UpdateAntsFormateurSequence())->execute(
            $id_stage,
            $ants_numero,
            $animatorId,
            $psy,
            $mysqli
        );
    }

    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "UPDATE stage SET id_psy = '$psy' WHERE id = '$id_stage'";
    mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
}

function update_hour($id_stage, $hour, $hour_type, $ants_numero)
{
    require_once("/home/prostage/connections/config.php");
    require_once "/home/prostage/www/params.php";
    require_once ROOT . '/debug.php';
    include("/home/prostage/connections/stageconnect.php");

    require_once APP . 'Api/ants/services/UpdateWsAntsStageDateSequence.php';

    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "UPDATE stage SET " . $hour_type . " = '$hour' WHERE id = '$id_stage'";
    mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);

    if ($ants_numero) {
        (new UpdateWsAntsStageDateSequence())->execute(
            $id_stage,
            $ants_numero,
            $hour,
            $hour_type,
            $mysqli
        );
    }

    //$trainingApi = new \App\Actions\Api\TrainingApiAction();
    //$trainingApi->updateDataStageApi($id_stage);
}

function affiche_stages($id_membre, $first_date, $end_date, $stagiaires, $departement, $status, $site, $display_animator_current_stage)
{

    $stagiaires_inscrits = stagiaires_inscrits_par_stage(1, $id_membre, $first_date, $end_date, $departement);
    $stagiaires_externes_inscrits = stagiaires_inscrits_par_stage(2, $id_membre, $first_date, $end_date, $departement);

    $stages = array();
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime($today . ' - 1 days'));

    if ($departement == 0)
        $departement_filter = "site.departement = site.departement";
    else
        $departement_filter = "site.departement = '$departement'";

    if ($site == 0) {
        $site_filter = " AND site.id = site.id";
    } else {
        $site_filter = " AND site.id = '$site'";
    }

    if ($status == 1)
        $status_filter = " AND stage.annule = 0 AND stage.nb_places_allouees > 0 ";
    else if ($status == 2)
        $status_filter = " AND (stage.annule > 0 OR stage.nb_places_allouees <= 0) ";
    else
        $status_filter = "";

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $query_site = "SELECT * FROM membre WHERE id = " . $id_membre;
    $rs = mysql_query($query_site, $stageconnect) or die(mysql_error());
    if ($row = mysql_fetch_assoc($rs))
        $nouveau_modele_commission = $row["nouveau_modele_commission"];
    else
        $nouveau_modele_commission = 0;
    $sql = "SELECT id, nom, prenom, formation FROM formateur WHERE id_membre = '$id_membre'";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    while ($row = mysql_fetch_assoc($rs))
        $formateurs[] = $row;

    $sql = "SELECT 
				stage.id,
				stage.date1,
				stage.annule,
				stage.nb_places_allouees,
				stage.prix,
				stage.debut_am,
				stage.fin_am,
				stage.debut_pm,
				stage.fin_pm,
				stage.id_bafm,
				stage.id_psy,
                stage.id_gta,
                stage.pass_sanitaire,
                stage.commission_ht,
                stage.partenariat,
               
				
				site.nom,
				site.adresse,
				site.code_postal,
				site.ville,
				site.actif
			FROM 
				stage, site
			WHERE
				stage.id_site = site.id AND
				stage.id_membre = '$id_membre' AND 
				stage.date1 >= '$first_date' AND
				stage.date1 <= '$end_date' AND " .
        $departement_filter . $status_filter . $site_filter . " ORDER BY date1 ASC";

    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);

    while ($row = mysql_fetch_assoc($rs)) {
        $stages[] = $row;
    }

    $c = "<table class=\"table main-table table-responsive-768 table-striped table-hover stage_table\" style='background-color: #FFF'>";
    $c .= "<tr class='hidden-xs'>";
    $c .= "<th style='background-color: #c2cce7'>Date</th>";
    //$c .= "<th style='text-align:center; background-color: #c2cce7'>Horaires</th>";
    $c .= "<th style='background-color: #c2cce7'>Ville</th>";
    $c .= "<th style='background-color: #c2cce7'>Lieu</th>";
    $c .= "<th style='background-color: #c2cce7'>BAFM</th>";
    $c .= "<th style='background-color: #c2cce7'>Psychologue</th>";

    if ($display_animator_current_stage) {
        $c .= "<th style='text-align:center; background-color: #c2cce7'>GTA</th>";
    }


    if ($nouveau_modele_commission == 1 || $id_membre >= 959) {
        $c .= "<th style='text-align:center; background-color: #c2cce7'>Prix Index<br>TTC</th>";
        $c .= "<th style='text-align:center; background-color: #c2cce7'>Reversement<br>TTC</th>";
    } else
        $c .= "<th style='text-align:center; background-color: #c2cce7'>Prix</th>";

    $c .= "<th style='text-align:center; background-color: #c2cce7'>Inscrits</th>";
    $c .= "<th style='text-align:center;width:200px; background-color: #c2cce7'>Actions</th>";
    $c .= "</tr>";

    foreach ($stages as $stage) {

        $id_stage = $stage['id'];
        $id_bafm = $stage['id_bafm'];
        $id_psy = $stage['id_psy'];
        $id_gta = $stage['id_gta'];
        $actif = intval($stage['actif']);
        $pass_sanitaire = $stage['pass_sanitaire'];
        $partenariat = $stage['partenariat'];

        $key_externe = array_search($stage['id'], array_column($stagiaires_externes_inscrits, 'id'));

        $key = array_search($stage['id'], array_column($stagiaires_inscrits, 'id'));
        if (($key === false || $stagiaires_inscrits[$key]['inscrits'] == 0) && (intval($stagiaires) == 1))
            continue;
        else if (($key !== false && $stagiaires_inscrits[$key]['inscrits'] > 0) && (intval($stagiaires) == 2))
            continue;

        $online = ($stage['annule'] == 0 && $stage['nb_places_allouees'] > 0) ? 1 : 0;
        $online_class = $online ? "online" : "offline";
        $online_title = $online ? "Mettre ce stage Hors-ligne" : "Cliquez ici pour remettre ce stage en ligne";

        $key_bafm = array_search($stage['id_bafm'], array_column($formateurs, 'id'));
        if ($key_bafm !== false) $bafm = utf8_decode($formateurs[$key_bafm]['nom']) . " " . utf8_decode($formateurs[$key_bafm]['prenom']);
        else                        $bafm = "Bafm à définir";

        $key_psy = array_search($stage['id_psy'], array_column($formateurs, 'id'));
        if ($key_psy !== false) $psy = utf8_decode($formateurs[$key_psy]['nom']) . " " . utf8_decode($formateurs[$key_psy]['prenom']);
        else                        $psy = "Psy à définir";

        $c .= "<tr style='border-top:1px solid #ccc'>";

        $c .= "<td style='border:0;vertical-align: middle'>";
        $c .= date('d-m-Y', strtotime($stage['date1']));
        $c .= "</td>";

        /*
        $c .= "<td style='text-align:center;border:0;vertical-align: middle'>";
        $liste_horaires_debut_am = array("07:00" => false, "07:15" => false, "07:30" => false, "07:45" => false, "08:00" => false, "08:15" => true, "08:30" => false, "08:45" => false, "09:00" => false, "09:15" => false, "09:30" => false, "09:45" => false, "10:00" => false, "10:15" => false, "10:30" => false, "10:45" => false, "11:00" => false, "11:15" => false, "11:30" => false, "11:45" => false, "12:00" => false, "12:15" => false, "12:30" => false, "12:45" => false, "13:00" => false, "13:15" => false, "13:30" => false, "13:45" => false, "14:00" => false, "14:15" => false, "14:30" => false, "14:45" => false);
        $liste_horaires_fin_am = array("11:00" => false, "11:15" => false, "11:30" => false, "11:45" => false, "12:00" => false, "12:15" => false, "12:30" => true, "12:45" => false, "13:00" => false, "13:15" => false, "13:30" => false, "13:45" => false, "14:00" => false, "14:15" => false, "14:30" => false, "14:45" => false, "15:00" => false, "15:15" => false, "15:30" => false, "15:45" => false, "16:00" => false, "16:15" => false, "16:30" => false, "16:45" => false, "17:00" => false, "17:15" => false, "17:30" => false, "17:45" => false, "18:00" => false);
        $liste_horaires_debut_pm = array("12:00" => false, "12:15" => false, "12:30" => false, "12:45" => false, "13:00" => false, "13:15" => false, "13:30" => true, "13:45" => false, "14:00" => false, "14:15" => false, "14:30" => false, "14:45" => false, "15:00" => false, "15:15" => false, "15:30" => false, "15:45" => false, "16:00" => false, "16:15" => false, "16:30" => false, "16:45" => false, "17:00" => false, "17:15" => false, "17:30" => false, "17:45" => false, "18:00" => false);
        $liste_horaires_fin_pm = array("16:00" => false, "16:15" => false, "16:30" => true, "16:45" => false, "17:00" => false, "17:15" => false, "17:30" => false, "17:45" => false, "18:00" => false, "18:15" => false, "18:30" => false, "18:45" => false, "19:00" => false, "19:15" => false, "19:30" => false, "19:45" => false, "20:00" => false, "20:15" => false, "20:30" => false, "20:45" => false, "21:00" => false);

        $c .= '<select class="debut_am select_hours2" name="debut_am[]" id="debut_am_' . $num_row . '" style="height:25px" id_stage=' . $id_stage . ' hour_type="debut_am">';
        foreach ($liste_horaires_debut_am as $horaire => $is_default) {
            $c .= "<option value='$horaire' " . ($horaire == $stage['debut_am'] ? " selected='selected'" : '') . ">$horaire</option>";
        }
        $c .= '</select><select class="fin_am select_hours2" name="fin_am[]" id="fin_am_' . $num_row . '" style="height:25px" id_stage=' . $id_stage . ' hour_type="fin_am">';
        foreach ($liste_horaires_fin_am as $horaire => $is_default) {
            $c .= "<option value='$horaire' " . ($horaire == $stage['fin_am'] ? " selected='selected'" : '') . ">$horaire</option>";
        }
        $c .= '</select>';
        $c .= '<br />';
        $c .= '<select class="debut_pm select_hours2" name="debut_pm[]" id="debut_pm_' . $num_row . '" style="height:25px" id_stage=' . $id_stage . ' hour_type="debut_pm">';
        foreach ($liste_horaires_debut_pm as $horaire => $is_default) {
            $c .= "<option value='$horaire' " . ($horaire == $stage['debut_pm'] ? " selected='selected'" : '') . ">$horaire</option>";
        }
        $c .= '</select><select class="fin_pm select_hours2" name="fin_pm[]" id="fin_pm_' . $num_row . '" style="height:25px" id_stage=' . $id_stage . ' hour_type="fin_pm">';
        foreach ($liste_horaires_fin_pm as $horaire => $is_default) {
            $c .= "<option value='$horaire' " . ($horaire == $stage['fin_pm'] ? " selected='selected'" : '') . ">$horaire</option>";
        }
        $c .= '</select>';
        $c .= "</td>";
        */

        $c .= "<td style=';border:0;vertical-align: middle;'>";
        $c .= $stage['code_postal'] . "<br>" . $stage['ville'];
        $c .= "</td>";

        $c .= "<td style=';border:0;vertical-align: middle; width: 300px'>";
        $c .= $stage['nom'] . "<br>" . $stage['adresse'];
        $c .= "</td>";

        $c .= "<td title='Cliquez pour changer les animateurs' style=';border:0;vertical-align: middle'>";

        $c .= "<p>";
        $c .= "<span class='bafm_container' id_stage='$id_stage' id_bafm='$id_bafm' style='cursor:pointer'>";
        $c .= "<span class='bafm'>" . $bafm . "</span>";
        $c .= "</span>";
        $c .= "</p>";

        $c .= "</td>";

        $c .= "<td title='Cliquez pour changer les animateurs' style=';border:0;vertical-align: middle'>";

        $c .= "<p>";
        $c .= "<span class='psy_container' id_stage='$id_stage' id_psy='$id_psy' style='cursor:pointer'>";
        $c .= "<span class='psy'>" . $psy . "</span>";
        $c .= "</span>";
        $c .= "</p>";

        $c .= "</td>";

        if ($display_animator_current_stage) {
            $c .= "<td style='border:0;vertical-align: middle; text-align: center'>";

            $c .= "<select class='select_gta' id='select_gta_" . $id_stage . "' id_psy=" . $id_psy . "  id_bafm=" . $id_bafm . " stageId=" . $id_stage . " style='height:25px;'>";
            $c .= "<option value=''>A renseigner</option>";
            $c .= "<option value='bafm' " . ($id_gta == $id_bafm ? 'selected' : '') . ">Bafm</option>";
            $c .= "<option value='psy' " . ($id_gta == $id_psy ? 'selected' : '') . ">Psy</option>";
            $c .= '</select>';

            $c .= "</td>";
        }

        $c .= "<td style='text-align:center;border:0;vertical-align: middle'>";
        $c .= "<select class='select_stage_prix' id='select_stage_prix_" . $id_stage . "' partenaire=" . $partenariat . " id_stage=" . $id_stage . " name='prix[]' style='height:25px;text-align:center'>";
        $prix_plancher = 1;
        $prix_plafond = 500;
        for ($j = $prix_plancher; $j <= $prix_plafond; $j++) {
            $c .= "<option value='$j' " . (($j == $stage['prix']) ? " selected='selected'" : '') . ">$j &euro;</option>";
        }
        $c .= '</select>';
        $c .= "</span>";
        $c .= "</td>";
        if ($nouveau_modele_commission == 1 || $id_membre >= 959)
            $c .= "<td style='text-align:center;border:0;vertical-align: middle'>" . number_format($stage['prix'] - ($stage['commission_ht'] * 1.2), 2, ',', ' ') . "</td>";

        $c .= "<td style='text-align:center;border:0;vertical-align: middle'>";
        $nb_inscrits = 0;
        $nb_inscrits += $key !== false ? $stagiaires_inscrits[$key]['inscrits'] : 0;
        $nb_inscrits += $key_externe !== false ? $stagiaires_externes_inscrits[$key_externe]['inscrits'] : 0;
        $c .= $nb_inscrits;
        $c .= "</td>";

        $c .= "<td style='border:0; width: 230px; text-align: center;vertical-align: middle'>";
        if ($actif)
            $c .= "<i title='$online_title' id_stage='$id_stage' class='fas fa-lightbulb fa-2x " . $online_class . "' style=';font-size:18px;'></i>";
        else
            $c .= "<i title=\"Ce stage est défini sur un lieu dont nous n'autorisons plus sa diffusion. Vous ne pouvez pas le mettre en ligne\" class='fas fa-minus-circle fa-2x' style=';font-size:18px;'></i>";
        if ($nouveau_modele_commission == 1 || $id_membre >= 959) {
            if ($partenariat == 2)
                $c .= "<i title='Désactiver Option Premium' id_stage='$id_stage' id_statut=1 class='fa fa-thumbs-o-up fa-2x' style='color:#EC971F;margin-left:10px;cursor:pointer;font-size:18px;'></i>";
            else
                $c .= "<i title=\"Activer l'Option Premium. \nL'Option Premium vous permet de bénéficier d'un montant de reversement plus élevé \nà condition que votre stage ne soit pas diffusé sur une autre Plateforme.\" id_stage='$id_stage' id_statut=2 class='fa fa-thumbs-o-up fa-2x' style='color:grey;margin-left:10px;cursor:pointer;font-size:18px;'></i>";
        }

        $c .= "<i id_stage='$id_stage' class='fas fa-user-plus fa-2x' isOpen='0' title=\"Ajouter un stagiaire provenant d'un autre réseau\" style=';font-size:18px;'></i>";
        $c .= "<i title='Voir la liste des candidats sur ce stage' id_stage='$id_stage' class='fas fa-users fa-2x' style='color:#EC971F;font-size:18px;'></i>";

        /*if ($nb_inscrits > 0) {
            $c .= "<i title='Accéder au dossier du stage' id_stage='$id_stage' class='fas fa-download fa-2x download_folder' style='color:#337ab7;font-size:18px;'></i>";
        } else {
            $c .= "<i title='Accéder au dossier du stage' id_stage='$id_stage' class='fas fa-download fa-2x' style='color:#337ab7; opacity: 0.2;font-size:18px;'></i>";
        }*/

        /*if ($pass_sanitaire == 1)
            $c .= "<i title='Désactiver le Pass vaccinal' id_stage='$id_stage' id_statut=0 class='fa fa-mobile fa-2x' style='color:#62AA2E;font-size:36px;padding-left:10px;padding-right:0px;cursor:pointer;font-size:26px;'></i>";
        else
            $c .= "<i title='Activer le Pass vaccinal sur ce stage' id_stage='$id_stage' id_statut=1 class='fa fa-mobile fa-2x' style='color:grey;font-size:36px;padding-left:10px;padding-right:0px;cursor:pointer;font-size:26px;'></i>";
        */

        if (!intval($online))
            $c .= "<i title=\"Ce stage est hors ligne. Pour pourvoir l'annuler,  cliquez d'abord sur l'ampoule rouge pour le remettre en ligne.\" id_stage='$id_stage' class='fas fa-times-circle fa-2x disabled pointer-events:none' style='color:grey;font-size:18px;'></i>";
        else
            $c .= "<i title='Annuler ce stage' nb_inscrits='$nb_inscrits' id_stage='$id_stage' class='fas fa-times-circle fa-2x' style='color:red;font-size:18px;'></i>";

        $c .= "<i title='Détail du stage' nb_inscrits='$nb_inscrits' isOpen='0' id_stage='$id_stage' class='fas fa-search fa-2x' style='font-size:18px;'></i>";

        $c .= "<p id='hidden_tr_$id_stage' style='display:none; margin-top:15px'>";

        $c .= "<i class='fas fa-tasks fa-2x'   style='color:#337ab7'  id='downloadFeuilleEmargement' title=\"Feuille d'émargement\" type='feuille_emargement' format='pdf' id_stage=" . $id_stage . "></i>";

        $c .= "<i class='far fa-file-archive fa-2x'    style='color:#337ab7'  style='color:#337ab7' id='downloadAttestations' title=\"Télécharger le dossier des attestations\" type='attestation' format='zip' id_stage=" . $id_stage . "></i>";

        $c .= "</p>";
        $c .= "</td>";

        $c .= "</tr>
        <tr id='tr_stagiaire_" . $id_stage . "' style='border:0;display:none;background:white;border-top:1px solid #ccc'>
        <td colspan=10 style='border:0;background: white;padding:0'><div id='stagiaires_" . $id_stage . "' style='    padding: 20px;
    background-color: #EFF2F9;'></div></td>
        </tr>";

        $c .= "</tr>
        <tr id='tr_stage_" . $id_stage . "' style='border:0;display:none;background:white;border-top:1px solid #ccc' class='stage_open'>
        <td colspan=10 style='border:0;background: white;padding:0'><div id='div_stage_" . $id_stage . "' class='div_stage_detail'></div></td>
        </tr>";

        $c .= "</tr>
        <tr id='tr_stagiaire_externe_" . $id_stage . "' style='border:0;display:none;background:white;border-top:1px solid #ccc' class='stagiaire_externe_open'>
        <td colspan=10 style='border:0;background: white;padding:0'><div id='div_stagiaire_externe_" . $id_stage . "' class='div_stage_detail'></div></td>
        </tr>";
    }

    $c .= "</table>";

    echo $c;
}

function affiche_lieux($params, $id_membre)
{

    $departement = intval($params['departement']);

    $lieux = array();

    if ($departement == 0)
        $departement_filter = "site.departement = site.departement";
    else
        $departement_filter = "site.departement = '$departement'";

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT site.id AS id_site FROM stage,site WHERE stage.id_site = site.id AND site.id_membre = " . $id_membre;
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());

    while ($row = mysql_fetch_assoc($rs)) {
        $stages[] = $row;
    }

    $sql = "SELECT 
				*
			FROM 
				site
			WHERE
				site.id_membre = " . $_SESSION['membre'] . " AND " . $departement_filter;

    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);

    while ($row = mysql_fetch_assoc($rs)) {
        $lieux[] = $row;
    }

    $c = "<table class=\"table main-table table-responsive-768 table-striped table-hover\">";
    $c .= "<tr class='hidden-xs'>";
    $c .= "<th width='20%'>Nom</th>";
    $c .= "<th width='20%'>Adresse</th>";
    $c .= "<th width='20%'>Ville</th>";
    $c .= "<th width='15%'>Gps</th>";
    $c .= "<th width='10%'>Commodités</th>";
    $c .= "<th width='10%'>Agrément</th>";
    $c .= "<th width='5%'>Actions</th>";
    $c .= "</tr>";

    foreach ($lieux as $lieu) {

        $id = $lieu['id'];

        $c .= "<tr>";

        $c .= "<td>";
        $c .= $lieu['nom'];
        $c .= "</td>";

        $c .= "<td>";
        $c .= $lieu['adresse'];
        $c .= "</td>";

        $c .= "<td>";
        $c .= $lieu['code_postal'] . " " . $lieu['ville'];
        $c .= "</td>";

        $c .= "<td>";
        $c .= "<p>lat: " . $lieu['longitude'] . "</p>";
        $c .= "<p>lon: " . $lieu['latitude'] . "</p>";
        $c .= "</td>";

        $c .= "<td>";
        $tmp = $lieu['commodites'];
        $commodites = explode(",", $tmp);
        $c .= ($commodites[0] == "on") ? "<i class='fas fa-parking'></i>" : "";
        $c .= ($commodites[1] == "on") ? "<i class='fas fa-utensils'></i>" : "";
        $c .= ($commodites[2] == "on") ? "<i class='fas fa-wheelchair'></i>" : "";

        $c .= "</td>";

        $c .= "<td>";
        $c .= $lieu['agrement'];
        if (!intval($lieu['actif']))
            $c .= "<p style='color:red;font-size:15px;font-weight:bold'>Lieu inactif</p>";
        $c .= "</td>";

        $c .= "<td>";
        $c .= "<i title='Modifier le lieu' id_site='$id' class='fas fa-pen fa-2x' style='color:black'></i>";

        $key = array_search($id, array_column($stages, 'id_site'));
        if ($key !== false) {
            $title = "Impossible de supprimer ce lieu car des inscriptions y ont déjà été réalisées";
            $disabled = "disabled";
            $c .= "<i title='$title' id_site='$id' class='fas fa-times-circle fa-2x $disabled' style='color:grey'></i>";
        } else {
            $title = "Supprimer ce lieu";
            $disabled = "";
            $c .= "<i title='$title' id_site='$id' class='fas fa-times-circle fa-2x delete_lieu $disabled' style='color:red'></i>";
        }


        $c .= "</td>";

        $c .= "</tr>";
    }

    $c .= "</table>";

    echo $c;
}

function affiche_formateurs($params, $id_membre)
{

    $formateurs = array();

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT stage.id_bafm, stage.id_psy FROM stage WHERE stage.id_membre = " . $id_membre;
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());

    while ($row = mysql_fetch_assoc($rs)) {
        $stages[] = $row;
    }

    $id_membre = $_SESSION['membre'];

    $sql = "SELECT 
				membre_formateur.*, formateur.*
			FROM 
				membre_formateur, formateur
			WHERE
			    membre_formateur.id_formateur = formateur.id  AND 
				membre_formateur.id_membre = $id_membre 
			      
			ORDER BY nom ASC";

    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);

    while ($row = mysql_fetch_assoc($rs)) {
        $formateurs[] = $row;
    }

    $c = "<table class=\"table main-table table-responsive-768 table-striped table-hover\" style='background-color: #FFF'>";
    $c .= "<tr class='hidden-xs'>";
    $c .= "<th style='background-color: #c2cce7'>Identité</th>";
    $c .= "<th style='background-color: #c2cce7'>Numéro d'autorisation d'animer</th>";
    $c .= "<th style='background-color: #c2cce7'>Métier</th>";
    $c .= "<th style='background-color: #c2cce7'>Adresse</th>";
    $c .= "<th style='background-color: #c2cce7'>Ville</th>";
    $c .= "<th style='background-color: #c2cce7'>Coordonnées</th>";
    $c .= "<th style='background-color: #c2cce7; text-align: center'>Actions</th>";
    $c .= "</tr>";

    foreach ($formateurs as $formateur) {

        $id = $formateur['id'];

        $c .= "<tr>";

        $c .= "<td>";
        $c .= ucfirst($formateur['nom']) . " " . ucfirst(strtolower($formateur['prenom']));
        $c .= "</td>";

        $c .= "<td>";
        $c .= 'B' . $formateur['num_autorisation'];
        $c .= "</td>";


        $c .= "<td>";
        $c .= $formateur['formation'];
        $c .= "</td>";

        $c .= "<td>";
        $c .= utf8_decode($formateur['adresse']);
        $c .= "</td>";

        $c .= "<td>";
        $c .= $formateur['code_postal'] . " " . utf8_decode($formateur['ville']);
        $c .= "</td>";

        $c .= "<td>";
        $c .= $formateur['tel'] . " " . $formateur['mobile'] . "<br>" . $formateur['email'];
        $c .= "</td>";

        $c .= "<td><div style='display: flex; align-items: center; justify-content: center'>";

        // $c .= "<i title='Modifier le formateur' id_formateur='$id' class='fas fa-pen fa-2x' style='color:black; margin-right: 15px'></i>";

        $key1 = array_search($id, array_column($stages, 'id_bafm'));
        $key2 = array_search($id, array_column($stages, 'id_psy'));
        if ($key1 !== false || $key2 !== false) {
            $title = "Impossible de supprimer ce formateur car il a déja été programmé sur des stages";
            $disabled = "disabled";
            $c .= "<i title='$title' id_formateur='$id' class='fas fa-times-circle fa-2x $disabled' style='color:grey'></i>";
        } else {
            $title = "Supprimer ce formateur";
            $disabled = "";
            $c .= "<i title='$title' id_formateur='$id' class='fas fa-times-circle fa-2x delete_formateur $disabled' style='color:red'></i>";
        }
        $c .= "<i style='font-size: 2em; margin-left: 15px; margin-top: 5px' class='fas fa-search fa-2x open_details' isOpen='0' animatorId='" . $id . "'></i>";

        $c .= "</div></td>";

        $c .= "</tr>";

        $c .= "
        <tr id='tr_animator_" . $id . "' style='border:0;display:none;background:white;border-top:1px solid #ccc' class='animator_open'>
        <td colspan=7 style='border:0;background: white;padding:0'><div id='div_animator_" . $id . "' class='div_animator'></div></td>
        </tr>";
    }

    $c .= "</table>";

    echo $c;
}

function formatPrix($prix)
{
    if ($prix == null || $prix == '')
        $prix = 0;

    return number_format($prix, 2, ',', ' ');
}


function get_commission_ht($id_membre, $paiement, $membre_comm, $min_comm, $max_comm, $dateInscription)
{
    $commission_ht = $paiement * ($membre_comm / 100);

    //rppc
    if ($id_membre == 793) {
        $from = new DateTimeImmutable('01-02-2019');
        $to = new DateTimeImmutable('01-05-2020');

        if ($dateInscription->getTimestamp() > $from->getTimestamp() && $dateInscription->getTimestamp() < $to->getTimestamp()) {
            if ($paiement <= 139)
                $commission_ht = $paiement * 0.15;
            else
                $commission_ht = $paiement * 0.22;
        } else {

            $commission_ttc = $paiement - 220;
            $commission_ht = $commission_ttc / 1.2;
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

function affiche_factures($params, $id_membre)
{

    $virements = array();
    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT * FROM virement WHERE id_membre = '$id_membre' AND date <= '2020-12-31' ORDER BY date DESC";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());

    while ($row = mysql_fetch_assoc($rs)) {
        $virements[] = $row;
    }

    require_once '/home/prostage/www/v2/Repository/MembreRepository.php';
    require_once '/home/prostage/www/v2/Repository/FactureCentreRepository.php';
    require_once '/home/prostage/www/v2/Repository/StagiaireRepository.php';

    $membreRepository = new MembreRepository();
    $factureCentreRepository = new FactureCentreRepository();
    $stagiaireRepository = new StagiaireRepository();
    $membre = $membreRepository->getMembreByMembreId($id_membre);

    $factures = array();
    $sql = "
        SELECT 0 as id,t.date_transaction as date,sum(s.paiement) as total_ttc,v.id AS id_virement, v.date AS date_virement, v.commentaire,v.total as virement_montant, v.num_suivi, v.commentaire_externe
            FROM transaction as t INNER JOIN virement AS v ON v.id = t.virement
            INNER JOIN stagiaire as s ON s.id=t.id_stagiaire 
            WHERE t.date_transaction>='2018-01-01' and t.id_membre=" . $id_membre . " and t.type_paiement='CB_OK' GROUP BY t.`virement` ORDER BY `v`.`date` DESC ";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    while ($row = mysql_fetch_assoc($rs)) {
        $factures[] = $row;
    }

    mysql_close($stageconnect);
    $stagiaires = $stagiaireRepository->findStagiairesEnAttenteFactureByMembreId($id_membre);

    $montantAReverserHT = 0;

    foreach ($stagiaires as $stagiaire) {

        $min_comm = vide($stagiaire['min_comm']) ? "36.8" : $stagiaire['min_comm'];
        $max_comm = vide($stagiaire['max_comm']) ? "43" : $stagiaire['max_comm'];

        $commissionHT = get_commission_ht($id_membre, $stagiaire['paiement'], $stagiaire['membre_commision2'], $min_comm, $max_comm, new DateTimeImmutable($stagiaire['date_inscription']));

        $commissionTTC = $commissionHT * 1.2;
        $reversementUnitaireHt = ($stagiaire['paiement'] - $commissionTTC) / 1.2;

        $montantAReverserHT += $reversementUnitaireHt;
    }

    if ($membre['assujetti_tva_confirme'] != 1)
        $montantAReverserTTC = $montantAReverserHT;
    else
        $montantAReverserTTC = $montantAReverserHT * 1.2;

    $c = "<table class=\"table main-table table-responsive-768 table-striped table-hover\" style='margin-bottom: 20px;background-color: #FFF'>";
    $c .= "<tr class='hidden-xs'>";
    $c .= "<th width='10%' style='background-color: #c2cce7; text-align: center'>Date émission facture</th>";
    $c .= "<th width='10%' style='background-color: #c2cce7; text-align: center'>Montant facture TTC</th>";
    $c .= "<th width='10%' style='background-color: #c2cce7; text-align: center'>Montant virement TTC</th>";
    $c .= "<th width='20%' style='background-color: #c2cce7; text-align: center'>Virement</th>";
    $c .= "<th width='40%' style='background-color: #c2cce7'>Commentaire</th>";
    $c .= "<th width='10%' style='background-color: #c2cce7; text-align: center'>Documents</th>";
    $c .= "</tr>";

    if (count($stagiaires) > 0) {
        $c .= "<tr>";
        $c .= "<td style='text-align: center;'>-</td>";
        $c .= "<td style='text-align: center;'>-</td>";
        $c .= "<td style='text-align: center;'>-</td>";
        $c .= "<td style='text-align: center'>En attente</td>";
        $c .= "<td style='text-align: center;'>-</td>";
        $c .= "<td style='text-align: center'><a style='float:left' href=\"virements_enattente_mc24.php\" target=\"_blank\"
						title=\"Télécharger récapitulatif virement en attente\"><i class='fas fa-file-pdf fa-2x'></i></a></td>";
        $c .= "</tr>";
    }

    foreach ($factures as $facture) {

        $I_dateToCheck = strtotime($facture['date_virement']);
        $I_dateRef = 1614556800; // Timestamp => 01/03/2021 00:00:00

        $numSuivi = $I_dateToCheck >= $I_dateRef ? $facture['num_suivi'] : 0;

        if (!empty($facture['date_virement']))
            $date_virement = $date_virement = date('d-m-Y', strtotime($facture['date_virement']));
        else
            $date_virement = '-';

        $id = $facture['id'];

        $c .= "<tr>";

        $c .= "<td style='text-align: center'>";
        $c .= date('d-m-Y', strtotime($facture['date_virement'] . ' -2 days'));
        $c .= "</td>";

        $c .= "<td style='text-align: center'>";
        $c .= formatPrix($facture['total_ttc'] - $facture['virement_montant']) . ' €';
        $c .= "</td>";

        $c .= "<td style='text-align: center'>";
        $c .= formatPrix($facture['virement_montant']) . ' €';
        $c .= "</td>";

        $c .= "<td style='text-align: center'>";
        if ($date_virement != '-')
            $c .= (is_null($id)) ? "En cours" : "Effectué le " . $date_virement;
        else
            $c .= 'En attente';
        $c .= "</td>";

        $c .= "<td>";
        $c .= $facture['commentaire_externe'];
        $c .= "</td>";

        $c .= "<td style='text-align: center'>";

        /*if($date_virement == '-') {
			$c .= "<a style='float:left' href=\"telecharger_recapitulatif_virement_en_attente.php?id_facture=$id\" target=\"_blank\"
		            title=\"Télécharger récapitulatif virement en attente\"><i class='fas fa-file-pdf fa-2x'></i></a>";
		} else {*/
        $c .= "
                <a style='float:left' href=\"telecharger_facture_centre2.php?id_membre=" . $id_membre . "&id_virement=" . $facture['id_virement'] . "&newNumeroFacture=" . $numSuivi . "\" target=\"_blank\" title=\"Télécharger facture\"><i class='fas fa-file-pdf fa-2x'></i></a>
                <a style='float:left; margin-right: 20px; margin-left: 20px;' href=\"recapitulatif_virements_mc24.php?id_facture=" . $id . "&id_virement=" . $facture['id_virement'] . "&newNumeroFacture=" . $numSuivi . "\" target=\"_blank\"title=\"Télécharger récapitulatif virement\"><i class='fas fa-list fa-2x'></i></a>
		        <a style='float:left' href=\"factures_centre_mc24.php?rv=1&id_membre=" . $id_membre . "&id_virement=" . $facture['id_virement'] . "&newNumeroFacture=" . $numSuivi . "\" target=\"_blank\" title=\"Télécharger Facture Globale\"><i class='fas fa-file-pdf fa-2x'></i></a>
            ";
        //}

        $c .= "</td>";

        $c .= "</tr>";
    }

    $c .= "</table>";

    echo $c;
    if ($id_membre < 959)
        echo '<span style="    color: #1a1a1a;
    font-size: 19px;
    margin: 20px 0 15px;
    line-height: 1.3em;
    display: inline-block;">Historique - (Jusqu’au 31/12/2020)</span>
      <button style="margin-left: 5px;" class="btn btn-warning btn-sm" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
          +
        </button>';

    echo '<div class="collapse" id="collapseExample">';

    $c = "<table class=\"table main-table table-responsive-768 table-striped table-hover\">";
    $c .= "<tr class='hidden-xs'>";
    $c .= "<th width='10%'>Date Emission Facture</th>";
    $c .= "<th width='15%'>Montant TTC</th>";
    $c .= "<th width='30%'>Virement</th>";
    $c .= "<th width='40%'>Commentaire</th>";
    $c .= "<th width='10%'>Actions</th>";
    $c .= "</tr>";

    foreach ($virements as $virement) {

        if (!empty($virement['date']))
            $date_virement = date('d-m-Y', strtotime($virement['date']));
        else
            $date_virement = '-';

        $id = $virement['id'];

        $c .= "<tr>";

        $c .= "<td>";
        $c .= date('d-m-Y', strtotime($virement['date']));
        $c .= "</td>";

        $c .= "<td>";
        $c .= formatPrix($virement['total']) . ' €';
        $c .= "</td>";

        $c .= "<td>";
        if ($date_virement != '-')
            $c .= (is_null($id)) ? "En cours" : "Effectué le " . $date_virement;
        else
            $c .= '-';
        $c .= "</td>";

        $c .= "<td>";
        $c .= $virement['commentaire'];
        $c .= "</td>";

        $c .= "<td>";
        $c .= "<a style='float:left' href=\"liste_stagiaire_virement.php?id_virement=$id\" target=\"_blank\"
		title=\"Télécharger facture\"><i class='fas fa-file-pdf fa-2x'></i></a>";
        $c .= "</td>";

        $c .= "</tr>";
    }

    $c .= "</table>";

    echo $c;

    echo '</div>';
}

function affiche_stagiaires_erreur($params, $id_membre, $idstage = NULL)
{

    $errors = array("", "Pas de stagiaire trouvé", "Multiple résultats", "Stagiaire non présent chez Rppc", "Pas d'identifiant chez RPPC", "Les identifiants stagiaires sont différents", "Les identifiants de stage sont différents", "Le statut d'inscription est divergeant", "Les dates de stage sont différentes", "Les codes postaux sont differents");

    $stagiaires = array();
    $stagiaires_externes = array();

    $first_date = $params['first_date'];
    $end_date = $params['end_date'];
    $departement = $params['departement'];
    $id_stagiaire = $params['id_stagiaire'];

    if ($departement == 0)
        $departement_filter = "site.departement = site.departement";
    else
        $departement_filter = "site.departement = '$departement'";

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    if (isset($idstage) && !is_null($idstage)) {
        $idstage_filter = " AND stage.id = '$idstage' ";
        $_SESSION['id_stage'] = NULL;

        $sql = "SELECT date1 FROM stage WHERE id = '$idstage'";
        $rs = mysql_query($sql, $stageconnect); // or die(mysql_error());
        $row = mysql_fetch_assoc($rs);
        $first_date = $row['date1'];
        $end_date = $row['date1'];
    } else
        $idstage_filter = "";

    $sql = "SELECT 
					stagiaire.id,
					stagiaire.id_externe,
					stagiaire.id_stage,
					stagiaire.nom,
					stagiaire.prenom,
					stagiaire.date_inscription,
					stagiaire.cas,
					stagiaire.paiement,
					stagiaire.supprime,
					stagiaire.remboursement,
					stagiaire.numtrans,
					stagiaire.numappel,
					stagiaire.commission,
					stagiaire.error_rppc,
					stagiaire.timestamp_verif_rppc,
					
					stage.date1,
					site.code_postal,
					site.ville
				FROM 
					stage, site, stagiaire
				WHERE
					stagiaire.error_rppc > 0 AND
					stagiaire.numtrans != '' AND
					stagiaire.id_stage = stage.id AND
					stagiaire.paiement > 0 AND
					stage.id_site = site.id AND
					stage.id_membre = '$id_membre' AND 
					stage.date1 >= '$first_date' AND
					stage.date1 <= '$end_date' AND " .
        $departement_filter . $idstage_filter . " 
				ORDER BY stage.date1 ASC";

    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $total = mysql_num_rows($rs);
    while ($row = mysql_fetch_assoc($rs)) {

        $row['provenance'] = 1;
        $row['table'] = '1';
        $stagiaires[] = $row;
    }

    $c = "<table class=\"table main-table table-responsive-768 table-striped table-hover\">";
    $c .= "<tr class='hidden-xs'>";
    $c .= "<th width='10%'>Identité</th>";
    $c .= "<th width='7%'>Inscription</th>";
    $c .= "<th width='15%'>Stage PSP</th>";
    $c .= "<th width='15%'>Stage RPPC</th>";
    $c .= "<th width='5%'>Cas</th>";
    $c .= "<th width='5%'>Paiement</th>";
    $c .= "<th width='7%'>Statut</th>";
    $c .= "<th width='7%'>Date vérifiation</th>";
    $c .= "<th width='15%' style='text-align:center'>Erreur</th>";
    $c .= "<th width='7%'>Actions</th>";
    $c .= "</tr>";

    foreach ($stagiaires as $stagiaire) {

        $table = intval($stagiaire['table']);

        if (($stagiaire['supprime'] == 1) &&
            vide($stagiaire['numtrans']) &&
            ($table == 1)
        )
            continue;

        if ($stagiaire['supprime'] == 1 && $stagiaire['error_rppc'] != 7) //Le statut d'inscription est divergeant
            continue;

        $id_stagiaire = $stagiaire['id'];

        $c .= "<tr class='ligne'>";

        $c .= "<td>";
        $c .= strtoupper($stagiaire['nom']) . " " . ucfirst($stagiaire['prenom']) . "<br>" . "ID rppc " . $stagiaire['id_externe'];
        $c .= "</td>";

        $c .= "<td>";
        $c .= date('d-m-Y', strtotime($stagiaire['date_inscription']));
        $c .= "</td>";

        $c .= "<td><strong>";
        $c .= date('d-m-Y', strtotime($stagiaire['date1'])) . "<br>";
        $c .= $stagiaire['code_postal'] . " " . $stagiaire['ville'];
        $c .= "</strong></td>";

        $c .= "<td>";
        $c .= "</td>";

        $c .= "<td style='text-align:center'>";
        $c .= $stagiaire['cas'];
        $c .= "</td>";

        $c .= "<td style='text-align:center'>";
        $c .= $stagiaire['paiement'];
        $c .= "</td>";

        $c .= "<td style='text-align:center'>";
        $status_class = $stagiaire['supprime'] == 0 ? "inscrit" : "annule";
        $c .= "<span class='status $status_class'></span>";
        $c .= "</td>";

        $c .= "<td style='text-align:center'>";
        $c .= $stagiaire['timestamp_verif_rppc'];
        $c .= "</td>";

        $c .= "<td style='text-align:center'>";
        $c .= $errors[$stagiaire['error_rppc']];
        $c .= "</td>";

        $c .= "<td>";
        $c .= "</td>";

        $c .= "</tr>";
    }

    $c .= "</table>";

    echo $c;
}

function affiche_stagiaires($params, $id_membre, $idstage = NULL)
{

    $stagiaires = array();
    $stagiaires_externes = array();

    $first_date = $params['first_date'];
    $end_date = $params['end_date'];
    $departement = $params['departement'];
    $id_stagiaire = $params['id_stagiaire'];
    $status_ants = $params['status_ants'];
    $dossier_complet = $params['dossier_complet'];

    if ($departement == 0)
        $departement_filter = "site.departement = site.departement";
    else
        $departement_filter = "site.departement = '$departement'";

    include("/home/prostage/connections/stageconnect.php");
    require_once "../params.php";
    require_once "../debug.php";

    mysql_select_db($database_stageconnect, $stageconnect);

    if (isset($idstage) && !is_null($idstage)) {
        $idstage_filter = " AND stage.id = '$idstage' ";
        $_SESSION['id_stage'] = NULL;

        $sql = "SELECT date1 FROM stage WHERE id = '$idstage'";
        $rs = mysql_query($sql, $stageconnect); // or die(mysql_error());
        $row = mysql_fetch_assoc($rs);
        $first_date = $row['date1'];
        $end_date = $row['date1'];
    } else
        $idstage_filter = "";

    $filterStagiaire = '';
    $filterStagiaireExterne = '';

    if ($dossier_complet != 0) {
        switch ($dossier_complet) {
            case 1:
                $filterStagiaire = " AND stagiaire.validations_stagiaire='1|1|1|1'";
                break;
            default:
                $filterStagiaire = " AND stagiaire.validations_stagiaire!='1|1|1|1'";
                break;
        }
    }

    if ($status_ants > 1) {
        switch ($status_ants) {
            case 2:
                $filterStagiaire = ' AND stagiaire.ants_idDemande=0';
                $filterStagiaireExterne = ' AND stagiaire_externe.ants_idDemande=0';
                break;
            default:
                $statut = StatutDemandeCode($status_ants);
                $filterStagiaire = ' AND stagiaire.ants_idDemande > 0 AND stagiaire.ants_statut in ' . $statut;
                $filterStagiaireExterne = ' AND stagiaire_externe.ants_idDemande > 0 AND stagiaire_externe.ants_statut in ' . $statut;
                break;
        }
    }

    if (is_numeric($id_stagiaire) && $id_stagiaire > 0) {

        $id_stagiaire = intval($id_stagiaire);
        $sql = "SELECT 
					stagiaire.id,
					stagiaire.id_stage,
					stagiaire.nom,
					stagiaire.prenom,
					stagiaire.date_inscription,
					stagiaire.cas,
					stagiaire.paiement,
                    stagiaire.price_transfer,
					stagiaire.supprime,
					stagiaire.remboursement,
					stagiaire.numtrans,
					stagiaire.numappel,
					stagiaire.commission,
					stagiaire.tel,
					stagiaire.mobile,
					stagiaire.email,
                    stagiaire.ants_erreurs,
                    stagiaire.ants_statut,
                    stagiaire.ants_idDemande,
                    stagiaire.ants_numeroDemande,
                    stagiaire.validations_stagiaire,
       
					stage.date1,
					site.code_postal,
					site.ville
				FROM 
					stage, site, stagiaire
				WHERE
					stagiaire.numtrans != '' AND
					stagiaire.id_stage = stage.id AND
					stage.id_site = site.id AND
					stage.id_membre = '$id_membre' AND 
					stagiaire.id = '$id_stagiaire' $filterStagiaire";
    } else
        $sql = "SELECT 
					stagiaire.id,
					stagiaire.id_stage,
					stagiaire.nom,
					stagiaire.prenom,
					stagiaire.date_inscription,
					stagiaire.cas,
					stagiaire.paiement,
                    stagiaire.price_transfer,
					stagiaire.supprime,
					stagiaire.remboursement,
					stagiaire.numtrans,
					stagiaire.numappel,
					stagiaire.commission,
					stagiaire.tel,
					stagiaire.mobile,
					stagiaire.email,
                    stagiaire.ants_erreurs,
                    stagiaire.ants_statut,
                    stagiaire.ants_idDemande,
                    stagiaire.ants_numeroDemande,
                    stagiaire.validations_stagiaire,
					
					stage.date1,
					site.code_postal,
					site.ville
				FROM 
					stage, site, stagiaire
				WHERE
					stagiaire.numtrans != '' AND
					stagiaire.id_stage = stage.id AND
					stage.id_site = site.id AND
					stage.id_membre = '$id_membre' AND 
					stage.date1 >= '$first_date' AND
					stage.date1 <= '$end_date' $filterStagiaire AND " .
            $departement_filter . $idstage_filter . "
				ORDER BY stagiaire.date_inscription DESC";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $total = mysql_num_rows($rs);
    while ($row = mysql_fetch_assoc($rs)) {

        $row['provenance'] = 1;
        $row['table'] = '1';
        $stagiaires[] = $row;
    }

    if (is_numeric($id_stagiaire) && $id_stagiaire > 0) {

        $sql = "SELECT 
					stagiaire_externe.id,
					stagiaire_externe.id_stage,
					stagiaire_externe.nom,
					stagiaire_externe.prenom,
					stagiaire_externe.date_inscription,
					stagiaire_externe.cas,
					stagiaire_externe.paiement,  
                    stagiaire_externe.price_transfer,
					stagiaire_externe.supprime,
					stagiaire_externe.remboursement,
					stagiaire_externe.numtrans,
					stagiaire_externe.numappel,
					stagiaire_externe.commission,
					stagiaire_externe.receipt,
					stagiaire_externe.tel,
					stagiaire_externe.email,
       
                    stagiaire_externe.ants_erreurs,
                    stagiaire_externe.ants_statut,
                    stagiaire_externe.ants_idDemande,
                    stagiaire_externe.ants_numeroDemande,
       
					
					stage.date1,
					site.code_postal,
					site.ville,
					
					stagiaire_externe.provenance
				FROM 
					stage, site, stagiaire_externe
				WHERE
					stagiaire_externe.id_stage = stage.id AND
					stage.id_site = site.id AND
					stage.id_membre = '$id_membre' AND 
					stagiaire_externe.id = '$id_stagiaire'  $filterStagiaireExterne ";

        $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
        mysql_close($stageconnect);
        $total = mysql_num_rows($rs);
    } else {
        $sql = "SELECT 
					stagiaire_externe.id,
					stagiaire_externe.id_stage,
					stagiaire_externe.nom,
					stagiaire_externe.prenom,
					stagiaire_externe.date_inscription,
					stagiaire_externe.cas,
					stagiaire_externe.paiement,  
                    stagiaire_externe.price_transfer,
					stagiaire_externe.supprime,
					stagiaire_externe.remboursement,
					stagiaire_externe.numtrans,
					stagiaire_externe.numappel,
					stagiaire_externe.commission,
					stagiaire_externe.receipt,
					stagiaire_externe.tel,
					stagiaire_externe.email,
       
                    stagiaire_externe.ants_erreurs,
                    stagiaire_externe.ants_statut,
                    stagiaire_externe.ants_idDemande,
                    stagiaire_externe.ants_numeroDemande,
					
					
					stage.date1,
					site.code_postal,
					site.ville,
					
					stagiaire_externe.provenance
				FROM 
					stage, site, stagiaire_externe
				WHERE
					stagiaire_externe.id_stage = stage.id AND
					stage.id_site = site.id AND
					stage.id_membre = '$id_membre' AND 
					stage.date1 >= '$first_date' AND
					stage.date1 <= '$end_date' $filterStagiaireExterne AND " .
            $departement_filter . $idstage_filter . "
				ORDER BY stagiaire_externe.date_inscription DESC";

        $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
        mysql_close($stageconnect);
        $total = mysql_num_rows($rs);
    }

    if ($total) {
        while ($row = mysql_fetch_assoc($rs)) {

            $row['date_inscription'] = date('Y-m-d', strtotime($row['date_inscription']));
            $row['table'] = '2';
            $row['validations_stagiaire'] = 'externe';
            $stagiaires_externes[] = $row;
        }

        $stagiaires = array_merge($stagiaires, $stagiaires_externes);
        usort($stagiaires, "tri_stagiaires");
    }

    $c = "<table class=\"table main-table table-responsive-768 table-striped table-hover stagiaire_table\" style='background-color: #FFF'>";
    $c .= "<tr class='hidden-xs'>";
    $c .= "<th style='background-color: #c2cce7'>Identité</th>";
    $c .= "<th style='background-color: #c2cce7'>Inscription</th>";
    $c .= "<th style='background-color: #c2cce7'>Stage</th>";
    $c .= "<th style='background-color: #c2cce7'>Cas</th>";
    $c .= "<th style='text-align: center; background-color: #c2cce7'>Prix Index</th>";
    $c .= "<th style='background-color: #c2cce7; text-align: center'>Réception<br>Paiement</th>";
    $c .= "<th style='text-align: center; background-color: #c2cce7'>Provenance</th>";
    $c .= "<th style='white-space:nowrap; background-color: #c2cce7; text-align: center'>Dossier Stagiaire</th>";
    $c .= "<th style='text-align: center; background-color: #c2cce7'>Statut</th>";
    $c .= "<th style='text-align: center; background-color: #c2cce7'>Dossier ANTS</th>";
    $c .= "<th width='180px;' style='text-align: center; background-color: #c2cce7'>Actions</th>";
    $c .= "</tr>";

    foreach ($stagiaires as $stagiaire) {

        $table = intval($stagiaire['table']);

        $paiement = $stagiaire['paiement'] - $stagiaire['price_transfer'];

        if (($stagiaire['supprime'] == 1) &&
            vide($stagiaire['numtrans']) &&
            ($table == 1)
        )
            continue;

        $id_stagiaire = $stagiaire['id'];

        $documents_telecharges = documents_telecharges($stagiaire['id_stage'], $stagiaire['date1'], $id_stagiaire);
        $dossier = "/home/prostage/www/stages/mois/" . date('Ym', strtotime($stagiaire['date1'])) . "/" . $stagiaire['id_stage'];
        $dossier_url = str_replace("/home/prostage/www", "https://www.prostagespermis.fr", $dossier);

        $c .= "<tr class='ligne' id='tr_" . $id_stagiaire . "'>";

        $c .= "<td>";
        $c .= mb_strtoupper($stagiaire['nom']) . " " . ucfirst($stagiaire['prenom']);
        /*$c .= "<p>";
		$c .= isset($stagiaire['mobile']) ? $stagiaire['mobile'] : "";
		$c .= isset($stagiaire['tel']) ? " ".$stagiaire['tel'] : "";
		$c .= "<br>";
		$c .= $stagiaire['email'];
		$c .= "</p>";*/
        $c .= "</td>";

        $c .= "<td>";
        $c .= date('d-m-Y', strtotime($stagiaire['date_inscription']));
        $c .= "</td>";

        $c .= "<td>";
        $c .= date('d-m-Y', strtotime($stagiaire['date1'])) . "<br>";
        $c .= $stagiaire['code_postal'] . " " . $stagiaire['ville'];
        $c .= "</td>";

        $c .= "<td style='text-align:center'>";
        $c .= $stagiaire['cas'];
        $c .= "</td>";

        $c .= "<td style='text-align:center'>";
        $c .= $paiement;
        $c .= "</td>";

        $c .= "<td style='text-align:center'>";
        if ($stagiaire['provenance'] == 1)
            $c .= "Reçu";
        else {
            $c .= "<span class='receipt_container' id_stagiaire='$id_stagiaire' style='cursor:pointer'>";
            $c .= "<span class='receipt'>";
            $c .= intval($stagiaire['receipt']) ? "Reçu" : "En attente";
            $c .= "</span>";
            $c .= "</span>";
        }
        $c .= "</td>";

        $c .= "<td style='text-align: center'>";
        if ($stagiaire['provenance'] == 1) $provenance = "Prostagespermis";
        else if ($stagiaire['provenance'] == 2) $provenance = "Autre plateforme";
        else if ($stagiaire['provenance'] == 3) $provenance = "Réseau propre";
        else $provenance = "";
        $c .= $provenance;
        $c .= "</td>";

        /*
		$c .= "<td style='text-align:center'>";
		$c .= $stagiaire['commission']/100;;
		$c .= "</td>";
		*/

        $c .= "<td style='white-space:nowrap; text-align: center'>";
        if ($stagiaire['validations_stagiaire'] == 'externe') {
            $validation_status = 'Externe';
        } else {
            $validation_status = $stagiaire['validations_stagiaire'] == '1|1|1|1' ? 'Complet' : 'Incomplet';
        }
        $c .= "<span>" . $validation_status . "</span>";
        /*
        foreach ($documents_telecharges as $document_telecharge) {

            if (stristr($document_telecharge, "verso")) $type = "Permis<br>verso";
            else if (stristr($document_telecharge, "permis")) $type = "Permis<br>recto";
            else if (stristr($document_telecharge, "rii")) $type = "RII";
            else if (stristr($document_telecharge, "48n")) $type = "48N";
            else if (stristr($document_telecharge, "ordonnance_penale")) $type = "Ordonnance<br>penale";
            else if (stristr($document_telecharge, "suspension")) $type = "Suspension";
            else if (stristr($document_telecharge, "retention")) $type = "Retention";
            else if (stristr($document_telecharge, "perte")) $type = "Perte";
            else if (stristr($document_telecharge, "cni")) $type = "Carte<br>identite";
            else if (stristr($document_telecharge, "vol")) $type = "Declaration<br>vol";
            else if (stristr($document_telecharge, "demande_permis")) $type = "Justificatif<br>demande permis";
            else continue;

            $url = $dossier_url . "/" . $document_telecharge;

            $c .= "<div style='float:left;margin-right:25px'>
					<a target='_blank' href='$url' download>
					<i class='far fa-file-alt fa-2x'></i><br>$type</a></div>";
        }
        */

        $c .= "</td>";

        $c .= "<td style='text-align:center'>";
        $status_class = $stagiaire['supprime'] == 0 ? "inscrit" : "annule";
        $c .= "<span class='status $status_class'></span>";
        $c .= "</td>";


        $c .= "<td style='text-align:center'>";
        $c .= "<span class='status_ants' data-antsDirectoryStatus='" . $stagiaire['ants_statut'] . "' data-antsMessage='" . parseAntsErrorDirectory($stagiaire['ants_erreurs']) . "'>" . StatutDemandeTexteGet($stagiaire['ants_statut']) . "</span>";
        $c .= "</td>";


        $c .= "<td>";
        $c .= "<i class='fas fa-file-pdf fa-2x'  style='color:#337ab7' id='downloadAttestation' title=\"Télécharger l'attestation du stagiaire pré-remplie\" type='attestation' format='pdf' id_stage=" . $stagiaire['id_stage'] . " id_stagiaire=" . $id_stagiaire . "></i>";

        //$c .= "<a style='float:left' href=\"attestation_stage.php?id_stagiaire=$id_stagiaire&table=$table\" target=\"_blank\"title=\"Télécharger l'attestation de stage\"><i class='fas fa-file-pdf fa-2x'></i></a>";

        $c .= "<div style='margin-left:4%;text-align:center;float:left'>";

        $liste = array();
        if (is_dir($dossier)) {

            $matche = '#^' . $id_stagiaire . '_attestation_signee#ui';
            $f = new FilesystemIterator($dossier, FilesystemIterator::KEY_AS_FILENAME);
            $r = new RegexIterator($f, $matche, RegexIterator::MATCH, RegexIterator::USE_KEY);
            foreach ($r as $t) {
                array_push($liste, $t->getFilename());
            }
        }

        if (count($liste)) {
            $path_2 = str_replace("/home/prostage/www", "https://www.prostagespermis.fr", $dossier);
            $url = $path_2 . "/" . $liste[0];
            $c .= "<a target='blank' href='$url' download><i title='Attestation du stagiaire signée' class='fas fa-file-pdf fa-2x' style='color:orange'></i></a>
				<br>
				<i style='color:red' class='fas fa-times' title='Supprimer l’attestation stagiaire signée' onclick='deleteAttestationSignee(&apos;" . $liste[0] . "&apos;, &apos;" . $dossier . "&apos;)'></i>";
        } else {

            $type = "attestation_signee";
            $form = "form_attestation_signee_" . $id_stagiaire;
            $name = "file_attestation_signee_" . $id_stagiaire;
            if ($table == 2)
                $c .= "<i class='fas fa-upload fa-2x disabled' style='color:orange' title='Charger l’attestation du stagiaire signée. Vous ne pouvez pas effectuer cette action sur un candidat ne provenant pas de ProstagesPermis'></i>";
            else
                $c .= "<form style='float:right' id='$form' method='post' enctype='multipart/form-data' action='https://www.prostagespermis.fr/ep/ajax_upload.php' onchange='upload(this, &apos;" . $type . "&apos;, &apos;" . $id_stagiaire . "&apos;, &apos;" . $dossier . "&apos;)'><input type='file' name='file_attestation_signee' id='$name' style='display:none'/>
					<i class='fas fa-upload fa-2x' style='color:orange' id='upload_link' onclick='$(&apos;#$name:hidden&apos;).trigger(&apos;click&apos;);' style='cursor:pointer' title='Charger l’attestation du stagiaire signée'></i>
					</form>";
        }
        $c .= "</div>";

        $c .= "<i table='$table' title='Envoyer un message au stagiaire' id_stagiaire='$id_stagiaire' class='far fa-comment-alt fa-2x'></i>";

        //bouton annulation
        $disabled = $stagiaire['supprime'] ? "disabled" : "";
        if (strtotime($stagiaire['date1']) <= strtotime(date('Y-m-d'))) {
            $title = "Il est trop tard pour annuler ce candidat. Vous aviez jusqu’à la veille du stage minuit";
            $disabled = "disabled";
        } else if ($stagiaire['supprime']) {
            $title = "Candidat déjà annulé";
            $disabled = "disabled";
        } else {
            $title = "Annuler l’inscription du candidat";
            $disabled = "";
        }
        $c .= "<i table='$table' title='$title' id_stagiaire='$id_stagiaire' id_stage='$idstage' class='fas fa-times-circle fa-2x $disabled' style='color:red;'></i>";

        $c .= "<i table='$table' title='Consulter ou modifier la fiche du stagiaire' isOpen='0' id_stagiaire='$id_stagiaire' id_member='$id_membre' class='fas fa-search fa-2x' style='color:#000'></i>";

        $c .= "</td>";

        $c .= "</tr>";
        $c .= '<tr style="border:0px;display:none" id="tr_student_' . $id_stagiaire . '" class="ligne student_open"><td colspan="11" style="border:0px;"><div class="div_student" id="div_student_' . $id_stagiaire . '"></div></td></tr>';
    }

    $c .= "</table>";

    echo $c;
}


function affiche_stagiaires_stage($params, $id_membre)
{
    $stagiaires = array();
    $stagiaires_externes = array();
    $idstage = $params['id_stage'];

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT 
					stagiaire.id,
					stagiaire.id_stage,
					stagiaire.nom,
					stagiaire.prenom,
					stagiaire.date_inscription,
					stagiaire.cas,
					stagiaire.paiement,
                    stagiaire.price_transfer,
					stagiaire.supprime,
					stagiaire.remboursement,
					stagiaire.numtrans,
					stagiaire.numappel,
					stagiaire.commission,
					stagiaire.tel,
					stagiaire.mobile,
					stagiaire.email,
                    stagiaire.validations_stagiaire,
					
					stage.date1,
                    stage.prix,
					site.code_postal,
					site.ville
				FROM 
					stage, site, stagiaire
				WHERE
					stagiaire.numtrans != '' AND
					stagiaire.id_stage = stage.id AND
					stage.id_site = site.id AND
					stage.id = '$idstage' 
				ORDER BY stagiaire.date_inscription DESC";

    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    $total = mysql_num_rows($rs);
    while ($row = mysql_fetch_assoc($rs)) {

        $row['provenance'] = 1;
        $row['table'] = '1';
        $stagiaires[] = $row;
    }

    $sql = "SELECT 
					stagiaire_externe.id,
					stagiaire_externe.id_stage,
					stagiaire_externe.nom,
					stagiaire_externe.prenom,
					stagiaire_externe.date_inscription,
					stagiaire_externe.cas,
					stagiaire_externe.paiement,      
                    stagiaire_externe.price_transfer,
					stagiaire_externe.supprime,
					stagiaire_externe.remboursement,
					stagiaire_externe.numtrans,
					stagiaire_externe.numappel,
					stagiaire_externe.commission,
					stagiaire_externe.receipt,
					stagiaire_externe.tel,
					stagiaire_externe.email,
					
					stage.date1,
                    stage.prix,
					site.code_postal,
					site.ville,
					
					stagiaire_externe.provenance
				FROM 
					stage, site, stagiaire_externe
				WHERE
					stagiaire_externe.id_stage = stage.id AND
					stage.id_site = site.id AND
					stage.id = '$idstage'
				ORDER BY stagiaire_externe.date_inscription DESC";

    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
    $total = mysql_num_rows($rs);


    if ($total) {
        while ($row = mysql_fetch_assoc($rs)) {

            $row['date_inscription'] = date('Y-m-d', strtotime($row['date_inscription']));
            $row['table'] = '2';
            $row[''];
            $stagiaires_externes[] = $row;
        }

        $stagiaires = array_merge($stagiaires, $stagiaires_externes);
        usort($stagiaires, "tri_stagiaires");
    }

    $c = "<table class=\"table main-table table-responsive-768 table-striped table-hover\" style='margin-top:0;margin-bottom:0px' id='table_" . $idstage . "'>";
    $c .= "<tr class='hidden-xs' style='border:0;background:#e3e3e3e3'>";
    $c .= "<th  style='border:0;background:#e3e3e3e3'>Identité</th>";
    $c .= "<th  style='border:0;background:#e3e3e3e3;text-align:center'>Inscription</th>";
    $c .= "<th  style='border:0;background:#e3e3e3e3;text-align:center'>Cas</th>";
    $c .= "<th  style='border:0;background:#e3e3e3e3;text-align:center'>Prix Index</th>";
    $c .= "<th  style='border:0;background:#e3e3e3e3;text-align:center'>Réception Paiement</th>";
    $c .= "<th  style='border:0;background:#e3e3e3e3;text-align:center'>Provenance</th>";
    $c .= "<th style='white-space:nowrap;border:0;background:#e3e3e3e3;text-align: center'>Dossier Stagiaire</th>";
    $c .= "<th  style='border:0;background:#e3e3e3e3;text-align:center'>Statut</th>";
    $c .= "<th width='180px;' style='border:0;background:#e3e3e3e3;text-align:center'>Actions</th>";
    $c .= "</tr>";

    if (count($stagiaires) == 0) {
        $c .= "
            <tr class='ligne' style='border-top:1px solid #ccc'>
		      <td style='border:0;background-color:white;text-align:center' colspan=9>Aucune inscription.</td>
            </tr>";
    }

    foreach ($stagiaires as $stagiaire) {

        $table = intval($stagiaire['table']);

        if (($stagiaire['supprime'] == 1) &&
            vide($stagiaire['numtrans']) &&
            ($table == 1)
        )
            continue;

        $id_stagiaire = $stagiaire['id'];

        $paiement = $stagiaire['paiement'] - $stagiaire['price_transfer'];

        $documents_telecharges = documents_telecharges($stagiaire['id_stage'], $stagiaire['date1'], $id_stagiaire);
        $dossier = "/home/prostage/www/stages/mois/" . date('Ym', strtotime($stagiaire['date1'])) . "/" . $stagiaire['id_stage'];
        $dossier_url = str_replace("/home/prostage/www", "https://www.prostagespermis.fr", $dossier);

        $c .= "<tr class='ligne' style='border-top:1px solid #ccc'>";

        $c .= "<td style='border:0;background-color:white'>";
        $c .= strtoupper($stagiaire['nom']) . " " . ucfirst($stagiaire['prenom']);
        /*$c .= "<p>";
		$c .= isset($stagiaire['mobile']) ? $stagiaire['mobile'] : "";
		$c .= isset($stagiaire['tel']) ? " ".$stagiaire['tel'] : "";
		$c .= "<br>";
		$c .= $stagiaire['email'];
		$c .= "</p>";*/
        $c .= "</td>";

        $c .= "<td style='text-align:center;border:0;background-color:white'>";
        $c .= date('d-m-Y', strtotime($stagiaire['date_inscription']));
        $c .= "</td>";

        $c .= "<td style='text-align:center;border:0;background-color:white'>";
        $c .= $stagiaire['cas'];
        $c .= "</td>";

        $c .= "<td style='text-align:center;border:0;background-color:white'>";
        $c .= $paiement;
        $c .= "</td>";

        $c .= "<td style='text-align:center;border:0;background-color:white'>";
        if ($stagiaire['provenance'] == 1)
            $c .= "Reçu";
        else {
            $c .= "<span class='receipt_container' id_stagiaire='$id_stagiaire' style='cursor:pointer'>";
            $c .= "<span class='receipt'>";
            $c .= intval($stagiaire['receipt']) ? "Reçu" : "En attente";
            $c .= "</span>";
            $c .= "</span>";
        }
        $c .= "</td>";

        $c .= "<td style='text-align:center;border:0;background-color:white'>";
        if ($stagiaire['provenance'] == 1) $provenance = "Prostagespermis";
        else if ($stagiaire['provenance'] == 2) $provenance = "Autre plateforme";
        else if ($stagiaire['provenance'] == 3) $provenance = "Réseau propre";
        else $provenance = "";
        $c .= $provenance;
        $c .= "</td>";

        /*
		$c .= "<td style='text-align:center'>";
		$c .= $stagiaire['commission']/100;;
		$c .= "</td>";
		*/

        $c .= "<td style='white-space:nowrap;text-align:center;border:0;background-color:white'>";
        $validation_status = $stagiaire['validations_stagiaire'] == '1|1|1|1' ? 'Complet' : 'Incomplet';
        $c .= "<span>" . $validation_status . "</span>";
        /*
        foreach ($documents_telecharges as $document_telecharge) {

            if (stristr($document_telecharge, "verso")) $type = "Permis<br>verso";
            else if (stristr($document_telecharge, "permis")) $type = "Permis<br>recto";
            else if (stristr($document_telecharge, "rii")) $type = "RII";
            else if (stristr($document_telecharge, "48n")) $type = "48N";
            else if (stristr($document_telecharge, "ordonnance_penale")) $type = "Ordonnance<br>penale";
            else if (stristr($document_telecharge, "suspension")) $type = "Suspension";
            else if (stristr($document_telecharge, "retention")) $type = "Retention";
            else if (stristr($document_telecharge, "perte")) $type = "Perte";
            else if (stristr($document_telecharge, "cni")) $type = "Carte<br>identite";
            else if (stristr($document_telecharge, "vol")) $type = "Declaration<br>vol";
            else if (stristr($document_telecharge, "demande_permis")) $type = "Justificatif<br>demande permis";
            else continue;

            $url = $dossier_url . "/" . $document_telecharge;

            $c .= "<div style='float:left;margin-right:25px'>
					<a target='_blank' href='$url' download>
					<i class='far fa-file-alt fa-2x'></i><br>$type</a></div>";
        }
        */
        $c .= "</td>";

        $c .= "<td style='text-align:center;border:0;background-color:white'>";
        $status_class = $stagiaire['supprime'] == 0 ? "inscrit" : "annule";
        $c .= "<span class='status $status_class'></span>";
        $c .= "</td>";

        $c .= "<td style='text-align:center;border:0;background-color:white; display: flex; justify-content: space-evenly'>";
        $c .= "<i class='fas fa-file-pdf fa-2x' style='float:left;color:#337ab7' id='downloadAttestation' title=\"Télécharger l'attestation du stagiaire pré-remplie\" type='attestation' format='pdf' id_stage=" . $idstage . " id_stagiaire='" . $id_stagiaire . "'></i>";
        $liste = array();
        if (is_dir($dossier)) {

            $matche = '#^' . $id_stagiaire . '_attestation_signee#ui';
            $f = new FilesystemIterator($dossier, FilesystemIterator::KEY_AS_FILENAME);
            $r = new RegexIterator($f, $matche, RegexIterator::MATCH, RegexIterator::USE_KEY);
            foreach ($r as $t) {
                array_push($liste, $t->getFilename());
            }
        }

        if (count($liste)) {
            $path_2 = str_replace("/home/prostage/www", "https://www.prostagespermis.fr", $dossier);
            $url = $path_2 . "/" . $liste[0];
            $c .= "<a target='blank' href='$url' download><i title='Attestation du stagiaire signée' class='fas fa-file-pdf fa-2x' style='color:orange;font-size:18px;float:left;margin-top:3px'></i></a>			
				<i style='color:red;font-size:18px;float:left;margin-top:3px' class='fas fa-times' title='Supprimer l’attestation stagiaire signée' onclick='deleteAttestationSignee(&apos;" . $liste[0] . "&apos;, &apos;" . $dossier . "&apos;, &apos;" . $idstage . "&apos;)'></i>";
        } else {

            $type = "attestation_signee";
            $form = "form_attestation_signee_" . $id_stagiaire;
            $name = "file_attestation_signee_" . $id_stagiaire;

            if ($table == 2)
                $c .= "<i class='fas fa-upload fa-2x disabled' style='color:orange;font-size:18px;float:left;margin-top:2px' title='Charger l’attestation du stagiaire signée. Vous ne pouvez pas effectuer cette action sur un candidat ne provenant pas de ProstagesPermis'></i>";
            else
                $c .= "<form style='float:left;margin-left:5px;' id='$form' method='post' enctype='multipart/form-data' action='https://www.prostagespermis.fr/ep/ajax_upload.php' onchange='upload(this, &apos;" . $type . "&apos;, &apos;" . $id_stagiaire . "&apos;, &apos;" . $dossier . "&apos;, &apos;" . $idstage . "&apos;)'><input type='file' name='file_attestation_signee' id='$name' style='display:none'/>
					<i class='fas fa-upload fa-2x' style='color:orange;font-size:18px;margin-top:2px' id='upload_link' onclick='$(&apos;#$name:hidden&apos;).trigger(&apos;click&apos;);' style='cursor:pointer' title='Charger l’attestation du stagiaire signée'></i>
					</form>";
        }


        $c .= "<i table='$table' title='Consulter ou modifier la fiche du stagiaire' id_stagiaire='$id_stagiaire' id_stage='$idstage' isOpen='0' id_member='$id_membre' class='fas fa-pen fa-2x' style='color:#01A31C;font-size:18px;float:left'></i>";

        $c .= "<i table='$table' title='Envoyer un message au stagiaire' id_stagiaire='$id_stagiaire' class='far fa-comment-alt fa-2x' style=';font-size:18px;float:left'></i>";

        //bouton annulation
        $disabled = $stagiaire['supprime'] ? "disabled" : "";
        if (strtotime($stagiaire['date1']) <= strtotime(date('Y-m-d'))) {
            $title = "Il est trop tard pour annuler ce candidat. Vous aviez jusqu’à la veille du stage minuit";
            $disabled = "disabled";
        } else if ($stagiaire['supprime']) {
            $title = "Candidat déjà annulé";
            $disabled = "disabled";
        } else {
            $title = "Annuler l’inscription du candidat";
            $disabled = "";
        }
        $c .= "<i table='$table' title='$title' id_stagiaire='$id_stagiaire' id_stage='$idstage' class='fas fa-times-circle fa-times-circle2 fa-2x $disabled' style='color:red;font-size:18px;float:left'></i>";

        $c .= "</td>";
        $c .= "</tr>";

        $c .= '<tr style="border:0px;display:none" id="tr_student_' . $id_stagiaire . '" class="ligne student_open"><td colspan="9" style="border:0px;"><div class="div_student" id="div_student_' . $id_stagiaire . '"></div></td></tr>';
    }

    //     padding: 20px;
    //    background-color: #EFF2F9;

    $c .= "</table>";

    $c .= '
        <div style="margin: 20px 0 0 0;padding: 20px; text-align: center"><button class="btn btn-grey close_stagiaires" style="width: 150px;
        background: linear-gradient(to bottom, #aba9a9 0%,#858585 100%);
        to bottom, #e2e2e2 0%,#c6c6c6 100%): round;
        color: #FFF;
        font-size: 14px;">Fermer</button>
        </div>
    ';

    echo $c;
}

function stagiaires_inscrits_par_stage($table, $id_membre, $first_date, $end_date, $departement)
{

    $stagiaires = array();

    $table = intval($table) == 2 ? "stagiaire_externe" : "stagiaire";

    if ($departement == 0)
        $departement_filter = "site.departement = site.departement";
    else
        $departement_filter = "site.departement = '$departement'";

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $sql = "SELECT 
				stage.id,
				SUM($table.supprime = 0) AS inscrits 
			FROM
				$table, stage, site
			WHERE
				stage.id_site = site.id AND
				$table.id_stage = stage.id AND
				stage.id_membre = '$id_membre' AND 
				stage.date1 >= '$first_date' AND
				stage.date1 <= '$end_date' AND " .
        $departement_filter . " 
			GROUP BY
				stage.id
			ORDER BY 
				stage.id ASC";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);

    while ($row = mysql_fetch_assoc($rs)) {
        $stagiaires[] = $row;
    }

    return $stagiaires;
}

function suivi_stage($id_stage, $text)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "INSERT INTO suivi_stage (id_stage, commentaire) VALUES (\"$id_stage\", \"$text\")";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());

    $sql = "SELECT 
				formateur.email FROM formateur, stage 
			WHERE 
				((stage.id = '$id_stage' and stage.id_bafm = formateur.id) OR (stage.id = '$id_stage' and stage.id_psy = formateur.id))";
    $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
    while ($row = mysql_fetch_assoc($rs)) {
        $to = $row['email'];
        $objet = "Vous avez un nouveau commentaire de suivi de stage";

        $c = "<div style='max-width:750px'>";
        $c .= "<h2 style=\"$h1\">Vous avez reçu un nouveau commentaire de suivi de stage !</h2>";
        $c .= "Bonjour<br><br>

		Nous venons de vous adresser un nouveau commentaire suite à l'animation de votre stage.<br><br>
		Rendez-vous sur votre espace simplistage rubrique <strong>Suivi de stage</strong> pour plus de détails.";

        $c .= "<br><br><br>
		A très bientôt,
		<br><br>

		<span style='font-style:italic;color:grey'>
		<hr>
		Marie Monti,<br>
		Service Partenariat IDStages 
		</span>

		</div>";

        phpmailer_v4($to, utf8_decode($objet), utf8_decode($c));
    }

    mysql_close($stageconnect);
}

function phpmailer_v4($to, $objet, $message, $filename = NULL, $path = NULL, $filename2 = NULL, $path2 = NULL)
{

    require_once('class.phpmailer.php'); // Your full path of phpmailer_v3.php file

    if (stristr($to, ','))
        $emails = array_map('trim', explode(",", $to));
    else if (stristr($to, ';'))
        $emails = array_map('trim', explode(";", $to));
    else
        $emails = array_map('trim', explode(",", $to));

    $email = new PHPMailer();
    $email->From = 'idstages@gmail.com'; //From email address
    $email->FromName = 'IDStages'; //your name
    $email->Subject = $objet; //Message
    $email->Body = $message; //Body message
    $email->IsHTML(true);
    $email->AddAddress($emails[0]); //To email address

    foreach ($emails as $key => $val) {
        if ($key == 0) continue;
        $email->AddAddress($val);
    }

    $email->AddCC('idstages@gmail.com');

    if (!is_null($filename)) {
        $attach_file = $path . $filename;
        $email->AddAttachment($attach_file, $filename);
    }

    if (!is_null($filename2)) {
        $attach_file = $path2 . $filename2;
        $email->AddAttachment($attach_file, $filename2);
    }

    return $email->Send();
}

function documents_telecharges($id_stage, $date_stage, $id_stagiaire)
{

    $liste = array();
    $mois = date("Ym", strtotime($date_stage));
    $path = "/home/prostage/www/stages/mois/" . $mois . "/" . $id_stage;

    if (is_dir($path)) {

        $matche = '#^' . $id_stagiaire . '#ui';
        $f = new FilesystemIterator($path, FilesystemIterator::KEY_AS_FILENAME);
        $r = new RegexIterator($f, $matche, RegexIterator::MATCH, RegexIterator::USE_KEY);

        foreach ($r as $t) {
            array_push($liste, $t->getFilename());
        }
    }
    return $liste;
}

function send_notification($type_interlocuteur, $id_interlocuteur, $type_destinataire, $notifie, $message)
{
    require_once("/home/prostage/common_bootstrap2/notifications.php");
    $id_notification = notification($type_interlocuteur, $id_interlocuteur, $type_destinataire, $notifie, $message);
    return $id_notification;
}

function update_notification_centre($id_notification, $membre)
{

    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $sql = "UPDATE notifications SET id_centre = '$membre' WHERE id = '$id_notification'";
    mysql_query($sql, $stageconnect) or die(mysql_error());
    mysql_close($stageconnect);
}

function vide($val)
{

    if (is_null($val) || empty($val) || $val == '' || strlen($val) == 0)
        return 1;
    else
        return 0;
}

function get_starred($str)
{
    $len = strlen($str);

    return substr($str, 0, 1) . str_repeat('*', $len - 2) . substr($str, $len - 1, 1);
}

function getCoordinates($address)
{

    $prepAddr = str_replace(' ', '+', $address);
    $url = 'https://maps.google.com/maps/api/geocode/json?address=' . $prepAddr . '&sensor=false';
    echo $url;
    $geocode = file_get_contents($url);
    $output = json_decode($geocode);
    $latitude = $output->results[0]->geometry->location->lat;
    $longitude = $output->results[0]->geometry->location->lng;

    echo "latitude - " . $latitude;
    echo "longitude - " . $longitude;
}

function getGpsCoordinates($code_postal, $ville)
{

    $urlWebServiceGoogle = 'http://maps.google.com/maps/api/geocode/json?address=%s&sensor=false&language=fr';
    $postalAddress = $code_postal . " " . $ville;

    $url = vsprintf($urlWebServiceGoogle, urlencode($postalAddress));
    $response = json_decode(file_get_contents($url));

    if (empty($response->status) || $response->status != "OK") {
        echo "Error Response API status: " . $response->status . "<br>";
        $latitude = 0;
        $longitude = 0;
    } else {
        $latitude = $response->results[0]->geometry->location->lat;
        $longitude = $response->results[0]->geometry->location->lng;
    }

    echo "latitude " . $latitude;

    return array($latitude, $longitude);
}

function tri_stagiaires($a, $b)
{
    return (strtotime($a['date_inscription']) > strtotime($b['date_inscription'])) ? -1 : 1;
}

#102
function checkIfUserHasAcceptedCGP($id_membre)
{
    include("/home/prostage/connections/stageconnect.php");
    require_once "/home/prostage/www/params.php";
    require_once APP . 'member/services/cgp/ParseCgpIdForMember.php';
    require_once ROOT . '/debug.php';

    mysql_select_db($database_stageconnect, $stageconnect);
    $query = "SELECT * FROM membre WHERE id=" . $id_membre;
    $res = mysql_query($query, $stageconnect);

    // 946 à 1005

    if ($row = mysql_fetch_assoc($res)) {
        $nouveau_modele_commission = $row["nouveau_modele_commission"];

        $id_cgp = ParseCgpIdForMember::getCGPId($id_membre);

        $query = "SELECT updated_article_number FROM cgp WHERE cgp_type_partenaire = 'Ancien Espace Partenaire 1 (CENTRE)' AND id=" . $id_cgp;
        $res = mysql_query($query, $stageconnect);
        $hasUpdates = mysql_fetch_assoc($res);

        $query = "SELECT cgp FROM cgp WHERE cgp_type_partenaire = 'Ancien Espace Partenaire 1 (CENTRE)' AND id=" . $id_cgp;
        $res = mysql_query($query, $stageconnect);
        $cgp = mysql_fetch_assoc($res);

        $query = "SELECT id_cgp,has_accepted_cgp,article_number_accepted FROM cgp_partenaire_status where id_membre = " . $id_membre;
        $res = mysql_query($query, $stageconnect);
        $hasAccepted = mysql_fetch_assoc($res);


        $id_cgp_bdd = $hasAccepted['id_cgp'];
        mysql_close($stageconnect);
        if ($id_cgp_bdd > 0) {
            if (($id_membre >= 959 && ($id_cgp_bdd != 3 || $hasUpdates['updated_article_number'] != $hasAccepted['article_number_accepted'])) || ($id_membre < 959 && $nouveau_modele_commission == 1 && ($id_cgp_bdd != 3 || $hasUpdates['updated_article_number'] != $hasAccepted['article_number_accepted']))) {
                if ($id_cgp_bdd != 3)
                    echo json_encode(['accepted' => false, 'message' => '', 'cgp' => $cgp]);
                else {
                    if ($hasUpdates['updated_article_number'] != $hasAccepted['article_number_accepted'])
                        echo json_encode(['accepted' => false, 'message' => $hasUpdates, 'cgp' => $cgp]);
                    else
                        echo json_encode(['accepted' => false, 'message' => '', 'cgp' => $cgp]);
                }
                return;
            } else {
                if ($hasUpdates['updated_article_number'] !== NULL && $hasAccepted['has_connected_first_time'] == '1' && $hasAccepted['has_accepted_cgp'] == '0') {
                    echo json_encode(['accepted' => false, 'message' => $hasUpdates, 'cgp' => $cgp]);
                    return;
                } else {
                    if ($hasAccepted['has_accepted_cgp'] == '1') {
                        echo json_encode(['accepted' => true]);
                        return;
                    }
                }
            }
            echo json_encode(['cgp' => $cgp]);
        } else {
            echo json_encode(['accepted' => false, 'message' => '', 'cgp' => $cgp]);
            return;
        }
    }
}

/**
 * @param $id_membre
 * @param $arrOldMemberDispenseOnNewCgp
 * @return mixed
 */
function getIdCgp($id_membre, $arrOldMemberDispenseOnNewCgp) {}

#102
function validateCGP($id_membre)
{
    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);
    $query = "SELECT * FROM membre WHERE id=" . $id_membre;
    $res = mysql_query($query, $stageconnect);
    $row = mysql_fetch_assoc($res);
    if ($id_membre >= 959 || $row["nouveau_modele_commission"] == 1)
        $id_cgp = 3;
    else
        $id_cgp = 1;

    $query = "SELECT has_accepted_cgp FROM cgp_partenaire_status where id_membre = " . $id_membre;
    $res = mysql_query($query, $stageconnect);
    $total = mysql_num_rows($res);

    $query = "SELECT updated_article_number FROM cgp WHERE cgp_type_partenaire = 'Ancien Espace Partenaire 1 (CENTRE)' AND id=" . $id_cgp;
    $res = mysql_query($query, $stageconnect);
    $cgp = mysql_fetch_assoc($res);

    if ($total == 0) {
        $query = "INSERT INTO cgp_partenaire_status(id_membre,id_cgp,has_accepted_cgp,has_connected_first_time,article_number_accepted) values(" . $id_membre . "," . $id_cgp . ",1,1," . $cgp['updated_article_number'] . ")";
    } else {
        $query = "UPDATE cgp_partenaire_status 
                    SET has_accepted_cgp = 1,
                        id_cgp = " . $id_cgp . ",
                        has_connected_first_time = 1,
                        article_number_accepted = " . $cgp['updated_article_number'] . "
                    WHERE id_membre =" . $id_membre;
    }

    $res = mysql_query($query, $stageconnect);
    $message = ['message' => 'Une erreur s\'est produite'];
    if ($res) {
        $message = ['message' => 'Les conditions ont &eacute;t&eacute; valid&eacute;es.', 'status' => $res];
    }

    echo json_encode($message);
}

#102
function updateCGP()
{
    include("/home/prostage/connections/stageconnect.php");
    mysql_select_db($database_stageconnect, $stageconnect);

    $id_articles_update = $_POST['data']['articles_ids'];
    $message = ['message' => 'Une erreur s\'est produite'];
    $query = "UPDATE cgp SET updated_article_number = '$id_articles_update' WHERE cgp_type_partenaire = 'Ancien Espace Partenaire 1 (CENTRE)'";

    $res = mysql_query($query, $stageconnect);

    if ($res) {
        $query = "UPDATE cgp_partenaire_status SET has_accepted_cgp = false WHERE id_cgp = 1";

        $res = mysql_query($query, $stageconnect);
        if ($res) {
            $message = ['message' => 'Les CGP ont &eacute;t&eacute; mise &agrave; jour', 'status' => $res];
        }
    }

    echo json_encode($message);
}

function StatutDemandeTexteGet($statutCode)
{
    switch ($statutCode) {
        case 'BROUILLON':
            return 'Brouillon';
            break;
        case 'A_ENVOYER_GDD':
            return 'Envoyée';
            break;
        case 'ENVOYEE_GDD':
            return 'En cours d\'instruction';
            break;
        case 'RECUE_GDD':
            return 'En cours d\'instruction';
            break;
        case 'A_COMPLETER':
            return 'A compléter';
            break;
        case 'EN_PRODUCTION':
            return 'Validé par l\'administration';
            break;
        case 'REJETEE':
            return 'Rejet par l\'instruction';
            break;
        case 'SUPPRIMEE':
            return 'Supprimé';
            break;
        case '':
            return 'A traiter';
            break;
        default:
            return "";
            break;
    }
}

function parseAntsErrorDirectory($ants_erreurs)
{
    if ($ants_erreurs == '') {
        return '';
    }
    $str = str_replace('^[A-Za-z \'-àáâãäåæçèéêëìíîïñòóôõöùúûüýÿœÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝŸŒ]+$', '', $ants_erreurs);
    $str = str_replace('^[A-Za-z \'()-àáâãäåæçèéêëìíîïñòóôõöùúûüýÿœÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝŸŒ0-9]+$', '', $str);
    $str = str_replace('^([a-zA-Z0-9]{1,15}|[0-9]{2}|[0-9]{2})|[0-9]{12}$', '', $str);
    $str = str_replace('^[09]?[0-9]{2}|0?2[AB]$', '', $str);
    $str = str_replace('^[1-9][0-9]{3}-[0-1][0-9]-[0-3][0-9]$', '', $str);
    $str = str_replace('"', '', $str);
    if (strpos($str, 'doit respecter') != false) {
        $str = str_replace('doit respecter', '', $str);
        $arrMessage = explode(':', $str);
        return $arrMessage[0] . ' : la taille doit être comprise entre 1 et 34';
    }
    return $str;
}

function StatutDemandeCode($id_statut)
{
    switch ($id_statut) {
        case 3: //A compléter: A_COMPLETER
            return "('A_COMPLETER', 'RECUE_ERREUR_GDD')";
            break;
        case 4: //En cours de traitement:
            return "('BROUILLON', 'A_ENVOYER_GDD','ENVOYEE_GDD','RECUE_GDD', 'RECUE_ERREUR_GDD')";
            break;
        case 5: //Rejeté
            return "('REJETEE')";
            break;
        case 6: //Accepté:
            return "('EN_PRODUCTION')";
            break;
    }
    //A_VALIDER_USAGER, REFUS_USAGER,  SUPPRIMEE, A_ENVOYER_CTN, NNULEE, ATTENTE_FPS, CONTROLEE_KO_CTN, CONTROLEE_OK_CTN, ENVOYEE_CTN, EXPIREE, RECUE_CTN, A_ENVOYER, CONTROLEE_KO, CONTROLEE_OK, ENVOYEE, RECUE, RECUE_ERREUR
}
