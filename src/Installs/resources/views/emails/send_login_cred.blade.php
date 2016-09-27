Dear {{ $user->name }},<br><br>

You have been registered on {{ url('/') }}.<br><br>

Your login credentials for the same are as below:<br><br>

Username: {{ $user->email }}<br>
password: {{ $password }}<br><br>

You can login on <a href="{{ url('/login') }}">{{ str_replace("http://", "", url('/login')) }}</a>.<br><br>

Best Regards,