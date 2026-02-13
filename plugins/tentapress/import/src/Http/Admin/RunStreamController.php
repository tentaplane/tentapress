<?php

declare(strict_types=1);

namespace TentaPress\Import\Http\Admin;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use TentaPress\Import\Services\Importer;

final class RunStreamController
{
    public function __invoke(Request $request, Importer $importer): StreamedResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'pages_mode' => ['required', 'in:create_only'],
            'settings_mode' => ['required', 'in:merge,overwrite'],
            'include_posts' => ['nullable', 'boolean'],
            'include_media' => ['nullable', 'boolean'],
            'include_seo' => ['nullable', 'boolean'],
        ]);

        return response()->stream(function () use ($importer, $data, $request): void {
            $send = function (array $payload): void {
                $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
                if (!is_string($json)) {
                    return;
                }

                echo 'data: ' . $json . "\n\n";
                @ob_flush();
                flush();
            };

            $send([
                'event' => 'started',
                'message' => 'Starting import...',
            ]);

            try {
                $result = $importer->runImport((string) $data['token'], [
                    'pages_mode' => (string) $data['pages_mode'],
                    'settings_mode' => (string) $data['settings_mode'],
                    'include_posts' => (bool) ($data['include_posts'] ?? false),
                    'include_media' => (bool) ($data['include_media'] ?? false),
                    'include_seo' => (bool) ($data['include_seo'] ?? false),
                    'actor_user_id' => (int) ($request->user()?->id ?? 0),
                    'progress' => function (array $event) use ($send): void {
                        $send(array_merge([
                            'event' => 'progress',
                        ], $event));
                    },
                ]);

                $send([
                    'event' => 'done',
                    'message' => (string) ($result['message'] ?? 'Import completed.'),
                ]);
            } catch (\Throwable $e) {
                $send([
                    'event' => 'error',
                    'message' => $e->getMessage(),
                ]);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
