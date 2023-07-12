Dear {{ $user->name }},<br><br>

Your login credentials are changed:<br><br>

Username: {{ $user->email }}<br>
password: {{ $password }}<br><br>

You can login on <a href="{{ url('/login') }}">{{ str_replace("http://", "", url('/login')) }}</a>.<br><br>

Best Regards,