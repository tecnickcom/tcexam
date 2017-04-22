<?php
//============================================================+
// File name   : tce_functions_form.php
// Begin       : 2001-11-07
// Last Update : 2013-04-02
//
// Description : Functions to handle XHTML Form Fields.
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
 * Functions to handle XHTML Form Fields.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @since 2001-11-07
 */

/**
 */

$formstatus = true; //reset form status

// check buttons actions
if (isset($_POST['update'])) {
    $menu_mode = 'update';
} elseif (isset($_POST['delete'])) {
    $menu_mode = 'delete';
} elseif (isset($_POST['forcedelete'])) {
    $menu_mode = 'forcedelete';
} elseif (isset($_POST['cancel'])) {
    $menu_mode = 'cancel';
} elseif (isset($_POST['add'])) {
    $menu_mode = 'add';
} elseif (isset($_POST['clear'])) {
    $menu_mode = 'clear';
} elseif (isset($_POST['upload'])) {
    $menu_mode = 'upload';
} elseif (isset($_POST['addquestion'])) {
    $menu_mode = 'addquestion';
}
if (!isset($menu_mode)) {
    $menu_mode = '';
}

/**
 * Returns an array containing form fields.
 * @return array containing form fields
 */
function F_decode_form_fields()
{
    return $_REQUEST;
}

/**
 * Check Required Form Fields.<br>
 * Returns a string containing a list of missing fields (comma separated).
 * @param $formfields (string) input array containing form fields
 * @return array containing a list of missing fields (if any)
 */
function F_check_required_fields($formfields)
{
    if (empty($formfields) or !array_key_exists('ff_required', $formfields) or strlen($formfields['ff_required']) <= 0) {
        return false;
    }
    $missing_fields = '';
    $required_fields = explode(',', $formfields['ff_required']);
    $required_fields_labels = explode(',', $formfields['ff_required_labels']); // form fields labels
    for ($i=0; $i<count($required_fields); $i++) { //for each required field
        $fieldname = trim($required_fields[$i]);
        $fieldname = preg_replace('/[^a-z0-9_\[\]]/i', '', $fieldname);
        if (!array_key_exists($fieldname, $formfields) or strlen(trim($formfields[$fieldname])) <= 0) { //if is empty
            if ($required_fields_labels[$i]) { // check if the field has a label
                $fieldname = $required_fields_labels[$i];
            }
            $missing_fields .= ', '.stripslashes($fieldname);
        }
    }
    if (strlen($missing_fields)>1) {
        $missing_fields = substr($missing_fields, 1); // cuts first comma
    }
    return ($missing_fields);
}

/**
 * Check fields format using regular expression comparisons.<br>
 * Returns a string containing a list of wrong fields (comma separated).
 *
 * NOTE:
 * to check a field create a new hidden field with the same name starting with 'x_'
 *
 * An example powerful regular expression for email check is:
 *  ^([a-zA-Z0-9_\.\-]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$
 * @param $formfields (string) input array containing form fields
 * @return array containing a list of wrongfields (if any)
 */
function F_check_fields_format($formfields)
{
    if (empty($formfields)) {
        return '';
    }
    reset($formfields);
    $wrongfields = '';
    while (list($key, $value) = each($formfields)) {
        if (substr($key, 0, 2) == 'x_') {
            $fieldname = substr($key, 2);
            $fieldname = preg_replace('/[^a-z0-9_\[\]]/i', '', $fieldname);
            if (array_key_exists($fieldname, $formfields) and strlen($formfields[$fieldname]) > 0) { //if is not empty
                if (!preg_match("'".stripslashes($value)."'i", $formfields[$fieldname])) { //check regular expression
                    if (isset($formfields['xl_'.$fieldname]) and !empty($formfields['xl_'.$fieldname])) { //check if field has label
                        $fieldname = $formfields['xl_'.$fieldname];
                    }
                    $wrongfields .= ', '.stripslashes($fieldname);
                }
            }
        }
    }
    if (strlen($wrongfields) > 1) {
        $wrongfields = substr($wrongfields, 2); // cuts first 2 chars
    }
    return ($wrongfields);
}

/**
 * Check Form Fields.
 * see: F_check_required_fields, F_check_fields_format
 * @return false in case of error, true otherwise
 */
function F_check_form_fields()
{
    require_once('../config/tce_config.php');
    global $l;
    $formfields = F_decode_form_fields(); //decode form fields
    //check missing fields
    if ($missing_fields = F_check_required_fields($formfields)) {
        F_print_error('WARNING', $l['m_form_missing_fields'].': '.$missing_fields);
        F_stripslashes_formfields();
        return false;
    }
    //check fields format
    if ($wrong_fields = F_check_fields_format($formfields)) {
        F_print_error('WARNING', $l['m_form_wrong_fields'].': '.$wrong_fields);
        F_stripslashes_formfields();
        return false;
    }
    return true;
}

/**
 * Strip slashes from posted form fields.
 */
function F_stripslashes_formfields()
{
    foreach ($_POST as $key => $value) {
        if (($key[0] != '_') and (is_string($value))) {
            $key = preg_replace('/[^a-z0-9_\[\]]/i', '', $key);
            global $$key;
            if (!isset($$key)) {
                $$key = stripslashes($value);
            }
        }
    }
}

/**
 * Returns XHTML code string to display a window close button
 * @param $onclick (string) additional javascript code to execute before closing the window.
 * @return XHTML code string
 */
function F_close_button($onclick = '')
{
    require_once('../config/tce_config.php');
    global $l;
    $str = '';
    $str .= '<div class="row">'.K_NEWLINE;
    $str .= '<form action="'.$_SERVER['SCRIPT_NAME'].'" id="closeform">'.K_NEWLINE;
    $str .= '<div>'.K_NEWLINE;
    $str .= '<input type="button" name="wclose" id="wclose" value="'.$l['w_close'].'" title="'.$l['h_close_window'].'" onclick="'.$onclick.'window.close();" />'.K_NEWLINE;
    $str .= '</div>'.K_NEWLINE;
    $str .= '</form>'.K_NEWLINE;
    $str .= '</div>'.K_NEWLINE;
    return $str;
}

/**
 * Returns XHTML code string to display Form Submit Button.
 * @param $name (string) button name
 * @param $value (string) label for button
 * @param $title (string) button title, default=""
 * @return XHTML code string
 */
function F_submit_button($name, $value, $title = "")
{
    echo '<input type="submit" name="'.$name.'" id="'.$name.'" value="'.$value.'" title="'.$title.'" />';
}

/**
 * Print input row form.
 * @param $field_name (string) Name of the form field.
 * @param $name (string) Label.
 * @param $description (string) Label description (tooltip).
 * @param $tip (string) Help to be displayed on the right of the input field.
 * @param $value (string) Initial value.
 * @param $format (string) Regular expression to check the format of the field.
 * @param $maxlen (int) Maximum input lenght.
 * @param $date (boolean) True if the field is a date input.
 * @param $datetime (boolean) True if the field is a date-time input.
 * @param $password (boolean) True if the field is a password.
 * @param $prefix (string) code to be displayed after label.
 * @return string
 */
function getFormRowTextInput($field_name, $name, $description = '', $tip = '', $value = '', $format = '', $maxlen = 255, $date = false, $datetime = false, $password = false, $prefix = '')
{
    require_once(dirname(__FILE__).'/../config/tce_config.php');
    global $l;
    if (strlen($description) == 0) {
        $description = $name;
    }
    $str = ''; // string to return
    $button = '';
    if ($date) {
        $button = '<button name="'.$field_name.'_date_trigger" id="'.$field_name.'_date_trigger" title="'.$l['w_calendar'].'">...</button>';
        $jsdate = 'Calendar.setup({inputField: "'.$field_name.'", ifFormat: "%Y-%m-%d", button: "'.$field_name.'_date_trigger"});'.K_NEWLINE;
        $format = '^([0-9]{4})([\-])([0-9]{2})([\-])([0-9]{2})$';
        $maxlen = 10;
        if (strlen($tip) == 0) {
            $tip = $l['w_date_format'];
        }
    } elseif ($datetime) {
        $button = '<button name="'.$field_name.'_date_trigger" id="'.$field_name.'_date_trigger" title="'.$l['w_calendar'].'">...</button>';
        $jsdate = 'Calendar.setup({inputField: "'.$field_name.'", ifFormat: "%Y-%m-%d %H:%M:%S", showsTime: "true", button: "'.$field_name.'_date_trigger"});'.K_NEWLINE;
        $format = '^([0-9]{4})([\-])([0-9]{2})([\-])([0-9]{2})([ ])([0-9]{2})([\:])([0-9]{2})([\:])([0-9]{2})$';
        $maxlen = 19;
        if (strlen($tip) == 0) {
            $tip = $l['w_datetime_format'];
        }
    }
    $str .= '<div class="row">'.K_NEWLINE;
    $str .= '<span class="label">'.K_NEWLINE;
    $str .= '<label for="'.$field_name.'" title="'.$description.'">'.$name.'</label>'.K_NEWLINE;
    if (!empty($prefix)) {
        $str .= $prefix;
    }
    $str .= '</span>'.K_NEWLINE;
    $str .= '<span class="formw">'.K_NEWLINE;
    $str .= '<input type="';
    if ($password) {
        $str .= 'password';
    } else {
        $str .= 'text';
    }
    $str .= '"';
    if ($date or $datetime) {
        $str .= ' style="direction:ltr;';
        if ($l['a_meta_dir'] == 'rtl') {
            $str .= 'text-align:right;';
        }
        $str .= '"';
    }
    $str .= ' name="'.$field_name.'" id="'.$field_name.'" value="'.htmlspecialchars($value, ENT_COMPAT, $l['a_meta_charset']).'" size="20" maxlength="'.$maxlen.'" title="'.$description.'" />';
    $str .= $button;
    if (strlen($tip) > 0) {
        $str .= ' <span class="labeldesc">'.$tip.'</span>';
    }
    if (strlen($format) > 0) {
        $str .= '<input type="hidden" name="x_'.$field_name.'" id="x_'.$field_name.'" value="'.$format.'" />'.K_NEWLINE;
        $str .= '<input type="hidden" name="xl_'.$field_name.'" id="xl_'.$field_name.'" value="'.$name.'" />'.K_NEWLINE;
    }
    $str .= '</span>'.K_NEWLINE;
    $str .= '</div>'.K_NEWLINE;
    if ($date or $datetime) {
        $str .= '<script type="text/javascript">'.K_NEWLINE;
        $str .= '//<![CDATA['.K_NEWLINE;
        $str .= $jsdate;
        $str .= '//]]>'.K_NEWLINE;
        $str .= '</script>'.K_NEWLINE;
    }
    return $str;
}

/**
 * Print text box row form.
 * @param $field_name (string) Name of the form field.
 * @param $name (string) Label.
 * @param $description (string) Label description (tooltip).
 * @param $value (string) Initial value.
 * @param $disabled (boolean) If true disable the field.
 * @param $prefix (string) code to be displayed after label.
 * @return string
 */
function getFormRowTextBox($field_name, $name, $description = '', $value = '', $disabled = false, $prefix = '')
{
    require_once(dirname(__FILE__).'/../config/tce_config.php');
    global $l;
    if (strlen($description) == 0) {
        $description = $name;
    }
    $str = ''; // string to return
    $str .= '<div class="row">'.K_NEWLINE;
    $str .= '<span class="label">'.K_NEWLINE;
    $str .= '<label for="'.$field_name.'" title="'.$description.'">'.$name.'</label>'.K_NEWLINE;
    if (!empty($prefix)) {
        $str .= $prefix;
    }
    $str .= '</span>'.K_NEWLINE;
    $str .= '<span class="formw">'.K_NEWLINE;
    $str .= '<textarea cols="50" rows="5" name="'.$field_name.'" id="'.$field_name.'" title="'.$description.'"';
    if ($disabled) {
        $str .= ' readonly="readonly" class="disabled"';
    }
    $str .= '>'.htmlspecialchars($value, ENT_NOQUOTES, $l['a_meta_charset']).'</textarea>'.K_NEWLINE;
    $str .= '</span>'.K_NEWLINE;
    $str .= '</div>'.K_NEWLINE;
    return $str;
}

/**
 * Print select box row form.
 * @param $field_name (string) Name of the form field.
 * @param $name (string) Label.
 * @param $description (string) Label description (tooltip).
 * @param $tip (string) Help to be displayed on the right of the input field.
 * @param $value (string) Initial value.
 * @param $items (array) array of items to print key => value.
 * @param $prefix (string) code to be displayed after label.
 * @return string
 */
function getFormRowSelectBox($field_name, $name, $description = '', $tip = '', $value = '', $items = array(), $prefix = '')
{
    require_once(dirname(__FILE__).'/../config/tce_config.php');
    global $l;
    if (strlen($description) == 0) {
        $description = $name;
    }
    $str = ''; // string to return
    $str .= '<div class="row">'.K_NEWLINE;
    $str .= '<span class="label">'.K_NEWLINE;
    $str .= '<label for="'.$field_name.'" title="'.$description.'">'.$name.'</label>'.K_NEWLINE;
    if (!empty($prefix)) {
        $str .= $prefix;
    }
    $str .= '</span>'.K_NEWLINE;
    $str .= '<span class="formw">'.K_NEWLINE;
    $str .= '<select name="'.$field_name.'" id="'.$field_name.'" size="0" title="'.$description.'">'.K_NEWLINE;
    foreach ($items as $key => $val) {
        $str .= '<option value="'.$key.'"';
        if ($key == $value) {
            $str .= ' selected="selected"';
        }
        $str .= '>'.$val.'</option>'.K_NEWLINE;
    }
    $str .= '</select>'.K_NEWLINE;
    if (strlen($tip) > 0) {
        $str .= ' <span class="labeldesc">'.$tip.'</span>';
    }
    $str .= '</span>'.K_NEWLINE;
    $str .= '</div>'.K_NEWLINE;
    return $str;
}

/**
 * Print check box row form.
 * @param $field_name (string) Name of the form field.
 * @param $name (string) Label.
 * @param $description (string) Label description (tooltip).
 * @param $tip (string) Help to be displayed on the right of the input field.
 * @param $value (string) Initial value.
 * @param $selected (boolean) set to true if selected.
 * @param $disabled (boolean) set to true to disable the field
 * @param $prefix (string) code to be displayed after label.
 * @return string
 */
function getFormRowCheckBox($field_name, $name, $description = '', $tip = '', $value = '', $selected = false, $disabled = false, $prefix = '')
{
    require_once(dirname(__FILE__).'/../config/tce_config.php');
    global $l;
    if (strlen($description) == 0) {
        $description = $name;
    }
    $str = ''; // string to return
    $str .= '<div class="row">'.K_NEWLINE;
    $str .= '<span class="label">'.K_NEWLINE;
    $hidden = '';
    if ($disabled) {
        // add hidden field to be submitted
        $hidden = '<input type="hidden" name="'.$field_name.'" id="'.$field_name.'" value="'.htmlspecialchars($value, ENT_COMPAT, $l['a_meta_charset']).'" />'.K_NEWLINE;
        $field_name = 'DISABLED_'.$field_name;
    }
    $str .= '<label for="'.$field_name.'" title="'.$description.'">'.$name.'</label>'.K_NEWLINE;
    if (!empty($prefix)) {
        $str .= $prefix;
    }
    $str .= '</span>'.K_NEWLINE;
    $str .= '<span class="formw">'.K_NEWLINE;
    $str .= '<input type="checkbox"';
    if ($disabled) {
        $str .= ' readonly="readonly" class="disabled"';
    }
    $str .= ' name="'.$field_name.'" id="'.$field_name.'" value="'.$value.'"';
    if (F_getBoolean($selected)) {
        $str .= ' checked="checked"';
    }
    $str .= ' title="'.$description.'" />';
    $str .= $hidden;
    if (strlen($tip) > 0) {
        $str .= ' <span class="labeldesc">'.$tip.'</span>';
    }
    $str .= '</span>'.K_NEWLINE;
    $str .= '</div>'.K_NEWLINE;
    return $str;
}

/**
 * Print fixed value row form.
 * @param $field_name (string) Name of the form field.
 * @param $name (string) Label.
 * @param $description (string) Label description (tooltip).
 * @param $tip (string) Help to be displayed on the right of the input field.
 * @param $value (string) Initial value.
 * @param $currency (boolean) if true the value is a curency number.
 * @param $prefix (string) code to be displayed after label.
 * @return string
 */
function getFormRowFixedValue($field_name, $name, $description = '', $tip = '', $value = '', $currency = false, $prefix = '')
{
    require_once(dirname(__FILE__).'/../config/tce_config.php');
    global $l;
    if (strlen($description) == 0) {
        $description = $name;
    }
    $str = ''; // string to return
    $str .= '<div class="row">'.K_NEWLINE;
    $str .= '<span class="label">'.K_NEWLINE;
    $str .= '<label for="DISABLED_'.$field_name.'" title="'.$description.'">'.$name.'</label>'.K_NEWLINE;
    if (!empty($prefix)) {
        $str .= $prefix;
    }
    $str .= '</span>'.K_NEWLINE;
    $str .= '<span class="formw">'.K_NEWLINE;
    $str .= '<input type="text" readonly="readonly" name="DISABLED_'.$field_name.'" id="DISABLED_'.$field_name.'"';
    if ($currency) {
        $value = F_formatCurrency($value, 2);
        $str .= ' class="disablednum"';
    } else {
        $str .= ' class="disabled"';
    }
    $size = 20; // default value
    if (strlen($value) > 20) {
        $size = 40;
    }
    $str .= ' value="'.htmlspecialchars($value, ENT_COMPAT, $l['a_meta_charset']).'" size="'.$size.'" maxlength="255" title="'.$description.'" />';
    if (strlen($tip) > 0) {
        $str .= ' <span class="labeldesc">'.$tip.'</span>';
    }
    // add hidden field to be submitted
    $str .= '<input type="hidden" name="'.$field_name.'" id="'.$field_name.'" value="'.htmlspecialchars($value, ENT_COMPAT, $l['a_meta_charset']).'" />'.K_NEWLINE;
    $str .= '</span>'.K_NEWLINE;
    $str .= '</div>'.K_NEWLINE;
    return $str;
}

/**
 * Print empty form row.
 * @return string
 */
function getFormSmallVertSpace()
{
    $str = '<div class="row">&nbsp;</div>'.K_NEWLINE;
    return $str;
}

/**
 * Print empty form row.
 * @return string
 */
function getFormSmallDivSpace()
{
    $str = '<div style="clear:both;height:1px;font-size:1px;">&nbsp;</div>'.K_NEWLINE;
    return $str;
}

/**
 * Print empty form row.
 * @return string
 */
function getFormRowVertSpace()
{
    $str = '<div class="row" style="margin-bottom:5px;"><hr class="dashed"/></div>'.K_NEWLINE;
    return $str;
}

/**
 * Print form row with title.
 * @param $title (string) Title to be printed.
 * @return string
 */
function getFormRowVertDiv($title = '')
{
    $str = '<div class="row"><hr class="dashed"/></div><div class="row"><div style="color:#666666;text-align:center;">'.$title.'</div></div>'.K_NEWLINE;
    return $str;
}

/**
 * Print form row with submit button when noscript is active.
 * @param $name (string) Name of the input form field.
 * @return string
 */
function getFormNoscriptSelect($name = 'selectrecord')
{
    require_once(dirname(__FILE__).'/../config/tce_config.php');
    global $l;
    $str = '<noscript>'.K_NEWLINE;
    $str .= '<div class="row">'.K_NEWLINE;
    $str .= '<span class="label">&nbsp;</span>'.K_NEWLINE;
    $str .= '<span class="formw">'.K_NEWLINE;
    $str .= '<input type="submit" name="'.$name.'" id="'.$name.'" value="'.$l['w_select'].'" />'.K_NEWLINE;
    $str .= '</span>'.K_NEWLINE;
    $str .= '</div>'.K_NEWLINE;
    $str .= '</noscript>'.K_NEWLINE;
    return $str;
}

/**
 * Print form row with label and description
 * @param $name (string) Label.
 * @param $description (string) Label description (tooltip).
 * @param $value (string) Initial value.
 * @return string
 */
function getFormDescriptionLine($name, $description = '', $value = '')
{
    if (strlen($description) == 0) {
        $description = $name;
    }
    $str = '<div class="row">'.K_NEWLINE;
    $str .= '<span class="label">'.K_NEWLINE;
    $str .= '<span title="'.$description.'">'.$name.'</span>'.K_NEWLINE;
    $str .= '</span>'.K_NEWLINE;
    $str .= '<span class="formw">'.K_NEWLINE;
    $str .= $value.'&nbsp;'.K_NEWLINE;
    $str .= '</span>'.K_NEWLINE;
    $str .= '</div>'.K_NEWLINE;
    return $str;
}

/**
 * Print input row form to upluad a file.
 * @param $field_name (string) Name of the form field.
 * @param $field_id (string) ID of the form field.
 * @param $name (string) Label.
 * @param $description (string) Label description (tooltip).
 * @param $onchange (string) Javascript code to execute at onchange event.
 * @return string
 */
function getFormUploadFile($field_name, $field_id, $name, $description = '', $onchange = '')
{
    if (strlen($description) == 0) {
        $description = $name;
    }
    $str = '<div class="row" id="div'.$field_id.'">'.K_NEWLINE;
    $str .= '<span class="label">'.K_NEWLINE;
    $str .= '<label for="'.$field_id.'" title="'.$description.'">'.$name.'</label>'.K_NEWLINE;
    $str .= '</span>'.K_NEWLINE;
    $str .= '<span class="formw">'.K_NEWLINE;
    $str .= '<input type="file" name="'.$field_name.'" id="'.$field_id.'" size="20" title="'.$description.'"';
    if (!empty($onchange)) {
        $str .= ' onchange="'.$onchange.'"';
    }
    $str .= ' />'.K_NEWLINE;
    $str .= '</span>'.K_NEWLINE;
    $str .= '&nbsp;'.K_NEWLINE;
    $str .= '</div>'.K_NEWLINE;
    return $str;
}

//============================================================+
// END OF FILE
//============================================================+
