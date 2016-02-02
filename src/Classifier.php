<?php

namespace ImageToWebPage;

class Classifier{


	/**
	 * All the classes that define colour
	 *
	 * @type {array} 
	 */
	private $arrClasses;


	/**
	 * Takes a hex colour as a parameter and returns a class name
	 * If a suitable class already exists it will reuse it, if not a new class is created
	 *
	 * @param {string} $strCol A hexidecimal colour code
	 *
	 * @return {string} The class name
	 */
	public function colorClass( $strCol ){

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

		return $className;
	}


	/** 
	 * Produces the CSS code
	 *
	 * @returns {string} CSS code
	 */
	public function writeCSS(){

		$strCSS = '
			body{
				background-color: #000;
				font-family: Arial, sans-serif;
			}
			#wrapper{
				margin: 20px auto;
			}
	 
			#wrapper p{
				font-style: normal;
				float: left;
				font-weight: bold;
				width: 10px;
				height: 10px;
				font-size: 10px;
				margin: 0;
				text-transform: uppercase;
			}

			#wrapper p{
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

}
