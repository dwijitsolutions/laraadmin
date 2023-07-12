@extends('la.layouts.app')

@section('contentheader_title')
    <a @ajaxload href="{{ url(config('laraadmin.adminRoute') . '/employees') }}">@lang('la_employee.employees')</a> :
@endsection
@section('contentheader_description', app('translator')->get('la_employee.employee_listing'))
@section('section', app('translator')->get('la_employee.employees'))
@section('sub_section', app('translator')->get('common.listing'))
@section('htmlheader_title', app('translator')->get('la_employee.employee_listing'))

@section('headerElems')
    @la_access('Employees', 'create')
        <button class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#AddModal">@lang('la_employee.employee_add')</button>
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
            <table id="dt_employees" class="table table-bordered">
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

    @la_access('Employees', 'create')
        <div class="modal fade" id="AddModal" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">@lang('la_employee.employee_add')</h4>
                    </div>
                    {!! Form::open(['action' => 'App\Http\Controllers\LA\EmployeesController@store', 'id' => 'employee-add-form']) !!}
                    <div class="modal-body">
                        <div class="box-body">
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
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('common.close')</button>
                        {!! Form::button(app('translator')->get('common.save'), ['class' => 'btn btn-success', 'type' => 'submit']) !!}
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
        var dt_employees = null;
        var submitBtn = null;
        var formObj = null;

        $(function() {
            dt_employees = $("#dt_employees").DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ url(config('laraadmin.adminRoute') . '/employee_dt_ajax') }}",
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
                        orderable: false,
                        targets: [-1]
                    }],
                @endif
            });

            @la_access("Employees", "create")
                // Create New Employee REST Request
                submitBtn = $('#employee-add-form button[type=submit]');
                formObj = $("#employee-add-form");

                formObj.validate({
                    submitHandler: function(form, event) {
                        event.preventDefault();
                        $.ajax({
                            url: formObj.attr('action'),
                            method: 'POST',
                            contentType: 'json',
                            headers: {
                                'X-CSRF-Token': '{{ csrf_token() }}'
                            },
                            data: getFormDataJSON(formObj),
                            beforeSend: function() {
                                submitBtn.html('<i class="fa fa-refresh fa-spin mr5"></i> Creating...');
                                submitBtn.prop('disabled', true);
                            },
                            success: function(data) {
                                console.log(data);
                                if (data.status == "success") {
                                    show_success("Employee Create", data);
                                    $('#AddModal').modal('hide')
                                    if (isset(data.redirect)) {
                                        window.location.href = data.redirect;
                                    }
                                } else {
                                    show_failure("Employee Create", data);
                                }
                                submitBtn.html('Save');
                                submitBtn.prop('disabled', false);
                            },
                            error: function(data) {
                                console.error(data);
                                show_failure("Employee Create", data);
                                submitBtn.html('Save');
                                submitBtn.prop('disabled', false);
                            }
                        });
                        return false;
                    }
                });
            @endla_access

            @la_access("Employees", "edit")
                // Section for Updating fields via X-editable
                dt_employees.on('draw', function() {
                    $('.update_field').editable({
                        container: 'body',
                        validate: function(value) {
                            var id = $(this).attr('id');
                            var field_name = $(this).attr('field_name');
                            // Make your validations here
                            if ($.trim(value) == '') {
                                return 'This field is required';
                            }
                            var formData = {};
                            formData[field_name] = value;
                            $.ajax({
                                url: "{{ url(config('laraadmin.adminRoute')) }}/employees/" + id,
                                method: 'PUT',
                                contentType: 'json',
                                headers: {
                                    'X-CSRF-Token': '{{ csrf_token() }}'
                                },
                                data: JSON.stringify(formData),
                                success: function(data) {
                                    if (data.status == "success") {
                                        show_success("Employee Update", data);
                                    } else {
                                        show_failure("Employee Update", data);
                                    }
                                    if (isset(data.redirect)) {
                                        // window.location.href = data.redirect;
                                    }
                                },
                                error: function(data) {
                                    show_failure("Employee Update", data);
                                    if (isset(data.redirect)) {
                                        window.location.href = data.redirect;
                                    }
                                }
                            });
                        }
                    });
                });
            @endla_access
        });
    </script>
@endpush
