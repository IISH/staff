<?php 

require_once dirname(__DIR__) . "/sites/default/staff.settings.php";

class Holiday {
	protected $id = 0;
	protected $date = '';
	protected $description = '';

	function __construct($id) {
		global $databases;
		$this->databases = $databases;

		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$this->id = $id;

		$query = "SELECT * FROM Staff_feestdagen WHERE ID=" . $this->getId();
		$res = mysql_query($query, $oConn->getConnection());
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
