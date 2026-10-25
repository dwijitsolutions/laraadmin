<?php

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use App\Models\LAModule;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Search a module's records with Laravel Scout (used by the navbar search box).
     *
     * Only modules whose model uses Laravel\Scout\Searchable can be searched.
     *
     * @param Request $request
     * @param string $module_name Module Name
     * @return mixed
     */
    public function find(Request $request, $module_name)
    {
        $module = LAModule::get($module_name);
        if (! isset($module->id)) {
            abort(404);
        }
        if (! LAModule::hasAccess($module->id, 'view')) {
            abort(403);
        }

        $model_name = 'App\\Models\\'.$module->model;
        if (! class_exists($model_name) || ! in_array('Laravel\Scout\Searchable', class_uses_recursive($model_name))) {
            abort(404);
        }

        return $model_name::search($request->get('q'))->get();
    }
}
