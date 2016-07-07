@extends("la.layouts.app")

<?php
use Dwij\Laraadmin\Models\Module;
?>

@section("contentheader_title", "Menus")
@section("contentheader_description", "Editor")
@section("section", "Menus")
@section("sub_section", "Editor")
@section("htmlheader_title", "Menu Editor")

@section("headerElems")

@endsection

@section("main-content")

<div class="box box-success menus">
	<!--<div class="box-header"></div>-->
	<div class="box-body">
		<div class="row">
			<div class="col-md-4 col-lg-4">
				<div class="nav-tabs-custom">
					<ul class="nav nav-tabs">
						<li class="active"><a href="#tab-modules" data-toggle="tab">Modules</a></li>
						<li><a href="#tab-custom-link" data-toggle="tab">Custom Links</a></li>
					</ul>
					<div class="tab-content">
						<div class="tab-pane active" id="tab-modules">
							<ul>
							@foreach ($modules as $module)
								<li><i class="fa {{ $module->fa_icon }}"></i> {{ $module->name }} <a class="pull-right"><i class="fa fa-plus"></i></a></li>
							@endforeach
							</ul>
						</div>
						<div class="tab-pane" id="tab-custom-link">
							{!! Form::open(['id' => 'menu-custom-form']) !!}
								<div class="form-group">
									<label for="url" style="font-weight:normal;">URL</label>
									<input class="form-control" placeholder="URL" name="url" type="text" value="http://" data-rule-minlength="1" required>
								</div>
								<div class="form-group">
									<label for="label" style="font-weight:normal;">Label</label>
									<input class="form-control" placeholder="Label" name="label" type="text" value=""  data-rule-minlength="1" required>
								</div>
								<div class="form-group">
									<label for="icon" style="font-weight:normal;">Icon</label>
									<div class="input-group">
										<input class="form-control" placeholder="FontAwesome Icon" name="icon" type="text" value="fa-cube"  data-rule-minlength="1" required>
										<span class="input-group-addon"></span>
									</div>
								</div>
								<input type="submit" class="btn btn-primary pull-right mr10" value="Add to menu">
							{!! Form::close() !!}
						</div>
					</div><!-- /.tab-content -->
				</div><!-- nav-tabs-custom -->
			</div>
			<div class="col-md-8 col-lg-8">
				<div class="dd" id="menu-nestable">
					<ol class="dd-list">
						@foreach ($menus as $menu)
							<?php echo LAHelper::print_menu_editor($menu); ?>
						@endforeach
						
						<!--
						<li class="dd-item dd3-item" data-id="13">
							<div class="dd-handle dd3-handle">Drag</div><div class="dd3-content">Item 13 <button class="btn btn-xs btn-danger pull-right"><i class="fa fa-times"></i></button> <button class="btn btn-xs btn-success pull-right"><i class="fa fa-edit"></i></button></div>
						</li>
						<li class="dd-item dd3-item" data-id="14">
							<div class="dd-handle dd3-handle">Drag</div><div class="dd3-content">Item 14</div>
						</li>
						<li class="dd-item dd3-item" data-id="15">
							<div class="dd-handle dd3-handle">Drag</div><div class="dd3-content">Item 15</div>
							<ol class="dd-list">
								<li class="dd-item dd3-item" data-id="16">
									<div class="dd-handle dd3-handle">Drag</div><div class="dd3-content">Item 16</div>
								</li>
								<li class="dd-item dd3-item" data-id="17">
									<div class="dd-handle dd3-handle">Drag</div><div class="dd3-content">Item 17</div>
								</li>
								<li class="dd-item dd3-item" data-id="18">
									<div class="dd-handle dd3-handle">Drag</div><div class="dd3-content">Item 18</div>
								</li>
							</ol>
						</li>
						-->
					</ol>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('la-assets/plugins/nestable/jquery.nestable.js') }}"></script>
<script src="{{ asset('la-assets/plugins/iconpicker/fontawesome-iconpicker.js') }}"></script>
<script>
$(function () {
	$('input[name=icon]').iconpicker();

	$('#menu-nestable').nestable({
        group: 1
    });
	$("#menu-custom-form").validate({
		
	});
});
</script>
@endpush