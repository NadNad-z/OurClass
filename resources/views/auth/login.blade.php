<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke OurClass</title>
    
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

    <div class="auth-wrapper">
        <!-- Banner Side -->
        <div class="auth-banner-side">
            <div class="banner-logo">
                <img src="{{ asset('images/logo_baru.jpeg') }}" alt="Logo">
                <span>OurClass</span>
            </div>
            
            <div class="banner-content">
                <h1>Kelas Digital Anda, Kapan Saja & Di Mana Saja.</h1>
                <p>Kelola materi pelajaran, tugas kuliah, diskusi interaktif, dan pantau perkembangan beban belajar Anda dengan visualisasi grafik yang cerdas.</p>
            </div>
            
            <div class="banner-footer">
                &copy; {{ date('Y') }} OurClass Team. Hak Cipta Dilindungi.
            </div>
        </div>

        <!-- Form Side -->
        <div class="auth-form-side">
            <div class="auth-form-card">
                
                <div class="auth-header">
                    <h2>Selamat Datang</h2>
                    <p>Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a></p>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">
                        <i data-lucide="check-circle-2" style="width: 18px; height: 18px;"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        <i data-lucide="alert-circle" style="width: 18px; height: 18px;"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    
                    <!-- Email -->
                    <div class="form-group">
                        <label for="email" class="form-label">Alamat Email</label>
                        <div class="input-wrapper">
                            <i data-lucide="mail"></i>
                            <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" placeholder="nama@kampus.ac.id" required autofocus>
                        </div>
                        @error('email')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">Kata Sandi</label>
                        <div class="input-wrapper">
                            <i data-lucide="lock"></i>
                            <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                        </div>
                        @error('password')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Helper items -->
                    <div class="form-helper">
                        <label class="checkbox-container">
                            <input type="checkbox" name="remember" id="remember">
                            <span>Ingat saya</span>
                        </label>
                        <a href="#" class="forgot-link">Lupa sandi?</a>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary">
                        <span>Masuk ke Kelas</span>
                        <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
                    </button>
                </form>

            </div>
        </div>
    </div>

    <script>
        // Init icons
        lucide.createIcons();

        // Theme check
        const currentTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', currentTheme);
    </script>
</body>
</html>
