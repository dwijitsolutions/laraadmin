@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/departments') }}">Departments</a> :
@endsection
@section("contentheader_description", $department->$view_col)
@section("section", "Departments")
@section("section_url", url(config('laraadmin.adminRoute') . '/departments'))
@section("sub_section", "Edit")

@section("htmlheader_title", "Department Edit : ".$department->$view_col)

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

<div class="box">
	<div class="box-header">
		
	</div>
	<div class="box-body">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				{!! Form::model($department, ['route' => [config('laraadmin.adminRoute') . '.departments.update', $department->id ], 'method'=>'PUT', 'id' => 'department-edit-form']) !!}
					@la_form($module)
					
					{{--
					@la_input($module, 'name')
					@la_input($module, 'tags')
					@la_input($module, 'color')
					--}}
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Update', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/departments') }}">Cancel</a></button>
					</div>
				{!! Form::close() !!}
			</div>
		</div>
	</div>
</div>

@endsection

@push('scripts')
<script>
$(function () {
	$("#department-edit-form").validate({
		
	});
});
</script>
@endpush
