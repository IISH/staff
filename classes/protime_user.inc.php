<?php
require_once "role_authorisation.inc.php";

class static_protime_user {
	public static function getProtimeUserByLoginName( $loginname ) {
		global $dbConn, $dateOutCriterium;
		$id = 0;

		//
		$loginname = trim($loginname);

		if ( $loginname != '' ) {

			//
			// Remark: don't check date_out here, sometimes they make errors when a person is re-hired they forget to remove the date_out value
			$query = "SELECT * FROM " . Settings::get('protime_tables_prefix') .  "curric WHERE ( CONCAT(TRIM(FIRSTNAME),'.',TRIM(NAME))='" . $loginname . "' OR TRIM(" . Settings::get('curric_loginname') . ")='" . $loginname . "' ) ";

			$stmt = $dbConn->getConnection()->prepare($query);
			$stmt->execute();
			$result = $stmt->fetchAll();
			foreach ($result as $row) {
				$id = $row['PERSNR'];
			}

			// if id still 0, try different way to find protime user
			if ( $id == 0 ) {

				$arrLoginname = explode('.', $loginname);

				$query2 = "SELECT * FROM " . Settings::get('protime_tables_prefix') .  "curric WHERE ". $dateOutCriterium . " AND FIRSTNAME LIKE '%" . $arrLoginname[0] . "%'  AND NAME LIKE '%" . $arrLoginname[1] . "%' ";
				$stmt = $dbConn->getConnection()->prepare($query2);
				$stmt->execute();
				$result = $stmt->fetchAll();
				foreach ($result as $row2) {
					$oEmp2 = new ProtimeUser( $row2['PERSNR']);
					if ( $oEmp2->getLoginname() == $loginname ) {
						$id = $oEmp2->getId();
					}

				}

			}
		}

		return new ProtimeUser( $id );
	}
}

class ProtimeUser {
	protected $protime_id = 0;
	protected $loginname = '';
	protected $firstname = '';
	protected $lastname = '';
	protected $email = '';
	protected $room = '';
	protected $telephones = '';
	protected $photo = '';
	protected $roles = '';
	protected $arrRoles = array();
	protected $arrDepartmentRoleAuthorisation = array();
	protected $arrUserAuthorisation = array();
	protected $is_admin = false;
	protected $department;
	protected $oDepartment;
	protected $arrSubEmployees = array();
	protected $arrUserSettings = array();

	function __construct($protime_id) {
		if ( $protime_id == '' || $protime_id < -1 ) {
			$protime_id = 0;
		}

		if ( $protime_id > 0 ) {
			$this->getProtimeValues( $protime_id );
		}
	}

	public function getProtimeValues( $protime_id ) {
		global $dbConn;

		// reset values
		$query = "SELECT * FROM " . Settings::get('protime_tables_prefix') .  "curric WHERE PERSNR=" . $protime_id;
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$this->protime_id = $protime_id;
			$this->lastname = trim($row["NAME"]);
			$this->firstname = trim($row["FIRSTNAME"]);
			$this->email = trim($row["EMAIL"]);
			$this->loginname = trim(strtolower($row[Settings::get('curric_loginname')]));
			$this->room =  trim($row[Settings::get('curric_room')]);
			$this->telephones =  $row[Settings::get('curric_telephone')];
			$this->photo = trim($row["PHOTO"]);
			$this->department = $row["DEPART"];
			$this->oDepartment = new Department( $row["DEPART"] );
			$this->roles = $row[Settings::get('curric_roles')];

			$this->calculateRoles();
			$this->calculateDepartmentRoleAuthorisation();
			$this->calculateUserAuthorisation();
			$this->findSubEmployees();
			$this->calculateUserSettings();
		}
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

	private function calculateDepartmentRoleAuthorisation() {
		global $dbConn;

		$departmentId = trim($this->department);

		if ( $departmentId == '' || $departmentId == 0 ) {
			return;
		}

		$arrAuthorisation = array();

		// Roles via department
		$query = "SELECT * FROM staff_department_authorisation WHERE DEPART=" . $departmentId;

		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $r) {
			$authorisation = trim( $r["authorisation"] );
			$authorisation = strtolower($authorisation);
			if ( trim($authorisation) != '' ) {
				$arrAuthorisation[] = $authorisation;
			}
		}

		// Roles via user03
		$this->calculateRoles();

		// loop through all roles
		foreach ( $this->arrRoles as $role ) {
			$query = "SELECT * FROM staff_role_authorisation WHERE role='" . $role . "' ";
			$stmt = $dbConn->getConnection()->prepare($query);
			$stmt->execute();
			$result = $stmt->fetchAll();
			foreach ($result as $r) {
				$authorisation = trim( $r["authorisation"] );
				$authorisation = strtolower($authorisation);
				if ( trim($authorisation) != '' ) {
					$arrAuthorisation[] = $authorisation;
				}
			}
		}

		// make unique
		$arrAuthorisation = array_unique( $arrAuthorisation );

		$this->arrDepartmentRoleAuthorisation = $arrAuthorisation;
	}

	private function calculateUserAuthorisation() {
		global $dbConn;

		$arrAuthorisation = array();

		// rights via user authorisation
		$query = "SELECT * FROM staff_user_authorisation WHERE user_id=" . $this->protime_id . " AND isdeleted=0 ";
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $r) {
			$authorisation = strtolower( trim( $r["authorisation"] ) );
			if ( trim($authorisation) != '' ) {
				$arrAuthorisation[] = $authorisation;
			}
		}

		// make unique
		$arrAuthorisation = array_unique( $arrAuthorisation );

		$this->arrUserAuthorisation = $arrAuthorisation;
	}

	private function calculateUserSettings() {
		global $dbConn;

		$arrSettings = array();

		// rights via user authorisation
		$query = "SELECT * FROM staff_user_settings WHERE user_id=" . $this->protime_id;
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $r) {
			$setting = $r["setting"];
			$value = trim( $r["value"] );
			$arrSettings[$setting] = $value;
		}

		$this->arrUserSettings = $arrSettings;
	}

	public function getUserSetting( $setting, $default = '' ) {
		return ( isset($this->arrUserSettings[$setting]) ) ? $this->arrUserSettings[$setting] : $default;
	}

	public function hasAuthorisationTabAbsences() {
		return ( $this->isAdmin() || in_array('tab_absences', $this->arrDepartmentRoleAuthorisation) );
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
		return ( $this->isAdmin() || in_array('tab_fire', $this->arrDepartmentRoleAuthorisation) );
	}

	public function hasAuthorisationReasonOfAbsenceAll() {
		return ( $this->isAdmin() || in_array('reason_of_absence_all', $this->arrDepartmentRoleAuthorisation) );
	}

	public function hasAuthorisationReasonOfAbsenceDepartment() {
		return ( $this->isAdmin() || in_array('reason_of_absence_department', $this->arrDepartmentRoleAuthorisation) );
	}

	public function hasAuthorisationBeoTelephone() {
		return ( $this->isAdmin() || in_array('beo_telephone', $this->arrDepartmentRoleAuthorisation) );
	}

	public function hasAuthorisationTabOntruimer() {
		return ( $this->isAdmin() || in_array('tab_ontruimer', $this->arrDepartmentRoleAuthorisation) );
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
		$ret = trim($this->photo);

		// if photo empty, try to use loginname
		if ( $ret == '' ) {
			$ret = trim($this->loginname);

			// if photo still empty, try to use a combination of firstname and lastname
			if ( $ret == '' ) {
				$ret = $this->firstname . '.' . $this->verplaatsTussenvoegselNaarBegin($this->lastname);
				$ret = $this->removeJobFunctionFromName($ret);
				$ret = $this->fixPhotoCharacters($ret);
				$ret = str_replace(' ', '', $ret);
			}

			$ret .= '.jpg';
			$ret = strtolower($ret);
		}

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
			$ret = str_replace('-', '', $ret);
			$ret = str_replace(' ', '', $ret);
			$ret = strtolower($ret);
		}

		// if no loginname use then session loginname
		if ( $ret == '' || $ret == '.' ) {
			$ret = $_SESSION["loginname"];
		}

		return $ret;
	}

	public function getRoom() {
		return $this->room;
	}

	public function getTelephones() {
		return $this->telephones;
	}

	public function getEmail() {
		return $this->email;
	}

	public function getDepartment() {
		return $this->oDepartment;
	}

	//
	public function isSuperAdmin() {
		return ( in_array('superadmin', $this->arrUserAuthorisation) );
	}

	//
	public function isAdmin() {
		return ( in_array('admin', $this->arrUserAuthorisation) || $this->isSuperAdmin() );
	}

	public function hasInOutTimeAuthorisation() {
		return ( $this->isAdmin() || in_array('inout_time', $this->arrDepartmentRoleAuthorisation) );
	}

	public function isHeadOfDepartment() {
		return ( $this->isAdmin() || in_array('hfd', $this->arrRoles) );
	}

	public function isLoggedIn() {
//		if ( $this->protime_id > 0 ) {
		if ( $_SESSION["loginname"] != '' ) {
			return true;
		}

		return false;
	}

	public function checkLoggedIn() {
		global $protect;

		// TODO: Opmerking: ook controleren of session loginname leeg is, want als de gebruiker wel in ActiveDirectory zit
		// maar niet in protime, dan heeft men wel een loginname maar geen protime_id
		if ( $this->protime_id < 1 && $_SESSION["loginname"] == '' ) {
			Header("Location: login.php?burl=" . URLencode($protect->getShortUrl()));
			die(Translations::get('go_to') . " <a href=\"login.php?burl=" . URLencode($protect->getShortUrl()) . "\">next</a>");
		}
	}

	public function getFavourites( $type ) {
		global $dbConn;

		$ids = array();
		$ids[] = '0';

		$query = 'SELECT * FROM staff_favourites WHERE user=\'' . $this->getLoginname() . '\' AND type=\'' . $type . '\' ';
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$ids[] = $row["ProtimeID"];
		}

		return $ids;
	}

	public function findSubEmployees() {
		global $dbConn;
		if ( $this->isHeadOfDepartment() ) {

			$department = $this->department;
			if ( $department > 0 ) {
				// reset values
				$query = "SELECT PERSNR FROM " . Settings::get('protime_tables_prefix') .  "curric WHERE DEPART=" . $department . " AND PERSNR<>" . $this->protime_id;
				$stmt = $dbConn->getConnection()->prepare($query);
				$stmt->execute();
				$result = $stmt->fetchAll();
				foreach ($result as $row) {
					$this->arrSubEmployees[] = $row["PERSNR"];
				}
			}
		}
	}

	public function isHeadOfEmployee($subEmployeeId) {
		return in_array($subEmployeeId, $this->arrSubEmployees);
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
		$photo = str_replace('-', '', $photo);
		$photo = str_replace(' ', '', $photo);

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
