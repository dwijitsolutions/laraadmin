<?php

namespace Dwij\Laraadmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class Module extends Model
{
    protected $table = 'modules';
    
    protected $fillable = [
        "name", "name_db", "view_col"
    ];
    
    protected $hidden = [
        
    ];
    
    public static function generate($module_name, $module_name_db, $view_col, $fields) {
        
        $module = Module::where('name', $module_name)->first();
        if(!isset($module->id)) {
            $module = Module::create([
                'name' => $module_name,
                'name_db' => $module_name_db,
                'view_col' => $view_col
            ]);
        }
        $fields = Module::format_fields($fields);
        $ftypes = ModuleFieldTypes::getFTypes();
        //print_r($ftypes);
        //print_r($module);
        //print_r($fields);
        
        Schema::create($module_name_db, function (Blueprint $table) use ($fields, $module, $ftypes) {
            $table->increments('id');
            foreach ($fields as $field) {
                
                $mod = ModuleFields::where('module', $module->id)->where('colname', $field->colname)->first();
                if(!isset($mod->id)) {
                    if($field->field_type == "Multiselect" || $field->field_type == "Taginput") {
                        if(is_string($field->defaultvalue) || is_int($field->defaultvalue)) {
                            $dvalue = json_encode([$field->defaultvalue]);
                        } else {
                            $dvalue = json_encode($field->defaultvalue);
                        }
                    } else {
                        $dvalue = $field->defaultvalue;
                        if(is_string($field->defaultvalue) || is_int($field->defaultvalue)) {
                            $dvalue = $field->defaultvalue;
                        } else if(is_array($field->defaultvalue) && is_object($field->defaultvalue)) {
                            $dvalue = json_encode($field->defaultvalue);
                        }
                    }
                    
                    $pvalues = $field->popup_vals;
                    if(is_array($field->popup_vals) || is_object($field->popup_vals)) {
                        $pvalues = json_encode($field->popup_vals);
                    }
                    
                    ModuleFields::create([
                        'module' => $module->id,
                        'colname' => $field->colname,
                        'label' => $field->label,
                        'field_type' => $ftypes[$field->field_type],
                        'readonly' => $field->readonly,
                        'defaultvalue' => $dvalue,
                        'minlength' => $field->minlength,
                        'maxlength' => $field->maxlength,
                        'required' => $field->required,
                        'popup_vals' => $pvalues
                    ]);
                }
                
                switch ($field->field_type) {
                    case 'Address':
                        $var = null;
                        if($field->maxlength == 0) {
                            $var = $table->text($field->colname);
                        } else {
                            $var = $table->string($field->colname, $field->maxlength);
                        }
                        if($field->defaultvalue != "") {
                            $var->default($field->defaultvalue);
                        }
                        break;
                    case 'Checkbox':
                        $var = $table->boolean($field->colname);
                        $var->default($field->defaultvalue);
                        break;
                    case 'Currency':
                        $var = $table->double($field->colname, 15, 2);
                        if($field->defaultvalue != "") {
                            $var->default($field->defaultvalue);
                        }
                        break;
                    case 'Date':
                        $var = $table->date($field->colname);
                        if($field->defaultvalue != "") {
                            $var->default($field->defaultvalue);
                        }
                        break;
                    case 'Datetime':
                        $var = $table->timestamp($field->colname);
                        if($field->defaultvalue != "") {
                            $var->default($field->defaultvalue);
                        }
                        break;
                    case 'Decimal':
                        $var = null;
                        if($field->maxlength == 0) {
                            $var = $table->decimal($field->colname, 15, 5);
                        } else {
                            $var = $table->decimal($field->colname, 15, $field->maxlength);
                        }
                        if($field->defaultvalue != "") {
                            $var->default($field->defaultvalue);
                        }
                        break;
                    case 'Dropdown':
                        if($field->popup_vals == "") {
                            if(is_int($field->defaultvalue)) {
                                $var = $table->integer($field->colname)->unsigned();
                                $var->default($field->defaultvalue);
                                break;
                            } else if(is_string($field->defaultvalue)) {
                                $var = $table->string($field->colname);
                                $var->default($field->defaultvalue);
                                break;
                            }
                        }
                        $popup_vals = json_decode($field->popup_vals);
                        if(is_array($popup_vals)) {
                            $var = $table->string($field->colname);
                            if($field->defaultvalue != "") {
                                $var->default($field->defaultvalue);
                            }
                        } else if(is_object($popup_vals)) {
                            // ############### Remaining
                            $var = $table->integer($field->colname)->unsigned();
                            // if(is_int($field->defaultvalue)) {
                            //     $var->default($field->defaultvalue);
                            //     break;
                            // }
                        }
                        break;
                    case 'Email':
                        $var = null;
                        if($field->maxlength == 0) {
                            $var = $table->string($field->colname, 100);
                        } else {
                            $var = $table->string($field->colname, $field->maxlength);
                        }
                        if($field->defaultvalue != "") {
                            $var->default($field->defaultvalue);
                        }
                        break;
                    case 'Float':
                        $var = $table->float($field->colname);
                        if($field->defaultvalue != "") {
                            $var->default($field->defaultvalue);
                        }
                        break;
                    case 'HTML':
                        $var = $table->longText($field->colname);
                        break;
                    case 'Image':
                        $var = $table->string($field->colname);
                        if($field->defaultvalue != "") {
                            $var->default($field->defaultvalue);
                        }
                        break;
                    case 'Integer':
                        $var = null;
                        if($field->maxlength == 0) {
                            $var = $table->string($field->colname, 11);
                        } else {
                            $var = $table->string($field->colname, $field->maxlength);
                        }
                        if($field->defaultvalue != "") {
                            $var->default($field->defaultvalue);
                        }
                        break;
                    case 'Mobile':
                        $var = null;
                        if($field->maxlength == 0) {
                            $var = $table->string($field->colname);
                        } else {
                            $var = $table->string($field->colname, $field->maxlength);
                        }
                        if($field->defaultvalue != "") {
                            $var->default($field->defaultvalue);
                        }
                        break;
                    case 'Multiselect':
                        $var = $table->string($field->colname, 256);
                        if(is_string($field->defaultvalue)) {
                            $field->defaultvalue = json_encode([$field->defaultvalue]);
                            //echo "string: ".$field->defaultvalue;
                            $var->default($field->defaultvalue);
                        } else if(is_array($field->defaultvalue)) {
                            $field->defaultvalue = json_encode($field->defaultvalue);
                            //echo "array: ".$field->defaultvalue;
                            $var->default($field->defaultvalue);
                        } else if(is_int($field->defaultvalue)) {
                            $field->defaultvalue = json_encode([$field->defaultvalue]);
                            //echo "int: ".$field->defaultvalue;
                            $var->default($field->defaultvalue);
                        }
                        break;
                    case 'Name':
                        $var = null;
                        if($field->maxlength == 0) {
                            $var = $table->string($field->colname);
                        } else {
                            $var = $table->string($field->colname, $field->maxlength);
                        }
                        if($field->defaultvalue != "") {
                            $var->default($field->defaultvalue);
                        }
                        break;
                    case 'Password':
                        $var = null;
                        if($field->maxlength == 0) {
                            $var = $table->string($field->colname);
                        } else {
                            $var = $table->string($field->colname, $field->maxlength);
                        }
                        if($field->defaultvalue != "") {
                            $var->default($field->defaultvalue);
                        }
                        break;
                    case 'Radio':
                        $var = null;
                        if($field->popup_vals == "") {
                            if(is_int($field->defaultvalue)) {
                                $var = $table->integer($field->colname)->unsigned();
                                $var->default($field->defaultvalue);
                                break;
                            } else if(is_string($field->defaultvalue)) {
                                $var = $table->string($field->colname);
                                $var->default($field->defaultvalue);
                                break;
                            }
                        }
                        $popup_vals = json_decode($field->popup_vals);
                        if(is_array($popup_vals)) {
                            $var = $table->string($field->colname);
                            if($field->defaultvalue != "") {
                                $var->default($field->defaultvalue);
                            }
                        } else if(is_object($popup_vals)) {
                            // ############### Remaining
                            $var = $table->integer($field->colname)->unsigned();
                            // if(is_int($field->defaultvalue)) {
                            //     $var->default($field->defaultvalue);
                            //     break;
                            // }
                        }
                        break;
                    case 'String':
                        $var = null;
                        if($field->maxlength == 0) {
                            $var = $table->string($field->colname);
                        } else {
                            $var = $table->string($field->colname, $field->maxlength);
                        }
                        if($field->defaultvalue != "") {
                            $var->default($field->defaultvalue);
                        }
                        break;
                    case 'Taginput':
                        $var = null;
                        $var = $table->string($field->colname, 1000);
                        if(is_string($field->defaultvalue)) {
                            $field->defaultvalue = json_encode([$field->defaultvalue]);
                            //echo "string: ".$field->defaultvalue;
                            $var->default($field->defaultvalue);
                        } else if(is_array($field->defaultvalue)) {
                            $field->defaultvalue = json_encode($field->defaultvalue);
                            //echo "array: ".$field->defaultvalue;
                            $var->default($field->defaultvalue);
                        }
                        break;
                    case 'Textarea':
                        $var = null;
                        if($field->maxlength == 0) {
                            $var = $table->text($field->colname);
                        } else {
                            $var = $table->string($field->colname, $field->maxlength);
                            if($field->defaultvalue != "") {
                                $var->default($field->defaultvalue);
                            }
                        }
                        break;
                    case 'TextField':
                        $var = null;
                        if($field->maxlength == 0) {
                            $var = $table->string($field->colname);
                        } else {
                            $var = $table->string($field->colname, $field->maxlength);
                        }
                        if($field->defaultvalue != "") {
                            $var->default($field->defaultvalue);
                        }
                        break;
                    case 'URL':
                        $var = null;
                        if($field->maxlength == 0) {
                            $var = $table->string($field->colname);
                        } else {
                            $var = $table->string($field->colname, $field->maxlength);
                        }
                        if($field->defaultvalue != "") {
                            $var->default($field->defaultvalue);
                        }
                        break;
                }
            }
            
            // $table->string('name');
            // $table->string('designation', 100);
            // $table->string('mobile', 20);
            // $table->string('mobile2', 20);
            // $table->string('email', 100)->unique();
            // $table->string('gender')->default('male');
            // $table->integer('dept')->unsigned();
            // $table->integer('role')->unsigned();
            // $table->string('city', 50);
            // $table->string('address', 1000);
            // $table->string('about', 1000);
            // $table->date('date_birth');
            // $table->date('date_hire');
            // $table->date('date_left');
            // $table->double('salary_cur');
            $table->timestamps();
        });
    }
    
    public static function format_fields($fields) {
        $out = array();
        foreach ($fields as $field) {
            $obj = (Object)array();
            $obj->colname = $field[0];
            $obj->label = $field[1];
            $obj->field_type = $field[2];
            
            if(!isset($field[3])) {
                $obj->readonly = 0;
            } else {
                $obj->readonly = $field[3];
            }
            if(!isset($field[4])) {
                $obj->defaultvalue = '';
            } else {
                $obj->defaultvalue = $field[4];
            }
            if(!isset($field[5])) {
                $obj->minlength = 0;
            } else {
                $obj->minlength = $field[5];
            }
            if(!isset($field[6])) {
                $obj->maxlength = 0;
            } else {
                $obj->maxlength = $field[6];
            }
            if(!isset($field[7])) {
                $obj->required = 0;
            } else {
                $obj->required = $field[7];
            }
            if(!isset($field[8])) {
                $obj->popup_vals = "";
            } else {
                if(is_array($field[8])) {
                    $obj->popup_vals = json_encode($field[8]);
                } else {
                    $obj->popup_vals = $field[8];
                }
            }
            $out[] = $obj;
        }
        return $out;
    }
    
    public static function get($module_name) {
        $module = Module::where('name', $module_name)->first();
        if(isset($module)) {
            $module = $module->toArray();
            $fields = ModuleFields::where('module', $module['id'])->get()->toArray();
            $fields2 = array();
            foreach ($fields as $field) {
                $fields2[$field['colname']] = $field;
            }
            $module['fields'] = $fields2;
            return (object)$module;
        } else {
            return null;
        }
    }
    
    public static function validateRules($module_name, $request) {
        $module = Module::get($module_name);
        $rules = [];
        if(isset($module)) {
            $module_path = "App\\".$module_name;
            $ftypes = ModuleFieldTypes::getFTypes2();
            foreach ($module->fields as $field) {
                if(isset($request->$field['colname'])) {
                    $col = "";
                    if($field['required']) {
                        $col .= "required|";
                    }
                    if(in_array($ftypes[$field['field_type']], array("Currency", "Decimal"))) {
                        // No min + max length
                    } else {
                        if($field['minlength'] != 0) {
                            $col .= "min:".$field['minlength']."|";
                        }
                        if($field['maxlength'] != 0) {
                            $col .= "max:".$field['maxlength']."|";
                        }
                    }
                    // 'name' => 'required|unique|min:5|max:256',
                    // 'author' => 'required|max:50',
                    // 'price' => 'decimal',
                    // 'pages' => 'integer|max:5',
                    // 'genre' => 'max:500',
                    // 'description' => 'max:1000'
                    if($col != "") {
                        $rules[$field['colname']] = trim($col, "|");
                    }
                }
            }
            return $rules;
        } else {
            return $rules;
        }
    }
    
    public static function insert($module_name, $request) {
        $module = Module::get($module_name);
        if(isset($module)) {
            $model = "App\\".ucfirst(str_singular($module_name));
            $row = new $model;
            $row = Module::processDBRow($module, $request, $row);
            $row->save();
        } else {
            return null;
        }
    }
    
    public static function updateRow($module_name, $request, $id) {
        $module = Module::get($module_name);
        if(isset($module)) {
            $model = "App\\".ucfirst(str_singular($module_name));
            //$row = new $module_path;
            $row = $model::find($id);
            $row = Module::processDBRow($module, $request, $row);
            $row->save();
        } else {
            return null;
        }
    }
    
    public static function processDBRow($module, $request, $row) {
        $ftypes = ModuleFieldTypes::getFTypes2();
        
        foreach ($module->fields as $field) {
            if(isset($request->$field['colname'])) {
                
                switch ($ftypes[$field['field_type']]) {
                    case 'Checkbox':
                        #TODO: Bug fix
                        $row->$field['colname'] = $request->$field['colname'];
                        break;
                    case 'Datetime':
                        #TODO: Bug fix
                        $row->$field['colname'] = $request->$field['colname'];
                        break;
                    case 'Multiselect':
                        #TODO: Bug fix
                        $row->$field['colname'] = json_encode($request->$field['colname']);
                        break;
                    case 'Taginput':
                        #TODO: Bug fix
                        $row->$field['colname'] = json_encode($request->$field['colname']);
                        break;
                    default:
                        $row->$field['colname'] = $request->$field['colname'];
                        break;
                }
            }
        }
        return $row;
    }
}
