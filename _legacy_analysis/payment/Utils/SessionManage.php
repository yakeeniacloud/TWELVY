<?php

class SessionManage
{

    public static function retrieveSessionData($session_id)
    {
        include("/home/prostage/connections/stageconnect.php");
        mysql_select_db($database_stageconnect, $stageconnect);
        $sql = "SELECT * FROM sessions WHERE session_id='$session_id'";
        $rs = mysql_query($sql, $stageconnect) or die(mysql_error());
        $row = mysql_fetch_assoc($rs);
        mysql_close($stageconnect);
        return $row;
    }

    public static function saveSessionData($data, $session_id)
    {
        $content = '';
        foreach ($data as $k => $v) {
            $content .= $k . '#' . $v . '*';
        }
        include("/home/prostage/connections/stageconnect.php");
        mysql_select_db($database_stageconnect, $stageconnect);

        $sqlDelete = "DELETE FROM sessions WHERE session_id = '$session_id'";
        mysql_query($sqlDelete, $stageconnect) or die(mysql_error());

        $sql = "INSERT INTO sessions (session_id, content) VALUES (\"$session_id\", \"$content\")";
        mysql_query($sql, $stageconnect) or die(mysql_error());
        mysql_close($stageconnect);
    }

    public static function parseSessionData($data)
    {
        $parseData = [];
        $dataKeyValue = explode('*', $data['content']);
        foreach ($dataKeyValue as $value) {
            $arrValueKey = explode('#', $value);
            if (count($arrValueKey) > 1) {
                $parseData[$arrValueKey[0]] = $arrValueKey[1];
            }
        }
        return $parseData;
    }

    public static function initPaymentData(array $arrData)
    {
        $studentId = $arrData['studentId'];
        $stageId = $arrData['stageId'];
        $email = $arrData['email'];
        $orderId = $arrData['orderId'];
        $cardNumber = $arrData['cardNumber'];
        $cardExpiry = $arrData['cardExpiry'];
        $cardCVC = $arrData['cardCVC'];
        $memberId = $arrData['memberId'];
        $isOrderBump = $arrData['isOrderBump'];
        $funnelId = $arrData['funnelId'];
        return [
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
        ];
    }

    public static function initPaymentUpsellData(array $arrData)
    {
        $studentId = $arrData['studentId'];
        $upsellId = $arrData['upsellId'];
        $email = $arrData['email'];
        $orderId = $arrData['orderId'];
        $reference = $arrData['reference'];
        $cardNumber = $arrData['cardNumber'];
        $cardExpiry = $arrData['cardExpiry'];
        $cardCVC = $arrData['cardCVC'];
        $funnelId = $arrData['funnelId'];

        return [
            $studentId,
            $upsellId,
            $orderId,
            $reference,
            $email,
            $cardNumber,
            $cardExpiry,
            $cardCVC,
            $funnelId
        ];
    }

    public static function initPaymentOneProductUpsellData(array $arrData)
    {
        $studentId = $arrData['studentId'];
        $upsellId = $arrData['upsellId'];
        $email = $arrData['email'];
        $orderId = $arrData['orderId'];
        $reference = $arrData['reference'];
        $cardNumber = $arrData['cardNumber'];
        $cardExpiry = $arrData['cardExpiry'];
        $cardCVC = $arrData['cardCVC'];
        $isOrderBump = $arrData['isOrderBump'];
        $funnelId = $arrData['funnelId'];

        return [
            $studentId,
            $upsellId,
            $orderId,
            $reference,
            $email,
            $cardNumber,
            $cardExpiry,
            $cardCVC,
            $isOrderBump,
            $funnelId
        ];
    }
}