<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SAPA GARUT | Kabupaten Garut</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --blue-900: #0b3a7a;
            --blue-700: #1258c9;
            --blue-600: #1a63e0;
            --blue-500: #2f7bf0;
            --blue-100: #e7f0fe;
            --green-accent: #3ddc84;
            --ink: #1c2536;
            --muted: #6b7688;
            --border: #e3e8f0;
            --bg-outer: #c3cdb8;
            --field-bg: #eef3ea;
            --radius-lg: 22px;
            --radius-md: 12px;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }

        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, sans-serif;
            color: var(--ink);
        }

        /* ---------- SHELL ---------- */
        .shell {
            width: 100%;
            height: 100vh;
            background: #ffffff;
            display: grid;
            grid-template-columns: 1.15fr 1fr;
            overflow: hidden;
        }

        /* ---------- LEFT PANEL ---------- */
        .left {
            position: relative;
            overflow: hidden;
            color: #fff;
            padding: 48px 46px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
        }

        .left img.bg-photo {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
            opacity: 0;
            transition: opacity 1.2s ease-in-out;
        }

        .left img.bg-photo.active {
            opacity: 1;
        }

        .left .overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(160deg, rgba(15, 64, 140, 0.18) 0%, rgba(10, 45, 105, 0.28) 100%);
            z-index: 1;
        }

        /* ---------- SLIDESHOW DOTS ---------- */
        .slide-dots {
            position: absolute;
            bottom: 28px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 3;
            display: flex;
            gap: 8px;
        }

        .slide-dots button {
            width: 22px;
            height: 4px;
            border-radius: 4px;
            border: none;
            padding: 0;
            background: rgba(255, 255, 255, 0.4);
            cursor: pointer;
            transition: background .2s ease, width .2s ease;
        }

        .slide-dots button.active {
            background: #ffffff;
            width: 30px;
        }

        .slide-dots button:hover {
            background: rgba(255, 255, 255, 0.75);
        }

        .left-content {
            position: relative;
            z-index: 2;
            max-width: 420px;
        }

        .left-content h1 {
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -0.01em;
            margin: 0 0 6px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.45);
        }

        .left-content h2.sub {
            font-size: 15px;
            font-weight: 600;
            margin: 0 0 12px;
            color: #eaf1ff;
            text-shadow: 0 1px 6px rgba(0, 0, 0, 0.4);
        }

        .accent-bar {
            width: 52px;
            height: 4px;
            border-radius: 4px;
            background: var(--green-accent);
            margin-bottom: 16px;
        }

        .left-content p {
            font-size: 14px;
            line-height: 1.65;
            color: #dbe6fb;
            margin: 0;
            text-shadow: 0 1px 6px rgba(0, 0, 0, 0.4);
        }

        /* ---------- RIGHT PANEL ---------- */
        .right {
            background: #eef4ea;
            padding: 24px 32px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            overflow-y: auto;
        }

        .form-card {
            width: 100%;
            max-width: 460px;
            background: #ffffff;
            border-radius: var(--radius-lg);
            box-shadow: 0 20px 45px -15px rgba(20, 40, 90, 0.25);
            padding: 32px 36px 28px;
            margin: 16px 0;
        }

        .form-card h2 {
            font-size: 24px;
            font-weight: 800;
            margin: 0 0 4px;
        }

        .form-card p.subtitle {
            margin: 0 0 20px;
            color: var(--muted);
            font-size: 13.5px;
            line-height: 1.5;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #333c4c;
            margin-bottom: 8px;
        }

        .field {
            margin-bottom: 14px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap svg.icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #8a9482;
            pointer-events: none;
        }

        .input-wrap input,
        .input-wrap select {
            width: 100%;
            padding: 11px 16px 11px 44px;
            border-radius: var(--radius-md);
            border: 1.5px solid var(--border);
            font-size: 14px;
            font-family: inherit;
            outline: none;
            background: var(--field-bg);
            color: var(--ink);
            transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
        }

        /* password field now has left icon + right eye toggle */
        .input-wrap input[type="password"] {
            padding-right: 42px;
        }

        .input-wrap select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            padding-right: 38px;
            cursor: pointer;
        }

        .input-wrap select:invalid {
            color: #93a08a;
        }

        .input-wrap svg.chevron {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #8a9482;
            pointer-events: none;
        }

        .input-wrap input::placeholder {
            color: #93a08a;
        }

        .input-wrap input:focus,
        .input-wrap select:focus {
            border-color: var(--blue-500);
            background: #fff;
            box-shadow: 0 0 0 4px var(--blue-100);
        }

        .input-wrap button.toggle-pass {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            width: 28px;
            height: 28px;
            border: none;
            background: none;
            color: #8a9482;
            cursor: pointer;
            padding: 0;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 0;
            flex-shrink: 0;
        }

        .input-wrap button.toggle-pass svg {
            position: static;
            transform: none;
            flex-shrink: 0;
        }

        .input-wrap button.toggle-pass:hover {
            color: var(--blue-600);
        }

        .field-error {
            color: #d64545;
            font-size: 12px;
            margin-top: 6px;
        }

        /* ---------- POPUP HINT PASSWORD ---------- */
        .pass-hint-popup {
            position: absolute;
            left: 0;
            top: calc(100% + 8px);
            width: 100%;
            background: #ffffff;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            box-shadow: 0 10px 24px -8px rgba(20, 40, 90, 0.22);
            padding: 10px 14px;
            font-size: 12.5px;
            color: var(--muted);
            z-index: 5;
            opacity: 0;
            transform: translateY(-4px);
            pointer-events: none;
            transition: opacity .15s ease, transform .15s ease;
        }

        .pass-hint-popup.show {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .pass-hint-popup::before {
            content: "";
            position: absolute;
            top: -6px;
            left: 20px;
            width: 10px;
            height: 10px;
            background: #ffffff;
            border-left: 1.5px solid var(--border);
            border-top: 1.5px solid var(--border);
            transform: rotate(45deg);
        }

        .pass-hint-popup .req {
            display: flex;
            align-items: center;
            gap: 7px;
            color: #93a08a;
            transition: color .15s ease;
        }

        .pass-hint-popup .req.ok {
            color: #1f9d55;
            font-weight: 600;
        }

        .pass-hint-popup .req svg {
            flex-shrink: 0;
        }

        .alert-box {
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 18px;
        }

        .alert-error {
            background: #fdeeee;
            border: 1px solid #f6c5c5;
            color: #b23a3a;
        }

        .alert-success {
            background: #eaf6ee;
            border: 1px solid #bfe6ca;
            color: #1f7a3f;
        }

        a.link {
            color: var(--blue-600);
            text-decoration: none;
            font-weight: 600;
        }

        a.link:hover {
            text-decoration: underline;
        }

        button.submit {
            width: 100%;
            border: none;
            cursor: pointer;
            padding: 15px 18px;
            border-radius: var(--radius-md);
            background: linear-gradient(90deg, var(--blue-600), var(--blue-500));
            color: #fff;
            font-size: 14.5px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 14px 24px -10px rgba(26, 99, 224, 0.55);
            transition: transform .12s ease, box-shadow .12s ease, filter .12s ease;
            margin-top: 8px;
        }

        button.submit:hover {
            filter: brightness(1.04);
            transform: translateY(-1px);
        }

        button.submit:active {
            transform: translateY(0);
        }

        .signup-hint {
            text-align: center;
            font-size: 13px;
            color: var(--muted);
            margin: 20px 0 0;
        }

        /* ---------- RESPONSIVE ---------- */
        @media (max-width: 900px) {
            .shell {
                grid-template-columns: 1fr;
            }

            .left {
                display: none;
            }

            .right {
                padding: 48px 20px;
            }
        }

        :focus-visible {
            outline: 2px solid var(--blue-500);
            outline-offset: 2px;
        }
    </style>
</head>

<body>

    <div class="shell">

        <!-- ================= LEFT: PHOTO + OVERLAY TEXT ================= -->
        <div class="left" id="photoStage">
            {{-- Tambah/kurangi <img> di sini untuk ganti-ganti foto slideshow --}}
            <img class="bg-photo active" src="{{ asset('images/7.jpeg') }}" alt="Gedung Kabupaten Garut">
            <img class="bg-photo" src="{{ asset('images/8.jpeg') }}" alt="Gedung Kabupaten Garut">
            <img class="bg-photo" src="{{ asset('images/9.jpeg') }}" alt="Gedung Kabupaten Garut">
            <div class="overlay"></div>
            <div class="slide-dots" id="slideDots">
                <button class="active" onclick="goToSlide(0)" aria-label="Foto 1"></button>
                <button onclick="goToSlide(1)" aria-label="Foto 2"></button>
                <button onclick="goToSlide(2)" aria-label="Foto 3"></button>
            </div>
            <div class="left-content">
                <h1>SAPA GARUT</h1>
                <h2 class="sub">Sistem Pengelolaan Aduan Garut</h2>
                <div class="accent-bar"></div>
                <p>
                    Solusi digital untuk mengelola, memantau, dan merekap data pengaduan
                    masyarakat guna meningkatkan kualitas pelayanan publik Kabupaten Garut.
                </p>
            </div>
        </div>

        <!-- ================= RIGHT: MINT PANEL WITH FLOATING WHITE CARD ================= -->
        <div class="right">
            <div class="form-card">
                <h2>Buat Akun Baru</h2>
                <p class="subtitle">Lengkapi data diri Anda untuk memulai pendaftaran.</p>

                @if ($errors->any())
                <div class="alert-box alert-error">
                    {{ $errors->first() }}
                </div>
                @endif

                @if (session('status'))
                <div class="alert-box alert-success">
                    {{ session('status') }}
                </div>
                @endif

                <form method="POST" action="{{ route('register') }}" novalidate>
                    @csrf

                    <div class="field">
                        <label for="nip">NIP (Nomor Induk Pegawai)</label>
                        <div class="input-wrap">
                            <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="4" y="4" width="16" height="16" rx="2" />
                                <path d="M9 4v3h6V4" />
                                <circle cx="12" cy="13" r="2" />
                            </svg>
                            <input
                                type="text"
                                id="nip"
                                name="nip"
                                placeholder="Contoh: 198501011XXXXXXXXX"
                                value="{{ old('nip') }}"
                                inputmode="numeric"
                                autocomplete="off"
                                required
                                autofocus>
                        </div>
                        @error('nip')
                        <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="name">Nama Lengkap</label>
                        <div class="input-wrap">
                            <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="8" r="4" />
                                <path d="M4 20c0-4.4 3.6-7 8-7s8 2.6 8 7" />
                            </svg>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                placeholder="Nama"
                                value="{{ old('name') }}"
                                autocomplete="name"
                                required>
                        </div>
                        @error('name')
                        <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="email">Alamat Email</label>
                        <div class="input-wrap">
                            <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="3" y="5" width="18" height="14" rx="2" />
                                <path d="M3 6.5 12 13l9-6.5" />
                            </svg>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="nama@gmail.com"
                                value="{{ old('email') }}"
                                autocomplete="email"
                                required>
                        </div>
                        @error('email')
                        <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password">Kata Sandi</label>
                        <div class="input-wrap">
                            <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="5" y="11" width="14" height="9" rx="2" />
                                <path d="M8 11V7a4 4 0 0 1 8 0v4" />
                            </svg>
                            <input type="password" id="password" name="password" placeholder="Kata sandi" required
                                autocomplete="new-password"
                                onfocus="document.getElementById('passHintPopup').classList.add('show')"
                                onblur="document.getElementById('passHintPopup').classList.remove('show')"
                                oninput="checkPasswordLength(this.value)">
                            <button type="button" class="toggle-pass" aria-label="Tampilkan password" onclick="togglePassword()">
                                <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </button>
                            <div class="pass-hint-popup" id="passHintPopup">
                                <div class="req" id="reqLength">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path d="M20 6 9 17l-5-5" />
                                    </svg>
                                    <span>Minimal 8 karakter</span>
                                </div>
                            </div>
                        </div>
                        @error('password')
                        <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="submit">
                        Daftar Sekarang
                    </button>
                </form>

                <p class="signup-hint">
                    Sudah punya akun? <a href="{{ route('login') }}" class="link">Masuk</a>
                </p>
            </div>

        </div>
    </div>

    <script>
        // ---- Toggle lihat/sembunyikan password ----
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';

            icon.innerHTML = isHidden
                ? '<path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a19.9 19.9 0 0 1 5.06-6.06M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a19.86 19.86 0 0 1-3.22 4.44M14.12 14.12a3 3 0 1 1-4.24-4.24" stroke-linecap="round" stroke-linejoin="round"/><line x1="1" y1="1" x2="23" y2="23" stroke-linecap="round"/>'
                : '<path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/>';
        }

        // ---- Popup cek panjang password ----
        function checkPasswordLength(value) {
            const req = document.getElementById('reqLength');
            if (value.length >= 8) {
                req.classList.add('ok');
            } else {
                req.classList.remove('ok');
            }
        }

        // ---- Slideshow foto latar (otomatis + bisa klik dots) ----
        (function() {
            const photos = document.querySelectorAll('#photoStage img.bg-photo');
            const dots = document.querySelectorAll('#slideDots button');
            if (photos.length <= 1) return;

            let current = 0;
            const intervalMs = 5000; // ganti foto tiap 5 detik, ubah sesuai kebutuhan
            let timer = setInterval(nextSlide, intervalMs);

            function showSlide(index) {
                photos[current].classList.remove('active');
                dots[current].classList.remove('active');
                current = index;
                photos[current].classList.add('active');
                dots[current].classList.add('active');
            }

            function nextSlide() {
                showSlide((current + 1) % photos.length);
            }

            window.goToSlide = function(index) {
                showSlide(index);
                clearInterval(timer);
                timer = setInterval(nextSlide, intervalMs);
            };
        })();
    </script>
</body>

</html>