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
        <p>Tyto materiály vznikly mezi lety 2017 a 2022.</p>
        <?php
        echo getDirectory(getcwd(), dirname($_SERVER['PHP_SELF']));

        function getDirectory($dir, $urlpath, $add = '') {
            $requestURI = urldecode($_SERVER['REQUEST_URI']);
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

            foreach ($directories as $file) {
                $filePath = $urlpath . '/' . $file . '/';
                $isOpen = strpos($requestURI, $filePath) !== false;
                $openText = $isOpen ? 'open' : '';
                $html .= '<details ' . $openText . ' data-path="' . $filePath . '"><summary><span>'
                    . prettyName($file) . '</span></summary>'
                    . getDirectory($dir . '/' . $file, $urlpath . '/' . $file) . '</details>' . PHP_EOL;
            }

            foreach ($files_list as $file) {
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
                'zsv' => 'Základy společenských věd',
                '1-prima' => 'Prima',
                '2-sekunda' => 'Sekunda',
                '3-tercie' => 'Tercie',
                '4-kvarta' => 'Kvarta',
                '5-kvinta' => 'Kvinta',
                '6-sexta' => 'Sexta',
                '7-septima' => 'Septima',
                '8-oktava' => 'Oktáva'
            );

            return isset($names[$originalName]) ? $names[$originalName] : $originalName;
        }
        ?>
    </div>
    <script>
        kebabize();
        addToggleListeners();
        var matomoLastNotified = Date.now();

        function kebabize() {
            [...document.getElementsByTagName('li')].forEach(function(el) {
                const link = el.querySelector('a').href;

                if (!link.includes('drive.google.com')) {
                    el.innerHTML += `<span class="kebab" onclick="kebab(this, '${link}');">⋮</span>`;
                }
            });
        }

        function addToggleListeners() {
            [...document.getElementsByTagName('details')].forEach(function(el) {
                el.addEventListener('toggle', function(event) {
                    detailsToggled(el.getAttribute('data-path'), el.open);
                })
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

        function detailsToggled(route, wasOpened, byUser) {
            // is fired on page load (if some details are opened by default)

            if (wasOpened) {
                window.history.replaceState({}, '', route);
                notifyMatomo(route);
            } else {
                window.history.replaceState({}, '', route.substring(0, route.slice(0, -1).lastIndexOf('/') + 1));
            }
        }

        function notifyMatomo(route) {
            // the primary purpose is to prevent logging page load repeatedly
            var currentMillis = Date.now();
            const cooldownMillis = 2000;

            if (matomoLastNotified + cooldownMillis < currentMillis) {
                _paq.push(['setCustomUrl', route]);
                _paq.push(['trackPageView']);
            }
        }
    </script>
</body>

</html>
