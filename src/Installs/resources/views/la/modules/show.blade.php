@extends('la.layouts.app')

@section('htmlheader_title')
	Module View
@endsection

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
					<div class="profile-icon text-primary"><i class="fa fa-cube"></i></div>
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
					
				@else
					<div class="dats1 text-center"><a data-toggle="tooltip" data-placement="left" title="Generate Migration + CRUD + Module" class="btn btn-sm btn-success" style="border-color:#FFF;" id="generate_migr_crud" href="#"><i class="fa fa-cube"></i> Generate Migration + CRUD</a></div>
					
 					<div class="dats1 text-center"><a data-toggle="tooltip" data-placement="left" title="Generate Migration File" class="btn btn-sm btn-success" style="border-color:#FFF;" id="generate_migr" href="#"><i class="fa fa-database"></i> Generate Migration</a></div>
				@endif
			@else
				<div class="dats1 text-center">To generate Migration or CRUD, set the view column using the <i class='fa fa-eye'></i> icon next to a column</div>
			@endif
		</div>
		
		<div class="col-md-1 actions">
			<a href="{{ url(config('laraadmin.adminRoute') . '/modules/'.$module->id.'/edit') }}" class="btn btn-xs btn-edit btn-default"><i class="fa fa-pencil"></i></a><br>
			{{ Form::open(['route' => [config('laraadmin.adminRoute') . '.modules.destroy', $module->id], 'method' => 'delete', 'style'=>'display:inline']) }}
				<button class="btn btn-default btn-delete btn-xs" type="submit"><i class="fa fa-times"></i></button>
			{{ Form::close() }}
		</div>
	</div>

	<ul data-toggle="ajax-tab" class="nav nav-tabs profile" role="tablist">
		<li class=""><a href="{{ url(config('laraadmin.adminRoute') . '/modules') }}" data-toggle="tooltip" data-placement="right" title="Back to Modules"> <i class="fa fa-chevron-left"></i>&nbsp;</a></li>
		<li class="active"><a role="tab" data-toggle="tab" class="active" href="#tab-general-info" data-target="#tab-info"><i class="fa fa-bars"></i> Module Fields</a></li>
		<a data-toggle="modal" data-target="#AddFieldModal" class="btn btn-success btn-sm pull-right btn-add-field" style="margin-top:10px;margin-right:10px;">Add Field</a>
	</ul>

	<div class="tab-content">
		<div role="tabpanel" class="tab-pane active fade in" id="tab-info">
			<div class="tab-content">
				<div class="panel">
					<!--<div class="panel-default panel-heading">
						<h4>Module Fields</h4>
					</div>-->
					<div class="panel-body">
						<table id="dt_module_fields" class="table table-bordered">
						<thead>
						<tr class="success">
							<th>#</th>
							<th>Label</th>
							<th>Column</th>
							<th>Type</th>
							<th>Readonly</th>
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
									<td>{{ $field['id'] }}</td>
									<td>{{ $field['label'] }}</td>
									<td>{{ $field['colname'] }}</td>
									<td>{{ $ftypes[$field['field_type']] }}</td>
									<td>@if($field['readonly']) <span class="text-danger">True</span>@endif </td>
									<td>{{ $field['defaultvalue'] }}</td>
									<td>{{ $field['minlength'] }}</td>
									<td>{{ $field['maxlength'] }}</td>
									<td>@if($field['required']) <span class="text-danger">True</span>@endif </td>
									<td><?php echo LAHelper::parseValues($field['popup_vals']) ?></td>
									<td>
										<a href="{{ url(config('laraadmin.adminRoute') . '/module_fields/'.$field['id'].'/edit') }}" class="btn btn-edit-field btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>
										@if($field['colname'] != $module->view_col)
											<a href="{{ url(config('laraadmin.adminRoute') . '/modules/'.$module->id.'/set_view_col/'.$field['colname']) }}" class="btn btn-edit-field btn-success btn-xs" style="display:inline;padding:2px 5px 3px 5px;" data-toggle="tooltip" data-placement="left" title="Set View Column"><i class="fa fa-eye"></i></a>
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
	</div>
	</div>
	</div>
</div>
@endsection

<div class="modal fade" id="AddFieldModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
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
						{{ Form::text("colname", null, ['class'=>'form-control', 'placeholder'=>'Column Name (lowercase)', 'data-rule-minlength' => 2, 'data-rule-maxlength'=>20, 'required' => 'required']) }}
					</div>
					
					<div class="form-group">
						<label for="field_type">UI Type:</label>
						{{ Form::select("field_type", $ftypes, null, ['class'=>'form-control', 'required' => 'required']) }}
					</div>
					
					<div class="form-group">
						<label for="readonly">Read Only:</label>
						{{ Form::checkbox("readonly", "readonly", false, []) }}
						<div class="Switch Round Off" style="vertical-align:top;margin-left:10px;"><div class="Toggle"></div></div>
					</div>
					
					<div class="form-group">
						<label for="defaultvalue">Default Value :</label>
						{{ Form::text("defaultvalue", null, ['class'=>'form-control', 'placeholder'=>'Default Value']) }}
					</div>
					
					<div class="form-group">
						<label for="minlength">Minimum :</label>
						{{ Form::number("minlength", null, ['class'=>'form-control', 'placeholder'=>'Minimum Value']) }}
					</div>
					
					<div class="form-group">
						<label for="maxlength">Maximum :</label>
						{{ Form::number("maxlength", null, ['class'=>'form-control', 'placeholder'=>'Maximum Value']) }}
					</div>
					
					<div class="form-group">
						<label for="required">Required:</label>
						{{ Form::checkbox("required", "required", false, []) }}
						<div class="Switch Round Off" style="vertical-align:top;margin-left:10px;"><div class="Toggle"></div></div>
					</div>
					
					<div class="form-group">
						<label for="required">Unique:</label>
						{{ Form::checkbox("unique", "unique", false, []) }}
						<div class="Switch Round Off" style="vertical-align:top;margin-left:10px;"><div class="Toggle"></div></div>
					</div>
					
					<div class="form-group">
						<label for="popup_vals">Values :</label>
						{{ Form::text("popup_vals", null, ['class'=>'form-control', 'placeholder'=>'Popup Values (Only for Radio, Dropdown, Multiselect, Taginput)']) }}
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

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('la-assets/plugins/datatables/datatables.min.css') }}"/>
@endpush

@push('scripts')
<script src="{{ asset('la-assets/plugins/datatables/datatables.min.js') }}"></script>
<script>
$(function () {
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
	$("#field-form").validate();
});
</script>
@endpush