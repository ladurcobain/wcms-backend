<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Init;
use App\Helpers\Dbase;
use App\Helpers\Status;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class SurveyController extends Controller
{
    private $table = "tp_survey";
    private $field = "survey_id";

    public function getAll(Request $request) {
        $limit  = $request->limit;
        $limit  = (($limit == "")?10:$limit);
        $offset = $request->offset;
        $offset = (($offset == "")?0:$offset);

        $start = (($request->start == "")?init::defaultStartDate():$request->start);
        $end   = (($request->end == "")?init::defaultEndDate():$request->end);
        
        $table = $this->table;
        $cnt = 0;
        $tmp = array();
        if($request->user != "") {
            $tmp = DB::table($table)->whereBetween('survey_date', [$start, $end])->where('survey_user', 'like', '%'.$request->user.'%')->take($limit)->skip($offset)->orderBy('survey_id', 'DESC')->get();
            $cnt = DB::table($table)->whereBetween('survey_date', [$start, $end])->where('survey_user', 'like', '%'.$request->user.'%')->count();
        }
        else {
            $tmp = DB::table($table)->whereBetween('survey_date', [$start, $end])->take($limit)->skip($offset)->orderBy('survey_id', 'DESC')->get();
            $cnt = DB::table($table)->whereBetween('survey_date', [$start, $end])->count();
        }
        
        $arr = Init::initSurvey(1, $tmp);
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

    public function getSummary(Request $request) {
        $now = Carbon::now();
        
        $firstDay = $now->firstOfMonth(); 
        $startDay = Carbon::createFromFormat('Y-m-d H:i:s', $firstDay)
                    ->format('Y-m-d'); 

        $lastDay = $now->lastOfMonth();        
        $endDay  = Carbon::createFromFormat('Y-m-d H:i:s', $lastDay)
                    ->format('Y-m-d'); 

        $start = (($request->start == "")?$startDay:$request->start);
        $end   = (($request->end == "")?$endDay:$request->end);
        
        $table = $this->table;
        $count = DB::table($table)->whereBetween('survey_date', [$start, $end])->count();
        $total = DB::table($table)->whereBetween('survey_date', [$start, $end])->sum('survey_value');

        if($count > 0) {
            $average = doubleval($total) / doubleval($count);
        }
        else {
            $average = 0;
        }
        
        $endDay  = Carbon::createFromFormat('Y-m-d H:i:s', $lastDay)
                    ->format('Y-m-d'); 

        $arr = array(
            'start'   => Carbon::createFromFormat('Y-m-d', $start)->format('d-m-Y'),
            'end'     => Carbon::createFromFormat('Y-m-d', $end)->format('d-m-Y'),
            'count'   => $count,
            'total'   => $total,
            'average' => doubleval(number_format($average, 1)),
        );
        
        return Init::initResponse($arr, 'View');
    }

    public function getSingle(Request $request) {
        $id = $request->survey_id;
        
        $data = DB::table($this->table)->where($this->field, $id)->get();
        $arr  = Init::initSurvey(0, $data);

        return Init::initResponse($arr, 'View');
    }

    public function processData(Request $request) {
        $user        = $request->user_id;
        $value       = $request->value;
        $description = $request->description;
        
        $validate = Init::initValidate(
            array('user'), 
            array($user)
        );

        if($validate == "") {
            $now = Carbon::now();
            $date_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                    ->format('Y-m-d');
            $time_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                    ->format('H:i:s');
                    
            $exist = DB::table($this->table)->where('user_id', $user)->count();
            if($exist <= 0) {
                $rst = DB::table($this->table)
                    ->insertGetId([
                        "survey_value"        => (($value == "")? 0:$value),
                        "survey_date"         => $date_at,
                        "survey_time"         => $time_at,
                        "survey_description"  => (($description == null)? "":nl2br($description)),
                        "survey_user"         => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $user),
                        "user_id"             => $user,
                    ]);  
            }
            else {
                $rst = DB::table($this->table)
                    ->where('user_id', $user)
                    ->update([
                        "survey_value"        => (($value == "")? 0:$value),
                        "survey_date"         => $date_at,
                        "survey_time"         => $time_at,
                        "survey_description"  => (($description == null)? "":nl2br($description)),
                    ]); 
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
    
    public function deleteData(Request $request) {
        $id         = $request->survey_id;
        $last_user  = $request->last_user;

        $validate = Init::initValidate(
            array('id'), 
            array($id)
        );

        if($validate == "") {
            $now = Carbon::now();
            $rst = DB::table($this->table)->where($this->field, $id)->delete();

            dBase::setLogActivity($rst, $last_user, $now, 'Delete', 'Hapus data survey');        
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

    public function getByUser(Request $request) {
        $id = $request->user_id;
        
        $data = DB::table($this->table)->where('user_id', $id)->get();
        $arr  = Init::initSurvey(0, $data);

        return Init::initResponse($arr, 'View');
    }
}