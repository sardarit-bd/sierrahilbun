<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportSoilData extends Command
{
    protected $signature = 'soil:import {path : The absolute path to the CSV file}';
    protected $description = 'Import TurfTec Soil Data (Matches Google Sheet Schema)';

    public function handle()
    {
        $path = $this->argument('path');

        if (!File::exists($path)) {
            $this->error("File not found at: $path");
            return 1;
        }

        $this->info("Starting full import...");
        
        $file = fopen($path, 'r');
        $header = fgetcsv($file); // Skip header row

        $batch = [];
        $batchSize = 500;
        
        // Approximate row count
        $this->output->progressStart(42500); 
        DB::beginTransaction();

        try {
            while (($row = fgetcsv($file)) !== false) {
                
                // --- 1. Map Columns Exactly to Sheet Order ---
                // Indexes 0-33 based on your CSV structure
                
                $zip_code = str_pad($row[0], 5, '0', STR_PAD_LEFT);
                $type = $row[1] ?? null;
                $decommissioned = (int)($row[2] ?? 0);
                $primaryCity = $row[3] ?? 'Unknown';
                $acceptableCities = $row[4] ?? null;
                $unacceptableCities = $row[5] ?? null;
                $state = $row[6] ?? '';
                $county = $row[7] ?? null;
                $timezone = $row[8] ?? null;
                $areaCodes = $row[9] ?? null;
                $worldRegion = $row[10] ?? null;
                $country = $row[11] ?? 'US';
                
                $lat = (float) ($row[12] ?? 0);
                $lon = (float) ($row[13] ?? 0);
                $climateZone = $row[14] ?? null;
                
                $annualRain = (float) ($row[15] ?? 0);
                $julyHigh = (int) ($row[16] ?? 0);
                $janLow = (int) ($row[17] ?? 0);
                $frostFreeDays = (int) ($row[18] ?? 0);
                
                $avgPh = (float) ($row[19] ?? 0);
                // Handle empty string for soil_ph_max which is nullable float
                $soilPhMax = isset($row[20]) && $row[20] !== '' ? (float)$row[20] : null;
                $avgPhClass = $row[21] ?? null;
                $soilPhClass = $row[22] ?? null;
                
                $cecMeq = (float) ($row[23] ?? 0);
                $cecClass = $row[24] ?? null;
                
                $organicMatterClass = $row[25] ?? null;
                $calciumSaturationClass = $row[26] ?? null;
                $soilTextureClass = $row[27] ?? null;
                $drainageClass = $row[28] ?? null;
                
                $compactionRisk = $row[29] ?? null;
                $nLeachingRisk = $row[30] ?? null;
                $diseasePressure = $row[31] ?? null;
                $droughtStressRisk = $row[32] ?? null;
                $organicMatterPct = (float) ($row[33] ?? 0);

                // --- 2. Generate Simulated Data for Charts ---
                $monthlyTemps = $this->simulateTemperatureCurve($janLow, $julyHigh);
                $monthlyRain = $this->simulateRainfall($annualRain);
                $growthData = $this->simulateGrowthPotential($monthlyTemps, $climateZone);

                $batch[] = [
                    'zip_code' => $zip_code,
                    'type' => $type,
                    'decommissioned' => $decommissioned,
                    'primary_city' => $primaryCity,
                    'acceptable_cities' => $acceptableCities,
                    'unacceptable_cities' => $unacceptableCities,
                    'state' => $state,
                    'county' => $county,
                    'timezone' => $timezone,
                    'area_codes' => $areaCodes,
                    'world_region' => $worldRegion,
                    'country' => $country,
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'climate_zone' => $climateZone,
                    'avg_annual_rain_in' => $annualRain,
                    'avg_july_high_F' => $julyHigh,
                    'avg_jan_low_F' => $janLow,
                    'frost_free_days' => $frostFreeDays,
                    'avg_ph' => $avgPh,
                    'soil_ph_max' => $soilPhMax,
                    'avg_ph_class' => $avgPhClass,
                    'soil_ph_class' => $soilPhClass,
                    'cec_meq_per_100g' => $cecMeq,
                    'cec_class' => $cecClass,
                    'organic_matter_class' => $organicMatterClass,
                    'calcium_saturation_class' => $calciumSaturationClass,
                    'soil_texture_class' => $soilTextureClass,
                    'drainage_class' => $drainageClass,
                    'compaction_risk' => $compactionRisk,
                    'n_leaching_risk' => $nLeachingRisk,
                    'disease_pressure' => $diseasePressure,
                    'drought_stress_risk' => $droughtStressRisk,
                    'organic_matter_pct' => $organicMatterPct,
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
            $this->info("✅ Complete! All data imported.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error processing row. Last zip_code: {$zip_code}. Error: " . $e->getMessage());
            return 1;
        }

        fclose($file);
        return 0;
    }

    private function insertBatch(array $batch)
    {
        DB::table('geo_soil_references')->upsert(
            $batch, 
            ['zip_code'], // Unique Key
            [
                // Update ALL columns if record exists
                'type', 'decommissioned', 'primary_city', 'acceptable_cities', 'unacceptable_cities',
                'state', 'county', 'timezone', 'area_codes', 'world_region', 'country',
                'latitude', 'longitude', 'climate_zone', 'avg_annual_rain_in', 
                'avg_july_high_F', 'avg_jan_low_F', 'frost_free_days', 
                'avg_ph', 'soil_ph_max', 'avg_ph_class', 'soil_ph_class',
                'cec_meq_per_100g', 'cec_class', 'organic_matter_class', 'calcium_saturation_class',
                'soil_texture_class', 'drainage_class', 'compaction_risk', 'n_leaching_risk',
                'disease_pressure', 'drought_stress_risk', 'organic_matter_pct',
                'monthly_temp_data', 'monthly_rainfall_data', 'growth_potential_data', 'updated_at'
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

    private function simulateGrowthPotential($temps, $climateZone) {
        $gp = [];
        $optimum = (stripos($climateZone, 'warm') !== false) ? 86 : 68;

        foreach ($temps as $t) {
            $val = exp(-0.5 * pow(($t - $optimum) / 10, 2)) * 100;
            $gp[] = (int) $val;
        }
        return $gp;
    }
}