<?php
    namespace App\Helpers;

    use Exception;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\DB;

    class Dbase {

        public static function json($arr) {
            echo json_encode($arr); die();
        }    

        public static function dbGetFieldById($table, $column, $field, $id) {
            $db = DB::table($table)->where($field, $id)->first($column .' AS temp');
            if($db != null) {
                $str = $db->temp;
            }
            else {
                $str = "";
            }

            return $str;
        }

        public static function dbGetFieldByIdOrder($table, $column, $field, $id, $order) {
            $db = DB::table($table)->where($field, $id)->orderBy($order, 'DESC')->first($column .' AS temp');
            if($db != null) {
                $str = $db->temp;
            }
            else {
                $str = "";
            }

            return $str;
        }

        public static function dbSumFieldById($table, $column, $field, $id) {
            $db = DB::table($table)->where($field, $id)->sum($column);
            if($db != null) {
                return $db;
            }
            else {
                return 0;
            }
        }

        public static function dbSetFieldById($table, $column, $value, $field, $id) {
            $db = DB::table($table)->where($field, $id)->update([$column => $value]);
            if($db != null) {
                return $db;
            }
            else {
                return 0;
            }
        }

        public static function dbGetCount($table) {
            $db = DB::table($table)->count();
            if($db != null) {
                return $db;
            }
            else {
                return 0;
            }
        }

        public static function dbGetCountById($table, $field, $id) {
            $db = DB::table($table)->where($field, $id)->count();
            if($db != null) {
                return $db;
            }
            else {
                return 0;
            }
        }

        public static function dbGetCountByTwoId($table, $field, $id, $fields, $ids) {
            $db = DB::table($table)->where($field, $id)->where($fields, $ids)->count();
            if($db != null) {
                return $db;
            }
            else {
                return 0;
            }
        }

        public static function dbGetCountByTripleId($table, $field, $id, $fields, $ids, $fieldss, $idss) {
            $db = DB::table($table)->where($field, $id)->where($fields, $ids)->where($fieldss, $idss)->count();
            if($db != null) {
                return $db;
            }
            else {
                return 0;
            }
        }

        public static function dbGetFieldByTwoId($table, $column, $field, $id, $fields, $ids) {
            $db = DB::table($table)->where($field, $id)->where($fields, $ids)->first($column .' AS temp');
            if($db != null) {
                $str = $db->temp;
            }
            else {
                $str = "";
            }

            return $str;
        }

        public static function dbGetFieldByThreeId($table, $column, $field, $id, $fields, $ids, $fieldss, $idss) {
            $db = DB::table($table)->where($field, $id)->where($fields, $ids)->where($fieldss, $idss)->first($column .' AS temp');
            if($db != null) {
                $str = $db->temp;
            }
            else {
                $str = "";
            }

            return $str;
        }

        public static function setLogActivity($rst, $user, $now, $type, $description, $ip='') {
            if($rst != 0) {
                if($user != "") {
                    $date_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                        ->format('Y-m-d');
                    $time_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                        ->format('H:i:s');
    
                    $log = Dbase::dbGetFieldById('tm_user', 'user_log', 'user_id', $user);
                    if($log != '') {
                        $arr = json_decode($log); 
                        
                        $ip         = (($arr->ip == false)?"-":$arr->ip); 
                        $agent      = (($arr->agent == false)?"-":$arr->agent);
                        $platform   = (($arr->platform == false)?"-":$arr->platform); 
                        $device     = (($arr->device == false)?"-":$arr->device);
                        $browser    = (($arr->browser == false)?"-":$arr->browser);
                    } 
                    
                    $rst = DB::table('tr_activity')
                        ->insertGetId([
                            "activity_user"         => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $user),
                            "activity_type"         => (($type == null)? "":nl2br($type)),
                            "activity_description"  => (($description == null)? "":nl2br($description)),
                            "activity_date"         => $date_at,
                            "activity_time"         => $time_at,
                            "activity_ip"           => (($log == null)? "":$ip),
                            "activity_agent"        => (($log == null)? "":$agent),
                            "activity_platform"     => (($log == null)? "":$platform),
                            "activity_device"       => (($log == null)? "":$device),
                            "activity_browser"      => (($log == null)? "":$browser),
                            "user_id"               => $user,
                        ]);     
                } 
            } 
            
            return $rst;
        }

        public static function processContentActivity($rst, $now, $menu, $satker, $in, $en) {
            if($rst != 0) {
                $table = 'tr_content';
                $date_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                    ->format('Y-m-d');
                $time_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                    ->format('H:i:s');

                $rst = DB::table($table)
                    ->insertGetId([
                        "content_date"      => $date_at,
                        "content_time"      => $time_at,
                        "content_text_in"   => (($in == null)? "":strip_tags($in)),
                        "content_text_en"   => (($en == null)? "":strip_tags($en)),
                        "satker_id"         => $satker,
                        "menu_id"           => $menu,
                        "reff_id"           => $rst,
                    ]);  
            } 
            
            return $rst;
        }

        public static function changeContentActivity($rst, $now, $menu, $satker, $reff, $in, $en) {
            if($rst != 0) {
                $table = 'tr_content';
                $date_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                    ->format('Y-m-d');
                $time_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                    ->format('H:i:s');

                $content_id = dBase::dbGetFieldByThreeId($table, 'content_id', 'satker_id', $satker, 'menu_id', $menu, 'reff_id', $reff);
                $rst = DB::table($table)
                    ->where('content_id', $content_id)
                    ->update([
                        "content_date"      => $date_at,
                        "content_time"      => $time_at,
                        "content_text_in"   => (($in == null)? "":strip_tags($in)),
                        "content_text_en"   => (($en == null)? "":strip_tags($en)),
                    ]); 
            } 
            
            return $rst;
        }

        public static function removeContentActivity($rst, $menu, $satker, $reff) {
            if($rst != 0) {
                $table = 'tr_content';
                $content_id = dBase::dbGetFieldByThreeId($table, 'content_id', 'satker_id', $satker, 'menu_id', $menu, 'reff_id', $reff);
                if($content_id != "") {
                    $rst = DB::table($table)->where('content_id', $content_id)->delete(); 
                }     
            } 
            
            return $rst;
        }

        public static function processUploadActivity($rst, $now, $menu, $satker, $type, $name, $size, $file, $path) {
            if($rst != 0) {
                $table = 'tr_upload';
                $date_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                    ->format('Y-m-d');
                $time_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                    ->format('H:i:s');

                if($file != "") {
                    $rst = DB::table($table)
                        ->insertGetId([
                            "upload_date"   => $date_at,
                            "upload_time"   => $time_at,
                            "upload_type"   => $type,
                            "upload_name"   => (($name == null)? :$name),
                            "upload_size"   => (($size == null)? 0:$size),
                            "upload_file"   => (($file == null)? "":$file),
                            "upload_path"   => (($file == null)? "":$path),
                            "satker_id"     => $satker,
                            "menu_id"       => $menu,
                            "reff_id"       => $rst,
                        ]);  
                }
                else {
                    $rst = 0;
                }
            } 
            
            return $rst;
        }

        public static function changeUploadActivity($rst, $now, $menu, $satker, $reff, $name, $size, $file, $path) {
            if($rst != 0) {
                $table = 'tr_upload';
                $date_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                    ->format('Y-m-d');
                $time_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                    ->format('H:i:s');

                $upload_id = dBase::dbGetFieldByThreeId($table, 'upload_id', 'satker_id', $satker, 'menu_id', $menu, 'reff_id', $reff);
                if($file != "") {
                    $rst = DB::table($table)
                        ->where('upload_id', $upload_id)
                        ->update([
                            "upload_date"   => $date_at,
                            "upload_time"   => $time_at,
                            "upload_name"   => (($name == null)? :$name),
                            "upload_size"   => (($size == null)? 0:$size),
                            "upload_file"   => (($file == null)? "":$file),
                            "upload_path"   => (($file == null)? "":$path),
                        ]); 
                }
                else {
                    $rst = 0;
                }  
            } 
            
            return $rst;
        }

        public static function removeUploadActivity($rst, $menu, $satker, $reff) {
            if($rst != 0) {
                $table = 'tr_upload';
                $upload_id = dBase::dbGetFieldByThreeId($table, 'upload_id', 'satker_id', $satker, 'menu_id', $menu, 'reff_id', $reff);
                if($upload_id != "") {
                    $rst = DB::table($table)->where('upload_id', $upload_id)->delete(); 
                }     
            } 
            
            return $rst;
        }

        public static function setLogNotification($rst, $satker_id, $user, $now, $type, $description) {
            if($rst != 0) {
                $date_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                    ->format('Y-m-d');
                $time_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                    ->format('H:i:s');
                    
                $title = $type .' Data';
                $user_satker = dBase::dbGetFieldById('tm_user', 'user_id', 'satker_id', $satker_id);
                if($user_satker != "") {
                    DB::table('tp_notification')
                        ->insert([
                            "is_published"              => 0,
                            "notification_date"         => $date_at,
                            "notification_time"         => $time_at,
                            "notification_title"        => $title,
                            "notification_description"  => (($description == null)? "":nl2br($description)),
                            "notification_user"         => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $user_satker),
                            "user_id"                   => $user_satker,
                            "last_user"                 => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $user),
                        ]);   

                    $token = Dbase::dbGetFieldById('tm_user', 'user_token', 'user_id', $user_satker);
                    if($token != "") {
                        $fcm = dBase::sendCloudMessaging($token, $title, (($description == null)? "":strip_tags($description)));  
                    }
                }
            } 
            
            return $rst;
        }

        public static function processVisitor($ip, $satker, $menu) {
            if($ip != "") {
                $now = Carbon::now();
                $date_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                        ->format('Y-m-d');
                $time_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                        ->format('H:i:s');
    
                $table = 'tr_visitor';
                $rst = DB::table($table)
                    ->insertGetId([
                        "visitor_date"  => $date_at,
                        "visitor_time"  => $time_at,
                        "visitor_ip"    => $ip,
                        "satker_id"     => $satker,
                        "menu_id"       => $menu,
                    ]);  
            } 
            else {
                $rst = 0;
            }
            
            return $rst;
        }

        public static function processView($table, $key, $field, $id) {
            $old = Dbase::dbGetFieldById($table, $key, $field, $id);
            $new = $old + 1;
            
            $rst = DB::table($table)
                ->where($field, $id)
                ->update([$key => $new]
            );
            
            return $rst;
        }

        public static function sendCloudMessaging($token, $title, $body) {
            $curl = curl_init();

            curl_setopt_array(
                $curl, array(
                    CURLOPT_URL => 'https://fcm.googleapis.com/fcm/send',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS =>'{
                    "to" : "'. $token .'",
                    "notification": {
                            "title": "'. $title .'",
                            "body": "'. $body .'"
                        }
                    }',
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: key=AAAArtzqaaI:APA91bGBl9baNkoivkCVRHHBBlDzJLRlhuR06VqWSd30F62KxGl8Hvnz8iXj8kcBlceB5M3tg25S4W4lVAr6qmYyNwIZwnT9XzjbY2ffEYfXa25dUnnq20nYcJm9lKOTUMHSc4dZXyjZ',
                        'content-type: application/json'
                    ),
                )
            );

            $response = curl_exec($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            return $httpcode;
        }

        public static function memberSatker($id, $flag=0) {
            $arr = array();
            $satker_type = dBase::dbGetFieldById('tm_satker', 'satker_type', 'satker_id', $id);
            if($satker_type == 0) {
                $arrKejagung = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '0' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                foreach ($arrKejagung as $rKejagung) {
                    if($flag == 0) {
                        $arr[] = $rKejagung->satker_id;   
                    }
                    else {
                        $arr[] = array(
                            'satker_id'   => $rKejagung->satker_id,
                            'satker_name' => $rKejagung->satker_name,
                        );   
                    }
                    
                    $arrKejati = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '1' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                    foreach ($arrKejati as $rKejati) {
                        if($flag == 0) {
                            $arr[] = $rKejati->satker_id;  
                        }
                        else {
                            $arr[] = array(
                                'satker_id'   => $rKejati->satker_id,
                                'satker_name' => $rKejati->satker_name,
                            );   
                        }
                        
                        $tempCode  = $rKejati->satker_code;
                        $arrKejari = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '2' AND `satker_code` LIKE '". $tempCode ."%' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                        foreach ($arrKejari as $rKejari) {
                            if($flag == 0) {
                                $arr[] = $rKejari->satker_id;
                            }
                            else {
                                $arr[] = array(
                                    'satker_id'   => $rKejari->satker_id,
                                    'satker_name' => $rKejari->satker_name,
                                );   
                            }

                            $tempCode  = $rKejari->satker_code;
                            $arrCabjari = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '3' AND `satker_code` LIKE '". $tempCode ."%' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                        
                            foreach ($arrCabjari as $rCabjari) {
                                if($flag == 0) {
                                    $arr[] = $rCabjari->satker_id;  
                                }
                                else {
                                    $arr[] = array(
                                        'satker_id'   => $rCabjari->satker_id,
                                        'satker_name' => $rCabjari->satker_name,
                                    );   
                                } 
                            }
                        }
                    }
                }
                
                $arrBadiklat = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '4' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                foreach ($arrBadiklat as $rBadiklat) {
                    if($flag == 0) {
                        $arr[] = $rBadiklat->satker_id;  
                    }
                    else {
                        $arr[] = array(
                            'satker_id'   => $rBadiklat->satker_id,
                            'satker_name' => $rBadiklat->satker_name,
                        );   
                    }  
                }
            } else if($satker_type == 1) {
                $arrKejati = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '1' AND `satker_id` = '". $id ."' AND `satker_code` IS NOT NULL;");
                foreach ($arrKejati as $rKejati) {
                    if($flag == 0) {
                        $arr[] = $rKejati->satker_id;
                    }
                    else {
                        $arr[] = array(
                            'satker_id'   => $rKejati->satker_id,
                            'satker_name' => $rKejati->satker_name,
                        );   
                    }     
                    
                    $tempCode  = $rKejati->satker_code;
                    $arrKejari = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '2' AND `satker_code` LIKE '". $tempCode ."%' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                    foreach ($arrKejari as $rKejari) {
                        if($flag == 0) {
                            $arr[] = $rKejari->satker_id;  
                        }
                        else {
                            $arr[] = array(
                                'satker_id'   => $rKejari->satker_id,
                                'satker_name' => $rKejari->satker_name,
                            );   
                        }  

                        $tempCode  = $rKejari->satker_code;
                        $arrCabjari = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '3' AND `satker_code` LIKE '". $tempCode ."%' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                    
                        foreach ($arrCabjari as $rCabjari) {
                            if($flag == 0) {
                                $arr[] = $rCabjari->satker_id;   
                            }
                            else {
                                $arr[] = array(
                                    'satker_id'   => $rCabjari->satker_id,
                                    'satker_name' => $rCabjari->satker_name,
                                );   
                            } 
                        }
                    }
                }
            }
            else if($satker_type == 2) {
                $arrKejari = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '2' AND `satker_id` = '". $id ."%' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                foreach ($arrKejari as $rKejari) {
                    if($flag == 0) {
                        $arr[] = $rKejari->satker_id;     
                    }
                    else {
                        $arr[] = array(
                            'satker_id'   => $rKejari->satker_id,
                            'satker_name' => $rKejari->satker_name,
                        );   
                    }

                    $tempCode  = $rKejari->satker_code;
                    $arrCabjari = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '3' AND `satker_code` LIKE '". $tempCode ."%' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                
                    foreach ($arrCabjari as $rCabjari) {
                        if($flag == 0) {
                            $arr[] = $rCabjari->satker_id;    
                        }
                        else {
                            $arr[] = array(
                                'satker_id'   => $rCabjari->satker_id,
                                'satker_name' => $rCabjari->satker_name,
                            );   
                        }   
                    }
                }
            }
            else if($satker_type == 3) {
                $arrCabjari = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '3' AND `satker_id` = '". $id ."%' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
            
                foreach ($arrCabjari as $rCabjari) {
                    if($flag == 0) {
                        $arr[] = $rCabjari->satker_id;    
                    }
                    else {
                        $arr[] = array(
                            'satker_id'   => $rCabjari->satker_id,
                            'satker_name' => $rCabjari->satker_name,
                        );   
                    }   
                }
            } 
            else if($satker_type == 4) {
                $arrBadiklat = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '4' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                foreach ($arrBadiklat as $rBadiklat) {
                    if($flag == 0) {
                        $arr[] = $rBadiklat->satker_id;     
                    }
                    else {
                        $arr[] = array(
                            'satker_id'   => $rBadiklat->satker_id,
                            'satker_name' => $rBadiklat->satker_name,
                        );   
                    }
                }
            }
            else {
                $arrSatker = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_status` = '1'");
                foreach ($arrSatker as $rSatker) {
                    if($flag == 0) {
                        $arr[] = $rSatker->satker_id;     
                    }
                    else {
                        $arr[] = array(
                            'satker_id'   => $rSatker->satker_id,
                            'satker_name' => $rSatker->satker_name,
                        );   
                    }  
                }
            }

            if(empty($arr)) {
                if($flag == 0) {
                    $arr[] = intval($id);        
                }
                else {
                    $arr[] = array(
                        'satker_id'   => intval($id),
                        'satker_name' => Dbase::dbGetFieldById('tm_satker', 'satker_name', 'satker_id', $id),
                    );   
                } 
            }

            return $arr;
        }

        public static function parentSatker($id, $flag=0) {
            $arr = array();
            $satker_type = dBase::dbGetFieldById('tm_satker', 'satker_type', 'satker_id', $id);
            
            if($satker_type == 3) {
                $arrCabjari = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_id` = ". $id);
                foreach ($arrCabjari as $rCabjari) {
                    if($flag == 0) {
                        $arr[] = $rCabjari->satker_id;  
                    }
                    else {
                        $arr[] = array(
                            'satker_id'   => $rCabjari->satker_id,
                            'satker_name' => $rCabjari->satker_name,
                        );   
                    } 

                    $tempCode  = substr($rCabjari->satker_code, 0, 4);
                    $arrKejari = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '2' AND `satker_code` LIKE '". $tempCode ."%' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                    foreach ($arrKejari as $rKejari) {
                        if($flag == 0) {
                            $arr[] = $rKejari->satker_id;
                        }
                        else {
                            $arr[] = array(
                                'satker_id'   => $rKejari->satker_id,
                                'satker_name' => $rKejari->satker_name,
                            );   
                        }

                        $tempCode  = substr($rKejari->satker_code, 0, 2);
                        $arrKejati = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '1' AND `satker_code` LIKE '". $tempCode ."%' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                        foreach ($arrKejati as $rKejati) {
                            if($flag == 0) {
                                $arr[] = $rKejati->satker_id;  
                            }
                            else {
                                $arr[] = array(
                                    'satker_id'   => $rKejati->satker_id,
                                    'satker_name' => $rKejati->satker_name,
                                );   
                            }
                        }
                    }
                }
            } 
            else if($satker_type == 2) {
                $arrKejari = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_id` = ". $id);
                foreach ($arrKejari as $rKejari) {
                    if($flag == 0) {
                        $arr[] = $rKejari->satker_id;
                    }
                    else {
                        $arr[] = array(
                            'satker_id'   => $rKejari->satker_id,
                            'satker_name' => $rKejari->satker_name,
                        );   
                    }

                    $tempCode  = substr($rKejari->satker_code, 0, 2);
                    $arrKejati = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '1' AND `satker_code` LIKE '". $tempCode ."%' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                    foreach ($arrKejati as $rKejati) {
                        if($flag == 0) {
                            $arr[] = $rKejati->satker_id;  
                        }
                        else {
                            $arr[] = array(
                                'satker_id'   => $rKejati->satker_id,
                                'satker_name' => $rKejati->satker_name,
                            );   
                        }
                    }
                }
            }
            else if($satker_type == 1) {
                $arrKejati = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_id` = ". $id);
                foreach ($arrKejati as $rKejati) {
                    if($flag == 0) {
                        $arr[] = $rKejati->satker_id;  
                    }
                    else {
                        $arr[] = array(
                            'satker_id'   => $rKejati->satker_id,
                            'satker_name' => $rKejati->satker_name,
                        );   
                    }
                }
            }
            else if($satker_type == 4) {
                $arrBadiklat = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_id` = ". $id);
                foreach ($arrBadiklat as $rBadiklat) {
                    if($flag == 0) {
                        $arr[] = $rBadiklat->satker_id;     
                    }
                    else {
                        $arr[] = array(
                            'satker_id'   => $rBadiklat->satker_id,
                            'satker_name' => $rBadiklat->satker_name,
                        );   
                    }
                }
            }

            $arrKejagung = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '0' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
            foreach ($arrKejagung as $rKejagung) {
                if($flag == 0) {
                    $arr[] = $rKejagung->satker_id;   
                }
                else {
                    $arr[] = array(
                        'satker_id'   => $rKejagung->satker_id,
                        'satker_name' => $rKejagung->satker_name,
                    );   
                }
            }
            
            if(empty($arr)) {
                if($flag == 0) {
                    $arr[] = intval($id);        
                }
                else {
                    $arr[] = array(
                        'satker_id'   => intval($id),
                        'satker_name' => Dbase::dbGetFieldById('tm_satker', 'satker_name', 'satker_id', $id),
                    );   
                } 
            }

            return $arr;
        }

        public static function getParentNavigationHome() {
            $data = DB::table('tm_menu')->where('menu_id', 1)->get();
            return $data;
        }
    
        public static function getParentNavigationMenu() {
            $data = DB::table('tm_menu')->where('menu_status', 1)->where('menu_nav', 1)->orderBy('menu_id', 'ASC')->get();
            return $data;
        }
    
        public static function getChildNavigationMenu($parent) {
            $data = DB::table('tm_menu')->where('menu_status', 1)->where('menu_parent', $parent)->orderBy('menu_id', 'ASC')->get();
            return $data;
        }
    
        public static function getAccesssNavigationMenu($slug) {
            $arr = array();
            $arr[] = 1;
            $satker = Dbase::dbGetFieldById('tm_satker', 'satker_id', 'satker_slug', $slug);
            $data   = DB::table('tr_navigation')->where('satker_id', $satker)->orderBy('menu_id', 'ASC')->get();
            if($data != "[]") {
                foreach($data as $row) {
                    $arr[] = $row->menu_id;
                }
            }
            
            return $arr;
        }
    }
?>