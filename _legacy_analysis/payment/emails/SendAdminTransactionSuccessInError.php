<?php
require_once APP . 'mail/Mail.php';
require_once APP . 'mail/EmailTag.php';
require_once ROOT . '/mails_v3/functions.php';
require_once APP . 'student/services/RetrieveStudentById.php';
require_once APP . 'stage/services/RetrieveStageById.php';
require_once APP . 'member/services/RetrieveMember.php';
require_once APP . "site/services/RetrieveSiteById.php";

class SendAdminTransactionSuccessInError
{
  public function execute(
    $idStagiaire,
    $mysqli
  ) {
    $student    =   (new RetrieveStudentById())->__invoke($idStagiaire, $mysqli);
    $subject = "Suspicion d'anomalie sur une transaction Stagiaire " . $student->nom . " " . $student->prenom;

    $content = $this->buildHtmlContent(
      $student->id,
      $student->nom,
      $student->prenom,
      $student->email
    );

    $mail = new Mail();

    try {
      $mail->to('contact@prostagespermis.fr')
        ->subject($subject)
        ->body($content)
        ->send();

      return true;
    } catch (\Exception $e) {
      return false;
    }
  }

  private function buildHtmlContent(
    $id,
    $firstname,
    $lastname,
    $email
  ) {
    $msg = "";
    $msg .= "<p style='color:red;text-align:center;'><u>Important:</u> Ce mail est envoyé automatiquement suite à une suspicion de transaction éronnée.</p>";
    $msg .= "<p>En effet, l'API Up2pay nous renvoie une transaction effective mais leur message de retour nous signale une anomalie.</p>";
    $msg .= "<p><b>Stagiaire : </b> $id - $email - $firstname $lastname</p>";
    $msg .= "<br><br>";
    $msg .= "<span style='font-style:italic;color:grey'><hr><img src='https://www.prostagespermis.fr/mails_v3/img/logo.jpg'></span>";

    return $msg;
  }
}
