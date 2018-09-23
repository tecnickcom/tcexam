<?php
//============================================================+
// File name   : tce_filemanager.php
// Begin       : 2010-09-20
// Last Update : 2013-04-12
//
// Description : File manager for media files.
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
 * File manager for media files.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2010-09-21
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_FILEMANAGER;
require_once('../../shared/code/tce_authorization.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('tce_functions_filemanager.php');

$thispage_title = $l['t_filemanager'];
require_once('../code/tce_page_header.php');

// Non-administrators may access to their cache folder or the cache folders of the users in their groups
if ($_SESSION['session_user_level'] < K_AUTH_ADMINISTRATOR) {
    $root_dir = K_PATH_CACHE.'uid/';
    $usr_dir = $root_dir.$_SESSION['session_user_id'].'/';
    // create user directory if missing
    if (!F_file_exists($usr_dir)) {
        $oldumask = @umask(0);
        if (!@mkdir($usr_dir, 0744, true)) {
            F_print_error('ERROR', $l['m_directory_create_error']);
        }
        @umask($oldumask);
    }
} else {
    $root_dir = K_PATH_CACHE;
    $usr_dir = $root_dir;
}

if (isset($_REQUEST['v'])) {
    $viewmode = $_REQUEST['v'];
} elseif (isset($_REQUEST['viewmodet'])) {
    $viewmode = true;
} elseif (isset($_REQUEST['viewmodev'])) {
    $viewmode = false;
} else {
    // default table mode
    $viewmode = true;
}

// select current dir
if (isset($_REQUEST['d'])) {
    $dir = urldecode($_REQUEST['d']);
} elseif (isset($_REQUEST['dir'])) {
    $dir = $_REQUEST['dir'];
} else {
    $dir = $usr_dir;
}
// get the authorized dirs
$authdirs = F_getAuthorizedDirs();
// check if the user is authorized to use this directory
if (!F_isAuthorizedDir($dir, $root_dir, $authdirs)) {
    $dir = $root_dir;
}

// select file
if (isset($_REQUEST['f'])) {
    $file = urldecode($_REQUEST['f']);
} elseif (isset($_REQUEST['file'])) {
    $file = $_REQUEST['file'];
} else {
    $file = '';
}
// check if the user is authorized to use this file
if (!F_isAuthorizedDir($file.'/', $root_dir, $authdirs)) {
    $file = '';
}

// upload multimedia file
if (isset($_POST['sendfile']) and ($_FILES['userfile']['name'])) {
    require_once('../code/tce_functions_upload.php');
    if (!F_isAuthorizedDir($dir, $root_dir, $authdirs)) {
        $dir = $usr_dir;
    }
    $file = F_upload_file('userfile', $dir);
    if (!empty($file)) {
        $file = $dir.$file;
    }
}

if (isset($_POST['rename'])) {
    $menu_mode = 'rename';
} elseif (isset($_POST['newdir'])) {
    $menu_mode = 'newdir';
} elseif (isset($_POST['deldir'])) {
    $menu_mode = 'deldir';
}

// switch actions
switch ($menu_mode) {
    case 'delete':{
        F_stripslashes_formfields();
        if ($_SESSION['session_user_level'] < K_AUTH_DELETE_MEDIAFILE) {
            F_print_error('WARNING', $l['m_authorization_denied']);
            break;
        }
        if (!F_isAuthorizedDir($dir, $root_dir, $authdirs)) {
            F_print_error('WARNING', $l['m_authorization_denied']);
            break;
        }
        // ask confirmation
        F_print_error('WARNING', $l['m_delete_confirm'].' [ '.basename($file).' ]');
        echo '<div class="confirmbox">'.K_NEWLINE;
        echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_delete">'.K_NEWLINE;
        echo '<div>'.K_NEWLINE;
        echo '<input type="hidden" name="dir" id="dir" value="'.$dir.'" />'.K_NEWLINE;
        echo '<input type="hidden" name="file" id="file" value="'.$file.'" />'.K_NEWLINE;
        F_submit_button('forcedelete', $l['w_delete'], $l['h_delete']);
        F_submit_button('cancel', $l['w_cancel'], $l['h_cancel']);
        echo '</div>'.K_NEWLINE;
        echo '</form>'.K_NEWLINE;
        echo '</div>'.K_NEWLINE;
        break;
    }

    case 'forcedelete':{
        F_stripslashes_formfields(); // Delete
        if ($_SESSION['session_user_level'] < K_AUTH_DELETE_MEDIAFILE) {
            F_print_error('WARNING', $l['m_authorization_denied']);
            break;
        }
        if (!F_isAuthorizedDir($dir, $root_dir, $authdirs)) {
            F_print_error('WARNING', $l['m_authorization_denied']);
            break;
        }
        if ($forcedelete == $l['w_delete']) {
            // check if this record is used (test_log)
            if (F_isUsedMediaFile($file)) {
                F_print_error('WARNING', $l['m_used_file']);
            } else {
                if (F_deleteMediaFile($file)) {
                    $file = '';
                    F_print_error('MESSAGE', $l['m_deleted']);
                } else {
                    F_print_error('ERROR', $l['m_delete_file_error']);
                }
            }
        }
        break;
    }

    case 'rename':{
        F_stripslashes_formfields();
        if ($_SESSION['session_user_level'] < K_AUTH_RENAME_MEDIAFILE) {
            F_print_error('WARNING', $l['m_authorization_denied']);
            break;
        }
        if (!F_isAuthorizedDir($dir, $root_dir, $authdirs)) {
            F_print_error('WARNING', $l['m_authorization_denied']);
            break;
        }
        // check if this record is used (test_log)
        if (F_file_exists($dir.$_REQUEST['newname'])) {
            F_print_error('WARNING', $l['m_file_already_exist']);
        } elseif (F_isUsedMediaFile($file)) {
            F_print_error('WARNING', $l['m_used_file']);
        } elseif (isset($_REQUEST['newname'])) {
            if (F_renameMediaFile($file, $dir.$_REQUEST['newname'])) {
                $file = $dir.$_REQUEST['newname'];
                F_print_error('MESSAGE', $l['m_file_renamed']);
            } else {
                F_print_error('ERROR', $l['m_file_rename_error']);
            }
        }
        break;
    }

    case 'newdir':{
        F_stripslashes_formfields();
        if ($_SESSION['session_user_level'] < K_AUTH_ADMIN_DIRS) {
            F_print_error('WARNING', $l['m_authorization_denied']);
            break;
        }
        if (!F_isAuthorizedDir($dir, $root_dir, $authdirs)) {
            F_print_error('WARNING', $l['m_authorization_denied']);
            break;
        }
        // check if this record is used (test_log)
        if (F_file_exists($dir.$_REQUEST['newdirname'])) {
            F_print_error('WARNING', $l['m_file_already_exist']);
        } elseif (isset($_REQUEST['newdirname'])) {
            if (F_createMediaDir($dir.$_REQUEST['newdirname'])) {
                $dir = $dir.$_REQUEST['newdirname'].'/';
                F_print_error('MESSAGE', $l['m_directory_created']);
            } else {
                F_print_error('ERROR', $l['m_directory_create_error']);
            }
        }
        break;
    }

    case 'deldir':{
        F_stripslashes_formfields(); // Delete
        if ($_SESSION['session_user_level'] < K_AUTH_ADMIN_DIRS) {
            F_print_error('WARNING', $l['m_authorization_denied']);
            break;
        }
        if (!F_isAuthorizedDir($dir, $root_dir, $authdirs)) {
            F_print_error('WARNING', $l['m_authorization_denied']);
            break;
        }
        if (F_deleteMediaDir($dir)) {
            $dir = $root_dir;
            F_print_error('MESSAGE', $l['m_deleted']);
        } else {
            F_print_error('ERROR', $l['m_delete_file_error']);
        }
        break;
    }

    default: {
        break;
    }
} //end of switch


echo '<div class="container">'.K_NEWLINE;

echo '<div class="contentbox">'.K_NEWLINE;
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_filemanager">'.K_NEWLINE;
echo '<div>'.K_NEWLINE;

// current dir
echo '<input type="hidden" name="d" id="d" value="'.$dir.'" />'.K_NEWLINE;

echo '<fieldset>'.K_NEWLINE;
echo '<legend title="'.$l['w_action'].'">'.$l['w_action'].'</legend>'.K_NEWLINE;

if (!empty($file)) {
    // file mode
    // preview
    $filedata = F_getFileInfo($file);
    $w = 500;
    $h = 250;
    echo F_objects_replacement($filedata['tcename'], $filedata['extension'], 0, 0, $l['w_preview'], $w, $h);
    echo '<br />'.K_NEWLINE;
    // display basic info
    echo '<span style="font-size:80%;color:#333333;">'.$w.' x '.$h.' px ( '.F_formatFileSize($filedata['size']).' ) '.$filedata['lastmod'].'</span>';
    echo '<br />'.K_NEWLINE;
    // action buttons
    echo '<input type="hidden" name="file" id="file" value="'.$file.'" />'.K_NEWLINE;
    echo '<input type="text" name="newname" id="newname" value="'.basename($file).'" size="40" maxlength="255" title="'.$l['w_name'].'" />'.K_NEWLINE;
    if ($_SESSION['session_user_level'] >= K_AUTH_RENAME_MEDIAFILE) {
        F_submit_button('rename', $l['w_rename'], $l['w_rename']);
    }
    if ($_SESSION['session_user_level'] >= K_AUTH_DELETE_MEDIAFILE) {
        F_submit_button('delete', $l['w_delete'], $l['w_delete']);
    }
} else {
    // upload a new file
    echo '<label for="userfile">'.$l['w_upload_file'].'</label>'.K_NEWLINE;
    echo '<input type="hidden" name="MAX_FILE_SIZE" value="'.K_MAX_UPLOAD_SIZE.'" />'.K_NEWLINE;
    echo '<input type="file" name="userfile" id="userfile" size="20" title="'.$l['h_upload_file'].'" />'.K_NEWLINE;
    echo '<input type="submit" name="sendfile" id="sendfile" value="'.$l['w_upload'].'" title="'.$l['h_upload_file'].'" />'.K_NEWLINE;
}
echo '</fieldset>'.K_NEWLINE;

// change view mode
echo '<div style="text-align:'.($l['a_meta_dir']=='ltr'?'right':'left').';font-size:75%;">';
if ($viewmode) {
    // table mode
    echo '<label for="viewmodev">'.$l['w_mode'].': </label>';
    F_submit_button('viewmodev', $l['w_visual'], $l['w_mode']);
} else {
    // visual mode
    echo '<label for="viewmodet">'.$l['w_mode'].': </label>';
    F_submit_button('viewmodet', $l['w_table'], $l['w_mode']);
}
echo '</div>'.K_NEWLINE;

// directory link path
echo '<br />'.K_NEWLINE;
echo '<strong>'.$l['w_position'].': '.F_getMediaDirPathLink($dir, $viewmode).'</strong>';

if ($_SESSION['session_user_level'] >= K_AUTH_ADMIN_DIRS) {
    // directory mode
    echo ' <input type="text" name="newdirname" id="newdirname" value="" size="15" maxlength="255" title="'.$l['w_new_directory'].'" />'.K_NEWLINE;
    F_submit_button('newdir', $l['w_create_directory'], $l['w_new_directory']);
    if (count(scandir($dir)) <= 2) {
        F_submit_button('deldir', $l['w_delete'], $l['w_delete']);
    }
}

echo '<br />'.K_NEWLINE;

// list files
if ($viewmode) {
    // table mode
    echo F_getDirTable($dir, basename($file), '', $root_dir, $authdirs);
} else {
    // visual mode
    echo F_getDirVisualTable($dir, basename($file), '', $root_dir, $authdirs);
}

echo '</div>'.K_NEWLINE;
echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;


echo '<div class="pagehelp">'.$l['hp_filemanager'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
