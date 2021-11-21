<?php

namespace RecursiveTree\Seat\InfoPlugin;

use Seat\Services\AbstractSeatPlugin;


class InfoServiceProvider extends AbstractSeatPlugin
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(){

        $this->publishes([
            __DIR__ . '/resources/js' => public_path('info/js')
        ]);

        if (! $this->app->routesAreCached()) {
            include __DIR__ . '/Http/routes.php';
        }
        $this->loadTranslationsFrom(__DIR__ . '/lang', 'info');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'info');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations/');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(){
        $this->mergeConfigFrom(__DIR__ . '/Config/info.sidebar.php','package.sidebar');
    }


    /**
     * Return the plugin public name as it should be displayed into settings.
     *
     * @example SeAT Web
     *
     * @return string
     */
    public function getName(): string
    {
        return 'SeAT Info';
    }


    /**
     * Return the plugin repository address.
     *
     * @example https://github.com/eveseat/web
     *
     * @return string
     */
    public function getPackageRepositoryUrl(): string
    {
        return 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
    }

    /**
     * Return the plugin technical name as published on package manager.
     *
     * @example web
     *
     * @return string
     */
    public function getPackagistPackageName(): string
    {
        return 'seat-info';
    }

    /**
     * Return the plugin vendor tag as published on package manager.
     *
     * @example eveseat
     *
     * @return string
     */
    public function getPackagistVendorName(): string
    {
        return 'recursive_tree';
    }
}