<?php 
class class_menuitem {
	protected $code = '';
	protected $label = '';
	protected $url = '';

	function class_menuitem($code, $label, $url ) {
		$this->code = $code;
		$this->label = $label;
		$this->url = $url;
	}

	function getCode() {
		return $this->code;
	}

	function getLabel() {
		return $this->label;
	}

	function getUrl() {
		return $this->url;
	}
}
