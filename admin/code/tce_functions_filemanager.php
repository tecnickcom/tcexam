<?php
//============================================================+
// File name   : tce_functions_filemanager.php
// Begin       : 2010-09-20
// Last Update : 2013-04-12
//
// Description : Functions for TCExam file manager.
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
 * Functions for TCExam file manager.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2010-09-20
 */

/**
 * Delete the selected media file
 * @author Nicola Asuni
 * @param $filename (string) the file name
 * @return true in case of success, false otherwise
 */
function F_deleteMediaFile($filename)
{
    require_once('../config/tce_config.php');
    if ($_SESSION['session_user_level'] < K_AUTH_DELETE_MEDIAFILE) {
        // insufficient user level
        return false;
    }
    $allowed_extensions = unserialize(K_ALLOWED_UPLOAD_EXTENSIONS);
    $path_parts = pathinfo($filename);
    if ((strpos($path_parts['dirname'].'/', K_PATH_CACHE) !== false)
        and in_array(strtolower($path_parts['extension']), $allowed_extensions)
        and (strpos($filename.'/', K_PATH_LANG_CACHE) === false)
        and (strpos($filename.'/', K_PATH_BACKUP) === false)) {
        return unlink($filename);
    }
    return false;
}

/**
 * Rename the selected media file
 * @author Nicola Asuni
 * @param $filename (string) old file name
 * @param $newname (string) new file name
 * @return true in case of success, false otherwise
 */
function F_renameMediaFile($filename, $newname)
{
    require_once('../config/tce_config.php');
    if ($_SESSION['session_user_level'] < K_AUTH_RENAME_MEDIAFILE) {
        // insufficient user level
        return false;
    }
    $allowed_extensions = unserialize(K_ALLOWED_UPLOAD_EXTENSIONS);
    $path_parts = pathinfo($filename);
    $path_parts_new = pathinfo($newname);
    if ((strpos($path_parts['dirname'].'/', K_PATH_CACHE) !== false)
        and in_array(strtolower($path_parts['extension']), $allowed_extensions)
        and (strpos($filename.'/', K_PATH_LANG_CACHE) === false)
        and (strpos($filename.'/', K_PATH_BACKUP) === false)
        and (strpos($path_parts_new['dirname'].'/', K_PATH_CACHE) !== false)
        and in_array(strtolower($path_parts_new['extension']), $allowed_extensions)
        and (strpos($newname.'/', K_PATH_LANG_CACHE) === false)
        and (strpos($newname.'/', K_PATH_BACKUP) === false)) {
        return rename($filename, $newname);
    }
    return false;
}

/**
 * Create a new media directory inside the cache
 * @author Nicola Asuni
 * @param $dirname (string) the directory name
 * @return true in case of success, false otherwise
 */
function F_createMediaDir($dirname)
{
    require_once('../config/tce_config.php');
    if ($_SESSION['session_user_level'] < K_AUTH_ADMIN_DIRS) {
        // insufficient user level
        return false;
    }
    if (strpos($dirname.'/', K_PATH_CACHE) !== false) {
        $oldumask = @umask(0);
        $ret = @mkdir($dirname, 0744, false);
        @umask($oldumask);
        return $ret;
    }
    return false;
}

/**
 * Delete the specified media directory
 * @author Nicola Asuni
 * @param $dirname (string) the directory name
 * @return true in case of success, false otherwise
 */
function F_deleteMediaDir($dirname)
{
    require_once('../config/tce_config.php');
    if ($_SESSION['session_user_level'] < K_AUTH_ADMIN_DIRS) {
        // insufficient user level
        return false;
    }
    if ((strpos($dirname.'/', K_PATH_CACHE) !== false) and (count(scandir($dirname)) <= 2)) {
        return @rmdir($dirname);
    }
    return false;
}

/**
 * Get file information
 * @author Nicola Asuni
 * @param $file (string) the file name
 * @return associative array containing file info or false in case of error
 */
function F_getFileInfo($file)
{
    require_once('../config/tce_config.php');
    $info = pathinfo($file);
    $info['dir'] = is_dir($file);
    $info['lastmod'] = date("Y-m-d H:i:s", @filemtime($file));
    $info['owner'] = @fileowner($file);
    $info['perms'] = @fileperms($file);
    if ($info['dir']) {
        $info['aperms'] = 'd';
    } else {
        $info['aperms'] = '-';
    }
    $info['aperms'] .= ($info['perms'] & 00400) ? 'r' : '-';
    $info['aperms'] .= ($info['perms'] & 00200) ? 'w' : '-';
    $info['aperms'] .= ($info['perms'] & 00100) ? 'x' : '-';
    $info['aperms'] .= ($info['perms'] & 00040) ? 'r' : '-';
    $info['aperms'] .= ($info['perms'] & 00020) ? 'w' : '-';
    $info['aperms'] .= ($info['perms'] & 00010) ? 'x' : '-';
    $info['aperms'] .= ($info['perms'] & 00004) ? 'r' : '-';
    $info['aperms'] .= ($info['perms'] & 00002) ? 'w' : '-';
    $info['aperms'] .= ($info['perms'] & 00001) ? 'x' : '-';
    $info['size'] = @filesize($file);
    $info['link'] = is_link($file);
    if ($info['link']) {
        $info['linkto'] = readlink($file);
    }
    if (!isset($info['extension'])) {
        $info['extension'] = '';
    }
    $info['tcefile'] = substr($file, strlen(K_PATH_CACHE));
    $info['tcename'] = substr($info['tcefile'], 0, -(strlen($info['extension']) + 1));
    return $info;
}

/**
 * Return a formatted file size
 * @author Nicola Asuni
 * @param $size (int) size in bytes
 * @return string formatted size
 */
function F_formatFileSize($size)
{
    $out = ''; // string to be returned
    $mult = array('B ', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'); // multipliers
    if ($size == 0) {
        $out = '0';
    } else {
        $i = floor(log($size, 1024));
        $out .= round(($size / pow(1024, $i)), $i > 1 ? 2 : 0);
        $out .= ' '.$mult[$i];
    }
    return $out;
}

/**
 * Get an html string containing active path of the specified directory with links to subdirectories.
 * @author Nicola Asuni
 * @param $dirpath (string) the directory path
 * @param $viewmode (boolean) true=table, false=visual
 * @return an html string
 */
function F_getMediaDirPathLink($dirpath, $viewmode = true)
{
    global $l, $db;
    require_once('../config/tce_config.php');
    $mode = intval($viewmode);
    $out = ''; //string to be returned
    // write root link
    $out .= '<a href="'.$_SERVER['SCRIPT_NAME'].'?d='.urlencode(K_PATH_CACHE).'&amp;v='.$mode.'" title="CACHE ROOT">[CACHE]</a> /';
    // conform windows-style directories
    $dirpath = str_replace("\\", '/', $dirpath); // Windows compatibility
    // remove cache root from path
    $dirpath = substr($dirpath, strlen(K_PATH_CACHE));
    if ($dirpath !== false) {
        // get subdirs
        $dirs = preg_split('/[\/]+/', $dirpath, -1, PREG_SPLIT_NO_EMPTY);
        $current_dir = K_PATH_CACHE;
        foreach ($dirs as $dir) {
            $current_dir .= $dir.'/';
            $out .= ' <a href="'.$_SERVER['SCRIPT_NAME'].'?d='.urlencode($current_dir).'&amp;v='.$mode.'" title="'.$l['w_change_dir'].'">'.$dir.'</a> /';
        }
    }
    return $out;
}

/**
 * Get an associative array of directories and folder inside the specified dir.
 * @author Nicola Asuni
 * @param $dir (string) the starting directory path
 * @param $rootdir (string) the user root dir.
 * @param $authdirs (string) regular expression containing the authorized dirs.
 * @return an associative array containing sorted 'dirs' and 'files'
 */
function F_getDirFiles($dir, $rootdir = K_PATH_CACHE, $authdirs = '')
{
    $data['dirs'] = array();
    $data['files'] = array();
    // open dir
    $dirhdl = @opendir($dir);
    if ($dirhdl === false) {
        return $data;
    }
    while ($file = readdir($dirhdl)) {
        if (($file != '.') and ($file != '..')) {
            $filename = $dir.$file;
            if (F_isAuthorizedDir($filename.'/', $rootdir, $authdirs)) {
                if (is_dir($filename)) {
                    if ((strpos($filename.'/', K_PATH_LANG_CACHE) === false) and (strpos($filename.'/', K_PATH_BACKUP) === false)) {
                        $data['dirs'][] = $filename;
                    }
                } else {
                    $data['files'][] = $filename;
                }
            }
        }
    }
    // sort files alphabetically
    natcasesort($data['dirs']);
    natcasesort($data['files']);
    return $data;
}

/**
 * Return true if the file is used on question or answer descriptions
 * @author Nicola Asuni
 * @param $file (string) the file to search
 * @return true if the file is used, false otherwise
 */
function F_isUsedMediaFile($file)
{
    global $l, $db;
    require_once('../config/tce_config.php');
    // remove cache root from file path
    $file = substr($file, strlen(K_PATH_CACHE));
    // search on questions
    $sql = 'SELECT question_id FROM '.K_TABLE_QUESTIONS.' WHERE question_description LIKE \'%'.$file.'[/object%\' OR question_explanation LIKE \'%'.$file.'[/object%\' LIMIT 1';
    if ($r = F_db_query($sql, $db)) {
        if ($m = F_db_fetch_array($r)) {
            return true;
        }
    } else {
        F_display_db_error();
    }
    // search on answers
    $sql = 'SELECT answer_id FROM '.K_TABLE_ANSWERS.' WHERE answer_description LIKE \'%'.$file.'[/object%\' OR answer_explanation LIKE \'%'.$file.'[/object%\' LIMIT 1';
    if ($r = F_db_query($sql, $db)) {
        if ($m = F_db_fetch_array($r)) {
            return true;
        }
    } else {
        F_display_db_error();
    }
    return false;
}

/**
 * Get an html table containing files and subdirs
 * @author Nicola Asuni
 * @param $dir (string) the starting directory path
 * @param $selected (string) the selected file
 * @param $params (string) additional parameters to add on links
 * @param $rootdir (string) the user root dir.
 * @param $authdirs (string) regular expression containing the authorized dirs.
 * @return html table
 */
function F_getDirTable($dir, $selected = '', $params = '', $rootdir = K_PATH_CACHE, $authdirs = '')
{
    global $l;
    require_once('../config/tce_config.php');
    $allowed_extensions = unserialize(K_ALLOWED_UPLOAD_EXTENSIONS);
    $out = ''; // html string to be returned
    $out .= '<table class="filemanager">'.K_NEWLINE;
    // header
    $out .= '<tr>';
    $out .= '<th>'.$l['w_name'].'</th>';
    $out .= '<th>'.$l['w_size'].'</th>';
    $out .= '<th title="'.$l['w_datetime_format'].'">'.$l['w_date'].'</th>';
    $out .= '<th>'.$l['w_permissions'].'</th>';
    $out .= '</tr>'.K_NEWLINE;
    $data = F_getDirFiles($dir, $rootdir, $authdirs);
    $usrdir = $rootdir.$_SESSION['session_user_id'];
    // dirs
    foreach ($data['dirs'] as $file) {
        $info = F_getFileInfo($file);
        $current_dir = urlencode($dir.$info['basename'].'/');
        if ($file == $usrdir) {
            $out .= '<tr style="background-color:#ddffdd;font-family:monospace;color:#660000;">';
        } else {
            $out .= '<tr style="background-color:#dddddd;font-family:monospace;color:#660000;">';
        }
        $out .= '<td><strong><a href="'.$_SERVER['SCRIPT_NAME'].'?d='.$current_dir.'&amp;v=1'.$params.'" title="'.$l['w_change_dir'].'" style="text-decoration:underline;">'.$info['basename'].'</a></strong></td>';
        $out .= '<td style="text-align:right;">'.F_formatFileSize($info['size']).'</td>';
        $out .= '<td>'.$info['lastmod'].'</td>';
        $out .= '<td>'.$info['aperms'].'</td>';
        $out .= '</tr>'.K_NEWLINE;
    }
    // files
    $current_dir = urlencode($dir);
    foreach ($data['files'] as $file) {
        $info = F_getFileInfo($file);
        if (isset($info['extension']) and in_array(strtolower($info['extension']), $allowed_extensions) and (substr($info['basename'], 0, 6) != 'latex_')) {
            $current_file = urlencode($dir.$info['basename']);
            if ($info['basename'] == $selected) {
                $out .= '<tr style="background-color:#ffffcc;font-family:monospace;">';
            } else {
                $out .= '<tr style="font-family:monospace;">';
            }
            $out .= '<td><a href="'.$_SERVER['SCRIPT_NAME'].'?d='.$current_dir.'&amp;f='.urlencode($current_file).'&amp;v=1'.$params.'" title="'.$l['w_select'].'">'.$info['basename'].'</a></td>';
            $out .= '<td style="text-align:right;">'.F_formatFileSize($info['size']).'</td>';
            //$out .= '<td style="text-align:right;">'.$info['size'].'</td>';
            $out .= '<td>'.$info['lastmod'].'</td>';
            $out .= '<td>'.$info['aperms'].'</td>';
            $out .= '</tr>'.K_NEWLINE;
        }
    }
    $out .= '</table>'.K_NEWLINE;
    return $out;
}

/**
 * Get an html visual list of files and subdirs
 * @author Nicola Asuni
 * @param $dir (string) the starting directory path
 * @param $selected (string) the selected file
 * @param $params (string) additional parameters to add on links
 * @param $rootdir (string) the user root dir.
 * @param $authdirs (string) regular expression containing the authorized dirs.
 * @return html table
 */
function F_getDirVisualTable($dir, $selected = '', $params = '', $rootdir = K_PATH_CACHE, $authdirs = '')
{
    global $l;
    require_once('../config/tce_config.php');
    $imgformats = array('gif', 'jpg', 'jpeg', 'png', 'svg');
    $allowed_extensions = unserialize(K_ALLOWED_UPLOAD_EXTENSIONS);
    $out = ''; // html string to be returned
    $data = F_getDirFiles($dir, $rootdir, $authdirs);
    // dirs
    foreach ($data['dirs'] as $file) {
        $info = F_getFileInfo($file);
        $current_dir = urlencode($dir.$info['basename'].'/');
        $out .= '<table style="float:left;border:none;margin:1px;padding:0;width:158px;background-color:#007fff;">';
        $out .= '<tr style="height:16px;font-family:monospace;font-size:12px;font-weight:bold;color:white;"><th>';
        $filename = $info['basename'];
        if (strlen($filename) > 20) {
            $filename = substr($filename, 0, 20).'...';
        }
        $out .= $filename;
        $out .= '</th></tr>';
        $out .= '<tr style="height:160px;"><td style="text-align:center;vertical-align:middle;background-color:white;">';
        $out .= '<a href="'.$_SERVER['SCRIPT_NAME'].'?d='.$current_dir.'&amp;v=0'.$params.'" title="'.$l['w_change_dir'].' : '.$info['basename'].'" style="text-decoration:underline;"><img src="'.K_PATH_IMAGES.'dir.png" width="50" height="50" alt="'.$l['w_change_dir'].' : '.$info['basename'].'" style="border:none;" /></a>';
        $out .= '</td></tr>';
        $out .= '</table>';
    }
    // files
    $current_dir = urlencode($dir);
    foreach ($data['files'] as $file) {
        $info = F_getFileInfo($file);
        if (isset($info['extension']) and in_array(strtolower($info['extension']), $allowed_extensions) and (substr($info['basename'], 0, 6) != 'latex_')) {
            $current_file = urlencode($dir.$info['basename']);
            if ($info['basename'] == $selected) {
                $bgcolor = '#009900';
            } else {
                $bgcolor = '#333333';
            }
            if (in_array(strtolower($info['extension']), $imgformats)) {
                $w = 150;
                $h = 150;
                $imgicon = F_objects_replacement($info['tcename'], $info['extension'], 0, 0, $l['w_preview'], $w, $h);
            } else {
                $imgicon = '<img src="'.K_PATH_IMAGES.'file.png" width="39" height="50" alt="'.$l['w_select'].' : '.$info['basename'].' ('.F_formatFileSize($info['size']).')'.'" style="border:none;" />';
            }
            $out .= '<table style="float:left;border:none;margin:1px;padding:0;width:158px;background-color:'.$bgcolor.';">';
            $out .= '<tr style="height:16px;font-family:monospace;font-size:12px;color:white;"><th>';
            $filename = $info['basename'];
            if (strlen($filename) > 20) {
                $filename = substr(substr($filename, 0, -(strlen($info['extension']) + 1)), 0, 15).'&rarr;.'.$info['extension'];
            }
            $out .= $filename;
            $out .= '</th></tr>';
            $out .= '<tr style="height:160px;"><td style="text-align:center;vertical-align:middle;background-color:white;">';
            $out .= '<a href="'.$_SERVER['SCRIPT_NAME'].'?d='.$current_dir.'&amp;f='.urlencode($current_file).'&amp;v=0'.$params.'" title="'.$l['w_select'].' : '.$info['basename'].' ('.F_formatFileSize($info['size']).')'.'">'.$imgicon.'</a>';
            $out .= '</td></tr>';
            $out .= '</table>';
        }
    }
    $out .= '<br style="clear:both;" />';
    return $out;
}

/**
 * Returns a regular expression to match authorised directories.
 * @return a regular expression to match authorised directories.
 */
function F_getAuthorizedDirs()
{
    require_once('../config/tce_config.php');
    require_once('../../shared/code/tce_functions_authorization.php');
    if ($_SESSION['session_user_level'] >= K_AUTH_ADMINISTRATOR) {
        return '[^/]*';
    }
    $reg = F_getAuthorizedUsers($_SESSION['session_user_id']);
    return str_replace(',', '|', $reg);
}

/**
 * Returns true if the user is authorized to use the specified directory, false otherwise.
 * @param $dir (string) the directory to check.
 * @param $rootdir (string) the user root dir.
 * @param $authdirs (string) regular expression containing the authorized dirs.
 * @return true if the user is authorized to use the specified directory, false otherwise.
 */
function F_isAuthorizedDir($dir, $rootdir, $authdirs = '')
{
    require_once('../config/tce_config.php');
    if ($_SESSION['session_user_level'] >= K_AUTH_ADMINISTRATOR) {
        return true;
    }
    if (empty($authdirs)) {
        $authdirs = F_getAuthorizedDirs();
    }
    if (preg_match('#^'.$rootdir.'('.$authdirs.')/#', $dir) > 0) {
        return true;
    }
    return false;
}

//============================================================+
// END OF FILE
//============================================================+
