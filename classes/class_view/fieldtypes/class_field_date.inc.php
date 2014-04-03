<?php 
// version: 2014-01-20

require_once("./classes/class_view/fieldtypes/class_field.inc.php");

class class_field_date extends class_field {
	protected $project_settings;

	protected $m_format;

	// TODOEXPLAIN
	function class_field_date($settings) {
		parent::class_field($settings);

		$this->project_settings = $settings;

		$this->m_format = '';

		if ( is_array( $settings ) ) {
			foreach ( $settings as $field => $value ) {
				switch ($field) {
					// only string specific parameters

					case "format":
						$this->m_format = $settings["format"];
						break;

				}
			}
		}

	}

	// TODOEXPLAIN
	function view_field($row, $criteriumResult = 0) {
		$retval = parent::view_field($row, $criteriumResult);

		if ( $retval != '' ) {
			// verwijder tijd uit datum
			$retval = trim(str_replace('12:00:00:000AM', '', $retval));
			$retval = trim(str_replace('12:00AM', '', $retval));

			if ( $this->m_format != '' ) {
				$retval = date($this->m_format, strtotime($retval));
			}
		}

		if ( is_array($criteriumResult) ) {
			$href2otherpage = $criteriumResult["href"];
			$url_onclick = $criteriumResult["onclick"];
		} else {
			$href2otherpage = $this->get_href();
			$url_onclick = $this->get_onclick();
		}

		if ( $_POST["form_fld_pressed_button"] != '-delete-' && $_POST["form_fld_pressed_button"] != '-delete-now-' ) {

			if ( $href2otherpage <> "" ) {
				$retval = $this->get_if_no_value_value($retval);

				$href2otherpage = $this->oClassMisc->ReplaceSpecialFieldsWithDatabaseValues($href2otherpage, $row);
				$href2otherpage = $this->oClassMisc->ReplaceSpecialFieldsWithQuerystringValues($href2otherpage);
				$url_onclick = $this->oClassMisc->ReplaceSpecialFieldsWithDatabaseValues($url_onclick, $row);
				$url_onclick = $this->oClassMisc->ReplaceSpecialFieldsWithQuerystringValues($url_onclick);

				if ( $url_onclick <> "" ) {
					$url_onclick = " onClick=\"" . $url_onclick . "\"";
				}

				$target = $this->m_target;
				if ( $target <> "" ) {
					$target = " target=\"" . $target . "\" ";
				}

				$alttitle = $this->m_alttitle;
				if ( $alttitle != '' ) {
					$alttitle = " alt=\"" . $alttitle . "\" title=\"" . $alttitle . "\" ";
				}

				$retval = "<A HREF=\"" . $href2otherpage . "\" " . $url_onclick . " " . $target . " " . $alttitle . ">" . $retval . "</a>";

			}

			// no break - keep together
			if ( $this->m_nobr === true ) {
				$retval = "<nobr>" . $retval . "</nobr>";
			}

		}

		return $retval;
	}

}
?>