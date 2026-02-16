<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Create Products first to satisfy Foreign Key constraints
        $this->createProducts();

        // 1. Create Services
        $services = [
            'Lawn' => $this->createService('Lawn'),
            'Pest' => $this->createService('Pest'),
            'Garden' => $this->createService('Garden'),
        ];

        // 2. Create Plans for "Lawn" Service
        $this->createLawnPlans($services['Lawn']);

        // 3. Create Plans for "Pest" Service
        $this->createPestPlans($services['Pest']);
        
        // 4. Create Plans for "Garden" Service
        $this->createGardenPlans($services['Garden']);
    }

    private function createProducts(): void
    {
        // These products must exist for plan_deliverables to link to them via SKU
        $products = [
            ['sku' => 'FERT-N-20-0-0', 'name' => 'High Nitrogen Fertilizer'],
            ['sku' => 'WEED-PRE-01', 'name' => 'Pre-Emergent Weed Control'],
            ['sku' => 'FERT-MAIN-10-10-10', 'name' => 'Balanced 10-10-10 Fertilizer'],
            ['sku' => 'FERT-WINTER-05-0-20', 'name' => 'Winterizer Fertilizer'],
            ['sku' => 'FERT-PREM-24-0-4', 'name' => 'Premium Lawn Food'],
            ['sku' => 'PEST-MOSQ-01', 'name' => 'Mosquito Defense Pack'],
            ['sku' => 'FERT-SUMMER-GUARD', 'name' => 'Summer Guard Heat Protection'],
            ['sku' => 'PEST-TICK-01', 'name' => 'Tick Shield'],
            ['sku' => 'SOIL-KIT-01', 'name' => 'Professional Soil Test Kit'],
            ['sku' => 'SEED-TRAFFIC-BLEND', 'name' => 'High Traffic Grass Seed'],
            ['sku' => 'SPOT-REPAIR-KIT', 'name' => 'Pet Spot Repair Kit'],
            ['sku' => 'FERT-ORG-SAFE', 'name' => 'Organic Pet Safe Fertilizer'],
            ['sku' => 'PEST-SPRAY-CONCENTRATE', 'name' => 'Home Pest Barrier Concentrate'],
            ['sku' => 'GARDEN-BLOOM-LIQ', 'name' => 'Liquid Bloom Booster'],
            ['sku' => 'GARDEN-ROOT-STIM', 'name' => 'Deep Root Stimulator'],
        ];

        foreach ($products as $product) {
            // Using updateOrInsert so we don't duplicate or fail if they exist
            DB::table('products')->updateOrInsert(
                ['sku' => $product['sku']],
                [
                    'name' => $product['name'],
                    'slug' => Str::slug($product['name']),
                    'description' => 'Auto-generated for Plan deliverables',
                    'base_price' => rand(1000, 10000) / 100, // Random price between 10.00 and 100.00
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function createService(string $name): int
    {
        return DB::table('services')->insertGetId([
            'name' => $name,
            'slug' => Str::slug($name),
            'is_active' => true,
            'created_at' => now(),
        ]);
    }

    private function createLawnPlans(int $serviceId): void
    {
        // --- Plan A: Basic Care ---
        $basicPlanId = DB::table('plans')->insertGetId([
            'service_id' => $serviceId,
            'name' => 'Basic Care',
            'slug' => 'lawn-basic-care',
            'description' => 'Essential nutrients for a healthy, green lawn.',
            'base_price_yearly' => 129.00,
            'current_price_yearly' => 99.00,
            'is_recommended' => false,
            'target_audience' => 'Homeowners who want a simple, effective routine.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->addFeatures($basicPlanId, [
            ['title' => 'Essential Nutrients', 'subtitle' => 'Nitrogen-rich fertilizer for growth'],
            ['title' => 'Weed Control', 'subtitle' => 'Pre-emergent weed prevention'],
            ['title' => 'Soil Health', 'subtitle' => 'Basic soil conditioning'],
        ]);

        $this->addDeliverables($basicPlanId, [
            ['product_sku' => 'FERT-N-20-0-0', 'quantity' => 1, 'shipment_month' => 1], // Spring
            ['product_sku' => 'WEED-PRE-01', 'quantity' => 1, 'shipment_month' => 1],
            ['product_sku' => 'FERT-MAIN-10-10-10', 'quantity' => 1, 'shipment_month' => 4], // Summer
            ['product_sku' => 'FERT-WINTER-05-0-20', 'quantity' => 1, 'shipment_month' => 9], // Fall
        ]);

        // --- Plan B: Keep & Protect (Recommended) ---
        $protectPlanId = DB::table('plans')->insertGetId([
            'service_id' => $serviceId,
            'name' => 'Keep & Protect',
            'slug' => 'lawn-keep-and-protect',
            'description' => 'Our most popular plan. Everything in Basic plus advanced pest protection.',
            'base_price_yearly' => 199.00,
            'current_price_yearly' => 149.00,
            'is_recommended' => true,
            'target_audience' => 'Families and pet owners who want a safe, green lawn.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->addFeatures($protectPlanId, [
            ['title' => 'Grow a green, lush lawn', 'subtitle' => 'With custom fertilizers'],
            ['title' => 'Banish biting pests', 'subtitle' => 'Mosquito and tick control included'],
            ['title' => 'Weed Defense', 'subtitle' => 'Targeted weed control for dandelions and clover'],
            ['title' => 'Soil Analysis', 'subtitle' => 'Yearly soil test included'],
        ]);

        $this->addDeliverables($protectPlanId, [
            ['product_sku' => 'FERT-PREM-24-0-4', 'quantity' => 1, 'shipment_month' => 1],
            ['product_sku' => 'PEST-MOSQ-01', 'quantity' => 2, 'shipment_month' => 3], // Early Summer
            ['product_sku' => 'FERT-SUMMER-GUARD', 'quantity' => 1, 'shipment_month' => 6],
            ['product_sku' => 'PEST-TICK-01', 'quantity' => 1, 'shipment_month' => 8],
            ['product_sku' => 'SOIL-KIT-01', 'quantity' => 1, 'shipment_month' => 1],
        ]);
        
        // --- Plan C: Lawn & Paws ---
        $pawsPlanId = DB::table('plans')->insertGetId([
            'service_id' => $serviceId,
            'name' => 'Lawn & Paws',
            'slug' => 'lawn-and-paws',
            'description' => 'Specially designed for pet owners. Repairs urine spots and is 100% pet safe.',
            'base_price_yearly' => 249.00,
            'current_price_yearly' => 199.00,
            'is_recommended' => false,
            'target_audience' => 'Dog owners dealing with patches and wear.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->addFeatures($pawsPlanId, [
            ['title' => 'Urine Spot Repair', 'subtitle' => 'Neutralizes soil and reseeds spots'],
            ['title' => 'Durable Grass Seed', 'subtitle' => 'High-traffic blend for play areas'],
            ['title' => 'Pet Safe', 'subtitle' => 'Non-toxic ingredients, safe immediately after application'],
        ]);

        $this->addDeliverables($pawsPlanId, [
            ['product_sku' => 'SEED-TRAFFIC-BLEND', 'quantity' => 2, 'shipment_month' => 1],
            ['product_sku' => 'SPOT-REPAIR-KIT', 'quantity' => 2, 'shipment_month' => 3],
            ['product_sku' => 'FERT-ORG-SAFE', 'quantity' => 2, 'shipment_month' => 5],
        ]);
    }

    private function createPestPlans(int $serviceId): void
    {
        // --- Plan A: Bug Barrier ---
        $bugId = DB::table('plans')->insertGetId([
            'service_id' => $serviceId,
            'name' => 'Bug Barrier',
            'slug' => 'pest-bug-barrier',
            'description' => 'Perimeter protection against common household pests.',
            'base_price_yearly' => 299.00,
            'current_price_yearly' => 249.00,
            'is_recommended' => true,
            'target_audience' => 'Homes prone to ants and spiders.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->addFeatures($bugId, [
            ['title' => 'Perimeter Defense', 'subtitle' => 'Keeps bugs out of the house'],
            ['title' => 'Mosquito Control', 'subtitle' => 'Reduces population by 90%'],
            ['title' => 'Tick Prevention', 'subtitle' => 'Protects your family from Lyme disease'],
        ]);

        $this->addDeliverables($bugId, [
             ['product_sku' => 'PEST-SPRAY-CONCENTRATE', 'quantity' => 4, 'shipment_month' => 1], // Quarterly
             ['product_sku' => 'PEST-SPRAY-CONCENTRATE', 'quantity' => 4, 'shipment_month' => 4],
             ['product_sku' => 'PEST-SPRAY-CONCENTRATE', 'quantity' => 4, 'shipment_month' => 7],
             ['product_sku' => 'PEST-SPRAY-CONCENTRATE', 'quantity' => 4, 'shipment_month' => 10],
        ]);
    }

    private function createGardenPlans(int $serviceId): void
    {
         // --- Plan A: Bloom Booster ---
         $bloomId = DB::table('plans')->insertGetId([
            'service_id' => $serviceId,
            'name' => 'Bloom Booster',
            'slug' => 'garden-bloom-booster',
            'description' => 'Maximize flowers and vegetables.',
            'base_price_yearly' => 89.00,
            'current_price_yearly' => null,
            'is_recommended' => false,
            'target_audience' => 'Gardeners wanting bigger yields.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->addFeatures($bloomId, [
            ['title' => 'Flower Power', 'subtitle' => 'High phosphorus blend for blooms'],
            ['title' => 'Root Strength', 'subtitle' => 'Deep root technology'],
        ]);
        
        $this->addDeliverables($bloomId, [
            ['product_sku' => 'GARDEN-BLOOM-LIQ', 'quantity' => 1, 'shipment_month' => 2],
            ['product_sku' => 'GARDEN-ROOT-STIM', 'quantity' => 1, 'shipment_month' => 4],
       ]);
    }

    private function addFeatures(int $planId, array $features): void
    {
        foreach ($features as $index => $feature) {
            DB::table('plan_features')->insert([
                'plan_id' => $planId,
                'title' => $feature['title'],
                'subtitle' => $feature['subtitle'] ?? null,
                'icon_url' => null, // You can add URLs to icons here later
                'image_url' => null,
                'sort_order' => $index,
            ]);
        }
    }

    private function addDeliverables(int $planId, array $deliverables): void
    {
        foreach ($deliverables as $deliverable) {
            DB::table('plan_deliverables')->insert([
                'plan_id' => $planId,
                'product_sku' => $deliverable['product_sku'],
                'quantity' => $deliverable['quantity'],
                'shipment_month' => $deliverable['shipment_month'],
            ]);
        }
    }
}