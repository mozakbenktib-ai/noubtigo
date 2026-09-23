<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.offline_title') ?? 'You are Offline | Noubtigo' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #22c55e, #06b6d4);
            --primary-color: #22c55e;
        }
        body {
            font-family: {{ app()->getLocale() == 'ar' ? "'Cairo', sans-serif" : "'Inter', sans-serif" }};
            background-color: #f8fafc;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .offline-card {
            background: white;
            padding: 3rem;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.08);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        .offline-icon {
            font-size: 5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1.5rem;
            display: inline-block;
        }
        .btn-retry {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 1rem 2.5rem;
            border-radius: 14px;
            font-weight: 600;
            margin-top: 2rem;
            transition: transform 0.2s;
        }
        .btn-retry:hover {
            transform: scale(1.05);
            color: white;
        }
        .brand-logo {
            font-weight: 800;
            font-size: 1.5rem;
            color: #1e293b;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .logo-circle {
            width: 40px;
            height: 40px;
            background: var(--primary-gradient);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
    </style>
</head>
<body>
    <div class="offline-card">
        <div class="brand-logo">
            <div class="logo-circle">
                <i class="bi bi-lightning-charge-fill"></i>
            </div>
            Noubtigo
        </div>
        <div class="offline-icon">
            <i class="bi bi-wifi-off"></i>
        </div>
        <h1 class="fw-bold h2 mb-3">{{ __('ui.offline_message_title') ?? 'Connection Lost' }}</h1>
        <p class="text-secondary fs-5">
            {{ __('ui.offline_message_body') ?? 'You are currently offline. Please check your internet connection to continue using Noubtigo.' }}
        </p>
        <button onclick="window.location.reload()" class="btn btn-retry shadow-lg">
            <i class="bi bi-arrow-clockwise me-2"></i> {{ __('ui.retry') ?? 'Retry Connection' }}
        </button>
    </div>
</body>
</html>
