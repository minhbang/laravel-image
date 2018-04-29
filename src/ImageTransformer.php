<?php namespace Minhbang\Image;

use Minhbang\Kit\Extensions\ModelTransformer;
use Html;

/**
 * Class ImageTransformer
 *
 * @package Minhbang\Image
 */
class ImageTransformer extends ModelTransformer
{
    /**
     * @param \Minhbang\Image\Image $image
     *
     * @return array
     */
    public function transform(Image $image)
    {
        return [
            'id'      => (int)$image->id,
            'title'   => $image->present()->block,
            'updated_at'   => $image->present()->updatedAt(['template' => ':date<br>:time']),
            'width'   => $image->width,
            'height'  => $image->height,
            'mime'    => $image->present()->mime,
            'size'    => $image->present()->size,
            'used'    => $image->used,
            'actions' => Html::tableActions(
                'backend.image',
                ['image' => $image->id],
                __('Images') . ($image->title ? ": {$image->title}" : ''),
                __('Images'),
                [
                    'renderShow'   => 'modal-large',
                    'renderDelete' => (int)$image->used ? 'disabled' : 'link',
                    'titleEdit'    => __('Replace image'),
                ]
            ),
        ];
    }
}