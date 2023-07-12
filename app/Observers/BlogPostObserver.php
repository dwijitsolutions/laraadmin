<?php
/***
 * Model generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Observers;

use App\Models\BlogPost;
use App\Models\LAModule;

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
