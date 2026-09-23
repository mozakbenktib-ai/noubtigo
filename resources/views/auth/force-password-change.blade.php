<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.change_your_password') }} | NoubtiGO</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    @if(app()->getLocale() == 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    @endif
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: '{{ app()->getLocale() == "ar" ? "Cairo" : "Inter" }}', sans-serif;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5 col-xl-4">
                
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-white rounded-circle shadow-sm mb-3" style="width: 70px; height: 70px;">
                        <i class="bi bi-shield-lock-fill text-primary" style="font-size: 2rem;"></i>
                    </div>
                    <h2 class="fw-bold text-dark mb-1">{{ __('ui.update_password') }}</h2>
                    <p class="text-secondary small">{{ __('ui.update_password_desc') }}</p>
                </div>

                <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white">
                    <div class="card-body p-4 p-md-5">
                        <form method="POST" action="{{ route('password.change.update') }}">
                            @csrf

                            <div class="form-floating mb-3">
                                <input type="password" name="password" class="form-control bg-light border-0 rounded-3 px-3 shadow-none @error('password') is-invalid @enderror" id="password" placeholder="{{ __('ui.new_password') }}" required minlength="6">
                                <label for="password" class="text-secondary">{{ __('ui.new_password') }}</label>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-floating mb-4">
                                <input type="password" name="password_confirmation" class="form-control bg-light border-0 rounded-3 px-3 shadow-none" id="password_confirmation" placeholder="{{ __('ui.confirm_new_password') }}" required minlength="6">
                                <label for="password_confirmation" class="text-secondary">{{ __('ui.confirm_new_password') }}</label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-sm mb-3">
                                <i class="bi bi-check-circle me-1"></i> {{ __('ui.update_password_continue') }}
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-link text-secondary text-decoration-none small fw-medium">
                                    <i class="bi bi-box-arrow-left me-1"></i> {{ __('ui.logout_instead') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>
</html>
