<?php
/***
 * Model generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Observers;

use App\Models\BlogCategory;
use App\Models\LAModule;

class BlogCategoryObserver
{
    /**
     * Listen to the Record deleting event.
     *
     * @param  BlogCategory  $blogcategory
     * @return void
     */
    public function deleting(BlogCategory $blogcategory)
    {
        return LAModule::clearMultiselects('Blog_categories', $blogcategory->id);
    }
}
