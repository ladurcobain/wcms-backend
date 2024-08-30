<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Init;
use App\Helpers\Dbase;
use App\Helpers\Status;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class MasterRequestController extends Controller
{
    private $table = "tp_request";
    private $field = "request_id";

    public function getAll(Request $request) {
        $limit  = $request->limit;
        $limit  = (($limit == "")?10:$limit);
        $offset = $request->offset;
        $offset = (($offset == "")?0:$offset);

        $cnt = 0;
        $tmp = array();
        if($request->name != "") {
            $tmp = DB::table($this->table)->where('request_name', 'like', '%'.$request->name.'%')->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy($this->field)->get();
            $cnt = DB::table($this->table)->where('request_name', 'like', '%'.$request->name.'%')->where('is_deleted', 0)->count();
        }
        else {
            $tmp = DB::table($this->table)->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy($this->field)->get();
            $cnt = DB::table($this->table)->where('is_deleted', 0)->count();
        }
        
        $arr = Init::initMasterRequest(1, $tmp);
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
        $id   = $request->request_id;
        $data = DB::table($this->table)->where($this->field, $id)->get();
        $arr  = Init::initMasterRequest(0, $data);

        return Init::initResponse($arr, 'View');
    }

    public function insertData(Request $request) {
        $name        = $request->name;
        $method      = $request->method;
        $url         = $request->url;
        $description = $request->description;
        $last_user   = $request->last_user;
        
        $validate = Init::initValidate(
            array('name'), 
            array($name)
        );

        if($validate == "") {
            $exist = DB::table($this->table)->where('request_name', $request->name)->where('is_deleted', 0)->count();
            if($exist <= 0) {
                $now = Carbon::now();
                $rst = DB::table($this->table)
                    ->insertGetId([
                        "request_name"          => Status::htmlCharacters($name),
                        "request_method"        => $method,
                        "request_url"           => $url,
                        "request_description"   => (($description == null)? "":nl2br($description)),
                        "created_at"            => $now,
                        "updated_at"            => $now,
                        "last_user"             => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                    ]);   
            
                dBase::setLogActivity($rst, $last_user, $now, 'Insert', 'Simpan data master request');      
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
        $id          = $request->request_id;
        $status      = $request->status;
        $name        = $request->name;
        $method      = $request->method;
        $url         = $request->url;
        $description = $request->description;
        $last_user   = $request->last_user;
        
        $validate = Init::initValidate(
            array('id', 'name'), 
            array($id, $name)
        );

        if($validate == "") {
            $exist = 0;
            $old_name = DB::table($this->table)->where($this->field, $id)->first('request_name');
            if($old_name->request_name != $name) {
                $exist = DB::table($this->table)->where('request_name', $request->name)->where('is_deleted', 0)->count();
            }
            
            if($exist <= 0) {
                $now = Carbon::now();
                $rst = DB::table($this->table)
                    ->where($this->field, $id)
                    ->update([
                        "request_status"       => $status,
                        "request_name"         => Status::htmlCharacters($name),
                        "request_method"       => $method,
                        "request_url"          => $url,
                        "request_description"  => (($description == null)? "":nl2br($description)),
                        "request_status"       => (($status == 1)? 1:0),
                        "updated_at"           => $now,
                        "last_user"            => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                    ]);  
                
                dBase::setLogActivity($rst, $last_user, $now, 'Update', 'Ubah data master request');      
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
        $id         = $request->request_id;
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
                    "request_status"   => 0,
                    "is_deleted"        => 1,
                    "updated_at"        => $now,
                    "last_user"         => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]); 

            dBase::setLogActivity($rst, $last_user, $now, 'Delete', 'Hapus data master request');      
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

    
    public function getParam(Request $request) {
        $id   = $request->request_id;
        $data = DB::table('tr_param')->where($this->field, $id)->get();
        $arr  = Init::initMasterParam(1, $data);

        return Init::initResponse($arr, 'View');
    }

    public function processParam(Request $request) {
        $type        = $request->type;
        $initial     = $request->initial;
        $description = $request->description;
        $request_id  = $request->request_id;
        
        $validate = Init::initValidate(
            array('type', 'initial'), 
            array($type, $initial)
        );

        $now = Carbon::now();
        $rst = DB::table('tr_param')
            ->insertGetId([
                "param_type"        => $type,
                "param_initial"     => $initial,
                "param_description" => (($description == null)? "":nl2br($description)),
                "request_id"        => $request_id,
            ]);   
        
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'Insert')),
            'data'      => array()],
            200
        );
    }

    public function removeParam(Request $request) {
        $id = $request->param_id;
        $validate = Init::initValidate(
            array('id'), 
            array($id)
        );

        if($validate == "") {
            $now = Carbon::now();
            $rst = DB::table('tr_param')->where('param_id', $id)->delete();   
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