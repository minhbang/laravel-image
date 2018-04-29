@extends('kit::backend.layouts.modal')
@section('content')
    <div class="row">
        <div class="col-xs-4">
            <table class="table table-hover table-striped table-bordered table-detail">
                <tr>
                    <td>ID</td>
                    <td><strong>{{ $image->id}}</strong></td>
                </tr>
                <tr>
                    <td>{{ __('Dimensions') }}</td>
                    <td>{{$image->present()->dimensions}}</td>
                </tr>
                <tr>
                    <td>{{ __('Mime') }}</td>
                    <td>{{$image->present()->mime}}</td>
                </tr>
                <tr>
                    <td>{{ __('Size') }}</td>
                    <td>{{$image->present()->size}}</td>
                </tr>
                <tr>
                    <td>{{ __('Used') }}</td>
                    <td>{{$image->used}}</td>
                </tr>
                <tr>
                    <td>{{ __('Created at') }}</td>
                    <td>{!! $image->present()->createdAt !!}</td>
                </tr>
                <tr>
                    <td>{{ __('Updated at') }}</td>
                    <td>{!! $image->present()->updatedAt !!}</td>
                </tr>
            </table>
        </div>
        <div class="col-xs-8">
            <table class="table table-hover table-striped table-bordered table-detail">
                <tr>
                    <td>{{ __('Title') }}</td>
                    <td>{{$image->title}}</td>
                </tr>
                <tr>
                    <td>{{ __('Tags') }}</td>
                    <td>{!!$image->present()->tags!!}</td>
                </tr>
                <tr>
                    <td>{{ __('Images') }}</td>
                    <td>{!!$image->present()->image!!}</td>
                </tr>
            </table>
        </div>
    </div>

@stop