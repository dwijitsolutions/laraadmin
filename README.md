<p align="center">
  <img src="https://laraadmin.com/img/laraadmin-256.png">
  <h1 align="center" style="margin-top:5px;">LaraAdmin 1.13</h1>
</p>

[![License](https://poser.pugx.org/dwij/laraadmin/license.svg)](https://packagist.org/packages/dwij/laraadmin)
[![Code Triagers Badge](https://www.codetriage.com/dwijitsolutions/laraadmin/badges/users.svg)](https://www.codetriage.com/dwijitsolutions/laraadmin)

LaraAdmin is a Open source CRM for quick-start Admin based applications with features like Advanced CRUD Generation, Schema Manager and Workflows.

> **Warning**
> Since version 1.1, LaraAdmin is not a package anymore.
> It's a template: create your application from this repository.

*Website: [LaraAdmin.com](https://laraadmin.com)*

*Creator: [@gdbhosale](https://github.com/gdbhosale), [Dwij IT Solutions](https://github.com/dwijitsolutions)*

--------

### Features:
##### Module Manager:
You can create and edit Modules without touching code. It generate CRUD methods and views on the go from your schema, which can be customized. [Schema based migrations done]

##### Workflows:
Workflows governs the automation of business processes like Tasks, Events and many others according to set of procedural rules. (Cron Events) [Under Development]

##### User Management:
Easy user Management with features like Employees, Roles, Groups, Departments and Access Control. [Under Development]

--------

### Snapshots:

CRUD Model Listing & Add:

![LaraAdmin CRUD Model Add](https://laraadmin.com/img/laraadmin/laraadmin-row-listing-add.jpg)

CRUD Model View:

![LaraAdmin CRUD Model View](https://laraadmin.com/img/laraadmin/laraadmin-row-view.jpg)

Online Code Editor:
![Online Code Editor](https://laraadmin.com/img/laraadmin/laraadmin-online-code-editor.jpg)

### Requirements

- PHP 8.3 or newer (tested on PHP 8.4)
- Laravel 13
- MySQL 8.0.13+ or MariaDB 10.2+

### Installation & Documentation

```
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
```

Set `MAIL_FROM_ADDRESS` in `.env`: spatie/laravel-backup refuses to boot without a sender address. Then open the site and register: the first user becomes Super Admin.

Documentation for the LaraAdmin can be found on the [LaraAdmin website](https://laraadmin.com/docs/1.1).
> **Warning**
> Documentation is written for version 1.1 and is not updated for 1.13 yet. See [What's new in 1.13](#whats-new-in-113) for the differences.

### Running tests

Tests run against a real MySQL database named `laraadmin_test` (configured in `phpunit.xml`):

```
./vendor/bin/phpunit
composer sniff    # coding style check (composer lint to fix)
```

### What's new in 1.13.1

A security and bug-fix release. **Update every site built on LaraAdmin 1.1 or 1.13**, or apply the registration fix
by hand (see below).

Security fixes:

- **Anyone could register as Super Admin.** The registration form is hidden once a user exists, but `POST /register`
  with `context_type=Employee` still created a Super Admin. It is now refused (403) once any user exists. On sites
  that can't update yet, add at the top of `RegisterController::create()`:
  `if ($data['context_type'] == 'Employee' && \App\Models\User::count() != 0) { abort(403); }`.
  Check the `users` table for accounts you don't recognise.
- Any user with the admin panel permission could change any employee's password, the Super Admin's included. Only
  the employee themself or a Super Admin can now.
- These actions now check the user's module access: every module's listing data (`*_dt_ajax`), LA Logs details, the
  role hierarchy, checklist updates, quick add forms, the unique value check and refreshing upload paths.
- Field access is enforced when saving: a role can no longer write a field it may only view, even with a crafted
  request.
- Employee forms can no longer grant the Super Admin role.
- Renaming an upload can no longer move the file out of `storage/uploads`.
- Uploaded HTML and SVG files are downloaded instead of shown as pages of the site.
- Downloading a backup needs create access to Backups (view access was enough).
- Draft blog posts are no longer public.

Bug fixes:

- Logins and logouts are written to LA Logs again (the listeners were never registered).
- Blog post pages failed with `Undefined array key "gallery"`. Views that show a field the module doesn't have now
  render nothing for it instead of failing.
- Editing an employee removed all their roles, and the Add Employee form never assigned them.
- Customers added in the admin never got their login user.
- Creating backups failed with spatie/laravel-backup 10. `config/backup.php` is now included: it backs up the
  database and `storage/uploads`, and leaves the code and `.env` out.
- Role and permission changes now apply immediately on Redis and Memcached caches.
- New custom menus and menu access settings had no effect for roles other than Super Admin.
- Quick add (the "+" next to a dropdown) never reported success.
- Pages that crashed now return 404 or work: unknown records and blog categories, the password confirmation page,
  module search, and resource routes that had no controller method (`users/create`, `uploads/{id}` and others).
- An empty optional dropdown linked to a table no longer fails to save.
- The CRUD generator writes files as `0644` instead of world-writable `0777`.

Tests now open every page and submit every form and action as six kinds of users (guest, customer, admin without
module access, read-only role, full-access role, Super Admin). `RouteCoverageTest` fails when a route is added
without a test.

### What's new in 1.13

LaraAdmin 1.13 moves from Laravel 8 to **Laravel 13** and runs on **PHP 8.3+ (tested on PHP 8.4)**.

- Role-based access uses [laraadmin/entrust](https://github.com/laraadmin/entrust) 7.0.
- Forms use [laraadmin/html](https://github.com/laraadmin/html) 7.0, a maintained fork of the abandoned `laravelcollective/html`. The `Form::`/`HTML::` API is unchanged.
- `fruitcake/laravel-cors`, `doctrine/dbal` and `facade/ignition` are no longer needed.
- Fixed:
  - CRUD generation and module deletion
  - editing File, Image and Datetime fields
  - creating JSON fields on MySQL 8
  - the Module Manager module page

Notes when upgrading an application built on 1.1:

- `MAIL_FROM_ADDRESS` must be set.
- New Currency columns are `DOUBLE` instead of `DOUBLE(15,2)`. Changing a field's type to Integer makes the column `NOT NULL`, the same as creating one.
- Sessions keep PHP serialization, so users stay logged in across the upgrade.
- The application keeps the Laravel 8 directory structure (`app/Http/Kernel.php` and so on), which Laravel 13 still supports.

Known issues and planned follow-ups are tracked in [engg-debt.md](engg-debt.md).

### Contributing

Thank you for considering contributing to the LaraAdmin. Please read [features doc](https://laraadmin.com/devdoc-features)

### Bugs, improvements & Security Vulnerabilities

If you discover a bug or security vulnerability within LaraAdmin, please send an e-mail to Ganesh Bhosale at ganesh@dwij.in. All requests will be addressed promptly.

### Issues

If you come across any issue/bug please [report them here](https://github.com/dwijitsolutions/laraadmin/issues).

### License

LaraAdmin is open-sourced software built by Dwij IT Solutions and contributors and licensed under the [MIT license](https://opensource.org/licenses/MIT).

### Credits:
- Ganesh Bhosale https://github.com/gdbhosale
- Ganesh Khade https://github.com/moraya-re
- https://github.com/almasaeed2010/AdminLTE
- https://github.com/acacha/adminlte-laravel
- https://github.com/yajra/laravel-datatables
- https://github.com/creativeorange/gravatar

<p align="center">
  <br>
  Brought to you by<br>
  <a href="https://dwijitsolutions.com">
  <img src="https://dwijitsolutions.com/wp-content/uploads/2016/01/dwij-it-solutions.png">
  </a>
</p>
