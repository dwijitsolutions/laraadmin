<?php
/***
 * Model generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Observers;

use App\Models\LAModule;
use App\Models\Upload;

class UploadObserver
{
    /**
     * Listen to the Record deleting event.
     *
     * @param  Upload  $upload
     * @return void
     */
    public function deleting(Upload $upload)
    {
        return LAModule::clearMultiselects('Uploads', $upload->id);
    }
}
