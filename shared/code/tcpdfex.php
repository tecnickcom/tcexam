<?php
//============================================================+
// File name   : tcpdfex.php
// Begin       : 2010-12-06
// Last Update : 2010-12-06
// Author      : Nicola Asuni - Tecnick.com S.r.l - Via Della Pace, 11 - 09044 - Quartucciu (CA) - ITALY - www.tecnick.com - info@tecnick.com
// License     : http://www.tecnick.com/pagefiles/tcpdf/LICENSE.TXT GNU-LGPLv3 + YOU CAN'T REMOVE ANY TCPDF COPYRIGHT NOTICE OR LINK FROM THE GENERATED PDF DOCUMENTS.
// -------------------------------------------------------------------
// Copyright (C) 2002-2010  Nicola Asuni - Tecnick.com S.r.l.
//
// This file is part of TCPDF software library.
//
// TCPDF is free software: you can redistribute it and/or modify it
// under the terms of the GNU Lesser General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version. Additionally,
// YOU CAN'T REMOVE ANY TCPDF COPYRIGHT NOTICE OR LINK FROM THE
// GENERATED PDF DOCUMENTS.
//
// TCPDF is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// See the GNU Lesser General Public License for more details.
//
// You should have received a copy of the License
// along with TCPDF. If not, see
// <http://www.tecnick.com/pagefiles/tcpdf/LICENSE.TXT>.
//
// See LICENSE.TXT file for more information.
// -------------------------------------------------------------------
//
// Description : This is a PHP class for generating PDF documents without
//               requiring external extensions.
//============================================================+

/**
 * @file
 * This is an extension of the TCPDF class for creating PDF document.
 * This extension allows you to define custom Header and Footer for PDF documents.
 * @package com.tecnick.tcpdf
 * @author Nicola Asuni
 */

require(dirname(__FILE__).'/tcpdf.php');

/**
 * @class TCPDFEX
 * This is an extension of the TCPDF class for creating PDF document.
 * This extension allows you to define custom Header and Footer for PDF documents.
 * @package com.tecnick.tcpdf
 * @author Nicola Asuni - info@tecnick.com
 */
class TCPDFEX extends TCPDF {

	/**
	 * URL link that points back to TCExam website.
	 * @protected
	 */
	protected $tcexam_backlink = '';

	/**
	 * Set an URL link that points back to TCExam website (this will be printed as QR-Code on header).
	 * @param $link URL link.
	 * @public
	 */
	public function setTCExamBackLink($link) {
		$this->tcexam_backlink = $link;
	}

	/**
	 * This method is used to render the page header and overrides the original Header() method on TCPDF.
	 * @public
	 */
	public function Header() {
		if (!empty($this->tcexam_backlink)) {
			$px = $this->x;
			$py = $this->y;
			// set style for barcode
			$style = array(
				'border' => false,
				'vpadding' => 0,
				'hpadding' => 0,
				'fgcolor' => array(0,0,0),
				'bgcolor' => false,
				'module_width' => 1,
				'module_height' => 1
			);
			// QRCODE,H : QR-CODE Best error correction
			$w = (PDF_MARGIN_TOP - PDF_MARGIN_HEADER - 2);
			$y = (PDF_MARGIN_HEADER);
			if ($this->rtl) {
				$x = PDF_MARGIN_LEFT + $w;
			} else {
				$x = $this->w - PDF_MARGIN_RIGHT - $w;
			}
			// write QR-Code on header
			$this->write2DBarcode($this->tcexam_backlink, 'QRCODE,M', $x, $y, $w, $w, $style, 'N');
			$this->x = $px;
			$this->y = $py;
		}
		parent::Header();
	}

	/**
	 * This method is used to render the page footer and overrides the original Footer() method on TCPDF.
	 * @public
	 */
	public function Footer() {
		parent::Footer();
	}

} // END OF TCPDFEX CLASS

//============================================================+
// END OF FILE
//============================================================+
