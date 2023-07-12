<?php
/***
 * Controller generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Role;

/***
 * Home Controller
 */
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function index()
    {
        $roleCount = Role::count();
        if ($roleCount != 0) {
            if ($roleCount != 0) {
                $posts = BlogPost::where('status', 'Published')->orderBy('post_date', 'desc')->get();

                return view('home', [
                    'posts' => $posts
                ]);
            }
        } else {
            return view('errors.error', [
                'title' => 'Migration not completed',
                'message' => 'Please run command <code>php artisan db:seed</code> to generate required table data.',
            ]);
        }
    }
}
