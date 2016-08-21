<?php
//============================================================+
// File name   : tce_pdf.php
// Begin       : 2004-06-11
// Last Update : 2013-03-17
//
// Description : Configuration file for pdf documents.
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
//    Copyright (C) 2004-2013  Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Configuration file for TCPDF.
 * @author Nicola Asuni
 * @package com.tecnick.tcexam.shared.cfg
 * @since 2004-06-11
 */

// PLEASE SET THE FOLLOWING CONSTANTS:

/**
 * Header title.
 */
define('PDF_HEADER_TITLE', "School name");

/**
 * Header description string.
 */
define('PDF_HEADER_STRING', "first row\nsecond row\nthird row");

/**
 * Image logo.
 */
define('PDF_HEADER_LOGO', 'logo_example.png');

/**
 * Header logo image width [mm].
 */
define('PDF_HEADER_LOGO_WIDTH', 20);

/**
 * Height of area for offline user answer.
 */
define('PDF_TEXTANSWER_HEIGHT', 40);

/**
 * Page format.
 */
define('PDF_PAGE_FORMAT', 'A4');

/**
 * Page orientation (P=portrait, L=landscape).
 */
define('PDF_PAGE_ORIENTATION', 'P');

/**
 * Document creator.
 */
define('PDF_CREATOR', 'TCExam 12');

/**
 * Document author.
 */
define('PDF_AUTHOR', 'TCExam 12');

/**
 * Document unit of measure [pt=point, mm=millimeter, cm=centimeter, in=inch].
 */
define('PDF_UNIT', 'mm');

/**
 * Header margin.
 */
define('PDF_MARGIN_HEADER', 5);

/**
 * Footer margin.
 */
define('PDF_MARGIN_FOOTER', 10);

/**
 * Top margin.
 */
define('PDF_MARGIN_TOP', 27);

/**
 * Bottom margin.
 */
define('PDF_MARGIN_BOTTOM', 30);

/**
 * Left margin.
 */
define('PDF_MARGIN_LEFT', 15);

/**
 * Right margin.
 */
define('PDF_MARGIN_RIGHT', 15);

/**
 * Main font name.
 */
define('PDF_FONT_NAME_MAIN', 'helvetica');

/**
 * Main font size.
 */
define('PDF_FONT_SIZE_MAIN', 9);

/**
 * Data font name.
 */
define('PDF_FONT_NAME_DATA', 'helvetica');

/**
 * Data font size.
 */
define('PDF_FONT_SIZE_DATA', 7);

/**
 * default monospaced font name
 */
define('PDF_FONT_MONOSPACED', 'courier');

/**
 * ratio used to adjust the conversion of pixels to user units
 */
define('PDF_IMAGE_SCALE_RATIO', 1.25);

/**
 * magnification factor for titles
 */
define('HEAD_MAGNIFICATION', 1.1);

/**
 * height of cell repect font height
 */
define('K_CELL_HEIGHT_RATIO', 1.25);

/**
 * title magnification respect main font size
 */
define('K_TITLE_MAGNIFICATION', 1.3);

/**
 * reduction factor for small font
 */
define('K_SMALL_RATIO', 2/3);

/**
 * set to true to enable the special procedure used to avoid the overlappind of symbols on Thai language
 */
define('K_THAI_TOPCHARS', false);

/**
 * if true allows to call TCPDF methods using HTML syntax
 * IMPORTANT: For security reason, disable this feature if you are printing user HTML content.
 */
define('K_TCPDF_CALLS_IN_HTML', false);

/**
 * if true adn PHP version is greater than 5, then the Error() method throw new exception instead of terminating the execution.
 */
define('K_TCPDF_THROW_EXCEPTION_ERROR', false);


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

/**
 * Do not change the value of this define.
 */
define('K_TCPDF_EXTERNAL_CONFIG', true);

//============================================================+
// END OF FILE
//============================================================+
