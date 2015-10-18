# Laravel Image

Package quản lý Image cho Laravel Application
> * Mở rộng tính năng từ package **intervention/image** (đã bao gồm)
> * Các hàm API sử dụng như **intervention/image**, xem [http://image.intervention.io](http://image.intervention.io)

## Install

* **Thêm vào file composer.json của app**
```json
	"repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/minhbang/laravel-image"
        }
    ],
    "require": {
        "minhbang/laravel-image": "dev-master"
    }
```
``` bash
$ composer update
```

* **Thêm vào file config/app.php => 'providers'**
```php
	Minhbang\LaravelImage\ImageServiceProvider::class,
```

* **Publish config và database migrations**
```bash
$ php artisan vendor:publish
$ php artisan migrate
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
