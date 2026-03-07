@php
    $resolver = app(\TentaPress\GlobalContent\Services\GlobalContentReferenceResolver::class);
    $globalContentId = (int) ($props['global_content_id'] ?? 0);
@endphp

{!! $resolver->renderPublishedById($globalContentId) !!}
