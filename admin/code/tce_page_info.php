<?php

//============================================================+
// File name   : tce_page_info.php
// Begin       : 2004-05-21
// Last Update : 2024-03-22
//
// Description : Outputs TCExam information page.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

/**
 * @file
 * Outputs TCExam information page.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2004-05-21
 */

require_once '../config/tce_config.php';

$pagelevel = K_AUTH_ADMIN_INFO;
require_once '../../shared/code/tce_authorization.php';

$thispage_title = $l['t_page_info'];
require_once '../code/tce_page_header.php';

echo '<div class="container">' . K_NEWLINE;

echo '' . $l['d_tcexam_desc'] . '<br />' . K_NEWLINE;

echo '<ul class="credits">' . K_NEWLINE;
echo '<li><strong>' . $l['w_author'] . ':</strong> Nicola Asuni</li>' . K_NEWLINE;
echo '<li><strong>Copyright:</strong><br /> (c) 2004-2026 Nicola Asuni - Tecnick.com LTD<br />' . K_NEWLINE;
echo '<a href="mailto:info@tecnick.com">info@tecnick.com</a> - ' . K_NEWLINE;
echo
    '<a href="https://tecnick.com" target="_blank" rel="noopener noreferrer" title="'
        . $l['m_new_window_link']
        . '">tecnick.com</a></li>'
        . K_NEWLINE
;
echo
    '<li><strong>'
        . $l['w_license']
        . ':</strong> <a href="../../LICENSE" target="_blank" rel="noopener noreferrer" title="'
        . $l['m_new_window_link']
        . '">LICENSE</a></li>'
        . K_NEWLINE
;
echo '</ul>' . K_NEWLINE;

echo '<h2>' . $l['t_third_parties'] . '</h2>' . K_NEWLINE;

echo
    '<p>TCExam relies on some third-party software components that are not bundled with this codebase but are installed via Composer. When used, they are subject to their respective licenses. The complete list of components, with their exact versions and license terms, is declared in the <code>composer.json</code> file and can be inspected with the <code>composer licenses</code> command.</p>'
        . K_NEWLINE
;

echo '<h2>' . $l['t_translations'] . '</h2>' . K_NEWLINE;

echo '<ul class="credits">' . K_NEWLINE;
echo '<li>[AR] Arabic : Red Sea</li>' . K_NEWLINE;
echo '<li>[AZ] Azerbaijani : Jamil Farzana</li>' . K_NEWLINE;
echo '<li>[BG] Bulgarian : Georgi Kostadinov</li>' . K_NEWLINE;
echo '<li>[BR] Brazilian Portuguese : Carlos Eduardo Vianna, Flávio Veras</li>' . K_NEWLINE;
echo '<li>[CN] Chinese : Liu Yongxin, Zheng Xiaojing</li>' . K_NEWLINE;
echo '<li>[DE] German : Oliver Kasch, André Scherrer, Wolfgang Stöggl</li>' . K_NEWLINE;
echo '<li>[EL] Greek : Kottas Alexandros</li>' . K_NEWLINE;
echo '<li>[EN] English : Nicola Asuni</li>' . K_NEWLINE;
echo '<li>[ES] Spanish : Carlos Alarcon, Maria del Rocio Peñas Serrano, Alejandra Ruiz</li>' . K_NEWLINE;
echo '<li>[FA] Farsi (Persian): Mahmoud Saghaei</li>' . K_NEWLINE;
echo '<li>[FR] French : Roger Koukerjinian, André Scherrer</li>' . K_NEWLINE;
echo '<li>[HI] Hindi : Mahesh K Bhandari, Shekhar K Maravi, Pradeep K Nayak</li>' . K_NEWLINE;
echo '<li>[HE] Hebrew : Oron Peled</li>' . K_NEWLINE;
echo '<li>[HU] Hungarian : Peter Ivanyi, Tibor Balázs</li>' . K_NEWLINE;
echo '<li>[ID] Indonesian : Ahmad Bardosono, Maman Sulaeman</li>' . K_NEWLINE;
echo '<li>[IT] Italian : Nicola Asuni</li>' . K_NEWLINE;
echo '<li>[JP] Japanese : Koji Nakajima</li>' . K_NEWLINE;
echo '<li>[MR] Marathi : Tushar Sayankar</li>' . K_NEWLINE;
echo '<li>[MS] Malay (Bahasa Melayu) : Arvind Prakash Jha</li>' . K_NEWLINE;
echo '<li>[NL] Dutch : Chris de Boer</li>' . K_NEWLINE;
echo '<li>[PL] Polish : Tomasz Parol</li>' . K_NEWLINE;
echo '<li>[RO] Romanian : Ovidiu Dragomir</li>' . K_NEWLINE;
echo '<li>[RU] Russian : Andrey, Sergey C.</li>' . K_NEWLINE;
echo '<li>[TR] Turkish : Mehmet Arif Icir</li>' . K_NEWLINE;
echo '<li>[UR] Urdu : Ghulam Abbas</li>' . K_NEWLINE;
echo '<li>[VN] Vietnamese : Nguyen Quynh Nga</li>' . K_NEWLINE;
echo '</ul>' . K_NEWLINE;

echo '</div>' . K_NEWLINE;

echo '<br />' . K_NEWLINE;

// display credit logos
echo '<div class="creditslogos">' . K_NEWLINE;
echo
    '<a href="https://www.gnu.org/licenses/agpl-3.0.en.html"><img src="../../images/credits/agplv3-88x31.png" alt="GNU AFFERO GENERAL PUBLIC LICENSE" width="88" height="31" style="border:none;" /></a>'
        . K_NEWLINE
;
echo '</div>' . K_NEWLINE;

require_once 'tce_page_footer.php';
