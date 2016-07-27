<?php
return [
    // Image browse page size
    'page_size'   => 12,
    /**
     * Tự động add các route
     */
    'add_route'   => true,
    /**
     * Khai báo middlewares cho các Controller
     */
    'middlewares' => [
        'api'     => 'auth',
        'backend' => 'role:admin',
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
    'driver'      => 'gd',
];