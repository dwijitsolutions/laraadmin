<?php
/**
 * Model generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is Proprietary Software created by Dwij IT Solutions. Use of LaraAdmin requires Paid Licence issued by Dwij IT Solutions.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Observers;

use Illuminate\Support\Facades\Log;
use App\Models\LAModule;
use App\Models\LAModuleField;
use Illuminate\Support\Facades\DB;

use App\Models\BlogPost;

class BlogPostObserver
{
    /**
     * Listen to the Record deleting event.
     *
     * @param  BlogPost  $blog_post
     * @return void
     */
    public function deleting(BlogPost $blog_post)
    {
        return LAModule::clearMultiselects('Blog_posts', $blog_post->id);
    }
}
