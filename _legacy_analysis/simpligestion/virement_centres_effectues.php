<?php
    
    require_once('../../common_bootstrap2/config2.php');

    $type_page = TYPE_PAGE_VIREMENTS_CENTRES_EFFECTUES;

?><!DOCTYPE html>
<html lang="fr">
<head>
    <title>Virements centres</title>
	
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

	<?php include("includes/header.php"); ?>

	<?php include('includes/head.php'); ?>

	<!-- sweet alert 2 -->
	<script src="https://cdn.jsdelivr.net/es6-promise/latest/es6-promise.auto.min.js"></script> <!-- IE support -->
	<script src="./dist/sweetalert2.js"></script>
	
	<script src="js/jquery-quickedit.js"></script>
	<script src="js/loadingoverlay.min.js"></script>
	
	<script src="js/jquery.modal.js" type="text/javascript" charset="utf-8"></script>
	<link rel="stylesheet" href="css/jquery.modal.css" type="text/css" media="screen" />
	
	<!-- sweet alert 2 -->
	<link rel="stylesheet" href="./dist/sweetalert2.min.css">

    <script type="text/javascript">
        type_page = '<?php echo TYPE_PAGE_VIREMENTS_CENTRES_EFFECTUES ?>';
    </script>

    <style type="text/css">
		
		.count {
			background: rgb(228, 35, 0) none repeat scroll 0 0;
			border: 1px solid #555;
			border-radius: 10px;
			color: white;
			font-size: 11px;
			line-height: 15px;
			padding: 0 3px;
			margin-left: -8px;
		}		
		
		.container {
			width: 100%;
		}         
		.table .widget-inner {
            margin:0 0 7px 0;
        }
        .table .widget-inner,
        .table .widget-liste {
            height:auto;
            overflow:auto;
            padding:0;
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
		 
		div.slider {
			display: none;
			float:left;
		}
		 
		table.dataTable tbody td.no-padding {
			padding: 0;
		}
		.panel-primary > .panel-heading {
			background-color: #eff2f9;
			border-color: #D9D9D9;
			color: #000;
		}
		.panel-primary {
			border-color: #D9D9D9;
		}
		.label {
			font-size:13px;
			background-color:#a2a2a2;
		}
		.nav-tabs li.active a span {
			background-color:#3A6EBF;
		}
		
		.input_ttc {
			padding:3px;
			font-weight:bold;
			font-size:13px;
		}
		.checkbox_centre {
			background-color:#2196F3;
			height: 20px;
			width: 20px;
		}
		input[type="text"]:disabled {
			/*background: #ff3434;*/
		}
		div.slider {
			display: none;
			padding:10px;
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
                            <h3 class="panel-title"><i class="fa fa-bar-chart-o"></i> Virements centres <img src="images/reload.png" style="width:32px;cursor:pointer" class="reload"></h3>
                        </div>
                        <div class="panel-body">
							<div id="shieldui-grid1">
								<div class="tabs-container">
								
								<table id="example" class="display responsive" cellspacing="0" width="100%">
								<thead>
									<tr>
										
										<th>ID</th>
										<th>ID Centre</th>
										<th>Nom Centre</th>
										<th>Date Facture</th>
										<th>Facture</th>
										<th>Facture2</th>
										<th>TVA</th>
										<th>Date virement</th>
										<th>Sepa</th>
										<th>NB Stagiaires</th>	
										<th>Enc HT</th>										
										<th>Enc TTC</th>
										<th>Comm HT</th>
										<th>Comm TTC</th>
										<th>Virement HT</th>
										<th>Virement TTC</th>	
										<th>Commentaire</th>										
										<th>ACTIONS</th>
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
			
			$("body").tooltip({ selector: '[data-toggle=tooltip]' });

			$.fn.editable.defaults.mode = 'inline';
			
			refreshNotifications();
			$(document).on('click', '.dropdown', function (e) {
			  e.stopPropagation();
			  refreshNotifications();
			});	

			$(document).on('click', '.reload', function () {
				table.state.clear();
				window.location.reload();
			} );		
			
			// Setup - add a text input to each footer cell
			$('#example tfoot th').each( function () {
				var visibility = $(this).attr("visibility");
				if (visibility != 'no') {
					var title = $(this).text();
					$(this).html( '<input type="text" placeholder="'+title+'" />' );
				}
			} );		
			
			// DataTable init			
			table = $('#example').DataTable( {		
				
				pageLength: 500,
				buttons: [
					'excel', 'csv', 'pdf', 'print'
				],
				dom: '<"top"iBf>rt<"bottom"lp><"clear">',

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
					'url': 'ajax_virement_centres_effectues.php'

				},

				columnDefs: [
					{ width: "20px", targets: [13] },
					{ width: "200px", targets: [12] },
					{ className: "dt-center", targets: [0,1,5,6,7,8,9,10,11]},
                    
				],				
				
				order: [[ 0, 'desc' ]],
				fnRowCallback: function( nRow ) {
					if(nRow.cells[3]) nRow.cells[3].noWrap = true;  //display sur une seule ligne
					return nRow;
				},
				columns: [
							{ "data": "id_virement" },
							{ "data": "id_membre" },
							{ "data": "nom" },
							{ "data": "date_facture" }, 
							{ "data": "facture" },
							{ "data": "facture2" },
							{ "data": "assujetti_tva" },
							{ "data": "date_virement" },
							{ "data": "sepa" },
							{ "data": "nb_stagiaires" },
							{ "data": "enc_ht" },
							{ "data": "enc_ttc" },
							{ "data": "comm_ht" },
							{ "data": "comm_ttc" },
							{ "data": "virement_ht" },
							{ "data": "virement_ttc" },
							{ "data": "commentaire" },
							{ "data": "action" }
						]
			});	

			$('#example tbody').on('click', '.fa-users', function () {

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
			} );		
			
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
			
		} );		

    </script>
	
	<script type='text/javascript'>
	
	function format ( d ) {

		var id_virement = d.id_virement;

		var xhr = getXhr();	
						
		xhr.open("POST","ajax_details_virement_centre_effectue.php",false);
		xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		xhr.send("id_virement="+id_virement);		
		
		return '<div class="slider">'+xhr.responseText+'</div>';
	}	

	function isPositiveInteger(str) {
		var n = Math.floor(Number(str));
		return String(n) === str && n > 0;
	}	
	</script>
	
</body>
</html>
