<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Init;
use App\Helpers\Dbase;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class RoleUserController extends Controller
{
    private $table = "tp_role";
    private $field = "role_id";

    public function getFull(Request $request) {
        $limit  = $request->limit;
        $limit  = (($limit == "")?10:$limit);
        $offset = $request->offset;
        $offset = (($offset == "")?0:$offset);

        $cnt = 0;
        $tmp = array();
        if($request->name != "") {
            $tmp = DB::table($this->table)->where('role_name', 'like', '%'.$request->name.'%')->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy($this->field)->get();
            $cnt = DB::table($this->table)->where('role_name', 'like', '%'.$request->name.'%')->where('is_deleted', 0)->count();
        }
        else {
            $tmp = DB::table($this->table)->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy($this->field)->get();
            $cnt = DB::table($this->table)->where('is_deleted', 0)->count();
        }
        
        $arr = Init::initRoleFull(1, $tmp);
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

    public function getAll(Request $request) {
        $limit  = $request->limit;
        $limit  = (($limit == "")?10:$limit);
        $offset = $request->offset;
        $offset = (($offset == "")?0:$offset);

        $cnt = 0;
        $tmp = array();
        if($request->name != "") {
            $tmp = DB::table($this->table)->where('role_name', 'like', '%'.$request->name.'%')->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy($this->field)->get();
            $cnt = DB::table($this->table)->where('role_name', 'like', '%'.$request->name.'%')->where('is_deleted', 0)->count();
        }
        else {
            $tmp = DB::table($this->table)->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy($this->field)->get();
            $cnt = DB::table($this->table)->where('is_deleted', 0)->count();
        }
        
        $arr = Init::initRoleUser(1, $tmp);
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
        $id   = $request->role_id;
        $data = DB::table($this->table)->where($this->field, $id)->get();
        $arr  = Init::initRoleUser(0, $data);

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
            $exist = DB::table($this->table)->where('role_name', $request->name)->where('is_deleted', 0)->count();
            if($exist <= 0) {
                $now = Carbon::now();
                $rst = DB::table($this->table)
                    ->insertGetId([
                        "role_name"         => $name,
                        "role_description"  => (($description == null)? "":nl2br($description)),
                        "created_at"        => $now,
                        "updated_at"        => $now,
                        "last_user"         => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                    ]);   

                dBase::setLogActivity($rst, $last_user, $now, 'Insert', 'Simpan data role user');        
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
        $id           = $request->role_id;
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
            $old_name = DB::table($this->table)->where($this->field, $id)->first('role_name');
            if($old_name->role_name != $name) {
                $exist = DB::table($this->table)->where('role_name', $request->name)->where('is_deleted', 0)->count();
            }
             
            if($exist <= 0) {
                $now = Carbon::now();
                $rst = DB::table($this->table)
                    ->where($this->field, $id)
                    ->update([
                        "role_name"         => $name,
                        "role_description"  => (($description == null)? "":nl2br($description)),
                        "role_status"       => (($status == 1)? 1:0),
                        "updated_at"        => $now,
                        "last_user"         => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                    ]);  

                dBase::setLogActivity($rst, $last_user, $now, 'Update', 'Ubah data role user');      
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
        $id         = $request->role_id;
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
                    "role_status"   => 0,
                    "is_deleted"    => 1,
                    "updated_at"    => $now,
                    "last_user"     => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]); 

            dBase::setLogActivity($rst, $last_user, $now, 'Delete', 'Hapus data role user');        
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

    public function processData(Request $request) {
        $id         = $request->role_id;
        $module     = $request->module_id;
        $parent     = $request->module_parent;
        $last_user  = $request->last_user;

        $validate = Init::initValidate(
            array('id'), 
            array($id)
        );

        if($validate == "") {
            $table = "tr_authority";
            DB::table($table)->where($this->field, $id)->where('module_parent', $parent)->delete();
            
            if($module != "") {
                if($parent != 0) {
                    $rst = DB::table($table)
                        ->insertGetId([
                            "module_parent" => $parent,
                            "module_id"     => $parent,
                            "role_id"       => $id,
                        ]); 
                }

                for($i=0; $i<count($module); $i++) {
                    $module_id = $module[$i];
                    $rst = DB::table($table)
                        ->insertGetId([
                            "module_parent" => $parent,
                            "module_id"     => $module_id,
                            "role_id"       => $id,
                        ]);   
                }
                
                $now = Carbon::now();
                dBase::setLogActivity($rst, $last_user, $now, 'Process', 'Proses data role user');
            }
            else {
                $rst = 0;
            }
        }
        else {
            $rst = 0;
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'Process')),
            'data'      => array()],
            200
        );
    }

    public function getAccess(Request $request) {
        $id   = $request->role_id;
        $data = DB::table('tr_authority')->where($this->field, $id)->get();
        
        $arr = array();
        if($data != "[]") {
            foreach($data as $r) {
                $arr[] = $r->module_id;
            }
        }
        
        return Init::initResponse($arr, 'View');
    }

    public function getAuthority(Request $request) {
        $id   = $request->role_id;
        $data = DB::table('tr_authority')->where($this->field, $id)->get();
        
        $arr = array();
        if($data != "[]") {
            foreach($data as $r) {
                $arr[] = dBase::dbGetFieldById('tm_module', 'module_name', 'module_id', $r->module_id);
            }
        }
        
        return Init::initResponse($arr, 'View');
    }
}