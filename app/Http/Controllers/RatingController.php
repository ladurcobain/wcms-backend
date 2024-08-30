<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Init;
use App\Helpers\Dbase;
use App\Helpers\Status;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class RatingController extends Controller
{
    private $table = "tp_rating";
    private $field = "rating_id";

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
                if($request->ip != "") {
                    $data = DB::table($table)->whereBetween('rating_date', [$start, $end])->where('satker_id', $satker)->where('rating_ip', 'like', '%'.$request->ip.'%')->take($limit)->skip($offset)->orderBy('rating_id', 'DESC')->get();
                }
                else {
                    $data = DB::table($table)->whereBetween('rating_date', [$start, $end])->where('satker_id', $satker)->take($limit)->skip($offset)->orderBy('rating_id', 'DESC')->get();
                }
            }
            else {
                if($request->ip != "") {
                    $data = DB::table($table)->whereBetween('rating_date', [$start, $end])->where('rating_ip', 'like', '%'.$request->ip.'%')->take($limit)->skip($offset)->orderBy('rating_id', 'DESC')->get();
                }
                else {
                    $data = DB::table($table)->whereBetween('rating_date', [$start, $end])->take($limit)->skip($offset)->orderBy('rating_id', 'DESC')->get();
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
            'data'      => Init::initRating(1, $data)],
            200
        );
    }

    public function getSingle(Request $request) {
        $id = $request->rating_id;
        
        $data = DB::table($this->table)->where($this->field, $id)->get();
        $arr  = Init::initRating(0, $data);

        return Init::initResponse($arr, 'View');
    }

    public function processData(Request $request) {
        $satker      = $request->satker_id;
        $ip          = $request->ip;
        $value       = $request->value;
        $description = $request->description;
        
        $validate = Init::initValidate(
            array('satker', 'ip'), 
            array($satker, $ip)
        );

        if($validate == "") {
            $now = Carbon::now();
            $date_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                    ->format('Y-m-d');
            $time_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                    ->format('H:i:s');
            
            $rst = DB::table($this->table)
                ->insertGetId([
                    "rating_value"        => (($value == "")? 0:$value),
                    "rating_ip"           => $ip,
                    "rating_date"         => $date_at,
                    "rating_time"         => $time_at,
                    "rating_description"  => (($description == null)? "":nl2br($description)),
                    "rating_satker"       => Dbase::dbGetFieldById('tm_satker', 'satker_name', 'satker_id', $satker),
                    "satker_id"           => $satker,
                ]);     
        }
        else {
            $rst = 0;
        }
 
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'Rating')),
            'data'      => array()],
            200
        );
    }
    
    public function deleteData(Request $request) {
        $id         = $request->rating_id;
        $last_user  = $request->last_user;

        $validate = Init::initValidate(
            array('id'), 
            array($id)
        );

        if($validate == "") {
            $now = Carbon::now();
            $rst = DB::table($this->table)->where($this->field, $id)->delete();

            dBase::setLogActivity($rst, $last_user, $now, 'Delete', 'Hapus data rating');        
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