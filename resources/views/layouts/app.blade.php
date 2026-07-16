<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - OurClass</title>
    
    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Flatpickr for Date/Time inputs -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    @yield('styles')
</head>
<body class="preload-transitions">

    <div class="app-container">
        <!-- Sidebar Overlay (Mobile) -->
        <div class="sidebar-overlay" id="sidebar-overlay"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <img src="{{ asset('images/logo.png') }}" alt="OurClass Logo">
                <span>OurClass</span>
            </div>
            
            <ul class="sidebar-menu">
                <li class="menu-item">
                    <a href="{{ route('dashboard') }}" class="menu-link {{ Route::is('dashboard') ? 'active' : '' }}" title="Dashboard">
                        <i data-lucide="layout-dashboard"></i>
                        <span>{{ __('Dashboard') }}</span>
                    </a>
                </li>
                
                <li class="menu-section">{{ __('Manajemen') }}</li>
                
                <li class="menu-item">
                    <a href="{{ route('classes.index') }}" class="menu-link {{ Route::is('classes.*') ? 'active' : '' }}" title="Kelas Saya">
                        <i data-lucide="book-open"></i>
                        <span>{{ __('Kelas Saya') }}</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="{{ route('agenda.index') }}" class="menu-link {{ Route::is('agenda.index') ? 'active' : '' }}" title="Agenda & Jadwal">
                        <i data-lucide="calendar"></i>
                        <span>{{ __('Agenda & Jadwal') }}</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="{{ route('agenda.tasks') }}" class="menu-link {{ Route::is('agenda.tasks') ? 'active' : '' }}" title="Tugas">
                        <i data-lucide="clipboard-list"></i>
                        <span>{{ __('Tugas') }}</span>
                    </a>
                </li>
                
                <li class="menu-section">{{ __('Lainnya') }}</li>
                
                <li class="menu-item">
                    <a href="{{ route('agenda.discussions') }}" class="menu-link {{ Route::is('agenda.discussions') ? 'active' : '' }}" title="Diskusi Kelas">
                        <i data-lucide="message-square"></i>
                        <span>{{ __('Diskusi Kelas') }}</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="{{ route('analytics.index') }}" class="menu-link {{ Route::is('analytics.index') ? 'active' : '' }}" title="Analisis Beban">
                        <i data-lucide="bar-chart-3"></i>
                        <span>{{ __('Analisis Beban') }}</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="{{ route('settings.index') }}" class="menu-link {{ Route::is('settings.*') ? 'active' : '' }}" title="Pengaturan">
                        <i data-lucide="settings"></i>
                        <span>{{ __('Pengaturan') }}</span>
                    </a>
                </li>
            </ul>
            
            <div style="padding: 1rem; border-top: 1px solid var(--border-color); display: flex; gap: 0.5rem; justify-content: center; margin-top: auto;">
                <a href="{{ route('lang.switch', 'id') }}" style="flex: 1; text-align: center; padding: 0.4rem; text-decoration: none; border-radius: var(--border-radius-sm); font-size: 0.85rem; font-weight: 700; background-color: {{ app()->getLocale() == 'id' ? 'var(--primary-color)' : 'var(--bg-card)' }}; color: {{ app()->getLocale() == 'id' ? 'white' : 'var(--text-muted)' }}; border: 1px solid {{ app()->getLocale() == 'id' ? 'var(--primary-color)' : 'var(--border-color)' }}; transition: all var(--transition-fast);">
                    ID
                </a>
                <a href="{{ route('lang.switch', 'en') }}" style="flex: 1; text-align: center; padding: 0.4rem; text-decoration: none; border-radius: var(--border-radius-sm); font-size: 0.85rem; font-weight: 700; background-color: {{ app()->getLocale() == 'en' ? 'var(--primary-color)' : 'var(--bg-card)' }}; color: {{ app()->getLocale() == 'en' ? 'white' : 'var(--text-muted)' }}; border: 1px solid {{ app()->getLocale() == 'en' ? 'var(--primary-color)' : 'var(--border-color)' }}; transition: all var(--transition-fast);">
                    EN
                </a>
            </div>
            
            <div class="sidebar-footer">
                <div class="user-profile-badge">
                    <img src="{{ Auth::user()->avatar ? (Str::startsWith(Auth::user()->avatar, 'http') ? Auth::user()->avatar : asset('storage/' . Auth::user()->avatar)) : 'https://api.dicebear.com/7.x/adventurer/svg?seed=' . urlencode(Auth::user()->name) }}" class="user-avatar" alt="Avatar">
                    <div class="user-info">
                        <div class="user-name">{{ Auth::user()->name }}</div>
                        <div class="user-role">{{ Auth::user()->role }}</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Wrapper -->
        <div class="content-wrapper">
            <!-- Header -->
            <header class="main-header">
                <button class="action-btn" id="sidebar-toggle" style="margin-right: 1rem;">
                    <i data-lucide="menu"></i>
                </button>
                
                <div class="header-search">
                    <i data-lucide="search"></i>
                    <input type="text" placeholder="{{ __('Cari materi, kelas, tugas...') }}">
                </div>
                
                <div class="header-actions">
                    <!-- Theme Toggle -->
                    <button class="action-btn theme-toggle" id="theme-toggle-btn" title="Ubah Tema">
                        <i data-lucide="moon" id="theme-icon"></i>
                    </button>
                    
                    <!-- Notifications -->
                    <a href="{{ route('notifications.index') }}" class="action-btn" title="Notifikasi" style="text-decoration: none;">
                        <i data-lucide="bell"></i>
                        @if(Auth::user()->unreadNotifications()->exists())
                            <span class="notification-badge"></span>
                        @endif
                    </a>
                    
                    <!-- Logout -->
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="action-btn" title="Keluar" style="color: #ef4444;">
                            <i data-lucide="log-out"></i>
                        </button>
                    </form>
                </div>
            </header>

            <!-- Main Body Content -->
            <main class="main-content">
                <!-- Status Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success">
                        <i data-lucide="check-circle-2"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger">
                        <i data-lucide="alert-circle"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Theme & Icon Scripts -->
    <script>
        // Prevent transitions on page load (Fix mobile sidebar animation glitch)
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(() => {
                document.body.classList.remove("preload-transitions");
            }, 100);
        });

        // Init Lucide Icons
        lucide.createIcons();

        // Dark/Light Theme Handler
        const htmlElement = document.documentElement;
        const themeToggleBtn = document.getElementById('theme-toggle-btn');
        const themeIcon = document.getElementById('theme-icon');

        // Check stored theme
        const currentTheme = localStorage.getItem('theme') || 'light';
        htmlElement.setAttribute('data-theme', currentTheme);
        updateThemeIcon(currentTheme);

        themeToggleBtn.addEventListener('click', () => {
            let activeTheme = htmlElement.getAttribute('data-theme');
            let newTheme = activeTheme === 'light' ? 'dark' : 'light';
            
            htmlElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
            
            // Sync with backend database via ajax if needed
            fetch('{{ url("/api/theme-update") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ theme: newTheme })
            }).catch(err => console.log('Theme sync error: ', err));
        });

        function updateThemeIcon(theme) {
            if (theme === 'dark') {
                themeIcon.setAttribute('data-lucide', 'sun');
            } else {
                themeIcon.setAttribute('data-lucide', 'moon');
            }
            lucide.createIcons();
        }

        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebarOverlay = document.getElementById('sidebar-overlay');

        // Close sidebar by default on mobile on page load
        if (window.innerWidth <= 768) {
            sidebar.classList.add('collapsed');
        }

        sidebarToggle.addEventListener('click', () => {
            const isCollapsed = sidebar.classList.toggle('collapsed');
            if (window.innerWidth <= 768) {
                if (!isCollapsed) {
                    sidebarOverlay.classList.add('active');
                } else {
                    sidebarOverlay.classList.remove('active');
                }
            }
        });

        // Close sidebar when clicking overlay
        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.add('collapsed');
            sidebarOverlay.classList.remove('active');
        });

        // Close sidebar when clicking menu link on mobile
        const menuLinks = document.querySelectorAll('.menu-link');
        menuLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    sidebar.classList.add('collapsed');
                    sidebarOverlay.classList.remove('active');
                }
            });
        });

        // Initialize Flatpickr for time inputs
        flatpickr("input[type=time]", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            altInput: true,
            altFormat: "{{ app()->getLocale() == 'id' ? 'H.i' : 'h:i K' }}",
            time_24hr: {{ app()->getLocale() == 'id' ? 'true' : 'false' }}
        });

        // Initialize Flatpickr for datetime inputs
        flatpickr("input[type=datetime-local]", {
            enableTime: true,
            dateFormat: "Y-m-d\\TH:i",
            altInput: true,
            altFormat: "{{ app()->getLocale() == 'id' ? 'd-m-Y, H.i' : 'm-d-Y, h:i K' }}",
            time_24hr: {{ app()->getLocale() == 'id' ? 'true' : 'false' }}
        });
    </script>
    
    @yield('scripts')
</body>
</html>
