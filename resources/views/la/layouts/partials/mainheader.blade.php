<!-- Main Header -->
<header class="main-header">

	@if(LAConfig::getByKey('layout') != 'layout-top-nav')
	<!-- Logo -->
	<a @ajaxload href="{{ url(config('laraadmin.adminRoute')) }}" class="logo">
		<!-- mini logo for sidebar mini 50x50 pixels -->
		<span class="logo-mini"><b>{{ LAConfig::getByKey('sitename_short') }}</b></span>
		<!-- logo for regular state and mobile devices -->
		<span class="logo-lg"><b>{{ LAConfig::getByKey('sitename_part1') }}</b>
		 {{ LAConfig::getByKey('sitename_part2') }}</span>
	</a>
	@endif

	<!-- Header Navbar -->
	<nav class="navbar navbar-static-top" role="navigation">
	@if(LAConfig::getByKey('layout') == 'layout-top-nav')
		<div class="container">
			<div class="navbar-header">
				<a @ajaxload href="{{ url(config('laraadmin.adminRoute')) }}" class="navbar-brand"><b>{{ LAConfig::getByKey('sitename_part1') }}</b>{{ LAConfig::getByKey('sitename_part2') }}</a>
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
					<i class="fa fa-bars"></i>
				</button>
			</div>
			@include('la.layouts.partials.top_nav_menu')
			@include('la.layouts.partials.notifs')
		</div><!-- /.container-fluid -->
	@else
		<!-- Sidebar toggle button-->
		<a href="#" class="sidebar-toggle b-l" data-toggle="offcanvas" role="button">
			<span class="sr-only">Toggle navigation</span>
		</a>
		<a href="{{ url('/') }}" class="btn-navbar"><i class="fa fa-home"></i></a>

        @if(LAConfig::getByKey('topbar_search'))
		<form id="navbar-search-form" action="{{ url(config('laraadmin.adminRoute') . '/search') }}" method="get" class="navbar-search-form">
            <div class="form-bg">
                <input class="typeahead" type="text" placeholder="Search">
            </div>
        </form>
        @endif
		
		@include('la.layouts.partials.notifs')
	@endif
	</nav>
</header>

@push('scripts')
@if(LAConfig::getByKey('topbar_search'))
<script>
$(function () {
	<?php
	$search_modules = array();
	?>
	@foreach (LAModule::all() as $search_module)
		<?php
		$module_name = $search_module->name;
		$model_filepath = "";
		if($module_name == "Roles" || $module_name == "Users" || $module_name == "Permissions") {
            $model_path = "\App\\";
			$model_filepath = app_path(''.$search_module->model.".php");
        } else {
            $model_path = "\App\Models\\";
			$model_filepath = app_path('Models/'.$search_module->model.".php");
        }
		if(file_exists($model_filepath) && in_array('Laravel\Scout\Searchable', class_uses($model_path.$search_module->model))) {
		$search_modules[] = $search_module; 
		?>

	var {{ $search_module->name_db }}Data = new Bloodhound({
		datumTokenizer: Bloodhound.tokenizers.obj.whitespace('q'),
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: "{{ url(config('laraadmin.adminRoute').'/find/'.$search_module->name.'?q=%QUERY%') }}",
			wildcard: '%QUERY%'
		},
	});
		<?php
		}
		?>
	@endforeach

	$('#navbar-search-form .typeahead').typeahead({
		hint: true,
        highlight: true,
        minLength: 1
	},
	@foreach ($search_modules as $search_module)

	{
		name: '{{ $search_module->name_db }}-data',
        display: 'name',
        source: {{ $search_module->name_db }}Data.ttAdapter(),
		templates: {
			header: ['<h3 class="list-group module-{{ $search_module->name_db }}">{{ $search_module->name }}</h3>'],
			suggestion: function (data) {
				return '<a @ajaxload href="{{ url(config("laraadmin.adminRoute")) }}/{{ $search_module->name_db }}/'+data.id+'" class="list-group-item">' + data.name+'</a>'
				
			}
		}
	}@if(!$loop->last), @endif
	@endforeach
	);

    $("#navbar-search-form .twitter-typeahead .tt-input").on("focus", function() {
        var rect = $(this).parent()[0].getBoundingClientRect();
        $(this).parent().css({top: rect.top+"px", left: rect.left + "px"});
        $(this).parent().parent().addClass("active");
    }).on("blur", function() {
        $(this).parent().css({top: "auto", left: "auto"});
        $(this).parent().parent().removeClass("active");
        if($("#navbar-search-form").hasClass("mobile-view")) {
            $("#navbar-search-form").attr("style", "");
        }
    });

    $(".btn-navbar-search").on("click", function () {
        $("#navbar-search-form").addClass("mobile-view");
        $("#navbar-search-form").show();
        $("#navbar-search-form .twitter-typeahead .tt-input").focus();
    });
});
</script>
@endif
@endpush