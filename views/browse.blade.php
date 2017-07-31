@extends('kit::backend.layouts.modal')
@section('content')
    <div id="page-image-browse">
        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active">
                <a href="#image-browser" aria-controls="image-browser" role="tab" data-toggle="tab">{{trans('image::common.library')}}</a>
            </li>
            <li role="presentation">
                <a href="#image-upload" aria-controls="image-upload" role="tab" data-toggle="tab">{{trans('image::common.upload')}}</a>
            </li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active image-browser" id="image-browser"></div>
            <div role="tabpanel" class="tab-pane" id="image-upload"></div>
        </div>
    </div>
@stop

@push('scripts')
<script type="text/javascript">
    $(function () {
        var modal = window.$.fn.mbHelpers.getParentModal(),
            btnConfirm = modal.find('button[data-bb-handler="confirm"]');
        btnConfirm.addClass('disabled');
        $('#image-browser').imageBrowser({
            url_data: '{!! $url_data !!}',
            multiSelect: {{$multi == '1' ? 'true' : 'false'}},
            change: function (browser) {
                var images = browser.selected();
                if (images.length) {
                    btnConfirm.removeClass('disabled');
                }
                if (window.$.isFunction(window.parent.$.fn.mbHelpers.imageBrowserChange)) {
                    window.parent.$.fn.mbHelpers.imageBrowserChange(images);
                }
            }
        });
        $('#image-upload').mbDropzone({
            manageMode: true,
            all_tags: '{!!$all_tags!!}'
        });

        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var related = $(e.relatedTarget).attr('href');
            if (related == '#image-upload') {
                $('#image-browser').imageBrowser('reload');
            }
            if (related == '#image-browser') {
                btnConfirm.addClass('disabled');
                $('#image-upload').mbDropzone('clear');
            }
        })
    });
</script>
@endpush