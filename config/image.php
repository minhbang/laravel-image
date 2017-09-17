<?php
return [
    // Image browse page size
    'page_size'   => 12,
    /**
     * Khai báo middlewares cho các Controller
     */
    'middlewares' => [
        'api'     => ['web', 'auth'],
        'backend' => ['web', 'role:sys.admin'],
    ],

    /**
     * Kích thước ảnh (crop cố định) khi xem thumbnail
     */
    'thumbnail'   => [
        'width'  => 120,
        'height' => 90,
    ],
    /**
     * Chiều rộng ảnh nhỏ, resize (không crop), chiều cao phụ thuộc
     */
    'small_width' => 260,

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

    // Định nghĩa menus cho image
    'menus'       => [
        'backend.sidebar.media.image' => [
            'priority' => 3,
            'url'      => 'route:backend.image.index',
            'label'    => 'trans:image::common.images',
            'icon'     => 'fa-image',
            'active'   => 'backend/image*',
            'role' => 'sys.admin',
        ],
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
    'driver'      => 'imagick',
];