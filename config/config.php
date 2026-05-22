<?php

return [
    'app_name' => 'Centro de Formação Operacional',
    'base_url' => getenv('APP_URL') ?: 'http://localhost:8000',
    'storage_path' => __DIR__ . '/../storage',
    'upload_videos_path' => __DIR__ . '/../public/assets/uploads/videos',
    'upload_thumbnails_path' => __DIR__ . '/../public/assets/uploads/thumbnails',
    'max_video_size' => 300 * 1024 * 1024,
    'allowed_video_types' => ['mp4', 'webm', 'mov'],
    'allowed_image_types' => ['jpg', 'jpeg', 'png', 'webp'],
    'default_language' => 'pt',
    'timezone' => 'Europe/Lisbon',
    'session_name' => 'cfo_session',
];
