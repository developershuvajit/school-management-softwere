<!-- admission.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>School India Junior - Admission Enquiry</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&family=Merriweather:wght@300;400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary: #1a4b8c;
            --muted: #6c757d;
            --card-radius: 12px;
            --card-shadow: 0 12px 40px rgba(2, 6, 23, 0.08);
        }

        /* Page background: white booking engine style */
        html,
        body {
            height: 100%;
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, Arial;
            background: #ffffff;
            /* white page */
            color: #111827;
        }

        /* Keep your header/navbar styles (from original theme) */
        header {
            position: sticky;
            top: 0;
            z-index: 1100;
            background: #0b0f14;
            /* dark header preserved */
            color: #e6eef6;
        }

        .container.header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 24px;
        }

        .logo img {
            height: 44px;
            border-radius: 8px;
        }

        nav.primary-nav a {
            color: #c7cdd6;
            text-decoration: none;
            margin-right: 8px;
            padding: 8px 10px;
            border-radius: 8px;
        }

        nav.primary-nav a.active {
            color: blue !important;
            background: rgba(26, 75, 140, 0.06);
        }

        /* Main: center the form card exactly like booking form */
        main.page-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 20px;
            min-height: calc(100vh - 72px);
            background: white;
        }

        /* Form card: white, minimal, centered */
        .admission-card {
            width: 100%;
            max-width: 620px;
            background: #ffffff;
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            border: 1px solid rgba(15, 23, 36, 0.03);
        }

        .admission-body {
            padding: 28px;
        }

        /* Title inside card (neutral) */
        .card-title {
            font-size: 20px;
            font-weight: 700;
            color: #0b0f14;
            margin-bottom: 6px;
        }

        .card-sub {
            color: var(--muted);
            font-size: 14px;
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #0b0f14;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px;
            border-radius: 8px;
            border: 1px solid #e6e9ec;
            box-shadow: none;
            font-size: 15px;
        }

        .form-control:focus {
            outline: none;
            box-shadow: 0 6px 18px rgba(26, 75, 140, 0.06);
            border-color: var(--primary);
        }

        .form-help {
            font-size: 13px;
            color: #6b7280;
            margin-top: 6px;
        }

        .submit-row {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-top: 6px;
        }

        .btn-primary-action {
            background: linear-gradient(90deg, var(--primary), #2a5da8);
            color: #fff;
            border: none;
            padding: 12px 18px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-secondary-ghost {
            background: transparent;
            border: 1px solid #e6e9ec;
            padding: 10px 14px;
            border-radius: 10px;
            color: #374151;
            cursor: pointer;
        }

        .muted-small {
            font-size: 13px;
            color: #6b7280;
            margin-top: 12px;
        }

        @media (max-width:540px) {
            .admission-body {
                padding: 18px;
            }

            .card-title {
                font-size: 18px;
            }
        }
    </style>
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
    </style>
</head>

<body>
    <!-- Keep the existing dark header/navbar -->
    <header class=" bg-dark">
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

                <a href="event.php">Events</a>
                <a href="admission.php" class="active">Admissions</a>
                <a href="login.php" class="btn-primary" style="padding:8px 12px; text-decoration:none;">Login</a>
            </nav>

            <!-- Mobile toggle -->
            <button class="mobile-menu" aria-label="Open menu" aria-expanded="false" type="button">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>

            <!-- Mobile nav panel (hidden by default, toggled by JS) -->
            <div class="mobile-nav-panel" aria-hidden="true">
                <a href="index.php" class="mobile-link">Home</a>

                <a href="event.php" class="mobile-link">Events</a>
                <a href="admission.php" class="mobile-link active">Admissions</a>
                <a href="login.php" class="mobile-link btn-primary" style="margin-top:8px; display:inline-block; padding:8px 12px;">Login</a>
            </div>
        </div>
    </header>

    <!-- Main: white booking-like form canvas -->
    <main class="page-wrap">
        <div class="admission-card" role="main" aria-labelledby="admissionTitle">
            <div class="admission-body">
                <!-- Neutral header (no blue strip) -->
                <div>
                    <h2 id="admissionTitle" class="card-title">Admission Enquiry</h2>
                    <div class="card-sub">Fill this short form and our admission team will get in touch with you.</div>
                </div>

                <!-- Form (same fields) -->
                <form id="admissionForm" method="post" action="" novalidate>
                    <div class="mb-3">
                        <label class="form-label" for="name"><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Rahul Roy" required minlength="2" maxlength="120" autocomplete="name" />
                        <div class="form-help">Enter full name as you'd like us to address you.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="mobile"><i class="fas fa-phone"></i> Mobile Number</label>
                        <input type="tel" id="mobile" name="mobile" class="form-control" placeholder="10-digit mobile number" required pattern="[0-9]{10}" maxlength="10" inputmode="numeric" autocomplete="tel" />
                        <div class="form-help">Enter a 10-digit mobile number (numbers only).</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="place"><i class="fas fa-map-marker-alt"></i> Place</label>
                        <input type="text" id="place" name="place" class="form-control" placeholder="Town / City" required maxlength="150" autocomplete="address-level2" />
                        <div class="form-help">City / town or locality.</div>
                    </div>

                    <div class="submit-row">
                        <button type="submit" class="btn-primary-action" id="submitBtn">Submit Admission Enquiry</button>
                        <button type="reset" class="btn-secondary-ghost" onclick="document.getElementById('name').focus()">Clear</button>
                    </div>

                    <div class="muted-small">After your submission, our team will get in touch with you soon.</div>
                </form>
            </div>
        </div>
    </main>

    <!-- Client validation + SweetAlert -->
    <script>
        (function() {
            const form = document.getElementById('admissionForm');

            function showError(message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops',
                    text: message
                });
            }

            function showSuccess() {
                Swal.fire({
                    icon: 'success',
                    title: 'Submitted',
                    text: 'Thanks — our admission team will contact you soon.',
                    timer: 1600,
                    showConfirmButton: false
                });
            }

            function validMobile(val) {
                return /^[0-9]{10}$/.test(val);
            }

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const name = form.name.value.trim();
                const mobile = form.mobile.value.trim();
                const place = form.place.value.trim();

                if (name.length < 2) {
                    showError('Please enter a valid name.');
                    form.name.focus();
                    return;
                }
                if (!validMobile(mobile)) {
                    showError('Please enter a valid 10-digit mobile number.');
                    form.mobile.focus();
                    return;
                }
                if (place.length < 2) {
                    showError('Please enter your place/city.');
                    form.place.focus();
                    return;
                }

                showSuccess();
                setTimeout(() => form.submit(), 700);
            });
        })();
    </script>
    <!-- Add this JS after your other scripts (before </body>) -->
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>