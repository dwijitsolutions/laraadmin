@extends('la.layouts.app')

@section('htmlheader_title')
    Menu
@endsection

@section('main-content')
<div id="page-content" class="profile2">
    <div class="bg-primary clearfix">
        <div class="col-md-4">
            <div class="row">
                <div class="col-md-3">
                    <!--<img class="profile-image" src="{{ asset('la-assets/img/avatar5.png') }}" alt="">-->
                    <div class="profile-icon text-primary"><i class="fa fa-bars"></i></div>
                </div>
                <div class="col-md-9">
                    <h4 class="name">{{ $menu->name }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            
        </div>
        <div class="col-md-4">
            
        </div>
        <div class="col-md-1 actions">
            {{-- <a @ajaxload href="{{ url(config('laraadmin.adminRoute') . '/la_menus/'.$menu->id.'/edit') }}" class="btn btn-xs btn-edit btn-default"><i class="fa fa-pencil"></i></a><br> --}}
            {{ Form::open(['route' => [config('laraadmin.adminRoute') . '.la_menus.destroy', $menu->id], 'method' => 'delete', 'style'=>'display:inline']) }}
                <button class="btn btn-default btn-delete btn-xs" type="submit"><i class="fa fa-times"></i></button>
            {{ Form::close() }}
        </div>
    </div>

    <ul data-toggle="ajax-tab" class="nav nav-tabs profile" role="tablist">
        <li class=""><a @ajaxload href="{{ url(config('laraadmin.adminRoute') . '/la_menus') }}" data-toggle="tooltip" data-placement="right" title="Menus"><i class="fa fa-chevron-left"></i></a></li>
        {{-- <li class="active"><a role="tab" data-toggle="tab" class="active" href="#tab-info" data-target="#tab-info"><i class="fa fa-bars"></i> @lang('common.general_info')</a></li> --}}
        {{-- <li class=""><a role="tab" data-toggle="tab" href="#tab-timeline" data-target="#tab-timeline"><i class="fa fa-clock-o"></i> @lang('common.timeline')</a></li> --}}

		<li class="tab-pane active" id="access">
			<a id="tab_access" role="tab" data-toggle="tab"  class="tab_info active" href="#access" data-target="#tab-access"><i class="fa fa-key"></i> Access</a>
		</li>
    </ul>
    
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active fade in p15" id="tab-access">
			<div class="guide1">
				
			</div>
            <form id="role-menu-access-add-form" novalidate="novalidate" method="POST">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
                <table class="table table-bordered dataTable table-access">
					<thead>
						<tr class="blockHeader">
							<th width="14%">Roles</th>
							<th width="14%"><input type="checkbox" id="access_checkbox" >&nbsp; Access</th>
						</tr>
					</thead>
					@foreach($roles as $role)
						<tr>
                            <td>{{ $role->name }}</td>
                            <td>
                            @if ($menu->roles->contains($role->id))
                                <input class="checkbox_role" type="checkbox" name="roles[]" id="role_{{ $role->id }}" value="{{ $role->id }}" checked="true">
                            @else
                                <input class="checkbox_role" type="checkbox" name="roles[]" id="role_{{ $role->id }}" value="{{ $role->id }}">
                            @endif
                            </td>
						</tr>
					@endforeach
				</table>
				<center class="p10"><button class="btn btn-success" type="submit" id="submit" name="submit">Submit</button></center>
			</form>
		</div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table-access{border:1px solid #CCC;}
.table-access thead tr{background-color: #DDD;}
.table-access thead tr th{border-bottom:1px solid #CCC;padding:10px 10px;text-align:center;}
.table-access thead tr th:first-child{text-align:left;}
.table-access input[type="checkbox"]{margin-right:5px;vertical-align:text-top;}
.table-access > tbody > tr > td{border-bottom:1px solid #EEE !important;padding:10px 10px;text-align:center;}
.table-access > tbody > tr > td:first-child {text-align:left;}

.table-access .tr-access-adv {background:#b9b9b9;}
.table-access .tr-access-adv .table{margin:0px;}
.table-access .tr-access-adv > td{padding: 7px 6px;}
.table-access .tr-access-adv .table-bordered td{padding:10px;}

.ui-field{list-style: none;padding: 3px 7px;border: solid 1px #cccccc;border-radius: 3px;background: #f5f5f5;margin-bottom: 4px;}

</style>
@endpush

@push('scripts')
<script>

$(function () {
    $('#access_checkbox').on('change', function() {
        var checkedStatus = this.checked;
        $('.checkbox_role').each(function () {
            $(this).prop('checked', checkedStatus);
        });
    });
    
    $(".checkbox_role").change(function(){
        if ($('.checkbox_role:checked').length == $('.checkbox_role').length) {
            $('#access_checkbox').prop('checked', true);
        } else {
            $('#access_checkbox').prop('checked', false);
        }
    });

    $(".checkbox_role").trigger("change");

    $("#role-menu-access-add-form").validate({
        submitHandler: function(form) {
            $.ajax({
                url: "{{ url(config('laraadmin.adminRoute') . '/la_menus_save_role_permissions/'.$menu->id) }}",
                method: 'Post',
                data: $(form).serialize(),
                beforeSend: function() {
                    $('#submit').html('<i class="fa fa-refresh fa-spin"></i> Submit..');
                    $('#submit').prop('disabled', true);
                },
                success: function( data ) {
                    console.log(data);
                    if(data.status == "success") {
                        show_success("Menu Access", data);
                        $('#submit').html('<i class="fa fa-check"></i> Submit');
                        $('#submit').prop('disabled', false);
                        setTimeout(function(){
                            location.reload();
                        }, 1000);
                    } else {
                        show_failure("Menu Access", data);
                        $('#submit').html('<i class="fa fa-refresh fa-spin"></i> Submit');
                        $('#submit').prop('disabled', false);
                    }
                }
            });
            return false;
        }
    });
});
</script>
@endpush