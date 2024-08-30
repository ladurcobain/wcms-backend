<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Init;
use App\Helpers\Dbase;
use App\Helpers\Status;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class MasterModuleController extends Controller
{
    private $table = "tm_module";
    private $field = "module_id";

    public function getAll(Request $request) {
        $limit  = $request->limit;
        $limit  = (($limit == "")?10:$limit);
        $offset = $request->offset;
        $offset = (($offset == "")?0:$offset);

        $cnt = 0;
        $tmp = array();
        if($request->name != "") {
            $tmp = DB::table($this->table)->where('module_name', 'like', '%'.$request->name.'%')->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy($this->field)->get();
            $cnt = DB::table($this->table)->where('module_name', 'like', '%'.$request->name.'%')->where('is_deleted', 0)->count();
        }
        else {
            $tmp = DB::table($this->table)->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy($this->field)->get();
            $cnt = DB::table($this->table)->where('is_deleted', 0)->count();
        }
        
        $arr = Init::initMasterModule(1, $tmp);
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
        $id   = $request->module_id;
        $data = DB::table($this->table)->where($this->field, $id)->get();
        $arr  = Init::initMasterModule(0, $data);

        return Init::initResponse($arr, 'View');
    }

    public function updateData(Request $request) {
        $id           = $request->module_id;
        $name         = $request->name;
        $description  = $request->description;
        $last_user    = $request->last_user;
        
        $validate = Init::initValidate(
            array('id', 'name'), 
            array($id, $name)
        );

        if($validate == "") {
            $exist = 0;
            $old_name = DB::table($this->table)->where($this->field, $id)->first('module_name');
            if($old_name->module_name != $name) {
                $exist = DB::table($this->table)->where('module_name', $request->name)->where('is_deleted', 0)->count();
            }
            
            if($exist <= 0) {
                $now = Carbon::now();
                $rst = DB::table($this->table)
                    ->where($this->field, $id)
                    ->update([
                        "module_name"           => Status::htmlCharacters($name),
                        "module_description"    => (($description == null)? "":nl2br($description)),
                        "updated_at"            => $now,
                        "last_user"             => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                    ]);  
                
                dBase::setLogActivity($rst, $last_user, $now, 'Update', 'Ubah data master module');       
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

}