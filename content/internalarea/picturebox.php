<?php
if (Constants::$accountManager->hasPermission("picturebox.upload"))
{
	?>
	<h1>Bilder hochladen</h1>
	<form action="/internalarea/picturebox" class="dropzone" id="picturebox-dropbox"></form>
	<?php
	// TODO: Allow user to upload multiple files
}

if (Constants::$accountManager->hasPermission("picturebox.manage"))
{
	// TODO: Allow user to manage files (Delete)
}