<?php

// TODOEXPLAIN
class class_authentication {

	// TODOEXPLAIN
	public function __construct() {
	}

	// TODOEXPLAIN
	function authenticate( $login, $password ) {
		return class_authentication::check_ldap('iisgnet\\' . $login, $password, array("apollo3.iisg.net", "apollo2.iisg.net"));
	}

	// TODOEXPLAIN
	function check_ldap($user, $pw, $servers) {
		$login_correct = 0;

		// LDAP AUTHENTICATION VIA PHP-LDAP
		// php-ldap must be installed on the server

		foreach ( $servers as $server ) {
			if ( $login_correct == 0 ) {

				// try to connect to ldap server
				$ad = ldap_connect($server);

				// set some variables
				ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
				ldap_set_option($ad, LDAP_OPT_REFERRALS, 0);

				// bind to the ldap directory
				$bd = @ldap_bind($ad, $user, $pw);

				// verify binding, if binding succeeds then login is correct
				if ($bd) {
					$login_correct = 1;
				}

				// never forget to unbind!
				ldap_unbind($ad);
			}
		}

		return $login_correct;
	}
}
