<?php

//use Dwij\Laraadmin\Controllers\ModuleController;

//Route::get('laraadmin', 'ModuleController@index');

Route::group([
    'namespace'  => 'Dwij\Laraadmin\Controllers',
    'middleware' => ['web', 'auth']
], function () {
    
	/* ================== Files ================== */
	Route::get(config('laraadmin.adminRoute') . '/folder_files/{folder_name}', 'FileController@get_folder_files');
	Route::post(config('laraadmin.adminRoute') . '/upload_files', 'FileController@upload_files');

	/* ================== Modules ================== */
	Route::resource(config('laraadmin.adminRoute') . '/modules', 'ModuleController');
	Route::resource(config('laraadmin.adminRoute') . '/module_fields', 'FieldController');
	Route::get(config('laraadmin.adminRoute') . '/module_generate_crud/{model_id}', 'ModuleController@generate_crud');
	Route::get(config('laraadmin.adminRoute') . '/module_generate_migr/{model_id}', 'ModuleController@generate_migr');
	
	/* ================== Code Editor ================== */
	Route::get(config('laraadmin.adminRoute') . '/laeditor', 'CodeEditorController@index');
	Route::any(config('laraadmin.adminRoute') . '/laeditor_get_dir', 'CodeEditorController@get_dir');
	Route::post(config('laraadmin.adminRoute') . '/laeditor_get_file', 'CodeEditorController@get_file');
	
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