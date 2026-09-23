<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authorize Display | Noubtigo</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Cairo:wght@400;700;900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary-color: #22c55e;
            --secondary-color: #06b6d4;
            --primary-gradient: linear-gradient(135deg, #22c55e, #06b6d4);
            --dark-bg: #0a0a0c;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: var(--dark-bg);
            color: #fff;
            font-family: 'Inter', sans-serif;
            height: 100vh;
            width: 100vw;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .auth-card {
            max-width: 600px;
            width: 90%;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 4rem;
            padding: 4rem;
            text-align: center;
            box-shadow: 0 40px 100px rgba(0,0,0,0.6);
            position: relative;
        }

        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: var(--primary-gradient);
            border-radius: 4rem 4rem 0 0;
        }

        .pin-display {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin: 3rem 0;
        }

        .pin-digit {
            width: 70px;
            height: 90px;
            background: rgba(255,255,255,0.05);
            border: 2px solid rgba(255,255,255,0.1);
            border-radius: 1.5rem;
            font-size: 3.5rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .pin-digit.active {
            border-color: var(--primary-color);
            box-shadow: 0 0 20px rgba(34, 197, 94, 0.2);
        }

        .numpad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .num-btn {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 1.5rem;
            padding: 1.5rem;
            font-size: 2rem;
            font-weight: 700;
            color: white;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .num-btn:hover {
            background: rgba(255,255,255,0.1);
            transform: translateY(-3px);
        }

        .num-btn:active {
            background: var(--primary-gradient);
            transform: scale(0.95);
        }

        .error-msg {
            color: #ef4444;
            font-weight: 600;
            margin-top: 1rem;
            display: none;
        }

        .verifying-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(10, 10, 12, 0.9);
            border-radius: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 10;
            display: none;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(255,255,255,0.1);
            border-top: 5px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 1.5rem;
        }

        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>

    <div class="auth-card">
        <div class="verifying-overlay" id="loader">
            <div class="spinner"></div>
            <h3>Authorizing...</h3>
        </div>

        <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem;">Authorize Display</h1>
        <p style="opacity: 0.7; font-size: 1.2rem;">Enter the Display PIN from your dashboard</p>

        <div class="pin-display" id="pin-container">
            <div class="pin-digit" data-index="0"></div>
            <div class="pin-digit" data-index="1"></div>
            <div class="pin-digit" data-index="2"></div>
            <div class="pin-digit" data-index="3"></div>
            <div class="pin-digit" data-index="4"></div>
            <div class="pin-digit" data-index="5"></div>
        </div>

        <div class="error-msg" id="error-box">Invalid PIN. Please check your dashboard.</div>

        <div class="numpad">
            @for($i = 1; $i <= 9; $i++)
                <button class="num-btn" onclick="addDigit('{{ $i }}')">{{ $i }}</button>
            @endfor
            <button class="num-btn text-danger" onclick="backspace()"><i class="bi bi-backspace"></i></button>
            <button class="num-btn" onclick="addDigit('0')">0</button>
            <button class="num-btn text-info" onclick="validate()"><i class="bi bi-check-circle-fill"></i></button>
        </div>
    </div>

    <script>
        let currentPin = "";
        const digits = document.querySelectorAll('.pin-digit');

        function addDigit(num) {
            if (currentPin.length < 6) {
                currentPin += num;
                updateDisplay();
                if (currentPin.length === 6) {
                    validate();
                }
            }
        }

        function backspace() {
            currentPin = currentPin.slice(0, -1);
            updateDisplay();
            document.getElementById('error-box').style.display = 'none';
        }

        function updateDisplay() {
            digits.forEach((el, index) => {
                el.innerText = currentPin[index] || "";
                el.classList.toggle('active', index === currentPin.length);
            });
        }

        async function validate() {
            if (currentPin.length !== 6) return;

            const loader = document.getElementById('loader');
            const errorBox = document.getElementById('error-box');
            
            loader.style.display = 'flex';
            errorBox.style.display = 'none';

            try {
                const response = await fetch("{{ route('queue.display.authorize') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ pairing_code: currentPin })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Success! Redirect to the live display
                    window.location.href = data.redirect;
                } else {
                    throw new Error(data.error || 'Authorization failed');
                }
            } catch (err) {
                loader.style.display = 'none';
                errorBox.style.display = 'block';
                errorBox.innerText = err.message;
                currentPin = ""; // Reset
                updateDisplay();
            }
        }

        // Support for physical keyboard
        document.addEventListener('keydown', (e) => {
            if (e.key >= '0' && e.key <= '9') addDigit(e.key);
            if (e.key === 'Backspace') backspace();
            if (e.key === 'Enter') validate();
        });

        updateDisplay();
    </script>
</body>
</html>
