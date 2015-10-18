<?php
namespace Minhbang\LaravelImage;

use Laracasts\Presenter\PresentableTrait;
use Minhbang\LaravelKit\Extensions\Model as BaseModel;
use Minhbang\LaravelKit\Traits\Model\DatetimeQuery;
use Minhbang\LaravelKit\Traits\Model\SearchQuery;
use Minhbang\LaravelKit\Traits\Model\TaggableTrait;
use Minhbang\LaravelUser\Support\UserQuery;

/**
 * Class ImageModel
 *
 * @package Minhbang\LaravelImage
 * @property integer $id
 * @property string $title
 * @property string $filename
 * @property integer $width
 * @property integer $height
 * @property string $mime
 * @property integer $size
 * @property integer $used
 * @property integer $user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read mixed $type
 * @property-read mixed $src
 * @property-read mixed $path
 * @property-read mixed $thumb
 * @property-read mixed $thumb_path
 * @property-read mixed $resource_name
 * @property string $tags
 * @property-read \Illuminate\Database\Eloquent\Collection|\Conner\Tagging\Tagged[] $tagged
 * @property-read \Minhbang\LaravelUser\User $user
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel whereFilename($value)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel whereWidth($value)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel whereHeight($value)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel whereMime($value)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel whereSize($value)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel whereUsed($value)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelKit\Extensions\Model except($id = null)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel related()
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel orderByMatchedTag($tagNames, $direction = 'desc')
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel withAllTags($tagNames)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel withAnyTag($tagNames)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel orderCreated($direction = 'desc')
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel orderUpdated($direction = 'desc')
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel period($start = null, $end = null, $field = 'created_at', $end_if_day = false, $is_month = false)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel today($field = 'created_at')
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel yesterday($same_time = false, $field = 'created_at')
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel thisWeek($field = 'created_at')
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel thisMonth($field = 'created_at')
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel notMine()
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel mine()
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel withAuthor()
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel searchWhere($column, $operator = '=', $fn = null)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel searchWhereIn($column, $fn)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel searchWhereBetween($column, $fn = null)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelImage\ImageModel searchWhereInDependent($column, $column_dependent, $fn, $empty = [])
 */
class ImageModel extends BaseModel
{
    use TaggableTrait;
    use PresentableTrait;
    use DatetimeQuery;
    use UserQuery;
    use SearchQuery;
    protected $presenter = \Minhbang\LaravelImage\ImageModelPresenter::class;
    protected $table = 'images';
    protected $fillable = ['title', 'filename', 'width', 'height', 'mime', 'size', 'used', 'user_id', 'tags'];

    /**
     * getter $model->type
     *
     * @return string
     */
    public function getTypeAttribute()
    {
        return $this->mime ? config("image.mime_types.{$this->mime}") : null;
    }

    /**
     * getter $model->src
     *
     * @return string
     */
    public function getSrcAttribute()
    {
        return $this->getPath('images', false);
    }

    /**
     * getter $model->path
     *
     * @return string
     */
    public function getPathAttribute()
    {
        return $this->getPath('images', true);
    }

    /**
     * getter $model->thumb
     *
     * @return string
     */
    public function getThumbAttribute()
    {
        return $this->getPath('thumbs', false);
    }

    /**
     * getter $model->thumb_path
     *
     * @return string
     */
    public function getThumbPathAttribute()
    {
        return $this->getPath('thumbs', true);
    }

    /**
     * @param string $of
     * @param bool $full
     *
     * @return null|string
     */
    protected function getPath($of, $full)
    {
        return $this->user_id ? user_public_path($of, $full, false, $this->user_id) . "/{$this->filename}" : null;
    }

    /**
     * Hook các events của model
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        // trước khi xóa $image trong DB, xóa 2 hình ảnh của nó
        static::deleting(
            function ($model) {
                /** @var static $model */
                @unlink($model->getPathAttribute());
                @unlink($model->getThumbPathAttribute());
            }
        );
    }
}