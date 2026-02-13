<?php

require_once APP . 'logging/Logging.php';

class E_TransactionPayment
{
    private $PBX_KEY;
    private $PBX_RAND;
    private $PBX_URL;
    private $MerchandId;
    private $PBX_SITE;
    private $logDebug;
    const SUCCESS_CODE = '00000';

    public function __construct()
    {
        $this->logDebug   =  new Logging();
        if (DEBUG == true) {
            $this->PBX_RAND = '63';
            $this->MerchandId = '222';
            $this->PBX_KEY = '0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF';
            $this->PBX_URL = 'https://recette-ppps.e-transactions.fr/PPPS.php';
            $this->PBX_SITE = '1999887';
        } else {
            $this->MerchandId = '651027368';
            $this->PBX_RAND = '02';
            $this->PBX_KEY = '78f9db5d0b421f5f5b7e0eda11f3a66c84b2fdadfcad8cf8c8df25b87a0a4988775f3ff7a81b5a9b653854c10bc742889f612e7741363e585b758fc4e2e86e0d';
            $this->PBX_URL = 'https://ppps.e-transactions.fr/PPPS.php';
            $this->PBX_SITE = '0966892';
        }
        $this->MerchandId = '651027368';
        $this->PBX_RAND = '02';
        $this->PBX_KEY = '78f9db5d0b421f5f5b7e0eda11f3a66c84b2fdadfcad8cf8c8df25b87a0a4988775f3ff7a81b5a9b653854c10bc742889f612e7741363e585b758fc4e2e86e0d';
        $this->PBX_URL = 'https://ppps.e-transactions.fr/PPPS.php';
        $this->PBX_SITE = '0966892';
        /*
        $this->PBX_RAND = '63';
        $this->MerchandId = '222';
        $this->PBX_KEY = '0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF';
        $this->PBX_URL = 'https://recette-ppps.e-transactions.fr/PPPS.php';
        $this->PBX_SITE = '1999887';*/
    }

    public function validateTransaction(
        $amount,
        $reference,
        $cardNumber,
        $cardDate,
        $cardCVV,
        $ID3D
    )
    {        
        $amount = $this->convertAmount($amount);
        $response = $this->authorizationDebit($amount, $reference, $cardNumber, $cardDate, $cardCVV, $ID3D);
        $codereponse = $this->decodeResponse($response, 'codereponse');

        if ($codereponse !== self::SUCCESS_CODE) {
            return ['response' => $response, 'etransactionToken' => ''];
        }

        $numTrans = $this->decodeResponse($response, 'numtrans');
        $numAppel = $this->decodeResponse($response, 'numappel');

        return [
            'response' => $this->validateDirectDebit($amount, $reference, $numAppel, $numTrans, $ID3D),
        ];
    }

    public function validateTransactionAbonnement(
        $amount,
        $reference,
        $studentId,
        $cardNumber,
        $cardDate,
        $cardCVV,
        $ID3D
    )
    {
        $amount = $this->convertAmount($amount);
        $response = $this->createAbonnee($amount, $reference, $studentId, $cardNumber, $cardDate, $cardCVV, $ID3D);
        $codereponse = $this->decodeResponse($response, 'codereponse');

        if ($codereponse !== self::SUCCESS_CODE) {
            return ['response' => $response, 'etransactionToken' => ''];
        }

        $numTrans = $this->decodeResponse($response, 'numtrans');
        $numAppel = $this->decodeResponse($response, 'numappel');
        $token = $this->decodeResponse($response, 'porteur');
        $referenceAbonne = $this->decodeResponse($response, 'refabonne');

        return [
            'numTrans' => $numTrans,
            'numAppel' => $numAppel,
            'token' => $token,
            'referenceAbonne' => $referenceAbonne
        ];
    }

    public function decodeResponse($query, $variable)
    {
        $value = '';
        foreach (explode('&', $query) as $chunk) {
            $param = explode("=", $chunk);
            if (!$param) {
                break;
            }
            if (stripos($param[0], $variable) !== false) {
                if ($variable == 'PORTEUR') {
                    $value = $param[1];
                } else {
                    $value = urldecode($param[1]);
                }
                break;
            }
        }
        return $value;
    }

    public function parseETransactionComment($response)
    {
        $commentaire = '';
        $msg = '';
        $arrResponseParams = explode('&', $response);
        foreach ($arrResponseParams as $chunk) {
            if ($msg == '')
                $msg = $chunk;
            else
                $msg .= " - " . $chunk;
            $param = explode("=", $chunk);
            if ($param[0] == "COMMENTAIRE")
                $commentaire = $param[1];
        }
        return array($commentaire, $msg);
    }

    private function autorisationDebitAndCreateCustomer($amount, $reference, $cardNumber, $cardDate, $cardCVV, $ID3D, $stagiaireId)
    {
        $numQuestion = time() + 1;
        $dateq = date('dmYHis');
        $hmac = $this->buildHMACEtransaction($amount, $reference, $cardNumber, $cardDate, $cardCVV, $ID3D, $numQuestion, $dateq, $stagiaireId);
        $curl = $this->initCurlETransaction();
        $data = $this->buildPostAutorisationData($amount, $reference, $cardNumber, $cardDate, $cardCVV, $ID3D, $numQuestion, $dateq, $stagiaireId, $hmac);
        $trame = http_build_query($data, '', '&');

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $trame);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    // TODO Refactor
    private function authorizationDebit($amount, $reference, $cardNumber, $cardDate, $cardCVV, $ID3D)
    {
        $numQuestion = time() + 1;
        $dateq = date('dmYHis');

        $msg = "VERSION=00104" .
            "&TYPE=00001" .
            "&SITE=" . $this->PBX_SITE .
            "&RANG=" . $this->PBX_RAND .
            "&IDENTIFIANT=" . $this->MerchandId .
            "&NUMQUESTION=" . $numQuestion .
            "&MONTANT=" . $amount .
            "&DEVISE=978" .
            "&REFERENCE=" . $reference .
            "&PORTEUR=" . $cardNumber .
            "&HASH=SHA512" .
            "&DATEVAL=" . $cardDate .
            "&CVV=" . $cardCVV .
            "&DATEQ=" . $dateq .
            "&ID3D=" . $ID3D;

        $binKey = pack("H*", $this->PBX_KEY);
        $hmac = strtoupper(hash_hmac('sha512', $msg, $binKey));
        $curl = curl_init($this->PBX_URL);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);


        $postfields = array(
            'VERSION' => '00104',
            'TYPE' => '00001',
            'SITE' => $this->PBX_SITE,
            'RANG' => $this->PBX_RAND,
            'IDENTIFIANT' => $this->MerchandId,
            'NUMQUESTION' => $numQuestion,
            'MONTANT' => $amount,
            'DEVISE' => '978',
            'REFERENCE' => $reference,
            'PORTEUR' => $cardNumber,
            'HASH' => 'SHA512',
            'DATEVAL' => $cardDate,
            'CVV' => $cardCVV,
            'DATEQ' => $dateq,
            'ID3D' => $ID3D,
            'HMAC' => $hmac
        );

        $trame = http_build_query($postfields, '', '&');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $trame);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    private function buildHMACEtransaction($amount, $reference, $cardNumber, $cardDate, $cardCVV, $ID3D, $numQuestion, $dateq, $stagiaireId)
    {
        $binKey = pack("H*", $this->PBX_KEY);
        $msg = $this->buildMessageHmac($numQuestion, $amount, $reference, $cardNumber, $cardDate, $cardCVV, $dateq, $stagiaireId, $ID3D);
        return strtoupper(hash_hmac('sha512', $msg, $binKey));
    }

    private function buildPostAutorisationData($amount, $reference, $cardNumber, $cardDate, $cardCVV, $ID3D, $numQuestion, $dateq, $stagiaireId, $hmac)
    {
        return [
            'VERSION' => '00104',
            'TYPE' => '00001',
            'SITE' => $this->PBX_SITE,
            'RANG' => $this->PBX_RAND,
            'IDENTIFIANT' => $this->MerchandId,
            'NUMQUESTION' => $numQuestion,
            'MONTANT' => $amount,
            'DEVISE' => '978',
            'REFERENCE' => $reference,
            'PORTEUR' => $cardNumber,
            'HASH' => 'SHA512',
            'DATEVAL' => $cardDate,
            'CVV' => $cardCVV,
            'DATEQ' => $dateq,
            "REFABONNE" => $stagiaireId,
            'ID3D' => $ID3D,
            'HMAC' => $hmac
        ];
    }

    private function initCurlETransaction()
    {
        $curl = curl_init($this->PBX_URL);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);
        return $curl;
    }

    private function validateDebit($amount, $reference, $cardDate, $stagiaireId, $etransactionToken)
    {
        $numQuestion = time() + 1;
        $dateq = date('dmYHis');

        $hmac = $this->buildHmacDebitEtransaction($amount, $reference, $cardDate, $stagiaireId, $etransactionToken, $numQuestion, $dateq);
        $curl = $this->initCurlETransaction();

        $trame = $this->buildTramDataPostToDebitTransaction($amount, $reference, $cardDate, $stagiaireId, $etransactionToken, $numQuestion, $dateq, $hmac);

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $trame);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    // TODO Refactor
    private function validateDirectDebit($amount, $reference, $numAppel, $numTrans, $ID3D)
    {
        $numQuestion = time() + 1;
        $dateq = date('dmYHis');

        $msg = "VERSION=00104" .
            "&TYPE=00002" .
            "&SITE=" . $this->PBX_SITE .
            "&RANG=" . $this->PBX_RAND .
            "&IDENTIFIANT=" . $this->MerchandId .
            "&NUMQUESTION=" . $numQuestion .
            "&MONTANT=" . $amount .
            "&DEVISE=978" .
            "&REFERENCE=" . $reference .
            "&NUMAPPEL=" . $numAppel .
            "&NUMTRANS=" . $numTrans .
            "&DATEQ=" . $dateq .
            "&HASH=SHA512" .
            "&ID3D=" . $ID3D;

        $binKey = pack("H*", $this->PBX_KEY);
        $hmac = strtoupper(hash_hmac('sha512', $msg, $binKey));

        $curl = curl_init($this->PBX_URL);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);

        $postfields = array(
            'VERSION' => '00104',
            'TYPE' => '00002',
            'SITE' => $this->PBX_SITE,
            'RANG' => $this->PBX_RAND,
            'IDENTIFIANT' => $this->MerchandId,
            'NUMQUESTION' => $numQuestion,
            'MONTANT' => $amount,
            'DEVISE' => '978',
            'REFERENCE' => $reference,
            'NUMAPPEL' => $numAppel,
            'NUMTRANS' => $numTrans,
            'DATEQ' => $dateq,
            'HASH' => 'SHA512',
            'ID3D' => $ID3D,
            'HMAC' => $hmac
        );

        // Crée la chaine url encodée selon la RFC1738 à partir du tableau de paramètres séparés par le caractère &
        $trame = http_build_query($postfields, '', '&');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $trame);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    private function buildHmacDebitEtransaction($amount, $reference, $cardDate, $stagiaireId, $etransactionToken, $numQuestion, $dateq)
    {
        $binKey = pack("H*", $this->PBX_KEY);
        $msg = $this->buildMessageHmacDebit($numQuestion, $amount, $reference, $etransactionToken, $stagiaireId, $cardDate, $dateq);

        return strtoupper(hash_hmac('sha512', $msg, $binKey));
    }

    private function buildTramDataPostToDebitTransaction($amount, $reference, $cardDate, $stagiaireId, $etransactionToken, $numQuestion, $dateq, $hmac)
    {
        $data = [
            'VERSION' => '00104',
            'TYPE' => '00053',
            'SITE' => $this->PBX_SITE,
            'RANG' => $this->PBX_RAND,
            'IDENTIFIANT' => $this->MerchandId,
            'NUMQUESTION' => $numQuestion,
            'MONTANT' => $amount,
            'DEVISE' => '978',
            'REFERENCE' => $reference,
            'PORTEUR' => $etransactionToken,
            'REFABONNE' => $stagiaireId,
            "DATEVAL" => $cardDate,
            'DATEQ' => $dateq,
            'HASH' => 'SHA512',
            'HMAC' => $hmac
        ];

        // Crée la chaine url encodée selon la RFC1738 à partir du tableau de paramètres séparés par le caractère &
        return http_build_query($data, '', '&');
    }

    /**
     * @param $amount
     * @return float|int
     */
    private function convertAmount($amount)
    {
        return $amount * 100;
    }

    /**
     * @param $numQuestion
     * @param $amount
     * @param $reference
     * @param $cardNumber
     * @param $cardDate
     * @param $cardCVV
     * @param $dateq
     * @param $stagiaireId
     * @param $ID3D
     */
    private function buildMessageHmac($numQuestion, $amount, $reference, $cardNumber, $cardDate, $cardCVV, $dateq, $stagiaireId, $ID3D)
    {
        $msg = "VERSION=00104" .
            "&TYPE=00001" .
            "&SITE=" . $this->PBX_SITE .
            "&RANG=" . $this->PBX_RAND .
            "&IDENTIFIANT=" . $this->MerchandId .
            "&NUMQUESTION=" . $numQuestion .
            "&MONTANT=" . $amount .
            "&DEVISE=978" .
            "&REFERENCE=" . $reference .
            "&PORTEUR=" . $cardNumber .
            "&HASH=SHA512" .
            "&DATEVAL=" . $cardDate .
            "&CVV=" . $cardCVV .
            "&DATEQ=" . $dateq .
            "&REFABONNE=" . $stagiaireId .
            "&ID3D=" . $ID3D;

        $this->logDebug->onlyLogForDebug('HMAC Authorisation ', 'hmac', $msg, 'hmac');

        return $msg;
    }

    /**
     * @param $numQuestion
     * @param $amount
     * @param $reference
     * @param $etransactionToken
     * @param $stagiaireId
     * @param $cardDate
     * @param $dateq
     */
    private function buildMessageHmacDebit($numQuestion, $amount, $reference, $etransactionToken, $stagiaireId, $cardDate, $dateq)
    {
        $msg = "VERSION=00104" .
            "&TYPE=00053" .
            "&SITE=" . $this->PBX_SITE .
            "&RANG=" . $this->PBX_RAND .
            "&IDENTIFIANT=" . $this->MerchandId .
            "&NUMQUESTION=" . $numQuestion .
            "&MONTANT=" . $amount .
            "&DEVISE=978" .
            "&REFERENCE=" . $reference .
            "&PORTEUR=" . $etransactionToken .
            "&REFABONNE=" . $stagiaireId .
            "&DATEVAL=" . $cardDate .
            "&DATEQ=" . $dateq .
            "&HASH=SHA512";

        $this->logDebug->onlyLogForDebug('HMAC Debit ', 'hmac', $msg, 'hmac');

        return $msg;
    }

    public function createAbonnee($amount, $reference, $studentId, $cardNumber, $cardDate, $cardCVV, $ID3D) {
        $numQuestion = time() + 1;
        $dateq = date('dmYHis');

        $msg = "VERSION=00104" .
            "&TYPE=00056" .
            "&SITE=" . $this->PBX_SITE .
            "&RANG=" . $this->PBX_RAND .
            "&IDENTIFIANT=" . $this->MerchandId .
            "&NUMQUESTION=" . $numQuestion .
            "&MONTANT=" . $amount .
            "&DEVISE=978" .
            "&REFERENCE=" . $reference .
            "&PORTEUR=" . $cardNumber .
            "&HASH=SHA512" .
            "&DATEVAL=" . $cardDate .
            "&CVV=" . $cardCVV .
            "&REFABONNE=" . $studentId .
            "&DATEQ=" . $dateq .
            "&ID3D=" . $ID3D;


        $binKey = pack("H*", $this->PBX_KEY);
        $hmac = strtoupper(hash_hmac('sha512', $msg, $binKey));

        $curl = curl_init($this->PBX_URL);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);


        $postfields = array(
            'VERSION' => '00104',
            'TYPE' => '00056',
            'SITE' => $this->PBX_SITE,
            'RANG' => $this->PBX_RAND,
            'IDENTIFIANT' => $this->MerchandId,
            'NUMQUESTION' => $numQuestion,
            'MONTANT' => $amount,
            'DEVISE' => '978',
            'REFERENCE' => $reference,
            'PORTEUR' => $cardNumber,
            'HASH' => 'SHA512',
            'DATEVAL' => $cardDate,
            'CVV' => $cardCVV,
            'REFABONNE' => $studentId,
            'DATEQ' => $dateq,
            'ID3D' => $ID3D,
            'HMAC' => $hmac
        );

        $trame = http_build_query($postfields, '', '&');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $trame);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}
