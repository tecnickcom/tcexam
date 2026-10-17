<?php

//============================================================+
// File name   : tce_functions_upload.php
// Begin       : 2001-11-19
// Last Update : 2023-11-30
//
// Description : Upload functions.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

/**
 * @file
 * Functions to upload files.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2001-11-19
 */

require_once __DIR__ . '/tce_functions_svg_check.php';

/**
 * Returns the file extensions that browsers can render as active documents (markup or script).
 * These are never accepted for media uploads, regardless of K_ALLOWED_UPLOAD_EXTENSIONS,
 * except 'svg', whose content must pass F_is_safe_svg_content().
 *
 * @return list<string> lowercase extensions
 */
function F_get_active_content_extensions(): array
{
    return [
        'svg',
        'svgz',
        'xml',
        'xsl',
        'xslt',
        'xht',
        'xhtml',
        'htm',
        'html',
        'shtml',
        'mht',
        'mhtml',
        'js',
        'mjs',
        'swf',
    ];
}

/**
 * Returns the file extensions that web servers can execute as scripts.
 * These are never accepted for any upload, regardless of K_ALLOWED_UPLOAD_EXTENSIONS.
 *
 * @return list<string> lowercase extensions
 */
function F_get_server_script_extensions(): array
{
    return [
        'php',
        'php3',
        'php4',
        'php5',
        'php7',
        'php8',
        'pht',
        'phtml',
        'phar',
        'phps',
        'cgi',
        'pl',
        'py',
        'sh',
        'asp',
        'aspx',
        'jsp',
        'htaccess',
        'htpasswd',
    ];
}

/**
 * Returns the detected MIME types that browsers can render as active documents.
 * Any type ending in '+xml' is also treated as active.
 *
 * @return list<string> lowercase MIME types
 */
function F_get_active_content_mime_types(): array
{
    return [
        'text/html',
        'text/xml',
        'application/xml',
        'application/xhtml+xml',
        'image/svg+xml',
        'text/javascript',
        'application/javascript',
        'application/x-javascript',
        'application/x-shockwave-flash',
        'text/x-php',
        'application/x-php',
        'application/x-httpd-php',
    ];
}

/**
 * Returns a safe file name for an uploaded file.
 * White spaces become underscores, characters outside [a-zA-Z0-9_.-] are removed and
 * all dots except the extension separator become underscores.
 *
 * @param string $name Original file name.
 *
 * @return string Sanitized file name, or an empty string if the name is not usable.
 */
function F_sanitize_upload_filename(string $name): string
{
    $filename = (string) preg_replace('/[\s]/', '_', $name);
    $filename = (string) preg_replace('/[^a-zA-Z0-9_\.\-]/', '', $filename);
    $pos = strrpos($filename, '.');
    if ($pos === false || $pos === 0 || $pos === (strlen($filename) - 1)) {
        // no extension, empty extension or hidden file
        return '';
    }

    return str_replace('.', '_', substr($filename, 0, $pos)) . substr($filename, $pos);
}

/**
 * Returns the lowercase extensions listed in K_ALLOWED_UPLOAD_EXTENSIONS.
 *
 * @return list<string> lowercase extensions, empty if the constant is missing or invalid
 */
function F_get_allowed_upload_extensions(): array
{
    if (!defined('K_ALLOWED_UPLOAD_EXTENSIONS')) {
        return [];
    }

    $allowed_extensions = unserialize((string) K_ALLOWED_UPLOAD_EXTENSIONS, ['allowed_classes' => false]);
    if (!is_array($allowed_extensions)) {
        return [];
    }

    return array_values(array_map(static fn(mixed $ext): string => strtolower((string) $ext), $allowed_extensions));
}

/**
 * Check if the uploaded file extension is allowed.
 * The extension must be listed in K_ALLOWED_UPLOAD_EXTENSIONS and must not be a server script.
 * When $extensions is null (media uploads), active content extensions other than 'svg' are also
 * rejected; otherwise the extension must be listed in $extensions.
 * @author Nicola Asuni
 * @since 2001-11-19
 *
 * @param string            $filename   File name.
 * @param list<string>|null $extensions Extensions accepted by the calling context, or null for media uploads.
 *
 * @return bool True if the file type is allowed.
 */
function F_is_allowed_upload(string $filename, ?array $extensions = null): bool
{
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (
        $extension === ''
        || in_array($extension, F_get_server_script_extensions(), true)
        || !in_array($extension, F_get_allowed_upload_extensions(), true)
    ) {
        return false;
    }

    if ($extensions === null) {
        return $extension === 'svg' || !in_array($extension, F_get_active_content_extensions(), true);
    }

    return in_array($extension, $extensions, true);
}

/**
 * Check that the content of a media file cannot be rendered as an active document.
 * SVG files must pass F_is_safe_svg_content(). Other files are rejected when detected as markup
 * or script, when a raster image does not decode as the declared type, or when the leading bytes
 * contain markup tags.
 *
 * @param string $filepath  Path of the file to check.
 * @param string $extension Declared file extension.
 *
 * @return bool True if the content is accepted.
 */
function F_is_safe_media_content(string $filepath, string $extension): bool
{
    if (!is_file($filepath) || !is_readable($filepath)) {
        return false;
    }

    $extension = strtolower($extension);
    if ($extension === 'svg') {
        return F_is_safe_svg_content($filepath);
    }

    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = strtolower((string) $finfo->file($filepath));
        if (in_array($mime, F_get_active_content_mime_types(), true) || str_ends_with($mime, '+xml')) {
            return false;
        }
    }

    $image_types = [
        'gif' => IMAGETYPE_GIF,
        'jpg' => IMAGETYPE_JPEG,
        'jpeg' => IMAGETYPE_JPEG,
        'png' => IMAGETYPE_PNG,
    ];
    if (isset($image_types[$extension])) {
        // @mago-expect lint:no-error-control-operator -- getimagesize() emits notices on malformed image data, which is rejected here
        $imsize = @getimagesize($filepath);
        if ($imsize === false || $imsize[2] !== $image_types[$extension]) {
            return false;
        }
    }

    $head = file_get_contents($filepath, false, null, 0, 1024);
    if ($head === false) {
        return false;
    }

    return preg_match('/<\s*(?:\?xml|!doctype|html|head|body|script|svg|iframe|object|embed|xsl:)/i', $head) !== 1;
}

/**
 * Returns the sanitized name and temporary path of a single uploaded file.
 *
 * @param string $fieldname Form field name.
 *
 * @return array{name: string, tmp_name: string}|null Null if no valid uploaded file or usable name is available.
 */
function F_get_uploaded_file_entry(string $fieldname): ?array
{
    $file = $_FILES[$fieldname] ?? null;
    if (
        !is_array($file)
        || !is_string($file['name'] ?? null)
        || !is_string($file['tmp_name'] ?? null)
        || !is_uploaded_file($file['tmp_name'])
    ) {
        return null;
    }

    $filename = F_sanitize_upload_filename($file['name']);
    if ($filename === '') {
        return null;
    }

    return ['name' => $filename, 'tmp_name' => $file['tmp_name']];
}

/**
 * Uploads a media file to the server.
 * The file name is sanitized, the extension and content are checked and existing files are
 * never overwritten.
 * @author Nicola Asuni
 * @since 2010-06-12
 *
 * @param string $fieldname Form field name containing the source file path.
 * @param string $uploaddir Upload directory.
 *
 * @return string|false File name, or false in case of error.
 */
function F_upload_file(string $fieldname, string $uploaddir): string|false
{
    global $l;
    require_once '../config/tce_config.php';
    $upload = F_get_uploaded_file_entry($fieldname);
    if ($upload === null) {
        F_print_error('ERROR', $l['m_upload_not']);
        return false;
    }

    $filename = $upload['name'];
    if (
        !F_is_allowed_upload($filename)
        || !F_is_safe_media_content($upload['tmp_name'], pathinfo($filename, PATHINFO_EXTENSION))
    ) {
        F_print_error('ERROR', $filename . ': ' . $l['m_upload_not']);
        return false;
    }

    // create the target exclusively so that an existing file is never replaced
    $filepath = $uploaddir . $filename;
    // @mago-expect lint:no-error-control-operator -- fopen() mode 'x' emits a warning when the file exists, which is reported here
    $placeholder = file_exists($filepath) ? false : @fopen($filepath, 'x');
    if ($placeholder === false) {
        F_print_error('WARNING', $filename . ': ' . $l['m_file_already_exist']);
        return false;
    }

    fclose($placeholder);
    if (!move_uploaded_file($upload['tmp_name'], $filepath)) {
        unlink($filepath);
        F_print_error('ERROR', $filename . ': ' . $l['m_upload_not']);
        return false;
    }

    F_print_error('MESSAGE', $filename . ': ' . $l['m_upload_yes']);
    return $filename;
}

/**
 * Returns an uploaded file without storing it.
 * The file stays in the PHP upload directory and is removed by PHP at the end of the request.
 *
 * @param string       $fieldname  Form field name containing the source file path.
 * @param list<string> $extensions Extensions accepted by the calling context.
 *
 * @return array{name: string, tmp_name: string}|false Sanitized file name and temporary path, or false in case of error.
 */
function F_get_uploaded_tmp_file(string $fieldname, array $extensions): array|false
{
    global $l;
    require_once '../config/tce_config.php';
    $upload = F_get_uploaded_file_entry($fieldname);
    if ($upload === null) {
        F_print_error('ERROR', $l['m_upload_not']);
        return false;
    }

    if (!F_is_allowed_upload($upload['name'], $extensions)) {
        F_print_error('ERROR', $upload['name'] . ': ' . $l['m_upload_not']);
        return false;
    }

    F_print_error('MESSAGE', $upload['name'] . ': ' . $l['m_upload_yes']);
    return $upload;
}

/**
 * returns the file size in bytes
 * @author Nicola Asuni
 * @since 2001-11-19
 * @param $filetocheck (string) file to check (local path or URL)
 * @return mixed file size in bytes or false in case of error
 */
function F_read_file_size($filetocheck)
{
    global $l;
    require_once '../config/tce_config.php';
    $filesize = 0;
    if ($fp = fopen($filetocheck, 'rb')) {
        $s_array = fstat($fp);
        if ($s_array['size'] !== 0) {
            $filesize = $s_array['size'];
        } else {
            //read size from remote file (very slow function)
            while (!feof($fp)) {
                $content = fread($fp, 1);
                ++$filesize;
            }
        }

        fclose($fp);
        return $filesize;
    }

    F_print_error('ERROR', basename($filetocheck) . ': ' . $l['m_openfile_not']);
    return false;
}
