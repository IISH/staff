<?php
class class_beo {
	var $type = '';
	var $label = '';

	var $typeOfBeo = array(
			'b' => array(
					'label' => '<a title="Emergency Response Team">ERT</a> (<a title="Bedrijfshulpverlening">BHV</a>)'
					, 'query' => " USER03 LIKE '%BHV%' "
					, 'show_level' => false
					, 'scriptname' => 'bhv'
				)
			, 'e' => array(
					'label' => 'First Aid (<a title="Eerste Hulp bij Ongevallen">EHBO</a>)'
					, 'query' => " USER03 LIKE '%EHBO%' "
					, 'show_level' => false
					, 'scriptname' => 'ehbo'
				)
			, 'o' => array(
					'label' => 'Evacuator (Ontruimer)'
					, 'query' => " ( USER03 LIKE '%O0%' OR USER03 LIKE '%O1%' OR USER03 LIKE '%O2%' OR USER03 LIKE '%O3%' OR USER03 LIKE '%O4%' OR USER03 LIKE '%O5%' OR USER03 LIKE '%O6%' ) "
					, 'show_level' => true
					, 'scriptname' => 'ontruimer'
				)
			, 'mnotonotb' => array(
					'label' => 'Colleagues (Medewerkers)'
					, 'query' => " USER03 NOT LIKE '%O0%' AND USER03 NOT LIKE '%O1%' AND USER03 NOT LIKE '%O2%' AND USER03 NOT LIKE '%O3%' AND USER03 NOT LIKE '%O4%' AND USER03 NOT LIKE '%O5%' AND USER03 NOT LIKE '%O6%' AND USER03 NOT LIKE '%BHV%' "
					, 'show_level' => false
					, 'scriptname' => ''
				)
		);

	// TODOEXPLAIN
	function __construct( $beo, $label ) {
		$this->type = strtolower($beo);
		$this->label = $label;
		$this->protectTypeOfBeo();
	}

	// TODOEXPLAIN
	function getType() {
		return $this->type;
	}

	// TODOEXPLAIN
	function getLabel() {
//		return $this->typeOfBeo[$this->type]['label'];
		return $this->label;
	}

	// TODOEXPLAIN
	function getQuery() {
		return $this->typeOfBeo[$this->type]['query'];
	}

	// TODOEXPLAIN
	function getShowLevel() {
		return $this->typeOfBeo[$this->type]['show_level'];
	}

	// TODOEXPLAIN
	function getScriptName() {
		return $this->typeOfBeo[$this->type]['scriptname'];
	}

	// TODOEXPLAIN
	private function protectTypeOfBeo() {
		$beo = $this->type;

		if ( !isset($beo) || trim($beo) == '' ) {
			$beo = "b";
		}

		if ( !in_array($beo, array('b', 'e', 'o', 'mnotonotb')) ) {
			$beo = "b";
		}

		$this->type = $beo;
	}
}