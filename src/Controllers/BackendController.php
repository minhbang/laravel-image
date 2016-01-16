<?php
namespace Minhbang\LaravelImage\Controllers;

use Minhbang\LaravelKit\Extensions\BackendController as Controller;
use Minhbang\LaravelKit\Traits\Controller\QuickUpdateActions;
use Minhbang\LaravelImage\ImageModel;
use Illuminate\Http\Request;
use Conner\Tagging\Tag;
use Datatable;
use Image;
use Html;

/**
 * Class BackendController
 *
 * @package Minhbang\LaravelImage\Controllers
 */
class BackendController extends Controller
{
    use QuickUpdateActions;
    /**
     * Danh sách hình ảnh theo định dạng của Datatables.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Datatable JSON
     */
    public function data(Request $request)
    {
        /** @var ImageModel $query */
        $query = ImageModel::orderUpdated();
        if ($request->has('search_form')) {
            $query = $query
                ->searchWhereBetween('images.created_at', 'mb_date_vn2mysql')
                ->searchWhereBetween('images.updated_at', 'mb_date_vn2mysql');
        }
        return Datatable::query($query)
            ->addColumn(
                'index',
                function (ImageModel $model) {
                    return $model->id;
                }
            )
            ->addColumn(
                'title',
                function (ImageModel $model) {
                    return $model->present()->block;
                }
            )
            ->addColumn(
                'dimensions',
                function (ImageModel $model) {
                    return $model->present()->dimensions;
                }
            )
            ->addColumn(
                'mime',
                function (ImageModel $model) {
                    return $model->present()->mime;
                }
            )
            ->addColumn(
                'size',
                function (ImageModel $model) {
                    return $model->present()->size;
                }
            )
            ->addColumn(
                'used',
                function (ImageModel $model) {
                    return $model->used;
                }
            )
            ->addColumn(
                'actions',
                function (ImageModel $model) {
                    return Html::tableActions(
                        'backend.image',
                        ['image' => $model->id],
                        trans('image::common.images') . ($model->title ? ": {$model->title}" : ''),
                        trans('image::common.images'),
                        [
                            'renderShow'   => 'modal-large',
                            'renderDelete' => (int)$model->used ? 'disabled' : 'link',
                            'titleEdit'    => trans('image::common.replace'),
                        ]
                    );
                }
            )
            ->searchColumns('images.title', 'images.mime')
            ->make();
    }

    /**
     * @return \Illuminate\View\View
     * @throws \Exception
     */
    public function index()
    {
        $tableOptions = [
            'id'        => 'image-manage',
            'row_index' => true,
            'class'     => 'table-image',
        ];
        $options = [
            'aoColumnDefs' => [
                ['sClass' => 'min-width', 'aTargets' => [0, 6]],
                ['sClass' => 'min-width text-center', 'aTargets' => [3]],
                ['sClass' => 'min-width text-right', 'aTargets' => [2, 4, 5]],
            ],
        ];
        $table = Datatable::table()
            ->addColumn(
                '#',
                trans('image::common.column.image'),
                trans('image::common.column.dimensions'),
                trans('image::common.column.mime'),
                trans('image::common.column.size'),
                trans('image::common.column.used'),
                trans('common.actions')
            )
            ->setOptions($options)
            ->setCustomValues($tableOptions);
        $this->buildHeading(trans('image::common.library'), 'fa-image', ['#' => trans('image::common.library')]);
        $allTags = implode(',', Tag::lists('name')->all());
        return view('image::index', compact('tableOptions', 'options', 'table', 'allTags'));
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
                '#'                          => trans('common.upload'),
            ]
        );
        $all_tags = ImageModel::allTagNames();
        return view('image::upload', compact('all_tags'));
    }

    /**
     * Xem chi tiết hình
     *
     * @param \Minhbang\LaravelImage\ImageModel $image
     *
     * @return \Illuminate\View\View
     */
    public function show(ImageModel $image)
    {
        return view('image::show', compact('image'));
    }

    /**
     * Thay thế hình ảnh đã upload
     *
     * @param \Minhbang\LaravelImage\ImageModel $image
     *
     * @return \Illuminate\View\View
     */
    public function edit(ImageModel $image)
    {
        return view('image::edit', compact('image'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Minhbang\LaravelImage\ImageModel $image
     *
     * @return \Illuminate\View\View
     */
    public function update(Request $request, ImageModel $image)
    {
        $result = Image::store($request);
        if (is_string($result)) {
            return view(
                '_modal_script',
                [
                    'message' => [
                        'type'    => 'error',
                        'content' => '<strong>' . trans('errors.whoops') . '</strong> ' . $result,
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
            '_modal_script',
            [
                'message'     => [
                    'type'    => 'success',
                    'content' => trans('image::common.replace_success'),
                ],
                'reloadTable' => 'image-manage',
            ]
        );
    }

    /**
     * Điều kiện xóa được: $image sử dụng trong nội dung và user là admin hoặc người tạo image
     *
     * @param \Minhbang\LaravelImage\ImageModel $image
     * @param bool $return
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(ImageModel $image, $return = true)
    {
        $user = user();
        if (($image->used <= 0) && ($user->inAdminGroup() || ($user->id === $image->user_id))) {
            $image->delete();
            return $return ? response()->json(
                [
                    'type'    => 'success',
                    'content' => trans('common.delete_object_success', ['name' => trans('image::common.images')]),
                ]
            ) : true;
        } else {
            return $return ? response()->json(
                [
                    'type'    => 'error',
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
            if ($id && ($image = ImageModel::find($id)) && $this->destroy($image, false)) {
                $count++;
            }
        }
        if ($count) {
            return response()->json(
                [
                    'type'    => 'success',
                    'content' => trans('common.delete_object_success', ['name' => $count + ' ' + trans('image::common.images')]),
                ]
            );
        } else {
            return response()->json(
                [
                    'type'    => 'error',
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
            'tags'  => ['rules' => 'max:255', 'label' => trans('image::common.tags'), 'result' => function ($model) {
                return ImageModel::allTagNames();
            }],
        ];
    }
}