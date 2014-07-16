<?php

// TODOEXPLAIN
class class_db {
	protected $m_server;
	protected $m_user;
	protected $m_password;
	protected $m_database;
	protected $conn;

	// TODOEXPLAIN
	function class_db($settings, $prefix) {
		$this->m_server = $settings[$prefix . "_server"];
		$this->m_user = $settings[$prefix . "_user"];
		$this->m_password = $settings[$prefix . "_password"];
		$this->m_database = $settings[$prefix . "_database"];
	}

	// TODOEXPLAIN
	function connect() {
		$this->conn = mysql_connect($this->m_server, $this->m_user, $this->m_password);
		if ( !$this->conn ) {
			die('Error: 100 - Could not connect: ' . mssql_error());
		}

		// connect to database
		mysql_select_db($this->m_database, $this->conn);

		return 1;
	}

	// TODOEXPLAIN
	function disconnect() {
		mysql_close($this->conn);
	}

	function connection() {
		return $this->conn;
	}
}
