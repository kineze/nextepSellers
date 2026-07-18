@extends('auth.layouts.app')

@section('content')
<div class="relative min-h-screen w-full overflow-hidden bg-slate-950 text-white">
    <video autoplay muted loop playsinline class="absolute inset-0 h-full w-full object-cover opacity-30">
        <source src="{{ asset('assets/videos/login-bg.mp4') }}" type="video/mp4">
    </video>
    <div class="absolute inset-0 bg-gradient-to-br from-slate-950/95 via-slate-900/80 to-black/95"></div>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(148,163,184,0.2),_transparent_45%)]"></div>

    <div class="relative z-10 flex min-h-screen items-center justify-center px-6 py-10">
        <div class="w-full max-w-md rounded-3xl border border-white/10 bg-white p-8 text-slate-900 shadow-2xl sm:p-10">
            <a href="{{ url('/') }}" class="inline-flex items-center">
                <img src="{{ asset('assets/img/nextep-logo.webp') }}" alt="Nextep" class="h-10 w-auto">
            </a>

            <div class="mt-8">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Account recovery</p>
                <h1 class="mt-2 text-2xl font-semibold text-slate-950">Forgot your password?</h1>
                <p class="mt-3 text-sm leading-6 text-slate-500">Enter your account email and we’ll send you a secure link to choose a new password.</p>
            </div>

            @session('status')
                <div class="mt-6 flex gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">
                    <i class="fas fa-circle-check mt-0.5"></i>
                    <span>{{ $value }}</span>
                </div>
            @endsession

            <form method="POST" action="{{ route('password.email') }}" class="mt-7 space-y-5">
                @csrf

                <div>
                    <label for="email" class="text-sm font-medium text-slate-700">Email address</label>
                    <div class="relative mt-2">
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="name@company.com"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 pr-11 text-sm text-slate-900 shadow-sm outline-none transition focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10"
                        >
                        <i class="fas fa-envelope pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    </div>
                    @error('email')
                        <p class="mt-2 text-xs font-medium text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full rounded-xl bg-slate-950 px-4 py-3 text-sm font-semibold uppercase tracking-wide text-white shadow-lg transition hover:bg-black">
                    Email reset link
                </button>
            </form>

            <a href="{{ route('login') }}" class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-slate-600 transition hover:text-slate-950">
                <i class="fas fa-arrow-left text-xs"></i> Back to login
            </a>
        </div>
    </div>
</div>
@endsection
