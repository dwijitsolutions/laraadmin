@extends('la.layouts.app')

@section('contentheader_title')
    <a @ajaxload href="{{ url(config('laraadmin.adminRoute') . '/users') }}">@lang('la_user.users')</a> :
@endsection
@section('contentheader_description', app('translator')->get('la_user.user_listing'))
@section('section', app('translator')->get('la_user.users'))
@section('sub_section', app('translator')->get('common.listing'))
@section('htmlheader_title', app('translator')->get('la_user.user_listing'))

@section('headerElems')
    @la_access('Users', 'create')
        <button class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#AddModal">@lang('la_user.user_add')</button>
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
            <table id="dt_users" class="table table-bordered">
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
            </table>
        </div>
    </div>

@endsection

@push('styles')
@endpush

@push('scripts')
    <script>
        var dt_users = null;
        $(function() {
            dt_users = $("#dt_users").DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ url(config('laraadmin.adminRoute') . '/user_dt_ajax') }}",
                pageLength: 100,
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
                        "orderable": false,
                        "targets": [-1]
                    }],
                @endif
            });
            $("#user-add-form").validate({

            });
        });
    </script>
@endpush
