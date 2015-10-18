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

@section('script')
    <script type="text/javascript">
        $(function() {
            // 15: body padding top
            $('.fileinput').on('change.bs.fileinput', function () {
                window.parent.$("#mbModal").find('iframe').height($('form').height()+15+1);
            });
        });
    </script>
@stop