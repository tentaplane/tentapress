<?php

declare(strict_types=1);

namespace TentaPress\HeadlessApi\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

final class ApiErrorResponder
{
    public function notFound(string $message): JsonResponse
    {
        return Response::json([
            'error' => [
                'code' => 'not_found',
                'message' => $message,
            ],
        ], 404);
    }
}
