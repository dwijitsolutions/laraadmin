@extends("la.layouts.app")

@section("contentheader_title", "Uploads")
@section("contentheader_description", "Uploaded images & files")
@section("section", "Uploads")
@section("sub_section", "Listing")
@section("htmlheader_title", "Uploaded images & files")

@section("headerElems")
<button id="AddNewUploads" class="btn btn-success btn-sm pull-right">Add New</button>
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

<div class="box box-success">
	<!--<div class="box-header"></div>-->
	<div class="box-body">
		File listing here
	</div>
</div>

@endsection

@push('styles')

@endpush

@push('scripts')
<script>
$(function () {
	
});
</script>
@endpush