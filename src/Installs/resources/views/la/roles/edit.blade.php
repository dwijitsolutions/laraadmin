@extends("la.layouts.app")

@section("contentheader_title", "Edit role: ")
@section("contentheader_description", $role->$view_col)
@section("section", "Roles")
@section("section_url", url(config('laraadmin.adminRoute') . '/roles'))
@section("sub_section", "Edit")

@section("htmlheader_title", "Role Edit : ".$role->$view_col)

@section("main-content")
<div class="box">
	<div class="box-header">
		
	</div>
	<div class="box-body">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				{!! Form::model($role, ['route' => [config('laraadmin.adminRoute') . '.roles.update', $role->id ], 'method'=>'PUT', 'id' => 'role-edit-form']) !!}
					@la_form($module)
					
					{{--
					@la_input($module, 'name')
					@la_input($module, 'name_short')
					@la_input($module, 'parent')
					@la_input($module, 'dept')
					--}}
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Update', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/roles') }}">Cancel</a></button>
					</div>
				{!! Form::close() !!}
				
				@if($errors->any())
				<ul class="alert alert-danger">
					@foreach($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
				@endif
			</div>
		</div>
	</div>
</div>

@endsection

@push('scripts')
<script>
$(function () {
	$("#role-edit-form").validate({
		
	});
});
</script>
@endpush