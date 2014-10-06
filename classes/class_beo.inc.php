<?php
class class_beo {
	var $type = '';
	var $typeOfBeo = array(
			'b' => array(
					'label' => 'BHV'
					, 'query' => " USER03 LIKE '%BHV%' "
					, 'show_level' => false
				)
			, 'e' => array(
					'label' => 'EHBO'
					, 'query' => " USER03 LIKE '%EHBO%' "
					, 'show_level' => false
				)
			, 'o' => array(
					'label' => 'Ontruimer'
					, 'query' => " ( USER03 LIKE '%O0%' OR USER03 LIKE '%O1%' OR USER03 LIKE '%O2%' OR USER03 LIKE '%O3%' OR USER03 LIKE '%O4%' OR USER03 LIKE '%O5%' ) "
					, 'show_level' => true
				)
		);

	// TODOEXPLAIN
	function __construct( $beo ) {
		$this->type = strtolower($beo);
		$this->protectTypeOfBeo();
	}

	// TODOEXPLAIN
	function getType() {
		return $this->type;
	}

	// TODOEXPLAIN
	function getLabel() {
		return $this->typeOfBeo[$this->type]['label'];
	}

	// TODOEXPLAIN
	function getQuery() {
		return $this->typeOfBeo[$this->type]['query'];
	}

	// TODOEXPLAIN
	function showLevel() {
		return $this->typeOfBeo[$this->type]['show_level'];
	}

	// TODOEXPLAIN
	private function protectTypeOfBeo() {
		$beo = $this->type;

		if ( !isset($beo) || trim($beo) == '' ) {
			$beo = "b";
		}

		$beo = substr($beo, 0, 1);

		if ( !in_array($beo, array('b', 'e', 'o')) ) {
			$beo = "b";
		}

		$this->type = $beo;
	}
}