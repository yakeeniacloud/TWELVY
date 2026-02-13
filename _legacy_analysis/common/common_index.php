<?php
function index($site)
{
	$sql_plus1 = "";
	$sql_plus2 = "";
	switch($site)
	{
		case psp: //psp
		break;
		case amf: //amf
		break;
		case sens: //sensibilisation
		break;
		case rec: //recuperer
		break;
		case rat: //rattrapage
		break;
		case spp: //stagepointpermis
		break;
		case pap: //permis a points
		break;
		case paca: //paca
			$sql_plus1 = " AND (site.departement = 04 OR
					site.departement = 05 OR
					site.departement = 06 OR
					site.departement = 13 OR
					site.departement = 83 OR
					site.departement = 84) ";
			$sql_plus2 = " AND (site_dyn.departement = 04 OR
					site_dyn.departement = 05 OR
					site_dyn.departement = 06 OR
					site_dyn.departement = 13 OR
					site_dyn.departement = 83 OR
					site_dyn.departement = 84) ";
		break;
		case paris: //paris
			$sql_plus1 = " AND (site.departement = 75 OR
					site.departement = 77 OR
					site.departement = 78 OR
					site.departement = 91 OR
					site.departement = 92 OR
					site.departement = 93 OR
					site.departement = 94 OR
					site.departement = 95) ";
			$sql_plus2 = " AND (site_dyn.departement = 75 OR
					site_dyn.departement = 77 OR
					site_dyn.departement = 78 OR
					site_dyn.departement = 91 OR
					site_dyn.departement = 92 OR
					site_dyn.departement = 93 OR
					site_dyn.departement = 94 OR
					site_dyn.departement = 95) ";
		break;
		case rh: //rhone alpes
			$sql_plus1 = " AND (site.departement = 01 OR
					site.departement = 07 OR
					site.departement = 26 OR
					site.departement = 38 OR
					site.departement = 42 OR
					site.departement = 69 OR
					site.departement = 73 OR
					site.departement = 74) ";
			$sql_plus2 = " AND (site_dyn.departement = 01 OR
					site_dyn.departement = 07 OR
					site_dyn.departement = 26 OR
					site_dyn.departement = 38 OR
					site_dyn.departement = 42 OR
					site_dyn.departement = 69 OR
					site_dyn.departement = 73 OR
					site_dyn.departement = 74) ";
		break;
		case lr: //languedoc
			$sql_plus1 = " AND (site.departement = 11 OR
					site.departement = 30 OR
					site.departement = 34 OR
					site.departement = 48 OR
					site.departement = 66) ";
			$sql_plus2 = " AND (site_dyn.departement = 11 OR
					site_dyn.departement = 30 OR
					site_dyn.departement = 34 OR
					site_dyn.departement = 48 OR
					site_dyn.departement = 66) ";
		break;
		case aqui: //aquitaine
			$sql_plus1 = " AND (site.departement = 24 OR
					site.departement = 33 OR
					site.departement = 40 OR
					site.departement = 47 OR
					site.departement = 64) ";
			$sql_plus2 = " AND (site_dyn.departement = 24 OR
					site_dyn.departement = 33 OR
					site_dyn.departement = 40 OR
					site_dyn.departement = 47 OR
					site_dyn.departement = 64) ";
		break;
	}
	$query_rsSite = "";
	$query_rsStage = "";
	//liste des sites
	//---------------
	$query_rsSite =	"SELECT DISTINCT site.ville, site.departement FROM site, stage WHERE
						stage.id_site = site.id AND
						stage.date1 > now() AND
						stage.annule = 0 AND
						stage.nb_inscrits < stage.nb_places_allouees".$sql_plus1."
						UNION
					SELECT DISTINCT ville, departement FROM site_dyn, stage_dyn WHERE
						stage_dyn.id_site = site_dyn.id_externe AND
						stage_dyn.date1 > now() AND
						stage_dyn.annule = 0 AND
						stage_dyn.nb_places_allouees > 0".$sql_plus2."
						ORDER BY ville ASC";
	//liste des stages
	//----------------
	$query_rsStage =
			"SELECT stage.id, stage.nb_preinscrits, stage.nb_inscrits, stage.nb_places_allouees FROM stage, site WHERE
				stage.date1 > now() AND
				stage.annule = 0 AND
				stage.nb_inscrits < stage.nb_places_allouees AND
				stage.id_site = site.id
				".$sql_plus1."
				UNION
			SELECT stage_dyn.id, stage_dyn.nb_preinscrits, stage_dyn.nb_inscrits,
				stage_dyn.nb_places_allouees FROM stage_dyn, site_dyn WHERE
				stage_dyn.id_site = site_dyn.id_externe AND
				stage_dyn.date1 > now() AND
				stage_dyn.annule = 0 AND
				stage_dyn.nb_places_allouees > 0".$sql_plus2;
	return array($query_rsSite, $query_rsStage);
}
?>