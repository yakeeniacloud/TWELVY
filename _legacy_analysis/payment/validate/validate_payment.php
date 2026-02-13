<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name="generator" content="PSPad editor, www.pspad.com">
    <title></title>
    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-NGT8W4');
    </script>
    <!-- End Google Tag Manager -->
</head>

<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NGT8W4"
            height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->
    <div style="text-align:center"> <br><br>
        <img src='https://www.prostagespermis.fr/assets/img/loading.gif' />
        <br><br>
        <b>Veuillez patienter...</b>
    </div>
    <?php
    require_once("/home/prostage/connections/config.php");
    require_once "/home/prostage/www/params.php";
    require_once ROOT . "/debug.php";
    require_once APP . "logging/Logging.php";
    require_once APP . "logging/LogCommission.php";
    require_once APP . "logging/LogPayment.php";
    require_once APP . "payment/E_Transaction/E_TransactionPayment.php";
    require_once APP . "payment/E_Transaction/E_TransactionError.php";
    require_once APP . "payment/Utils/SessionManage.php";
    require_once APP . 'order/services/RetrieveFullOrderByStageStudent.php';
    require_once APP . 'order/services/GenerateReferenceOrder.php';
    require_once APP . 'payment/services/email/SendTicketPaymentEmail.php';
    require_once APP . 'payment/services/email/SendPaymentSuccessEmail.php';
    require_once APP . 'payment/services/UpdateStagePaymentData.php';
    require_once APP . 'stage/services/IsStagePlaceAvailableForSale.php';
    require_once APP . 'upsell/services/RetrieveNextUpSellToPay.php';
    require_once APP . 'upsell/services/RetrieveOrderBumpUpsell.php';
    require_once APP . 'funnel/services/RetrieveFunnelByType.php';
    require_once APP . 'upsell/services/RetrieveUpsellById.php';
    require_once APP . 'student/services/RetrieveStudentById.php';
    require_once APP . 'Api/Sms/sendSMS.php';
    require_once ROOT . '/mails_v3/mail_echec_paiement.php';
    require_once APP . 'payment/emails/SendAdminTransactionSuccessInError.php';
    require_once APP . 'student/services/UpdateOneFieldStudent.php';
    require_once APP . 'payment/repositories/TrackingPathUserRepository.php';
    require_once APP . 'payment/repositories/TrackingUserPaymentErrorCode.php';

    $data = [];

    foreach ($_GET as $key => $value) {
        $param = explode('_', $key);
        if ($param[0] == "d2305") {
            session_id($param[1]);
            session_start();
            $data = SessionManage::retrieveSessionData($param[1]);
        }
    }

    $eTransaction = new E_TransactionPayment();
    $arrData = SessionManage::parseSessionData($data);
    $page_redirection = '';

    list(
        $studentId,
        $stageId,
        $orderId,
        $memberId,
        $email,
        $cardNumber,
        $cardExpiry,
        $cardCVC,
        $isOrderBump,
        $funnelId
    ) = SessionManage::initPaymentData($arrData);

    $funnel = (new RetrieveFunnelByType())->__invoke('stage', $mysqli);
    $upsellIdToPay = (new RetrieveNextUpSellToPay())->__invoke($studentId, $mysqli, $funnel->id);

    if (!empty($_POST) && !empty($data)) {
        if (
            (isset($_POST['ID3D']) && $_POST['ID3D'] != '')
        ) {

            $ID3D = $_POST['ID3D'];

            $_SESSION["paiement_error"] = '';
            $_SESSION["cardNumber"] = $cardNumber;
            $_SESSION["cardCVC"] = $cardCVC;
            $_SESSION["cardExpiry"] = $cardExpiry;
            $_SESSION['id_stagiaire'] = $studentId;
            $_SESSION['i_stagiaireId'] = $studentId;
            $_SESSION['i_stageId'] = $stageId;
            $_SESSION['i_membreId'] = $memberId;
            $_SESSION['memberId'] = $memberId;
            $_SESSION['stageId'] = $stageId;
            $_SESSION['studentId'] = $studentId;
            $_SESSION['funnelId'] = $funnelId;

            $logPayment = new LogPayment();
            $logCommission = new LogCommission();

            $isPlaceAvailableForSale = (new IsStagePlaceAvailableForSale())->__invoke($stageId, $mysqli);
            $stage = (new RetrieveFullOrderByStageStudent())->__invoke($studentId, $stageId, $mysqli);
            $amount = $stage->paiement;

            $arrReference = (new GenerateReferenceOrder())->__invoke($studentId, $mysqli);
            $isAlreadyBuyStage = false;

            if (
                $stage->status == 'inscrit' &&
                $stage->supprime == 0 &&
                $stage->numappel != '' &&
                $stage->numtrans != ''
            ) {
                $isAlreadyBuyStage = true;
            }

            if (
                empty($arrReference) ||
                !$isPlaceAvailableForSale ||
                $isAlreadyBuyStage
            ) {
                $_SESSION["paiement_error"] = $isAlreadyBuyStage ? "<div style='color:red;'>Vous êtes déjà inscrit à un stage.</div>" : "<div style='color:red;'>Nous n'avons plus de place de disponible sur ce stage, veuillez en chosir un autre.</div>";
                $page_redirection = '/page_recap.php?id=' . $studentId . '&s=' . $stageId . '&m=' . $memberId;
                header('Content-type: text/html; charset=utf-8'); // make sure this is set
                header('Location:' . $page_redirection);
                flush();
                ob_flush();
                die();
            }

            $reference = $arrReference['reference'];
            $numSuivi = $arrReference['num_suivi'];
            $emails_de_tests = array("ismaelkhapeo@gmail.com", "adk.malki@gmail.com");
            /*if(in_array($email, $emails_de_tests)){
                $codereponse = "00000";
                $numTransInt = "11111";
                $numAppelInt = "11111";
                $numTrans = "11111";
                $numAppel = "11111";
                $autorisation = "11111";
            }else{*/
            $arrResponse = $eTransaction->validateTransaction(
                $amount,
                $reference,
                $cardNumber,
                $cardExpiry,
                $cardCVC,
                $ID3D
            );

            $response = $arrResponse['response'];

            $codereponse = intval($eTransaction->decodeResponse($response, 'codereponse'));
            $numTrans = $eTransaction->decodeResponse($response, 'numtrans');
            $numAppel = $eTransaction->decodeResponse($response, 'numappel');
            $autorisation = $eTransaction->decodeResponse($response, 'autorisation');

            list($commentaire, $msg) = $eTransaction->parseETransactionComment($response);

            $numTransInt = intval($numTrans);
            $numAppelInt = intval($numAppel);
            //}
            $isInError = false;

            $codereponseFlat = $eTransaction->decodeResponse($response, 'codereponse');

            if ($codereponse != "00000" || ($numTransInt == 0 && $numAppelInt == 0)) {

                $Error_Etransaction = new E_TransactionError();
                $errorMsg = $Error_Etransaction->getFullTextErrorCodes($codereponseFlat);

                $_SESSION["paiement_error"] = "<div>" . $errorMsg . "</div>";

                $logPayment->errorPaymentMessage(
                    $commentaire,
                    $msg,
                    " - STAGE - Reference = " . $reference,
                    $studentId,
                    $amount,
                    $email,
                    $cardNumber
                );

                $page_redirection = '/page_recap.php?id=' . $studentId . '&s=' . $stageId . '&m=' . $memberId;
                $isInError = true;

                $data['redirection'] = 'https://www.prostagespermis.fr' . $page_redirection . '&order=2';
                $data['email'] = $email;
                $data['id_stage'] = $stageId;
                $data['id_stagiaire'] = $studentId;
                $data['errorMsg'] = $errorMsg;
                mail_echec_paiement($data);

                $updateOneFieldStudent = new UpdateOneFieldStudent();
                $updateOneFieldStudent->__invoke($studentId, 'up2pay_code_error', $codereponseFlat, $mysqli);

                (new TrackingPathUserRepository($mysqli))->addTracking('process_payment_return_error', 'id_stagiaire', $studentId);
                (new TrackingUserPaymentErrorCode($mysqli))->addTrackingError($studentId, $codereponseFlat);
            }

            if (!$isInError && $codereponse == "00000") {
                $studentOBJ = (new RetrieveStudentById())->__invoke($studentId, $mysqli);
                //if (!DEBUG && strlen($studentOBJ->tel) == 10 && $studentOBJ->is_sms_confirmation_send == 0){
                if (strlen($studentOBJ->tel) == 10 && $studentOBJ->is_sms_confirmation_send == 0) {
                    $arrPhones[] = $studentOBJ->tel;
                    $key = md5($studentId . '!psp13#');
                    $key = substr($key, 0, 5);
                    $url_join = HOST . "/es/login.php?id=$studentId&k=$key";
                    $resp = (new sendSMS())->sendSMS(
                        "Merci de votre inscription au stage! Retrouvez les détails par mail (VERIFIEZ VOS SPAMS) Complétez rapidement votre dossier sur votre Espace Stagiaire en cliquant sur le lien suivant %RICHURL________%",
                        $arrPhones,
                        $url_join,
                        null,
                        0,
                        '',
                        $studentId
                    );
                    if ($resp) {
                        include("/home/prostage/connections/stageconnect.php");
                        mysql_select_db($database_stageconnect, $stageconnect);
                        $sql = 'UPDATE stagiaire SET is_sms_confirmation_send=1 WHERE id=' . $studentId;
                        $rs = mysql_query($sql, $stageconnect);
                        mysql_close($stageconnect);
                    }
                }

                $autorisation = $eTransaction->decodeResponse($response, 'autorisation');

                SessionManage::saveSessionData($arrData, session_id());

                $logPayment->successPaymentMessage(
                    $commentaire,
                    $msg,
                    " - STAGE - Reference = " . $reference,
                    $studentId,
                    $amount,
                    $email
                );

                /*$logfileSuspicion = "/home/prostage/www/logs/test_suspicion.txt";
                $tmpfileSuspicion = file_get_contents($logfileSuspicion);
                $msgSuspision = date("d-m-Y H:i:s")
                    . $reference
                    . " - Stagiaire=" . $studentId
                    . " - Commentaire="
                    . $commentaire . " - Message= " . $msg;
                file_put_contents($logfileSuspicion, "\n" . $msgSuspision . "\n" . $tmpfileSuspicion);*/

                $Error_Etransaction = new E_TransactionError();
                if ($Error_Etransaction->transactionSuccessInError($msg, $commentaire)) {
                    (new SendAdminTransactionSuccessInError())->execute($studentId, $mysqli);
                }

                $logCommission->loggingCommission($studentId, $stageId, $stage->partenariat, $stage->stage_commission);

                (new SendTicketPaymentEmail())->__invoke(
                    $reference,
                    $autorisation,
                    $amount,
                    $email
                );

                // TODO update webservice RPPC after complete information,

                (new UpdateStagePaymentData())->__invoke(
                    $stageId,
                    $studentId,
                    $stage->member_id,
                    $orderId,
                    $autorisation,
                    $cardNumber,
                    $numAppel,
                    $numTrans,
                    $stage->partenariat,
                    $stage->stage_commission,
                    $reference,
                    $numSuivi,
                    $mysqli,
                    $stage->marge_commerciale_centre,
                    $stage->taux_marge_commerciale_centre
                );

                $updateOneFieldStudent = new UpdateOneFieldStudent();
                $updateOneFieldStudent->__invoke($studentId, 'up2pay_code_error', NULL, $mysqli);

                (new TrackingPathUserRepository($mysqli))->addTracking('process_payment_return_success', 'id_stagiaire', $studentId);

                if (in_array($email, $emails_de_tests)) {
                    require_once ROOT . '/mails_v3/mail_inscription.php';
                    mail_inscription($studentId);
                } else (new SendPaymentSuccessEmail())->__invoke($studentId, $stage->member_id);

                if ($isOrderBump == 1) {
                    $upsellOrderBump = (new RetrieveUpsellById())->__invoke($funnel->order_bump_id, $mysqli);
                    $page_redirection = '/page_order_dump.php?upsell=' . $upsellOrderBump->id . '&s=' . session_id();
                } else {
                    if ($upsellIdToPay) {
                        $page_redirection = '/page_upsell.php?upsell=' . $upsellIdToPay . '&s=' . session_id();
                    } else {
                        $page_redirection = '/order_confirmation.php?&s=' . session_id();
                    }
                }

                if (!DEBUG && !in_array($email, $emails_de_tests)) {
                    switch ($stage->member_id) {
                        case '1060':
                            include("/home/prostage/www/ws/prod/fsp/to/inscription/add.php");
                            addInscription($stage->member_id, $studentId);
                            break;
                    }
                }
            }
        }
    } else {
        $_SESSION["paiement_error"] = "<div style='color:red;'>Votre paiement n'a pas pu être traité</div>";
    }


    $mysqli->close();
    echo ("<script>location.href = '" . $page_redirection . "';</script>");
    /*header('Content-type: text/html; charset=utf-8'); // make sure this is set
    header('Location:' . $page_redirection);
    flush();
    ob_flush();
    die();*/
    ?>
</body>

</html>