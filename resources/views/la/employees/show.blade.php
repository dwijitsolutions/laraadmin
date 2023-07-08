@extends('la.layouts.app')

@section('htmlheader_title')
    @lang('la_employee.employee_view')
@endsection

@section('main-content')
<div id="page-content" class="profile2">
    <div class="bg-success clearfix">
        <div class="col-md-4">
            <div class="row">
                <div class="col-md-3">
                    <img class="profile-image" src="{{ $employee->profileImageUrl() }}" alt="">
                </div>
                <div class="col-md-9">
                    <h4 class="name">{{ $employee->$view_col }}</h4>
                    <div class="row stats">
                        <div class="col-md-6 stat"><div class="label2" data-toggle="tooltip" data-placement="top" title="Designation">{{ $employee->designation }}</div></div>
                        <div class="col-md-6 stat"><i class="fa fa-map-marker"></i> {{ $employee->city ?? "NA" }}</div>
                    </div>
                    <p class="desc">{{ substr($employee->about, 0, 33) }}@if(strlen($employee->about) > 33)...@endif</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dats1"><i class="fa fa-envelope-o"></i> {{ $employee->email_primary }}</div>
            <div class="dats1"><i class="fa fa-phone"></i> {{ $employee->phone_primary }}</div>
            <div class="dats1"><i class="fa fa-clock-o"></i> Birthday @if(isset($employee->date_birth)) {{ date("M d, Y", strtotime($employee->date_birth)) }} @else NA @endif</div>
        </div>
        <div class="col-md-4">
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

        </div>
        <div class="col-md-1 actions">
            @la_access("Employees", "edit")
                <a @ajaxload href="{{ url(config('laraadmin.adminRoute') . '/employees/'.$employee->id.'/edit') }}" class="btn btn-xs btn-edit btn-default"><i class="fa fa-pencil"></i></a><br>
            @endla_access
            
            @la_access("Employees", "delete")
                {{ Form::open(['route' => [config('laraadmin.adminRoute') . '.employees.destroy', $employee->id], 'method' => 'delete', 'style'=>'display:inline']) }}
                    <button class="btn btn-default btn-delete btn-xs" type="submit"><i class="fa fa-times"></i></button>
                {{ Form::close() }}
            @endla_access
        </div>
    </div>

    <ul data-toggle="ajax-tab" class="nav nav-tabs profile" role="tablist">
        <li class=""><a @ajaxload href="{{ url(config('laraadmin.adminRoute') . '/employees') }}" data-toggle="tooltip" data-placement="right" title="@lang('la_employee.back_to_employees')"><i class="fa fa-chevron-left"></i></a></li>
        <li class="active"><a role="tab" data-toggle="tab" class="active" href="#tab-info" data-target="#tab-info"><i class="fa fa-bars"></i> @lang('common.general_info')</a></li>
        <li class=""><a role="tab" data-toggle="tab" href="#tab-timeline" data-target="#tab-timeline"><i class="fa fa-clock-o"></i> @lang('common.timeline')</a></li>
        @if($employee->id == Auth::user()->id || Entrust::hasRole("SUPER_ADMIN"))
            <li class=""><a role="tab" data-toggle="tab" href="#tab-account-settings" data-target="#tab-account-settings"><i class="fa fa-key"></i> @lang('la_employee.account_settings')</a></li>
        @endif
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
						@la_display($module, 'designation')
						@la_display($module, 'gender')
						@la_display($module, 'phone_primary')
						@la_display($module, 'phone_secondary')
						@la_display($module, 'email_primary')
						@la_display($module, 'email_secondary')
						@la_display($module, 'profile_img')
						@la_display($module, 'city')
						@la_display($module, 'address')
						@la_display($module, 'about')
						@la_display($module, 'date_birth')
                    </div>
                </div>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane fade in p20 bg-white" id="tab-timeline">
            @php
            $timeline = $employee->timeline();
            @endphp
            @if(count($timeline) > 0)
                <ul class="timeline timeline-inverse">
                    @foreach($timeline as $log)
                        @php
                        $icon = "fa-clock-o";
                        $iconColor = "bg-blue";
                        if(str_contains($log->type, "CREATED")) {
                            $icon = "fa-plus";
                            $iconColor = "bg-green";
                        } else if(str_contains($log->type, "UPDATED")) {
                            $icon = "fa-pencil";
                            $iconColor = "bg-orange";
                        } else if(str_contains($log->type, "DELETED")) {
                            $icon = "fa-times";
                            $iconColor = "bg-red";
                        }

                        @endphp
                        <li class="time-label"><span class="{{ $iconColor }}">{{ date("d M, Y", strtotime($log->created_at)) }}</span></li>
                        <li>
                            <i class="fa {{ $icon }} {{ $iconColor }}"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fa fa-clock-o"></i> {{ date("g:ia", strtotime($log->created_at)) }}</span>
                                <h3 class="timeline-header no-border">{{ $log->title }}</h3>
                                @if(!str_contains($log->type, "DELETED"))
                                    <div class="timeline-body">
                                        @php
                                            $log_content = json_decode($log->content, true);
                                        @endphp
                                        <ul class="log-changes">
                                            @foreach ($log_content as $key => $logitem)
                                                @php
                                                    if($key == "created_at" || $key == "updated_at") {
                                                        if(str_contains($log->type, "UPDATED")) {
                                                            $logitem['old'] = LAHelper::dateFormat($logitem['old']);
                                                            $logitem['new'] = LAHelper::dateFormat($logitem['new']);
                                                        } else if(str_contains($log->type, "CREATED")) {
                                                            $logitem = LAHelper::dateFormat($logitem);
                                                        }
                                                    }
                                                @endphp
                                                @if(str_contains($log->type, "UPDATED"))
                                                    <li><span class="key"><a class="label label-warning">{{ $key }}</a></span> <span class="old">{{ $logitem['old'] }}</span> <i class="fa fa-arrow-right"></i> <span class="new">{{ $logitem['new'] }}</span></li>
                                                @elseif(str_contains($log->type, "CREATED"))
                                                    <li><span class="key"><a class="label label-success">{{ $key }}</a></span> <span class="old">{{ $logitem }}</span></li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                @if(isset($log->user->id))
                                    <div class="timeline-footer">
                                        @if(str_contains($log->type, "UPDATED"))
                                            <a href="{{ url(config('laraadmin.adminRoute') . '/users/'.$log->user->id) }}" class="btn btn-warning btn-xs">Updated by {{ $log->user->name }}</a>
                                        @elseif(str_contains($log->type, "CREATED"))
                                            <a href="{{ url(config('laraadmin.adminRoute') . '/users/'.$log->user->id) }}" class="btn btn-success btn-xs">Created by {{ $log->user->name }}</a>
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

        @if($employee->id == Auth::user()->context_id || Entrust::hasRole("SUPER_ADMIN"))
        <div role="tabpanel" class="tab-pane fade" id="tab-account-settings">
            <div class="tab-content">
                <form action="{{ url(config('laraadmin.adminRoute') . '/change_password/'.$employee->id) }}" id="password-reset-form" class="general-form dashed-row white" method="post" accept-charset="utf-8">
                    {{ csrf_field() }}
                    <div class="panel">
                        <div class="panel-default panel-heading">
                            <h4>Account settings</h4>
                        </div>
                        <div class="panel-body">
                            @if (count($errors) > 0)
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            @if(Session::has('success_message'))
                                <p class="alert {{ Session::get('alert-class', 'alert-success') }}">{{ Session::get('success_message') }}</p>
                            @endif
                            <div class="form-group">
                                <label for="password" class=" col-md-2">Password</label>
                                <div class=" col-md-10">
                                    <input type="password" name="password" value="" id="password" class="form-control" placeholder="Password" autocomplete="off" required="required" data-rule-minlength="6" data-msg-minlength="Please enter at least 6 characters.">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="password_confirmation" class=" col-md-2">Retype password</label>
                                <div class=" col-md-10">
                                    <input type="password" name="password_confirmation" value="" id="password_confirmation" class="form-control" placeholder="Retype password" autocomplete="off" required="required" data-rule-equalto="#password" data-msg-equalto="Please enter the same value again.">
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <button type="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span> Change Password</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    @if($employee->id == Auth::user()->context_id || Entrust::hasRole("SUPER_ADMIN"))
    $('#password-reset-form').validate({
        
    });
    @endif
});
</script>
@endpush
