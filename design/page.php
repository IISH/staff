<!doctype html>

<html>
<head>
	<title>{title}</title>
	<script language="JavaScript" src="javascript/shared.js"></script>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
	<meta name="Robots" content="noindex,nofollow">
	<style type="text/css" media="all">@import url("/design/presentornot.css.php?c={color}");</style>
<script language="Javascript">
<!--
	// source: http://stackoverflow.com/questions/2400935/browser-detection-in-javascript
	navigator.browser_detection= (function(){
		var ua= navigator.userAgent, tem,
			M= ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];
		if(/trident/i.test(M[1])){
			tem=  /\brv[ :]+(\d+)/g.exec(ua) || [];
			return 'IE '+(tem[1] || '');
		}
		if(M[1]=== 'Chrome'){
			tem= ua.match(/\b(OPR|Edge)\/(\d+)/);
			if(tem!= null) return tem.slice(1).join(' ').replace('OPR', 'Opera');
		}
		M= M[2]? [M[1], M[2]]: [navigator.appName, navigator.appVersion, '-?'];
		if((tem= ua.match(/version\/(\d+)/i))!= null) M.splice(1, 1, tem[1]);
		return M.join(' ');
	})();

	function showFloorPlan( image, $floor ) {
		var browser = navigator.browser_detection;
		// dirty solution for IE policy problems at work
		if ( browser.toLowerCase().substring(0,2) == 'ie' ) {
			// IE
			window.open('floor.php?f=' + $floor,'_top')
		} else {
			// other browsers
			var elem = document.getElementById('imageDiv');
			elem.innerHTML = '<br><br><a href="#" onclick="return closeImageDiv();"><img src="' + image + '" title="{click_to_close_image}" border="0"></a>';
			elem.style.width = '100%';
			elem.style.height = '100%';
			elem.style.display = 'flex';
		}

		return false;
	}

	function closeImageDiv() {
		document.getElementById('imageDiv').style.display = 'none';
		return false;
	}
// -->
</script>
</head>
<body>

<div name="imageDiv" id="imageDiv" class="imageDiv"></div>

<div class="main">

	<div class="header">
	<table width="100%" cellspacing="0" cellpadding="0">
		<tr>
			<td rowspan="2" width="130"><div class="logo"><img src="images/logo/{color}.png"></div></td>
			<td><span class="title">{website_name}</span></td>
			<td align="right">
				<span class="name">{welcome}</span><br>
				<span class="logout">{logout}</span><br>
				<a href="en.php"><img src="images/misc/en.png" border="0"></a>
				<a href="nl.php"><img src="images/misc/nl.png" border="0"></a>
			</td>
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
			<td>&nbsp;<a href="contact.php">{contact}</a></td>
		</tr>
		</table>
	</div>

</div>

<script language="JavaScript">
<!--
closeImageDiv();
// -->
</script>

</body>
</html>