<?php
//============================================================+
// File name   : tce_functions_page.php
// Begin       : 2002-03-21
// Last Update : 2009-02-12
// 
// Description : Functions for XHTML pages.
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
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//    
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//    
//    You should have received a copy of the GNU General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
//     
//    Additionally, you can't remove the original TCExam logo, copyrights statements
//    and links to Tecnick.com and TCExam websites.
//    
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * Functions for XHTML pages.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link www.tecnick.com
 * @since 2002-03-21
 */

/**
 * Display Pages navigation index.
 * @param string $script_name url of the calling page
 * @param string $sql sql used to select records
 * @param int $firstrow first row number
 * @param int $rowsperpage number of max rows per page
 * @param string $param_array parameters to pass on url via GET
 * @return mixed the number of pages in case of success, FALSE otherwise
 */
function F_show_page_navigator($script_name, $sql, $firstrow, $rowsperpage, $param_array) {
	global $l, $db;
	require_once('../config/tce_config.php');
	
	$max_pages = 4; // max pages to display on page selector
	$indexbar = ''; // string for selection page html code
	$firstrow = intval($firstrow);
	$rowsperpage = intval($rowsperpage);
	
	if(!$sql) {return FALSE;}
	
	if(!$r = F_db_query($sql, $db)) {
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
	if ( ($all_updates == 1) AND (!$count_rows) ) {
		list($all_updates) = F_db_fetch_array($r);
	}
	
	if(!$all_updates) { //no records
		F_print_error('MESSAGE', $l['m_search_void']);
	} else {
		if($all_updates > $rowsperpage) {
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
			for($x = $rowsperpage; $x < ($all_updates - $rowsperpage); $x += $rowsperpage) {
				if(($x >= ($firstrow - $page_range)) AND ($x <= ($firstrow + $page_range))) {
					if($x == $firstrow) {
						$indexbar .= ''.$count.' | ';
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
				$indexbar .= '&gt; | '.$count.'';
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
?>
