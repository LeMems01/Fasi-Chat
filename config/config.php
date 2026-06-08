<?php
/**
 * FasiChat Classroom — Configuration générale
 */
define('DB_HOST',    'localhost');
define('DB_PORT',    '3306');
define('DB_NAME',    'fasichat');
define('DB_USER',    'root');
define('DB_PASS',    ''); 
define('DB_CHARSET', 'utf8mb4');

define('UPLOAD_MAX_SIZE', 20 * 1024 * 1024);
define('UPLOAD_DIR',  dirname(__DIR__) . '/uploads/');
define('TEMP_DIR',    dirname(__DIR__) . '/temp/');

define('IMAGE_QUALITY',    75);
define('IMAGE_MAX_WIDTH',  1920);
define('IMAGE_MAX_HEIGHT', 1080);

define('APP_NAME',        'FasiChat Classroom');
define('APP_VERSION',     '1.0.0');
define('CSRF_TOKEN_NAME', '_fasichat_token');

define('ALLOWED_IMAGE_TYPES', ['image/jpeg','image/png','image/gif','image/webp']);
define('ALLOWED_VIDEO_TYPES', ['video/mp4','video/webm','video/ogg','video/avi','video/quicktime']);
define('ALLOWED_AUDIO_TYPES', ['audio/mpeg','audio/ogg','audio/wav','audio/mp4','audio/webm']);
define('ALLOWED_DOCUMENT_TYPES', [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'text/plain',
]);

spl_autoload_register(function (string $className): void {
    $paths = [
        dirname(__DIR__) . '/classes/'     . $className . '.php',
        dirname(__DIR__) . '/controllers/' . $className . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) { require_once $path; return; }
    }
});
