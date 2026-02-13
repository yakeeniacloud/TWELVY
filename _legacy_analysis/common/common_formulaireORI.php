<?php
//echo "<br><br><b>SITE EN COURS DE MAINTENANCE. MERCI DE PATIENTER JUSQU'AU 26/04/2011</b>";
//exit;
?>

<STYLE type="text/css">
p {
  font-size:13px;
  text-align:justify;
  }

.texte_contact {
	font-size:12px;
	color:#000066;
	margin-right:0px;
}

.input_contact {
	border:1px solid #5e7e88;
	padding:1px;
	font-size:14px;
	width:300px;
	background-color: #D8D8D8;
}

.Style18 {
	color: #FF0000;
}

fieldset
{
padding:15px;
margin-bottom:5px;
}
</STYLE>



<?php
function inscription($id_stage, $id_membre)
{

	//cas des stages internes
	if ($id_membre == 38 || $id_membre == 175)
	{
		echo "<br><br><br><br><br><br>";
		echo "<div align=\"center\" style=\"font-size:18px\">";
		echo "<font color='red'><b>Désolé, ce stage n'est plus à la vente car la session est complète.</b></font>";
		echo "<br><br>";
		echo "<b>D'autres sessions sont disponibles dans votre département.
			Retournez à l'accueil pour effectuer une nouvelle recherche.<br><br> <a href=\"./\">=> RETOUR ACCUEIL</a></b>";
		echo "</div>";
		exit;
	}

	require ("../connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);


	$query_stage =
		"SELECT stage.*, site.nom, site.ville, site.adresse, site.code_postal, membre.mode, membre.types_paiement FROM
			stage, site, membre WHERE
			stage.id = $id_stage AND
			stage.id_site = site.id AND
			stage.id_membre = $id_membre AND
			membre.id = $id_membre";

	$rsStage = mysql_query($query_stage, $stageconnect) or die(mysql_error());
	$row_stage = mysql_fetch_assoc($rsStage);
	$totalRows_stage = mysql_num_rows($rsStage);

	mysql_close($stageconnect);

	echo "<form action='fiche-preinscription.php' name='Inscription' method='post' class='formulaire'>";

	echo "<input name='stageId_externe' id='stageId_externe' type='hidden' value=".$row_stage['id_externe'].">";
	echo "<input name='stageId' id='stageId' type='hidden' value=".$row_stage['id'].">";
	echo "<input name='membreId' id='membreId' type='hidden' value=".$id_membre.">";
	echo "<input name='nom_site' id='nom_site' type='hidden' value=".$row_stage['nom'].">";
	echo "<input name='prix' id='prix' type='hidden' value=".$row_stage['prix'].">";
	echo "<input name='mode' type='hidden' id='mode' value=".$row_stage['mode'].">";
	echo "<input name='date1' id='date1' type='hidden' value=".$row_stage['date1'].">";
	echo "<input name='date2' id='date2' type='hidden' value=".$row_stage['date2'].">";
	echo "<input name='adresse_site' id='adresse_site' type='hidden' value=".$row_stage['adresse'].">";
	echo "<input name='ville_site' id='ville_site' type='hidden' value=".$row_stage['ville'].">";
	echo "<input name='cp_site' id='cp_site' type='hidden' value=".$row_stage['code_postal'].">";
	echo "<input name='debut_am' id='debut_am' type='hidden' value=".$row_stage['debut_am'].">";
	echo "<input name='fin_am' id='fin_am' type='hidden' value=".$row_stage['fin_am'].">";
	echo "<input name='debut_pm' id='debut_pm' type='hidden' value=".$row_stage['debut_pm'].">";
	echo "<input name='fin_pm' id='fin_pm' type='hidden' value=".$row_stage['fin_pm'].">";

	echo "<fieldset><legend><strong>Stage sélectionné </strong></legend>";
	echo "<table>";

	echo "<tr>";
	echo "<td><strong>Dates:</strong></td>";
	echo "<td><em>".MySQLDateToExplicitDate($row_stage['date1'])." et ".MySQLDateToExplicitDate($row_stage['date2'])."</em></td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>-------------------</td>";
	echo "<td></td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td><strong>Horaires:</strong></td>";
	echo "<td><em>".$row_stage['debut_am']."-".$row_stage['fin_am']." et ".$row_stage['debut_pm']."-".$row_stage['fin_pm']."</em></td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>-------------------</td>";
	echo "<td></td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td><strong>Adresse:</strong></td>";
	echo "<td><em>".supprime_nom_centre($row_stage['nom'])."<br>".$row_stage['adresse']."<br>".sprintf('%05d',$row_stage['code_postal'])." ".$row_stage['ville']."</em></td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>-------------------</td>";
	echo "<td></td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td><strong>Prix:</strong></td>";
	echo "<td><em>".$row_stage['prix']." euros</em></td>";
	echo "</tr>";

	echo "</table>";
	echo "</fieldset>";

	//FICHE DE RENSEIGNEMENTS
	//-----------------------
	echo "<fieldset><legend><strong>Fiche de pré-inscription / Renseignements </strong></legend>";

	echo "<table>";

	echo "<tr><td colspan='2'>&nbsp;</td></tr>";

	echo "<tr><td colspan='2'><em><strong><u>Attention</u></strong>, pour pouvoir vous inscrire en ligne il faut impérativement que vous ayez une <strong><u>adresse email valide</u></strong>. Si ce
n'est pas le cas, appelez nous pour réserver votre place par <strong><u>téléphone</u></strong>.</em></td></tr>";

	echo "<tr><td colspan='2'>&nbsp;</td></tr>";

	echo "<tr><td width='40%'><p class='texte_contact'>Civilité:<span class='Style18'>*</span></p></td>";
	echo "<td><select name='civilite'>";
	echo "<option value='Mr' selected='selected'>Mr</option>";
	echo "<option value='Mme'>Mme</option>";
	echo "<option value='Mlle'>Mlle</option></select>";
	echo "</td></tr>";

	echo "<tr><td><p class='texte_contact'>Nom<span class='Style18'>*</span></p></td>";
	echo "<td><input name='nom' type='text' value='' size='13'  class='input_contact'/></td></tr>";

	echo "<tr><td><p class='texte_contact'>Prenom<span class='Style18'>*</span></p></td>";
	echo "<td><input name='prenom' type='text' value='' size='13' class='input_contact'/></td></tr>";

	echo "<tr><td><p class='texte_contact'>Nom de jeune fille</p></td>";
	echo "<td><input name='jeune_fille' type='text' value='' size='13'  class='input_contact'/></td></tr>";

	echo "<tr><td><p class='texte_contact'>Adresse<span class='Style18'>*</span></p></td>";
	echo "<td><textarea name='adresse' rows='3' maxlength='200' class='input_contact'></textarea></td></tr>";

	echo "<tr><td><p class='texte_contact'>Code postal<span class='Style18'>*</span></p></td>";
	echo "<td><input name='code_postal' type='text' maxlength='5' class='input_contact'/></td></tr>";

	echo "<tr><td><p class='texte_contact'>Ville<span class='Style18'>*</span></p></td>";
	echo "<td><input name='ville' type='text' class='input_contact'/></td></tr>";

	echo "<tr><td><p class='texte_contact'>Date naissance<span class='Style18'>*</span></p></td>";
	echo "<td><select name='jour_naissance'>";
	for ($i=1; $i<=31; $i++)
	{
		echo "<option value=$i>$i</option>";
	}
	echo "</select>";

	echo "<select name='mois_naissance'>";
	echo "<option value='1' selected>Jan</option>";
	echo "<option value='2'>Fev</option>";
	echo "<option value='3'>Mars</option>";
	echo "<option value='4'>Avr</option>";
	echo "<option value='5'>Mai</option>";
	echo "<option value='6'>Juin</option>";
	echo "<option value='7'>Juil</option>";
	echo "<option value='8'>Aout</option>";
	echo "<option value='9'>Sept</option>";
	echo "<option value='10'>Oct</option>";
	echo "<option value='11'>Nov</option>";
	echo "<option value='12'>Dec</option>";
	echo "</select>";

	echo "<select name='annee_naissance'>";
	for ($i=1920; $i<2005; $i++)
	{
		echo "<option value=$i";
		if ($i == '1980')
		{
			echo " selected ";
		}
		echo ">";echo $i;
		echo "</option>";
	}
	echo "</select>";
	echo "</td></tr>";

	echo "<tr><td><p class='texte_contact'>Lieu naissance<span class='Style18'>*</span></p></td>";
	echo "<td><input name='lieu_naissance' type='text' value='' size='13' class='input_contact'/></td></tr>";

	echo "<tr><td><p class='texte_contact'>Email<span class='Style18'>*</span></p></td>";
	echo "<td><input name='email' type='text' value='' size='13' class='input_contact'/></td></tr>";

	echo "<input name='email2' type='text' size='30' style='display:none'>";

	echo "<tr><td><p class='texte_contact'>Confirmer Email<span class='Style18'>*</span></p></td>";
	echo "<td><input name='confirm_email' type='text' value='' size='13' class='input_contact'/></td></tr>";

	echo "<tr><td><p class='texte_contact'>Téléphone 1<span class='Style18'>*</span></p></td>";
	echo "<td><input name='tel1' type='text' value='' size='13' maxlength='10' class='input_contact'/></td></tr>";

	echo "<tr><td><p class='texte_contact'>Téléphone 2</p></td>";
	echo "<td><input name='tel2' type='text' value='' size='13' maxlength='10' class='input_contact'/></td></tr>";

	echo "<tr><td><p class='texte_contact'>Fax</p></td>";
	echo "<td><input name='fax' type='text' value='' size='13' class='input_contact'/></td></tr>";

	echo "<tr><td><p class='texte_contact'>N° Permis</p></td>";
	echo "<td><input name='num_permis' type='text' value='' size='13' class='input_contact'/></td></tr>";

	echo "<tr><td><p class='texte_contact'>Date permis</p></td>";
	echo "<td><select name='jour_permis'>";
	echo "<option value='--' selected>--</option>";
    for ($i=1; $i<=31; $i++)
	{
		echo "<option value=$i>$i</option>";
	}
	echo "</select>";

	echo "<select name='mois_permis'>";
	echo "<option value='--' selected>--</option>";
    echo "<option value='1' >Jan</option>";
	echo "<option value='2' >Fev</option>";
	echo "<option value='3' >Mars</option>";
	echo "<option value='4' >Avr</option>";
	echo "<option value='5' >Mai</option>";
	echo "<option value='6' >Juin</option>";
	echo "<option value='7' >Juil</option>";
	echo "<option value='8' >Aout</option>";
	echo "<option value='9' >Sept</option>";
	echo "<option value='10' >Oct</option>";
	echo "<option value='11' >Nov</option>";
	echo "<option value='12' >Dec</option>";
	echo "</select>";

	echo "<select name='annee_permis'>";
	echo "<option value='--' selected>--</option>";
	$annee_courante = date('Y');
	for ($a=1920; $a<=$annee_courante; $a++)
	{
		echo "<option value=$a ";
		echo ">";
		echo $a;
		echo "</option>";
	}
	echo "</select>";
	echo "</td></tr>";

	echo "<tr><td><p class='texte_contact'>Lieu permis</p></td>";
	echo "<td><input name='lieu_permis' type='text' value='' size='13' class='input_contact'/></td></tr>";


	if ($id_membre == 64 || $id_membre == 38) //actiroute ou stagespermis
	{
		echo "<tr>";
		echo "<td><p class='texte_contact'>Type de stage: <span class='Style18'>*</span></p></td>";
		echo "<td><label><input name='cas' type='radio' value='1'
			checked='checked' onclick='change()'/>Cas1: Récupération volontaire de 4 points</label>";
		echo "<br>";
		echo "<label><input type='radio' name='cas' value='2'
			onclick='change()'/>Cas2: Stage en période probatoire (joindre lettre 48N)</label>";
		echo "<br>";
		echo "<label><input type='radio' name='cas' value='3'
			onclick='change()'/>Cas3: Alternative aux poursuites pénales</label>";
		echo "<br>";
		echo "<label><input type='radio' name='cas' value='4' onclick='change()'/>Cas4: Peine complémentaire</label></td></tr>";
	}
	else
	{
		echo "<tr>";
		echo "<td><p class='texte_contact'>Type de stage: <span class='Style18'>*</span></p></td>";
		echo "<td><label><input name='cas' type='radio' value='1'
			checked='checked'/>Cas1: Récupération volontaire de 4 points</label>";
		echo "<br>";
		echo "<label><input type='radio' name='cas' value='2'/>Cas2: Stage en période probatoire (joindre lettre 48N)</label>";
		echo "<br>";
		echo "<label><input type='radio' name='cas' value='3'/>Cas3: Alternative aux poursuites pénales</label>";
		echo "<br>";
		echo "<label><input type='radio' name='cas' value='4'/>Cas4: Peine complémentaire</label></td></tr>";
	}

	echo "</table>";
	echo "<table>";

	echo "<tr id='jeunes_conducteurs1' style='display:none'>";
		echo "<td width='30%'><p class='texte_contact'>Infraction en<br>période probatoire</p></td>";
		echo "<td>Date: <select name='jour_infraction'>";
		for ($i=1; $i<=31; $i++)
		{
			echo "<option value=$i>$i</option>";
		}
		echo "</select>";

		echo "<select name='mois_infraction'>";
		echo "<option value='Janvier' selected>Jan</option>";
		echo "<option value='Fevrier' selected>Fev</option>";
		echo "<option value='Mars' selected>Mars</option>";
		echo "<option value='Avril' selected>Avr</option>";
		echo "<option value='Mai' selected>Mai</option>";
		echo "<option value='Juin' selected>Juin</option>";
		echo "<option value='Juillet' selected>Juil</option>";
		echo "<option value='Aout' selected>Aout</option>";
		echo "<option value='Septembre' selected>Sept</option>";
		echo "<option value='Octobre' selected>Oct</option>";
		echo "<option value='Novembre' selected>Nov</option>";
		echo "<option value='Decembre' selected>Dec</option>";
		echo "</select>";

		echo "<select name='annee_infraction'>";
		for ($i=1920; $i<=$annee_courante; $i++)
		{
			if ($i == '2010'){
				echo "<option value=$i selected='selected'>$i</option>";}
			else{
				echo "<option value=$i>$i</option>";
			}
    	}
		echo "</select>";

		echo " Heure: ";
		echo "<input name='heure_infraction' type='text' value='' size='2' MAXLENGTH='2'/> :
			<input name='minutes_infraction' type='text' value=''  size='2' MAXLENGTH='2'/>";
		echo "<br>";
		echo "Lieu: ";
		echo "<input name='lieu_infraction' type='text' value='' size='20' MAXLENGTH='20'/>";
	echo "</td></tr>";

	echo "<tr id='jeunes_conducteurs2' style='display:none'>";
		echo "<td><p class='texte_contact'>Type d'infraction:</p></td>";
		echo "<td><select id='motif_infraction' name='motif_infraction'>";
		echo "<option value=''></option>";
		echo "<option value='Alcool'>Alcool - 6 points </option>";
		echo "<option value='Blessures Involontaires'>Blessures Involontaires - 0 points </option>";
		echo "<option value='Casque non attache'>Casque non attaché - 3 points </option>";
		echo "<option value='Ceinture'>Ceinture - 3 points </option>";
		echo "<option value='Circulation à gauche sur chaussee à double sens'>Circulation à gauche sur chaussée à double sens - 3 points </option>";
		echo "<option value='Circulation en contre sens'>Circulation en contre sens - 3 points </option>";
		echo "<option value='Circulation sur bande d'arret d'urgence'>Circulation sur bande d'arrêt d'urgence - 3 points </option>";
		echo "<option value='Circule sans permis de conduire'>Circule sans permis de conduire - 0 points </option>";
		echo "<option value='Clignotant'>Clignotant - 3 points </option>";
		echo "<option value='Délit de fuite'>Délit de fuite - 6 points </option>";
		echo "<option value='Dépassement dangereux'>Dépassement dangereux - 3 points </option>";
		echo "<option value='Feu'>Feu - 4 points </option>";
		echo "<option value='Feux éteints'>Feux éteints - 4 points </option>";
		echo "<option value='Franchissement ligne continue'>Franchissement ligne continue - 3 points </option>";
		echo "<option value='Homicide involontaire'>Homicide involontaire  - 6 points </option>";
		echo "<option value='Ligne'>Ligne - 0 points </option>";
		echo "<option value='Non port des lunettes'>Non port des lunettes - 3 points </option>";
		echo "<option value='Non respect des distances de sécurite'>Non respect des distances de sécurité - 3 points </option>";
		echo "<option value='Permis Non Proroge'>Permis Non Prorogé - 3 points </option>";
		echo "<option value='Refus de priorite'>Refus de priorité - 4 points </option>";
		echo "<option value='Sens Interdit'>Sens Interdit - 4 points </option>";
		echo "<option value='Stationnement dangereux'>Stationnement dangereux - 3 points </option>";
		echo "<option value='Stop'>Stop - 4 points </option>";
		echo "<option value='Stupefiant'>Stupéfiant - 6 points </option>";
		echo "<option value='Telephone'>Téléphone - 2 points </option>";
		echo "<option value='Vehicule non assure'>vehicule non assuré - 0 points </option>";
		echo "<option value='Vehicule sans certificat d'immatriculation'>Vehicule sans certificat d'immatriculation - 0 points </option>";
		echo "<option value='Visite médicale non effectuee'>visite médicale non effectuée - 3 points </option>";
		echo "<option value='Vitesse - 2 points'>Vitesse - 2 points </option>";
		echo "<option value='Vitesse - 1 point'>Vitesse - 1 points </option>";
		echo "<option value='Vitesse - 4 points'>Vitesse - 4 points </option>";
		echo "<option value='Vitesse - 3 points'>Vitesse - 3 points </option>";
		echo "<option value='Vitesse excessive - 6 points'>Vitesse excessive - 6 points </option>";
		echo "</select>";
 	echo "</td></tr>";


	echo "<tr id='jeunes_conducteurs3' style='display:none'>";
		echo "<td><p class='texte_contact'>Date réception lettre 48N:</p></td>";
		echo "<td><select name='jour_48'>";
		for ($i=1; $i<=31; $i++)
		{
			echo "<option value=$i>$i</option>";
		}
		echo "</select>";

		echo "<select name='mois_48'>";
		echo "<option value='Janvier' selected>Jan</option>";
		echo "<option value='Fevrier' selected>Fev</option>";
		echo "<option value='Mars' selected>Mars</option>";
		echo "<option value='Avril' selected>Avr</option>";
		echo "<option value='Mai' selected>Mai</option>";
		echo "<option value='Juin' selected>Juin</option>";
		echo "<option value='Juillet' selected>Juil</option>";
		echo "<option value='Aout' selected>Aout</option>";
		echo "<option value='Septembre' selected>Sept</option>";
		echo "<option value='Octobre' selected>Oct</option>";
		echo "<option value='Novembre' selected>Nov</option>";
		echo "<option value='Decembre' selected>Dec</option>";
		echo "</select>";

		echo "<select name='annee_48'>";
		for ($i=1920; $i<=$annee_courante; $i++)
		{
			if ($i == '2010')
			{
				echo "<option value=$i selected='selected'>$i</option>";
			}
			else
			{
				echo "<option value=$i>$i</option>";
			}
    	}
		echo "</select>";

	echo "</td></tr>";

	echo "<input name='jeunes_conducteurs_ref' type='hidden' id='jeunes_conducteurs_ref' value=''/>";


	$temps = time();
	echo "<input type='hidden' name='verifTemps' value='$temps'/>";

	//conditions generales securoute
	//------------------------------
	if ($id_membre == 44)
	{
		echo "<tr>";
		echo "<td colspan='2' align='justify'><input type='checkbox' name='conditions' value='0'> J'accepte les <a href='javascript:popup()'>conditions générales de vente.</a></td>";
		echo "</tr>";
	}



	//type de paiement
	//----------------
	$pay = explode(",", $row_stage['types_paiement']);

	if ($row_stage['types_paiement'] != "" && $row_stage['types_paiement']!= NULL && $pay[0] == "on")
	{
		echo "<tr>";
		echo "<td colspan='2' align='justify'><em>Nous vous conseillons fortement de régler votre stage par carte bleue afin
de garantir dès aujourd'hui votre place (les sessions étant rapidement complètes). Le paiement en ligne est <b><u>entièrement
sécurisé via PAYBOX</u></b>.</em></td><br>";
		echo "</tr>";

		echo "<tr>";
		echo "<td>&nbsp;</td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td><img src='images/cb.png' alt='paiement securisé par CB'/></td>";
		echo "<td><img src='images/cheque.png' alt='paiement par cheque'/></td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td><input name='radio_type_paiement' type='radio' value='1' checked='checked'/>Paiement par CB</td>";
		echo "<td><input name='radio_type_paiement' type='radio' value='0' />Paiement par chèque</td>";
		echo "</tr>";
	}

	echo "<tr><td><br>";
	if ($id_membre == 44) //verification des conditions generales pour securoute
	{
		echo "<input name='Submit' width='100px' type='submit' value=' VALIDER '
			onclick='javascript: return verif(1);' class='bouton200'/>";
	}
	else
	{
		echo "<input name='Submit' width='100px' type='submit' value=' VALIDER '
			onclick='javascript: return verif(0);' class='bouton200'/>";
	}
	echo "</td></tr>";

echo "</table>";
echo "</fieldset>";

echo "</form>";
}

?>


<script type="text/javascript">

function popup(page)
{
	window.open('conditions-generales2.php','popup','width=700,height=700,toolbar=false,scrollbars=yes, resizable=yes');
}


function change()
{
	if (document.Inscription.cas[1].checked)
	{
		document.getElementById('jeunes_conducteurs1').style.display = 'block';
		document.getElementById('jeunes_conducteurs2').style.display = 'block';
		document.getElementById('jeunes_conducteurs3').style.display = 'block';
		document.getElementById('jeunes_conducteurs_ref').value = 'here';
	}
	else
	{
		document.getElementById('jeunes_conducteurs1').style.display = 'none';
		document.getElementById('jeunes_conducteurs2').style.display = 'none';
		document.getElementById('jeunes_conducteurs3').style.display = 'none';
		document.getElementById('jeunes_conducteurs_ref').value = '';
	}
}

function verif(conditions_generales)
{
	if (document.Inscription.nom.value == "")
	{
		alert ('Erreur de saisie du nom');
		document.Inscription.nom.focus();
		return false;
	}
	else if (document.Inscription.prenom.value == "")
	{
		alert ('Erreur de saisie du prenom');
		document.Inscription.prenom.focus();
		return false;
	}
	else if (document.Inscription.adresse.value == "")
	{
		alert ('Erreur de saisie de l"adresse');
		document.Inscription.adresse.focus();
		return false;
	}
	else if (document.Inscription.code_postal.value == "" || isNaN(document.Inscription.code_postal.value))
	{
		alert ('Erreur de saisie du code postal');
		document.Inscription.code_postal.focus();
		return false;
	}
	else if (document.Inscription.ville.value == "")
	{
		alert ('Erreur de saisie de la ville');
		document.Inscription.ville.focus();
		return false;
	}
	else if (document.Inscription.lieu_naissance.value == "")
	{
		alert ('Erreur de saisie du lieu de naissance');
		document.Inscription.lieu_naissance.focus();
		return false;
	}
	else if (document.Inscription.tel1.value == "" && document.Inscription.tel2.value == "")
	{
		alert ('Saisissez au moins un numero de téléphone (fixe ou mobile)');
		document.Inscription.tel1.focus();
		return false;
	}
	else if (document.Inscription.tel1.value != "" && (isNaN(document.Inscription.tel1.value) == true))
	{
		alert ('Format de téléphone invalide : veuillez ne rentrer que des chiffres');
		document.Inscription.tel1.focus();
		return false;
	}
	else if (document.Inscription.tel2.value != "" && (isNaN(document.Inscription.tel2.value) == true))
	{
		alert ('Format de téléphone invalide : veuillez ne rentrer que des chiffres');
		document.Inscription.tel1.focus();
		return false;
	}
	else if (document.Inscription.email.value == "")
	{
		alert ('Erreur de saisie de l"email');
		document.Inscription.email.focus();
		return false;
	}
	else if (VerifMail(document.Inscription.email.value) == false)
	{
		alert ('Erreur de saisie de l"email');
		document.Inscription.email.focus();
		return false;
	}
	else if (document.Inscription.confirm_email.value == "")
	{
		alert ('Erreur de saisie de l"email de confirmation');
		document.Inscription.confirm_email.focus();
		return false;
	}
	else if (document.Inscription.confirm_email.value != document.Inscription.email.value)
	{
		alert ('Erreur de saisie de l"email de confirmation');
		document.Inscription.confirm_email.focus();
		return false;
	}
	else if (conditions_generales == 1 && document.Inscription.conditions.checked == false)
	{
		alert ('Vous devez valider les conditions générales de vente pour réserver votre stage !');
		return false;
	}

	/*else if (document.Inscription.num_permis.value == "")
	{
		alert ('Erreur de saisie du numéro de permis');
		document.Inscription.num_permis.focus();
		return false;
	}
	else if (document.Inscription.lieu_permis.value == "")
	{
		alert ('Erreur de saisie du lieu du permis');
		document.Inscription.lieu_permis.focus();
		return false;
	}
	else if (document.Inscription.jeunes_conducteurs_ref.value == 'here')
	{
		if (document.Inscription.annee_infraction.value == '1980')
		{
			alert ('Jeune conducteur, merci de saisir la date de votre infraction');
			document.Inscription.annee_infraction.focus();
			return false;
		}
		else if (document.Inscription.heure_infraction.value == '' || document.Inscription.minutes_infraction.value == '')
		{
			alert ('Jeune conducteur, merci de saisir les horaires de votre infraction');
			document.Inscription.heure_infraction.focus();
			return false;
		}
		else if (document.Inscription.lieu_infraction.value == '')
		{
			alert ('Jeune conducteur, merci de saisir le lieu de votre infraction');
			document.Inscription.lieu_infraction.focus();
			return false;
		}
		else if (document.Inscription.motif_infraction.value == '')
		{
			alert ('Jeune conducteur, merci de saisir le type de votre infraction');
			return false;
		}
		else if (document.Inscription.annee_48.value == '1980')
		{
			alert ('Jeune conducteur, merci de saisir la date de reception de votre lettre 48N');
			document.Inscription.annee_48.focus();
			return false;
		}
		else
		{
			return true;
		}
	}*/
	else
	{
		return true;
	}
}

// Cette fonction vérifie le format JJ/MM/AAAA saisi et la validité de la date.
// Le séparateur est défini dans la variable separateur
function verifdate(d)
{
	var dateaverifier=d
	if (dateaverifier.substring(0,1)=="0"){
         var j=parseInt(dateaverifier.substring(1,2));
	}
	else {
		var j=parseInt(dateaverifier.substring(0,2));
	}
	if (dateaverifier.substring(3,4)=="0"){
		var m=parseInt(dateaverifier.substring(4,5));
	}
	else {
		var m=parseInt(dateaverifier.substring(3,5));
	}
	var a=parseInt(dateaverifier.substring(6,10));

   //si la longueur est différent de 10 , problème
	if (dateaverifier.length != 10) {
		return false;
	}
	//les caratères / ne sont pas aux endroits attendus
	else {
		if((dateaverifier.charAt(2) != '/') && (dateaverifier.charAt(5) != '/')) {
			return false;
		}
	}
   //l'année n'est pa un chiffre
   if (isNaN(a)) {
      return false;
    }
   //le mois n'est pas un chiffre ou n'est pas compris entre 0 et12
     if ((isNaN(m))||(m<1)||(m>12)) {
      return false;
    }
   //test si il s'agit d'une année bissextile pour accepter le 29/02
   if (((a % 4)==0 && (a % 100)!=0) || (a % 400)==0){
         if ((isNaN(j)) || ((m!=2) && ((j<1)||(j>31))) || ((m==2) && ((j<1)||(j>29)))) {
            return false;
        }
   }
    else {
         if ((isNaN(j)) || ((m!=2) && ((j<1)||(j>31))) || ((m==2) && ((j<1)||(j>28)))){
         return false;
      }
   }
   return true;
}

//cette fonction test si caractères numériques récupérée
function IsNumberString(NumStr)
{
	var regEx=/^[0-9]+$/;
	var ret=false;
	if (regEx.test(NumStr))
		ret=true;
	return ret;
}

function VerifMail(adresse)
{
	var place = adresse.indexOf("@",1);
	var point = adresse.indexOf(".",place+1);

	if ((place > -1)&&(adresse.length >2)&&(point > 1))
	{
		return(true);
	}
	else
	{
		return(false);
	}
}

function CGBoxChange()
{
	if (document.Inscription.checkbox.checked == true)
		document.Inscription.Submit.disabled = false;
	else
		document.Inscription.Submit.disabled = true;
}
</script>