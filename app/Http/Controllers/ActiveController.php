<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Init;
use App\Helpers\Dbase;
use App\Helpers\Status;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class ActiveController extends Controller
{
    public function getMenu(Request $request) {
        $table = "tm_menu";
        $data = DB::table($table)->where('menu_status', 1)->orderBy('menu_id')->get();
        
        $arr = Init::initMasterMenu(1, $data);
        return Init::initResponse($arr, 'View');
    }

    public function getRole(Request $request) {
        $table = "tp_role";
        $data = DB::table($table)->where('role_status', 1)->orderBy('role_name')->get();
        
        $arr = Init::initRoleUser(1, $data);
        return Init::initResponse($arr, 'View');
    }

    public function getSatker(Request $request) {
        $table = "tm_satker";
        $data = DB::table($table)->where('satker_status', 1)->orderBy('satker_code')->get();
        
        $arr = Init::initSatker(1, $data);
        return Init::initResponse($arr, 'View');
    }

    public function getTutorial(Request $request) {
        $table = "tm_tutorial";
        $data = DB::table($table)->where('tutorial_status', 1)->orderBy('tutorial_id')->get();
        
        $arr = Init::initMasterTutorial(1, $data);
        return Init::initResponse($arr, 'View');
    }

    public function getIntegration(Request $request) {
        $table = "tp_request";
        $data = DB::table($table)->where('request_status', 1)->orderBy('request_id')->get();
        
        $arr = Init::initMasterIntegration(1, $data);
        return Init::initResponse($arr, 'View');
    }

    public function getPattern(Request $request) {
        $table = "tm_pattern";
        $data = DB::table($table)->where('pattern_status', 1)->orderBy('pattern_id')->get();
        
        $arr = Init::initMasterPattern(1, $data);
        return Init::initResponse($arr, 'View');
    }

    public function getCover(Request $request) {
        $table = "tm_cover";
        $data = DB::table($table)->where('cover_status', 1)->orderBy('cover_id')->get();
        
        $arr = Init::initMasterCover(1, $data);
        return Init::initResponse($arr, 'View');
    }
    
    public function getUser(Request $request) {
        $table = "tm_user";
        if(($request->name != "") && ($request->type != "")) {
            $data = DB::table($table)->where('user_fullname', 'like', '%'.$request->name.'%')->where('user_type', $request->type)->where('user_status', 1)->orderBy('user_type')->get();
        }    
        else if(($request->name != "") && ($request->type == "")) {
            $data = DB::table($table)->where('user_fullname', 'like', '%'.$request->name.'%')->where('user_status', 1)->orderBy('user_type')->get();
        }    
        else if(($request->name == "") && ($request->type != "")) {
            $data = DB::table($table)->where('user_type', $request->type)->where('user_status', 1)->orderBy('user_type')->get();
        }    
        else {
            $data = DB::table($table)->where('user_status', 1)->orderBy('user_type')->get();
        }
        
        $arr = Init::initUser(1, $data);
        return Init::initResponse($arr, 'View');
    }

    public function getNotification(Request $request) {
        $table = "tp_notification";
        if($request->user_id != "") {
            $data = DB::table($table)->where('user_id', $request->user_id)->where('is_read', 0)->orderBy('notification_id', 'DESC')->get();
        }    
        else {
            $data = DB::table($table)->where('user_id', $request->user_id)->where('is_read', 0)->orderBy('notification_id', 'DESC')->get();
        }    
        
        $arr = Init::initNotification(1, $data);
        return Init::initResponse($arr, 'View');
    }

    public function getMessaging(Request $request) {
        $type    = $request->type;
        $user_id = $request->user_id;

        $validate = Init::initValidate(
            array('type', 'user'), 
            array($type, $user_id)
        );

        if($validate == "") {
            if($type == 1) {
                $field  = 'chat_from';
                $fields = 'read_from';
            }
            else {
                $field  = 'chat_to';
                $fields = 'read_to';
            }
    
            $data = DB::table('tr_message')->where($field, $user_id)->where($fields, 0)->orderBy('message_datetime')->get();
            $arr  = Init::initMessageResponse(1, $data);

            if($data != "[]") {
                $arr  = Init::initMessageResponse(1, $data);
                return Init::initResponse($arr, 'View'); 
            }
            else {
                $rst = 0;
                return response()->json([
                    'status'    => Init::responseStatus($rst),
                    'message'   =>Init::responseMessage($rst, 'View'),
                    'data'      => array()],
                    200
                );
            }   
        }
        else {
            $rst = 0;
            return response()->json([
                'status'    => Init::responseStatus($rst),
                'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
                'data'      => array()],
                200
            );
        }
    }

    public function sessionSatker(Request $request) {
        $table = "tm_satker";
        $data = DB::table($table)->where('satker_id', $request->satker_id)->get();
        
        $arr = Init::initSatker(1, $data);
        return Init::initResponse($arr, 'View');
    }

    public function AllSatker(Request $request) {
        $table = "tm_satker";
        
        $limit  = $request->limit;
        $limit  = (($limit == "")?10:$limit);
        $offset = $request->offset;
        $offset = (($offset == "")?0:$offset);

        $data = DB::table($table)->where('satker_status', 1)->take($limit)->skip($offset)->orderBy('satker_code', 'ASC')->get();

        $arr = Init::initSatker(1, $data);
        return Init::initResponse($arr, 'View');
    }

    public function levelingSatker(Request $request) {
        $table = "tm_satker";

        $arr = array();
        $satker_type = dBase::dbGetFieldById('tm_satker', 'satker_type', 'satker_id', $request->satker_id);
        if($satker_type == 0) {
            $arrKejagung = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '0' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
            foreach ($arrKejagung as $rKejagung) {
                $arr[] = array(
                    "satker_id"   => $rKejagung->satker_id,
                    "satker_code" => $rKejagung->satker_code,
                    "satker_slug" => $rKejagung->satker_slug,
                    "satker_name" => (($rKejagung->satker_name == null? "":$rKejagung->satker_name)),
                );   
                
                $arrKejati = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '1' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                foreach ($arrKejati as $rKejati) {
                    $arr[] = array(
                        "satker_id"   => $rKejati->satker_id,
                        "satker_code" => $rKejati->satker_code,
                        "satker_slug" => $rKejati->satker_slug,
                        "satker_name" => (($rKejati->satker_name == null? "":$rKejati->satker_name)),
                    );   
                    
                    $tempCode  = $rKejati->satker_code;
                    $arrKejari = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '2' AND `satker_code` LIKE '". $tempCode ."%' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                    foreach ($arrKejari as $rKejari) {
                        $arr[] = array(
                            "satker_id"   => $rKejari->satker_id,
                            "satker_code" => $rKejari->satker_code,
                            "satker_slug" => $rKejari->satker_slug,
                            "satker_name" => (($rKejari->satker_name == null? "":$rKejari->satker_name)),
                        );   

                        $tempCode  = $rKejari->satker_code;
                        $arrCabjari = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '3' AND `satker_code` LIKE '". $tempCode ."%' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                    
                        foreach ($arrCabjari as $rCabjari) {
                            $arr[] = array(
                                "satker_id"   => $rCabjari->satker_id,
                                "satker_code" => $rCabjari->satker_code,
                                "satker_slug" => $rCabjari->satker_slug,
                                "satker_name" => (($rCabjari->satker_name == null? "":$rCabjari->satker_name)),
                            );   
                        }
                    }
                }
            }
            
            $arrBadiklat = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '4' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
            foreach ($arrBadiklat as $rBadiklat) {
                $arr[] = array(
                    "satker_id"   => $rBadiklat->satker_id,
                    "satker_code" => $rBadiklat->satker_code,
                    "satker_slug" => $rBadiklat->satker_slug,
                    "satker_name" => (($rBadiklat->satker_name == null? "":$rBadiklat->satker_name)),
                );   
            }
        } else if($satker_type == 1) {
            $arrKejati = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '1' AND `satker_id` = '". $request->satker_id ."' AND `satker_code` IS NOT NULL;");
            foreach ($arrKejati as $rKejati) {
                $arr[] = array(
                    "satker_id"   => $rKejati->satker_id,
                    "satker_code" => $rKejati->satker_code,
                    "satker_slug" => $rKejati->satker_slug,
                    "satker_name" => (($rKejati->satker_name == null? "":$rKejati->satker_name)),
                );   
                
                $tempCode  = $rKejati->satker_code;
                $arrKejari = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '2' AND `satker_code` LIKE '". $tempCode ."%' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                foreach ($arrKejari as $rKejari) {
                    $arr[] = array(
                        "satker_id"   => $rKejari->satker_id,
                        "satker_code" => $rKejari->satker_code,
                        "satker_slug" => $rKejari->satker_slug,
                        "satker_name" => (($rKejari->satker_name == null? "":$rKejari->satker_name)),
                    );   

                    $tempCode  = $rKejari->satker_code;
                    $arrCabjari = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '3' AND `satker_code` LIKE '". $tempCode ."%' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                
                    foreach ($arrCabjari as $rCabjari) {
                        $arr[] = array(
                            "satker_id"   => $rCabjari->satker_id,
                            "satker_code" => $rCabjari->satker_code,
                            "satker_slug" => $rCabjari->satker_slug,
                            "satker_name" => (($rCabjari->satker_name == null? "":$rCabjari->satker_name)),
                        );   
                    }
                }
            }
        }
        else if($satker_type == 2) {
            $arrKejari = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '2' AND `satker_id` = '". $request->satker_id ."%' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
            foreach ($arrKejari as $rKejari) {
                $arr[] = array(
                    "satker_id"   => $rKejari->satker_id,
                    "satker_code" => $rKejari->satker_code,
                    "satker_slug" => $rKejari->satker_slug,
                    "satker_name" => (($rKejari->satker_name == null? "":$rKejari->satker_name)),
                );   

                $tempCode  = $rKejari->satker_code;
                $arrCabjari = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '3' AND `satker_code` LIKE '". $tempCode ."%' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
            
                foreach ($arrCabjari as $rCabjari) {
                    $arr[] = array(
                        "satker_id"   => $rCabjari->satker_id,
                        "satker_code" => $rCabjari->satker_code,
                        "satker_slug" => $rCabjari->satker_slug,
                        "satker_name" => (($rCabjari->satker_name == null? "":$rCabjari->satker_name)),
                    );   
                }
            }
        }
        else if($satker_type == 3) {
            $arrCabjari = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '3' AND `satker_id` = '". $request->satker_id ."%' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
        
            foreach ($arrCabjari as $rCabjari) {
                $arr[] = array(
                    "satker_id"   => $rCabjari->satker_id,
                    "satker_code" => $rCabjari->satker_code,
                    "satker_slug" => $rCabjari->satker_slug,
                    "satker_name" => (($rCabjari->satker_name == null? "":$rCabjari->satker_name)),
                );   
            }
        } 
        else if($satker_type == 4) {
            $arrBadiklat = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '4' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
            foreach ($arrBadiklat as $rBadiklat) {
                $arr[] = array(
                    "satker_id"   => $rBadiklat->satker_id,
                    "satker_code" => $rBadiklat->satker_code,
                    "satker_slug" => $rBadiklat->satker_slug,
                    "satker_name" => (($rBadiklat->satker_name == null? "":$rBadiklat->satker_name)),
                );   
            }
        }
        
        if(empty($arr)) {
            $arrSatker = DB::select("SELECT `satker_id`, `satker_code`, `satker_slug`, `satker_name` FROM `tm_satker` WHERE `satker_id` = '". $request->satker_id ."';");
            foreach ($arrSatker as $rSatker) {
                $arr[] = array(
                    "satker_id"   => $rSatker->satker_id,
                    "satker_code" => $rSatker->satker_code,
                    "satker_slug" => $rSatker->satker_slug,
                    "satker_name" => (($rSatker->satker_name == null? "":$rSatker->satker_name)),
                );   
            }
        }
        
        return Init::initResponse($arr, 'View');
    }
}