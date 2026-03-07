<?php

namespace App\Http\Controllers\Lawn;

use App\Http\Controllers\Controller;
use App\Services\Lawn\LawnPricingService;
use App\Services\Lawn\PackagingService;
use App\Services\Lawn\PlanResolverService;
use App\Services\Lawn\ProductRecommendationService;
use App\Services\Lawn\SessionFlowService;
use App\Services\Lawn\TierResolverService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

final class PlanController extends Controller
{
    public function __construct(
        private readonly SessionFlowService           $sessionFlow,
        private readonly TierResolverService          $tierResolver,
        private readonly ProductRecommendationService $recommendationService,
        private readonly PlanResolverService          $planResolver,
        private readonly PackagingService             $packagingService,
        private readonly LawnPricingService           $pricingService,
    ) {}

    public function show(): Response
    {
        $assessment = $this->sessionFlow->getAssessmentOrFail();

        if ($assessment->isCompleted() && $assessment->generated_products) {
            return $this->renderPlan($assessment);
        }

        $assessment = DB::transaction(function () use ($assessment) {
            $squareFeet    = (int) ($assessment->square_feet ?? 0);
            $quizFloorTier = $assessment->quiz_floor_tier ?? 'bronze';

            // Step 1 — Run soil engine, get recommended products
            $recommendationDTO = $this->recommendationService->getRecommendationDTO($assessment);

            if ($recommendationDTO === null) {
                Log::error('PlanController: recommendation engine returned null', [
                    'assessment_id' => $assessment->id,
                    'zip_code'      => $assessment->zip_code,
                ]);

                $assessment->update([
                    'status'       => 'completed',
                    'current_step' => 6,
                    'completed_at' => now(),
                ]);

                return $assessment->fresh();
            }

            // Step 2 — Build all 3 plans with product sets + recommended flag
            $productSlugs = $recommendationDTO->allSlugs();

            $planResult = $this->planResolver->buildPlans(
                quizFloorTier: $quizFloorTier,
                productSlugs:  $productSlugs,
            );

            // Step 3 — Build packaging for each plan's product set
            $packagingByTier = $this->buildPackagingForAllTiers(
                planResult: $planResult,
                dto:        $recommendationDTO,
                squareFeet: $squareFeet,
            );

            // Step 4 — Persist
            // PackagingResultDTO objects are JSON-serialized via toArray()
            // which produces { packaging: [...], pricing: { base_price, addons_total, total_price } }
            $assessment->update([
                'generated_products'   => $recommendationDTO->toArray(),
                'resolved_tier'        => $planResult['recommended_tier'],
                'recommended_plan_ids' => $this->extractPlanIds($planResult),
                'packaging_by_tier'    => $packagingByTier,
                'status'               => 'completed',
                'current_step'         => 6,
                'completed_at'         => now(),
            ]);

            return $assessment->fresh();
        });

        return $this->renderPlan($assessment);
    }

    // -------------------------------------------------------
    // Render
    // -------------------------------------------------------

    private function renderPlan($assessment): Response
    {
        $quizFloorTier    = $assessment->quiz_floor_tier ?? 'bronze';
        $selectedServices = $assessment->selected_services ?? ['lawn'];

        // Rebuild plan structure for render (uses cache, no extra DB hits)
        $recommendationDTO = $this->recommendationService->getRecommendationDTO($assessment);
        $productSlugs      = $recommendationDTO?->allSlugs() ?? [];

        $planResult = $this->planResolver->buildPlans(
            quizFloorTier: $quizFloorTier,
            productSlugs:  $productSlugs,
        );

        $enrichedPlans = $this->enrichPlans(
            planResult:      $planResult,
            packagingByTier: $assessment->packaging_by_tier ?? [],
        );

        return Inertia::render('yard/plan', [
            'assessment' => [
                'id'                   => $assessment->id,
                'zip_code'             => $assessment->zip_code,
                'square_feet'          => $assessment->square_feet,
                'quiz_floor_tier'      => $quizFloorTier,
                'resolved_tier'        => $assessment->resolved_tier,
                'selected_services'    => $selectedServices,
                'quiz_answers'         => $assessment->quiz_answers ?? [],
                'soil'                 => $assessment->soil_snapshot,
                'recommended_plan_ids' => $assessment->recommended_plan_ids,
                'garden_products'      => $assessment->garden_products,
                'garden_types'         => $assessment->garden_types,
                'garden_size'          => $assessment->garden_size,
            ],
            'plans'            => $enrichedPlans,
            'recommended_tier' => $planResult['recommended_tier'],
            'all_plans'        => $this->planResolver->allPlans(),
        ]);
    }

    // -------------------------------------------------------
    // Packaging builder
    // -------------------------------------------------------

    private function buildPackagingForAllTiers(
        array $planResult,
        $dto,
        int $squareFeet,
    ): array {
        $packagingByTier = [];
        $scaledPrices    = $this->pricingService->allScaledPrices($squareFeet);

        foreach ($planResult['plans'] as $tier => $planData) {
            $tierProductSlugs = $planData['products'];

            if (empty($tierProductSlugs)) {
                $packagingByTier[$tier] = [];
                continue;
            }

            $packagingByTier[$tier] = $this->packagingService->package(
                recommendation: $dto,
                squareFeet:     $squareFeet,
                tier:           $tier,
                basePrice:      $scaledPrices[$tier] ?? 179.00,
                productSlugs:   $tierProductSlugs,
            );
        }

        return $packagingByTier;
    }

    // -------------------------------------------------------
    // Plan enrichment
    // -------------------------------------------------------

    /**
     * Builds the enriched plan array for each tier.
     *
     * Each tier entry includes:
     *   - plan:           Plan model data
     *   - is_recommended: bool
     *   - is_redundant:   bool
     *   - features:       grouped product lines under their feature cards
     *   - pricing:        { base_price, addons_total, total_price } from packaging engine
     *                     This is the CORRECT price: (base × sqft multiplier) + addon retail
     *                     Use pricing.total_price for display — do NOT use plan.base_price_yearly
     */
    private function enrichPlans(array $planResult, array $packagingByTier): array
    {
        $enriched = [];

        foreach ($planResult['plans'] as $tier => $planData) {
            // PackagingResultDTO::toArray() uses camelCase keys (confirmed via dd()):
            //   lines, basePrice, totalPrice, addonsTotal
            $packaging = $packagingByTier[$tier]['lines'] ?? [];
            $pricing   = [
                'base_price'   => $packagingByTier[$tier]['basePrice']   ?? 0,
                'addons_total' => $packagingByTier[$tier]['addonsTotal'] ?? 0,
                'total_price'  => $packagingByTier[$tier]['totalPrice']  ?? 0,
            ];
            $slugs = array_column($packaging, 'slug');

            $enrichedProducts = $this->enrichPackagingLines($packaging, $slugs);
            $features         = $this->groupProductsByFeature($planData['plan']->id, $enrichedProducts);

            $enriched[$tier] = [
                'plan'           => array_merge(
                    $planData['plan']->toArray(),
                    ['is_recommended' => $planData['is_recommended']]
                ),
                'is_recommended' => $planData['is_recommended'],
                'is_redundant'   => $planData['is_redundant'] ?? false,
                'features'       => $features,

                // Correct scaled + addon-adjusted pricing from the engine.
                // Structure: { base_price: float, addons_total: float, total_price: float }
                // Frontend should use pricing.total_price for display.
                'pricing'        => $pricing,
            ];
        }

        return $enriched;
    }

    /**
     * Groups enriched products under their feature for a given plan.
     * Returns features in sort_order, each with their products nested.
     */
    private function groupProductsByFeature(int $planId, array $enrichedProducts): array
    {
        $planFeatures = DB::table('plan_feature as pf')
            ->join('features as f', 'f.id', '=', 'pf.feature_id')
            ->where('pf.plan_id', $planId)
            ->orderBy('pf.sort_order')
            ->select('f.id', 'f.title', 'f.subtitle', 'f.image_url', 'pf.sort_order')
            ->get();

        if ($planFeatures->isEmpty()) {
            return [];
        }

        $featureIds = $planFeatures->pluck('id')->all();

        $featureProductRows = DB::table('feature_product')
            ->whereIn('feature_id', $featureIds)
            ->get()
            ->groupBy('feature_id');

        $productsBySlug = collect($enrichedProducts)->keyBy('slug');

        $features = [];

        foreach ($planFeatures as $feature) {
            $featureSlugRows = $featureProductRows->get($feature->id, collect());

            $featureProducts = [];
            foreach ($featureSlugRows as $row) {
                $product = $productsBySlug->get($row->product_sku);
                if ($product) {
                    $featureProducts[] = $product;
                }
            }

            if (empty($featureProducts)) {
                continue;
            }

            $features[] = [
                'id'       => $feature->id,
                'title'    => $feature->title,
                'subtitle' => $feature->subtitle,
                'image_url' => $feature->image_url
                    ? '/storage/' . ltrim(json_decode($feature->image_url) ?? $feature->image_url, '/')
                    : null,
                'products' => $featureProducts,
            ];
        }

        return $features;
    }

    private function enrichPackagingLines(array $packaging, array $slugs): array
    {
        if (empty($slugs)) {
            return [];
        }

        $products = DB::table('products')
            ->whereIn('slug', $slugs)
            ->select('slug', 'name', 'subtitle', 'description', 'benefits', 'usage_instructions')
            ->get()
            ->keyBy('slug');

        $images = DB::table('product_images as pi')
            ->join('products as p', 'p.id', '=', 'pi.product_id')
            ->whereIn('p.slug', $slugs)
            ->select('p.slug', 'pi.image_url', 'pi.is_primary', 'pi.sort_order')
            ->orderBy('p.slug')
            ->orderBy('pi.sort_order')
            ->get()
            ->groupBy('slug');

        return array_map(function (array $line) use ($products, $images) {
            $slug    = $line['slug'];
            $product = $products->get($slug);
            $imgs    = $images->get($slug, collect());

            $benefits = null;
            if ($product?->benefits) {
                $decoded  = json_decode($product->benefits, true);
                $benefits = is_array($decoded) ? $decoded : null;
            }

            return array_merge($line, [
                'subtitle'           => $product?->subtitle,
                'description'        => $product?->description,
                'benefits'           => $benefits,
                'usage_instructions' => $product?->usage_instructions,
                'images'             => $imgs->map(
                    fn($img) => $this->resolveImageUrl($img->image_url)
                )->values()->all(),
                'primary_image'      => $imgs->firstWhere('is_primary', 1)?->image_url
                    ? $this->resolveImageUrl($imgs->firstWhere('is_primary', 1)->image_url)
                    : null,
            ]);
        }, $packaging);
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    private function extractPlanIds(array $planResult): array
    {
        return collect($planResult['plans'])
            ->map(fn(array $planData) => $planData['plan']->id)
            ->values()
            ->all();
    }

    private function resolveImageUrl(string $path): string
    {
        return str_starts_with($path, 'http') ? $path : '/storage/' . ltrim($path, '/');
    }
}