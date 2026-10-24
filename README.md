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
