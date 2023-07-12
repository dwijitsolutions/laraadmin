<?php
/***
 * Controller generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\LALog;
use App\Models\LAModule;
use App\Models\LAModuleField;
use Collective\Html\FormFacade as Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class BlogCategoriesController extends Controller
{
    public $show_action = true;

    /**
     * Display a listing of the Blog_categories.
     *
     * @return mixed
     */
    public function index()
    {
        $module = LAModule::get('Blog_categories');

        if (LAModule::hasAccess($module->id)) {
            return View('la.blog_categories.index', [
                'show_actions' => $this->show_action,
                'listing_cols' => LAModule::getListingColumns('Blog_categories'),
                'module' => $module
            ]);
        } else {
            return redirect(config('laraadmin.adminRoute').'/');
        }
    }

    /**
     * Show the form for creating a new blog_category.
     *
     * @return mixed
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created blog_category in database.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        if (LAModule::hasAccess('Blog_categories', 'create')) {
            $rules = LAModule::validateRules('Blog_categories', $request);

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                if (isset($request->quick_add) && $request->quick_add) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Validation error',
                        'errors' => $validator->errors()
                    ]);
                } else {
                    return redirect()->back()->withErrors($validator)->withInput();
                }
            }

            $insert_id = LAModule::insert('Blog_categories', $request);

            $blog_category = BlogCategory::find($insert_id);

            // Add LALog
            LALog::make('Blog_categories.BLOG_CATEGORY_CREATED', [
                'title' => 'Blog category Created',
                'module_id' => 'Blog_categories',
                'context_id' => $blog_category->id,
                'content' => $blog_category,
                'user_id' => Auth::user()->id,
                'notify_to' => '[]'
            ]);

            if (isset($request->quick_add) && $request->quick_add) {
                return response()->json([
                    'status' => 'success',
                    'insert_id' => $insert_id
                ]);
            } else {
                return redirect()->route(config('laraadmin.adminRoute').'.blog_categories.index');
            }
        } else {
            if (isset($request->quick_add) && $request->quick_add) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized Access'
                ]);
            } else {
                return redirect(config('laraadmin.adminRoute').'/');
            }
        }
    }

    /**
     * Display the specified blog_category.
     *
     * @param int $id blog_category ID
     * @return mixed
     */
    public function show($id)
    {
        if (LAModule::hasAccess('Blog_categories', 'view')) {
            $blog_category = BlogCategory::find($id);
            if (isset($blog_category->id)) {
                $module = LAModule::get('Blog_categories');
                $module->row = $blog_category;

                return view('la.blog_categories.show', [
                    'module' => $module,
                    'view_col' => $module->view_col,
                    'no_header' => true,
                    'no_padding' => 'no-padding'
                ])->with('blog_category', $blog_category);
            } else {
                return view('errors.404', [
                    'record_id' => $id,
                    'record_name' => ucfirst('blog_category'),
                ]);
            }
        } else {
            return redirect(config('laraadmin.adminRoute').'/');
        }
    }

    /**
     * Show the form for editing the specified blog_category.
     *
     * @param int $id blog_category ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        if (LAModule::hasAccess('Blog_categories', 'edit')) {
            $blog_category = BlogCategory::find($id);
            if (isset($blog_category->id)) {
                $module = LAModule::get('Blog_categories');

                $module->row = $blog_category;

                return view('la.blog_categories.edit', [
                    'module' => $module,
                    'view_col' => $module->view_col,
                ])->with('blog_category', $blog_category);
            } else {
                return view('errors.404', [
                    'record_id' => $id,
                    'record_name' => ucfirst('blog_category'),
                ]);
            }
        } else {
            return redirect(config('laraadmin.adminRoute').'/');
        }
    }

    /**
     * Update the specified blog_category in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id blog_category ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        if (LAModule::hasAccess('Blog_categories', 'edit')) {
            $rules = LAModule::validateRules('Blog_categories', $request, true);

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $blog_category_old = BlogCategory::find($id);
            $insert_id = LAModule::updateRow('Blog_categories', $request, $id);
            $blog_category_new = BlogCategory::find($id);

            // Add LALog
            LALog::make('Blog_categories.BLOG_CATEGORY_UPDATED', [
                'title' => 'Blog category Updated',
                'module_id' => 'Blog_categories',
                'context_id' => $blog_category_new->id,
                'content' => [
                    'old' => $blog_category_old,
                    'new' => $blog_category_new
                ],
                'user_id' => Auth::user()->id,
                'notify_to' => '[]'
            ]);

            return redirect()->route(config('laraadmin.adminRoute').'.blog_categories.index');
        } else {
            return redirect(config('laraadmin.adminRoute').'/');
        }
    }

    /**
     * Remove the specified blog_category from storage.
     *
     * @param int $id blog_category ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        if (LAModule::hasAccess('Blog_categories', 'delete')) {
            $blog_category = BlogCategory::find($id);
            $blog_category->delete();

            // Add LALog
            LALog::make('Blog_categories.BLOG_CATEGORY_DELETED', [
                'title' => 'Blog category Deleted',
                'module_id' => 'Blog_categories',
                'context_id' => $blog_category->id,
                'content' => $blog_category,
                'user_id' => Auth::user()->id,
                'notify_to' => '[]'
            ]);

            // Redirecting to index() method
            return redirect()->route(config('laraadmin.adminRoute').'.blog_categories.index');
        } else {
            return redirect(config('laraadmin.adminRoute').'/');
        }
    }

    /**
     * Server side Datatable fetch via Ajax.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function dtajax(Request $request)
    {
        $module = LAModule::get('Blog_categories');
        $listing_cols = LAModule::getListingColumns('Blog_categories');

        $values = DB::table('blog_categories')->select($listing_cols)->whereNull('deleted_at');
        $out = Datatables::of($values)->make();
        $data = $out->getData();

        $fields_popup = LAModuleField::getModuleFields('Blog_categories');

        for ($i = 0; $i < count($data->data); $i++) {
            $blog_category = BlogCategory::find($data->data[$i]->id);

            for ($j = 0; $j < count($listing_cols); $j++) {
                $col = $listing_cols[$j];
                if (isset($fields_popup[$col]) && str_starts_with($fields_popup[$col]->popup_vals, '@')) {
                    if ($col == $module->view_col) {
                        $data->data[$i]->$col = LAModuleField::getFieldValue($fields_popup[$col], $data->data[$i]->$col);
                    } else {
                        $data->data[$i]->$col = LAModuleField::getFieldLink($fields_popup[$col], $data->data[$i]->$col);
                    }
                }
                if ($col == $module->view_col) {
                    $data->data[$i]->$col = '<a '.config('laraadmin.ajaxload').' href="'.url(config('laraadmin.adminRoute').'/blog_categories/'.$data->data[$i]->id).'">'.$data->data[$i]->$col.'</a>';
                }
                // else if($col == "author") {
                //    $data->data[$i]->$col;
                // }
            }

            if ($this->show_action) {
                $output = '';
                if (LAModule::hasAccess('Blog_categories', 'edit')) {
                    $output .= '<a '.config('laraadmin.ajaxload').' href="'.url(config('laraadmin.adminRoute').'/blog_categories/'.$data->data[$i]->id.'/edit').'" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;" data-toggle="tooltip" title="Edit"><i class="fa fa-edit"></i></a>';
                }

                if (LAModule::hasAccess('Blog_categories', 'delete')) {
                    $output .= Form::open(['route' => [config('laraadmin.adminRoute').'.blog_categories.destroy', $data->data[$i]->id], 'method' => 'delete', 'style' => 'display:inline']);
                    $output .= ' <button class="btn btn-danger btn-xs" type="submit" data-toggle="tooltip" title="Delete"><i class="fa fa-times"></i></button>';
                    $output .= Form::close();
                }
                $data->data[$i]->dt_action = (string) $output;
            }
        }
        $out->setData($data);

        return $out;
    }
}
