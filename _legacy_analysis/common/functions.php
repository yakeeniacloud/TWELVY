<?php

function datefr($date)

{

	$split = split("-",$date);

	$annee = $split[0];

	$mois = $split[1];

	$jour = $split[2];

	return "$jour"."-"."$mois"."-"."$annee";

}



function MySQLDateToExplicitDate($MyDate, $WeekDayOn=1, $YearOn=1)

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


function MySQLDateToExplicitDate2($MyDate, $WeekDayOn=1, $YearOn=1)

{

	$MyMonths = array("/01/", "/02/", "/03/", "/04/", "/05/", "/06/",

			"/07/", "/08/", "/09/", "/10/", "/11/", "/12/");



	$MyDays = array("Dim", "Lun", "Mar", "Mer", "Jeu",

					  "Ven", "Sam");



	$DF=explode('-',$MyDate);

	$TheDay=getdate(mktime(0,0,0,$DF[1],$DF[2],$DF[0]));

	$MyDate=$DF[2].$MyMonths[$DF[1]-1];



	if($WeekDayOn){$MyDate=$MyDays[$TheDay["wday"]]." ".$MyDate;}

	if($YearOn){$MyDate.="".$DF[0];}



	return $MyDate;

}




function getDepartement($val)

{

	switch ($val)

	{

		case 1:	return "Ain 01"; break;

		case 2:	return "Aisne 02";	break;

		case 3:	return "Allier 03";break;

		case 4:	return "Alpes de Haute Provence 04";break;

		case 5:	return "Hautes Alpes 05";break;

		case 6:	return "Alpes Maritimes 06";break;

		case 7:	return "Ardeche 07";break;

		case 8:	return "Ardennes 08";break;

		case 9:	return "Ariege 09";break;

		case 10:return "Aube 10";break;

		case 11:return "Aude 11";break;

		case 12:return "Aveyron 12";break;

		case 13:return "Bouches du Rhone 13";break;

		case 14:return "Calvados 14";break;

		case 15:return "Cantal 15";break;

		case 16:return "Charente 16";break;

		case 17:return "Charente Maritime 17";break;

		case 18:return "Cher 18";break;

		case 19:return "Correze 19";break;

		case 20:return "Corse 20 (2A-2B)";break;

		case 21:return "Cote d'Or 21";break;

		case 22:return "Cotes d'Armor 22";break;

		case 23:return "Creuse 23";break;

		case 24:return "Dordogne 24";break;

		case 25:return "Doubs 25"; break;

		case 26:return "Drome 26";	break;

		case 27:return "Eure 27";break;

		case 28:return "Eure et Loire 28";break;

		case 29:return "Finistere 29";break;

		case 30:return "Gard 30";break;

		case 31:return "Haute Garonne 31";break;

		case 32:return "Gers 32";break;

		case 33:return "Gironde 33";break;

		case 34:return "Herault 34";break;

		case 35:return "Ile et Vilaine 35";break;

		case 36:return "Indre 36";break;

		case 37:return "Indre et Loire 37";break;

		case 38:return "Isere 38";break;

		case 39:return "Jura 39";break;

		case 40:return "Landes 40";break;

		case 41:return "Loire et Cher 41";break;

		case 42:return "Loire 42";break;

		case 43:return "Haute Loire 43";break;

		case 44:return "Loire Atlantique 44";break;

		case 45:return "Loiret 45";break;

		case 46:return "Lot 46";break;

		case 47:return "Lot et Garonne 47";break;

		case 48:return "Lozere 48";break;

		case 49:return "Maine et Loire 49"; break;

		case 50:return "Manche 50";	break;

		case 51:return "Marne 51";break;

		case 52:return "Haute Marne 52";break;

		case 53:return "Mayenne 53";break;

		case 54:return "Meurthe et Moselle 54";break;

		case 55:return "Meuse 55";break;

		case 56:return "Morbihan 56";break;

		case 57:return "Moselle 57";break;

		case 58:return "Nievre 58";break;

		case 59:return "Nord 59";break;

		case 60:return "Oise 60";break;

		case 61:return "Orne 61";break;

		case 62:return "Pas de Calais 62";break;

		case 63:return "Puy de Dome 63";break;

		case 64:return "Pyrenees Atlantiques 64";break;

		case 65:return "Hautes Pyrenees 65";break;

		case 66:return "Pyrenees Orientales 66";break;

		case 67:return "Bas Rhin 67";break;

		case 68:return "Haut Rhin 68";break;

		case 69:return "Rhone 69";break;

		case 70:return "Haute Saone 70";break;

		case 71:return "Saone et Loire 71";break;

		case 72:return "Sarthe 72";break;

		case 73:return "Savoie 73";break;

		case 74:return "Haute Savoie 74";break;

		case 75:return "Paris 75";break;

		case 76:return "Seine Maritime 76";break;

		case 77:return "Seine et Marne 77";break;

		case 78:return "Yvelines 78";break;

		case 79:return "Deux Sevres 79"; break;

		case 80:return "Somme 80";	break;

		case 81:return "Tarn 81";break;

		case 82:return "Tarn et Garonne 82";break;

		case 83:return "Var 83";break;

		case 84:return "Vaucluse 84";break;

		case 85:return "Vendee 85";break;

		case 86:return "Vienne 86";break;

		case 87:return "Haute Vienne 87";break;

		case 88:return "Vosges 88";break;

		case 89:return "Yonne 89";break;

		case 90:return "Territoire de Belfort 90";break;

		case 91:return "Essonne 91";break;

		case 92:return "Hauts de Seine 92";break;

		case 93:return "Seine Saint Denis 93";break;

		case 94:return "Val de Marne 94";break;

		case 95:return "Val d'Oise 95";break;

	}

}

function getUrlDepartement($dep)
{
switch ($dep)
{
   case 1: return "ain"; break;
   case 2: return "aisne"; break;
   case 3: return "allier"; break;
   case 4: return "alpeshauteprovence"; break;
   case 5: return "hautesalpes"; break;
   case 6: return "alpesmaritimes"; break;
   case 7: return "ardeche"; break;
   case 8: return "ardennes"; break;
   case 9: return "ariege"; break;
   case 10: return "aube"; break;
   case 11: return "aude"; break;
   case 12: return "aveyron"; break;
   case 13: return "bouchesdurhone"; break;
   case 14: return "calvados"; break;
   case 15: return "cantal"; break;
   case 16: return "charente"; break;
   case 17: return "charentemaritime"; break;
   case 18: return "cher"; break;
   case 19: return "correze"; break;
   case 19: return "corse"; break;
   case 21: return "cotedor"; break;
   case 22: return "cotesdarmor"; break;
   case 23: return "creuse"; break;
   case 24: return "dordogne"; break;
   case 25: return "doubs"; break;
   case 26: return "drome"; break;
   case 27: return "eure"; break;
   case 28: return "eureetloire"; break;
   case 29: return "finistere"; break;
   case 30: return "gard"; break;
   case 31: return "hautegaronne"; break;
   case 32: return "gers"; break;
   case 33: return "gironde"; break;
   case 34: return "herault"; break;
   case 35: return "ileetvilaine"; break;
   case 36: return "indre"; break;
   case 37: return "indreetloire"; break;
   case 38: return "isere"; break;
   case 39: return "jura"; break;
   case 40: return "landes"; break;
   case 41: return "loiretcher"; break;
   case 42: return "loire"; break;
   case 43: return "hauteloire"; break;
   case 44: return "loireatlantique"; break;
   case 45: return "loiret"; break;
   case 46: return "lot"; break;
   case 47: return "lotetgaronne"; break;
   case 48: return "lozere"; break;
   case 49: return "maineetloire"; break;
   case 50: return "manche"; break;
   case 51: return "marne"; break;
   case 52: return "hautemarne"; break;
   case 53: return "mayenne"; break;
   case 54: return "meurtheetmoselle"; break;
   case 55: return "meuse"; break;
   case 56: return "morbihan"; break;
   case 57: return "moselle"; break;
   case 58: return "nievre"; break;
   case 59: return "nord"; break;
   case 60: return "oise"; break;
   case 61: return "orne"; break;
   case 62: return "pasdecalais"; break;
   case 63: return "puydedome"; break;
   case 64: return "pyreneesatlantiques"; break;
   case 65: return "hautespyrenees"; break;
   case 66: return "pyreneesorientales"; break;
   case 67: return "basrhin"; break;
   case 68: return "hautrhin"; break;
   case 69: return "rhone"; break;
   case 70: return "hautesaone"; break;
   case 71: return "saoneetloire"; break;
   case 72: return "sarthe"; break;
   case 73: return "savoie"; break;
   case 74: return "hautesavoie"; break;
   case 75: return "paris"; break;
   case 76: return "seinemaritime"; break;
   case 77: return "seineetmarne"; break;
   case 78: return "yvelines"; break;
   case 79: return "deuxsevres"; break;
   case 80: return "somme"; break;
   case 81: return "tarn"; break;
   case 82: return "tarnetgaronne"; break;
   case 83: return "var"; break;
   case 84: return "vaucluse"; break;
   case 85: return "vendee"; break;
   case 86: return "vienne"; break;
   case 87: return "hautevienne"; break;
   case 88: return "vosges"; break;
   case 89: return "yonne"; break;
   case 90: return "territoirebelfort"; break;
   case 91: return "essonne"; break;
   case 92: return "hautsdeseine"; break;
   case 93: return "seinesaintdenis"; break;
   case 94: return "valdemarne"; break;
   case 95: return "valdoise"; break;
}
}



function switchcolor($site = psp)
{
	static $col;
	if ($site == psp || $site == paris || $site == paca || $site == rh || $site == lr || $site == aqui || $site == pap)
	{
		$couleur1 = "#E9DBAB";
		$couleur2 = "#EFD994";
	}
	else
	{
		$couleur1 = "#C3E7FD";
		$couleur2 = "#B5E7FE";
	}

	if ($col == $couleur1)
	{
		$col = $couleur2;
	}
    else
    {
		$col = $couleur1;
	}
    return $col;
 }

function switchcolorAffilie()
{
	static $col;

	$couleur1 = "#E2E9E8";
	$couleur2 = "#C8D7DD";

	if ($col == $couleur1)
	{
		$col = $couleur2;
	}
    else
    {
		$col = $couleur1;
	}
    return $col;
 }

function filter($chaine)
{
       $chaine = str_replace ( "&agrave;" , "a" , $chaine );
	   $chaine = str_replace ( "&egrave;" , "e" , $chaine );
	   $chaine = str_replace ( "&eacute;" , "e" , $chaine );
	   $chaine = str_replace ( "&eacute;" , "e" , $chaine );
	   $chaine = str_replace ( "&ocirc;" , "o" , $chaine );
	   $chaine = str_replace ( "&icirc;" , "i" , $chaine );

	   return $chaine;
}


?>