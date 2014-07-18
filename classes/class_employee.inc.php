<?php 

// TODOEXPLAIN
class class_employee {
	protected $user = '';
	protected $project_settings;
	protected $protime_id;
    protected $firstname = '';
	protected $lastname = '';
    protected $email = '';
    protected $authorisation = array();

	// TODOEXPLAIN
	function class_employee($user, $project_settings) {
		$this->user = $user;
		$this->project_settings = $project_settings;

		$oConn = new class_mysql($this->project_settings, 'presentornot');
		$oConn->connect();

        $query = 'SELECT * FROM Users WHERE user=\'' . $this->getUser() . '\' ';
        $result = mysql_query($query, $oConn->getConnection());
        if ($row = mysql_fetch_assoc($result)) {

            $this->firstname = $row["firstname"];
            $this->lastname = $row["lastname"];
            $this->email = $row["email"];
			$this->protime_id = $row["ProtimeID"];

            if ( $row["inouttime"] == 1 ) {
                $this->authorisation[] = 'inouttime';
            }
        }
        mysql_free_result($result);
	}

	// TODOEXPLAIN
	function getAuthorisation() {
		return $this->authorisation ;
	}

	// TODOEXPLAIN
	function hasInOutTimeAuthorisation() {
		return ( in_array( 'inouttime', $this->getAuthorisation() ) ) ? true : false ;
	}

	// TODOEXPLAIN
	function getUser() {
		return $this->user;
	}

    // TODOEXPLAIN
    function getProtimeId() {
        return $this->protime_id;
    }

    // TODOEXPLAIN
    function getLastname() {
        return $this->lastname;
    }

    // TODOEXPLAIN
    function getFirstname() {
        return $this->firstname;
    }

    // TODOEXPLAIN
	function isLoggedIn() {
		$ret = false;

		if ( $this->getUser() != '' ) {
			$ret = true;
		}

		return $ret;
	}

	// TODOEXPLAIN
	function checkLoggedIn() {
		global $protect;

		if ( $this->getUser() == '' ) {
			Header("Location: login.php?burl=" . URLencode($protect->getShortUrl()));
			die("go to <a href=\"login.php?burl=" . URLencode($protect->getShortUrl()) . "\">next</a>");
		}
	}

	// TODOEXPLAIN
	function getEmail() {
		return $this->email;
	}

	// TODOEXPLAIN
	function getFavourites( $type ) {
		$oConn = new class_mysql($this->project_settings, 'presentornot');
		$oConn->connect();

		$ids = array();
		$ids[] = '0';

		$query = 'SELECT * FROM Favourites WHERE user=\'' . $this->getUser() . '\' AND type=\'' . $type . '\' ';
		$result = mysql_query($query, $oConn->getConnection());
		while ( $row = mysql_fetch_assoc($result) ) {
			$ids[] = $row["ProtimeID"];
		}
		mysql_free_result($result);

		return $ids;
	}
}
