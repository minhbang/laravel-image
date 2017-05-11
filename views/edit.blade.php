@extends('backend.layouts.modal')
@section('content')
    {!! Form::open(['url' => route('backend.image.update', ['image' => $image->id]), 'files' => true]) !!}
    <div class="no-margins form-group form-image{{ $errors->has('file') ? ' has-error':'' }}">
        {!! Form::selectImage('file', ['thumbnail' => ['url' => $image->src,'width' => $image->width,'height' => $image->height]]) !!}
        @if($errors->has('file'))
            <p class="help-block">{{ $errors->first('file') }}</p>
        @endif
    </div>
    {!! Form::close() !!}
@stop

@push('scripts')
<script type="text/javascript">
    $(function () {
        $('.fileinput').on('change.bs.fileinput', function () {
            $('.fileinput-preview img').on('load', function () {
                var modal = window.parent.$("#mbModal .modal-dialog"),
                    iframe = $('iframe', modal);
                iframe.height($(this).height() + 62);
                iframe.width('100%');
            });
        });
    });
</script>
@endpush