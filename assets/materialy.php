<?php
if (isset($_GET["404"])) {
	http_response_code(404);
}
?>
<!doctype html>
<html>

<head>
	<title><?= isset($_GET['404']) ? 'Stránka nenalezena | ' : '' ?>Materiály pro studenty</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<style>
		body {
			font-family: sans-serif;
			margin: 1.5rem 2rem;
			line-height: 1.5;
		}

		.container {
			max-width: 800px;
			margin: 0 auto;
		}

		h1 a {
			color: black;
		}

		li,
		details {
			margin: 0.5rem 0;
		}

		details {
			margin-left: -14px;
		}

		summary {
			cursor: pointer;
		}

		summary span {
			text-decoration: underline;
		}

		a:hover,
		summary:hover span {
			text-decoration: none;
		}

		.kebab {
			cursor: pointer;
			color: #555;
			font-weight: 600;
			padding: 0.5rem;
		}

		.kebab__inner,
		.kebab__inner a {
			font-weight: normal;
			color: grey;
			display: inline-block;
		}
	</style>
</head>

<body>
	<div class="container">
		<h1><a href="..">iGOH</a>: Materiály<?= isset($_GET['404']) ? ' (stránka nenalezena)' : '' ?></h1>
		<?php
		$zapisy = '<details>
			<summary><span>Zápisy Vítka Kološe</span></summary>
			<ul>
				<li><a href="https://drive.google.com/drive/folders/1-PCBU3vUn7lCqsebNp0f1KbjsCqc7Pk1?usp=sharing">sexta</a></li>
				<li><a href="https://drive.google.com/drive/folders/1-HBIFa0uipgozbAD7bM4hPgYwLBN5p-7?usp=sharing">septima</a></li>
				<li><a href="https://drive.google.com/drive/folders/1T7OBse-4oGzC-Iq_JPcfnWzU7VkhbZhf?usp=sharing">oktáva</a></li>
			</ul>
		</details>';
		echo getDirectory(getcwd(), dirname($_SERVER['PHP_SELF']), $zapisy);

		function getDirectory($dir, $urlpath, $add = '') {
			$directories = array();
			$files_list = array();
			$files = scandir($dir);
			$html = '';

			foreach ($files as $file) {
				if (substr($file, 0, 1) != '.' && ($urlpath . '/' . $file) != $_SERVER['PHP_SELF']) {
					if (is_dir($dir . '/' . $file)) {
						$directories[] = $file;
					} else {
						$files_list[] = $file;
					}
				}
			}

			$html .= '<ul>' . PHP_EOL;

			foreach ($directories as $key => $file) {
				$html .= '<details><summary><span>' . prettyName($file) . '</span></summary>' . getDirectory($dir . '/' . $file, $urlpath . '/' . $file) . '</details>' . PHP_EOL;
			}

			foreach ($files_list as $key => $file) {
				$html .= "\t" . '<li><a href="' . $urlpath . '/' . $file . '">' . $file . '</a></li>' . PHP_EOL;
			}
			$html .= $add;
			$html .= '</ul>' . PHP_EOL;
			return $html;
		}

		function prettyName($originalName) {
			$names = array(
				'cj' => 'Český jazyk',
				'sm' => 'Slepé mapy',
				'aj' => 'Anglický jazyk',
				'z' => 'Zeměpis',
				'hv' => 'Hudební výchova',
				'ch' => 'Chemie',
				'f' => 'Fyzika',
				'lab' => 'Laboratorní práce',
				'vo' => 'Výchova k občanství',
				'b' => 'Biologie',
				'd' => 'Dějepis',
				'nizsi' => 'Nižší gymnázium',
				'vyssi' => 'Vyšší gymnázium',
				'spolecne' => 'Společné soubory',
				'fr' => 'Francouzský jazyk',
				'ikt' => 'IKT',
				'vv' => 'Výtvarná výchova',
				'zsv' => 'Základy společenských věd'
			);

			return isset($names[$originalName]) ? $names[$originalName] : $originalName;
		}
		?>
	</div>
	<script>
		kebabize();

		function kebabize() {
			[...document.getElementsByTagName('li')].forEach(el => {
				const link = el.querySelector('a').href;

				if (!link.includes('drive.google.com')) {
					el.innerHTML += `<span class="kebab" onclick="kebab(this, '${link}');">⋮</span>`;
				}
			});
		}

		function kebab(el, fullUrl) {
			if (el.innerHTML == '⋮') {
				el.innerHTML = `⋮ &nbsp;<span class="kebab__inner">
					<a href="${fullUrl}">otevřít</a> |
					<a href="${fullUrl}" download>stáhnout</a> |
					<a href="https://docs.google.com/viewerng/viewer?url=${fullUrl}">Google náhled</a> |
					<a href="https://view.officeapps.live.com/op/view.aspx?src=${fullUrl}">MS Office náhled</a>
				</span>`;
			} else {
				el.innerHTML = '⋮';
			}
		}
	</script>
</body>

</html>
