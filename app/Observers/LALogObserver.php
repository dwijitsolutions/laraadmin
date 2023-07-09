<?php
/***
 * Model generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Observers;

use App\Models\LALog;
use App\Models\LAModule;
use Illuminate\Support\Facades\Log;

class LALogObserver
{
    /**
     * Listen to the Record deleting event.
     *
     * @param  LALog  $la_log
     * @return void
     */
    public function deleting(LALog $la_log)
    {
        return LAModule::clearMultiselects('LA_logs', $la_log->id);
    }
}
