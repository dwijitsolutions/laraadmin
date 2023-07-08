<?php
/**
 * Model generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is Proprietary Software created by Dwij IT Solutions. Use of LaraAdmin requires Paid Licence issued by Dwij IT Solutions.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Listeners;

use App\Models\User;
use App\Models\LALog;
use App\Models\LAModule;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    /**
     * Create the event handler.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  User $user
     * @param  $remember
     * @return void
     */
    public function handle(Login $event)
    {
        $eventJSON = json_decode(json_encode($event));

        if (isset($eventJSON->user)) {
            LALog::make("Users.USER_LOGIN", [
                'title' => "User ".$eventJSON->user->name." Login",
                'module_id' => 'Users',
                'context_id' => $eventJSON->user->id,
                'content' => "{}",
                'user_id' => $eventJSON->user->id,
                'notify_to' => "[]"
            ]);
        }
    }
}
