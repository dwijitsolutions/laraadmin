<?php
/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use SoftDeletes;

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
    
    protected $dates = ['deleted_at'];
}
