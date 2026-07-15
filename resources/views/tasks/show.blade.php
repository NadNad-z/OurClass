@extends('layouts.app')

@section('title', $task->judul)

@section('styles')
<style>
    /* Set --class-color globally for entire page */
    :root {
        --class-color: {{ $class->color ?: '#10B981' }};
    }
    .btn-primary {
        color: #ffffff !important;
    }
    .task-container {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
    }
    @media (max-width: 1024px) {
        .task-container {
            grid-template-columns: 1fr;
        }
    }
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

    .grade-display {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        color: var(--text-white);
        border-radius: var(--border-radius-md);
        padding: 1.5rem;
        text-align: center;
        box-shadow: var(--shadow-sm);
    }
    .submission-item {
        border-bottom: 1px solid var(--border-color);
        padding: 1rem 0;
    }
    .submission-item:last-child {
        border-bottom: none;
    }
    /* Modal Styles for grading */
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
        max-width: 450px;
        box-shadow: var(--shadow-lg);
        transform: translateY(20px);
        transition: all var(--transition-normal);
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
<!-- Back Link -->
<div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
    <a href="{{ route('classes.show', $class->id) }}" style="display: inline-flex; align-items: center; gap: 0.35rem; text-decoration: none; color: var(--text-muted); font-weight: 600; font-size: 0.9rem;">
        <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
        <span>{{ __('Kembali ke Kelas') }}: {{ $class->nama_kelas }}</span>
    </a>
    
    @if($user->isClassAdmin($class))
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            @if(!isset($editMode) || !$editMode)
            <a href="{{ route('tasks.edit', $task->id) }}" class="btn" style="background-color: var(--bg-app); color: var(--text-main); border: 1px solid var(--border-color); width: auto; padding: 0.5rem 1rem; font-size: 0.85rem; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="edit" style="width: 16px; height: 16px;"></i>
                <span>{{ __('Edit Tugas') }}</span>
            </a>
            @endif
            <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('{{ __('Apakah Anda yakin ingin menghapus tugas ini?') }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn" style="background-color: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.2); width: auto; padding: 0.5rem 1rem; font-size: 0.85rem;">
                    <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                    <span>{{ __('Hapus Tugas') }}</span>
                </button>
            </form>
        </div>
    @endif
</div>

<div class="task-container" style="--class-color: {{ $class->color ?: '#10B981' }};">
    
    <!-- Left Column: Task Detail and Description -->
    <div style="display: flex; flex-direction: column; gap: 2rem;">
        
        @if(isset($editMode) && $editMode)
        <!-- Edit Task Form -->
        <div class="card">
            <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 1.5rem;">{{ __('Edit Tugas') }}</h2>
            <form action="{{ route('tasks.update', $task->id) }}" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1.5rem;">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; font-size: 0.9rem;">{{ __('Judul Tugas') }}</label>
                    <input type="text" name="judul" class="form-control" value="{{ old('judul', $task->judul) }}" required>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; font-size: 0.9rem;">{{ __('Tipe Tugas') }}</label>
                        <select name="tipe" class="form-control" required style="padding: 0.65rem 1rem; border-radius: var(--border-radius-md); border: 1px solid var(--border-color); width: 100%; font-family: inherit; font-size: 0.95rem;">
                            <option value="tugas" {{ old('tipe', $task->tipe) == 'tugas' ? 'selected' : '' }}>Tugas</option>
                            <option value="kuis" {{ old('tipe', $task->tipe) == 'kuis' ? 'selected' : '' }}>Kuis</option>
                            <option value="ujian" {{ old('tipe', $task->tipe) == 'ujian' ? 'selected' : '' }}>Ujian</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; font-size: 0.9rem;">{{ __('Nilai Maksimal') }}</label>
                        <input type="number" name="nilai_max" class="form-control" value="{{ old('nilai_max', $task->nilai_max) }}" min="1" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; font-size: 0.9rem;">{{ __('Batas Waktu (Deadline)') }}</label>
                    <input type="datetime-local" name="deadline" class="form-control" value="{{ old('deadline', $task->deadline->format('Y-m-d\TH:i')) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; font-size: 0.9rem;">{{ __('Deskripsi / Instruksi') }}</label>
                    <textarea name="deskripsi" class="form-control" rows="5">{{ old('deskripsi', $task->deskripsi) }}</textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; font-size: 0.9rem;">{{ __('Lampiran Soal Baru (Opsional)') }}</label>
                    <input type="file" name="file_soal" class="form-control" style="padding-top: 0.65rem;">
                    @if($task->file_soal)
                        <div style="margin-top: 0.75rem; font-size: 0.85rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem; background-color: var(--bg-app); padding: 0.75rem; border-radius: var(--border-radius-sm); border: 1px solid var(--border-color);">
                            <input type="checkbox" name="hapus_file" id="hapus_file" value="1">
                            <label for="hapus_file">{{ __('Hapus file lampiran saat ini:') }} <strong style="color: var(--text-main);">{{ basename($task->file_soal) }}</strong></label>
                        </div>
                    @endif
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
                    <a href="{{ route('tasks.show', $task->id) }}" class="btn" style="background-color: var(--bg-app); color: var(--text-main); border: 1px solid var(--border-color); width: auto; padding: 0.65rem 1.25rem;">{{ __('Batal') }}</a>
                    <button type="submit" class="btn" style="background-color: var(--primary); color: white; width: auto; padding: 0.65rem 1.5rem; font-weight: 700; border: none; display: flex; align-items: center;">
                        <i data-lucide="save" style="width: 18px; height: 18px; margin-right: 0.5rem;"></i>
                        {{ __('Simpan Perubahan') }}
                    </button>
                </div>
            </form>
        </div>
        @else
        <div class="card">
            <!-- Header Meta -->
            <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.75rem;">
                <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; background-color: var(--bg-app); border: 1px solid var(--border-color); padding: 0.25rem 0.6rem; border-radius: var(--border-radius-sm);">
                    {{ __($task->tipe) }}
                </span>
                <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">
                    {{ __('Dibuat oleh') }}: {{ $task->creator->name }}
                </span>
            </div>

            <!-- Title -->
            <h1 style="font-weight: 800; font-size: 2rem; letter-spacing: -0.02em; line-height: 1.25; margin-bottom: 1rem; color: var(--text-main);">
                {{ $task->judul }}
            </h1>

            <!-- Deadline Box -->
            <div style="background-color: var(--bg-app); border: 1px solid var(--border-color); border-radius: var(--border-radius-md); padding: 1rem; display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                <div style="background-color: rgba(239, 68, 68, 0.1); color: #ef4444; width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i data-lucide="clock"></i>
                </div>
                <div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">{{ __('Batas Waktu Pengumpulan') }}</div>
                    <div style="font-size: 1rem; font-weight: 800; color: #ef4444;">
                        {{ app()->getLocale() == 'en' ? $task->deadline->format('l, d F Y, h:i A') : $task->deadline->format('l, d F Y, H.i') . ' WIB' }}
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <h3 style="font-weight: 700; font-size: 1.15rem; margin-bottom: 0.75rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">{{ __('Instruksi Pengerjaan') }}</h3>
            <div style="line-height: 1.6; color: var(--text-main); font-size: 0.95rem; margin-bottom: 2rem;">
                {!! nl2br(e($task->deskripsi ?: 'Tidak ada instruksi khusus.')) !!}
            </div>

            <!-- Attachment File -->
            @if($task->file_soal)
                <div style="border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
                    <h4 style="font-weight: 700; font-size: 0.95rem; margin-bottom: 0.75rem;">{{ __('Lampiran Soal') }}:</h4>
                    <a href="{{ asset('storage/' . $task->file_soal) }}" target="_blank" class="btn" style="background-color: var(--bg-app); border: 1px solid var(--border-color); color: var(--text-main); font-size: 0.85rem; width: auto; display: inline-flex;">
                        <i data-lucide="download" style="width: 16px; height: 16px;"></i>
                        <span>{{ __('Unduh Soal') }} ({{ basename($task->file_soal) }})</span>
                    </a>
                </div>
            @endif
        </div>
        @endif

        <!-- Lecturer View: Student Submissions Grid -->
        @if($user->isClassAdmin($class))
            <div class="card">
                <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1.25rem;">{{ __('Pengumpulan Mahasiswa') }}</h3>
                
                @if($submissions->isEmpty())
                    <div style="text-align: center; padding: 4rem 1rem;">
                        <i data-lucide="clipboard" style="width: 48px; height: 48px; color: var(--text-muted); margin-bottom: 1rem; opacity: 0.6;"></i>
                        <h4 style="font-weight: 700; margin-bottom: 0.25rem;">{{ __('Belum Ada Jawaban Masuk') }}</h4>
                        <p style="color: var(--text-muted); font-size: 0.85rem;">{{ __('Mahasiswa di kelas ini belum ada yang mengumpulkan jawaban.') }}</p>
                    </div>
                @else
                    <div style="display: flex; flex-direction: column;">
                        @foreach($submissions as $sub)
                            <div class="submission-item" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <img src="https://api.dicebear.com/7.x/adventurer/svg?seed={{ urlencode($sub->user->name) }}" style="width: 40px; height: 40px; border-radius: 50%; background-color: var(--primary-soft);" alt="Avatar">
                                    <div>
                                        <h4 style="font-weight: 700; font-size: 0.95rem;">{{ $sub->user->name }}</h4>
                                        <div style="font-size: 0.8rem; color: var(--text-muted); display: flex; gap: 0.5rem; align-items: center; margin-top: 0.15rem;">
                                            <span>NIM: {{ $sub->user->nim_nip ?: '-' }}</span>
                                            &bull;
                                            <span>{{ __('Dikumpul') }}: {{ app()->getLocale() == 'en' ? $sub->submitted_at->format('d M h:i A') : $sub->submitted_at->format('d M H.i') }}</span>
                                            @if($sub->isLate())
                                                <span class="status-badge status-late" style="font-size: 0.65rem; padding: 0.1rem 0.3rem;">{{ __('Terlambat') }}</span>
                                            @endif
                                        </div>
                                        @if($sub->catatan)
                                            <p style="font-size: 0.8rem; color: var(--text-muted); font-style: italic; margin-top: 0.35rem;">
                                                "{{ $sub->catatan }}"
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <!-- Attachment -->
                                    <a href="{{ asset('storage/' . $sub->file) }}" target="_blank" class="action-btn" title="Unduh File Jawaban" style="width: 36px; height: 36px;">
                                        <i data-lucide="file-down"></i>
                                    </a>
                                    
                                    <!-- Grade Display / Action -->
                                    @if($sub->isGraded())
                                        <div style="text-align: right;">
                                            <div style="font-size: 1.15rem; font-weight: 800; color: var(--primary);">{{ $sub->nilai }} / {{ $task->nilai_max }}</div>
                                            <button onclick="openGradingModal({{ $sub->id }}, '{{ $sub->user->name }}', {{ $sub->nilai }}, '{{ $sub->feedback }}')" style="background: none; border: none; color: var(--secondary); font-size: 0.75rem; font-weight: 700; cursor: pointer; text-decoration: underline;">{{ __('Edit Nilai') }}</button>
                                        </div>
                                    @else
                                        <button class="btn btn-primary" onclick="openGradingModal({{ $sub->id }}, '{{ $sub->user->name }}', null, '')" style="width: auto; padding: 0.5rem 1rem; font-size: 0.8rem; background-color: var(--secondary);">
                                            <span>{{ __('Beri Nilai') }}</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

    </div>

    <!-- Right Column: Student Submission Card & Score Display -->
    <div style="display: flex; flex-direction: column; gap: 2rem;">
        
        <!-- Points / Score Card Info -->
        @if(!$class->is_private)
            <div class="card" style="text-align: center;">
                <h3 style="font-size: 0.9rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em; margin-bottom: 0.5rem;">{{ __('Nilai Maksimal') }}</h3>
                <div style="font-size: 3rem; font-weight: 800; color: var(--text-main); line-height: 1.1;">
                    {{ $task->nilai_max }}
                </div>
                <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500; margin-top: 0.5rem;">Points</div>
            </div>
        @endif

        <!-- Student view: Submission Box -->
        @if(!$user->isClassAdmin($class) && !$class->is_private)
            <div class="card">
                <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between;">
                    <span>{{ __('Tugas Saya') }}</span>
                    
                    @if($submission)
                        @if($submission->isGraded())
                            <span class="status-badge status-graded">{{ __('Dinilai') }}</span>
                        @elseif($submission->isLate())
                            <span class="status-badge status-late">{{ __('Lambat') }}</span>
                        @else
                            <span class="status-badge status-submitted">{{ __('Terkumpul') }}</span>
                        @endif
                    @else
                        <span class="status-badge status-pending">{{ __('Belum Dikumpul') }}</span>
                    @endif
                </h3>

                <!-- Score Display if Graded -->
                @if($submission && $submission->isGraded())
                    <div class="grade-display" style="margin-bottom: 1.5rem;">
                        <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.8; font-weight: 700;">{{ __('Nilai Anda') }}</div>
                        <div style="font-size: 2.5rem; font-weight: 800; margin: 0.25rem 0;">{{ $submission->nilai }}</div>
                        <div style="font-size: 0.8rem; opacity: 0.9;">Skala 0 - {{ $task->nilai_max }}</div>
                    </div>
                    
                    @if($submission->feedback)
                        <div style="background-color: var(--bg-app); border: 1px solid var(--border-color); border-radius: var(--border-radius-md); padding: 1rem; text-align: left; font-size: 0.85rem;">
                            <div style="font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">{{ __('Feedback Dosen') }}:</div>
                            <div style="color: var(--text-muted); font-style: italic;">"{{ $submission->feedback }}"</div>
                        </div>
                    @endif
                @endif

                <!-- Upload Form (Disabled if graded, otherwise enabled) -->
                @if(!$submission || !$submission->isGraded())
                    <form action="{{ route('submissions.submit', $task->id) }}" method="POST" enctype="multipart/form-data" style="margin-top: 1.5rem;">
                        @csrf
                        
                        <div class="form-group">
                            <label for="file_jawaban" class="form-label">{{ __('File Jawaban') }} *</label>
                            <input type="file" name="file_jawaban" id="file_jawaban" class="form-control form-control-noicon" style="padding: 0.5rem;" required>
                            <span style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem; display: block;">{{ __('Format: PDF, Zip, Word, Gambar (Max 20MB)') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="catatan" class="form-label">{{ __('Catatan Untuk Dosen (Optional)') }}</label>
                            <textarea name="catatan" id="catatan" class="form-control form-control-noicon" style="height: 80px; resize: none; font-size: 0.85rem;" placeholder="{{ __('Tulis catatan penyerahan tugas jika ada...') }}">{{ $submission ? $submission->catatan : '' }}</textarea>
                        </div>

                        @if($submission)
                            <!-- File submitted link -->
                            <div style="margin-bottom: 1.25rem; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i data-lucide="paperclip" style="width: 16px; height: 16px; color: var(--primary);"></i>
                                <span style="font-weight: 600;">{{ __('File Terkumpul') }}:</span>
                                <a href="{{ asset('storage/' . $submission->file) }}" target="_blank" style="color: var(--primary); text-decoration: underline; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 120px;">
                                    {{ basename($submission->file) }}
                                </a>
                            </div>
                        @endif

                        <button type="submit" class="btn btn-primary" style="background-color: var(--class-color);">
                            <i data-lucide="upload-cloud" style="width: 18px; height: 18px;"></i>
                            <span>{{ $submission ? __('Kirim Ulang Jawaban (Revisi)') : __('Kumpulkan Jawaban') }}</span>
                        </button>
                    </form>
                @endif
            </div>
        @endif

    </div>

</div>


<!-- ===== Q&A / Tanya-Jawab Section ===== -->
<div style="margin-top: 2.5rem;">
    <div class="card" style="border-top: 3px solid var(--class-color);">
        <!-- Section Header -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="background: linear-gradient(135deg, var(--class-color), var(--secondary)); width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i data-lucide="help-circle" style="width: 20px; height: 20px; color: white;"></i>
                </div>
                <div>
                    <h2 style="font-size: 1.2rem; font-weight: 800; margin: 0; letter-spacing: -0.01em;">{{ __('Tanya Jawab Tugas') }}</h2>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0;">{{ $taskDiscussions->count() }} {{ __('pertanyaan') }} · {{ __('Tanya langsung di sini!') }}</p>
                </div>
            </div>
            <button onclick="document.getElementById('qa-form-area').classList.toggle('hidden')" 
                    style="display: inline-flex; align-items: center; gap: 0.5rem; background: linear-gradient(135deg, var(--class-color), var(--secondary)); color: white; border: none; padding: 0.6rem 1.25rem; border-radius: 9999px; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 4px 10px rgba(0,0,0,0.15);"
                    onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 14px rgba(0,0,0,0.2)'"
                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.15)'">
                <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                {{ __('Ajukan Pertanyaan') }}
            </button>
        </div>

        <!-- Post Question Form (hidden by default, toggles on click) -->
        <div id="qa-form-area" class="hidden" style="background: linear-gradient(135deg, rgba(16,185,129,0.04), rgba(79,70,229,0.04)); border: 1.5px solid var(--border-color); border-radius: var(--border-radius-md); padding: 1.5rem; margin-bottom: 1.5rem;">
            <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="help-circle" style="width: 16px; height: 16px; color: var(--class-color);"></i>
                {{ __('Ajukan Pertanyaan Baru') }}
            </h4>
            <form action="{{ route('discussions.store', $class->id) }}" method="POST">
                @csrf
                <input type="hidden" name="task_id" value="{{ $task->id }}">
                <div class="form-group" style="margin-bottom: 0.75rem;">
                    <input type="text" name="judul" class="form-control form-control-noicon" 
                           placeholder="{{ __('Tulis pertanyaan singkat...') }}" required
                           style="font-size: 0.9rem;">
                </div>
                <div class="form-group" style="margin-bottom: 1rem;">
                    <textarea name="konten" class="form-control form-control-noicon" rows="3"
                              placeholder="{{ __('Jelaskan pertanyaanmu lebih detail di sini...') }}" required
                              style="font-size: 0.9rem; resize: vertical; height: 80px;"></textarea>
                </div>
                <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                    <button type="button" onclick="document.getElementById('qa-form-area').classList.add('hidden')"
                            style="padding: 0.55rem 1.25rem; background: transparent; border: 1.5px solid var(--border-color); color: var(--text-muted); border-radius: 9999px; font-weight: 600; font-size: 0.85rem; cursor: pointer;">
                        {{ __('Batal') }}
                    </button>
                    <button type="submit"
                            style="padding: 0.55rem 1.5rem; background: linear-gradient(135deg, var(--class-color), var(--secondary)); color: white; border: none; border-radius: 9999px; font-weight: 700; font-size: 0.85rem; cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                        <i data-lucide="send" style="width: 14px; height: 14px; display: inline; margin-right: 4px;"></i>
                        {{ __('Kirim Pertanyaan') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Success/Error messages -->
        @if(session('success'))
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 0.75rem 1rem; border-radius: var(--border-radius-md); margin-bottom: 1rem; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="check-circle-2" style="width: 16px; height: 16px; flex-shrink: 0;"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Q&A Thread List -->
        @if($taskDiscussions->isEmpty())
            <div style="text-align: center; padding: 3rem 1rem; background: var(--bg-app); border-radius: var(--border-radius-md);">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, rgba(16,185,129,0.1), rgba(79,70,229,0.1)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                    <i data-lucide="message-circle" style="width: 28px; height: 28px; color: var(--class-color);"></i>
                </div>
                <h4 style="font-weight: 700; margin-bottom: 0.25rem; color: var(--text-main);">{{ __('Belum Ada Pertanyaan') }}</h4>
                <p style="color: var(--text-muted); font-size: 0.85rem; max-width: 300px; margin: 0 auto;">
                    {{ __('Jadilah yang pertama mengajukan pertanyaan seputar tugas ini!') }}
                </p>
            </div>
        @else
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                @foreach($taskDiscussions as $qa)
                    <div style="border: 1px solid var(--border-color); border-radius: var(--border-radius-md); overflow: hidden; background: var(--bg-card);">
                        <!-- Question Header -->
                        <div style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color);">
                            <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                                <img src="https://api.dicebear.com/7.x/adventurer/svg?seed={{ urlencode($qa->user->name) }}"
                                     style="width: 36px; height: 36px; border-radius: 50%; background-color: var(--primary-soft); flex-shrink: 0;" alt="Avatar">
                                <div style="flex: 1; min-width: 0;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.3rem;">
                                        <span style="font-weight: 700; font-size: 0.9rem;">{{ $qa->user->name }}</span>
                                        <span style="background: rgba(16,185,129,0.1); color: var(--class-color); font-size: 0.7rem; font-weight: 700; padding: 0.1rem 0.4rem; border-radius: 9999px; text-transform: capitalize;">
                                            {{ $qa->user->role }}
                                        </span>
                                        <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $qa->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-main); margin-bottom: 0.4rem;">{{ $qa->judul }}</div>
                                    <div style="font-size: 0.875rem; color: var(--text-muted); line-height: 1.5;">{{ $qa->konten }}</div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.4rem; flex-shrink: 0; font-size: 0.8rem; color: var(--text-muted);">
                                    <i data-lucide="message-square" style="width: 14px; height: 14px;"></i>
                                    <span>{{ $qa->replies->count() }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Replies -->
                        @if($qa->replies->count() > 0)
                            <div style="padding: 0; background: var(--bg-app);">
                                @foreach($qa->replies as $reply)
                                    @php $isAdmin = $reply->user->id === $class->admin_id; @endphp
                                    <div style="display: flex; gap: 0.75rem; padding: 0.85rem 1.25rem; border-bottom: 1px solid var(--border-color); {{ $isAdmin ? 'background: linear-gradient(135deg, rgba(16,185,129,0.04), rgba(79,70,229,0.04));' : '' }}">
                                        <img src="https://api.dicebear.com/7.x/adventurer/svg?seed={{ urlencode($reply->user->name) }}"
                                             style="width: 28px; height: 28px; border-radius: 50%; background-color: var(--primary-soft); flex-shrink: 0;" alt="Avatar">
                                        <div style="flex: 1; min-width: 0;">
                                            <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap; margin-bottom: 0.2rem;">
                                                <span style="font-weight: 700; font-size: 0.82rem;">{{ $reply->user->name }}</span>
                                                @if($isAdmin)
                                                    <span style="background: linear-gradient(135deg, var(--class-color), var(--secondary)); color: white; font-size: 0.65rem; font-weight: 800; padding: 0.1rem 0.45rem; border-radius: 9999px;">{{ __('PENGAJAR') }}</span>
                                                @endif
                                                <span style="font-size: 0.72rem; color: var(--text-muted);">{{ $reply->created_at->diffForHumans() }}</span>
                                            </div>
                                            <div style="font-size: 0.875rem; color: var(--text-main); line-height: 1.5;">{{ $reply->konten }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Reply Form -->
                        <div style="padding: 0.75rem 1.25rem; background: var(--bg-app); border-top: {{ $qa->replies->count() ? '0' : '1px solid var(--border-color)' }};">
                            <form action="{{ route('discussions.reply', $qa->id) }}" method="POST" 
                                  style="display: flex; gap: 0.5rem; align-items: flex-start;">
                                @csrf
                                <img src="https://api.dicebear.com/7.x/adventurer/svg?seed={{ urlencode($user->name) }}"
                                     style="width: 28px; height: 28px; border-radius: 50%; background-color: var(--primary-soft); flex-shrink: 0;" alt="Avatar">
                                <input type="text" name="konten" 
                                       placeholder="{{ __('Tulis balasan...') }}" required
                                       style="flex: 1; border: 1.5px solid var(--border-color); border-radius: 9999px; padding: 0.45rem 1rem; font-size: 0.82rem; font-family: inherit; background: var(--bg-card); color: var(--text-main); outline: none; transition: border-color 0.2s;"
                                       onfocus="this.style.borderColor='var(--class-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                                <button type="submit"
                                        style="background: var(--class-color); color: white; border: none; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: all 0.2s;"
                                        onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                                    <i data-lucide="send" style="width: 14px; height: 14px;"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Grading Modal (Dosen only) -->
@if($user->isClassAdmin($class))

    <div class="modal-overlay" id="gradingModal">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">{{ __('Beri Nilai Tugas') }}</h3>
                <button class="modal-close" onclick="closeGradingModal()">
                    <i data-lucide="x" style="width: 20px; height: 20px;"></i>
                </button>
            </div>
            
            <form id="gradingForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div style="margin-bottom: 1.25rem;">
                        <span style="font-size: 0.8rem; color: var(--text-muted);">{{ __('Mahasiswa') }}:</span>
                        <h4 id="grade-student-name" style="font-weight: 800; font-size: 1.1rem; color: var(--text-main);">{{ __('Nama Mahasiswa') }}</h4>
                    </div>

                    <!-- Nilai -->
                    <div class="form-group">
                        <label for="input_nilai" class="form-label">{{ __('Input Nilai') }} (0 - {{ $task->nilai_max }}) *</label>
                        <input type="number" name="nilai" id="input_nilai" class="form-control form-control-noicon" min="0" max="{{ $task->nilai_max }}" step="0.01" required>
                    </div>

                    <!-- Feedback -->
                    <div class="form-group">
                        <label for="input_feedback" class="form-label">{{ __('Umpan Balik (Feedback)') }}</label>
                        <textarea name="feedback" id="input_feedback" class="form-control form-control-noicon" style="height: 100px; resize: none;" placeholder="{{ __('Tulis komentar atau masukan untuk pengerjaan mahasiswa...') }}"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" style="width: auto;" onclick="closeGradingModal()">{{ __('Batal') }}</button>
                    <button type="submit" class="btn btn-primary" style="width: auto; padding-left: 2rem; padding-right: 2rem; background-color: var(--class-color);">{{ __('Simpan Nilai') }}</button>
                </div>
            </form>
        </div>
    </div>
@endif

@endsection

@section('scripts')
@if($user->isClassAdmin($class))
<script>
    function openGradingModal(submissionId, studentName, currentGrade, currentFeedback) {
        // Set action form dynamically
        const form = document.getElementById('gradingForm');
        form.action = `/submissions/${submissionId}/grade`;
        
        // Set values
        document.getElementById('grade-student-name').textContent = studentName;
        document.getElementById('input_nilai').value = currentGrade;
        document.getElementById('input_feedback').value = currentFeedback;
        
        // Open modal
        document.getElementById('gradingModal').classList.add('active');
        setTimeout(() => {
            document.getElementById('input_nilai').focus();
        }, 100);
    }

    function closeGradingModal() {
        document.getElementById('gradingModal').classList.remove('active');
    }

    // Close on click outside
    window.addEventListener('click', (e) => {
        if (e.target.id === 'gradingModal') {
            closeGradingModal();
        }
    });
</script>
@endif
@endsection
