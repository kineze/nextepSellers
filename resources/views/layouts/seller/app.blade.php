<!DOCTYPE html>
<html lang="en">
@include('site.includes.headerlinks')
<body class="m-0 font-sans antialiased text-slate-600 dark:bg-slate-950 dark:text-white">
  <div id="app" class="flex min-h-screen flex-col bg-slate-50 dark:bg-slate-950">
    @include('site.includes.nav')

    <main class="mx-auto w-full max-w-screen-2xl flex-1 px-6 py-8">
      <div class="grid gap-6 lg:grid-cols-12">
        <aside class="lg:col-span-3">
          @include('dashboards.seller.includes.sidebar')
        </aside>

        <section class="lg:col-span-9">
          @yield('content')
        </section>
      </div>
    </main>

  </div>

  @include('site.includes.footerlinks')
  @stack('scripts')
</body>
</html>
