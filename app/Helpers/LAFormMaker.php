<?php

/***
 * Code generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Helpers;

use App\Models\LAConfig;
use App\Models\LAModule;
use App\Models\LAModuleFieldType;
use Collective\Html\FormFacade as Form;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/***
 * LaraAdmin FormMaker Helper
 *
 * This class is blade directive implementation for Form Elements in Module as well as other utilities
 * for Access Control. It also has method process_values which processes fields data from its context.
 */
class LAFormMaker
{
    /**
     * Print input field enclosed within form.
     *
     * Uses blade syntax @la_input('name')
     *
     * @param $module Module Object
     * @param $field_name Field Name for which input has be created
     * @param null $default_val Default Value of Field. This will override default value from context.
     * @param null $required2 Is this field mandatory.
     * @param string $class Custom css class. Default would be bootstrap 'form-control' class
     * @param array $params Additional Parameters for Customization
     * @return string This return html string with field inputs
     */
    public static function input($module, $field_name, $default_val = null, $required2 = null, $class = 'form-control', $params = [])
    {
        // Check Field Write Aceess
        if (LAModule::hasFieldAccess($module->id, $module->fields[$field_name]['id'], $access_type = 'write')) {
            $row = null;
            if (isset($module->row)) {
                $row = $module->row;
            }

            // print_r($module->fields);
            $label = $module->fields[$field_name]['label'];
            $field_type = $module->fields[$field_name]['field_type'];
            $unique = $module->fields[$field_name]['unique'];
            $defaultvalue = $module->fields[$field_name]['defaultvalue'];
            $minlength = $module->fields[$field_name]['minlength'];
            $maxlength = $module->fields[$field_name]['maxlength'];
            $required = $module->fields[$field_name]['required'];
            $popup_vals = $module->fields[$field_name]['popup_vals'];
            $comment = $module->fields[$field_name]['comment'];

            if (isset($required2)) {
                $required = $required2;
            }

            $field_type = LAModuleFieldType::find($field_type);

            $form_group_select2 = '';
            if ($field_type->name == 'Multiselect' || $field_type->name == 'Dropdown') {
                $form_group_select2 = 'fg-select2';
            }

            $out = '<div class="form-group '.$form_group_select2.'" title="'.$comment.'">';
            // Asterisk for required field
            $required_ast = '';

            if (! isset($params['class'])) {
                $params['class'] = $class;
            }
            if (! isset($params['placeholder'])) {
                $params['placeholder'] = 'Enter '.$label;
            }
            if (isset($minlength)) {
                $params['data-rule-minlength'] = $minlength;
            }
            if (isset($maxlength)) {
                $params['data-rule-maxlength'] = $maxlength;
            }
            if ($unique && ! isset($params['unique'])) {
                $params['data-rule-unique'] = 'true';
                $params['field_id'] = $module->fields[$field_name]['id'];
                $params['adminRoute'] = config('laraadmin.adminRoute');
                if (isset($row)) {
                    $params['isEdit'] = true;
                    $params['row_id'] = $row->id;
                } else {
                    $params['isEdit'] = false;
                    $params['row_id'] = 0;
                }
                $out .= '<input type="hidden" name="_token_'.$module->fields[$field_name]['id'].'" value="'.csrf_token().'">';
            }

            if ($required && ! isset($params['required'])) {
                $params['required'] = $required;
                $required_ast = '*';
            }

            $quick_add_form = (isset($module->quick_add_form) && $module->quick_add_form == true);
            $out .= self::input_field($module->id, $field_type->name, $label, $field_name, $row, $required_ast, $default_val, $defaultvalue, $params, $popup_vals, $required, $quick_add_form);

            $out .= '</div>';

            return $out;
        } else {
            return '';
        }
    }

    /**
     * Print config field.
     *
     * @return string This return html string with field inputs
     */
    public static function config($key)
    {
        // Check Config Availability
        $config = LAConfig::where('key', $key)->first();
        if (isset($config->id)) {
            $field_type = LAModuleFieldType::find($config->field_type);
            $label = $config->label;
            $field_name = $config->key;
            $required_ast = '';
            $default_val = null;
            $defaultvalue = $config->value;
            $params = [];
            $minlength = $config->minlength;
            $maxlength = $config->maxlength;
            $popup_vals = $config->popup_vals;
            $required = $config->required;
            $quick_add_form = false;

            // Create row from Config Table
            $row = (object) [
                $field_name => $config->value
            ];

            $out = '<div class="form-group config-input row">';
            $out .= '<div class="col-md-6"><button class="btn btn-danger delete-config btn-xs pull-right edit_config ml5" config_id="'.$config->id.'" config_key="'.$config->key.'" title="Delete Configuration - '.$config->label.'">
                    <i class="fa fa-trash"></i></button>
                    <a href="'.url(config('laraadmin.adminRoute')).'/la_configs/'.$config->id.'/edit" class="btn btn-warning btn-xs pull-right edit_config" config_id="'.$config->id.'" config_key="'.$config->label.' ('.$config->key.')" title="Edit Configuration - '.$config->label.'">
                    <i class="fa fa-pencil"></i></a>';

            if (! isset($params['class'])) {
                $params['class'] = 'form-control';
            }
            if (! isset($params['placeholder'])) {
                $params['placeholder'] = 'Enter '.$label;
            }
            if (isset($minlength)) {
                $params['data-rule-minlength'] = $minlength;
            }
            if (isset($maxlength)) {
                $params['data-rule-maxlength'] = $maxlength;
            }

            if ($required && ! isset($params['required'])) {
                $params['required'] = $required;
                $required_ast = '*';
            }
            $out .= self::input_field(0, $field_type->name, $label, $field_name, $row, $required_ast, $default_val, $defaultvalue, $params, $popup_vals, $required, $quick_add_form);

            $out .= "</div><div class='col-md-6' style=''><pre class='mb16 language-php' style='line-height:3;'><code class=' language-php'>LAConfig::getByKey('$key')</code></pre></div></div><hr>";

            return $out;
        } else {
            return '';
        }
    }

    private static function input_field($module_id, $field_type, $label, $field_name, $row, $required_ast, $default_val, $defaultvalue, $params, $popup_vals, $required, $quick_add_form)
    {
        $out = '';
        switch ($field_type) {
            case 'Address':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';

                if ($default_val == null) {
                    $default_val = $defaultvalue;

                    // Override the edit value
                    if (isset($row) && isset($row->$field_name)) {
                        $default_val = $row->$field_name;
                    }
                }
                if (! (isset($params['data-rule-maxlength']) && $params['data-rule-maxlength'] != 0 && $params['data-rule-maxlength'] != '0')) {
                    unset($params['data-rule-maxlength']);
                }
                $params['cols'] = 30;
                $params['rows'] = 3;
                $out .= Form::textarea($field_name, $default_val, $params);
                break;
            case 'Checkbox':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
                $out .= '<input type="hidden" value="false" name="'.$field_name.'_hidden">';

                // ############### Remaining
                unset($params['placeholder']);
                unset($params['data-rule-maxlength']);

                if ($default_val == null) {
                    $default_val = $defaultvalue;
                }
                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = $row->$field_name;
                }

                $out .= Form::checkbox($field_name, $field_name, $default_val, $params);
                $out .= '<div class="Switch Round On" style="vertical-align:top;margin-left:10px;"><div class="Toggle"></div></div>';
                break;
            case 'Currency':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';

                if ($default_val == null) {
                    $default_val = $defaultvalue;

                    // Override the edit value
                    if (isset($row) && isset($row->$field_name)) {
                        $default_val = $row->$field_name;
                    }
                }

                if ($params['data-rule-maxlength'] != '' && $params['data-rule-maxlength'] != '0' && $params['data-rule-maxlength'] != 0) {
                    $params['max'] = $params['data-rule-maxlength'];
                }
                if ($params['data-rule-minlength'] == 0) {
                    $params['min'] = 0;
                } elseif ($params['data-rule-minlength'] != '' && $params['data-rule-minlength'] != '-1' && $params['data-rule-minlength'] != -1) {
                    $params['min'] = $params['data-rule-minlength'];
                }

                unset($params['data-rule-minlength']);
                unset($params['data-rule-maxlength']);

                $params['data-rule-currency'] = 'true';
                $params['min'] = '0';
                $out .= Form::number($field_name, $default_val, $params);
                break;
            case 'Date':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';

                if ($default_val == null) {
                    $default_val = $defaultvalue;
                }
                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = $row->$field_name;
                }
                $dval = $default_val;
                $is_null = '';
                if ($default_val == null || $default_val == 'NULL') {
                    // $is_null = " checked";
                    // $params['readonly'] = "";
                    $dval = '';
                } elseif ($default_val != '') {
                    $dval = date('d/m/Y', strtotime($default_val));
                }

                unset($params['data-rule-minlength']);
                unset($params['data-rule-maxlength']);
                // $params['data-rule-date'] = "true";

                $null_text = '';
                if ($required) {
                    unset($params['readonly']);
                    $null_text = '';
                } else {
                    $null_text = "<span class='input-group-addon null_date'><input class='cb_null_date' id='cb_null_date_".$module_id.'_'.$field_name."' type='checkbox' name='null_date_".$field_name."' $is_null value='true'> <label for='cb_null_date_".$module_id.'_'.$field_name."' class='label-for-check' title='Date Not Available'>NA ?</label></span>";
                }

                $out .= "<div class='input-group date'>";
                $out .= Form::text($field_name, $dval, $params);
                $out .= "<span class='input-group-addon input_dt'><span class='fa fa-calendar'></span></span>$null_text</div>"; // if same field name occure in same page
                // $out .= Form::date($field_name, $default_val, $params);
                break;
            case 'Datetime':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';

                if ($default_val == null) {
                    $default_val = $defaultvalue;
                }

                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = $row->$field_name;
                }

                $is_null = '';
                if ($default_val == null || $default_val == 'NULL') {
                    $is_null = ' checked';
                    $params['readonly'] = '';
                } elseif ($default_val == null) {
                    $default_val = $defaultvalue;
                }

                unset($params['data-rule-minlength']);
                unset($params['data-rule-maxlength']);

                // ############### Remaining
                $dval = $default_val;
                if ($default_val == 'now()') {
                    $dval = date('d/m/Y h:i A');
                } elseif (isset($default_val) && $default_val != '' && $default_val != 'NULL') {
                    $dval = date('d/m/Y h:i A', strtotime($default_val));
                }
                $out .= "<div class='input-group datetime'>";
                $out .= Form::text($field_name, $dval, $params);
                $out .= "<span class='input-group-addon input_dt'><span class='fa fa-calendar'></span></span><span class='input-group-addon null_date'><input class='cb_null_date' id='cb_null_datetime_".$module_id.'_'.$field_name."' type='checkbox' name='null_date_".$field_name."' $is_null value='true'><label for='cb_null_datetime_".$module_id.'_'.$field_name."' class='label-for-check'>Null ?</label></span></div>";
                break;
            case 'Decimal':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';

                if ($default_val == null) {
                    $default_val = $defaultvalue;
                }
                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = $row->$field_name;
                }

                if ($params['data-rule-maxlength'] != '' && $params['data-rule-maxlength'] != '0' && $params['data-rule-maxlength'] != 0) {
                    $params['max'] = $params['data-rule-maxlength'];
                }
                if ($params['data-rule-minlength'] == 0) {
                    $params['min'] = 0;
                } elseif ($params['data-rule-minlength'] != '' && $params['data-rule-minlength'] != '-1' && $params['data-rule-minlength'] != -1) {
                    $params['min'] = $params['data-rule-minlength'];
                }

                unset($params['data-rule-minlength']);
                unset($params['data-rule-maxlength']);

                $out .= Form::number($field_name, $default_val, $params);
                break;
            case 'Dropdown':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';

                unset($params['data-rule-maxlength']);
                $params['data-placeholder'] = $params['placeholder'];
                unset($params['placeholder']);
                $params['rel'] = 'select2';
                $params['id'] = $field_name;

                // echo $defaultvalue;
                if ($default_val == null) {
                    $default_val = $defaultvalue;
                }
                // Override the edit value
                if (isset($row) && $row->$field_name) {
                    $default_val = $row->$field_name;
                } elseif ($default_val == null || $default_val == '' || $default_val == 'NULL') {
                    // When Adding Record if we dont have default value let's not show NULL By Default
                    $default_val = '0';
                }

                // Bug here - NULL value Item still shows Not null in Form
                if ($default_val == null) {
                    $params['disabled'] = '';
                }

                $popup_vals_str = $popup_vals;
                if ($popup_vals != '') {
                    $popup_vals = self::process_values($popup_vals);
                    if (isset($params['single-option']) && $params['single-option'] == true) {
                        foreach ($popup_vals as $key => $value) {
                            if ($key == $default_val) {
                                $popup_vals = [
                                    $key => $value
                                ];
                            }
                        }
                        unset($params['single-option']);
                    } elseif (isset($params['given-options-only'])) {
                        $popup_vals_temp = [];
                        foreach ($popup_vals as $key => $value) {
                            if (in_array($key, $params['given-options-only'])) {
                                $popup_vals_temp[$key] = $value;
                            }
                        }
                        $popup_vals = $popup_vals_temp;
                        unset($params['given-options-only']);
                    }
                } else {
                    $popup_vals = [];
                }

                if (! $required) {
                    $popup_vals[0] = 'None';
                    ksort($popup_vals);
                }
                $popup_module_id = 0;
                if (is_string($popup_vals_str) && str_starts_with($popup_vals_str, '@')) {
                    // Get Module / Table Name
                    $table_name = str_ireplace('@', '', $popup_vals_str);
                    $table_name = strtolower(Str::plural($table_name));
                    // Search Module
                    $popup_module = LAModule::getByTable($table_name);
                    if (isset($popup_module->id)) {
                        $popup_module_id = $popup_module->id;
                    }
                }

                if ($popup_module_id) {
                    if ($quick_add_form) {
                        $out .= Form::select($field_name, $popup_vals, $default_val, $params);
                    } else {
                        $out .= "<span class='row m0'><span class='col-md-11 col-sm-8 col-xs-6 p0'>".Form::select($field_name, $popup_vals, $default_val, $params).'</span>';
                        $out .= "<span class='col-md-1 col-sm-4 col-xs-6 m0 p0'><a class='btn btn-success btn-xs pull-right btn_quick_add' popup_module_id='".$popup_module_id."' field_name='$field_name' popup_vals='".$popup_vals_str."' style='margin-top:3px;'><i class='fa fa-plus'></i></a></span>";
                        $out .= '</span>';
                    }
                } else {
                    $out .= Form::select($field_name, $popup_vals, $default_val, $params);
                }

                break;
            case 'Email':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';

                if ($default_val == null) {
                    $default_val = $defaultvalue;
                }
                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = $row->$field_name;
                }

                $params['data-rule-email'] = 'true';
                $out .= Form::email($field_name, $default_val, $params);
                break;
            case 'File':
                $out .= '<label for="'.$field_name.'" style="display:block;">'.$label.$required_ast.' :</label>';

                if ($default_val == null) {
                    $default_val = $defaultvalue;
                }
                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = $row->$field_name;
                }
                if (! is_numeric($default_val)) {
                    $default_val = 0;
                }
                $out .= Form::hidden($field_name, $default_val, $params);

                if ($default_val != 0) {
                    $upload = \App\Models\Upload::find($default_val);
                }
                if (isset($upload->id)) {
                    $out .= "<a class='btn btn-default btn_upload_file hide' file_type='file' selecter='".$field_name."'>Upload <i class='fa fa-cloud-upload'></i></a>
                        <a class='uploaded_file' target='_blank' href='".url('files/'.$upload->hash.DIRECTORY_SEPARATOR.$upload->name)."'><i class='fa fa-file-o'></i><i title='Remove File' class='fa fa-times'></i></a>";
                } else {
                    $out .= "<a class='btn btn-default btn_upload_file' file_type='file' selecter='".$field_name."'>Upload <i class='fa fa-cloud-upload'></i></a>
                        <a class='uploaded_file hide' target='_blank'><i class='fa fa-file-o'></i><i title='Remove File' class='fa fa-times'></i></a>";
                }
                break;

            case 'Files':
                $out .= '<label for="'.$field_name.'" style="display:block;">'.$label.$required_ast.' :</label>';

                if ($default_val == null) {
                    $default_val = $defaultvalue;
                }
                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = $row->$field_name;
                }
                if (is_array($default_val)) {
                    $default_val = json_encode($default_val);
                }

                $default_val_arr = json_decode($default_val);

                if (is_array($default_val_arr) && count($default_val_arr) > 0) {
                    $uploadIds = [];
                    $uploadImages = '';
                    foreach ($default_val_arr as $uploadId) {
                        $upload = \App\Models\Upload::find($uploadId);
                        if (isset($upload->id)) {
                            $uploadIds[] = $upload->id;
                            $fileImage = '';
                            if (in_array($upload->extension, ['jpg', 'png', 'gif', 'jpeg'])) {
                                $fileImage = "<img src='".url('files/'.$upload->hash.DIRECTORY_SEPARATOR.$upload->name.'?s=90')."'>";
                            } else {
                                $fileImage = "<i class='fa fa-file-o'></i>";
                            }
                            $uploadImages .= "<a class='uploaded_file2' upload_id='".$upload->id."' target='_blank' href='".url('files/'.$upload->hash.DIRECTORY_SEPARATOR.$upload->name)."'>".$fileImage."<i title='Remove File' class='fa fa-times'></i></a>";
                        }
                    }

                    $out .= Form::hidden($field_name, json_encode($uploadIds), $params);
                    if (count($uploadIds) > 0) {
                        $out .= "<div class='uploaded_files'>".$uploadImages.'</div>';
                    }
                } else {
                    $out .= Form::hidden($field_name, '[]', $params);
                    $out .= "<div class='uploaded_files'></div>";
                }
                $out .= "<a class='btn btn-default btn_upload_files' file_type='files' selecter='".$field_name."' style='margin-top:5px;'>Upload <i class='fa fa-cloud-upload'></i></a>";
                break;

            case 'Float':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';

                if ($default_val == null) {
                    $default_val = $defaultvalue;
                }
                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = $row->$field_name;
                }

                if ($params['data-rule-maxlength'] != '' && $params['data-rule-maxlength'] != 0) {
                    $params['max'] = $params['data-rule-maxlength'];
                }
                if ($params['data-rule-minlength'] != '' && $params['data-rule-minlength'] != 0) {
                    $params['min'] = $params['data-rule-minlength'];
                }

                unset($params['data-rule-minlength']);
                unset($params['data-rule-maxlength']);

                $out .= Form::number($field_name, $default_val, $params);
                break;
            case 'HTML':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';

                if ($default_val == null) {
                    $default_val = $defaultvalue;
                }
                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = $row->$field_name;
                }
                unset($params['data-rule-minlength']);
                unset($params['data-rule-maxlength']);
                unset($params['placeholder']);

                $params['class'] = 'htmlbox';
                $params['id'] = 'summernote_'.$field_name;
                $out .= Form::textarea($field_name, $default_val, $params);
                break;
            case 'Image':
                $out .= '<label for="'.$field_name.'" style="display:block;">'.$label.$required_ast.' :</label>';

                if ($default_val == null) {
                    $default_val = $defaultvalue;
                }
                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = $row->$field_name;
                }
                if (! is_numeric($default_val)) {
                    $default_val = 0;
                }
                $out .= Form::hidden($field_name, $default_val, $params);

                if ($default_val != 0) {
                    $upload = \App\Models\Upload::find($default_val);
                }
                if (isset($params['direct_file_select']) && $params['direct_file_select'] == true) {
                    // TODO: Direct Upload
                    // @la_input($module, 'profile_img', null, null, 'form-control', ['direct_file_select' => true])
                } else {
                    // Via File Selector
                    if (isset($upload->id)) {
                        $out .= "<a class='btn btn-default btn_upload_image hide' file_type='image' selecter='".$field_name."'>Upload <i class='fa fa-cloud-upload'></i></a>
                            <div class='uploaded_image'><img src='".url('files/'.$upload->hash.DIRECTORY_SEPARATOR.$upload->name.'?s=150')."'><i title='Remove Image' class='fa fa-times'></i></div>";
                    } else {
                        $out .= "<a class='btn btn-default btn_upload_image' file_type='image' selecter='".$field_name."'>Upload <i class='fa fa-cloud-upload'></i></a>
                            <div class='uploaded_image hide'><img src=''><i title='Remove Image' class='fa fa-times'></i></div>";
                    }
                }

                break;
            case 'Integer':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';

                if ($params['data-rule-maxlength'] != '' && $params['data-rule-maxlength'] != '0' && $params['data-rule-maxlength'] != 0) {
                    $params['max'] = $params['data-rule-maxlength'];
                }
                if ($params['data-rule-minlength'] == 0) {
                    $params['min'] = 0;
                } elseif ($params['data-rule-minlength'] != '' && $params['data-rule-minlength'] != '-1' && $params['data-rule-minlength'] != -1) {
                    $params['min'] = $params['data-rule-minlength'];
                }

                unset($params['data-rule-minlength']);
                unset($params['data-rule-maxlength']);

                if ($default_val == null) {
                    $default_val = $defaultvalue;
                }
                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = $row->$field_name;
                }
                // $params['min'] = "0"; // Required for Non-negative numbers
                $out .= Form::number($field_name, $default_val, $params);
                break;
            case 'JSON':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';

                if ($default_val == null) {
                    $default_val = $defaultvalue;
                }
                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = $row->$field_name;
                }
                if (! (isset($params['data-rule-maxlength']) && $params['data-rule-maxlength'] != 0 && $params['data-rule-maxlength'] != '0')) {
                    unset($params['data-rule-maxlength']);
                }
                $out .= Form::text($field_name, $default_val, $params);
                break;
            case 'List':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
                if ($default_val == null) {
                    $default_val = $defaultvalue;
                }
                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = $row->$field_name;
                }
                $out .= Form::hidden($field_name, $default_val, []);

                $min = '';
                $max = '';
                if ($params['data-rule-minlength'] > 0) {
                    $min = "data-rule-mincount='".$params['data-rule-minlength']."'";
                }
                if ($params['data-rule-maxlength'] > 0) {
                    $max = "data-rule-maxcount='".$params['data-rule-maxlength']."'";
                }
                $out .= "<div class='dynalist' id='".$field_name."' label='".$label."' $min $max><ul contenteditable='true'>";

                /*
                // This portion handled by JS
                if(is_string($default_val) && str_starts_with($default_val, "[")) {
                    $value = json_decode($default_val);
                    foreach ($value as $row) {
                        $out .= "<li>".$row."</li>";
                    }
                    if(count($value) == 0) {
                        $out .= "<li><br></li>";
                    }
                }
                */
                $out .= '</ul></div>';
                break;
            case 'Checklist':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';

                $min = '';
                $max = '';
                if ($params['data-rule-minlength'] > 0) {
                    $min = "data-rule-mincount='".$params['data-rule-minlength']."'";
                }
                if ($params['data-rule-maxlength'] > 0) {
                    $max = "data-rule-maxcount='".$params['data-rule-maxlength']."'";
                }

                if ($default_val == null) {
                    $default_val = $defaultvalue;
                }
                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = $row->$field_name;
                    if (isset($default_val) && $default_val != '' && $default_val != '[]' && $default_val != '{}') {
                        $default_val_count = count($default_val);
                    }
                }

                $out .= '<span class="row m0 checklist">'.
                    Form::hidden($field_name, $default_val, []).
                    '<span class="col-md-11 col-sm-8 col-xs-6 p0">'.
                    '<ul class="todo-list">';
                if (isset($default_val_count) && $default_val_count) {
                    foreach (json_decode($default_val) as $value) {
                        $out .= '<li class="'.($value->checked == 'true' ? 'done' : '').'">'.
                            '<span class="handle">
                                            <i class="fa fa-ellipsis-v"></i>
                                            <i class="fa fa-ellipsis-v"></i>
                                        </span>'.
                            '<span class="value_checklist">'.
                            '<input type="checkbox" value="'.$value->checked.'" '.($value->checked == 'true' ? 'checked' : '').' name="checked" style="position:relative;top:1px;margin:0 2px">'.
                            '<span style="display:inline-block;"><span class="text display checklist_title">'.$value->title.'</span>'.
                            '<input type="text" class="edit form-control" style="display:none"/></span>'.
                            '</span>'.
                            '<div class="tools">'.
                            '<i class="fa fa-trash-o btn_checklist_remove"></i>
                                        </div>'.
                            '</li>';
                    }
                } else {
                    $out .= '<li>'.
                        '<span class="handle"><i class="fa fa-ellipsis-v" style="margin-right:3px"></i><i class="fa fa-ellipsis-v"></i></span>'.
                        '<span class="value_checklist">'.
                        '<input type="checkbox" value="false" name="checked" style="position:relative;top:1px;margin:0 2px">'.
                        '<span style="display:inline-block;"><span class="text display checklist_title">Untitled</span>'.
                        '<input type="text" class="edit form-control" style="display:none"/></span>'.
                        '</span>'.
                        '<div class="tools">'.
                        '<i class="fa fa-trash-o btn_checklist_remove"></i>'.
                        '</div>'.
                        '</li>';
                }
                $out .= '</ul></span>';
                $out .= "<span class='col-md-1 col-sm-4 col-xs-6 m0 p0 div-btn-checklist'><a class='btn btn-success btn-xs pull-right btn-checklist' ".$min.$max."><i class='fa fa-plus'></i></a></span>";
                $out .= '</span>';
                break;
            case 'Mobile':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';

                if ($default_val == null) {
                    $default_val = $defaultvalue;
                }
                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = $row->$field_name;
                }

                $out .= Form::text($field_name, $default_val, $params);
                break;
            case 'Multiselect':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';

                unset($params['data-rule-maxlength']);
                $params['data-placeholder'] = 'Select multiple '.Str::plural($label);
                unset($params['placeholder']);
                $params['multiple'] = 'true';
                $params['rel'] = 'select2';
                $params['id'] = $field_name;
                if ($default_val == null) {
                    if ($defaultvalue != '') {
                        $default_val = json_decode($defaultvalue);
                    } else {
                        $default_val = '';
                    }
                }
                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = json_decode($row->$field_name);
                }

                $popup_vals_str = $popup_vals;
                if ($popup_vals != '') {
                    $popup_vals = self::process_values($popup_vals);
                } else {
                    $popup_vals = [];
                }

                if (! $required) {
                    $popup_vals[0] = 'None';
                    ksort($popup_vals);
                }
                $popup_module_id = 0;
                if (is_string($popup_vals_str) && str_starts_with($popup_vals_str, '@')) {
                    // Get Module / Table Name
                    $table_name = str_ireplace('@', '', $popup_vals_str);
                    $table_name = strtolower(Str::plural($table_name));
                    // Search Module
                    $popup_module = LAModule::getByTable($table_name);
                    if (isset($popup_module->id)) {
                        $popup_module_id = $popup_module->id;
                    }
                }

                if ($popup_module_id) {
                    if ($quick_add_form) {
                        $out .= Form::select($field_name.'[]', $popup_vals, $default_val, $params);
                    } else {
                        $out .= "<span class='row m0'><span class='col-md-11 col-sm-8 col-xs-6 p0'>".Form::select($field_name.'[]', $popup_vals, $default_val, $params).'</span>';
                        $out .= "<span class='col-md-1 col-sm-4 col-xs-6 m0 p0'><a class='btn btn-success btn-xs pull-right btn_quick_add' popup_module_id='".$popup_module_id."' field_name='$field_name' popup_vals='".$popup_vals_str."' style='margin-top:3px;'><i class='fa fa-plus'></i></a></span>";
                        $out .= '</span>';
                    }
                } else {
                    $out .= Form::select($field_name.'[]', $popup_vals, $default_val, $params);
                }
                break;
            case 'Name':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';

                if ($default_val == null) {
                    $default_val = $defaultvalue;
                }
                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = $row->$field_name;
                }

                $out .= Form::text($field_name, $default_val, $params);
                break;
            case 'Password':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';

                $out .= Form::password($field_name, $params);
                break;
            case 'Radio':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' : </label><br>';

                // TODO: Remaining - Edit Selected Value not getting selected
                unset($params['placeholder']);
                unset($params['data-rule-maxlength']);

                if ($default_val == null) {
                    $default_val = $defaultvalue;
                }
                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = $row->$field_name;
                }

                if (str_starts_with($popup_vals, '@')) {
                    $popup_vals = self::process_values($popup_vals);
                    $out .= '<div class="radio">';
                    foreach ($popup_vals as $key => $value) {
                        $sel = false;
                        if ($default_val != '' && $default_val == $value) {
                            $sel = true;
                        }
                        $out .= '<label>'.(Form::radio($field_name, $key, $sel)).' '.$value.' </label> &nbsp;&nbsp;';
                    }
                    $out .= '</div>';
                    break;
                } else {
                    if ($popup_vals != '') {
                        $popup_vals = array_values(json_decode($popup_vals));
                    } else {
                        $popup_vals = [];
                    }
                    $out .= '<div class="radio">';
                    foreach ($popup_vals as $value) {
                        $sel = false;
                        if ($default_val != '' && $default_val == $value) {
                            $sel = true;
                        }
                        $out .= '<label>'.(Form::radio($field_name, $value, $sel)).' '.$value.' </label> &nbsp;&nbsp;';
                    }
                    $out .= '</div>';
                    break;
                }
                // no break
            case 'String':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';

                if ($default_val == null) {
                    $default_val = $defaultvalue;
                }
                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = $row->$field_name;
                }
                if (! (isset($params['data-rule-maxlength']) && $params['data-rule-maxlength'] != 0 && $params['data-rule-maxlength'] != '0')) {
                    unset($params['data-rule-maxlength']);
                }
                $out .= Form::text($field_name, $default_val, $params);
                break;
            case 'Taginput':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';

                if (isset($params['data-rule-maxlength'])) {
                    $params['maximumSelectionLength'] = $params['data-rule-maxlength'];
                    unset($params['data-rule-maxlength']);
                }
                if (isset($params['data-rule-minlength'])) {
                    $params['minimumSelectionLength'] = $params['data-rule-minlength'];
                    unset($params['data-rule-minlength']);
                }
                $params['multiple'] = 'true';
                $params['rel'] = 'taginput';
                $params['data-placeholder'] = 'Add multiple '.Str::plural($label);
                unset($params['placeholder']);

                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = json_decode($row->$field_name);
                }

                if ($default_val == null) {
                    $defaultvalue2 = json_decode($defaultvalue);
                    if (is_array($defaultvalue2)) {
                        $default_val = $defaultvalue;
                    } elseif (is_string($defaultvalue)) {
                        if (strpos($defaultvalue, ',') !== false) {
                            $default_val = array_map('trim', explode(',', $defaultvalue));
                        } else {
                            $default_val = [$defaultvalue];
                        }
                    } else {
                        $default_val = [];
                    }
                }
                $default_val = self::process_values($default_val);
                $out .= Form::select($field_name.'[]', $default_val, $default_val, $params);
                break;
            case 'Textarea':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';

                $params['cols'] = 30;
                $params['rows'] = 3;

                if ($default_val == null) {
                    $default_val = $defaultvalue;
                }
                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = $row->$field_name;
                }
                if (! (isset($params['data-rule-maxlength']) && $params['data-rule-maxlength'] != 0 && $params['data-rule-maxlength'] != '0')) {
                    unset($params['data-rule-maxlength']);
                }
                $out .= Form::textarea($field_name, $default_val, $params);
                break;
            case 'TextField':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';

                if ($default_val == null) {
                    $default_val = $defaultvalue;
                }
                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = $row->$field_name;
                }

                if (! (isset($params['data-rule-maxlength']) && $params['data-rule-maxlength'] != 0 && $params['data-rule-maxlength'] != '0')) {
                    unset($params['data-rule-maxlength']);
                }
                $out .= Form::text($field_name, $default_val, $params);
                break;
            case 'URL':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';

                if ($default_val == null) {
                    $default_val = $defaultvalue;
                }
                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = $row->$field_name;
                }

                $params['data-rule-url'] = 'true';
                $out .= Form::text($field_name, $default_val, $params);
                break;
            case 'Location':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';

                if ($default_val == null) {
                    $default_val = $defaultvalue;
                }
                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = $row->$field_name;
                }
                $lat = 0;
                $lng = 0;
                if (strpos($default_val, ',') !== false) {
                    $arr = explode(',', $default_val);
                    $lat = $arr[0];
                    $lng = $arr[1];
                }

                unset($params['data-rule-minlength']);
                unset($params['data-rule-maxlength']);

                $params['class'] = 'form-control lp-lat';
                $field_label = $params['placeholder'];
                $params['placeholder'] = $params['title'] = $field_label.' Latitude';
                $input_lat = Form::text($field_name.'_lat', $lat, $params);

                $params['class'] = 'form-control lp-lng';
                $params['placeholder'] = $params['title'] = $field_label.' Longitude';
                $input_lng = Form::text($field_name.'_lng', $lng, $params);

                $params = ['class' => 'lp-latlng'];
                $input_latlng = Form::hidden($field_name, $default_val, $params);

                $out .= '<div class="location-select">
                            <div class="form-group">
                                <input type="text" class="form-control lp-address" data-toggle="tooltip" data-placement="top" title="Search Location Address" />
                            </div>
                            <div class="lp-map" style="width:100%;height:300px;"></div>
                            <label class="text-danger" style="margin-top:5px;">Drag the location marker on map to correct location.</label>
                            <div class="clearfix">&nbsp;</div>
                            <div class="row m0">
                                <div class="col-md-6 col-lg-6">'.$input_lat.'</div>
                                <div class="col-md-6 col-lg-6">'.$input_lng.'</div>
                            </div>
                            '.$input_latlng.'
                        </div>
                        <style>.pac-container{z-index:1050;}</style>';
                break;
            case 'Color':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';

                if ($default_val == null) {
                    $default_val = $defaultvalue;
                }
                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = $row->$field_name;
                }

                unset($params['data-rule-minlength']);
                unset($params['data-rule-maxlength']);

                $params['class'] = 'form-control colorpicker';
                $params['type'] = 'color';
                $out .= Form::text($field_name, $default_val, $params);
                break;
            case 'Time':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';

                if ($default_val == null) {
                    $default_val = $defaultvalue;
                }
                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = $row->$field_name;
                }

                unset($params['data-rule-minlength']);
                unset($params['data-rule-maxlength']);
                unset($params['type']);

                $params['class'] = 'form-control timepicker';

                if (isset($default_val) && $default_val != '' && is_numeric($default_val) && strlen($default_val) > 3) {
                    $valueHour = intval(substr($default_val, 0, 2));
                    $valueMin = intval(substr($default_val, 2, 2));
                    $valueAMPM = 'AM';
                    if ($valueHour > 12) {
                        $valueHour = $valueHour - 12;
                        $valueAMPM = 'PM';
                    } elseif ($valueHour == 12) {
                        $valueAMPM = 'PM';
                    } elseif ($valueHour == 0) {
                        $valueAMPM = 'AM';
                    }
                    $default_val = ''.$valueHour.':'.$valueMin.' '.$valueAMPM;
                } else {
                    $default_val = date('H:i A');
                }
                $out .= Form::text($field_name, $default_val, $params);
                $out .= '<style>.bootstrap-timepicker-widget{z-index:1050 !important;}</style>';
                break;
            case 'Duration':
                $out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';

                unset($params['data-rule-minlength']);
                unset($params['data-rule-maxlength']);

                $paramsdd = $paramshh = $paramsmm = $params;

                $paramshh['max'] = 23;
                $paramsmm['max'] = 59;
                $paramsdd['placeholder'] = 'Days';
                $paramshh['placeholder'] = 'Hours';
                $paramsmm['placeholder'] = 'Minutes';
                $params['min'] = $paramsdd['min'] = $paramshh['min'] = $paramsmm['min'] = 0;
                $edit_value_hr = $edit_value_min = 0;

                if (isset($params['duration-format']) && ($params['duration-format'] == 'HH:MM' || $params['duration-format'] == 'hh:MM' || $params['duration-format'] == 'HH:mm' || $params['duration-format'] == 'hh:mm')) {
                    unset($paramshh['max']);

                    $days = null;

                    if (isset($row) && isset($row->$field_name)) {
                        $minutes = $row->$field_name;
                        if ($minutes >= 60) {
                            $edit_value_hr = floor($minutes / 60);
                        }
                        if ($minutes > 0) {
                            $hours_data = floor($minutes / 60);
                            $edit_value_min = $minutes - ($hours_data * 60);
                        }
                    }
                } else {
                    $edit_value_date = 0;
                    if (isset($row) && isset($row->$field_name)) {
                        $minutes = $row->$field_name;
                        if ($minutes >= 1440) {
                            $edit_value_date = floor($minutes / 1440);
                        }
                        $minutes = $row->$field_name;
                        if ($minutes >= 60) {
                            $days_data = floor($minutes / 1440);
                            $edit_value_hr = floor(($minutes - $days_data * 1440) / 60);
                        }
                        if ($minutes > 0) {
                            $days_data = floor($minutes / 1440);
                            $hours_data = floor(($minutes - $days_data * 1440) / 60);
                            $edit_value_min = $minutes - ($days_data * 1440) - ($hours_data * 60);
                        }
                    }
                    $days = '<span class="input-group-addon" data-toggle="tooltip" title="Days"><b>D</b></span>'.Form::number($field_name.'_days', $edit_value_date, $paramsdd);
                }

                $hours = Form::number($field_name.'_hours', $edit_value_hr, $paramshh);
                $minutes = Form::number($field_name.'_minutes', $edit_value_min, $paramsmm);

                if ($default_val == null) {
                    $default_val = 0;
                }
                // Override the edit value
                if (isset($row) && isset($row->$field_name)) {
                    $default_val = $row->$field_name;
                }

                // $params['min'] = "0"; // Required for Non-negative numbers
                $out .= '<div class="input-group duration">'.
                    Form::hidden($field_name, $default_val).
                    $days
                    .'<span class="input-group-addon" data-toggle="tooltip" title="Hours"><b>H</b></span>'.$hours
                    .'<span class="input-group-addon" data-toggle="tooltip" title="Minutes"><b>M</b></span>'.$minutes
                    .'</div>';
                break;
        }

        return $out;
    }

    /**
     * Processes the populated values for Multiselect / Taginput / Dropdown
     * get data from module / table whichever is found if starts with '@'.
     **/
    // $values = LAFormMaker::process_values($data);
    public static function process_values($json)
    {
        $out = [];
        // Check if populated values are from Module or Database Table
        if (is_string($json) && str_starts_with($json, '@')) {
            // Get Module / Table Name
            $json = str_ireplace('@', '', $json);
            $table_name = strtolower(Str::plural($json));

            // Search Module
            $module = LAModule::getByTable($table_name);
            if (isset($module->id)) {
                $out = LAModule::getDDArray($module->name);
            } else {
                // Search Table if no module found
                if (Schema::hasTable($table_name)) {
                    if (file_exists(resource_path('app/Models/'.ucfirst(Str::singular($table_name).'.php')))) {
                        $model = 'App\\Models\\'.ucfirst(Str::singular($table_name));
                        $result = $model::all();
                    } else {
                        $result = DB::table($table_name)->get();
                    }
                    // find view column name
                    $view_col = '';
                    // Check if atleast one record exists
                    if (isset($result[0])) {
                        $view_col_test_1 = 'name';
                        $view_col_test_2 = 'title';
                        if (isset($result[0]->$view_col_test_1)) {
                            // Check whether view column name == "name"
                            $view_col = $view_col_test_1;
                        } elseif (isset($result[0]->$view_col_test_2)) {
                            // Check whether view column name == "title"
                            $view_col = $view_col_test_2;
                        } else {
                            // retrieve the second column name which comes after "id"
                            $arr2 = $result[0]->toArray();
                            $arr2 = array_keys($arr2);
                            $view_col = $arr2[1];
                            // if second column not exists
                            if (! isset($result[0]->$view_col)) {
                                $view_col = '';
                            }
                        }
                        // If view column name found successfully through all above efforts
                        if ($view_col != '') {
                            // retrieve rows of table
                            foreach ($result as $row) {
                                $out[$row->id] = $row->$view_col;
                            }
                        } else {
                            // Failed to find view column name
                        }
                    } else {
                        // Skipped efforts to detect view column name
                    }
                } elseif (Schema::hasTable($json)) {
                    // $array = \DB::table($table_name)->get();
                }
            }
        } elseif (is_string($json)) {
            $array = json_decode($json);
            if (is_array($array)) {
                foreach ($array as $value) {
                    $out[$value] = $value;
                }
            } else {
                // TODO: Check posibility of comma based pop values.
            }
        } elseif (is_array($json)) {
            foreach ($json as $value) {
                $out[$value] = $value;
            }
        }

        return $out;
    }

    /**
     * Display field is CRUDs View show.blade.php with Label.
     *
     * Uses blade syntax @la_display('name')
     *
     * @param $module Module Object
     * @param $field_name Field Name for which display has be created
     * @param bool $display_row Whether to display bootstrap row with styles or just return Formatted Value
     * @return string This return html string with field display with Label
     */
    public static function display($module, $field_name, $display_row = true, $params = [])
    {
        // Check Field View Access
        if (LAModule::hasFieldAccess($module->id, $module->fields[$field_name]['id'], $access_type = 'view')) {
            $fieldObj = $module->fields[$field_name];
            $label = $module->fields[$field_name]['label'];
            $field_type = $module->fields[$field_name]['field_type'];
            $field_type = LAModuleFieldType::find($field_type);

            $row = null;
            if (isset($module->row)) {
                $row = $module->row;
            }
            if ($display_row) {
                $out = '<div class="form-group">';
                $out .= '<label for="'.$field_name.'" class="col-md-4 col-sm-6 col-xs-6">'.$label.' :</label>';
            }
            $value = $row->$field_name;

            switch ($field_type->name) {
                case 'Address':
                    if ($value != '') {
                        $value = $value.'<a target="_blank" class="pull-right btn btn-xs btn-primary btn-circle" href="https://maps.google.com/?q='.$value.'" data-toggle="tooltip" data-placement="left" title="Check location on Map"><i class="fa fa-map-marker"></i></a>';
                    }
                    break;
                case 'Checkbox':
                    if ($value == 0) {
                        $value = "<div class='label label-danger'>False</div>";
                    } else {
                        $value = "<div class='label label-success'>True</div>";
                    }
                    break;
                case 'Currency':

                    break;
                case 'Date':
                    if ($value == null) {
                        $value = 'Not Available';
                    } else {
                        $dt = strtotime($value);
                        $value = date('d M Y', $dt);
                    }
                    break;
                case 'Datetime':
                    if ($value == null) {
                        $value = 'Not Available';
                    } else {
                        $dt = strtotime($value);
                        $value = date('d M Y, h:i A', $dt);
                    }
                    break;
                case 'Decimal':

                    break;
                case 'Dropdown':
                    $values = self::process_values($fieldObj['popup_vals']);
                    if (str_starts_with($fieldObj['popup_vals'], '@')) {
                        if ($value != 0) {
                            $moduleVal = LAModule::getByTable(str_replace('@', '', $fieldObj['popup_vals']));
                            if (isset($moduleVal->id)) {
                                $value = "<a href='".url(config('laraadmin.adminRoute').'/'.$moduleVal->name_db.'/'.$value)."' class='label label-primary'>".$values[$value].'</a> ';
                            } else {
                                $value = "<a class='label label-primary'>".$values[$value].'</a> ';
                            }
                        } else {
                            $value = 'None';
                        }
                    }
                    break;
                case 'Email':
                    $value = '<a href="mailto:'.$value.'">'.$value.'</a>';
                    break;
                case 'File':
                    if ($value != 0 && $value != '0') {
                        $upload = \App\Models\Upload::find($value);
                        if (isset($upload->id)) {
                            $value = '<a class="preview" target="_blank" href="'.url('files/'.$upload->hash.DIRECTORY_SEPARATOR.$upload->name).'">
                            <span class="fa-stack fa-lg"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-file-o fa-stack-1x fa-inverse"></i></span> '.$upload->name.'</a>';
                        } else {
                            $value = 'Uploaded file not found.';
                        }
                    } else {
                        $value = 'No file';
                    }
                    break;
                case 'Files':
                    if ($value != '' && $value != '[]' && $value != 'null' && str_starts_with($value, '[')) {
                        $uploads = json_decode($value);
                        $uploads_html = '';

                        foreach ($uploads as $uploadId) {
                            $upload = \App\Models\Upload::find($uploadId);
                            if (isset($upload->id)) {
                                $uploadIds[] = $upload->id;
                                $fileImage = '';
                                if (in_array($upload->extension, ['jpg', 'png', 'gif', 'jpeg'])) {
                                    $fileImage = "<img src='".url('files/'.$upload->hash.DIRECTORY_SEPARATOR.$upload->name.'?s=90')."'>";
                                } else {
                                    $fileImage = "<i class='fa fa-file-o'></i>";
                                }
                                // $uploadImages .= "<a class='uploaded_file2' upload_id='".$upload->id."' target='_blank' href='".url("files/".$upload->hash.DIRECTORY_SEPARATOR.$upload->name)."'>".$fileImage."<i title='Remove File' class='fa fa-times'></i></a>";
                                $uploads_html .= '<a class="preview" target="_blank" href="'.url('files/'.$upload->hash.DIRECTORY_SEPARATOR.$upload->name).'" data-toggle="tooltip" data-placement="top" data-container="body" style="display:inline-block;margin-right:5px;" title="'.$upload->name.'">
                                        '.$fileImage.'</a>';
                            }
                        }
                        $value = $uploads_html;
                    } else {
                        $value = 'No files found.';
                    }
                    break;
                case 'Float':

                    break;
                case 'HTML':
                    break;
                case 'Image':
                    if ($value != 0 && $value != '0') {
                        $upload = \App\Models\Upload::find($value);
                        if (isset($upload->id)) {
                            $value = '<a class="preview" target="_blank" href="'.url('files/'.$upload->hash.DIRECTORY_SEPARATOR.$upload->name).'"><img src="'.url('files/'.$upload->hash.DIRECTORY_SEPARATOR.$upload->name.'?s=150').'"></a>';
                        } else {
                            $value = 'Uploaded image not found.';
                        }
                    } else {
                        $value = 'No Image';
                    }
                    break;
                case 'Integer':

                    break;
                case 'JSON':
                    $value = json_encode($value, JSON_PRETTY_PRINT);
                    break;
                case 'List':
                    $list = json_decode($value);
                    $value = '';
                    if (count($list) > 0) {
                        $value .= "<ol class='pl10'>";
                        foreach ($list as $item) {
                            $value .= '<li>'.$item.'</li>';
                        }
                        $value .= '</ol>';
                    }
                    break;
                case 'Checklist':
                    $checklists = json_decode($value);

                    $min = '';
                    $max = '';
                    if ($module->fields[$field_name]['minlength'] > 0) {
                        $min = "data-rule-mincount='".$module->fields[$field_name]['minlength']."'";
                    }
                    if ($module->fields[$field_name]['maxlength'] > 0) {
                        $max = "data-rule-maxcount='".$module->fields[$field_name]['maxlength']."'";
                    }

                    $data = '';
                    if (count($checklists) > 0) {
                        if (isset($params['data-editable']) && ($params['data-editable'] == 'true')) {
                            $hidden_input = Form::hidden($field_name, $value, ['module_name_db' => $module->name_db, 'field_name' => $field_name, 'row_id' => $module->row->id]);
                            $readOnly = '';
                            $add_btn = "<span class='col-md-1 col-sm-4 col-xs-6 m0 p0'><a class='btn btn-success btn-xs pull-right btn-checklist' ".$min.$max."><i class='fa fa-plus'></i></a></span>";
                            $editable_input = '<input type="text" class="edit form-control" style="display:none"/></span>';
                            $class_display = 'display';
                            $btn_delete = '<i class="fa fa-trash-o btn_checklist_remove"></i>';
                        } else {
                            $hidden_input = '';
                            $readOnly = ''; // 'disabled';
                            $add_btn = '';
                            $editable_input = '';
                            $class_display = '';
                            $btn_delete = '';
                        }

                        $data .= '<span class="row m0 checklist">'.
                            $hidden_input.
                            '<span class="col-md-11 col-sm-8 col-xs-6 p0">'.
                            '<ul class="todo-list">';
                        foreach ($checklists as $checklist) {
                            $data .= '<li class="'.($checklist->checked == 'true' ? 'done' : '').'">'.
                                '<span class="handle">
                                        <i class="fa fa-ellipsis-v"></i>
                                        <i class="fa fa-ellipsis-v"></i>
                                    </span>'.
                                '<span class="value_checklist">'.
                                '<input type="checkbox" '.$readOnly.' row_id="'.$row->id.'"  module_id="'.$module->id.'" module_field_id="'.$module->fields[$field_name]['id'].'" title="'.$checklist->title.'" value="'.$checklist->checked.'" '.($checklist->checked == 'true' ? 'checked' : '').' name="checked" style="position:relative;top:1px;margin:0 2px">'.
                                '<span style="display:inline-block;"><span class="text '.$class_display.' checklist_title">'.$checklist->title.'</span>'.
                                $editable_input.
                                '</span>'.
                                '<div class="tools">'.
                                $btn_delete.
                                '</div>'.
                                '</li>';
                        }
                        $data .= '</ul></span>';
                        $data .= $add_btn;
                        $data .= '</span>';
                    } else {
                        if (isset($params['data-editable']) && ($params['data-editable'] == 'true')) {
                            $data .= '<span class="row m0 checklist">'.
                                Form::hidden($field_name, $value, ['module_name_db' => $module->name_db, 'field_name' => $field_name, 'row_id' => $module->row->id]).
                                '<span class="col-md-11 col-sm-8 col-xs-6 p0">'.
                                '<ul class="todo-list">';
                            $data .= '</ul></span>';
                            $data .= "<span class='col-md-1 col-sm-4 col-xs-6 m0 p0'><a class='btn btn-success btn-xs pull-right btn-checklist' ".$min.$max."><i class='fa fa-plus'></i></a></span>";
                            $data .= '</span>';
                        }
                    }
                    $value = $data;
                    break;
                case 'Mobile':
                    $value = '<a target="_blank" href="tel:'.$value.'">'.$value.'</a>';
                    break;
                case 'Multiselect':
                    $valueOut = '';
                    $values = self::process_values($fieldObj['popup_vals']);
                    if (count($values)) {
                        if (str_starts_with($fieldObj['popup_vals'], '@')) {
                            $moduleVal = LAModule::getByTable(str_replace('@', '', $fieldObj['popup_vals']));
                            $valueSel = json_decode($value);
                            foreach ($values as $key => $val) {
                                if (in_array($key, $valueSel)) {
                                    $module_link = '';
                                    if (isset($moduleVal->id)) {
                                        $module_link = "href='".url(config('laraadmin.adminRoute').'/'.$moduleVal->name_db.'/'.$key)."'";
                                    }
                                    $valueOut .= "<a $module_link class='label label-primary'>".$val.'</a> ';
                                }
                            }
                        } else {
                            $valueSel = json_decode($value);
                            foreach ($values as $key => $val) {
                                if (in_array($key, $valueSel)) {
                                    $valueOut .= "<span class='label label-primary'>".$val.'</span> ';
                                }
                            }
                        }
                    }
                    $value = $valueOut;
                    break;
                case 'Name':

                    break;
                case 'Password':
                    $value = '<a href="#" data-toggle="tooltip" data-placement="top" data-container="body" title="Cannot be declassified !!!">********</a>';
                    break;
                case 'Radio':

                    break;
                case 'String':

                    break;
                case 'Taginput':
                    $valueOut = '';
                    $values = self::process_values($fieldObj['popup_vals']);
                    if (count($values)) {
                        if (str_starts_with($fieldObj['popup_vals'], '@')) {
                            $moduleVal = LAModule::getByTable(str_replace('@', '', $fieldObj['popup_vals']));
                            $valueSel = json_decode($value);
                            foreach ($values as $key => $val) {
                                if (in_array($key, $valueSel)) {
                                    $valueOut .= "<a href='".url(config('laraadmin.adminRoute').'/'.$moduleVal->name_db.'/'.$key)."' class='label label-primary'>".$val.'</a> ';
                                }
                            }
                        } else {
                            $valueSel = json_decode($value);
                            foreach ($valueSel as $key => $val) {
                                $valueOut .= "<span class='label label-primary'>".$val.'</span> ';
                            }
                        }
                    } else {
                        $valueSel = json_decode($value);
                        foreach ($valueSel as $key => $val) {
                            $valueOut .= "<span class='label label-primary'>".$val.'</span> ';
                        }
                    }
                    $value = $valueOut;
                    break;
                case 'Textarea':

                    break;
                case 'TextField':

                    break;
                case 'URL':
                    $value = '<a target="_blank" href="'.$value.'">'.$value.'</a>';
                    break;
                case 'Location':
                    $value = '<a target="_blank" href="https://www.google.co.in/maps/search/'.$value.'">'.$value.'</a>';
                    break;
                case 'Color':
                    if ($value != '') {
                        $value = "<div class='label' style='background-color:".$value.";font-size:14px;padding:1px 15px 3px 15px;'>".$value.'</div>';
                    } else {
                        $value = 'None';
                    }
                    break;
                case 'Time':
                    if (isset($value) && $value != '' && is_numeric($value) && strlen($value) > 2) {
                        $valueHour = intval(substr($value, 0, 2));
                        $valueMin = intval(substr($value, 2, 2));
                        $valueAMPM = 'AM';
                        if ($valueHour > 12) {
                            $valueHour = $valueHour - 12;
                            $valueAMPM = 'PM';
                        }
                        $value = ''.$valueHour.':'.$valueMin.' '.$valueAMPM;
                    } else {
                        $value = 'None';
                    }
                    break;
                case 'Duration':
                    if (isset($value) && $value != '' && is_numeric($value)) {
                        $data = '';
                        $minutes = $value;
                        if (isset($params['duration-format']) && ($params['duration-format'] == 'HH:MM' || $params['duration-format'] == 'hh:MM' || $params['duration-format'] == 'HH:mm' || $params['duration-format'] == 'hh:mm')) {
                            $hour = floor($minutes / 60);
                            $min = $minutes - ($hour * 60);
                            $data = "<span class='label bg-blue'>".$hour.' Hr '.$min.' Min</span>';
                        } else {
                            if ($minutes == 1440) {
                                $day = floor($minutes / 1440);
                                $data = "<span class='label bg-blue'>".$day.' Day</span>';
                            } elseif ($minutes > 1440) {
                                $day = floor($minutes / 1440);
                                $hour = floor(($minutes - $day * 1440) / 60);
                                $min = $minutes - ($day * 1440) - ($hour * 60);
                                $data = "<span class='label bg-blue'>".$day.' Day '.$hour.' Hr '.$min.' Min</span>';
                            } else {
                                $hour = floor($minutes / 60);
                                $min = $minutes - ($hour * 60);
                                $data = "<span class='label bg-blue'>".$hour.' Hr '.$min.' Min</span>';
                            }
                        }

                        $value = $data;
                    } else {
                        $value = 'None';
                    }
                    break;
            }
            if ($display_row) {
                $out .= '<div class="col-md-8 col-sm-6 col-xs-6 fvalue">'.$value.'</div>';
                $out .= '</div>';
            } else {
                $out .= $value;
            }

            return $out;
        } else {
            return '';
        }
    }

    /**
     * Print complete add/edit form for Module.
     *
     * Uses blade syntax @la_form($employee_module_object)
     *
     * @param $module Module for which add/edit form has to be created.
     * @param array $fields List of Module Field Names to customize Selective Fields for Form
     * @return string returns HTML for complete Module Add/Edit Form
     */
    public static function form($module, $fields = [])
    {
        if (count($fields) == 0) {
            $fields = array_keys($module->fields);
        }
        $out = '';
        foreach ($fields as $field) {
            // Use input method of this class to generate all Module fields
            $out .= self::input($module, $field);
        }

        return $out;
    }

    /**
     * Check Whether User has Module Access
     * Work like @if blade directive of Laravel.
     *
     * @param $module_id Module Id for which Access will be checked
     * @param string $access_type Access type like - view / create / edit / delete
     * @param int $user_id User id for which access is checked. By default it takes logged-in user
     * @return bool return whether access for this Module is true / false
     */
    public static function la_access($module_id, $access_type = 'view', $user_id = 0)
    {
        // Check Module access by hasAccess method
        return LAModule::hasAccess($module_id, $access_type, $user_id);
    }

    /**
     * Check Whether User has Module Field Access.
     *
     * Work like @if blade directive of Laravel
     *
     * @param $module_id Module Id for which Access will be checked
     * @param $field_id Field Id / Name for which Access will be checked
     * @param string $access_type Field Access type like - view / write
     * @param int $user_id User id for which access is checked. By default it takes logged-in user
     * @return bool return whether access for this Module Field is true / false
     */
    public static function la_field_access($module_id, $field_id, $access_type = 'view', $user_id = 0)
    {
        // Check Module Field access by hasFieldAccess method
        return LAModule::hasFieldAccess($module_id, $field_id, $access_type, $user_id);
    }
}
