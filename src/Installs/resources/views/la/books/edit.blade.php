@extends("la.layouts.app")

@section("contentheader_title", "Edit book: ")
@section("contentheader_description", $book->$view_col)
@section("section", "Books")
@section("section_url", url(config('laraadmin.adminRoute') . '/books'))
@section("sub_section", "Edit")

@section("htmlheader_title", "Book Edit : ".$book->$view_col)

@section("main-content")
<div class="box">
	<div class="box-header">
		
	</div>
	<div class="box-body">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				{!! Form::model($book, ['route' => [config('laraadmin.adminRoute') . '.books.update', $book->id ], 'method'=>'PUT', 'id' => 'book-edit-form']) !!}
					@la_form($module)
					
					{{--
					@la_input($module, 'name')
					@la_input($module, 'author')
					@la_input($module, 'author_address')
					@la_input($module, 'price')
					@la_input($module, 'weight')
					@la_input($module, 'pages')
					@la_input($module, 'genre')
					@la_input($module, 'publisher')
					@la_input($module, 'status')
					@la_input($module, 'media_type')
					@la_input($module, 'description')
					@la_input($module, 'email')
					@la_input($module, 'restricted')
					@la_input($module, 'mobile')
					@la_input($module, 'preview')
					@la_input($module, 'website')
					@la_input($module, 'date_release')
					@la_input($module, 'time_started')
					--}}
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Update', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/books') }}">Cancel</a></button>
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
	$("#book-edit-form").validate({
		
	});
});
</script>
@endpush