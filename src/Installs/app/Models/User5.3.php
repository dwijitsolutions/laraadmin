<?php
/**
 * Model generated using LaraAdmin
 * Help: http://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: http://dwijitsolutions.com
 */

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;
use Zizaco\Entrust\Traits\EntrustUserTrait;

class User extends Authenticatable implements AuthorizableContract, CanResetPasswordContract
{
    use Notifiable;
    use CanResetPassword;
    // use SoftDeletes;
    use EntrustUserTrait;

    protected $table = 'users';
	
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	protected $fillable = [
		'name', 'email', 'password', "role", "context_id", "type"
	];
	
	/**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
	protected $hidden = [
		'password', 'remember_token',
    ];
    
    // protected $dates = ['deleted_at'];

    /**
     * @return mixed
     */
    public function uploads()
    {
        return $this->hasMany('App\Models\Upload');
    }
}
