
var preferredmode;
var systempreferredmode;
var currentmode;
function initializeTheme(){
	console.log("initializing theme");
	if(localStorage.getItem('preferredmode') == 'dark'){
		swapCss("dark");
	}
	else if(localStorage.getItem('preferredmode') == 'light'){
		swapCss("light");
	}else if(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)
	{
		swapCss("dark");
	}
	else{
		swapCss("light");
	}
}
initializeTheme();


function buttonwaspressed() {
	if (currentmode == "dark")
	{
		swapCss("light");
	}
	else if(currentmode == "light") {
		swapCss("dark");
	}
	
}

function swapCss(a){
	if(a == "dark"){
		document.getElementById("theme").setAttribute('href', "style_dark.css?v1.1");
		document.getElementById("modebuttontext").innerText = "Light mode :(";
		currentmode = "dark";
		localStorage.setItem('preferredmode', 'dark');
		
	}
	else if(a == "light"){
		
		document.getElementById("theme").setAttribute('href', "style.css?v1.1");
		document.getElementById("modebuttontext").innerText = "Dark mode :)";
		currentmode = "light";
		localStorage.setItem('preferredmode', 'light');
	}
}
var changed = false;

function calculate() {
	if (!document.getElementById('znamky')) {
		return;
	}

	let znamky = document.getElementById('znamky').value;
	let vahy = document.getElementById('vahy').value;

	document.getElementById('vysledek').innerHTML = getAverageMark(znamky, vahy);
}

function getAverageMark(znamky, vahy, justNumber) {
	var znamka = 0, vaha = 0;

	znamky = znamky.match(/([0-9]+((\.[0-9])|(\-)?))|N|X|S|\?/g);
	vahy = vahy.match(/([0-9]+(\.[0-9]*)?)|C/g);

	if (!znamky) {
		document.getElementById('vysledek').innerHTML = '';
		return '';
	}

	for (var x = 0; x < znamky.length; x++) {
		if (znamky[x] == "N" || znamky[x] == "X" || znamky[x] == "S" || znamky[x] == "?") {
			znamky[x] = 0;
			vahy[x] = 0;
		}
		if (vahy[x] == "C") {
			vahy[x] = 10;
		}
		if (/([0-9]\-)/.test(znamky[x])) {
			znamky[x] = znamky[x].substring(0, 1);
			znamky[x] = znamky[x] + ".5";
		}
		znamka += parseFloat(znamky[x]) * parseFloat(vahy[x]);
		vaha += parseFloat(vahy[x]);
	}
	var prumer = znamka / vaha;
	let number = '0';

	if (isNaN(prumer)) {
		prumer = "Průměr známek nelze vypočítat.";
	} else {
		prumer = prumer.toFixed(justNumber ? 1 : 3);
		number = parseFloat(prumer).toString().replace(".", ",");
		prumer = `
			Průměr známek: <b id="result">${number}</b></span>
			<br>
			<small style="color:grey">
				Pomohla ti tato kalkulačka? Nenech si to pro sebe a
				<a class="link" href=\"https://www.facebook.com/dialog/share?app_id=1796482747030911&display=page&href=http%3A%2F%2Fwww.igoh.tk%2Fprumer%2F&redirect_uri=https%3A%2F%2Fwww.igoh.tk%2Fprumer%2F\">
					sdílej ji na Facebooku!
				</a>
			</small>
		`;
	}

	return justNumber ? number : prumer;
}

function keyUp(obj, ev) {
	obj.value = obj.value.replace(/[\s\*\,\;]/g, '\t');
	obj.value = obj.value.replace(/\+/g, '-');
	obj.value = obj.value.replace(/\#/g, 'N');
	if (ev.keyCode === 13) {
		calculate();
	} else if (!changed) {
		changed = true;
		document.querySelectorAll('nav a').forEach(a => a.setAttribute('style', (a.getAttribute('style') || '').replace('solid', 'dotted')));
	}
}

skrytNapovedu();
showMenu();

if (window.history.replaceState) {
	window.history.replaceState(null, null, window.location.href.replace(/\?.*/g, ''));
}

function napoveda(e) {
	e.preventDefault();
	document.getElementById("napoveda").innerHTML = '<a href="#" class="link" onclick="skrytNapovedu(event);" tabindex="0" id="help">Schovat nápovědu...</a> <br>' +
		'Jednotlivé známky a váhy můžeš oddělit mezerami, hvězdičkami, čárkami, středníky, případně klávesami Pauza nebo Čekat. ' +
		'Místo 1- můžeš napsat 1+ a místo N (nepsal/a) lze použít #. <br>' +
		'Měj prosím na paměti, že i nula se chová jako známka, pokud nemá nulovou váhu. (Když je ale jedna ze známek N, její váha se automaticky anuluje.)<br>' +
		'Pokud máš pocit, že tady nějaké známky chybí, zkus obnovit stránku.';
	document.getElementById("help").focus();
}

function skrytNapovedu(e) {
	if (document.getElementById("napoveda")) {
		document.getElementById("napoveda").innerHTML = '<a href="#" class="link" onclick="napoveda(event);" tabindex="0" id="help">Nápověda ke kalkulačce...</a>';
	}
	if (e) {
		e.preventDefault();
		document.getElementById("help").focus();
	}
}

function fill(e, marks, weights) {
	document.querySelectorAll('nav a').forEach(a => a.removeAttribute('style'));

	if (e) {
		e.preventDefault();
		e.currentTarget.setAttribute('style', marks ? 'text-decoration:underline;text-decoration-style:solid' : '');
	}

	changed = false;
	marks = marks || '';
	weights = weights || '';
	document.getElementById('znamky').value = marks.replace(/ /g, '\t');
	document.getElementById('vahy').value = weights.replace(/ /g, '\t');
	calculate();
}

// function getAll(e) {
// 	if (e) { e.preventDefault(); }
// 	let links = document.querySelectorAll('.bottom-row a:not(:last-child)');
// 	let data = [...links].map(el => {
// 		el.click();
// 		return [el.textContent.trim(), document.getElementById('result').textContent];
// 	});
// 	data.sort((a, b) => b[1].replace(',', '.') - a[1].replace(',', '.'));
// 	let result = data.map(row => `<b>${row[0]}</b>&nbsp;&nbsp;${row[1]}&nbsp;&nbsp;&nbsp;&nbsp; `);
// 	fill();
// 	document.getElementById('vysledek').innerHTML = 'Všechny průměry<br>' + result.join('');
// }

function showMenu() {
	data.forEach(item => { item.prumer = getAverageMark(item.znamky, item.vahy, true); });
	data.sort((a, b) => (b.prumer.replace(',', '.') - a.prumer.replace(',', '.')));
	let result = data.map(item => item.znamky == ' ' ? '' : `<a href="#" onclick="fill(event, '${item.znamky}', '${item.vahy}');"><b>${item.predmet}</b>&nbsp;&nbsp;${item.prumer}</a>`);
	document.getElementById('menu').innerHTML = result.join('');
}

