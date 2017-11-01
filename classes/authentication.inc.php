<?php

class Authentication {
	public static function authenticate( $login, $password ) {
		// IISG Active Directory
		if ( strpos($login, '.') !== false ) {
			// if login contains 'dot' then authenticate with Microsoft Active Directory
			return Authentication::check_ad($login, $password, 'iisg');
		} // KNAW LDAP
		else {
			// if login does not contain 'dot' then authenticate with LDAP
			return Authentication::check_ldap($login, $password, 'knaw');
		}
	}

	public static function check_ad($user, $pw, $authenticationServer) {
		$login_correct = 0;

//preprint('iisg ad');

		// LDAP AUTHENTICATIE VIA PHP-LDAP
		// php-ldap must be installed on the server

		// get settings
		$auth = Authentication::getServerAuthorisationInfo($authenticationServer);

		// add prefix
		$user = $auth['prefix'] . $user . $auth['postfix'];
		// remove double prefix
		$user = str_replace($auth['prefix'] . $auth['prefix'], $auth['prefix'], $user);

		foreach ( unserialize($auth['servers']) as $server ) {
			if ( $login_correct == 0 ) {
				// connect
				$ad = ldap_connect($server['server']) or die ("Could not connect to $server. Please contact IT Servicedesk");

				// set some variables
				ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
				ldap_set_option($ad, LDAP_OPT_REFERRALS, 0);

				// bind to the ldap directory
				$bd = @ldap_bind($ad, $user, $pw);

				// verify binding
				if ($bd) {
					$login_correct = 1;
				}

				// never forget to unbind!
				ldap_unbind($ad);
			}
		}

		if ( $login_correct == 0 ) {
			error_log("LOGIN FAILED $user from " . Misc::get_remote_addr() . " (AD: " . trim(Settings::get('ms_active_directories')) . ")");
		}

		return $login_correct;
	}

	//
	public static function check_ldap($user, $pw, $authenticationServer) {
		$login_correct = 0;

preprint('knaw ad');
		// get settings
		$auth = Authentication::getServerAuthorisationInfo($authenticationServer);

		// add prefix
		$user = $auth['prefix'] . $user . $auth['postfix'];
		// remove double prefix
		$user = str_replace($auth['prefix'] . $auth['prefix'], $auth['prefix'], $user);

		// loop all Active Directory servers
		foreach ( unserialize($auth['servers']) as $server ) {
			if ( $login_correct == 0 ) {
preprint( $user );
				// try to connect to the ldap server
				$ad = ldap_connect($auth['protocol'] . $server['server'], $server['port']);

				// set some variables
				ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
				ldap_set_option($ad, LDAP_OPT_REFERRALS, 0);

				// bind to the ldap directory
				$bd = @ldap_bind($ad, $user, $pw);

				// verify binding, if binding succeeds then login is correct
				if ($bd) {
					$login_correct = 1;
				} else {
					error_log("LOGIN FAILED $user from " . Misc::get_remote_addr() . " (LDAP: " . $server . ")");
				}

				// never forget to unbind!
				ldap_unbind($ad);
			}
		}

preprint( $login_correct );
die('BBBB<BR>Under construction');

		return $login_correct;
	}


	public static function getServerAuthorisationInfo( $authenticationServer ) {
		global $dbConn;

		// get settings
		$query = "SELECT * FROM server_authorisation WHERE code = :code ";
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->bindParam(':code', $authenticationServer, PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch();

		return $result;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n";
	}
}
