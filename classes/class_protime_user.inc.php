<?php
require_once "class_role_authorisation.inc.php";

// TODOEXPLAIN
class static_protime_user {
	public static function getProtimeUserByLoginName( $loginname ) {
		global $databases;
		$id = 0;

		$oProtime = new class_mysql($databases['default']);
		$oProtime->connect();

		//
		$loginname = trim($loginname);

		if ( $loginname != '' ) {
			//
			$query = "SELECT * FROM " . class_settings::get('protime_tables_prefix') .  "CURRIC WHERE CONCAT(TRIM(FIRSTNAME),'.',TRIM(NAME))='" . $loginname . "' OR TRIM(" . class_settings::get('curric_loginname') . ")='" . $loginname . "' ";

			$resultReset = mysql_query($query, $oProtime->getConnection());
			if ($row = mysql_fetch_assoc($resultReset)) {
				$id = $row['PERSNR'];

			}
			mysql_free_result($resultReset);
		}

		return new class_protime_user( $id );
	}
}

// TODOEXPLAIN
class class_protime_user {
	protected $protime_id = 0;
	protected $loginname = '';
	protected $databases;
	protected $firstname = '';
	protected $lastname = '';
	protected $email = '';
	protected $room = '';
	protected $telephone = '';
	protected $beo = '';
	protected $authorisation = '';
	protected $is_admin = false;
	protected $is_tab_absences = false;
	protected $is_tab_fire = false;
	protected $all_roles = array();
	protected $department;

	// TODOEXPLAIN
	function __construct($protime_id) {
		global $databases;
		$this->databases = $databases;

		if ( $protime_id == '' || $protime_id < -1 ) {
			$protime_id = 0;
		}

		if ( $protime_id > 0 ) {
			$this->getProtimeValues( $protime_id );
		}
	}

	// TODOEXPLAIN
	function getProtimeValues( $protime_id ) {
		$oProtime = new class_mysql($this->databases['default']);
		$oProtime->connect();

		// reset values
		$query = "SELECT * FROM " . class_settings::get('protime_tables_prefix') .  "CURRIC WHERE PERSNR=" . $protime_id;
		$resultReset = mysql_query($query, $oProtime->getConnection());
		if ($row = mysql_fetch_assoc($resultReset)) {
			$this->protime_id = $protime_id;
			$this->lastname = trim($row["NAME"]);
			$this->firstname = trim($row["FIRSTNAME"]);
			$this->email = trim($row["EMAIL"]);

			$valueLoginNameField = trim($row[class_settings::get('curric_loginname')]);
			if ( $valueLoginNameField == '' ) {
				$this->loginname = strtolower(trim($row["FIRSTNAME"]) . '.' . trim($row["NAME"]));
			} else {
				$this->loginname = $valueLoginNameField;
			}

			$this->room =  trim($row[class_settings::get('curric_room')]);
			$this->telephone =  trim($row[class_settings::get('curric_telephone')]);
			$this->beo =  trim($row[class_settings::get('curric_beo')]);
//			$this->authorisation =  trim($row[class_settings::get('curric_authorisation')]);

			$this->department = new class_department( $row["DEPART"] );

			$this->calculateIsAdmin();

			$this->getAllRoles();

		}
		mysql_free_result($resultReset);
	}

	private function getAllRoles() {
		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$query = "SELECT * FROM Staff_role_authorisation WHERE isdeleted=0 ";

		$res = mysql_query($query, $oConn->getConnection());
		while ($r = mysql_fetch_assoc($res)) {
			$this->all_roles[] = new class_role_authorisation( $r["role"] );
		}
		mysql_free_result($res);
	}

	private function isAllowed( $authorisation, $field ) {
		$is_allowed = false;

		$arr = splitStringIntoArray( $authorisation, "/[^a-zA-Z0-9]/" );

		foreach ( $arr as $item ) {
			if ( !$is_allowed ) {

				foreach ( $this->all_roles as $role ) {
					if ( !$is_allowed ) {

						if ( strtolower($item) == strtolower($role->getRole()) ) {
							switch ( $field ) {
								case "tab_absences":
									if ( $role->getTabAbsences() == 1 ) {
										$is_allowed = true;
									}
									break;
								case "tab_fire":
									if ( $role->getTabFire() == 1 ) {
										$is_allowed = true;
									}
									break;
								case "tab_ontruimer":
									if ( $role->getTabOntruimer() == 1 ) {
										$is_allowed = true;
									}
									break;
							}
						}
					}
				}
			}
		}

		return $is_allowed;
	}

	private function hasRole( $authorisation, $field ) {
		$is_allowed = false;

		$arr = splitStringIntoArray( $authorisation, "/[^a-zA-Z0-9]/" );

		foreach ( $arr as $item ) {
			if ( !$is_allowed ) {

				if ( strtolower($item) == strtolower($field) ) {
					$is_allowed = true;
				}

			}
		}

		return $is_allowed;
	}

	function isTabAbsences() {
		if ( $this->isAdmin() ) {
			return true;
		}

		return $this->isAllowed($this->authorisation, "tab_absences" );
	}

	function isBhv() {
		return $this->hasRole($this->beo, "bhv" );
	}

	function isEhbo() {
		$hasRole = false;

		$roles = array('ehbo', 'e');
		foreach ( $roles as $role ) {
			if ( !$hasRole ) {
				$hasRole = $this->hasRole($this->beo, $role );
			}
		}

		return $hasRole;
	}

	function isOntruimer() {
		$hasRole = false;

		$roles = array();
		for ( $i = 0; $i <= class_settings::get('number_of_levels'); $i++ ) {
			$roles[] = 'O'.$i;
		}

		foreach ( $roles as $role ) {
			if ( !$hasRole ) {
				$hasRole = $this->hasRole($this->beo, $role );
			}
		}

		return $hasRole;
	}

	function isTabFire() {
		if ( $this->isAdmin() ) {
			return true;
		}

		return $this->isAllowed($this->authorisation, "tab_fire" );
	}

	function isTabOntruimer() {
		if ( $this->isAdmin() ) {
			return true;
		}

		return $this->isAllowed($this->authorisation, "tab_ontruimer" );
	}

	private function calculateIsAdmin() {
		// check if and set is_admin
		$arr = splitStringIntoArray(class_settings::get('superadmin'), "/[^a-zA-Z0-9\.]/");
		if ( in_array( $this->loginname, $arr ) ) {
			$this->is_admin = true;
		}
	}

	function getId() {
		return $this->protime_id;
	}

	function getFirstname() {
		return $this->firstname;
	}

	function getLastname() {
		return $this->lastname;
	}

	function getLoginname() {
		return $this->loginname;
	}

	function getRoom() {
		return $this->room;
	}

	function getTelephone() {
		return $this->telephone;
	}

	function getEmail() {
		return $this->email;
	}

	function getDepartment() {
		return $this->department;
	}

	//
	function isAdmin() {
		// TODO: tijdelijk uitgezet
		return $this->is_admin;
		//return false;
	}

	// TODO
	function hasInOutTimeAuthorisation() {
		return false;
	}

	// TODO
	function isHead() {
		return false;
	}

	// TODOEXPLAIN
	function isLoggedIn() {
		if ( $this->protime_id > 0 ) {
			return true;
		}

		return false;
	}

	// TODOEXPLAIN
	function checkLoggedIn() {
		global $protect;

		if ( $this->protime_id < 1 ) {
			Header("Location: login.php?burl=" . URLencode($protect->getShortUrl()));
			die("go to <a href=\"login.php?burl=" . URLencode($protect->getShortUrl()) . "\">next</a>");
		}
	}

	// TODOEXPLAIN
	function getFavourites( $type ) {
		global $databases;

		$oConn = new class_mysql($databases['default']);
		$oConn->connect();

		$ids = array();
		$ids[] = '0';

		$query = 'SELECT * FROM Staff_favourites WHERE user=\'' . $this->loginname . '\' AND type=\'' . $type . '\' ';
		$result = mysql_query($query, $oConn->getConnection());
		while ( $row = mysql_fetch_assoc($result) ) {
			$ids[] = $row["ProtimeID"];
		}
		mysql_free_result($result);

		return $ids;
	}
}
