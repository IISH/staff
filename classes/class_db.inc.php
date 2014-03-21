<?php 
// version: 2014-01-20

class class_db {
	var $m_server;
	var $m_user;
	var $m_password;
	var $m_database;
	var $conn;

	// TODOEXPLAIN
	function class_db($settings, $prefix) {
		$this->m_server = $settings[$prefix . "_server"];
		$this->m_user = $settings[$prefix . "_user"];
		$this->m_password = $settings[$prefix . "_password"];
		$this->m_database = $settings[$prefix . "_database"];
	}

	// TODOEXPLAIN
	function connect() {
		$this->conn = mssql_connect($this->m_server, $this->m_user, $this->m_password);
		if ( !$this->conn ) {
			die('Error: 100 - Could not connect: ' . mssql_error());
		}

		// connect to database
		mssql_select_db($this->m_database, $this->conn);

		return 1;
	}

	// TODOEXPLAIN
	function disconnect() {
		mssql_close($this->conn);
	}

	function connection() {
		return $this->conn;
	}
}
?>