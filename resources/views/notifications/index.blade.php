@extends('layouts.app')

@section('title', __('Notifikasi'))

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
    <div>
        <h1 style="font-weight: 800; font-size: 2.2rem; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
            {{ __('Notifikasi Saya') }} 🔔
        </h1>
        <p style="color: var(--text-muted);">
            {{ __('Daftar update terbaru tentang tugas, jadwal, dan kelas Anda.') }}
        </p>
    </div>
    
    @if(Auth::user()->unreadNotifications()->exists())
        <form action="{{ route('notifications.mark-all-read') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary" style="width: auto; padding: 0.5rem 1rem; font-size: 0.85rem; background-color: var(--secondary);">
                <i data-lucide="check-check" style="width: 16px; height: 16px;"></i>
                <span>{{ __('Tandai Semua Dibaca') }}</span>
            </button>
        </form>
    @endif
</div>

<div class="card">
    @if($notifications->isEmpty())
        <div style="text-align: center; padding: 5rem 1rem;">
            <i data-lucide="bell-off" style="width: 64px; height: 64px; color: var(--text-muted); margin-bottom: 1.5rem; opacity: 0.5;"></i>
            <h3 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 0.5rem;">{{ __('Kotak Masuk Bersih') }}</h3>
            <p style="color: var(--text-muted); font-size: 0.95rem;">{{ __('Tidak ada notifikasi baru untuk Anda saat ini.') }}</p>
        </div>
    @else
        <div style="display: flex; flex-direction: column;">
            @foreach($notifications as $notif)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 0; border-bottom: 1px solid var(--border-color); opacity: {{ $notif->is_read ? '0.7' : '1' }}">
                    <div style="display: flex; gap: 1rem; align-items: flex-start; overflow: hidden; margin-right: 1rem;">
                        <!-- Icon type -->
                        @php
                            $icon = 'info';
                            $iconColor = 'var(--secondary)';
                            $iconBg = 'rgba(79, 70, 229, 0.1)';
                            if ($notif->tipe === 'tugas' || $notif->tipe === 'deadline') {
                                $icon = 'clipboard-list';
                                $iconColor = '#10B981';
                                $iconBg = 'rgba(16, 185, 129, 0.1)';
                            } elseif ($notif->tipe === 'jadwal') {
                                $icon = 'calendar';
                                $iconColor = '#f59e0b';
                                $iconBg = 'rgba(245, 158, 11, 0.1)';
                            }
                        @endphp
                        <div style="background-color: {{ $iconBg }}; color: {{ $iconColor }}; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i data-lucide="{{ $icon }}" style="width: 20px; height: 20px;"></i>
                        </div>
                        <div>
                            <h4 style="font-weight: 700; font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem; color: var(--text-main);">
                                <span>{{ $notif->translated_judul }}</span>
                                @if(!$notif->is_read)
                                    <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #ef4444;" title="Baru"></span>
                                @endif
                            </h4>
                            <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.4; margin-top: 0.15rem;">
                                {{ $notif->translated_pesan }}
                            </p>
                            <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-top: 0.35rem; font-weight: 500;">
                                {{ $notif->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                    
                    <div>
                        <a href="{{ route('notifications.read', $notif->id) }}" class="btn {{ $notif->is_read ? 'btn-secondary' : 'btn-primary' }}" style="width: auto; padding: 0.4rem 1rem; font-size: 0.8rem;">
                            <span>{{ $notif->is_read ? __('Lihat') : __('Baca & Buka') }}</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Pagination Links -->
        <div style="margin-top: 2rem;">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
