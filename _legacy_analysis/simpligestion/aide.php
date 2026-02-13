<?php
    
    require_once('../../common_psycho_bootstrap/config.php');

    $type_page = TYPE_PAGE_AIDE;

?><!DOCTYPE html>
<html lang="fr">
<head>
    <title>Centre d'aide Espace Centre</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

<?php include("includes/header.php"); ?>

    <style type="text/css">
        .popup-row-separator {
            margin-top: 15px;
        }
        h2:first-child {
            margin-top: 0px;
        }
        .colmiddle table tr td {
            padding:10px;
        }

        @media (min-width:768px) {
            .contentwhite .colmiddle {
                padding-left: 2%;
            }
        }
    </style>
</head>

<body class="contentspage">
    
    <?php include("includes/topbar.php"); ?>


     <div id="content">

         <div class="contentwhite">
             <div class="container">


                 <div class="col-md-3 col-sm-3 col-xs-12 colleft">

                     <?php include("includes/sidebar_aide.php"); ?>

                 </div>

                 <div class="col-md-9 col-sm-9 col-xs-12 colmiddle popup-rows-border-left">

                     <h2>
                         Centre d'aide Espace Centre
                     </h2>
                     <p>Bienvenue dans votre espace,</p>
                     <p>Votre Espace Centre a &eacute;t&eacute; con&ccedil;u pour &ecirc;tre intuitif et simple d'utilisation. Vous y trouverez un ensemble de fonctionnalit&eacute;s vous permettant de diffuser vos sessions, g&eacute;rer efficacement vos stagiaires et communiquer &eacute;troitement avec eux.</p>
                     <p>Voici un tutoriel vous permettant d'effectuer vos premiers pas et vous guidant pour mieux utiliser l'ensemble des fonctionnalit&eacute;s mises &agrave; votre disposition qui vous permettront d&rsquo;augmenter le taux de remplissage de vos sessions. Si vous avez besoin d'aide n'h&eacute;sitez pas &agrave; nous contacter.</p>

                 </div>

             </div>
         </div><!-- End contentwhite -->

     </div> <!-- End content General -->

    <?php include("includes/footer.php"); ?>

</body>
</html>
