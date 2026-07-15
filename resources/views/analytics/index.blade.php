@extends('layouts.app')

@section('title', __('Analisis Beban Belajar'))

@section('content')
<div style="margin-bottom: 2rem;">
    <h1 style="font-weight: 800; font-size: 2.2rem; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
        {{ __('Analisis Beban Belajar & Produktivitas') }} 📈
    </h1>
    <p style="color: var(--text-muted);">
        {{ __('Pantau beban tugas kuliah mingguan Anda dan ukur statistik pengerjaan secara real-time.') }}
    </p>
</div>

@if($classes->isEmpty())
    <div class="card" style="text-align: center; padding: 5rem 1rem;">
        <i data-lucide="bar-chart-3" style="width: 64px; height: 64px; color: var(--text-muted); margin-bottom: 1.5rem; opacity: 0.6;"></i>
        <h3 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 0.5rem;">{{ __('Data Grafik Kosong') }}</h3>
        <p style="color: var(--text-muted); font-size: 0.95rem; max-width: 380px; margin: 0 auto;">
            {{ __('Anda harus memiliki kelas aktif yang diikuti untuk melihat visualisasi beban belajar di sini.') }}
        </p>
    </div>
@else
    <!-- Analytics Dashboard Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 2rem;">
        
        <!-- Load analysis bar chart -->
        <div class="card">
            <h3 class="card-title">
                <span>{{ __('Beban Belajar per Kelas') }}</span>
                <i data-lucide="bar-chart-3" style="width: 18px; height: 18px; color: var(--text-muted);"></i>
            </h3>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 1rem; margin-top: -0.75rem;">
                {{ __('Visualisasi jumlah tugas/kuis/ujian yang aktif saat ini di masing-masing kelas.') }}
            </p>
            <div style="height: 280px; position: relative;">
                <canvas id="loadChart"></canvas>
            </div>
        </div>

        <!-- Productivity pie chart -->
        <div class="card">
            <h3 class="card-title">
                <span>{{ __('Produktivitas Tugas') }}</span>
                <i data-lucide="pie-chart" style="width: 18px; height: 18px; color: var(--text-muted);"></i>
            </h3>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 1rem; margin-top: -0.75rem;">
                @if($user->role === 'mahasiswa')
                    {{ __('Rincian status pengumpulan seluruh tugas Anda dari semua kelas.') }}
                @else
                    {{ __('Rata-rata pengumpulan tugas oleh mahasiswa di seluruh kelas Anda.') }}
                @endif
            </p>
            <div style="height: 280px; display: flex; align-items: center; justify-content: center; position: relative;">
                <canvas id="productivityChart"></canvas>
            </div>
        </div>

        <!-- Weekly activity timeline -->
        <div class="card" style="grid-column: span 2;">
            <h3 class="card-title">
                <span>{{ __('Tenggat Waktu Minggu Ini') }}</span>
                <i data-lucide="calendar-days" style="width: 18px; height: 18px; color: var(--text-muted);"></i>
            </h3>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 1rem; margin-top: -0.75rem;">
                {{ __('Distribusi sebaran deadline tugas dari hari Senin sampai Minggu untuk minggu ini.') }}
            </p>
            <div style="height: 240px; position: relative;">
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>

    </div>
@endif

@endsection

@section('scripts')
@if(!$classes->isEmpty())
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // --- 1. Load Chart (Bar) ---
        const loadCtx = document.getElementById('loadChart').getContext('2d');
        new Chart(loadCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($loadLabels) !!},
                datasets: [{
                    label: '{{ __('Jumlah Tugas') }}',
                    data: {!! json_encode($loadData) !!},
                    backgroundColor: 'rgba(16, 185, 129, 0.75)', // Emerald
                    borderColor: 'rgb(16, 185, 129)',
                    borderWidth: 2,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });

        // --- 2. Productivity Chart (Doughnut) ---
        const prodCtx = document.getElementById('productivityChart').getContext('2d');
        new Chart(prodCtx, {
            type: 'doughnut',
            data: {
                labels: ['{{ __('Sudah Dinilai') }}', '{{ __('Menunggu Penilaian') }}', '{{ __('Terlambat Dikumpul') }}', '{{ __('Belum Mengerjakan') }}'],
                datasets: [{
                    data: [
                        {{ $productivityData['graded'] }},
                        {{ $productivityData['submitted'] }},
                        {{ $productivityData['late'] }},
                        {{ $productivityData['pending'] }}
                    ],
                    backgroundColor: [
                        '#10b981', // Graded (Green)
                        '#3b82f6', // Submitted (Blue)
                        '#ef4444', // Late (Red)
                        '#64748b'  // Pending (Slate)
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { family: 'Outfit' } }
                    }
                },
                cutout: '65%'
            }
        });

        // --- 3. Weekly Timeline Chart (Line) ---
        const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
        new Chart(weeklyCtx, {
            type: 'line',
            data: {
                labels: ['{{ __('Senin') }}', '{{ __('Selasa') }}', '{{ __('Rabu') }}', '{{ __('Kamis') }}', '{{ __('Jumat') }}', '{{ __('Sabtu') }}', '{{ __('Minggu') }}'],
                datasets: [{
                    label: '{{ __('Tugas yang Jatuh Tempo') }}',
                    data: [
                        {{ $weeklyActivity['Senin'] }},
                        {{ $weeklyActivity['Selasa'] }},
                        {{ $weeklyActivity['Rabu'] }},
                        {{ $weeklyActivity['Kamis'] }},
                        {{ $weeklyActivity['Jumat'] }},
                        {{ $weeklyActivity['Sabtu'] }},
                        {{ $weeklyActivity['Minggu'] }}
                    ],
                    fill: true,
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    borderColor: 'rgb(79, 70, 229)', // Indigo
                    tension: 0.3,
                    borderWidth: 3,
                    pointBackgroundColor: 'rgb(79, 70, 229)',
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    });
</script>
@endif
@endsection
