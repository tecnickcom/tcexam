<?php

//============================================================+
// File name   : tce_altauth.php
// Begin       : 2008-03-28
// Last Update : 2023-11-30
//
// Description : Check user authorization against alternative
//               systems (SSL, HTTP-BASIC, CAS, SHIBBOLETH, RADIUS, LDAP)
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
//    Copyright (C) 2004-2025 Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Check user authorization against alternative systems (HTTP-BASIC, CAS, SHIBBOLETH, RADIUS, LDAP)
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @since 2008-03-28
 */

/**
 * Try various external Login Systems.
 * (SSL, HTTP-BASIC, CAS, SHIBBOLETH, RADIUS, LDAP, CUSTOM)
 * @return array of user's data for successful login, false otherwise
 * @since 2012-06-05
 */
function F_altLogin()
{
    global $l, $db;
    require_once('../config/tce_config.php');

    // TCExam tries to retrive the user login information from the following systems:

    // 1) SSL ----------------------------------------------------------
    require_once('../../shared/config/tce_ssl.php');
    if ((K_SSL_ENABLED && (!isset($_SESSION['logout']) || !$_SESSION['logout'])) && (isset($_SERVER['SSL_CLIENT_M_SERIAL']) && isset($_SERVER['SSL_CLIENT_I_DN']) && isset($_SERVER['SSL_CLIENT_V_END']) && isset($_SERVER['SSL_CLIENT_VERIFY']) && $_SERVER['SSL_CLIENT_VERIFY'] === 'SUCCESS' && isset($_SERVER['SSL_CLIENT_V_REMAIN']) && $_SERVER['SSL_CLIENT_V_REMAIN'] <= 0)) {
        $_POST['xuser_name'] = md5($_SERVER['SSL_CLIENT_M_SERIAL'] . $_SERVER['SSL_CLIENT_I_DN']);
        $_POST['xuser_password'] = getPasswordHash($_SERVER['SSL_CLIENT_M_SERIAL'] . $_SERVER['SSL_CLIENT_I_DN'] . K_RANDOM_SECURITY . $_SERVER['SSL_CLIENT_V_END']);
        $_POST['logaction'] = 'login';
        $usr = [];
        $usr['user_email'] = $_SERVER['SSL_CLIENT_S_DN_Email'] ?? '';

        $usr['user_firstname'] = $_SERVER['SSL_CLIENT_S_DN_CN'] ?? '';

        $usr['user_lastname'] = '';
        $usr['user_birthdate'] = '';
        $usr['user_birthplace'] = '';
        $usr['user_regnumber'] = '';
        $usr['user_ssn'] = '';
        $usr['user_level'] = K_SSL_USER_LEVEL;
        $usr['usrgrp_group_id'] = K_SSL_USER_GROUP_ID;
        return $usr;
    }

    // -----------------------------------------------------------------

    // 2) HTTP BASIC ---------------------------------------------------
    require_once('../../shared/config/tce_httpbasic.php');
    if ((K_HTTPBASIC_ENABLED && (!isset($_SESSION['logout']) || !$_SESSION['logout'])) && (isset($_SERVER['AUTH_TYPE']) && $_SERVER['AUTH_TYPE'] == 'Basic' && isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) && $_SESSION['session_user_name'] != $_SERVER['PHP_AUTH_USER'])) {
        $_POST['xuser_name'] = $_SERVER['PHP_AUTH_USER'];
        $_POST['xuser_password'] = $_SERVER['PHP_AUTH_PW'];
        $_POST['logaction'] = 'login';
        $usr = [];
        $usr['user_email'] = '';
        $usr['user_firstname'] = '';
        $usr['user_lastname'] = '';
        $usr['user_birthdate'] = '';
        $usr['user_birthplace'] = '';
        $usr['user_regnumber'] = '';
        $usr['user_ssn'] = '';
        $usr['user_level'] = K_HTTPBASIC_USER_LEVEL;
        $usr['usrgrp_group_id'] = K_HTTPBASIC_USER_GROUP_ID;
        return $usr;
    }

    // -----------------------------------------------------------------

    // 3) CAS - Central Authentication Service -------------------------
    require_once('../../shared/config/tce_cas.php');
    if (K_CAS_ENABLED) {
        require_once('../../shared/cas/CAS.php');
        phpCAS::client(K_CAS_VERSION, K_CAS_HOST, K_CAS_PORT, K_CAS_PATH, false);
        phpCAS::setNoCasServerValidation();
        phpCAS::forceAuthentication();
        if ($_SESSION['session_user_name'] != phpCAS::getUser()) {
            $_POST['xuser_name'] = phpCAS::getUser();
            $_POST['xuser_password'] = getPasswordHash($_POST['xuser_name'] . K_RANDOM_SECURITY);
            $_POST['logaction'] = 'login';
            $usr = [];
            $usr['user_email'] = '';
            $usr['user_firstname'] = '';
            $usr['user_lastname'] = '';
            $usr['user_birthdate'] = '';
            $usr['user_birthplace'] = '';
            $usr['user_regnumber'] = '';
            $usr['user_ssn'] = '';
            $usr['user_level'] = K_CAS_USER_LEVEL;
            $usr['usrgrp_group_id'] = K_CAS_USER_GROUP_ID;
            return $usr;
        }
    }

    // -----------------------------------------------------------------

    // 4) Shibboleth ---------------------------------------------------
    require_once('../../shared/config/tce_shibboleth.php');
    if ((K_SHIBBOLETH_ENABLED && (!isset($_SESSION['logout']) || !$_SESSION['logout'])) && (isset($_SERVER['AUTH_TYPE']) && $_SERVER['AUTH_TYPE'] == 'shibboleth' && (isset($_SERVER['Shib_Session_ID']) && !empty($_SERVER['Shib_Session_ID']) || isset($_SERVER['HTTP_SHIB_IDENTITY_PROVIDER']) && !empty($_SERVER['HTTP_SHIB_IDENTITY_PROVIDER'])) && isset($_SERVER['eppn']) && $_SESSION['session_user_name'] != $_SERVER['eppn'])) {
        $_POST['xuser_name'] = $_SERVER['eppn'];
        $_POST['xuser_password'] = getPasswordHash($_POST['xuser_name'] . K_RANDOM_SECURITY);
        $_POST['logaction'] = 'login';
        $usr = [];
        $usr['user_email'] = $_SERVER['eppn'];
        $usr['user_firstname'] = $_SERVER['givenName'] ?? '';

        $usr['user_lastname'] = $_SERVER['sn'] ?? '';

        $usr['user_birthdate'] = '';
        $usr['user_birthplace'] = '';
        $usr['user_regnumber'] = $_SERVER['employeeNumber'] ?? '';

        $usr['user_ssn'] = '';
        $usr['user_level'] = K_SHIBBOLETH_USER_LEVEL;
        $usr['usrgrp_group_id'] = K_SHIBBOLETH_USER_GROUP_ID;
        return $usr;
    }

    // -----------------------------------------------------------------

    if (isset($_POST['logaction']) && $_POST['logaction'] == 'login' && isset($_POST['xuser_name']) && isset($_POST['xuser_password'])) {
        // 5) RADIUS ---------------------------------------------------
        require_once('../../shared/config/tce_radius.php');
        if (K_RADIUS_ENABLED) {
            require_once('../../shared/radius/radius.class.php');
            $radius = new Radius(K_RADIUS_SERVER_IP, K_RADIUS_SHARED_SECRET, K_RADIUS_SUFFIX, K_RADIUS_UDP_TIMEOUT, K_RADIUS_AUTHENTICATION_PORT, K_RADIUS_ACCOUNTING_PORT);
            if (K_RADIUS_UTF8) {
                $radusername = mb_convert_encoding($_POST['xuser_name'], 'UTF-8', 'auto');
                $radpassword = mb_convert_encoding($_POST['xuser_password'], 'UTF-8', 'auto');
            } else {
                $radusername = $_POST['xuser_name'];
                $radpassword = $_POST['xuser_password'];
            }

            if ($radius->AccessRequest($radusername, $radpassword)) {
                return [
                    'user_email' => '',
                    'user_firstname' => '',
                    'user_lastname' => '',
                    'user_birthdate' => '',
                    'user_birthplace' => '',
                    'user_regnumber' => '',
                    'user_ssn' => '',
                    'user_level' => K_RADIUS_USER_LEVEL,
                    'usrgrp_group_id' => K_RADIUS_USER_GROUP_ID,
                ];
            }
        }

        // -------------------------------------------------------------

        // 6) LDAP -----------------------------------------------------
        require_once('../../shared/config/tce_ldap.php');
        if (K_LDAP_ENABLED) {
            // make ldap connection
            $ldapconn = ldap_connect(K_LDAP_HOST, K_LDAP_PORT);
            ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, K_LDAP_PROTOCOL_VERSION);
            ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0); // recommended for W2K3
            // bind anonymously and get dn for username.
            if (K_LDAP_UTF8) {
                $ldapusername = mb_convert_encoding($_POST['xuser_name'], 'UTF-8', 'auto');
                $ldappassword = mb_convert_encoding($_POST['xuser_password'], 'UTF-8', 'auto');

            } else {
                $ldapusername = $_POST['xuser_name'];
                $ldappassword = $_POST['xuser_password'];
            }

            if ($lbind = ldap_bind($ldapconn, K_LDAP_ROOT_DN, K_LDAP_ROOT_PASS)) {
                // Search user on LDAP tree
                $ldap_filter = str_replace('#USERNAME#', $ldapusername, K_LDAP_FILTER);
                $sorted_ldap_attr = $ldap_attr;
                sort($sorted_ldap_attr);
                //var_export($rdn); // uncomment this to see the structure of the entries
                if (($search = @ldap_search($ldapconn, K_LDAP_BASE_DN, $ldap_filter, $sorted_ldap_attr)) && ($rdn = @ldap_get_entries($ldapconn, $search)) && (!empty($rdn[0]['dn']) && @ldap_bind($ldapconn, $rdn[0]['dn'], $ldappassword))) {
                    @ldap_unbind($ldapconn);
                    $usr = [];
                    foreach ($ldap_attr as $k => $v) {
                        if (!empty($v) && isset($rdn[0][$v])) {
                            $usr[$k] = is_array($rdn[0][$v]) ? $rdn[0][$v][0] : $rdn[0][$v];
                        } else {
                            $usr[$k] = '';
                        }
                    }

                    $usr['user_level'] = K_LDAP_USER_LEVEL;
                    $usr['usrgrp_group_id'] = K_LDAP_USER_GROUP_ID;
                    return $usr;
                }
            }

            @ldap_unbind($ldapconn);
        }

        // -------------------------------------------------------------
    }

    /**
     * Custom authentication methods hook.
     */
    if (K_CUSTOM_AUTH_METHODS) {
        $methods = unserialize(K_CUSTOM_AUTH_METHODS);
        foreach ($methods as $method) {
            $config_file = '../../shared/config/custom_auth/' . $method . '.php';
            if (file_exists($config_file)) {
                require_once($config_file);
            }

            $main_file = '../../shared/custom_auth/' . $method . '.php';
            if (file_exists($main_file)) {
                require_once($main_file);
            }

            $auth_function = 'custom_auth_' . $method . '_check_login';
            if (function_exists($auth_function)) {
                return $auth_function();
            }
        }
    }

    return false;
}

//=====================================================================+
// END OF FILE
//=====================================================================+
