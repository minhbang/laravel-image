<?php
namespace Minhbang\LaravelImage;

use Image;
use Minhbang\LaravelKit\Extensions\Model;

/**
 * Class ImageableModel
 * Model sử dụng tính năng LINKED image phải thêm 'linked_image_ids' vào $fillable[]
 *
 * @package Minhbang\LaravelImage
 * @property array $linked_image_ids
 * @property-read mixed $linked_image_ids_original
 * @property-read \Illuminate\Database\Eloquent\Collection|\Minhbang\LaravelImage\ImageModel[] $images
 * @property-read \Illuminate\Database\Eloquent\Collection|\Minhbang\LaravelImage\ImageModel[] $content_images
 * @property-read \Illuminate\Database\Eloquent\Collection|\Minhbang\LaravelImage\ImageModel[] $linked_images
 * @property-read mixed $resource_name
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\LaravelKit\Extensions\Model except($id = null)
 */
abstract class ImageableModel extends Model
{
    /**
     * Images trong model CONTENT, vd: chèn hình vào nội dung bài viết
     */
    const IMAGEABLE_CONTENT = 1;
    /**
     * Images LINKED với model, tách rời nội dung, vd: hình ảnh của 1 Album
     */
    const IMAGEABLE_LINKED = 2;

    /**
     * Danh sách LINKED images mới gán, [id1,id2...]
     *
     * @var array
     */
    protected $_linked_image_ids;

    /**
     * Danh sách LINKED images lấy từ DB
     *
     * @var array
     */
    protected $_linked_image_ids_original;

    /**
     * @var \Image;
     */
    protected $image_manager;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->image_manager = app('image');
    }


    /**
     * @param string $value Danh sách images id, vd: id1,id2,id3
     */
    public function setLinkedImageIdsAttribute($value)
    {
        $value = str_replace(' ', '', trim($value, ','));
        $this->_linked_image_ids = preg_match('/^[0-9]+(,[0-9]+)*$/', $value) ? explode(',', $value) : [];
    }

    /**
     * @return array
     */
    public function getLinkedImageIdsAttribute()
    {
        return $this->_linked_image_ids ?: [];
    }

    /**
     * @return array
     */
    public function getLinkedImageIdsOriginalAttribute()
    {
        if (!is_array($this->_linked_image_ids_original)) {
            $this->_linked_image_ids_original = $this->exists ? $this->link_images()->lists('id')->all() : [];
        }
        return $this->_linked_image_ids_original;
    }

    /**
     * List attributes có thể insert images
     * vd: attr content của article
     *
     * @return array
     */
    abstract public function imageables();

    /**
     * Tất cả images của model, hoặc chỉ CONTENT hay LINK
     *
     * @param null|int $type
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function images($type = null)
    {
        $query = $this->morphToMany('Minhbang\LaravelImage\ImageModel', 'imageable', 'imageables', 'imageable_id', 'image_id');
        return $type ? $query->wherePivot('type', '=', $type) : $query;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function content_images()
    {
        return $this->images(static::IMAGEABLE_CONTENT);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function linked_images()
    {
        return $this->images(static::IMAGEABLE_LINKED);
    }

    /**
     * Get a plain attribute (not a relationship).
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getAttributeValue($key)
    {
        $value = parent::getAttributeValue($key);
        return in_array($key, $this->imageables()) ? image_src_decode($value) : $value;
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
        if (in_array($key, $this->imageables())) {
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
                $model->onDeleting();
            }
        );
        // trước khi save $model, cả create hay update
        static::saving(
            function ($model) {
                /** @var static $model */
                $model->onSaving();
            }
        );
    }

    /**
     * Cập nhật used count cho các images của model khi DELETE
     */
    public function onDeleting()
    {
        // Cập nhật used count của các CONTENT image
        if ($attributes = $this->imageables()) {
            $imgs = [];
            foreach ($attributes as $attribute) {
                $this->image_manager->imageIds($this->getOriginal($attribute), $imgs);
            }
            if ($imgs) {
                foreach ($imgs as $id => $count) {
                    $this->image_manager->updateUsed($id, -$count);
                }
            }
        }

        // Cập nhật used count của các LINKED image
        foreach ($this->linked_image_ids_original as $id) {
            $this->image_manager->updateUsed($id, -1);
        }

        // Xóa mọi image của model, cả CONTENT và LINKED
        // chỉ table 'imageables', không xóa trong table 'images'
        $this->images()->detach();
    }

    /**
     * Cập nhật used count cho các images của model khi SAVE (create và update)
     */
    public function onSaving()
    {
        // Cập nhật các CONTENT image
        if ($attributes = $this->imageables()) {
            $old_imgs = [];
            $new_imgs = [];
            foreach ($attributes as $attribute) {
                $this->image_manager->imageIds($this->getOriginal($attribute), $old_imgs);
                $this->image_manager->imageIds($this->getAttributeRaw($attribute), $new_imgs);
            }
            if ($new_imgs) {
                // 'type' default = 1 = IMAGEABLE_CONTENT
                $this->content_images()->sync(array_keys($new_imgs));
            } else {
                if ($old_imgs) {
                    $this->content_images()->detach();
                }
            }
            /**
             * imgs có trong OLD, không có trong NEW ==> removed
             * khi create $old_imgs = [] => $remove = []
             */
            $remove = array_diff_key($old_imgs, $new_imgs);
            foreach ($remove as $id => $count) {
                $this->image_manager->updateUsed($id, -$count);
            }

            /**
             * imgs có trong NEW, không có trong OLD ==> new insert
             */
            $insert = array_diff_key($new_imgs, $old_imgs);
            foreach ($insert as $id => $count) {
                $this->image_manager->updateUsed($id, $count);
            }
            /**
             * imgs đồng thời có trong NEW và OLD ==> thay đổi số lượng
             * khi create $old_imgs = [] => $same = []
             */
            $same = array_intersect_key($old_imgs, $new_imgs);
            foreach ($same as $id => $count) {
                $this->image_manager->updateUsed($id, $new_imgs[$id] - $old_imgs[$id]);
            }
        }

        /**
         * Cập nhật các LINKED image
         */
        // có gán giá trị mới cho 'linked_image_ids'
        if (is_array($this->_linked_image_ids)) {
            /**
             * imgs có trong OLD, không có trong NEW ==> removed
             * khi $this->linked_image_ids_original = [] => $remove = []
             */
            $remove = array_diff_key($this->linked_image_ids_original, $this->linked_image_ids);
            foreach ($remove as $id => $count) {
                $this->image_manager->updateUsed($id, -1);
            }

            /**
             * imgs có trong NEW, không có trong OLD ==> new insert
             * khi $this->linked_image_ids = [] => $insert = []
             */
            $insert = array_diff_key($this->linked_image_ids, $this->linked_image_ids_original);
            foreach ($insert as $id => $count) {
                $this->image_manager->updateUsed($id, 1);
            }

            if ($this->linked_image_ids) {
                $linked = [];
                foreach ($this->linked_image_ids as $id) {
                    $linked[$id] = ['type' => static::IMAGEABLE_LINKED];
                }
                $this->linked_images()->sync($linked);
            } else {
                if ($this->linked_image_ids_original) {
                    $this->linked_images()->detach();
                }
            }
        }
    }
}