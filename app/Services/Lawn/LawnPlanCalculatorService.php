<?php

namespace App\Services\Lawn;

class LawnPlanCalculatorService
{
    // -------------------------------------------------------
    // Constants
    // -------------------------------------------------------

    private const OZ_PER_GAL   = 128;
    private const ACRE_SQFT    = 43560;
    private const SQFT_PER_GAL = 10.7639;

    private const PACKAGE_SIZES = [5.0, 2.5, 1.0, 0.5, 0.25];

    private const SIZE_MULTIPLIERS = [
        [0,     4000,  1.0],
        [4001,  8000,  1.4],
        [8001,  12000, 1.8],
        [12001, 20000, 2.2],
        [20001, 32000, 3.0],
        [32001, 43560, 4.0],
    ];

    private const TIER_BASE_PRICES = [
        'bronze' => 179.00,
        'silver' => 249.00,
        'gold'   => 289.00,
    ];

    private const ADDON_PRICE_PER_GAL = [
        'PATCHPRO'       => 42.00,
        'PET_SPOT_REPAIR' => 38.00,
        'AERATE'         => 35.00,
        'HEATGUARD'      => 35.00,
    ];

    private const PRODUCTS = [
        'NEUTRALYZE'      => ['name' => 'Neutralyze',       'oz_per_1000' => 60],
        'KICKSTART'       => ['name' => 'KickStart+',       'oz_per_1000' => 75],
        'TURFFUEL_N'      => ['name' => 'TurfFuel N',       'oz_per_1000' => 75],
        'MICROBE_BOOST'   => ['name' => 'Microbe-Boost',    'oz_per_1000' => 10],
        'PATCHPRO'        => ['name' => 'PatchPro+',        'oz_per_1000' => 55],
        'PET_SPOT_REPAIR' => ['name' => 'Pet Spot Repair',  'oz_per_1000' => 40],
        'AERATE'          => ['name' => 'Aerate',           'oz_per_1000' => 64],
        'HEATGUARD'       => ['name' => 'HeatGuard',        'oz_per_1000' => 40],
    ];

    private const TIER_FIXED_PRODUCTS = [
        'bronze' => ['NEUTRALYZE', 'KICKSTART', 'MICROBE_BOOST'],
        'silver' => ['NEUTRALYZE', 'KICKSTART', 'TURFFUEL_N', 'MICROBE_BOOST'],
        'gold'   => ['NEUTRALYZE', 'KICKSTART', 'TURFFUEL_N', 'MICROBE_BOOST'],
    ];

    // pH-only Neutralyze schedule (gal/acre)
    // [ph_min, ph_max, gal_per_acre]
    private const NEUTRALYZE_SCHEDULE = [
        [6.5, 7.0, 0.0],
        [6.0, 6.4, 1.5],
        [5.5, 5.9, 2.75],
        [5.0, 5.4, 3.5],
        [4.5, 4.9, 5.0],
    ];

    // -------------------------------------------------------
    // Public Entry Point
    // -------------------------------------------------------

    public function calculate(string $tier, int $squareFeet, array $soilSnapshot, array $quizAnswers): array
    {
        $tier        = strtolower($tier);
        $sizeMulti   = $this->getSizeMultiplier($squareFeet);
        $soilMulti   = $this->getSoilMultipliers($soilSnapshot);
        $triggers    = $this->evaluateTriggers($quizAnswers, $soilSnapshot);
        $basePrice   = $this->resolveBasePrice($tier, $sizeMulti);
        $products    = $this->resolveProducts($tier, $squareFeet, $soilSnapshot, $quizAnswers, $soilMulti, $triggers);
        $addonsTotal = $this->sumAddons($products);

        return [
            'tier'              => $tier,
            'square_feet'       => $squareFeet,
            'size_multiplier'   => $sizeMulti,
            'base_price'        => round($basePrice, 2),
            'addons_total'      => round($addonsTotal, 2),
            'total_price'       => round($basePrice + $addonsTotal, 2),
            'products'          => $products,
        ];
    }

    // -------------------------------------------------------
    // Size Multiplier
    // -------------------------------------------------------

    private function getSizeMultiplier(int $sqft): float
    {
        foreach (self::SIZE_MULTIPLIERS as [$min, $max, $mult]) {
            if ($sqft >= $min && $sqft <= $max) {
                return $mult;
            }
        }

        return self::SIZE_MULTIPLIERS[array_key_last(self::SIZE_MULTIPLIERS)][2];
    }

    // -------------------------------------------------------
    // Base Price
    // -------------------------------------------------------

    private function resolveBasePrice(string $tier, float $sizeMulti): float
    {
        $base = self::TIER_BASE_PRICES[$tier] ?? self::TIER_BASE_PRICES['bronze'];

        return $base * $sizeMulti;
    }

    // -------------------------------------------------------
    // Soil-Driven Rate Multipliers
    // -------------------------------------------------------

    private function getSoilMultipliers(array $soil): array
    {
        $drought    = strtolower($soil['drought_stress_risk'] ?? 'low');
        $compaction = strtolower($soil['compaction_risk'] ?? 'low');
        $leaching   = strtolower($soil['n_leaching_risk'] ?? 'low');

        $droughtMap    = ['low' => 1.00, 'med' => 1.10, 'high' => 1.20];
        $compactionMap = ['low' => 1.00, 'med' => 1.15, 'high' => 1.30];
        $leachingMap   = ['low' => 1.00, 'med' => 1.10, 'high' => 1.20];

        $aerateMult   = min(
            ($droughtMap[$drought] ?? 1.0) * ($compactionMap[$compaction] ?? 1.0),
            1.50
        );

        $turffuelMult = min($leachingMap[$leaching] ?? 1.0, 1.25);

        return [
            'AERATE'    => $aerateMult,
            'TURFFUEL_N'=> $turffuelMult,
        ];
    }

    // -------------------------------------------------------
    // Triggers
    // -------------------------------------------------------

    private function evaluateTriggers(array $answers, array $soil): array
    {
        $pets    = strtolower($answers['pets'] ?? '');
        $patches = strtolower($answers['patches'] ?? 'none');
        $climate = strtolower($soil['climate_zone'] ?? '');
        $drought = strtolower($soil['drought_stress_risk'] ?? 'low');
        $compact = strtolower($soil['compaction_risk'] ?? 'low');

        return [
            'pet_trigger'        => $pets === 'lot',
            'bare_patch_trigger' => in_array($patches, ['lots', 'moderate']),
            'heatguard_trigger'  => str_contains($climate, 'warm'),
            'aerate_trigger'     => !($drought === 'low' && $compact === 'low'),
        ];
    }

    // -------------------------------------------------------
    // Product Resolution
    // -------------------------------------------------------

    private function resolveProducts(
        string $tier,
        int $sqft,
        array $soil,
        array $answers,
        array $soilMulti,
        array $triggers
    ): array {
        $products = [];

        // Fixed tier products
        foreach (self::TIER_FIXED_PRODUCTS[$tier] as $pid) {
            $rateMulti    = $soilMulti[$pid] ?? 1.0;
            $ozOverride   = null;
            $note         = '';

            if ($pid === 'NEUTRALYZE') {
                [$ozOverride, $note] = $this->neutralyzeRate($soil['avg_ph'] ?? 7.0);
            }

            if ($pid === 'TURFFUEL_N') {
                $leaching = strtolower($soil['n_leaching_risk'] ?? 'low');
                $note     = "N leaching risk = " . strtoupper($leaching) . " → rate x{$rateMulti}";
            }

            $products[] = $this->buildLineItem(
                $pid, $sqft, $rateMulti, 'INCLUDED', $note, $ozOverride
            );
        }

        // PatchPro+
        if ($triggers['bare_patch_trigger']) {
            $pricingType = $tier === 'bronze' ? 'ADD-ON' : 'INCLUDED';
            $patches     = strtoupper($answers['patches'] ?? '');
            $products[]  = $this->buildLineItem(
                'PATCHPRO', $sqft, 1.0, $pricingType,
                "Bare patches = {$patches} → PatchPro+ added ({$pricingType})"
            );
        }

        // Pet Spot Repair
        if ($triggers['pet_trigger']) {
            $products[] = $this->buildLineItem(
                'PET_SPOT_REPAIR', $sqft, 1.0, 'ADD-ON',
                'Pets on lawn = OFTEN → Pet Spot Repair add-on'
            );
        }

        // Aerate
        if ($triggers['aerate_trigger']) {
            $pricingType = $tier === 'bronze' ? 'ADD-ON' : 'INCLUDED';
            $rateMulti   = $soilMulti['AERATE'] ?? 1.0;
            $drought     = strtoupper($soil['drought_stress_risk'] ?? 'low');
            $compact     = strtoupper($soil['compaction_risk'] ?? 'low');
            $products[]  = $this->buildLineItem(
                'AERATE', $sqft, $rateMulti, $pricingType,
                "Aerate triggered (drought={$drought}, compaction={$compact}) → rate x{$rateMulti} ({$pricingType})"
            );
        }

        // HeatGuard
        if ($triggers['heatguard_trigger']) {
            $products[] = $this->buildLineItem(
                'HEATGUARD', $sqft, 1.0, 'ADD-ON',
                'Climate zone = WARM SEASON → HeatGuard added'
            );
        }

        return $products;
    }

    // -------------------------------------------------------
    // Build Single Line Item
    // -------------------------------------------------------

    private function buildLineItem(
        string $productId,
        int $sqft,
        float $rateMultiplier,
        string $pricingType,
        string $note = '',
        ?float $ozPer1000Override = null
    ): array {
        $product    = self::PRODUCTS[$productId];
        $baseRate   = $ozPer1000Override ?? $product['oz_per_1000'];
        $adjustedRate = $baseRate * $rateMultiplier;

        $sqftFactor = $sqft / 1000.0;
        $ozNeeded   = $adjustedRate * $sqftFactor;
        $galNeeded  = $ozNeeded / self::OZ_PER_GAL;

        $packaging  = $this->optimizePackaging($galNeeded);

        $unitPrice  = 0.0;
        $totalPrice = 0.0;

        if ($pricingType === 'ADD-ON') {
            $unitPrice  = self::ADDON_PRICE_PER_GAL[$productId] ?? 0.0;
            $totalPrice = round($unitPrice * $galNeeded, 2);
        }

        return [
            'id'               => $productId,
            'name'             => $product['name'],
            'oz_needed'        => round($ozNeeded, 2),
            'gallons_needed'   => round($galNeeded, 4),
            'pricing_type'     => $pricingType,
            'unit_price'       => $unitPrice,
            'total_price'      => $totalPrice,
            'packages'         => $packaging['packages'],
            'delivered_gallons'=> $packaging['delivered_gallons'],
            'overage_gallons'  => $packaging['overage_gallons'],
            'note'             => $note,
        ];
    }

    // -------------------------------------------------------
    // Neutralyze Rate — pH Only
    // -------------------------------------------------------

    private function neutralyzeRate(float $avgPh): array
    {
        foreach (self::NEUTRALYZE_SCHEDULE as [$phMin, $phMax, $galPerAcre]) {
            if ($avgPh >= $phMin && $avgPh <= $phMax) {
                if ($galPerAcre === 0.0) {
                    return [0.0, 'pH in optimal range (6.5–7.0) → Neutralyze not required'];
                }

                $galPer1000 = $galPerAcre * (1000.0 / self::ACRE_SQFT);
                $ozPer1000  = $galPer1000 * self::OZ_PER_GAL;

                return [
                    round($ozPer1000, 4),
                    "Neutralyze rate from pH schedule: pH {$phMin}–{$phMax} → {$galPerAcre} gal/acre",
                ];
            }
        }

        // Fallback for pH outside all ranges
        return [
            self::PRODUCTS['NEUTRALYZE']['oz_per_1000'],
            'pH outside schedule range → fallback rate used',
        ];
    }

    // -------------------------------------------------------
    // Packaging Optimizer
    // -------------------------------------------------------

    private function optimizePackaging(float $gallonsNeeded): array
    {
        $remaining = $gallonsNeeded;
        $packages  = [];

        foreach (self::PACKAGE_SIZES as $size) {
            $count = (int) floor($remaining / $size);
            if ($count > 0) {
                $packages[$size] = $count;
                $remaining      -= $size * $count;
            }
        }

        // Round up with the smallest sufficient package
        if ($remaining > 0.001) {
            foreach (array_reverse(self::PACKAGE_SIZES) as $size) {
                if ($size >= $remaining) {
                    $packages[$size] = ($packages[$size] ?? 0) + 1;
                    break;
                }
            }
        }

        $delivered = array_sum(array_map(
            fn($size, $qty) => $size * $qty,
            array_keys($packages),
            $packages
        ));

        return [
            'packages'         => $packages,
            'delivered_gallons'=> round($delivered, 2),
            'overage_gallons'  => round($delivered - $gallonsNeeded, 2),
        ];
    }

    // -------------------------------------------------------
    // Sum Add-On Totals
    // -------------------------------------------------------

    private function sumAddons(array $products): float
    {
        return array_sum(array_column(
            array_filter($products, fn($p) => $p['pricing_type'] === 'ADD-ON'),
            'total_price'
        ));
    }
}