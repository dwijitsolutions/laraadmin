
/* ================== Homepage ================== */

Route::group(['middleware' => ['web']], function () {
	Route::get('/', 'HomeController@index');
	Route::get('/home', 'HomeController@index');
	Route::auth();
});

/* ================== Access Uploaded Files ================== */
Route::get('files/{hash}/{name}', 'LA\UploadsController@get_file');

/*
|--------------------------------------------------------------------------
| Admin Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

if(Request::is(config('laraadmin.adminRoute')) || Request::is(config('laraadmin.adminRoute').'/*')) {
	require __DIR__.'/admin_routes.php';
}
