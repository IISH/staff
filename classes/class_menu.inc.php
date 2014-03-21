<?php 
$menu = new class_menu();

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

// TAB: PROTIME
$menu->addMenuGroup( new class_menugroup('') );
$menu->addMenuItem( new class_menuitem('protime.presentornot', 'Present or not', 'present_or_not.php') );
$menu->addMenuItem( new class_menuitem('protime.vakantie', 'Absences', 'vakantie.php') );
$menu->addMenuItem( new class_menuitem('protime.feestdagen', 'National holidays', 'feestdagen.php') );

// TAB: PERSONAL PAGES
//$menu->addMenuGroup( new class_menugroup('Personal pages') );
//if ( !$oWebuser->isLoggedIn() ) {
//	$menu->addMenuItem( new class_menuitem('pp.login', 'Login', 'login.php') );
//}

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

class class_menuitem {
	var $code = '';
	var $label = '';
	var $url = '';

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

class class_menugroup {
	var $code = '';
	var $label = '';
	var $menuitems = array();
	var $counter = 0;

	function class_menugroup($label) {
		$this->code = $code;
		$this->label = $label;
	}

	function getCode() {
		return $this->code;
	}

	function getLabel() {
		return $this->label;
	}

	function addMenuItem( $menuitem ) {
		$this->menuitems[] = $menuitem;
	}

	function getMenuItems() {
		return $this->menuitems;
	}

	function showMenuItems() {
		for ( $i = 0; $i < count($this->menuitems); $i++ ) {
			echo '- ' . $this->menuitems[$i]->getLabel() . '<br>';
		}
	}

	function getMenuItemsSubset() {
		global $oWebuser;

		$menuitemssubset = array();

		for ( $i = 0; $i < count($this->menuitems); $i++ ) {
			$a = $this->menuitems[$i];

			$menuitemssubset[] = new class_menuitem( $a->getCode(), $a->getLabel(), $a->getUrl() );
		}
		return $menuitemssubset;
	}
}

class class_menu {
	var $menu = array();

	function class_menu() {
	}

	function addMenuGroup( $menugroup ) {
		$this->menu[] = $menugroup;
	}

	function addMenuItem( $menuitem ) {
		$this->menu[count($this->menu)-1]->addMenuItem($menuitem);
	}

	function show() {
		for ( $i = 0; $i < count($this->menu); $i++ ) {
			echo $this->menu[$i]->getLabel() . '<br>';
			echo $this->menu[$i]->showMenuItems();
		}
	}

	function getMenuSubset() {
		$menusubset = new class_menu();

		for ( $i = 0; $i < count($this->menu); $i++ ) {

			$menuitemssubset = $this->menu[$i]->getMenuItemsSubset();

			if ( count($menuitemssubset) > 0 ) {
				// 
				$menusubset->addMenuGroup( new class_menugroup($this->menu[$i]->getLabel() ) );

				foreach ( $menuitemssubset as $mitem ) {
					$menusubset->addMenuItem( $mitem );
				}
			}
		}

		return $menusubset;
	}
}
?>