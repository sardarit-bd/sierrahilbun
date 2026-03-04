<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds all lawn products with:
 *   - Correct tier_inclusion values (drives TierInclusionMap pricing)
 *   - Correct jug sizes per spec allowed container list
 *   - Correct prices per spec add-on retail pricing
 *   - Correct application_rate_oz_per_1k per spec modifier rates
 *
 * Allowed jug sizes per product (spec):
 *   KickStart+:  0.25 gal (32 oz), 5 gal (640 oz), 55 gal (7040 oz), 275 gal (35200 oz)
 *   TurfFuel N:  0.25 gal (32 oz), 1 gal (128 oz), 2.5 gal (320 oz), 5 gal (640 oz), 55 gal (7040 oz), 275 gal (35200 oz)
 *   Neutralyze:  0.25 gal (32 oz), 1 gal (128 oz), 2.5 gal (320 oz), 5 gal (640 oz), 55 gal (7040 oz), 275 gal (35200 oz)
 *   PatchPro+:   0.25 gal (32 oz), 1 gal (128 oz), 2.5 gal (320 oz), 5 gal (640 oz), 55 gal (7040 oz), 275 gal (35200 oz)
 *   Aerate:      0.125 gal (16 oz), 0.25 gal (32 oz), 1 gal (128 oz), 2.5 gal (320 oz), 5 gal (640 oz), 55 gal (7040 oz)
 *   HeatGuard:   0.25 gal (32 oz), 1 gal (128 oz), 2.5 gal (320 oz), 5 gal (640 oz), 55 gal (7040 oz), 275 gal (35200 oz)
 *
 * Add-on retail pricing (spec):
 *   PatchPro+:  $25.00 / quart  → $100.00/gal fallback
 *   Aerate:     $45.00 / pint   → $360.00/gal fallback
 *   HeatGuard:  $55.00 / quart  → $220.00/gal fallback
 *
 * Tier inclusion (drives TierInclusionMap — do not change without spec approval):
 *   bronze: kickstart, turf-fuel-n, neutralyze
 *   silver: patchpro
 *   gold:   aerate, heatguard
 *   addon:  everything else
 */
class ProductSeeder extends Seeder
{
    /**
     * Core products — always included, tier = bronze.
     * Jug sizes and prices are for display/packaging optimizer only.
     * Ratios are driven by CoreRatioCalculator, not app_rate.
     */
    private const CORE_PRODUCTS = [
        [
            'name'           => 'KickStart+',
            'slug'           => 'kickstart',
            'subtitle'       => 'Rapid Growth & Recovery',
            'description'    => 'Jump-starts turf growth and recovery by delivering readily available nutrients that support early root and shoot development.',
            'app_rate'       => 4.0, // base oz/1000 — display only
            'tier_inclusion' => 'bronze',
            'benefits'       => [
                'Promotes faster green-up and turf recovery',
                'Supports early root and shoot development',
                'Helps turf rebound from stress, wear, or seasonal transitions',
                'Ideal for spring start-up or post-stress recovery',
            ],
            // Spec: 0.25 gal, 5 gal, 55 gal, 275 gal ONLY
            'variants' => [
                ['size' => 'Quart',      'volume_oz' => 32,    'price' => 27.99],
                ['size' => '5 Gallon',   'volume_oz' => 640,   'price' => 299.99],
                ['size' => '55 Gallon',  'volume_oz' => 7040,  'price' => 2199.99],
                ['size' => '275 Gallon', 'volume_oz' => 35200, 'price' => 8999.99],
            ],
        ],
        [
            'name'           => 'TurfFuel N',
            'slug'           => 'turf-fuel-n',
            'subtitle'       => 'Controlled Release Nitrogen',
            'description'    => 'Provides a steady, controlled release of nitrogen to drive consistent green-up without excessive growth or burn.',
            'app_rate'       => 3.0, // base oz/1000 — display only
            'tier_inclusion' => 'bronze',
            'benefits'       => [
                'Provides steady nitrogen for consistent green color',
                'Reduces risk of burn compared to quick-release fertilizers',
                'Supports balanced growth without excessive top growth',
                'Ideal for maintaining healthy, uniform turf',
            ],
            // Spec: 0.25, 1, 2.5, 5, 55, 275 gal
            'variants' => [
                ['size' => 'Quart',      'volume_oz' => 32,    'price' => 27.99],
                ['size' => '1 Gallon',   'volume_oz' => 128,   'price' => 89.99],
                ['size' => '2.5 Gallon', 'volume_oz' => 320,   'price' => 189.99],
                ['size' => '5 Gallon',   'volume_oz' => 640,   'price' => 299.99],
                ['size' => '55 Gallon',  'volume_oz' => 7040,  'price' => 2199.99],
                ['size' => '275 Gallon', 'volume_oz' => 35200, 'price' => 8999.99],
            ],
        ],
        [
            'name'           => 'Neutralyze',
            'slug'           => 'neutralyze',
            'subtitle'       => 'Soil pH Balancer & Calcium',
            'description'    => 'Supplies highly available calcium to help balance soil pH and improve nutrient uptake for healthier, stronger turf.',
            'app_rate'       => 3.0, // base oz/1000 — display only
            'tier_inclusion' => 'bronze',
            'benefits'       => [
                'Supplies highly available calcium for healthier turf',
                'Helps balance soil pH and nutrient availability',
                'Supports stronger roots and improved soil structure',
                'Improves overall turf performance and resilience',
            ],
            // Spec: 0.25, 1, 2.5, 5, 55, 275 gal
            'variants' => [
                ['size' => 'Quart',      'volume_oz' => 32,    'price' => 24.99],
                ['size' => '1 Gallon',   'volume_oz' => 128,   'price' => 64.99],
                ['size' => '2.5 Gallon', 'volume_oz' => 320,   'price' => 119.99],
                ['size' => '5 Gallon',   'volume_oz' => 640,   'price' => 199.99],
                ['size' => '55 Gallon',  'volume_oz' => 7040,  'price' => 1599.99],
                ['size' => '275 Gallon', 'volume_oz' => 35200, 'price' => 6999.99],
            ],
        ],
    ];

    /**
     * Modifier products — recommended by engine based on soil conditions.
     * Prices follow spec add-on retail pricing exactly.
     * Per-gallon fallback rates are applied to all sizes above base unit.
     */
    private const MODIFIER_PRODUCTS = [
        [
            'name'           => 'PatchPro+',
            'slug'           => 'patchpro',
            'subtitle'       => 'Lawn Repair & Regrowth Formula',
            'description'    => 'Repairs thin, stressed, or damaged turf by improving soil conditions that promote faster, more uniform regrowth.',
            'app_rate'       => 2.0,   // spec max oz/1000
            'tier_inclusion' => 'silver',
            'benefits'       => [
                'Helps repair thin, bare, or damaged turf areas',
                'Improves soil conditions for faster, more uniform regrowth',
                'Strengthens roots to support new grass establishment',
                'Ideal for high-traffic or stressed lawn areas',
            ],
            // Spec: 0.25, 1, 2.5, 5, 55, 275 gal
            // Base: $25/quart → $100/gal fallback
            'variants' => [
                ['size' => 'Quart',      'volume_oz' => 32,    'price' => 25.00],   // spec base price
                ['size' => '1 Gallon',   'volume_oz' => 128,   'price' => 100.00],  // $100/gal fallback
                ['size' => '2.5 Gallon', 'volume_oz' => 320,   'price' => 250.00],  // $100/gal × 2.5
                ['size' => '5 Gallon',   'volume_oz' => 640,   'price' => 500.00],  // $100/gal × 5
                ['size' => '55 Gallon',  'volume_oz' => 7040,  'price' => 5500.00], // $100/gal × 55
                ['size' => '275 Gallon', 'volume_oz' => 35200, 'price' => 27500.00],// $100/gal × 275
            ],
        ],
        [
            'name'           => 'Aerate',
            'slug'           => 'aerate',
            'subtitle'       => 'Liquid Soil Aerator',
            'description'    => 'Improves soil structure by reducing compaction, allowing water, nutrients, and oxygen to move more freely into the root zone.',
            'app_rate'       => 0.5,   // spec oz/1000
            'tier_inclusion' => 'gold',
            'benefits'       => [
                'Reduces soil compaction without mechanical aeration',
                'Improves water, air, and nutrient movement into the soil',
                'Enhances root development and soil structure',
                'Safe for use any time during the growing season',
            ],
            // Spec: 0.125, 0.25, 1, 2.5, 5, 55 gal (NO 275 gal)
            // Base: $45/pint → $360/gal fallback
            'variants' => [
                ['size' => 'Pint',       'volume_oz' => 16,   'price' => 45.00],   // spec base price
                ['size' => 'Quart',      'volume_oz' => 32,   'price' => 90.00],   // $360/gal × 0.25
                ['size' => '1 Gallon',   'volume_oz' => 128,  'price' => 360.00],  // $360/gal fallback
                ['size' => '2.5 Gallon', 'volume_oz' => 320,  'price' => 900.00],  // $360/gal × 2.5
                ['size' => '5 Gallon',   'volume_oz' => 640,  'price' => 1800.00], // $360/gal × 5
                ['size' => '55 Gallon',  'volume_oz' => 7040, 'price' => 19800.00],// $360/gal × 55
            ],
        ],
        [
            'name'           => 'HeatGuard',
            'slug'           => 'heatguard',
            'subtitle'       => 'Heat & Drought Stress Protection',
            'description'    => 'Helps turf withstand heat and drought stress by supporting root health and moisture efficiency during extreme summer conditions.',
            'app_rate'       => 1.0,   // spec oz/1000
            'tier_inclusion' => 'gold',
            'benefits'       => [
                'Helps turf stay green and resilient during extreme heat and drought',
                'Supports deeper, stronger root systems under stress',
                'Improves moisture efficiency during hot, dry conditions',
                'Ideal for summer protection and stress recovery',
            ],
            // Spec: 0.25, 1, 2.5, 5, 55, 275 gal
            // Base: $55/quart → $220/gal fallback
            'variants' => [
                ['size' => 'Quart',      'volume_oz' => 32,    'price' => 55.00],   // spec base price
                ['size' => '1 Gallon',   'volume_oz' => 128,   'price' => 220.00],  // $220/gal fallback
                ['size' => '2.5 Gallon', 'volume_oz' => 320,   'price' => 550.00],  // $220/gal × 2.5
                ['size' => '5 Gallon',   'volume_oz' => 640,   'price' => 1100.00], // $220/gal × 5
                ['size' => '55 Gallon',  'volume_oz' => 7040,  'price' => 12100.00],// $220/gal × 55
                ['size' => '275 Gallon', 'volume_oz' => 35200, 'price' => 60500.00],// $220/gal × 275
            ],
        ],
    ];

    /**
     * Non-engine products — available for display/upsell but never
     * recommended by the soil engine. tier_inclusion defaults to 'addon'.
     */
    private const ADDON_PRODUCTS = [
        [
            'name'        => 'Microbe Boost',
            'slug'        => 'microbe-boost',
            'subtitle'    => 'Soil Microbe Enhancer',
            'description' => 'Feeds beneficial soil microbes that improve nutrient cycling, organic matter breakdown, and overall soil health.',
            'app_rate'    => 2.0,
            'benefits'    => [
                'Feeds beneficial soil microbes that improve soil health',
                'Enhances nutrient cycling and organic matter breakdown',
                'Supports stronger roots and more efficient nutrient uptake',
                'Improves long-term soil performance',
            ],
            'variants' => [
                ['size' => 'Quart',      'volume_oz' => 32,  'price' => 49.99],
                ['size' => '1 Gallon',   'volume_oz' => 128, 'price' => 124.99],
                ['size' => '2.5 Gallon', 'volume_oz' => 320, 'price' => 209.99],
            ],
        ],
        [
            'name'        => 'Pet Spot Repair',
            'slug'        => 'pet-spot-repair',
            'subtitle'    => 'Neutralizes Urine Damage',
            'description' => 'Targets pet-damaged areas by helping neutralize salts and restoring soil conditions needed for grass recovery.',
            'app_rate'    => 2.0,
            'benefits'    => [
                'Helps neutralize salts that cause pet urine damage',
                'Restores soil conditions needed for grass recovery',
                'Supports regrowth in pet-damaged areas',
                'Safe for lawns with pets when used as directed',
            ],
            'variants' => [
                ['size' => 'Quart',      'volume_oz' => 32,  'price' => 49.99],
                ['size' => '1 Gallon',   'volume_oz' => 128, 'price' => 124.99],
                ['size' => '2.5 Gallon', 'volume_oz' => 320, 'price' => 209.99],
            ],
        ],
        [
            'name'        => 'SulfaCore',
            'slug'        => 'sulfacore',
            'subtitle'    => 'Sulfur & Nutrient Maximizer',
            'description' => 'Supplies sulfur to support chlorophyll production and nutrient availability, helping maintain deeper green color.',
            'app_rate'    => 7.0,
            'benefits'    => [
                'Supplies sulfur to support chlorophyll production',
                'Helps maintain deeper, richer green color',
                'Improves nutrient availability in the soil',
                'Supports overall turf vigor and performance',
            ],
            'variants' => [
                ['size' => 'Quart',      'volume_oz' => 32,  'price' => 49.99],
                ['size' => '1 Gallon',   'volume_oz' => 128, 'price' => 124.99],
                ['size' => '2.5 Gallon', 'volume_oz' => 320, 'price' => 209.99],
            ],
        ],
        [
            'name'        => 'K Boost',
            'slug'        => 'k-boost',
            'subtitle'    => 'Potassium Stress Defense',
            'description' => 'Delivers potassium to strengthen turf against stress from heat, drought, traffic, and seasonal transitions.',
            'app_rate'    => 15.0,
            'benefits'    => [
                'Strengthens turf against heat, drought, and traffic stress',
                'Supports root strength and stress tolerance',
                'Improves turf durability during seasonal transitions',
                'Ideal for summer and fall stress support',
            ],
            'variants' => [
                ['size' => 'Quart',      'volume_oz' => 32,  'price' => 49.99],
                ['size' => '1 Gallon',   'volume_oz' => 128, 'price' => 124.99],
                ['size' => '2.5 Gallon', 'volume_oz' => 320, 'price' => 209.99],
            ],
        ],
        [
            'name'        => 'Green Gro+',
            'slug'        => 'green-gro',
            'subtitle'    => 'Balanced Growth Formula',
            'description' => 'Promotes thicker, greener turf by supporting balanced growth and improved nutrient efficiency.',
            'app_rate'    => 12.0,
            'benefits'    => [
                'Promotes thicker, greener turf growth',
                'Supports balanced nutrient efficiency',
                'Improves overall lawn density and appearance',
                'Ideal for regular maintenance applications',
            ],
            'variants' => [
                ['size' => 'Quart',      'volume_oz' => 32,  'price' => 27.99],
                ['size' => '1 Gallon',   'volume_oz' => 128, 'price' => 89.99],
                ['size' => '2.5 Gallon', 'volume_oz' => 320, 'price' => 199.99],
            ],
        ],
        [
            'name'        => 'Fall Support',
            'slug'        => 'fall-support',
            'subtitle'    => 'Winter Prep & Root Strengthener',
            'description' => 'Prepares turf for winter by strengthening roots and improving stress tolerance heading into cold conditions.',
            'app_rate'    => 12.0,
            'benefits'    => [
                'Strengthens roots before winter dormancy',
                'Improves cold-weather stress tolerance',
                'Helps turf store nutrients for spring green-up',
                'Ideal for fall lawn preparation',
            ],
            'variants' => [
                ['size' => 'Quart',      'volume_oz' => 32,  'price' => 27.99],
                ['size' => '1 Gallon',   'volume_oz' => 128, 'price' => 89.99],
                ['size' => '2.5 Gallon', 'volume_oz' => 320, 'price' => 199.99],
            ],
        ],
    ];

    // -------------------------------------------------------

    public function run(): void
    {
        $allProducts = array_merge(
            self::CORE_PRODUCTS,
            self::MODIFIER_PRODUCTS,
            self::ADDON_PRODUCTS,
        );

        foreach ($allProducts as $index => $data) {
            $tierInclusion = $data['tier_inclusion'] ?? 'addon';

            $product = Product::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name'                       => $data['name'],
                    'subtitle'                   => $data['subtitle'],
                    'slug'                       => $data['slug'],
                    'description'                => $data['description'],
                    'benefits'                   => json_encode($data['benefits']),
                    'usage_instructions'         => 'Shake well. Apply using a hose-end sprayer or tank sprayer at the recommended rate. Water in lightly after application for best results.',
                    'coverage_sqft'              => (int) (($data['variants'][0]['volume_oz'] / $data['app_rate']) * 1000),
                    'application_rate_oz_per_1k' => $data['app_rate'],
                    'base_price'                 => $data['variants'][0]['price'],
                    'discount_price'             => null,
                    'rating_avg'                 => 0,
                    'reviews_count'              => 0,
                    'is_active'                  => true,
                    'tier_inclusion'             => $tierInclusion,
                ],
            );

            // Remove old variants and re-seed cleanly
            $product->variants()->delete();

            foreach ($data['variants'] as $sortOrder => $variant) {
                ProductVariant::create([
                    'product_id'      => $product->id,
                    'sku'             => strtoupper($data['slug']) . '-' . strtoupper(Str::slug($variant['size'])),
                    'size_label'      => $variant['size'],
                    'size_volume_oz'  => $variant['volume_oz'],
                    'sort_order'      => $sortOrder,
                    'price'           => $variant['price'],
                    'compare_at_price'=> round($variant['price'] * 1.2, 2),
                    'stock_quantity'  => 100,
                    'is_default'      => $sortOrder === 0,
                ]);
            }

            // Seed placeholder image only if none exists
            if (! $product->images()->exists()) {
                $product->images()->create([
                    'image_url'  => 'products/placeholder.png',
                    'is_primary' => true,
                    'sort_order' => 0,
                ]);
            }
        }
    }
}