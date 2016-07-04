<?php
/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Upload extends Model
{
    use SoftDeletes;
	
	protected $table = 'uploads';
	
	// By default making all fields fillable
	// protected $fillable = [];
	
	protected $hidden = [
        
    ];

	protected $dates = ['deleted_at'];
}
