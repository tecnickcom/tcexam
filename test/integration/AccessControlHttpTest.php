<?php

//============================================================+
// File name   : AccessControlHttpTest.php
// Begin       : 2026-11-02
//
// Description : HTTP-level integration tests for page levels and
//               cross-operator authorization on admin controllers.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

namespace Test\Integration;

/**
 * @file
 * HTTP-level integration tests for page levels and cross-operator authorization.
 * Seeds two operators in separate groups, a public user and an examinee with results on
 * operator B's test, then checks what anonymous users, public users and operator A can reach.
 * @package com.tecnick.tcexam.test
 */
final class AccessControlHttpTest extends AppHttpTestCase
{
    /** Password of every seeded user. */
    private const PW = 'itest-ac-pw-4Hq9x2Lm7v';

    /** Last name of the examinee whose results belong to operator B's test. */
    private const SECRET_NAME = 'ItestAcSecretName';

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

    /** Insert a module, subject, question and answer owned by a user and return their IDs. */
    private static function addContent(string $prefix, int $userId): array
    {
        self::dbExec(
            "INSERT INTO tce_modules (module_name,module_enabled,module_user_id) VALUES ('" . $prefix . "_mod','1'," . $userId . ')'
        );
        $mod = self::dbInt("SELECT module_id FROM tce_modules WHERE module_name='" . $prefix . "_mod'");
        self::dbExec(
            'INSERT INTO tce_subjects (subject_name,subject_enabled,subject_user_id,subject_module_id) '
            . "VALUES ('" . $prefix . "_subj','1'," . $userId . ',' . $mod . ')'
        );
        $subj = self::dbInt("SELECT subject_id FROM tce_subjects WHERE subject_name='" . $prefix . "_subj'");
        self::dbExec(
            'INSERT INTO tce_questions (question_subject_id,question_description,question_type,question_difficulty,question_enabled,question_position) '
            . 'VALUES (' . $subj . ",'" . $prefix . "_question',3,1,'1',1)"
        );
        $quest = self::dbInt('SELECT question_id FROM tce_questions WHERE question_subject_id=' . $subj);
        self::dbExec(
            'INSERT INTO tce_answers (answer_question_id,answer_description,answer_enabled) '
            . 'VALUES (' . $quest . ",'" . $prefix . "_answer','1')"
        );
        $answ = self::dbInt('SELECT answer_id FROM tce_answers WHERE answer_question_id=' . $quest);

        return ['mod' => $mod, 'subj' => $subj, 'quest' => $quest, 'answ' => $answ];
    }

    /** Seed groups, users, content, tests and one result on operator B's test. */
    private static function seed(): void
    {
        self::cleanup();
        foreach (['ga', 'gb'] as $g) {
            self::dbExec("INSERT INTO tce_user_groups (group_name) VALUES ('itest_ac_" . $g . "')");
        }
        $ga = self::dbInt("SELECT group_id FROM tce_user_groups WHERE group_name='itest_ac_ga'");
        $gb = self::dbInt("SELECT group_id FROM tce_user_groups WHERE group_name='itest_ac_gb'");

        $opa = self::addUser('itest_ac_opa', 5, $ga);
        $opb = self::addUser('itest_ac_opb', 5, $gb);
        $student = self::addUser('itest_ac_student', 1, $ga);
        $examinee = self::addUser('itest_ac_examinee', 1, $gb, self::SECRET_NAME);

        $a = self::addContent('itest_ac_a', $opa);
        $b = self::addContent('itest_ac_b', $opb);

        foreach (['a' => $opa, 'b' => $opb] as $k => $owner) {
            self::dbExec(
                'INSERT INTO tce_tests (test_name,test_description,test_user_id) '
                . "VALUES ('itest_ac_test_" . $k . "','d'," . $owner . ')'
            );
        }
        $testa = self::dbInt("SELECT test_id FROM tce_tests WHERE test_name='itest_ac_test_a'");
        $testb = self::dbInt("SELECT test_id FROM tce_tests WHERE test_name='itest_ac_test_b'");

        self::dbExec(
            'INSERT INTO tce_tests_users (testuser_test_id,testuser_user_id,testuser_status,testuser_creation_time) '
            . 'VALUES (' . $testb . ',' . $examinee . ",4,'2020-01-01 00:00:00')"
        );
        $tub = self::dbInt('SELECT testuser_id FROM tce_tests_users WHERE testuser_test_id=' . $testb);
        self::dbExec(
            'INSERT INTO tce_tests_logs (testlog_testuser_id,testlog_question_id,testlog_score,testlog_creation_time) '
            . 'VALUES (' . $tub . ',' . $b['quest'] . ",1,'2020-01-01 00:00:00')"
        );

        self::$ids = [
            'ga' => $ga,
            'gb' => $gb,
            'opa' => $opa,
            'opb' => $opb,
            'student' => $student,
            'examinee' => $examinee,
            'testa' => $testa,
            'testb' => $testb,
            'tub' => $tub,
            'moda' => $a['mod'],
            'subja' => $a['subj'],
            'questa' => $a['quest'],
            'answa' => $a['answ'],
            'modb' => $b['mod'],
            'subjb' => $b['subj'],
            'questb' => $b['quest'],
            'answb' => $b['answ'],
        ];
    }

    /** Remove every record created by this class. */
    private static function cleanup(): void
    {
        $tests = 'SELECT test_id FROM tce_tests WHERE test_name LIKE \'itest_ac_%\'';
        $testusers = 'SELECT testuser_id FROM tce_tests_users WHERE testuser_test_id IN (' . $tests . ')';
        $tsubsets = 'SELECT tsubset_id FROM tce_test_subject_set WHERE tsubset_test_id IN (' . $tests . ')';
        $subjects = 'SELECT subject_id FROM tce_subjects WHERE subject_name LIKE \'itest_ac_%\'';
        $users = 'SELECT user_id FROM tce_users WHERE user_name LIKE \'itest_ac_%\'';
        $groups = 'SELECT group_id FROM tce_user_groups WHERE group_name LIKE \'itest_ac_%\'';
        foreach ([
            'DELETE FROM tce_tests_logs WHERE testlog_testuser_id IN (' . $testusers . ')',
            'DELETE FROM tce_tests_users WHERE testuser_test_id IN (' . $tests . ')',
            'DELETE FROM tce_test_subjects WHERE subjset_tsubset_id IN (' . $tsubsets . ')',
            'DELETE FROM tce_test_subject_set WHERE tsubset_test_id IN (' . $tests . ')',
            'DELETE FROM tce_tests WHERE test_name LIKE \'itest_ac_%\'',
            'DELETE FROM tce_answers WHERE answer_question_id IN (SELECT question_id FROM tce_questions WHERE question_subject_id IN (' . $subjects . '))',
            'DELETE FROM tce_questions WHERE question_subject_id IN (' . $subjects . ')',
            'DELETE FROM tce_subjects WHERE subject_name LIKE \'itest_ac_%\'',
            'DELETE FROM tce_modules WHERE module_name LIKE \'itest_ac_%\'',
            'DELETE FROM tce_usrgroups WHERE usrgrp_user_id IN (' . $users . ') OR usrgrp_group_id IN (' . $groups . ')',
            'DELETE FROM tce_users WHERE user_name LIKE \'itest_ac_%\'',
            'DELETE FROM tce_user_groups WHERE group_name LIKE \'itest_ac_%\'',
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

    /**
     * Return a CSRF token rendered by the given page.
     *
     * @param array<string,string> $cookies Session cookies.
     */
    private function tokenFor(string $path, array $cookies): string
    {
        [, $body] = $this->http('GET', $path, $cookies);
        $token = self::extractCsrfToken($body);
        $this->assertNotNull($token, 'expected a CSRF token on ' . $path);

        return (string) $token;
    }

    /**
     * Report pages that must require an authenticated operator.
     *
     * @return list<string>
     */
    private function reportPages(): array
    {
        return [
            '/admin/code/tce_pdf_results.php?mode=1',
            '/admin/code/tce_pdf_results.php?mode=4',
            '/admin/code/tce_pdf_results.php?mode=5',
            '/admin/code/tce_pdf_results.php?mode=3&test_id=' . self::$ids['testb'],
            '/admin/code/tce_pdf_testgen.php?test_id=' . self::$ids['testb'],
            '/admin/code/tce_xml_user_results.php?user_id=' . self::$ids['examinee'],
            '/admin/code/tce_export_custom.php',
        ];
    }

    public function testAnonymousCannotReachReportPages(): void
    {
        foreach ($this->reportPages() as $path) {
            [$status, $body] = $this->http('GET', $path);
            $this->assertSame(200, $status, $path);
            $this->assertStringNotContainsString('%PDF', substr($body, 0, 16), $path . ' must not return a PDF');
            $this->assertStringContainsString('form_login', $body, $path . ' must show the login form');
            $this->assertStringNotContainsString(self::SECRET_NAME, $body, $path);
        }
    }

    public function testPublicUserCannotReachReportPages(): void
    {
        $cookies = $this->loginAs('itest_ac_student');
        foreach ($this->reportPages() as $path) {
            [, $body] = $this->http('GET', $path, $cookies);
            $this->assertStringNotContainsString('%PDF', substr($body, 0, 16), $path . ' must not return a PDF');
            $this->assertStringContainsString('form_login', $body, $path . ' must show the login form');
        }
    }

    public function testSessionIdIsNotAcceptedFromUrl(): void
    {
        $fixed = str_repeat('ab', 16);
        [, , $cookies] = $this->http('GET', '/public/code/index.php?PHPSESSID=' . $fixed);
        $this->assertArrayHasKey('PHPSESSID', $cookies);
        $this->assertNotSame($fixed, $cookies['PHPSESSID'], 'a session ID supplied in the URL must be ignored');
    }

    public function testOperatorAllResultsExcludeForeignTests(): void
    {
        $cookies = $this->loginAs('itest_ac_opb');
        [, $body] = $this->http('GET', '/admin/code/tce_show_result_allusers.php?sel=1&test_id=0', $cookies);
        $this->assertStringContainsString(self::SECRET_NAME, $body, 'the test owner must see its own results');

        $cookies = $this->loginAs('itest_ac_opa');
        [, $body] = $this->http('GET', '/admin/code/tce_show_result_allusers.php?sel=1&test_id=0', $cookies);
        $this->assertStringNotContainsString(self::SECRET_NAME, $body, 'results of foreign tests must not be listed');
    }

    public function testOperatorCannotChangeForeignResults(): void
    {
        $cookies = $this->loginAs('itest_ac_opa');
        $page = '/admin/code/tce_show_result_allusers.php';
        foreach (['lock', 'unlock', 'delete'] as $action) {
            $this->http('POST', $page, $cookies, [
                $action => '1',
                'itemcount' => '1',
                'testuserid1' => (string) self::$ids['tub'],
                'csrf_token' => $this->tokenFor($page, $cookies),
            ]);
        }

        $this->assertSame(
            '4',
            self::dbScalar('SELECT testuser_status FROM tce_tests_users WHERE testuser_id=' . self::$ids['tub']),
            'a foreign test-user record must not be unlocked or deleted'
        );
    }

    public function testOperatorCannotEditForeignSubject(): void
    {
        $cookies = $this->loginAs('itest_ac_opa');
        $page = '/admin/code/tce_edit_subject.php';
        $this->http('POST', $page, $cookies, [
            'update' => '1',
            'confirmupdate' => '1',
            'changecategory' => '1',
            'subject_id' => (string) self::$ids['subjb'],
            'subject_module_id' => (string) self::$ids['moda'],
            'subject_name' => 'itest_ac_hijacked',
            'subject_enabled' => '1',
            'csrf_token' => $this->tokenFor($page, $cookies),
        ]);
        $this->assertSame(
            'itest_ac_b_subj',
            self::dbScalar('SELECT subject_name FROM tce_subjects WHERE subject_id=' . self::$ids['subjb']),
            'changecategory must not skip the ownership check'
        );

        $this->http('POST', $page, $cookies, [
            'forcedelete' => 'delete',
            'changecategory' => '1',
            'subject_id' => (string) self::$ids['subjb'],
            'csrf_token' => $this->tokenFor($page, $cookies),
        ]);
        $this->assertSame(1, self::dbInt('SELECT COUNT(*) FROM tce_subjects WHERE subject_id=' . self::$ids['subjb']));

        $this->http('POST', $page, $cookies, [
            'add' => '1',
            'subject_module_id' => (string) self::$ids['modb'],
            'subject_name' => 'itest_ac_foreign_subj',
            'csrf_token' => $this->tokenFor($page, $cookies),
        ]);
        $this->assertSame(
            0,
            self::dbInt("SELECT COUNT(*) FROM tce_subjects WHERE subject_name='itest_ac_foreign_subj'"),
            'a subject must not be added to a foreign module'
        );
    }

    public function testOperatorCannotWriteUnderForeignParents(): void
    {
        $cookies = $this->loginAs('itest_ac_opa');

        $page = '/admin/code/tce_edit_question.php';
        $this->http('POST', $page, $cookies, [
            'add' => '1',
            'question_subject_id' => (string) self::$ids['subjb'],
            'question_description' => 'itest_ac_foreign_question',
            'question_type' => '3',
            'csrf_token' => $this->tokenFor($page, $cookies),
        ]);
        $this->assertSame(
            1,
            self::dbInt('SELECT COUNT(*) FROM tce_questions WHERE question_subject_id=' . self::$ids['subjb']),
            'a question must not be added to a foreign subject'
        );

        $page = '/admin/code/tce_edit_answer.php';
        $this->http('POST', $page, $cookies, [
            'add' => '1',
            'answer_question_id' => (string) self::$ids['questb'],
            'answer_description' => 'itest_ac_foreign_answer',
            'csrf_token' => $this->tokenFor($page, $cookies),
        ]);
        $this->assertSame(
            1,
            self::dbInt('SELECT COUNT(*) FROM tce_answers WHERE answer_question_id=' . self::$ids['questb']),
            'an answer must not be added to a foreign question'
        );

        $this->http('POST', $page, $cookies, [
            'update' => '1',
            'confirmupdate' => '1',
            'answer_id' => (string) self::$ids['answa'],
            'answer_question_id' => (string) self::$ids['questb'],
            'answer_description' => 'itest_ac_a_answer',
            'csrf_token' => $this->tokenFor($page, $cookies),
        ]);
        $this->assertSame(
            self::$ids['questa'],
            self::dbInt('SELECT answer_question_id FROM tce_answers WHERE answer_id=' . self::$ids['answa']),
            'an owned answer must not be moved under a foreign question'
        );

        $page = '/admin/code/tce_edit_test.php?test_id=' . self::$ids['testa'];
        $this->http('POST', '/admin/code/tce_edit_test.php', $cookies, [
            'addquestion' => '1',
            'test_id' => (string) self::$ids['testa'],
            'test_name' => 'itest_ac_test_a',
            'test_description' => 'd',
            'test_ip_range' => '*.*.*.*',
            'test_duration_time' => '60',
            'test_score_right' => '1',
            'subject_id' => [(string) self::$ids['subjb']],
            'tsubset_quantity' => '1',
            'csrf_token' => $this->tokenFor($page, $cookies),
        ]);
        $this->assertSame(
            0,
            self::dbInt('SELECT COUNT(*) FROM tce_test_subject_set WHERE tsubset_test_id=' . self::$ids['testa']),
            'a foreign subject must not be added to a test'
        );
    }
}
