<?php

//use Dwij\Laraadmin\Controllers\ModuleController;

//Route::get('laraadmin', 'ModuleController@index');

Route::group([
    'namespace'  => 'Dwij\Laraadmin\Controllers',
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
	
	/* ================== Code Editor ================== */
	Route::get(config('laraadmin.adminRoute') . '/laeditor', 'CodeEditorController@index');
	Route::any(config('laraadmin.adminRoute') . '/laeditor_get_dir', 'CodeEditorController@get_dir');
	Route::post(config('laraadmin.adminRoute') . '/laeditor_get_file', 'CodeEditorController@get_file');
	Route::post(config('laraadmin.adminRoute') . '/laeditor_save_file', 'CodeEditorController@save_file');

	/* ================== Menu Editor ================== */
	Route::resource(config('laraadmin.adminRoute') . '/la_menus', 'MenuController');
	Route::post(config('laraadmin.adminRoute') . '/la_menus/update_hierarchy', 'MenuController@update_hierarchy');
	
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
