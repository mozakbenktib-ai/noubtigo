<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.login_to_portal') }} - Noubtigo</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    @if(app()->getLocale() == 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    @endif

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #22c55e, #06b6d4);
            --text-main: #1e293b;
        }

        body {
            font-family: {{ app()->getLocale() == 'ar' ? "'Cairo', sans-serif" : "'Outfit', sans-serif" }};
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 420px;
            overflow: hidden;
        }

        .auth-header {
            background: var(--primary-gradient);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .auth-body {
            padding: 30px;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            font-weight: 500;
        }

        .form-control:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.1);
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(34, 197, 94, 0.2);
        }

        .auth-footer {
            text-align: center;
            padding-bottom: 30px;
        }
    </style>
</head>

<body>

    <div class="auth-card">
        <div class="auth-header">
            <div class="mb-3">
                <img src="{{ asset('images/ng_logo.png') }}" alt="Logo" height="50" style="filter: brightness(0) invert(1);">
            </div>
            <h4 class="fw-bold mb-1">{{ __('ui.login_to_portal') }}</h4>
            <p class="mb-0 opacity-75 small">{{ __('ui.welcome_back') }}</p>
        </div>

        <div class="auth-body">
            @if($errors->any())
                <div class="alert alert-danger border-0 rounded-3 small">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('customer.login') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold">{{ __('ui.email') }}</label>
                    <input type="email" name="email" class="form-control" placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold">{{ __('ui.password') }}</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <div class="mb-3 form-check small">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">{{ __('ui.remember_me') }}</label>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    {{ __('ui.login') }}
                </button>
            </form>
        </div>

        <div class="auth-footer">
            <p class="small text-muted mb-0">
                {{ __('ui.dont_have_account') }} 
                <a href="{{ route('customer.register') }}" class="text-success fw-bold text-decoration-none">{{ __('ui.signup') }}</a>
            </p>
        </div>
    </div>

</body>

</html>
