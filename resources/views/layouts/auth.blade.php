<!DOCTYPE html>
<html lang="id">
<head>
    @php
        $appName = \App\Models\Setting::valueOr('app_name', 'LibraVault');
        $appLogo = \App\Models\Setting::valueOr('app_logo');
        $appColor = \App\Models\Setting::valueOr('app_color', '#FFFFFF');
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Auth' }} - {{ $appName }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                        serif: ['Playfair Display', 'Georgia', 'serif'],
                    },
                    colors: {
                        lib: {
                            50: '#FBF8F1',
                            100: '#F5EDD6',
                            200: '#E8D5A8',
                            300: '#D4B978',
                            400: '#C9A84C',
                            500: '#B8923A',
                            600: '#9A7530',
                            700: '#7A5A28',
                            800: '#5C4320',
                            900: '#3D2D15',
                            950: '#2C1810',
                        },
                        forest: {
                            50: '#F0F7F4',
                            100: '#D9EDE2',
                            200: '#B5DBC6',
                            300: '#83C2A0',
                            400: '#52A479',
                            500: '#35875F',
                            600: '#276C4B',
                            700: '#1F573D',
                            800: '#1B4532',
                            900: '#17392A',
                            950: '#0D201A',
                        },
                    },
                },
            },
        };
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
            color: #2C1810;
        }
        .book-pattern {
            background-color: #1B4532;
            background-image:
                linear-gradient(to right, rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255,255,255,0.04) 48px, rgba(201,168,76,0.15) 48px, rgba(201,168,76,0.15) 50px, transparent 50px),
                linear-gradient(90deg,
                    rgba(139,90,43,0.25) 0px, rgba(139,90,43,0.25) 18px,
                    rgba(27,69,50,0.3) 18px, rgba(27,69,50,0.3) 20px,
                    rgba(160,82,45,0.2) 20px, rgba(160,82,45,0.2) 34px,
                    rgba(27,69,50,0.3) 34px, rgba(27,69,50,0.3) 36px,
                    rgba(85,107,47,0.2) 36px, rgba(85,107,47,0.2) 52px,
                    rgba(27,69,50,0.3) 52px, rgba(27,69,50,0.3) 54px,
                    rgba(178,34,34,0.15) 54px, rgba(178,34,34,0.15) 70px,
                    rgba(27,69,50,0.3) 70px, rgba(27,69,50,0.3) 72px,
                    rgba(70,100,50,0.2) 72px, rgba(70,100,50,0.2) 88px,
                    rgba(27,69,50,0.3) 88px, rgba(27,69,50,0.3) 90px,
                    rgba(120,60,30,0.2) 90px, rgba(120,60,30,0.2) 108px,
                    rgba(27,69,50,0.3) 108px, rgba(27,69,50,0.3) 110px,
                    rgba(50,80,120,0.15) 110px, rgba(50,80,120,0.15) 125px,
                    rgba(27,69,50,0.3) 125px, rgba(27,69,50,0.3) 127px,
                    transparent 127px
                );
            background-size: 128px 50px;
        }
        .auth-shell {
            background: linear-gradient(160deg, #FDF8F0 0%, #FEFCF9 40%, #FBF8F1 100%);
        }
        .login-card {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(201,168,76,0.2);
            box-shadow:
                0 25px 60px rgba(0,0,0,0.15),
                0 0 0 1px rgba(255,255,255,0.5) inset,
                0 1px 0 rgba(201,168,76,0.1) inset;
        }
        .brand-glow {
            box-shadow: 0 0 24px rgba(201, 168, 76, 0.22);
        }
        .logo-slot {
            background: var(--brand-logo-bg, #FFFFFF);
            border: 1px solid rgba(201,168,76,0.35);
            box-shadow: 0 12px 30px rgba(201,168,76,0.15);
        }
        .input-lib {
            border: 1.5px solid #E8D5A8;
            background: #FEFCF9;
            transition: all 0.25s ease;
        }
        .input-lib:focus {
            border-color: #C9A84C;
            box-shadow: 0 0 0 3px rgba(201,168,76,0.15), 0 1px 2px rgba(0,0,0,0.05);
            background: #fff;
            outline: none;
        }
        .auth-button {
            background: #C9A15B;
            color: #ffffff;
            transition: all 0.25s ease;
            box-shadow: 0 12px 24px rgba(201,161,91,0.25);
        }
        .auth-button:hover {
            background: #bf9550;
            transform: translateY(-1px);
            box-shadow: 0 16px 30px rgba(191,149,80,0.3);
        }
        .auth-button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        .checkbox-lib {
            appearance: none;
            width: 18px;
            height: 18px;
            border: 1.5px solid #D4B978;
            border-radius: 4px;
            background: #FEFCF9;
            position: relative;
        }
        .checkbox-lib:checked {
            background: #C9A15B;
            border-color: #C9A15B;
        }
        .checkbox-lib:checked::after {
            content: '✓';
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            font-size: 12px;
            font-weight: 700;
            color: #ffffff;
        }
        .auth-link {
            color: #1B4532;
            font-weight: 600;
            text-decoration: none;
        }
        .auth-link:hover { color: #276C4B; }
        .status-box {
            border: 1px solid rgba(39,108,75,0.18);
            background: rgba(240,247,244,0.9);
            color: #1F573D;
        }
        .error-box {
            border: 1px solid rgba(220,38,38,0.15);
            background: rgba(254,242,242,0.9);
            color: #b91c1c;
        }
        @media (max-width: 1023px) {
            .side-illustration { display: none !important; }
        }
    </style>
</head>
<body class="min-h-screen" style="--brand-logo-bg: {{ $appColor }};">
    <main class="auth-shell min-h-screen flex items-center justify-center px-6 py-12 relative">
        <div class="absolute top-0 right-0 w-64 h-64 rounded-full opacity-30" style="background: radial-gradient(circle, rgba(201,168,76,0.15), transparent 70%);"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 rounded-full opacity-20" style="background: radial-gradient(circle, rgba(27,69,50,0.1), transparent 70%);"></div>

        <div class="w-full max-w-md relative z-10">
            <div class="lg:hidden flex items-center justify-center gap-3 mb-10">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center text-lib-300 text-sm font-bold overflow-hidden" style="background: var(--brand-logo-bg);">
                    @if ($appLogo && file_exists(public_path($appLogo)))
                        <img src="{{ asset($appLogo) }}" alt="{{ $appName }}" class="w-full h-full object-contain p-2">
                    @else
                        {{ strtoupper(substr($appName, 0, 2)) }}
                    @endif
                </div>
                <div>
                    <h1 class="text-lg font-serif font-bold text-forest-900">{{ $appName }}</h1>
                    <p class="text-lib-500 text-[10px] font-medium tracking-[0.3em] uppercase">Perpustakaan Digital</p>
                </div>
            </div>

            <div class="login-card rounded-3xl p-8 sm:p-10 brand-glow">
                @if (session('status'))
                    <div class="status-box rounded-2xl px-4 py-3 text-sm mb-5">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="error-box rounded-2xl px-4 py-3 text-sm mb-5">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="text-center mb-8">
                    <div class="logo-slot inline-flex items-center justify-center w-24 h-24 rounded-[1.75rem] mb-4 overflow-hidden">
                        @if ($appLogo && file_exists(public_path($appLogo)))
                            <img src="{{ asset($appLogo) }}" alt="{{ $appName }}" class="w-full h-full object-contain p-3">
                        @else
                            <div class="text-center leading-tight">
                                <div class="text-lib-900 text-xs font-semibold tracking-[0.3em] uppercase">Logo</div>
                                <div class="text-lib-700 text-[11px] mt-1">{{ $appName }}</div>
                            </div>
                        @endif
                    </div>
                    <h2 class="text-2xl font-serif font-bold text-lib-950 mb-1">@yield('heading')</h2>
                    <p class="text-sm text-lib-700/60 font-light">@yield('subheading')</p>
                </div>

                @yield('content')
            </div>
        </div>
    </main>
</body>
</html>
