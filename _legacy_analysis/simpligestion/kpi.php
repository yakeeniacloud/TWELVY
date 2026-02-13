<?php

require_once('../../common_bootstrap2/config.php');

//$type_page = TYPE_PAGE_KPI;

$query_string = "journalier";
if ($_SERVER['QUERY_STRING'] == "mensuel" && !empty($_SERVER['QUERY_STRING']))
    $query_string = "mensuel";
else if ($_SERVER['QUERY_STRING'] == "ville" && !empty($_SERVER['QUERY_STRING']))
    $query_string = "ville";

?><!DOCTYPE html>
<html lang="fr">
<head>
    <title>KPI</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php include("includes/header.php"); ?>

    <?php include('includes/head.php'); ?>

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css"
          integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">


    <!-- sweet alert 2 -->
    <script src="https://cdn.jsdelivr.net/es6-promise/latest/es6-promise.auto.min.js"></script> <!-- IE support -->
    <script src="./dist/sweetalert2.js"></script>

    <script src="js/jquery-quickedit.js"></script>
    <script src="js/loadingoverlay.min.js"></script>

    <script src="js/jquery.modal.js" type="text/javascript" charset="utf-8"></script>
    <link rel="stylesheet" href="css/jquery.modal.css" type="text/css" media="screen"/>

    <!-- sweet alert 2 -->
    <link rel="stylesheet" href="./dist/sweetalert2.min.css">

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
            margin: 0 0 7px 0;
        }

        .table .widget-inner,
        .table .widget-liste {
            height: auto;
            overflow: auto;
            padding: 0;
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

        div.slider {
            display: none;
            float: left;
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
            font-size: 13px;
            background-color: #a2a2a2;
        }

        .nav-tabs li.active a span {
            background-color: #3A6EBF;
        }

        .input_ttc {
            padding: 3px;
            font-weight: bold;
            font-size: 13px;
        }

        .checkbox_centre {
            background-color: #2196F3;
            height: 20px;
            width: 20px;
        }

        input[type="text"]:disabled {
            /*background: #ff3434;*/
        }

        div.slider {
            display: none;
            padding: 10px;
        }

        table.details_ville {
            text-align: center;
        }

        .fixed {
            position: fixed;
            top: 0;
            left: 35px;
            width: 100%;
            background-color: white;
            text-align: center;
            font-size: 11px;
            z-index: 10000;
        }

        .dataTables_scroll {
            position: relative;
        }

        .dataTables_scrollHead {
            height: 130px;
        }

        .dataTables_scrollFoot {
            position: absolute;
            top: 100px;
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
                                        <h3 class="panel-title"><i class="fa fa-bar-chart-o"></i> KPI <img
                                                    src="images/reload.png" style="width:32px;cursor:pointer"
                                                    class="reload"></h3>
                                    </div>
                                    <div class="panel-body">
                                        <div id="shieldui-grid1">
                                            Date
                                            <input type="text" name="from-date" id="from-date"
                                                   class="datepicker input_filtres_avances"
                                                   placeholder="Date stage min"/>
                                            <input type="text" name="to-date" id="to-date"
                                                   class="datepicker input_filtres_avances"
                                                   placeholder="Date stage max"/>
                                            <input id="ok_range_date_stage" type="button" value="OK"
                                                   class="valid_button">

                                            <img src="images/reload.png"
                                                 style="margin-left:20px;width:32px;cursor:pointer;margin-right:40px"
                                                 class="reload">

                                            <table id="example_ville" class="display responsive" cellspacing="0"
                                                   width="100%">
                                                <thead class="sticky">
                                                <tr style="font-size:11px">
                                                    <th>VILLE</th>
                                                    <th>HAB</th>
                                                    <th>Top Villes</th>
                                                    <th>POS<br>NAT</th>
                                                    <th>POS<br>ADW</th>
                                                    <th>CAMPAGNES <br> ADW <br>ACTIVEES</th>
                                                    <th>CENTRES <br>(avec stages en ligne)</th>
                                                    <th>STAGES PSP<br>(rayon 15 km)</th>
                                                    <th>STAGES PAP<br>(rayon 15 km)</th>
                                                    <th>OFFRE PSP/PAP</th>
                                                    <th>TX<br>ANNUL</th>
                                                    <th>INSCRITS</th>
                                                    <th>STAGIAIRES / STAGE</th>
                                                    <th>% INSCRITS<br>NAT (passé)</th>
                                                    <th>% INSCRITS<br>ADW (passé)</th>
                                                    <th></th>
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
    var table_mensuel;
    var table_ville;

    $(document).ready(function () {

        $(document).on('click', '.reload', function () {
            table_ville.state.clear();
            window.location.reload();
        } );

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


        $("body").tooltip({selector: '[data-toggle=tooltip]'});

        $.fn.editable.defaults.mode = 'inline';

        refreshNotifications();

        $(document).on('click', '.dropdown', function (e) {
            e.stopPropagation();
            refreshNotifications();
        });

        // Setup - add a text input to each footer cell
        $('#example tfoot th').each(function () {
            var visibility = $(this).attr("visibility");
            if (visibility != 'no') {
                var title = $(this).text();
                $(this).html('<input type="text" placeholder="' + title + '" />');
            }
        });

        $('#example_mensuel tfoot th').each(function () {
            var visibility = $(this).attr("visibility");
            if (visibility != 'no') {
                var title = $(this).text();
                $(this).html('<input type="text" placeholder="' + title + '" />');
            }
        });

        $('#example_ville tfoot th').each(function () {
            var visibility = $(this).attr("visibility");
            if (visibility != 'no') {
                var title = $(this).text();
                $(this).html('<input type="text" placeholder="' + title + '" />');
            }
        });

        // DataTable init

        //reload ajax on click sur bouton
        $( "#ok_range_date_stage" ).click(function() {
            table_ville.ajax.reload();
        });

        // Table kpi ville
        table_ville = $('#example_ville').DataTable({
            'scrollY': 1000,
            'scrollCollapse': true,
            'scroller': true,
            pageLength: 500,
            buttons: [
                'excel', 'csv', 'pdf', 'print'
            ],
            dom: '<"top"iBf>rt<"bottom"lp><"clear">',

            serverSide: false,
            bJQueryUI: false,
            ajax: {
                'beforeSend': function () {
                    $.LoadingOverlay("show");
                },
                'complete': function () {
                    $.LoadingOverlay("hide");
                    //load_quick_edit();
                },
                'type': 'POST',
                'url': 'ajax_kpi_ville.php',
                'data':
                    function(d) {
                        var arrDateFrom = $("#from-date").val().split("/")
                        var arrDateTo = $("#to-date").val().split("/")
                        d.from = arrDateFrom[2]+ '-' + arrDateFrom[1] + '-' + arrDateFrom[0];
                        d.to = arrDateTo[2]+ '-' + arrDateTo[1] + '-' + arrDateTo[0];
                    },
            },

            columnDefs: [
                {
                    className: "dt-center",
                    targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]
                }
            ],

            order: [[1, 'desc']],
            fnRowCallback: function (nRow) {
                //if(nRow.cells[0]) nRow.cells[0].noWrap = true;  //display sur une seule ligne
                //return nRow;
            },
            columns: [
                {"data": "ville"},
                {"data": "habitants"},
                {"data": "top_ville"},
                {"data": "pos_nat"},
                {"data": "pos_ads"},
                {"data": "campagne_adw"},
                {"data": "centres"},
                {"data": "nb_stages_psp"},
                {"data": "nb_stages_pap"},
                {"data": "offre_psp_pap"},
                {"data": "tx_annulation"},
                {"data": "inscrits"},
                {"data": "stagiaires_par_stage"},
                //{"data": "prix_max"},
                //{"data": "prix_moy"},
                //{"data": "prix_min"},
                //{"data": "min_conc"},
                {"data": "inscrits_nat"},
                {"data": "inscrits_ads"},
                /*{"data": "cout_global_adw"},
                {"data": "cout_par_conversion"},
                {"data": "comm_moy_stagiaire"},
                {"data": "cout_moy_stagiaire"},
                {"data": "marge_brute_stagiaire"},
                {"data": "marge_brute"},*/
                {"data": "action"}
            ]
        });

        $("ul.nav-tabs li").click(function () {

            $('ul.nav-tabs li').removeClass('active'); //enleve le active pour toutes les tabulations
            $(this).addClass("active");

            if (parseInt($("a", this).attr('id')) == 0) {
                //$('.btn-virement').show();
                //table.state.clear();
                //window.location.reload();
                window.location.href = "https://www.prostagespermis.fr/simpligestion/kpi.php?journalier";
            } else if (parseInt($("a", this).attr('id')) == 1) {
                //$('.btn-virement').hide();
                window.location.href = "https://www.prostagespermis.fr/simpligestion/kpi.php?mensuel";
                //affiche_stagiaires_bloques()
                //$('#example').html('ok');
            } else if (parseInt($("a", this).attr('id')) == 2) {
                window.location.href = "https://www.prostagespermis.fr/simpligestion/kpi.php?ville";
            }


        });

        $('#example_ville tbody').on('click', '.fa-search', function () {

            var tr = $(this).closest('tr');
            var row = table_ville.row(tr);

            //remove_non_lu(row.data().id);
            //$(this).find('.count').remove();

            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
            } else {
                // Open this row
                row.child(format(row.data())).show();
                tr.addClass('shown');
                $('div.slider', row.child()).slideDown();

                $('html, body').animate({
                    scrollTop: tr.offset().top - 50
                }, 700);
            }

        });

        $('#example_ville tbody').on('click', '.display_other_kpi_ville', function () {

            var tr = $(this).closest('tr');
            var row = table_ville.row(tr);
            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                row.child(displayOtherStatCity(row.data())).show();
                tr.addClass('shown');
                $('div.slider', row.child()).slideDown();
                $('html, body').animate({
                    scrollTop: tr.offset().top - 50
                }, 700);
            }
        });

        $('#example_ville tbody').on('click', 'i.detail_centre', function () {

            var tr = $(this).closest('tr');
            var row = table_ville.row(tr);
            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                row.child(displayCityDetails(row.data())).show();
                tr.addClass('shown');
                $('div.slider', row.child()).slideDown();

                $('html, body').animate({
                    scrollTop: tr.offset().top - 50
                }, 700);
            }
        });

        $('#example_ville tbody').on('click', 'i.detail_lieux', function () {

            var tr = $(this).closest('tr');
            var row = table_ville.row(tr);
            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                row.child(displayDetailsLieux(row.data())).show();
                tr.addClass('shown');
                $('div.slider', row.child()).slideDown();

                $('html, body').animate({
                    scrollTop: tr.offset().top - 50
                }, 700);
            }
        });


        $('#example_ville tbody').on('click', 'i.detail_inscription', function () {

            var tr = $(this).closest('tr');
            var row = table_ville.row(tr);
            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                row.child(displayCityDetailDays(row.data())).show();
                tr.addClass('shown');
                $('div.slider', row.child()).slideDown();

                $('html, body').animate({
                    scrollTop: tr.offset().top - 50
                }, 700);
            }
        });

        $('#example_ville tbody').on('click', 'i.detail_inscription_month', function () {

            var tr = $(this).closest('tr');
            var row = table_ville.row(tr);
            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                row.child(displayCityDetailMonth(row.data())).show();
                tr.addClass('shown');
                $('div.slider', row.child()).slideDown();

                $('html, body').animate({
                    scrollTop: tr.offset().top - 50
                }, 700);
            }
        });

        table_ville.columns().every(function () {
            var that = this;

            $('input', this.footer()).on('keyup change', function () {
                if (that.search() !== this.value) {
                    that
                        .search(this.value)
                        .draw();
                }
            });
        });

        var stickyOffset = $('.sticky').offset().top;

        $(window).scroll(function () {
            var sticky = $('.sticky'),
                scroll = $(window).scrollTop();

            if (scroll >= stickyOffset) sticky.addClass('fixed');
            else sticky.removeClass('fixed');
        });

        $(document).on('click', '.check_has_vip', function() {
            if($(this).is(':checked')) {
                $(this).parent().find('span').html('(1)');
                var isChecked = 1;
            } else {
                $(this).parent().find('span').html('(2)');
                var isChecked = 0;
            }

            var idVille = $(this).attr('id_ville');

            $.ajax({
                url: 'kpi/ajax_update_top_ville.php',
                data: {
                    id: idVille,
                    value: isChecked
                },
                type: 'post',
                success: function (output) {
                }
            });
        });


    });

</script>

<script type='text/javascript'>

    function format(d) {
        var id_ville = d.id;
        var xhr = getXhr();
        xhr.open("POST", "ajax_details_kpi_ville.php", false);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send("id_ville=" + id_ville);
        return '<div class="slider">' + xhr.responseText + '</div>';
    }

    function displayOtherStatCity(d) {

        var arrDateFrom = $("#from-date").val().split("/")
        var arrDateTo = $("#to-date").val().split("/")
        var from = arrDateFrom[2]+ '-' + arrDateFrom[1] + '-' + arrDateFrom[0];
        var to = arrDateTo[2]+ '-' + arrDateTo[1] + '-' + arrDateTo[0];

        var id_ville = d.id;
        var xhr = getXhr();
        xhr.open("POST", "ajax_details_other_kpi_ville.php", false);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send("id_ville=" + id_ville + "&from=" + from + "&to=" + to);
        return '<div class="slider">' + xhr.responseText + '</div>';
    }

    function displayCityDetails(d) {
        var arrDateFrom = $("#from-date").val().split("/")
        var arrDateTo = $("#to-date").val().split("/")
        var from = arrDateFrom[2]+ '-' + arrDateFrom[1] + '-' + arrDateFrom[0];
        var to = arrDateTo[2]+ '-' + arrDateTo[1] + '-' + arrDateTo[0];

        var id_ville = d.id;
        var xhr = getXhr();
        xhr.open("POST", "kpi/ajax_renta_kpi_ville.php", false);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send("id_ville=" + id_ville + "&from=" + from + "&to=" + to);
        return '<div class="slider">' + xhr.responseText + '</div>';
    }

    function displayCityDetailDays(d) {
        var arrDateFrom = $("#from-date").val().split("/")
        var arrDateTo = $("#to-date").val().split("/")
        var from = arrDateFrom[2]+ '-' + arrDateFrom[1] + '-' + arrDateFrom[0];
        var to = arrDateTo[2]+ '-' + arrDateTo[1] + '-' + arrDateTo[0];

        var id_ville = d.id;
        var xhr = getXhr();
        xhr.open("POST", "kpi/ajax_kpi_ville_day.php", false);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send("id_ville=" + id_ville + "&from=" + from + "&to=" + to);
        return '<div class="slider">' + xhr.responseText + '</div>';
    }

    function displayCityDetailMonth(d) {
        var arrDateFrom = $("#from-date").val().split("/")
        var arrDateTo = $("#to-date").val().split("/")
        var from = arrDateFrom[2]+ '-' + arrDateFrom[1] + '-' + arrDateFrom[0];
        var to = arrDateTo[2]+ '-' + arrDateTo[1] + '-' + arrDateTo[0];

        var id_ville = d.id;
        var xhr = getXhr();
        xhr.open("POST", "kpi/ajax_kpi_ville_month.php", false);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send("id_ville=" + id_ville + "&from=" + from + "&to=" + to);
        return '<div class="slider">' + xhr.responseText + '</div>';
    }

    function displayDetailsLieux(d) {
        var arrDateFrom = $("#from-date").val().split("/")
        var arrDateTo = $("#to-date").val().split("/")
        var from = arrDateFrom[2]+ '-' + arrDateFrom[1] + '-' + arrDateFrom[0];
        var to = arrDateTo[2]+ '-' + arrDateTo[1] + '-' + arrDateTo[0];

        var id_ville = d.id;
        var xhr = getXhr();
        xhr.open("POST", "kpi/ajax_kpi_ville_detail_lieu.php", false);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send("id_ville=" + id_ville + "&from=" + from + "&to=" + to);
        return '<div class="slider">' + xhr.responseText + '</div>';
    }

</script>
<!-- Mainly scripts -->
<script src="js/bootstrap.min.js"></script>
<script src="js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

</body>
</html>
