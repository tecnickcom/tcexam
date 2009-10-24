<?php
//============================================================+
// File name   : tce_latexrender.php
// Begin       : 2007-05-18
// Last Update : 2009-10-24
// Author      : Nicola Asuni
//
// Description : 
// ------------------------------------------------------------
// This is a PHP5 class for generating images from LaTeX Formulas.
// This class is based on the following:
// LaTeX Rendering Class v0.8 (Licensed under GPL 2)
// Copyright (C) 2003 Benjamin Zeiss <zeiss@math.uni-goettingen.de>
// Currently the project is maintained by Steve Mayer.
// Please check the following Website to obtain the original
// source code: http://www.mayer.dial.pipex.com/tex.htm
// ------------------------------------------------------------
//
// License: 
//    Copyright (C) 2004-2009  Nicola Asuni - Tecnick.com S.r.l.
//    
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU Affero General Public License as
//    published by the Free Software Foundation, either version 3 of the
//    License, or (at your option) any later version.
//    
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU Affero General Public License for more details.
//    
//    You should have received a copy of the GNU Affero General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
//     
//    Additionally, you can't remove the original TCExam logo, copyrights statements
//    and links to Tecnick.com and TCExam websites.
//    
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * LaTeX Rendering Class.
 * @package com.tecnick.latex
 */

/**
 * LaTeX Rendering Class.
 * @package com.tecnick.latex
 */

/**
 * Includes configuration file.
 */
require_once('../../shared/config/tce_latex.php');

/**
 * This is a PHP5 class for generating images from LaTeX Formulas.
 * This class is based on the following:
 * LaTeX Rendering Class v0.8 (Licensed under GPL 2)
 * Copyright (C) 2003 Benjamin Zeiss <zeiss@math.uni-goettingen.de>
 * Currently the project is maintained by Steve Mayer.
 * Please check the following Website to obtain the original
 * source code: http://www.mayer.dial.pipex.com/tex.htm
 * @name LaTeX Render
 * @package com.tecnick.latex
 * @abstract Class for generating images from LaTeX Formulas.
 * @author Nicola Asuni
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class LatexRender {

	//  ---------- Variable Definitions ---------- * ---------- * ----------

	/**
	 * @var Absolute path to images directory.
	 * @access protected
	 */
	protected $picture_path = K_LATEX_PATH_PICTURE;
	
	/**
	 * @var relative path to images directory.
	 * @access protected
	 */
	protected $picture_path_httpd = K_LATEX_PATH_PICTURE_HTTPD;
	
	/**
	 * @var Path to temporary directory.
	 * @access protected
	 */
	protected $tmp_dir = K_LATEX_TMP_DIR;
	
	/**
	 * @var Path to LATEX.
	 * @access protected
	 */
	protected $latex_path = K_LATEX_PATH_LATEX;
	
	/**
	 * @var Path to DVIPS.
	 * @access protected
	 */
	protected $dvips_path = K_LATEX_PATH_DVIPS;
	
	/**
	 * @var Path to ImageMagick convert.
	 * @access protected
	 */
	protected $convert_path = K_LATEX_PATH_CONVERT;
	
	/**
	 * @var Path to ImageMagick identify.
	 * @access protected
	 */
	protected $identify_path = K_LATEX_PATH_IDENTIFY;
	
	/**
	 * @var Formula density (used by ImageMagick)
	 * @access protected
	 */
	protected $formula_density = K_LATEX_FORMULA_DENSITY;
	
	/**
	 * @var Image width limit in pixels.
	 * @access protected
	 */
	protected $width_limit = K_LATEX_MAX_WIDTH;
	
	/**
	 * @var Image height limit in pixels.
	 * @access protected
	 */
	protected $height_limit = K_LATEX_MAX_HEIGHT;
	
	/**
	 * @var Size limit for input string.
	 * @access protected
	 */
	protected $string_length_limit = K_LATEX_MAX_LENGHT;
	
	/**
	 * @var Font size.
	 * @access protected
	 */
	protected $font_size = K_LATEX_FONT_SIZE;
	
	/**
	 * @var LaTeX class.
	 * @access protected
	*/
	protected $latexclass = K_LATEX_CLASS;
	
	/**
	 * @var Filename prefix for chached images.
	 * @access protected
	 */
	protected $img_prefix = K_LATEX_IMG_PREFIX;
	
	/**
	 * @var Image format (default = PNG).
	 * @access protected
	 */
	protected $image_format = K_LATEX_IMG_FORMAT;
	
	/**
	 * @var list of unauthorized LaTeX commands.
	 * @access protected
	 */
	protected $latex_tags_blacklist = array('include', 'def', 'command', 'loop', 'repeat', 'open', 'toks', 'output', 'input', 'catcode', 'name', '^^', '\every', '\errhelp', '\errorstopmode', '\scrollmode', '\nonstopmode', '\batchmode', '\read', '\write', 'csname', '\newhelp', '\uppercase', '\lowercase', '\relax', '\aftergroup', '\afterassignment', '\expandafter', '\noexpand', '\special');
	
	// ------ private ------
	
	/**
	 * @var Error code.
	 * @access private
	 */
	private $errorcode = 0;
	
	/**
	 * @var Temporary filename.
	 * @access private
	*/
	private $tmp_filename = '';
	
	/**
	 * @var Latex formula.
	 * @access private
	 */
	private $latex_formula = '';
	
	/**
	 * @var Image width.
	 * @access private
	 */
	private $img_width = 0;
	
	/**
	 * @var Image height.
	 * @access private
	 */
	private $img_height = 0;


	//  ---------- constructor / destructor functions ---------- * ---------- * ----------


    /**
     * Class Constructor.
     */
    public function __construct() {
		$this->tmp_filename = md5(rand());
    }
	
	/**
	 * Default destructor.
	 */
	public function __destruct() {
	}


	// ---------- public functions ---------- * ---------- * ---------- * ----------

	// ---------- set functions ----------

	/**
	 * Set the absolute path to images directory.
	 * @param string $picture_path absolute path to images directory.
	 */
	public function setPathToPicturesDir($picture_path) {
		$this->picture_path = $picture_path;
	}
	
	/**
	 * Set relative path to images directory.
	 * @param string $picture_path_httpd relative path to images directory.
	 */
	public function setPathToPicturesDirHttpd($picture_path_httpd) {
		$this->picture_path_httpd = $picture_path_httpd;
	}
	
	/**
	 * Set path to temporary directory.
	 * @param string $tmp_dir path to temporary directory.
	 */
	public function setPathToTempDir($tmp_dir) {
		$this->tmp_dir = $tmp_dir;
	}
	
	/**
	 * Set path to LATEX.
	 * @param string $latex_path path to LATEX.
	 */
	public function setPathToLatex($latex_path) {
		$this->latex_path = $latex_path;
	}
	
	/**
	 * Set path to DVIPS.
	 * @param string $dvips_path path to DVIPS.
	 */
	public function setPathToDvips($dvips_path) {
		$this->dvips_path = $dvips_path;
	}
	
	/**
	 * Set path to ImageMagick convert.
	 * @param string $convert_path path to ImageMagick convert.
	 */
	public function setPathToImageMagicConvert($convert_path) {
		$this->convert_path = $convert_path;
	}
	
	/**
	 * Set path to ImageMagick identify.
	 * @param string $identify_path path to ImageMagick identify.
	 */
	public function setPathToImageMagicIdentify($identify_path) {
		$this->identify_path = $identify_path;
	}
	
	/**
	 * Set formula density (used by ImageMagick)
	 * @param int $formula_density formula density.
	 */
	public function setFormulaDensity($formula_density) {
		$this->formula_density = $formula_density;
	}
	
	/**
	 * Set image width limit in pixels.
	 * @param string $width_limit Max image width in pixels.
	 */
	public function setMaxWidth($width_limit) {
		$this->width_limit = $width_limit;
	}
	
	/**
	 * Set image height limit in pixels.
	 * @param string $height_limit Max image height in pixels.
	 */
	public function setMaxHeight($height_limit) {
		$this->height_limit = $height_limit;
	}
	
	/**
	 * Set size limit for input string.
	 * @param string $string_length_limit max lenght for LaTeX string.
	 */
	public function setMaxLenght($string_length_limit) {
		$this->string_length_limit = $string_length_limit;
	}
	
	/**
	 * Set font size.
	 * @param int $font_size font size in points.
	 */
	public function setFontSize($font_size) {
		$this->font_size = $font_size;
	}
	
	/**
	 * Set LaTeX class.
	 * Install extarticle class if you wish to have smaller font sizes.
	 * @param string $latexclass LaTeX class.
	 */
	public function setLatexClass($latexclass) {
		$this->latexclass = $latexclass; 
	}
	
	/**
	 * Set filename prefix for chached images.
	 * @param string $img_prefix filename prefix.
	 */
	public function setFilenamePrefix($img_prefix) {
		$this->img_prefix = $img_prefix;
	}
	
	/**
	 * Set the image format (default = PNG).
	 * @param string $image_format image format(e.g.: png).
	 */
	public function setImageFormat($image_format) {
		$this->image_format = $image_format;
	}
	
	/**
	 * Set the list of unauthorized LaTeX commands.
	 * @param array $latex_tags_blacklist array of blacklisted commands.
	 */
	public function setLatexBlackList($latex_tags_blacklist) {
		$this->latex_tags_blacklist = $latex_tags_blacklist;
	}

	// ---------- get functions ----------

	/**
	 * Tries to match the LaTeX Formula given as argument against the
	 * formula cache. If the picture has not been rendered before, it'll
	 * try to render the formula and drop it in the picture cache directory.
	 *
	 * @param string $latex_formula formula in LaTeX format
	 * @returns the webserver based URL to a picture which contains the
	 * requested LaTeX formula. If anything fails, the result value is false.
	 */
	public function getFormulaURL($latex_formula) {
		
		// circumvent certain security functions of web-software which
		// is pretty pointless right here
		$latex_formula = preg_replace("/&gt;/i", '>', $latex_formula);
		$latex_formula = preg_replace("/&lt;/i", '<', $latex_formula);
		
		$filename = $this->getFilename($latex_formula);
		$full_path_filename = $this->picture_path.''.$filename;
		
		if (is_file($full_path_filename)) {
			return $this->picture_path_httpd.''.$filename;
		} else {
			// security filter: reject too long formulas
			if (strlen($latex_formula) > $this->string_length_limit) {
				$this->errorcode = 1;
				return false;
			}
			// security filter: try to match against LaTeX-Tags Blacklist
			for ($i=0; $i<sizeof($this->latex_tags_blacklist); $i++) {
				if (stristr($latex_formula,$this->latex_tags_blacklist[$i])) {
					$this->errorcode = 2;
					return false;
				}
			}
			// security checks assume correct formula, let's render it
			if ($this->renderLatex($latex_formula)) {
				return $this->picture_path_httpd.''.$filename;
			} else {
				return false;
			}
		}
	}
	
	/**
	 * Returns Image width
	 * @returns image width in pixels.
	 */
	public function getImageWidth() {
		return $this->img_width;
	}
	
	/**
	 * Returns Image height
	 * @returns image height in pixels.
	 */
	public function getImageHeight() {
		return $this->img_height;
	}
	
	/**
	 * Returns the error code
	 * @returns int error code.
	 */
	public function getErrorCode() {
		return $this->errorcode;
	}
	


	//  ---------- private functions ---------- * ---------- * ---------- * ----------


	/**
	 * Wraps a minimalistic LaTeX document around the formula and returns a string
	 * containing the whole document as string.
	 * Customize if you want other fonts for example.
	 *
	 * @param string $latex_formula formula in LaTeX format
	 * @returns minimalistic LaTeX document containing the given formula
	 */
	private function getFilename($latex_formula) {
		$filename = $this->img_prefix.md5($latex_formula).'.'.$this->image_format;
		return $filename;
	}
	
	/**
	 * Wraps a minimalistic LaTeX document around the formula and returns a string
	 * containing the whole document as string.
	 * Customize if you want other fonts for example.
	 *
	 * @param string $latex_formula formula in LaTeX format
	 * @returns minimalistic LaTeX document containing the given formula
	 */
	private function wrapFormula($latex_formula) {
		$string  = '\documentclass['.$this->font_size.'pt]{'.$this->latexclass.'}'."\n";
		$string .= '\usepackage[latin1]{inputenc}'."\n";
		$string .= '\usepackage{amsmath}'."\n";
		$string .= '\usepackage{amsfonts}'."\n";
		$string .= '\usepackage{amssymb}'."\n";
		$string .= '\pagestyle{empty}'."\n";
		$string .= '\begin{document}'."\n";
		$string .= '$'.$latex_formula.'$'."\n";
		$string .= '\end{document}'."\n";
		return $string;
	}
	
	/**
	 * Removes temporary files.
	 * @param string $current_dir current directory.
	 * @param int $error_code error code.
	 */
	private function cleanTemporaryDirectory($current_dir, $error_code=0) {
		chdir($this->tmp_dir);
		unlink($this->tmp_dir.''.$this->tmp_filename.'.tex');
		unlink($this->tmp_dir.''.$this->tmp_filename.'.aux');
		unlink($this->tmp_dir.''.$this->tmp_filename.'.log');
		unlink($this->tmp_dir.''.$this->tmp_filename.'.dvi');
		unlink($this->tmp_dir.''.$this->tmp_filename.'.ps');
		unlink($this->tmp_dir.''.$this->tmp_filename.'.'.$this->image_format);
		chdir($current_dir);
		$this->errorcode = $error_code;
	}
	
	/**
	 * Check the dimensions of a picture file using 'identify' of the
	 * ImageMagick tools.
	 *
	 * @param string $filename path to a picture
	 * @returns array containing the picture dimensions
	 */
	private function checkImageDimensions($filename) {
		$output = exec($this->identify_path." ".$filename);
		if (empty($output)) {
			return false;
		}
		$result = explode(' ',$output);
		$dim = explode('x',$result[2]);
		$this->img_width = $dim[0];
		$this->img_height = $dim[1];
		if (($this->img_width > $this->width_limit) OR ($this->img_height > $this->height_limit)) {
			return false;
		}
		return true;
	}

	/**
	 * Renders a LaTeX formula by the using the following method:
	 *  - write the formula into a wrapped tex-file in a temporary directory
	 *    and change to it
	 *  - Create a DVI file using latex (tetex)
	 *  - Convert DVI file to Postscript (PS) using dvips (tetex)
	 *  - convert, trim and add transparancy by using 'convert' from the
	 *    ImageMagick package.
	 *  - Save the resulting image to the picture cache directory using an
	 *    md5 hash as filename. Already rendered formulas can be found directly
	 *    this way.
	 *
	 * @param string $latex_formula LaTeX formula
	 * @returns true if the picture has been successfully saved to the picture
	 *          cache directory
	 */
	private function renderLatex($latex_formula) {
		$latex_document = $this->wrapFormula($latex_formula);
		
		$current_dir = getcwd();
		chdir($this->tmp_dir);
		
		// create temporary latex file
		$fp = fopen($this->tmp_dir.''.$this->tmp_filename.'.tex', 'a+');
		fputs($fp,$latex_document);
		fclose($fp);
		
		// create temporary DVI file
		$command = $this->latex_path.' --interaction=nonstopmode '.$this->tmp_filename.'.tex';
		$status_code = exec($command);
		if (!$status_code) { 
			$this->cleanTemporaryDirectory($current_dir, 4);
			return false;
		}
		
		// convert DVI file to postscript using DVIPS
		$command = $this->dvips_path.' -E '.$this->tmp_filename.'.dvi'.' -o '.$this->tmp_filename.'.ps';
		$status_code = exec($command);
		
		// ImageMagick convert PS to image and trim picture
		$command = $this->convert_path.' -density '.$this->formula_density.' -background "#FFFFFF" -depth 8 '.$this->tmp_filename.'.ps '.$this->tmp_filename.'.'.$this->image_format;
		$status_code = exec($command);
		
		// check picture dimensions
		if(!$this->checkImageDimensions($this->tmp_filename.'.'.$this->image_format)) {
			$this->cleanTemporaryDirectory($current_dir, 7);
			return false;
		}
		
		// copy temporary formula file to cached formula directory
		$filename = $this->getFilename($latex_formula);
		$status_code = copy($this->tmp_filename.'.'.$this->image_format, $filename);
		
		if (!$status_code) {
			$this->cleanTemporaryDirectory($current_dir, 8);
			return false;
		}
		
		$this->cleanTemporaryDirectory($current_dir, 0);
		
		return true;
	}
	
} // end of class
?>
