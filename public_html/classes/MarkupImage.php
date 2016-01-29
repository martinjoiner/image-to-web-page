<?php

namespace ImageToWebPage;

Class MarkupImage{

	var $strImageFilename = '';
	var $numPosterise = 0;
	var $strWording = '';
	var $numDesiredWindowWidth = 0;
	var $numPixelWidth = 0;

	var $arrPixels;
	var $arrClasses;
	var $strCSS = '';
	var $strHTML = '';


	/** 
	 Returns a string of CSS
	*/
	function __construct( $strImageFilename, $numPosterise, $strWording, $numDesiredWindowWidth, $numPixelWidth ){

		$this->strImageFilename 		= $strImageFilename;
		$this->numPosterise 			= $numPosterise;
		$this->strWording 				= $strWording;
		$this->numDesiredWindowWidth 	= $numDesiredWindowWidth;
		$this->numPixelWidth 			= $numPixelWidth;

		$absoluteFilename = $_SERVER['DOCUMENT_ROOT'] . '/sourceimages/' . $this->strImageFilename;

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
				$arrPixels[] = $skvPixel;
			}
		}


		$cnt = sizeof( $arrPixels );
		for( $i = 0; $i < $cnt; $i++ ){
			$arrPixels[$i]['class'] = $this->gimmeColClass( $arrPixels[$i]['col'] );
		}

		$this->arrPixels = $arrPixels;

		$this->numWindowWidth = $this->numPixelWidth * ceil($width / $averagerange);
		$this->numWindowHeight = $this->numPixelWidth * ceil($height / $averagerange);

	}




	function getName(){
		$name = preg_replace( '/\.jpg$/', '', $this->strImageFilename );
		$name = preg_replace( '/-/', ' ', $name );
		return trim($name);
	}

	function getFilename( $strExtension = 'html' ){
		$name = preg_replace( '/\.jpg$/', '', $this->strImageFilename );
		return $name . '.' . $strExtension;
	}




	/**
	 Takes a hex colour as a parameter and returns a class name
	 Returns an existing class with that colour if available, if not returns a new class
	*/
	function gimmeColClass( $strCol ){

		if( is_array($this->arrClasses) ){
			foreach( $this->arrClasses as $thisClass ){
				if( $thisClass['col'] == $strCol ){
					//print 'Class matched at ' . $thisClass['class'];
					return $thisClass['class'];
				}
			}
		}

		// If the code has reached this point it means the class does not exist so create it
		$cntClasses = sizeof( $this->arrClasses );
		$this->arrClasses[$cntClasses]['col'] = $strCol;
		$className = 'p' . $cntClasses;
		$this->arrClasses[$cntClasses]['class'] = $className;


//print 'Creating new class at ' . $cntClasses;
		return $className;

	}




	/** 
	 Returns a string of CSS
	*/
	function getCSS(){
		$strCSS = '#wrapper p{
			width: ' . $this->numPixelWidth . 'px;
			height: ' . $this->numPixelWidth . 'px;
			font-size: ' . $this->numPixelWidth . 'px;
		}
		';

		foreach( $this->arrClasses as $thisClass ){
			$strCSS .= '.' . $thisClass['class'] . '{color:#' . $thisClass['col'] . '} ';
		}

		return $strCSS;
	}




	/**
	 Returns a string of markup
	*/
	function getHTML(){
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

}
