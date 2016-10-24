@extends('la.layouts.app')

@section('htmlheader_title', 'Module View')

<?php
use Dwij\Laraadmin\Models\Module;
?>

@section('main-content')
<div id="page-content" class="profile2">
	@if(isset($module->is_gen) && $module->is_gen)
	<div class="bg-success clearfix">
	@else
	<div class="bg-danger clearfix">
	@endif
		<div class="col-md-4">
			<div class="row">
				<div class="col-md-3">
					<!--<img class="profile-image" src="{{ asset('/img/avatar5.png') }}" alt="">-->
					<div class="profile-icon text-primary"><i class="fa {{$module->fa_icon}}"></i></div>
				</div>
				<div class="col-md-9">
					<a class="text-white" href="{{ url(config('laraadmin.adminRoute') . '/'.$module->name_db) }}"><h4 data-toggle="tooltip" data-placement="left" title="Open {{ $module->model }} Module" class="name">{{ $module->label }}</h4></a>
					<div class="row stats">
						<div class="col-md-12">{{ Module::itemCount($module->name) }} Items</div>
					</div>
					<p class="desc">@if(isset($module->is_gen) && $module->is_gen) <div class="label2 success">Module Generated</div> @else <div class="label2 danger" style="border:solid 1px #FFF;">Module not Generated</div> @endif</p>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="dats1" data-toggle="tooltip" data-placement="left" title="Controller"><i class="fa fa-anchor"></i> {{ $module->controller }}</div>
			<div class="dats1" data-toggle="tooltip" data-placement="left" title="Model"><i class="fa fa-database"></i> {{ $module->model }}</div>
			<div class="dats1" data-toggle="tooltip" data-placement="left" title="View Column Name"><i class="fa fa-eye"></i>
				@if($module->view_col!="")
					{{$module->view_col}}
				@else
					Not Set
				@endif
			</div>
		</div>
		
		<div class="col-md-4">
			@if($module->view_col != "")
				@if(isset($module->is_gen) && $module->is_gen)
					<div class="dats1 text-center"><a data-toggle="tooltip" data-placement="left" title="Update Module" class="btn btn-sm btn-success" style="border-color:#FFF;" id="generate_update" href="#"><i class="fa fa-refresh"></i> Update Module</a></div>
					<div class="dats1 text-center"><a data-toggle="tooltip" data-placement="left" title="Update Migration File" class="btn btn-sm btn-success" style="border-color:#FFF;" id="update_migr" href="#"><i class="fa fa-database"></i> Update Migration</a></div>
				@else
					<div class="dats1 text-center"><a data-toggle="tooltip" data-placement="left" title="Generate Migration + CRUD + Module" class="btn btn-sm btn-success" style="border-color:#FFF;" id="generate_migr_crud" href="#"><i class="fa fa-cube"></i> Generate Migration + CRUD</a></div>
					
 					<div class="dats1 text-center"><a data-toggle="tooltip" data-placement="left" title="Generate Migration File" class="btn btn-sm btn-success" style="border-color:#FFF;" id="generate_migr" href="#"><i class="fa fa-database"></i> Generate Migration</a></div>
				@endif
			@else
				<div class="dats1 text-center">To generate Migration or CRUD, set the view column using the <i class='fa fa-eye'></i> icon next to a column</div>
			@endif
		</div>
		
		<div class="col-md-1 actions">
			<button module_name="{{ $module->name }}" module_id="{{ $module->id }}" class="btn btn-default btn-delete btn-xs delete_module"><i class="fa fa-times"></i></button>
		</div>
	</div>

	<ul id="module-tabs" data-toggle="ajax-tab" class="nav nav-tabs profile" role="tablist">
		<li class=""><a href="{{ url(config('laraadmin.adminRoute') . '/modules') }}" data-toggle="tooltip" data-placement="right" title="Back to Modules"> <i class="fa fa-chevron-left"></i>&nbsp;</a></li>
		
		<li class="tab-pane" id="fields">
			<a id="tab_fields" role="tab" data-toggle="tab" class="tab_info" href="#fields" data-target="#tab-info"><i class="fa fa-bars"></i> Module Fields</a>
		</li>
		
		<li class="tab-pane" id="access">
			<a id="tab_access" role="tab" data-toggle="tab"  class="tab_info " href="#access" data-target="#tab-access"><i class="fa fa-key"></i> Access</a>
		</li>
		
		<li class="tab-pane" id="sort">
			<a id="tab_sort" role="tab" data-toggle="tab"  class="tab_info " href="#sort" data-target="#tab-sort"><i class="fa fa-sort"></i> Sort</a>
		</li>
		
		<a data-toggle="modal" data-target="#AddFieldModal" class="btn btn-success btn-sm pull-right btn-add-field" style="margin-top:10px;margin-right:10px;">Add Field</a>
	</ul>

	<div class="tab-content">
		<div role="tabpanel" class="tab-pane fade in" id="tab-info">
			<div class="tab-content">
				<div class="panel">
					<!--<div class="panel-default panel-heading">
						<h4>Module Fields</h4>
					</div>-->
					<div class="panel-body">
						<table id="dt_module_fields" class="table table-bordered" style="width:100% !important;">
						<thead>
						<tr class="success">
							<th style="display:none;"></th>
							<th>#</th>
							<th>Label</th>
							<th>Column</th>
							<th>Type</th>
							<th>Unique</th>
							<th>Default</th>
							<th>Min</th>
							<th>Max</th>
							<th>Required</th>
							<th>Values</th>
							<th><i class="fa fa-cogs"></i></th>
						</tr>
						</thead>
						<tbody>														
							@foreach ($module->fields as $field)
								<tr>
									<td style="display:none;">{{ $field['sort'] }}</td>
									<td>{{ $field['id'] }}</td>
									<td>{{ $field['label'] }}</td>
									<td>{{ $field['colname'] }}</td>
									<td>{{ $ftypes[$field['field_type']] }}</td>
									<td>@if($field['unique']) <span class="text-danger">True</span>@endif </td>
									<td>{{ $field['defaultvalue'] }}</td>
									<td>{{ $field['minlength'] }}</td>
									<td>{{ $field['maxlength'] }}</td>
									<td>@if($field['required']) <span class="text-danger">True</span>@endif </td>
									<td><?php echo LAHelper::parseValues($field['popup_vals']) ?></td>
									<td>
										<a href="{{ url(config('laraadmin.adminRoute') . '/module_fields/'.$field['id'].'/edit') }}" class="btn btn-edit-field btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;" id="edit_{{ $field['colname'] }}"><i class="fa fa-edit"></i></a>
										<a href="{{ url(config('laraadmin.adminRoute') . '/module_fields/'.$field['id'].'/delete') }}" class="btn btn-edit-field btn-danger btn-xs" style="display:inline;padding:2px 5px 3px 5px;" id="delete_{{ $field['colname'] }}"><i class="fa fa-trash"></i></a>
										@if($field['colname'] != $module->view_col)
											<a href="{{ url(config('laraadmin.adminRoute') . '/modules/'.$module->id.'/set_view_col/'.$field['colname']) }}" class="btn btn-edit-field btn-success btn-xs" style="display:inline;padding:2px 5px 3px 5px;" id="view_col_{{ $field['colname'] }}"><i class="fa fa-eye"></i></a>
										@endif
									</td>
								</tr>
							@endforeach
						</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<div role="tabpanel" class="tab-pane fade in p20 bg-white" id="tab-access">
			<div class="guide1">
				<span class="pull-left">Module Access for Roles</span>
				<i class="fa fa-circle gray"></i> Invisible <i class="fa fa-circle orange"></i> Read-Only <i class="fa fa-circle green"></i> Write
			</div>
			<form action="{{ url(config('laraadmin.adminRoute') . '/save_role_module_permissions/'.$module->id) }}" method="post">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<table class="table table-bordered dataTable no-footer table-access">
					<thead>
						<tr class="blockHeader">
							<th width="14%">
								<input class="alignTop" type="checkbox" id="role_select_all" >&nbsp; Roles
							</th>
							<th width="14%">
								<input type="checkbox" id="view_all" >&nbsp; View
							</th>
							<th width="14%">
								<input type="checkbox" id="create_all" >&nbsp; Create
							</th>
							<th width="14%">
								<input type="checkbox" id="edit_all" >&nbsp; Edit
							</th>
							<th width="14%">
								<input class="alignTop" type="checkbox" id="delete_all" >&nbsp; Delete
							</th>
							<th width="14%">Field Privileges</th>
						</tr>
					</thead>
					@foreach($roles as $role)
						<tr class="tr-access-basic" role_id="{{ $role->id }}">
							<td><input class="role_checkb" type="checkbox" name="module_{{ $role->id }}" id="module_{{ $role->id }}" checked="checked"> {{ $role->name }}</td>
							
							<td><input class="view_checkb" type="checkbox" name="module_view_{{$role->id}}" id="module_view_{{$role->id}}" <?php if($role->view == 1) { echo 'checked="checked"'; } ?> ></td>
							<td><input class="create_checkb" type="checkbox" name="module_create_{{$role->id}}" id="module_create_{{$role->id}}" <?php if($role->create == 1) { echo 'checked="checked"'; } ?> ></td>
							<td><input class="edit_checkb" type="checkbox" name="module_edit_{{$role->id}}" id="module_edit_{{$role->id}}" <?php if($role->edit == 1) { echo 'checked="checked"'; } ?> ></td>
							<td><input class="delete_checkb" type="checkbox" name="module_delete_{{$role->id}}" id="module_delete_{{$role->id}}" <?php if($role->delete == 1) { echo 'checked="checked"'; } ?> ></td>
							<td>
								<a role_id="{{ $role->id }}" class="toggle-adv-access btn btn-default btn-sm hide_row"><i class="fa fa-chevron-down"></i></a>
							</td>
						</tr>
						<tr class="tr-access-adv module_fields_{{ $role->id }} hide" role_id="{{ $role->id }}" >
							<td colspan=6>
								<table class="table table-bordered">
								@foreach (array_chunk($module->fields, 3, true) as $fields)
									<tr>
										@foreach ($fields as $field)
											<td><div class="col-md-3"><input type="text" name="{{ $field['colname'] }}_{{ $role->id }}" value="{{ $role->fields[$field['id']]['access'] }}" data-slider-value="{{ $role->fields[$field['id']]['access'] }}" class="slider form-control" data-slider-min="0" data-slider-max="2" data-slider-step="1" data-slider-orientation="horizontal"  data-slider-id="{{ $field['colname'] }}_{{ $role->id }}"></div> {{ $field['label'] }} </td>
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
		<div role="tabpanel" class="tab-pane fade in p20 bg-white" id="tab-sort">
			<div class="row">
				<div class="col-lg-3 col-md-3 col-sm-3">
					<ul id="sortable_module_fields">
						@foreach ($module->fields as $field)
							<li class="ui-field" field_id="{{ $field['id'] }}"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>{{ $field['label'] }}
								@if($field['colname'] == $module->view_col)
									<i class="fa fa-eye pull-right" style="margin-top:3px;"></i>
								@endif
							</li>
						@endforeach
					</ul>
				</div>
			</div>
		</div>
	</div>
	</div>
	</div>
</div>

<!-- module deletion confirmation  -->
<div class="modal" id="module_delete_confirm">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
				<h4 class="modal-title">Module Delete</h4>
			</div>
			<div class="modal-body">
				<p>Do you really want to delete module <b id="moduleNameStr" class="text-danger"></b> ?</p>
				<p>Following files will be deleted:</p>
				<div id="moduleDeleteFiles"></div>
				<p class="text-danger">Note: Migration file will not be deleted but modified.</p>
			</div>
			<div class="modal-footer">
				{{ Form::open(['route' => [config('laraadmin.adminRoute') . '.modules.destroy', 0], 'id' => 'module_del_form', 'method' => 'delete', 'style'=>'display:inline']) }}
					<button class="btn btn-danger btn-delete pull-left" type="submit">Yes</button>
				{{ Form::close() }}
				<a data-dismiss="modal" class="btn btn-default pull-right" >No</a>				
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>

<div class="modal fade" id="AddFieldModal" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Add {{ $module->model }} Field</h4>
			</div>
			{!! Form::open(['route' => config('laraadmin.adminRoute') . '.module_fields.store', 'id' => 'field-form']) !!}
			{{ Form::hidden("module_id", $module->id) }}
			<div class="modal-body">
				<div class="box-body">
					<div class="form-group">
						<label for="label">Field Label :</label>
						{{ Form::text("label", null, ['class'=>'form-control', 'placeholder'=>'Field Label', 'data-rule-minlength' => 2, 'data-rule-maxlength'=>20, 'required' => 'required']) }}
					</div>
					
					<div class="form-group">
						<label for="colname">Column Name :</label>
						{{ Form::text("colname", null, ['class'=>'form-control', 'placeholder'=>'Column Name (lowercase)', 'data-rule-minlength' => 2, 'data-rule-maxlength'=>20, 'data-rule-banned-words' => 'true', 'required' => 'required']) }}
					</div>
					
					<div class="form-group">
						<label for="field_type">UI Type:</label>
						{{ Form::select("field_type", $ftypes, null, ['class'=>'form-control', 'required' => 'required']) }}
					</div>
					
					<div id="unique_val">
						<div class="form-group">
							<label for="unique">Unique:</label>
							{{ Form::checkbox("unique", "unique", false, []) }}
							<div class="Switch Round Off" style="vertical-align:top;margin-left:10px;"><div class="Toggle"></div></div>
						</div>
					</div>	
					
					<div id="default_val">
						<div class="form-group">
							<label for="defaultvalue">Default Value :</label>
							{{ Form::text("defaultvalue", null, ['class'=>'form-control', 'placeholder'=>'Default Value']) }}
						</div>
					</div>

					<div id="length_div">
						<div class="form-group">
							<label for="minlength">Minimum :</label>
							{{ Form::number("minlength", null, ['class'=>'form-control', 'placeholder'=>'Minimum Value']) }}
						</div>
						
						<div class="form-group">
							<label for="maxlength">Maximum :</label>
							{{ Form::number("maxlength", null, ['class'=>'form-control', 'placeholder'=>'Maximum Value']) }}
						</div>
					</div>
					
					<div class="form-group">
						<label for="required">Required:</label>
						{{ Form::checkbox("required", "required", false, []) }}
						<div class="Switch Round Off" style="vertical-align:top;margin-left:10px;"><div class="Toggle"></div></div>
					</div>
					
					<!--
					<div class="form-group">
						<label for="popup_vals">Values :</label>
						{{-- Form::text("popup_vals", null, ['class'=>'form-control', 'placeholder'=>'Popup Values (Only for Radio, Dropdown, Multiselect, Taginput)']) --}}
					</div>
					-->
					
					<div class="form-group values">
						<label for="popup_vals">Values :</label>
						<div class="radio" style="margin-bottom:20px;">
							<label>{{ Form::radio("popup_value_type", "table", true) }} From Table</label>
							<label>{{ Form::radio("popup_value_type", "list", false) }} From List</label>
						</div>
						{{ Form::select("popup_vals_table", $tables, "", ['id'=>'popup_vals_table', 'class'=>'form-control', 'rel' => '']) }}
						
						<select id="popup_vals_list" class="form-control popup_vals_list" rel="taginput" multiple="1" data-placeholder="Add Multiple values (Press Enter to add)" name="popup_vals_list[]">
							@if(env('APP_ENV') == "testing")
								<option>Bloomsbury</option>
								<option>Marvel</option>
								<option>Universal</option>
							@endif
						</select>
					</div>
					
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				{!! Form::submit( 'Submit', ['class'=>'btn btn-success']) !!}
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('la-assets/plugins/datatables/datatables.min.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('la-assets/plugins/bootstrap-slider/slider.css') }}"/>
<style>
.btn-default{border-color:#D6D3D3}
.slider .tooltip{display:none !important;}
.slider.gray .slider-handle{background-color:#888;}
.slider.orange .slider-handle{background-color:#FF9800;}
.slider.green .slider-handle{background-color:#8BC34A;}

.guide1{text-align: right;margin: 0px 15px 15px 0px;font-size:16px;}
.guide1 .fa{font-size:22px;vertical-align:bottom;margin-left:17px;}
.guide1 .fa.gray{color:#888;}
.guide1 .fa.orange{color:#FF9800;}
.guide1 .fa.green{color:#8BC34A;}

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
<script src="{{ asset('la-assets/plugins/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('la-assets/plugins/bootstrap-slider/bootstrap-slider.js') }}"></script>
<script src="{{ asset('la-assets/plugins/jQueryUI/jquery-ui.js') }}"></script>

<script>

$(function () {
	
	$("select.popup_vals_list").show();
	$("select.popup_vals_list").next().show();
	$("select[name='popup_vals']").hide();

	$('.delete_module').on("click", function () {
    	var module_id = $(this).attr('module_id');
		var module_name = $(this).attr('module_name');
		$("#moduleNameStr").html(module_name);
		$url = $("#module_del_form").attr("action");
		$("#module_del_form").attr("action", $url.replace("/0", "/"+module_id));
		$("#module_delete_confirm").modal('show');
		$.ajax({
			url: "{{ url(config('laraadmin.adminRoute') . '/get_module_files/') }}/" + module_id,
			type:"POST",
			beforeSend: function() {
				$("#moduleDeleteFiles").html('<center><i class="fa fa-refresh fa-spin"></i></center>');
			},
			headers: {
		    	'X-CSRF-Token': '{{ csrf_token() }}'
    		},
			success: function(data) {
				var files = data.files;
				var filesList = "<ul>";
				for ($i = 0; $i < files.length; $i++) { 
					filesList += "<li>" + files[$i] + "</li>";
				}
				filesList += "</ul>";
				$("#moduleDeleteFiles").html(filesList);
			}
		});
	});
	
	function showValuesSection() {
		var ft_val = $("select[name='field_type']").val();
		if(ft_val == 7 || ft_val == 15 || ft_val == 18 || ft_val == 20) {
			$(".form-group.values").show();
		} else {
			$(".form-group.values").hide();
		}
				
		$('#length_div').removeClass("hide");
		if(ft_val == 2 || ft_val == 4 || ft_val == 5 || ft_val == 7 || ft_val == 9 || ft_val == 11 || ft_val == 12 || ft_val == 15 || ft_val == 18 || ft_val == 21 || ft_val == 24 ) {
			$('#length_div').addClass("hide");
		}

		$('#unique_val').removeClass("hide");
		if(ft_val == 1 || ft_val == 2 || ft_val == 3 || ft_val == 7 || ft_val == 9 || ft_val == 11 || ft_val == 12 || ft_val == 15 || ft_val == 18 || ft_val == 20 || ft_val == 21 || ft_val == 24 ) {
			$('#unique_val').addClass("hide");
		}

		$('#default_val').removeClass("hide");
		if(ft_val == 11) {
			$('#default_val').addClass("hide");
		}
	}

	$("select[name='field_type']").on("change", function() {
		showValuesSection();
	});
	showValuesSection();

	function showValuesTypes() {
		console.log($("input[name='popup_value_type']:checked").val());
		if($("input[name='popup_value_type']:checked").val() == "list") {
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

	$("#sortable_module_fields").sortable({
		update: function(event, ui) {
            // var index = ui.placeholder.index();
            var array = [];
			$("#sortable_module_fields li").each(function(index) {
				var field_id = $(this).attr("field_id");
				if(typeof field_id != "undefined") {
					array.push(field_id);
				}
			});
			
			$.ajax({
				url: "{{ url(config('laraadmin.adminRoute') . '/save_module_field_sort') }}/"+{{ $module->id }},
				data : {'sort_array': array},
				method: 'GET',
				success: function( data ) {
					console.log(data);
				}
			});
        },
	});
    $("#sortable_module_fields").disableSelection();	
	
	$("#generate_migr").on("click", function() {
		var $fa = $(this).find("i");
		$fa.removeClass("fa-database");
		$fa.addClass("fa-refresh");
		$fa.addClass("fa-spin");
		$.ajax({
			url: "{{ url(config('laraadmin.adminRoute') . '/module_generate_migr') }}/"+{{ $module->id }},
			method: 'GET',
			success: function( data ) {
				$fa.removeClass("fa-refresh");
				$fa.removeClass("fa-spin");
				$fa.addClass("fa-check");
				console.log(data);
				location.reload();
			}
		});
	});
	$("#update_migr").on("click", function() {
		var $fa = $(this).find("i");
		$fa.removeClass("fa-database");
		$fa.addClass("fa-refresh");
		$fa.addClass("fa-spin");
		$.ajax({
			url: "{{ url(config('laraadmin.adminRoute') . '/module_generate_migr') }}/"+{{ $module->id }},
			method: 'GET',
			success: function( data ) {
				$fa.removeClass("fa-refresh");
				$fa.removeClass("fa-spin");
				$fa.addClass("fa-check");
				console.log(data);
				location.reload();
			}
		});
	});
	$("#generate_update").on("click", function() {
		var $fa = $(this).find("i");
		$fa.removeClass("fa-database");
		$fa.addClass("fa-refresh");
		$fa.addClass("fa-spin");
		$.ajax({
			url: "{{ url(config('laraadmin.adminRoute') . '/module_generate_update') }}/"+{{ $module->id }},
			method: 'GET',
			success: function( data ) {
				$fa.removeClass("fa-refresh");
				$fa.removeClass("fa-spin");
				$fa.addClass("fa-check");
				console.log(data);
				location.reload();
			}
		});
	});
	
	$("#generate_migr_crud").on("click", function() {
		var $fa = $(this).find("i");
		$fa.removeClass("fa-cube");
		$fa.addClass("fa-refresh");
		$fa.addClass("fa-spin");
		$.ajax({
			url: "{{ url(config('laraadmin.adminRoute') . '/module_generate_migr_crud') }}/"+{{ $module->id }},
			method: 'GET',
			success: function( data ) {
				$fa.removeClass("fa-refresh");
				$fa.removeClass("fa-spin");
				$fa.addClass("fa-check");
				console.log(data);
				location.reload();
			}
		});
	});
	$("#dt_module_fields").DataTable({
		"initComplete": function(settings, json) {
			console.log( 'DataTables has finished its initialisation.' );
			console.log("Win: "+$(window).height()+" header: "+$(".main-header").height());
			$(".sidebar").slimscroll({
				height: ($(window).height() - $(".main-header").height()) + "px",
				color: "rgba(0,0,0,0.2)",
				size: "3px"
			});
		}
	});
	
	$.validator.addMethod("data-rule-banned-words", function(value) {
		return $.inArray(value, ['files']) == -1;
	}, "Column name not allowed.");

	$("#field-form").validate();
		
	/* ================== Tab Selection ================== */
	
	var $tabs = $('#module-tabs').tabs();
	
	var url = window.location.href;
	var activeTab = url.substring(url.indexOf("#") + 1);
	
	if(!activeTab.includes("http") && activeTab.length > 1) {
		$('#module-tabs #'+activeTab+' a').tab('show');
	} else {
		$('#module-tabs #fields a').tab('show');
	}
	
	/* ================== Access Control ================== */
	
	$('.slider').slider();
	
	$(".slider.slider-horizontal").each(function(index) {
		var field = $(this).next().attr("name");
		var value = $(this).next().val();
		// console.log(""+field+" ^^^ "+value);
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
	
	$('.slider').bind('slideStop', function(event) {
		if($(this).next().attr("name")) {
			var field = $(this).next().attr("name");
			var value = $(this).next().val();
			// console.log(""+field+" = "+value);
			if(value == 0) {
				$(this).removeClass("orange");
				$(this).removeClass("green");
				$(this).addClass("gray");
			} else if(value == 1) {
				$(this).removeClass("gray");
				$(this).removeClass("green");
				$(this).addClass("orange");
			} else if(value == 2) {
				$(this).removeClass("gray");
				$(this).removeClass("orange");
				$(this).addClass("green");
			}
		}
	});

	$("#role_select_all,  #view_all").on("change", function() {
		$(".role_checkb").prop('checked', this.checked);
		$(".view_checkb").prop('checked', this.checked);
		$(".edit_checkb").prop('checked', this.checked)
		$(".create_checkb").prop('checked', this.checked);
		$(".delete_checkb").prop('checked', this.checked);
		$("#role_select_all").prop('checked', this.checked);
		$("#view_all").prop('checked', this.checked);
		$("#create_all").prop('checked', this.checked);
		$("#edit_all").prop('checked', this.checked);
		$("#delete_all").prop('checked', this.checked);		
	});
	
	$("#create_all").on("change", function() {
		$(".create_checkb").prop('checked', this.checked);
		if($('#create_all').is(':checked')){
			$(".role_checkb").prop('checked', this.checked);
			$(".view_checkb").prop('checked', this.checked);
			$("#role_select_all").prop('checked', this.checked);
			$("#view_all").prop('checked', this.checked);
		}
	});
	
	$("#edit_all").on("change", function() {
		$(".edit_checkb").prop('checked', this.checked);
		if($('#edit_all').is(':checked')){
			$(".role_checkb").prop('checked', this.checked);
			$(".view_checkb").prop('checked', this.checked);
			$("#role_select_all").prop('checked', this.checked);
			$("#view_all").prop('checked', this.checked);
		}
	});
	
	$("#delete_all").on("change", function() {
		$(".delete_checkb").prop('checked', this.checked);
		if($('#delete_all').is(':checked')){
			$(".role_checkb").prop('checked', this.checked);
			$(".view_checkb").prop('checked', this.checked);
			$("#role_select_all").prop('checked', this.checked);
			$("#view_all").prop('checked', this.checked);
		}
	});
	
	$(".hide_row").on("click", function() { 
		var val = $(this).attr( "role_id" );
		var $icon = $(".hide_row[role_id="+val+"] > i");
		if($('.module_fields_'+val).hasClass('hide')) {
			$('.module_fields_'+val).removeClass('hide');
			$icon.removeClass('fa-chevron-down');
			$icon.addClass('fa-chevron-up');
		} else {
			$('.module_fields_'+val).addClass('hide');
			$icon.removeClass('fa-chevron-up');
			$icon.addClass('fa-chevron-down');
		}
	});
});
</script>
@endpush
