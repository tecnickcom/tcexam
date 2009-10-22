<?php
//============================================================+
// File name   : tce_export_custom.php
// Begin       : 2008-11-29
// Last Update : 2009-09-30
// 
// Description : Export all users' results in XML using a custom format (UNIWEX).
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
 * Export all users' results in XML using a custom format (UNIWEX).
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2008-11-29
 * @param int $_REQUEST['testid'] test ID
 * @param int $_REQUEST['groupid'] group ID
 * @param string $_REQUEST['orderfield'] ORDER BY portion of SQL selection query
 */

/**
 */

require_once('../config/tce_config.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../../shared/code/tce_functions_test.php');
require_once('../../shared/code/tce_functions_test_stats.php');

if (isset($_REQUEST['testid']) AND ($_REQUEST['testid'] > 0)) {
	$test_id = intval($_REQUEST['testid']);
	// check user's authorization
	require_once('../../shared/code/tce_authorization.php');
	if (!F_isAuthorizedUser(K_TABLE_TESTS, 'test_id', $test_id, 'test_user_id')) {
		exit;
	}
} else {
	exit;
}

if (isset($_REQUEST['groupid']) AND ($_REQUEST['groupid'] > 0)) {
	$group_id = intval($_REQUEST['groupid']);
} else {
	$group_id = 0;
}

// define symbols for answers list
$qtype = array('S', 'M', 'T', 'O'); // question types
$type = array('single', 'multiple', 'text', 'ordering');
$boolean = array('false', 'true');

// send XML headers
header('Content-Description: XML File Transfer');
header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
header('Pragma: public');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
// force download dialog
header('Content-Type: application/force-download');
header('Content-Type: application/octet-stream', false);
header('Content-Type: application/download', false);
header('Content-Type: application/xml', false);
// use the Content-Disposition header to supply a recommended filename
header('Content-Disposition: attachment; filename=tcexam_results_'.$_REQUEST['testid'].'_'.date('YmdHis').'.xml;');
header('Content-Transfer-Encoding: binary');

$xml = ''; // XML data to be returned

$xml .= '<'.'?xml version="1.0" encoding="ISO-8859-1" ?'.'>'.K_NEWLINE;

$xml .= '<!DOCTYPE XLSListaVerbali [ <!ELEMENT XLSListaVerbali ( Giudizi, GiudiziDesc, Voti, MateriaCod, MateriaDesc, MateriaCreditiDesc, XLSVerbale* ) ><!ATTLIST XLSListaVerbali version CDATA #REQUIRED ><!ELEMENT Giudizi (#PCDATA) > <!ATTLIST Giudizi value CDATA #IMPLIED ><!ELEMENT GiudiziDesc (#PCDATA) > <!ATTLIST GiudiziDesc value CDATA #IMPLIED ><!ELEMENT Voti (#PCDATA) > <!ATTLIST Voti value CDATA #IMPLIED ><!ELEMENT MateriaCod (#PCDATA) > <!ATTLIST MateriaCod value CDATA #IMPLIED ><!ELEMENT MateriaDesc (#PCDATA) > <!ATTLIST MateriaDesc value CDATA #IMPLIED ><!ELEMENT MateriaCreditiDesc (#PCDATA) > <!ATTLIST MateriaCreditiDesc value CDATA #IMPLIED ><!ELEMENT XLSVerbale ( Studente, Voto, Lode, Giudizio, Data, Quesito1, Quesito2, Quesito3, Note ) ><!ELEMENT Studente ( Matricola, CorsoDiLaurea, EsaCrediti, CfuDebito, Cognome, Nome ) ><!ELEMENT Matricola (#PCDATA) > <!ATTLIST Matricola value CDATA #REQUIRED ><!ELEMENT CorsoDiLaurea (#PCDATA) > <!ATTLIST CorsoDiLaurea value CDATA #IMPLIED ><!ELEMENT EsaCrediti (#PCDATA) > <!ATTLIST EsaCrediti value CDATA #IMPLIED ><!ELEMENT CfuDebito (#PCDATA) > <!ATTLIST CfuDebito value CDATA #IMPLIED ><!ELEMENT Nome (#PCDATA) > <!ATTLIST Nome value CDATA #IMPLIED ><!ELEMENT Cognome (#PCDATA) > <!ATTLIST Cognome value CDATA #IMPLIED > <!ELEMENT Voto (#PCDATA) > <!ATTLIST Voto value CDATA #IMPLIED ><!ELEMENT Lode (#PCDATA) > <!ATTLIST Lode value CDATA #IMPLIED ><!ELEMENT Giudizio (#PCDATA) > <!ATTLIST Giudizio value CDATA #IMPLIED ><!ELEMENT Data (#PCDATA) > <!ATTLIST Data value CDATA #IMPLIED ><!ELEMENT Quesito1 (#PCDATA) > <!ATTLIST Quesito1 value CDATA #IMPLIED ><!ELEMENT Quesito2 (#PCDATA) > <!ATTLIST Quesito2 value CDATA #IMPLIED ><!ELEMENT Quesito3 (#PCDATA) > <!ATTLIST Quesito3 value CDATA #IMPLIED ><!ELEMENT Note (#PCDATA) > <!ATTLIST Note value CDATA #IMPLIED > ]>'.K_NEWLINE;

$xml .= '<XLSListaVerbali version="1.2">'.K_NEWLINE;

$xml .= K_TAB.'<Giudizi value="AM,BN,ID,IN,NA,NI,OT,RE,RT,SF,ST"/>'.K_NEWLINE;
$xml .= K_TAB.'<GiudiziDesc value="AM - Ammesso,BN - Buono,ID - Idoneo,IN - Insufficiente,NA - Non Ammesso,NI - Non Idoneo,OT - Ottimo,RE - Respinto,RT - Ritirato,SU - Sufficiente,SO - Sostenuto"/>'.K_NEWLINE;
$xml .= K_TAB.'<Voti value="18,19,20,21,22,23,24,25,26,27,28,29,30"/>'.K_NEWLINE;
$xml .= K_TAB.'<MateriaCod value="CodMateria"/>'.K_NEWLINE;
$xml .= K_TAB.'<MateriaDesc value="DescMateria"/>'.K_NEWLINE;
$xml .= K_TAB.'<MateriaCreditiDesc value=""/>'.K_NEWLINE;

// get test data
$sql = 'SELECT test_score_threshold 
	FROM '.K_TABLE_TESTS.' 
	WHERE test_id='.$test_id.'
	LIMIT 1';
if($r = F_db_query($sql, $db)) {
	if($m = F_db_fetch_array($r)) {
		// test data
		$test_score_threshold = $m['test_score_threshold'];
	}
} else {
	F_display_db_error();
}

// for each user
$sql = 'SELECT testuser_id, user_id, SUM(testlog_score) AS total_score, MAX(testlog_change_time) AS test_end_time, testuser_creation_time, testuser_comment
	FROM '.K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER.', '.K_TABLE_USERS.' 
	WHERE testlog_testuser_id=testuser_id
		AND testuser_user_id=user_id 
		AND testuser_test_id='.$test_id.'';
if ($group_id > 0) {
	$sql .= ' AND testuser_user_id IN (
			SELECT usrgrp_user_id
			FROM '.K_TABLE_USERGROUP.' 
			WHERE usrgrp_group_id='.$group_id.'
		)';
}
$sql .= ' GROUP BY testuser_id, user_id, testuser_creation_time, testuser_comment';
if($r = F_db_query($sql, $db)) {
	while($m = F_db_fetch_array($r)) {
		$xml .= K_TAB.'<XLSVerbale>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.'<Studente>'.K_NEWLINE;
		$sqla = 'SELECT * 
		FROM '.K_TABLE_USERS.'
		WHERE user_id='.$m['user_id'].'
		LIMIT 1';
		if($ra = F_db_query($sqla, $db)) {
			if($ma = F_db_fetch_array($ra)) {
				if (isset($ma['user_regnumber']) AND !empty($ma['user_regnumber'])) {
					$matricola = F_text_to_xml($ma['user_regnumber']);
				} else {
					$matricola = F_text_to_xml($ma['user_name']);
				}
				$xml .= K_TAB.K_TAB.K_TAB.'<Matricola value="'.utf8_decode($matricola).'"/>'.K_NEWLINE;
				$xml .= K_TAB.K_TAB.K_TAB.'<CorsoDiLaurea value=""/>'.K_NEWLINE;
				$xml .= K_TAB.K_TAB.K_TAB.'<EsaCrediti value=""/>'.K_NEWLINE;
				$xml .= K_TAB.K_TAB.K_TAB.'<CfuDebito value=""/>'.K_NEWLINE;
				$xml .= K_TAB.K_TAB.K_TAB.'<Cognome value="'.F_text_to_xml(utf8_decode($ma['user_lastname'])).'"/>'.K_NEWLINE;
				$xml .= K_TAB.K_TAB.K_TAB.'<Nome value="'.F_text_to_xml(utf8_decode($ma['user_firstname'])).'"/>'.K_NEWLINE;
			}
		} else {
			F_display_db_error();
		}
		$xml .= K_TAB.K_TAB.'</Studente>'.K_NEWLINE;
		$voto = '';
		$giudizio = '';
		if (isset($test_score_threshold) AND ($test_score_threshold > 0)) {
			if ($m['total_score'] >= $test_score_threshold) {
				$giudizio = 'ID'; // idoneo
			} else {
				$giudizio = 'NI'; // non idoneo
			}
		} else {
			$voto = round($m['total_score'],0);
		}
		$xml .= K_TAB.K_TAB.'<Voto value="'.$voto.'"/>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.'<Lode value=""/>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.'<Giudizio value="'.$giudizio.'"/>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.'<Data value="'.date('d/m/Y', strtotime($m['test_end_time'])).'"/>'.K_NEWLINE;
		$quesito = array('', '', '');
		$sqls = 'SELECT *
			FROM '.K_TABLE_SUBJECTS.', '.K_TABLE_SUBJECT_SET.', '.K_TABLE_TEST_SUBJSET.'
			WHERE subjset_tsubset_id=tsubset_id
				AND subject_id=subjset_subject_id
				AND tsubset_test_id='.$test_id.'
			LIMIT 3';
		if($rs = F_db_query($sqls, $db)) {
			$i = 0;
			while($ms = F_db_fetch_array($rs)) {
				$quesito[$i] = F_text_to_xml(utf8_decode($ms['subject_name']));
				$i++;
			} 
		} else {
			F_display_db_error();
		}
		$xml .= K_TAB.K_TAB.'<Quesito1 value="'.$quesito[0].'"/>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.'<Quesito2 value="'.$quesito[1].'"/>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.'<Quesito3 value="'.$quesito[2].'"/>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.'<Note value=""/>'.K_NEWLINE;
		$xml .= K_TAB.'</XLSVerbale>'.K_NEWLINE;
	} 
} else {
	F_display_db_error();
}

$xml .= '</XLSListaVerbali>'.K_NEWLINE;
echo $xml;

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
