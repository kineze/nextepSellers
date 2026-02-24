<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/nextep-icon.webp') }}" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/nextep-icon.webp') }}">
    <title>Nextep</title>

    <style>
        html.theme-preload-dark,
        html.theme-preload-dark body {
            background-color: #020617;
            color: #e2e8f0;
        }

        html.theme-preload-comfort,
        html.theme-preload-comfort body {
            background-color: #fdf6e3;
            color: #586e75;
        }
    </style>
    <script>
        (function() {
            try {
                var theme = localStorage.getItem('nextep-theme-pref') || 'light';
                var root = document.documentElement;

                root.classList.remove('theme-preload-dark', 'theme-preload-comfort');
                if (theme === 'dark') {
                    root.classList.add('dark', 'theme-preload-dark');
                } else if (theme === 'comfort') {
                    root.classList.add('theme-preload-comfort');
                }
            } catch (e) {
                // ignore localStorage/theme preload errors
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
