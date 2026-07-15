@extends('layouts.app')

@section('title', 'Diskusi Kelas')

@section('content')
<div style="margin-bottom: 2rem;">
    <h1 style="font-weight: 800; font-size: 2.2rem; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
        {{ __('Forum Diskusi Kelas') }} 💬
    </h1>
    <p style="color: var(--text-muted);">
        {{ __('Semua topik diskusi umum dari seluruh kelas yang Anda ikuti atau ampu.') }}
    </p>
</div>

<div style="display: flex; flex-direction: column; gap: 1.5rem;">
    @if($discussions->isEmpty())
        <div class="card" style="text-align: center; padding: 4rem 1.5rem;">
            <div style="background: rgba(79, 70, 229, 0.1); color: var(--secondary); width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <i data-lucide="message-square" style="width: 32px; height: 32px;"></i>
            </div>
            <h4 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">{{ __('Belum Ada Diskusi') }}</h4>
            <p style="color: var(--text-muted); font-size: 0.95rem; max-width: 400px; margin: 0 auto;">
                {{ __('Tidak ada topik diskusi yang dibuat di kelas Anda saat ini.') }}
            </p>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            @foreach($discussions as $disc)
                @php
                    $classColor = $disc->classModel->color ?: '#10B981';
                @endphp
                <div class="card card-hover" style="border-top: 4px solid {{ $classColor }}; padding: 1.5rem; position: relative;">
                    @if($disc->is_pinned)
                        <span style="position: absolute; top: 1.5rem; right: 1.5rem; background-color: rgba(245, 158, 11, 0.12); color: #c2410c; padding: 0.25rem 0.5rem; border-radius: var(--border-radius-sm); font-size: 0.7rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                            <i data-lucide="pin" style="width: 12px; height: 12px;"></i> Pinned
                        </span>
                    @endif
                    
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem; flex-wrap: wrap;">
                        <span style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; background-color: var(--primary-soft); color: var(--primary); padding: 0.2rem 0.5rem; border-radius: var(--border-radius-sm);">
                            {{ $disc->classModel->nama_kelas }}
                        </span>
                            &bull; {{ __('Diposting oleh') }} <strong>{{ $disc->user->name }}</strong> ({{ __($disc->user->role) }}) {{ $disc->created_at->diffForHumans() }}
                    </div>

                    <h3 style="font-weight: 800; font-size: 1.3rem; line-height: 1.3; color: var(--text-main); margin-bottom: 0.75rem;">
                        <a href="{{ route('discussions.show', $disc->id) }}" style="color: inherit; text-decoration: none; transition: color var(--transition-fast);" onmouseover="this.style.color='{{ $classColor }}'" onmouseout="this.style.color='inherit'">
                            {{ $disc->judul }}
                        </a>
                    </h3>

                    <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.5; margin-bottom: 1.25rem; white-space: pre-line;">
                        {{ Str::limit($disc->konten, 200) }}
                    </p>

                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 1rem; flex-wrap: wrap; gap: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.35rem; color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">
                            <i data-lucide="message-circle" style="width: 16px; height: 16px;"></i>
                            <span>{{ $disc->replies->count() }} {{ __('Balasan') }}</span>
                        </div>

                        <a href="{{ route('discussions.show', $disc->id) }}" class="btn btn-primary" style="width: auto; padding: 0.5rem 1.25rem; font-size: 0.85rem; background-color: {{ $classColor }}; border-color: {{ $classColor }}; text-decoration: none; color: white;">
                            {{ __('Buka Diskusi') }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
