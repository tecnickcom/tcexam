<?php

//============================================================+
// File name   : TcecodeLatexTest.php
// Begin       : 2026-11-02
//
// Description : Unit tests for the [tex] handling of
//               shared/code/tce_functions_tcecode.php.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

namespace Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @file
 * Tests for F_decode_tcecode() with LaTeX disabled and for the command filter of F_latex_callback().
 * The functions include '../config/tce_config.php' and '../../shared/config/tce_latex.php'
 * relative to the working directory, so the tests run from a temporary directory tree that
 * provides both files. Rejected formulas return before pdflatex is called.
 * @package com.tecnick.tcexam.test
 */
final class TcecodeLatexTest extends TestCase
{
    private static string $root = '';

    private static string $cwd = '';

    public static function setUpBeforeClass(): void
    {
        self::$cwd = (string) getcwd();
        self::$root = (string) tempnam(sys_get_temp_dir(), 'tcexam-latex-test-');
        unlink(self::$root);
        mkdir(self::$root . '/area/code', 0o777, true);
        mkdir(self::$root . '/area/config', 0o777, true);
        mkdir(self::$root . '/shared/config', 0o777, true);
        file_put_contents(self::$root . '/area/config/tce_config.php', "<?php\n");
        file_put_contents(self::$root . '/shared/config/tce_paths.php', "<?php\n");
        copy(__DIR__ . '/../shared/config.default/tce_latex.php', self::$root . '/shared/config/tce_latex.php');
        chdir(self::$root . '/area/code');
        // @mago-expect lint:no-global -- F_decode_tcecode() reads the language array from the global $l
        $GLOBALS['l'] = ['a_meta_charset' => 'UTF-8'];
    }

    public static function tearDownAfterClass(): void
    {
        chdir(self::$cwd);
        unlink(self::$root . '/area/config/tce_config.php');
        unlink(self::$root . '/shared/config/tce_paths.php');
        unlink(self::$root . '/shared/config/tce_latex.php');
        rmdir(self::$root . '/area/code');
        rmdir(self::$root . '/area/config');
        rmdir(self::$root . '/area');
        rmdir(self::$root . '/shared/config');
        rmdir(self::$root . '/shared');
        rmdir(self::$root);
    }

    public function testLatexDisabledKeepsTexTagAsText(): void
    {
        $html = F_decode_tcecode('a [tex]\pdffiledump length 8 {/etc/passwd}[/tex] b', false);
        self::assertStringContainsString('[tex]\pdffiledump length 8 {/etc/passwd}[/tex]', $html);
        self::assertStringNotContainsString('<img', $html);
        self::assertStringNotContainsString('LaTeX', $html);
    }

    /**
     * @return array<string,array{0:string}>
     */
    public static function rejectedFormulas(): array
    {
        return [
            'pdffiledump' => ['\pdffiledump length 64 {/etc/passwd}'],
            'pdfmdfivesum' => ['\pdfmdfivesum file {/etc/passwd}'],
            'pdffilesize' => ['\pdffilesize{/etc/passwd}'],
            'scantokens' => ['\scantokens{x}'],
            'immediate' => ['\immediate\message{x}'],
            'shipout' => ['\shipout\hbox{x}'],
            'input' => ['\input{/etc/passwd}'],
        ];
    }

    #[DataProvider('rejectedFormulas')]
    public function testFilterRejectsFormula(string $latex): void
    {
        self::assertSame('[LaTeX error]', F_latex_callback([0 => '', 1 => $latex]));
    }
}
