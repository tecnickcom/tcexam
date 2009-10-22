<?php
//============================================================+
// File name   : tce_test_results.php
// Begin       : 2004-06-10
// Last Update : 2009-09-30
// 
// Description : Display test results for current user.
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
 * Display test results for current user.
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2004-06-10
 * @uses F_lockUserTest
 * @uses F_getUserTestStat
 * @uses F_testInfoLink
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = 1;
$thispage_title = $l['t_test_results'];
$thispage_description = $l['hp_test_execute'];
require_once('../../shared/code/tce_authorization.php');

$formname = 'testform';
$test_id = 0;

require_once('../code/tce_page_header.php');

echo '<div class="container">'.K_NEWLINE;

if (isset($_REQUEST['testid']) AND ($_REQUEST['testid'] > 0)) {
	$test_id = intval($_REQUEST['testid']);
	require_once('../../shared/code/tce_functions_test.php');
	require_once('../../shared/code/tce_functions_test_stats.php');
	
	$testdata = F_getTestData($test_id);
	
	if (F_getBoolean($testdata['test_results_to_users'])) {
		//lock user's test
		F_lockUserTest($test_id, $_SESSION['session_user_id']);
		
		// get user's test stats
		$usrtestdata = F_getUserTestStat($test_id, $_SESSION['session_user_id']);
		
		// display results
		
		echo '<div class="container">'.K_NEWLINE;
		echo '<div class="tceformbox">'.K_NEWLINE;
		
		echo '<div class="row"><span class="label">';
		echo '<span title="'.$l['h_test_name'].'">'.$l['w_test'].': </span>'.K_NEWLINE;
		echo '</span><span class="formw">';
		echo ''.$testdata['test_name'].' '.F_testInfoLink($test_id, $l['w_info']).'';
		echo '</span></div>'.K_NEWLINE;
		
		$passmsg = '';
		if ($testdata['test_score_threshold'] > 0) {
			echo '<div class="row"><span class="label">';
			echo '<span title="'.$l['h_score'].'">'.$l['w_test_score_threshold'].': </span>'.K_NEWLINE;
			echo '</span><span class="formw">';
			echo ''.$testdata['test_score_threshold'].'';
			echo '</span></div>'.K_NEWLINE;
			if ($usrtestdata['score'] >= $testdata['test_score_threshold']) {
				$passmsg = ' - <strong>'.$l['w_passed'].'</strong>';
			} else {
				$passmsg = ' - <strong>'.$l['w_not_passed'].'</strong>';
			}
		}
		
		echo '<div class="row"><span class="label">';
		echo '<span title="'.$l['h_score'].'">'.$l['w_score'].': </span>'.K_NEWLINE;
		echo '</span><span class="formw">';
		echo ''.$usrtestdata['score'].'';
		echo ' ('.round(100 * $usrtestdata['score'] / $usrtestdata['max_score']).'%)'.$passmsg.'';
		echo '</span></div>'.K_NEWLINE;
		
		echo '<div class="row"><span class="label">';
		echo '<span title="'.$l['h_answers_right'].'">'.$l['w_answers_right'].': </span>'.K_NEWLINE;
		echo '</span><span class="formw">';
		echo ''.$usrtestdata['right'].' ('.round(100 * $usrtestdata['right'] / $usrtestdata['all']).'%)';
		echo '</span></div>'.K_NEWLINE;
		
		echo '<div class="row"><span class="label">';
		echo '<span title="'.$l['h_answers_wrong'].'">'.$l['w_answers_wrong'].': </span>'.K_NEWLINE;
		echo '</span><span class="formw">';
		echo ''.$usrtestdata['wrong'].' ('.round(100 * $usrtestdata['wrong'] / $usrtestdata['all']).'%)';
		echo '</span></div>'.K_NEWLINE;
		
		echo '<div class="row"><span class="label">';
		echo '<span title="'.$l['h_questions_unanswered'].'">'.$l['w_questions_unanswered'].': </span>'.K_NEWLINE;
		echo '</span><span class="formw">';
		echo ''.$usrtestdata['unanswered'].' ('.round(100 * $usrtestdata['unanswered'] / $usrtestdata['all']).'%)';
		echo '</span></div>'.K_NEWLINE;
		
		echo '<div class="row"><span class="label">';
		echo '<span title="'.$l['h_questions_undisplayed'].'">'.$l['w_questions_undisplayed'].': </span>'.K_NEWLINE;
		echo '</span><span class="formw">';
		echo ''.$usrtestdata['undisplayed'].' ('.round(100 * $usrtestdata['undisplayed'] / $usrtestdata['all']).'%)';
		echo '</span></div>'.K_NEWLINE;
		
		if (F_getBoolean($testdata['test_report_to_users'])) {
			echo '<div class="row"><span class="label">';
			echo '<span title="'.$l['h_view_details'].'">'.$l['w_details'].': </span>'.K_NEWLINE;
			echo '</span><span class="formw">';
			echo '<a href="'.pdfLink(3, $test_id).'" class="xmlbutton" title="'.$l['h_pdf'].'">'.$l['w_pdf'].'</a>'.K_NEWLINE;
			echo '</span></div>'.K_NEWLINE;
		}
		echo '<div class="spacer"></div>'.K_NEWLINE;
		
		echo '</div>'.K_NEWLINE;
		echo '</div>'.K_NEWLINE;
	}	
}

echo '<a href="index.php" title="'.$l['h_index'].'">&lt; '.$l['w_index'].'</a>'.K_NEWLINE;
echo '<div class="pagehelp">'.$l['hp_test_results'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE; // container

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
