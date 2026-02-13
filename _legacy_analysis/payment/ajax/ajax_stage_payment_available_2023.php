<?php
header('Access-Control-Allow-Origin: https://www.reservermonstagepermis.fr');
session_id($_GET['s']);
session_start();
require_once("/home/prostage/connections/config.php");
require_once("/home/prostage/www/params.php");
require_once "/home/prostage/www/debug.php";

require_once APP . 'order/services/RetrieveFullOrderByStageStudent.php';
require_once APP . 'order/services/CheckIfStageAlreadyPay.php';
require_once APP . 'stage/services/IsStagePlaceAvailableForSale.php';
require_once APP . 'payment/Utils/SessionManage.php';
require_once APP . 'upsell/services/RetrieveNextUpSellToPay.php';
require_once APP . 'funnel/services/RetrieveFunnelByType.php';

unset($_SESSION['upsell_id']);

$cardNumber = $_POST['cardNumber'];
$cardExpiry = $_POST['cardExpiry'];
$cardCVC = $_POST['cardCVC'];
$studentId = $_POST['studentId'];
$stageId = $_POST['stageId'];
$isOrderBump = $_POST['isOrderBump'];

$_SESSION['cardNumber'] = $cardNumber;
$_SESSION['cardExpiry'] = $cardExpiry;
$_SESSION['cardCVC'] = $cardCVC;
$_SESSION["dossier_inscription"] = 0;
$_SESSION['studentId'] = $studentId;
$_SESSION['id_stagiaire'] = $studentId;
$_SESSION['i_stagiaireId'] = $studentId;
$_SESSION['i_stageId'] = $stageId;
$_SESSION['stageId'] = $stageId;
$_SESSION['isOrderBump'] = $isOrderBump;

$stage = (new RetrieveFullOrderByStageStudent())->__invoke($studentId, $stageId, $mysqli);
$funnel = (new RetrieveFunnelByType())->__invoke('stage', $mysqli);

$_SESSION['funnelId'] = $funnel->id;

$isStageAvailable = (new IsStagePlaceAvailableForSale())->__invoke($stageId, $mysqli);
$isAlreadyPay = (new CheckIfStageAlreadyPay())->__invoke($studentId, $stageId, $mysqli);

if (!$isStageAvailable) {
    $mysqli->close();
    echo json_encode(['isAvailable' => false]);
    exit;
}

if ($isAlreadyPay) {
    $upsellIdToPay = (new RetrieveNextUpSellToPay())->__invoke($studentId, $mysqli, $funnel->id);
    //$redirectionPage = $upsellIdToPay ? '/page_upsell.php?upsell=' . $upsellIdToPay . '&s=' . session_id() : '/order_confirmation.php?&s=' . session_id();
    $redirectionPage = '/confirmation.php?&s=' . session_id();
    $mysqli->close();
    echo json_encode(['isAvailable' => true, 'isAlreadyPay' => $isAlreadyPay, 'redirectToUpsell' => $redirectionPage]);
    exit;
}

if (!DEBUG) {
    $logfile = "/home/prostage/www/logs/paiements_demandes.txt";
    $tmpfile = file_get_contents($logfile);
    $msg = date("d-m-Y H:i:s") . " - STAGE - Reference = " . $stage->reference . " - Stagiaire=" . $studentId . " - Montant=" . $stage->paiement . " - Email=" . $stage->email . " - Porteur=" . $cardNumber;
    file_put_contents($logfile, "\n" . $msg . "\n" . $tmpfile);
}

$_SESSION['i_membreId'] = $stage->member_id;
$_SESSION['memberId'] = $stage->member_id;

SessionManage::saveSessionData(
    [
        'studentId' => $studentId,
        'stageId' => $stageId,
        'email' => $stage->email,
        'orderId' => $stage->order_id,
        'reference' => $stage->reference_order,
        'cardNumber' => $cardNumber,
        'memberId' => $stage->member_id,
        'cardExpiry' => $cardExpiry,
        'cardCVC' => $cardCVC,
        'isOrderBump' => $isOrderBump,
        'funnelId' => $funnel->id
    ],
    session_id()
);

$mysqli->close();

echo json_encode(['isAvailable' => true, 'isAlreadyPay' => false]);
exit;


?>
