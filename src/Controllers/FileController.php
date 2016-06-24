<?php
/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace Dwij\Laraadmin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \Illuminate\Http\Response;
use App\Http\Requests;
use File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{
    
    public function __construct() {
        // for authentication (optional)
        $this->middleware('auth');
    }
    
    /**
     * Get all files from folder
     *
     * @return \Illuminate\Http\Response
     */
    public function get_folder_files($folder_name)
    {
        $files = array();
        if(file_exists(public_path($folder_name))) {
            $filesArr = File::allFiles(public_path($folder_name));
            foreach ($filesArr as $file) {
                $files[] = $file->getfilename();
            }
        }
        return response()->json(['folder_name' => $folder_name, 'files' => $files]);
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
            
            $directory = public_path('/uploads');
            $filename = $file->getClientOriginalName();

            $upload_success = Input::file('file')->move($directory, $filename);
            
            if( $upload_success ) {
                return response()->json('success', 200);
            } else {
                return response()->json('error', 400);
            }
        } else {
            return response()->json('error: upload file not found.', 400);
        }
    }
}
