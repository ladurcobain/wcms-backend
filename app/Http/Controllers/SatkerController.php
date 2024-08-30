<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Ciphertext;
use App\Helpers\Dbase;
use App\Helpers\Init;
use App\Helpers\Status;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class SatkerController extends Controller
{
    private $table = "tm_satker";
    private $field = "satker_id";

    public function getFull(Request $request) {
        $limit  = $request->limit;
        $limit  = (($limit == "")?10:$limit);
        $offset = $request->offset;
        $offset = (($offset == "")?0:$offset);
        
        $cnt = 0;
        $tmp = array();
        if(($request->name != "") && ($request->type != "")) {
            $tmp = DB::table($this->table)->where('satker_name', 'like', '%'.$request->name.'%')->where('satker_type', $request->type)->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('satker_code')->get();
            $cnt = DB::table($this->table)->where('satker_name', 'like', '%'.$request->name.'%')->where('satker_type', $request->type)->where('is_deleted', 0)->count();
        }    
        else if(($request->name != "") && ($request->type == "")) {
            $tmp = DB::table($this->table)->where('satker_name', 'like', '%'.$request->name.'%')->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('satker_code')->get();
            $cnt = DB::table($this->table)->where('satker_name', 'like', '%'.$request->name.'%')->where('is_deleted', 0)->count();
        }    
        else if(($request->name == "") && ($request->type != "")) {
            $tmp = DB::table($this->table)->where('satker_type', $request->type)->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('satker_code')->get();
            $cnt = DB::table($this->table)->where('satker_type', $request->type)->where('is_deleted', 0)->count();
        }    
        else {
            $tmp = DB::table($this->table)->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('satker_code')->get();
            $cnt = DB::table($this->table)->where('is_deleted', 0)->count();
        }
        
        $arr = Init::initSatkerFull(1, $tmp);
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
        if(($request->name != "") && ($request->type != "")) {
            $tmp = DB::table($this->table)->where('satker_name', 'like', '%'.$request->name.'%')->where('satker_type', $request->type)->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('satker_type')->get();
            $cnt = DB::table($this->table)->where('satker_name', 'like', '%'.$request->name.'%')->where('satker_type', $request->type)->where('is_deleted', 0)->count();
        }    
        else if(($request->name != "") && ($request->type == "")) {
            $tmp = DB::table($this->table)->where('satker_name', 'like', '%'.$request->name.'%')->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('satker_type')->get();
            $cnt = DB::table($this->table)->where('satker_name', 'like', '%'.$request->name.'%')->where('is_deleted', 0)->count();
        }    
        else if(($request->name == "") && ($request->type != "")) {
            $tmp = DB::table($this->table)->where('satker_type', $request->type)->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('satker_type')->get();
            $cnt = DB::table($this->table)->where('satker_type', $request->type)->where('is_deleted', 0)->count();
        }    
        else {
            $tmp = DB::table($this->table)->where('is_deleted', 0)->take($limit)->skip($offset)->orderBy('satker_type')->get();
            $cnt = DB::table($this->table)->where('is_deleted', 0)->count();
        }
        
        $arr = Init::initSatker(1, $tmp);
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
        $id   = $request->satker_id;
        $data = DB::table($this->table)->where($this->field, $id)->get();
        $arr  = Init::initSatker(0, $data);

        return Init::initResponse($arr, 'View');
    }

    public function insertData(Request $request) {
        $type          = $request->type;
        $name          = $request->name;
        $phone         = $request->phone;
        $email         = $request->email;
        $address       = $request->address;
        $embed_map     = $request->embed_map;
        $url_facebook  = $request->url_facebook;
        $url_twitter   = $request->url_twitter;
        $url_instagram = $request->url_instagram;
        $description   = $request->description;
        $last_user     = $request->last_user;

        $validate = Init::initValidate(
            array('name', 'type'), 
            array($name, $type)
        );

        if($validate == "") {
            $exist = DB::table($this->table)->where('satker_name', $request->name)->where('is_deleted', 0)->count();
            if($exist <= 0) {
                $slug = Status::slugCharacters($name);
                $url  = Init::landingUrl() . $slug ."/home/";
                $now  = Carbon::now();
                
                $rst = DB::table($this->table)
                    ->insertGetId([
                        "satker_slug"        => $slug,
                        "satker_type"        => $type,
                        "satker_name"        => (($name == null)? "":Status::htmlCharacters($name)),
                        "satker_phone"       => (($phone == null)? "":$phone),
                        "satker_email"       => (($email == null)? "":$email),
                        "satker_address"     => (($address == null)? "":nl2br($address)),
                        "satker_map"         => (($embed_map == null)? "":$embed_map),
                        "satker_facebook"    => (($url_facebook == null)? "":$url_facebook),
                        "satker_twitter"     => (($url_twitter == null)? "":$url_twitter),
                        "satker_instagram"   => (($url_instagram == null)? "":$url_instagram),
                        "satker_description" => (($description == null)? "":nl2br($description)),
                        "satker_url"         => $url,
                        "created_at"         => $now,
                        "updated_at"         => $now,
                        "last_user"          => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                    ]);   
            
                dBase::setLogActivity($rst, $last_user, $now, 'Insert', 'Simpan data satuan kerja');  
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
        $id            = $request->satker_id;
        $status        = $request->status;
        $url           = $request->url;
        $name          = $request->name;
        $phone         =  $request->phone;
        $email         = $request->email;
        $address       = $request->address;
        $embed_map     = $request->embed_map;
        $url_facebook  = $request->url_facebook;
        $url_twitter   = $request->url_twitter;
        $url_instagram = $request->url_instagram;
        $description   = $request->description;
        $last_user     = $request->last_user;
        
        $validate = Init::initValidate(
            array('id', 'name'), 
            array($id, $name)
        );

        if($validate == "") {
            $slug = Status::slugCharacters($name);
            $uri  = Init::landingUrl() . $slug ."/home/";
            $now  = Carbon::now();

            $rst = DB::table($this->table)
                ->where($this->field, $id)
                ->update([
                    "satker_slug"        => $slug,
                    "satker_name"        => (($name == null)? "":Status::htmlCharacters($name)),
                    "satker_phone"       => (($phone == null)? "":$phone),
                    "satker_email"       => (($email == null)? "":$email),
                    "satker_address"     => (($address == null)? "":nl2br($address)),
                    "satker_map"         => (($embed_map == null)? "":$embed_map),
                    "satker_facebook"    => (($url_facebook == null)? "":$url_facebook),
                    "satker_twitter"     => (($url_twitter == null)? "":$url_twitter),
                    "satker_instagram"   => (($url_instagram == null)? "":$url_instagram),
                    "satker_description" => (($description == null)? "":nl2br($description)),
                    "satker_status"      => (($status == 1)? 1:0),
                    "satker_url"         => (($url == null)? $uri:$url),
                    "updated_at"         => $now,
                    "last_user"          => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]);  

            dBase::setLogActivity($rst, $last_user, $now, 'Update', 'Ubah data satuan kerja');     
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
        $id         = $request->satker_id;
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
                    "satker_status" => 0,
                    "is_deleted"    => 1,
                    "updated_at"    => $now,
                    "last_user"     => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]); 

            dBase::setLogActivity($rst, $last_user, $now, 'Delete', 'Hapus data satuan kerja');    
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
        $id        = $request->satker_id;
        $menu      = $request->menu_id;
        $parent    = $request->menu_parent;
        $last_user = $request->last_user;

        $validate = Init::initValidate(
            array('id'), 
            array($id)
        );

        if($validate == "") {
            $table = "tr_navigation";
            DB::table($table)->where($this->field, $id)->where('menu_parent', $parent)->delete();
            
            if($menu != "") {
                if($parent != 0) {
                    $rst = DB::table($table)
                        ->insertGetId([
                            "menu_parent" => $parent,
                            "menu_id"     => $parent,
                            "satker_id"   => $id,
                        ]); 
                }

                for($i=0; $i<count($menu); $i++) {
                    $menu_id = $menu[$i];
                    $rst = DB::table($table)
                        ->insertGetId([
                            "menu_parent" => $parent,
                            "menu_id"     => $menu_id,
                            "satker_id"   => $id,
                        ]);   
                }
                
                $now = Carbon::now();
                dBase::setLogActivity($rst, $last_user, $now, 'Process', 'Proses data navigasi menu satker');
            }
            else {
                $rst = 1;
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
        $id   = $request->satker_id;
        $data = DB::table('tr_navigation')->where($this->field, $id)->get();
        
        $arr = array();
        if($data != "[]") {
            foreach($data as $r) {
                $arr[] = $r->menu_id;
            }
        }
        
        return Init::initResponse($arr, 'View');
    }

    public function getNavigation(Request $request) {
        $id   = $request->satker_id;
        $data = DB::table('tr_navigation')->where($this->field, $id)->get();
        
        $arr = array();
        if($data != "[]") {
            foreach($data as $r) {
                $arr[] = dBase::dbGetFieldById('tm_menu', 'menu_name', 'menu_id', $r->menu_id);
            }
        }
        
        return Init::initResponse($arr, 'View');
    }

    public function updateInfo(Request $request) {
        $id            = $request->satker_id;
        $status        = $request->status;
        $url           = $request->url;
        $phone         = $request->phone;
        $email         = $request->email;
        $address       = $request->address;
        $overlay       = $request->overlay;
        $last_user     = $request->last_user;
        
        $validate = Init::initValidate(
            array('id'), 
            array($id)
        );

        if($validate == "") {
            $now  = Carbon::now();
            $slug = dBase::dbGetFieldById('tm_satker', 'satker_slug', 'satker_id', $id);
            $uri  = Init::landingUrl() . $slug ."/home/";

            $rst = DB::table($this->table)
                ->where($this->field, $id)
                ->update([
                    "satker_phone"       => (($phone == null)? "":$phone),
                    "satker_email"       => (($email == null)? "":$email),
                    "satker_address"     => (($address == null)? "":nl2br($address)),
                    "satker_overlay"     => (($overlay == null)? "":$overlay),
                    "satker_status"      => (($status == 1)? 1:0),
                    "satker_url"         => (($url == null)? $uri:$url),
                    "updated_at"         => $now,
                    "last_user"          => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]);  

            dBase::setLogActivity($rst, $last_user, $now, 'Update', 'Ubah data satuan kerja');     
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

    public function updateMedsos(Request $request) {
        $id            = $request->satker_id;
        $map_google    = $request->embed_map;
        $url_facebook  = $request->url_facebook;
        $url_twitter   = $request->url_twitter;
        $url_instagram = $request->url_instagram;
        $description   = $request->description;
        $last_user     = $request->last_user;
        
        $validate = Init::initValidate(
            array('id'), 
            array($id)
        );

        if($validate == "") {
            $now  = Carbon::now();

            $embed_map = Status::find_link_map($map_google);
            $rst = DB::table($this->table)
                ->where($this->field, $id)
                ->update([
                    "satker_map"         => (($embed_map == null)? "":$embed_map),
                    "satker_facebook"    => (($url_facebook == null)? "":$url_facebook),
                    "satker_twitter"     => (($url_twitter == null)? "":$url_twitter),
                    "satker_instagram"   => (($url_instagram == null)? "":$url_instagram),
                    "satker_description" => (($description == null)? "":nl2br($description)),
                    "updated_at"         => $now,
                    "last_user"          => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]);  

            dBase::setLogActivity($rst, $last_user, $now, 'Update', 'Ubah data satuan kerja');     
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

    public function updateSupport(Request $request) {
        $id       = $request->satker_id;
        $whatsapp = $request->whatsapp;
        $opening  = $request->opening;
        $last_user     = $request->last_user;
        
        $validate = Init::initValidate(
            array('id'), 
            array($id)
        );

        if($validate == "") {
            $now  = Carbon::now();

            $rst = DB::table($this->table)
                ->where($this->field, $id)
                ->update([
                    "satker_whatsapp"   => (($whatsapp == null)? "":$whatsapp),
                    "satker_opening"    => (($opening == null)? "":$opening),
                    "updated_at"        => $now,
                    "last_user"         => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]);  

            dBase::setLogActivity($rst, $last_user, $now, 'Update', 'Ubah data satuan kerja');     
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

    public function updateVideos(Request $request) {
        $id             = $request->satker_id;
        $videotitle     = $request->videotitle;
        $videosubtitle  = $request->videosubtitle;
        $videotype      = $request->videotype;
        $videolink      = $request->videolink;
        $last_user      = $request->last_user;
        
        $validate = Init::initValidate(
            array('id'), 
            array($id)
        );

        if($validate == "") {
            $old_video = Dbase::dbGetFieldById('tm_satker', 'satker_videolink', 'satker_id', $id);
            if($old_video == "") {
                $old_video = Dbase::dbGetFieldById('tm_satker', 'satker_videopath', 'satker_id', $id);
            }
            
            $file = null; $path = "-"; $folder = "video"; $size = 0;
            if ($request->hasFile('userfile')) {
                $files = $request->file('userfile');
                $original_filename = $files->getClientOriginalName();
                $original_filename_arr = explode('.', $original_filename);
                $file_ext = end($original_filename_arr);
                $size = $files->getSize();

                if(($file_ext == "mp4") || ($file_ext == "avi")) {    
                    $image = 'VID-' . time() . '.' . $file_ext;
                    $destination_path = storage_path('assets/uploads/'. $folder);
                    if ($files->move($destination_path, $image)) {
                        $file = $image;
                        $path = Init::storagePath() ."/". $folder ."/". $file;

                        if($request->old_videotype == 2) {
                            $old_filename = str_replace(Init::storagePath() ."/". $folder, '', $request->old_videopath);
                            $old_filename = str_replace('/', '', $old_filename);
                            
                            unlink($destination_path ."/". $old_filename); 
                        }

                        $videolink = $path;
                    } 
                }
                else {
                    return response()->json([
                        'status'    => Init::responseStatus(0),
                        'message'   => "Jenis berkas unggahan: mp4, avi",
                        'data'      => array()],
                        200
                    );
                }
            }
            else {
                $videolink = Status::youtube_watch($videolink);
            }
            
            $now  = Carbon::now();
            $rst = DB::table($this->table)
                ->where($this->field, $id)
                ->update([
                    "satker_videotitle"     => (($videotitle == null)? "":$videotitle),
                    "satker_videosubtitle"  => (($videosubtitle == null)? "":$videosubtitle),
                    "satker_videotype"      => (($videotype == null)? 1:$videotype),
                    "satker_videolink"      => (($videotype == 1)? $videolink:""),
                    "satker_videopath"      => (($videotype == 2)? $videolink:""),
                    "updated_at"            => $now,
                    "last_user"             => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]);  

            dBase::setLogActivity($rst, $last_user, $now, 'Update', 'Ubah data satuan kerja');    
            if($old_video == "") {
                dBase::processUploadActivity($id, $now, 1, $id, 2, $videotitle, $size, "-", $videolink);
            }
            else {
                dBase::ChangeUploadActivity($id, $now, 1, $id, 2, $videotitle, $size, "-", $videolink);
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

    public function updatePatterns(Request $request) {
        $id        = $request->satker_id;
        $pattern   = $request->pattern;
        $is_cover  = $request->is_cover;
        $last_user = $request->last_user;
        
        $validate = Init::initValidate(
            array('id'), 
            array($id)
        );

        if($validate == "") {
            $now  = Carbon::now();

            $rst = DB::table($this->table)
                ->where($this->field, $id)
                ->update([
                    "satker_pattern"   => (($pattern == null)? "":$pattern),
                    "is_cover"         => $is_cover,
                    "updated_at"       => $now,
                    "last_user"        => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]);  

            dBase::setLogActivity($rst, $last_user, $now, 'Update', 'Ubah data satuan kerja');     
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

    public function updateBackgrounds(Request $request) {
        $id         = $request->satker_id;
        $background = $request->background;
        $last_user  = $request->last_user;
        
        $validate = Init::initValidate(
            array('id'), 
            array($id)
        );

        if($validate == "") {
            $now  = Carbon::now();

            $rst = DB::table($this->table)
                ->where($this->field, $id)
                ->update([
                    "satker_background" => (($background == null)? "":$background),
                    "updated_at"        => $now,
                    "last_user"         => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]);  

            dBase::setLogActivity($rst, $last_user, $now, 'Update', 'Ubah data satuan kerja');     
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