<?php
//============================================================+
// File name   : tce_show_all_questions.php
// Begin       : 2005-07-06
// Last Update : 2009-09-30
// 
// Description : Display all questions grouped by topic.
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
//    Copyright (C) 2004-2009  Nicola Asuni - Tecnick.com S.r.l.
//    
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//    
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//    
//    You should have received a copy of the GNU General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
//     
//    Additionally, you can't remove the original TCExam logo, copyrights statements
//    and links to Tecnick.com and TCExam websites.
//    
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * Display all questions grouped by topic.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link www.tecnick.com
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
require_once('../code/tce_functions_auth_sql.php');
require_once('tce_functions_questions.php');

// --- Initialize variables

// set default values
if (!isset($wherequery)) {$wherequery='';}
if (!isset($order_field)) {$order_field='question_enabled DESC, question_position, question_description';}
if (!isset($orderdir)) {$orderdir=0;}
if (!isset($firstrow)) {$firstrow=0;}
if (!isset($rowsperpage)) {$rowsperpage=K_MAX_ROWS_PER_PAGE;}
if (!isset($hide_answers)) {$hide_answers=false;}

if (isset($selectmodule)) {
	$changemodule = 1;
}
if (isset($selectcategory)) {
	$changecategory = 1;
}

if ((isset($changemodule) AND ($changemodule > 0)) OR (isset($changecategory) AND ($changecategory > 0))) {
	$wherequery='';
	$order_field='question_enabled DESC, question_position, question_description';
	$firstrow=0;
	$orderdir=0;
}

// select default module/subject (if not specified)
if(!(isset($subject_module_id) AND ($subject_module_id > 0))) {
	$sql = F_select_subjects_sql().' LIMIT 1';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			$subject_module_id = $m['subject_module_id'];
		} else {
			$subject_module_id = 0;
		}
	} else {
		F_display_db_error();
	}
}

// select subject
if ((isset($changemodule) AND ($changemodule > 0)) 
	OR (!(isset($subject_id) AND ($subject_id > 0)))) {
	$sql = F_select_subjects_sql('subject_module_id='.$subject_module_id.'').' LIMIT 1';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			$subject_id = $m['subject_id'];
		} else {
			$subject_id = 0;
		}
	} else {
		F_display_db_error();
	}
}

// check user's authorization
if (!F_isAuthorizedUser(K_TABLE_SUBJECTS, 'subject_id', $subject_id, 'subject_user_id')) {
	F_print_error('ERROR', $l['m_authorization_denied']);
	exit;
}

if (isset($menu_mode) AND ($menu_mode == 'update') AND isset($menu_action) AND !empty($menu_action)) {
	$istart = 1 + $firstrow;
	$iend = $rowsperpage + $firstrow;
	for ($i = $istart; $i <= $iend; $i++) {
		// for each selected question
		$keyname = 'questionid'.$i;
		if (isset($$keyname)) {
			$question_id = $$keyname;
			switch($menu_action) {
				case 'move': {
					if (isset($new_subject_id) AND ($new_subject_id > 0)) {
						F_question_copy($question_id, $new_subject_id);
						F_question_delete($question_id, $subject_id);
					}
					break;
				}
				case 'copy': {
					if (isset($new_subject_id) AND ($new_subject_id > 0)) {
						F_question_copy($question_id, $new_subject_id);
					}
					break;
				}
				case 'delete': {
					F_question_delete($question_id, $subject_id);
					break;
				}
				case 'disable': {
					F_question_set_enabled($question_id, $subject_id, false);
					break;
				}
				case 'enable': {
					F_question_set_enabled($question_id, $subject_id, true);
					break;
				}	
			} // end of switch
		}
	}
	F_print_error('MESSAGE', $l['m_updated']);
}
?>

<div class="container">

<div class="tceformbox">
<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data" id="form_selectquestions">

<div class="row">
<span class="label">
<label for="subject_module_id"><?php echo $l['w_module']; ?></label>
</span>
<span class="formw">
<input type="hidden" name="changemodule" id="changemodule" value="" />
<select name="subject_module_id" id="subject_module_id" size="0" onchange="document.getElementById('form_selectquestions').changemodule.value=1;document.getElementById('form_selectquestions').changecategory.value=1; document.getElementById('form_selectquestions').submit();" title="<?php echo $l['w_module']; ?>">
<?php
$sql = 'SELECT * FROM '.K_TABLE_MODULES.' ORDER BY module_name';
if($r = F_db_query($sql, $db)) {
	$countitem = 1;
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['module_id'].'"';
		if($m['module_id'] == $subject_module_id) {
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
?>
</select>
</span>
</div>

<noscript>
<div class="row">
<span class="label">&nbsp;</span>
<span class="formw">
<input type="submit" name="selectmodule" id="selectmodule" value="<?php echo $l['w_select']; ?>" />
</span>
</div>
</noscript>

<div class="row">
<span class="label">
<label for="subject_id"><?php echo $l['w_subject']; ?></label>
</span>
<span class="formw">
<input type="hidden" name="changecategory" id="changecategory" value="" />
<select name="subject_id" id="subject_id" size="0" onchange="document.getElementById('form_selectquestions').changecategory.value=1;document.getElementById('form_selectquestions').submit()" title="<?php echo $l['h_subject']; ?>">
<?php
$sql = F_select_subjects_sql('subject_module_id='.$subject_module_id);
if($r = F_db_query($sql, $db)) {
	$countitem = 1;
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['subject_id'].'"';
		if($m['subject_id'] == $subject_id) {
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
}
else {
	echo '</select></span></div>'.K_NEWLINE;
	F_display_db_error();
}
?>
</select>
</span>
</div>

<noscript>
<div class="row">
<span class="label">&nbsp;</span>
<span class="formw">
<input type="submit" name="selectcategory" id="selectcategory" value="<?php echo $l['w_select']; ?>" />
</span>
</div>
</noscript>

<div class="row">
<span class="label">
&nbsp;
</span>
<span class="formw">
<?php
echo '<input type="checkbox" name="hide_answers" id="hide_answers" value="1"';
if($hide_answers) {echo ' checked="checked"';}
echo ' title="'.$l['w_hide_answers'].'" onclick="document.getElementById(\'form_selectquestions\').submit()" />';
?>
<label for="hide_answers"><?php echo $l['w_hide_answers']; ?></label>
</span>
</div>

<noscript>
<div class="row">
<span class="label">&nbsp;</span>
<span class="formw">
<input type="submit" name="selectrecord" id="selectrecord" value="<?php echo $l['w_select']; ?>" />
</span>
</div>
</noscript>

<div class="row"><hr /></div>

<?php 
// display questions statistics
$qtype = array('<acronym class="offbox" title="'.$l['w_single_answer'].'">S</acronym>', '<acronym class="offbox" title="'.$l['w_multiple_answers'].'">M</acronym>', '<acronym class="offbox" title="'.$l['w_free_answer'].'">T</acronym>', '<acronym class="offbox" title="'.$l['w_ordering_answer'].'">O</acronym>'); // question types
$qstat = '';
$nqsum = 0;
$sql = 'SELECT question_type, COUNT(*) as numquestions 
	FROM '.K_TABLE_QUESTIONS.' 
	WHERE question_subject_id='.$subject_id.' 
	GROUP BY question_type';
if($r = F_db_query($sql, $db)) {
	$countitem = 1;
	while($m = F_db_fetch_array($r)) {
		$nqsum += $m['numquestions'];
		$qstat .= ' + '.$m['numquestions'].' '.$qtype[($m['question_type']-1)].'';
	}
}
else {
	F_display_db_error();
}

echo '<div class="rowl">';
echo ''.$l['w_questions'].': '.$nqsum.' = '.$qstat.'';
echo '</div>'.K_NEWLINE;
?>

<div class="row"><hr /></div>

<div class="rowl">
<?php 
if (isset($subject_id) AND ($subject_id > 0)) {
	F_show_select_questions($wherequery, $subject_module_id, $subject_id, $order_field, $orderdir, $firstrow, $rowsperpage, $hide_answers); 
}
?>
&nbsp;
</div>
<div class="row"><hr /></div>

<div class="row">
<?php
// show buttons by case
if (isset($subject_id) AND ($subject_id > 0)) {
	$pdflink = 'tce_pdf_all_questions.php';
	$pdflink .= '?module_id='.$subject_module_id;
	$pdflink .= '&amp;subject_id='.$subject_id;
	echo '<a href="'.$pdflink.'&amp;expmode=1" class="xmlbutton" title="'.$l['h_pdf'].'">PDF</a>';
	echo '<a href="'.$pdflink.'&amp;expmode=2" class="xmlbutton" title="'.$l['h_pdf'].'">PDF '.$l['w_module'].'</a>';
	echo '<a href="'.$pdflink.'&amp;expmode=3" class="xmlbutton" title="'.$l['h_pdf'].'">PDF '.$l['w_all'].'</a> ';
	$xmllink = 'tce_xml_questions.php';
	$xmllink .= '?module_id='.$subject_module_id;
	$xmllink .= '&amp;subject_id='.$subject_id;
	echo '<a href="'.$xmllink.'&amp;expmode=1" class="xmlbutton" title="'.$l['h_xml_export'].'">XML</a>';
	echo '<a href="'.$xmllink.'&amp;expmode=2" class="xmlbutton" title="'.$l['h_xml_export'].'">XML '.$l['w_module'].'</a>';
	echo '<a href="'.$xmllink.'&amp;expmode=3" class="xmlbutton" title="'.$l['h_xml_export'].'">XML '.$l['w_all'].'</a>';
}
?>
&nbsp;

<input type="hidden" name="firstrow" id="firstrow" value="<?php echo $firstrow; ?>" />
<input type="hidden" name="order_field" id="order_field" value="<?php echo $order_field; ?>" />
<input type="hidden" name="orderdir" id="orderdir" value="<?php echo $orderdir; ?>" />
<input type="hidden" name="submitted" id="submitted" value="0" />
<input type="hidden" name="usersearch" id="usersearch" value="" />
</div>

</form>

</div>
<?php

echo '<div class="pagehelp">'.$l['hp_select_all_questions'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

// ------------------------------

/**
 * Display a list of selected questions.
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link www.tecnick.com
 * @since 2005-07-06
 * @param string $wherequery question selection query
 * @param string $subject_module_id module ID
 * @param string $subject_id topic ID
 * @param string $order_field order by column name
 * @param int $orderdir oreder direction
 * @param int $firstrow number of first row to display
 * @param int $rowsperpage number of rows per page
 * @param boolean $hide_answers if true hide answers
 * @return false in case of empty database, true otherwise
 */
function F_show_select_questions($wherequery, $subject_module_id, $subject_id, $order_field, $orderdir, $firstrow, $rowsperpage, $hide_answers=false) {
	global $l, $db;
	require_once('../config/tce_config.php');
	require_once('../../shared/code/tce_functions_page.php');
	
	$order_field = F_escape_sql($order_field);
	$orderdir = intval($orderdir);
	if($orderdir == 0) {
		$nextorderdir = 1;
		$full_order_field = $order_field;
	} else {
		$nextorderdir = 0;
		$full_order_field = $order_field.' DESC';
	}
	
	if(!F_count_rows(K_TABLE_QUESTIONS)) { //if the table is void (no items) display message
		F_print_error('MESSAGE', $l['m_databasempty']);
		return FALSE;
	}
	
	if(empty($wherequery)) {
		$wherequery = 'WHERE question_subject_id='.$subject_id.'';
	} else {
		$wherequery .= ' AND question_subject_id='.$subject_id.'';
	}
	$sql = 'SELECT * 
		FROM '.K_TABLE_QUESTIONS.'
		'.$wherequery.'
		ORDER BY '.$full_order_field.' 
		LIMIT '.$rowsperpage.' OFFSET '.$firstrow.'';
	if($r = F_db_query($sql, $db)) {
		$questlist = '';
		$itemcount = $firstrow;
		while($m = F_db_fetch_array($r)) {
			$itemcount++;
			$questlist .= '<li>'.K_NEWLINE;
			$questlist .= '<strong>'.$itemcount.'.</strong> ';
			$questlist .= '<input type="checkbox" name="questionid'.$itemcount.'" id="questionid'.$itemcount.'" value="'.$m['question_id'].'" title="'.$l['w_select'].'"';
			if (isset($_REQUEST['checkall']) AND ($_REQUEST['checkall'] == 1)) {
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
			if (K_ENABLE_QUESTION_EXPLANATION AND !empty($m['question_explanation'])) {
				$questlist .=  '<div class="paddingleft"><br /><span class="explanation">'.$l['w_explanation'].':</span><br />'.F_decode_tcecode($m['question_explanation']).'</div>'.K_NEWLINE;
			}
			if (!$hide_answers) {
				// display alternative answers
				$sqla = 'SELECT *
					FROM '.K_TABLE_ANSWERS.'
					WHERE answer_question_id=\''.$m['question_id'].'\'
					ORDER BY answer_enabled DESC,answer_position,answer_isright DESC';
				if($ra = F_db_query($sqla, $db)) {
					$answlist = '';
					while($ma = F_db_fetch_array($ra)) {
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
						if (K_ENABLE_ANSWER_EXPLANATION AND !empty($ma['answer_explanation'])) {
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
			echo '<span dir="ltr">';
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
			if($r = F_db_query($sql, $db)) {
				echo '<option value="0" style="color:gray">'.$l['w_subject'].'</option>'.K_NEWLINE;
				$prev_module_id = 0;
				while($m = F_db_fetch_array($r)) {
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
		$sql = 'SELECT count(*) AS total FROM '.K_TABLE_QUESTIONS.' '.$wherequery.'';
		if (!empty($order_field)) {$param_array = '&amp;order_field='.urlencode($order_field).'';}
		if (!empty($orderdir)) {$param_array .= '&amp;orderdir='.$orderdir.'';}
		if (!empty($hide_answers)) {$param_array .= '&amp;hide_answers='.intval($hide_answers).'';}
		$param_array .= '&amp;subject_module_id='.$subject_module_id.'';
		$param_array .= '&amp;subject_id='.$subject_id.'';
		$param_array .= '&amp;submitted=1';
		F_show_page_navigator($_SERVER['SCRIPT_NAME'], $sql, $firstrow, $rowsperpage, $param_array);
	} else {
		F_display_db_error();
	}
	return TRUE;
}

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
