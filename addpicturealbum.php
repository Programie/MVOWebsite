<?php
if ($_GET["key"] == "KnpZblPE7BXk7J2CqxDi22iPQpUQWR3Bh4303cBnU2iBKE2UZR")
{
	include("includes/mysql.php");
	mysql_query("
		INSERT INTO `pictures_cat`
		(
			`Public`,
			`Date`,
			`Name`,
			`Title`
		)
		VALUES(
			'" . $_GET["public"] . "',
			'" . $_GET["date"] . "',
			'" . $_GET["name"] . "',
			'" . $_GET["title"] . "'
		);
	");
	echo "OK";
}
else
{
	header("HTTP/1.1 403 Forbidden");
}
?>