@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div style="margin-bottom: 2rem;">
    <h1 style="font-weight: 800; font-size: 2.2rem; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
        {{ __('Halo') }}, {{ $user->name }}! 👋
    </h1>
    <p style="color: var(--text-muted);">
        {{ __('Berikut adalah ringkasan kelas digital Anda untuk hari ini.') }}
    </p>
</div>

<!-- Welcome Panel Banner -->
<div class="card" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(79, 70, 229, 0.1) 100%); border-color: rgba(16, 185, 129, 0.2); margin-bottom: 2rem;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.5rem;">
        <div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--primary); margin-bottom: 0.5rem;">
                {{ __('Selamat datang kembali di OurClass!') }}
            </h3>
            <p style="max-width: 600px; font-size: 0.95rem; color: var(--text-main);">
                {{ __('Aplikasi ini dirancang untuk memudahkan manajemen kelas digital, pengumpulan tugas, pemantauan jadwal kuliah, dan visualisasi grafik beban belajar Anda.') }}
            </p>
        </div>
        <div style="background: var(--bg-card); padding: 0.75rem 1.5rem; border-radius: var(--border-radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">{{ __('Peran Anda') }}</div>
            <div style="font-size: 1.15rem; font-weight: 800; color: var(--secondary); text-transform: capitalize;">
                {{ $user->role }}
            </div>
        </div>
    </div>
</div>

<!-- Dashboard Grid Stats -->
<div class="dashboard-grid">
    <!-- Left Column: Classes, Tasks, Schedules -->
    <div style="display: flex; flex-direction: column; gap: 2rem;">
        
        <!-- Stats Summary Badges -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
            
            <a href="{{ route('classes.index') }}" style="text-decoration: none; color: inherit;">
                <div class="card card-hover" style="display: flex; align-items: center; gap: 1rem;">
                    <div style="background-color: var(--primary-soft); color: var(--primary); width: 48px; height: 48px; border-radius: var(--border-radius-md); display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="book-open" style="width: 24px; height: 24px;"></i>
                    </div>
                    <div>
                        <div style="font-size: 1.5rem; font-weight: 800; line-height: 1.2;">{{ $classesCount }}</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 500;">{{ __('Kelas Terdaftar') }}</div>
                    </div>
                </div>
            </a>

            <div class="card card-hover" style="display: flex; align-items: center; gap: 1rem;">
                <div style="background: rgba(79, 70, 229, 0.1); color: var(--secondary); width: 48px; height: 48px; border-radius: var(--border-radius-md); display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="clipboard-list" style="width: 24px; height: 24px;"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 800; line-height: 1.2;">{{ $pendingTasksCount }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 500;">
                        {{ $user->role === 'mahasiswa' ? __('Tugas Pending') : __('Tugas Perlu Dinilai') }}
                    </div>
                </div>
            </div>

            <div class="card card-hover" style="display: flex; align-items: center; gap: 1rem;">
                <div style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; width: 48px; height: 48px; border-radius: var(--border-radius-md); display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="calendar" style="width: 24px; height: 24px;"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 800; line-height: 1.2;">{{ $todaySchedulesCount }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 500;">{{ __('Jadwal Hari Ini') }}</div>
                </div>
            </div>
            
        </div>

        <!-- Class List Container -->
        <div class="card">
            <div class="card-title">
                <span>{{ __('Kelas Saya') }}</span>
                <a href="{{ route('classes.index') }}" class="btn btn-primary" style="width: auto; padding: 0.5rem 1rem; font-size: 0.85rem; text-decoration: none;">
                    {{ __('Kelola Kelas') }}
                </a>
            </div>
            
            @if($classes->isEmpty())
                <!-- Empty State -->
                <div style="text-align: center; padding: 3rem 1.5rem; border: 2px dashed var(--border-color); border-radius: var(--border-radius-md); background: var(--bg-app); margin-top: 1rem;">
                    <i data-lucide="book-copy" style="width: 48px; height: 48px; color: var(--text-muted); margin-bottom: 1rem;"></i>
                    <h4 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.25rem;">{{ __('Belum Ada Kelas') }}</h4>
                    <p style="color: var(--text-muted); font-size: 0.9rem; max-width: 320px; margin: 0 auto;">
                        @if($user->role === 'dosen')
                            {{ __('Anda belum membuat kelas apapun. Klik Kelola Kelas untuk memulai.') }}
                        @else
                            {{ __('Anda belum bergabung dalam kelas apapun. Masukkan kode kelas dari dosen Anda.') }}
                        @endif
                    </p>
                </div>
            @else
                <!-- Class Grid Item List -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-top: 1rem;">
                    @foreach($classes->take(3) as $class)
                        <div class="card card-hover" style="border-top: 4px solid {{ $class->color ?: '#10B981' }}; padding: 1.25rem; min-height: 140px; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <h4 style="font-weight: 800; font-size: 1.05rem; line-height: 1.3;">
                                    <a href="{{ route('classes.show', $class->id) }}" style="text-decoration: none; color: inherit;">
                                        {{ $class->nama_kelas }}
                                    </a>
                                </h4>
                                @if($class->mata_kuliah)
                                    <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; margin-top: 0.25rem;">
                                        {{ $class->mata_kuliah }}
                                    </div>
                                @endif
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.25rem; font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">
                                <span>Code: {{ $class->kode_unik }}</span>
                                <span>{{ $class->members_count }} {{ __('Anggota') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Today's Schedule Card -->
        <div class="card">
            <h3 class="card-title">
                <span>{{ __('Jadwal Kuliah Hari Ini') }}</span>
                <i data-lucide="calendar-check" style="width: 18px; height: 18px; color: var(--text-muted);"></i>
            </h3>

            @if($todaySchedules->isEmpty())
                <div style="text-align: center; padding: 2.5rem 1rem; border: 1px dashed var(--border-color); border-radius: var(--border-radius-md); background: var(--bg-app);">
                    <i data-lucide="calendar-heart" style="width: 32px; height: 32px; color: var(--text-muted); margin-bottom: 0.5rem; opacity: 0.6;"></i>
                    <h5 style="font-weight: 700; margin-bottom: 0.15rem;">{{ __('Santai Dulu!') }}</h5>
                    <p style="color: var(--text-muted); font-size: 0.8rem;">{{ __('Tidak ada jadwal hari ini.') }}</p>
                </div>
            @else
                <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-top: 1rem;">
                    @foreach($todaySchedules as $sched)
                        <div style="display: flex; justify-content: space-between; align-items: center; border: 1px solid var(--border-color); padding: 1rem; border-radius: var(--border-radius-md); background-color: var(--bg-app); border-left: 4px solid {{ $sched->color ?: '#3B82F6' }}">
                            <div>
                                <h4 style="font-weight: 700; font-size: 0.95rem;">{{ $sched->mata_kuliah }}</h4>
                                <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">
                                    {{ __('Dosen') }}: {{ $sched->dosen }} &bull; {{ __('Ruangan') }}: {{ $sched->ruangan ?: '-' }}
                                </span>
                            </div>
                            <div style="font-weight: 700; font-size: 0.85rem; color: var(--primary); background: var(--primary-soft); padding: 0.3rem 0.6rem; border-radius: var(--border-radius-sm);">
                                {{ $sched->formatted_start_time }} - {{ $sched->formatted_end_time }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    <!-- Right Column: Notifications & Productivity -->
    <div style="display: flex; flex-direction: column; gap: 2rem;">
        
        <!-- Notifications Card -->
        <div class="card">
            <h3 class="card-title">
                <span>{{ __('Notifikasi Terbaru') }}</span>
                <a href="{{ route('notifications.index') }}" style="color: var(--primary); text-decoration: none; font-size: 0.8rem; font-weight: 700;">{{ __('Lihat Semua') }}</a>
            </h3>
            
            <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem;">
                @if($recentNotifications->isEmpty())
                    <div style="text-align: center; padding: 2rem 1rem;">
                        <p style="color: var(--text-muted); font-size: 0.85rem;">{{ __('Tidak ada notifikasi baru.') }}</p>
                    </div>
                @else
                    @foreach($recentNotifications as $notif)
                        <div style="display: flex; gap: 0.75rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-color); opacity: {{ $notif->is_read ? '0.7' : '1' }}">
                            @php
                                $icon = 'info';
                                $iconBg = 'var(--primary-soft)';
                                $iconColor = 'var(--primary)';
                                if($notif->tipe === 'tugas' || $notif->tipe === 'deadline') {
                                    $icon = 'clipboard-list';
                                    $iconBg = 'rgba(79, 70, 229, 0.1)';
                                    $iconColor = 'var(--secondary)';
                                } elseif($notif->tipe === 'jadwal') {
                                    $icon = 'calendar';
                                    $iconBg = 'rgba(245, 158, 11, 0.1)';
                                    $iconColor = '#f59e0b';
                                }
                            @endphp
                            <div style="background-color: {{ $iconBg }}; color: {{ $iconColor }}; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 0.15rem;">
                                <i data-lucide="{{ $icon }}" style="width: 16px; height: 16px;"></i>
                            </div>
                            <div style="overflow: hidden;">
                                <h5 style="font-size: 0.85rem; font-weight: 700; margin-bottom: 0.1rem; display: flex; align-items: center; gap: 0.25rem;">
                                    <a href="{{ route('notifications.read', $notif->id) }}" style="text-decoration: none; color: inherit;">
                                        {{ $notif->judul }}
                                    </a>
                                    @if(!$notif->is_read)
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #ef4444; display: inline-block;"></span>
                                    @endif
                                </h5>
                                <p style="font-size: 0.75rem; color: var(--text-muted); line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    {{ $notif->pesan }}
                                </p>
                                <span style="font-size: 0.65rem; color: var(--text-muted); font-weight: 500; display: block; margin-top: 0.25rem;">
                                    {{ $notif->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Weekly Productivity Card -->
        <div class="card" style="text-align: center;">
            <h3 class="card-title" style="text-align: left;">
                <span>{{ __('Menu Pintasan') }}</span>
                <i data-lucide="compass" style="width: 18px; height: 18px; color: var(--text-muted);"></i>
            </h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 1rem;">
                <a href="{{ route('classes.index') }}" class="btn btn-primary" style="background-color: var(--primary-soft); color: var(--primary); padding: 0.75rem 0.5rem; flex-direction: column; font-size: 0.85rem; border: 1px solid rgba(16,185,129,0.1); border-radius: var(--border-radius-md); box-shadow: none;">
                    <i data-lucide="book-open" style="width: 20px; height: 20px;"></i>
                    <span style="margin-top: 0.25rem;">{{ __('Kelas Saya') }}</span>
                </a>
                <a href="{{ route('analytics.index') }}" class="btn btn-primary" style="background-color: rgba(79, 70, 229, 0.08); color: var(--secondary); padding: 0.75rem 0.5rem; flex-direction: column; font-size: 0.85rem; border: 1px solid rgba(79,70,229,0.1); border-radius: var(--border-radius-md); box-shadow: none;">
                    <i data-lucide="bar-chart-3" style="width: 20px; height: 20px;"></i>
                    <span style="margin-top: 0.25rem;">{{ __('Analisis Beban') }}</span>
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
