<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\EmailNotificationService;
use App\Contracts\EmailNotificationServiceInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind interface to concrete implementation
        $this->app->bind(EmailNotificationServiceInterface::class, EmailNotificationService::class);

        // Register EmailNotificationService as singleton for better performance
        $this->app->singleton(EmailNotificationService::class, function ($app) {
            return new EmailNotificationService();
        });

        // Also register with alias for convenience
        $this->app->alias(EmailNotificationService::class, 'email.notification');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
