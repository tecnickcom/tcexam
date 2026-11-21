<?php

//============================================================+
// File name   : RegistrationHttpTest.php
// Begin       : 2026-11-18
//
// Description : HTTP-level integration tests for the public user
//               registration form and its group handling.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

namespace Test\Integration;

/**
 * @file
 * Drives public/code/tce_user_registration.php in the app-under-test container.
 * docker/test-app-entrypoint.sh makes the groups field required and sets
 * K_USRREG_ALLOWED_GROUPS to [ALLOWED_GROUP_ID]; K_USRREG_GROUP stays 1.
 * @package com.tecnick.tcexam.test
 */
final class RegistrationHttpTest extends AppHttpTestCase
{
    /** Group ID listed in K_USRREG_ALLOWED_GROUPS by the test entrypoint. */
    private const ALLOWED_GROUP_ID = 900001;

    /** Default group ID (K_USRREG_GROUP). */
    private const DEFAULT_GROUP_ID = 1;

    private const ALLOWED_GROUP_NAME = 'itest_reg_allowed';

    private const HIDDEN_GROUP_NAME = 'itest_reg_hidden';

    private const USER_PREFIX = 'itest_reg_';

    protected function setUp(): void
    {
        parent::setUp();
        self::cleanup();
        self::dbExec(
            'INSERT INTO tce_user_groups (group_id,group_name) VALUES ('
            . self::ALLOWED_GROUP_ID
            . ",'"
            . self::ALLOWED_GROUP_NAME
            . "')",
        );
        self::dbExec("INSERT INTO tce_user_groups (group_name) VALUES ('" . self::HIDDEN_GROUP_NAME . "')");
    }

    protected function tearDown(): void
    {
        if ((string) getenv('TCEXAM_APP_URL') !== '') {
            self::cleanup();
        }

        parent::tearDown();
    }

    /** Remove the users and groups created by these tests. */
    private static function cleanup(): void
    {
        self::dbExec(
            'DELETE FROM tce_usrgroups WHERE usrgrp_user_id IN (SELECT user_id FROM tce_users WHERE user_name LIKE \''
            . self::USER_PREFIX
            . '%\')',
        );
        self::dbExec("DELETE FROM tce_users WHERE user_name LIKE '" . self::USER_PREFIX . "%'");
        foreach ([self::ALLOWED_GROUP_NAME, self::HIDDEN_GROUP_NAME] as $name) {
            self::dbExec(
                'DELETE FROM tce_usrgroups WHERE usrgrp_group_id IN (SELECT group_id FROM tce_user_groups WHERE group_name=\''
                . $name
                . '\')',
            );
            self::dbExec("DELETE FROM tce_user_groups WHERE group_name='" . $name . "'");
        }
    }

    /** Open a direct database connection via the DAL. */
    private static function dbConnect(): mixed
    {
        $type = (string) getenv('TCEXAM_DB_TYPE');
        $dal = $type === 'POSTGRESQL'
            ? __DIR__ . '/../../shared/code/tce_db_dal_postgresql.php'
            : __DIR__ . '/../../shared/code/tce_db_dal_mysqli.php';
        require_once $dal;

        return \F_db_connect(
            (string) getenv('TCEXAM_DB_HOST'),
            (string) getenv('TCEXAM_DB_PORT'),
            (string) getenv('TCEXAM_DB_USER'),
            (string) getenv('TCEXAM_DB_PASSWORD'),
            (string) getenv('TCEXAM_DB_NAME'),
        );
    }

    /** Run a write statement via the DAL. */
    private static function dbExec(string $sql): void
    {
        $db = self::dbConnect();
        \F_db_query($sql, $db);
        \F_db_close($db);
    }

    /** Return the integer result of a query, or 0 if no row. */
    private static function dbInt(string $sql): int
    {
        $db = self::dbConnect();
        $res = \F_db_query($sql, $db);
        $val = 0;
        if ($res !== false) {
            $row = \F_db_fetch_assoc($res);
            if (\is_array($row)) {
                $val = (int) reset($row);
            }
        }
        \F_db_close($db);

        return $val;
    }

    private static function userId(string $name): int
    {
        return self::dbInt("SELECT user_id FROM tce_users WHERE user_name='" . $name . "'");
    }

    private static function inGroup(int $userId, int $groupId): bool
    {
        return (
            self::dbInt(
                'SELECT COUNT(*) FROM tce_usrgroups WHERE usrgrp_user_id='
                . $userId
                . ' AND usrgrp_group_id='
                . $groupId,
            ) > 0
        );
    }

    /**
     * Load the registration form and submit it with the given group value.
     *
     * @param mixed $groups value of the user_groups field (null omits the field)
     * @return array{0:int,1:string} status and response body
     */
    private function register(string $name, mixed $groups): array
    {
        [$status, $body, $cookies] = $this->http('GET', '/public/code/tce_user_registration.php');
        $this->assertSame(200, $status);
        $token = self::extractCsrfToken($body);
        $this->assertNotNull($token);

        $fields = [
            'add' => '1',
            'user_name' => $name,
            'user_email' => $name . '@example.com',
            'user_firstname' => 'Reg',
            'user_lastname' => 'Test',
            'newpassword' => 'Reg-pw-12345',
            'newpassword_repeat' => 'Reg-pw-12345',
            'user_agreement' => '1',
            'csrf_token' => $token,
        ];
        if ($groups !== null) {
            $fields['user_groups'] = $groups;
        }

        [$status, $body] = $this->http('POST', '/public/code/tce_user_registration.php', $cookies, $fields);

        return [$status, $body];
    }

    public function testFormListsOnlyAllowedGroups(): void
    {
        [$status, $body] = $this->http('GET', '/public/code/tce_user_registration.php');

        $this->assertSame(200, $status);
        $this->assertStringContainsString(self::ALLOWED_GROUP_NAME, $body);
        $this->assertStringNotContainsString(self::HIDDEN_GROUP_NAME, $body);
    }

    public function testRegistrationWithAllowedGroupCreatesUser(): void
    {
        $name = self::USER_PREFIX . 'ok';
        [$status] = $this->register($name, [(string) self::ALLOWED_GROUP_ID]);

        $this->assertSame(200, $status);
        $id = self::userId($name);
        $this->assertGreaterThan(0, $id, 'the user must be created');
        $this->assertTrue(self::inGroup($id, self::ALLOWED_GROUP_ID), 'the selected allowed group is assigned');
        $this->assertTrue(self::inGroup($id, self::DEFAULT_GROUP_ID), 'the default group is always assigned');
    }

    public function testRegistrationWithoutGroupIsRejected(): void
    {
        $name = self::USER_PREFIX . 'nogrp';
        [$status, $body] = $this->register($name, null);

        $this->assertSame(200, $status);
        $this->assertStringContainsString('required fields have not been filled out: groups', $body);
        $this->assertSame(0, self::userId($name), 'no user is created');
    }

    public function testRegistrationWithScalarGroupValueIsRejected(): void
    {
        $name = self::USER_PREFIX . 'scalar';
        [$status, $body] = $this->register($name, 'abc');

        $this->assertSame(200, $status);
        $this->assertStringContainsString('required fields have not been filled out: groups', $body);
        $this->assertSame(0, self::userId($name), 'no user is created');
    }

    public function testRegistrationWithOnlyHiddenGroupIsRejected(): void
    {
        $hidden = self::dbInt(
            "SELECT group_id FROM tce_user_groups WHERE group_name='" . self::HIDDEN_GROUP_NAME . "'",
        );
        $this->assertGreaterThan(0, $hidden);
        $name = self::USER_PREFIX . 'hidden';
        [$status, $body] = $this->register($name, [(string) $hidden]);

        $this->assertSame(200, $status);
        $this->assertStringContainsString('required fields have not been filled out: groups', $body);
        $this->assertSame(0, self::userId($name), 'no user is created');
    }
}
