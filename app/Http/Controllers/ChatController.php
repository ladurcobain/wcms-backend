<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Init;
use App\Helpers\Dbase;
use App\Helpers\Status;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    private $table = "tp_chat";
    private $field = "chat_id";

    
    public function getByType(Request $request) {
        $type = ($request->type == "")?1:$request->type;
        if($type == 1) {
            $field = 'chat_from';
        }
        else {
            $field = 'chat_to';
        }
        
        $id   = $request->user_id;
        $data = DB::table($this->table)->where($field, $id)->orderBy('last_edited', 'DESC')->get();
        $arr  = Init::initChatResponse(1, $data, $type, $id);

        return Init::initResponse($arr, 'View');
    }

    public function getBySingle(Request $request) {
        $id   = $request->chat_id;
        $data = DB::table($this->table)->where($this->field, $id)->orderBy('last_edited', 'DESC')->get();
        $arr  = Init::initChatResponse(0, $data);

        return Init::initResponse($arr, 'View');
    }

    public function processData(Request $request) {
        $type    = $request->type;
        $from    = $request->user_from;
        $to      = $request->user_to;
        $message = $request->message;
       
        $validate = Init::initValidate(
            array('type', 'from', 'to'), 
            array($type, $from, $to)
        );

        if($validate == "") {
            if($type == 1) {
                $fields = 'read_from';
            }
            else {
                $fields = 'read_to';
            }

            $rst = 1;
            $now = Carbon::now();
            $chat_id = Dbase::dbGetFieldByTwoId($this->table, 'chat_id', 'chat_from', $from, 'chat_to', $to);
            if($chat_id == "") {
                $chat_id = DB::table($this->table)
                    ->insertGetId([
                        "chat_from"   => $from,
                        "chat_to"     => $to,
                        "last_edited" => $now,
                    ]);   
            
                DB::table('tr_message')
                    ->insert([
                        "message_type"      => $type,
                        "message_datetime"  => $now,
                        "message_text"      => ($message == "")?"...":nl2br($message),
                        "chat_from"         => $from,
                        "chat_to"           => $to,
                        "chat_id"           => $chat_id,
                        $fields             => 1
                    ]); 
            }
            else {
                DB::table('tr_message')
                    ->insert([
                        "message_type"      => $type,
                        "message_datetime"  => $now,
                        "message_text"      => ($message == "")?"...":nl2br($message),
                        "chat_from"         => $from,
                        "chat_to"           => $to,
                        "chat_id"           => $chat_id,
                        $fields             => 1
                    ]);   
            }

            $data = DB::table($this->table)->where($this->field, $chat_id)->orderBy('last_edited', 'DESC')->get();
            $arr  = Init::initChatResponse(0, $data); 
            return Init::initResponse($arr, 'View');  
        }
        else {
            $rst = 0;
            return response()->json([
                'status'    => Init::responseStatus($rst),
                'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'Insert')),
                'data'      => array()],
                200
            );
        }
    }

    public function getMessage(Request $request) {
        $id      = $request->chat_id;
        $type    = $request->type;
        $user_id = $request->user_id;

        $validate = Init::initValidate(
            array('id', 'type', 'user'), 
            array($id, $type, $user_id)
        );

        if($validate == "") {
            $data = DB::table('tr_message')->where($this->field, $id)->orderBy('message_datetime')->get();
            $arr  = Init::initMessageResponse(1, $data);
    
            if($type == 1) {
                $field  = 'chat_from';
                $fields = 'read_from';
            }
            else {
                $field  = 'chat_to';
                $fields = 'read_to';
            }
    
            DB::table('tr_message')
                ->where($this->field, $id)
                ->where($field, $user_id)
                ->where($fields, 0)
                ->update([
                    $fields => 1,
                ]); 

            return Init::initResponse($arr, 'View');    
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

    public function checkMessage(Request $request) {
        $id   = $request->chat_id;
        $type = $request->type;

        $validate = Init::initValidate(
            array('id', 'type'), 
            array($id, $type)
        );

        if($validate == "") {
            if($type == 1) {
                $data = DB::table('tr_message')->where($this->field, $id)->where('read_from', 0)->get();
                DB::table('tr_message')
                    ->where($this->field, $id)
                    ->where('read_from', 0)
                    ->update([
                        'read_from' => 1,
                ]); 
            }
            else if($type == 2) {
                $data = DB::table('tr_message')->where($this->field, $id)->where('read_to', 0)->get();
                DB::table('tr_message')
                    ->where($this->field, $id)
                    ->where('read_to', 0)
                    ->update([
                        'read_to' => 1,
                ]); 
            }
            else {
                $data = array();
            }

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

    public function processMessage(Request $request) {
        $id      = $request->chat_id;
        $type    = $request->type;
        $message = $request->message;
       
        $validate = Init::initValidate(
            array('id', 'type'), 
            array($id, $type)
        );

        if($validate == "") {
            $now  = Carbon::now();
            $from = Dbase::dbGetFieldById($this->table, 'chat_from', $this->field, $id);
            $to   = Dbase::dbGetFieldById($this->table, 'chat_to', $this->field, $id);

            if($type == 1) {
                $fields = 'read_from';
            }
            else {
                $fields = 'read_to';
            }

            $rst =DB::table('tr_message')
                ->insertGetId([
                    "message_type"      => $type,
                    "message_datetime"  => $now,
                    "message_text"      => ($message == "")?"...":nl2br($message),
                    "chat_from"         => $from,
                    "chat_to"           => $to,
                    "chat_id"           => $id,
                    $fields             => 1,
                ]); 
            
            Dbase::dbSetFieldById($this->table, 'last_edited', $now, $this->field, $id);
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

    public function removeData(Request $request) {
        $id         = $request->chat_id;
        $last_user  = $request->last_user;

        $validate = Init::initValidate(
            array('id'), 
            array($id)
        );

        if($validate == "") {
            $now = Carbon::now();
            $rst = DB::table($this->table)->where($this->field, $id)->delete();
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