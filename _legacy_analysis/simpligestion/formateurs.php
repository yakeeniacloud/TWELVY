<?php
    
    require_once('../../common_bootstrap2/config.php');

    $type_page = TYPE_PAGE_FORMATEURS;

?><!DOCTYPE html>
<html lang="fr">
<head>
    <title>Stages</title>
	
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

	<?php include("includes/header.php"); ?>

	<?php include('includes/head.php'); ?>

	<!-- sweet alert 2 -->
	<script src="https://cdn.jsdelivr.net/es6-promise/latest/es6-promise.auto.min.js"></script> <!-- IE support -->
	<script src="./dist/sweetalert2.js"></script>
	
	<!-- sweet alert 2 -->
	<link rel="stylesheet" href="./dist/sweetalert2.min.css">
	
	<script src="js/loadingoverlay.min.js"></script>
	
	<script src="js/custom.js"></script>

    <script type="text/javascript">
        type_page = '<?php echo TYPE_PAGE_FORMATEURS ?>';
    </script>
	
	<script type="text/javascript">
	
	function blink_text() {  
		$('.blink').fadeOut(500);  
		$('.blink').fadeIn(500);  
	}  
	setInterval(blink_text, 1000);
	
	</script>

    <style type="text/css">
        .table .widget-inner {
            margin:0 0 7px 0;
        }
        .table .widget-inner,
        .table .widget-liste {
            height:auto;
            overflow:auto;
            padding:0;
        }
		
		.count {
			background: rgb(228, 35, 0) none repeat scroll 0 0;
			border: 1px solid #555;
			border-radius: 10px;
			color: white;
			font-size: 11px;
			line-height: 15px;
			padding: 0 3px;
			margin-left: 57px;
			top:-7px;
			position: relative;
		}
		
		.container {
			width: 100%;
		} 

        .table .widget-inner .empty-msg {
            margin:0px;
        }
        .table .widget-upload {
            padding-bottom:8px;
        }
        .table th {
            text-align:center;
        }
		body {
			margin-top: 0px;
		}
		#wrapper {
			padding-left: 0px;
		}
		td.details-control {
			background: url('images/loupe.png') no-repeat center center;
			cursor: pointer;
		}
		 
		tr.shown td.details-control {
			background: url('images/loupe.png') no-repeat center center;
		}
		 
		div.slider {
			display: none;
			padding:10px;
		}
		 
		table.dataTable tbody td.no-padding {
			padding: 0;
		}
		.panel-primary > .panel-heading {
			background-color: #f9f8f8;
			border-color: #D9D9D9;
			color: #000;
		}
		.panel-primary {
			border-color: #D9D9D9;
		}
		
		.charge_compte {
		  position:relative;
		  padding:5px 0px 0px 0px;
		  color:#000;
		  width:90%;
		  font-style:italic;
		  color:blue;
		}
		.utilisateur {
		  position:relative;
		  padding:5px 0px 0px 0px;
		  color:#000;
		  float:left;
		  width:90%;
		  font-weight:bold;
		}
				
		.box {
			border:1px solid #ccc;
			border-radius:5px;
			background-color:#f6f6f6;
			padding:20px 5px;
			height:580px;
			overflow-y: scroll;
			padding:10px;
		}
		.box legend{
			padding:3px;
			align:center;
			font-size:16px;
			margin-bottom:5px;
		}
		.box p {
			font-size:13px;
		}
		
		.box .btn {
			min-width:100%;
			margin-bottom:5px;
		}
		.custom-swal-1 {
			height:500px;
		}
		.custom-swal-1 .swal2-textarea{
			height:310px;
		}
		textarea { width: 100%; padding: 5px;}

        .dataTables_scroll {
            position: relative;
        }

        .dataTables_scrollHead {
            height: 80px;
        }

        .dataTables_scrollFoot {
            position: absolute;
            top: 40px;
        }

        div.dataTables_scrollFootInner > table.dataTable tfoot th, table.dataTable tfoot td {
            border-top: 0px solid #111;
        }


    </style>
</head>

<body class="contentspage">
    
    <?php include("includes/topbar.php"); ?>

	<div id="loading-overlay"></div>
     
	 <div id="content">

         <div class="contentwhite">
             <div class="container">
                 <div id="contenucentral">
                     
    <div id="wrapper">

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-bar-chart-o"></i>Formateurs</h3>
                        </div>
                        <div class="panel-body">
							<div id="shieldui-grid1">
							
							<button id="button_ajouter_formateur" class="btn btn-primary">Ajouter un formateur</button>
							
							<img src="images/reload.png" style="margin-left:20px;width:32px;cursor:pointer" class="reload">
					
							<div class="row">
							<div id="div_ajout_formateur" style="display:none;margin-top:10px;padding:10px;border-radius:3px" class="col-md-6">
								<p><input class="form-control" name="nom" id="nom" type="text" placeholder="Nom"></p>
								<p><input class="form-control" name="prenom" id="prenom" type="text" placeholder="Pr�nom"></p>
								<p><select class="form-control" style="width:50%;float:left" id="select_nouvelle_formation"><option value="bafm">bafm</option><option value="psy">psy</option></select></p>
								<button class="btn btn-success" id="valider_nouveau_formateur" style="float:right">AJOUTER</button>
							</div>
													
							</div>
							
							<table id="example" class="display responsive" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th>Nom</th>
										<th>Prénom</th>
										<th>Profession</th>
										<th>Gta</th>
										<th>Status</th>
										<th>Coordonnées</th>
										<th>Identifiants</th>
										<th>Ville</th>
										<th>Tél</th>
										<th>Email</th>
										<th>Partenaire</th>
										<th>Zone intervention</th>
										<th>Commentaire</th>									
										<th>Actions</th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<th></th>
										<th></th>
										<th></th>
										<th></th>
										<th></th>
										<th></th>
										<th></th>
										<th></th>
										<th></th>
										<th></th>
										<th></th>
										<th></th>
										<th></th>
										<th></th>
									</tr>
								</tfoot>
							</table>
							</div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->
    </div>
					 
					 
					 
                </div>
            </div>
         </div><!-- End contentwhite -->

     </div> <!-- End content General -->

    <?php include("includes/footer.php"); ?>


    <script type="text/javascript">		
		
		var table;
		$(document).ready(function() {
			
			$.ajaxSetup({ cache: false });
			
			$("body").tooltip({ selector: '[data-toggle=tooltip]' });

			$.fn.editable.defaults.mode = 'inline';
			
			refreshNotifications();
			$(document).on('click', '.dropdown', function (e) {
			  e.stopPropagation();
			  refreshNotifications();
			});
			
			$('#button_ajouter_formateur').click(function(){				
				$('#prenom').val('');
				$('#nom').val('');
				$('#div_ajout_formateur').slideToggle("fast");
			});
			
			$(document).on('click', '.reload', function () {
				table.state.clear();
				window.location.reload();
			} );
			
			$(document).on('click', '#valider_nouveau_formateur', function () {

				var nom = $('#nom').val();
				var prenom = $('#prenom').val();
				var formation = $('#select_nouvelle_formation').val();

				$.ajax({ url: 'ajax_functions.php',
						 data: {action: 'ajout_nouveau_formateur', nom: nom, prenom: prenom, formation: formation},
						 type: 'post',
						 success: function(output) {
							$('#div_ajout_formateur').slideToggle("fast");
							table.ajax.reload();
						 }
				});
			});
			
			$(document).on('click', '.statut_lu', function () {
				
				var id = jQuery(this).attr("id");
				var ethis = jQuery(this);
				
				$.ajax({ url: 'ajax_functions.php',
							 data: {action: 'change_etat_nonlu', id: id},
							 type: 'post',
							 success: function(output) {
									ethis.children().css( "color", "grey" );
									refreshNotifications();									
							 }
				});
			});		
			
			$(document).on('click', '.class_slide', function () {

				var tr = $(this).closest('tr');
				var row = table.row( tr );
				var action = $(this).attr('action');
			 
				if ( row.child.isShown() ) {
					// This row is already open - close it
					row.child.hide();
					tr.removeClass('shown');
				}
				else {
					// Open this row
					row.data().action = action;
					row.child(showSlider(row.data())).show();
					tr.addClass('shown');
					$('div.slider', row.child()).slideDown();
					load_editable();
				}
			} );
						
			
			// Setup - add a text input to each footer cell
			$('#example tfoot th').each( function () {
				var visibility = $(this).attr("visibility");
				if (visibility != 'no') {
					var title = $(this).text();
					$(this).html( '<input type="text" placeholder="'+title+'" />' );
				}
			} );
				
			
			$("input:eq(12)").val("oui"); //valeur par d�faut de l'input status
			
			// DataTable init			
			table = $('#example').DataTable( {
                'scrollY': 1000,
                'scrollCollapse': true,
                'scroller': true,
				pageLength: 500,
				buttons: [
					'excel', 'csv', 'pdf', 'print'
				],
				dom: '<"top"iB>rt<"bottom"lp><"clear">',

				serverSide: false,
				bJQueryUI: false,
				stateSave: true,
				ajax: {
					'beforeSend': function () {
					   $.LoadingOverlay("show");
					},
					'complete': function () {
						$.LoadingOverlay("hide");
					},					
					'type': 'POST',
					'url': 'ajax_formateurs.php'

				},
				aoSearchCols: [ //valeur par d�faut des inputs column
					null,null,null,null,null,null,null,null,null,null,
					{ "sSearch": "oui" }
				],
				columns: [
							{ "data": "nom" },
							{ "data": "prenom" },
							{ "data": "formation" },
							{ "data": "gta" },
							{ "data": "status" },
							{ "data": "coordonnees" },
							{ "data": "identifiants" },
							{ "data": "ville" },
							{ "data": "tel" },
							{ "data": "email" },
							{ "data": "partenaire" },
							{ "data": "perimetre" },
							{ "data": "commentaire" },
							{ "data": "actions" }
						],
				fnRowCallback: function( nRow ) {
					//nRow.cells[4].noWrap = true;
					//nRow.cells[11].noWrap = true;
					nRow.cells[10].noWrap = true;
					//nRow.cells[9].noWrap = true;
					//nRow.cells[10].noWrap = true;
					return nRow;
				}
			});
			
			// Apply the search
			table.columns().every( function () {
				var that = this;
		 
				$( 'input', this.footer() ).on( 'keyup change', function () {
					if ( that.search() !== this.value ) {
						that
							.search( this.value )
							.draw();
					}
				} );
			} );	

			$('#example tbody').on('click', 'td.details-control', function () {

				var tr = $(this).closest('tr');
				var row = table.row( tr );
				
				//remove_non_lu(row.data().id);
				//$(this).find('.count').remove();
			 
				if ( row.child.isShown() ) {
					// This row is already open - close it
					row.child.hide();
					tr.removeClass('shown');
				}
				else {
					// Open this row
					row.child( format(row.data()) ).show();
					tr.addClass('shown');
					$('div.slider', row.child()).slideDown();
					
					$('html, body').animate({
						scrollTop: tr.offset().top -50
					}, 700);
				}
			});
			
			$('#example tbody').on('click', '.delete_formateur', function () {
				
				var id_formateur = parseInt($(this).attr('id_formateur'));
				
				swal({
				  title: 'Suppression',
				  text: 'Voulez-vous supprimer ce formateur ?',
				  type: 'warning',
				  showCloseButton: true,
				  showCancelButton: true,
				  confirmButtonColor: '#3085d6',
				  cancelButtonColor: '#d33',
				  confirmButtonText: 'Oui',
				  cancelButtonText: 'Non'
				}).then(function () {			
					
					$.ajax({ url: 'ajax_functions.php',
							 data: {action: 'delete_formateur', id: id_formateur},
							 type: 'post',
							 async: false,
							 success: function(output) {	
								table.ajax.reload();
							 }
					});
				})
			});	

			$('#example tbody').on('click', '.valid_modif_formateur', function () {
				
				var info = [];  	
				info[0] = parseInt($(this).attr('id'));
				info[1] = $(this).closest(".slider").find("input[name=nom]").val();
				info[2] = $(this).closest(".slider").find("input[name=prenom]").val();
				info[3] = $(this).closest('.slider').find('select[name=metier] :selected').val();
				info[4] = $(this).closest('.slider').find('select[name=gta] :selected').val();
				info[5] = $(this).closest('.slider').find('select[name=status_societe] :selected').val();
				info[6] = $(this).closest(".slider").find("input[name=tel]").val();
				info[7] = $(this).closest(".slider").find("input[name=mobile]").val();
				info[8] = $(this).closest(".slider").find("input[name=email]").val();
				info[9] = $(this).closest(".slider").find("input[name=password]").val();
				info[10] = $(this).closest(".slider").find("input[name=code_postal]").val();
				info[11] = $(this).closest(".slider").find("input[name=ville]").val();
				info[12] = $(this).closest(".slider").find("textarea[name=commentaire]").val();
				
				$.ajax({
					type: "POST",
					url: "ajax_functions.php",
					data: {action:'update_formateur', params: info},
					success: function(output){
						table.ajax.reload();
					}
				});
			});	
			
			$('#example tbody').on('click', '.valid_status_stage', function () {
				
				var id_stage = parseInt($(this).attr('id_stage'));
				var val = parseInt($(this).closest('.slider').find('select[name=select_status_stage] :selected').val());
				
				if (val == 0) { //annulation
				
					swal({
					  title: 'Annulation de stage',
					  text: 'Choisissez le motif d\'annulation',
					  type: 'warning',
					  showCloseButton: true,
					  showCancelButton: true,
					  confirmButtonColor: '#3085d6',
					  cancelButtonColor: '#d33',
					  confirmButtonText: 'OK',
					  cancelButtonText: 'Fermer',
					  input: 'select',
					  inputPlaceholder: 'Motif',
					  inputOptions: {
						'9': 'faute de participants suffisants',
						'16': 'faute de salle disponible',
						'10': 'faute d�animateur'
					  },

					  // validator is optional
					  inputValidator: function(result) {
						return new Promise(function(resolve, reject) {
						  if (result) {
							resolve();
						  } else {
							reject('Choisissez une option pour l\'annulation des stagiaires');
						  }
						});
					  }
					}).then(function(result) {
					  if (result) {						
							$.LoadingOverlay("show");
							result = parseInt(result);
							$.ajax({ url: 'ajax_functions.php',
										 data: {action:'update_status_stage', id: id_stage, val: val, motif: result},
										 type: 'post',
										 success: function(output) {
											table.ajax.reload();								
										 }
							});
							$.LoadingOverlay("hide");
					  }
					})				
				
				}
				else {
					$.LoadingOverlay("show");
					$.ajax({ url: 'ajax_functions.php',
								 data: {action:'update_status_stage', id: id_stage, val: val, motif: 0},
								 type: 'post',
								 success: function(output) {
									table.ajax.reload();								
								 }
					});
					$.LoadingOverlay("hide");
				}
				
			});	

			$('#example tbody').on('click', '.fa-users', function () {
				var id_stage = parseInt($(this).attr('id_stage'));
				window.location.href = "inscriptions2.php?"+id_stage;
			});	

			$(document).on('click', '.smsButton', function () {

				var id = $(this).attr('id');
				swal({
				  title: 'SMS recherche animateurs',
				  showCancelButton: true,
				  cancelButtonText: "Annuler",
				  confirmButtonText: "Envoyer SMS",
				  input: 'select',
				  inputOptions: {
					'0': 'Tous les BAFM',
					'1': 'BAFM gta uniquement',
					'2': 'Tous les PSY',
					'3': 'PSY gta uniquement'
				  },

				  // validator is optional
				  inputValidator: function(result) {
					return new Promise(function(resolve, reject) {
					  if (result) {
						resolve();
					  } else {
						reject('Vous devez s�lectionner le type de m�tier');
					  }
					});
				  }
				}).then(function(result) {
				  if (result) {
					//sendSms(result, id);
				  }
				})
			});				
			
		} );

	function sendSms(type, id) {
		
		var xhr = getXhr();

		// On d�fini ce qu on va faire quand on aura la r�ponse
		xhr.onreadystatechange = function()
		{
			// On ne fait quelque chose que si on a tout re�u et que le serveur est ok
			if(xhr.readyState == 4 && xhr.status == 200)
			{
				swal({
				  type: 'success',
				  html: xhr.responseText + ' SMS envoy�s !'
				});
			}
		}

		xhr.open("POST","ajax_send_sms.php",true);
		xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		xhr.send('type='+type+'&id='+id);		
	}		
		
	function format ( d ) {

		var id_stagiaire = d.id_stagiaire;
		var type_interlocuteur = "STAGIAIRE";

		var xhr = getXhr();	
						
		xhr.open("POST","ajax_details_notification.php",false);
		xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		xhr.send("type_interlocuteur="+type_interlocuteur+"&id_interlocuteur="+id_stagiaire);	
		
		return '<div class="slider">'+xhr.responseText+'</div>';
	}
	
	function remove_non_lu(id_stage) {
		
		var xhr = getXhr();
		
		xhr.onreadystatechange = function()
		{
			// On ne fait quelque chose que si on a tout re�u et que le serveur est ok
			if(xhr.readyState == 4 && xhr.status == 200)
			{
			}
		}

		xhr.open("POST","ajax_remove_nonlu.php", true);
		xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		xhr.send('id_stage='+id_stage);
	}
	
	function attribue_formateur(id_formateur, type, id_stage, old_formateur, email_auto) {
		
		if (id_formateur != 1) 
			email_auto = 0;
		
		var xhr = getXhr();	
		xhr.open("POST","ajax_attribue_formateur.php", false);		
		xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		xhr.send('id='+id_formateur+'&type='+type+'&id_stage='+id_stage+'&email_auto='+email_auto+'&old_formateur='+old_formateur);	
	}
	
	function mail_confirmation(id_stage, destinataire) {
		
		var xhr = getXhr();
			
		var href;
		
		if (destinataire == 0)			href = 'img_hotel'+id_stage;
		else if (destinataire == 1)		href = 'img_bafm'+id_stage;
		else if (destinataire == 2)		href = 'img_psy'+id_stage;
		
		if (document.getElementById(href).src == 'http://www.prostagespermis.fr/affilies_bootstrap/images/led_green.png')
			return;
		
		ret = confirm('Confirmer l\'envoie des mails ?');

		if (ret == true)
		{
			$.LoadingOverlay("show");
			xhr.onreadystatechange = function()
			{
				// On ne fait quelque chose que si on a tout re�u et que le serveur est ok
				if(xhr.readyState == 4 && xhr.status == 200)
				{
					var ret = parseInt(xhr.responseText);
					if (ret == 1)
						document.getElementById(href).src= "images/led_blue.png";
					else if (ret >= 2)
						document.getElementById(href).src= "images/led_green.png";
					
					$.LoadingOverlay("hide");
				}
			}

			xhr.open("POST","ajax_mails_confirmation_stage.php", true);
			xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
			xhr.send('id_stage='+id_stage+'&destinataire='+destinataire);	
		}
	}
	

    </script>
	
    <!-- Mainly scripts -->
    <script src="js/bootstrap.min.js"></script>
    <script src="js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <!-- Custom and plugin javascript -->
    <script src="js/inspinia.js"></script>
    <script src="js/plugins/pace/pace.min.js"></script>
	
</body>
</html>


