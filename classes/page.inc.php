<?php 

require_once dirname(__FILE__) . "/file.inc.php";
require_once dirname(__FILE__) . "/misc.inc.php";

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
		$this->color = '73A0C9';
		$this->favicon = 'favicon.ico';
	}

	public function getPage() {
		global $oWebuser;

		$oFile = new class_file();
		$page = $oFile->getFileSource($this->page_template);

		$page = str_replace('{content}', $this->content, $page);

		$page = str_replace('{title}', $this->title, $page);
		$page = str_replace('{favicon}', $this->favicon, $page);
		$page = str_replace('{color}', $this->color, $page);
		$page = str_replace('{menu}', $this->createMenu(), $page);

		// 
		$welcome = Translations::get('welcome');
		$logout = '';
		if ( $oWebuser->isLoggedIn() ) {
			$niceName = trim($oWebuser->getNiceFirstLastname());
			if ( $niceName == '' ) {
				$niceName = '...';
			}
			$niceName = '<a href="user.php">' . $niceName . '</a>';

			$welcome .= ', ' . $niceName;

			$logout = '<a href="logout.php" onclick="if (!confirm(\'' . Translations::get('confirm') . '\')) return false;">(' . Translations::get('logout') . ')</a>';
		} else {
			$logout = '<a href="login.php">(' . Translations::get('login') . ')</a>';;
		}
		$page = str_replace('{welcome}', $welcome, $page);
		$page = str_replace('{logout}', $logout, $page);
		$page = str_replace('{website_name}', Translations::get('website_name'), $page);
		$page = str_replace('{contact}', Translations::get('contact'), $page);
		$page = str_replace('{click_to_close_image}', Translations::get('click_to_close_image'), $page);

		// als laatste
		$page = str_replace('{date}', class_datetime::getQueryDate(), $page);

		return $page;
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
		$logout = '';
		if ( $oWebuser->isLoggedIn() ) {
			$niceName = trim($oWebuser->getNiceFirstLastname());
			if ( $niceName == '' ) {
				$niceName = '...';
			}
			$niceName = '<a href="user.php">' . $niceName . '</a>';

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

//	public function getUrl() {
//		return 'https://' . ( isset($_SERVER["HTTP_X_FORWARDED_HOST"]) && $_SERVER["HTTP_X_FORWARDED_HOST"] != '' ? $_SERVER["HTTP_X_FORWARDED_HOST"] : $_SERVER["SERVER_NAME"] ) . $_SERVER["SCRIPT_NAME"];
//	}

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
