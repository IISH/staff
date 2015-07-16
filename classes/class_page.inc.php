<?php 

require_once dirname(__FILE__) . "/class_file.inc.php";
require_once dirname(__FILE__) . "/class_misc.inc.php";

// TODOEXPLAIN
class class_page {
	protected $page_template;
	protected $content;
	protected $tab;
	protected $title;
	protected $color;

	// TODOEXPLAIN
	function __construct($page_template) {
		$this->page_template = $page_template;
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
		$welcome = class_translations::get('welcome');
		$logout = '';
		if ( $oWebuser->isLoggedIn() ) {
			$welcome .= ', ' . $oWebuser->getNiceFirstLastname();

			$logout = '<a href="logout.php" onclick="if (!confirm(\'' . class_translations::get('confirm') . '\')) return false;">(' . class_translations::get('logout') . ')</a>';
		}
		$page = str_replace('{welcome}', $welcome, $page);
		$page = str_replace('{logout}', $logout, $page);
		$page = str_replace('{website_name}', class_translations::get('website_name'), $page);
		$page = str_replace('{contact_and_questions}', class_translations::get('contact_and_questions'), $page);
		$page = str_replace('{last_modified}', class_translations::get('last_modified'), $page);

		// als laatste
		$page = str_replace('{date}', class_datetime::getQueryDate(), $page);

		return $page;
	}

	// TODOEXPLAIN
	function createMenu() {
		global $menu;

		$sMenu = "<ul>";

		//
		foreach ( $menu as $a ) {
			$sMenu .= "				<li><a href=\"" . $a->getUrl() . "\">" . $a->getLabel() . "</a></li>\n";
		}

		$sMenu .= "</ul>";

		return $sMenu;
	}

	// TODOEXPLAIN
	function getUrl() {
		return 'https://' . ( isset($_SERVER["HTTP_X_FORWARDED_HOST"]) && $_SERVER["HTTP_X_FORWARDED_HOST"] != '' ? $_SERVER["HTTP_X_FORWARDED_HOST"] : $_SERVER["SERVER_NAME"] ) . $_SERVER["SCRIPT_NAME"];
	}

	// TODOEXPLAIN
	function getLastModified( $dateformat = "j F Y") {
		return date($dateformat, strtotime(class_settings::get("last_modified")));
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
