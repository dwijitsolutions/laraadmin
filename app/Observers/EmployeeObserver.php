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

use App\Models\Employee;

class EmployeeObserver
{
    /**
     * Listen to the Record deleting event.
     *
     * @param  Employee  $employee
     * @return void
     */
    public function deleting(Employee $employee)
    {
        return LAModule::clearMultiselects('Employees', $employee->id);
    }
}
