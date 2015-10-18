<?php
return [
    /**
     * Tự động add các route
     */
    'add_route'   => true,
    /**
     * Khai báo middlewares cho các Controller
     */
    'middlewares' => [
        // image api controller bắt buộc phải có auth middleware
        'api'     => 'auth',
        'backend' => 'admin',
    ],
    /**
     * Resources model có sử dụng image
     * - Định dạng array: ['class1', 'class2',...]
     * - Model phải có public array|string $has_images: danh sách attributes có images
     * - Sử dụng khi:
     *     + Manual update image used count (1 hoặc nhiều image) trong trang quản lý image
     *     + Cập nhật
     */
    'resources'   => [
        Minhbang\LaravelArticle\Article::class,
    ],
    /**
     * Image thumbnails
     */
    'thumbnail'   => [
        'width'  => 120,
        'height' => 90,
    ],
    /**
     * Allowed extentions.
     */
    'extentions'  => ["gif", "jpeg", "jpg", "png"],
    /**
     * Allowed Mime stypes, map with file extention
     */
    'mime_types'  => [
        "image/gif"   => 'gif',
        "image/jpeg"  => 'jpg',
        "image/pjpeg" => 'jpg',
        "image/x-png" => 'png',
        "image/png"   => 'png',
    ],
    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    |
    | Intervention Image supports "GD Library" and "Imagick" to process images
    | internally. You may choose one of them according to your PHP
    | configuration. By default PHP's "GD Library" implementation is used.
    |
    | Supported: "gd", "imagick"
    |
    */

    'driver'      => 'imagick'
];