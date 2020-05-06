<?php
//============================================================+
// File name   : tce_edit_backup.php
// Begin       : 2009-04-06
// Last Update : 2020-05-06
//
// Description : Backup and Restore TCExam Database.
//               ONLY FOR POSIX SYSTEMS
//               SOME POSIX COMMANDS ARE HARD-CODED
//               ONLY FOR MySQL and PostgreSQL
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
//    Copyright (C) 2004-2020 Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Backup and Restore TCExam Database (works only on POSIX systems with MySQL or PostgreSQL).
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2010-02-12
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_BACKUP;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_backup_editor'];

require_once('../../shared/code/tce_functions_form.php');

if (isset($_POST['backup'])) {
    $menu_mode = 'backup';
} elseif (isset($_POST['restore'])) {
    $menu_mode = 'restore';
} elseif (isset($_POST['forcerestore'])) {
    $menu_mode = 'forcerestore';
} elseif (isset($_POST['download'])) {
    $menu_mode = 'download';
}

function F_isValidbackupFile($file)
{
    return ((strlen($file) === 35) and (substr($file, -3) === '.gz') and (preg_match('|\.\./|i', $file) === 0) and (preg_match('/[^a-zA-Z0-9\_\-\.]+/i', $file) === 0));
}

// check backup filename
if (!empty($backup_file) and !F_isValidbackupFile($backup_file)) {
	F_print_error('ERROR', 'SECURITY ERROR', true);
}


switch ($menu_mode) { // process submitted data

    case 'restore':{
        if (isset($backup_file) and !empty($backup_file)) {
            F_print_error('WARNING', $l['m_restore_confirm'].': '.$backup_file);
            echo '<div class="confirmbox">'.K_NEWLINE;
            echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_delete">'.K_NEWLINE;
            echo '<div>'.K_NEWLINE;
            echo '<input type="hidden" name="backup_file" id="backup_file" value="'.stripslashes($backup_file).'" />'.K_NEWLINE;
            F_submit_button('forcerestore', $l['w_restore'], $l['h_restore']);
            F_submit_button('cancel', $l['w_cancel'], $l['h_cancel']);
            echo '</div>'.K_NEWLINE;
            echo F_getCSRFTokenField().K_NEWLINE;
            echo '</form>'.K_NEWLINE;
            echo '</div>'.K_NEWLINE;
        }
        break;
    }

    case 'forcerestore':{
        F_stripslashes_formfields(); // Delete specified record
        if ($forcerestore == $l['w_restore']) { //check if delete button has been pushed (redundant check)
            if (isset($backup_file) and !empty($backup_file)) {
                // create a backup of the current database data
                switch (K_DATABASE_TYPE) {
                    case 'POSTGRESQL': {
                        $filename = K_PATH_BACKUP.date('YmdHis').'_tcexam_backup.tar';
                        $command = 'export PGUSER="'.addslashes(K_DATABASE_USER_NAME).'"; export PGPASSWORD="'.addslashes(K_DATABASE_USER_PASSWORD).'"; pg_dump -h'.K_DATABASE_HOST.' -p'.K_DATABASE_PORT.'  -U'.K_DATABASE_USER_NAME.' -Ft '.K_DATABASE_NAME.' | gzip > '.$filename.'.gz';
                        break;
                    }
                    case 'MYSQL':
                    default: {
                        $filename = K_PATH_BACKUP.date('YmdHis').'_tcexam_backup.sql';
                        $command = 'mysqldump --opt -h'.K_DATABASE_HOST.' -P'.K_DATABASE_PORT.' -u'.K_DATABASE_USER_NAME.' -p'.K_DATABASE_USER_PASSWORD.' '.K_DATABASE_NAME.' | gzip > '.$filename.'.gz';
                        break;
                    }
                }
                exec($command);
                // subtring file name for security reason
                $backup_file = substr($backup_file, 0, 35);
                // uncompressed filename (remove .gz extension)
                $sql_backup_file = substr($backup_file, 0, -3);
                $sql_backup_file_esc = escapeshellarg($sql_backup_file);
                // get current dir
                $current_dir = getcwd();
                // change dir
                chdir(K_PATH_BACKUP);
                // uncompress backup archive
                $command = 'gunzip -c '.escapeshellarg($backup_file).' > '.$sql_backup_file_esc.'';
                exec($command);
                // restore SQL file
                switch (K_DATABASE_TYPE) {
                    case 'POSTGRESQL': {
                        $command = 'export PGUSER="'.addslashes(K_DATABASE_USER_NAME).'"; export PGPASSWORD="'.addslashes(K_DATABASE_USER_PASSWORD).'"; pg_restore -c -h'.K_DATABASE_HOST.' -p'.K_DATABASE_PORT.' -U'.K_DATABASE_USER_NAME.' -d'.K_DATABASE_NAME.' -Ft '.$sql_backup_file_esc.'';
                        break;
                    }
                    case 'MYSQL':
                    default: {
                        $command = 'mysql -h'.K_DATABASE_HOST.' -P'.K_DATABASE_PORT.' -u'.K_DATABASE_USER_NAME.' -p'.K_DATABASE_USER_PASSWORD.' '.K_DATABASE_NAME.' < '.$sql_backup_file_esc.'';
                        break;
                    }
                }
                exec($command);
                // delete uncompressed backup
                unlink($sql_backup_file);
                // restore current dir
                chdir($current_dir);
                F_print_error('MESSAGE', $l['m_restore_completed'].': '.$backup_file);
            }
        }
        break;
    }

    case 'backup':{ // backup
        switch (K_DATABASE_TYPE) {
            case 'POSTGRESQL': {
                $filename = K_PATH_BACKUP.date('YmdHis').'_tcexam_backup.tar';
                $command = 'export PGUSER="'.addslashes(K_DATABASE_USER_NAME).'"; export PGPASSWORD="'.addslashes(K_DATABASE_USER_PASSWORD).'"; pg_dump -h'.K_DATABASE_HOST.' -p'.K_DATABASE_PORT.' -U'.K_DATABASE_USER_NAME.' -Ft '.K_DATABASE_NAME.' | gzip > '.$filename.'.gz';
                break;
            }
            case 'MYSQL':
            default: {
                $filename = K_PATH_BACKUP.date('YmdHis').'_tcexam_backup.sql';
                $command = 'mysqldump --opt -h'.K_DATABASE_HOST.' -P'.K_DATABASE_PORT.' -u'.K_DATABASE_USER_NAME.' -p'.K_DATABASE_USER_PASSWORD.' '.K_DATABASE_NAME.' | gzip > '.$filename.'.gz';
                break;
            }
        }
        exec($command);
        F_print_error('MESSAGE', $l['m_backup_completed']);
        break;
    }

    case 'download':{
        if (K_DOWNLOAD_BACKUPS and isset($backup_file) and !empty($backup_file)) {
            $file_to_download = K_PATH_BACKUP.$backup_file;
            // send headers
            header('Content-Description: File Transfer');
            header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
            header('Pragma: public');
            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
            // force download dialog
            header('Content-Type: application/force-download');
            header('Content-Type: application/octet-stream', false);
            header('Content-Type: application/download', false);
            header('Content-Type: application/x-gzip', false);
            // use the Content-Disposition header to supply a recommended filename
            header('Content-Disposition: attachment; filename='.$backup_file.';');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: '.filesize($file_to_download));
            echo file_get_contents($file_to_download);
            exit;
        }
        break;
    }

    default :{
        break;
    }
} //end of switch

require_once('../code/tce_page_header.php');

echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_editor">'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="backup_file">'.$l['w_backup_file'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="backup_file" id="backup_file" size="0">'.K_NEWLINE;

// read directory for backup files.
$handle = opendir(K_PATH_BACKUP);
echo '<option value="">&nbsp;</option>'.K_NEWLINE;
// get backup files
$files_list = array();
while (false !== ($file = readdir($handle))) {
    if (F_isValidbackupFile($file) and is_file(K_PATH_BACKUP.$file)) {
        $files_list[] = $file;
    }
}
closedir($handle);
// sort alphabetically
sort($files_list);
$files_list = array_reverse($files_list);
foreach ($files_list as $file) {
    echo '<option value="'.$file.'">'.$file.'</option>'.K_NEWLINE;
}

echo '</select>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormNoscriptSelect('selectrecord');

echo '<div class="row"><hr /></div>'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;

F_submit_button('backup', $l['w_backup'], $l['h_backup']);
F_submit_button('restore', $l['w_restore'], $l['h_restore']);
if (K_DOWNLOAD_BACKUPS) {
    F_submit_button('download', $l['w_download'], $l['h_download']);
}

echo '</div>'.K_NEWLINE;
echo F_getCSRFTokenField().K_NEWLINE;
echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_edit_backups'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
