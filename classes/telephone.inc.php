<?php
class Telephone {

	public static function getTelephonesHref( $telephone ) {
		global $oWebuser, $deviceType;

		$ret = '';

		$tel = Telephone::splitTelephoneInParts( $telephone );

		foreach ( $tel as $part ) {
			$telephoneStyled = Telephone::styleTelephone($part);

//			if ( $oWebuser->getUserSetting('call_via_computer', 0) == 1 || $oWebuser->getUserSetting('call_via_mobile', 0) == 1 ) {
			if ( $deviceType == 'phone' ) {
				$telephoneHref = Telephone::createTelephoneUrlLink($part);
				if ( $telephoneHref != '' ) {
					$ret .= '<a href="tel:' . $telephoneHref . '">' . $telephoneStyled . '</a>';
				} else {
					$ret .= $telephoneStyled;
				}
			} else {
				$ret .= $telephoneStyled;
			}
		}

		return $ret;
	}

	public static function createTelephoneUrlLink( $telephone ) {
		$href = '';

		$lengthShortTelephoneNumber = Settings::get('max_length_short_company_telephone_number');
		$instituteTelephoneTemplate = Settings::get('company_telephone_number_template');
		$patternInstitute = '/^' . Settings::get('institute_prefix') . '[0-9]{' . $lengthShortTelephoneNumber . ',' . $lengthShortTelephoneNumber . '}$/';

		if ( preg_match($patternInstitute, $telephone) ) {
			// institute telephone number
			$href = Telephone::addCountryPrefixToTelephone(substr($instituteTelephoneTemplate, 0, strlen($instituteTelephoneTemplate) - $lengthShortTelephoneNumber) . substr($telephone, -$lengthShortTelephoneNumber));
		} else {
			// normal telephone number
			$telephoneNumberFullLength = Settings::get('telephone_number_full_length');
			$patternLong = '/^[0-9]{' . $telephoneNumberFullLength . ',' . $telephoneNumberFullLength . '}$/';

			$telephoneCleaned = str_replace(array(' ', '-', '(', ')'), '', $telephone);
			if ( preg_match($patternLong, $telephoneCleaned) ) {
				$href = Telephone::addCountryPrefixToTelephone($telephoneCleaned);
			}
		}

		return $href;
	}

	public static function styleTelephone( $telephone ) {
		global $oWebuser;

		// lowercase institute prefix
		if ( $oWebuser->getUserSetting('smaller_font_institute_prefix', 0) == 1 ) {
			$pre = '<span style="font-size:70%;">';
			$post = '</span>';

			$lengthInstitutePrefix = strlen(Settings::get('institute_prefix'));
			$lengthShortTelephoneNumber = Settings::get('max_length_short_company_telephone_number');
			$pattern = '/^' . Settings::get('institute_prefix') . '[0-9]{' . $lengthShortTelephoneNumber . ',' . $lengthShortTelephoneNumber . '}$/';

			// style
			if ( preg_match($pattern, $telephone) ) {
				$styled = $pre . substr($telephone, 0, $lengthInstitutePrefix) . $post . substr($telephone, $lengthInstitutePrefix, strlen($telephone)-$lengthInstitutePrefix);
			} else {
				$styled = $telephone;
			}
		} else {
			$styled = $telephone;
		}

		return $styled;
	}

	public static function addCountryPrefixToTelephone($telephone) {
		$telephoneNumberFullLength = Settings::get('telephone_number_full_length');

		if ( strlen($telephone) == $telephoneNumberFullLength ) {
			if ( $telephone[0] == '0' ) {
				$countryTelephonePrefix = Settings::get('country_telephone_number_prefix');
				$telephone = $countryTelephonePrefix . substr($telephone, -(strlen($telephone)-1));
			}
		}

		return $telephone;
	}

	public static function splitTelephoneInParts( $telephone ) {
		$ret = array();

		$telephone = trim($telephone);

		$nrOfCharacters = strlen($telephone);

		$previousChar = '';
		$tmp = '';
		$currentType = '';
		for ( $i = 0; $i < $nrOfCharacters; $i++ ) {
			$char = $telephone[$i];

			if ( in_array($char, array(' ', '-') ) ) {
				// no swith when current character is space or slash
				$tmp .= $char;
			} elseif ( is_numeric($char) ) {
				// digit

				// if current character type is not a digit
				// save the 'alpha', no trimming
				if ( $currentType != 'd' ) {
					// no trim when saving text
					if ( $tmp != '' ) {
						$ret[] = $tmp;
					}
					$tmp = '';
				}
				$tmp .= $char;
				$currentType = 'd';
			} else {
				// alpha

				// if current character type is not alpha
				// save digit, here we do trim
				if ( $currentType != 'a' ) {
					if ( trim($tmp) != '' ) {
						// save digit
						$ret[] = trim($tmp);
					}

					$tmp = '';

					if ( $previousChar == ' ' ) {
						$tmp .= $previousChar;
					}
				}

				$tmp .= $char;
				$currentType = 'a';
			}

			$previousChar = $char;
		}

		if ( trim($tmp) != '' ) {
			$ret[] = $tmp;
		}

		return $ret;
	}
}