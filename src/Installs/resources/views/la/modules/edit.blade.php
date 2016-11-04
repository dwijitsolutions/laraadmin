@extends("la.layouts.app")

@section("contentheader_title", "Edit course: ")
@section("contentheader_description", $course->$view_col)
@section("section", "Courses")
@section("sub_section", "Edit")

@section("htmlheader_title", "Course Edit : ".$course->$view_col)

@section("main-content")
<div class="box">
	<div class="box-header">
		
	</div>
	<div class="box-body">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				{!! Form::model($course, ['route' => [config('laraadmin.adminRoute') . '.modules.update', $course->id ], 'method'=>'PUT', 'id' => 'course-edit-form']) !!}
					
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Update', ['class'=>'btn btn-success']) !!} <a href="{{ url(config('laraadmin.adminRoute') . '/courses') }}" class="btn btn-default pull-right">Cancel</a>
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
	$("#course-edit-form").validate({
		
	});
});
</script>
@endpush