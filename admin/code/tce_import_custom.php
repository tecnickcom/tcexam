<?php
//============================================================+
// File name   : tce_import_custom.php
// Begin       : 2008-12-01
// Last Update : 2009-09-30
// 
// Description : Class to import questions from a custom-format file.
//               FORMAT: ISA
//
// Note: To avoid timeots, please setup a very high limit 
//       for memory (512MB) and execution time (300) on php.ini
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
 * Class to import questions from a custom file.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2000-12-01
 */

/**
 * This PHP Class imports question data directly from a custom file.
 * @package com.tecnick.tcexam.admin
 * @name CustomQuestionImporter
 * @abstract question importer from a custom file format
 * @license http://www.gnu.org/copyleft/lesser.html GPL
 * @author Nicola Asuni [www.tecnick.com]
 * @copyright Copyright (c) 2004-2009 - Nicola Asuni - Tecnick.com S.r.l (www.tecnick.com) - Via della Pace n.11 - 09044 Quartucciu (CA) - ITALY - www.tecnick.com - info@tecnick.com
 * @version 1.0.000
 */
class CustomQuestionImporter {
	
	/**
	 * @var XML file
	 * @access private
	 */
	private $xmlfile = 0;
	
	/**
	 * @var Array to store current level data.
	 * @access private
	 */
	private $level = Array();
	
	/**
	 * @var Current data value.
	 * @access private
	 */
	private $current_data = '';
	
	/**
	 * @var Current data element.
	 * @access private
	 */
	private $current_element = '';
	
	/**
	 * @var Array to map XML indexes with database indexes.
	 * @access private
	 */
	private $ids = Array();
	
	/**
	 * @var current index for XML indexes.
	 * @access private
	 */
	private $xid = 0;
	
	/**
	 * @var store hash values of question descriptions.
	 * This is used to avoid the 255 chars limitation for string indexes on MySQL
	 * @access private
	 */
	private $questionhash = array();
	
	/**
	 * Class constructor.
	 * @param string $xmlfile xml (XML) file name
	 * @param string $subject_id subject ID
	 * @return true or die for parsing error
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
		$xmldata = file_get_contents($xmlfile);
		$xmldata = $this->html2TCECode($xmldata);
		// start parsing an XML document
		if(!xml_parse($this->parser, $xmldata)) {
			die(sprintf('ERROR xmlResourceBundle :: XML error: %s at line %d',
				xml_error_string(xml_get_error_code($this->parser)),
				xml_get_current_line_number($this->parser)));
		}
		// free this XML parser
		xml_parser_free($this->parser);
		return true;
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
		global $l, $db;
		require_once('../config/tce_config.php');
		$name = strtolower($name);
		switch($name) {
			case 'modulo': {
				$this->level['module'] = Array();
				$this->current_data = '';
				if (array_key_exists('numero', $attribs)) {
					$this->xid = intval($attribs['numero']);
				} else {
					$this->xid = 1;
				}
				if (array_key_exists('titolo', $attribs)) {
					$this->level['module']['module_name'] = F_escape_sql(utrim(F_xml_to_text($attribs['titolo'])), false);
				} else {
					$this->level['module']['module_name'] = 'default';
				}
				$this->addModule();
				$this->ids["'".$this->xid."'"] = array();
				break;
			}
			case 'blocco': {
				$this->level['subject'] = Array();
				$this->current_data = '';
				if (array_key_exists('numero', $attribs)) {
					$this->level['subject']['numero'] = intval($attribs['numero']);
				} else {
					$this->level['subject']['numero'] = 1;
				}
				$this->level['subject']['subject_name'] = 'default';
				break;
			}
			case 'chiusa': {
				$this->level['question'] = Array();
				$this->current_data = '';
				if (array_key_exists('modulo', $attribs) AND array_key_exists('blocco', $attribs)) {
					$this->level['question']['question_subject_id'] = $this->ids['\''.intval($attribs['modulo']).'\'']['\''.intval($attribs['blocco']).'\''];
				} else {
					$this->level['question']['question_subject_id'] = 1;
				}
				if (array_key_exists('difficile', $attribs)) {
					$this->level['question']['question_difficulty'] = intval($attribs['difficile']);
				} else {
					$this->level['question']['question_difficulty'] = 1;
				}
				$this->level['question']['question_description'] = 'default';
				$this->level['question']['question_explanation'] = '';
				break;
			}
			case 'giusta': {
				$this->level['answer'] = Array();
				$this->current_data = '';
				$this->level['answer']['answer_description'] = 'default';
				$this->level['answer']['answer_explanation'] = '';
				$this->level['answer']['answer_isright'] = '1';
				break;
			}
			case 'sbagliata': {
				$this->level['answer'] = Array();
				$this->current_data = '';
				$this->level['answer']['answer_description'] = 'default';
				$this->level['answer']['answer_explanation'] = '';
				$this->level['answer']['answer_isright'] = '0';
				break;
			}
			default: {
				$this->current_element = $name;
				$this->current_data = '';
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
		$name = strtolower($name);
		switch($name) {
			case 'blocco': {
				$this->level['subject']['subject_name'] = F_escape_sql(F_xml_to_text(utrim($this->current_data)), false);
				$this->addSubject();
				$this->ids['\''.$this->xid.'\'']['\''.$this->level['subject']['numero'].'\''] = $this->level['subject']['subject_id'];
				break;
			}
			case 'domanda': {
				$this->level['question']['question_description'] = F_escape_sql(F_xml_to_text(utrim($this->current_data)), false);
				$this->addQuestion();
				break;
			}
			case 'risposta': {
				$this->level['answer']['answer_description'] = F_escape_sql(F_xml_to_text(utrim($this->current_data)), false);
				break;
			}
			case 'spiegazione': {
				$this->level['answer']['answer_explanation'] = F_escape_sql(F_xml_to_text(utrim($this->current_data)), false);
				break;
			}
			case 'giusta':
			case 'sbagliata': {
				$this->addAnswer();
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
	
	/**
	 * Add a new module if not exist.
	 * @access private
	 */
	private function addModule() {
		global $l, $db;
		require_once('../config/tce_config.php');
		if (isset($this->level['module']['module_id']) AND ($this->level['module']['module_id'] > 0)) {
			return;
		}
		// check if this module already exist
		$sql = 'SELECT module_id 
			FROM '.K_TABLE_MODULES.'
			WHERE module_name=\''.$this->level['module']['module_name'].'\'
			LIMIT 1';
		if($r = F_db_query($sql, $db)) {
			if($m = F_db_fetch_array($r)) {
				// get existing module ID
				$this->level['module']['module_id'] = $m['module_id'];
			} else {
				// insert new module
				$sql = 'INSERT INTO '.K_TABLE_MODULES.' (
					module_name,
					module_enabled
					) VALUES (
					\''.$this->level['module']['module_name'].'\',
					\'1\'
					)';
				if(!$r = F_db_query($sql, $db)) {
					F_display_db_error();
				} else {
					// get new module ID
					$this->level['module']['module_id'] = F_db_insert_id($db, K_TABLE_MODULES, 'module_id');
				}
			}
		} else {
			F_display_db_error();
		}
	}
	
	/**
	 * Add a new subject if not exist.
	 * @access private
	 */
	private function addSubject() {
		global $l, $db;
		require_once('../config/tce_config.php');
		if (isset($this->level['subject']['subject_id']) AND ($this->level['subject']['subject_id'] > 0)) {
			return;
		}
		// check if this subject already exist
		$sql = 'SELECT subject_id 
			FROM '.K_TABLE_SUBJECTS.'
			WHERE subject_name=\''.$this->level['subject']['subject_name'].'\'
				AND subject_module_id='.$this->level['module']['module_id'].'
			LIMIT 1';
		if($r = F_db_query($sql, $db)) {
			if($m = F_db_fetch_array($r)) {
				// get existing subject ID
				$this->level['subject']['subject_id'] = $m['subject_id'];
			} else {
				// insert new subject
				$sql = 'INSERT INTO '.K_TABLE_SUBJECTS.' (
					subject_name, 
					subject_description, 
					subject_enabled,
					subject_user_id,
					subject_module_id
					) VALUES (
					\''.$this->level['subject']['subject_name'].'\',
					'.F_empty_to_null($this->level['subject']['subject_name']).', 
					\'1\',
					\''.$_SESSION['session_user_id'].'\',
					'.$this->level['module']['module_id'].'
					)';
				if(!$r = F_db_query($sql, $db)) {
					F_display_db_error();
				} else {
					// get new subject ID
					$this->level['subject']['subject_id'] = F_db_insert_id($db, K_TABLE_SUBJECTS, 'subject_id');
				}
			}
		} else {
			F_display_db_error();
		}
	}
	
	/**
	 * Add a new question if not exist.
	 * @access private
	 */
	private function addQuestion() {
		global $l, $db;
		require_once('../config/tce_config.php');
		if (isset($this->level['question']['question_id']) AND ($this->level['question']['question_id'] > 0)) {
			return;
		}
		// check if this question already exist
		$sql = 'SELECT question_id 
		FROM '.K_TABLE_QUESTIONS.'
		WHERE question_description=\''.$this->level['question']['question_description'].'\'
			AND question_subject_id='.$this->level['question']['question_subject_id'].'
		LIMIT 1';
		if($r = F_db_query($sql, $db)) {
			if($m = F_db_fetch_array($r)) {
				// get existing question ID
				$this->level['question']['question_id'] = $m['question_id'];
				return;
			}
		} else {
			F_display_db_error();
		}
		if (K_DATABASE_TYPE == 'MYSQL') {
			$maxkey = 240;
			$strkeylimit = min($maxkey, strlen($this->level['question']['question_description']));
			$stop = $maxkey / 3;
			while (in_array(md5(strtolower(substr($this->level['question']['question_subject_id'].$this->level['question']['question_description'], 0, $strkeylimit))), $this->questionhash) AND ($stop > 0)) {
				// a similar question was already imported from this XML, so we change it a little bit to avoid duplicate keys
				$this->level['question']['question_description'] = '_'.$this->level['question']['question_description'];
				$strkeylimit = min($maxkey, ($strkeylimit + 1));
				$stop--; // variable used to avoid infinite loop
			}
			if ($stop == 0) {
				F_print_error('ERROR', 'Unable to get unique question ID');
				return;
			}
		}
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
			'.$this->level['question']['question_subject_id'].',
			\''.$this->level['question']['question_description'].'\',
			'.F_empty_to_null($this->level['question']['question_explanation']).',
			\'1\',
			\''.$this->level['question']['question_difficulty'].'\',
			\'1\',
			'.F_zero_to_null(0).',
			\'0\',
			\'0\',
			\'0\',
			\'0\'
			)';
		if(!$r = F_db_query($sql, $db)) {
			F_display_db_error();
		} else {
			// get new question ID
			$this->level['question']['question_id'] = F_db_insert_id($db, K_TABLE_QUESTIONS, 'question_id');
			if (K_DATABASE_TYPE == 'MYSQL') {
				$this->questionhash[] = md5(strtolower(substr($this->level['question']['question_subject_id'].$this->level['question']['question_description'], 0, $strkeylimit)));
			}
		}
	}
	
	/**
	 * Add a new answer if not exist.
	 * @access private
	 */
	private function addAnswer() {
		global $l, $db;
		require_once('../config/tce_config.php');
		if (isset($this->level['answer']['answer_id']) AND ($this->level['answer']['answer_id'] > 0)) {
			return;
		}
		// check if this answer already exist
		$sql = 'SELECT answer_id 
			FROM '.K_TABLE_ANSWERS.'
			WHERE answer_description=\''.$this->level['answer']['answer_description'].'\'
				AND answer_question_id='.$this->level['question']['question_id'].'
			LIMIT 1';
		if($r = F_db_query($sql, $db)) {
			if($m = F_db_fetch_array($r)) {
				// get existing subject ID
				$this->level['answer']['answer_id'] = $m['answer_id'];
			} else {
				$sql = 'INSERT INTO '.K_TABLE_ANSWERS.' (
					answer_question_id,
					answer_description,
					answer_explanation,
					answer_isright,
					answer_enabled,
					answer_position,
					answer_keyboard_key
					) VALUES (
					'.$this->level['question']['question_id'].',
					\''.$this->level['answer']['answer_description'].'\',
					'.F_empty_to_null($this->level['answer']['answer_explanation']).',
					\''.$this->level['answer']['answer_isright'].'\',
					\'1\',
					'.F_zero_to_null(0).',
					'.F_empty_to_null('').'
					)';
				if(!$r = F_db_query($sql, $db)) {
					F_display_db_error();
				} else {
					// get new answer ID
					$this->level['answer']['answer_id'] = F_db_insert_id($db, K_TABLE_ANSWERS, 'answer_id');
				}
			}
		} else {
			F_display_db_error();
		}
	}
	
	/**
	 * Convert HTML string to TCECode (BBCode)
	 * @param string $html string to convert
	 * @return string converted
	 * @access private
	 */
	private function html2TCECode($html) {
		// convert html examples
		$newtext = $html;
		// convert html to TCECode
		$newtext = str_replace('<spiegazione/>', '<spiegazione> </spiegazione>', $newtext);
		$newtext = str_replace('<ul>', '[ulist]', $newtext);
		$newtext = str_replace('</ul>', '[/ulist]', $newtext);
		$newtext = str_replace('<ol>', '[olist]', $newtext);
		$newtext = str_replace('</ol>', '[/olist]', $newtext);
		$newtext = str_replace('<li>', '[li]', $newtext);
		$newtext = str_replace('</li>', '[/li]', $newtext);
		$newtext = str_replace('<sup>', '[sup]', $newtext);
		$newtext = str_replace('</sup>', '[/sup]', $newtext);
		$newtext = str_replace('<sub>', '[sub]', $newtext);
		$newtext = str_replace('</sub>', '[/sub]', $newtext);
		$newtext = str_replace('<i>', '[i]', $newtext);
		$newtext = str_replace('</i>', '[/i]', $newtext);
		$newtext = str_replace('<b>', '[b]', $newtext);
		$newtext = str_replace('</b>', '[/b]', $newtext);
		$newtext = str_replace('<strong>', '[b]', $newtext);
		$newtext = str_replace('</strong>', '[/b]', $newtext);
		$newtext = str_replace('<small>', '[small]', $newtext);
		$newtext = str_replace('</small>', '[/small]', $newtext);	
		$newtext = str_replace('<pre>', '[code]', $newtext);
		$newtext = str_replace('</pre>', '[/code]', $newtext);
		$newtext = str_replace('<br/>', chr(10), $newtext);
		$newtext = str_replace('<p>', chr(10), $newtext);
		$newtext = str_replace('</p>', chr(10), $newtext);
		$newtext = preg_replace('/<a[\s]*href=\"([^\"]*)\">(.*?)<\/a>/si', '[url="\1"]\2[/url]', $newtext);
		// remove other tags
		$newtext = strip_tags($newtext, '<database_domande><indice><modulo><blocco><domande><chiusa><domanda><giusta><sbagliata><risposta><spiegazione>');
		//replace some special characters <>
		$newtext = preg_replace_callback('/(<domanda>)(.*?)(<\/domanda>)/si', create_function('$matches', '$str=str_replace("<","&lt;",$matches[2]);$str=str_replace(">","&gt;",$str);return "<domanda>".$str."</domanda>";'), $newtext);
		$newtext = preg_replace_callback('/(<risposta>)(.*?)(<\/risposta>)/si', create_function('$matches', '$str=str_replace("<","&lt;",$matches[2]);$str=str_replace(">","&gt;",$str);return "<risposta>".$str."</risposta>";'), $newtext);
		$newtext = preg_replace_callback('/(<spiegazione>)(.*?)(<\/spiegazione>)/si', create_function('$matches', '$str=str_replace("<","&lt;",$matches[2]);$str=str_replace(">","&gt;",$str);return "<spiegazione>".$str."</spiegazione>";'), $newtext);
		// encode string and return
		return ($newtext);
	}
	
	/**
	 * Strip whitespace (or other characters) from the beginning and end of a string (works with UTF-8 strings)
	 * @param string $txt The string that will be trimmed. 
	 * @return string The trimmed string. 
	 * @access private
	 */
	private function utrim($txt) {
		$txt = preg_replace('/\xA0/u', ' ', $txt);
		$txt = preg_replace('/^([\s]+)/u', '', $txt);
		$txt = preg_replace('/([\s]+)$/u', '', $txt);
		return $txt;
	}
	
} // END OF CLASS

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
