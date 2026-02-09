<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/nextep-icon.webp') }}" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/nextep-icon.webp') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

     <title>Nextep</title>

    <link href="{{asset('/assets/css/theme.css')}}" rel="stylesheet" />
    
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

<style>
  /* Global scrollbar styles for dark mode */
.dark #sidebar::-webkit-scrollbar,
.dark #subSidebar::-webkit-scrollbar {
  width: 8px;
}

.dark #sidebar::-webkit-scrollbar-track,
.dark #subSidebar::-webkit-scrollbar-track {
  background: #1e293b; /* dark slate background */
}

.dark #sidebar::-webkit-scrollbar-thumb,
.dark #subSidebar::-webkit-scrollbar-thumb {
  background-color: #000; /* solid black */
  border-radius: 6px;
}
</style>
  </head>

  
