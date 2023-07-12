<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ LAConfig::getByKey('site_description') }}">
    <meta name="author" content="Dwij IT Solutions">

    <meta property="og:title" content="Blog - {{ LAConfig::getByKey('sitename') }}" />
    <meta property="og:type" content="website" />
    <meta property="og:description" content="{{ LAConfig::getByKey('site_description') }}" />
    
    <meta property="og:url" content="https://laraadmin.com/" />
    <meta property="og:sitename" content="laraAdmin" />
	<meta property="og:image" content="" />
    
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:site" content="@laraadmin" />
    <meta name="twitter:creator" content="@laraadmin" />
    
    <title>Blog - {{ LAConfig::getByKey('sitename') }}</title>
    
    <!-- Bootstrap core CSS -->
    <link href="{{ asset('/la-assets/css/bootstrap.css') }}" rel="stylesheet">

	<link href="{{ asset('la-assets/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css" />
    
    <!-- Custom styles for this template -->
    <link href="{{ asset('/css/home.css') }}" rel="stylesheet">

    <link href='https://fonts.googleapis.com/css?family=Lato:300,400,700,300italic,400italic' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Raleway:400,300,700' rel='stylesheet' type='text/css'>

    <script src="{{ asset('/la-assets/plugins/jQuery/jquery-2.2.3.min.js') }}"></script>
    <script src="{{ asset('/la-assets/plugins/masonry/masonry.pkgd.min.js') }}"></script>
    <script src="{{ asset('/la-assets/plugins/masonry/imagesloaded.pkgd.min.js') }}"></script>
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

<!-- FEATURES WRAP -->
<div id="blog">
    <div class="container">
        <div class="row centered">
            <h1>@lang('home.blog')</h1>
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="blog-sidebar" style="margin-top:20px;">
                    <h3 class="blog-category">Categories</h3>
                    <ul class="blog-categories">
                        @forelse($categories as $category)
                            <li><a href="{{ url('/category/' . $category->url) }}">{{ $category->name }}</a></li>
                        @empty
                            <li>No Categories</li>
                        @endforelse
                    </ul>
                    <h3 class="blog-category">Latest Posts</h3>
                    <ul class="recent-posts">
                        @forelse($recent_posts as $post)
                            <li>
                                <a href="{{ url(config('laraadmin.blogRoute') . '/' . $post->url) }}">
                                    <img src="{{ $post->bannerImage() }}?s=130" />
                                    <span class="post-title">{{ $post->title }}</span><br>
                                    <span class="post-date">{{ $post->date() }}</span>
                                </a>
                            </li>
                        @empty
                            <li>No Categories</li>
                        @endforelse
                    </ul>
                </div>
            </div>
            <div class="col-md-9">
                <div class="grid">
                    <div class="grid-sizer"></div>
                    @foreach($posts as $post)
                        <div class="grid-item">
                            <a class="grid-item-image" href="{{ url(config('laraadmin.blogRoute') . '/' . $post->url) }}">
                                <img src="{{ $post->bannerImage() }}" />
                            </a>
                            <div class="grid-item-inner">
                                <h2 class="grid-item-title">
                                    <a href="{{ url(config('laraadmin.blogRoute') . '/' . $post->url) }}">{{ $post->title }}</a>
                                </h2>
                                <span class="grid-item-date"><i class="fa fa-clock-o"></i> {{ $post->date() }} <span class="pull-right"><i class="fa fa-comments"></i> {{ 0 }} comments</span></span>
                                <div class="grid-item-excerpt">
                                    {{ $post->excerpt }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div><!--/ .container -->
</div><!--/ #features -->

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
    var $grid = $('.grid').imagesLoaded( function() {
        $grid.masonry({
            fitWidth: true,
            gutter: 10,
            itemSelector: '.grid-item',
            columnWidth: '.grid-sizer'
        }); 
    });
</script>
</body>
</html>