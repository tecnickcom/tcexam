<?php

//============================================================+
// File name   : UploadFunctionsTest.php
// Begin       : 2026-10-17
//
// Description : Unit tests for the upload file name, extension and content
//               checks (admin/code/tce_functions_upload.php).
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
 * Tests for F_sanitize_upload_filename(), F_is_allowed_upload() and F_is_safe_media_content().
 * The bootstrap configures K_ALLOWED_UPLOAD_EXTENSIONS with svg, xml, html and php.
 * @package com.tecnick.tcexam.test
 */
final class UploadFunctionsTest extends TestCase
{
    /** 1x1 PNG image. */
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

    private static string $dir = '';

    public static function setUpBeforeClass(): void
    {
        self::$dir = (string) tempnam(sys_get_temp_dir(), 'tcexam-upload-test-');
        unlink(self::$dir);
        mkdir(self::$dir);
    }

    public static function tearDownAfterClass(): void
    {
        $files = glob(self::$dir . '/*');
        foreach (is_array($files) ? $files : [] as $file) {
            unlink($file);
        }

        rmdir(self::$dir);
    }

    private function fixture(string $name, string $content): string
    {
        $path = self::$dir . '/' . $name;
        file_put_contents($path, $content);
        return $path;
    }

    /**
     * @return array<string,array{0:string,1:string}>
     */
    public static function filenameCases(): array
    {
        return [
            'plain' => ['photo.png', 'photo.png'],
            'spaces' => ['my photo.png', 'my_photo.png'],
            'inner dots' => ['shell.php.png', 'shell_php.png'],
            'markup characters' => ['a"><script>.png', 'ascript.png'],
            'path separators' => ['../../etc/passwd.txt', '____etcpasswd.txt'],
            'hidden file' => ['.htaccess', ''],
            'no extension' => ['README', ''],
            'empty extension' => ['file.', ''],
            'empty' => ['', ''],
        ];
    }

    #[DataProvider('filenameCases')]
    public function testSanitizeUploadFilename(string $name, string $expected): void
    {
        $this->assertSame($expected, \F_sanitize_upload_filename($name));
    }

    public function testMediaUploadRejectsActiveContentEvenWhenConfigured(): void
    {
        foreach (['x.xml', 'x.html', 'x.Html'] as $name) {
            $this->assertFalse(\F_is_allowed_upload($name), $name);
        }
    }

    public function testMediaUploadAcceptsConfiguredSvg(): void
    {
        $this->assertTrue(\F_is_allowed_upload('x.svg'));
        $this->assertTrue(\F_is_allowed_upload('x.SVG'));
        $this->assertFalse(\F_is_allowed_upload('x.svgz'));
        $this->assertFalse(\F_is_allowed_upload('x.svg', ['png']));
    }

    public function testServerScriptsAreRejectedInEveryContext(): void
    {
        $this->assertFalse(\F_is_allowed_upload('x.php'));
        $this->assertFalse(\F_is_allowed_upload('x.php', ['php']));
        $this->assertFalse(\F_is_allowed_upload('x.PHP', ['php']));
    }

    public function testMediaUploadAcceptsConfiguredMediaTypes(): void
    {
        foreach (['x.png', 'x.PNG', 'x.jpeg', 'x.mp3', 'x.ogv', 'x.txt'] as $name) {
            $this->assertTrue(\F_is_allowed_upload($name), $name);
        }

        $this->assertFalse(\F_is_allowed_upload('x.exe'), 'extensions missing from the configuration are rejected');
        $this->assertFalse(\F_is_allowed_upload('noextension'));
    }

    public function testContextListRestrictsImports(): void
    {
        $this->assertTrue(\F_is_allowed_upload('users.xml', ['xml']));
        $this->assertFalse(\F_is_allowed_upload('users.tsv', ['xml']));
        $this->assertFalse(\F_is_allowed_upload('users.xml', ['tsv', 'txt', 'csv']));
        $this->assertTrue(\F_is_allowed_upload('cert.pfx', ['pfx', 'pem', 'crt']));
        $this->assertFalse(
            \F_is_allowed_upload('archive.zip', ['zip']),
            'context lists cannot extend the configuration',
        );
    }

    public function testRasterImageMustDecodeAsDeclaredType(): void
    {
        $png = $this->fixture('image.png', (string) base64_decode(self::PNG, true));
        $this->assertTrue(\F_is_safe_media_content($png, 'png'));
        $this->assertFalse(\F_is_safe_media_content($png, 'jpg'));
        $this->assertFalse(\F_is_safe_media_content($this->fixture('fake.png', 'not an image'), 'png'));
    }

    public function testMarkupContentIsRejected(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><script>document.title="x"</script></svg>';
        $this->assertFalse(\F_is_safe_media_content($this->fixture('svg.png', $svg), 'png'));
        $this->assertFalse(\F_is_safe_media_content($this->fixture('svg.txt', $svg), 'txt'));

        $html = '<!DOCTYPE html><html><body><script>document.title="x"</script></body></html>';
        $this->assertFalse(\F_is_safe_media_content($this->fixture('page.txt', $html), 'txt'));

        $xml = '<?xml version="1.0"?><?xml-stylesheet type="text/xsl" href="data:,x"?><root/>';
        $this->assertFalse(\F_is_safe_media_content($this->fixture('doc.txt', $xml), 'txt'));
        $this->assertFalse(\F_is_safe_media_content($this->fixture('doc.mp3', $xml), 'mp3'));
    }

    public function testPlainDataIsAccepted(): void
    {
        $this->assertTrue(\F_is_safe_media_content($this->fixture('data.csv', "a,b\n1,2\n"), 'csv'));
        $this->assertTrue(\F_is_safe_media_content($this->fixture('note.txt', 'a < b and c > d'), 'txt'));
        $this->assertTrue(\F_is_safe_media_content(
            $this->fixture('audio.mp3', "ID3\x03\x00\x00\x00\x00\x00\x00" . str_repeat("\xff\xfb", 64)),
            'mp3',
        ));
    }

    public function testMissingFileIsRejected(): void
    {
        $this->assertFalse(\F_is_safe_media_content(self::$dir . '/missing.png', 'png'));
    }
}
