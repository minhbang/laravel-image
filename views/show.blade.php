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
                    <td>{{ trans('image::common.dimensions') }}</td>
                    <td>{{$image->present()->dimensions}}</td>
                </tr>
                <tr>
                    <td>{{ trans('image::common.mime') }}</td>
                    <td>{{$image->present()->mime}}</td>
                </tr>
                <tr>
                    <td>{{ trans('image::common.size') }}</td>
                    <td>{{$image->present()->size}}</td>
                </tr>
                <tr>
                    <td>{{ trans('image::common.used') }}</td>
                    <td>{{$image->used}}</td>
                </tr>
                <tr>
                    <td>{{ trans('common.created_at') }}</td>
                    <td>{!! $image->present()->createdAt !!}</td>
                </tr>
                <tr>
                    <td>{{ trans('common.updated_at') }}</td>
                    <td>{!! $image->present()->updatedAt !!}</td>
                </tr>
            </table>
        </div>
        <div class="col-xs-8">
            <table class="table table-hover table-striped table-bordered table-detail">
                <tr>
                    <td>{{ trans('image::common.title') }}</td>
                    <td>{{$image->title}}</td>
                </tr>
                <tr>
                    <td>{{ trans('image::common.tags') }}</td>
                    <td>{!!$image->present()->tags!!}</td>
                </tr>
                <tr>
                    <td>{{ trans('image::common.images') }}</td>
                    <td>{!!$image->present()->image!!}</td>
                </tr>
            </table>
        </div>
    </div>

@stop