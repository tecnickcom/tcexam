<?php

//============================================================+
// File name   : AppHttpTestCase.php
// Begin       : 2026-06-22
//
// Description : Base class for HTTP-level controller integration tests.
//               Provides a minimal HTTP client (over the stream wrapper, no
//               curl extension required) with cookie tracking and CSRF-token
//               extraction, driving the app-under-test container.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

namespace Test\Integration;

use PHPUnit\Framework\TestCase;

/**
 * @file
 * Base class for HTTP-level controller integration tests. Subclasses drive the real controllers
 * running in the app-under-test container (see docker/test-app-entrypoint.sh). Tests self-skip
 * when TCEXAM_APP_URL is not set (i.e. outside `make dockertest`).
 * @package com.tecnick.tcexam.test
 */
abstract class AppHttpTestCase extends TestCase
{
    /** Base URL of the app-under-test (no trailing slash). */
    protected string $base = '';

    protected function setUp(): void
    {
        $url = (string) getenv('TCEXAM_APP_URL');
        if ($url === '') {
            $this->markTestSkipped('App-under-test not configured: set TCEXAM_APP_URL (run via `make dockertest`).');
        }

        $this->base = rtrim($url, '/');
    }

    /**
     * Perform an HTTP request over the stream wrapper (no curl extension needed in the runner).
     *
     * @param array<string,string> $cookies Cookies to send (name => value).
     * @param array<string,mixed>  $post    Form fields for a POST request (values may be arrays,
     *                                       e.g. multi-select `name[]` fields).
     *
     * @return array{0:int,1:string,2:array<string,string>,3:list<string>} [status, body, cookies(sent+received), response headers]
     */
    protected function http(string $method, string $path, array $cookies = [], array $post = []): array
    {
        $header = '';
        $content = null;
        if ($method === 'POST') {
            $header = "Content-Type: application/x-www-form-urlencoded\r\n";
            $content = http_build_query($post);
        }

        return $this->send($method, $path, $cookies, $header, $content);
    }

    /**
     * Perform a multipart/form-data POST request with file uploads.
     *
     * @param array<string,string>                    $cookies Cookies to send (name => value).
     * @param array<string,string>                    $fields  Form fields.
     * @param array<string,array{0:string,1:string}> $files   Upload fields (name => [file name, content]).
     *
     * @return array{0:int,1:string,2:array<string,string>,3:list<string>} [status, body, cookies(sent+received), response headers]
     */
    protected function httpMultipart(string $path, array $cookies, array $fields, array $files): array
    {
        $boundary = '----tcexam' . uniqid();
        $content = '';
        foreach ($fields as $name => $value) {
            $content .=
                '--'
                . $boundary
                . "\r\n"
                . 'Content-Disposition: form-data; name="'
                . $name
                . "\"\r\n\r\n"
                . $value
                . "\r\n";
        }

        foreach ($files as $name => [$filename, $data]) {
            $content .=
                '--'
                . $boundary
                . "\r\n"
                . 'Content-Disposition: form-data; name="'
                . $name
                . '"; filename="'
                . $filename
                . "\"\r\n"
                . "Content-Type: application/octet-stream\r\n\r\n"
                . $data
                . "\r\n";
        }

        $content .= '--' . $boundary . "--\r\n";
        $header = 'Content-Type: multipart/form-data; boundary=' . $boundary . "\r\n";

        return $this->send('POST', $path, $cookies, $header, $content);
    }

    /**
     * Send a request with the given extra headers and body.
     *
     * @param array<string,string> $cookies Cookies to send (name => value).
     *
     * @return array{0:int,1:string,2:array<string,string>,3:list<string>} [status, body, cookies(sent+received), response headers]
     */
    private function send(string $method, string $path, array $cookies, string $header, ?string $content): array
    {
        $header = "Accept: text/html\r\n" . $header;
        if ($cookies !== []) {
            $pairs = [];
            foreach ($cookies as $k => $v) {
                $pairs[] = $k . '=' . $v;
            }

            $header .= 'Cookie: ' . implode('; ', $pairs) . "\r\n";
        }

        $opts = ['method' => $method, 'header' => $header, 'ignore_errors' => true, 'timeout' => 20];
        if ($content !== null) {
            $opts['content'] = $content;
        }

        $body = file_get_contents($this->base . $path, false, stream_context_create(['http' => $opts]));
        $headers = $http_response_header ?? [];

        return [self::statusFrom($headers), (string) $body, $cookies + self::cookiesFrom($headers), $headers];
    }

    /**
     * Return the value of the last response header with the given name, or null when absent.
     *
     * @param list<string> $headers Response header lines.
     */
    protected static function headerValue(array $headers, string $name): ?string
    {
        $value = null;
        foreach ($headers as $h) {
            if (stripos($h, $name . ':') !== 0) {
                continue;
            }

            $value = trim(substr($h, strlen($name) + 1));
        }

        return $value;
    }

    /** Extract the CSRF token embedded in a form, or null when absent. */
    protected static function extractCsrfToken(string $body): ?string
    {
        return preg_match('/name="csrf_token"[^>]*value="([^"]+)"/', $body, $m) === 1 ? $m[1] : null;
    }

    /** Extract the HTTP status code from a response header list (last status line wins). */
    private static function statusFrom(array $headers): int
    {
        $status = 0;
        foreach ($headers as $h) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', $h, $m) === 1) {
                $status = (int) $m[1];
            }
        }

        return $status;
    }

    /**
     * Parse Set-Cookie response headers into a name => value map.
     *
     * @return array<string,string>
     */
    private static function cookiesFrom(array $headers): array
    {
        $cookies = [];
        foreach ($headers as $h) {
            if (preg_match('/^Set-Cookie:\s*([^=]+)=([^;]*)/i', $h, $m) === 1) {
                $cookies[trim($m[1])] = $m[2];
            }
        }

        return $cookies;
    }
}
