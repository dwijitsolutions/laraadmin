<?php
/**
 * Code generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is Proprietary Software created by Dwij IT Solutions. Use of LaraAdmin requires Paid Licence issued by Dwij IT Solutions.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Laraadmin\Entrust\EntrustFacade as Entrust;
use App\Helpers\LAHelper;

use App\Models\LALog;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Role;

/**
 * Class Menu
 * @package App\Models
 *
 * Menu Model which looks after Menus in Sidebar and Navbar
 */
class LAMenu extends Model
{
    protected $table = 'la_menus';

    protected $guarded = [

    ];

    /**
     * @return mixed
     */
    public function roles()
    {
        return $this->belongsToMany('App\Models\Role', 'role_la_menu', 'menu_id', 'role_id')->withTimestamps();
    }

    /**
     * @return mixed
     */
    public function has_role_access($role_id)
    {
        if (!Entrust::hasRole('SUPER_ADMIN')) {
            $role_id = intval($role_id);
            $roles = $this->roles;
            foreach ($roles as $role) {
                if ($role_id == $role->id) {
                    return true;
                }
            }
            return false;
        } else {
            return true;
        }
    }

    /**
     * @return mixed
     */
    public function set_access_to($role_ids)
    {
        if (is_string($role_ids) && $role_ids == "all") {
            $roles = Role::all();
            $role_ids = array();
            foreach ($roles as $role) {
                $role_ids[] = "".$role->id;
            }
        } elseif (is_string($role_ids) && $role_ids == "remove_all") {
            $role_ids = array();
        }
        if (is_array($role_ids)) {
            $roles = Role::all();
            foreach ($roles as $role) {
                if (in_array("".$role->id, $role_ids)) {
                    if (!$this->has_role_access($role->id)) {
                        // Give Access
                        $this->roles()->attach($role->id);

                        // Add LALog
                        LALog::make("Roles.MENU_ROLE_ATTACHED", [
                            'title' => "'".$this->name."' - Menu '".$role->name."' - role Attached by - ".Auth::user()->name,
                            'module_id' => 'Roles',
                            'context_id' => $this->id,
                            'context2_id' => $role->id,
                            'content' => "{}",
                            'user_id' => Auth::user()->id,
                            'notify_to' => "[]"
                        ]);
                    }
                } elseif ($this->has_role_access($role->id)) {
                    // Remove Access
                    $this->roles()->detach($role->id);

                    // Add LALog
                    LALog::make("Roles.MENU_ROLE_DETACHED", [
                        'title' => "'".$this->name."' - Menu '".$role->name."' - role Detached by - ".Auth::user()->name,
                        'module_id' => 'Roles',
                        'context_id' => $this->id,
                        'context2_id' => $role->id,
                        'content' => "{}",
                        'user_id' => Auth::user()->id,
                        'notify_to' => "[]"
                    ]);
                }
            }
        }
    }

    /**
     * @return mixed
     */
    public function user_access($user_id)
    {
        if ($this->type == "custom") {
            $access = false;
            $user = User::find($user_id);
            foreach ($user->roles as $role) {
                if ($this->has_role_access($role->id)) {
                    $access = true;
                }
            }
            return $access;
        } else {
            return true;
        }
    }
}
