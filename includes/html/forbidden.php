<h1>Zugriff nicht erlaubt</h1>

<p>
	<?php
	if (Constants::$accountManager->getUserId())
	{
		echo "Sie besitzen nicht die notwendigen Rechte um auf die gew&uuml;nschte Seite <b>" . implode(Constants::$pagePath, "/") . "</b> zuzugreifen!";
	}
	else
	{
		echo "Die Seite <b>" . implode(Constants::$pagePath, "/") . "</b> ist nur zug&auml;nglich wenn Sie im internen Bereich angemeldet sind. Bitte melden Sie sich <a href='/internarea'>hier</a> an und versuchen Sie es erneut.";
	}
	?>
</p>