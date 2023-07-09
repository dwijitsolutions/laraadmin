<?php
/***
 * Code generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/***
 * LaraAdmin Config Class looks after LaraAdmin configurations
 * Check details on https://laraadmin.com/docs.
 */
class LAConfig extends Model
{
    protected $table = 'la_configs';

    protected $hidden = [

    ];

    protected $guarded = [];

    /**
     * Get configuration string value by using key such as 'sitename'.
     *
     * LAConfig::getByKey('sitename');
     *
     * @param $key key string of configuration
     * @return bool value of configuration
     */
    public static function getByKey($key)
    {
        $row = self::where('key', $key)->first();
        if (isset($row->value)) {
            return $row->value;
        } else {
            return false;
        }
    }

    /**
     * Get all configuration as object.
     *
     * LAConfig::getAll();
     *
     * @return object
     */
    public static function getAll()
    {
        $configs = [];
        $configs_db = self::all();
        foreach ($configs_db as $row) {
            $configs[$row->key] = $row->value;
        }

        return (object) $configs;
    }
}
