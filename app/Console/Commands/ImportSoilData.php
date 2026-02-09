<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportSoilData extends Command
{
    // The command you will run in terminal: "php artisan soil:import"
    protected $signature = 'soil:import {path : The absolute path to the CSV file}';
    protected $description = 'Import TurfTec Soil Analysis CSV and transform to JSON';

    public function handle()
    {
        $path = $this->argument('path');

        if (!File::exists($path)) {
            $this->error("File not found at: $path");
            return 1;
        }

        $this->info("Starting import... this may take a minute.");
        
        $file = fopen($path, 'r');
        $header = fgetcsv($file); 

        $batch = [];
        $batchSize = 500;
        
        $this->output->progressStart(40000);

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($file)) !== false) {
                
                $zipCode = str_pad($row[0], 5, '0', STR_PAD_LEFT);
                
                // Convert 12 separate columns into one JSON Array for the frontend charts
                // Indices 5-16 assumed to be Jan-Dec Temps
                $monthlyTemps = array_map('floatval', array_slice($row, 5, 12));
                
                // Indices 17-28 assumed to be Jan-Dec Rainfall
                $monthlyRain = array_map('floatval', array_slice($row, 17, 12));
                
                // Indices 29-40 assumed to be Growth Potential
                $growthData = array_map('intval', array_slice($row, 29, 12));

                $batch[] = [
                    'zip_code' => $zipCode,
                    'state_code' => $row[1],
                    'city' => $row[2],
                    'dominant_soil_texture' => $row[3],
                    'ph_mean' => (float) $row[4],
                    'organic_matter_pct' => (float) $row[41],
                    'monthly_temp_data' => json_encode($monthlyTemps),
                    'monthly_rainfall_data' => json_encode($monthlyRain),
                    'growth_potential_data' => json_encode($growthData),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($batch) >= $batchSize) {
                    DB::table('geo_soil_references')->upsert(
                        $batch, 
                        ['zip_code'],
                        ['monthly_temp_data', 'growth_potential_data', 'updated_at']
                    );
                    $batch = [];
                    $this->output->progressAdvance($batchSize);
                }
            }

            if (count($batch) > 0) {
                DB::table('geo_soil_references')->upsert(
                    $batch, ['zip_code'], ['monthly_temp_data', 'growth_potential_data', 'updated_at']
                );
            }

            DB::commit();
            $this->output->progressFinish();
            $this->info("Successfully imported all soil data!");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error: " . $e->getMessage());
            return 1;
        }

        fclose($file);
        return 0;
    }
}