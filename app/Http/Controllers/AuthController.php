<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Ciphertext;
use App\Helpers\Dbase;
use App\Helpers\Init;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    private $table = "tm_user";
    private $field = "user_id";

    public function login(Request $request) {
        $ip         = $request->ip;
        $account    = $request->username;
        $password   = $request->password;
        $token      = $request->token;

        $validate = Init::initValidate(
            array('username', 'password'), 
            array($account, $password)
        );

        if($validate == "") {
            $pass = Ciphertext::simpleEncrypt($password);
            $data = DB::table($this->table)->where('user_account', $account)->where('user_password', $pass)->where('user_status', 1)->where('is_deleted', 0)->orderBy('user_id')->get();
            if($data != "[]") {
                $user = Init::initUser(0, $data);
                if($user['role_id'] != "") {
                    $access = array();
                    $authority = DB::table('tr_authority')->where('role_id', $user['role_id'])->orderBy('authority_id')->get();
                    if($authority != "[]") {
                        foreach($authority as $r) {
                            $access[] = $r->module_id;
                        }
                    }
                }
                else {
                    $access = array();
                }
                
                $arr = array(
                    'user'   => $user,
                    'access' => $access,
                );

                $agent = new \Jenssegers\Agent\Agent;

                $log = [];
                $log['ip']       = (($ip == "")?$request->ip():$ip);
                $log['agent']    = $request->header('user-agent');
                $log['platform'] = $agent->platform();
                $log['device']   = $agent->device();
                $log['browser']  = $agent->browser();

                $now = Carbon::now();
                $rst = DB::table($this->table)
                    ->where($this->field, $user['user_id'])
                    ->update([
                        "user_token" => $token,
                        "user_login" => $now,
                        "user_log"   => json_encode($log)
                    ]); 
                
                Dbase::dbSetFieldById($this->table, 'user_count', 0, 'user_account', $account);
                Dbase::dbSetFieldById($this->table, 'user_cstatus', 1, 'user_account', $account);
                dBase::setLogActivity($rst, $user['user_id'], $now, 'Login', 'Masuk aplikasi', $ip); 

                if($user['satker_id'] != "") {
                    Dbase::dbSetFieldById('tm_satker', 'updated_at', $now, 'satker_id', $user['satker_id']);
                }
            }
            else {
                $arr = array();
             
                $count = dBase::dbGetFieldById($this->table, 'user_count', 'user_account', $account);
                $count = $count + 1;
                Dbase::dbSetFieldById($this->table, 'user_count', $count, 'user_account', $account);
                Dbase::dbSetFieldById($this->table, 'user_status', 0, 'user_account', $account);

                if($count > 3) {
                    return response()->json([
                        'status'    => Init::responseStatus(0),
                        'message'   => 'Kata sandi salah 3x, silahkan hubungi Administrator',
                        'data'      => array()],
                        200
                    );
                }
            }

            return Init::initResponse($arr, 'Masuk Aplikasi');
        }
        else {
            return response()->json([
                'status'    => Init::responseStatus(0),
                'message'   => (($validate != "")?$validate:Init::responseMessage(0, 'Login')),
                'data'      => array()],
                200
            );
        }
    }
    
    public function logout(Request $request) {
        $id = $request->user_id;
        $ip = $request->ip;
        
        $validate = Init::initValidate(
            array('id'), 
            array($id)
        );

        if($validate == "") {
            $now = Carbon::now();
            
            $agent = new \Jenssegers\Agent\Agent;

            $log = [];
            $log['ip']       = (($ip == "")?$request->ip():$ip);
            $log['agent']    = $request->header('user-agent');
            $log['platform'] = $agent->platform();
            $log['device']   = $agent->device();
            $log['browser']  = $agent->browser();

            $now = Carbon::now();
            $rst = DB::table($this->table)
                ->where($this->field, $id)
                ->update([
                    "user_token" => null,
                    "user_login" => $now,
                    "user_log"   => json_encode($log)
                ]); 
            
            dBase::setLogActivity($rst, $id, $now, 'Logout', 'Keluar aplikasi', $ip);     
        }
        else {
            $rst = 0;
        }
         
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'Logout')),
            'data'      => array()],
            200
        );
    }

    public function changePassword(Request $request) {
        $id              = $request->user_id;
        $oldpassword     = $request->oldpassword;
        $newpassword     = $request->newpassword;
        $confirmpassword = $request->confirmpassword;
        
        
        $validate = Init::initValidate(
            array('id', 'old password', 'new password', 'confirm password'), 
            array($id, $oldpassword, $newpassword, $confirmpassword)
        );
         
        if($validate == "") {
            if($newpassword == $confirmpassword) {
                $currentpassword = dBase::dbGetFieldById($this->table, 'user_password', $this->field, $id); 
                $currentpassword = Ciphertext::simpleDecrypt($currentpassword);
                if($oldpassword == $currentpassword) {
                    $now = Carbon::now();
                    $rst = DB::table($this->table)
                        ->where($this->field, $id)
                        ->update([
                            "user_password" => Ciphertext::simpleEncrypt($newpassword),
                        ]);  
        
                    dBase::setLogActivity($rst, $id, $now, 'Change Password', 'Ganti Password profil pengguna');    
                }
                else {
                    $rst = 0;
                    $validate = "Kata kunci lama salah";
                }
            }
            else {
                $rst = 0;
                $validate = "Kata kunci tidak sama";
            }    
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

    public function updateProfile(Request $request) {
        $id         = $request->user_id;
        $code       = $request->code;
        $fullname   = $request->fullname;
        $phone      = $request->phone;
        $email      = $request->email;
        $address    = $request->address;
        
        $validate = Init::initValidate(
            array('id', 'fullname'), 
            array($id, $fullname)
        );

        if($validate == "") {
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
                    "user_fullname" => (($fullname == null)? "":$fullname),
                    "user_phone"    => (($phone == null)? "":$phone),
                    "user_email"    => (($email == null)? "":$email),
                    "user_address"  => (($address == null)? "":nl2br($address)),
                    "user_image"    => (($file == null)? "":$file),
                    "user_path"     => (($file == null)? "":$path),
                ]);  
               
            dBase::setLogActivity($rst, $id, $now, 'Update', 'Ubah data profil pengguna');     
        }
        else {
            $rst = 0;
        }
        
        $data = DB::table($this->table)->where($this->field, $request->user_id)->get();
        $arr  = Init::initUser(0, $data);

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'Update')),
            'data'      => $arr],
            200
        );
    }
}
