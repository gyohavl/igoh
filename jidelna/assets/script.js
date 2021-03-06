"use strict";

window.addEventListener("popstate", urlChanged);

var current = 0;
urlChanged();

function urlChanged() {
	loading();
	// document.getElementById("content").innerHTML = "";
	loadMeals();
}

function redir(obj, e) {
	e.preventDefault();
	urlChanged();
}

function loading() {
	document.body.setAttribute("class", "loading");
}

function loaded() {
	document.body.removeAttribute("class");
}


function loadMeals() {
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200 && current == 0) {
			document.getElementById("content").innerHTML = this.responseText;
			loaded();
			if(this.responseText.indexOf("Přihlášení") != -1) {
				document.body.setAttribute("class", "login");
				// document.getElementById("username").focus();
				// let val = document.getElementById("username").value;
				// document.getElementById("username").value = "";
				// document.getElementById("username").value = val;
			}
		}
	};
	xhttp.open("GET", "assets/get.php", true);
	xhttp.send();
}

function checkboxClick(el, url) {
	document.body.setAttribute("class", "loading");
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			// console.log(this.responseText);
			if(this.responseText.indexOf("Proveďte znovunačtení aplikace.") == -1) {
				loadMeals();
			} else {
				alert("Operace se nepovedla, kontaktuj tvůrce aplikace na vit.kolos@gmail.com.")
				//deleteCookie("susenky");
				//location.reload();
			}
		}
	};
	xhttp.open("POST", "assets/order.php", true);
	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send(url);
}

//cookies
function deleteCookie(cname) {
	var expires = "expires=10";
	document.cookie = cname + "=;" + expires + ";path=/";
}

function setCookie(cname, cvalue, exdays) {
	var d = new Date();
	d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
	var expires = "expires=" + d.toUTCString();
	document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
	var name = cname + "=";
	var ca = document.cookie.split(';');
	for(var i = 0; i < ca.length; i++) {
		var c = ca[i];
		while(c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if(c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
		}
	}
	return undefined;
}
