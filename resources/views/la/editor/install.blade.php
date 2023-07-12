@extends('la.layouts.app')

@section("contentheader_title", app('translator')->get('la_editor.la_code_editor'))
@section("contentheader_description", app('translator')->get('la_editor.inst_instn'))
@section("section", app('translator')->get('la_editor.la_code_editor'))
@section("sub_section", app('translator')->get('la_editor.not_installed'))
@section("htmlheader_title", app('translator')->get('la_editor.inst_la_code_editor'))

@section('main-content')

<div class="box">
	<div class="box-header">
		
	</div>
	<div class="box-body">
		<p>LaraAdmin Code Editor does not comes inbuilt now. You can get it by following commands.</p>
		<pre><code>composer require laraadmin/editor</code></pre>
		<p>This will download the editor package. Not install editor by following command:</p>
		<pre><code>php artisan la:editor</code></pre>
		<p>Now refresh this page or go to <a @ajaxload href="{{ url(config('laraadmin.adminRoute') . '/la_editor') }}">{{ url(config('laraadmin.adminRoute') . '/la_editor') }}</a>.</p>
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