<?php
function sendError($message, $err_id1=NULL, $err_id2=NULL, $err_id3=NULL)
{
	$dateLocal = date("d-m-Y");

	$contact = "contact@prostagespermis.fr";
	$subject = "Alerte: Erreur technique";

	$headers = "From: PROStagesPermis <contact@prostagespermis.fr>\n";
	$headers .= "Reply-To: ".$contact."\n";
	$headers .= 'MIME-version: 1.0'."\n";
	$headers .= 'Content-type: text/html; charset= iso-8859-1'."\n";

	$contenu  = "<pre style=\"font-size:12px\">";
	$contenu .= "Erreur: ".$message;
	$contenu .= "<br><br>";
	$contenu .= $err_id1."<br>";
	$contenu .= $err_id2."<br>";
	$contenu .= $err_id3."<br>";
	$contenu .= "</pre>";

	mail($contact, $subject, $contenu, $headers);
}

?>