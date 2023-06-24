<?php
if (isset($_GET["404"])) {
    http_response_code(404);
} else {
    http_response_code(200);
}
?>
<!doctype html>
<html>

<head>
    <title><?= isset($_GET['404']) ? 'Stránka nenalezena | ' : '' ?>Materiály pro studenty</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <script src="/assets/theme.js"></script>
    <style>
        @import '/assets/theme.css';

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
            color: currentColor;
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
            font-weight: 600;
            padding: 0.5rem;
        }

        .kebab,
        .kebab a {
            color: #555;
        }

        .kebab__inner,
        .kebab__inner a {
            font-weight: normal;
            display: inline-block;
        }

        [data-theme=dark] .kebab,
        [data-theme=dark] .kebab a {
            color: #aaa;
        }
    </style>

    <!-- Matomo -->
    <script>
        var _paq = window._paq = window._paq || [];
        _paq.push(['disableCookies']);
        _paq.push(['trackPageView']);
        _paq.push(['enableLinkTracking']);
        (function() {
            var u = "//www.vitkolos.cz/matomo/";
            _paq.push(['setTrackerUrl', u + 'matomo.php']);
            _paq.push(['setSiteId', '2']);
            var d = document,
                g = d.createElement('script'),
                s = d.getElementsByTagName('script')[0];
            g.async = true;
            g.src = u + 'matomo.js';
            s.parentNode.insertBefore(g, s);
        })();
    </script>
    <!-- End Matomo Code -->
</head>

<body>
    <div class="container">
        <h1><a href="/">iGOH</a>: Materiály<?= isset($_GET['404']) ? ' (stránka nenalezena)' : '' ?></h1>
        <p>Tyto materiály vznikly mezi lety 2017 a 2022. <br>Jsou dílem studentů „béčka“, kteří studovali v letech 2014–2022.</p>
        <?php
        echo getDirectory(getcwd(), dirname($_SERVER['PHP_SELF']));

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
                $isOpen = strpos($_SERVER['REQUEST_URI'], $urlpath . '/' . $file . '/') !== false;
                $openText = $isOpen ? 'open' : '';
                $openText2 = $isOpen ? 'true' : 'false';
                $html .= '<details ' . $openText . '><summary onclick="summaryClicked(\'' . $urlpath . '/' . $file . '/' . '\', ' . $openText2 . ', this);"><span>' . prettyName($file) . '</span></summary>' . getDirectory($dir . '/' . $file, $urlpath . '/' . $file) . '</details>' . PHP_EOL;
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
                'zapisky-vk' => 'Zápisky Vítka Kološe',
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

        function summaryClicked(route, isOpen, el) {
            if (isOpen) {
                window.history.replaceState({}, '', route.substring(0, route.slice(0, -1).lastIndexOf('/') + 1));
            } else {
                window.history.replaceState({}, '', route);
            }

            el.setAttribute('onclick', `summaryClicked('${route}', ${!isOpen}, this);`);
        }
    </script>
</body>

</html>
