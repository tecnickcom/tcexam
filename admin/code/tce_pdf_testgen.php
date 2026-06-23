<?php

//============================================================+
// File name   : tce_pdf_testgen.php
// Begin       : 2004-06-13
// Last Update : 2026-06-22
//
// Description : Creates PDF documents for offline (pen-and-paper) testing,
//               including the OMR (Optical Mark Recognition) answer sheet.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

/**
 * @file
 * Creates PDF documents for Pen-and-Paper testing.
 *
 * NOTE: the OMR answer-sheet grid (alignment marks, answer bubbles, barcodes) is
 * read back by a physical scanner via tce_functions_omr.php, so its coordinates must
 * stay byte-for-byte identical to the legacy layout. The question sheet (not scanned)
 * is rendered with the tc-lib-pdf HTML engine.
 *
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2004-06-13
 * @param $_REQUEST['test_id'] (int) test ID
 * @param $_REQUEST['num'] (int) number of tests to generate
 */

// Use the generated tc-lib-pdf fonts for this document (set before the config defines the legacy default).
require_once __DIR__ . '/../../vendor/autoload.php';
define('K_PATH_FONTS', realpath(__DIR__ . '/../../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

require_once '../config/tce_config.php';
require_once '../../shared/code/tce_authorization.php';
require_once '../../shared/code/tce_functions_tcecode.php';
require_once '../../shared/code/tce_functions_test.php';
require_once 'tce_functions_omr.php';
require_once '../../shared/config/tce_pdf.php';
require_once '../../shared/code/tce_pdf_report.php';

// --- Initialize variables
if (isset($_REQUEST['test_id']) && $_REQUEST['test_id'] > 0) {
    $test_id = (int) $_REQUEST['test_id'];
    // check user's authorization
    if (!F_isAuthorizedUser(K_TABLE_TESTS, 'test_id', $test_id, 'test_user_id')) {
        exit();
    }
} else {
    exit();
}

$test_num = isset($_REQUEST['num']) ? (int) $_REQUEST['num'] : 1;

$doc_title = unhtmlentities($l['w_test']);
$doc_description = F_compact_string(unhtmlentities($l['h_test']));
$qtype = ['S', 'M', 'T', 'O']; // question types

$rtl_doc = $l['a_meta_dir'] == 'rtl';
$dirlabel = $rtl_doc ? 'left' : 'right';
$dirvalue = $rtl_doc ? 'right' : 'left';

// RGB array [r,g,b] -> '#rrggbb' for the tc-lib-pdf graph/colour API.
$rgb = static fn(array $c): string => sprintf('#%02x%02x%02x', $c[0], $c[1], $c[2]);

// --- OMR grid geometry (millimetres) — DO NOT change: read back by the scanner.
$grid_color = [255, 0, 0];
$grid_bg_color = [255, 205, 205];
$circle_bg_color = [255, 255, 255];
$line_width = 0.177; // about half point
$circle_radius = $line_width * 11;
$circle_width = (2 * $circle_radius) + $line_width;
$circle_shift = $circle_width + $line_width;
$circle_half_width = $circle_width / 2;
$align_mark_color = [0, 0, 0];
$align_mark_width = $line_width * 7;
$align_mark_length = $line_width * 22;
$align_mark_shift = $line_width * 8;
$row_height = $circle_width + (8 * $line_width);

$grid_hex = $rgb($grid_color);
$grid_bg_hex = $rgb($grid_bg_color);
$circle_bg_hex = $rgb($circle_bg_color);
$align_hex = $rgb($align_mark_color);
$omr_line_style = ['lineWidth' => $line_width, 'lineColor' => $grid_hex];

// get test data
$testdata = F_getTestData($test_id);
$test_random_questions_select = F_getBoolean($testdata['test_random_questions_select']);
$test_random_questions_order = F_getBoolean($testdata['test_random_questions_order']);
$test_questions_order_mode = (int) $testdata['test_questions_order_mode'];
$test_random_answers_select = F_getBoolean($testdata['test_random_answers_select']);
$test_random_answers_order = F_getBoolean($testdata['test_random_answers_order']);
$test_answers_order_mode = (int) $testdata['test_answers_order_mode'];
$random_questions = $test_random_questions_select || $test_random_questions_order;
$sql_answer_position = '';
if (!$test_random_answers_order && $test_answers_order_mode == 0) {
    $sql_answer_position = ' AND answer_position>0';
}

$sql_questions_order_by = '';
switch ($test_questions_order_mode) {
    case 0: // position
        $sql_questions_order_by = ' AND question_position>0 ORDER BY question_position';
        break;
    case 1: // alphabetic
        $sql_questions_order_by = ' ORDER BY question_description';
        break;
    case 2: // ID
        $sql_questions_order_by = ' ORDER BY question_id';
        break;
    case 3: // type
        $sql_questions_order_by = ' ORDER BY question_type';
        break;
    case 4: // subject ID
        $sql_questions_order_by = ' ORDER BY question_subject_id';
        break;
}

// --- create the PDF document (tc-lib-pdf) ---

$pdf = new TcePdfReport();
$pdf->setCreator('TCExam ver.' . K_TCEXAM_VERSION);
$pdf->setAuthor(PDF_AUTHOR);
$pdf->setTitle((string) $doc_title);
$pdf->setSubject((string) $doc_description);
$pdf->setKeywords('TCExam, ' . $doc_title);
$pdf->setLanguageArray($l);
$pdf->setReportHeader(PDF_HEADER_TITLE, PDF_HEADER_STRING, PDF_HEADER_LOGO, (float) PDF_HEADER_LOGO_WIDTH);

// Draw a text cell at an absolute position (used for the coordinate-exact OMR pages).
$omrText = static function (
    string $txt,
    float $px,
    float $py,
    float $w,
    float $h,
    string $halign,
    string $fname,
    string $fstyle,
    int $fsize,
    string $colorhex,
) use ($pdf): string {
    $fnt = $pdf->font->insert($pdf->pon, $fname, $fstyle, $fsize);
    return (
        $fnt['out']
        . $pdf->color->getPdfColor($colorhex)
        . $pdf->getTextCell(
            txt: $txt,
            posx: $px,
            posy: $py,
            width: $w,
            height: $h,
            offset: 0,
            linespace: 0,
            valign: 'C',
            halign: $halign,
        )
    );
};

// NOTE: PDF tests are always random

for ($item = 1; $item <= $test_num; ++$item) {
    // generate $test_num tests

    // data to be printed as QR-Code to be later used as input from scanner/image
    $barcode_test_data = [];
    $barcode_test_data[0] = $test_id;

    $test_ref = $test_id . ':' . $item . ':' . date(K_TIMESTAMP_FORMAT);

    // ====================================================================
    // QUESTION SHEET (HTML; not scanned, so rendered with the HTML engine)
    // ====================================================================
    $pdf->enablePageDecoration(true);
    $pdf->setTCExamBackLink(K_PATH_URL . 'admin/code/tce_edit_test.php?test_id=' . $test_id);
    $pdf->addReportPage();

    $html =
        '<h2 style="text-align:center;background-color:#cccccc;border:0.5px solid #000000;">'
        . htmlspecialchars((string) $doc_title)
        . '</h2>';
    $html .= '<div style="text-align:center;color:#ff0000;font-size:7pt;">[' . htmlspecialchars($test_ref) . ']</div>';

    // user data input boxes
    $html .=
        '<table border="0.5" cellpadding="3" style="font-size:8pt;text-align:center;">'
        . '<tr style="background-color:#cccccc;font-weight:bold;"><td width="25%">'
        . htmlspecialchars($l['w_lastname'])
        . '</td>'
        . '<td width="25%">'
        . htmlspecialchars($l['w_firstname'])
        . '</td>'
        . '<td width="25%">'
        . htmlspecialchars($l['w_code'])
        . '</td>'
        . '<td width="25%">'
        . htmlspecialchars($l['w_score'])
        . '</td></tr>'
        . '<tr><td style="padding-top:10px;padding-bottom:10px;">&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr></table>';

    // test info box
    $threshold = '';
    if ($testdata['test_score_threshold'] > 0) {
        $threshold =
            '<tr><td style="font-weight:bold;" align="'
            . $dirlabel
            . '">'
            . htmlspecialchars($l['w_test_score_threshold'])
            . ': </td><td align="'
            . $dirvalue
            . '">'
            . htmlspecialchars((string) $testdata['test_score_threshold'])
            . '</td></tr>';
    }
    $info = [
        $l['w_test_time'] . ' [' . $l['w_minutes'] . ']' => $testdata['test_duration_time'],
        $l['w_time_begin'] => '',
        $l['w_time_end'] => '',
        $l['w_score_right'] => $testdata['test_score_right'],
        $l['w_score_wrong'] => $testdata['test_score_wrong'],
        $l['w_score_unanswered'] => $testdata['test_score_unanswered'],
        $l['w_max_score'] => $testdata['test_max_score'],
    ];
    $html .= '<table border="0.5" cellpadding="2" style="font-size:8pt;">';
    $html .=
        '<tr style="background-color:#cccccc;font-weight:bold;"><td colspan="2">'
        . htmlspecialchars($l['w_test'] . ': ' . $testdata['test_name'])
        . '</td></tr>';
    foreach ($info as $k => $v) {
        $html .=
            '<tr><td style="font-weight:bold;" width="40%" align="'
            . $dirlabel
            . '">'
            . htmlspecialchars((string) $k)
            . ': </td><td align="'
            . $dirvalue
            . '">'
            . htmlspecialchars((string) $v)
            . '</td></tr>';
    }
    $html .= $threshold . '</table>';
    if (!empty($testdata['test_description'])) {
        $html .=
            '<div style="font-size:8pt;border:0.5px solid #000000;padding:2px;">'
            . F_decode_tcecode($testdata['test_description'])
            . '</div>';
    }

    // count questions
    $itemcount = 1;

    // selected questions IDs
    $right_answers_mcsa_questions_ids = '';
    $wrong_answers_mcsa_questions_ids = [];
    $answers_mcma_questions_ids = [];
    $answers_order_questions_ids = '';
    $selected_questions = '0';

    // 2. for each set of subjects
    $sql =
        'SELECT *
		FROM '
        . K_TABLE_TEST_SUBJSET
        . '
		WHERE tsubset_test_id='
        . $test_id
        . '
		ORDER BY tsubset_type, tsubset_difficulty, tsubset_answers DESC';
    if ($r = F_db_query($sql, $db)) {
        $questions_data = [];
        while ($m = F_db_fetch_array($r)) {
            // 3. select the subjects IDs
            $selected_subjects = '0';
            $sqlt =
                'SELECT subjset_subject_id FROM '
                . K_TABLE_SUBJECT_SET
                . ' WHERE subjset_tsubset_id='
                . $m['tsubset_id'];
            if ($rt = F_db_query($sqlt, $db)) {
                while ($mt = F_db_fetch_array($rt)) {
                    $selected_subjects .= ',' . $mt['subjset_subject_id'];
                }
            }

            // 4. select questions
            $sqlq = 'SELECT question_id, question_type, question_difficulty, question_position, question_description
				FROM ' . K_TABLE_QUESTIONS;
            $sqlq .=
                ' WHERE question_subject_id IN ('
                . $selected_subjects
                . ')
				AND question_difficulty='
                . $m['tsubset_difficulty']
                . '
				AND question_enabled=\'1\'
				AND question_id NOT IN ('
                . $selected_questions
                . ')';
            if ($m['tsubset_type'] > 0) {
                $sqlq .= ' AND question_type=' . $m['tsubset_type'];
            }

            if ($m['tsubset_type'] == 1) {
                // (MCSA : Multiple Choice Single Answer)
                if ($right_answers_mcsa_questions_ids === '') {
                    $right_answers_mcsa_questions_ids = '0';
                    $sqlt =
                        'SELECT DISTINCT answer_question_id FROM '
                        . K_TABLE_ANSWERS
                        . " WHERE answer_enabled='1' AND answer_isright='1'"
                        . $sql_answer_position;
                    if ($rt = F_db_query($sqlt, $db)) {
                        while ($mt = F_db_fetch_array($rt)) {
                            $right_answers_mcsa_questions_ids .= ',' . $mt['answer_question_id'];
                        }
                    }
                }

                $sqlq .= ' AND question_id IN (' . $right_answers_mcsa_questions_ids . ')';
                if ($m['tsubset_answers'] > 0) {
                    if (!isset($wrong_answers_mcsa_questions_ids["'" . $m['tsubset_answers'] . "'"])) {
                        $wrong_answers_mcsa_questions_ids["'" . $m['tsubset_answers'] . "'"] = '0';
                        $sqlt =
                            'SELECT answer_question_id FROM '
                            . K_TABLE_ANSWERS
                            . " WHERE answer_enabled='1' AND answer_isright='0'"
                            . $sql_answer_position
                            . ' GROUP BY answer_question_id HAVING (COUNT(answer_id)>='
                            . ($m['tsubset_answers'] - 1)
                            . ')';
                        if ($rt = F_db_query($sqlt, $db)) {
                            while ($mt = F_db_fetch_array($rt)) {
                                $wrong_answers_mcsa_questions_ids["'" . $m['tsubset_answers'] . "'"] .=
                                    ',' . $mt['answer_question_id'];
                            }
                        }
                    }

                    $sqlq .=
                        ' AND question_id IN ('
                        . $wrong_answers_mcsa_questions_ids["'" . $m['tsubset_answers'] . "'"]
                        . ')';
                }
            } elseif ($m['tsubset_type'] == 2) {
                // (MCMA : Multiple Choice Multiple Answers)
                if ($m['tsubset_answers'] > 0) {
                    if (!isset($answers_mcma_questions_ids["'" . $m['tsubset_answers'] . "'"])) {
                        $answers_mcma_questions_ids["'" . $m['tsubset_answers'] . "'"] = '0';
                        $sqlt =
                            'SELECT answer_question_id FROM '
                            . K_TABLE_ANSWERS
                            . " WHERE answer_enabled='1'"
                            . $sql_answer_position
                            . ' GROUP BY answer_question_id HAVING (COUNT(answer_id)>='
                            . $m['tsubset_answers']
                            . ')';
                        if ($rt = F_db_query($sqlt, $db)) {
                            while ($mt = F_db_fetch_array($rt)) {
                                $answers_mcma_questions_ids["'" . $m['tsubset_answers'] . "'"] .=
                                    ',' . $mt['answer_question_id'];
                            }
                        }
                    }

                    $sqlq .=
                        ' AND question_id IN (' . $answers_mcma_questions_ids["'" . $m['tsubset_answers'] . "'"] . ')';
                }
            } elseif ($m['tsubset_type'] == 4) {
                // ORDERING
                if ($answers_order_questions_ids === '') {
                    $answers_order_questions_ids = '0';
                    $sqlt =
                        'SELECT answer_question_id FROM '
                        . K_TABLE_ANSWERS
                        . " WHERE answer_enabled='1' AND answer_position>0 GROUP BY answer_question_id HAVING (COUNT(answer_id)>1)";
                    if ($rt = F_db_query($sqlt, $db)) {
                        while ($mt = F_db_fetch_array($rt)) {
                            $answers_order_questions_ids .= ',' . $mt['answer_question_id'];
                        }
                    }
                }

                $sqlq .= ' AND question_id IN (' . $answers_order_questions_ids . ')';
            }

            if ($random_questions) {
                $sqlq .= ' ORDER BY RAND()';
            } else {
                $sqlq .= $sql_questions_order_by;
            }

            if (K_DATABASE_TYPE == 'ORACLE') {
                $sqlq = 'SELECT * FROM (' . $sqlq . ') WHERE rownum <= ' . $m['tsubset_quantity'];
            } else {
                $sqlq .= ' LIMIT ' . $m['tsubset_quantity'];
            }

            if ($rq = F_db_query($sqlq, $db)) {
                while ($mq = F_db_fetch_array($rq)) {
                    $tmp_data = [
                        'id' => $mq['question_id'],
                        'type' => $mq['question_type'],
                        'difficulty' => $mq['question_difficulty'],
                        'description' => $mq['question_description'],
                        'answers' => $m['tsubset_answers'],
                        'score' => $testdata['test_score_unanswered'] * $mq['question_difficulty'],
                    ];
                    if ($random_questions || $test_questions_order_mode != 0) {
                        $questions_data[] = $tmp_data;
                    } else {
                        $questions_data[$mq['question_position']] = $tmp_data;
                    }

                    $selected_questions .= ',' . $mq['question_id'];
                }
            } else {
                F_display_db_error(false);
                return false;
            }
        } // end while for each set of subjects

        // 5. STORE QUESTIONS AND ANSWERS
        if ($random_questions) {
            shuffle($questions_data);
        } else {
            ksort($questions_data);
        }

        // 4. PRINT QUESTIONS (build HTML; page-break-inside:avoid replaces the legacy transaction logic)
        $question_order = 0;
        foreach ($questions_data as $key => $q) {
            ++$question_order;

            // add question ID to QR-Code data
            $barcode_test_data[$question_order] = [
                0 => $q['id'],
                1 => [],
            ];

            $block = '<div style="page-break-inside:avoid;">';
            // question number + type, max points, description
            // width:100% + explicit per-column widths (summing to 100%) so the row spans
            // the full content width up to the margin; an auto description column would
            // otherwise default to availableWidth/cols, leaving the table narrow and
            // letting wide images overflow past the cell. (See tce_pdf_report.php.)
            $block .=
                '<table border="0.5" cellpadding="2" style="width:100%;font-size:8pt;"><tr>'
                . '<td align="right" style="width:8%;">'
                . htmlspecialchars($itemcount . ' ' . $qtype[$q['type'] - 1])
                . '</td>'
                . '<td align="right" style="width:8%;">'
                . htmlspecialchars((string) ($q['difficulty'] * $testdata['test_score_right']))
                . '</td>'
                . '<td style="width:84%;">'
                . F_decode_tcecode($q['description'])
                . '</td></tr></table>';

            ++$itemcount;

            if ($q['type'] == 3) {
                // free-text question: print a writing area; the correct short answers are
                // printed in hidden white (visible only via "Replace Document Colors").
                $shortanswers = '';
                $sqlsa =
                    'SELECT answer_description FROM '
                    . K_TABLE_ANSWERS
                    . '
					WHERE answer_question_id='
                    . $q['id']
                    . " AND answer_enabled='1' AND answer_isright='1'";
                if ($rsa = F_db_query($sqlsa, $db)) {
                    while ($msa = F_db_fetch_array($rsa)) {
                        $shortanswers .= $msa['answer_description'] . ' ; ';
                    }
                } else {
                    F_display_db_error();
                }
                $block .=
                    '<div style="border:0.5px solid #000000;height:'
                    . (int) PDF_TEXTANSWER_HEIGHT
                    . 'px;color:#ffffff;font-size:7pt;">'
                    . htmlspecialchars($shortanswers)
                    . '</div>';
            } else {
                // select answers (identical logic to the legacy generator)
                $randorder = $test_random_answers_order;
                $answers_ids = [];
                switch ($q['type']) {
                    case 1: // MCSA
                        $answers_ids += F_selectAnswers($q['id'], 1, false, 1, 0, $randorder, $test_answers_order_mode);
                        $answers_ids += F_selectAnswers(
                            $q['id'],
                            0,
                            false,
                            $q['answers'] - 1,
                            1,
                            $randorder,
                            $test_answers_order_mode,
                        );
                        break;
                    case 2: // MCMA
                        $answers_ids += F_selectAnswers(
                            $q['id'],
                            '',
                            false,
                            $q['answers'],
                            0,
                            $randorder,
                            $test_answers_order_mode,
                        );
                        break;
                    case 4: // ORDERING
                        $randorder = true;
                        $answers_ids += F_selectAnswers($q['id'], '', true, 0, 0, $randorder, $test_answers_order_mode);
                        break;
                }

                if ($randorder) {
                    shuffle($answers_ids);
                } else {
                    ksort($answers_ids);
                }

                // width:100% so the answer rows span the full content width up to the margin
                // (explicit per-column widths below); otherwise the table auto-sizes narrow and
                // wide answer images overflow the cell. (Same fix as tce_pdf_report.php.)
                $block .= '<table border="0.5" cellpadding="2" style="width:100%;font-size:8pt;">';
                $answ_id = 0;
                foreach ($answers_ids as $key2 => $answer_id) {
                    ++$answ_id;
                    // add answer ID to QR-Code data
                    $barcode_test_data[$question_order][1][$answ_id] = $answer_id;

                    $sqla = 'SELECT * FROM ' . K_TABLE_ANSWERS . ' WHERE answer_id=' . $answer_id . ' LIMIT 1';
                    if ($ra = F_db_query($sqla, $db)) {
                        if ($ma = F_db_fetch_array($ra)) {
                            $rightanswer = '';
                            if ($q['type'] == 4) {
                                $rightanswer = $ma['answer_position'];
                            } elseif (F_getBoolean($ma['answer_isright'])) {
                                $rightanswer = 'X';
                            }
                            // hidden white correct-answer marker + answer number + description
                            $block .=
                                '<tr>'
                                . '<td align="center" style="width:8%;color:#ffffff;">'
                                . htmlspecialchars((string) $rightanswer)
                                . '</td>'
                                . '<td align="right" style="width:8%;">'
                                . $answ_id
                                . '</td>'
                                . '<td style="width:84%;">'
                                . F_decode_tcecode($ma['answer_description'])
                                . '</td></tr>';
                        }
                    } else {
                        F_display_db_error();
                    }
                }
                $block .= '</table>';
            }
            $block .= '</div>';
            $html .= $block;
        } // end foreach questions
    } else {
        F_display_db_error();
    }

    if ($rtl_doc) {
        $html = '<div dir="rtl">' . $html . '</div>';
    }
    $pdf->writeReportHTML($html);

    // ====================================================================
    // OMR DATA PAGE — encoded test data as a large centred QR-Code (no header)
    // ====================================================================
    $pdf->enablePageDecoration(false);
    $pdf->addPage(['format' => 'A4', 'orientation' => 'P']);
    $pg = $pdf->page->getPage($pdf->page->getPageId());
    $pw = (float) $pg['width'];
    $ph = (float) $pg['height'];
    $cw = $pw - PDF_MARGIN_LEFT - PDF_MARGIN_RIGHT;

    $out = $omrText(
        'OMR DATA',
        PDF_MARGIN_LEFT,
        PDF_MARGIN_TOP,
        $cw,
        6,
        'C',
        PDF_FONT_NAME_DATA,
        '',
        (int) round(PDF_FONT_SIZE_DATA * 1.5),
        $grid_hex,
    );
    $out .= $omrText(
        '[' . $test_ref . ']',
        PDF_MARGIN_LEFT,
        PDF_MARGIN_TOP + 6,
        $cw,
        5,
        'C',
        PDF_FONT_NAME_DATA,
        '',
        PDF_FONT_SIZE_DATA,
        $grid_hex,
    );
    $pdf->page->addContent($out);

    // encode data to be printed on the QR-Code (used to create test logs)
    $qr_test_data = F_encodeOMRTestData($barcode_test_data);
    // render at natural module size (unstretched) and centre it — a stretched QR-Code scans poorly
    $qrw = (float) $pdf->barcode->getBarcodeObj('QRCODE,L', $qr_test_data)->getArray()['ncols'];
    $qry = ($ph - $qrw) / 2; // vertically centred
    $qrx = ($pw - $qrw) / 2; // horizontally centred
    $pdf->page->addContent($pdf->getBarcode(
        'QRCODE,L',
        $qr_test_data,
        $qrx,
        $qry,
        (int) round($qrw),
        (int) round($qrw),
        style: ['fillColor' => $align_hex],
    ));

    // ====================================================================
    // OMR ANSWER SHEET — coordinate-exact grid (scanner-read; do not alter layout)
    // Supports up to 30 questions per sheet and up to 12 answers per question (MCSA/MCMA).
    // ====================================================================
    $num_questions = count($barcode_test_data) - 1;
    $num_omr_pages = (int) ceil($num_questions / 30);

    // centre the block on the page (legacy magic dimensions)
    $start_x = ($pw - 173.964) / 2;
    $start_y = ($ph - 178.062) / 2;
    $bcy = $ph - PDF_MARGIN_FOOTER - 12;

    for ($omrpage = 0; $omrpage < $num_omr_pages; ++$omrpage) {
        $pdf->addPage(['format' => 'A4', 'orientation' => 'P']);

        $head = $omrText(
            'OMR ANSWER SHEET ' . ($omrpage + 1),
            PDF_MARGIN_LEFT,
            PDF_MARGIN_TOP,
            $cw,
            6,
            'C',
            PDF_FONT_NAME_DATA,
            '',
            (int) round(PDF_FONT_SIZE_DATA * 1.5),
            $grid_hex,
        );
        $head .= $omrText(
            '[' . $test_ref . ']',
            PDF_MARGIN_LEFT,
            PDF_MARGIN_TOP + 6,
            $cw,
            5,
            'C',
            PDF_FONT_NAME_DATA,
            '',
            PDF_FONT_SIZE_DATA,
            $grid_hex,
        );
        $pdf->page->addContent($head);

        // starting (first) question number on this sheet
        $first_question = 1 + (30 * $omrpage);
        $qnum = sprintf('%04d', $first_question);

        $out = '';

        // top alignment marks for columns
        $x = $start_x;
        $y = $start_y;
        $out .= $pdf->graph->getRect($x, $y, $align_mark_length, $align_mark_length, 'F', ['all' => [
            'fillColor' => $align_hex,
        ]]);
        $x += $align_mark_length + 9;
        for ($i = 0; $i < 12; ++$i) {
            $x += $circle_shift;
            $out .= $pdf->graph->getRect(
                $x + $align_mark_shift,
                $y,
                $align_mark_width,
                $align_mark_length,
                'F',
                ['all' => ['fillColor' => $align_hex]],
            );
            $x += $circle_shift;
            $out .= $pdf->graph->getRect(
                $x + $align_mark_shift,
                $y,
                $align_mark_width,
                $align_mark_length,
                'F',
                ['all' => ['fillColor' => $align_hex]],
            );
            $x += $circle_shift;
        }

        $y += $row_height + $circle_half_width;

        for ($rr = 0; $rr < 30; ++$rr) {
            $current_question = $first_question + $rr;
            $x = $start_x;
            $cy = $y + $circle_half_width;
            // left alignment mark for row
            $out .= $pdf->graph->getRect(
                $x,
                $y + $align_mark_shift,
                $align_mark_length,
                $align_mark_width,
                'F',
                ['all' => ['fillColor' => $align_hex]],
            );
            $x += $align_mark_length;
            if ($current_question <= $num_questions) {
                if (($rr % 2) != 0) {
                    // row background
                    $out .= $pdf->graph->getRect(
                        $x,
                        $y - (4 * $line_width),
                        166.176,
                        $circle_width + (8 * $line_width),
                        'F',
                        ['all' => ['fillColor' => $grid_bg_hex]],
                    );
                }

                // print question number (courier bold)
                $out .= $omrText(
                    (string) $current_question,
                    $x,
                    $y,
                    8,
                    $circle_width,
                    'R',
                    'courier',
                    'B',
                    10,
                    $grid_hex,
                );
                $x += 9;
                $question_type = $questions_data[$current_question - 1]['type'];
                if ($question_type < 3) { // MCSA or MCMA
                    $num_answers = count($barcode_test_data[$current_question][1]);
                    for ($i = 1; $i <= 12; ++$i) {
                        if ($i <= $num_answers) {
                            // print answer number
                            $out .= $omrText(
                                (string) $i,
                                $x,
                                $y,
                                $circle_shift,
                                $circle_width,
                                'R',
                                'helvetica',
                                '',
                                8,
                                $grid_hex,
                            );
                            $x += $circle_shift;
                            // "true" select circle
                            $out .= $pdf->graph->getCircle(
                                $x + $circle_half_width,
                                $cy,
                                $circle_radius,
                                0,
                                360,
                                'DF',
                                $omr_line_style + ['fillColor' => $circle_bg_hex],
                            );
                            $out .= $omrText(
                                $l['w_true_acronym'],
                                $x,
                                $y,
                                $circle_width,
                                $circle_width,
                                'C',
                                PDF_FONT_NAME_DATA,
                                '',
                                6,
                                $grid_bg_hex,
                            );
                            $x += $circle_shift;
                            if ($question_type == 2) { // MCMA: add a "false" circle
                                $out .= $pdf->graph->getCircle(
                                    $x + $circle_half_width,
                                    $cy,
                                    $circle_radius,
                                    0,
                                    360,
                                    'DF',
                                    $omr_line_style + ['fillColor' => $circle_bg_hex],
                                );
                                $out .= $omrText(
                                    $l['w_false_acronym'],
                                    $x,
                                    $y,
                                    $circle_width,
                                    $circle_width,
                                    'C',
                                    PDF_FONT_NAME_DATA,
                                    '',
                                    6,
                                    $grid_bg_hex,
                                );
                            }
                        } else {
                            $x += 2 * $circle_shift;
                        }
                        $x += $circle_shift;
                    }
                } else {
                    $x += 36 * $circle_shift;
                }
            } else {
                $x += 9 + (36 * $circle_shift);
            }

            $x += $circle_shift;
            // right alignment mark for row
            $out .= $pdf->graph->getRect(
                $x,
                $y + $align_mark_shift,
                $align_mark_length,
                $align_mark_width,
                'F',
                ['all' => ['fillColor' => $align_hex]],
            );
            $y += $row_height;
        }

        // bottom alignment marks for columns
        $x = $start_x + $align_mark_length + 9;
        $y += $circle_half_width;
        for ($i = 0; $i < 12; ++$i) {
            $x += $circle_shift;
            $out .= $pdf->graph->getRect(
                $x + $align_mark_shift,
                $y,
                $align_mark_width,
                $align_mark_length,
                'F',
                ['all' => ['fillColor' => $align_hex]],
            );
            $x += $circle_shift;
            $out .= $pdf->graph->getRect(
                $x + $align_mark_shift,
                $y,
                $align_mark_width,
                $align_mark_length,
                'F',
                ['all' => ['fillColor' => $align_hex]],
            );
            $x += $circle_shift;
        }

        // barcode identifying the starting question number — natural width (legacy 0.8 mm/module),
        // centred horizontally; it must NOT be stretched to the content width or the scanner can misread it
        $bcw = $pdf->barcode->getBarcodeObj('C128C', $qnum)->getArray()['full_width'] * 0.8;
        $out .= $pdf->getBarcode('C128C', $qnum, ($pw - $bcw) / 2, $bcy, (int) round($bcw), 10, style: [
            'fillColor' => $align_hex,
        ]);

        $pdf->page->addContent($out);
    } // end for each OMR page
} // end for each test

$pdf->outputReport('tcexam_test_' . $test_id . '_' . date('YmdHis') . '.pdf');
