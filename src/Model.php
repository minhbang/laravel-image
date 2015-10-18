<?php
namespace Minhbang\LaravelImage;

use Minhbang\LaravelKit\Extensions\Model as BaseModel;
use Image;
/**
 * Class Model
 *
 * @package Minhbang\LaravelImage
 * @property-read mixed $resource_name
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelKit\Extensions\Model except($id = null)
 */
class Model extends BaseModel
{
    /**
     * @var array các attributes có thể insert image
     */
    public $has_images = [];

    /**
     * Get a plain attribute (not a relationship).
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function getAttributeValue($key)
    {
        $value = parent::getAttributeValue($key);
        return in_array($key, $this->has_images) ? image_src_decode($value) : $value;
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string $key
     * @param  mixed $value
     *
     * @return void
     */
    public function setAttribute($key, $value)
    {
        parent::setAttribute($key, $value);
        if (in_array($key, $this->has_images)) {
            $this->attributes[$key] = image_src_code($value);
        }
    }

    /**
     * Hook các events của model
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        // trước khi xóa $model
        static::deleting(
            function ($model) {
                /** @var static $model */
                Image::updateDB($model, 'deleting');
            }
        );
        // trước khi save $model, cả create hay update
        static::saving(
            function ($model) {
                /** @var static $model */
                Image::updateDB($model, 'saving');
            }
        );
    }
}
