<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClearDompdfFonts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dompdf:clear-fonts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Borra la caché de fuentes de Dompdf y la regenera';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cachePath = storage_path('app/fonts/cache');

        if (File::exists($cachePath)) {
            File::deleteDirectory($cachePath);
            $this->info("✅ Caché de fuentes borrada: $cachePath");
        } else {
            $this->warn("⚠️ No existe carpeta de caché: $cachePath");
        }

        // Regenerar cache creando carpeta vacía
        File::makeDirectory($cachePath, 0755, true, true);

        $this->info("🚀 Caché de fuentes regenerada con éxito.");
        return Command::SUCCESS;
    }
}
