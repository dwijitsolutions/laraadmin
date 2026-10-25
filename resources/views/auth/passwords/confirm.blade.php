@extends('la.layouts.auth')

@section('htmlheader_title')
    Confirm Password
@endsection

@section('content')

<body class="login-page">
    <div class="login-box">
        <div class="login-logo">
            <a href="{{ url('/home') }}"><b>{{ LAConfig::getByKey('sitename_part1') }} </b>{{ LAConfig::getByKey('sitename_part2') }}</a>
        </div><!-- /.login-logo -->

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

        <div class="login-box-body">
            <p class="login-box-msg">Please confirm your password before continuing.</p>
            <form action="{{ route('password.confirm') }}" method="post">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="form-group has-feedback">
                    <input type="password" class="form-control" placeholder="Password" name="password" required autocomplete="current-password"/>
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>

                <div class="row">
                    <div class="col-xs-2">
                    </div><!-- /.col -->
                    <div class="col-xs-8">
                        <button type="submit" class="btn btn-primary btn-block btn-flat">Confirm Password</button>
                    </div><!-- /.col -->
                    <div class="col-xs-2">
                    </div><!-- /.col -->
                </div>
            </form>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">I forgot my password</a><br>
            @endif

        </div><!-- /.login-box-body -->

    </div><!-- /.login-box -->

    @include('la.layouts.partials.scripts_auth')
</body>

@endsection
