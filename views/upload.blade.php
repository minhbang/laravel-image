@extends('kit::backend.layouts.master')
@section('content')
<div id="dropzone-manage"></div>
@stop

@push('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            // Thay đổi các giá trị mặc định, xem trong jquery.dopzone.manage.js
            $('#dropzone-manage').mbDropzone({
                manageMode: true,
                all_tags: '{!!$all_tags!!}'
            });
        });
    </script>
@endpush