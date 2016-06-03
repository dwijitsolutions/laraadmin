<?php

namespace Dwijitso\Sbscrud\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class Module extends Model
{
    protected $table = 'modules';
    
    protected $fillable = [
        "name", "name_db"
    ];
    
    protected $hidden = [
        
    ];
    
    public static function generate($module_name, $module_name_db, $fields) {
        
        $module = Module::where('name', $module_name)->first();
        if(!isset($module->id)) {
            $module = Module::create([
                'name' => $module_name,
                'name_db' => $module_name_db
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
                    
                    ModuleFields::create([
                        'module' => $module->id,
                        'colname' => $field->colname,
                        'label' => $field->label,
                        'field_type' => $ftypes[$field->field_type],
                        'readonly' => $field->readonly,
                        'defaultvalue' => $field->defaultvalue,
                        'minlength' => $field->minlength,
                        'maxlength' => $field->maxlength,
                        'required' => $field->required,
                        'popup_vals' => $field->popup_vals
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
                            $var = $table->integer($field->colname)->unsigned();
                            if(is_int($field->defaultvalue)) {
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
                        if($field->defaultvalue != "") {
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
                        if($field->maxlength == 0) {
                            $var = $table->string($field->colname);
                        } else {
                            $var = $table->string($field->colname, $field->maxlength);
                        }
                        if($field->defaultvalue != "") {
                            $var->default($field->defaultvalue);
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
                        if($field->maxlength == 0) {
                            $var = $table->text($field->colname);
                        } else {
                            $var = $table->text($field->colname);
                            if($field->defaultvalue != "") {
                                $var->default($field->defaultvalue);
                            }
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
        $module = Module::where('name', $module_name)->first()->toArray();
        $fields = ModuleFields::where('module', $module['id'])->get()->toArray();
        $fields2 = array();
        foreach ($fields as $field) {
            $fields2[$field['colname']] = $field;
        }
        $module['fields'] = $fields2;
        return (object)$module;
    }
}
