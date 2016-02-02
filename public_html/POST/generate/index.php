<?php

/**
 * Receives a form by POST, uploads the image file to 'uploads' folder, processes it using an instance of Converter, 
 * Writes a single static HTML page to disk in 'generated' folder, returns a report as a JSON object.
 */

// Ensure only POST requests get served
if( $_SERVER['REQUEST_METHOD'] != 'POST' ){
	http_response_code(404);
	throw new Exception('Invalid request');
}

// Include autoload
require '../../../vendor/autoload.php';


// Create a path and clean file name
$uploadFile = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . preg_replace( '/[^A-Za-z0-9_.-]*/', '', basename($_FILES['imagefile']['name']) );


// If the file is a jpg, move it to uploads
if( preg_match( '/\.(jpg|jpeg|png)$/i', $uploadFile ) ){

	move_uploaded_file( $_FILES['imagefile']['tmp_name'], $uploadFile );

} else {

	throw new Exception('Non-image file uploaded');
}


use ImageToWebPage\Converter;

$objMarkupImage = new Converter( $uploadFile, $_POST["posterise"], $_POST["wording"], $_POST["image_width"], $_POST["pixelwidth"] );

$writePageReport = $objMarkupImage->writeStaticPage( '/generated/' );

// Set header to JSON
header('Content-Type: application/json');

// Output report as JSON object
echo json_encode( $writePageReport );
