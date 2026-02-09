<!DOCTYPE html>
<html lang="en">

@include('dashboards.admin.includes.headerlinks')

@livewireStyles

<body class="m-0 font-sans antialiased font-normal text-left  dark:bg-slate-950  text-slate-500 dark:text-white">

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
       
    <main  id="mainContent" class="content-gradient relative h-full duration-200 ease-soft-in-out z-50 rounded-xl">

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
