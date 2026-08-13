<?php

namespace App\Console\Commands;

use App\Services\StorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MigrateStorageToR2Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:migrate-to-r2 {--disk= : Target storage disk (defaults to filesystems.default)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely migrate existing local uploaded images to S3/Cloudflare R2 storage without deleting local files.';

    /**
     * Execute the console command.
     */
    public function handle(StorageService $storageService): int
    {
        $targetDisk = $this->option('disk') ?: config('filesystems.default', 's3');
        $this->info("🚀 Starting storage migration to disk: [{$targetDisk}]...");

        $uploadDirectory = public_path('uploads');

        if (!File::isDirectory($uploadDirectory)) {
            $this->warn("No local uploads directory found at [{$uploadDirectory}]. Nothing to migrate.");
            return Command::SUCCESS;
        }

        $allFiles = File::allFiles($uploadDirectory);
        $totalFiles = count($allFiles);

        if ($totalFiles === 0) {
            $this->info("No local files found in uploads directory.");
            return Command::SUCCESS;
        }

        $this->info("Found {$totalFiles} local upload file(s) to process.");

        $migrated = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($allFiles as $file) {
            $relativePath = 'uploads/' . $file->getRelativePathname();
            $targetPath = str_replace('\\', '/', $relativePath);

            try {
                if (Storage::disk($targetDisk)->exists($targetPath)) {
                    $this->line("⏩ Skipping existing object: {$targetPath}");
                    $skipped++;
                    continue;
                }

                $content = File::get($file->getRealPath());
                $success = Storage::disk($targetDisk)->put($targetPath, $content, 'public');

                if ($success) {
                    $this->info("✅ Migrated: {$targetPath}");
                    $migrated++;
                } else {
                    $this->error("❌ Failed to migrate: {$targetPath}");
                    $failed++;
                }
            } catch (\Throwable $e) {
                $this->error("❌ Exception migrating {$targetPath}: " . $e->getMessage());
                $failed++;
            }
        }

        $this->newLine();
        $this->info("📊 Storage Migration Summary:");
        $this->table(
            ['Total Discovered', 'Successfully Migrated', 'Skipped (Already Existed)', 'Failed'],
            [[$totalFiles, $migrated, $skipped, $failed]]
        );

        return Command::SUCCESS;
    }
}
