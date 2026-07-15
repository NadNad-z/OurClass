<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Storage;

trait FileUploadSecurity
{
    /**
     * Optionally scan uploaded file using local clamscan binary when enabled.
     * If infected, the method should delete the file and return false.
     */
    protected function scanUploadedFile(string $storageDisk, string $filePath): bool
    {
        // Only attempt scan if CLAMAV_SCAN env enabled
        if (! env('CLAMAV_SCAN', false)) {
            return true;
        }

        try {
            $fullPath = storage_path('app/'.($storageDisk === 'public' ? 'public/' : '').$filePath);
            if (! file_exists($fullPath)) {
                return true;
            }

            // Run clamscan if available
            $cmd = 'clamscan --no-summary '.escapeshellarg($fullPath).' 2>&1';
            $output = null;
            $returnVar = null;
            @exec($cmd, $output, $returnVar);

            // clamscan returns 0 = no virus, 1 = virus found, 2 = error
            if ($returnVar === 1) {
                // delete infected file
                try {
                    Storage::disk($storageDisk)->delete($filePath);
                } catch (\Exception $e) {
                    // ignore deletion failure
                }

                return false;
            }

            return $returnVar === 0;
        } catch (\Throwable $e) {
            // On any scanning error, allow file (fail-open) but log to laravel log
            logger()->warning('File scan failed: '.$e->getMessage());

            return true;
        }
    }
}
