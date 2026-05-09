<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallBillFonts extends Command
{
    protected $signature = 'bill:install-fonts {--force : Overwrite existing font files}';

    protected $description = 'Copy DejaVu TTF fonts into resources/fonts for bill PNG rendering (TrueType).';

    public function handle(): int
    {
        $from = base_path('vendor/dompdf/dompdf/lib/fonts');
        $to = resource_path('fonts');
        $files = ['DejaVuSans.ttf', 'DejaVuSans-Bold.ttf'];

        if (! File::isDirectory($from)) {
            $this->error('Dompdf vendor fonts directory not found. Run composer install.');

            return self::FAILURE;
        }

        File::ensureDirectoryExists($to);

        foreach ($files as $file) {
            $src = $from.'/'.$file;
            $dest = $to.'/'.$file;
            if (! File::exists($src)) {
                $this->error("Missing source font: {$src}");

                return self::FAILURE;
            }
            if (File::exists($dest) && ! $this->option('force')) {
                $this->line("Skipped (exists): {$dest}");

                continue;
            }
            File::copy($src, $dest);
            $this->info("Installed: {$dest}");
        }

        $this->info('Done. Ensure PHP GD is built with FreeType for the designed bill template.');

        return self::SUCCESS;
    }
}
