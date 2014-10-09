<?php 
require_once("./classes/class_file.inc.php");
require_once("./classes/class_misc.inc.php");

class class_view {
	protected $oDb;
	protected $oClassFile;
	protected $oClassMisc;

	protected $m_view;
	protected $m_array_of_fields = Array();

	// TODOEXPLAIN
	function __construct($oDb) {
		$this->oDb = $oDb;
		$this->oClassFile = new class_file();
		$this->oClassMisc = new class_misc();
	}

	// TODOEXPLAIN
	function generate_view_header() {
		$total_header = '';

		$row_template = "<tr>::TR::</tr>";
		$header_template = "\n<TH align=\"left\"><a class=\"nolink\">::TH::</a>&nbsp;</TH>\n";

		foreach ($this->m_array_of_fields as $one_field_in_array_of_fields) {
				$tmp_header = $header_template;

				// plaats label en buttons in header
				$tmp_header = str_replace("::TH::", $one_field_in_array_of_fields->get_fieldlabel(), $tmp_header);

				$total_header .= $tmp_header;
		}

		$return_value = str_replace("::TR::", $total_header, $row_template);

		return $return_value;
	}

	// generate_view
	function generate_view() {
		$return_value = '';

		// connect to server
		$this->oDb->connect();

		// execute query
		$res=mysql_query($this->m_view["query"], $this->oDb->getConnection()) or die( "error 8712378" . "<br>" . mysql_error());

		if($res){

            $return_value .= "<table";

            // extra tabel parameters
            if ( $this->m_view["table_parameters"] != '' ) {
                $return_value .= " " . $this->m_view["table_parameters"] . " ";
            }

            // sluit tabel
            $return_value .= ">";

            $total_row = '';

            // add header row
            $return_value .= $this->generate_view_header();

            // doorloop gevonden recordset
            while($row=mysql_fetch_assoc($res)){
                $total_data = '';

                foreach ($this->m_array_of_fields as $one_field_in_array_of_fields) {
                    $total_data .= str_replace("::TD::", $one_field_in_array_of_fields->get_value($row), "<TD class=\"recorditem\">::TD::&nbsp;</td>");
                }
                // plaats alle cellen in row template
                $total_row .= str_replace("::TR::", $total_data, "<tr>::TR::</tr>");
            }

            // voeg alle rijen toe aan tabel
            $return_value .= $total_row;

            // end table
            $return_value .= "</table>";

		}

		// free result set
		mysql_free_result($res);

		// disconnect from database
		$this->oDb->close();

		// return result
		return $return_value;
	}

	// set_view
	function set_view($aView) {
		$this->m_view = $aView;

		return 1;
	}

	// add_field
	function add_field($aField) {
		array_push($this->m_array_of_fields, $aField);
		return 1;
	}
}
