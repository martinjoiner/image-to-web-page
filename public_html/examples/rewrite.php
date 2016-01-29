<?php

$rawR = preg_replace( '/\.html/', '', $_GET['r'] );

$templateConfig["title"] = $rawR;
$templateConfig["content"] = '/examples/' . $rawR . '.inc.html';
$templateConfig["css"] = '<link href="/examples/' . $rawR . '.css" rel="stylesheet" type="text/css">';

include( $_SERVER['DOCUMENT_ROOT'] . '/template/main.inc.php' );
