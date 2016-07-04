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

<form action="{{ url(config('laraadmin.adminRoute') . '/upload_files')}}" id="fm_dropzone_main" enctype="multipart/form-data" method="POST">
    {{ csrf_field() }}
    <a id="closeDZ1"><i class="fa fa-times"></i></a>
    <div class="dz-message"><i class="fa fa-cloud-upload"></i><br>Drop files here to upload</div>
</form>

<div class="box box-success">
	<!--<div class="box-header"></div>-->
	<div class="box-body">
		<ul class="files_container">
        </ul>
	</div>
</div>

@endsection

@push('styles')

@endpush

@push('scripts')
<script>
var bsurl = $('body').attr("bsurl");
var fm_dropzone_main = null;
var cntFiles = null;
$(function () {
	fm_dropzone_main = new Dropzone("#fm_dropzone_main", {
        maxFilesize: 2,
        acceptedFiles: "image/*,application/pdf",
        init: function() {
            this.on("complete", function(file) {
                this.removeFile(file);
            });
            this.on("success", function(file) {
                console.log("addedfile");
                console.log(file);
                loadUploadedFiles();
            });
        }
    });
    $("#fm_dropzone_main").hide();
    $("#AddNewUploads").on("click", function() {
        $("#fm_dropzone_main").show();
    });
    $("#closeDZ1").on("click", function() {
        $("#fm_dropzone_main").hide();
    });
    loadUploadedFiles();
});
function loadUploadedFiles() {
    // load folder files
    $.ajax({
        dataType: 'json',
        url: $('body').attr("bsurl")+"/admin/folder_files/uploads",
        success: function ( json ) {
            console.log(json);
            cntFiles = json.files;
            $("ul.files_container").empty();
            for (var index = 0; index < json.files.length; index++) {
                var element = json.files[index];
                var li = formatFile(json.folder_name, element);
                $("ul.files_container").append(li);
            }
        }
    });
}
function formatFile(folder_name, filename) {
    ext = filename.split('.').pop();
    var image = '';
    // if(.contains(ext)) {
    if($.inArray(ext, ["jpg", "jpeg", "png", "gif", "bmp"]) > -1) {
        image = '<img src="'+bsurl+'/'+folder_name+'/'+filename+'">';
    } else {
        switch (ext) {
            case "pdf":
                image = '<i class="fa fa-file-pdf-o"></i>';
                break;
            case "pdf":
                image = '<i class="fa fa-file-pdf-o"></i>';
                break;
            default:
                image = '<i class="fa fa-file-text-o"></i>';
                break;
        }
    }
    return '<li><a class="fm_file_sel" data-toggle="tooltip" data-placement="top" title="'+folder_name+'/'+filename+'" fpath="'+folder_name+'/'+filename+'">'+image+'</a></li>';
}
</script>
@endpush