@extends('la.layouts.app')

@section('contentheader_title')
    <a @ajaxload href="{{ url(config('laraadmin.adminRoute') . '/blog_categories') }}">@lang('la_blog_category.blog_category')</a> :
@endsection
@section('contentheader_description', $blog_category->$view_col)
@section('section', app('translator')->get('la_blog_category.blog_categories'))
@section('section_url', url(config('laraadmin.adminRoute') . '/blog_categories'))
@section('sub_section', app('translator')->get('common.edit'))

@section('htmlheader_title', app('translator')->get('la_blog_category.blog_category_edit') . ' : ' . $blog_category->$view_col)

@section('main-content')

    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="box">
        <div class="box-header">

        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    {!! Form::model($blog_category, [
                        'route' => [config('laraadmin.adminRoute') . '.blog_categories.update', $blog_category->id],
                        'method' => 'PUT',
                        'id' => 'blog_category-edit-form',
                    ]) !!}
                    @la_form($module)

                    {{--
                    @la_input($module, 'name')
                    @la_input($module, 'url')
                    @la_input($module, 'description')
                    --}}
                    <br>
                    <div class="form-group">
                        {!! Form::submit(app('translator')->get('common.update'), ['class' => 'btn btn-success']) !!} <a @ajaxload href="{{ url(config('laraadmin.adminRoute') . '/blog_categories') }}"
                            class="btn btn-default pull-right">@lang('common.cancel')</a>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(function() {
            $("#blog_category-edit-form").validate({

            });
        });
    </script>
@endpush
