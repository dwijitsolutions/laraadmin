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

class LAModuleFieldType extends Model
{
    protected $table = 'la_module_field_types';

    protected $fillable = [
        'name'
    ];

    protected $hidden = [

    ];

    // LAModuleFieldType::getFTypes()
    public static function getFTypes()
    {
        $fields = self::orderBy('name', 'asc')->get();
        $fields2 = [];
        foreach ($fields as $field) {
            $fields2[$field['name']] = $field['id'];
        }

        return $fields2;
    }

    // LAModuleFieldType::getFTypes2()
    public static function getFTypes2()
    {
        $fields = self::orderBy('name', 'asc')->get();
        $fields2 = [];
        foreach ($fields as $field) {
            $fields2[$field['id']] = $field['name'];
        }

        return $fields2;
    }
}
