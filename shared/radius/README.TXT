Pure PHP radius class

Last update: 2009-01-05, release 1.2.2

This Radius class is a radius client implementation in pure PHP
following the RFC 2865 rules (http://www.ietf.org/rfc/rfc2865.txt)


This class works with at least the following RADIUS servers:
 - Authenex Strong Authentication System (ASAS) with two-factor authentication
 - FreeRADIUS, a free Radius server implementation for Linux and *nix environments
 - Microsoft Radius server IAS
 - Mideye RADIUS server (http://www.mideye.com)
 - Radl, a free Radius server for Windows
 - RSA SecurID
 - VASCO Middleware 3.0 server
 - WinRadius, Windows Radius server (free for 5 users)
 - ZyXEL ZyWALL OTP (Authenex ASAS branded by ZyXEL, cheaper)
 


USAGE

require_once('radius.class.php');
$radius = new Radius($ip_radius_server = 'radius_server_ip_address', $shared_secret = 'radius_shared_secret'[, $radius_suffix = 'optional_radius_suffix'[, $udp_timeout = udp_timeout_in_seconds[, $authentication_port = 1812]]]);
$result = $radius->Access_Request($username = 'username', $password = 'password'[, $udp_timeout = udp_timeout_in_seconds]);



EXAMPLES

Example 1
    require_once('radius.class.php');
    $radius = new Radius('127.0.0.1', 'secret');
    $radius->SetNasIpAddress('1.2.3.4'); // Needed for some devices (not always auto-detected)
    if ($radius->AccessRequest('user', 'pass'))
    {
        echo "Authentication accepted.";
    }
    else
    {
        echo "Authentication rejected.";
    }


Example 2
    require_once('radius.class.php');
    $radius = new Radius('127.0.0.1', 'secret');
    $radius->SetNasPort(0);
    $radius->SetNasIpAddress('1.2.3.4'); // Needed for some devices (not always auto-detected)
    if ($radius->AccessRequest('user', 'pass'))
    {
        echo "Authentication accepted.";
        echo "<br />";
    }
    else
    {
        echo "Authentication rejected.";
        echo "<br />";
    }
    echo $radius->GetReadableReceivedAttributes();



LICENCE

Copyright (c) 2008, SysCo systemes de communication sa
SysCo (tm) is a trademark of SysCo systemes de communication sa
(http://www.sysco.ch/)
All rights reserved.

Pure PHP radius class is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public License as
published by the Free Software Foundation, either version 3 of the License,
or (at your option) any later version.

Pure PHP radius class is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with Pure PHP radius class.
If not, see <http://www.gnu.org/licenses/>.
