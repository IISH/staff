<?php 
class MenuItem {
	protected $label = '';
	protected $url = '';

	function __construct($label, $url ) {
		$this->label = $label;
		$this->url = $url;
	}

	public function getLabel() {
		return $this->label;
	}

	public function getUrl() {
		return $this->url;
	}
}
