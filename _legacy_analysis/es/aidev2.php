<?php
include('includes/config.php');
require_once('includes/functions.php');
?>

<!DOCTYPE html>
<html>

<head>

    <?php include("includes/head.html"); ?>

    <title>Aide</title>

    <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
    <style>

        ul.ul_custom li {
            list-style-type: circle;
        }

        .colors {
            float: left;
            margin: 20px auto;
            width: 260px;
        }

        .colors a {
            float: left;
            height: 30px;
            width: 33.33333333%;
        }

        .colors .default {
            background: #414956;
        }

        .colors .blue {
            background: #4a89dc;
        }

        .colors .white {
            background: #ffffff;
        }

        .menu {
            box-shadow: 0 20px 50px #333333;
            /*float: left;*/
            /*min-width: 260px;*/
            outline: 0;
            position: relative;
        }

        .menu * {
            -moz-box-sizing: border-box;
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
            outline: 0;
        }

        .menu .menu-footer {
            background: #414956;
            color: #f0f0f0;
            float: left;
            font-weight: normal;
            height: 50px;
            line-height: 50px;
            font-size: 6px;
            width: 100%;
            text-align: center;
        }

        .menu .menu-header {
            background: #fff;
            color: #000;
            font-weight: bold;
            height: 50px;
            line-height: 50px;
            text-align: center;
            width: 100%;
            font-size: 17px;
            margin-bottom: 2px;
        }

        .menu ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .menu ul li {
            display: block;
            float: left;
            position: relative;
            width: 100%;
            margin: 2px 0;
            border-bottom: 2px solid #FFF;
        }

        .menu ul li a {
            background: #ebecee;
            color: #000;
            float: left;
            font-size: 15px;
            overflow: hidden;
            padding: 10px 5px;
            position: relative;
            text-decoration: none;
            white-space: nowrap;
            width: 100%;
        }

        .menu ul li a i {
            float: left;
            font-size: 16px;
            line-height: 18px;
            text-align: left;
            width: 34px;
        }

        .menu ul li .menu-label {
            background: #f0f0f0;
            border-radius: 100%;
            color: #555555;
            font-size: 11px;
            font-weight: 800;
            line-height: 18px;
            min-width: 20px;
            padding: 1px 2px 1px 1px;
            position: absolute;
            right: 18px;
            text-align: center;
            top: 14px;
        }

        .menu ul .submenu {
            display: none;
            position: static;
            width: 100%;
        }

        .menu ul .submenu .submenu-indicator {
            line-height: 16px;
        }

        .menu ul .submenu li {
            clear: both;
            width: 100%;
            padding-left: 10px;
            padding-right: 10px;
            padding-bottom: 3px;
            color: #262424;
        }

        .menu ul .submenu li ul.submenu {
            display: none;
            position: static;
            width: 100%;
            overflow: hidden;
            padding: 10px;
            color: #383838;
        }

        .menu ul .submenu li a {
            background: #ebecee;
            border-left: solid 6px transparent;
            border-top: none;
            float: left;
            font-size: 14px;
            position: relative;
            width: 100%;
            color: blue;
        }

        .menu ul .submenu li:hover > a {
            border-left-color: #414956;
        }

        .menu ul .submenu li .menu-label {
            background: #f0f0f0;
            border-radius: 100%;
            color: #555555;
            font-size: 11px;
            font-weight: 800;
            line-height: 18px;
            min-width: 20px;
            padding: 1px 2px 1px 1px;
            position: absolute;
            right: 18px;
            text-align: center;
            top: 14px;
        }

        .menu ul .submenu > li > a {
        }

        .menu ul .submenu > li > ul.submenu > li > a {
            padding-left: 45px;
        }

        .menu ul .submenu > li > ul.submenu > li > ul.submenu > li > a {
            padding-left: 60px;
        }

        .menu .submenu-indicator {
            -moz-transition: "transform .3s linear";
            -o-transition: "transform .3s linear";
            -webkit-transition: "transform .3s linear";
            transition: "transform .3s linear";
            float: right;
            font-size: 25px;
            line-height: 19px;
            position: absolute;
            right: 22px;
        }

        .menu .submenu-indicator-minus > .submenu-indicator {
            -moz-transform: rotate(45deg);
            -ms-transform: rotate(45deg);
            -o-transform: rotate(45deg);
            -webkit-transform: rotate(45deg);
            transform: rotate(45deg);
        }

        .menu > ul > li.active > a {
            background: #3b424d;
            color: #ffffff;
        }

        .menu > ul > li:hover > a {
            background: #3b424d;
            color: #ffffff;
        }

        .menu > ul > li > a {
            border-bottom: solid 1px #d0d0d0;
        }

        .ink {
            -moz-transform: scale(0);
            -ms-transform: scale(0);
            -o-transform: scale(0);
            -webkit-transform: scale(0);
            background: rgba(255, 255, 255, 0.3);
            border-radius: 100%;
            display: block;
            position: absolute;
            transform: scale(0);
        }

        .animate-ink {
            -moz-animation: ripple .3s linear;
            -ms-animation: ripple .3s linear;
            -o-animation: ripple .3s linear;
            -webkit-animation: ripple .3s linear;
            animation: ripple .3s linear;
        }

        @-moz-keyframes 'ripple' {
            100% {
                opacity: 0;
                transform: scale(2.5);
            }
        }

        @-webkit-keyframes 'ripple' {
            100% {
                opacity: 0;
                transform: scale(2.5);
            }
        }

        @keyframes 'ripple' {
            100% {
                opacity: 0;
                transform: scale(2.5);
            }
        }

        .blue.menu .menu-footer {
            background: #4a89dc;
        }

        .blue.menu .menu-header {
            background: #4a89dc;
        }

        .blue.menu ul li > a {
            background: #4a89dc;
        }

        .blue.menu ul ul.submenu li:hover > a {
            border-left-color: #3e82da;
        }

        .blue.menu > ul > li.active > a {
            background: #000;
        }

        .blue.menu > ul > li:hover > a {
            background: #3e82da;
        }

        .blue.menu > ul > li > a {
            border-bottom-color: #3e82da;
        }

        .white.menu .menu-footer {
            background: #ffffff;
            color: #555555;
        }

        .white.menu .menu-header {
            background: #ffffff;
            color: #555555;
        }

        .white.menu ul li a {
            background: #ffffff;
            color: #555555;
        }

        .white.menu ul ul.submenu li:hover > a {
            border-left-color: #f0f0f0;
        }

        .white.menu ul ul.submenu li a {
            color: #f0f0f0;
        }

        .white.menu > ul > li.active > a {
            background: #f0f0f0;
        }

        .white.menu > ul > li:hover > a {
            background: #f0f0f0;
        }

        .white.menu > ul > li > a {
            border-bottom-color: #f0f0f0;
        }

        .white.menu > ul > li > a > .ink {
            background: rgba(0, 0, 0, 0.1);
        }

        .ibox {
            padding: 0 40px 0 2px !Important;
        }

        .float-e-margins {
            text-align: justify !Important;
        }

        @media screen and (max-width: 768px) {
            .menu ul li a i {
                width: 20px !Important;
            }

            .content {
                padding-right: 30px;
            }

            .menu ul li a {
                padding-left: 2px !Important;
                white-space: normal !Important;
                font-size: 13px !important;
            }

            .menu .submenu-indicator {
                right: 5px !Important;
            }

            .menu ul .submenu li {
                padding-left: 0 !Important;
                padding-right: 0 !Important;
            }

            ol {
                padding-left: 5px !Important;
            }

            .ibox {
                padding: 10px !Important;
            }

            .menu ul {
                padding: 0px
            }

        }

    </style>
    <script>
        /* accordion menu plugin*/
        ;(function ($, window, document, undefined) {
            var pluginName = "accordion";
            var defaults = {
                speed: 200,
                showDelay: 0,
                hideDelay: 0,
                singleOpen: true,
                clickEffect: true,
                indicator: 'submenu-indicator-minus',
                subMenu: 'submenu',
                event: 'click touchstart' // click, touchstart
            };

            function Plugin(element, options) {
                this.element = element;
                this.settings = $.extend({}, defaults, options);
                this._defaults = defaults;
                this._name = pluginName;
                this.init();
            }

            $.extend(Plugin.prototype, {
                init: function () {
                    this.openSubmenu();
                    this.submenuIndicators();
                    if (defaults.clickEffect) {
                        this.addClickEffect();
                    }
                },
                openSubmenu: function () {
                    $(this.element).children("ul").find("li").bind(defaults.event, function (e) {
                        e.stopPropagation();
                        e.preventDefault();
                        var $subMenus = $(this).children("." + defaults.subMenu);
                        var $allSubMenus = $(this).find("." + defaults.subMenu);
                        if ($subMenus.length > 0) {
                            if ($subMenus.css("display") == "none") {
                                $subMenus.slideDown(defaults.speed).siblings("a").addClass(defaults.indicator);
                                if (defaults.singleOpen) {
                                    $(this).siblings().find("." + defaults.subMenu).slideUp(defaults.speed)
                                        .end().find("a").removeClass(defaults.indicator);
                                }
                                return false;
                            } else {
                                $(this).find("." + defaults.subMenu).delay(defaults.hideDelay).slideUp(defaults.speed);
                            }
                            if ($allSubMenus.siblings("a").hasClass(defaults.indicator)) {
                                $allSubMenus.siblings("a").removeClass(defaults.indicator);
                            }
                        }
                        //window.location.href = $(this).children("a").attr("href");
                    });
                },
                submenuIndicators: function () {
                    if ($(this.element).find("." + defaults.subMenu).length > 0) {
                        //$(this.element).find("." + defaults.subMenu).siblings("a").append("<span class='submenu-indicator'>+</span>");
                        $(this.element).find("." + defaults.subMenu).siblings("a").prepend("<div style='width: 20px;float: right;text-align: left;margin-right: 5px;margin-top: 5px;margin-left: 2px'>" +
                            "<svg id='gly' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 320 512' class='triangle-bottom triangle' style='color: #868686'  fill='currentColor'>" +
                            "<path d='M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9l22.6-22.6c9.4-9.4 24.6-9.4 33.9 0l96.4 96.4 96.4-96.4c9.4-9.4 24.6-9.4 33.9 0l22.6 22.6c9.4 9.4 9.4 24.6 0 33.9l-136 136c-9.2 9.4-24.4 9.4-33.8 0z'/>" +
                            "</svg>" +
                            "</div>"
                        );
                    }
                },
                addClickEffect: function () {
                    var ink, d, x, y;
                    $(this.element).find("a").bind("click touchstart", function (e) {
                        var gly = $(this);
                        var cas = 'bottom';
                        if (gly.find(".triangle-bottom").length > 0)
                            cas = 'top';

                        if (gly.css("color") == 'rgb(255, 255, 255)' || gly.css("color") == 'rgb(0, 0, 0)') {
                            $("svg").each(function (index) {
                                if ($(this).is("#gly")) {
                                    $(this).html("<path d='M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9l22.6-22.6c9.4-9.4 24.6-9.4 33.9 0l96.4 96.4 96.4-96.4c9.4-9.4 24.6-9.4 33.9 0l22.6 22.6c9.4 9.4 9.4 24.6 0 33.9l-136 136c-9.2 9.4-24.4 9.4-33.8 0z'/>");
                                    $(this).attr('class', 'triangle triangle-bottom');
                                }
                            });
                        } else {
                            $("svg").each(function (index) {
                                if ($(this).is("#gly")) {
                                    if ($(this).css("color") == 'rgb(0, 0, 255)') {
                                        $(this).html("<path d='M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9l22.6-22.6c9.4-9.4 24.6-9.4 33.9 0l96.4 96.4 96.4-96.4c9.4-9.4 24.6-9.4 33.9 0l22.6 22.6c9.4 9.4 9.4 24.6 0 33.9l-136 136c-9.2 9.4-24.4 9.4-33.8 0z'/>");
                                        $(this).attr('class', 'triangle triangle-bottom');
                                    }
                                }
                            });
                        }
                        var selectedGly = gly.find(".triangle");
                        if (cas == 'top') {
                            selectedGly.html('<path d="M177 159.7l136 136c9.4 9.4 9.4 24.6 0 33.9l-22.6 22.6c-9.4 9.4-24.6 9.4-33.9 0L160 255.9l-96.4 96.4c-9.4 9.4-24.6 9.4-33.9 0L7 329.7c-9.4-9.4-9.4-24.6 0-33.9l136-136c9.4-9.5 24.6-9.5 34-.1z"/>')
                            $(selectedGly).attr('class', 'triangle triangle-top');
                        } else {
                            selectedGly.html("<path d='M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9l22.6-22.6c9.4-9.4 24.6-9.4 33.9 0l96.4 96.4 96.4-96.4c9.4-9.4 24.6-9.4 33.9 0l22.6 22.6c9.4 9.4 9.4 24.6 0 33.9l-136 136c-9.2 9.4-24.4 9.4-33.8 0z'/>")
                            $(selectedGly).attr('class', 'triangle triangle-bottom');
                        }

                        $(".ink").remove();
                        if ($(this).children(".ink").length === 0) {
                            $(this).prepend("<span class='ink'></span>");
                        }
                        ink = $(this).find(".ink");
                        ink.removeClass("animate-ink");
                        if (!ink.height() && !ink.width()) {
                            d = Math.max($(this).outerWidth(), $(this).outerHeight());
                            ink.css({
                                height: d,
                                width: d
                            });
                        }
                        x = e.pageX - $(this).offset().left - ink.width() / 2;
                        y = e.pageY - $(this).offset().top - ink.height() / 2;
                        ink.css({
                            top: y + 'px',
                            left: x + 'px'
                        }).addClass("animate-ink");
                    });
                }
            });
            $.fn[pluginName] = function (options) {
                this.each(function () {
                    if (!$.data(this, "plugin_" + pluginName)) {
                        $.data(this, "plugin_" + pluginName, new Plugin(this, options));
                    }
                });
                return this;
            };
        })(jQuery, window, document);
    </script>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $("#menu").accordion();
            $(".colors a").click(function () {
                if ($(this).attr("class") != "default") {
                    $("#menu").removeClass();
                    $("#menu").addClass("menu").addClass($(this).attr("class"));
                } else {
                    $("#menu").removeClass();
                    $("#menu").addClass("menu");
                }
            });
        });
    </script>
</head>

<body>
<?php include("includes/topbarv2.php"); ?>
<div id="wrapper" style="border-bottom:1px solid #f3f3f4">
    <?php include("includes/nav_leftv2.php"); ?>
    <div id="page-wrapper" class="gray-bg">

        <div class="wrapper wrapper-content animated fadeIn">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">

                        <h3 style="text-align:center">Vous avez une question ? Consultez la rubrique "Aide" ci-dessous. Vous y trouverez les réponses aux questions les plus fréquentes posées par nos clients. Et si malgré tout, votre question reste sans réponse, <a href='https://www.khapeo.com/wp/psp/aide-et-contact-prostagespermis/' target='_blank'>cliquez ici</a>
                        </h3>

                        <p style="margin-bottom:20px"></p>


                        <div id="jquery-script-menu">
                            <div class="jquery-script-center">

                                <div class="jquery-script-clear"></div>
                            </div>
                        </div>

                        <div class="content">
                            <nav>
                                <div id="menu" class="menu">
                                    <div class="menu-header"> QUESTIONS - REPONSES</div>
                                    <ul class="accordion">

                                        <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                <div style="float:left;width:85%">MES PREMIERS PAS DANS L’ESPACE
                                                    STAGIAIRE
                                                </div>
                                            </a>
                                            <ul class="submenu">

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> A quoi sert mon espace
                                                            stagiaire ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>Votre Espace Stagiaire a été conçu et développé pour vous
                                                            permettre de gérer votre stage en autonomie
                                                            <p>
                                                            <ul class="ul_custom">
                                                                <li>=> Consulter les détails de votre stage : adresse
                                                                    exacte, accès, commodités, horaires, etc. Tout ce
                                                                    que vous devez savoir avant d’y aller !
                                                                </li>
                                                                <li>=> Compléter votre dossier en ligne : pas besoin de
                                                                    passer par La Poste, vous pouvez télécharger vos
                                                                    documents directement.
                                                                </li>
                                                                <li>=> Un empêchement ? Vous pouvez modifier la date de
                                                                    votre stage ou demander le remboursement en quelques
                                                                    clics.
                                                                </li>
                                                                <li>=> Après le stage, retrouvez votre attestation ou
                                                                    votre facture
                                                                </li>
                                                            </ul>
                                                            </p>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Que dois-je faire une fois
                                                            que je suis inscrit au stage ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>Votre Espace Stagiaire a été conçu et développé pour vous
                                                            permettre de gérer votre stage en autonomie
                                                            <p>
                                                                Vous avez effectué votre inscription sur le site
                                                                prostagespermis.fr et réalisé le paiement en ligne par
                                                                carte bleue? C’est très bien ! Mais ce n’est pas
                                                                totalement terminé. Pour constituer un dossier complet
                                                                afin que la Préfecture valide votre dossier, nous avons
                                                                besoin d’informations complémentaires et de documents
                                                                précis.<br><br>
                                                                Pour compléter votre dossier, cliquez sur la rubrique
                                                                <strong>“Mes informations”</strong><br>

                                                            <ul class="ul_custom">
                                                                <li> => Etape 1: complétez vos informations dans les
                                                                    rubriques “Mes données personnelles”, “Mon permis de
                                                                    conduire” et “Mon stage” puis validez en cliquant
                                                                    sur le bouton “Je valide”
                                                                </li>
                                                                <li> => Etape 2: Téléchargez les documents nécessaires
                                                                    dans la rubrique “Mes documents” puis validez en
                                                                    cliquant sur le bouton “Je valide”
                                                                </li>
                                                            </ul>
                                                            <br><br><br>
                                                            Pour savoir comment compléter correctement vos informations,
                                                            consulter la section <strong>“Comment renseigner
                                                                correctement mes informations ?”</strong>. Pour
                                                            télécharger vos documents consulter la section <strong>“Comment
                                                                télécharger mes documents ?”</strong>
                                                            </p>
                                                        </li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </li>

                                        <!--
                                        <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;"><div style="float:left;width:85%"><i class="fa fa-question"> </i>QUESTIONS POSÉES FRÉQUEMMENT</div></a>
                                          <ul class="submenu">

                                            <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;"><div style="float:left;width:85%"> Que dois-je faire une fois que je suis inscrit au stage ?</div></a>
                                            <ul class="submenu">
                                                <li>Vous avez effectué votre inscription sur le site prostagespermis.fr et réalisé le paiement en ligne par carte bleue? C’est très bien ! Mais ce n’est pas totalement terminé. Pour constituer un dossier complet afin que la Préfecture valide votre dossier, nous avons besoin d’informations complémentaires et de documents précis.
                                                <p>
                                                Pour compléter votre dossier, cliquez sur la rubrique “Mes informations”
                                                </p>
                                                <p>
                                                <ul class="ul_custom">
                                                <li>=> Etape 1: complétez vos informations dans les rubriques “Mes données personnelles”, “Mon permis de conduire” et “Mon stage” puis validez en cliquant sur le bouton “Je valide”</li>
                                                <li>=> Etape 2: Téléchargez les documents nécessaires dans la rubrique “Mes documents” puis validez en cliquant sur le bouton “Je valide”</li>
                                                </ul>
                                                </p>
                                <p>
                                Pour savoir comment compléter correctement vos informations, consulter la section Comment renseigner correctement mes informations?
                                Pour télécharger vos documents consulter la section Comment télécharger mes documents ?</p>

                                                </li>
                                            </ul>
                                            </li>

                                            <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;"><div style="float:left;width:85%"> Comment compléter mon dossier ?</div></a>
                                            <ul class="submenu">
                                                <li>

                                                <p>Pour compléter votre dossier, cliquez sur la rubrique “Mes informations”.</p>

                                                <p>Une fois que vous êtes sur cette page, il vous suffit de renseigner les informations nécessaires et télécharger les documents demandés. Voici comment faire:</p>

                                                <p>
                                                <ul>
                                                <li> => Etape 1: complétez vos information dans les rubriques “Mes données personnelles”, “Mon permis de conduire” et “Mon stage” puis validez en cliquant sur le bouton “Je valide”</li>
                                                <li> => Etape 2: Téléchargez les documents nécessaires dans la rubrique “Mes documents” puis validez en cliquant sur le bouton “Je valide”</li>
                                                </ul>
                                                </p>

                                                <p>
                                                Pour savoir comment compléter correctement vos informations, consulter la section “Comment renseigner correctement mes informations ?”. Pour télécharger vos documents consulter la section “Comment télécharger mes documents ?”
                                                </p>

                                                </li>
                                            </ul>
                                            </li>

                                            <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;"><div style="float:left;width:85%"> Comment renseigner correctement mes informations ?</div></a>
                                            <ul class="submenu">
                                                <li>

                                                <p>Avant de commencer, sachez que <strong>ce travail est très important !</strong> Prenez le temps de le faire correctement dès votre première connection à votre Espace Stagiaire. Mettez-vous au calme et soyez concentré ! Si vos données ne sont pas correctes, la Préfecture ne pourra pas valider votre dossier et vous aurez suivi un stage pour rien.</p>

                                                <p>Rendez-vous sur la page <strong>“Mes informations”</strong>.</p>

                                                <p>Voici les étapes à suivre:</p>

                                                <strong>Etape 1: “Mes données personnelles”</strong><br>
                                                <ul>
                                <li>1. Vérifiez scrupuleusement vos informations l’une après l’autre (faute d’orthographe, erreurs, données manquantes….)</li>
                                <li>2. Si une information est incorrecte ou incomplète, cliquez directement sur l’information en bleue. Faites la modification puis cliquez sur le bouton “Valider” à droite du champs</li>
                                <li>3. Après avoir vérifié chaque donnée, cliquez sur le bouton vert en bas à droite de la rubrique “Je valide mes données personnelles”</li>
                                <li>4. Si après avoir cliqué sur ce bouton un message apparait à l’écran vous signalant “Validation impossible” alors c’est qu’une information est manquante ou mal renseignée. Vérifiez à nouveau une à une vos informations puis cliquez sur “Je valide mes données personnelles”</li>
                                <li>5. Si tout est ok, La mention “Validé” apparaît en haut à droite de l’encart “Mes données personnelles”</li>
                                </ul>
                                &nbsp;<br>&nbsp;
                                                <strong>Etape 2: “Mon permis de conduire”</strong><br>
                                                <ul>
                                                <li>1. Suivez le même processus que pour l’étape 1</li>
                                                <li>2. N’oubliez pas de cliquer sur le bouton en bas  “Je valide mes données de permis”</li>
                                                <li>3. Si tout est ok, La mention “Validé” apparaît en haut à droite de l’encart “Mon permis de conduire”</li>
                                                </ul>

                                &nbsp;<br>&nbsp;
                                                <strong>Etape 3: “Mon stage”</strong><br>
                                                <ul>
                                <li>1. Commencez par vérifier le cas dans lequel vous êtes dans l’espace de droite “Sélectionnez votre cas de stage”. En passant votre souris sur le cas de stage, une bulle info apparaît pour vous donner toutes les précisions sur ce cas (stage de récupération de points volontaire, stage obligatoire…)</li>
                                <li>2. Ensuite, répondez aux questions dans l’encart de droite ‘Vérifiez votre situation” (pour les cas 1 et cas 2 uniquement)</li>
                                <li>3. Vous constaterez que les informations que nous vous fournissons juste en dessous se mettent à jour en fonction du cas dans lequel vous êtes. Lisez attentivement ces informations pour ne pas suivre un stage inutilement.</li>
                                <li>4. En fonction de votre situation:<br>
                                 => soit vous maintenez votre inscription en cliquant sur le bouton “Je valide mon cas de stage”<br>
                                 => soit vous annulez votre inscription en cliquant sur le bouton “je ne maintiens pas mon inscription”. Dans ce cas là, votre inscription est annulée. Vous pouvez soit conserver votre avoir (valable 1 an) soit demander le remboursement. Rendez-vous dans la rubrique “Je change de d’avis” pour faire votre choix
                                </li>
                                                </ul>

                                &nbsp;<br>&nbsp;
                                                <strong>Etape 4: “Mon stage”</strong><br>
                                <ul>
                                <li> => Vérifier que la mention “Validé” apparaît en vert en haut à droite de chaque rubrique</li>
                                <li> => Si c’est le cas alors vous avez terminé la partie concernant le renseignement de vos informations. Il ne vous reste plus qu’à télécharger vos documents.</li>
                                </ul>

                                                </li>
                                            </ul>
                                            </li>

                                            <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;"><div style="float:left;width:85%"> Comment télécharger mes documents ?</div></a>
                                            <ul class="submenu">
                                                <li>

                                                <p>Pour télécharger vos documents il vous suffit de vous rendre sur la page “Mes informations”. En bas de page, une rubrique “Mes documents” vous permet de télécharger vos documents personnels. Voici les étapes à suivre:</p>

                                                <ul>
                                <li>1. Cliquez sur le bouton “Télécharger” en bas de chaque encart dans lequel se trouve une flèche pointée vers le haut qui clignote</li>
                                <li>2. Une fenêtre s’ouvre afin que vous puissiez choisir le bon fichier sur votre ordinateur</li>
                                <li>3. Sélectionnez sur votre ordinateur le fichier concerné (Ex: Permi recto) puis validez en cliquant sur le bouton “ouvrir”</li>
                                <li>4. Le document a été téléchargé et apparaît désormais sur votre Espace Stagiaire à l’endroit prévu</li>
                                <li>5. Valider en cliquant sur le bouton vert en bas à droite “je valide mes documents” </li>
                                <li>6. Vérifier que la mention “Validé” apparaît en vert en haut à droite la rubrique “Mes documents”. Voilà c’est terminé !</li>
                                                </ul>
                                &nbsp;<br>&nbsp;
                                <p>
                                Il ne vous reste plus qu’à vérifier, corriger et/ou compléter vos informations, dans les rubriques au-dessus (“Mes données personnelles”, “Mon permis de conduire” et “Mon stage”). Si vous avez déjà effectué ces vérifications, alors votre dossier est complet !
                                </p>

                                <p>
                                Si vous ne savez pas comment faire pour télécharger vos documents depuis votre ordinateur ou que vous n’avez pas ces documents sur votre ordinateur en format électronique, aucune inquiétude. Voici la question <strong>Je n’arrive pas à télécharger mes documents. Comment faire ?</strong>
                                </p>


                                                </li>
                                            </ul>
                                            </li>

                                            <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;"><div style="float:left;width:85%"> Comment vous contacter ?</div></a>
                                            <ul class="submenu">
                                                <li>
                                <p>
                                Nous avons fait le choix de ne pas avoir de conseillers qui répondent par téléphone. Nous n’avons donc pas de numéro auquel vous pouvez nous joindre. Pourquoi? Pour vous proposer les prix les plus bas de France. Grâce à Internet, tout peut être réalisé à distance et ça nous permet de faire des économies dont on vous fait directement bénéficier sur le prix du stage. Et tout ça avec une qualité de service encore plus grande. Tout le monde est gagnant !
                                </p>
                                <p>
                                En toute logique, vous n’avez pas besoin de nous contacter. Votre Espace Stagiaire a été conçu pour vous permettre d’effectuer en toute autonomie toutes les démarches nécessaires. Si vous avez des questions, vous trouverez toutes les réponses dans la rubrique <strong>Aide</strong>.
                                </p>
                                <p>
                                Si malgré cela vous souhaitez tout de même nous contacter, alors envoyez-nous un message à partir de la rubrique <strong>“Contact”</strong>. Notre équipe vous répond sous 48h maximum. Dès que votre demande aura été traitée, vous recevrez un email vous informant qu’un message vous attend dans votre Espace Stagiaire.
                                </p>


                                                </li>
                                            </ul>
                                            </li>

                                            <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;"><div style="float:left;width:85%"> Mon stage a été annulé. Que dois-je faire ?</div></a>
                                            <ul class="submenu">
                                                <li>
                                                <p>
                                Les Préfectures imposent des règles très strictes (6 participants au minimum, présence de 2 animateurs…) et il arrive quelquefois que le centre ne peut les respecter. Dans le cas, il est dans l’obligation d’annuler la session. Si votre stage a été annulé, plusieurs possibilités s’offrent à vous:
                                <ul>
                                <li> => Vous inscrire sur une nouvelle date</li>
                                <li> => Mettre votre dossier en attente le temps pour vous de consulter vos prochaines disponibilités</li>
                                <li> => Demander le remboursement</li>
                                </ul>
                                 </p>
                                &nbsp;<br>&nbsp;
                                Quelque soit votre décision il vous suffit d’aller dans la rubrique <strong>“Je change d’avis”</strong> de votre Espace Stagiaire pour faire votre choix. C’est aussi simple que cela !

                                                </li>
                                            </ul>
                                            </li>

                                            <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;"><div style="float:left;width:85%"> Je souhaite changer d’avis sur mon stage. Dois-je régler des frais ?</div></a>
                                            <ul class="submenu">
                                                <li>
                                                <p>
                                Vous disposez d’un <strong>droit de rétraction de 14 jours</strong> (Loi Hamon sur les achats en ligne) à partir de votre inscription au stage. Dans ce délai vous pouvez changer d’avis à n’importe quel instant sans aucun frais à payer et ce jusqu’à la veille du stage 18h (horaires de fermeture de nos bureaux):
                                </p>
                                <p>
                                Vous souhaitez changer d’avis dans les 14 jours qui suivent votre inscription (Loi Hamon):
                                <ul>
                                <li> => Changement de date: aucun frais</li>
                                <li> => Mise en attente de votre dossier: aucun frais</li>
                                <li> => Annulation de stage et demande de remboursement: aucun frais</li>
                                </ul>
                                </p>

                                                </li>
                                            </ul>
                                            </li>

                                            <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;"><div style="float:left;width:85%"> Comment annuler mon inscription ?</div></a>
                                            <ul class="submenu">
                                                <li>
                                                <p>
                                Il vous suffit de vous rendre sur la page <strong>“Je change d’avis”</strong> puis de cliquer sur le bouton rouge “Annuler ma participation”.
                                Ensuite, choisissez le motif d’annulation en fonction de votre situation. Selon votre choix, un texte explicatif vous apporte toutes les précisions dont vous avez besoin. Vous n’avez plus alors qu’à cliquer sur le bouton <strong>“Trouver une autre date”</strong> ou <strong>“Mettre mon dossier en attente”</strong> ou <strong>“Demander le remboursement”</strong>. Si vous n’êtes pas certain de votre situation, choisissez plutôt de mettre votre dossier en attente. Vous disposerez d’un avoir valable 1 an à valoir sur votre prochain stage.

                                </p>

                                                </li>
                                            </ul>
                                            </li>

                                            <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;"><div style="float:left;width:85%"> Comment changer de date de stage ?</div></a>
                                            <ul class="submenu">
                                                <li>
                                                <p>
                                Votre n’êtes plus disponible aux dates que vous aviez choisis lors de votre inscription ? Aucun souci ! Vous pouvez changer de date en vous rendant sur la page <strong>“Je change d’avis”</strong>  puis en cliquant sur le bouton <strong>“Changer de date”.</strong>
                                </p>
                                <p>
                                Renseignez ensuite la ville dans laquelle vous souhaitez effectuer votre nouveau stage. Enfin, choisissez la date et le lieu de votre choix en cliquant sur <strong>“Réservez”</strong>. Si le nouveau stage choisi est au même prix que votre stage initial, il vous suffit de cliquer sur le bouton <strong>“Transfert”</strong>. En revanche, si le nouveau stage choisi est plus cher, vous devez payer la différence. Pour cela, il suffit de renseigner les coordonnées de votre carte bleue puis de cliquer sur “Transfert”. <strong>La transaction bancaire est totalement sécurisée</strong> (cryptage SSL 128 bits). Voilà c’est terminée: votre nouveau stage et toutes les informations nécessaires apparaissent sur la page “Mon stage” de votre Espace Stagiaire.

                                </p>
                                                </li>
                                            </ul>
                                            </li>

                                            <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;"><div style="float:left;width:85%"> Comment mettre mon dossier en attente ?</div></a>
                                            <ul class="submenu">
                                                <li>
                                                <p>
                                Vous ne souhaitez plus participer au stage aux dates que vous aviez choisies et vous n’êtes pas certaine(e) de vos prochaines disponibilités? Il vous suffit de vous rendre sur la page <strong>“Je change d’avis”</strong> puis de mettre votre dossier en attente en cliquant sur le bouton <strong>“Annuler ma participation”</strong>.
                                </p>
                                <p>
                                Ensuite, dans le menu déroulant qui s’affiche en dessous, choisissez le motif “Je ne suis plus disponible à ces dates”. Enfin, cliquez sur le bouton <strong>“Mettre mon dossier en attente”</strong>. Voilà, c’est terminé ! Votre inscription est annulée et vous disposez d’un avoir équivalent au prix que vous avez payé lors de votre inscription. Cet avoir est valable 1 an.
                                </p>
                                                </li>
                                            </ul>
                                            </li>

                                            <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;"><div style="float:left;width:85%"> Comment obtenir le remboursement de mon stage ?</div></a>
                                            <ul class="submenu">
                                                <li>
                                                <p>
                                Il vous suffit de vous rendre sur la page “Je change d’avis” puis de cliquer puis de cliquer sur le bouton <strong>“Annuler ma participation”</strong>.
                                </p>
                                <p>
                                Ensuite, dans le menu déroulant qui s’affiche en dessous, choisissez le motif qui correspond à votre situation. Puis cliquez sur le bouton <strong>“Demander le remboursement”</strong>. Votre demande est envoyée immédiatement à notre service client qui effectue le remboursement sous 7 jours. Dès que le remboursement a été effectué, vous recevez email informatif. Attention, le remboursement sera visible sur votre <strong>relevé bancaire sous 5 jours</strong> si votre carte est à débit immédiat ou en <strong>fin de mois</strong> si votre carte est à débit différé.

                                </p>
                                                </li>
                                            </ul>
                                            </li>

                                            <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;"><div style="float:left;width:85%"> Comment savoir si je peux suivre un stage ou pas ?</div></a>
                                            <ul class="submenu">
                                                <li>
                                                <p>
                                Il vous suffit d’utiliser notre outil “Diagnostic Permis” ci-dessous. Voici les étapes à suivre:
                                <ul>
                                <li>1. Choisissez votre cas</li>
                                <li>2. Répondez aux questions qui s’affichent</li>
                                <li>3. Lisez la réponse apportée et décidez ou non de suivre un stage</li>
                                </ul>
                                </p>
                                                </li>
                                            </ul>
                                            </li>

                                            <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;"><div style="float:left;width:85%"> Comment choisir correctement mon cas de stage ?</div></a>
                                            <ul class="submenu">
                                                <li>
                                <p>
                                Il existe 4 cas de stage. Voici un descriptif de chaque cas vous permettant de bien faire votre choix:
                                </p>
                                <p>
                                <strong>Cas 1 “Stage de récupération de points volontaire </strong><br>
                                Le stage volontaire de récupération de points permet de récupérer 4 points sur son permis de conduire (dans la limite du plafond maximum autorisé et du respect du délai d'un an et un jour entre deux stages). Il est ouvert à tous les conducteurs titulaires d'un permis de conduire valide, même en cas de suspension ou de rétention de permis.
                                </p>
                                <p>
                                <strong>Cas 2 : Stage obligatoire en période probatoire avec lettre 48N </strong><br>
                                Jeunes conducteurs uniquement !<br>
                                <i>Ce stage de sensibilisation à la sécurité routière ne concerne que les jeunes conducteurs ayant reçu la lettre 48N. Ce courrier est adressé à tous les conducteurs ayant commis une infraction de 3 points ou plus pendant leur période probatoire. Ce stage doit être effectué dans un délai de 4 mois après la réception de la lettre et il permet la récupération de 4 points (dans la limite du plafond autorisé et du respect du délai d'un an et un jour entre deux stages).</i>
                                </p>

                                <p>
                                <strong>Cas 3 : Stage obligatoire en alternative aux poursuites judiciaires ou en composition pénale</strong><br>
                                Stage proposé par le Procureur de la République afin d’éviter les poursuites judiciaires (alternative aux poursuites pénales) ou dans le but d’aménager sa peine (composition pénale).
                                </p>

                                <p>
                                <strong>Cas 4 : Stage obligatoire en peine complémentaire ou de mise à l'épreuve avec sursis</strong><br>
                                Stage imposé par le juge suite à une faute grave (délit routier) en plus des autres peines (amende, perte de points, etc.).
                                </p>
                                                </li>
                                            </ul>
                                            </li>

                                            <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;"><div style="float:left;width:85%"> Mes points n’apparaissent pas sur le site Télépoints alors que mon stage est terminé. Pourquoi ?</div></a>
                                            <ul class="submenu">
                                                <li>
                                                <p>
                                La saisie informatique de vos points sur le fichier national du permis à points est effectuée par un agent administratif…qui n’a pas que votre dossier à traiter !
                                Voilà pourquoi il existe très souvent un décalage et qu’en consultant le site Télépoints, vous ne voyez pas vos précieux 4 points apparaître. Pas d’inquiétude ! Votre attestation fait foi : la date de récupération de vos points est bien le lendemain du deuxième jour.
                                </p>

                                <p>
                                Vos points sont donc bien déjà sur votre permis de conduire mails il faut quelques fois compter plus d’un mois pour les voir apparaitre sur le site Télépoints. Voilà comment ça se passe:
                                <ul>
                                <li> => le soir du deuxième jour de stage, le centre vous remet votre attestation</li>
                                <li> => sous 15 jours, il remet un double de cette attestation à la préfecture du département</li>
                                <li> => un agent administratif enregistre vos 4 points sur le fichier national du permis à points</li>
                                <li> => vos points apparaissent sur Internet</li>
                                </ul>
                                </p>

                                <p>
                                <a download href="cir_36943.pdf">Extrait de la circulaire relative au régime général du permis à points du 11 mars 2004</a>
                                </p>
                                <p>
                                <i>« Le préfet procède à la reconstitution du nombre de points, dans un délai d’un mois à compter de la réception de l’attestation. La reconstitution prend effet dès le <strong>lendemain de la dernière journée du stage</strong>. » </i>
                                </p>
                                <p>
                                La date retenue reste bien celle du <strong>lendemain du stage</strong>, et non la date où la saisie sera effectué sur le fichier. Mais il peut se passer plus d’un mois entre le moment où vous avez suivi votre stage et l’instant où vos points apparaissent sur Internet (Télépoints). Donc patience, <strong>votre permis n’est plus en danger !</strong>
                                </p>

                                                </li>
                                            </ul>
                                            </li>

                                          </ul>
                                          </li>
                                          -->

                                        <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                <div style="float:left;width:85%">CONNAITRE MA SITUATION</div>
                                            </a>
                                            <ul class="submenu">

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Comment puis-je savoir si je
                                                            peux suivre un stage ou pas ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p>
                                                                Il vous suffit d’utiliser notre outil “Diagnostic
                                                                Permis” ci-dessous. Voici les étapes à suivre:
                                                            <ul>
                                                                <li>1. Choisissez votre cas</li>
                                                                <li>2. Répondez aux questions qui s’affichent</li>
                                                                <li>3. Lisez la réponse apportée et décidez ou non de
                                                                    suivre un stage
                                                                </li>
                                                            </ul>
                                                            </p>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 60px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Je suis en permis probatoire
                                                            mais je n’ai toujours reçu la lettre 48N. Dois-je faire un
                                                            stage ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p>
                                                                Vous ne pouvez suivre un stage obligatoire en période
                                                                probatoire (Cas 2) que si vous avez reçu la <strong>lettre
                                                                    48N</strong>. Une fois que vous l’avez reçue,
                                                                inscrivez-vous à un stage en Cas 2 et téléchargez cette
                                                                lettre sur votre Espace Stagiaire rubriques <strong>“Mes
                                                                    informations”</strong>.
                                                            </p>
                                                            <p>
                                                                Toutefois, si vous pensez que votre permis est en danger
                                                                car il vous reste peu de points et donc que vous risquez
                                                                l’<strong>invalidation</strong> alors inscrivez à un
                                                                stage en cas 1 (récupération volontaire de 4 points).
                                                                Assistez au stage et récupérez 4 points sur votre permis
                                                                pour le sauver.
                                                            </p>
                                                            <p>
                                                                Mais sachez que le jour où vous recevrez la lettre 48N,
                                                                vous devrez à nouveau vous inscrire pour suivre un stage
                                                                mais cette fois-ci en Cas 2 (stage obligatoire en
                                                                période probatoire):
                                                            <ul>
                                                                <li> => Si ce stage est effectué moins d’un an après
                                                                    votre précédent stage, nous ne récupérerez pas 4
                                                                    points supplémentaires mais vous obtiendrez le
                                                                    remboursement de l’amende.
                                                                </li>
                                                                <li> => Si ce stage est effectué plus d’un an après
                                                                    votre précédent stage, vous récupérerez 4 points
                                                                    supplémentaires ainsi que le remboursement de
                                                                    l’amende.
                                                                </li>
                                                            </ul>
                                                            </p>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Je ne sais pas combien de
                                                            points il y a sur mon permis. Dois-je suivre un stage ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p>
                                                                Nous vous conseillons fortement de consulter votre solde
                                                                de points avant de suivre un stage. C’est très simple,
                                                                il suffit de le consulter:
                                                            </p>
                                                            <p>
                                                                <strong>Directement en préfecture</strong>, sur
                                                                présentation (en personne) d’une pièce d’identité. On
                                                                vous remettra un Relevé Intégral d’Information (RII) où
                                                                figurent votre solde de points, vos codes de connexion à
                                                                Télépoints et l’historique de votre permis (infractions,
                                                                points, etc. )
                                                            </p>
                                                            <p>
                                                                <strong>Directement en ligne</strong>, sans bouger de
                                                                chez vous :<br>
                                                                => sur le site <strong>telepointspermis.fr</strong> à
                                                                l’aide de vos identifiants (disponible sur le Relevé
                                                                Intégral d’Information, les lettre 48N ou 48M si vous
                                                                les avez reçues)<br>
                                                                => sur le site <strong>France Connect</strong>, à l’aide
                                                                d’identifiants pré-existants : ceux que vous utilisez
                                                                pour payer vos impôts sur impots.gouv.fr, ceux que vous
                                                                utilisez sur ameli.fr (Sécurité Sociale) ou si vous avez
                                                                une identité numérique La Poste.
                                                            </p>
                                                            <p>
                                                                Il serait dommage de suivre un stage alors que vous avez
                                                                un solde de 12 points ou bien si votre permis a été
                                                                invalidé. Dans les 2 cas, le stage serait inutile: vous
                                                                aurez gaspillé votre argent et perdu 2 jours. Même si
                                                                une mise à jour de ses connaissances sur la sécurité
                                                                routière à toujours du bon !
                                                            </p>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> J’ai zéro point sur mon
                                                            permis. Puis-je effectuer un stage pour sauver mon permis ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p>
                                                                Si vous avez zéro point sur votre permis, vous pouvez
                                                                encore effectuer un stage pour sauver votre permis.
                                                                Toutefois, vous devez être certain que votre permis n’a
                                                                pas été invalidé.
                                                                Pour en être convaincu il vous suffit de consulter votre
                                                                solde de points sur <strong>Télépoints</strong>. Si le
                                                                dossier indique un <strong>permis valide</strong>, vous
                                                                pouvez suivre un stage.
                                                            </p>
                                                            <p>
                                                                Si votre permis est invalidé, la Préfecture vous adresse
                                                                un courrier qu’on appelle “48SI” dans lequel elle vous
                                                                informe que votre permis n’a plus de validité. Vous avez
                                                                10 jours pour le remettre aux autorités. Si vous avez
                                                                déménagé entre temps, il se peut que le courrier soit
                                                                revenu à la Préfecture en NPAI (n’habite plus à
                                                                l'adresse indiqué) et que votre permis a tout de même
                                                                été invalidé.
                                                            </p>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> J’ai reçu la lettre 48SI qui
                                                            invalide mon permis. Puis-je suivre un stage ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p>
                                                                Non il est trop tard pour suivre un stage:
                                                            <ul>
                                                                <li>Vous avez 10 jours pour le remettre aux autorités
                                                                </li>
                                                                <li>Vous êtes dans l’interdiction de conduire ou de
                                                                    repasser votre permis pendant 6 mois
                                                                </li>
                                                                <li>Vous devez effectuer un test psychotechnique et
                                                                    passer une visite médicale avant de pouvoir repasser
                                                                    votre permis
                                                                </li>
                                                                <li>Vous devez passer l’épreuve théorique (code)</li>
                                                                <li>Vous devez passer l’épreuve pratique (conduite) si
                                                                    vous êtes en permis probatoire
                                                                </li>
                                                            </ul>
                                                            </p>
                                                        </li>
                                                    </ul>
                                                </li>

                                            </ul>
                                        </li>

                                        <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                <div style="float:left;width:85%">COMPLÉTER MON DOSSIER</div>
                                            </a>
                                            <ul class="submenu">

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Comment compléter mon dossier
                                                            ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p>
                                                                Pour compléter votre dossier, cliquez sur la rubrique
                                                                <strong>“Mes informations”</strong>.
                                                                Une fois que vous êtes sur cette page, il vous suffit de
                                                                renseigner les informations nécessaires et télécharger
                                                                les documents demandés. Voici comment faire:
                                                                <br><br>
                                                                &nbsp;&nbsp;=> Etape 1: complétez vos information dans
                                                                les rubriques “Mes données personnelles”, “Mon permis de
                                                                conduire” et “Mon stage” puis validez en cliquant sur le
                                                                bouton “Je valide”<br>
                                                                &nbsp;&nbsp;=> Etape 2: Téléchargez les documents
                                                                nécessaires dans la rubrique “Mes documents” puis
                                                                validez en cliquant sur le bouton “Je valide”
                                                                <br><br>
                                                                Pour savoir comment compléter correctement vos
                                                                informations, consulter la section <strong>“Comment
                                                                    renseigner correctement mes informations ?”</strong>
                                                                Pour télécharger vos documents consulter la section
                                                                <strong>“Comment télécharger mes documents ?”</strong>
                                                            </p>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Comment renseigner
                                                            correctement mes informations ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p>
                                                                Avant de commencer, sachez que <strong>ce travail est
                                                                    très important !</strong> Prenez le temps de le
                                                                faire correctement dès votre première connection à votre
                                                                Espace Stagiaire. Mettez-vous au calme et soyez
                                                                concentré ! Si vos données ne sont pas correctes, la
                                                                Préfecture ne pourra pas valider votre dossier et vous
                                                                aurez suivi un stage pour rien.
                                                                <br><br>
                                                                Rendez-vous sur la page <strong>“Mes
                                                                    informations”</strong>.
                                                                <br><br>
                                                                Voici les étapes à suivre:<br><br>
                                                                <strong>Etape 1: “Mes données personnelles”</strong>
                                                            <div style="margin:10px 10px 20px 5px">
                                                                <ol>
                                                                    <li>1. Vérifiez scrupuleusement vos informations
                                                                        l’une après l’autre (faute d’orthographe,
                                                                        erreurs, données manquantes….)
                                                                    </li>
                                                                    <li>2. Si une information est incorrecte ou
                                                                        incomplète, cliquez directement sur
                                                                        l’information en bleue. Faites la modification
                                                                        puis cliquez sur le bouton “Valider” à droite du
                                                                        champs
                                                                    </li>
                                                                    <li>3. Après avoir vérifié chaque donnée, cliquez
                                                                        sur le bouton vert en bas à droite de la
                                                                        rubrique “Je valide mes données personnelles”
                                                                    </li>
                                                                    <li>4. Si après avoir cliqué sur ce bouton un
                                                                        message apparait à l’écran vous signalant
                                                                        “Validation impossible” alors c’est qu’une
                                                                        information est manquante ou mal renseignée.
                                                                        Vérifiez à nouveau une à une vos informations
                                                                        puis cliquez sur “Je valide mes données
                                                                        personnelles”
                                                                    </li>
                                                                    <li>5. Si tout est ok, La mention “Validé” apparaît
                                                                        en haut à droite de l’encart “Mes données
                                                                        personnelles”
                                                                    </li>
                                                                    <li>&nbsp;</li>
                                                                </ol>
                                                            </div>

                                                            <strong>Etape 2: “Mon permis de conduire”</strong>
                                                            <div style="margin:10px 10px 20px 5px">
                                                                <ol>
                                                                    <li>1. Suivez le même processus que pour l’étape 1
                                                                    </li>
                                                                    <li>2. N’oubliez pas de cliquer sur le bouton en bas
                                                                        “Je valide mes données de permis”
                                                                    </li>
                                                                    <li>3. Si tout est ok, La mention “Validé” apparaît
                                                                        en haut à droite de l’encart “Mon permis de
                                                                        conduire”
                                                                    </li>
                                                                    <li>&nbsp;</li>
                                                                </ol>
                                                            </div>

                                                            <strong>Etape 3: “Mon stage”</strong>
                                                            <div style="margin:10px 10px 20px 5px">
                                                                <ol>
                                                                    <li>1. Commencez par vérifier le cas dans lequel
                                                                        vous êtes dans l’espace de droite “Sélectionnez
                                                                        votre cas de stage”. En passant votre souris sur
                                                                        le cas de stage, une bulle info apparaît pour
                                                                        vous donner toutes les précisions sur ce cas
                                                                        (stage de récupération de points volontaire,
                                                                        stage obligatoire…)
                                                                    </li>
                                                                    <li>2. Ensuite, répondez aux questions dans l’encart
                                                                        de droite ‘Vérifiez votre situation” (pour les
                                                                        cas 1 et cas 2 uniquement)
                                                                    </li>
                                                                    <li>3. Vous constaterez que les informations que
                                                                        nous vous fournissons juste en dessous se
                                                                        mettent à jour en fonction du cas dans lequel
                                                                        vous êtes. Lisez attentivement ces informations
                                                                        pour ne pas suivre un stage inutilement.
                                                                    </li>
                                                                    <li>4. En fonction de votre situation:<br></li>
                                                                    soit vous maintenez votre inscription en cliquant
                                                                    sur le bouton “Je valide mon cas de stage” <br>
                                                                    soit vous annulez votre inscription en cliquant sur
                                                                    le bouton “je ne maintiens pas mon inscription”.
                                                                    Dans ce cas là, votre inscription est annulée. Vous
                                                                    pouvez soit conserver votre avoir (valable 1 an)
                                                                    soit demander le remboursement. Rendez-vous dans la
                                                                    rubrique “Je change de d’avis” pour faire votre
                                                                    choix
                                                                </ol>
                                                            </div>

                                                            <strong>Etape 4</strong>
                                                            <div style="margin:10px 10px 20px 5px">
                                                                <ol>
                                                                    Vérifier que la mention “Validé” apparaît en vert en
                                                                    haut à droite de chaque rubrique
                                                                    Si c’est le cas alors vous avez terminé la partie
                                                                    concernant le renseignement de vos informations. Il
                                                                    ne vous reste plus qu’à télécharger vos documents.
                                                                </ol>
                                                            </div>


                                                            </p>
                                                        </li>
                                                    </ul>
                                                </li>


                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Comment télécharger mes
                                                            documents ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p>
                                                                Pour télécharger vos documents il vous suffit de vous
                                                                rendre sur la page “Mes informations”.
                                                                En bas de page, une rubrique “Mes documents” vous permet
                                                                de télécharger vos documents personnels. Voici les
                                                                étapes à suivre:
                                                            <ol>
                                                                <li>1. Cliquez sur le bouton “Télécharger” en bas de
                                                                    chaque encart dans lequel se trouve une flèche
                                                                    pointée vers le haut qui clignote
                                                                </li>
                                                                <li>2. Une fenêtre s’ouvre afin que vous puissiez
                                                                    choisir le bon fichier sur votre ordinateur
                                                                </li>
                                                                <li>3. Sélectionnez sur votre ordinateur le fichier
                                                                    concerné (Ex: Permi recto) puis validez en cliquant
                                                                    sur le bouton “ouvrir”
                                                                </li>
                                                                <li>4. Le document a été téléchargé et apparaît
                                                                    désormais sur votre Espace Stagiaire à l’endroit
                                                                    prévu
                                                                </li>
                                                                <li>5. Valider en cliquant sur le bouton vert en bas à
                                                                    droite “je valide mes documents”
                                                                </li>
                                                                <li>6. Vérifier que la mention “Validé” apparaît en vert
                                                                    en haut à droite la rubrique “Mes documents”. Voilà
                                                                    c’est terminé !
                                                                </li>
                                                            </ol>

                                                            Il ne vous reste plus qu’à vérifier, corriger et/ou
                                                            compléter vos informations, dans les rubriques au-dessus
                                                            (“Mes données personnelles”, “Mon permis de conduire” et
                                                            “Mon stage”). Si vous avez déjà effectué ces vérifications,
                                                            alors votre dossier est complet !
                                                            <br><br>
                                                            Si vous ne savez pas comment faire pour télécharger vos
                                                            documents depuis votre ordinateur ou que vous n’avez pas ces
                                                            documents sur votre ordinateur en format électronique,
                                                            aucune inquiétude. Voici la question <strong>Je n’arrive pas
                                                                à télécharger mes documents. Comment faire ?</strong>

                                                            </p>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Je n’arrive pas à télécharger
                                                            mes documents. Comment faire ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p>
                                                                Télécharger des documents, c’est comme les envoyer par
                                                                mail ! Voici comment faire en quelques étapes.
                                                            </p>
                                                            <p>
                                                                NB : les formats acceptés sont les suivants : jpg, jpeg,
                                                                png, gif, bmp, pdf. La taille ne doit pas dépasser 2Mo.
                                                            </p>

                                                            &nbsp;<br>
                                                            <h3>Depuis mon ordinateur</h3>
                                                            <p>
                                                                Avant toute chose, vérifiez que vous avez :<br>
                                                                ● un ordinateur (ou empruntez celui d'un proche)<br>
                                                                ● Un scanner (généralement intégré à votre imprimante)
                                                                connecté à cet ordinateur<br>
                                                                ● Une connexion internet<br>
                                                            </p>

                                                            <p>
                                                                Tout y est ? Alors suivez le guide !
                                                            </p>
                                                            <p>
                                                                1. Scannez vos documents. Ouvrez le capot de votre
                                                                imprimante pour y placer vos documents. Attention, un
                                                                scan par document ! Donc un scan pour le recto du permis
                                                                et un scan pour le verso par exemple.Enregistrez ces
                                                                fichiers sur votre ordinateur sur un dossier que vous
                                                                allez pouvoir retrouver rapidement, par exemple “Dossier
                                                                stage permis”<br>
                                                                2. Connectez-vous à votre Espace Stagiaire. Depuis votre
                                                                ordinateur (depuis le mail d’inscription que vous avez
                                                                reçu ou depuis le site Internet Prostagespermis rubrique
                                                                “Espace Stagiaire”.<br>
                                                                3. Une fois sur la page d’accueil “Mon Stage”, cliquez
                                                                sur le bouton rouge “Dossier incomplet” ou sur “Mes
                                                                informations” dans le menu de gauche<br>
                                                                4. Page “Mes informations”: dirigez-vous en bas de page
                                                                dans l’encart “Mes documents”<br>
                                                                5. Cliquez sur le lien en orange "Télécharger Permis
                                                                (Recto)" par exemple.<br>
                                                                6. Une fenêtre s’ouvre afin que vous puissiez choisir le
                                                                bon fichier sur votre ordinateur<br>
                                                                7. Sélectionnez le dossier dans lequel vous avez scanné
                                                                tout à l’heure les documents puis choisissez le fichier
                                                                concerné (Ex: Permi recto) puis cliquez sur le bouton
                                                                “ouvrir” en bas à droite de la fenêtre<br>
                                                                8. Le document a été téléchargé et apparaît désormais
                                                                sur votre Espace Stagiaire à l’endroit prévu<br>
                                                                9. Répétez cette action pour chaque document à
                                                                télécharger.<br>
                                                                10. Valider en cliquant sur le bouton vert en bas à
                                                                droite “je valide mes documents”<br>
                                                                11. Vérifier que la mention “Validé” apparaît en vert en
                                                                haut à droite la rubrique “Mes documents”.<br>
                                                            </p>
                                                            <p>
                                                                Voilà c’est terminé !
                                                            </p>
                                                            &nbsp;<br>
                                                            <h3>Depuis mon téléphone portable</h3>
                                                            <p>
                                                                Avant toute chose, vérifiez que vous avez :<br>
                                                                ● un téléphone qui prend des photos<br>
                                                                ● une connexion à Internet (Wifi, 3G ou 4G)<br>
                                                                ● la possibilité de consulter votre boîte mail depuis ce
                                                                téléphone
                                                            </p>
                                                            <p>
                                                                Tout y est ? Alors suivez le guide !
                                                            </p>
                                                            <p>
                                                                1. Prenez vos documents en photo. Prenez en photos avec
                                                                votre téléphone les documents demandés dans votre
                                                                situation (permis de conduire recto, permis de conduire
                                                                verso, lettre 48N, ordonnance pénale, etc.). Jusque là,
                                                                vous devriez suivre. Attention, veillez à ce que la
                                                                photo soit de bonne qualité pour que les informations
                                                                soient lisibles !<br>

                                                                2. Connectez-vous à votre Espace Stagiaire.
                                                                Depuis votre ordinateur (depuis le mail d’inscription
                                                                que vous avez reçu ou depuis le site Internet
                                                                Prostagespermis rubrique “Espace Stagiaire”.<br>

                                                                3. Une fois sur la page d’accueil “Mon Stage”, cliquez
                                                                sur le bouton rouge “Dossier incomplet” ou sur “Mes
                                                                informations” dans le menu de gauche<br>
                                                                4. Page “Mes informations”: dirigez-vous en bas de page
                                                                dans l’encart “Mes documents”<br>
                                                                5. Cliquez sur le lien en orange "Télécharger Permis
                                                                (Recto)" par exemple.<br>
                                                                6. Sélectionner le dossier dans lequel les photos prises
                                                                sont enregistrées, puis sélectionnez le document
                                                                correspondant (le recto de votre permis si on reste dans
                                                                l'exemple, c'est-à-dire celui avec votre photo).<br>
                                                                7. Répétez cette action pour chaque document à
                                                                télécharger.<br>
                                                                8. Valider en cliquant sur le bouton vert en bas à
                                                                droite “je valide mes documents” <br>
                                                                9. Vérifier que la mention “Validé” apparaît en vert en
                                                                haut à droite la rubrique “Mes documents”. <br>
                                                            </p>
                                                            <p>
                                                                Voilà c’est terminé !
                                                            </p>

                                                        </li>
                                                    </ul>
                                                </li>

                                                <!--
                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;"><div style="float:left;width:85%"> Quelles informations dois-je compléter ?</div></a>
                                                <ul class="submenu">
                                                <li>
                                                <p>
                                                Il vous suffit de cliquer sur la rubrique “Mes informations” dans le menu général de gauche. Ensuite, complétez vos informations dans les rubriques <strong>“Mes données personnelles”</strong>, <strong>“Mon permis de conduire”</strong> et <strong>“Mon stage”</strong> puis validez en cliquant sur le bouton “Je valide”
                                                Pour compléter ou modifier une information, cliquez directement sur l’information en bleu.
                                                </p>
                                                </li>
                                                </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;"><div style="float:left;width:85%"> Comment modifier ou ajouter une information ?</div></a>
                                                <ul class="submenu">
                                                <li>
                                                <p>
                                                Vous êtes sur la page “Mes informations” et vous souhaitez modifier ou ajouter une information?
                                                </p>
                                                <p>
                                                C’est très simple:
                                                <ul>
                                                <li> => Il vous suffit de cliquer sur l’information en bleu que vous souhaitez modifier. L’information concernée apparaît alors à l’intérieur d’un champ avec un bouton “Valider” sur la droite. </li>
                                                <li> => Effectuez la modification nécessaire directement dans ce champ puis cliquez sur le bouton “Valider” sur la droite. Voilà c’est terminé, la modification a été prise en compte.</li>
                                                </ul>
                                                </p>
                                                &nbsp;<br>
                                                <p>
                                                N’oubliez pas que vous devez compléter vos informations dans les 3 rubriques:
                                                <ul>
                                                <li> => <strong>“Mes données personnelles”</strong></li>
                                                <li> => <strong>“Mon permis de conduire”</strong></li>
                                                <li> => <strong>“Mon stage”</strong></li>
                                                <li> => Puis validez en cliquant sur le bouton “Je valide” en bas à droite de chaque espace</li>
                                                </ul>

                                                </p>
                                                </li>
                                                </ul>
                                                </li>
                                                -->


                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 60px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Quand j’essaie de télécharger
                                                            mes document, j’ai des erreurs techniques qui apparaissent.
                                                            Que puis-je faire ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p>
                                                                Changez de navigateur Internet. Nous vous conseillons
                                                                fortement d’utiliser Google Chrome. Mais il en existe
                                                                plusieurs:<br>
                                                                ● Google Chrome<br>
                                                                ● Firefox<br>
                                                                ● Internet Explorer<br>
                                                                ● Opera<br>
                                                            </p>

                                                            <p>
                                                                <strong>Ca ne fonctionne toujours pas ?</strong><br>
                                                                Faites appel à un ami qui dispose d’un ordinateur. Vous
                                                                pouvez lui transférez vos documents:<br>
                                                                ● à l’aide d’une clé USB. Votre ami n’aura plus qu’à
                                                                brancher cette clé USB sur son ordinateur pour récupérer
                                                                vos fichiers<br>
                                                                ● en lui envoyant vos documents par mail <br>
                                                            </p>
                                                            <p>
                                                                Si malgré tout, vous êtes toujours dans une impasse,
                                                                envoyez-nous un message via notre page Contact.
                                                            </p>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Il me manque certains
                                                            documents. Que dois-je faire ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p>
                                                                Afin que votre stage soit validé en préfecture, vous
                                                                devez fournir l’intégralité des documents demandés:<br>
                                                                &nbsp;&nbsp;● En cas de perte: déclaration de perte ou
                                                                relevé intégral d’information<br>
                                                                &nbsp;&nbsp;● En cas de vol: déclaration de vol ou
                                                                relevé intégral d’information<br>
                                                                &nbsp;&nbsp;● En cas de suspension ou de rétention: avis
                                                                de suspension ou avis de rétention<br>
                                                            </p>

                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Comment vérifier que mon
                                                            dossier est complet ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p>
                                                                Votre dossier est complet une fois que vous avez
                                                                complété toutes les informations et téléchargé les
                                                                documents nécessaires
                                                            </p>
                                                            <p>
                                                                Si votre dossier est complet voici les informations qui
                                                                apparaissent sur votre Espace Stagiaire:<br>
                                                                &nbsp;&nbsp;1. Page “Mes informations”:la mention
                                                                “Validé” apparaît en haut à droite des rubriques “Mes
                                                                données personnelles”, “Mon permis de conduire”, “Mon
                                                                stage” et mes documents.<br>
                                                                &nbsp;&nbsp;2. Page “Mon Stage”:<br>
                                                                &nbsp;&nbsp;&nbsp;&nbsp;● un bouton affiche sur la
                                                                droite “Modifier mon dossier”<br>
                                                                &nbsp;&nbsp;&nbsp;&nbsp;● la mention “ Dossier Validé”
                                                                apparaît en rouge dan le fil d’ariane bleu (en haut de
                                                                page) qui vous permet de suivre l'avancement de votre
                                                                dossier.
                                                            </p>
                                                            <p>
                                                                Si votre dossier est incomplet:<br>
                                                                &nbsp;&nbsp;1. Page “Mes informations”, au moins une
                                                                rubrique affiche la mention rouge “A valider”.<br>
                                                                &nbsp;&nbsp;2. Page “Mon Stage”:<br>
                                                                &nbsp;&nbsp;&nbsp;&nbsp;● un bouton rouge signale
                                                                “Inscription incomplète” sur la droite <br>
                                                                &nbsp;&nbsp;&nbsp;&nbsp;● la mention “A valider”
                                                                apparait en rouge dan le fil d’ariane bleu (en haut de
                                                                page) qui vous permet de suivre l'avancement de votre
                                                                dossier. ce même bouton affiche “Modifier mon dossier”,
                                                                ce qui signifie que, à moins d’un changement de
                                                                situation, nous avons tous les éléments nécessaires.
                                                            </p>

                                                        </li>
                                                    </ul>
                                                </li>

                                            </ul>
                                        </li>


                                        <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 60px;
    justify-content: space-between;">
                                                <div style="float:left;width:85%">JE SOUHAITE CHANGER D’AVIS
                                                    (Annulation, changement de date, demande de remboursement)
                                                </div>
                                            </a>
                                            <ul class="submenu">

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Je souhaite changer d’avis
                                                            sur mon stage. Dois-je régler des frais ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            Vous disposez d’un droit de rétraction de <strong>14
                                                                jours</strong> à partir de votre inscription au stage.
                                                            Dans ce délai vous pouvez changer d’avis à n’importe quel
                                                            instant sans aucun frais à payer et ce jusqu’à la veille du
                                                            stage 18h (horaires de fermeture de nos bureaux). Au delà de
                                                            ce délai, les changements de date et demande de
                                                            remboursement sont impossibles.<br><br>
                                                            Vous souhaitez changer d’avis dans les 14 jours qui suivent
                                                            votre inscription (Loi Hamon):<br>
                                                            <ul>
                                                                <li>&nbsp;&nbsp;&nbsp=> Changement de date: aucun
                                                                    frais
                                                                </li>
                                                                <li>&nbsp;&nbsp;&nbsp=> Mise en attente de votre
                                                                    dossier: aucun frais
                                                                </li>
                                                                <li>&nbsp;&nbsp;&nbsp=> Annulation de stage et demande
                                                                    de remboursement: aucun frais
                                                                </li>
                                                                <li>&nbsp;</li>
                                                            </ul>

                                                            Vous souhaitez changer d’avis une fois que les 14 jours sont
                                                            écoulés:
                                                            <ul>
                                                                <li>&nbsp;&nbsp;&nbsp=> Changement de date: impossible
                                                                    (le stage est dû entièrement)
                                                                </li>
                                                                <li>&nbsp;&nbsp;&nbsp=> Mise en attente de votre
                                                                    dossier: impossible (le stage est dû entièrement)
                                                                </li>
                                                                <li>&nbsp;&nbsp;&nbsp=> Annulation de stage et demande
                                                                    de remboursement: impossible (le stage est dû
                                                                    entièrement)
                                                                </li>
                                                            </ul>

                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Puis-je changer d’avis une
                                                            fois que le délai des 14 jours est écoulé ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            Malheureusement, au delà de ce délai le changement d’avis et
                                                            le remboursement ne sont plus possibles:

                                                            <ul>
                                                                <li>&nbsp;</li>
                                                                <li>&nbsp;&nbsp;&nbsp=> Changement de date: impossible
                                                                    (le stage est dû entièrement)
                                                                </li>
                                                                <li>&nbsp;&nbsp;&nbsp=> Mise en attente: impossible (le
                                                                    stage est dû entièrement)
                                                                </li>
                                                                <li>&nbsp;&nbsp;&nbsp=> Demande de remboursement:
                                                                    impossible (le stage est dû entièrement)
                                                                </li>
                                                            </ul>

                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Comment annuler mon
                                                            inscription ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            Il vous suffit de vous rendre sur la page <strong>“Je change
                                                                d’avis”</strong> puis de cliquer sur le bouton rouge
                                                            <strong>“Annuler ma participation”</strong>.
                                                            Ensuite, choisissez le motif d’annulation en fonction de
                                                            votre situation. Selon votre choix, un texte explicatif vous
                                                            apporte toutes les précisions dont vous avez besoin. Vous
                                                            n’avez plus alors qu’à cliquer sur le bouton <strong>“Trouver
                                                                une autre date”</strong> ou <strong>“Mettre mon dossier
                                                                en attente” </strong> ou <strong>“Demander le
                                                                remboursement” </strong>. Si vous n’êtes pas certain de
                                                            votre situation, choisissez plutôt de mettre votre dossier
                                                            en attente. Vous disposerez d’un avoir valable 1 an à valoir
                                                            sur votre prochain stage.
                                                            <br><br>
                                                            <strong>Important:</strong> Conformément aux dispositions de l'article L221-18 du Code de la consommation, vous disposez d'un délai de quatorze (14) jours pour exercer votre droit de rétractation (changer de date ou demander le remboursement). Ce délai court à compter du jour de l’acceptation de l’inscription (date du paiement) et <strong>se termine la veille du stage à 18h</strong>.
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Comment changer de date de
                                                            stage ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            Votre n’êtes plus disponible aux dates que vous aviez
                                                            choisis lors de votre inscription? Aucun souci ! Vous pouvez
                                                            changer de date en vous rendant sur la page <strong>“Je
                                                                change d’avis”</strong> puis en cliquant sur le bouton
                                                            <strong>“Changer de date”</strong>.
                                                            Renseignez ensuite la ville dans laquelle vous souhaitez
                                                            effectuer votre nouveau stage. Enfin, choisissez la date et
                                                            le lieu de votre choix en cliquant sur
                                                            <strong>“Réservez”</strong>. Si le nouveau stage choisi est
                                                            au même prix que votre stage initial, il vous suffit de
                                                            cliquer sur le bouton <strong>“Transfert”</strong>. En
                                                            revanche, si le nouveau stage choisi est plus cher, vous
                                                            devez payer la différence. Pour cela, il suffit de
                                                            renseigner les coordonnées de votre carte bleue puis de
                                                            cliquer sur “Transfert”. <strong>La transaction bancaire est
                                                                totalement sécurisée</strong> (cryptage SSL 128 bits).
                                                            Voilà c’est terminée: votre nouveau stage et toutes les
                                                            informations nécessaires apparaissent sur la page “Mon
                                                            stage” de votre Espace Stagiaire.
                                                            <br><br>
                                                            <strong>Important:</strong> Conformément aux dispositions de l'article L221-18 du Code de la consommation, vous disposez d'un délai de quatorze (14) jours pour exercer votre droit de rétractation (changer de date ou demander le remboursement). Ce délai court à compter du jour de l’acceptation de l’inscription (date du paiement) et <strong>se termine la veille du stage à 18h</strong>.
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Comment mettre mon dossier en
                                                            attente ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            Vous ne souhaitez plus participer au stage aux dates que
                                                            vous aviez choisies et vous n’êtes pas certaine(e) de vos
                                                            prochaines disponibilités? Il vous suffit de vous rendre sur
                                                            la page <strong>“Je change d’avis”</strong> puis de mettre
                                                            votre dossier en attente en cliquant sur le bouton <strong>“Annuler
                                                                ma participation”</strong>. <br>
                                                            Ensuite, dans le menu déroulant qui s’affiche en dessous,
                                                            choisissez le motif “Je ne suis plus disponible à ces
                                                            dates”. Enfin, cliquez sur le bouton <strong>“Mettre mon
                                                                dossier en attente”</strong>. Voilà, c’est terminé !
                                                            Votre inscription est annulée et vous disposez d’un avoir
                                                            équivalent au prix que vous avez payé lors de votre
                                                            inscription. Cet avoir est valable 1 an.
                                                            <br><br>
                                                            <strong>Important:</strong> vous disposez de 14 jours après
                                                            l’inscription pour mettre votre dossier en attente. Une fois
                                                            ce délai écoulé, la mise en attente de votre dossier est
                                                            impossible et le stage est dû entièrement.
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Comment obtenir le
                                                            remboursement de mon stage ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            Il vous suffit de vous rendre sur la page <strong>“Je change
                                                                d’avis”</strong> puis de cliquer puis de cliquer sur le
                                                            bouton <strong>“Annuler ma participation”</strong>.<br>
                                                            Ensuite, dans le menu déroulant qui s’affiche en dessous,
                                                            choisissez le motif qui correspond à votre situation. Puis
                                                            cliquez sur le bouton <strong>“Demander le
                                                                remboursement”</strong>. Votre demande est envoyée
                                                            immédiatement à notre service client qui effectue le
                                                            remboursement sous 8 jours ouvrés. Dès que le remboursement a été
                                                            effectué, vous recevez email informatif. Attention, le
                                                            remboursement sera visible sur <strong>votre relevé bancaire
                                                                sous 5 jours</strong> si votre carte est à débit
                                                            immédiat ou en <strong>fin de mois</strong> si votre carte
                                                            est à débit différé.
                                                            <br><br>
                                                            <strong>Important:</strong> Conformément aux dispositions de l'article L221-18 du Code de la consommation, vous disposez d'un délai de quatorze (14) jours pour exercer votre droit de rétractation (changer de date ou demander le remboursement). Ce délai court à compter du jour de l’acceptation de l’inscription (date du paiement) et <strong>se termine la veille du stage à 18h</strong>.
                                                        </li>
                                                    </ul>
                                                </li>

                                            </ul>
                                        </li>


                                        <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                <div style="float:left;width:85%">MON STAGE A ÉTÉ ANNULÉ</div>
                                            </a>
                                            <ul class="submenu">

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Pourquoi mon stage a-t-il été
                                                            annulé ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p style="line-height: normal;">Si un stage est annul&eacute;,
                                                                c'est parce qu&rsquo;il ne respecte pas les obligations
                                                                pr&eacute;fectorale. Ne doutez pas d&rsquo;une chose :
                                                                si le centre annule votre stage, c&rsquo;est qu&rsquo;il
                                                                ne peut pas faire autrement. Et sachez que si le centre
                                                                maintient la session dans l&rsquo;ill&eacute;galit&eacute;,
                                                                votre stage n&rsquo;est pas valid&eacute; et ne vous
                                                                permet pas de r&eacute;cup&eacute;rer 4 points.</p>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p style="line-height: normal;">Ainsi, pour avoir lieu, un
                                                                stage doit respecter 3 conditions :</p>
                                                            <ul>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt; line-height: normal;">
                                                                    un minimum de 6 participants
                                                                </li>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt; line-height: normal;">
                                                                    deux animateurs
                                                                </li>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt; line-height: normal;">
                                                                    une salle de formation agr&eacute;&eacute;e
                                                                </li>
                                                            </ul>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p style="line-height: normal;">Si l&rsquo;une d&rsquo;elles
                                                                n&rsquo;est pas remplie, le stage sera syst&eacute;matiquement
                                                                annul&eacute;. Et la r&eacute;glementation est la m&ecirc;me
                                                                pour tous ! Plus de d&eacute;tails ci-dessous.</p>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p style="line-height: normal;"><strong><span
                                                                            style="font-size: 12.0pt;">Un minimum de 6 participants</span></strong>
                                                            </p>
                                                            <p style="line-height: normal;">Un stage de sensibilisation
                                                                &agrave; la s&eacute;curit&eacute; routi&egrave;re doit
                                                                comprendre entre 6 et 20 participants. En de&ccedil;&agrave;,
                                                                le centre a l&rsquo;interdiction de maintenir la
                                                                session. Ainsi, si une semaine avant le stage ce nombre
                                                                de 6 n&rsquo;est pas respect&eacute;, le centre se voit
                                                                dans l&rsquo;obligation d&rsquo;annuler la session.
                                                                Cette annulation peut intervenir avant la p&eacute;riode
                                                                des 7 jours si le centre estime qu&rsquo;il n&rsquo;atteindra
                                                                pas le nombre de stagiaires requis. Il pr&eacute;f&egrave;re
                                                                ainsi vous en informer le plus rapidement possible !</p>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p style="line-height: normal;"><u><span
                                                                            style="color: #1155cc;">Arr&ecirc;t&eacute; du 26 juin 2012 fixant les conditions d&rsquo;exploitation des &eacute;tablissements charg&eacute;s d&rsquo;organiser les stages de sensibilisation &agrave; la s&eacute;curit&eacute; routi&egrave;re - Annexe 5</span></u>
                                                            </p>
                                                            <p style="line-height: normal;"><em>&ldquo;Afin de garantir
                                                                    le respect de la r&eacute;glementation, la qualit&eacute;
                                                                    de la formation et les int&eacute;r&ecirc;ts des
                                                                    stagiaires, l&rsquo;exploitant de l&rsquo;&eacute;tablissement
                                                                    ou les personnes d&eacute;sign&eacute;es pour l&rsquo;accueil
                                                                    et l&rsquo;encadrement technique et administratif
                                                                    des stages doivent :</em></p>
                                                            <ul>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt; line-height: normal;">
                                                                    <em>s&rsquo;assurer que le nombre de stagiaires pr&eacute;sents
                                                                        est compris entre six et vingts.&rdquo; </em>
                                                                </li>
                                                            </ul>
                                                            <p style="line-height: normal;"><em>&nbsp;</em></p>
                                                            <p style="line-height: normal;"><strong><span
                                                                            style="font-size: 12.0pt;">Deux animateurs</span></strong>
                                                            </p>
                                                            <p style="line-height: normal;">Le stage doit &ecirc;tre
                                                                anim&eacute; par deux formateurs agr&eacute;&eacute;s,
                                                                un psychologue et un sp&eacute;cialiste de la s&eacute;curit&eacute;
                                                                routi&egrave;re (titulaire du BAFM - Brevet d&rsquo;Aptitude
                                                                &agrave; la Formation de Moniteurs d'enseignement de la
                                                                conduite des v&eacute;hicules terrestres &agrave;
                                                                moteur). De plus, l&rsquo;un des deux doit &ecirc;tre
                                                                titulaire de la GTA (Gestion Technique et
                                                                Administrative). Sans ces deux animateurs, la pr&eacute;fecture
                                                                interdit le maintien du stage. Il arrive donc qu&rsquo;un
                                                                cas de force majeure emp&ecirc;che l&rsquo;un des deux
                                                                animateurs de se rendre au stage. Les centres font tout
                                                                leur possible pour trouver en rempla&ccedil;ant.
                                                                Malheureusement, il peut arriver qu&rsquo;aucun rempla&ccedil;ant
                                                                ne soit disponible et que le stage soit
                                                                annul&eacute;.</p>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p style="line-height: normal;"><u><span
                                                                            style="color: #1155cc;">Arr&ecirc;t&eacute; du 26 juin 2012 fixant les conditions d&rsquo;exploitation des &eacute;tablissements charg&eacute;s d&rsquo;organiser les stages de sensibilisation &agrave; la s&eacute;curit&eacute; routi&egrave;re - Annexe 5</span></u>
                                                            </p>
                                                            <p style="line-height: normal;"><em>&ldquo;Afin de garantir
                                                                    le respect de la r&eacute;glementation, la qualit&eacute;
                                                                    de la formation et les int&eacute;r&ecirc;ts des
                                                                    stagiaires, l&rsquo;exploitant de l&rsquo;&eacute;tablissement
                                                                    ou les personnes d&eacute;sign&eacute;es pour l&rsquo;accueil
                                                                    et l&rsquo;encadrement technique et administratif
                                                                    des stages doivent :</em></p>
                                                            <ul>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt; line-height: normal;">
                                                                    <em>s&rsquo;assurer que le stage est conduit par une
                                                                        &eacute;quipe de deux animateurs pr&eacute;sents
                                                                        pendant toute la dur&eacute;e de la formation, l&rsquo;un
                                                                        psychologue, l&rsquo;autre animateur expert en s&eacute;curit&eacute;
                                                                        routi&egrave;re, titulaires d&rsquo;une
                                                                        autorisation d&rsquo;animer les stages de
                                                                        sensibilisation &agrave; la s&eacute;curit&eacute;
                                                                        routi&egrave;re en cours de validit&eacute;.&rdquo;</em>
                                                                </li>
                                                            </ul>
                                                            <p style="line-height: normal;"><em>&nbsp;</em></p>
                                                            <p style="line-height: normal;"><strong><span
                                                                            style="font-size: 12.0pt;">Une salle de formation agr&eacute;&eacute;</span></strong>
                                                            </p>
                                                            <p style="line-height: normal;">Avant d&rsquo;obtenir un agr&eacute;ment
                                                                pour organiser des stages, les centres doivent faire
                                                                valider les lieux dans lesquels ils souhaitent recevoir
                                                                les stagiaires, g&eacute;n&eacute;ralement des salles de
                                                                s&eacute;minaire dans des h&ocirc;tels. Si l'&eacute;tablissement
                                                                initialement pr&eacute;vu pour recevoir le stage n&rsquo;est
                                                                plus disponible (cas de force majeure), le centre ne
                                                                peut pas d&eacute;placer le stage dans n'importe quel
                                                                lieu. Si aucun lieu d&eacute;j&agrave; agr&eacute;&eacute;
                                                                n&rsquo;est disponible, il se verra dans l&rsquo;obligation
                                                                d&rsquo;annuler la session.</p>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p style="line-height: normal;"><u><span
                                                                            style="color: #1155cc;">Arr&ecirc;t&eacute; du 26 juin 2012 fixant les conditions d'exploitation des &eacute;tablissements charg&eacute;s d'organiser les stages de sensibilisation &agrave; la s&eacute;curit&eacute; routi&egrave;re - Article 2</span></u>
                                                            </p>
                                                            <p style="line-height: normal;"><em>&ldquo;Toute personne d&eacute;sirant
                                                                    obtenir un agr&eacute;ment pour l'exploitation d'un
                                                                    &eacute;tablissement charg&eacute; d'organiser des
                                                                    stages de sensibilisation &agrave; la s&eacute;curit&eacute;
                                                                    routi&egrave;re doit adresser au pr&eacute;fet du d&eacute;partement
                                                                    du lieu d'implantation de l'&eacute;tablissement une
                                                                    demande dat&eacute;e et sign&eacute;e. [...] La ou
                                                                    les salles de formation doivent &ecirc;tre situ&eacute;es
                                                                    dans un local adapt&eacute; &agrave; la formation,
                                                                    &ecirc;tre d'une superficie minimale de 35 m&sup2;
                                                                    chacune et r&eacute;pondre aux r&egrave;gles d'hygi&egrave;ne,
                                                                    de s&eacute;curit&eacute; et d'accessibilit&eacute;
                                                                    des &eacute;tablissements recevant du public.
                                                                    Elle(s) doi(ven)t disposer d'un &eacute;clairage
                                                                    naturel occultable et des capacit&eacute;s
                                                                    d'installation du mat&eacute;riel audiovisuel,
                                                                    informatique et p&eacute;dagogique n&eacute;cessaire
                                                                    au bon d&eacute;roulement des stages.&rdquo;</em>
                                                            </p>
                                                            <p style="line-height: normal;"><span
                                                                        style="color: #3c78d8;">&nbsp;</span></p>
                                                            <p>
                                                                <span style="font-size: 11.0pt; line-height: 115%; font-family: 'Arial',sans-serif;">Rassurez-vous, nous avons rigoureusement s&eacute;lectionn&eacute; les centres qui sont les mieux organis&eacute;s et qui ont un taux d&rsquo;annulation le plus bas. En s&eacute;lectionnant un stage chez Prostagespermis, vous choisissez la s&eacute;curit&eacute;.</span>
                                                            </p>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Que dois-je faire si mon
                                                            stage a été annulé ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            Votre stage a été annulé par le centre ? Vous disposez alors
                                                            d’un avoir équivalent au prix du stage que vous avez payé.
                                                            Cet avoir est valable un an.
                                                            3 solutions s’offrent à vous:
                                                            <ol>
                                                                <li>&nbsp;&nbsp; => Vous ne savez pas quand seront vos
                                                                    prochaines disponibilités: conservez cet avoir et
                                                                    choisissez une nouvelle date de stage une fois que
                                                                    l’aurez décidé en cliquant sur la rubrique <strong>“Je
                                                                        change d’avis”</strong> et laissez-vous guider
                                                                </li>
                                                                <li>&nbsp;&nbsp; => Choisissez dès maintenant un nouveau
                                                                    stage en cliquant sur la rubrique <strong>“Je change
                                                                        d’avis”</strong> et laissez-vous guider
                                                                </li>
                                                                <li>&nbsp;&nbsp; => Vous souhaitez obtenir le
                                                                    remboursement: cliquez sur la rubrique “Je change
                                                                    d’avis” et laissez-vous guider
                                                                </li>
                                                            </ol>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Je veux m’inscrire sur une
                                                            autre date
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            Une fois le stage annulé par le Centre, vous disposez alors
                                                            d’un avoir équivalent au prix du stage que vous avez payé.
                                                            Cet avoir est valable un an.
                                                            <br><br>
                                                            Pour choisir un nouveau stage, rendez-vous sur la page
                                                            <strong>“Je change d’avis”</strong> puis en cliquez sur le
                                                            bouton <strong>“Changer de date”</strong>.
                                                            <br><br>
                                                            Renseignez ensuite la ville dans laquelle vous souhaitez
                                                            effectuer votre nouveau stage. Enfin, choisissez la date et
                                                            le lieu de votre choix en cliquant sur
                                                            <strong>“Réservez”</strong>. Si le nouveau stage choisi est
                                                            au même prix que votre stage initial, il vous suffit de
                                                            cliquer sur le bouton <strong>“Transfert”</strong>.<br>
                                                            En revanche, si le nouveau stage choisi est plus cher, vous
                                                            devez payer la différence. Pour cela, il suffit de
                                                            renseigner les coordonnées de votre carte bleue puis de
                                                            cliquer sur “Transfert”.
                                                            <br><br>
                                                            <strong>La transaction bancaire est totalement
                                                                sécurisée</strong> (cryptage SSL 128 bits). Voilà c’est
                                                            terminée: votre nouveau stage et toutes les informations
                                                            nécessaires apparaissent sur la page “Mon stage” de votre
                                                            Espace Stagiaire.
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Je veux obtenir le
                                                            remboursement
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            Il vous suffit de vous rendre sur la page <strong>“Je change
                                                                d’avis”</strong> puis de cliquer puis de cliquer sur le
                                                            bouton <strong>“Annuler ma participation”</strong>.
                                                            <br><br>
                                                            Ensuite, dans le menu déroulant qui s’affiche en dessous,
                                                            choisissez le motif qui correspond à votre situation. Puis
                                                            cliquez sur le bouton <strong>“Demander le
                                                                remboursement”</strong>. Votre demande est envoyée
                                                            immédiatement à notre service client qui effectue le
                                                            remboursement sous 7 jours.
                                                            <br><br>
                                                            Dès que le remboursement a été effectué, vous recevez email
                                                            informatif. Attention, le remboursement sera visible sur
                                                            <strong>votre relevé bancaire sous 5 jours</strong> si votre
                                                            carte est à débit immédiat ou en <strong>fin de
                                                                mois</strong> si votre carte est à débit différé.
                                                        </li>
                                                    </ul>
                                                </li>

                                            </ul>
                                        </li>


                                        <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                <div style="float:left;width:85%">LE DÉROULEMENT DU STAGE</div>
                                            </a>
                                            <ul class="submenu">

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> A quelle heure dois-je me
                                                            présenter ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p style="line-height: normal;">Pour conna&icirc;tre tous
                                                                les d&eacute;tails du stage,&nbsp; rendez-vous sur la
                                                                page<u><span style="color: #1155cc;"> &ldquo;Mon stage&rdquo;</span></u>
                                                                de votre Espace Stagiaire. L&rsquo;heure &agrave;
                                                                laquelle vous &ecirc;tes attendu est indiqu&eacute; en
                                                                gros dans la rubrique <strong>&ldquo;A quelle heure
                                                                    &ccedil;a commence?</strong>&rdquo;. C&rsquo;est
                                                                aussi indiqu&eacute; dans le mail que avez re&ccedil;u
                                                                apr&egrave;s votre inscription.</p>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p style="line-height: normal;">Aucun retard ou d&eacute;part
                                                                anticip&eacute; ne sont tol&eacute;r&eacute;s. Idem pour
                                                                une absence partielle. Vous devez &ecirc;tre pr&eacute;sent
                                                                dans la salle pendant les 14h que dure la formation.
                                                                Cela signifie que, pour les deux jours de formation,
                                                                vous ne devez pas :</p>
                                                            <ul>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt; line-height: normal;">
                                                                    arriver en retard le matin
                                                                </li>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt; line-height: normal;">
                                                                    partir plus t&ocirc;t en pause d&eacute;jeuner
                                                                </li>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt; line-height: normal;">
                                                                    revenir plus tard de pause d&eacute;jeuner
                                                                </li>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt; line-height: normal;">
                                                                    partir plus t&ocirc;t en fin de stage
                                                                </li>
                                                            </ul>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p style="line-height: normal;"><strong>Pourquoi une telle
                                                                    rigueur ?</strong> Parce que les centres, en tant qu&rsquo;organisme
                                                                agr&eacute;&eacute;, sont oblig&eacute;s de faire
                                                                respecter la loi. S&rsquo;ils ne respectent pas leurs
                                                                engagements envers la pr&eacute;fecture, l&rsquo;agr&eacute;ment
                                                                qui leur a &eacute;t&eacute; d&eacute;livr&eacute; leur
                                                                est retir&eacute;.</p>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p style="line-height: normal;">Ainsi, quel que soit le
                                                                motif et la dur&eacute;e de votre absence, votre stage
                                                                ne sera pas valid&eacute; (pas de r&eacute;cup&eacute;ration
                                                                de points) si vous ne respectez pas rigoureusement les
                                                                horaires. Ainsi, si vous arrivez en retard le premier ou
                                                                le deuxi&egrave;me jour, le matin ou apr&egrave;s la
                                                                pause d&eacute;jeuner, l&rsquo;acc&egrave;s au stage
                                                                vous sera refus&eacute;.</p>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p style="line-height: normal;"><strong>Pourquoi ? </strong>Car
                                                                en cas de retard, les animateurs sont oblig&eacute;s de
                                                                le signaler dans le dossier qui est transmis &agrave; la
                                                                pr&eacute;fecture. Les retards n&rsquo;&eacute;tant pas
                                                                accept&eacute;s, la pr&eacute;fecture retournera le
                                                                dossier au centre en indiquant que le stage ne peut pas
                                                                &ecirc;tre valid&eacute;.</p>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p style="line-height: normal;"><strong>Conclusion
                                                                    ?</strong> Vous aurez assist&eacute; &agrave; deux
                                                                jours de formation pour rien. Les animateurs pr&eacute;f&egrave;rent
                                                                donc vous refuser en cas de retard, tout simplement pour
                                                                vous &eacute;viter de perdre votre temps !</p>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p style="line-height: normal;">Donc aucun retard ni d&eacute;part
                                                                anticip&eacute; m&ecirc;me de quelques minutes !</p>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Je ne peux pas arriver au
                                                            stage le matin à l’heure. Que dois-je faire ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p style="text-align: justify;"><span
                                                                        style="color: #767171; background: white;">Malheureusement, nous n&rsquo;avons aucune solution &agrave; vous proposer. La Pr&eacute;fecture impose au centre organisateur de refuser </span><strong><span
                                                                            style="color: #222222; background: white;">tout retardataire le jour du stage</span></strong><span
                                                                        style="color: #222222; background: white;">, ne serait-ce que de quelques minutes (article R213-4 du code de la route et de l&rsquo;arr&ecirc;t&eacute; du 26 Juin 2012 fixant les conditions de mise en &oelig;uvre). Si vous arrivez en retard, vous trouverez donc porte close et <strong>aucun remboursement ne sera possible</strong>.</span>
                                                            </p>
                                                            <p style="text-align: justify;"><span
                                                                        style="color: #222222; background: white;">Vous devrez vous inscrire &agrave; nouveau sur un stage et &agrave; vos frais. Prenez donc vos dispositions pour &ecirc;tre sur place en avance et &eacute;viter ainsi les embouteillages et autres probl&egrave;mes de trafic impr&eacute;visibles !</span>
                                                            </p>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Puis-je partir un peu en
                                                            avance en fin de journée ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p style="line-height: normal;">Aucun retard ou d&eacute;part
                                                                anticip&eacute; ne sont tol&eacute;r&eacute;s. Idem pour
                                                                une absence partielle. Vous devez &ecirc;tre pr&eacute;sent
                                                                dans la salle pendant les 14h que dure la formation.
                                                                Cela signifie que, pour les deux jours de formation,
                                                                vous ne devez pas :</p>
                                                            <ul>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt; line-height: normal;">
                                                                    arriver en retard le matin
                                                                </li>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt; line-height: normal;">
                                                                    partir plus t&ocirc;t en pause d&eacute;jeuner
                                                                </li>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt; line-height: normal;">
                                                                    revenir plus tard de pause d&eacute;jeuner
                                                                </li>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt; line-height: normal;">
                                                                    partir plus t&ocirc;t en fin de stage
                                                                </li>
                                                            </ul>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p style="line-height: normal;"><strong>Pourquoi une telle
                                                                    rigueur ?</strong> Parce que les centres, en tant qu&rsquo;organisme
                                                                agr&eacute;&eacute;, sont oblig&eacute;s de faire
                                                                respecter la loi. S&rsquo;ils ne respectent pas leurs
                                                                engagements envers la pr&eacute;fecture, l&rsquo;agr&eacute;ment
                                                                qui leur a &eacute;t&eacute; d&eacute;livr&eacute; leur
                                                                est retir&eacute;.</p>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p style="line-height: normal;">Ainsi, quel que soit le
                                                                motif et la dur&eacute;e de votre absence, votre stage
                                                                ne sera pas valid&eacute; (pas de r&eacute;cup&eacute;ration
                                                                de points) si vous ne respectez pas rigoureusement les
                                                                horaires. Ainsi, si vous arrivez en retard le premier ou
                                                                le deuxi&egrave;me jour, le matin ou apr&egrave;s la
                                                                pause d&eacute;jeuner, l&rsquo;acc&egrave;s au stage
                                                                vous sera refus&eacute;.</p>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p style="line-height: normal;"><strong>Pourquoi ?</strong>
                                                                Car en cas de retard, les animateurs sont oblig&eacute;s
                                                                de le signaler dans le dossier qui est transmis &agrave;
                                                                la pr&eacute;fecture. Les retards n&rsquo;&eacute;tant
                                                                pas accept&eacute;s, la pr&eacute;fecture retournera le
                                                                dossier au centre en indiquant que le stage ne peut pas
                                                                &ecirc;tre valid&eacute;.</p>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p style="line-height: normal;"><strong>Conclusion
                                                                    ?</strong> Vous aurez assist&eacute; &agrave; deux
                                                                jours de formation pour rien. Les animateurs pr&eacute;f&egrave;rent
                                                                donc vous refuser en cas de retard, tout simplement pour
                                                                vous &eacute;viter de perdre votre temps !</p>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p style="line-height: normal;">Donc aucun retard ni d&eacute;part
                                                                anticip&eacute; m&ecirc;me de quelques minutes !</p>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Je ne sais pas où me rendre
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p style="line-height: normal;">Pour conna&icirc;tre l&rsquo;adresse
                                                                exacte de votre stage, rendez-vous sur la page<u><span
                                                                            style="color: #1155cc;"> &ldquo;Mon stage&rdquo;</span></u>
                                                                de votre Espace Stagiaire. L&rsquo;adresse est indiqu&eacute;
                                                                en gros dans la rubrique <strong>&ldquo;Comment je vais
                                                                    au stage?</strong>&rdquo;. C&rsquo;est aussi indiqu&eacute;
                                                                dans le mail que avez re&ccedil;u apr&egrave;s votre
                                                                inscription.</p>
                                                            <p style="line-height: normal;">Dans votre Espace Stagiaire,
                                                                consulter tous les d&eacute;tails de votre stage :
                                                                adresse exacte, plan d&rsquo;acc&egrave;s, coordonn&eacute;es
                                                                GPS, transports en commun, photos de l&rsquo;&eacute;tablissement,
                                                                etc. Tout ce que vous devez savoir avant d&rsquo;y aller
                                                                !</p>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Quels document apporter avec
                                                            moi le jour du stage ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p>
                                                                En fonction de votre situation:<br>
                                                                &nbsp;&nbsp; ● Permis de conduire + <strong>une
                                                                    photocopie recto-verso</strong><br>
                                                                &nbsp;&nbsp; OU <br>
                                                                &nbsp;&nbsp; ● En cas de perte: déclaration de perte ou
                                                                relevé intégral d’information<br>
                                                                &nbsp;&nbsp; OU<br>
                                                                &nbsp;&nbsp; ● En cas de vol: déclaration de vol ou
                                                                relevé intégral d’information<br>
                                                                &nbsp;&nbsp; OU<br>
                                                                &nbsp;&nbsp; ● En cas de suspension ou de rétention:
                                                                avis de suspension ou avis de rétention<br>
                                                            </p>
                                                            <p>
                                                                En fonction de votre cas de stage:<br>
                                                                &nbsp;&nbsp; ● Si cas 2 (stage obligatoire en période
                                                                probatoire): courrier 48N<br>
                                                                &nbsp;&nbsp; ● Si cas 3 (composition pénale) ou 4 (peine
                                                                complémentaire ou mise à l’épreuve): ordonnance
                                                                pénale<br>
                                                            </p>
                                                            <p>
                                                                Dans tous les cas:<br>
                                                                &nbsp;&nbsp; ● L’original de votre carte d’identité (ou
                                                                votre passeport)<br>
                                                                &nbsp;&nbsp; ● Une enveloppe libellée à votre adresse et
                                                                affranchie au tarif en vigueur<br>
                                                                &nbsp;&nbsp; ● Du papier, un stylo et une bouteille
                                                                d’eau<br>
                                                            </p>

                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Combien de temps dure le
                                                            stage ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p style="line-height: normal;">Le stage se d&eacute;roule
                                                                sur 2 jours cons&eacute;cutifs, pour un total de 14h de
                                                                formation (2 x 7 heures). C&rsquo;est la dur&eacute;e l&eacute;gale
                                                                qui est impos&eacute;e pour un stage. Inutile de
                                                                chercher plus court ou sur deux samedis, &ccedil;a n&rsquo;existe
                                                                pas (ou alors fuyez !).</p>
                                                            <p><u><span style="color: #1155cc;">Arr&ecirc;t&eacute; du 26 juin 2012 - Annexe 5</span></u>
                                                            </p>
                                                            <p><u>Obligations relatives &agrave; l&rsquo;organisation
                                                                    des stages de sensibilisation &agrave; la s&eacute;curit&eacute;
                                                                    routi&egrave;re</u>.</p>
                                                            <p><em>&ldquo;Afin de garantir le respect de la r&eacute;glementation,
                                                                    la qualit&eacute; de la formation et les int&eacute;r&ecirc;ts
                                                                    des stagiaires, l&rsquo;exploitant de l'&eacute;tablissement
                                                                    ou les personnes d&eacute;sign&eacute;es pour l&rsquo;accueil
                                                                    et l&rsquo;encadrement technique et administratif
                                                                    des stagiaires doivent : </em></p>
                                                            <ul>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt;">
                                                                    <em>programmer un stage sur <strong>deux jours <u>cons&eacute;cutifs</u>,
                                                                            &agrave; raison de 7 heures par jours <u>effectives</u></strong>,
                                                                        [...] en excluant le dimanche et les jours f&eacute;ri&eacute;s
                                                                        ainsi que les horaires correspondant &agrave; du
                                                                        travail de nuit&rdquo;.</em></li>
                                                            </ul>
                                                            <p>&nbsp;</p>
                                                            <p>La pr&eacute;sence aux 14h de formation est donc n&eacute;cessaire
                                                                et obligatoire !</p>

                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Dois-je réviser le code de la
                                                            route ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p style="line-height: normal;">Heureusement non ! Le stage
                                                                auquel vous allez assister n&rsquo;est pas l&agrave;
                                                                pour vous rappeler que vous devez vous arr&ecirc;ter
                                                                &agrave; un feu rouge ou que c&rsquo;est &agrave; 130
                                                                que vous devez rouler sur l&rsquo;autoroute. Pas de
                                                                test, pas d&rsquo;examen. La seule condition pour
                                                                valider votre stage est d&rsquo;assister &agrave; l&rsquo;int&eacute;gralit&eacute;
                                                                des 14h de formation.</p>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p style="line-height: normal;">Le stage va plut&ocirc;t
                                                                vous sensibiliser au comment vivre ensemble sur la
                                                                route. Analyses de cas, astuces, on vous apprendra
                                                                justement ce que vous n&rsquo;avez pas appris &agrave; l&rsquo;auto-&eacute;cole
                                                                pour conserver vos points !</p>

                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Puis-je faire mon stage sur
                                                            deux samedis ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p style="line-height: normal;">Non, le stage doit
                                                                obligatoirement &ecirc;tre r&eacute;alis&eacute; sur
                                                                deux jours cons&eacute;cutifs (donc deux jours qui se
                                                                suivent).&nbsp;</p>
                                                            <p style="line-height: normal;"><span
                                                                        style="color: #3c78d8;">&nbsp;</span></p>
                                                            <p><u><span style="color: #1155cc;">Arr&ecirc;t&eacute; du 26 juin 2012 - Annexe 5</span></u>
                                                            </p>
                                                            <p><u>Obligations relatives &agrave; l&rsquo;organisation
                                                                    des stages de sensibilisation &agrave; la s&eacute;curit&eacute;
                                                                    routi&egrave;re</u>.</p>
                                                            <p><em>&ldquo;Afin de garantir le respect de la r&eacute;glementation,
                                                                    la qualit&eacute; de la formation et les int&eacute;r&ecirc;ts
                                                                    des stagiaires, l&rsquo;exploitant de l'&eacute;tablissement
                                                                    ou les personnes d&eacute;sign&eacute;es pour l&rsquo;accueil
                                                                    et l&rsquo;encadrement technique et administratif
                                                                    des stagiaires doivent : </em></p>
                                                            <ul>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt;">
                                                                    <em>programmer un stage sur <strong>deux jours <u>cons&eacute;cutifs</u>,
                                                                            &agrave; raison de 7 heures par jours <u>effectives</u></strong>,
                                                                        [...] en excluant le dimanche et les jours f&eacute;ri&eacute;s
                                                                        ainsi que les horaires correspondant &agrave; du
                                                                        travail de nuit&rdquo;.</em></li>
                                                            </ul>

                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Le repas est-il compris dans
                                                            la formation ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p style="line-height: normal;">Non, le d&eacute;jeuner n&rsquo;est
                                                                pas compris dans le tarif de la formation. Vous pouvez
                                                                cependant d&eacute;jeuner sur place &agrave; vos frais
                                                                dans la plupart des lieux de stage (g&eacute;n&eacute;ralement
                                                                des h&ocirc;tels).</p>

                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Je suis sourd ou malentendant
                                                            : comment faire ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p style="line-height: normal;">Si vous &ecirc;tes atteint d&rsquo;un
                                                                handicap vous emp&ecirc;chant de suivre la formation
                                                                normalement, nous vous invitons &agrave; vous faire
                                                                accompagner d&rsquo;une tierce personne.</p>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p><u><span style="color: #1155cc;">Arr&ecirc;t&eacute; du 26 juin 2012 - Annexe 5</span></u>
                                                            </p>
                                                            <p><u>Obligations relatives &agrave; l&rsquo;organisation
                                                                    des stages de sensibilisation &agrave; la s&eacute;curit&eacute;
                                                                    routi&egrave;re</u>.</p>
                                                            <p><em>&ldquo;Afin de garantir le respect de la r&eacute;glementation,
                                                                    la qualit&eacute; de la formation et les int&eacute;r&ecirc;ts
                                                                    des stagiaires, l&rsquo;exploitant de l'&eacute;tablissement
                                                                    ou les personnes d&eacute;sign&eacute;es pour l&rsquo;accueil
                                                                    et l&rsquo;encadrement technique et administratif
                                                                    des stagiaires doivent : </em></p>
                                                            <p>
                                                                <em><span style="font-size: 11.0pt; line-height: 115%; font-family: 'Arial',sans-serif;">conseiller l&rsquo;accompagnement, durant le stage, d&rsquo;un interpr&egrave;te si le stagiaire est non francophone ou d&rsquo;<strong>un m&eacute;diateur en langue des signes</strong> s&rsquo;il est sourd ou malentendant ou d&rsquo;une personne facilitant son autonomie s&rsquo;il est handicap&eacute;.</span></em>
                                                            </p>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Je ne parle pas ou mal le
                                                            français
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p style="line-height: normal;">Si le stagiaire ne parle pas
                                                                ou mal le fran&ccedil;ais, nous l&rsquo;invitons
                                                                &agrave; se faire accompagner d&rsquo;une tierce
                                                                personne.</p>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p><u><span style="color: #1155cc;">Arr&ecirc;t&eacute; du 26 juin 2012 - Annexe 5</span></u>
                                                            </p>
                                                            <p><u>Obligations relatives &agrave; l&rsquo;organisation
                                                                    des stages de sensibilisation &agrave; la s&eacute;curit&eacute;
                                                                    routi&egrave;re</u>.</p>
                                                            <p><em>&ldquo;Afin de garantir le respect de la r&eacute;glementation,
                                                                    la qualit&eacute; de la formation et les int&eacute;r&ecirc;ts
                                                                    des stagiaires, l&rsquo;exploitant de l'&eacute;tablissement
                                                                    ou les personnes d&eacute;sign&eacute;es pour l&rsquo;accueil
                                                                    et l&rsquo;encadrement technique et administratif
                                                                    des stagiaires doivent : </em></p>
                                                            <ul>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt;">
                                                                    <em>conseiller l&rsquo;accompagnement, durant le
                                                                        stage, d&rsquo;un interpr&egrave;te si le
                                                                        stagiaire est non francophone ou
                                                                        d&rsquo;<strong>un m&eacute;diateur en langue
                                                                            des signes</strong> s&rsquo;il est sourd ou
                                                                        malentendant ou d&rsquo;une personne facilitant
                                                                        son autonomie s&rsquo;il est
                                                                        handicap&eacute;.</em></li>
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Je ne sais pas lire ni
                                                            écrire
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p style="line-height: normal;">Si le stagiaire ne sait pas
                                                                lire ou &eacute;crire, nous l&rsquo;invitons &agrave; se
                                                                faire accompagner d&rsquo;une tierce personne.</p>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p><u><span style="color: #1155cc;">Arr&ecirc;t&eacute; du 26 juin 2012 - Annexe 5</span></u>
                                                            </p>
                                                            <p><u>Obligations relatives &agrave; l&rsquo;organisation
                                                                    des stages de sensibilisation &agrave; la s&eacute;curit&eacute;
                                                                    routi&egrave;re</u>.</p>
                                                            <p><em>&ldquo;Afin de garantir le respect de la r&eacute;glementation,
                                                                    la qualit&eacute; de la formation et les int&eacute;r&ecirc;ts
                                                                    des stagiaires, l&rsquo;exploitant de l'&eacute;tablissement
                                                                    ou les personnes d&eacute;sign&eacute;es pour l&rsquo;accueil
                                                                    et l&rsquo;encadrement technique et administratif
                                                                    des stagiaires doivent : </em></p>
                                                            <ul>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt;">
                                                                    <em>conseiller l&rsquo;accompagnement, durant le
                                                                        stage, d&rsquo;un interpr&egrave;te si le
                                                                        stagiaire est non francophone ou
                                                                        d&rsquo;<strong>un m&eacute;diateur en langue
                                                                            des signes</strong> s&rsquo;il est sourd ou
                                                                        malentendant ou d&rsquo;une personne facilitant
                                                                        son autonomie s&rsquo;il est
                                                                        handicap&eacute;.</em></li>
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                </li>

                                            </ul>
                                        </li>


                                        <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                <div style="float:left;width:85%">ABSENCE, EXCLUSION ET RETARD</div>
                                            </a>
                                            <ul class="submenu">

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 60px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Je n’ai pas pu me rendre au
                                                            stage car j’ai eu un accident sur le chemin. Puis-je être
                                                            inscrit gratuitement sur un autre stage ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            Malheureusement, toute absence au stage ne peut donner lieu
                                                            à aucun replacement sur une autre session ou un
                                                            remboursement. Vous devez vous réinscrire à vos frais.<br>
                                                            Toutefois si vous avez fait l’objet d’un cas de force de
                                                            majeur comme un accident et que vous pouvez en produire les
                                                            justificatifs, notre service client étudiera attentivement
                                                            votre situation pour décider si vous pouvez prétendre ou non
                                                            à un dédommagement.<br>
                                                            Adressez-nous un message via la page
                                                            <strong>“Contact”</strong> de votre Espace Stagiaire en nous
                                                            expliquant en détail ce qui vous a empêché d’être présent au
                                                            stage. Nous traiterons votre demande sous 7 jours et nous
                                                            vous contacterons pour vous signaler les justificatifs
                                                            nécessaires à produire.
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 60px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Je suis arrivé en retard au
                                                            stage le matin et j’ai été refusé par les animateurs. Que
                                                            dois-je faire ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p style="text-align: justify;">Nous vous avions pr&eacute;venu
                                                                &agrave; maintes reprises par mails et sms. C&rsquo;est
                                                                aussi indiqu&eacute; en &eacute;vidence sur la page
                                                                &ldquo;Mon stage&rdquo; de votre Espace Stagiaire ainsi
                                                                que sur nos <u>conditions g&eacute;n&eacute;rales de
                                                                    vente. </u>La Pr&eacute;fecture ne tol&egrave;re
                                                                aucun retard ne serait-ce que de quelques minutes
                                                                (article R213-4 du code de la route et de l&rsquo;arr&ecirc;t&eacute;
                                                                du 26 Juin 2012 fixant les conditions de mise en &oelig;uvre)
                                                                et les animateurs sont tenus d&rsquo;appliquer cette r&egrave;gle
                                                                scrupuleusement. Dans ce cas, <strong>aucun
                                                                    remboursement n&rsquo;est possible.</strong></p>
                                                            <p style="text-align: justify;">Pour suivre un stage il faut
                                                                vous r&eacute;inscrire &agrave; vos frais. Il vous
                                                                suffit de cliquer sur la rubrique <u>&ldquo;Je change d&rsquo;avis&rdquo;</u>
                                                                dans le menu de gauche de votre Espace Stagiaire puis de
                                                                choisir une date de stage en cliquant sur <u>&ldquo;R&eacute;server
                                                                    un stage&rdquo;</u></p>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> J’ai été exclu pendant le
                                                            stage. Que dois-je faire ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p style="text-align: justify;">La Pr&eacute;fecture est tr&egrave;s
                                                                stricte concernant le comportement des stagiaires
                                                                pendant la session et ne tol&egrave;re aucun
                                                                comportement inappropri&eacute; comme cela est indiqu&eacute;
                                                                dans l&rsquo;<a
                                                                        href="https://www.legifrance.gouv.fr/affichTexte.do?cidTexte=JORFTEXT000026087827&amp;categorieLien=id"><span
                                                                            style="color: #337ab7; background: #F5F7FF;">Arr&ecirc;t&eacute; du 26 juin 2012 fixant les conditions d'exploitation des &eacute;tablissements charg&eacute;s d'organiser les stages de sensibilisation &agrave; la s&eacute;curit&eacute; routi&egrave;re</span></a>.
                                                            </p>
                                                            <p style="text-align: justify;">&nbsp;</p>
                                                            <p style="text-align: justify;">Dans votre Espace Stagiaire,
                                                                rubrique &ldquo;Mon stage&rdquo; nous vous avions indiqu&eacute;
                                                                d&egrave;s votre inscription l&rsquo;attitude &agrave;
                                                                avoir:</p>
                                                            <p style="text-align: justify;">&nbsp;</p>
                                                            <p style="text-align: center;"><strong><em><span
                                                                                style="color: #676a6c; background: #F5F7FF;">&ldquo;Tout comportement inappropri&eacute; fera l&rsquo;objet d&rsquo;une exclusion ferme et d&eacute;finitive du stage. Votre stage ne sera pas rembours&eacute;, votre formation ne sera pas valid&eacute;e et vous devrez payer un autre stage dans son int&eacute;gralit&eacute;.&rdquo;</span></em></strong>
                                                            </p>
                                                            <p style="text-align: justify;">&nbsp;</p>
                                                            <p style="text-align: justify;">C&rsquo;est aussi indiqu&eacute;
                                                                tr&egrave;s clairement dans nos <u><span
                                                                            style="color: #1155cc;">conditions g&eacute;n&eacute;rales de vente. </span></u>que
                                                                vous avez accept&eacute;.</p>
                                                            <p style="text-align: justify;">&nbsp;</p>
                                                            <p style="text-align: justify;">Dans une telle situation
                                                                <strong>aucun remboursement n&rsquo;est
                                                                    possible</strong>.</p>
                                                            <p style="text-align: justify;">&nbsp;</p>
                                                            <p style="text-align: justify;">Pour suivre un stage il faut
                                                                vous r&eacute;inscrire &agrave; vos frais. Il vous
                                                                suffit de cliquer sur la rubrique <u>&ldquo;Je change d&rsquo;avis&rdquo;</u>
                                                                dans le menu de gauche de votre Espace Stagiaire puis de
                                                                choisir une date de stage en cliquant sur <u>&ldquo;R&eacute;server
                                                                    un stage&rdquo;</u></p>
                                                        </li>
                                                    </ul>
                                                </li>

                                            </ul>
                                        </li>

                                        <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                <div style="float:left;width:85%">RECUPERATION DE POINTS ET AGRÉMENTS
                                                    DES CENTRES
                                                </div>
                                            </a>
                                            <ul class="submenu">

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Combien de points je récupère
                                                            après un stage ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p style="line-height: normal;">Participer &agrave; un stage
                                                                permet de r&eacute;cup&eacute;rer 4 points. Ces 4 points
                                                                sont cr&eacute;dit&eacute;s sur votre permis d&egrave;s
                                                                le lendemain du stage, dans la limite de votre plafond
                                                                maximum. Ainsi, si vous avez 9 points sur 12 avant le
                                                                stage, vous ne pourrez r&eacute;cup&eacute;rer que 3
                                                                points. Pensez-donc &agrave; bien v&eacute;rifier votre
                                                                solde de points avant de vous inscrire !</p>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p style="line-height: normal;">Pour rappel, il n&rsquo;est
                                                                possible de r&eacute;cup&eacute;rer des points qu&rsquo;une
                                                                seule fois par an.</p>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Au bout de combien de temps
                                                            je récupère mes points ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p style="line-height: normal;">D&egrave;s le lendemain du
                                                                stage !</p>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p style="line-height: normal;"><u><span
                                                                            style="color: #1155cc;"><a
                                                                                href="http://circulaire.legifrance.gouv.fr/pdf/2013/05/cir_36943.pdf">Extrait de la circulaire relative au r&eacute;gime g&eacute;n&eacute;ral du permis &agrave; points du 11 mars 2004</a></span></u>
                                                            </p>
                                                            <p style="line-height: normal;"><u><span
                                                                            style="color: #1155cc;"><span
                                                                                style="text-decoration: none;">&nbsp;</span></span></u>
                                                            </p>
                                                            <p style="line-height: normal;"><em>&laquo; Le pr&eacute;fet
                                                                    proc&egrave;de &agrave; la reconstitution du nombre
                                                                    de points, dans un d&eacute;lai d&rsquo;un mois
                                                                    &agrave; compter de la r&eacute;ception de l&rsquo;attestation.
                                                                    La reconstitution prend effet <strong>d&egrave;s le
                                                                        lendemain de la derni&egrave;re journ&eacute;e
                                                                        du stage</strong>. &raquo;</em></p>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p style="line-height: normal;">Dans les faits, les points
                                                                ne sont pas visibles sur Internet d&egrave;s le
                                                                lendemain du stage. Pourquoi ?</p>
                                                            <ul>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt; line-height: normal;">
                                                                    le soir du deuxi&egrave;me jour de stage, le centre
                                                                    vous remet votre attestation
                                                                </li>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt; line-height: normal;">
                                                                    sous 15 jours, il remet un double de cette
                                                                    attestation &agrave; la pr&eacute;fecture du d&eacute;partement
                                                                </li>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt; line-height: normal;">
                                                                    un agent administratif enregistre vos 4 points sur
                                                                    le fichier national du permis &agrave; points
                                                                </li>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt; line-height: normal;">
                                                                    vos points apparaissent sur Internet
                                                                </li>
                                                            </ul>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p style="line-height: normal;">La date retenue reste bien
                                                                celle du <strong>lendemain du stage</strong>, et non la
                                                                date o&ugrave; la saisie sera effectu&eacute; sur le
                                                                fichier.</p>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Comment connaître mon solde
                                                            de points ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p style="line-height: normal;">Plusieurs moyens sont mis
                                                                &agrave; votre disposition pour consulter votre solde de
                                                                points :</p>
                                                            <p style="line-height: normal;"><strong>&nbsp;</strong></p>
                                                            <p style="line-height: normal;"><strong>Directement en pr&eacute;fecture</strong>,
                                                                sur pr&eacute;sentation (en personne) d&rsquo;une pi&egrave;ce
                                                                d&rsquo;identit&eacute;. On vous remettra un Relev&eacute;
                                                                Int&eacute;gral d&rsquo;Information (RII) o&ugrave;
                                                                figurent votre solde de points, vos codes de connexion
                                                                &agrave; <u><span style="color: #1155cc;">T&eacute;l&eacute;points</span></u>
                                                                et l&rsquo;historique de votre permis (infractions,
                                                                points, etc. )</p>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p style="line-height: normal;"><strong>Directement en
                                                                    ligne</strong>, sans bouger de chez vous :</p>
                                                            <ul>
                                                                <li>sur le site <strong>telepointspermis.fr</strong> à
                                                                    l’aide de vos identifiants (disponible sur le Relevé
                                                                    Intégral d’Information, les lettre 48N ou 48M si
                                                                    vous les avez reçues)
                                                                </li>
                                                                <li>sur le site <strong>France Connect</strong>, à
                                                                    l’aide d’identifiants pré-existants : ceux que vous
                                                                    utilisez pour payer vos impôts sur impots.gouv.fr,
                                                                    ceux que vous utilisez sur ameli.fr (Sécurité
                                                                    Sociale) ou si vous avez une identité numérique La
                                                                    Poste
                                                                </li>

                                                            </ul>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> J’ai commis une infraction,
                                                            quand mes points sont-ils retirés ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p style="line-height: normal;">Contrairement &agrave; ce
                                                                que l&rsquo;on pourrait penser, les points ne sont pas
                                                                retir&eacute;s le jour de l&rsquo;infraction. En effet,
                                                                la loi estime qu&rsquo;il faut que la r&eacute;alit&eacute;
                                                                de l&rsquo;infraction soit &eacute;tablie, c&rsquo;est-&agrave;-dire
                                                                que le contrevenant ait reconnu sa faute, ou bien qu&rsquo;il
                                                                ait &eacute;puis&eacute; tous les moyens de recours.</p>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p style="line-height: normal;">Dans les faits, les points
                                                                sont donc retir&eacute;s :</p>
                                                            <ul>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt; line-height: normal;">
                                                                    le jour du paiement de l&rsquo;amende, car payer
                                                                    signifie reconna&icirc;tre sa culpabilit&eacute;
                                                                </li>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt; line-height: normal;">
                                                                    &agrave; d&eacute;faut, &agrave; 45 jours apr&egrave;s
                                                                    la r&eacute;ception de l&rsquo;amende (c&rsquo;est
                                                                    &agrave; dire &agrave; l&rsquo;&eacute;mission de l&rsquo;amende
                                                                    major&eacute;e), car c&rsquo;est le d&eacute;lai l&eacute;gal
                                                                    pendant lequel vous pouvez contester. Ne pas
                                                                    contester dans le d&eacute;lai &eacute;quivaut donc
                                                                    &agrave; reconna&icirc;tre sa culpabilit&eacute;
                                                                </li>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt; line-height: normal;">
                                                                    dans le cas d&rsquo;une composition p&eacute;nale,
                                                                    les points sont retir&eacute;s une fois l&rsquo;ensemble
                                                                    des peines ex&eacute;cut&eacute;s
                                                                </li>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt; line-height: normal;">
                                                                    en cas de d&eacute;lit et de passage au tribunal,
                                                                    les points sont retir&eacute;s une fois le jugement
                                                                    rendu d&eacute;finitif
                                                                </li>
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Les stages proposés sont-ils
                                                            agréés par la préfecture ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p>Tous les stages propos&eacute;s sur le site
                                                                prostagespermis.fr sont agr&eacute;&eacute;s par la pr&eacute;fecture
                                                                du d&eacute;partement dans lequel ils sont organis&eacute;s.
                                                                Avant de pouvoir diffuser leurs offres sur notre site,
                                                                les organismes de r&eacute;cup&eacute;ration de points
                                                                doivent imp&eacute;rativement montrer patte blanche et
                                                                nous <strong>fournir l&rsquo;int&eacute;gralit&eacute;
                                                                    de leurs agr&eacute;ments en cours de validit&eacute;</strong>.
                                                            </p>
                                                            <p style="line-height: normal;">Pour connaitre le num&eacute;ro
                                                                d&rsquo;agr&eacute;ment rendez-vous sur le site
                                                                prostagespermis. Sur la page r&eacute;pertoriant toutes
                                                                les dates de stage de votre ville, cliquez sur &ldquo;+
                                                                d&rsquo;infos&rdquo;. Une fen&ecirc;tre s&rsquo;ouvre
                                                                avec toutes les pr&eacute;cisions n&eacute;cessaires
                                                                dont le num&eacute;ro d&rsquo;agr&eacute;ment.</p>
                                                            <p>&nbsp;</p>
                                                            <p>Pas de mauvaises surprises, nous faisons le travail pour
                                                                vous !</p>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Où trouver le numéro
                                                            d’agrément ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            C’est très simple ! Pour chaque date proposée sur le site
                                                            prostagespermis, il vous suffit de cliquer sur “+ d’infos”.
                                                            Une fenêtre s’ouvre avec toutes les précisions nécessaires
                                                            dont le numéro d’agrément.<br>
                                                            Sur votre Espace Stagiaire, le numéro d’agrément se trouve
                                                            sur la page <strong>“Mon stage”</strong> rubrique <strong>“Comment
                                                                je vais au stage”</strong>.<br>
                                                            En effet, avant de diffuser les offres de stages de nos
                                                            partenaires, nous leur demandons de nous fournir un agrément
                                                            en cours de validité que nous vérifions. Nous communiquons
                                                            donc en toute transparence ces informations.

                                                        </li>
                                                    </ul>
                                                </li>

                                            </ul>
                                        </li>

                                        <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                <div style="float:left;width:85%">PRIX DES STAGES ET PAIEMENT PAR CARTE
                                                    BANCAIRE
                                                </div>
                                            </a>
                                            <ul class="submenu">

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Pourquoi les prix des stages
                                                            sont-ils différents ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p>Les stages de sensibilisation &agrave; la s&eacute;curit&eacute;
                                                                routi&egrave;re sont organis&eacute;s par des soci&eacute;t&eacute;s
                                                                priv&eacute;es : organismes de formation, associations,
                                                                auto-&eacute;coles ou encore des entreprises sp&eacute;cialis&eacute;es
                                                                dans la s&eacute;curit&eacute; routi&egrave;re.</p>
                                                            <p>&nbsp;</p>
                                                            <p>Ces organismes sont agr&eacute;&eacute;s par les pr&eacute;fectures
                                                                mais, <strong>comme pour tout bien de consommation, ils
                                                                    peuvent fixer les prix librement</strong>. Voil&agrave;
                                                                pourquoi vous pouvez trouver la m&ecirc;me formation
                                                                &agrave; des prix diff&eacute;rents. De la m&ecirc;me
                                                                mani&egrave;re que, selon les auto-&eacute;coles, vous
                                                                allez payer votre permis de conduire 1 200 euros ou 700
                                                                euros !</p>
                                                            <p>&nbsp;</p>
                                                            <p>
                                                                <span style="font-size: 11.0pt; line-height: 115%; font-family: 'Arial',sans-serif;">ProStagesPermis regroupe l&rsquo;ensemble des organismes Low cost partout en France pour que vous puissiez justement comparer et s&eacute;lectionner un stage au prix le plus bas &agrave; c&ocirc;t&eacute; de chez vous.Et nous veillons &agrave; la qualit&eacute; des formations !</span>
                                                            </p>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Puis-je payer avec la carte
                                                            bancaire d’un proche ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p style="line-height: normal;">Bien-s&ucirc;r !</p>
                                                            <p style="line-height: normal;">Vous avez trouv&eacute; le
                                                                stage id&eacute;al... mais vous n'avez pas de carte
                                                                bancaire ? Perdue, vol&eacute;e ou vous n'en avez
                                                                simplement jamais eu ?</p>
                                                            <p style="line-height: normal;">Faites appel &agrave; un ami
                                                                : un coll&egrave;gue, un proche, un oncle, un
                                                                cousine...bref ! Il n'est <strong>pas n&eacute;cessaire
                                                                    que la carte bancaire soit &agrave; votre
                                                                    nom</strong>. Il vous suffit alors de faire un ch&egrave;que
                                                                ou de donner des esp&egrave;ces au d&eacute;tenteur de
                                                                la carte, qui vous pr&ecirc;tera gracieusement (ou
                                                                presque) sa carte bancaire.</p>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Le paiement par carte est-il
                                                            sécurisé ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p>La carte bancaire, c'est un moyen de paiement facile,
                                                                rapide et totalement s&eacute;curis&eacute; (cryptage
                                                                SSL 128 bits).</p>
                                                            <p>&nbsp;</p>
                                                            <p style="text-align: center;"><strong>Serveur s&eacute;curis&eacute;
                                                                    + informations crypt&eacute;es = paiement 100% s&eacute;curis&eacute;
                                                                    !</strong></p>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p><span style="color: blue;">Comment en &ecirc;tre s&ucirc;r ?</span>
                                                            </p>
                                                            <p>Nous utilisons la solution de paiement en ligne du Cr&eacute;dit
                                                                Agricole pour une s&eacute;curisation optimis&eacute;e
                                                                des paiements. E-transactions offre des standards de s&eacute;curit&eacute;
                                                                &eacute;lev&eacute;s : protection des donn&eacute;es
                                                                sensibles de la carte et respect des exigences s&eacute;curitaires
                                                                internationales. La demande d&rsquo;autorisation de
                                                                paiement syst&eacute;matique permet de r&eacute;duire
                                                                les risques de paiements frauduleux.</p>
                                                            <p>&nbsp;</p>
                                                            <p>Vous pouvez jeter un oeil aux avis eKomi (avis
                                                                authentiques non modifiables). Vous constaterez qu&rsquo;aucun
                                                                de nos clients ne s&rsquo;est fait pirater sa carte
                                                                alors que notre soci&eacute;t&eacute; existe depuis 10
                                                                ans !</p>
                                                            <p>&nbsp;</p>
                                                            <p>En haut &agrave; droite de la barre o&ugrave; se trouve
                                                                l'adresse du site (l'url), vous trouverez un petit
                                                                cadenas suivi du code "https" : "s" comme...S&eacute;curit&eacute;
                                                                !</p>
                                                            <p>&nbsp;<img src="img/cb_secure.png"></p>
                                                            <p><span style="color: blue;">&nbsp;</span></p>
                                                            <p>Voici l&rsquo;encart qui appara&icirc;t au moment de
                                                                votre paiement. Les seules informations que vous devez
                                                                fournir sont :</p>
                                                            <ul>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt;">
                                                                    les 16 chiffres de votre carte bancaire (4 s&eacute;ries
                                                                    de 4 chiffres dor&eacute;s ou argent&eacute;s)
                                                                </li>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt;">
                                                                    le mois et la date d&rsquo;expiration
                                                                </li>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt;">
                                                                    le cryptogramme visuel, compos&eacute; de 3
                                                                    chiffres, consultable au dos de votre carte
                                                                </li>
                                                            </ul>
                                                            <p><span style="color: blue;">&nbsp;<img
                                                                            src="img/form_paiement.png"></span></p>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Mon paiement par carte
                                                            bancaire n’est pas passé. Que dois-je faire?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p style="line-height: normal;">Cela arrive quelques fois !
                                                                Il peut s&rsquo;agir:</p>
                                                            <ul>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt; line-height: normal;">
                                                                    soit d&rsquo;une erreur technique qui s&rsquo;est
                                                                    produite lors de la validation de votre achat
                                                                </li>
                                                                <li style="margin-left: 36.0pt; text-indent: -18.0pt; line-height: normal;">
                                                                    soit d&rsquo;un refus de votre banque d&rsquo;effectuer
                                                                    le paiement car vous avez d&eacute;pass&eacute; le
                                                                    plafond mensuel autoris&eacute;.
                                                                </li>
                                                            </ul>
                                                            <p style="line-height: normal;">&nbsp;</p>
                                                            <p style="line-height: normal;">Voici les &eacute;tapes
                                                                &agrave; suivre:</p>
                                                            <ol style="margin-top: 0cm;">
                                                                <li style="line-height: normal;">Effectuer &agrave;
                                                                    nouveau le paiement par carte bancaire en
                                                                    renseignant &agrave; nouveau les coordonn&eacute;es
                                                                    de votre carte puis en validant
                                                                </li>
                                                                <li style="line-height: normal;">Si &ccedil;a ne
                                                                    fonctionne toujours pas vous avez 2 solutions:
                                                                </li>
                                                            </ol>
                                                            <ul>
                                                                <li style="margin-left: 72.0pt; text-indent: -18.0pt; line-height: normal;">
                                                                    Contacter votre banque afin qu&rsquo;il d&eacute;plafonne
                                                                    votre carte bancaire
                                                                </li>
                                                                <li style="margin-left: 72.0pt; text-indent: -18.0pt; line-height: normal;">
                                                                    Utiliser la carte bancaire d&rsquo;un proche ou d&rsquo;un
                                                                    ami et vous lui remettez un ch&egrave;que ou des esp&egrave;ces
                                                                    en &eacute;change
                                                                </li>
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                </li>

                                            </ul>
                                        </li>

                                        <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                <div style="float:left;width:85%">APRÈS MON STAGE</div>
                                            </a>
                                            <ul class="submenu">

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Mes points n’ont toujours pas
                                                            été crédités
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            Si vous venez de faire votre stage et que vous constatez que
                                                            vos 4 points n'apparaissent toujours pas sur le site
                                                            Télépoints, c’est tout à fait normal ! En effet, il faut
                                                            compter plus d’un mois pour que vos points soient visibles
                                                            sur votre solde, donc patience !
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> J’ai récupéré moins de 4
                                                            points
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            Participer à un stage permet de récupérer 4 points <strong>dans
                                                                la limite de votre plafond maximum</strong>. Ainsi, si
                                                            vous avez 9 points sur 12 avant le stage, vous ne pourrez
                                                            récupérer que 3 points. De même, si vous êtes en période
                                                            probatoire, votre plafond est bloqué à 6 la première année
                                                            (puis 8, puis 10, puis 12). Si vous faites un stage alors
                                                            que vous aviez 6 points sur 8, vous ne pourrez en récupérer
                                                            que 2.
                                                            <br><br>
                                                            Pour rappel, il n’est possible de récupérer des points
                                                            qu’une seule fois par an.
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> J’ai perdu mon attestation.
                                                            Comment faire ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            En tant que Plateforme de réservation, ProStagesPermis ne
                                                            détient aucun duplicata d'attestation de suivi de stage. Il
                                                            vous faut retrouver l’original que vous avez signé et qui
                                                            vous a été remis en mains propres à l'issue du stage.
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Je souhaite obtenir une
                                                            facture
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            Vous avez la possibilité d’éditer une facture depuis votre
                                                            compte, en cliquant sur le bouton <strong>“Ma
                                                                facture”</strong>. Attention, vous ne pouvez télécharger
                                                            ce document qu’une fois le stage terminé !
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Comment me faire rembourser
                                                            l’amende dans le cadre d’un stage obligatoire ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            Il vous suffit de télécharger le formulaire en cliquant sur
                                                            le bouton “Remboursement Lettre 48N” et de suivre les étapes
                                                            suivantes:
                                                            <br><br>
                                                            <strong>Etape 1: réunissez les documents suivant:</strong>
                                                            <ul>
                                                                <li>&nbsp;&nbsp; => Photocopie de la lettre 48N recto et
                                                                    verso
                                                                </li>
                                                                <li>&nbsp;&nbsp; => Photocopie de l’attestation de stage
                                                                    que les animateurs vous ont remis à la fin du stage
                                                                </li>
                                                                <li>&nbsp;&nbsp; => Photocopie de l’avis de
                                                                    contravention
                                                                </li>
                                                                <li>&nbsp;&nbsp; => La date et le mode de paiement de
                                                                    l’amende (chèque, virement, télé-paiement…)
                                                                </li>
                                                                <li>&nbsp;&nbsp; => Le justificatif du paiement de
                                                                    l’amende
                                                                </li>
                                                                <li>&nbsp;&nbsp; => un relevé d'identité bancaire
                                                                    libellé à votre nom
                                                                </li>
                                                                <li>&nbsp;&nbsp; => La demande de remboursement
                                                                    complétée et signée (voir pièce-jointe)
                                                                </li>
                                                                <li>&nbsp;</li>
                                                            </ul>

                                                            <strong>Etape 2: envoyez le tout par courrier:</strong><br>
                                                            Au centre des finances publiques qui est mentionné sur
                                                            l’avis réclamant le paiement de l’amende.

                                                        </li>
                                                    </ul>
                                                </li>

                                            </ul>
                                        </li>

                                        <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                <div style="float:left;width:85%">CONTACTER LE SERVICE CLIENT
                                                    PROSTAGESPERMIS
                                                </div>
                                            </a>
                                            <ul class="submenu">
                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Comment vous contacter ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p style="text-align: justify;">En toute logique, vous n&rsquo;avez
                                                                pas besoin de nous contacter. Votre Espace Stagiaire a
                                                                &eacute;t&eacute; con&ccedil;u pour vous permettre d&rsquo;effectuer
                                                                en toute autonomie toutes les d&eacute;marches n&eacute;cessaires.
                                                                Si vous avez des questions, vous trouverez toutes les r&eacute;ponses
                                                                dans la rubrique <strong>Aide.</strong></p>
                                                            <p style="text-align: justify;"><strong>&nbsp;</strong></p>
                                                            <p style="text-align: justify;">De nore c&ocirc;t&eacute;,
                                                                toute notre &eacute;quipe travaille dur pour que le
                                                                stage se d&eacute;roule dans les meilleures conditions
                                                                pour vous et pour que votre dossier soit envoy&eacute;
                                                                en Pr&eacute;fecture le plus rapidement possible pour sa
                                                                validation !</p>
                                                            <p style="text-align: justify;">&nbsp;</p>
                                                            <p>
                                                                <span style="font-size: 11.0pt; line-height: 115%; font-family: 'Arial',sans-serif;">Si malgr&eacute; cela vous souhaitez tout de m&ecirc;me nous contacter, alors envoyez-nous un message &agrave; partir de la rubrique <u><span
                                                                                style="color: #3c78d8;">&ldquo;Contact&rdquo;</span></u>. Notre &eacute;quipe vous r&eacute;pond sous 48h maximum. D&egrave;s que votre demande aura &eacute;t&eacute; trait&eacute;e, vous recevrez un email vous informant qu&rsquo;un message vous attend dans votre Espace Stagiaire. </span>
                                                            </p>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="#" style="display: flex;
    align-items: center;
    flex-direction: row-reverse;
    height: 40px;
    justify-content: space-between;">
                                                        <div style="float:left;width:85%"> Vous n’avez pas de numéro de
                                                            téléphone pour vous contacter. Pourquoi ?
                                                        </div>
                                                    </a>
                                                    <ul class="submenu">
                                                        <li>
                                                            <p style="text-align: justify;">Nous avons fait le choix de
                                                                ne pas avoir de conseillers qui r&eacute;pondent par t&eacute;l&eacute;phone.
                                                                Nous n&rsquo;avons donc pas de num&eacute;ro auquel vous
                                                                pouvez nous joindre.</p>
                                                            <p style="text-align: justify;">&nbsp;</p>
                                                            <p style="text-align: justify;">Pourquoi? Pour vous proposer
                                                                les prix les plus bas de France. Gr&acirc;ce &agrave;
                                                                Internet, tout peut &ecirc;tre r&eacute;alis&eacute;
                                                                &agrave; distance et &ccedil;a nous permet de faire des
                                                                &eacute;conomies dont on vous fait directement b&eacute;n&eacute;ficier
                                                                sur le prix du stage. Et tout &ccedil;a avec une qualit&eacute;
                                                                de service encore plus grande. Tout le monde est gagnant
                                                                !</p>
                                                            <p style="text-align: justify;">&nbsp;</p>
                                                            <p style="text-align: justify;">Avec les progr&egrave;s d&rsquo;Internet
                                                                et de l&rsquo;informatique, de plus en plus de soci&eacute;t&eacute;
                                                                &agrave; travers le monde fonctionnent sur ce mod&egrave;le.
                                                                Par exemple, les service bancaires qui sont de plus en
                                                                plus d&eacute;mat&eacute;rialis&eacute;s. &ccedil;a r&eacute;duit
                                                                le co&ucirc;t pour les entreprises et le prix pour les
                                                                clients. Et nous on aime &ccedil;a !</p>
                                                        </li>
                                                    </ul>
                                                </li>

                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                            </nav>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Mainly scripts -->
<script src="js/jquery-2.1.1.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

<!-- Flot -->
<script src="js/plugins/flot/jquery.flot.js"></script>
<script src="js/plugins/flot/jquery.flot.tooltip.min.js"></script>
<script src="js/plugins/flot/jquery.flot.spline.js"></script>
<script src="js/plugins/flot/jquery.flot.resize.js"></script>
<script src="js/plugins/flot/jquery.flot.pie.js"></script>
<script src="js/plugins/flot/jquery.flot.symbol.js"></script>
<script src="js/plugins/flot/jquery.flot.time.js"></script>

<!-- Custom and plugin javascript -->
<script src="js/inspinia.js"></script>
<script src="js/plugins/pace/pace.min.js"></script>

<!-- Sparkline -->
<script src="js/plugins/sparkline/jquery.sparkline.min.js"></script>

<!-- FooTable -->
<script src="js/plugins/footable/footable.all.min.js"></script>

<!-- sweet alert 2 -->
<script src="https://cdn.jsdelivr.net/es6-promise/latest/es6-promise.auto.min.js"></script> <!-- IE support -->
<script src="./dist/sweetalert2.js"></script>

<script src="js/jquery.form.js"></script> <!-- necessaire pour chargement documents -->

<script src="js/loadingoverlay.min.js"></script>

<!-- FooTable -->
<link href="css/plugins/footable/footable.core.css" rel="stylesheet">

<!-- sweet alert 2 -->
<link rel="stylesheet" href="./dist/sweetalert2.min.css">

<link href="bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet">
<script src="bootstrap3-editable/js/bootstrap-editable.js"></script>
<script src="bootstrap3-editable/js/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.0/locale/fr.js"></script>

</body>
</html>