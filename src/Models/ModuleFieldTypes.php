<?php

namespace Dwijitso\Sbscrud\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleFieldTypes extends Model
{
    protected $table = 'module_field_types';
    
    protected $fillable = [
        "name"
    ];
    
    protected $hidden = [
        
    ];
    
    // ModuleFieldTypes::getFTypes()
    public static function getFTypes() {
        $fields = ModuleFieldTypes::all();
        $fields2 = array();
        foreach ($fields as $field) {
            $fields2[$field['name']] = $field['id'];
        }
        return $fields2;
    }
}
