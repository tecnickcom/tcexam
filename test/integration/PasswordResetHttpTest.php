<?php

//============================================================+
// File name   : PasswordResetHttpTest.php
// Begin       : 2026-06-22
//
// Description : HTTP-level controller integration test for the password-reset
//               flow, exercised against the live app-under-test container.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

namespace Test\Integration;

/**
 * @file
 * HTTP-level controller integration test. It drives the real password-reset controller
 * (public/code/tce_password_reset.php) running in the app-under-test container against the
 * seeded database, going through the genuine session + CSRF machinery.
 *
 * This is the proving ground for the plan's Stage 8.2 work (removing the register-globals
 * emulation): the controller was converted to read $_POST['user_email'] explicitly, and this
 * test confirms the submitted value is processed correctly end-to-end over HTTP.
 * @package com.tecnick.tcexam.test
 */
final class PasswordResetHttpTest extends AppHttpTestCase
{
    public function testPasswordResetFormIsServed(): void
    {
        [$status, $body] = $this->http('GET', '/public/code/tce_password_reset.php');

        $this->assertSame(200, $status, 'the password-reset page should be served');
        $this->assertStringContainsString('name="user_email"', $body, 'the email field should be present');
        $this->assertStringContainsString('csrf_token', $body, 'a CSRF token field should be present');
    }

    public function testSubmittedEmailIsProcessedFromPost(): void
    {
        // GET the form first to obtain a session cookie and a valid CSRF token.
        [$status, $body, $cookies] = $this->http('GET', '/public/code/tce_password_reset.php');
        $this->assertSame(200, $status);

        $token = self::extractCsrfToken($body);
        $this->assertNotNull($token, 'CSRF token must be present');

        // POST a syntactically valid but non-existent address: the controller processes the form
        // (so $_POST['user_email'] is read) but sends no email, and echoes the address back.
        $email = 'nobody-itest@example.com';
        [$status, $body] = $this->http('POST', '/public/code/tce_password_reset.php', $cookies, [
            'resetpassword' => '1',
            'user_email' => $email,
            'csrf_token' => $token,
        ]);

        $this->assertSame(200, $status, 'the form submission should be accepted');
        $this->assertStringContainsString($email, $body, 'the controller must echo the email read from $_POST');
        $this->assertStringNotContainsStringIgnoringCase('wrong fields', $body, 'form validation should pass');
        $this->assertStringNotContainsStringIgnoringCase('missing fields', $body, 'CSRF/required-field checks should pass');
    }
}

