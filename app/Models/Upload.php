<?php
/***
 * Model generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Upload extends Model
{
    use SoftDeletes;

    protected $table = 'uploads';

    protected $hidden = [

    ];

    protected $guarded = [];

    protected $dates = ['deleted_at'];

    public static function add($filename, $caption = '', $user_id = null, $is_public = true)
    {
        $img = self::create([
            'name' => $filename,
            'path' =>  storage_path('uploads/'.$filename),
            'extension' => pathinfo($filename, PATHINFO_EXTENSION),
            'caption' => $caption,
            'user_id' => $user_id,
            'hash' => strtolower(Str::random(20)),
            'public' => $is_public
        ]);

        return $img;
    }

    public static function update_local_upload_paths()
    {
        $uploads = self::all();
        $storage_local_path = storage_path('');
        $path = '';
        foreach ($uploads as $upload) {
            $path = $upload->path;
            $path = substr($path, strpos($path, 'uploads'));
            $path = $storage_local_path.'/'.$path;
            $upload->path = $path;
            $upload->save();
        }

        return redirect(config('laraadmin.adminRoute').'/uploads');
    }

    /**
     * Get the user that owns upload.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Get File URL.
     */
    public function url()
    {
        return url('files/'.$this->hash.'/'.$this->name);
    }
}
