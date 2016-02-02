<?php

namespace ImageToWebPage;

use ImageToWebPage\Classifier;

Class Converter{


	/**
	 * The name of the source file
	 */
	private $originalFileName = '';

	/**
	 * Posterise determines how many groups we break colour spectrum into
	 */
	private $numPosterise = 0;

	/**
	 * The message that will be repeated in the web page
	 */
	private $strWording = '';

	var $numDesiredWindowWidth = 0;
	var $numPixelWidth = 0;

	private $arrPixels = [];

	private $classifier;



	public function __construct( $strImageFilename, $numPosterise, $strWording, $numDesiredWindowWidth, $numPixelWidth ){

		$this->classifier = new Classifier();

		$this->originalFileName = end( explode('/', $strImageFilename) );

		$this->numPosterise 			= $numPosterise;
		$this->strWording 				= $strWording;
		$this->numDesiredWindowWidth 	= $numDesiredWindowWidth;
		$this->numPixelWidth 			= $numPixelWidth;

		$absoluteFilename = $strImageFilename;

		list($width, $height, $type, $attr) = getimagesize( $absoluteFilename );

		if( $width > ($this->numDesiredWindowWidth / $this->numPixelWidth) ){
			$averagerange = round($width / ($this->numDesiredWindowWidth / $this->numPixelWidth));
		} else {
			$averagerange = 4;
		}

		$im = imagecreatefromjpeg( $absoluteFilename );

		for($y = 0; $y < $height; $y = $y + $averagerange){

			for($x = 0; $x < $width; $x = $x + $averagerange){	

				unset($r);
				unset($g);
				unset($b);
				
				// Build an array of all the pixels around the target one
				for($y2 = $y; $y2 < $y+$averagerange; $y2++){

					for($x2 = $x; $x2 < $x+$averagerange; $x2++){

						$rgb = imagecolorat($im, $x2, $y2);
						
						$r[] = ($rgb >> 16) & 0xFF;
						$g[] = ($rgb >> 8) & 0xFF;
						$b[] = $rgb & 0xFF;
					}
				}
				
				// Calculate the average colour of that group
				$totalr = 0;
				$totalg = 0;
				$totalb = 0;
				for($i = 0; $i < sizeof($r); $i++){
					$totalr = $totalr + $r[$i];
					$totalg = $totalg + $g[$i];
					$totalb = $totalb + $b[$i];
				}
				$totalaveraged = $averagerange * $averagerange;
				$red = $totalr / $totalaveraged;
				$green = $totalg / $totalaveraged;
				$blue = $totalb / $totalaveraged;
				
				if($red > 256)
					$red = 256;
				if($green > 256)
					$green = 256;
				if($blue > 256)
					$blue = 256;
					
				if($red < 0)
					$red = 0;
				if($green < 0)
					$green = 0;
				if($blue < 0)
					$blue = 0;
					
				$matchfound = FALSE;
				if( $this->numPosterise ){

					$total = ($red + $green + $blue) / 3;
					
					for($i = 0; $i < $this->numPosterise; $i++){

						if($total < ((255 / $this->numPosterise) * $i)){
							if( !$matchfound ){
								$total = ((255 / $this->numPosterise) * $i);
							}
							$matchfound = TRUE;
						}
					}
					$red = $total;
					$green = $total;
					$blue = $total;
				}
				
				$hexr = dechex($red);
				$hexg = dechex($green);
				$hexb = dechex($blue);
				
				if(strlen($hexr) == 1)
					$hexr = "0".$hexr;
				if(strlen($hexg) == 1)
					$hexg = "0".$hexg;
				if(strlen($hexb) == 1)
					$hexb = "0".$hexb;
				
				$skvPixel['col'] = $hexr . $hexg . $hexb;
				$this->arrPixels[] = $skvPixel;
			}
		}


		$cnt = sizeof( $this->arrPixels );
		for( $i = 0; $i < $cnt; $i++ ){
			$this->arrPixels[$i]['class'] = $this->classifier->colorClass( $this->arrPixels[$i]['col'] );
		}

		$this->numWindowWidth = $this->numPixelWidth * ceil($width / $averagerange);
		$this->numWindowHeight = $this->numPixelWidth * ceil($height / $averagerange);

	}




	/**
	 * Produces the HTML markup
	 *
	 * @returns {string} HTML markup for the page
	 */
	private function writeHTML(){
		$strHTML = '<div id="wrapper" style="width: ' . $this->numWindowWidth . 'px; height: ' . $this->numWindowHeight . 'px;">';
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
		$sourceCode = '<!DOCTYPE html><html><head><style type="text/css">';
		$sourceCode .= $this->classifier->writeCSS();
		$sourceCode .= '</style></head><body><div class="wrapper">';
		$sourceCode .= $this->writeHTML();
		$sourceCode .= '</div></body></html>';
		return $sourceCode;
	}



	/** 
	 * Produces a filename for the HTML file based on the origin image
	 *
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
		$report = [ 'success'=>true, 'message'=>'', 'address'=>'' ];

		$fileContents = $this->writeSourceCode();

		$report['address'] = $destinationFolder . $this->staticFilename();

		$report['success'] = file_put_contents( $_SERVER['DOCUMENT_ROOT'] . $report['address'], $fileContents );

		if( $report['success'] ){
			$report['message'] =  'HTML file written';
		} else {
			$report['message'] =  'Failed to write HTML file';
		}

		return $report;
	}

}
