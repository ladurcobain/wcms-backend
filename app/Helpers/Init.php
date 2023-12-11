<?php
    namespace App\Helpers;

    use Exception;
    use App\Helpers\Dbase;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\DB;

    class Init {

        public static function backendUrl()
        {
            $str = Endpoint::backendEndpoint();
            return $str;
        } 
         
        public static function frontUrl() {
            $str = Endpoint::frontEndpoint();
            return $str;
        }
        public static function landingUrl() {
            $str = Endpoint::landingEndpoint();
            return $str;
        }

        public static function storagePath() {
            $endpoint = Endpoint::storagePathEndpoint();
            return $endpoint;
        }

        public static function defaultImage() {
            $path = Init::frontUrl() ."assets/img/logo-wcms.png";
            return $path;
        }

        public static function defaultStartDate() {
            $str = "2023-01-01";
            return $str;
        }

        public static function defaultEndDate() {
            $str = "2023-12-31";
            return $str;
        }

        public static function responseStatus($rst) {
            $status = (($rst != 0)?true:false);
            return $status;
        }

        public static function responseMessage($rst, $operation) {
            $status    = (($rst != 0)?"Berhasil":"Gagal");
            
            $operation = (($operation == 'View')?"Menampilkan":$operation);
            if($operation == "Masuk Aplikasi") {
                $message   = $status .' '. $operation;
            }
            else if($operation == "Logout") {
                $message   = $status .' '. $operation;
            }
            else if($operation == "Process") {
                $operation = "Proses";
                $message   = $status .' '. $operation .' Data';
            }
            else if($operation == "Insert") {
                $operation = "Simpan";
                $message   = $status .' '. $operation .' Data';
            }
            else if($operation == "Update") {
                $operation = "Ubah";
                $message   = $status .' '. $operation .' Data';
            }
            else if($operation == "Delete") {
                $operation = "Hapus";
                $message   = $status .' '. $operation .' Data';
            }
            else if($operation == "Rating") {
                $message = "Terima kasih atas penilaian Anda berikan";
            }
            else if($operation == "Newsletter") {
                $message = "Terima kasih Anda telah berlangganan";
            }
            else if($operation == "Contact") {
                $message = "Terima kasih pesan Anda berhasil terkirim";
            }
            else {
                $message   = $status .' '. $operation .' Data';    
            }
            
            

            return $message;
        }

        public static function initResponse($data, $operation) {
            if(!empty($data)) {
                $rst = 1;
            }
            else {
                $rst = 0;
            }
            
            return response()->json([
                'status'    => Init::responseStatus($rst),
                'message'   => Init::responseMessage($rst, $operation),
                'data'      => $data],
                200
            );
        } 

        public static function initValidate($arrLabel, $arrValue) {
            $message = "";
            if(!empty($arrLabel)) {
                for($i=0; $i<count($arrLabel); $i++) {
                    if ($arrValue[$i] == "") {
                        $message = "Kurang parameter ". $arrLabel[$i];  
                        break;
                    } 
                }
            }
            
            return $message;
        } 
        
        public static function checkHeader($request) {
            $header = $request->header('X-api-key');
            if($header != "webphada") {
                return false;
            }
            else {
                return true;
            }
        }

        // INIT TABLE
        public static function initUser($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
                    
                    $role_id = (($row->role_id == null? "":$row->role_id));         
                    if($role_id != "") {
                        $role_name = Dbase::dbGetFieldById('tp_role', 'role_name', 'role_id', $role_id);
                    }   

                    $satker_id = (($row->satker_id == null? "":$row->satker_id));         
                    if($satker_id != "") {
                        $satker_name = Dbase::dbGetFieldById('tm_satker', 'satker_name', 'satker_id', $satker_id);
                        $satker_slug = Dbase::dbGetFieldById('tm_satker', 'satker_slug', 'satker_id', $satker_id);
                        $satker_url  = Dbase::dbGetFieldById('tm_satker', 'satker_url', 'satker_id', $satker_id);
                    }   

                    if($row->user_login != null) {
                        $user_login = Carbon::createFromFormat('Y-m-d H:i:s', $row->user_login)
                            ->format('d-m-Y H:i:s');
                    }

                    $user_activity = Dbase::dbGetFieldByIdOrder('tr_activity', 'activity_description', 'user_id', $row->user_id, 'activity_id');
                    if($flag == 1) {
                        $arr[] = array(
                            "user_id"       => $row->user_id,
                            "user_type"     => $row->user_type,
                            "user_account"  => $row->user_account,
                            "user_code"     => (($row->user_code == null? "":$row->user_code)),
                            "user_fullname" => (($row->user_fullname == null? "":$row->user_fullname)),
                            "user_phone"    => (($row->user_phone == null? "":$row->user_phone)),
                            "user_email"    => (($row->user_email == null? "":$row->user_email)),
                            "user_address"  => (($row->user_address == null? "":$row->user_address)),
                            "user_status"   => $row->user_status,
                            "user_login"    => (($row->user_login == null? "":$user_login)),
                            "user_activity" => $user_activity,
                            "user_token"    => (($row->user_token == null? "":$row->user_token)),
                            "user_image"    => (($row->user_image == null? "":$row->user_image)),
                            "user_path"     => (($row->user_image == null? Init::defaultImage():$row->user_path)),
                            "satker_id"     => (($satker_id == null? "":strval($satker_id))),
                            "satker_name"   => (($satker_id == null? "":$satker_name)),
                            "satker_slug"   => (($satker_id == null? "":$satker_slug)),
                            "satker_url"    => (($satker_id == null? "":$satker_url)),
                            "role_id"       => (($role_id == null? "":strval($role_id))),
                            "role_name"     => (($role_id == null? "":$role_name)),
                            "created_at"    => $created_at,
                            "updated_at"    => $updated_at,
                            "last_user"     => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "user_id"       => $row->user_id,
                            "user_type"     => $row->user_type,
                            "user_account"  => $row->user_account,
                            "user_code"     => (($row->user_code == null? "":$row->user_code)),
                            "user_fullname" => (($row->user_fullname == null? "":$row->user_fullname)),
                            "user_phone"    => (($row->user_phone == null? "":$row->user_phone)),
                            "user_email"    => (($row->user_email == null? "":$row->user_email)),
                            "user_address"  => (($row->user_address == null? "":$row->user_address)),
                            "user_status"   => $row->user_status,
                            "user_login"    => (($row->user_login == null? "":$user_login)),
                            "user_activity" => $user_activity,
                            "user_token"    => (($row->user_token == null? "":$row->user_token)),
                            "user_image"    => (($row->user_image == null? "":$row->user_image)),
                            "user_path"     => (($row->user_image == null? Init::defaultImage():$row->user_path)),
                            "satker_id"     => (($satker_id == null? "":strval($satker_id))),
                            "satker_name"   => (($satker_id == null? "":$satker_name)),
                            "satker_slug"   => (($satker_id == null? "":$satker_slug)),
                            "satker_url"    => (($satker_id == null? "":$satker_url)),
                            "role_id"       => (($role_id == null? "":strval($role_id))),
                            "role_name"     => (($role_id == null? "":$role_name)),
                            "created_at"    => $created_at,
                            "updated_at"    => $updated_at,
                            "last_user"     => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }


        public static function initSatkerFull($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    $temp  = DB::table('tr_navigation')->where('satker_id', $row->satker_id)->get();
                    $child = array();
                    if($temp != "[]") {
                        foreach($temp as $r) {
                            $child[] = dBase::dbGetFieldById('tm_menu', 'menu_name', 'menu_id', $r->menu_id);
                        }
                    }
                    
                    if($flag == 1) {
                        $arr[] = array(
                            "satker_id"             => $row->satker_id,
                            "satker_type"           => $row->satker_type,
                            "satker_code"           => $row->satker_code,
                            "satker_akronim"        => $row->satker_akronim,
                            "satker_slug"           => $row->satker_slug,
                            "satker_name"           => (($row->satker_name == null? "":$row->satker_name)),
                            "satker_phone"          => (($row->satker_phone == null? "":$row->satker_phone)),
                            "satker_email"          => (($row->satker_email == null? "":$row->satker_email)),
                            "satker_address"        => (($row->satker_address == null? "":$row->satker_address)),
                            "satker_map"            => (($row->satker_map == null? "":$row->satker_map)),
                            "satker_facebook"       => (($row->satker_facebook == null? "":$row->satker_facebook)),
                            "satker_twitter"        => (($row->satker_twitter == null? "":$row->satker_twitter)),
                            "satker_instagram"      => (($row->satker_instagram == null? "":$row->satker_instagram)),
                            "satker_description"    => (($row->satker_description == null? "":$row->satker_description)),
                            "satker_whatsapp"       => (($row->satker_whatsapp == null? "":$row->satker_whatsapp)),
                            "satker_opening"        => (($row->satker_opening == null? "":$row->satker_opening)),
                            "satker_videotitle"     => (($row->satker_videotitle == null? "":$row->satker_videotitle)),
                            "satker_videosubtitle"  => (($row->satker_videosubtitle == null? "":$row->satker_videosubtitle)),
                            "satker_videolink"      => (($row->satker_videolink == null? "":$row->satker_videolink)),
                            "satker_videopath"      => (($row->satker_videopath == null? "":$row->satker_videopath)),
                            "satker_videotype"      => (($row->satker_videotype == null? "":$row->satker_videotype)),
                            "satker_url"            => (($row->satker_url == null? "":$row->satker_url)),
                            "satker_link"           => (($row->satker_link == null? "":$row->satker_link)),
                            "satker_version"        => (($row->satker_version == null? "":$row->satker_version)),
                            "is_cover"              => $row->is_cover,
                            "satker_pattern"        => (($row->satker_pattern == null? "":$row->satker_pattern)),
                            "satker_background"     => (($row->satker_background == null? "":$row->satker_background)),
                            "satker_overlay"        => (($row->satker_overlay == null? "":$row->satker_overlay)),
                            "satker_status"         => $row->satker_status,
                            "created_at"            => $created_at,
                            "updated_at"            => $updated_at,
                            "last_user"             => (($row->last_user == null? "":$row->last_user)),
                            'child'                 => $child
                        );
                    }            
                    else {
                        $arr = array(
                            "satker_id"             => $row->satker_id,
                            "satker_type"           => $row->satker_type,
                            "satker_code"           => $row->satker_code,
                            "satker_akronim"        => $row->satker_akronim,
                            "satker_slug"           => $row->satker_slug,
                            "satker_name"           => (($row->satker_name == null? "":$row->satker_name)),
                            "satker_phone"          => (($row->satker_phone == null? "":$row->satker_phone)),
                            "satker_email"          => (($row->satker_email == null? "":$row->satker_email)),
                            "satker_address"        => (($row->satker_address == null? "":$row->satker_address)),
                            "satker_map"            => (($row->satker_map == null? "":$row->satker_map)),
                            "satker_facebook"       => (($row->satker_facebook == null? "":$row->satker_facebook)),
                            "satker_twitter"        => (($row->satker_twitter == null? "":$row->satker_twitter)),
                            "satker_instagram"      => (($row->satker_instagram == null? "":$row->satker_instagram)),
                            "satker_description"    => (($row->satker_description == null? "":$row->satker_description)),
                            "satker_whatsapp"       => (($row->satker_whatsapp == null? "":$row->satker_whatsapp)),
                            "satker_opening"        => (($row->satker_opening == null? "":$row->satker_opening)),
                            "satker_videotitle"     => (($row->satker_videotitle == null? "":$row->satker_videotitle)),
                            "satker_videosubtitle"  => (($row->satker_videosubtitle == null? "":$row->satker_videosubtitle)),
                            "satker_videolink"      => (($row->satker_videolink == null? "":$row->satker_videolink)),
                            "satker_videopath"      => (($row->satker_videopath == null? "":$row->satker_videopath)),
                            "satker_videotype"      => (($row->satker_videotype == null? "":$row->satker_videotype)),
                            "satker_url"            => (($row->satker_url == null? "":$row->satker_url)),
                            "satker_link"           => (($row->satker_link == null? "":$row->satker_link)),
                            "satker_version"        => (($row->satker_version == null? "":$row->satker_version)),
                            "is_cover"              => $row->is_cover,
                            "satker_pattern"        => (($row->satker_pattern == null? "":$row->satker_pattern)),
                            "satker_background"     => (($row->satker_background == null? "":$row->satker_background)),
                            "satker_overlay"        => (($row->satker_overlay == null? "":$row->satker_overlay)),
                            "satker_status"         => $row->satker_status,
                            "created_at"            => $created_at,
                            "updated_at"            => $updated_at,
                            "last_user"             => (($row->last_user == null? "":$row->last_user)),
                            'child'                 => $child
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initSatker($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "satker_id"             => $row->satker_id,
                            "satker_type"           => $row->satker_type,
                            "satker_code"           => $row->satker_code,
                            "satker_akronim"        => $row->satker_akronim,
                            "satker_slug"           => $row->satker_slug,
                            "satker_name"           => (($row->satker_name == null? "":$row->satker_name)),
                            "satker_phone"          => (($row->satker_phone == null? "":$row->satker_phone)),
                            "satker_email"          => (($row->satker_email == null? "":$row->satker_email)),
                            "satker_address"        => (($row->satker_address == null? "":$row->satker_address)),
                            "satker_map"            => (($row->satker_map == null? "":$row->satker_map)),
                            "satker_facebook"       => (($row->satker_facebook == null? "":$row->satker_facebook)),
                            "satker_twitter"        => (($row->satker_twitter == null? "":$row->satker_twitter)),
                            "satker_instagram"      => (($row->satker_instagram == null? "":$row->satker_instagram)),
                            "satker_description"    => (($row->satker_description == null? "":$row->satker_description)),
                            "satker_whatsapp"       => (($row->satker_whatsapp == null? "":$row->satker_whatsapp)),
                            "satker_opening"        => (($row->satker_opening == null? "":$row->satker_opening)),
                            "satker_videotitle"     => (($row->satker_videotitle == null? "":$row->satker_videotitle)),
                            "satker_videosubtitle"  => (($row->satker_videosubtitle == null? "":$row->satker_videosubtitle)),
                            "satker_videolink"      => (($row->satker_videolink == null? "":$row->satker_videolink)),
                            "satker_videopath"      => (($row->satker_videopath == null? "":$row->satker_videopath)),
                            "satker_videotype"      => (($row->satker_videotype == null? "":$row->satker_videotype)),
                            "satker_url"            => (($row->satker_url == null? "":$row->satker_url)),
                            "satker_link"           => (($row->satker_link == null? "":$row->satker_link)),
                            "satker_version"        => (($row->satker_version == null? "":$row->satker_version)),
                            "is_cover"              => $row->is_cover,
                            "satker_pattern"        => (($row->satker_pattern == null? "":$row->satker_pattern)),
                            "satker_background"     => (($row->satker_background == null? "":$row->satker_background)),
                            "satker_overlay"        => (($row->satker_overlay == null? "":$row->satker_overlay)),
                            "satker_status"         => $row->satker_status,
                            "created_at"            => $created_at,
                            "updated_at"            => $updated_at,
                            "last_user"             => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "satker_id"             => $row->satker_id,
                            "satker_type"           => $row->satker_type,
                            "satker_code"           => $row->satker_code,
                            "satker_akronim"        => $row->satker_akronim,
                            "satker_slug"           => $row->satker_slug,
                            "satker_name"           => (($row->satker_name == null? "":$row->satker_name)),
                            "satker_phone"          => (($row->satker_phone == null? "":$row->satker_phone)),
                            "satker_email"          => (($row->satker_email == null? "":$row->satker_email)),
                            "satker_address"        => (($row->satker_address == null? "":$row->satker_address)),
                            "satker_map"            => (($row->satker_map == null? "":$row->satker_map)),
                            "satker_facebook"       => (($row->satker_facebook == null? "":$row->satker_facebook)),
                            "satker_twitter"        => (($row->satker_twitter == null? "":$row->satker_twitter)),
                            "satker_instagram"      => (($row->satker_instagram == null? "":$row->satker_instagram)),
                            "satker_description"    => (($row->satker_description == null? "":$row->satker_description)),
                            "satker_whatsapp"       => (($row->satker_whatsapp == null? "":$row->satker_whatsapp)),
                            "satker_opening"        => (($row->satker_opening == null? "":$row->satker_opening)),
                            "satker_videotitle"     => (($row->satker_videotitle == null? "":$row->satker_videotitle)),
                            "satker_videosubtitle"  => (($row->satker_videosubtitle == null? "":$row->satker_videosubtitle)),
                            "satker_videolink"      => (($row->satker_videolink == null? "":$row->satker_videolink)),
                            "satker_videopath"      => (($row->satker_videopath == null? "":$row->satker_videopath)),
                            "satker_videotype"      => (($row->satker_videotype == null? "":$row->satker_videotype)),
                            "satker_url"            => (($row->satker_url == null? "":$row->satker_url)),
                            "satker_link"           => (($row->satker_link == null? "":$row->satker_link)),
                            "satker_version"        => (($row->satker_version == null? "":$row->satker_version)),
                            "is_cover"              => $row->is_cover,
                            "satker_pattern"        => (($row->satker_pattern == null? "":$row->satker_pattern)),
                            "satker_background"     => (($row->satker_background == null? "":$row->satker_background)),
                            "satker_overlay"        => (($row->satker_overlay == null? "":$row->satker_overlay)),
                            "satker_status"         => $row->satker_status,
                            "created_at"            => $created_at,
                            "updated_at"            => $updated_at,
                            "last_user"             => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initSatkerInfo($satker_id, $data) {
            foreach ($data as $row) {
                $profile = array(
                    "satker_code"           => $row->satker_code,
                    "satker_akronim"        => $row->satker_akronim,
                    "satker_slug"           => $row->satker_slug,
                    "satker_name"           => (($row->satker_name == null? "-":$row->satker_name)),
                    "satker_phone"          => (($row->satker_phone == null? "-":$row->satker_phone)),
                    "satker_email"          => (($row->satker_email == null? "-":$row->satker_email)),
                    "satker_address"        => (($row->satker_address == null? "-":$row->satker_address)),
                    "satker_map"            => (($row->satker_map == null? "":$row->satker_map)),
                    "satker_facebook"       => (($row->satker_facebook == null? "":$row->satker_facebook)),
                    "satker_twitter"        => (($row->satker_twitter == null? "":$row->satker_twitter)),
                    "satker_instagram"      => (($row->satker_instagram == null? "":$row->satker_instagram)),
                    "satker_description"    => (($row->satker_description == null? "":$row->satker_description)),
                    "satker_whatsapp"       => (($row->satker_whatsapp == null? "":$row->satker_whatsapp)),
                    "satker_opening"        => (($row->satker_opening == null? "":$row->satker_opening)),
                    "satker_videotitle"     => (($row->satker_videotitle == null? "":$row->satker_videotitle)),
                    "satker_videosubtitle"  => (($row->satker_videosubtitle == null? "":$row->satker_videosubtitle)),
                    "satker_videolink"      => (($row->satker_videolink == null? "":$row->satker_videolink)),
                    "satker_videopath"      => (($row->satker_videopath == null? "":$row->satker_videopath)),
                    "satker_videotype"      => (($row->satker_videotype == null? "":$row->satker_videotype)),
                    "satker_url"            => (($row->satker_url == null? "":$row->satker_url)),
                    "satker_link"           => (($row->satker_link == null? "":$row->satker_link)),
                    "satker_version"        => (($row->satker_version == null? "":$row->satker_version)),
                    "is_cover"              => $row->is_cover,
                    "satker_pattern"        => (($row->satker_pattern == null? "":$row->satker_pattern)),
                    "satker_background"     => (($row->satker_background == null? "":$row->satker_background)),
                    "satker_overlay"        => (($row->satker_overlay == null? "":$row->satker_overlay)),
                );       
            }

            $medsos = array();
            $sosmed = DB::table('tp_medsos')->where('satker_id', $satker_id)->where('medsos_status', 1)->orderBy('medsos_id')->get();
            foreach($sosmed as $row) {
                $medsos[] = array(
                    'name' => Status::medsosName($row->medsos_type),
                    'url'  => $row->medsos_link,
                );
            }
    
            $related = array();
            $related[] = array(
                'name' => 'Kejaksaan Agung Republik Indonesia',   
                'url'  => 'https://www.kejaksaan.go.id/index.php'
            );

            $links = DB::table('tp_related')->where('satker_id', $satker_id)->where('related_status', 1)->orderBy('related_id')->get();
            foreach($links as $row) {
                $related[] = array(
                    'name' => $row->related_name,
                    'url'  => $row->related_link,
                );
            }

            $visitor = array();
            $now = Carbon::now();
            
            $dateOfYear = Carbon::createFromFormat('Y-m-d H:i:s', $now)->format('Y-m-d'); 
            $monthOfYear = Carbon::createFromFormat('Y-m-d H:i:s', $now)->format('m'); 
            $yearOfYear  = Carbon::createFromFormat('Y-m-d H:i:s', $now)->format('Y'); 
            
            $visitor[] = array(
                'title' => 'Total',
                'count' => DB::table('tr_visitor')->where('satker_id', $satker_id)->count()
            );

            $visitor[] = array(
                'title' => "Tahun ". $yearOfYear,
                'count' => DB::table('tr_visitor')->where('satker_id', $satker_id)->where(DB::raw('YEAR(visitor_date)'), '=', $yearOfYear)->count()
            );

            $visitor[] = array(
                'title' => Status::monthName(Carbon::createFromFormat('Y-m-d H:i:s', $now)->format('m')) .' '. $yearOfYear,
                'count' => DB::table('tr_visitor')->where('satker_id', $satker_id)->where('visitor_date', 'like', '%'.$yearOfYear .'-'. $monthOfYear.'%')->count()
            );

            $visitor[] = array(
                'title' => 'Hari ini',
                'count' => DB::table('tr_visitor')->where('satker_id', $satker_id)->where('visitor_date', $dateOfYear)->count()
            );

            $rating = array();
            $num = 0; $total = 0; $average = 0;
            for($i=5; $i >= 1; $i--) {
                $count = Dbase::dbGetCountByTwoId('tp_rating', 'satker_id', $satker_id, 'rating_value', $i);
                $num   = $num + $count;
            }

            $total = Dbase::dbSumFieldById('tp_rating', 'rating_value', 'satker_id', $satker_id); 
            if($num > 0) {
                $average = doubleval($total) / doubleval($num);
            }
            else {
                $average = 0;
            }

            $rating = array(
                'num'     => $num,
                'total'   => $total,
                'average' => doubleval(number_format($average, 2)),
            );


            $arr = array(
                'profile' => $profile,
                'medsos'  => $medsos,
                'related' => $related,
                'visitor' => $visitor,
                'rating'  => $rating
            );

            return $arr;
        }

        
        public static function initRoleFull($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    $temp  = DB::table('tr_authority')->where('role_id', $row->role_id)->get();
                    $child = array();
                    if($temp != "[]") {
                        foreach($temp as $r) {
                            $child[] = dBase::dbGetFieldById('tm_module', 'module_name', 'module_id', $r->module_id);
                        }
                    }
                    
                    if($flag == 1) {
                        $arr[] = array(
                            "role_id"           => $row->role_id,
                            "role_name"         => (($row->role_name == null? "":$row->role_name)),
                            "role_description"  => (($row->role_description == null? "":$row->role_description)),
                            "role_status"       => $row->role_status,
                            "created_at"        => $created_at,
                            "updated_at"        => $updated_at,
                            "last_user"         => (($row->last_user == null? "":$row->last_user)),
                            "child"             => $child,
                        );
                    }            
                    else {
                        $arr = array(
                            "role_id"           => $row->role_id,
                            "role_name"         => (($row->role_name == null? "":$row->role_name)),
                            "role_description"  => (($row->role_description == null? "":$row->role_description)),
                            "role_status"       => $row->role_status,
                            "created_at"        => $created_at,
                            "updated_at"        => $updated_at,
                            "last_user"         => (($row->last_user == null? "":$row->last_user)),
                            "child"             => $child,
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initRoleUser($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "role_id"           => $row->role_id,
                            "role_name"         => (($row->role_name == null? "":$row->role_name)),
                            "role_description"  => (($row->role_description == null? "":$row->role_description)),
                            "role_status"       => $row->role_status,
                            "created_at"        => $created_at,
                            "updated_at"        => $updated_at,
                            "last_user"         => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "role_id"           => $row->role_id,
                            "role_name"         => (($row->role_name == null? "":$row->role_name)),
                            "role_description"  => (($row->role_description == null? "":$row->role_description)),
                            "role_status"       => $row->role_status,
                            "created_at"        => $created_at,
                            "updated_at"        => $updated_at,
                            "last_user"         => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }


        public static function initConfigPreference($data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $arr = array(
                        "preference_id"             => $row->preference_id,
                        "preference_appname"        => (($row->preference_appname == null? "":$row->preference_appname)),
                        "preference_appicon"        => (($row->preference_appicon == null? "":$row->preference_appicon)),
                        "preference_appdescription" => (($row->preference_appdescription == null? "":$row->preference_appdescription)),
                        "last_user"                 => (($row->last_user == null? "":$row->last_user)),
                    );        
                }
            }

            return $arr;
        }

        public static function initConfigIntegration($data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $arr = array(
                        "integration_id"        => $row->integration_id,
                        "integration_backend"   => (($row->integration_backend == null? "":$row->integration_backend)),
                        "last_user"             => (($row->last_user == null? "":$row->last_user)),
                    );        
                }
            }

            return $arr;
        }
        

        public static function initMasterModule($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "module_id"             => $row->module_id,
                            "module_name"           => (($row->module_name == null? "":$row->module_name)),
                            "module_description"    => (($row->module_description == null? "":$row->module_description)),
                            "module_icon"           => (($row->module_icon == null? "":$row->module_icon)),
                            "module_url"            => (($row->module_url == null? "":$row->module_url)),
                            "module_position"       => (($row->module_position == null? 0:$row->module_position)),
                            "module_parent"         => (($row->module_parent == null? 0:$row->module_parent)),
                            "module_nav"            => (($row->module_nav == null? 0:$row->module_nav)),
                            "module_active"         => (($row->module_active == null? "":$row->module_active)),
                            "module_status"         => $row->module_status,
                            "created_at"            => $created_at,
                            "updated_at"            => $updated_at,
                            "last_user"             => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "module_id"             => $row->module_id,
                            "module_name"           => (($row->module_name == null? "":$row->module_name)),
                            "module_description"    => (($row->module_description == null? "":$row->module_description)),
                            "module_icon"           => (($row->module_icon == null? "":$row->module_icon)),
                            "module_url"            => (($row->module_url == null? "":$row->module_url)),
                            "module_position"       => (($row->module_position == null? 0:$row->module_position)),
                            "module_parent"         => (($row->module_parent == null? 0:$row->module_parent)),
                            "module_nav"            => (($row->module_nav == null? 0:$row->module_nav)),
                            "module_active"         => (($row->module_active == null? "":$row->module_active)),
                            "module_status"         => $row->module_status,
                            "created_at"            => $created_at,
                            "updated_at"            => $updated_at,
                            "last_user"             => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initMasterMenu($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "menu_id"           => $row->menu_id,
                            "menu_name"         => (($row->menu_name == null? "":$row->menu_name)),
                            "menu_label"        => (($row->menu_label == null? "":$row->menu_label)),
                            "menu_description"  => (($row->menu_description == null? "":$row->menu_description)),
                            "menu_parent"       => $row->menu_parent,
                            "menu_status"       => $row->menu_status,
                            "created_at"        => $created_at,
                            "updated_at"        => $updated_at,
                            "last_user"         => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "menu_id"           => $row->menu_id,
                            "menu_name"         => (($row->menu_name == null? "":$row->menu_name)),
                            "menu_label"        => (($row->menu_label == null? "":$row->menu_label)),
                            "menu_description"  => (($row->menu_description == null? "":$row->menu_description)),
                            "menu_parent"       => $row->menu_parent,
                            "menu_status"       => $row->menu_status,
                            "created_at"        => $created_at,
                            "updated_at"        => $updated_at,
                            "last_user"         => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initMasterTutorial($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "tutorial_id"           => $row->tutorial_id,
                            "tutorial_name"         => (($row->tutorial_name == null? "":$row->tutorial_name)),
                            "tutorial_file"         => (($row->tutorial_file == null? "":$row->tutorial_file)),
                            "tutorial_path"         => (($row->tutorial_file == null? "":$row->tutorial_path)),
                            "tutorial_description"  => (($row->tutorial_description == null? "":$row->tutorial_description)),
                            "tutorial_status"       => $row->tutorial_status,
                            "created_at"            => $created_at,
                            "updated_at"            => $updated_at,
                            "last_user"             => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "tutorial_id"           => $row->tutorial_id,
                            "tutorial_name"         => (($row->tutorial_name == null? "":$row->tutorial_name)),
                            "tutorial_file"         => (($row->tutorial_file == null? "":$row->tutorial_file)),
                            "tutorial_path"         => (($row->tutorial_file == null? "":$row->tutorial_path)),
                            "tutorial_description"  => (($row->tutorial_description == null? "":$row->tutorial_description)),
                            "tutorial_status"       => $row->tutorial_status,
                            "created_at"            => $created_at,
                            "updated_at"            => $updated_at,
                            "last_user"             => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initMasterPattern($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "pattern_id"           => $row->pattern_id,
                            "pattern_name"         => (($row->pattern_name == null? "":$row->pattern_name)),
                            "pattern_size"         => (($row->pattern_size == null? 0:$row->pattern_size)),
                            "pattern_image"        => (($row->pattern_image == null? "":$row->pattern_image)),
                            "pattern_path"         => (($row->pattern_path == null? "":$row->pattern_path)),
                            "pattern_description"  => (($row->pattern_description == null? "":$row->pattern_description)),
                            "pattern_status"       => $row->pattern_status,
                            "created_at"           => $created_at,
                            "updated_at"           => $updated_at,
                            "last_user"            => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "pattern_id"           => $row->pattern_id,
                            "pattern_name"         => (($row->pattern_name == null? "":$row->pattern_name)),
                            "pattern_size"         => (($row->pattern_size == null? 0:$row->pattern_size)),
                            "pattern_image"        => (($row->pattern_image == null? "":$row->pattern_image)),
                            "pattern_path"         => (($row->pattern_path == null? "":$row->pattern_path)),
                            "pattern_description"  => (($row->pattern_description == null? "":$row->pattern_description)),
                            "pattern_status"       => $row->pattern_status,
                            "created_at"           => $created_at,
                            "updated_at"           => $updated_at,
                            "last_user"            => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initMasterCover($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "cover_id"           => $row->cover_id,
                            "cover_name"         => (($row->cover_name == null? "":$row->cover_name)),
                            "cover_size"         => (($row->cover_size == null? 0:$row->cover_size)),
                            "cover_image"        => (($row->cover_image == null? "":$row->cover_image)),
                            "cover_path"         => (($row->cover_path == null? "":$row->cover_path)),
                            "cover_description"  => (($row->cover_description == null? "":$row->cover_description)),
                            "cover_status"       => $row->cover_status,
                            "created_at"         => $created_at,
                            "updated_at"         => $updated_at,
                            "last_user"          => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "cover_id"           => $row->cover_id,
                            "cover_name"         => (($row->cover_name == null? "":$row->cover_name)),
                            "cover_size"         => (($row->cover_size == null? 0:$row->cover_size)),
                            "cover_image"        => (($row->cover_image == null? "":$row->cover_image)),
                            "cover_path"         => (($row->cover_path == null? "":$row->cover_path)),
                            "cover_description"  => (($row->cover_description == null? "":$row->cover_description)),
                            "cover_status"       => $row->cover_status,
                            "created_at"         => $created_at,
                            "updated_at"         => $updated_at,
                            "last_user"          => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initMasterRequest($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "request_id"            => $row->request_id,
                            "request_name"          => (($row->request_name == null? "":$row->request_name)),
                            "request_method"        => (($row->request_method == null? "":$row->request_method)),
                            "request_url"           => (($row->request_url == null? "":$row->request_url)),
                            "request_description"   => (($row->request_description == null? "":$row->request_description)),
                            "request_status"        => $row->request_status,
                            "created_at"            => $created_at,
                            "updated_at"            => $updated_at,
                            "last_user"             => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "request_id"            => $row->request_id,
                            "request_name"          => (($row->request_name == null? "":$row->request_name)),
                            "request_method"        => (($row->request_method == null? "":$row->request_method)),
                            "request_url"           => (($row->request_url == null? "":$row->request_url)),
                            "request_description"   => (($row->request_description == null? "":$row->request_description)),
                            "request_status"        => $row->request_status,
                            "created_at"            => $created_at,
                            "updated_at"            => $updated_at,
                            "last_user"             => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initMasterParam($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    if($flag == 1) {
                        $arr[] = array(
                            "param_id"            => $row->param_id,
                            "param_type"          => $row->param_type,
                            "param_initial"       => $row->param_initial,
                            "param_description"   => (($row->param_description == null? "":$row->param_description)),
                            "request_id"          => $row->request_id,
                        );
                    }            
                    else {
                        $arr = array(
                            "param_id"            => $row->param_id,
                            "param_type"          => $row->param_type,
                            "param_initial"       => $row->param_initial,
                            "param_description"   => (($row->param_description == null? "":$row->param_description)),
                            "request_id"          => $row->request_id,
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initMasterIntegration($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    $arr_param = array();
                    $param = DB::table('tr_param')->where('request_id', $row->request_id)->orderBy('param_id')->get();
                    $arr_param = Init::initMasterParam(1, $param);
                    if($flag == 1) {
                        $arr[] = array(
                            "request_id"            => $row->request_id,
                            "request_name"          => (($row->request_name == null? "":$row->request_name)),
                            "request_method"        => (($row->request_method == null? "":$row->request_method)),
                            "request_url"           => (($row->request_url == null? "":$row->request_url)),
                            "request_description"   => (($row->request_description == null? "":$row->request_description)),
                            "request_status"        => $row->request_status,
                            "created_at"            => $created_at,
                            "updated_at"            => $updated_at,
                            "last_user"             => (($row->last_user == null? "":$row->last_user)),
                            "parameter"             => $arr_param
                        );
                    }            
                    else {
                        $arr = array(
                            "request_id"            => $row->request_id,
                            "request_name"          => (($row->request_name == null? "":$row->request_name)),
                            "request_method"        => (($row->request_method == null? "":$row->request_method)),
                            "request_url"           => (($row->request_url == null? "":$row->request_url)),
                            "request_description"   => (($row->request_description == null? "":$row->request_description)),
                            "request_status"        => $row->request_status,
                            "created_at"            => $created_at,
                            "updated_at"            => $updated_at,
                            "last_user"             => (($row->last_user == null? "":$row->last_user)),
                            "parameter"             => $arr_param
                        );
                    }        
                }
            }

            return $arr;
        }


        // Activity
        public static function initActivityLog($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $datetime = $row->activity_date .' '. $row->activity_time;

                    $activity_date = Carbon::createFromFormat('Y-m-d H:i:s', $datetime)
                        ->format('d-m-Y');
                    $activity_time = Carbon::createFromFormat('Y-m-d H:i:s', $datetime)
                        ->format('H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "activity_id"           => $row->activity_id,
                            "activity_date"         => $activity_date,
                            "activity_time"         => $activity_time,
                            "activity_type"         => $row->activity_type,
                            "activity_description"  => (($row->activity_description == null? "":$row->activity_description)),
                            "activity_ip"           => (($row->activity_ip == null? "-":$row->activity_ip)),
                            "activity_agent"        => (($row->activity_agent == null? "-":$row->activity_agent)),
                            "activity_platform"     => (($row->activity_platform == null? "-":$row->activity_platform)),
                            "activity_device"       => (($row->activity_device == null? "-":$row->activity_device)),
                            "activity_browser"      => (($row->activity_browser == null? "-":$row->activity_browser)),
                            "activity_user"         => $row->activity_user,
                            "user_id"               => $row->user_id,
                        );
                    }            
                    else {
                        $arr = array(
                            "activity_id"           => $row->activity_id,
                            "activity_date"         => $activity_date,
                            "activity_time"         => $activity_time,
                            "activity_type"         => $row->activity_type,
                            "activity_description"  => (($row->activity_description == null? "":$row->activity_description)),
                            "activity_ip"           => (($row->activity_ip == null? "-":$row->activity_ip)),
                            "activity_user"         => $row->activity_user,
                            "user_id"               => $row->user_id,
                        );
                    }        
                }
            }

            return $arr;
        }


        // Notification
        public static function initNotification($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $datetime = $row->notification_date .' '. $row->notification_time;

                    $notification_date = Carbon::createFromFormat('Y-m-d H:i:s', $datetime)
                        ->format('d-m-Y');
                    $notification_time = Carbon::createFromFormat('Y-m-d H:i:s', $datetime)
                        ->format('H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "notification_id"           => $row->notification_id,
                            "notification_date"         => $notification_date,
                            "notification_time"         => $notification_time,
                            "notification_title"        => $row->notification_title,
                            "notification_description"  => (($row->notification_description == null? "":$row->notification_description)),
                            "notification_user"         => $row->notification_user,
                            "user_id"                   => $row->user_id,
                            "is_published"              => (($row->is_published == 1? 1:0)),
                            "is_read"                   => (($row->is_read == 1? 1:0)),
                        );
                    }            
                    else {
                        $arr = array(
                            "notification_id"           => $row->notification_id,
                            "notification_date"         => $notification_date,
                            "notification_time"         => $notification_time,
                            "notification_title"        => $row->notification_title,
                            "notification_description"  => (($row->notification_description == null? "":$row->notification_description)),
                            "notification_user"         => $row->notification_user,
                            "user_id"                   => $row->user_id,
                            "is_published"              => (($row->is_published == 1? 1:0)),
                            "is_read"                   => (($row->is_read == 1? 1:0)),
                        );
                    }        
                }
            }

            return $arr;
        }

        // Survey
        public static function initSurvey($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $datetime = $row->survey_date .' '. $row->survey_time;

                    $survey_date = Carbon::createFromFormat('Y-m-d H:i:s', $datetime)
                        ->format('d-m-Y');
                    $survey_time = Carbon::createFromFormat('Y-m-d H:i:s', $datetime)
                        ->format('H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "survey_id"           => $row->survey_id,
                            "survey_date"         => $survey_date,
                            "survey_time"         => $survey_time,
                            "survey_value"        => $row->survey_value,
                            "survey_description"  => (($row->survey_description == null? "":$row->survey_description)),
                            "survey_user"         => $row->survey_user,
                            "survey_image"        => Init::defaultImage(),
                            "user_id"             => $row->user_id,
                        );
                    }            
                    else {
                        $arr = array(
                            "survey_id"           => $row->survey_id,
                            "survey_date"         => $survey_date,
                            "survey_time"         => $survey_time,
                            "survey_value"        => $row->survey_value,
                            "survey_description"  => (($row->survey_description == null? "":$row->survey_description)),
                            "survey_user"         => $row->survey_user,
                            "survey_image"        => Init::defaultImage(),
                            "user_id"             => $row->user_id,
                        );
                    }        
                }
            }

            return $arr;
        }

        // visitor
        public static function initVisitor($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $datetime = $row->visitor_date .' '. $row->visitor_time;

                    $visitor_date = Carbon::createFromFormat('Y-m-d H:i:s', $datetime)
                        ->format('d-m-Y');
                    $visitor_time = Carbon::createFromFormat('Y-m-d H:i:s', $datetime)
                        ->format('H:i:s');
                    
                    $menu_id = (($row->menu_id == null? "":$row->menu_id));         
                    if($menu_id != "") {
                        $menu_name = Dbase::dbGetFieldById('tm_menu', 'menu_name', 'menu_id', $menu_id);
                    } 

                    $satker_id = (($row->satker_id == null? "":$row->satker_id));         
                    if($satker_id != "") {
                        $satker_name = Dbase::dbGetFieldById('tm_satker', 'satker_name', 'satker_id', $satker_id);
                    }   
                    
                    if($flag == 1) {
                        $arr[] = array(
                            "visitor_id"    => $row->visitor_id,
                            "visitor_date"  => $visitor_date,
                            "visitor_time"  => $visitor_time,
                            "visitor_ip"    => $row->visitor_ip,
                            "menu_id"       => $row->menu_id,
                            "menu_name"     => (($menu_id == ""? "":$menu_name)),
                            "satker_id"     => $row->satker_id,
                            "satker_name"   => (($satker_id == ""? "":$satker_name)),
                        );
                    }            
                    else {
                        $arr = array(
                            "visitor_id"    => $row->visitor_id,
                            "visitor_date"  => $visitor_date,
                            "visitor_time"  => $visitor_time,
                            "visitor_ip"    => $row->visitor_ip,
                            "menu_id"       => $row->menu_id,
                            "menu_name"     => (($menu_id == ""? "":$menu_name)),
                            "satker_id"     => $row->satker_id,
                            "satker_name"   => (($satker_id == ""? "":$satker_name)),
                        );
                    }        
                }
            }

            return $arr;
        }

        // Rating
        public static function initRating($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $datetime = $row->rating_date .' '. $row->rating_time;

                    $rating_date = Carbon::createFromFormat('Y-m-d H:i:s', $datetime)
                        ->format('d-m-Y');
                    $rating_time = Carbon::createFromFormat('Y-m-d H:i:s', $datetime)
                        ->format('H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "rating_id"           => $row->rating_id,
                            "rating_date"         => $rating_date,
                            "rating_time"         => $rating_time,
                            "rating_ip"           => $row->rating_ip,
                            "rating_value"        => $row->rating_value,
                            "rating_description"  => (($row->rating_description == null? "":$row->rating_description)),
                            "rating_satker"       => $row->rating_satker,
                            "satker_id"           => $row->satker_id,
                        );
                    }            
                    else {
                        $arr = array(
                            "rating_id"           => $row->rating_id,
                            "rating_date"         => $rating_date,
                            "rating_time"         => $rating_time,
                            "rating_ip"           => $row->rating_ip,
                            "rating_value"        => $row->rating_value,
                            "rating_description"  => (($row->rating_description == null? "":$row->rating_description)),
                            "rating_satker"       => $row->rating_satker,
                            "satker_id"           => $row->satker_id,
                        );
                    }        
                }
            }

            return $arr;
        }

        // Newsletter
        public static function initNewsletter($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $datetime = $row->newsletter_date .' '. $row->newsletter_time;

                    $newsletter_date = Carbon::createFromFormat('Y-m-d H:i:s', $datetime)
                        ->format('d-m-Y');
                    $newsletter_time = Carbon::createFromFormat('Y-m-d H:i:s', $datetime)
                        ->format('H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "newsletter_id"     => $row->newsletter_id,
                            "newsletter_date"   => $newsletter_date,
                            "newsletter_time"   => $newsletter_time,
                            "newsletter_email"  => $row->newsletter_email,
                            "newsletter_satker" => $row->newsletter_satker,
                            "satker_id"         => $row->satker_id,
                        );
                    }            
                    else {
                        $arr = array(
                            "newsletter_id"     => $row->newsletter_id,
                            "newsletter_date"   => $newsletter_date,
                            "newsletter_time"   => $newsletter_time,
                            "newsletter_email"  => $row->newsletter_email,
                            "newsletter_satker" => $row->newsletter_satker,
                            "satker_id"         => $row->satker_id,
                        );
                    }        
                }
            }

            return $arr;
        }

        // Contact-Us
        public static function initContactUs($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $datetime = $row->contactus_date .' '. $row->contactus_time;

                    $contactus_date = Carbon::createFromFormat('Y-m-d H:i:s', $datetime)
                        ->format('d-m-Y');
                    $contactus_time = Carbon::createFromFormat('Y-m-d H:i:s', $datetime)
                        ->format('H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "contactus_id"      => $row->contactus_id,
                            "contactus_date"    => $contactus_date,
                            "contactus_time"    => $contactus_time,
                            "contactus_name"    => $row->contactus_name,
                            "contactus_email"   => $row->contactus_email,
                            "contactus_subject" => (($row->contactus_subject == null? "":$row->contactus_subject)),
                            "contactus_message" => (($row->contactus_message == null? "":$row->contactus_message)),
                            "contactus_satker"  => $row->contactus_satker,
                            "satker_id"         => $row->satker_id,
                        );
                    }            
                    else {
                        $arr = array(
                            "contactus_id"      => $row->contactus_id,
                            "contactus_date"    => $contactus_date,
                            "contactus_time"    => $contactus_time,
                            "contactus_name"    => $row->contactus_name,
                            "contactus_email"   => $row->contactus_email,
                            "contactus_subject" => (($row->contactus_subject == null? "":$row->contactus_subject)),
                            "contactus_message" => (($row->contactus_message == null? "":$row->contactus_message)),
                            "contactus_satker"  => $row->contactus_satker,
                            "satker_id"         => $row->satker_id,
                        );
                    }        
                }
            }

            return $arr;
        }


        public static function initHomeBanner($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "banner_id"             => $row->banner_id,
                            "banner_name"           => (($row->banner_name == null? "":$row->banner_name)),
                            "banner_title_in"       => (($row->banner_title_in == null? "":$row->banner_title_in)),
                            "banner_subtitle_in"    => (($row->banner_subtitle_in == null? "":$row->banner_subtitle_in)),
                            "banner_title_en"       => (($row->banner_title_en == null? "":$row->banner_title_en)),
                            "banner_subtitle_en"    => (($row->banner_subtitle_en == null? "":$row->banner_subtitle_en)),
                            "banner_size"           => (($row->banner_size == null? 0:$row->banner_size)),
                            "banner_image"          => (($row->banner_image == null? "":$row->banner_image)),
                            "banner_path"           => (($row->banner_path == null? Init::defaultImage():$row->banner_path)),
                            "banner_status"         => $row->banner_status,
                            "banner_satker"         => $row->banner_satker,
                            "satker_id"             => $row->satker_id,
                            "created_at"            => $created_at,
                            "updated_at"            => $updated_at,
                            "last_user"             => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "banner_id"             => $row->banner_id,
                            "banner_name"           => (($row->banner_name == null? "":$row->banner_name)),
                            "banner_title_in"       => (($row->banner_title_in == null? "":$row->banner_title_in)),
                            "banner_subtitle_in"    => (($row->banner_subtitle_in == null? "":$row->banner_subtitle_in)),
                            "banner_title_en"       => (($row->banner_title_en == null? "":$row->banner_title_en)),
                            "banner_subtitle_en"    => (($row->banner_subtitle_en == null? "":$row->banner_subtitle_en)),
                            "banner_size"           => (($row->banner_size == null? 0:$row->banner_size)),
                            "banner_image"          => (($row->banner_image == null? "":$row->banner_image)),
                            "banner_path"           => (($row->banner_path == null? Init::defaultImage():$row->banner_path)),
                            "banner_status"         => $row->banner_status,
                            "banner_satker"         => $row->banner_satker,
                            "satker_id"             => $row->satker_id,
                            "created_at"            => $created_at,
                            "updated_at"            => $updated_at,
                            "last_user"             => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initHomeInfografis($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "infografis_id"         => $row->infografis_id,
                            "infografis_name"       => (($row->infografis_name == null? "":$row->infografis_name)),
                            "infografis_link"       => (($row->infografis_link == null? "":$row->infografis_link)),
                            "infografis_size"       => (($row->infografis_size == null? 0:$row->infografis_size)),
                            "infografis_image"      => (($row->infografis_image == null? "":$row->infografis_image)),
                            "infografis_path"       => (($row->infografis_path == null? Init::defaultImage():$row->infografis_path)),
                            "infografis_status"     => $row->infografis_status,
                            "infografis_satker"     => $row->infografis_satker,
                            "satker_id"             => $row->satker_id,
                            "created_at"            => $created_at,
                            "updated_at"            => $updated_at,
                            "last_user"             => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "infografis_id"         => $row->infografis_id,
                            "infografis_name"       => (($row->infografis_name == null? "":$row->infografis_name)),
                            "infografis_link"       => (($row->infografis_link == null? "":$row->infografis_link)),
                            "infografis_size"       => (($row->infografis_size == null? 0:$row->infografis_size)),
                            "infografis_image"      => (($row->infografis_image == null? "":$row->infografis_image)),
                            "infografis_path"       => (($row->infografis_path == null? Init::defaultImage():$row->infografis_path)),
                            "infografis_status"     => $row->infografis_status,
                            "infografis_satker"     => $row->infografis_satker,
                            "satker_id"             => $row->satker_id,
                            "created_at"            => $created_at,
                            "updated_at"            => $updated_at,
                            "last_user"             => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initHomeMedsos($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "medsos_id"     => $row->medsos_id,
                            "medsos_type"   => (($row->medsos_type == null? "":$row->medsos_type)),
                            "medsos_link"   => (($row->medsos_link == null? "":$row->medsos_link)),
                            "medsos_status" => $row->medsos_status,
                            "medsos_satker" => $row->medsos_satker,
                            "satker_id"     => $row->satker_id,
                            "created_at"    => $created_at,
                            "updated_at"    => $updated_at,
                            "last_user"     => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "medsos_id"     => $row->medsos_id,
                            "medsos_type"   => (($row->medsos_type == null? "":$row->medsos_type)),
                            "medsos_link"   => (($row->medsos_link == null? "":$row->medsos_link)),
                            "medsos_status" => $row->medsos_status,
                            "medsos_satker" => $row->medsos_satker,
                            "satker_id"     => $row->satker_id,
                            "created_at"    => $created_at,
                            "updated_at"    => $updated_at,
                            "last_user"     => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }
        
        
        public static function initAboutInfo($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "info_id"       => $row->info_id,
                            "info_text_in"  => (($row->info_text_in == null? "":$row->info_text_in)),
                            "info_text_en"  => (($row->info_text_en == null? "":$row->info_text_en)),
                            "info_status"   => $row->info_status,
                            "info_satker"   => $row->info_satker,
                            "satker_id"     => $row->satker_id,
                            "created_at"    => $created_at,
                            "updated_at"    => $updated_at,
                            "last_user"     => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "info_id"       => $row->info_id,
                            "info_text_in"  => (($row->info_text_in == null? "":$row->info_text_in)),
                            "info_text_en"  => (($row->info_text_en == null? "":$row->info_text_en)),
                            "info_status"   => $row->info_status,
                            "info_satker"   => $row->info_satker,
                            "satker_id"     => $row->satker_id,
                            "created_at"    => $created_at,
                            "updated_at"    => $updated_at,
                            "last_user"     => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initAboutStory($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "story_id"       => $row->story_id,
                            "story_text_in"  => (($row->story_text_in == null? "":$row->story_text_in)),
                            "story_text_en"  => (($row->story_text_en == null? "":$row->story_text_en)),
                            "story_status"   => $row->story_status,
                            "story_satker"   => $row->story_satker,
                            "satker_id"      => $row->satker_id,
                            "created_at"     => $created_at,
                            "updated_at"     => $updated_at,
                            "last_user"      => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "story_id"       => $row->story_id,
                            "story_text_in"  => (($row->story_text_in == null? "":$row->story_text_in)),
                            "story_text_en"  => (($row->story_text_en == null? "":$row->story_text_en)),
                            "story_status"   => $row->story_status,
                            "story_satker"   => $row->story_satker,
                            "satker_id"      => $row->satker_id,
                            "created_at"     => $created_at,
                            "updated_at"     => $updated_at,
                            "last_user"      => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initAboutDoctrin($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "doctrin_id"        => $row->doctrin_id,
                            "doctrin_text_in"   => (($row->doctrin_text_in == null? "":$row->doctrin_text_in)),
                            "doctrin_text_en"   => (($row->doctrin_text_en == null? "":$row->doctrin_text_en)),
                            "doctrin_status"    => $row->doctrin_status,
                            "doctrin_satker"    => $row->doctrin_satker,
                            "satker_id"         => $row->satker_id,
                            "created_at"        => $created_at,
                            "updated_at"        => $updated_at,
                            "last_user"         => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "doctrin_id"        => $row->doctrin_id,
                            "doctrin_text_in"   => (($row->doctrin_text_in == null? "":$row->doctrin_text_in)),
                            "doctrin_text_en"   => (($row->doctrin_text_en == null? "":$row->doctrin_text_en)),
                            "doctrin_status"    => $row->doctrin_status,
                            "doctrin_satker"    => $row->doctrin_satker,
                            "satker_id"         => $row->satker_id,
                            "created_at"        => $created_at,
                            "updated_at"        => $updated_at,
                            "last_user"         => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initAboutLogo($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "logo_id"       => $row->logo_id,
                            "logo_text_in"  => (($row->logo_text_in == null? "":$row->logo_text_in)),
                            "logo_text_en"  => (($row->logo_text_en == null? "":$row->logo_text_en)),
                            "logo_status"   => $row->logo_status,
                            "logo_satker"   => $row->logo_satker,
                            "satker_id"     => $row->satker_id,
                            "created_at"    => $created_at,
                            "updated_at"    => $updated_at,
                            "last_user"     => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "logo_id"       => $row->logo_id,
                            "logo_text_in"  => (($row->logo_text_in == null? "":$row->logo_text_in)),
                            "logo_text_en"  => (($row->logo_text_en == null? "":$row->logo_text_en)),
                            "logo_status"   => $row->logo_status,
                            "logo_satker"   => $row->logo_satker,
                            "satker_id"     => $row->satker_id,
                            "created_at"    => $created_at,
                            "updated_at"    => $updated_at,
                            "last_user"     => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initAboutiad($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "iad_id"       => $row->iad_id,
                            "iad_text_in"  => (($row->iad_text_in == null? "":$row->iad_text_in)),
                            "iad_text_en"  => (($row->iad_text_en == null? "":$row->iad_text_en)),
                            "iad_status"   => $row->iad_status,
                            "iad_satker"   => $row->iad_satker,
                            "satker_id"    => $row->satker_id,
                            "created_at"   => $created_at,
                            "updated_at"   => $updated_at,
                            "last_user"    => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "iad_id"       => $row->iad_id,
                            "iad_text_in"  => (($row->iad_text_in == null? "":$row->iad_text_in)),
                            "iad_text_en"  => (($row->iad_text_en == null? "":$row->iad_text_en)),
                            "iad_status"   => $row->iad_status,
                            "iad_satker"   => $row->iad_satker,
                            "satker_id"    => $row->satker_id,
                            "created_at"   => $created_at,
                            "updated_at"   => $updated_at,
                            "last_user"    => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initAboutIntro($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "intro_id"      => $row->intro_id,
                            "intro_text_in" => (($row->intro_text_in == null? "":$row->intro_text_in)),
                            "intro_text_en" => (($row->intro_text_en == null? "":$row->intro_text_en)),
                            "intro_status"  => $row->intro_status,
                            "intro_satker"  => $row->intro_satker,
                            "satker_id"     => $row->satker_id,
                            "created_at"    => $created_at,
                            "updated_at"    => $updated_at,
                            "last_user"     => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "intro_id"      => $row->intro_id,
                            "intro_text_in" => (($row->intro_text_in == null? "":$row->intro_text_in)),
                            "intro_text_en" => (($row->intro_text_en == null? "":$row->intro_text_en)),
                            "intro_status"  => $row->intro_status,
                            "intro_satker"  => $row->intro_satker,
                            "satker_id"     => $row->satker_id,
                            "created_at"    => $created_at,
                            "updated_at"    => $updated_at,
                            "last_user"     => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initAboutVision($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "vision_id"       => $row->vision_id,
                            "vision_text_in"  => (($row->vision_text_in == null? "":$row->vision_text_in)),
                            "vision_text_en"  => (($row->vision_text_en == null? "":$row->vision_text_en)),
                            "vision_status"   => $row->vision_status,
                            "vision_satker"   => $row->vision_satker,
                            "satker_id"       => $row->satker_id,
                            "created_at"      => $created_at,
                            "updated_at"      => $updated_at,
                            "last_user"       => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "vision_id"       => $row->vision_id,
                            "vision_text_in"  => (($row->vision_text_in == null? "":$row->vision_text_in)),
                            "vision_text_en"  => (($row->vision_text_en == null? "":$row->vision_text_en)),
                            "vision_status"   => $row->vision_status,
                            "vision_satker"   => $row->vision_satker,
                            "satker_id"       => $row->satker_id,
                            "created_at"      => $created_at,
                            "updated_at"      => $updated_at,
                            "last_user"       => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initAboutMision($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "mision_id"       => $row->mision_id,
                            "mision_text_in"  => (($row->mision_text_in == null? "":$row->mision_text_in)),
                            "mision_text_en"  => (($row->mision_text_en == null? "":$row->mision_text_en)),
                            "mision_status"   => $row->mision_status,
                            "mision_satker"   => $row->mision_satker,
                            "satker_id"       => $row->satker_id,
                            "created_at"      => $created_at,
                            "updated_at"      => $updated_at,
                            "last_user"       => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "mision_id"       => $row->mision_id,
                            "mision_text_in"  => (($row->mision_text_in == null? "":$row->mision_text_in)),
                            "mision_text_en"  => (($row->mision_text_en == null? "":$row->mision_text_en)),
                            "mision_status"   => $row->mision_status,
                            "mision_satker"   => $row->mision_satker,
                            "satker_id"       => $row->satker_id,
                            "created_at"      => $created_at,
                            "updated_at"      => $updated_at,
                            "last_user"       => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initAboutProgram($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "program_id"      => $row->program_id,
                            "program_text_in" => (($row->program_text_in == null? "":$row->program_text_in)),
                            "program_text_en" => (($row->program_text_en == null? "":$row->program_text_en)),
                            "program_status"  => $row->program_status,
                            "program_satker"  => $row->program_satker,
                            "satker_id"       => $row->satker_id,
                            "created_at"      => $created_at,
                            "updated_at"      => $updated_at,
                            "last_user"       => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "program_id"      => $row->program_id,
                            "program_text_in" => (($row->program_text_in == null? "":$row->program_text_in)),
                            "program_text_en" => (($row->program_text_en == null? "":$row->program_text_en)),
                            "program_status"  => $row->program_status,
                            "program_satker"  => $row->program_satker,
                            "satker_id"       => $row->satker_id,
                            "created_at"      => $created_at,
                            "updated_at"      => $updated_at,
                            "last_user"       => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initAboutCommand($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "command_id"       => $row->command_id,
                            "command_text_in"  => (($row->command_text_in == null? "":$row->command_text_in)),
                            "command_text_en"  => (($row->command_text_en == null? "":$row->command_text_en)),
                            "command_status"   => $row->command_status,
                            "command_satker"   => $row->command_satker,
                            "satker_id"        => $row->satker_id,
                            "created_at"       => $created_at,
                            "updated_at"       => $updated_at,
                            "last_user"        => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "command_id"       => $row->command_id,
                            "command_text_in"  => (($row->command_text_in == null? "":$row->command_text_in)),
                            "command_text_en"  => (($row->command_text_en == null? "":$row->command_text_en)),
                            "command_status"   => $row->command_status,
                            "command_satker"   => $row->command_satker,
                            "satker_id"        => $row->satker_id,
                            "created_at"       => $created_at,
                            "updated_at"       => $updated_at,
                            "last_user"        => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initIntegrityLegal($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "legal_id"       => $row->legal_id,
                            "legal_text_in"  => (($row->legal_text_in == null? "":$row->legal_text_in)),
                            "legal_text_en"  => (($row->legal_text_en == null? "":$row->legal_text_en)),
                            "legal_status"   => $row->legal_status,
                            "legal_satker"   => $row->legal_satker,
                            "satker_id"      => $row->satker_id,
                            "created_at"     => $created_at,
                            "updated_at"     => $updated_at,
                            "last_user"      => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "legal_id"       => $row->legal_id,
                            "legal_text_in"  => (($row->legal_text_in == null? "":$row->legal_text_in)),
                            "legal_text_en"  => (($row->legal_text_en == null? "":$row->legal_text_en)),
                            "legal_status"   => $row->legal_status,
                            "legal_satker"   => $row->legal_satker,
                            "satker_id"      => $row->satker_id,
                            "created_at"     => $created_at,
                            "updated_at"     => $updated_at,
                            "last_user"      => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initIntegrityAccountability($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "accountability_id"       => $row->accountability_id,
                            "accountability_text_in"  => (($row->accountability_text_in == null? "":$row->accountability_text_in)),
                            "accountability_text_en"  => (($row->accountability_text_en == null? "":$row->accountability_text_en)),
                            "accountability_status"   => $row->accountability_status,
                            "accountability_satker"   => $row->accountability_satker,
                            "satker_id"               => $row->satker_id,
                            "created_at"              => $created_at,
                            "updated_at"              => $updated_at,
                            "last_user"               => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "accountability_id"       => $row->accountability_id,
                            "accountability_text_in"  => (($row->accountability_text_in == null? "":$row->accountability_text_in)),
                            "accountability_text_en"  => (($row->accountability_text_en == null? "":$row->accountability_text_en)),
                            "accountability_status"   => $row->accountability_status,
                            "accountability_satker"   => $row->accountability_satker,
                            "satker_id"               => $row->satker_id,
                            "created_at"              => $created_at,
                            "updated_at"              => $updated_at,
                            "last_user"               => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initIntegrityArrangement($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "arrangement_id"       => $row->arrangement_id,
                            "arrangement_text_in"  => (($row->arrangement_text_in == null? "":$row->arrangement_text_in)),
                            "arrangement_text_en"  => (($row->arrangement_text_en == null? "":$row->arrangement_text_en)),
                            "arrangement_status"   => $row->arrangement_status,
                            "arrangement_satker"   => $row->arrangement_satker,
                            "satker_id"            => $row->satker_id,
                            "created_at"           => $created_at,
                            "updated_at"           => $updated_at,
                            "last_user"            => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "arrangement_id"       => $row->arrangement_id,
                            "arrangement_text_in"  => (($row->arrangement_text_in == null? "":$row->arrangement_text_in)),
                            "arrangement_text_en"  => (($row->arrangement_text_en == null? "":$row->arrangement_text_en)),
                            "arrangement_status"   => $row->arrangement_status,
                            "arrangement_satker"   => $row->arrangement_satker,
                            "satker_id"            => $row->satker_id,
                            "created_at"           => $created_at,
                            "updated_at"           => $updated_at,
                            "last_user"            => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initIntegrityInnovation($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "innovation_id"      => $row->innovation_id,
                            "innovation_text_in" => (($row->innovation_text_in == null? "":$row->innovation_text_in)),
                            "innovation_text_en" => (($row->innovation_text_en == null? "":$row->innovation_text_en)),
                            "innovation_status"  => $row->innovation_status,
                            "innovation_satker"  => $row->innovation_satker,
                            "satker_id"          => $row->satker_id,
                            "created_at"         => $created_at,
                            "updated_at"         => $updated_at,
                            "last_user"          => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "innovation_id"      => $row->innovation_id,
                            "innovation_text_in" => (($row->innovation_text_in == null? "":$row->innovation_text_in)),
                            "innovation_text_en" => (($row->innovation_text_en == null? "":$row->innovation_text_en)),
                            "innovation_status"  => $row->innovation_status,
                            "innovation_satker"  => $row->innovation_satker,
                            "satker_id"          => $row->satker_id,
                            "created_at"         => $created_at,
                            "updated_at"         => $updated_at,
                            "last_user"          => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initIntegrityMechanism($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "mechanism_id"       => $row->mechanism_id,
                            "mechanism_text_in"  => (($row->mechanism_text_in == null? "":$row->mechanism_text_in)),
                            "mechanism_text_en"  => (($row->mechanism_text_en == null? "":$row->mechanism_text_en)),
                            "mechanism_status"   => $row->mechanism_status,
                            "mechanism_satker"   => $row->mechanism_satker,
                            "satker_id"          => $row->satker_id,
                            "created_at"         => $created_at,
                            "updated_at"         => $updated_at,
                            "last_user"          => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "mechanism_id"       => $row->mechanism_id,
                            "mechanism_text_in"  => (($row->mechanism_text_in == null? "":$row->mechanism_text_in)),
                            "mechanism_text_en"  => (($row->mechanism_text_en == null? "":$row->mechanism_text_en)),
                            "mechanism_status"   => $row->mechanism_status,
                            "mechanism_satker"   => $row->mechanism_satker,
                            "satker_id"          => $row->satker_id,
                            "created_at"         => $created_at,
                            "updated_at"         => $updated_at,
                            "last_user"          => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initIntegrityProfessionalism($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "professionalism_id"       => $row->professionalism_id,
                            "professionalism_text_in"  => (($row->professionalism_text_in == null? "":$row->professionalism_text_in)),
                            "professionalism_text_en"  => (($row->professionalism_text_en == null? "":$row->professionalism_text_en)),
                            "professionalism_status"   => $row->professionalism_status,
                            "professionalism_satker"   => $row->professionalism_satker,
                            "satker_id"                => $row->satker_id,
                            "created_at"               => $created_at,
                            "updated_at"               => $updated_at,
                            "last_user"                => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "professionalism_id"       => $row->professionalism_id,
                            "professionalism_text_in"  => (($row->professionalism_text_in == null? "":$row->professionalism_text_in)),
                            "professionalism_text_en"  => (($row->professionalism_text_en == null? "":$row->professionalism_text_en)),
                            "professionalism_status"   => $row->professionalism_status,
                            "professionalism_satker"   => $row->professionalism_satker,
                            "satker_id"                => $row->satker_id,
                            "created_at"               => $created_at,
                            "updated_at"               => $updated_at,
                            "last_user"                => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initIntegritySupervision($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "supervision_id"       => $row->supervision_id,
                            "supervision_text_in"  => (($row->supervision_text_in == null? "":$row->supervision_text_in)),
                            "supervision_text_en"  => (($row->supervision_text_en == null? "":$row->supervision_text_en)),
                            "supervision_status"   => $row->supervision_status,
                            "supervision_satker"   => $row->supervision_satker,
                            "satker_id"            => $row->satker_id,
                            "created_at"           => $created_at,
                            "updated_at"           => $updated_at,
                            "last_user"            => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "supervision_id"       => $row->supervision_id,
                            "supervision_text_in"  => (($row->supervision_text_in == null? "":$row->supervision_text_in)),
                            "supervision_text_en"  => (($row->supervision_text_en == null? "":$row->supervision_text_en)),
                            "supervision_status"   => $row->supervision_status,
                            "supervision_satker"   => $row->supervision_satker,
                            "satker_id"            => $row->satker_id,
                            "created_at"           => $created_at,
                            "updated_at"           => $updated_at,
                            "last_user"            => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initContactRelated($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "related_id"         => $row->related_id,
                            "related_name"       => (($row->related_name == null? "":$row->related_name)),
                            "related_link"       => (($row->related_link == null? "":$row->related_link)),
                            "related_size"       => (($row->related_size == null? 0:$row->related_size)),
                            "related_image"      => (($row->related_image == null? "":$row->related_image)),
                            "related_path"       => (($row->related_path == null? Init::defaultImage():$row->related_path)),
                            "related_status"     => $row->related_status,
                            "related_satker"     => $row->related_satker,
                            "satker_id"          => $row->satker_id,
                            "created_at"         => $created_at,
                            "updated_at"         => $updated_at,
                            "last_user"          => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "related_id"         => $row->related_id,
                            "related_name"       => (($row->related_name == null? "":$row->related_name)),
                            "related_link"       => (($row->related_link == null? "":$row->related_link)),
                            "related_size"       => (($row->related_size == null? 0:$row->related_size)),
                            "related_image"      => (($row->related_image == null? "":$row->related_image)),
                            "related_path"       => (($row->related_path == null? Init::defaultImage():$row->related_path)),
                            "related_status"     => $row->related_status,
                            "related_satker"     => $row->related_satker,
                            "satker_id"          => $row->satker_id,
                            "created_at"         => $created_at,
                            "updated_at"         => $updated_at,
                            "last_user"          => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initConferenceNews($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $date = Carbon::createFromFormat('Y-m-d', $row->news_date)
                        ->format('d-m-Y');

                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "news_id"             => $row->news_id,
                            "news_date"           => $date,
                            "news_title"          => (($row->news_title == null? "":$row->news_title)),
                            "news_category"       => (($row->news_category == null? "":$row->news_category)),
                            "news_text_in"        => (($row->news_text_in == null? "":$row->news_text_in)),
                            "news_text_en"        => (($row->news_text_en == null? "":$row->news_text_en)),
                            "news_broadcast"      => (($row->news_broadcast == null? 0:$row->news_broadcast)),
                            "news_size"           => (($row->news_size == null? 0:$row->news_size)),
                            "news_image"          => (($row->news_image == null? "":$row->news_image)),
                            "news_path"           => (($row->news_path == null? Init::defaultImage():$row->news_path)),
                            "news_link_instagram" => (($row->news_link_instagram == null? "":$row->news_link_instagram)),
                            "news_link_youtube"   => (($row->news_link_youtube == null? "":$row->news_link_youtube)),
                            "news_view"           => $row->news_view,
                            "news_status"         => $row->news_status,
                            "news_satker"         => $row->news_satker,
                            "satker_id"           => $row->satker_id,
                            "created_at"          => $created_at,
                            "updated_at"          => $updated_at,
                            "last_user"           => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "news_id"             => $row->news_id,
                            "news_date"           => $date,
                            "news_title"          => (($row->news_title == null? "":$row->news_title)),
                            "news_category"       => (($row->news_category == null? "":$row->news_category)),
                            "news_text_in"        => (($row->news_text_in == null? "":$row->news_text_in)),
                            "news_text_en"        => (($row->news_text_en == null? "":$row->news_text_en)),
                            "news_broadcast"      => (($row->news_broadcast == null? 0:$row->news_broadcast)),
                            "news_size"           => (($row->news_size == null? 0:$row->news_size)),
                            "news_image"          => (($row->news_image == null? "":$row->news_image)),
                            "news_path"           => (($row->news_path == null? Init::defaultImage():$row->news_path)),
                            "news_link_instagram" => (($row->news_link_instagram == null? "":$row->news_link_instagram)),
                            "news_link_youtube"   => (($row->news_link_youtube == null? "":$row->news_link_youtube)),
                            "news_view"           => $row->news_view,
                            "news_status"         => $row->news_status,
                            "news_satker"         => $row->news_satker,
                            "satker_id"           => $row->satker_id,
                            "created_at"          => $created_at,
                            "updated_at"          => $updated_at,
                            "last_user"           => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initConferenceAnnouncement($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');

                    if($flag == 1) {
                        $arr[] = array(
                            "announcement_id"         => $row->announcement_id,
                            "announcement_title"      => (($row->announcement_title == null? "":$row->announcement_title)),
                            "announcement_text_in"    => (($row->announcement_text_in == null? "":$row->announcement_text_in)),
                            "announcement_text_en"    => (($row->announcement_text_en == null? "":$row->announcement_text_en)),
                            "announcement_size"       => (($row->announcement_size == null? 0:$row->announcement_size)),
                            "announcement_image"      => (($row->announcement_image == null? "":$row->announcement_image)),
                            "announcement_path"       => (($row->announcement_path == null? Init::defaultImage():$row->announcement_path)),
                            "announcement_view"       => $row->announcement_view,
                            "announcement_status"     => $row->announcement_status,
                            "announcement_satker"     => $row->announcement_satker,
                            "satker_id"               => $row->satker_id,
                            "created_at"              => $created_at,
                            "updated_at"              => $updated_at,
                            "last_user"               => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "announcement_id"         => $row->announcement_id,
                            "announcement_title"      => (($row->announcement_title == null? "":$row->announcement_title)),
                            "announcement_text_in"    => (($row->announcement_text_in == null? "":$row->announcement_text_in)),
                            "announcement_text_en"    => (($row->announcement_text_en == null? "":$row->announcement_text_en)),
                            "announcement_size"       => (($row->announcement_size == null? 0:$row->announcement_size)),
                            "announcement_image"      => (($row->announcement_image == null? "":$row->announcement_image)),
                            "announcement_path"       => (($row->announcement_path == null? Init::defaultImage():$row->announcement_path)),
                            "announcement_view"       => $row->announcement_view,
                            "announcement_status"     => $row->announcement_status,
                            "announcement_satker"     => $row->announcement_satker,
                            "satker_id"               => $row->satker_id,
                            "created_at"              => $created_at,
                            "updated_at"              => $updated_at,
                            "last_user"               => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initConferenceEvent($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "event_id"         => $row->event_id,
                            "event_title"      => (($row->event_title == null? "":$row->event_title)),
                            "event_date"       => (($row->event_date == null? "":$row->event_date)),
                            "event_text_in"    => (($row->event_text_in == null? "":$row->event_text_in)),
                            "event_text_en"    => (($row->event_text_en == null? "":$row->event_text_en)),
                            "event_size"       => (($row->event_size == null? 0:$row->event_size)),
                            "event_image"      => (($row->event_image == null? "":$row->event_image)),
                            "event_path"       => (($row->event_path == null? Init::defaultImage():$row->event_path)),
                            "event_view"       => $row->event_view,
                            "event_status"     => $row->event_status,
                            "event_satker"     => $row->event_satker,
                            "satker_id"        => $row->satker_id,
                            "created_at"       => $created_at,
                            "updated_at"       => $updated_at,
                            "last_user"        => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "event_id"         => $row->event_id,
                            "event_title"      => (($row->event_title == null? "":$row->event_title)),
                            "event_date"       => (($row->event_date == null? "":$row->event_date)),
                            "event_text_in"    => (($row->event_text_in == null? "":$row->event_text_in)),
                            "event_text_en"    => (($row->event_text_en == null? "":$row->event_text_en)),
                            "event_size"       => (($row->event_size == null? 0:$row->event_size)),
                            "event_image"      => (($row->event_image == null? "":$row->event_image)),
                            "event_path"       => (($row->event_path == null? Init::defaultImage():$row->event_path)),
                            "event_view"       => $row->event_view,
                            "event_status"     => $row->event_status,
                            "event_satker"     => $row->event_satker,
                            "satker_id"        => $row->satker_id,
                            "created_at"       => $created_at,
                            "updated_at"       => $updated_at,
                            "last_user"        => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initInformationUnit($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "unit_id"       => $row->unit_id,
                            "unit_title"    => (($row->unit_title == null? "":$row->unit_title)),
                            "unit_text_in"  => (($row->unit_text_in == null? "":$row->unit_text_in)),
                            "unit_text_en"  => (($row->unit_text_en == null? "":$row->unit_text_en)),
                            "unit_status"   => $row->unit_status,
                            "unit_satker"   => $row->unit_satker,
                            "satker_id"     => $row->satker_id,
                            "created_at"    => $created_at,
                            "updated_at"    => $updated_at,
                            "last_user"     => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "unit_id"       => $row->unit_id,
                            "unit_title"    => (($row->unit_title == null? "":$row->unit_title)),
                            "unit_text_in"  => (($row->unit_text_in == null? "":$row->unit_text_in)),
                            "unit_text_en"  => (($row->unit_text_en == null? "":$row->unit_text_en)),
                            "unit_status"   => $row->unit_status,
                            "unit_satker"   => $row->unit_satker,
                            "satker_id"     => $row->satker_id,
                            "created_at"    => $created_at,
                            "updated_at"    => $updated_at,
                            "last_user"     => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initInformationService($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "service_id"            => $row->service_id,
                            "service_title"         => (($row->service_title == null? "":$row->service_title)),
                            "service_link"          => (($row->service_link == null? "":$row->service_link)),
                            "service_size"          => (($row->service_size == null)? 0:$row->service_size),
                            "service_image"         => (($row->service_image == null? "":$row->service_image)),
                            "service_path"          => (($row->service_path == null? Init::defaultImage():$row->service_path)),
                            "service_information"   => (($row->service_information == null? "":$row->service_information)),
                            "service_status"        => $row->service_status,
                            "service_satker"        => $row->service_satker,
                            "satker_id"             => $row->satker_id,
                            "created_at"            => $created_at,
                            "updated_at"            => $updated_at,
                            "last_user"             => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "service_id"            => $row->service_id,
                            "service_title"         => (($row->service_title == null? "":$row->service_title)),
                            "service_link"          => (($row->service_link == null? "":$row->service_link)),
                            "service_size"          => (($row->service_size == null)? 0:$row->service_size),
                            "service_image"         => (($row->service_image == null? "":$row->service_image)),
                            "service_path"          => (($row->service_path == null? Init::defaultImage():$row->service_path)),
                            "service_information"   => (($row->service_information == null? "":$row->service_information)),
                            "service_status"        => $row->service_status,
                            "service_satker"        => $row->service_satker,
                            "satker_id"             => $row->satker_id,
                            "created_at"            => $created_at,
                            "updated_at"            => $updated_at,
                            "last_user"             => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initInformationDpo($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "dpo_id"            => $row->dpo_id,
                            "dpo_name"          => (($row->dpo_name == null? "":$row->dpo_name)),
                            "dpo_size"          => (($row->dpo_size == null)? 0:$row->dpo_size),
                            "dpo_image"         => (($row->dpo_image == null? "":$row->dpo_image)),
                            "dpo_path"          => (($row->dpo_path == null? Init::defaultImage():$row->dpo_path)),
                            "dpo_information"   => (($row->dpo_information == null? "":$row->dpo_information)),
                            "dpo_status"        => $row->dpo_status,
                            "dpo_satker"        => $row->dpo_satker,
                            "satker_id"         => $row->satker_id,
                            "created_at"        => $created_at,
                            "updated_at"        => $updated_at,
                            "last_user"         => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "dpo_id"            => $row->dpo_id,
                            "dpo_name"          => (($row->dpo_name == null? "":$row->dpo_name)),
                            "dpo_size"          => (($row->dpo_size == null)? 0:$row->dpo_size),
                            "dpo_image"         => (($row->dpo_image == null? "":$row->dpo_image)),
                            "dpo_path"          => (($row->dpo_path == null? Init::defaultImage():$row->dpo_path)),
                            "dpo_information"   => (($row->dpo_information == null? "":$row->dpo_information)),
                            "dpo_status"        => $row->dpo_status,
                            "dpo_satker"        => $row->dpo_satker,
                            "satker_id"         => $row->satker_id,
                            "created_at"        => $created_at,
                            "updated_at"        => $updated_at,
                            "last_user"         => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }
        
        public static function initInformationStructural($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "structural_id"            => $row->structural_id,
                            "structural_position"      => (($row->structural_position == null? "1":$row->structural_position)),
                            "structural_name"          => (($row->structural_name == null? "":$row->structural_name)),
                            "structural_nip"           => (($row->structural_nip == null? "":$row->structural_nip)),
                            "structural_title"         => (($row->structural_title == null? "":$row->structural_title)),
                            "structural_size"          => (($row->structural_size == null)? 0:$row->structural_size),
                            "structural_image"         => (($row->structural_image == null? "":$row->structural_image)),
                            "structural_path"          => (($row->structural_path == null? Init::defaultImage():$row->structural_path)),
                            "structural_information"   => (($row->structural_information == null? "":$row->structural_information)),
                            "structural_status"        => $row->structural_status,
                            "structural_satker"        => $row->structural_satker,
                            "satker_id"                => $row->satker_id,
                            "created_at"               => $created_at,
                            "updated_at"               => $updated_at,
                            "last_user"                => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "structural_id"            => $row->structural_id,
                            "structural_position"      => (($row->structural_position == null? "1":$row->structural_position)),
                            "structural_name"          => (($row->structural_name == null? "":$row->structural_name)),
                            "structural_nip"           => (($row->structural_nip == null? "":$row->structural_nip)),
                            "structural_title"         => (($row->structural_title == null? "":$row->structural_title)),
                            "structural_size"          => (($row->structural_size == null)? 0:$row->structural_size),
                            "structural_image"         => (($row->structural_image == null? "":$row->structural_image)),
                            "structural_path"          => (($row->structural_path == null? Init::defaultImage():$row->structural_path)),
                            "structural_information"   => (($row->structural_information == null? "":$row->structural_information)),
                            "structural_status"        => $row->structural_status,
                            "structural_satker"        => $row->structural_satker,
                            "satker_id"                => $row->satker_id,
                            "created_at"               => $created_at,
                            "updated_at"               => $updated_at,
                            "last_user"                => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initInformationInfrastructure($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "infrastructure_id"            => $row->infrastructure_id,
                            "infrastructure_name"          => (($row->infrastructure_name == null? "":$row->infrastructure_name)),
                            "infrastructure_size"          => (($row->infrastructure_size == null)? 0:$row->infrastructure_size),
                            "infrastructure_image"         => (($row->infrastructure_image == null? "":$row->infrastructure_image)),
                            "infrastructure_path"          => (($row->infrastructure_path == null? Init::defaultImage():$row->infrastructure_path)),
                            "infrastructure_information"   => (($row->infrastructure_information == null? "":$row->infrastructure_information)),
                            "infrastructure_status"        => $row->infrastructure_status,
                            "infrastructure_satker"        => $row->infrastructure_satker,
                            "satker_id"                    => $row->satker_id,
                            "created_at"                   => $created_at,
                            "updated_at"                   => $updated_at,
                            "last_user"                    => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "infrastructure_id"            => $row->infrastructure_id,
                            "infrastructure_name"          => (($row->infrastructure_name == null? "":$row->infrastructure_name)),
                            "infrastructure_size"          => (($row->infrastructure_size == null)? 0:$row->infrastructure_size),
                            "infrastructure_image"         => (($row->infrastructure_image == null? "":$row->infrastructure_image)),
                            "infrastructure_path"          => (($row->infrastructure_path == null? Init::defaultImage():$row->infrastructure_path)),
                            "infrastructure_information"   => (($row->infrastructure_information == null? "":$row->infrastructure_information)),
                            "infrastructure_status"        => $row->infrastructure_status,
                            "infrastructure_satker"        => $row->infrastructure_satker,
                            "satker_id"                    => $row->satker_id,
                            "created_at"                   => $created_at,
                            "updated_at"                   => $updated_at,
                            "last_user"                    => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }


        public static function initArchivePhoto($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "photo_id"           => $row->photo_id,
                            "photo_title"        => (($row->photo_title == null? "":$row->photo_title)),
                            "photo_size"         => (($row->photo_size == null? 0:$row->photo_size)),
                            "photo_image"        => (($row->photo_image == null? "":$row->photo_image)),
                            "photo_path"         => (($row->photo_path == null? Init::defaultImage():$row->photo_path)),
                            "photo_description"  => (($row->photo_description == null? "":$row->photo_description)),
                            "photo_status"       => $row->photo_status,
                            "photo_satker"       => $row->photo_satker,
                            "satker_id"          => $row->satker_id,
                            "created_at"         => $created_at,
                            "updated_at"         => $updated_at,
                            "last_user"          => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "photo_id"           => $row->photo_id,
                            "photo_title"        => (($row->photo_title == null? "":$row->photo_title)),
                            "photo_size"         => (($row->photo_size == null? 0:$row->photo_size)),
                            "photo_image"        => (($row->photo_image == null? "":$row->photo_image)),
                            "photo_path"         => (($row->photo_path == null? Init::defaultImage():$row->photo_path)),
                            "photo_description"  => (($row->photo_description == null? "":$row->photo_description)),
                            "photo_status"       => $row->photo_status,
                            "photo_satker"       => $row->photo_satker,
                            "satker_id"          => $row->satker_id,
                            "created_at"         => $created_at,
                            "updated_at"         => $updated_at,
                            "last_user"          => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }

        public static function initArchiveRegulation($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "regulation_id"           => $row->regulation_id,
                            "regulation_title"        => (($row->regulation_title == null? "":$row->regulation_title)),
                            "regulation_size"         => (($row->regulation_size == null? 0:$row->regulation_size)),
                            "regulation_file"         => (($row->regulation_file == null? "":$row->regulation_file)),
                            "regulation_path"         => (($row->regulation_path == null? "":$row->regulation_path)),
                            "regulation_description"  => (($row->regulation_description == null? "":$row->regulation_description)),
                            "regulation_status"       => $row->regulation_status,
                            "regulation_satker"       => $row->regulation_satker,
                            "satker_id"               => $row->satker_id,
                            "created_at"              => $created_at,
                            "updated_at"              => $updated_at,
                            "last_user"               => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "regulation_id"           => $row->regulation_id,
                            "regulation_title"        => (($row->regulation_title == null? "":$row->regulation_title)),
                            "regulation_size"         => (($row->regulation_size == null? 0:$row->regulation_size)),
                            "regulation_file"         => (($row->regulation_file == null? "":$row->regulation_file)),
                            "regulation_path"         => (($row->regulation_path == null? "":$row->regulation_path)),
                            "regulation_description"  => (($row->regulation_description == null? "":$row->regulation_description)),
                            "regulation_status"       => $row->regulation_status,
                            "regulation_satker"       => $row->regulation_satker,
                            "satker_id"               => $row->satker_id,
                            "created_at"              => $created_at,
                            "updated_at"              => $updated_at,
                            "last_user"               => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }
        
        public static function initArchiveMovie($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)
                        ->format('d-m-Y H:i:s');
                    $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $row->updated_at)
                        ->format('d-m-Y H:i:s');
    
                    if($flag == 1) {
                        $arr[] = array(
                            "movie_id"           => $row->movie_id,
                            "movie_title"        => (($row->movie_title == null? "":$row->movie_title)),
                            "movie_description"  => (($row->movie_description == null? "":$row->movie_description)),
                            "movie_link"         => (($row->movie_link == null? "":$row->movie_link)),
                            "movie_status"       => $row->movie_status,
                            "movie_satker"       => $row->movie_satker,
                            "satker_id"          => $row->satker_id,
                            "created_at"         => $created_at,
                            "updated_at"         => $updated_at,
                            "last_user"          => (($row->last_user == null? "":$row->last_user)),
                        );
                    }            
                    else {
                        $arr = array(
                            "movie_id"           => $row->movie_id,
                            "movie_title"        => (($row->movie_title == null? "":$row->movie_title)),
                            "movie_description"  => (($row->movie_description == null? "":$row->movie_description)),
                            "movie_link"         => (($row->movie_link == null? "":$row->movie_link)),
                            "movie_status"       => $row->movie_status,
                            "movie_satker"       => $row->movie_satker,
                            "satker_id"          => $row->satker_id,
                            "created_at"         => $created_at,
                            "updated_at"         => $updated_at,
                            "last_user"          => (($row->last_user == null? "":$row->last_user)),
                        );
                    }        
                }
            }

            return $arr;
        }


        public static function initChatResponse($flag, $data, $type=0, $user=0) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $last_edited = Carbon::createFromFormat('Y-m-d H:i:s', $row->last_edited)
                        ->format('d-m-Y H:i:s');
                    
                    if($type == 1) {
                        $unread = Dbase::dbGetCountByTripleId('tr_message', 'chat_from', $user, 'chat_to', $row->chat_to, 'read_from', 0);
                    }
                    else if($type == 2) {
                        $unread = Dbase::dbGetCountByTripleId('tr_message', 'chat_from', $row->chat_from, 'chat_to', $user, 'read_to', 0);
                    }
                    else {
                        $unread = 0;
                    }
                    
                    $chat_from = array();
                    $user_from = DB::table('tm_user')->where('user_id', $row->chat_from)->get();
                    $from = Init::initUser(1, $user_from);
                    foreach($from as $rows) {
                        $chat_from = array(
                            'user_id'       => $rows['user_id'],
                            'user_fullname' => $rows['user_fullname'],
                            'user_path'     => $rows['user_path'],
                        );
                    }

                    $chat_to = array();
                    $user_to = DB::table('tm_user')->where('user_id', $row->chat_to)->get();
                    $to = Init::initUser(1, $user_to);
                    foreach($to as $rows) {
                        $chat_to = array(
                            'user_id'       => $rows['user_id'],
                            'user_fullname' => $rows['user_fullname'],
                            'user_path'     => $rows['user_path'],
                        );
                    }

                    if($flag == 1) {     
                        $arr[] = array(
                            "chat_id"       => $row->chat_id,
                            "last_edited"   => $last_edited,
                            "user_from"     => $chat_from,
                            "user_to"       => $chat_to,
                            "unread"        => $unread 
                        );     
                    }
                    else {
                        $arr = array(
                            "chat_id"       => $row->chat_id,
                            "last_edited"   => $last_edited,
                            "chat_from"     => $chat_from,
                            "chat_to"       => $chat_to,
                            "unread"        => $unread
                        );  
                    }
                }
            }
    
            return $arr;
        }

        public static function initMessageResponse($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $datetime = Carbon::createFromFormat('Y-m-d H:i:s', $row->message_datetime)
                        ->format('d-m-Y H:i:s');
                    
                    $chat_from = array();
                    $user_from = DB::table('tm_user')->where('user_id', $row->chat_from)->get();
                    $from = Init::initUser(1, $user_from);
                    foreach($from as $rows) {
                        $chat_from = array(
                            'user_id'       => $rows['user_id'],
                            'user_fullname' => $rows['user_fullname'],
                            'user_path'     => $rows['user_path'],
                        );
                    }

                    $chat_to = array();
                    $user_to = DB::table('tm_user')->where('user_id', $row->chat_to)->get();
                    $to = Init::initUser(1, $user_to);
                    foreach($to as $rows) {
                        $chat_to = array(
                            'user_id'       => $rows['user_id'],
                            'user_fullname' => $rows['user_fullname'],
                            'user_path'     => $rows['user_path'],
                        );
                    }

                    if($flag == 1) {     
                        $arr[] = array(
                            "message_id"        => $row->message_id,
                            "message_type"      => $row->message_type,
                            "message_datetime"  => $datetime,
                            "message_text"      => (($row->message_text == null? "":$row->message_text)),
                            "read_from"         => $row->read_from,
                            "read_to"           => $row->read_to,
                            "chat_id"           => $row->chat_id,
                            "chat_from"         => $chat_from,
                            "chat_to"           => $chat_to,
                        );     
                    }
                    else {
                        $arr = array(
                            "message_id"        => $row->message_id,
                            "message_type"      => $row->message_type,
                            "message_datetime"  => $datetime,
                            "message_text"      => (($row->message_text == null? "":$row->message_text)),
                            "read_from"         => $row->read_from,
                            "read_to"           => $row->read_to,
                            "chat_id"           => $row->chat_id,
                            "chat_from"         => $chat_from,
                            "chat_to"           => $chat_to,
                        );  
                    }
                }
            }
    
            return $arr;
        }

        public static function initSearchContent($flag, $data) {
            $arr = array();
            if(!empty($data)) {
                foreach ($data as $row) {
                    $date = Carbon::createFromFormat('Y-m-d', $row->content_date)
                        ->format('d-m-Y');
                    $time = Carbon::createFromFormat('H:i:s', $row->content_time)
                        ->format('H:i:s');

                    if($flag == 1) {     
                        $arr[] = array(
                            "content_id"        => $row->content_id,
                            "content_date"      => $date,
                            "content_time"      => $time,
                            "content_text_in"   => $row->content_text_in,
                            "content_text_en"   => $row->content_text_en,
                            "satker_id"         => $row->satker_id,
                            "satker_name"       => $row->satker_name,
                            "menu_id"           => $row->menu_id,
                            "menu_name"         => $row->menu_name,
                            "menu_url"          => $row->menu_url,
                            "reff_id"           => $row->reff_id,
                        );     
                    }
                    else {
                        $arr = array(
                            "content_id"        => $row->content_id,
                            "content_date"      => $date,
                            "content_time"      => $time,
                            "content_text_in"   => $row->content_text_in,
                            "content_text_en"   => $row->content_text_en,
                            "satker_id"         => $row->satker_id,
                            "satker_name"       => $row->satker_name,
                            "menu_id"           => $row->menu_id,
                            "menu_name"         => $row->menu_name,
                            "menu_url"          => $row->menu_url,
                            "reff_id"           => $row->reff_id,
                        );  
                    }
                }
            }
    
            return $arr;
        }
    }
?>
