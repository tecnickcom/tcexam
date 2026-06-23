<?php

//============================================================+
// File name   : tce_xhtml_header.php
// Begin       : 2004-04-24
// Last Update : 2023-11-30
//
// Description : print default XHTML page header
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

/**
 * @file
 * output default XHTML header (doctype + head)
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2004-04-24
 * int $pagelevel page access level (0-10), default 0
 * string $thispage_title page title, default K_TCEXAM_TITLE
 * string $thispage_description page description, default K_TCEXAM_DESCRIPTION
 * string $thispage_author page author, default K_TCEXAM_AUTHOR
 * string $thispage_reply page reply to, default K_TCEXAM_REPLY_TO
 * string $thispage_keywords page keywords, default K_TCEXAM_KEYWORDS
 * string $thispage_icon page icon, default K_TCEXAM_ICON
 * string $thispage_style page CSS file name, default K_TCEXAM_STYLE
 */

//if necessary load default values
if (!isset($pagelevel) || empty($pagelevel)) {
    $pagelevel = 0;
}

if (!isset($thispage_title) || empty($thispage_title)) {
    $thispage_title = K_TCEXAM_TITLE;
}

if (!isset($thispage_description) || empty($thispage_description)) {
    $thispage_description = K_TCEXAM_DESCRIPTION;
}

if (!isset($thispage_author) || empty($thispage_author)) {
    $thispage_author = K_TCEXAM_AUTHOR;
}

if (!isset($thispage_reply) || empty($thispage_reply)) {
    $thispage_reply = K_TCEXAM_REPLY_TO;
}

if (!isset($thispage_keywords) || empty($thispage_keywords)) {
    $thispage_keywords = K_TCEXAM_KEYWORDS;
}

if (!isset($thispage_icon) || empty($thispage_icon)) {
    $thispage_icon = K_TCEXAM_ICON;
}

if (!isset($thispage_style) || empty($thispage_style)) {
    $thispage_style = strcasecmp($l['a_meta_dir'], 'rtl') == 0 ? K_TCEXAM_STYLE_RTL : K_TCEXAM_STYLE;
}

echo '<!DOCTYPE html>' . K_NEWLINE;
echo '<html lang="' . $l['a_meta_language'] . '" dir="' . $l['a_meta_dir'] . '">' . K_NEWLINE;

echo '<head>' . K_NEWLINE;
echo '<meta charset="' . $l['a_meta_charset'] . '" />' . K_NEWLINE;
echo '<title>' . htmlspecialchars($thispage_title, ENT_NOQUOTES, $l['a_meta_charset']) . '</title>' . K_NEWLINE;
echo '<meta name="viewport" content="width=device-width, initial-scale=1" />' . K_NEWLINE;
echo '<meta name="language" content="' . $l['a_meta_language'] . '" />' . K_NEWLINE;
echo '<meta name="tcexam_level" content="' . $pagelevel . '" />' . K_NEWLINE;
echo
    '<meta name="description" content="'
        . htmlspecialchars($thispage_description, ENT_COMPAT, $l['a_meta_charset'])
        . ' ['
        . base64_decode(K_KEY_SECURITY)
        . ']" />'
        . K_NEWLINE
;
echo
    '<meta name="author" content="'
        . htmlspecialchars($thispage_author, ENT_COMPAT, $l['a_meta_charset'])
        . '" />'
        . K_NEWLINE
;
echo
    '<meta name="reply-to" content="'
        . htmlspecialchars($thispage_reply, ENT_COMPAT, $l['a_meta_charset'])
        . '" />'
        . K_NEWLINE
;
echo
    '<meta name="keywords" content="'
        . htmlspecialchars($thispage_keywords, ENT_COMPAT, $l['a_meta_charset'])
        . '" />'
        . K_NEWLINE
;
echo '<link rel="stylesheet" href="' . $thispage_style . '" />' . K_NEWLINE;
echo '<link rel="icon" href="' . $thispage_icon . '" />' . K_NEWLINE;
echo '<!-- TCExam19730104 -->' . K_NEWLINE;
echo '</head>' . K_NEWLINE;

echo '<body>' . K_NEWLINE;
// accessibility: skip link to the main content (must be the first focusable element)
echo
    '<a href="#maincontent" class="skiplink" accesskey="2" title="[2] '
        . htmlspecialchars($l['w_skip_navigation'], ENT_QUOTES, $l['a_meta_charset'])
        . '">'
        . $l['w_skip_navigation']
        . '</a>'
        . K_NEWLINE
;

global $login_error;
if (isset($login_error) && $login_error) {
    F_print_error('WARNING', $l['m_login_wrong']);
}
