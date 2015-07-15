<?php
require_once "class_role_authorisation.inc.php";

// TODOEXPLAIN
class static_protime_user {
	public static function getProtimeUserByLoginName( $loginname ) {
		global $databases;
		$id = 0;

		$oProtime = new class_mysql($databases['default']);
		$oProtime->connect();

		$oProtime2 = new class_mysql($databases['default']);
		$oProtime2->connect();

		//
		$loginname = trim($loginname);

		if ( $loginname != '' ) {

			//
			$query = "SELECT * FROM " . class_settings::get('protime_tables_prefix') .  "CURRIC WHERE ( DATE_OUT='0' OR DATE_OUT>='" . date("Ymd") . "' ) AND ( CONCAT(TRIM(FIRSTNAME),'.',TRIM(NAME))='" . $loginname . "' OR TRIM(" . class_settings::get('curric_loginname') . ")='" . $loginname . "' ) ";

			$resultReset = mysql_query($query, $oProtime->getConnection());
			if ($row = mysql_fetch_assoc($resultReset)) {
				$id = $row['PERSNR'];

			}
			mysql_free_result($resultReset);

			// if id still 0, try different way to find protime user
			if ( $id == 0 ) {

				$arrLoginname = explode('.', $loginname);

				$query2 = "SELECT * FROM " . class_settings::get('protime_tables_prefix') .  "CURRIC WHERE ( DATE_OUT='0' OR DATE_OUT>='" . date("Ymd") . "' ) AND FIRSTNAME LIKE '%" . $arrLoginname[0] . "%' ";
				$resultReset2 = mysql_query($query2, $oProtime2->getConnection());
				while ($row2 = mysql_fetch_assoc($resultReset2) ) {
					$oEmp2 = new class_protime_user( $row2['PERSNR']);
					if ( $oEmp2->getLoginname() == $loginname ) {
						$id = $oEmp2->getId();
					}

				}
				mysql_free_result($resultReset2);

			}
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
	protected $roles = '';
	protected $arrRoles = array();
	protected $isLoaded_arrRoles = false;
	protected $arrAuthorisation = array();
	protected $is_admin = false;
	protected $department;
	protected $oDepartment;

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
			$this->loginname = trim(strtolower($row[class_settings::get('curric_loginname')]));
			$this->room =  trim($row[class_settings::get('curric_room')]);
			$this->telephone =  trim($row[class_settings::get('curric_telephone')]);
			$this->department = $row["DEPART"];
			$this->oDepartment = new class_department( $row["DEPART"] );
			$this->roles = $row[class_settings::get('curric_roles')];

			$this->calculateIsAdmin();
			$this->calculateRoles();
			$this->calculateAuthorisation();
			$this->fixLoginName();
		}
		mysql_free_result($resultReset);
	}

	private function fixLoginName() {
		if ( $this->loginname != '' ) {
			return;
		}

		$new = $this->firstname . '.' . verplaatsTussenvoegselNaarBegin($this->lastname);
		$new = removeJobFunctionFromName($new);
		$new = str_replace(' ', '', $new);
		$new = strtolower($new);

		$this->loginname = $new;
	}

	private function calculateRoles() {
		$roles = trim($this->roles);

		$arrRoles = array();

		$arr = splitStringIntoArray( $roles, "/[^a-zA-Z0-9]/" );
		foreach ( $arr as $item ) {

			$item = trim($item);
			$item = strtolower($item);

			if ( $item != '' ) {
				$arrRoles[] = $item;
			}
		}

		// make unique
		$arrRoles = array_unique( $arrRoles );

		$this->arrRoles = $arrRoles;
	}

	private function calculateAuthorisation() {
		$departmentId = trim($this->department);

		if ( $departmentId == '' || $departmentId == 0 ) {
			return;
		}

		$arrAuthorisation = array();

		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		// DEPARTMENT
		$query = "SELECT * FROM Staff_department_authorisation WHERE DEPART=" . $departmentId;

		$res = mysql_query($query, $oConn->getConnection());
		while ($r = mysql_fetch_assoc($res)) {
			$authorisation = trim( $r["authorisation"] );
			$authorisation = strtolower($authorisation);
			if ( trim($authorisation) != '' ) {
				$arrAuthorisation[] = $authorisation;
			}
		}
		mysql_free_result($res);

		// ROLE
		if ( !$this->isLoaded_arrRoles ) {
			$this->calculateRoles();
		}
		// loop through all roles
		foreach ( $this->arrRoles as $role ) {
			$query = "SELECT * FROM Staff_role_authorisation WHERE role='" . $role . "' ";

			$res = mysql_query($query, $oConn->getConnection());
			while ($r = mysql_fetch_assoc($res)) {
				$authorisation = trim( $r["authorisation"] );
				$authorisation = strtolower($authorisation);
				if ( trim($authorisation) != '' ) {
					$arrAuthorisation[] = $authorisation;
				}
			}
			mysql_free_result($res);

		}

		// make unique
		$arrAuthorisation = array_unique( $arrAuthorisation );

		$this->arrAuthorisation = $arrAuthorisation;
	}

	function hasAuthorisationTabAbsences() {
		return ( $this->isAdmin() || in_array('tab_absences', $this->arrAuthorisation) );
	}

	function isBhv() {
		return in_array('bhv', $this->arrRoles);
	}

	function isEhbo() {
		return ( in_array('ehbo', $this->arrRoles) || in_array('e', $this->arrRoles) );
	}

	function isOntruimer() {
		$hasRole = false;
		$roles = array();

		// make list of all possible ontruimers
		for ( $i = 0; $i <= class_settings::get('number_of_levels'); $i++ ) {
			$roles[] = 'O'.$i;
		}

		// check for each ontruimer is has role
		foreach ( $roles as $role ) {
			if ( !$hasRole ) {
				$hasRole = in_array($role, $this->arrRoles);
			}
		}

		return $hasRole;
	}

	function hasAuthorisationTabFire() {
		return ( $this->isAdmin() || in_array('tab_fire', $this->arrAuthorisation) );
	}

	function hasAuthorisationBeoTelephone() {
		return ( $this->isAdmin() || in_array('beo_telephone', $this->arrAuthorisation) );
	}

	function hasAuthorisationTabOntruimer() {
		return ( $this->isAdmin() || in_array('tab_ontruimer', $this->arrAuthorisation) );
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

	function getNiceFirstLastname() {
		$ret = $this->firstname . ' ' . verplaatsTussenvoegselNaarBegin($this->lastname);
		$ret = removeJobFunctionFromName($ret);
		$ret = replaceDoubleTripleSpaces($ret);

		return $ret;
	}

	function getPhoto() {
		$ret = $this->firstname . ' ' . verplaatsTussenvoegselNaarBegin($this->lastname);
		$ret = removeJobFunctionFromName($ret);
		$ret = fixPhotoCharacters($ret);
		$ret = replaceDoubleTripleSpaces($ret);
		$ret = str_replace(' ', '.', $ret);
		$ret .= '.jpg';
		$ret = strtolower($ret);

		return $ret;
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
		return $this->oDepartment;
	}

	//
	function isAdmin() {
		return $this->is_admin;
	}

	function hasInOutTimeAuthorisation() {
		return ( $this->isAdmin() || in_array('inout_time', $this->arrAuthorisation) );
	}

	function isHeadOfDepartment() {
		return in_array('hfd', $this->arrRoles);
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
