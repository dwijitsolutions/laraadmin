<?php
/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Requests;
use Auth;
use DB;
use File;
use Illuminate\Support\Facades\Input;
use Validator;
use Datatables;
use Collective\Html\FormFacade as Form;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Helpers\LAHelper;

use App\Upload;

class UploadsController extends Controller
{
    public $show_action = true;
    public $view_col = 'name';
    public $listing_cols = ['id', 'name', 'path', 'extension', 'caption', 'user'];
    
    public function __construct() {
        // for authentication (optional)
        // $this->middleware('auth');
        $this->middleware('auth', ['except' => 'get_file']);
    }
    
    /**
     * Display a listing of the Uploads.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $module = Module::get('Uploads');
        
        return View('la.uploads.index', [
            'show_actions' => $this->show_action,
            'listing_cols' => $this->listing_cols,
            'module' => $module
        ]);
    }

    /**
     * Get file
     *
     * @return \Illuminate\Http\Response
     */
    public function get_file($hash, $name)
    {
        $upload = Upload::where("hash", $hash)->first();

        // Validate Upload Hash & Filename
        if(!isset($upload->id) || $upload->name != $name) {
            return response()->json([
                'status' => "failure",
                'message' => "Unauthorized Access 1"
            ]);
        }

        // Validate if Image is Public
        if(!isset($upload->public) || !isset(Auth::user()->id)) {
            return response()->json([
                'status' => "failure",
                'message' => "Unauthorized Access 2"
            ]);
        }

        if($upload->public || Auth::user()->hasRole("Super Admin") || Auth::user()->id == $upload->user_id) {
            
            $path = $upload->path;

            if(!File::exists($path))
                abort(404);
            
            // Check if thumbnail
            $size = Input::get('s');
            if(isset($size)) {
                if(!is_numeric($size)) {
                    $size = 150;
                }
                $thumbpath = storage_path("thumbnails/".basename($upload->path)."-".$size."x".$size);
                
                if(File::exists($thumbpath)) {
                    $path = $thumbpath;
                } else {
                    // Create Thumbnail
                    LAHelper::createThumbnail($upload->path, $thumbpath, $size, $size, "transparent");
                    $path = $thumbpath;
                }
            }

            $file = File::get($path);
            $type = File::mimeType($path);

            $download = Input::get('download');
            if(isset($download)) {
                return response()->download($path, $upload->name);
            } else {
                $response = FacadeResponse::make($file, 200);
                $response->header("Content-Type", $type);
            }
            
            return $response;
        } else {
            return response()->json([
                'status' => "failure",
                'message' => "Unauthorized Access 3"
            ]);
        }
    }

    /**
     * Upload fiels via DropZone.js
     *
     * @return \Illuminate\Http\Response
     */
    public function upload_files() {
        
        $input = Input::all();
        
        if(Input::hasFile('file')) {
            /*
            $rules = array(
                'file' => 'mimes:jpg,jpeg,bmp,png,pdf|max:3000',
            );
            $validation = Validator::make($input, $rules);
            if ($validation->fails()) {
                return response()->json($validation->errors()->first(), 400);
            }
            */
            $file = Input::file('file');
            
            // print_r($file);
            
            $folder = storage_path('uploads');
            $filename = $file->getClientOriginalName();

            $date_append = date("Y-m-d-His-");
            $upload_success = Input::file('file')->move($folder, $date_append.$filename);
            
            if( $upload_success ) {
                $upload = Upload::create([
                    "name" => $filename,
                    "path" => $folder.DIRECTORY_SEPARATOR.$date_append.$filename,
                    "extension" => pathinfo($filename, PATHINFO_EXTENSION),
                    "caption" => "",
                    "public" => config("laraadmin.uploads.default_public"),
                    "user_id" => Auth::user()->id
                ]);
                // apply unique random hash to file
                while(true) {
                    $hash = strtolower(str_random(20));
                    if(!Upload::where("hash", $hash)->count()) {
                        $upload->hash = $hash;
                        break;
                    }
                }
                $upload->save();

                return response()->json([
                    "status" => "success",
                    "upload" => $upload
                ], 200);
            } else {
                return response()->json([
                    "status" => "error"
                ], 400);
            }
        } else {
            return response()->json('error: upload file not found.', 400);
        }
    }

    /**
     * Get all files from uploads folder
     *
     * @return \Illuminate\Http\Response
     */
    public function uploaded_files()
    {
        $uploads = array();

        // print_r(Auth::user()->roles);
        if(Auth::user()->hasRole("Super Admin")) {
            $uploads = Upload::all();
        } else {
            if(config('laraadmin.uploads.private_uploads')) {
                // Upload::where('user_id', 0)->get();
                $uploads = Auth::user()->uploads;
            } else {
                $uploads = Upload::all();
            }
        }
        $uploads2 = array();
        foreach ($uploads as $upload) {
            $u = (object) array();
            $u->id = $upload->id;
            $u->name = $upload->name;
            $u->extension = $upload->extension;
            $u->hash = $upload->hash;
            $u->public = $upload->public;
            $u->caption = $upload->caption;
            $u->user = $upload->user->name;
            
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
    }

    /**
     * Update Uploads Caption
     *
     * @return \Illuminate\Http\Response
     */
    public function update_caption()
    {
        $file_id = Input::get('file_id');
        $caption = Input::get('caption');
        
        $upload = Upload::find($file_id)->first();
        if(isset($upload->id)) {
            if($upload->user_id == Auth::user()->id || Auth::user()->hasRole("Super Admin")) {

                // Update Caption
                $upload->caption = $caption;
                $upload->save();

                return response()->json([
                    'status' => "success"
                ]);

            } else {
                return response()->json([
                    'status' => "failure",
                    'message' => "Upload not found"
                ]);
            }
        } else {
            return response()->json([
                'status' => "failure",
                'message' => "Upload not found"
            ]);
        }
    }

    /**
     * Update Uploads Filename
     *
     * @return \Illuminate\Http\Response
     */
    public function update_filename()
    {
        $file_id = Input::get('file_id');
        $filename = Input::get('filename');
        
        $upload = Upload::find($file_id)->first();
        if(isset($upload->id)) {
            if($upload->user_id == Auth::user()->id || Auth::user()->hasRole("Super Admin")) {

                // Update Caption
                $upload->name = $filename;
                $upload->save();

                return response()->json([
                    'status' => "success"
                ]);

            } else {
                return response()->json([
                    'status' => "failure",
                    'message' => "Unauthorized Access 1"
                ]);
            }
        } else {
            return response()->json([
                'status' => "failure",
                'message' => "Upload not found"
            ]);
        }
    }

    /**
     * Update Uploads Public Visibility
     *
     * @return \Illuminate\Http\Response
     */
    public function update_public()
    {
        $file_id = Input::get('file_id');
        $public = Input::get('public');
        if(isset($public)) {
            $public = true;
        } else {
            $public = false;
        }
        
        $upload = Upload::find($file_id)->first();
        if(isset($upload->id)) {
            if($upload->user_id == Auth::user()->id || Auth::user()->hasRole("Super Admin")) {

                // Update Caption
                $upload->public = $public;
                $upload->save();

                return response()->json([
                    'status' => "success"
                ]);

            } else {
                return response()->json([
                    'status' => "failure",
                    'message' => "Unauthorized Access 1"
                ]);
            }
        } else {
            return response()->json([
                'status' => "failure",
                'message' => "Upload not found"
            ]);
        }
    }

    /**
     * Remove the specified upload from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function delete_file()
    {
        $file_id = Input::get('file_id');
        
        $upload = Upload::find($file_id)->first();
        if(isset($upload->id)) {
            if($upload->user_id == Auth::user()->id || Auth::user()->hasRole("Super Admin")) {

                // Update Caption
                $upload->delete();

                return response()->json([
                    'status' => "success"
                ]);

            } else {
                return response()->json([
                    'status' => "failure",
                    'message' => "Unauthorized Access 1"
                ]);
            }
        } else {
            return response()->json([
                'status' => "failure",
                'message' => "Upload not found"
            ]);
        }
    }
}
