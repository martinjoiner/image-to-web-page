<?php

// TODO: Include autoload

// TODO: Return JSON

$objMarkupImage = new MarkupImage( $_POST["imageFilename"], $_POST["posterise"], $_POST["wording"], $_POST["desiredwindowwidth"], $_POST["pixelwidth"]);


// Write the HTML include
$fileContents = $objMarkupImage->getHTML();
$htmlFilename = '/generated/' . $objMarkupImage->getFilename('inc.html');

if( file_put_contents( $_SERVER['DOCUMENT_ROOT'] . $htmlFilename, $fileContents ) ){
	print '<h2>HTML file written</h2>';
} else {
	print '<h2>Failed to write HTML file</h2>';
}



// Write the CSS include
$fileContents = $objMarkupImage->getCSS();
$cssFilename = '/generated/' . $objMarkupImage->getFilename('css');

if( file_put_contents( $_SERVER['DOCUMENT_ROOT'] . $cssFilename, $fileContents ) ){
	print '<h2>CSS file written</h2>';
} else {
	print '<h2>Failed to write CSS file</h2>';
}

?>

<p><a href="/generated/<?=$objMarkupImage->getFilename('html')?>">/generated/<?=$objMarkupImage->getFilename('html')?></a></p>
