<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\PushMessageNotification;

/**
 * High-level FCM + in-app (database) notifications.
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
        $this->sendToUserWithStore($user, $title, $body, $data);
    }

    /**
     * @param  iterable<int, User>  $users
     * @param  array<string, mixed>  $data
     */
    public function toUsers(iterable $users, string $title, string $body, array $data = []): void
    {
        foreach ($users as $user) {
            if ($user instanceof User) {
                $this->sendToUserWithStore($user, $title, $body, $data);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function toUsersWithRole(string $role, string $title, string $body, array $data = []): void
    {
        foreach (User::query()->where('role', $role)->cursor() as $user) {
            if (! $user instanceof User) {
                continue;
            }

            $this->sendToUserWithStore($user, $title, $body, $data);
        }
    }

    /**
     * Send the same notification from the admin panel to a chosen audience.
     *
     * @param  array<string, mixed>  $data  Extra payload (merged with type for FCM + DB)
     * @return int Number of users notified (database); FCM only reaches users with a token when configured
     */
    public function broadcast(string $title, string $body, string $audience, ?int $userId = null, array $data = []): int
    {
        $payload = array_merge(['type' => 'admin_broadcast'], $data);

        if ($audience === 'user') {
            if ($userId === null) {
                return 0;
            }

            $user = User::query()->find($userId);
            if (! $user instanceof User) {
                return 0;
            }

            $this->sendToUserWithStore($user, $title, $body, $payload);

            return 1;
        }

        $query = User::query();

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

            $this->sendToUserWithStore($user, $title, $body, $payload);
            $n++;
        }

        return $n;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function sendToUserWithStore(User $user, string $title, string $body, array $data = []): void
    {
        $user->notify(new PushMessageNotification($title, $body, $data));

        if ($this->fcm->isConfigured()) {
            $this->fcm->sendToUser($user, $title, $body, $data);
        }
    }
}
