<?php
/***
 * Controller generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Http\Controllers\LA;

use App\Helpers\LAHelper;
use App\Http\Controllers\Controller;
use App\Models\LALog;
use App\Models\LAModule;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response as FacadeResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laraadmin\Entrust\EntrustFacade as Entrust;

class UploadsController extends Controller
{
    public $show_action = true;

    public function __construct()
    {
        // for authentication (optional)
        $this->middleware('auth', ['except' => 'get_file']);
    }

    /**
     * Display a listing of the Uploads.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $module = LAModule::get('Uploads');

        if (LAModule::hasAccess($module->id)) {
            return View('la.uploads.index', [
                'show_actions' => $this->show_action,
                'module' => $module
            ]);
        } else {
            return redirect(config('laraadmin.adminRoute').'/');
        }
    }

    /**
     * Get file.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_file(Request $request, $hash, $name)
    {
        $upload = Upload::where('hash', $hash)->first();

        // Validate Upload Hash & Filename
        if (! isset($upload->id) || $upload->name != $name) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Unauthorized Access 1'
            ]);
        }

        if ($upload->public == 1) {
            $upload->public = true;
        } else {
            $upload->public = false;
        }

        // Validate if Image is Public
        if (! $upload->public && ! isset(Auth::user()->id)) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Unauthorized Access 2',
            ]);
        }

        if ($upload->public || Entrust::hasRole('SUPER_ADMIN') || Auth::user()->id == $upload->user_id) {
            $path = $upload->path;

            if (! File::exists($path)) {
                abort(404);
            }

            // Check if thumbnail
            $size = $request->input('s');
            if (isset($size)) {
                if (! is_numeric($size)) {
                    $size = 150;
                }
                $thumbpath = storage_path('thumbnails/'.basename($upload->path).'-'.$size.'x'.$size);

                if (File::exists($thumbpath)) {
                    $path = $thumbpath;
                } else {
                    // Create Thumbnail
                    LAHelper::createThumbnail($upload->path, $thumbpath, $size, $size, 'transparent');
                    $path = $thumbpath;
                }
            }

            $file = File::get($path);
            $type = File::mimeType($path);

            $download = $request->input('download');
            if (isset($download)) {
                return response()->download($path, $upload->name);
            } else {
                $response = FacadeResponse::make($file, 200);
                $response->header('Content-Type', $type);
            }

            return $response;
        } else {
            return response()->json([
                'status' => 'failure',
                'message' => 'Unauthorized Access 3'
            ]);
        }
    }

    /**
     * Upload fiels via DropZone.js.
     *
     * @return \Illuminate\Http\Response
     */
    public function upload_files(Request $request)
    {
        if (LAModule::hasAccess('Uploads', 'create')) {
            if ($request->hasFile('file')) {
                /*
                $rules = array(
                    'file' => 'mimes:jpg,jpeg,bmp,png,pdf|max:3000',
                );
                $validation = Validator::make($input, $rules);
                if ($validation->fails()) {
                    return response()->json($validation->errors()->first(), 400);
                }
                */
                $file = $request->file('file');

                // print_r($file);

                $folder = storage_path('uploads');
                $filename = $file->getClientOriginalName();

                $date_append = date('Y-m-d-His-');
                $upload_success = $request->file('file')->move($folder, $date_append.$filename);

                if ($upload_success) {
                    // Get public preferences
                    // config("laraadmin.uploads.default_public")
                    $public = $request->input('public');
                    if (isset($public)) {
                        $public = true;
                    } else {
                        $public = false;
                    }

                    $upload = Upload::create([
                        'name' => $filename,
                        'path' => $folder.DIRECTORY_SEPARATOR.$date_append.$filename,
                        'extension' => pathinfo($filename, PATHINFO_EXTENSION),
                        'caption' => '',
                        'hash' => '',
                        'public' => $public,
                        'user_id' => Auth::user()->id
                    ]);
                    // apply unique random hash to file
                    while (true) {
                        $hash = strtolower(Str::random(20));
                        if (! Upload::where('hash', $hash)->count()) {
                            $upload->hash = $hash;
                            break;
                        }
                    }
                    $upload->save();

                    // Add LALog
                    LALog::make('Uploads.UPLOAD_CREATED', [
                        'title' => 'Upload Created',
                        'module_id' => 'Uploads',
                        'context_id' => $upload->id,
                        'content' => $upload,
                        'user_id' => Auth::user()->id,
                        'notify_to' => '[]'
                    ]);

                    return response()->json([
                        'status' => 'success',
                        'upload' => $upload
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 'error'
                    ], 400);
                }
            } else {
                return response()->json('error: upload file not found.', 400);
            }
        } else {
            return response()->json([
                'status' => 'failure',
                'message' => 'Unauthorized Access'
            ]);
        }
    }

    /**
     * Get all files from uploads folder.
     *
     * @return \Illuminate\Http\Response
     */
    public function uploaded_files()
    {
        if (LAModule::hasAccess('Uploads', 'view')) {
            $uploads = [];

            // print_r(Auth::user()->roles);
            if (Entrust::hasRole('SUPER_ADMIN')) {
                $uploads = Upload::orderBy('updated_at', 'desc')->get();
            } else {
                if (config('laraadmin.uploads.private_uploads')) {
                    // Upload::where('user_id', 0)->first();
                    $uploads = Auth::user()->uploads;
                } else {
                    $uploads = Upload::all();
                }
            }
            $uploads2 = [];
            foreach ($uploads as $upload) {
                $u = (object) [];
                $u->id = $upload->id;
                $u->name = $upload->name;
                $u->extension = $upload->extension;
                $u->hash = $upload->hash;
                $u->public = $upload->public;
                $u->caption = $upload->caption;
                if (isset($upload->user->id)) {
                    $u->user = $upload->user->name;
                }

                $uploads2[] = $u;
            }

            // $folder = storage_path('/uploads');
            // $files = array();
            // if(file_exists($folder)) {
            //     $filesArr = File::allFiles($folder);
            //     foreach ($filesArr as $file) {
            //         $files[] = $file->getfilename();
            //     }
            // }
            // return response()->json(['files' => $files]);
            return response()->json(['uploads' => $uploads2]);
        } else {
            return response()->json([
                'status' => 'failure',
                'message' => 'Unauthorized Access'
            ]);
        }
    }

    /**
     * Update Uploads Caption.
     *
     * @return \Illuminate\Http\Response
     */
    public function update_caption(Request $request)
    {
        if (LAModule::hasAccess('Uploads', 'edit')) {
            $file_id = $request->input('file_id');
            $caption = $request->input('caption');

            $upload_old = Upload::find($file_id);
            $upload = Upload::find($file_id);
            if (isset($upload->id)) {
                if ($upload->user_id == Auth::user()->id || Entrust::hasRole('SUPER_ADMIN')) {
                    // Update Caption
                    $upload->caption = $caption;
                    $upload->save();

                    // Add LALog
                    LALog::make('Uploads.UPLOAD_UPDATED', [
                        'title' => 'Upload Caption Updated',
                        'module_id' => 'Uploads',
                        'context_id' => $upload->id,
                        'content' => [
                            'old' => $upload_old,
                            'new' => $upload
                        ],
                        'user_id' => Auth::user()->id,
                        'notify_to' => '[]'
                    ]);

                    return response()->json([
                        'status' => 'success'
                    ]);
                } else {
                    return response()->json([
                        'status' => 'failure',
                        'message' => 'Upload not found'
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'failure',
                    'message' => 'Upload not found'
                ]);
            }
        } else {
            return response()->json([
                'status' => 'failure',
                'message' => 'Unauthorized Access'
            ]);
        }
    }

    /**
     * Update Uploads Filename.
     *
     * @return \Illuminate\Http\Response
     */
    public function update_filename(Request $request)
    {
        if (LAModule::hasAccess('Uploads', 'edit')) {
            $file_id = $request->input('file_id');
            $filename = $request->input('filename');

            $upload_old = Upload::find($file_id);
            $upload = Upload::find($file_id);

            $folder = storage_path('uploads');
            $date_append = date('Y-m-d-His-');
            if (isset($upload->id)) {
                if ($upload->user_id == Auth::user()->id || Entrust::hasRole('SUPER_ADMIN')) {
                    $basename = basename($upload_old->path, $upload_old->extension);
                    rename($folder.DIRECTORY_SEPARATOR.$basename.$upload_old->extension, $folder.DIRECTORY_SEPARATOR.$date_append.$filename);

                    // Update filename
                    $upload->name = $filename;
                    $upload->path = $folder.DIRECTORY_SEPARATOR.$date_append.$filename;
                    $upload->save();

                    // Add LALog
                    LALog::make('Uploads.UPLOAD_UPDATED', [
                        'title' => 'Upload filename Updated',
                        'module_id' => 'Uploads',
                        'context_id' => $upload->id,
                        'content' => [
                            'old' => $upload_old,
                            'new' => $upload
                        ],
                        'user_id' => Auth::user()->id,
                        'notify_to' => '[]'
                    ]);

                    return response()->json([
                        'status' => 'success'
                    ]);
                } else {
                    return response()->json([
                        'status' => 'failure',
                        'message' => 'Unauthorized Access 1'
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'failure',
                    'message' => 'Upload not found'
                ]);
            }
        } else {
            return response()->json([
                'status' => 'failure',
                'message' => 'Unauthorized Access'
            ]);
        }
    }

    /**
     * Update Uploads Public Visibility.
     *
     * @return \Illuminate\Http\Response
     */
    public function update_public(Request $request)
    {
        if (LAModule::hasAccess('Uploads', 'edit')) {
            $file_id = $request->input('file_id');
            $public = $request->input('public');
            if (isset($public)) {
                $public = true;
            } else {
                $public = false;
            }

            $upload_old = Upload::find($file_id);
            $upload = Upload::find($file_id);
            if (isset($upload->id)) {
                if ($upload->user_id == Auth::user()->id || Entrust::hasRole('SUPER_ADMIN')) {
                    // Update Caption
                    $upload->public = $public;
                    $upload->save();

                    // Add LALog
                    if ($public != $upload_old->public) {
                        LALog::make('Uploads.UPLOAD_UPDATED', [
                            'title' => 'Upload is_public Updated',
                            'module_id' => 'Uploads',
                            'context_id' => $upload->id,
                            'content' => [
                                'old' => $upload_old,
                                'new' => $upload
                            ],
                            'user_id' => Auth::user()->id,
                            'notify_to' => '[]'
                        ]);
                    }

                    return response()->json([
                        'status' => 'success'
                    ]);
                } else {
                    return response()->json([
                        'status' => 'failure',
                        'message' => 'Unauthorized Access 1'
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'failure',
                    'message' => 'Upload not found'
                ]);
            }
        } else {
            return response()->json([
                'status' => 'failure',
                'message' => 'Unauthorized Access'
            ]);
        }
    }

    /**
     * Remove the specified upload from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function delete_file(Request $request)
    {
        if (LAModule::hasAccess('Uploads', 'delete')) {
            $file_id = $request->input('file_id');

            $upload = Upload::find($file_id);
            if (isset($upload->id)) {
                if ($upload->user_id == Auth::user()->id || Entrust::hasRole('SUPER_ADMIN')) {
                    // Update Caption
                    $upload->delete();

                    // Add LALog
                    LALog::make('Uploads.UPLOAD_DELETED', [
                        'title' => 'Upload Deleted',
                        'module_id' => 'Uploads',
                        'context_id' => $upload->id,
                        'content' => $upload,
                        'user_id' => Auth::user()->id,
                        'notify_to' => '[]'
                    ]);

                    return response()->json([
                        'status' => 'success'
                    ]);
                } else {
                    return response()->json([
                        'status' => 'failure',
                        'message' => 'Unauthorized Access 1'
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'failure',
                    'message' => 'Upload not found'
                ]);
            }
        } else {
            return response()->json([
                'status' => 'failure',
                'message' => 'Unauthorized Access'
            ]);
        }
    }

    public function update_local_upload_paths()
    {
        Upload::update_local_upload_paths();

        return redirect(config('laraadmin.adminRoute').'/uploads');
    }
}
