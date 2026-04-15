<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta http-equiv="refresh" content="60">
    <title>Under Maintenance - Meeting Room LCD</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style>
        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: radial-gradient(circle at top right, #2f3c7e 0%, #10142c 60%, #080b1a 100%);
            color: #ffffff;
        }

        .maintenance-wrapper {
            min-height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .maintenance-card {
            width: 100%;
            max-width: 900px;
            border-radius: 16px;
            padding: 40px 30px;
            text-align: center;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.35);
        }

        .maintenance-icon {
            font-size: 84px;
            color: #ffd24d;
            margin-bottom: 20px;
        }

        .maintenance-title {
            font-size: 54px;
            font-weight: 800;
            margin: 0 0 12px 0;
            letter-spacing: 1px;
        }

        .maintenance-subtitle {
            font-size: 24px;
            margin: 0 0 28px 0;
            opacity: 0.95;
        }

        .maintenance-meta {
            font-size: 18px;
            opacity: 0.9;
            margin-bottom: 24px;
        }

        .maintenance-note {
            font-size: 16px;
            opacity: 0.8;
        }

        .loader-dots {
            margin-top: 25px;
            letter-spacing: 6px;
            font-size: 24px;
            animation: blink 1.5s infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 0.2; }
            50% { opacity: 1; }
        }

        @media (max-width: 768px) {
            .maintenance-title {
                font-size: 36px;
            }

            .maintenance-subtitle {
                font-size: 18px;
            }

            .maintenance-meta {
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="maintenance-wrapper">
        <div class="maintenance-card">
            <div class="maintenance-icon">
                <i class="fa fa-wrench"></i>
            </div>

            <h1 class="maintenance-title">UNDER MAINTENANCE</h1>
            <p class="maintenance-subtitle">
                {{ $dashboardName ?? 'Meeting Room LCD Dashboard' }} sedang dalam pemulihan.
            </p>

            <div class="maintenance-meta">
                Waktu deteksi error: {{ isset($errorAt) ? $errorAt->format('d M Y H:i:s') : now()->format('d M Y H:i:s') }}
            </div>

            <p class="maintenance-note">
                Halaman akan mencoba memuat ulang otomatis setiap 60 detik.
            </p>

            <div class="loader-dots">. . .</div>
        </div>
    </div>
</body>
</html>
