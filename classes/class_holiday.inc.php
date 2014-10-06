<?php 

require_once dirname(__DIR__) . "/sites/default/presentornot.settings.php";

// TODOEXPLAIN
class class_holiday {
	protected $id = 0;
	protected $date = '';
	protected $description = '';

	// TODOEXPLAIN
	function __construct($id) {
		global $databases;
		$this->databases = $databases;

		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

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
