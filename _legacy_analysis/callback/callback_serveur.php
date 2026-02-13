<?php

$action = $_GET['action'];
$key = $_GET['key'];


if ($key != "psp")
{
	return false;
}

if ($action == "get_xml")
{
	get_xml();
}

else if ($action == "rappel")
{
	$numero = $_GET['numero'];
	$nom = $_GET['nom'];
	$motif = $_GET['motif'];
	insert_rappel($numero, $nom, $motif);
}
else if ($action == "supprimer")
{
	$id = $_GET['id'];
	supprimer($id);
}
else if ($action == "change_status")
{
	$id = $_GET['id'];
	$status = $_GET['status'];
	change_status($id, $status);
}
else if ($action == "change_commentaire")
{
	$id = $_GET['id'];
	$commentaire = $_GET['commentaire'];
	change_commentaire($id, $commentaire);
}
?>

<?php
function insert_rappel($numero, $nom, $motif)
{

	include ("../callback/connection_callback.php");
	mysql_select_db($database_callconnect, $callconnect);

	$sql = "INSERT INTO rappels (date, numero, nom, motif) VALUES (NOW(), \"$numero\", \"$nom\", \"$motif\")";

	$rs = mysql_query($sql, $callconnect);
	mysql_close($callconnect);
}

function supprimer($id)
{

	include ("../callback/connection_callback.php");
	mysql_select_db($database_callconnect, $callconnect);

	$sql = "DELETE FROM rappels WHERE id=\"$id\"";

	$rs = mysql_query($sql, $callconnect);
	mysql_close($callconnect);
}

function change_status($id, $status)
{

	include ("../callback/connection_callback.php");
	mysql_select_db($database_callconnect, $callconnect);

	$sql = "UPDATE rappels SET status=\"$status\" WHERE id=\"$id\"";

	$rs = mysql_query($sql, $callconnect);
	mysql_close($callconnect);
}

function change_commentaire($id, $commentaire)
{

	include ("../callback/connection_callback.php");
	mysql_select_db($database_callconnect, $callconnect);

	$sql = "UPDATE rappels SET commentaire=\"$commentaire\" WHERE id=\"$id\"";

	$rs = mysql_query($sql, $callconnect);
	mysql_close($callconnect);
}

function get_xml()
{

	include ("../callback/connection_callback.php");
	mysql_select_db($database_callconnect, $callconnect);

	$sql = "SELECT * FROM rappels WHERE date < (CURDATE() - INTERVAL 7 DAY)";
	$rs = mysql_query($sql, $callconnect);
	$total = mysql_num_rows($rs);
	mysql_close($callconnect);

	if ($total != 0)
	{
		$_xml ="<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\r\n";
		$_xml .="<rappels>\r\n";

		while ($row = mysql_fetch_array($rs))
		{
			if ($row["id"])
			{
				$_xml .="\t<rappel id=\"" . $row["id"] . "\">\r\n";
				$_xml .="\t\t<date>" . $row["date"] . "</date>\r\n";
				$_xml .="\t\t<numero>" . $row["numero"] . "</numero>\r\n";
				$_xml .="\t\t<nom>" . $row["nom"] . "</nom>\r\n";
				$_xml .="\t\t<motif>" . $row["motif"] . "</motif>\r\n";
				$_xml .="\t\t<status>" . $row["status"] . "</status>\r\n";
				$_xml .="\t\t<commentaire>" . $row["commentaire"] . "</commentaire>\r\n";
				$_xml .="\t</rappel>\r\n";
			}
		}
		$_xml .="</rappels>";

		echo $_xml;

		//file_put_contents("rappels.xml",$_xml);  //méthode pour créér le fichier xml sur le serveur.
	}
	else
	{
		echo "No Records found";
	}
}
?>