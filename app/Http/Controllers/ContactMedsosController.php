<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Init;
use App\Helpers\Dbase;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class ContactMedsosController extends Controller
{
    private $table = "tp_medsos";
    private $field = "medsos_id";

    private $menuId  = "71";
    private $menuLog = "Kontak Kami Media Sosial";

    public function getAll(Request $request) {
        $satker = $request->satker_id;

        $limit  = $request->limit;
        $limit  = (($limit == "")?10:$limit);
        $offset = $request->offset;
        $offset = (($offset == "")?0:$offset);

        $cnt = 0;
        $tmp = array();
        if($satker != "") {
            if($request->link != "") {
                $tmp = DB::table($this->table)->where('satker_id', $satker)->where('medsos_link', 'like', '%'.$request->link.'%')->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('medsos_id', 'DESC')->get();
                $cnt = DB::table($this->table)->where('satker_id', $satker)->where('medsos_link', 'like', '%'.$request->link.'%')->where('is_deleted', 0)->count();
            }    
            else {
                $tmp = DB::table($this->table)->where('satker_id', $satker)->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('medsos_id', 'DESC')->get();
                $cnt = DB::table($this->table)->where('satker_id', $satker)->where('is_deleted', 0)->count();
            }
        }
        else {
            if($request->link != "") {
                $tmp = DB::table($this->table)->where('medsos_link', 'like', '%'.$request->link.'%')->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('medsos_id', 'DESC')->get();
                $cnt = DB::table($this->table)->where('medsos_link', 'like', '%'.$request->link.'%')->where('is_deleted', 0)->count();
            }    
            else {
                $tmp = DB::table($this->table)->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('medsos_id', 'DESC')->get();
                $cnt = DB::table($this->table)->where('is_deleted', 0)->count();
            }
        }
        
        $arr = Init::initHomeMedsos(1, $tmp);
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
        $id   = $request->medsos_id;
        $data = DB::table($this->table)->where($this->field, $id)->get();
        $arr  = Init::initHomeMedsos(0, $data);

        return Init::initResponse($arr, 'View');
    }

    public function insertData(Request $request) {
        $satker      = $request->satker_id;
        $type        = $request->type;
        $link        = $request->link;
        $last_user   = $request->last_user;
        
        $validate = Init::initValidate(
            array('satker', 'type', 'link'), 
            array($satker, $type, $link)
        );

        if($validate == "") {
            $now = Carbon::now();
            $rst = DB::table($this->table)
                ->insertGetId([
                    "medsos_type"   => $type,
                    "medsos_link"   => $link,
                    "medsos_satker" => Dbase::dbGetFieldById('tm_satker', 'satker_name', 'satker_id', $satker),
                    "satker_id"     => $satker,
                    "created_at"    => $now,
                    "updated_at"    => $now,
                    "last_user"     => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]);   
        
            dBase::setLogActivity($rst, $last_user, $now, 'Insert', 'Simpan data '. $this->menuLog); 
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
        $id          = $request->medsos_id;
        $status      = $request->status;
        $type        = $request->type;
        $link        = $request->link;
        $last_user   = $request->last_user;
        
        $validate = Init::initValidate(
            array('id', 'type', 'link'), 
            array($id, $type, $link)
        );

        if($validate == "") {
            $now = Carbon::now();
            $rst = DB::table($this->table)
                ->where($this->field, $id)
                ->update([
                    "medsos_status" => $status,
                    "medsos_type"   => $type,
                    "medsos_link"   => $link,
                    "updated_at"    => $now,
                    "last_user"     => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]);  
            
            dBase::setLogActivity($rst, $last_user, $now, 'Update', 'Ubah data '. $this->menuLog); 
            
            $satker = Dbase::dbGetFieldById($this->table, 'satker_id', $this->field, $id);
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
        $id         = $request->medsos_id;
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
                    "medsos_status" => 0,
                    "is_deleted"    => 1,
                    "updated_at"    => $now,
                    "last_user"     => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]); 

            dBase::setLogActivity($rst, $last_user, $now, 'Delete', 'Hapus data '. $this->menuLog);      
            
            $satker = Dbase::dbGetFieldById($this->table, 'satker_id', $this->field, $id);
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