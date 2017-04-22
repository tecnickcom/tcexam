<?php
//============================================================+
// File name   : tce_latex.php
// Begin       : 2007-05-18
// Last Update : 2009-10-22
//
// Description : Configuration file LaTeX Render Class.
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//
// License:
//    Copyright (C) 2004-2010  Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Configuration file for LaTeX Render Class.
 * @package com.tecnick.tcexam.shared.cfg
 * @author Nicola Asuni
 * @since 2007-05-18
 */

/*
NOTES
------------------------------------------------------------

In Linux do "which latex", "which convert" and "which identify" to find right the paths.

In Windows use the dir /x command to find the short (DOS) path to the executables.

To debug the system comment the following line on /shared/code/tce_functions_errmsg.php :
	//$old_error_handler = set_error_handler("F_error_handler", K_ERROR_TYPES);

The default is to use article.cls for LaTeX which is a common class but it only supports 10,11,12 point font sizes. For smaller (or larger fonts) in the image, install the extsizes package available from CTAN http://ctan.tug.org/. Add these files to a new extsizes directory in usr/share/texmf/tex/latex. Refresh the database using "texhash" command (if using teTeX) or MiKTex Options, Refresh Now (Windows), Then in class.latexrender.php you can change var $_font_size = 10; to var $_font_size = 8;

You can make equation arrays and other code that starts with \begin, by prefacing them with 2 new lines.

Displayed formulae can be rendered using \displaystyle;

Examples of conversions can be found in http://www.mayer.dial.pipex.com/latexrender/latexrender.htm

Although the default size is set, you can resize a formula by using \mbox as in
\mbox{\huge\sqrt{2}} or \mbox{\footnotesize\sqrt{2}}

------------------------------------------------------------
*/

/**
 * Includes paths configuration file.
 */
require_once('../../shared/config/tce_paths.php');

/**
 * Absolute path to images directory.
 */
define('K_LATEX_PATH_PICTURE', K_PATH_CACHE);

/**
 * relative path to images directory.
 */
define('K_LATEX_PATH_PICTURE_HTTPD', K_PATH_URL_CACHE);

/**
 * Path to PDFLATEX (/usr/bin/pdflatex).
 */
define('K_LATEX_PDFLATEX', '/usr/bin/pdflatex');

/**
 * Path to ImageMagick convert (/usr/bin/convert).
 */
define('K_LATEX_PATH_CONVERT', '/usr/bin/convert');

/**
 * Formula density used by ImageMagick (120).
 */
define('K_LATEX_FORMULA_DENSITY', 120);

/**
 * Image width limit in pixels (500).
 */
define('K_LATEX_MAX_WIDTH', 800);

/**
 * Image height limit in pixels (500).
 */
define('K_LATEX_MAX_HEIGHT', 800);

/**
 * Size limit for input string (500).
 */
define('K_LATEX_MAX_LENGHT', 500);

/**
 * Font size (10).
 */
define('K_LATEX_FONT_SIZE', 10);

/**
 * LaTeX class (article).
 */
define('K_LATEX_CLASS', 'article');

/**
 * Filename prefix for chached images (latex_).
 */
define('K_LATEX_IMG_PREFIX', 'latex_');

/**
 * Image format (png).
 */
define('K_LATEX_IMG_FORMAT', 'png');

//============================================================+
// END OF FILE
//============================================================+
