<?php
namespace Minhbang\Image;

use Laracasts\Presenter\PresentableTrait;
use Minhbang\Kit\Extensions\Model;
use Minhbang\Kit\Traits\Model\DatetimeQuery;
use Minhbang\Kit\Traits\Model\SearchQuery;
use Minhbang\User\Support\HasOwner;
use Minhbang\Tag\Taggable;

/**
 * Class ImageModel
 *
 * @package Minhbang\Image
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
 * @property-read string $src
 * @property-read string $path
 * @property-read string $thumb
 * @property-read string $thumb_path
 * @property-read string $thumb_4x
 * @property-read string $thumb_4x_path
 * @property-read string $resource_name
 * @property-read \Minhbang\User\User $user
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel whereFilename($value)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel whereWidth($value)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel whereHeight($value)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel whereMime($value)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel whereSize($value)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel whereUsed($value)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Kit\Extensions\Model except($id = null)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel related()
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel orderByMatchedTag($tagNames, $direction = 'desc')
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel withAllTags($tagNames)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel withAnyTag($tagNames)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel orderCreated($direction = 'desc')
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel orderUpdated($direction = 'desc')
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel period($start = null, $end = null, $field = 'created_at', $end_if_day = false, $is_month = false)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel today($field = 'created_at')
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel yesterday($same_time = false, $field = 'created_at')
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel thisWeek($field = 'created_at')
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel thisMonth($field = 'created_at')
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel notMine()
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel mine()
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel withAuthor()
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel searchWhere($column, $operator = '=', $fn = null)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel searchWhereIn($column, $fn)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel searchWhereBetween($column, $fn = null)
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Image\ImageModel searchWhereInDependent($column, $column_dependent, $fn, $empty = [])
 */
class ImageModel extends Model
{
    use Taggable;
    use PresentableTrait;
    use DatetimeQuery;
    use HasOwner;
    use SearchQuery;

    protected $presenter = Presenter::class;
    protected $table = 'images';
    protected $fillable = ['title', 'filename', 'width', 'height', 'mime', 'size', 'used', 'user_id', 'tag_names'];

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
     * getter $model->thumb_4x
     *
     * @return string
     */
    public function getThumb4xAttribute()
    {
        return $this->getPath('thumbs-4x', false);
    }

    /**
     * getter $model->thumb_4x_path
     *
     * @return string
     */
    public function getThumb4xPathAttribute()
    {
        return $this->getPath('thumbs-4x', true);
    }

    /**
     * Lấy một số attributes, trả về dạng array
     * vd: $select = ['id', 'tag' => 'tag_names']
     * trả về ['id' => $image->id, 'tag' => $images->tag_names]
     *
     * @param array $select
     *
     * @return array
     */
    public function arrayAttributes($select = [])
    {
        $array = [];
        foreach ($select as $key => $attr) {
            if (is_numeric($key)) {
                $array[$attr] = $this->$attr;
            } else {
                $array[$key] = $this->$attr;
            }
        }

        return $array;
    }

    /**
     * @param string $of
     * @param bool $full
     *
     * @return null|string
     */
    protected function getPath($of, $full)
    {
        if ($user = $this->user) {
            return $user->upload_path($of, $full) . '/' . $this->filename;
        } else {
            return null;
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
        // trước khi xóa $image trong DB, xóa 2 hình ảnh của nó
        static::deleting(
            function (ImageModel $model) {
                @unlink($model->getPathAttribute());
                @unlink($model->getThumbPathAttribute());
                @unlink($model->getThumb4xPathAttribute());
            }
        );
    }
}