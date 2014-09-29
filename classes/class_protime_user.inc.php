<?php

// TODOEXPLAIN
class class_protime_user {
	protected $protime_id = 0;
	protected $databases;
	protected $firstname = '';
	protected $lastname = '';

	// TODOEXPLAIN
	function __construct($protime_id) {
		global $databases;
		$this->databases = $databases;

		if ( $protime_id == '' || $protime_id < -1 ) {
			$protime_id = 0;
		}

		$this->protime_id = $protime_id;

		if ( $protime_id > 0 ) {
			$this->getProtimeValues();
		}
	}

	// TODOEXPLAIN
	function getProtimeValues() {
		$oProtime = new class_mysql($this->databases['default']);
		$oProtime->connect();

		// reset values
		$query = "SELECT * FROM PROTIME_CURRIC WHERE PERSNR=" . $this->protime_id;
		$resultReset = mysql_query($query, $oProtime->getConnection());
		if ($row = mysql_fetch_assoc($resultReset)) {

			$this->lastname = $row["NAME"];
			$this->firstname = $row["FIRSTNAME"];

		}
		mysql_free_result($resultReset);
	}

	function getId() {
		return $this->protime_id;
	}

	function getFirstname() {
		return $this->firstname;
	}

	function getLastname() {
		return $this->lastname;
	}
}
