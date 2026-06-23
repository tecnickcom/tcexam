<?php

//============================================================+
// File name   : FormValidationTest.php
// Begin       : 2026-06-23
//
// Description : Unit tests for the server-side form-field format validation
//               (shared/code/tce_functions_form.php) — Option C registry.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

namespace Test;

use PHPUnit\Framework\TestCase;

/**
 * @file
 * Tests for F_check_fields_format() and the canonical pattern registry.
 * @package com.tecnick.tcexam.test
 */
final class FormValidationTest extends TestCase
{
    public function testValidValuesPass(): void
    {
        $fields = [
            'user_email' => 'john.doe+tag@example.com',
            'newpassword' => 'longenough',
            'user_birthdate' => '1990-12-31',
            'test_begin_time' => '2026-01-15T09:30',
            'test_end_time' => '2026-01-15 09:30:00',
            'test_duration_time' => '3600',
            'test_ip_range' => '192.168.0.1,10.0.0.*',
            'test_score_right' => '1.5',
            'test_score_wrong' => '-0.25',
            'tsubset_quantity' => '10',
            'question_timer' => '60',
            'testlog_score' => '+2',
        ];
        $this->assertSame('', \F_check_fields_format($fields));
    }

    public function testInvalidValuesAreFlagged(): void
    {
        $this->assertSame('user_email', \F_check_fields_format(['user_email' => 'not-an-email']));
        $this->assertSame('newpassword', \F_check_fields_format(['newpassword' => 'short'])); // < 8 chars
        $this->assertSame('user_birthdate', \F_check_fields_format(['user_birthdate' => '1990-1-5']));
        $this->assertSame('test_begin_time', \F_check_fields_format(['test_begin_time' => '2026-01-15']));
        $this->assertSame('question_timer', \F_check_fields_format(['question_timer' => '12a']));
        $this->assertSame('test_score_right', \F_check_fields_format(['test_score_right' => 'abc']));
        $this->assertSame('test_ip_range', \F_check_fields_format(['test_ip_range' => '192.168.0.1/24']));
    }

    public function testMultipleWrongFieldsAreListed(): void
    {
        $result = \F_check_fields_format(['user_email' => 'bad', 'question_timer' => 'x']);
        $this->assertStringContainsString('user_email', $result);
        $this->assertStringContainsString('question_timer', $result);
        $this->assertStringContainsString(', ', $result); // comma-separated
    }

    public function testEmptyValueIsSkipped(): void
    {
        $this->assertSame('', \F_check_fields_format(['user_email' => '']));
    }

    public function testNonScalarValueIsSkipped(): void
    {
        // an array-valued field must not crash strlen()/preg_match() (PHP 8 throws on array args)
        $this->assertSame('', \F_check_fields_format(['user_email' => ['a', 'b']]));
    }

    public function testUnknownFieldIsNotValidated(): void
    {
        // fields absent from the registry are ignored entirely
        $this->assertSame('', \F_check_fields_format(['some_random_field' => '!!! not validated !!!']));
    }

    public function testLabelFromXlIsUsedInErrorMessage(): void
    {
        $result = \F_check_fields_format(['user_email' => 'bad', 'xl_user_email' => 'Email Address']);
        $this->assertSame('Email Address', $result);
    }

    // --- Security properties (Option C) ---

    public function testTamperedClientPatternIsIgnored(): void
    {
        // attacker swaps the round-tripped regex for a permissive one to smuggle a bad email;
        // the server uses its own canonical pattern, so the field is still flagged.
        $result = \F_check_fields_format(['user_email' => 'not-an-email', 'x_user_email' => '^.*$']);
        $this->assertSame('user_email', $result);
    }

    public function testOmittedClientPatternStillValidates(): void
    {
        // dropping x_<field> no longer skips the check (the old bypass) — the registry decides.
        $result = \F_check_fields_format(['user_email' => 'not-an-email']);
        $this->assertSame('user_email', $result);
    }

    public function testCatastrophicClientPatternIsNeverExecuted(): void
    {
        // a classic catastrophic-backtracking regex supplied by the client must be ignored; the
        // value is matched against the safe canonical integer pattern instead (and fails fast).
        $fields = [
            'question_timer' => str_repeat('a', 40) . '!',
            'x_question_timer' => '^(a+)+$',
        ];
        $this->assertSame('question_timer', \F_check_fields_format($fields));
    }

    public function testOverLongValueIsRejected(): void
    {
        // an all-digits value would satisfy the integer pattern, but exceeding the length cap is
        // treated as invalid so an unbounded POST cannot drive worst-case matching cost.
        $this->assertSame('question_timer', \F_check_fields_format(['question_timer' => str_repeat('1', 5000)]));
        // ...while a long-but-bounded valid value still passes.
        $this->assertSame('', \F_check_fields_format(['question_timer' => str_repeat('1', 100)]));
    }

    public function testRegistryCoversTheKnownValidatedFields(): void
    {
        $registry = \F_get_field_format_registry();
        foreach (['user_email', 'newpassword', 'user_birthdate', 'test_begin_time', 'test_ip_range', 'question_timer'] as $field) {
            $this->assertArrayHasKey($field, $registry);
        }
    }
}
