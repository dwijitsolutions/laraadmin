@extends("la.layouts.app")

@section("contentheader_title", app('translator')->get('la_configure.configurations'))
@section("contentheader_description", "")
@section("section", app('translator')->get('la_configure.configurations'))
@section("sub_section", "")
@section("htmlheader_title", app('translator')->get('la_configure.configurations'))

@section("headerElems")
<button class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#AddConfigModal">@lang('la_configure.config_add')</button>
@endsection

@section("main-content")

@if (count($errors) > 0)
	<div class="alert alert-danger">
		<ul>
			@foreach ($errors->all() as $error)
				<li>{{ $error }}</li>
			@endforeach
		</ul>
	</div>
@endif

<div class="box" style="border-top-color:#0073b7;">
	<!-- /.box-header -->
	<div class="box-body" style="padding:0px;">
		<ul data-toggle="ajax-tab" class="nav nav-tabs profile" role="tablist">
			@foreach($sections as $section)
				@if($sections[0]->section == $section->section)
					@php $active_class = 'active'; @endphp
				@else
					@php $active_class = ''; @endphp
				@endif
				<li  class="{{ $active_class }}"><a href="#tab-{{ $section->section }}" role="tab" data-toggle="tab" class="active" href="#tab-{{ $section->section }}" data-target="#tab-{{ $section->section }}"><i class="fa fa-puzzle-piece"></i>{{ $section->section }}</a></li>
			@endforeach
		</ul>

		<div class="tab-content">
			@foreach($sections as $section)
				@if($sections[0]->section == $section->section)
					@php $active_class = 'active'; @endphp
				@else
					@php $active_class = ''; @endphp
				@endif
				<div role="tabpanel" class="tab-pane {{ $active_class }} fade in" id="tab-{{ $section->section }}">
					<div class="tab-content">
						<div class="panel infolist">
							<div class="panel-body">
								<form class="config_update_form" action="{{ route(config('laraadmin.adminRoute').'.la_configs.update', $section->section) }}" method="POST">
									<input name="_method" type="hidden" value="PUT">
									{{ csrf_field() }}
									@foreach($section->keys as $config)
										@la_config_input($config->key)
									@endforeach
									<div class="box-footer">
										<button type="submit" class="btn btn-primary">@lang('common.save')</button>
									</div><!-- /.box-footer -->
								</form>
							</div>
						</div>
					</div>
				</div>
			@endforeach
		</div>
	</div>
</div>

<div class="modal fade" id="AddConfigModal" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Add Configuration</h4>
			</div>
			{!! Form::open(['route' => config('laraadmin.adminRoute') . '.la_configs.store', 'id' => 'config-field-form']) !!}
			{{ csrf_field() }}
			<div class="modal-body">
				<div class="box-body">
					<div class="form-group">
						<label for="label">Labal :</label>
						{{ Form::text("label", "", ['class'=>'form-control', 'placeholder'=>'Labal', 'data-rule-minlength' => 2, 'data-rule-maxlength'=>50, 'required' => 'required']) }}
					</div>
					<div class="form-group">
						<label for="key">Config Key/Name :</label>
						{{ Form::text("key", "", ['class'=>'form-control', 'placeholder'=>'Config Key/Name', 'data-rule-minlength' => 2, 'data-rule-maxlength'=>50, 'required' => 'required']) }}
					</div>
					
					<div class="form-group">
						<label for="section">Config Section :</label>
						{{ Form::text("section", null, ['class'=>'form-control', 'id' => 'config_section', 'autocomplete'=>'on', 'placeholder'=>'Config Section', 'data-rule-minlength' => 2, 'data-rule-maxlength'=>50, 'required' => 'required']) }}
					</div>
					<div class="form-group">
						<label for="field_type">UI Type:</label>
						{{ Form::select("field_type", $ftypes, null, ['class'=>'form-control', 'rel' => 'select2', 'required' => 'required']) }}
					</div>
					
					<div class="form-group values">
						<label for="popup_vals">Values :</label>
						<div class="radio" style="margin-bottom:20px;">
							<label>{{ Form::radio("popup_value_type", "table", true) }} From Table</label>
							<label>{{ Form::radio("popup_value_type", "list", false) }} From List</label>
						</div>
						{{ Form::select("popup_vals_table", $tables, "", ['id'=>'popup_vals_table', 'class'=>'form-control', 'rel' => 'select2']) }}
						
						<select id="popup_vals_list" class="form-control popup_vals_list" rel="taginput" multiple="1" data-placeholder="Add Multiple values (Press Enter to add)" name="popup_vals_list[]">
							@if(env('APP_ENV') == "testing")
								<option>Bloomsbury</option>
								<option>Marvel</option>
								<option>Universal</option>
							@endif
						</select>
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

<div class="modal" id="ConfigEditModal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
				<h4 class="modal-title">Config</h4>
			</div>
			<div class="modal-footer">
				<a href="{{ url(config('laraadmin.adminRoute') . '/la_configs/0') }}/edit" id="config_update_url" class="btn btn-success btn-delete pull-left">Update</a>
				{{ Form::open(['route' => [config('laraadmin.adminRoute') . '.la_configs.destroy', 0], 'id' => 'config_del_form', 'method' => 'delete', 'style'=>'display:inline']) }}
					<button class="btn btn-danger btn-delete pull-left" style="margin-left:10px;" type="submit">Delete</button>
				{{ Form::close() }}
				<a data-dismiss="modal" class="btn btn-default pull-right" >Cancel</a>				
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
} --}}
.ui-menu .ui-menu-item a {
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
.ui-menu-item > a.ui-corner-all {
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
.ui-state-hover, .ui-state-active {
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
#modalIns{
    width: 500px;
}
</style>
@endpush

@push('scripts')
<script>

$(function () {
	var sections = [
		@foreach($sections as $section)
			"{{ $section->section }}",
		@endforeach
	];
    $( "#AddConfigModal #config_section" ).autocomplete({ 
		source: sections
    });

	function showValuesSection() {
		var ft_val = $("select[name='field_type']").val();
		if(ft_val == 7 || ft_val == 15 || ft_val == 18 || ft_val == 20) {
			$(".form-group.values").show();
		} else {
			$(".form-group.values").hide();
		}
				
		$('#length_div').removeClass("hide");
		if(ft_val == 2 || ft_val == 4 || ft_val == 5 || ft_val == 7 || ft_val == 9 || ft_val == 11 || ft_val == 12 || ft_val == 15 || ft_val == 18 || ft_val == 21 || ft_val == 24 || ft_val == 25 || ft_val == 26 || ft_val == 27 ) {
			$('#length_div').addClass("hide");
		}

		$('#unique_val').removeClass("hide");
		if(ft_val == 1 || ft_val == 2 || ft_val == 3 || ft_val == 7 || ft_val == 9 || ft_val == 11 || ft_val == 12 || ft_val == 15 || ft_val == 18 || ft_val == 20 || ft_val == 21 || ft_val == 24 ) {
			$('#unique_val').addClass("hide");
		}
	}

	$("select[name='field_type']").on("change", function() {
		showValuesSection();
	});
	showValuesSection();

	function showValuesTypes() {
		// console.log($("input[name='popup_value_type']:checked").val());
		if($("input[name='popup_value_type']:checked").val() == "list") {
			$("select.popup_vals_list").show();
			$("select.popup_vals_list").next().show();
			
			$("select[name='popup_vals_table']").hide();
			$("select[name='popup_vals_table']").next().hide();
		} else {
			$("select.popup_vals_list").hide();
			$("select.popup_vals_list").next().hide();

			$("select[name='popup_vals_table']").show();
			$("select[name='popup_vals_table']").next().show();
		}
	}
	
	$("input[name='popup_value_type']").on("change", function() {
		showValuesTypes();
	});
	showValuesTypes();

	$(".config_update_form").each(function(index) {
		$(this).validate({});
	});

	$("#config-field-form").validate({});

	$(".edit_config").on("click", function() {
		var key = $(this).attr('config_key');
		var id = $(this).attr('config_id');
		
		$("#ConfigEditModal .modal-title").html("Config - " + key);
		$("#config_del_form").attr("action", "{{ url(config('laraadmin.adminRoute') . '/la_configs') }}/"+id);
		$("#config_update_url").attr("href", "{{ url(config('laraadmin.adminRoute') . '/la_configs') }}/"+id+'/edit');
	});

	$(".delete-config").on("click", function() {
		var key = $(this).attr('config_key');
		var id = $(this).attr('config_id');
		if (confirm('Are you sure you want to delete this?')) {
			$.ajax({
				url: "{{ url(config('laraadmin.adminRoute') . '/la_configs/ajax_destroy') }}/" + key,
				method: 'GET',
				data: {
					'_token': '{{ csrf_token() }}',
					'id': id,
				},
				success: function( data ) { 
					location.reload();
				}
			});
		} else {
			return false;
		}
	});
});
</script>
@endpush
