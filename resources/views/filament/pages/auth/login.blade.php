@push('styles')
     @vite(['resources/css/app.css', 'resources/js/app.tsx'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --forest:        #2D4739;
            --forest-light:  #3a5c49;
            --forest-dark:   #1e3028;
            --forest-deeper: #141f1a;
            --sage:          #7a9e8e;
            --sage-light:    #a8c4b8;
            --cream:         #f5f0e8;
            --cream-dark:    #ece5d8;
            --gold:          #c9a84c;
            --gold-light:    #e2c47a;
        }

        * { box-sizing: border-box; }

        .login-root {
            min-height: 100vh;
            display: flex;
            font-family: 'DM Sans', sans-serif;
            background: var(--forest-deeper);
        }

        /* ── LEFT PANEL ─────────────────────────────────────────── */
        .login-left {
            display: none;
            position: relative;
            overflow: hidden;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
        }
        @media (min-width: 1024px) {
            .login-left  { display: flex; width: 52%; }
            .login-right { width: 48%; }
        }

        .login-left-bg {
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 20%, rgba(45,71,57,0.9) 0%, transparent 60%),
                radial-gradient(ellipse 60% 80% at 80% 80%, rgba(30,48,40,0.95) 0%, transparent 60%),
                linear-gradient(160deg, #141f1a 0%, #1e3028 40%, #2D4739 100%);
            z-index: 0;
        }
        .login-left-noise {
            position: absolute; inset: 0; z-index: 1; opacity: 0.04;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
            background-size: 200px;
        }
        .login-left-ring {
            position: absolute; border-radius: 50%;
            border: 1px solid rgba(201,168,76,0.08); z-index: 1;
        }
        .ring-1 { width: 600px; height: 600px; top: -200px; left: -200px; }
        .ring-2 { width: 400px; height: 400px; bottom: -100px; right: -100px; border-color: rgba(122,158,142,0.1); }
        .ring-3 { width: 200px; height: 200px; top: 40%; left: 60%; border-color: rgba(201,168,76,0.12); }
        .login-left-accent {
            position: absolute; top: 0; left: 60px;
            width: 1px; height: 45%;
            background: linear-gradient(to bottom, transparent, var(--gold), transparent);
            z-index: 2; opacity: 0.4;
        }

        .login-left-content { position: relative; z-index: 10; max-width: 420px; width: 100%; }

        .login-logo-mark {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, var(--forest-light), var(--forest-dark));
            border: 1px solid rgba(255, 189, 7, 0.3);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 2.5rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4), inset 0 1px 0 rgba(201,168,76,0.2);
        }
        .login-logo-mark svg { width: 28px; height: 28px; color: var(--gold-light); }

        .login-brand-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 3rem; font-weight: 600;
            color: var(--cream); line-height: 1.1;
            letter-spacing: -0.02em; margin-bottom: 1rem;
        }
        .login-brand-name em { font-style: italic; color: var(--gold-light); }

        .login-brand-tagline {
            font-size: 0.9rem; color: var(--sage);
            line-height: 1.7; font-weight: 300;
            margin-bottom: 2.5rem; max-width: 320px;
        }

        .login-divider { display: flex; align-items: center; gap: 1rem; margin-bottom: 2.5rem; }
        .login-divider-line { flex: 1; height: 1px; background: linear-gradient(to right, transparent, rgba(201,168,76,0.3), transparent); }
        .login-divider-dot { width: 4px; height: 4px; background: var(--gold); border-radius: 50%; opacity: 0.6; }

        .login-features { display: flex; flex-direction: column; gap: 0.875rem; }
        .login-feature { display: flex; align-items: center; gap: 0.875rem; }
        .login-feature-icon {
            width: 32px; height: 32px;
            background: rgba(45,71,57,0.6);
            border: 1px solid rgba(122,158,142,0.2);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .login-feature-icon svg { width: 14px; height: 14px; color: var(--sage-light); }
        .login-feature-text { font-size: 0.8rem; color: var(--sage); font-weight: 400; letter-spacing: 0.01em; }

        .login-left-footer {
            position: absolute; bottom: 2rem; left: 3rem; z-index: 10;
            display: flex; align-items: center; gap: 0.5rem;
        }
        .login-left-footer-dot { width: 6px; height: 6px; background: var(--gold); border-radius: 50%; opacity: 0.6; }
        .login-left-footer span { font-size: 0.7rem; color: rgba(122,158,142,0.5); letter-spacing: 0.08em; text-transform: uppercase; }

        /* ── RIGHT PANEL ─────────────────────────────────────────── */
        .login-right {
            width: 100%;
            display: flex; align-items: center; justify-content: center;
            background: var(--cream);
            padding: 2rem 1.5rem;
            position: relative; overflow: hidden;
        }
        .login-right::before {
            content: ''; position: absolute; inset: 0; pointer-events: none;
            background:
                radial-gradient(ellipse 70% 50% at 100% 0%, rgba(45,71,57,0.06) 0%, transparent 60%),
                radial-gradient(ellipse 50% 70% at 0% 100%, rgba(201,168,76,0.04) 0%, transparent 60%);
        }

        .login-form-wrap { position: relative; z-index: 10; width: 100%; max-width: 400px; }

        .login-mobile-logo { display: flex; justify-content: center; margin-bottom: 2rem; }
        @media (min-width: 1024px) { .login-mobile-logo { display: none; } }
        .login-mobile-logo-mark {
            width: 52px; height: 52px; background: var(--forest);
            border-radius: 14px; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 16px rgba(45,71,57,0.3);
        }
        .login-mobile-logo-mark svg { width: 24px; height: 24px; color: var(--cream); }

        .login-form-heading { margin-bottom: 2.5rem; }
        .login-form-eyebrow {
            font-size: 0.7rem; font-weight: 500; color: var(--sage);
            letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 0.5rem;
            display: flex; align-items: center; gap: 0.5rem;
        }
        .login-form-eyebrow::before { content: ''; display: block; width: 20px; height: 1px; background: var(--gold); }
        .login-form-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.6rem; font-weight: 600;
            color: var(--forest-dark); line-height: 1.05;
            letter-spacing: -0.02em; margin-bottom: 0.5rem;
        }
        .login-form-subtitle { font-size: 0.85rem; color: #7a8a80; font-weight: 300; }

        /* Filament field overrides */
        .login-form-fields { margin-bottom: 0.5rem; }
        .login-form-fields label,
        .login-form-fields .fi-label {
            font-family: 'DM Sans', sans-serif !important;
            font-size: 0.72rem !important; font-weight: 500 !important;
            color: #4a5e52 !important; letter-spacing: 0.06em !important;
            text-transform: uppercase !important;
        }

        /* Fix password field — merge eye icon into the input border */
        .login-form-fields .fi-input-wrp {
            border: 1px solid #d4ddd7 !important;
            border-radius: 10px !important;
            background: white !important;
            box-shadow: 0 1px 3px rgba(45,71,57,0.06) !important;
            overflow: hidden;
        }

        .login-form-fields .fi-input-wrp:focus-within {
            border-color: var(--sage) !important;
            box-shadow: 0 0 0 3px rgba(122,158,142,0.18) !important;
        }
        /* .login-form-fields input {
            font-family: 'DM Sans', sans-serif !important;
            background: white !important;
            border: 1px solid #d4ddd7 !important;
            border-radius: 10px !important;
            color: var(--forest-dark) !important;
            font-size: 0.9rem !important;
            transition: border-color 0.2s, box-shadow 0.2s !important;
            box-shadow: 0 1px 3px rgba(45,71,57,0.06) !important;
        } */
        .login-form-fields input:focus {
            outline: none !important;
            border-color: var(--sage) !important;
            box-shadow: 0 0 0 3px rgba(122,158,142,0.18) !important;
        }

        /* Submit button */
        .login-submit-btn {
            width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem;
            padding: 0.9rem 1.5rem;
            background: var(--forest); color: var(--cream);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.8rem; font-weight: 500;
            letter-spacing: 0.1em; text-transform: uppercase;
            border: none; border-radius: 10px; cursor: pointer;
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 4px 16px rgba(45,71,57,0.35), inset 0 1px 0 rgba(255,255,255,0.08);
            position: relative; overflow: hidden; margin-top: 1.75rem;
        }
        .login-submit-btn::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.08) 0%, transparent 50%);
            pointer-events: none;
        }
        .login-submit-btn:hover { background: var(--forest-light); box-shadow: 0 6px 24px rgba(45,71,57,0.45); transform: translateY(-1px); }
        .login-submit-btn:active { transform: translateY(0); box-shadow: 0 2px 8px rgba(45,71,57,0.3); }
        .login-submit-btn:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }

        .login-spinner {
            width: 15px; height: 15px;
            border: 2px solid rgba(245,240,232,0.3);
            border-top-color: var(--cream);
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .login-form-footer { margin-top: 2.5rem; text-align: center; font-size: 0.72rem; color: #a0b0a8; letter-spacing: 0.02em; }
        .login-form-footer strong { color: var(--sage); font-weight: 500; }

        /* Entry animations */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .login-form-wrap    { animation: fadeUp 0.55s cubic-bezier(.22,.68,0,1.2) both; }
        .login-left-content { animation: fadeUp 0.7s cubic-bezier(.22,.68,0,1.2) 0.1s both; }
    </style>
@endpush

<div class="login-root">

    {{-- ── LEFT PANEL ──────────────────────────────────────────────────── --}}
    <div class="login-left">
        <div class="login-left-bg"></div>
        <div class="login-left-noise"></div>
        <div class="login-left-ring ring-1"></div>
        <div class="login-left-ring ring-2"></div>
        <div class="login-left-ring ring-3"></div>
        <div class="login-left-accent"></div>

        <div class="login-left-content">
            <div class="flex items-center gap-3 mb-10">
                <img src="/images/turftec-logo.png" alt="TurfTec Logo" style="height: 48px; width: auto;">
                <span class="login-brand-name" style="margin-bottom: 0; font-size: 2rem;">TurfTec</span>
            </div>

            <!-- <h1 class="login-brand-name">Turf<em>Tec</em></h1> -->
            <p class="login-brand-tagline">
                A refined platform for managing your business with clarity, precision, and elegance.
            </p>

            <div class="login-divider">
                <div class="login-divider-line"></div>
                <div class="login-divider-dot"></div>
                <div class="login-divider-line"></div>
            </div>

            <div class="login-features">
                @foreach([
                    ['icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z', 'label' => 'Analytics & Reporting Dashboard'],
                    ['icon' => 'M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z', 'label' => 'Product & Order Management'],
                    ['icon' => 'M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z', 'label' => 'Blog & Content Publishing'],
                ] as $f)
                    <div class="login-feature">
                        <div class="login-feature-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $f['icon'] }}" />
                            </svg>
                        </div>
                        <span class="login-feature-text">{{ $f['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- RIGHT PANEL  --}}
    <div class="login-right">

    {{-- Back button --}}
    <a href="{{ url('/') }}"
    style="position: absolute; top: 1.5rem; left: 0.5rem; display: flex; align-items: center; gap: 0.4rem; font-family: 'DM Sans', sans-serif; font-size: 0.78rem; font-weight: 500; color: var(--sage); text-decoration: none; letter-spacing: 0.03em; transition: color 0.2s;"
    onmouseover="this.style.color='var(--forest)'"
    onmouseout="this.style.color='var(--sage)'">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:14px;height:14px;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        Back to site
    </a>
        <div class="login-form-wrap">

            {{-- Mobile logo --}}
            <div class="login-mobile-logo">
                <!-- <div class="login-mobile-logo-mark">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.955 11.955 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                </div> -->
                <div class="login-mobile-logo mt-10">
                    <div class="flex items-center gap-2">
                        <img src="/images/turftec-logo.png" alt="TurfTec Logo" style="height: 36px; width: auto;">
                        <span style="font-family: 'Cormorant Garamond', serif; font-size: 1.5rem; font-weight: 600; color: var(--forest-dark);">TurfTec</span>
                    </div>
                </div>
            </div>

            {{-- Heading --}}
            <div class="login-form-heading">
                <div class="login-form-eyebrow">Admin Portal</div>
                <h2 class="login-form-title">Welcome back.</h2>
                <p class="login-form-subtitle">Sign in to access your dashboard.</p>
            </div>

            {{-- Form --}}
            <form wire:submit="authenticate">
                <div class="login-form-fields">
                    {{ $this->form }}
                </div>

                <button
                    type="submit"
                    class="login-submit-btn"
                    wire:loading.attr="disabled"
                >
                    <div wire:loading wire:target="authenticate" class="login-spinner"></div>
                    <span wire:loading.remove wire:target="authenticate">Sign In</span>
                    <span wire:loading wire:target="authenticate">Signing in…</span>
                </button>
            </form>

            <div class="login-form-footer">
                &copy; {{ now()->year }} <strong>TurfTec</strong>. All rights reserved.
            </div>

        </div>
    </div>

</div>