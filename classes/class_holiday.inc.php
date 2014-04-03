<?php 
// version: 2014-01-20

require_once dirname(__DIR__) . "/sites/default/settings.inc.php";

class class_holiday {
	protected $id = 0;
	protected $date = '';
	protected $description = '';

	// TODOEXPLAIN
	function class_holiday($id, $settings) {
		global $dbhandleTimecard;

		$this->settings = $settings;
		$this->id = $id;

		$query = "SELECT * FROM Feestdagen WHERE ID=" . $this->getId();
		$res = mysql_query($query, $dbhandleTimecard);
		if ($r = mysql_fetch_assoc($res)) {
			$this->date = $r["datum"];
			$this->description = $r["omschrijving"];
		}
		mysql_free_result($res);
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
