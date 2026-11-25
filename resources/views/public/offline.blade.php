<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sedang Offline — {{ $blogName }}</title>

    <x-pwa-meta :favicon-url="$faviconUrl" :app-name="$blogName" />

    <script>
        (function () {
            const isDark = localStorage.getItem('theme') === 'dark'
                || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', isDark);
            const meta = document.getElementById('pwa-theme-color');
            if (meta) meta.setAttribute('content', isDark ? '#222520' : '#F6F6F3');
        })();
    </script>

    <style>
        @font-face {
            font-family: 'Caveat';
            font-style: normal;
            font-weight: 700;
            font-display: swap;
            src: url('/fonts/caveat-700.woff2') format('woff2');
        }
        @font-face {
            font-family: 'Patrick Hand';
            font-style: normal;
            font-weight: 400;
            font-display: swap;
            src: url('/fonts/patrick-hand-400.woff2') format('woff2');
        }
        @font-face {
            font-family: 'DM Sans';
            font-style: normal;
            font-weight: 400;
            font-display: swap;
            src: url('/fonts/dm-sans-400.woff2') format('woff2');
        }

        :root {
            --bg: #F6F6F3;
            --surface: #FFFFFF;
            --text: #20211F;
            --text-2: #6B6C66;
            --ink: #20211F;
            --accent-dark: #435343;
            --on-accent: #FFFFFF;
            --radius-sketch-a: 255px 15px 225px 15px / 15px 225px 15px 255px;
            --radius-sketch-b: 15px 225px 15px 255px / 255px 15px 225px 15px;
        }
        .dark {
            --bg: #222520;
            --surface: #2B2F29;
            --text: #F4F4EF;
            --text-2: #A4A69D;
            --ink: #E9E9E2;
            --accent-dark: #B4C5AB;
            --on-accent: #1B1E1A;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--bg);
            color: var(--text);
            font-family: 'DM Sans', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            line-height: 1.6;
        }
        .card {
            max-width: 32rem;
            width: 100%;
            background-color: var(--surface);
            border: 2px solid var(--ink);
            border-radius: var(--radius-sketch-a);
            box-shadow: 3px 3px 0 var(--ink);
            padding: 2.25rem 1.75rem;
            text-align: center;
        }
        .icon {
            width: 88px;
            height: 88px;
            margin: 0 auto 1.25rem;
            padding: 0.375rem;
            background-color: var(--bg);
            border: 2px solid var(--ink);
            border-radius: 1rem;
            box-shadow: 2px 2px 0 var(--ink);
            display: block;
            object-fit: contain;
        }
        h1 {
            font-family: 'Caveat', cursive;
            font-size: 2.75rem;
            line-height: 1.1;
            color: var(--ink);
            margin-bottom: 0.75rem;
        }
        p {
            color: var(--text-2);
            font-size: 1rem;
            margin-bottom: 1.75rem;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 0.875rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0.625rem 1.25rem;
            font-family: 'Patrick Hand', cursive, sans-serif;
            font-size: 1.0625rem;
            text-decoration: none;
            border: 2px solid var(--ink);
            border-radius: var(--radius-sketch-b);
            box-shadow: 3px 3px 0 var(--ink);
            cursor: pointer;
            transition: transform 0.12s ease, box-shadow 0.12s ease;
        }
        .btn:active {
            transform: translate(3px, 3px);
            box-shadow: 0 0 0 var(--ink);
        }
        .btn-primary {
            background-color: var(--accent-dark);
            color: var(--on-accent);
        }
        .btn-secondary {
            background-color: var(--surface);
            color: var(--text);
        }
        .btn:focus-visible {
            outline: 3px solid var(--accent-dark);
            outline-offset: 3px;
        }
    </style>
</head>
<body>
    <main class="card">
        <img src="/icons/icon-192x192.png" width="88" height="88" alt="{{ $blogName }}" class="icon">
        <h1>Sedang Offline</h1>
        <p>
            Koneksi internet sedang terputus. Halaman yang sudah pernah kamu buka tetap tersimpan untuk dibaca, atau coba muat ulang setelah tersambung kembali.
        </p>
        <div class="actions">
            <button type="button" id="offline-retry-btn" class="btn btn-primary">
                Muat Ulang
            </button>
            <a href="{{ route('home') }}" class="btn btn-secondary">
                Kembali ke Beranda
            </a>
        </div>
    </main>

    <script>
        document.getElementById('offline-retry-btn')?.addEventListener('click', function () {
            window.location.reload();
        });
    </script>
</body>
</html>

