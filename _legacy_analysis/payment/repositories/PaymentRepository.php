<?php

class PaymentRepository
{


    private $mysqli;

    public function __construct($mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function updateTransactionData(
        $stageId,
        $studentId,
        $memberId,
        $orderId,
        $autorisation
    ) {
        $sql = "UPDATE transaction set type_paiement = 'CB_OK', autorisation = '$autorisation', paiement_interne = 1 WHERE id_stagiaire = $studentId AND id_stage = $stageId";
        $this->mysqli->query($sql);

        $sql = "UPDATE order_stage set is_paid = true WHERE id = $orderId";
        $this->mysqli->query($sql);
    }

    public function updateStageData($stageId) {
        $sql = "UPDATE 
					stage 
				SET 
					nb_places_allouees = (nb_places_allouees - 1), 
					nb_inscrits = (nb_inscrits + 1),
					taux_remplissage = (taux_remplissage +1)
				WHERE 
					id = $stageId";
        $this->mysqli->query($sql);
    }

    public function updateStudentData(
        $studentId,
        $stageId,
        $memberId,
        $cardNumber,
        $numAppel,
        $numTrans,
        $partenariat,
        $commission_ht,
        $numSuivi,
        $marge_commerciale='',
        $taux_marge_commerciale=''
    ) {

        $date = date('Y-m-d');
        $dateTime = date('Y-m-d H:i:s');

        $facture_num = $numSuivi - 1000;
        $sql = "SELECT s.* 
        FROM stage AS s 
        INNER JOIN site AS si ON si.id=s.id_site AND s.id=".$stageId."
        INNER JOIN departement AS d ON d.code_departement=si.departement AND si.visibilite=1 AND d.activation_mc24=1";
		$marge_commerciale = 0;
		$taux_marge_commerciale = 0;
		$prix_index_ttc = 0;
		$prix_index_min = 0;
        if($stage = mysqli_fetch_object($this->mysqli->query($sql))){
			if($stage->prix_ancien > 0)
				$prix_index_ttc = $stage->prix_ancien;
			else
				$prix_index_ttc = $stage->prix; 
			
            $marge_commerciale = $stage->marge_commerciale;
            $taux_marge_commerciale = $stage->taux_marge_commerciale;
            $prix_index_min = $stage->prix_index_min;
		}

        $sql = "UPDATE stagiaire 
                SET supprime=0, 
                    status='inscrit', 
                    numero_cb = '$cardNumber',
                    numappel = '$numAppel', 
                    numtrans = '$numTrans',   
                    partenariat = '$partenariat',
                    commission_ht = '$commission_ht',
                    date_inscription = '$date',
                    date_preinscription = '$date',
                    datetime_preinscription = '$dateTime',
                    facture_num  = $facture_num,
                    marge_commerciale  = $marge_commerciale ,
                    taux_marge_commerciale  = $taux_marge_commerciale,
                    prix_index_ttc  = $prix_index_ttc,
                    prix_index_min  = $prix_index_min  
                WHERE id=$studentId";

        $this->mysqli->query($sql);
        $sql = "INSERT INTO archive_inscriptions(id_stagiaire, id_stage, id_membre) VALUES (". $studentId .", ". $stageId ." , ". $memberId .")";
        $this->mysqli->query($sql);
    }

    public function getTransactionById($transactionId) {
        $sql = "SELECT * FROM transaction WHERE id = '$transactionId'";
        return mysqli_fetch_object($this->mysqli->query($sql));
    }

}