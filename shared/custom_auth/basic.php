<?php
// This file name must match the name of your custom authentication method.

/**
 * Implement the custom authentication function.
 * Function name format "custom_auth_[auth method name]_check_login".
 */
function custom_auth_basic_check_login() {
    // Do anything you want for the authentication here:
    // - It can be checking username and password from another database.
    // - Token from query string.
    // - etc.
    if (isset($_POST['logaction']) and ($_POST['logaction'] == 'login') and isset($_POST['xuser_name']) and isset($_POST['xuser_password'])) {
        $username = $_POST['xuser_name'];
        $password = $_POST['xuser_password'];

        if (($username == K_CUSTOM_AUTH_BASIC_USERNAME) and password_verify($password, K_CUSTOM_AUTH_BASIC_PASSWORD_HASH)) {
            // Return the user data at least with the following minimum format.
            $usr['user_email'] = '';
            $usr['user_firstname'] = '';
            $usr['user_lastname'] = '';
            $usr['user_birthdate'] = '';
            $usr['user_birthplace'] = '';
            $usr['user_regnumber'] = '';
            $usr['user_ssn'] = '';
            $usr['user_level'] = K_CUSTOM_AUTH_BASIC_USER_LEVEL;
            $usr['usrgrp_group_id'] = K_CUSTOM_AUTH_BASIC_USER_GROUP_ID;

            return $usr;
        }
    }
}
