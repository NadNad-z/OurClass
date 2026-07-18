@extends('layouts.app')

@section('title', $class->nama_kelas)

@section('styles')
<style>
    /* Set --class-color globally for entire page so modals outside .tab-content can use it */
    :root {
        --class-color: {{ $class->color ?: '#10B981' }};
    }
    /* Ensure btn-primary always has white text when using class-color bg */
    .btn-primary {
        color: #ffffff !important;
    }
    .class-banner {
        background: linear-gradient(135deg, var(--class-color) 0%, var(--secondary) 100%);
        border-radius: var(--border-radius-lg);
        padding: 2.5rem;
        color: var(--text-white);
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-md);
    }
    .class-banner::before {
        content: '';
        position: absolute;
        top: -30%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        filter: blur(50px);
    }
    .class-banner::after {
        content: '';
        position: absolute;
        bottom: -20%;
        left: -10%;
        width: 200px;
        height: 200px;
        background: rgba(16, 185, 129, 0.2);
        border-radius: 50%;
        filter: blur(40px);
    }
    .banner-meta {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        flex-wrap: wrap;
        font-size: 0.9rem;
        font-weight: 600;
        margin-top: 1.5rem;
        border-top: 1px solid rgba(255, 255, 255, 0.15);
        padding-top: 1.25rem;
    }
    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background-color: rgba(255, 255, 255, 0.1);
        padding: 0.4rem 0.8rem;
        border-radius: var(--border-radius-sm);
        backdrop-filter: blur(4px);
    }
    /* Tabs navigation styling */
    .tab-nav {
        display: flex;
        border-bottom: 2px solid var(--border-color);
        gap: 2rem;
        margin-bottom: 2rem;
        overflow-x: auto;
        padding-bottom: 0.25rem;
    }
    .tab-btn {
        background: none;
        border: none;
        padding: 0.75rem 0.25rem;
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-muted);
        cursor: pointer;
        position: relative;
        transition: color var(--transition-fast);
        white-space: nowrap;
    }
    .tab-btn:hover {
        color: var(--text-main);
    }
    .tab-btn.active {
        color: var(--class-color);
    }
    .tab-btn.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 3px;
        background-color: var(--class-color);
        border-radius: var(--border-radius-full);
    }
    /* Tab Pane content */
    .tab-pane {
        display: none;
    }
    .tab-pane.active {
        display: block;
        animation: fadeInUp 0.4s ease forwards;
    }
    .schedule-grid-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }
    .schedule-grid-table th {
        background-color: var(--bg-app);
        padding: 1rem;
        font-weight: 700;
        text-align: left;
        border-bottom: 2px solid var(--border-color);
    }
    .schedule-grid-table td {
        padding: 1rem;
        border-bottom: 1px solid var(--border-color);
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
        box-shadow: var(--shadow-lg);
        transform: translateY(20px);
        transition: all var(--transition-normal);
        max-height: 90vh;
        overflow-y: auto;
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
    }
    .modal-close:hover {
        background-color: var(--primary-soft);
        color: var(--text-main);
    }
</style>
@endsection

@section('content')
<!-- Back to Classes link -->
<div style="margin-bottom: 1rem;">
    <a href="{{ route('classes.index') }}" style="display: inline-flex; align-items: center; gap: 0.35rem; text-decoration: none; color: var(--text-muted); font-weight: 600; font-size: 0.9rem; transition: color var(--transition-fast);" onmouseover="this.style.color='var(--text-main)'" onmouseout="this.style.color='var(--text-muted)'">
        <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
        <span>{{ __('Kembali ke Kelas Saya') }}</span>
    </a>
</div>

<!-- Class Banner header -->
<div class="class-banner" style="--class-color: {{ $class->color ?: '#10B981' }};">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1.5rem;">
        <div>
            @if($class->mata_kuliah)
                <span style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; background-color: rgba(255,255,255,0.15); padding: 0.3rem 0.6rem; border-radius: var(--border-radius-sm); margin-bottom: 0.75rem; display: inline-block;">
                    {{ $class->mata_kuliah }}
                </span>
            @endif
            <h1 style="font-weight: 800; font-size: 2.2rem; letter-spacing: -0.02em; line-height: 1.2;">
                {{ $class->nama_kelas }}
            </h1>
                @if($class->is_private)
                    <span style="display: inline-flex; align-items: center; gap: 0.4rem; margin-top: 0.75rem; background-color: rgba(249, 115, 22, 0.12); color: #c2410c; padding: 0.4rem 0.75rem; border-radius: 9999px; font-size: 0.85rem; font-weight: 700;">
                        <i data-lucide="lock" style="width: 16px; height: 16px;"></i>
                        {{ __('Kelas Privat') }}
                    </span>
                @endif
            @if($class->admin_id === $user->id)
                <!-- Admin options delete and edit -->
                <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                    <button type="button" onclick="openModal('editClassModal')" class="action-btn" style="background-color: rgba(255, 255, 255, 0.2); color: white; width: 44px; height: 44px; border-radius: var(--border-radius-md);" title="Edit Kelas">
                        <i data-lucide="edit"></i>
                    </button>
                    <form action="{{ route('classes.destroy', $class->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelas ini secara permanen?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="action-btn" style="background-color: rgba(239, 68, 68, 0.2); color: #fee2e2; width: 44px; height: 44px; border-radius: var(--border-radius-md);" title="Hapus Kelas">
                            <i data-lucide="trash-2"></i>
                        </button>
                    </form>
                </div>
            @else
                <!-- Leave class -->
                <form action="{{ route('classes.leave', $class->id) }}" method="POST" onsubmit="return confirm('{{ __('Apakah Anda yakin ingin keluar dari kelas ini?') }}')">
                    @csrf
                    <button type="submit" class="btn" style="background-color: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); width: auto; padding: 0.6rem 1.2rem; font-size: 0.85rem;" title="Keluar Kelas">
                        <i data-lucide="log-out" style="width: 16px; height: 16px;"></i>
                        <span>{{ __('Keluar Kelas') }}</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Banner Meta Info Row -->
    <div class="banner-meta">
        <div class="meta-item">
            <i data-lucide="user" style="width: 16px; height: 16px;"></i>
            <span>{{ __('Dosen') }}: {{ $class->admin->name }}</span>
        </div>
        @if($class->admin_id === $user->id)
            <div class="meta-item" style="cursor: pointer; background-color: rgba(16, 185, 129, 0.2); border: 1px dashed rgba(255,255,255,0.4);" onclick="copyJoinLink('{{ route('classes.join.link', $class->kode_unik) }}')" title="Klik untuk menyalin link undangan">
                <i data-lucide="link" style="width: 16px; height: 16px;"></i>
                <span>{{ __('Kode:') }} {{ $class->kode_unik }}</span>
                <i data-lucide="copy" style="width: 14px; height: 14px; margin-left: 0.25rem; opacity: 0.8;"></i>
            </div>
        @endif
        @if($class->ruangan)
            <div class="meta-item">
                <i data-lucide="map-pin" style="width: 16px; height: 16px;"></i>
                <span>{{ __('Ruangan') }}: {{ $class->ruangan }}</span>
            </div>
        @endif
        @if($class->semester || $class->tahun_ajaran)
            <div class="meta-item">
                <i data-lucide="calendar" style="width: 16px; height: 16px;"></i>
                <span>{{ $class->semester }} ({{ $class->tahun_ajaran }})</span>
            </div>
        @endif
        <div class="meta-item">
            <i data-lucide="users" style="width: 16px; height: 16px;"></i>
            <span>{{ $class->members->count() }} {{ __('Anggota') }}</span>
        </div>
    </div>
</div>

<!-- Navigation Tabs -->
<nav class="tab-nav">
    <button class="tab-btn active" onclick="switchTab(event, 'tab-info')">{{ __('Beranda') }}</button>
    <button class="tab-btn" onclick="switchTab(event, 'tab-tasks')">{{ __('Tugas & Kuis') }}</button>
    <button class="tab-btn" onclick="switchTab(event, 'tab-schedule')">{{ __('Jadwal') }}</button>
    <button class="tab-btn" onclick="switchTab(event, 'tab-discussions')">{{ __('Diskusi Forum') }}</button>
    <button class="tab-btn" onclick="switchTab(event, 'tab-members')">{{ __('Anggota') }}</button>
</nav>

<!-- Tab Content Panes -->
<div class="tab-content" style="--class-color: {{ $class->color ?: '#10B981' }}">
    
    <!-- Tab 1: Beranda / Informasi -->
    <div class="tab-pane active" id="tab-info">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <!-- Main Content Left -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <div class="card">
                    <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem;">{{ __('Deskripsi Kelas') }}</h3>
                    <p style="color: var(--text-main); font-size: 0.95rem; line-height: 1.6;">
                        {{ $class->deskripsi ?: __('Tidak ada deskripsi tertulis untuk kelas ini.') }}
                    </p>
                </div>
                
                <!-- Announcement Card -->
                <div class="card">
                    <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem;">{{ __('Pengumuman Kelas') }}</h3>
                    <div style="border-left: 3px solid var(--class-color); padding-left: 1rem; margin-top: 1rem;">
                        <p style="font-size: 0.9rem; color: var(--text-muted); font-style: italic;">
                            {{ __('Belum ada pengumuman khusus dari dosen pengajar.') }}
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar Right -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <!-- Dosen Info Card -->
                <div class="card">
                    <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1rem;">Pengampu Utama</h3>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <img src="{{ $class->admin->avatar_url }}" style="width: 50px; height: 50px; border-radius: 50%; background-color: var(--primary-soft);" alt="Avatar">
                        <div>
                            <h4 style="font-weight: 700; font-size: 0.95rem;">{{ $class->admin->name }}</h4>
                            <p style="font-size: 0.75rem; color: var(--text-muted);">{{ $class->admin->email }}</p>
                            @if($class->admin->nim_nip)
                                <p style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; margin-top: 0.15rem;">NIP: {{ $class->admin->nim_nip }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Academic Info -->
                <div class="card" style="font-size: 0.85rem;">
                    <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1rem;">{{ __('Rincian Akademik') }}</h3>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                            <span style="color: var(--text-muted); font-weight: 500;">{{ __('Status Kelas') }}</span>
                            <span style="color: #10b981; font-weight: 700;">{{ __('Aktif') }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                            <span style="color: var(--text-muted); font-weight: 500;">{{ __('Tahun Ajaran') }}</span>
                            <span style="font-weight: 600;">{{ $class->tahun_ajaran ?: '-' }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                            <span style="color: var(--text-muted); font-weight: 500;">{{ __('Semester') }}</span>
                            <span style="font-weight: 600;">{{ $class->semester ?: '-' }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted); font-weight: 500;">{{ __('Ruang Kuliah') }}</span>
                            <span style="font-weight: 600;">{{ $class->ruangan ?: '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tab 2: Tugas & Kuis -->
    <div class="tab-pane" id="tab-tasks">
        <div class="card">
            <div class="card-title">
                <span>{{ __('Daftar Tugas & Kuis') }}</span>
                @if($user->isClassAdmin($class))
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <a href="{{ route('reports.excel', $class->id) }}" class="btn" style="width: auto; padding: 0.5rem 1rem; font-size: 0.85rem; background-color: #10B981; color: white; text-decoration: none;">
                             <i data-lucide="file-spreadsheet" style="width: 16px; height: 16px;"></i>
                            <span>Excel</span>
                        </a>
                        <a href="{{ route('reports.pdf', $class->id) }}" target="_blank" class="btn" style="width: auto; padding: 0.5rem 1rem; font-size: 0.85rem; background-color: #ef4444; color: white; text-decoration: none;">
                            <i data-lucide="file-text" style="width: 16px; height: 16px;"></i>
                            <span>PDF</span>
                        </a>
                        <button class="btn btn-primary" style="width: auto; padding: 0.5rem 1rem; font-size: 0.85rem; background-color: var(--class-color);" onclick="openModal('createTaskModal')">
                            <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                            <span>{{ __('Tambah Tugas') }}</span>
                        </button>
                    </div>
                @endif
            </div>
            
            @if($class->tasks->isEmpty())
                <div style="text-align: center; padding: 4rem 1rem;">
                    <i data-lucide="clipboard-list" style="width: 48px; height: 48px; color: var(--text-muted); margin-bottom: 1rem; opacity: 0.6;"></i>
                    <h4 style="font-weight: 700; margin-bottom: 0.25rem;">{{ __('Tidak ada tugas saat ini') }}</h4>
                    <p style="color: var(--text-muted); font-size: 0.85rem; max-width: 320px; margin: 0 auto;">
                        @if($user->isClassAdmin($class))
                            {{ __('Anda belum menerbitkan tugas apa pun untuk kelas ini. Klik tombol di atas untuk membuat tugas baru.') }}
                        @else
                            {{ __('Bagus sekali! Semua tugas kelas telah Anda kerjakan atau belum ada tugas baru dari dosen.') }}
                        @endif
                    </p>
                </div>
            @else
                <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem;">
                    @foreach($class->tasks as $task)
                        <div style="display: flex; justify-content: space-between; align-items: center; border: 1px solid var(--border-color); padding: 1.25rem; border-radius: var(--border-radius-md); background-color: var(--bg-app);">
                            <div>
                                <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.25rem;">
                                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; background-color: var(--border-color); padding: 0.2rem 0.5rem; border-radius: var(--border-radius-sm);">
                                        {{ $task->tipe }}
                                    </span>
                                    <span style="font-size: 0.8rem; color: #ef4444; font-weight: 600;">
                                        Deadline: {{ app()->getLocale() == 'en' ? $task->deadline->format('d M Y, h:i A') : $task->deadline->format('d M Y, H.i') . ' WIB' }}
                                    </span>
                                </div>
                                <h4 style="font-weight: 700; font-size: 1.05rem;">
                                    <a href="#" style="color: inherit; text-decoration: none;">{{ $task->judul }}</a>
                                </h4>
                            </div>
                            
                            <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-primary" style="width: auto; padding: 0.5rem 1.25rem; font-size: 0.85rem; background-color: var(--class-color);">
                                <span>{{ __('Detail') }}</span>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    
    <!-- Tab 3: Jadwal -->
    <div class="tab-pane" id="tab-schedule">
        <div class="card">
            <div class="card-title">
                <span>{{ __('Jadwal Perkuliahan Kelas') }}</span>
                @if($user->isClassAdmin($class))
                    <button class="btn btn-primary" style="width: auto; padding: 0.5rem 1rem; font-size: 0.85rem; background-color: var(--class-color);" onclick="openModal('createScheduleModal')">
                        <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                        <span>{{ __('Tambah Jadwal') }}</span>
                    </button>
                @endif
            </div>

            @if($class->schedules->isEmpty())
                <div style="text-align: center; padding: 4rem 1rem;">
                    <i data-lucide="calendar" style="width: 48px; height: 48px; color: var(--text-muted); margin-bottom: 1rem; opacity: 0.6;"></i>
                    <h4 style="font-weight: 700; margin-bottom: 0.25rem;">{{ __('Belum ada jadwal terdaftar') }}</h4>
                    <p style="color: var(--text-muted); font-size: 0.85rem; max-width: 320px; margin: 0 auto;">
                        {{ __('Silakan tambahkan jam perkuliahan mingguan untuk membantu mahasiswa memantau perkuliahan.') }}
                    </p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="schedule-grid-table">
                        <thead>
                            <tr>
                                <th>{{ __('Mata Kuliah / Topik') }}</th>
                                <th>{{ __('Hari') }}</th>
                                <th>{{ __('Waktu') }}</th>
                                <th>{{ __('Ruangan') }}</th>
                                <th>{{ __('Dosen Pengampu') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($class->schedules as $sched)
                                <tr>
                                    <td style="font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                                        <span style="width: 12px; height: 12px; border-radius: 50%; background-color: {{ $sched->color ?: $class->color }}"></span>
                                        <span>{{ $sched->mata_kuliah }}</span>
                                    </td>
                                    <td>{{ $sched->hari }}</td>
                                    <td>{{ $sched->formatted_start_time }} - {{ $sched->formatted_end_time }}</td>
                                    <td>{{ $sched->ruangan ?: '-' }}</td>
                                    <td>{{ $sched->dosen ?: $class->admin->name }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Tab 4: Diskusi Forum -->
    <div class="tab-pane" id="tab-discussions">
        <div class="card">
            <div class="card-title">
                <span>{{ __('Diskusi Kelas') }}</span>
                <button class="btn btn-primary" style="width: auto; padding: 0.5rem 1rem; font-size: 0.85rem; background-color: var(--class-color);" onclick="openModal('createDiscussionModal')">
                    <i data-lucide="message-square" style="width: 16px; height: 16px;"></i>
                    <span>{{ __('Tanya Dosen/Teman') }}</span>
                </button>
            </div>
            
            @if($class->discussions->isEmpty())
                <div style="text-align: center; padding: 4rem 1rem;">
                    <i data-lucide="messages-square" style="width: 48px; height: 48px; color: var(--text-muted); margin-bottom: 1rem; opacity: 0.6;"></i>
                    <h4 style="font-weight: 700; margin-bottom: 0.25rem;">{{ __('Forum Diskusi Masih Sepi') }}</h4>
                    <p style="color: var(--text-muted); font-size: 0.85rem; max-width: 320px; margin: 0 auto;">
                        {{ __('Gunakan forum ini untuk berdiskusi seputar materi perkuliahan, tugas, atau sekadar tanya jawab.') }}
                    </p>
                </div>
            @else
                <!-- Forum Thread lists -->
                <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem;">
                    @foreach($class->discussions as $disc)
                        <div style="border: 1px solid var(--border-color); padding: 1.25rem; border-radius: var(--border-radius-md); background-color: var(--bg-app); display: flex; flex-direction: column; gap: 0.5rem;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <img src="{{ $disc->user->avatar_url }}" style="width: 24px; height: 24px; border-radius: 50%; background-color: var(--primary-soft);" alt="User">
                                    <span style="font-size: 0.8rem; font-weight: 700;">{{ $disc->user->name }}</span>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $disc->created_at->diffForHumans() }}</span>
                                </div>
                                @if($disc->is_pinned)
                                    <span style="background-color: rgba(245, 158, 11, 0.15); color: #d97706; padding: 0.1rem 0.4rem; border-radius: var(--border-radius-sm); font-size: 0.7rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.2rem;">
                                        <i data-lucide="pin" style="width: 10px; height: 10px;"></i> Pinned
                                    </span>
                                @endif
                            </div>
                            <h4 style="font-weight: 700; font-size: 1.05rem; margin-top: 0.25rem;">
                                <a href="{{ route('discussions.show', $disc->id) }}" style="text-decoration: none; color: inherit; transition: color var(--transition-fast);" onmouseover="this.style.color='var(--class-color)'" onmouseout="this.style.color='inherit'">
                                    {{ $disc->judul }}
                                </a>
                            </h4>
                            <p style="font-size: 0.85rem; color: var(--text-muted); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.5;">
                                {{ $disc->konten }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    
    <!-- Tab 5: Anggota -->
    <div class="tab-pane" id="tab-members">
        <div style="display: grid; grid-template-columns: 2fr 1.2fr; gap: 2rem;">
            <!-- Left: List Members -->
            <div class="card">
                <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                    {{ __('Anggota Kelas') }}
                </h3>
                
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <!-- Admin / Lecturer -->
                    <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-color);">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <img src="{{ $class->admin->avatar_url }}" style="width: 36px; height: 36px; border-radius: 50%; background-color: var(--primary-soft);" alt="Avatar">
                            <div>
                                <h4 style="font-weight: 700; font-size: 0.95rem;">{{ $class->admin->name }}</h4>
                                <p style="font-size: 0.75rem; color: var(--text-muted);">{{ $class->admin->email }}</p>
                            </div>
                        </div>
                        <span style="background-color: var(--class-color); color: var(--text-white); font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: var(--border-radius-sm);">
                            {{ __('Pengajar (Owner)') }}
                        </span>
                    </div>

                    <!-- Other Admins / Class Members -->
                    @foreach($class->members as $member)
                        @if($member->id !== $class->admin_id)
                            <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-color);">
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <img src="{{ $member->avatar_url }}" style="width: 36px; height: 36px; border-radius: 50%; background-color: var(--primary-soft);" alt="Avatar">
                                    <div>
                                        <h4 style="font-weight: 700; font-size: 0.95rem;">{{ $member->name }}</h4>
                                        <p style="font-size: 0.75rem; color: var(--text-muted);">
                                            {{ $member->email }}
                                            @if($member->nim_nip)
                                                &bull; {{ $member->nim_nip }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <span style="background-color: {{ $member->pivot->role === 'admin' ? 'var(--class-color)' : 'var(--bg-app)' }}; color: {{ $member->pivot->role === 'admin' ? 'var(--text-white)' : 'var(--text-muted)' }}; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: var(--border-radius-sm); border: 1px solid var(--border-color);">
                                    {{ $member->pivot->role === 'admin' ? __('Pengajar') : __('Mahasiswa') }}
                                </span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Right: Invite Dosen / Admin (Multi-admin delegate) -->
            @if($user->isClassAdmin($class))
                <div class="card" style="height: fit-content;">
                    <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1rem;">{{ __('Delegasi Pengajar') }}</h3>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.25rem;">
                        {{ __('Tambahkan dosen/pengajar lain ke kelas ini sebagai co-admin untuk mengelola tugas dan melihat nilai mahasiswa.') }}
                    </p>
                    
                    <form action="{{ route('classes.add-admin', $class->id) }}" method="POST">
                        @csrf
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label for="admin_email" class="form-label">{{ __('Email Calon Pengajar') }}</label>
                            <input type="email" name="email" id="admin_email" class="form-control form-control-noicon" style="font-size: 0.9rem;" placeholder="dosen2@kampus.ac.id" required>
                        </div>
                        <button type="submit" class="btn btn-primary" style="background-color: var(--class-color);">
                            <i data-lucide="user-plus" style="width: 18px; height: 18px;"></i>
                            <span>{{ __('Jadikan Pengajar') }}</span>
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

</div>

<!-- Modals (Dosen Only) -->
@if($user->isClassAdmin($class))
    <!-- Create Task Modal -->
    <div class="modal-overlay" id="createTaskModal">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">{{ __('Tambah Tugas Baru') }}</h3>
                <button class="modal-close" onclick="closeModal('createTaskModal')">
                    <i data-lucide="x" style="width: 20px; height: 20px;"></i>
                </button>
            </div>
            
            <form action="{{ route('tasks.store', $class->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <!-- Judul Tugas -->
                    <div class="form-group">
                        <label for="judul_tugas" class="form-label">{{ __('Judul Tugas') }} *</label>
                        <input type="text" name="judul" id="judul_tugas" class="form-control form-control-noicon" placeholder="{{ __('Contoh: Tugas Individu 1 - OOP') }}" required>
                    </div>

                    <!-- Tipe & Deadline -->
                    <div style="display: grid; grid-template-columns: {{ $class->is_private ? '1fr' : '1fr 1fr' }}; gap: 1rem;">
                        <div class="form-group">
                            <label for="tipe_tugas" class="form-label">{{ __('Tipe') }}</label>
                            <select name="tipe" id="tipe_tugas" class="form-control form-control-noicon" required>
                                <option value="tugas">{{ __('Tugas') }}</option>
                                <option value="kuis">{{ __('Kuis') }}</option>
                                <option value="ujian">{{ __('Ujian') }}</option>
                            </select>
                        </div>
                        @if(!$class->is_private)
                            <div class="form-group">
                                <label for="nilai_max" class="form-label">{{ __('Nilai Maksimal') }}</label>
                                <input type="number" name="nilai_max" id="nilai_max" value="100" class="form-control form-control-noicon" min="0" max="1000" required>
                            </div>
                        @else
                            <input type="hidden" name="nilai_max" value="0">
                        @endif
                    </div>

                    <!-- Deadline -->
                    <div class="form-group">
                        <label for="deadline" class="form-label">{{ __('Tenggat Waktu (Deadline)') }} *</label>
                        <input type="datetime-local" name="deadline" id="deadline" class="form-control form-control-noicon" required>
                    </div>

                    <!-- Deskripsi -->
                    <div class="form-group">
                        <label for="deskripsi_tugas" class="form-label">{{ __('Instruksi & Deskripsi') }}</label>
                        <textarea name="deskripsi" id="deskripsi_tugas" class="form-control form-control-noicon" style="height: 100px; resize: none;" placeholder="{{ __('Tulis instruksi pengerjaan tugas di sini...') }}"></textarea>
                    </div>

                    <!-- File Soal (Attachment) -->
                    <div class="form-group" style="margin-bottom: 0.5rem;">
                        <label for="file_soal" class="form-label">{{ __('Lampiran Soal (Optional)') }}</label>
                        <input type="file" name="file_soal" id="file_soal" class="form-control form-control-noicon" style="padding: 0.5rem;">
                        <span style="font-size: 0.75rem; color: var(--text-muted);">{{ __('Format: PDF, Doc, Zip, Rar, Gambar (Max 10MB)') }}</span>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" style="width: auto;" onclick="closeModal('createTaskModal')">{{ __('Batal') }}</button>
                    <button type="submit" class="btn btn-primary" style="width: auto; padding-left: 2rem; padding-right: 2rem; background-color: var(--class-color);">{{ __('Terbitkan Tugas') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Schedule Modal -->
    <div class="modal-overlay" id="createScheduleModal">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">{{ __('Tambah Jadwal Perkuliahan') }}</h3>
                <button class="modal-close" onclick="closeModal('createScheduleModal')">
                    <i data-lucide="x" style="width: 20px; height: 20px;"></i>
                </button>
            </div>
            
            <form action="{{ route('schedules.store', $class->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <!-- Mata Kuliah -->
                    <div class="form-group">
                        <label for="sched_matkul" class="form-label">{{ __('Mata Kuliah / Topik') }} *</label>
                        <input type="text" name="mata_kuliah" id="sched_matkul" class="form-control form-control-noicon" value="{{ $class->nama_kelas }}" required>
                    </div>

                    <!-- Dosen Pengampu -->
                    <div class="form-group">
                        <label for="sched_dosen" class="form-label">{{ __('Dosen Pengampu') }}</label>
                        <input type="text" name="dosen" id="sched_dosen" class="form-control form-control-noicon" value="{{ $class->admin->name }}" placeholder="{{ __('Nama Dosen') }}">
                    </div>

                    <!-- Hari & Ruangan -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="sched_hari" class="form-label">{{ __('Hari') }} *</label>
                            <select name="hari" id="sched_hari" class="form-control form-control-noicon" required>
                                <option value="Senin">{{ __('Senin') }}</option>
                                <option value="Selasa">{{ __('Selasa') }}</option>
                                <option value="Rabu">{{ __('Rabu') }}</option>
                                <option value="Kamis">{{ __('Kamis') }}</option>
                                <option value="Jumat">{{ __('Jumat') }}</option>
                                <option value="Sabtu">{{ __('Sabtu') }}</option>
                                <option value="Minggu">{{ __('Minggu') }}</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="sched_ruangan" class="form-label">{{ __('Ruangan') }}</label>
                            <input type="text" name="ruangan" id="sched_ruangan" class="form-control form-control-noicon" value="{{ $class->ruangan }}" placeholder="{{ __('Contoh: H.4.5') }}">
                        </div>
                    </div>

                    <!-- Waktu Mulai & Waktu Selesai -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="waktu_mulai" class="form-label">{{ __('Waktu Mulai') }} *</label>
                            <input type="time" name="waktu_mulai" id="waktu_mulai" class="form-control form-control-noicon" required>
                        </div>
                        <div class="form-group">
                            <label for="waktu_selesai" class="form-label">{{ __('Waktu Selesai') }} *</label>
                            <input type="time" name="waktu_selesai" id="waktu_selesai" class="form-control form-control-noicon" required>
                        </div>
                    </div>

                    <!-- Catatan & Color -->
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="sched_catatan" class="form-label">{{ __('Catatan Tambahan') }}</label>
                            <input type="text" name="catatan" id="sched_catatan" class="form-control form-control-noicon" placeholder="{{ __('Contoh: Kuliah Pengganti / Ujian Tengah Semester') }}">
                        </div>
                        <div class="form-group">
                            <label for="sched_color" class="form-label">{{ __('Warna Aksen') }}</label>
                            <input type="color" name="color" id="sched_color" value="{{ $class->color ?: '#3B82F6' }}" class="form-control form-control-noicon" style="padding: 0.2rem; height: 42px; width: 100%; cursor: pointer;">
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" style="width: auto;" onclick="closeModal('createScheduleModal')">{{ __('Batal') }}</button>
                    <button type="submit" class="btn btn-primary" style="width: auto; padding-left: 2rem; padding-right: 2rem; background-color: var(--class-color);">{{ __('Simpan Jadwal') }}</button>
                </div>
            </form>
        </div>
    </div>
@endif

<!-- Create Discussion Modal (Shared by Dosen & Mahasiswa) -->
<div class="modal-overlay" id="createDiscussionModal">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">{{ __('Mulai Diskusi Baru') }}</h3>
            <button class="modal-close" onclick="closeModal('createDiscussionModal')">
                <i data-lucide="x" style="width: 20px; height: 20px;"></i>
            </button>
        </div>
        
        <form action="{{ route('discussions.store', $class->id) }}" method="POST">
            @csrf
            <div class="modal-body">
                <!-- Judul Diskusi -->
                <div class="form-group">
                    <label for="judul_diskusi" class="form-label">{{ __('Topik / Pertanyaan') }} *</label>
                    <input type="text" name="judul" id="judul_diskusi" class="form-control form-control-noicon" placeholder="{{ __('Contoh: Tanya rumus nomor 3 di halaman 15') }}" required>
                </div>

                <!-- Konten -->
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="konten_diskusi" class="form-label">{{ __('Deskripsi Lengkap') }} *</label>
                    <textarea name="konten" id="konten_diskusi" class="form-control form-control-noicon" style="height: 120px; resize: none;" placeholder="{{ __('Tulis rincian pertanyaan atau topik diskusi yang ingin ditanyakan...') }}" required></textarea>
                </div>

                <!-- Pin checkbox (Only Dosen/Admin) -->
                @if($user->isClassAdmin($class))
                    <div class="form-helper" style="margin-bottom: 0.5rem; justify-content: flex-start;">
                        <label class="checkbox-container">
                            <input type="checkbox" name="is_pinned" id="is_pinned" value="1">
                            <span>{{ __('Sematkan Diskusi (Pin thread di paling atas)') }}</span>
                        </label>
                    </div>
                @endif
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" style="width: auto;" onclick="closeModal('createDiscussionModal')">{{ __('Batal') }}</button>
                <button type="submit" class="btn btn-primary" style="width: auto; padding-left: 2rem; padding-right: 2rem; background-color: var(--class-color);">{{ __('Mulai Diskusi') }}</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<!-- Edit Class Modal -->
<div class="modal-overlay" id="editClassModal">
    <div class="modal-container" style="max-width: 600px;">
        <div class="modal-header">
            <h3 style="font-size: 1.25rem; font-weight: 700;">{{ __('Edit Informasi Kelas') }}</h3>
            <button class="modal-close" onclick="closeModal('editClassModal')">
                <i data-lucide="x" style="width: 20px; height: 20px;"></i>
            </button>
        </div>
        <div style="padding: 1.5rem;">
            <form action="{{ route('classes.update', $class->id) }}" method="POST" style="display: flex; flex-direction: column; gap: 1.25rem;">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">{{ __('Nama Kelas') }}</label>
                    <input type="text" name="nama_kelas" class="form-control" value="{{ old('nama_kelas', $class->nama_kelas) }}" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <div class="form-group">
                        <label class="form-label">{{ __('Mata Kuliah') }}</label>
                        <input type="text" name="mata_kuliah" class="form-control" value="{{ old('mata_kuliah', $class->mata_kuliah) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('Ruangan') }}</label>
                        <input type="text" name="ruangan" class="form-control" value="{{ old('ruangan', $class->ruangan) }}">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <div class="form-group">
                        <label class="form-label">{{ __('Semester') }}</label>
                        <input type="text" name="semester" class="form-control" value="{{ old('semester', $class->semester) }}" placeholder="e.g. Ganjil 2026">
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('Tahun Ajaran') }}</label>
                        <input type="text" name="tahun_ajaran" class="form-control" value="{{ old('tahun_ajaran', $class->tahun_ajaran) }}" placeholder="e.g. 2026/2027">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Warna Tema Kelas') }}</label>
                    <div style="display: flex; align-items: center; gap: 1rem; margin-top: 0.25rem;">
                        <input type="color" name="color" value="{{ old('color', $class->color ?: '#10B981') }}" style="width: 50px; height: 50px; padding: 0; border: none; border-radius: var(--border-radius-sm); cursor: pointer;">
                        <span style="font-size: 0.85rem; color: var(--text-muted);">Pilih warna identitas untuk kelas ini.</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Deskripsi Singkat') }}</label>
                    <textarea name="deskripsi" class="form-control" rows="3">{{ old('deskripsi', $class->deskripsi) }}</textarea>
                </div>
                <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="is_private" id="is_private_edit" value="1" {{ $class->is_private ? 'checked' : '' }}>
                    <label for="is_private_edit" style="font-weight: 600; cursor: pointer; color: var(--text-main);">{{ __('Jadikan Kelas Privat') }}</label>
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 0.5rem;">
                    <button type="button" class="btn" onclick="closeModal('editClassModal')" style="background-color: var(--bg-app); color: var(--text-main); border: 1px solid var(--border-color); width: auto;">{{ __('Batal') }}</button>
                    <button type="submit" class="btn" style="background-color: var(--primary); color: white; width: auto;">
                        <i data-lucide="save" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i>
                        {{ __('Simpan Perubahan') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function switchTab(e, tabId) {
        // Deactivate all tab buttons and panes
        const buttons = document.querySelectorAll('.tab-btn');
        const panes = document.querySelectorAll('.tab-pane');
        
        buttons.forEach(btn => btn.classList.remove('active'));
        panes.forEach(pane => pane.classList.remove('active'));
        
        // Activate current tab button and target pane
        e.currentTarget.classList.add('active');
        document.getElementById(tabId).classList.add('active');
    }

    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('active');
            setTimeout(() => {
                const input = modal.querySelector('input[type="text"]');
                if (input) input.focus();
            }, 100);
        }
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('active');
        }
    }

    // Close modals on click outside container
    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal-overlay')) {
            e.target.classList.remove('active');
        }
    });

    function copyJoinLink(url) {
        navigator.clipboard.writeText(url).then(() => {
            alert('Link undangan berhasil disalin!\n' + url);
        }).catch(err => {
            console.error('Gagal menyalin text: ', err);
            prompt("Gagal menyalin otomatis. Silakan copy link berikut secara manual:", url);
        });
    }
</script>
@endsection
