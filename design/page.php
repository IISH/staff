<!doctype html>

<html>
<head>
	<title>{title}</title>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
	<meta name="Robots" content="noindex,nofollow">
	<style type="text/css" media="all">@import url("/design/presentornot.css.php?c={color}");</style>
</head>
<body>

<div class="main">

	<div class="header">
	<table width="100%" cellspacing="0" cellpadding="0">
		<tr>
			<td rowspan="2"><div class="logo"><img src="images/logo/{color}.png"></div></td>
			<td><span class="title">{website_name}</span></td>
			<td align="right">
				<span class="name">{welcome}</span>
				<br>
				<a href="en.php"><img src="images/misc/en.png"></a>
				<a href="nl.php"><img src="images/misc/nl.png"></a>
				<br>
				<span class="logout">{logout}</span></td>
		</tr>
		<tr>
			<td colspan="3"><span class="subtitle" id="menu">{menu}</span></td>
		</tr>
	</table>
	</div>

	<div class="content">{content}</div>

	<div class="footer">
		<table width="100%" cellspacing="0" cellpadding="0">
		<tr>
			<td>&nbsp;<a href="contact.php">{contact_and_questions}</a></td>
			<td align="right">{url}&nbsp;</td>
		</tr>
		</table>
	</div>

</div>

</body>
</html>