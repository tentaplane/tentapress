@php($html = app(\TentaPress\Marketing\Services\MarketingManager::class)->renderPlacement('head'))

@if (trim($html) !== '')
{!! $html !!}
@endif
