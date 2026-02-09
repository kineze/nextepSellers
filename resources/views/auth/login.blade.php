@extends('auth.layouts.app')

@section('content')

<div class="relative min-h-screen w-full overflow-hidden bg-slate-950 text-white">
    <video autoplay muted loop playsinline class="absolute inset-0 h-full w-full object-cover opacity-40">
        <source src="{{ asset('assets/videos/login-bg.mp4') }}" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <div class="absolute inset-0 bg-gradient-to-br from-slate-950/90 via-slate-900/70 to-black/90"></div>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(148,163,184,0.2),_transparent_45%)]"></div>

    <div class="relative z-10 flex min-h-screen items-center justify-center px-6 py-10">
        <div class="w-full max-w-5xl overflow-hidden rounded-3xl border border-white/10 bg-white/10 shadow-2xl backdrop-blur-xl">
            <div class="grid gap-0 lg:grid-cols-2">
                <div class="flex flex-col justify-between gap-10 px-8 py-10 sm:px-12">
                    <div class="flex items-center gap-3">
                        <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                            <img src="{{ asset('assets/img/nextep-logo.webp') }}" alt="Nextep" class="h-10 w-auto dark:hidden">
                            <img src="{{ asset('assets/img/nextep-logo-dark.webp') }}" alt="Nextep" class="h-10 w-auto hidden dark:block">
                        </a>
                    </div>

                    <div>
                        <p class="text-sm uppercase tracking-[0.3em] text-white/60">Welcome Back</p>
                        <h1 class="mt-3 text-3xl font-semibold leading-tight text-white sm:text-4xl">
                            Sign in to manage your workspace.
                        </h1>
                        <p class="mt-4 text-sm text-white/70">
                            Secure access to orders, inventory, and team updates in one place.
                        </p>
                    </div>

                    <div class="flex items-center gap-3 text-xs text-white/60">
                        <span class="inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                        Encrypted sessions and account protection enabled.
                    </div>
                </div>

                <div class="bg-white px-8 py-10 text-slate-900 sm:px-12">
                    <div>
                        <h2 class="text-2xl font-semibold">Log in</h2>
                        <p class="mt-2 text-sm text-slate-500">Use your email and password to continue.</p>
                    </div>

                    <form method="POST" action="{{ route('login') }}" class="mt-8 flex w-full flex-col gap-6">
                        @csrf
                        @error('email')
                        <div class="flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="font-medium">{{ $message }}</p>
                        </div>
                        @enderror

                        <div>
                            <label for="email" class="text-sm font-medium text-slate-700">Email</label>
                            <div class="relative mt-2">
                                <input
                                    type="text"
                                    name="email"
                                    id="email"
                                    value="{{ old('email') }}"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10"
                                    placeholder="name@company.com"
                                    required
                                    autofocus
                                />
                                <i class="fa-solid fa-envelope pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            </div>
                            @error('email')
                            <p class="mt-2 text-xs font-medium text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="text-sm font-medium text-slate-700">Password</label>
                            <div class="relative mt-2">
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10"
                                    placeholder="Enter your password"
                                    required
                                />
                                <i id="togglePassword" class="fa-solid fa-eye absolute right-4 top-1/2 -translate-y-1/2 cursor-pointer text-slate-400 hover:text-slate-700"></i>
                            </div>
                            @error('password')
                            <p class="mt-2 text-xs font-medium text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between text-sm">
                            <label for="remember_me" class="flex items-center gap-2 text-slate-600">
                                <x-checkbox id="remember_me" name="remember" />
                                <span>{{ __('Remember me') }}</span>
                            </label>
                            {{-- @if (Route::has('password.request'))
                                <a class="font-medium text-slate-600 hover:text-slate-900" href="{{ route('password.request') }}">
                                    {{ __('Forgot your password?') }}
                                </a>
                            @endif --}}
                        </div>

                        <button class="w-full rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold uppercase tracking-wide text-white shadow-lg transition hover:bg-black">
                            {{ __('Log in') }}
                        </button>

                        <p class="text-xs text-slate-500">
                            Need help? Contact your administrator for access.
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
