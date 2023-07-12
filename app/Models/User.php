<?php
/***
 * Model generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laraadmin\Entrust\Traits\EntrustUserTrait;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use EntrustUserTrait;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'context_id',
        'type'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * @return mixed
     */
    public function la_logs()
    {
        return $this->belongsToMany('App\Models\LALog', 'lalog_user', 'user_id', 'lalog_id');
    }

    /**
     * @return mixed
     */
    public function uploads()
    {
        return $this->hasMany('App\Models\Upload');
    }

    /**
     * Get context object return for User - Employee / Customer.
     *
     * @return object / NULL
     */
    public function context()
    {
        if ($this->hasRole(Role::ctype('Employee', 'Name'))) {
            $employee = Employee::find($this->context_id);
            $employee->context_type = 'Employee';

            return $employee;
        } elseif ($this->hasRole(Role::ctype('Customer', 'Name'))) {
            $customer = Customer::find($this->context_id);
            $customer->context_type = 'Customer';

            return $customer;
        }

        return null;
    }

    /**
     * @return mixed
     */
    public static function get($context_id, $context_type)
    {
        $users = self::where('context_id', $context_id)->get();
        foreach ($users as $user) {
            // Check if Use belong to Context Type like "Employees" or "Customers"
            // e.g. $context_type = "Employees" then $user->hasRole(["SUPER_ADMIN", "ADMIN])
            if ($user->hasRole(Role::ctype($context_type, 'Name'))) {
                return $user;
            }
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
