<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Sandi - OurClass</title>
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
                <h1>Buat Kata Sandi Baru</h1>
                <p>Masukkan kata sandi baru untuk akun Anda. Pastikan minimal 8 karakter dan mudah diingat.</p>
            </div>
            
            <div class="banner-footer">
                &copy; {{ date('Y') }} OurClass Team. Hak Cipta Dilindungi.
            </div>
        </div>

        <!-- Form Side -->
        <div class="auth-form-side">
            <div class="auth-form-card">
                
                <div class="auth-header">
                    <h2>Reset Kata Sandi</h2>
                    <p>Akun: <strong>{{ session('reset_email') }}</strong></p>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger">
                        <i data-lucide="alert-circle" style="width: 18px; height: 18px;"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                <form action="{{ route('password.update') }}" method="POST">
                    @csrf

                    <!-- New Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">Kata Sandi Baru</label>
                        <div class="input-wrapper">
                            <i data-lucide="lock"></i>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 8 karakter" required autofocus>
                        </div>
                        @error('password')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi Baru</label>
                        <div class="input-wrapper">
                            <i data-lucide="lock"></i>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Ulangi kata sandi baru" required>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary" style="margin-top: 0.5rem;">
                        <span>Simpan Kata Sandi Baru</span>
                        <i data-lucide="check" style="width: 18px; height: 18px;"></i>
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
