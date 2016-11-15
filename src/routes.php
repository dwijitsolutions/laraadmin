<?php

$as = "";
if(\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
	$as = config('laraadmin.adminRoute').'.';
}

Route::group([
    'namespace'  => 'Dwij\Laraadmin\Controllers',
	'as' => $as,
    'middleware' => ['web', 'auth', 'permission:ADMIN_PANEL', 'role:SUPER_ADMIN']
], function () {
    
	/* ================== Modules ================== */
	Route::resource(config('laraadmin.adminRoute') . '/modules', 'ModuleController');
	Route::resource(config('laraadmin.adminRoute') . '/module_fields', 'FieldController');
	Route::get(config('laraadmin.adminRoute') . '/module_generate_crud/{model_id}', 'ModuleController@generate_crud');
	Route::get(config('laraadmin.adminRoute') . '/module_generate_migr/{model_id}', 'ModuleController@generate_migr');
	Route::get(config('laraadmin.adminRoute') . '/module_generate_update/{model_id}', 'ModuleController@generate_update');
	Route::get(config('laraadmin.adminRoute') . '/module_generate_migr_crud/{model_id}', 'ModuleController@generate_migr_crud');
	Route::get(config('laraadmin.adminRoute') . '/modules/{model_id}/set_view_col/{column_name}', 'ModuleController@set_view_col');
	Route::post(config('laraadmin.adminRoute') . '/save_role_module_permissions/{id}', 'ModuleController@save_role_module_permissions');
	Route::get(config('laraadmin.adminRoute') . '/save_module_field_sort/{model_id}', 'ModuleController@save_module_field_sort');
	Route::post(config('laraadmin.adminRoute') . '/check_unique_val/{field_id}', 'FieldController@check_unique_val');
	Route::get(config('laraadmin.adminRoute') . '/module_fields/{id}/delete', 'FieldController@destroy');
	Route::post(config('laraadmin.adminRoute') . '/get_module_files/{module_id}', 'ModuleController@get_module_files');
	Route::post(config('laraadmin.adminRoute'). '/module_update', 'ModuleController@update');
	
	/* ================== Code Editor ================== */
	Route::get(config('laraadmin.adminRoute') . '/lacodeeditor', function () {
		if(file_exists(resource_path("views/la/editor/index.blade.php"))) {
			return redirect(config('laraadmin.adminRoute') . '/laeditor');
		} else {
			// show install code editor page
			return View('la.editor.install');
		}
	});

	/* ================== Menu Editor ================== */
	Route::resource(config('laraadmin.adminRoute') . '/la_menus', 'MenuController');
	Route::post(config('laraadmin.adminRoute') . '/la_menus/update_hierarchy', 'MenuController@update_hierarchy');
	
	/* ================== Configuration ================== */
	Route::resource(config('laraadmin.adminRoute') . '/la_configs', '\App\Http\Controllers\LA\LAConfigController');
	
    Route::group([
        'middleware' => 'role'
    ], function () {
		/*
		Route::get(config('laraadmin.adminRoute') . '/menu', [
            'as'   => 'menu',
            'uses' => 'LAController@index'
        ]);
		*/
    });
});
