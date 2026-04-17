<?php

namespace App\Providers;

use App\Services\FcmService;
use App\Services\PushNotificationService;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(FcmService::class, fn () => new FcmService);
        $this->app->singleton(PushNotificationService::class, fn ($app) => new PushNotificationService(
            $app->make(FcmService::class),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        TextInput::configureUsing(function (TextInput $component): void {
            if (app()->getLocale() !== 'ar') {
                return;
            }

            if (in_array($component->getType(), ['email', 'tel', 'url', 'number', 'password'], true)) {
                return;
            }

            $component->extraInputAttributes(['dir' => 'rtl'], merge: true);
        });

        Textarea::configureUsing(function (Textarea $component): void {
            if (app()->getLocale() !== 'ar') {
                return;
            }

            $component->extraInputAttributes(['dir' => 'rtl'], merge: true);
        });
    }
}
