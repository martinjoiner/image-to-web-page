<?php

namespace ImageToWebPage;

use ImageToWebPage\Classifier;

Class Converter{

	/**
	 * The name of the source file that was uploaded
	 *
	 * @type {string}
	 */
	private $originalFileName = '';


	/**
	 * Posterise determines how many groups we break color spectrum into
	 *
	 * @type {integer}
	 */
	private $numPosterise = 0;


	/**
	 * The message that will be repeated in the web page
	 * 
	 * @type {string}
	 */
	private $strWording = '';


	/**
	 * The total width of the resulting image
	 *
	 * @type {integer}
	 */
	private $image_width = 0;


	/**
	 * The actual width of the resulting image (May not be the same as the width the user requested)
	 *
	 * @type {integer}
	 */
	private $wrapperWidth = 0;


	/**
	 * The actual height of the resulting image
	 *
	 * @type {integer}
	 */
	private $wrapperHeight = 0;


	/**
	 * The width (and height; pixels are square) of the blocks that make up the image
	 *
	 * @type {integer}
	 */
	private $pixelWidth = 0;


	/**
	 * Array of pixels (HTML elements) that make up the resulting page
	 *
	 * @type {array}
	 */
	private $arrPixels = [];


	/**
	 * The classifier is used to build the library of CSS classes
	 *
	 * @type {object} Instance of Classifier class 
	 */
	private $classifier;


	/**
	 * An associative array to store stats for profiling
	 *
	 * @type {array}
	 */
	private $stats = [];



	/**
	 * Constructor that does the conversion
	 * 
	 * @param {string} $absoluteFilename The path of an image file
	 * @param {integer} $numPosterise The level of posterisation
	 * @param {string} $strWording The message that will be repeated in the web page
	 * @param {integer} $image_width The total width (in CSS pixels) of the produced HTML image
	 * @param {integer} $pixelWidth The width (in CSS pixels) of an individual HTML element
	 */
	public function __construct( $absoluteFilename, $numPosterise, $strWording, $image_width, $pixelWidth ){

		$startTime = microtime(true);

		// Build a stats object to 
		$this->stats = [ 	'time' => null,
							'sourcePixels' => null
						];

		$this->classifier = new Classifier();

		$this->originalFileName = end( explode('/', $absoluteFilename) );

		$this->numPosterise 	= $numPosterise;
		$this->strWording 		= $strWording;
		$this->image_width 		= $image_width;
		$this->pixelWidth 		= $pixelWidth;

		list($sourceImageWidth, $sourceImageHeight, $type, $attr) = getimagesize( $absoluteFilename );

		$this->stats['sourcePixels'] = $sourceImageWidth * $sourceImageHeight;

		// Discover how many pixels from the original source image we use to make an average colour for our HTML elements 
		$numHorizontalElems = $this->image_width / $this->pixelWidth;
		if( $sourceImageWidth > $numHorizontalElems ){
			$averageRange = round( $sourceImageWidth / $numHorizontalElems );
		} else {
			// There are less pixels in a row of the source image than we need elements in our page
			$averageRange = 4;
		}

		$img = $this->imageResouceFromFile( $absoluteFilename );

		// Iterate down the Y axis, incrementing by the range size each time
		for($y = 0; $y < $sourceImageHeight; $y = $y + $averageRange){

			for($x = 0; $x < $sourceImageWidth; $x = $x + $averageRange){	

				// Define 3 arrays to hold all the color values of surrounding pixels
				$r = [];
				$g = [];
				$b = [];
				
				// Build an array of all the pixels within the average range of this target one
				for($y2 = $y; $y2 < $y+$averageRange; $y2++){

					for($x2 = $x; $x2 < $x+$averageRange; $x2++){

						$rgb = imagecolorat($img, $x2, $y2);
						
						$r[] = ($rgb >> 16) & 0xFF;
						$g[] = ($rgb >> 8) & 0xFF;
						$b[] = $rgb & 0xFF;
					}
				}
				
				// Calculate the average color of each color group
				$red 	= array_sum( $r ) / count($r);
				$green 	= array_sum( $g ) / count($g);
				$blue 	= array_sum( $b ) / count($b);
				
				// Ensure values are within RGB friendly range
				$red 	= $this->limitTo256Bit( $red );
				$green 	= $this->limitTo256Bit( $green );
				$blue 	= $this->limitTo256Bit( $blue );
				
				// Posterise if required
				if( $this->numPosterise ){
					list( $red, $green, $blue ) = $this->posterise( $red, $green, $blue );
				}
				
				// Add this pixel object to the array
				$this->arrPixels[] = [ 'color' => $this->rgbToHexCode( $red, $green, $blue ) ];
			}
		}

		// Iterate over the array of pixels, assigning their CSS class, based on their color
		$iLimit = sizeof( $this->arrPixels );
		for( $i = 0; $i < $iLimit; $i++ ){
			$this->arrPixels[$i]['class'] = $this->classifier->colorClass( $this->arrPixels[$i]['color'] );
		}

		$this->wrapperWidth = $this->pixelWidth * ceil($sourceImageWidth / $averageRange);
		$this->wrapperHeight = $this->pixelWidth * ceil($sourceImageHeight / $averageRange);

		// Record processing time in stats array
		$this->stats['time'] = microtime(true) - $startTime; 

	}



	/**
	 * Turns a file into an image resource using the appropriate PHP function for the file type
	 *
	 * @param {string} $file File path
	 *
	 * @return {object} Image resource
	 */
	private function imageResouceFromFile( $file ){
		$extension = strtolower(strrchr($file, '.'));

		switch ($extension) {
	        case '.jpg':
	        case '.jpeg':
	            return @imagecreatefromjpeg($file);
	            break;
	        case '.gif':
	            return @imagecreatefromgif($file);
	            break;
	        case '.png':
	            return @imagecreatefrompng($file);
	            break;
	        default:
	            return false;
	            break;
	    }

	}



	/**
	 * Converts a color to a closest match within a limited range
	 *
	 * NOTE: At the moment this is a very basic monotone version of posterisation.
	 * 		 Future enhancement would involve waiting until all the required colors
	 *		 have been assigned and then posterising them. 
	 *
	 * @param {integer} $red
	 * @param {integer} $green
	 * @param {integer} $blue
	 *
	 * @return {array} New Red, Green, Blue values
	 */
	private function posterise( $red, $green, $blue){
		$matchFound = false;

		// Average colors to make a monotone
		$monotone = ($red + $green + $blue) / 3;
		
		for($i = 0; $i < $this->numPosterise; $i++){

			if($monotone < ( (255 / $this->numPosterise) * $i) ){
				if( !$matchFound ){
					$monotone = ((255 / $this->numPosterise) * $i);
				}
				$matchFound = true;
			}
		}

		// Return array of RGB values
		return [ $monotone, $monotone, $monotone ];
	}



	/**
	 * Forces a number to be between 0 and 256
	 *
	 * @param {integer} $val Number to inspect
	 *
	 * @return {integer} 
	 */
	private function limitTo256Bit( $val ){
		if($val > 256){
			return 256;
		}
			
		if($val < 0){
			return 0;
		}

		return $val;
	} 



	/**
	 * Takes red, green, blue vals and produces a CSS hex color code
	 *
	 * @param {integer} $red
	 * @param {integer} $green
	 * @param {integer} $blue
	 *
	 * @return {string} Hex color code
	 */
	private function rgbToHexCode( $red, $green, $blue ){
		$hexr = dechex($red);
		$hexg = dechex($green);
		$hexb = dechex($blue);
		
		// Force the leading zeros to make it a CSS color code
		if(strlen($hexr) == 1)
			$hexr = "0".$hexr;
		if(strlen($hexg) == 1)
			$hexg = "0".$hexg;
		if(strlen($hexb) == 1)
			$hexb = "0".$hexb;

		return $hexr . $hexg . $hexb;
	}



	/**
	 * Produces the HTML markup
	 *
	 * @return {string} HTML markup for the page
	 */
	private function writeHTML(){
		$strHTML = '<div id="wrapper" style="width: ' . $this->wrapperWidth . 'px; height: ' . $this->wrapperHeight . 'px;">';
		$licount = 0;
		for($i = 0; $i < sizeof($this->arrPixels); $i++){

			if( $licount >= strlen($this->strWording) ){
				$licount = 0;
			}

			$strHTML .= '<p class=' . $this->arrPixels[$i]['class'] . '>' . substr($this->strWording, $licount, 1);

			$licount++;
		}
		$strHTML .= '</div>';
		return $strHTML;
	}



	/**
	 * Writes the complete source code for a static web page
	 *
	 * @return {string} The produced source code
	 */
	private function writeSourceCode(){
		$sourceCode  = "<!DOCTYPE html>\n<html>\n<head>\n";
		$sourceCode .= "\t<title>" . $this->originalFileName . " converted to " . $this->staticFilename() . "</title>\n";

		// Print last-modified meta tag in format: YYYY-MM-DD@hh:mm:ss TMZ
		$sourceCode .= "\t<meta http-equiv=\"last-modified\" content=\"" . date('Y-m-d@H:i:s e') . "\" />\n";

		$sourceCode .= "\t<style type=\"text/css\">\n";
		$sourceCode .= $this->classifier->writeCSS( $this->pixelWidth );
		$sourceCode .= "\n\t</style>\n</head>\n<body>\n";
		$sourceCode .= "\t<div class=\"wrapper\">\n";
		$sourceCode .= $this->writeHTML();
		$sourceCode .= "\t</div>\n";

		// Debugging line (uncomment to price report)
		//$sourceCode .= "\t<p style=\"color: white\"><strong>Source Pixels:</strong> " . $this->stats['sourcePixels'] . " | <strong>Process Time:</strong> " . $this->stats['time'] . "</p>\n";
		$sourceCode .= "\t</body>\n</html>";
		return $sourceCode;
	}



	/** 
	 * Produces a filename for the HTML file based on the filename of the origin image
	 *
	 * @return {string} .html Filename
	 */
	private function staticFilename(){
		return preg_replace( '/(jpg|png)$/', 'html', $this->originalFileName );
	}



	/**
	 * Writes a single static web page to disk
	 *
	 * @param {string} $destinationFolder Destination folder relative to document root
	 */
	public function writeStaticPage( $destinationFolder = '/generated/' ){

		$report = [ 'success' => true, 
					'bytes_written' => 0, 
					'message' => '', 
					'address' => '' 
				];

		$fileContents = $this->writeSourceCode();

		$report['address'] = $destinationFolder . $this->staticFilename();

		// Attempt to write the file
		$report['bytes_written'] = file_put_contents( $_SERVER['DOCUMENT_ROOT'] . $report['address'], $fileContents );

		if( $report['bytes_written'] == false ){
			$report['success'] = false;
			$report['message'] =  'Failed to write HTML file';
			$report['bytes_written'] = 0;
		} else {
			$report['success'] = true;
			$report['message'] =  'HTML file written';
		}

		return $report;
	}

}
