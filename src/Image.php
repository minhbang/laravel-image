<?php
namespace Minhbang\LaravelImage;

use Validator;
use Intervention\Image\ImageManager;

/**
 * Class Image
 *
 * @package Minhbang\LaravelImage
 */
class Image extends ImageManager
{
    /**
     * @var string images table name
     */
    protected $table = 'images';

    public function __construct(array $config, $table = null)
    {
        parent::__construct($config);
        $this->table = $table ?: $this->table;
    }

    /**
     * Get image model từ img $src
     *
     * @param string $src
     *
     * @return \Minhbang\LaravelImage\ImageModel|null
     */
    public function getModel($src)
    {
        $model = null;
        $file = realpath(public_path(trim($src, '/')));
        $public_path = public_path(setting('system.public_files'));
        if ($file && (strpos($file, $public_path) === 0)) {
            $file = str_replace("$public_path/", '', $file);
            // $file còn lại <user_code>/images/<name>.<ext>
            if (preg_match('/^([a-z0-9]+)\/images\/([a-z0-9]+)\.([a-z0-9]+)$/', $file, $matches)) {
                $filename = "{$matches[2]}.{$matches[3]}";
                // Todo: nếu filename trùng nhau trong DB thì sao? có thể unique filename
                $model = ImageModel::findBy('filename', $filename);
            }
        }
        return $model;
    }
    /**
     * Kiểm tra có thể xóa $file hình ảnh
     * Điều kiện:
     * - file thuộc thư mục <upload public>
     * - Là admin hoặc file của mình (thuộc thư mục của user) !SECURITY tránh xóa file không được phép
     * - có thông tin trong CSDL (image model)
     * - không sử dụng trong các resource (used <= 0)
     *
     * @param string $src
     *
     * @return bool
     */
    public function destroy($src)
    {
        if (!($image = $this->getModel($src))) {
            return response()->json(['success' => 'invalid request']);
        }
        if ($image->used <= 0) {
            $image->delete();
        } else {
            return response()->json(['success' => 'image in used']);
        }
        return response()->json(['success' => 'deleted']);
    }

    /**
     * Xử lý file upload, error: trả về string
     *
     * @param \Illuminate\Http\Request $request
     * @param string $attribute
     * @param int|null $user_id User hieejn tại hay $user_id
     *
     * @return \Intervention\Image\Image|string
     */
    public function store($request, $attribute = 'file', $user_id = null)
    {
        //validate
        $rules = [$attribute => 'required|max:' . setting('system.max_image_size') * 1024];//kilobytes
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $validator->messages()->first($attribute);
        }

        // prepare
        $file = $request->file($attribute);
        $mime = $file->getMimeType();
        $ext = config("image.mime_types.{$mime}");
        if (!$ext) {
            return trans('errors.mime_type_not_allwed');
        }
        $filename = xuuid() . '.' . $ext;
        $image = $this->make($file->getRealPath());

        // save thumbnail
        $thumb = clone $image;
        $path = user_public_path('thumbs', true, false, $user_id) . "/$filename";
        $thumb->fit(config('image.thumbnail.width'), config('image.thumbnail.height'))->save($path);
        $thumb->destroy();

        // save thumbnail 4x
        $thumb_4x = clone $image;
        $path_4x = user_public_path('thumbs-4x', true, false, $user_id) . "/$filename";
        $thumb_4x->fit(config('image.thumbnail.width')*4, config('image.thumbnail.height')*4)->save($path_4x);
        $thumb_4x->destroy();

        // save image
        $path = user_public_path('images', true, false, $user_id) . "/$filename";
        $max_width = setting('display.image_width_max');
        if ($image->width() > $max_width) {
            $image = $image->widen($max_width);
            // insert vào nền trắng(#ffffff), tránh nền đen khi widen() ảnh trong suốt...
            $this->canvas($image->width(), $image->height(), '#ffffff')->insert($image)->save($path);
        } else {
            $image->save($path);
        }
        return [$image, $filename, $mime];
    }

    /**
     * Chuyển image src thành src coded: #!!img:id!!
     *
     * @param string $html
     * @param null $count
     *
     * @return string
     */
    public function srcCode($html, &$count = null)
    {
        $count = 0;
        $search = [];
        $replace = [];
        list($imgs, $srcs) = $this->parser($html);
        if ($imgs) {
            foreach ($srcs as $i => $src) {
                if ($image = $this->getModel($src)) {
                    $search[] = $imgs[$i];
                    $replace[] = str_replace($src, "#!!img:{$image->id}!!", $imgs[$i]);
                    $count++;
                }
            }
        }
        return $search ? str_replace($search, $replace, $html) : $html;
    }

    /**
     * Chuyển image src coded thành src
     *
     * @param string $html
     *
     * @return string
     */
    public function srcDecode($html)
    {
        $search = [];
        $replace = [];
        list($imgs, $srcs) = $this->parser($html);
        if ($imgs) {
            foreach ($srcs as $i => $src) {
                if ($id = $this->getId($src)) {
                    if ($image = ImageModel::find($id)) {
                        /** @var \Minhbang\LaravelImage\ImageModel $image */
                        $search[] = $imgs[$i];
                        $replace[] = str_replace($src, $image->src, $imgs[$i]);
                    }
                }
            }
        }
        return $search ? str_replace($search, $replace, $html) : $html;
    }

    /**
     * Lấy danh sách image ids từ $html đã code
     * Định dạng: 'image id' => count
     *
     * @param string $html
     * @param array $ids
     */
    public function imageIds($html, &$ids)
    {
        if ($html) {
            list(, $srcs) = $this->parser($html);
            foreach ($srcs as $src) {
                if ($id = $this->getId($src)) {
                    if (isset($ids[$id])) {
                        $ids[$id]++;
                    } else {
                        $ids[$id] = 1;
                    }
                }
            }
        }
    }

    /**
     * @param int $id
     * @param integer $amount
     */
    public function updateUsed($id, $amount)
    {
        if ($amount !== 0 && $image = ImageModel::find($id)) {
            /** @var \Minhbang\LaravelImage\ImageModel $image */
            $image->used += $amount;
            if ($image->used > 0) {
                $image->timestamps = false;
                $image->save();
            } else {
                $image->delete();
            }
        }
    }

    /**
     * Lấy image id từ src đã code
     *
     * @param string $src_code
     *
     * @return int
     */
    public function getId($src_code)
    {
        if (preg_match('/^\#\!\!img:([\d]+)\!\!$/', $src_code, $matches)) {
            // image id = $matches[1] tương ứng regex ([\d]+)
            return $matches[1];
        } else {
            return null;
        }
    }

    /**
     * Get img tag in html
     *
     * @param string $html
     *
     * @return array [$img_tags, $img_srcs]
     */
    public function parser($html)
    {
        /**
         * $result[0]: array toàn bộ img tag
         * $result[1]: array dấu " hoặc ', tương ứng regex (["\'])
         * $result[2]: array thuộc tính src, tương ứng regex (.*?)
         * Nếu không tìm thấy img: $result[0] = $result[1] = $result[2] = array()
         */
        preg_match_all('/<img\s+[^>]*src=(["\'])(.*?)\1[^\>]*>/im', $html, $result);
        return [$result[0], $result[2]];
    }
}