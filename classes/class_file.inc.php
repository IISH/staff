<?php

// TODOEXPLAIN
class class_file {

	// TODOEXPLAIN
	function getFileSource($bestandsnaam) {
		$return_value = implode("\n", file($bestandsnaam));

		return $return_value;
	}
}
