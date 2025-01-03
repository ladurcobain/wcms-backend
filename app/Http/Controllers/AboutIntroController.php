<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Init;
use App\Helpers\Dbase;
use App\Helpers\Status;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class AboutIntroController extends Controller
{
    private $table = "tp_intro";
    private $field = "intro_id";

    private $menuId  = "25";
    private $menuLog = "Tentang Kami Kata Sambutan";

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
                $tmp = DB::table($this->table)->whereIn('satker_id', $arrSatker)->where('intro_text_in', 'like', '%'.$request->keyword.'%')->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('intro_id', 'DESC')->get();
                $cnt = DB::table($this->table)->whereIn('satker_id', $arrSatker)->where('intro_text_in', 'like', '%'.$request->keyword.'%')->where('is_deleted', 0)->count();
            }    
            else {
                $tmp = DB::table($this->table)->whereIn('satker_id', $arrSatker)->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('intro_id', 'DESC')->get();
                $cnt = DB::table($this->table)->whereIn('satker_id', $arrSatker)->where('is_deleted', 0)->count();
            }
        }
        else {
            if($request->keyword != "") {
                $tmp = DB::table($this->table)->where('intro_text_in', 'like', '%'.$request->keyword.'%')->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('intro_id', 'DESC')->get();
                $cnt = DB::table($this->table)->where('intro_text_in', 'like', '%'.$request->keyword.'%')->where('is_deleted', 0)->count();
            }    
            else {
                $tmp = DB::table($this->table)->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('intro_id', 'DESC')->get();
                $cnt = DB::table($this->table)->where('is_deleted', 0)->count();
            }
        }
        
        $arr = Init::initAboutIntro(1, $tmp);
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
        $id   = $request->intro_id;
        $data = DB::table($this->table)->where($this->field, $id)->get();
        $arr  = Init::initAboutIntro(0, $data);

        return Init::initResponse($arr, 'View');
    }

    public function insertData(Request $request) {
        $satker     = $request->satker_id;
        $text_in    = $request->text_in;
        $text_en    = $request->text_en;
        $last_user  = $request->last_user;
        
        $validate = Init::initValidate(
            array('satker', 'text ina', 'text eng'), 
            array($satker, $text_in, $text_en)
        );

        if($validate == "") {
            $now = Carbon::now();
            $rst = DB::table($this->table)
                ->insertGetId([
                    "intro_text_in"  => $text_in,
                    "intro_text_en"  => $text_en,
                    "intro_satker"   => Dbase::dbGetFieldById('tm_satker', 'satker_name', 'satker_id', $satker),
                    "satker_id"     => $satker,
                    "created_at"    => $now,
                    "updated_at"    => $now,
                    "last_user"     => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]);   
        
            dBase::setLogActivity($rst, $last_user, $now, 'Insert', 'Simpan data '. $this->menuLog);
            dBase::processContentActivity($rst, $now, $this->menuId, $satker, $text_in, $text_en);
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
        $id         = $request->intro_id;
        $status     = $request->status;
        $text_in    = $request->text_in;
        $text_en    = $request->text_en;
        $last_user  = $request->last_user;
        
        $validate = Init::initValidate(
            array('id', 'text ina', 'text eng'), 
            array($id, $text_in, $text_en)
        );

        if($validate == "") {
            $now = Carbon::now();
            $rst = DB::table($this->table)
                ->where($this->field, $id)
                ->update([
                    "intro_status"   => $status,
                    "intro_text_in"  => $text_in,
                    "intro_text_en"  => $text_en,
                    "updated_at"    => $now,
                    "last_user"     => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]);  
            
            dBase::setLogActivity($rst, $last_user, $now, 'Update', 'Ubah data '. $this->menuLog);  

            $satker = Dbase::dbGetFieldById($this->table, 'satker_id', $this->field, $id);
            dBase::changeContentActivity($rst, $now, $this->menuId, $satker, $id, $text_in, $text_en);    
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
        $id         = $request->intro_id;
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
                    "intro_status"   => 0,
                    "is_deleted"    => 1,
                    "updated_at"    => $now,
                    "last_user"     => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]); 

            dBase::setLogActivity($rst, $last_user, $now, 'Delete', 'Hapus data '. $this->menuLog);  

            $satker = Dbase::dbGetFieldById($this->table, 'satker_id', $this->field, $id);
            dBase::removeContentActivity($rst, $this->menuId, $satker, $id);     
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
