<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Init;
use App\Helpers\Dbase;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class InformationDpoController extends Controller
{
    private $table = "tp_dpo";
    private $field = "dpo_id";

    private $menuId  = "34";
    private $menuLog = "Informasi Umum Daftar Pencarian Orang";

    public function getAll(Request $request) {
        $satker = $request->satker_id;

        $limit  = $request->limit;
        $limit  = (($limit == "")?10:$limit);
        $offset = $request->offset;
        $offset = (($offset == "")?0:$offset);

        $cnt = 0;
        $tmp = array();
        if($satker != "") {
            $arrSatker = Dbase::memberSatker($satker);

            if($request->keyword != "") {
                $tmp = DB::table($this->table)->whereIn('satker_id', $arrSatker)->where('dpo_name', 'like', '%'.$request->keyword.'%')->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('dpo_id', 'DESC')->get();
                $cnt = DB::table($this->table)->whereIn('satker_id', $arrSatker)->where('dpo_name', 'like', '%'.$request->keyword.'%')->where('is_deleted', 0)->count();
            }    
            else {
                $tmp = DB::table($this->table)->whereIn('satker_id', $arrSatker)->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('dpo_id', 'DESC')->get();
                $cnt = DB::table($this->table)->whereIn('satker_id', $arrSatker)->where('is_deleted', 0)->count();
            }
        }
        else {
            if($request->keyword != "") {
                $tmp = DB::table($this->table)->where('dpo_name', 'like', '%'.$request->keyword.'%')->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('dpo_id', 'DESC')->get();
                $cnt = DB::table($this->table)->where('dpo_name', 'like', '%'.$request->keyword.'%')->where('is_deleted', 0)->count();
            }    
            else {
                $tmp = DB::table($this->table)->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('dpo_id', 'DESC')->get();
                $cnt = DB::table($this->table)->where('is_deleted', 0)->count();
            }
        }
        
        $arr = Init::initInformationDpo(1, $tmp);
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
        $id   = $request->dpo_id;
        $data = DB::table($this->table)->where($this->field, $id)->get();
        $arr  = Init::initInformationDpo(0, $data);

        return Init::initResponse($arr, 'View');
    }

    public function insertData(Request $request) {
        $satker      = $request->satker_id;
        $name        = $request->name;
        $information = $request->information;
        $last_user   = $request->last_user;
        
        $validate = Init::initValidate(
            array('satker', 'name'), 
            array($satker, $name)
        );

        if($validate == "") {
            $file = null; $path = "-"; $folder = "dpo"; $size = 0;
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
                    "dpo_name"          => $name,
                    "dpo_size"          => (($size == null)? 0:$size),
                    "dpo_image"         => (($file == null)? "":$file),
                    "dpo_path"          => (($file == null)? "":$path),
                    "dpo_information"   => $information,
                    "dpo_satker"        => Dbase::dbGetFieldById('tm_satker', 'satker_name', 'satker_id', $satker),
                    "satker_id"         => $satker,
                    "created_at"        => $now,
                    "updated_at"        => $now,
                    "last_user"         => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]);   
                
            dBase::setLogActivity($rst, $last_user, $now, 'Insert', 'Simpan data '. $this->menuLog);
            dBase::processUploadActivity($rst, $now, $this->menuId, $satker, 1, $name, $size, $file, $path);
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
        $id          = $request->dpo_id;
        $status      = $request->status;
        $name        = $request->name;
        $information = $request->information;
        $last_user   = $request->last_user;
        
        $validate = Init::initValidate(
            array('id', 'name' ), 
            array($id, $name)
        );

        if($validate == "") {
            $file = null; $path = "-"; $folder = "dpo"; $size = 0;
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

                        $old_image = $request->dpo_image;
                        if($old_image != "") {
                            unlink($destination_path ."/". $old_image); 
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
                $size = $request->dpo_size;
                $file = $request->dpo_image;
                $path = Init::storagePath() ."/". $folder ."/". $file;
            }

            $now = Carbon::now();
            $rst = DB::table($this->table)
                ->where($this->field, $id)
                ->update([
                    "dpo_status"        => $status,
                    "dpo_name"          => $name,
                    "dpo_size"          => (($size == null)? 0:$size),
                    "dpo_image"         => (($file == null)? "":$file),
                    "dpo_path"          => (($file == null)? "":$path),
                    "dpo_information"   => $information,
                    "updated_at"        => $now,
                    "last_user"         => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]);  
            
            dBase::setLogActivity($rst, $last_user, $now, 'Update', 'Ubah data '. $this->menuLog);  

            $satker = Dbase::dbGetFieldById($this->table, 'satker_id', $this->field, $id);
            dBase::ChangeUploadActivity($rst, $now, $this->menuId, $satker, $id, $name, $size, $file, $path); 
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
        $id         = $request->dpo_id;
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
                    "dpo_status" => 0,
                    "is_deleted" => 1,
                    "updated_at" => $now,
                    "last_user"  => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]); 

            dBase::setLogActivity($rst, $last_user, $now, 'Delete', 'Hapus data '. $this->menuLog);  

            $satker = Dbase::dbGetFieldById($this->table, 'satker_id', $this->field, $id);
            dBase::removeUploadActivity($rst, $this->menuId, $satker, $id);  
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
