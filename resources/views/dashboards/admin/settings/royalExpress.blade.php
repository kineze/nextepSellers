@extends('layouts.admin.app')

@section('content')
    <div class="space-y-6">
        <div class="rounded-3xl p-7 shadow-sm backdrop-blur">
            <p class="text-[0.7rem] font-semibold uppercase tracking-[0.28em] text-blue-600 dark:text-blue-300">System Configuration</p>
            <h1 class="mt-3 text-3xl font-extrabold text-slate-900 dark:text-white">Royal Express</h1>
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
                Manage courier login, business sync, state matching, and city mapping in one place.
            </p>
        </div>

        <curfox-login></curfox-login>

        <city-sync></city-sync>

        <state-matcher></state-matcher>

    </div>
@endsection
