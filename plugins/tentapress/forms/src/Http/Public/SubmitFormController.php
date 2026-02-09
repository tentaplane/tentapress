<?php

declare(strict_types=1);

namespace TentaPress\Forms\Http\Public;

use Illuminate\Http\RedirectResponse;
use TentaPress\Forms\Http\Requests\SubmitFormRequest;
use TentaPress\Forms\Services\FormSubmissionService;

final readonly class SubmitFormController
{
    public function __construct(
        private FormSubmissionService $submissions,
    ) {
    }

    public function __invoke(SubmitFormRequest $request, string $formKey): RedirectResponse
    {
        $statusKey = $this->statusSessionKey($formKey);
        $outcome = $this->submissions->submit($request->all(), $formKey);

        if (! $outcome->ok) {
            return $this->redirectToReturnUrl($request)
                ->withInput($request->except(['_tp_payload', '_tp_hp', '_tp_started_at']))
                ->with($statusKey, [
                    'type' => 'error',
                    'message' => $outcome->message,
                ]);
        }

        if (is_string($outcome->redirectUrl) && trim($outcome->redirectUrl) !== '') {
            return redirect()->to($outcome->redirectUrl)
                ->with($statusKey, [
                    'type' => 'success',
                    'message' => $outcome->message,
                ]);
        }

        return $this->redirectToReturnUrl($request)
            ->with($statusKey, [
                'type' => 'success',
                'message' => $outcome->message,
            ]);
    }

    private function redirectToReturnUrl(SubmitFormRequest $request): RedirectResponse
    {
        $returnUrl = trim((string) $request->input('_tp_return_url', ''));

        if ($returnUrl !== '') {
            if (str_starts_with($returnUrl, '/')) {
                return redirect()->to($returnUrl);
            }

            $targetHost = parse_url($returnUrl, PHP_URL_HOST);
            $appHost = parse_url(url('/'), PHP_URL_HOST);

            if (is_string($targetHost) && is_string($appHost) && strcasecmp($targetHost, $appHost) === 0) {
                return redirect()->to($returnUrl);
            }
        }

        return redirect()->back();
    }

    private function statusSessionKey(string $formKey): string
    {
        $normalized = strtolower(trim($formKey));
        $normalized = (string) preg_replace('/[^a-z0-9_-]/', '', $normalized);

        if ($normalized === '') {
            $normalized = 'form';
        }

        return 'tp_forms.status.'.$normalized;
    }
}
