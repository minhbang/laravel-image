<?php
namespace Minhbang\LaravelImage\Controllers;

use Minhbang\LaravelKit\Extensions\Controller;
use Minhbang\LaravelImage\ImageModel;
use Illuminate\Http\Request;
use Image;

/**
 * Class ApiController
 *
 * @package Minhbang\LaravelImage\Controllers
 */
class ApiController extends Controller
{
    /**
     * @param string|null $except
     *
     * @return \Illuminate\View\View
     */
    public function browse($except = null)
    {
        $url_data = route('image.data', ['page' => '__PAGE__', 'except' => $except]);
        return view('image::browse', compact('url_data'));
    }

    /**
     * Get danh sách hình ảnh, dạng Json
     * - Có page: for my image browse
     * - Không page: for Froala image manage plugin
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function data(Request $request)
    {
        $page = $request->get('page');
        $results = ImageModel::mine()->orderUpdated();
        if ($except = $request->get('except')) {
            $results = $results->except($except);
        }
        /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Minhbang\LaravelImage\ImageModel[] $results */
        $results = $page ? $results->paginate(config('image.page_size')) : $results->get();
        $images = [];
        foreach ($results as $image) {
            $images[] = $image->arrayAttributes(['id', 'url' => 'src', 'thumb', 'thumb_4x', 'tag' => 'tags', 'title', 'size']);
        }
        if ($images) {
            if ($page) {
                return response()->json([
                    'page_size' => config('image.page_size'),
                    'pages'     => $results->lastPage(),
                    'page'      => $page,
                    'images'    => $images,
                ]);
            } else {
                return response()->json($images);
            }
        } else {
            return $this->abort(trans('common.images_folder_empty'));
        }
    }

    /**
     * Tiếp nhận image do user upload lên
     * Sử dụng cho cả Froala Editor và Dropzone Js
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $result = Image::store($request);
        if (is_string($result)) {
            $this->abort($result);
        }
        /** @var \Intervention\Image\Image $image */
        list($image, $filename, $mime) = $result;
        // save image info to database
        $model = ImageModel::create(
            [
                'tags'     => $request->get('tags'),
                'title'    => $request->get('title'),
                'filename' => $filename,
                'width'    => $image->width(),
                'height'   => $image->height(),
                'mime'     => $mime,
                'size'     => filesize(user_public_path('images', true) . '/' . $filename),
                'used'     => 0,
                'user_id'  => user('id'),
            ]
        );
        $image->destroy();

        return response()->json(['link' => user_public_path('images') . "/$filename", 'id' => $model->id]);
    }

    /**
     * Xóa image
     * Trước khi xóa, kiểm tra hình ảnh có đang được sử dụng trong bài viết khác không
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request)
    {
        if (!($src = $request->get('src'))) {
            return $this->abort(trans('errors.invalid_request'));
        }
        return Image::destroy($src);
    }

    /**
     * @param string $message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function abort($message)
    {
        return response()->json(['error' => $message], 200);
    }
}