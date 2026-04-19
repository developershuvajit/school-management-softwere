<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management Softwere</title>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&family=Merriweather:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

   <!-- PWA Meta -->
<meta name="theme-color" content="#0a5cff">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="School Management Softwere">

<!-- Manifest -->
<link rel="manifest" href="/manifest.json">

<!-- Favicon -->
<link rel="icon" type="image/png" href="public/images/logo.png">

<!-- Apple Touch Icon -->
<link rel="apple-touch-icon" href="public/images/logo.png">


</head>
 <style>
        :root {
            --bg: #0b0f14;
            --card: #0f1720;
            --muted: #98a0aa;
            --accent: #2bb7ff;
            --accent-2: #7be495;
            --gold: #f9a826;
            --glass: rgba(255, 255, 255, 0.04);
            --radius: 14px;
            --max-width: 1200px;
            --text: #e6eef6;
            --soft-shadow: 0 6px 30px rgba(2, 6, 23, 0.6);
            --glass-border: rgba(255, 255, 255, 0.04);
            --footer-gap: 36px;
        }

        /* ---------- Base ---------- */
        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
        }

        body {
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
            background: radial-gradient(1200px 400px at 10% 10%, rgba(43, 183, 255, 0.06), transparent), var(--bg);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        /* Utility container */
        .container {
            width: 92%;
            max-width: var(--max-width);
            margin: 0 auto;
            padding: 24px 12px;
        }

        /* ---------- Header ---------- */
        header {
            position: sticky;
            top: 12px;
            z-index: 1200;
            padding: 10px 0;
            background: linear-gradient(180deg, rgba(15, 19, 24, 0.6), rgba(15, 19, 24, 0.45));
            backdrop-filter: blur(8px);
            border-radius: 12px;
            box-shadow: var(--soft-shadow);
        }

        .header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo img {
            height: 56px;
            border-radius: 10px;
            object-fit: cover;
        }

        .logo-text {
            font-weight: 700;
            font-size: 20px;
            color: var(--text);
            letter-spacing: 0.2px;
        }

        .logo-text span {
            color: var(--gold);
        }

        .tagline {
            font-size: 12px;
            color: var(--muted);
            margin-top: 2px;
            font-weight: 500;
        }

        nav {
            display: flex;
            align-items: center;
            gap: 26px;
        }

        nav a {
            color: var(--muted);
            text-decoration: none;
            font-weight: 600;
            padding: 8px 12px;
            border-radius: 10px;
            transition: all .18s ease;
        }

        nav a:hover {
            color: var(--text);
            background: var(--glass);
            transform: translateY(-2px);
        }

        .cta-button {
            background: linear-gradient(90deg, var(--gold), #f6c86a);
            color: #0b0f14;
            padding: 9px 18px;
            border-radius: 999px;
            font-weight: 700;
            box-shadow: 0 8px 30px rgba(249, 168, 38, 0.12);
        }

        .mobile-menu {
            display: none;
            color: var(--text);
            font-size: 20px;
        }

        /* ---------- Hero / Slider ---------- */
        .banner-slider {
            position: relative;
            height: 74vh;
            min-height: 520px;
            margin: 36px 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--soft-shadow);
        }

        .slide {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            opacity: 0;
            transform: translateY(8px);
            transition: opacity .8s ease, transform .8s ease;
        }

        .slide.active {
            opacity: 1;
            transform: none;
        }

        .slide::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(7, 10, 14, 0.75) 20%, rgba(7, 10, 14, 0.3) 60%);
        }

        .slide-inner {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            gap: 18px;
            padding: 56px 6% 56px 8%;
            max-width: 680px;
        }

        .kicker {
            display: inline-block;
            background: rgba(123, 228, 149, 0.06);
            color: var(--accent-2);
            border: 1px solid rgba(123, 228, 149, 0.08);
            padding: 8px 12px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 12px;
        }

        .slide h1 {
            font-family: 'Merriweather', serif;
            font-size: 42px;
            margin: 6px 0;
            line-height: 1.05;
            color: var(--text);
        }

        .slide p {
            color: var(--muted);
            font-size: 16px;
            max-width: 680px;
        }

        /* ---------- Notice Section (updated: slow scroll + highlight visible items) ---------- */
        .notice-section {
            padding: 28px 0 40px;
            background: #0b0f14;
        }

        .notice-frame {
            width: 100%;
            max-width: 900px;
            margin: 12px auto 0;
            background: var(--card);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(15, 23, 36, 0.04);
            overflow: hidden;
            position: relative;
        }

        /* Use group wrappers; animate transform more slowly for smoother scroll */
        .notice-frame .notice-list {
            margin: 0;
            padding: 0;
            list-style: none;
            transform: translateY(0);
            transition: transform 1.2s;
            /* slower */
        }

        /* Each group wrapper holds visibleCount items */
        .notice-group {
            display: block;
        }

        /* item styling */
        .notice-item {
            padding: 14px 18px;
            border-bottom: 1px dashed rgba(15, 23, 36, 0.04);
            color: var(--text);
            font-weight: 600;
            display: block;
            line-height: 1.4;
            background: transparent;
            transform: translateX(0);
            transition: background .28s ease, transform .18s ease, box-shadow .18s ease;
        }

        /* visible (highlighted) items styling */
        .notice-item.visible {
            background: linear-gradient(90deg, rgba(249, 168, 38, 0.06), rgba(255, 255, 255, 0.02));
            box-shadow: 0 6px 18px rgba(249, 168, 38, 0.06);
            border-left: 4px solid var(--gold);
            padding-left: 14px;
            /* compensate for border */
            color: var(--text);
            transform: translateX(0);
        }

        /* subtle meta */
        .notice-item small {
            display: block;
            color: var(--muted);
            font-weight: 400;
            margin-top: 6px;
        }

        /* remove last border if you want */
        .notice-item:last-child {
            border-bottom: none;
        }

        /* hover/focus outline on container for accessibility */
        .notice-frame:focus {
            outline: 3px solid rgba(14, 165, 255, 0.12);
            outline-offset: 4px;
        }

        /* responsive: tighten padding on small screens */
        @media (max-width:560px) {
            .notice-item {
                padding: 12px 14px;
                font-size: 14px;
            }

            .notice-item.visible {
                padding-left: 12px;
            }
        }

        /* buttons */
        .hero-buttons {
            display: flex;
            gap: 14px;
            margin-top: 10px;
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--accent), #2a9fff);
            color: #022430;
            padding: 12px 18px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            box-shadow: 0 10px 30px rgba(43, 183, 255, 0.08);
        }

        .btn-secondary {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.06);
            color: var(--text);
            padding: 12px 18px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
        }

        /* slider controls */
        .slider-controls {
            position: absolute;
            right: 18px;
            bottom: 18px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            z-index: 6;
        }

        .arrow {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.04);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            backdrop-filter: blur(4px);
            transition: transform .18s ease;
        }

        .arrow:hover {
            transform: translateY(-4px);
        }

        .dots {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: 20px;
            display: flex;
            gap: 10px;
            z-index: 6;
        }

        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.02);
            cursor: pointer;
            transition: transform .12s ease, background .12s ease;
        }

        .dot.active {
            transform: scale(1.35);
            background: linear-gradient(90deg, var(--accent), var(--accent-2));
            box-shadow: 0 6px 20px rgba(43, 183, 255, 0.12);
        }

        /* hero right image */
        .hero-image-wrap {
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 48%;
            overflow: hidden;
        }

        .hero-image {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            filter: contrast(1.02) saturate(1.05) brightness(.95);
            transform-origin: center;
            transition: transform .7s ease;
        }

        .slide.active .hero-image {
            transform: scale(1.04);
        }

        /* ---------- Sections ---------- */
        section {
            padding: 64px 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 34px;
        }

        .section-title h2 {
            font-family: 'Merriweather', serif;
            color: var(--gold);
            font-size: 26px;
            margin-bottom: 8px;
        }

        .section-title p {
            color: var(--muted);
            max-width: 820px;
            margin: 0 auto;
        }

        /* features */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 22px;
        }

        .feature-card {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0.01));
            padding: 26px;
            border-radius: 12px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 8px 30px rgba(2, 6, 23, 0.5);
            transition: transform .22s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--accent), #2a5da8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            color: white;
            margin: 0 auto 14px;
        }

        .feature-card h3 {
            color: var(--accent);
            margin-bottom: 8px;
        }

        .feature-card p {
            color: var(--muted);
        }

        /* programs */
        .programs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }

        .program-card {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.04);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.01), rgba(255, 255, 255, 0.005));
            box-shadow: 0 10px 30px rgba(2, 6, 23, 0.5);
            transition: transform .22s ease;
        }

        .program-image {
            height: 180px;
            background-size: cover;
            background-position: center;
        }

        .program-content {
            padding: 18px;
        }

        .program-content h3 {
            color: var(--accent);
        }

        .program-content p {
            color: var(--muted);
            margin-bottom: 12px;
        }

        /* testimonials */
        .testimonials-container {
            display: flex;
            flex-direction: column;
            gap: 18px;
            align-items: center;
        }

        .testimonial-card {
            max-width: 820px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), transparent);
            padding: 22px;
            border-radius: 12px;
            border: 1px solid var(--glass-border);
        }

        .testimonial-text {
            color: var(--text);
            font-style: italic;
            margin-bottom: 14px;
        }

        .author-avatar {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--accent), #2a5da8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: #00111a;
        }

        /* cta */
        .cta-section {
            border-radius: 12px;
            padding: 48px;
            text-align: center;
            background: linear-gradient(90deg, rgba(43, 183, 255, 0.12), rgba(123, 228, 149, 0.06));
            margin: 28px 0;
        }

        .cta-section h2 {
            font-size: 28px;
            color: var(--text);
            margin-bottom: 12px;
        }

        .cta-section p {
            color: var(--muted);
            max-width: 720px;
            margin: 0 auto 18px;
        }

        /* ---------- Footer (improved & responsive) ---------- */
        footer {
            background: linear-gradient(180deg, rgba(7, 10, 14, 1) 0%, rgba(11, 15, 20, 1) 100%);
            color: var(--muted);
            padding: 64px 0 28px;
            position: relative;
            z-index: 5;
            border-top: 1px solid rgba(255, 255, 255, 0.02);
        }

        /* layout: use grid for neat columns and responsive reflow */
        .footer-grid {
            width: 92%;
            max-width: var(--max-width);
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.4fr 1fr 1fr 1.1fr;
            /* left column slightly wider */
            gap: var(--footer-gap);
            align-items: start;
            padding: 8px 12px;
        }

        /* Left / about column */
        .footer-col {
            min-width: 0;
        }

        /* prevent overflow */
        .footer-col h3 {
            color: var(--gold);
            margin-bottom: 12px;
            font-size: 18px;
        }

        .footer-col p {
            color: var(--muted);
            line-height: 1.7;
            margin-bottom: 12px;
        }

        /* Quick link lists */
        .footer-col ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-col ul li {
            margin-bottom: 12px;
        }

        .footer-col ul li a {
            color: var(--muted);
            text-decoration: none;
            display: block;
            transition: color .16s ease, transform .12s ease;
            line-height: 1.6;
        }

        .footer-col ul li a:hover {
            color: var(--text);
            transform: translateY(-2px);
        }

        /* contact list icons spacing */
        .footer-contact li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            color: var(--muted);
            margin-bottom: 12px;
        }

        .footer-contact li i {
            min-width: 18px;
            color: var(--muted);
        }

        /* ------------------ Footer anchor & visited reset ------------------ */

        footer a,
        footer a:link,
        footer a:visited,
        footer a:active,
        footer a:hover {
            color: var(--muted);
            text-decoration: none;
        }


        footer a:hover {
            color: var(--text);
        }

        /* ------------------ Social icons (fixed) ------------------ */

        .footer-col .social-links {
            display: flex;
            gap: 10px;
            font-size: 20px;
            margin-top: 10px;
            align-items: center;
        }

        .footer-col .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.02);
            color: var(--muted);
            text-decoration: none;
            transition: transform .18s ease, background .18s ease, color .18s ease, box-shadow .18s ease;
            -webkit-tap-highlight-color: transparent;
        }


        .footer-col .social-links a:visited {
            color: var(--muted) !important;
        }


        .footer-col .social-links a i,
        .footer-col .social-links a svg {
            color: inherit;
            font-size: 16px;
            line-height: 1;
            display: inline-block;
        }


        .footer-col .social-links a:hover {
            background: var(--gold);
            color: #0b0f14 !important;
            transform: translateY(-4px);
            box-shadow: 0 8px 26px rgba(249, 168, 38, 0.08);
        }


        .footer-col .social-links a:focus {
            outline: 3px solid rgba(123, 228, 149, 0.18);
            outline-offset: 2px;
        }


        .footer-col .social-links a.facebook:hover {
            background: #1877F2;
            color: #fff !important;
        }

        .footer-col .social-links a.twitter:hover {
            background: #1DA1F2;
            color: #fff !important;
        }

        .footer-col .social-links a.instagram:hover {
            background: radial-gradient(circle at 30% 30%, #feda75, #f58529 30%, #d62976 60%, #962fbf 80%);
            color: #fff !important;
        }

        .footer-col .social-links a.linkedin:hover {
            background: #0A66C2;
            color: #fff !important;
        }

        .footer-divider {
            margin-top: 24px;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.02), transparent);
        }

        .footer-bottom {
            width: 92%;
            max-width: var(--max-width);
            margin: 20px auto 0;
            padding-top: 20px;
            color: var(--muted);
            font-size: 14px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 8px;
        }

        /* developer link */
        .developer-credit a {
            color: var(--gold);
            text-decoration: none;
            font-weight: 600;
        }

        .developer-credit a:hover {
            color: #fbe3a8;
            text-decoration: underline;
        }

        /* small helpers */
        .muted-small {
            color: var(--muted);
            font-size: 13px;
        }

        /* ---------- Footer responsiveness ---------- */
        @media (max-width:1100px) {
            .footer-grid {
                grid-template-columns: 1fr 1fr 1fr;
            }

            /* collapse left text into first column and move others below — keep layout balanced */
            .footer-grid .footer-col:nth-child(1) {
                grid-column: 1 / 2;
            }

            .footer-grid .footer-col:nth-child(2) {
                grid-column: 2 / 3;
            }

            .footer-grid .footer-col:nth-child(3) {
                grid-column: 3 / 4;
            }

            .footer-grid .footer-col:nth-child(4) {
                grid-column: 1 / 4;
            }

            /* contact goes full width below on medium screens */
        }

        @media (max-width:880px) {
            .footer-grid {
                grid-template-columns: 1fr 1fr;
                gap: 24px;
            }

            .footer-grid .footer-col:nth-child(1) {
                grid-column: 1 / 3;
            }

            /* about spans full width */
            .footer-grid .footer-col:nth-child(4) {
                grid-column: 1 / 3;
            }

            /* contact spans full width */
            .footer-grid .footer-col:nth-child(2),
            .footer-grid .footer-col:nth-child(3) {
                grid-column: auto;
            }

            footer {
                padding: 48px 0 20px;
            }

            .footer-bottom {
                padding-top: 16px;
                margin-top: 16px;
            }
        }

        @media (max-width:560px) {
            .footer-grid {
                grid-template-columns: 1fr;
                gap: 18px;
                padding: 0 12px;
            }

            .footer-grid .footer-col {
                grid-column: auto;
            }

            .footer-grid .footer-col:nth-child(1) {
                order: 0;
            }

            .footer-grid .footer-col:nth-child(4) {
                order: 1;
            }

            .footer-grid .footer-col:nth-child(2) {
                order: 2;
            }

            .footer-grid .footer-col:nth-child(3) {
                order: 3;
            }

            .footer-bottom {
                padding-top: 12px;
                margin-top: 12px;
                font-size: 13px;
            }

            .social-links a {
                width: 36px;
                height: 36px;
                font-size: 13px;
            }
        }

        /* ---------- Rest of responsive rules (unchanged) ---------- */
        @media (max-width: 980px) {
            .slide-inner {
                padding: 42px 6%;
            }

            .banner-slider {
                height: 64vh;
                min-height: 420px;
            }

            .hero-image-wrap {
                width: 46%;
            }
        }

        @media (max-width: 780px) {
            nav {
                display: none;
            }

            .mobile-menu {
                display: block;
            }

            .hero-image-wrap {
                display: none;
            }

            .banner-slider {
                height: 62vh;
                min-height: 380px;
            }

            .slide h1 {
                font-size: 28px;
            }

            .slide p {
                font-size: 15px;
            }
        }

        @media (max-width:420px) {
            .container {
                padding: 18px;
            }

            .cta-button {
                padding: 10px 14px;
            }

            .slide h1 {
                font-size: 22px;
            }
        }

        /* small utility */
        .muted-small {
            color: var(--muted);
            font-size: 13px;
        }
    </style>
<style>
        /* Desktop/desktop-nav */
        nav.primary-nav {
            display: flex;
            gap: 18px;
            align-items: center;
        }

        /* mobile button - hidden on desktop */
        .mobile-menu {
            display: none;
            background: transparent;
            border: 0;
            color: inherit;
            font-size: 20px;
        }

        /* mobile panel (hidden by default) */
        .mobile-nav-panel {
            display: none;
            position: absolute;
            right: 16px;
            top: calc(100% + 8px);
            min-width: 200px;
            background: rgba(11, 15, 20, 0.98);
            border-radius: 12px;
            padding: 12px;
            box-shadow: 0 18px 40px rgba(2, 6, 23, 0.45);
            border: 1px solid rgba(255, 255, 255, 0.03);
            z-index: 9999;
            flex-direction: column;
            gap: 8px;
            transform-origin: top right;
            animation: slideDown .16s ease;
        }

        .mobile-nav-panel a.mobile-link {
            color: #e6eef6;
            padding: 10px 12px;
            border-radius: 8px;
            text-decoration: none;
            display: block;
            font-weight: 600;
        }

        .mobile-nav-panel a.mobile-link:hover {
            background: rgba(255, 255, 255, 0.02);
            color: #fff;
        }

        .mobile-nav-panel a.mobile-link.active {
            color: blue !important;
            background: rgba(249, 168, 38, 0.06);
        }

        /* open state */
        .mobile-nav-panel.open {
            display: flex;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-6px) scale(.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Responsive rules */
        @media (max-width: 780px) {
            nav.primary-nav {
                display: none !important;
            }

            /* Hide desktop nav on small screens */
            .mobile-menu {
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }
        }

        nav.primary-nav a.active {
            color: blue !important;
            background: rgba(26, 75, 140, 0.06);
        }
    </style>

<body>


<button id="installBtn" class="pwa-install-btn">
    <i class="fas fa-download"></i> Install App
</button>
<style>
.pwa-install-btn {
    position: fixed;
    top: 15px;
    right: 15px;
    background: linear-gradient(135deg, #0a5cff, #0040ff);
    color: #fff;
    border: none;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    cursor: pointer;
    z-index: 9999;
}

.pwa-install-btn i {
    margin-right: 6px;
}

@media (max-width: 768px) {
    .pwa-install-btn {
        font-size: 13px;
        padding: 9px 12px;
    }
}
</style>
<script>
let deferredPrompt = null;
const installBtn = document.getElementById('installBtn');

// Check if app already installed (standalone mode)
if (window.matchMedia('(display-mode: standalone)').matches) {
    installBtn.style.display = 'none';
}

// Capture install availability
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
});

// Button click logic
installBtn.addEventListener('click', async () => {

    // If already installed
    if (window.matchMedia('(display-mode: standalone)').matches) {
        installBtn.style.display = 'none';
        return;
    }

    // Install possible
    if (deferredPrompt) {
        deferredPrompt.prompt();
        const result = await deferredPrompt.userChoice;

        if (result.outcome === 'accepted') {
            installBtn.style.display = 'none';
        }

        deferredPrompt = null;
    } else {
        alert('Install option not available. Use Chrome browser & HTTPS.');
    }
});
</script>




























 <header>
        <div class="container header-row" style="position:relative;">
            <div class="logo" style="display:flex;align-items:center;gap:12px;">
                <img src="public/images/logo.png" alt="School Management Softwere Logo" onerror="this.style.display='none'">
                <div>
                    <div class="logo-text">School <span>India Junior</span></div>
                    <div class="tagline">Nurturing curious, confident learners</div>
                </div>
            </div>

            <!-- Desktop nav -->
            <nav class="primary-nav" aria-label="Main navigation">
                <a href="index.php" class="active">Home</a>

                <a href="event.php">Events</a>
                <a href="admission.php">Admissions</a>
                <a href="login.php" class="btn-primary" style="padding:8px 12px; text-decoration:none;">Login</a>
            </nav>

            <!-- Mobile toggle -->
            <button class="mobile-menu" aria-label="Open menu" aria-expanded="false" type="button">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>

            <!-- Mobile nav panel (hidden by default, toggled by JS) -->
            <div class="mobile-nav-panel" aria-hidden="true">
                <a href="index.php" class="mobile-link active">Home</a>
                <a href="event.php" class="mobile-link">Events</a>
                <a href="admission.php" class="mobile-link ">Admissions</a>
                <a href="login.php" class="mobile-link btn-primary" style="margin-top:8px; display:inline-block; padding:8px 12px;">Login</a>
            </div>
        </div>
    </header>


    <!-- Banner Slider (dynamic PHP block kept intact) -->
    <?php
    include __DIR__ . '/config/database.php';
    $sql = "SELECT id, title, description, image FROM hero_sliders ORDER BY  id ASC LIMIT 3";
    $res = $conn->query($sql);

    $slides = [];
    if ($res && $res->num_rows > 0) {
        while ($r = $res->fetch_assoc()) {
            $slides[] = $r;
        }
    }
    $fallbackImg = '/mnt/data/3f93a5b6-3702-47e5-81d0-92fd16820a60.png';

    if (empty($slides)) {
        $slides = [
            [
                'title' => 'Shaping Young Minds for a Brighter Future',
                'description' => "At School Management Softwere, we provide a nurturing environment where children discover their potential, develop critical thinking skills, and build character for lifelong success.",
                'image' => $fallbackImg
            ],
            [
                'title' => 'Excellence in Education Since 2005',
                'description' => "With over 15 years of experience, we've perfected the art of balancing academic rigor with creative exploration.",
                'image' => $fallbackImg
            ],
            [
                'title' => 'State-of-the-Art Learning Facilities',
                'description' => "Our campus features modern classrooms, science labs, sports facilities, and creative spaces designed to inspire young minds.",
                'image' => $fallbackImg
            ],
        ];
    }
    ?>

    <section class="banner-slider" id="banner-slider">
        <?php foreach ($slides as $i => $s):
            $active = $i === 0 ? ' active' : '';
            $img = '';
            if (!empty($s['image'])) {
                $imgCandidate = $s['image'];
                if (strpos($imgCandidate, '/') === false && strpos($imgCandidate, 'http') !== 0) {
                    $img = 'uploads/hero/' . $imgCandidate;
                } else {
                    $img = $imgCandidate;
                }
            } else {
                $img = $fallbackImg;
            }

            $imgEsc = htmlspecialchars($img);
            $titleEsc = htmlspecialchars($s['title']);
            $descEsc = nl2br(htmlspecialchars($s['description']));
        ?>
            <div class="slide<?= $active ?>" style="background-image: url('<?= $imgEsc ?>');">
                <div class="slide-inner">
                    <span class="kicker">Admissions Open</span>
                    <h1><?= $titleEsc ?></h1>
                    <p><?= $descEsc ?></p>
                    <div class="hero-buttons">
                        <a href="#" class="btn-primary">Explore Programs</a>
                        <a href="#" class="btn-secondary">Schedule a Visit</a>
                    </div>
                </div>

                <div class="hero-image-wrap" aria-hidden="true">
                    <div class="hero-image" style="background-image: url('<?= $imgEsc ?>');"></div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="slider-controls">
            <div class="arrow prev" aria-label="previous"><i class="fas fa-chevron-left"></i></div>
            <div class="arrow next" aria-label="next"><i class="fas fa-chevron-right"></i></div>
        </div>

        <div class="dots">
            <?php for ($d = 0; $d < count($slides); $d++): ?>
                <div class="dot<?= $d === 0 ? ' active' : '' ?>" data-index="<?= $d ?>" aria-label="slide <?= $d + 1 ?>"></div>
            <?php endfor; ?>
        </div>
    </section>
    <!-- Notice Section -->
    <?php
    include __DIR__ . '/config/database.php'; // Your mysqli connection


    $sql = "SELECT id, title, content, start_date, end_date, created_at
        FROM notices
        WHERE (start_date IS NULL OR start_date <= NOW())
          AND (end_date IS NULL OR end_date >= NOW())
        ORDER BY created_at DESC";

    $result = $conn->query($sql);
    $notices = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $notices[] = $row;
        }
    }
    ?>

    <section class="notice-section">
        <div class="container">
            <div class="section-title">
                <h2>Latest Notices</h2>
                <p class="muted-small">Important updates for parents & students — shows 3 at a time and scrolls automatically.</p>
            </div>

            <div class="notice-frame" id="noticeFrame">
                <ul class="notice-list" id="noticeList">
                    <?php if (!empty($notices)): ?>
                        <?php foreach ($notices as $n): ?>
                            <li class="notice-item">
                                <strong><?= htmlspecialchars($n['title']) ?></strong><br>
                                <?= nl2br(htmlspecialchars($n['content'])) ?>

                                <?php if ($n['start_date'] || $n['end_date']): ?>
                                    <div class="notice-dates" style="font-size: 0.85rem; color: #6b7280; margin-top: 0.25rem;">
                                        <?php if ($n['start_date']): ?>
                                            <div>Start: <?= date('d M Y, H:i', strtotime($n['start_date'])) ?></div>
                                        <?php endif; ?>
                                        <?php if ($n['end_date']): ?>
                                            <div>End: <?= date('d M Y, H:i', strtotime($n['end_date'])) ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="notice-item">No notices available right now.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="section-title">
                <h2>Why Choose School Management Softwere?</h2>
                <p>We are committed to providing exceptional education that prepares students for the challenges of tomorrow.</p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3>Experienced Faculty</h3>
                    <p>Our dedicated teachers are experts in early childhood education with years of experience.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-flask"></i>
                    </div>
                    <h3>Modern Facilities</h3>
                    <p>State-of-the-art classrooms, science labs, and recreational areas designed for young learners.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Holistic Development</h3>
                    <p>We focus on academic excellence along with physical, emotional, and social growth.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Programs Section -->
    <section class="programs">
        <div class="container">
            <div class="section-title">
                <h2>Our Educational Programs</h2>
                <p>We offer a comprehensive curriculum designed to meet the unique needs of each age group.</p>
            </div>

            <div class="programs-grid">
                <div class="program-card">
                    <div class="program-image" style="background-image: url('https://images.unsplash.com/photo-1588072432836-e10032774350?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1472&q=80');"></div>
                    <div class="program-content">
                        <h3>Kindergarten</h3>
                        <p>Our play-based learning approach helps young children develop social, emotional, and cognitive skills.</p>
                        <a href="#" class="program-link muted-small">Learn More <i class="fas fa-arrow-right" style="margin-left:6px"></i></a>
                    </div>
                </div>

                <div class="program-card">
                    <div class="program-image" style="background-image: url('https://images.unsplash.com/photo-1588072432836-e10032774350?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1472&q=80');"></div>
                    <div class="program-content">
                        <h3>Elementary School</h3>
                        <p>Building strong foundations in literacy, numeracy, and scientific thinking through engaging activities.</p>
                        <a href="#" class="program-link muted-small">Learn More <i class="fas fa-arrow-right" style="margin-left:6px"></i></a>
                    </div>
                </div>

                <div class="program-card">
                    <div class="program-image" style="background-image: url('https://images.unsplash.com/photo-1577896851231-70ef18881754?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80');"></div>
                    <div class="program-content">
                        <h3>Middle School</h3>
                        <p>Preparing students for higher education with specialized subjects and critical thinking development.</p>
                        <a href="#" class="program-link muted-small">Learn More <i class="fas fa-arrow-right" style="margin-left:6px"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
            <div class="section-title">
                <h2>What Parents Say</h2>
                <p>Hear from our community about their experiences with School Management Softwere.</p>
            </div>

            <div class="testimonials-container">
                <div class="testimonial-card">
                    <p class="testimonial-text">"School Management Softwere has been a blessing for our daughter. She's not only excelling academically but has also developed incredible confidence and social skills."</p>
                    <div style="display:flex;align-items:center;gap:12px">
                        <div class="author-avatar">PS</div>
                        <div>
                            <h4 style="margin:0;color:var(--accent);">Priya Sharma</h4>
                            <div class="muted-small">Parent of Grade 3 Student</div>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <p class="testimonial-text">"The teachers at School Management Softwere go above and beyond to ensure each child receives personalized attention. Our son loves going to school every day!"</p>
                    <div style="display:flex;align-items:center;gap:12px">
                        <div class="author-avatar">RK</div>
                        <div>
                            <h4 style="margin:0;color:var(--accent);">Rahul Kumar</h4>
                            <div class="muted-small">Parent of Kindergarten Student</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Ready to Join Our School Community?</h2>
            <p>Take the first step toward providing your child with an exceptional educational experience. Contact us today to learn more about admissions and schedule a campus tour.</p>
            <a href="#" class="btn-primary">Enroll Now</a>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3>School Management Softwere</h3>
                    <p>Providing quality education for young minds since 2005. Our mission is to nurture curious, confident, and compassionate learners.</p>
                    <div style="margin-top:10px">
                        <a class="muted-small" href="#">Privacy Policy</a>
                    </div>
                    <div style="margin-top:12px; display:flex; gap:20px;">
                        <a class="social-links kicker" href="#"><i class="fab fa-facebook-f"></i></a>
                        <a class="social-links kicker" href="#"><i class="fab fa-twitter"></i></a>
                        <a class="social-links kicker" href="#"><i class="fab fa-instagram"></i></a>
                        <a class="social-links kicker" href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                <div class="footer-col">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="#">Home</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Academic Programs</a></li>
                        <li><a href="#">Admissions</a></li>
                        <li><a href="#">Faculty</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h3>Programs</h3>
                    <ul>
                        <li><a href="#">Kindergarten</a></li>
                        <li><a href="#">Elementary School</a></li>
                        <li><a href="#">Middle School</a></li>
                        <li><a href="#">After School Programs</a></li>
                        <li><a href="#">Summer Camp</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h3>Contact Us</h3>
                    <ul>
                        <li><i class="fas fa-map-marker-alt"></i> 123 Education Lane, Mumbai, India</li>
                        <li><i class="fas fa-phone"></i> +91 98765 43210</li>
                        <li><i class="fas fa-envelope"></i> info@schoolindiajunior.edu</li>
                        <li><i class="fas fa-clock"></i> Mon-Fri: 8:00 AM - 4:00 PM</li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2023 School Management Softwere. All Rights Reserved.</p>
                <div class="developer-credit">
                    <p>Designed and Developed by <a href="https://elexa.in" target="_blank" style="color:var(--gold);text-decoration:none">Elexa Technologies</a></p>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Mobile Menu Toggle
        document.querySelector('.mobile-menu').addEventListener('click', function() {
            document.querySelector('nav').classList.toggle('active');
            // simple mobile nav fallback: toggle a class that shows a minimal menu
            if (document.querySelector('nav').classList.contains('active')) {
                document.querySelector('nav').style.display = 'flex';
                document.querySelector('nav').style.flexDirection = 'column';
                document.querySelector('nav').style.position = 'fixed';
                document.querySelector('nav').style.top = '90px';
                document.querySelector('nav').style.right = '18px';
                document.querySelector('nav').style.background = 'linear-gradient(180deg, rgba(15,19,24,0.95), rgba(15,19,24,0.95))';
                document.querySelector('nav').style.padding = '18px';
                document.querySelector('nav').style.borderRadius = '12px';
                document.querySelector('nav').style.boxShadow = 'var(--soft-shadow)';
            } else {
                document.querySelector('nav').style.display = '';
                document.querySelector('nav').style.position = '';
                document.querySelector('nav').style.top = '';
                document.querySelector('nav').style.right = '';
                document.querySelector('nav').style.padding = '';
                document.querySelector('nav').style.borderRadius = '';
            }
        });

        // Close mobile menu when clicking on a link
        document.querySelectorAll('nav a').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 780) {
                    document.querySelector('nav').classList.remove('active');
                    document.querySelector('nav').style.display = '';
                }
            });
        });

        // Banner Slider Functionality (keeps your server-driven dynamic content intact)
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.dot');
        const prevBtn = document.querySelector('.arrow.prev');
        const nextBtn = document.querySelector('.arrow.next');
        let currentSlide = 0;
        let slideInterval;

        function showSlide(n) {
            slides.forEach(s => s.classList.remove('active'));
            dots.forEach(d => d.classList.remove('active'));
            currentSlide = (n + slides.length) % slides.length;
            slides[currentSlide].classList.add('active');
            dots[currentSlide].classList.add('active');
        }

        function nextSlide() {
            showSlide(currentSlide + 1);
        }

        function prevSlide() {
            showSlide(currentSlide - 1);
        }

        function startSlider() {
            slideInterval = setInterval(nextSlide, 5200);
        }

        function resetSlider() {
            clearInterval(slideInterval);
            startSlider();
        }

        nextBtn.addEventListener('click', () => {
            nextSlide();
            resetSlider();
        });
        prevBtn.addEventListener('click', () => {
            prevSlide();
            resetSlider();
        });

        dots.forEach((dot, idx) => {
            dot.addEventListener('click', () => {
                showSlide(idx);
                resetSlider();
            });
        });

        // small accessibility: allow left/right arrow keys
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                prevSlide();
                resetSlider();
            }
            if (e.key === 'ArrowRight') {
                nextSlide();
                resetSlider();
            }
        });

        // start
        startSlider();
    </script>


    <script>
        (function() {
            const frame = document.querySelector('.notice-frame');
            if (!frame) return;
            const list = frame.querySelector('.notice-list');
            const rawItems = Array.from(list.children);
            if (!rawItems.length) return;

            const visibleCount = 3;
            const visibleDelay = 3000;
            const animDuration = 2000;

            const groups = [];
            for (let i = 0; i < rawItems.length; i += visibleCount) {
                groups.push(rawItems.slice(i, i + visibleCount));
            }
            if (groups.length && groups[groups.length - 1].length < visibleCount) {
                const needed = visibleCount - groups[groups.length - 1].length;
                for (let i = 0; i < needed; i++) {
                    groups[groups.length - 1].push(rawItems[(i) % rawItems.length].cloneNode(true));
                }
            }

            list.innerHTML = '';
            const groupElems = [];
            groups.forEach(g => {
                const wrapper = document.createElement('div');
                wrapper.className = 'notice-group';
                g.forEach(li => wrapper.appendChild(li.cloneNode(true)));
                groupElems.push(wrapper);
                list.appendChild(wrapper);
            });
            groupElems.forEach(g => list.appendChild(g.cloneNode(true)));

            function computeFrameMetrics() {
                const firstItem = list.querySelector('.notice-group .notice-item');
                const itemHeight = firstItem ? Math.round(firstItem.getBoundingClientRect().height) : 56;
                const frameHeight = itemHeight * visibleCount;
                frame.style.height = frameHeight + 'px';
                return {
                    itemHeight,
                    frameHeight
                };
            }

            let {
                itemHeight,
                frameHeight
            } = computeFrameMetrics();

            let resizeTimeout;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(() => {
                    const metrics = computeFrameMetrics();
                    itemHeight = metrics.itemHeight;
                    frameHeight = metrics.frameHeight;
                    list.style.transition = 'none';
                    list.style.transform = `translateY(${-currentIndex * frameHeight}px)`;
                    void list.offsetWidth;
                    list.style.transition = '';
                    markVisible(currentIndex % groupCount);
                }, 150);
            });

            const groupCount = groupElems.length;
            let currentIndex = 0;

            function markVisible(idx) {
                list.querySelectorAll('.notice-item.visible').forEach(n => n.classList.remove('visible'));
                const groupsInDOM = Array.from(list.querySelectorAll('.notice-group'));
                const target = groupsInDOM.find((g, i) => i % groupCount === idx);
                if (!target) return;
                target.querySelectorAll('.notice-item').forEach(it => it.classList.add('visible'));
            }

            markVisible(0);

            function advance() {
                currentIndex++;
                list.style.transition = `transform ${animDuration}ms cubic-bezier(.2,.9,.2,1)`;
                list.style.transform = `translateY(${-currentIndex * frameHeight}px)`;
                const visibleIdx = currentIndex % groupCount;

                setTimeout(() => {
                    if (currentIndex >= groupCount) {
                        list.style.transition = 'none';
                        currentIndex = 0;
                        list.style.transform = `translateY(0px)`;
                        void list.offsetWidth;
                    }
                    markVisible(visibleIdx);
                }, animDuration + 20);
            }

            let ticker = null;

            function startTicker() {
                if (ticker) return;
                ticker = setInterval(advance, visibleDelay + animDuration);
            }

            function stopTicker() {
                if (!ticker) return;
                clearInterval(ticker);
                ticker = null;
            }

            frame.addEventListener('mouseenter', stopTicker);
            frame.addEventListener('mouseleave', startTicker);
            frame.addEventListener('focusin', stopTicker);
            frame.addEventListener('focusout', startTicker);

            setTimeout(() => {
                ({
                    itemHeight,
                    frameHeight
                } = computeFrameMetrics());
                list.style.transform = 'translateY(0)';
                markVisible(0);
                startTicker();
            }, 150);
        })();
    </script>
    <script>
        (function() {
            const mobileBtn = document.querySelector('.mobile-menu');
            const mobilePanel = document.querySelector('.mobile-nav-panel');

            if (!mobileBtn || !mobilePanel) return;

            function closePanel() {
                mobilePanel.classList.remove('open');
                mobilePanel.setAttribute('aria-hidden', 'true');
                mobileBtn.setAttribute('aria-expanded', 'false');
            }

            function openPanel() {
                mobilePanel.classList.add('open');
                mobilePanel.setAttribute('aria-hidden', 'false');
                mobileBtn.setAttribute('aria-expanded', 'true');
            }

            mobileBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                if (mobilePanel.classList.contains('open')) closePanel();
                else openPanel();
            });

            // close when clicking outside
            document.addEventListener('click', function(e) {
                if (!mobilePanel.contains(e.target) && !mobileBtn.contains(e.target)) {
                    closePanel();
                }
            });

            // close when pressing Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closePanel();
            });

            // close when a mobile link is clicked (so menu hides on navigation)
            mobilePanel.querySelectorAll('a.mobile-link').forEach(a => {
                a.addEventListener('click', () => closePanel());
            });
        })();
    </script>

</body>

</html>