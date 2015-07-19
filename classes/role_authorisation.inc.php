<?php
require_once "mysql.inc.php";

class RoleAuthorisation {
	private $id = 0;
	private $role = '';
	private $authorisation = '';
	private $isdeleted = 0;

	function __construct( $id ) {
		global $databases;
		$this->databases = $databases;

		$this->initValues( $id );
	}

	private function initValues( $id ) {
		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$query = "SELECT * FROM Staff_role_authorisation WHERE id=" . $id . " ";

		$res = mysql_query($query, $oConn->getConnection());
		if ($r = mysql_fetch_assoc($res)) {
			$this->id = $id;
			$this->role = $r['role'];
			$this->authorisation = $r["authorisation"];
			$this->isdeleted = $r["isdeleted"];
		}
		mysql_free_result($res);
	}

	public function getId() {
		return $this->id;
	}

	public function getRole() {
		return $this->role;
	}

	public function getAuthorisation() {
		return $this->authorisation;
	}

	public function getIsDeleted() {
		return $this->isdeleted;
	}
}
