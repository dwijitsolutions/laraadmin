@extends('la.layouts.app')

@section('htmlheader_title')
    @lang('la_role.role_view')
@endsection

@section('main-content')
    <div id="page-content" class="profile2">
        <div class="bg-primary clearfix">
            <div class="col-md-4">
                <div class="row">
                    <div class="col-md-3">
                        <!--<img class="profile-image" src="{{ asset('la-assets/img/avatar5.png') }}" alt="">-->
                        <div class="profile-icon text-primary"><i class="fa {{ $module->fa_icon }}"></i></div>
                    </div>
                    <div class="col-md-9">
                        <h4 class="name">{{ $role->$view_col }}</h4>
                        <div class="row stats">
                            <div class="col-md-4"><i class="fa fa-facebook"></i> 234</div>
                            <div class="col-md-4"><i class="fa fa-twitter"></i> 12</div>
                            <div class="col-md-4"><i class="fa fa-instagram"></i> 89</div>
                        </div>
                        <p class="desc">Test Description in one line</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dats1">
                    <div class="label2">Admin</div>
                </div>
                <div class="dats1"><i class="fa fa-envelope-o"></i> superadmin@gmail.com</div>
                <div class="dats1"><i class="fa fa-map-marker"></i> Pune, India</div>
            </div>
            <div class="col-md-4">
                <!--
                                                            <div class="teamview">
                                                                <a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user1-128x128.jpg') }}" alt=""><i class="status-online"></i></a>
                                                                <a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user2-160x160.jpg') }}" alt=""></a>
                                                                <a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user3-128x128.jpg') }}" alt=""></a>
                                                                <a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user4-128x128.jpg') }}" alt=""><i class="status-online"></i></a>
                                                                <a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user5-128x128.jpg') }}" alt=""></a>
                                                                <a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user6-128x128.jpg') }}" alt=""></a>
                                                                <a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user7-128x128.jpg') }}" alt=""></a>
                                                                <a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user8-128x128.jpg') }}" alt=""></a>
                                                                <a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user5-128x128.jpg') }}" alt=""></a>
                                                                <a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user6-128x128.jpg') }}" alt=""><i class="status-online"></i></a>
                                                                <a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user7-128x128.jpg') }}" alt=""></a>
                                                            </div>
                                                            -->
                <div class="dats1 pb">
                    <div class="clearfix">
                        <span class="pull-left">Task #1</span>
                        <small class="pull-right">20%</small>
                    </div>
                    <div class="progress progress-xs active">
                        <div class="progress-bar progress-bar-warning progress-bar-striped" style="width: 20%" role="progressbar" aria-valuenow="20"
                            aria-valuemin="0" aria-valuemax="100">
                            <span class="sr-only">20% Complete</span>
                        </div>
                    </div>
                </div>
                <div class="dats1 pb">
                    <div class="clearfix">
                        <span class="pull-left">Task #2</span>
                        <small class="pull-right">90%</small>
                    </div>
                    <div class="progress progress-xs active">
                        <div class="progress-bar progress-bar-warning progress-bar-striped" style="width: 90%" role="progressbar" aria-valuenow="90"
                            aria-valuemin="0" aria-valuemax="100">
                            <span class="sr-only">90% Complete</span>
                        </div>
                    </div>
                </div>
                <div class="dats1 pb">
                    <div class="clearfix">
                        <span class="pull-left">Task #3</span>
                        <small class="pull-right">60%</small>
                    </div>
                    <div class="progress progress-xs active">
                        <div class="progress-bar progress-bar-warning progress-bar-striped" style="width: 60%" role="progressbar" aria-valuenow="60"
                            aria-valuemin="0" aria-valuemax="100">
                            <span class="sr-only">60% Complete</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-1 actions">
                @la_access('Roles', 'edit')
                    <a @ajaxload href="{{ url(config('laraadmin.adminRoute') . '/roles/' . $role->id . '/edit') }}"
                        class="btn btn-xs btn-edit btn-default"><i class="fa fa-pencil"></i></a><br>
                @endla_access

                @la_access('Roles', 'delete')
                    {{ Form::open(['route' => [config('laraadmin.adminRoute') . '.roles.destroy', $role->id], 'method' => 'delete', 'style' => 'display:inline']) }}
                    <button class="btn btn-default btn-delete btn-xs" type="submit"><i class="fa fa-times"></i></button>
                    {{ Form::close() }}
                @endla_access
            </div>
        </div>

        <ul data-toggle="ajax-tab" class="nav nav-tabs profile" role="tablist">
            <li class=""><a @ajaxload href="{{ url(config('laraadmin.adminRoute') . '/roles') }}" data-toggle="tooltip" data-placement="right"
                    title="@lang('la_role.back_to_roles')"><i class="fa fa-chevron-left"></i></a></li>
            <li class="active"><a role="tab" data-toggle="tab" class="active" href="#tab-info" data-target="#tab-info"><i
                        class="fa fa-bars"></i> @lang('common.general_info')</a></li>
            <li class=""><a role="tab" data-toggle="tab" href="#tab-timeline" data-target="#tab-timeline"><i class="fa fa-clock-o"></i>
                    @lang('common.timeline')</a></li>
            @role('SUPER_ADMIN')
                <li class=""><a role="tab" data-toggle="tab" href="#tab-access" data-target="#tab-access"><i class="fa fa-key"></i>
                        @lang('common.access')</a></li>
                <li class=""><a role="tab" data-toggle="tab" href="#tab-Users" data-target="#tab-Users"><i
                            class="fa {{ $module_users->fa_icon }}"></i> {{ $module_users->lang_name() }}</a></li>
            @endrole
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active fade in" id="tab-info">
                <div class="tab-content">
                    <div class="panel infolist">
                        <div class="panel-default panel-heading">
                            <h4>@lang('common.general_info')</h4>
                        </div>
                        <div class="panel-body">
                            @la_display($module, 'name')
                            @la_display($module, 'display_name')
                            @la_display($module, 'description')
                            @la_display($module, 'parent')
                            @la_display($module, 'context_type')
                            @la_display($module, 'dept')
                        </div>
                    </div>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade in p20 bg-white" id="tab-timeline">
                @php
                    $timeline = $role->timeline();
                @endphp
                @if (count($timeline) > 0)
                    <ul class="timeline timeline-inverse">
                        @foreach ($timeline as $log)
                            @php
                                $icon = 'fa-clock-o';
                                $iconColor = 'bg-blue';
                                if (str_contains($log->type, 'CREATED')) {
                                    $icon = 'fa-plus';
                                    $iconColor = 'bg-green';
                                } elseif (str_contains($log->type, 'UPDATED')) {
                                    $icon = 'fa-pencil';
                                    $iconColor = 'bg-orange';
                                } elseif (str_contains($log->type, 'DELETED')) {
                                    $icon = 'fa-times';
                                    $iconColor = 'bg-red';
                                }

                            @endphp
                            <li class="time-label"><span class="{{ $iconColor }}">{{ date('d M, Y', strtotime($log->created_at)) }}</span></li>
                            <li>
                                <i class="fa {{ $icon }} {{ $iconColor }}"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fa fa-clock-o"></i> {{ date('g:ia', strtotime($log->created_at)) }}</span>
                                    <h3 class="timeline-header no-border">{{ $log->title }}</h3>
                                    @if (!str_contains($log->type, 'DELETED'))
                                        <div class="timeline-body">
                                            @php
                                                $log_content = json_decode($log->content, true);
                                            @endphp
                                            <ul class="log-changes">
                                                @foreach ($log_content as $key => $logitem)
                                                    @php
                                                        if ($key == 'created_at' || $key == 'updated_at') {
                                                            if (str_contains($log->type, 'UPDATED')) {
                                                                $logitem['old'] = LAHelper::dateFormat($logitem['old']);
                                                                $logitem['new'] = LAHelper::dateFormat($logitem['new']);
                                                            } elseif (str_contains($log->type, 'CREATED')) {
                                                                $logitem = LAHelper::dateFormat($logitem);
                                                            }
                                                        }
                                                    @endphp
                                                    @if (str_contains($log->type, 'UPDATED'))
                                                        <li><span class="key"><a class="label label-warning">{{ $key }}</a></span> <span
                                                                class="old">{{ $logitem['old'] }}</span> <i class="fa fa-arrow-right"></i> <span
                                                                class="new">{{ $logitem['new'] }}</span></li>
                                                    @elseif(str_contains($log->type, 'CREATED'))
                                                        <li><span class="key"><a class="label label-success">{{ $key }}</a></span> <span
                                                                class="old">{{ $logitem }}</span></li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    @if (isset($log->user->id))
                                        <div class="timeline-footer">
                                            @if (str_contains($log->type, 'UPDATED'))
                                                <a href="{{ url(config('laraadmin.adminRoute') . '/users/' . $log->user->id) }}"
                                                    class="btn btn-warning btn-xs">Updated by {{ $log->user->name }}</a>
                                            @elseif(str_contains($log->type, 'CREATED'))
                                                <a href="{{ url(config('laraadmin.adminRoute') . '/users/' . $log->user->id) }}"
                                                    class="btn btn-success btn-xs">Created by {{ $log->user->name }}</a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                        <!-- END timeline item -->
                        <li><i class="fa fa-clock-o bg-gray"></i></li>
                    </ul>
                @else
                    <div class="text-center p30"><i class="fa fa-list-alt" style="font-size: 100px;"></i> <br> No logs to show</div>
                @endif
            </div>
            @role('SUPER_ADMIN')
                <div role="tabpanel" class="tab-pane fade in p20 bg-white" id="tab-access">
                    <div class="guide1">
                        <span class="pull-left">Module Accesses for {{ $role->display_name }} Role</span>
                        <i class="fa fa-circle gray"></i> Invisible <i class="fa fa-circle orange"></i> Read-Only <i class="fa fa-circle green"></i> Write
                    </div>
                    <form action="{{ url(config('laraadmin.adminRoute') . '/save_module_role_permissions/' . $role->id) }}" method="post">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <table class="table table-bordered dataTable no-footer table-access">
                            <thead>
                                <tr class="blockHeader">
                                    <th width="30%">
                                        <input class="alignTop" type="checkbox" id="module_select_all" id="module_select_all" checked="checked">&nbsp;
                                        Modules
                                    </th>
                                    <th width="14%">
                                        <input type="checkbox" id="view_all" checked="checked">&nbsp; View
                                    </th>
                                    <th width="14%">
                                        <input type="checkbox" id="create_all" checked="checked">&nbsp; Create
                                    </th>
                                    <th width="14%">
                                        <input type="checkbox" id="edit_all" checked="checked">&nbsp; Edit
                                    </th>
                                    <th width="14%">
                                        <input class="alignTop" id="delete_all" type="checkbox" checked="checked">&nbsp; Delete
                                    </th>
                                    <th width="14%">Field Privileges</th>
                                </tr>
                            </thead>
                            @foreach ($modules_access as $module)
                                <tr>
                                    <td><input module_id="{{ $module->id }}" class="module_checkb" type="checkbox" name="module_{{ $module->id }}"
                                            id="module_{{ $module->id }}" <?php if ($module->accesses->view == 1) {
                                                echo 'checked="checked"';
                                            } ?>>&nbsp; {{ $module->name }}</td>
                                    <!-- <td><input module_id="{{ $module->id }}" class="module_checkb" type="checkbox" name="module_{{ $module->id }}" id="module_{{ $module->id }}" checked="checked">&nbsp; {{ $module->name }}</td> -->
                                    <td><input module_id="{{ $module->id }}" class="view_checkb" type="checkbox"
                                            name="module_view_{{ $module->id }}" id="module_view_{{ $module->id }}" <?php if ($module->accesses->view == 1) {
                                                echo 'checked="checked"';
                                            } ?>></td>
                                    <td><input module_id="{{ $module->id }}" class="create_checkb" type="checkbox"
                                            name="module_create_{{ $module->id }}" id="module_create_{{ $module->id }}" <?php if ($module->accesses->create == 1) {
                                                echo 'checked="checked"';
                                            } ?>></td>
                                    <td><input module_id="{{ $module->id }}" class="edit_checkb" type="checkbox"
                                            name="module_edit_{{ $module->id }}" id="module_edit_{{ $module->id }}" <?php if ($module->accesses->edit == 1) {
                                                echo 'checked="checked"';
                                            } ?>></td>
                                    <td><input module_id="{{ $module->id }}" class="delete_checkb" type="checkbox"
                                            name="module_delete_{{ $module->id }}" id="module_delete_{{ $module->id }}" <?php if ($module->accesses->delete == 1) {
                                                echo 'checked="checked"';
                                            } ?>></td>
                                    <td>
                                        <a module_id="{{ $module->id }}" class="toggle-adv-access btn btn-default btn-sm hide_row"><i
                                                class="fa fa-chevron-down"></i></a>
                                    </td>
                                </tr>
                                <tr class="tr-access-adv module_fields_{{ $module->id }} hide" module_id="{{ $module->id }}">
                                    <td colspan=6>
                                        <table class="table table-bordered">
                                            <tr>
                                                <td colspan=6>
                                                    <div class="col-md-5 col-lg-5"></div>
                                                    <div class="col-md-2 col-lg-2 pb10 pt10 all_field_div">
                                                        @php
                                                            $value_access = 0;
                                                            $one = 0;
                                                            $two = 0;
                                                            foreach ($module->accesses->fields as $field) {
                                                                if ($field['access'] == '2') {
                                                                    $two++;
                                                                } elseif ($field['access'] == '1') {
                                                                    $one++;
                                                                }
                                                            }
                                                            if (count($module->accesses->fields) == $two) {
                                                                $value_access = 2;
                                                            } elseif (count($module->accesses->fields) == $one) {
                                                                $value_access = 1;
                                                            }
                                                        @endphp
                                                        <input type="text" name="{{ $module->name }}_{{ $role->id }}"
                                                            class="form-control slider all_field" module-id="{{ $module->id }}"
                                                            value="{{ $value_access }}" data-slider-value="{{ $value_access }}" data-slider-min="0"
                                                            data-slider-max="2" data-slider-step="1" data-slider-orientation="horizontal"
                                                            data-slider-id="{{ $module->id }}_{{ $role->id }}">
                                                    </div>
                                                    <div class="col-md-5 col-lg-5"></div>
                                                </td>
                                            </tr>
                                            @foreach (array_chunk($module->accesses->fields, 3, true) as $fields)
                                                <tr>
                                                    @foreach ($fields as $field)
                                                        <td>
                                                            <div class="col-md-3">
                                                                <input type="text"
                                                                    name="{{ $field['colname'] }}_{{ $module->id }}_{{ $role->id }}"
                                                                    value="{{ $field['access'] }}" data-slider-value="{{ $field['access'] }}"
                                                                    class="slider form-control module_{{ $module->id }}" data-slider-min="0"
                                                                    data-slider-max="2" data-slider-step="1" data-slider-orientation="horizontal"
                                                                    data-slider-id="{{ $field['colname'] }}_{{ $module->id }}_{{ $role->id }}">
                                                            </div>
                                                            {{ $field['label'] }}
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </table>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                        <center><input class="btn btn-success" type="submit" name="Save"></center>
                    </form>
                    <!--<div class="text-center p30"><i class="fa fa-list-alt" style="font-size: 100px;"></i> <br> No posts to show</div>-->
                </div>
                <div role="tabpanel" class="tab-pane fade in p20 bg-white" id="tab-Users">
                    <div class="tab-content">
                        <div class="box box-primary">
                            <div class="box-footer text-black">
                                <table id="dt_role_users" class="table table-bordered" style="width:100%">
                                    <thead>
                                        <tr class="success">
                                            @foreach ($module_users->listingColumns(true) as $col)
                                                <th>{{ $col['label'] ?? ucfirst($col) }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endrole
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('la-assets/plugins/bootstrap-slider/slider.css') }}" />
    <style>
        .btn-default {
            border-color: #D6D3D3
        }

        .slider .tooltip {
            display: none !important;
        }

        .slider.gray .slider-handle {
            background-color: #888;
        }

        .slider.orange .slider-handle {
            background-color: #FF9800;
        }

        .slider.green .slider-handle {
            background-color: #8BC34A;
        }

        .guide1 {
            text-align: right;
            margin: 0px 15px 15px 0px;
            font-size: 16px;
        }

        .guide1 .fa {
            font-size: 22px;
            vertical-align: bottom;
            margin-left: 17px;
        }

        .guide1 .fa.gray {
            color: #888;
        }

        .guide1 .fa.orange {
            color: #FF9800;
        }

        .guide1 .fa.green {
            color: #8BC34A;
        }

        .table-access {
            border: 1px solid #CCC;
        }

        .table-access thead tr {
            background-color: #DDD;
        }

        .table-access thead tr th {
            border-bottom: 1px solid #CCC;
            padding: 10px 10px;
            text-align: center;
        }

        .table-access thead tr th:first-child {
            text-align: left;
        }

        .table-access input[type="checkbox"] {
            margin-right: 5px;
            vertical-align: text-top;
        }

        .table-access>tbody>tr>td {
            border-bottom: 1px solid #EEE !important;
            padding: 10px 10px;
            text-align: center;
        }

        .table-access>tbody>tr>td:first-child {
            text-align: left;
        }

        .table-access .tr-access-adv {
            background: #b9b9b9;
        }

        .table-access .tr-access-adv .table {
            margin: 0px;
        }

        .table-access .tr-access-adv>td {
            padding: 7px 6px;
        }

        .table-access .tr-access-adv .table-bordered td {
            padding: 10px;
        }

        .all_field_div .slider-track,
        .all_field_div .slider-selection {
            background-image: linear-gradient(to bottom, #bbe6f7, #9ad4ea);
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(function() {
            @role('SUPER_ADMIN')
                /* ================== Access Control ================== */

                $('.slider').slider();

                $(".slider.slider-horizontal").each(function(index) {
                    var field = $(this).next().attr("name");
                    var value = $(this).next().val();
                    console.log("" + field + " ^^^ " + value);
                    switch (value) {
                        case '0':
                            $(this).removeClass("orange");
                            $(this).removeClass("green");
                            $(this).addClass("gray");
                            break;
                        case '1':
                            $(this).removeClass("gray");
                            $(this).removeClass("green");
                            $(this).addClass("orange");
                            break;
                        case '2':
                            $(this).removeClass("gray");
                            $(this).removeClass("orange");
                            $(this).addClass("green");
                            break;
                    }
                });

                $('.slider').bind('change', function(event) {
                    if ($(this).next().attr("name")) {
                        var field = $(this).next().attr("name");
                        var value = $(this).next().val();
                        console.log("" + field + " = " + value);
                        if (value == 0) {
                            $(this).removeClass("orange");
                            $(this).removeClass("green");
                            $(this).addClass("gray");
                        } else if (value == 1) {
                            $(this).removeClass("gray");
                            $(this).removeClass("green");
                            $(this).addClass("orange");
                        } else if (value == 2) {
                            $(this).removeClass("gray");
                            $(this).removeClass("orange");
                            $(this).addClass("green");
                        }
                    }
                });

                $(".all_field").on("change", function() {
                    var module_id = $(this).attr("module-id");
                    var all_field_value = $(this).val();
                    console.log("module_id: " + module_id + " - all_field_value:" + all_field_value);

                    $('.module_' + module_id).slider('setValue', parseInt(all_field_value));

                    switch (all_field_value) {
                        case '0':
                            $('.module_' + module_id).parent().find('.slider').removeClass("orange");
                            $('.module_' + module_id).parent().find('.slider').removeClass("green");
                            $('.module_' + module_id).parent().find('.slider').addClass("gray");
                            break;
                        case '1':
                            $('.module_' + module_id).parent().find('.slider').removeClass("gray");
                            $('.module_' + module_id).parent().find('.slider').removeClass("green");
                            $('.module_' + module_id).parent().find('.slider').addClass("orange");
                            break;
                        case '2':
                            $('.module_' + module_id).parent().find('.slider').removeClass("gray");
                            $('.module_' + module_id).parent().find('.slider').removeClass("orange");
                            $('.module_' + module_id).parent().find('.slider').addClass("green");
                            break;
                    }
                    if (parseInt(all_field_value) == 0) {
                        $("#module_view_" + module_id).prop('checked', false);
                        $("#module_create_" + module_id).prop('checked', false);
                        $("#module_edit_" + module_id).prop('checked', false);
                        $("#module_delete_" + module_id).prop('checked', false);
                    } else if (parseInt(all_field_value) == 1) {
                        $("#module_view_" + module_id).prop('checked', true);
                        $("#module_create_" + module_id).prop('checked', false);
                        $("#module_edit_" + module_id).prop('checked', false);
                        $("#module_delete_" + module_id).prop('checked', false);
                    } else {
                        $("#module_view_" + module_id).prop('checked', true);
                        $("#module_create_" + module_id).prop('checked', true);
                    }
                });

                $("#module_select_all").on("change", function() {
                    $(".module_checkb").prop('checked', this.checked);
                    $(".view_checkb").prop('checked', this.checked);
                    $(".edit_checkb").prop('checked', this.checked)
                    $(".create_checkb").prop('checked', this.checked);
                    $(".delete_checkb").prop('checked', this.checked);
                    $("#module_select_all").prop('checked', this.checked);
                    $("#view_all").prop('checked', this.checked);
                    $("#create_all").prop('checked', this.checked);
                    $("#edit_all").prop('checked', this.checked);
                    $("#delete_all").prop('checked', this.checked);
                });

                $(".module_checkb,  .view_checkb").on("change", function() {
                    var val = $(this).attr("module_id");
                    $("#module_" + val).prop('checked', this.checked)
                    $("#module_view_" + val).prop('checked', this.checked);
                    $("#module_create_" + val).prop('checked', this.checked)
                    $("#module_edit_" + val).prop('checked', this.checked);
                    $("#module_delete_" + val).prop('checked', this.checked);
                });

                $(".create_checkb,  .edit_checkb, .delete_checkb").on("change", function() {
                    var val = $(this).attr("module_id");
                    $(this).prop('checked', this.checked);
                    if (!$("#module_" + val).is(':checked')) {
                        $("#module_" + val).prop('checked', this.checked);
                    }
                    if (!$("#module_view_" + val).is(':checked')) {
                        $("#module_view_" + val).prop('checked', this.checked);
                    }
                });

                $("#create_all").on("change", function() {
                    $(".create_checkb").prop('checked', this.checked);
                    if ($('#create_all').is(':checked')) {
                        $(".module_checkb").prop('checked', this.checked);
                        $(".view_checkb").prop('checked', this.checked);
                        $("#module_select_all").prop('checked', this.checked);
                        $("#view_all").prop('checked', this.checked);
                    }
                });

                $("#edit_all").on("change", function() {
                    $(".edit_checkb").prop('checked', this.checked);
                    if ($('#edit_all').is(':checked')) {
                        $(".module_checkb").prop('checked', this.checked);
                        $(".view_checkb").prop('checked', this.checked);
                        $("#module_select_all").prop('checked', this.checked);
                        $("#view_all").prop('checked', this.checked);
                    }
                });

                $("#delete_all").on("change", function() {
                    $(".delete_checkb").prop('checked', this.checked);
                    if ($('#delete_all').is(':checked')) {
                        $(".module_checkb").prop('checked', this.checked);
                        $(".view_checkb").prop('checked', this.checked);
                        $("#module_select_all").prop('checked', this.checked);
                        $("#view_all").prop('checked', this.checked);
                    }
                });

                $(".hide_row").on("click", function() {
                    var val = $(this).attr("module_id");
                    var $icon = $(".hide_row[module_id=" + val + "] > i");
                    if ($('.module_fields_' + val).hasClass('hide')) {
                        $('.module_fields_' + val).removeClass('hide');
                        $icon.removeClass('fa-chevron-down');
                        $icon.addClass('fa-chevron-up');
                    } else {
                        $('.module_fields_' + val).addClass('hide');
                        $icon.removeClass('fa-chevron-up');
                        $icon.addClass('fa-chevron-down');
                    }
                });

                $("#dt_role_users").DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        "url": "{{ url(config('laraadmin.adminRoute') . '/user_dt_ajax') }}",
                        "data": function(data_custom) {
                            data_custom.filter_column = "role_id";
                            data_custom.filter_column_value = "{{ $role->id }}";
                        }
                    },
                    columns: [{
                        data: 'id',
                        name: 'id'
                    }, {
                        data: 'name',
                        name: 'name'
                    }, {
                        data: 'email',
                        name: 'email'
                    }, ],
                    language: {
                        lengthMenu: "_MENU_",
                        search: "_INPUT_",
                        searchPlaceholder: "@lang('common.search')"
                    }
                });
            @endrole
        });
    </script>
@endpush
