<?php

//============================================================+
// File name   : tce_functions_otp.php
// Begin       : 2012-01-09
// Last Update : 2023-11-30
//
// Description : Functions for One Time Password (OTP).
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

/**
 * @file
 * Functions for One Time Password (OTP).
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @since 2012-01-09
 */

/**
 * Return a random One Time Password Secret Key (Base32 encoded).
 * @return Base32 encoded key.
 */
function F_getRandomOTPkey()
{
    // dictionary
    $dict = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $key = '';
    // generate a 16 char random secret key
    for ($i = 0; $i < 16; ++$i) {
        $key .= $dict[random_int(0, 31)];
    }

    return $key;
}

/**
 * Decode a Base32 encoded string.
 * @param $code (string) Base32 code to be decoded.
 * @return Decoded key.
 */
function F_decodeBase32($code)
{
    // dictionary
    $dict = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    // remove invalid chars
    $code = preg_replace('/[^' . $dict . ']+/', '', $code);
    $n = 0;
    $j = 0;
    $bin = '';
    $len = strlen($code);
    // for each char on code
    for ($c = 0; $c < $len; ++$c) {
        $n <<= 5;
        $n += strpos($dict, $code[$c]);
        $j += 5;
        if ($j >= 8) {
            $j -= 8;
            $bin .= chr(($n & (0xFF << $j)) >> $j);
        }
    }

    return $bin;
}

/**
 * Get a One Time Password for the specified secret key.
 * @param $otpkey (string) One Time Password secret key.
 * @param $mtime (int) Reference time in microseconds.
 * @return OTP
 */
function F_getOTP($otpkey, $mtime = 0)
{
    // get binary key
    $binkey = F_decodeBase32($otpkey);
    // get the current timestamp (the one time password changes every 30 seconds)
    if ($mtime == 0) {
        $mtime = microtime(true);
    }

    $time = floor($mtime / 30);
    // convert timestamp into a binary string of 8 bytes
    $bintime = pack('N*', 0) . pack('N*', $time);
    // calculate the SHA1 hash
    $hash = hash_hmac('sha1', $bintime, $binkey, true);

    $offset = ord($hash[19]) & 0xf;
    // one time password
    return (
        (
            ((ord($hash[$offset + 0]) & 0x7f) << 24)
            | ((ord($hash[$offset + 1]) & 0xff) << 16)
            | ((ord($hash[$offset + 2]) & 0xff) << 8)
            | (ord($hash[$offset + 3]) & 0xff)
        ) % (10 ** 6)
    );
}

/**
 * Check a One Time Password code against a secret key.
 * The code must have 6 digits and match the key for the current, previous or next 30-second step.
 * @param $otpkey (string) One Time Password secret key (Base32 encoded).
 * @param $code (string) code to check.
 * @param $mtime (float) reference time in seconds (0 = current time).
 * @return bool true if the code matches, false otherwise.
 */
function F_isValidOTPCode(
    #[\SensitiveParameter]
    mixed $otpkey,
    #[\SensitiveParameter]
    mixed $code,
    float $mtime = 0.0,
): bool {
    if (!is_string($otpkey) || $otpkey === '' || !is_string($code) || preg_match('/^[0-9]{6}$/', $code) !== 1) {
        return false;
    }

    if ($mtime === 0.0) {
        $mtime = microtime(true);
    }

    $valid = false;
    foreach ([0, -30, 30] as $step) {
        if (hash_equals(sprintf('%06d', (int) F_getOTP($otpkey, $mtime + $step)), $code)) {
            $valid = true;
        }
    }

    return $valid;
}

/**
 * Check a One Time Password submitted at login.
 * A valid code is stored in the sessions table for 5 minutes and is rejected while stored.
 * @param $user_name (string) user name.
 * @param $otpkey (string) user's One Time Password secret key (Base32 encoded).
 * @param $code (string) submitted code.
 * @return bool true if the code is valid and not yet used, false otherwise.
 */
function F_checkOTPLogin(
    string $user_name,
    #[\SensitiveParameter]
    mixed $otpkey,
    #[\SensitiveParameter]
    mixed $code,
): bool {
    global $db;
    if (!F_isValidOTPCode($otpkey, $code)) {
        return false;
    }

    // replay key bound to the user; without the secret it cannot be used as a session ID
    $otpid = hash_hmac('md5', 'otp' . $user_name . (string) $code, K_RANDOM_SECURITY);
    $sql = 'SELECT cpsession_id FROM ' . K_TABLE_SESSIONS . " WHERE cpsession_id='" . $otpid . "' LIMIT 1";
    if (!($r = F_db_query($sql, $db)) || F_db_fetch_array($r)) {
        return false;
    }

    $sql =
        'INSERT INTO '
        . K_TABLE_SESSIONS
        . ' (cpsession_id, cpsession_expiry, cpsession_data) VALUES (\''
        . $otpid
        . '\', \''
        . date(K_TIMESTAMP_FORMAT, time() + 300)
        . '\', \'300\')';
    if (!F_db_query($sql, $db)) {
        F_display_db_error();
        return false;
    }

    return true;
}
