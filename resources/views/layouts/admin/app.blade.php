<!DOCTYPE html>
<html lang="en">

@include('dashboards.admin.includes.headerlinks')

@livewireStyles

<body class="m-0 bg-slate-50 font-sans font-normal antialiased text-left text-slate-500 dark:bg-slate-950 dark:text-white">

    @auth
        <script>
            window.userId = {{ auth()->id() }};
        </script>
    @endauth

    @role('Admin')
        @include('dashboards.admin.includes.sidebar')
    @endrole

    @role('Marketer')
        @include('dashboards.marketer.includes.sidebar')
    @endrole
       
    <main id="mainContent" class="content-gradient relative z-50 min-h-screen rounded-xl duration-200 ease-soft-in-out">

        <div id="app">
        @include('dashboards.admin.includes.nav')

            @yield('content')
      </div>

    </main>
    
    @include('dashboards.admin.includes.slider')
 
</body>

@livewireScripts

@include('dashboards.admin.includes.footerlinks')
@stack('scripts')

</html>
