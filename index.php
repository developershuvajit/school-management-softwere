<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management Software | Smart School ERP</title>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- PWA Meta -->
    <meta name="theme-color" content="#0a5cff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="School Management Software">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" href="public/images/logo.png">
    <link rel="apple-touch-icon" href="public/images/logo.png">

    <style>
        :root {
            --primary: #0a5cff;
            --primary-light: #3b82f6;
            --accent: #10b981;
            --gold: #f59e0b;
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #1e2937;
            --muted: #64748b;
            --border: #e2e8f0;
            --radius: 16px;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            --soft-shadow: 0 4px 20px rgba(10, 92, 255, 0.08);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            width: 92%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 16px;
        }

        /* ==================== HEADER ==================== */
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 1200;
            border-bottom: 1px solid var(--border);
            padding: 16px 0;
        }

        .header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--text);
        }

        .logo img {
            height: 54px;
            border-radius: 10px;
            object-fit: cover;
        }

        .logo-text {
            font-weight: 700;
            font-size: 23px;
            letter-spacing: -0.4px;
        }

        .logo-text span {
            color: var(--primary);
        }

        .tagline {
            font-size: 13.5px;
            color: var(--muted);
            margin-top: -2px;
        }

        nav.primary-nav {
            display: flex;
            align-items: center;
            gap: 28px;
        }

        nav a {
            color: var(--text);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        nav a:hover, nav a.active {
            color: var(--primary);
        }

        .cta-button {
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            color: white;
            padding: 11px 26px;
            border-radius: 9999px;
            font-weight: 600;
            text-decoration: none;
            box-shadow: var(--soft-shadow);
        }

        .mobile-menu {
            display: none;
            font-size: 26px;
            background: none;
            border: none;
            color: var(--text);
            cursor: pointer;
        }

        /* ==================== HERO SLIDER ==================== */
        .banner-slider {
            position: relative;
            height: 76vh;
            min-height: 540px;
            margin: 40px 0 60px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .slide {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            opacity: 0;
            transition: opacity 0.9s ease;
        }

        .slide.active {
            opacity: 1;
        }

        .slide::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(15, 23, 42, 0.68) 25%, rgba(15, 23, 42, 0.35));
        }

        .slide-inner {
            position: relative;
            z-index: 2;
            max-width: 680px;
            padding: 0 8%;
        }

        .kicker {
            background: rgba(16, 185, 129, 0.15);
            color: var(--accent);
            padding: 8px 18px;
            border-radius: 999px;
            font-size: 13.5px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 14px;
        }

        .slide h1 {
            font-family: 'Playfair Display', serif;
            font-size: 46px;
            line-height: 1.08;
            color: white;
            margin-bottom: 16px;
        }

        .slide p {
            color: #e2e8f0;
            font-size: 17.5px;
            max-width: 520px;
        }

        .hero-buttons {
            margin-top: 30px;
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: white;
            color: var(--primary);
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
        }

        .btn-secondary {
            background: transparent;
            border: 2px solid rgba(255,255,255,0.8);
            color: white;
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
        }

        .slider-controls {
            position: absolute;
            bottom: 32px;
            right: 32px;
            display: flex;
            gap: 12px;
            z-index: 10;
        }

        .arrow {
            width: 50px;
            height: 50px;
            background: rgba(255,255,255,0.95);
            color: var(--text);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.2s;
        }

        .arrow:hover {
            background: white;
            transform: scale(1.1);
        }

        .dots {
            position: absolute;
            bottom: 32px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 10;
        }

        .dot {
            width: 11px;
            height: 11px;
            border-radius: 50%;
            background: rgba(255,255,255,0.6);
            cursor: pointer;
            transition: all 0.3s;
        }

        .dot.active {
            background: white;
            transform: scale(1.4);
        }

        /* ==================== NOTICE SECTION ==================== */
        .notice-section {
            padding: 60px 0;
            background: white;
        }

        .notice-frame {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow);
            max-width: 920px;
            margin: 0 auto;
        }

        .notice-list {
            list-style: none;
        }

        .notice-item {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
            font-weight: 500;
            transition: all 0.3s;
        }

        .notice-item:last-child {
            border-bottom: none;
        }

        .notice-item.visible {
            background: linear-gradient(90deg, #fefce8, #fef9c3);
            border-left: 5px solid var(--gold);
        }

        .notice-item small {
            color: var(--muted);
            font-size: 0.9rem;
            margin-top: 6px;
            display: block;
        }

        /* ==================== SECTIONS ==================== */
        section {
            padding: 80px 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 52px;
        }

        .section-title h2 {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            color: var(--text);
            margin-bottom: 12px;
        }

        .section-title p {
            color: var(--muted);
            max-width: 720px;
            margin: 0 auto;
            font-size: 17px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 28px;
        }

        .feature-card {
            background: white;
            padding: 32px 26px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            text-align: center;
            transition: all 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-12px);
            box-shadow: var(--shadow);
        }

        .feature-icon {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin: 0 auto 20px;
        }

        .programs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 26px;
        }

        .program-card {
            background: white;
            border-radius: var(--radius);
            overflow: hidden;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            transition: all 0.3s;
        }

        .program-card:hover {
            transform: translateY(-10px);
        }

        .program-image {
            height: 210px;
            background-size: cover;
            background-position: center;
        }

        .program-content {
            padding: 24px;
        }

        .program-content h3 {
            margin-bottom: 10px;
            color: var(--primary);
        }

        /* CTA */
        .cta-section {
            background: linear-gradient(135deg, #0a5cff, #10b981);
            color: white;
            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            margin: 40px auto;
            max-width: 1100px;
        }

        /* Footer */
        footer {
            background: #0f172a;
            color: #cbd5e1;
            padding: 80px 0 40px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.2fr;
            gap: 40px;
        }

        .footer-col h3 {
            color: white;
            margin-bottom: 20px;
            font-size: 18px;
        }

        .footer-col a {
            color: #cbd5e1;
            text-decoration: none;
        }

        .footer-col a:hover {
            color: var(--accent);
        }

        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            background: rgba(255,255,255,0.08);
            border-radius: 10px;
            color: #cbd5e1;
            font-size: 18px;
            margin-right: 10px;
            transition: all 0.3s;
        }

        .social-links a:hover {
            background: var(--accent);
            color: white;
            transform: translateY(-3px);
        }

        .footer-bottom {
            text-align: center;
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid #334155;
            color: #94a3b8;
            font-size: 14.5px;
        }

        /* Responsive */
        @media (max-width: 780px) {
            .mobile-menu { display: block; }
            nav.primary-nav { display: none; }
            .banner-slider { height: 68vh; min-height: 420px; }
            .slide h1 { font-size: 34px; }
        }

        @media (max-width: 640px) {
            .footer-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

<!-- PWA Install Button -->
<button id="installBtn" class="pwa-install-btn">
    <i class="fas fa-download"></i> Install App
</button>

<style>
.pwa-install-btn {
    position: fixed;
    top: 18px;
    right: 18px;
    background: linear-gradient(135deg, #0a5cff, #0040ff);
    color: #fff;
    border: none;
    padding: 11px 18px;
    border-radius: 10px;
    font-size: 14.5px;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(0,0,0,0.25);
    cursor: pointer;
    z-index: 9999;
}
</style>

<script>
let deferredPrompt = null;
const installBtn = document.getElementById('installBtn');

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
});

installBtn.addEventListener('click', async () => {
    if (deferredPrompt) {
        deferredPrompt.prompt();
        const result = await deferredPrompt.userChoice;
        if (result.outcome === 'accepted') installBtn.style.display = 'none';
        deferredPrompt = null;
    }
});
</script>

<header>
    <div class="container header-row">
        <a href="index.php" class="logo">
            <img src="public/images/logo.png" alt="School Logo" onerror="this.style.display='none'">
            <div>
                <div class="logo-text">School <span>India</span></div>
                <div class="tagline">Smart School Management Software</div>
            </div>
        </a>

        <!-- Desktop Navigation -->
        <nav class="primary-nav">
            <a href="index.php" class="active">Home</a>
            <a href="event.php">Events</a>
            <a href="admission.php">Admissions</a>
            <a href="login.php" class="cta-button">Login</a>
        </nav>

        <!-- Mobile Menu Button -->
        <button class="mobile-menu" aria-label="Open menu">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Mobile Navigation Panel -->
        <div id="mobileNavPanel" style="display:none; position:absolute; right:20px; top:85px; background:white; border-radius:14px; box-shadow:0 15px 35px rgba(0,0,0,0.12); padding:18px 0; width:230px; z-index:9999;">
            <a href="index.php" style="display:block; padding:12px 22px; color:#1e2937; text-decoration:none;">Home</a>
            <a href="event.php" style="display:block; padding:12px 22px; color:#1e2937; text-decoration:none;">Events</a>
            <a href="admission.php" style="display:block; padding:12px 22px; color:#1e2937; text-decoration:none;">Admissions</a>
            <a href="login.php" style="display:block; padding:12px 22px; margin-top:8px; background:var(--primary); color:white; text-align:center; border-radius:8px; margin:12px 22px;">Login</a>
        </div>
    </div>
</header>

<!-- ====================== HERO SLIDER ====================== -->
<?php
include __DIR__ . '/config/database.php';
$sql = "SELECT id, title, description, image FROM hero_sliders ORDER BY id ASC LIMIT 3";
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
        ['title' => 'Shaping Young Minds for a Brighter Future', 'description' => 'Providing a nurturing environment where children discover their potential.', 'image' => $fallbackImg],
        ['title' => 'Excellence in Education Since 2005', 'description' => 'Balancing academic rigor with creative exploration.', 'image' => $fallbackImg],
        ['title' => 'State-of-the-Art Learning Facilities', 'description' => 'Modern classrooms, labs and creative spaces.', 'image' => $fallbackImg]
    ];
}
?>

<section class="banner-slider" id="banner-slider">
    <?php foreach ($slides as $i => $s):
        $active = $i === 0 ? ' active' : '';
        $img = !empty($s['image']) ? (strpos($s['image'], '/') === false ? 'uploads/hero/' . $s['image'] : $s['image']) : $fallbackImg;
        $imgEsc = htmlspecialchars($img);
        $titleEsc = htmlspecialchars($s['title']);
        $descEsc = nl2br(htmlspecialchars($s['description']));
    ?>
        <div class="slide<?= $active ?>" style="background-image: url('<?= $imgEsc ?>');">
            <div class="slide-inner">
                <span class="kicker">Admissions Open 2026-27</span>
                <h1><?= $titleEsc ?></h1>
                <p><?= $descEsc ?></p>
                <div class="hero-buttons">
                    <a href="#" class="btn-primary">Explore Programs</a>
                    <a href="#" class="btn-secondary">Schedule Visit</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="slider-controls">
        <div class="arrow prev"><i class="fas fa-chevron-left"></i></div>
        <div class="arrow next"><i class="fas fa-chevron-right"></i></div>
    </div>

    <div class="dots">
        <?php for ($d = 0; $d < count($slides); $d++): ?>
            <div class="dot<?= $d === 0 ? ' active' : '' ?>" data-index="<?= $d ?>"></div>
        <?php endfor; ?>
    </div>
</section>

<!-- ====================== NOTICE SECTION ====================== -->
<?php
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
            <p>Important updates and announcements for parents and students</p>
        </div>

        <div class="notice-frame">
            <ul class="notice-list" id="noticeList">
                <?php if (!empty($notices)): ?>
                    <?php foreach ($notices as $n): ?>
                        <li class="notice-item">
                            <strong><?= htmlspecialchars($n['title']) ?></strong><br>
                            <?= nl2br(htmlspecialchars($n['content'])) ?>
                            <?php if ($n['start_date'] || $n['end_date']): ?>
                                <small>
                                    <?php if ($n['start_date']): ?>Start: <?= date('d M Y', strtotime($n['start_date'])) ?> &nbsp;<?php endif; ?>
                                    <?php if ($n['end_date']): ?>End: <?= date('d M Y', strtotime($n['end_date'])) ?><?php endif; ?>
                                </small>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="notice-item">No active notices at the moment.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</section>

<!-- Features -->
<section>
    <div class="container">
        <div class="section-title">
            <h2>Why Choose Our School Management Software?</h2>
            <p>Powerful, easy-to-use, and designed for modern schools</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-user-graduate"></i></div>
                <h3>Smart Administration</h3>
                <p>Complete school ERP with attendance, fees, exams, and reports in one platform.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                <h3>Real-time Insights</h3>
                <p>Powerful dashboards and analytics for principals and administrators.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
                <h3>Parent Mobile App</h3>
                <p>Keep parents connected with instant notifications and progress reports.</p>
            </div>
        </div>
    </div>
</section>

<!-- Programs -->
<section style="background:#f1f5f9;">
    <div class="container">
        <div class="section-title">
            <h2>Our Educational Programs</h2>
            <p>From foundational learning to advanced academics</p>
        </div>
        <div class="programs-grid">
            <div class="program-card">
                <div class="program-image" style="background-image: url('https://images.unsplash.com/photo-1588072432836-e10032774350?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80');"></div>
                <div class="program-content">
                    <h3>Kindergarten</h3>
                    <p>Play-based learning that builds strong foundations.</p>
                </div>
            </div>
            <div class="program-card">
                <div class="program-image" style="background-image: url('https://images.unsplash.com/photo-1588072432836-e10032774350?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80');"></div>
                <div class="program-content">
                    <h3>Elementary School</h3>
                    <p>Strong focus on literacy, numeracy and creativity.</p>
                </div>
            </div>
            <div class="program-card">
                <div class="program-image" style="background-image: url('https://images.unsplash.com/photo-1577896851231-70ef18881754?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80');"></div>
                <div class="program-content">
                    <h3>Middle School</h3>
                    <p>Preparing students for higher education with critical thinking.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container">
        <h2 style="font-size: 38px; margin-bottom: 16px;">Ready to Transform Your School?</h2>
        <p style="font-size: 18px; max-width: 680px; margin: 0 auto 28px;">Join hundreds of schools using our powerful School Management Software.</p>
        <a href="#" class="cta-button" style="background:white; color:var(--primary); padding:16px 36px; font-size:17px;">Get Started Today</a>
    </div>
</section>

<!-- Footer -->
<footer>
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <h3>School Management Software</h3>
                <p>Complete ERP solution for modern schools. Manage admissions, academics, fees, attendance, and parent communication effortlessly.</p>
                <div class="social-links" style="margin-top:20px;">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h3>Quick Links</h3>
                <ul style="list-style:none;">
                    <li><a href="#">Home</a></li>
                    <li><a href="#">Features</a></li>
                    <li><a href="#">Pricing</a></li>
                    <li><a href="#">Demo</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Resources</h3>
                <ul style="list-style:none;">
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Contact</h3>
                <p>📍 123 Education Street, Kolkata, India<br>
                   📞 +91 98765 43210<br>
                   ✉️ info@schoolindia.in</p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date("Y") ?> School Management Software. All Rights Reserved.</p>
            <p>Designed & Developed by <a href="https://elexa.in" target="_blank" style="color:#60a5fa;">Elexa Technologies</a></p>
        </div>
    </div>
</footer>

<script>
// ==================== Mobile Menu ====================
const mobileBtn = document.querySelector('.mobile-menu');
const mobilePanel = document.getElementById('mobileNavPanel');

mobileBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    mobilePanel.style.display = mobilePanel.style.display === 'block' ? 'none' : 'block';
});

document.addEventListener('click', () => {
    mobilePanel.style.display = 'none';
});

// ==================== Banner Slider ====================
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

function nextSlide() { showSlide(currentSlide + 1); }
function prevSlide() { showSlide(currentSlide - 1); }

function startSlider() {
    slideInterval = setInterval(nextSlide, 5500);
}

prevBtn.addEventListener('click', () => { prevSlide(); resetSlider(); });
nextBtn.addEventListener('click', () => { nextSlide(); resetSlider(); });

dots.forEach((dot, i) => {
    dot.addEventListener('click', () => { showSlide(i); resetSlider(); });
});

function resetSlider() {
    clearInterval(slideInterval);
    startSlider();
}

startSlider();

// ==================== Notice Ticker ====================
(function() {
    const frame = document.querySelector('.notice-frame');
    if (!frame) return;
    const list = document.getElementById('noticeList');
    const items = Array.from(list.children);
    if (items.length < 2) return;

    const visibleCount = 3;
    const delay = 4000;

    let index = 0;
    function advance() {
        index = (index + 1) % Math.max(1, items.length - visibleCount + 1);
        list.style.transition = 'transform 1.2s ease';
        list.style.transform = `translateY(-${index * 78}px)`;
    }

    setInterval(advance, delay);
})();
</script>

</body>
</html>