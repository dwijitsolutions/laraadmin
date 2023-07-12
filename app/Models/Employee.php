<?php
/***
 * Model generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $table = 'employees';

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
        return 'employees_index';
    }

    /**
     * Get mapping array by key.
     *
     * @return array
     */
    public static function arr($key = 'id')
    {
        $results = self::all();
        $arr = [];
        foreach ($results as $result) {
            $arr[$result->$key] = $result;
        }

        return $arr;
    }

    /**
     * Get all events happened on Module.
     *
     * @return mixed
     */
    public function timeline()
    {
        $moduleConfigs = config('laraadmin.log.Employees');
        $moduleConfigsArr = array_keys($moduleConfigs);

        return LALog::where('context_id', $this->id)->whereIn('type', $moduleConfigsArr)->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get Profile Image URL.
     */
    public function profileImageUrl()
    {
        if ($this->profile_img != 0) {
            return Upload::find($this->profile_img)->url();
        } else {
            return asset('la-assets/img/avatar5.png');
        }

        return null;
    }
}
