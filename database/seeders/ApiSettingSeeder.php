<?php

namespace Database\Seeders;

use App\Models\ApiSetting;
use Illuminate\Database\Seeder;

class ApiSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ── Mapbox ──────────────────────────────────────────────
            [
                'key'   => 'mapbox_token',
                'group' => 'mapbox',
                'label' => 'Mapbox Public Token',
                'type'  => 'secret',
                'value' => null,
            ],
            [
                'key'   => 'mapbox_style_satellite',
                'group' => 'mapbox',
                'label' => 'Mapbox Satellite Style ID',
                'type'  => 'text',
                'value' => 'mapbox://styles/mapbox/satellite-streets-v12',
            ],
            [
                'key'   => 'mapbox_geocoding_limit',
                'group' => 'mapbox',
                'label' => 'Geocoding Results Limit',
                'type'  => 'number',
                'value' => '1',
            ],
        ];

        foreach ($settings as $setting) {
            ApiSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting,
            );
        }
    }
}