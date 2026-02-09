<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/nextep-icon.webp') }}" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/nextep-icon.webp') }}">
    <title>Nextep</title>

    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:title" content="Nextep" />
    <meta property="og:description" content="Nextep" />
    <meta property="og:image" content="{{ asset('assets/img/nextep-icon.webp') }}" />
    <meta property="og:image:alt" content="Nextep logo" />
    <meta property="og:image:type" content="image/webp" />
    <meta property="og:image:width" content="630" />
    <meta property="og:image:height" content="630" />
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>
