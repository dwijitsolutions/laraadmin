<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\LALog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Validator;

/***
 * Register Controller
 *
 * This controller handles the registration of new users as well as their
 * validation and creation. By default this controller uses a trait to
 * provide this functionality without requiring any additional code.
 */
class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');

        $this->redirectTo = '/'.config('laraadmin.adminRoute');
    }

    /**
     * Show Register form Super Admin.
     *
     * @return mixed
     */
    public function showRegistrationForm()
    {
        $roleCount = Role::count();
        if ($roleCount != 0) {
            // Register Super Admin
            $userCount = User::count();
            if ($userCount == 0) {
                return view('auth.register');
            } else {
                return redirect('login');
            }
        } else {
            return view('errors.error', [
                'title' => 'Migration not completed',
                'message' => 'Please run command <code>php artisan db:seed</code> to generate required table data.',
            ]);
        }
    }

    /**
     * Show Register form for Customer.
     *
     * @return mixed
     */
    public function showCustomerRegistrationForm()
    {
        $roleCount = Role::count();
        if ($roleCount != 0) {
            // Register Customer
            return view('auth.register_customer');
        } else {
            return view('errors.error', [
                'title' => 'Migration not completed',
                'message' => 'Please run command <code>php artisan db:seed</code> to generate required table data.',
            ]);
        }
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        // TODO: This is Not Standard. Need to find alternative
        User::unguard();

        $roleCount = Role::count();

        if ($data['context_type'] == 'Employee' && $roleCount != 0) {
            // Register Super Admin

            $employee = Employee::create([
                'name' => $data['name'],
                'designation' => 'Super Admin',
                'gender' => 'Male',
                'phone_primary' => '',
                'phone_secondary' => '',
                'email_primary' => $data['email'],
                'email_secondary' => '',
                'profile_img' => null,
                'city' => '',
                'address' => '',
                'about' => '',
                'date_birth' => null
            ]);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'context_id' => $employee->id
            ]);
            $role = Role::where('name', 'SUPER_ADMIN')->first();
            $user->attachRole($role);

            LALog::make('Employees.EMPLOYEE_CREATED', [
                'title' => 'Employee '.$employee->name.' Created',
                'module_id' => 'Employees',
                'context_id' => $employee->id,
                'content' => $employee,
                'user_id' => $user->id,
                'notify_to' => '[]'
            ]);

            LALog::make('Users.USER_CREATED', [
                'title' => 'User/Employee '.$user->name.' Created',
                'module_id' => 'Users',
                'context_id' => $user->id,
                'content' => $user,
                'user_id' => $user->id,
                'notify_to' => '[]'
            ]);
        } elseif ($data['context_type'] == 'Customer') {
            // Register Customer

            $customer = Customer::create([
                'name' => $data['name'],
                'designation' => '',
                'organization' => '',
                'gender' => 'Male',
                'phone_primary' => $data['phone'],
                'phone_secondary' => '',
                'email_primary' => $data['email'],
                'email_secondary' => '',
                'profile_img' => null,
                'city' => '',
                'address' => '',
                'about' => '',
                'date_birth' => null
            ]);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'context_id' => $customer->id,
            ]);
            $customerRole = Role::where('name', 'CUSTOMER')->first();
            $user->attachRole($customerRole);

            LALog::make('Customers.CUSTOMER_CREATED', [
                'title' => 'Customer '.$customer->name.' Created',
                'module_id' => 'Customers',
                'context_id' => $customer->id,
                'content' => $customer,
                'user_id' => $user->id,
                'notify_to' => '[]'
            ]);

            LALog::make('Users.USER_CREATED', [
                'title' => 'User/Customer '.$user->name.' Created',
                'module_id' => 'Users',
                'context_id' => $user->id,
                'content' => $user,
                'user_id' => $user->id,
                'notify_to' => '[]'
            ]);

            $this->redirectTo = '/';
        }

        return $user;
    }
}
