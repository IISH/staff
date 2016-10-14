<?php
function preprint( $object ) {
	echo '<pre>';
	print_r( $object );
	echo '</pre>';
}

class Misc {

    public static function get_remote_addr() {
        $retval = '';
        if ( isset( $_SERVER["HTTP_X_FORWARDED_FOR"] ) ) {
            $retval = trim($_SERVER["HTTP_X_FORWARDED_FOR"]);
        }

        if ( $retval == '' ) {
            if ( isset( $_SERVER["REMOTE_ADDR"] ) ) {
                $retval = trim($_SERVER["REMOTE_ADDR"]);
            }
        }

        return $retval;
    }

	public static function removeJobFunctionFromName( $string ) {
		$string = str_ireplace('(vrijwilliger)', '', $string);
		$string = str_ireplace('(vrijwillig)', '', $string);
		$string = str_ireplace('(stz)', '', $string);
		$string = str_ireplace('(rec)', '', $string);
		$string = str_ireplace('(uu)', '', $string);

		return $string;
	}

	public static function verplaatsTussenvoegselNaarBegin( $text ) {
		$array = array( ' van den', ' van der', ' van', ' de', ' el' );

		foreach ( $array as $t ) {
			if ( strtolower(substr($text, -strlen($t))) == strtolower($t) ) {
				$text = trim($t . ' ' . substr($text, 0, strlen($text)-strlen($t)));
			}
		}

		return $text;
	}

	public static function stripLeftPart( $string, $strip ) {
		if ( strtolower(substr($string, 0, strlen($strip))) == strtolower($strip) ) {
			$string = substr($string, -(strlen($string)-strlen($strip)));
		}
		return $string;
	}

	public static function getNeverShowPersonsCriterium() {
		$never_show_persnr = '0,' . preg_replace('/[^0-9]/', ',', trim(Settings::get("never_show_persnr")));
		$never_show_persnr = preg_replace('/,{2,}/', ',', $never_show_persnr);
		$never_show_persnr = ' AND PERSNR NOT IN (' . $never_show_persnr . ') ';

		return $never_show_persnr;
	}

	public static function multiplyTag($tag, $code, $start, $end) {
		$ret = '';
		$separator = '';

		for ( $i = $start ; $i <= $end; $i++ ) {
			$ret .= $separator . str_replace($code, $i, $tag);
			$separator = ', ';
		}

		return $ret;
	}

	function PlaceURLParametersInQuery($query) {
		$return_value = $query;

		// vervang in de url, de FLD: door waardes
		$pattern = '/\[FLD\:[a-zA-Z0-9_]*\]/';
		preg_match($pattern, $return_value, $matches);
		while ( count($matches) > 0 ) {
			if ( isset($this->m_form["primarykey"]) && "[FLD:" . $this->m_form["primarykey"] . "]" == $matches[0] ) {
				$return_value = str_replace($matches[0], $this->m_doc_id, $return_value);
			} else {
				$return_value = str_replace($matches[0], addslashes($_GET[str_replace("]", '', str_replace("[FLD:", '', $matches[0]))]), $return_value);
			}

			$matches = null;
			preg_match($pattern, $return_value, $matches);
		}

		$return_value = str_replace("[BACKURL]", urlencode(getBackUrl()), $return_value);

		return $return_value;
	}

	public function ReplaceSpecialFieldsWithDatabaseValues($url, $row) {
		$return_value = $url;

		// vervang in de url, de FLD: door waardes
		$pattern = '/\[FLD\:[a-zA-Z0-9_]*\]/';
		preg_match($pattern, $return_value, $matches);
		while ( count($matches) > 0 ) { 
			$return_value = str_replace($matches[0], addslashes($row[str_replace("]", "", str_replace("[FLD:", "", $matches[0]))]), $return_value);
			$matches = null;
			preg_match($pattern, $return_value, $matches);
		}

		$backurl = $_SERVER["QUERY_STRING"];
		if ( $backurl <> "" ) {
			$backurl = "?" . $backurl;
		}
		$backurl = urlencode($_SERVER["SCRIPT_NAME"] . $backurl);
		$return_value = str_replace("[BACKURL]", $backurl, $return_value);

		return $return_value;
	}

	public function ReplaceSpecialFieldsWithQuerystringValues($url) {
		$return_value = $url;

		// vervang in de url, de FLD: door waardes
		$pattern = '/\[QUERYSTRING\:[a-zA-Z0-9_]*\]/';
		preg_match($pattern, $return_value, $matches);
		while ( count($matches) > 0 ) { 
			$return_value = str_replace($matches[0], addslashes($_GET[str_replace("]", "", str_replace("[QUERYSTRING:", "", $matches[0]))]), $return_value);
			$matches = null;
			preg_match($pattern, $return_value, $matches);
		}

		// calculate 'go back' url
		$backurl = $_SERVER["QUERY_STRING"];
		if ( $backurl <> "" ) {
			$backurl = "?" . $backurl;
		}
		$backurl = urlencode($_SERVER["SCRIPT_NAME"] . $backurl);
		// if there is a backurl then place the new blackurl into the string
		$return_value = str_replace("[BACKURL]", $backurl, $return_value);

		return $return_value;
	}
}
