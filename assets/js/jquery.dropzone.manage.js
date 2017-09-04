/**
 * Quản lý Dropzone Js
 * Sử dụng:
 * $(selector).mbDropzone(options);
 *
 * Thay đổi các giá trị mặc định:
 * 1. Url:
 * url_store:  '{!!route('image.store')!!}',
 * url_delete: '{!!route('backend.image.destroy', ['image' => '__ID__'])!!}',
 * url_update: '{!!route('backend.image.quick_update', ['image' => '__ID__'])!!}',
 * 2. Thumbnail width
 * thumb_width: '{{config('image.thumbnail.width')*4}}',
 * thumb_height: '{{config('image.thumbnail.height')*4}}',
 * 3. Ngôn ngữ:
 * trans:{
 *     title: '{{trans('image::common.title')}}',
 *     null_title: '{{trans('image::common.null_title')}}',
 *     delete_title: '{{trans('image::common.delete_title')}}',
 *     delete_confirm: '{{trans('image::common.delete_confirm')}}',
 *     ok: '{{trans('common.ok')}}',
 *     cancel: '{{trans('common.cancel')}}',
 *     delete: '{{trans('common.delete')}}',
 *     upload: '{{trans('common.upload')}}',
 *     add_files: '{{trans('common.add_files')}}',
 *     tags: '{{trans('image::common.tags')}}'
 * },
 */
;
(function ($, window) {
    'use strict';
    var defaults = {
        autoUpload: true,
        manageMode: false,
        all_tags: "",
        url_browse: "/image/browse/__EXCEPT__",
        url_store: "/image/store",
        url_delete: "/backend/image/__ID__",
        url_delete_batch: "/backend/image/batch/__IDS__",
        url_update: "/backend/image/__ID__/quick_update",
        thumb_width: 480,
        thumb_height: 360,
        token: window.Laravel.csrfToken,
        images: [],
        trans: {
            title: "Tiêu đề",
            null_title: "— Không tiêu đề —",
            delete_title: "Xóa Hình ảnh",
            delete_confirm: "Bạn có chắc chắn muốn xóa Hình ảnh này?",
            ok: "Đồng ý",
            cancel: "Bỏ qua",
            view: "Xem",
            delete: "Xóa",
            upload_image: "Tải hình ảnh lên",
            upload: "Tải lên",
            add_files: "Thêm",
            browse_image: "Hình trên Server",
            browse: "Chọn",
            remove_all: "Xóa tất cả",
            remove_all_confirm: "Bạn có chắc chắn muốn xóa tất cả Hình ảnh?<br><span class='text-navy'>Lưu ý: Hình ảnh vẫn còn trên Server, vào menu <strong>Hình ảnh</strong> để quản lý hình...</span>",
            remove_all_confirm_manage: "Bạn có chắc chắn muốn xóa tất cả Hình ảnh này?",
            tags: "Tags"
        },
        preview_grid: "col-lg-3 col-md-4 col-sm-4 col-xs-6",
        onSuccess: null,
        onDeleted: null
    };

    function templateQuickUpdate(attr, element_class, title, placement, label) {
        var data = {attr: attr, title: title, class: "w-md", placement: placement, value: ""},
            data_qu = '';
        $.each(data, function (name, value) {
            data_qu += ' data-qu_' + name + '="' + value + '"';
        });
        return '<a href="#" class="' + element_class + ' quick-update text-gray"' + data_qu + ' title="' + title + '" data-toggle="tooltip">' + label + '</a>';
    }

    function templateButton(type, name, title, icon) {
        return '<a href="#" class="btn btn-' + type + ' btn-xs ' + name + '" data-toggle="tooltip" title="' + title + '">' +
            '<i class="fa fa-' + icon + '"></i>' +
            '</a>';
    }

    function templatePreview(options) {
        var buttons = templateButton('primary', 'start', options.trans.upload, 'upload') +
            templateButton('success', 'view', options.trans.view, 'eye') +
            templateButton('warning', 'cancel', options.trans.cancel, 'remove');
        if (options.manageMode) {
            buttons += templateButton('danger', 'delete', options.trans.delete, 'trash');
        }
        return '<div class="' + options.preview_grid + '">' +
            '<div class="inner">' +
            '<div class="status-mark" style="display: none">' +
            '<span class="fa fa-remove error"></span>' +
            '<span class="fa fa-check success"></span>' +
            '<div class="bg"></div>' +
            '</div>' +
            '<div class="alert alert-danger" data-dz-errormessage style="display: none"></div>' +
            '<div class="preview"><img data-dz-thumbnail/><div class="sending"></div></div>' +
            '<div class="form">' +
            '<div class="input-group">' +
            templateQuickUpdate('title', 'a-title form-control input-sm img-title', options.trans.title, 'top', options.trans.null_title) +
            '<span class="input-group-addon">' +
            templateQuickUpdate('tags', 'a-tags', options.trans.tags, 'top', '<i class="fa fa-tags"></i> <span class="badge badge-danger"></span>') +
            '</span>' +
            '</div>' +
            '<input type="hidden" class="img-title" value=""/>' +
            '<input type="hidden" class="img-tags" value=""/>' +
            '</div>' +
            '<div class="actions">' +
            '<div class="left">' + buttons + '</div>' +
            '<div class="right">' +
            '<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">' +
            '<div class="progress-bar progress-bar-success" style="width:0;" data-dz-uploadprogress></div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>';
    }

    function templateContainer(options) {
        var browse = options.manageMode ? '' :
            '<div class="col-xs-3 group">' +
            '<div>' + options.trans.browse_image + '</div>' +
            '<a href="#" class="btn btn-primary btn-xs browse" data-title="' + options.trans.browse + '" data-label="' + options.trans.ok + '" data-icon="fa-folder-open" data-width="large" data-callback="1"><i class="fa fa-folder-open"></i> ' + options.trans.browse + '</a>' +
            '</div>';

        return '<div class="ibox">' +
            '<div class="ibox-title no-height">' +
            '<div class="row buttons">' +
            '<div class="col-xs-' + (options.manageMode ? '9' : '6') + ' group">' +
            '<div>' + options.trans.upload_image + '</div>' +
            '<span class="btn btn-success btn-xs fileinput-button"><i class="fa fa-plus"></i> ' + options.trans.add_files + '</span>' +
            '<a href="#" class="btn btn-primary btn-xs start"><i class="fa fa-upload"></i> ' + options.trans.upload + '</a>' +
            '<a href="#" class="btn btn-warning btn-xs cancel"><i class="fa fa-remove"></i> ' + options.trans.cancel + '</a>' +
            '</div>' +
            browse +
            '<div class="col-xs-3 group">' +
            '<div>' + options.trans.remove_all + '</div>' +
            '<a href="#" class="btn btn-danger btn-xs remove_all"><i class="fa fa-remove"></i> ' + options.trans.delete + '</a>' +
            '</div>' +
            '</div>' +
            '<span class="fileupload-process">' +
            '<div class="dropzone-total-progress progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">' +
            '<div class="progress-bar progress-bar-success" style="width:0;" data-dz-uploadprogress></div>' +
            '</div>' +
            '</span>' +
            '</div>' +
            '<div class="ibox-content">' +
            '<div class="row dropzone-previews' + (options.manageMode ? ' dz-manage-mode' : '') + '"></div>' +
            '</div>' +
            '</div>';
    }

    function MbDropzone(element, options) {
        this.element = $(element);
        this.options = $.extend(true, defaults, options);
        this.init();
    }

    MbDropzone.prototype = {
        init: function () {
            this.element.html(templateContainer(this.options));
            this.btn_start = $('.ibox-title .start', this.element);
            this.btn_cancel = $('.ibox-title .cancel', this.element);
            this.btn_browse = $('.ibox-title .browse', this.element);
            this.btn_remove_all = $('.ibox-title .remove_all', this.element);

            this.totalBytes = 0;
            this.totalBytesSent = 0;
            this.totalProgress = $('.dropzone-total-progress', this.element);
            this.startAll = false;
            this.exceptImages = [];
            if (this.options.autoUpload) {
                this.btn_start.hide();
            }
            var that = this;
            that.myDropzone = new Dropzone('body', {
                url: this.options.url_store,
                thumbnailWidth: this.options.thumb_width,
                thumbnailHeight: this.options.thumb_height,
                previewTemplate: templatePreview(this.options),
                parallelUploads: that.options.autoUpload ? 5: 1,
                autoQueue: that.options.autoUpload,
                previewsContainer: ".dropzone-previews",
                clickable: ".fileinput-button",
                init: function () {
                    var thisDropzone = this;
                    thisDropzone.on("addedfile", function (file) {
                        that.totalBytes += file.upload.total;
                        $('[data-toggle=tooltip]', file.previewElement).tooltip({'container': 'body'}).click(function () {
                            $(this).tooltip('hide')
                        });

                        $('.view', file.previewElement).attr("disabled", "disabled")

                        $('.cancel', file.previewElement).click(function (e) {
                            e.preventDefault();
                            that.totalBytes -= file.upload.total;
                            thisDropzone.removeFile(file);
                        });

                        if (file.title) {
                            $('a.a-title', file.previewElement)
                                .data('qu_value', file.title)
                                .removeClass('text-gray')
                                .addClass('text-primary')
                                .html(file.title);
                        }

                        if (file.tag) {
                            $('a.a-tags', file.previewElement)
                                .data('qu_value', file.tag)
                                .find('.badge').html(file.tag.split(',').length)
                        }

                        $('a.quick-update', file.previewElement).quickUpdate({
                            url: that.options.url_update,
                            container: '.dropzone-previews',
                            afterShow: function (e, form) {
                                if ($(e).hasClass('a-tags')) {
                                    $('input._value', form).selectize_tags({options: that.options.all_tags + ',' + $(e).data('qu_value')});
                                }
                            },
                            processResult: function (e, result) {
                                that.hideProcessing(e);
                                if ($(e).hasClass('a-tags')) {
                                    that.options.all_tags = result + '';
                                }
                            },
                            beforeSubmit: function (e, new_value) {
                                if ($(e).hasClass('a-tags')) {
                                    $('.badge', e).html(new_value ? new_value.split(',').length : '');
                                    $(e).parents('.form').find('input.img-tags').val(new_value);
                                }
                                if ($(e).hasClass('a-title')) {
                                    if (new_value) {
                                        $(e).removeClass('text-gray').addClass('text-primary').html(new_value);
                                    } else {
                                        $(e).removeClass('text-primary').addClass('text-gray').html(that.options.trans.null_title);
                                    }
                                    $(e).parents('.form').find('input.img-title').val(new_value);
                                }
                                $(e).data('qu_value', new_value);

                                var submit = $(e).attr('href') != '#';
                                if (submit) {
                                    that.showProcessing(e);
                                }
                                return submit;
                            }
                        });
                        if (that.options.autoUpload) {
                            $('.start', file.previewElement).hide();
                            that.myDropzone.processQueue();
                        } else {
                            $('.start', file.previewElement).click(function (e) {
                                e.preventDefault();
                                thisDropzone.enqueueFile(file);
                            });
                        }

                        that.btn_remove_all.attr("disabled", null);
                        if (that.myDropzone.getFilesWithStatus(Dropzone.ADDED).length) {
                            that.btn_start.attr("disabled", null);
                            that.btn_cancel.attr("disabled", null);
                        }
                    });
                    thisDropzone.on("removedfile", function (file) {
                        if (!that.myDropzone.files.length) {
                            that.reset();
                        } else {
                            if (!that.myDropzone.getFilesWithStatus(Dropzone.ADDED).length) {
                                that.btn_start.attr("disabled", "disabled");
                                that.btn_cancel.attr("disabled", "disabled");
                            }
                        }
                        var id = $(file.previewElement).data('id');
                        if (id) {
                            // exceptImages without id
                            that.exceptImages = $.grep(that.exceptImages, function (old_id) {
                                return old_id !== id;
                            });
                        }
                    });
                    // Upload/Add old file hoàn thành, cả success và error
                    thisDropzone.on("complete", function (file) {
                        $('.status-mark, .alert', file.previewElement).attr('style', null);
                        var has_added = that.myDropzone.getFilesWithStatus(Dropzone.ADDED).length > 0;
                        that.btn_start.attr("disabled", has_added ? null : "disabled");
                        that.btn_cancel.attr("disabled", has_added ? null : "disabled");
                    });
                    // Upload/Add old file thành công
                    thisDropzone.on("success", function (file, result) {
                        if (that.startAll) {
                            that.totalBytesSent += file.upload.total;
                            if (that.totalBytesSent >= that.totalBytes) {
                                that.reset();
                            }
                        } else {
                            that.totalBytes -= file.upload.total;
                        }

                        $(file.previewElement).addClass('dz-image-uploaded');
                        $(file.previewElement).data('id', result.id);
                        that.exceptImages.push(result.id);
                        $('.view', file.previewElement)
                            .attr('href', result.link)
                            .attr('data-lightbox', 'dropzone-image');

                        if (result.old) {
                            $(file.previewElement).addClass('dz-image-old');
                        }

                        $('a.quick-update', file.previewElement).attr('href', '#' + result.id);
                        if (that.options.manageMode) {
                            $('.delete', file.previewElement)
                                .attr('href', that.options.url_delete.replace('__ID__', result.id))
                                .click(function (e) {
                                    e.preventDefault();
                                    var url = $(this).attr('href');
                                    window.bootbox.confirm({
                                        message: "<div class=\"message-delete\"><div class=\"confirm\">" + that.options.trans.delete_confirm + "</div></div>",
                                        title: that.options.trans.delete_title + '?',
                                        buttons: {
                                            cancel: {
                                                label: that.options.trans.cancel,
                                                className: "btn-default btn-white"
                                            },
                                            confirm: {label: that.options.trans.ok, className: "btn-danger"}
                                        },
                                        callback: function (ok) {
                                            if (ok) {
                                                that.showProcessing(e.target);
                                                $.ajax({
                                                    url: url,
                                                    type: 'DELETE',
                                                    dataType: 'json',
                                                    data: {_token: that.options.token},
                                                    success: function (message) {
                                                        thisDropzone.removeFile(file);
                                                        $.fn.mbHelpers.showMessage(message.type, message.content);
                                                        if (typeof that.options.onDeleted === 'function') {
                                                            that.options.onDeleted(result.id, file);
                                                        }
                                                    }
                                                });
                                            }
                                        }
                                    });
                                });
                        }
                        if (typeof that.options.onSuccess === 'function') {
                            that.options.onSuccess(result.id, file);
                        }
                    });

                    thisDropzone.on("sending", function (file, xhr, formData) {
                        var title = $('.form input.img-title', file.previewElement).val(),
                            tags = $('.form input.img-tags', file.previewElement).val();
                        if (title) {
                            formData.append('title', title);
                        }
                        if (tags) {
                            formData.append('tags', tags);
                        }
                        formData.append('_token', that.options.token);
                        $('.start', file.previewElement).attr("disabled", "disabled");
                    });

                    thisDropzone.on("uploadprogress", function (file, progress, bytesSent) {
                        if (that.startAll) {
                            var p = 100 * (that.totalBytesSent + bytesSent) / that.totalBytes;
                            that.totalProgress.find('.progress-bar').width(p + "%");
                        }
                    });
                }
            });

            // Add images đã upload trên server
            $.each(that.options.images, function (i, image) {
                that.add(image);
            });

            if (!that.options.autoUpload) {
                that.btn_start.click(function (e) {
                    e.preventDefault();
                    if ($(this).attr("disabled")) {
                        return;
                    }
                    that.start();
                });
            }
            that.btn_cancel.click(function (e) {
                e.preventDefault();
                if ($(this).attr("disabled")) {
                    return;
                }
                that.btn_start.attr("disabled", null);
                that.removeAll(false);
            });
            that.btn_remove_all.click(function (e) {
                e.preventDefault();
                if ($(this).attr("disabled")) {
                    return;
                }
                that.removeAllConfirm(true);
            });

            if (!that.options.manageMode) {
                that.btn_browse.click(function (e) {
                    e.preventDefault();
                    if ($(this).attr("disabled")) {
                        return;
                    }
                    var url = that.options.url_browse.replace('__EXCEPT__', that.exceptImages.join(','));
                    $(this).attr('href', url);
                    $.fn.mbHelpers.showModal($(this));
                });

                // callback for image browser
                window.modal_callback = function () {
                    //Find the iframe in the current document
                    var doc = window.$.fn.mbHelpers.getParentIframe().contents()[0];
                    //var doc = $('#mbModal iframe').contents()[0];
                    //Get the iframe's window context
                    var modalWindow = 'defaultView' in doc ? doc.defaultView : doc.parentWindow;
                    modalWindow.$('selector').data('key');
                    var images = modalWindow.$('.image.selected');
                    $.each(images, function (i, image) {
                        that.add(modalWindow.$(image).data('image'));
                    });
                }
            }

            that.reset();
        },
        add: function (image) {
            var mockFile = {
                name: 'image_' + $.now(),
                size: image.size,
                upload: {progress: 0, total: 0, bytesSent: 0},
                title: image.title,
                tag: image.tag
            };
            this.myDropzone.emit("addedfile", mockFile);
            this.myDropzone.emit("thumbnail", mockFile, image.thumb_4x);
            this.myDropzone.emit("success", mockFile, {id: image.id, link: image.url, old: true});
            this.myDropzone.emit("complete", mockFile);
            mockFile.status = Dropzone.SUCCESS;
            this.myDropzone.files.push(mockFile);
        },
        start: function () {
            var files = this.myDropzone.getFilesWithStatus(Dropzone.ADDED);
            if (files.length) {
                this.btn_start.attr("disabled", "disabled");
                this.btn_cancel.attr("disabled", null);
                this.startAll = true;
                this.totalProgress
                    .css({'filter': 'alpha(opacity=100)', 'zoom': '1', 'opacity': '1'})
                    .find('.progress-bar').width("0%");
                this.myDropzone.enqueueFiles(files);
            }
        },
        reset: function () {
            this.totalProgress
                .css({'filter': 'alpha(opacity=0)', 'zoom': '1', 'opacity': '0'})
                .find('.progress-bar').width("0%");
            this.totalBytes = 0;
            this.totalBytesSent = 0;
            this.startAll = false;
            this.btn_start.attr("disabled", "disabled");
            this.btn_cancel.attr("disabled", "disabled");
            if (!this.myDropzone.files.length) {
                this.btn_remove_all.attr("disabled", "disabled");
            }
        },
        /**
         * remove tất cả hình ảnh,
         * includeSuccess: bao gồm các file đã upload thành công (cả file chọn từ server)
         */
        removeAll: function (includeSuccess) {
            var file, _i, _len, _ref, deleted = [];
            includeSuccess = includeSuccess || false;
            _ref = this.myDropzone.files;
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                file = _ref[_i];
                if (file.status === Dropzone.SUCCESS) {
                    if (includeSuccess) {
                        this.myDropzone.removeFile(file);
                        if (this.options.manageMode) {
                            deleted.push($(file.previewElement).data('id'))
                        }
                    }
                } else {
                    this.myDropzone.removeFile(file);
                }
                /*if (file.status !== Dropzone.SUCCESS || includeSuccess) {
                 this.myDropzone.removeFile(file);
                 }*/
            }
            if (this.options.manageMode && deleted.length) {
                $.ajax({
                    url: this.options.url_delete_batch.replace('__IDS__', deleted.join(',')),
                    type: 'DELETE',
                    dataType: 'json',
                    data: {_token: this.options.token},
                    success: function (message) {
                        $.fn.mbHelpers.showMessage(message.type, message.content);
                    }
                });
            }
            return true;
        },
        removeAllConfirm: function (includeSuccess) {
            var that = this,
                removed = false,
                confirm = that.options.manageMode ? that.options.trans.remove_all_confirm_manage : that.options.trans.remove_all_confirm;
            window.bootbox.confirm({
                message: "<div class=\"message-delete\"><div class=\"confirm\">" + confirm + "</div></div>",
                title: that.options.trans.remove_all + '?',
                buttons: {
                    cancel: {label: that.options.trans.cancel, className: "btn-default btn-white"},
                    confirm: {label: that.options.trans.ok, className: "btn-danger"}
                },
                callback: function (ok) {
                    if (ok) {
                        that.removeAll(includeSuccess);
                    }
                    removed = ok;
                }
            });
            return removed;
        },
        showProcessing: function (e) {
            $(e).parents('.dz-image-preview').addClass('dz-my-processing');
        },
        hideProcessing: function (e) {
            $(e).parents('.dz-image-preview').removeClass('dz-my-processing');
        },
        clear: function () {
            var file, _i, _len, _ref
            _ref = this.myDropzone.files;
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                file = _ref[_i];
                this.myDropzone.removeFile(_ref[_i]);
            }
        }
    };

    Dropzone.autoDiscover = false;
    $.fn.mbDropzone = function (options) {
        var lists = this,
            retval = this;
        lists.each(function () {
            var plugin = $(this).data("mbDropzone");

            if (!plugin) {
                $(this).data("mbDropzone", new MbDropzone(this, options));
            } else {
                if (typeof options === 'string' && typeof plugin[options] === 'function') {
                    retval = plugin[options]();
                }
            }
        });

        return retval || lists;
    };
})(jQuery, window);