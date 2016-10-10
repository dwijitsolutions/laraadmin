<script src="{{ asset('la-assets/plugins/dropzone/dropzone.js') }}"></script>

<div class="modal fade" id="fm" tabindex="-1" role="dialog" aria-labelledby="fileManagerLabel">
	<input type="hidden" id="image_selecter_origin" value="">
	<input type="hidden" id="image_selecter_origin_type" value="">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="fileManagerLabel">Select File</h4>
			</div>
			<div class="modal-body p0">
				<div class="row">
					<div class="col-xs-3 col-sm-3 col-md-3">
						<div class="fm_folder_selector">
							<form action="{{ url(config('laraadmin.adminRoute') . '/upload_files')}}" id="fm_dropzone" enctype="multipart/form-data" method="POST">
								{{ csrf_field() }}
								<div class="dz-message"><i class="fa fa-cloud-upload"></i><br>Drop files here to upload</div>
								
								@if(!config('laraadmin.uploads.private_uploads'))
									<label class="fm_folder_title">Is Public ?</label>
									{{ Form::checkbox("public", "public", config("laraadmin.uploads.default_public"), []) }}
									<div class="Switch Ajax Round On"><div class="Toggle"></div></div>
								@endif
							</form>
						</div>
					</div>
					<div class="col-xs-9 col-sm-9 col-md-9 pl0">
						<div class="nav">
							<div class="row">
								<div class="col-xs-2 col-sm-7 col-md-7"></div>
								<div class="col-xs-10 col-sm-5 col-md-5">
									<input type="search" class="form-control pull-right" placeholder="Search file name">
								</div>
							</div>
						</div>
						<div class="fm_file_selector">
							<ul>
								
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>