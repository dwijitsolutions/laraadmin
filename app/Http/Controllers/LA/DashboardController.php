<?php
/**
 * Controller generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is Proprietary Software created by Dwij IT Solutions. Use of LaraAdmin requires Paid Licence issued by Dwij IT Solutions.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Models\BlogPost;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Upload;

/**
 * Class DashboardController
 * @package App\Http\Controllers
 */
class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function index()
    {
        $blog_post_count = BlogPost::count();
        $customers_count = Customer::count();
        $employee_count = Employee::count();
        $upload_count = Upload::count();
        return view('la.dashboard', [
            'blog_post_count' => $blog_post_count,
            'customers_count' => $customers_count,
            'employee_count' => $employee_count,
            'upload_count' => $upload_count
        ]);
    }
}