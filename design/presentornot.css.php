<?php 
header('Content-type: text/css');
require_once "../classes/class_misc.inc.php";
require_once "../classes/class_website_protection.inc.php";

// number of tabs
$t = 10;

// menu/footer color (only 6 char/digit allowed)
$protect = new class_website_protection();
$c = $protect->request('get', 'c', '/^[0-9a-zA-Z]{6,6}$/');
if ( $c == '' ) {
	$c = '#73A0C9';
} else {
	$c = '#' . $c;
}
?>
html, body, input, select {
	font-family: Verdana;
	font-size: 95%;
}

.bold {
	font-weight: bold;
}

.login, .password {
	width: 175px;
}

.error {
	color: red;
}

a, a:visited, a:active, a:hover {
	color: <?php echo $c; ?>;
	text-decoration: none;
	border-bottom: 1px blue dotted;
}

a.PT, a.PT:visited, a.PT:active, a.PT:hover {
	color: black;
	text-decoration: none;
	border-bottom: 0px;
	font-style:italic;
	font-size:80%;
}

a.nolink, a.nolink:visited, a.nolink:active, a.nolink:hover {
	text-decoration: none;
	border-bottom: 0px;
}

a.add, a.add:visited, a.add:active, a.add:hover {
	font-size: 90%;
	font-style:italic;
}

input, select {
	border-width: 1px;
	border-style: solid;
	border-color: <?php echo $c; ?>;
}

.button, .button_login {
	color: <?php echo $c; ?>;
	background-color: white;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	border-radius: 3px;
	padding: 3px;
	width: 75px;
	border: 1px solid <?php echo $c; ?>;
}

h2, h3 {
	color: <?php echo $c; ?>;
	margin-top: 0px;
	margin-bottom: 0px;
	font-size: 15px;
}

hr {
	color: <?php echo $c; ?>;
	border: 1px solid;
}

.contenttitle {
	color: <?php echo $c; ?>;
	font-size: 18px;
	font-weight: bold;
}

div {
	border: 0px solid;
}

div.main {
	width: 960px;
	margin-left: auto;
	margin-right: auto;
}

div.header {
	position: relative;
	margin-top: auto;
	margin-bottom: auto;
}

div.logo {
	position: relative;
	margin-left: -13px;
	margin-bottom: 7px;
	height: 94px;
	width: 122px;
}

div.title {
	position: absolute;
	margin-left: 117px;
	top: 7px;
}

div.welcome {
	float: right;
	margin-top: 5px;
}

div.logout {
	float: right;
	margin-top: 50px;
}

span.name {
	display: block;
	font-family: 'Times New Roman';
	font-size: 18px;
	font-weight: bold;
	color: <?php echo $c; ?>;
	text-align: right;
}

span.logout {
	display: block;
	font-family: 'Times New Roman';
	font-size: 14px;
	font-style: italic;
	color: <?php echo $c; ?>;
	text-align: right;
}

div.content {
	width: 950px;
	border: 1px solid #AAAAAA;
	margin-top: 5px;
	margin-bottom: 5px;
	padding-top: 5px;
	padding-bottom: 15px;
	padding-left: 5px;
	padding-right: 5px;
	-moz-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
}

div.footer {
	color: white;
	background-color: <?php echo $c; ?>;
	text-align: right;
	border: 1px solid #AAAAAA;
}

div.hidden {
	display:none;
}

span.title {
	display: block;
	font-family: 'Times New Roman';
	font-size: 32px;
	font-weight: bold;
	color: <?php echo $c; ?>;
}

span.subtitle {
	display: block;
	font-family: 'Times New Roman';
	font-size: 16px;
	font-weight: bold;
	color: <?php echo $c; ?>;
}

.comment {
	line-height: 95%;
	font-size: 85%;
	font-style:italic;
}

div.checkedInOut {
	margin-top: 20px;
	margin-bottom: 10px;
}

.presentornot_absence {
	font-size: 80%;
	text-align: center;
}

a.checkinouttime {
	color: white;
	text-decoration: double;
	border-bottom: 0px;
}

#menu {
	margin-top: 9px;
	margin-bottom: 0px;
	padding-top: 8px;
	padding-bottom: 8px;
	padding-left: 0px;
	padding-right: 0px;
	-moz-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
}

#menu ul {
	margin-top: 0px;
	margin-bottom: 0px;
	margin-left: 0px;
	margin-right: 0px;
	list-style-type: none;
	padding-left: 0px;
}

#menu li {
	display: inline;
	padding-right: 20px;
}

a.favourites_on:link, a.favourites_on:visited {
	color: #FFD700;
	font-size: 120%;
}

a.favourites_off:link, a.favourites_off:visited {
	color: lightgrey;
	font-size: 120%;
}
