<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Init;
use App\Helpers\Dbase;
use App\Helpers\Status;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class MasterMenuController extends Controller
{
    private $table = "tm_menu";
    private $field = "menu_id";

    public function getAll(Request $request) {
        $limit  = $request->limit;
        $limit  = (($limit == "")?10:$limit);
        $offset = $request->offset;
        $offset = (($offset == "")?0:$offset);

        $cnt = 0;
        $tmp = array();
        if($request->name != "") {
            $tmp = DB::table($this->table)->where('menu_name', 'like', '%'.$request->name.'%')->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy($this->field)->get();
            $cnt = DB::table($this->table)->where('menu_name', 'like', '%'.$request->name.'%')->where('is_deleted', 0)->count();
        }
        else {
            $tmp = DB::table($this->table)->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy($this->field)->get();
            $cnt = DB::table($this->table)->where('is_deleted', 0)->count();
        }
        
        $arr = Init::initMasterMenu(1, $tmp);
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
        $id   = $request->menu_id;
        $data = DB::table($this->table)->where($this->field, $id)->get();
        $arr  = Init::initMasterMenu(0, $data);

        return Init::initResponse($arr, 'View');
    }

    public function updateData(Request $request) {
        $id           = $request->menu_id;
        $name         = $request->name;
        $label        = $request->label;
        $description  = $request->description;
        $last_user    = $request->last_user;
        
        $validate = Init::initValidate(
            array('id', 'name', 'label'), 
            array($id, $name, $label)
        );
        
        if($validate == "") {
            $exist = 0;
            $old_name = DB::table($this->table)->where($this->field, $id)->first('menu_name');
            if($old_name->menu_name != $name) {
                $exist = DB::table($this->table)->where('menu_name', $request->name)->where('is_deleted', 0)->count();
            }
            
            if($exist <= 0) {
                $now = Carbon::now();
                $rst = DB::table($this->table)
                    ->where($this->field, $id)
                    ->update([
                        "menu_name"         => Status::htmlCharacters($name),
                        "menu_label"        => Status::htmlCharacters($label),
                        "menu_description"  => (($description == null)? "":nl2br($description)),
                        "updated_at"        => $now,
                        "last_user"         => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                    ]);  
            
                dBase::setLogActivity($rst, $last_user, $now, 'Update', 'Ubah data master menu');         
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