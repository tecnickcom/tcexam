<?php
//============================================================+
// File name   : tce_functions_menu.php
// Begin       : 2001-09-08
// Last Update : 2010-09-16
//
// Description : Functions for Web menu.
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
//    Copyright (C) 2004-2010 Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Functions for Web menu.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @since 2010-09-16
 */

/**
 * Returns a menu element link wit subitems.
 * If the link refers to the current page, only the name will be returned.
 * @param $link (string) URL
 * @param $data (array) link data
 * @param $level (int) item level
 */
function F_menu_link($link, $data, $level = 0)
{
    global $l, $db;
    require_once('../config/tce_config.php');
    if (!$data['enabled'] or ($_SESSION['session_user_level'] < $data['level'])) {
        // this item is disabled
        return;
    }
    $str = '<li>';
    if ($link != basename($_SERVER['SCRIPT_NAME'])) {
        $str .= '<a href="'.$data['link'].'" title="'.$data['title'].'"';
        if (!empty($data['key'])) {
            $str .= ' accesskey="'.$data['key'].'"';
        }
        if (F_menu_isChildActive($data)) {
            $str .= ' class="active"';
        }
        $str .= '>'.$data['name'].'</a>';
    } else {
        // active link
        $str .= '<span class="active">'.$data['name'].'</span>';
    }
    if (isset($data['sub']) and !empty($data['sub'])) {
        // print sub-items
        $sublevel = ($level + 1);
        $str .= K_NEWLINE.'<!--[if lte IE 6]><iframe class="menu"></iframe><![endif]-->'.K_NEWLINE;
        $str .= '<ul>'.K_NEWLINE;
        foreach ($data['sub'] as $sublink => $subdata) {
            $str .= F_menu_link($sublink, $subdata, $sublevel);
        }
        $str .= '</ul>'.K_NEWLINE;
    }
    $str .= '</li>'.K_NEWLINE;
    return $str;
}

/**
 * Returns true if the menu item has an active child, false otherwise.
 * @param $data (array) link data
 */
function F_menu_isChildActive($data)
{
    if (isset($data['sub']) and !empty($data['sub'])) {
        if (array_key_exists(basename($_SERVER['SCRIPT_NAME']), $data['sub'])) {
            // key found
            return true;
        } else {
            // try sub-trees
            foreach ($data['sub'] as $submenu) {
                if (F_menu_isChildActive($submenu)) {
                    return true;
                }
            }
        }
    }
    return false;
}

//============================================================+
// END OF FILE
//============================================================+
