<?php
//============================================================+
// File name   : tce_page_userbar.php
// Begin       : 2004-04-24
// Last Update : 2012-12-30
//
// Description : Display user's bar containing copyright
//               information, user status and language
//               selector.
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
//    Copyright (C) 2004-2013 Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Display user's bar containing copyright information, user status and language selector.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @since 2004-04-24
 */

// IMPORTANT: DO NOT REMOVE OR ALTER THIS PAGE!

// skip links
echo '<div class="minibutton" dir="ltr">'.K_NEWLINE;
echo '<a href="#timersection" accesskey="3" title="[3] '.$l['w_jump_timer'].'" class="white">'.$l['w_jump_timer'].'</a> <span style="color:white;">|</span>'.K_NEWLINE;
echo '<a href="#menusection" accesskey="4" title="[4] '.$l['w_jump_menu'].'" class="white">'.$l['w_jump_menu'].'</a>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="userbar">'.K_NEWLINE;
if ($_SESSION['session_user_level'] > 0) {
    // display user information
    echo '<span title="'.$l['h_user_info'].'">'.$l['w_user'].': '.$_SESSION['session_user_name'].'</span>';
    // display logout link
    echo ' <a href="tce_logout.php" class="logoutbutton" title="'.$l['h_logout_link'].'">'.$l['w_logout'].'</a>'.K_NEWLINE;
} else {
    // display login link
    echo ' <a href="tce_login.php" class="loginbutton" title="'.$l['h_login_button'].'">'.$l['w_login'].'</a>'.K_NEWLINE;
}
echo '</div>'.K_NEWLINE;

// language selector
if (K_LANGUAGE_SELECTOR and (stristr($_SERVER['SCRIPT_NAME'], 'tce_test_execute.php') === false)) {
    echo '<div class="minibutton" dir="ltr">'.K_NEWLINE;
    echo '<span class="langselector" title="change language">'.K_NEWLINE;
    $lang_array = unserialize(K_AVAILABLE_LANGUAGES);
    $lngstr = '';
    while (list($lang_code, $lang_name) = each($lang_array)) {
        $lngstr .= ' | ';
        if ($lang_code == K_USER_LANG) {
            $lngstr .= '<span class="selected" title="'.$lang_name.'">'.strtoupper($lang_code).'</span>';
        } else {
            // query string was removed because unnecessary
            //if (isset($_SERVER['QUERY_STRING']) AND (strlen($_SERVER['QUERY_STRING'])>0)) {
            //	$querystr = preg_replace("/([\?|\&]?)lang=([a-z]{2,3})/si", '', $_SERVER['QUERY_STRING']);
            //}
            //if (isset($querystr) AND (strlen($querystr)>0)) {
            //	$langlink = $_SERVER['SCRIPT_NAME'].'?'.str_replace('&', '&amp;', $querystr).'&amp;lang='.$lang_code;
            //} else {
                $langlink = $_SERVER['SCRIPT_NAME'].'?lang='.$lang_code;
            //}
            $lngstr .= '<a href="'.$langlink.'" class="langselector" title="'.$lang_name.'">'.strtoupper($lang_code).'</a>';
        }
    }
    echo substr($lngstr, 3);
    echo '</span>'.K_NEWLINE;
    echo '</div>'.K_NEWLINE;
}

echo '<div class="minibutton" dir="ltr">';
echo '<span class="copyright"><a href="http://www.tcexam.org">TCExam</a> ver. '.K_TCEXAM_VERSION.' - Copyright &copy; 2004-2016 Nicola Asuni - <a href="http://www.tecnick.com">Tecnick.com LTD</a></span>';
echo '</div>'.K_NEWLINE;

// Display W3C logos
echo '<div class="minibutton" dir="ltr">'.K_NEWLINE;
echo '<a href="http://validator.w3.org/check?uri='.K_PATH_HOST.$_SERVER['SCRIPT_NAME'].'" class="minibutton" title="This Page Is Valid XHTML 1.0 Strict!">W3C <span>XHTML 1.0</span></a> <span style="color:white;">|</span>'.K_NEWLINE;
echo '<a href="http://jigsaw.w3.org/css-validator/" class="minibutton" title="This document validates as CSS!">W3C <span>CSS 2.0</span></a> <span style="color:white;">|</span>'.K_NEWLINE;
echo '<a href="http://www.w3.org/WAI/WCAG1AAA-Conformance" class="minibutton" title="Explanation of Level Triple-A Conformance">W3C <span>WAI-AAA</span></a>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

//============================================================+
// END OF FILE
//============================================================+
