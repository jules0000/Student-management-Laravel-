<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Python DeepFace verification service
    |--------------------------------------------------------------------------
    |
    | When enabled, Laravel POSTs the profile photo + live capture to the local
    | FastAPI app in python-face-service/ (see python-face-service/README.md).
    |
    */
    'enabled' => (bool) env('FACE_VERIFY_ENABLED', false),

    'service_url' => rtrim((string) env('FACE_VERIFY_SERVICE_URL', 'http://127.0.0.1:8765'), '/'),

    'service_secret' => (string) env('FACE_VERIFY_SERVICE_SECRET', ''),

    'timeout' => (int) env('FACE_VERIFY_HTTP_TIMEOUT', 120),

];
