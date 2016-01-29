<!DOCTYPE html>
<html>
<head>

	<title><?php
	if( isSet( $templateConfig["title"] ) ){
		print $templateConfig["title"] . ' - ';
	}
	?>Pixels - Convert an image to a HTML web page</title>

	<link href="/css/style.css" rel="stylesheet" type="text/css">
	<?php
	if( isSet( $templateConfig["css"] ) ){
		print $templateConfig["css"];
	}
	?>

</head>
<body>

	<nav>
		<a class="btn" href="/">Home</a>
		<!-- <a class="btn" href="/convert/">Convert an Image</a> -->
	</nav>
	
	<main>
		<?php 
		if( isSet( $templateConfig["content"] ) ){
			include( $_SERVER["DOCUMENT_ROOT"] . $templateConfig["content"] );
		} ?>
	</main>

	<footer>
		<p class="moreBSW">A project by Martin Joiner (<a href="//twitter.com/martinjoiner">@MartinJoiner on Twitter</a>) | Part of <a href="http://butterscotchworld.co.uk/">butterscotchworld.co.uk</a></p> 
	</footer>
</body>
</html>
