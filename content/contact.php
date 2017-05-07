<?php
$contacts = array
(
	"vorstand" => array
	(
		"name" => "Vorstandsteam",
		"text" => "Birgit Gerweck (07224 99 69 822)<br/>Edith Wieland (07224 69 70 467)"
	),
	"musikervorstand" => array
	(
		"name" => "Musikervorstand",
		"text" => "Katrin H&ouml;rth, Jasmin Melcher"
	),
	"kassenverwalter" => array
	(
		"name" => "Kassenverwalter",
		"text" => "Florian Wieland"
	),
	"schriftfuehrer" => array
	(
		"name" => "Schriftf&uuml;hrer",
		"text" => "Heike Kast"
	),
	"jugendleiter" => array
	(
		"name" => "Jugendleiter",
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
			<h3>" . $data["name"] . "</h3>
			<div>
				<p>" . $data["text"] . "</p>
				<p><a href='#' id='contact-mail-" . $name . "'></a></p>
			</div>
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