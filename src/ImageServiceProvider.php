<?php

namespace Minhbang\LaravelImage;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

/**
 * Class ImageServiceProvider
 *
 * @package Minhbang\LaravelImage
 */
class ImageServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @param \Illuminate\Routing\Router $router
     * @return void
     */
    public function boot(Router $router)
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'image');
        $this->loadViewsFrom(__DIR__ . '/../views', 'image');
        $this->publishes(
            [
                __DIR__ . '/../views'                       => base_path('resources/views/vendor/image'),
                __DIR__ . '/../config/image.php'            => config_path('image.php'),
                __DIR__ . '/../lang'                        => base_path('resources/lang/vendor/image'),
                __DIR__ . '/../database/migrations/' .
                '2015_09_21_020347_create_images_table.php' =>
                    database_path('migrations/' . '2015_09_21_020347_create_images_table.php'),
            ]
        );

        if (config('image.add_route') && !$this->app->routesAreCached()) {
            require __DIR__ . '/routes.php';
        }

        // pattern filters
        $router->pattern('image', '[0-9]+');
        // model bindings
        $router->model('image', 'Minhbang\LaravelImage\ImageModel');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/image.php', 'image');
        $this->app['image'] = $this->app->share(
            function ($app) {
                return new Image(
                    ['driver' => config('image.driver')],
                    config('image.resources')
                );
            }
        );
        // add Setting alias
        $this->app->booting(
            function ($app) {
                AliasLoader::getInstance()->alias('Image', ImageFacade::class);
            }
        );
    }
}