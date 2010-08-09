<?php
/*********************************************************************
 *
 * Pure PHP radius class, WWW Authentication file to be required
 *
 * This file must be required before displaying any protected page.
 * This file should be prepended automatically using the
 *   PHP auto_prepend_file directive in a .htaccess file.
 *
 *
 * LICENCE
 *
 *   Copyright (c) 2008, SysCo systèmes de communication sa
 *   SysCo (tm) is a trademark of SysCo systèmes de communication sa
 *   (http://www.sysco.ch/)
 *   All rights reserved.
 *
 *   This file is part of the Pure PHP radius class
 *
 *   Pure PHP radius class is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU Lesser General Public License as
 *   published by the Free Software Foundation, either version 3 of the License,
 *   or (at your option) any later version.
 *
 *   Pure PHP radius class is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU Lesser General Public License for more details.
 *
 *   You should have received a copy of the GNU Lesser General Public
 *   License along with Pure PHP radius class.
 *   If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @author: SysCo/al
 * @since CreationDate: 2008-01-06
 * @copyright (c) 2008 by SysCo systèmes de communication sa
 * @version $LastChangedRevision: 1.0 $
 * @version $LastChangedDate: 2008-01-07 $
 * @version $LastChangedBy: SysCo/al $
 * @link $HeadURL: radius.www.authenticate.php $
 * @link http://developer.sysco.ch/php/
 * @link developer@sysco.ch
 * Language: PHP 4.0.7 or higher
 *
 *
 * Usage
 *
 *   require_once("radius.www.authenticate.php");
 *   echo "User <strong>".$_SERVER['PHP_AUTH_USER']."</strong> authenticated.";
 *
 *
 * External file needed
 *
 *   radius.class.php
 *
 *
 * External file created
 *
 *   none.
 *
 *
 * Change Log
 *
 *   2008-01-07 1.0   SysCo/al Initial release
 *
 *********************************************************************/

// 2010-08-09 Nicola Asuni: Code clean-up

require_once('radius.class.php');

function authenticate_and_cache($ip_radius_server, $shared_secret, $username, $password, $timeout=900) {
	$result = FALSE;
	$cache_unique_id = (isset($_SESSION['authentication_unique_id']) ? $_SESSION['authentication_unique_id'] : '');
	if ($cache_unique_id != '') {
		$cache_timestamp= $_SESSION[$cache_unique_id.'_authentication_timestamp'];
		$cache_remote_addr = $_SESSION[$cache_unique_id.'_authentication_remote_addr'];
		$cache_username = $_SESSION[$cache_unique_id.'_authentication_username'];
	}
	if (($cache_timestamp == 0) OR (($cache_timestamp + $timeout) < time()) OR ($cache_remote_addr != $_SERVER['REMOTE_ADDR']) OR ($cache_username != $username)) {
		$radius = new Radius($ip_radius_server, $shared_secret);
		$radius->SetDebugMode($php_debug_mode);
		$result = $radius->AccessRequest($username, $password);
		if ($result === TRUE) {
			if ($cache_unique_id == '') {
				$cache_unique_id = md5(uniqid(rand(), true));
			}
			$_SESSION['authentication_unique_id'] = $cache_unique_id;
			$_SESSION[$cache_unique_id.'_authentication_timestamp'] = time();
			$_SESSION[$cache_unique_id.'_authentication_remote_addr'] = $_SERVER['REMOTE_ADDR'];
			$_SESSION[$cache_unique_id.'_authentication_username'] = $username;
		} else {
			$_SESSION['authentication_unique_id'] = '';
		}
	} else {
		$_SESSION[$cache_unique_id.'_authentication_timestamp'] = time();
		$result = TRUE;
	}
	return $result;
}

// Start session if not already done
if (session_id() == '') {
	session_start();
}

$php_auth_user = $_SERVER['PHP_AUTH_USER'];
$php_auth_pw = $_SERVER['PHP_AUTH_PW'];
$php_auth_realm = (isset($custom_auth_realm)?$custom_auth_realm:$_SERVER['SERVER_NAME']);
$php_auth_timeout = (isset($custom_auth_timeout)?$custom_auth_timeout:(15*60));
$php_ip_radius_server = (isset($custom_ip_radius_server)?$custom_ip_radius_server:'');
$php_shared_secret = (isset($custom_shared_secret)?$custom_shared_secret:'');
$php_debug_mode = (isset($custom_debug_mode)?(TRUE === $custom_debug_mode):FALSE);

if (('' == $php_auth_user) OR (!authenticate_and_cache($php_ip_radius_server, $php_shared_secret, $php_auth_user, $php_auth_pw, $php_auth_timeout))) {
	header('HTTP/1.0 401 Unauthorized');
	header('WWW-Authenticate: Basic realm="'.$php_auth_realm.'"');
	echo '<html>';
	echo '<head><title>401 Unauthorized access</title></head>';
	echo '<body>';
	echo '<h1>401 Unauthorized access</h1>';
	echo '<br />';
	echo 'You must login using your username and your password.';
	echo '</body>';
	echo '</html>';
	exit;
}

//============================================================+
// END OF FILE
//============================================================+
