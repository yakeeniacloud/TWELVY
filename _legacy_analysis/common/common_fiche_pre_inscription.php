<?php
function fiche($tab, $site = "psp")
{
	$dateLocal = date("y-m-d");

	//anti casse pieds:
	if (isset($tab['email2']) && !empty($tab['email2']))
	{
		echo "Problème technique (email2). Merci de contacter un conseiller";
		exit;
	}

	$temps = time();
	if (isset($tab['verifTemps']) && ($temps - $tab['verifTemps']) < 3)
	{
		echo "Problème technique (temps). Merci de contacter un conseiller";
		exit;
	}
	//fin anti casse pieds

	if (!isset($tab['stageId']) 	||
		!isset($tab['membreId']) 	||
		!isset($tab['nom']) 		||
		!isset($tab['prenom']) 	||
		!isset($tab['adresse']) 	||
		!isset($tab['code_postal']) ||
		!isset($tab['ville']) 	||
		!isset($tab['lieu_naissance']) ||
		!isset($tab['email']) 	||
		!isset($tab['num_permis']))
	{
		echo "Erreur sur la page, revenez au menu principal";
		exit;
	}

	$stageId = $tab['stageId'];
	$membreId = $tab['membreId'];
	$nom = $tab['nom'];
	$jeune_fille = $tab['jeune_fille'];
	$nom = strtoupper($nom);
	$prenom = $tab['prenom'];
	$prenom = strtolower($prenom);
	$date_naissance = $tab['annee_naissance']."-".$tab['mois_naissance']."-".$tab['jour_naissance'];
	$lieu_naissance = $tab['lieu_naissance'];
	$lieu_naissance = strtoupper($lieu_naissance);
	$adresse = $tab['adresse'];
	$code_postal = $tab['code_postal'];
	$ville = $tab['ville'];
	$ville = strtoupper($ville);
	$tel1 = $tab['tel1'];
	$tel2 = $tab['tel2'];
	$email = $tab['email'];
	$date_preinscription = date("y-m-d");
	$prix = $tab['prix'];
	$num_permis = $tab['num_permis'];
	$lieu_permis = $tab['lieu_permis'];
	$lieu_permis = strtoupper($lieu_permis);
	$date_permis = $tab['annee_permis']."-".$tab['mois_permis']."-".$tab['jour_permis'];
	$cas = $tab['cas'];
	$civilite = $tab['civilite'];
	$date_infraction = $tab['jour_infraction']." ".$tab['mois_infraction']." ".
		$tab['annee_infraction']." (".$tab['heure_infraction'].":".$tab['minutes_infraction'].") a ".$tab['lieu_infraction'];
	$motif_infraction = $tab['motif_infraction'];
	$date_lettre = $tab['jour_48']." ".$tab['mois_48']." ".$tab['annee_48'];

	if (isset($tab['radio_type_paiement']) && $tab['radio_type_paiement'] == 1)
	{
		$supprime = 1;
		$status = "supprime";
	}
	else
	{
		$supprime = 0;
		$status = "pre-inscrit";
	}


	//filtrage inscriptions multiples
	//-------------------------------
	require ("../connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	$sql = "SELECT
				stagiaire.* FROM stagiaire
			WHERE
				stagiaire.nom='$nom' AND stagiaire.id_stage=$stageId AND stagiaire.date_naissance='$date_naissance'";

	$rs = mysql_query($sql, $stageconnect) or die(mysql_error());
	$row = mysql_fetch_assoc($rs);
	$totalRows = mysql_num_rows($rs);


	if ($totalRows != 0)
	{
		echo "<br><br><strong><font color='red'>VOUS ETES DEJA INSCRIT A CE STAGE, VOTRE INSCRIPTION A BIEN ETE PRISE EN COMPTE PAR NOTRE CENTRALE DE RESERVATION.<BR><BR>
		UN EMAIL VOUS A ETE ENVOYE A L'ADRESSE QUE VOUS NOUS AVEZ INDIQUEE RECAPITULANT LES DETAILS DE VOTRE INSCRIPTION.<br>
		SI TEL N'EST PAS LE CAS, MERCI DE CONTACTER NOTRE HOTLINE AU PLUS VITE AU 04-86-31-80-70<BR><BR>
		<u>L'EQUIPE PROSTAGESPERMIS.</u></font></strong>";

		exit;
	}


	//CAS CARTE BLEUE ACTIROUTE:
	//--------------------------
	if (isset($tab['radio_type_paiement']) && $tab['radio_type_paiement'] == 1 && $membreId == 64)
	{
		$caddie = array();

		$caddie[] =  $stageId;
		$caddie[] =  $membreId;
		$caddie[] =  $civilite;
		$caddie[] =  $nom;
		$caddie[] =  $jeune_fille;
		$caddie[] =  $prenom;
		$caddie[] =  $date_naissance;
		$caddie[] =  $lieu_naissance;
		$caddie[] =  $adresse;
		$caddie[] =  $code_postal;
		$caddie[] =  $ville;
		$caddie[] =  $tel1;
		$caddie[] =  $tel2;
		$caddie[] =  $email;
		$caddie[] =  $date_preinscription;
		$caddie[] =  $prix;
		$caddie[] =  $num_permis;
		$caddie[] =  $lieu_permis;
		$caddie[] =  $date_permis;
		$caddie[] =  $cas;
		$caddie[] =  $date_infraction;
		$caddie[] =  $motif_infraction;
		$caddie[] =  $date_lettre;
		$caddie[] =  $supprime;

		//Numéro de commande
		$NumCmd      = "" . date("YmdHis");
		$caddie[] =  $NumCmd ;

		$xCaddie = base64_encode(serialize($caddie));

		//FIN CADDIE
		//----------

		print ("<HTML><HEAD><TITLE>E-TRANSACTIONS - Paiement Securise sur Internet</TITLE></HEAD>");
		print ("<BODY bgcolor=#ffffff>");
		print ("<Font color=#000000>");
		print ("<center><H1>PAIEMENT CARTE BLEUE SECURISE</H1></center><br><br>");

		$parm="merchant_id=039248918300041";  //013044876511111
		$parm="$parm merchant_country=fr";
		$parm="$parm amount=".$prix*100;
		$parm="$parm currency_code=978";
		$parm="$parm language=fr";
		$parm="$parm order_id=PSP_".$NumCmd;
		$parm="$parm payment_means=CB,2,VISA,2,MASTERCARD,2";
		$parm="$parm caddie=".$xCaddie;

		$parm="$parm pathfile=/home/prostage/bav/actiroute/pathfile";

		$parm="$parm normal_return_url=http://www.prostagespermis.fr/merci.php";
		$parm="$parm cancel_return_url=http://www.prostagespermis.fr/regret.php";
		$parm="$parm automatic_response_url=http://www.prostagespermis.fr/call_autoresponse.php";

		$path_bin = "/home/prostage/bav/actiroute/request";

		$result = exec("$path_bin $parm");

		//	sortie de la fonction : $result=!code!error!buffer!
		//	    - code=0	: la fonction génère une page html contenue dans la variable buffer
		//	    - code=-1 	: La fonction retourne un message d erreur dans la variable error

		//On separe les differents champs et on les met dans une variable tableau

		$tableau = explode ("!", "$result");

		//	récupération des paramètres
		$code = $tableau[1];
		$error = $tableau[2];
		$message = $tableau[3];

		//  analyse du code retour
		if (( $code == "" ) && ( $error == "" ) )
 		{
  			print ("<BR><CENTER>erreur appel request</CENTER><BR>");
  			print ("executable request non trouve $path_bin");
 		}

		//	Erreur, affiche le message d erreur
		else if ($code != 0)
		{
			print ("<center><b><h2>Erreur appel API de paiement.</h2></center></b>");
			print ("<br><br><br>");
			print (" message erreur : $error <br>");
		}

		//	OK, affiche le formulaire HTML
		else
		{
			print ("<br><br>");

			# OK, affichage du mode DEBUG si activé
			print (" $error <br>");
			print ("  $message <br>");
		}

		print ("</BODY></HTML>");

		exit;

	}


	//require ("../connections/stageconnect.php");
	//mysql_select_db($database_stageconnect, $stageconnect);

	//insertion stagiaire
	//-------------------
 	$sql = "INSERT INTO stagiaire (id_stage,
 									nom,
 									jeune_fille,
 									prenom,
 									date_naissance,
 									lieu_naissance,
 									adresse,
 									code_postal,
 									ville,
 									tel,
 									mobile,
 									email,
 									date_preinscription,
									status,
 									paiement,
									num_permis,
									lieu_permis,
									date_permis,
									cas,
									civilite,
									date_infraction,
									motif_infraction,
									date_lettre,
									supprime)
 	VALUES (\"$stageId\",
 			\"$nom\",
 			\"$jeune_fille\",
 			\"$prenom\",
 			\"$date_naissance\",
 			\"$lieu_naissance\",
 			\"$adresse\",
 			\"$code_postal\",
 			\"$ville\",
 			\"$tel1\",
 			\"$tel2\",
 			\"$email\",
 			\"$date_preinscription\",
			\"$status\",
 			\"$prix\",
 			\"$num_permis\",
 			\"$lieu_permis\",
			\"$date_permis\",
			\"$cas\",
			\"$civilite\",
			\"$date_infraction\",
			\"$motif_infraction\",
			\"$date_lettre\",
			\"$supprime\"
			)";

	mysql_query($sql) or die('Erreur SQL !'.$sql.'<br />'.mysql_error());
	$id_stagiaire_tmp = mysql_insert_id(); //attention s'assurer que l'ID n'est pas un bigInt

	//stagiaire
	//---------
	$query_stagiaire = "SELECT stagiaire.* FROM stagiaire WHERE stagiaire.id = $id_stagiaire_tmp";
	$rsStagiaire = mysql_query($query_stagiaire, $stageconnect) or die(mysql_error());
	$row_stagiaire = mysql_fetch_assoc($rsStagiaire);
	$totalRows_stagiaire = mysql_num_rows($rsStagiaire);

	//insertion transaction
	//---------------------
	$date = date("y-m-d");

	$sql = "INSERT INTO transaction (id_stage,
									id_stagiaire,
									id_membre,
									type_paiement,
									date_transaction) VALUES
			('$stageId',
			'$id_stagiaire_tmp',
			'$membreId',
			'cheque_en_attente',
			'$date')";

	$res = mysql_query($sql, $stageconnect)or die ("echec insertion transaction");
	$id_transaction = mysql_insert_id(); //attention s'assurer que l'ID n'est pas un bigInt

	//Cas paiement par CB securoute
	//-----------------------------
	if (isset($tab['radio_type_paiement']) && $tab['radio_type_paiement'] == 1 && $membreId == 44)
	{
		$PBX_MODE        = '4'; // appel en ligne de commande
		$PBX_LANGUE      = 'FRA'; // FRA French
		$PBX_SITE        = '5132338';
		$PBX_RANG        = '01';
		$PBX_IDENTIFIANT = '715269714';
		$PBX_TOTAL       = $prix*100;
		$PBX_DEVISE      = '978'; // monaie = euros
		$PBX_CMD         = $id_transaction;
		$PBX_PORTEUR     = $email;
		$PBX_RETOUR      = "tarif:M;ref:R;auto:A;trans:T;erreur:E";
		$PBX_REPONDRE_A = "http://www.prostagespermis.fr/response_cb_securoute.php";
		$PBX_EFFECTUE    = "http://www.prostagespermis.fr/merci.php";
		$PBX_REFUSE      = "http://www.prostagespermis.fr/regret.php";
		$PBX_ANNULE      = "http://www.prostagespermis.fr/regret.php";

		$PBX = "PBX_MODE=$PBX_MODE PBX_SITE=$PBX_SITE PBX_RANG=$PBX_RANG PBX_IDENTIFIANT=$PBX_IDENTIFIANT PBX_TOTAL=$PBX_TOTAL PBX_DEVISE=$PBX_DEVISE PBX_CMD=$PBX_CMD PBX_PORTEUR=$PBX_PORTEUR PBX_RETOUR=$PBX_RETOUR PBX_EFFECTUE=$PBX_EFFECTUE PBX_REFUSE=$PBX_REFUSE PBX_ANNULE=$PBX_ANNULE PBX_REPONDRE_A=$PBX_REPONDRE_A PBX_OUTPUT=D";

		$post_data = "$PBX";

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "http://www.prostagespermis.securoute.net/module_paybox.php");
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$page = curl_exec($curl);
		curl_close($curl);

		echo "<HTML>";
		echo "<HEAD>";
		echo "<TITLE>PAYBOX</TITLE></HEAD>";
		echo "<BODY onload='document.PAYBOX.submit();'><CENTER>";
		echo "<b><br> connexion en cours <br> sur le serveur de paiement sécurisé... </b>";
		echo "<Form name=PAYBOX Action='https://tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi' Method=POST>";
		echo "<INPUT TYPE=hidden NAME=PBX_DATA VALUE=$page>";
		echo "<INPUT TYPE=hidden name=PBX_ANNULE value='http://www.prostagespermis.fr/regret.php'>";
		echo "<INPUT TYPE=hidden name=PBX_EFFECTUE value='http://www.prostagespermis.fr/merci.php'>";
		echo "<INPUT TYPE=hidden name=PBX_REFUSE value='http://www.prostagespermis.fr/regret.php'>";
		echo "<INPUT TYPE=hidden name=PBX_SOURCE value='HTML'>";
		echo "<INPUT TYPE=hidden name=PBX_VERSION value='211-OS INCONNU'>";
		echo "<INPUT TYPE=SUBMIT VALUE='PAYBOX'>";
		echo "</FORM>";
		echo "</CENTER></BODY>";
		echo "</HTML>";

		exit;
	}

	if ($membreId == 65) //allopermis
	{
		require_once("../soap/allopermis/inscription_allopermis.php");
		$ret = inscriptionAllopermis($id_stagiaire_tmp);

		if (isset($tab['radio_type_paiement']) && $tab['radio_type_paiement'] == 1 && $ret!=0)
		{
			//$adresseAP = "http://test.allopermis.com/stages/inscription-cb-diffuseur.php?diffuseur=prostages&siid=".$ret."&idt=".$id_transaction;
			$adresseAP = "http://www.allopermis.com/stages/inscription-cb-diffuseur.php?diffuseur=prostages&siid=".$ret."&idt=".$id_transaction;

			echo "<form id='cb_allopermis' name='cb_allopermis' action='$adresseAP' method=POST>";
			echo "</form>";
			?>


			<script>
			document.forms.cb_allopermis.submit();
			</script>


			<?php
			//header("Location: '$adresseAP'");
			//include("'$adresseAP'");
			exit;
		}
	}

	//requete stage

	$query_stage = "SELECT  stage.*,
									site.nom,
									site.ville,
									site.adresse,
									site.code_postal FROM stage, site
							WHERE 	stage.id = $stageId AND
									stage.id_site = site.id AND
									stage.id_membre = $membreId

							UNION

							SELECT 	stage_dyn.*,
									site_dyn.nom,
									site_dyn.ville,
									site_dyn.adresse,
									site_dyn.id_membre,
									site_dyn.code_postal FROM stage_dyn, site_dyn
							WHERE 	stage_dyn.id = $stageId AND
									stage_dyn.id_site = site_dyn.id_externe AND
									stage_dyn.id_membre = site_dyn.id_membre AND
									stage_dyn.id_membre = $membreId";

	$rsStage = mysql_query($query_stage, $stageconnect) or die(mysql_error());
	$row_stage = mysql_fetch_assoc($rsStage);
	$totalRows_stage = mysql_num_rows($rsStage);

	//update stage
	$nb = $row_stage['nb_preinscrits']+1;
	$sql = "UPDATE stage SET nb_preinscrits = $nb WHERE stage.id = $stageId";
	mysql_query($sql, $stageconnect) or die('Erreur SQL !'.$sql.'<br />'.mysql_error());

	//requete membre
	$membreID = $row_stage['id_membre'];
	$query_membre = sprintf("SELECT membre.* FROM membre
							WHERE membre.id = $membreId");
	$rsMembre = mysql_query($query_membre, $stageconnect) or die(mysql_error());
	$row_membre = mysql_fetch_assoc($rsMembre);
	$totalRows_membre = mysql_num_rows($rsMembre);

	//FICHE PRE-INSCRIPTION
	//---------------------
	echo "<table width='650px' style='font-size:14px'>";
	echo "<tr>";
		echo "<td><img src='images/logo.jpg' width='190' height='80' align='left'/></td>";
		echo "<td align='right'><strong>Id (".$row_stagiaire['id'].") ".$row_stagiaire['civilite']." ".
				$row_stagiaire['nom']." ".$row_stagiaire['prenom']." (Cas ".$row_stagiaire['cas'].")<br>";
		echo $row_stagiaire['tel']."   ".$row_stagiaire['email'];
		echo"<br>";
		echo "Permis: ".$row_stagiaire['num_permis'];echo "<br>";
		echo MySQLDateToExplicitDate($row_stage['date1'])." (".$row_stage['prix']." euros)"; echo "<br>";
		echo $row_stage['nom']." ".$row_stage['ville'];
		echo "</strong></td>";
	echo "</tr>";
	echo "</table>";
	echo "<br>";

	echo "<strong>FICHE DE PRE-INSCRIPTION A RETOURNER A L'ADRESSE DU CENTRE ORGANISATEUR</strong>";
	echo "<table width='650px' style='font-size:14px'>";
	echo "<tr>";
	echo "<td width='350px'><fieldset>";
	echo "<legend><strong>Le stage:</strong></legend>";
		echo "<table>";
		echo "<tr>";
			echo "<td>Dates:</td>";
			echo "<td>".MySQLDateToExplicitDate($row_stage['date1'])."<br>";
			echo MySQLDateToExplicitDate($row_stage['date2'])."</td>";
		echo "</tr>";

		echo "<tr>";
			echo "<td>Lieu:</td>";
			echo "<td>".$row_stage['nom']."<br>";
			echo $row_stage['adresse']."<br>";
			echo sprintf("%05d", $row_stage['code_postal'])." ".html_entity_decode($row_stage['ville'])."</td>";
		echo "</tr>";

		echo "<tr>";
			echo "<td>Horaires:</td>";
			echo "<td>".$row_stage['debut_am']." ".$row_stage['fin_am']." et ".
				$row_stage['debut_pm']." ".$row_stage['fin_pm']."</td>";
		echo "</tr>";

		echo "<tr>";
			echo "<td>Prix:</td>";
			echo "<td>".$row_stage['prix']." euros </td>";
		echo "</tr>";
		echo "</table>";
	echo "</fieldset></td>";

	echo "<td>";
	echo "<fieldset><legend><strong>Le centre organisateur:</strong></legend>";
	echo "<table>";
	echo "<tr>";
		echo "<td>Nom:</td>";
		echo "<td>".$row_membre['nom']."</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>Adresse: </td>";
	echo "<td>".$row_membre['adresse']."</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>Tel:</td>";
	echo "<td>".$row_membre['tel']."  ".$row_membre['mobile']."   Fax:".$row_membre['fax']."</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>Email: </td>";
	echo "<td>".$row_membre['email']."</td>";
	echo "</tr>";
	echo "</table></fieldset></td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td colspan='2'>";
	echo "<fieldset><legend><strong>Le stagiaire:</strong></legend>";
	echo "<table width='650px'>";
	echo "<tr>";
		echo "<td width='30%'>Nom Prenom:</td>";
		echo "<td>".$row_stagiaire['civilite']." ".stripslashes($row_stagiaire['nom']." ".
				$row_stagiaire['prenom'])." (pre-inscrit le ".dateFr($dateLocal).")</td>";
	echo "</tr>";

	if ($row_stagiaire['jeune_fille'] != "")
	{
		echo "<tr>";
		echo "<td>Nom jeune fille:</td>";
		echo "<td>".$row_stagiaire['jeune_fille']."</td>";
		echo "</tr>";
	}

	echo "<tr>";
	echo "<td>Ne(e) le:</td>";
	echo "<td>".datefr($row_stagiaire['date_naissance'])." a ".stripslashes($row_stagiaire['lieu_naissance'])."</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>Adresse:</td>";
	echo "<td>".stripslashes($row_stagiaire['adresse'])."</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td> </td>";
	echo "<td>".$row_stagiaire['code_postal']." ".stripslashes($row_stagiaire['ville'])."</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>Telephone:</td>";
	echo "<td>".$row_stagiaire['tel']."    ".$row_stagiaire['mobile']."</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>Email:</td>";
	echo "<td>".$row_stagiaire['email']."</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>Permis:</td>";
	echo "<td>".$row_stagiaire['num_permis']." le ".datefr($row_stagiaire['date_permis']).
			" (".stripslashes($row_stagiaire['lieu_permis']).")</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>Type de stage:</td>";
	echo "<td>Cas ".$row_stagiaire['cas']."</td>";
	echo "</tr>";

	if (($row_membre['id'] == 64 || $row_membre['id'] == 38) && $row_stagiaire['cas'] == 2)
	{
		echo "<tr>";
		echo "<td>Infraction:</td>";
		echo "<td>".$row_stagiaire['motif_infraction']." ".$row_stagiaire['date_infraction']."</td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td>Reception lettre 48N:</td>";
		echo "<td>".$row_stagiaire['date_lettre']."</td>";
		echo "</tr>";
	}

	echo "<tr>";
	echo "<td colspan='2'><div align='justify'><br>";
	echo "Merci d'envoyer au plus tot votre fiche de pre-inscription par courrier
en imprimant directement cette page ou l'email qui vous sera envoye dans quelques instants a l'adresse du centre organisateur. N'oubliez pas de signer la fiche et d'y joindre imperativement les pieces suivantes: </div>";
	echo "<ul>";
		echo "<li>Photocopie de<strong> l'interieur de votre permis</strong> (cote avec votre photo) ou, en cas de suspension la notification,</li>";
		echo "<li>Un cheque de <strong>".$row_stage['prix']."</strong> euros a l'ordre de <strong>".
		$row_membre['nom']."</strong></li>";
		echo "<li>ATTENTION JEUNES CONDUCTEURS : si <strong>48 N</strong>, nous envoyer la photocopie(recto/verso)</li>";
	echo "</ul>";
	echo "<br>";
	echo "Retournez votre dossier complet par courrier";
	if (strtotime($row_stage['date1']) - strtotime("now") < 518400){echo " sous 48H ";}
	else {echo " sous 4 jours ";}
	echo "a:<br><br>";
	echo "<div align='center'>";
		echo $row_membre['nom'];
		echo "<br>";
		echo $row_membre['adresse'];
	echo "</div>";

	echo "<div align='justify'>";
	echo "<br><br>";

	if ($row_membre['id'] == 64)
	{
		echo "<em>En cas d'annulation de votre part moins de 5 jours avant le premier jour du stage
pour lequel vous avez reserve votre inscription, une somme de 60 euros sera conservee
par acti-ROUTE pour frais de gestion de votre dossier.</em>";
	}

	if ($row_membre['id'] == 44)
	{
		echo "<em>Rappel des Articles de vente 1 et 6:</em><br />
		<em>Article 1 : Capital points</em><br />
		Afin d'effectuer un stage de Securite Routiere cas 1 (recuperation de 4 points), le capital points du permis de conduire doit etre au moins egal a 1 point et inferieur ou egal a 8 points. Dans le cas ou votre solde de points est nul mais que vous n'avez pas receptionne de lettre 48S, Il est obligatoire, avant de vous inscrire, de faire valider votre releve integral d'information par un conseiller permis. Si le conducteur n'a jamais recu de lettre (type 48, 48M ou 48N), il doit demander un releve integral d'information dans une Prefecture ou Sous-Prefecture. En cas de fausse declaration la responsabilite de PROStagesPermis et de l'organisateur du stage ne pourra en aucun cas etre engagee et le remboursement du stage sera impossible.<br /><br />
		<em>Article 6 : Annulation d'une inscription</em><br />
		En cas d'absence (quelque en soit la cause) signalee entre 7 jours et 4 jours ouvrables avant le debut du stage, les frais administratifs occasionnes au centre organisateur seront factures 50 euros. Si l'absence est signalee 4 jours ouvrables avant le stage (quelque en soit la cause), le prix de la formation reste entierement du. Dans tous les cas de remboursement, il sera deduit des frais de traitement de 5,00 euros. La validation de la commande vaut acceptation de ces conditions d'annulation. Toute demande d'annulation devra etre faite par lettre recommandee au centre organisateur.";
	}

	echo "<br><br>";
	echo "BON POUR ACCORD. Date et Signature:";
	echo "<br><br>";
	echo "</div></td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td><input type='button' value='Cliquez ici pour imprimer votre fiche'
		onclick='javascript: window.print()'/></td>";

	echo "<td align='right'><input type='button' value='Retour accueil'                  '
		onClick=\"parent.location='about:home'\"/></td>";
	echo "</tr>";
	echo "</table>";
	echo "</fieldset>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";

	sendEmail($id_transaction, $site);
}

function sendEmail($transactionID, $site)
{
	$dateLocal = date("y-m-d");
	$aujourdui = date("d-m-y");

	require ("../connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	//retrouver la transaction
	$query_transaction = sprintf("SELECT transaction.* FROM transaction WHERE
										transaction.id = $transactionID");

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
		$membreID = $row_transaction['id_membre'];
	}

	//requete stagiaire
	$query_stagiaire = sprintf("SELECT stagiaire.* FROM stagiaire WHERE
										stagiaire.id = $stagiaire_ID");

	$rsStagiaire = mysql_query($query_stagiaire, $stageconnect) or die(mysql_error());
	$row_stagiaire = mysql_fetch_assoc($rsStagiaire);
	$totalRows_stagiaire = mysql_num_rows($rsStagiaire);

	//requete stage

	$query_stage = sprintf("SELECT  stage.*,
									site.nom,
									site.ville,
									site.adresse,
									site.code_postal FROM stage, site
							WHERE 	stage.id = $stageID AND
									stage.id_site = site.id AND
									stage.id_membre = $membreID

							UNION

							SELECT 	stage_dyn.*,
									site_dyn.nom,
									site_dyn.ville,
									site_dyn.adresse,
									site_dyn.code_postal FROM stage_dyn, site_dyn
							WHERE 	stage_dyn.id = $stageID AND
									stage_dyn.id_site = site_dyn.id_externe AND
									stage_dyn.id_membre = $membreID");

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

	//ENVOI DU MAIL
	//-------------
	$to = $row_stagiaire['email'];
	$contact = "contact@prostagespermis.fr";

	if ($row_stagiaire['status'] == "inscrit")
	{
		$subject = "Inscription: ".$row_stagiaire['nom']." ".$row_stagiaire['prenom'];
	}
	else
	{
		$subject = "PRE-Inscription: ".$row_stagiaire['nom']." ".$row_stagiaire['prenom'];
	}

	$subject = stripslashes($subject);

	$msg  = "<b>FICHE INSCRIPTION PROStagesPermis (le ".$aujourdui.")</b>";$msg .= "<br>";

	if ($row_membre['id'] != 64 && $row_membre['id'] != 38)
	{
			$msg .= "==================================================================";$msg .= "<br>";
			$msg .= "   Stagiaire: "."(Id ".$row_stagiaire['id'].") ".stripslashes($row_stagiaire['nom']." ".$row_stagiaire['prenom'])." (Cas ".$row_stagiaire['cas'].")";$msg .= "<br>";
			$msg .= "   Tel/Email: ".$row_stagiaire['tel']." ".$row_stagiaire['mobile']." ".$row_stagiaire['email'];$msg .= "<br>";
			$msg .= "   Permis: ".$row_stagiaire['num_permis'];$msg .= "<br>";
			$msg .= "   Stage: ".MySQLDateToExplicitDate($row_stage['date1'])." ".filter($row_stage['nom'])." ".filter($row_stage['ville'])." (".$row_stagiaire['paiement']." euros)";$msg .= "<br>";
			$msg .= "==================================================================";$msg .= "<br>";$msg .= "<br>";
	}
	else
	{
		$msg .= "<br>";
	}

	$msg .= "<u><b>CENTRE ORGANISATEUR:</b></u>";$msg .= "<br>";
	$msg .= "<table  width=\"100%\">";
	$msg .= "<tr>";
	$msg .= "<td width=\"23%\">";$msg .= "<em>Nom: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= stripslashes($row_membre['nom']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Adresse: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= stripslashes($row_membre['adresse']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Tel: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_membre['tel']."  ".$row_membre['mobile'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Email: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_membre['email']."  Fax: ".$row_membre['fax'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "</table>";

	$msg .= "<br><u><b>STAGIAIRE:</b></u>";$msg .= "<br>";
	$msg .= "<table  width=\"100%\">";
	$msg .= "<tr>";
	$msg .= "<td width=\"23%\">";$msg .= "<em>Identite: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['civilite']." ".stripslashes($row_stagiaire['nom']." ".$row_stagiaire['prenom']);$msg .= "</td>";
	$msg .= "</tr>";
	if ($row_stagiaire['jeune_fille'] != "")
	{
		$msg .= "<tr>";
		$msg .= "<td>";$msg .= "<em>Nom de jeune fille: </em>";$msg .= "</td>";
		$msg .= "<td>";$msg .= $row_stagiaire['jeune_fille'];$msg .= "</td>";
		$msg .= "</tr>";
	}
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Ne(e) le: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= datefr($row_stagiaire['date_naissance'])." a ".stripslashes($row_stagiaire['lieu_naissance']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Adresse: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= stripslashes($row_stagiaire['adresse'])."<br>".$row_stagiaire['code_postal']." ".stripslashes($row_stagiaire['ville']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Coordonnees: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= "Tel:".$row_stagiaire['tel']." ".$row_stagiaire['mobile']." Email: ".$row_stagiaire['email'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Permis: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stagiaire['num_permis']." le ".datefr($row_stagiaire['date_permis'])." a ".stripslashes($row_stagiaire['lieu_permis']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Type de stage: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= "Cas ".$row_stagiaire['cas'];$msg .= "</td>";
	$msg .= "</tr>";

	if (($row_membre['id'] == 64 || $row_membre['id'] == 38) && $row_stagiaire['cas'] == 2)
	{
		$msg .= "<tr>";
		$msg .= "<td>";$msg .= "<em>Infraction: </em>";$msg .= "</td>";
		$msg .= "<td>";$msg .= $row_stagiaire['motif_infraction']." - ".$row_stagiaire['date_infraction'];$msg .= "</td>";
		$msg .= "</tr>";
		$msg .= "<tr>";
		$msg .= "<td>";$msg .= "<em>Reception lettre 48N: </em>";$msg .= "</td>";
		$msg .= "<td>";$msg .= $row_stagiaire['date_lettre'];$msg .= "</td>";
		$msg .= "</tr>";
	}
	$msg .= "</table>";

	$msg .= "<br><u><b>DETAILS DU STAGE:</b></u>";$msg .= "<br>";
	$msg .= "<table  width=\"100%\">";
	$msg .= "<tr>";
	$msg .= "<td width=\"23%\">";$msg .= "<em>Dates: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= MySQLDateToExplicitDate($row_stage['date1'])." et ".MySQLDateToExplicitDate($row_stage['date2']);$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Horaires: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $row_stage['debut_am']." ".$row_stage['fin_am']." et ".$row_stage['debut_pm']." ".$row_stage['fin_pm'];$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Lieu: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= filter($row_stage['nom'])."<br>".filter($row_stage['adresse'])."<br>".$row_stage['code_postal']." ".filter($row_stage['ville']);$msg .= "</td>";
	$msg .= "</tr>";
	$prix = $row_stagiaire['paiement'];
	$prix .= " euros";
	$msg .= "<tr>";
	$msg .= "<td>";$msg .= "<em>Prix: </em>";$msg .= "</td>";
	$msg .= "<td>";$msg .= $prix." (Mode paiement: <b>".$row_transaction['type_paiement']."</b>)";$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "</table>";
	$msg .= "<br>";

	$msg2 = $msg;

	$msg .= "<table  width=\"100%\">";
	$msg .= "<tr>";
	$msg .= "<td>";
	$msg .= "Merci de retourner par courrier votre fiche de pre-inscription (etablie depuis notre site internet) ou ce mail, de le dater, de le signer et d'y joindre imperativement les pieces suivantes:";
	$msg .= "<ul>";
	$msg .= "<li> La photocopie de l'interieur de votre permis de conduire (cote avec votre photo) ou, en cas de suspension, la notification,</li>";

	if ($row_stagiaire['status'] != "inscrit")
	{
		$msg .= "<li> Un cheque de ".$row_stage['prix']." euros a l'ordre de ".stripslashes($row_membre['nom']);$msg .= "</li>";
	}

	$msg .= "<li> Attention jeunes conducteurs: si 48 N, joindre la photocopie (recto/verso)";$msg .= "</li>";
	$msg .= "</ul>";
	$msg .= "<br>";

	$msg .= "<u><b>RETOURNEZ VOTRE DOSSIER COMPLET A L'ADRESSE SUIVANTE:</b></u>";$msg .= "<br>";
	$msg .= "     ".stripslashes($row_membre['nom']);$msg .= "<br>";
	$msg .= "     ".stripslashes($row_membre['adresse']);$msg .= "<br><br>";

	$msg .= "Votre inscription  devient definitive si votre dossier complet est recu dans les ";
	if (strtotime($row_stage['date1']) - strtotime("now") < 518400)
	{
		$msg .= "48H suivants votre pre-inscription (date de ce courrier).";
	}
	else
	{
		$msg .= "4 jours suivants votre pre-inscription (date de ce courrier).";
	}
	$msg .= "A reception de votre dossier complet, vous recevrez un email confirmant votre inscription definitive.";

	if ($row_membre['id'] == 64)
	{
		$msg .= " En cas d'annulation de votre part moins de 5 jours avant le premier jour du stage pour lequel vous avez reserve votre inscription, une somme de 60 euros sera conservee par acti-ROUTE pour frais de gestion de votre dossier.";$msg .= "<br><br>";
	}
	else
	{
		$msg .= "<br><br>";
	}

	if ($row_membre['id'] == 44)
	{
		$msg .= "Rappel des Articles de vente 1 et 6:";$msg .= "<br>";
		$msg .= "Article 1 : Capital points:";$msg .= "<br>";
		$msg .= "Afin d’effectuer un stage de «Sécurité Routière» cas n°1 (récupération de 4 points), le capital points du permis de conduire doit être au moins égal à 1 point et inférieur ou égal à 8 points. Dans le cas où votre solde de points est nul mais que vous n'avez pas réceptionné de lettre 48S, Il est obligatoire, avant de vous inscrire, de faire valider votre relevé intégral d'information par un conseiller permis. Si le conducteur n’a jamais reçu de lettre (type 48, 48M ou 48N), il doit demander un relevé intégral d’information dans une Préfecture ou Sous-Préfecture. En cas de fausse déclaration la responsabilité de PROStagesPermis et de l’organisateur du stage ne pourra en aucun cas être engagée et le remboursement du stage sera impossible.";$msg .= "<br><br>";
		$msg .= "Article 6 : Annulation inscription:";$msg .= "<br>";
		$msg .= "En cas d’absence (quelque en soit la cause) signalée entre 7 jours et 4 jours ouvrables avant le début du stage, les frais administratifs occasionnés au centre organisateur seront facturés 50€. Si l’absence est signalée 4 jours ouvrables avant le stage (quelque en soit la cause), le prix de la formation reste entièrement dû. Dans tous les cas de remboursement, il sera déduit des frais de traitement de 5,00€. La validation de la commande vaut acceptation de ces conditions d’annulation. Toute demande d’annulation devra être faite par lettre recommandée au centre organisateur.";$msg .= "<br><br>";
	}

	$msg .= "BON POUR ACCORD. Date et signature:";
	$msg .= "</td>";
	$msg .= "</tr>";
	$msg .= "</table>";

	//$headers = "From: ".stripslashes($row_membre['nom'])."\n";
	$headers = "From: PROStagesPermis <contact@prostagespermis.fr>\n";
	$headers .= "Reply-To: ".$contact."\n";
	$headers .= 'MIME-version: 1.0'."\n";
	$headers .= 'Content-type: text/html; charset= iso-8859-1'."\n";

	$provenance = getIndexUrl($site);
	$intitule = $provenance[1];


	if ($row_membre['id'] == 65)
	{
		mail($contact, $intitule." ".$subject." - ".stripslashes($row_membre['nom']), $msg, $headers);
		sleep(1);
		mail("prostages@allopermis.com", $subject, $msg, $headers);
	}
	else
	{
		if ($row_membre['id'] == 44)
		{
			mail($row_membre['email'], $subject, $msg2, $headers);
		}
		else
		{
			mail($row_membre['email'], $subject, $msg, $headers);
		}
		sleep(1);
		mail($to, $subject, $msg, $headers);
		sleep(1);
		mail($contact, $intitule." ".$subject." - ".stripslashes($row_membre['nom']), $msg, $headers);
	}
}

function getIndexUrl($site)
{
	$provenance = "";
	$url ="";

	switch($site)
	{
		case psp: //psp
			$url = "http://www.prostagespermis.fr";
			$provenance = "";
		break;

		case amf: //amf
			$url = "http://www.amf-permis.fr";
			$provenance = "AMF";
		break;

		case sens: //sensibilisation
			$url = "http://www.sensibilisation-securite-routiere.fr";
			$provenance = "SENS";
		break;

		case rec: //recuperer
			$url = "http://www.recuperer-point-permis.fr";
			$provenance = "REC";
		break;

		case rat: //rattrapage
			$url = "http://www.rattrapage-points-permis.fr";
			$provenance = "RAT";
		break;

		case spp: //stagepointpermis
			$url = "http://www.stages-point-permis.fr";
			$provenance = "SPP";
		break;

		case pap: //permis a points
			$url = "http://www.lepermis-a-point.fr";
			$provenance = "PAP";
		break;

		case paca: //paca
			$url = "http://www.prostagespermis-paca.fr";
			$provenance = "PACA";
		break;

		case paris: //paris
			$url = "http://www.prostagespermis-paris.fr";
			$provenance = "PARIS";
		break;

		case rh: //rhone alpes
			$url = "http://www.prostagespermis-rhonealpes.fr";
			$provenance = "RH";
		break;

		case lr: //languedoc
			$url = "http://www.prostagespermis-languedoc.fr";
			$provenance = "LANG";
		break;

		case aqui: //aquitaine
			$url = "http://www.prostagespermis-aquitaine.fr";
			$provenance = "AQUI";
		break;
	}
	return array($url, $provenance);
}
?>