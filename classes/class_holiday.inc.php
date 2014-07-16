<?php 

require_once dirname(__DIR__) . "/sites/default/settings.php";

// TODOEXPLAIN
class class_holiday {
	protected $id = 0;
	protected $date = '';
	protected $description = '';

	// TODOEXPLAIN
	function class_holiday($id, $settings) {
		$oConn = new class_mysql($settings, 'presentornot');
		$oConn->connect();

		$this->settings = $settings;
		$this->id = $id;

		$query = "SELECT * FROM Feestdagen WHERE ID=" . $this->getId();
		$res = mysql_query($query, $oConn->getConnection());
		if ($r = mysql_fetch_assoc($res)) {
			$this->date = $r["datum"];
			$this->description = $r["omschrijving"];
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
	public function getDescription() {
		return $this->description;
	}
}
