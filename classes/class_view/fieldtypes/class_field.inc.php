<?php 
// version: 2009-02-19

require_once("./classes/class_misc.inc.php");

class class_field {
	protected $oClassMisc;
	protected $m_fieldname;
	protected $m_fieldname_pointer;
	protected $m_fieldlabel;
	protected $m_fieldlabel_alttitle;
	protected $m_href;
	protected $m_nobr;
	protected $m_onclick;
	protected $m_view_max_length;
	protected $m_view_max_length_extension;
	protected $m_if_no_value_value;
	protected $m_target;
	protected $m_table_cell_width;
	protected $m_show_different_value = '';
	protected $m_alttitle = '';
	protected $m_class = '';
	protected $m_style = '';
	protected $m_noheader;

	// TODOEXPLAIN
	function class_field($settings) {
		$this->oClassMisc = new class_misc();
		$this->m_fieldname = '';
		$this->m_fieldname_pointer = '';
		$this->m_fieldlabel = '';
		$this->m_fieldlabel_alttitle = '';
		$this->m_href = '';
		$this->m_nobr = '';
		$this->m_onclick = '';
		$this->m_view_max_length = 0;
		$this->m_view_max_length_extension = '..';
		$this->m_if_no_value_value = '';
		$this->m_target = '';
		$this->m_table_cell_width = '';
		$this->m_show_different_value = '';
		$this->m_alttitle = '';
		$this->m_class = '';
		$this->m_style = '';
		$this->m_noheader = false;

		if ( is_array( $settings ) ) {
			foreach ( $settings as $field => $value ) {
				switch ($field) {
					case "fieldname":
						$this->m_fieldname = $settings["fieldname"];
						break;
					case "fieldname_pointer":
						$this->m_fieldname_pointer = $settings["fieldname_pointer"];
						break;
					case "fieldlabel":
						$this->m_fieldlabel = $settings["fieldlabel"];
						break;
					case "fieldlabel_alttitle":
						$this->m_fieldlabel_alttitle = $settings["fieldlabel_alttitle"];
						break;
					case "table_cell_width":
						$this->m_table_cell_width = $settings["table_cell_width"];
						break;
					case "href":
						$this->m_href = $settings["href"];
						break;
					case "nobr":
						$this->m_nobr = $settings["nobr"];
						break;
					case "onclick":
						$this->m_onclick = $settings["onclick"];
						break;
					case "view_max_length":
						$this->m_view_max_length = $settings["view_max_length"];
						break;
					case "view_max_length_extension":
						$this->m_view_max_length_extension = $settings["view_max_length_extension"];
						break;
					case "if_no_value_value":
						$this->m_if_no_value_value = $settings["if_no_value_value"];
						break;
					case "target":
						$this->m_target = $settings["target"];
						break;
					case "show_different_value":
						$this->m_show_different_value = $settings["show_different_value"];
						break;
					case "href_alttitle":
						$this->m_alttitle = $settings["href_alttitle"];
						break;
					case "class":
						$this->m_class = $settings["class"];
						break;
					case "style":
						$this->m_style = $settings["style"];
						break;
					case "noheader":
						$this->m_noheader = $settings["noheader"];
						break;
				}
			}
		}
	}

	// TODOEXPLAIN
	function get_if_no_value_value($retval) {
		$retval = trim($retval);
		if ( strlen($retval) == 0 ) {
			$retval = trim($this->m_if_no_value_value);
			if ( strlen($retval) == 0 ) {
				$retval = "..no value..";
			}
		}
		return $retval;
	}

	// TODOEXPLAIN
	function get_fieldname() {
		return $this->m_fieldname;
	}

	// TODOEXPLAIN
	function get_fieldname_pointer() {
		return $this->m_fieldname_pointer;
	}

	// TODOEXPLAIN
	function get_fieldlabel() {
		return $this->m_fieldlabel;
	}

	// TODOEXPLAIN
	function get_fieldlabel_alttitle() {
		return $this->m_fieldlabel_alttitle;
	}

	// TODOEXPLAIN
	function get_table_cell_width() {
		return $this->m_table_cell_width;
	}

	// TODOEXPLAIN
	function get_href() {
		return $this->m_href;
	}

	// TODOEXPLAIN
	function get_nobr() {
		return $this->m_nobr;
	}

	// TODOEXPLAIN
	function get_onclick() {
		return $this->m_onclick;
	}

	// TODOEXPLAIN
	function get_noheader() {
		return $this->m_noheader;
	}

	// TODOEXPLAIN
	function get_value($row, $criteriumResult = 0) {

		if ( is_array($criteriumResult) ) {
			// 
			if ( $criteriumResult["fieldname"] == "-novalue-" ) {
				$retval = '';
			} elseif ( $criteriumResult["fieldname"] <> "" ) {
				$retval = stripslashes($row[$criteriumResult["fieldname"]]);
			} else {
				$retval = stripslashes($row[$this->get_fieldname()]);
			}
		} else {
			$retval = stripslashes($row[$this->get_fieldname()]);
		}

		return $retval;
	}

	// TODOEXPLAIN
	function view_field($row, $criteriumResult = 0) {

		if ( is_array($criteriumResult) ) {
			// 
			if ( $criteriumResult["fieldname"] == "-novalue-" ) {
				$retval = '';
			} elseif ( $criteriumResult["fieldname"] <> "" ) {
				$retval = stripslashes($row[$criteriumResult["fieldname"]]);
			} else {
				$retval = stripslashes($row[$this->get_fieldname()]);
			}
		} else {
			$retval = stripslashes($row[$this->get_fieldname()]);
		}

		// toon andere waarde
		if ( is_array($this->m_show_different_value) ) {
			if ( $retval == $this->m_show_different_value["value"] ) {
				if ( isset($this->m_show_different_value["showvalue"]) ) {
					$retval = $this->m_show_different_value["showvalue"];
				}
			} else {
				if ( isset($this->m_show_different_value["showelsevalue"]) ) {
					$retval = $this->m_show_different_value["showelsevalue"];
				}
			}

		}

		if ( $this->m_view_max_length != 0 ) {
			if ( strlen($retval) > $this->m_view_max_length ) {

				$tmp_retval = $retval;

				// omdat we gaan knippen, vervang dan paar speciale html characters door gewone characters
				$tmp_retval = str_replace("&ndash;", "-", $tmp_retval);
				$tmp_retval = str_replace("&mdash;", "-", $tmp_retval);

				// neem de eerste x karakters
				$tmp_retval = substr($tmp_retval, 0, $this->m_view_max_length);

				// moeten er nog extra puntjes achter de string geplaatst worden
				if ( $this->m_view_max_length_extension !== false ) {
					$tmp_retval .= $this->m_view_max_length_extension;
				}

				$tmp_searchstring = strtolower(trim($_GET["vf_" . $this->m_fieldname ]));
				if ( $tmp_searchstring != '' ) {
					$all_search_found_in_max_length_value = 1;
					$tmp_searchstring_array = explode(" ", $tmp_searchstring);

					foreach ( $tmp_searchstring_array as $array_value) {

						$pos = strpos(strtolower($tmp_retval), $array_value);
						if ( $pos === false ) {
							$all_search_found_in_max_length_value = 0;
						}
					}

					if ( $all_search_found_in_max_length_value != 1 ) {
						$tmp_retval = $retval;
					}

				}

				$retval = $tmp_retval;

			} else {
				// controleer of string langer is dan de maximale opgegeven lengte
				if ( strlen($retval) > $this->m_view_max_length ) {
					// ja, neem dan alleen maximaal x karakters

					// omdat we gaan knippen, vervang dan paar speciale html characters door gewone characters
					$retval = str_replace("&ndash;", "-", $retval);
					$retval = str_replace("&mdash;", "-", $retval);

					// neem de eerste x karakters
					$retval = substr($retval, 0, $this->m_view_max_length);

					// moeten er nog extra puntjes achter de string geplaatst worden
					if ( $this->m_view_max_length_extension !== false ) {
						$retval .= $this->m_view_max_length_extension;
					}
				}
			}
		}

		// als veld geen waarde heeft, toon dan de -empty- waarde
		if ( $retval == '' ) {
			if ( $this->m_if_no_value_value != '' ) {
				$retval = $this->m_if_no_value_value;
			}
		}

		return $retval;
	}
}
?>