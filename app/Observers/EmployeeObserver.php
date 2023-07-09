<?php
/***
 * Model generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Observers;

use App\Models\Employee;
use App\Models\LAModule;

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
