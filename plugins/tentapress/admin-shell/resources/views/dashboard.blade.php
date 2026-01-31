@extends('tentapress-admin::layouts.shell')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-5">
        <h1 class="text-2xl font-semibold">Dashboard</h1>
        <p class="mt-1 text-sm text-black/60">Welcome to TentaPress admin.</p>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="rounded border border-black/10 bg-white p-4 shadow-sm lg:col-span-2">
            <h2 class="text-base font-semibold">Getting started</h2>
            <p class="mt-2 text-sm text-black/70">
                This is the admin shell. Everything you see here is intended to be driven by packages/plugins.
            </p>

            <div class="mt-4 flex flex-wrap gap-2"></div>
        </div>

        <div class="rounded border border-black/10 bg-white p-4 shadow-sm">
            <h2 class="text-base font-semibold">System</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-black/60">PHP</dt>
                    <dd class="font-mono text-black/80">{{ PHP_VERSION }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-black/60">App Env</dt>
                    <dd class="font-mono text-black/80">{{ app()->environment() }}</dd>
                </div>
            </dl>
        </div>
    </div>
@endsection
