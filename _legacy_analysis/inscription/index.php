<!DOCTYPE html>
<html lang="fr">
<head>
  <title>Espace stagiaire - PROStagesPermis</title>
<?php include("../includes/header.php"); ?>

    <meta name="robots" content="noindex,nofollow" />
  </head>

  <body class="paiement">

      <?php include("../includes/topbar.php"); ?>
      <?php include("../includes/nav2.php"); ?>

     <!-- NavLeft
     ================================================== -->

     <!-- CONTENT CENTRAL
     ================================================== -->

     <div id="content" class="background-color-grey">


		<div class="content-middle">
        <div class="container">

        <div class="col-md-9 col-sm-12 col-xs-12 colmiddle">

            <div class="content-paiement-infos">
              <div class="paiement-infos content-relief">

                <div class="paiement-infos-inner">
					<div class="title-paiement-inner font-red">Récapitulatif de votre commande</div>
<?php
$id_stagiaire = "152645";
$tab = getStagiairesInformation($id_stagiaire);
echo affiche_infos_stagiaires($tab);
?>
				</div>
                
              </div>
			</div>
          
        </div>
		
		</div>

		</div>
	</div>

      <?php include("includes/footer.php"); ?>
    
  </body>
  </html>



<?php


function getStagiairesInformation($id_stagiaire)
{
	include ("/home/prostage/connections/stageconnect.php");
	mysql_select_db($database_stageconnect, $stageconnect);
	
	$sql = "SELECT 
				stagiaire.*,
				stage.date1,
				site.ville,
				site.code_postal,
				site.nom,
				site.adresse
				
			FROM 
				stagiaire, stage, site
			WHERE
				stage.id_site = site.id AND
				stagiaire.id_stage = stage.id AND
				stagiaire.id = $id_stagiaire";
    
	$rs = mysql_query($sql, $stageconnect);
    $row = mysql_fetch_assoc($rs);
    $total = mysql_num_rows($rs);
	mysql_close($stageconnect);
	
	return $row;		
}

function affiche_infos_stagiaires($tab)
{
	$cont = '<div>';
	
	$cont .= '<table>';
	$cont .= '<tr><td>'.$tab['nom'].'</td></tr>';
	$cont .= '</table>';
	
	$cont .= '</div>';
	
	return $cont;
}

?>