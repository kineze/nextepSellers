<!DOCTYPE html>
<html lang="en">

@include('site.includes.headerlinks')

<body class="m-0 font-sans antialiased text-slate-600 dark:bg-slate-950 dark:text-white">
    <div id="app" class="min-h-screen bg-slate-50 dark:bg-slate-950">
        @include('site.includes.nav')

        <main class="mx-auto w-full max-w-6xl px-6 py-10">
            @yield('content')
        </main>

        @include('site.includes.footer')
    </div>

    @include('site.includes.footerlinks')
    @stack('scripts')
</body>

</html>
