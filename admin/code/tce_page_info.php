<?php
//============================================================+
// File name   : tce_page_info.php
// Begin       : 2004-05-21
// Last Update : 2017-04-22
//
// Description : Outputs TCExam information page.
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
//    Copyright (C) 2004-2018 Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Outputs TCExam information page.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2004-05-21
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_INFO;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_page_info'];
require_once('../code/tce_page_header.php');

require_once('tce_page_header.php');

echo '<div class="container">'.K_NEWLINE;

echo ''.$l['d_tcexam_desc'].'<br />'.K_NEWLINE;

echo '<ul class="credits">'.K_NEWLINE;
echo '<li><strong>'.$l['w_author'].':</strong> Nicola Asuni</li>'.K_NEWLINE;
echo '<li><strong>Copyright:</strong><br /> (c) 2004-2018 Nicola Asuni - Tecnick.com LTD<br />'.K_NEWLINE;
echo '<a href="mailto:info@tecnick.com">info@tecnick.com</a> - '.K_NEWLINE;
echo '<a href="http://www.tecnick.com" title="'.$l['m_new_window_link'].'">www.tecnick.com</a></li>'.K_NEWLINE;
echo '<li><strong>'.$l['w_license'].':</strong> <a href="../../LICENSE.TXT" title="'.$l['m_new_window_link'].'">LICENSE.TXT</a></li>'.K_NEWLINE;
echo '</ul>'.K_NEWLINE;

echo '<h2>'.$l['t_third_parties'].'</h2>'.K_NEWLINE;

echo '<p>TCExam includes some third-party software components that are not strictly required but have been included as you convenience, and if used are subject to their respective licenses.</p>'.K_NEWLINE;

echo '<ul class="credits">'.K_NEWLINE;

echo '<li><strong>PHPMailer</strong><br />
Full Featured Email Transfer Class for PHP.<br />
Authors:<ul>
<li>Brent R. Matzelle (original founder)</li>
<li>2014 Marcus Bointon
<li>2010 - 2012 Jim Jagielski
<li>2004 - 2009 Andy Prevost
</ul>
Homepage: <a href="https://github.com/PHPMailer/PHPMailer/">https://github.com/PHPMailer/PHPMailer</a><br />
License: <a href="http://www.gnu.org/copyleft/lesser.html" title="GNU Lesser General Public License">LGPL (GNU LESSER GENERAL PUBLIC LICENSE)</a><br />
Location: /shared/phpmailer/<br /><br />
</li>'.K_NEWLINE;

echo '<li><strong>The DHTML Calendar</strong><br />
Calendar widget written in Javascript.<br />
Author: Mihai Bazon (<a href="mailto:mihai_bazon@yahoo.com">mihai_bazon@yahoo.com</a>)<br />
Homepage: <a href="http://dynarch.com/mishoo/" title="mishoo">http://dynarch.com/mishoo/</a><br />
License: <a href="http://www.gnu.org/copyleft/lesser.html" title="GNU Lesser General Public License">LGPL (GNU LESSER GENERAL PUBLIC LICENSE)</a><br />
Location: /shared/jscripts/jscalendar/<br /><br />
</li>'.K_NEWLINE;

echo '<li><strong>Javascript VirtualKeyboard</strong><br />
Author: Ilya Lebedev (<a href="mailto:ilya@lebedev.net">ilya@lebedev.net</a>)<br />
Homepage: <a href="http://debugger.ru/projects/virtualkeyboard" title="VirtualKeyboard">http://debugger.ru/projects/virtualkeyboard</a><br />
License: <a href="http://www.gnu.org/copyleft/lesser.html" title="GNU Lesser General Public License">LGPL (GNU LESSER GENERAL PUBLIC LICENSE)</a><br />
Location: /shared/jscripts/vk/<br /><br />
</li>'.K_NEWLINE;

echo '<li><strong>Radius Class</strong><br />
Radius client implementation in pure PHP.<br />
Author: SysCo/al (<a href="mailto:developer@sysco.ch">developer@sysco.ch</a>)<br />
Homepage: <a href="http://developer.sysco.ch/php/" title="sysco">http://developer.sysco.ch/php/</a><br />
License: <a href="http://www.gnu.org/copyleft/lesser.html" title="GNU Lesser General Public License">LGPL (GNU LESSER GENERAL PUBLIC LICENSE)</a><br />
Location: /shared/radius/<br /><br />
</li>'.K_NEWLINE;

echo '<li><strong>phpCAS</strong><br />
CAS client in PHP.<br />
Copyright: 2007-2015, JA-SIG, Inc. <a href="http://www.jasig.org" title="Jasig Website">http://www.jasig.org</a><br />
Homepage: <a href="https://wiki.jasig.org/display/CASC/phpCAS" title="phpCAS">https://wiki.jasig.org/display/CASC/phpCAS</a><br />
License: <a href="http://www.apache.org/licenses/LICENSE-2.0" title="Apache License, Version 2.0">Apache License, Version 2.0</a><br />
Location: /shared/cas/<br /><br />
</li>'.K_NEWLINE;

echo '<li><strong>Fonts</strong><br />

TCExam includes fonts for the <a href="http://www.tcpdf.org" title="TCPDF - PHP class for PDF">TCPDF</a> library, they are not strictly required but have been included as you convenience.<br />
<br />
All the PHP files on the fonts directory are subject to the general TCPDF license (GNU-LGPLv3), they do not contain any binary data but just a description of the general properties of a particular font. These files can be also generated on the fly using the font utilities and TCPDF methods.
<br />
All the original binary TTF font files have been renamed for compatibility with TCPDF and compressed using the gzcompress PHP function that uses the ZLIB data format (.z files).<br />
<br />
The binary files (.z) that begins with the prefix "free" have been extracted from the GNU FreeFont collection (GNU-GPLv3).<br />
The binary files (.z) that begins with the prefix "pdfa" have been derived from the GNU FreeFont, so they are subject to the same license.<br />
For the details of Copyright, License and other information, please check the files inside the directory fonts/freefont-20100919<br />
Homepage: <a href="http://www.gnu.org/software/freefont/" title="GNU FreeFont">http://www.gnu.org/software/freefont/</a><br />
License: <a href="http://www.gnu.org/licenses/gpl.html" title="GNU General Public License, version 3">GNU-GPLv3</a><br />
<br />
The binary files (.z) that begins with the prefix "dejavu" have been extracted from the DejaVu fonts 2.33 (Bitstream) collection.<br />
For the details of Copyright, License and other information, please check the files inside the directory fonts/dejavu-fonts-ttf-2.33<br />
Homepage: <a href="http://dejavu-fonts.org" title="DejaVu fonts">http://dejavu-fonts.org</a><br />
License: <a href="http://dejavu-fonts.org/wiki/License" title="Bitstream License">DejaVu changes are in public domain</a><br />
<br />
The binary files (.z) that begins with the prefix "ae" have been extracted from the Arabeyes.org collection (GNU-GPLv2).<br />
Homepage: <a href="http://projects.arabeyes.org" title="Arabeyes.org">http://projects.arabeyes.org</a><br />
License: <a href="http://www.gnu.org/licenses/old-licenses/gpl-2.0.html" title="GNU General Public License, version 2">GNU-GPLv2</a><br />


</li>'.K_NEWLINE;

echo '</ul>'.K_NEWLINE;

echo '<h2>'.$l['t_translations'].'</h2>'.K_NEWLINE;

echo '<ul class="credits">'.K_NEWLINE;
echo '<li>[AR] Arabic : Red Sea</li>'.K_NEWLINE;
echo '<li>[AZ] Azerbaijani : Jamil Farzana</li>'.K_NEWLINE;
echo '<li>[BG] Bulgarian : Georgi Kostadinov</li>'.K_NEWLINE;
echo '<li>[BR] Brazilian Portuguese : Carlos Eduardo Vianna, Flávio Veras</li>'.K_NEWLINE;
echo '<li>[CN] Chinese : Liu Yongxin, Zheng Xiaojing</li>'.K_NEWLINE;
echo '<li>[DE] German : Oliver Kasch, André Scherrer, Wolfgang Stöggl</li>'.K_NEWLINE;
echo '<li>[EL] Greek : Kottas Alexandros</li>'.K_NEWLINE;
echo '<li>[EN] English : Nicola Asuni</li>'.K_NEWLINE;
echo '<li>[ES] Spanish : Carlos Alarcon, Maria del Rocio Peñas Serrano, Alejandra Ruiz</li>'.K_NEWLINE;
echo '<li>[FA] Farsi (Persian): Mahmoud Saghaei</li>'.K_NEWLINE;
echo '<li>[FR] French : Roger Koukerjinian, André Scherrer</li>'.K_NEWLINE;
echo '<li>[HI] Hindi : Mahesh K Bhandari, Shekhar K Maravi, Pradeep K Nayak</li>'.K_NEWLINE;
echo '<li>[HE] Hebrew : Oron Peled</li>'.K_NEWLINE;
echo '<li>[HU] Hungarian : Peter Ivanyi, Tibor Balázs</li>'.K_NEWLINE;
echo '<li>[ID] Indonesian : Ahmad Bardosono</li>'.K_NEWLINE;
echo '<li>[IT] Italian : Nicola Asuni</li>'.K_NEWLINE;
echo '<li>[JP] Japanese : Koji Nakajima</li>'.K_NEWLINE;
echo '<li>[MR] Marathi : Tushar Sayankar</li>'.K_NEWLINE;
echo '<li>[MS] Malay (Bahasa Melayu) : Arvind Prakash Jha</li>'.K_NEWLINE;
echo '<li>[NL] Dutch : Chris de Boer</li>'.K_NEWLINE;
echo '<li>[PL] Polish : Tomasz Parol</li>'.K_NEWLINE;
echo '<li>[RO] Romanian : Ovidiu Dragomir</li>'.K_NEWLINE;
echo '<li>[RU] Russian : Andrey, Sergey C.</li>'.K_NEWLINE;
echo '<li>[TR] Turkish : Mehmet Arif Icir</li>'.K_NEWLINE;
echo '<li>[VN] Vietnamese : Nguyen Quynh Nga</li>'.K_NEWLINE;
echo '</ul>'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

echo '<br />'.K_NEWLINE;

// display credit logos
echo '<div class="creditslogos">'.K_NEWLINE;
echo '<a href="http://www.tcexam.org/license.php"><img src="../../images/credits/agplv3-88x31.png" alt="TCExam License" width="88" height="31" style="border:none;" /></a>'.K_NEWLINE;
//echo '<a href="http://www.php.net"><img src="../../images/credits/poweredby_php_88x31.png" alt="Powered by PHP" width="88" height="31" /></a>'.K_NEWLINE;
//echo '<a href="http://www.mysql.com"><img src="../../images/credits/poweredbymysql_88x31.png" alt="Powered by MySQL" width="88" height="31" /></a>'.K_NEWLINE;
//echo '<a href="http://www.postgresql.org"><img src="../../images/credits/poweredbypgsql_88x31.png" alt="Powered by PostgreSQL" width="88" height="31" /></a>'.K_NEWLINE;
echo '<a href="http://validator.w3.org/check?uri='.K_PATH_HOST.$_SERVER['SCRIPT_NAME'].'" title="This Page Is Valid XHTML 1.0 Strict!"><img src="../../images/credits/w3c_xhtml10_88x31.png" alt="Valid XHTML 1.0!" height="31" width="88" style="border:none;" /></a>'.K_NEWLINE;
echo '<a href="http://jigsaw.w3.org/css-validator/" title="This document validates as CSS!"><img src="../../images/credits/w3c_css_88x31.png" alt="Valid CSS1!" height="31" width="88" style="border:none;" /></a>'.K_NEWLINE;
echo '<a href="http://www.w3.org/WAI/WCAG1AAA-Conformance" title="Explanation of Level Triple-A Conformance"><img src="../../images/credits/w3c_wai_aaa_88x31.png" alt="Level Triple-A conformance icon, W3C-WAI Web Content Accessibility Guidelines 1.0" width="88" height="31" style="border:none;" /></a>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
