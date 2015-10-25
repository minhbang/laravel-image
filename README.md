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

## Sử dụng

* Model: extends ImageableModel
* Many To Many Polymorphic Relations, vd: article
> - $article->images tất cả hình ảnh của article
> - $article->content_images tất cả hình ảnh chèn vào content của article
> - $article->linked_images tất cả hình ảnh liên kết với article
> - Ngược lại: extends ImageModel, thêm vào: `public function articles(){return $this->morphedByMany('Article class', 'taggable');}`
> - $image->articles, $image->articles() _(khi query)_: tất cả articles có sử dụng $image

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
