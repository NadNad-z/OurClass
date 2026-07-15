@extends('layouts.app')

@section('title', __('Agenda & Jadwal'))

@section('content')
<div style="margin-bottom: 2rem;">
    <h1 style="font-weight: 800; font-size: 2.2rem; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
        {{ __('Agenda & Jadwal Kuliah') }} 📅
    </h1>
    <p style="color: var(--text-muted);">
        {{ __('Jadwal terpadu dari seluruh kelas yang Anda ikuti atau kelola.') }}
    </p>
</div>

<div style="display: flex; flex-direction: column; gap: 1.5rem;">
    @php
        $hasAnySchedule = false;
        foreach($hariOrder as $hari) {
            if(isset($schedules[$hari]) && $schedules[$hari]->isNotEmpty()) {
                $hasAnySchedule = true;
            }
        }
    @endphp

    @if(!$hasAnySchedule)
        <div class="card" style="text-align: center; padding: 4rem 1.5rem; border: 2px dashed var(--border-color); border-radius: var(--border-radius-lg); background: var(--bg-card);">
            <div style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <i data-lucide="calendar" style="width: 32px; height: 32px;"></i>
            </div>
            <h4 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-main);">{{ __('Belum Ada Jadwal Kuliah') }}</h4>
            <p style="color: var(--text-muted); font-size: 0.95rem; max-width: 400px; margin: 0 auto;">
                {{ __('Tidak ada jadwal kuliah yang terdaftar di kelas-kelas Anda saat ini.') }}
            </p>
        </div>
    @else
        <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem;">
            @foreach($hariOrder as $hari)
                @if(isset($schedules[$hari]) && $schedules[$hari]->isNotEmpty())
                    <div class="card" style="padding: 1.5rem;">
                        <h3 style="font-size: 1.2rem; font-weight: 800; border-bottom: 2px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1rem; color: var(--primary); display: flex; align-items: center; gap: 0.5rem;">
                            <i data-lucide="calendar-days" style="width: 20px; height: 20px;"></i>
                            <span>{{ __($hari) }}</span>
                        </h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem;">
                            @foreach($schedules[$hari] as $sched)
                                <div style="border: 1px solid var(--border-color); border-left: 5px solid {{ $sched->color ?: ($sched->classModel->color ?: '#10B981') }}; padding: 1.25rem; border-radius: var(--border-radius-md); background-color: var(--bg-app); display: flex; flex-direction: column; justify-content: space-between;">
                                    <div>
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem; gap: 0.5rem;">
                                            <span style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background-color: var(--primary-soft); color: var(--primary); padding: 0.2rem 0.5rem; border-radius: var(--border-radius-sm);">
                                                {{ $sched->classModel->nama_kelas }}
                                            </span>
                                            <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-main);">
                                                {{ $sched->formatted_start_time }} - {{ $sched->formatted_end_time }}
                                            </span>
                                        </div>
                                        <h4 style="font-weight: 700; font-size: 1.05rem; margin: 0.5rem 0; color: var(--text-main);">
                                            {{ $sched->mata_kuliah }}
                                        </h4>
                                    </div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 1rem; border-top: 1px dashed var(--border-color); padding-top: 0.75rem; display: flex; flex-direction: column; gap: 0.25rem;">
                                        <div style="display: flex; align-items: center; gap: 0.35rem;">
                                            <i data-lucide="user" style="width: 14px; height: 14px;"></i>
                                            <span>{{ __('Dosen') }}: {{ $sched->dosen ?: $sched->classModel->admin->name }}</span>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 0.35rem;">
                                            <i data-lucide="map-pin" style="width: 14px; height: 14px;"></i>
                                            <span>{{ __('Ruangan') }}: {{ $sched->ruangan ?: '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</div>
@endsection
