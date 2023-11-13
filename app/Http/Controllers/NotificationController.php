<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Init;
use App\Helpers\Dbase;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    private $table = "tp_notification";
    private $field = "notification_id";

    public function getAll(Request $request) {
        $user   = $request->user_id;
        
        $limit  = $request->limit;
        $limit  = (($limit == "")?10:$limit);
        $offset = $request->offset;
        $offset = (($offset == "")?0:$offset);

        $validate = Init::initValidate(
            array('user'), 
            array($user)
        );

        if($validate == "") {
            $rst = 1;
            $start = (($request->start == "")?init::defaultStartDate():$request->start);
            $end   = (($request->end == "")?init::defaultEndDate():$request->end);
            
            $table = $this->table;
            $satker = dBase::dbGetFieldById('tm_user', 'satker_id', 'user_id', $user);

            $cnt = 0;
            $tmp = array();
            if($satker != "") {
                if($request->title != "") {
                    $tmp = DB::table($table)->whereBetween('notification_date', [$start, $end])->where('user_id', $user)->where('notification_title', 'like', '%'.$request->title.'%')->take($limit)->skip($offset)->orderBy('notification_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('notification_date', [$start, $end])->where('user_id', $user)->where('notification_title', 'like', '%'.$request->title.'%')->count();
                }
                else {
                    $tmp = DB::table($table)->whereBetween('notification_date', [$start, $end])->where('user_id', $user)->take($limit)->skip($offset)->orderBy('notification_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('notification_date', [$start, $end])->where('user_id', $user)->count();
                }
            }
            else {
                if($request->title != "") {
                    $tmp = DB::table($table)->whereBetween('notification_date', [$start, $end])->where('notification_title', 'like', '%'.$request->title.'%')->take($limit)->skip($offset)->orderBy('notification_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('notification_date', [$start, $end])->where('notification_title', 'like', '%'.$request->title.'%')->count();
                }
                else {
                    $tmp = DB::table($table)->whereBetween('notification_date', [$start, $end])->take($limit)->skip($offset)->orderBy('notification_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('notification_date', [$start, $end])->count();
                }
            }  
            
            $arr = Init::initNotification(1, $tmp);
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
        else {
            return response()->json([
                'status'    => Init::responseStatus(0),
                'message'   => $validate,
                'data'      => array()],
                200
            );
        }
    }

    public function getSingle(Request $request) {
        $id   = $request->notification_id;
        dBase::dbSetFieldById($this->table, 'is_read', 1, $this->field, $id);
        
        $data = DB::table($this->table)->where($this->field, $id)->get();
        $arr  = Init::initNotification(0, $data);

        return Init::initResponse($arr, 'View');
    }

    public function processData(Request $request) {
        $user        = $request->user_id;
        $title       = $request->title;
        $description = $request->description;
        $published   = $request->published;
        $last_user   = $request->last_user;

        $validate = Init::initValidate(
            array('user', 'title', 'description'), 
            array($user, $title, $description)
        );

        if($validate == "") {
            $now = Carbon::now();
            $date_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                    ->format('Y-m-d');
            $time_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                    ->format('H:i:s');

            if($user == "*") {
                $users = DB::table('tm_user')->where('user_type', 2)->where('user_status', 1)->where('is_deleted', 0)->orderBy('user_id')->get();
                if($users != "[]") {
                    foreach($users as $row) {
                        $rst = DB::table($this->table)
                            ->insertGetId([
                                "is_published"              => (($published != 1)? 0:1),
                                "notification_date"         => $date_at,
                                "notification_time"         => $time_at,
                                "notification_title"        => $title,
                                "notification_description"  => (($description == null)? "":nl2br($description)),
                                "notification_user"         => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $row->user_id),
                                "user_id"                   => $row->user_id,
                                "last_user"                 => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                            ]);   

                        $token = Dbase::dbGetFieldById('tm_user', 'user_token', 'user_id', $row->user_id);
                        if($token != "") {
                            $fcm = dBase::sendCloudMessaging($token, $title, (($description == null)? "":strip_tags($description)));  
                        }
                    }    
                }
                else {
                    $rst = 0;
                }
            }        
            else {
                $rst = DB::table($this->table)
                    ->insertGetId([
                        "is_published"              => (($published == 1)? 1:0),
                        "notification_date"         => $date_at,
                        "notification_time"         => $time_at,
                        "notification_title"        => $title,
                        "notification_description"  => (($description == null)? "":nl2br($description)),
                        "notification_user"         => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $user),
                        "user_id"                   => $user,
                        "last_user"                 => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                    ]);   

                $token = Dbase::dbGetFieldById('tm_user', 'user_token', 'user_id', $user);
                if($token != "") {
                    $fcm = dBase::sendCloudMessaging($token, $title, (($description == null)? "":strip_tags($description)));  
                }    
            }
            
            if($rst != 0) {
                dBase::setLogActivity($rst, $last_user, $now, 'Process', 'Proses data notifikasi');  
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
    
    public function updateData(Request $request) {
        $id        = $request->notification_id;
        $published = $request->published;
        $last_user = $request->last_user;

        $validate = Init::initValidate(
            array('id'), 
            array($id)
        );

        if($validate == "") {
            $now = Carbon::now();
            $rst = DB::table($this->table)
                ->where($this->field, $id)
                ->update([
                    "is_published"  => (($published == 1)? 1:0),
                ]); 

            dBase::setLogActivity($rst, $last_user, $now, 'Update', 'Ubah data notifikasi');        
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
        $id         = $request->notification_id;
        $last_user  = $request->last_user;

        $validate = Init::initValidate(
            array('id'), 
            array($id)
        );

        if($validate == "") {
            $now = Carbon::now();
            $rst = DB::table($this->table)->where($this->field, $id)->delete();

            dBase::setLogActivity($rst, $last_user, $now, 'Delete', 'Hapus data notifikasi');        
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