@extends('la.layouts.auth')

@section('htmlheader_title')
    Verify User
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

            @if (session('resent'))
                <div class="alert alert-success" role="alert">
                    {{ __('A fresh verification link has been sent to your email address.') }}
                </div>
            @endif

            <div class="register-box-body">
                <p class="login-box-msg">{{ __('Verify Your Email Address') }}</p>

                {{ __('Before proceeding, please check your email for a verification link.') }}
                {{ __('If you did not receive the email') }}, <a href="{{ route('verification.resend') }}">{{ __('click here to request another') }}</a>.

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

        <script></script>
    </body>

@endsection
