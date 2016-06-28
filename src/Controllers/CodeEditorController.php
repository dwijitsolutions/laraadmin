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

class CodeEditorController extends Controller
{
    
    public function __construct() {
        // for authentication (optional)
        $this->middleware('auth');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return View('la.editor.index', [
            'no_header' => true,
            'no_padding' => "no-padding",
            'sidebar_mini' => "sidebar-mini sidebar-collapse"
        ]);
    }

    /**
     * Get Directory Files : Jquery File Tree
     * http://jqueryfiletree.github.io
     * @param  string  $dir
     * @return \Illuminate\Http\Response
     */
    public function get_dir(Request $request)
    {

        $root = base_path() . DIRECTORY_SEPARATOR;
        $postDir = rawurldecode(base_path($request->get('dir')));

        if (file_exists($postDir))
        {
            $files = scandir($postDir);
            $returnDir = substr($postDir, strlen($root));
            natcasesort($files);

            if (count($files) > 2)
            { // The 2 accounts for . and ..
                echo "<ul class='jqueryFileTree'>";
                foreach ($files as $file)
                {
                    $htmlRel = htmlentities($returnDir . $file);
                    $htmlName = htmlentities($file);
                    $ext = preg_replace('/^.*\./', '', $file);
                    if (file_exists($postDir . $file) && $file != '.' && $file != '..')
                    {
                        if (is_dir($postDir . $file))
                        {
                            echo "<li class='directory collapsed'><a rel='" . $htmlRel . "/'>" . $htmlName . "</a></li>";
                        }
                        else
                        {
                            echo "<li class='file ext_{$ext}'><a rel='" . $htmlRel . "'>" . $htmlName . "</a></li>";
                        }
                    }
                }
                echo "</ul>";
            }
        }
    }
    
    /**
     * Get file content
     *
     * @return \Illuminate\Http\Response
     */
    public function get_file(Request $request)
    {
        $filepath = $request->input('filepath');
        $data = file_get_contents(base_path($filepath));
        echo $data;
    }
    
    /**
     * Save file content
     *
     * @return \Illuminate\Http\Response
     */
    public function save_file(Request $request)
    {
        $filepath = $request->input('filepath');
        $filedata = $request->input('filedata');
        $data = file_put_contents(base_path($filepath), $filedata);
        return response()->json(['success' => true]);
    }
}
