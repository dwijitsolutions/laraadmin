<?php
/***
 * Model generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Listeners;

use App\Models\LALog;
use App\Models\User;
use Illuminate\Auth\Events\Logout;

class LogSuccessfulLogout
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
    public function handle(Logout $event)
    {
        $eventJSON = json_decode(json_encode($event));

        if (isset($eventJSON->user)) {
            LALog::make('Users.USER_LOGOUT', [
                'title' => 'User '.$eventJSON->user->name.' Logout',
                'module_id' => 'Users',
                'context_id' => $eventJSON->user->id,
                'content' => '{}',
                'user_id' => $eventJSON->user->id,
                'notify_to' => '[]'
            ]);
        }
    }
}
