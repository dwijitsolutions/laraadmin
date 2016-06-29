@extends('la.layouts.app')

@section('htmlheader_title')
	Code Editor
@endsection

@section('main-content')
<div id="laeditor" class="row">
	<div class="col-md-2 col-sm-3">
		<div class="la-header">
			LA Editor
			<!--<div class="la-dir">/Applications/MAMP/htdocs</div>-->
		</div>
		<div class="la-file-tree">
			
		</div>
	</div>
	<div class="col-md-10 col-sm-9">
		<ul class="laeditor-tabs">
			
		</ul>
		<pre id="la-ace-editor"></pre>
	</div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('la-assets/plugins/jquery-filetree/jQueryFileTree.min.css') }}"/>
@endpush

@push('scripts')
<script src="{{ asset('la-assets/plugins/jquery-filetree/jQueryFileTree.min.js') }}"></script>
<script src="{{ asset('la-assets/plugins/ace/ace.js') }}" type="text/javascript" charset="utf-8"></script>
<script src="{{ asset('la-assets/plugins/ace/ext-modelist.js') }}" type="text/javascript" charset="utf-8"></script>

<script>
var $openFiles = [];
var laeditor = null;
var cntFile;
var modelist = ace.require("ace/ext/modelist");
var $laetabs = $(".laeditor-tabs");

$(function () {
	// Start Jquery File Tree
	$('.la-file-tree').fileTree({
		root: '/',
		script: "{{ url(config('laraadmin.adminRoute') . '/laeditor_get_dir?_token=' . csrf_token()) }}"
	}, function(file) {
		openFile(file);
		// do something with file
		// $('.selected-file').text( $('a[rel="'+file+'"]').text() );
	});
	
	// Start Ace editor
	laeditor = ace.edit("la-ace-editor");
    laeditor.setTheme("ace/theme/twilight");
    laeditor.session.setMode("ace/mode/javascript");
	laeditor.$blockScrolling = Infinity
	laeditor.commands.addCommand({
		name: 'save',
		bindKey: {win: "Ctrl-S", "mac": "Cmd-S"},
		exec: function(editor) {
			// console.log("saving", editor.session.getValue());
			saveFileCode(cntFile, editor.session.getValue());
		}
	})
	
	setEditorSize();
	
	$(window).resize(function() {
		setEditorSize();
	});
});
function setEditorSize() {
	var windowHeight = $(window).height();
	var editorHeight = windowHeight-50-31;
	var treeHeight = windowHeight-70-21;
	// console.log("windowHeight	: "+windowHeight);
	// console.log("editorHeight: "+editorHeight);
	// console.log("treeHeight: "+treeHeight);
	
	$(".la-file-tree").height(treeHeight+"px");
	$("#la-ace-editor").css("height", editorHeight+"px");
	$("#la-ace-editor").css("max-height", editorHeight+"px");
}

$(".laeditor-tabs").on("click", "li i.fa", function(e) {
	filepath = $(this).parent().attr("filepath");
	closeFile(filepath);
	e.stopPropagation();
});
$(".laeditor-tabs").on("click", "li", function(e) {
	filepath = $(this).attr("filepath");
	openFile(filepath);
	e.stopPropagation();
});

function openFile(filepath) {
	var fileFound = fileContains(filepath);
	// console.log("openFile: "+filepath+" fileFound: "+fileFound);
	
	loadFileCode(filepath);
	// console.log($openFiles);
}

function closeFile(filepath) {
	// console.log("closeFile: "+filepath);
	// $openFiles[getFileIndex(filepath)] = null;
	var index = getFileIndex(filepath);
	// console.log("index: "+index);
	$openFiles.splice(index, 1);
	$laetabs.children("li[filepath='"+filepath+"']").remove();
	// console.log($openFiles);
	
	if(index != 0 && $openFiles.length != 0) {
		openFile($openFiles[index-1].filepath);
	} else {
		laeditor.setValue("", -1);
		laeditor.focus();
		laeditor.session.setMode("ace/mode/text");
	}
}

function loadFileCode(filepath, reload = false) {
	// console.log("loadFileCode: "+filepath+" contains: "+fileContains(filepath));
	if(!fileContains(filepath)) {
		$.ajax({
			url: "{{ url(config('laraadmin.adminRoute') . '/laeditor_get_file?_token=' . csrf_token()) }}",
			method: 'POST',
			data: {"filepath": filepath},
			async: false,
			success: function( data ) {
				//console.log(data);
				laeditor.setValue(data, -1);
				laeditor.focus();
				
				var mode = modelist.getModeForPath(filepath).mode;
				laeditor.session.setMode(mode);
				
				// $openFiles[getFileIndex(filepath)].filedata = data;
				// $openFiles[getFileIndex(filepath)].filemode = mode;
				
				$file = {
					"filepath": filepath,
					"filedata": data,
					"filemode": mode
				}
				$openFiles.push($file);
				var filename = filepath.replace(/^.*[\\\/]/, '');
				$laetabs.append('<li filepath="'+filepath+'">'+filename+' <i class="fa fa-5x fa-times"></i></li>');
				highlightFileTab(filepath);
			}
		});
	} else {
		// console.log("File found offline");
		var data = $openFiles[getFileIndex(filepath)].filedata;
		laeditor.setValue(data, -1);
		laeditor.focus();
		var mode = modelist.getModeForPath(filepath).mode;
		laeditor.session.setMode(mode);
		highlightFileTab(filepath);
	}
}

function saveFileCode(filepath, filedata, reload = false) {
	//console.log("saveFileCode: "+filepath);
	$(".laeditor-tabs li[filepath='"+filepath+"'] i.fa").removeClass("fa-times").addClass("fa-spin").addClass("fa-refresh");
	
	$.ajax({
		url: "{{ url(config('laraadmin.adminRoute') . '/laeditor_save_file?_token=' . csrf_token()) }}",
		method: 'POST',
		data: {
			"filepath": filepath,
			"filedata": filedata
		},
		success: function( data ) {
			// console.log(data);
			$(".laeditor-tabs li[filepath='"+filepath+"'] i.fa").removeClass("fa-spin").removeClass("fa-refresh").addClass("fa-times");
		}
	});
}

function highlightFileTab(filepath) {
	cntFile = filepath;
	$laetabs.children("li").removeClass("active");
	$laetabs.children("li[filepath='"+filepath+"']").addClass("active");
}

function getFileIndex(filepath) {
	for (var i=0; i < $openFiles.length; i++) {
		if($openFiles[i].filepath == filepath) {
			return i;
		}
	}
}

function fileContains(filepath) {
	for (var i=0; i < $openFiles.length; i++) {
		if($openFiles[i].filepath == filepath) {
			return true;
		}
	}
	return false;
}

</script>
@endpush