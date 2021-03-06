<?php
class Beo {
	var $type = '';
	var $label = '';

	var $typeOfBeo = array(
			// bhv
			'b' => array(
					'query' => " USER03 LIKE '%BHV%' "
					, 'show_floor' => false
					, 'scriptname' => 'ert_list.php'
					, 'extra_order_by' => 'IFNULL(bhv_sortorder, 9999), '
				)
			// emergency
			, 'e' => array(
					'query' => " ( USER03 LIKE '%EHBO%' OR USER03 LIKE '%+E%' ) "
					, 'show_floor' => false
					, 'scriptname' => 'firstaid_list.php'
					, 'extra_order_by' => ''
				)
			// ontruimer
			, 'o' => array(
					'query' => " ( USER03 LIKE '%O0%' OR USER03 LIKE '%O1%' OR USER03 LIKE '%O2%' OR USER03 LIKE '%O3%' OR USER03 LIKE '%O4%' OR USER03 LIKE '%O5%' OR USER03 LIKE '%O6%' ) "
					, 'show_floor' => true
					, 'scriptname' => 'evacuators_list.php'
					, 'extra_order_by' => ''
				)
			// medewerkers uitgezonderd ontruimers uitgezonderd bhv
			, 'm-not-o-not-b' => array(
					'query' => " USER03 NOT LIKE '%O0%' AND USER03 NOT LIKE '%O1%' AND USER03 NOT LIKE '%O2%' AND USER03 NOT LIKE '%O3%' AND USER03 NOT LIKE '%O4%' AND USER03 NOT LIKE '%O5%' AND USER03 NOT LIKE '%O6%' AND USER03 NOT LIKE '%BHV%' "
					, 'show_floor' => false
					, 'scriptname' => ''
					, 'extra_order_by' => ''
				)
		);

	function __construct( $beo, $label ) {
		$this->type = strtolower($beo);
		$this->label = $label;
		$this->protectTypeOfBeo();
	}

	public function getType() {
		return $this->type;
	}

	public function getLabel() {
		return $this->label;
	}

	public function getShowFloor() {
		return $this->typeOfBeo[$this->type]['show_floor'];
	}

	public function getQuery() {
		return $this->typeOfBeo[$this->type]['query'];
	}

	public function getExtraOrderBy() {
		return $this->typeOfBeo[$this->type]['extra_order_by'];
	}

	public function getScriptName() {
		return $this->typeOfBeo[$this->type]['scriptname'];
	}

	private function protectTypeOfBeo() {
		$beo = $this->type;

		if ( !isset($beo) || trim($beo) == '' ) {
			$beo = "b";
		}

		if ( !in_array($beo, array('b', 'e', 'o', 'm-not-o-not-b')) ) {
			$beo = "b";
		}

		$this->type = $beo;
	}
}