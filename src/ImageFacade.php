<?php
namespace Minhbang\LaravelImage;

use Illuminate\Support\Facades\Facade;

class ImageFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'image';
    }
}