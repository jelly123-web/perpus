<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait HandlesAsyncRequests
{
    protected function successResponse(Request $request, string $message, array $data = []): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $message,
                ...$data,
            ]);
        }

        return back()->with('status', $message);
    }

    protected function errorResponse(Request $request, string $message, int $status = 422, string $key = 'error'): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => $message,
                'errors' => [$key => [$message]],
            ], $status);
        }

        return back()->withErrors([$key => $message]);
    }
}
