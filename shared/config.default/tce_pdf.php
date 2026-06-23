<?php

//============================================================+
// File name   : tce_pdf.php
// Begin       : 2004-06-11
// Last Update : 2024-03-18
//
// Description : Configuration file for pdf documents.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

/**
 * @file
 * Configuration file for PDF documents (tc-lib-pdf).
 * @author Nicola Asuni
 * @package com.tecnick.tcexam.shared.cfg
 * @since 2004-06-11
 */

// PLEASE SET THE FOLLOWING CONSTANTS:

/**
 * Header title.
 */
define('PDF_HEADER_TITLE', 'School name');

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
 * DejaVu Sans is used by default because it carries a broad Unicode glyph set
 * (Latin with diacritics, Cyrillic, Greek, ...) so exported PDFs render the
 * non-Latin languages TCExam supports. The 14 standard PDF fonts (e.g. helvetica)
 * only cover Latin-1. For CJK content set a cid0* font here.
 */
define('PDF_FONT_NAME_MAIN', 'dejavusans');

/**
 * Main font size.
 */
define('PDF_FONT_SIZE_MAIN', 9);

/**
 * Data font name.
 * See PDF_FONT_NAME_MAIN: DejaVu Sans is used so the test/answer content (which may
 * be in any of the supported languages) renders with the correct glyphs.
 */
define('PDF_FONT_NAME_DATA', 'dejavusans');

/**
 * Data font size.
 */
define('PDF_FONT_SIZE_DATA', 7);

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
// tc-lib-pdf engine options (PDF report/export documents)
//============================================================+

/**
 * If true, treat input text as Unicode (UTF-8). Disable only for legacy single-byte output.
 */
define('K_PDF_UNICODE', true);

/**
 * If true, subset embedded fonts (smaller files, slightly slower generation).
 */
define('K_PDF_SUBSET_FONT', false);

/**
 * If true, compress the generated PDF stream.
 */
define('K_PDF_COMPRESS', true);

/**
 * PDF compliance mode. One of: '' (none), 'pdfa1', 'pdfa2', 'pdfa3', 'pdfua1', 'pdfua2'.
 */
define('K_PDF_MODE', '');

//============================================================+
// tc-lib-file access security (configurable, mirrors tc-lib-pdf fileOptions)
//============================================================+

/**
 * Serialized list of trusted local directory prefixes the PDF engine may read
 * (images, fonts, cache, SVG temp files). Restricts file:// reads to these paths
 * to prevent path traversal / arbitrary file disclosure.
 */
define(
    'K_PDF_ALLOWED_PATHS',
    serialize(array_values(array_filter([
        realpath(K_PATH_MAIN . 'images'),
        realpath(K_PATH_MAIN . 'cache'),
        realpath(K_PATH_MAIN . 'vendor/tecnickcom/tc-lib-pdf-font/target/fonts'),
        defined('K_PATH_FONTS') ? realpath(K_PATH_FONTS) : false,
        realpath(sys_get_temp_dir()),
    ]))),
);

/**
 * Serialized whitelist of remote host names the PDF engine may fetch over HTTP(S).
 * Remote URL loading is DISABLED by default (empty list) to prevent SSRF; add hosts
 * (e.g. ['cdn.example.com']) only if your templates must load remote resources.
 */
define('K_PDF_ALLOWED_HOSTS', serialize([]));

/**
 * Maximum size, in bytes, accepted for a remote download (default 50 MiB).
 */
define('K_PDF_MAX_REMOTE_SIZE', 52_428_800);
