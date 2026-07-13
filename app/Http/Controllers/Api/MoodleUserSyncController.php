<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MoodleSyncUserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MoodleUserSyncController extends Controller
{
    /**
     * List users for Moodle account synchronization.
     *
     * Returns a paginated, read-only snapshot of Pasca users. The password field
     * contains the existing bcrypt hash and must never be logged or shown in a UI.
     *
     * @group Moodle Integration
     * @authenticated
     *
     * @queryParam per_page integer Number of users per page, from 1 to 100. Example: 100
     * @queryParam updated_after string Return users updated on or after this ISO-8601 date and time. Example: 2026-07-01T00:00:00+07:00
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'updated_after' => ['sometimes', 'date'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 100);
        $updatedAfter = $validated['updated_after'] ?? null;

        $query = User::query()
            ->select([
                'id',
                'name',
                'email',
                'email_verified_at',
                'password',
                'created_at',
                'updated_at',
            ])
            ->when(
                $updatedAfter,
                fn ($builder, string $date) => $builder->where('updated_at', '>=', $date),
            )
            ->orderBy('id');

        $users = $query->paginate($perPage)->appends($request->query());

        $data = collect($users->items())
            ->map(fn (User $user): array => (new MoodleSyncUserResource($user))->resolve($request))
            ->values();

        return response()
            ->json([
                'data' => $data,
                'meta' => [
                    'description' => 'Paginated Pasca users for Moodle account synchronization.',
                    'current_page' => $users->currentPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'last_page' => $users->lastPage(),
                    'updated_after' => $updatedAfter,
                ],
                'links' => [
                    'first' => $users->url(1),
                    'last' => $users->url($users->lastPage()),
                    'previous' => $users->previousPageUrl(),
                    'next' => $users->nextPageUrl(),
                ],
            ])
            ->withHeaders([
                'Cache-Control' => 'no-store, private',
                'Pragma' => 'no-cache',
                'X-Content-Type-Options' => 'nosniff',
            ]);
    }
}
