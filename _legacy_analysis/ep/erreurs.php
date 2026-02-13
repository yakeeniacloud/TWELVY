<?php

require_once('../../common_bootstrap2/config.php');
require_once('includes/functions.php');
$type_page = TYPE_PAGE_ERREURS;

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
	<!-- <script src="https://code.jquery.com/jquery-1.12.4.js"></script> -->	
	
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
  
	$(function() {
		
		$( "#search_stagiaire" ).autocomplete({
			delay: 500,
			minLength: 3,
			source: "autocomplete_stagiaire.php",                     
			select: function(event, ui) {  
				$( "#search_stagiaire" ).val(ui.item.label);  
				$( "#search_stagiaire_hidden" ).val(ui.item.value); 
				
				affiche_stagiaires();

				return false;  
			}                           
		});
	});  
  
	</script>
  
 	<style>
	.swal2-modal .swal2-textarea {
		height: 200px;
	}
	
	</style>

</head>

<body class="contentspage">

<?php include("includes/topbar.php"); ?>


<div id="content">

    <?php //include("includes/search_bar_home.php"); ?>


<div class="main-content">

<?php
if (isset($_SESSION['membre'])) { ?>

	<div class="row">
	<div class="col-md-10 col-xs-12 col-md-offset-1">
		
		<div class="col-md-10 col-xs-12">
		<div style="border:1px solid grey;padding:5px;border-radius:3px;display: inline-block">
		<span style="float:left;padding:8px">FILTRES</span>
		<input title="Date de début de stage" type="text" name="from-date" id="from-date" class="datepicker form-control" style="width:100px;float:left;margin-right:3px"/>
		<input title="Date de fin de stage" type="text" name="to-date" id="to-date" class="datepicker form-control" style="width:100px;float:left;margin-right:3px"/>
		<select title="Département" id="departement_filter" class="form-control" style="width:170px;float:left;margin-right:3px">
			<option value="0">Tous départements</option>
			<?php
			for ($i=1; $i<=95; $i++)
				echo "<option value='$i'>".sprintf("%02d", $i)."</option>";
			?>
		</select>	
		<button title="Afficher la liste des stages" class="btn btn-blue btn-filter" style="margin-left:10px">Rafraichir</button>	
		</div>
		</div>

		<div class="col-md-2 col-xs-12">
		<input title="Saisissez le nom du stagiaire" type="text" id="search_stagiaire" placeholder="Rechercher un stagiaire" style="font-size: 14px; float:right; width: 300px; padding:5px; text-transform: uppercase; margin-bottom:15px">
		<input type="hidden" id="search_stagiaire_hidden" value="">	
		</div>
	</div>
	</div>

	<div id="liste_stagiaires" class="container"></div>
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
	
	$.ajaxSetup({ cache: false });
	
	$(document).ajaxStart(function(){
		$.LoadingOverlay("show");
	});
	$(document).ajaxStop(function(){
		$.LoadingOverlay("hide");
	});	
	
	$(document).on('click', '.btn-filter', function () {		
		$('#search_stagiaire_hidden').val("");
		$('#search_stagiaire').val("");
		affiche_stagiaires();
	});
	
	$("#to-date").datepicker({
		dateFormat: "dd-mm-yy"
		}).datepicker("setDate", "+61");
	
	$("#from-date").datepicker({
		dateFormat: "dd-mm-yy"
		}).datepicker("setDate", "+1");
						
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
	
	affiche_stagiaires();
	
	$(document).on('click', '.fa-comment-alt', function () {
		
		var id_interlocuteur = $(this).attr('id_stagiaire');
		var type_interlocuteur = 3;
		var type_destinataire = 1;
		var notifie = 1;		
		var table = parseInt($(this).attr('table'));
		
		if (table == 2) {
			swal({
			  type: 'error',
			  text: 'Vous ne pouvez pas exécuter cette action sur un candidat ne provenant pas de ProstagesPermis',
			  showCancelButton: false,
			  confirmButtonText: 'OK'
			});
			
			exit;
		}
		
		var historic;
		$.ajax({ url: 'ajax_functions.php',
				 data: {action: 'get_historic_notifications', id_stagiaire: id_interlocuteur},
				 type: 'post',
				 async: false,
				 success: function(output) {
					historic = output;
				 }
		});
		
		swal({
		  title: 'Envoyer un message',
		  html: historic,
		  showCancelButton: true,
		  confirmButtonText: 'OK',
		  cancelButtonText: 'FERMER',
		  preConfirm: function (resolve, reject) {
			
				return new Promise(function (resolve, reject) {
					
					var text = $('#message').val();
					var phone_regex = RegExp('0[1-9]([-. ,/]?[0-9]{2}){4}');
					var email_regex = RegExp('[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\@[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\.[a-zA-Z]{2,4}');
					
					if (!parseInt($('#message').val().length)) {
						reject('Message obligatoire');
					}
					else if (phone_regex.test(text)) {
						reject('Numéro de téléphone non autorisé');
					}
					else if (email_regex.test(text)) {
						reject('Email et Url non autorisés');
					}
					else {
						resolve();
					}				
				});			
			
			}
		}).then(function(result) {
			
			$.ajax({ url: 'ajax_functions.php',
					 data: {action: 'send_message', type_interlocuteur: type_interlocuteur, id_interlocuteur: id_interlocuteur, type_destinataire: type_destinataire, notifie:notifie, message: text},
					 type: 'post',
					 success: function(output) {
						
						$.ajax({ url: '../mails_v3/ajax_actions.php',
								 data: {action: 'mail_nouveau_message', id: output},
								 type: 'post'
						});

						swal({
						  title: 'Contact',
						  html: 'Message envoyé',
						  showCancelButton: false,
						  confirmButtonText: 'OK'
						});					
					 }
			});
		})
		
		
		/*
		swal({
		  title: 'Message',
		  input: 'textarea',
		  customClass: 'custom-swal-1',
		  inputPlaceholder: 'Entrez votre texte ici',
		  showCancelButton: true,
		  confirmButtonColor: '#3085d6',
		  cancelButtonColor: '#d33',
		  confirmButtonText: 'Envoyer',
		  cancelButtonText: 'Fermer'
		}).then(function (text) {
			$.ajax({ url: 'ajax_functions.php',
					 data: {action: 'send_message', type_interlocuteur: type_interlocuteur, id_interlocuteur: id_interlocuteur, type_destinataire: type_destinataire, notifie:notifie, message: text},
					 type: 'post',
					 success: function(output) {
						
						$.ajax({ url: '../mails_v3/ajax_actions.php',
								 data: {action: 'mail_nouveau_message', id: output},
								 type: 'post'
						});

						swal({
						  title: 'Contact',
						  html: 'Message envoyé',
						  showCancelButton: false,
						  confirmButtonText: 'OK'
						});					
					 }
			});
		})	
		*/
		
	});
	
	$(document).on('click', '.fa-address-book', function () {
		
		var cont;
		var id_stagiaire = $(this).attr('id_stagiaire');
		var table = parseInt($(this).attr('table'));
		
		$.ajax({ url: 'ajax_functions.php',
					 data: {action: 'fiche_stagiaire', id_stagiaire: id_stagiaire, table: table},
					 type: 'post',
					 async: false,
					 success: function(output) {
						cont = output;
					 }
		});		
		
		swal({
		  html: cont,
		  showCancelButton: true,
		  cancelButtonText: 'Fermer',
		  confirmButtonText: 'Modifier',
		  preConfirm: function (resolve, reject) {
			
				return new Promise(function (resolve, reject) {
					
					if (!parseInt($('#nom').val().length)) {
						reject('Nom obligatoire');
					}												
					else {
						resolve();
					}				
				});			
			
			}
		}).then(function(result) {
			
			var nom = $('#nom').val();
			var prenom = $('#prenom').val();
			var adresse = $('#adresse').val();
			var code_postal = $('#code_postal').val();
			var ville = $('#ville').val();
			var cas = $('#cas').val();
			var date_naissance = $('#date_naissance').val();
			var lieu_naissance = $('#lieu_naissance').val();
			var num_permis = $('#num_permis').val();
			var date_permis = $('#date_permis').val();
			var lieu_permis = $('#lieu_permis').val();
			var date_infraction = $('#date_infraction').val();
			var heure_infraction = $('#heure_infraction').val();
			var lieu_infraction = $('#lieu_infraction').val();			
			
			$.ajax({ url: 'ajax_functions.php',
					 data: {action: 'update_fiche_stagiaire', 
										nom: nom, 
										prenom: prenom, 
										adresse: adresse, 
										code_postal:code_postal, 
										ville: ville,
										cas: cas,
										date_naissance: date_naissance,
										lieu_naissance: lieu_naissance,
										num_permis: num_permis,
										date_permis: date_permis,
										lieu_permis: lieu_permis,
										date_infraction: date_infraction,
										heure_infraction: heure_infraction,
										lieu_infraction: lieu_infraction,
										id_stagiaire: id_stagiaire,
										table: table},
					 type: 'post',
					 success: function(output) {
						affiche_stagiaires();										
					 }
			});
		})
		
	});
		
	$(document).on('click', '.fa-times-circle', function () {	
		
		if ($(this).hasClass('disabled'))
			return;
		
		var id_stagiaire = parseInt($(this).attr('id_stagiaire'));
		var table = parseInt($(this).attr('table'));
		var ethis = $(this);
		
		if (table == 2) {
			swal({
			  type: 'warning',
			  text: 'Attention, vous êtes sur le point de supprimer définitivement ce stagiaire. Voulez-vous continuer ?',
			  showCancelButton: true,
			  confirmButtonColor: '#3085d6',
			  cancelButtonColor: '#d33',
			  confirmButtonText: 'Confirmer',
			  cancelButtonText: 'Fermer'
			}).then(function (text) {
				$.ajax({ url: 'ajax_functions.php',
						 data: {action: 'delete_stagiaire_externe', id_stagiaire: id_stagiaire},
						 type: 'post',
						 success: function(output) {
							affiche_stagiaires();					
						 }
				});
			});

			return;			
		}
		
		swal({
		  text: 'Vous êtes sur le point d\'annuler l\'inscription de ce candidat. Veuillez sélectionner un motif ci-dessous ',
		  type: 'info',
		  showCloseButton: true,
		  showCancelButton: true,
		  confirmButtonColor: '#3085d6',
		  cancelButtonColor: '#d33',
		  confirmButtonText: 'Confirmer',
		  cancelButtonText: 'Fermer',
		  input: 'select',
		  inputPlaceholder: 'Motif annulation',
		  inputOptions: {
			'0': 'Ne souhaite plus faire le stage',
			'1': 'Dossier non réceptionné / Injoignable',
			'2': 'Absent le jour du stage',
			'3': 'Souhaite l\'effectuer plus tard',
			'4': 'A encore 12/12',
			'5': 'En attente de 48N',
			'6': 'Stage de moins de un an',
			'7': 'Lettre 48 SI',
			'8': 'Stage annulé faute de participants',
			'9': 'Stage annulé faute d\'animateurs',
			'10': 'Stage complet (paiement réceptionné trop tard)',
			'11': 'A réservé auprès d\'un autre prestataire',
			'12': 'Fausse inscription',
			'13': 'Coordonnées incorrectes',
			'14': 'Doublon'
		  },

		  // validator is optional
		  inputValidator: function(result) {
			return new Promise(function(resolve, reject) {
			  if (result) {
				resolve();
			  } else {
				reject('Vous devez sélectionner un motif');
			  }
			});
		  }
		}).then(function(result) {
		  if (result) {

				$.ajax({ url: 'ajax_functions.php',
							 data: {action: 'annulation_inscription', id_stagiaire: id_stagiaire, motif:result, table:table},
							 type: 'post',
							 success: function(output) {

									$.ajax({ url: '../mails_v3/ajax_actions.php',
											 data: {action: 'mail_annulation_centre', id: id_stagiaire},
											 type: 'post'
									});						
									
									$.ajax({ url: '../mails_v3/ajax_actions.php',
											 data: {action: 'annulation_stagiaire', id: id_stagiaire},
											 type: 'post'
									});
									
									ethis.closest('tr.ligne').find('.status').removeClass( "inscrit" ).addClass( "annule" );
							 }
				});				
		  }
		})
	});

});


function affiche_stagiaires() {
	
	var first_date 	= $('#from-date').val();
	first_date 		= first_date.split('-');
	first_date 		= first_date[2] + '-' + first_date[1] + '-' + first_date[0].slice(-2);
	
	var end_date 	= $('#to-date').val();
	end_date 		= end_date.split('-');
	end_date 		= end_date[2] + '-' + end_date[1] + '-' + end_date[0].slice(-2);	
	
	var departement = $('#departement_filter').val();
	
	var id_stagiaire = $('#search_stagiaire_hidden').val();
	
	$.ajax({ url: 'ajax_functions.php',
				 data: {action: 'affiche_stagiaires_erreur', first_date: first_date, end_date: end_date, departement: departement, id_stagiaire: id_stagiaire},
				 type: 'post',
				 success: function(output) {
					$("#liste_stagiaires").html(output);
				 }
	});
}

function upload(form, type, id_stagiaire, dossier) {
	
	$(form).ajaxForm(	
	{
			data: { 'type': type, 'id_stagiaire': id_stagiaire, 'dossier': dossier },
			success: function(data)
			{
				affiche_stagiaires();
			}
		}).submit();

}

function deleteAttestationSignee(file, dossier) {
	
	var xhr = getXhr();

	if (confirm('Voulez-vous supprimer ce document ?')) {
		xhr.onreadystatechange = function()
		{
			// On ne fait quelque chose que si on a tout recu et que le serveur est ok
			if(xhr.readyState == 4 && xhr.status == 200)
			{				
				location.reload();
			}
		}

		xhr.open("POST","ajax_delete_document.php",true);
		xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		xhr.send('file='+file+'&dossier='+dossier);	
	}
}


</script>

</html>


