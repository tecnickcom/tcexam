<?php
//============================================================+
// File name   : tce_colorpicker.php
// Begin       : 2001-11-05
// Last Update : 2013-03-17
//
// Description : HTML Color Picker Functions.
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
 * HTML Color Picker Functions.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2008-10-01
 */

/**
 */

require_once('../config/tce_config.php');
$pagelevel = 0;
//require_once('../../shared/code/tce_authorization.php');
$thispage_title = 'Color Picker';
require_once('../code/tce_page_header_popup.php');
echo '<script src="'.K_PATH_SHARED_JSCRIPTS.'inserttag.js" type="text/javascript"></script>'.K_NEWLINE;
F_html_color_picker($_REQUEST['frm'], $_REQUEST['fld'], $_REQUEST['tag']);
require_once('../code/tce_page_footer_popup.php');

/**
 * Display Color Picker
 * @author Nicola Asuni
 * @since 2008-10-01
 */
function F_html_color_picker($callingform, $callingfield, $tag)
{
    global $l;
    require_once('../config/tce_config.php');
    require_once('../../shared/tcpdf/include/tcpdf_colors.php');
    require_once('../../shared/code/tce_functions_form.php');

    // sanitize input parameters
    $callingform = preg_replace('/[^a-z0-9_]/', '', $callingform);
    $callingfield = preg_replace('/[^a-z0-9_]/', '', $callingfield);
    $tag = preg_replace('/[^a-z0-9_]/', '', $tag);

    echo '<div style="margin:0;padding:0;">'.K_NEWLINE;
    echo '<a onclick="FJ_pick_color(0); document.getElementById(\'colorname\').selectedIndex=0;"><img src="'.K_PATH_IMAGES.'buttons/colortable.jpg" alt="" id="colorboard" width="320" height="300" style="margin:0;padding:0;border:none;" /></a>'.K_NEWLINE;
    echo K_NEWLINE;
    echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_colorpicker">'.K_NEWLINE;
    echo '<div class="smalldigit" style="width:320px;font-size:80%;" >';
    echo 'DEC:';
    echo '<input type="text" name="RED" id="RED" size="3" maxlength="3" readonly="readonly" title="RED (DEC)"/>';
    echo '<input type="text" name="GREEN" id="GREEN" size="3" maxlength="3" readonly="readonly" title="GREEN (DEC)"/>';
    echo '<input type="text" name="BLUE" id="BLUE" size="3" maxlength="3" readonly="readonly" title="BLUE (DEC)"/>';
    echo '&nbsp;&nbsp;&nbsp;&nbsp;';
    echo 'HEX:';
    echo '<input type="text" name="HRED" id="HRED" size="3" maxlength="2" readonly="readonly" title="RED (HEX)"/>';
    echo '<input type="text" name="HGREEN" id="HGREEN" size="3" maxlength="2" readonly="readonly" title="GREEN (HEX)"/>';
    echo '<input type="text" name="HBLUE" id="HBLUE" size="3" maxlength="2" readonly="readonly" title="BLUE (HEX)"/>';
    echo '</div>'.K_NEWLINE;

    // print a table of websafe colors
    $ck = 1;
    echo '<div style="width:320px;">';
    foreach (TCPDF_COLORS::$webcolor as $key => $val) { // for each color in table
        echo '<a title="'.$key.'" onclick="document.getElementById(\'CSELECTED\').value=\'#'.$val.'\';FJ_pick_color(1);document.getElementById(\'colorname\').selectedIndex='.$ck.';" style="text-decoration:none;font-size:3px;">';
        echo '<span style="background-color:#'.$val.';padding:0;margin:0;width:20px;height:10px;float:left;">&nbsp;</span>';
        echo '</a>';
        $ck++;
    }
    echo '<br style="clear:both;"/>';
    echo '</div>'.K_NEWLINE;
    echo '<div id="pickedcolor" style="visibility:visible;border:1px solid black;width:320px;height:30px;">&nbsp;</div>'.K_NEWLINE;
    echo '<div>'.K_NEWLINE;
    echo '<select name="colorname" id="colorname" size="0" onchange="document.getElementById(\'CSELECTED\').value=document.getElementById(\'colorname\').options[document.getElementById(\'colorname\').selectedIndex].value; FJ_pick_color(1);">'.K_NEWLINE;
    echo '<option value=""></option>'.K_NEWLINE;
    reset(TCPDF_COLORS::$webcolor);
    foreach (TCPDF_COLORS::$webcolor as $key => $val) { // for each color in table
        echo '<option value="#'.$val.'">'.$key.'</option>'.K_NEWLINE;
    }
    echo '</select>';
    echo '<input type="text" name="CSELECTED" id="CSELECTED" size="10" maxlength="7" value="" onchange="FJ_pick_color(1); document.getElementById(\'colorname\').selectedIndex=0;" />'.K_NEWLINE;
    $onclick = 'FJ_insert_tag(window.opener.document.getElementById(\''.$callingform.'\').'.$callingfield.', \'['.$tag.'=\'+document.getElementById(\'CSELECTED\').value+\']\');';
    echo '<input type="button" name="wclose" id="wclose" value="'.$l['w_close'].'" title="'.$l['h_close_window'].'" onclick="'.$onclick.'self.close();" />'.K_NEWLINE;
    echo '</div>'.K_NEWLINE;
    echo F_getCSRFTokenField().K_NEWLINE;
    echo '</form>'.K_NEWLINE;
    echo '</div>'.K_NEWLINE;
?>
<script type="text/javascript">
//<![CDATA[
// variables
// ------------------------------------------------------------
var Xpos, Ypos;
var Red, Green, Blue;
var hexChars = '0123456789ABCDEF';

// ------------------------------------------------------------
// capture event
// ------------------------------------------------------------
if (window.captureEvents) {
    document.captureEvents(Event.MOUSEMOVE);
}
document.onmousemove = FJ_get_coordinates;

// ------------------------------------------------------------
// Get cursor coordinates and store on Xpos and Ypos variables
// ------------------------------------------------------------
function FJ_get_coordinates(e) {
    if (window.captureEvents) {
        Xpos = e.pageX;
        Ypos = e.pageY;
    } else {
        Xpos = (event.clientX + document.body.scrollLeft);
        Ypos = (event.clientY + document.body.scrollTop);
    }

    //calculate color
    if (Xpos<=50) {
        Red=255;
        Green=Math.round(Xpos * 5.1);
        Blue=0;
    } else if (Xpos<=100) {
        Red=255-Math.round((Xpos-50) * 5.1);
        Green=255;
        Blue=0;
    } else if (Xpos<=150) {
        Red=0;
        Green=255;
        Blue=Math.round((Xpos-100) * 5.1);
    } else if (Xpos<=200) {
        Red=0;
        Green=255-Math.round((Xpos-150) * 5.1);
        Blue=255;
    } else if (Xpos<=250) {
        Red=Math.round((Xpos-200) * 5.1);
        Green=0;
        Blue=255;
    } else if (Xpos<=300) {
        Red=255;
        Green=0;
        Blue=255-Math.round((Xpos-250) * 5.1);
    } else if (Xpos<=320){ //grey scale
        light = Math.round((1-(Ypos/300))*255);
        Red=light;
        Green=light;
        Blue=light;
    }

    // change luminosity
    if ((Xpos>=0) && (Xpos<=300) && (Ypos>=0) && (Ypos<=300)) {
        light = Math.round((1-(Ypos/150))*255);
        Red += light;
        if (Red>255) {
            Red=255;
        } else if (Red<0) {
            Red=0;
        }
        Green += light;
        if (Green>255) {
            Green=255;
        } else if (Green<0) {
            Green=0;
        }
        Blue += light;
        if (Blue>255) {
            Blue=255;
        } else if (Blue<0) {
            Blue=0;
        }
    }
    // display color
    if ((Xpos>=0) && (Xpos<=320) && (Ypos>=0) && (Ypos<=300)) {
        document.getElementById('RED').value = Red;
        document.getElementById('GREEN').value = Green;
        document.getElementById('BLUE').value = Blue;
        document.getElementById('HRED').value = FJ_dec_to_hex(Red);
        document.getElementById('HGREEN').value = FJ_dec_to_hex(Green);
        document.getElementById('HBLUE').value = FJ_dec_to_hex(Blue);
    }
    return;
}

// ------------------------------------------------------------
// calculate color from coordinates
// manual=1 means color introduced by keyboard
// ------------------------------------------------------------
function FJ_pick_color(manual) {
if ((manual)||((Xpos<=320)&&(Ypos<=300))) { //check if coordinates are valid

if (!manual) {
document.getElementById('CSELECTED').value = '#'+document.getElementById('HRED').value+''+document.getElementById('HGREEN').value+''+document.getElementById('HBLUE').value;
}

newcolor = document.getElementById('CSELECTED').value;

//show selected color on picked color layer
// check browser capabilities
if (document.layers){
document.layers['pickedcolor'].bgColor=newcolor;
}
if (document.all){
document.all.pickedcolor.style.backgroundColor=newcolor;
}
if (!document.all && document.getElementById){
document.getElementById('pickedcolor').style.backgroundColor=newcolor;
}
}
return;
}

// ------------------------------------------------------------
// convert decimal value to hexadecimal (FF is the max value)
// ------------------------------------------------------------
function FJ_dec_to_hex (Dec) {
var a = Dec % 16;
var b = (Dec - a)/16;
hex = hexChars.charAt(b)+''+hexChars.charAt(a);
return hex;
}

// default color
document.getElementById('CSELECTED').value='#000000';
FJ_pick_color(1);
//]]>
</script>
<?php
return;
}

//============================================================+
// END OF FILE
//============================================================+
