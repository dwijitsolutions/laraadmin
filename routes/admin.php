<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
*/

/**
 * Connect routes with ADMIN_PANEL permission(for security) and 'App\Http\Controllers\LA' namespace
 * and '/admin' url.
 */
Route::group([
    'namespace' => 'App\Http\Controllers\LA',
    'as' => config('laraadmin.adminRoute').'.',
    'middleware' => ['web', 'auth', 'permission:ADMIN_PANEL', 'role:SUPER_ADMIN']
], function () {
    /* ================== Modules ================== */
    Route::resource(config('laraadmin.adminRoute') . '/la_modules', 'LAModuleController');
    Route::resource(config('laraadmin.adminRoute') . '/la_module_fields', 'LAModuleFieldController');
    Route::get(config('laraadmin.adminRoute') . '/la_modules/generate_crud/{model_id}', 'LAModuleController@generate_crud');
    Route::get(config('laraadmin.adminRoute') . '/la_modules/generate_migr/{model_id}', 'LAModuleController@generate_migr');
    Route::get(config('laraadmin.adminRoute') . '/la_modules/generate_update/{model_id}', 'LAModuleController@generate_update');
    Route::get(config('laraadmin.adminRoute') . '/la_modules/generate_migr_crud/{model_id}', 'LAModuleController@generate_migr_crud');
    Route::get(config('laraadmin.adminRoute') . '/la_modules/{model_id}/set_view_col/{column_name}', 'LAModuleController@set_view_col');
    Route::post(config('laraadmin.adminRoute') . '/la_modules/save_role_module_permissions/{id}', 'LAModuleController@save_role_module_permissions');
    Route::get(config('laraadmin.adminRoute') . '/la_modules/save_module_field_sort/{model_id}', 'LAModuleController@save_module_field_sort');
    Route::get(config('laraadmin.adminRoute') . '/la_module_fields/{id}/delete', 'LAModuleFieldController@destroy');
    Route::post(config('laraadmin.adminRoute') . '/la_modules/get_module_files/{module_id}', 'LAModuleController@get_module_files');
    Route::post(config('laraadmin.adminRoute') . '/la_module_update', 'LAModuleController@update');
    Route::post(config('laraadmin.adminRoute') . '/la_module_fields_listing_show', 'LAModuleFieldController@module_field_listing_show_ajax');

    /* ================== Code Editor ================== */
    Route::get(config('laraadmin.adminRoute') . '/la_codeeditor', function () {
        if (file_exists(resource_path("views/la/editor/index.blade.php"))) {
            return redirect(config('laraadmin.adminRoute') . '/la_editor');
        } else {
            // show install code editor page
            return View('la.editor.install');
        }
    });

    /* ================== Utilities ================== */

    Route::get(config('laraadmin.adminRoute') . '/git-status', function () {
        $commitHash = trim(exec('git log --pretty="%h" -n1 HEAD'));
        $commitComment = trim(exec('git log -n1 HEAD'));
        $commitDate = trim(exec('git log --pretty="%cd" -n1 HEAD'));

        echo "<b>Last Commit: </b>" . $commitHash . " - " . $commitComment . " - " . $commitDate;
    });

    /* ================== Menu Editor ================== */
    Route::resource(config('laraadmin.adminRoute') . '/la_menus', 'LAMenuController');
    Route::post(config('laraadmin.adminRoute') . '/la_menus/update_hierarchy', 'LAMenuController@update_hierarchy');
    Route::post(config('laraadmin.adminRoute') . '/la_menus_save_role_permissions/{id}', 'LAMenuController@la_menus_save_role_permissions');

    /* ================== Configuration ================== */
    Route::resource(config('laraadmin.adminRoute') . '/la_configs', 'LAConfigController');
    Route::post(config('laraadmin.adminRoute') . '/la_configs/edit_save/{id}', 'LAConfigController@edit_save');
    Route::get(config('laraadmin.adminRoute') . '/la_configs/ajax_destroy/{id}', 'LAConfigController@ajax_destroy');
});

/*
|--------------------------------------------------------------------------
| Node.js Routes
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| Admin Application Routes
|--------------------------------------------------------------------------
*/

Route::group([
    'as' => config('laraadmin.adminRoute').'.',
    'middleware' => ['auth', 'permission:ADMIN_PANEL'],
    'namespace' => 'App\Http\Controllers\LA'
], function () {
    /* ================== General ================== */

    Route::post(config('laraadmin.adminRoute') . '/check_unique_val/{field_id}', 'LAModuleFieldController@check_unique_val');
    Route::get(config('laraadmin.adminRoute') . '/quick_add_form/{module_id}', 'LAModuleController@quick_add_form');
    Route::post(config('laraadmin.adminRoute') . '/quick_add_form_save/{module_id}', 'LAModuleController@quick_add_form_save');
    Route::post(config('laraadmin.adminRoute') . '/checklist_update', 'LAModuleController@checklist_update');

    /* ================== Dashboard ================== */

    Route::get(config('laraadmin.adminRoute'), 'DashboardController@index');
    Route::get(config('laraadmin.adminRoute'). '/dashboard', 'DashboardController@index');

    /* ================== LALogs ================== */
    Route::resource(config('laraadmin.adminRoute') . '/la_logs', 'LALogsController');
    Route::get(config('laraadmin.adminRoute') . '/la_log_dt_ajax', 'LALogsController@dtajax');
    Route::post(config('laraadmin.adminRoute') . '/get_lalog_details/{id}', 'LALogsController@get_lalog_details');

    /* ================== Users ================== */
    Route::resource(config('laraadmin.adminRoute') . '/users', 'UsersController');
    Route::get(config('laraadmin.adminRoute') . '/user_dt_ajax', 'UsersController@dtajax');

    /* ================== Uploads ================== */
    Route::resource(config('laraadmin.adminRoute') . '/uploads', 'UploadsController');
    Route::post(config('laraadmin.adminRoute') . '/upload_files', 'UploadsController@upload_files');
    Route::get(config('laraadmin.adminRoute') . '/uploaded_files', 'UploadsController@uploaded_files');
    Route::post(config('laraadmin.adminRoute') . '/uploads_update_caption', 'UploadsController@update_caption');
    Route::post(config('laraadmin.adminRoute') . '/uploads_update_filename', 'UploadsController@update_filename');
    Route::post(config('laraadmin.adminRoute') . '/uploads_update_public', 'UploadsController@update_public');
    Route::post(config('laraadmin.adminRoute') . '/uploads_delete_file', 'UploadsController@delete_file');
    Route::get(config('laraadmin.adminRoute') . '/update_local_upload_paths', 'UploadsController@update_local_upload_paths');

    /* ================== Roles ================== */
    Route::resource(config('laraadmin.adminRoute') . '/roles', 'RolesController');
    Route::get(config('laraadmin.adminRoute') . '/role_dt_ajax', 'RolesController@dtajax');
    Route::post(config('laraadmin.adminRoute') . '/save_module_role_permissions/{id}', 'RolesController@save_module_role_permissions');
    Route::get(config('laraadmin.adminRoute') . '/hierarchy_view', 'RolesController@hierarchy_view');
    Route::post(config('laraadmin.adminRoute') . '/update_role_hierarchy', 'RolesController@update_role_hierarchy');

    /* ================== Permissions ================== */
    Route::resource(config('laraadmin.adminRoute') . '/permissions', 'PermissionsController');
    Route::get(config('laraadmin.adminRoute') . '/permission_dt_ajax', 'PermissionsController@dtajax');
    Route::post(config('laraadmin.adminRoute') . '/save_permissions/{id}', 'PermissionsController@save_permissions');

    /* ================== Departments ================== */
    Route::resource(config('laraadmin.adminRoute') . '/departments', 'DepartmentsController');
    Route::get(config('laraadmin.adminRoute') . '/department_dt_ajax', 'DepartmentsController@dtajax');

    /* ================== Employees ================== */
    Route::resource(config('laraadmin.adminRoute') . '/employees', 'EmployeesController');
    Route::get(config('laraadmin.adminRoute') . '/employee_dt_ajax', 'EmployeesController@dtajax');
    Route::post(config('laraadmin.adminRoute') . '/change_password/{id}', 'EmployeesController@change_password');

    /* ================== Customers ================== */
    Route::resource(config('laraadmin.adminRoute') . '/customers', 'CustomersController');
    Route::get(config('laraadmin.adminRoute') . '/customer_dt_ajax', 'CustomersController@dtajax');

    /* ================== Backups ================== */
    Route::resource(config('laraadmin.adminRoute') . '/backups', 'BackupsController');
    Route::get(config('laraadmin.adminRoute') . '/backup_dt_ajax', 'BackupsController@dtajax');
    Route::post(config('laraadmin.adminRoute') . '/create_backup_ajax', 'BackupsController@create_backup_ajax');
    Route::get(config('laraadmin.adminRoute') . '/downloadBackup/{id}', 'BackupsController@downloadBackup');

    /* ================== search ================== */
    Route::get(config('laraadmin.adminRoute') . '/find/{module_name}', 'SearchController@find');

    /* ================== Blog_categories ================== */
    Route::resource(config('laraadmin.adminRoute') . '/blog_categories', 'BlogCategoriesController');
    Route::get(config('laraadmin.adminRoute') . '/blog_category_dt_ajax', 'BlogCategoriesController@dtajax');

    /* ================== Blog_posts ================== */
    Route::resource(config('laraadmin.adminRoute') . '/blog_posts', 'BlogPostsController');
    Route::get(config('laraadmin.adminRoute') . '/blog_post_dt_ajax', 'BlogPostsController@dtajax');
});
