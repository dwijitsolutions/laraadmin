@extends('la.layouts.app')

@section('contentheader_title')
    <a @ajaxload href="{{ url(config('laraadmin.adminRoute') . '/roles') }}">@lang('la_role.roles')</a> :
@endsection
@section('contentheader_description', $role->$view_col)
@section('section', app('translator')->get('la_role.roles'))
@section('section_url', url(config('laraadmin.adminRoute') . '/roles'))
@section('sub_section', app('translator')->get('common.edit'))

@section('htmlheader_title', app('translator')->get('la_role.role_edit') . ' : ' . $role->$view_col)

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

    <div class="box">
        <div class="box-header">

        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    {!! Form::model($role, [
                        'route' => [config('laraadmin.adminRoute') . '.roles.update', $role->id],
                        'method' => 'PUT',
                        'id' => 'role-edit-form',
                    ]) !!}
                    @la_input($module, 'name', null, null, 'form-control text-uppercase', ['placeholder' => "Role Name in CAPITAL LETTERS with '_' to JOIN e.g. 'SUPER_ADMIN'"])
                    @la_input($module, 'display_name')
                    @la_input($module, 'description')
                    @la_input($module, 'parent')
                    @la_input($module, 'context_type')
                    @la_input($module, 'dept')
                    <br>
                    <div class="form-group">
                        {!! Form::button(app('translator')->get('common.update'), ['class' => 'btn btn-success', 'type' => 'submit']) !!} <a @ajaxload href="{{ url(config('laraadmin.adminRoute') . '/roles') }}"
                            class="btn btn-default pull-right">@lang('common.cancel')</a>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        var submitBtn = null;
        var formObj = null;

        $(function() {
            @la_access('Roles', 'edit')
            // Edit Role REST Request
            submitBtn = $('#role-edit-form button[type=submit]');
            formObj = $("#role-edit-form");

            formObj.validate({
                submitHandler: function(form, event) {
                    event.preventDefault();
                    $.ajax({
                        url: formObj.attr('action'),
                        method: 'PUT',
                        contentType: 'json',
                        headers: {
                            'X-CSRF-Token': '{{ csrf_token() }}'
                        },
                        data: getFormDataJSON(formObj),
                        beforeSend: function() {
                            submitBtn.html('<i class="fa fa-refresh fa-spin mr5"></i> Updating...');
                            submitBtn.prop('disabled', true);
                        },
                        success: function(data) {
                            if (data.status == "success") {
                                show_success("Role Update", data);
                            } else {
                                show_failure("Role Update", data);
                            }
                            submitBtn.html('Update');
                            submitBtn.prop('disabled', false);
                            if (isset(data.redirect)) {
                                window.location.href = data.redirect;
                            }
                        },
                        error: function(data) {
                            show_failure("Role Update", data);
                            submitBtn.html('Update');
                            submitBtn.prop('disabled', false);
                            if (isset(data.redirect)) {
                                window.location.href = data.redirect;
                            }
                        }
                    });
                    return false;
                }
            });
            @endla_access
        });
    </script>
@endpush
