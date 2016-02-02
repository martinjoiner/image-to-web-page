<?php

header('Content-Type: application/json');

/**
 Receives a form by POST, uploads the image file to 'uploads' folder, processes it using settings, 
 writes a single static HTML page to disk in 'generated' folder, returns a JSON object with success/fail message and url of generated file.
*/

// Include autoload
require '../../../vendor/autoload.php';




$uploadFile = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . preg_replace( '/[^A-Za-z0-9_.-]*/', '', basename($_FILES['imagefile']['name']) );

// If the file is a jpg, move it to uploads
if( preg_match( '/\.(jpg|jpeg|png)$/i', $uploadFile ) ){

	move_uploaded_file( $_FILES['imagefile']['tmp_name'], $uploadFile );

} else {

	throw new Exception('Non-image file uploaded');
}



use ImageToWebPage\Converter;

$objMarkupImage = new Converter( $uploadFile, $_POST["posterise"], $_POST["wording"], $_POST["desiredwindowwidth"], $_POST["pixelwidth"]);

$writePageReport = $objMarkupImage->writeStaticPage( '/generated/' );

echo json_encode( $writePageReport );

