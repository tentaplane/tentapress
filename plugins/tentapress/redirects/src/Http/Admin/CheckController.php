<?php

declare(strict_types=1);

namespace TentaPress\Redirects\Http\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use TentaPress\Redirects\Services\RedirectManager;

final class CheckController
{
    public function __invoke(Request $request, RedirectManager $manager): JsonResponse
    {
        $data = $request->validate([
            'source_path' => ['required', 'string'],
            'target_path' => ['required', 'string'],
            'status_code' => ['required', 'integer'],
            'ignore_id' => ['nullable', 'integer'],
        ]);

        try {
            $normalized = $manager->validateAndNormalize([
                'source_path' => (string) $data['source_path'],
                'target_path' => (string) $data['target_path'],
                'status_code' => (int) $data['status_code'],
                'is_enabled' => true,
            ], isset($data['ignore_id']) ? (int) $data['ignore_id'] : null);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'normalized' => $normalized,
        ]);
    }
}
