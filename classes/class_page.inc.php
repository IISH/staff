<?php 
// version: 2014-01-20

require_once "classes/class_file.inc.php";
require_once "classes/class_misc.inc.php";

class class_page {
	var $page_template;
	var $project_settings;
	var $content;
	var $tab;
	var $title;
	var $color;

	// TODOEXPLAIN
	function class_page($page_template, $project_settings) {
		$this->page_template = $page_template;
		$this->project_settings = $project_settings;
		$this->content = '';
		$this->tab = 0;
		$this->title = '';
		$this->color = '73A0C9';
	}

	// TODOEXPLAIN
	function getPage() {
		global $oWebuser;

		$oFile = new class_file();
		$page = $oFile->getFileSource($this->page_template);
		$page = str_replace('{url}', $this->getUrl(), $page);
		$page = str_replace('{lastmodified}', $this->getLastModified(), $page);

		$page = str_replace('{content}', $this->getContent(), $page);

		$page = str_replace('{title}', $this->getTitle(), $page);
		$page = str_replace('{color}', $this->getColor(), $page);

		$page = str_replace('{menu}', $this->createMenu(), $page);

		// 
		$welcome = 'Welcome';
		$logout = '';
		if ( $oWebuser->isLoggedIn() ) {
//			if ( $_SESSION["presentornot"]["name"] != '' ) {
//            $welcome .= ', ' . $_SESSION["presentornot"]["name"];
			$welcome .= ', ' . $oWebuser->getFirstname() . ' ' . $oWebuser->getLastname();
//			}
			$logout = '<a href="logout.php" onclick="if (!confirm(\'Please confirm logout\')) return false;">(logout)</a>';
		}
		$page = str_replace('{welcome}', $welcome, $page);
		$page = str_replace('{logout}', $logout, $page);

		// als laatste
		$page = str_replace('{date}', class_datetime::getQueryDate(), $page);

		return $page;
	}

	function createMenu() {
		global $menuList;

		$sMenu = '';

		// GROUPS
		$sMenu = "<ul>";

		// TODOTODO MODIFY
		foreach ( $menuList as $a=>$b ) {
			foreach ( $b as $c ) {

				foreach ( $c->getMenuItems() as $mitem ) {
					$sMenu .= "				<li><a href=\"" . $mitem->getUrl() . "\">" . $mitem->getLabel() . "</a></li>\n";
				}

			}
		}

		$sMenu .= "</ul>";

		return $sMenu;
	}

	// TODOEXPLAIN
	function getUrl() {
		return 'https://' . ( $_SERVER["HTTP_X_FORWARDED_HOST"] != '' ? $_SERVER["HTTP_X_FORWARDED_HOST"] : $_SERVER["SERVER_NAME"] ) . $_SERVER["SCRIPT_NAME"];
	}

	// TODOEXPLAIN
	function getLastModified() {
		return 'Last modified: ' . $this->project_settings["last_modified"];
	}

	// TODOEXPLAIN
	function setContent( $content ) {
		$this->content = $content;
	}

	// TODOEXPLAIN
	function getContent() {
		return $this->content;
	}

	// TODOEXPLAIN
	function setTitle( $title ) {
		$this->title = $title;
	}

	// TODOEXPLAIN
	function getTitle() {
		return $this->title;
	}

	// TODOEXPLAIN
	function setColor( $color ) {
		$this->color = $color;
	}

	// TODOEXPLAIN
	function getColor() {
		return $this->color;
	}
}
?>