<?php
//============================================================+
// File name   : tce_test_start.php
// Begin       : 2010-02-06
// Last Update : 2012-12-04
//
// Description : Display selected test description and buttons
//               to start or cancel the test.
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
 * Display selected test description and buttons to start or cancel the test.
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
 * @since 2010-02-06
 */

/**
 */

require_once('../config/tce_config.php');

$test_id = 0;
$pagelevel = K_AUTH_PUBLIC_TEST_EXECUTE;
$thispage_title = $l['t_test_info'];
$thispage_description = $l['hp_test_info'];
require_once('../../shared/code/tce_authorization.php');
require_once('../code/tce_page_header.php');

echo '<div class="popupcontainer">'.K_NEWLINE;
if (isset($_REQUEST['testid']) and ($_REQUEST['testid'] > 0)) {
    require_once('../../shared/code/tce_functions_test.php');
    $test_id = intval($_REQUEST['testid']);
    echo F_printTestInfo($test_id, false);
    echo '<br />'.K_NEWLINE;
    echo '<div class="row">'.K_NEWLINE;
    // display execute button
    echo '<a href="tce_test_execute.php?testid='.$test_id.'';
    if (isset($_REQUEST['repeat']) and ($_REQUEST['repeat'] == 1)) {
        echo '&amp;repeat=1';
    }
    echo '" title="'.$l['h_execute'].'" class="xmlbutton">'.$l['w_execute'].'</a> ';
    echo '<a href="index.php" title="'.$l['h_cancel'].'" class="xmlbutton">'.$l['w_cancel'].'</a>';
    echo '</div>'.K_NEWLINE;
}
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
