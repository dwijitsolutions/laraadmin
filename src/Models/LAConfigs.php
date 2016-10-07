<?php

namespace Dwij\Laraadmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Exception;
use Log;
use DB;
use Dwij\Laraadmin\Helpers\LAHelper;

class LAConfigs extends Model
{   
	protected $table = 'la_configs';
	
	protected $fillable = [
		"key", "value"
	];
	
	protected $hidden = [
		
	];
	public static function getByKey($key){
		return LAConfigs::where('key',$key)->first()->value;
	}
	
}
