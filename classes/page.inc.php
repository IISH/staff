<?php 

require_once __DIR__ . "/file.inc.php";
require_once __DIR__ . "/misc.inc.php";

class Page {
	protected $page_template;
	protected $content;
	protected $tab;
	protected $title;
	protected $color;
	protected $favicon;

	function __construct($page_template) {
		$this->page_template = $page_template;
		$this->content = '';
		$this->tab = 0;
		$this->title = '';
		//$this->color = '73A0C9';
		$this->color = '707070';
		$this->favicon = 'favicon.ico';
	}

	public function getPageAttributes() {
		global $oWebuser;

		$arr = array();

		$arr['content'] = $this->content;
		$arr['title'] = $this->title;
		$arr['favicon'] = $this->favicon;
		$arr['color'] = $this->color;
		$arr['menu'] = $this->createMenu();

		//
		$welcome = Translations::get('welcome');
		if ( $oWebuser->isLoggedIn() ) {
			$niceName = trim($oWebuser->getNiceFirstLastname());
			if ( $niceName == '' ) {
				$niceName = $_SESSION["loginname"];
			} else {
				$niceName = '<a href="user.php">' . $niceName . '</a>';
			}
			$welcome .= ', ' . $niceName;

			$logout = '<a href="logout.php" onclick="if (!confirm(\'' . Translations::get('confirm') . '\')) return false;">(' . Translations::get('logout') . ')</a>';
		} else {
			$logout = '<a href="login.php">(' . Translations::get('login') . ')</a>';;
		}
		$arr['welcome'] = $welcome;
		$arr['logout'] = $logout;
		$arr['website_name'] = Translations::get('website_name');
		$arr['contact'] = Translations::get('contact');
		$arr['click_to_close_image'] = Translations::get('click_to_close_image');

		//
		if ( !isset($_GET['alert']) ) {
			$_GET['alert'] = '';
		}
		switch ( $_GET['alert'] ) {
			case "next_time":
				$alertMessage = Translations::get('next_time');
				$knawLogin = $oWebuser->getLoginnameKnaw();
				if ( $knawLogin == '' ) {
					// try to find knaw login
					$knawLogin = Synonyms::getLoginName($oWebuser->getLoginname(), 'knaw_login', 'iisg_login');
				}
				if ( $knawLogin == '' ) {
					$alertMessage .= '.';
				} else {
					$alertMessage .= ': ' . $knawLogin;
				}
				break;
			default:
				$alertMessage = '';
		}
		$arr['alertMessage'] = $alertMessage;

		//
		return $arr;
	}

	private function createMenu() {
		global $menu;

		$sMenu = "<ul>";

		//
		foreach ( $menu as $a ) {
			$sMenu .= "				<li class=\"" . $a->getClass() . "\"><a href=\"" . $a->getUrl() . "\">" . $a->getLabel() . "</a></li>\n";
		}

		$sMenu .= "</ul>";

		return $sMenu;
	}

	public function setContent( $content ) {
		$this->content = $content;
	}

	public function setFavicon( $favicon ) {
		$this->favicon = $favicon;
	}

	public function getContent() {
		return $this->content;
	}

	public function setTitle( $title ) {
		$this->title = $title;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getColor() {
		return $this->color;
	}
}
