<!doctype html>

<html>
<head>
	<title>{title}</title>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
	<meta name="Robots" content="noindex,nofollow">
	<link rel="stylesheet" href="jquery/jquery-ui.css" />
	<script src="jquery/jquery-1.8.2.js"></script>
	<script src="jquery/jquery-ui.js"></script>
	<style type="text/css" media="all">@import url("design/timecard.css.php?c={color}");</style>
</head>
<body>

<div class="main">

	<div class="header">
		<div class="welcome"><span><span class="name">{welcome}</span><span class="logout">{logout}</span></span></div>
		<div class="logo"><img src="images/logo-iisg/{color}.png"></div>
		<div class="title"><span><span class="title">present or not</span><span class="subtitle" id="menu">{menu}</span></span></div>
	</div>

	<div class="content">{content}</div>

	<div class="footer">{url} - {lastmodified}</div>

</div>

</body>
</html>