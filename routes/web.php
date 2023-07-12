<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LA\BlogPostsController;
use App\Http\Controllers\LA\UploadsController;
use App\Models\LAConfig;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
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

Route::get('/', [HomeController::class, 'index']);
Route::get('/home', [HomeController::class, 'index']);
Route::get(config('laraadmin.blogRoute'), [BlogPostsController::class, 'show_blog']);
Route::get(config('laraadmin.blogRoute').'/{post_url}', [BlogPostsController::class, 'show_post']);
Route::get('/category/{category_url}', [BlogPostsController::class, 'show_category']);

Route::post('/inquiry', function (Illuminate\Http\Request $request) {
    Mail::send('emails.send_inquiry', ['request' => $request], function ($m) use ($request) {
        $to = LAConfig::getByKey('default_email');
        $to_name = LAConfig::getByKey('sitename');
        $m->from(LAConfig::getByKey('default_email'), LAConfig::getByKey('sitename'));
        $m->to($to, $to_name)->subject('New Inquiry - '.$request->input('name'));
    });

    return redirect()->back()->with('success', 'Your message is delivered successfully !');
});

/* ================== Auth ================== */

Auth::routes();
Route::get('/logout', [LoginController::class, 'logout']);
Route::get('/customers/register', [RegisterController::class, 'showCustomerRegistrationForm']);

/* ================== Access Uploaded Files ================== */

Route::get('files/{hash}/{name}', [UploadsController::class, 'get_file']);

/* ================== Call LaraAdmin Routes  ================== */

require __DIR__.'/admin.php';
