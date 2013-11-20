<?php
$contacts = array
(
	"vorstand" => array
	(
		"name" => "Vorstand (Gleichberechtigt)",
		"text" => "Erhard Klumpp (07224 65 26 83)<br/>Birgit Gerweck (07224 99 69 822)"
	),
	"musikervorstand" => array
	(
		"name" => "Musikervorstand",
		"text" => "Karl Fortenbacher<br/>Daniela Zapf"
	),
	"kassier" => array
	(
		"name" => "Kassenverwalterin",
		"text" => "Katrin H&ouml;rth"
	),
	"schriftfuehrer" => array
	(
		"name" => "Schriftf&uuml;hrerin",
		"text" => "Heike Kast"
	),
	"jugendleiter" => array
	(
		"name" => "Jugendleiterin",
		"text" => "Gisela Wieland"
	),
	"oeffentlichkeitsarbeit" => array
	(
		"name" => "&Ouml;ffentlichkeitsarbeit",
		"text" => "Edith Wieland"
	),
	"webmaster" => array
	(
		"name" => "Webmaster",
		"text" => "Michael Wieland"
	)
);

$contactNames = array();
foreach ($contacts as $name => $data)
{
	$contactNames[] = $name;
}
?>
<div class="center">
	<h1>Kontakt</h1>

	<h3>postalisch</h3>

	<p>
		Musikverein "Orgelfels" Reichental e.V.<br/>
		Birgit Gerweck<br/>
		Neuer Weg 13/1<br/>
		76593 Gernsbach - Reichental
	</p>

	<?php
	foreach ($contacts as $name => $data)
	{
		echo "
			<h3>" . $data["name"] . " <a href='#' id='contact-mail-" . $name . "'></a></h3>
			<p>" . $data["text"] . "</p>
		";
	}
	?>
</div>

<script type="text/javascript">
	var contactNames = <?php echo json_encode($contactNames);?>;
	for (var index in contactNames)
	{
		var name = contactNames[index];
		var element = document.getElementById("contact-mail-" + name);
		var uMail = name + decodeURIComponent("<?php echo jsEscape("@" . CONTACT_MAIL_DOMAIN);?>");
		element.innerText = uMail;
		element.href = "mailto:" + uMail;
	}
</script>