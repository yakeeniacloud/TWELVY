<?php

require_once('../../common_bootstrap2/config.php');
require_once('includes/functions.php');
$type_page = TYPE_PAGE_STAGES;
$membre = $_SESSION['membre'];
    /*$sql = "select * from membre_voeux where id=".$membre;
    $rs = mysql_query($sql);
    $total = mysql_num_rows($rs);
    if($total >= 1)
        $afficher_voeux = 0;
    else
        $afficher_voeux = 1; */ 
        
    $afficher_voeux = 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Espace affili&eacute;s</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php include("includes/header.php"); ?>
	
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">
	
	<!-- sweet alert 2 -->
	<link rel="stylesheet" href="./dist/sweetalert2.min.css">
	
	<link href="css/custom.css" rel="stylesheet">
	
	<!-- sweet alert 2 -->
	<script src="https://cdn.jsdelivr.net/es6-promise/latest/es6-promise.auto.min.js"></script> <!-- IE support -->
	<script src="./dist/sweetalert2.js"></script>
	
	<script src="js/jquery.form.js"></script> <!-- necessaire pour chargement documents -->
	
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>	
	
  <script>
  $( function() {
    $( document ).tooltip({
      position: {
        my: "center bottom-20",
        at: "center top",
        using: function( position, feedback ) {
          $( this ).css( position );
          $( "<div>" )
            .addClass( "arrow" )
            .addClass( feedback.vertical )
            .addClass( feedback.horizontal )
            .appendTo( this );
        }
      }
    });
  } );
  </script>

</head>

<body class="contentspage">

<?php include("includes/topbar.php"); ?>


<div id="content">

    <?php //include("includes/search_bar_home.php"); ?>


<div class="main-content">

<?php
if (isset($_SESSION['membre'])) { ?>
    <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal" style="display:none" id="voeux"></button>
     <div id="myModal" class="modal fade" role="dialog">
      <div class="modal-dialog">
    
        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
          <div class="modal-body">
            <p>Cher Partenaire,<br><br> 
            Belle et Heureuse Année 2020 !<br>
            Nous vous adressons nos vœux les plus sincères !<br><br>
            L'Equipe ProStagesPermis.
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
          </div>
        </div>
    
      </div>
    </div>
	<div class="row">
	<div class="col-md-10 col-xs-12 col-md-offset-1">
		
		<div class="col-md-10 col-xs-12">
		<div style="border:1px solid grey;padding:5px;border-radius:3px;display: inline-block">
		
		<span style="float:left;padding:8px">FILTRES</span>
		<input title="Date de début de stage" type="text" name="from-date" id="from-date" class="datepicker form-control" style="width:100px;float:left;margin-right:3px"/>
		<input title="Date de fin de stage" type="text" name="to-date" id="to-date" class="datepicker form-control" style="width:100px;float:left;margin-right:3px"/>
		<select title="Visibilité du stage" id="status_filter" class="form-control" style="width:100px;float:left;margin-right:3px">
			<option value="0">Tous</option>
			<option value="1">En ligne</option>
			<option value="2">Hors ligne</option>
		</select>
		<select title="Départements" id="departement_filter" class="form-control" style="width:100px;float:left;margin-right:3px">
			<option value="0">Tous départements</option>
			<?php
			for ($i=1; $i<=95; $i++)
				echo "<option value='$i'>".sprintf("%02d", $i)."</option>";
			?>
		</select>
		<select id="stagiaires_filter" class="form-control" style="width:100px;float:left;margin-right:3px">
			<option value="0">Tous</option>
			<option value="1">Avec stagiaires</option>
			<option value="2">Sans stagiaires</option>
		</select>	
		
		<button class="btn btn-blue btn-filter pull-right" title="Afficher la liste des stages">Rafraichir</button>	
		</div>
		</div>

		<div class="col-md-2 col-xs-12">
		<a class="btn btn-green btn-add pull-right ls-modal" href='popup_ajouter_stage2_m1.php'>AJOUTER UN STAGE</a>
		</div>
	
		<div id="myModal" class="modal fade col-md-10 col-xs-12 col-md-offset-1">
		<div class="modal-dialog" style="width:100%;">
			<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					</div>
					<div class="modal-body">
						<p>Chargement ...</p>
					</div>
			</div>
		</div>
		</div>
	</div>
	</div>

	<div id="liste_stages" class="container"></div>
<?php
} 
else {
	echo "<span style='text-align:center'>Vous n'êtes pas connecté</span>";
}?>

</div><!-- End contentwhite -->

</div> <!-- End content General -->

<?php include("includes/footer.php"); ?>


</body>

<script src="js/loadingoverlay.min.js"></script>

<script type="text/javascript">
	
$(document).ready(function() {
	if(<?php echo $afficher_voeux ?> == 1){
             $('#voeux').click();
             $.ajax({url: 'ajax_voeux.php',
      			 data: {id:<?php echo $membre;?>},
      			 type: 'post',
      			 success: function(output) {
      			 }
      	    });
        }
	$.ajaxSetup({ cache: false });
	
	$(document).ajaxStart(function(){
		$.LoadingOverlay("show");
	});
	$(document).ajaxStop(function(){
		$.LoadingOverlay("hide");
	});	
	
	$(document).on('click', '.btn-filter', function () {		
		affiche_stages();
	});
	
	$("#to-date").datepicker({
		dateFormat: "dd-mm-yy"
		}).datepicker("setDate", "+61");
	
	$("#from-date").datepicker({
		dateFormat: "dd-mm-yy"
		}).datepicker("setDate", "+0");
						
	$.datepicker.setDefaults(
		{
			altField: "#datepicker",
			firstDay: 1,
			closeText: 'Fermer',
			prevText: 'Précédent',
			nextText: 'Suivant',
			currentText: 'Aujourd\'hui',
			monthNames: ['Janvier', 'Fevrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Decembre'],
			monthNamesShort: ['Janv.', 'Févr.', 'Mars', 'Avril', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'],
			dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
			dayNamesShort: ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'],
			dayNamesMin: ['D', 'L', 'M', 'M', 'J', 'V', 'S'],
			weekHeader: 'Sem.',
			dateFormat: 'dd-mm-yy'
		}
	);
	
	affiche_stages();
	
	$(document).on('click', '.fa-download', function () {
		
			var id_stage = jQuery(this).attr("id_stage");
			var div = "#hidden_tr_"+id_stage;
			$(div).slideToggle("slow");	
	});
	
	$(document).on('click', '.fa-users', function () {
		
		var id_stage = jQuery(this).attr("id_stage");
		
		$.ajax({ url: 'ajax_session.php',
							 data: {session: 'id_stage', val: id_stage},
							 type: 'post',
							 success: function(output) {
									window.location.href = "stagiaires.php";
							 }
		});			
	});
	
	$(document).on('click', '.fa-user-plus', function () {		
		
		var id_stage = jQuery(this).attr("id_stage");
		
		swal({
		  title: 'Ajout stagiaire externe',
		  html: "<input id='nom' class='swal2-input' placeHolder='Nom'>"+"<input id='prenom' placeHolder='Prénom' class='swal2-input'>"+"<input id='adresse' placeHolder='Adresse' class='swal2-input'>"+"<input id='code_postal' placeHolder='Code postal' class='swal2-input'>"+"<input id='ville' placeHolder='Ville' class='swal2-input'>"+"<input id='date_naissance' placeHolder='Date naissance' class='swal2-input'>"+"<input id='lieu_naissance' placeHolder='Lieu naissance' class='swal2-input'>"+"<input id='tel' placeHolder='Téléphone' class='swal2-input'>"+"<input id='email' placeHolder='Email' class='swal2-input'>"+"<input id='paiement' placeHolder='Prix' class='swal2-input'>"+"<select id='receipt' placeHolder='Paiement reçu' class='swal2-input'><option value='0'>Paiement en attente</option><option value='1'>Paiement reçu</option></select>"+"<select id='cas' placeHolder='Cas' class='swal2-input'><option value='1'>Cas 1</option><option value='2'>Cas 2</option><option value='3'>Cas 3</option><option value='4'>Cas 4</option></select>"+"<input id='date_infraction' placeHolder='Date infraction si Cas 2' class='swal2-input'>"+"<input id='heure_infraction' placeHolder='Heure infraction si cas 2' class='swal2-input'>"+"<input id='lieu_infraction' placeHolder='Lieu infraction si Cas 2' class='swal2-input'>"+"<input id='num_permis' placeHolder='Numéro permis' class='swal2-input'>"+"<input id='date_permis' placeHolder='Date permis' class='swal2-input'>"+"<input id='lieu_permis' placeHolder='Lieu permis' class='swal2-input'>"+"<select id='provenance' class='swal2-input'><option value='0' disabled selected>Provenance</option><option value='1'>ProstagesPermis</option><option value='2'>Autre plateforme</option><option value='3'>Réseau propre</option></select>",
		  showCancelButton: true,
		  confirmButtonText: 'OK',
		  cancelButtonText: 'FERMER',
		  preConfirm: function (resolve, reject) {

				return new Promise(function (resolve, reject) {
					
					if (!parseInt($('#nom').val().length) || !parseInt($('#prenom').val().length)) {
						reject('Les noms et prénoms sont obligatoires');
					}												
					else {
						resolve();
					}				
				});
			}
		}).then(function(result) {
			if (result) {
				
				var nom 	= $('#nom').val();
				var prenom 	= $('#prenom').val();
				var adresse = $('#adresse').val();
				var code_postal = $('#code_postal').val();
				var ville 	= $('#ville').val();
				var date_naissance 	= $('#date_naissance').val();
				var lieu_naissance 	= $('#lieu_naissance').val();
				var tel 	= $('#tel').val();
				var email 	= $('#email').val();
				var paiement = $('#paiement').val();
				var receipt = $('#receipt').val();
				var date_infraction = $('#date_infraction').val();
				var heure_infraction = $('#heure_infraction').val();
				var lieu_infraction = $('#lieu_infraction').val();
				var cas 	= $('#cas').val();
				var num_permis 	= $('#num_permis').val();
				var date_permis 	= $('#date_permis').val();
				var lieu_permis 	= $('#lieu_permis').val();
				var provenance 	= $('#provenance').val();

				$.ajax({ url: 'ajax_functions.php',
							 data: {action: 'ajout_stagiaire_externe', id_stage: id_stage, nom: nom, prenom: prenom, adresse: adresse, code_postal: code_postal, ville: ville, date_naissance:date_naissance, lieu_naissance:lieu_naissance, tel:tel, email:email, cas: cas, num_permis: num_permis, date_permis: date_permis, lieu_permis: lieu_permis, provenance: provenance, paiement: paiement, receipt: receipt, date_infraction: date_infraction, heure_infraction: heure_infraction, lieu_infraction: lieu_infraction},
							 type: 'post',
							 success: function(output) {	
							 }
				});
			}
		})	
	});
	
	$(document).on('click', '.fa-lightbulb', function () {
		
		var ethis = $(this);
		var id_stage = jQuery(this).attr("id_stage");
		
		$.ajax({ url: 'ajax_functions.php',
					 data: {action: 'update_diffusion', id_stage: id_stage},
					 type: 'post',
					 success: function(output) {
						/*
						if (ethis.hasClass('online')) {
							ethis.removeClass( "online" ).addClass( "offline" );
						}
						else {	
							ethis.removeClass( "offline" ).addClass( "online" );
						}
						*/
						
						affiche_stages();
					 }
		});
	});
	
	$(document).on('click', '.fa-times-circle', function () {
		
		var nb_inscrits = jQuery(this).attr("nb_inscrits");
		
		if (!$(this).hasClass('disabled')) {
			var id_stage = jQuery(this).attr("id_stage");

			swal({
			  type: 'info', 
			  text: 'Votre stage va être mis hors ligne, Etes-vous sur de vouloir l\'annuler ?',
			  showCancelButton: true,
			  confirmButtonText: 'OUI',
			  cancelButtonText: 'NON'
			}).then(function(result) {

				$.ajax({ url: 'ajax_functions.php',
							 data: {action: 'delete_stage', id_stage: id_stage},
							 type: 'post',
							 success: function(output) {
								//if (parseInt(nb_inscrits)) { 
								if (1) {
									swal({
									  type: 'info', 
									  text: 'Votre stage est désormais hors ligne .Pour supprimer les stagiaires inscrits, accédez à la liste des candidats (en cliquant sur le Picto correspondant) et supprimez les un par un',
									  showCancelButton: false,
									  showConfirmButton: true,
									  confirmButtonText: 'OK'
									}).then(function(result) {
											affiche_stages();
									});
								}
								else
									affiche_stages();
							 }
				});
			})
		}
	});	
		
	$(document).on('click', '.hours_container_debut_am', function () {
		
		var id_stage = jQuery(this).attr("id_stage");
		
		if ($(this).find('.select_hours').length != 1) {  //vérifie l'existence de l'élément
			
			var selected;
			var hour = $(this).find('.debut_am').html();
			
			$( ".debut_am" ).show();
			$( ".select_hours" ).remove();
			$(this).find('.debut_am').hide();
			
			var ethis = jQuery(this);
			
			var sel = '<select class="select_hours form-control" style="padding:2px;font-size:12px;height:25px" id_stage="'+id_stage+'" hour_type="debut_am">';
			
			var hour_tmp = '7:00';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';
			
			hour_tmp = '7:15';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';

			hour_tmp = '7:30';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';

			hour_tmp = '7:45';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';

			hour_tmp = '8:00';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';	

			hour_tmp = '8:15';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';

			hour_tmp = '8:30';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';	

			hour_tmp = '8:45';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';

			hour_tmp = '9:00';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';				
			
			sel += '</select>';
			
			$( this ).append( sel );
		}
		return false;		
	});	

	$(document).on('click', '.hours_container_fin_am', function () {
		
		var id_stage = jQuery(this).attr("id_stage");
		
		if ($(this).find('.select_hours').length != 1) {  //vérifie l'existence de l'élément
			
			var selected;
			var hour = $(this).find('.fin_am').html();
			
			$( ".fin_am" ).show();
			$( ".select_hours" ).remove();
			$(this).find('.fin_am').hide();
			
			var ethis = jQuery(this);
			
			var sel = '<select class="select_hours form-control" style="padding:2px;font-size:12px;height:25px" id_stage="'+id_stage+'" hour_type="fin_am">';
			
			var hour_tmp = '11:30';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';
			
			hour_tmp = '11:45';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';

			hour_tmp = '12:00';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';

			hour_tmp = '12:15';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';

			hour_tmp = '12:30';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';	

			hour_tmp = '12:45';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';

			hour_tmp = '13:00';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';				
			
			sel += '</select>';
			
			$( this ).append( sel );
		}
		return false;		
	});	

	$(document).on('click', '.hours_container_debut_pm', function () {
		
		var id_stage = jQuery(this).attr("id_stage");
		
		if ($(this).find('.select_hours').length != 1) {  //vérifie l'existence de l'élément
			
			var selected;
			var hour = $(this).find('.debut_pm').html();
			
			$( ".debut_pm" ).show();
			$( ".select_hours" ).remove();
			$(this).find('.debut_pm').hide();
			
			var ethis = jQuery(this);
			
			var sel = '<select class="select_hours form-control" style="padding:2px;font-size:12px;height:25px" id_stage="'+id_stage+'" hour_type="debut_pm">';
			
			var hour_tmp = '13:00';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';
			
			hour_tmp = '13:15';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';

			hour_tmp = '13:30';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';

			hour_tmp = '13:45';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';

			hour_tmp = '14:00';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';	

			hour_tmp = '14:15';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';

			hour_tmp = '14:30';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';				
			
			sel += '</select>';
			
			$( this ).append( sel );
		}
		return false;		
	});

	$(document).on('click', '.hours_container_fin_pm', function () {
		
		var id_stage = jQuery(this).attr("id_stage");
		
		if ($(this).find('.select_hours').length != 1) {  //vérifie l'existence de l'élément
			
			var selected;
			var hour = $(this).find('.fin_pm').html();
			
			$( ".fin_pm" ).show();
			$( ".select_hours" ).remove();
			$(this).find('.fin_pm').hide();
			
			var ethis = jQuery(this);
			
			var sel = '<select class="select_hours form-control" style="padding:2px;font-size:12px;height:25px" id_stage="'+id_stage+'" hour_type="fin_pm">';
			
			var hour_tmp = '16:00';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';
			
			hour_tmp = '16:15';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';

			hour_tmp = '16:30';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';

			hour_tmp = '16:45';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';

			hour_tmp = '17:00';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';	

			hour_tmp = '17:15';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';

			hour_tmp = '17:30';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';	

			hour_tmp = '17:45';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';	

			hour_tmp = '18:00';
			selected = hour == hour_tmp ? "selected" : "";
			sel += '<option value="'+hour_tmp+'" '+selected+'>'+hour_tmp+'</option>';				
			
			sel += '</select>';
			
			$( this ).append( sel );
		}
		return false;		
	});		
	
	$(document).on('click', '.price_container', function () {
		
		var id_stage = jQuery(this).attr("id_stage");
		
		if ($(this).find('.select_price').length != 1) {  //vérifie l'existence de l'élément
			
			var selected;
			var prix = $(this).find('.price').html();
			prix = parseInt(prix);
			
			$( ".price" ).show();
			$( ".select_price" ).remove();
			$(this).find('.price').hide();
			
			var ethis = jQuery(this);
			
			var sel = '<select class="select_price form-control" id_stage="'+id_stage+'">';
			for (i=1; i<=500; i++) {
				selected = prix == i ? "selected" : "";
				sel += '<option value="'+i+'" '+selected+'>'+i+'</option>';
			}
			sel += '</select>';
			
			$( this ).append( sel );
		}
		return false;		
	});
	
	$(document).on('click', '.place_container', function () {
		
		var id_stage = jQuery(this).attr("id_stage");
		
		if ($(this).find('.select_place').length != 1) {  //vérifie l'existence de l'élément
			
			var selected;
			var place = $(this).find('.place').html();
			place = parseInt(place);
			
			$( ".place" ).show();
			$( ".select_place" ).remove();
			$(this).find('.place').hide();
			
			var ethis = jQuery(this);
			
			var sel = '<select class="select_place form-control" id_stage="'+id_stage+'">';
			for (i=1; i<=20; i++) {
				selected = place == i ? "selected" : "";
				sel += '<option value="'+i+'" '+selected+'>'+i+'</option>';
			}
			sel += '</select>';
			
			$( this ).append( sel );
		}
		return false;		
	});

	$(document).on('click', '.bafm_container', function () {
		
		var id_stage = jQuery(this).attr("id_stage");
		var id_bafm = jQuery(this).attr("id_bafm");
		var obj;
		
		if ($(this).find('.select_bafm').length != 1) {  //vérifie l'existence de l'élément			
			
			$.ajax({ url: 'ajax_functions.php',
						 data: {action: 'liste_bafm'},
						 type: 'post',
						 async: false,
						 success: function(output) {
							obj = output;
						 },
						 dataType:"json"
			});			
			
			
			var selected ='';
			var bafm = $(this).find('.bafm').html();
			
			$( ".bafm" ).show();
			$( ".select_bafm" ).remove();
			$(this).find('.bafm').hide();
			
			var ethis = jQuery(this);
			
			var sel = '<select class="select_bafm form-control" id_stage="'+id_stage+'">';
			sel += '<option value=0>Bafm à définir</option>';
			$.each(obj, function() {
				selected = id_bafm == this['id'] ? "selected" : "";
				sel += '<option value="'+this['id']+'" '+selected+'>'+this['nom']+' '+this['prenom']+'</option>';
			});			
			
			sel += '</select>';
			
			
			$( this ).append( sel );
		}
		return false;		
	});	

	$(document).on('click', '.psy_container', function () {
		
		var id_stage = jQuery(this).attr("id_stage");
		var id_psy = jQuery(this).attr("id_psy");
		var obj;
		
		if ($(this).find('.select_psy').length != 1) {  //vérifie l'existence de l'élément			
				
			$.ajax({ url: 'ajax_functions.php',
						 data: {action: 'liste_psy'},
						 type: 'post',
						 async: false,
						 success: function(output) {
							obj = output;
						 },
						 dataType:"json"
			});			
			
			var selected ='';
			var psy = $(this).find('.psy').html();
			
			$( ".psy" ).show();
			$( ".select_psy" ).remove();
			$(this).find('.psy').hide();
			
			var ethis = jQuery(this);
			
			var sel = '<select class="select_psy form-control" id_stage="'+id_stage+'">';
			sel += '<option value=0>Psy à définir</option>';
			$.each(obj, function() {
				selected = id_psy == this['id'] ? "selected" : "";
				sel += '<option value="'+this['id']+'" '+selected+'>'+this['nom']+' '+this['prenom']+'</option>';
			});			
			
			sel += '</select>';
			
			$( this ).append( sel );
		}
		return false;		
	});		

	$(document).on('click', '.contentspage', function () {		
		$( ".select_price" ).remove();
		$( ".price" ).show();
		
		$( ".select_hours" ).remove();
		$( ".debut_am" ).show();
		$( ".fin_am" ).show();
		$( ".debut_pm" ).show();
		$( ".fin_pm" ).show();	

		$( ".select_bafm" ).remove();
		$( ".bafm" ).show();

		$( ".select_psy" ).remove();
		$( ".psy" ).show();

		$( ".select_place" ).remove();
		$( ".place" ).show();		
		
	});
	
	$(document).on('change', '.select_price', function () {		
		
		var id_stage = jQuery(this).attr("id_stage");
		var ethis = this;
		var ethis2 = $(this);

		$.ajaxSetup({ cache: false });	
		$.ajax({ url: 'ajax_functions.php',
					 data: {action: 'update_prix', id_stage: id_stage, prix: ethis.value},
					 type: 'post',
					 success: function(output) {
						ethis2.closest('.price_container').find('.price').html(ethis.value);
						ethis2.closest('.price_container').find('.price').show();					
						ethis2.remove();
					 }
		});
	});
	
	$(document).on('change', '.select_place', function () {		
		
		var id_stage = jQuery(this).attr("id_stage");
		var ethis = this;
		var ethis2 = $(this);

		$.ajaxSetup({ cache: false });	
		$.ajax({ url: 'ajax_functions.php',
					 data: {action: 'update_places', id_stage: id_stage, place: ethis.value},
					 type: 'post',
					 success: function(output) {
						ethis2.closest('.place_container').find('.place').html(ethis.value);
						ethis2.closest('.place_container').find('.place').show();					
						ethis2.remove();
					 }
		});
	});
	
	$(document).on('change', '.select_bafm', function () {		
		
		var id_stage = jQuery(this).attr("id_stage");
		var ethis = this;
		var ethis2 = $(this);

		$.ajaxSetup({ cache: false });	
		$.ajax({ url: 'ajax_functions.php',
					 data: {action: 'update_bafm', id_stage: id_stage, bafm: ethis.value},
					 type: 'post',
					 success: function(output) {
						var val = ethis2.find(":selected").text();
						ethis2.closest('.bafm_container').find('.bafm').html(val);
						ethis2.closest('.bafm_container').find('.bafm').show();					
						ethis2.remove();
					 }
		});
	});
	
	$(document).on('change', '.select_psy', function () {		
		
		var id_stage = jQuery(this).attr("id_stage");
		var ethis = this;
		var ethis2 = $(this);

		$.ajaxSetup({ cache: false });	
		$.ajax({ url: 'ajax_functions.php',
					 data: {action: 'update_psy', id_stage: id_stage, psy: ethis.value},
					 type: 'post',
					 success: function(output) {
						var val = ethis2.find(":selected").text();
						ethis2.closest('.psy_container').find('.psy').html(val);
						ethis2.closest('.psy_container').find('.psy').show();					
						ethis2.remove();
					 }
		});
	});
	
	$(document).on('change', '.select_hours', function () {		
		
		var id_stage = jQuery(this).attr("id_stage");
		var hour_type = jQuery(this).attr("hour_type");
		var hour_type_class = "."+jQuery(this).attr("hour_type");
		var ethis = this;
		var ethis2 = $(this);
		var hours_container = ".hours_container_"+hour_type;

		$.ajaxSetup({ cache: false });	
		$.ajax({ url: 'ajax_functions.php',
					 data: {action: 'update_hour', id_stage: id_stage, hour: ethis.value, hour_type:hour_type},
					 type: 'post',
					 success: function(output) {
						ethis2.closest(hours_container).find(hour_type_class).html(ethis.value);
						ethis2.closest(hours_container).find(hour_type_class).show();					
						ethis2.remove();
					 }
		});
	});
	
	$(document).on('click', '.ls-modal', function (e) {
	  e.preventDefault();
	  $('#myModal').modal('show').find('.modal-body').load($(this).attr('href'));
	});
	

});


function affiche_stages() {
	
	var first_date 	= $('#from-date').val();
	first_date 		= first_date.split('-');
	first_date 		= first_date[2] + '-' + first_date[1] + '-' + first_date[0].slice(-2);
	
	var end_date 	= $('#to-date').val();
	end_date 		= end_date.split('-');
	end_date 		= end_date[2] + '-' + end_date[1] + '-' + end_date[0].slice(-2);	
	
	var departement = $('#departement_filter').val();
	
	var stagiaires = $('#stagiaires_filter').val();
	
	var status = $('#status_filter').val();
	
	$.ajax({ url: 'ajax_functions.php',
				 data: {action: 'affiche_stages', first_date: first_date, end_date: end_date, stagiaires: stagiaires, departement: departement, status: status},
				 type: 'post',
				 success: function(output) {
					$("#liste_stages").html(output);
				 }
	});
}


</script>

</html>


