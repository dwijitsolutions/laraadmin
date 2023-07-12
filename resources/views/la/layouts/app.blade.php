@php
$pjax = false;
if(isset($_GET['_pjax'])) {
    $pjax = true;
}
@endphp

@if(isset($pjax) && $pjax == true)

    @if(!isset($no_header))
        @include('la.layouts.partials.contentheader')
    @endif

    <!-- Main content -->
    <section class="content {{ $no_padding ?? '' }}">
        <!-- Your Page Content Here -->
        @yield('main-content')
    </section><!-- /.content -->
    
    @stack('styles')
    @stack('scripts')

@else
<!DOCTYPE html>
<html lang="en">

@section('htmlheader')
    @include('la.layouts.partials.htmlheader')
@show
<?php
$layout_str = "";
$layout = LAConfig::getByKey('layout');
if($layout == "fixed") {
    $layout_str = "fixed";
} else if($layout == "sidebar-mini") {
    $layout_str = "sidebar-mini sidebar-collapse";
} else if($layout == "fixed-sidebar-mini") {
    $layout_str = "fixed sidebar-mini sidebar-collapse";
} else if($layout == "sidebar-collapse") {
    $layout_str = "sidebar-collapse";
} else if($layout == "layout-boxed") {
    $layout_str = "layout-boxed";
} else if($layout == "layout-top-nav") {
    $layout_str = "layout-top-nav";
}
?>
<body class="{{ LAConfig::getByKey('skin') }} {{ $layout_str }}" bsurl="{{ url('') }}" adminRoute="{{ config('laraadmin.adminRoute') }}">
<div class="wrapper">

	@include('la.layouts.partials.mainheader')

	@if(LAConfig::getByKey('layout') != 'layout-top-nav')
		@include('la.layouts.partials.sidebar')
	@endif

	<!-- Content Wrapper. Contains page content -->
	<div id="content-wrapper" class="content-wrapper">
        @if(LAConfig::getByKey('layout') == 'layout-top-nav') <div class="container"> @endif
		@if(!isset($no_header))
			@include('la.layouts.partials.contentheader')
		@endif
		
		<!-- Main content -->
		<section class="content {{ $no_padding ?? '' }}">
			<!-- Your Page Content Here -->
			@yield('main-content')
		</section><!-- /.content -->
        
		@if(LAConfig::getByKey('layout') == 'layout-top-nav') </div> @endif
	</div><!-- /.content-wrapper -->

	@include('la.layouts.partials.controlsidebar')

	@include('la.layouts.partials.footer')

</div><!-- ./wrapper -->

@include('la.layouts.partials.file_manager')
@include('la.layouts.partials.quick_add')

@section('scripts')
	@include('la.layouts.partials.scripts')
@show

</body>
</html>

@endif