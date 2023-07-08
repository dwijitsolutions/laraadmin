<!-- Collect the nav links, forms, and other content for toggling -->
<div class="collapse navbar-collapse pull-left" id="navbar-collapse">
	<ul class="nav navbar-nav">
		<li><a @ajaxload href="{{ url(config('laraadmin.adminRoute')) }}">Dashboard</a></li>
		<?php
		$menuItems = App\Models\LAMenu::where("parent", 0)->orderBy('hierarchy', 'asc')->get();
		?>
		@foreach ($menuItems as $menu)
			@if($menu->type == "module")
				<?php
				$temp_module_obj = LAModule::get($menu->name);
				?>
				@la_access($temp_module_obj->id)
					@if(isset($module->id) && $module->name == $menu->name)
						<?php echo LAHelper::print_menu_topnav($menu ,true); ?>
					@else
						<?php echo LAHelper::print_menu_topnav($menu); ?>
					@endif
				@endla_access
			@else
				<?php echo LAHelper::print_menu_topnav($menu); ?>
			@endif
		@endforeach
	</ul>
</div><!-- /.navbar-collapse -->