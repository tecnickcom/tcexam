<?php
//============================================================+
// File name   : tce_xml_question_stats.php
// Begin       : 2010-05-10
// Last Update : 2013-09-05
//
// Description : Functions to export question stats using XML
//               or JSON format.
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               Manor Coach House, Church Hill
//               Aldershot, Hants, GU12 4RQ
//               UK
//               www.tecnick.com
//               info@tecnick.com
//
// License:
//    Copyright (C) 2004-2013  Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Export question stats in XML or JSON format.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2010-05-10
 */

/**
 */

 // check user's authorization
require_once('../config/tce_config.php');
$pagelevel = K_AUTH_ADMIN_RESULTS;
require_once('../../shared/code/tce_authorization.php');
require_once('../../shared/code/tce_functions_test.php');
require_once('../../shared/code/tce_functions_test_stats.php');
require_once('../code/tce_functions_statistics.php');
require_once('../code/tce_functions_auth_sql.php');

if (isset($_REQUEST['testid']) and ($_REQUEST['testid'] > 0)) {
    $test_id = intval($_REQUEST['testid']);
    // check user's authorization
    if (!F_isAuthorizedUser(K_TABLE_TESTS, 'test_id', $test_id, 'test_user_id')) {
        F_print_error('ERROR', $l['m_authorization_denied']);
        exit;
    }

    $output_format = isset($_REQUEST['format']) ? strtoupper($_REQUEST['format']) : 'XML';
    $out_filename = 'tcexam_questions_'.$test_id.'_'.date('YmdHis');
    $xml = F_xml_export_question_stats($test_id);

    switch ($output_format) {
        case 'JSON': {
            header('Content-Description: JSON File Transfer');
            header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
            header('Pragma: public');
            header('Expires: Thu, 04 Jan 1973 00:00:00 GMT'); // Date in the past
            header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
            // force download dialog
            header('Content-Type: application/force-download');
            header('Content-Type: application/octet-stream', false);
            header('Content-Type: application/download', false);
            header('Content-Type: application/json', false);
            // use the Content-Disposition header to supply a recommended filename
            header('Content-Disposition: attachment; filename='.$out_filename.'.json;');
            header('Content-Transfer-Encoding: binary');
            $xmlobj = new SimpleXMLElement($xml);
            echo json_encode($xmlobj);
            break;
        }
        case 'XML':
        default: {
            header('Content-Description: XML File Transfer');
            header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
            header('Pragma: public');
            header('Expires: Thu, 04 Jan 1973 00:00:00 GMT'); // Date in the past
            header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
            // force download dialog
            header('Content-Type: application/force-download');
            header('Content-Type: application/octet-stream', false);
            header('Content-Type: application/download', false);
            header('Content-Type: application/xml', false);
            // use the Content-Disposition header to supply a recommended filename
            header('Content-Disposition: attachment; filename='.$out_filename.'.xml;');
            header('Content-Transfer-Encoding: binary');
            echo $xml;
            break;
        }
    }
} else {
    exit;
}

/**
 * Export all question statistics of the selected test to XML.
 * @author Nicola Asuni
 * @since 2010-05-10
 * @param $test_id (int) test ID
 * @return XML data
 */
function F_xml_export_question_stats($test_id)
{
    global $l, $db;
    require_once('../config/tce_config.php');
    require_once('../../shared/code/tce_authorization.php');
    require_once('../code/tce_functions_auth_sql.php');

    $boolean = array('false', 'true');
    $type = array('single', 'multiple', 'text', 'ordering');

    $xml = ''; // XML data to be returned

    $xml .= '<'.'?xml version="1.0" encoding="UTF-8" ?'.'>'.K_NEWLINE;
    $xml .= '<tcexamquestionstats version="'.K_TCEXAM_VERSION.'">'.K_NEWLINE;
    $xml .=  K_TAB.'<header';
    $xml .= ' lang="'.K_USER_LANG.'"';
    $xml .= ' date="'.date(K_TIMESTAMP_FORMAT).'">'.K_NEWLINE;
    $xml .= K_TAB.'</header>'.K_NEWLINE;
    $xml .=  K_TAB.'<body>'.K_NEWLINE;

    // get test data
    $testdata = F_getTestData($test_id);

    // get total number of questions for the selected test
    $num_questions = F_count_rows(K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER, 'WHERE testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.'');

    // output questions stats
    $sqlr = 'SELECT
			question_id,
			COUNT(question_id) AS recurrence,
			AVG(testlog_score) AS average_score,
			AVG(testlog_change_time - testlog_display_time) AS average_time,
			min(question_difficulty) AS question_difficulty
		FROM '.K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER.', '.K_TABLE_QUESTIONS.'
		WHERE testlog_testuser_id=testuser_id
			AND testlog_question_id=question_id
			AND testuser_test_id='.$test_id.'
		GROUP BY question_id
		ORDER BY recurrence DESC,average_score DESC';
    if ($rr = F_db_query($sqlr, $db)) {
        while ($mr = F_db_fetch_array($rr)) {
            $xml .= K_TAB.K_TAB.'<question>'.K_NEWLINE;

            // get the question max score
            $question_max_score = $testdata['test_score_right'] * $mr['question_difficulty'];
            $qsttestdata = F_getQuestionTestStat($test_id, $mr['question_id']);

            $xml .= K_TAB.K_TAB.K_TAB.'<id>'.$mr['question_id'].'</id>'.K_NEWLINE;
            $question_description = '';
            $sqlrq = 'SELECT question_description FROM '.K_TABLE_QUESTIONS.' WHERE question_id='.$mr['question_id'].'';
            if ($rrq = F_db_query($sqlrq, $db)) {
                if ($mrq = F_db_fetch_array($rrq)) {
                    $question_description = $mrq['question_description'];
                }
            } else {
                F_display_db_error();
            }
            $xml .= K_TAB.K_TAB.K_TAB.'<description>'.F_text_to_xml($question_description).'</description>'.K_NEWLINE;
            $xml .= K_TAB.K_TAB.K_TAB.'<recurrence>'.$mr['recurrence'].'</recurrence>'.K_NEWLINE;
            $xml .= K_TAB.K_TAB.K_TAB.'<recurrence_percent>'.F_formatXMLPercentage($mr['recurrence'] / $num_questions).'</recurrence_percent>'.K_NEWLINE;
            $xml .= K_TAB.K_TAB.K_TAB.'<points>'.number_format($mr['average_score'], 3, '.', '').'</points>'.K_NEWLINE;
            $xml .= K_TAB.K_TAB.K_TAB.'<points_percent>'.F_formatXMLPercentage($mr['average_score'] / $question_max_score).'</points_percent>'.K_NEWLINE;
            if (stripos($mr['average_time'], ':') !== false) {
                // PostgreSQL returns formatted time, while MySQL returns the number of seconds
                $mr['average_time'] = strtotime($mr['average_time']);
            }
            $xml .= K_TAB.K_TAB.K_TAB.'<time>'.date('i:s', $mr['average_time']).'</time>'.K_NEWLINE;
            $xml .= K_TAB.K_TAB.K_TAB.'<correct>'.$qsttestdata['right'].'</correct>'.K_NEWLINE;
            $xml .= K_TAB.K_TAB.K_TAB.'<correct_percent>'.F_formatXMLPercentage($qsttestdata['right'] / $qsttestdata['num']).'</correct_percent>'.K_NEWLINE;
            $xml .= K_TAB.K_TAB.K_TAB.'<wrong>'.$qsttestdata['wrong'].'</wrong>'.K_NEWLINE;
            $xml .= K_TAB.K_TAB.K_TAB.'<wrong_percent>'.F_formatXMLPercentage($qsttestdata['wrong'] / $qsttestdata['num']).'</wrong_percent>'.K_NEWLINE;
            $xml .= K_TAB.K_TAB.K_TAB.'<unanswered>'.$qsttestdata['unanswered'].'</unanswered>'.K_NEWLINE;
            $xml .= K_TAB.K_TAB.K_TAB.'<unanswered_percent>'.F_formatXMLPercentage($qsttestdata['unanswered'] / $qsttestdata['num']).'</unanswered_percent>'.K_NEWLINE;
            $xml .= K_TAB.K_TAB.K_TAB.'<undisplayed>'.$qsttestdata['undisplayed'].'</undisplayed>'.K_NEWLINE;
            $xml .= K_TAB.K_TAB.K_TAB.'<undisplayed_percent>'.F_formatXMLPercentage($qsttestdata['undisplayed'] / $qsttestdata['num']).'</undisplayed_percent>'.K_NEWLINE;
            $xml .= K_TAB.K_TAB.K_TAB.'<unrated>'.$qsttestdata['unrated'].'</unrated>'.K_NEWLINE;
            $xml .= K_TAB.K_TAB.K_TAB.'<unrated_percent>'.F_formatXMLPercentage($qsttestdata['unrated'] / $qsttestdata['num']).'</unrated_percent>'.K_NEWLINE;

            // answers statistics

            $sqla = 'SELECT *
				FROM '.K_TABLE_ANSWERS.'
				WHERE answer_question_id='.$mr['question_id'].'
				ORDER BY answer_id';
            if ($ra = F_db_query($sqla, $db)) {
                while ($ma = F_db_fetch_array($ra)) {
                    $xml .= K_TAB.K_TAB.K_TAB.'<answer>'.K_NEWLINE;
                    $xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<id>'.$ma['answer_id'].'</id>'.K_NEWLINE;
                    $xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<description>'.F_text_to_xml($ma['answer_description']).'</description>'.K_NEWLINE;

                    $num_all_answers = F_count_rows(K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_ANSWERS.', '.K_TABLE_LOG_ANSWER.' WHERE logansw_answer_id=answer_id AND logansw_testlog_id=testlog_id AND testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.' AND testlog_question_id='.$mr['question_id'].'');
                    $num_answers = F_count_rows(K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_ANSWERS.', '.K_TABLE_LOG_ANSWER.' WHERE answer_id='.$ma['answer_id'].' AND logansw_answer_id=answer_id AND logansw_testlog_id=testlog_id AND testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.' AND testlog_question_id='.$mr['question_id'].'');
                    $right_answers = F_count_rows(K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_ANSWERS.', '.K_TABLE_LOG_ANSWER.' WHERE answer_id='.$ma['answer_id'].' AND logansw_answer_id=answer_id AND logansw_testlog_id=testlog_id AND testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.' AND testlog_question_id='.$mr['question_id'].' AND ((answer_isright=\'0\' AND logansw_selected=0) OR (answer_isright=\'1\' AND logansw_selected=1) OR (answer_position IS NOT NULL AND logansw_position IS NOT NULL AND answer_position=logansw_position))');
                    $wrong_answers = F_count_rows(K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_ANSWERS.', '.K_TABLE_LOG_ANSWER.' WHERE answer_id='.$ma['answer_id'].' AND logansw_answer_id=answer_id AND logansw_testlog_id=testlog_id AND testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.' AND testlog_question_id='.$mr['question_id'].' AND ((answer_isright=\'0\' AND logansw_selected=1) OR (answer_isright=\'1\' AND logansw_selected=0) OR (answer_position IS NOT NULL AND answer_position!=logansw_position))');
                    $unanswered = F_count_rows(K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_ANSWERS.', '.K_TABLE_LOG_ANSWER.' WHERE answer_id='.$ma['answer_id'].' AND logansw_answer_id=answer_id AND logansw_testlog_id=testlog_id AND testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.' AND testlog_question_id='.$mr['question_id'].' AND logansw_selected=-1');

                    $xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<recurrence>'.$num_answers.'</recurrence>'.K_NEWLINE;
                    $perc = 0;
                    if ($num_all_answers > 0) {
                        $perc = ($num_answers / $num_all_answers);
                    }
                    $xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<recurrence_percent>'.F_formatXMLPercentage($perc).'</recurrence_percent>'.K_NEWLINE;
                    $xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<correct>'.$right_answers.'</correct>'.K_NEWLINE;
                    $perc = 0;
                    if ($num_answers > 0) {
                        $perc = ($right_answers / $num_answers);
                    }
                    $xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<correct_percent>'.F_formatXMLPercentage($perc).'</correct_percent>'.K_NEWLINE;
                    $xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<wrong>'.$wrong_answers.'</wrong>'.K_NEWLINE;
                    $perc = 0;
                    if ($num_answers > 0) {
                        $perc = round($wrong_answers / $num_answers);
                    }
                    $xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<wrong_percent>'.F_formatXMLPercentage($perc).'</wrong_percent>'.K_NEWLINE;
                    $xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<unanswered>'.$unanswered.'</unanswered>'.K_NEWLINE;
                    $perc = 0;
                    if ($num_answers > 0) {
                        $perc = round($unanswered / $num_answers);
                    }
                    $xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<unanswered_percent>'.F_formatXMLPercentage($perc).'</unanswered_percent>'.K_NEWLINE;
                    $xml .= K_TAB.K_TAB.K_TAB.'</answer>'.K_NEWLINE;
                }
            } else {
                F_display_db_error();
            }
            $xml .= K_TAB.K_TAB.'</question>'.K_NEWLINE;
        }
    } else {
        F_display_db_error();
    }

    $xml .= K_TAB.'</body>'.K_NEWLINE;
    $xml .= '</tcexamquestionstats>'.K_NEWLINE;

    return $xml;
}

//============================================================+
// END OF FILE
//============================================================+
