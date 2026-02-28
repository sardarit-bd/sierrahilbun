<?php

namespace App\Providers;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Observers\QuestionObserver;
use App\Observers\QuestionOptionObserver;
use App\Repositories\BlogRepository;
use App\Repositories\Contracts\BlogRepositoryInterface;
use App\Repositories\PackagingRepository;
use App\Repositories\RecommendationRepository;
use App\Services\Area\LawnAreaCalculator;
use App\Services\Area\MapboxBuildingDetector;
use App\Services\Area\OverpassLotDetector;
use App\Services\Checkout\CheckoutService;
use App\Services\Config\DatabaseApiConfig;
use App\Services\Contracts\ApiConfigInterface;
use App\Services\Contracts\BuildingDetectorInterface;
use App\Services\Contracts\GeocoderInterface;
use App\Services\Contracts\LotDetectorInterface;
use App\Services\Geocoding\MapboxGeocoder;
use App\Services\Lawn\CoreRatioCalculator;
use App\Services\Lawn\LawnSizeService;
use App\Services\Lawn\ModifierResolver;
use App\Services\Lawn\Modifiers\AerateModifierRule;
use App\Services\Lawn\Modifiers\FallSupportModifierRule;
use App\Services\Lawn\Modifiers\GreenGroModifierRule;
use App\Services\Lawn\Modifiers\HeatGuardModifierRule;
use App\Services\Lawn\Modifiers\KBoostModifierRule;
use App\Services\Lawn\Modifiers\MicrobeBoostModifierRule;
use App\Services\Lawn\Modifiers\PatchProModifierRule;
use App\Services\Lawn\Modifiers\PetSpotRepairModifierRule;
use App\Services\Lawn\Modifiers\SulfaCoreModifierRule;
use App\Services\Lawn\PackagingService;
use App\Services\Lawn\ProductRecommendationEngine;
use App\Services\Lawn\ProductRecommendationService;
use App\Services\Lawn\SoilInputHydrator;
use App\Services\Lawn\TierInclusionMap;
use App\Services\Lawn\VariantSelectorService;
use App\Services\Order\OrderService;
use App\Services\Payment\Contracts\WebhookHandlerInterface;
use App\Services\Payment\Factory\PaymentGatewayFactory;
use App\Services\Payment\PaymentService;
use App\Services\Payment\Webhooks\StripeWebhookHandler;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentGatewayFactory::class);
        $this->app->singleton(PaymentService::class);
        $this->app->bind(WebhookHandlerInterface::class, StripeWebhookHandler::class);
        $this->app->singleton(CheckoutService::class);

        $this->app->bind(
            BlogRepositoryInterface::class,
            BlogRepository::class
        );

        $this->app->singleton(
            ApiConfigInterface::class,
            DatabaseApiConfig::class,
        );

        $this->app->singleton(
            GeocoderInterface::class,
            MapboxGeocoder::class,
        );

        $this->app->singleton(
            BuildingDetectorInterface::class,
            MapboxBuildingDetector::class,
        );

        $this->app->singleton(
            LotDetectorInterface::class,
            OverpassLotDetector::class,
        );

        $this->app->singleton(LawnAreaCalculator::class);

        $this->app->singleton(LawnSizeService::class);

        $this->app->singleton(OrderService::class);

        $this->app->bind(ModifierResolver::class, function () {
            return new ModifierResolver([
                new PatchProModifierRule(),
                new AerateModifierRule(),
                new HeatGuardModifierRule(),
                new MicrobeBoostModifierRule(),
                new PetSpotRepairModifierRule(),
                new SulfaCoreModifierRule(),
                new KBoostModifierRule(),
                new GreenGroModifierRule(),
                new FallSupportModifierRule(),
            ]);
        });

        $this->app->bind(ProductRecommendationEngine::class, function ($app) {
            return new ProductRecommendationEngine(
                coreCalculator:   $app->make(CoreRatioCalculator::class),
                modifierResolver: $app->make(ModifierResolver::class),
            );
        });

        $this->app->bind(ProductRecommendationService::class, function ($app) {
            return new ProductRecommendationService(
                hydrator:    $app->make(SoilInputHydrator::class),
                engine:      $app->make(ProductRecommendationEngine::class),
                repository:  $app->make(RecommendationRepository::class),
            );
        });

        $this->app->bind(VariantSelectorService::class, fn () => new VariantSelectorService());

        $this->app->bind(TierInclusionMap::class, fn () => new TierInclusionMap());

        $this->app->bind(PackagingService::class, function ($app) {
            return new PackagingService(
                variantSelector:  $app->make(VariantSelectorService::class),
                tierInclusionMap: $app->make(TierInclusionMap::class),
            );
        });

        $this->app->bind(PackagingRepository::class, fn () => new PackagingRepository());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        
        Question::observe(QuestionObserver::class);
        QuestionOption::observe(QuestionOptionObserver::class);
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }
}
