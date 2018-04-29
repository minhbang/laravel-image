@extends('kit::backend.layouts.master')
@section('content')
    <div class="ibox ibox-table">
        <div class="ibox-title">
            <h5>{!! __('Images list <small>oder by updated time</small>') !!}</h5>
            <div class="buttons">
                {!! Html::linkButton('#', __('Filter'), ['class'=>'advanced_filter_collapse','type'=>'info', 'size'=>'xs', 'icon' => 'filter']) !!}
                {!! Html::linkButton('#', __('All'), ['class'=>'advanced_filter_clear', 'type'=>'warning', 'size'=>'xs', 'icon' => 'list']) !!}
                {!! Html::linkButton(route('backend.image.upload'), __('Add image'), ['type'=>'success', 'size'=>'xs', 'icon' => 'plus-sign']) !!}
            </div>
        </div>
        <div class="ibox-content">
            <div class="bg-warning dataTables_advanced_filter hidden">
                <form class="form-horizontal" role="form">
                    {!! Form::hidden('filter_form', 1) !!}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('filter_created_at', __('Created at'), ['class' => 'col-md-3 control-label']) !!}
                                <div class="col-md-9">
                                    {!! Form::daterange('filter_created_at', [], ['class' => 'form-control']) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('filter_updated_at', __('Updated at'), ['class' => 'col-md-3 control-label']) !!}
                                <div class="col-md-9">
                                    {!! Form::daterange('filter_updated_at', [], ['class' => 'form-control']) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            {!! $html->table(['id' => 'image-manage']) !!}
        </div>
    </div>
@endsection

@push('scripts')
<script type="text/javascript">
    window.all_tags = '{{$all_tags}}';
    window.datatableDrawCallback = function (dataTableApi) {
        dataTableApi.$('a.quick-update').quickUpdate({
            'url': '{{ route('backend.image.quick_update', ['image' => '__ID__']) }}',
            'container': '#image-manage',
            'dataTableApi': dataTableApi,
            afterShow: function (element, form) {
                if ($(element).hasClass('a-tags')) {
                    $('input._value', form).selectize_tags({options: window.all_tags});
                }
            },
            processResult: function (element, result) {
                window.all_tags = result;
            }
        });
    };
    window.settings.mbDatatables = {
        trans: {
            name: '{{__('Image')}}'
        }
    }
</script>
{!! $html->scripts() !!}
@endpush