<?php
//============================================================+
// File name   : tce_page_footer.php
// Begin       : 2001-09-02
// Last Update : 2009-09-30
//
// Description : Outputs default XHTML page footer.
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
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Outputs default XHTML page footer.
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
 * @since 2001-09-02
 */

/**
 */

echo K_NEWLINE;
echo '</div>'.K_NEWLINE; //close div.body

include('../../shared/code/tce_page_userbar.php'); // display user bar

echo '<!-- '.base64_decode(K_KEY_SECURITY).' -->'.K_NEWLINE;
if(K_PUBLIC_THEME!='default'){
	include(K_PATH_JSCRIPTS.'additional/'.K_PUBLIC_THEME.'/index.php');
}
echo '</body>'.K_NEWLINE;
echo '</html>';

//============================================================+
// END OF FILE
//============================================================+
