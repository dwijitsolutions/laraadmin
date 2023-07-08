<?php
/**
 * Model generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is Proprietary Software created by Dwij IT Solutions. Use of LaraAdmin requires Paid Licence issued by Dwij IT Solutions.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\LALog;

class BlogPost extends Model
{
    use SoftDeletes;

    protected $table = 'blog_posts';

    protected $hidden = [

    ];

    protected $guarded = [];

    protected $dates = ['deleted_at'];

    /**
     * Get the index name for the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return 'blog_posts_index';
    }

    /**
     * Get mapping array by key
     *
     * @return array
     */
    public static function arr($key = "id")
    {
        $results = BlogPost::all();
        $arr = array();
        foreach ($results as $result) {
            $arr[$result->$key] = $result;
        }
        return $arr;
    }

    /**
     * Get the banner Image url
     */
    public function bannerImage($large = false)
    {
        if ($this->banner != 0) {
            return Upload::find($this->banner)->url();
        } else {
            if ($large) {
                return asset('la-assets/img/post_banner_large.jpg');
            } else {
                return asset('la-assets/img/post_banner.png');
            }
        }
        return null;
    }

    /**
     * Get the user that owns post
     */
    public function author()
    {
        return $this->belongsTo('App\Models\Employee', 'author_id');
    }

    /**
     * Get the user that owns post
     */
    public function date()
    {
        return date("M d, Y", strtotime($this->post_date));
    }

    /**
     * Get the category that has post
     */
    public function category()
    {
        return $this->belongsTo('App\Models\BlogCategory', 'category_id');
    }

    /**
     * Get all events happened on Module
     *
     * @return mixed
     */
    public function timeline()
    {
        $moduleConfigs = config('laraadmin.log.Blog_posts');
        $moduleConfigsArr = array_keys($moduleConfigs);
        return LALog::where("context_id", $this->id)->whereIn("type", $moduleConfigsArr)->orderBy("created_at", "desc")->get();
    }
}
