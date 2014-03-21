<?php 
// version: 2014-01-20

class class_file {

	// TODOEXPLAIN
	function class_file() {
	}

	// TODOEXPLAIN
	function getFileSource($bestandsnaam) {
		$return_value = implode("\n", file($bestandsnaam));

		return $return_value;
	}
}
?>