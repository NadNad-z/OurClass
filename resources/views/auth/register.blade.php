<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun OurClass</title>
    
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
                <h1>Mulai Langkah Belajar Anda Bersama Kami.</h1>
                <p>Bergabunglah dengan ribuan pengajar dan pelajar lainnya untuk membuat manajemen kelas digital yang terintegrasi secara profesional.</p>
            </div>
            
            <div class="banner-footer">
                &copy; {{ date('Y') }} OurClass Team. Hak Cipta Dilindungi.
            </div>
        </div>

        <!-- Form Side -->
        <div class="auth-form-side" style="padding-top: 2rem; padding-bottom: 2rem;">
            <div class="auth-form-card">
                
                <div class="auth-header" style="margin-bottom: 1.5rem;">
                    <h2>Buat Akun Baru</h2>
                    <p>Sudah punya akun? <a href="{{ route('login') }}">Masuk disini</a></p>
                </div>

                <form action="{{ route('register') }}" method="POST">
                    @csrf
                    
                    <!-- Nama Lengkap -->
                    <div class="form-group">
                        <label for="name" class="form-label">Nama Lengkap</label>
                        <div class="input-wrapper">
                            <i data-lucide="user"></i>
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" placeholder="Contoh: Budi Santoso, M.T." required autofocus>
                        </div>
                        @error('name')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email" class="form-label">Alamat Email</label>
                        <div class="input-wrapper">
                            <i data-lucide="mail"></i>
                            <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" placeholder="nama@kampus.ac.id" required>
                        </div>
                        @error('email')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Role & NIM/NIP in dynamic grid -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <!-- Role -->
                        <div class="form-group">
                            <label for="role" class="form-label">Peran</label>
                            <div class="input-wrapper">
                                <i data-lucide="users"></i>
                                <select name="role" id="role" class="form-control" required onchange="handleRoleChange()">
                                    <option value="mahasiswa" {{ old('role') === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                                    <option value="siswa" {{ old('role') === 'siswa' ? 'selected' : '' }}>Siswa</option>
                                    <option value="dosen" {{ old('role') === 'dosen' ? 'selected' : '' }}>Dosen / Guru</option>
                                </select>
                            </div>
                            @error('role')
                                <span class="error-text">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- NIM/NIP -->
                        <div class="form-group">
                            <label for="nim_nip" class="form-label" id="label-id">NIM</label>
                            <div class="input-wrapper">
                                <i data-lucide="hash"></i>
                                <input type="text" name="nim_nip" id="nim_nip" class="form-control" value="{{ old('nim_nip') }}" placeholder="A11.2023.12345">
                            </div>
                            @error('nim_nip')
                                <span class="error-text">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- No. Telp -->
                    <div class="form-group">
                        <label for="phone" class="form-label">No. Telepon (WhatsApp)</label>
                        <div class="input-wrapper">
                            <i data-lucide="phone"></i>
                            <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}" placeholder="081234567890">
                        </div>
                        @error('phone')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">Kata Sandi</label>
                        <div class="input-wrapper">
                            <i data-lucide="lock"></i>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 8 karakter" required>
                        </div>
                        @error('password')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Password Confirmation -->
                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label>
                        <div class="input-wrapper">
                            <i data-lucide="lock"></i>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Ulangi sandi" required>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary" style="margin-top: 0.5rem;">
                        <span>Daftar Akun</span>
                        <i data-lucide="user-plus" style="width: 18px; height: 18px;"></i>
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

        // Dynamically toggle NIM/NIP placeholder
        function handleRoleChange() {
            const roleSelect = document.getElementById('role');
            const labelId = document.getElementById('label-id');
            const inputId = document.getElementById('nim_nip');
            
            if (roleSelect.value === 'dosen') {
                labelId.textContent = 'NIP / NIDN';
                inputId.placeholder = '19880703...';
            } else if (roleSelect.value === 'siswa') {
                labelId.textContent = 'NIS';
                inputId.placeholder = '12345678';
            } else {
                labelId.textContent = 'NIM';
                inputId.placeholder = 'A11.2023.12345';
            }
        }
        
        // Run once on load
        handleRoleChange();
    </script>
</body>
</html>
