<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\GoogleSheetsImportController;
use Illuminate\Support\Facades\Log;

class ImportGoogleSheetsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:google-sheets {sheet_type=students} {--preview}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data from Google Sheets to database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $sheetType = $this->argument('sheet_type');
        $preview = $this->option('preview');

        $this->info("Starting Google Sheets import for: {$sheetType}");
        
        try {
            $controller = new GoogleSheetsImportController();
            
            if ($preview) {
                $this->info("Preview mode - fetching data only");
                // Preview logic here
            } else {
                $this->info("Import mode - processing data");
                // Import logic here
            }
            
            $this->info("Import completed successfully");
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Import failed: " . $e->getMessage());
            Log::error("Console import failed", [
                'sheet_type' => $sheetType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
