<?php
//============================================================+
// File name   : tce_pdf.php
// Begin       : 2004-06-11
// Last Update : 2009-11-10
// 
// Description : Configuration file for pdf documents.
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com S.r.l.
//               Via della Pace, 11
//               09044 Quartucciu (CA)
//               ITALY
//               www.tecnick.com
//               info@tecnick.com
//
// License: 
//    Copyright (C) 2004-2010  Nicola Asuni - Tecnick.com S.r.l.
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
 * Configuration file for TCPDF.
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package com.tecnick.tcexam.shared.cfg
 * @link http://tcpdf.sourceforge.net
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * @since 2004-06-11
 */

// PLEASE SET THE FOLLOWING CONSTANTS:

/**
 * Header title.
 */
define ('PDF_HEADER_TITLE', "School name");

/**
 * Header description string.
 */
define ('PDF_HEADER_STRING', "first row\nsecond row\nthird row");

/**
 * Image logo.
 */
define ('PDF_HEADER_LOGO', 'logo_example.png');

/**
 * Header logo image width [mm].
 */
define ('PDF_HEADER_LOGO_WIDTH', 20);

/**
 * Height of area for offline user answer.
 */
define ('PDF_TEXTANSWER_HEIGHT', 40);

/**
 * Page format.
 */
define ('PDF_PAGE_FORMAT', 'A4');

/**
 * Page orientation (P=portrait, L=landscape).
 */
define ('PDF_PAGE_ORIENTATION', 'P');

/**
 * Document creator.
 */
define ('PDF_CREATOR', 'TCExam 9');

/**
 * Document author.
 */
define ('PDF_AUTHOR', 'TCExam 9');

/**
 * Document unit of measure [pt=point, mm=millimeter, cm=centimeter, in=inch].
 */
define ('PDF_UNIT', 'mm');

/**
 * Header margin.
 */
define ('PDF_MARGIN_HEADER', 5);

/**
 * Footer margin.
 */
define ('PDF_MARGIN_FOOTER', 10);

/**
 * Top margin.
 */
define ('PDF_MARGIN_TOP', 27);

/**
 * Bottom margin.
 */
define ('PDF_MARGIN_BOTTOM', 30);

/**
 * Left margin.
 */
define ('PDF_MARGIN_LEFT', 15);

/**
 * Right margin.
 */
define ('PDF_MARGIN_RIGHT', 15);

/**
 * Main font name.
 */
define ('PDF_FONT_NAME_MAIN', 'helvetica');

/**
 * Main font size.
 */
define ('PDF_FONT_SIZE_MAIN', 9);

/**
 * Data font name.
 */
define ('PDF_FONT_NAME_DATA', 'helvetica');

/**
 * Data font size.
 */
define ('PDF_FONT_SIZE_DATA', 7);

/**
 * Ratio used to adjust the conversion of pixels to user units.
 */
define ('PDF_IMAGE_SCALE_RATIO', 2);

/**
 * Magnification factor for titles.
 */
define('HEAD_MAGNIFICATION', 1.1);

/**
 * Height of cell repect font height.
 */
define('K_CELL_HEIGHT_RATIO', 1.25);

/**
 * Title magnification respect main font size.
 */
define('K_TITLE_MAGNIFICATION', 1.3);

/**
 * Reduction factor for small font.
 */
define('K_SMALL_RATIO', 2/3);


// --- DATA FOR DIGITAL SIGNATURE ----------------------------------------------


/**
 * If true digitally sign PDF documents.
 */
define('K_DIGSIG_ENABLE', false);

/**
 * Signing certificate (string or filename prefixed with 'file://').
 */
define('K_DIGSIG_CERTIFICATE', 'file://../../shared/config/tcpdf.crt');

/**
 * Private key (string or filename prefixed with 'file://').
 */
define('K_DIGSIG_PRIVATE_KEY', 'file://../../shared/config/tcpdf.crt');

/**
 * Password for private key.
 */
define('K_DIGSIG_PASSWORD', 'tcpdfdemo');

/**
 * Name of a file containing a bunch of extra certificates to include in the signature which can for example be used to help the recipient to verify the certificate that you used.
 */
define('K_DIGSIG_EXTRA_CERTS', '');

/**
 * The access permissions granted for this document.
 * Valid values shall be:
 * 1 = No changes to the document shall be permitted; any change to the document shall invalidate the signature;
 * 2 = Permitted changes shall be filling in forms, instantiating page templates, and signing; other changes shall invalidate the signature;
 * 3 = Permitted changes shall be the same as for 2, as well as annotation creation, deletion, and modification; other changes shall invalidate the signature.
 */
define('K_DIGSIG_CERT_TYPE', '1');

/**
 * The name of the person or authority signing the document.
 */
define('K_DIGSIG_NAME', 'TCEXAM');

/**
 * The CPU host name or physical location of the signing.
 */
define('K_DIGSIG_LOCATION', 'Office');

/**
 * The reason for the signing, such as ( I agree ...).
 */
define('K_DIGSIG_REASON', 'Certification');

/**
 * Information provided by the signer to enable a recipient to contact the signer to verify the signature; for example, a phone number or email address.
 */
define('K_DIGSIG_CONTACT', 'you@example.com');

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
