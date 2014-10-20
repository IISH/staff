<?php 

// TODOEXPLAIN
class class_employee {
    protected $id = 0;
	protected $user = '';
	protected $protime_id;
    protected $firstname = '';
	protected $lastname = '';
    protected $email = '';
    protected $authorisation = array();
    protected $databases;

	// TODOEXPLAIN
	function __construct($user) {
		global $databases;
        $this->databases = $databases;

		$this->user = $user;

        $this->init();
	}

    // TODOEXPLAIN
    private function init() {
        $oConn = new class_mysql($this->databases['default']);
        $oConn->connect();

        $query = 'SELECT * FROM Users WHERE user=\'' . $this->user . '\' ';
        $result = mysql_query($query, $oConn->getConnection());
        if ($row = mysql_fetch_assoc($result)) {

            $this->id = $row["ID"];
            $this->firstname = $row["firstname"];
            $this->lastname = $row["lastname"];
            $this->email = $row["email"];
            $this->protime_id = $row["ProtimeID"];

            if ( $row["inouttime"] == 1 ) {
                $this->authorisation[] = 'inouttime';
            }

	        if ( $row["isadmin"] == 1 ) {
		        $this->authorisation[] = 'admin';
	        }

	        if ( $row["isreception"] == 1 ) {
		        $this->authorisation[] = 'reception';
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
	function isAdmin() {
		return ( in_array( 'admin', $this->getAuthorisation() ) ) ? true : false ;
	}

	// TODOEXPLAIN
	function isReception() {
		return ( in_array( 'reception', $this->getAuthorisation() ) ) ? true : false ;
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
    function getId() {
        return $this->id;
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
		global $databases;

		$oConn = new class_mysql($databases['default']);
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

    // TODOEXPLAIN
    public function setUser($user) {
        $this->user = $user;
    }

    // TODOEXPLAIN
    public function save() {
        if ( $this->id == 0 ) {
            $this->insert();
            $this->init();
        } else {
            $this->update();
        }
    }

    // TODOEXPLAIN
    private function insert() {
        global $databases;

        $oConn = new class_mysql($databases['default']);
        $oConn->connect();

        $query = "INSERT INTO Users (user, firstname, lastname, email) VALUES('::user::', '::firstname::', '::lastname::', '::email::'); ";
        $query = str_replace('::user::', addslashes($this->getUser()), $query);
        $query = str_replace('::firstname::', addslashes($this->getFirstname()), $query);
        $query = str_replace('::lastname::', addslashes($this->getLastname()), $query);
        $query = str_replace('::email::', addslashes($this->getEmail()), $query);

        $result = mysql_query($query, $oConn->getConnection());
    }

    // TODOEXPLAIN
    private function update() {
        echo "ERROR: update function (in class_employee.inc.php) not implemented yet";
    }

    // TODOEXPLAIN
    public function updateLastLogin() {
        global $databases;

        $oConn = new class_mysql($databases['default']);
        $oConn->connect();

        $query = "UPDATE Users SET last_user_login='" . date("Y-m-d H:i:s") . "' WHERE user='::user::' ";
        $query = str_replace('::user::', addslashes($this->getUser()), $query);

        $result = mysql_query($query, $oConn->getConnection());
    }
}
