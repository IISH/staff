<?php
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";

class class_feestdag {
	private $id;
	private $date;
	private $description;
	private $vooreigenrekening;
	private $isdeleted;
	private $last_refresh;
	private $is_new;
	private $databases;

	// TODOEXPLAIN
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

	// TODOEXPLAIN
	private function initValues() {
		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$query = "SELECT * FROM Feestdagen WHERE ID=" . $this->getId();

		$res = mysql_query($query, $oConn->getConnection());
		if ($r = mysql_fetch_assoc($res)) {
			$this->date = $r["datum"];
			$this->description = $r["omschrijving"];
			$this->vooreigenrekening = $r["vooreigenrekening"];
			$this->isdeleted = $r["isdeleted"];
			$this->last_refresh = $r["last_refresh"];
			$this->is_new = false;
		}
		mysql_free_result($res);
	}

	// TODOEXPLAIN
	public function getId() {
		return $this->id;
	}

	// TODOEXPLAIN
	public function getDate() {
		return $this->date;
	}

	// TODOEXPLAIN
	public function setDate( $date ) {
		$this->date = $date;
	}

	// TODOEXPLAIN
	public function getDescription() {
		return $this->description;
	}

	// TODOEXPLAIN
	public function setDescription( $description ) {
		$this->description = $description;
	}

	// TODOEXPLAIN
	public function getVooreigenrekening() {
		return $this->vooreigenrekening;
	}

	// TODOEXPLAIN
	public function setVooreigenrekening( $vooreigenrekening ) {
		$this->vooreigenrekening = $vooreigenrekening;
	}

	// TODOEXPLAIN
	public function getIsdeleted() {
		return $this->isdeleted;
	}

	// TODOEXPLAIN
	public function setIsdeleted( $isdeleted ) {
		$this->isdeleted = $isdeleted;
	}

	// TODOEXPLAIN
	public function getLastrefresh() {
		return $this->last_refresh;
	}

	// TODOEXPLAIN
	public function getIsnew() {
		return $this->is_new;
	}

	// TODOEXPLAIN
	public function setLastrefresh( $last_refresh ) {
		$this->last_refresh = $last_refresh;
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\n#: " . $this->id . "\n";
	}

	// TODOEXPLAIN
	public function save() {
		if ( $this->getIsnew() ) {
			$this->insert();
		} else {
			$this->update();
		}
	}

	// TODOEXPLAIN
	protected function insert() {
		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$query = "INSERT INTO Feestdagen (ID, datum, omschrijving, vooreigenrekening, isdeleted, last_refresh) VALUES (
			" . $this->id . "
			, '" . addslashes($this->date) . "'
			, '" . addslashes($this->description) . "'
			, " . $this->vooreigenrekening . "
			, " . $this->isdeleted . "
			, '" . addslashes($this->last_refresh) . "'
			) ";
		$result = mysql_query($query, $oConn->getConnection());
	}

	// TODOEXPLAIN
	protected function update() {
		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$query = "UPDATE Feestdagen
			SET datum = '" . addslashes($this->date) . "'
				, omschrijving = '" . addslashes($this->description) . "'
				, vooreigenrekening = " . $this->vooreigenrekening . "
				, isdeleted = " . $this->isdeleted . "
				, last_refresh = '" . addslashes($this->last_refresh) . "'
			WHERE ID=" . $this->id;
		$result = mysql_query($query, $oConn->getConnection());
	}
}
