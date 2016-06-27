<?php
require_once dirname(__FILE__) . "/../sites/default/staff.settings.php";

class Feestdag {
	private $id;
	private $date;
	private $description;
	private $vooreigenrekening;
	private $isdeleted;
	private $last_refresh;
	private $is_new;
	private $databases;

	function __construct($id) {
		global $databases;
		$this->databases = $databases;

		$this->id = $id;
		$this->date = '';
		$this->description = '';
		$this->vooreigenrekening = 0;
		$this->isdeleted = 0;
		$this->last_refresh = '';
		$this->is_new = true;

		$this->initValues();
	}

	private function initValues() {
		global $dbConn;

		$query = "SELECT * FROM staff_feestdagen WHERE ID=" . $this->getId();
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		if ( $r = $stmt->fetch() ) {
			$this->date = $r["datum"];
			$this->description = $r["omschrijving"];
			$this->vooreigenrekening = $r["vooreigenrekening"];
			$this->isdeleted = $r["isdeleted"];
			$this->last_refresh = $r["last_refresh"];
			$this->is_new = false;
		}
	}

	public function getId() {
		return $this->id;
	}

	public function getDate() {
		return $this->date;
	}

	public function setDate( $date ) {
		$this->date = $date;
	}

	public function getDescription() {
		return $this->description;
	}

	public function setDescription( $description ) {
		$this->description = $description;
	}

	public function getVooreigenrekening() {
		return $this->vooreigenrekening;
	}

	public function setVooreigenrekening( $vooreigenrekening ) {
		$this->vooreigenrekening = $vooreigenrekening;
	}

	public function getIsdeleted() {
		return $this->isdeleted;
	}

	public function setIsdeleted( $isdeleted ) {
		$this->isdeleted = $isdeleted;
	}

	public function getLastrefresh() {
		return $this->last_refresh;
	}

	public function getIsnew() {
		return $this->is_new;
	}

	public function setLastrefresh( $last_refresh ) {
		$this->last_refresh = $last_refresh;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n#: " . $this->id . "\n";
	}

	public function save() {
		if ( $this->getIsnew() ) {
			$this->insert();
		} else {
			$this->update();
		}
	}

	protected function insert() {
		global $dbConn;

		$query = "INSERT INTO staff_feestdagen (ID, datum, omschrijving, vooreigenrekening, isdeleted, last_refresh) VALUES (
			" . $this->id . "
			, '" . addslashes($this->date) . "'
			, '" . addslashes($this->description) . "'
			, " . $this->vooreigenrekening . "
			, " . $this->isdeleted . "
			, '" . addslashes($this->last_refresh) . "'
			) ";
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
	}

	protected function update() {
		global $dbConn;

		$query = "UPDATE staff_feestdagen
			SET datum = '" . addslashes($this->date) . "'
				, omschrijving = '" . addslashes($this->description) . "'
				, vooreigenrekening = " . $this->vooreigenrekening . "
				, isdeleted = " . $this->isdeleted . "
				, last_refresh = '" . addslashes($this->last_refresh) . "'
			WHERE ID=" . $this->id;
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
	}
}
