<?php

namespace Minhbang\Image;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Foundation\AliasLoader;
use MenuManager;
use Illuminate\Database\Eloquent\Relations\Relation;
use Authority;
use Kit;

/**
 * Class ServiceProvider
 *
 * @package Minhbang\Image
 */
class ServiceProvider extends BaseServiceProvider {
    /**
     * Perform post-registration booting of services.
     *
     * @param \Illuminate\Routing\Router $router
     *
     * @return void
     */
    public function boot( Router $router ) {
        $this->loadTranslationsFrom( __DIR__ . '/../lang', 'image' );
        $this->loadViewsFrom( __DIR__ . '/../views', 'image' );
        $this->loadMigrationsFrom( __DIR__ . '/../database/migrations' );
        $this->loadRoutesFrom( __DIR__ . '/routes.php' );

        $this->publishes(
            [
                __DIR__ . '/../views'            => base_path( 'resources/views/vendor/image' ),
                __DIR__ . '/../config/image.php' => config_path( 'image.php' ),
                __DIR__ . '/../lang'             => base_path( 'resources/lang/vendor/image' ),
            ]
        );

        // pattern filters
        $router->pattern( 'image', '[0-9]+' );
        // model bindings
        $router->model( 'image', ImageModel::class );

        Kit::alias( ImageModel::class, 'image' );
        Kit::title( ImageModel::class, trans( 'image::common.images' ) );

        // Add image menus
        MenuManager::addItems( config( 'image.menus' ) );


        // Đăng ký các CRUD actions cho hình ảnh
        Authority::permission()->registerCRUD( ImageModel::class );
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register() {
        $this->mergeConfigFrom( __DIR__ . '/../config/image.php', 'image' );
        $this->app->singleton( 'image', function () {
            return new Image(
                [ 'driver' => config( 'image.driver' ) ]
            );
        } );
        // add Setting alias
        $this->app->booting(
            function () {
                AliasLoader::getInstance()->alias( 'Image', Facade::class );
            }
        );
    }
}