<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Init;
use App\Helpers\Dbase;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class NewsletterController extends Controller
{
    private $table = "tp_newsletter";
    private $field = "newsletter_id";

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
                if($request->email != "") {
                    $data = DB::table($table)->whereBetween('newsletter_date', [$start, $end])->where('satker_id', $satker)->where('newsletter_email', 'like', '%'.$request->email.'%')->take($limit)->skip($offset)->orderBy('newsletter_id', 'DESC')->get();
                }
                else {
                    $data = DB::table($table)->whereBetween('newsletter_date', [$start, $end])->where('satker_id', $satker)->take($limit)->skip($offset)->orderBy('newsletter_id', 'DESC')->get();
                }
            }
            else {
                if($request->email != "") {
                    $data = DB::table($table)->whereBetween('newsletter_date', [$start, $end])->where('newsletter_email', 'like', '%'.$request->email.'%')->take($limit)->skip($offset)->orderBy('newsletter_id', 'DESC')->get();
                }
                else {
                    $data = DB::table($table)->whereBetween('newsletter_date', [$start, $end])->take($limit)->skip($offset)->orderBy('newsletter_id', 'DESC')->get();
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
            'data'      => Init::initNewsletter(1, $data)],
            200
        );
    }

    public function getSingle(Request $request) {
        $id = $request->newsletter_id;
        
        $data = DB::table($this->table)->where($this->field, $id)->get();
        $arr  = Init::initNewsletter(0, $data);

        return Init::initResponse($arr, 'View');
    }

    public function processData(Request $request) {
        $satker = $request->satker_id;
        $email  = $request->email;
        
        $validate = Init::initValidate(
            array('satker', 'email'), 
            array($satker, $email)
        );

        if($validate == "") {
            $now = Carbon::now();
            $date_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                    ->format('Y-m-d');
            $time_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                    ->format('H:i:s');
            
            $rst = DB::table($this->table)
                ->insertGetId([
                    "newsletter_email"  => $email,
                    "newsletter_date"   => $date_at,
                    "newsletter_time"   => $time_at,
                    "newsletter_satker" => Dbase::dbGetFieldById('tm_satker', 'satker_name', 'satker_id', $satker),
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
        $id         = $request->newsletter_id;
        $last_user  = $request->last_user;

        $validate = Init::initValidate(
            array('id'), 
            array($id)
        );

        if($validate == "") {
            $now = Carbon::now();
            $rst = DB::table($this->table)->where($this->field, $id)->delete();

            dBase::setLogActivity($rst, $last_user, $now, 'Delete', 'Hapus data newsletter');        
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