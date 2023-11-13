<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Init;
use App\Helpers\Dbase;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class MasterTutorialController extends Controller
{
    private $table = "tm_tutorial";
    private $field = "tutorial_id";

    public function getAll(Request $request) {
        $limit  = $request->limit;
        $limit  = (($limit == "")?10:$limit);
        $offset = $request->offset;
        $offset = (($offset == "")?0:$offset);

        $cnt = 0;
        $tmp = array();
        if($request->name != "") {
            $tmp = DB::table($this->table)->where('tutorial_name', 'like', '%'.$request->name.'%')->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy($this->field)->get();
            $cnt = DB::table($this->table)->where('tutorial_name', 'like', '%'.$request->name.'%')->where('is_deleted', 0)->count();
        }
        else {
            $tmp = DB::table($this->table)->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy($this->field)->get();
            $cnt = DB::table($this->table)->where('is_deleted', 0)->count();
        }
        
        $arr = Init::initMasterTutorial(1, $tmp);
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
        $id   = $request->tutorial_id;
        $data = DB::table($this->table)->where($this->field, $id)->get();
        $arr  = Init::initMasterTutorial(0, $data);

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
            $exist = DB::table($this->table)->where('tutorial_name', $request->name)->where('is_deleted', 0)->count();
            if($exist <= 0) {
                $file = null; $path = "-"; $folder = "pdf"; $size = 0;
                if ($request->hasFile('userfile')) { 
                    $files = $request->file('userfile');
                    $original_filename = $files->getClientOriginalName();
                    $original_filename_arr = explode('.', $original_filename);
                    $file_ext = end($original_filename_arr);
                    $size = $files->getSize();
                    
                    if($file_ext == "pdf") {
                        $image = 'PDF-' . time() . '.' . $file_ext;
                        $destination_path = storage_path('assets/uploads/'. $folder);
                        if ($files->move($destination_path, $image)) {
                            $file = $image;
                            $path = Init::storagePath() ."/". $folder ."/". $file;
                        } 
                    }
                    else {
                        return response()->json([
                            'status'    => Init::responseStatus(0),
                            'message'   => "Jenis berkas unggahan: pdf",
                            'data'      => array()],
                            200
                        );
                    }
                }

                $now = Carbon::now();
                $rst = DB::table($this->table)
                    ->insertGetId([
                        "tutorial_name"         => $name,
                        "tutorial_file"         => $file,
                        "tutorial_path"         => $path,
                        "tutorial_description"  => (($description == null)? "":nl2br($description)),
                        "created_at"            => $now,
                        "updated_at"            => $now,
                        "last_user"             => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                    ]);   
            
                dBase::setLogActivity($rst, $last_user, $now, 'Insert', 'Simpan data master dokumen panduan');      
            }
            else {
                $rst = 0;
                $validate = "Data sudah ada";
            }
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
        $id           = $request->tutorial_id;
        $status       = $request->status;
        $name         = $request->name;
        $description  = $request->description;
        $last_user    = $request->last_user;
        
        $validate = Init::initValidate(
            array('id', 'name'), 
            array($id, $name)
        );

        if($validate == "") {
            $exist = 0;
            $old_name = DB::table($this->table)->where($this->field, $id)->first('tutorial_name');
            if($old_name->tutorial_name != $name) {
                $exist = DB::table($this->table)->where('tutorial_name', $request->name)->where('is_deleted', 0)->count();
            }
            
            if($exist <= 0) {
                $file = null; $path = "-"; $folder = "pdf"; $size = 0;
                if ($request->hasFile('userfile')) { 
                    $files = $request->file('userfile');
                    $original_filename = $files->getClientOriginalName();
                    $original_filename_arr = explode('.', $original_filename);
                    $file_ext = end($original_filename_arr);
                    $size = $files->getSize();

                    if($file_ext == "pdf") {
                        $image = 'PDF-' . time() . '.' . $file_ext;
                        $destination_path = storage_path('assets/uploads/'. $folder);
                        if ($files->move($destination_path, $image)) {
                            $file = $image;
                            $path = Init::storagePath() ."/". $folder ."/". $file;

                            if($request->tutorial_file != "") {
                                unlink($destination_path ."/". $request->tutorial_file); 
                            }
                        } 
                    }
                    else {
                        return response()->json([
                            'status'    => Init::responseStatus(0),
                            'message'   => "Jenis berkas unggahan: pdf",
                            'data'      => array()],
                            200
                        );
                    }
                }

                $now = Carbon::now();
                $rst = DB::table($this->table)
                    ->where($this->field, $id)
                    ->update([
                        "tutorial_status"       => $status,
                        "tutorial_name"         => $name,
                        "tutorial_file"         => $file,
                        "tutorial_path"         => $path,
                        "tutorial_description"  => (($description == null)? "":nl2br($description)),
                        "tutorial_status"       => (($status == 1)? 1:0),
                        "updated_at"            => $now,
                        "last_user"             => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                    ]);  
                
                dBase::setLogActivity($rst, $last_user, $now, 'Update', 'Ubah data master dokumen panduan');      
            }
            else {
                $rst = 0;
                $validate = "Data sudah ada";
            }
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
        $id         = $request->tutorial_id;
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
                    "tutorial_status"   => 0,
                    "is_deleted"        => 1,
                    "updated_at"        => $now,
                    "last_user"         => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]); 

            dBase::setLogActivity($rst, $last_user, $now, 'Delete', 'Hapus data master dokumen panduan');      
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