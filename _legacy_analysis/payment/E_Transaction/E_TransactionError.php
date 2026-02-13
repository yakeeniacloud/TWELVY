<?php

class E_TransactionError
{
    public $api_response_codes_internal = array(
        "00001" => "La connexion au centre d’autorisation a échoué ou une erreur interne est survenue.",
        "00101" => "Contacter l’émetteur de carte.",
        "00102" => "Contacter l’émetteur de carte.",
        "00103" => "Commerçant invalide.",
        "00104" => "Conserver la carte.",
        "00105" => "Ne pas honorer.",
        "00107" => "Conserver la carte, conditions spéciales.",
        "00108" => "Approuver après identification du porteur.",
        "00112" => "Transaction invalide.",
        "00113" => "Montant invalide.",
        "00119" => "Répéter la transaction ultérieurement.",
        "00120" => "Réponse erronée (erreur dans le domaine serveur).",
        "00124" => "Mise à jour de fichier non supportée.",
        "00125" => "Impossible de localiser l’enregistrement dans le fichier.",
        "00126" => "Enregistrement dupliqué, ancien enregistrement remplacé.",
        "00127" => "Erreur en « edit » sur champ de mise à jour fichier.",
        "00128" => "Accès interdit au fichier.",
        "00129" => "Mise à jour de fichier impossible.",
        "00158" => "Transaction interdite au terminal",
        "00160" => "L’accepteur de carte doit contacter l’acquéreur",
        "00163" => "Règles de sécurité non respectées",
        "00176" => "Porteur déjà en opposition, ancien enregistrement conservé",
        "00191" => "Arrêt momentané du système",
        "00194" => "Demande dupliquée",
        "00197" => "Échéance de la temporisation de surveillance globale",
        "00002" => "Une erreur de cohérence est survenue.",
        "00003" => "Erreur Plateforme.",
        "00005" => "Numéro de question invalide.",
        "00006" => "Accès refusé au site / rang incorrect.",
        "00009" => "Type d’opération invalide.",
        "00010" => "Devise inconnue.",
        "00011" => "Montant incorrect.",
        "00012" => "Référence commande invalide.",
        "00013" => "Cette version n’est plus soutenue.",
        "00014" => "Trame reçue incohérente.",
        "00015" => "Erreur d’accès aux données précédemment référencées.",
        "00016" => "Abonné déjà existant (inscription nouvel abonné).",
        "00017" => "Abonné inexistant.",
        "00018" => "Transaction non trouvée (question du type 11).",
        "00019" => "Réservé.",
        "00023" => "Porteur déjà passé aujourd’hui.",
        "00024" => "Code pays filtré pour ce commerçant.",
        "00041" => "ID3D Inconnu.",
        "00098" => "Erreur de connexion interne.",
        "00099" => "Incohérence entre la question et la réponse. Refaire une nouvelle tentative ultérieurement.",
        "00182" => "Code erreur en retour de la banque inconnu",
        "00196" => "Mauvais fonctionnement du système"
    );

    public $api_response_codes_customers = array(
        "00114" => "Numéro de carte erroné",
        "00115" => "Banque non reconnue",
        "00117" => "Annulation client.",
        "00130" => "Numéro de carte erroné",
        "00133" => "Carte expirée.",
        "00138" => "Nombre d’essais code confidentiel dépassé.",
        "00141" => "Carte perdue.",
        "00143" => "Carte volée.",
        "00151" => "Provision insuffisante ou crédit dépassé",
        "00154" => "Date de validité de la carte dépassée",
        "00155" => "Code confidentiel erroné",
        "00156" => "Carte non reconnue",
        "00157" => "Carte non autorisée",
        "00159" => "Carte non reconnue",
        "00161" => "Provision insuffisante ou crédit dépassé",
        "00168" => "Temps d'execution du paiement dépassé",
        "00175" => "Nombre d’essais code confidentiel dépassé",
        "00190" => "Échec de l’authentification",
        "00196" => "Banque non reconnue",
        "00201" => "Échec de l’authentification 3DS",
        "00004" => "Votre numéro de carte est erroné.",
        "00007" => "Date d'expiration carte invalide.",
        "00008" => "Date de fin de validité incorrecte.",
        "00020" => "Cryptogramme non renseigné.",
        "00021" => "Carte non autorisée.",
        "00022" => "Plafond atteint.",
        "00097" => "Temps d'execution du paiement dépassé.",
    );

    public function isCritiqueCode($code)
    {
        return array_key_exists(
            $code,
            [
                "00001" => "La connexion au centre d’autorisation a échoué ou une erreur interne est survenue.",

                "00003" => "Erreur de la plateforme. Dans ce cas, 
                il est souhaitable de faire une tentative sur l‘autre site tpeweb.e-transactions.fr ou 
                tpeweb1.e-transactions.fr en fonction de celui que vous utilisez.",

                "00006" => "Accès refusé ou site/rang/identifiant incorrect. 
                Veuillez vérifier votre paramétrage ou le calcul de la signature HMAC (PBX_HMAC).",

                "00009" => "Erreur de création d’un abonnement.",
                "00010" => "Devise inconnue.",
                "00011" => "Montant incorrect.",
                "00037" => "HMAC invalide"

            ]
        );
    }


    public function parseErrorCode($code)
    {
        $comment = "Le code de sécurité (cryptogramme) de votre carte est erroné. Veuillez corriger votre saisie et valider le paiement";

        switch ($code) {
            case '00004':
                $comment = 'Votre numéro de carte est erroné. Veuillez corriger votre saisie et valider le paiement';
                break;
            case '00008':
                $comment = 'La date de fin de validité de votre carte est incorrecte';
                break;
            case '00015':
                $comment = 'Votre paiement a déjà été effectué';
                break;
            case '00021':
                $comment = 'Votre carte n\'a pas été autorisée';
                break;
            case '00022':
                $comment = 'Plafond atteint';
                break;
            case '00105':
                $comment = 'Votre paiement n\'a pas pu aboutir, veuillez reprendre le processus d\'achat';
                break;
            case '00114':
                $comment = 'Le numéro de votre carte est invalide, veuillez vérifier et réessayer à nouveau';
                break;
            case '00115':
                //Votre carte est inconnu, veuillez réessayer à nouveau
                $comment = 'Votre numéro de carte est erroné. Veuillez corriger votre saisie et valider le paiement';
                break;
            case '00117':
                $comment = 'Vous avez annulé votre transaction';
                break;
            case '00133':
                $comment = 'Votre carte a expirée, veuillez réessayer à nouveau avec une autre carte';
                break;
            case '00143':
                $comment = 'Cette carte semble volée, veuillez réessayer à nouveau avec une autre carte';
                break;
            case '00151':
                $comment = 'Solde insuffisant ou crédit dépassé, veuillez vérifier votre solde et réessayer à nouveau';
                break;
            case '00154':
                $comment = 'Date de validité de la carte dépassée';
                break;
            case '00157':
                $comment = 'La date d\'expiration de votre carte est erronée. Veuillez renseigner la bonne date d\'expiration et valider le paiement';
                break;
            case '00159':
                $comment = 'Suspicion de fraude, veuillez vérifier au près de votre banque ou changer de carte';
                break;
        }

        return $comment;
    }

    public function parseFullErrorCodes($code)
    {
        $api_response_codes_internal = array(
            "00001" => "La connexion au centre d’autorisation a échoué ou une erreur interne est survenue.",
            "00101" => "Contacter l’émetteur de carte.",
            "00102" => "Contacter l’émetteur de carte.",
            "00103" => "Commerçant invalide.",
            "00104" => "Conserver la carte.",
            "00105" => "Ne pas honorer.",
            "00107" => "Conserver la carte, conditions spéciales.",
            "00108" => "Approuver après identification du porteur.",
            "00112" => "Transaction invalide.",
            "00113" => "Montant invalide.",
            "00119" => "Répéter la transaction ultérieurement.",
            "00120" => "Réponse erronée (erreur dans le domaine serveur).",
            "00124" => "Mise à jour de fichier non supportée.",
            "00125" => "Impossible de localiser l’enregistrement dans le fichier.",
            "00126" => "Enregistrement dupliqué, ancien enregistrement remplacé.",
            "00127" => "Erreur en « edit » sur champ de mise à jour fichier.",
            "00128" => "Accès interdit au fichier.",
            "00129" => "Mise à jour de fichier impossible.",
            "00158" => "Transaction interdite au terminal",
            "00160" => "L’accepteur de carte doit contacter l’acquéreur",
            "00163" => "Règles de sécurité non respectées",
            "00176" => "Porteur déjà en opposition, ancien enregistrement conservé",
            "00191" => "Arrêt momentané du système",
            "00194" => "Demande dupliquée",
            "00197" => "Échéance de la temporisation de surveillance globale",
            "00002" => "Une erreur de cohérence est survenue.",
            "00003" => "Erreur Plateforme.",
            "00005" => "Numéro de question invalide.",
            "00006" => "Accès refusé au site / rang incorrect.",
            "00009" => "Type d’opération invalide.",
            "00010" => "Devise inconnue.",
            "00011" => "Montant incorrect.",
            "00012" => "Référence commande invalide.",
            "00013" => "Cette version n’est plus soutenue.",
            "00014" => "Trame reçue incohérente.",
            "00015" => "Erreur d’accès aux données précédemment référencées.",
            "00016" => "Abonné déjà existant (inscription nouvel abonné).",
            "00017" => "Abonné inexistant.",
            "00018" => "Transaction non trouvée (question du type 11).",
            "00019" => "Réservé.",
            "00023" => "Porteur déjà passé aujourd’hui.",
            "00024" => "Code pays filtré pour ce commerçant.",
            "00041" => "ID3D Inconnu.",
            "00098" => "Erreur de connexion interne.",
            "00099" => "Incohérence entre la question et la réponse. Refaire une nouvelle tentative ultérieurement.",
            "00182" => "Code erreur en retour de la banque inconnu",
            "00196" => "Mauvais fonctionnement du système"
        );

        $api_response_codes_customers = array(
            "00114" => "Numéro de carte erroné",
            "00115" => "Banque non reconnue",
            "00117" => "Annulation client.",
            "00130" => "Numéro de carte erroné",
            "00133" => "Carte expirée.",
            "00138" => "Nombre d’essais code confidentiel dépassé.",
            "00141" => "Carte perdue.",
            "00143" => "Carte volée.",
            "00151" => "Provision insuffisante ou crédit dépassé",
            "00154" => "Date de validité de la carte dépassée",
            "00155" => "Code confidentiel erroné",
            "00156" => "Carte non reconnue",
            "00157" => "Carte non autorisée",
            "00159" => "Carte non reconnue",
            "00161" => "Provision insuffisante ou crédit dépassé",
            "00168" => "Temps d'execution du paiement dépassé",
            "00175" => "Nombre d’essais code confidentiel dépassé",
            "00190" => "Échec de l’authentification",
            "00196" => "Banque non reconnue",
            "00201" => "Échec de l’authentification 3DS",
            "00004" => "Votre numéro de carte est erroné.",
            "00007" => "Date d'expiration carte invalide.",
            "00008" => "Date de fin de validité incorrecte.",
            "00020" => "Cryptogramme non renseigné.",
            "00021" => "Carte non autorisée.",
            "00022" => "Plafond atteint.",
            "00097" => "Temps d'execution du paiement dépassé.",
        );

        if (isset($api_response_codes_customers[$code])) {
            return $api_response_codes_customers[$code];
        } else {
            if (isset($api_response_codes_internal[$code])) {
                return "Une erreur technique est survenue.";
            } else {
                return "Une erreur technique est survenue.";
            }
        }
    }

    public function getFullTextErrorCodes($code)
    {
        $api_response_codes_internal = array(
            "00001" => "La connexion au centre d’autorisation a échoué ou une erreur interne est survenue.",
            "00101" => "Contacter l’émetteur de carte.",
            "00102" => "Contacter l’émetteur de carte.",
            "00103" => "Commerçant invalide.",
            "00104" => "Conserver la carte.",
            "00105" => "Ne pas honorer.",
            "00107" => "Conserver la carte, conditions spéciales.",
            "00108" => "Approuver après identification du porteur.",
            "00112" => "Transaction invalide.",
            "00113" => "Montant invalide.",
            "00119" => "Répéter la transaction ultérieurement.",
            "00120" => "Réponse erronée (erreur dans le domaine serveur).",
            "00124" => "Mise à jour de fichier non supportée.",
            "00125" => "Impossible de localiser l’enregistrement dans le fichier.",
            "00126" => "Enregistrement dupliqué, ancien enregistrement remplacé.",
            "00127" => "Erreur en « edit » sur champ de mise à jour fichier.",
            "00128" => "Accès interdit au fichier.",
            "00129" => "Mise à jour de fichier impossible.",
            "00158" => "Transaction interdite au terminal",
            "00160" => "L’accepteur de carte doit contacter l’acquéreur",
            "00163" => "Règles de sécurité non respectées",
            "00176" => "Porteur déjà en opposition, ancien enregistrement conservé",
            "00191" => "Arrêt momentané du système",
            "00194" => "Demande dupliquée",
            "00197" => "Échéance de la temporisation de surveillance globale",
            "00002" => "Une erreur de cohérence est survenue.",
            "00003" => "Erreur Plateforme.",
            "00005" => "Numéro de question invalide.",
            "00006" => "Accès refusé au site / rang incorrect.",
            "00009" => "Type d’opération invalide.",
            "00010" => "Devise inconnue.",
            "00011" => "Montant incorrect.",
            "00012" => "Référence commande invalide.",
            "00013" => "Cette version n’est plus soutenue.",
            "00014" => "Trame reçue incohérente.",
            "00015" => "Erreur d’accès aux données précédemment référencées.",
            "00016" => "Abonné déjà existant (inscription nouvel abonné).",
            "00017" => "Abonné inexistant.",
            "00018" => "Transaction non trouvée (question du type 11).",
            "00019" => "Réservé.",
            "00023" => "Porteur déjà passé aujourd’hui.",
            "00024" => "Code pays filtré pour ce commerçant.",
            "00041" => "ID3D Inconnu.",
            "00098" => "Erreur de connexion interne.",
            "00099" => "Incohérence entre la question et la réponse. Refaire une nouvelle tentative ultérieurement.",
            "00182" => "Code erreur en retour de la banque inconnu",
            "00196" => "Mauvais fonctionnement du système"
        );

        $api_response_codes_customers = array(
            "00114" => "Votre paiement n'a pas pu aboutir car <u>le numéro de carte saisi est incorrect</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.",
            "00115" =>
            "Votre paiement n'a pas pu aboutir car <u>les informations de votre carte n'ont pas été reconnues</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.",
            "00117" =>
            "Votre paiement n'a pas pu aboutir car <u>les informations de votre carte n'ont pas été reconnues</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.",
            "00130" =>
            "Votre paiement n'a pas pu aboutir car <u>votre numéro de carte est erroné</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.",
            "00133" =>
            "Votre paiement n'a pas pu aboutir car <u>votre numéro de carte est erroné</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.",
            "00138" =>
            "Votre paiement n'a pas pu aboutir car <u>le nombre d'essais du code confidentiel a été dépassé</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.",
            "00141" =>
            "Votre paiement n'a pas pu aboutir car <u>votre carte semble avoir été déclarée comme étant perdue</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.",
            "00143" =>
            "Votre paiement n'a pas pu aboutir car <u>votre carte semble avoir été déclarée comme étant volée</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.",
            "00151" =>
            "Votre paiement n'a pas pu aboutir car <u>la provision sur votre compte est insuffisante ou le plafond de votre carte a été atteint</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.",
            "00154" =>
            "Votre paiement n'a pas pu aboutir car <u>la date de validité de votre carte est dépassée</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.",
            "00155" =>
            "Votre paiement n'a pas pu aboutir car <u>votre code confidentiel est erroné.</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.",
            "00156" =>
            "Votre paiement n'a pas pu aboutir car <u>le numéro de carte saisi est incorrect</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.",
            "00157" =>
            "Votre paiement n'a pas pu aboutir car <u>les informations de votre carte n'ont pas été reconnues</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.",
            "00159" =>
            "Votre paiement n'a pas pu aboutir car <u>les informations de votre carte n'ont pas été reconnues</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.",
            "00161" =>
            "Votre paiement n'a pas pu aboutir car <u>la provision sur votre compte est insuffisante ou le plafond de votre carte a été atteint</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.",
            "00168" =>
            "Votre paiement n'a pas pu aboutir car <u>le temps d'exécution du paiement a été dépassé</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.",
            "00175" =>
            "Votre paiement n'a pas pu aboutir car <u>le nombre d'essais du code confidentiel a été dépassé</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.",
            "00190" =>
            "Votre paiement n'a pas pu aboutir car <u>les informations de votre carte n'ont pas été reconnues</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.",
            "00196" =>
            "Votre paiement n'a pas pu aboutir car <u>les informations de votre carte n'ont pas été reconnues</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.",
            "00201" =>
            "Votre paiement n'a pas pu aboutir car <u>votre code confidentiel est erroné</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.",
            "00004" =>
            "Votre paiement n'a pas pu aboutir car <u>le numéro de carte saisi est incorrect</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.",
            "00007" =>
            "Votre paiement n'a pas pu aboutir car <u>la date d'expiration de votre carte est incorrecte</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.",
            "00008" =>
            "Votre paiement n'a pas pu aboutir car <u>la date d'expiration de votre carte est incorrecte</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.",
            "00020" =>
            "Votre paiement n'a pas pu aboutir car <u>les numéros de votre carte sont incorrects</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.",
            "00021" =>
            "Votre paiement n'a pas pu aboutir car <u>les informations de votre carte n'ont pas été reconnues</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.",
            "00022" =>
            "Votre paiement n'a pas pu aboutir car <u>la provision sur votre compte est insuffisante ou le plafond de votre carte a été atteint</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.",
            "00097"
            => "Votre paiement n'a pas pu aboutir car <u>le temps d'exécution du paiement a été dépassé</u>.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte."
        );

        if (isset($api_response_codes_customers[$code])) {
            return $api_response_codes_customers[$code];
        } else {
            if (isset($api_response_codes_internal[$code])) {
                return "Votre paiement n'a pas pu aboutir suite à une erreur technique.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.";
            } else {
                return "Votre paiement n'a pas pu aboutir suite à une erreur technique.<br>Veuillez vérifier vos informations bancaires et réessayer.<br>Si le problème persiste, veuillez contacter votre banque ou utiliser une autre carte.";
            }
        }
    }

    function transactionSuccessInError($msg, $commentaire)
    {
        $acceptSentences = ["demande trait"];

        $msg = strtolower($msg);
        $commentaire = strtolower($commentaire);

        foreach ($acceptSentences as $sentence) {
            if (is_int(stripos($msg, $sentence)) || is_int(stripos($commentaire, $sentence))) {
                return false;
            }
        }

        return true;
    }
}
