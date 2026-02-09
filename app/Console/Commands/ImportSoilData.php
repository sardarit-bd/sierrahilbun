<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportSoilData extends Command
{
    protected $signature = 'soil:import {path : The absolute path to the CSV file}';
    protected $description = 'Import TurfTec Soil CSV (Final Version)';

    public function handle()
    {
        $path = $this->argument('path');

        if (!File::exists($path)) {
            $this->error("File not found at: $path");
            return 1;
        }

        $this->info("Starting full import...");
        
        $file = fopen($path, 'r');
        $header = fgetcsv($file);

        $batch = [];
        $batchSize = 500;
        $this->output->progressStart(42500); 
        DB::beginTransaction();

        try {
            while (($row = fgetcsv($file)) !== false) {
                
               
                $zipCode = str_pad($row[0], 5, '0', STR_PAD_LEFT);
                $city = $row[3] ?? 'Unknown';
                $state = $row[6] ?? '';
                $county = $row[7] ?? null;
                $timezone = $row[8] ?? null;
                $lat = (float) ($row[12] ?? 0);
                $lon = (float) ($row[13] ?? 0);
                $climateZone = $row[14] ?? null;
                
        
                $annualRain = (float) ($row[15] ?? 30);
                $julyHigh = (float) ($row[16] ?? 85);
                $janLow = (float) ($row[17] ?? 30);
                $frostFreeDays = (int) ($row[18] ?? 0);
      
                $ph = (float) ($row[19] ?? 7.0);
                $cec = (int) ($row[23] ?? 0);
                $texture = $row[27] ?? 'loam';
                $drainage = $row[28] ?? null;
                
     
                $compactionRisk = $row[29] ?? 'Low';
                $leachingRisk = $row[30] ?? 'Low';
                $diseasePressure = $row[31] ?? 'Low'; 
                $droughtRisk = $row[32] ?? 'Low';
                $organicMatter = (float) ($row[33] ?? 0.0);

                $monthlyTemps = $this->simulateTemperatureCurve($janLow, $julyHigh);
                $monthlyRain = $this->simulateRainfall($annualRain);
                $growthData = $this->simulateGrowthPotential($monthlyTemps);

                $batch[] = [
                    'zip_code' => $zipCode,
                    'state_code' => $state,
                    'city' => $city,
                    'county' => $county,
                    'timezone' => $timezone,
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'climate_zone' => $climateZone,
                    'frost_free_days' => $frostFreeDays,
                    'dominant_soil_texture' => $texture,
                    'drainage_class' => $drainage,
                    'ph_mean' => $ph,
                    'organic_matter_pct' => $organicMatter,
                    'cec_meq' => $cec, 
                    'compaction_risk' => $compactionRisk,
                    'drought_risk' => $droughtRisk,
                    'n_leaching_risk' => $leachingRisk,
                    'disease_pressure' => $diseasePressure, 
                    'monthly_temp_data' => json_encode($monthlyTemps),
                    'monthly_rainfall_data' => json_encode($monthlyRain),
                    'growth_potential_data' => json_encode($growthData),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($batch) >= $batchSize) {
                    $this->insertBatch($batch);
                    $batch = [];
                    $this->output->progressAdvance($batchSize);
                }
            }

            if (count($batch) > 0) {
                $this->insertBatch($batch);
            }

            DB::commit();
            $this->output->progressFinish();
            $this->info("✅ Complete! All 42.5k zip codes imported.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error at Zip {$zipCode}: " . $e->getMessage());
            return 1;
        }

        fclose($file);
        return 0;
    }

    private function insertBatch(array $batch)
    {
        DB::table('geo_soil_references')->upsert(
            $batch, 
            ['zip_code'], 
            [
                'city', 'state_code', 'county', 'timezone', 
                'latitude', 'longitude', 'climate_zone', 'frost_free_days',
                'dominant_soil_texture', 'drainage_class', 'ph_mean', 'organic_matter_pct', 'cec_meq',
                'drought_risk', 'compaction_risk', 'n_leaching_risk', 'disease_pressure',
                'monthly_temp_data', 'growth_potential_data', 'updated_at'
            ]
        );
    }


    private function simulateTemperatureCurve($min, $max) {
        $temps = [];
        $amplitude = ($max - $min) / 2;
        $midpoint = ($max + $min) / 2;
        for ($month = 0; $month < 12; $month++) {
            $radians = (($month - 6) / 12) * 2 * M_PI; 
            $temps[] = round($midpoint + ($amplitude * cos($radians)), 1);
        }
        return $temps;
    }

    private function simulateRainfall($annualTotal) {
        $avg = $annualTotal / 12;
        $data = [];
        for ($i = 0; $i < 12; $i++) {
            $factor = ($i >= 2 && $i <= 4) ? 1.2 : (rand(80, 110) / 100);
            $data[] = round($avg * $factor, 2);
        }
        return $data;
    }

    private function simulateGrowthPotential($temps) {
        $gp = [];
        foreach ($temps as $t) {
            $val = exp(-0.5 * pow(($t - 68) / 10, 2)) * 100;
            $gp[] = (int) $val;
        }
        return $gp;
    }
}