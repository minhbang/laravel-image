<?php

namespace Minhbang\Image\Controllers;

use Illuminate\Http\Request;
use Minhbang\Image\Image;
use Minhbang\Kit\Extensions\Controller;

/**
 * Class ApiController
 *
 * @package Minhbang\Image\Controllers
 */
class ApiController extends Controller
{
    /**
     * @param string $except
     * @param string $multi
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function browse($multi = '0', $except = null)
    {
        $url_data = route('image.data', ['page' => '__PAGE__', 'except' => $except]);
        $all_tags = Image::usedTagNames();

        return view('image::browse', compact('url_data', 'multi', 'all_tags'));
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
        $results = Image::mine()->orderUpdated();
        if ($except = $request->get('except')) {
            $results = $results->except($except);
        }
        /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Minhbang\Image\Image[] $results */
        $results = $page ? $results->paginate(config('image.page_size')) : $results->get();
        $images = [];
        foreach ($results as $image) {
            $images[] = $image->arrayAttributes([
                'id',
                'url' => 'src',
                'thumb',
                'thumb_4x',
                'small',
                'tag' => 'tag_names',
                'tags',
                'title',
                'size',
                'dimensions',
                'updatedAt',
            ]);
        }
        if ($images) {
            if ($page) {
                return response()->json([
                    'page_size' => config('image.page_size'),
                    'pages' => $results->lastPage(),
                    'page' => $page,
                    'images' => $images,
                ]);
            } else {
                return response()->json($images);
            }
        } else {
            return $this->abort(__('No images have been uploaded yet'));
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
        $result = app('image-factory')->store($request);
        if (is_string($result)) {
            return $this->abort($result);
        }
        /** @var \Intervention\Image\Image $image */
        list($image, $filename, $mime) = $result;
        $model = new Image([
            'tag_names' => $request->get('tags'),
            'title' => $request->get('title'),
            'filename' => $filename,
            'width' => $image->width(),
            'height' => $image->height(),
            'mime' => $mime,
            'size' => filesize(user()->upload_path('images', true).'/'.$filename),
            'used' => 0,
            'user_id' => user('id'),
        ]);
        $model->save();
        $image->destroy();

        return response()->json(['link' => user()->upload_path('images')."/$filename", 'id' => $model->id]);
    }

    /**
     * Xóa image
     * Trước khi xóa, kiểm tra hình ảnh có đang được sử dụng trong bài viết khác không
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool|\Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        if (! ($src = $request->get('src'))) {
            return $this->abort(__('Lỗi!...Invalid request'));
        }

        return app('image-factory')->destroy($src);
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