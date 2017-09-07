<?php namespace Minhbang\Image\Support;

use Minhbang\Image\Image;

/**
 * Dùng cho Model có nhiều images (vd: Gallery... )
 * Trait Imageable
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\Minhbang\Image\Image[] $images
 * @package Minhbang\Image\Support
 * @mixin \Eloquent
 */
trait Imageable
{
    public static function bootImageable()
    {
        static::deleting(
            function ($model) {
                /** @var \Minhbang\Image\Support\Imageable|static $model */
                $model->images()->detach();
            }
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function images()
    {
        return $this->morphToMany(Image::class, 'imageable')->orderBy('imageables.position');
    }

    /**
     * @return \Minhbang\Image\Image
     */
    public function firstImage()
    {
        return $this->images->first();
    }
}