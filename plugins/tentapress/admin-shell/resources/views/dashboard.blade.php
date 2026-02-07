@extends('tentapress-admin::layouts.shell')

@section('title', 'Dashboard')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Dashboard</h1>
            <p class="tp-description">View a snapshot of your site and jump into common admin tasks.</p>
        </div>
    </div>

    @if (count($widgets) > 0)
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($widgets as $widget)
                <div
                    class="@if($widget->colspan() === 2) md:col-span-2 @elseif($widget->colspan() === 3) md:col-span-2 lg:col-span-3 @endif">
                    {!! $widget->render() !!}
                </div>
            @endforeach
        </div>
    @else
        <div class="tp-metabox">
            <div class="tp-metabox__body py-8 text-center">
                <p class="text-black/60">No dashboard cards are available yet.</p>
            </div>
        </div>
    @endif
@endsection
