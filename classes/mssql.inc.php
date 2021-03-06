<?php

class class_mssql {
	private $server;
	private $user;
	private $password;
	private $database;
	private $conn;

	function __construct($database) {
		$this->server = $database["host"];
		$this->user = $database["username"];
		$this->password = $database["password"];
		$this->database = $database["database"];
	}

	public function connect() {
		$this->conn = mssql_connect($this->server, $this->user, $this->password);
		if ( !$this->conn ) {
			die('Error: 174154 - Could not connect to MSSQL server<br>' . mssql_error());
		}

		// connect to database
		mssql_select_db($this->database, $this->conn);

		return 1;
	}

	public function close() {
		@mssql_close($this->conn);
	}

	public function getConnection() {
		return $this->conn;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\nserver: " . $this->server . "\nuser: " . $this->user . "\ndatabase: " . $this->database . "\n";
	}
}
