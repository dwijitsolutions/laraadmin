@extends('la.layouts.app')

@section('contentheader_title')
    <a @ajaxload href="{{ url(config('laraadmin.adminRoute') . '/blog_posts') }}">@lang('la_blog_post.blog_posts')</a> :
@endsection
@section('contentheader_description', $blog_post->$view_col)
@section('section', app('translator')->get('la_blog_post.blog_posts'))
@section('section_url', url(config('laraadmin.adminRoute') . '/blog_posts'))
@section('sub_section', app('translator')->get('common.edit'))

@section('htmlheader_title', app('translator')->get('la_blog_post.blog_post_edit') . ' : ' . $blog_post->$view_col)

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
                    {!! Form::model($blog_post, [
                        'route' => [config('laraadmin.adminRoute') . '.blog_posts.update', $blog_post->id],
                        'method' => 'PUT',
                        'id' => 'blog_post-edit-form',
                    ]) !!}
                    @la_form($module)

                    {{--
                    @la_input($module, 'title')
                    @la_input($module, 'url')
                    @la_input($module, 'category_id')
                    @la_input($module, 'status')
                    @la_input($module, 'author_id')
                    @la_input($module, 'tags')
                    @la_input($module, 'post_date')
                    @la_input($module, 'excerpt')
                    @la_input($module, 'banner')
                    @la_input($module, 'content')
                    --}}
                    <br>
                    <div class="form-group">
                        {!! Form::button(app('translator')->get('common.update'), ['class' => 'btn btn-success', 'type' => 'submit']) !!} <a @ajaxload href="{{ url(config('laraadmin.adminRoute') . '/blog_posts') }}"
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
        var submitBtn = null;
        var formObj = null;

        $(function() {
            @la_access('Blog_posts', 'edit')
            // Edit BlogPost REST Request
            submitBtn = $('#blog_post-edit-form button[type=submit]');
            formObj = $("#blog_post-edit-form");

            formObj.validate({
                submitHandler: function(form, event) {
                    event.preventDefault();
                    $.ajax({
                        url: formObj.attr('action'),
                        method: 'PUT',
                        contentType: 'json',
                        headers: {
                            'X-CSRF-Token': '{{ csrf_token() }}'
                        },
                        data: getFormDataJSON(formObj),
                        beforeSend: function() {
                            submitBtn.html('<i class="fa fa-refresh fa-spin mr5"></i> Updating...');
                            submitBtn.prop('disabled', true);
                        },
                        success: function(data) {
                            if (data.status == "success") {
                                show_success("BlogPost Update", data);
                            } else {
                                show_failure("BlogPost Update", data);
                            }
                            submitBtn.html('Update');
                            submitBtn.prop('disabled', false);
                            if (isset(data.redirect)) {
                                window.location.href = data.redirect;
                            }
                        },
                        error: function(data) {
                            show_failure("BlogPost Update", data);
                            submitBtn.html('Update');
                            submitBtn.prop('disabled', false);
                            if (isset(data.redirect)) {
                                window.location.href = data.redirect;
                            }
                        }
                    });
                    return false;
                }
            });
            @endla_access
        });
    </script>
@endpush
