<?php
//============================================================+
// File name   : tce_show_all_questions.php
// Begin       : 2005-07-06
// Last Update : 2014-03-04
//
// Description : Display all questions grouped by topic.
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
//    Copyright (C) 2004-2014 Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Display all questions grouped by topic.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2005-07-06
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_RESULTS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_questions_list'];
require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../../shared/code/tce_functions_auth_sql.php');
require_once('tce_functions_questions.php');

// --- Initialize variables

// set default values
$wherequery='';
$order_field = 'question_enabled DESC, question_position,';
if (K_DATABASE_TYPE == 'ORACLE') {
    $order_field .= ' CAST(question_description as varchar2(100))';
} else {
    $order_field .= ' question_description';
}
if (!isset($orderdir)) {
    $orderdir=0;
}
if (!isset($firstrow)) {
    $firstrow=0;
}
if (!isset($rowsperpage)) {
    $rowsperpage=K_MAX_ROWS_PER_PAGE;
}
if (!isset($hide_answers)) {
    $hide_answers=false;
}

if (isset($selectmodule)) {
    $changemodule = 1;
}
if (isset($selectcategory)) {
    $changecategory = 1;
}
if ((isset($changemodule) and ($changemodule > 0)) or (isset($changecategory) and ($changecategory > 0))) {
    $wherequery = '';
    $firstrow = 0;
    $orderdir = 0;
    $order_field = 'question_enabled DESC, question_position,';
    if (K_DATABASE_TYPE == 'ORACLE') {
        $order_field .= ' CAST(question_description as varchar2(100))';
    } else {
        $order_field .= ' question_description';
    }
}
if (isset($subject_module_id)) {
    $subject_module_id = intval($subject_module_id);
} else {
    // select default module/subject (if not specified)
    $sql = F_select_modules_sql().' LIMIT 1';
    if ($r = F_db_query($sql, $db)) {
        if ($m = F_db_fetch_array($r)) {
            $subject_module_id = $m['module_id'];
        } else {
            $subject_module_id = 0;
        }
    } else {
        F_display_db_error();
    }
}

// check user's authorization
if (!F_isAuthorizedUser(K_TABLE_MODULES, 'module_id', $subject_module_id, 'module_user_id')) {
    F_print_error('ERROR', $l['m_authorization_denied']);
    require_once('../code/tce_page_footer.php');
    exit;
}

if (isset($subject_id)) {
    $subject_id = intval($subject_id);
}

// select subject
if ((isset($changemodule) and ($changemodule > 0))
    or (!(isset($subject_id) and ($subject_id > 0)))) {
    $sql = F_select_subjects_sql('subject_module_id='.$subject_module_id.'').' LIMIT 1';
    if ($r = F_db_query($sql, $db)) {
        if ($m = F_db_fetch_array($r)) {
            $subject_id = $m['subject_id'];
        } else {
            $subject_id = 0;
        }
    } else {
        F_display_db_error();
    }
}

if (isset($menu_mode) and ($menu_mode == 'update') and isset($menu_action) and !empty($menu_action)) {
    $istart = 1 + $firstrow;
    $iend = $rowsperpage + $firstrow;
    for ($i = $istart; $i <= $iend; $i++) {
        // for each selected question
        $keyname = 'questionid'.$i;
        if (isset($$keyname)) {
            $question_id = $$keyname;
            switch ($menu_action) {
                case 'move': {
                    if (isset($new_subject_id) and ($new_subject_id > 0)) {
                        F_question_copy($question_id, $new_subject_id);
                        F_question_delete($question_id, $subject_id);
                    }
                    break;
                }
                case 'copy': {
                    if (isset($new_subject_id) and ($new_subject_id > 0)) {
                        F_question_copy($question_id, $new_subject_id);
                    }
                    break;
                }
                case 'delete': {
                    F_question_delete($question_id, $subject_id);
                    break;
                }
                case 'disable': {
                    F_question_set_enabled($question_id, false);
                    break;
                }
                case 'enable': {
                    F_question_set_enabled($question_id, true);
                    break;
                }
            } // end of switch
        }
    }
    F_print_error('MESSAGE', $l['m_updated']);
}

echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_selectquestions">'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="subject_module_id">'.$l['w_module'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<input type="hidden" name="changemodule" id="changemodule" value="" />'.K_NEWLINE;
echo '<select name="subject_module_id" id="subject_module_id" size="0" onchange="document.getElementById(\'form_selectquestions\').changemodule.value=1;document.getElementById(\'form_selectquestions\').changecategory.value=1; document.getElementById(\'form_selectquestions\').submit();" title="'.$l['w_module'].'">'.K_NEWLINE;
$sql = F_select_modules_sql();
if ($r = F_db_query($sql, $db)) {
    $countitem = 1;
    while ($m = F_db_fetch_array($r)) {
        echo '<option value="'.$m['module_id'].'"';
        if ($m['module_id'] == $subject_module_id) {
            echo ' selected="selected"';
        }
        echo '>'.$countitem.'. ';
        if (F_getBoolean($m['module_enabled'])) {
            echo '+';
        } else {
            echo '-';
        }
        echo ' '.htmlspecialchars($m['module_name'], ENT_NOQUOTES, $l['a_meta_charset']).'&nbsp;</option>'.K_NEWLINE;
        $countitem++;
    }
    if ($countitem == 1) {
        echo '<option value="0">&nbsp;</option>'.K_NEWLINE;
    }
} else {
    echo '</select></span></div>'.K_NEWLINE;
    F_display_db_error();
}
echo '</select>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormNoscriptSelect('selectmodule');

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="subject_id">'.$l['w_subject'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<input type="hidden" name="changecategory" id="changecategory" value="" />'.K_NEWLINE;
echo '<select name="subject_id" id="subject_id" size="0" onchange="document.getElementById(\'form_selectquestions\').changecategory.value=1;document.getElementById(\'form_selectquestions\').submit()" title="'.$l['h_subject'].'">'.K_NEWLINE;
$sql = F_select_subjects_sql('subject_module_id='.$subject_module_id);
if ($r = F_db_query($sql, $db)) {
    $countitem = 1;
    while ($m = F_db_fetch_array($r)) {
        echo '<option value="'.$m['subject_id'].'"';
        if ($m['subject_id'] == $subject_id) {
            echo ' selected="selected"';
        }
        echo '>'.$countitem.'. ';
        if (F_getBoolean($m['subject_enabled'])) {
            echo '+';
        } else {
            echo '-';
        }
        echo ' '.htmlspecialchars($m['subject_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
        $countitem++;
    }
} else {
    echo '</select></span></div>'.K_NEWLINE;
    F_display_db_error();
}
echo '</select>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormNoscriptSelect('selectcategory');

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">&nbsp;</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<input type="checkbox" name="hide_answers" id="hide_answers" value="1"';
if ($hide_answers) {
    echo ' checked="checked"';
}
echo ' title="'.$l['w_hide_answers'].'" onclick="document.getElementById(\'form_selectquestions\').submit()" />';
echo '<label for="hide_answers">'.$l['w_hide_answers'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormNoscriptSelect('selectrecord');

echo '<div class="row"><hr /></div>'.K_NEWLINE;

// display questions statistics
$qtype = array('<acronym class="offbox" title="'.$l['w_single_answer'].'">S</acronym>', '<acronym class="offbox" title="'.$l['w_multiple_answers'].'">M</acronym>', '<acronym class="offbox" title="'.$l['w_free_answer'].'">T</acronym>', '<acronym class="offbox" title="'.$l['w_ordering_answer'].'">O</acronym>'); // question types
$qstat = '';
$nqsum = 0;
$sql = 'SELECT question_type, COUNT(*) as numquestions
	FROM '.K_TABLE_QUESTIONS.'
	WHERE question_subject_id='.$subject_id.'
	GROUP BY question_type';
if ($r = F_db_query($sql, $db)) {
    $countitem = 1;
    while ($m = F_db_fetch_array($r)) {
        $nqsum += $m['numquestions'];
        $qstat .= ' + '.$m['numquestions'].' '.$qtype[($m['question_type']-1)].'';
    }
} else {
    F_display_db_error();
}

echo '<div class="rowl">';
echo '<span>'.$l['w_questions'].': '.$nqsum.' = '.$qstat.'</span><br />'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="row"><hr /></div>'.K_NEWLINE;

echo '<div class="rowl">'.K_NEWLINE;

if (isset($subject_id) and ($subject_id > 0)) {
    F_show_select_questions($wherequery, $subject_module_id, $subject_id, $order_field, $orderdir, $firstrow, $rowsperpage, $hide_answers);
}

echo '&nbsp;'.K_NEWLINE;
echo '</div>'.K_NEWLINE;
echo '<div class="row"><hr /></div>'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;

// show buttons by case
if (isset($subject_id) and ($subject_id > 0)) {
    $pdflink = 'tce_pdf_all_questions.php';
    $pdflink .= '?module_id='.$subject_module_id;
    $pdflink .= '&amp;subject_id='.$subject_id;
    $pdflink .= '&amp;hide_answers='.intval($hide_answers); // hide answers option
    echo '<a href="'.$pdflink.'&amp;expmode=1" class="xmlbutton" title="'.$l['h_pdf'].'">PDF</a>';
    echo '<a href="'.$pdflink.'&amp;expmode=2" class="xmlbutton" title="'.$l['h_pdf'].'">PDF '.$l['w_module'].'</a>';
    echo '<a href="'.$pdflink.'&amp;expmode=3" class="xmlbutton" title="'.$l['h_pdf'].'">PDF '.$l['w_all'].'</a>';
    $xmllink = 'tce_xml_questions.php';
    $xmllink .= '?module_id='.$subject_module_id;
    $xmllink .= '&amp;subject_id='.$subject_id;
    echo ' <a href="'.$xmllink.'&amp;expmode=1" class="xmlbutton" title="'.$l['h_xml_export'].'">XML</a>';
    echo '<a href="'.$xmllink.'&amp;expmode=2" class="xmlbutton" title="'.$l['h_xml_export'].'">XML '.$l['w_module'].'</a>';
    echo '<a href="'.$xmllink.'&amp;expmode=3" class="xmlbutton" title="'.$l['h_xml_export'].'">XML '.$l['w_all'].'</a>';
    echo ' <a href="'.$xmllink.'&amp;expmode=1&amp;format=JSON" class="xmlbutton" title="JSON">JSON</a>';
    echo '<a href="'.$xmllink.'&amp;expmode=2&amp;format=JSON" class="xmlbutton" title="JSON">JSON '.$l['w_module'].'</a>';
    echo '<a href="'.$xmllink.'&amp;expmode=3&amp;format=JSON" class="xmlbutton" title="JSON">JSON '.$l['w_all'].'</a>';
    $tsvlink = 'tce_tsv_questions.php';
    $tsvlink .= '?module_id='.$subject_module_id;
    $tsvlink .= '&amp;subject_id='.$subject_id;
    echo ' <a href="'.$tsvlink.'&amp;expmode=1" class="xmlbutton" title="'.$l['h_tsv_export'].'">TSV</a>';
    echo '<a href="'.$tsvlink.'&amp;expmode=2" class="xmlbutton" title="'.$l['h_tsv_export'].'">TSV '.$l['w_module'].'</a>';
    echo '<a href="'.$tsvlink.'&amp;expmode=3" class="xmlbutton" title="'.$l['h_tsv_export'].'">TSV '.$l['w_all'].'</a>';
}

echo '&nbsp;'.K_NEWLINE;
echo '<input type="hidden" name="firstrow" id="firstrow" value="'.$firstrow.'" />'.K_NEWLINE;
echo '<input type="hidden" name="order_field" id="order_field" value="'.$order_field.'" />'.K_NEWLINE;
echo '<input type="hidden" name="orderdir" id="orderdir" value="'.$orderdir.'" />'.K_NEWLINE;
echo '<input type="hidden" name="submitted" id="submitted" value="0" />'.K_NEWLINE;
echo '<input type="hidden" name="usersearch" id="usersearch" value="" />'.K_NEWLINE;
echo '</div>'.K_NEWLINE;
echo F_getCSRFTokenField().K_NEWLINE;
echo '</form>'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_select_all_questions'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

// ------------------------------

/**
 * Display a list of selected questions.
 * @author Nicola Asuni
 * @since 2005-07-06
 * @param $wherequery (string) question selection query
 * @param $subject_module_id (string) module ID
 * @param $subject_id (string) topic ID
 * @param $order_field (string) order by column name
 * @param $orderdir (int) oreder direction
 * @param $firstrow (int) number of first row to display
 * @param $rowsperpage (int) number of rows per page
 * @param $hide_answers (boolean) if true hide answers
 * @return false in case of empty database, true otherwise
 */
function F_show_select_questions($wherequery, $subject_module_id, $subject_id, $order_field, $orderdir, $firstrow, $rowsperpage, $hide_answers = false)
{
    global $l, $db;
    require_once('../config/tce_config.php');
    require_once('../../shared/code/tce_functions_page.php');

    $subject_module_id = intval($subject_module_id);
    $subject_id = intval($subject_id);
    $orderdir = intval($orderdir);
    $firstrow = intval($firstrow);
    $rowsperpage = intval($rowsperpage);
    if (empty($order_field) or (!in_array($order_field, array('question_id', 'question_subject_id', 'question_description', 'question_explanation', 'question_type', 'question_difficulty', 'question_enabled', 'question_position', 'question_timer', 'question_fullscreen', 'question_inline_answers', 'question_auto_next', 'question_enabled DESC, question_position, CAST(question_description as varchar2(100))', 'question_enabled DESC, question_position, question_description')))) {
        $order_field = 'question_description';
    }
    if ($orderdir == 0) {
        $nextorderdir = 1;
        $full_order_field = $order_field;
    } else {
        $nextorderdir = 0;
        $full_order_field = $order_field.' DESC';
    }

    if (!F_count_rows(K_TABLE_QUESTIONS)) { //if the table is void (no items) display message
        F_print_error('MESSAGE', $l['m_databasempty']);
        return false;
    }

    if (empty($wherequery)) {
        $wherequery = 'WHERE question_subject_id='.$subject_id.'';
    } else {
        $wherequery = F_escape_sql($db, $wherequery);
        $wherequery .= ' AND question_subject_id='.$subject_id.'';
    }
    $sql = 'SELECT *
		FROM '.K_TABLE_QUESTIONS.'
		'.$wherequery.'
		ORDER BY '.$full_order_field;
    if (K_DATABASE_TYPE == 'ORACLE') {
        $sql = 'SELECT * FROM ('.$sql.') WHERE rownum BETWEEN '.$firstrow.' AND '.($firstrow + $rowsperpage).'';
    } else {
        $sql .= ' LIMIT '.$rowsperpage.' OFFSET '.$firstrow.'';
    }
    if ($r = F_db_query($sql, $db)) {
        $questlist = '';
        $itemcount = $firstrow;
        while ($m = F_db_fetch_array($r)) {
            $itemcount++;
            $questlist .= '<li>'.K_NEWLINE;
            $questlist .= '<strong>'.$itemcount.'.</strong> ';
            $questlist .= '<input type="checkbox" name="questionid'.$itemcount.'" id="questionid'.$itemcount.'" value="'.$m['question_id'].'" title="'.$l['w_select'].'"';
            if (isset($_REQUEST['checkall']) and ($_REQUEST['checkall'] == 1)) {
                $questlist .= ' checked="checked"';
            }
            $questlist .= ' />';
            // display question description
            if (F_getBoolean($m['question_enabled'])) {
                $questlist .= '<acronym class="onbox" title="'.$l['w_enabled'].'">+</acronym>';
            } else {
                $questlist .= '<acronym class="offbox" title="'.$l['w_disabled'].'">-</acronym>';
            }
            switch ($m['question_type']) {
                case 1: {
                    $questlist .= ' <acronym class="offbox" title="'.$l['w_single_answer'].'">S</acronym>';
                    break;
                }
                case 2: {
                    $questlist .= ' <acronym class="offbox" title="'.$l['w_multiple_answers'].'">M</acronym>';
                    break;
                }
                case 3: {
                    $questlist .= ' <acronym class="offbox" title="'.$l['w_free_answer'].'">T</acronym>';
                    break;
                }
                case 4: {
                    $questlist .= ' <acronym class="offbox" title="'.$l['w_ordering_answer'].'">O</acronym>';
                    break;
                }
            }
            $questlist .= ' <acronym class="offbox" title="'.$l['h_question_difficulty'].'">'.$m['question_difficulty'].'</acronym>';
            if ($m['question_position'] > 0) {
                $questlist .= ' <acronym class="onbox" title="'.$l['h_position'].'">'.intval($m['question_position']).'</acronym>';
            } else {
                $questlist .= ' <acronym class="offbox" title="'.$l['h_position'].'">&nbsp;</acronym>';
            }
            if (F_getBoolean($m['question_fullscreen'])) {
                $questlist .= ' <acronym class="onbox" title="'.$l['w_fullscreen'].': '.$l['w_enabled'].'">F</acronym>';
            } else {
                $questlist .= ' <acronym class="offbox" title="'.$l['w_fullscreen'].': '.$l['w_disabled'].'">&nbsp;</acronym>';
            }
            if (F_getBoolean($m['question_inline_answers'])) {
                $questlist .= ' <acronym class="onbox" title="'.$l['w_inline_answers'].': '.$l['w_enabled'].'">I</acronym>';
            } else {
                $questlist .= ' <acronym class="offbox" title="'.$l['w_inline_answers'].': '.$l['w_disabled'].'">&nbsp;</acronym>';
            }
            if (F_getBoolean($m['question_auto_next'])) {
                $questlist .= ' <acronym class="onbox" title="'.$l['w_auto_next'].': '.$l['w_enabled'].'">A</acronym>';
            } else {
                $questlist .= ' <acronym class="offbox" title="'.$l['w_auto_next'].': '.$l['w_disabled'].'">&nbsp;</acronym>';
            }
            if ($m['question_timer'] > 0) {
                $questlist .= ' <acronym class="onbox" title="'.$l['h_question_timer'].'">'.intval($m['question_timer']).'</acronym>';
            } else {
                $questlist .= ' <acronym class="offbox" title="'.$l['h_question_timer'].'">&nbsp;</acronym>';
            }

            $questlist .= ' <a href="tce_edit_question.php?subject_module_id='.$subject_module_id.'&amp;question_subject_id='.$subject_id.'&amp;question_id='.$m['question_id'].'" title="'.$l['t_questions_editor'].' [ID = '.$m['question_id'].']" class="xmlbutton">'.$l['w_edit'].'</a>';

            $questlist .= '<br /><br />'.K_NEWLINE;
            $questlist .=  '<div class="paddingleft">'.F_decode_tcecode($m['question_description']).'</div>'.K_NEWLINE;
            if (K_ENABLE_QUESTION_EXPLANATION and !empty($m['question_explanation'])) {
                $questlist .=  '<div class="paddingleft"><br /><span class="explanation">'.$l['w_explanation'].':</span><br />'.F_decode_tcecode($m['question_explanation']).'</div>'.K_NEWLINE;
            }
            if (!$hide_answers) {
                // display alternative answers
                $sqla = 'SELECT *
					FROM '.K_TABLE_ANSWERS.'
					WHERE answer_question_id=\''.$m['question_id'].'\'
					ORDER BY answer_enabled DESC,answer_position,answer_isright DESC';
                if ($ra = F_db_query($sqla, $db)) {
                    $answlist = '';
                    while ($ma = F_db_fetch_array($ra)) {
                        $answlist .= '<li>';
                        if (F_getBoolean($ma['answer_enabled'])) {
                            $answlist .= '<acronym class="onbox" title="'.$l['w_enabled'].'">+</acronym>';
                        } else {
                            $answlist .= '<acronym class="offbox" title="'.$l['w_disabled'].'">-</acronym>';
                        }
                        if ($m['question_type'] != 4) {
                            if (F_getBoolean($ma['answer_isright'])) {
                                $answlist .= ' <acronym class="okbox" title="'.$l['h_answer_right'].'">T</acronym>';
                            } else {
                                $answlist .= ' <acronym class="nobox" title="'.$l['h_answer_wrong'].'">F</acronym>';
                            }
                        }
                        if ($ma['answer_position'] > 0) {
                            $answlist .= ' <acronym class="onbox" title="'.$l['h_position'].'">'.intval($ma['answer_position']).'</acronym>';
                        } else {
                            $answlist .= ' <acronym class="offbox" title="'.$l['h_position'].'">&nbsp;</acronym>';
                        }
                        if ($ma['answer_keyboard_key'] > 0) {
                            $answlist .= ' <acronym class="onbox" title="'.$l['h_answer_keyboard_key'].'">'.F_text_to_xml(chr($ma['answer_keyboard_key'])).'</acronym>';
                        } else {
                            $answlist .= ' <acronym class="offbox" title="'.$l['h_answer_keyboard_key'].'">&nbsp;</acronym>';
                        }

                        $answlist .= ' <a href="tce_edit_answer.php?subject_module_id='.$subject_module_id.'&amp;question_subject_id='.$subject_id.'&amp;answer_question_id='.$m['question_id'].'&amp;answer_id='.$ma['answer_id'].'" title="'.$l['t_answers_editor'].' [ID = '.$ma['answer_id'].']" class="xmlbutton">'.$l['w_edit'].'</a>';
                        //$answlist .= " ";
                        //$answlist .= "".F_decode_tcecode($ma['answer_description'])."";
                        $answlist .= '<br /><br />'.K_NEWLINE;
                        $answlist .= '<div class="paddingleft">'.F_decode_tcecode($ma['answer_description']).'</div>'.K_NEWLINE;
                        if (K_ENABLE_ANSWER_EXPLANATION and !empty($ma['answer_explanation'])) {
                            $answlist .=  '<div class="paddingleft"><br /><span class="explanation">'.$l['w_explanation'].':</span><br />'.F_decode_tcecode($ma['answer_explanation']).'</div>'.K_NEWLINE;
                        }
                        $answlist .= '</li>'.K_NEWLINE;
                    }
                    if (strlen($answlist) > 0) {
                        $questlist .= "<ol class=\"answer\">\n".$answlist."</ol><br /><br />\n";
                    }
                } else {
                    F_display_db_error();
                }
            } // end if hide_answers
            $questlist .= '</li>'.K_NEWLINE;
        }
        if (strlen($questlist) > 0) {
            // display the list
            echo '<ul class="question">'.K_NEWLINE;
            echo $questlist;
            echo '</ul>'.K_NEWLINE;
            echo '<div class="row"><hr /></div>'.K_NEWLINE;
            // check/uncheck all options
            echo '<span dir="'.$l['a_meta_dir'].'">';
            echo '<input type="radio" name="checkall" id="checkall1" value="1" onclick="document.getElementById(\'form_selectquestions\').submit()" />';
            echo '<label for="checkall1">'.$l['w_check_all'].'</label> ';
            echo '<input type="radio" name="checkall" id="checkall0" value="0" onclick="document.getElementById(\'form_selectquestions\').submit()" />';
            echo '<label for="checkall0">'.$l['w_uncheck_all'].'</label>';
            echo '</span>'.K_NEWLINE;
            echo '&nbsp;';
            if ($l['a_meta_dir'] == 'rtl') {
                $arr = '&larr;';
            } else {
                $arr = '&rarr;';
            }
            // action options
            echo '<select name="menu_action" id="menu_action" size="0">'.K_NEWLINE;
            echo '<option value="0" style="color:gray">'.$l['m_with_selected'].'</option>'.K_NEWLINE;
            echo '<option value="enable">'.$l['w_enable'].'</option>'.K_NEWLINE;
            echo '<option value="disable">'.$l['w_disable'].'</option>'.K_NEWLINE;
            echo '<option value="delete">'.$l['w_delete'].'</option>'.K_NEWLINE;
            echo '<option value="copy">'.$l['w_copy'].' '.$arr.'</option>'.K_NEWLINE;
            echo '<option value="move">'.$l['w_move'].' '.$arr.'</option>'.K_NEWLINE;
            echo '</select>'.K_NEWLINE;
            // select new topic (for copy or move action)
            echo '<select name="new_subject_id" id="new_subject_id" size="0" title="'.$l['h_subject'].'">'.K_NEWLINE;
            $sql = F_select_module_subjects_sql('module_enabled=\'1\' AND subject_enabled=\'1\'');
            if ($r = F_db_query($sql, $db)) {
                echo '<option value="0" style="color:gray">'.$l['w_subject'].'</option>'.K_NEWLINE;
                $prev_module_id = 0;
                while ($m = F_db_fetch_array($r)) {
                    if ($m['module_id'] != $prev_module_id) {
                        $prev_module_id = $m['module_id'];
                        echo '<option value="0" style="color:gray;font-weight:bold;" disabled="disabled">* '.htmlspecialchars($m['module_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
                    }
                    echo '<option value="'.$m['subject_id'].'">&nbsp;&nbsp;&nbsp;&nbsp;'.htmlspecialchars($m['subject_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
                }
            } else {
                echo '</select>'.K_NEWLINE;
                F_display_db_error();
            }
            echo '</select>'.K_NEWLINE;
            // submit button
            F_submit_button("update", $l['w_update'], $l['h_update']);
        }

        // ---------------------------------------------------------------
        // -- page jumper (menu for successive pages)
        if ($rowsperpage > 0) {
            $sql = 'SELECT count(*) AS total FROM '.K_TABLE_QUESTIONS.' '.$wherequery.'';
            if (!empty($order_field)) {
                $param_array = '&amp;order_field='.urlencode($order_field).'';
            }
            if (!empty($orderdir)) {
                $param_array .= '&amp;orderdir='.$orderdir.'';
            }
            if (!empty($hide_answers)) {
                $param_array .= '&amp;hide_answers='.intval($hide_answers).'';
            }
            $param_array .= '&amp;subject_module_id='.$subject_module_id.'';
            $param_array .= '&amp;subject_id='.$subject_id.'';
            $param_array .= '&amp;submitted=1';
            F_show_page_navigator($_SERVER['SCRIPT_NAME'], $sql, $firstrow, $rowsperpage, $param_array);
        }
    } else {
        F_display_db_error();
    }
    return true;
}

//============================================================+
// END OF FILE
//============================================================+
