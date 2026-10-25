<?php

namespace Tests\Concerns;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\LAModule;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

/**
 * Users for each kind of visitor the admin panel distinguishes.
 *
 * - guest: not logged in
 * - customer: CUSTOMER role, no ADMIN_PANEL permission
 * - admin: the seeded ADMIN role, ADMIN_PANEL permission but no module access
 * - viewer: a role with read-only access to every module (what Roles > Add gives a new role)
 * - manager: a role with full access to every module, but not SUPER_ADMIN
 * - super_admin: the first registered user
 */
trait ActsAsRoles
{
    /**
     * Users by role key. Call after seeding.
     *
     * @var array<string, User|null>
     */
    protected $users = [];

    /**
     * Register the Super Admin and create one user for every other role.
     *
     * @return array<string, User|null>
     */
    protected function createRoleUsers()
    {
        $this->users = [
            'guest' => null,
            'super_admin' => $this->registerSuperAdmin(),
        ];
        auth()->logout();

        $this->users['customer'] = $this->customerUser('customer@example.com');
        $this->users['admin'] = $this->employeeUser('ADMIN', 'admin2@example.com');
        $this->users['viewer'] = $this->employeeUser($this->roleWithModuleAccess('VIEWER', 'readonly')->name, 'viewer@example.com');
        $this->users['manager'] = $this->employeeUser($this->roleWithModuleAccess('MANAGER', 'full')->name, 'manager@example.com');

        return $this->users;
    }

    /**
     * An Employee and its User with the given role.
     *
     * @param  string  $roleName
     * @param  string  $email
     * @return User
     */
    protected function employeeUser($roleName, $email)
    {
        $employee = new Employee;
        $employee->forceFill([
            'name' => ucfirst(strtolower($roleName)).' Employee', 'designation' => $roleName, 'gender' => 'Female',
            'phone_primary' => '9800000000', 'phone_secondary' => '', 'email_primary' => $email,
            'email_secondary' => '', 'city' => '', 'address' => '', 'about' => '',
        ])->save();

        return $this->userFor($employee->id, $roleName, $email, $employee->name);
    }

    /**
     * A Customer and its User with the CUSTOMER role.
     *
     * @param  string  $email
     * @return User
     */
    protected function customerUser($email)
    {
        $customer = new Customer;
        $customer->forceFill([
            'name' => 'Some Customer', 'designation' => '', 'organization' => '', 'gender' => 'Male',
            'phone_primary' => '9811111111', 'phone_secondary' => '', 'email_primary' => $email,
            'email_secondary' => '', 'city' => '', 'address' => '', 'about' => '',
        ])->save();

        return $this->userFor($customer->id, 'CUSTOMER', $email, $customer->name);
    }

    /**
     * A new Employee role with the ADMIN_PANEL permission and the given access to every module.
     *
     * @param  string  $name
     * @param  string  $access  "readonly" or "full"
     * @return Role
     */
    protected function roleWithModuleAccess($name, $access)
    {
        $role = new Role;
        $role->forceFill([
            'name' => $name, 'display_name' => ucfirst(strtolower($name)), 'description' => '', 'parent' => 1,
            'dept' => 1, 'context_type' => 'Employee',
        ])->save();

        foreach (LAModule::all() as $module) {
            LAModule::setDefaultRoleAccess($module->id, $role->id, $access);
        }
        $role->attachPermission(Permission::where('name', 'ADMIN_PANEL')->first());

        return $role;
    }

    /**
     * Create a User for a context record and give it a role.
     *
     * @return User
     */
    private function userFor($contextId, $roleName, $email, $name)
    {
        $user = new User;
        $user->forceFill([
            'name' => $name, 'email' => $email, 'password' => bcrypt('secret1234'), 'context_id' => $contextId,
        ])->save();
        $user->attachRole(Role::where('name', $roleName)->first());

        return $user;
    }

    /**
     * Run a request as the given role's user (or as a guest).
     *
     * @param  string  $role
     * @return $this
     */
    protected function asRole($role)
    {
        auth()->guard()->forgetUser();
        if ($this->users[$role] !== null) {
            $this->actingAs($this->users[$role]);
        }

        return $this;
    }
}
