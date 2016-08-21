<?php
//============================================================+
// File name   : tce_functions_page.php
// Begin       : 2002-03-21
// Last Update : 2011-02-05
//
// Description : Functions for XHTML pages.
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
//    Copyright (C) 2004-2010  Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Functions for XHTML pages.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @since 2002-03-21
 */

/**
 * Display Pages navigation index.
 * @param $script_name (string) url of the calling page
 * @param $sql (string) sql used to select records
 * @param $firstrow (int) first row number
 * @param $rowsperpage (int) number of max rows per page
 * @param $param_array (string) parameters to pass on url via GET
 * @return mixed the number of pages in case of success, FALSE otherwise
 */
function F_show_page_navigator($script_name, $sql, $firstrow, $rowsperpage, $param_array)
{
    global $l, $db;
    require_once('../config/tce_config.php');
    $max_pages = 4; // max pages to display on page selector
    $indexbar = ''; // string for selection page html code
    $firstrow = intval($firstrow);
    $rowsperpage = intval($rowsperpage);
    if (!$sql or ($rowsperpage < 1)) {
        return false;
    }
    if (!$r = F_db_query($sql, $db)) {
            F_display_db_error();
    }
    // build base url for all links
    $baseaddress = $script_name;
    if (empty($param_array)) {
        $baseaddress .= '?';
    } else {
        $param_array = substr($param_array, 5); // remove first "&amp;"
        $baseaddress .= '?'.$param_array.'&amp;';
    }
    $count_rows = preg_match('/GROUP BY/i', $sql); //check if query contain a "GROUP BY"
    $all_updates = F_db_num_rows($r);
    if (($all_updates == 1) and (!$count_rows)) {
        list($all_updates) = F_db_fetch_array($r);
    }
    if (!$all_updates) { //no records
        F_print_error('MESSAGE', $l['m_search_void']);
    } else {
        if ($all_updates > $rowsperpage) {
            $indexbar .= '<div class="pageselector">'.$l['w_page'].': ';
            $page_range = $max_pages * $rowsperpage;
            if ($firstrow <= $page_range) {
                $page_range = (2 * $page_range) - $firstrow + $rowsperpage;
            } elseif ($firstrow >= ($all_updates - $page_range)) {
                $page_range = (2 * $page_range) - ($all_updates - (2 * $rowsperpage) - $firstrow);
            }

            if ($firstrow >= $rowsperpage) {
                $indexbar .= '<a href="'.$baseaddress.'firstrow=0">1</a> | ';
                $indexbar .= '<a href="'.$baseaddress.'firstrow='.($firstrow - $rowsperpage).'" title="'.$l['w_previous'].'">&lt;</a> | ';
            } else {
                $indexbar .= '1 | &lt; | ';
            }
            $count = 2;
            $x = 0;
            for ($x = $rowsperpage; $x < ($all_updates - $rowsperpage); $x += $rowsperpage) {
                if (($x >= ($firstrow - $page_range)) and ($x <= ($firstrow + $page_range))) {
                    if ($x == $firstrow) {
                        $indexbar .= $count.' | ';
                    } else {
                        $indexbar .= '<a href="'.$baseaddress.'firstrow='.$x.'" title="'.$count.'">'.$count.'</a> | ';
                    }
                }
                $count++;
            }
            if (($firstrow + $rowsperpage) < $all_updates) {
                $indexbar .= '<a href="'.$baseaddress.'firstrow='.($firstrow + $rowsperpage).'" title="'.$l['w_next'].'">&gt;</a> | ';
                $indexbar .= '<a href="'.$baseaddress.'firstrow='.$x.'" title="'.$count.'">'.$count.'</a>';
            } else {
                $indexbar .= '&gt; | '.$count;
            }
            $indexbar .= '</div>';
        }
    }
    echo $indexbar; // display the page selector
    return $all_updates; //return number of records found
}

//============================================================+
// END OF FILE
//============================================================+
