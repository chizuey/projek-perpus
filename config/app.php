<?php

function app_base_url(): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    $hostingConfig = __DIR__ . '/hosting.php';
    if (file_exists($hostingConfig)) {
        $config = require $hostingConfig;
        if (!empty($config['base_url'])) {
            return rtrim($config['base_url'], '/');
        }
    }

    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $parts = explode('/', trim($scriptDir, '/'));

    while (!empty($parts) && in_array(end($parts), ['auth', 'user', 'admin', 'actions', 'reservasi', 'buku', 'kategori', 'peminjaman'], true)) {
        array_pop($parts);
    }

    $basePath = empty($parts) ? '' : '/' . implode('/', $parts);
    return $scheme . '://' . $host . $basePath;
}

function app_url(string $path = ''): string
{
    return rtrim(app_base_url(), '/') . '/' . ltrim($path, '/');
}

function google_redirect_uri(): string
{
    return app_url('auth/proses-login.php');
}

function google_client_id(): string
{
    $hostingConfig = __DIR__ . '/hosting.php';
    if (file_exists($hostingConfig)) {
        $config = require $hostingConfig;
        if (!empty($config['google_client_id'])) {
            return $config['google_client_id'];
        }
    }

    return '118639840694-uuda9i1n1bc3c216tqufrjirucg3chdv.apps.googleusercontent.com';
}

function google_client_secret(): string
{
    $hostingConfig = __DIR__ . '/hosting.php';
    if (file_exists($hostingConfig)) {
        $config = require $hostingConfig;
        if (!empty($config['google_client_secret'])) {
            return $config['google_client_secret'];
        }
    }

    return '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';
}
