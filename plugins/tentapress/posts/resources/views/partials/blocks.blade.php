@php
    $renderer = app(\TentaPress\Blocks\Render\BlockRenderer::class);
    $blocks = is_array($blocks ?? null) ? $blocks : [];
@endphp

@foreach ($blocks as $block)
    {!! $renderer->render(is_array($block) ? $block : []) !!}
@endforeach
