<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.reset_password_btn') }} | NoubtiGO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    @if(app()->getLocale() == 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    @endif
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
    <main class="container" style="max-width: 460px;">
        <section class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">
                <h1 class="h3 fw-bold mb-2">{{ __('ui.set_new_password') }}</h1>
                <p class="text-muted mb-4">{{ __('ui.choose_strong_password') }}</p>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('password.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="mb-3">
                        <label for="email" class="form-label">{{ __('ui.email_address') }}</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $email) }}" required autocomplete="email">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">{{ __('ui.new_password') }}</label>
                        <input type="password" id="password" name="password" class="form-control" required minlength="8" autocomplete="new-password">
                    </div>
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">{{ __('ui.confirm_new_password') }}</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required minlength="8" autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-success w-100">{{ __('ui.reset_password_btn') }}</button>
                </form>
                <a href="{{ route('login') }}" class="d-block text-center mt-3 text-decoration-none">{{ __('ui.back_to_sign_in') }}</a>
            </div>
        </section>
    </main>
</body>
</html>
