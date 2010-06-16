<?php
//============================================================+
// File name   : tce_page_userbar.php
// Begin       : 2004-04-24
// Last Update : 2009-09-30
//
// Description : Display user's bar containing copyright
//               information, user status and language
//               selector.
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
//    Copyright (C) 2004-2010  Nicola Asuni - Tecnick.com S.r.l.
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
 * Display user's bar containing copyright information, user status and language selector.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010 - Nicola Asuni - Tecnick.com S.r.l (www.tecnick.com) - Via della Pace n.11 - 09044 Quartucciu (CA) - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2004-04-24
 */

// IMPORTANT: DO NOT REMOVE OR ALTER THIS PAGE!

// skip links
echo '<div class="minibutton" dir="ltr">'.K_NEWLINE;
echo '<a href="#timersection" accesskey="3" title="[3] '.$l['w_jump_timer'].'" class="white">'.$l['w_jump_timer'].'</a> '.K_NEWLINE;
echo '<a href="#menusection" accesskey="4" title="[4] '.$l['w_jump_menu'].'" class="white">'.$l['w_jump_menu'].'</a> '.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="userbar">'.K_NEWLINE;
// display user information
echo '<span title="'.$l['h_user_info'].'">'.$l['w_user'].': '.$_SESSION['session_user_name'].'</span>'.K_NEWLINE;
if ($_SESSION['session_user_level'] > 0) {
	// display logout link
	echo ' <a href="tce_logout.php" class="logoutbutton" title="'.$l['h_logout_link'].'">'.$l['w_logout'].'</a>'.K_NEWLINE;
}
echo '&nbsp;'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

// language selector
if (K_LANGUAGE_SELECTOR AND (stristr($_SERVER['SCRIPT_NAME'], 'tce_test_execute.php') === FALSE)) {
	echo '<div class="minibutton" dir="ltr">'.K_NEWLINE;
	echo '<span class="langselector" title="change language">'.K_NEWLINE;
	$lang_array = unserialize(K_AVAILABLE_LANGUAGES);
	while (list($lang_code, $lang_name) = each($lang_array)) {
		if ($lang_code == K_USER_LANG) {
			echo '<span class="selected" title="'.$lang_name.'">'.strtoupper($lang_code).'</span>'.K_NEWLINE;
		} else {
			if (isset($_SERVER['QUERY_STRING']) AND (strlen($_SERVER['QUERY_STRING'])>0)) {
				$querystr = preg_replace("/([\?|\&]?)lang=([a-z]{2,3})/si", '', $_SERVER['QUERY_STRING']);
			}
			if (isset($querystr) AND (strlen($querystr)>0)) {
				$langlink = $_SERVER['SCRIPT_NAME'].'?'.$querystr.'&amp;lang='.$lang_code;
			} else {
				$langlink = $_SERVER['SCRIPT_NAME'].'?lang='.$lang_code;
			}
			echo '<a href="'.$langlink.'" class="langselector" title="'.$lang_name.'">'.strtoupper($lang_code).'</a>'.K_NEWLINE;
		}
	}
	echo '</span>'.K_NEWLINE;
	echo '</div>'.K_NEWLINE;
}

// Display W3C logos
echo '<div class="minibutton" dir="ltr">'.K_NEWLINE;
echo '<a href="http://validator.w3.org/check/referer" class="minibutton" title="This Page Is Valid XHTML 1.0 Strict!">W3C <span>XHTML 1.0</span></a>'.K_NEWLINE;
echo '<a href="http://jigsaw.w3.org/css-validator/" class="minibutton" title="This document validates as CSS!">W3C <span>CSS 2.0</span></a>'.K_NEWLINE;
echo '<a href="http://www.w3.org/WAI/WCAG1AAA-Conformance" class="minibutton" title="Explanation of Level Triple-A Conformance">W3C <span>WAI-AAA</span></a>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="minibutton" dir="ltr">'.K_NEWLINE;
echo '<span class="copyright"><a href="http://www.tcexam.org">TCExam</a> ver. '.K_TCEXAM_VERSION.' - Copyright &copy; 2004-2010 Nicola Asuni - <a href="http://www.tecnick.com">Tecnick.com S.r.l.</a></span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

//============================================================+
// END OF FILE
//============================================================+
?>
