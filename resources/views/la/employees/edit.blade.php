@extends('la.layouts.app')

@section('contentheader_title')
    <a @ajaxload href="{{ url(config('laraadmin.adminRoute') . '/employees') }}">@lang('la_employee.employees')</a> :
@endsection
@section('contentheader_description', $employee->$view_col)
@section('section', app('translator')->get('la_employee.employees'))
@section('section_url', url(config('laraadmin.adminRoute') . '/employees'))
@section('sub_section', app('translator')->get('common.edit'))

@section('htmlheader_title', app('translator')->get('la_employee.employee_edit') . ' : ' . $employee->$view_col)

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
                    {!! Form::model($employee, [
                        'route' => [config('laraadmin.adminRoute') . '.employees.update', $employee->id],
                        'method' => 'PUT',
                        'id' => 'employee-edit-form',
                    ]) !!}
                    @la_form($module)

                    {{--
                    @la_input($module, 'name')
                    @la_input($module, 'designation')
                    @la_input($module, 'gender')
                    @la_input($module, 'phone_primary')
                    @la_input($module, 'phone_secondary')
                    @la_input($module, 'email_primary')
                    @la_input($module, 'email_secondary')
                    @la_input($module, 'profile_img')
                    @la_input($module, 'city')
                    @la_input($module, 'address')
                    @la_input($module, 'about')
                    @la_input($module, 'date_birth')
                    --}}
                    <div class="form-group fg-select2">
                        <label for="role">Role* :</label>
                        <select class="form-control" required="1" data-placeholder="Select Role" rel="select2" name="role[]" multiple=true>
                            @php $roles = App\Models\Role::ctype('Employee', 'Object'); @endphp
                            @foreach ($roles as $role)
                                @if ($role->id != 1)
                                    @if (isset($user->id) && $user->hasRole($role->name))
                                        <option value="{{ $role->id }}" selected>{{ $role->name }}</option>
                                    @else
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endif
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <br>
                    <div class="form-group">
                        {!! Form::button(app('translator')->get('common.update'), ['class' => 'btn btn-success', 'type' => 'submit']) !!} <a @ajaxload href="{{ url(config('laraadmin.adminRoute') . '/employees') }}"
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
            @la_access('Employees', 'edit')
            // Edit Employee REST Request
            submitBtn = $('#employee-edit-form button[type=submit]');
            formObj = $("#employee-edit-form");

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
                                show_success("Employee Update", data);
                            } else {
                                show_failure("Employee Update", data);
                            }
                            submitBtn.html('Update');
                            submitBtn.prop('disabled', false);
                            if (isset(data.redirect)) {
                                window.location.href = data.redirect;
                            }
                        },
                        error: function(data) {
                            show_failure("Employee Update", data);
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
