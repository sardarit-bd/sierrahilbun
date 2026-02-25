<?php

namespace App\Providers;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Observers\QuestionObserver;
use App\Observers\QuestionOptionObserver;
use App\Repositories\BlogRepository;
use App\Repositories\Contracts\BlogRepositoryInterface;
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
use App\Services\Lawn\LawnSizeService;
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
