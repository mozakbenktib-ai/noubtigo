<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}" data-theme="{{ isset($device) && $device ? ($device->theme ?? 'dark') : 'dark' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.public_display') }} | {{ $company->name }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @if(app()->getLocale() == 'ar')
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @endif
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --bg-body: #060b18;
            --bg-card: rgba(15, 23, 42, 0.75);
            --bg-card-elevated: rgba(30, 41, 59, 0.7);
            --border-glass: rgba(255, 255, 255, 0.08);
            --border-glass-strong: rgba(255, 255, 255, 0.15);
            
            --brand-green: #22c55e;
            --brand-cyan: #06b6d4;
            --brand-gradient: linear-gradient(135deg, var(--brand-green) 0%, var(--brand-cyan) 100%);
            
            --text-main: #ffffff;
            --text-muted: #94a3b8;
            --text-subtle: #64748b;
            
            --glow-green: 0 0 35px rgba(34, 197, 94, 0.35);
            --glow-cyan: 0 0 35px rgba(6, 182, 212, 0.35);
        }

        /* Light Theme Overrides */
        [data-theme="light"] {
            --bg-body: #f8fafc;
            --bg-card: rgba(255, 255, 255, 0.92);
            --bg-card-elevated: #ffffff;
            --border-glass: rgba(0, 0, 0, 0.07);
            --border-glass-strong: rgba(0, 0, 0, 0.12);
            
            --brand-green: #16a34a;
            --brand-cyan: #0284c7;
            --brand-gradient: linear-gradient(135deg, #16a34a 0%, #0284c7 100%);
            
            --text-main: #0f172a;
            --text-muted: #475569;
            --text-subtle: #64748b;
            
            --glow-green: 0 0 30px rgba(22, 163, 74, 0.15);
            --glow-cyan: 0 0 30px rgba(2, 132, 199, 0.15);
        }

        [data-theme="light"] .signage-ambient-bg {
            background: 
                radial-gradient(circle at 15% 20%, rgba(22, 163, 74, 0.06) 0%, transparent 45%),
                radial-gradient(circle at 85% 75%, rgba(2, 132, 199, 0.06) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.95) 0%, #e2e8f0 100%);
        }

        [data-theme="light"] .signage-header {
            background: rgba(255, 255, 255, 0.92);
        }

        [data-theme="light"] .brand-title {
            color: #0f172a;
        }

        [data-theme="light"] .destination-value {
            color: #0f172a;
        }

        [data-theme="light"] .destination-container {
            background: rgba(0, 0, 0, 0.03);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
        }

        [data-theme="light"] .now-serving-card {
            background: linear-gradient(160deg, rgba(255, 255, 255, 0.95) 0%, rgba(241, 245, 249, 0.98) 100%);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
        }

        [data-theme="light"] .upcoming-tickets-card,
        [data-theme="light"] .media-card {
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.04);
        }

        [data-theme="light"] .upcoming-item {
            background: rgba(241, 245, 249, 0.85);
            border-color: rgba(0, 0, 0, 0.08);
            color: #0f172a;
        }

        [data-theme="light"] .upcoming-num {
            color: #0f172a;
        }

        [data-theme="light"] .upcoming-count-badge {
            background: rgba(0, 0, 0, 0.06);
            color: #0f172a;
        }

        [data-theme="light"] .btn-fullscreen {
            background: rgba(0, 0, 0, 0.04);
            color: #475569;
        }

        [data-theme="light"] .btn-fullscreen:hover {
            background: rgba(0, 0, 0, 0.08);
            color: #0f172a;
        }

        [data-theme="light"] .time-pill,
        [data-theme="light"] .date-pill {
            background: rgba(0, 0, 0, 0.04);
            color: #0f172a;
        }

        [data-theme="light"] .time-pill span {
            color: #0f172a;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: var(--bg-body);
            color: var(--text-main);
            font-family: 'Outfit', 'Inter', sans-serif;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            user-select: none;
            -webkit-font-smoothing: antialiased;
        }

        [dir="rtl"] {
            font-family: 'Noto Sans Arabic', 'Outfit', sans-serif;
        }

        /* Digital Signage Ambient Background */
        .signage-ambient-bg {
            position: absolute;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
            background: 
                radial-gradient(circle at 15% 20%, rgba(34, 197, 94, 0.08) 0%, transparent 45%),
                radial-gradient(circle at 85% 75%, rgba(6, 182, 212, 0.07) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(15, 23, 42, 0.8) 0%, #060b18 100%);
        }

        /* ==========================================================================
           1. TOP HEADER
           ========================================================================== */
        .signage-header {
            height: 80px;
            padding: 0 2.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(10, 15, 30, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-glass);
            position: relative;
            z-index: 10;
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .brand-logo-img {
            height: 48px;
            width: auto;
            max-width: 160px;
            object-fit: contain;
            border-radius: 10px;
            filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.3));
        }

        .brand-text-block {
            display: flex;
            flex-direction: column;
        }

        .brand-title {
            font-size: 1.45rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            margin: 0;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .room-filter-tag {
            font-size: 0.85rem;
            font-weight: 600;
            background: rgba(6, 182, 212, 0.15);
            color: var(--brand-cyan);
            border: 1px solid rgba(6, 182, 212, 0.3);
            padding: 0.2rem 0.65rem;
            border-radius: 9999px;
            letter-spacing: 0;
        }

        .brand-subline {
            font-size: 0.8rem;
            color: var(--text-subtle);
            font-weight: 500;
            margin-top: 2px;
        }

        .header-center-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .header-date-box {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 1rem;
            color: var(--text-muted);
            font-weight: 500;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-glass);
            padding: 0.45rem 1rem;
            border-radius: 9999px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .live-clock-badge {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            color: #ffffff;
            font-variant-numeric: tabular-nums;
            font-family: 'Outfit', sans-serif;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
        }

        .live-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(34, 197, 94, 0.12);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: var(--brand-green);
            padding: 0.4rem 0.9rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .live-status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: var(--brand-green);
            box-shadow: 0 0 10px var(--brand-green);
            animation: pulseDot 1.8s infinite;
        }

        @keyframes pulseDot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.3); opacity: 0.6; }
        }

        .btn-fullscreen {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-glass);
            color: var(--text-muted);
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-fullscreen:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
        }

        /* ==========================================================================
           2. MAIN GRID LAYOUT
           ========================================================================== */
        .signage-main {
            flex: 1;
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 1.75rem;
            padding: 1.5rem 2.5rem;
            position: relative;
            z-index: 5;
            min-height: 0; /* Prevents overflow issues in flex container */
        }

        /* ==========================================================================
           LEFT COLUMN: OPERATIONAL QUEUE (NOW SERVING + NEXT)
           ========================================================================== */
        .queue-column {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            height: 100%;
            min-height: 0;
        }

        /* --- Dominant NOW SERVING Area --- */
        .now-serving-card {
            flex: 1.4;
            background: linear-gradient(160deg, rgba(15, 23, 42, 0.85) 0%, rgba(2, 6, 23, 0.95) 100%);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-glass-strong);
            border-radius: 28px;
            padding: 2.25rem 2.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.6), inset 0 0 40px rgba(34, 197, 94, 0.05);
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Glowing top accent border */
        .now-serving-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 10%;
            right: 10%;
            height: 3px;
            background: var(--brand-gradient);
            border-radius: 9999px;
            box-shadow: var(--glow-green);
        }

        .now-serving-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            background: rgba(34, 197, 94, 0.12);
            color: var(--brand-green);
            border: 1px solid rgba(34, 197, 94, 0.25);
            padding: 0.5rem 1.4rem;
            border-radius: 9999px;
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .now-serving-badge i {
            font-size: 1.1rem;
            animation: pulseDot 1.5s infinite;
        }

        .ticket-number-hero {
            font-size: clamp(6.5rem, 12vw, 11.5rem);
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.03em;
            background: var(--brand-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0.5rem 0 1.25rem 0;
            filter: drop-shadow(0 10px 30px rgba(34, 197, 94, 0.3));
            transition: all 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
            display: block;
        }

        /* Counter / Desk Room Destination Box */
        .destination-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-glass-strong);
            padding: 0.75rem 2.25rem;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
            max-width: 90%;
        }

        .destination-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: var(--brand-gradient);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
            box-shadow: 0 4px 15px rgba(34, 197, 94, 0.4);
        }

        .destination-meta {
            display: flex;
            flex-direction: column;
            text-align: start;
        }

        .destination-label {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-muted);
        }

        .destination-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.1;
            letter-spacing: -0.01em;
        }

        /* --- Upcoming Tickets Area (NEXT) --- */
        .upcoming-tickets-card {
            flex: 0.8;
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border-glass);
            border-radius: 24px;
            padding: 1.25rem 1.75rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .upcoming-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 0.6rem;
            border-bottom: 1px solid var(--border-glass);
        }

        .upcoming-title {
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin: 0;
        }

        .upcoming-title i {
            color: var(--brand-cyan);
            font-size: 1.15rem;
        }

        .upcoming-count-badge {
            font-size: 0.8rem;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
        }

        .upcoming-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 0.85rem;
            align-items: center;
        }

        .upcoming-item {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-glass);
            border-radius: 16px;
            padding: 0.85rem 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .upcoming-item:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(34, 197, 94, 0.3);
            transform: translateY(-2px);
        }

        .upcoming-item.first-in-line {
            border-color: rgba(34, 197, 94, 0.35);
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.08) 0%, rgba(6, 182, 212, 0.04) 100%);
            box-shadow: 0 4px 15px rgba(34, 197, 94, 0.1);
        }

        .upcoming-num {
            font-size: 1.9rem;
            font-weight: 800;
            color: #ffffff;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }

        .upcoming-badge-row {
            margin-top: 0.4rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .upcoming-tag {
            font-size: 0.65rem;
            font-weight: 700;
            padding: 0.15rem 0.45rem;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .tag-appt {
            background: rgba(6, 182, 212, 0.15);
            color: var(--brand-cyan);
            border: 1px solid rgba(6, 182, 212, 0.3);
        }

        .tag-walk {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .tag-vip {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.4);
        }

        .empty-upcoming {
            text-align: center;
            padding: 1.5rem;
            color: var(--text-subtle);
            font-size: 1.05rem;
            font-weight: 500;
        }

        /* ==========================================================================
           RIGHT COLUMN: DIGITAL SIGNAGE / INFORMATION & QR TRACKING
           ========================================================================== */
        .signage-content-column {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-glass-strong);
            border-radius: 28px;
            padding: 2.25rem 2.5rem;
            display: flex;
            flex-direction: column;
            position: relative;
            
            overflow: hidden;
            height: 100%;
        }

        /* Slides Carousel Container */
        .signage-carousel-wrapper {
            flex: 1;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .signage-slide {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.6s ease, transform 0.6s ease;
            transform: scale(0.98);
            pointer-events: none;
        }

        .signage-slide.active {
            opacity: 1;
            visibility: visible;
            transform: scale(1);
            pointer-events: auto;
            position: relative;
        }

        /* Slide 1: QR Tracking Slide */
        .qr-slide-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .signage-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(6, 182, 212, 0.12);
            color: var(--brand-cyan);
            border: 1px solid rgba(6, 182, 212, 0.3);
            padding: 0.4rem 1.1rem;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 1.25rem;
        }

        .slide-heading {
            font-size: 2.1rem;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.2;
            letter-spacing: -0.02em;
            margin: 0 0 0.75rem 0;
        }

        .slide-lead {
            font-size: 1.05rem;
            color: var(--text-muted);
            line-height: 1.55;
            margin: 0 0 1.75rem 0;
            max-width: 440px;
        }

        /* Crisp QR Code Showcase Box */
        .qr-showcase-box {
            background: #ffffff;
            padding: 1.25rem;
            border-radius: 22px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4), 0 0 30px rgba(34, 197, 94, 0.15);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .qr-image-display {
            width: 175px;
            height: 175px;
            display: block;
            object-fit: contain;
            border-radius: 8px;
        }

        .qr-footer-hint {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.8rem;
            color: #0f172a;
            font-weight: 700;
            margin-top: 0.75rem;
        }

        .qr-footer-hint i {
            color: var(--brand-green);
        }

        /* Micro Feature Badges Under QR */
        .qr-perks-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.75rem;
        }

        .qr-perk-pill {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-glass);
            padding: 0.4rem 0.85rem;
            border-radius: 9999px;
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .qr-perk-pill i {
            color: var(--brand-green);
        }

        /* Slide 2 & 3: Guidelines and Welcoming Cards */
        .guideline-steps-list {
            display: flex;
            flex-direction: column;
            gap: 1.15rem;
            margin-top: 1rem;
            width: 100%;
        }

        .guideline-item {
            display: flex;
            align-items: flex-start;
            gap: 1.15rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-glass);
            border-radius: 18px;
            padding: 1.15rem 1.4rem;
            text-align: start;
        }

        .guideline-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(34, 197, 94, 0.12);
            color: var(--brand-green);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
            border: 1px solid rgba(34, 197, 94, 0.25);
        }

        .guideline-item:nth-child(2) .guideline-icon {
            background: rgba(6, 182, 212, 0.12);
            color: var(--brand-cyan);
            border-color: rgba(6, 182, 212, 0.25);
        }

        .guideline-item:nth-child(3) .guideline-icon {
            background: rgba(245, 158, 11, 0.12);
            color: #fbbf24;
            border-color: rgba(245, 158, 11, 0.25);
        }

        .guideline-item h6 {
            margin: 0 0 0.25rem 0;
            font-size: 1.15rem;
            font-weight: 700;
            color: #ffffff;
        }

        .guideline-item p {
            margin: 0;
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.4;
        }

        /* Promo, Info & Announcement Enhancements */
        .promo-banner-wrapper, .info-media-wrapper {
            width: 100%;
            max-height: 220px;
            overflow: hidden;
            border-radius: 18px;
            margin: 1rem 0;
            border: 1px solid var(--border-glass-strong);
           
        }
        .promo-banner-img, .info-media-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 18px;
        }
        .promo-action-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(6, 182, 212, 0.15);
            color: #38bdf8;
            border: 1px solid rgba(6, 182, 212, 0.3);
            padding: 0.5rem 1.25rem;
            border-radius: 9999px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }
        .announcement-box {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 18px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        .bg-emerald {
            background: rgba(34, 197, 94, 0.15) !important;
            color: #4ade80 !important;
            border-color: rgba(34, 197, 94, 0.3) !important;
        }

        /* Slide Indicator Dots */
        .carousel-indicators-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }

        .carousel-dot {
            width: 10px;
            height: 10px;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .carousel-dot.active {
            width: 32px;
            background: var(--brand-gradient);
            box-shadow: 0 0 12px rgba(34, 197, 94, 0.5);
        }

        /* ==========================================================================
           3. FLOATING NOUTBIGO BRAND WATERMARK (BOTTOM RIGHT)
           ========================================================================== */
        .display-watermark-logo {
            position: fixed;
            bottom: 1.25rem;
            right: 1.75rem;
            z-index: 40;
            display: flex;
            align-items: center;
            opacity: 0.30;
            pointer-events: none;
            user-select: none;
            transition: opacity 0.3s ease;
        }

        [dir="rtl"] .display-watermark-logo {
            right: auto;
            left: 1.75rem;
        }

        .display-watermark-logo img {
            height: 40px;
            width: auto;
            max-width: 120px;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        [data-theme="light"] .display-watermark-logo {
            opacity: 0.30;
        }

        [data-theme="light"] .display-watermark-logo img {
            filter: none;
        }

        /* ==========================================================================
           ANIMATIONS & TRANSITIONS
           ========================================================================== */
        .fade-scale-in {
            animation: fadeScaleIn 0.55s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes fadeScaleIn {
            0% { opacity: 0; transform: scale(0.88); }
            100% { opacity: 1; transform: scale(1); }
        }

        .pulse-highlight {
            animation: callPulse 2.5s ease-out;
        }

        @keyframes callPulse {
            0% { box-shadow: 0 0 0 rgba(34, 197, 94, 0); }
            30% { box-shadow: 0 0 60px rgba(34, 197, 94, 0.45); }
            100% { box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.6); }
        }

        .slide-up {
            animation: slideUp 0.4s ease forwards;
        }

        @keyframes slideUp {
            from { transform: translateY(16px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Initial Audio Activation Overlay */
        #interaction-overlay {
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 23, 0.96);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }

        .play-button-circle {
            width: 130px;
            height: 130px;
            background: var(--brand-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4.5rem;
            color: #ffffff;
            box-shadow: 0 0 50px rgba(34, 197, 94, 0.5);
            animation: startPulse 2s infinite;
            border: none;
            margin-bottom: 2rem;
            cursor: pointer;
        }

        @keyframes startPulse {
            0%, 100% { transform: scale(1); filter: brightness(1); }
            50% { transform: scale(1.06); filter: brightness(1.15); box-shadow: 0 0 70px rgba(34, 197, 94, 0.7); }
        }

        .overlay-title {
            font-size: 2.2rem;
            font-weight: 800;
            color: #ffffff;
            text-align: center;
            letter-spacing: -0.02em;
            margin-bottom: 0.5rem;
        }

        .overlay-hint {
            font-size: 1.1rem;
            color: var(--text-muted);
            text-align: center;
        }

        /* ==========================================================================
           RESPONSIVE TUNING
           ========================================================================== */
        @media (max-width: 1024px) {
            .signage-main {
                grid-template-columns: 1fr;
                overflow-y: auto;
            }
            .signage-content-column {
                min-height: 480px;
            }
        }
    </style>
</head>
<body>
    <div class="signage-ambient-bg"></div>

    <!-- Click to Activate Audio Screen Overlay (Browser autoplay requirement) -->
    <div id="interaction-overlay" onclick="startDisplay()">
        <button class="play-button-circle">
            <i class="bi bi-play-fill"></i>
        </button>
        <div class="overlay-title">{{ __('ui.click_to_activate_display') }}</div>
        <div class="overlay-hint">
            <i class="bi bi-volume-up-fill me-1 text-success"></i> {{ __('ui.public_display') }} — Required for audio chime & screen sync
        </div>
    </div>

    <!-- Chime Audio Notification -->
    <audio id="chime-audio" preload="auto">
        <source src="https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3" type="audio/mpeg">
    </audio>

    <!-- 1. TOP HEADER -->
    <header class="signage-header">
        <div class="header-brand">
            <img src="{{ $company->getLogoUrl() }}" class="brand-logo-img" alt="{{ $company->name }}">
            <div class="brand-text-block">
                <h1 class="brand-title">
                    {{ $company->name }}
                    @if($room)
                        <span class="room-filter-tag"><i class="bi bi-geo-alt-fill me-1"></i>{{ $room->name }}</span>
                    @endif
                </h1>
                <span class="brand-subline">NoubtiGO Smart Digital Signage</span>
            </div>
        </div>

        <div class="header-center-info d-none d-lg-flex">
            <div class="header-date-box" id="live-date">
                <i class="bi bi-calendar3"></i>
                <span>{{ __('ui.loading') }}</span>
            </div>
        </div>

        <div class="header-right">
            <div class="live-clock-badge" id="live-clock">00:00:00</div>
            <div class="live-status-pill">
                <span class="live-status-dot"></span>
                <span>{{ __('ui.live_system') }}</span>
            </div>
            <button class="btn-fullscreen" onclick="toggleFullScreen()" title="{{ __('ui.fullscreen') }}">
                <i class="bi bi-arrows-fullscreen"></i>
            </button>
        </div>
    </header>

    <!-- 2. MAIN WORKSPACE GRID -->
    <main class="signage-main">
        <!-- LEFT COLUMN: OPERATIONAL QUEUE -->
        <div class="queue-column">
            
            <!-- DOMINANT NOW SERVING AREA -->
            @php $current = $activeTickets->first(); @endphp
            <div class="now-serving-card pulse-highlight" id="now-serving-card">
                <div class="now-serving-badge">
                    <i class="bi bi-broadcast"></i>
                    <span>{{ __('ui.now_serving') }}</span>
                </div>

                <div class="ticket-number-hero {{ $current ? 'fade-scale-in' : '' }}" id="main-ticket-number">
                    {{ $current->ticket_number ?? '---' }}
                </div>

                <div class="destination-container">
                    <div class="destination-icon">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <div class="destination-meta">
                        <span class="destination-label">{{ __('ui.please_proceed_to') }}</span>
                        <span class="destination-value" id="main-room-name">
                            {{ $current->room->name ?? ($room ? $room->name : __('ui.counter_desk')) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- UPCOMING TICKETS AREA (NEXT) -->
            <div class="upcoming-tickets-card">
                <div class="upcoming-header">
                    <h3 class="upcoming-title">
                        <i class="bi bi-people-fill"></i>
                        <span>{{ __('ui.next_tickets') }}</span>
                    </h3>
                    <span class="upcoming-count-badge" id="waiting-count">
                        {{ $waitingTickets->count() }} {{ __('ui.waiting') }}
                    </span>
                </div>

                <div class="upcoming-grid" id="next-tickets-list">
                    @forelse($waitingTickets as $ticket)
                        <div class="upcoming-item slide-up {{ $loop->first ? 'first-in-line' : '' }}" style="animation-delay: {{ $loop->index * 0.08 }}s">
                            <span class="upcoming-num">{{ $ticket->ticket_number }}</span>
                            <div class="upcoming-badge-row">
                                @if($ticket->is_vip)
                                    <span class="upcoming-tag tag-vip">{{ __('ui.vip') }}</span>
                                @endif
                                <span class="upcoming-tag {{ $ticket->source === 'appointment' ? 'tag-appt' : 'tag-walk' }}">
                                    {{ $ticket->source === 'appointment' ? 'APPT' : 'WALK' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="empty-upcoming col-12">{{ __('ui.no_tickets_waiting') }}</div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN: DIGITAL SIGNAGE CONTENT & QR TRACKING -->
        <div class="signage-content-column">
            
            <div class="signage-carousel-wrapper" id="signage-carousel-container">
                @if(isset($contents) && $contents->isNotEmpty())
                    @foreach($contents as $index => $content)
                        @php
                            $type = $content->type;
                            $duration = $content->duration ?? 10;
                        @endphp
                        <div class="signage-slide {{ $loop->first ? 'active' : '' }}" 
                             id="slide-{{ $content->id }}" 
                             data-duration="{{ $duration }}" 
                             data-index="{{ $index }}">
                            
                            @if($type === \App\Modules\Displays\Models\DisplayContent::TYPE_QR_TRACKING)
                                <div class="qr-slide-content">
                                    <span class="signage-badge-pill">
                                        <i class="bi bi-phone"></i> {{ __('ui.mobile_live_tracking') }}
                                    </span>
                                    <h2 class="slide-heading">{{ $content->title }}</h2>
                                    @if($content->description)
                                        <p class="slide-lead">{{ $content->description }}</p>
                                    @endif
                                    <div class="qr-showcase-box">
                                        <img src="{{ $content->qr_image_url }}" alt="{{ __('ui.scan_with_camera') }}" class="qr-image-display" loading="lazy">
                                        <div class="qr-footer-hint">
                                            <i class="bi bi-qr-code-scan"></i>
                                            <span>{{ __('ui.scan_with_camera') }}</span>
                                        </div>
                                    </div>
                                    <div class="qr-perks-row">
                                        <div class="qr-perk-pill"><i class="bi bi-check-circle-fill"></i> {{ __('ui.zero_app_install') }}</div>
                                        <div class="qr-perk-pill"><i class="bi bi-check-circle-fill"></i> {{ __('ui.live_turn_timer') }}</div>
                                        <div class="qr-perk-pill"><i class="bi bi-check-circle-fill"></i> {{ __('ui.wait_in_comfort') }}</div>
                                    </div>
                                </div>

                            @elseif($type === \App\Modules\Displays\Models\DisplayContent::TYPE_PROMOTION)
                                <div class="promo-slide-content text-center">
                                    <span class="signage-badge-pill bg-emerald">
                                        <i class="bi bi-megaphone-fill"></i> {{ __('ui.special_feature') }}
                                    </span>
                                    <h2 class="slide-heading">{{ $content->title }}</h2>
                                    @if($content->description)
                                        <p class="slide-lead">{{ $content->description }}</p>
                                    @endif
                                    @if($content->image_url)
                                        <div class="promo-banner-wrapper">
                                            <img src="{{ $content->image_url }}" alt="{{ $content->title }}" class="promo-banner-img">
                                        </div>
                                    @endif
                                    @if($content->link_url)
                                        <div class="promo-action-pill">
                                            <i class="bi bi-link-45deg"></i>
                                            <span>{{ $content->link_url }}</span>
                                        </div>
                                    @endif
                                </div>

                            @elseif($type === \App\Modules\Displays\Models\DisplayContent::TYPE_ANNOUNCEMENT)
                                <div class="announcement-slide-content text-center">
                                    <span class="signage-badge-pill" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border-color: rgba(245, 158, 11, 0.3);">
                                        <i class="bi bi-bell-fill"></i> {{ __('ui.announcement') }}
                                    </span>
                                    <h2 class="slide-heading" style="color: #fef08a;">{{ $content->title }}</h2>
                                    @if($content->description)
                                        <div class="announcement-box">
                                            <p class="slide-lead mb-0" style="color: #ffffff;">{{ $content->description }}</p>
                                        </div>
                                    @endif
                                    @if($content->image_url)
                                        <div class="promo-banner-wrapper">
                                            <img src="{{ $content->image_url }}" alt="{{ $content->title }}" class="promo-banner-img">
                                        </div>
                                    @endif
                                </div>

                            @else
                                <!-- Information Type (Default) -->
                                <div class="info-slide-content text-center">
                                    <span class="signage-badge-pill">
                                        <i class="bi bi-info-circle-fill"></i> {{ __('ui.information') }}
                                    </span>
                                    <h2 class="slide-heading">{{ $content->title }}</h2>
                                    @if($content->description)
                                        <p class="slide-lead">{{ $content->description }}</p>
                                    @endif
                                    @if($content->image_url)
                                        <div class="info-media-wrapper">
                                            <img src="{{ $content->image_url }}" alt="{{ $content->title }}" class="info-media-img">
                                        </div>
                                    @endif
                                    <div class="guideline-steps-list">
                                        <div class="guideline-item">
                                            <div class="guideline-icon">
                                                <i class="bi bi-shield-check"></i>
                                            </div>
                                            <div>
                                                <h6>{{ __('ui.smooth_pleasant_service') }}</h6>
                                                <p>{{ __('ui.smooth_service_hint') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        </div>
                    @endforeach
                @else
                    <!-- Clean Default Noubtigo Branded Fallback State -->
                    <div class="signage-slide active" id="slide-default-1" data-duration="12" data-index="0">
                        <div class="qr-slide-content">
                            <span class="signage-badge-pill">
                                <i class="bi bi-phone"></i> {{ __('ui.mobile_live_tracking') }}
                            </span>
                            <h2 class="slide-heading">{{ __('ui.follow_ticket_remotely') }}</h2>
                            <p class="slide-lead">{{ __('ui.scan_qr_track_lead') }}</p>
                            <div class="qr-showcase-box">
                                @php
                                    $trackUrl = $company && $company->secure_public_token 
                                        ? route('queue.track.hub', $company->secure_public_token) 
                                        : url('/track/status');
                                    $qrApi = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&margin=4&data=' . urlencode($trackUrl);
                                @endphp
                                <img src="{{ $qrApi }}" alt="{{ __('ui.scan_with_camera') }}" class="qr-image-display" id="qr-tracking-img">
                                <div class="qr-footer-hint">
                                    <i class="bi bi-qr-code-scan"></i>
                                    <span>{{ __('ui.scan_with_camera') }}</span>
                                </div>
                            </div>
                            <div class="qr-perks-row">
                                <div class="qr-perk-pill"><i class="bi bi-check-circle-fill"></i> {{ __('ui.zero_app_install') }}</div>
                                <div class="qr-perk-pill"><i class="bi bi-check-circle-fill"></i> {{ __('ui.live_turn_timer') }}</div>
                                <div class="qr-perk-pill"><i class="bi bi-check-circle-fill"></i> {{ __('ui.wait_in_comfort') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="signage-slide" id="slide-default-2" data-duration="10" data-index="1">
                        <span class="signage-badge-pill">
                            <i class="bi bi-shield-check"></i> {{ __('ui.smooth_service') }}
                        </span>
                        <h2 class="slide-heading">{{ __('ui.welcome_to_company', ['company' => $company->name]) }}</h2>
                        <p class="slide-lead">{{ __('ui.service_commitment') }}</p>
                        <div class="guideline-steps-list">
                            <div class="guideline-item">
                                <div class="guideline-icon"><i class="bi bi-bell-fill"></i></div>
                                <div><h6>{{ __('ui.watch_for_call') }}</h6><p>{{ __('ui.watch_for_call_desc') }}</p></div>
                            </div>
                            <div class="guideline-item">
                                <div class="guideline-icon"><i class="bi bi-file-earmark-text-fill"></i></div>
                                <div><h6>{{ __('ui.have_documents_ready') }}</h6><p>{{ __('ui.have_documents_ready_desc') }}</p></div>
                            </div>
                            <div class="guideline-item">
                                <div class="guideline-icon"><i class="bi bi-chat-heart-fill"></i></div>
                                <div><h6>{{ __('ui.staff_ready_to_help') }}</h6><p>{{ __('ui.staff_ready_to_help_desc') }}</p></div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Slide Progress Indicators -->
            <div class="carousel-indicators-bar" id="carousel-dots-bar">
                @php
                    $slideCount = (isset($contents) && $contents->isNotEmpty()) ? $contents->count() : 2;
                @endphp
                @if($slideCount > 1)
                    @for($i = 0; $i < $slideCount; $i++)
                        <span class="carousel-dot {{ $i === 0 ? 'active' : '' }}" onclick="goToSlide({{ $i }})"></span>
                    @endfor
                @endif
            </div>

        </div>
    </main>

    <!-- Floating Minimal Opacity Noubtigo Brand Watermark -->
    <div class="display-watermark-logo">
        <img src="{{ asset('images/ng_logo.png') }}" alt="Noubtigo">
    </div>

    <!-- Session Superseded Overlay (Displayed if this screen is opened elsewhere) -->
    <div id="session-superseded-overlay" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(6, 11, 24, 0.96); backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px); align-items: center; justify-content: center;">
        <div style="max-width: 580px; width: 90%; background: rgba(15, 23, 42, 0.95); border: 1px solid rgba(245, 158, 11, 0.35); box-shadow: 0 25px 60px rgba(0, 0, 0, 0.8), 0 0 50px rgba(245, 158, 11, 0.25); border-radius: 2rem; padding: 3rem 2.5rem; text-align: center;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(245, 158, 11, 0.15); border: 2px solid rgba(245, 158, 11, 0.4); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; color: #fbbf24; font-size: 2.5rem;">
                <i class="bi bi-display"></i>
            </div>
            <h2 style="font-size: 1.85rem; font-weight: 800; color: #ffffff; margin-bottom: 0.75rem;">
                {{ __('ui.display_session_superseded') ?? 'Session Disconnected' }}
            </h2>
            <p style="color: #94a3b8; font-size: 1.05rem; line-height: 1.6; margin-bottom: 2rem;">
                {{ __('ui.display_session_superseded_desc') ?? 'This display link was opened on another screen or device. Each display link is limited to one active screen at a time to prevent duplicate displays.' }}
            </p>
            @if(isset($device) && $device && $device->device_token)
            <form action="{{ route('queue.display.takeover', $device->device_token) }}" method="POST">
                @csrf
                <input type="hidden" name="session_id" class="overlay-session-id-input" value="">
                <button type="submit" style="background: linear-gradient(135deg, #22c55e 0%, #06b6d4 100%); color: #ffffff; border: none; padding: 1rem 2rem; font-size: 1.1rem; font-weight: 700; border-radius: 9999px; width: 100%; cursor: pointer; box-shadow: 0 10px 25px rgba(34, 197, 94, 0.4); transition: all 0.2s ease;">
                    <i class="bi bi-box-arrow-in-up-right me-2"></i> {{ __('ui.take_over_display') ?? 'Take Over / Reconnect Here' }}
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Unique Client Session Identity
        let clientSessionId = sessionStorage.getItem('display_client_session_id');
        if (!clientSessionId) {
            clientSessionId = 'sess_' + Math.random().toString(36).substring(2, 12) + '_' + Date.now().toString(36);
            sessionStorage.setItem('display_client_session_id', clientSessionId);
        }

        // Keep URL updated with session_id parameter
        if (!window.location.search.includes('session_id=')) {
            const newUrl = new URL(window.location.href);
            newUrl.searchParams.set('session_id', clientSessionId);
            window.history.replaceState({}, '', newUrl.toString());
        }

        document.querySelectorAll('.overlay-session-id-input').forEach(el => el.value = clientSessionId);

        // Immediate release when closing the tab/window
        window.addEventListener('beforeunload', () => {
            @if(isset($device) && $device && $device->device_token)
                const releaseUrl = "{{ route('queue.display.release', $device->device_token) }}";
                const payload = JSON.stringify({ session_id: clientSessionId, _token: '{{ csrf_token() }}' });
                const blob = new Blob([payload], { type: 'application/json' });
                navigator.sendBeacon(releaseUrl, blob);
            @endif
        });

        const audio = document.getElementById('chime-audio');
        const displayTimezone = '{{ app(\App\Services\TimezoneService::class)->resolve() }}';

        // Unlock browser audio restriction
        function startDisplay() {
            if (audio) {
                audio.play().then(() => {
                    audio.pause();
                    audio.currentTime = 0;
                }).catch(e => console.log('Audio init error:', e));
            }
            const overlay = document.getElementById('interaction-overlay');
            if (overlay) overlay.style.display = 'none';
        }

        // Fullscreen Toggle
        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => console.log(err));
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }

        // Live Clock & Localized Date
        function updateClockAndDate() {
            const now = new Date();
            
            // Time
            const time = now.toLocaleTimeString([], {
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit', 
                hour12: false, 
                timeZone: displayTimezone
            });
            const clockEl = document.getElementById('live-clock');
            if (clockEl) clockEl.textContent = time;

            // Date
            const dateEl = document.getElementById('live-date');
            if (dateEl) {
                const dateStr = now.toLocaleDateString('{{ app()->getLocale() }}', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    timeZone: displayTimezone
                });
                const span = dateEl.querySelector('span');
                if (span) span.textContent = dateStr;
            }
        }
        setInterval(updateClockAndDate, 1000);
        updateClockAndDate();

        // Dynamic Signage Content Slides Rotation
        let currentSlideIndex = 0;
        let slideTimer = null;

        function getSlideDuration(slideEl) {
            if (!slideEl) return 10000;
            const dur = parseInt(slideEl.getAttribute('data-duration'), 10);
            return (isNaN(dur) || dur < 3) ? 10000 : dur * 1000;
        }

        function goToSlide(index) {
            const slides = document.querySelectorAll('.signage-slide');
            const dots = document.querySelectorAll('.carousel-dot');

            if (slides.length <= 1) {
                if (slides[0] && !slides[0].classList.contains('active')) {
                    slides[0].classList.add('active');
                }
                return;
            }

            slides.forEach(s => s.classList.remove('active'));
            dots.forEach(d => d.classList.remove('active'));

            currentSlideIndex = index % slides.length;
            if (slides[currentSlideIndex]) {
                slides[currentSlideIndex].classList.add('active');
                const nextDuration = getSlideDuration(slides[currentSlideIndex]);
                clearTimeout(slideTimer);
                slideTimer = setTimeout(() => {
                    goToSlide(currentSlideIndex + 1);
                }, nextDuration);
            }
            if (dots[currentSlideIndex]) {
                dots[currentSlideIndex].classList.add('active');
            }
        }

        // Initialize rotation on load
        document.addEventListener('DOMContentLoaded', () => {
            const slides = document.querySelectorAll('.signage-slide');
            if (slides.length > 1) {
                const firstDuration = getSlideDuration(slides[0]);
                clearTimeout(slideTimer);
                slideTimer = setTimeout(() => {
                    goToSlide(1);
                }, firstDuration);
            }
        });

        // Real-Time Queue Listeners via Laravel Echo
        document.addEventListener('DOMContentLoaded', () => {
            const companyId = {{ $company->id }};
            const currentRoomId = @json($room ? $room->id : null);
            
            if (typeof Echo !== 'undefined') {
                Echo.channel(`queue.company.${companyId}`)
                    .listen('.ticket.updated', (e) => {
                        if (!currentRoomId || e.ticket.room_id == currentRoomId) {
                            const isBeingCalled = e.ticket.status === 'called' || e.ticket.status === 'serving';
                            refreshDisplay(isBeingCalled);
                        }
                    })
                    .listen('.ticket.created', (e) => {
                        if (!currentRoomId || e.ticket.room_id == currentRoomId) {
                            refreshDisplay(false);
                        }
                    });
            }
            else{
                 window.displayPollTimer = setInterval(() => {
                refreshDisplay(false);
            }, 8000);
            }

        });

        async function refreshDisplay(shouldPlaySound = false) {
            try {
                const targetUrl = new URL(window.location.href);
                targetUrl.searchParams.set('session_id', clientSessionId);

                const response = await fetch(targetUrl.toString(), {
                    headers: { 
                        'Accept': 'application/json',
                        'X-Display-Session': clientSessionId
                    }
                });

                if (response.status === 409) {
                    handleSessionTermination();
                    return;
                }

                const data = await response.json();
                if (data.session_terminated || data.error === 'session_superseded') {
                    handleSessionTermination();
                    return;
                }

                updateUI(data, shouldPlaySound);
            } catch (error) {
                console.error("Queue display refresh error:", error);
            }
        }

        function handleSessionTermination() {
            if (window.displayPollTimer) {
                clearInterval(window.displayPollTimer);
            }
            if (audio) {
                try { audio.pause(); } catch(e) {}
            }
            const overlay = document.getElementById('session-superseded-overlay');
            if (overlay) {
                overlay.style.display = 'flex';
            }
        }

        function updateUI(data, shouldPlaySound = false) {
            const current = data.active && data.active.length > 0 ? data.active[0] : null;
            const ticketEl = document.getElementById('main-ticket-number');
            const roomEl = document.getElementById('main-room-name');
            const cardEl = document.getElementById('now-serving-card');
            const listEl = document.getElementById('next-tickets-list');
            const countEl = document.getElementById('waiting-count');
            
            // 1. Update Currently Called Ticket
            const oldTicket = ticketEl ? ticketEl.textContent.trim() : '';
            const newTicket = current ? current.ticket_number : '---';
            
            if (oldTicket !== newTicket) {
                if (ticketEl) ticketEl.textContent = newTicket;
                if (roomEl) roomEl.textContent = current && current.room ? current.room.name : '{{ $room ? $room->name : __("ui.counter_desk") }}';
                
                // Trigger smooth animation & card glow
                if (ticketEl) {
                    ticketEl.classList.remove('fade-scale-in');
                    void ticketEl.offsetWidth; 
                    ticketEl.classList.add('fade-scale-in');
                }
                if (cardEl) {
                    cardEl.classList.remove('pulse-highlight');
                    void cardEl.offsetWidth;
                    cardEl.classList.add('pulse-highlight');
                }

                // Play notification chime
                if (audio && current && (shouldPlaySound || oldTicket !== '---')) {
                    audio.play().catch(e => console.log("Chime play error:", e));
                }
            }

            // 2. Update Upcoming Tickets
            const waiting = data.waiting || [];
            if (countEl) {
                countEl.textContent = `${waiting.length} {{ __('ui.waiting') }}`;
            }

            if (listEl) {
                if (waiting.length === 0) {
                    listEl.innerHTML = `<div class="empty-upcoming col-12">{{ __('ui.no_tickets_waiting') }}</div>`;
                } else {
                    listEl.innerHTML = waiting.map((t, index) => {
                        const isAppt = t.source === 'appointment';
                        const isVip = t.is_vip;
                        const isFirst = index === 0;

                        return `
                            <div class="upcoming-item slide-up ${isFirst ? 'first-in-line' : ''}" style="animation-delay: ${index * 0.06}s">
                                <span class="upcoming-num">${t.ticket_number}</span>
                                <div class="upcoming-badge-row">
                                    ${isVip ? '<span class="upcoming-tag tag-vip">{{ __("ui.vip") }}</span>' : ''}
                                    <span class="upcoming-tag ${isAppt ? 'tag-appt' : 'tag-walk'}">
                                        ${isAppt ? 'APPT' : 'WALK'}
                                    </span>
                                </div>
                            </div>
                        `;
                    }).join('');
                }
            }
        }
    </script>
</body>
</html>
