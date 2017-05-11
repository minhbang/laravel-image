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
     * @param \Minhbang\Image\ImageModel $image
     *
     * @return array
     */
    public function transform(ImageModel $image)
    {
        return [
            'id'      => (int)$image->id,
            'title'   => $image->present()->block,
            'width'   => $image->width,
            'height'  => $image->height,
            'mime'    => $image->present()->mime,
            'size'    => $image->present()->size,
            'used'    => $image->used,
            'actions' => Html::tableActions(
                'backend.image',
                ['image' => $image->id],
                trans('image::common.images') . ($image->title ? ": {$image->title}" : ''),
                trans('image::common.images'),
                [
                    'renderShow'   => 'modal-large',
                    'renderDelete' => (int)$image->used ? 'disabled' : 'link',
                    'titleEdit'    => trans('image::common.replace'),
                ]
            ),
        ];
    }
}