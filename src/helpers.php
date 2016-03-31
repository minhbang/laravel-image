<?php
if (!function_exists('process_image_uploaded')) {
    /**
     * @param string $original_name
     * @param string $original_path
     * @param string $path
     * @param array $versions
     * @param array $options
     *
     * @return string
     */
    function save_new_image($original_name, $original_path, $path, $versions = [], $options = [])
    {
        $options = $options + ['method' => 'fit', 'background' => '#ffffff', 'position' => 'center'];
        $filename = Minhbang\Kit\Support\VnString::slug_filename($original_name, true);
        foreach ($versions as $ver => $config) {
            $image_name = $ver == 'main' ? $filename : "$ver-$filename";
            $method = isset($config['method']) ? $config['method'] : $options['method'];
            switch ($method) {
                case 'insert':
                    $image = Image::make($original_path);
                    if ($image->width() > $config['width']) {
                        $image = $image->widen($config['width']);
                    }
                    if ($image->height() > $config['height']) {
                        $image = $image->heighten($config['height']);
                    }
                    Image::canvas($config['width'], $config['height'], $options['background'])
                        ->insert($image, $options['position'])
                        ->save("$path/$image_name");
                    $image->destroy();
                    break;
                case 'max':
                    $image = Image::make($original_path);
                    if ($config['width'] > 0 && $image->width() > $config['width']) {
                        $image = $image->widen($config['width']);
                    }
                    if ($config['height'] > 0 && $image->height() > $config['height']) {
                        $image = $image->heighten($config['height']);
                    }
                    $image->save("$path/$image_name");
                    $image->destroy();
                    break;
                case 'resize':
                    Image::make($original_path)
                        ->resize($config['width'], $config['height'])
                        ->save("$path/$image_name");
                default: // fit
                    Image::make($original_path)
                        ->fit($config['width'], $config['height'])
                        ->save("$path/$image_name");
            }
        }

        return $filename;
    }
}

if (!function_exists('process_image_uploaded')) {
    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param null|string|array $old_image các file image cũ cần xóa
     * @param string $path thư mục lưu hình ảnh
     * @param array $versions các version của hình ảnh [ [width, height, suffix],... ]
     * @param array $options ['method' => 'fit', 'background' => '#ffffff', 'position' => 'center'], method = fit | max | insert
     *
     * @return array
     */
    function process_image_uploaded($file, $old_image, $path, $versions = [], $options = [])
    {
        if (!empty($old_image)) {
            if (is_string($old_image)) {
                $old_image = [$old_image];
            }
            foreach ($old_image as $f) {
                @unlink($f);
            }
        }

        return save_new_image($file->getClientOriginalName(), $file->getRealPath(), $path, $versions, $options);
    }
}

if (!function_exists('save_image')) {
    /**
     * Lưu image do người dùng upload lên
     *
     * @param \App\Http\Requests\Request $request
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
        $file = $request->file($name);

        return $file ? process_image_uploaded($file, $old_image, $path, $versions, $options) : $default;
    }
}

if (!function_exists('save_images')) {
    /**
     * Lưu nhiều image do người dùng upload lên
     *
     * @param \App\Http\Requests\Request $request
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
                            @unlink("$path/" . ($ver == ('main' ? '' : "$ver-") . $old_images[$i]));
                        }
                        $old_images[$i] = $filename;
                    } else {
                        $old_images[] = $filename;
                    }
                } else {
                    // không upload hình ảnh, chỉ update title
                    if (isset($old_images[$i]) && !empty($titles[$i])) {
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

if (!function_exists('image_src_code')) {
    /**
     * Chuyển image src thành code: #{{img:id}}
     *
     * @param string $html
     *
     * @return string
     */
    function image_src_code($html)
    {
        return app('image')->srcCode($html);
    }
}

if (!function_exists('image_src_decode')) {
    /**
     * Chuyển image code thành src
     *
     * @param string $html
     *
     * @return string
     */
    function image_src_decode($html)
    {
        return app('image')->srcDecode($html);
    }
}
