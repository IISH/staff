<?php
require_once "class_mysql.inc.php";

class class_role_authorisation {
	private $role = '';
	private $description_lang1 = '';
	private $description_lang2 = '';
	private $field_inout_time = 0;
	private $field_reason_of_absence = 0;
	private $field_beo_telephone = 0;
	private $tab_absences = 0;
	private $tab_ontruimer = 0;
	private $tab_fire = 0;

	// TODOEXPLAIN
	function __construct($role) {
		global $databases;
		$this->databases = $databases;

		$this->initValues( $role );
	}

	// TODOEXPLAIN
	private function initValues( $role ) {
		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$query = "SELECT * FROM Staff_role_authorisation WHERE role='" . $role . "' ";

		$res = mysql_query($query, $oConn->getConnection());
		if ($r = mysql_fetch_assoc($res)) {
			$this->role = $role;
			$this->description_lang1 = $r["description_lang1"];
			$this->description_lang2 = $r["description_lang2"];
			$this->field_inout_time = $r["field_inout_time"];
			$this->field_reason_of_absence = $r["field_reason_of_absence"];
			$this->field_beo_telephone = $r["field_beo_telephone"];
			$this->tab_absences = $r["tab_absences"];
			$this->tab_ontruimer = $r["tab_ontruimer"];
			$this->tab_fire = $r["tab_fire"];
		}
		mysql_free_result($res);
	}

	public function getRole() {
		return $this->role;
	}

	public function getDescriptionLang1() {
		return $this->description_lang1;
	}

	public function getDescriptionLang2() {
		return $this->description_lang2;
	}

	public function getFieldInoutTime() {
		return $this->field_inout_time;
	}

	public function getFieldReasonOfAbsence() {
		return $this->field_reason_of_absence;
	}

	public function getFieldBeoTelephone() {
		return $this->field_beo_telephone;
	}

	public function getTabAbsences() {
		return $this->tab_absences;
	}

	public function getTabOntruimer() {
		return $this->tab_ontruimer;
	}

	public function getTabFire() {
		return $this->tab_fire;
	}
}
