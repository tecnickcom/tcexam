<?php
//============================================================+
// File name   : tce_class_import_xml.php
// Begin       : 2006-03-12
// Last Update : 2012-11-14
//
// Description : Class to import questions from an XML file.
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
 * Class to import questions from an XML file.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2006-03-12
 */

/**
 * @class XMLQuestionImporter
 * This PHP Class imports question data directly from an XML file.
 * @package com.tecnick.tcexam.admin
 * @version 1.1.000
 */
class XMLQuestionImporter
{

    /**
     * XML file.
     * @private
     */
    private $xmlfile = 0;

    /**
     * Current level: 'module', 'subject', 'question', 'answer'.
     * @private
     */
    private $level = '';

    /**
     * Array to store current level data.
     * @private
     */
    private $level_data = array();

    /**
     * Current data element.
     * @private
     */
    private $current_element = '';

    /**
     * Current data value.
     * @private
     */
    private $current_data = '';

    /**
     * Boolean values.
     * @private
     */
    private $boolval = array('false' => '0', 'true' => '1');

    /**
     * Type of questions.
     * @private
     */
    private $qtype = array('single' => '1', 'multiple' => '2', 'text' => '3', 'ordering' => '4');

    /**
     * Store hash values of question descriptions.
     * This is used to avoid the 255 chars limitation for string indexes on MySQL
     * @private
     */
    private $questionhash = array();

    /**
     * Class constructor.
     * @param $xmlfile (string) xml (XML) file name
     * @return true or die for parsing error
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
        return true;
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
            case 'module':
            case 'subject':
            case 'question':
            case 'answer': {
                $this->level = $name;
                $this->level_data[$name] = array();
                $this->current_data = '';
                switch ($name) {
                    case 'module': {
                        $this->level_data['module']['module_name'] = 'default';
                        $this->level_data['module']['module_enabled'] = 'false';
                        $this->level_data['module']['module_user_id'] = '1';
                        break;
                    }
                    case 'subject': {
                        $this->addModule();
                        $this->level_data['subject']['subject_name'] = 'default';
                        $this->level_data['subject']['subject_description'] = 'default';
                        $this->level_data['subject']['subject_enabled'] = 'false';
                        $this->level_data['subject']['subject_user_id'] = '1';
                        $this->level_data['subject']['subject_module_id'] = '1';
                        break;
                    }
                    case 'question': {
                        $this->addSubject();
                        $this->level_data['question']['question_subject_id'] = '1';
                        $this->level_data['question']['question_description'] = 'default';
                        $this->level_data['question']['question_explanation'] = '';
                        $this->level_data['question']['question_type'] = 'single';
                        $this->level_data['question']['question_difficulty'] = '0';
                        $this->level_data['question']['question_enabled'] = 'false';
                        $this->level_data['question']['question_position'] = 0;
                        $this->level_data['question']['question_timer'] = 0;
                        $this->level_data['question']['question_fullscreen'] = 'false';
                        $this->level_data['question']['question_inline_answers'] = 'false';
                        $this->level_data['question']['question_auto_next'] = 'false';
                        break;
                    }
                    case 'answer': {
                        $this->addQuestion();
                        $this->level_data['answer']['answer_question_id'] = '1';
                        $this->level_data['answer']['answer_description'] = 'default';
                        $this->level_data['answer']['answer_explanation'] = '';
                        $this->level_data['answer']['answer_isright'] = 'false';
                        $this->level_data['answer']['answer_enabled'] = 'false';
                        $this->level_data['answer']['answer_position'] = '0';
                        $this->level_data['answer']['answer_keyboard_key'] = '';
                        break;
                    }
                }
                break;
            }
            default: {
                $this->current_element = $this->level.'_'.$name;
                $this->current_data = '';
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
        $name = strtolower($name);
        switch ($name) {
            case 'module': {
                $this->addModule();
                $this->level = '';
                break;
            }
            case 'subject': {
                $this->addSubject();
                $this->level = 'module';
                break;
            }
            case 'question': {
                $this->addQuestion();
                $this->level = 'subject';
                break;
            }
            case 'answer': {
                $this->addAnswer();
                $this->level = 'question';
                break;
            }
            default: {
                $elname = $this->level.'_'.$name;
                if ($this->current_element == $elname) {
                    // convert XML special chars
                    $this->level_data[$this->level][$this->current_element] = F_xml_to_text(utrim($this->current_data));
                    if (($this->current_element == 'question_description') or ($this->current_element == 'answer_description')) {
                        // normalize UTF-8 string based on settings
                        $this->level_data[$this->level][$this->current_element] = F_utf8_normalizer($this->level_data[$this->level][$this->current_element], K_UTF8_NORMALIZATION_MODE);
                    }
                    // escape for SQL
                    $this->level_data[$this->level][$this->current_element] = F_escape_sql($db, $this->level_data[$this->level][$this->current_element], false);
                }
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

    /**
     * Add a new module if not exist.
     * @private
     */
    private function addModule()
    {
        global $l, $db;
        require_once('../config/tce_config.php');
        require_once('../../shared/code/tce_functions_auth_sql.php');
        if (isset($this->level_data['module']['module_id']) and ($this->level_data['module']['module_id'] > 0)) {
            return;
        }
        // check if this module already exist
        $sql = 'SELECT module_id
			FROM '.K_TABLE_MODULES.'
			WHERE module_name=\''.$this->level_data['module']['module_name'].'\'
			LIMIT 1';
        if ($r = F_db_query($sql, $db)) {
            if ($m = F_db_fetch_array($r)) {
                // get existing module ID
                if (!F_isAuthorizedUser(K_TABLE_MODULES, 'module_id', $m['module_id'], 'module_user_id')) {
                    // unauthorized user
                    $this->level_data['module']['module_id'] = false;
                } else {
                    $this->level_data['module']['module_id'] = $m['module_id'];
                }
            } else {
                // insert new module
                $sql = 'INSERT INTO '.K_TABLE_MODULES.' (
					module_name,
					module_enabled,
					module_user_id
					) VALUES (
					\''.$this->level_data['module']['module_name'].'\',
					\''.$this->boolval[$this->level_data['module']['module_enabled']].'\',
					\''.$_SESSION['session_user_id'].'\'
					)';
                if (!$r = F_db_query($sql, $db)) {
                    F_display_db_error();
                } else {
                    // get new module ID
                    $this->level_data['module']['module_id'] = F_db_insert_id($db, K_TABLE_MODULES, 'module_id');
                }
            }
        } else {
            F_display_db_error();
        }
    }

    /**
     * Add a new subject if not exist.
     * @private
     */
    private function addSubject()
    {
        global $l, $db;
        require_once('../config/tce_config.php');
        if ($this->level_data['module']['module_id'] === false) {
            return;
        }
        if (isset($this->level_data['subject']['subject_id']) and ($this->level_data['subject']['subject_id'] > 0)) {
            return;
        }
        // check if this subject already exist
        $sql = 'SELECT subject_id
			FROM '.K_TABLE_SUBJECTS.'
			WHERE subject_name=\''.$this->level_data['subject']['subject_name'].'\'
				AND subject_module_id='.$this->level_data['module']['module_id'].'
			LIMIT 1';
        if ($r = F_db_query($sql, $db)) {
            if ($m = F_db_fetch_array($r)) {
                // get existing subject ID
                $this->level_data['subject']['subject_id'] = $m['subject_id'];
            } elseif ($this->level_data['module']['module_id'] !== false) {
                // insert new subject
                $sql = 'INSERT INTO '.K_TABLE_SUBJECTS.' (
					subject_name,
					subject_description,
					subject_enabled,
					subject_user_id,
					subject_module_id
					) VALUES (
					\''.$this->level_data['subject']['subject_name'].'\',
					'.F_empty_to_null($this->level_data['subject']['subject_description']).',
					\''.$this->boolval[$this->level_data['subject']['subject_enabled']].'\',
					\''.$_SESSION['session_user_id'].'\',
					'.$this->level_data['module']['module_id'].'
					)';
                if (!$r = F_db_query($sql, $db)) {
                    F_display_db_error();
                } else {
                    // get new subject ID
                    $this->level_data['subject']['subject_id'] = F_db_insert_id($db, K_TABLE_SUBJECTS, 'subject_id');
                }
            } else {
                $this->level_data['subject']['subject_id'] = false;
            }
        } else {
            F_display_db_error();
        }
    }

    /**
     * Add a new question if not exist.
     * @private
     */
    private function addQuestion()
    {
        global $l, $db;
        require_once('../config/tce_config.php');
        if ($this->level_data['module']['module_id'] === false) {
            return;
        }
        if ($this->level_data['subject']['subject_id'] === false) {
            return;
        }
        if (isset($this->level_data['question']['question_id']) and ($this->level_data['question']['question_id'] > 0)) {
            return;
        }
        // check if this question already exist
        $sql = 'SELECT question_id
			FROM '.K_TABLE_QUESTIONS.'
			WHERE ';
        if (K_DATABASE_TYPE == 'ORACLE') {
            $sql .= 'dbms_lob.instr(question_description,\''.$this->level_data['question']['question_description'].'\',1,1)>0';
        } elseif ((K_DATABASE_TYPE == 'MYSQL') and K_MYSQL_QA_BIN_UNIQUITY) {
            $sql .= 'question_description=\''.$this->level_data['question']['question_description'].'\' COLLATE utf8_bin';
        } else {
            $sql .= 'question_description=\''.$this->level_data['question']['question_description'].'\'';
        }
        $sql .= ' AND question_subject_id='.$this->level_data['subject']['subject_id'].' LIMIT 1';
        if ($r = F_db_query($sql, $db)) {
            if ($m = F_db_fetch_array($r)) {
                // get existing question ID
                $this->level_data['question']['question_id'] = $m['question_id'];
                return;
            }
        } else {
            F_display_db_error();
        }
        if (K_DATABASE_TYPE == 'MYSQL') {
            // this section is to avoid the problems on MySQL string comparison
            $maxkey = 240;
            $strkeylimit = min($maxkey, strlen($this->level_data['question']['question_description']));
            $stop = $maxkey / 3;
            while (in_array(md5(strtolower(substr($this->level_data['subject']['subject_id'].$this->level_data['question']['question_description'], 0, $strkeylimit))), $this->questionhash) and ($stop > 0)) {
                // a similar question was already imported from this XML, so we change it a little bit to avoid duplicate keys
                $this->level_data['question']['question_description'] = '_'.$this->level_data['question']['question_description'];
                $strkeylimit = min($maxkey, ($strkeylimit + 1));
                $stop--; // variable used to avoid infinite loop
            }
            if ($stop == 0) {
                F_print_error('ERROR', 'Unable to get unique question ID');
                return;
            }
        }
        $sql = 'START TRANSACTION';
        if (!$r = F_db_query($sql, $db)) {
            F_display_db_error();
        }
        // insert question
        $sql = 'INSERT INTO '.K_TABLE_QUESTIONS.' (
			question_subject_id,
			question_description,
			question_explanation,
			question_type,
			question_difficulty,
			question_enabled,
			question_position,
			question_timer,
			question_fullscreen,
			question_inline_answers,
			question_auto_next
			) VALUES (
			'.$this->level_data['subject']['subject_id'].',
			\''.$this->level_data['question']['question_description'].'\',
			'.F_empty_to_null($this->level_data['question']['question_explanation']).',
			\''.$this->qtype[$this->level_data['question']['question_type']].'\',
			\''.$this->level_data['question']['question_difficulty'].'\',
			\''.$this->boolval[$this->level_data['question']['question_enabled']].'\',
			'.F_zero_to_null($this->level_data['question']['question_position']).',
			\''.$this->level_data['question']['question_timer'].'\',
			\''.$this->boolval[$this->level_data['question']['question_fullscreen']].'\',
			\''.$this->boolval[$this->level_data['question']['question_inline_answers']].'\',
			\''.$this->boolval[$this->level_data['question']['question_auto_next']].'\'
			)';
        if (!$r = F_db_query($sql, $db)) {
            F_display_db_error(false);
        } else {
            // get new question ID
            $this->level_data['question']['question_id'] = F_db_insert_id($db, K_TABLE_QUESTIONS, 'question_id');
            if (K_DATABASE_TYPE == 'MYSQL') {
                $this->questionhash[] = md5(strtolower(substr($this->level_data['subject']['subject_id'].$this->level_data['question']['question_description'], 0, $strkeylimit)));
            }
        }
        $sql = 'COMMIT';
        if (!$r = F_db_query($sql, $db)) {
            F_display_db_error();
        }
    }

    /**
     * Add a new answer if not exist.
     * @private
     */
    private function addAnswer()
    {
        global $l, $db;
        require_once('../config/tce_config.php');
        if ($this->level_data['module']['module_id'] === false) {
            return;
        }
        if ($this->level_data['subject']['subject_id'] === false) {
            return;
        }
        if (isset($this->level_data['answer']['answer_id']) and ($this->level_data['answer']['answer_id'] > 0)) {
            return;
        }
        // check if this answer already exist
        $sql = 'SELECT answer_id
			FROM '.K_TABLE_ANSWERS.'
			WHERE ';
        if (K_DATABASE_TYPE == 'ORACLE') {
            $sql .= 'dbms_lob.instr(answer_description, \''.$this->level_data['answer']['answer_description'].'\',1,1)>0';
        } elseif ((K_DATABASE_TYPE == 'MYSQL') and K_MYSQL_QA_BIN_UNIQUITY) {
            $sql .= 'answer_description=\''.$this->level_data['answer']['answer_description'].'\' COLLATE utf8_bin';
        } else {
            $sql .= 'answer_description=\''.$this->level_data['answer']['answer_description'].'\'';
        }
        $sql .= ' AND answer_question_id='.$this->level_data['question']['question_id'].' LIMIT 1';
        if ($r = F_db_query($sql, $db)) {
            if ($m = F_db_fetch_array($r)) {
                // get existing subject ID
                $this->level_data['answer']['answer_id'] = $m['answer_id'];
            } else {
                $sql = 'START TRANSACTION';
                if (!$r = F_db_query($sql, $db)) {
                    F_display_db_error();
                }
                $sql = 'INSERT INTO '.K_TABLE_ANSWERS.' (
					answer_question_id,
					answer_description,
					answer_explanation,
					answer_isright,
					answer_enabled,
					answer_position,
					answer_keyboard_key
					) VALUES (
					'.$this->level_data['question']['question_id'].',
					\''.$this->level_data['answer']['answer_description'].'\',
					'.F_empty_to_null($this->level_data['answer']['answer_explanation']).',
					\''.$this->boolval[$this->level_data['answer']['answer_isright']].'\',
					\''.$this->boolval[$this->level_data['answer']['answer_enabled']].'\',
					'.F_zero_to_null($this->level_data['answer']['answer_position']).',
					'.F_empty_to_null($this->level_data['answer']['answer_keyboard_key']).'
					)';
                if (!$r = F_db_query($sql, $db)) {
                    F_display_db_error(false);
                    F_db_query('ROLLBACK', $db);
                } else {
                    // get new answer ID
                    $this->level_data['answer']['answer_id'] = F_db_insert_id($db, K_TABLE_ANSWERS, 'answer_id');
                }
                $sql = 'COMMIT';
                if (!$r = F_db_query($sql, $db)) {
                    F_display_db_error();
                }
            }
        } else {
            F_display_db_error();
        }
    }
} // END OF CLASS

//============================================================+
// END OF FILE
//============================================================+
