@extends('backend.layouts.main')
@section('content')
<div id="dropzone-manage"></div>
@stop

@section('script')
    <script type="text/javascript">
        $(document).ready(function () {
            // Thay đổi các giá trị mặc định, xem trong jquery.dopzone.manage.js
            $('#dropzone-manage').mbDropzone({
                manageMode: true,
                all_tags: '{!!$all_tags!!}'
            });
        });
    </script>
@stop