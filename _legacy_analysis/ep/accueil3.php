<?php

require_once('../../common_bootstrap2/config.php');
require_once('includes/functions.php');
require_once "../params.php";
require_once "../debug.php";

$type_page = TYPE_PAGE_STAGES;
$membre = $_SESSION['membre'];
$display_animator_current_stage = 0;
$display_stage_pourvoir = 0;
$display_commission2024 = 0;
//-
$query_site = "SELECT * FROM membre WHERE id = " . $membre;
$rs = mysql_query($query_site) or die(mysql_error());
if ($row = mysql_fetch_assoc($rs)) {
    $nouveau_modele_commission = $row["nouveau_modele_commission"];
    $display_animator_current_stage = $row['display_animator_current_stage'];
    $display_stage_pourvoir = $row['display_stage_pourvoir'];
    $display_commission2024 = $row['msg_commission2024_lu'];
} else {
    $nouveau_modele_commission = 0;
}

$isDisplayEmailNotification = isset($_SESSION['email_panne_notification']) ? 1 : 0;
unset($_SESSION['email_panne_notification']);

$afficher_voeux = 0;
$departements_commission2024 = '';
$display_commission2024_txt = '';

// Vérifier si le nouveau système de commission est applicable
if ($display_commission2024 == '0') {
    $query_site = "SELECT DISTINCT departement FROM site WHERE id_membre = '$membre' and visibilite = 1 ORDER BY departement";
    $rs = mysql_query($query_site) or die(mysql_error());
    $list = '';
    while ($row = mysql_fetch_assoc($rs)) {
        $dep = sprintf("%02d", $row['departement']);
        if ($list == '')
            $list = ' departements like "%' . $dep . '%"';
        else
            $list .= ' || departements like "%' . $dep . '%"';
    }
    if ($list != '') {
        $query = "SELECT * FROM `commission2024_departement` where " . $list;
        $rs = mysql_query($query) or die(mysql_error());
        $list = '';
        while ($row = mysql_fetch_assoc($rs)) {
            $list .= $row['departements'];
        }
        if ($list != '') {
            $list = explode(' | ', $list);
            $list = array_filter($list);
            sort($list);
            foreach ($list as $key => $val) {
                if ($departements_commission2024 == '')
                    $departements_commission2024 = $val;
                else
                    $departements_commission2024 .= ',' . $val;
            }
        }
        if ($departements_commission2024 != '') {
            $query = "SELECT * FROM `commission2024_params` where id=1";
            $rs = mysql_query($query) or die(mysql_error());
            if ($row = mysql_fetch_assoc($rs))
                $display_commission2024_txt = str_replace('%DEPS%', $departements_commission2024, addslashes($row['txt']));
        }
    }
}

$query_site = "SELECT * FROM site WHERE id_membre = " . $membre;
$resSite = mysql_query($query_site) or die(mysql_error());

$endYear = date('Y-m-d', strtotime('-16 year'));
$iStartYear = strtotime('1920-01-01');
$iEndYear = strtotime($endYear);

$optionsYear = "<option value=''>Année</option>";
$iCurrentYear = $iEndYear;
while ($iCurrentYear > $iStartYear) {
    $currentDate = date('Y-m-d', $iCurrentYear);
    $Y = date('Y', $iCurrentYear);
    $optionsYear .= "<option value='" . $Y . "'>" . $Y . "</option>";
    $iCurrentYear = strtotime("-1 year", strtotime($currentDate));
};

$endYear = date('Y-m-d');
$iStartYear = strtotime('1920-01-01');
$iEndYear = strtotime($endYear);

$optionsYearPermis = "<option value=''>Année</option>";
$iCurrentYear = $iEndYear;
while ($iCurrentYear > $iStartYear) {
    $currentDate = date('Y-m-d', $iCurrentYear);
    $Y = date('Y', $iCurrentYear);
    $optionsYearPermis .= "<option value='" . $Y . "'>" . $Y . "</option>";
    $iCurrentYear = strtotime("-1 year", strtotime($currentDate));
};

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <title>Espace affili&eacute;s</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php include("includes/header.php"); ?>

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css"
        integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <!-- sweet alert 2 -->
    <link rel="stylesheet" href="./dist/sweetalert2.min.css">

    <link href="css/custom.css" rel="stylesheet">

    <!-- sweet alert 2 -->
    <script src="https://cdn.jsdelivr.net/es6-promise/latest/es6-promise.auto.min.js"></script> <!-- IE support -->
    <script src="./dist/sweetalert2.js"></script>

    <script src="js/jquery.form.js"></script> <!-- necessaire pour chargement documents -->

    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="../../here/autosuggest.js"></script>

    <script>
        $(function() {
            $(document).tooltip({
                position: {
                    my: "center bottom-20",
                    at: "center top",
                    using: function(position, feedback) {
                        $(this).css(position);
                        $("<div>")
                            .addClass("arrow")
                            .addClass(feedback.vertical)
                            .addClass(feedback.horizontal)
                            .appendTo(this);
                    }
                }
            });
        });
    </script>

    <style>
        .btn-simulateur {
            padding: 14px !important;
            width: 100%;
            white-space: normal;
        }

        .select_hours2,
        .hour_stage {
            background-color: #E3EFF7;
            font-size: 13px;
            height: 2em;
            padding: 0em;
            margin: 5px;
            color: rgb(48, 91, 169);
            cursor: Pointer;
        }

        .select_stage_prix,
        .stage_price,
        .select_stage_nbplaces_max {
            background-color: #E3EFF7;
            font-size: 13px;
            height: 2em;
            padding: 0.2em;
            color: rgb(48, 91, 169);
            cursor: Pointer;
        }

        .border-col>div.row {
            margin: 20px 0;
            display: flex;
            /*align-items: center;*/
        }

        .div_student {
            display: flex;
            justify-content: center;
            flex-direction: column;
            align-items: center;
        }

        .div_stage_detail {
            padding: 20px;
            background-color: #EFF2F9;
            display: flex;
            justify-content: center;
            flex-direction: column;
            align-items: center;
        }

        .div_recherche_animator {
            padding: 20px;
            background-color: #EFF2F9;
            display: flex;
            justify-content: center;
            flex-direction: column;
            align-items: center;
        }

        .border-col {
            border: 1px solid #8f8f8f;
            padding: 15px;
            margin: 10px;
            background-color: #f5f5f5;
        }

        .border-col span.span_title {
            font-size: 12px;
            font-style: italic;
        }

        .padding-left-20 {
            padding-left: 20px;
        }

        .btn_stage {
            width: 200px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn_simulate,
        .btn_simulate:hover,
        .btn_simulate:active {
            background: #f0ad4e;
            border-color: #eea236;
            font-size: 12px;
            width: 230px;
            margin-left: 15px;
        }

        #myModal .modal-header {
            border-bottom: 0px;
        }

        #liste_stages {
            margin-top: 0px;
        }

        .main-content {
            padding: 60px 0 20px;
        }

        .fiche_stagiaire th {
            width: 30%;
            font-weight: normal;
        }

        .tr_infraction_display {
            display: none;
        }

        .swal2-confirm:hover,
        .swal2-confirm:active,
        .swal2-confirm:focus,
        .swal2-confirm:focus-visible {
            border-color: rgb(100, 163, 40) !important;
            outline: none;
        }

        .swal2-cancel,
        .swal2-confirm {
            font-size: 14px !important;
        }

        .main-table td {
            border: none;
        }

        .stage_table>tbody>tr:nth-child(odd)>td {
            background-color: #FFF !important;
        }

        .stage_table>tbody>tr:hover>td {
            background-color: #FFF !important;
        }

        .content_filter {
            display: none;
            flex-direction: column;
            position: absolute;
            background-color: #FFF;
            z-index: 10;
            padding: 20px;
            top: 30px;
            box-shadow: 0px 0px 2px rgb(0 0 0 / 20%), 0px 2px 8px rgb(0 0 0 / 10%);
            left: 0;
            max-height: 300px;
            width: 340px;
            overflow-y: scroll;
        }

        .content_filter ul {
            list-style-type: none;
            margin: 0 0 20px 0;
            padding: 0;
        }

        .content_filter ul li {
            margin: 4px 0;
        }

        .content_filter ul li label {
            display: flex;
            align-items: center;
        }

        .content_filter ul li label span {
            margin-left: 5px;
            margin-top: 1px;
        }

        .select_bafm_filter,
        .select_psy_filter {
            margin-top: 1px !important;
        }

        .btn-danger {
            font-size: 12px;
        }

        .only_simplegestion {
            display: none;
        }

        .main-content-head {
            padding-left: 55px;
            padding-right: 55px;
        }

        @media screen and (max-width: 767px) {
            .main-content {
                padding: 20px 0 10px;
            }

            .main-content-head {
                padding-left: 20px;
                padding-right: 20px;
            }
        }

        .swal-facture-stagiaire-externe h4 {
            margin-top: 5px;
            font-weight: 600;
        }

        .swal-facture-stagiaire-externe .swal2-title {
            background: #fff !important;
        }

        .swal-facture-stagiaire-externe .swal2-file::placeholder,
        .swal-facture-stagiaire-externe .swal2-input::placeholder,
        .swal-facture-stagiaire-externe .swal2-textarea::placeholder {
            color: #898989;
        }

        .swal-facture-stagiaire-externe input {
            height: 43px !important;
            padding: 0 12px !important;
            margin: 8px auto !important;
        }

        .swal-facture-stagiaire-externe .swal2-styled {
            font-size: 17px !important;
        }

        .popup_infos_access .swal2-icon.swal2-warning {
            color: red !important;
            border-color: red !important;
        }

        .popup_infos_access .swal2-spacer {
            display: none !important;
        }
    </style>

</head>

<body class="contentspage">

    <?php include("includes/topbar.php"); ?>


    <div id="content">

        <?php //include("includes/search_bar_home.php"); 
        ?>


        <div class="main-content">
            <div  style="margin-top:-40px;">
            <?php include(dirname(__FILE__) . "/includes/admin_options/popup_info_centre.php"); ?>
            </div>
        
            <?php
            if (isset($_SESSION['membre'])) { ?>
                <div class="row">
                    <div class="col-md-12 col-xs-12 col-md-offset-1 main-content-head">

                        <div class="col-md-8 col-xs-12">
                            <div style="border:1px solid grey;padding:5px;border-radius:3px;display: inline-block">
                                <span style="float:left;padding:8px">FILTRES</span>
                                <input title="Date de début de stage" type="text" name="from-date" id="from-date"
                                    class="datepicker form-control" style="width:100px;float:left;margin-right:3px" />
                                <input title="Date de fin de stage" type="text" name="to-date" id="to-date"
                                    class="datepicker form-control" style="width:100px;float:left;margin-right:3px" />
                                <select title="Visibilité du stage" id="status_filter" class="form-control"
                                    style="width:123px;float:left;margin-right:3px">
                                    <option value="0">Tous stages</option>
                                    <option value="1">En ligne</option>
                                    <option value="2">Hors ligne</option>
                                    <option value="3">Annulé</option>
                                </select>
                                <select title="Départements" id="departement_filter" class="form-control"
                                    style="width:105px;float:left;margin-right:3px">
                                    <option value="0">Tous départements</option>
                                    <?php
                                    for ($i = 1; $i <= 95; $i++)
                                        echo "<option value='$i'>" . sprintf("%02d", $i) . "</option>";
                                    ?>
                                </select>
                                <select title="Lieux" id="site" class="form-control"
                                    style="width:120px;float:left;margin-right:3px">
                                    <option value="0">Tous Lieux</option>
                                    <?php
                                    while ($site = mysql_fetch_assoc($resSite)) {
                                        echo "<option value='" . $site['id'] . "'>" . $site['nom'] . "</option>";
                                    }
                                    ?>
                                </select>
                                <select id="stagiaires_filter" class="form-control"
                                    style="width:100px;float:left;margin-right:3px">
                                    <option value="0">Tous</option>
                                    <option value="1">Avec stagiaires</option>
                                    <option value="2">Sans stagiaires</option>
                                </select>

                                <?php if ($display_stage_pourvoir) : ?>

                                    <label class="label_oder d-none d-sm-block" style="position: relative">
                                        <span class="animator_filter_bafm" is-open="0" filter=""
                                            style="display: flex; align-items: center;
                                          background-color: #FFF;
                                            border: 1px solid #d4d4d4;
                                            padding: 4px;
                                            border-radius: 3px;
                                            font-weight: normal;">
                                            <span class="city_filter_span" style="margin-right: 5px;">Statut BAFM</span>
                                            <i class="fa fa-2x fa-angle-down"></i>
                                        </span>
                                        <div class="content_filter bafm_filter_content" filter-apply="bafm" filter=""
                                            style="display: none;">
                                            <ul>
                                                <li>
                                                    <label>
                                                        <input type="checkbox" name="select_bafm_filter" value="all"
                                                            class="select_bafm_filter">
                                                        <span style="font-weight: normal">Tous</span>
                                                    </label>
                                                </li>
                                                <li>
                                                    <label>
                                                        <input type="checkbox" name="select_bafm_filter"
                                                            value="search_ok"
                                                            class="select_bafm_filter">
                                                        <span style="font-weight: normal">Trouvé</span>
                                                    </label>
                                                </li>
                                                <li>
                                                    <label>
                                                        <input type="checkbox" name="select_bafm_filter"
                                                            value="init_search"
                                                            class="select_bafm_filter">
                                                        <span style="font-weight: normal">A rechercher</span>
                                                    </label>
                                                </li>
                                                <li>
                                                    <label>
                                                        <input type="checkbox" name="select_bafm_filter"
                                                            value="current_search"
                                                            class="select_bafm_filter">
                                                        <span style="font-weight: normal">Recherche en cours</span>
                                                    </label>
                                                </li>
                                                <li>
                                                    <label>
                                                        <input type="checkbox" name="select_bafm_filter"
                                                            value="search_end"
                                                            class="select_bafm_filter">
                                                        <span style="font-weight: normal">Recherche terminée</span>
                                                    </label>
                                                </li>
                                            </ul>
                                            <button class="btn btn-default" id="close_bafm_search_filter">Fermer</button>
                                        </div>
                                    </label>

                                    <label class="label_oder d-none d-sm-block"
                                        style="margin: 0 20px 0 0; position: relative">
                                        <span class="animator_filter_psy" is-open="0" filter=""
                                            style="display: flex; align-items: center;
                                          background-color: #FFF;
                                            border: 1px solid #d4d4d4;
                                            padding: 4px;
                                            border-radius: 3px;
                                            font-weight: normal;">
                                            <span class="city_filter_span" style="margin-right: 5px;">Statut PSY</span>
                                            <i class="fa fa-2x fa-angle-down"></i>
                                        </span>
                                        <div class="content_filter psy_filter_content" filter="" style="display: none;">
                                            <ul>
                                                <li>
                                                    <label>
                                                        <input type="checkbox" name="select_psy_filter" value="all"
                                                            class="select_psy_filter">
                                                        <span style="font-weight: normal">Tous</span>
                                                    </label>
                                                </li>
                                                <li>
                                                    <label>
                                                        <input type="checkbox" name="select_psy_filter" value="search_ok"
                                                            class="select_psy_filter">
                                                        <span style="font-weight: normal">Trouvé</span>
                                                    </label>
                                                </li>
                                                <li>
                                                    <label>
                                                        <input type="checkbox" name="select_psy_filter" value="init_search"
                                                            class="select_psy_filter">
                                                        <span style="font-weight: normal">A rechercher</span>
                                                    </label>
                                                </li>
                                                <li>
                                                    <label>
                                                        <input type="checkbox" name="select_psy_filter"
                                                            value="current_search" class="select_psy_filter">
                                                        <span style="font-weight: normal">Recherche en cours</span>
                                                    </label>
                                                </li>
                                                <li>
                                                    <label>
                                                        <input type="checkbox" name="select_psy_filter"
                                                            value="search_end" class="select_psy_filter">
                                                        <span style="font-weight: normal">Recherche terminée</span>
                                                    </label>
                                                </li>
                                            </ul>
                                            <button class="btn btn-default" id="close_psy_search_filter">Fermer</button>
                                        </div>
                                    </label>

                                <?php endif ?>

                                <button class="btn btn-blue btn-filter pull-right" title="Afficher la liste des stages">
                                    Rafraichir
                                </button>
                            </div>
                        </div>

                        <!--div class="col-md-1 col-xs-12" style="text-align: center">
                    </div-->

                        <!--div class="col-md-1 col-xs-12" style="text-align: center">
                    </div-->

                        <div class="col-md-4 col-xs-12" style="display: flex; justify-content: end; padding-right: 10px">
                            <?php
                            if (false && $membre != 946) {
                                echo '<a class="btn btn-green btn-add ls-modal btn_stage" href="popup_ajouter_stage2025.php?key=' . uniqid() . '">AJOUTER UN STAGE</a>';
                            } else {
                                echo '<a class="btn btn-green btn-add ls-modal22 btn_stage" href="popup_ajouter_stage2025_v2.php?key=' . uniqid() . '">AJOUTER UN STAGE</a>';
                            }
                            ?>

                            <?php
                            if (false && ($nouveau_modele_commission == 1 || $membre >= 959))
                                echo '<a class="btn btn-green btn-add ls-modal2 btn_stage btn_simulate" href="popup_simulateur_renversement2024.php">SIMULATEUR DE REVERSEMENT</a>';
                            //echo '<a class="btn btn-warning btn-simulateur pull-center ls-modal2" href="popup_simulateur_renversement.php" style="width:200px;height:47px;">Simulateur de reversement</a>';
                            ?>

                            <?php if (true || $membre == 946) : ?>
                                <a class="btn btn-warning btn_stage btn_simulate" style="width:auto!important" href="javascript:TINY.box.show({iframe:'/src/commission/view/popup_simulateur.php', width:630, height:480, fixed:false, maskopacity:20})">SIMULATEUR DE REVERSEMENT</a>
                            <?php endif; ?>

                        </div>

                        <div id="myModal" class="modal fade col-md-10 col-xs-12 col-md-offset-1">
                            <div class="modal-dialog" style="width:100%">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                            &times;
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Chargement ...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="myModal2" class="modal fade col-md-10 col-xs-12 col-md-offset-1">
                            <div class="modal-dialog" style="width:700px">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                            &times;
                                        </button>
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
            } else {
                echo "<span style='text-align:center'>Vous n'êtes pas connecté</span>";
            } ?>

        </div><!-- End contentwhite -->

    </div> <!-- End content General -->

    <div id="addStagePopup" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" style="width: 1100px; max-width: 100%;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Ajout d'un stage</h4>
                </div>
                <div class="modal-body" style="height:600px; padding:0;">
                    <iframe id="popupIframe" style="width:100%; height:100%; border:none;" src=""></iframe>
                </div>
            </div>
        </div>
    </div>

    <?php include("includes/footer.php"); ?>


</body>

<script src="/assets/js/form_ants_validate.js"></script>
<script src="/assets/js/ants_common.js"></script>
<script src="js/loadingoverlay.min.js"></script>

<?php if (true || $membre == 946) : ?>
    <script src="/src/commission/assets/js/ep/popup_announcement_commission.js"></script>
    <script src="/src/commission/assets/js/ep/popup_announcement_commission_postprod.js"></script>
    <script>
        EffectiveCommissionAnnouncement.Init(<?php echo $membre; ?>);
        EffectiveCommissionAnnouncementPostProd.Init(<?php echo $membre; ?>);
    </script>
<?php endif; ?>

<script>
    $(document).on('click', '.open_declaration_simple', function() {

        var stageId = $(this).attr('id_stage');
        var memberId = $(this).attr('member_id');
        var isDeclareAnts = parseInt($(this).attr('is_declare_ants'));
        var cancel = parseInt($(this).attr('cancel'));

        if (!isDeclareAnts && !cancel) {
            AntsCommonUtils.ExternalSimpleDeclarationStage(stageId, memberId);
        }
    });
</script>


<script>
    $(document).on('click', '.reopen_declaration_edit', function() {

        var stageId = $(this).attr('id_stage');
        var memberId = $(this).attr('member_id');
        var isDeclareAnts = parseInt($(this).attr('is_declare_ants'));
        var cancel = parseInt($(this).attr('cancel'));

        if (!isDeclareAnts && !cancel) {
            AntsCommonUtils.ExternalDeclarationStage(stageId, memberId);
        }
    });
</script>

<script type="text/javascript">
    var id_membre0 = <?php echo $membre; ?>;
    var departements_commission2024 = "<?php echo $departements_commission2024; ?>";
    var display_commission2024 = "<?php echo $display_commission2024; ?>";
    var display_commission2024_txt = "<?php echo nl2br($display_commission2024_txt); ?>";
    var commission2024 = [];
    facture_stagiaire();

    function facture_stagiaire() {
        $(document).on('click', "#bouton_facture_stagiaire", function(e) {
            e.preventDefault();

            var url_facture = jQuery(this).attr("url_facture");

            //console.log('FACTURE STAGIAIRE', url_facture);

            swal({
                title: '<h4>Pour obtenir une facture directement au nom du stagiaire, cliquez directement sur “Télécharger la facture”.<br><br>Pour obtenir une facture au nom d\'une société, renseignez les champs nécessaires puis cliquez sur “Télécharger la facture”"</h4>',
                customClass: 'swal-facture-stagiaire-externe',
                html: '<input id="swal-input-company" class="swal2-input" placeholder="Société">' +
                    '<input id="swal-input-address" class="swal2-input" placeholder="Adresse">' +
                    '<input id="swal-input-cpville" class="swal2-input" placeholder="Code postal + Ville">',
                confirmButtonText: 'Télécharger la facture',
                onOpen: function() {
                    $('#swal-input-company').focus()
                }
            }).then(function(result) {
                //swal(JSON.stringify(result))
                if ($('#swal-input-company').val().length > 0) url_facture += '&societe=' + escape(encodeURIComponent($('#swal-input-company').val()));
                if ($('#swal-input-address').val().length > 0) url_facture += '&adresse=' + escape(encodeURIComponent($('#swal-input-address').val()));
                if ($('#swal-input-cpville').val().length > 0) url_facture += '&cpville=' + escape(encodeURIComponent($('#swal-input-cpville').val()));

                window.open(url_facture, '_blank');

            }).catch(swal.noop)

        });
    }

    function mergeAndFormatDate(year_field, month_field, day_field) {
        var year = (year_field != undefined) ? $(year_field).val() : '';
        var month = (month_field != undefined) ? $(month_field).val() : '';
        var day = (day_field != undefined) ? $(day_field).val() : '';

        if (year.length > 0 && month.length > 0 && day.length > 0) {
            return year + '-' + month + '-' + day;
        } else {
            return '';
        }
    }

    function isValidDate(s) {
        var bits = s.split('/');
        var d = new Date(bits[2] + '/' + bits[1] + '/' + bits[0]);
        return !!(d && (d.getMonth() + 1) == bits[1] && d.getDate() == Number(bits[0]));
    }

    function StagiairesStageDisplay(id_stage) {
        $.ajax({
            url: '/src/student/ajax/list_stagiaire_stage.php',
            data: {
                stageId: id_stage
            },
            type: 'post',
            success: function(output) {
                $("#stagiaires_" + id_stage).html(output);
            }
        });
    }

    function upload(form, type, id_stagiaire, dossier, id_stage) {

        $(form).ajaxForm({
            data: {
                'type': type,
                'id_stagiaire': id_stagiaire,
                'dossier': dossier
            },
            success: function(data) {
                StagiairesStageDisplay(id_stage);
            }
        }).submit();

    }

    function deleteAttestationSignee(file, dossier, id_stage) {

        var xhr = getXhr();

        if (confirm('Voulez-vous supprimer ce document ?')) {
            xhr.onreadystatechange = function() {
                // On ne fait quelque chose que si on a tout recu et que le serveur est ok
                if (xhr.readyState == 4 && xhr.status == 200) {
                    StagiairesStageDisplay(id_stage);
                }
            }

            xhr.open("POST", "ajax_delete_document.php", true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send('file=' + file + '&dossier=' + dossier);
        }
    }

    $(document).ready(function() {
        var popup_infos_access_display = false;
        if ($('.adjust-wrapper').data('mode_display_infos_current_date') == 1) {
            popup_infos_access_display = true;
        }
        /*if (display_commission2024 == '0' && departements_commission2024 != '' && display_commission2024_txt != '')
            //popup_infos_commission2024();
        else
            popup_infos_access();
        */
       
        function popup_infos_commission2024() {
            var corePopupInfosAccess = '<div style="text-align:center"><h3 style="color:red;font-weight:bold;">IMPORTANT</h3><p style="text-align:justify">' + display_commission2024_txt + '</p></div>';
            swal({
                title: '',
                html: corePopupInfosAccess,
                type: 'warning',
                customClass: 'popup_infos_access',
                input: "checkbox",
                inputValue: 0,
                inputPlaceholder: " J'accepte les nouvelles conditions",
                confirmButtonText: `Fermer`,
                confirmButtonColor: "#535D66",
                onOpen: AffichageDisplayUpdate()
            }).then(function(result) {
                if (parseInt(result) === 1) {
                    $.ajax({
                        url: 'ajax_commission2024_process.php',
                        data: {
                            op: 'msg_lu',
                            id_membre: id_membre
                        },
                        type: 'post',
                        async: false,
                        success: function(output) {
                            ;
                        }
                    });
                }
                if (popup_infos_access_display == true)
                    popup_infos_access();
            });
        }

        function AffichageDisplayUpdate() {
            $.ajax({
                url: 'ajax_commission2024_process.php',
                data: {
                    op: 'msg_display',
                    id_membre: id_membre0
                },
                type: 'post',
                async: false,
                success: function(output) {
                    ;
                }
            });
        }

        function popup_infos_access() {
            /*
            if(!localStorage.getItem('popup_infos_access')) {
                var corePopupInfosAccess = `
                    <div style="text-align:center">
                        <h3 style="color:red;font-weight:bold;">IMPORTANT</h3>
                        <p>Désormais les coordonnées des stagiaires vous seront communiquées <b>à partir du 1er jour de stage</b>.</p>
                        <p>Merci de votre compréhension.</p>
                    </div>
                `;

                swal({
                    title: '',
                    html: corePopupInfosAccess,
                    type: 'warning',
                    customClass: 'popup_infos_access',
                    input: "checkbox",
                    inputValue: 0,
                    inputPlaceholder: `
                        Ne plus afficher le message
                    `,
                    confirmButtonText: `
                        Ok
                    `,
                }).then(function(result) {
                    if(parseInt(result) === 1) {
                        localStorage.setItem('popup_infos_access', 'OK');
                    }
                });
            }
            */
        }

        var id_membre = <?php echo $membre; ?>;
        var display_animator_current_stage = <?php echo $display_animator_current_stage; ?>;

        $.ajaxSetup({
            cache: false
        });

        $(document).ajaxStart(function() {
            $.LoadingOverlay("show");
        });
        $(document).ajaxStop(function() {
            $.LoadingOverlay("hide");
        });

        $(document).on('click', '#downloadFeuilleEmargement', function(e) {
            var type = jQuery(this).attr("type");
            var format = jQuery(this).attr("format");
            var id_stagiaire = 0;
            var id_stage = jQuery(this).attr("id_stage");
            swal({
                title: "<div style='width:100%;color:#062B4E;background:white;padding-top:20px;padding-bottom:20px;'>Téléchargement de la feuille d'émargement</div>",
                html: "<div style='color:#062B4E'><img src='../images/progress.gif'><br><br>Traitement en cours...<div>",
                showCancelButton: true,
                cancelButtonText: 'Annuler',
                cancelButtonColor: '#808080',
                showConfirmButton: false,
            }).then(function(result) {
                $.LoadingOverlay("hide");
            });
            setTimeout(function(e) {
                $.ajax({
                    url: '/document/download/print.php',
                    data: {
                        type: type,
                        format: format,
                        studentId: id_stagiaire,
                        stageId: id_stage
                    },
                    type: 'post',
                    async: false,
                    success: function(output) {
                        if (output != '') {
                            var file_path = output;
                            var a = document.createElement('A');
                            a.href = file_path;
                            a.download = file_path.substr(file_path.lastIndexOf('/') + 1);
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                        }
                        swal.close();
                    }
                });
            }, 1000);
        });


        $(document).on('click', '#download_mail_inscription_pdf', function(e) {
            var type = jQuery(this).attr("type");
            var format = jQuery(this).attr("format");
            var id_stage = jQuery(this).attr("id_stage");
            var id_stagiaire = jQuery(this).attr("id_stagiaire");

            swal({
                title: "<div style='width:100%;color:#062B4E;background:white;padding-top:20px;padding-bottom:20px;'>Téléchargement du fichier de mail</div>",
                html: "<div style='color:#062B4E'><img src='../images/progress.gif'><br><br>Traitement en cours...<div>",
                showCancelButton: true,
                cancelButtonText: 'Annuler',
                cancelButtonColor: '#808080',
                showConfirmButton: false,
            }).then(function(result) {
                $.LoadingOverlay("hide");
            });
            setTimeout(function(e) {
                $.ajax({
                    url: '/document/download/print_mail.php',
                    data: {
                        type: type,
                        format: format,
                        stagiaireId: id_stagiaire,
                        stageId: id_stage
                    },
                    type: 'post',
                    async: false,
                    success: function(output) {
                        if (output != '') {
                            var file_path = output;
                            var a = document.createElement('A');
                            a.href = file_path;
                            a.download = file_path.substr(file_path.lastIndexOf('/') + 1);
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                        }
                        swal.close();
                    }
                });
            }, 1000);
        });


        $(document).on('click', '.print_feuille_emargement', function(e) {
            var type = jQuery(this).attr("type");
            var format = jQuery(this).attr("format");
            var id_stagiaire = 0;
            var id_stage = jQuery(this).attr("stageId");
            swal({
                title: "<div style='width:100%;color:#062B4E;background:white;padding-top:20px;padding-bottom:20px;'>Téléchargement de la feuille d'émargement pré-remplie</div>",
                html: "<div style='color:#062B4E'><img src='../images/progress.gif'><br><br>Traitement en cours...<div>",
                showCancelButton: true,
                cancelButtonText: 'Annuler',
                cancelButtonColor: '#808080',
                showConfirmButton: false,
            }).then(function(result) {
                $.LoadingOverlay("hide");
            });
            setTimeout(function(e) {
                $.ajax({
                    url: '/document/download/print.php',
                    data: {
                        type: type,
                        format: format,
                        studentId: id_stagiaire,
                        stageId: id_stage
                    },
                    type: 'post',
                    async: false,
                    success: function(output) {
                        if (output != '') {
                            var file_path = output;
                            var a = document.createElement('A');
                            a.href = file_path;
                            a.download = file_path.substr(file_path.lastIndexOf('/') + 1);
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                        }
                        swal.close();
                    }
                });
            }, 1000);
        });

        $(document).on('click', '#downloadAttestations', function(e) {
            var type = jQuery(this).attr("type");
            var format = jQuery(this).attr("format");
            var id_stagiaire = 0;
            var id_stage = jQuery(this).attr("id_stage");
            swal({
                title: "<div style='width:100%;color:#062B4E;background:white;padding-top:20px;padding-bottom:20px;'>Téléchargement du dossier des attestations</div>",
                html: "<div style='color:#062B4E'><img src='../images/progress.gif'><br><br>Traitement en cours...<div>",
                showCancelButton: true,
                cancelButtonText: 'Annuler',
                cancelButtonColor: '#808080',
                showConfirmButton: false,
            }).then(function(result) {
                $.LoadingOverlay("hide");
            });
            setTimeout(function(e) {
                $.ajax({
                    url: 'ajax_attestations.php',
                    data: {
                        type: type,
                        format: format,
                        id_stagiaire: id_stagiaire,
                        id_stage: id_stage
                    },
                    type: 'post',
                    async: false,
                    success: function(output) {
                        if (output != '') {
                            var file_path = output;
                            var a = document.createElement('A');
                            a.href = file_path;
                            a.download = file_path.substr(file_path.lastIndexOf('/') + 1);
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                        }
                        swal.close();
                    }
                });
            }, 1000);
        });

        $(document).on('click', '.download_all_attestation', function(e) {
            var type = jQuery(this).attr("type");
            var format = jQuery(this).attr("format");
            var id_stagiaire = 0;
            var stageId = jQuery(this).attr("stageId");
            swal({
                title: "<div style='width:100%;color:#062B4E;background:white;padding-top:20px;padding-bottom:20px;'>Téléchargement du dossier des attestations</div>",
                html: "<div style='color:#062B4E'><img src='../images/progress.gif'><br><br>Traitement en cours...<div>",
                showCancelButton: true,
                cancelButtonText: 'Annuler',
                cancelButtonColor: '#808080',
                showConfirmButton: false,
            }).then(function(result) {
                $.LoadingOverlay("hide");
            });
            setTimeout(function(e) {
                $.ajax({
                    url: '/document/download/print.php',
                    data: {
                        type: type,
                        format: format,
                        studentId: id_stagiaire,
                        stageId: stageId
                    },
                    type: 'post',
                    async: false,
                    success: function(output) {
                        if (output != '') {
                            var file_path = output;
                            var a = document.createElement('A');
                            a.href = file_path;
                            a.download = file_path.substr(file_path.lastIndexOf('/') + 1);
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                        }
                        swal.close();
                    }
                });
            }, 1000);
        });

        $(document).on('click', '.downloadAttestation', function(e) {
            var type = jQuery(this).attr("type");
            var format = jQuery(this).attr("format");
            var id_stagiaire = jQuery(this).attr("id_stagiaire");
            var id_stage = jQuery(this).attr("id_stage");
            swal({
                title: "<div style='width:100%;color:#062B4E;background:white;padding-top:20px;padding-bottom:20px;'>Téléchargement de l'attestation de stage</div>",
                html: "<div style='color:#062B4E'><img src='../images/progress.gif'><br><br>Traitement en cours...<div>",
                showCancelButton: true,
                cancelButtonText: 'Annuler',
                cancelButtonColor: '#808080',
                showConfirmButton: false,
            });
            setTimeout(function(e) {
                $.ajax({
                    url: '/document/download/print.php',
                    data: {
                        type: type,
                        format: format,
                        studentId: id_stagiaire,
                        stageId: id_stage
                    },
                    type: 'post',
                    async: false,
                    success: function(output) {
                        if (output != '') {
                            var file_path = output;
                            var a = document.createElement('A');
                            a.href = file_path;
                            a.download = file_path.substr(file_path.lastIndexOf('/') + 1);
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                            swal.close();
                        }
                    }
                });
            }, 1000);
        });

        $(document).on('click', '.download_attestation_stagiaire', function(e) {
            var type = $(this).attr("type");
            var format = $(this).attr("format");
            var studentId = $(this).attr("studentId");
            var stageId = $(this).attr("stageId");
            swal({
                title: "<div style='width:100%;color:#062B4E;background:white;margin-top:20px;'>Téléchargement de l'attestation de stage pré-remplie</div>",
                html: "<div style='color:#062B4E'><img src='../images/progress.gif'><br><br>Traitement en cours...<div>",
                showCancelButton: true,
                cancelButtonText: 'Annuler',
                cancelButtonColor: '#808080',
                showConfirmButton: false,
            });
            setTimeout(function(e) {
                $.ajax({
                    url: '/document/download/print.php',
                    data: {
                        type: type,
                        format: format,
                        studentId: studentId,
                        stageId: stageId
                    },
                    type: 'post',
                    async: false,
                    success: function(output) {
                        if (output != '') {
                            var file_path = output;
                            var a = document.createElement('A');
                            a.href = file_path;
                            a.download = file_path.substr(file_path.lastIndexOf('/') + 1);
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                        }
                        swal.close();
                    }
                });
            }, 1000);
        });

        $(document).on('click', '.btn-filter', function() {
            affiche_stages();
        });

        $("#to-date").datepicker({
            dateFormat: "dd-mm-yy"
        }).datepicker("setDate", "+360");

        const today = new Date();

        console.log('TODAY', today.getMonth(), today.getFullYear());

        // Vérifie si on est en septembre 2025
        let offset = "-20";
        if (today.getMonth() === 8 && today.getFullYear() === 2025) {
            // getMonth() retourne 0 = janvier, donc 8 = septembre
            offset = new Date(2025, 8, 16);
        }

        $("#from-date").datepicker({
            dateFormat: "dd-mm-yy"
        }).datepicker("setDate", offset);

        $.datepicker.setDefaults({
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
        });

        affiche_stages();

        $(document).on('click', '.download_folder', function() {
            var id_stage = jQuery(this).attr("id_stage");
            var div = "#hidden_tr_" + id_stage;
            $(div).slideToggle("slow");
        });

        $(document).on('click', '.fa-comment-alt', function() {

            var text;
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
            $.ajax({
                url: 'ajax_functions2024.php',
                data: {
                    action: 'get_historic_notifications',
                    id_stagiaire: id_interlocuteur
                },
                type: 'post',
                async: false,
                success: function(output) {
                    historic = output;
                }
            });

            var textIntro = '<div style="font-size:14px;"><p>Cette fonctionnalité vous permet de contacter un stagiaire uniquement pour <b>demander des documents manquants</b> ou <b>transmettre des informations essentielles</b>.</p><p><hr><b style="text-transform:uppercase">Règles à respecter</b><hr></p><ul><li><b>Ne demandez jamais</b> aux stagiaires leurs coordonnées téléphoniques ou e-mail.</li><li><b>Ne communiquez jamais</b> celles de votre organisme.</li></ul><hr><p style="font-weight:bold;text-transform:uppercase">Pourquoi ?</p><hr><p>Depuis <b>la Loi Hamon (2014)</b>, renforcée par le règlement <b>RGPD (2018)</b>, <b>ProStagesPermis est l\'unique interlocuteur des stagiaires jusqu\'au jour du stage</b>.<br>Nous vous remercions de respecter cette procédure. </p><hr><p style="font-weight:bold;">En cas de difficulté pour envoyer votre message :</p><p>Il peut arriver que le réseau soit temporairement saturé, <b>n’hésitez pas à réessayer dans quelques minutes</b>.</p><p>Merci de votre compréhension</p></b><hr>';

            swal({
                title: '',
                html: textIntro + historic,
                showCancelButton: true,
                confirmButtonText: 'ENVOYER',
                cancelButtonText: 'FERMER',
                confirmButtonColor: '#01A31C',
                preConfirm: function(resolve, reject) {

                    return new Promise(function(resolve, reject) {

                        text = $('#message').val();
                        var phone_regex = RegExp('0[1-9]([-. ,/]?[0-9]{2}){4}');
                        var email_regex = RegExp('[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\@[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\.[a-zA-Z]{2,4}');

                        if (!parseInt($('#message').val().length)) {
                            reject('Message obligatoire');
                        } else if (phone_regex.test(text)) {
                            reject('Numéro de téléphone non autorisé');
                        } else if (email_regex.test(text)) {
                            reject('Email et Url non autorisés');
                        } else {
                            resolve();
                        }
                    });

                }
            }).then(function(result) {

                $.ajax({
                    url: 'ajax_functions2024.php',
                    data: {
                        action: 'send_message',
                        type_interlocuteur: type_interlocuteur,
                        id_interlocuteur: id_interlocuteur,
                        type_destinataire: type_destinataire,
                        notifie: notifie,
                        message: text
                    },
                    type: 'post',
                    success: function(output) {

                        $.ajax({
                            url: '../mails_v3/ajax_actions.php',
                            data: {
                                action: 'mail_nouveau_message',
                                id: output
                            },
                            type: 'post'
                        });

                        swal({
                            title: 'Contact',
                            html: 'Message envoyé',
                            showCancelButton: false,
                            confirmButtonText: 'OK'
                        });

                        window.location.reload();
                    }
                });
            })
        });

        $(document).on('click', '.detail_student', function() {
            var studentId = $(this).attr('id_stagiaire');
            var isOpen = $(this).attr('isOpen');
            var memberId = $(this).attr('id_member');
            var table = parseInt($(this).attr('table'));
            var validations_stagiaire = $(this).attr('validations_stagiaire');
            if (parseInt(isOpen) == 0) {
                $(this).attr('isOpen', 1);
                $.ajax({
                    url: '/src/student/ajax/member_form_student_update25.php',
                    data: {
                        studentId: studentId,
                        memberId: memberId,
                        table: table,
                        isAnimator: 0,
                        validations_stagiaire: validations_stagiaire
                    },
                    type: 'post',
                    async: false,
                    success: function(output) {
                        $('.student_open').css('display', 'none');
                        $('.div_student').html('');
                        $('#div_student_' + studentId).html(output);
                        $('#tr_student_' + studentId).css('display', 'table-row');
                        initAddressAS();
                        initCityAS();
                    }
                });
            } else {
                $(this).attr('isOpen', 0);
                $('.student_open').css('display', 'none');
                $('.div_student').html('');
            }
        });

        $(document).on('change', '#cas', function() {
            var studentId = $(this).attr('student_id');
            var provenance = $(this).attr('provenance');
            $.ajax({
                url: '/src/student/ajax/update_one_field.php',
                data: {
                    field: 'cas',
                    value: parseInt($('#cas').val()),
                    id: studentId,
                    provenance: provenance
                },
                type: 'post',
                success: function(output) {
                    reload_documents(studentId);
                }
            });
            /*if (parseInt($('#cas').val()) == 2) {
                $('tr.tr_infraction_display').css('display', 'table-row');
            } else {
                $('tr.tr_infraction_display').css('display', 'none');
            }*/
            if (parseInt($('#cas').val()) == 2) {
                $('div.display_cas').css('display', 'block');
            } else {
                $('div.display_cas').css('display', 'none');
            }

            if (parseInt($('#cas').val()) == 3) {
                $('div.display_cas_3').css('display', 'block');
            } else {
                $('div.display_cas_3').css('display', 'none');
            }

            if (parseInt($('#cas').val()) == 4) {
                $('div.display_cas_4').css('display', 'block');
            } else {
                $('div.display_cas_4').css('display', 'none');
            }

            if (parseInt($('#cas').val()) == 3 || parseInt($('#cas').val()) == 4) {
                $('div.display_cas_34').css('display', 'block');
            } else {
                $('div.display_cas_34').css('display', 'none');
            }
        });

        $(document).on('change', '#modality_send_ants', function() {
            var studentId = $(this).attr('student_id');
            var provenance = $(this).attr('provenance');
            $.ajax({
                url: '/src/student/ajax/update_one_field.php',
                data: {
                    field: 'modality_send_ants',
                    value: parseInt($('#modality_send_ants').val()),
                    id: studentId,
                    provenance: provenance
                },
                type: 'post',
                success: function(output) {}
            });
        });

        $(document).on('change', '.presence_day', function() {
            var studentId = $(this).attr('student_id');
            var provenance = $(this).attr('provenance');
            var field = $(this).attr('field');
            var value = $(this).val();

            $.ajax({
                url: '/src/student/ajax/update_one_field.php',
                data: {
                    field: field,
                    value: value,
                    id: studentId,
                    provenance: provenance
                },
                type: 'post',
                success: function(output) {}
            });

        });


        $(document).on('click', '#cancel_update_student', function() {
            $('.student_open').css('display', 'none');
            $('.div_student').html('');
            $('.fa-pen').find().each(function(el) {
                $(el).attr('isOpen', 0);
            })
        })

        $(document).on('click', '#cancel_add_student', function() {
            $('.stagiaire_externe_open').css('display', 'none');
            $('.div_stage_detail').html('');
            $('.fa-user-plus').find().each(function(el) {
                $(el).attr('isOpen', 0);
            })
        })

        $(document).on('click', '#cancel_search_btn', function() {
            $('.recherche_animator_open').css('display', 'none');
            $('.div_recherche_animator').html('');
            $('.current_search_animator').attr('isOpen', 0);
            $('.search_animator').attr('isOpen', 0);
        });

        $(document).on('click', '#validate_update_student', function() {
            $('.ants_error').remove();
            var studentId = $('#studentId').val();
            var table = $('#table').val();
            var civility = $('#civility').val();
            var nom = $('#nom').val();
            var nom_usage = $('#nom_usage').val();
            var prenom = $('#prenom').val();
            var prenom2 = $('#prenom2').val();
            var prenom3 = $('#prenom3').val();
            var adresse = $('#adresse').val();
            var email = $('#email').val();
            var phone = $('#phone').val();
            //var adresse_complement = $('#adresse_complement').val();
            var code_postal = $('#code_postal').val();
            var ville = $('#ville').val();
            var birthday_day = $('#birthday_day').val();
            var birthday_month = $('#birthday_month').val();
            var birthday_year = $('#birthday_year').val();
            var lieu_naissance = $('#lieu_naissance').val();
            var departement_naissance = $('#departement_naissance').val();
            var pays_naissance = $('#pays_naissance').val();
            var num_permis = $('#num_permis').val();
            var lieu_permis = $('#lieu_permis').val();
            var permis_day = $('#permis_day').val();
            var permis_month = $('#permis_month').val();
            var permis_year = $('#permis_year').val();
            var etat_permis = $('#etat_permis').val();
            var cas = $('#cas').val();
            var infraction_day = $('#infraction_day').val();
            var infraction_month = $('#infraction_month').val();
            var infraction_year = $('#infraction_year').val();
            var heure_infraction = $('#heure_infraction').val();
            var minute_infraction = $('#minute_infraction').val();
            var lieu_infraction = $('#lieu_infraction').val();
            var is_directory_validate_member = $('#is_directory_validate_member').is(':checked') ? 1 : 0;

            var provenance = $('#provenance').val();
            //var paiement = $('#paiement').val();
            var receipt = $('#receipt').val();

            var prix_externe = $('#prix_externe').val();

            var dateCompositionPenale = mergeAndFormatDate('#date_composition_penale_year', '#date_composition_penale_month', '#date_composition_penale_day');

            var dateJugement = mergeAndFormatDate('#date_jugement_year', '#date_jugement_month', '#date_jugement_day');

            var numero_parquet = $('#numero_parquet').val();

            //console.log('ADD UPDATE MEMBER', provenance, receipt);

            if (permis_day != '' && permis_month != '' && permis_year != '') {
                $('#date_permis').val(permis_month + '-' + permis_month + '-' + permis_day);
            }

            var isValid = true;

            if (!validateAntsForm.validateNomNaissance($('#nom'))) {
                isValid = false;
                return false;
            }
            if (!validateAntsForm.validateNomUsage($('#nom_usage'))) {
                isValid = false;
                return false;
            }
            if (!validateAntsForm.validatePrenom($('#prenom'))) {
                isValid = false;
                return false;
            }
            if (!validateAntsForm.validatePrenom2($('#prenom2'))) {
                isValid = false;
                return false;
            }
            if (!validateAntsForm.validatePrenom3($('#prenom3'))) {
                isValid = false;
                return false;
            }
            if (!validateAntsForm.validateAdresse($('#adresse'))) {
                isValid = false;
                return false;
            }
            /*if (adresse_complement.length > 0) {
                if (!validateAntsForm.validateComplementAdresse($('#adresse_complement'))) {
                    isValid = false;
                }
            }*/
            if (!validateAntsForm.validateCodePostal($('#code_postal'))) {
                isValid = false;
                return false;
            }
            if (!validateAntsForm.validateVille($('#ville'))) {
                isValid = false;
                return false;
            }
            if (!validateAntsForm.validateLieuNaissance($('#lieu_naissance'))) {
                isValid = false;
                return false;
            }
            
            /*if (!validateAntsForm.validateCodeDepartement($('#departement_naissance'))) {
                isValid = false;
                return false;
            }*/

            if (!validateAntsForm.validatePayNaissance($('#pays_naissance'))) {
                isValid = false;
                return false;
            }
            if (!validateAntsForm.validateNumeroPermis($('#num_permis'))) {
                isValid = false;
                return false;
            }

            if (!validateAntsForm.validatePrectureDelivrancePermis($('#lieu_permis'))) {
                isValid = false;
                return false;
            }
            if (!validateAntsForm.validateDateDelivrancePermis($('#date_permis'))) {
                isValid = false;
                return false;
            }

            var animator_validate_data = $('#animator_validate_data').val();

            var adresse_complement = '';
            if (adresse.length > 34) {
                adresse_complement = adresse.substring(33);
            }

            if (!isValid) {

                swal({
                    type: 'warning',
                    text: 'Certains champs obligatoires pour la transmission à l\'Ants n\'ont pas été renseignés. Souhaitez-vous continuer?',
                    showCancelButton: true,
                    confirmButtonText: 'Confirmer',
                    cancelButtonText: 'Annuler',
                    confirmButtonColor: '#64a328',
                    cancelButtonColor: '#808080',
                    reverseButtons: true
                }).then(function(result) {
                    $.ajax({
                        url: '/src/student/ajax/member_update_student_data.php',
                        data: {
                            studentId: studentId,
                            table: table,
                            civility: civility,
                            nom: nom,
                            nom_usage: nom_usage,
                            prenom: prenom,
                            prenom2: prenom2,
                            prenom3: prenom3,
                            adresse: adresse,
                            adresse_complement: adresse_complement,
                            email: email,
                            phone: phone,
                            code_postal: code_postal,
                            ville: ville,
                            birthday_day: birthday_day,
                            birthday_month: birthday_month,
                            birthday_year: birthday_year,
                            lieu_naissance: lieu_naissance,
                            departement_naissance: departement_naissance,
                            pays_naissance: pays_naissance,
                            num_permis: num_permis,
                            lieu_permis: lieu_permis,
                            etat_permis: etat_permis,
                            permis_day: permis_day,
                            permis_month: permis_month,
                            permis_year: permis_year,
                            cas: cas,
                            infraction_day: infraction_day,
                            infraction_month: infraction_month,
                            infraction_year: infraction_year,
                            heure_infraction: heure_infraction,
                            minute_infraction: minute_infraction,
                            lieu_infraction: lieu_infraction,
                            animator_validate_data: animator_validate_data,
                            is_directory_validate_member: is_directory_validate_member,
                            provenance: provenance,
                            receipt_payment: receipt,
                            prix_externe: prix_externe,
                            dateCompositionPenale: dateCompositionPenale,
                            dateJugement: dateJugement,
                            numero_parquet: numero_parquet
                        },
                        type: 'post',
                        async: false,
                        success: function(output) {
                            $('.student_open').css('display', 'none');
                            $('.div_student').html('');
                        }
                    });
                });
            } else {
                $.ajax({
                    url: '/src/student/ajax/member_update_student_data.php',
                    data: {
                        studentId: studentId,
                        table: table,
                        civility: civility,
                        nom: nom,
                        nom_usage: nom_usage,
                        prenom: prenom,
                        prenom2: prenom2,
                        prenom3: prenom3,
                        adresse: adresse,
                        adresse_complement: adresse_complement,
                        email: email,
                        phone: phone,
                        code_postal: code_postal,
                        ville: ville,
                        birthday_day: birthday_day,
                        birthday_month: birthday_month,
                        birthday_year: birthday_year,
                        lieu_naissance: lieu_naissance,
                        departement_naissance: departement_naissance,
                        pays_naissance: pays_naissance,
                        num_permis: num_permis,
                        lieu_permis: lieu_permis,
                        etat_permis: etat_permis,
                        permis_day: permis_day,
                        permis_month: permis_month,
                        permis_year: permis_year,
                        cas: cas,
                        infraction_day: infraction_day,
                        infraction_month: infraction_month,
                        infraction_year: infraction_year,
                        heure_infraction: heure_infraction,
                        minute_infraction: minute_infraction,
                        lieu_infraction: lieu_infraction,
                        animator_validate_data: animator_validate_data,
                        is_directory_validate_member: is_directory_validate_member,
                        provenance: provenance,
                        receipt_payment: receipt,
                        prix_externe: prix_externe,
                        dateCompositionPenale: dateCompositionPenale,
                        dateJugement: dateJugement,
                        numero_parquet: numero_parquet
                    },
                    type: 'post',
                    async: false,
                    success: function(output) {
                        $('.student_open').css('display', 'none');
                        $('.div_student').html('');
                    }
                });
            }
        });

        $(document).on('click', '#validate_add_student', function() {
            $('.ants_error').remove();
            var studentId = $('#student_id').val();
            var stageId = $('#stage_id').val();
            var civility = $('#civility').val();
            var nom = $('#nom').val();
            var nom_usage = $('#nom_usage').val();
            var prenom = $('#prenom').val();
            var prenom2 = $('#prenom2').val();
            var prenom3 = $('#prenom3').val();
            var adresse = $('#adresse').val();
            var code_postal = $('#code_postal').val();
            var ville = $('#ville').val();
            var birthday_day = $('#birthday_day').val();
            var birthday_month = $('#birthday_month').val();
            var birthday_year = $('#birthday_year').val();
            var lieu_naissance = $('#lieu_naissance').val();
            var departement_naissance = $('#departement_naissance').val();
            var pays_naissance = $('#pays_naissance').val();
            var num_permis = $('#num_permis').val();
            var lieu_permis = $('#lieu_permis').val();
            var permis_day = $('#permis_day').val();
            var permis_month = $('#permis_month').val();
            var permis_year = $('#permis_year').val();
            var etat_permis = $('#etat_permis').val();
            var cas = $('#cas').val();
            var infraction_day = $('#infraction_day').val();
            var infraction_month = $('#infraction_month').val();
            var infraction_year = $('#infraction_year').val();
            var heure_infraction = $('#heure_infraction').val();
            var minute_infraction = $('#minute_infraction').val();
            var lieu_infraction = $('#lieu_infraction').val();
            var is_directory_validate_member = $('#is_directory_validate_member').is(':checked') ? 1 : 0;
            var tel = $('#phone').val();
            var email = $('#email').val();
            var provenance = $('#provenance').val();
            var paiement = $('#paiement').val();
            var receipt = $('#receipt').val();

            var dateCompositionPenale = mergeAndFormatDate('#date_composition_penale_year', '#date_composition_penale_month', '#date_composition_penale_day');

            var dateJugement = mergeAndFormatDate('#date_jugement_year', '#date_jugement_month', '#date_jugement_day');

            var numero_parquet = $('#numero_parquet').val();

            if (permis_day != '' && permis_month != '' && permis_year != '') {
                $('#date_permis').val(permis_month + '-' + permis_month + '-' + permis_day);
            }

            var isValid = true;

            if (!validateAntsForm.validateNomNaissance($('#nom'))) {
                isValid = false;
                return false;
            }
            if (!validateAntsForm.validateNomUsage($('#nom_usage'))) {
                isValid = true;
            }
            if (!validateAntsForm.validatePrenom($('#prenom'))) {
                isValid = false;
                return false
            }
            if (!validateAntsForm.validatePrenom2($('#prenom2'))) {
                isValid = true;
            }
            if (!validateAntsForm.validatePrenom3($('#prenom3'))) {
                isValid = true;
            }
            if (!validateAntsForm.validateAdresse($('#adresse'))) {
                isValid = true;
            }
            if (!validateAntsForm.validateCodePostal($('#code_postal'))) {
                isValid = true;
            }
            if (!validateAntsForm.validateVille($('#ville'))) {
                isValid = true;
            }
            if (!validateAntsForm.validateLieuNaissance($('#lieu_naissance'))) {
                isValid = true;
            }

            /*
            if (!validateAntsForm.validateCodeDepartement($('#departement_naissance'))) {
                isValid = true;
            }
            */

            if (!validateAntsForm.validatePayNaissance($('#pays_naissance'))) {
                isValid = true;
            }
            if (!validateAntsForm.validateNumeroPermis($('#num_permis'))) {
                isValid = true;
            }
            if (!validateAntsForm.validatePrectureDelivrancePermis($('#lieu_permis'))) {
                isValid = true;
            }
            if (!validateAntsForm.validateDateDelivrancePermis($('#date_permis'))) {
                isValid = true;
            }
            var animator_validate_data = $('#animator_validate_data').val();

            var adresse_complement = '';
            if (adresse.length > 34) {
                adresse_complement = adresse.substring(33);
            }
            if (!isValid) {

                swal({
                    type: 'warning',
                    text: 'Certains champs obligatoires pour la transmission à l\'Ants n\'ont pas été renseignés. Souhaitez-vous continuer?',
                    showCancelButton: true,
                    confirmButtonText: 'Confirmer',
                    cancelButtonText: 'Annuler',
                    confirmButtonColor: '#64a328',
                    cancelButtonColor: '#808080',
                    reverseButtons: true
                }).then(function(result) {
                    $.ajax({
                        url: '/src/student/ajax/save_external_student.php',
                        data: {
                            studentId: studentId,
                            civility: civility,
                            nom: nom,
                            nom_usage: nom_usage,
                            prenom: prenom,
                            prenom2: prenom2,
                            prenom3: prenom3,
                            adresse: adresse,
                            adresse_complement: adresse_complement,
                            code_postal: code_postal,
                            ville: ville,
                            birthday_day: birthday_day,
                            birthday_month: birthday_month,
                            birthday_year: birthday_year,
                            lieu_naissance: lieu_naissance,
                            departement_naissance: departement_naissance,
                            pays_naissance: pays_naissance,
                            num_permis: num_permis,
                            lieu_permis: lieu_permis,
                            etat_permis: etat_permis,
                            permis_day: permis_day,
                            permis_month: permis_month,
                            permis_year: permis_year,
                            cas: cas,
                            infraction_day: infraction_day,
                            infraction_month: infraction_month,
                            infraction_year: infraction_year,
                            heure_infraction: heure_infraction,
                            minute_infraction: minute_infraction,
                            lieu_infraction: lieu_infraction,
                            animator_validate_data: animator_validate_data,
                            is_directory_validate_member: is_directory_validate_member,
                            tel: tel,
                            email: email,
                            provenance: provenance,
                            receipt: receipt,
                            paiement: paiement,
                            dateCompositionPenale: dateCompositionPenale,
                            dateJugement: dateJugement,
                            numero_parquet: numero_parquet
                        },
                        type: 'post',
                        async: false,
                        success: function(output) {
                            $('.stagiaire_externe_open').css('display', 'none');
                            $('.div_stage_detail').html('');
                            affiche_stages();
                        }
                    });
                });
            } else {
                $.ajax({
                    url: '/src/student/ajax/save_external_student.php',
                    data: {
                        studentId: studentId,
                        civility: civility,
                        nom: nom,
                        nom_usage: nom_usage,
                        prenom: prenom,
                        prenom2: prenom2,
                        prenom3: prenom3,
                        adresse: adresse,
                        adresse_complement: adresse_complement,
                        code_postal: code_postal,
                        ville: ville,
                        birthday_day: birthday_day,
                        birthday_month: birthday_month,
                        birthday_year: birthday_year,
                        lieu_naissance: lieu_naissance,
                        departement_naissance: departement_naissance,
                        pays_naissance: pays_naissance,
                        num_permis: num_permis,
                        lieu_permis: lieu_permis,
                        etat_permis: etat_permis,
                        permis_day: permis_day,
                        permis_month: permis_month,
                        permis_year: permis_year,
                        cas: cas,
                        infraction_day: infraction_day,
                        infraction_month: infraction_month,
                        infraction_year: infraction_year,
                        heure_infraction: heure_infraction,
                        minute_infraction: minute_infraction,
                        lieu_infraction: lieu_infraction,
                        animator_validate_data: animator_validate_data,
                        is_directory_validate_member: is_directory_validate_member,
                        tel: tel,
                        email: email,
                        provenance: provenance,
                        receipt: receipt,
                        paiement: paiement,
                        dateCompositionPenale: dateCompositionPenale,
                        dateJugement: dateJugement,
                        numero_parquet: numero_parquet
                    },
                    type: 'post',
                    async: false,
                    success: function(output) {
                        $('.stagiaire_externe_open').css('display', 'none');
                        $('.div_stage_detail').html('');
                        affiche_stages();
                    }
                });
            }

        });

        $(document).on('click', '.collapse_one', function() {
            var isOpen = $(this).attr('is-open');
            if (parseInt(isOpen) == 0) {
                $(this).attr('is-open', 1);
                $(this).find('.chevron_down_one i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                $(this).find('.chevron_down_one span').html('Fermer')
            } else {
                $(this).attr('is-open', 0);
                $(this).find('.chevron_down_one i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
                $(this).find('.chevron_down_one span').html('Ouvrir')
            }
        });


        $(document).on('click', '.fa-users', function() {
            var id_stage = jQuery(this).attr("id_stage");
            var open = true;
            if ($("#tr_stagiaire_" + id_stage).css('display') != "none")
                open = false;
            $("table[id^=table_]").remove();
            $("tr[id^=tr_stagiaire_]").hide();
            if (open == true) {
                StagiairesStageDisplay(id_stage);
                $("#tr_stagiaire_" + id_stage).show();
            }
        });

        $(document).on('click', '.detail_stage_display', function() {
            var stageId = jQuery(this).attr("id_stage");
            var isOpen = parseInt($(this).attr('isOpen'));
            if (isOpen == 0) {
                $(this).attr('isOpen', 1);
                $.ajax({
                    url: 'stage/detail_stage3.php',
                    data: {
                        stageId: stageId
                    },
                    type: 'post',
                    async: false,
                    success: function(output) {
                        $('.stage_open').css('display', 'none');
                        $('.div_stage_detail').html('');
                        $('#div_stage_' + stageId).html(output);
                        $('#tr_stage_' + stageId).css('display', 'table-row');
                    }
                });
            } else {
                $(this).attr('isOpen', 0);
                $('.stage_open').css('display', 'none');
                $('.div_stage_detail').html('');
            }
        });

        $(document).on('click', '#cancel_card_stage_detail', function() {
            $('.fa-search').find().each(function(el) {
                $(el).attr('isOpen', 0);
            })
            $('.stage_open').css('display', 'none');
            $('.div_stage_detail').html('');
        })

        $(document).on('click', '.close_stagiaires', function() {
            $("table[id^=table_]").remove();
            $("tr[id^=tr_stagiaire_]").hide();
        });


        $(document).on('click', '.fa-user-plus', function() {
            var stageId = jQuery(this).attr("id_stage");
            var isOpen = parseInt($(this).attr('isOpen'));
            var cancel = parseInt($(this).attr('cancel'));
            var nb_inscrit = parseInt($(this).attr('nb_inscrit'));

            if (isOpen == 0 && cancel == 0 && nb_inscrit < 20) {
                $(this).attr('isOpen', 1);
                $.ajax({
                    url: 'stage/form_stagiaire_externe.php',
                    data: {
                        stageId: stageId
                    },
                    type: 'post',
                    async: false,
                    success: function(output) {
                        $('.stagiaire_externe_open').css('display', 'none');
                        $('.div_stage_detail').html('');
                        $('#div_stagiaire_externe_' + stageId).html(output);
                        $('#tr_stagiaire_externe_' + stageId).css('display', 'table-row');
                        initAddressAS();
                        initCityAS();
                    }
                });
            } else {
                $(this).attr('isOpen', 0);
                $('.stagiaire_externe_open').css('display', 'none');
                $('.div_stage_detail').html('');
            }
        });

        $(document).on('click', '.fa-lightbulb', function() {
            var ethis = $(this);
            var id_stage = jQuery(this).attr("id_stage");
            var online = parseInt($(this).attr('online')) == 0 ? 1 : 0;
            var is_cancel = parseInt($(this).attr('cancel'));

            if (online == 0) {

                var htmlAlert = '<div style="font-size: 16px; margin-bottom: 15px">Vous êtes sur le point de mettre ce ' +
                    'stage hors-ligne. Il ne sera plus diffusé sur la Plateforme ProStagesPermis. ' +
                    'Vous pouvez le remettre en ligne à tout moment. Ce n\'est donc pas une annulation de stage. ' +
                    'Si vous souhaitez annuler définitivement ce stage et envoyer un mail automatique d\'annulation ' +
                    'à chaque stagiaire, cliquez sur le picto "Annuler le stage" dans la colonne "Actions." <br><br>' +
                    'Souhaitez-vous mettre ce stage hors-ligne ?' +
                    '</div>';

                swal({
                    html: htmlAlert,
                    type: 'info',
                    showCloseButton: true,
                    showCancelButton: true,
                    confirmButtonColor: '#62AA2E',
                    cancelButtonColor: '#808080',
                    confirmButtonText: 'Confirmer',
                    cancelButtonText: 'Annuler',
                    reverseButtons: true
                }).then(function(result) {
                    if (result) {
                        $.ajax({
                            url: 'ajax_functions2024.php',
                            data: {
                                action: 'update_diffusion',
                                id_stage: id_stage,
                                online: online
                            },
                            type: 'post',
                            success: function(output) {
                                affiche_stages();
                            }
                        });
                    }
                })

            } else {
                $.ajax({
                    url: 'ajax_functions2024.php',
                    data: {
                        action: 'update_diffusion',
                        id_stage: id_stage,
                        online: online
                    },
                    type: 'post',
                    success: function(output) {
                        affiche_stages();
                    }
                });
            }
        });

        $(document).on('click', '.fa-times-circle', function() {

            if (!$(this).hasClass('disabled')) {
                var id_stage = jQuery(this).attr("id_stage");
                var isBefore48h = jQuery(this).attr("isBefore48h");

                if (isBefore48h == '1') {
                    var htmlAlert = '<div style="font-size: 14px; margin-bottom: 15px"><b style="color:red;">ATTENTION !</b><br><br>' +
                        '<p style="color:red;">Vous êtes sur le point d\'annuler un stage <b>à moins de 48 heures de son début</b>. </p>' +
                        '<p style="color:red;"><b>Une telle annulation ne peut intervenir qu’en cas de force majeure !</b></p>' +
                        '<p style="color:red;">Les annulations de dernière minute génèrent un fort mécontentement auprès des stagiaires et compliquent leur organisation. Merci de les limiter autant que possible.</p>' +
                        '<p style="padding-top:10px;">Une fois l\'annulation effectuée, le stage ne sera plus visible sur notre Plateforme et tous les stagiaires inscrits sur cette session recevront un mail et un SMS automatiques les informant de l\'annulation de leur inscription. </p>' +
                        '</div>' +
                        '<div style="font-size: 14px; margin-bottom: 15px">Pour confirmer cette action, sélectionnez le motif d\'annulation ci-dessous ' +
                        'et cliquez sur le bouton "Confirmer"' +
                        '</div>';
                } else {
                    var htmlAlert = '<div style="font-size: 14px; margin-bottom: 15px">Vous êtes sur le point d\'annuler ce stage. Le stage ne sera plus visible ' +
                        'sur notre Plateforme et tous les stagiaires inscrits sur cette session recevront un mail et un SMS ' +
                        'automatiques les informant de l\'annulation de leur inscription. ' +
                        '</div>' +
                        '<div style="font-size: 14px; margin-bottom: 15px">Pour confirmer cette action, sélectionnez le motif d\'annulation ci-dessous ' +
                        'et cliquez sur le bouton "Confirmer"' +
                        '</div>';
                }



                swal({
                    //text: 'Votre stage va être mis hors ligne, Etes-vous sur de vouloir l\'annuler ?',
                    html: htmlAlert,
                    type: 'info',
                    showCloseButton: true,
                    showCancelButton: true,
                    confirmButtonColor: '#62AA2E',
                    cancelButtonColor: '#808080',
                    confirmButtonText: 'Confirmer',
                    cancelButtonText: 'Annuler',
                    reverseButtons: true,
                    input: 'select',
                    inputPlaceholder: 'Motif annulation',
                    inputOptions: {
                        '0': 'Annulation faute de participants',
                        '1': 'Annulation faute d\'animateurs',
                        '2': 'Annulation faute de salle'
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
                        $.ajax({
                            url: '/src/stage/ajax/cancel_stage.php',
                            data: {
                                id_stage: id_stage,
                                motif: result
                            },
                            type: 'post',
                            success: function(output) {

                                var htmlConfirm = '<div style="font-size: 14px; margin-bottom: 15px">Votre stage a été annulé. Mais la procédure n\'est pas terminée. <br>' +
                                    'Il vous reste à vérifier que tous les stagiaires ont bien reçus un mail automatique les informant de l\'annulation du stage, ' +
                                    'Pour cela, sur la ligne du stage concerné, cliquez sur le picto jaune "Voir la liste des stagiaires sur ce stage" et vérifiez' +
                                    ' que le statut de chaque stagiaire inscrit à cette session soit bien passé en statut "Annulé". <br> Si ce n\'est pas le cas, ' +
                                    'annulez manuellement chaque stagiaire  à l\'aide du bouton "Annuler" pour l\'informer par mail automatique. ' +
                                    'Si le statut est passé en "Annulé" vous n\'avez plus rien à faire.' +
                                    '</div>';

                                swal({
                                    type: 'info',
                                    html: htmlConfirm,
                                    //text: 'Votre stage est désormais hors ligne .Pour supprimer les stagiaires inscrits, accédez à la liste des candidats (en cliquant sur le Picto correspondant) et supprimez les un par un',
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'OK'
                                }).then(function(result) {
                                    affiche_stages();
                                });
                            }
                        });
                    }
                })
            }
        });

        $(document).on('click', '.cancel_student_stage', function() {

            if ($(this).hasClass('disabled')) return;

            var id_stagiaire = parseInt($(this).attr('id_stagiaire'));
            var id_stage = parseInt($(this).attr('id_stage'));

            var table = parseInt($(this).attr('table'));
            var ethis = $(this);

            if (table == 2) {
                swal({
                    type: 'warning',
                    text: 'Attention, vous êtes sur le point de supprimer définitivement ce stagiaire. Voulez-vous continuer ?',
                    showCancelButton: true,
                    confirmButtonColor: '#62AA2E',
                    cancelButtonColor: '#808080',
                    confirmButtonText: 'Confirmer',
                    cancelButtonText: 'Annuler',
                    reverseButtons: true
                }).then(function(text) {
                    $.ajax({
                        url: 'ajax_functions2024.php',
                        data: {
                            action: 'delete_stagiaire_externe',
                            id_stage: id_stage,
                            id_stagiaire: id_stagiaire
                        },
                        type: 'post',
                        success: function(output) {
                            affiche_stages();
                        }
                    });
                });

                return;
            }

            var isBefore48h = jQuery(this).attr("isBefore48h");

            if (isBefore48h == '1') {
                var htmlAlert = '<div style="font-size: 14px; margin-bottom: 15px"><b style="color:red;">ATTENTION !</b><br><br>' +
                    '<p style="color:red;">Vous êtes sur le point d\'annuler l\'inscription de ce stagiaire <b>à moins de 48 heures de son début</b>. </p>' +
                    '<p style="color:red;"><b>Une telle annulation ne peut intervenir qu’en cas de force majeure !</b></p>' +
                    '<p style="color:red;">Les annulations de dernière minute génèrent un fort mécontentement auprès des stagiaires et compliquent leur organisation. Merci de les limiter autant que possible.</p>' +
                    '<p style="padding-top:10px;">Une fois l\'annulation effectuée, le stagiaire recevra un mail et un SMS automatiques l\'informant de l\'annulation de son inscription.</p>' +
                    '</div>' +
                    '<div style="font-size: 14px; margin-bottom: 15px">Pour confirmer cette action, sélectionnez le motif d\'annulation ci-dessous ' +
                    'et cliquez sur le bouton "Confirmer"' +
                    '</div>';
            } else {
                var htmlAlert = '<div style="font-size: 14px; margin-bottom: 15px">Vous êtes sur le point d\'annuler l\'inscription de ce stagiaire. Une fois l\'annulation effectuée, le stagiaire recevra un mail et un SMS automatiques l\'informant de l\'annulation de son inscription.<br><br> Pour confirmer cette action, sélectionnez le motif d\'annulation ci-dessous et cliquez sur le bouton "Confirmer"' +
                    '</div>';
            }

            swal({
                html: htmlAlert,
                type: 'info',
                showCloseButton: true,
                showCancelButton: true,
                confirmButtonColor: '#62AA2E',
                cancelButtonColor: '#808080',
                confirmButtonText: 'Confirmer',
                cancelButtonText: 'Annuler',
                reverseButtons: true,
                input: 'select',
                inputPlaceholder: 'Motif annulation',
                inputOptions: {
                    '0': 'Annulation faute de participants',
                    '1': 'Annulation faute d\'animateurs',
                    '2': 'Annulation faute de salle',
                    '3': 'Stage déjà complet au moment de la réservation',
                    '4': 'Stage inapproprié à la situation du stagiaire'
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

                    $.ajax({
                        url: 'ajax_functions2024.php',
                        data: {
                            action: 'annulation_inscription',
                            id_stage: id_stage,
                            id_stagiaire: id_stagiaire,
                            motif: result,
                            table: table
                        },
                        type: 'post',
                        success: function(output) {
                            $.ajax({
                                url: '../mails_v3/ajax_actions.php',
                                data: {
                                    action: 'mail_annulation_centre',
                                    id: id_stagiaire
                                },
                                type: 'post'
                            });

                            $.ajax({
                                url: '../mails_v3/ajax_actions.php',
                                data: {
                                    action: 'annulation_stagiaire',
                                    id: id_stagiaire
                                },
                                type: 'post'
                            }).then(function() {
                                var htmlConfirm = '<div style="font-size: 14px; margin-bottom: 15px">La participation du stagiaire a été annulée. Mais la procédure n\'est pas terminée. <br>' +
                                    'Il vous reste à vérifier que le stagiairee ait bien reçu un mail automatique l\'informant de l\'annulation du stage, ' +
                                    'Pour cela, sur la ligne du stage concerné, cliquez sur le picto jaune "Voir la liste des stagiaires sur ce stage" et vérifiez' +
                                    ' que le statut du stagiaire inscrit à cette session soit bien passé en statut "Annulé".' +
                                    '</div>';

                                swal({
                                    type: 'info',
                                    html: htmlConfirm,
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'OK'
                                }).then(function(result) {
                                    window.location.reload();
                                });
                            });

                            ethis.closest('tr.ligne').find('.status').removeClass("inscrit").addClass("annule");
                        }
                    });
                }
            })
        });

        $(document).on('click', '.fa-mobile', function() {
            var id_stage = jQuery(this).attr("id_stage");
            var id_statut = jQuery(this).attr("id_statut");
            $.ajax({
                url: 'ajax_functions2024.php',
                data: {
                    action: 'pass_sanitaire',
                    id_stage: id_stage,
                    id_statut: id_statut
                },
                type: 'post',
                success: function(output) {
                    location.reload();
                }
            });
        });

        $(document).on('change', '.update_passvacinnal', function() {
            var stageId = $(this).attr("stageId");
            var id_statut = $(this).val();
            $.ajax({
                url: 'ajax_functions2024.php',
                data: {
                    action: 'pass_sanitaire',
                    id_stage: stageId,
                    id_statut: id_statut
                },
                type: 'post',
                success: function(output) {}
            });
        });

        $(document).on('click', '.fa-thumbs-o-up', function() {
            var id_stage = jQuery(this).attr("id_stage");
            var partenariat = jQuery(this).attr("id_statut");
            var prix_index_ttc = $('#select_stage_prix_' + id_stage).val();
            $.ajax({
                url: 'ajax_functions2024.php',
                data: {
                    action: 'partenariat',
                    id_membre: id_membre,
                    id_stage: id_stage,
                    prix_index_ttc: prix_index_ttc,
                    partenariat: partenariat
                },
                type: 'post',
                success: function(output) {
                    location.reload();
                }
            });
        });

        $(document).on('change', '.update_status_animator_stage', function() {
            var stageId = jQuery(this).attr("stageId");
            var value = $(this).val();
            $.ajax({
                url: 'stage/update_one_field.php',
                data: {
                    field: 'status_stage_for_animator',
                    value: value,
                    id: stageId
                },
                type: 'post',
                success: function(output) {}
            });
        });

        $(document).on('click', '.update_stage_information', function() {
            var stageId = jQuery(this).attr("stageId");
            var value = $('#content_information_' + stageId).val();
            $.ajax({
                url: 'stage/update_one_field.php',
                data: {
                    field: 'information_stage_animator',
                    value: value,
                    id: stageId
                },
                type: 'post',
                success: function(output) {}
            });
        });

        $(document).on('click', '.place_container', function() {

            var id_stage = jQuery(this).attr("id_stage");

            if ($(this).find('.select_place').length != 1) { //vérifie l'existence de l'élément

                var selected;
                var place = $(this).find('.place').html();
                place = parseInt(place);

                $(".place").show();
                $(".select_place").remove();
                $(this).find('.place').hide();

                var ethis = jQuery(this);

                var sel = '<select class="select_place form-control" id_stage="' + id_stage + '">';
                for (i = 1; i <= 20; i++) {
                    selected = place == i ? "selected" : "";
                    sel += '<option value="' + i + '" ' + selected + '>' + i + '</option>';
                }
                sel += '</select>';

                $(this).append(sel);
            }
            return false;
        });

        $(document).on('click', '.bafm_container', function() {

            var id_stage = jQuery(this).attr("id_stage");
            var id_bafm = jQuery(this).attr("id_bafm");
            var obj;

            if ($(this).find('.select_bafm').length != 1) { //vérifie l'existence de l'élément

                $.ajax({
                    url: 'ajax_functions2024.php',
                    data: {
                        action: 'liste_bafm'
                    },
                    type: 'post',
                    async: false,
                    success: function(output) {
                        obj = output;
                    },
                    dataType: "json"
                });


                var selected = '';
                var bafm = $(this).find('.bafm').html();

                $(".bafm").show();
                $(".select_bafm").remove();
                $(this).find('.bafm').hide();

                var ethis = jQuery(this);

                var sel = '<select class="select_bafm form-control" id_stage="' + id_stage + '">';
                sel += '<option value=0>Bafm à définir</option>';
                $.each(obj, function() {
                    selected = id_bafm == this['id'] ? "selected" : "";
                    sel += '<option value="' + this['id'] + '" ' + selected + '>' + this['nom'] + ' ' + this['prenom'] + '</option>';
                });

                sel += '</select>';


                $(this).append(sel);
            }
            return false;
        });

        $(document).on('click', '.psy_container', function() {

            var id_stage = jQuery(this).attr("id_stage");
            var id_psy = jQuery(this).attr("id_psy");
            var obj;

            if ($(this).find('.select_psy').length != 1) { //vérifie l'existence de l'élément

                $.ajax({
                    url: 'ajax_functions2024.php',
                    data: {
                        action: 'liste_psy'
                    },
                    type: 'post',
                    async: false,
                    success: function(output) {
                        obj = output;
                    },
                    dataType: "json"
                });

                var selected = '';
                var psy = $(this).find('.psy').html();

                $(".psy").show();
                $(".select_psy").remove();
                $(this).find('.psy').hide();

                var ethis = jQuery(this);

                var sel = '<select class="select_psy form-control" id_stage="' + id_stage + '">';
                sel += '<option value=0>Psy à définir</option>';
                $.each(obj, function() {
                    selected = id_psy == this['id'] ? "selected" : "";
                    sel += '<option value="' + this['id'] + '" ' + selected + '>' + this['nom'] + ' ' + this['prenom'] + '</option>';
                });

                sel += '</select>';

                $(this).append(sel);
            }
            return false;
        });

        $(document).on('change', '.select_stage_prix', function() {
            var partenaire = jQuery(this).attr("partenaire");
            var id_stage = jQuery(this).attr("id_stage");
            var prix = jQuery(this).val();
            /*
            var commission = $('#reversement_' + id_stage).attr('commission');
            var reversement = parseFloat((prix - (commission * 1.2))).toFixed(2);*/

            $.ajax({
                url: 'ajax_commission2024_get.php',
                data: {
                    id: 0,
                    id_s: id_stage
                },
                type: 'post',
                async: false,
                success: function(output) {
                    commission2024 = $.parseJSON(output);
                }
            });
            var cos2024 = -1;
            var cop2024 = -1;
            $.each(commission2024, function(i, l) {
                if (parseInt(prix) == parseInt(l.pmi) || parseInt(prix) == parseInt(l.pma) || (parseInt(prix) > parseInt(l.pmi) && parseInt(prix) < parseInt(l.pma))) {
                    cos2024 = parseInt(l.cos);
                    cop2024 = parseInt(l.cop);
                }
            });
            if (cos2024 > -1) {
                // Nouvelle commission 2024
                if (partenaire == 1) {
                    commission = (parseFloat(cos2024)).toFixed(2);
                    reversement = parseFloat((prix - (commission * 1.2))).toFixed(2);
                } else {
                    commission = (parseFloat(cop2024)).toFixed(2);
                    reversement = parseFloat((prix - (commission * 1.2))).toFixed(2);
                }
            }
            /*
            $('#reversement_' + id_stage).html(reversement);
            $('#detail_reversement_' + id_stage).html(reversement + ' €');*/

            $.ajaxSetup({
                cache: false
            });
            $.ajax({
                url: 'ajax_functions2024.php',
                data: {
                    action: 'update_prix',
                    id_membre: id_membre,
                    id_stage: id_stage,
                    prix: prix,
                    partenaire: partenaire
                },
                type: 'post',
                success: function(commission) {
                    var reversement = parseFloat((prix - (commission * 1.2))).toFixed(2);
                    $('#reversement_' + id_stage).html(reversement);
                    $('#detail_reversement_' + id_stage).html(reversement + ' €');
                    $('#stage_price_' + id_stage).val(prix);
                    //location.reload();
                }
            });
        });

        $(document).on('change', '.select_stage_nbplaces_max', function() {
            var id_stage = jQuery(this).attr("id_stage");
            var place = jQuery(this).val();
            var nb_inscrits = parseInt(jQuery(this).attr("nb_inscrits"));
            var nbAllouees = parseInt(jQuery(this).attr("nb_allouees"));

            $.ajaxSetup({
                cache: false
            });
            $.ajax({
                url: 'ajax_functions2024.php',
                data: {
                    action: 'update_max_places',
                    place: place,
                    id_stage: id_stage,
                    nb_inscrits: nb_inscrits
                },
                type: 'post',
                success: function(output) {
                    if (nbAllouees == 0) {
                        swal({
                            type: 'info',
                            title: 'Ré-activation de votre stage',
                            html: 'Le nombre de places maximum a bien été mis à jour.<br><br>Cependant, pour que votre stage soit de nouveau visible, <b>veuillez cliquer sur l\'icône de mise en ligne de votre stage</b> ( <i class="fas fa-lightbulb fa-2x offline" style=";font-size:18px; color: #EC971F!important;margin-left:0!important;pointer-events:none;"></i> )',
                            showCancelButton: false,
                            confirmButtonText: 'OK'
                        }).then(function() {
                            affiche_stages();
                        });
                    } else {
                        affiche_stages();
                    }

                    $('#select_stage_nbplaces_max_' + id_stage).val(place);
                }
            });
        });

        $(document).on('change', '.select_place', function() {

            var id_stage = jQuery(this).attr("id_stage");
            var ethis = this;
            var ethis2 = $(this);

            $.ajaxSetup({
                cache: false
            });
            $.ajax({
                url: 'ajax_functions2024.php',
                data: {
                    action: 'update_places',
                    id_stage: id_stage,
                    place: ethis.value
                },
                type: 'post',
                success: function(output) {
                    ethis2.closest('.place_container').find('.place').html(ethis.value);
                    ethis2.closest('.place_container').find('.place').show();
                    ethis2.remove();
                }
            });
        });

        $(document).on('change', '#departement_filter', function() {
            var codeDepartment = jQuery(this).val();
            $.ajax({
                url: '/src/site/ajax/site_by_department.php',
                data: {
                    department: codeDepartment,
                    id_member: <?php echo $membre ?>
                },
                type: 'post',
                success: function(output) {
                    $('#site').html(output)
                }
            });
        })

        $(document).on('change', '.select_bafm', function() {

            var id_stage = jQuery(this).attr("id_stage");
            var ants_numero = jQuery(this).attr("ants_numero");
            var ethis = this;
            var ethis2 = $(this);

            $.ajaxSetup({
                cache: false
            });
            $.ajax({
                url: 'ajax_functions2024.php',
                data: {
                    action: 'update_bafm',
                    id_stage: id_stage,
                    bafm: ethis.value,
                    ants_numero: ants_numero
                },
                type: 'post',
                success: function(output) {
                    //;location.reload();
                }
            });
        });

        $(document).on('change', '.select_psy', function() {

            var id_stage = jQuery(this).attr("id_stage");
            var ants_numero = jQuery(this).attr("ants_numero");
            var ethis = this;
            var ethis2 = $(this);

            $.ajaxSetup({
                cache: false
            });
            $.ajax({
                url: 'ajax_functions2024.php',
                data: {
                    action: 'update_psy',
                    id_stage: id_stage,
                    psy: ethis.value,
                    ants_numero: ants_numero
                },
                type: 'post',
                success: function(output) {
                    location.reload();
                }
            });
        });

        $(document).on('change', '.select_hours2', function() {
            var id_stage = jQuery(this).attr("id_stage");
            var hour_type = jQuery(this).attr("hour_type");
            var ants_numero = jQuery(this).attr("ants_numero");
            var hour = jQuery(this).val();

            $.ajax({
                url: 'ajax_functions2024.php',
                data: {
                    action: 'update_hour',
                    id_stage: id_stage,
                    hour: hour,
                    hour_type: hour_type,
                    ants_numero: ants_numero
                },
                type: 'post',
                success: function(output) {
                    $.ajax({
                        url: '../mails_v3/ajax_actions.php',
                        data: {
                            action: 'mail_changement_horaire_centre',
                            stageId: id_stage
                        },
                        type: 'post',
                        success: function(output) {
                            $.ajax({
                                url: '../mails_v3/ajax_actions.php',
                                data: {
                                    action: 'mail_changement_horaire_stagiaire',
                                    stageId: id_stage
                                },
                                type: 'post',
                                success: function(output) {
                                    swal({
                                        title: "<div style='font-weight:bold;color:red'>ATTENTION<div",
                                        html: "<div>Vous venez de modifier les horaires de votre stage. Un mail de confirmation vient de vous être envoyé et les stagiaires ont été informés de ce changement d'horaires par mail automatique</div>",
                                        type: 'info',
                                        showCloseButton: true,
                                        showCancelButton: false,
                                        confirmButtonColor: '#333333',
                                        confirmButtonText: 'Fermer',
                                    });
                                }
                            });
                        }
                    });
                }
            });
        });

        $(document).on('click', '.open_declaration', function() {

            var stageId = $(this).attr('id_stage');
            var memberId = $(this).attr('member_id');
            var isDeclareAnts = parseInt($(this).attr('is_declare_ants'));
            var cancel = parseInt($(this).attr('cancel'));

            if (!isDeclareAnts && !cancel) {
                AntsCommonUtils.DeclareAnts(stageId, memberId);
            }

        });

        $(document).on('click', '.ls-modal22', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            $('#popupIframe').attr('src', url);
            $('#addStagePopup').modal('show');
        });

        $(document).on('click', '.ls-modal', function(e) {
            e.preventDefault();
            $('#myModal').modal('show').find('.modal-body').load($(this).attr('href'));
        });

        $(document).on('click', '.ls-modal2', function(e) {
            e.preventDefault();
            $('#myModal2').modal('show').find('.modal-body').load($(this).attr('href'));
        });

        $(document).on('click', '.download_all_attestation_signee', function() {
            var stageId = $(this).attr("stageId");
            var dateStage = $(this).attr("date");

            swal({
                title: "<div style='width:100%;color:#062B4E;background:white;padding-top:20px;padding-bottom:20px;'>Téléchargement du dossier des attestations signées</div>",
                html: "<div style='color:#062B4E'><img src='../images/progress.gif'><br><br>Traitement en cours...<div>",
                showCancelButton: true,
                cancelButtonText: 'Annuler',
                cancelButtonColor: '#808080',
                showConfirmButton: false,
            }).then(function(result) {
                $.LoadingOverlay("hide");
            });
            setTimeout(function(e) {
                $.ajax({
                    url: '/document/download/download_attestation_signee.php',
                    data: {
                        stageId: stageId,
                        dateStage: dateStage
                    },
                    type: 'post',
                    async: false,
                    success: function(output) {
                        if (output != '') {
                            var file_path = output;
                            var a = document.createElement('A');
                            a.href = file_path;
                            a.download = file_path.substr(file_path.lastIndexOf('/') + 1);
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                            swal.close();
                        } else {
                            swal({
                                title: 'Attestation',
                                text: 'Vous n\'avez encore chargé aucune attestation signée.',
                                type: 'warning',
                                showCancelButton: false,
                                confirmButtonText: 'OK'
                            }).then(function() {});
                        }
                    }
                });
            }, 1000);

        });

        $(document).on('change', '.stage_price', function() {
            var partenaire = $(this).attr("partenaire");
            var stageId = $(this).attr("stageId");
            var prix = $(this).val();

            var commission = $('#detail_reversement_' + stageId).attr('commission');
            var reversement = parseFloat((prix - (commission * 1.2))).toFixed(2);
            $.ajax({
                url: 'ajax_commission2024_get.php',
                data: {
                    id: 0,
                    id_s: stageId
                },
                type: 'post',
                async: false,
                success: function(output) {
                    commission2024 = $.parseJSON(output);
                }
            });
            var cos2024 = -1;
            var cop2024 = -1;
            $.each(commission2024, function(i, l) {
                if (parseInt(prix) == parseInt(l.pmi) || parseInt(prix) == parseInt(l.pma) || (parseInt(prix) > parseInt(l.pmi) && parseInt(prix) < parseInt(l.pma))) {
                    cos2024 = parseInt(l.cos);
                    cop2024 = parseInt(l.cop);
                }
            });
            if (cos2024 > -1) {
                // Nouvelle commission 2024
                if (partenaire == 1) {
                    commission = (parseFloat(cos2024)).toFixed(2);
                    reversement = parseFloat((prix - (commission * 1.2))).toFixed(2);
                } else {
                    commission = (parseFloat(cop2024)).toFixed(2);
                    reversement = parseFloat((prix - (commission * 1.2))).toFixed(2);
                }
            }
            /*
            $('#reversement_' + stageId).html(reversement);
            $('#detail_reversement_' + stageId).html(reversement + ' €');*/

            $.ajaxSetup({
                cache: false
            });
            $.ajax({
                url: 'ajax_functions2024.php',
                data: {
                    action: 'update_prix',
                    id_membre: id_membre,
                    id_stage: stageId,
                    prix: prix,
                    partenaire: partenaire
                },
                type: 'post',
                success: function(commission) {
                    var reversement = parseFloat((prix - (commission * 1.2))).toFixed(2);
                    $('#reversement_' + stageId).html(reversement);
                    $('#detail_reversement_' + stageId).html(reversement + ' €');
                    $('#select_stage_prix_' + stageId).val(prix);
                }
            });
        });

        $(document).on('change', '.update_partenariat', function() {
            var stageId = $(this).attr("stageId");
            var partenariat = $(this).val();
            var prix_index_ttc = $('#stage_price_' + stageId).val();
            $.ajax({
                url: 'ajax_functions2024.php',
                data: {
                    action: 'partenariat',
                    id_membre: id_membre,
                    id_stage: stageId,
                    prix_index_ttc: prix_index_ttc,
                    partenariat: partenariat
                },
                type: 'post',
                success: function(commission) {
                    var reversement = parseFloat((prix_index_ttc - (commission * 1.2))).toFixed(2);
                    $('#reversement_' + stageId).html(reversement);
                    $('#detail_reversement_' + stageId).html(reversement + ' €');
                    $('#stage_price_' + stageId).attr('partenaire', partenariat);
                    if (parseInt(partenariat) == 1) {
                        $('#i_activate_partenaire_' + stageId).css('color', 'grey')
                    } else {
                        $('#i_activate_partenaire_' + stageId).css('color', '#EC971F')
                    }
                }
            });
        });

        $(document).on('change', '.select_gta', function() {

            var stageId = $(this).attr('id_stage');
            var id_bafm = $(this).attr('id_bafm');
            var id_psy = $(this).attr('id_psy');
            var value = $(this).val();

            if (value == 'bafm' && !parseInt(id_bafm)) {
                swal({
                    title: 'Erreur Gta !',
                    text: 'Vous devez sélectionné votre animateur BAFM avant le Gta.',
                    type: 'warning',
                    showCancelButton: false,
                    confirmButtonText: 'OK'
                }).then(function(result) {
                    location.reload();
                });

                return false;
            }

            if (value == 'psy' && !parseInt(id_psy)) {
                swal({
                    title: 'Erreur Gta !',
                    text: 'Vous devez sélectionné votre animateur PSY avant le Gta.',
                    type: 'warning',
                    showCancelButton: false,
                    confirmButtonText: 'OK'
                }).then(function(result) {
                    location.reload();
                });

                return false;
            }

            var id_gta = 0;

            if (value != '') {
                var id_gta = value == 'psy' ? id_psy : id_bafm;
            }


            $.ajax({
                url: 'stage/update_one_field.php',
                data: {
                    field: 'id_gta',
                    value: id_gta,
                    id: stageId
                },
                type: 'post',
                success: function(output) {
                    affiche_stages();
                }
            });

        });

        $(document).on('click', '.confirm_stage', function() {
            var stageId = $(this).attr('stageId');
            var email_bafm = $(this).attr('email_bafm');
            var email_psy = $(this).attr('email_psy');

            if (email_bafm.length == 0 || email_psy.length == 0) {
                swal({
                    title: 'l\'adresse email des animateurs n\'ont pas été renseigné par le centre',
                    type: 'info',
                    showCloseButton: true,
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK',
                });
                return false;
            }

            swal({
                title: "Vous êtes sur le point de confirmer le stage aux animateurs",
                text: "Souhaitez-vous confirmer cette action ?",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: 'Valider',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#64a328',
                cancelButtonColor: '#808080',
                reverseButtons: true
            }).then(function(result) {
                if (result) {
                    $.ajax({
                        url: 'stage/confirm_animator.php',
                        data: {
                            field: 'status_stage_for_animator',
                            value: 1,
                            id: stageId,
                            email_psy: email_psy,
                            email_bafm: email_bafm
                        },
                        type: 'post',
                        success: function(output) {
                            table.ajax.reload();
                        }
                    });
                }
            });
        });

        $(document).on('click', '.confirm_salle', function() {
            var stageId = $(this).attr('stageId');
            var email = $(this).attr('email');

            if (email.length == 0) {
                swal({
                    title: 'l\'adresse email de l\'hôtel n\'a pas été renseigné par le centre',
                    type: 'info',
                    showCloseButton: true,
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK',
                });
                return false;
            }

            swal({
                title: "Vous êtes sur le point d'envoyer 1 email de confirmation à l'hôtel.",
                text: "Souhaitez-vous continuer ?",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: 'Valider',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#64a328',
                cancelButtonColor: '#808080',
                reverseButtons: true
            }).then(function(result) {
                if (result) {
                    $.ajax({
                        url: 'stage/send_confirmation_email.php',
                        data: {
                            stageId: stageId,
                            email: email
                        },
                        type: 'post',
                        async: false,
                        success: function(output) {
                            window.location.reload();
                        }
                    });
                }
            });
        });

        $(document).on('click', '.search_animator', function() {
            var stageId = jQuery(this).attr("id_stage");
            var searchId = jQuery(this).attr("id_search");
            var isOpen = parseInt($(this).attr('isOpen'));
            var type = $(this).attr('data-type');

            if (isOpen == 0) {
                $(this).attr('isOpen', 1);
                $.ajax({
                    url: '/src/search_animator/ajax/detail_search_animator.php',
                    data: {
                        stageId: stageId,
                        searchId: searchId,
                        type: type
                    },
                    type: 'post',
                    async: false,
                    success: function(output) {
                        $('.recherche_animator_open').css('display', 'none');
                        $('.div_recherche_animator').html('');
                        $('#div_recherche_animator_' + stageId).html(output);
                        $('#tr_recherche_animator_' + stageId).css('display', 'table-row');
                    }
                });
            } else {
                $(this).attr('isOpen', 0);
                $('.recherche_animator_open').css('display', 'none');
                $('.div_recherche_animator').html('');
            }
        });

        $(document).on('change', '.update_send_ants', function() {
            var provenance = $(this).attr("provenance");
            var studentId = $(this).attr("id_stagiaire");
            var value = $(this).val();
            $.ajax({
                url: '/src/student/ajax/update_one_field.php',
                data: {
                    id: studentId,
                    field: 'is_send_directory_to_ants',
                    value: value,
                    provenance: provenance
                },
                type: 'post',
                async: false,
                success: function(output) {}
            });
        });

        $(document).on('click', '.current_search_animator', function() {
            var searchId = jQuery(this).attr("id_search");
            var stageId = jQuery(this).attr("id_stage");
            var isOpen = parseInt($(this).attr('isOpen'));
            var type = $(this).attr('data-type');

            if (isOpen == 0) {
                $(this).attr('isOpen', 1);
                $.ajax({
                    url: '/src/search_animator/ajax/detail_current_search_animator.php',
                    data: {
                        searchId: searchId,
                        stageId: stageId,
                        type: type
                    },
                    type: 'post',
                    async: false,
                    success: function(output) {
                        $('.recherche_animator_open').css('display', 'none');
                        $('.div_recherche_animator').html('');
                        $('#div_recherche_animator_' + stageId).html(output);
                        $('#tr_recherche_animator_' + stageId).css('display', 'table-row');
                    }
                });
            } else {
                $(this).attr('isOpen', 0);
                $('.recherche_animator_open').css('display', 'none');
                $('.div_recherche_animator').html('');
            }
        });

        $(document).on('click', '.historic_search_animator', function() {

            var searchId = jQuery(this).attr("id_search");
            var stageId = jQuery(this).attr("id_stage");
            var isOpen = parseInt($(this).attr('isOpen'));
            var type = $(this).attr('data-type');

            if (isOpen == 0) {
                $(this).attr('isOpen', 1);
                $.ajax({
                    url: '/src/search_animator/ajax/historic_current_search_animator.php',
                    data: {
                        searchId: searchId,
                        stageId: stageId,
                        type: type
                    },
                    type: 'post',
                    async: false,
                    success: function(output) {
                        $('.recherche_animator_open').css('display', 'none');
                        $('.div_recherche_animator').html('');
                        $('#div_recherche_animator_' + stageId).html(output);
                        $('#tr_recherche_animator_' + stageId).css('display', 'table-row');
                    }
                });
            } else {
                $(this).attr('isOpen', 0);
                $('.recherche_animator_open').css('display', 'none');
                $('.div_recherche_animator').html('');
            }

        });

        $(document).on('click', '.init_search_animator', function() {
            var stageId = $(this).attr("stage_id");
            var type = $(this).attr('type');

            var html = "<div>Vous êtes sur le point de lancer une recherche d'animateur pour ce stage. Une alerte va être envoyée à tous les animateurs qui interviennent sur ce secteur.</div>"
            html += "<div style='margin-top: 20px; font-size: 16px'>Souhaitez-vous confirmer cette action ?</div>"

            html += '<div style="text-align: left; margin-top: 40px"><label style="width: 40%; font-size: 16px">Forfait proposé</label>' +
                '<input style="width: 30%" type="text" id="forfait">' +
                '</div>';
            html += '<div style="text-align: left; margin-top: 20px"><label style="width: 40%; font-size: 16px">GTA indispensable</label>' +
                '<select name="" id="gta_indispensable" style="width: 30%">' +
                '<option value="0"></option><option value="1">Oui</option><option value="2">Non</option>' +
                '</select>' +
                '</div>';

            var has_gta = $('#gta_indispensable').val();
            var forfait = $('#forfait').val();

            swal({
                type: 'warning',
                html: html,
                showCancelButton: true,
                confirmButtonText: 'Valider',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#64a328',
                cancelButtonColor: '#808080',
                reverseButtons: true,
                preConfirm: function(resolve, reject) {
                    return new Promise(function(resolve, reject) {
                        has_gta = $('#gta_indispensable').val();
                        forfait = $('#forfait').val();
                        $.ajax({
                            url: '/src/search_animator/ajax/init_search_animator.php',
                            data: {
                                stageId: stageId,
                                type: type,
                                has_gta: has_gta,
                                forfait: forfait
                            },
                            type: 'post',
                            async: false,
                            success: function(output) {
                                resolve()
                            }
                        });
                    });

                }
            }).then(function(result) {
                if (result) {
                    window.location.reload();
                }
            });
        });

        $(document).on('change', '#update_gta_search', function() {
            var idSearch = $(this).attr('id_search');
            var value = $(this).val();
            $.ajax({
                url: '/src/search_animator/ajax/update_one_field_search_animator.php',
                data: {
                    id: idSearch,
                    value: value,
                    field: 'is_gta_required'
                },
                type: 'post',
                async: false,
                success: function(output) {}
            });
        });

        $(document).on('click', '.reload_search_animator', function() {
            var idSearch = $(this).attr('id_search');
            $.ajax({
                url: '/src/search_animator/ajax/update_one_field_search_animator.php',
                data: {
                    id: idSearch,
                    value: 1,
                    field: 'status'
                },
                type: 'post',
                async: false,
                success: function(output) {
                    window.location.reload()
                }
            });
        })

        $(document).on('click', '.cancel_search_animator', function() {
            var idSearch = $(this).attr('id_search');
            $.ajax({
                url: '/src/search_animator/ajax/update_one_field_search_animator.php',
                data: {
                    id: idSearch,
                    value: 0,
                    field: 'status'
                },
                type: 'post',
                async: false,
                success: function(output) {
                    window.location.reload()
                }
            });
        })

        $(document).on('click', '.validate_apply', function() {

            var id_candidature = $(this).attr('id_candidature');
            var email = $(this).attr('email');

            var html = "<div>Vous êtes sur le point de valider la candidature de cet animateur pour votre stage. Celui ci " +
                "recevra un email et son planning sur son Espace Privé sera mis à jour.</div>"
            html += "<div style='margin-top: 20px; font-size: 15px'>Souhaitez-vous confirmer cette action ?</div>"

            swal({
                type: 'warning',
                html: html,
                showCancelButton: true,
                confirmButtonText: 'Valider',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#64a328',
                cancelButtonColor: '#808080',
                reverseButtons: true
            }).then(function(result) {
                if (result) {
                    $.ajax({
                        url: '/src/search_animator/ajax/update_apply_animator.php',
                        data: {
                            id: id_candidature,
                            email: email,
                            value: 1
                        },
                        type: 'post',
                        async: false,
                        success: function(output) {
                            window.location.reload()
                        }
                    });
                }
            });
        });

        $(document).on('click', '.reject_apply', function() {

            var id_candidature = $(this).attr('id_candidature');
            var email = $(this).attr('email');

            var html = "<div>Vous êtes sur le point de rejeter la candidature de cet animateur pour votre stage. Celui ci " +
                "recevra un email et son planning sur son Espace Privé sera mis à jour</div>"
            html += "<div style='margin-top: 20px; font-size: 15px'>Souhaitez-vous confirmer cette action ?</div>"

            swal({
                type: 'warning',
                html: html,
                showCancelButton: true,
                confirmButtonText: 'Valider',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#64a328',
                cancelButtonColor: '#808080',
                reverseButtons: true
            }).then(function(result) {
                if (result) {
                    $.ajax({
                        url: '/src/search_animator/ajax/reject_apply_animator.php',
                        data: {
                            id: id_candidature,
                            email: email,
                            value: 1
                        },
                        type: 'post',
                        async: false,
                        success: function(output) {
                            window.location.reload()
                        }
                    });
                }
            });
        });

        $(document).on('click', '.cancel_apply', function() {

            var id_candidature = $(this).attr('id_candidature');
            var email = $(this).attr('email');

            var html = "<div>Vous êtes sur le point d'annuler la candidature de cet animateur pour votre stage. Celui ci" +
                " recevra un email et son planning sur son Espace Privé sera mis à jour."

            html += "<div style='margin-top: 20px; font-size: 15px'>Souhaitez-vous confirmer cette action ?</div>"

            swal({
                type: 'warning',
                html: html,
                showCancelButton: true,
                confirmButtonText: 'Valider',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#64a328',
                cancelButtonColor: '#808080',
                reverseButtons: true
            }).then(function(result) {
                if (result) {
                    $.ajax({
                        url: '/src/search_animator/ajax/update_apply_animator.php',
                        data: {
                            id: id_candidature,
                            email: email,
                            value: 0
                        },
                        type: 'post',
                        async: false,
                        success: function(output) {
                            window.location.reload()
                        }
                    });
                }
            });
        });

        $(document).on('click', '.send_animator_message', function() {
            var text;
            var id_destinataire = $(this).attr('id_formateur');
            var id_emetteur = "<?php echo $membre ?>"

            var historic;
            $.ajax({
                url: '/animateur/ajax/get_historique_message.php',
                data: {
                    id_emetteur: id_emetteur,
                    id_destinataire: id_destinataire,
                    user_id: id_emetteur,
                    from: 'centre'
                },
                type: 'post',
                async: false,
                success: function(output) {
                    historic = output;
                }
            });

            swal({
                title: '<h5>Envoyer un message à l\'animateur</h5>',
                html: historic,
                showCancelButton: true,
                confirmButtonText: 'ENVOYER',
                cancelButtonText: 'FERMER',
                confirmButtonColor: '#01A31C',
                reverseButtons: true,
                preConfirm: function(resolve, reject) {

                    return new Promise(function(resolve, reject) {

                        text = $('#message').val();
                        var phone_regex = RegExp('0[1-9]([-. ,/]?[0-9]{2}){4}');
                        var email_regex = RegExp('[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\@[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\.[a-zA-Z]{2,4}');

                        if (!parseInt($('#message').val().length)) {
                            reject('Message obligatoire');
                        } else if (phone_regex.test(text)) {
                            reject('Numéro de téléphone non autorisé');
                        } else if (email_regex.test(text)) {
                            reject('Email et Url non autorisés');
                        } else {
                            resolve();
                        }
                    });

                }
            }).then(function(result) {

                $.ajax({
                    url: '/animateur/ajax/save_message.php',
                    data: {
                        id_emetteur: id_emetteur,
                        id_destinataire: id_destinataire,
                        content: text,
                        from: 'centre'
                    },
                    type: 'post',
                    success: function(output) {
                        swal({
                            title: 'Contact',
                            html: 'Message envoyé',
                            showCancelButton: false,
                            confirmButtonText: 'OK'
                        });

                        //window.location.reload();
                    }
                });
            })
        })

        $(document).on('click', '.validate_and_send_ants_dir', async function() {
            var studentId = $(this).attr('id_stagiaire');
            var provenance = $(this).attr('provenance');
            var ants_numero_stage = $(this).attr('ants_numero_stage');
            var id_stage = $(this).attr('id_stage');
            var cas = $(this).attr('cas');
            var is_send_directory_to_ants = $(this).attr('is_send_directory_to_ants');

            if (parseInt(cas) == 3 || parseInt(cas) == 4) {
                return false
            }

            if (parseInt(is_send_directory_to_ants) == 0) {
                return false;
            }

            if (!antsStudentDir.checkIfStageHasDeclared(ants_numero_stage)) {
                swal({
                    html: 'Veuillez déclarer votre stage auprès de l\'ANTS',
                    type: 'info',
                    showCloseButton: true,
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK',
                });
                return false
            }


            swal({
                type: 'warning',
                title: 'Confirmation',
                html: '<br>Souhaitez-vous confirmer la transmission du dossier de ce stagiaire à l\'ANTS?',
                confirmButtonText: 'Transmettre',
                showCancelButton: true,
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#64a328',
                cancelButtonColor: '#808080',
                reverseButtons: true
            }).then(async function(result) {
                if (result) {
                    $.LoadingOverlay("show");

                    const validateResult = await antsStudentDir.validateStudentDir(
                        studentId,
                        provenance,
                        ants_numero_stage,
                        id_stage
                    );

                    if (!validateResult.status) {
                        swal({
                            html: validateResult.message,
                            type: 'warning',
                            showCloseButton: true,
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK',
                        });
                        $.LoadingOverlay("hide");
                        return false
                    }

                    const sendResult = await antsStudentDir.sendStudentDir(
                        studentId,
                        provenance,
                        ants_numero_stage,
                        id_stage
                    )

                    $.LoadingOverlay("hide");

                    if (sendResult.message != '') {
                        swal({
                            html: sendResult.message,
                            type: 'warning',
                            showCloseButton: true,
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK',
                        });
                        return false
                    }

                    swal({
                        title: 'Transmission du dossier réussi',
                        html: 'Le dossier du stagiaire a été transmis auprès de l\'ANTS',
                        type: 'success',
                        showCloseButton: true,
                        showCancelButton: false,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK',
                    });
                    return false

                }
            });

        });

        $(document).on('click', '#send_all_studentr_dir', async function(e) {
            e.stopPropagation();
            e.preventDefault();
            var ants_numero_stage = $(this).attr('ants_numero_stage');
            var id_stage = $(this).attr('id_stage');
            var id_membre = $(this).attr('id_membre');

            if (!antsStudentDir.checkIfStageHasDeclared(ants_numero_stage)) {
                swal({
                    html: 'Veuillez déclarer votre stage auprès de l\'ANTS',
                    type: 'info',
                    showCloseButton: true,
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK',
                });
                return false
            }

            swal({
                type: 'warning',
                title: 'Confirmation',
                html: '<br>Souhaitez-vous confirmer la transmission des dossiers de stagiaires à l\'ANTS?',
                confirmButtonText: 'Transmettre',
                showCancelButton: true,
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#64a328',
                cancelButtonColor: '#808080',
                reverseButtons: true
            }).then(async function(result) {
                if (result) {

                    $.LoadingOverlay("show");

                    const allStudents = await antsStudentDir.retrieveAllStudentDir(
                        id_stage
                    )

                    let htmlContentMessage = '';
                    var nb_dossier = allStudents.length;
                    var nb_dossier_incomplet = 0;
                    var nb_dossier_complet = 0;

                    for (var i = 0; i < allStudents.length; i++) {
                        var student = allStudents[i];

                        const validateResult = await antsStudentDir.validateStudentDir(
                            student.id,
                            student.provenance,
                            ants_numero_stage,
                            id_stage
                        );

                        if (!validateResult.status) {
                            nb_dossier_incomplet++;
                            htmlContentMessage += '<div style="font-weight: bold">Erreur sur le stagaire ' + student.nom + ' ' + student.prenom + '</div>';
                            htmlContentMessage += '<div style="margin: 4px 0 4px 20px">- ' + validateResult.message + '</div>'
                            continue;
                        }

                        let sendResult = await antsStudentDir.sendStudentDir(
                            student.id,
                            student.provenance,
                            ants_numero_stage,
                            id_stage
                        )

                        if (sendResult.message != '') {
                            nb_dossier_incomplet++;
                            htmlContentMessage += '<div style="font-weight: bold">Erreur sur le stagaire ' + student.nom + ' ' + student.prenom + '</div>';
                            htmlContentMessage += '<div style="margin: 4px 0 4px 20px">- ' + sendResult.message + '</div>'
                        } else {
                            nb_dossier_complet++;
                        }
                    }

                    let htmlContent = '<div>';

                    htmlContent += '<div style="font-weight: bold; font-size: 22px; margin-bottom: 10px">Voici le bilan de la transmission :</div>'
                    htmlContent += '<div style="margin: 4px 0 4px 20px">- Nombre de dossier traité : ' + nb_dossier + '</div>'
                    htmlContent += '<div style="margin: 4px 0 4px 20px">- Nombre de dossier transmis : ' + nb_dossier_complet + '</div>'
                    htmlContent += '<div style="margin: 4px 0 4px 20px; margin-bottom: 20px">- Nombre de dossier incomplet : ' + nb_dossier_incomplet + '</div>'

                    //htmlContent += htmlContentMessage;

                    htmlContent += '</div>';


                    await antsStudentDir.updateBilanTransmission(
                        nb_dossier,
                        nb_dossier_complet,
                        nb_dossier_incomplet,
                        id_membre,
                        id_stage
                    )

                    $.LoadingOverlay("hide");

                    swal({
                        html: htmlContent,
                        type: 'info',
                        showCloseButton: true,
                        showCancelButton: false,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK',
                    });

                    return false

                    /*

                    const sendResult = await antsStudentDir.sendAllStudentDir(
                        ants_numero_stage,
                        id_stage
                    )

                    $.LoadingOverlay("hide");

                    if (sendResult.message != '') {

                        let htmlContent = '<div style="text-align: left; font-size: 14px">';

                        Object.keys(sendResult.messages).forEach(function (key) {
                            htmlContent += '<div style="font-weight: bold">Erreur sur le stagaire ' + key + '</div>';
                            sendResult.messages[key].forEach(function (m) {
                                htmlContent += '<div style="margin: 4px 0 4px 20px">- ' + m + '</div>'
                            })
                        });

                        htmlContent += '</div>';

                        swal({
                            html: htmlContent,
                            type: 'warning',
                            showCloseButton: true,
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK',
                        });
                        return false
                    }

                    return false;

                     */


                }
            });

        });

        $('.animator_filter_bafm').on('click', function(e) {
            e.preventDefault();
            var isOpen = $(this).attr('is-open');
            if (parseInt(isOpen) == 0) {
                $(this).attr('is-open', 1);
                $('.animator_filter_bafm i').removeClass('fa-angle-down');
                $('.animator_filter_bafm i').addClass('fa-angle-up');
                $('.bafm_filter_content').css('display', 'flex');
            } else {
                $(this).attr('is-open', 0);
                $('.animator_filter_bafm i').removeClass('fa-angle-up');
                $('.animator_filter_bafm i').addClass('fa-angle-down');
                $('.bafm_filter_content').css('display', 'none');
            }
        })

        $('.animator_filter_psy').on('click', function(e) {
            e.preventDefault();
            var isOpen = $(this).attr('is-open');
            if (parseInt(isOpen) == 0) {
                $(this).attr('is-open', 1);
                $('.animator_filter_psy i').removeClass('fa-angle-down');
                $('.animator_filter_psy i').addClass('fa-angle-up');
                $('.psy_filter_content').css('display', 'flex');
            } else {
                $(this).attr('is-open', 0);
                $('.animator_filter_psy i').removeClass('fa-angle-up');
                $('.animator_filter_psy i').addClass('fa-angle-down');
                $('.psy_filter_content').css('display', 'none');
            }
        })

        $('#close_bafm_search_filter').click(function() {
            $(this).attr('is-open', 0);
            $('.animator_filter_bafm i').removeClass('fa-angle-up');
            $('.animator_filter_bafm i').addClass('fa-angle-down');
            $('.bafm_filter_content').css('display', 'none');
            var allInputFilters = document.querySelectorAll('.select_bafm_filter');
            /*allInputFilters.forEach(function (el) {
                $(el).prop('checked', false);
                updateFilter('.bafm_filter_content', $(el).val(), false);
            });*/
        });

        $('#close_psy_search_filter').click(function() {
            $(this).attr('is-open', 0);
            $('.animator_filter_psy i').removeClass('fa-angle-up');
            $('.animator_filter_psy i').addClass('fa-angle-down');
            $('.psy_filter_content').css('display', 'none');
        });

        $('.select_bafm_filter').click(function() {
            var value = $(this).val();
            var allSelected = this;
            if (value == 'all') {
                var allInputFilters = document.querySelectorAll('.select_bafm_filter');
                allInputFilters.forEach(function(el) {
                    if ($(allSelected).is(':checked')) {
                        if (!$(el).is(':checked')) {
                            $(el).prop('checked', true);
                            updateFilter('.bafm_filter_content', $(el).val(), true);
                        }
                    } else {
                        if ($(el).is(':checked')) {
                            $(el).prop('checked', false);
                            updateFilter('.bafm_filter_content', $(el).val(), false);
                        }
                    }
                });
            } else {
                if ($(this).is(':checked')) {
                    updateFilter('.bafm_filter_content', $(this).val(), true);
                } else {
                    updateFilter('.bafm_filter_content', $(this).val(), false);
                }
            }

        });

        $('.select_psy_filter').click(function() {
            var value = $(this).val();
            var allSelected = this;
            if (value == 'all') {
                var allInputFilters = document.querySelectorAll('.select_psy_filter');
                allInputFilters.forEach(function(el) {
                    if ($(allSelected).is(':checked')) {
                        if (!$(el).is(':checked')) {
                            $(el).prop('checked', true);
                            updateFilter('.psy_filter_content', $(el).val(), true);
                        }
                    } else {
                        if ($(el).is(':checked')) {
                            $(el).prop('checked', false);
                            updateFilter('.psy_filter_content', $(el).val(), false);
                        }
                    }
                });
            } else {
                if ($(this).is(':checked')) {
                    updateFilter('.psy_filter_content', $(this).val(), true);
                } else {
                    updateFilter('.psy_filter_content', $(this).val(), false);
                }
            }
        });

    });


    function affiche_stages() {
        var first_date = $('#from-date').val();
        var end_date = $('#to-date').val();
        var departement = $('#departement_filter').val();
        var site = $('#site').val();
        var stagiaires = $('#stagiaires_filter').val();
        var status = $('#status_filter').val();

        first_date = first_date.split('-');
        first_date = first_date[2] + '-' + first_date[1] + '-' + first_date[0].slice(-2);

        end_date = end_date.split('-');
        end_date = end_date[2] + '-' + end_date[1] + '-' + end_date[0].slice(-2);

        var arrFilterBafm = [];
        var arrFilterPsy = [];

        var filterBafm = $('.bafm_filter_content').attr('filter');
        var filterPsy = $('.psy_filter_content').attr('filter');
        var filter_apply = $('.bafm_filter_content').attr('filter-apply');

        if (filterBafm != "" && filterBafm != undefined) {
            arrFilterBafm = JSON.parse(filterBafm);
        }

        if (filterPsy != "" && filterPsy != undefined) {
            arrFilterPsy = JSON.parse(filterPsy);
        }

        $.ajax({
            url: '/src/stage/ajax/display_all_stage_member2025.php',
            data: {
                memberId: "<?php echo $membre ?>",
                startDate: first_date,
                endDate: end_date,
                hasStudent: stagiaires,
                department: departement,
                site: site,
                status: status,
                arrFilterBafm: arrFilterBafm,
                arrFilterPsy: arrFilterPsy,
                filter_apply: filter_apply
            },
            type: 'post',
            success: function(output) {
                $("#liste_stages").html(output);
            }
        });
    }

    function affiche_stages_with_filter_animator(arrFilter, filterApplyOn) {

        var first_date = $('#from-date').val();
        var end_date = $('#to-date').val();
        var departement = $('#departement_filter').val();
        var site = $('#site').val();
        var stagiaires = $('#stagiaires_filter').val();
        var status = $('#status_filter').val();

        first_date = first_date.split('-');
        first_date = first_date[2] + '-' + first_date[1] + '-' + first_date[0].slice(-2);

        end_date = end_date.split('-');
        end_date = end_date[2] + '-' + end_date[1] + '-' + end_date[0].slice(-2);

        $.ajax({
            url: '/src/stage/ajax/display_all_animator_stage_member.php',
            data: {
                memberId: "<?php echo $membre ?>",
                startDate: first_date,
                endDate: end_date,
                hasStudent: stagiaires,
                department: departement,
                site: site,
                status: status,
                arrFilter: arrFilter,
                filterApplyOn: filterApplyOn
            },
            type: 'post',
            success: function(output) {
                $("#liste_stages").html(output);
            }
        });
    }

    function uploadFeuilleEmargement(form, type, directory) {
        $(form).ajaxForm({
            data: {
                'type': type,
                directory: directory
            },
            success: function(data) {
                var dataJson = JSON.parse(data);
                if (dataJson.status == true) {
                    swal({
                        title: 'Document chargé !',
                        text: 'Votre document a été enregistré.',
                        type: 'success',
                        showCancelButton: false,
                        confirmButtonText: 'OK'
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    swal({
                        title: 'Opération refusé !',
                        text: dataJson.message,
                        type: 'error',
                        showCancelButton: false,
                        confirmButtonText: 'OK'
                    }).then(function() {
                        location.reload();
                    });
                }
            }
        }).submit();
    }

    function uploadAttestation(form, type, studentId, directory) {
        $(form).ajaxForm({
            data: {
                'type': type,
                'studentId': studentId,
                directory: directory
            },
            success: function(data) {
                var dataJson = JSON.parse(data);
                if (dataJson.status == true) {
                    swal({
                        title: 'Document chargé !',
                        text: 'Votre document a été enregistré sur votre Espace Partenaire.',
                        type: 'success',
                        showCancelButton: false,
                        confirmButtonText: 'OK'
                    }).then(function() {
                        //location.reload();
                    });
                } else {
                    swal({
                        title: 'Opération refusé !',
                        text: dataJson.message,
                        type: 'error',
                        showCancelButton: false,
                        confirmButtonText: 'OK'
                    }).then(function() {
                        location.reload();
                    });
                }
            }
        }).submit();
    }

    function deleteDocument(file, directory) {
        swal({
            title: "Vous êtes sur le point de supprimer ce document",
            text: "Souhaitez-vous confirmer cette action ?",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: 'Valider',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#64a328',
            cancelButtonColor: '#808080',
            reverseButtons: true
        }).then(function(result) {
            if (result) {
                $.ajax({
                    url: '/document/ajax/delete.php',
                    data: {
                        file: file,
                        directory: directory
                    },
                    type: 'post',
                    async: false,
                    success: function(output) {
                        window.location.reload();
                    }
                });
            }
        });
    }

    function reload_documents(id_stagiaire) {
        newdate = new Date();
        var provenance = $('#provenance_stagiaire').val();
        $.ajax({
            url: 'stage/reload_documents.php',
            data: {
                studentId: id_stagiaire,
                provenance: provenance
            },
            type: 'post',
            success: function(output) {
                document.getElementById('div_espace_stagiaire_telechargement_documents').innerHTML = output;
            }
        });
    }

    function updateFilter(elSelector, selectedValue, isAdd) {
        var filter = $(elSelector).attr('filter');
        var arrFilter = [];
        if (filter != "") {
            arrFilter = JSON.parse(filter);
        }
        if (isAdd) {
            arrFilter.push(selectedValue);
        } else {
            var isAdded = arrFilter.find(el => el == selectedValue);
            if (isAdded) {
                arrFilter = arrFilter.filter(el => el != isAdded);
            }
        }
        $(elSelector).attr('filter', JSON.stringify(arrFilter));
    }

    function WaitProcess(id, visible, msg = '') {
        if (visible) {
            $("#" + id).html('<img  src="https://www.prostagespermis.fr/images/bx_loader.gif" style="width:50px;"/><div id="' + id + '_msg">' + msg + '</div>');
            //$("#"+id).html('<div id="'+id+'_msg">'+msg+'</div>');
            $("#" + id).show();
        } else {
            $("#" + id).hide();
            $("#" + id).html('');
        }
    }

    var checkStageBeforeDeclaration = {
        checkFormateur: async (stageId) => {
            var form = new FormData();
            form.append('stageId', stageId);
            return await fetch("/src/Api/ants/ajax/validate_stage.php", {
                    method: "POST",
                    body: form
                })
                .then((response) => {
                    return response.json();
                })
                .catch((err) => {})
        }
    }

    var antsStudentDir = {
        checkIfStageHasDeclared: function(ants_numero) {
            if (ants_numero) {
                return true
            }
            return false
        },

        validateStudentDir: async function(
            studentId,
            provenance,
            ants_numero_stage,
            stageId
        ) {
            var form = new FormData();
            form.append('studentId', studentId);
            form.append('provenance', provenance);
            form.append('ants_numero_stage', ants_numero_stage);
            form.append('stageId', stageId);
            return await fetch("/src/Api/ants/ajax/validate_ants_student_data.php", {
                    method: "POST",
                    body: form
                })
                .then((response) => {
                    return response.json();
                })
                .catch((err) => {})
        },

        sendStudentDir: async function(
            studentId,
            provenance,
            ants_numero_stage,
            stageId
        ) {
            var form = new FormData();
            form.append('studentId', studentId);
            form.append('provenance', provenance);
            form.append('ants_numero_stage', ants_numero_stage);
            form.append('stageId', stageId);
            return await fetch("/src/Api/ants/ajax/send_one_student_dir.php", {
                    method: "POST",
                    body: form
                })
                .then((response) => {
                    return response.json();
                })
                .catch((err) => {})
        },

        retrieveAllStudentDir: async function(
            stageId
        ) {
            var form = new FormData();
            form.append('stageId', stageId);
            return await fetch("/src/student/ajax/retrieve_all_ants_dir.php", {
                    method: "POST",
                    body: form
                })
                .then((response) => {
                    return response.json();
                })
                .catch((err) => {})
        },

        sendAllStudentDir: async function(
            ants_numero_stage,
            stageId
        ) {
            var form = new FormData();
            form.append('ants_numero_stage', ants_numero_stage);
            form.append('stageId', stageId);
            return await fetch("/src/Api/ants/ajax/send_all_student_dir.php", {
                    method: "POST",
                    body: form
                })
                .then((response) => {
                    return response.json();
                })
                .catch((err) => {})
        },

        updateBilanTransmission: async function(
            nb_dossier,
            nb_dossier_complet,
            nb_dossier_incomplet,
            id_membre,
            id_stage
        ) {
            var form = new FormData();
            form.append('nb_dossier', nb_dossier);
            form.append('nb_dossier_complet', nb_dossier_complet);
            form.append('nb_dossier_incomplet', nb_dossier_incomplet);
            form.append('id_membre', id_membre);
            form.append('id_stage', id_stage);
            return await fetch("/src/Api/ants/ajax/update_bilan_transmission.php", {
                    method: "POST",
                    body: form
                })
                .then((response) => {
                    return response.json();
                })
                .catch((err) => {})
        },
    }
</script>

<?php
$url1 = 'https://' . $_SERVER['HTTP_HOST'] . '/simpligestion/centres.php';
$url2 = 'https://' . $_SERVER['HTTP_HOST'] . '/simpligestion/cgp.php';
?>
<?php //if(($_SERVER['HTTP_REFERER'] !== $url1) && ($_SERVER['HTTP_REFERER']) !== $url2) :
if (!isset($_GET["a"]) && !isset($_SESSION["a"])) { ?>
    <script>
        document.addEventListener('DOMContentLoaded', checkIfUserHasAcceptedCGP);

        function showWarning() {
            const html = `L'acceptation des Conditions G&eacuten&eacute;rales de Partenariat est n&eacute;cessaire pour poursuivre votre navigation sur votre espace personnel. Pour toute question, vous pouvez contacter votre conseiller &agrave l'adresse <a href="mailto:contact@prostagespermis.fr">contact@prostagespermis.fr</a>.`
            swal({
                title: '',
                html: html,
                type: 'info',
                showCloseButton: true,
                showCancelButton: false,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK',
            });
        }

        function validateCGP(id_membre) {
            $.ajax({
                url: 'ajax_functions2024.php',
                data: {
                    action: 'validateCGP',
                    id_membre: <?= $membre ?>
                },
                type: 'POST',
                success: function(output) {
                    let message = JSON.parse(output);
                    if (message.status === true) {
                        document.querySelector('.cgp').style.display = 'none';
                        document.querySelector('.adjust-wrapper').style.opacity = 1;
                        document.querySelector('.adjust-wrapper').style.display = 'block';
                        document.querySelector('#footer').style.display = 'block';
                        return swal({
                            title: '',
                            html: message.message,
                            type: 'info',
                            showCloseButton: true,
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK',
                        });
                    }
                    return showWarning();
                }
            });
        }

        function checkIfUserHasAcceptedCGP() {
            $.ajax({
                url: 'ajax_functions2024.php',
                data: {
                    action: 'check_if_user_has_accepted_CGP',
                    id_membre: <?= $membre ?>
                },
                type: 'POST',
                success: function(output) {
                    let cgp = JSON.parse(output);
                    if (cgp.message) {
                        let article = String(cgp.message.updated_article_number);
                        article = article.replace(',', ' et ', article);
                        let maybePlural = 'article';
                        let maybePluralPronoun = 'L\'';
                        maybePluralCompound = 'a';
                        if (article.length > 2) {
                            maybePlural = 'articles';
                            maybePluralPronoun = 'Les';
                            maybePluralCompound = 'ont';
                        }
                        swal({
                            title: '',
                            html: `${maybePluralPronoun} ${maybePlural} ${article} ${maybePluralCompound} &eacute;t&eacute; mis &agrave; jour. Cliquez sur "OK" puis consultez l'article modifi&eacute;. Cliquez ensuite sur "Accepter" pour poursuivre votre navigation sur votre espace personnel. Pour toutes question vous pouvez contacter votre conseiller &agrave; l'adresse suivante <a href="mailto:contact@prostagespermis.fr">contact@prostagespermis.fr</a>`,
                            type: 'info',
                            showCloseButton: true,
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK',
                        });
                    }
                    if (!cgp.accepted) {
                        const div = document.createElement('div');
                        div.classList.add('cgp');
                        div.innerHTML = cgp.cgp.cgp;
                        div.style.position = 'absolute';
                        div.style.top = '10%';
                        div.style.left = '13%';
                        div.style.width = '75%';
                        div.style.height = '600px';
                        div.style.overflow = 'auto';
                        div.style.overflowX = 'hidden';
                        div.style.zIndex = 1000;
                        div.style.paddingLeft = '1em';
                        div.style.paddingRight = '1em';
                        div.style.paddingTop = '1em';
                        div.style.backgroundColor = 'white';

                        const buttonContainer = document.createElement('div');
                        const buttonOK = document.createElement('button');
                        buttonOK.innerHTML = 'Accepter';
                        buttonOK.style.float = 'right';
                        buttonOK.style.backgroundColor = 'rgb(48, 133, 214)';
                        buttonOK.style.color = 'white';
                        buttonOK.style.fontWeight = 'bolder';
                        buttonOK.style.padding = '0.5em';
                        buttonOK.style.width = '120px';
                        buttonOK.style.textAlign = 'center';
                        buttonOK.style.border = 'none';
                        buttonOK.style.marginRight = '1em';
                        buttonOK.style.borderRadius = '3px';
                        buttonOK.addEventListener('click', function() {
                            validateCGP(<?= $membre ?>);
                        });
                        const buttonNotOK = document.createElement('button');
                        buttonNotOK.innerHTML = 'Refuser';
                        buttonNotOK.style.backgroundColor = 'rgb(170, 170, 170)';
                        buttonNotOK.style.padding = '0.5em';
                        buttonNotOK.style.color = 'white';
                        buttonNotOK.style.fontWeight = 'bolder';
                        buttonNotOK.style.width = '120px';
                        buttonNotOK.style.textAlign = 'center';
                        buttonNotOK.style.border = 'none';
                        buttonNotOK.style.marginLeft = '1em';
                        buttonNotOK.style.borderRadius = '3px';
                        buttonNotOK.addEventListener('click', showWarning);

                        buttonContainer.appendChild(buttonOK);
                        buttonContainer.appendChild(buttonNotOK);
                        buttonContainer.style.position = 'sticky';
                        buttonContainer.style.backgroundColor = 'white';
                        buttonContainer.style.bottom = 0;
                        buttonContainer.style.paddingBottom = '1em';
                        buttonContainer.style.paddingTop = '1em';
                        buttonContainer.style.width = '100%';

                        div.appendChild(buttonContainer);

                        document.querySelector('.adjust-wrapper').style.opacity = 0.3;
                        //document.querySelector('#footer').style.display = 'none';
                        //document.querySelector('.adjust-wrapper').style.display = 'none';
                        if (!cgp.message) {
                            swal({
                                title: 'Bienvenue sur votre espace personnel !',
                                html: `Merci de prendre connaissance des Conditions G&eacute;n&eacute;rales de Partenariat avant de poursuivre dans votre espace client.`,
                                type: 'info',
                                showCloseButton: true,
                                showCancelButton: false,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK',
                            });
                        }
                        document.body.appendChild(div);
                    }
                }
            });

        }
    </script>
<?php
} else {
    $_SESSION["a"] = 1;
}
?>

</html>