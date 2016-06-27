<?php

class Department {
	private $databases;

	private $id = 0;
	private $short_1 = '';
	private $short_2 = '';
	private $code_extern = '';
	private $customer = '';

	function __construct($id) {
		global $databases;
		$this->databases = $databases;

		$this->initValues( $id );
	}

	private function initValues( $id ) {
		global $dbConn;

		$query = "SELECT * FROM " . Settings::get('protime_tables_prefix') .  "depart WHERE DEPART=" . $id;
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		if ( $r = $stmt->fetch() ) {
			$this->id = $id;
			$this->short_1 = $r["SHORT_1"];
			$this->short_2 = $r["SHORT_2"];
			$this->code_extern = $r["CODE_EXTERN"];
			$this->customer = $r["CUSTOMER"];
		}
	}

	public function getId() {
		return $this->id;
	}

	public function getShort1() {
		return $this->short_1;
	}

	public function getShort2() {
		return $this->short_2;
	}

	public function getCodeExtern() {
		return $this->code_extern;
	}

	public function getCustomer() {
		return $this->customer;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n#: " . $this->id . "\n";
	}

	public function getShort() {
		if ( getLanguage() == 2 ) {
			return $this->short_2;
		}

		return $this->short_1;
	}
}
