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
use Laraadmin\Entrust\EntrustPermission;

class Permission extends EntrustPermission
{
    use SoftDeletes;

    protected $table = 'permissions';

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
        return 'permissions_index';
    }

    /**
     * Get all events happened on Module.
     *
     * @return mixed
     */
    public function timeline()
    {
        $moduleConfigs = config('laraadmin.log.Permissions');
        $moduleConfigsArr = array_keys($moduleConfigs);

        return LALog::where('context_id', $this->id)->whereIn('type', $moduleConfigsArr)->orderBy('created_at', 'desc')->get();
    }
}
