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

class LAModuleFieldType extends Model
{
    protected $table = 'la_module_field_types';
    
    protected $fillable = [
        "name"
    ];
    
    protected $hidden = [
    
    ];
    
    // LAModuleFieldType::getFTypes()
    public static function getFTypes()
    {
        $fields = LAModuleFieldType::orderBy('name', 'asc')->get();
        $fields2 = array();
        foreach($fields as $field) {
            $fields2[$field['name']] = $field['id'];
        }
        return $fields2;
    }
    
    // LAModuleFieldType::getFTypes2()
    public static function getFTypes2()
    {
        $fields = LAModuleFieldType::orderBy('name', 'asc')->get();
        $fields2 = array();
        foreach($fields as $field) {
            $fields2[$field['id']] = $field['name'];
        }
        return $fields2;
    }
}
