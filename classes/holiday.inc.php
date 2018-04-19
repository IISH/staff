<?php 

require_once dirname(__DIR__) . "/sites/default/settings.php";

class Holiday {
	protected $id = 0;
	protected $date = '';
	protected $description = '';

	function __construct($id) {
		global $dbConn;

		$this->id = $id;

		$query = "SELECT * FROM staff_feestdagen WHERE ID=" . $this->getId();
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		if ( $r = $stmt->fetch() ) {
			$this->date = $r["datum"];
			$this->description = $r["omschrijving"];
		}
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
