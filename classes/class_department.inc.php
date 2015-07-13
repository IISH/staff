<?php

class class_department {
	private $databases;

	private $id = 0;
	private $short_1 = '';
	private $short_2 = '';
	private $code_extern = '';
	private $customer = '';

	// TODOEXPLAIN
	function __construct($id) {
		global $databases;
		$this->databases = $databases;

		$this->initValues( $id );
	}

	// TODOEXPLAIN
	private function initValues( $id ) {
		$oProtime = new class_mysql($this->databases['default']);
		$oProtime->connect();

		$query = "SELECT * FROM " . class_settings::get('protime_tables_prefix') .  "DEPART WHERE DEPART=" . $id;

		$res = mysql_query($query, $oProtime->getConnection());
		if ($r = mysql_fetch_assoc($res)) {
			$this->id = $id;
			$this->short_1 = $r["SHORT_1"];
			$this->short_2 = $r["SHORT_2"];
			$this->code_extern = $r["CODE_EXTERN"];
			$this->customer = $r["CUSTOMER"];
		}
		mysql_free_result($res);
	}

	// TODOEXPLAIN
	public function getId() {
		return $this->id;
	}

	// TODOEXPLAIN
	public function getShort1() {
		return $this->short_1;
	}

	// TODOEXPLAIN
	public function getShort2() {
		return $this->short_2;
	}

	// TODOEXPLAIN
	public function getCodeExtern() {
		return $this->code_extern;
	}

	// TODOEXPLAIN
	public function getCustomer() {
		return $this->customer;
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\n#: " . $this->id . "\n";
	}
}
