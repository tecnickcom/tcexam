<?php

//============================================================+
// File name   : SvgCheckTest.php
// Begin       : 2026-10-17
//
// Description : Unit tests for the uploaded SVG content check
//               (admin/code/tce_functions_svg_check.php).
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
 * Tests for F_is_safe_svg_content() and its use by F_is_safe_media_content().
 * @package com.tecnick.tcexam.test
 */
final class SvgCheckTest extends TestCase
{
    /** 1x1 PNG image. */
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

    private static string $dir = '';

    public static function setUpBeforeClass(): void
    {
        self::$dir = (string) tempnam(sys_get_temp_dir(), 'tcexam-svg-test-');
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
     * @return array<string,array{0:string}>
     */
    public static function safeSvgCases(): array
    {
        return [
            'minimal' => [
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><rect width="10" height="10" fill="red"/></svg>',
            ],
            'xml declaration and comment' => [
                '<?xml version="1.0" encoding="UTF-8" standalone="no"?>'
                    . "\n"
                    . '<!-- Created with Inkscape -->'
                    . "\n"
                    . '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"><circle cx="5" cy="5" r="4"/></svg>',
            ],
            'external doctype' => [
                '<?xml version="1.0" encoding="utf-8"?>'
                    . "\n"
                    . '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">'
                    . "\n"
                    . '<svg xmlns="http://www.w3.org/2000/svg" version="1.1"><path d="M0 0L10 10"/></svg>',
            ],
            'gradient, use and fragment urls' => [
                '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">'
                    . '<defs><linearGradient id="g1"><stop offset="0" stop-color="#fff"/></linearGradient>'
                    . '<path id="p1" d="M0 0h5v5z"/></defs>'
                    . '<use xlink:href="#p1" style="fill:url(#g1);stroke:url( \'#g1\' )"/><use href="#p1" x="5"/>'
                    . '<rect width="1" height="1" fill="url(#g1)" data-name="r" aria-hidden="true"/>'
                    . '<use href="#0"/><use href="#Безымянный_1"/><rect solid-color="#000" solid-opacity="1"/></svg>',
            ],
            'style element' => [
                '<svg xmlns="http://www.w3.org/2000/svg"><style type="text/css"><![CDATA[.a{fill:url(#g);}]]></style>'
                    . '<rect class="a" width="1" height="1"/></svg>',
            ],
            'rdf metadata' => [
                '<svg xmlns="http://www.w3.org/2000/svg" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"'
                    . ' xmlns:cc="http://creativecommons.org/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/">'
                    . '<metadata><rdf:RDF><cc:Work rdf:about=""><dc:format>image/svg+xml</dc:format>'
                    . '<dc:type rdf:resource="http://purl.org/dc/dcmitype/StillImage"/></cc:Work></rdf:RDF></metadata>'
                    . '<g xml:space="preserve"><text x="1" y="1">a &amp; b</text></g></svg>',
            ],
            'embedded raster image' => [
                '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">'
                    . '<image width="1" height="1" xlink:href="data:image/png;base64,'
                    . self::PNG
                    . '"/></svg>',
            ],
            'filter' => [
                '<svg xmlns="http://www.w3.org/2000/svg"><filter id="f"><feGaussianBlur stdDeviation="2" result="b"/>'
                    . '<feOffset in="b" dx="1" dy="1"/></filter><rect width="1" height="1" filter="url(#f)"/></svg>',
            ],
        ];
    }

    #[DataProvider('safeSvgCases')]
    public function testStaticSvgIsAccepted(string $svg): void
    {
        $this->assertTrue(\F_is_safe_svg_content($this->fixture('safe.svg', $svg)));
        $this->assertTrue(\F_is_safe_media_content($this->fixture('safe.svg', $svg), 'svg'));
        $this->assertTrue(\F_is_safe_media_content($this->fixture('safe.svg', $svg), 'SVG'));
    }

    /**
     * @return array<string,array{0:string}>
     */
    public static function unsafeSvgCases(): array
    {
        $ns = 'xmlns="http://www.w3.org/2000/svg"';
        $xlink = 'xmlns:xlink="http://www.w3.org/1999/xlink"';
        return [
            'empty' => [''],
            'not xml' => ['not an svg'],
            'malformed' => ['<svg ' . $ns . '><rect></svg>'],
            'root without namespace' => ['<svg><rect/></svg>'],
            'html root' => ['<html xmlns="http://www.w3.org/1999/xhtml"><body/></html>'],
            'script' => ['<svg ' . $ns . '><script>alert(1)</script></svg>'],
            'uppercase script in svg namespace' => ['<svg ' . $ns . '><SCRIPT>alert(1)</SCRIPT></svg>'],
            'xhtml script' => [
                '<svg ' . $ns . '><h:script xmlns:h="http://www.w3.org/1999/xhtml">alert(1)</h:script></svg>',
            ],
            'event handler' => ['<svg ' . $ns . ' onload="alert(1)"/>'],
            'event handler on child' => ['<svg ' . $ns . '><rect onmouseover="alert(1)"/></svg>'],
            'foreignObject' => [
                '<svg '
                    . $ns
                    . '><foreignObject><body xmlns="http://www.w3.org/1999/xhtml">x</body></foreignObject></svg>',
            ],
            'link' => ['<svg ' . $ns . ' ' . $xlink . '><a xlink:href="javascript:alert(1)"><rect/></a></svg>'],
            'set href' => ['<svg ' . $ns . '><set attributeName="href" to="javascript:alert(1)"/></svg>'],
            'animate' => ['<svg ' . $ns . '><animate attributeName="x" values="0;1"/></svg>'],
            'javascript href on use' => ['<svg ' . $ns . '><use href="javascript:alert(1)"/></svg>'],
            'external use' => ['<svg ' . $ns . ' ' . $xlink . '><use xlink:href="https://example.com/a.svg#x"/></svg>'],
            'external image' => [
                '<svg ' . $ns . ' ' . $xlink . '><image xlink:href="https://example.com/a.png"/></svg>',
            ],
            'local file image' => ['<svg ' . $ns . '><image href="file:///etc/passwd"/></svg>'],
            'svg data image' => ['<svg ' . $ns . '><image href="data:image/svg+xml;base64,PHN2Zy8+"/></svg>'],
            'feImage external' => [
                '<svg ' . $ns . '><filter><feImage href="https://example.com/a.png"/></filter></svg>',
            ],
            'xml:base' => ['<svg ' . $ns . ' xml:base="https://example.com/"/>'],
            'unknown attribute namespace' => ['<svg ' . $ns . ' xmlns:e="urn:e" e:x="1"/>'],
            'editor namespace' => [
                '<svg '
                    . $ns
                    . ' xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape" inkscape:version="1.3"/>',
            ],
            'editor element' => [
                '<svg '
                    . $ns
                    . ' xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd">'
                    . '<sodipodi:namedview/></svg>',
            ],
            'style attribute external url' => [
                '<svg ' . $ns . '><rect style="fill:url(https://example.com/a)"/></svg>',
            ],
            'presentation attribute external url' => ['<svg ' . $ns . '><rect fill="url(//example.com/a#g)"/></svg>'],
            'style element import' => ['<svg ' . $ns . '><style>@import "https://example.com/a.css";</style></svg>'],
            'style element external url' => [
                '<svg ' . $ns . '><style><![CDATA[rect{fill:url("https://example.com/a")}]]></style></svg>',
            ],
            'css escape' => ['<svg ' . $ns . '><rect style="fill:\\75rl(https://example.com/a)"/></svg>'],
            'style element image-set' => [
                '<svg ' . $ns . '><style>rect{fill:-webkit-image-set("https://example.com/a.png" 1x)}</style></svg>',
            ],
            'style attribute image' => [
                '<svg ' . $ns . '><rect style="fill:image(\'https://example.com/a.png\')"/></svg>',
            ],
            'style attribute cross-fade' => [
                '<svg ' . $ns . '><rect style="fill:cross-fade(\'https://example.com/a.png\', red)"/></svg>',
            ],
            'style element src' => ['<svg ' . $ns . '><style>rect{fill:src("https://example.com/a")}</style></svg>'],
            'stylesheet processing instruction' => [
                '<?xml-stylesheet type="text/xsl" href="data:,x"?><svg ' . $ns . '/>',
            ],
            'processing instruction inside' => ['<svg ' . $ns . '><?php echo 1; ?></svg>'],
            'internal subset' => ['<!DOCTYPE svg [<!ATTLIST svg onload CDATA "alert(1)">]><svg ' . $ns . '/>'],
            'entity declaration' => [
                '<!DOCTYPE svg [<!ENTITY x SYSTEM "file:///etc/passwd">]><svg ' . $ns . '><text>&x;</text></svg>',
            ],
            'html doctype' => ['<!DOCTYPE html><svg ' . $ns . '/>'],
            'utf-16 declaration' => ['<?xml version="1.0" encoding="UTF-16"?><svg ' . $ns . '/>'],
            'utf-7 declaration' => ["<?xml version='1.0' encoding = 'UTF-7'?><svg " . $ns . '/>'],
            'invalid utf-8' => ['<svg ' . $ns . "><text>\xff\xfe</text></svg>"],
            'utf-16 content' => [(string) mb_convert_encoding('<svg ' . $ns . '/>', 'UTF-16', 'UTF-8')],
            'utf-16le content without byte order mark' => [
                (string) mb_convert_encoding(
                    '<?xml version="1.0" encoding="UTF-16"?><svg ' . $ns . '/>',
                    'UTF-16LE',
                    'UTF-8',
                ),
            ],
            'nul byte' => ['<svg ' . $ns . "><text>a\0b</text></svg>"],
            'gzip compressed' => [(string) gzencode('<svg ' . $ns . '/>')],
            'svg element in metadata' => ['<svg ' . $ns . '><metadata><script>alert(1)</script></metadata></svg>'],
            'xhtml element in metadata' => [
                '<svg ' . $ns . '><metadata><h:iframe xmlns:h="http://www.w3.org/1999/xhtml"/></metadata></svg>',
            ],
            'rdf element outside metadata' => [
                '<svg ' . $ns . ' xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"><rdf:RDF/></svg>',
            ],
        ];
    }

    #[DataProvider('unsafeSvgCases')]
    public function testActiveSvgIsRejected(string $svg): void
    {
        $this->assertFalse(\F_is_safe_svg_content($this->fixture('unsafe.svg', $svg)));
        $this->assertFalse(\F_is_safe_media_content($this->fixture('unsafe.svg', $svg), 'svg'));
    }

    public function testMissingFileIsRejected(): void
    {
        $this->assertFalse(\F_is_safe_svg_content(self::$dir . '/missing.svg'));
    }
}
