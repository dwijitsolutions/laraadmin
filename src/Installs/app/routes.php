
/* ================== Homepage ================== */

Route::get('/', 'HomeController@index');
Route::get('/home', 'HomeController@index');

Route::auth();

/* ================== Dashboard ================== */

Route::get(config('laraadmin.adminRoute'), 'LA\DashboardController@index');
Route::get(config('laraadmin.adminRoute'). '/dashboard', 'LA\DashboardController@index');
Route::get('/dashboard', 'LA\DashboardController@index');

/* ================== Users ================== */
Route::get(config('laraadmin.adminRoute') . '/user/{id}', 'LA\UserController@show');

/* ================== Employees ================== */
Route::resource(config('laraadmin.adminRoute') . '/employees', 'LA\EmployeesController');
Route::get(config('laraadmin.adminRoute') . '/employee_dt_ajax', 'LA\EmployeesController@dtajax');

/* ================== Books ================== */
Route::resource(config('laraadmin.adminRoute') . '/books', 'LA\BooksController');
Route::get(config('laraadmin.adminRoute') . '/book_dt_ajax', 'LA\BooksController@dtajax');
