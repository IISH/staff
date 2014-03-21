<?php 
// version: 2014-01-20

ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_NOTICE);

require_once "settings.inc.php";

class class_feestdag {
	var $id;
	var $date;
	var $description;

	// TODOEXPLAIN
	function class_feestdag($id, $settings) {
		$this->settings = $settings;
		$this->id = $id;
		$this->date = '';
		$this->description = '';

		$this->initValues();
	}

	// TODOEXPLAIN
	private function initValues() {
		global $dbhandleTimecard;

		$query = "SELECT * FROM Feestdagen WHERE ID=" . $this->getId();

		$res = mssql_query($query, $dbhandleTimecard);
		if ($r = mssql_fetch_assoc($res)) {
			$this->date = $r["datum"];
			$this->description = $r["omschrijving"];
		}
		mssql_free_result($res);
	}

	public function getId() {
		return $this->id;
	}

	public function getDate() {
		return $this->date;
	}

	public function getDescription() {
		return $this->description;
	}
}
