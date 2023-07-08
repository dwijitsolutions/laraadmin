<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $post->excerpt }}">
    <meta name="author" content="Dwij IT Solutions">

    <meta property="og:title" content="{{ $post->title . " - " . LAConfig::getByKey('sitename') }}" />
    <meta property="og:type" content="website" />
    <meta property="og:description" content="{{ $post->excerpt }}" />
    
    <meta property="og:url" content="https://laraadmin.com/" />
    <meta property="og:sitename" content="LaraAdmin" />
	<meta property="og:image" content="" />
    
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:site" content="@laraadmin" />
    <meta name="twitter:creator" content="@laraadmin" />
    
    <title>{{ $post->title . " - " . LAConfig::getByKey('sitename') }}</title>
    
    <!-- Bootstrap core CSS -->
    <link href="{{ asset('/la-assets/css/bootstrap.css') }}" rel="stylesheet">

	<link href="{{ asset('la-assets/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css" />
    
    <!-- Custom styles for this template -->
    <link href="{{ asset('/css/home.css') }}" rel="stylesheet">

    <link href='https://fonts.googleapis.com/css?family=Lato:300,400,700,300italic,400italic' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Raleway:400,300,700' rel='stylesheet' type='text/css'>

    <script src="{{ asset('/la-assets/plugins/jQuery/jquery-2.2.3.min.js') }}"></script>
    <script src="{{ asset('/la-assets/plugins/SmoothScroll/smoothscroll.js') }}"></script>
</head>

<body data-spy="scroll" data-offset="0" data-target="#navigation">

<!-- Fixed navbar -->
<div id="navigation" class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="{{ url('/home') }}"><b>{{ LAConfig::getByKey('sitename') }}</b></a>
        </div>
        <div class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li><a href="{{ url('/home') }}" class="smoothScroll">@lang('home.home')</a></li>
                <li><a href="{{ url('/home#about') }}" class="smoothScroll">@lang('home.about')</a></li>
                <li><a href="{{ url('/home#contact') }}" class="smoothScroll">@lang('home.contact')</a></li>
                <li class="active"><a href="{{ url('/blog') }}" class="smoothScroll">@lang('home.blog')</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                @if (Auth::guest())
                    <li><a href="{{ url('/login') }}">@lang('home.login')</a></li>
                    <!--<li><a href="{{ url('/register') }}">Register</a></li>-->
                @else
                    <li><a href="{{ url(config('laraadmin.adminRoute')) }}">{{ Auth::user()->name }}</a></li>
                @endif
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</div>
@php
$back
@endphp
<div id="blog-header" style="background: url('{{ $post->bannerImage(true) }}')">
    <div class="overlay">
        <div class="container">
            <div class="row">
                <h1>{{ $post->title }}</h1>
                <h5>
                    <i class="fa fa-clock-o"></i> {{ $post->date() }} &nbsp;|&nbsp;
                    <span class="blog-tags">
                        @php
                        $tags = json_decode($post->tags);
                        @endphp
                        @foreach($tags as $tag)
                            <span class="badge">{{ $tag }}</span>
                        @endforeach
                    </span>
                </h5>
            </div>
        </div> <!--/ .container -->
    </div>
</div><!--/ #blog-header -->

<div id="content">
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="blog-sidebar">
                    @if(isset($post->category->id))
                        <h5 class="blog-category"><b>Category:</b> <a href="{{ url('/category/' . $post->category->url) }}" class="badge">{{ $post->category->name }}</a></h5>
                    @endif
                    <!-- User Info -->
                    <img src="{{ $post->bannerImage() }}" />
                    <div class="blog-excerpt">{{ $post->excerpt }}</div>
                    <!-- User Info -->
                    <div class="user-profile">
                        @if(isset($post->author->id))
                            <img class="profile_img" src="{{ $post->author->profileImageUrl() }}" />
                            <h4 class="user-name">{{ $post->author->name }}</h4>
                            <p class="user-about">{{ $post->author->about }}</p>
                        @endif
                    </div>
                    <h3 class="blog-category">Latest Posts</h3>
                    <ul class="recent-posts">
                        @forelse($recent_posts as $recent_post)
                            <li>
                                <a href="{{ url(config('laraadmin.blogRoute') . '/' . $recent_post->url) }}">
                                    <img src="{{ $recent_post->bannerImage() }}?s=130" />
                                    <span class="post-title">{{ $recent_post->title }}</span><br>
                                    <span class="post-date">{{ $recent_post->date() }}</span>
                                </a>
                            </li>
                        @empty
                            <li>No Categories</li>
                        @endforelse
                    </ul>
                </div>
            </div>
            <div class="col-md-9">
                <div class="blog-content">
                    {!! $post->content !!}
                </div>
            </div>
        </div>
    </div> <!--/ .container -->
</div><!--/ #introwrap -->

<div id="c">
    <div class="container">
        <p>
            <strong>Copyright &copy; 2016. Powered by <a href="https://dwijitsolutions.com" title="Web & Mobile Development Company in Pune, India"><b>Dwij IT Solutions</b></a>
        </p>
    </div>
</div>


<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="{{ asset('/la-assets/js/bootstrap.min.js') }}" type="text/javascript"></script>
<script>

</script>
</body>
</html>
