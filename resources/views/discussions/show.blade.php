@extends('layouts.app')

@section('title', $discussion->judul)

@section('styles')
<style>
    /* Set --class-color globally for entire page */
    :root {
        --class-color: {{ $class->color ?: '#10B981' }};
    }
    .btn-primary {
        color: #ffffff !important;
    }
    .discussion-card {
        border-top: 4px solid var(--class-color);
        margin-bottom: 2rem;
    }
    .reply-card {
        border-left: 3px solid var(--border-color);
        margin-bottom: 1rem;
        background-color: var(--bg-app);
    }
    .reply-card.dosen-reply {
        border-left: 3px solid var(--class-color);
        background-color: var(--primary-soft);
    }
</style>
@endsection

@section('content')
<!-- Back Link -->
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('classes.show', $class->id) }}" style="display: inline-flex; align-items: center; gap: 0.35rem; text-decoration: none; color: var(--text-muted); font-weight: 600; font-size: 0.9rem;">
        <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
        <span>{{ __('Kembali ke Kelas') }}: {{ $class->nama_kelas }}</span>
    </a>
</div>

<div style="--class-color: {{ $class->color ?: '#10B981' }};">
    
    <!-- Main Thread Discussion Card -->
    <div class="card discussion-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <img src="{{ $discussion->user->avatar_url }}" style="width: 44px; height: 44px; border-radius: 50%; background-color: var(--primary-soft);" alt="Avatar">
                <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <h4 style="font-weight: 800; font-size: 1rem; color: var(--text-main);">{{ $discussion->user->name }}</h4>
                        <span style="background-color: {{ $discussion->user->role === 'dosen' ? 'var(--class-color)' : 'var(--bg-app)' }}; color: {{ $discussion->user->role === 'dosen' ? 'var(--text-white)' : 'var(--text-muted)' }}; font-size: 0.7rem; font-weight: 700; padding: 0.1rem 0.4rem; border-radius: var(--border-radius-sm); text-transform: capitalize; border: 1px solid var(--border-color);">
                            {{ $discussion->user->role }}
                        </span>
                    </div>
                    <span style="font-size: 0.75rem; color: var(--text-muted);">{{ __('Diposting') }} {{ $discussion->created_at->diffForHumans() }}</span>
                </div>
            </div>
            
            @if($discussion->is_pinned)
                <span style="background-color: rgba(245, 158, 11, 0.15); color: #d97706; padding: 0.25rem 0.6rem; border-radius: var(--border-radius-sm); font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                    <i data-lucide="pin" style="width: 12px; height: 12px;"></i> Pinned Thread
                </span>
            @endif
        </div>

        <h2 style="font-weight: 800; font-size: 1.5rem; letter-spacing: -0.02em; line-height: 1.3; margin-bottom: 1rem; color: var(--text-main);">
            {{ $discussion->judul }}
        </h2>
        
        <div style="line-height: 1.6; color: var(--text-main); font-size: 1rem; white-space: pre-line;">
            {{ $discussion->konten }}
        </div>
    </div>

    <!-- Replies Section -->
    <div style="margin-bottom: 2rem;">
        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
            <i data-lucide="messages-square" style="width: 20px; height: 20px; color: var(--text-muted);"></i>
            <span>{{ __('Balasan') }} ({{ $discussion->replies->count() }})</span>
        </h3>

        @if($discussion->replies->isEmpty())
            <div class="card" style="text-align: center; padding: 3rem 1rem;">
                <p style="color: var(--text-muted); font-size: 0.9rem;">{{ __('Belum ada balasan untuk diskusi ini. Jadilah yang pertama memberikan balasan!') }}</p>
            </div>
        @else
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                @foreach($discussion->replies as $reply)
                    @php $isDosenReply = $reply->user->role === 'dosen'; @endphp
                    <div class="card reply-card {{ $isDosenReply ? 'dosen-reply' : '' }}">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <img src="{{ $reply->user->avatar_url }}" style="width: 32px; height: 32px; border-radius: 50%; background-color: var(--primary-soft);" alt="Avatar">
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <h5 style="font-weight: 700; font-size: 0.9rem;">{{ $reply->user->name }}</h5>
                                    <span style="background-color: {{ $isDosenReply ? 'var(--class-color)' : 'var(--bg-app)' }}; color: {{ $isDosenReply ? 'var(--text-white)' : 'var(--text-muted)' }}; font-size: 0.65rem; font-weight: 700; padding: 0.05rem 0.3rem; border-radius: var(--border-radius-sm); border: 1px solid var(--border-color); text-transform: capitalize;">
                                        {{ $reply->user->role }}
                                    </span>
                                </div>
                                <span style="font-size: 0.7rem; color: var(--text-muted);">{{ $reply->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div style="font-size: 0.9rem; color: var(--text-main); white-space: pre-line; padding-left: 0.25rem;">
                            {{ $reply->konten }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Quick Reply Form Card -->
    <div class="card">
        <h4 style="font-weight: 700; font-size: 1.05rem; margin-bottom: 1rem;">{{ __('Berikan Balasan') }}</h4>
        
        <form action="{{ route('discussions.reply', $discussion->id) }}" method="POST">
            @csrf
            
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <textarea name="konten" class="form-control form-control-noicon" style="height: 120px; resize: none; font-size: 0.9rem;" placeholder="{{ __('Tulis jawaban, tanggapan, atau komentar diskusi Anda di sini...') }}" required></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: auto; padding: 0.6rem 2rem; background-color: var(--class-color);">
                <i data-lucide="send" style="width: 16px; height: 16px;"></i>
                <span>{{ __('Kirim Balasan') }}</span>
            </button>
        </form>
    </div>

</div>
@endsection

@section('scripts')
<script>
    lucide.createIcons();
</script>
@endsection
