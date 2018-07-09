<?php
require_once "role_authorisation.inc.php";

class static_protime_user {
	public static function getProtimeUserByLoginName( $loginname ) {
		global $dbConn;
		$id = array();

		//
		$loginname = trim($loginname);

		if ( $loginname != '' ) {

			// Remark: don't check date_out here, sometimes they make errors when a person is re-hired they forget to remove the date_out value
			$query = "
				SELECT *
				FROM protime_curric
					WHERE
						(
							CONCAT(TRIM(FIRSTNAME),'.',TRIM(NAME))='$loginname'
							OR TRIM(" . Settings::get('curric_loginname') . ")='$loginname'
							OR TRIM(" . Settings::get('curric_loginname_knaw') . ")='$loginname'
						) " . Settings::get('exclude_protime_users') . "
				ORDER BY PERSNR ASC
				";
//preprint( $query );
			$stmt = $dbConn->getConnection()->prepare($query);
			$stmt->execute();
			$result = $stmt->fetchAll();
			foreach ($result as $row) {
				$id[] = $row['PERSNR'];
			}

			// if id still 0, try different way to find protime user
			if ( count( $id ) == 0 ) {
				if ( strpos($loginname, '.') === false ) {
					// try to find ID by using KNAW login name
					$tmpId = Synonyms::getIdByUsingKnawLoginName($loginname);
				} else {
					// try to find ID by using IISG login name
					$tmpId = Synonyms::getIdByUsingIisgLoginName($loginname);
				}

				if ( $tmpId != '' && $tmpId != '0' ) {
					$id[] = $tmpId;
				}
			}

			// if id still 0, try different way to find protime user
			if ( count( $id ) == 0 ) {
				// TODO TODOGCU
				// DIT STUKJE MOET EIGENLIJK HELEMAAL WEG
				// ER MOET EIGENLIJK EEN MELDING KOMEN DAT MEN WEL INGELOGD MAAR DAT MEN GEWONE GEBRUIKERS RECHTEN HEEFT
				// OMDAT ER GEEN KOPPELING GEMAAKT KAN WORDEN MET DE JUISTE PROTIME RECORD
				// WAARSCHIJNLIJK WEGENS TUSSENVOEGSELS, SPATIES, STREEPJES
				// IN HET VELD LOGINNAAM (IN DE TAB VARIABLES MOET BIJ DE PERSOON HET VELD LOGINNAAM INGEVULD WORDEN
				// MET DE LOGIN DIE ZE GEBRUIKEN VOOR STAFF.IISG.NL MEESTAL FIRSTNAME.LASTNAME ZONDER SPATIES
				$arrLoginname = explode('.', $loginname);
				if ( count( $arrLoginname ) >= 2 ) {
					// dirty solution
					$arrLoginname[1] = trim(Misc::stripLeftPart($arrLoginname[1], 'vanden'));
					$arrLoginname[1] = trim(Misc::stripLeftPart($arrLoginname[1], 'vander'));
					$arrLoginname[1] = trim(Misc::stripLeftPart($arrLoginname[1], 'vande'));
					$arrLoginname[1] = trim(Misc::stripLeftPart($arrLoginname[1], 'van'));

					$query2 = "
						SELECT *
						FROM protime_curric
						WHERE FIRSTNAME LIKE '%" . $arrLoginname[0] . "%'  AND NAME LIKE '%" . $arrLoginname[1] . "%'
						" . Settings::get('exclude_protime_users') . "
						ORDER BY PERSNR ASC
					";
//preprint( $query2 );
					$stmt = $dbConn->getConnection()->prepare($query2);
					$stmt->execute();
					$result = $stmt->fetchAll();
					foreach ($result as $row2) {
						$oEmp2 = new ProtimeUser( $row2['PERSNR']);
						// try to calculate loginname and compare it with entered loginname
						if ( $oEmp2->getLoginname() == $loginname ) {
							$id[] = $oEmp2->getId();
						}
					}
				}
			}
		}

		if ( count( $id ) == 0 ) {
			$id[] = 0;
		}

		return new ProtimeUser( $id );
	}
}

class ProtimeUser {
	protected $protime_id = 0;
	protected $loginname = '';
	protected $loginnameknaw = '';
	protected $firstname = '';
	protected $lastname = '';
	protected $email = '';
	protected $room = '';
	protected $telephones = '';
	protected $photo = '';
	protected $roles = '';
	protected $arrRoles = array();
	protected $arrDepartmentRoleAuthorisation = array();
	protected $is_admin = false;
	protected $department = 0;
	protected $oDepartment;
	protected $arrSubEmployees = array();
	protected $arrUserSettings = array();
	protected $dateIn;
	protected $dateOut;
	protected $arrUserAuthorisation = array();
	protected $badgenr = '';

	function __construct($protimeId) {
		if ( !is_array( $protimeId ) ) {
			$protimeId = array( $protimeId );
		}

		if ( count( $protimeId ) == 0 ) {
			$protimeId[] = 0;
		}

		$this->getProtimeValues( $protimeId );
	}

	public function getProtimeValues( $protime_id ) {
		global $dbConn;
		// reset values
		$query = "SELECT * FROM protime_curric WHERE PERSNR IN ( " . implode(',', $protime_id) . " ) ";
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$this->protime_id = $row['PERSNR'];
			$this->lastname = trim($row["NAME"]);
			$this->firstname = trim($row["FIRSTNAME"]);
			$this->email = trim($row["EMAIL"]);
			$this->loginname = trim(strtolower($row[Settings::get('curric_loginname')]));
			$this->loginnameknaw = trim(strtolower($row[Settings::get('curric_loginname_knaw')]));
			$this->room =  trim($row[Settings::get('curric_room')]);
			$this->telephones =  $row[Settings::get('curric_telephone')];
			$this->photo = trim($row["PHOTO"]);
			$this->department = $row["DEPART"];
			$this->dateIn = $row["DATE_IN"];
			$this->dateOut = $row["DATE_OUT"];
			$this->badgenr = $row["BADGENR"];

			$this->oDepartment = new Department( $row["DEPART"] );
			$this->roles = $row[Settings::get('curric_roles')];

			$this->calculateRoles();
			$this->findSubEmployees();
			$this->calculateUserSettings();
			$this->calculateDepartmentRoleAuthorisation();
			$this->calculateUserAuthorisation();
		}

		// outside the loop
		// get extra authorisation via session name, this is for users not in protime but who need extra authorisation
//		$this->calculateUserAuthorisationViaSessionName();
	}

	private function calculateRoles() {
		$roles = trim($this->roles);

		//$arrRoles = array();
		$arrRoles = $this->arrRoles;

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

		$arrAuthorisation = $this->arrDepartmentRoleAuthorisation;

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

	private function calculateUserSettings() {
		global $dbConn;

		$arrSettings = $this->arrUserSettings;

		// rights via user authorisation
		$query = "SELECT * FROM staff_user_settings WHERE user_id IN ( " . $this->protime_id . " ) ";
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
		return ( $this->isAdmin() || in_array('tab_fire', $this->arrDepartmentRoleAuthorisation) || in_array('tab_fire', $this->arrUserAuthorisation) );
	}

	public function hasAuthorisationReasonOfAbsenceAll() {
		return ( $this->isAdmin() || in_array('reason_of_absence_all', $this->arrDepartmentRoleAuthorisation) || in_array('reason_of_absence_all', $this->arrUserAuthorisation) );
	}

	public function hasAuthorisationReasonOfAbsenceDepartment() {
		return ( $this->isAdmin() || in_array('reason_of_absence_department', $this->arrDepartmentRoleAuthorisation) || in_array('reason_of_absence_department', $this->arrUserAuthorisation) );
	}

	public function hasAuthorisationBeoTelephone() {
		return ( $this->isAdmin() || in_array('beo_telephone', $this->arrDepartmentRoleAuthorisation) || in_array('beo_telephone', $this->arrUserAuthorisation) );
	}

	public function hasAuthorisationTabOntruimer() {
		return ( $this->isAdmin() || in_array('tab_ontruimer', $this->arrDepartmentRoleAuthorisation) || in_array('tab_ontruimer', $this->arrUserAuthorisation) );
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

	public function getNameForFirePage() {
		$ret =  $this->lastname . ', ' . $this->firstname;
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
				$ret = Misc::fixBrokenChars($ret);
				$ret = $this->removeJobFunctionFromName($ret);
				$ret = $this->fixPhotoCharacters($ret);
				$ret = str_replace(' ', '', $ret);
			}

			$ret .= '.jpg';
		}

        $ret = strtolower($ret);

//		preprint( $ret );

		return $ret;
	}

	public function getPhotoImg() {
		global $oWebuser;

		$photo = $this->getPhoto();
		$alttitle = '';

		if ( checkPhotoExists(Settings::get('staff_images_directory') . $photo) ) {
			$photo = Settings::get('staff_images_directory') . $photo;
		} else {
			if ( $oWebuser->isAdmin() ) {
				$alttitle = 'Missing photo: &quot;' . Settings::get('staff_images_directory') . $photo . '&quot;';
			}
			$photo = Settings::get('noimage_file');
		}
		return "<img src=\"$photo\" style=\"height:140px;\" title=\"$alttitle\">";
	}

	public function getLastname() {
		return $this->fixBrokenChars($this->lastname);
	}

	public function getLoginnameKnaw() {
		return trim($this->loginnameknaw);
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

	public function getDateIn() {
		return $this->dateIn;
	}

	public function getDateOut() {
		return $this->dateOut;
	}

	public function isLoggedIn() {
		if ( $_SESSION["loginname"] != '' ) {
			return true;
		}

		return false;
	}

	public function getBadgenr() {
		return $this->badgenr;
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

		$query = 'SELECT * FROM staff_favourites WHERE ( user=\'' . $this->getLoginname() . '\' OR user_iisg=\'' . $this->getLoginname() . '\' OR user=\'' . $_SESSION['loginname'] . '\' OR user_iisg=\'' . $_SESSION['loginname'] . '\' ) AND type=\'' . $type . '\' ';
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
				$query = "SELECT PERSNR FROM protime_curric WHERE DEPART=" . $department . " AND PERSNR NOT IN ( " . $this->protime_id . " ) ";
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
		$string = str_ireplace('(oproep)', '', $string);
		$string = str_ireplace('(rec)', '', $string);
		$string = str_ireplace('(receptie)', '', $string);
		$string = str_ireplace('(kantine)', '', $string);
		$string = str_ireplace('(uu)', '', $string);
		$string = str_ireplace('(pm)', '', $string);

		return $string;
	}

	private function fixPhotoCharacters( $photo ) {
		$photo =  iconv('Windows-1252', 'ASCII//TRANSLIT//IGNORE', $photo);
		$photo = str_replace('`', '', $photo);
		$photo = str_replace('"', '', $photo);
		$photo = str_replace('-', '', $photo);
		$photo = str_replace(' ', '', $photo);
		$photo = str_replace('/', '', $photo);
		$photo = str_replace('\\', '', $photo);
		$photo = str_replace('|', '', $photo);
		$photo = str_replace('&ouml;', 'o', $photo);
		$photo = str_replace("&aacute;", 'a', $photo);

//$a = str_split($photo);
//preprint( $a );

		return $photo;
	}

	private function fixBrokenChars($text) {
		return htmlentities($text, ENT_COMPAT | ENT_XHTML, 'ISO-8859-1', true);
	}

	private function verplaatsTussenvoegselNaarBegin( $text ) {
		$array = array( ' van den', ' van der', ' van de', ' van', ' de', ' el' );

		foreach ( $array as $t ) {
			// check if last part
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

	public function saveSetting($field, $value) {
		global $dbConn;

		// TODO
		$field = addslashes($field);
		$value = addslashes($value);

		$query_update = "INSERT INTO staff_user_settings (`user_id`, `setting`, `value`) VALUES (" . $this->protime_id . ", '$field', '$value') ON DUPLICATE KEY UPDATE `value`='$value' ";
		$stmt = $dbConn->getConnection()->prepare($query_update);
		$stmt->execute();
	}

	public function getAuthorisations() {
		$arr = array();

//preprint( $this->getLoginname() );

		if ( $this->isSuperAdmin() ) {
			$arr[] = 'Superadmin';
		} elseif ( $this->isAdmin() ) {
			$arr[] = 'Admin';
		}

		if (  $this->isBhv() ) {
			$arr[] = 'BHV';
		}

		if (  $this->isEhbo() ) {
			$arr[] = 'EHBO';
		}

		if (  $this->isOntruimer() ) {
			$arr[] = 'Ontruimer';
		}

		if (  $this->hasAuthorisationTabAbsences() ) {
			$arr[] = 'TabAbsences';
		}

		if (  $this->hasAuthorisationTabFire() ) {
			$arr[] = 'TabFire';
		}

		if (  $this->hasAuthorisationTabOntruimer() ) {
			$arr[] = 'TabOntruimer';
		}

		if (  $this->hasAuthorisationReasonOfAbsenceAll() ) {
			$arr[] = 'ReasonOfAbsenceAll';
		} elseif (  $this->hasAuthorisationReasonOfAbsenceDepartment() ) {
			$arr[] = 'ReasonOfAbsenceDepartment';
		}

		if (  $this->hasAuthorisationBeoTelephone() ) {
			$arr[] = 'BeoTelephone';
		}

		//
		if ( count($arr) == 0 ) {
			$arr[] = 'default authorisation';
		}

		return $arr;
	}

	private function calculateUserAuthorisation() {
		global $dbConn;

		if ( trim($_SESSION['loginname']) != '' ) {
			$arrAuthorisation = $this->arrUserAuthorisation;

			// rights via user authorisation
			//		$query = "SELECT * FROM staff_user_authorisation WHERE user_id IN ( " . $this->protime_id . " ) AND isdeleted=0 ";
			$query = "SELECT * FROM staff_user_authorisation_via_loginname WHERE loginname = '" . addslashes(trim($_SESSION['loginname'])) . "' AND isdeleted=0 ";
//preprint( $query );
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
//			preprint( $arrAuthorisation );
			$this->arrUserAuthorisation = $arrAuthorisation;
		}
	}

//	private function calculateUserAuthorisationViaSessionName() {
//		global $dbConn;
//
//		if ( trim($_SESSION['loginname']) != '' ) {
//			$arrAuthorisation = $this->arrUserAuthorisation;
//
//			// rights via user authorisation
//	//		$query = "SELECT * FROM staff_user_authorisation WHERE user_id IN ( " . $this->protime_id . " ) AND isdeleted=0 ";
//			$query = "SELECT * FROM staff_user_authorisation_via_loginname WHERE loginname = '" . addslashes(trim($_SESSION['loginname'])) . "' AND isdeleted=0 ";
////preprint( $query );
//			$stmt = $dbConn->getConnection()->prepare($query);
//			$stmt->execute();
//			$result = $stmt->fetchAll();
//			foreach ($result as $r) {
//				$authorisation = strtolower( trim( $r["authorisation"] ) );
//				if ( trim($authorisation) != '' ) {
//					$arrAuthorisation[] = $authorisation;
//				}
//			}
//
//			// make unique
//			$arrAuthorisation = array_unique( $arrAuthorisation );
//preprint( $arrAuthorisation );
//			$this->arrUserAuthorisation = $arrAuthorisation;
//		}
//	}

	public function hasAuthorisationTabAbsences() {
		return ( $this->isAdmin() || in_array('tab_absences', $this->arrDepartmentRoleAuthorisation) || in_array('tab_absences', $this->arrUserAuthorisation) );
	}

	//
	public function isAdmin() {
		return ( in_array('admin', $this->arrUserAuthorisation) || $this->isSuperAdmin() );
	}

	//
	public function isSuperAdmin() {
		return ( in_array('superadmin', $this->arrUserAuthorisation) );
	}

	public function hasInOutTimeAuthorisation() {
//		preprint( $this->arrDepartmentRoleAuthorisation );
//		preprint( in_array('inout_time', $this->arrDepartmentRoleAuthorisation) . '-aaa');
//		preprint( in_array('inout_time', $this->arrUserAuthorisation) . '-bbb');
		return ( $this->isAdmin() || in_array('inout_time', $this->arrDepartmentRoleAuthorisation) || in_array('inout_time', $this->arrUserAuthorisation) );
	}

	public function isHeadOfDepartment() {
		return ( $this->isAdmin() || in_array('hfd', $this->arrRoles) );
	}
}
