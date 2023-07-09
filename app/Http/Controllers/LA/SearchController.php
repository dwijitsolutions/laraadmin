<?php

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use App\Models\LAModule;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function find(Request $request, $module_name)
    {
        if ($module_name == 'Roles' || $module_name == 'Users' || $module_name == 'Permissions') {
            $model_path = "\App\\";
        } else {
            $model_path = "\App\Models\\";
        }
        $module = LAModule::get($module_name);
        $model_name = $model_path.$module->model;

        return $model_name::search($request->get('q'))->get();
    }
}
