<?php

namespace Dwij\Laraadmin\Models;

use Illuminate\Database\Eloquent\Model;
use Schema;

use Dwij\Laraadmin\Models\Module;

class ModuleFields extends Model
{
    protected $table = 'module_fields';
    
    protected $fillable = [
        "colname", "label", "module", "field_type", "readonly", "defaultvalue", "minlength", "maxlength", "required", "popup_vals"
    ];
    
    protected $hidden = [
        
    ];
    
    public static function createField($request) {
        $module = Module::find($request->module_id);
        $module_id = $request->module_id;
        
        $field = ModuleFields::where('colname', $request->colname)->where('module', $module_id)->first();
        if(!isset($field->id)) {
            $field = new ModuleFields;
            $field->colname = $request->colname;
            $field->label = $request->label;
            $field->module = $request->module_id;
            $field->field_type = $request->field_type;
            if($request->readonly) {
                $field->readonly = true;
            } else {
                $field->readonly = false;
            }
            $field->defaultvalue = $request->defaultvalue;
            $field->minlength = $request->minlength;
            $field->maxlength = $request->maxlength;
            if($request->required) {
                $field->required = true;
            } else {
                $field->required = false;
            }
            $field->popup_vals = $request->popup_vals;
            $field->save();
            
            // Create Schema for Module Field
            if (!Schema::hasTable($module->name_db)) {
                Schema::create($module->name_db, function($table) {
                    $table->increments('id');
                    $table->timestamps();
                });
            }
            Schema::table($module->name_db, function($table) use ($field) {
                $table->string($field->colname);
            });
        }
        return $field->id;
    }
}
