<?php
/***
 * Code generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Models;

use App\Helpers\CodeGenerator;
use App\Helpers\LAHelper;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/***
 * LaraAdmin Module
 *
 * Most important Model of LaraAdmin which looks after Module, ModuleField Generation.
 * It also handles Module migrations via "generate" method to create Module Schema in Database.
 */
class LAModule extends Model
{
    protected $table = 'la_modules';

    protected $fillable = [
        'name', 'name_db', 'label', 'view_col', 'model', 'controller', 'is_gen', 'fa_icon'
    ];

    protected $hidden = [

    ];

    public function lang_name()
    {
        return app('translator')->get('la_'.Str::singular($this->name_db).'.'.$this->name_db);
    }

    /**
     * Generates Module Base by taking Module Name and Module FontAwesome Icon
     * It firstly checks if Module is already generated or not then
     * it Creates Module into Database and return Module ID.
     *
     * @param $module_name Module name
     * @param $icon Module FontAwesome Icon e.g. "fa-cube"
     * @return mixed Returns Module ID
     */
    public static function generateBase($module_name, $icon)
    {
        $names = LAHelper::generateModuleNames($module_name, $icon);

        // Check is Generated
        $is_gen = false;
        if (file_exists(base_path('app/Http/Controllers/'.($names->controller).'.php'))) {
            if (file_exists(base_path('app/Models/'.($names->model).'.php'))) {
                $is_gen = true;
            }
        }
        $module = self::where('name', $names->module)->first();
        if (! isset($module->id)) {
            $module = self::create([
                'name' => $names->module,
                'label' => $names->label,
                'name_db' => $names->table,
                'view_col' => '',
                'model' => $names->model,
                'controller' => $names->controller,
                'fa_icon' => $names->fa_icon,
                'is_gen' => $is_gen,

            ]);
        }

        return $module->id;
    }

    /**
     * This function handles Module Migration via "LAModule::generate()" call from migrations file.
     * This creates all given Module fields into database.
     *
     * @param $module_name Module Name
     * @param $module_name_db Module Database name in lowercase and concatenated by underscore.
     * @param $view_col View Column of Module for Index Anchor purpose.
     * @param string $faIcon Module FontAwesome Icon "fa-cube"
     * @param $fields Array of Module fields
     * @throws Exception Throws exceptions if Invalid view_column_name provided.
     */
    public static function generate($module_name, $module_name_db, $view_col, $faIcon, $fields)
    {
        $names = LAHelper::generateModuleNames($module_name, $faIcon);
        $fields = self::format_fields($module_name, $fields);

        if (substr_count($view_col, ' ') || substr_count($view_col, '.')) {
            throw new Exception('Unable to generate migration for '.($names->module)." : Invalid view_column_name. 'This should be database friendly lowercase name.'", 1);
        } elseif (! self::validate_view_column($fields, $view_col) && $view_col != 'id') {
            throw new Exception('Unable to generate migration for '.($names->module).' : view_column_name not found in field list.', 1);
        } else {
            // Check is Generated
            $is_gen = false;
            if (file_exists(base_path('app/Http/Controllers/'.($names->controller).'.php'))) {
                if (file_exists(base_path('app/Models/'.($names->model).'.php'))) {
                    $is_gen = true;
                }
            }

            // Create Module if not exists
            $module = self::where('name', $names->module)->first();
            if (! isset($module->id)) {
                $module = self::create([
                    'name' => $names->module,
                    'label' => $names->label,
                    'name_db' => $names->table,
                    'view_col' => $view_col,
                    'model' => $names->model,
                    'controller' => $names->controller,
                    'is_gen' => $is_gen,
                    'fa_icon' => $faIcon
                ]);
            }

            $ftypes = LAModuleFieldType::getFTypes();
            // print_r($ftypes);
            // print_r($module);
            // print_r($fields);

            // Create Database Schema for table
            Schema::create($names->table, function (Blueprint $table) use ($fields, $module, $ftypes) {
                $table->id();
                foreach ($fields as $field) {
                    $mod = LAModuleField::where('module', $module->id)->where('colname', $field->colname)->first();
                    if (! isset($mod->id)) {
                        if ($field->field_type == 'Multiselect' || $field->field_type == 'Taginput') {
                            if (is_string($field->defaultvalue) && str_starts_with($field->defaultvalue, '[')) {
                                $field->defaultvalue = json_decode($field->defaultvalue);
                            }

                            if (is_string($field->defaultvalue) || is_int($field->defaultvalue)) {
                                $dvalue = json_encode([$field->defaultvalue]);
                            } else {
                                $dvalue = json_encode($field->defaultvalue);
                            }
                        } else {
                            $dvalue = $field->defaultvalue;
                            if (is_array($field->defaultvalue)) {
                                $dvalue = json_encode($field->defaultvalue);
                            } elseif (is_string($field->defaultvalue) || is_int($field->defaultvalue)) {
                                $dvalue = $field->defaultvalue;
                            } elseif (is_array($field->defaultvalue) && is_object($field->defaultvalue)) {
                                $dvalue = json_encode($field->defaultvalue);
                            }
                        }

                        $pvalues = $field->popup_vals;
                        if (is_array($field->popup_vals) || is_object($field->popup_vals)) {
                            $pvalues = json_encode($field->popup_vals);
                        }

                        // Create Module field Metadata / Context
                        $field_obj = LAModuleField::create([
                            'module' => $module->id,
                            'colname' => $field->colname,
                            'label' => $field->label,
                            'field_type' => $ftypes[$field->field_type],
                            'unique' => $field->unique,
                            'defaultvalue' => $dvalue,
                            'minlength' => $field->minlength,
                            'maxlength' => $field->maxlength,
                            'required' => $field->required,
                            'listing_col' => $field->listing_col,
                            'popup_vals' => $pvalues,
                            'comment' => $field->comment
                        ]);
                        $field->id = $field_obj->id;
                        $field->module_obj = $module;
                    }

                    // Create Module field schema in database
                    self::create_field_schema($table, $field);
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
                if ($module->name_db == 'users') {
                    $table->rememberToken();
                    $table->timestamp('email_verified_at')->nullable();
                }
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    /**
     * Validates if given view_column_name exists in fields array.
     *
     * @param $fields Array of fields from migration file
     * @param $view_col View Column Name
     * @return bool returns true if view_column_name found in fields otherwise false
     */
    public static function validate_view_column($fields, $view_col)
    {
        $found = false;
        foreach ($fields as $field) {
            if ($field->colname == $view_col) {
                $found = true;
                break;
            }
        }

        return $found;
    }

    /**
     * Method creates database table field via $table variable from Schema.
     * @param $table
     * @param $field
     * @param bool $update
     * @param bool $isFieldTypeChange
     */
    public static function create_field_schema($table, $field, $update = false, $isFieldTypeChange = false)
    {
        if (is_numeric($field->field_type)) {
            $ftypes = LAModuleFieldType::getFTypes();
            $field->field_type = array_search($field->field_type, $ftypes);
        }
        if (! is_string($field->defaultvalue)) {
            $defval = json_encode($field->defaultvalue);
        } else {
            $defval = $field->defaultvalue;
        }
        // Log::debug('LAModule:create_field_schema ('.$update.') - '.$field->colname." - ".$field->field_type
        // ." - ".$defval." - ".$field->maxlength);

        // Create Field in Database for respective Field Type
        switch($field->field_type) {
            case 'Address':
                $var = null;
                if ($field->maxlength == 0) {
                    if ($update) {
                        $var = $table->text($field->colname)->change();
                    } else {
                        $var = $table->text($field->colname);
                    }
                } else {
                    if ($update) {
                        $var = $table->string($field->colname, $field->maxlength)->nullable()->change();
                    } else {
                        $var = $table->string($field->colname, $field->maxlength)->nullable();
                    }
                }
                if ($field->defaultvalue != '') {
                    $var->default($field->defaultvalue);
                } elseif ($field->required) {
                    $var->default('');
                }
                break;
            case 'Checkbox':
                if ($update) {
                    $var = $table->boolean($field->colname)->change();
                } else {
                    $var = $table->boolean($field->colname);
                }
                if ($field->defaultvalue == 'true' || $field->defaultvalue == 'false' || $field->defaultvalue == true || $field->defaultvalue == false) {
                    if (is_string($field->defaultvalue)) {
                        if ($field->defaultvalue == 'true') {
                            $field->defaultvalue = true;
                        } else {
                            $field->defaultvalue = false;
                        }
                    }
                    $var->default($field->defaultvalue);
                } elseif ($field->required) {
                    $field->defaultvalue = false;
                }
                break;
            case 'Currency':
                if ($update) {
                    $var = $table->double($field->colname, 15, 2)->change();
                } else {
                    $var = $table->double($field->colname, 15, 2);
                }
                if ($field->defaultvalue != '') {
                    $var->default($field->defaultvalue);
                } elseif ($field->required) {
                    $var->default('0.0');
                } else {
                    $var->default('0.0');
                }
                break;
            case 'Date':
                if ($update) {
                    $var = $table->date($field->colname)->nullable()->change();
                } else {
                    $var = $table->date($field->colname)->nullable();
                }

                if ($field->defaultvalue == null || $field->defaultvalue == '' || $field->defaultvalue == 'NULL') {
                    $var->default(null);
                } elseif ($field->defaultvalue == 'now()') {
                    $var->default(null);
                } elseif ($field->required) {
                    $var->default('1970-01-01');
                } else {
                    $var->default($field->defaultvalue);
                }
                break;
            case 'Datetime':
                if ($update) {
                    // Timestamp Edit Not working - http://stackoverflow.com/questions/34774628/how-do-i-make-doctrine-support-timestamp-columns
                    // Error Unknown column type "timestamp" requested. Any Doctrine type that you use has to be registered with \Doctrine\DBAL\Types\Type::addType()
                    // $var = $table->timestamp($field->colname)->change();
                } else {
                    $var = $table->timestamp($field->colname)->nullable()->nullableTimestamps();
                }
                // $table->timestamp('created_at')->useCurrent();
                if ($field->defaultvalue == null || $field->defaultvalue == '' || $field->defaultvalue == 'NULL') {
                    $var->default(null);
                } elseif ($field->defaultvalue == 'now()') {
                    $var->default(DB::raw('CURRENT_TIMESTAMP'));
                } elseif ($field->required) {
                    $var->default('1970-01-01 01:01:01');
                } else {
                    $var->default($field->defaultvalue);
                }
                break;
            case 'Decimal':
                $var = null;
                if ($update) {
                    $var = $table->decimal($field->colname, 15, 2)->change();
                } else {
                    $var = $table->decimal($field->colname, 15, 2);
                }
                if ($field->defaultvalue == 0) {
                    $var->default('0.0');
                } elseif ($field->defaultvalue != '') {
                    $var->default($field->defaultvalue);
                } elseif ($field->required) {
                    $var->default('0.0');
                }
                break;
            case 'Dropdown':
                if ($field->popup_vals == '') {
                    if (is_int($field->defaultvalue)) {
                        if ($update) {
                            $var = $table->integer($field->colname)->unsigned()->nullable()->change();
                        } else {
                            $var = $table->integer($field->colname)->unsigned()->nullable();
                        }
                        $var->default($field->defaultvalue);
                        break;
                    } elseif (is_string($field->defaultvalue)) {
                        if ($update) {
                            $var = $table->string($field->colname)->nullable()->change();
                        } else {
                            $var = $table->string($field->colname)->nullable();
                        }
                        $var->default($field->defaultvalue);
                        break;
                    }
                }
                $popup_vals = json_decode($field->popup_vals);
                if (str_starts_with($field->popup_vals, '@')) {
                    $foreign_table_name = str_replace('@', '', $field->popup_vals);
                    if ($update) {
                        $var = $table->unsignedBigInteger($field->colname)->nullable()->unsigned()->change();
                        if ($field->defaultvalue == '' || $field->defaultvalue == '0') {
                            $var->default(null);
                        } else {
                            $var->default($field->defaultvalue);
                        }
                        // TODO: SQLite Foreign Keys - does not dropForeign
                        // https://laravel.com/docs/5.7/upgrade
                        $table->dropForeign($field->module_obj->name_db.'_'.$field->colname.'_foreign');
                        $table->foreign($field->colname)->references('id')->on($foreign_table_name);
                    } else {
                        $var = $table->unsignedBigInteger($field->colname)->nullable()->unsigned();
                        if ($field->defaultvalue == '' || $field->defaultvalue == '0') {
                            $var->default(null);
                        } else {
                            $var->default($field->defaultvalue);
                        }
                        $table->foreign($field->colname)->references('id')->on($foreign_table_name);
                    }
                } elseif (is_array($popup_vals)) {
                    if ($update) {
                        $var = $table->string($field->colname)->nullable()->change();
                    } else {
                        $var = $table->string($field->colname)->nullable();
                    }
                    if ($field->defaultvalue != '') {
                        $var->default($field->defaultvalue);
                    } elseif ($field->required) {
                        $var->default('');
                    }
                } elseif (is_object($popup_vals)) {
                    // ############### Remaining
                    if ($update) {
                        $var = $table->integer($field->colname)->nullable()->unsigned()->change();
                    } else {
                        $var = $table->integer($field->colname)->nullable()->unsigned();
                    }
                    // if(is_int($field->defaultvalue)) {
                    //     $var->default($field->defaultvalue);
                    //     break;
                    // }
                }
                break;
            case 'Email':
                $var = null;
                if ($field->maxlength == 0) {
                    if ($update) {
                        $var = $table->string($field->colname, 100)->nullable()->change();
                    } else {
                        $var = $table->string($field->colname, 100)->nullable();
                    }
                } else {
                    if ($update) {
                        $var = $table->string($field->colname, $field->maxlength)->nullable()->change();
                    } else {
                        $var = $table->string($field->colname, $field->maxlength)->nullable();
                    }
                }
                if ($field->defaultvalue != '') {
                    $var->default($field->defaultvalue);
                } elseif ($field->required) {
                    $var->default('');
                }
                break;
            case 'File':
                if ($update) {
                    $var = $table->unsignedBigInteger($field->colname)->unsigned()->nullable()->change();
                } else {
                    $var = $table->unsignedBigInteger($field->colname)->unsigned()->nullable();
                }
                $table->foreign($field->colname)->references('id')->on('uploads');

                if ($field->defaultvalue != '' && is_numeric($field->defaultvalue)) {
                    $var->default($field->defaultvalue);
                } elseif ($field->required) {
                    $var->default(null);
                } else {
                    $var->default(null);
                }
                break;
            case 'Files':
                if ($update) {
                    $var = $table->string($field->colname, 256)->change();
                } else {
                    $var = $table->string($field->colname, 256);
                }
                if (is_string($field->defaultvalue) && str_starts_with($field->defaultvalue, '[')) {
                    $var->default($field->defaultvalue);
                } elseif (is_array($field->defaultvalue)) {
                    $var->default(json_encode($field->defaultvalue));
                } else {
                    $var->default('[]');
                }
                break;
            case 'Float':
                if ($update) {
                    $var = $table->float($field->colname)->nullable()->change();
                } else {
                    $var = $table->float($field->colname)->nullable();
                }
                if ($field->defaultvalue != '') {
                    $var->default($field->defaultvalue);
                } elseif ($field->required) {
                    $var->default('0.0');
                }
                break;
            case 'HTML':
                if ($update) {
                    $var = $table->string($field->colname, 10000)->nullable()->change();
                } else {
                    $var = $table->string($field->colname, 10000)->nullable();
                }
                if (isset($field->defaultvalue)) {
                    $var->default($field->defaultvalue);
                } elseif ($field->required) {
                    $var->default('');
                }
                break;
            case 'Image':
                if ($update) {
                    $var = $table->unsignedBigInteger($field->colname)->unsigned()->nullable()->change();
                } else {
                    $var = $table->unsignedBigInteger($field->colname)->unsigned()->nullable();
                }
                $table->foreign($field->colname)->references('id')->on('uploads');

                if ($field->defaultvalue != '' && is_numeric($field->defaultvalue)) {
                    $var->default($field->defaultvalue);
                } elseif ($field->required) {
                    $var->default(null);
                } else {
                    $var->default(null);
                }
                break;
            case 'JSON':
                $var = null;
                // TODO: json data type does not support all databases. You cann use Text instead
                if ($update) {
                    $var = $table->json($field->colname)->change();
                } else {
                    $var = $table->json($field->colname);
                }
                if ($field->defaultvalue != '') {
                    $var->default($field->defaultvalue);
                } else {
                    $var->default('{}');
                }
                break;
            case 'Integer':
                $var = null;
                if ($update) {
                    $var = $table->integer($field->colname, false)->change();
                } else {
                    $var = $table->integer($field->colname, false);
                }
                if ($field->defaultvalue != '') {
                    $var->default($field->defaultvalue);
                } else {
                    $var->default('0');
                }
                break;
            case 'List':
                $var = null;
                if ($update) {
                    $var = $table->text($field->colname)->change();
                } else {
                    $var = $table->text($field->colname);
                }
                break;
            case 'Checklist':
                $var = null;
                if ($update) {
                    $var = $table->text($field->colname)->change();
                } else {
                    $var = $table->text($field->colname);
                }
                break;
            case 'Mobile':
                $var = null;
                if ($field->maxlength == 0) {
                    if ($update) {
                        $var = $table->string($field->colname)->nullable()->change();
                    } else {
                        $var = $table->string($field->colname)->nullable();
                    }
                } else {
                    if ($update) {
                        $var = $table->string($field->colname, $field->maxlength)->nullable()->change();
                    } else {
                        $var = $table->string($field->colname, $field->maxlength)->nullable();
                    }
                }
                if ($field->defaultvalue != '') {
                    $var->default($field->defaultvalue);
                } elseif ($field->required) {
                    $var->default('');
                }
                break;
            case 'Multiselect':
                if ($update) {
                    $var = $table->string($field->colname, 256)->change();
                } else {
                    $var = $table->string($field->colname, 256);
                }
                if (is_array($field->defaultvalue)) {
                    $field->defaultvalue = json_encode($field->defaultvalue);
                    $var->default($field->defaultvalue);
                } elseif (is_string($field->defaultvalue) && str_starts_with($field->defaultvalue, '[')) {
                    $var->default($field->defaultvalue);
                } elseif ($field->defaultvalue == '' || $field->defaultvalue == null) {
                    $var->default('[]');
                } elseif (is_string($field->defaultvalue)) {
                    $field->defaultvalue = json_encode([$field->defaultvalue]);
                    $var->default($field->defaultvalue);
                } elseif (is_int($field->defaultvalue)) {
                    $field->defaultvalue = json_encode([$field->defaultvalue]);
                    // echo "int: ".$field->defaultvalue;
                    $var->default($field->defaultvalue);
                } elseif ($field->required) {
                    $var->default('[]');
                }
                break;
            case 'Name':
                $var = null;
                if ($field->maxlength == 0) {
                    if ($update) {
                        $var = $table->string($field->colname)->change();
                    } else {
                        $var = $table->string($field->colname);
                    }
                } else {
                    if ($update) {
                        $var = $table->string($field->colname, $field->maxlength)->change();
                    } else {
                        $var = $table->string($field->colname, $field->maxlength);
                    }
                }
                if ($field->defaultvalue != '') {
                    $var->default($field->defaultvalue);
                } elseif ($field->required) {
                    $var->default('');
                }
                break;
            case 'Password':
                $var = null;
                if ($field->maxlength == 0) {
                    if ($update) {
                        $var = $table->string($field->colname)->nullable()->change();
                    } else {
                        $var = $table->string($field->colname)->nullable();
                    }
                } else {
                    if ($update) {
                        $var = $table->string($field->colname, $field->maxlength)->nullable()->change();
                    } else {
                        $var = $table->string($field->colname, $field->maxlength)->nullable();
                    }
                }
                if ($field->defaultvalue != '') {
                    $var->default($field->defaultvalue);
                } elseif ($field->required) {
                    $var->default('');
                }
                break;
            case 'Radio':
                $var = null;
                if ($field->popup_vals == '') {
                    if (is_int($field->defaultvalue)) {
                        if ($update) {
                            $var = $table->integer($field->colname)->unsigned()->change();
                        } else {
                            $var = $table->integer($field->colname)->unsigned();
                        }
                        $var->default($field->defaultvalue);
                        break;
                    } elseif (is_string($field->defaultvalue)) {
                        if ($update) {
                            $var = $table->string($field->colname)->nullable()->change();
                        } else {
                            $var = $table->string($field->colname)->nullable();
                        }
                        $var->default($field->defaultvalue);
                        break;
                    }
                }
                if (is_string($field->popup_vals) && str_starts_with($field->popup_vals, '@')) {
                    if ($update) {
                        $var = $table->integer($field->colname)->unsigned()->change();
                    } else {
                        $var = $table->integer($field->colname)->unsigned();
                    }
                    break;
                }
                $popup_vals = json_decode($field->popup_vals);
                if (is_array($popup_vals)) {
                    if ($update) {
                        $var = $table->string($field->colname)->nullable()->change();
                    } else {
                        $var = $table->string($field->colname)->nullable();
                    }
                    if ($field->defaultvalue != '') {
                        $var->default($field->defaultvalue);
                    } elseif ($field->required) {
                        $var->default('');
                    }
                } elseif (is_object($popup_vals)) {
                    // ############### Remaining
                    if ($update) {
                        $var = $table->integer($field->colname)->unsigned()->change();
                    } else {
                        $var = $table->integer($field->colname)->unsigned();
                    }
                    // if(is_int($field->defaultvalue)) {
                    //     $var->default($field->defaultvalue);
                    //     break;
                    // }
                }
                break;
            case 'String':
                $var = null;
                if ($field->maxlength == 0) {
                    if ($update) {
                        $var = $table->string($field->colname)->nullable()->change();
                    } else {
                        $var = $table->string($field->colname)->nullable();
                    }
                } else {
                    if ($update) {
                        $var = $table->string($field->colname, $field->maxlength)->nullable()->change();
                    } else {
                        $var = $table->string($field->colname, $field->maxlength)->nullable();
                    }
                }
                if (isset($field->defaultvalue)) {
                    $var->default($field->defaultvalue);
                } elseif ($field->required) {
                    $var->default('');
                } elseif ($field->required) {
                    $var->default('');
                }
                break;
            case 'Taginput':
                $var = null;
                if ($update) {
                    $var = $table->string($field->colname, 1000)->nullable()->change();
                } else {
                    $var = $table->string($field->colname, 1000)->nullable();
                }
                if (is_string($field->defaultvalue) && str_starts_with($field->defaultvalue, '[')) {
                    $field->defaultvalue = json_decode($field->defaultvalue);
                }

                if (is_string($field->defaultvalue)) {
                    $field->defaultvalue = json_encode([$field->defaultvalue]);
                    // echo "string: ".$field->defaultvalue;
                    $var->default($field->defaultvalue);
                } elseif (is_array($field->defaultvalue)) {
                    $field->defaultvalue = json_encode($field->defaultvalue);
                    // echo "array: ".$field->defaultvalue;
                    $var->default($field->defaultvalue);
                } elseif ($field->required) {
                    $var->default('');
                }
                break;
            case 'Textarea':
                $var = null;
                if ($field->maxlength == 0) {
                    if ($update) {
                        $var = $table->text($field->colname)->change();
                    } else {
                        $var = $table->text($field->colname);
                    }
                } else {
                    if ($update) {
                        $var = $table->string($field->colname, $field->maxlength)->nullable()->change();
                    } else {
                        $var = $table->string($field->colname, $field->maxlength)->nullable();
                    }
                    if ($field->defaultvalue != '') {
                        $var->default($field->defaultvalue);
                    } elseif ($field->required) {
                        $var->default('');
                    }
                }
                break;
            case 'TextField':
                $var = null;
                if ($field->maxlength == 0) {
                    if ($update) {
                        $var = $table->string($field->colname)->nullable()->change();
                    } else {
                        $var = $table->string($field->colname)->nullable();
                    }
                } else {
                    if ($update) {
                        $var = $table->string($field->colname, $field->maxlength)->nullable()->change();
                    } else {
                        $var = $table->string($field->colname, $field->maxlength)->nullable();
                    }
                }
                if ($field->defaultvalue != '') {
                    $var->default($field->defaultvalue);
                } elseif ($field->required) {
                    $var->default('');
                }
                break;
            case 'URL':
                $var = null;
                if ($field->maxlength == 0) {
                    if ($update) {
                        $var = $table->string($field->colname)->nullable()->change();
                    } else {
                        $var = $table->string($field->colname)->nullable();
                    }
                } else {
                    if ($update) {
                        $var = $table->string($field->colname, $field->maxlength)->nullable()->change();
                    } else {
                        $var = $table->string($field->colname, $field->maxlength)->nullable();
                    }
                }
                if ($field->defaultvalue != '') {
                    $var->default($field->defaultvalue);
                } elseif ($field->required) {
                    $var->default('');
                }
                break;
            case 'Location':
                $var = null;
                if ($update) {
                    $var = $table->string($field->colname, 30)->nullable()->change();
                } else {
                    $var = $table->string($field->colname, 30)->nullable();
                }
                if ($field->defaultvalue != '' && str_contains($field->defaultvalue, ',')) {
                    $var->default($field->defaultvalue);
                } elseif ($field->required) {
                    $var->default('0.0,0.0');
                }
                break;
            case 'Color':
                $var = null;
                if ($update) {
                    $var = $table->string($field->colname, 25)->nullable()->change();
                } else {
                    $var = $table->string($field->colname, 25)->nullable();
                }
                if ($field->defaultvalue != '') {
                    $var->default($field->defaultvalue);
                } elseif ($field->required) {
                    $var->default('#000');
                } else {
                    $var->default('#000');
                }
                break;
            case 'Time':
                $var = null;
                if ($update) {
                    $var = $table->string($field->colname, 4)->nullable()->change();
                } else {
                    $var = $table->string($field->colname, 4)->nullable();
                }
                if ($field->defaultvalue != '') {
                    $var->default($field->defaultvalue);
                } elseif ($field->required) {
                    $var->default('0000');
                } else {
                    $var->default('0000');
                }
                break;
            case 'Duration':
                $var = null;
                if ($update) {
                    $var = $table->integer($field->colname, false)->unsigned()->change();
                } else {
                    $var = $table->integer($field->colname, false)->unsigned();
                }
                if ($field->defaultvalue != '') {
                    $var->default($field->defaultvalue);
                } else {
                    $var->default(0);
                }
                break;
        }

        // set column unique
        if ($update) {
            if ($isFieldTypeChange) {
                if ($field->unique && isset($var) && $field->maxlength < 256) {
                    $table->unique($field->colname);
                }
            }
        } else {
            if ($field->unique && isset($var) && $field->maxlength < 256) {
                $table->unique($field->colname);
            }
        }
        if (isset($var) && $field->comment != '') {
            $var->comment($field->comment);
        }
    }

    /**
     * This method process and alters user created migration fields array to fit into standard field Context / Metedata.
     *
     * Note: field array type change
     * Earlier we were taking sequential array for fields, but from version 1.1 we are using different format
     * with associative array. It also supports old sequential array. This step is taken to accommodate "listing_col"
     * which allows field to be listed in index/listing table. This step will also allow us to take more Metadata about
     * field.
     *
     * @param $module_name Module Name
     * @param $fields Fields Array
     * @return array Returns Array of Field Objects
     * @throws Exception Throws exception if field missing any details like colname, label, field_type
     */
    public static function format_fields($module_name, $fields)
    {
        $out = [];
        foreach ($fields as $field) {
            // Check if field format is New
            if (LAHelper::is_assoc_array($field)) {
                $obj = (object) $field;

                if (! isset($obj->colname)) {
                    throw new Exception('Migration '.$module_name.' -  Field does not have colname', 1);
                } elseif (! isset($obj->label)) {
                    throw new Exception('Migration '.$module_name.' -  Field does not have label', 1);
                } elseif (! isset($obj->field_type)) {
                    throw new Exception('Migration '.$module_name.' -  Field does not have field_type', 1);
                }
                if (! isset($obj->unique)) {
                    $obj->unique = 0;
                }
                if (! isset($obj->defaultvalue)) {
                    $obj->defaultvalue = '';
                }
                if (! isset($obj->minlength)) {
                    $obj->minlength = 0;
                }
                if (! isset($obj->maxlength)) {
                    $obj->maxlength = 0;
                } else {
                    // Because maxlength above 256 will not be supported by Unique
                    if ($obj->unique) {
                        $obj->maxlength = 250;
                    } else {
                        $obj->maxlength = $obj->maxlength;
                    }
                }
                if (! isset($obj->required)) {
                    $obj->required = 0;
                }
                if (! isset($obj->listing_col)) {
                    $obj->listing_col = 1;
                } else {
                    if ($obj->listing_col == true) {
                        $obj->listing_col = 1;
                    } else {
                        $obj->listing_col = 0;
                    }
                }

                if (! isset($obj->popup_vals)) {
                    $obj->popup_vals = '';
                } else {
                    if (is_array($obj->popup_vals)) {
                        $obj->popup_vals = json_encode($obj->popup_vals);
                    } else {
                        $obj->popup_vals = $obj->popup_vals;
                    }
                }
                if (! isset($obj->comment)) {
                    $obj->comment = '';
                }
                // var_dump($obj);
                $out[] = $obj;
            } else {
                // Handle Old field format - Sequential Array
                $obj = (object) [];
                $obj->colname = $field[0];
                $obj->label = $field[1];
                $obj->field_type = $field[2];

                if (! isset($field[3])) {
                    $obj->unique = 0;
                } else {
                    $obj->unique = $field[3];
                }
                if (! isset($field[4])) {
                    $obj->defaultvalue = '';
                } else {
                    $obj->defaultvalue = $field[4];
                }
                if (! isset($field[5])) {
                    $obj->minlength = 0;
                } else {
                    $obj->minlength = $field[5];
                }
                if (! isset($field[6])) {
                    $obj->maxlength = 0;
                } else {
                    // Because maxlength above 256 will not be supported by Unique
                    if ($obj->unique) {
                        $obj->maxlength = 250;
                    } else {
                        $obj->maxlength = $field[6];
                    }
                }
                if (! isset($field[7])) {
                    $obj->required = 0;
                } else {
                    $obj->required = $field[7];
                }
                $obj->listing_col = 1;

                if (! isset($field[8])) {
                    $obj->popup_vals = '';
                } else {
                    if (is_array($field[8])) {
                        $obj->popup_vals = json_encode($field[8]);
                    } else {
                        $obj->popup_vals = $field[8];
                    }
                }
                $out[] = $obj;
            }
        }

        return $out;
    }

    /**
     * Get Module Object by passing Module Name / id
     * It also includes array of Module fields.
     *
     * $module = LAModule::get($module_name);
     *
     * @param $module_name Module Name / Id
     * @return null|object Returns Module Object if found or NULL
     */
    public static function get($module_name)
    {
        $module = null;
        if (is_int($module_name)) {
            $module = self::find($module_name);
        } else {
            $module = self::where('name', $module_name)->orWhere('name_db', $module_name)->first();
        }

        // If Module is found in database also attach its field array to it.
        if (isset($module)) {
            $moduleArr = $module->toArray();
            $fields = LAModuleField::where('module', $moduleArr['id'])->orderBy('sort', 'asc')->get()->toArray();
            $fields2 = [];
            foreach ($fields as $field) {
                $fields2[$field['colname']] = $field;
            }
            $module->fields = $fields2;

            return $module;
        } else {
            return null;
        }
    }

    /**
     * Get Module by table name.
     *
     * $module = LAModule::getByTable($table_name);
     *
     * @param $table_name table name in lowercase
     * @return null|object Returns Module Object if found or NULL
     */
    public static function getByTable($table_name)
    {
        $module = self::where('name_db', $table_name)->first();
        if (isset($module)) {
            $module = $module->toArray();

            return self::get($module['name']);
        } else {
            return null;
        }
    }

    /**
     * Get Array of Values for Dropdown, Multiselect, Taginput, Radio from Module.
     *
     * $array = LAModule::getDDArray($module_name);
     *
     * @param $module_name Module Name
     * @return array Returns Array of View Column Values for Given Module
     */
    public static function getDDArray($module_name)
    {
        $module = self::where('name', $module_name)->first();
        if (isset($module)) {
            $model_name = $module->model;
            $model = 'App\\Models\\'.$module->model;

            $result = $model::all();
            $out = [];
            foreach ($result as $row) {
                $view_col = $module->view_col;
                $out[$row->id] = $row->{$view_col};
            }

            return $out;
        } else {
            return [];
        }
    }

    /**
     * Create Validations rules array for Laravel Validations using Module Field Context / Metadata
     * Used in LaraAdmin generated Controllers for store and update.
     * This generates array of validation rules for whole Module.
     *
     *
     * @param $module_name Module Name
     * @param $request \Illuminate\Http\Request Object
     * @param bool $isEdit Is this a Update or Store Request
     * @return array Returns Array to validate given Request
     */
    public static function validateRules($module_name, $request, $isEdit = false)
    {
        $module = self::get($module_name);
        $rules = [];
        if (isset($module)) {
            $ftypes = LAModuleFieldType::getFTypes2();
            foreach ($module->fields as $field) {
                if (isset($request->{$field['colname']})) {
                    $col = '';
                    if ($field['required']) {
                        $col .= 'required|';
                    }
                    if (in_array($ftypes[$field['field_type']], ['Currency', 'Decimal'])) {
                        // No min + max length
                    } elseif ($ftypes[$field['field_type']] == 'List') {
                        if ($field['minlength'] != 0) {
                            $col .= 'mincount:'.$field['minlength'].'|';
                        }
                        if ($field['maxlength'] != 0) {
                            $col .= 'maxcount:'.$field['maxlength'].'|';
                        }
                    } elseif ($ftypes[$field['field_type']] == 'Taginput') {
                        if ($field['minlength'] != 0) {
                            $col .= 'mincount:'.$field['minlength'].'|';
                        }
                        if ($field['maxlength'] != 0) {
                            $col .= 'maxcount:'.$field['maxlength'].'|';
                        }
                    } elseif ($ftypes[$field['field_type']] == 'Checklist') {
                        if ($field['minlength'] != 0) {
                            $col .= 'mincount:'.$field['minlength'].'|';
                        }
                        if ($field['maxlength'] != 0) {
                            $col .= 'maxcount:'.$field['maxlength'].'|';
                        }
                    } else {
                        if ($ftypes[$field['field_type']] == 'Integer') {
                            $col .= 'numeric|'; // TODO check
                        }
                        if ($field['minlength'] != 0) {
                            $col .= 'min:'.$field['minlength'].'|';
                        }
                        if ($field['maxlength'] != 0) {
                            $col .= 'max:'.$field['maxlength'].'|';
                        }
                    }
                    if ($field['unique'] && ! $isEdit) {
                        $col .= 'unique:'.$module->name_db.',deleted_at,NULL';
                    }
                    // 'name' => 'required|unique|min:5|max:256',
                    // 'author' => 'required|max:50',
                    // 'price' => 'decimal',
                    // 'pages' => 'integer|max:5',
                    // 'genre' => 'max:500',
                    // 'description' => 'max:1000'
                    if ($col != '') {
                        $rules[$field['colname']] = trim($col, '|');
                    }
                }
            }

            return $rules;
        } else {
            return $rules;
        }
    }

    /**
     * This method saves data from Request to Database.
     *
     * @param $module_name Module Name
     * @param $request \Illuminate\Http\Request Object
     * @return null/int Returns inserted row id or NULL
     */
    public static function insert($module_name, $request)
    {
        $module = self::get($module_name);
        if (isset($module)) {
            $model_name = $module->model;
            $model = 'App\\Models\\'.$module->model;

            // Delete if unique rows available which are deleted
            $old_row = null;
            $uniqueFields = LAModuleField::where('module', $module->id)->where('unique', '1')->get()->toArray();
            foreach ($uniqueFields as $field) {
                Log::debug('insert: '.$module->name_db.' - '.$field['colname'].' - '.$request->{$field['colname']});
                $old_row = DB::table($module->name_db)->whereNotNull('deleted_at')->where($field['colname'], $request->{$field['colname']})->first();
                if (isset($old_row->id)) {
                    Log::debug('deleting: '.$module->name_db.' - '.$field['colname'].' - '.$request->{$field['colname']});
                    DB::table($module->name_db)->whereNotNull('deleted_at')->where($field['colname'], $request->{$field['colname']})->delete();
                }
            }

            $row = new $model();
            if (isset($old_row->id)) {
                // To keep old & new row id remain same
                $row->id = $old_row->id;
            }
            $row = self::processDBRow($module, $request, $row);
            $row->save();

            return $row->id;
        } else {
            return null;
        }
    }

    /**
     * This method updates data from Request to Database for given Module and Row Id.
     *
     * @param $module_name Module Name
     * @param $request \Illuminate\Http\Request Object
     * @param $id int
     * @return null/int Returns updated row id or NULL
     */
    public static function updateRow($module_name, $request, $id)
    {
        $module = self::get($module_name);
        if (isset($module)) {
            $model_name = $module->model;
            $model = 'App\\Models\\'.$module->model;

            // $row = new $module_path;
            $row = $model::find($id);
            $row = self::processDBRow($module, $request, $row);
            $row->save();

            return $row->id;
        } else {
            return null;
        }
    }

    /**
     * Process Row Data According to its Field Type & Context / Metadata
     * Helps to save and update database records.
     *
     * @param $module Module Name
     * @param $request \Illuminate\Http\Request Object
     * @param $row Database table row to be updated/stored
     * @return object Returns Updated row
     */
    public static function processDBRow($module, $request, $row)
    {
        $ftypes = LAModuleFieldType::getFTypes2();

        foreach ($module->fields as $field) {
            if (isset($request->{$field['colname']}) || isset($request->{$field['colname'].'_hidden'}) || $ftypes[$field['field_type']] == 'Textarea') {
                switch($ftypes[$field['field_type']]) {
                    case 'Integer':
                        if (isset($request->{$field['colname']}) && $request->{$field['colname']} != '') {
                            $row->{$field['colname']} = $request->{$field['colname']};
                        } else {
                            $row->{$field['colname']} = '0';
                        }
                        break;
                    case 'Currency':
                        if (isset($request->{$field['colname']}) && $request->{$field['colname']} != '') {
                            $row->{$field['colname']} = $request->{$field['colname']};
                        } else {
                            $row->{$field['colname']} = '0.00';
                        }
                        break;
                    case 'Checkbox':
                        if (isset($request->{$field['colname']})) {
                            $row->{$field['colname']} = true;
                        } elseif (isset($request->{$field['colname'].'_hidden'})) {
                            $row->{$field['colname']} = false;
                        }
                        break;
                    case 'Date':
                        $null_date = $request->{'null_date_'.$field['colname']};
                        if (isset($null_date) && $null_date == 'true') {
                            $request->{$field['colname']} = null;
                        } elseif ($request->{$field['colname']} != '') {
                            $date = $request->{$field['colname']};
                            $d2 = date_parse_from_format('d/m/Y', $date);
                            $request->{$field['colname']} = date('Y-m-d', strtotime($d2['year'].'-'.$d2['month'].'-'.$d2['day']));
                        } else {
                            $request->{$field['colname']} = date('Y-m-d');
                        }
                        $row->{$field['colname']} = $request->{$field['colname']};
                        break;
                    case 'Datetime':
                        $null_date = $request->{'null_date_'.$field['colname']};
                        if (isset($null_date) && $null_date == 'true') {
                            $request->{$field['colname']} = null;
                        } elseif ($request->{$field['colname']} != '') {
                            $date = $request->{$field['colname']};
                            $d2 = date_parse_from_format('d/m/Y h:i A', $date);
                            $request->{$field['colname']} = date('Y-m-d H:i:s', strtotime($d2['year'].'-'.$d2['month'].'-'.$d2['day'].' '.substr($date, 11)));
                        } else {
                            $request->{$field['colname']} = date('Y-m-d H:i:s');
                        }
                        $row->{$field['colname']} = $request->{$field['colname']};
                        break;
                    case 'Dropdown':
                        if ($request->{$field['colname']} == '0') {
                            if (str_starts_with($field['popup_vals'], '@')) {
                                $request->{$field['colname']} = DB::raw('NULL');
                            } elseif (str_starts_with($field['popup_vals'], '[')) {
                                $request->{$field['colname']} = '';
                            }
                        }
                        $row->{$field['colname']} = $request->{$field['colname']};
                        break;
                    case 'Image':
                        $image = $request->{$field['colname']};
                        if ($image == 0 || $image == '0') {
                            $row->{$field['colname']} = null;
                        } else {
                            $row->{$field['colname']} = $image;
                        }
                        break;
                    case 'Multiselect':
                        // TODO: Bug fix
                        $row->{$field['colname']} = json_encode($request->{$field['colname']});
                        break;
                    case 'Password':
                        $row->{$field['colname']} = bcrypt($request->{$field['colname']});
                        break;
                    case 'Taginput':
                        // TODO: Bug fix
                        $row->{$field['colname']} = json_encode($request->{$field['colname']});
                        break;
                    case 'Textarea':
                        if (! isset($request->{$field['colname']})) {
                            $row->{$field['colname']} = '';
                        } else {
                            $row->{$field['colname']} = $request->{$field['colname']};
                        }
                        break;
                    case 'File':
                        $file = $request->{$field['colname']};
                        if ($file == 0 || $file == '0') {
                            $row->{$field['colname']} = null;
                        } else {
                            $row->{$field['colname']} = $request->{$field['colname']};
                        }
                        break;
                    case 'Files':
                        $files = json_decode($request->{$field['colname']});
                        $files2 = [];
                        foreach ($files as $file) {
                            $files2[] = ''.$file;
                        }
                        $row->{$field['colname']} = json_encode($files2);
                        break;
                    case 'Time':
                        $time = $request->{$field['colname']};
                        if (strlen($time) >= 7) {
                            $arr = explode(' ', $time);
                            $arr2 = explode(':', $arr[0]);
                            $hour = intval($arr2[0]);
                            $minute = intval($arr2[1]);
                            $ampm = trim($arr[1]);
                            if ($ampm == 'PM' && $hour < 12) {
                                $hour = $hour + 12;
                            } elseif ($ampm == 'AM' && $hour == 12) {
                                $hour = 0;
                            }

                            // Prepend 0
                            if ($hour < 10) {
                                $hour = '0'.$hour;
                            }
                            if ($minute < 10) {
                                $minute = '0'.$minute;
                            }
                            $time24 = $hour.$minute;
                            $row->{$field['colname']} = $time24;
                        }
                        break;
                    default:
                        $row->{$field['colname']} = $request->{$field['colname']};
                        break;
                }
            }
        }

        return $row;
    }

    /**
     * Count Number of rows in Table of given Module.
     *
     * @param $module_name Module Name
     * @return int Number of rows in Module Table. -1 if Module doesn't exists.
     */
    public static function itemCount($module_name)
    {
        $module = self::get($module_name);
        if (isset($module)) {
            $model_name = $module->model;
            if (file_exists(base_path('app/Models/'.$model_name.'.php'))) {
                $model = 'App\\Models\\'.$model_name;

                return $model::count();
            } else {
                return -1;
            }
        } else {
            return -1;
        }
    }

    /**
     * Get Module Access for all roles or specific role.
     *
     * $role_accesses = LAModule::getRoleAccess($id);
     *
     * @param $module_id Module ID
     * @param int $specific_role Specific role id
     * @return array Array of Roles with accesses
     */
    public static function getRoleAccess($module_id, $specific_role = 0)
    {
        $module = self::find($module_id);
        $module = self::get($module->name);

        if ($specific_role) {
            $roles_arr = DB::table('roles')->where('id', $specific_role)->get();
        } else {
            $roles_arr = DB::table('roles')->get();
        }
        $roles = [];

        $arr_field_access = [
            'invisible' => 0,
            'readonly' => 1,
            'write' => 2
        ];

        foreach ($roles_arr as $role) {
            // get Current Module permissions for this role

            $module_perm = DB::table('role_la_module')->where('role_id', $role->id)->where('module_id', $module->id)->first();
            if (isset($module_perm->id)) {
                // set db values
                $role->view = $module_perm->acc_view;
                $role->create = $module_perm->acc_create;
                $role->edit = $module_perm->acc_edit;
                $role->delete = $module_perm->acc_delete;
            } else {
                $role->view = false;
                $role->create = false;
                $role->edit = false;
                $role->delete = false;
            }

            // get Current Module Fields permissions for this role

            $role->fields = [];
            foreach ($module->fields as $field) {
                // find role field permission
                $field_perm = DB::table('role_la_module_fields')->where('role_id', $role->id)->where('field_id', $field['id'])->first();

                if (isset($field_perm->id)) {
                    $field['access'] = $arr_field_access[$field_perm->access];
                } else {
                    $field['access'] = 0;
                }
                $role->fields[$field['id']] = $field;
                // $role->fields[$field['id']] = $field_perm->access;
            }
            $roles[] = $role;
        }

        return $roles;
    }

    /**
     * Get Specific Module Access for login user or specific user ($user_id).
     *
     * LAModule::hasAccess($module_id, $access_type, $user_id);
     *
     * @param $module_id Module ID / Name
     * @param string $access_type Access Type - view / create / edit / delete
     * @param int $user_id User id for which Access will be checked
     * @return bool Returns true if access is there or false
     */
    public static function hasAccess($module_id, $access_type = 'view', $user_id = 0)
    {
        $roles = [];

        if (is_string($module_id)) {
            $module = self::get($module_id);
            $module_id = $module->id;
        }

        if ($access_type == null || $access_type == '') {
            $access_type = 'view';
        }

        if ($user_id) {
            $user = User::find($user_id);
            if (isset($user->id)) {
                $roles = $user->roles();
            }
        } else {
            $roles = Auth::user()->roles();
        }
        foreach ($roles->get() as $role) {
            $module_perm = DB::table('role_la_module')->where('role_id', $role->id)->where('module_id', $module_id)->first();
            if (isset($module_perm->id)) {
                if (isset($module_perm->{'acc_'.$access_type}) && $module_perm->{'acc_'.$access_type} == 1) {
                    return true;
                } else {
                    continue;
                }
            } else {
                continue;
            }
        }

        return false;
    }

    /**
     * Get Module Field Access for role and access type.
     *
     * LAModule::hasFieldAccess($module_id, $field_id, $access_type, $user_id);
     *
     * @param $module_id Module ID / Name
     * @param $field_id Field ID / Name
     * @param string $access_type Access Type - view / write
     * @param int $user_id User id for which Access will be checked
     * @return bool Returns true if access is there or false
     */
    public static function hasFieldAccess($module_id, $field_id, $access_type = 'view', $user_id = 0)
    {
        $roles = [];

        // \Log::debug("module_id: ".$module_id." field_id: ".$field_id." access_type: ".$access_type);

        if (Auth::guest()) {
            return false;
        }

        if (is_string($module_id)) {
            $module = self::get($module_id);
            $module_id = $module->id;
        }

        if (is_string($field_id)) {
            $field_object = LAModuleField::where('module', $module_id)->where('colname', $field_id)->first();
            $field_id = $field_object->id;
        }

        if ($access_type == null || $access_type == '') {
            $access_type = 'view';
        }

        if ($user_id) {
            $user = User::find($user_id);
            if (isset($user->id)) {
                $roles = $user->roles();
            }
        } else {
            $roles = Auth::user()->roles();
        }

        $hasModuleAccess = false;

        foreach ($roles->get() as $role) {
            $module_perm = DB::table('role_la_module')->where('role_id', $role->id)->where('module_id', $module_id)->first();
            if (isset($module_perm->id)) {
                if ($access_type == 'view' && isset($module_perm->{'acc_'.$access_type}) && $module_perm->{'acc_'.$access_type} == 1) {
                    $hasModuleAccess = true;
                    break;
                } elseif ($access_type == 'write' && ((isset($module_perm->{'acc_create'}) && $module_perm->{'acc_create'} == 1) || (isset($module_perm->{'acc_edit'}) && $module_perm->{'acc_edit'} == 1))) {
                    $hasModuleAccess = true;
                    break;
                } else {
                    continue;
                }
            } else {
                continue;
            }
        }
        if ($hasModuleAccess) {
            $module_field_perm = DB::table('role_la_module_fields')->where('role_id', $role->id)->where('field_id', $field_id)->first();
            if (isset($module_field_perm->access)) {
                if ($access_type == 'view' && ($module_field_perm->{'access'} == 'readonly' || $module_field_perm->{'access'} == 'write')) {
                    return true;
                } elseif ($access_type == 'write' && $module_field_perm->{'access'} == 'write') {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }

        return false;
    }

    /**
     * Set Default Access for given Module and Role
     * Helps to set Full Module Access for Super Admin.
     *
     * LAModule::setDefaultRoleAccess($module_id, $role_id);
     *
     * @param $module_id Module ID / Name
     * @param $role_id Role ID
     * @param string $access_type Access Type - full / readonly
     */
    public static function setDefaultRoleAccess($module_id, $role_id, $access_type = 'readonly')
    {
        $module = null;

        if (is_string($module_id)) {
            $module = self::get($module_id);
            $module_id = $module->id;
        } else {
            $module = self::find($module_id);
            $module = self::get($module->name);
        }

        // Log::debug('LAModule:setDefaultRoleAccess ('.$module_id.', '.$role_id.', '.$access_type.')');

        $role = DB::table('roles')->where('id', $role_id)->first();

        $access_view = 0;
        $access_create = 0;
        $access_edit = 0;
        $access_delete = 0;
        $access_fields = 'invisible';

        if ($access_type == 'full') {
            $access_view = 1;
            $access_create = 1;
            $access_edit = 1;
            $access_delete = 1;
            $access_fields = 'write';
        } elseif ($access_type == 'readonly') {
            $access_view = 1;
            $access_create = 0;
            $access_edit = 0;
            $access_delete = 0;

            $access_fields = 'readonly';
        }

        $now = date('Y-m-d H:i:s');

        // 1. Set Module Access

        $module_perm = DB::table('role_la_module')->where('role_id', $role->id)->where('module_id', $module->id)->first();
        if (! isset($module_perm->id)) {
            DB::insert('insert into role_la_module (role_id, module_id, acc_view, acc_create, acc_edit, acc_delete, created_at, updated_at) values (?, ?, ?, ?, ?, ?, ?, ?)', [$role->id, $module->id, $access_view, $access_create, $access_edit, $access_delete, $now, $now]);
        } else {
            DB::table('role_la_module')->where('role_id', $role->id)->where('module_id', $module->id)->update(['acc_view' => $access_view, 'acc_create' => $access_create, 'acc_edit' => $access_edit, 'acc_delete' => $access_delete]);
        }

        // 2. Set Module Fields Access

        foreach ($module->fields as $field) {
            // find role field permission
            $field_perm = DB::table('role_la_module_fields')->where('role_id', $role->id)->where('field_id', $field['id'])->first();
            if (! isset($field_perm->id)) {
                DB::insert('insert into role_la_module_fields (role_id, field_id, access, created_at, updated_at) values (?, ?, ?, ?, ?)', [$role->id, $field['id'], $access_fields, $now, $now]);
            } else {
                DB::table('role_la_module_fields')->where('role_id', $role->id)->where('field_id', $field['id'])->update(['access' => $access_fields]);
            }
        }
    }

    /**
     * Set Default Access for given Module Field and Role
     * Helps to set Full Module Access for Super Admin when new field is created.
     *
     * LAModule::setDefaultFieldRoleAccess($field_id, $role_id);
     *
     * @param $field_id Field ID
     * @param $role_id Role ID
     * @param string $access_type Access Type - full / readonly
     */
    public static function setDefaultFieldRoleAccess($field_id, $role_id, $access_type = 'readonly')
    {
        $field = LAModuleField::find($field_id);
        $module = self::get($field->module);

        $role = DB::table('roles')->where('id', $role_id)->first();

        $access_fields = 'invisible';

        if ($access_type == 'full') {
            $access_fields = 'write';
        } elseif ($access_type == 'readonly') {
            $access_fields = 'readonly';
        }

        $now = date('Y-m-d H:i:s');

        // find role field permission
        $field_perm = DB::table('role_la_module_fields')->where('role_id', $role->id)->where('field_id', $field->id)->first();
        if (! isset($field_perm->id)) {
            DB::insert('insert into role_la_module_fields (role_id, field_id, access, created_at, updated_at) values (?, ?, ?, ?, ?)', [$role->id, $field->id, $access_fields, $now, $now]);
        } else {
            DB::table('role_la_module_fields')->where('role_id', $role->id)->where('field_id', $field->id)->update(['access' => $access_fields]);
        }
    }

    /**
     * Get list of Columns to display in Index Page for a particular Module
     * Also Filters the columns for Access control.
     *
     * LAModule::getListingColumns('Employees')
     *
     * @param $module_id_name Module Name / ID
     * @param bool $isObjects Whether you want just Names of Columns or Column Field Objects
     * @return array Array of Columns Names/Objects
     */
    public static function getListingColumns($module_id_name, $isObjects = false)
    {
        $module = null;
        if (is_int($module_id_name)) {
            $module = self::get($module_id_name);
        } else {
            $module = self::where('name', $module_id_name)->first();
        }
        $listing_cols = LAModuleField::where('module', $module->id)->where('listing_col', 1)->orderBy('sort', 'asc')->get()->toArray();

        if ($isObjects) {
            $id_col = ['label' => 'id', 'colname' => 'id'];
        } else {
            $id_col = 'id';
        }
        $listing_cols_temp = [$id_col];
        foreach ($listing_cols as $col) {
            if (self::hasFieldAccess($module->id, $col['id'])) {
                if ($isObjects) {
                    $listing_cols_temp[] = $col;
                } else {
                    $listing_cols_temp[] = $col['colname'];
                }
            }
        }

        return $listing_cols_temp;
    }

    /**
     * Get list of Columns to display in Index Page for a particular Module
     * Also Filters the columns for Access control.
     *
     * LAModule::getListingColumns('Employees')
     *
     * @param $module_id_name Module Name / ID
     * @param bool $isObjects Whether you want just Names of Columns or Column Field Objects
     * @return array Array of Columns Names/Objects
     */
    public function listingColumns($isObjects = false)
    {
        $listing_cols = LAModuleField::where('module', $this->id)->where('listing_col', 1)->orderBy('sort', 'asc')->get()->toArray();

        if ($isObjects) {
            $id_col = ['label' => 'id', 'colname' => 'id'];
        } else {
            $id_col = 'id';
        }
        $listing_cols_temp = [$id_col];
        foreach ($listing_cols as $col) {
            if (self::hasFieldAccess($this->id, $col['id'])) {
                if ($isObjects) {
                    $listing_cols_temp[] = $col;
                } else {
                    $listing_cols_temp[] = $col['colname'];
                }
            }
        }

        return $listing_cols_temp;
    }

    /**
     * Update Basic Module Information.
     *
     * LAModule::update($module_name, $view_col, $icon);
     *
     * @param text $module_name Module Name
     * @param text $view_col View Column Name to be changed
     * @param text $icon Font Awesome Icon to be changed
     * @return bool success Return true if success
     */
    public static function update2($module_name, $view_col, $icon)
    {
        $module = self::where('name', $module_name)->first();
        if (isset($module->id)) {
            $module->view_col = $view_col;
            $module->fa_icon = $icon;
            $module->save();

            $menu = LAMenu::where('url', strtolower($module->name))->where('type', 'module')->first();
            if (isset($menu->id)) {
                $menu->icon = $icon;
                $menu->save();
            }

            return true;
        }

        return false;
    }

    /**
     * Remove the specified Module Including Module Schema, DB Table,
     * Menu, Model, Model fields, Controller, Views directory, routes, Observers, Language file and modifies the migration file.
     *
     * @param $id Module ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public static function deleteModule($id, $return_response = false)
    {
        $permission_msg = '';
        $correct_file_perms = '0644';
        $correct_dir_perms = '0755';
        $modules = self::all();

        if (is_numeric($id)) {
            $module = self::find($id);
        } else {
            $module = self::where('name', $id)->orWhere('name_db', $id)->first();
        }

        // ===================================================
        // ============ check permissions for all ============
        // ===================================================

        // Migration
        $mfiles = scandir(base_path('database/migrations/'));
        foreach ($mfiles as $mfile) {
            if (str_contains($mfile, 'create_'.$module->name_db.'_table')) {
                $perms = LAHelper::fileperms(base_path('database/migrations/'.$mfile));
                if (! LAHelper::fileperms_cmp($perms, $correct_file_perms)) {
                    $permission_msg .= 'Please Give permission to database/migrations<br>';
                }
            }
        }
        // Admin Route
        if (LAHelper::laravel_ver() >= 5.3) {
            $file_admin_routes = base_path('routes/admin_routes.php');
        } else {
            $file_admin_routes = base_path('app/Http/admin_routes.php');
        }
        if (file_exists($file_admin_routes)) {
            $perms = LAHelper::fileperms($file_admin_routes);
            if (! LAHelper::fileperms_cmp($perms, $correct_file_perms)) {
                $permission_msg .= 'Please Give permission to routes<br>';
            }
        }
        // Models
        if ($module->model == 'User' || $module->model == 'Role' || $module->model == 'Permission') {
            $model_file = app_path($module->model.'.php');
        } else {
            $model_file = app_path('Models/'.$module->model.'.php');
        }
        if (file_exists($model_file)) {
            $perms = LAHelper::fileperms($model_file);
            if (! LAHelper::fileperms_cmp($perms, $correct_file_perms)) {
                $permission_msg .= 'Please Give permission to '.$model_file.'<br>';
            }
        }
        // View Files
        $views_dir = resource_path('views/la/'.$module->name_db);
        if (file_exists($views_dir)) {
            // Directory
            $perms = LAHelper::fileperms($views_dir);
            if (! LAHelper::fileperms_cmp($perms, $correct_file_perms)) {
                $permission_msg .= 'Please Give permission to '.$views_dir.'<br>';
            }

            // Directory Files
            $views = scandir($views_dir);
            foreach ($views as $view) {
                if ($view != '.' && $view != '..') {
                    $file = $views_dir.'/'.$view;
                    $perms = LAHelper::fileperms($file);
                    if (! LAHelper::fileperms_cmp($perms, $correct_file_perms)) {
                        $permission_msg .= 'Please Give permission to '.$file.'<br>';
                    }
                }
            }
        }

        // Controller
        $file = app_path('Http/Controllers/LA/'.$module->controller.'.php');
        if (file_exists($file)) {
            $perms = LAHelper::fileperms($file);
            if (! LAHelper::fileperms_cmp($perms, $correct_file_perms)) {
                $permission_msg .= 'Please Give permission to '.$file.'<br>';
            }
        }
        // Observer
        $file = app_path('Observers/'.$module->model.'Observer.php');
        if (file_exists($file)) {
            $perms = LAHelper::fileperms($file);
            if (! LAHelper::fileperms_cmp($perms, $correct_file_perms)) {
                $permission_msg .= 'Please Give permission to '.$file.'<br>';
            }
        }
        // Language file
        $config = CodeGenerator::generateConfig($module->name, $module->icon, false);
        $langFile = resource_path('lang/en/'.$config->langFile).'.php';
        if (file_exists($langFile)) {
            $perms = LAHelper::fileperms($langFile);
            if (! LAHelper::fileperms_cmp($perms, $correct_file_perms)) {
                $permission_msg .= 'Please Give permission to '.$langFile.'<br>';
            }
        }

        // return if need permission
        if ($permission_msg != '') {
            return redirect()->route(config('laraadmin.adminRoute').'.la_modules.index', ['modules' => $modules, 'msg' => $permission_msg, 'err_module' => $module->name]);
        }

        // ===================================================
        // ============== Start Module Deletion ==============
        // ===================================================

        // Delete Menu
        $menuItems = LAMenu::where('name', $module->name)->first();
        if (isset($menuItems)) {
            $menuItems->delete();
        }

        // Delete Module Fields
        $la_module_fields = LAModuleField::where('module', $module->id);
        foreach ($la_module_fields as $field) {
            $field->delete();
        }

        // Delete Resource Views directory
        if (file_exists(resource_path('views/la/'.$module->name_db))) {
            File::cleanDirectory(resource_path('views/la/'.$module->name_db));
        }

        // Delete Controller
        if (file_exists(app_path('/Http/Controllers/LA/'.$module->controller.'.php'))) {
            File::delete(app_path('/Http/Controllers/LA/'.$module->controller.'.php'));
        }

        // Delete Language File
        if (file_exists($langFile)) {
            File::delete($langFile);
        }
        // Modify Migration for Deletion
        // Find existing migration file
        $fileExistName = '';
        foreach ($mfiles as $mfile) {
            if (str_contains($mfile, 'create_'.$module->name_db.'_table')) {
                $migrationClassName = ucfirst(Str::camel('create_'.$module->name_db.'_table'));

                $templateDirectory = base_path('/app/Stubs');
                $migrationData = file_get_contents($templateDirectory.'/migration_removal.stub');
                $migrationData = str_replace('__migration_class_name__', $migrationClassName, $migrationData);
                $migrationData = str_replace('__db_table_name__', $module->name_db, $migrationData);
                file_put_contents(base_path('database/migrations/'.$mfile), $migrationData);
            }
        }

        // Delete Admin Routes
        while (LAHelper::getLineWithString($file_admin_routes, 'LA\\'.$module->controller) != -1) {
            $line = LAHelper::getLineWithString($file_admin_routes, 'LA\\'.$module->controller);
            if (is_string($line)) {
                $fileData = file_get_contents($file_admin_routes);
                $fileData = str_replace($line, '', $fileData); // /\r|\n/
                file_put_contents($file_admin_routes, $fileData);
            }
        }
        if (LAHelper::getLineWithString($file_admin_routes, '=== '.$module->name.' ===') != -1) {
            $line = LAHelper::getLineWithString($file_admin_routes, '=== '.$module->name.' ===');
            $fileData = file_get_contents($file_admin_routes);
            $fileData = str_replace($line.'', '', $fileData); // /\r|\n/
            file_put_contents($file_admin_routes, $fileData);
        }

        // Delete Model
        if (file_exists($model_file)) {
            File::delete($model_file);
        }

        // Delete Observer
        if (file_exists(app_path('/Observers/'.$module->model.'Observer.php'))) {
            File::delete(app_path('/Observers/'.$module->model.'Observer.php'));
        }
        if (file_exists(app_path('Providers/LAProvider.php'))) {
            $file_observer = app_path('Providers/LAProvider.php');
            if (LAHelper::getLineWithString($file_observer, '\\App\\Observers\\'.$module->model) != -1) {
                $line = LAHelper::getLineWithString($file_observer, '\\App\\Observers\\'.$module->model);
                $observerfileData1 = file_get_contents($file_observer);
                $observerfileData = str_replace($line.'', '', $observerfileData1); // /\r|\n/
                file_put_contents($file_observer, $observerfileData);
            }
        }
        // Delete Table
        if (Schema::hasTable($module->name_db)) {
            Schema::disableForeignKeyConstraints();
            Schema::drop($module->name_db);
            Schema::enableForeignKeyConstraints();
        }

        // Delete Module
        $module->delete();

        if ($return_response) {
            return ['modules' => $modules, 'msg' => $permission_msg, 'err_module' => $module];
        } else {
            return true;
        }
    }

    /**
     * Remove Ultiselect values when deleted.
     *
     * LAModule::clearMultiselects($module_name, $id);
     *
     * @param text $module_name Module Name
     * @param text $id View Record id
     * @return bool success Return true if success
     */
    public static function clearMultiselects($module_name, $id)
    {
        Log::debug("$module_name::deleting('$id')");

        $module = self::where('name', $module_name)->first();

        if (isset($module->id)) {
            $fields = LAModuleField::where('field_type', '15')->where('popup_vals', '@'.$module->name_db)->get();

            foreach ($fields as $field) {
                $field_module = self::find($field->module);
                $existing_records = DB::table($field_module->name_db)->where($field->colname, 'LIKE', '%"'.$id.'"%')->get();
                foreach ($existing_records as $record) {
                    $colname = $field->colname;
                    $record_array = [];
                    $json = json_decode($record->$colname);
                    foreach ($json as $key => $value) {
                        if ($value != $id) {
                            $record_array[] = $value;
                        }
                    }
                    $new_record = json_encode($record_array);
                    DB::table($field_module->name_db)->where('id', $record->id)->update([$field->colname => $new_record]);
                }
            }

            return true;
        }

        return false;
    }
}
