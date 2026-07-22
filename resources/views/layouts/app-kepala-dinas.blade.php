<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Monitoring Pengaduan') - SAPA GARUT</title>

    <!-- Tailwind CSS (CDN, tanpa build step) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: {
                            DEFAULT: '#0B3D91',
                            dark: '#082C6B',
                            light: '#1E4FA3',
                        },
                        brand: {
                            blue: '#1D4ED8',
                            lightblue: '#BFDBFE',
                            softblue: '#DBEAFE',
                        }
                    },
                    fontFamily: {
                        sans: ['"Inter"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; background:#F5F7FB; }
        .nav-item { transition: background-color .15s ease; }
        .nav-item.active { background-color: rgba(255,255,255,0.12); border-right: 4px solid #ffffff; }
        .nav-item:hover:not(.active) { background-color: rgba(255,255,255,0.06); }
        .card-shadow { box-shadow: 0 1px 3px rgba(16,24,40,.06), 0 1px 2px rgba(16,24,40,.04); }
        [x-cloak] { display: none !important; }
        /* biar transisi lebar sidebar & margin konten mulus */
        #sidebar, #main-wrapper { transition: all .25s ease; }
        .nav-label { transition: opacity .15s ease; }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js" defer></script>

    @stack('styles')
</head>
<body class="text-slate-800"
      x-data="{
          logoutModal: false,
          sidebarCollapsed: localStorage.getItem('sg_sidebar_collapsed') === 'true',
          mobileOpen: false
      }"
      x-init="$watch('sidebarCollapsed', value => localStorage.setItem('sg_sidebar_collapsed', value))">

    <div class="flex min-h-screen">

        {{-- ============ OVERLAY (MOBILE) ============ --}}
        <div x-show="mobileOpen" x-cloak
             @click="mobileOpen = false"
             class="fixed inset-0 bg-slate-900/50 z-20 md:hidden"
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        {{-- ============ SIDEBAR ============ --}}
        <aside id="sidebar"
               :class="[
                   sidebarCollapsed ? 'md:w-20' : 'md:w-72',
                   mobileOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
               ]"
               class="w-72 bg-navy text-white flex flex-col fixed inset-y-0 left-0 z-30">

            {{-- Header sidebar: logo + judul + tombol hamburger (sejajar) --}}
            <div class="flex items-center gap-3 px-4 py-6" :class="sidebarCollapsed ? 'md:justify-center md:px-0' : 'justify-between'">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-lg bg-white flex items-center justify-center overflow-hidden shrink-0">
                        <img src="{{ asset('images/10.jpeg') }}" alt="Logo SAPA GARUT" class="w-full h-full object-cover">
                    </div>
                    <div class="min-w-0" x-show="!sidebarCollapsed" x-transition x-cloak>
                        <p class="font-extrabold leading-tight tracking-wide truncate">SAPA GARUT</p>
                        <p class="text-[11px] text-blue-100/80 leading-tight">Sistem Pengelolaan Aduan</p>
                    </div>
                </div>

                {{-- Tombol Hamburger (khusus desktop, collapse jadi mode ikon) --}}
                <button type="button"
                        @click="sidebarCollapsed = !sidebarCollapsed"
                        class="hidden md:flex items-center justify-center w-8 h-8 rounded-lg text-blue-100/80 hover:bg-white/10 transition shrink-0"
                        x-show="!sidebarCollapsed" x-cloak>
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>

            {{-- Saat collapsed, hamburger dipindah ke bawah logo, tetap center --}}
            <button type="button"
                    @click="sidebarCollapsed = !sidebarCollapsed"
                    x-show="sidebarCollapsed" x-cloak
                    class="hidden md:flex items-center justify-center mx-auto mb-2 w-9 h-9 rounded-lg text-blue-100/80 hover:bg-white/10 transition">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <nav class="flex-1 mt-2 px-3 space-y-1">
                <a href="{{ route('kepala-dinas.dashboard') }}"
                   class="nav-item {{ request()->routeIs('kepala-dinas.dashboard') ? 'active' : '' }} flex items-center gap-3 px-2.5 py-2 rounded-lg text-sm {{ request()->routeIs('kepala-dinas.dashboard') ? '' : 'text-blue-100/90' }} font-medium"
                   :class="sidebarCollapsed && 'md:justify-center'"
                   title="Dashboard">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/>
                        <rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>
                    </svg>
                    <span class="nav-label" x-show="!sidebarCollapsed" x-cloak>Dashboard</span>
                </a>
                <a href="{{ route('data-pengaduan') }}"
                   class="nav-item {{ request()->routeIs('data-pengaduan') ? 'active' : '' }} flex items-center gap-3 px-2.5 py-2 rounded-lg text-sm {{ request()->routeIs('data-pengaduan') ? '' : 'text-blue-100/90' }} font-medium"
                   :class="sidebarCollapsed && 'md:justify-center'"
                   title="Data Pengaduan">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M4 5h16M4 5a2 2 0 00-2 2v9a2 2 0 002 2h11l3 3v-3h1a2 2 0 002-2V7a2 2 0 00-2-2H4z"/>
                    </svg>
                    <span class="nav-label" x-show="!sidebarCollapsed" x-cloak>Data Pengaduan</span>
                </a>
                <a href="{{ route('monitoring-skpd') }}"
                   class="nav-item {{ request()->routeIs('monitoring-skpd') ? 'active' : '' }} flex items-center gap-3 px-2.5 py-2 rounded-lg text-sm {{ request()->routeIs('monitoring-skpd') ? '' : 'text-blue-100/90' }} font-medium"
                   :class="sidebarCollapsed && 'md:justify-center'"
                   title="Monitoring SKPD">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M8 13v4M12 9v8M16 6v11"/>
                    </svg>
                    <span class="nav-label" x-show="!sidebarCollapsed" x-cloak>Monitoring SKPD</span>
                </a>

                {{-- ===== MENU LAPORAN (SINGLE LINK, BUKAN DROPDOWN) =====
                     Khusus layout Kepala Dinas: cuma 1 tujuan yaitu Arsip Laporan.
                     Beda dengan layout Pengelola yang punya submenu
                     Generate Laporan + Arsip Laporan. --}}
                <a href="{{ route('laporan.pengaduan') }}"
                   class="nav-item {{ request()->routeIs('laporan.pengaduan') ? 'active' : '' }} flex items-center gap-3 px-2.5 py-2 rounded-lg text-sm {{ request()->routeIs('laporan.pengaduan') ? '' : 'text-blue-100/90' }} font-medium"
                   :class="sidebarCollapsed && 'md:justify-center'"
                   title="Laporan">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M7 3h7l5 5v13a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1z"/><path d="M9 12h6M9 16h6M9 8h2"/>
                    </svg>
                    <span class="nav-label" x-show="!sidebarCollapsed" x-cloak>Laporan</span>
                </a>
                {{-- ===== END MENU LAPORAN ===== --}}

                {{-- Catatan: Menu "Kelola Banner" & "Manajemen Pengguna" SENGAJA
                     tidak ditampilkan di layout Kepala Dinas ini. --}}

                <a href="{{ route('profil') }}"
                   class="nav-item {{ request()->routeIs('profil') ? 'active' : '' }} flex items-center gap-3 px-2.5 py-2 rounded-lg text-sm {{ request()->routeIs('profil') ? '' : 'text-blue-100/90' }} font-medium"
                   :class="sidebarCollapsed && 'md:justify-center'"
                   title="Profil">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>
                    </svg>
                    <span class="nav-label" x-show="!sidebarCollapsed" x-cloak>Profil</span>
                </a>
            </nav>

            <div class="px-3 pb-6">
                <button type="button" @click="logoutModal = true"
                        class="nav-item w-full flex items-center gap-3 px-2.5 py-2 rounded-lg text-sm text-blue-100/90 font-medium"
                        :class="sidebarCollapsed && 'md:justify-center'"
                        title="Keluar">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M17 16l4-4m0 0l-4-4m4 4H7"/><path d="M7 4H5a2 2 0 00-2 2v12a2 2 0 002 2h2"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak>Keluar</span>
                </button>
            </div>
        </aside>

        {{-- Form logout tersembunyi, disubmit lewat modal konfirmasi --}}
        <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
            @csrf
        </form>

        {{-- ============ MAIN CONTENT ============ --}}
        <div id="main-wrapper"
             :class="sidebarCollapsed ? 'md:ml-20' : 'md:ml-72'"
             class="flex-1 ml-0 min-w-0">

            {{-- TOP BAR --}}
            <header class="bg-white border-b border-slate-200 px-4 md:px-8 py-4 flex items-center justify-between sticky top-0 z-20">
                <div class="flex items-center gap-3 w-full">
                    {{-- Tombol Hamburger (mobile) --}}
                    <button type="button" @click="mobileOpen = !mobileOpen"
                            class="md:hidden shrink-0 w-10 h-10 flex items-center justify-center rounded-lg text-navy hover:bg-slate-100">
                        <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg x-show="mobileOpen" x-cloak class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <div class="relative w-full max-w-md">
                        <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/>
                        </svg>
                        <input type="text" placeholder="{{ $searchPlaceholder ?? 'Cari nomor pengaduan, SKPD...' }}"
                               class="w-full bg-slate-50 border border-slate-200 rounded-full pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-navy/30">
                    </div>
                </div>

                <div class="flex items-center gap-5 ml-6 shrink-0">
                    <button class="relative">
                        <svg class="w-6 h-6 text-navy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-4-5.65V5a2 2 0 10-4 0v.35A6 6 0 006 11v3.2a2 2 0 01-.6 1.4L4 17h5"/>
                            <path d="M9 17a3 3 0 006 0"/>
                        </svg>
                        <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
                    </button>

                    <a href="{{ route('profil') }}" class="text-navy hidden sm:inline-flex">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09a1.65 1.65 0 00-1-1.51 1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09a1.65 1.65 0 001.51-1 1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>
                        </svg>
                    </a>

                    <a href="{{ route('profil') }}" class="flex items-center gap-3">
                        <div class="text-right leading-tight hidden sm:block">
                            <p class="text-sm font-semibold text-slate-800">{{ auth()->user()->nama_lengkap ?? auth()->user()->name }}</p>
                            <p class="text-xs text-slate-400">{{ auth()->user()->peran ?? 'Kepala Dinas' }}</p>
                        </div>
                        @if(auth()->user()->foto_profil ?? false)
                            <img src="{{ asset('storage/'.auth()->user()->foto_profil) }}"
                                 alt="{{ auth()->user()->nama_lengkap ?? auth()->user()->name }}"
                                 class="w-10 h-10 rounded-full object-cover">
                        @else
                            <div class="w-10 h-10 rounded-full bg-navy text-white flex items-center justify-center text-sm font-bold">
                                {{ strtoupper(substr(auth()->user()->nama_lengkap ?? auth()->user()->name, 0, 1)) }}
                            </div>
                        @endif
                    </a>
                </div>
            </header>

            <main class="p-4 md:p-8 overflow-x-hidden">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- ============ MODAL KONFIRMASI LOGOUT ============ --}}
    <div x-show="logoutModal" x-cloak
         class="fixed inset-0 bg-slate-900/50 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="logoutModal = false">
        <div @click.outside="logoutModal = false" class="bg-white rounded-2xl w-full max-w-sm p-6 text-center">
            <div class="mx-auto mb-4 w-14 h-14 rounded-full bg-red-50 flex items-center justify-center">
                <svg class="w-7 h-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M17 16l4-4m0 0l-4-4m4 4H7"/><path d="M7 4H5a2 2 0 00-2 2v12a2 2 0 002 2h2"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-1.5">Keluar dari Akun?</h3>
            <p class="text-sm text-slate-500 mb-6">Yakin mau keluar dari SAPA GARUT? Kamu harus login lagi untuk masuk.</p>
            <div class="flex justify-center gap-3">
                <button type="button" @click="logoutModal = false"
                        class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-500 hover:bg-slate-50">
                    Batal
                </button>
                <button type="button" @click="document.getElementById('logout-form').submit()"
                        class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-red-500 hover:bg-red-600">
                    Ya, Keluar
                </button>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>