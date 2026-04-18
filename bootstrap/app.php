<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'active' => \App\Http\Middleware\CheckActiveUser::class,
            'request.context' => \App\Http\Middleware\RequestLogContext::class,
            'force.password.change' => \App\Http\Middleware\ForcePasswordChange::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\RequestLogContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (QueryException $e, Request $request) {
            $message = $e->getMessage();
            $normalized = strtolower($message);

            $isConnectionIssue = str_contains($normalized, 'access denied for user')
                || str_contains($normalized, 'connection refused')
                || str_contains($normalized, 'can\'t connect')
                || str_contains($normalized, 'unknown error while connecting')
                || str_contains($normalized, 'operation not permitted');

            $isSchemaIssue = str_contains($normalized, 'unknown column')
                || str_contains($normalized, 'base table or view not found')
                || str_contains($normalized, 'table') && str_contains($normalized, 'doesn\'t exist');

            if (!$isConnectionIssue && !$isSchemaIssue) {
                return null;
            }

            $status = $isConnectionIssue ? 503 : 500;
            $title = $isConnectionIssue ? 'Database Belum Siap' : 'Database Perlu Migrasi';
            $summary = $isConnectionIssue
                ? 'Laravel berhasil jalan, tetapi koneksi ke database MySQL/MariaDB masih ditolak atau belum bisa dibuka.'
                : 'Aplikasi menemukan schema database yang belum lengkap, jadi beberapa fitur belum bisa dipakai dengan aman.';

            $steps = $isConnectionIssue
                ? [
                    'Pastikan service MySQL/MariaDB sedang aktif.',
                    'Gunakan user database aplikasi di file .env, jangan mengandalkan root Linux/auth_socket.',
                    'Sesuaikan DB_HOST, DB_DATABASE, DB_USERNAME, dan DB_PASSWORD di .env.',
                    'Setelah diubah, jalankan php artisan optimize:clear.',
                    'Lalu cek ulang dengan php artisan app:doctor.',
                ]
                : [
                    'Jalankan php artisan migrate untuk melengkapi schema.',
                    'Jika ini instalasi baru, lanjutkan dengan php artisan db:seed.',
                    'Cek ulang kesiapan fitur lewat php artisan app:doctor.',
                ];

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $title,
                    'summary' => $summary,
                    'steps' => $steps,
                    'error' => app()->hasDebugModeEnabled() ? $message : null,
                ], $status);
            }

            return response()->view('errors.database-setup', [
                'title' => $title,
                'summary' => $summary,
                'steps' => $steps,
                'errorMessage' => app()->hasDebugModeEnabled() ? $message : null,
            ], $status);
        });
    })->create();
