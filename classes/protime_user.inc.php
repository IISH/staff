<?php
require_once "role_authorisation.inc.php";

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
			$query = "SELECT * FROM " . Settings::get('protime_tables_prefix') .  "CURRIC WHERE ( DATE_OUT='0' OR DATE_OUT>='" . date("Ymd") . "' ) AND ( CONCAT(TRIM(FIRSTNAME),'.',TRIM(NAME))='" . $loginname . "' OR TRIM(" . Settings::get('curric_loginname') . ")='" . $loginname . "' ) ";

			$resultReset = mysql_query($query, $oProtime->getConnection());
			if ($row = mysql_fetch_assoc($resultReset)) {
				$id = $row['PERSNR'];

			}
			mysql_free_result($resultReset);

			// if id still 0, try different way to find protime user
			if ( $id == 0 ) {

				$arrLoginname = explode('.', $loginname);

				$query2 = "SELECT * FROM " . Settings::get('protime_tables_prefix') .  "CURRIC WHERE ( DATE_OUT='0' OR DATE_OUT>='" . date("Ymd") . "' ) AND FIRSTNAME LIKE '%" . $arrLoginname[0] . "%' ";
				$resultReset2 = mysql_query($query2, $oProtime2->getConnection());
				while ($row2 = mysql_fetch_assoc($resultReset2) ) {
					$oEmp2 = new ProtimeUser( $row2['PERSNR']);
					if ( $oEmp2->getLoginname() == $loginname ) {
						$id = $oEmp2->getId();
					}

				}
				mysql_free_result($resultReset2);

			}
		}

		return new ProtimeUser( $id );
	}
}

class ProtimeUser {
	protected $protime_id = 0;
	protected $isLoaded_loginname = false;
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
	protected $arrSubEmployees = array();

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

	public function getProtimeValues( $protime_id ) {
		$oProtime = new class_mysql($this->databases['default']);
		$oProtime->connect();

		// reset values
		$query = "SELECT * FROM " . Settings::get('protime_tables_prefix') .  "CURRIC WHERE PERSNR=" . $protime_id;
		$resultReset = mysql_query($query, $oProtime->getConnection());
		if ($row = mysql_fetch_assoc($resultReset)) {
			$this->protime_id = $protime_id;
			$this->lastname = trim($row["NAME"]);
			$this->firstname = trim($row["FIRSTNAME"]);
			$this->email = trim($row["EMAIL"]);
			$this->loginname = trim(strtolower($row[Settings::get('curric_loginname')]));
			$this->room =  trim($row[Settings::get('curric_room')]);
			$this->telephone =  trim($row[Settings::get('curric_telephone')]);
			$this->department = $row["DEPART"];
			$this->oDepartment = new Department( $row["DEPART"] );
			$this->roles = $row[Settings::get('curric_roles')];

			$this->calculateRoles();
			$this->calculateAuthorisation();
			$this->calculateIsAdmin();
			$this->findSubEmployees();
		}
		mysql_free_result($resultReset);
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

		// Roles via department
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

		// Roles via user03
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

	public function hasAuthorisationTabAbsences() {
		return ( $this->isAdmin() || in_array('tab_absences', $this->arrAuthorisation) );
	}

	public function isBhv() {
		return in_array('bhv', $this->arrRoles);
	}

	public function isEhbo() {
		return ( in_array('ehbo', $this->arrRoles) || in_array('e', $this->arrRoles) );
	}

	public function isOntruimer() {
		$hasRole = false;
		$roles = array();

		// make list of all possible ontruimers
		for ( $i = 0; $i <= Settings::get('number_of_levels'); $i++ ) {
			$roles[] = 'o'.$i;
		}

		// check for each ontruimer is has role
		foreach ( $roles as $role ) {
			if ( !$hasRole ) {
				$hasRole = in_array($role, $this->arrRoles);
			}
		}

		return $hasRole;
	}

	public function hasAuthorisationTabFire() {
		return ( $this->isAdmin() || in_array('tab_fire', $this->arrAuthorisation) );
	}

	public function hasAuthorisationReasonOfAbsenceAll() {
		return ( $this->isAdmin() || in_array('reason_of_absence_all', $this->arrAuthorisation) );
	}

	public function hasAuthorisationReasonOfAbsenceDepartment() {
		return ( $this->isAdmin() || in_array('reason_of_absence_department', $this->arrAuthorisation) );
	}

	public function hasAuthorisationBeoTelephone() {
		return ( $this->isAdmin() || in_array('beo_telephone', $this->arrAuthorisation) );
	}

	public function hasAuthorisationTabOntruimer() {
		return ( $this->isAdmin() || in_array('tab_ontruimer', $this->arrAuthorisation) );
	}

	private function calculateIsAdmin() {
		// check if and set is_admin
		$arr = splitStringIntoArray(Settings::get('superadmin'), "/[^a-zA-Z0-9\.]/");
		if ( in_array( $this->getLoginname(), $arr ) ) {
			$this->is_admin = true;
		}
	}

	public function getId() {
		return $this->protime_id;
	}

	public function getFirstname() {
		return $this->fixBrokenChars($this->firstname);
	}

	public function getNiceFirstLastname() {
		$ret = $this->firstname . ' ' . $this->verplaatsTussenvoegselNaarBegin($this->lastname);
		$ret = $this->removeJobFunctionFromName($ret);
		$ret = replaceDoubleTripleSpaces($ret);
		$ret = $this->fixBrokenChars($ret);

		return $ret;
	}

	public function getPhoto() {
		$ret = trim($this->loginname);
		if ( $ret == '' ) {
			$ret = $this->firstname . '.' . $this->verplaatsTussenvoegselNaarBegin($this->lastname);
		}
		$ret = $this->removeJobFunctionFromName($ret);
		$ret = $this->fixPhotoCharacters($ret);

		$ret = str_replace(' ', '', $ret);
		$ret .= '.jpg';
		$ret = strtolower($ret);

		return $ret;
	}

	public function getLastname() {
		return $this->fixBrokenChars($this->lastname);
	}

	public function getLoginname() {
		$ret = trim($this->loginname);

		if ( $ret == '' ) {
			$ret = $this->firstname . '.' . $this->verplaatsTussenvoegselNaarBegin($this->lastname);
			$ret = $this->removeJobFunctionFromName($ret);
			$ret = str_replace(' ', '', $ret);
			$ret = strtolower($ret);
		}

		return $ret;
	}

	public function getRoom() {
		return $this->room;
	}

	public function getTelephone() {
		return $this->cleanUpTelephone($this->telephone);
	}

	public function getEmail() {
		return $this->email;
	}

	public function getDepartment() {
		return $this->oDepartment;
	}

	//
	public function isAdmin() {
		return $this->is_admin;
	}

	public function hasInOutTimeAuthorisation() {
		return ( $this->isAdmin() || in_array('inout_time', $this->arrAuthorisation) );
	}

	public function isHeadOfDepartment() {
		return ( $this->isAdmin() || in_array('hfd', $this->arrRoles) );
	}

	public function isLoggedIn() {
		if ( $this->protime_id > 0 ) {
			return true;
		}

		return false;
	}

	public function checkLoggedIn() {
		global $protect;

		if ( $this->protime_id < 1 ) {
			Header("Location: login.php?burl=" . URLencode($protect->getShortUrl()));
			die(Translations::get('go_to') . " <a href=\"login.php?burl=" . URLencode($protect->getShortUrl()) . "\">next</a>");
		}
	}

	public function getFavourites( $type ) {
		global $databases;

		$oConn = new class_mysql($databases['default']);
		$oConn->connect();

		$ids = array();
		$ids[] = '0';

		$query = 'SELECT * FROM Staff_favourites WHERE user=\'' . $this->getLoginname() . '\' AND type=\'' . $type . '\' ';
		$result = mysql_query($query, $oConn->getConnection());
		while ( $row = mysql_fetch_assoc($result) ) {
			$ids[] = $row["ProtimeID"];
		}
		mysql_free_result($result);

		return $ids;
	}

	public function findSubEmployees() {
		if ( $this->isHeadOfDepartment() ) {

			$department = $this->department;
			if ( $department > 0 ) {
				$oProtime = new class_mysql($this->databases['default']);
				$oProtime->connect();

				// reset values
				$query = "SELECT PERSNR FROM " . Settings::get('protime_tables_prefix') .  "CURRIC WHERE DEPART=" . $department . " AND PERSNR<>" . $this->protime_id;
				$resultReset = mysql_query($query, $oProtime->getConnection());
				while ($row = mysql_fetch_assoc($resultReset)) {
					$this->arrSubEmployees[] = $row["PERSNR"];
				}
				mysql_free_result($resultReset);

			}
		}
	}

	public function isHeadOfEmployee($subEmployeeId) {
		return in_array($subEmployeeId, $this->arrSubEmployees);
	}

	private function cleanUpTelephone($telephone) {
		$retval = $telephone;

		// remove some dirty data from telephone
		$retval = str_replace(array("/", "(", ")", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"), ' ', $retval);

		//
		while ( strpos($retval, '  ') !== false ) {
			$retval = str_replace('  ',' ', $retval);
		}

		//
		$retval = trim($retval);

		return $retval;
	}

	private function removeJobFunctionFromName( $string ) {
		$string = str_ireplace('(vrijwilliger)', '', $string);
		$string = str_ireplace('(vrijwillig)', '', $string);
		$string = str_ireplace('(stz)', '', $string);
		$string = str_ireplace('(rec)', '', $string);

		return $string;
	}

	private function fixPhotoCharacters( $photo ) {
		$photo =  iconv('Windows-1252', 'ASCII//TRANSLIT//IGNORE', $photo);
		$photo = str_replace('`', '', $photo);
		$photo = str_replace('"', '', $photo);
		return $photo;
	}

	private function fixBrokenChars($text) {
		return htmlentities($text, ENT_COMPAT | ENT_XHTML, 'ISO-8859-1', true);
	}

	private function verplaatsTussenvoegselNaarBegin( $text ) {
		$array = array( ' van den', ' van der', ' van', ' de', ' el' );

		foreach ( $array as $t ) {
			if ( strtolower(substr($text, -strlen($t))) == strtolower($t) ) {
				$text = trim($t . ' ' . substr($text, 0, strlen($text)-strlen($t)));
			}
		}

		return $text;
	}

	public function getOntruimerVerdieping() {
		$retval = '';

		//
		$arrRole = $this->arrRoles;

		// loop
		foreach ( $arrRole as $item ) {
			// check if o<level>
			if ( strlen($item) == 2 && substr($item, 0, 1) == 'o' ) {
				// get level
				$retval .= ' ' . substr($item, -1);
			}
		}

		//
		$retval = trim($retval);

		return $retval;
	}

	public function getRolesForFirePage() {
		$ret = '';
		$separator = '';

		if ( $this->isBhv() ) {
			$ret .= $separator . Translations::get('lbl_ert');
			$separator = ', ';
		}

		if ( $this->isEhbo() ) {
			$ret .= $separator . Translations::get('lbl_firstaid');
			$separator = ', ';
		}

		if ( $this->isOntruimer() ) {
			$ret .= $separator . Translations::get('lbl_evacuator_short') . $this->getOntruimerVerdieping();
		}

		return $ret;
	}
}