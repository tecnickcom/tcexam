<?php
//============================================================+
// File name   : tce_functions_general.php
// Begin       : 2001-09-08
// Last Update : 2010-05-10
//
// Description : General functions.
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
//    Copyright (C) 2004-2010 Nicola Asuni - Tecnick.com S.r.l.
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
//    Additionally, you can't remove the original TCExam logo, copyrights statements
//    and links to Tecnick.com and TCExam websites.
//
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * General functions.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2001-09-08
 */

/**
 * Count rows of the given table.
 * @param string $dbtable database table name
 * @param string $where optional where SQL clause (including the WHERE keyword).
 * @return number of rows
 */
function F_count_rows($dbtable, $where='') {
	global $db;
	require_once('../config/tce_config.php');
	$numofrows = 0;
	$sql = 'SELECT COUNT(*) AS numrows FROM '.$dbtable.' '.$where.'';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			$numofrows = $m['numrows'];
		}
	} else {
		F_display_db_error();
	}
	return($numofrows);
}

/**
 * Prepare field value for SQL query.<br>
 * Returns the quoted string if not empty, NULL otherwise.
 * @param string $str string to check.
 * @return string $str quoted if not empty, NULL otherwise
 */
function F_empty_to_null($str) {
	if (strlen($str) > 0) {
		return '\''.$str.'\'';
	}
	return 'NULL';
}

/**
 * Prepare field value for SQL query.<br>
 * Returns the num if different from zero, NULL otherwise.
 * @param string $num string to check.
 * @return string $num if != 0, NULL otherwise
 */
function F_zero_to_null($num) {
	if ($num == 0) {
		return 'NULL';
	}
	return $num;
}

/**
 * Returns boolean value from string.<br>
 * This function is needed to get the right boolean value from boolean field returned by PostgreSQL query.
 * @param string $str string to check.
 * @return boolean value.
 */
function F_getBoolean($str) {
	if (is_string($str) AND ((strncasecmp($str, 't', 1) == 0) OR (strncasecmp($str, '1', 1) == 0))) {
		return true;
	}
	return false;
}

/**
 * Check if specified fields are unique on table.
 * @param string $table table name
 * @param string $where SQL where clause
 * @param mixed $fieldname name of table column to check
 * @param mixed $fieldid ID of table row to check
 * @return bool true if unique, false otherwise
 */
function F_check_unique($table, $where, $fieldname=FALSE, $fieldid=FALSE) {
	require_once('../config/tce_config.php');
	global $l, $db;
	$sqlc = 'SELECT * FROM '.$table.' WHERE '.$where.' LIMIT 1';
	if($rc = F_db_query($sqlc, $db)) {
		if (($fieldname === FALSE) AND ($fieldid === FALSE) AND (F_count_rows($table, 'WHERE '.$where) > 0)) {
			return FALSE;
		}
		if($mc = F_db_fetch_array($rc)) {
			if($mc[$fieldname] == $fieldid) {
				return TRUE; // the values are unchanged
			}
		} else {
			// the new values are not yet present on table
			return TRUE;
		}
	} else {
		F_display_db_error();
	}
	// another table row contains the same values
	return FALSE;
}

/**
 * Reverse function for htmlentities.
 * @param string $text_to_convert input string to convert
 * @param boolean $preserve_tagsign if true preserve <> symbols, default=FALSE
 * @return converted string
 */
function unhtmlentities($text_to_convert, $preserve_tagsign=FALSE) {
	$trans_tbl = get_html_translation_table(HTML_ENTITIES);
	$trans_tbl = array_flip($trans_tbl);
	if ($preserve_tagsign) {
		$trans_tbl['&lt;'] = '&lt;'; //do not convert '<' equivalent
		$trans_tbl['&gt;'] = '&gt;'; //do not convert '>' equivalent
	}
	$return_text = strtr($text_to_convert, $trans_tbl);
	$return_text = preg_replace('/\&\#([0-9]+)\;/me', "chr('\\1')", $return_text);
	return $return_text;
}

/**
 * Remove the following characters:
 * <ul>
 * <li>"\t" (ASCII 9 (0x09)), a tab.</li>
 * <li>"\n" (ASCII 10 (0x0A)), a new line (line feed)</li>
 * <li>"\r" (ASCII 13 (0x0D)), a carriage return</li>
 * <li>"\0" (ASCII 0 (0x00)), the NUL-byte</li>
 * <li>"\x0B" (ASCII 11 (0x0B)), a vertical tab</li>
 * </ul>
 * @param string $string input string to convert
 * @return converted string
 */
function F_compact_string($string) {
	$repTable = array("\t" => ' ', "\n" => ' ', "\r" => ' ', "\0" => ' ', "\x0B" => ' '); //to escape quotes
	return strtr($string, $repTable);
}

/**
 * Replace angular parenthesis with html equivalents (html entities).
 * @param string $str input string to convert
 * @return converted string
 */
function F_replace_angulars($str) {
	$replaceTable = array('<' => '&lt;', '>' => '&gt;');
	return strtr($str, $replaceTable);
}

/**
 * Return part of a string removing remaining non-ASCII characters.
 * @param string $str input string
 * @param int $start substring start index
 * @param int $length substring max lenght
 * @return substring
 */
function F_substr_utf8($str, $start=0, $length) {
	if (strlen($str) > $length) {
		$i = $length - 1;
		// remove non-ASCII characters from the string end
		while (($i >= 0) AND (ord($str{$i}) > 0x7F)) {
			$i--;
		}
		$str = substr($str, 0, $i+1);
	}
	return $str;
}

/**
 * Escape some special characters (&lt; &gt; &amp;).
 * @param string $str input string to convert
 * @return converted string
 */
function F_text_to_xml($str) {
	$replaceTable = array("\0" => '', '&' => '&amp;', '<' => '&lt;', '>' => '&gt;');
	return strtr($str, $replaceTable);
}

/**
 * Unescape some special characters (&lt; &gt; &amp;).
 * @param string $str input string to convert
 * @return converted string
 */
function F_xml_to_text($str) {
	$replaceTable = array('&amp;' => '&', '&lt;' => '<', '&gt;' => '>');
	return strtr($str, $replaceTable);
}

/**
 * Return a string containing an HTML acronym for required/not required fields.
 * @param int $mode field mode: 1=not required; 2=required.
 * @return html string
 */
function showRequiredField($mode=1) {
	global $l;
	$str = '';
	if ($mode == 2) {
		$str = ' <acronym class="requiredonbox" title="'.$l['w_required'].'">+</acronym>';
	} else {
		$str = ' <acronym class="requiredoffbox" title="'.$l['w_not_required'].'">-</acronym>';
	}
	return $str;
}

/**
 * Strip whitespace (or other characters) from the beginning and end of an UTF-8 string
 * and replace the \xA0 with normal space.
 * @param string $txt The string that will be trimmed.
 * @return string The trimmed string.
 */
function utrim($txt) {
	$txt = preg_replace('/\xA0/u', ' ', $txt);
	$txt = preg_replace('/^([\s]+)/u', '', $txt);
	$txt = preg_replace('/([\s]+)$/u', '', $txt);
	return $txt;
}

/**
 * Convert all IP addresses to IPv6 expanded notation.
 * @param string IP address to normalize.
 * @return string IPv6 address in expanded notation or false in case of invalid input.
 * @since 7.1.000 (2009-02-13)
 */
function getNormalizedIP($ip) {
	if (($ip == '0000:0000:0000:0000:0000:0000:0000:0001') OR ($ip == '::1')) {
		// fix localhost problem
		$ip = '127.0.0.1';
	}
	$ip = strtolower($ip);
	// remove unsupported parts
	if (($pos = strrpos($ip, '%')) !== false) {
		$ip = substr($ip, 0, $pos);
	}
	if (($pos = strrpos($ip, '/')) !== false) {
		$ip = substr($ip, 0, $pos);
	}
	$ip = preg_replace("/[^0-9a-f:\.]+/si", '', $ip);
	// check address type
	$is_ipv6 = (strpos($ip, ':') !== false);
	$is_ipv4 = (strpos($ip, '.') !== false);
	if ((!$is_ipv4) AND (!$is_ipv6)) {
		return false;
	}
	if ($is_ipv6 AND $is_ipv4) {
		// strip IPv4 compatibility notation from IPv6 address
		$ip = substr($ip, strrpos($ip, ':') + 1);
		$is_ipv6 = false;
	}
	if ($is_ipv4) {
		// convert IPv4 to IPv6
		$ip_parts = array_pad(explode('.', $ip), 4, 0);
		if (count($ip_parts) > 4) {
			return false;
		}
		for ($i = 0; $i < 4; ++$i) {
			if ($ip_parts[$i] > 255) {
				return false;
			}
		}
		$part7 = base_convert(($ip_parts[0] * 256) + $ip_parts[1], 10, 16);
		$part8 = base_convert(($ip_parts[2] * 256) + $ip_parts[3], 10, 16);
		$ip = '::ffff:'.$part7.':'.$part8;
	}
	// expand IPv6 notation
	if (strpos($ip, '::') !== false) {
		$ip = str_replace('::', str_repeat(':0000', (8 - substr_count($ip, ':'))).':', $ip);
	}
	if (strpos($ip, ':') === 0) {
		$ip = '0000'.$ip;
	}
	// normalize parts to 4 bytes
	$ip_parts = explode(':', $ip);
	foreach ($ip_parts as $key => $num) {
		$ip_parts[$key] = sprintf('%04s', $num);
	}
	$ip = implode(':', $ip_parts);
	return $ip;
}

/**
 * Converts a string containing an IP address into its integer value.
 * @param string IP address to convert.
 * @return int IP address as integer number.
 * @since 7.1.000 (2009-02-13)
 */
function getIpAsInt($ip) {
	$ip = getNormalizedIP($ip);
	$ip = str_replace(':', '', $ip);
	return hexdec($ip);
}

/**
 * Converts a string containing an IP address into its integer value and return string representation.
 * @param string IP address to convert.
 * @return int IP address as string.
 * @since 9.0.033 (2009-11-03)
 */
function getIpAsString($ip) {
	$ip = getIpAsInt($ip);
	return sprintf('%.0f', $ip);
}

/**
 * Format a percentage number.
 * @param float number to be formatted
 * @return formatted string
 */
function F_formatPercentage($num) {
	return '('.str_replace(' ', '&nbsp;', sprintf('% 3d', round(100 * $num))).'%)';
}

/**
 * format a percentage number
 * @param float number to be formatted
 * @return string
 */
function F_formatPdfPercentage($num) {
	return '('.sprintf('% 3d', round(100 * $num)).'%)';
}


/**
 * format a percentage number for XML
 * @param float number to be formatted
 * @return string
 */
function F_formatXMLPercentage($num) {
	return sprintf('%3d', round(100 * $num));
}

//============================================================+
// END OF FILE
//============================================================+
?>
