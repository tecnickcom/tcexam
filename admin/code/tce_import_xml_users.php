<?php
//============================================================+
// File name   : tce_import_xml_users.php
// Begin       : 2006-03-17
// Last Update : 2009-09-30
// 
// Description : Import users from an XML file or tab-delimited 
//               CSV file.
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
//    Additionally, you can't remove the original TCExam logo, copyrights statements
//    and links to Tecnick.com and TCExam websites.
//    
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * Import users from an XML file or CSV (Tab delimited text file).
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2006-03-17
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_USERS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_user_importer'];
require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');

switch($menu_mode) {

	case 'upload': {
		if($_FILES['userfile']['name']) {
			require_once('../code/tce_functions_upload.php');
			// upload file
			$uploadedfile = F_upload_file('userfile', K_PATH_CACHE);
			if ($uploadedfile !== false) {
				switch ($file_type) {
					case 1: {
						$xmlimporter = new XMLUserImporter(K_PATH_CACHE.$uploadedfile);
						F_print_error('MESSAGE', $l['m_importing_complete']);
						break;
					}
					case 2: {
						if (F_import_csv_users(K_PATH_CACHE.$uploadedfile)) {
							F_print_error('MESSAGE', $l['m_importing_complete']);
						}
						break;
					}
				}
			}
		}
		break;
	}

	default: { 
		break;
	}

} //end of switch
?>

<div class="container">

<div class="tceformbox">
<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data" id="form_importusers">

<div class="row">
<span class="label">
<label for="userfile"><?php echo $l['w_upload_file']; ?></label>
</span>
<span class="formw">
<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo K_MAX_UPLOAD_SIZE ?>" />
<input type="file" name="userfile" id="userfile" size="20" title="<?php echo $l['h_upload_file']; ?>" />
</span>
&nbsp;
</div>

<div class="row">
<div class="formw">
<fieldset class="noborder">
<legend title="<?php echo $l['h_file_type']; ?>"><?php echo $l['w_type']; ?></legend>

<input type="radio" name="file_type" id="file_type_xml" value="1" checked="checked" title="<?php echo $l['h_file_type_xml']; ?>" />
<label for="file_type_xml">XML</label>
<br />
<input type="radio" name="file_type" id="file_type_csv" value="2" title="<?php echo $l['h_file_type_csv']; ?>" />
<label for="file_type_csv">CSV</label>
</fieldset>
</div>
</div>

<div class="row">
<?php
// show buttons by case
F_submit_button("upload", $l['w_upload'], $l['h_submit_file']);
?>
</div>

</form>

</div>
<?php

echo '<div class="pagehelp">'.$l['hp_import_xml_users'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

// ------------------------------------------------------------

/**
 * This PHP Class imports users and groups data directly from a XML file.
 *
 * @package com.tecnick.tcexam.admin
 * @name XMLUserImporter
 * @abstract XML users and groups importer
 * @license http://www.gnu.org/copyleft/lesser.html GPL
 * @author Nicola Asuni [www.tecnick.com]
 * @copyright Copyright (c) 2004-2010 - Nicola Asuni - Tecnick.com S.r.l (www.tecnick.com) - Via della Pace n.11 - 09044 Quartucciu (CA) - ITALY - www.tecnick.com - info@tecnick.com
 * @version 1.0.000
 */
class XMLUserImporter {
	
	/**
	 * @var string Current data element.
	 * @access private
	 */
	private $current_element = '';
	
	/**
	 * @var string Current data value.
	 * @access private
	 */
	private $current_data = '';
	
	/**
	 * @var array Array for storing user data.
	 * @access private
	 */
	private $user_data = Array();
	
	/**
	 * @var array Array for storing user's group data.
	 * @access private
	 */
	private $group_data = Array();
	
	/**
	 * @var int ID of last inserted user (counter)
	 * @access private
	 */
	private $user_id = 0;
	
	/**
	 * @var string XML file
	 * @access private
	 */
	private $xmlfile = '';
	
	/**
	 * Class constructor.
	 * @param string $xmlfile XML file name
	 */
	public function __construct($xmlfile) {
		// set xml file
		$this->xmlfile = $xmlfile;
		// creates a new XML parser to be used by the other XML functions
		$this->parser = xml_parser_create();
		// the following function allows to use parser inside object
		xml_set_object($this->parser, $this);
		// disable case-folding for this XML parser
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
		// sets the element handler functions for the XML parser
		xml_set_element_handler($this->parser, 'startElementHandler', 'endElementHandler');
		// sets the character data handler function for the XML parser
		xml_set_character_data_handler($this->parser, 'segContentHandler');
		// start parsing an XML document
		if(!xml_parse($this->parser, file_get_contents($xmlfile))) {
			die(sprintf('ERROR xmlResourceBundle :: XML error: %s at line %d',
			xml_error_string(xml_get_error_code($this->parser)),
			xml_get_current_line_number($this->parser)));
		}
		// free this XML parser
		xml_parser_free($this->parser);
	}
	
	/**
	 * Class destructor;
	 */
	public function __destruct() {
		// delete uploaded file
		@unlink($this->xmlfile);
	}
	
	/**
	 * Sets the start element handler function for the XML parser parser.start_element_handler.
	 * @param resource $parser The first parameter, parser, is a reference to the XML parser calling the handler.
	 * @param string $name The second parameter, name, contains the name of the element for which this handler is called. If case-folding is in effect for this parser, the element name will be in uppercase letters. 
	 * @param array $attribs The third parameter, attribs, contains an associative array with the element's attributes (if any). The keys of this array are the attribute names, the values are the attribute values. Attribute names are case-folded on the same criteria as element names. Attribute values are not case-folded. The original order of the attributes can be retrieved by walking through attribs the normal way, using each(). The first key in the array was the first attribute, and so on. 
	 * @access private
	 */
	private function startElementHandler($parser, $name, $attribs) {
		$name = strtolower($name);
		switch($name) {
			case 'user': {
				$this->user_data = Array();
				$this->group_data = Array();
				$this->current_data = '';
				break;
			}
			case 'name':
			case 'password':
			case 'email':
			case 'regdate':
			case 'ip':
			case 'firstname':
			case 'lastname':
			case 'birthdate':
			case 'birthplace':
			case 'regnumber':
			case 'ssn':
			case 'level':
			case 'verifycode': {
				$this->current_element = 'user_'.$name;
				$this->current_data = '';
				break;
			}
			case 'group': {
				$this->current_element = 'group_name';
				$this->current_data = '';
				break;
			}
			default: {
				break;
			}
		}
	}
	
	/**
	 * Sets the end element handler function for the XML parser parser.end_element_handler.
	 * @param resource $parser The first parameter, parser, is a reference to the XML parser calling the handler.
	 * @param string $name The second parameter, name, contains the name of the element for which this handler is called. If case-folding is in effect for this parser, the element name will be in uppercase letters. 
	 * @access private
	 */
	private function endElementHandler($parser, $name) {
		global $l, $db;
		require_once('../config/tce_config.php');
		
		switch(strtolower($name)) {
			case 'name':
			case 'password':
			case 'email':
			case 'regdate':
			case 'ip':
			case 'firstname':
			case 'lastname':
			case 'birthdate':
			case 'birthplace':
			case 'regnumber':
			case 'ssn':
			case 'level':
			case 'verifycode': {
				$this->current_data = F_escape_sql(F_xml_to_text($this->current_data));
				$this->user_data[$this->current_element] = $this->current_data;
				$this->current_element = '';
				$this->current_data = '';
				break;
			}
			case 'group': {
				$group_name = F_escape_sql(F_xml_to_text($this->current_data));
				// check if group already exist
				$sql = 'SELECT group_id 
					FROM '.K_TABLE_GROUPS.' 
					WHERE group_name=\''.$group_name.'\'
					LIMIT 1';
				if($r = F_db_query($sql, $db)) {
					if($m = F_db_fetch_array($r)) {
						// the group has been already added
						$this->group_data[] = $m['group_id'];
					} else {
						// add new group
						$sqli = 'INSERT INTO '.K_TABLE_GROUPS.' (
							group_name
							) VALUES (
							\''.$group_name.'\'
							)';
						if(!$ri = F_db_query($sqli, $db)) {
							F_display_db_error(false);
						} else {
							$this->group_data[] = F_db_insert_id($db, K_TABLE_GROUPS, 'group_id');
						}
					}
				} else {
					F_display_db_error();
				}
				
				break;
			}
			case 'user': {
				// insert users
				if (!empty($this->user_data['user_name'])) {
					if (empty($this->user_data['user_regdate'])) {
						$this->user_data['user_regdate'] = date(K_TIMESTAMP_FORMAT);
					}
					if (empty($this->user_data['user_ip'])) {
						$this->user_data['user_ip'] = getNormalizedIP($_SERVER['REMOTE_ADDR']);
					}
					if (empty($this->user_data['user_level'])) {
						$this->user_data['user_level'] = 1;
					}
					// check if user already exist
					$sql = 'SELECT user_id 
						FROM '.K_TABLE_USERS.' 
						WHERE user_name=\''.$this->user_data['user_name'].'\'
							OR user_regnumber=\''.$this->user_data['user_regnumber'].'\'
							OR user_ssn=\''.$this->user_data['user_ssn'].'\'
						LIMIT 1';
					if($r = F_db_query($sql, $db)) {
						if($m = F_db_fetch_array($r)) {
							// the user has been already added
							$user_id = $m['user_id'];
							
							//update user data
							$sqlu = 'UPDATE '.K_TABLE_USERS.' SET 
								user_regdate=\''.$this->user_data['user_regdate'].'\',
								user_ip=\''.$this->user_data['user_ip'].'\',
								user_name=\''.$this->user_data['user_name'].'\',
								user_email='.F_empty_to_null($this->user_data['user_email']).',
								user_password=\''.md5($this->user_data['user_password']).'\',
								user_regnumber='.F_empty_to_null($this->user_data['user_regnumber']).',
								user_firstname='.F_empty_to_null($this->user_data['user_firstname']).',
								user_lastname='.F_empty_to_null($this->user_data['user_lastname']).',
								user_birthdate='.F_empty_to_null($this->user_data['user_birthdate']).',
								user_birthplace='.F_empty_to_null($this->user_data['user_birthplace']).',
								user_ssn='.F_empty_to_null($this->user_data['user_ssn']).',
								user_level=\''.$this->user_data['user_level'].'\',
								user_verifycode='.F_empty_to_null($this->user_data['user_verifycode']).'
								WHERE user_id='.$user_id.'';
							if(!$ru = F_db_query($sqlu, $db)) {
								F_display_db_error(false);
								return FALSE;
							}
							
						} else {
							// add new user
							$sqlu = 'INSERT INTO '.K_TABLE_USERS.' (
								user_regdate, 
								user_ip, 
								user_name, 
								user_email, 
								user_password, 
								user_regnumber,
								user_firstname, 
								user_lastname, 
								user_birthdate, 
								user_birthplace, 
								user_ssn, 
								user_level,
								user_verifycode
								) VALUES (
								'.F_empty_to_null($this->user_data['user_regdate']).',
								\''.$this->user_data['user_ip'].'\',
								\''.$this->user_data['user_name'].'\',
								'.F_empty_to_null($this->user_data['user_email']).',
								\''.md5($this->user_data['user_password']).'\',
								'.F_empty_to_null($this->user_data['user_regnumber']).',
								'.F_empty_to_null($this->user_data['user_firstname']).',
								'.F_empty_to_null($this->user_data['user_lastname']).',
								'.F_empty_to_null($this->user_data['user_birthdate']).',
								'.F_empty_to_null($this->user_data['user_birthplace']).',
								'.F_empty_to_null($this->user_data['user_ssn']).',
								\''.$this->user_data['user_level'].'\',
								'.F_empty_to_null($this->user_data['user_verifycode']).'
								)';
							if(!$ru = F_db_query($sqlu, $db)) {
								F_display_db_error(false);
								return FALSE;
							} else {
								$user_id = F_db_insert_id($db, K_TABLE_USERS, 'user_id');
							}
						}
					} else {
						F_display_db_error(false);
						return FALSE;
					}
					
					// user's groups
					if (!empty($this->group_data)) {
						while(list($key,$group_id)=each($this->group_data)) {
							// check if user-group already exist
							$sqls = 'SELECT * 
								FROM '.K_TABLE_USERGROUP.' 
								WHERE usrgrp_group_id=\''.$group_id.'\'
									AND usrgrp_user_id=\''.$user_id.'\'
								LIMIT 1';
							if($rs = F_db_query($sqls, $db)) {
								if(!$ms = F_db_fetch_array($rs)) {
									// associate group to user
									$sqlg = 'INSERT INTO '.K_TABLE_USERGROUP.' (
										usrgrp_user_id,
										usrgrp_group_id
										) VALUES (
										'.$user_id.',
										'.$group_id.'
										)';
									if(!$rg = F_db_query($sqlg, $db)) {
										F_display_db_error(false);
										return FALSE;
									}
								}
							} else {
								F_display_db_error(false);
								return FALSE;
							}
						}
					}
				}
				break;
			}
			default: {
				break;
			}
		}
	}
	
	/**
	 * Sets the character data handler function for the XML parser parser.handler.
	 * @param resource $parser The first parameter, parser, is a reference to the XML parser calling the handler.
	 * @param string $data The second parameter, data, contains the character data as a string. 
	 * @access private
	 */
	private function segContentHandler($parser, $data) {
		if (strlen($this->current_element) > 0) {
			// we are inside an element
			$this->current_data .= $data;
		}
	}
	
} // END OF CLASS

/**
 * Import users from CSV file (tab delimited text).
 * The format of CSV is the same obtained by exporting data from Users Selection Form.
 * @param string $csvfile CSV (tab delimited text) file name
 * @return boolean TRUE in case of success, FALSE otherwise
 */
function F_import_csv_users($csvfile) {
	global $l, $db;
	require_once('../config/tce_config.php');
	
	// get file content as array
	$csvrows = file($csvfile); // array of CSV lines
	if ($csvrows === FALSE) {
		return FALSE;
	}
	
	// move pointer to second line (discard headers)
	next($csvrows);
	
	// for each row
	while (list($item, $rowdata) = each($csvrows)) {
		// get user data into array
		$userdata = explode("\t", $rowdata);
		
		// set some default values
		if (empty($userdata[4])) {
			$userdata[4] = date(K_TIMESTAMP_FORMAT);
		}
		if (empty($userdata[5])) {
			$userdata[5] = getNormalizedIP($_SERVER['REMOTE_ADDR']);
		}
		if (empty($userdata[12])) {
			$userdata[12] = 1;
		}
		
		// check if user already exist
		$sql = 'SELECT user_id 
			FROM '.K_TABLE_USERS.' 
			WHERE user_name=\''.F_escape_sql($userdata[1]).'\'
				OR user_regnumber='.F_empty_to_null(F_escape_sql($userdata[10])).'
				OR user_ssn='.F_empty_to_null(F_escape_sql($userdata[11])).'
			LIMIT 1';
		if($r = F_db_query($sql, $db)) {
			if($m = F_db_fetch_array($r)) {
				// the user has been already added
				$user_id = $m['user_id'];
				
				//update user data
				$sqlu = 'UPDATE '.K_TABLE_USERS.' SET 
					user_name=\''.F_escape_sql($userdata[1]).'\',
					user_password=\''.md5($userdata[2]).'\',
					user_email='.F_empty_to_null(F_escape_sql($userdata[3])).',
					user_regdate=\''.F_escape_sql($userdata[4]).'\',
					user_ip=\''.F_escape_sql($userdata[5]).'\',
					user_firstname='.F_empty_to_null(F_escape_sql($userdata[6])).',
					user_lastname='.F_empty_to_null(F_escape_sql($userdata[7])).',
					user_birthdate='.F_empty_to_null(F_escape_sql($userdata[8])).',
					user_birthplace='.F_empty_to_null(F_escape_sql($userdata[9])).',
					user_regnumber='.F_empty_to_null(F_escape_sql($userdata[10])).',
					user_ssn='.F_empty_to_null(F_escape_sql($userdata[11])).',
					user_level=\''.intval($userdata[12]).'\',
					user_verifycode='.F_empty_to_null(F_escape_sql($userdata[13])).'
					WHERE user_id='.$user_id.'';
				if(!$ru = F_db_query($sqlu, $db)) {
					F_display_db_error(false);
					return FALSE;
				}
				
			} else {
				// add new user
				$sqlu = 'INSERT INTO '.K_TABLE_USERS.' (
					user_name, 
					user_password, 
					user_email, 
					user_regdate, 
					user_ip, 
					user_firstname, 
					user_lastname, 
					user_birthdate, 
					user_birthplace, 
					user_regnumber,
					user_ssn, 
					user_level,
					user_verifycode
					) VALUES (
					\''.F_escape_sql($userdata[1]).'\',
					\''.md5($userdata[2]).'\',
					'.F_empty_to_null(F_escape_sql($userdata[3])).',
					\''.F_escape_sql($userdata[4]).'\',
					\''.F_escape_sql($userdata[5]).'\',
					'.F_empty_to_null(F_escape_sql($userdata[6])).',
					'.F_empty_to_null(F_escape_sql($userdata[7])).',
					'.F_empty_to_null(F_escape_sql($userdata[8])).',
					'.F_empty_to_null(F_escape_sql($userdata[9])).',
					'.F_empty_to_null(F_escape_sql($userdata[10])).',
					'.F_empty_to_null(F_escape_sql($userdata[11])).',
					\''.intval($userdata[12]).'\',
					'.F_empty_to_null(F_escape_sql($userdata[13])).'
					)';
				if(!$ru = F_db_query($sqlu, $db)) {
					F_display_db_error(false);
					return FALSE;
				} else {
					$user_id = F_db_insert_id($db, K_TABLE_USERS, 'user_id');
				}
			}
		} else {
			F_display_db_error(false);
			return FALSE;
		}
		
		// user's groups
		if (!empty($userdata[14])) {
			$groups = explode(',', addslashes($userdata[14]));
			while(list($key,$group_name)=each($groups)) {
				$group_name = F_escape_sql($group_name);
				// check if group already exist
				$sql = 'SELECT group_id 
					FROM '.K_TABLE_GROUPS.' 
					WHERE group_name=\''.$group_name.'\'
					LIMIT 1';
				if($r = F_db_query($sql, $db)) {
					if($m = F_db_fetch_array($r)) {
						// the group already exist
						$group_id = $m['group_id'];
					} else {
						// creat a new group
						$sqli = 'INSERT INTO '.K_TABLE_GROUPS.' (
							group_name
							) VALUES (
							\''.$group_name.'\'
							)';
						if(!$ri = F_db_query($sqli, $db)) {
							F_display_db_error(false);
							return FALSE;
						} else {
							$group_id = F_db_insert_id($db, K_TABLE_GROUPS, 'group_id');
						}
					}
				} else {
					F_display_db_error(false);
					return FALSE;
				}
				// check if user-group already exist
				$sqls = 'SELECT * 
					FROM '.K_TABLE_USERGROUP.' 
					WHERE usrgrp_group_id=\''.$group_id.'\'
						AND usrgrp_user_id=\''.$user_id.'\'
					LIMIT 1';
				if($rs = F_db_query($sqls, $db)) {
					if(!$ms = F_db_fetch_array($rs)) {
						// associate group to user
						$sqlg = 'INSERT INTO '.K_TABLE_USERGROUP.' (
							usrgrp_user_id,
							usrgrp_group_id
							) VALUES (
							'.$user_id.',
							'.$group_id.'
							)';
						if(!$rg = F_db_query($sqlg, $db)) {
							F_display_db_error(false);
							return FALSE;
						}
					}
				} else {
					F_display_db_error(false);
					return FALSE;
				}
			}
		}
	}
	
	return TRUE;
}

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
