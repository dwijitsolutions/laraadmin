<?php

/*
|--------------------------------------------------------------------------
| Laracogs Config
|--------------------------------------------------------------------------
|
| WARNING! do not change any thing that starts and ends with _
|
*/


return [

    'crud' => [

        'template_source'               => base_path('resources/laraadmin'),

        /*
        |--------------------------------------------------------------------------
        | Single CRUD
        |--------------------------------------------------------------------------
        | The config for CRUDs which which are simple tables:
        | roles, settings etc.
        */

        'single' => [
            '_path_facade_'              => app_path('Facades'),
            '_path_service_'             => app_path('Services'),
            '_path_repository_'          => app_path('Repositories/_table_'),
            '_path_model_'               => app_path('Repositories/_table_'),
            '_path_controller_'          => app_path('Http/Controllers/'),
            '_path_api_controller_'      => app_path('Http/Controllers/Api'),
            '_path_views_'               => base_path('resources/views'),
            '_path_tests_'               => base_path('tests'),
            '_path_request_'             => app_path('Http/Requests/'),
            '_path_routes_'              => app_path('Http/routes.php'),
            '_path_api_routes_'          => app_path('Http/api-routes.php'),
            'routes_prefix'              => '',
            'routes_suffix'              => '',
            '_app_namespace_'            => app()->getInstance()->getNamespace(),
            '_namespace_services_'       => app()->getInstance()->getNamespace().'Services',
            '_namespace_facade_'         => app()->getInstance()->getNamespace().'Facades',
            '_namespace_repository_'     => app()->getInstance()->getNamespace().'Repositories\_table_',
            '_namespace_model_'          => app()->getInstance()->getNamespace().'Repositories\_table_',
            '_namespace_controller_'     => app()->getInstance()->getNamespace().'Http\Controllers',
            '_namespace_api_controller_' => app()->getInstance()->getNamespace().'Http\Controllers\Api',
            '_namespace_request_'        => app()->getInstance()->getNamespace().'Http\Requests',
        ],

        /*
        |--------------------------------------------------------------------------
        | Sectioned CRUD
        |--------------------------------------------------------------------------
        | The config for CRUDs which are like as sections such as various admin tables:
        | admin_role, admin_settings etc.
        */
        'sectioned' => [
            '_path_facade_'              => app_path('Facades'),
            '_path_service_'             => app_path('Services/_section_'),
            '_path_repository_'          => app_path('Repositories/_section_/_table_'),
            '_path_model_'               => app_path('Repositories/_section_/_table_'),
            '_path_controller_'          => app_path('Http/Controllers/_section_/'),
            '_path_api_controller_'      => app_path('Http/Controllers/Api/_section_/'),
            '_path_views_'               => base_path('resources/views/_sectionLowerCase_'),
            '_path_tests_'               => base_path('tests'),
            '_path_request_'             => app_path('Http/Requests/_section_'),
            '_path_routes_'              => app_path('Http/routes.php'),
            '_path_api_routes_'          => app_path('Http/api-routes.php'),
            'routes_prefix'              => "\n\nRoute::group(['namespace' => '_section_', 'prefix' => '_sectionLowerCase_', 'middleware' => ['web']], function () { \n",
            'routes_suffix'              => "\n});",
            '_app_namespace_'            => app()->getInstance()->getNamespace(),
            '_namespace_services_'       => app()->getInstance()->getNamespace().'Services\_section_',
            '_namespace_facade_'         => app()->getInstance()->getNamespace().'Facades',
            '_namespace_repository_'     => app()->getInstance()->getNamespace().'Repositories\_section_\_table_',
            '_namespace_model_'          => app()->getInstance()->getNamespace().'Repositories\_section_\_table_',
            '_namespace_controller_'     => app()->getInstance()->getNamespace().'Http\Controllers\_section_',
            '_namespace_api_controller_' => app()->getInstance()->getNamespace().'Http\Controllers\Api\_section_',
            '_namespace_request_'        => app()->getInstance()->getNamespace().'Http\Requests\_section_',
        ]
    ]

];