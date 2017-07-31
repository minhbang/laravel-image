/**
 * Quản lý Image Browser
 * Sử dụng:
 * $(selector).imageBrowser(options);
 *
 * Thay đổi các giá trị mặc định:
 * 1. Url:
 * url_store:  '{!!route('image.data', ['page' => '__PAGE__'])!!}',
 * 3. Ngôn ngữ:
 * trans:{
 *     first: "{{trans('common.first')}}",
 *     prev: "{{trans('common.previous')}}",
 *     next: "{{trans('common.next')}}",
 *     last: "{{trans('common.last')}}"
 * },
 */
;
(function ($) {
    'use strict';
    var defaults = {
        url_data: "/image/data/?page=__PAGE__",
        page: 1,
        trans: {
            first: "Đầu",
            prev: "Trước",
            next: "Sau",
            last: "Cuối"
        },
        multiSelect: false,
        onSuccess: null,
        onDeleted: null,
        change: null,
        templates: {
            detail_content: '<img src="__small__" class="img-responsive" />' +
            '<ul class="info">' +
            '<li><i class="fa fa-calendar"></i> __updatedAt__</li>' +
            '<li><i class="fa fa-file"></i> __size__</li>' +
            '<li><i class="fa fa-expand"></i> __dimensions__</li>' +
            '<li><i class="fa fa-quote-right"></i> __title__</li>' +
            '<li><i class="fa fa-tags"></i> __tags__</li>' +
            '</ul>'
        }
    };

    function ImageBrowser(element, options) {
        this.element = $(element);
        this.options = $.extend(true, defaults, options);
        this.init();
    }

    ImageBrowser.prototype = {
        init: function () {
            var _this = this;
            _this.initialized = false;
            _this.page = null;
            _this.images = $('<div class="images">');
            _this.pagination = $('<ul class="pagination-sm">');
            _this.detail_content = $('<div class="detail-view-content">');
            _this.element.append(
                $('<div class="row">')
                    .append(
                        $('<div class="col-xs-9 image-list">')
                            .append($('<div class="image-pagination">').append(_this.pagination))
                            .append(_this.images)
                    )
                    .append(
                        $('<div class="col-xs-3 detail-view">').append(_this.detail_content)
                    )
            );

            _this.load(_this.options.page, false);
            _this.images.on('click', '.image', function () {
                // toggle selected class
                if (_this.options.multiSelect) {
                    $(this).toggleClass('selected');
                } else {
                    _this.images.find('.image').removeClass('selected');
                    $(this).addClass('selected');
                }

                // update image detail view
                if ($(this).hasClass('selected')) {
                    _this.detail_content.html($.fn.mbHelpers.render(_this.options.templates.detail_content, $(this).data('image')))
                } else {
                    _this.detail_content.html("");
                }
                // fire change event
                if (_this.options.change) {
                    _this.options.change(_this);
                }
            });
        },
        selected: function () {
            return $.map(this.images.find('.image.selected'), function (item) {
                return $(item).data('image');
            });
        },
        reload: function () {
            this.load(1, true);
        },
        load: function (page, reload) {
            if (!reload && (page == this.page)) {
                return;
            }
            var _this = this;
            _this.images.empty();
            $.get(_this.options.url_data.replace('__PAGE__', page), function (data) {
                if (data["error"]) {
                    _this.images.html('<div class="alert alert-danger">' + data["error"] + '</div>');
                } else {
                    if (!_this.initialized) {
                        _this.pagination.twbsPagination({
                            totalPages: data["pages"],
                            visiblePages: 5,
                            first: _this.options.trans.first,
                            prev: _this.options.trans.prev,
                            next: _this.options.trans.next,
                            last: _this.options.trans.last,
                            onPageClick: function (event, _page) {
                                _this.load(_page, false);
                            }
                        });
                        _this.initialized = true;
                    }
                    var images = '';
                    $.each(data["images"], function (i, image) {
                        _this.add(image);
                    });
                    _this.images.waitForImages(function () {
                        $.fn.mbHelpers.updateModalHeight();
                    });
                }
            }, "json");
            this.page = page;
        },
        add: function (image) {
            var item = $('<div class="image">').data('image', image),
                img = $('<img />').attr('src', image["thumb"]),
                title = $('<div class="title">').html(image["title"]),
                selected_mark = $('<div class="selected-mark">').append($('<span class="fa fa-check">')).append($('<div class="bg">'));
            item.append(img).append(selected_mark);
            if (image["title"]) {
                item.append(title);
            }
            this.images.append(item);
        }
    };

    $.fn.imageBrowser = function (options) {
        var lists = this,
            retval = this;
        lists.each(function () {
            var plugin = $(this).data("imageBrowser");
            if (!plugin) {
                $(this).data("imageBrowser", new ImageBrowser(this, options));
            } else {
                if (typeof options === 'string' && typeof plugin[options] === 'function') {
                    retval = plugin[options]();
                }
            }
        });

        return retval || lists;
    };
})(jQuery);