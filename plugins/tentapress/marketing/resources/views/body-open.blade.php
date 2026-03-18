@php($html = app(\TentaPress\Marketing\Services\MarketingManager::class)->renderPlacement('body-open'))

@if (trim($html) !== '')
{!! $html !!}
@endif
