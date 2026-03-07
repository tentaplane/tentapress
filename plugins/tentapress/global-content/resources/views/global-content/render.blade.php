@php
    $renderer = app()->bound('tp.blocks.render') ? app('tp.blocks.render') : null;
@endphp

<div class="tp-global-content-render" data-tp-global-content-id="{{ $globalContent->id }}">
    @if (isset($blocksHtml) && is_string($blocksHtml))
        {!! $blocksHtml !!}
    @elseif (is_callable($renderer))
        {!! $renderer($blocks) !!}
    @endif
</div>
