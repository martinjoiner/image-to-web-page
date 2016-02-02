<?php

namespace ImageToWebPage;


/**
 * The name of this class is a bit of a pun. It builds a library of CSS classes. 
 */
class Classifier{


	/**
	 * All the classes that define colour
	 *
	 * @type {array} 
	 */
	private $arrClasses = [];


	/**
	 * Shortens six-digit hex colors
	 *
	 * @param {string} $color 
	 * 
	 * @return {string} Hex color in the shortest form it can be 
	 */
	private function shortenColor( $color ){
		$hex_char = '[a-f0-9]';

		return preg_replace("/^($hex_char)\\1($hex_char)\\2($hex_char)\\3\z/i", '\1\2\3', $color);
	}


	/**
	 * Takes a hex colour as a parameter and returns a class name
	 * If a suitable class already exists it will reuse it, if not a new class is created
	 *
	 * @param {string} $longColor A hexidecimal colour code
	 *
	 * @return {string} The class name
	 */
	public function colorClass( $longColor ){

		// Find the short version of the color if possible
		$color = $this->shortenColor( $longColor );

		// Iterate over classes, searching for an existing one that will suffice
		foreach( $this->arrClasses as $thisClass ){
			if( $thisClass['color'] == $color ){
				return $thisClass['class'];
			}
		}
		
		// If at this point, the class does not exist so...

		// Generate a name by simply prepending 'p' to the total number
		$className = 'p' . sizeof( $this->arrClasses );

		// Create new class
		$this->arrClasses[] = [ 'color'=>$color, 'class'=>$className ];

		return $className;
	}


	/** 
	 * Produces the CSS code
	 *
	 * @return {string} CSS code
	 */
	public function writeCSS( $pixelWidth = 0 ){

		$strCSS = '
			body{
				background-color: #000;
				font-family: Arial, sans-serif;
			}
			#wrapper{
				margin: 20px auto;
			}
	 
			#wrapper p{
				display: block;
				float: left;
				margin: 0;
				padding: 0;

				font-style: normal;
				font-weight: bold;
				text-transform: uppercase;

				width: ' . $pixelWidth . 'px;
				height: ' . $pixelWidth . 'px;
				font-size: ' . $pixelWidth . 'px;
			}
		';

		foreach( $this->arrClasses as $thisClass ){
			$strCSS .= '.' . $thisClass['class'] . '{color:#' . $thisClass['color'] . '} ';
		}

		return $strCSS;
	}

}
