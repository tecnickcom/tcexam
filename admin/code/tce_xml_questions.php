<?php
//============================================================+
// File name   : tce_xml_questions.php
// Begin       : 2006-03-06
// Last Update : 2009-09-30
// 
// Description : Functions to export questions using XML
//               format.
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
//    Additionally, you can't remove the original TCExam logo, copyrights statements
//    and links to Tecnick.com and TCExam websites.
//    
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * Display all questions grouped by topic in XML format.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2006-03-11
 */

/**
 */

// send XML headers
header('Content-Description: XML File Transfer');
header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
header('Pragma: public');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
// force download dialog
header('Content-Type: application/force-download');
header('Content-Type: application/octet-stream', false);
header('Content-Type: application/download', false);
header('Content-Type: application/xml', false);
// use the Content-Disposition header to supply a recommended filename
header('Content-Disposition: attachment; filename=tcexam_questions_'.$_REQUEST['subject_id'].'_'.date('YmdHis').'.xml;');
header('Content-Transfer-Encoding: binary');

if ((isset($_REQUEST['expmode']) AND ($_REQUEST['expmode'] > 0))
	AND (isset($_REQUEST['module_id']) AND ($_REQUEST['module_id'] > 0))
	AND (isset($_REQUEST['subject_id']) AND ($_REQUEST['subject_id'] > 0))) {
	$expmode = intval($_REQUEST['expmode']);
	$module_id = intval($_REQUEST['module_id']);
	$subject_id = intval($_REQUEST['subject_id']);
	echo F_xml_export_questions($module_id, $subject_id, $expmode);
} else {
	exit;
}

/**
 * Export all questions of the selected subject to XML.
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2006-03-06
 * @param int $module_id  module ID
 * @param int $subject_id topic ID
 * @param int $expmode export mode: 1 = selected topic; 2 = selected module; 3 = all modules.
 * @return XML data
 */
function F_xml_export_questions($module_id, $subject_id, $expmode) {
	global $l, $db;
	require_once('../config/tce_config.php');
	require_once('../../shared/code/tce_authorization.php');
	require_once('../code/tce_functions_auth_sql.php');
	$module_id = intval($module_id);
	$subject_id = intval($subject_id);
	$expmode = intval($expmode);
	
	$boolean = array('false', 'true');
	$type = array('single', 'multiple', 'text', 'ordering');

	$xml = ''; // XML data to be returned
	
	$xml .= '<'.'?xml version="1.0" encoding="UTF-8" ?'.'>'.K_NEWLINE;
	$xml .= '<tcexamquestions version="'.K_TCEXAM_VERSION.'">'.K_NEWLINE;
	$xml .=  K_TAB.'<header';
	$xml .= ' lang="'.K_USER_LANG.'"';
	$xml .= ' date="'.date(K_TIMESTAMP_FORMAT).'">'.K_NEWLINE;
	$xml .= K_TAB.'</header>'.K_NEWLINE;
	$xml .=  K_TAB.'<body>'.K_NEWLINE;
	
	// ---- module
	$sqlm = 'SELECT * FROM '.K_TABLE_MODULES.'';
	if ($expmode < 3) {
		$sqlm .= ' WHERE module_id='.$module_id.'';
	}
	$sqlm .= ' ORDER BY module_name';
	if($rm = F_db_query($sqlm, $db)) {
		while($mm = F_db_fetch_array($rm)) {
			$xml .= K_TAB.K_TAB.'<module>'.K_NEWLINE;
			
			$xml .= K_TAB.K_TAB.K_TAB.'<name>';
			$xml .=  F_text_to_xml($mm['module_name']);
			$xml .= '</name>'.K_NEWLINE;
			
			$xml .= K_TAB.K_TAB.K_TAB.'<enabled>';
			$xml .= $boolean[intval(F_getBoolean($mm['module_enabled']))];
			$xml .= '</enabled>'.K_NEWLINE;
			
			// ---- topic
			$where_sqls = 'subject_module_id='.$mm['module_id'].'';
			if ($expmode < 2) {
				$where_sqls .= ' AND subject_id='.$subject_id.'';
			}
			$sqls = F_select_subjects_sql($where_sqls);
			if($rs = F_db_query($sqls, $db)) {
				while($ms = F_db_fetch_array($rs)) {
					$xml .= K_TAB.K_TAB.K_TAB.'<subject>'.K_NEWLINE;
					
					$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<name>';
					$xml .= F_text_to_xml($ms['subject_name']);
					$xml .= '</name>'.K_NEWLINE;
					
					$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<description>';
					$xml .= F_text_to_xml($ms['subject_description']);
					$xml .= '</description>'.K_NEWLINE;
								
					$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<enabled>';
					$xml .= $boolean[intval(F_getBoolean($ms['subject_enabled']))];
					$xml .= '</enabled>'.K_NEWLINE;
					
					// ---- questions
					$sql = "SELECT * 
						FROM ".K_TABLE_QUESTIONS."
						WHERE question_subject_id=".$ms['subject_id']."
						ORDER BY question_enabled DESC, question_position, question_description";
					if($r = F_db_query($sql, $db)) {
						while($m = F_db_fetch_array($r)) {
			
							$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<question>'.K_NEWLINE;
			
							$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<enabled>';
							$xml .= $boolean[intval(F_getBoolean($m['question_enabled']))];
							$xml .= '</enabled>'.K_NEWLINE;
			
							$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<type>';
							$xml .= $type[$m['question_type']-1];
							$xml .= '</type>'.K_NEWLINE;
			
							$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<difficulty>';
							$xml .= $m['question_difficulty'];
							$xml .= '</difficulty>'.K_NEWLINE;
			
							$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<position>';
							$xml .= $m['question_position'];
							$xml .= '</position>'.K_NEWLINE;
			
							$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<timer>';
							$xml .= $m['question_timer'];
							$xml .= '</timer>'.K_NEWLINE;
			
							$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<fullscreen>';
							$xml .= $boolean[intval(F_getBoolean($m['question_fullscreen']))];
							$xml .= '</fullscreen>'.K_NEWLINE;
			
							$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<inline_answers>';
							$xml .= $boolean[intval(F_getBoolean($m['question_inline_answers']))];
							$xml .= '</inline_answers>'.K_NEWLINE;
			
							$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<auto_next>';
							$xml .= $boolean[intval(F_getBoolean($m['question_auto_next']))];
							$xml .= '</auto_next>'.K_NEWLINE;
			
							$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<description>';
							$xml .=  F_text_to_xml($m['question_description']);
							$xml .= '</description>'.K_NEWLINE;
							
							$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<explanation>';
							$xml .=  F_text_to_xml($m['question_explanation']);
							$xml .= '</explanation>'.K_NEWLINE;
			
							// display alternative answers
							$sqla = 'SELECT *
								FROM '.K_TABLE_ANSWERS.'
								WHERE answer_question_id=\''.$m['question_id'].'\'
								ORDER BY answer_position,answer_isright DESC';
							if($ra = F_db_query($sqla, $db)) {
								while($ma = F_db_fetch_array($ra)) {
					
									$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<answer>'.K_NEWLINE;
					
									$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<enabled>';
									$xml .= $boolean[intval(F_getBoolean($ma['answer_enabled']))];
									$xml .= '</enabled>'.K_NEWLINE;
					
									$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<isright>';
									$xml .= $boolean[intval(F_getBoolean($ma['answer_isright']))];
									$xml .= '</isright>'.K_NEWLINE;
									
									$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<position>';
									$xml .= $ma['answer_position'];
									$xml .= '</position>'.K_NEWLINE;
					
									$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<key>';
									$xml .= F_text_to_xml(chr($ma['answer_keyboard_key']));
									$xml .= '</key>'.K_NEWLINE;
					
									$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<description>';
									$xml .=  F_text_to_xml($ma['answer_description']);
									$xml .= '</description>'.K_NEWLINE;
									
									$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<explanation>';
									$xml .=  F_text_to_xml($ma['answer_explanation']);
									$xml .= '</explanation>'.K_NEWLINE;
					
									$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'</answer>'.K_NEWLINE;
								}
							} else {
								F_display_db_error();
							}
							
							$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'</question>'.K_NEWLINE;
							
						} // end while for questions
					} else {
						F_display_db_error();
					}
					
					$xml .= K_TAB.K_TAB.K_TAB.'</subject>'.K_NEWLINE;
					
				} // end while for topics
			} else {
				F_display_db_error();
			}
			
			$xml .= K_TAB.K_TAB.'</module>'.K_NEWLINE;
			
		} // end while for module
	} else {
		F_display_db_error();
	}
	
	$xml .= K_TAB.'</body>'.K_NEWLINE;
	$xml .= '</tcexamquestions>'.K_NEWLINE;
	
	return $xml;
}

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
