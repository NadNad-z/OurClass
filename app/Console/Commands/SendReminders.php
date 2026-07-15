<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Models\Schedule;
use App\Models\Notification;
use Carbon\Carbon;

class SendReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim pengingat otomatis untuk tugas dan jadwal kelas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting automated reminders check...');
        $this->checkTaskDeadlines();
        $this->checkUpcomingSchedules();
        $this->info('Reminders check completed.');
    }

    private function checkTaskDeadlines()
    {
        // Find tasks due within the next 24 hours
        $now = Carbon::now();
        $tomorrow = Carbon::now()->addHours(24);

        $tasks = Task::where('status', 'published')
                     ->whereBetween('deadline', [$now, $tomorrow])
                     ->with('classModel.students')
                     ->get();

        foreach ($tasks as $task) {
            if (!$task->classModel) continue;

            foreach ($task->classModel->students as $student) {
                // Check if student already submitted
                $hasSubmitted = $task->submissions()->where('user_id', $student->id)->exists();
                if ($hasSubmitted) continue;

                // Check if we already sent a reminder today for this task
                $alreadySent = Notification::where('user_id', $student->id)
                    ->where('tipe', 'deadline')
                    ->where('link', route('tasks.show', $task->id))
                    ->whereDate('created_at', $now->toDateString())
                    ->exists();

                if (!$alreadySent) {
                    Notification::create([
                        'user_id' => $student->id,
                        'class_id' => $task->class_id,
                        'judul' => 'Pengingat Deadline Tugas',
                        'pesan' => 'Tugas "' . $task->judul . '" dari kelas ' . $task->classModel->nama_kelas . ' akan jatuh tempo dalam waktu kurang dari 24 jam.',
                        'tipe' => 'deadline',
                        'link' => route('tasks.show', $task->id),
                        'is_read' => false
                    ]);
                }
            }
        }
    }

    private function checkUpcomingSchedules()
    {
        $now = Carbon::now();
        $inOneHour = Carbon::now()->addHour();
        
        $hariMap = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];
        $todayName = $hariMap[$now->format('l')];

        $nowTime = $now->format('H:i:s');
        $inOneHourTime = $inOneHour->format('H:i:s');
        
        $schedulesQuery = Schedule::where('hari', $todayName);
        
        if ($inOneHour->isSameDay($now)) {
            $schedulesQuery->whereBetween('waktu_mulai', [$nowTime, $inOneHourTime]);
        } else {
            $schedulesQuery->where(function ($q) use ($nowTime, $inOneHourTime) {
                $q->where('waktu_mulai', '>=', $nowTime)
                  ->orWhere('waktu_mulai', '<=', $inOneHourTime);
            });
        }
        
        $schedules = $schedulesQuery->with('classModel.students')->get();

        foreach ($schedules as $schedule) {
            if (!$schedule->classModel) continue;

            foreach ($schedule->classModel->students as $student) {
                // Check if reminder sent today
                $alreadySent = Notification::where('user_id', $student->id)
                    ->where('tipe', 'jadwal')
                    ->where('link', route('classes.show', $schedule->class_id))
                    ->whereDate('created_at', $now->toDateString())
                    ->exists();

                if (!$alreadySent) {
                    Notification::create([
                        'user_id' => $student->id,
                        'class_id' => $schedule->class_id,
                        'judul' => 'Pengingat Kelas',
                        'pesan' => 'Kelas ' . $schedule->mata_kuliah . ' akan dimulai pada pukul ' . substr($schedule->waktu_mulai, 0, 5) . ' WIB di ruangan ' . ($schedule->ruangan ?: 'TBA') . '.',
                        'tipe' => 'jadwal',
                        'link' => route('classes.show', $schedule->class_id),
                        'is_read' => false
                    ]);
                }
            }
        }
    }
}
