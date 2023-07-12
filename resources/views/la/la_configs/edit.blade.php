@extends('la.layouts.app')

@section('contentheader_title', app('translator')->get('la_configure.configurations'))
@section('contentheader_description', '')
@section('section', app('translator')->get('la_configure.configurations'))
@section('sub_section', '')
@section('htmlheader_title', app('translator')->get('la_configure.configurations'))

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
                    <form action="{{ url(config('laraadmin.adminRoute') . '/la_configs/edit_save/' . $config->id) }}" id="config-edit-form" method="post"
                        accept-charset="utf-8">
                        {{ csrf_field() }}
                        {{ Form::hidden('config_id', $config->id) }}

                        <div class="form-group">
                            <label for="label">Label :</label>
                            {{ Form::text('label', $config->label, ['class' => 'form-control', 'placeholder' => 'Label', 'data-rule-minlength' => 2, 'data-rule-maxlength' => 20, 'required' => 'required']) }}
                        </div>

                        <div class="form-group">
                            <label for="colname">Column Name :</label>
                            {{ Form::text('key', $config->key, ['class' => 'form-control', 'placeholder' => 'Column Name (lowercase)', 'data-rule-minlength' => 2, 'data-rule-maxlength' => 20, 'data-rule-banned-words' => 'true', 'required' => 'required']) }}
                        </div>

                        <div class="form-group">
                            <label for="colname">Section :</label>
                            {{ Form::text('section', $config->section, ['class' => 'form-control', 'id' => 'config_section', 'autocomplete' => 'on', 'placeholder' => 'Config Section', 'data-rule-minlength' => 2, 'data-rule-maxlength' => 50, 'required' => 'required']) }}
                        </div>

                        <div class="form-group">
                            <label for="field_type">UI Type:</label>
                            {{ Form::select('field_type', $ftypes, $config->field_type, ['class' => 'form-control', 'required' => 'required']) }}
                        </div>

                        <div id="length_div">
                            <div class="form-group">
                                <label for="minlength">Minimum :</label>
                                {{ Form::number('minlength', $config->minlength, ['class' => 'form-control', 'placeholder' => 'Default Value']) }}
                            </div>

                            <div class="form-group">
                                <label for="maxlength">Maximum :</label>
                                {{ Form::number('maxlength', $config->maxlength, ['class' => 'form-control', 'placeholder' => 'Default Value']) }}
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="required">Required:</label>
                            {{ Form::checkbox('required', 'required', $config->required) }}
                            <div class="Switch Round Off" style="vertical-align:top;margin-left:10px;">
                                <div class="Toggle"></div>
                            </div>
                        </div>

                        <div class="form-group values">
                            <label for="popup_vals">Values :</label>
                            <?php
                            $default_val = '';
                            $popup_value_type_table = false;
                            $popup_value_type_list = false;
                            if (str_starts_with($config->popup_vals, '@')) {
                                $popup_value_type_table = true;
                                $default_val = str_replace('@', '', $config->popup_vals);
                            } elseif (str_starts_with($config->popup_vals, '[')) {
                                $popup_value_type_list = true;
                                $default_val = json_decode($config->popup_vals);
                            }
                            ?>
                            <div class="radio" style="margin-bottom:20px;">
                                <label>{{ Form::radio('popup_value_type', 'table', $popup_value_type_table) }} From Table</label>
                                <label>{{ Form::radio('popup_value_type', 'list', $popup_value_type_list) }} From List</label>
                            </div>
                            {{ Form::select('popup_vals_table', $tables, $default_val, ['class' => 'form-control', 'rel' => '']) }}

                            <select class="form-control popup_vals_list" rel="taginput" multiple="1"
                                data-placeholder="Add Multiple values (Press Enter to add)" name="popup_vals_list[]">
                                @if (is_array($default_val))
                                    @foreach ($default_val as $value)
                                        <option selected>{{ $value }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <br>
                        <div class="form-group">
                            {!! Form::submit('Update', ['class' => 'btn btn-success']) !!} <a @ajaxload href="{{ url(config('laraadmin.adminRoute') . '/la_configs') }}"
                                class="btn btn-default pull-right">Cancel</a>
                        </div>
                        {!! Form::close() !!}

                        @if ($errors->any())
                            <ul class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        {{-- .ui-autocomplete-input {
  border: none;
  font-size: 14px;
  width: 300px;
  height: 24px;
  margin-bottom: 5px;
  padding-top: 2px;
  border: 1px solid #DDD !important;
  padding-top: 0px !important;
  z-index: 1511;
  position: relative;
} --}} .ui-menu .ui-menu-item a {
            font-size: 12px;
            padding: 6px;
        }

        .ui-autocomplete {
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 1051 !important;
            float: left;
            display: none;
            min-width: 160px;
            _width: 160px;
            padding: 4px 0;
            margin: 2px 0 0 0;
            list-style: none;
            background-color: #ffffff;
            border-color: #ccc;
            border-color: rgba(0, 0, 0, 0.2);
            border-style: solid;
            border-width: 1px;
            -webkit-border-radius: 2px;
            -moz-border-radius: 2px;
            border-radius: 2px;
            -webkit-box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
            -moz-box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
            -webkit-background-clip: padding-box;
            -moz-background-clip: padding;
            background-clip: padding-box;
            *border-right-width: 2px;
            *border-bottom-width: 2px;
        }

        .ui-menu-item>a.ui-corner-all {
            display: block;
            padding: 3px 15px;
            clear: both;
            font-weight: normal;
            line-height: 18px;
            color: #555555;
            white-space: nowrap;
            text-decoration: none;
            padding: 6px;
        }

        .ui-state-hover,
        .ui-state-active {
            color: #ffffff;
            text-decoration: none;
            background-color: #0088cc;
            border-radius: 0px;
            -webkit-border-radius: 0px;
            -moz-border-radius: 0px;
            background-image: none;
            padding: 6px;
        }

        .ui-state {
            color: #ffffff;
            text-decoration: none;
            background-color: #0088cc;
            border-radius: 0px;
            -webkit-border-radius: 0px;
            -moz-border-radius: 0px;
            background-image: none;
            padding: 6px;
        }

        #modalIns {
            width: 500px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(function() {
            var sections = [
                @foreach ($sections as $section)
                    "{{ $section->section }}",
                @endforeach
            ];
            $("#config_section").autocomplete({
                source: sections
            });
            $("select.popup_vals_list").show();
            $("select.popup_vals_list").next().show();
            $("select[name='popup_vals_table']").hide();

            function showValuesSection() {
                var ft_val = $("select[name='field_type']").val();
                if (ft_val == 7 || ft_val == 15 || ft_val == 18 || ft_val == 20) {
                    $(".form-group.values").show();
                } else {
                    $(".form-group.values").hide();
                }

                $('#length_div').removeClass("hide");
                if (ft_val == 2 || ft_val == 4 || ft_val == 5 || ft_val == 7 || ft_val == 9 || ft_val == 11 || ft_val == 12 || ft_val == 15 ||
                    ft_val == 18 || ft_val == 21 || ft_val == 24 || ft_val == 25 || ft_val == 26 || ft_val == 27) {
                    $('#length_div').addClass("hide");
                }
            }

            $("select[name='field_type']").on("change", function() {
                showValuesSection();
            });
            showValuesSection();

            function showValuesTypes() {
                //console.log($("input[name='popup_value_type']:checked").val());
                if ($("input[name='popup_value_type']:checked").val() == "list") {
                    $("select.popup_vals_list").show();
                    $("select.popup_vals_list").next().show();
                    $("select[name='popup_vals_table']").hide();
                } else {
                    $("select[name='popup_vals_table']").show();
                    $("select.popup_vals_list").hide();
                    $("select.popup_vals_list").next().hide();
                }
            }

            $("input[name='popup_value_type']").on("change", function() {
                showValuesTypes();
            });
            showValuesTypes();

            $("#config-edit-form").validate({

            });
        });
    </script>
@endpush
