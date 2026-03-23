<?php

namespace App\Providers;

use App\Services\FaceVerification\PythonFaceVerificationClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PythonFaceVerificationClient::class, function () {
            return new PythonFaceVerificationClient(
                config('face.service_url'),
                config('face.service_secret'),
                (int) config('face.timeout'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
