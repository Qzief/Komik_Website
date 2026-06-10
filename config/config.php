<?php

declare(strict_types=1);

return [
    'app' => [
        'name' => env_value('APP_NAME', 'KomikHub'),
        'url' => env_value('APP_URL', 'http://localhost/Komik_Website/public'),
        'session_name' => env_value('SESSION_NAME', 'komikhub_session'),
    ],
    'db' => [
        'host' => env_value('DB_HOST', '127.0.0.1'),
        'port' => (int) env_value('DB_PORT', '3306'),
        'name' => env_value('DB_NAME', 'komik_web'),
        'user' => env_value('DB_USER', 'root'),
        'pass' => env_value('DB_PASS', ''),
        'charset' => 'utf8mb4',
    ],
    'ai' => [
        'nvidia_api_key' => env_value('NVIDIA_API_KEY', ''),
        'nvidia_invoke_url' => env_value('NVIDIA_INVOKE_URL', 'https://integrate.api.nvidia.com/v1/chat/completions'),
        'nvidia_model' => env_value('NVIDIA_MODEL', 'meta/llama-3.1-70b-instruct'),
    ],
];
