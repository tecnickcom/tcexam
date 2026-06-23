<?php

//============================================================+
// File name   : tce_functions_form.php
// Begin       : 2001-11-07
// Last Update : 2023-11-30
//
// Description : Functions to handle XHTML Form Fields.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

/**
 * @file
 * Functions to handle XHTML Form Fields.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @since 2001-11-07
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
} elseif (isset($_POST['deletesubject'])) {
    $menu_mode = 'deletesubject';
}

if (empty($menu_mode)) {
    $menu_mode = '';
} elseif (empty($_POST['csrf_token']) || !checkCSRFToken($_POST['csrf_token'])) {
    // check for CSRF
    exit();
}

define('K_EMAIL_RE_PATTERN', '^([a-zA-Z0-9_\.\-\+\%]+)@([a-zA-Z0-9\.\-]+)$');

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
    global $l;
    if (
        empty($formfields)
        || !array_key_exists('ff_required', $formfields)
        || strlen($formfields['ff_required']) <= 0
    ) {
        return false;
    }

    $missing_fields = '';
    $required_fields = explode(',', $formfields['ff_required']);
    $required_fields_labels = explode(',', $formfields['ff_required_labels']);
    // form fields labels
    $counter = count($required_fields); // form fields labels
    for ($i = 0; $i < $counter; ++$i) { //for each required field
        $fieldname = trim($required_fields[$i]);
        $fieldname = preg_replace('/[^a-z0-9_\[\]]/i', '', $fieldname);
        if (!array_key_exists($fieldname, $formfields) || strlen(trim($formfields[$fieldname])) <= 0) { //if is empty
            if ($required_fields_labels[$i] !== '' && $required_fields_labels[$i] !== '0') { // check if the field has a label
                $fieldname = htmlspecialchars($required_fields_labels[$i], ENT_NOQUOTES, $l['a_meta_charset']);
            }

            $missing_fields .= ', ' . stripslashes($fieldname);
        }
    }

    if (strlen($missing_fields) > 1) {
        $missing_fields = substr($missing_fields, 1); // cuts first comma
    }

    return $missing_fields;
}

/**
 * Server-side registry of canonical field-format patterns, keyed by form field name.
 *
 * This is the single source of truth used by F_check_fields_format() to validate submitted
 * values. The matching pattern is resolved here, on the server, by field name.
 *
 * Field names are static across the application, so a flat "name => un-delimited regex" map is
 * sufficient. Configurable constants are reused where they already exist.
 *
 * @return array<string,string> map of field name to un-delimited regular expression
 */
function F_get_field_format_registry(): array
{
    // Canonical, server-authored patterns (the password pattern is admin-configurable).
    $re_email = K_EMAIL_RE_PATTERN;
    $re_password = defined('K_USRREG_PASSWORD_RE') ? K_USRREG_PASSWORD_RE : '^(.{8,})$';
    $re_int = '^([0-9]*)$';
    $re_decimal = '^([0-9\+\-]*)([\.]?)([0-9]*)$';
    $re_iplist = '^([0-9a-fA-F,\:\.\*-]*)$';
    $re_date = '^([0-9]{4})([\-])([0-9]{2})([\-])([0-9]{2})$';
    $re_datetime = '^([0-9]{4})([\-])([0-9]{2})([\-])([0-9]{2})([ T])([0-9]{2})([\:])([0-9]{2})(([\:])([0-9]{2}))?$';

    return [
        // user identity / credentials
        'user_email' => $re_email,
        'newpassword' => $re_password,
        'new_test_password' => $re_password,
        'user_birthdate' => $re_date,
        // test definition
        'test_begin_time' => $re_datetime,
        'test_end_time' => $re_datetime,
        'test_duration_time' => $re_int,
        'test_ip_range' => $re_iplist,
        'test_score_right' => $re_decimal,
        'test_score_wrong' => $re_decimal,
        'test_score_unanswered' => $re_decimal,
        'test_score_threshold' => $re_decimal,
        'tsubset_quantity' => $re_int,
        'tsubset_answers' => $re_int,
        // question / rating
        'question_timer' => $re_int,
        'testlog_score' => $re_decimal,
    ];
}

/**
 * Check fields format against the server-side canonical pattern registry.<br>
 * Returns a string containing a list of wrong fields (comma separated).
 *
 * For every field present in F_get_field_format_registry() that was submitted with a non-empty
 * value, the value is matched against its canonical (server-authored) pattern. The client-supplied
 * 'x_<field>' value is ignored entirely, so a tampered/omitted/malicious pattern can neither bypass
 * validation nor be executed as a regular expression.
 *
 * @param $formfields (array) input array containing form fields
 * @return string comma-separated list of wrong fields (empty when all valid)
 */
function F_check_fields_format($formfields)
{
    global $l;
    if (!is_array($formfields) || empty($formfields)) {
        return '';
    }

    // Upper bound on the value length we will run a pattern against; bounds worst-case
    // backtracking cost so an over-long submitted value cannot stall the request (maxlength is
    // a client-only hint and is not enforced by the browser for a crafted POST).
    $maxvaluelen = 4096;

    $wrongfields = '';
    foreach (F_get_field_format_registry() as $fieldname => $pattern) {
        // only validate fields that were actually submitted with a non-empty scalar value
        $raw = $formfields[$fieldname] ?? null;
        if (!is_scalar($raw) || strlen((string) $raw) === 0) {
            continue;
        }

        $value = (string) $raw;
        // an over-long value is treated as invalid rather than risk a costly match. Patterns are
        // server-authored constants, so preg_match needs no error suppression.
        $matches = strlen($value) <= $maxvaluelen ? preg_match('~' . $pattern . '~i', $value) : 0;
        // $matches === false means the (server-authored) pattern errored: treat as "skip" so a
        // bad pattern cannot silently reject every submission; only a clean 0 means "wrong format".
        if ($matches === 0) {
            $label = $fieldname;
            $xlabel = $formfields['xl_' . $fieldname] ?? ''; // human label supplied by the form
            if (is_scalar($xlabel) && (string) $xlabel !== '') {
                $charset = (string) ($l['a_meta_charset'] ?? 'UTF-8');
                $label = htmlspecialchars((string) $xlabel, ENT_NOQUOTES, $charset);
            }

            $wrongfields .= ', ' . $label;
        }
    }

    if (strlen($wrongfields) > 1) {
        $wrongfields = substr($wrongfields, 2); // cuts first 2 chars (", ")
    }

    return $wrongfields;
}

/**
 * Check Form Fields.
 * see: F_check_required_fields, F_check_fields_format
 * @return false in case of error, true otherwise
 */
function F_check_form_fields()
{
    require_once '../config/tce_config.php';
    global $l;
    $formfields = F_decode_form_fields(); //decode form fields
    //check missing fields
    if ($missing_fields = F_check_required_fields($formfields)) {
        F_print_error('WARNING', $l['m_form_missing_fields'] . ': ' . $missing_fields);

        return false;
    }

    //check fields format
    if ($wrong_fields = F_check_fields_format($formfields)) {
        F_print_error('WARNING', $l['m_form_wrong_fields'] . ': ' . $wrong_fields);

        return false;
    }

    return true;
}

/**
 * Returns XHTML code string to display a window close button
 * @param $onclick (string) additional javascript code to execute before closing the window.
 * @return XHTML code string
 */
function F_close_button($onclick = '')
{
    require_once '../config/tce_config.php';
    global $l;
    $str = '';
    $str .= '<div class="row">' . K_NEWLINE;
    $str .= '<form action="' . htmlspecialchars($_SERVER['SCRIPT_NAME'], ENT_QUOTES) . '" id="closeform">' . K_NEWLINE;
    $str .= '<div>' . K_NEWLINE;
    $str .=
        '<input type="button" name="wclose" id="wclose" value="'
        . $l['w_close']
        . '" title="'
        . $l['h_close_window']
        . '" onclick="'
        . $onclick
        . 'window.close();" />'
        . K_NEWLINE;
    $str .= '</div>' . K_NEWLINE;
    $str .= '</form>' . K_NEWLINE;
    return $str . ('</div>' . K_NEWLINE);
}

/**
 * Returns XHTML code string to display Form Submit Button.
 * @param $name (string) button name
 * @param $value (string) label for button
 * @param $title (string) button title, default=''
 * @param $extra (string) optional extra fields to add to the input tag, default=''
 *
 * @return XHTML code string
 */
function F_submit_button($name, $value, $title = '', $extra = '')
{
    echo
        '<input type="submit" name="'
            . $name
            . '" id="'
            . $name
            . '" value="'
            . $value
            . '" title="'
            . $title
            . '" '
            . $extra
            . '/>'
    ;
}

/**
 * Returns XHTML code string to display the CSRF token field.
 * @return XHTML code string
 */
function F_getCSRFTokenField()
{
    return '<input type="hidden" name="csrf_token" id="csrf_token" value="' . F_getCSRFToken() . '" />';
}

/**
 * Returns the visual "required field" marker to append to a form label.
 * The control itself should also carry aria-required="true".
 * @param $required (boolean) true if the field is required.
 * @return string XHTML code (empty string when the field is not required).
 */
function getRequiredMark($required = false)
{
    global $l;
    if (!$required) {
        return '';
    }

    return (
        ' <abbr class="required" title="'
        . htmlspecialchars($l['w_required'], ENT_QUOTES, $l['a_meta_charset'])
        . '">*</abbr>'
    );
}

/**
 * Print input row form.
 * @param $field_name (string) Name of the form field.
 * @param $name (string) Label.
 * @param $description (string) Label description (tooltip).
 * @param $tip (string) Help to be displayed on the right of the input field.
 * @param $value (string) Initial value.
 * @param $format (string) Regular expression to check the format of the field.
 * @param $maxlen (int) Maximum input length.
 * @param $date (boolean) True if the field is a date input.
 * @param $datetime (boolean) True if the field is a date-time input.
 * @param $password (boolean) True if the field is a password.
 * @param $prefix (string) code to be displayed after label.
 * @param $required (boolean) If true the field is marked as required.
 * @param $autocomplete (string) HTML autocomplete token (e.g. 'email', 'username', 'current-password', 'new-password').
 * @param $inputtype (string) Override for the HTML input type (e.g. 'email', 'tel', 'number'); ignored for password/date/datetime fields.
 * @return string
 */
function getFormRowTextInput(
    $field_name,
    $name,
    $description = '',
    $tip = '',
    $value = '',
    $format = '',
    $maxlen = 255,
    $date = false,
    $datetime = false,
    #[\SensitiveParameter]
    $password = false,
    $prefix = '',
    $required = false,
    $autocomplete = '',
    $inputtype = '',
) {
    require_once __DIR__ . '/../config/tce_config.php';
    global $l;
    if (strlen($description) == 0) {
        $description = $name;
    }

    $str = ''; // string to return
    if ($date) {
        $format = '^([0-9]{4})([\-])([0-9]{2})([\-])([0-9]{2})$';
        $maxlen = 10;
        if (strlen($tip) == 0) {
            $tip = $l['w_date_format'];
        }
    } elseif ($datetime) {
        // native datetime-local uses an ISO 'T' separator and may omit the seconds
        $format = '^([0-9]{4})([\-])([0-9]{2})([\-])([0-9]{2})([ T])([0-9]{2})([\:])([0-9]{2})(([\:])([0-9]{2}))?$';
        $maxlen = 19;
        if (strlen($tip) == 0) {
            $tip = $l['w_datetime_format'];
        }
    }

    $str .= '<div class="row">' . K_NEWLINE;
    $str .= '<span class="label">' . K_NEWLINE;
    // the caller may supply its own required marker via $prefix; only add the default mark otherwise
    $str .=
        '<label for="'
        . $field_name
        . '" title="'
        . $description
        . '">'
        . $name
        . (empty($prefix) ? getRequiredMark($required) : '')
        . '</label>'
        . K_NEWLINE;
    if (!empty($prefix)) {
        $str .= $prefix;
    }

    $str .= '</span>' . K_NEWLINE;
    $str .= '<span class="formw">' . K_NEWLINE;
    $str .= '<input type="';
    if ($password) {
        $str .= 'password';
    } elseif ($date) {
        $str .= 'date';
    } elseif ($datetime) {
        $str .= 'datetime-local';
    } elseif (strlen($inputtype) > 0) {
        $str .= $inputtype;
    } else {
        $str .= 'text';
    }

    $str .= '"';
    if ($datetime) {
        $str .= ' step="1"';
    }

    if ($value === null) {
        $value = '';
    }

    if ($datetime) {
        // native datetime-local requires the ISO 'T' separator in the value attribute
        $value = str_replace(' ', 'T', $value);
    }

    $str .=
        ' name="'
        . $field_name
        . '" id="'
        . $field_name
        . '" value="'
        . htmlspecialchars($value, ENT_COMPAT, $l['a_meta_charset'])
        . '" size="20" maxlength="'
        . $maxlen
        . '" title="'
        . $description
        . '"';
    if ($required) {
        $str .= ' aria-required="true"';
    }

    if (strlen($autocomplete) > 0) {
        $str .= ' autocomplete="' . $autocomplete . '"';
    }

    if (strlen($tip) > 0) {
        $str .= ' aria-describedby="desc_' . $field_name . '"';
    }

    $str .= ' />';
    if (strlen($tip) > 0) {
        $str .= ' <span class="labeldesc" id="desc_' . $field_name . '">' . $tip . '</span>';
    }

    if (strlen($format) > 0) {
        // The value's format is validated server-side against a canonical pattern looked up by
        // field name (see F_get_field_format_registry()); the regex is no longer shipped to, nor
        // read back from, the client. Only the human label is emitted, used to name the field in
        // any "wrong format" error message.
        $str .=
            '<input type="hidden" name="xl_'
            . $field_name
            . '" id="xl_'
            . $field_name
            . '" value="'
            . $name
            . '" />'
            . K_NEWLINE;
    }

    $str .= '</span>' . K_NEWLINE;
    $str .= '</div>' . K_NEWLINE;

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
 * @param $required (boolean) If true the field is marked as required.
 * @return string
 */
function getFormRowTextBox(
    $field_name,
    $name,
    $description = '',
    $value = '',
    $disabled = false,
    $prefix = '',
    $required = false,
) {
    require_once __DIR__ . '/../config/tce_config.php';
    global $l;
    if (strlen($description) == 0) {
        $description = $name;
    }

    $str = ''; // string to return
    $str .= '<div class="row">' . K_NEWLINE;
    $str .= '<span class="label">' . K_NEWLINE;
    $str .=
        '<label for="'
        . $field_name
        . '" title="'
        . $description
        . '">'
        . $name
        . getRequiredMark($required)
        . '</label>'
        . K_NEWLINE;
    if (!empty($prefix)) {
        $str .= $prefix;
    }

    $str .= '</span>' . K_NEWLINE;
    $str .= '<span class="formw">' . K_NEWLINE;
    $str .=
        '<textarea cols="50" rows="5" name="' . $field_name . '" id="' . $field_name . '" title="' . $description . '"';
    if ($required) {
        $str .= ' aria-required="true"';
    }

    if ($disabled) {
        $str .= ' readonly="readonly" class="disabled"';
    }

    $str .= '>' . htmlspecialchars($value ?? '', ENT_NOQUOTES, $l['a_meta_charset']) . '</textarea>' . K_NEWLINE;
    $str .= '</span>' . K_NEWLINE;
    return $str . ('</div>' . K_NEWLINE);
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
function getFormRowSelectBox(
    $field_name,
    $name,
    $description = '',
    $tip = '',
    $value = '',
    $items = [],
    $prefix = '',
    $required = false,
) {
    require_once __DIR__ . '/../config/tce_config.php';
    global $l;
    if (strlen($description) == 0) {
        $description = $name;
    }

    $str = ''; // string to return
    $str .= '<div class="row">' . K_NEWLINE;
    $str .= '<span class="label">' . K_NEWLINE;
    $str .=
        '<label for="'
        . $field_name
        . '" title="'
        . $description
        . '">'
        . $name
        . getRequiredMark($required)
        . '</label>'
        . K_NEWLINE;
    if (!empty($prefix)) {
        $str .= $prefix;
    }

    $str .= '</span>' . K_NEWLINE;
    $str .= '<span class="formw">' . K_NEWLINE;
    $str .= '<select name="' . $field_name . '" id="' . $field_name . '" title="' . $description . '"';
    if ($required) {
        $str .= ' aria-required="true"';
    }

    if (strlen($tip) > 0) {
        $str .= ' aria-describedby="desc_' . $field_name . '"';
    }

    $str .= '>' . K_NEWLINE;
    foreach ($items as $key => $val) {
        $str .= '<option value="' . $key . '"';
        if ($key == $value) {
            $str .= ' selected="selected"';
        }

        $str .= '>' . $val . '</option>' . K_NEWLINE;
    }

    $str .= '</select>' . K_NEWLINE;
    if (strlen($tip) > 0) {
        $str .= ' <span class="labeldesc" id="desc_' . $field_name . '">' . $tip . '</span>';
    }

    $str .= '</span>' . K_NEWLINE;
    return $str . ('</div>' . K_NEWLINE);
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
function getFormRowCheckBox(
    $field_name,
    $name,
    $description = '',
    $tip = '',
    $value = '',
    $selected = false,
    $disabled = false,
    $prefix = '',
) {
    require_once __DIR__ . '/../config/tce_config.php';
    global $l;
    if (strlen($description) == 0) {
        $description = $name;
    }

    $str = ''; // string to return
    $str .= '<div class="row">' . K_NEWLINE;
    $str .= '<span class="label">' . K_NEWLINE;
    $hidden = '';
    if ($disabled) {
        // add hidden field to be submitted
        $hidden =
            '<input type="hidden" name="'
            . $field_name
            . '" id="'
            . $field_name
            . '" value="'
            . htmlspecialchars($value, ENT_COMPAT, $l['a_meta_charset'])
            . '" />'
            . K_NEWLINE;
        $field_name = 'DISABLED_' . $field_name;
    }

    $str .= '<label for="' . $field_name . '" title="' . $description . '">' . $name . '</label>' . K_NEWLINE;
    if (!empty($prefix)) {
        $str .= $prefix;
    }

    $str .= '</span>' . K_NEWLINE;
    $str .= '<span class="formw">' . K_NEWLINE;
    $str .= '<input type="checkbox"';
    if ($disabled) {
        $str .= ' readonly="readonly" class="disabled"';
    }

    $str .= ' name="' . $field_name . '" id="' . $field_name . '" value="' . $value . '"';
    if (F_getBoolean($selected)) {
        $str .= ' checked="checked"';
    }

    $str .= ' title="' . $description . '"';
    if (strlen($tip) > 0) {
        $str .= ' aria-describedby="desc_' . $field_name . '"';
    }

    $str .= ' />';
    $str .= $hidden;
    if (strlen($tip) > 0) {
        $str .= ' <span class="labeldesc" id="desc_' . $field_name . '">' . $tip . '</span>';
    }

    $str .= '</span>' . K_NEWLINE;
    return $str . ('</div>' . K_NEWLINE);
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
function getFormRowFixedValue(
    $field_name,
    $name,
    $description = '',
    $tip = '',
    $value = '',
    $currency = false,
    $prefix = '',
) {
    require_once __DIR__ . '/../config/tce_config.php';
    global $l;
    if (strlen($description) == 0) {
        $description = $name;
    }

    $str = ''; // string to return
    $str .= '<div class="row">' . K_NEWLINE;
    $str .= '<span class="label">' . K_NEWLINE;
    $str .= '<label for="DISABLED_' . $field_name . '" title="' . $description . '">' . $name . '</label>' . K_NEWLINE;
    if (!empty($prefix)) {
        $str .= $prefix;
    }

    $str .= '</span>' . K_NEWLINE;
    $str .= '<span class="formw">' . K_NEWLINE;
    $str .=
        '<input type="text" readonly="readonly" name="DISABLED_' . $field_name . '" id="DISABLED_' . $field_name . '"';
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

    $str .=
        ' value="'
        . htmlspecialchars($value, ENT_COMPAT, $l['a_meta_charset'])
        . '" size="'
        . $size
        . '" maxlength="255" title="'
        . $description
        . '" />';
    if (strlen($tip) > 0) {
        $str .= ' <span class="labeldesc">' . $tip . '</span>';
    }

    // add hidden field to be submitted
    $str .=
        '<input type="hidden" name="'
        . $field_name
        . '" id="'
        . $field_name
        . '" value="'
        . htmlspecialchars($value, ENT_COMPAT, $l['a_meta_charset'])
        . '" />'
        . K_NEWLINE;
    $str .= '</span>' . K_NEWLINE;
    return $str . ('</div>' . K_NEWLINE);
}

/**
 * Print empty form row.
 * @return string
 */
function getFormSmallVertSpace()
{
    return '<div class="row">&nbsp;</div>' . K_NEWLINE;
}

/**
 * Print empty form row.
 * @return string
 */
function getFormSmallDivSpace()
{
    return '<div style="clear:both;height:1px;font-size:1px;">&nbsp;</div>' . K_NEWLINE;
}

/**
 * Print empty form row.
 * @return string
 */
function getFormRowVertSpace()
{
    return '<div class="row" style="margin-bottom:5px;"><hr class="dashed"/></div>' . K_NEWLINE;
}

/**
 * Print form row with title.
 * @param $title (string) Title to be printed.
 * @return string
 */
function getFormRowVertDiv($title = '')
{
    return (
        '<div class="row"><hr class="dashed"/></div><div class="row"><div style="color:#666666;text-align:center;">'
        . $title
        . '</div></div>'
        . K_NEWLINE
    );
}

/**
 * Print form row with submit button when noscript is active.
 * @param $name (string) Name of the input form field.
 * @return string
 */
function getFormNoscriptSelect($name = 'selectrecord')
{
    require_once __DIR__ . '/../config/tce_config.php';
    global $l;
    $str = '<noscript>' . K_NEWLINE;
    $str .= '<div class="row">' . K_NEWLINE;
    $str .= '<span class="label">&nbsp;</span>' . K_NEWLINE;
    $str .= '<span class="formw">' . K_NEWLINE;
    $str .=
        '<input type="submit" name="' . $name . '" id="' . $name . '" value="' . $l['w_select'] . '" />' . K_NEWLINE;
    $str .= '</span>' . K_NEWLINE;
    $str .= '</div>' . K_NEWLINE;
    return $str . ('</noscript>' . K_NEWLINE);
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

    $str = '<div class="row">' . K_NEWLINE;
    $str .= '<span class="label">' . K_NEWLINE;
    $str .= '<span title="' . $description . '">' . $name . '</span>' . K_NEWLINE;
    $str .= '</span>' . K_NEWLINE;
    $str .= '<span class="formw">' . K_NEWLINE;
    $str .= $value . '&nbsp;' . K_NEWLINE;
    $str .= '</span>' . K_NEWLINE;
    return $str . ('</div>' . K_NEWLINE);
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

    $str = '<div class="row" id="div' . $field_id . '">' . K_NEWLINE;
    $str .= '<span class="label">' . K_NEWLINE;
    $str .= '<label for="' . $field_id . '" title="' . $description . '">' . $name . '</label>' . K_NEWLINE;
    $str .= '</span>' . K_NEWLINE;
    $str .= '<span class="formw">' . K_NEWLINE;
    $str .=
        '<input type="file" name="' . $field_name . '" id="' . $field_id . '" size="20" title="' . $description . '"';
    if (!empty($onchange)) {
        $str .= ' onchange="' . $onchange . '"';
    }

    $str .= ' />' . K_NEWLINE;
    $str .= '</span>' . K_NEWLINE;
    $str .= '&nbsp;' . K_NEWLINE;
    return $str . ('</div>' . K_NEWLINE);
}
