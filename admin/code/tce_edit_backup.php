<?php
//============================================================+
// File name   : tce_edit_backup.php
// Begin       : 2009-04-06
// Last Update : 2010-07-14
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
//               Tecnick.com S.r.l.
//               Via della Pace, 11
//               09044 Quartucciu (CA)
//               ITALY
//               www.tecnick.com
//               info@tecnick.com
//
// License:
//    Copyright (C) 2004-2010 Nicola Asuni - Tecnick.com S.r.l.
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
//    Additionally, you can't remove, move or hide the original TCExam logo,
//    copyrights statements and links to Tecnick.com and TCExam websites.
//
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * Backup and Restore TCExam Database (works only on POSIX systems with MySQL or PostgreSQL).
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2010-02-12
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_BACKUP;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_backup_editor'];
require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');

if(isset($_POST['backup'])) {
	$menu_mode = 'backup';
} elseif(isset($_POST['restore'])) {
	$menu_mode = 'restore';
} elseif(isset($_POST['forcerestore'])) {
	$menu_mode = 'forcerestore';
} elseif(isset($_POST['download'])) {
	$menu_mode = 'download';
}

switch($menu_mode) { // process submited data

	case 'restore':{
		if (isset($backup_file) AND !empty($backup_file)) {
			F_print_error('WARNING', $l['m_restore_confirm'].': '.$backup_file);
			?>
			<div class="confirmbox">
			<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data" id="form_delete">
			<div>
			<input type="hidden" name="backup_file" id="backup_file" value="<?php echo stripslashes($backup_file); ?>" />
			<?php
			F_submit_button('forcerestore', $l['w_restore'], $l['h_restore']);
			F_submit_button('cancel', $l['w_cancel'], $l['h_cancel']);
			?>
			</div>
			</form>
			</div>
			<?php

		}
		break;
	}

	case 'forcerestore':{
		F_stripslashes_formfields(); // Delete specified record
		if($forcerestore == $l['w_restore']) { //check if delete button has been pushed (redundant check)
			if (isset($backup_file) AND !empty($backup_file)) {
				// create a backup of the current database data
				switch (K_DATABASE_TYPE) {
					case 'POSTGRESQL': {
						$filename = K_PATH_BACKUP.date('YmdHis').'_tcexam_backup.tar';
						$command = 'export PGUSER="'.addslashes(K_DATABASE_USER_NAME).'"; export PGPASSWORD="'.addslashes(K_DATABASE_USER_PASSWORD).'"; pg_dump -h'.K_DATABASE_HOST.' -p'.K_DATABASE_PORT.'  -U'.K_DATABASE_USER_NAME.' -w -Ft '.K_DATABASE_NAME.' | gzip > '.$filename.'.gz';
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
				// get current dir
				$current_dir = getcwd();
				// change dir
				chdir(K_PATH_BACKUP);
				// uncompress backup archive
				$command = 'gunzip -c '.$backup_file.' > '.$sql_backup_file.'';
				exec($command);
				// restore SQL file
				switch (K_DATABASE_TYPE) {
					case 'POSTGRESQL': {
						$command = 'export PGUSER="'.addslashes(K_DATABASE_USER_NAME).'"; export PGPASSWORD="'.addslashes(K_DATABASE_USER_PASSWORD).'"; pg_restore -c -h'.K_DATABASE_HOST.' -p'.K_DATABASE_PORT.' -U'.K_DATABASE_USER_NAME.' -w -d'.K_DATABASE_NAME.' -Ft '.$sql_backup_file.'';
						break;
					}
					case 'MYSQL':
					default: {
						$command = 'mysql -h'.K_DATABASE_HOST.' -P'.K_DATABASE_PORT.' -u'.K_DATABASE_USER_NAME.' -p'.K_DATABASE_USER_PASSWORD.' '.K_DATABASE_NAME.' < '.$sql_backup_file.'';
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
		if (K_DOWNLOAD_BACKUPS AND isset($backup_file) AND !empty($backup_file)) {
			if ((preg_match('/[^a-zA-Z0-9\_\-\.]+/i', $backup_file) > 0) OR (strlen($backup_file) != 35) OR (substr($backup_file, -3) != '.gz')) {
				// ERROR
				F_print_error('ERROR', 'SECURITY ERROR');
			} else {
				// open a new window to send the file (requires JavaScript)
				echo '<script language="JavaScript" type="text/javascript">'.K_NEWLINE;
				echo '//<![CDATA['.K_NEWLINE;
				echo 'dw=window.open(\'tce_download.php?t=b&f='.urlencode($backup_file).'\', \'dw\', \'dependent,height=1,width=1,menubar=no,resizable=no,scrollbars=no,status=no,toolbar=no\');'.K_NEWLINE;
				echo 'setInterval(\'dw.close()\', 5000);'.K_NEWLINE;
				echo '//]]>'.K_NEWLINE;
				echo '</script>'.K_NEWLINE;
			}
		}
		break;
	}

	default :{
		break;
	}

} //end of switch

?>

<div class="container">

<div class="tceformbox">
<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data" id="form_editor">

<div class="row">
<span class="label">
<label for="backup_file"><?php echo $l['w_backup_file']; ?></label>
</span>
<span class="formw">
<select name="backup_file" id="backup_file" size="0">
<?php
// read directory for backup files.
$handle = opendir(K_PATH_BACKUP);
echo '<option value="">&nbsp;</option>'.K_NEWLINE;
// get backup files
$files_list = array();
while (false !== ($file = readdir($handle))) {
	if (is_file(K_PATH_BACKUP.$file)) {
		$files_list[] = $file;
	}
}
closedir($handle);
// sort alphabetically
sort($files_list);
$files_list = array_reverse($files_list);
foreach($files_list as $file) {
	echo '<option value="'.$file.'">'.$file.'</option>'.K_NEWLINE;
}
?>
</select>
</span>
</div>

<noscript>
<div class="row">
<span class="label">&nbsp;</span>
<span class="formw">
<input type="submit" name="selectrecord" id="selectrecord" value="<?php echo $l['w_select']; ?>" />
</span>
</div>
</noscript>

<div class="row"><hr /></div>

<div class="row">
<?php
F_submit_button('backup', $l['w_backup'], $l['h_backup']);
F_submit_button('restore', $l['w_restore'], $l['h_restore']);
if (K_DOWNLOAD_BACKUPS) {
	F_submit_button('download', $l['w_download'], $l['h_download']);
}
?>
</div>

</form>
</div>
<?php
echo '<div class="pagehelp">'.$l['hp_edit_backups'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
?>
