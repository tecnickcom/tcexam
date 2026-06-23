<?php

//============================================================+
// File name   : tce_page_header_popup.php
// Begin       : 2001-11-01
// Last Update : 2023-11-30
//
// Description : Outputs default XHTML popup page header.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

/**
 * @file
 * Outputs default XHTML popup page header.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2001-11-01
 */

require_once 'tce_xhtml_header.php';

echo '<main id="maincontent" class="content">' . K_NEWLINE;
echo '<h1>' . htmlspecialchars($thispage_title, ENT_NOQUOTES, $l['a_meta_charset']) . '</h1>' . K_NEWLINE;
