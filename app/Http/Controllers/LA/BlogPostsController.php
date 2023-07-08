<?php
/**
 * Controller generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is Proprietary Software created by Dwij IT Solutions. Use of LaraAdmin requires Paid Licence issued by Dwij IT Solutions.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Collective\Html\FormFacade as Form;
use App\Helpers\LAHelper;
use App\Models\LAModule;
use App\Models\LAModuleField;
use App\Models\LALog;

use App\Models\BlogPost;
use App\Models\BlogCategory;

class BlogPostsController extends Controller
{
    public $show_action = true;

    /**
     * Display a listing of the Blog_posts.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $module = LAModule::get('Blog_posts');

        if (LAModule::hasAccess($module->id)) {
            if ($request->ajax() && !isset($request->_pjax)) {
                // TODO: Implement good Query Builder
                return BlogPost::all();
            } else {
                return View('la.blog_posts.index', [
                    'show_actions' => $this->show_action,
                    'listing_cols' => LAModule::getListingColumns('Blog_posts'),
                    'module' => $module
                ]);
            }
        } else {
            if ($request->ajax() && !isset($request->_pjax)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized Access'
                ], 403);
            } else {
                return redirect(config('laraadmin.adminRoute') . "/");
            }
        }
    }

    /**
     * Show the form for creating a new blog_post.
     *
     * @return mixed
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created blog_post in database.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        if (LAModule::hasAccess("Blog_posts", "create")) {
            if ($request->ajax() && !isset($request->quick_add)) {
                $request->merge((array)json_decode($request->getContent()));
            }
            $rules = LAModule::validateRules("Blog_posts", $request);

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                if ($request->ajax() || isset($request->quick_add)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Validation error',
                        'errors' => $validator->messages()
                    , 400]);
                } else {
                    return redirect()->back()->withErrors($validator)->withInput();
                }
            }

            $insert_id = LAModule::insert("Blog_posts", $request);

            $blog_post = BlogPost::find($insert_id);

            // Add LALog
            LALog::make("Blog_posts.BLOG_POST_CREATED", [
                'title' => "Blog post Created",
                'module_id' => 'Blog_posts',
                'context_id' => $blog_post->id,
                'content' => $blog_post,
                'user_id' => Auth::user()->id,
                'notify_to' => "[]"
            ]);

            if ($request->ajax() || isset($request->quick_add)) {
                return response()->json([
                    'status' => 'success',
                    'object' => $blog_post,
                    'message' => 'BlogPost updated successfully!',
                    'redirect' => url(config('laraadmin.adminRoute') . '/blog_posts')
                ], 201);
            } else {
                return redirect()->route(config('laraadmin.adminRoute') . '.blog_posts.index');
            }
        } else {
            if ($request->ajax() || isset($request->quick_add)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized Access'
                ], 403);
            } else {
                return redirect(config('laraadmin.adminRoute') . "/");
            }
        }
    }

    /**
     * Display the specified blog_post.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id blog_post ID
     * @return mixed
     */
    public function show(Request $request, $id)
    {
        if (LAModule::hasAccess("Blog_posts", "view")) {
            $blog_post = BlogPost::find($id);
            if (isset($blog_post->id)) {
                if ($request->ajax() && !isset($request->_pjax)) {
                    return $blog_post;
                } else {
                    $module = LAModule::get('Blog_posts');
                    $module->row = $blog_post;

                    return view('la.blog_posts.show', [
                        'module' => $module,
                        'view_col' => $module->view_col,
                        'no_header' => true,
                        'no_padding' => "no-padding"
                    ])->with('blog_post', $blog_post);
                }
            } else {
                if ($request->ajax() && !isset($request->_pjax)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Record not found'
                    ], 404);
                } else {
                    return view('errors.404', [
                        'record_id' => $id,
                        'record_name' => ucfirst("blog_post"),
                    ]);
                }
            }
        } else {
            if ($request->ajax() && !isset($request->_pjax)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized Access'
                ], 403);
            } else {
                return redirect(config('laraadmin.adminRoute') . "/");
            }
        }
    }

    /**
     * Show the form for editing the specified blog_post.
     *
     * @param int $id blog_post ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        if (LAModule::hasAccess("Blog_posts", "edit")) {
            $blog_post = BlogPost::find($id);
            if (isset($blog_post->id)) {
                $module = LAModule::get('Blog_posts');

                $module->row = $blog_post;

                return view('la.blog_posts.edit', [
                    'module' => $module,
                    'view_col' => $module->view_col,
                ])->with('blog_post', $blog_post);
            } else {
                return view('errors.404', [
                    'record_id' => $id,
                    'record_name' => ucfirst("blog_post"),
                ]);
            }
        } else {
            return redirect(config('laraadmin.adminRoute') . "/");
        }
    }

    /**
     * Update the specified blog_post in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id blog_post ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        if (LAModule::hasAccess("Blog_posts", "edit")) {
            if ($request->ajax()) {
                $request->merge((array)json_decode($request->getContent()));
            }
            $rules = LAModule::validateRules("Blog_posts", $request, true);

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Validation error',
                        'errors' => $validator->messages()
                    ], 400);
                } else {
                    return redirect()->back()->withErrors($validator)->withInput();
                }
            }

            $blog_post_old = BlogPost::find($id);

            if (isset($blog_post_old->id)) {
                // Update Data
                LAModule::updateRow("Blog_posts", $request, $id);

                $blog_post_new = BlogPost::find($id);

                // Add LALog
                LALog::make("Blog_posts.BLOG_POST_UPDATED", [
                    'title' => "Blog post Updated",
                    'module_id' => 'Blog_posts',
                    'context_id' => $blog_post_new->id,
                    'content' => [
                        'old' => $blog_post_old,
                        'new' => $blog_post_new
                    ],
                    'user_id' => Auth::user()->id,
                    'notify_to' => "[]"
                ]);

                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'success',
                        'object' => $blog_post_new,
                        'message' => 'BlogPost updated successfully!',
                        'redirect' => url(config('laraadmin.adminRoute') . '/blog_posts')
                    ], 200);
                } else {
                    return redirect()->route(config('laraadmin.adminRoute') . '.blog_posts.index');
                }
            } else {
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Record not found'
                    ], 404);
                } else {
                    return view('errors.404', [
                        'record_id' => $id,
                        'record_name' => ucfirst("blog_post"),
                    ]);
                }
            }
        } else {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized Access'
                ], 403);
            } else {
                return redirect(config('laraadmin.adminRoute') . "/");
            }
        }
    }

    /**
     * Remove the specified blog_post from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id blog_post ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $id)
    {
        if (LAModule::hasAccess("Blog_posts", "delete")) {
            $blog_post = BlogPost::find($id);
            if (isset($blog_post->id)) {
                $blog_post->delete();

                // Add LALog
                LALog::make("Blog_posts.BLOG_POST_DELETED", [
                    'title' => "Blog post Deleted",
                    'module_id' => 'Blog_posts',
                    'context_id' => $blog_post->id,
                    'content' => $blog_post,
                    'user_id' => Auth::user()->id,
                    'notify_to' => "[]"
                ]);

                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Record Deleted successfully!',
                        'redirect' => url(config('laraadmin.adminRoute') . '/blog_posts')
                    ], 204);
                } else {
                    return redirect()->route(config('laraadmin.adminRoute') . '.blog_posts.index');
                }
            } else {
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Record not found'
                    ], 404);
                } else {
                    return redirect()->route(config('laraadmin.adminRoute') . '.blog_posts.index');
                }
            }
        } else {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized Access'
                ], 403);
            } else {
                return redirect(config('laraadmin.adminRoute') . "/");
            }
        }
    }

    /**
     * Server side Datatable fetch via Ajax
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function dtajax(Request $request)
    {
        $module = LAModule::get('Blog_posts');
        $listing_cols = LAModule::getListingColumns('Blog_posts');

        $values = DB::table('blog_posts')->select($listing_cols)->whereNull('deleted_at');
        $out = Datatables::of($values)->make();
        $data = $out->getData();

        $fields_popup = LAModuleField::getModuleFields('Blog_posts');

        for ($i = 0; $i < count($data->data); $i++) {
            $blog_post = BlogPost::find($data->data[$i]->id);

            for ($j = 0; $j < count($listing_cols); $j++) {
                $col = $listing_cols[$j];
                if (isset($fields_popup[$col]) && str_starts_with($fields_popup[$col]->popup_vals, "@")) {
                    if ($col == $module->view_col) {
                        $data->data[$i]->$col = LAModuleField::getFieldValue($fields_popup[$col], $data->data[$i]->$col);
                    } else {
                        $data->data[$i]->$col = LAModuleField::getFieldLink($fields_popup[$col], $data->data[$i]->$col);
                    }
                }
                if ($col == $module->view_col) {
                    $data->data[$i]->$col = '<a '.config('laraadmin.ajaxload').' href="' . url(config('laraadmin.adminRoute') . '/blog_posts/' . $data->data[$i]->id) . '">' . $data->data[$i]->$col . '</a>';
                }
                // else if($col == "author") {
                //    $data->data[$i]->$col;
                // }
            }

            if ($this->show_action) {
                $output = '';
                if (LAModule::hasAccess("Blog_posts", "edit")) {
                    $output .= '<a '.config('laraadmin.ajaxload').' href="' . url(config('laraadmin.adminRoute') . '/blog_posts/' . $data->data[$i]->id . '/edit') . '" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;" data-toggle="tooltip" title="Edit"><i class="fa fa-edit"></i></a>';
                }

                if (LAModule::hasAccess("Blog_posts", "delete")) {
                    $output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.blog_posts.destroy', $data->data[$i]->id], 'method' => 'delete', 'style' => 'display:inline']);
                    $output .= ' <button class="btn btn-danger btn-xs" type="submit" data-toggle="tooltip" title="Delete"><i class="fa fa-times"></i></button>';
                    $output .= Form::close();
                }
                $data->data[$i]->dt_action = (string)$output;
            }
        }
        $out->setData($data);
        return $out;
    }

    /**
     * Show the blog post.
     *
     * @return mixed
     */
    public function show_blog()
    {
        $posts = BlogPost::where("status", "Published")->orderBy("post_date", "desc")->get();
        $recent_posts = BlogPost::where("status", "Published")->orderBy("post_date", "desc")->limit(3)->get();
        $categories = BlogCategory::all();
        return view('blog.blog', [
            'posts' => $posts,
            'recent_posts' => $recent_posts,
            'categories' => $categories
        ]);
    }

    /**
     * Show the blog category
     *
     * @return mixed
     */
    public function show_category($url)
    {
        $category = BlogCategory::where("url", $url)->first();
        $posts = BlogPost::where("status", "Published")->where("category_id", $category->id)->orderBy("post_date", "desc")->get();
        $recent_posts = BlogPost::where("status", "Published")->orderBy("post_date", "desc")->limit(3)->get();
        $categories = BlogCategory::all();
        if (isset($category->id)) {
            return view('blog.category', [
                'category' => $category,
                'posts' => $posts,
                'recent_posts' => $recent_posts,
                'categories' => $categories
            ]);
        } else {
            return view('errors.404', [
                'record_id' => $url,
                'record_name' => ucfirst("blog category"),
            ]);
        }
    }

    /**
     * Show the blog post.
     *
     * @return mixed
     */
    public function show_post($url)
    {
        $post = BlogPost::where("url", $url)->first();
        if (isset($post->id)) {
            $recent_posts = BlogPost::where("status", "Published")->orderBy("post_date", "desc")->limit(3)->get();
            return view('blog.post', [
                'post' => $post,
                'recent_posts' => $recent_posts
            ]);
        } else {
            return view('errors.404', [
                'record_id' => $url,
                'record_name' => ucfirst("blog post"),
            ]);
        }
    }
}
