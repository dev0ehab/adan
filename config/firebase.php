<?php

/**
 * Firebase Admin (FCM HTTP v1). Set FIREBASE_CREDENTIALS to a JSON key file path
 * relative to the project root (e.g. storage/app/firebase-service-account.json).
 * Never commit service account keys.
 *
 * Web push (Filament / browser): set FIREBASE_WEB_* and FIREBASE_WEB_VAPID_KEY
 * (Firebase Console → Project settings → Cloud Messaging → Web Push certificates).
 */
return [
    'credentials' => ($path = env('FIREBASE_CREDENTIALS'))
        ? (str_starts_with($path, DIRECTORY_SEPARATOR) || preg_match('#^[A-Za-z]:[\\\\/]#', $path) === 1
            ? $path
            : base_path($path))
        : null,

    'web' => [
        'api_key' => env('FIREBASE_WEB_API_KEY'),
        'auth_domain' => env('FIREBASE_WEB_AUTH_DOMAIN'),
        'project_id' => env('FIREBASE_WEB_PROJECT_ID'),
        'storage_bucket' => env('FIREBASE_WEB_STORAGE_BUCKET'),
        'messaging_sender_id' => env('FIREBASE_WEB_MESSAGING_SENDER_ID'),
        'app_id' => env('FIREBASE_WEB_APP_ID'),
        'measurement_id' => env('FIREBASE_WEB_MEASUREMENT_ID'),
        'vapid_key' => env('FIREBASE_WEB_VAPID_KEY'),
    ],
];
