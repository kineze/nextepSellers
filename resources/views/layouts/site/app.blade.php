<!DOCTYPE html>
<html lang="en">

@include('site.includes.headerlinks')

<body class="m-0 font-sans antialiased text-slate-600 dark:bg-slate-950 dark:text-white">
    @php
        $isLanding = request()->routeIs('index');
    @endphp
    <div id="app" class="flex min-h-screen flex-col bg-slate-50 dark:bg-slate-950">
        @include('site.includes.nav')

        <main class="mx-auto w-full max-w-screen-2xl flex-1 px-6">
            @yield('content')
        </main>

        @include('site.includes.footer')
    </div>

    @include('site.includes.footerlinks')
    @stack('scripts')
</body>

</html>
