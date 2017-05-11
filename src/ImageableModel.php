<?php
namespace Minhbang\Image;

use Image;
use Minhbang\Kit\Extensions\Model;

/**
 * Class ImageableModel
 * Model sử dụng tính năng LINKED image phải thêm 'linked_image_ids' vào $fillable[]
 *
 * @package Minhbang\Image
 * @property string $linked_image_ids
 * @property-read string $linked_image_ids_original
 * @property-read \Illuminate\Database\Eloquent\Collection|\Minhbang\Image\ImageModel[] $images
 * @property-read \Illuminate\Database\Eloquent\Collection|\Minhbang\Image\ImageModel[] $contentImages
 * @property-read \Illuminate\Database\Eloquent\Collection|\Minhbang\Image\ImageModel[] $linkedImages
 * @method static \Illuminate\Database\Query\Builder|\Minhbang\Kit\Extensions\Model except($id = null)
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
     * @return string
     */
    public function getLinkedImageIdsAttribute()
    {
        return $this->getLinkedImageIdsOriginalAttribute();
    }

    /**
     * @return string
     */
    public function getLinkedImageIdsOriginalAttribute()
    {
        $this->loadLinkedImageIdsOriginal();

        return implode(',', $this->_linked_image_ids_original);
    }

    /**
     * Load linked image ids from DB
     * Chưa load: NULL, load rồi: ARRAY
     */
    public function loadLinkedImageIdsOriginal()
    {
        if (!is_array($this->_linked_image_ids_original)) {
            $this->_linked_image_ids_original = $this->exists ? $this->linkedImages()->pluck('id')->all() : [];
        }
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
        $query = $this->morphToMany('Minhbang\Image\ImageModel', 'imageable', 'imageables', 'imageable_id', 'image_id');

        return $type ? $query->wherePivot('type', '=', $type) : $query;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function contentImages()
    {
        return $this->images(static::IMAGEABLE_CONTENT);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function linkedImages()
    {
        return $this->images(static::IMAGEABLE_LINKED);
    }

    /**
     * Lấy danh sách images dạng array: id => [title => '', 'tags' => '', 'src' => 'thumb src']
     *
     * @param array $select
     * @param null|string $type
     *
     * @return array
     */
    public function arrayImages($select = [], $type = null)
    {
        $array = [];
        foreach ($this->images($type)->get() as $image) {
            /** @var \Minhbang\Image\ImageModel $image */
            $array[] = $image->arrayAttributes($select);
        }

        return $array;
    }

    /**
     * @param array $select
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function arrayContentImages($select = [])
    {
        return $this->arrayImages($select, static::IMAGEABLE_CONTENT);
    }

    /**
     * @param array $select
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function arrayLinkedImages($select = [])
    {
        return $this->arrayImages($select, static::IMAGEABLE_LINKED);
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
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->imageables())) {
            $value = image_src_code($value);
        }

        return parent::setAttribute($key, $value);
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
        static::saved(
            function ($model) {
                /** @var static $model */
                $model->onSaved();
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
        $this->loadLinkedImageIdsOriginal();
        foreach ($this->_linked_image_ids_original as $id) {
            $this->image_manager->updateUsed($id, -1);
        }

        // Xóa mọi image của model, cả CONTENT và LINKED
        // chỉ table 'imageables', không xóa trong table 'images'
        $this->images()->detach();
    }

    //TODO: Kiểm tra khi sử dụng ImageableModel cùng với Translatable, chú ý 2 hàm getAttributeRaw và getOriginal
    /**
     * Cập nhật used count cho các images của model khi SAVE (create và update)
     */
    public function onSaved()
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
                $this->contentImages()->sync(array_keys($new_imgs));
            } else {
                if ($old_imgs) {
                    $this->contentImages()->detach();
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
        // có gán giá trị mới cho 'linked_image_ids' ARRAY, chưa gán NULL
        if (is_array($this->_linked_image_ids)) {
            $this->loadLinkedImageIdsOriginal();
            /**
             * imgs có trong OLD, không có trong NEW ==> removed
             * khi $this->linked_image_ids_original = [] => $remove = []
             */
            $remove = array_diff($this->_linked_image_ids_original, $this->_linked_image_ids);
            foreach ($remove as $id) {
                $this->image_manager->updateUsed($id, -1);
            }

            /**
             * imgs có trong NEW, không có trong OLD ==> new insert
             * khi $this->linked_image_ids = [] => $insert = []
             */
            $insert = array_diff($this->_linked_image_ids, $this->_linked_image_ids_original);
            foreach ($insert as $id) {
                $this->image_manager->updateUsed($id, 1);
            }

            if ($this->_linked_image_ids) {
                $linked = [];
                foreach ($this->_linked_image_ids as $id) {
                    $linked[$id] = ['type' => static::IMAGEABLE_LINKED];
                }
                $this->linkedImages()->sync($linked);
            } else {
                if ($this->_linked_image_ids_original) {
                    $this->linkedImages()->detach();
                }
            }
        }
    }
}