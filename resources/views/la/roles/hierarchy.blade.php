@extends('la.layouts.app')
<?php
use App\Models\LAModule;
?>
@section('contentheader_title', app('translator')->get('la_role.roles'))
@section('contentheader_description', app('translator')->get('la_role.role_listing'))
@section('section', app('translator')->get('la_role.roles'))
@section('sub_section', app('translator')->get('common.listing'))
@section('htmlheader_title', app('translator')->get('la_role.role_listing'))

@section('headerElems')
    @la_access('Roles', 'create')
        <button class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#AddModal">@lang('la_role.role_add')</button>
    @endla_access
    <a href="{{ url(config('laraadmin.adminRoute') . '/roles') }}" class="btn btn-primary btn-sm pull-right mr5">@lang('la_role.list_view')</a>
@endsection

@section('main-content')

    <div class="box box-success menus">
        <!--<div class="box-header"></div>-->
        <div class="box-body">
            <div class="row">
                <div class="col-md-3 col-lg-3 col-md-offset-4 col-lg-offset-4">
                    <div class="dd" id="menu-nestable">
                        <ol class="dd-list">
                            @foreach ($parent_roles as $role)
                                <?php
                                echo LAHelper::print_roles($role);
                                ?>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@la_access('Roles', 'create')
    <div class="modal fade" id="AddModal" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">@lang('la_role.role_add')</h4>
                </div>
                {!! Form::open(['action' => 'App\Http\Controllers\LA\RolesController@store', 'id' => 'role-add-form']) !!}
                <div class="modal-body">
                    <div class="box-body">
                        @la_input($module, 'name', null, null, 'form-control text-uppercase', ['placeholder' => "Role Name in CAPITAL LETTERS with '_' to JOIN e.g. 'SUPER_ADMIN'"])
                        @la_input($module, 'display_name')
                        @la_input($module, 'description')
                        @la_input($module, 'parent')
                        @la_input($module, 'context_type')
                        @la_input($module, 'dept')
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

@push('scripts')
    <script>
        $(function() {

            $('#menu-nestable').nestable({
                group: 1
            });
            $('#menu-nestable').on('change', function() {
                var jsonData = $('#menu-nestable').nestable('serialize');
                // console.log(jsonData);
                $.ajax({
                    url: "{{ url(config('laraadmin.adminRoute') . '/update_role_hierarchy') }}",
                    method: 'POST',
                    data: {
                        jsonData: jsonData,
                        "_token": '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        // console.log(data);
                    }
                });
            });
            $("#menu-custom-form").validate({

            });

            $("#role-add-form").validate({

            });
        });
    </script>
@endpush
