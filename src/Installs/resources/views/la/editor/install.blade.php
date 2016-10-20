@extends('la.layouts.app')

@section("contentheader_title", "LA Code Editor")
@section("contentheader_description", "Installation instructions")
@section("section", "LA Code Editor")
@section("sub_section", "Not installed")
@section("htmlheader_title", "Install LA Code Editor")

@section('main-content')

<div class="box">
	<div class="box-header">
		
	</div>
	<div class="box-body">
		<p>LaraAdmin Code Editor does not comes inbuilt now. You can get it by following commands.</p>
		<pre><code>composer require dwij/laeditor</code></pre>
		<p>This will download the editor package. Not install editor by following command:</p>
		<pre><code>php artisan la:editor</code></pre>
		<p>Now refresh this page or go to <a href="{{ url(config('laraadmin.adminRoute') . '/laeditor') }}">{{ url(config('laraadmin.adminRoute') . '/laeditor') }}</a>.</p>
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