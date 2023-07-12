<?php
/***
 * Code generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Http\Controllers\LA;

use App\Helpers\CodeGenerator;
use App\Helpers\LAFormMaker;
use App\Helpers\LAHelper;
use App\Http\Controllers\Controller;
use App\Models\LAMenu;
use App\Models\LAModule;
use App\Models\LAModuleField;
use App\Models\LAModuleFieldType;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Schema;

/***
 * LaraAdmin Module Controller
 */
class LAModuleController extends Controller
{
    public function __construct()
    {
        // for authentication (optional)
        // $this->middleware('auth');
    }

    /**
     * Display a listing of the Module.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $modules = LAModule::all();
        $tables = LAHelper::getDBTables([]);

        return View('la.la_modules.index', [
            'modules' => $modules,
            'tables' => $tables
        ]);
    }

    /**
     * Store a newly created Module.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $module_id = LAModule::generateBase($request->name, $request->icon);

        // Create Migration
        $migrationFileName = CodeGenerator::generateMigration($request->name);

        // Migration Entry into Database
        DB::insert('insert into migrations (migration, batch) values (?, ?)', [$migrationFileName, 1]);

        return redirect()->route(config('laraadmin.adminRoute').'.la_modules.show', [$module_id]);
    }

    /**
     * Display the specified Module.
     *
     * @param $id Module ID
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $ftypes = LAModuleFieldType::getFTypes2();
        $module = LAModule::find($id);
        $module = LAModule::get($module->name);

        $tables = LAHelper::getDBTables([]);
        $modules = LAHelper::getModuleNames([]);

        // Get Module Access for all roles
        $roles = LAModule::getRoleAccess($id);

        return view('la.la_modules.show', [
            'no_header' => true,
            'no_padding' => 'no-padding',
            'ftypes' => $ftypes,
            'tables' => $tables,
            'modules' => $modules,
            'roles' => $roles
        ])->with('module', $module);
    }

    /**
     * Update the specified Module.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function update(Request $request)
    {
        $module = LAModule::find($request->id);
        if (isset($module->id)) {
            $module->label = ucfirst($request->label);
            $module->fa_icon = $request->icon;
            $module->save();

            $menu = LAMenu::where('url', strtolower($module->name))->where('type', 'module')->first();
            if (isset($menu->id)) {
                $menu->name = ucfirst($request->label);
                $menu->icon = $request->icon;
                $menu->save();
            }
        }
    }

    /**
     * Remove the specified Module Including Module Schema, DB Table,
     * Menu, Model, Model fields, Controller, Views directory, routes, Observers, Language file and modifies the migration file.
     *
     * @param $id Module ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // Delete Module
        $data = LAModule::deleteModule($id, true);

        return redirect()->route(config('laraadmin.adminRoute').'.la_modules.index', ['modules' => $data['modules'], 'msg' => $data['msg'], 'err_module' => $data['err_module']]);
    }

    /**
     * Generate Modules CRUDs Views, Controller, Model, Routes, Menu and Set Default Full Access for Super Admin.
     *
     * @param $module_id Module ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate_crud($module_id)
    {
        $module = LAModule::find($module_id);
        $module = LAModule::get($module->name);

        $config = CodeGenerator::generateConfig($module->name, $module->fa_icon);

        CodeGenerator::createController($config);
        CodeGenerator::createModel($config);
        CodeGenerator::createObserver($config);
        CodeGenerator::appendObservers($config);
        CodeGenerator::createViews($config);
        CodeGenerator::appendRoutes($config);
        CodeGenerator::addMenu($config);
        CodeGenerator::createLanguageFile($config);

        // Set Module Generated = True
        $module = LAModule::find($module_id);
        $module->is_gen = '1';
        $module->save();

        // Give Default Full Access to Super Admin
        $role = Role::where('name', 'SUPER_ADMIN')->first();
        LAModule::setDefaultRoleAccess($module->id, $role->id, 'full');

        return response()->json([
            'status' => 'success'
        ]);
    }

    /**
     * Generate Module Migrations.
     *
     * @param $module_id Module ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate_migr($module_id)
    {
        $module = LAModule::find($module_id);
        $module = LAModule::get($module->name);

        CodeGenerator::generateMigration($module->name_db, true);

        return response()->json([
            'status' => 'success'
        ]);
    }

    /**
     * Generate Modules Migrations and CRUDs Views, Controller, Model, Routes, Menu and Set Default Full Access
     * for Super Admin.
     *
     * @param $module_id Module ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate_migr_crud($module_id)
    {
        $module = LAModule::find($module_id);
        $module = LAModule::get($module->name);

        // Generate Migration
        CodeGenerator::generateMigration($module->name_db, true);

        // Create Config for Code Generation
        $config = CodeGenerator::generateConfig($module->name, $module->fa_icon);

        // Generate CRUD
        CodeGenerator::createController($config);
        CodeGenerator::createModel($config);
        CodeGenerator::createObserver($config);
        CodeGenerator::appendObservers($config);
        CodeGenerator::createViews($config);
        CodeGenerator::appendRoutes($config);
        CodeGenerator::addMenu($config);
        CodeGenerator::createLanguageFile($config);

        // Set Module Generated = True
        $module = LAModule::find($module_id);
        $module->is_gen = '1';
        $module->save();

        // Give Default Full Access to Super Admin
        $role = Role::where('name', 'SUPER_ADMIN')->first();
        LAModule::setDefaultRoleAccess($module->id, $role->id, 'full');

        // Give Default Full Access to all
        $menu = LAMenu::where('name', $config->moduleName)->first();
        $roles = Role::all();
        foreach ($roles as $role) {
            // Set Full Access For all - Menu
            $menu->roles()->attach($role->id);
            // Set Full Access For all Role
            LAModule::setDefaultRoleAccess($module->id, $role->id, 'full');
        }

        return response()->json([
            'status' => 'success'
        ]);
    }

    /**
     * Updates Modules all files except routes.
     *
     * @param $module_id Module ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate_update($module_id)
    {
        $module = LAModule::find($module_id);
        $module = LAModule::get($module->name);

        // Generate Migration
        CodeGenerator::generateMigration($module->name_db, true);

        // Create Config for Code Generation
        $config = CodeGenerator::generateConfig($module->name, $module->fa_icon);

        // Generate CRUD
        CodeGenerator::createController($config);
        CodeGenerator::createModel($config);
        CodeGenerator::createObserver($config);
        CodeGenerator::createViews($config);
        CodeGenerator::createLanguageFile($config);

        // Set Module Generated = True
        $module = LAModule::find($module_id);
        $module->is_gen = '1';
        $module->save();

        return response()->json([
            'status' => 'success'
        ]);
    }

    /**
     * Set the Modules view_column_name.
     *
     * @param $module_id Module ID
     * @param $column_name Module's View Column Name
     * @return \Illuminate\Http\RedirectResponse
     */
    public function set_view_col($module_id, $column_name)
    {
        $module = LAModule::find($module_id);
        $module->view_col = $column_name;
        $module->save();

        return redirect()->route(config('laraadmin.adminRoute').'.la_modules.show', [$module_id]);
    }

    /**
     * Save Module-Role Permissions including Module Fields.
     *
     * @param Request $request
     * @param $id Module ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save_role_module_permissions(Request $request, $id)
    {
        $module = LAModule::find($id);
        $module = LAModule::get($module->name);

        $tables = LAHelper::getDBTables([]);
        $modules = LAHelper::getModuleNames([]);
        $roles = Role::all();

        $now = date('Y-m-d H:i:s');

        foreach ($roles as $role) {
            /* =============== role_la_module_fields =============== */

            foreach ($module->fields as $field) {
                $field_name = $field['colname'].'_'.$role->id;
                $field_value = $request->$field_name;
                if ($field_value == 0) {
                    $access = 'invisible';
                } elseif ($field_value == 1) {
                    $access = 'readonly';
                } elseif ($field_value == 2) {
                    $access = 'write';
                }

                $query = DB::table('role_la_module_fields')->where('role_id', $role->id)->where('field_id', $field['id']);
                if ($query->count() == 0) {
                    DB::insert('insert into role_la_module_fields (role_id, field_id, access, created_at, updated_at) values (?, ?, ?, ?, ?)', [$role->id, $field['id'], $access, $now, $now]);
                } else {
                    DB::table('role_la_module_fields')->where('role_id', $role->id)->where('field_id', $field['id'])->update(['access' => $access]);
                }
            }

            /* =============== role_la_module =============== */

            $module_name = 'module_'.$role->id;
            if (isset($request->$module_name)) {
                $view = 'module_view_'.$role->id;
                $create = 'module_create_'.$role->id;
                $edit = 'module_edit_'.$role->id;
                $delete = 'module_delete_'.$role->id;
                if (isset($request->$view)) {
                    $view = 1;
                } else {
                    $view = 0;
                }
                if (isset($request->$create)) {
                    $create = 1;
                } else {
                    $create = 0;
                }
                if (isset($request->$edit)) {
                    $edit = 1;
                } else {
                    $edit = 0;
                }
                if (isset($request->$delete)) {
                    $delete = 1;
                } else {
                    $delete = 0;
                }

                $query = DB::table('role_la_module')->where('role_id', $role->id)->where('module_id', $id);
                if ($query->count() == 0) {
                    DB::insert('insert into role_la_module (role_id, module_id, acc_view, acc_create, acc_edit, acc_delete, created_at, updated_at) values (?, ?, ?, ?, ?, ?, ?, ?)', [$role->id, $id, $view, $create, $edit, $delete, $now, $now]);
                } else {
                    DB::table('role_la_module')->where('role_id', $role->id)->where('module_id', $id)->update(['acc_view' => $view, 'acc_create' => $create, 'acc_edit' => $edit, 'acc_delete' => $delete]);
                }
            }
        }

        return redirect(config('laraadmin.adminRoute').'/la_modules/'.$id.'#access');
    }

    /**
     * Update Module Field's Sorting Numbers.
     *
     * @param Request $request
     * @param $id Module ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function save_module_field_sort(Request $request, $id)
    {
        $sort_array = $request->sort_array;

        foreach ($sort_array as $index => $field_id) {
            DB::table('la_module_fields')->where('id', $field_id)->update(['sort' => ($index + 1)]);
        }

        return response()->json([
            'status' => 'success'
        ]);
    }

    /**
     * Get Array of all Module Files generated by LaraAdmin.
     *
     * @param Request $request
     * @param $module_id Module ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_module_files(Request $request, $module_id)
    {
        $module = LAModule::find($module_id);
        $config = CodeGenerator::generateConfig($module->name, $module->fa_icon, false);
        $correct_file_perms = '0644';
        $correct_dir_perms = '0755';

        $arr = [];

        // Routes
        if (LAHelper::laravel_ver() >= 5.3) {
            $file_admin_routes = base_path('routes/admin_routes.php');
        } else {
            $file_admin_routes = base_path('app/Http/admin_routes.php');
        }
        if (file_exists($file_admin_routes)) {
            $class = '';
            $perms = LAHelper::fileperms($file_admin_routes);
            if (! LAHelper::fileperms_cmp($perms, $correct_file_perms)) {
                $class = 'text-red';
            }
        }
        $arr[] = ['name' => $file_admin_routes, 'perms' => $perms, 'class' => $class];

        // Controller
        $file = 'app/Http/Controllers/LA/'.$module->controller.'.php';
        if (file_exists(base_path($file))) {
            $perms = LAHelper::fileperms(base_path($file));
            $class = '';
            if (! LAHelper::fileperms_cmp($perms, $correct_file_perms)) {
                $class = 'text-red';
            }
            $arr[] = ['name' => $file, 'perms' => $perms, 'class' => $class];
        }

        // Model
        $file = 'app/Models/'.$module->model.'.php';
        if (file_exists(base_path($file))) {
            $perms = LAHelper::fileperms(base_path($file));
            $class = '';
            if (! LAHelper::fileperms_cmp($perms, $correct_file_perms)) {
                $class = 'text-red';
            }
            $arr[] = ['name' => $file, 'perms' => $perms, 'class' => $class];
        }

        // views
        $views_dir = resource_path('views/la/'.$module->name_db);
        if (file_exists($views_dir)) {
            // Directory
            $perms = LAHelper::fileperms($views_dir);
            $class = '';
            if (! LAHelper::fileperms_cmp($perms, $correct_dir_perms)) {
                $class = 'text-red';
            }
            $arr[] = ['name' => $views_dir, 'perms' => $perms, 'class' => $class];

            // Directory Files
            $views = scandir($views_dir);
            foreach ($views as $view) {
                if ($view != '.' && $view != '..') {
                    $file = $views_dir.'/'.$view;
                    $perms = LAHelper::fileperms($file);
                    $class = '';
                    if (! LAHelper::fileperms_cmp($perms, $correct_file_perms)) {
                        $class = 'text-red';
                    }
                    $arr[] = ['name' => $file, 'perms' => $perms, 'class' => $class];
                }
            }
        }

        // lang
        $file = resource_path('lang/en/'.$config->langFile).'.php';
        if (file_exists($file)) {
            $perms = LAHelper::fileperms($file);
            $class = '';
            if (! LAHelper::fileperms_cmp($perms, $correct_file_perms)) {
                $class = 'text-red';
            }
            $arr[] = ['name' => $file, 'perms' => $perms, 'class' => $class];
        }

        // Observer
        $file = base_path('app/Observers/'.$module->model.'Observer.php');
        if (file_exists($file)) {
            $perms = LAHelper::fileperms($file);
            $class = '';
            if (! LAHelper::fileperms_cmp($perms, $correct_file_perms)) {
                $class = 'text-red';
            }
            $arr[] = ['name' => $file, 'perms' => $perms, 'class' => $class];
        }

        // Find existing migration file
        $mfiles = scandir(base_path('database/migrations/'));
        $fileExistName = '';
        foreach ($mfiles as $mfile) {
            if (str_contains($mfile, 'create_'.$module->name_db.'_table')) {
                $file = 'database/migrations/'.$mfile;
                $perms = LAHelper::fileperms(base_path($file));
                $class = '';
                if (! LAHelper::fileperms_cmp($perms, $correct_file_perms)) {
                    $class = 'text-red';
                }
                $arr[] = ['name' => $file, 'perms' => $perms, 'class' => $class];
            }
        }

        return response()->json([
            'files' => $arr
        ]);
    }

    /**
     * Get Quick Add Form.
     *
     * @param Request $request
     * @param $module_id Module ID
     * @return \Illuminate\Http
     */
    public function quick_add_form(Request $request, $module_id)
    {
        $fields_req = [];

        $module = LAModule::get((int) $module_id);
        $module->quick_add_form = true;
        foreach ($module->fields as $field_name => $field) {
            if ($field['required']) {
                $fields_req[] = $field_name;
            }
        }

        return view('la.la_modules.quick_add_form', [
            'module_id' => $module_id,
            'field_name' => $request->field_name,
            'popup_vals' => $request->popup_vals,
            'fields_req' => $fields_req
        ])->with('module', $module);
    }

    /**
     * Submit Quick Add Form.
     *
     * @param Request $request
     * @param $module_id Module ID
     * @return \Illuminate\Http
     */
    public function quick_add_form_save(Request $request, $module_id)
    {
        $module = LAModule::get((int) $module_id);

        if (LAModule::hasAccess($module->name, 'create')) {
            $request->quick_add = true;

            $response = app('App\Http\Controllers\LA\\'.$module->controller)->store($request);

            $response = $response->getData();

            if (isset($response->status) && $response->status == 'success' && isset($response->insert_id)) {
                $popup_vals = LAFormMaker::process_values($request->popup_vals);

                return response()->json([
                    'status' => 'success',
                    'insert_id' => $response->insert_id,
                    'popup_vals' => $popup_vals
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => $response->message
                ]);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorised Access'
            ]);
        }
    }

    /**
     * update list in show.blade.php.
     *
     * @param Request $request
     * @param $module_id Module ID
     * @return \Illuminate\Http
     */
    public function checklist_update(Request $request)
    {
        $module = LAModule::get((int) $request->module_id);
        $row = DB::table($module->name_db)->where('id', $request->row_id)->first();
        $field = LAModuleField::find($request->module_field_id);
        $colname = $field->colname;
        $checklists = json_decode($row->$colname);
        $list = [];
        $i = 0;
        foreach ($checklists as $checklist) {
            $list[$i]['checked'] = $checklist->checked;
            $list[$i]['title'] = $checklist->title;
            if ($checklist->title == $request->list_title) {
                $list[$i]['checked'] = $request->list_vals;
            }
            $i++;
        }
        $list = json_encode($list);
        DB::table($module->name_db)->where('id', $request->row_id)->update([$colname => $list]);
    }
}
