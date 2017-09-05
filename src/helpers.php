<?php

use Illuminate\Http\UploadedFile;
use Minhbang\Kit\Support\VnString;

if (! function_exists('process_image_uploaded')) {
    /**
     * @param string $original_name
     * @param string|\Imagick $original_image
     * @param string $path
     * @param array $versions
     * @param array $options
     *
     * @return string
     */
    function save_new_image($original_name, $original_image, $path, $versions = [], $options = [])
    {
        $original_image = app('image-factory')->make($original_image);

        $options = $options + ['method' => 'fit', 'background' => '#ffffff', 'position' => 'center'];
        $filename = VnString::slug_filename($original_name, true);
        foreach ($versions as $ver => $config) {
            $image_name = $ver == 'main' ? $filename : "$ver-$filename";
            $method = isset($config['method']) ? $config['method'] : $options['method'];
            $image = clone $original_image;
            switch ($method) {
                case 'insert':
                    if ($image->width() > $config['width']) {
                        $image = $image->widen($config['width']);
                    }
                    if ($image->height() > $config['height']) {
                        $image = $image->heighten($config['height']);
                    }
                    app('image-factory')->canvas($config['width'], $config['height'], $options['background'])
                        ->insert($image, $options['position'])
                        ->save("$path/$image_name");
                    break;
                case 'max':
                    if ($config['width'] > 0 && $image->width() > $config['width']) {
                        $image = $image->widen($config['width']);
                    }
                    if ($config['height'] > 0 && $image->height() > $config['height']) {
                        $image = $image->heighten($config['height']);
                    }
                    $image->save("$path/$image_name");
                    break;
                case 'resize':
                    $image->resize($config['width'], $config['height'])->save("$path/$image_name");
                    break;
                default: // fit
                    $image->fit($config['width'], $config['height'])->save("$path/$image_name");
            }
            $image->destroy();
        }

        return $filename;
    }
}

if (! function_exists('process_image_uploaded')) {
    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile|\Imagick $image
     * @param null|string|array $old_image các file image cũ cần xóa
     * @param string $path thư mục lưu hình ảnh
     * @param array $versions các version của hình ảnh [ [width, height, suffix],... ]
     * @param array $options ['method' => 'fit', 'background' => '#ffffff', 'position' => 'center'], method = fit | max | insert
     *
     * @return array
     */
    function process_image_uploaded($image, $old_image, $path, $versions = [], $options = [])
    {
        if (! empty($old_image)) {
            if (is_string($old_image)) {
                $old_image = [$old_image];
            }
            foreach ($old_image as $f) {
                @unlink($f);
            }
        }

        return is_a($image, 'Imagick') ?
            save_new_image("image.{$image->getFormat()}", $image, $path, $versions, $options) :
            save_new_image($image->getClientOriginalName(), $image->getRealPath(), $path, $versions, $options);
    }
}

if (! function_exists('save_image')) {
    /**
     * Lưu image, Tham số $request:
     * - Request object: hình ảnh upload
     * - array: hình ảnh từ file, [path, original file name]
     * - Imagick: hình ảnh 'on the fly', ví dụ từ PDF
     *
     * @param \Minhbang\Kit\Extensions\Request|array|\Imagick $request
     * @param string $name
     * @param null|string|array $old_image các file củ cần xóa
     * @param string $path
     * @param array $versions các version của hình ảnh [ [width, height, suffix],... ]
     * @param array $options ['method' => 'fit', 'background' => '#ffffff', 'position' => 'center']
     * @param mixed $default
     *
     * @return mixed
     */
    function save_image($request, $name, $old_image = null, $path, $versions = [], $options = [], $default = null)
    {
        $image = is_a($request, 'Imagick') ?
            $request :
            (is_array($request) ? new UploadedFile($request[0], $request[1]) : $request->file($name));

        return $image ? process_image_uploaded($image, $old_image, $path, $versions, $options) : $default;
    }
}

if (! function_exists('save_images')) {
    /**
     * Lưu nhiều image do người dùng upload lên
     *
     * @param \Minhbang\Kit\Extensions\Request $request
     * @param string $name
     * @param array $old_images các file củ cần xóa
     * @param string $path
     * @param array $versions các version của hình ảnh [ [width, height, suffix],... ]
     * @param array $options ['method' => 'fit', 'background' => '#ffffff', 'position' => 'center']
     * @param mixed $default
     *
     * @return mixed
     */
    function save_images($request, $name, $old_images = [], $path, $versions = [], $options = [], $default = null)
    {
        $inputs = $request->all();
        if (isset($inputs[$name])) {
            $files = $inputs[$name];
            $titles = isset($inputs["title_$name"]) ? $inputs["title_$name"] : [];
            $prefixes = array_keys($versions);
            foreach ($files as $i => $file) {
                if ($file instanceof Symfony\Component\HttpFoundation\File\UploadedFile) {
                    $filename = process_image_uploaded($file, null, $path, $versions, $options);
                    $filename .= empty($titles[$i]) ? '' : "#$titles[$i]";
                    if (isset($old_images[$i])) {
                        //remove old image
                        foreach ($prefixes as $ver) {
                            @unlink("$path/".($ver == ('main' ? '' : "$ver-").$old_images[$i]));
                        }
                        $old_images[$i] = $filename;
                    } else {
                        $old_images[] = $filename;
                    }
                } else {
                    // không upload hình ảnh, chỉ update title
                    if (isset($old_images[$i]) && ! empty($titles[$i])) {
                        $filename = explode('#', $old_images[$i], 2);
                        $old_images[$i] = "{$filename[0]}#{$titles[$i]}";
                    }
                }
            }

            return implode('|', $old_images);
        } else {
            return $default;
        }
    }
}

if (! function_exists('image_src_code')) {
    /**
     * Chuyển image src thành code: #{{img:id}}
     *
     * @param string $html
     *
     * @return string
     */
    function image_src_code($html)
    {
        return app('image-factory')->srcCode($html);
    }
}

if (! function_exists('image_src_decode')) {
    /**
     * Chuyển image code thành src
     *
     * @param string $html
     *
     * @return string
     */
    function image_src_decode($html)
    {
        return app('image-factory')->srcDecode($html);
    }
}
