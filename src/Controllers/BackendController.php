<?php

namespace Minhbang\Image\Controllers;

use Datatables;
use Illuminate\Http\Request;
use Minhbang\Image\Image;
use Minhbang\Image\ImageTransformer;
use Minhbang\Kit\Extensions\BackendController as Controller;
use Minhbang\Kit\Extensions\DatatableBuilder as Builder;
use Minhbang\Kit\Traits\Controller\CheckDatatablesInput;
use Minhbang\Kit\Traits\Controller\QuickUpdateActions;

//use Minhbang\Tag\Tag;

/**
 * Class BackendController
 *
 * @package Minhbang\Image\Controllers
 */
class BackendController extends Controller
{
    use QuickUpdateActions;
    use CheckDatatablesInput;

    /**
     * Danh sách hình ảnh theo định dạng của Datatables.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function data(Request $request)
    {
        $this->filterDatatablesParametersOrAbort($request);
        /** @var Image $query */
        $query = Image::query()->orderUpdated();
        if ($request->has('search_form')) {
            $query = $query
                ->searchWhereBetween('images.created_at', 'mb_date_vn2mysql')
                ->searchWhereBetween('images.updated_at', 'mb_date_vn2mysql');
        }

        return Datatables::of($query)->setTransformer(new ImageTransformer())->make(true);
    }

    /**
     * @param \Minhbang\Kit\Extensions\DatatableBuilder $builder
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Builder $builder)
    {
        $builder->addTableClass('table-image');
        $builder->ajax(route('backend.image.data'));
        $html = $builder->columns([
            ['data' => 'id', 'name' => 'id', 'title' => 'ID', 'class' => 'min-width text-center'],
            ['data' => 'title', 'name' => 'title', 'title' => trans('image::common.column.image')],
            [
                'data' => 'updated_at',
                'name' => 'updated_at',
                'title' => trans('common.updated_at'),
                'class' => 'min-width text-right',
            ],
            [
                'data' => 'width',
                'name' => 'width',
                'title' => trans('image::common.column.width'),
                'class' => 'min-width text-right',
            ],
            [
                'data' => 'height',
                'name' => 'height',
                'title' => trans('image::common.column.height'),
                'class' => 'min-width text-right',
            ],
            [
                'data' => 'mime',
                'name' => 'mime',
                'title' => trans('image::common.column.mime'),
                'class' => 'min-width text-center',
            ],
            [
                'data' => 'size',
                'name' => 'size',
                'title' => trans('image::common.column.size'),
                'class' => 'min-width text-right',
            ],
            [
                'data' => 'used',
                'name' => 'used',
                'title' => trans('image::common.column.used'),
                'class' => 'min-width text-right',
            ],
        ])->addAction([
            'data' => 'actions',
            'name' => 'actions',
            'title' => trans('common.actions'),
            'class' => 'min-width',
        ]);
        $this->buildHeading(trans('image::common.library'), 'fa-image', ['#' => trans('image::common.library')]);
        $all_tags = Image::usedTagNames();

        return view('image::index', compact('html', 'all_tags'));
    }

    /**
     * @return \Illuminate\View\View
     * @throws \Laracasts\Presenter\Exceptions\PresenterException
     */
    public function upload()
    {
        $this->buildHeading(
            trans('image::common.upload_title'),
            'fa-upload',
            [
                route('backend.image.index') => trans('image::common.library'),
                '#' => trans('common.upload'),
            ]
        );
        $all_tags = Image::usedTagNames();

        return view('image::upload', compact('all_tags'));
    }

    /**
     * Xem chi tiết hình
     *
     * @param \Minhbang\Image\Image $image
     *
     * @return \Illuminate\View\View
     */
    public function show(Image $image)
    {
        return view('image::show', compact('image'));
    }

    /**
     * Thay thế hình ảnh đã upload
     *
     * @param \Minhbang\Image\Image $image
     *
     * @return \Illuminate\View\View
     */
    public function edit(Image $image)
    {
        return view('image::edit', compact('image'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Minhbang\Image\Image $image
     *
     * @return \Illuminate\View\View
     */
    public function update(Request $request, Image $image)
    {
        $result = app('image-factory')->store($request);
        if (is_string($result)) {
            return view(
                'kit::_modal_script',
                [
                    'message' => [
                        'type' => 'error',
                        'content' => '<strong>'.trans('errors.whoops').'</strong> '.$result,
                    ],
                ]
            );
        }
        /** @var \Intervention\Image\Image $new_image */
        list($new_image, $filename, $mime) = $result;

        $image->filename = $filename;
        $image->width = $new_image->width();
        $image->height = $new_image->height();
        $image->mime = $mime;
        $image->size = $new_image->filesize();
        $image->save();

        return view(
            'kit::_modal_script',
            [
                'message' => [
                    'type' => 'success',
                    'content' => trans('image::common.replace_success'),
                ],
                'reloadTable' => 'image-manage',
            ]
        );
    }

    /**
     * Điều kiện xóa được: $image sử dụng trong nội dung và user là admin hoặc người tạo image
     *
     * @param \Minhbang\Image\Image $image
     * @param bool $return
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Image $image, $return = true)
    {
        $user = user();
        if (($image->used <= 0) && (authority()->user()->isAdmin() || ($user->id === $image->user_id))) {
            $image->delete();

            return $return ? response()->json(
                [
                    'type' => 'success',
                    'content' => trans('common.delete_object_success', ['name' => trans('image::common.images')]),
                ]
            ) : true;
        } else {
            return $return ? response()->json(
                [
                    'type' => 'error',
                    'content' => trans('image::common.delete_error'),
                ]
            ) : false;
        }
    }

    /**
     * Xóa nhiều image cùng lúc
     *
     * @param string $ids
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyBatch($ids)
    {
        $ids = explode(',', $ids);
        $count = 0;
        foreach ($ids as $id) {
            if ($id && ($image = Image::find($id)) && $this->destroy($image, false)) {
                $count++;
            }
        }
        if ($count) {
            return response()->json(
                [
                    'type' => 'success',
                    'content' => trans('common.delete_object_success',
                        ['name' => $count + ' ' + trans('image::common.images')]),
                ]
            );
        } else {
            return response()->json(
                [
                    'type' => 'error',
                    'content' => trans('image::common.delete_error'),
                ]
            );
        }
    }

    /**
     * Các attributes cho phéo quick-update
     *
     * @return array
     */
    protected function quickUpdateAttributes()
    {
        return [
            'title' => ['rules' => 'max:255', 'label' => trans('image::common.title')],
            'tag_names' => [
                'rules' => 'max:255',
                'label' => trans('image::common.tags'),
                'result' => function () {
                    return Image::usedTagNames();
                },
            ],
        ];
    }
}