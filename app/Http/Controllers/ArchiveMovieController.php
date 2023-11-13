<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Init;
use App\Helpers\Dbase;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class ArchiveMovieController extends Controller
{
    private $table = "tp_movie";
    private $field = "movie_id";

    private $menuId  = "53";
    private $menuLog = "Arsip Pemberkasan Koleksi Video";

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
                $tmp = DB::table($this->table)->whereIn('satker_id', $arrSatker)->where('movie_title', 'like', '%'.$request->keyword.'%')->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('movie_id', 'DESC')->get();
                $cnt = DB::table($this->table)->whereIn('satker_id', $arrSatker)->where('movie_title', 'like', '%'.$request->keyword.'%')->where('is_deleted', 0)->count();
            }    
            else {
                $tmp = DB::table($this->table)->whereIn('satker_id', $arrSatker)->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('movie_id', 'DESC')->get();
                $cnt = DB::table($this->table)->whereIn('satker_id', $arrSatker)->where('is_deleted', 0)->count();
            }
        }
        else {
            if($request->keyword != "") {
                $tmp = DB::table($this->table)->where('movie_title', 'like', '%'.$request->keyword.'%')->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('movie_id', 'DESC')->get();
                $cnt = DB::table($this->table)->where('movie_title', 'like', '%'.$request->keyword.'%')->where('is_deleted', 0)->count();
            }    
            else {
                $tmp = DB::table($this->table)->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('movie_id', 'DESC')->get();
                $cnt = DB::table($this->table)->where('is_deleted', 0)->count();
            }
        }
        
        $arr = Init::initArchivemovie(1, $tmp);
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
        $id   = $request->movie_id;
        $data = DB::table($this->table)->where($this->field, $id)->get();
        $arr  = Init::initArchivemovie(0, $data);

        return Init::initResponse($arr, 'View');
    }

    public function insertData(Request $request) {
        $satker      = $request->satker_id;
        $title       = $request->title;
        $description = $request->description;
        $link        = $request->link;
        $last_user   = $request->last_user;
        
        $validate = Init::initValidate(
            array('satker', 'title'), 
            array($satker, $title)
        );

        if($validate == "") {
            $now = Carbon::now();
            $rst = DB::table($this->table)
                ->insertGetId([
                    "movie_title"        => $title,
                    "movie_description"  => (($description == null)? "":nl2br($description)),
                    "movie_link"         => $link,
                    "movie_satker"       => Dbase::dbGetFieldById('tm_satker', 'satker_name', 'satker_id', $satker),
                    "satker_id"          => $satker,
                    "created_at"         => $now,
                    "updated_at"         => $now,
                    "last_user"          => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]);   
        
            dBase::setLogActivity($rst, $last_user, $now, 'Insert', 'Simpan data '. $this->menuLog);  
            dBase::processUploadActivity($rst, $now, $this->menuId, $satker, 2, $title, 0, "-", $link);
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
        $id           = $request->movie_id;
        $status       = $request->status;
        $title        = $request->title;
        $description  = $request->description;
        $link         = $request->link;
        $last_user    = $request->last_user;
        
        $validate = Init::initValidate(
            array('id', 'title'), 
            array($id, $title)
        );

        if($validate == "") {
            $now = Carbon::now();
            $rst = DB::table($this->table)
                ->where($this->field, $id)
                ->update([
                    "movie_status"       => $status,
                    "movie_title"        => $title,
                    "movie_description"  => (($description == null)? "":nl2br($description)),
                    "movie_link"         => $link,
                    "updated_at"         => $now,
                    "last_user"          => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]);  
            
            dBase::setLogActivity($rst, $last_user, $now, 'Update', 'Ubah data '. $this->menuLog);  

            $satker = Dbase::dbGetFieldById($this->table, 'satker_id', $this->field, $id);
            dBase::ChangeUploadActivity($rst, $now, $this->menuId, $satker, 2, $title, 0, "-", $link); 
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
        $id         = $request->movie_id;
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
                    "movie_status"  => 0,
                    "is_deleted"    => 1,
                    "updated_at"    => $now,
                    "last_user"     => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
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
