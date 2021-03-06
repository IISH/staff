<?php 
header('Content-type: text/css');
require_once "../classes/misc.inc.php";

// menu/footer color (only 6 char/digit allowed)
//$protect = new WebsiteProtection();
//$c = $protect->request('get', 'c', '/^[0-9a-zA-Z]{6,6}$/');
//if ( $c == '' ) {
	//$c = '#73A0C9';
	$c = '#707070;';
//} else {
//	$c = '#' . $c;
//}
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

a
, a:visited
, a:active
, a:hover {
	color: <?php echo $c; ?>;
	text-decoration: none;
	border-bottom: 1px dotted <?php echo $c; ?>;
}

a.nolink
, a.nolink:visited
, a.nolink:active
, a.nolink:hover {
	text-decoration: none;
	border-bottom: 0;
}

a.add
, a.add:visited
, a.add:active
, a.add:hover {
	font-size: 90%;
	font-style:italic;
}

input, select {
	border-width: thin;
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
	border: thin solid <?php echo $c; ?>;
}

h1 {
	color: <?php echo $c; ?>;
	margin-top: 0;
	margin-bottom: 0;
	font-size: 15px;
}

hr {
	color: <?php echo $c; ?>;
	border: thin solid;
}

.contenttitle {
	color: <?php echo $c; ?>;
	font-size: 18px;
	font-weight: bold;
}

div {
	border: 0 solid;
}

div.main {
	width: 1200px;
	margin-left: auto;
	margin-right: auto;
}

div.header {
	margin-top: auto;
	margin-bottom: auto;
}

div.title {
	position: absolute;
	margin-left: 117px;
	top: 7px;
}

span.name {
	font-family: 'Times New Roman';
	font-size: 18px;
	font-weight: bold;
	color: <?php echo $c; ?>;
}

span.logout {
	font-family: 'Times New Roman';
	font-size: 14px;
	font-style: italic;
	color: <?php echo $c; ?>;
	text-align: right;
}

div.content {
	width: 1190px;
	border: thin solid #AAAAAA;
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
	background-color: <?php echo $c; ?>;
	border: thin solid #AAAAAA;
	color: white;
}

div.footer a
, div.footer a:visited
, div.footer a:active
, div.footer a:hover {
	color: white;
	text-decoration: none;
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
	border-color: black;
}

a.checkinouttime {
	color: white;
	text-decoration: double;
	border-bottom: 0;
}

#menu {
	margin-top: 9px;
	margin-bottom: 0;
}

#menu ul {
	margin-top: 0;
	margin-bottom: 1px;
	margin-left: 0;
	margin-right: 0;
	list-style-type: none;
	padding-left: 0;
}

#menu li {
	display: inline;
	margin-right: 2px;
	padding: 3px 6px;
	border-style: solid;
	border-width: thin;
	border-color: #AAAAAA;
	border-bottom: 0;
	background-color: <?php echo $c; ?>;
}

#menu li a
, #menu li a:visited
, #menu li a:active
, #menu li a:hover {
	color: white;
}

a.favourites_on:link, a.favourites_on:visited {
	color: #FFD700;
	font-size: 120%;
}

a.favourites_off:link, a.favourites_off:visited {
	color: grey;
	font-size: 120%;
}

div.incaseofemergency {
	font-size: 130%;
	margin-top: 15px;
	margin-bottom: 15px;
}

span.incaseofemergencynumber {
	font-weight: bold;
	color: red;
}

div.photobook {
	margin-bottom: 30px;
	margin-right: 20px;
}

table.photobook {
	text-align: center;
	width: 210px;
	border-spacing: 0;
	border-collapse: collapse;
}

tr.photobook {
	text-align: center;
}

td.photobook {
	border-style: solid;
	border-width: thin;
	border-color: #AAAAAA;
}

table.employee_schedule, table.special_numbers {
	border-spacing: 0;
	border-collapse: collapse;
}

tr.employee_schedule {
}

th.employee_schedule {
	border-style: solid;
	border-width: thin;
	border-color: grey;
	font-size: 90%;
	width: 35px;
	text-align: center;
}

td.employee_schedule {
	border-style: solid;
	border-width: thin;
	border-color: grey;
	font-size: 90%;
	text-align: center;
	padding-left: 6px;
	padding-right: 6px;
	padding-top: 2px;
	padding-bottom: 2px;
}

th.employee_schedule_accentuate {
	background-color: yellow;
}

td.employee_schedule_accentuate {
	background-color: yellow;
}

.imageDiv {
	display: hidden;
	justify-content: center; /* align horizontal */
	align-items: center; /* align vertical */
	position:absolute;
	top:0;
	left:0;
	width:100%;
	height:100%;
	z-index:1000;
	background: rgba(211,211,211,0.8);
	text-align: center;
}

div.personalpage {
	float: left;
	margin-right: 15px;
}

.clearBoth {
	clear: both;
}

table.fire {
	border-spacing: 0;
	border-collapse: collapse;
}

table.fire tr th {
	/* font-size: 110%; */
	padding: 6px;
	text-align: left;
	border-style: solid;
	border-width: thin;
	border-color: grey;
}

table.fire tr td {
	/* font-size: 110%; */
	padding: 6px;
	border-style: solid;
	border-width: thin;
	border-color: grey;
}

.fire_header {
	/* font-size: 110% !important; */
	border-style: none !important;
}

table.fire a
, table.fire a:visited
, table.fire a:active
, table.fire a:hover
{
	color: black;
	border-bottom: 0
}

#menu li.fire {
	background-color: red;
	margin-right: 0 !important;
}

#menu li a
, ul li a:visited
, ul li a:active
, ul li a:hover {
	text-decoration: none;
	border-bottom: 0;
}

#menu li.fire a
, ul li.fire a:visited
, ul li.fire a:active
, ul li.fire a:hover {
	color: white;
}

::-webkit-input-placeholder { /* Chrome/Opera/Safari */
	color: darkgrey;
}
::-moz-placeholder { /* Firefox 19+ */
	color: darkgrey;
}
:-ms-input-placeholder { /* IE 10+ */
    color: darkgrey;
}
:-moz-placeholder { /* Firefox 18- */
    color: darkgrey;
}

.minxcharacters {
    font-size: 80%;
}

.blink {
    animation: blinker 3s linear infinite;
    color: #C62431;
    font-weight: bold;
}

@keyframes blinker {
    50% { opacity: 0; }
}


/* NATIONAL HOLIDAYS */
table.nationalholidays {
    border-spacing: 0;
    border-collapse: collapse;
}

table.nationalholidays tr th {
    padding: 0 6px 0 0;
    text-align: left;
}

table.nationalholidays tr td {
    padding: 0 6px 0 0;
}

div.warningalert {
	width: 60%;
	display: block;
	margin-left: auto;
	margin-right: auto;
	border: thin solid #C62431;
	color: #C62431;
	margin-bottom: 5px;
	padding-top: 5px;
	padding-bottom: 5px;
	padding-left: 5px;
	padding-right: 5px;
	-moz-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
	text-align: center;
	font-weight: bold;
}

a img
, a:link img
, a:visited img
, a:hover img
, a:active img {
	border: 0 none;
	text-decoration: underline;
}

.image-link {
	border: 0 none;
	text-decoration: underline;
}
