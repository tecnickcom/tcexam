<?php
//============================================================+
// File name   : tce_tmx.php
// Begin       : 2004-10-19
// Last Update : 2010-08-09
//
// Description : TMX-PHP Bridge Class
// Platform    : PHP 5
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
//
// 	This program is free software: you can redistribute it and/or modify
// 	it under the terms of the GNU Lesser General Public License as published by
// 	the Free Software Foundation, either version 2.1 of the License, or
// 	(at your option) any later version.
//
// 	This program is distributed in the hope that it will be useful,
// 	but WITHOUT ANY WARRANTY; without even the implied warranty of
// 	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// 	GNU Lesser General Public License for more details.
//
// 	You should have received a copy of the GNU Lesser General Public License
// 	along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * TMX-PHP Bridge Class (TMXResourceBundle).
 * @package com.tecnick.tmxphpbridge
 */

/**
 * @class TMXResourceBundle
 * This PHP Class reads resource text data directly from a TMX (XML) file.
 * First, the XMLTMXResourceBundle class instantiates itself with two parameters:
 * a TMX file name and a target language name. Then, using an XML parser, it
 * reads all of a translation unit's properties for the key information and
 * specified language data and populates the resource array with them (key -> value).
 *
 * @package com.tecnick.tmxphpbridge
 * @brief TMX-PHP Bridge Class
 * @author Nicola Asuni [www.tecnick.com]
 * @version 1.1.005
 */
class TMXResourceBundle
{

    /**
     * Array used to contain key-translation couples.
     * @private
     */
    private $resource = array();

    /**
     * String Current tu -> tuid value.
     * @private
     */
    private $current_key = '';

    /**
     * String Current data value.
     * @private
     */
    private $current_data = '';

    /**
     * String Current tuv -> xml:lang value.
     * @private
     */
    private $current_language = '';

    /**
     * Boolean value true when we are inside a seg element
     * @private
     */
    private $segdata = false;

    /**
     * String ISO language identifier (a two- or three-letter code)
     * @private
     */
    private $language = '';

    /**
     * String filename for cache
     * @private
     */
    private $cachefile = '';

    /**
     * Class constructor.
     * @param $tmxfile (string) TMX (XML) file name
     * @param $language (string) ISO language identifier (a two- or three-letter code)
     * @param $cachefile (string) set filename for cache (leave blank to exclude cache)
     */
    public function __construct($tmxfile, $language, $cachefile = '')
    {
        // reset array
        $this->resource = array();
        // set selecteed language
        $this->language = strtoupper($language);
        // set filename for cache
        $this->cachefile = $cachefile;

        if (file_exists($this->cachefile)) {
            // read data from cache
            require_once($this->cachefile);
            $this->resource = $tmx;
        } else {
            if (!empty($this->cachefile)) {
                // open cache file
                file_put_contents($this->cachefile, '<'.'?php'."\n".
                '// CACHE FILE FOR LANGUAGE: '.substr($language, 0, 2)."\n".
                '// DATE: '.date('Y-m-d H:i:s')."\n".
                '// *** DELETE THIS FILE TO RELOAD DATA FROM TMX FILE ***'."\n", FILE_APPEND | LOCK_EX);
            }

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
            if (!xml_parse($this->parser, file_get_contents($tmxfile))) {
                die(sprintf(
                    'ERROR TMXResourceBundle :: XML error: %s at line %d',
                    xml_error_string(xml_get_error_code($this->parser)),
                    xml_get_current_line_number($this->parser)
                ));
            }
            // free this XML parser
            xml_parser_free($this->parser);
            if (!empty($this->cachefile)) {
                // close cache file
                file_put_contents($this->cachefile, "\n\n".'// --- EOF ---', FILE_APPEND);
            }
        }
    }

    /**
     * Class destructor; resets $resource array.
     */
    public function __destruct()
    {
        $resource = array(); // reset resource array
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
        switch (strtolower($name)) {
            case 'tu': {
                // translation unit element, unit father of every element to be translated. It can contain a unique identifier (tuid).
                if (array_key_exists('tuid', $attribs)) {
                    $this->current_key = $attribs['tuid'];
                }
                break;
            }
            case 'tuv': {
                // translation unit variant, unit that contains the language code of the translation (xml:lang)
                if (array_key_exists('xml:lang', $attribs)) {
                    $this->current_language = strtoupper($attribs['xml:lang']);
                }
                break;
            }
            case 'seg': {
                // segment, it contains the translated text
                $this->segdata = true;
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
        switch (strtolower($name)) {
            case 'tu': {
                // translation unit element, unit father of every element to be translated. It can contain a unique identifier (tuid).
                $this->current_key = '';
                break;
            }
            case 'tuv': {
                // translation unit variant, unit that contains the language code of the translation (xml:lang)
                $this->current_language = '';
                break;
            }
            case 'seg': {
                // segment, it contains the translated text
                $this->segdata = false;
                if (!empty($this->current_data) or !array_key_exists($this->current_key, $this->resource)) {
                    $this->resource[$this->current_key] = $this->current_data; // set new array element
                    if (!empty($this->cachefile) and ($this->current_language == $this->language)) {
                        // write element to cache file
                        file_put_contents($this->cachefile, "\n".'$'.'tmx[\''.$this->current_key.'\']=\''.str_replace('\'', '\\\'', $this->current_data).'\';', FILE_APPEND);
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
        if ($this->segdata and (strlen($this->current_key)>0) and (strlen($this->current_language)>0)) {
            // we are inside a seg element
            if (strcasecmp($this->current_language, $this->language) == 0) {
                // we have reached the requested language translation
                $this->current_data .= $data;
            }
        }
    }

    /**
     * Returns the resource array containing the translated word/sentences.
     * @return Array.
     */
    public function getResource()
    {
        return $this->resource;
    }
} // END OF CLASS

//============================================================+
// END OF FILE
//============================================================+
