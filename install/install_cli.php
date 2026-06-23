<?php
//============================================================+
// File name   : install_cli.php
// Begin       : 2026-06-23
//
// Description : Non-interactive (command-line) TCExam installer.
//
//   Generates the configuration files and loads the database schema and
//   default data from environment variables, without the interactive web
//   form. It is idempotent and safe to run on every container start:
//
//     * configuration is written only when it is not already present, so a
//       persisted config (and its generated K_RANDOM_SECURITY) is preserved;
//     * the database schema/data are loaded only when the application tables
//       are absent, so existing data is never overwritten.
//
//   This powers the zero-touch Docker quick start (see docker/entrypoint.sh):
//   `make up` yields a ready-to-use instance with no browser install step.
//   It can also be used for scripted/headless installs on any host.
//
//   Settings are read from these environment variables (defaults in brackets):
//
//     TCEXAM_DB_TYPE        MYSQL | POSTGRESQL | ORACLE | MYSQLDEPRECATED [MYSQL]
//     TCEXAM_DB_HOST        database host                                [localhost]
//     TCEXAM_DB_PORT        database port            [3306 / 5432 / 1521 by type]
//     TCEXAM_DB_NAME        database name                                [tcexam]
//     TCEXAM_DB_USER        database user                                [root]
//     TCEXAM_DB_PASSWORD    database password                            ['']
//     TCEXAM_TABLE_PREFIX   table-name prefix                            [tce_]
//     TCEXAM_PATH_HOST      host URL          [http://localhost:<STANDARD_PORT>]
//     TCEXAM_PATH_TCEXAM    relative URL                                 [/]
//     TCEXAM_PATH_MAIN      absolute install path        [parent of install/]
//     TCEXAM_STANDARD_PORT  HTTP/HTTPS port                              [80]
//     TCEXAM_DB_CREATE      "1" => attempt CREATE DATABASE first         [0]
//     TCEXAM_DB_WAIT        seconds to wait for the database             [60]
//
//   Flags:  --reconfig  rewrite the configuration files even if present
//                       (regenerates K_RANDOM_SECURITY if it is still the
//                       shipped placeholder).
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

// Refuse to run from the web: this is a command-line only tool.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("install_cli.php is a command-line tool and cannot be run over the web.\n");
}

error_reporting(E_ALL);

// PHP 8.1+ makes mysqli throw exceptions on error by default. The legacy Database Abstraction
// Layer and the install functions are written against the historical "return false on failure"
// contract (so a bad connection or a failed query can be reported gracefully and retried). Restore
// that contract for the install flow.
if (function_exists('mysqli_report')) {
    mysqli_report(MYSQLI_REPORT_OFF);
}

// Resolve relative requires/paths used by tce_functions_install.php against install/.
chdir(__DIR__);

require_once(__DIR__.'/tce_functions_install.php');

$argv = isset($argv) ? $argv : array();
$reconfig = in_array('--reconfig', $argv, true);

/**
 * Read an environment variable, returning $default when unset or empty.
 */
function F_cli_env($name, $default) {
    $val = getenv($name);
    if (($val === false) OR ($val === '')) {
        return $default;
    }
    return $val;
}

function F_cli_log($msg) {
    fwrite(STDOUT, '[tcexam-install] '.$msg."\n");
}

function F_cli_err($msg) {
    fwrite(STDERR, '[tcexam-install] ERROR: '.$msg."\n");
}

// --- collect settings from the environment -----------------------------------------------------
$db_type = strtoupper(F_cli_env('TCEXAM_DB_TYPE', 'MYSQL'));
$valid_types = array('MYSQL', 'POSTGRESQL', 'ORACLE', 'MYSQLDEPRECATED');
if (!in_array($db_type, $valid_types, true)) {
    F_cli_err('unsupported TCEXAM_DB_TYPE "'.$db_type.'" (expected one of: '.implode(', ', $valid_types).')');
    exit(2);
}

switch ($db_type) {
    case 'POSTGRESQL': $default_port = '5432'; break;
    case 'ORACLE':     $default_port = '1521'; break;
    default:           $default_port = '3306';
}

$db_host       = F_cli_env('TCEXAM_DB_HOST', 'localhost');
$db_port       = F_cli_env('TCEXAM_DB_PORT', $default_port);
$db_user       = F_cli_env('TCEXAM_DB_USER', 'root');
$db_password   = F_cli_env('TCEXAM_DB_PASSWORD', '');
$db_name       = F_cli_env('TCEXAM_DB_NAME', 'tcexam');
$table_prefix  = F_cli_env('TCEXAM_TABLE_PREFIX', 'tce_');
$standard_port = F_cli_env('TCEXAM_STANDARD_PORT', '80');
$path_host     = F_cli_env('TCEXAM_PATH_HOST', 'http://localhost:'.$standard_port);
$path_tcexam   = F_cli_env('TCEXAM_PATH_TCEXAM', '/');
$path_main     = rtrim(str_replace('\\', '/', F_cli_env('TCEXAM_PATH_MAIN', dirname(__DIR__))), '/').'/';
$create_db     = (F_cli_env('TCEXAM_DB_CREATE', '0') === '1');
$db_wait       = (int) F_cli_env('TCEXAM_DB_WAIT', '60');

$progress_log  = __DIR__.'/install.log';
$config_marker = __DIR__.'/../shared/config/tce_db_config.php';

F_cli_log('TCExam non-interactive installer starting ('.$db_type.' on '.$db_host.':'.$db_port.', db "'.$db_name.'").');

// --- 1) configuration files (no database needed) -----------------------------------------------
if ($reconfig OR !is_file($config_marker)) {
    F_cli_log($reconfig ? 'rewriting configuration files (--reconfig)…' : 'writing configuration files…');
    ob_start();
    $ok = F_update_config_files(
        $db_type, $db_host, $db_port, $db_user, $db_password, $db_name, $table_prefix,
        $path_host, $path_tcexam, $path_main, $standard_port, $progress_log
    );
    $out = ob_get_clean();
    if (!$ok) {
        F_cli_err('configuration generation failed:');
        fwrite(STDERR, trim(strip_tags($out))."\n");
        exit(3);
    }
    F_cli_log('configuration files ready.');
} else {
    F_cli_log('configuration already present — preserving it (use --reconfig to overwrite).');
}

// --- 2) database schema and default data -------------------------------------------------------
// Load the Database Abstraction Layer for the selected database type.
if (!defined('K_DATABASE_TYPE')) {
    define('K_DATABASE_TYPE', $db_type);
}
switch (K_DATABASE_TYPE) {
    case 'ORACLE':          require_once('../shared/code/tce_db_dal_oracle.php'); break;
    case 'POSTGRESQL':      require_once('../shared/code/tce_db_dal_postgresql.php'); break;
    case 'MYSQLDEPRECATED': require_once('../shared/code/tce_db_dal_mysql.php'); break;
    default:                require_once('../shared/code/tce_db_dal_mysqli.php');
}

// Connect (optionally creating the database), waiting for the server to become reachable.
$deadline = time() + max(0, $db_wait);
$db = false;
$last_out = '';
do {
    ob_start();
    $db = F_create_database($db_type, $db_host, $db_port, $db_user, $db_password, $db_name, $table_prefix, false, $create_db);
    $last_out = ob_get_clean();
    if ($db) {
        break;
    }
    if (time() >= $deadline) {
        break;
    }
    F_cli_log('waiting for the database to become available…');
    sleep(2);
} while (true);

if (!$db) {
    F_cli_err('could not connect to the database:');
    fwrite(STDERR, trim(strip_tags($last_out))."\n");
    exit(4);
}

// Idempotency: if the application tables already exist, do not reload the schema/data.
$already_installed = false;
$probe = @F_db_query('SELECT 1 FROM '.$table_prefix.'users', $db);
if ($probe !== false) {
    $already_installed = true;
}

if ($already_installed) {
    F_cli_log('database already initialised (table "'.$table_prefix.'users" present) — skipping schema load.');
} else {
    F_cli_log('loading database schema…');
    ob_start();
    $ok_schema = F_execute_sql_queries($db, strtolower($db_type).'_db_structure.sql', 'tce_', $table_prefix, $progress_log);
    ob_end_clean();
    if (!$ok_schema) {
        F_cli_err('database schema load failed: '.F_db_error($db));
        exit(5);
    }
    F_cli_log('loading default data…');
    ob_start();
    $ok_data = F_execute_sql_queries($db, 'db_data.sql', 'tce_', $table_prefix, $progress_log);
    ob_end_clean();
    if (!$ok_data) {
        F_cli_err('default data load failed: '.F_db_error($db));
        exit(6);
    }
    F_cli_log('database initialised with the default data.');
}

@F_db_close($db);

F_cli_log('installation complete. Sign in at '.rtrim($path_host, '/').rtrim($path_tcexam, '/').'/admin/code/ (default: admin / 1234 — change it immediately).');
exit(0);

//============================================================+
// END OF FILE
//============================================================+
