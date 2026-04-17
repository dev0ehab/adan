<?php

namespace App\Services;

use App\Models\User;

/**
 * High-level FCM entry point: send data + notification payloads to stored device tokens.
 */
class PushNotificationService
{
    public function __construct(private readonly FcmService $fcm) {}

    public function isConfigured(): bool
    {
        return $this->fcm->isConfigured();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function toUser(User $user, string $title, string $body, array $data = []): void
    {
        $this->fcm->sendToUser($user, $title, $body, $data);
    }

    /**
     * @param  iterable<int, User>  $users
     * @param  array<string, mixed>  $data
     */
    public function toUsers(iterable $users, string $title, string $body, array $data = []): void
    {
        if (! $this->fcm->isConfigured()) {
            return;
        }

        foreach ($users as $user) {
            if ($user instanceof User) {
                $this->fcm->sendToUser($user, $title, $body, $data);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function toUsersWithRole(string $role, string $title, string $body, array $data = []): void
    {
        if (! $this->fcm->isConfigured()) {
            return;
        }

        foreach (User::query()->where('role', $role)->whereNotNull('fcm_token')->cursor() as $user) {
            if (! $user instanceof User) {
                continue;
            }

            $this->fcm->sendToUser($user, $title, $body, $data);
        }
    }

    /**
     * Send the same notification from the admin panel to a chosen audience.
     *
     * @param  array<string, mixed>  $data  Extra FCM data payload (merged with type)
     * @return int Number of send attempts (users with a stored FCM token)
     */
    public function broadcast(string $title, string $body, string $audience, ?int $userId = null, array $data = []): int
    {
        if (! $this->fcm->isConfigured()) {
            return 0;
        }

        $payload = array_merge(['type' => 'admin_broadcast'], $data);

        if ($audience === 'user') {
            if ($userId === null) {
                return 0;
            }

            $user = User::query()->find($userId);
            if (! $user instanceof User) {
                return 0;
            }

            if (! is_string($user->fcm_token) || $user->fcm_token === '') {
                return 0;
            }

            $this->fcm->sendToUser($user, $title, $body, $payload);

            return 1;
        }

        $query = User::query()->whereNotNull('fcm_token');

        if ($audience === 'doctors') {
            $query->where('role', 'doctor');
        } elseif ($audience === 'customers') {
            $query->where('role', 'customer');
        } elseif ($audience !== 'all') {
            return 0;
        }

        $n = 0;
        foreach ($query->cursor() as $user) {
            if (! $user instanceof User) {
                continue;
            }

            $this->fcm->sendToUser($user, $title, $body, $payload);
            $n++;
        }

        return $n;
    }
}
