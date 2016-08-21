<?php
//============================================================+
// File name   : tce_xml_users.php
// Begin       : 2006-03-17
// Last Update : 2013-09-05
//
// Description : Functions to export users' accounts using
//               XML or JSON format.
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
//    Copyright (C) 2004-2013  Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Display all users in XML or JSON format.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2006-03-17
 */

/**
 */

// check user's authorization
require_once('../config/tce_config.php');
$pagelevel = K_AUTH_EXPORT_USERS;
require_once('../../shared/code/tce_authorization.php');

$output_format = isset($_REQUEST['format']) ? strtoupper($_REQUEST['format']) : 'XML';
$out_filename = 'tcexam_users_'.date('YmdHis');
$xml = F_xml_export_users();

switch ($output_format) {
    case 'JSON': {
        header('Content-Description: JSON File Transfer');
        header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
        header('Pragma: public');
        header('Expires: Thu, 04 Jan 1973 00:00:00 GMT'); // Date in the past
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        // force download dialog
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream', false);
        header('Content-Type: application/download', false);
        header('Content-Type: application/json', false);
        // use the Content-Disposition header to supply a recommended filename
        header('Content-Disposition: attachment; filename='.$out_filename.'.json;');
        header('Content-Transfer-Encoding: binary');
        $xmlobj = new SimpleXMLElement($xml);
        echo json_encode($xmlobj);
        break;
    }
    case 'XML':
    default: {
        header('Content-Description: XML File Transfer');
        header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
        header('Pragma: public');
        header('Expires: Thu, 04 Jan 1973 00:00:00 GMT'); // Date in the past
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        // force download dialog
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream', false);
        header('Content-Type: application/download', false);
        header('Content-Type: application/xml', false);
        // use the Content-Disposition header to supply a recommended filename
        header('Content-Disposition: attachment; filename='.$out_filename.'.xml;');
        header('Content-Transfer-Encoding: binary');
        echo $xml;
        break;
    }
}

/**
 * Export all users to XML grouped by users' groups.
 * @author Nicola Asuni
 * @since 2006-03-17
 * @return XML data
 */
function F_xml_export_users()
{
    global $l, $db;
    require_once('../config/tce_config.php');

    $boolean = array('false','true');

    $xml = ''; // XML data to be returned

    $xml .= '<'.'?xml version="1.0" encoding="UTF-8" ?'.'>'.K_NEWLINE;
    $xml .= '<tcexamusers version="'.K_TCEXAM_VERSION.'">'.K_NEWLINE;
    $xml .=  K_TAB.'<header';
    $xml .= ' lang="'.K_USER_LANG.'"';
    $xml .= ' date="'.date(K_TIMESTAMP_FORMAT).'">'.K_NEWLINE;
    $xml .= K_TAB.'</header>'.K_NEWLINE;
    $xml .=  K_TAB.'<body>'.K_NEWLINE;

    // select users
    $sqla = 'SELECT * FROM '.K_TABLE_USERS.' WHERE (user_id>1)';
    if ($_SESSION['session_user_level'] < K_AUTH_ADMINISTRATOR) {
        // filter for level
        $sqla .= ' AND ((user_level<'.$_SESSION['session_user_level'].') OR (user_id='.$_SESSION['session_user_id'].'))';
        // filter for groups
        $sqla .= ' AND user_id IN (SELECT tb.usrgrp_user_id
			FROM '.K_TABLE_USERGROUP.' AS ta, '.K_TABLE_USERGROUP.' AS tb
			WHERE ta.usrgrp_group_id=tb.usrgrp_group_id
				AND ta.usrgrp_user_id='.intval($_SESSION['session_user_id']).'
				AND tb.usrgrp_user_id=user_id)';
    }
    $sqla .= ' ORDER BY user_lastname,user_firstname,user_name';
    if ($ra = F_db_query($sqla, $db)) {
        while ($ma = F_db_fetch_array($ra)) {
            $xml .= K_TAB.K_TAB.K_TAB.'<user id="'.$ma['user_id'].'">'.K_NEWLINE;

            $xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<name>';
            $xml .= F_text_to_xml($ma['user_name']);
            $xml .= '</name>'.K_NEWLINE;

            $xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<password>';
            // password cannot be exported because is encrypted
            //$xml .= $ma['user_password'];
            $xml .= '</password>'.K_NEWLINE;

            $xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<email>';
            $xml .= $ma['user_email'];
            $xml .= '</email>'.K_NEWLINE;

            $xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<regdate>';
            $xml .= $ma['user_regdate'];
            $xml .= '</regdate>'.K_NEWLINE;

            $xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<ip>';
            $xml .= $ma['user_ip'];
            $xml .= '</ip>'.K_NEWLINE;

            $xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<firstname>';
            $xml .= F_text_to_xml($ma['user_firstname']);
            $xml .= '</firstname>'.K_NEWLINE;

            $xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<lastname>';
            $xml .= F_text_to_xml($ma['user_lastname']);
            $xml .= '</lastname>'.K_NEWLINE;

            $xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<birthdate>';
            $xml .= substr($ma['user_birthdate'], 0, 10);
            $xml .= '</birthdate>'.K_NEWLINE;

            $xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<birthplace>';
            $xml .= F_text_to_xml($ma['user_birthplace']);
            $xml .= '</birthplace>'.K_NEWLINE;

            $xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<regnumber>';
            $xml .= F_text_to_xml($ma['user_regnumber']);
            $xml .= '</regnumber>'.K_NEWLINE;

            $xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<ssn>';
            $xml .= F_text_to_xml($ma['user_ssn']);
            $xml .= '</ssn>'.K_NEWLINE;

            $xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<level>';
            $xml .= $ma['user_level'];
            $xml .= '</level>'.K_NEWLINE;

            $xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<verifycode>';
            $xml .= $ma['user_verifycode'];
            $xml .= '</verifycode>'.K_NEWLINE;

            $xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<otpkey>';
            $xml .= $ma['user_otpkey'];
            $xml .= '</otpkey>'.K_NEWLINE;

            // add user's groups
            $sqlg = 'SELECT *
				FROM '.K_TABLE_GROUPS.', '.K_TABLE_USERGROUP.'
				WHERE usrgrp_group_id=group_id
					AND usrgrp_user_id='.$ma['user_id'].'
				ORDER BY group_name';
            if ($rg = F_db_query($sqlg, $db)) {
                while ($mg = F_db_fetch_array($rg)) {
                    $xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<group id="'.$mg['group_id'].'">';
                    $xml .= $mg['group_name'];
                    $xml .= '</group>'.K_NEWLINE;
                }
            } else {
                F_display_db_error();
            }

            $xml .= K_TAB.K_TAB.K_TAB.'</user>'.K_NEWLINE;
        }
    } else {
        F_display_db_error();
    }

    $xml .= K_TAB.'</body>'.K_NEWLINE;
    $xml .= '</tcexamusers>'.K_NEWLINE;
    return $xml;
}

//============================================================+
// END OF FILE
//============================================================+
