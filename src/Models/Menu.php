<?php

namespace Dwij\Laraadmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Dwij\Laraadmin\Helpers\LAHelper;

class Menu extends Model
{
    protected $table = 'la_menus';
    
    protected $guarded = [
        
    ];
}
