<?php
define(SIMPLIGESTION,'/home/prostage/www/simpligestion/');
require_once('/home/prostage/common_bootstrap2/config.php');
$type_page = 14;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Marge Commerciale 2025</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include("includes/header.php"); ?>
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="js/loadingoverlay.min.js"></script>
    
    <link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="font-awesome/css/font-awesome.min.css" />
    
    <style>
        body{
            font-size: 12px !important;     
        }
        div.container {
            width: 100% !important;
            height: 100% !important;
            margin: 0 auto !important;
        }
        .dataTables_wrapper{
            margin-top: 10px !important;
        }
        .h2, h2 {
            font-size:20px !important;  
        }
        input{
            background-color: #c2dbe9;
            height: 25px;
            padding: 3px;
            width: 100px;
        }
        table.dataTable thead th, table.dataTable thead td{
            padding: 5px 18px 5px 8px !important;  
        }
    </style>
</head>
<body>
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
                                        <h3 class="panel-title"><i class="fa fa-bar-chart-o"></i> STAGES</h3>
                                    </div>
                                    <div class="panel-body">
                                        <div id="shieldui-grid1">  
                                            <input type="text" name="from-date" id="from-date" class="datepicker input_filtres_avances" placeholder="Date stage min"/>
                                            <input type="text" name="to-date" id="to-date" class="datepicker input_filtres_avances" placeholder="Date stage max"/>
                                            <input id="ok_range_date_stage" type="button" value="OK" class="valid_button" style="background-color:rgb(240, 240, 240);width:50px"/>
                                            <img src="images/reload.png" style="margin-left:20px;width:32px;cursor:pointer" class="reload">
                                            <table id="table_data" class="display pageResize" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>Trier</th>
                                                        <th>Date</th>
                                                        <th>ID du stage</th>
                                                        <th>CP</th>
                                                        <th>Ville</th>
                                                        <th>Centre</th>
                                                        <th>Partenariat</th>
                                                        <th>Statut Stage</th>
                                                        <th>Prix Plancher TTC</th>
                                                        <th>Ville référente</th>
                                                        <th>Prix Index Min Référent</th>
                                                        <th>Prix Index Centre TTC</th>
                                                        <th>Prix Index Centre TTC (2)</th>
                                                        <th>Prix Index Centre HT</th>
                                                        <th>Delta Prix Index</th>
                                                        <th>Prix Vente TTC</th>
                                                        <th>Commission HT</th>
                                                        <th>Prix Achat HT</th>
                                                        <th>Marge Commerciale Globale HT</th>
                                                        <th>% Marge Commerciale Centre</th>
                                                        <th>Marge Commerciale Centre</th>
                                                        <th>Marge Commerciale PSP HT</th>
                                                        <th>Marge Brute PSP HT</th>
                                                        <th>Algo Activé</th>
                                                        <th>Nb Affichage Sur Site PSP</th>
                                                        <th>Boost Stage</th>
                                                    </tr>
                                                    <tr>
                                                        <th><input type="text" placeholder="Trier" /></th>
                                                        <th><input type="text" placeholder="Date" /></th>
                                                        <th><input type="text" placeholder="ID du stage" /></th>
                                                        <th><input type="text" placeholder="CP" /></th>
                                                        <th><input type="text" placeholder="Ville" /></th>
                                                        <th><input type="text" placeholder="Centre" /></th>
                                                        <th><input type="text" placeholder="Partenariat" /></th>
                                                        <th><input type="text" placeholder="Statut Stage" /></th>
                                                        <th><input type="text" placeholder="Prix Plancher" /></th>
                                                        <th><input type="text" placeholder="Ville référente" /></th>
                                                        <th><input type="text" placeholder="Prix Index Min Référent" /></th>
                                                        <th><input type="text" placeholder="Prix Index" /></th>
                                                        <th><input type="text" placeholder="Prix Index (2)" /></th>
                                                        <th><input type="text" placeholder="Prix Index HT" /></th>
                                                        <th><input type="text" placeholder="Delta Prix Index" /></th>
                                                        <th><input type="text" placeholder="Prix Vente" /></th>
                                                        <th><input type="text" placeholder="Commission HT" /></th>
                                                        <th><input type="text" placeholder="Prix Achat" /></th>
                                                        <th><input type="text" placeholder="Marge Commerciale Globale HT" /></th>
                                                        <th><input type="text" placeholder="% Marge Commerciale Centre" /></th>
                                                        <th><input type="text" placeholder="Marge Commerciale Centre" /></th>
                                                        <th><input type="text" placeholder="Marge Commerciale PSP HT" /></th>
                                                        <th><input type="text" placeholder="Marge Brute PSP HT" /></th>
                                                        <th><input type="text" placeholder="Algo Désactivé" /></th>
                                                        <th><input type="text" placeholder="Nb Affichage Sur Site PSP" /></th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>  
    <script type="text/javascript">
        var table;
        var id_stage;
        $(document).ready(function() {
            $("#from-date").datepicker({
                dateFormat: "dd/mm/yy"
            }).datepicker("setDate", "0");

            $("#to-date").datepicker({
                dateFormat: "dd/mm/yy"
            }).datepicker("setDate", "15");
               
            $( "#ok_range_date_stage" ).click(function() {
                table.ajax.reload();
            });

            $(document).on('click', '.reload', function () {
                table.state.clear();
                window.location.reload();
            } );

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

            $.ajaxSetup({ cache: false });
                table = $('#table_data').DataTable({
                    scrollX: true,
                    "pageLength": 500,
                ajax: {
                    'beforeSend': function () {
                        $.LoadingOverlay("show");
                    },
                    'complete': function () {
                        $.LoadingOverlay("hide");
                    },
                    'type': 'POST',
                    'url': 'stage/load_stages_mc25.php',
                    'data':
                        function (d) {
                            var arrDateFrom = $("#from-date").val().split("/")
                            var arrDateTo = $("#to-date").val().split("/")

                            d.from = arrDateFrom[2]+ '-' + arrDateFrom[1] + '-' + arrDateFrom[0];
                            d.to = arrDateTo[2]+ '-' + arrDateTo[1] + '-' + arrDateTo[0];
                        },
                },
                columns: [
                    { "data": "date_tri" },
                    { "data": "date" },
                    { "data": "id" },
                    { "data": "cp" },
                    { "data": "ville" },
                    { "data": "centre" },
                    { "data": "partenariat" },
                    { "data": "stage_statut" },
                    { "data": "prix_plancher" },
                    { "data": "ville_referente" },
                    { "data": "prix_index_min" },
                    { "data": "prix_index" },
                    { "data": "prix_index_2" },
                    { "data": "prix_index_centre_ht" },
                    { "data": "delta_prix_index" },
                    { "data": "prix_vente_ttc" },
                    { "data": "commission_ht" },
                    { "data": "prix_achat_ht" },
                    { "data": "marge_commerciale_globat_ht" },
                    { "data": "taux_marge_commerciale_centre" },
                    { "data": "marge_commerciale_centre" },
                    { "data": "marge_brute_psp_ht" },
                    { "data": "marge_brute_psp_ht" },
                    { "data": "algo_active" },
                    { "data": "nb_affichage" },
                    { "data": "boost_stage" }
                ],
                columnDefs: [
                    { width:'80px', targets: 0 },
                    { width:'80px', targets: 1 },
                    { width:'80px', targets: 2 },
                    { width:'80px', targets: 3 },
                    { width:'80px', targets: 5 },
                    { width:'80px', targets: 6 },
                    { width:'80px', targets: 9 },
                    { width:'80px', targets: 10 },
                    { width:'80px', targets: 11 },
                    { width:'80px', targets: 12 },
                    { width:'80px', targets: 13 },
                    { width:'80px', targets: 14 },
                    { width:'80px', targets: 15 },
                    { width:'80px', targets: 16 },
                    { width:'80px', targets: 17 },
                    { width:'80px', targets: 18 },
                    { width:'80px', targets: 19 },
                    { width:'80px', targets: 20 },
                    { width:'80px', targets: 21 }
                ]
            });
            table.columns().every( function () {
                var that = this;
                $( 'input', this.header() ).on( 'keyup change', function () {
                    if ( that.search() !== this.value ) {
                        that
                            .search( "^"+this.value, true, true, true)
                            .draw();
                    }
                } );
            } );
        } );
        $(document).on('change', '.select_stage_prix', function () {
            $.ajaxSetup({cache: false});
            var id_membre = $(this).attr("id_membre");
            id_stage = $(this).attr("id_stage");
            var date_stage = $(this).attr("date_stage");
            var id_site = $(this).attr("id_site");
            var id_ville_referente = $(this).attr("id_ville_referente");
            var departement = $(this).attr("departement");
            var partenariat = $(this).attr("partenaire"); 
            var prix_index_centre_ttc = $(this).val();  
            params = {
                ist: id_stage,
                dst: date_stage,
                ime: id_membre,
                isi: id_site,
                dep: departement,
                pri: prix_index_centre_ttc
            }
            var jsonString = JSON.stringify(params);
            $.ajax({
                url: 'https://www.prostagespermis.fr/src/mc25/get.php',
                data: {params: jsonString},
                type: 'post',
                async: false,
                success: function (output) {
                    results = $.parseJSON(output);
                    switch(results.error){
                        case '':
                            results['op'] = 'update';
                            results['ime'] = id_membre;
                            results['ist'] = id_stage;
                            results['isi'] = id_site;
                            results['pic'] = prix_index_centre_ttc;
                            
                            var params = JSON.stringify(results);
                            $.ajax({ 
                                url: 'https://www.prostagespermis.fr/src/mc25/process.php',
                                data: {action: 'stage', params: params},
                                type: 'post',
                                async: false,
                                success: function(output) {
                                    switch(output){
                                        case '2':
                                        break;
                                        default:
                                            table.ajax.reload();
                                        break;
                                    }
                                }
                            });    
                        break;
                    }
                }
            });
        });
    </script>
</body>
</html>