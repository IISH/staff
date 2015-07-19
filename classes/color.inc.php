<?php
class class_color {
	var $absence_id = 0;
	var $description = '';
	var $code = '';
	var $backgroundColor = '';
	var $fontColor = '';
	var $everyone = 0;

	function __construct( $absence_id, $description, $code, $backgroundColor, $fontColor, $everyone ) {
		$this->absence_id = $absence_id;
		$this->description = $description;
		$this->code = $code;
		$this->backgroundColor = $backgroundColor;
		$this->fontColor = $fontColor;
		$this->everyone = $everyone;
	}

	public function getBackgroundColor() {
		return $this->backgroundColor . $this->fontColor;
	}

	public function getFontColor() {
		return $this->fontColor;
	}

	public function getDescriptin() {
		return $this->description;
	}

	public function isVisibleForEveryone() {
		return $this->everyone;
	}
}
