<!DOCTYPE html>
<html>
    <head>
        <title>{{ $title }}</title>

        <link href="https://fonts.googleapis.com/css?family=Roboto:200,400" rel="stylesheet" type="text/css">
		<link href="{{ asset('la-assets/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css" />

        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                color: #B0BEC5;
                display: table;
                font-weight: 200;
                font-family: 'Lato';
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 60px;
                margin-bottom: 40px;
				color: #444;
            }
			a {
				font-weight:normal;
				color:#3061B6;
				text-decoration: none;
			}
			code {
				padding: 2px 4px;
				color: #c7254e;
				background-color: #f9f2f4;
				border-radius: 4px;
			}
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
				<i class="fa fa-bug" style="font-size:120px;color:#FF5959;margin-bottom:30px;"></i>
                <div class="title">{{ $title }}</div>
				<p>{!! $message !!}</p>
				@if(Auth::guest())
					<a href="{{ url('/') }}">Homepage</a> | 
					<a href="javascript:history.back()">Go Back</a>
				@else
					<a href="{{ url(config('laraadmin.adminRoute')) }}">Dashboard.</a> | 
					<a href="javascript:history.back()">Go Back</a>
				@endif
            </div>
        </div>
    </body>
</html>
