<?php

class Synonyms {
	public static function getIdByUsingKnawLoginName( $knawLogin ) {
		return Synonyms::getLoginName($knawLogin, 'iisg_curric_persnr', 'knaw_login');
	}

	public static function getIdByUsingIisgLoginName($iisgLogin ) {
		return Synonyms::getLoginName($iisgLogin, 'iisg_curric_persnr', 'iisg_login');
	}

	public static function getKnawLoginName( $iisgLogin ) {
		return Synonyms::getLoginName($iisgLogin, 'knaw_login', 'iisg_login');
	}

	public static function getIisgLoginName( $knawLogin ) {
		return Synonyms::getLoginName($knawLogin, 'iisg_login', 'knaw_login');
	}

	public static function getLoginName( $login, $selectField, $whereField ) {
		global $dbConn;

		$ret = '';

		$login = trim($login);

		if ( $login != '' ) {
			$query = "SELECT * FROM synonyms WHERE " . $whereField . "='" . addslashes($login) . "' ";
			$stmt = $dbConn->getConnection()->prepare($query);
			$stmt->execute();
			$result = $stmt->fetchAll();

			foreach ($result as $row) {
				$ret = $row[$selectField];
			}
		}

		return $ret;
	}
}