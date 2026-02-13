<?php

require_once('../../common_bootstrap2/config3.php');
require_once "../params.php";
require_once "../debug.php";

$type_page = 'TYPE_PAGE_LIEUX';
$id_site_filter = !empty($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : "0";
$id_membre = 0;
if (isset($_SESSION['id_membre'])) {
    $id_membre = $_SESSION['id_membre'];
    unset($_SESSION['id_membre']);
}

$idSite = 0;
if (isset($_SESSION['isSite'])) {
    $idSite = $_SESSION['isSite'];
    unset($_SESSION['isSite']);
}


?><!DOCTYPE html>
<html lang="fr">
<head>
    <title>Sites avec stages en ligne</title>

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
        type_page = '<?php echo TYPE_PAGE_SITES ?>';
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
            margin: 0 0 7px 0;
        }

        .table .widget-inner,
        .table .widget-liste {
            height: auto;
            overflow: auto;
            padding: 0;
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
            top: -7px;
            position: relative;
        }

        .container {
            width: 100%;
        }

        .table .widget-inner .empty-msg {
            margin: 0px;
        }

        .table .widget-upload {
            padding-bottom: 8px;
        }

        .table th {
            text-align: center;
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
            padding: 10px;
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
            position: relative;
            padding: 5px 0px 0px 0px;
            color: #000;
            width: 90%;
            font-style: italic;
            color: blue;
        }

        .utilisateur {
            position: relative;
            padding: 5px 0px 0px 0px;
            color: #000;
            float: left;
            width: 90%;
            font-weight: bold;
        }

        .box {
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f6f6f6;
            padding: 20px 5px;
            height: 580px;
            overflow-y: scroll;
            padding: 10px;
        }

        .box legend {
            padding: 3px;
            align: center;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .box p {
            font-size: 13px;
        }

        .box .btn {
            min-width: 100%;
            margin-bottom: 5px;
        }

        .custom-swal-1 {
            height: 500px;
        }

        .custom-swal-1 .swal2-textarea {
            height: 310px;
        }

        textarea {
            width: 100%;
            padding: 5px;
        }


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

<?php
if (isset($_SESSION['partenariat']))
    include("includes/topbar_partenariat.php");
else
    include("includes/topbar.php");
?>

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
                                        <h3 class="panel-title"><i class="fa fa-bar-chart-o"></i> Sites des stages en
                                            ligne <img src="images/reload.png" style="width:32px;cursor:pointer"
                                                       class="reload"></h3>
                                    </div>
                                    <div class="panel-body">
                                        <div id="shieldui-grid1">

                                            <!--input type="text" name="from-date" id="from-date"
                                                   class="datepicker input_filtres_avances"
                                                   placeholder="Date stage min"/>
                                            <input type="text" name="to-date" id="to-date"
                                                   class="datepicker input_filtres_avances"
                                                   placeholder="Date stage max"/>
                                            <input type="hidden" name="id_site_filter" id="id_site_filter"
                                                   value="<?php //echo $id_site_filter; ?>"/>
                                            <input id="ok_range_date_stage" type="button" value="OK"
                                                   class="valid_button">

                                            <img src="images/reload.png"
                                                 style="margin-left:20px;width:32px;cursor:pointer;margin-right:40px"
                                                 class="reload"-->

                                            <!--input type="text" id="search_site" placeholder="Recherche un site"
                                                   style="font-size: 14px; float:right; width: 300px; padding:5px; text-transform: uppercase; margin-bottom:15px">
                                            <input type="hidden" id="search_site_hidden" value="<?php //echo $idSite; ?>"-->

                                            <table id="example" class="display responsive" cellspacing="0" width="100%">
                                                <thead>
                                                <tr>
                                                    <th>ID du centre</th>
                                                    <th>Nom du centre</th>
                                                    <th>Nom du lieu</th>
                                                    <th>Adresse du lieu</th>
                                                    <th>Code postal</th>
                                                    <th>Ville</th>
                                                    <th>Date création</th>
                                                    <th>No Agrément</th>
                                                    <th>Latitude</th>
                                                    <th>Longitude</th>
                                                    <th>Actif (nb place > 0)</th>
                                                    <th>Ville référente</th>
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
    var HOST = "<?php echo HOST ?>"

    $(document).ready(function () {

        $.ajaxSetup({cache: false});

        $(document).ajaxStart(function () {
            $.LoadingOverlay("show");
        });
        $(document).ajaxStop(function () {
            $.LoadingOverlay("hide");
        });

        $("body").tooltip({selector: '[data-toggle=tooltip]'});

        $.fn.editable.defaults.mode = 'inline';

        refreshNotifications();
        $(document).on('click', '.dropdown', function (e) {
            e.stopPropagation();
            refreshNotifications();
        });

        $("#from-date").datepicker({
            dateFormat: "dd/mm/yy"
        }).datepicker("setDate", "0");

        $("#to-date").datepicker({
            dateFormat: "dd/mm/yy"
        }).datepicker("setDate", "+30");

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

        //reload ajax on click sur bouton
        $( "#ok_range_date_stage" ).click(function() {
            $("#search_site_hidden").val("0");
            $("#id_site_filter").val("0");
            table.ajax.reload();
        });


        $(document).on('click', '.reload', function () {
            $("#id_membre_hidden").val("0");
            table.state.clear();
            window.location.reload();
        });

        $(document).on('click', '.ville_france_free_referente', function () {

            var id_site = $(this).attr('id_site');
            var ville_ref = $(this).attr('ville_ref');
            var contenu;

            $.ajax({
                url: 'ajax_functions.php',
                data: {action: 'select_ville_france_free_referente', id_ville: ville_ref},
                type: 'post',
                async: false,
                success: function (output) {
                    contenu = output;
                }
            });

            swal({
                title: "Ville référente",
                html: contenu,
                showCancelButton: true,
                confirmButtonText: 'VALIDER'
            }).then(function (result) {

                var new_val = $('#sel').val();

                $.ajax({
                    url: 'ajax_functions.php',
                    data: {action: 'change_ville_france_free_referente', id_site: id_site, new_val: new_val},
                    type: 'post',
                    success: function (output) {
                        table.ajax.reload();
                    }
                });
            })
        });

        $(document).on('click', '.delete_site', function () {
            var id_site = $(this).attr('id_site');
            swal({
                title: "Confirmer la suppression du lieux",
                showCancelButton: true,
                confirmButtonText: 'Valider',
                cancelButtonText: "Annuler",
            }).then(function (result) {
                $.ajax({
                    url: HOST + '/src/site/ajax/delete_site.php',
                    data: {idSite: id_site},
                    type: 'post',
                    success: function (output) {
                        table.ajax.reload();
                    }
                });
            })
        });

        $(document).on('click', '.edit_site', function () {

            var id_site = $(this).attr('id_site');
            var contenu;

            $.ajax({
                url: HOST + '/src/site/ajax/form_update_site.php',
                data: {idSite: id_site},
                type: 'post',
                async: false,
                success: function (output) {
                    contenu = output;
                }
            });

            swal({
                title: "Mettre à jour le lieu",
                html: contenu,
                showCancelButton: true,
                confirmButtonText: 'VALIDER',
                allowOutsideClick: false,
                cancelButtonText: "ANNULER",
                closeOnConfirm: false,
                closeOnCancel: true,
                preConfirm: function (resolve, reject) {
                    return new Promise(function (resolve, reject) {
                        var name = $('#name').val();
                        var address = $('#address').val();
                        var postal_code = $('#postal_code').val();
                        var city = $('#city').val();
                        var agreement = $('#agreement').val();
                        var lng = $('#lng').val();
                        var lat = $('#lat').val();
                        if (
                            !name || !address || !postal_code || !city || !agreement || !lng || !lat
                        ) {
                            reject('veuillez renseigner tous les champs');
                            return false;
                        } else {
                            resolve();
                        }
                    });
                }
            }).then(function (result) {
                var name = $('#name').val();
                var address = $('#address').val();
                var postal_code = $('#postal_code').val();
                var city = $('#city').val();
                var agreement = $('#agreement').val();
                var lng = $('#lng').val();
                var lat = $('#lat').val();
                $.ajax({
                    url: HOST + '/src/site/ajax/update_site.php',
                    data: {
                        name: name,
                        idSite: id_site,
                        address: address,
                        postal_code: postal_code,
                        city: city,
                        agreement: agreement,
                        lng: lng,
                        lat: lat
                    },
                    type: 'post',
                    success: function (output) {
                        table.ajax.reload();
                    }
                });
            })
        });

        $(document).on('click', '.comm_range', function () {

            var id_centre = $(this).attr('id_centre');
            var type = parseInt($(this).attr('type'));
            var comm = $(this).attr('comm');
            var contenu = "<input style='padding:3px' name='sel_comm' id='sel_comm' value='" + comm + "'>";
            var titre = type == 1 ? "Commission minimum" : "Commission maximum";

            swal({
                title: titre,
                html: contenu,
                showCancelButton: true,
                confirmButtonText: 'VALIDER'
            }).then(function (result) {

                var new_val = $('#sel_comm').val();

                $.ajax({
                    url: 'ajax_functions.php',
                    data: {action: 'change_comm_range', id_centre: id_centre, new_val: new_val, type: type},
                    type: 'post',
                    success: function (output) {
                        table.ajax.reload();
                    }
                });
            })
        });

        // Setup - add a text input to each footer cell
        $('#example tfoot th').each(function () {
            var visibility = $(this).attr("visibility");
            if (visibility != 'no') {
                var title = $(this).text();
                $(this).html('<input type="text" placeholder="' + title + '" />');
            }
        });


        // DataTable init
        table = $('#example').DataTable({
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
                'url': HOST + '/src/site/ajax/list_site.php',
                'data':
                    function (d) {
                        d.actif = 1
                        /*var arrDateFrom = $("#from-date").val().split("/")
                        var arrDateTo = $("#to-date").val().split("/")

                        d.from = arrDateFrom[2] + '-' + arrDateFrom[1] + '-' + arrDateFrom[0];
                        d.to = arrDateTo[2] + '-' + arrDateTo[1] + '-' + arrDateTo[0];

                        d.site = $("#search_site_hidden").val();
                        d.id_site_filter = $("#id_site_filter").val();

                         */
                    },

            },

            order : [[0, 'desc']],
            columnDefs: [{className: "dt-center", targets: [0,4,6,7,8,9,10]}],
            fnRowCallback: function (nRow) {
                //nRow.cells[13].noWrap = true;
                //nRow.cells[6].noWrap = true;
                //nRow.cells[11].noWrap = true;
                //nRow.cells[11].noWrap = true;
                //nRow.cells[9].noWrap = true;
                //nRow.cells[10].noWrap = true;
                return nRow;
            }

        ,
        columns: [
            {"data": "id"},
            {"data": "centre"},
            {"data": "nom"},
            {"data": "adresse"},
            {"data": "code_postal"},
            {"data": "ville"},
            {"data": "date_creation"},
            {"data": "agrement"},
            {"data": "latitude"},
            {"data": "longitude"},
            {"data": "actif"},
            {"data": "ville_france_free_referente"},
            {"data": "actions"}
        ]
    })
        ;

        // Apply the search
        table.columns().every(function () {
            var that = this;

            $('input', this.footer()).on('keyup change', function () {
                if (that.search() !== this.value) {
                    that
                        .search( "^"+this.value, true, true, true)
                        .draw();
                }
            });
        });

    })
    ;

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


