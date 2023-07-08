@extends('la.layouts.app')

@section('contentheader_title')
    <a @ajaxload href="{{ url(config('laraadmin.adminRoute') . '/customers') }}">@lang('la_customer.customers')</a> :
@endsection
@section('contentheader_description', $customer->$view_col)
@section('section', app('translator')->get('la_customer.customers'))
@section('section_url', url(config('laraadmin.adminRoute') . '/customers'))
@section('sub_section', app('translator')->get('common.edit'))

@section('htmlheader_title', app('translator')->get('la_customer.customer_edit') . ' : ' . $customer->$view_col)

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
                    {!! Form::model($customer, [
                        'route' => [config('laraadmin.adminRoute') . '.customers.update', $customer->id],
                        'method' => 'PUT',
                        'id' => 'customer-edit-form',
                    ]) !!}
                    @la_input($module, 'name')
                    @la_input($module, 'designation')
                    @la_input($module, 'organization')
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

                    <div class="form-group">
                        <label for="create_user">Create user :</label>
                        <input class="form-control" name="create_user" type="checkbox" style="display: none;">
                        <div class="Switch Round" style="vertical-align:top;margin-left:10px;">
                            <div class="Toggle"></div>
                        </div>
                    </div>
                    <br>
                    <div class="form-group">
                        {!! Form::button(app('translator')->get('common.update'), ['class' => 'btn btn-success', 'type' => 'submit']) !!} <a @ajaxload href="{{ url(config('laraadmin.adminRoute') . '/customers') }}"
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
            @la_access('Customers', 'edit')
            // Edit Customer REST Request
            submitBtn = $('#customer-edit-form button[type=submit]');
            formObj = $("#customer-edit-form");

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
                                show_success("Customer Update", data);
                            } else {
                                show_failure("Customer Update", data);
                            }
                            submitBtn.html('Update');
                            submitBtn.prop('disabled', false);
                            if (isset(data.redirect)) {
                                window.location.href = data.redirect;
                            }
                        },
                        error: function(data) {
                            show_failure("Customer Update", data);
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
