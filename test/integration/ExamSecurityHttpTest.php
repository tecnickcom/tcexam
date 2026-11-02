<?php

//============================================================+
// File name   : ExamSecurityHttpTest.php
// Begin       : 2026-11-02
//
// Description : HTTP-level integration tests for test log access
//               during an exam, the online users list and the
//               web installer guard.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

namespace Test\Integration;

/**
 * @file
 * HTTP-level integration tests for test log access during an exam, the online users list and
 * the web installer guard.
 * Seeds an active test with two examinees: examinee A has an earlier attempt and an active
 * attempt, examinee B has an active attempt with a free-text answer.
 * @package com.tecnick.tcexam.test
 */
final class ExamSecurityHttpTest extends AppHttpTestCase
{
    /** Password of every seeded user. */
    private const PW = 'itest-ex-pw-8Rt2v6Kp3z';

    /** Free-text answer of examinee B. */
    private const SECRET_ANSWER = 'ItestExSecretAnswer';

    /** Last name of the user listed on the online users page. */
    private const XSS_NAME = '&lt;img src=x onerror=alert(1)&gt;';

    /**
     * IDs of the seeded records.
     *
     * @var array<string,int>
     */
    private static array $ids = [];

    protected function setUp(): void
    {
        parent::setUp();
        if (self::$ids === []) {
            self::seed();
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$ids !== []) {
            self::cleanup();
            self::$ids = [];
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
            (string) getenv('TCEXAM_DB_NAME')
        );
    }

    /** Run a write statement via the DAL. */
    private static function dbExec(string $sql): void
    {
        $db = self::dbConnect();
        \F_db_query($sql, $db);
        \F_db_close($db);
    }

    /** Return the first column of the first row of a query, or null if no row. */
    private static function dbScalar(string $sql): ?string
    {
        $db = self::dbConnect();
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

    /** Return the integer result of a query, or 0 if no row. */
    private static function dbInt(string $sql): int
    {
        return (int) (self::dbScalar($sql) ?? '0');
    }

    /** Insert a user in a group and return its ID. */
    private static function addUser(string $name, int $level, int $groupId, string $lastname = ''): int
    {
        $hash = password_hash(self::PW, PASSWORD_DEFAULT);
        self::dbExec(
            'INSERT INTO tce_users (user_regdate,user_ip,user_name,user_password,user_level,user_lastname) '
            . "VALUES ('2020-01-01 00:00:00','0.0.0.0','" . $name . "','" . $hash . "'," . $level . ",'" . $lastname . "')"
        );
        $id = self::dbInt("SELECT user_id FROM tce_users WHERE user_name='" . $name . "'");
        self::dbExec('INSERT INTO tce_usrgroups (usrgrp_user_id,usrgrp_group_id) VALUES (' . $id . ',' . $groupId . ')');

        return $id;
    }

    /** Insert a free-text question in the subject and return its ID. */
    private static function addQuestion(int $subjectId, string $description, int $position): int
    {
        self::dbExec(
            'INSERT INTO tce_questions (question_subject_id,question_description,question_type,question_difficulty,question_enabled,question_position) '
            . 'VALUES (' . $subjectId . ",'" . $description . "',3,1,'1'," . $position . ')'
        );

        return self::dbInt("SELECT question_id FROM tce_questions WHERE question_description='" . $description . "'");
    }

    /** Insert a test attempt and return its ID. */
    private static function addAttempt(int $testId, int $userId, int $status): int
    {
        self::dbExec(
            'INSERT INTO tce_tests_users (testuser_test_id,testuser_user_id,testuser_status,testuser_creation_time) '
            . 'VALUES (' . $testId . ',' . $userId . ',' . $status . ",'" . date('Y-m-d H:i:s') . "')"
        );

        return self::dbInt(
            'SELECT testuser_id FROM tce_tests_users WHERE testuser_test_id=' . $testId
            . ' AND testuser_user_id=' . $userId . ' AND testuser_status=' . $status
        );
    }

    /** Insert a test log for an attempt and return its ID. */
    private static function addLog(int $testuserId, int $questionId, string $answer = ''): int
    {
        $text = $answer === '' ? 'NULL' : "'" . $answer . "'";
        self::dbExec(
            'INSERT INTO tce_tests_logs (testlog_testuser_id,testlog_question_id,testlog_answer_text,testlog_score,testlog_creation_time,testlog_order) '
            . 'VALUES (' . $testuserId . ',' . $questionId . ',' . $text . ",0,'2020-01-01 00:00:00',1)"
        );

        return self::dbInt('SELECT testlog_id FROM tce_tests_logs WHERE testlog_testuser_id=' . $testuserId);
    }

    /** Seed a group, users, an active test, attempts and test logs. */
    private static function seed(): void
    {
        self::cleanup();
        self::dbExec("INSERT INTO tce_user_groups (group_name) VALUES ('itest_ex_g')");
        $group = self::dbInt("SELECT group_id FROM tce_user_groups WHERE group_name='itest_ex_g'");

        $admin = self::addUser('itest_ex_admin', 10, $group);
        $usera = self::addUser('itest_ex_a', 1, $group);
        $userb = self::addUser('itest_ex_b', 1, $group);
        self::addUser('itest_ex_xss', 1, $group, self::XSS_NAME);

        self::dbExec(
            "INSERT INTO tce_modules (module_name,module_enabled,module_user_id) VALUES ('itest_ex_mod','1'," . $admin . ')'
        );
        $mod = self::dbInt("SELECT module_id FROM tce_modules WHERE module_name='itest_ex_mod'");
        self::dbExec(
            'INSERT INTO tce_subjects (subject_name,subject_enabled,subject_user_id,subject_module_id) '
            . "VALUES ('itest_ex_subj','1'," . $admin . ',' . $mod . ')'
        );
        $subj = self::dbInt("SELECT subject_id FROM tce_subjects WHERE subject_name='itest_ex_subj'");
        $qa = self::addQuestion($subj, 'itest_ex_question_a', 1);
        $qb = self::addQuestion($subj, 'itest_ex_question_b', 2);

        self::dbExec(
            'INSERT INTO tce_tests (test_name,test_description,test_begin_time,test_end_time,test_duration_time,'
            . 'test_ip_range,test_user_id,test_repeatable) '
            . "VALUES ('itest_ex_test','d','2000-01-01 00:00:00','2099-12-31 23:59:59',30000,'*.*.*.*',"
            . $admin . ',1)'
        );
        $test = self::dbInt("SELECT test_id FROM tce_tests WHERE test_name='itest_ex_test'");
        self::dbExec('INSERT INTO tce_testgroups (tstgrp_test_id,tstgrp_group_id) VALUES (' . $test . ',' . $group . ')');

        $olda = self::addAttempt($test, $usera, 5);
        $activea = self::addAttempt($test, $usera, 1);
        $activeb = self::addAttempt($test, $userb, 1);

        self::$ids = [
            'group' => $group,
            'test' => $test,
            'log_old_a' => self::addLog($olda, $qa),
            'log_a' => self::addLog($activea, $qa),
            'log_b' => self::addLog($activeb, $qb, self::SECRET_ANSWER),
        ];
    }

    /** Remove every record created by this class. */
    private static function cleanup(): void
    {
        $tests = 'SELECT test_id FROM tce_tests WHERE test_name LIKE \'itest_ex_%\'';
        $testusers = 'SELECT testuser_id FROM tce_tests_users WHERE testuser_test_id IN (' . $tests . ')';
        $subjects = 'SELECT subject_id FROM tce_subjects WHERE subject_name LIKE \'itest_ex_%\'';
        $users = 'SELECT user_id FROM tce_users WHERE user_name LIKE \'itest_ex_%\'';
        $groups = 'SELECT group_id FROM tce_user_groups WHERE group_name LIKE \'itest_ex_%\'';
        foreach ([
            'DELETE FROM tce_tests_logs WHERE testlog_testuser_id IN (' . $testusers . ')',
            'DELETE FROM tce_tests_users WHERE testuser_test_id IN (' . $tests . ')',
            'DELETE FROM tce_testgroups WHERE tstgrp_test_id IN (' . $tests . ')',
            'DELETE FROM tce_tests WHERE test_name LIKE \'itest_ex_%\'',
            'DELETE FROM tce_questions WHERE question_subject_id IN (' . $subjects . ')',
            'DELETE FROM tce_subjects WHERE subject_name LIKE \'itest_ex_%\'',
            'DELETE FROM tce_modules WHERE module_name LIKE \'itest_ex_%\'',
            'DELETE FROM tce_usrgroups WHERE usrgrp_user_id IN (' . $users . ') OR usrgrp_group_id IN (' . $groups . ')',
            'DELETE FROM tce_users WHERE user_name LIKE \'itest_ex_%\'',
            'DELETE FROM tce_user_groups WHERE group_name LIKE \'itest_ex_%\'',
        ] as $sql) {
            self::dbExec($sql);
        }
    }

    /**
     * Log in as a seeded user through the admin login form.
     *
     * @return array<string,string> session cookies
     */
    private function loginAs(string $name): array
    {
        [, , $cookies] = $this->http('GET', '/admin/code/index.php');
        [, , $cookies] = $this->http('POST', '/admin/code/index.php', $cookies, [
            'logaction' => 'login',
            'xuser_name' => $name,
            'xuser_password' => self::PW,
        ]);

        return $cookies;
    }

    /** Return the URL of the exam page for the seeded test with the given query parameters. */
    private static function examUrl(string $query): string
    {
        return '/public/code/tce_test_execute.php?testid=' . self::$ids['test'] . '&' . $query;
    }

    public function testExamineeCannotDisplayForeignTestLog(): void
    {
        $cookies = $this->loginAs('itest_ex_a');
        [, $body] = $this->http(
            'GET',
            self::examUrl('testlogid=' . self::$ids['log_a'] . '&nextquestion=1&nextquestionid=' . self::$ids['log_b']),
            $cookies
        );
        $this->assertStringContainsString('itest_ex_question_a', $body, 'the own question must be displayed');
        $this->assertStringNotContainsString('itest_ex_question_b', $body, 'a foreign question must not be displayed');
        $this->assertStringNotContainsString(self::SECRET_ANSWER, $body, 'a foreign answer must not be displayed');
    }

    public function testExamineeCannotChangeEarlierAttempt(): void
    {
        $cookies = $this->loginAs('itest_ex_a');
        $this->http('GET', self::examUrl('testlogid=' . self::$ids['log_a'] . '&answertext=itest_ex_own'), $cookies);
        $this->assertSame(
            'itest_ex_own',
            self::dbScalar('SELECT testlog_answer_text FROM tce_tests_logs WHERE testlog_id=' . self::$ids['log_a']),
            'the answer of the active attempt must be stored'
        );

        $this->http('GET', self::examUrl('testlogid=' . self::$ids['log_old_a'] . '&answertext=itest_ex_rewrite'), $cookies);
        $this->assertSame(
            0,
            self::dbInt(
                'SELECT COUNT(*) FROM tce_tests_logs WHERE testlog_id=' . self::$ids['log_old_a']
                . " AND testlog_answer_text='itest_ex_rewrite'"
            ),
            'the answer of an earlier attempt must not be changed'
        );
    }

    public function testOnlineUsersEscapesNames(): void
    {
        $this->loginAs('itest_ex_xss');
        $cookies = $this->loginAs('itest_ex_admin');
        [, $body] = $this->http(
            'GET',
            '/admin/code/tce_show_online_users.php?order_field=cpsession_expiry&orderdir=1',
            $cookies
        );
        $this->assertStringContainsString('onerror=alert(1)', $body, 'the user must be listed');
        $this->assertStringNotContainsString('<img src=x', $body, 'entity-encoded names must not become markup');
    }

    public function testInstallerIsNotAvailable(): void
    {
        [$status, $body] = $this->http('GET', '/install/install.php');
        $this->assertStringNotContainsString('form_TCExam_installer', $body);
        $this->assertTrue(
            $status === 403 || str_contains($body, 'already installed'),
            'the installer must be denied or refuse to run on an installed instance'
        );
    }
}
