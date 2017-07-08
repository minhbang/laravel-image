@extends('kit::backend.layouts.modal')
@section('content')
    <div id="image-browser" class="image-browser"></div>
@stop

@push('scripts')
    <script type="text/javascript">
        $(function() {
            $('#image-browser').imageBrowser({
                url_data: '{!! $url_data !!}'
            });
        });
    </script>
@endpush