<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Init;
use App\Helpers\Dbase;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class MasterPatternController extends Controller
{
    private $table = "tm_pattern";
    private $field = "pattern_id";

    public function getAll(Request $request) {
        $limit  = $request->limit;
        $limit  = (($limit == "")?10:$limit);
        $offset = $request->offset;
        $offset = (($offset == "")?0:$offset);

        $cnt = 0;
        $tmp = array();
        if($request->name != "") {
            $tmp = DB::table($this->table)->where('pattern_name', 'like', '%'.$request->name.'%')->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy($this->field)->get();
            $cnt = DB::table($this->table)->where('pattern_name', 'like', '%'.$request->name.'%')->where('is_deleted', 0)->count();
        }
        else {
            $tmp = DB::table($this->table)->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy($this->field)->get();
            $cnt = DB::table($this->table)->where('is_deleted', 0)->count();
        }
        
        $arr = Init::initMasterPattern(1, $tmp);
        $rst = (($cnt > 0)?1:0);
        $data = array(
            'total' => $cnt,
            'lists' => $arr,
        );

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => Init::responseMessage($rst, 'View'),
            'data'      => $data],
            200
        );
    }

    public function getSingle(Request $request) {
        $id   = $request->pattern_id;
        $data = DB::table($this->table)->where($this->field, $id)->get();
        $arr  = Init::initMasterPattern(0, $data);

        return Init::initResponse($arr, 'View');
    }

    public function insertData(Request $request) {
        $name         = $request->name;
        $description  = $request->description;
        $last_user    = $request->last_user;
        
        $validate = Init::initValidate(
            array('name'), 
            array($name)
        );

        if($validate == "") {
            $file = null; $path = "-"; $folder = "pattern"; $size = 0;
            if ($request->hasFile('userfile')) {
                
                $files = $request->file('userfile');
                $original_filename = $files->getClientOriginalName();
                $original_filename_arr = explode('.', $original_filename);
                $file_ext = end($original_filename_arr);
                $size = $files->getSize();
                
                if(($file_ext == "jpg") || ($file_ext == "png") || ($file_ext == "jpeg")) {
                    $image = 'IMG-' . time() . '.' . $file_ext;
                    $destination_path = storage_path('assets/uploads/'. $folder);
                    if ($files->move($destination_path, $image)) {
                        $file = $image;
                        $path = Init::storagePath() ."/". $folder ."/". $file;
                    } 
                }
                else {
                    return response()->json([
                        'status'    => Init::responseStatus(0),
                        'message'   => "Jenis berkas unggahan: jpg, png, jpeg",
                        'data'      => array()],
                        200
                    );
                }
            }
            
            $now = Carbon::now();
            $rst = DB::table($this->table)
                ->insertGetId([
                    "pattern_name"          => $name,
                    "pattern_size"          => (($size == null)? 0:$size),
                    "pattern_image"         => (($file == null)? "":$file),
                    "pattern_path"          => (($file == null)? "":$path),
                    "pattern_description"   => (($description == null)? "":nl2br($description)),
                    "created_at"            => $now,
                    "updated_at"            => $now,
                    "last_user"             => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]);     
            
            dBase::setLogActivity($rst, $last_user, $now, 'Insert', 'Simpan data master gambar pola');      
        }
        else {
            $rst = 0;
        }
        
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'Insert')),
            'data'      => array()],
            200
        );
    }

    public function updateData(Request $request) {
        $id           = $request->pattern_id;
        $status       = $request->status;
        $name         = $request->name;
        $description  = $request->description;
        $last_user    = $request->last_user;
        
        $validate = Init::initValidate(
            array('id', 'name'), 
            array($id, $name)
        );

        if($validate == "") {
            $file = null; $path = "-"; $folder = "pattern"; $size = 0;
            if ($request->hasFile('userfile')) {
                $files = $request->file('userfile');
                $original_filename = $files->getClientOriginalName();
                $original_filename_arr = explode('.', $original_filename);
                $file_ext = end($original_filename_arr);
                $size = $files->getSize();
                
                if(($file_ext == "jpg") || ($file_ext == "png") || ($file_ext == "jpeg")) {
                    $image = 'IMG-' . time() . '.' . $file_ext;
                    $destination_path = storage_path('assets/uploads/'. $folder);
                    if ($files->move($destination_path, $image)) {
                        $file = $image;
                        $path = Init::storagePath() ."/". $folder ."/". $file;

                        $old_image = $request->pattern_image;
                        if($old_image != "") {
                            //unlink($destination_path ."/". $old_image); 
                        }
                    } 
                }
                else {
                    return response()->json([
                        'status'    => Init::responseStatus(0),
                        'message'   => "Jenis berkas unggahan: jpg, png, jpeg",
                        'data'      => array()],
                        200
                    );
                }
            }
            else {
                $size = $request->pattern_size;
                $file = $request->pattern_image;
                $path = Init::storagePath() ."/". $folder ."/". $file;
            }

            $now = Carbon::now();
            $rst = DB::table($this->table)
                ->where($this->field, $id)
                ->update([
                    "pattern_status"        => $status,
                    "pattern_name"          => $name,
                    "pattern_size"          => (($size == null)? 0:$size),
                    "pattern_image"         => (($file == null)? "":$file),
                    "pattern_path"          => (($file == null)? "":$path),
                    "pattern_description"   => (($description == null)? "":nl2br($description)),
                    "pattern_status"        => (($status == 1)? 1:0),
                    "updated_at"            => $now,
                    "last_user"             => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]);  

            dBase::setLogActivity($rst, $last_user, $now, 'Update', 'Ubah data master gambar pola');      
        }
        else {
            $rst = 0;
        }
         
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'Update')),
            'data'      => array()],
            200
        );
    }

    public function deleteData(Request $request) {
        $id         = $request->pattern_id;
        $last_user  = $request->last_user;

        $validate = Init::initValidate(
            array('id'), 
            array($id)
        );

        if($validate == "") {
            $now = Carbon::now();
            $rst = DB::table($this->table)
                ->where($this->field, $id)
                ->update([
                    "pattern_status"   => 0,
                    "is_deleted"       => 1,
                    "updated_at"       => $now,
                    "last_user"        => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]); 

            dBase::setLogActivity($rst, $last_user, $now, 'Delete', 'Hapus data master gambar pola');      
        }
        else {
            $rst = 0;
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'Delete')),
            'data'      => array()],
            200
        );
    }
}