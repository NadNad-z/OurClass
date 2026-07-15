@extends('layouts.app')

@section('title', __('Tugas Saya'))

@section('styles')
<style>
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        border-radius: var(--border-radius-sm);
        font-weight: 700;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .status-pending { background-color: #fef3c7; color: #d97706; }
    .status-submitted { background-color: #e0f2fe; color: #0284c7; }
    .status-graded { background-color: #dcfce7; color: #15803d; }
    .status-late { background-color: #fef2f2; color: #b91c1c; }

    .tab-btn-tasks {
        background: none;
        border: none;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-muted);
        cursor: pointer;
        position: relative;
        transition: color var(--transition-fast);
    }
    .tab-btn-tasks:hover {
        color: var(--text-main);
    }
    .tab-btn-tasks.active {
        color: var(--primary);
    }
    .tab-btn-tasks.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 3px;
        background-color: var(--primary);
        border-radius: var(--border-radius-full);
    }
</style>
@endsection

@section('content')
<div style="margin-bottom: 2rem;">
    <h1 style="font-weight: 800; font-size: 2.2rem; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
        {{ __('Daftar Tugas Kuliah') }} 📝
    </h1>
    <p style="color: var(--text-muted);">
        {{ __('Pantau seluruh tugas, kuis, dan ujian aktif dari kelas Anda.') }}
    </p>
</div>

<!-- Tabs Navigation for Tasks -->
<div style="display: flex; border-bottom: 2px solid var(--border-color); gap: 1rem; margin-bottom: 1.5rem; overflow-x: auto;">
    <button class="tab-btn-tasks active" onclick="switchTaskTab('upcoming')">
        {{ __('Tugas Aktif') }} ({{ $upcoming->count() }})
    </button>
    <button class="tab-btn-tasks" onclick="switchTaskTab('overdue')">
        {{ __('Tugas Terlewat / Lampau') }} ({{ $overdue->count() }})
    </button>
</div>

<!-- Upcoming Tasks Pane -->
<div id="upcoming-tasks" class="task-pane" style="display: block;">
    @if($upcoming->isEmpty())
        <div class="card" style="text-align: center; padding: 4rem 1.5rem;">
            <div style="background: rgba(16, 185, 129, 0.1); color: #10B981; width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <i data-lucide="check-circle" style="width: 32px; height: 32px;"></i>
            </div>
            <h4 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">{{ __('Semua Tugas Selesai!') }}</h4>
            <p style="color: var(--text-muted); font-size: 0.95rem; max-width: 400px; margin: 0 auto;">
                {{ __('Bagus sekali! Tidak ada tugas aktif yang perlu Anda kerjakan saat ini.') }}
            </p>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            @foreach($upcoming as $task)
                @php
                    $classColor = $task->classModel->color ?: '#10B981';
                @endphp
                <div class="card card-hover" style="border-left: 5px solid {{ $classColor }}; padding: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                        <div style="flex: 1; min-width: 250px;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; flex-wrap: wrap;">
                                <span style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; background-color: var(--primary-soft); color: var(--primary); padding: 0.2rem 0.5rem; border-radius: var(--border-radius-sm);">
                                    {{ __($task->tipe) }}
                                </span>
                                <span style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted);">
                                    {{ $task->classModel->nama_kelas }}
                                </span>
                            </div>
                            
                            <h3 style="font-weight: 800; font-size: 1.2rem; color: var(--text-main); margin-bottom: 0.5rem;">
                                <a href="{{ route('tasks.show', $task->id) }}" style="color: inherit; text-decoration: none; transition: color var(--transition-fast);" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='inherit'">
                                    {{ $task->judul }}
                                </a>
                            </h3>
                            
                            <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; font-size: 0.85rem; color: var(--text-muted);">
                                <div style="display: flex; align-items: center; gap: 0.25rem;">
                                    <i data-lucide="calendar" style="width: 14px; height: 14px;"></i>
                                    <span>Deadline: {{ app()->getLocale() == 'en' ? $task->deadline->format('d M Y, h:i A') : $task->deadline->format('d M Y, H.i') . ' WIB' }}</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.25rem;">
                                    <i data-lucide="award" style="width: 14px; height: 14px;"></i>
                                    <span>{{ __('Max Nilai') }}: {{ $task->nilai_max }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <!-- Task Status Badge -->
                            @if($user->role === 'mahasiswa')
                                @php
                                    $submission = $user->submissions->where('task_id', $task->id)->first();
                                @endphp
                                @if($submission)
                                    @if($submission->status === 'graded')
                                        <span class="status-badge status-graded">
                                            <i data-lucide="check-circle-2" style="width: 14px; height: 14px;"></i>
                                            {{ __('Nilai') }}: {{ $submission->nilai }}
                                        </span>
                                    @else
                                        <span class="status-badge status-submitted">
                                            <i data-lucide="check" style="width: 14px; height: 14px;"></i>
                                            {{ __('Dikumpulkan') }}
                                        </span>
                                    @endif
                                @else
                                    <span class="status-badge status-pending">
                                        <i data-lucide="clock" style="width: 14px; height: 14px;"></i>
                                        {{ __('Belum Dikumpul') }}
                                    </span>
                                @endif
                            @else
                                <!-- Dosen View -->
                                <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted);">
                                    {{ $task->submissions->count() }} / {{ $task->classModel->students()->count() }} {{ __('Dikumpulkan') }}
                                </span>
                            @endif

                            <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-primary" style="width: auto; padding: 0.5rem 1.25rem; font-size: 0.85rem; background-color: {{ $classColor }}; border-color: {{ $classColor }}; color: white; text-decoration: none;">
                                {{ __('Detail') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Overdue Tasks Pane -->
<div id="overdue-tasks" class="task-pane" style="display: none;">
    @if($overdue->isEmpty())
        <div class="card" style="text-align: center; padding: 4rem 1.5rem;">
            <div style="background: var(--primary-soft); color: var(--primary); width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <i data-lucide="calendar" style="width: 32px; height: 32px;"></i>
            </div>
            <h4 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">{{ __('Tidak Ada Tugas Lampau') }}</h4>
            <p style="color: var(--text-muted); font-size: 0.95rem; max-width: 400px; margin: 0 auto;">
                {{ __('Tidak ada tugas kuliah masa lalu yang terdaftar.') }}
            </p>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            @foreach($overdue as $task)
                @php
                    $classColor = $task->classModel->color ?: '#10B981';
                @endphp
                <div class="card" style="border-left: 5px solid {{ $classColor }}; padding: 1.5rem; opacity: 0.8;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                        <div style="flex: 1; min-width: 250px;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; flex-wrap: wrap;">
                                <span style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; background-color: var(--border-color); color: var(--text-muted); padding: 0.2rem 0.5rem; border-radius: var(--border-radius-sm);">
                                    {{ __($task->tipe) }}
                                </span>
                                <span style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted);">
                                    {{ $task->classModel->nama_kelas }}
                                </span>
                            </div>
                            
                            <h3 style="font-weight: 800; font-size: 1.2rem; color: var(--text-main); margin-bottom: 0.5rem;">
                                <a href="{{ route('tasks.show', $task->id) }}" style="color: inherit; text-decoration: none; transition: color var(--transition-fast);" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='inherit'">
                                    {{ $task->judul }}
                                </a>
                            </h3>
                            
                            <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; font-size: 0.85rem; color: var(--text-muted);">
                                <div style="display: flex; align-items: center; gap: 0.25rem;">
                                    <i data-lucide="calendar" style="width: 14px; height: 14px;"></i>
                                    <span>Deadline: {{ app()->getLocale() == 'en' ? $task->deadline->format('d M Y, h:i A') : $task->deadline->format('d M Y, H.i') . ' WIB' }}</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.25rem;">
                                    <i data-lucide="award" style="width: 14px; height: 14px;"></i>
                                    <span>{{ __('Max Nilai') }}: {{ $task->nilai_max }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <!-- Task Status Badge -->
                            @if($user->role === 'mahasiswa')
                                @php
                                    $submission = $user->submissions->where('task_id', $task->id)->first();
                                @endphp
                                @if($submission)
                                    @if($submission->status === 'graded')
                                        <span class="status-badge status-graded">
                                            <i data-lucide="check-circle-2" style="width: 14px; height: 14px;"></i>
                                            {{ __('Nilai') }}: {{ $submission->nilai }}
                                        </span>
                                    @else
                                        <span class="status-badge status-submitted">
                                            <i data-lucide="check" style="width: 14px; height: 14px;"></i>
                                            {{ __('Dikumpulkan') }}
                                        </span>
                                    @endif
                                @else
                                    <span class="status-badge status-late">
                                        <i data-lucide="alert-triangle" style="width: 14px; height: 14px;"></i>
                                        {{ __('Terlewat') }}
                                    </span>
                                @endif
                            @else
                                <!-- Dosen View -->
                                <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted);">
                                    {{ $task->submissions->count() }} / {{ $task->classModel->students()->count() }} {{ __('Dikumpulkan') }}
                                </span>
                            @endif

                            <a href="{{ route('tasks.show', $task->id) }}" class="btn" style="width: auto; padding: 0.5rem 1.25rem; font-size: 0.85rem; border: 1px solid var(--border-color); color: var(--text-main); text-decoration: none;">
                                {{ __('Detail') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@section('scripts')
<script>
    function switchTaskTab(tabName) {
        // Toggle buttons
        const buttons = document.querySelectorAll('.tab-btn-tasks');
        buttons.forEach(btn => btn.classList.remove('active'));
        
        // Toggle active button
        const activeBtn = Array.from(buttons).find(btn => btn.getAttribute('onclick').includes(tabName));
        if (activeBtn) activeBtn.classList.add('active');
        
        // Toggle panes
        const panes = document.querySelectorAll('.task-pane');
        panes.forEach(pane => pane.style.display = 'none');
        
        document.getElementById(tabName + '-tasks').style.display = 'block';
    }
</script>
@endsection
@endsection
