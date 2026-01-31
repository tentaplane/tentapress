@php
    $success = session('tp_notice_success');
    $error = session('tp_notice_error');
    $warning = session('tp_notice_warning');
    $info = session('tp_notice_info');
@endphp

@if ($success)
    <div class="tp-notice-success">{{ $success }}</div>
@endif

@if ($error)
    <div class="tp-notice-error">{{ $error }}</div>
@endif

@if ($warning)
    <div class="tp-notice-warning">{{ $warning }}</div>
@endif

@if ($info)
    <div class="tp-notice-info">{{ $info }}</div>
@endif

@if (isset($errors) && $errors->any())
    <div class="tp-notice-error">
        <div class="mb-1 font-semibold">We found some problems, correct these to continue:</div>
        <ul class="list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
