<?php

namespace Dwij\Laraadmin\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleFields extends Model
{
    protected $table = 'module_fields';
    
    protected $fillable = [
        "colname", "label", "module", "field_type", "readonly", "defaultvalue", "minlength", "maxlength", "required", "popup_vals"
    ];
    
    protected $hidden = [
        
    ];
}
