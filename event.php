<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School India Junior - Quality Education for Young Minds</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&family=Merriweather:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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

        /* .program-card {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.04);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.01), rgba(255, 255, 255, 0.005));
            box-shadow: 0 10px 30px rgba(2, 6, 23, 0.5);
            transition: transform .22s ease;
        } */

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
        .events-blog {
            padding: 48px 0;
        }

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }

        /* Card */
        .event-card {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.01), rgba(255, 255, 255, 0.005));
            border: 1px solid var(--glass-border);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: var(--soft-shadow);
            transition: transform .18s ease, box-shadow .18s ease;
            display: flex;
            flex-direction: column;
            min-height: 340px;
        }

        .event-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(2, 6, 23, 0.65);
        }

        /* Image */
        .event-image {
            height: 180px;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            border-bottom: 1px solid rgba(255, 255, 255, 0.02);
        }

        /* Content */
        .event-content {
            padding: 16px 18px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            color: var(--text);
            flex: 1;
        }

        .event-content h3 {
            margin: 0;
            font-size: 1.05rem;
            color: var(--accent);
            line-height: 1.15;
        }

        .event-date {
            color: var(--muted);
            font-size: 0.9rem;
        }

        /* excerpt */
        .event-excerpt {
            color: var(--text);
            opacity: 0.95;
            margin: 0;
            line-height: 1.6;
            color: var(--muted);
            margin-top: 6px;
        }

        /* link */
        .event-link {
            margin-top: auto;
            text-decoration: none;
            color: var(--accent);
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .event-link:hover {
            text-decoration: underline;
            color: var(--accent-2);
        }

        /* responsive */
        @media (max-width: 780px) {
            .events-grid {
                gap: 16px;
            }

            .event-image {
                height: 160px;
            }
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
</head>

<body>
    <header>
        <div class="container header-row" style="position:relative;">
            <div class="logo" style="display:flex;align-items:center;gap:12px;">
                <img src="public/images/logo.png" alt="School India Junior Logo" onerror="this.style.display='none'">
                <div>
                    <div class="logo-text">School <span>India Junior</span></div>
                    <div class="tagline">Nurturing curious, confident learners</div>
                </div>
            </div>

            <!-- Desktop nav -->
            <nav class="primary-nav" aria-label="Main navigation">
                <a href="index.php">Home</a>
           
                <a href="event.php" class="active">Events</a>
                <a href="admission.php">Admissions</a>
                <a href="login.php" class="btn-primary" style="padding:8px 12px; text-decoration:none;">Login</a>
            </nav>

            <!-- Mobile toggle -->
            <button class="mobile-menu" aria-label="Open menu" aria-expanded="false" type="button">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>

            <!-- Mobile nav panel (hidden by default, toggled by JS) -->
            <div class="mobile-nav-panel" aria-hidden="true">
                <a href="index.php" class="mobile-link">Home</a>
                <a href="event.php" class="mobile-link active">Events</a>
                <a href="admission.php" class="mobile-link ">Admissions</a>
                <a href="login.php" class="mobile-link btn-primary" style="margin-top:8px; display:inline-block; padding:8px 12px;">Login</a>
            </div>
        </div>
    </header>
    <!-- Programs Section -->
    <section class="programs">
        <div class="container">
            <div class="section-title text-center">
                <h2>Our Educational Programs</h2>
                <p>We offer a comprehensive curriculum designed to meet the unique needs of each age group.</p>
            </div>
            <?php
            include __DIR__ . '/config/database.php';

            function esc($v)
            {
                return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
            }

            $sql = "SELECT * FROM events
        WHERE status IS NULL OR status = 1
        ORDER BY created_at DESC
        LIMIT 12";
            $res = $conn->query($sql);

            $programs = [];
            if ($res && $res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                    $programs[] = $row;
                }
            }


            $placeholder = 'https://via.placeholder.com/1200x700?text=Program+Image';
            ?>
            <div class="row">
                <?php if (empty($programs)): ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center muted-small">No programs found.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($programs as $p):
                        $id = (int)$p['id'];
                        $title = esc($p['title']);
                        $descRaw = $p['description'] ?? '';

                        $excerptText = mb_strlen($descRaw) > 220 ? mb_substr($descRaw, 0, 220) . '...' : $descRaw;
                        $excerptHtml = nl2br(esc($excerptText));


                        $rawImg = $p['image'] ?? '';
                        $rawDate = $p['event_date'] ?? null;

                        $dateFormatted = $rawDate
                            ? date('l, j M Y', strtotime($rawDate))
                            : null;
                    ?>
                        <div class="col-12 col-md-6 col-lg-4 mb-4 d-flex">
                            <article class="program-card h-100 w-100 shadow-lg">
                                <div class="program-image" style="background-image: url('<?= "./" . $rawImg ?>'); height: 220px; background-size: cover; background-position: center;"></div>

                                <div class="program-content p-0 pt-4 d-flex flex-column h-100">
                                    <?php if (!empty($dateFormatted)): ?>
                                        <p class="muted-small mb-2 text-light">
                                            <?= $dateFormatted ?>
                                        </p>
                                    <?php endif; ?>
                                    <h3 class="mb-2"><?= $title ?></h3>
                                    <div class="mb-3 text-white" style="line-height:1.45;"><?= $excerptHtml ?></div>


                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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
                    <h3>School India Junior</h3>
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
                <p>&copy; 2023 School India Junior. All Rights Reserved.</p>
                <div class="developer-credit">
                    <p>Designed and Developed by <a href="https://elexa.in" target="_blank" style="color:var(--gold);text-decoration:none">Elexa Technologies</a></p>
                </div>
            </div>
        </div>
    </footer>

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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>