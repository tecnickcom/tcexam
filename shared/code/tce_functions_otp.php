<?php
//============================================================+
// File name   : tce_functions_otp.php
// Begin       : 2012-01-09
// Last Update : 2012-11-22
//
// Description : Functions for One Time Password (OTP).
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//
// License:
//    Copyright (C) 2004-2012  Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
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
        $key .= $dict[(rand(0, 31))];
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
    $code = preg_replace('/[^'.$dict.']+/', '', $code);
    $n = 0;
    $j = 0;
    $bin = '';
    $len = strlen($code);
    // for each char on code
    for ($c = 0; $c < $len; ++$c) {
        $n = ($n << 5);
        $n = ($n + strpos($dict, $code[$c]));
        $j = ($j + 5);
        if ($j >= 8) {
            $j = ($j - 8);
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
    $bintime = pack('N*', 0).pack('N*', $time);
    // calculate the SHA1 hash
    $hash = hash_hmac('sha1', $bintime, $binkey, true);
    // get offset
    $offset = (ord($hash[19]) & 0xf);
    // one time password
    $otp = ((((ord($hash[($offset + 0)]) & 0x7f) << 24 )
        | ((ord($hash[($offset + 1)]) & 0xff) << 16 )
        | ((ord($hash[($offset + 2)]) & 0xff) << 8 )
        | (ord($hash[($offset + 3)]) & 0xff)) % pow(10, 6));
    return $otp;
}

//============================================================+
// END OF FILE
//============================================================+
