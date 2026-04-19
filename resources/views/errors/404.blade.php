<!DOCTYPE html>
<html lang="id">
<head>
    @php
        $appName = \App\Models\Setting::valueOr('app_name', 'LibraVault');
        $appLogo = \App\Models\Setting::appLogoPath();
        $appColor = \App\Models\Setting::valueOr('app_color', '#c4956a');
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg: #f8f6f1;
            --bg-card: #ffffff;
            --fg: #1a2e35;
            --fg-muted: #394b53;
            --primary: #0f4c5c;
            --primary-light: #1a6b7c;
            --accent: {{ $appColor }};
            --accent-light: #d4a574;
            --border: #e2ddd4;
            --accent-glow: rgba(196, 149, 106, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--fg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        .font-display {
            font-family: 'Playfair Display', serif;
        }

        /* Background Pattern */
        .bg-pattern {
            position: fixed;
            inset: 0;
            background-image: 
                radial-gradient(ellipse at 30% 20%, rgba(15, 76, 92, 0.04) 0%, transparent 50%), 
                radial-gradient(ellipse at 70% 80%, rgba(196, 149, 106, 0.06) 0%, transparent 50%), 
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%230f4c5c' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 0;
        }

        /* Navbar Minimal */
        .navbar {
            padding: 20px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 10;
            border-bottom: 1px solid var(--border);
            background: rgba(248, 246, 241, 0.8);
            backdrop-filter: blur(10px);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .logo-icon {
            width: auto;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .logo-icon img {
            height: 100%;
            width: auto;
            object-fit: contain;
        }

        .logo-icon svg {
            width: 22px;
            height: 22px;
            color: white;
        }

        .logo-text {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            font-weight: 700;
            color: var(--fg);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
            position: relative;
            z-index: 1;
        }

        .error-container {
            text-align: center;
            max-width: 600px;
            animation: fadeInUp 0.8s ease-out;
        }

        /* 404 Graphic */
        .error-graphic {
            position: relative;
            margin-bottom: 40px;
        }

        .error-code {
            font-family: 'Playfair Display', serif;
            font-size: clamp(120px, 25vw, 200px);
            font-weight: 800;
            color: var(--primary);
            line-height: 1;
            opacity: 0.1;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            user-select: none;
            z-index: 0;
        }

        .floating-books {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            height: 200px;
            z-index: 1;
        }

        .book-item {
            animation: float 4s ease-in-out infinite;
        }

        .book-item:nth-child(1) { animation-delay: 0s; }
        .book-item:nth-child(2) { animation-delay: 0.5s; }
        .book-item:nth-child(3) { animation-delay: 1s; }

        .book-svg {
            width: 60px;
            height: 80px;
            filter: drop-shadow(0 10px 20px rgba(15, 76, 92, 0.15));
        }

        .book-item:nth-child(1) .book-svg { transform: rotate(-15deg); width: 50px; height: 70px; }
        .book-item:nth-child(2) .book-svg { transform: rotate(5deg); width: 70px; height: 90px; }
        .book-item:nth-child(3) .book-svg { transform: rotate(-8deg); width: 55px; height: 75px; }

        .searching-glass {
            position: absolute;
            right: calc(50% - 120px);
            top: 20px;
            animation: searching 3s ease-in-out infinite;
        }

        .searching-glass svg {
            width: 40px;
            height: 40px;
            color: var(--accent);
            filter: drop-shadow(0 4px 8px rgba(196, 149, 106, 0.3));
        }

        /* Text Content */
        .error-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(28px, 5vw, 40px);
            font-weight: 700;
            color: var(--fg);
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }

        .error-subtitle {
            font-size: 16px;
            color: var(--fg-muted);
            line-height: 1.6;
            margin-bottom: 32px;
            max-width: 450px;
            margin-left: auto;
            margin-right: auto;
        }

        .highlight {
            color: #5d4037;
            font-weight: 800;
        }

        /* Buttons - Glow Style based on Image */
        .action-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-glow {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 12px 36px;
            border-radius: 16px;
            background: #c4956a;
            border: none;
            color: #ffffff !important;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all .3s ease;
            box-shadow: 0 8px 20px -6px rgba(196, 149, 106, 0.5);
            text-decoration: none;
            position: relative;
        }

        .btn-glow:hover {
            background: #b38459;
            transform: translateY(-3px);
            box-shadow: 0 12px 25px -5px rgba(196, 149, 106, 0.6);
            color: #ffffff !important;
        }

        .btn-glow svg {
            width: 22px;
            height: 22px;
            stroke-width: 3;
            color: #ffffff !important;
        }

        /* Decorative Elements */
        .dust-particles {
            position: absolute;
            inset: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            width: 6px;
            height: 6px;
            background: var(--accent);
            border-radius: 50%;
            opacity: 0.3;
            animation: drift 8s linear infinite;
        }

        .particle:nth-child(1) { left: 10%; top: 20%; animation-duration: 10s; animation-delay: 0s; }
        .particle:nth-child(2) { left: 80%; top: 30%; animation-duration: 12s; animation-delay: 2s; }
        .particle:nth-child(3) { left: 20%; top: 70%; animation-duration: 9s; animation-delay: 4s; }
        .particle:nth-child(4) { left: 70%; top: 80%; animation-duration: 11s; animation-delay: 1s; }
        .particle:nth-child(5) { left: 50%; top: 10%; animation-duration: 13s; animation-delay: 3s; }

        /* Footer */
        .footer {
            text-align: center;
            padding: 24px;
            color: var(--fg-muted);
            font-size: 13px;
            position: relative;
            z-index: 10;
        }

        /* Keyframes */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(var(--rot, 0deg)); }
            50% { transform: translateY(-20px) rotate(var(--rot, 0deg)); }
        }

        @keyframes searching {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(10px, 5px) rotate(5deg); }
            50% { transform: translate(0, 10px) rotate(0deg); }
            75% { transform: translate(-10px, 5px) rotate(-5deg); }
        }

        @keyframes drift {
            0% { transform: translateY(0) translateX(0); opacity: 0; }
            20% { opacity: 0.4; }
            80% { opacity: 0.4; }
            100% { transform: translateY(-100vh) translateX(20px); opacity: 0; }
        }

        /* Responsive */
        @media (max-width: 640px) {
            .navbar { padding: 16px 20px; }
            .floating-books { height: 150px; gap: 12px; }
            .book-svg { width: 45px !important; height: 60px !important; }
            .action-buttons { flex-direction: column; align-items: center; }
            .btn-glow { width: 100%; max-width: 280px; }
        }
    </style>
</head>
<body>
    <div class="bg-pattern"></div>
    <div class="dust-particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <!-- Navbar -->
    <nav class="navbar">
        <a href="{{ route('dashboard') }}" class="logo">
            <div class="logo-icon">
                @if($appLogo)
                    <img src="{{ asset($appLogo) }}" alt="{{ $appName }}">
                @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                        <path d="M8 7h8M8 11h8M8 15h4"/>
                    </svg>
                @endif
            </div>
            <span class="logo-text">{{ $appName }}</span>
        </a>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="error-container">
            <!-- 404 Graphic -->
            <div class="error-graphic">
                <div class="error-code">404</div>
                
                <div class="floating-books">
                    <!-- Book 1 -->
                    <div class="book-item" style="--rot: -15deg;">
                        <svg class="book-svg" viewBox="0 0 50 70" fill="none">
                            <rect x="2" y="2" width="46" height="66" rx="4" fill="#0f4c5c"/>
                            <rect x="6" y="6" width="38" height="58" rx="2" fill="#1a6b7c"/>
                            <rect x="10" y="10" width="30" height="4" rx="1" fill="{{ $appColor }}"/>
                            <rect x="10" y="18" width="20" height="2" rx="1" fill="rgba(255,255,255,0.3)"/>
                            <rect x="10" y="24" width="24" height="2" rx="1" fill="rgba(255,255,255,0.2)"/>
                        </svg>
                    </div>
                    
                    <!-- Book 2 (Center/Highlighted) -->
                    <div class="book-item" style="--rot: 5deg;">
                        <svg class="book-svg" viewBox="0 0 60 85" fill="none">
                            <rect x="2" y="2" width="56" height="81" rx="4" fill="#c4956a"/>
                            <rect x="6" y="6" width="48" height="73" rx="2" fill="#d4a574"/>
                            <rect x="12" y="12" width="36" height="6" rx="1" fill="#0f4c5c"/>
                            <rect x="12" y="24" width="28" height="3" rx="1" fill="rgba(255,255,255,0.4)"/>
                            <rect x="12" y="32" width="32" height="3" rx="1" fill="rgba(255,255,255,0.3)"/>
                            <rect x="12" y="40" width="24" height="3" rx="1" fill="rgba(255,255,255,0.3)"/>
                            <text x="30" y="68" text-anchor="middle" fill="#0f4c5c" font-size="24" font-weight="bold" font-family="serif">?</text>
                        </svg>
                    </div>
                    
                    <!-- Book 3 -->
                    <div class="book-item" style="--rot: -8deg;">
                        <svg class="book-svg" viewBox="0 0 50 70" fill="none">
                            <rect x="2" y="2" width="46" height="66" rx="4" fill="#5a6d73"/>
                            <rect x="6" y="6" width="38" height="58" rx="2" fill="#7a8d93"/>
                            <rect x="10" y="10" width="30" height="4" rx="1" fill="{{ $appColor }}"/>
                            <rect x="10" y="18" width="22" height="2" rx="1" fill="rgba(255,255,255,0.3)"/>
                            <rect x="10" y="24" width="26" height="2" rx="1" fill="rgba(255,255,255,0.2)"/>
                        </svg>
                    </div>

                    <!-- Searching Magnifying Glass -->
                    <div class="searching-glass">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Text Content -->
            <h1 class="error-title">Halaman Tidak Ditemukan</h1>
            <p class="error-subtitle">
                Sepertinya halaman yang kamu cari  <span class="highlight">salah</span> atau mungkin belum tersedia di rak perpustakaan kami.
            </p>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ route('login') }}" class="btn-glow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M13.8 12H3"/>
                    </svg>
                    <span>Kembali ke Dashboard</span>
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>{{ $appName }} &copy; {{ date('Y') }}</p>
    </footer>
</body>
</html>