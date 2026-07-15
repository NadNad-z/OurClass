<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ProjectRestore extends Command
{
    protected $signature = 'project:restore {backup} {--disk=local : Storage disk where the backup file is located}';

    protected $description = 'Restore a backup ZIP from local storage or S3 to the local storage/backups folder.';

    public function handle()
    {
        $backupFile = $this->argument('backup');
        $disk = $this->option('disk') ?: 'local';

        if ($disk !== 'local' && ! Storage::disk($disk)->exists('backups/'.$backupFile)) {
            $this->error('Backup not found on disk '.$disk.': backups/'.$backupFile);

            return 1;
        }

        $localPath = storage_path('app/backups/'.$backupFile);
        if ($disk !== 'local') {
            $this->info('Downloading backup from disk '.$disk);
            $content = Storage::disk($disk)->get('backups/'.$backupFile);
            Storage::disk('local')->put('backups/'.$backupFile, $content);
        }

        if (! file_exists($localPath)) {
            $this->error('Backup file missing locally: '.$localPath);

            return 1;
        }

        $zip = new ZipArchive;
        if ($zip->open($localPath) !== true) {
            $this->error('Unable to open backup file: '.$localPath);

            return 1;
        }

        $extractPath = storage_path('app/backups/restore_'.time());
        if (! is_dir($extractPath) && ! mkdir($extractPath, 0755, true) && ! is_dir($extractPath)) {
            $this->error('Unable to create extract folder: '.$extractPath);

            return 1;
        }

        $zip->extractTo($extractPath);
        $zip->close();

        $this->info('Backup extracted to: '.$extractPath);
        $this->info('You can now inspect the extracted JSON files and optionally restore database content manually.');

        return 0;
    }
}
