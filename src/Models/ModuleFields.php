<?php

namespace Dwijitso\Sbscrud\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleFields extends Model
{
    protected $table = 'module_fields';
    
    protected $fillable = [
        "colname", "label", "module", "field_type", "readonly", "defaultvalue", "minlength", "maxlength", "required"
    ];
    
    protected $hidden = [
        
    ];
}
