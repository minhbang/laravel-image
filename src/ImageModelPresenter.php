<?php
namespace Minhbang\LaravelImage;

use Laracasts\Presenter\Presenter;
use Html;
use Minhbang\LaravelKit\Traits\Presenter\DatetimePresenter;

/**
 * Class ImageModelPresenter
 * @package Minhbang\LaravelImage
 */
class ImageModelPresenter extends Presenter
{
    use DatetimePresenter;

    /**
     * @return string
     */
    public function mime()
    {
        return strtoupper($this->entity->type);
    }

    /**
     * @return string
     */
    public function dimensions()
    {
        return "{$this->entity->width}x{$this->entity->height}";
    }

    /**
     * @return string
     */
    public function size()
    {
        return mb_format_bytes($this->entity->size, 0);
    }

    /**
     * @return string
     */
    public function tags()
    {
        if ($tags = $this->entity->tagNames()) {
            return '<span class="label label-primary">' . implode('</span><span class="label label-primary">', $tags) . '</span>';
        } else {
            return null;
        }
    }

    /**
     * @param string $group
     * @return string
     */
    public function lightbox($group = 'image')
    {
        return "<a class='a-image' href=\"{$this->entity->src}\" data-lightbox=\"{$group}\" data-title=\"{$this->entity->title}\">{$this->thumbnail()}</a>";
    }

    /**
     * @return string
     */
    public function image()
    {
        return "<img src=\"{$this->entity->src}\" title=\"{$this->entity->title}\" alt=\"{$this->entity->title}\" />";
    }

    /**
     * @return string
     */
    public function thumbnail()
    {
        return "<img class=\"thumb\" src=\"{$this->entity->thumb}\" title=\"{$this->entity->title}\" alt=\"{$this->entity->title}\" />";
    }

    /**
     * @return string
     */
    public function block()
    {
        return <<<"HTML"
<div class="block-thumb">
    <div class="d-left">{$this->lightbox()}</div>
    <div class="d-right">{$this->titleQuickUpdate()}</div>
    <div class="d-right">{$this->tagsQuickUpdate()}</div>
</div>
HTML;
    }

    /**
     * @return string
     */
    public function tagsQuickUpdate()
    {
        return Html::linkQuickUpdate(
            $this->entity->id,
            $this->entity->tags,
            [
                'attr'       => 'tags',
                'title'      => trans("image::common.tags"),
                'class'      => 'w-lg',
                'placement'  => 'bottom',
                'label'      => $this->tags(),
                'null_label' => trans("image::common.null_tags"),
            ],
            ['class' => 'a-tags']
        );
    }

    /**
     * @return string
     */
    public function titleQuickUpdate()
    {
        return Html::linkQuickUpdate(
            $this->entity->id,
            $this->entity->title,
            [
                'attr'       => 'title',
                'title'      => trans("image::common.title"),
                'class'      => 'w-lg',
                'placement'  => 'top',
                'null_label' => trans("image::common.null_title"),
            ],
            ['class' => 'a-title']
        );
    }
}