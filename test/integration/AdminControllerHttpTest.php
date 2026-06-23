<?php

//============================================================+
// File name   : AdminControllerHttpTest.php
// Begin       : 2026-06-22
//
// Description : Authenticated HTTP-level integration tests for the admin
//               controllers converted off the register-globals emulation
//               (plan Stage 8.2). Logs in as an administrator against the
//               app-under-test container and exercises the controllers.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

namespace Test\Integration;

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @file
 * Authenticated HTTP-level integration tests. Establishes an admin session against the
 * app-under-test container, then verifies the Stage 8.2-converted admin controllers load and
 * process input correctly through the real auth/session/CSRF machinery.
 * @package com.tecnick.tcexam.test
 */
final class AdminControllerHttpTest extends AppHttpTestCase
{
    /** Known password seeded for the 'admin' user so the test can authenticate. */
    private const ADMIN_PW = 'itest-admin-pw-7Q2cl8k8ec';

    /** Cached authenticated cookies (log in once for the whole class). */
    private static array $authCookies = [];

    /** Guard so the admin password is seeded only once. */
    private static bool $seeded = false;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAdminPassword();
    }

    /** Open a direct database connection via the DAL (for seeding/assertions). */
    private function dbConnect(): mixed
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
            (string) getenv('TCEXAM_DB_NAME')
        );
    }

    /** Set the 'admin' user's password to a known value via the DAL (portable bcrypt hash). */
    private function seedAdminPassword(): void
    {
        if (self::$seeded) {
            return;
        }

        $db = $this->dbConnect();
        $this->assertNotFalse($db, 'seed: database connection should open');

        $hash = password_hash(self::ADMIN_PW, PASSWORD_DEFAULT);
        $ok = \F_db_query("UPDATE tce_users SET user_password='" . $hash . "' WHERE user_name='admin'", $db);
        $this->assertNotFalse($ok, 'seed: admin password update should succeed');
        \F_db_close($db);

        self::$seeded = true;
    }

    /** Run a write/DDL statement via the DAL. */
    private function dbExec(string $sql): void
    {
        $db = $this->dbConnect();
        \F_db_query($sql, $db);
        \F_db_close($db);
    }

    /** Return the first column of the first row of a query, or null if no row. */
    private function dbScalar(string $sql): ?string
    {
        $db = $this->dbConnect();
        $res = \F_db_query($sql, $db);
        $val = null;
        if ($res !== false) {
            $row = \F_db_fetch_assoc($res);
            if (\is_array($row)) {
                $val = (string) reset($row);
            }
        }
        \F_db_close($db);

        return $val;
    }

    /** Return the id of the group with the given name, or 0 if absent. */
    private function groupIdByName(string $name): int
    {
        return (int) ($this->dbScalar("SELECT group_id FROM tce_user_groups WHERE group_name='" . $name . "'") ?? '0');
    }

    /** Ensure a group with the given name exists; return its id. */
    private function ensureGroup(string $name): int
    {
        $id = $this->groupIdByName($name);
        if ($id === 0) {
            $this->dbExec("INSERT INTO tce_user_groups (group_name) VALUES ('" . $name . "')");
            $id = $this->groupIdByName($name);
        }

        return $id;
    }

    /** Return the id of the user with the given name, or 0 if absent. */
    private function userIdByName(string $name): int
    {
        return (int) ($this->dbScalar("SELECT user_id FROM tce_users WHERE user_name='" . $name . "'") ?? '0');
    }

    /** True when the given user is linked to the given group. */
    private function userInGroup(int $userId, int $groupId): bool
    {
        $n = (int) ($this->dbScalar(
            'SELECT COUNT(*) FROM tce_usrgroups WHERE usrgrp_user_id=' . $userId . ' AND usrgrp_group_id=' . $groupId
        ) ?? '0');

        return $n > 0;
    }

    /** Remove a test user (and its group links) by name. */
    private function deleteUserByName(string $name): void
    {
        $id = $this->userIdByName($name);
        if ($id > 0) {
            $this->dbExec('DELETE FROM tce_usrgroups WHERE usrgrp_user_id=' . $id);
            $this->dbExec('DELETE FROM tce_users WHERE user_id=' . $id);
        }
    }

    /** Remove a test group (and its links) by id. */
    private function deleteGroupById(int $id): void
    {
        if ($id > 0) {
            $this->dbExec('DELETE FROM tce_usrgroups WHERE usrgrp_group_id=' . $id);
            $this->dbExec('DELETE FROM tce_user_groups WHERE group_id=' . $id);
        }
    }

    /** Log in as admin once and cache the authenticated session cookies. */
    private function login(): array
    {
        if (self::$authCookies !== []) {
            return self::$authCookies;
        }

        // GET an admin page to obtain a session cookie, then POST the login credentials to it.
        [, , $cookies] = $this->http('GET', '/admin/code/index.php');
        [, , $cookies] = $this->http('POST', '/admin/code/index.php', $cookies, [
            'logaction' => 'login',
            'xuser_name' => 'admin',
            'xuser_password' => self::ADMIN_PW,
        ]);

        self::$authCookies = $cookies;
        return self::$authCookies;
    }

    public function testAdminLoginSucceeds(): void
    {
        $cookies = $this->login();
        [$status, $body] = $this->http('GET', '/admin/code/index.php', $cookies);

        $this->assertSame(200, $status);
        $this->assertStringNotContainsString('form_login', $body, 'an authenticated session should not see the login form');
    }

    /**
     * The admin controllers converted off the register-globals emulation in Stage 8.2.
     *
     * @return array<string,array{0:string}>
     */
    public static function convertedAdminControllers(): array
    {
        $files = [
            'tce_edit_answer.php', 'tce_edit_group.php', 'tce_edit_module.php', 'tce_edit_question.php',
            'tce_edit_subject.php', 'tce_edit_sslcerts.php', 'tce_filemanager.php', 'tce_select_mediafile.php',
            'tce_edit_backup.php', 'tce_edit_user.php', 'tce_edit_test.php', 'tce_edit_rating.php',
            'tce_import_users.php', 'tce_select_users.php', 'tce_select_tests.php', 'tce_show_all_questions.php',
            'tce_show_result_allusers.php', 'tce_show_result_user.php',
        ];
        $cases = [];
        foreach ($files as $f) {
            $cases[$f] = ['/admin/code/' . $f];
        }

        return $cases;
    }

    #[DataProvider('convertedAdminControllers')]
    public function testConvertedAdminControllerLoadsAuthenticated(string $path): void
    {
        $cookies = $this->login();
        [$status, $body] = $this->http('GET', $path, $cookies);

        // A converted controller's explicit $_POST reads run at load time; a fatal there (e.g. a
        // bad conversion) would surface as a 500 (display_errors is off) or a PHP error in the body.
        $this->assertLessThan(500, $status, $path . ' should load without a server error');
        $this->assertStringNotContainsStringIgnoringCase('Parse error', $body, $path . ' should have no PHP parse error');
        $this->assertStringNotContainsStringIgnoringCase('Fatal error', $body, $path . ' should have no PHP fatal error');
        $this->assertStringNotContainsStringIgnoringCase('Uncaught', $body, $path . ' should have no uncaught exception');
        // Authenticated: the page must not have bounced us back to the login form.
        $this->assertStringNotContainsString('form_login', $body, $path . ' should be reachable while authenticated');
    }

    /**
     * End-to-end exercise of a converted POST path: add a group, then delete it through the
     * confirm/forcedelete flow (which runs the Stage 8.2-converted `$_POST['forcedelete']` read),
     * going through the real menu_mode dispatch + CSRF validation.
     */
    public function testGroupAddAndForceDeleteFlow(): void
    {
        $cookies = $this->login();
        $name = 'itest_grp_http';

        // A CSRF token rendered in any form is valid for the whole session (it verifies against the
        // session's plaintext token), so one extracted token serves all the POSTs below.
        [$status, $body] = $this->http('GET', '/admin/code/tce_edit_group.php', $cookies);
        $this->assertSame(200, $status);
        $token = self::extractCsrfToken($body);
        $this->assertNotNull($token, 'the group editor should expose a CSRF token');

        // 1) Add the group (menu_mode 'add' → INSERT). The button value is irrelevant; presence
        //    of the 'add' key drives the dispatch.
        [$status] = $this->http('POST', '/admin/code/tce_edit_group.php', $cookies, [
            'add' => '1',
            'group_name' => $name,
            'csrf_token' => $token,
        ]);
        $this->assertSame(200, $status, 'the add submission should be accepted');
        $id = $this->groupIdByName($name);
        $this->assertGreaterThan(0, $id, 'the group should have been created via the add POST');

        // 2) Request the delete confirmation (menu_mode 'delete') to obtain the forcedelete button
        //    value (the localized "delete" word the converted code compares against).
        [$status, $body] = $this->http('POST', '/admin/code/tce_edit_group.php', $cookies, [
            'delete' => '1',
            'group_id' => (string) $id,
            'csrf_token' => $token,
        ]);
        $this->assertSame(200, $status);
        $this->assertSame(1, preg_match('/name="forcedelete"[^>]*value="([^"]+)"/', $body, $m), 'the confirm form should render a forcedelete button');
        $forceValue = $m[1];

        // 3) Confirm the deletion (menu_mode 'forcedelete' → runs the converted $_POST read).
        [$status] = $this->http('POST', '/admin/code/tce_edit_group.php', $cookies, [
            'forcedelete' => $forceValue,
            'group_id' => (string) $id,
            'csrf_token' => $token,
        ]);
        $this->assertSame(200, $status, 'the forcedelete submission should be accepted');
        $this->assertSame(0, $this->groupIdByName($name), 'the group should have been deleted via the forcedelete POST');
    }

    /**
     * Regression test for the Stage 8.2 array bug fix: the `user_groups[]` multi-select was never
     * set by the old register-globals emulation (it skipped arrays via is_string), so group
     * assignment was silently broken. With the explicit `$_POST['user_groups'] ?? []` read, adding
     * a user with a selected group must actually link the user to that group.
     */
    public function testAddUserAssignsGroups(): void
    {
        $cookies = $this->login();
        $userName = 'itest_user_http';
        $groupId = $this->ensureGroup('itest_ug_grp');
        $this->assertGreaterThan(0, $groupId);
        $this->deleteUserByName($userName); // start clean

        [$status, $body] = $this->http('GET', '/admin/code/tce_edit_user.php', $cookies);
        $this->assertSame(200, $status);
        $token = self::extractCsrfToken($body);
        $this->assertNotNull($token, 'the user editor should expose a CSRF token');

        // Add a user with the group selected (user_groups[] is the multi-select array field).
        [$status] = $this->http('POST', '/admin/code/tce_edit_user.php', $cookies, [
            'add' => '1',
            'user_name' => $userName,
            'newpassword' => 'Itest-pw-123456',
            'newpassword_repeat' => 'Itest-pw-123456',
            'user_level' => '1',
            'user_groups' => [(string) $groupId],
            'csrf_token' => $token,
        ]);
        $this->assertSame(200, $status, 'the add-user submission should be accepted');

        $userId = $this->userIdByName($userName);
        $this->assertGreaterThan(0, $userId, 'the user should have been created');
        $this->assertTrue(
            $this->userInGroup($userId, $groupId),
            'the user_groups[] array must be read and persisted (Stage 8.2 array bug fix)'
        );

        // Cleanup.
        $this->deleteUserByName($userName);
        $this->deleteGroupById($groupId);
    }

    /**
     * Regression test: editing a user must preserve the round-tripped system fields user_regdate
     * and user_ip (hidden form fields). They were emulation-provided; without the explicit reads
     * the UPDATE would blank them.
     */
    public function testEditUserUpdatePreservesRoundTrippedFields(): void
    {
        $cookies = $this->login();
        $name = 'itest_user_upd';
        $newName = 'itest_user_upd2';
        $this->deleteUserByName($name);
        $this->deleteUserByName($newName);

        $hash = password_hash('x', PASSWORD_DEFAULT);
        $this->dbExec(
            "INSERT INTO tce_users (user_regdate,user_ip,user_name,user_password,user_level) "
            . "VALUES ('2020-01-01 00:00:00','1.2.3.4','" . $name . "','" . $hash . "',1)"
        );
        $id = $this->userIdByName($name);
        $this->assertGreaterThan(0, $id);

        [$status, $body] = $this->http('GET', '/admin/code/tce_edit_user.php?user_id=' . $id, $cookies);
        $this->assertSame(200, $status);
        $token = self::extractCsrfToken($body);
        $this->assertNotNull($token);

        // Update the name, round-tripping the hidden system fields exactly as the browser form does.
        [$status] = $this->http('POST', '/admin/code/tce_edit_user.php', $cookies, [
            'update' => '1',
            'confirmupdate' => '1',
            'user_id' => (string) $id,
            'user_name' => $newName,
            'user_level' => '1',
            'user_regdate' => '2020-01-01 00:00:00',
            'user_ip' => '1.2.3.4',
            'csrf_token' => $token,
        ]);
        $this->assertSame(200, $status);

        $this->assertSame($newName, $this->dbScalar('SELECT user_name FROM tce_users WHERE user_id=' . $id), 'user_name should be updated');
        $this->assertStringStartsWith('2020-01-01', (string) $this->dbScalar('SELECT user_regdate FROM tce_users WHERE user_id=' . $id), 'user_regdate must be preserved');
        $this->assertSame('1.2.3.4', $this->dbScalar('SELECT user_ip FROM tce_users WHERE user_id=' . $id), 'user_ip must be preserved');

        $this->deleteUserByName($newName);
        $this->deleteUserByName($name);
    }

    /** Regression test: adding a test via the converted edit_test controller persists it. */
    public function testEditTestAddPersists(): void
    {
        $cookies = $this->login();
        $name = 'itest_test_http';
        $this->dbExec("DELETE FROM tce_tests WHERE test_name='" . $name . "'");

        [$status, $body] = $this->http('GET', '/admin/code/tce_edit_test.php', $cookies);
        $this->assertSame(200, $status);
        $token = self::extractCsrfToken($body);
        $this->assertNotNull($token);

        // Satisfy ff_required = test_name,test_description,test_ip_range,test_duration_time,test_score_right.
        [$status] = $this->http('POST', '/admin/code/tce_edit_test.php', $cookies, [
            'add' => '1',
            'test_name' => $name,
            'test_description' => 'itest description',
            'test_ip_range' => '0.0.0.0',
            'test_duration_time' => '60',
            'test_score_right' => '1',
            'csrf_token' => $token,
        ]);
        $this->assertSame(200, $status, 'the add-test submission should be accepted');

        $id = (int) ($this->dbScalar("SELECT test_id FROM tce_tests WHERE test_name='" . $name . "'") ?? '0');
        $this->assertGreaterThan(0, $id, 'edit_test add must create the test (converted form fields read from $_REQUEST)');

        $this->dbExec('DELETE FROM tce_tests WHERE test_id=' . $id);
    }

    /**
     * Regression test for the dynamic `${$keyname}` → `$_POST[$keyname]` conversion plus the
     * self-referential `$new_group_id` read: the select_users 'addgroup' action reads the selected
     * userid<N> checkbox and the target group id, both formerly emulation-provided.
     */
    public function testSelectUsersAddGroupReadsSelection(): void
    {
        $cookies = $this->login();
        $userName = 'itest_su_user';
        $this->deleteUserByName($userName);
        $groupId = $this->ensureGroup('itest_su_group');

        $hash = password_hash('x', PASSWORD_DEFAULT);
        $this->dbExec(
            "INSERT INTO tce_users (user_regdate,user_ip,user_name,user_password,user_level) "
            . "VALUES ('2020-01-01 00:00:00','0.0.0.0','" . $userName . "','" . $hash . "',1)"
        );
        $userId = $this->userIdByName($userName);
        $this->assertGreaterThan(0, $userId);
        $this->assertFalse($this->userInGroup($userId, $groupId), 'precondition: user is not yet in the group');

        // The CSRF token is bound to the entry script, so fetch it from this controller's own page.
        [, $form] = $this->http('GET', '/admin/code/tce_select_users.php', $cookies);
        $token = self::extractCsrfToken($form) ?? '';

        // 'addgroup': the position-1 checkbox (userid1) selects our user; new_group_id is the target.
        [$status] = $this->http('POST', '/admin/code/tce_select_users.php', $cookies, [
            'addgroup' => '1',
            'new_group_id' => (string) $groupId,
            'userid1' => (string) $userId,
            'csrf_token' => $token,
        ]);
        $this->assertSame(200, $status);
        $this->assertTrue(
            $this->userInGroup($userId, $groupId),
            'addgroup must read the userid<N> selection ($_POST[$keyname]) and the self-ref $new_group_id'
        );

        $this->deleteUserByName($userName);
        $this->deleteGroupById($groupId);
    }

    /**
     * Regression test for the `$itemcount` count-bound loop + the dynamic `${$keyname}` →
     * `$_POST[$keyname]` conversion: show_result_allusers deletes the selected test-result rows.
     */
    public function testDeleteSelectedTestResult(): void
    {
        $cookies = $this->login();
        $adminId = $this->userIdByName('admin');

        // A real test row is required (testuser_test_id has a FK to tce_tests); none are seeded.
        // Owned by admin so the results page is authorized to render.
        $this->dbExec(
            "INSERT INTO tce_tests (test_name,test_description,test_user_id) VALUES ('itest_res_test','d'," . $adminId . ')'
        );
        $testId = (int) ($this->dbScalar("SELECT test_id FROM tce_tests WHERE test_name='itest_res_test'") ?? '0');
        $this->assertGreaterThan(0, $testId);

        $this->dbExec(
            "INSERT INTO tce_tests_users (testuser_test_id,testuser_user_id,testuser_creation_time) "
            . 'VALUES (' . $testId . ',' . $adminId . ",'2020-01-01 00:00:00')"
        );
        $tid = (int) ($this->dbScalar(
            'SELECT testuser_id FROM tce_tests_users WHERE testuser_test_id=' . $testId . ' ORDER BY testuser_id DESC'
        ) ?? '0');
        $this->assertGreaterThan(0, $tid);

        // CSRF token is entry-script-bound: fetch it from this controller's own results page.
        [, $form] = $this->http('GET', '/admin/code/tce_show_result_allusers.php?test_id=' . $testId, $cookies);
        $token = self::extractCsrfToken($form);
        $this->assertNotNull($token, 'the results page should expose a CSRF token');

        // 'delete': $itemcount bounds the loop; testuserid1 is the selected row.
        [$status] = $this->http('POST', '/admin/code/tce_show_result_allusers.php', $cookies, [
            'delete' => '1',
            'itemcount' => '1',
            'testuserid1' => (string) $tid,
            'csrf_token' => $token,
        ]);
        $this->assertSame(200, $status);
        $this->assertSame(
            '0',
            $this->dbScalar('SELECT COUNT(*) FROM tce_tests_users WHERE testuser_id=' . $tid),
            'delete must read $itemcount and the testuserid<N> selection'
        );
    }

}
