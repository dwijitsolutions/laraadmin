<?php

namespace Dwij\Laraadmin\Models;

use Illuminate\Database\Eloquent\Model;
use Schema;
use Log;
use DB;

use Dwij\Laraadmin\Models\Module;

class ModuleFields extends Model
{
    protected $table = 'module_fields';
    
    protected $fillable = [
        "colname", "label", "module", "field_type", "unique", "defaultvalue", "minlength", "maxlength", "required", "popup_vals"
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
            if($request->unique) {
                $field->unique = true;
            } else {
                $field->unique = false;
            }
            $field->defaultvalue = $request->defaultvalue;
            if($request->minlength == "") {
				$request->minlength = 0;
			}
			if($request->maxlength == "") {
				if(in_array($request->field_type, [1, 8, 16, 17, 19, 20, 22, 23 ])) {
					$request->maxlength = 256;
				} else if(in_array($request->field_type, [14])) {
					$request->maxlength = 20;
				} else if(in_array($request->field_type, [3, 6, 10, 13])) {
					$request->maxlength = 11;
				}
			}
            $field->minlength = $request->minlength;
            if($request->maxlength != null && $request->maxlength != "") {
				$field->maxlength = $request->maxlength;
			}
            if($request->required) {
                $field->required = true;
            } else {
                $field->required = false;
            }
            if($request->field_type == 7 || $request->field_type == 15 || $request->field_type == 18 || $request->field_type == 20) {
                if($request->popup_value_type == "table") {
                    $field->popup_vals = "@".$request->popup_vals_table;
                } else if($request->popup_value_type == "list") {
                    $request->popup_vals_list = json_encode($request->popup_vals_list);
                    $field->popup_vals = $request->popup_vals_list;
                }
            } else {
				$field->popup_vals = "";
			}
            $field->save();
            
            // Create Schema for Module Field
            if (!Schema::hasTable($module->name_db)) {
                Schema::create($module->name_db, function($table) {
                    $table->increments('id');
                    $table->softDeletes();
                    $table->timestamps();
                });
            }
            Schema::table($module->name_db, function($table) use ($field) {
                // $table->string($field->colname);
                // createUpdateFieldSchema()
				$field->module_obj = $module;
				Module::create_field_schema($table, $field, false);
            });
        }
        return $field->id;
    }

    public static function updateField($id, $request) {
        $module_id = $request->module_id;
        
        $field = ModuleFields::find($id);

        // Update the Schema
        // Change Column Name if Different
        $module = Module::find($module_id);
        if($field->colname != $request->colname) {
            Schema::table($module->name_db, function ($table) use ($field, $request) {
                $table->renameColumn($field->colname, $request->colname);
            });
        }
        
        // Update Context in ModuleFields
        $field->colname = $request->colname;
        $field->label = $request->label;
        $field->module = $request->module_id;
        $field->field_type = $request->field_type;
        if($request->unique) {
            $field->unique = true;
        } else {
            $field->unique = false;
        }
        $field->defaultvalue = $request->defaultvalue;
        if($request->minlength == "") {
			$request->minlength = 0;
		}
		if($request->maxlength == "" || $request->maxlength == 0) {
			if(in_array($request->field_type, [1, 8, 16, 17, 19, 20, 22, 23 ])) {
				$request->maxlength = 256;
			} else if(in_array($request->field_type, [14])) {
				$request->maxlength = 20;
			} else if(in_array($request->field_type, [3, 6, 10, 13])) {
				$request->maxlength = 11;
			}
		}
		$field->minlength = $request->minlength;
		if($request->maxlength != null && $request->maxlength != "") {
			$field->maxlength = $request->maxlength;
		}
        if($request->required) {
            $field->required = true;
        } else {
            $field->required = false;
        }
        if($request->field_type == 7 || $request->field_type == 15 || $request->field_type == 18 || $request->field_type == 20) {
            if($request->popup_value_type == "table") {
                $field->popup_vals = "@".$request->popup_vals_table;
            } else if($request->popup_value_type == "list") {
                $request->popup_vals_list = json_encode($request->popup_vals_list);
                $field->popup_vals = $request->popup_vals_list;
            }
        } else {
			$field->popup_vals = "";
		}
        $field->save();

		$field->module_obj = $module;

        Schema::table($module->name_db, function ($table) use ($field) {
            Module::create_field_schema($table, $field, true);
        });
    }

	public static function getModuleFields($moduleName) {
        $module = Module::where('name', $moduleName)->first();
        $fields = DB::table('module_fields')->where('module', $module->id)->get();
        $ftypes = ModuleFieldTypes::getFTypes();
		
		$fields_popup = array();
        $fields_popup['id'] = null;
        
		foreach($fields as $f) {
			$f->field_type_str = array_search($f->field_type, $ftypes);
            $fields_popup [ $f->colname ] = $f;
        }
		return $fields_popup;
    }

	public static function getFieldValue($field, $value) {
        $external_table_name = substr($field->popup_vals, 1);
		if(Schema::hasTable($external_table_name)) {
			$external_value = DB::table($external_table_name)->where('id', $value)->get();
			if(isset($external_value[0])) {
                $external_module = DB::table('modules')->where('name_db', $external_table_name)->first();
                if(isset($external_module->view_col)) {
                    $external_value_viewcol_name = $external_module->view_col;
				    return $external_value[0]->$external_value_viewcol_name;
                } else {
                    if(isset($external_value[0]->{"name"})) {
                        return $external_value[0]->name;
                    } else if(isset($external_value[0]->{"title"})) {
                        return $external_value[0]->title;
                    }
                }
			} else {
				return $value;
			}
		} else {
			return $value;
		}
    }
	
	public static function listingColumnAccessScan($module_name, $listing_cols) {
        $module = Module::get($module_name);
		$listing_cols_temp = array();
		foreach ($listing_cols as $col) {
			if($col == 'id') {
				$listing_cols_temp[] = $col;
			} else if(Module::hasFieldAccess($module->id, $module->fields[$col]['id'])) {
				$listing_cols_temp[] = $col;
			}
		}
		return $listing_cols_temp;
    }
}
