<?php 
// version: 2014-01-20

class class_employee {
	protected $user = '';
	protected $project_settings;
	protected $protime_id = 0;
	protected $email = '';
	protected $authorisation = array();
	protected $lastname = '';
	protected $firstname = '';

	// TODOEXPLAIN
	function class_employee($user, $project_settings) {
        global $dbhandlePresentornot, $dbhandleTimecard;

		$this->user = $user;
		$this->project_settings = $project_settings;
echo "TMPTMP";
/*
        // get protime_id and email
        $query_project = 'SELECT * FROM Employees WHERE LongCode=\'' . $this->getUser() . '\' ';
//echo $query_project . ' +<br>';
        $resultReset = mssql_query($query_project, $dbhandleTimecard);
        if ($row_project = mssql_fetch_assoc($resultReset)) {
            $this->email = $row_project["Email"];
            $this->protime_id = $row_project["ProtimePersNr"];
            $this->lastname = $row_project["LastName"];
            $this->firstname = $row_project["FirstName"];
        }
        mssql_free_result($resultReset);

        // get authorisation
        $queryAuthorisation = 'SELECT * FROM Users WHERE user=\'' . $this->getUser() . '\' ';
        $resultAuthorisation = mysql_query($queryAuthorisation, $dbhandlePresentornot);
        while ($rowAuthorisation = mysql_fetch_assoc($resultAuthorisation)) {
            if ( $rowAuthorisation["inouttime"] == 1 ) {
                $this->authorisation[] = 'inouttime';
            }
        }
        mysql_free_result($resultAuthorisation);
*/
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

	function getFavourites( $type ) {
		global $dbhandlePresentornot;

		$ids = array();
		$ids[] = '0';

		$query = 'SELECT * FROM Favourites WHERE user=\'' . $this->getUser() . '\' AND type=\'' . $type . '\' ';
//echo $query . ' +<br>';
		$result = mysql_query($query, $dbhandlePresentornot);
		while ( $row = mysql_fetch_array($result) ) {
			$ids[] = $row["ProtimeID"];
		}
		mysql_free_result($result);

		return $ids;
	}
}
?>