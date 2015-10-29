@extends('backend.layouts.modal')
@section('content')
    <div id="image-browser" class="image-browser"></div>
@stop

@section('script')
    <script type="text/javascript">
        $(function() {
            $('#image-browser').imageBrowser({
                url_data: '{!! $url_data !!}'
            });
        });
    </script>
@stop