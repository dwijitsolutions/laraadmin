<?php
/***
 * Model generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Laraadmin\Entrust\EntrustRole;

class Role extends EntrustRole
{
    use SoftDeletes;

    protected $table = 'roles';

    protected $hidden = [

    ];

    protected $guarded = [];

    protected $dates = ['deleted_at'];

    /**
     * Get the index name for the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return 'roles_index';
    }

    /**
     * Get Roles Array with matching context type - Employee / Customer.
     *
     * @return array
     */
    public static function ctype($type, $return_type = 'Object')
    {
        $empRoles = self::where('context_type', $type)->get();
        if ($return_type == 'Object') {
            return $empRoles;
        } elseif ($return_type == 'Name') {
            $rolesArray = [];
            foreach ($empRoles as $role) {
                $rolesArray[] = $role->name;
            }

            return $rolesArray;
        } else {
            return [];
        }
    }

    /**
     * @return mixed
     */
    public function users()
    {
        return $this->belongsToMany('App\Models\User');
    }

    /**
     * Get all events happened on Module.
     *
     * @return mixed
     */
    public function timeline()
    {
        $moduleConfigs = config('laraadmin.log.Roles');
        $moduleConfigsArr = array_keys($moduleConfigs);

        return LALog::where('context_id', $this->id)->whereIn('type', $moduleConfigsArr)->orderBy('created_at', 'desc')->get();
    }
}
