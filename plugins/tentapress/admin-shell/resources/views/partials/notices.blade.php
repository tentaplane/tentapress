@php
    $success = session('tp_notice_success');
    $error = session('tp_notice_error');
    $warning = session('tp_notice_warning');
    $info = session('tp_notice_info');

    $toasts = [];

    if ($success) {
        $toasts[] = ['type' => 'success', 'message' => $success];
    }

    if ($error) {
        $toasts[] = ['type' => 'error', 'message' => $error];
    }

    if ($warning) {
        $toasts[] = ['type' => 'warning', 'message' => $warning];
    }

    if ($info) {
        $toasts[] = ['type' => 'info', 'message' => $info];
    }

    if (isset($errors) && $errors->any()) {
        $firstError = (string) $errors->first();
        $count = (int) $errors->count();
        $plural = $count === 1 ? '' : 's';
        $message = $count === 1
            ? $firstError
            : "We found {$count} problem{$plural}. First: {$firstError}";

        $toasts[] = ['type' => 'error', 'message' => $message];
    }
@endphp

@if ($toasts !== [])
    <div id="tp-toast-root" data-toasts='@json($toasts)'></div>
@endif
