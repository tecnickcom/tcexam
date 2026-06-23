<?php

//============================================================+
// File name   : build_lang_cache.php
// Begin       : 2026-06-22
//
// Description : Pre-generate and validate the per-language translation
//               caches from the TMX source (plan Stage 6, Option C).
//
//               Usage:
//                 php tools/build_lang_cache.php [--tmx=FILE] [--out=DIR]
//
//               Generates cache/lang/language_tmx_<lang>.php for every
//               language found in the TMX, reusing the application's own
//               TMXResourceBundle so the output is exactly what the runtime
//               loads. Reports per-language key coverage and exits non-zero
//               if any language fails to produce a cache.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This tool must be run from the command line.\n");
    exit(1);
}

$root = dirname(__DIR__);

require_once $root . '/vendor/autoload.php';

// Minimal shim for the file-existence helper used by TMXResourceBundle (the real one lives in
// tce_functions_errmsg.php, which registers a global error handler on include).
if (! function_exists('F_file_exists')) {
    function F_file_exists($filename)
    {
        return @file_exists((string) $filename);
    }
}

require_once $root . '/shared/code/tce_tmx.php';

$opts = getopt('', ['tmx::', 'out::']);

// TMX source: prefer the active install config, fall back to the shipped default template.
$tmxfile = $opts['tmx'] ?? null;
if ($tmxfile === null) {
    $candidates = [
        $root . '/shared/config/lang/language_tmx.xml',
        $root . '/shared/config.default/lang/language_tmx.xml',
    ];
    foreach ($candidates as $candidate) {
        if (is_file($candidate)) {
            $tmxfile = $candidate;
            break;
        }
    }
}

if ($tmxfile === null || ! is_file($tmxfile)) {
    fwrite(STDERR, "ERROR: TMX file not found (looked for shared/config[.default]/lang/language_tmx.xml).\n");
    exit(1);
}

$outdir = $opts['out'] ?? ($root . '/cache/lang');
if (! is_dir($outdir) && ! @mkdir($outdir, 0o775, true) && ! is_dir($outdir)) {
    fwrite(STDERR, "ERROR: cannot create output directory: {$outdir}\n");
    exit(1);
}

// Derive the language set from the TMX itself (distinct xml:lang, normalised to a lowercase
// two-letter code), so the tool stays in sync with whatever the TMX actually contains.
$xml = (string) file_get_contents($tmxfile);
preg_match_all('/xml:lang="([^"]+)"/', $xml, $matches);
$languages = [];
foreach ($matches[1] as $lang) {
    $code = strtolower(substr($lang, 0, 2));
    $languages[$code] = true;
}

$languages = array_keys($languages);
sort($languages);

if ($languages === []) {
    fwrite(STDERR, "ERROR: no languages found in {$tmxfile}.\n");
    exit(1);
}

fwrite(STDOUT, "Building language caches from: {$tmxfile}\n");
fwrite(STDOUT, "Output directory:             {$outdir}\n\n");

$failed = 0;
$maxkeys = 0;
$report = [];
foreach ($languages as $lang) {
    $cachefile = $outdir . '/' . basename($tmxfile, '.xml') . '_' . $lang . '.php';
    // remove a stale cache so TMXResourceBundle regenerates instead of loading it
    if (is_file($cachefile)) {
        @unlink($cachefile);
    }

    $bundle = new TMXResourceBundle($tmxfile, $lang, $cachefile);
    $resource = $bundle->getResource();
    $total = count($resource);
    $translated = count(array_filter($resource, static fn($v): bool => $v !== ''));
    $maxkeys = max($maxkeys, $translated);
    $report[$lang] = ['translated' => $translated, 'total' => $total, 'cache' => $cachefile];
    if ($translated === 0 || ! is_file($cachefile)) {
        ++$failed;
    }
}

// Report: per-language translated-key count and coverage relative to the best-covered language.
foreach ($report as $lang => $info) {
    $coverage = $maxkeys > 0 ? round(100 * $info['translated'] / $maxkeys) : 0;
    $flag = $info['translated'] === 0 ? ' [FAIL: no translations]'
        : ($coverage < 90 ? ' [partial]' : '');
    fwrite(STDOUT, sprintf(
        "  %-3s  %4d keys  (%3d%%)%s\n",
        $lang,
        $info['translated'],
        $coverage,
        $flag
    ));
}

$ok = count($languages) - $failed;
fwrite(STDOUT, sprintf("\n%d/%d language caches generated (reference key count: %d).\n", $ok, count($languages), $maxkeys));

if ($failed > 0) {
    fwrite(STDERR, "ERROR: {$failed} language(s) failed to generate.\n");
    exit(1);
}

exit(0);
