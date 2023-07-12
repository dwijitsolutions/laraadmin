@extends('la.layouts.app')

@section('contentheader_title')
    <a @ajaxload href="{{ url(config('laraadmin.adminRoute') . '/blog_categories') }}">@lang('la_blog_category.blog_categories')</a> :
@endsection
@section('contentheader_description', app('translator')->get('la_blog_category.blog_category_listing'))
@section('section', app('translator')->get('la_blog_category.blog_categories'))
@section('sub_section', app('translator')->get('common.listing'))
@section('htmlheader_title', app('translator')->get('la_blog_category.blog_category_listing'))

@section('headerElems')
    @la_access('Blog_categories', 'create')
        <button class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#AddModal">@lang('la_blog_category.blog_category_add')</button>
    @endla_access
@endsection

@section('main-content')

    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="box box-success">
        <!--<div class="box-header"></div>-->
        <div class="box-body">
            <table id="dt_blog_categories" class="table table-bordered">
                <thead>
                    <tr class="success">
                        @foreach ($listing_cols as $col)
                            <th>{{ $module->fields[$col]['label'] ?? ucfirst($col) }}</th>
                        @endforeach
                        @if ($show_actions)
                            <th>@lang('common.actions')</th>
                        @endif
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>

    @la_access('Blog_categories', 'create')
        <div class="modal fade" id="AddModal" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">@lang('la_blog_category.blog_category_add')</h4>
                    </div>
                    {!! Form::open(['action' => 'App\Http\Controllers\LA\BlogCategoriesController@store', 'id' => 'blog_category-add-form']) !!}
                    <div class="modal-body">
                        <div class="box-body">
                            @la_form($module)

                            {{--
                            @la_input($module, 'name')
                            @la_input($module, 'url')
                            @la_input($module, 'description')
                            --}}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('common.close')</button>
                        {!! Form::submit(app('translator')->get('common.save'), ['class' => 'btn btn-success']) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    @endla_access

@endsection

@push('styles')
@endpush

@push('scripts')
    <script>
        var dt_blog_categories = null;
        $(function() {
            dt_blog_categories = $("#dt_blog_categories").DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ url(config('laraadmin.adminRoute') . '/blog_category_dt_ajax') }}",
                language: {
                    lengthMenu: "_MENU_",
                    search: "_INPUT_",
                    searchPlaceholder: '@lang('common.search')'
                },
                columns: [
                    @foreach ($listing_cols as $col)
                        {
                            data: '{{ $col }}',
                            name: '{{ $col }}'
                        },
                    @endforeach
                    @if ($show_actions)
                        {
                            data: 'dt_action',
                            name: 'dt_action',
                        },
                    @endif
                ],
                @if ($show_actions)
                    columnDefs: [{
                        orderable: false,
                        targets: [-1]
                    }],
                @endif
            });
            $("#blog_category-add-form").validate({

            });
        });
    </script>
@endpush
