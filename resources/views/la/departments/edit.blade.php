@extends('la.layouts.app')

@section('contentheader_title')
    <a @ajaxload href="{{ url(config('laraadmin.adminRoute') . '/departments') }}">@lang('la_department.departments')</a> :
@endsection
@section('contentheader_description', $department->$view_col)
@section('section', app('translator')->get('la_department.departments'))
@section('section_url', url(config('laraadmin.adminRoute') . '/departments'))
@section('sub_section', app('translator')->get('common.edit'))

@section('htmlheader_title', app('translator')->get('la_department.department_edit') . ' : ' . $department->$view_col)

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
                    {!! Form::model($department, [
                        'route' => [config('laraadmin.adminRoute') . '.departments.update', $department->id],
                        'method' => 'PUT',
                        'id' => 'department-edit-form',
                    ]) !!}
                    @la_form($module)

                    {{--
                    @la_input($module, 'name')
                    @la_input($module, 'tags')
                    @la_input($module, 'color')
                    --}}
                    <br>
                    <div class="form-group">
                        {!! Form::button(app('translator')->get('common.update'), ['class' => 'btn btn-success', 'type' => 'submit']) !!} <a @ajaxload href="{{ url(config('laraadmin.adminRoute') . '/departments') }}"
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
            @la_access('Departments', 'edit')
            // Edit Department REST Request
            submitBtn = $('#department-edit-form button[type=submit]');
            formObj = $("#department-edit-form");

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
                                show_success("Department Update", data);
                            } else {
                                show_failure("Department Update", data);
                            }
                            submitBtn.html('Update');
                            submitBtn.prop('disabled', false);
                            if (isset(data.redirect)) {
                                window.location.href = data.redirect;
                            }
                        },
                        error: function(data) {
                            show_failure("Department Update", data);
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
