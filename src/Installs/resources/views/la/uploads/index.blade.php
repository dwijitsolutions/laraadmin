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
    $("#fm_dropzone_main").slideUp();
    $("#AddNewUploads").on("click", function() {
        $("#fm_dropzone_main").slideDown();
    });
    $("#closeDZ1").on("click", function() {
        $("#fm_dropzone_main").slideUp();
    });
    loadUploadedFiles();
});
function loadUploadedFiles() {
    // load folder files
    $.ajax({
        dataType: 'json',
        url: $('body').attr("bsurl")+"/admin/uploaded_files",
        success: function ( json ) {
            console.log(json);
            cntFiles = json.uploads;
            $("ul.files_container").empty();
            if(cntFiles.length) {
                for (var index = 0; index < cntFiles.length; index++) {
                    var element = cntFiles[index];
                    var li = formatFile(element);
                    $("ul.files_container").append(li);
                }
            } else {
                $("ul.files_container").html("<div class='text-center text-danger' style='margin-top:40px;'>No Files</div>");
            }
        }
    });
}
function formatFile(upload) {
    var image = '';
    if($.inArray(upload.extension, ["jpg", "jpeg", "png", "gif", "bmp"]) > -1) {
        image = '<img src="'+bsurl+'/files/'+upload.hash+'/'+upload.name+'?s=130">';
    } else {
        switch (upload.extension) {
            case "pdf":
                image = '<i class="fa fa-file-pdf-o"></i>';
                break;
            default:
                image = '<i class="fa fa-file-text-o"></i>';
                break;
        }
    }
    return '<li><a class="fm_file_sel" data-toggle="tooltip" data-placement="top" title="'+upload.name+'" fpath="'+bsurl+'/files/'+upload.hash+'/'+upload.name+'">'+image+'</a></li>';
}
</script>
@endpush