@extends('layouts.app')

@section('title', 'Kelas Saya')

@section('styles')
<style>
    .class-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-top: 1.5rem;
    }
    .class-card {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 220px;
        position: relative;
        overflow: hidden;
        border-top: 4px solid var(--primary);
    }
    .class-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 120px;
        height: 120px;
        background: radial-gradient(circle, var(--class-color-alpha) 0%, transparent 70%);
        opacity: 0.5;
        pointer-events: none;
    }
    .class-code-badge {
        font-family: monospace;
        background-color: var(--primary-soft);
        color: var(--primary);
        padding: 0.25rem 0.5rem;
        border-radius: var(--border-radius-sm);
        font-size: 0.85rem;
        font-weight: 700;
        letter-spacing: 0.05em;
    }
    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: all var(--transition-fast);
    }
    .modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    .modal-container {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        box-shadow: var(--shadow-lg);
        transform: translateY(20px);
        transition: all var(--transition-normal);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .modal-overlay.active .modal-container {
        transform: translateY(0);
    }
    .modal-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .modal-title {
        font-weight: 700;
        font-size: 1.15rem;
    }
    .modal-close {
        background: none;
        border: none;
        cursor: pointer;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.25rem;
        border-radius: 50%;
        transition: background var(--transition-fast);
    }
    .modal-close:hover {
        background-color: var(--primary-soft);
        color: var(--text-main);
    }
    .modal-body {
        padding: 1.5rem;
        overflow-y: auto;
        flex: 1;
    }
    .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        background-color: var(--bg-app);
    }
    .btn-secondary {
        background-color: transparent;
        border: 1px solid var(--border-color);
        color: var(--text-main);
    }
    .btn-secondary:hover {
        background-color: var(--primary-soft);
    }
</style>
@endsection

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
    <div>
        <h1 style="font-weight: 800; font-size: 2.2rem; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
            {{ __('Kelas Saya') }} 📚
        </h1>
        <p style="color: var(--text-muted);">
            {{ __('Daftar kelas digital yang Anda ikuti sebagai') }} {{ __($user->role) }}.
        </p>
    </div>
    
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
        <button class="btn btn-primary" onclick="openModal('createClassModal')">
            <i data-lucide="plus" style="width: 18px; height: 18px;"></i>
            <span>{{ __('Buat Kelas') }}</span>
        </button>
        <button class="btn btn-secondary" onclick="openModal('joinClassModal')">
            <i data-lucide="log-in" style="width: 18px; height: 18px;"></i>
            <span>{{ __('Gabung Kelas') }}</span>
        </button>
    </div>
</div>

@if($classes->isEmpty())
    <!-- Empty State -->
    <div class="card" style="text-align: center; padding: 5rem 2rem; margin-top: 2rem;">
        <i data-lucide="book-open" style="width: 64px; height: 64px; color: var(--text-muted); margin-bottom: 1.5rem;"></i>
        <h3 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 0.5rem;">{{ __('Belum ada kelas terdaftar') }}</h3>
        <p style="color: var(--text-muted); font-size: 0.95rem; max-width: 420px; margin: 0 auto 2rem;">
            {{ __('Anda belum memiliki kelas. Buat kelas baru untuk belajar mandiri atau kerja kelompok, atau gabung ke kelas yang sudah ada menggunakan kode unik.') }}
        </p>
        <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;">
            <button class="btn btn-primary" style="width: auto; padding-left: 2rem; padding-right: 2rem;" onclick="openModal('createClassModal')">
                <i data-lucide="plus" style="width: 18px; height: 18px;"></i>
                <span>{{ __('Buat Kelas Baru') }}</span>
            </button>
            <button class="btn btn-secondary" style="width: auto; padding-left: 2rem; padding-right: 2rem;" onclick="openModal('joinClassModal')">
                <i data-lucide="log-in" style="width: 18px; height: 18px;"></i>
                <span>{{ __('Gabung Kelas') }}</span>
            </button>
        </div>
    </div>
@else
    <!-- Classes Grid List -->
    <div class="class-grid">
        @foreach($classes as $class)
            @php
                // Convert hex to rgb to make translucent color overlay
                $hex = str_replace('#', '', $class->color ?: '#10B981');
                if(strlen($hex) == 3) {
                    $r = hexdec(substr($hex,0,1).substr($hex,0,1));
                    $g = hexdec(substr($hex,1,1).substr($hex,1,1));
                    $b = hexdec(substr($hex,2,1).substr($hex,2,1));
                } else {
                    $r = hexdec(substr($hex,0,2));
                    $g = hexdec(substr($hex,2,2));
                    $b = hexdec(substr($hex,4,2));
                }
                $rgbAlpha = "rgba($r, $g, $b, 0.25)";
            @endphp
            <div class="card class-card card-hover" style="--primary: {{ $class->color }}; --class-color-alpha: {{ $rgbAlpha }}; cursor: pointer;" onclick="window.location.href='{{ route('classes.show', $class->id) }}'">
                
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem; gap: 0.5rem; flex-wrap: wrap;">
                        <span class="class-code-badge" style="background-color: {{ $rgbAlpha }}; color: {{ $class->color }};">
                            {{ $class->kode_unik }}
                        </span>
                        @if($class->is_private)
                            <span style="font-size: 0.75rem; color: #ffffff; font-weight: 700; background-color: #f97316; padding: 0.35rem 0.75rem; border-radius: 9999px;">
                                {{ __('Privat') }}
                            </span>
                        @endif
                        @if($class->semester || $class->tahun_ajaran)
                            <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">
                                {{ $class->semester }} / {{ $class->tahun_ajaran }}
                            </span>
                        @endif
                    </div>
                    
                    <h3 style="font-size: 1.2rem; font-weight: 800; line-height: 1.3; margin-bottom: 0.5rem; letter-spacing: -0.01em;">
                        <a href="{{ route('classes.show', $class->id) }}" style="text-decoration: none; color: inherit; transition: color var(--transition-fast);" onmouseover="this.style.color='{{ $class->color }}'" onmouseout="this.style.color='inherit'">
                            {{ $class->nama_kelas }}
                        </a>
                    </h3>
                    
                    @if($class->mata_kuliah)
                        <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.75rem;">
                            {{ $class->mata_kuliah }}
                        </div>
                    @endif
                    
                    @if($class->deskripsi)
                        <p style="font-size: 0.85rem; color: var(--text-muted); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.5;">
                            {{ $class->deskripsi }}
                        </p>
                    @endif
                </div>

                <div style="margin-top: 1.5rem; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid var(--border-color); padding-top: 0.75rem;">
                    <!-- Teacher Info -->
                    <div style="display: flex; align-items: center; gap: 0.5rem; overflow: hidden;" onclick="event.stopPropagation();">
                        <img src="{{ $class->admin->avatar_url }}" style="width: 28px; height: 28px; border-radius: 50%; background-color: var(--primary-soft);" alt="Dosen">
                        <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 130px;" title="{{ $class->admin->name }}">
                            {{ $class->admin->name }}
                        </span>
                    </div>

                    <!-- Members Count -->
                    <div style="display: flex; align-items: center; gap: 0.35rem; color: var(--text-muted); font-size: 0.8rem; font-weight: 500;">
                        <i data-lucide="users" style="width: 16px; height: 16px;"></i>
                        <span>{{ $class->members_count }} {{ __('Anggota') }}</span>
                    </div>
                </div>

            </div>
        @endforeach
    </div>
@endif

<!-- Modals -->
    <!-- Create Class Modal -->
    <div class="modal-overlay" id="createClassModal">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">{{ __('Buat Kelas Baru') }}</h3>
                <button class="modal-close" onclick="closeModal('createClassModal')">
                    <i data-lucide="x" style="width: 20px; height: 20px;"></i>
                </button>
            </div>
            
            <form action="{{ route('classes.store') }}" method="POST" style="display: flex; flex-direction: column; overflow: hidden; flex: 1;">
                @csrf
                <div class="modal-body">
                    <!-- Nama Kelas -->
                    <div class="form-group">
                        <label for="nama_kelas" class="form-label">{{ __('Nama Kelas') }} *</label>
                        <input type="text" name="nama_kelas" id="nama_kelas" class="form-control form-control-noicon" placeholder="{{ __('Contoh: Pemrograman Web Lanjut - A') }}" required>
                    </div>

                    <!-- Mata Kuliah -->
                    <div class="form-group">
                        <label for="mata_kuliah" class="form-label">{{ __('Mata Kuliah') }}</label>
                        <input type="text" name="mata_kuliah" id="mata_kuliah" class="form-control form-control-noicon" placeholder="{{ __('Contoh: Rekayasa Perangkat Lunak') }}">
                    </div>

                    <div class="form-group">
                        <label for="is_private" class="form-label">{{ __('Mode Kelas') }}</label>
                        <select name="is_private" id="is_private" class="form-control form-control-noicon">
                            <option value="0" selected>{{ __('Publik (Bisa bergabung dengan kode unik)') }}</option>
                            <option value="1">{{ __('Privat (Hanya pemilik yang dapat melihat & mengatur)') }}</option>
                        </select>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">{{ __('Pilih mode kelas untuk kerja kelompok atau belajar mandiri.') }}</span>
                    </div>

                    <!-- Deskripsi -->
                    <div class="form-group">
                        <label for="deskripsi" class="form-label">{{ __('Deskripsi Kelas') }}</label>
                        <textarea name="deskripsi" id="deskripsi" class="form-control form-control-noicon" style="height: 100px; resize: none;" placeholder="{{ __('Tulis deskripsi kelas, aturan kuliah, atau info penunjang lainnya...') }}"></textarea>
                    </div>

                    <!-- Room & Semester / Academic Year in dynamic grid -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="ruangan" class="form-label">{{ __('Ruangan') }}</label>
                            <input type="text" name="ruangan" id="ruangan" class="form-control form-control-noicon" placeholder="{{ __('Contoh: H.4.5') }}">
                        </div>
                        <div class="form-group">
                            <label for="color" class="form-label">{{ __('Warna Tema Kelas') }}</label>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="color" name="color" id="color" value="#10B981" class="form-control form-control-noicon" style="padding: 0.2rem; height: 42px; width: 60px; cursor: pointer;">
                                <span style="font-size: 0.8rem; color: var(--text-muted);">{{ __('Pilih aksen kelas') }}</span>
                            </div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="semester" class="form-label">{{ __('Semester') }}</label>
                            <input type="text" name="semester" id="semester" class="form-control form-control-noicon" placeholder="{{ __('Contoh: Gasal (5)') }}">
                        </div>
                        <div class="form-group">
                            <label for="tahun_ajaran" class="form-label">{{ __('Tahun Ajaran') }}</label>
                            <input type="text" name="tahun_ajaran" id="tahun_ajaran" class="form-control form-control-noicon" placeholder="{{ __('Contoh: 2026/2027') }}">
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" style="width: auto;" onclick="closeModal('createClassModal')">{{ __('Batal') }}</button>
                    <button type="submit" class="btn btn-primary" style="width: auto; padding-left: 2rem; padding-right: 2rem;">{{ __('Simpan Kelas') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Join Class Modal -->
    <div class="modal-overlay" id="joinClassModal">
        <div class="modal-container" style="max-width: 400px;">
            <div class="modal-header">
                <h3 class="modal-title">{{ __('Gabung dengan Kelas') }}</h3>
                <button class="modal-close" onclick="closeModal('joinClassModal')">
                    <i data-lucide="x" style="width: 20px; height: 20px;"></i>
                </button>
            </div>
            
            <form action="{{ route('classes.join') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1.25rem;">
                        {{ __('Masukkan 6 digit kode unik kelas yang diberikan oleh Dosen/Pengajar Anda untuk bergabung.') }}
                    </p>
                    
                    <div class="form-group" style="margin-bottom: 0.5rem;">
                        <label for="kode_unik" class="form-label">{{ __('Kode Kelas') }}</label>
                        <input type="text" name="kode_unik" id="kode_unik" class="form-control form-control-noicon" style="font-size: 1.5rem; text-align: center; letter-spacing: 0.3em; font-family: monospace; font-weight: 800; text-transform: uppercase;" maxlength="6" placeholder="ABCXYZ" required>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" style="width: auto;" onclick="closeModal('joinClassModal')">{{ __('Batal') }}</button>
                    <button type="submit" class="btn btn-primary" style="width: auto; padding-left: 2rem; padding-right: 2rem;">{{ __('Gabung Kelas') }}</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        modal.classList.add('active');
        // Auto focus input
        setTimeout(() => {
            const input = modal.querySelector('input[type="text"]');
            if (input) input.focus();
        }, 100);
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    // Close modal on click outside container
    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal-overlay')) {
            e.target.classList.remove('active');
        }
    });
</script>
@endsection
