<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Init;
use App\Helpers\Dbase;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class ConferenceNewsController extends Controller
{
    private $table = "tp_news";
    private $field = "news_id";

    private $menuId  = "41";
    private $menuLog = "Siaran Pers Berita";

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

            if($request->title != "") {
                $tmp = DB::table($this->table)->whereIn('satker_id', $arrSatker)->where('news_title', 'like', '%'.$request->title.'%')->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('news_date', 'DESC')->get();
                $cnt = DB::table($this->table)->whereIn('satker_id', $arrSatker)->where('news_title', 'like', '%'.$request->title.'%')->where('is_deleted', 0)->count();
            }    
            else {
                $tmp = DB::table($this->table)->whereIn('satker_id', $arrSatker)->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('news_date', 'DESC')->get();
                $cnt = DB::table($this->table)->whereIn('satker_id', $arrSatker)->where('is_deleted', 0)->count();
            }
        }
        else {
            if($request->title != "") {
                $tmp = DB::table($this->table)->where('news_title', 'like', '%'.$request->title.'%')->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('news_date', 'DESC')->get();
                $cnt = DB::table($this->table)->where('news_title', 'like', '%'.$request->title.'%')->where('is_deleted', 0)->count();
            }    
            else {
                $tmp = DB::table($this->table)->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('news_date', 'DESC')->get();
                $cnt = DB::table($this->table)->where('is_deleted', 0)->count();
            }
        }
        
        $arr = Init::initConferenceNews(1, $tmp);
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
        $id   = $request->news_id;
        $data = DB::table($this->table)->where($this->field, $id)->get();
        $arr  = Init::initConferenceNews(0, $data);

        return Init::initResponse($arr, 'View');
    }

    public function insertData(Request $request) {
        $broadcast      = $request->broadcast;
        $status         = $request->status;
        $satker         = $request->satker_id;
        $title          = $request->title;
        $date           = $request->date;
        $category       = $request->category;
        $text_in        = $request->text_in;
        $text_en        = $request->text_en;
        $link_instagram = $request->link_instagram;
        $link_youtube   = $request->link_youtube;
        $last_user      = $request->last_user;
        
        $validate = Init::initValidate(
            array('satker', 'title', 'date'), 
            array($satker, $title, $date)
        );

        if($validate == "") {
            $file = null; $path = "-"; $folder = "news"; $size = 0;
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
                    "news_title"            => $title,
                    "news_date"             => $date,
                    "news_category"         => $category,
                    "news_text_in"          => $text_in,
                    "news_text_en"          => $text_en,
                    "news_broadcast"        => (($broadcast == null)? 0:$broadcast),
                    "news_size"             => (($size == null)? 0:$size),
                    "news_image"            => (($file == null)? "":$file),
                    "news_path"             => (($file == null)? "":$path),
                    "news_link_instagram"   => (($link_instagram == null)? "":$link_instagram),
                    "news_link_youtube"     => (($link_youtube == null)? "":$link_youtube),
                    "news_satker"           => Dbase::dbGetFieldById('tm_satker', 'satker_name', 'satker_id', $satker),
                    "satker_id"             => $satker,
                    "created_at"            => $now,
                    "updated_at"            => $now,
                    "last_user"             => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]);   
                
            dBase::setLogActivity($rst, $last_user, $now, 'Insert', 'Simpan data '. $this->menuLog);
            dBase::processContentActivity($rst, $now, $this->menuId, $satker, $text_in, $text_en); 
            
            if(($category == "Berita") || ($category == "Pengumuman") || ($category == "Kegiatan")) {
                dBase::processUploadActivity($rst, $now, $this->menuId, $satker, 1, $title, $size, $file, $path);
            } 

            //dBase::setLogNotification($rst, $satker, $last_user, $now, 'Insert', 'Simpan data '. $this->menuLog);
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
        $id             = $request->news_id;
        $status         = $request->status;
        $title          = $request->title;
        $date           = $request->date;
        $text_in        = $request->text_in;
        $text_en        = $request->text_en;
        $link_instagram = $request->link_instagram;
        $link_youtube   = $request->link_youtube;
        $last_user      = $request->last_user;
        
        $validate = Init::initValidate(
            array('id', 'title', 'date' ), 
            array($id, $title, $date)
        );

        if($validate == "") {
            $file = null; $path = "-"; $folder = "news"; $size = 0;
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

                        $old_image = $request->news_image;
                        if($old_image!= "") {
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
                $size = $request->user_size;
                $file = $request->user_image;
                $path = Init::storagePath() ."/". $folder ."/". $file;
            }

            $now = Carbon::now();
            $rst = DB::table($this->table)
                ->where($this->field, $id)
                ->update([
                    "news_status"           => $status,
                    "news_title"            => $title,
                    "news_date"             => $date,
                    "news_text_in"          => $text_in,
                    "news_text_en"          => $text_en,
                    "news_size"             => (($size == null)? 0:$size),
                    "news_image"            => (($file == null)? "":$file),
                    "news_path"             => (($file == null)? "":$path),
                    "news_link_instagram"   => (($link_instagram == null)? "":$link_instagram),
                    "news_link_youtube"     => (($link_youtube == null)? "":$link_youtube),
                    "updated_at"            => $now,
                    "last_user"             => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]);  
            
            dBase::setLogActivity($rst, $last_user, $now, 'Update', 'Ubah data '. $this->menuLog);  

            $satker = Dbase::dbGetFieldById($this->table, 'satker_id', $this->field, $id);
            dBase::ChangeContentActivity($rst, $now, $this->menuId, $satker, $id, $text_in, $text_en);
            
            $category = Dbase::dbGetFieldById($this->table, 'news_category', $this->field, $id);
            if(($category == "Berita") || ($category == "Pengumuman") || ($category == "Kegiatan")) {
                dBase::ChangeUploadActivity($rst, $now, $this->menuId, $satker, $id, $title, $size, $file, $path); 
            } 

            //dBase::setLogNotification($rst, $satker, $last_user, $now, 'Update', 'Ubah data '. $this->menuLog);
        
            $satker_by_user = dBase::dbGetFieldById('tm_user', 'satker_id', 'user_id', $last_user);
            if($satker_by_user == "") {
                if($status != 1) {
                    $nmSatker  = dBase::dbGetFieldById('tm_satker', 'satker_name', 'satker_id', $satker);
                    $arrSatker = Dbase::parentSatker($satker, 1);
                    for($i=0; $i<count($arrSatker); $i++) {
                        $satker_id = $arrSatker[$i]['satker_id'];
                        dBase::setLogNotification($rst, $satker_id, $last_user, $now, 'Update', 'Berita '. $nmSatker .' diturunkan');
                    }
                }
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
        $id         = $request->news_id;
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
                    "news_status"   => 0,
                    "is_deleted"    => 1,
                    "updated_at"    => $now,
                    "last_user"     => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]); 

            dBase::setLogActivity($rst, $last_user, $now, 'Delete', 'Hapus data '. $this->menuLog);  

            $satker = Dbase::dbGetFieldById($this->table, 'satker_id', $this->field, $id);
            dBase::removeContentActivity($rst, $this->menuId, $satker, $id);
            
            $category = Dbase::dbGetFieldById($this->table, 'news_category', $this->field, $id);
            if(($category == "Berita") || ($category == "Pengumuman") || ($category == "Kegiatan")) {
                dBase::removeUploadActivity($rst, $this->menuId, $satker, $id); 
            } 
      
            dBase::setLogNotification($rst, $satker, $last_user, $now, 'Delete', 'Hapus data '. $this->menuLog);
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
