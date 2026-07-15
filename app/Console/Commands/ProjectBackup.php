<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProjectBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:backup {--include-public : Include storage/app/public files in the backup} {--disk=local : Storage disk to save the backup file to (local or s3)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a lightweight JSON backup of key tables and optionally include public storage files.';

    public function handle()
    {
        $this->info('Starting backup...');

        $tables = [
            'users', 'classes', 'class_user', 'tasks', 'submissions', 'schedules',
            'discussions', 'discussion_replies', 'notifications', 'reports', 'activity_logs',
        ];

        $tmpDir = storage_path('app/backups/tmp_'.time());
        if (! is_dir($tmpDir) && ! mkdir($tmpDir, 0755, true) && ! is_dir($tmpDir)) {
            $this->error('Failed to create temp directory: '.$tmpDir);

            return 1;
        }

        try {
            foreach ($tables as $table) {
                $this->line('Exporting table: '.$table);
                $rows = DB::table($table)->get();
                $json = $rows->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                file_put_contents($tmpDir.DIRECTORY_SEPARATOR.$table.'.json', $json);
            }

            // Optionally include public storage files
            $includePublic = $this->option('include-public');

            $zipName = 'backup_'.date('Ymd_His').'.zip';
            $zipPath = storage_path('app/backups/'.$zipName);

            $zip = new \ZipArchive;
            if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
                $this->error('Unable to create zip file at: '.$zipPath);

                return 1;
            }

            // Add JSON files
            $files = glob($tmpDir.DIRECTORY_SEPARATOR.'*.json');
            foreach ($files as $file) {
                $zip->addFile($file, 'data/'.basename($file));
            }

            if ($includePublic) {
                $this->line('Adding public storage files...');
                $publicPath = storage_path('app/public');
                if (is_dir($publicPath)) {
                    $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($publicPath));
                    foreach ($iterator as $file) {
                        if ($file->isDir()) {
                            continue;
                        }
                        $localName = 'public/'.ltrim(str_replace($publicPath, '', $file->getPathname()), DIRECTORY_SEPARATOR);
                        $zip->addFile($file->getPathname(), $localName);
                    }
                }
            }

            $zip->close();

            // Optionally upload to chosen disk
            $disk = $this->option('disk') ?: 'local';
            if ($disk !== 'local') {
                $this->line('Uploading backup to disk: '.$disk);
                $backupPath = 'backups/'.$zipName;
                Storage::disk($disk)->putFileAs('backups', new File($zipPath), $zipName);
                $this->info('Backup uploaded to disk '.$disk.': '.$backupPath);
            } else {
                $this->info('Backup created: storage/app/backups/'.$zipName);
            }

            // Cleanup tmp json files
            foreach ($files as $file) {
                @unlink($file);
            }
            @rmdir($tmpDir);

            return 0;
        } catch (\Exception $e) {
            $this->error('Backup failed: '.$e->getMessage());

            return 1;
        }
    }
}
