<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Sandi - OurClass</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

    <div class="auth-wrapper">
        <!-- Banner Side -->
        <div class="auth-banner-side">
            <div class="banner-logo">
                <img src="{{ asset('images/logo.png') }}" alt="Logo">
                <span>OurClass</span>
            </div>
            
            <div class="banner-content">
                <h1>Lupa Kata Sandi?</h1>
                <p>Tenang, kami akan membantu Anda memulihkan akses ke akun OurClass Anda. Masukkan email yang terdaftar untuk melanjutkan.</p>
            </div>
            
            <div class="banner-footer">
                &copy; {{ date('Y') }} OurClass Team. Hak Cipta Dilindungi.
            </div>
        </div>

        <!-- Form Side -->
        <div class="auth-form-side">
            <div class="auth-form-card">
                
                <div class="auth-header">
                    <h2>Pemulihan Akun</h2>
                    <p>Masukkan email yang terdaftar untuk mereset kata sandi Anda.</p>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger">
                        <i data-lucide="alert-circle" style="width: 18px; height: 18px;"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-success">
                        <i data-lucide="info" style="width: 18px; height: 18px;"></i>
                        <div>{{ session('info') }}</div>
                    </div>
                @endif

                <form action="{{ route('password.verify') }}" method="POST">
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

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary" style="margin-top: 0.5rem;">
                        <span>Lanjutkan</span>
                        <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
                    </button>
                </form>

                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="{{ route('login') }}" style="color: var(--primary); font-weight: 600; font-size: 0.9rem; text-decoration: none;">
                        <i data-lucide="arrow-left" style="width: 14px; height: 14px; display: inline-block; vertical-align: middle; margin-right: 0.25rem;"></i>
                        Kembali ke Login
                    </a>
                </div>

            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
        const currentTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', currentTheme);
    </script>
</body>
</html>
