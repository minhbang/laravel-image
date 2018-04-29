<?php

namespace Minhbang\Image;

use Authority;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Kit;
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
        //$this->loadTranslationsFrom(__DIR__.'/../lang', 'image');
        $this->loadViewsFrom(__DIR__.'/../views', 'image');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes(
            [
                __DIR__.'/../views' => base_path('resources/views/vendor/image'),
                __DIR__.'/../config/image.php' => config_path('image.php'),
                //__DIR__.'/../lang' => base_path('resources/lang/vendor/image'),
            ]
        );

        // pattern filters
        $router->pattern('image', '[0-9]+');
        // model bindings
        $router->model('image', Image::class);
        $this->loadRoutesFrom(__DIR__.'/routes.php');

        Kit::alias(Image::class, 'image');
        Kit::title(Image::class, __('Images'));

        // Add image menus
        MenuManager::addItems(config('image.menus'));

        // Đăng ký các CRUD actions cho hình ảnh
        Authority::permission()->registerCRUD(Image::class);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/image.php', 'image');
        $this->app->singleton('image-factory', function () {
            return new ImageFactory(
                ['driver' => config('image.driver')]
            );
        });
        // add Setting alias
        $this->app->booting(
            function () {
                AliasLoader::getInstance()->alias('ImageFactory', Facade::class);
            }
        );
    }

    public function provides()
    {
        return ['image-factory'];
    }
}