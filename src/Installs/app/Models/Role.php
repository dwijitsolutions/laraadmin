<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        "name", "name_short", "parent", "dept"
    ];
    
    protected $hidden = [
        
    ];
}
