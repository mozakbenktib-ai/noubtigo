<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Noubtigo Scan Poster</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Outfit:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --dark-blue: #1e293b;
            --teal-brand: #14b8a6;
            --bg-gradient: linear-gradient(to bottom, #f0fdfa, #ffffff);
            --shadow-card: 0 20px 40px rgba(0, 0, 0, 0.08);
        }

        @page {
            size: A4;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
        }

        body {
            margin: 0;
            padding: 0;
            background: #f1f5f9;
            font-family: 'Cairo', 'Outfit', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .poster-page {
            background: var(--bg-gradient);
            width: 210mm;
            height: 297mm;
            padding: 0;
            position: relative;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.1);
        }

        /* Geometric Background Pattern */
        .bg-pattern {
            position: absolute;
            top: 400px;
            left: 0;
            right: 0;
            bottom: 0;
            opacity: 0.1;
            background-image: url("{{ asset('images/ng_logo1.png') }}");
            z-index: 1;
        }

        /* Header */
        header {
            padding: 40px 60px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            z-index: 10;
        }

        .fox-logo {
            width: 60px;
            height: 60px;
            padding: 5px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .fox-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .noubtigo-center-logo {
            position: absolute;
            left: 50%;
            top: 40px;
            transform: translateX(-50%);
        }

        .noubtigo-center-logo img {
            height: 65px;
        }

        /* Content */
        .content {
            flex: 1;
            padding: 0 60px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            z-index: 10;
            margin-top: 40px;
        }

        .main-title {
            font-size: 6rem;
            font-weight: 900;
            color: #0f172a;
            margin: 0;
            line-height: 1;
            text-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .translations {
            margin-top: 20px;
            color: var(--teal-brand);
            font-weight: 700;
            letter-spacing: 1px;
            font-size: 1.4rem;
            text-transform: uppercase;
        }

        .instructions {
            margin-top: 30px;
            font-size: 1.25rem;
            font-weight: 600;
            color: #334155;
            max-width: 600px;
            line-height: 1.5;
        }

        /* QR Section */
        .qr-section {
            margin-top: 50px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        .qr-card {
            background: white;
            padding: 25px;
            border-radius: 35px;
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(0, 0, 0, 0.02);
        }

        .qr-card img {
            width: 320px;
            height: 320px;
            display: block;
        }

        .scan-pill {
            background: #0f172a;
            color: white;
            padding: 10px 40px;
            border-radius: 100px;
            font-size: 1.3rem;
            font-weight: 800;
        }

        /* Footer */
        footer {
            padding: 40px 60px;
            border-top: 1px solid #cbd5e1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
            position: relative;
        }

        .footer-website {
            font-weight: 800;
            color: #0f172a;
            font-size: 1.1rem;
        }

        .footer-tagline {
            font-weight: 800;
            color: #64748b;
            font-size: 0.9rem;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .faint-fox-footer {
            position: absolute;
            bottom: -20px;
            right: 0;
            width: 250px;
            opacity: 0.15;
            pointer-events: none;
            z-index: 1;
        }

        /* Controls */
        .controls {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .btn-print {
            background: #0f172a;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .poster-page {
                width: 100%;
                height: 100%;
                box-shadow: none;
                margin: 0;
                border: none;
            }

            .controls {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="controls">
        <button onclick="window.print()" class="btn-print">
            <i class="bi bi-printer-fill"></i> PRINT
        </button>
    </div>

    <div class="poster-page">
        <div class="bg-pattern"></div>

        <!-- Header -->
        <header>
            <div class="fox-logo">
                <img src="{{ $company->getLogoUrl() }}" alt="Logo">
            </div>

            <div class="noubtigo-center-logo">
                <img src="{{ asset('images/ng_logo.png') }}" alt="Noubtigo">
            </div>

            <div class="fox-logo">
                <img src="{{ $company->getLogoUrl() }}" alt="Logo">
            </div>
        </header>

        <!-- Content -->
        <div class="content">
            <h1 class="main-title">تبع نوبتك</h1>

            <div class="translations">
                <div>SUIVEZ VOTRE TOUR</div>
                <div>TRACK YOUR TURN</div>
            </div>

            <p class="instructions">
                امسح رمز QR وتابع دورك مباشرة من أي مكان بدون الحاجة للانتظار داخل القاعة
            </p>

            <div class="qr-section">
                <div class="qr-card">
                    @php
                        $trackingUrl = route('queue.track.hub', $company->secure_public_token);
                    @endphp
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=400x400&data={{ urlencode($trackingUrl) }}&color=0F172A"
                        alt="QR Code">
                </div>

                <div class="scan-pill">امسح هنا</div>
            </div>
        </div>

        <!-- Footer -->
        <footer>
            <div class="footer-website">www.noubtigo.com</div>
            <div class="footer-tagline">SMART QUEUE MANAGEMENT SYSTEM</div>
        </footer>
    </div>

</body>

</html>