<?php
//============================================================+
// File name   : tce_import_users.php
// Begin       : 2006-03-17
// Last Update : 2018-11-29
//
// Description : Import users from an XML file or tab-delimited
//               TSV file.
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
//    Copyright (C) 2004-2012  Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Import users from an XML file or TSV (Tab delimited text file).
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2006-03-17
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_IMPORT_USERS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_user_importer'];
require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');

switch ($menu_mode) {
    case 'upload': {
        if ($_FILES['userfile']['name']) {
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
                        if (F_import_tsv_users(K_PATH_CACHE.$uploadedfile)) {
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
<input type="radio" name="file_type" id="file_type_tsv" value="2" title="<?php echo $l['h_file_type_tsv']; ?>" />
<label for="file_type_tsv">TSV</label>
</fieldset>
</div>
</div>

<div class="row">
<?php
// show buttons by case
F_submit_button("upload", $l['w_upload'], $l['h_submit_file']);
echo F_getCSRFTokenField().K_NEWLINE;
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
 * @class XMLUserImporter
 * This PHP Class imports users and groups data directly from a XML file.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni [www.tecnick.com]
 * @version 1.0.000
 */
class XMLUserImporter
{

    /**
     * String Current data element.
     * @private
     */
    private $current_element = '';

    /**
     * String Current data value.
     * @private
     */
    private $current_data = '';

    /**
     * Array Array for storing user data.
     * @private
     */
    private $user_data = array();

    /**
     * Array for storing user's group data.
     * @private
     */
    private $group_data = array();

    /**
     * Int ID of last inserted user (counter)
     * @private
     */
    private $user_id = 0;

    /**
     * String XML file
     * @private
     */
    private $xmlfile = '';

    /**
     * Class constructor.
     * @param $xmlfile (string) XML file name
     */
    public function __construct($xmlfile)
    {
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
        if (!xml_parse($this->parser, file_get_contents($xmlfile))) {
            die(sprintf(
                'ERROR xmlResourceBundle :: XML error: %s at line %d',
                xml_error_string(xml_get_error_code($this->parser)),
                xml_get_current_line_number($this->parser)
            ));
        }
        // free this XML parser
        xml_parser_free($this->parser);
    }

    /**
     * Class destructor;
     */
    public function __destruct()
    {
        // delete uploaded file
        @unlink($this->xmlfile);
    }

    /**
     * Sets the start element handler function for the XML parser parser.start_element_handler.
     * @param $parser (resource) The first parameter, parser, is a reference to the XML parser calling the handler.
     * @param $name (string) The second parameter, name, contains the name of the element for which this handler is called. If case-folding is in effect for this parser, the element name will be in uppercase letters.
     * @param $attribs (array) The third parameter, attribs, contains an associative array with the element's attributes (if any). The keys of this array are the attribute names, the values are the attribute values. Attribute names are case-folded on the same criteria as element names. Attribute values are not case-folded. The original order of the attributes can be retrieved by walking through attribs the normal way, using each(). The first key in the array was the first attribute, and so on.
     * @private
     */
    private function startElementHandler($parser, $name, $attribs)
    {
        $name = strtolower($name);
        switch ($name) {
            case 'user': {
                $this->user_data = array();
                $this->group_data = array();
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
            case 'verifycode':
            case 'otpkey': {
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
     * @param $parser (resource) The first parameter, parser, is a reference to the XML parser calling the handler.
     * @param $name (string) The second parameter, name, contains the name of the element for which this handler is called. If case-folding is in effect for this parser, the element name will be in uppercase letters.
     * @private
     */
    private function endElementHandler($parser, $name)
    {
        global $l, $db;
        require_once('../config/tce_config.php');
        require_once('tce_functions_user_select.php');

        switch (strtolower($name)) {
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
            case 'verifycode':
            case 'otpkey': {
                $this->current_data = F_escape_sql($db, F_xml_to_text($this->current_data));
                $this->user_data[$this->current_element] = $this->current_data;
                $this->current_element = '';
                $this->current_data = '';
                break;
            }
            case 'group': {
                $group_name = F_escape_sql($db, F_xml_to_text($this->current_data));
                // check if group already exist
                $sql = 'SELECT group_id
					FROM '.K_TABLE_GROUPS.'
					WHERE group_name=\''.$group_name.'\'
					LIMIT 1';
                if ($r = F_db_query($sql, $db)) {
                    if ($m = F_db_fetch_array($r)) {
                        // the group has been already added
                        $this->group_data[] = $m['group_id'];
                    } else {
                        // add new group
                        $sqli = 'INSERT INTO '.K_TABLE_GROUPS.' (
							group_name
							) VALUES (
							\''.$group_name.'\'
							)';
                        if (!$ri = F_db_query($sqli, $db)) {
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
                    if (!isset($this->user_data['user_level']) or (strlen($this->user_data['user_level']) == 0)) {
                        $this->user_data['user_level'] = 1;
                    }
                    if ($_SESSION['session_user_level'] < K_AUTH_ADMINISTRATOR) {
                        // you cannot edit a user with a level equal or higher than yours
                        $this->user_data['user_level'] = min(max(0, ($_SESSION['session_user_level'] - 1)), $this->user_data['user_level']);
                        // non-administrator can access only to his/her groups
                        if (empty($this->group_data)) {
                            break;
                        }
                        $common_groups = array_intersect(F_get_user_groups($_SESSION['session_user_id']), $this->group_data);
                        if (empty($common_groups)) {
                            break;
                        }
                    }
                    // check if user already exist
                    $sql = 'SELECT user_id,user_level
						FROM '.K_TABLE_USERS.'
						WHERE user_name=\''.$this->user_data['user_name'].'\'
							OR user_regnumber=\''.$this->user_data['user_regnumber'].'\'
							OR user_ssn=\''.$this->user_data['user_ssn'].'\'
						LIMIT 1';
                    if ($r = F_db_query($sql, $db)) {
                        if ($m = F_db_fetch_array($r)) {
                            // the user has been already added
                            $user_id = $m['user_id'];
                            if (($_SESSION['session_user_level'] >= K_AUTH_ADMINISTRATOR) or ($_SESSION['session_user_level'] > $m['user_level'])) {
                                //update user data
                                $sqlu = 'UPDATE '.K_TABLE_USERS.' SET
									user_regdate=\''.$this->user_data['user_regdate'].'\',
									user_ip=\''.$this->user_data['user_ip'].'\',
									user_name=\''.$this->user_data['user_name'].'\',
									user_email='.F_empty_to_null($this->user_data['user_email']).',';
                                // update password only if it is specified
                                if (!empty($this->user_data['user_password'])) {
                                    $sqlu .= ' user_password=\''.F_escape_sql($db, getPasswordHash($this->user_data['user_password'])).'\',';
                                }
                                $sqlu .= '
									user_regnumber='.F_empty_to_null($this->user_data['user_regnumber']).',
									user_firstname='.F_empty_to_null($this->user_data['user_firstname']).',
									user_lastname='.F_empty_to_null($this->user_data['user_lastname']).',
									user_birthdate='.F_empty_to_null($this->user_data['user_birthdate']).',
									user_birthplace='.F_empty_to_null($this->user_data['user_birthplace']).',
									user_ssn='.F_empty_to_null($this->user_data['user_ssn']).',
									user_level=\''.$this->user_data['user_level'].'\',
									user_verifycode='.F_empty_to_null($this->user_data['user_verifycode']).',
									user_otpkey='.F_empty_to_null($this->user_data['user_otpkey']).'
									WHERE user_id='.$user_id.'';
                                if (!$ru = F_db_query($sqlu, $db)) {
                                    F_display_db_error(false);
                                    return false;
                                }
                            } else {
                                // no user is updated, so empty groups
                                $this->group_data = array();
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
								user_verifycode,
								user_otpkey
								) VALUES (
								'.F_empty_to_null($this->user_data['user_regdate']).',
								\''.$this->user_data['user_ip'].'\',
								\''.$this->user_data['user_name'].'\',
								'.F_empty_to_null($this->user_data['user_email']).',
								\''.F_escape_sql($db, getPasswordHash($this->user_data['user_password'])).'\',
								'.F_empty_to_null($this->user_data['user_regnumber']).',
								'.F_empty_to_null($this->user_data['user_firstname']).',
								'.F_empty_to_null($this->user_data['user_lastname']).',
								'.F_empty_to_null($this->user_data['user_birthdate']).',
								'.F_empty_to_null($this->user_data['user_birthplace']).',
								'.F_empty_to_null($this->user_data['user_ssn']).',
								\''.$this->user_data['user_level'].'\',
								'.F_empty_to_null($this->user_data['user_verifycode']).',
								'.F_empty_to_null($this->user_data['user_otpkey']).'
								)';
                            if (!$ru = F_db_query($sqlu, $db)) {
                                F_display_db_error(false);
                                return false;
                            } else {
                                $user_id = F_db_insert_id($db, K_TABLE_USERS, 'user_id');
                            }
                        }
                    } else {
                        F_display_db_error(false);
                        return false;
                    }

                    // user's groups
                    if (!empty($this->group_data)) {
                        foreach ($this->group_data as $key => $group_id) {
                            // check if user-group already exist
                            $sqls = 'SELECT *
								FROM '.K_TABLE_USERGROUP.'
								WHERE usrgrp_group_id=\''.$group_id.'\'
									AND usrgrp_user_id=\''.$user_id.'\'
								LIMIT 1';
                            if ($rs = F_db_query($sqls, $db)) {
                                if (!$ms = F_db_fetch_array($rs)) {
                                    // associate group to user
                                    $sqlg = 'INSERT INTO '.K_TABLE_USERGROUP.' (
										usrgrp_user_id,
										usrgrp_group_id
										) VALUES (
										'.$user_id.',
										'.$group_id.'
										)';
                                    if (!$rg = F_db_query($sqlg, $db)) {
                                        F_display_db_error(false);
                                        return false;
                                    }
                                }
                            } else {
                                F_display_db_error(false);
                                return false;
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
     * @param $parser (resource) The first parameter, parser, is a reference to the XML parser calling the handler.
     * @param $data (string) The second parameter, data, contains the character data as a string.
     * @private
     */
    private function segContentHandler($parser, $data)
    {
        if (strlen($this->current_element) > 0) {
            // we are inside an element
            $this->current_data .= $data;
        }
    }
} // END OF CLASS

/**
 * Import users from TSV file (tab delimited text).
 * The format of TSV is the same obtained by exporting data from Users Selection Form.
 * @param $tsvfile (string) TSV (tab delimited text) file name
 * @return boolean TRUE in case of success, FALSE otherwise
 */
function F_import_tsv_users($tsvfile)
{
    global $l, $db;
    require_once('../config/tce_config.php');

    // get file content as array
    $tsvrows = file($tsvfile); // array of TSV lines
    if ($tsvrows === false) {
        return false;
    }

    $nrows = count($tsvrows);
    for ($i = 1; $i < $nrows; $i++) {
        $rowdata = $tsvrows[$i];

        // get user data into array
        $userdata = explode("\t", $rowdata);

        // set some default values
        if (empty($userdata[4])) {
            $userdata[4] = date(K_TIMESTAMP_FORMAT);
        }
        if (empty($userdata[5])) {
            $userdata[5] = getNormalizedIP($_SERVER['REMOTE_ADDR']);
        }
        // user level
        if (!isset($userdata[12]) or (strlen($userdata[12]) == 0)) {
            $userdata[12] = 1;
        }
        if ($_SESSION['session_user_level'] < K_AUTH_ADMINISTRATOR) {
            // you cannot edit a user with a level equal or higher than yours
            $userdata[12] = min(max(0, ($_SESSION['session_user_level'] - 1)), $userdata[12]);
            // non-administrator can access only to his/her groups
            if (empty($userdata[15])) {
                break;
            }
            $usrgroups = explode(',', addslashes($userdata[15]));
            $common_groups = array_intersect(F_get_user_groups($_SESSION['session_user_id']), $usrgroups);
            if (empty($common_groups)) {
                break;
            }
        }
        // check if user already exist
        $sql = 'SELECT user_id,user_level
			FROM '.K_TABLE_USERS.'
			WHERE user_name=\''.F_escape_sql($db, $userdata[1]).'\'
				OR user_regnumber='.F_empty_to_null($userdata[10]).'
				OR user_ssn='.F_empty_to_null($userdata[11]).'
			LIMIT 1';
        if ($r = F_db_query($sql, $db)) {
            if ($m = F_db_fetch_array($r)) {
                // the user has been already added
                $user_id = $m['user_id'];
                if (($_SESSION['session_user_level'] >= K_AUTH_ADMINISTRATOR) or ($_SESSION['session_user_level'] > $m['user_level'])) {
                    //update user data
                    $sqlu = 'UPDATE '.K_TABLE_USERS.' SET
						user_name=\''.F_escape_sql($db, $userdata[1]).'\',';
                    // update password only if it is specified
                    if (!empty($userdata[2])) {
                        $sqlu .= ' user_password=\''.F_escape_sql($db, getPasswordHash($userdata[2])).'\',';
                    }
                    $sqlu .= '
						user_email='.F_empty_to_null($userdata[3]).',
						user_regdate=\''.F_escape_sql($db, $userdata[4]).'\',
						user_ip=\''.F_escape_sql($db, $userdata[5]).'\',
						user_firstname='.F_empty_to_null($userdata[6]).',
						user_lastname='.F_empty_to_null($userdata[7]).',
						user_birthdate='.F_empty_to_null($userdata[8]).',
						user_birthplace='.F_empty_to_null($userdata[9]).',
						user_regnumber='.F_empty_to_null($userdata[10]).',
						user_ssn='.F_empty_to_null($userdata[11]).',
						user_level=\''.intval($userdata[12]).'\',
						user_verifycode='.F_empty_to_null($userdata[13]).',
						user_otpkey='.F_empty_to_null($userdata[14]).'
						WHERE user_id='.$user_id.'';
                    if (!$ru = F_db_query($sqlu, $db)) {
                        F_display_db_error(false);
                        return false;
                    }
                } else {
                    // no user is updated, so empty groups
                    $userdata[15] = '';
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
					user_verifycode,
					user_otpkey
					) VALUES (
					\''.F_escape_sql($db, $userdata[1]).'\',
					\''.F_escape_sql($db, getPasswordHash($userdata[2])).'\',
					'.F_empty_to_null($userdata[3]).',
					\''.F_escape_sql($db, $userdata[4]).'\',
					\''.F_escape_sql($db, $userdata[5]).'\',
					'.F_empty_to_null($userdata[6]).',
					'.F_empty_to_null($userdata[7]).',
					'.F_empty_to_null($userdata[8]).',
					'.F_empty_to_null($userdata[9]).',
					'.F_empty_to_null($userdata[10]).',
					'.F_empty_to_null($userdata[11]).',
					\''.intval($userdata[12]).'\',
					'.F_empty_to_null($userdata[13]).',
					'.F_empty_to_null($userdata[14]).'
					)';
                if (!$ru = F_db_query($sqlu, $db)) {
                    F_display_db_error(false);
                    return false;
                } else {
                    $user_id = F_db_insert_id($db, K_TABLE_USERS, 'user_id');
                }
            }
        } else {
            F_display_db_error(false);
            return false;
        }

        // user's groups
        if (!empty($userdata[15])) {
            $groups = preg_replace("/[\r\n]+/", '', $userdata[15]);
            $groups = explode(',', addslashes($groups));
            foreach ($groups as $key => $group_name) {
                $group_name = F_escape_sql($db, $group_name);
                // check if group already exist
                $sql = 'SELECT group_id
					FROM '.K_TABLE_GROUPS.'
					WHERE group_name=\''.$group_name.'\'
					LIMIT 1';
                if ($r = F_db_query($sql, $db)) {
                    if ($m = F_db_fetch_array($r)) {
                        // the group already exist
                        $group_id = $m['group_id'];
                    } else {
                        // create a new group
                        $sqli = 'INSERT INTO '.K_TABLE_GROUPS.' (
							group_name
							) VALUES (
							\''.$group_name.'\'
							)';
                        if (!$ri = F_db_query($sqli, $db)) {
                            F_display_db_error(false);
                            return false;
                        } else {
                            $group_id = F_db_insert_id($db, K_TABLE_GROUPS, 'group_id');
                        }
                    }
                } else {
                    F_display_db_error(false);
                    return false;
                }
                // check if user-group already exist
                $sqls = 'SELECT *
					FROM '.K_TABLE_USERGROUP.'
					WHERE usrgrp_group_id=\''.$group_id.'\'
						AND usrgrp_user_id=\''.$user_id.'\'
					LIMIT 1';
                if ($rs = F_db_query($sqls, $db)) {
                    if (!$ms = F_db_fetch_array($rs)) {
                        // associate group to user
                        $sqlg = 'INSERT INTO '.K_TABLE_USERGROUP.' (
							usrgrp_user_id,
							usrgrp_group_id
							) VALUES (
							'.$user_id.',
							'.$group_id.'
							)';
                        if (!$rg = F_db_query($sqlg, $db)) {
                            F_display_db_error(false);
                            return false;
                        }
                    }
                } else {
                    F_display_db_error(false);
                    return false;
                }
            }
        }
    }

    return true;
}

//============================================================+
// END OF FILE
//============================================================+
