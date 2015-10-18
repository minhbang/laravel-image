@extends('backend.layouts.main')
@section('content')
<div id="dropzone-manage"></div>
@stop

@section('script')
    <script type="text/javascript">
        $(document).ready(function () {
            $('#dropzone-manage').mbDropzone({
                all_tags: '{!!$all_tags!!}',
                url_store: '{!!route('image.store')!!}',
                url_delete: '{!!route('backend.image.destroy', ['image' => '__ID__'])!!}',
                url_update: '{!!route('backend.image.quick_update', ['image' => '__ID__'])!!}',
                thumb_width: '{{config('image.thumbnail.width')*4}}',
                thumb_height: '{{config('image.thumbnail.height')*4}}',
                token: window.csrf_token,
                trans:{
                    title: '{{trans('image::common.title')}}',
                    null_title: '{{trans('image::common.null_title')}}',
                    delete_title: '{{trans('image::common.delete_title')}}',
                    delete_confirm: '{{trans('image::common.delete_confirm')}}',
                    ok: '{{trans('common.ok')}}',
                    cancel: '{{trans('common.cancel')}}',
                    delete: '{{trans('common.delete')}}',
                    upload: '{{trans('common.upload')}}',
                    add_files: '{{trans('common.add_files')}}',
                    tags: '{{trans('image::common.tags')}}'
                }
            });
        });
    </script>
@stop