var browser_IE4 = (document.all) ? 1 : 0;
var browser_NS6 = (document.getElementById&&!document.all) ? 1 : 0;
var browser_SAFARI = (navigator.userAgent.indexOf('Safari')>-1) ? 1 : 0;

//
function open_page(url) {
  window.open(url, '_top');

	return false;
}

//
function doc_submit(pressedbutton) {
	if (browser_SAFARI) { // SAFARI
		document.formulier.pressedbutton.value=pressedbutton;
		document.formulier.submit();
	} else if (browser_IE4) { // OPERA & IE
		document.all.formulier.pressedbutton.value=pressedbutton;
		document.all.formulier.submit();
	} else if (browser_NS6) { // FIREFOX
		document.formulier.pressedbutton.value=pressedbutton;
		document.formulier.submit();
	} else {
		document.all.formulier.pressedbutton.value=pressedbutton;
		document.all.formulier.submit();
	}

	return true;
}

//
function doc_delete(pressedbutton) {
	input_box=confirm('Please confirm delete');
	if (input_box==true) {

		if (browser_SAFARI) { // SAFARI
			document.formulier.pressedbutton.value=pressedbutton;
			document.formulier.FORM_isdeleted.value='1';
			document.formulier.submit();
		} else if (browser_IE4) { // OPERA & IE
			document.all.formulier.pressedbutton.value=pressedbutton;
			document.all.formulier.FORM_isdeleted.value='1';
			document.all.formulier.submit();
		} else if (browser_NS6) { // FIREFOX
			document.formulier.pressedbutton.value=pressedbutton;
			document.formulier.FORM_isdeleted.value='1';
			document.formulier.submit();
		} else {
			document.all.formulier.pressedbutton.value=pressedbutton;
			document.all.formulier.FORM_isdeleted.value='1';
			document.all.formulier.submit();
		}

		return true;
	} else {
		return false;
	}
}

// hide & show upload new photo div
function hideShowUploadDiv() {
	var x = document.getElementById("uploadDiv");
	if (x.style.display === "none") {
		x.style.display = "block";
	} else {
		x.style.display = "none";
	}

	return false;
}
