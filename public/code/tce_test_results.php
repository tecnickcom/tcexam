<?php
//============================================================+
// File name   : tce_test_results.php
// Begin       : 2004-06-10
// Last Update : 2011-05-24
//
// Description : Display test results to the current user.
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
//    Copyright (C) 2004-2010  Nicola Asuni - Tecnick.com LTD
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
//    Additionally, you can't remove, move or hide the original TCExam logo,
//    copyrights statements and links to Tecnick.com and TCExam websites.
//
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Display test results to the current user.
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
 * @since 2004-06-10
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_PUBLIC_TEST_RESULTS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_test_results'];
require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../../shared/code/tce_functions_test.php');
require_once('../../shared/code/tce_functions_test_stats.php');

$user_id = intval($_SESSION['session_user_id']);

if (isset($_REQUEST['testid']) and ($_REQUEST['testid'] > 0)) {
    $test_id = intval($_REQUEST['testid']);
} else {
    header('Location: index.php'); //redirect browser to public main page
    exit;
}

// get test basic score
$test_basic_score = 1;

$testdata = F_getTestData($test_id);
if (!F_getBoolean($testdata['test_results_to_users'])) {
    exit;
}
$test_basic_score = $testdata['test_score_right'];
//lock user's test
F_lockUserTest($test_id, $_SESSION['session_user_id']);
// get user's test stats
$usrtestdata = F_getUserTestStat($test_id, $user_id);
$userdata = F_getUserData($user_id);


echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;

$usr_all = htmlspecialchars($userdata['user_lastname'].' '.$userdata['user_firstname'].' - '.$userdata['user_name'].'', ENT_NOQUOTES, $l['a_meta_charset']);
echo getFormDescriptionLine($l['w_user'].':', $l['w_user'], $usr_all);

$test_all = '<strong>'.htmlspecialchars($testdata['test_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</strong><br />'.K_NEWLINE;
$test_all .=htmlspecialchars($testdata['test_description'], ENT_NOQUOTES, $l['a_meta_charset']);
echo getFormDescriptionLine($l['w_test'].':', $l['w_test'], $test_all);

echo getFormDescriptionLine($l['w_time_begin'].':', $l['h_time_begin'], $usrtestdata['test_start_time']);
echo getFormDescriptionLine($l['w_time_end'].':', $l['h_time_end'], $usrtestdata['test_end_time']);

if (!isset($usrtestdata['test_end_time']) or ($usrtestdata['test_end_time'] <= 0)) {
    $time_diff = $testdata['test_duration_time'] * 60;
} else {
    $time_diff = strtotime($usrtestdata['test_end_time']) - strtotime($usrtestdata['test_start_time']); //sec
}
$time_diff = gmdate('H:i:s', $time_diff);
echo getFormDescriptionLine($l['w_test_time'].':', $l['w_test_time'], $time_diff);

$passmsg = '';
if ($usrtestdata['score_threshold'] > 0) {
    if ($usrtestdata['score'] >= $usrtestdata['score_threshold']) {
        $passmsg = ' - '.$l['w_passed'];
    } else {
        $passmsg = ' - '.$l['w_not_passed'];
    }
}
$score_all = $usrtestdata['score'].' / '.$usrtestdata['max_score'].' ('.round(100 * $usrtestdata['score'] / $usrtestdata['max_score']).'%)'.$passmsg;
echo getFormDescriptionLine($l['w_score'].':', $l['h_score_total'], $score_all);

$score_right_all = $usrtestdata['right'].' / '.$usrtestdata['all'].' ('.round(100 * $usrtestdata['right'] / $usrtestdata['all']).'%)';
echo getFormDescriptionLine($l['w_answers_right'].':', $l['h_answers_right'], $score_right_all);

echo getFormDescriptionLine($l['w_comment'].':', $l['h_testcomment'], F_decode_tcecode($usrtestdata['comment']));

if (F_getBoolean($testdata['test_report_to_users'])) {
    echo '<div class="rowl">'.K_NEWLINE;

    $topicresults = array(); // per-topic results
    $testuser_id = $usrtestdata['testuser_id'];
    if (isset($testuser_id) and (!empty($testuser_id))) {
        // display user questions
        $sql = 'SELECT *
			FROM '.K_TABLE_QUESTIONS.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_SUBJECTS.', '.K_TABLE_MODULES.'
			WHERE question_id=testlog_question_id
				AND testlog_testuser_id='.$testuser_id.'
				AND question_subject_id=subject_id
				AND subject_module_id=module_id
			ORDER BY testlog_id';
        if ($r = F_db_query($sql, $db)) {
            echo '<ol class="question">'.K_NEWLINE;
            while ($m = F_db_fetch_array($r)) {
                // create per-topic results array
                if (!array_key_exists($m['module_id'], $topicresults)) {
                    $topicresults[$m['module_id']] = array();
                    $topicresults[$m['module_id']]['name'] = $m['module_name'];
                    $topicresults[$m['module_id']]['num'] = 0;
                    $topicresults[$m['module_id']]['right'] = 0;
                    $topicresults[$m['module_id']]['wrong'] = 0;
                    $topicresults[$m['module_id']]['unanswered'] = 0;
                    $topicresults[$m['module_id']]['undisplayed'] = 0;
                    $topicresults[$m['module_id']]['unrated'] = 0;
                    $topicresults[$m['module_id']]['score'] = 0;
                    $topicresults[$m['module_id']]['maxscore'] = 0;
                    $topicresults[$m['module_id']]['subjects'] = array();
                }
                if (!array_key_exists($m['subject_id'], $topicresults[$m['module_id']]['subjects'])) {
                    $topicresults[$m['module_id']]['subjects'][$m['subject_id']] = array();
                    $topicresults[$m['module_id']]['subjects'][$m['subject_id']]['name'] = $m['subject_name'];
                    $topicresults[$m['module_id']]['subjects'][$m['subject_id']]['num'] = 0;
                    $topicresults[$m['module_id']]['subjects'][$m['subject_id']]['right'] = 0;
                    $topicresults[$m['module_id']]['subjects'][$m['subject_id']]['wrong'] = 0;
                    $topicresults[$m['module_id']]['subjects'][$m['subject_id']]['unanswered'] = 0;
                    $topicresults[$m['module_id']]['subjects'][$m['subject_id']]['undisplayed'] = 0;
                    $topicresults[$m['module_id']]['subjects'][$m['subject_id']]['unrated'] = 0;
                    $topicresults[$m['module_id']]['subjects'][$m['subject_id']]['score'] = 0;
                    $topicresults[$m['module_id']]['subjects'][$m['subject_id']]['maxscore'] = 0;
                }
                $question_max_score = ($m['question_difficulty'] * $test_basic_score);
                // total number of questions
                $topicresults[$m['module_id']]['num'] += 1;
                $topicresults[$m['module_id']]['subjects'][$m['subject_id']]['num'] += 1;
                // number of right answers
                if ($m['testlog_score'] > ($question_max_score / 2)) {
                    $topicresults[$m['module_id']]['right'] += 1;
                    $topicresults[$m['module_id']]['subjects'][$m['subject_id']]['right'] += 1;
                } else {
                    // number of wrong answers
                    $topicresults[$m['module_id']]['wrong'] += 1;
                    $topicresults[$m['module_id']]['subjects'][$m['subject_id']]['wrong'] += 1;
                }
                // total number of unanswered questions
                if (strlen($m['testlog_change_time']) <= 0) {
                    $topicresults[$m['module_id']]['unanswered'] += 1;
                    $topicresults[$m['module_id']]['subjects'][$m['subject_id']]['unanswered'] += 1;
                }
                // total number of undisplayed questions
                if (strlen($m['testlog_display_time']) <= 0) {
                    $topicresults[$m['module_id']]['undisplayed'] += 1;
                    $topicresults[$m['module_id']]['subjects'][$m['subject_id']]['undisplayed'] += 1;
                }
                // number of free-text unrated questions
                if (strlen($m['testlog_score']) <= 0) {
                    $topicresults[$m['module_id']]['unrated'] += 1;
                    $topicresults[$m['module_id']]['subjects'][$m['subject_id']]['unrated'] += 1;
                }
                // score
                $topicresults[$m['module_id']]['score'] += $m['testlog_score'];
                $topicresults[$m['module_id']]['subjects'][$m['subject_id']]['score'] += $m['testlog_score'];
                // max score
                $topicresults[$m['module_id']]['maxscore'] += $question_max_score;
                $topicresults[$m['module_id']]['subjects'][$m['subject_id']]['maxscore'] += $question_max_score;

                echo '<li>'.K_NEWLINE;
                // display question stats
                echo '<strong>['.$m['testlog_score'].']'.K_NEWLINE;
                echo ' (';
                echo 'IP:'.getIpAsString($m['testlog_user_ip']).K_NEWLINE;
                if (isset($m['testlog_display_time']) and (strlen($m['testlog_display_time']) > 0)) {
                    echo ' | '.substr($m['testlog_display_time'], 11, 8).K_NEWLINE;
                } else {
                    echo ' | --:--:--'.K_NEWLINE;
                }
                if (isset($m['testlog_change_time']) and (strlen($m['testlog_change_time']) > 0)) {
                    echo ' | '.substr($m['testlog_change_time'], 11, 8).K_NEWLINE;
                } else {
                    echo ' | --:--:--'.K_NEWLINE;
                }
                if (isset($m['testlog_display_time']) and isset($m['testlog_change_time'])) {
                    echo ' | '.date('i:s', (strtotime($m['testlog_change_time']) - strtotime($m['testlog_display_time']))).'';
                } else {
                    echo ' | --:--'.K_NEWLINE;
                }
                if (isset($m['testlog_reaction_time']) and ($m['testlog_reaction_time'] > 0)) {
                    echo ' | '.($m['testlog_reaction_time']/1000).'';
                } else {
                    echo ' | ------'.K_NEWLINE;
                }
                echo ')</strong>'.K_NEWLINE;
                echo '<br />'.K_NEWLINE;
                // display question description
                echo F_decode_tcecode($m['question_description']).K_NEWLINE;
                if (K_ENABLE_QUESTION_EXPLANATION and !empty($m['question_explanation'])) {
                    echo '<br /><span class="explanation">'.$l['w_explanation'].':</span><br />'.F_decode_tcecode($m['question_explanation']).''.K_NEWLINE;
                }
                if ($m['question_type'] == 3) {
                    // TEXT
                    echo '<ul class="answer"><li>'.K_NEWLINE;
                    echo F_decode_tcecode($m['testlog_answer_text']);
                    echo '&nbsp;</li></ul>'.K_NEWLINE;
                } else {
                    echo '<ol class="answer">'.K_NEWLINE;
                    // display each answer option
                    $sqla = 'SELECT *
						FROM '.K_TABLE_LOG_ANSWER.', '.K_TABLE_ANSWERS.'
						WHERE logansw_answer_id=answer_id
							AND logansw_testlog_id=\''.$m['testlog_id'].'\'
						ORDER BY logansw_order';
                    if ($ra = F_db_query($sqla, $db)) {
                        while ($ma = F_db_fetch_array($ra)) {
                            echo '<li>';
                            if ($m['question_type'] == 4) {
                                // ORDER
                                if ($ma['logansw_position'] > 0) {
                                    if ($ma['logansw_position'] == $ma['answer_position']) {
                                        echo '<acronym title="'.$l['h_answer_right'].'" class="okbox">'.$ma['logansw_position'].'</acronym>';
                                    } else {
                                        echo '<acronym title="'.$l['h_answer_wrong'].'" class="nobox">'.$ma['logansw_position'].'</acronym>';
                                    }
                                } else {
                                    echo '<acronym title="'.$l['m_unanswered'].'" class="offbox">&nbsp;</acronym>';
                                }
                            } elseif ($ma['logansw_selected'] > 0) {
                                if (F_getBoolean($ma['answer_isright'])) {
                                    echo '<acronym title="'.$l['h_answer_right'].'" class="okbox">x</acronym>';
                                } else {
                                    echo '<acronym title="'.$l['h_answer_wrong'].'" class="nobox">x</acronym>';
                                }
                            } elseif ($m['question_type'] == 1) {
                                // MCSA
                                echo '<acronym title="-" class="offbox">&nbsp;</acronym>';
                            } else {
                                if ($ma['logansw_selected'] == 0) {
                                    if (F_getBoolean($ma['answer_isright'])) {
                                        echo '<acronym title="'.$l['h_answer_wrong'].'" class="nobox">&nbsp;</acronym>';
                                    } else {
                                        echo '<acronym title="'.$l['h_answer_right'].'" class="okbox">&nbsp;</acronym>';
                                    }
                                } else {
                                    echo '<acronym title="'.$l['m_unanswered'].'" class="offbox">&nbsp;</acronym>';
                                }
                            }
                            echo '&nbsp;';
                            if ($m['question_type'] == 4) {
                                echo '<acronym title="'.$l['w_position'].'" class="onbox">'.$ma['answer_position'].'</acronym>';
                            } elseif (F_getBoolean($ma['answer_isright'])) {
                                echo '<acronym title="'.$l['w_answers_right'].'" class="onbox">&reg;</acronym>';
                            } else {
                                echo '<acronym title="'.$l['w_answers_wrong'].'" class="offbox">&nbsp;</acronym>';
                            }
                            echo ' ';
                            echo F_decode_tcecode($ma['answer_description']);
                            if (K_ENABLE_ANSWER_EXPLANATION and !empty($ma['answer_explanation'])) {
                                echo '<br /><span class="explanation">'.$l['w_explanation'].':</span><br />'.F_decode_tcecode($ma['answer_explanation']).''.K_NEWLINE;
                            }
                            echo '</li>'.K_NEWLINE;
                        }
                    } else {
                        F_display_db_error();
                    }
                    echo '</ol>'.K_NEWLINE;
                } // end multiple answers
                // display teacher/supervisor comment to the question
                if (isset($m['testlog_comment']) and (!empty($m['testlog_comment']))) {
                    echo '<ul class="answer"><li class="comment">'.K_NEWLINE;
                    echo F_decode_tcecode($m['testlog_comment']);
                    echo '&nbsp;</li></ul>'.K_NEWLINE;
                }
                echo '<br /><br />'.K_NEWLINE;
                echo '</li>'.K_NEWLINE;
            }
            echo '</ol>'.K_NEWLINE;
        } else {
            F_display_db_error();
        }
    }
    echo '</div>'.K_NEWLINE;


    // print per-topic results
    echo '<div class="rowl">'.K_NEWLINE;
    echo '<hr />'.K_NEWLINE;
    echo '<h2>'.$l['w_subjects'].'</h2>';
    echo '<ul>';
    foreach ($topicresults as $res_module) {
        echo '<li>';
        $score_percent = round(100 * $res_module['score'] / $res_module['maxscore']);
        echo '<acronym title="'.$l['w_score'].'" class="';
        if ($score_percent > 50) {
            echo 'okbox';
        } else {
            echo 'nobox';
        }
        echo '">'.$res_module['score'].' / '.$res_module['maxscore'].' ('.$score_percent.'%)</acronym>';
        $score_percent = round(100 * $res_module['right'] / $res_module['num']);
        echo ' <acronym title="'.$l['w_answers_right'].'" class="';
        if ($score_percent > 50) {
            echo 'okbox';
        } else {
            echo 'nobox';
        }
        echo '">'.$res_module['right'].' / '.$res_module['num'].' ('.$score_percent.'%)</acronym>';
        echo ' <strong>'.$res_module['name'].'</strong>';
        echo '<ul>';
        foreach ($res_module['subjects'] as $res_subject) {
            echo '<li>';
            $score_percent = round(100 * $res_subject['score'] / $res_subject['maxscore']);
            echo '<acronym title="'.$l['w_score'].'" class="';
            if ($score_percent > 50) {
                echo 'okbox';
            } else {
                echo 'nobox';
            }
            echo '">'.$res_subject['score'].' / '.$res_subject['maxscore'].' ('.$score_percent.'%)</acronym>';
            $score_percent = round(100 * $res_subject['right'] / $res_subject['num']);
            echo ' <acronym title="'.$l['w_answers_right'].'" class="';
            if ($score_percent > 50) {
                echo 'okbox';
            } else {
                echo 'nobox';
            }
            echo '">'.$res_subject['right'].' / '.$res_subject['num'].' ('.$score_percent.'%)</acronym>';
            echo ' '.$res_subject['name'];
            echo '</li>'.K_NEWLINE;
        }
        echo '</ul>';
        echo '</li>'.K_NEWLINE;
    }
    echo '</ul>';
    echo '<hr />'.K_NEWLINE;
    echo '</div>'.K_NEWLINE;

    if (K_ENABLE_PUBLIC_PDF) {
        echo '<div class="row">'.K_NEWLINE;
        // PDF button
        echo '<a href="'.pdfLink(3, $test_id, 0, $user_id, '', 0).'" class="xmlbutton" title="'.$l['h_pdf'].'">'.$l['w_pdf'].'</a> ';
        echo '</div>'.K_NEWLINE;
    }
}

echo '</div>'.K_NEWLINE;

echo '<a href="index.php" title="'.$l['h_index'].'">&lt; '.$l['w_index'].'</a>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_result_user'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
