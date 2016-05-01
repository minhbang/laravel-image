<?php

namespace Minhbang\Image;

use Illuminate\Routing\Router;
use Minhbang\Kit\Extensions\BaseServiceProvider;
use Illuminate\Foundation\AliasLoader;
use MenuManager;

/**
 * Class ServiceProvider
 *
 * @package Minhbang\Image
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @param \Illuminate\Routing\Router $router
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'image');
        $this->loadViewsFrom(__DIR__ . '/../views', 'image');
        $this->publishes(
            [
                __DIR__ . '/../views'            => base_path('resources/views/vendor/image'),
                __DIR__ . '/../config/image.php' => config_path('image.php'),
                __DIR__ . '/../lang'             => base_path('resources/lang/vendor/image'),
            ]
        );
        $this->publishes(
            [
                __DIR__ . '/../database/migrations/2015_09_21_020347_create_images_table.php'     =>
                    database_path('migrations/2015_09_21_020347_create_images_table.php'),
                __DIR__ . '/../database/migrations/2015_09_21_030347_create_imageables_table.php' =>
                    database_path('migrations/2015_09_21_030347_create_imageables_table.php'),
            ],
            'db'
        );

        $this->mapWebRoutes($router, __DIR__ . '/routes.php', config('image.add_route'));

        // pattern filters
        $router->pattern('image', '[0-9]+');
        // model bindings
        $router->model('image', 'Minhbang\Image\ImageModel');

        // Add image menus
        MenuManager::addItems(config('image.menus'));
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
            function () {
                return new Image(
                    ['driver' => config('image.driver')]
                );
            }
        );
        // add Setting alias
        $this->app->booting(
            function () {
                AliasLoader::getInstance()->alias('Image', Facade::class);
            }
        );
    }
}