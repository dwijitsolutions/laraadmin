@extends("la.layouts.app")

@section("contentheader_title", "Students")
@section("contentheader_description", "students listing")
@section("section", "Students")
@section("sub_section", "Listing")
@section("htmlheader_title", "Students Listing")

@section("headerElems")
<button class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#AddModal">Add Student</button>
@endsection

@section("main-content")

<div class="box box-success">
	<!--<div class="box-header"></div>-->
	<div class="box-body">
		<table id="example1" class="table table-bordered">
		<thead>
		<tr class="success">
			@foreach( $listing_cols as $col )
			<th>{{ $module->fields[$col]['label'] or ucfirst($col) }}</th>
			@endforeach
			@if($show_actions)
			<th>Actions</th>
			@endif
		</tr>
		</thead>
		<tbody>
			
		</tbody>
		</table>
	</div>
</div>

<div class="modal fade" id="AddModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Add Student</h4>
			</div>
			{!! Form::open(['action' => 'LA\StudentsController@store', 'id' => 'student-add-form']) !!}
			<div class="modal-body">
				<div class="box-body">
                    @la_form($module)
					
					{{--
					@la_input($module, 'name')
					@la_input($module, 'roll_number')
					@la_input($module, 'profile_image')
					@la_input($module, 'weight')
					@la_input($module, 'mobile')
					@la_input($module, 'email')
					@la_input($module, 'biography')
					@la_input($module, 'fav_genre')
					@la_input($module, 'resume')
					@la_input($module, 'way_commute')
					--}}
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
@endpush

@push('scripts')
<script src="{{ asset('la-assets/plugins/datatables/datatables.min.js') }}"></script>
<script>
$(function () {
	$("#example1").DataTable({
		processing: true,
        serverSide: true,
        ajax: "{{ url(config('laraadmin.adminRoute') . '/student_dt_ajax') }}",
		language: {
			lengthMenu: "_MENU_",
			search: "_INPUT_",
			searchPlaceholder: "Search"
		},
		@if($show_actions)
		columnDefs: [ { orderable: false, targets: [-1] }],
		@endif
	});
	$("#student-add-form").validate({
		
	});
});
</script>
@endpush