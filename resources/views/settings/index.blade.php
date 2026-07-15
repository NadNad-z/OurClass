@extends('layouts.app')

@section('title', __('Pengaturan'))

@section('styles')
<style>
    .settings-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 2rem;
        align-items: start;
    }

    @media (max-width: 968px) {
        .settings-grid {
            grid-template-columns: 1fr;
        }
    }

    .avatar-upload-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 2rem;
        position: relative;
    }

    .avatar-preview-wrapper {
        position: relative;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid var(--primary-soft);
        box-shadow: var(--shadow-md);
        margin-bottom: 1rem;
        background-color: var(--bg-app);
    }

    .avatar-preview {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    /* Hover Overlay for Profile Image */
    .avatar-view-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(9, 15, 28, 0.5);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        color: white;
        font-size: 0.7rem;
        font-weight: 600;
        opacity: 0;
        cursor: pointer;
        transition: opacity var(--transition-fast) ease;
        border-radius: 50%;
        z-index: 2;
    }

    .avatar-preview-wrapper:hover .avatar-view-overlay {
        opacity: 1;
    }

    .avatar-edit-label {
        position: absolute;
        bottom: 0;
        right: 0;
        background-color: var(--primary);
        color: white;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: var(--shadow-sm);
        transition: background-color var(--transition-fast);
        border: 2px solid var(--bg-card);
        z-index: 5;
    }

    .avatar-edit-label:hover {
        background-color: var(--primary-hover);
    }

    .avatar-edit-label input {
        display: none;
    }

    /* Default Avatars Grid CSS */
    .default-avatars-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 0.6rem;
        justify-content: center;
        max-width: 340px;
        margin: 0 auto;
    }

    .default-avatar-item {
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid transparent;
        cursor: pointer;
        transition: transform var(--transition-fast), border-color var(--transition-fast);
        aspect-ratio: 1;
        background-color: var(--bg-app);
    }

    .default-avatar-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .default-avatar-item:hover {
        transform: scale(1.15);
    }

    .default-avatar-item.active {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-soft);
        transform: scale(1.05);
    }

    /* Lightbox Modal CSS */
    .lightbox-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(9, 15, 28, 0.85);
        backdrop-filter: blur(8px);
        z-index: 2000;
        justify-content: center;
        align-items: center;
        opacity: 0;
        transition: opacity var(--transition-normal) ease;
    }

    .lightbox-modal.show {
        display: flex;
        opacity: 1;
    }

    .lightbox-content-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        max-width: 90%;
        max-height: 80%;
    }

    .lightbox-image {
        max-width: 320px;
        max-height: 320px;
        width: 80vw;
        height: 80vw;
        border-radius: 50%;
        object-fit: cover;
        border: 6px solid rgba(255, 255, 255, 0.1);
        box-shadow: var(--shadow-lg);
        transform: scale(0.9);
        transition: transform var(--transition-normal) ease;
    }

    .lightbox-modal.show .lightbox-image {
        transform: scale(1);
    }

    .lightbox-caption {
        color: var(--text-white);
        margin-top: 1.5rem;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .lightbox-close {
        position: absolute;
        top: 2rem;
        right: 2rem;
        background: none;
        border: none;
        color: var(--text-white);
        font-size: 2.5rem;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity var(--transition-fast) ease;
    }

    .lightbox-close:hover {
        opacity: 1;
    }

    .settings-title {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-main);
    }

    .readonly-field {
        background-color: var(--bg-app);
        cursor: not-allowed;
    }
</style>
@endsection

@section('content')
<div class="settings-grid">
    <!-- Profile Card -->
    <div class="card">
        <h2 class="settings-title">{{ __('Informasi Profil') }}</h2>
        
        <form action="{{ route('settings.profile') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="avatar-upload-container">
                <div class="avatar-preview-wrapper">
                    <img src="{{ $user->avatar ? (Str::startsWith($user->avatar, 'http') ? $user->avatar : asset('storage/' . $user->avatar)) : 'https://api.dicebear.com/7.x/adventurer/svg?seed=' . urlencode($user->name) }}" class="avatar-preview" id="avatar-preview-img" alt="Avatar">
                    <div class="avatar-view-overlay" id="avatar-view-trigger">
                        <i data-lucide="maximize-2" style="width: 18px; height: 18px; margin-bottom: 4px;"></i>
                        <span>{{ __('Lihat') }}</span>
                    </div>
                    <label for="avatar-input" class="avatar-edit-label" title="{{ __('Unggah Foto Baru') }}">
                        <i data-lucide="camera" style="width: 16px; height: 16px;"></i>
                        <input type="file" id="avatar-input" name="avatar" accept="image/*">
                    </label>
                </div>
                <span style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 1rem;">{{ __('Format: JPG, PNG. Maks 2MB.') }}</span>
                @error('avatar')
                    <span class="error-text" style="margin-bottom: 1rem;">{{ $message }}</span>
                @enderror

                <!-- Default Avatars Selection Grid -->
                <div class="default-avatars-section" style="width: 100%; border-top: 1px dashed var(--border-color); padding-top: 1rem;">
                    <label class="form-label" style="text-align: center; display: block; margin-bottom: 0.75rem;">{{ __('Atau Pilih Avatar Bawaan') }}</label>
                    <div class="default-avatars-grid">
                        @php
                            $defaults = [
                                'https://api.dicebear.com/7.x/adventurer/svg?seed=Felix',
                                'https://api.dicebear.com/7.x/adventurer/svg?seed=Aneka',
                                'https://api.dicebear.com/7.x/adventurer/svg?seed=Jack',
                                'https://api.dicebear.com/7.x/adventurer/svg?seed=Luna',
                                'https://api.dicebear.com/7.x/adventurer/svg?seed=Oliver',
                                'https://api.dicebear.com/7.x/adventurer/svg?seed=Zoe',
                                // Tambahan 4 avatar baru
                                'https://api.dicebear.com/9.x/avataaars/svg?seed=Aisyah&top=hijab', // Cewek berkerudung
                                'https://api.dicebear.com/9.x/avataaars/svg?seed=Fadil&top=turban', // Cowo berpeci
                                'https://api.dicebear.com/9.x/micah/svg?seed=Anak', // Anak-anak
                                'https://api.dicebear.com/9.x/personas/svg?seed=Kakek' // Orang tua
                            ];
                        @endphp
                        @foreach($defaults as $index => $avatarUrl)
                            <div class="default-avatar-item {{ $user->avatar === $avatarUrl ? 'active' : '' }}" data-avatar-url="{{ $avatarUrl }}">
                                <img src="{{ $avatarUrl }}" alt="Default Avatar {{ $index + 1 }}">
                            </div>
                        @endforeach
                    </div>
                    <input type="hidden" name="default_avatar" id="default-avatar-hidden-input" value="{{ Str::startsWith($user->avatar, 'http') ? $user->avatar : '' }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="name">{{ __('Nama Lengkap') }}</label>
                <div class="input-wrapper">
                    <i data-lucide="user"></i>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>
                @error('name')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="email">{{ __('Email') }}</label>
                <div class="input-wrapper">
                    <i data-lucide="mail"></i>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>
                @error('email')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="phone">{{ __('Nomor Telepon') }}</label>
                <div class="input-wrapper">
                    <i data-lucide="phone"></i>
                    <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="{{ __('Contoh: 08123456789') }}">
                </div>
                @error('phone')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="nim_nip">{{ $user->role === 'dosen' ? __('NIP') : __('NIM') }}</label>
                <div class="input-wrapper">
                    <i data-lucide="key-round"></i>
                    <input type="text" id="nim_nip" class="form-control readonly-field" value="{{ $user->nim_nip ?? '-' }}" readonly>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="role">{{ __('Peran / Hak Akses') }}</label>
                <div class="input-wrapper">
                    <i data-lucide="shield"></i>
                    <input type="text" id="role" class="form-control readonly-field" style="text-transform: capitalize;" value="{{ __($user->role) }}" readonly>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">
                <i data-lucide="save"></i>
                {{ __('Simpan Perubahan') }}
            </button>
        </form>
    </div>

    <!-- Password Card -->
    <div class="card">
        <h2 class="settings-title">{{ __('Keamanan & Password') }}</h2>
        
        <form action="{{ route('settings.password') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label class="form-label" for="current_password">{{ __('Password Saat Ini') }}</label>
                <div class="input-wrapper">
                    <i data-lucide="lock"></i>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                </div>
                @error('current_password')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="new_password">{{ __('Password Baru') }}</label>
                <div class="input-wrapper">
                    <i data-lucide="key"></i>
                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                </div>
                @error('new_password')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="new_password_confirmation">{{ __('Konfirmasi Password Baru') }}</label>
                <div class="input-wrapper">
                    <i data-lucide="check-square"></i>
                    <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-control" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 1rem; background-color: var(--secondary); box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2);">
                <i data-lucide="lock-keyhole"></i>
                {{ __('Perbarui Password') }}
            </button>
        </form>
    </div>
</div>

<!-- Lightbox Modal -->
<div class="lightbox-modal" id="lightbox-modal">
    <button class="lightbox-close" id="lightbox-close-btn">&times;</button>
    <div class="lightbox-content-wrapper">
        <img class="lightbox-image" id="lightbox-target-img" src="" alt="Enlarged Profile Avatar">
        <div class="lightbox-caption">{{ __('Foto Profil Anda') }}</div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Image Upload Preview
    document.getElementById('avatar-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('avatar-preview-img').setAttribute('src', event.target.result);
                // Clear selected default avatar indicator
                document.querySelectorAll('.default-avatar-item').forEach(item => {
                    item.classList.remove('active');
                });
                document.getElementById('default-avatar-hidden-input').value = '';
            };
            reader.readAsDataURL(file);
        }
    });

    // Default Avatar Selection
    const defaultAvatarItems = document.querySelectorAll('.default-avatar-item');
    const defaultAvatarHiddenInput = document.getElementById('default-avatar-hidden-input');
    const avatarPreviewImg = document.getElementById('avatar-preview-img');
    const avatarInput = document.getElementById('avatar-input');

    defaultAvatarItems.forEach(item => {
        item.addEventListener('click', function() {
            const avatarUrl = this.getAttribute('data-avatar-url');
            
            // Highlight selected avatar
            defaultAvatarItems.forEach(el => el.classList.remove('active'));
            this.classList.add('active');

            // Update preview image
            avatarPreviewImg.setAttribute('src', avatarUrl);

            // Set hidden input value
            defaultAvatarHiddenInput.value = avatarUrl;

            // Clear file input to prioritize default avatar selection
            avatarInput.value = '';
        });
    });

    // Lightbox Modal Logic
    const lightboxModal = document.getElementById('lightbox-modal');
    const lightboxCloseBtn = document.getElementById('lightbox-close-btn');
    const avatarViewTrigger = document.getElementById('avatar-view-trigger');
    const lightboxTargetImg = document.getElementById('lightbox-target-img');

    avatarViewTrigger.addEventListener('click', function(e) {
        // Set lightbox image src to current preview src
        const currentSrc = avatarPreviewImg.getAttribute('src');
        lightboxTargetImg.setAttribute('src', currentSrc);

        // Show modal
        lightboxModal.classList.add('show');
    });

    lightboxCloseBtn.addEventListener('click', function() {
        lightboxModal.classList.remove('show');
    });

    // Close lightbox on click outside the image
    lightboxModal.addEventListener('click', function(e) {
        if (e.target === lightboxModal) {
            lightboxModal.classList.remove('show');
        }
    });
</script>
@endsection
