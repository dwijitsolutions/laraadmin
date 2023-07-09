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
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * LaraAdmin Module Field.
 *
 * Module Fields Model which works for create / update of fields via "Module Manager"
 * This uses "LAModule::create_field_schema" method to actually create database schema
 */
class LAModuleField extends Model
{
    protected $table = 'la_module_fields';

    protected $fillable = [
        'colname', 'label', 'module', 'field_type', 'unique', 'defaultvalue', 'minlength', 'maxlength', 'required', 'listing_col', 'popup_vals', 'comment'
    ];

    protected $hidden = [

    ];

    /**
     * Create Module Field by $request
     * Method used in "Module Manager" via LAModuleFieldController.
     *
     * @param $request \Illuminate\Http\Request Object
     * @return int Returns field id after creation
     */
    public static function createField($request)
    {
        $module = LAModule::find($request->module_id);
        $module_id = $request->module_id;

        $field = self::where('colname', $request->colname)->where('module', $module_id)->first();
        if (! isset($field->id)) {
            $field = new self();
            $field->colname = $request->colname;
            $field->label = $request->label;
            $field->module = $request->module_id;
            $field->field_type = $request->field_type;
            if ($request->unique) {
                $field->unique = true;
            } else {
                $field->unique = false;
            }

            // List Data Type Default value
            if ($request->field_type == '29') {
                if ($request->defaultvalue == '') {
                    $field->defaultvalue = '[]';
                } else {
                    if (is_array($request->defaultvalue)) {
                        $field->defaultvalue = json_encode($request->defaultvalue);
                    } elseif (is_string($request->defaultvalue) && strpos($request->defaultvalue, '[') !== false) {
                        $field->defaultvalue = $request->defaultvalue;
                    } elseif (is_string($request->defaultvalue)) {
                        $field->defaultvalue = '["'.$request->defaultvalue.'"]';
                    } else {
                        $field->defaultvalue = '[]';
                    }
                }
            } else {
                $field->defaultvalue = $request->defaultvalue;
            }

            if ($request->minlength == '') {
                $request->minlength = 0;
            }
            if ($request->maxlength == '' || $request->maxlength == '0' || $request->maxlength == 0) {
                if (in_array($request->field_type, [1, 8, 16, 17, 19, 20, 22, 23])) {
                    $request->maxlength = 256;
                } elseif (in_array($request->field_type, [14])) {
                    $request->maxlength = 20;
                } elseif (in_array($request->field_type, [3, 6, 10, 13])) {
                    $request->maxlength = 11;
                }
            }
            $field->minlength = $request->minlength;
            if (isset($request->maxlength) && $request->maxlength != '') {
                $field->maxlength = $request->maxlength;
            }
            if ($request->required) {
                $field->required = true;
            } else {
                $field->required = false;
            }
            if ($request->listing_col) {
                $field->listing_col = true;
            } else {
                $field->listing_col = false;
            }
            if ($request->field_type == 7 || $request->field_type == 15 || $request->field_type == 18 || $request->field_type == 20) {
                if ($request->popup_value_type == 'table') {
                    $field->popup_vals = '@'.$request->popup_vals_table;
                } elseif ($request->popup_value_type == 'list') {
                    $request->popup_vals_list = json_encode($request->popup_vals_list);
                    $field->popup_vals = $request->popup_vals_list;
                }
            } else {
                $field->popup_vals = '';
            }
            $field->comment = $request->comment;

            // Get number of Module fields
            $modulefields = self::where('module', $module_id)->get();

            // Create Schema for Module Field when table is not exist
            if (! Schema::hasTable($module->name_db)) {
                Schema::create($module->name_db, function ($table) {
                    $table->id();
                    $table->softDeletes();
                    $table->timestamps();
                });
            } elseif (Schema::hasTable($module->name_db) && count($modulefields) == 0) {
                // create SoftDeletes + Timestamps for module with existing table
                Schema::table($module->name_db, function ($table) {
                    $table->softDeletes();
                    $table->timestamps();
                });
            }

            // Create Schema for Module Field when table is exist
            if (! Schema::hasColumn($module->name_db, $field->colname)) {
                Schema::table($module->name_db, function ($table) use ($field, $module) {
                    // $table->string($field->colname);
                    // createUpdateFieldSchema()
                    $field->module_obj = $module;
                    LAModule::create_field_schema($table, $field, false);
                });
            }
        }
        unset($field->module_obj);

        // field_type conversion to integer
        if (is_string($field->field_type)) {
            $ftypes = LAModuleFieldType::getFTypes();
            $field->field_type = $ftypes[$field->field_type];
        }

        $field->save();

        // give full access of field to all role
        $now = date('Y-m-d H:i:s');
        $module = LAModule::find($field->module);
        $roles = Role::all();
        foreach ($roles as $role) {
            $module_perm = DB::table('role_la_module')->where('role_id', $role->id)->where('module_id', $module->id)->first();
            if (isset($module_perm->id)) {
                $access = 'invisible';
                if ($module_perm->acc_view == 1) {
                    $access = 'readonly';
                }
                if ($module_perm->acc_create == 1) {
                    $access = 'full';
                }
                LAModule::setDefaultFieldRoleAccess($field->id, $role->id, $access);
            }
        }

        return $field->id;
    }

    /**
     * Update Module Field Context / Metadata
     * Method used in "Module Manager" via LAModuleFieldController.
     *
     * @param $id Field ID
     * @param $request \Illuminate\Http\Request Object
     */
    public static function updateField($id, $request)
    {
        $module_id = $request->module_id;

        $field = self::find($id);

        // Update the Schema
        // Change Column Name if Different
        $module = LAModule::find($module_id);
        if ($field->colname != $request->colname) {
            Schema::table($module->name_db, function ($table) use ($field, $request) {
                $table->renameColumn($field->colname, $request->colname);
            });
        }

        $isFieldTypeChange = false;

        // Update Context in LAModuleField
        $field->colname = $request->colname;
        $field->label = $request->label;
        $field->module = $request->module_id;

        if ($field->field_type != $request->field_type) {
            $isFieldTypeChange = true;
        }

        $field->field_type = $request->field_type;

        if ($request->unique) {
            $field->unique = true;
        } else {
            $field->unique = false;
        }
        $field->defaultvalue = $request->defaultvalue;
        if ($request->minlength == '') {
            $request->minlength = 0;
        }
        if ($request->maxlength == '' || $request->maxlength == '0' || $request->maxlength == 0) {
            if (in_array($request->field_type, [1, 8, 16, 17, 19, 20, 22, 23])) {
                $request->maxlength = 256;
            } elseif (in_array($request->field_type, [14])) {
                $request->maxlength = 20;
            } elseif (in_array($request->field_type, [3, 6, 10, 13])) {
                $request->maxlength = 11;
            }
        }
        $field->minlength = $request->minlength;
        if (isset($request->maxlength) && $request->maxlength != '') {
            $field->maxlength = $request->maxlength;
        }
        if ($request->required) {
            $field->required = true;
        } else {
            $field->required = false;
        }
        if ($request->listing_col) {
            $field->listing_col = true;
        } else {
            $field->listing_col = false;
        }

        if ($request->field_type == 7 || $request->field_type == 15 || $request->field_type == 18 || $request->field_type == 20) {
            if ($request->popup_value_type == 'table') {
                $field->popup_vals = '@'.$request->popup_vals_table;
            } elseif ($request->popup_value_type == 'list') {
                $request->popup_vals_list = json_encode($request->popup_vals_list);
                $field->popup_vals = $request->popup_vals_list;
            }
        } else {
            $field->popup_vals = '';
        }
        $field->comment = $request->comment;

        $field->save();

        $field->module_obj = $module;

        Schema::table($module->name_db, function ($table) use ($field, $isFieldTypeChange) {
            LAModule::create_field_schema($table, $field, true, $isFieldTypeChange);
        });

        return true;
    }

    /**
     * Get Array of Fields for given Module.
     *
     * @param $moduleName Module Name
     * @return array Array of Field Objects
     */
    public static function getModuleFields($moduleName)
    {
        $module = LAModule::where('name', $moduleName)->first();
        $fields = DB::table('la_module_fields')->where('module', $module->id)->get();
        $ftypes = LAModuleFieldType::getFTypes();

        $fields_popup = [];
        $fields_popup['id'] = null;

        // Set field type (e.g. Dropdown/Taginput) in String Format to field Object
        foreach ($fields as $f) {
            $f->field_type_str = array_search($f->field_type, $ftypes);
            $fields_popup[$f->colname] = $f;
        }

        return $fields_popup;
    }

    /**
     * Get Field Value when its associated with another Module / Table via "@"
     * e.g. "@employees".
     *
     * @param $field Module Field Object
     * @param $value_id This is a ID for which we wanted the Value from another table
     * @return mixed Returns Value found in table or Value id itself
     */
    public static function getFieldValue($field, $value_id)
    {
        $external_table_name = substr($field->popup_vals, 1);
        if (Schema::hasTable($external_table_name)) {
            $external_value = DB::table($external_table_name)->where('id', $value_id)->get();
            if (isset($external_value[0])) {
                $external_module = DB::table('la_modules')->where('name_db', $external_table_name)->first();
                if (isset($external_module->view_col)) {
                    $external_value_viewcol_name = $external_module->view_col;

                    return $external_value[0]->$external_value_viewcol_name;
                } else {
                    if (isset($external_value[0]->{'name'})) {
                        return $external_value[0]->name;
                    } elseif (isset($external_value[0]->{'title'})) {
                        return $external_value[0]->title;
                    }
                }
            } else {
                return $value_id;
            }
        } else {
            return $value_id;
        }
    }

    /**
     * Get Field Link when its associated with another Module / Table via "@"
     * e.g. "@employees".
     *
     * @param $field Module Field Object
     * @param $value_id This is a ID for which we wanted the Value from another table
     * @return mixed Returns Value with Link found in table or Value id itself
     */
    public static function getFieldLink($field, $value_id)
    {
        $external_table_name = substr($field->popup_vals, 1);
        if (Schema::hasTable($external_table_name)) {
            $external_value = DB::table($external_table_name)->where('id', $value_id)->get();
            if (isset($external_value[0])) {
                $external_module = DB::table('la_modules')->where('name_db', $external_table_name)->first();
                if (isset($external_module->view_col)) {
                    $external_value_viewcol_name = $external_module->view_col;
                    // return $external_value[0]->$external_value_viewcol_name;
                    return '<a '.config('laraadmin.ajaxload').' href="'.url(config('laraadmin.adminRoute').'/'.$external_table_name.'/'.$external_value[0]->id).'">'.$external_value[0]->$external_value_viewcol_name.'</a>';
                } else {
                    if (isset($external_value[0]->{'name'})) {
                        return $external_value[0]->name;

                        return '<a '.config('laraadmin.ajaxload').' href="'.url(config('laraadmin.adminRoute').'/'.$external_table_name.'/'.$external_value[0]->id).'">'.$external_value[0]->name.'</a>';
                    } elseif (isset($external_value[0]->{'title'})) {
                        return $external_value[0]->title;

                        return '<a '.config('laraadmin.ajaxload').' href="'.url(config('laraadmin.adminRoute').'/'.$external_table_name.'/'.$external_value[0]->id).'">'.$external_value[0]->title.'</a>';
                    }
                }
            } else {
                return $value_id;
            }
        } else {
            return $value_id;
        }
    }

    /**
     * Exclude the Columns form given list ($listing_cols) if don't have field View Access
     * and return remaining Columns.
     *
     * @param $module_name Module Name
     * @param $listing_cols Array Listing Column Names
     * @return array Excluded array of Listing Column Names
     */
    public static function listingColumnAccessScan($module_name, $listing_cols)
    {
        $module = LAModule::get($module_name);
        $listing_cols_temp = [];
        foreach ($listing_cols as $col) {
            if ($col == 'id') {
                $listing_cols_temp[] = $col;
            } elseif (LAModule::hasFieldAccess($module->id, $module->fields[$col]['id'])) {
                $listing_cols_temp[] = $col;
            }
        }

        return $listing_cols_temp;
    }

    /**
     * Insert New Field.
     *
     * LAModuleField::insert($module_name, $field_arr)
     *
     * @param string $module_name Module Name
     * @param array $field_arr Field Array
     * @return int Returns field id after creation
     */
    public static function insert($module_name, $field_arr)
    {
        $module = LAModule::get($module_name);
        $request = (object) [];
        $request->module_id = $module->id;
        $request->colname = $field_arr['colname'];
        $request->label = $field_arr['label'];
        $request->unique = $field_arr['unique'];
        $request->required = $field_arr['required'];
        $request->listing_col = $field_arr['listing_col'];

        if (isset($field_arr['minlength'])) {
            $request->minlength = $field_arr['minlength'];
        } else {
            $request->minlength = '';
        }
        if (isset($field_arr['maxlength'])) {
            $request->maxlength = $field_arr['maxlength'];
        } else {
            $request->maxlength = '';
        }

        if (isset($field_arr['defaultvalue'])) {
            $request->defaultvalue = $field_arr['defaultvalue'];
        } else {
            $request->defaultvalue = '';
        }

        if (isset($field_arr['popup_vals'])) {
            $request->popup_vals = $field_arr['popup_vals'];
        }

        $field_type = LAModuleFieldType::where('name', $field_arr['field_type'])->first();
        $request->field_type = $field_type->id;

        if ($field_type->id == 7 || $field_type->id == 15 || $field_type->id == 18 || $field_type->id == 20) {
            if (is_string($request->popup_vals) && strpos($request->popup_vals, '@') !== false) {
                $request->popup_value_type = 'table';
                $request->popup_vals_table = str_replace('@', '', $request->popup_vals);
            } else {
                $request->popup_value_type = 'list';
                $request->popup_vals_list = $request->popup_vals;
            }
        } else {
            $request->popup_vals = '';
        }
        $request->comment = $field_arr['comment'];

        return self::createField($request);
    }

    /**
     * Rename a existing Field.
     *
     * LAModuleField::rename($module_name, $current_field_name, $new_field_name)
     *
     * @param string $module_name Module Name
     * @param array $current_field_name Current Field Name
     * @param array $new_field_name New Field Name
     * @return bool success Return true if success
     */
    public static function rename($module_name, $current_field_name, $new_field_name)
    {
        $module = LAModule::get($module_name);
        $field = self::where('module', $module->id)->where('colname', $current_field_name)->first();
        if (isset($field->id)) {
            // Rename in Metadata
            $field->colname = $new_field_name;
            $field->save();

            // Rename in Schema
            Schema::table($module->name_db, function (Blueprint $table) use ($module, $field, $current_field_name, $new_field_name) {
                $table->renameColumn($current_field_name, $new_field_name);

                // Change Foreign Keys if exists for Dropdown or similar Data Type
                if (is_string($field->popup_vals) && strpos($field->popup_vals, '@') !== false) {
                    $foreign_table_name = str_replace('@', '', $field->popup_vals);
                    // TODO: SQLite Foreign Keys - does not dropForeign
                    // https://laravel.com/docs/5.7/upgrade
                    $table->dropForeign($module->name_db.'_'.$current_field_name.'_foreign');
                    $table->foreign($field->colname)->references('id')->on($foreign_table_name);
                }
            });

            return true;
        }

        return false;
    }

    /**
     * Update existing Field.
     *
     * LAModuleField::rename($field_name, $field_arr)
     *
     * @param string $module_name Module Name
     * @param array $field_name Field Name
     * @param array $field_arr Updated Field Details
     * @return bool success Return true if success
     */
    public static function update2($module_name, $field_name, $field_arr)
    {
        $module = LAModule::get($module_name);
        $field_object = self::where('module', $module->id)->where('colname', $field_name)->first();

        $request = (object) [];
        $request->module_id = $module->id;
        $request->colname = $field_arr['colname'];
        $request->label = $field_arr['label'];
        $request->unique = $field_arr['unique'];
        $request->required = $field_arr['required'];
        $request->listing_col = $field_arr['listing_col'];

        if (isset($field_arr['minlength'])) {
            $request->minlength = $field_arr['minlength'];
        } else {
            $request->minlength = '';
        }
        if (isset($field_arr['maxlength'])) {
            $request->maxlength = $field_arr['maxlength'];
        } else {
            $request->maxlength = '';
        }

        if (isset($field_arr['defaultvalue'])) {
            $request->defaultvalue = $field_arr['defaultvalue'];
        } else {
            $request->defaultvalue = '';
        }

        if (isset($field_arr['popup_vals'])) {
            $request->popup_vals = $field_arr['popup_vals'];
        }

        $field_type = LAModuleFieldType::where('name', $field_arr['field_type'])->first();
        $request->field_type = $field_type->id;

        if ($field_type->id == 7 || $field_type->id == 15 || $field_type->id == 18 || $field_type->id == 20) {
            if (is_string($request->popup_vals) && strpos($request->popup_vals, '@') !== false) {
                $request->popup_value_type = 'table';
                $request->popup_vals_table = str_replace('@', '', $request->popup_vals);
            } else {
                $request->popup_value_type = 'list';
                $request->popup_vals_list = $request->popup_vals;
            }
        } else {
            $request->popup_vals = '';
        }
        $request->comment = $field_arr['comment'];

        // return $field_object;
        return self::updateField($field_object->id, $request);
    }

    /**
     * Delete Field.
     *
     * LAModuleField::delete($module_name, $field_name)
     *
     * @param string $module_name Module Name
     * @param array $field_name Field Array
     * @return int Returns field id after creation
     */
    public static function deleteField($module_name, $field_name)
    {
        // Get Context
        $module = LAModule::get($module_name);
        $field = self::where('module', $module->id)->where('colname', $field_name)->first();

        Schema::table($module->name_db, function ($table) use ($field, $module) {
            Schema::disableForeignKeyConstraints();

            if (strpos($field->popup_vals, '@') !== false) {
                // TODO: SQLite Foreign Keys - does not dropForeign
                // https://laravel.com/docs/5.7/upgrade
                $table->dropForeign([$field->colname]);
            }

            $table->dropColumn($field->colname);
            Schema::enableForeignKeyConstraints();
        });

        // Delete Context
        $field->delete();

        return true;
    }
}
