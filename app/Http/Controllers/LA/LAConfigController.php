<?php
/***
 * Controller generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Http\Controllers\LA;

use App\Helpers\LAHelper;
use App\Http\Controllers\Controller;
use App\Models\LAConfig;
use App\Models\LAModuleFieldType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LAConfigController extends Controller
{
    public $skin_array = [
        'Blue Skin' => 'skin-blue',
        'Black Skin' => 'skin-black',
        'Purple Skin' => 'skin-purple',
        'Yellow Skin' => 'skin-yellow',
        'Red Skin' => 'skin-red',
        'Green Skin' => 'skin-green',
        'Blue Light Skin' => 'skin-blue-light',
        'Black Light Skin' => 'skin-black-light',
        'Purple Light Skin' => 'skin-purple-light',
        'Green Light Skin' => 'skin-green-light',
        'Red Light Skin' => 'skin-red-light',
        'Yellow Light Skin' => 'skin-yellow-light'

    ];

    public $layout_array = [
        'Fixed Layout' => 'fixed',
        'Mini Sidebar Layout' => 'sidebar-mini',
        'Fixed Mini Sidebar Layout' => 'fixed-sidebar-mini',
        'Boxed Layout' => 'layout-boxed',
        'Top Navigation Layout' => 'layout-top-nav',
        'Sidebar Collapse Layout' => 'sidebar-collapse',

    ];

    /**
     * Display a listing of configurations.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $configs = LAConfig::getAll();
        $ftypes = LAModuleFieldType::getFTypes2();
        $tables = LAHelper::getDBTables([]);

        $sections = LAConfig::select('section')->groupBy('section')->get();
        $sections_arr = [];
        foreach ($sections as $section) {
            $keys = LAConfig::select('key')->where('section', $section->section)->get();
            $section->keys = $keys;
            $sections_arr[] = $section;
        }

        return View('la.la_configs.index', [
            'ftypes' => $ftypes,
            'tables' => $tables,
            'sections' => $sections
        ]);
    }

    /**
     * Store a newly created config in database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|unique:la_configs|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        $config = LAConfig::where('key', $request->key)->first();
        if (! isset($config->id)) {
            if (isset($request->required)) {
                $required = true;
            } else {
                $required = false;
            }
            if ($request->minlength != '') {
                $minlength = $request->minlength;
            } else {
                $minlength = 0;
            }
            if ($request->maxlength != '') {
                $maxlength = $request->maxlength;
            } else {
                $maxlength = 0;
            }

            // Field type : Dropdown
            if ($request->field_type == 7 || $request->field_type == 15 || $request->field_type == 18 || $request->field_type == 20) {
                if ($request->popup_value_type == 'table') {
                    $popup_vals = '@'.$request->popup_vals_table;
                } elseif ($request->popup_value_type == 'list') {
                    $popup_vals = [];
                    $json = $request->popup_vals_list;
                    if (is_string($json)) {
                        $array = json_decode($json);
                        if (is_array($json)) {
                            foreach ($json as $json2) {
                                $popup_vals[] = $json2;
                            }
                        } else {
                            // TODO: Check posibility of comma based pop values.
                        }
                    } elseif (is_array($json)) {
                        foreach ($json as $json2) {
                            $popup_vals[] = $json2;
                        }
                    }
                    $popup_vals = json_encode($popup_vals);
                }
            } else {
                $popup_vals = '';
            }

            LAConfig::create([
                'label' => $request->label,
                'key' => $request->key,
                'section' => ucfirst($request->section),
                'value' => null,
                'field_type' => $request->field_type,
                'minlength' => $minlength,
                'maxlength' => $maxlength,
                'required' => $required,
                'popup_vals' => $popup_vals
            ]);

            return redirect(config('laraadmin.adminRoute').'/la_configs');
        } else {
            return redirect(config('laraadmin.adminRoute').'/la_configs');
        }
    }

    /**
     * Update the configurations in database.
     *
     * @param Request $request
     * @param $id Field's ID to be Updated
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $section)
    {
        $configs = LAConfig::where('section', $section)->get();
        $ftypes = LAModuleFieldType::getFTypes2();

        foreach ($configs as $config) {
            $key = $config->key;

            switch($ftypes[$config['field_type']]) {
                case 'Checkbox':
                    if (isset($request->$key)) {
                        $value = true;
                    } elseif (isset($request->{$key.'_hidden'})) {
                        $value = false;
                    }
                    break;
                case 'Date':
                    $null_date = $request->{'null_date_'.$key};
                    if (isset($null_date) && $null_date == 'true') {
                        $value = null;
                    } elseif ($request->$key != '') {
                        $date = $request->$key;
                        $d2 = date_parse_from_format('d/m/Y', $date);
                        $value = date('Y-m-d', strtotime($d2['year'].'-'.$d2['month'].'-'.$d2['day']));
                    } else {
                        $value = date('Y-m-d');
                    }
                    break;
                case 'Datetime':
                    $null_date = $request->{'null_date_'.$key};
                    if (isset($null_date) && $null_date == 'true') {
                        $value = null;
                    } elseif ($request->$key != '') {
                        $date = $request->$key;
                        $d2 = date_parse_from_format('d/m/Y h:i A', $date);
                        $value = date('Y-m-d H:i:s', strtotime($d2['year'].'-'.$d2['month'].'-'.$d2['day'].' '.substr($date, 11)));
                    } else {
                        $value = date('Y-m-d H:i:s');
                    }
                    break;
                case 'Dropdown':
                    if ($request->$key == 0) {
                        if (str_starts_with($config['popup_vals'], '@')) {
                            $value = DB::raw('NULL');
                        } elseif (str_starts_with($config['popup_vals'], '[')) {
                            $value = '';
                        }
                    }
                    $value = $request->$key;
                    break;
                case 'Multiselect':
                    // TODO: Bug fix
                    $value = json_encode($request->$key);
                    break;
                case 'Password':
                    $value = bcrypt($request->$key);
                    break;
                case 'Taginput':
                    // TODO: Bug fix
                    $value = json_encode($request->$key);
                    break;
                case 'Files':
                    $files = json_decode($request->$key);
                    $files2 = [];
                    foreach ($files as $file) {
                        $files2[] = ''.$file;
                    }
                    $value = json_encode($files2);
                    break;
                case 'Time':
                    $time = $request->$key;
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
                        $value = $time24;
                    }
                    break;
                default:
                    $value = $request->$key;
                    break;
            }
            LAConfig::where('key', $key)->update(['value' => $value]);
        }

        return redirect(config('laraadmin.adminRoute').'/la_configs');
    }

    /**
     * Remove the specified config from Database.
     *
     * @param  int  $key
     * @return \Illuminate\Http\Response
     */
    public function destroy($key)
    {
        $config = LAConfig::where('key', $key)->first();
        if (isset($config->id)) {
            $config->delete();
        }

        return redirect(config('laraadmin.adminRoute').'/la_configs');
    }

    /**
     * Remove the specified config from Database.
     *
     * @param  int  $key
     * @return \Illuminate\Http\Response
     */
    public function ajax_destroy($key)
    {
        $config = LAConfig::where('key', $key)->first();
        if (isset($config->id)) {
            $config->delete();
        }

        return redirect(config('laraadmin.adminRoute').'/la_configs');
    }

    /**
     * Edit specified config.
     *
     * @param  int  $key
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $config = LAConfig::find($id);
        if (isset($config->id)) {
            $ftypes = LAModuleFieldType::getFTypes2();
            $tables = LAHelper::getDBTables([]);
            $sections = LAConfig::select('section')->groupBy('section')->get();

            return View('la.la_configs.edit', [
                'ftypes' => $ftypes,
                'tables' => $tables,
                'config' => $config,
                'sections' => $sections
            ]);
        } else {
            return view('errors.404', [
                'record_id' => $id,
                'record_name' => 'Configuration',
            ]);
        }
    }

    /**
     * Update information / meta about Configuration.
     *
     * @param  int  $key
     * @return \Illuminate\Http\Response
     */
    public function edit_save(Request $request, $id)
    {
        $config = LAConfig::find($id);
        if (isset($config->id)) {
            $value = $request->value;
            if ($request->required == 'required') {
                $required = 1;
            } else {
                $required = 0;
            }

            // popup values for Field type : Dropdown
            if ($request->field_type == 7 || $request->field_type == 15 || $request->field_type == 18 || $request->field_type == 20) {
                if ($request->popup_value_type == 'table') {
                    $popup_vals = '@'.$request->popup_vals_table;
                } elseif ($request->popup_value_type == 'list') {
                    $popup_vals = [];
                    $json = $request->popup_vals_list;
                    if (is_string($json)) {
                        $array = json_decode($json);
                        if (is_array($json)) {
                            foreach ($json as $json2) {
                                $popup_vals[] = $json2;
                            }
                        } else {
                            // TODO: Check posibility of comma based pop values.
                        }
                    } elseif (is_array($json)) {
                        foreach ($json as $json2) {
                            $popup_vals[] = $json2;
                        }
                    }
                    $popup_vals = json_encode($popup_vals);
                }
            } else {
                $popup_vals = '';
            }

            if (isset($request->minlength) && $request->minlength != '') {
                $minlength = $request->minlength;
            } else {
                $minlength = 0;
            }
            if (isset($request->maxlength) && $request->maxlength != '') {
                $maxlength = $request->maxlength;
            } else {
                $maxlength = 0;
            }

            $config->label = $request->label;
            $config->key = $request->key;
            $config->section = $request->section;
            $config->field_type = $request->field_type;
            $config->minlength = $minlength;
            $config->maxlength = $maxlength;
            $config->required = $required;
            $config->popup_vals = $popup_vals;
            $config->save();

            return redirect(config('laraadmin.adminRoute').'/la_configs#tab-'.$request->section);
        } else {
            return view('errors.404', [
                'record_id' => $id,
                'record_name' => 'Configuration',
            ]);
        }
    }
}
