<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ LAConfig::getByKey('site_description') }}">
    <meta name="author" content="Dwij IT Solutions">

    <meta property="og:title" content="{{ LAConfig::getByKey('sitename') }}" />
    <meta property="og:type" content="website" />
    <meta property="og:description" content="{{ LAConfig::getByKey('site_description') }}" />

    <link rel="shortcut icon" type="image/ico" href="{{ asset('favicon.ico') }}">

    <meta property="og:url" content="https://laraadmin.com/" />
    <meta property="og:sitename" content="laraAdmin" />
    <meta property="og:image" content="" />

    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:site" content="@laraadmin" />
    <meta name="twitter:creator" content="@laraadmin" />

    <title>{{ LAConfig::getByKey('sitename') }}</title>

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
                <a class="navbar-brand" href="#"><b>{{ LAConfig::getByKey('sitename') }}</b></a>
            </div>
            <div class="navbar-collapse collapse">
                <ul class="nav navbar-nav">
                    <li class="active"><a href="#home" class="smoothScroll">@lang('home.home')</a></li>
                    <li><a href="#about" class="smoothScroll">@lang('home.about')</a></li>
                    <li><a href="#contact" class="smoothScroll">@lang('home.contact')</a></li>
                    <li><a href="{{ url('/blog') }}" class="smoothScroll">@lang('home.blog')</a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    @if (Auth::guest())
                        <li><a href="{{ url('/login') }}">@lang('home.login')</a></li>
                        <!--<li><a href="{{ url('/register') }}">Register</a></li>-->
                    @else
                        <li><a href="{{ url(config('laraadmin.adminRoute')) }}">{{ Auth::user()->name }}</a></li>
                    @endif
                </ul>
            </div>
            <!--/.nav-collapse -->
        </div>
    </div>


    <section id="home" name="home"></section>
    <div id="headerwrap">
        <div class="container">
            <div class="row centered">
                <div class="col-lg-12">
                    <h1>{{ LAConfig::getByKey('sitename_part1') }} <b><a>{{ LAConfig::getByKey('sitename_part2') }}</a></b></h1>
                    <h3>{{ LAConfig::getByKey('site_description') }}</h3>
                    <h3><a href="{{ url('/login') }}" class="btn btn-lg btn-success">@lang('home.get_started') !</a></h3><br>
                </div>
                <div class="col-lg-2">
                    <h5>@lang('home.amaze_funct')</h5>
                    <p>@lang('home.mod_adm_panl')</p>
                    <img class="hidden-xs hidden-sm hidden-md" src="{{ asset('/la-assets/img/arrow1.png') }}">
                </div>
                <div class="col-lg-8">
                    <img class="img-responsive" src="{{ asset('/la-assets/img/app-bg.png') }}" alt="">
                </div>
                <div class="col-lg-2">
                    <br>
                    <img class="hidden-xs hidden-sm hidden-md" src="{{ asset('/la-assets/img/arrow2.png') }}">
                    <h5>@lang('home.comp_pckd')...</h5>
                    <p>@lang('home.comp_pckd_sub')</p>
                </div>
            </div>
        </div>
        <!--/ .container -->
    </div>
    <!--/ #headerwrap -->


    <section id="about" name="about"></section>
    <!-- INTRO WRAP -->
    <div id="intro">
        <div class="container">
            <div class="row centered">
                <h1>@lang('home.architecture_designed')</h1>
                <br>
                <br>
                <div class="col-lg-4">
                    <i class="fa fa-cubes" style="font-size:100px;height:110px;"></i>
                    <h3>@lang('home.modular')</h3>
                    <p>@lang('home.future_expn')</p>
                </div>
                <div class="col-lg-4">
                    <i class="fa fa-paper-plane" style="font-size:100px;height:110px;"></i>
                    <h3>@lang('home.easy_to_install')</h3>
                    <p>@lang('home.easy_to_install_sub')</p>
                </div>
                <div class="col-lg-4">
                    <i class="fa fa-cubes" style="font-size:100px;height:110px;"></i>
                    <h3>@lang('home.customizable')</h3>
                    <p>@lang('home.customizable_sub')</p>
                </div>
            </div>
            <br>
            <hr>
        </div>
        <!--/ .container -->
    </div>
    <!--/ #introwrap -->

    <!-- FEATURES WRAP -->
    <div id="features">
        <div class="container">
            <div class="row">
                <div class="col-lg-5 centered">
                    <img class="centered" src="{{ asset('/la-assets/img/mobile.png') }}" alt="">
                </div>

                <div class="col-lg-7">
                    <h3 class="feature-title">What is LaraAdmin ?</h3><br>
                    <ol class="features">
                        <li><strong>CMS</strong> (Content Management System) &#8211; Manages Modules &amp; their Data</li>
                        <li>Backend <strong>Admin Panel</strong> &#8211; Data can be used in front end applications with ease.</li>
                        <li>A probable <strong>CRM</strong> System &#8211; Can be evolved into a CRM system like <a target="_blank"
                                href="https://www.sugarcrm.com">SugarCRM</a></li>
                    </ol><br>

                    <h3 class="feature-title">Why LaraAdmin ?</h3><br>
                    <ol class="features">
                        <li><strong>Philosophy:</strong> Inspired by SugarCRM &amp; based on Advanced <strong>Data Types</strong> like Image, HTML,
                            File, Dropdown, TagInput which makes developers job easy. See more in <a target="_blank"
                                href="https://laraadmin.com/features">features</a></li>
                        <li>Superior <strong>CRUD generation</strong> for Modules which generates Migration, Controller, Model and Views with single
                            artisan command and integrates with Routes as as well.</li>
                        <li><strong>Form Maker</strong> helper is provided for generating entire form with single function call with module name as
                            single parameter. It also gives you freedom to customise form for every field by providing method to generate single field
                            with parameters for customisations.</li>
                        <li><b>Upload Manager </b>manages project files &amp; images which are integrated with your Module fields.</li>
                        <li><strong>Menu Manager</strong> creates menu with Modules &amp; Custom Links likes WordPress</li>
                        <li><strong>Online Code Editor</strong> allows developers to customise the generated Module Views &amp; Files.</li>
                    </ol>
                </div>
            </div>
        </div>
        <!--/ .container -->
    </div>
    <!--/ #features -->

    <!-- FEATURES WRAP -->
    <div id="blog">
        <div class="container">
            <div class="row centered">
                <h1>Blogs</h1>
            </div>
            <div class="row">

                <div class="grid">
                    <div class="grid-sizer"></div>
                    @foreach ($posts as $post)
                        <div class="grid-item">
                            <a class="grid-item-image" href="{{ url(config('laraadmin.blogRoute') . '/' . $post->url) }}">
                                <img src="{{ $post->bannerImage() }}" />
                            </a>
                            <div class="grid-item-inner">
                                <h2 class="grid-item-title">
                                    <a href="{{ url(config('laraadmin.blogRoute') . '/' . $post->url) }}">{{ $post->title }}</a>
                                </h2>
                                <span class="grid-item-date"><i class="fa fa-clock-o"></i> {{ $post->date() }}
                                    <div class="grid-item-excerpt">
                                        {{ $post->excerpt }}
                                    </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
        <!--/ .container -->
    </div>
    <!--/ #features -->

    <section id="contact" name="contact"></section>
    <div id="footerwrap">
        <div class="container">
            <div class="col-lg-5">
                <h3>Contact Us</h3><br>
                <p>
                    Dwij IT Solutions,<br />
                    Web Development Company in Pune,<br />
                    B4, Patang Plaza Phase 5,<br />
                    Opp. PICT College,<br />
                    Katraj, Pune, India - 411046
                </p>
                <div class="contact-link"><i class="fa fa-envelope-o"></i> <a href="mailto:hello@laraadmin.com">hello@laraadmin.com</a></div>
                <div class="contact-link"><i class="fa fa-cube"></i> <a href="https://laraadmin.com">laraadmin.com</a></div>
                <div class="contact-link"><i class="fa fa-building"></i> <a href="https://dwijitsolutions.com">dwijitsolutions.com</a></div>
            </div>

            <div class="col-lg-7">
                <h3>@lang('home.contact_form.drop_line')</h3>
                <br>
                <form id="inquiryForm" role="form" action="{{ url('/inquiry') }}" method="post">
                    {{ csrf_field() }}
                    @if (\Session::has('success'))
                        <div class="alert alert-success">
                            <ul>
                                <li>{!! \Session::get('success') !!}</li>
                            </ul>
                        </div>
                    @endif
                    <div class="form-group">
                        <label for="name">@lang('home.contact_form.your_name')</label>
                        <input type="name" name="name" class="form-control" placeholder="@lang('home.contact_form.your_name')">
                    </div>
                    <div class="form-group">
                        <label for="phone">@lang('home.contact_form.phone_num')</label>
                        <input type="tel" name="phone" class="form-control" placeholder="@lang('home.contact_form.enter_phone')">
                    </div>
                    <div class="form-group">
                        <label for="email">@lang('home.contact_form.email_address')</label>
                        <input type="email" name="email" class="form-control" placeholder="@lang('home.contact_form.enter_email')">
                    </div>
                    <div class="form-group">
                        <label for="message">@lang('home.contact_form.your_text')</label>
                        <textarea class="form-control" name="message" rows="3"></textarea>
                    </div>
                    <br>
                    <button type="submit" class="btn btn-large btn-success">@lang('home.contact_form.submit')</button>
                </form>
            </div>
        </div>
    </div>
    <div id="c">
        <div class="container">
            <p>
                <strong>Copyright &copy; 2019. Powered by <a href="https://dwijitsolutions.com/crm"
                        title="CRM Solution Company in Pune, India"><b>LaraAdmin Plus</b></a>
            </p>
        </div>
    </div>


    <!-- Bootstrap core JavaScript
================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="{{ asset('la-assets/js/bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('la-assets/plugins/jquery-validation/jquery.validate.min.js') }}" type="text/javascript"></script>
    <script>
        $('.carousel').carousel({
            interval: 3500
        });

        var $grid = $('.grid').imagesLoaded(function() {
            $grid.masonry({
                fitWidth: true,
                gutter: 10,
                itemSelector: '.grid-item',
                columnWidth: '.grid-sizer'
            });
        });

        $("#inquiryForm").validate({

        });
    </script>
</body>

</html>
