<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Ciphertext;
use App\Helpers\Dbase;
use App\Helpers\Status;
use App\Helpers\Init;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    private $table = "tm_user";
    private $field = "user_id";

    public function getAll(Request $request) {
        $limit  = $request->limit;
        $limit  = (($limit == "")?10:$limit);
        $offset = $request->offset;
        $offset = (($offset == "")?0:$offset);

        $cnt = 0;
        $tmp = array();
        if(($request->name != "") && ($request->type != "")) {
            $tmp = DB::table($this->table)->where('user_fullname', 'like', '%'.$request->name.'%')->where('user_type', $request->type)->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('user_type')->get();
            $cnt = DB::table($this->table)->where('user_fullname', 'like', '%'.$request->name.'%')->where('user_type', $request->type)->where('is_deleted', 0)->count();
        }    
        else if(($request->name != "") && ($request->type == "")) {
            $tmp = DB::table($this->table)->where('user_fullname', 'like', '%'.$request->name.'%')->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('user_type')->get();
            $cnt = DB::table($this->table)->where('user_fullname', 'like', '%'.$request->name.'%')->where('is_deleted', 0)->count();
        }    
        else if(($request->name == "") && ($request->type != "")) {
            $tmp = DB::table($this->table)->where('user_type', $request->type)->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('user_type')->get();
            $cnt = DB::table($this->table)->where('user_type', $request->type)->where('is_deleted', 0)->count();
        }    
        else {
            $tmp = DB::table($this->table)->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('user_type')->get();
            $cnt = DB::table($this->table)->where('is_deleted', 0)->count();
        }
        
        $arr = Init::initUser(1, $tmp);
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
        $id   = $request->user_id;
        $data = DB::table($this->table)->where($this->field, $id)->get();
        $arr  = Init::initUser(0, $data);

        return Init::initResponse($arr, 'View');
    }

    public function insertData(Request $request) {
        $account    = $request->username;
        $password   = $request->password;
        $code       = $request->code;
        $fullname   = $request->fullname;
        $phone      = $request->phone;
        $email      = $request->email;
        $address    = $request->address;
        $type       = $request->type;
        $role_id    = $request->role_id;
        $satker_id  = $request->satker_id;
        $last_user  = $request->last_user;
        
        if ($account == trim($account) && strpos($account, ' ')) {
            return response()->json([
                'status'    => false,
                'message'   => 'Username contains whitespace',
                'data'      => array()],
                200
            );
        }
    
        if($type == 1) {
            if($role_id == "") {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Misiing parameter role id',
                    'data'      => array()],
                    200
                );
            } 
        }
        else {
            if($satker_id == "") {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Misiing parameter satker id',
                    'data'      => array()],
                    200
                );
            } 
        }

        $validate = Init::initValidate(
            array('username', 'password', 'fullname', 'type'), 
            array($account, $password, $fullname, $type)
        );

        if($validate == "") {
            $exist = DB::table($this->table)->where('user_account', $account)->where('is_deleted', 0)->count();
            if($exist <= 0) {
                $file = null; $path = "-"; $folder = "user";
                if ($request->hasFile('userfile')) {
                    
                    $files = $request->file('userfile');
                    $original_filename = $files->getClientOriginalName();
                    $original_filename_arr = explode('.', $original_filename);
                    $file_ext = end($original_filename_arr);
                    
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
                        "user_account"  => Status::htmlCharacters($account),
                        "user_password" => Ciphertext::simpleEncrypt($password),
                        "user_type"     => $type,
                        "user_code"     => (($code == null)? "":$code),
                        "user_fullname" => (($fullname == null)? "":Status::htmlCharacters($fullname)),
                        "user_phone"    => (($phone == null)? "":$phone),
                        "user_email"    => (($email == null)? "":$email),
                        "user_address"  => (($address == null)? "":nl2br($address)),
                        "user_image"    => (($file == null)? "":$file),
                        "user_path"     => (($file == null)? "":$path),
                        "role_id"       => (($type == 2)? null:$role_id),
                        "satker_id"     => (($type == 1)? null:$satker_id),
                        "created_at"    => $now,
                        "updated_at"    => $now,
                        "last_user"     => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                    ]);   
            
                dBase::setLogActivity($rst, $last_user, $now, 'Insert', 'Simpan data pengguna');     
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
        $id         = $request->user_id;
        $status     = $request->status;
        $code       = $request->code;
        $fullname   = $request->fullname;
        $phone      = $request->phone;
        $email      = $request->email;
        $address    = $request->address;
        $last_user  = $request->last_user;
        
        $validate = Init::initValidate(
            array('id', 'fullname'), 
            array($id, $fullname)
        );

        if($validate == "") {
            $file = $request->user_image; $path = "-"; $folder = "user";
            if ($request->hasFile('userfile')) {
                $files = $request->file('userfile');
                $original_filename = $files->getClientOriginalName();
                $original_filename_arr = explode('.', $original_filename);
                $file_ext = end($original_filename_arr);
                
                if(($file_ext == "jpg") || ($file_ext == "png") || ($file_ext == "jpeg")) {
                    $image = 'IMG-' . time() . '.' . $file_ext;
                    $destination_path = storage_path('assets/uploads/'. $folder);
                    if ($files->move($destination_path, $image)) {
                        $file = $image;
                        $path = Init::storagePath() ."/". $folder ."/". $file;

                        if($request->user_image != "") {
                            unlink($destination_path ."/". $request->user_image); 
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
                $file = $request->user_image;
                $path = Init::storagePath() ."/". $folder ."/". $file;
            }
            
            $now = Carbon::now();
            $rst = DB::table($this->table)
                ->where($this->field, $id)
                ->update([
                    "user_code"     => (($code == null)? "":$code),
                    "user_fullname" => (($fullname == null)? "":Status::htmlCharacters($fullname)),
                    "user_phone"    => (($phone == null)? "":$phone),
                    "user_email"    => (($email == null)? "":$email),
                    "user_address"  => (($address == null)? "":nl2br($address)),
                    "user_image"    => (($file == null)? "":$file),
                    "user_path"     => (($file == null)? "":$path),
                    "user_status"   => (($status == 1)? 1:0),
                    "updated_at"    => $now,
                    "last_user"     => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]);  

            dBase::setLogActivity($rst, $last_user, $now, 'Update', 'Ubah data pengguna'); 
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

    public function updatePassword(Request $request) {
        $id         = $request->user_id;
        $password   = $request->password;
        $last_user  = $request->last_user;
        
        $validate = Init::initValidate(
            array('id', 'password'), 
            array($id, $password)
        );
         
        if($validate == "") {
            $now = Carbon::now();
            $rst = DB::table($this->table)
                ->where($this->field, $id)
                ->update([
                    "user_password" => Ciphertext::simpleEncrypt($password),
                    "updated_at"    => $now,
                    "last_user"     => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]);  

            dBase::setLogActivity($rst, $last_user, $now, 'Change Password', 'Ubah kata sandi pengguna');        
        }
        else {
            $rst = 0;
        }
        
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'Password Update')),
            'data'      => array()],
            200
        );
    }

    public function deleteData(Request $request) {
        $id         = $request->user_id;
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
                    "user_status"   => 0,
                    "is_deleted"    => 1,
                    "updated_at"    => $now,
                    "last_user"     => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]); 

            dBase::setLogActivity($rst, $last_user, $now, 'Delete', 'Hapus data pengguna');    
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
