@extends('la.layouts.auth')

@section('htmlheader_title')
    Register
@endsection

@section('content')

    <body class="hold-transition register-page">
        <div class="bg-image">

        </div>
        <div class="register-box">
            <div class="register-logo">
                <a href="{{ url('/home') }}"><b>{{ LAConfig::getByKey('sitename_part1') }} </b>{{ LAConfig::getByKey('sitename_part2') }}</a>
            </div>

            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="register-box-body">
                <p class="login-box-msg">Register Super Admin</p>
                <form action="{{ url('/register') }}" method="post">
                    <input type="hidden" name="context_type" value="Employee">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="form-group has-feedback">
                        <input type="text" class="form-control" placeholder="Full name" name="name" value="{{ old('name') }}" />
                        <span class="glyphicon glyphicon-user form-control-feedback"></span>
                    </div>
                    <div class="form-group has-feedback">
                        <input type="email" class="form-control" placeholder="Email" name="email" value="{{ old('email') }}" />
                        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                    </div>
                    <div class="form-group has-feedback">
                        <input type="password" class="form-control" placeholder="Password" name="password" />
                        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                    </div>
                    <div class="form-group has-feedback">
                        <input type="password" class="form-control" placeholder="Retype password" name="password_confirmation" />
                        <span class="glyphicon glyphicon-log-in form-control-feedback"></span>
                    </div>
                    <div class="row">
                        <div class="col-xs-8">
                            <div class="checkbox icheck">
                                <label>
                                    <input type="checkbox"> I agree to the terms
                                </label>
                            </div>
                        </div><!-- /.col -->
                        <div class="col-xs-4">
                            <button type="submit" class="btn btn-primary btn-block btn-flat">Register</button>
                        </div><!-- /.col -->
                    </div>
                </form>

                <center><a href="{{ url('/login') }}" class="text-center">Login</a></center>
            </div><!-- /.register-box-body -->

            <div class="register-box-footer">
                <div class="row">
                    <div class="col-xs-2">
                        <img src="{{ asset('/la-assets/img/laraadmin-256.png') }}" width="30px" alt="">
                    </div>
                    <p class="col-xs-10">LaraAdmin is a Open source Laravel Admin Panel / CMS which can be used as Admin Backend, Data Management Tool or
                        CRM boilerplate for Laravel</p>
                </div>
            </div>
        </div><!-- /.register-box -->

        @include('la.layouts.partials.scripts_auth')

        <script>
            $(function() {
                $('input').iCheck({
                    checkboxClass: 'icheckbox_square-blue',
                    radioClass: 'iradio_square-blue',
                    increaseArea: '20%' // optional
                });
            });
        </script>
    </body>

@endsection
