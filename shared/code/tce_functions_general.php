<?php
//============================================================+
// File name   : tce_functions_general.php
// Begin       : 2001-09-08
// Last Update : 2014-02-03
//
// Description : General functions.
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
 * General functions.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @since 2001-09-08
 */

/**
 * Count rows of the given table.
 * @param $dbtable (string) database table name
 * @param $where (string) optional where SQL clause (including the WHERE keyword).
 * @return number of rows
 */
function F_count_rows($dbtable, $where = '')
{
    global $db;
    require_once('../config/tce_config.php');
    $numofrows = 0;
    $sql = 'SELECT COUNT(*) AS numrows FROM '.$dbtable.' '.$where.'';
    if ($r = F_db_query($sql, $db)) {
        if ($m = F_db_fetch_array($r)) {
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
 * @param $str (string) string to check.
 * @return string $str quoted if not empty, NULL otherwise
 */
function F_empty_to_null($str)
{
    global $db;
    require_once('../../shared/code/tce_db_dal.php');
    if (strlen($str) > 0) {
        return '\''.F_escape_sql($db, $str).'\'';
    }
    return 'NULL';
}

/**
 * Prepare field value for SQL query.<br>
 * Returns the num if different from zero, NULL otherwise.
 * @param $num (string) string to check.
 * @return string $num if != 0, NULL otherwise
 */
function F_zero_to_null($num)
{
    global $db;
    require_once('../../shared/code/tce_db_dal.php');
    if ($num == 0) {
        return 'NULL';
    }
    return F_escape_sql($db, $num);
}

/**
 * Returns boolean value from string or integer.<br>
 * This function is needed to get the right boolean value from boolean field returned by PostgreSQL query.
 * @param $str (string) string to check.
 * @return boolean value.
 */
function F_getBoolean($str)
{
    if (is_bool($str)) {
        return $str;
    }
    if (is_string($str) and ((strncasecmp($str, 't', 1) == 0) or (strncasecmp($str, '1', 1) == 0))) {
        return true;
    }
    if (is_int($str) and ($str == 1)) {
        return true;
    }
    return false;
}

/**
 * Check if specified fields are unique on table.
 * @param $table (string) table name
 * @param $where (string) SQL where clause
 * @param $fieldname (mixed) name of table column to check
 * @param $fieldid (mixed) ID of table row to check
 * @return bool true if unique, false otherwise
 */
function F_check_unique($table, $where, $fieldname = false, $fieldid = false)
{
    require_once('../config/tce_config.php');
    global $l, $db;
    $sqlc = 'SELECT * FROM '.$table.' WHERE '.$where.' LIMIT 1';
    if ($rc = F_db_query($sqlc, $db)) {
        if (($fieldname === false) and ($fieldid === false) and (F_count_rows($table, 'WHERE '.$where) > 0)) {
            return false;
        }
        if ($mc = F_db_fetch_array($rc)) {
            if ($mc[$fieldname] == $fieldid) {
                return true; // the values are unchanged
            }
        } else {
            // the new values are not yet present on table
            return true;
        }
    } else {
        F_display_db_error();
    }
    // another table row contains the same values
    return false;
}

/**
 * Reverse function for htmlentities.
 * @param $text_to_convert (string) input string to convert
 * @param $preserve_tagsign (boolean) if true preserve <> symbols, default=FALSE
 * @return converted string
 */
function unhtmlentities($text_to_convert, $preserve_tagsign = false)
{
    if ($preserve_tagsign) {
        $text_to_convert = preg_replace('/\&([gl])t;/', '&amp;\\1t;', $text_to_convert);
    }
    return @html_entity_decode($text_to_convert, ENT_NOQUOTES | ENT_XHTML, 'UTF-8');
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
 * @param $string (string) input string to convert
 * @param $dquotes (boolean) If true add slash in fron of double quotes;
 * @return converted string
 */
function F_compact_string($string, $dquotes = false)
{
    $repTable = array("\t" => ' ', "\n" => ' ', "\r" => ' ', "\0" => ' ', "\x0B" => ' ');
    if ($dquotes) {
        $repTable['"'] = '&quot;';
    }
    return strtr($string, $repTable);
}

/**
 * Replace angular parenthesis with html equivalents (html entities).
 * @param $str (string) input string to convert
 * @return converted string
 */
function F_replace_angulars($str)
{
    $replaceTable = array('<' => '&lt;', '>' => '&gt;');
    return strtr($str, $replaceTable);
}

/**
 * Performs a multi-byte safe substr() operation based on number of characters.
 * @param $str (string) input string
 * @param $start (int) substring start index
 * @param $length (int) substring max lenght
 * @return substring
 */
function F_substr_utf8($str, $start = 0, $length)
{
    $str .= ''; // force $str to be a string
    $bytelen = strlen($str);
    $i = 0;
    $j = 0;
    $str_start = 0;
    $str_end = $bytelen;
    while ($i < $bytelen) {
        if ($j == $start) {
            $str_start = $i;
        } elseif ($j == $length) {
            $str_end = $i;
            break;
        }
        $char = ord($str{$i}); // get one string character at time
        if ($char <= 0x7F) {
            $i += 1;
        } elseif (($char >> 0x05) == 0x06) { // 2 bytes character (0x06 = 110 BIN)
            $i += 2;
        } elseif (($char >> 0x04) == 0x0E) { // 3 bytes character (0x0E = 1110 BIN)
            $i += 3;
        } elseif (($char >> 0x03) == 0x1E) { // 4 bytes character (0x1E = 11110 BIN)
            $i += 4;
        } else {
            $i += 1;
        }
        ++$j;
    }
    $str = substr($str, $str_start, $str_end);
    return $str;
}

/**
 * Escape some special characters (&lt; &gt; &amp;).
 * @param $str (string) input string to convert
 * @return converted string
 */
function F_text_to_xml($str)
{
    $replaceTable = array("\0" => '', '&' => '&amp;', '<' => '&lt;', '>' => '&gt;');
    return strtr($str, $replaceTable);
}

/**
 * Unescape some special characters (&lt; &gt; &amp;).
 * @param $str (string) input string to convert
 * @return converted string
 */
function F_xml_to_text($str)
{
    $replaceTable = array('&amp;' => '&', '&lt;' => '<', '&gt;' => '>');
    return strtr($str, $replaceTable);
}

/**
 * Escape some special characters for TSV output.
 * @param $str (string) input string to convert
 * @return converted string
 */
function F_text_to_tsv($str)
{
    $replaceTable = array("\0" => '', "\t" => '\t', "\n" => '\n', "\r" => '\r');
    return strtr($str, $replaceTable);
}

/**
 * Unescape some special characters from TSV format.
 * @param $str (string) input string to convert
 * @return converted string
 */
function F_tsv_to_text($str)
{
    $replaceTable = array('\t' => "\t", '\n' => "\n", '\r' => "\r");
    return strtr($str, $replaceTable);
}

/**
 * Return a string containing an HTML acronym for required/not required fields.
 * @param $mode (int) field mode: 1=not required; 2=required.
 * @return html string
 */
function showRequiredField($mode = 1)
{
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
 * Strip whitespace (or other characters) from the beginning and end of an UTF-8 string and replace the "\xA0" with normal space.
 * @param $txt (string) The string that will be trimmed.
 * @return string The trimmed string.
 */
function utrim($txt)
{
    $txt = preg_replace('/\xA0/u', ' ', $txt);
    $txt = preg_replace('/^([\s]+)/u', '', $txt);
    $txt = preg_replace('/([\s]+)$/u', '', $txt);
    return $txt;
}

/**
 * Convert all IP addresses to IPv6 expanded notation.
 * @param $ip (string) IP address to normalize.
 * @return string IPv6 address in expanded notation or false in case of invalid input.
 * @since 7.1.000 (2009-02-13)
 */
function getNormalizedIP($ip)
{
    if (($ip == '0000:0000:0000:0000:0000:0000:0000:0001') or ($ip == '::1')) {
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
    if ((!$is_ipv4) and (!$is_ipv6)) {
        return false;
    }
    if ($is_ipv6 and $is_ipv4) {
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
 * @param $ip (string) IP address to convert.
 * @return int IP address as integer number.
 * @since 7.1.000 (2009-02-13)
 */
function getIpAsInt($ip)
{
    $ip = getNormalizedIP($ip);
    $ip = str_replace(':', '', $ip);
    return hexdec($ip);
}

/**
 * Converts a string containing an IP address into its integer value and return string representation.
 * @param $ip (string) IP address to convert.
 * @return int IP address as string.
 * @since 9.0.033 (2009-11-03)
 */
function getIpAsString($ip)
{
    $ip = getIpAsInt($ip);
    return sprintf('%.0f', $ip);
}

/**
 * Format a percentage number.
 * @param $num (float) number to be formatted
 * @return formatted string
 */
function F_formatFloat($num)
{
    return sprintf('%.03f', round($num, 3));
}

/**
 * Format a percentage number.
 * @param $num (float) Number to be formatted.
 * @param $ratio (boolean) Set to true if the number is a ratio between 0 and 1, false if is a percentage number between 0 an 100.
 * @return formatted string
 */
function F_formatPercentage($num, $ratio = true)
{
    if ($ratio) {
        $num = (100 * $num);
    }
    return '('.str_replace(' ', '&nbsp;', sprintf('% 3d', round($num))).'%)';
}

/**
 * format a percentage number
 * @param $num (float) number to be formatted
 * @param $ratio (boolean) Set to true if the number is a ratio between 0 and 1, false if is a percentage number between 0 an 100.
 * @return string
 */
function F_formatPdfPercentage($num, $ratio = true)
{
    if ($ratio) {
        $num = (100 * $num);
    }
    return '('.sprintf('% 3d', round($num)).'%)';
}


/**
 * format a percentage number for XML
 * @param $num (float) number to be formatted
 * @param $ratio (boolean) Set to true if the number is a ratio between 0 and 1, false if is a percentage number between 0 an 100.
 * @return string
 */
function F_formatXMLPercentage($num, $ratio = true)
{
    if ($ratio) {
        $num = (100 * $num);
    }
    return sprintf('%3d', round($num));
}

/**
 * Returns the UTC time offset in seconds
 * @param $timezone (string) current user timezone
 * @return int UTC time offset in seconds
 */
function F_getUTCoffset($timezone)
{
    $user_timezone = new DateTimeZone($timezone);
    $user_datetime = new DateTime('now', $user_timezone);
    return $user_timezone->getOffset($user_datetime);
}

/**
 * Returns the UTC time offset yo be used with CONVERT_TZ function
 * @param $timezone (string) current user timezone
 * @return string UTC time offset (+HH:mm)
 */
function F_db_getUTCoffset($timezone)
{
    $time_offset = F_getUTCoffset($timezone);
    $sign = ($time_offset >= 0)?'+':'-';
    return $sign.gmdate('H:i', abs($time_offset));
}

/**
 * Get data array in XML format.
 * @param $data (array) Array of data (key => value).
 * @param $level (int) Indentation level.
 * @return string XML data
 */
function getDataXML($data, $level = 1)
{
    $xml = '';
    $tb = str_repeat("\t", $level);
    foreach ($data as $key => $value) {
        $key = strtolower($key);
        $key = preg_replace('/[^a-z0-9]+/', '_', $key);
        if (is_numeric($key[0]) or ($key[0] == '_')) {
            $key = 'item'.$key;
        }
        $xml .= $tb.'<'.$key.'>';
        if (is_array($value)) {
            $xml .= "\n".getDataXML($value, ($level + 1));
        } else {
            $xml .= F_text_to_xml($value);
        }
        $xml .= '</'.$key.'>'."\n";
    }
    return $xml;
}

/**
 * Get data headers (keys) in TSV header (tab separated text values).
 * @param $data (array) Array of data (key => value).
 * @param $prefix (string) Prefix to add to keys.
 * @return string data
 */
function getDataTSVHeader($data, $prefix = '')
{
    $tsv = '';
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $tsv .= getDataTSVHeader($value, $prefix.$key.'_');
        } else {
            $tsv .= "\t".$prefix.$key;
        }
    }
    return $tsv;
}

/**
 * Get data in TSV format (tab separated text values).
 * @param $data (array) Array of data.
 * @return string XML data
 */
function getDataTSV($data)
{
    $tsv = '';
    foreach ($data as $value) {
        if (is_array($value)) {
            $tsv .= getDataTSV($value);
        } else {
            $tsv .= "\t".F_text_to_tsv($value);
        }
    }
    return $tsv;
}

/**
 * Convert HTML code to TSV string.
 * @param $str (string) HTML string to convert.
 * @return string TSV
 */
function F_html_to_TSV($str)
{
    $dollar_replacement = ":.dlr.:"; //string replacement for dollar symbol
    //tags conversion table
    $tags2textTable = array (
        "'<br[^>]*?>'i" => ' ',
        "'<table[^>]*?>'i" => "\n",
        "'</table>'i" => "\n",
        "'<tr[^>]*?>'i" => "\n",
        "'<th[^>]*?>'i" => "\t",
        "'<td[^>]*?>'i" => "\t",
        "'<h[0-9][^>]*?>'i" => "\n\n",
        "'</h[0-9]>'i" => "\n"
    );
    $str = str_replace('&nbsp;', ' ', $str);
    $str = str_replace('&rarr;', '-', $str);
    $str = str_replace('&darr;', '', $str);
    $str = str_replace("\t", ' ', $str);
    $str = preg_replace_callback('/colspan="([0-9]*)"/x', create_function('$matches', 'if ($matches[1] > 1) {return str_repeat("></td><td", ($matches[1] - 1));} return "";'), $str);
    $str = str_replace("\r\n", "\n", $str);
    $str = str_replace("\$", $dollar_replacement, $str); //replace special character
    //remove newlines
    $str = str_replace("\n", '', $str);
    $str = preg_replace(array_keys($tags2textTable), array_values($tags2textTable), $str);
    $str = preg_replace("'<[^>]*?>'si", '', $str); //strip out remaining tags
    //remove some newlines in excess
    $str = preg_replace("'[ \t\f]+[\r\n]'si", "\n", $str);
    $str = preg_replace("'[\r\n][\r\n]+'si", "\n\n", $str);
    $str = unhtmlentities($str, false);
    $str = str_replace($dollar_replacement, "\$", $str); //restore special character
    $str = rtrim($str);
    $str = ltrim($str, " \r\n\0\x0B");
    $str = stripslashes($str);
    return $str;
}

/**
 * Display table header element with order link.
 * @param $order_field (string) name of table field
 * @param $orderdir (string) order direction
 * @param $title title (string) field of anchor link
 * @param $name column (string) name
 * @param $current_order_field (string) current order field name
 * @param $filter (string) additional parameters to pass on URL
 * @return table header element string
 */
function F_select_table_header_element($order_field, $orderdir, $title, $name, $current_order_field = '', $filter = '')
{
    global $l;
    require_once('../config/tce_config.php');
    $ord = '';
    if ($order_field == $current_order_field) {
        if ($orderdir == 1) {
            $ord = ' <acronym title="'.$l['w_ascent'].'">&gt;</acronym>';
        } else {
            $ord = ' <acronym title="'.$l['w_descent'].'">&lt;</acronym>';
        }
    }
    $str = '<th><a href="'.$_SERVER['SCRIPT_NAME'].'?'.$filter.'&amp;firstrow=0&amp;order_field='.$order_field.'&amp;orderdir='.$orderdir.'" title="'.$title.'">'.$name.'</a>'.$ord.'</th>'."\n";
    return $str;
}

/**
 * Get a black or white color that maximize contrast.
 * @param $color (string) color in HEX format.
 * @return (string) Color.
 */
function getContrastColor($color)
{
    $r = hexdec(substr($color, 0, 2));
    $g = hexdec(substr($color, 2, 2));
    $b = hexdec(substr($color, 4, 2));
    // brightness of the selected color
    $br = (((299 * $r) + (587 * $g) + (114 * $b)) / 1000);
    if ($br < 128) {
        // white
        return 'ffffff';
    }
    // black
    return '000000';
}

/**
 * Returns true if the string is an URL.
 * @param $str (string) String to check.
 * @return boolean true or false.
 */
function F_isURL($str)
{
    if ((preg_match('/^(ftp|http|https|mail|sftp|ssh|telnet|vnc)[:][\/][\/]/', $str) > 0) and (parse_url($str) !== false)) {
        return true;
    }
    return false;
}

/**
 * Normalize the UTF-8 input string.
 * Modes greater than 0 requires php5-intl module.
 * Please edit this function to implement your custom normalization method.
 * @param $str (string) UTF-8 string to normalize.
 * @param $mode (int) Normalization type: NONE=None; C=Normalization Form C (NFC) - Canonical Decomposition followed by Canonical Composition; D=Normalization Form D (NFD) - Canonical Decomposition; KC=Normalization Form KC (NFKC) - Compatibility Decomposition, followed by Canonical Composition; KD=Normalization Form KD (NFKD) - Compatibility Decomposition; CUSTOM=Custom normalization using user defined function 'user_utf8_custom_normalizer'.
 * @return normalized string using the specified algorithm.
 */
function F_utf8_normalizer($str, $mode = 'NONE')
{
    switch ($mode) {
        case 'CUSTOM': {
            if (function_exists('user_utf8_custom_normalizer')) {
                return call_user_func('user_utf8_custom_normalizer', $str);
            } else {
                return $str;
            }
            break;
        }
        case 'C': {
            // Normalization Form C (NFC) - Canonical Decomposition followed by Canonical Composition
            return normalizer_normalize($str, Normalizer::FORM_C);
            break;
        }
        case 'D': {
            // Normalization Form D (NFD) - Canonical Decomposition
            return normalizer_normalize($str, Normalizer::FORM_D);
            break;
        }
        case 'KC': {
            // Normalization Form KC (NFKC) - Compatibility Decomposition, followed by Canonical Composition
            return normalizer_normalize($str, Normalizer::FORM_KC);
            break;
        }
        case 'KD': {
            // Normalization Form KD (NFKD) - Compatibility Decomposition
            return normalizer_normalize($str, Normalizer::FORM_KD);
            break;
        }
        case 'NONE':
        default: {
            return $str;
            break;
        }
    }
}

/**
 * Convert an long integer number to a Hexadecimal representation
 * @param string|int $dec Decimal number to convert.
 * @return string containing the HEX representation in uppercase.
 * @author Nicola Asuni
 * @since 2013-07-02
 */
function bcdechex($dec)
{
    $last = bcmod($dec, 16);
    $remain = bcdiv(bcsub($dec, $last), 16);
    if ($remain == 0) {
        return strtoupper(dechex($last));
    } else {
        return bcdechex($remain).strtoupper(dechex($last));
    }
}

//============================================================+
// END OF FILE
//============================================================+
