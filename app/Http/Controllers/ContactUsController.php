<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Init;
use App\Helpers\Dbase;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class ContactUsController extends Controller
{
    private $table = "tp_contactus";
    private $field = "contactus_id";

    public function getAll(Request $request) {
        $user   = $request->user_id;
        $limit  = $request->limit;
        $offset = $request->offset;

        $validate = Init::initValidate(
            array('user'), 
            array($user)
        );

        if($validate == "") {
            $rst = 1;
            $start = (($request->start == "")?init::defaultStartDate():$request->start);
            $end   = (($request->end == "")?init::defaultEndDate():$request->end);
            
            $table = $this->table;
            if($request->satker_id != "") {
                $satker = $request->satker_id;
            }
            else {
                $satker = dBase::dbGetFieldById('tm_user', 'satker_id', 'user_id', $user);
            }
            
            if($satker != "") {
                if(($limit != "") && ($offset != "")) {
                    if($request->name != "") {
                        $data = DB::table($table)->whereBetween('contactus_date', [$start, $end])->where('satker_id', $satker)->where('contactus_name', 'like', '%'.$request->name.'%')->take($limit)->skip($offset)->orderBy('contactus_id', 'DESC')->get();
                    }
                    else {
                        $data = DB::table($table)->whereBetween('contactus_date', [$start, $end])->where('satker_id', $satker)->take($limit)->skip($offset)->orderBy('contactus_id', 'DESC')->get();
                    }
                }
                else {
                    if($request->name != "") {
                        $data = DB::table($table)->whereBetween('contactus_date', [$start, $end])->where('satker_id', $satker)->where('contactus_name', 'like', '%'.$request->name.'%')->orderBy('contactus_id', 'DESC')->get();
                    }
                    else {
                        $data = DB::table($table)->whereBetween('contactus_date', [$start, $end])->where('satker_id', $satker)->orderBy('contactus_id', 'DESC')->get();
                    }
                }
            }
            else {
                if(($limit != "") && ($offset != "")) {
                    if($request->name != "") {
                        $data = DB::table($table)->whereBetween('contactus_date', [$start, $end])->where('contactus_name', 'like', '%'.$request->name.'%')->take($limit)->skip($offset)->orderBy('contactus_id', 'DESC')->get();
                    }
                    else {
                        $data = DB::table($table)->whereBetween('contactus_date', [$start, $end])->take($limit)->skip($offset)->orderBy('contactus_id', 'DESC')->get();
                    }
                }
                else {
                    if($request->name != "") {
                        $data = DB::table($table)->whereBetween('contactus_date', [$start, $end])->where('contactus_name', 'like', '%'.$request->name.'%')->orderBy('contactus_id', 'DESC')->get();
                    }
                    else {
                        $data = DB::table($table)->whereBetween('contactus_date', [$start, $end])->orderBy('contactus_id', 'DESC')->get();
                    }
                }
            }
        }
        else {
            $rst = 0;
            $data = array();
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => Init::initcontactus(1, $data)],
            200
        );
    }

    public function getSingle(Request $request) {
        $id = $request->contactus_id;
        
        $data = DB::table($this->table)->where($this->field, $id)->get();
        $arr  = Init::initcontactus(0, $data);

        return Init::initResponse($arr, 'View');
    }

    public function processData(Request $request) {
        $satker  = $request->satker_id;
        $name    = $request->name;
        $email   = $request->email;
        $subject = $request->subject;
        $message = $request->message;
        
        $validate = Init::initValidate(
            array('satker', 'name', 'email'), 
            array($satker, $name, $email)
        );

        if($validate == "") {
            $now = Carbon::now();
            $date_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                    ->format('Y-m-d');
            $time_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                    ->format('H:i:s');
            
            $rst = DB::table($this->table)
                ->insertGetId([
                    "contactus_name"    => $name,
                    "contactus_email"   => $email,
                    "contactus_subject" => (($subject == null)? "":$subject),
                    "contactus_message" => (($message == null)? "":nl2br($message)),
                    "contactus_date"    => $date_at,
                    "contactus_time"    => $time_at,
                    "contactus_satker"  => Dbase::dbGetFieldById('tm_satker', 'satker_name', 'satker_id', $satker),
                    "satker_id"         => $satker,
                ]);     
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
    
    public function deleteData(Request $request) {
        $id         = $request->contactus_id;
        $last_user  = $request->last_user;

        $validate = Init::initValidate(
            array('id'), 
            array($id)
        );

        if($validate == "") {
            $now = Carbon::now();
            $rst = DB::table($this->table)->where($this->field, $id)->delete();

            dBase::setLogActivity($rst, $last_user, $now, 'Delete', 'Hapus data contactus');        
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