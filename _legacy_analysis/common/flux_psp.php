<?php

function process_flux()
{

	$update_flux_stage = false;
	$update_flux_site = false;

	$fp = fopen ("../common/last_flux_update.txt", "r+");

	$data = fgets ($fp, 50);
	$data = split("-",$data);

	$last_fluxstage_update_time = $data[0];
	$last_fluxsite_update_time = $data[1];

	if ((strtotime("now") - $last_fluxstage_update_time) > 300)  //5 minutes
	{
		$update_flux_stage = true;
		$last_fluxstage_update_time = strtotime("now");
	}

	if ((strtotime("now") - $last_fluxsite_update_time) > 10800)  //3 heures
	{
		$update_flux_site = true;
		$last_fluxsite_update_time = strtotime("now");
	}


	if ($update_flux_site || $update_flux_stage)
	{
		$data = $last_fluxstage_update_time."-".$last_fluxsite_update_time;
		fseek ($fp, 0);
		fputs ($fp, $data);
		fclose ($fp);

		if ($update_flux_site)
		{
			getFluxSites();
		}

		if ($update_flux_stage)
		{
			getFluxStages();
		}
	}
	else
	{
		fclose ($fp);
	}
}


function getFluxSites()
{
	include ("../connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	$sql = "DELETE FROM site_dyn";
	mysql_query($sql);

	mysql_query("ALTER TABLE site_dyn AUTO_INCREMENT = 1");

	$enregistrement_present = false;   // variable servant à mettre ou non une virgule entre les différentes valeurs de centres

	$sql = "INSERT INTO site_dyn (id_externe, id_membre, nom, adresse, code_postal, ville, departement) VALUES ";

	$var = getSitesAllopermis();
	if ($var != "")
	{
		$enregistrement_present = true;
		$sql = $sql.$var;
	}

	$var = getSitesAutoClub();
	if ($var != "")
	{
		if ($enregistrement_present == true)
		{
			$sql .= ",";
		}
		$sql .= $var;

		$enregistrement_present = true;
	}


	mysql_query($sql) or die('Erreur SQL !'.$sql.'<br />'.mysql_error());

	mysql_close($stageconnect);
}


function getFluxStages()
{
	include ("../connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);

	$sql = "DELETE FROM stage_dyn WHERE stage_dyn.date1 > now()";
	mysql_query($sql);

	$last = mysql_query("SELECT MAX(id) FROM stage_dyn") + 1;
	mysql_query("ALTER TABLE stage_dyn AUTO_INCREMENT = $last");

	$enregistrement_present = false;   // variable servant à mettre ou non une virgule entre les différentes valeurs de centres

	$sql = "INSERT INTO stage_dyn (id_externe, id_membre, id_site, date1, date2, debut_am, fin_am, debut_pm, fin_pm, nb_places_allouees, prix, annule, motCle) VALUES ";

	$var = getStagesAllopermis();
	if ($var != "")
	{
		$enregistrement_present = true;
		$sql = $sql.$var;
	}

	$var = getStagesAutoClub();
	if ($var != "")
	{
		if ($enregistrement_present == true)
		{
			$sql .= ",";
		}
		$sql .= $var;

		$enregistrement_present = true;
	}


	mysql_query($sql) or die('Erreur SQL !'.$sql.'<br />'.mysql_error());

	mysql_close($stageconnect);
}


//ALLOPERMIS
//----------
function getSitesAllopermis()
{
	$id_membre = 65; //allopermis

	$email = "contact@prostagespermis.fr";
	$mdp = "Xu9x0G8";
	$qui = md5($email.$mdp);
	$url = "http://www.allopermis.com/xml/lieux.php?qui=".$qui;
	$lieux = simplexml_load_file($url);

	$first = true;
	$sql = "";

	foreach ($lieux->lieu as $lieu)
	{
		if ($first == false)
			$sql = $sql.",";

		$first = false;
		$id = $lieu['id'];

		$adresseComplete = utf8_decode($lieu->adresse);
		//$adresseComplete = str_ireplace("allo permis - ", "", $adresseComplete);
		//$adresseComplete = str_ireplace("allo permis", "", $adresseComplete);
		$adresseComplete = stripAccents($adresseComplete);
		$adresseComplete = addslashes($adresseComplete);
		$nom = $adresseComplete;

		$code_postal = $lieu->cp;

		$ville = utf8_decode($lieu->ville);
		$ville = addslashes($ville);
		$ville = simpleString($ville);

		if ($code_postal == "75014"){$ville = "PARIS-14EME";}
		else if ($code_postal == "75006"){$ville = "PARIS-6EME";}
		else if ($code_postal == "75011"){$ville = "PARIS-11EME";}
		else if ($code_postal == "75012"){$ville = "PARIS-12EME";}
		else if ($code_postal == "75017"){$ville = "PARIS-17EME";}

		$departement = substr($code_postal,0,2);

		$adresse = "";

		$sql = $sql."('$id', \"$id_membre\", \"$nom\", \"$adresse\", \"$code_postal\", \"$ville\", \"$departement\")";
	}

	return $sql;
}

function getStagesAllopermis()
{
	$id_membre = 65; //allopermis

	$tab = array ("1", "2", "3", "4", "5", "6");

	//nouvelle procedure
	$email = "contact@prostagespermis.fr";
	$mdp = "Xu9x0G8";
	$qui = md5($email.$mdp);
	$url = "http://www.allopermis.com/xml/stages.php?qui=".$qui;
	$stages = simplexml_load_file($url);
	//!nouvelle procedure

	$first = true;
	$sql = "";

	foreach ($stages->stage as $stage)
	{
		$aujourdui = date("y-m-d");
		$aujourdui = split("-",$aujourdui);
		$a = $aujourdui[0];
		$m = $aujourdui[1];
		$j = $aujourdui[2];

		$date1 = $stage->date;

		if (strtotime($date1) - strtotime("now") > 0)  //securite: on ne tient pas compte des stages ayant une date passé car déjà archivés dans la base
		{
			$split = split("-",$date1);
			$annee = $split[0];
			$mois = $split[1];
			$jour = $split[2];

			$diff = mktime(0, 0, 0, $split[1], $split[2], $split[0]) - mktime(0, 0, 0, $aujourdui[1], $aujourdui[2], $aujourdui[0]);

			$diff = ($diff / 86400) + 1;

			if ($diff < 60)
			{

				$mc = $tab[rand(0,sizeof($tab)-1)];

				if ($first == false)
					$sql = $sql.",";

				$first = false;

				$id = $stage['id'];
				$id_site = $stage->lieu;

				$date2 = date("Y-m-d", mktime(0, 0, 0, $mois, $jour+1, $annee));

				$nb_places_allouees = $stage->pLibres;
				$prix = $stage->prix;

				if ($stage->etat == 2)
				{
					$annule = 0;
				}
				else
				{
					$annule = 1;
				}

				$debut_am="8:30";
				$fin_am="12:30";
				$debut_pm="13:30";
				$fin_pm="17:30";

				$sql = $sql."(	'$id',
							'$id_membre',
							'$id_site',
							'$date1',
							'$date2',
							'$debut_am',
							'$fin_am',
							'$debut_pm',
							'$fin_pm',
							'$nb_places_allouees',
							'$prix',
							'$annule',
							'$mc')";
			}
		}
	}

	return $sql;
}


//AUTOMOBILE CLUB
//---------------
function getSitesAutoClub()
{
	$sql = "";

	if (!remote_file_exists("http://web.automobileclub.org/www/stages/liste_lieux_pap.asp"))
	{
		return $sql;
	}

	$id_membre = 110; //autoclub

	$lieux = simplexml_load_file("http://web.automobileclub.org/www/stages/liste_lieux_pap.asp");

	$first = true;

	foreach($lieux->xpath('//lieu') as $lieu)
	{
		if ($first == false)
			$sql = $sql.",";

		$first = false;
		$id = $lieu['id'];

		$nom = "Automobile Club";

		$adresse1 = utf8_decode($lieu->adresse1);
		$adresse2 = utf8_decode($lieu->adresse2);

		$adresseComplete = $adresse1." ".$adresse2;
		$adresseComplete = stripAccents($adresseComplete);
		$adresseComplete = addslashes($adresseComplete);
		$adresse = $adresseComplete;

		$code_postal = $lieu->cp;

		$ville = utf8_decode($lieu->ville);
		$ville = addslashes($ville);
		$ville = simpleString($ville);

		if ($code_postal == "75017"){$ville = "PARIS-17EME";}

		$departement = substr($code_postal,0,2);

		$sql = $sql."('$id', \"$id_membre\", \"$nom\", \"$adresse\", \"$code_postal\", \"$ville\", \"$departement\")";

	}

	return $sql;
}

function getStagesAutoClub()
{
	$sql = "";

	if (!remote_file_exists("http://web.automobileclub.org/www/stages/liste_stages_pap_prostagespermis.asp"))
	{
		return $sql;
	}

	$id_membre = 110; //autoclub

	$tab = array ("1", "2", "3", "4", "5", "6");

	$stages = simplexml_load_file("http://web.automobileclub.org/www/stages/liste_stages_pap_prostagespermis.asp");

	$first = true;

	foreach($stages->xpath('//stage') as $stage)
	{

		$aujourdui = date("y-m-d");
		$aujourdui = split("-",$aujourdui);
		$a = $aujourdui[0];
		$m = $aujourdui[1];
		$j = $aujourdui[2];

		$date1 = $stage->date;

		if (strtotime($date1) - strtotime("now") > 0)  //securite: on ne tient pas compte des stages ayant une date passé car déjà archivés dans la base
		{
			$split = split("-",$date1);
			$annee = $split[0];
			$mois = $split[1];
			$jour = $split[2];

			$diff = mktime(0, 0, 0, $split[1], $split[2], $split[0]) - mktime(0, 0, 0, $aujourdui[1], $aujourdui[2], $aujourdui[0]);

			$diff = ($diff / 86400) + 1;

			if ($diff < 60)
			{

				$mc = $tab[rand(0,sizeof($tab)-1)];

				if ($first == false)
					$sql = $sql.",";

				$first = false;

				$id = $stage['id'];
				$id_site = $stage->lieu;

				$date2 = date("Y-m-d", mktime(0, 0, 0, $mois, $jour+1, $annee));

				$nb_places_allouees = $stage->pLibres;
				$prix = $stage->prix;

				if ($stage->etat == 2)
				{
					$annule = 0;
				}
				else
				{
					$annule = 1;
				}

				$debut_am="8:30";
				$fin_am="12:30";
				$debut_pm="13:30";
				$fin_pm="17:30";

				$sql = $sql."(	'$id',
							'$id_membre',
							'$id_site',
							'$date1',
							'$date2',
							'$debut_am',
							'$fin_am',
							'$debut_pm',
							'$fin_pm',
							'$nb_places_allouees',
							'$prix',
							'$annule',
							'$mc')";
			}
		}
	}

	return $sql;
}

function remote_file_exists ($url)
{
	ini_set('allow_url_fopen', '1');

	if (@fclose(@fopen($url, 'r')))
	{
		return true;
	}
	else
	{
		return false;
	}
}

function getmicrotime()
{
      list($usec, $sec) = explode(" ",microtime());
      return ((float)$usec + (float)$sec);
}


function stripAccents($string)
{
	return strtr($string,'aáâaäçêéèëiíîinoóôoöuúuüýyAÁÂAÄÇÊÉEËIÍÎINOÓÔOÖUÚUÜÝ',
'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
}

function simpleString($string)
{
	$string=str_replace("    ","-",$string);
	$string=str_replace("   ","-",$string);
	$string=str_replace("  ","-",$string);
	$string=str_replace(" ","-",$string);
	$string=str_replace("----","-",$string);
	$string=str_replace("---","-",$string);
	$string=str_replace("--","-",$string);
	$string=str_replace("'","-",$string);
	$string=str_replace("-(","-",$string);
	$string=str_replace(")","",$string);

	return strtr($string,'aáâaäçêéèëiíîinoóôoöuúuüýyAÁÂAÄÇÊÉEËIÍÎINOÓÔOÖUÚUÜÝ',
'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
}

?>