<?php
//============================================================+
// File name   : tce_xhtml_header.php
// Begin       : 2004-04-24
// Last Update : 2011-03-15
//
// Description : Output defaults XHTML header (doctype + head).
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
 * @file
 * Outputs default XHTML header (doctype + head).
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
 * @since 2004-04-24
 * int $pagelevel page access level (0-10), default 0
 * string $thispage_title page title, default K_SITE_TITLE
 * string $thispage_description page description, default K_SITE_DESCRIPTION
 * string $thispage_author page author, default K_SITE_AUTHOR
 * string $thispage_reply page reply to, default K_SITE_REPLY_TO
 * string $thispage_keywords page keywords, default K_SITE_KEYWORDS
 * string $thispage_icon page icon, default K_SITE_ICON
 * string $thispage_style page CSS file name, default K_SITE_STYLE
 */

/**
 */

// if necessary load default values
if(!isset($pagelevel) OR empty($pagelevel)) {$pagelevel = 0;}
if(!isset($thispage_title) OR empty($thispage_title)) {$thispage_title = K_SITE_TITLE;}
if(!isset($thispage_description) OR empty($thispage_description)) {$thispage_description = K_SITE_DESCRIPTION;}
if(!isset($thispage_author) OR empty($thispage_author)) {$thispage_author = K_SITE_AUTHOR;}
if(!isset($thispage_reply) OR empty($thispage_reply)) {$thispage_reply = K_SITE_REPLY;}
if(!isset($thispage_keywords) OR empty($thispage_keywords)) {$thispage_keywords = K_SITE_KEYWORDS;}
if(!isset($thispage_icon) OR empty($thispage_icon)) {$thispage_icon = K_SITE_ICON;}
if(!isset($thispage_style) OR empty($thispage_style)) {
	if(strcasecmp($l['a_meta_dir'], 'rtl') == 0) {
		$thispage_style = K_SITE_STYLE_RTL;
	} else {
		$thispage_style = K_SITE_STYLE;
	}
}

echo '<'.'?'.'xml version="1.0" encoding="'.$l['a_meta_charset'].'" '.'?'.'>'.K_NEWLINE;
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'.K_NEWLINE;
echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$l['a_meta_language'].'" lang="'.$l['a_meta_language'].'" dir="'.$l['a_meta_dir'].'">'.K_NEWLINE;

echo '<head>'.K_NEWLINE;
echo '<title>'.htmlspecialchars($thispage_title, ENT_NOQUOTES, $l['a_meta_charset']).'</title>'.K_NEWLINE;
echo '<meta http-equiv="Content-Type" content="text/html; charset='.$l['a_meta_charset'].'" />'.K_NEWLINE;
echo '<meta name="language" content="'.$l['a_meta_language'].'" />'.K_NEWLINE;
echo '<meta name="tcexam_level" content="'.$pagelevel.'" />'.K_NEWLINE;
echo '<meta name="description" content="'."\x5b\x54\x43\x45\x78\x61\x6d\x5d".' '.htmlspecialchars($thispage_description, ENT_COMPAT, $l['a_meta_charset']).' ['.base64_decode(K_KEY_SECURITY).']" />'.K_NEWLINE;
echo '<meta name="author" content="nick"/>'.K_NEWLINE;
echo '<meta name="reply-to" content="'.htmlspecialchars($thispage_reply, ENT_COMPAT, $l['a_meta_charset']).'" />'.K_NEWLINE;
echo '<meta name="keywords" content="'.htmlspecialchars($thispage_keywords, ENT_COMPAT, $l['a_meta_charset']).'" />'.K_NEWLINE;
echo '<link rel="stylesheet" href="'.$thispage_style.'" type="text/css" />'.K_NEWLINE;
echo '<link rel="shortcut icon" href="'.$thispage_icon.'" />'.K_NEWLINE;
echo '<!-- '.'T'.'C'.'E'.'x'.'a'.'m'.'19'.'73'.'01'.'04'.' -->'.K_NEWLINE;
echo '</head>'.K_NEWLINE;

echo '<body>'.K_NEWLINE;

global $login_error;
if (isset($login_error) AND $login_error) {
	F_print_error('WARNING', $l['m_login_wrong']);
}

//============================================================+
// END OF FILE
//============================================================+
