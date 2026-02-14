<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use App\Models\ProductReview; // Assuming you have this model
use App\Models\User; // To attach reviews to a user
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create([
            'name' => 'Verified Customer',
            'email' => 'customer@example.com',
        ]);

        $products = [
            [
                'name' => 'HeatGuard',
                'subtitle' => 'Heat & Drought Stress Protection',
                'description' => 'Helps turf withstand heat and drought stress by supporting root health and moisture efficiency during extreme summer conditions.',
                'app_rate' => 2.0,
                'benefits' => [
                    'Helps turf stay green and resilient during extreme heat and drought',
                    'Supports deeper, stronger root systems under stress',
                    'Improves moisture efficiency during hot, dry conditions',
                    'Ideal for summer protection and stress recovery'
                ],
                'variants' => [
                    ['size' => 'Quart', 'price' => 49.99, 'volume' => 32],
                    ['size' => '1 Gallon', 'price' => 124.99, 'volume' => 128],
                    ['size' => '2.5 Gallon', 'price' => 209.99, 'volume' => 320],
                ],
                'reviews' => [
                    "We hit triple-digit heat and my lawn held color way better than usual.",
                    "This helped my grass bounce back faster during a really dry stretch.",
                    "I noticed less stress and fewer dry patches during peak summer heat."
                ]
            ],
            [
                'name' => 'Aerate',
                'subtitle' => 'Liquid Soil Aerator',
                'description' => 'Improves soil structure by reducing compaction, allowing water, nutrients, and oxygen to move more freely into the root zone.',
                'app_rate' => 0.5,
                'benefits' => [
                    'Reduces soil compaction without mechanical aeration',
                    'Improves water, air, and nutrient movement into the soil',
                    'Enhances root development and soil structure',
                    'Safe for use any time during the growing season'
                ],
                'variants' => [
                    ['size' => 'Pint', 'price' => 39.99, 'volume' => 16],
                    ['size' => 'Quart', 'price' => 64.99, 'volume' => 32],
                    ['size' => '1 Gallon', 'price' => 149.99, 'volume' => 128],
                ],
                'reviews' => [
                    "After using this, water actually soaked in instead of running off.",
                    "My soil was hard as a rock and this made a noticeable difference.",
                    "Grass rooted better and didn't feel as compact after a few weeks."
                ]
            ],
            [
                'name' => 'PatchPro+',
                'subtitle' => 'Lawn Repair & Regrowth Formula',
                'description' => 'Repairs thin, stressed, or damaged turf by improving soil conditions that promote faster, more uniform regrowth.',
                'app_rate' => 10.0,
                'benefits' => [
                    'Helps repair thin, bare, or damaged turf areas',
                    'Improves soil conditions for faster, more uniform regrowth',
                    'Strengthens roots to support new grass establishment',
                    'Ideal for high-traffic or stressed lawn areas'
                ],
                'variants' => [
                    ['size' => 'Quart', 'price' => 29.99, 'volume' => 32],
                    ['size' => '1 Gallon', 'price' => 89.99, 'volume' => 128],
                    ['size' => '2.5 Gallon', 'price' => 199.99, 'volume' => 320],
                ],
                'reviews' => [
                    "Thin spots filled in quicker than they ever have before.",
                    "Helped repair areas that normally struggle all summer.",
                    "Great for high-traffic spots where grass usually won't grow."
                ]
            ],
            [
                'name' => 'Neutralyze',
                'subtitle' => 'Soil pH Balancer & Calcium',
                'description' => 'Supplies highly available calcium to help balance soil pH and improve nutrient uptake for healthier, stronger turf.',
                'app_rate' => 12.0,
                'benefits' => [
                    'Supplies highly available calcium for healthier turf',
                    'Helps balance soil pH and nutrient availability',
                    'Supports stronger roots and improved soil structure',
                    'Improves overall turf performance and resilience'
                ],
                'variants' => [
                    ['size' => 'Quart', 'price' => 24.99, 'volume' => 32],
                    ['size' => '1 Gallon', 'price' => 64.99, 'volume' => 128],
                    ['size' => '2.5 Gallon', 'price' => 119.99, 'volume' => 320],
                ],
                'reviews' => [
                    "My lawn started responding better to everything else I was applying.",
                    "Grass looked healthier and more consistent across the yard.",
                    "This helped even things out where my soil has always been tough."
                ]
            ],
            [
                'name' => 'KickStart',
                'subtitle' => 'Rapid Growth & Recovery',
                'description' => 'Jump-starts turf growth and recovery by delivering readily available nutrients that support early root and shoot development.',
                'app_rate' => 15.0,
                'benefits' => [
                    'Promotes faster green-up and turf recovery',
                    'Supports early root and shoot development',
                    'Helps turf rebound from stress, wear, or seasonal transitions',
                    'Ideal for spring start-up or post-stress recovery'
                ],
                'variants' => [
                    ['size' => 'Quart', 'price' => 27.99, 'volume' => 32],
                    ['size' => '1 Gallon', 'price' => 89.99, 'volume' => 128],
                    ['size' => '2.5 Gallon', 'price' => 199.99, 'volume' => 320],
                ],
                'reviews' => [
                    "Green-up was noticeably faster after application.",
                    "Helped the lawn recover quickly after stress and mowing.",
                    "Perfect for getting things moving early in the season."
                ]
            ],
            [
                'name' => 'Turf Fuel N',
                'subtitle' => 'Controlled Release Nitrogen',
                'description' => 'Provides a steady, controlled release of nitrogen to drive consistent green-up without excessive growth or burn.',
                'app_rate' => 15.0,
                'benefits' => [
                    'Provides steady nitrogen for consistent green color',
                    'Reduces risk of burn compared to quick-release fertilizers',
                    'Supports balanced growth without excessive top growth',
                    'Ideal for maintaining healthy, uniform turf'
                ],
                'variants' => [
                    ['size' => 'Quart', 'price' => 27.99, 'volume' => 32],
                    ['size' => '1 Gallon', 'price' => 89.99, 'volume' => 128],
                    ['size' => '2.5 Gallon', 'price' => 199.99, 'volume' => 320],
                ],
                'reviews' => [
                    "Consistent green color without crazy growth.",
                    "No burn issues and the lawn stayed even and healthy.",
                    "It fed the grass without making me mow nonstop."
                ]
            ],
            [
                'name' => 'Microbe Boost',
                'subtitle' => 'Soil Microbe Enhancer',
                'description' => 'Feeds beneficial soil microbes that improve nutrient cycling, organic matter breakdown, and overall soil health.',
                'app_rate' => 2.0,
                'benefits' => [
                    'Feeds beneficial soil microbes that improve soil health',
                    'Enhances nutrient cycling and organic matter breakdown',
                    'Supports stronger roots and more efficient nutrient uptake',
                    'Improves long-term soil performance'
                ],
                'variants' => [
                    ['size' => 'Quart', 'price' => 49.99, 'volume' => 32],
                    ['size' => '1 Gallon', 'price' => 124.99, 'volume' => 128],
                    ['size' => '2.5 Gallon', 'price' => 209.99, 'volume' => 320],
                ],
                'reviews' => [
                    "Soil feels healthier and the lawn looks stronger overall.",
                    "Grass responded better to nutrients after using this.",
                    "Great product for improving the soil long-term."
                ]
            ],
            [
                'name' => 'Pet Spot Repair',
                'subtitle' => 'Neutralizes Urine Damage',
                'description' => 'Targets pet-damaged areas by helping neutralize salts and restoring soil conditions needed for grass recovery.',
                'app_rate' => 2.0,
                'benefits' => [
                    'Helps neutralize salts that cause pet urine damage',
                    'Restores soil conditions needed for grass recovery',
                    'Supports regrowth in pet-damaged areas',
                    'Safe for lawns with pets when used as directed'
                ],
                'variants' => [
                    ['size' => 'Quart', 'price' => 49.99, 'volume' => 32],
                    ['size' => '1 Gallon', 'price' => 124.99, 'volume' => 128],
                    ['size' => '2.5 Gallon', 'price' => 209.99, 'volume' => 320],
                ],
                'reviews' => [
                    "This helped green up areas my dog usually destroys.",
                    "Spots recovered faster and didn't stay brown as long.",
                    "Best thing I've used for pet damage so far."
                ]
            ],
            [
                'name' => 'SulfaCore',
                'subtitle' => 'Sulfur & Nutrient Maximizer',
                'description' => 'Supplies sulfur to support chlorophyll production and nutrient availability, helping maintain deeper green color.',
                'app_rate' => 7.0,
                'benefits' => [
                    'Supplies sulfur to support chlorophyll production',
                    'Helps maintain deeper, richer green color',
                    'Improves nutrient availability in the soil',
                    'Supports overall turf vigor and performance'
                ],
                'variants' => [
                    ['size' => 'Quart', 'price' => 49.99, 'volume' => 32],
                    ['size' => '1 Gallon', 'price' => 124.99, 'volume' => 128],
                    ['size' => '2.5 Gallon', 'price' => 209.99, 'volume' => 320],
                ],
                'reviews' => [
                    "Color deepened within a couple weeks.",
                    "Helped bring back a richer green without pushing growth.",
                    "Made the lawn look healthier overall."
                ]
            ],
            [
                'name' => 'K Boost',
                'subtitle' => 'Potassium Stress Defense',
                'description' => 'Delivers potassium to strengthen turf against stress from heat, drought, traffic, and seasonal transitions.',
                'app_rate' => 15.0,
                'benefits' => [
                    'Strengthens turf against heat, drought, and traffic stress',
                    'Supports root strength and stress tolerance',
                    'Improves turf durability during seasonal transitions',
                    'Ideal for summer and fall stress support'
                ],
                'variants' => [
                    ['size' => 'Quart', 'price' => 49.99, 'volume' => 32],
                    ['size' => '1 Gallon', 'price' => 124.99, 'volume' => 128],
                    ['size' => '2.5 Gallon', 'price' => 209.99, 'volume' => 320],
                ],
                'reviews' => [
                    "Grass handled heat and foot traffic better after using this.",
                    "Lawn stayed stronger during stressful weather.",
                    "Helped toughen things up when conditions got rough."
                ]
            ],
            [
                'name' => 'Green Gro+',
                'subtitle' => 'Balanced Growth Formula',
                'description' => 'Promotes thicker, greener turf by supporting balanced growth and improved nutrient efficiency.',
                'app_rate' => 12.0,
                'benefits' => [
                    'Promotes thicker, greener turf growth',
                    'Supports balanced nutrient efficiency',
                    'Improves overall lawn density and appearance',
                    'Ideal for regular maintenance applications'
                ],
                'variants' => [
                    ['size' => 'Quart', 'price' => 27.99, 'volume' => 32],
                    ['size' => '1 Gallon', 'price' => 89.99, 'volume' => 128],
                    ['size' => '2.5 Gallon', 'price' => 199.99, 'volume' => 320],
                ],
                'reviews' => [
                    "The lawn filled in thicker and looked more uniform.",
                    "Great for keeping everything looking healthy and full.",
                    "Easy way to maintain good color and density."
                ]
            ],
            [
                'name' => 'Fall Support',
                'subtitle' => 'Winter Prep & Root Strengthener',
                'description' => 'Prepares turf for winter by strengthening roots and improving stress tolerance heading into cold conditions.',
                'app_rate' => 12.0,
                'benefits' => [
                    'Strengthens roots before winter dormancy',
                    'Improves cold-weather stress tolerance',
                    'Helps turf store nutrients for spring green-up',
                    'Ideal for fall lawn preparation'
                ],
                'variants' => [
                    ['size' => 'Quart', 'price' => 27.99, 'volume' => 32],
                    ['size' => '1 Gallon', 'price' => 89.99, 'volume' => 128],
                    ['size' => '2.5 Gallon', 'price' => 199.99, 'volume' => 320],
                ],
                'reviews' => [
                    "Lawn went into winter looking strong instead of stressed.",
                    "Grass greened up faster the following spring.",
                    "I'll definitely use this every fall moving forward."
                ]
            ],
        ];

        foreach ($products as $p) {

            $slug = Str::slug($p['name']);

            $baseCoverage = ($p['variants'][0]['volume'] / $p['app_rate']) * 1000;


            $product = Product::create([
                'name' => $p['name'],
                'subtitle' => $p['subtitle'],
                'slug' => $slug,
                'description' => $p['description'],
                'benefits' => $p['benefits'], 
                'usage_instructions' => 'Shake well. Apply using a hose-end sprayer or tank sprayer at the recommended rate. Water in lightly after application for best results.', // Generic instruction
                'coverage_sqft' => (int) $baseCoverage,
                'application_rate_oz_per_1k' => $p['app_rate'],
                'base_price' => $p['variants'][0]['price'], 
                'discount_price' => null,
                'rating_avg' => 0, 
                'reviews_count' => 0,
                'is_active' => true,
            ]);

     
            foreach ($p['variants'] as $index => $v) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => strtoupper(Str::slug($p['name'])) . '-' . strtoupper(Str::slug($v['size'])),
                    'size_label' => $v['size'],
                    'size_volume_oz' => $v['volume'],
                    'sort_order' => $index,
                    'price' => $v['price'],
                    'compare_at_price' => $v['price'] * 1.2, 
                    'stock_quantity' => 100,
                    'is_default' => $index === 0, 
                ]);
            }

     
            ProductImage::create([
                'product_id' => $product->id,
                'image_url' => 'products/placeholder.png', 
                'is_primary' => true,
                'sort_order' => 0,
            ]);

  
            foreach ($p['reviews'] as $reviewContent) {
                ProductReview::create([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'rating' => 5,
                    'title' => 'Great results!',
                    'content' => $reviewContent,
                    'is_verified_purchase' => true,
                    'is_approved' => true,
                    'helpful_count' => rand(1, 10),
                ]);
            }
        }
    }
}