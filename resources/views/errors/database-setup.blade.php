<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - SFCS</title>
    <style>
        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f4f7fb;
            color: #122033;
        }
        .wrap {
            max-width: 760px;
            margin: 48px auto;
            padding: 0 20px;
        }
        .card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 18px 60px rgba(18, 32, 51, 0.12);
            padding: 28px;
        }
        .eyebrow {
            display: inline-block;
            background: #e7f0ff;
            color: #1d4ed8;
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        h1 {
            margin: 16px 0 10px;
            font-size: 30px;
            line-height: 1.15;
        }
        p {
            line-height: 1.6;
        }
        ol {
            padding-left: 20px;
            line-height: 1.7;
        }
        code, pre {
            font-family: "SFMono-Regular", Consolas, "Liberation Mono", monospace;
        }
        .panel {
            margin-top: 20px;
            padding: 16px;
            background: #f8fafc;
            border: 1px solid #dbe5f0;
            border-radius: 12px;
        }
        .error {
            background: #fff4f4;
            border-color: #fecaca;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="eyebrow">SFCS Setup Check</div>
            <h1>{{ $title }}</h1>
            <p>{{ $summary }}</p>

            <div class="panel">
                <strong>Langkah yang disarankan</strong>
                <ol>
                    @foreach ($steps as $step)
                        <li>{{ $step }}</li>
                    @endforeach
                </ol>
            </div>

            <div class="panel">
                <strong>Perintah yang biasanya dipakai</strong>
                <pre>php artisan optimize:clear
php artisan migrate
php artisan db:seed
php artisan app:doctor</pre>
            </div>

            @if (!empty($errorMessage))
                <div class="panel error">
                    <strong>Detail teknis</strong>
                    <pre>{{ $errorMessage }}</pre>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
