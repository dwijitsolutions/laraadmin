@extends("la.layouts.app")

@section("contentheader_title", "Configuration")
@section("contentheader_description", "")
@section("section", "Configuration")
@section("sub_section", "")
@section("htmlheader_title", "Configuration")

@section("headerElems")
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
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<form action="{{route(config('laraadmin.adminRoute').'.la_configs.store')}}" method="POST">
				<!-- general form elements disabled -->
				<div class="box box-warning">
					<div class="box-header with-border">
						<h3 class="box-title">GUI Settings</h3>
					</div>
					<!-- /.box-header -->
					<div class="box-body">
						{{ csrf_field() }}
						<!-- text input -->
						<div class="form-group">
							<label>Sitename First Word</label>
							<input type="text" class="form-control" placeholder="Lara" name="sitename_part1" value="{{$configs[0]->value}}">
						</div>
						<div class="form-group">
							<label>Sitename Second Word</label>
							<input type="text" class="form-control" placeholder="Admin 1.0" name="sitename_part2" value="{{$configs[1]->value}}">
						</div>
						<div class="form-group">
							<label>Sitename Short (2/3 Characters)</label>
							<input type="text" class="form-control" placeholder="LA" maxlength="2" name="sitename_short" value="{{$configs[2]->value}}">
						</div>
						<!-- checkbox -->
						<div class="form-group">
							<div class="checkbox">
								<label>
									<input type="checkbox" name="sidebar_search" @if($configs[3]->value=="on") checked @endif>
									Show Search Bar
								</label>
							</div>
							<div class="checkbox">
								<label>
									<input type="checkbox" name="show_messages" @if($configs[4]->value=="on") checked @endif>
									Show Messages Icon
								</label>
							</div>
							<div class="checkbox">
								<label>
									<input type="checkbox" name="show_notifications" @if($configs[5]->value=="on") checked @endif>
									Show Notifications Icon
								</label>
							</div>
							<div class="checkbox">
								<label>
									<input type="checkbox" name="show_tasks" @if($configs[6]->value=="on") checked @endif>
									Show Tasks Icon
								</label>
							</div>
							<div class="checkbox">
								<label>
									<input type="checkbox" name="show_rightsidebar" @if($configs[7]->value=="on") checked @endif>
									Show Right SideBar Icon
								</label>
							</div>
						</div>
						<!-- select -->
						<div class="form-group">
							<label>Skin Color</label>
							<select class="form-control" name="skin">
								@foreach($skins as $name=>$property)
									<option value="{{ $property }}" @if($configs[8]->value == $property) selected @endif>{{ $name }}</option>
								@endforeach
							</select>
						</div>
						
						<div class="form-group">
							<label>Layout</label>
							<select class="form-control" name="layout">
								@foreach($layouts as $name=>$property)
									<option value="{{ $property }}" @if($configs[9]->value == $property) selected @endif>{{ $name }}</option>
								@endforeach
							</select>
						</div>
					</div><!-- /.box-body -->
					<div class="box-footer">
						<button type="submit" class="btn btn-primary">Save</button>
					</div><!-- /.box-footer -->
				</div><!-- /.box -->
			</form>
		</div><!-- /.col-md-12 -->
	</div><!-- /.row -->
</section>

@endsection

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('la-assets/plugins/datatables/datatables.min.css') }}"/>
@endpush

@push('scripts')
<script src="{{ asset('la-assets/plugins/datatables/datatables.min.js') }}"></script>

@endpush
