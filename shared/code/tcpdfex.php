<?php
//============================================================+
// File name   : tcpdfex.php
// Begin       : 2010-12-06
// Last Update : 2014-04-15
// Author      : Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
// License     : http://www.tecnick.com/pagefiles/tcpdf/LICENSE.TXT GNU-LGPLv3 + YOU CAN'T REMOVE ANY TCPDF COPYRIGHT NOTICE OR LINK FROM THE GENERATED PDF DOCUMENTS.
// -------------------------------------------------------------------
// Copyright (C) 2002-2014 Nicola Asuni - Tecnick.com LTD
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

require(dirname(__FILE__).'/../tcpdf/tcpdf.php');

/**
 * @class TCPDFEX
 * This is an extension of the TCPDF class for creating PDF document.
 * This extension allows you to define custom Header and Footer for PDF documents.
 * @package com.tecnick.tcpdf
 * @author Nicola Asuni - info@tecnick.com
 */
class TCPDFEX extends TCPDF
{

    /**
     * URL link that points back to TCExam website.
     * @protected
     */
    protected $tcexam_backlink = '';

    /**
     *
     * @protected
     */
    protected $tce_cell_height_ratio;

    /**
     *
     * @protected
     */
    protected $tce_page_width;

    /**
     *
     * @protected
     */
    protected $tce_data_cell_height;

    /**
     *
     * @protected
     */
    protected $tce_main_cell_height;

    /**
     * This is the class constructor.
     * It allows to set up the page format, the orientation and the measure unit used in all the methods (except for the font sizes).
     * @param $orientation (string) page orientation. Possible values are (case insensitive):<ul><li>P or Portrait (default)</li><li>L or Landscape</li><li>'' (empty string) for automatic orientation</li></ul>
     * @param $unit (string) User measure unit. Possible values are:<ul><li>pt: point</li><li>mm: millimeter (default)</li><li>cm: centimeter</li><li>in: inch</li></ul><br />A point equals 1/72 of inch, that is to say about 0.35 mm (an inch being 2.54 cm). This is a very common unit in typography; font sizes are expressed in that unit.
     * @param $format (mixed) The format used for pages. It can be either: one of the string values specified at getPageSizeFromFormat() or an array of parameters specified at setPageFormat().
     * @param $unicode (boolean) TRUE means that the input text is unicode (default = true)
     * @param $encoding (string) Charset encoding; default is UTF-8.
     * @param $diskcache (boolean) If TRUE reduce the RAM memory usage by caching temporary data on filesystem (slower).
     * @param $pdfa (boolean) If TRUE set the document to PDF/A mode.
     * @public
     * @see getPageSizeFromFormat(), setPageFormat()
     */
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false)
    {
        parent::__construct();
        // calculate some sizes
        $this->tce_cell_height_ratio = (K_CELL_HEIGHT_RATIO + 0.1);
        $this->tce_page_width = ($this->getPageWidth() - PDF_MARGIN_LEFT - PDF_MARGIN_RIGHT);
        $this->tce_data_cell_height = round(($this->tce_cell_height_ratio * PDF_FONT_SIZE_DATA) / $this->getScaleFactor(), 2);
        $this->tce_main_cell_height = round(($this->tce_cell_height_ratio * PDF_FONT_SIZE_MAIN) / $this->getScaleFactor(), 2);
    }

    /**
     * Set an URL link that points back to TCExam website (this will be printed as QR-Code on header).
     * @param $link URL link.
     * @public
     */
    public function setTCExamBackLink($link)
    {
        $this->tcexam_backlink = $link;
    }

    /**
     * This method is used to render the page header and overrides the original Header() method on TCPDF.
     * @public
     */
    public function Header()
    {
        if ($this->header_xobjid === false) {
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
            if (($headerdata['logo']) and ($headerdata['logo'] != K_BLANK_IMAGE)) {
                $imgtype = TCPDF_IMAGES::getImageFileType(K_PATH_IMAGES.$headerdata['logo']);
                if (($imgtype == 'eps') or ($imgtype == 'ai')) {
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
                // QRCODE
                $w = (PDF_MARGIN_TOP - PDF_MARGIN_HEADER - (5.7 / $this->k));
                $y = (PDF_MARGIN_HEADER);
                if ($this->rtl) {
                    $x = PDF_MARGIN_LEFT + $w;
                } else {
                    $x = $this->w - PDF_MARGIN_RIGHT - $w;
                }
                // write QR-Code on header
                $this->write2DBarcode($this->tcexam_backlink, 'QRCODE,L', $x, $y, $w, $w, $style, 'N');
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
        if ($this->booklet and (($this->page % 2) == 0)) {
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
    public function Footer()
    {
        parent::Footer();
    }

    /**
     * Print question stats.
     * @param $stats (array) data to print
     * @param $display_mode (int) display mode: 0 = disabled; 1 = minimum; 2 = module; 3 = subject; 4 = question; 5 = answer.
     * @public
     */
    public function printQuestionStats($stats, $display_mode = 2)
    {

        if ($display_mode < 2) {
            return;
        }
        
        require_once('../config/tce_config.php');
        require_once('../../shared/code/tce_functions_tcecode.php');
        global $l;

        $tce_data_cell_width = round($this->tce_page_width / 9, 2);
        $tce_data_cell_width_third = round($tce_data_cell_width / 3, 2);
        $tce_data_cell_width_half = round($tce_data_cell_width / 2, 2);

        if (empty($stats)) {
            return;
        }

        $numberfont = 'courier';
        $fontdatasize = PDF_FONT_SIZE_DATA - 1;

        $this->SetFillColor(204, 204, 204);
        $this->SetLineWidth(0.1);
        $this->SetDrawColor(0, 0, 0);

        $this->SetFont(PDF_FONT_NAME_DATA, 'B', (PDF_FONT_SIZE_DATA + 1));

        $title = $l['w_statistics'].' ['.$l['w_all'].' + '.$l['w_module'].'';
        if ($display_mode > 2) {
            $title .= ' + '.$l['w_subject'].'';
            if ($display_mode > 3) {
                $title .= ' + '.$l['w_question'].'';
                if ($display_mode > 4) {
                    $title .= ' + '.$l['w_answer'].'';
                }
            }
        }
        $title .= ']';
        $this->Cell(0, $this->tce_data_cell_height, $title, 'T', 1, 'C', 0);
        $this->Ln(1);

        $this->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA);

        // print table headings
        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, '#', 1, 0, 'C', 1);
        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $l['w_recurrence'], 1, 0, 'C', true, '', 1);
        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $l['w_score'], 1, 0, 'C', true, '', 1);
        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $l['w_answer_time'], 1, 0, 'C', true, '', 1);
        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $l['w_answers_right_th'], 1, 0, 'C', true, '', 1);
        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $l['w_answers_wrong_th'], 1, 0, 'C', true, '', 1);
        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $l['w_questions_unanswered_th'], 1, 0, 'C', true, '', 1);
        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $l['w_questions_undisplayed_th'], 1, 0, 'C', true, '', 1);
        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $l['w_questions_unrated_th'], 1, 1, 'C', true, '', 1);
        $this->Ln(2);

        // print table rows
        $this->SetFillColor(255, 238, 238);
        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $l['w_all'], 1, 0, 'L', true, '', 1);
        $this->SetFont($numberfont, 'B', $fontdatasize);
        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $stats['recurrence'].' '.F_formatPdfPercentage($stats['recurrence_perc'], false), 1, 0, 'R', true);
        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, number_format($stats['average_score'], 3, '.', '').' '.F_formatPdfPercentage($stats['average_score_perc'], false), 1, 0, 'R', true);
        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, date('i:s', $stats['average_time']), 1, 0, 'R', true);
        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $stats['right'].' '.F_formatPdfPercentage($stats['right_perc'], false), 1, 0, 'R', true);
        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $stats['wrong'].' '.F_formatPdfPercentage($stats['wrong_perc'], false), 1, 0, 'R', true);
        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $stats['unanswered'].' '.F_formatPdfPercentage($stats['unanswered_perc'], false), 1, 0, 'R', true);
        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $stats['undisplayed'].' '.F_formatPdfPercentage($stats['undisplayed_perc'], false), 1, 0, 'R', true);
        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $stats['unrated'].' '.F_formatPdfPercentage($stats['unrated_perc'], false), 1, 1, 'R', true);

        $this->Ln(1);

        $num_module = 0;
        foreach ($stats['module'] as $module) {
            $num_module++;

            $this->SetFillColor(221, 238, 255);
            $this->SetFont($numberfont, 'B', $fontdatasize);
            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, 'M'.$num_module, 1, 0, 'L', true, '', 1);
            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $module['recurrence'].' '.F_formatPdfPercentage($module['recurrence_perc'], false), 1, 0, 'R', true);
            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, number_format($module['average_score'], 3, '.', '').' '.F_formatPdfPercentage($module['average_score_perc'], false), 1, 0, 'R', true);
            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, date('i:s', $module['average_time']), 1, 0, 'R', true);
            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $module['right'].' '.F_formatPdfPercentage($module['right_perc'], false), 1, 0, 'R', true);
            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $module['wrong'].' '.F_formatPdfPercentage($module['wrong_perc'], false), 1, 0, 'R', true);
            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $module['unanswered'].' '.F_formatPdfPercentage($module['unanswered_perc'], false), 1, 0, 'R', true);
            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $module['undisplayed'].' '.F_formatPdfPercentage($module['undisplayed_perc'], false), 1, 0, 'R', true);
            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $module['unrated'].' '.F_formatPdfPercentage($module['unrated_perc'], false), 1, 1, 'R', true);
            $this->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
            $this->writeHTMLCell(0, $this->tce_data_cell_height, (PDF_MARGIN_LEFT + (2 * $tce_data_cell_width_third)), $this->GetY(), F_decode_tcecode($module['name']), 1, 1);

            if ($display_mode > 2) {
                $num_subject = 0;
                foreach ($module['subject'] as $subject) {
                    $num_subject++;

                    $this->SetFillColor(221, 255, 221);
                    $this->SetFont($numberfont, 'B', $fontdatasize);
                    $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, 'M'.$num_module.'S'.$num_subject, 1, 0, 'L', true, '', 1);
                    $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $subject['recurrence'].' '.F_formatPdfPercentage($subject['recurrence_perc'], false), 1, 0, 'R', true);
                    $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, number_format($subject['average_score'], 3, '.', '').' '.F_formatPdfPercentage($subject['average_score_perc'], false), 1, 0, 'R', true);
                    $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, date('i:s', $subject['average_time']), 1, 0, 'R', true);
                    $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $subject['right'].' '.F_formatPdfPercentage($subject['right_perc'], false), 1, 0, 'R', true);
                    $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $subject['wrong'].' '.F_formatPdfPercentage($subject['wrong_perc'], false), 1, 0, 'R', true);
                    $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $subject['unanswered'].' '.F_formatPdfPercentage($subject['unanswered_perc'], false), 1, 0, 'R', true);
                    $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $subject['undisplayed'].' '.F_formatPdfPercentage($subject['undisplayed_perc'], false), 1, 0, 'R', true);
                    $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $subject['unrated'].' '.F_formatPdfPercentage($subject['unrated_perc'], false), 1, 1, 'R', true);
                    $this->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
                    $this->writeHTMLCell(0, $this->tce_data_cell_height, (PDF_MARGIN_LEFT + (2 * $tce_data_cell_width_third)), $this->GetY(), F_decode_tcecode($subject['name']), 1, 1);

                    if ($display_mode > 3) {
                        $num_question = 0;
                        foreach ($subject['question'] as $question) {
                            $num_question++;

                            $this->SetFillColor(255, 250, 205);
                            $this->SetFont($numberfont, 'B', $fontdatasize);
                            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, 'M'.$num_module.'S'.$num_subject.'Q'.$num_question, 1, 0, 'L', true, '', 1);
                            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $question['recurrence'].' '.F_formatPdfPercentage($question['recurrence_perc'], false), 1, 0, 'R', true);
                            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, number_format($question['average_score'], 3, '.', '').' '.F_formatPdfPercentage($question['average_score_perc'], false), 1, 0, 'R', true);
                            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, date('i:s', $question['average_time']), 1, 0, 'R', true);
                            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $question['right'].' '.F_formatPdfPercentage($question['right_perc'], false), 1, 0, 'R', true);
                            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $question['wrong'].' '.F_formatPdfPercentage($question['wrong_perc'], false), 1, 0, 'R', true);
                            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $question['unanswered'].' '.F_formatPdfPercentage($question['unanswered_perc'], false), 1, 0, 'R', true);
                            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $question['undisplayed'].' '.F_formatPdfPercentage($question['undisplayed_perc'], false), 1, 0, 'R', true);
                            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $question['unrated'].' '.F_formatPdfPercentage($question['unrated_perc'], false), 1, 1, 'R', true);
                            $this->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
                            $this->writeHTMLCell(0, $this->tce_data_cell_height, (PDF_MARGIN_LEFT + (2 * $tce_data_cell_width_third)), $this->GetY(), F_decode_tcecode($question['description']), 1, 1);

                            if ($display_mode > 4) {
                                $num_answer = 0;
                                foreach ($question['answer'] as $answer) {
                                    $num_answer++;

                                    $this->SetFont($numberfont, 'B', $fontdatasize);
                                    $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, 'M'.$num_module.'S'.$num_subject.'Q'.$num_question.'A'.$num_answer, 1, 0, 'L', 0, '', 1);
                                    $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $answer['recurrence'].' '.F_formatPdfPercentage($answer['recurrence_perc'], false), 1, 0, 'R', 0);
                                    $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, '', 1, 0, 'R', 0);
                                    $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, '', 1, 0, 'R', 0);
                                    $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $answer['right'].' '.F_formatPdfPercentage($answer['right_perc'], false), 1, 0, 'R', 0);
                                    $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $answer['wrong'].' '.F_formatPdfPercentage($answer['wrong_perc'], false), 1, 0, 'R', 0);
                                    $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $answer['unanswered'].' '.F_formatPdfPercentage($answer['unanswered_perc'], false), 1, 0, 'R', 0);
                                    $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, '', 1, 0, 'R', 0);
                                    $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, '', 1, 1, 'R', 0);
                                    $this->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
                                    $this->writeHTMLCell(0, $this->tce_data_cell_height, (PDF_MARGIN_LEFT + (2 * $tce_data_cell_width_third)), $this->GetY(), F_decode_tcecode($answer['description']), 1, 1);
                                } // end for answer
                            }
                        } // end for question
                    }
                } // end for subject
            }
        } // end for module
    }

    /**
     * Print SVG graph
     * @param $svgdata (string) String containing SVG data to print.
     * @public
     */
    public function printSVGStatsGraph($svgdata)
    {
        require_once('../config/tce_config.php');
        require_once('../../shared/code/tce_functions_svg_graph.php');
        global $l;
        // display svg graph
        if (preg_match_all('/[x]/', $svgdata, $match) > 1) {
            $this->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA);
            // legend
            $legend = '<span style="background-color:#ff0000;color:#ffffff;">&nbsp;'.$l['w_score'].'&nbsp;</span> <span style="background-color:#0000ff;color:#ffffff;">&nbsp;'.$l['w_answers_right'].'&nbsp;</span> / <span style="background-color:#dddddd;color:#000000;">&nbsp;'.$l['w_tests'].'&nbsp;</span></div>';
            $this->writeHTML($legend, true, false, true, false, 'C');
            $this->SetFont(PDF_FONT_NAME_DATA, '', 6);
            $w = $this->tce_page_width * $this->imgscale * $this->k;
            $h = round($this->tce_page_width * 9 / 16);
            $svg = F_getSVGGraphCode(substr($svgdata, 1), $w, $h);
            if (isset($svg[0]) and ($svg[0] == '<')) {
                $this->ImageSVG('@'.$svg, '', '', $this->tce_page_width, 0, '', 'N', 'C', 0, false);
            }
        }
    }

    /**
    * Print test stats table
    * @param $data (array) Array containing test statistics.
    * @param $pubmode (boolean) If true filter the results for the public interface.
    * @param $stats (int) 2 = full stats; 1 = user stats; 0 = disabled stats;
    */
    public function printTestResultStat($data, $pubmode = false, $stats = 2)
    {
        require_once('../config/tce_config.php');
        global $l;
        // add a bookmark
        $this->Bookmark($l['w_results']);
        if ($l['a_meta_dir'] == 'rtl') {
            $tdalignr = 'L';
            $tdalign = 'R';
        } else {
            $tdalignr = 'R';
            $tdalign = 'L';
        }

        if ($pubmode) {
            $num_column = 11;
        } else {
            $num_column = 14;
        }
        if ($stats < 1) {
            $num_column -= 5;
        }
        $tce_data_cell_width = round($this->tce_page_width / $num_column, 2);
        $tce_data_cell_width_third = round($tce_data_cell_width / 3, 2);
        $tce_data_cell_width_half = round($tce_data_cell_width / 2, 2);

        $numberfont = 'courier';
        $fontdatasize = PDF_FONT_SIZE_DATA - 1;

        $this->SetFillColor(204, 204, 204);
        $this->SetLineWidth(0.1);
        $this->SetDrawColor(0, 0, 0);

        $this->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA);


        // print table headings
        $this->Cell($tce_data_cell_width_third, $this->tce_data_cell_height, '#', 1, 0, 'C', 1);
        $this->Cell(5 * $tce_data_cell_width_third, $this->tce_data_cell_height, $l['w_time_begin'], 1, 0, 'C', true, '', 1);
        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $l['w_time'], 1, 0, 'C', true, '', 1);
        $this->Cell(4 * $tce_data_cell_width_third, $this->tce_data_cell_height, $l['w_test'], 1, 0, 'C', true, '', 1);
        if (!$pubmode) {
            $this->Cell((3 * $tce_data_cell_width), $this->tce_data_cell_height, $l['w_user'].' - '.$l['w_lastname'].', '.$l['w_firstname'], 1, 0, 'C', true, '', 1);
        }
        $this->Cell(5 * $tce_data_cell_width_third, $this->tce_data_cell_height, $l['w_score'], 1, 0, 'C', true, '', 1);
        if ($stats > 0) {
            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $l['w_answers_right_th'], 1, 0, 'C', true, '', 1);
            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $l['w_answers_wrong_th'], 1, 0, 'C', true, '', 1);
            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $l['w_questions_unanswered_th'], 1, 0, 'C', true, '', 1);
            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $l['w_questions_undisplayed_th'], 1, 0, 'C', true, '', 1);
            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $l['w_questions_unrated_th'], 1, 0, 'C', true, '', 1);
        }
        $this->Ln();
        $this->Ln(2);

        $this->SetFont($numberfont, '', $fontdatasize);

        foreach ($data['testuser'] as $tu) {
            $this->Cell($tce_data_cell_width_third, $this->tce_data_cell_height, $tu['num'], 1, 0, 'R', 0);
            $this->Cell(5 * $tce_data_cell_width_third, $this->tce_data_cell_height, $tu['testuser_creation_time'], 1, 0, 'R', 0, '', 1);
            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $tu['time_diff'], 1, 0, 'R', 0, '', 1);
            $this->SetFont(PDF_FONT_NAME_DATA, '', $fontdatasize);
            $this->Cell(4 * $tce_data_cell_width_third, $this->tce_data_cell_height, $tu['test']['test_name'], 1, 0, $tdalign, 0, '', 1);
            if (!$pubmode) {
                $this->Cell((3 * $tce_data_cell_width), $this->tce_data_cell_height, $tu['user_name'].' - '.$tu['user_lastname'].', '.$tu['user_firstname'], 1, 0, $tdalign, 0, '', 1);
            }
            $this->SetFont($numberfont, '', $fontdatasize);
            if ($tu['passmsg']) {
                $this->SetFillColor(221, 255, 221);
                $this->SetFont($numberfont, 'B', $fontdatasize);
            } else {
                $this->SetFillColor(255, 238, 238);
            }
            $this->Cell(5 * $tce_data_cell_width_third, $this->tce_data_cell_height, $tu['total_score'].' '.F_formatPdfPercentage($tu['total_score_perc'], false), 1, 0, 'R', true, '', 1);
            if ($stats > 0) {
                $this->SetFont($numberfont, '', $fontdatasize);
                $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $tu['right'].' '.F_formatPdfPercentage($tu['right_perc'], false), 1, 0, 'R', 0, '', 1);
                $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $tu['wrong'].' '.F_formatPdfPercentage($tu['wrong_perc'], false), 1, 0, 'R', 0, '', 1);
                $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $tu['unanswered'].' '.F_formatPdfPercentage($tu['unanswered_perc'], false), 1, 0, 'R', 0, '', 1);
                $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $tu['undisplayed'].' '.F_formatPdfPercentage($tu['undisplayed_perc'], false), 1, 0, 'R', 0, '', 1);
                $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $tu['unrated'].' '.F_formatPdfPercentage($tu['unrated_perc'], false), 1, 0, 'R', 0, '', 1);
            }
            $this->Ln();
        }
        $this->SetFont(PDF_FONT_NAME_DATA, 'B', $fontdatasize);
        if ($data['passed_perc'] > 50) {
            $this->SetFillColor(221, 255, 221);
        } else {
            $this->SetFillColor(255, 238, 238);
        }
        $this->Cell(0, $this->tce_data_cell_height, $l['w_passed'].': '.$data['passed'].' '.F_formatPdfPercentage($data['passed_perc'], false), 1, 1, 'L', true, '', 1);
        // print statistics
        $printstat = array('mean', 'median', 'mode', 'standard_deviation', 'skewness', 'kurtosi');
        $noperc = array('skewness', 'kurtosi');
        foreach ($data['statistics'] as $row => $col) {
            if (in_array($row, $printstat)) {
                $this->SetFont(PDF_FONT_NAME_DATA, 'B', $fontdatasize);
                if ($pubmode) {
                    $this->Cell((4 * $tce_data_cell_width) + $tce_data_cell_width_third, $this->tce_data_cell_height, $l['w_'.$row], 1, 0, $tdalignr, 0, '', 1);
                } else {
                    $this->Cell((7 * $tce_data_cell_width) + $tce_data_cell_width_third, $this->tce_data_cell_height, $l['w_'.$row], 1, 0, $tdalignr, 0, '', 1);
                }
                $this->SetFont($numberfont, '', $fontdatasize);
                if (in_array($row, $noperc)) {
                    $this->Cell(5 * $tce_data_cell_width_third, $this->tce_data_cell_height, F_formatFloat($col['score_perc']), 1, 0, 'R', 0, '', 1);
                    if ($stats > 0) {
                        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, F_formatFloat($col['right_perc']), 1, 0, 'R', 0, '', 1);
                        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, F_formatFloat($col['wrong_perc']), 1, 0, 'R', 0, '', 1);
                        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, F_formatFloat($col['unanswered_perc']), 1, 0, 'R', 0, '', 1);
                        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, F_formatFloat($col['undisplayed_perc']), 1, 0, 'R', 0, '', 1);
                        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, F_formatFloat($col['unrated_perc']), 1, 0, 'R', 0, '', 1);
                    }
                    $this->Ln();
                } else {
                    $this->Cell(5 * $tce_data_cell_width_third, $this->tce_data_cell_height, round($col['score_perc']).'%', 1, 0, 'R', 0, '', 1);
                    if ($stats > 0) {
                        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, round($col['right_perc']).'%', 1, 0, 'R', 0, '', 1);
                        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, round($col['wrong_perc']).'%', 1, 0, 'R', 0, '', 1);
                        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, round($col['unanswered_perc']).'%', 1, 0, 'R', 0, '', 1);
                        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, round($col['undisplayed_perc']).'%', 1, 0, 'R', 0, '', 1);
                        $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, round($col['unrated_perc']).'%', 1, 0, 'R', 0, '', 1);
                    }
                    $this->Ln();
                }
            }
        }
    }

    /**
    * Print testuser data
    * @param $data (array) Array containing test statistics.
    * @param $onlytext (boolean) If true print only text questions.
    */
    function printTestUserInfo($data, $onlytext = false)
    {
        require_once('../config/tce_config.php');
        global $l;

        $this->SetFillColor(204, 204, 204);
        $this->SetLineWidth(0.1);
        $this->SetDrawColor(0, 0, 0);

        if ($l['a_meta_dir'] == 'rtl') {
            $dirlabel = 'L';
            $dirvalue = 'R';
        } else {
            $dirlabel = 'R';
            $dirvalue = 'L';
        }

        // add a bookmark
        $this->Bookmark($data['user_lastname'].' '.$data['user_firstname'].' ('.$data['user_name'].'), '.$data['total_score'].' '.F_formatPdfPercentage($data['total_score_perc'], false), 0, 0);

        // --- display test info ---

        $info_cell_width = round($this->tce_page_width / 4, 2);

        $boxStartY = $this->GetY(); // store current Y position

        // test name
        $this->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA * HEAD_MAGNIFICATION);
        $this->Cell($this->tce_page_width, $this->tce_data_cell_height * HEAD_MAGNIFICATION, $l['w_test'].': '.$data['test']['test_name'], 1, 1, '', 1);

        $this->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);

        $infoStartY = $this->GetY() + 2; // store current Y position
        $this->SetY($infoStartY);

        $column_names_width = round($info_cell_width * 0.75, 2);

        $this->Cell($column_names_width, $this->tce_data_cell_height, $l['w_lastname'].': ', 0, 0, $dirlabel, 0, '', 1);
        $this->Cell($info_cell_width, $this->tce_data_cell_height, $data['user_lastname'], 0, 1, $dirvalue, 0, '', 1);

        $this->Cell($column_names_width, $this->tce_data_cell_height, $l['w_firstname'].': ', 0, 0, $dirlabel, 0, '', 1);
        $this->Cell($info_cell_width, $this->tce_data_cell_height, $data['user_firstname'], 0, 1, $dirvalue, 0, '', 1);

        $this->Cell($column_names_width, $this->tce_data_cell_height, $l['w_user'].': ', 0, 0, $dirlabel, 0, '', 1);
        $this->Cell($info_cell_width, $this->tce_data_cell_height, $data['user_name'], 0, 1, $dirvalue, 0, '', 1);

        // test start time
        $this->Cell($column_names_width, $this->tce_data_cell_height, $l['w_time_begin'].': ', 0, 0, $dirlabel, 0, '', 1);
        $this->Cell($info_cell_width, $this->tce_data_cell_height, $data['test']['user_test_start_time'], 0, 1, $dirvalue, 0, '', 1);

        // test end time
        $this->Cell($column_names_width, $this->tce_data_cell_height, $l['w_time_end'].': ', 0, 0, $dirlabel, 0, '', 1);
        $this->Cell($info_cell_width, $this->tce_data_cell_height, $data['test']['user_test_end_time'], 0, 1, $dirvalue, 0, '', 1);

        // test duration
        //$this->Cell($column_names_width, $this->tce_data_cell_height, $l['w_test_time'].' ['.$l['w_minutes'].']: ', 0, 0, $dirlabel, 0, '', 1);
        //$this->Cell($info_cell_width, $this->tce_data_cell_height, $data['test']['test_duration_time'], 0, 1, $dirvalue, 0, '', 1);

        if (!isset($data['test']['user_test_end_time']) or ($data['test']['user_test_end_time'] <= 0) or (strtotime($data['test']['user_test_end_time']) < strtotime($data['test']['user_test_start_time']))) {
            $time_diff = $data['test']['test_duration_time'] * 60;
        } else {
            $time_diff = strtotime($data['test']['user_test_end_time']) - strtotime($data['test']['user_test_start_time']); //sec
        }
        $time_diff = gmdate('H:i:s', $time_diff);
        // elapsed time (time difference)
        $this->Cell($column_names_width, $this->tce_data_cell_height, $l['w_time'].': ', 0, 0, $dirlabel, 0);
        $this->Cell($info_cell_width, $this->tce_data_cell_height, $time_diff, 0, 1, $dirvalue, 0);

        //$this->Cell($column_names_width, $this->tce_data_cell_height, $l['w_ip_range'].': ', 0, 0, $dirlabel, 0, '', 1);
        //$this->Cell($info_cell_width, $this->tce_data_cell_height, $data['test']['test_ip_range'], 0, 1, $dirvalue, 0, '', 1);

        //$this->Cell($column_names_width, $this->tce_data_cell_height, $l['w_score_right'].': ', 0, 0, $dirlabel, 0, '', 1);
        //$this->Cell($info_cell_width, $this->tce_data_cell_height, $data['test']['test_score_right'], 0, 1, $dirvalue, 0, '', 1);

        //$this->Cell($column_names_width, $this->tce_data_cell_height, $l['w_score_wrong'].': ', 0, 0, $dirlabel, 0, '', 1);
        //$this->Cell($info_cell_width, $this->tce_data_cell_height, $data['test']['test_score_wrong'], 0, 1, $dirvalue, 0, '', 1);

        //$this->Cell($column_names_width, $this->tce_data_cell_height, $l['w_score_unanswered'].': ', 0, 0, $dirlabel, 0, '', 1);
        //$this->Cell($info_cell_width, $this->tce_data_cell_height, $data['test']['test_score_unanswered'], 0, 1, $dirvalue, 0, '', 1);

        // max score
        //$this->Cell($column_names_width, $this->tce_data_cell_height, $l['w_max_score'].': ', 0, 0, $dirlabel, 0, '', 1);
        //$this->Cell($info_cell_width, $this->tce_data_cell_height, $data['test']['test_max_score'], 0, 1, $dirvalue, 0, '', 1);

        $passmsg = '';
        if ($data['test']['test_score_threshold'] > 0) {
            $this->Cell($column_names_width, $this->tce_data_cell_height, $l['w_test_score_threshold'].': ', 0, 0, $dirlabel, 0, '', 1);
            $this->Cell($info_cell_width, $this->tce_data_cell_height, $data['test']['test_score_threshold'], 0, 1, $dirvalue, 0, '', 1);
            if ($data['total_score'] >= $data['test']['test_score_threshold']) {
                $passmsg = ' - '.$l['w_passed'];
            } else {
                $passmsg = ' - '.$l['w_not_passed'];
            }
        }

        $this->Cell($column_names_width, $this->tce_data_cell_height, $l['w_answers_right'].': ', 0, 0, $dirlabel, 0, '', 1);
        $this->Cell($info_cell_width, $this->tce_data_cell_height, $data['right'].' '.F_formatPdfPercentage($data['right_perc'], false), 0, 1, $dirvalue, 0, '', 1);

        $this->Cell($column_names_width, $this->tce_data_cell_height, $l['w_answers_wrong'].': ', 0, 0, $dirlabel, 0, '', 1);
        $this->Cell($info_cell_width, $this->tce_data_cell_height, $data['wrong'].' '.F_formatPdfPercentage($data['wrong_perc'], false), 0, 1, $dirvalue, 0, '', 1);

        $this->Cell($column_names_width, $this->tce_data_cell_height, $l['w_questions_unanswered'].': ', 0, 0, $dirlabel, 0, '', 1);
        $this->Cell($info_cell_width, $this->tce_data_cell_height, $data['unanswered'].' '.F_formatPdfPercentage($data['unanswered_perc'], false), 0, 1, $dirvalue, 0, '', 1);

        $this->Cell($column_names_width, $this->tce_data_cell_height, $l['w_questions_undisplayed'].': ', 0, 0, $dirlabel, 0, '', 1);
        $this->Cell($info_cell_width, $this->tce_data_cell_height, $data['undisplayed'].' '.F_formatPdfPercentage($data['undisplayed_perc'], false), 0, 1, $dirvalue, 0, '', 1);

        $this->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA);
        $this->Cell($column_names_width, $this->tce_data_cell_height, $l['w_score'].': ', 0, 0, $dirlabel, 0, '', 1);
        $this->Cell($info_cell_width, $this->tce_data_cell_height, $data['total_score'].' / '.$data['test']['test_max_score'].' '.F_formatPdfPercentage($data['total_score_perc'], false).$passmsg, 0, 1, $dirvalue, 0, '', 1);

        $this->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);



        $boxEndY = $this->GetY();

        $this->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);

        // test description
        $this->writeHTMLCell(0, ($boxEndY - $infoStartY + 4), (PDF_MARGIN_LEFT + ($info_cell_width * 2)), $infoStartY - 2, $data['test']['test_description'], 1, 0);

        $boxEndY = max($boxEndY, $this->GetY());

        // print box around test info
        $this->SetY($boxStartY);
        $this->Cell($this->tce_page_width, ($boxEndY - $boxStartY + 2), '', 1, 1, 'C', 0, '', 1);
        $this->SetY($boxEndY - 1);
        // --- end test info ---

        // print user's comments
        if (!empty($data['test']['user_comment'])) {
            $this->Cell($this->tce_page_width, $this->tce_data_cell_height, '', 0, 1, '', 0);
            $this->writeHTMLCell($this->tce_page_width, $this->tce_data_cell_height, '', '', '<b>'.$l['w_comment'].'</b>:<br />'.$data['test']['user_comment'], 1, 1);
        }

        $this->Ln(4);

        $this->printUserTestDetails($data, $onlytext);
    }

    /**
    * print test details for the selected user
    * @param $data (array) Testuser data array.
    * @param $onlytext (boolean) If true print only text questions.
    */
    public function printUserTestDetails($data, $onlytext = false)
    {
        require_once('../config/tce_config.php');
        require_once('../../shared/code/tce_functions_test_stats.php');
        require_once('../../shared/code/tce_functions_tcecode.php');
        global $db, $l;
        $testuser_id = intval($data['id']);
        $qtype = array('S', 'M', 'T', 'O'); // question types

        $num_column = 7;
        $tce_data_cell_width = round($this->tce_page_width / $num_column, 2);
        $tce_data_cell_width_third = round($tce_data_cell_width / 3, 2);
        $tce_data_cell_width_half = round($tce_data_cell_width / 2, 2);

        $numberfont = 'courier';

        // display user questions
        $sql = 'SELECT *
			FROM '.K_TABLE_QUESTIONS.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_SUBJECTS.', '.K_TABLE_MODULES.'
			WHERE question_id=testlog_question_id
				AND testlog_testuser_id='.$testuser_id.'
				AND question_subject_id=subject_id
				AND subject_module_id=module_id';
        if ($onlytext) {
            // display only TEXT questions
            $sql .= ' AND question_type=3';
        }
        $sql .= ' ORDER BY testlog_id';
        if ($r = F_db_query($sql, $db)) {
            $this->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA);

            $this->Cell($tce_data_cell_width_third, $this->tce_data_cell_height, '#', 1, 0, 'C', 1);
            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $l['w_score'], 1, 0, 'C', 1);
            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $l['w_ip'], 1, 0, 'C', 1);
            $this->Cell($tce_data_cell_width + $tce_data_cell_width_third, $this->tce_data_cell_height, $l['w_start'].' ['.$l['w_time_hhmmss'].']', 1, 0, 'C', 1);
            $this->Cell($tce_data_cell_width + $tce_data_cell_width_third, $this->tce_data_cell_height, $l['w_end'].' ['.$l['w_time_hhmmss'].']', 1, 0, 'C', 1);
            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $l['w_time'].' ['.$l['w_time_mmss'].']', 1, 0, 'C', 1);
            $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $l['w_reaction'].' [sec]', 1, 1, 'C', 1);
            $this->Ln($this->tce_data_cell_height);

            // print table rows

            $this->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
            $itemcount = 1;

            while ($m = F_db_fetch_array($r)) {
                $this->Cell($tce_data_cell_width_third, $this->tce_data_cell_height, $itemcount.' '.$qtype[($m['question_type']-1)], 1, 0, 'R', 0);
                $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $m['testlog_score'], 1, 0, 'C', 0);
                $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, getIpAsString($m['testlog_user_ip']), 1, 0, 'C', 0);
                if (isset($m['testlog_display_time']) and (strlen($m['testlog_display_time']) > 0)) {
                    $display_time =  substr($m['testlog_display_time'], 11, 8);
                } else {
                    $display_time =  '--:--:--';
                }
                if (isset($m['testlog_change_time']) and (strlen($m['testlog_change_time']) > 0)) {
                    $change_time = substr($m['testlog_change_time'], 11, 8);
                } else {
                    $change_time = '--:--:--';
                }
                if (isset($m['testlog_display_time']) and isset($m['testlog_change_time'])) {
                    $diff_time = date('i:s', (strtotime($m['testlog_change_time']) - strtotime($m['testlog_display_time'])));
                } else {
                    $diff_time = '--:--';
                }
                if (isset($m['testlog_reaction_time']) and (strlen($m['testlog_reaction_time']) > 0)) {
                    $reaction_time =  ($m['testlog_reaction_time'] / 1000);
                } else {
                    $reaction_time =  '';
                }
                $this->Cell($tce_data_cell_width + $tce_data_cell_width_third, $this->tce_data_cell_height, $display_time, 1, 0, 'C', 0);
                $this->Cell($tce_data_cell_width + $tce_data_cell_width_third, $this->tce_data_cell_height, $change_time, 1, 0, 'C', 0);
                $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $diff_time, 1, 0, 'C', 0);
                $this->Cell($tce_data_cell_width, $this->tce_data_cell_height, $reaction_time, 1, 1, 'C', 0);

                $this->writeHTMLCell(0, $this->tce_data_cell_height, (PDF_MARGIN_LEFT + $tce_data_cell_width_third), $this->GetY(), F_decode_tcecode($m['question_description']), 1, 1);
                if (K_ENABLE_QUESTION_EXPLANATION and !empty($m['question_explanation'])) {
                    $this->Cell($tce_data_cell_width_third, $this->tce_data_cell_height, '', 0, 0, 'C', 0);
                    $this->SetFont('', 'BIU');
                    $this->Cell(0, $this->tce_data_cell_height, $l['w_explanation'], 'LTR', 1, '', 0, '', 0);
                    $this->SetFont('', '');
                    $this->writeHTMLCell(0, $this->tce_data_cell_height, (PDF_MARGIN_LEFT + $tce_data_cell_width_third), $this->GetY(), F_decode_tcecode($m['question_explanation']), 'LRB', 1, '', '');
                }

                if ($m['question_type'] == 3) {
                    // free-text question - print user text answer
                    $this->writeHTMLCell(0, $this->tce_data_cell_height, (PDF_MARGIN_LEFT + (2 * $tce_data_cell_width_third)), $this->GetY(), F_decode_tcecode($m['testlog_answer_text']), 1, 1);
                } else {
                    // display each answer option
                    $sqla = 'SELECT * FROM '.K_TABLE_LOG_ANSWER.', '.K_TABLE_ANSWERS.' WHERE logansw_answer_id=answer_id AND logansw_testlog_id='.$m['testlog_id'].' ORDER BY logansw_order';
                    if ($ra = F_db_query($sqla, $db)) {
                        $idx = 0; // count items
                        while ($ma = F_db_fetch_array($ra)) {
                            $posfill = 0;
                            $idx++;
                            $this->Cell($tce_data_cell_width_third, $this->tce_data_cell_height, '', 0, 0, 'C', 0);
                            if ($m['question_type'] == 4) {
                                if ($ma['logansw_position'] > 0) {
                                    if ($ma['logansw_position'] == $ma['answer_position']) {
                                        $posfill = 1;
                                        $this->Cell($tce_data_cell_width_third, $this->tce_data_cell_height, $ma['logansw_position'], 1, 0, 'C', 1);
                                    } else {
                                        $this->Cell($tce_data_cell_width_third, $this->tce_data_cell_height, $ma['logansw_position'], 1, 0, 'C', 0);
                                    }
                                } else {
                                    $this->Cell($tce_data_cell_width_third, $this->tce_data_cell_height, ' ', 1, 0, 'C', 0);
                                }
                            } elseif ($ma['logansw_selected'] > 0) {
                                // selected
                                if (F_getBoolean($ma['answer_isright'])) {
                                    $this->Cell($tce_data_cell_width_third, $this->tce_data_cell_height, '+', 1, 0, 'C', 1);
                                } else {
                                    $this->Cell($tce_data_cell_width_third, $this->tce_data_cell_height, '-', 1, 0, 'C', 1);
                                }
                            } elseif ($m['question_type'] == 1) {
                                // MCSA
                                $this->Cell($tce_data_cell_width_third, $this->tce_data_cell_height, ' ', 1, 0, 'C', 0);
                            } else {
                                if ($ma['logansw_selected'] == 0) {
                                    // unselected
                                    if (F_getBoolean($ma['answer_isright'])) {
                                        $this->Cell($tce_data_cell_width_third, $this->tce_data_cell_height, '-', 1, 0, 'C', 0);
                                    } else {
                                        $this->Cell($tce_data_cell_width_third, $this->tce_data_cell_height, '+', 1, 0, 'C', 0);
                                    }
                                } else {
                                    // no answer
                                    $this->Cell($tce_data_cell_width_third, $this->tce_data_cell_height, ' ', 1, 0, 'C', 0);
                                }
                            }
                            if ($m['question_type'] == 4) {
                                    $this->Cell($tce_data_cell_width_third, $this->tce_data_cell_height, $ma['answer_position'], 1, 0, 'C', $posfill);
                            } elseif (F_getBoolean($ma['answer_isright'])) {
                                $this->Cell($tce_data_cell_width_third, $this->tce_data_cell_height, $idx, 1, 0, 'C', 1);
                            } else {
                                $this->Cell($tce_data_cell_width_third, $this->tce_data_cell_height, $idx, 1, 0, 'C', 0);
                            }
                            $this->writeHTMLCell(0, $this->tce_data_cell_height, (PDF_MARGIN_LEFT + $tce_data_cell_width), $this->GetY(), F_decode_tcecode($ma['answer_description']), 'LRTB', 1);
                            if (K_ENABLE_ANSWER_EXPLANATION and !empty($ma['answer_explanation'])) {
                                $this->Cell((3 * $tce_data_cell_width_third), $this->tce_data_cell_height, '', 0, 0, 'C', 0);
                                $this->SetFont('', 'BIU');
                                $this->Cell(0, $this->tce_data_cell_height, $l['w_explanation'], 'LTR', 1, '', 0, '', 0);
                                $this->SetFont('', '');
                                $this->writeHTMLCell(0, $this->tce_data_cell_height, (PDF_MARGIN_LEFT + (3 * $tce_data_cell_width_third)), $this->GetY(), F_decode_tcecode($ma['answer_explanation']), 'LRB', 1, '', '');
                            }
                        }
                    } else {
                        F_display_db_error();
                    }
                } // end multiple answers
                if (strlen($m['testlog_comment']) > 0) {
                    // teacher / supervisor comment
                    $this->SetTextColor(255, 0, 0);
                    $this->writeHTMLCell(0, $this->tce_data_cell_height, (PDF_MARGIN_LEFT + (2 * $tce_data_cell_width_third)), $this->GetY(), F_decode_tcecode($m['testlog_comment']), 'LRTB', 1);
                    $this->SetTextColor(0, 0, 0);
                }
                $this->Ln($this->tce_data_cell_height);
                $itemcount++;
            }
        } else {
            F_display_db_error();
        }
        $stats = F_getTestStat($data['test']['test_id'], 0, $data['user_id'], 0, 0, $data['id']);
        $this->printQuestionStats($stats['qstats'], 1);
    }
} // END OF TCPDFEX CLASS

//============================================================+
// END OF FILE
//============================================================+
