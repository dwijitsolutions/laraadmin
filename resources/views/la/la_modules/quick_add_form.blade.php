<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="quick_add_title">Quick Add {{ Str::singular($module->label) }}</h4>
</div>
{!! Form::open(['id' => 'quick-add-form']) !!}
<div class="modal-body">
    <div class="box-body">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="module_id" value="{{ $module_id }}">
        <input type="hidden" name="field_name" value="{{ $field_name }}">
        <input type="hidden" name="popup_vals" value="{{ $popup_vals }}">

        @la_form($module, $fields_req)
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal" id="close_quick_add">@lang('common.close')</button>
    {!! Form::submit(app('translator')->get('common.submit'), ['class' => 'btn btn-success']) !!}
</div>
{!! Form::close() !!}
<script>
    $(document).ready(function() {

        /* ================= bootstrap-datetimepicker ================= */
        $(".input-group.date").datetimepicker({
            format: 'DD/MM/YYYY'
        });

        $(".input-group.datetime").datetimepicker({
            format: 'DD/MM/YYYY LT',
            sideBySide: true
        });

        // Null Value for Date + Datetime
        $(".input-group-addon.null_date").on("click", function(event) {
            var cb = $(this).find('.cb_null_date');
            if (cb.is(":checked")) {
                cb.prop("checked", !0);
                cb.attr("checked", !0);
                $(this).parent().find('input[type="text"]').prop('readonly', !0);
            } else {
                cb.prop("checked", !1);
                cb.attr("checked", !1);
                $(this).parent().find('input[type="text"]').prop('readonly', !1);
            }
        });

        $("#quick-add-form").validate({
            submitHandler: function(form) {
                if (popup_module_id != 0) {
                    $.ajax({
                        url: "{{ url(config('laraadmin.adminRoute') . '/quick_add_form_save') }}/" + popup_module_id,
                        method: 'POST',
                        data: $("#quick-add-form").serialize(),
                        success: function(data) {
                            console.log(popup_module_id, popup_field_name, data);
                            if (data.status == "success") {
                                $('#quick_add').modal('hide');

                                // Put New Values to Select
                                $("select[name=" + popup_field_name + "]").html('');
                                $.each(data.popup_vals, function(index, value) {
                                    var sel = "";
                                    if (data.insert_id == index) {
                                        sel = " selected";
                                    }
                                    $("#" + popup_field_name).append('<option value="' + index + '"' + sel +
                                        '>' + value + '</option>');
                                });
                            }
                        }
                    });
                    return false;
                }
            }
        });
    });
</script>
