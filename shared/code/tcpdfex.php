<?php
//============================================================+
// File name   : tcpdfex.php
// Begin       : 2010-12-06
// Last Update : 2011-02-27
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
		if ($this->header_xobjid < 0) {
			// start a new XObject Template
			$this->header_xobjid = $this->startTemplate($this->w, $this->tMargin);
			$headerfont = $this->getHeaderFont();
			$headerdata = $this->getHeaderData();
			$this->y = $this->header_margin;
			if ($this->rtl) {
				$this->x = $this->w - $this->original_rMargin;
			} else {
				$this->x = $this->original_lMargin;
			}
			if (($headerdata['logo']) AND ($headerdata['logo'] != K_BLANK_IMAGE)) {
				$imgtype = $this->getImageFileType(K_PATH_IMAGES.$headerdata['logo']);
				if (($imgtype == 'eps') OR ($imgtype == 'ai')) {
					$this->ImageEps(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
				} elseif ($imgtype == 'svg') {
					$this->ImageSVG(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
				} else {
					$this->Image(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
				}
				$imgy = $this->getImageRBY();
			} else {
				$imgy = $this->y;
			}
			$cell_height = round(($this->cell_height_ratio * $headerfont[2]) / $this->k, 2);
			// set starting margin for text data cell
			if ($this->getRTL()) {
				$header_x = $this->original_rMargin + ($headerdata['logo_width'] * 1.1);
			} else {
				$header_x = $this->original_lMargin + ($headerdata['logo_width'] * 1.1);
			}
			$cw = $this->w - $this->original_lMargin - $this->original_rMargin - ($headerdata['logo_width'] * 1.1);
			$this->SetTextColor(0, 0, 0);
			// header title
			$this->SetFont($headerfont[0], 'B', $headerfont[2] + 1);
			$this->SetX($header_x);
			$this->Cell($cw, $cell_height, $headerdata['title'], 0, 1, '', 0, '', 0);
			// header string
			$this->SetFont($headerfont[0], $headerfont[1], $headerfont[2]);
			$this->SetX($header_x);
			$this->MultiCell($cw, $cell_height, $headerdata['string'], 0, '', 0, 1, '', '', true, 0, false);
			// print an ending header line
			$this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
			if (!empty($this->tcexam_backlink)) {
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
				$w = (PDF_MARGIN_TOP - PDF_MARGIN_HEADER - (5.7 / $this->k));
				$y = (PDF_MARGIN_HEADER);
				if ($this->rtl) {
					$x = PDF_MARGIN_LEFT + $w;
				} else {
					$x = $this->w - PDF_MARGIN_RIGHT - $w;
				}
				// write QR-Code on header
				$this->write2DBarcode($this->tcexam_backlink, 'QRCODE,M', $x, $y, $w, $w, $style, 'N');
			}
			$this->SetY((2.835 / $this->k) + max($imgy, $this->y));
			if ($this->rtl) {
				$this->SetX($this->original_rMargin);
			} else {
				$this->SetX($this->original_lMargin);
			}
			$this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, '', 'T', 0, 'C');
			$this->endTemplate();
		}
		// print header template
		$x = 0;
		$dx = 0;
		if ($this->booklet AND (($this->page % 2) == 0)) {
			// adjust margins for booklet mode
			$dx = ($this->original_lMargin - $this->original_rMargin);
		}
		if ($this->rtl) {
			$x = $this->w + $dx;
		} else {
			$x = 0 + $dx;
		}
		$this->printTemplate($this->header_xobjid, $x, 0, 0, 0, '', '', false);
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
