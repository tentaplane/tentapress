@php($html = app(\TentaPress\Marketing\Services\MarketingManager::class)->renderPlacement('body-close'))

@if (trim($html) !== '')
{!! $html !!}
@endif
