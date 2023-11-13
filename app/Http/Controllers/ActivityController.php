<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Init;
use App\Helpers\Dbase;
use App\Helpers\Status;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    public function getLog(Request $request) {
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
            
            $table = "tr_activity";
            $satker = dBase::dbGetFieldById('tm_user', 'satker_id', 'user_id', $user);
            
            $cnt = 0;
            $tmp = array();
            if($satker != "") {
                if($request->keyword != "") {
                    $tmp = DB::table($table)->whereBetween('activity_date', [$start, $end])->where('user_id', $user)->where('activity_description', 'like', '%'.$request->keyword.'%')->take($limit)->skip($offset)->orderBy('activity_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('activity_date', [$start, $end])->where('user_id', $user)->where('activity_description', 'like', '%'.$request->keyword.'%')->count();
                }
                else {
                    $tmp = DB::table($table)->whereBetween('activity_date', [$start, $end])->where('user_id', $user)->take($limit)->skip($offset)->orderBy('activity_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('activity_date', [$start, $end])->where('user_id', $user)->count();
                }
            }
            else {
                if($request->keyword != "") {
                    $tmp = DB::table($table)->whereBetween('activity_date', [$start, $end])->where('activity_description', 'like', '%'.$request->keyword.'%')->take($limit)->skip($offset)->orderBy('activity_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('activity_date', [$start, $end])->where('activity_description', 'like', '%'.$request->keyword.'%')->count();
                }
                else {
                    $tmp = DB::table($table)->whereBetween('activity_date', [$start, $end])->take($limit)->skip($offset)->orderBy('activity_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('activity_date', [$start, $end])->count();
                }
            }

            $arr = Init::initActivityLog(1, $tmp);
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

    public function getDashboard(Request $request) {
        $user = $request->user_id;
        $validate = Init::initValidate(
            array('user'), 
            array($user)
        );

        $arrLinechart = array();
        $arrPiechart  = array();

        $arrPlotchart = array();
        $arrBarchart  = array();
        //$arrLog = array();
        //$arrNotification = array();
        $arrNewslatest  = array();
        //$arrNewspopular = array();
        $arrRating = array();
        //$arrSurvey = array();
        
        if($validate == "") {
            $rst = 1;
        
            if($request->mobile != 1) {
                $surveys = DB::table('tp_survey')->limit(5)->orderBy('survey_id', 'DESC')->get();
            }

            $now = Carbon::now();
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth(); 

            $dayStartOfMonth = Carbon::createFromFormat('Y-m-d H:i:s', $startOfMonth)->format('d');
            $dayEndOfMonth   = Carbon::createFromFormat('Y-m-d H:i:s', $endOfMonth)->format('d');

            $dateOfYear  = Carbon::createFromFormat('Y-m-d H:i:s', $now)->format('Y-m-d');
            $monthOfYear = Carbon::createFromFormat('Y-m-d H:i:s', $startOfMonth)->format('m'); 
            $yearOfYear  = Carbon::createFromFormat('Y-m-d H:i:s', $startOfMonth)->format('Y'); 

            $menus  = DB::table('tm_menu')->where('menu_status', 1)->orderBy('menu_id', 'ASC')->get();
            $satker = dBase::dbGetFieldById('tm_user', 'satker_id', 'user_id', $user);
            
            $visitor = array();
            $article = array();
            if($satker != "") {
                $satkerName = dBase::dbGetFieldById('tm_satker', 'satker_name', 'satker_id', $satker);
                $visitor[] = array(
                    'title' => $satkerName,
                    'count' => DB::table('tr_visitor')->where('satker_id', $satker)->count()
                );

                $visitor[] = array(
                    'title' => "Tahun ". $yearOfYear,
                    'count' => DB::table('tr_visitor')->where('satker_id', $satker)->where(DB::raw('YEAR(visitor_date)'), '=', $yearOfYear)->count()
                );

                $visitor[] = array(
                    'title' => "Periode ". Status::monthName(Carbon::createFromFormat('Y-m-d H:i:s', $now)->format('m')) .' '. $yearOfYear,
                    'count' => DB::table('tr_visitor')->where('satker_id', $satker)->where('visitor_date', 'like', '%'.$yearOfYear .'-'. $monthOfYear.'%')->count()
                );

                $visitor[] = array(
                    'title' => "Tanggal ". Carbon::createFromFormat('Y-m-d H:i:s', $now)->format('d-m-Y'),
                    'count' => DB::table('tr_visitor')->where('satker_id', $satker)->where('visitor_date', $dateOfYear)->count()
                );
                
                $article[] = array(
                    'title' => 'Total Berita',
                    'count' => DB::table('tp_news')->where('is_deleted', 0)->where('satker_id', $satker)->count()
                );

                $article[] = array(
                    'title' => 'Berita Diterbitkan',
                    'count' => DB::table('tp_news')->where('news_status', 1)->where('is_deleted', 0)->where('satker_id', $satker)->count()
                );

                $article[] = array(
                    'title' => 'Berita Diturunkan',
                    'count' => DB::table('tp_news')->where('news_status', 0)->where('is_deleted', 0)->where('satker_id', $satker)->count()
                );

                for($i=5; $i >= 1; $i--) {
                    $label = Status::statusRating($i);
        
                    $arrPiechart[] = array(
                        'title' => $label,
                        'count' => dBase::dbGetCountByTwoId('tp_rating', 'rating_value', $i, 'satker_id', $satker)
                    );
                }

                for($i=0; $i<7; $i++) {
                    $newDateTime = Carbon::now()->subDays($i);
                    $dateWeek = Carbon::createFromFormat('Y-m-d H:i:s', $newDateTime)->format('Y-m-d'); 
                    $dateFormat = Carbon::createFromFormat('Y-m-d', $dateWeek)->format('d-m-Y'); 
                        
                    $arrLinechart[] = array(
                        'day'   => intval($i),
                        'title' => $dateFormat,
                        'count' => dBase::dbGetCountById('tr_visitor', 'visitor_date', $dateWeek, 'satker_id', $satker)
                    );
    
                }

                if($request->mobile != 1) {
                    //$logs           = DB::table('tr_activity')->limit(20)->where('user_id', $user)->orderBy('activity_id', 'DESC')->get();
                    //$notifications  = DB::table('tp_notification')->limit(5)->where('user_id', $user)->orderBy('notification_id', 'DESC')->get();
                    $ratings        = DB::table('tp_rating')->limit(5)->where('satker_id', $satker)->orderBy('rating_id', 'DESC')->get();
                    $news_latest    = DB::table('tp_news')->limit(10)->where('satker_id', $satker)->where('news_status', 1)->orderBy('news_date', 'DESC')->get();
                    //$news_popular   = DB::table('tp_news')->limit(5)->where('satker_id', $satker)->where('news_status', 1)->orderBy('news_view', 'DESC')->get();

                    for($i=$dayStartOfMonth; $i <= $dayEndOfMonth; $i++) {
                        $dateOf    = $yearOfYear .'-'. $monthOfYear .'-'. $i;
                        $dateMonth = Carbon::createFromFormat('Y-m-d', $dateOf)->format('d-m-Y'); 
                        
                        $arrPlotchart[] = array(
                            'day'   => intval($i),
                            'title' => $dateMonth,
                            'count' => dBase::dbGetCountByTwoId('tr_visitor', 'visitor_date', $dateOf, 'satker_id', $satker)
                        );
                    }

                    $arrMenu = array();
                    foreach($menus as $r) {
                        $menu = $r->menu_name;

                        $arrMenu[] = array(
                            'title' => $menu,
                            'count' => dBase::dbGetCountByTwoId('tr_visitor', 'menu_id', $r->menu_id, 'satker_id', $satker)
                        );
                    }
                    
                    if($arrMenu != "[]") {
                        $arr = collect($arrMenu)->sortBy('count')->reverse()->toArray();    
                        $i = 0;
                        foreach($arr as $r) {
                            $arrBarchart[] = array(
                                'title' => $r['title'],
                                'count' => $r['count'],
                            );

                            $i = $i + 1;
                            if($i == 10) {
                                break;
                            } 
                        }
                    }
                }
            }
            else {
                $visitor[] = array(
                    'title' => 'Kunjungan Persatker',
                    'count' => DB::table('tr_visitor')->count()
                );

                $visitor[] = array(
                    'title' => "Tahun ". $yearOfYear,
                    'count' => DB::table('tr_visitor')->where(DB::raw('YEAR(visitor_date)'), '=', $yearOfYear)->count()
                );

                $visitor[] = array(
                    'title' => "Periode ". Status::monthName(Carbon::createFromFormat('Y-m-d H:i:s', $now)->format('m')) .' '. $yearOfYear,
                    'count' => DB::table('tr_visitor')->where('visitor_date', 'like', '%'.$yearOfYear .'-'. $monthOfYear.'%')->count()
                );

                $visitor[] = array(
                    'title' => "Tanggal ". Carbon::createFromFormat('Y-m-d H:i:s', $now)->format('d-m-Y'),
                    'count' => DB::table('tr_visitor')->where('visitor_date', $dateOfYear)->count()
                );

                $article[] = array(
                    'title' => 'Total Berita',
                    'count' => DB::table('tp_news')->where('is_deleted', 0)->count()
                );

                $article[] = array(
                    'title' => 'Berita Diterbitkan',
                    'count' => DB::table('tp_news')->where('news_status', 1)->where('is_deleted', 0)->count()
                );

                $article[] = array(
                    'title' => 'Berita Diturunkan',
                    'count' => DB::table('tp_news')->where('news_status', 0)->where('is_deleted', 0)->count()
                );

                for($i=5; $i >= 1; $i--) {
                    $label = Status::statusRating($i);
        
                    $arrPiechart[] = array(
                        'title' => $label,
                        'count' => dBase::dbGetCountById('tp_rating', 'rating_value', $i)
                    );
                }

                for($i=0; $i<7; $i++) {
                    $newDateTime = Carbon::now()->subDays($i);
                    $dateWeek = Carbon::createFromFormat('Y-m-d H:i:s', $newDateTime)->format('Y-m-d'); 
                    $dateFormat = Carbon::createFromFormat('Y-m-d', $dateWeek)->format('d-m-Y'); 
                        
                    $arrLinechart[] = array(
                        'day'   => intval($i),
                        'title' => $dateFormat,
                        'count' => dBase::dbGetCountById('tr_visitor', 'visitor_date', $dateWeek)
                    );
    
                }

                if($request->mobile != 1) {
                    //$logs           = DB::table('tr_activity')->limit(20)->orderBy('activity_id', 'DESC')->get();
                    //$notifications  = DB::table('tp_notification')->limit(5)->orderBy('notification_id', 'DESC')->get();
                    $ratings        = DB::table('tp_rating')->limit(5)->orderBy('rating_id', 'DESC')->get();
                    $news_latest    = DB::table('tp_news')->limit(10)->where('news_status', 1)->orderBy('news_date', 'DESC')->get();
                    //$news_popular   = DB::table('tp_news')->limit(5)->where('news_status', 1)->orderBy('news_view', 'DESC')->get();

                    for($i=$dayStartOfMonth; $i <= $dayEndOfMonth; $i++) {
                        $dateOf    = $yearOfYear .'-'. $monthOfYear .'-'. $i;
                        $dateMonth = Carbon::createFromFormat('Y-m-d', $dateOf)->format('d-m-Y'); 
                        
                        $arrPlotchart[] = array(
                            'day'   => intval($i),
                            'title' => $dateMonth,
                            'count' => dBase::dbGetCountById('tr_visitor', 'visitor_date', $dateOf)
                        );
                    }

                    $arrMenu = array();
                    foreach($menus as $r) {
                        $menu = $r->menu_name;

                        $arrMenu[] = array(
                            'title' => $menu,
                            'count' => dBase::dbGetCountById('tr_visitor', 'menu_id', $r->menu_id)
                        );
                    }
                    
                    if($arrMenu != "[]") {
                        $arr = collect($arrMenu)->sortBy('count')->reverse()->toArray();    
                        $i = 0;
                        foreach($arr as $r) {
                            $arrBarchart[] = array(
                                'title' => $r['title'],
                                'count' => $r['count'],
                            );

                            $i = $i + 1;
                            if($i == 10) {
                                break;
                            } 
                        }
                    }
                }
            }

            if($request->mobile != 1) {
                //$arrLog = Init::initActivityLog(1, $logs);
                //$arrNotification = Init::initNotification(1, $notifications);
                $arrRating = Init::initRating(1, $ratings);
                //$arrSurvey = Init::initSurvey(1, $surveys);

                $arrNewslatest = Init::initConferenceNews(1, $news_latest);
                //$arrNewspopular = Init::initConferenceNews(1, $news_popular);
            }
        }    
        else {
            $rst = 0;
        }
        
        $piechart = array(
            'title' => "Rating indeks kepuasaan",
            'arr'   => $arrPiechart
        );
        
        $linechart = array(
            'title' => "Kunjungan minggu ini",
            'arr'   => $arrLinechart
        );

        if($request->mobile != 1) {
            $plotchart = array(
                'title' => "Kunjungan bulan ini",
                'arr'   => $arrPlotchart
            );
            
            $barchart = array(
                'title' => "Kunjungan berdasarkan menu",
                'arr'   => $arrBarchart
            );
        }

        if($request->mobile == 1) {
            $arr = array(
                'linechart' => $linechart,
                'piechart'  => $piechart,
            );
        }
        else {
            $arr = array(
                //'latest_survey'         => $arrSurvey,
                //'latest_activity'       => $arrLog,
                //'latest_notification'   => $arrNotification,
                'news_latest'           => $arrNewslatest,
                //'news_popular'          => $arrNewspopular,
                'latest_rating'         => $arrRating,
                //'linechart'             => $linechart,
                'barchart'              => $barchart,
                'piechart'              => $piechart,
                'plotchart'             => $plotchart,
                'visitor'               => $visitor,
                'article'               => $article
            );    
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => Init::responseMessage($rst, 'View'),
            'data'      => $arr],
            200
        );
    }

    // public function getSearchContent(Request $request) {
    //     $user   = $request->user_id;
        
    //     $limit  = $request->limit;
    //     $limit  = (($limit == "")?10:$limit);
    //     $offset = $request->offset;
    //     $offset = (($offset == "")?0:$offset);

    //     $validate = Init::initValidate(
    //         array('user'), 
    //         array($user)
    //     );

        
    //     $rst = 0;
    //     $cnt = 0;
    //     $arr = array();
    //     if($validate == "") {
    //         $rst = 1;
            
    //         $table = "tv_content";
    //         $satker = dBase::dbGetFieldById('tm_user', 'satker_id', 'user_id', $user);
            
    //         if($satker != "") {
    //             if($request->keyword != "") {
    //                 $arr = DB::table($table)->where('satker_id', $satker)->where('content_text_in', 'like', '%'.$request->keyword.'%')->take($limit)->skip($offset)->orderBy('content_id', 'DESC')->get();
    //                 $cnt = DB::table($table)->where('satker_id', $satker)->where('content_text_in', 'like', '%'.$request->keyword.'%')->count();
    //             }
    //             else {
    //                 $arr = DB::table($table)->where('satker_id', $satker)->take($limit)->skip($offset)->orderBy('content_id', 'DESC')->get();
    //                 $cnt = DB::table($table)->where('satker_id', $satker)->count();
    //             }
    //         }
    //         else {
    //             if($request->keyword != "") {
    //                 $arr = DB::table($table)->where('content_text_in', 'like', '%'.$request->keyword.'%')->take($limit)->skip($offset)->orderBy('content_id', 'DESC')->get();
    //                 $cnt = DB::table($table)->where('content_text_in', 'like', '%'.$request->keyword.'%')->count();
    //             }
    //             else {
    //                 $arr = DB::table($table)->take($limit)->skip($offset)->orderBy('content_id', 'DESC')->get();
    //                 $cnt = DB::table($table)->count();
    //             }
    //         }
    //     }

    //     $data = array(
    //         'total' => $cnt,
    //         'list'  => Init::initSearchContent(1, $arr),
    //     );

    //     return response()->json([
    //         'status'    => Init::responseStatus($rst),
    //         'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
    //         'data'      => $data],
    //         200
    //     );
    // }

    public function getSearchContent(Request $request) {
        $user   = $request->user_id;
        
        $limit  = $request->limit;
        $limit  = (($limit == "")?10:$limit);
        $offset = $request->offset;
        $offset = (($offset == "")?0:$offset);

        $validate = Init::initValidate(
            array('user'), 
            array($user)
        );

        
        $rst = 0;
        $cnt = 0;
        $arr = array();
        if($validate == "") {
            $rst = 1;
            
            $start = (($request->start == "")?init::defaultStartDate():$request->start);
            $end   = (($request->end == "")?init::defaultEndDate():$request->end);
            
            $table = "tv_content";
            $menu  = $request->menu_id;
            if($request->satker_id != "") {
                $satker = $request->satker_id;
            }
            else {
                $satker = dBase::dbGetFieldById('tm_user', 'satker_id', 'user_id', $user);
            }

            if($satker != "") {

                $arrSatker = Dbase::memberSatker($satker);
                
                if(($request->keyword != "") && ($menu != "")) {
                    $arr = DB::table($table)->whereBetween('content_date', [$start, $end])->whereIn('satker_id', $arrSatker)->where('menu_id', $menu)->where('content_text_in', 'like', '%'.$request->keyword.'%')->take($limit)->skip($offset)->orderBy('content_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('content_date', [$start, $end])->whereIn('satker_id', $arrSatker)->where('menu_id', $menu)->where('content_text_in', 'like', '%'.$request->keyword.'%')->count();
                }
                else if(($request->keyword != "") && ($menu == "")) {
                    $arr = DB::table($table)->whereBetween('content_date', [$start, $end])->whereIn('satker_id', $arrSatker)->where('content_text_in', 'like', '%'.$request->keyword.'%')->take($limit)->skip($offset)->orderBy('content_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('content_date', [$start, $end])->whereIn('satker_id', $arrSatker)->where('content_text_in', 'like', '%'.$request->keyword.'%')->count();
                }
                else if(($request->keyword == "") && ($menu != "")) {
                    $arr = DB::table($table)->whereBetween('content_date', [$start, $end])->whereIn('satker_id', $arrSatker)->where('menu_id', $menu)->take($limit)->skip($offset)->orderBy('content_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('content_date', [$start, $end])->whereIn('satker_id', $arrSatker)->where('menu_id', $menu)->count();
                }
                else {
                    $arr = DB::table($table)->whereBetween('content_date', [$start, $end])->whereIn('satker_id', $arrSatker)->take($limit)->skip($offset)->orderBy('content_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('content_date', [$start, $end])->whereIn('satker_id', $arrSatker)->count();
                }
            }
            else {
                if(($request->keyword != "") && ($menu != "")) {
                    $arr = DB::table($table)->whereBetween('content_date', [$start, $end])->where('menu_id', $menu)->where('content_text_in', 'like', '%'.$request->keyword.'%')->take($limit)->skip($offset)->orderBy('content_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('content_date', [$start, $end])->where('menu_id', $menu)->where('content_text_in', 'like', '%'.$request->keyword.'%')->count();
                }
                else if(($request->keyword != "") && ($menu == "")) {
                    $arr = DB::table($table)->whereBetween('content_date', [$start, $end])->where('content_text_in', 'like', '%'.$request->keyword.'%')->take($limit)->skip($offset)->orderBy('content_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('content_date', [$start, $end])->where('content_text_in', 'like', '%'.$request->keyword.'%')->count();
                }
                else if(($request->keyword == "") && ($menu != "")) {
                    $arr = DB::table($table)->whereBetween('content_date', [$start, $end])->where('menu_id', $menu)->take($limit)->skip($offset)->orderBy('content_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('content_date', [$start, $end])->where('menu_id', $menu)->count();
                }
                else {
                    $arr = DB::table($table)->whereBetween('content_date', [$start, $end])->take($limit)->skip($offset)->orderBy('content_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('content_date', [$start, $end])->count();
                }
            }
        }

        $data = array(
            'total' => $cnt,
            'list'  => Init::initSearchContent(1, $arr),
        );

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getFileManager(Request $request) {
        $user = $request->user_id;
        $type = $request->type;
        
        $limit  = $request->limit;
        $limit  = (($limit == "")?20:$limit);
        $offset = $request->offset;
        $offset = (($offset == "")?0:$offset);

        $validate = Init::initValidate(
            array('type', 'user'), 
            array($type, $user)
        );

        $rst = 0;
        $cnt = 0;
        $arr = array();
        if($validate == "") {
            $rst = 1;

            $table = "tv_upload";
            $satker = dBase::dbGetFieldById('tm_user', 'satker_id', 'user_id', $user);
            
            if($satker != "") {
                if($request->keyword != "") {
                    $arr = DB::table($table)->where('upload_type', $type)->where('satker_id', $satker)->where('upload_name', 'like', '%'.$request->keyword.'%')->take($limit)->skip($offset)->orderBy('upload_id', 'DESC')->get();
                    $cnt = DB::table($table)->where('upload_type', $type)->where('satker_id', $satker)->where('upload_name', 'like', '%'.$request->keyword.'%')->count();
                }
                else {
                    $arr = DB::table($table)->where('upload_type', $type)->where('satker_id', $satker)->take($limit)->skip($offset)->orderBy('upload_id', 'DESC')->get();
                    $cnt = DB::table($table)->where('upload_type', $type)->where('satker_id', $satker)->count();
                }
            }
            else {
                if($request->keyword != "") {
                    $arr = DB::table($table)->where('upload_type', $type)->where('upload_name', 'like', '%'.$request->keyword.'%')->take($limit)->skip($offset)->orderBy('upload_id', 'DESC')->get();
                    $cnt = DB::table($table)->where('upload_type', $type)->where('upload_name', 'like', '%'.$request->keyword.'%')->count();
                }
                else {
                    $arr = DB::table($table)->where('upload_type', $type)->take($limit)->skip($offset)->orderBy('upload_id', 'DESC')->get();
                    $cnt = DB::table($table)->where('upload_type', $type)->count();
                }
            }
        }

        $fix = array();
        foreach($arr as $r) {
            $date = Carbon::createFromFormat('Y-m-d', $r->upload_date)
                        ->format('d-m-Y');
            $time = $r->upload_time;

            $fix[] = array(
                "upload_id"    => $r->upload_id,
                "upload_date"  => $date,
                "upload_time"  => $time,
                "upload_type"  => $r->upload_type,
                "upload_name"  => $r->upload_name,
                "upload_size"  => $r->upload_size,
                "upload_file"  => $r->upload_file,
                "upload_path"  => $r->upload_path,
                "satker_id"    => $r->satker_id,
                "satker_name"  => $r->satker_name,
                "menu_id"      => $r->menu_id,
                "menu_name"    => $r->menu_name,
                "reff_id"      => $r->reff_id,
            );
        }

        $data = array(
            'total' => $cnt,
            'lists' => $fix,
        );

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getVideoEmbed(Request $request) {
        $user = $request->user_id;
        
        $limit  = $request->limit;
        $limit  = (($limit == "")?20:$limit);
        $offset = $request->offset;
        $offset = (($offset == "")?0:$offset);

        $validate = Init::initValidate(
            array('user'), 
            array($user)
        );

        $rst = 0;
        $cnt = 0;
        $arr = array();
        if($validate == "") {
            $rst  = 1;
            $type = 2;

            $table = "tv_upload";
            $satker = dBase::dbGetFieldById('tm_user', 'satker_id', 'user_id', $user);
            
            if($satker != "") {
                if($request->keyword != "") {
                    $arr = DB::table($table)->where('upload_size', 0)->where('upload_type', $type)->where('satker_id', $satker)->where('upload_name', 'like', '%'.$request->keyword.'%')->take($limit)->skip($offset)->orderBy('upload_id', 'DESC')->get();
                    $cnt = DB::table($table)->where('upload_size', 0)->where('upload_type', $type)->where('satker_id', $satker)->where('upload_name', 'like', '%'.$request->keyword.'%')->count();
                }
                else {
                    $arr = DB::table($table)->where('upload_size', 0)->where('upload_type', $type)->where('satker_id', $satker)->take($limit)->skip($offset)->orderBy('upload_id', 'DESC')->get();
                    $cnt = DB::table($table)->where('upload_size', 0)->where('upload_type', $type)->where('satker_id', $satker)->count();
                }
            }
            else {
                if($request->keyword != "") {
                    $arr = DB::table($table)->where('upload_size', 0)->where('upload_type', $type)->where('upload_name', 'like', '%'.$request->keyword.'%')->take($limit)->skip($offset)->orderBy('upload_id', 'DESC')->get();
                    $cnt = DB::table($table)->where('upload_size', 0)->where('upload_type', $type)->where('upload_name', 'like', '%'.$request->keyword.'%')->count();
                }
                else {
                    $arr = DB::table($table)->where('upload_size', 0)->where('upload_type', $type)->take($limit)->skip($offset)->orderBy('upload_id', 'DESC')->get();
                    $cnt = DB::table($table)->where('upload_size', 0)->where('upload_type', $type)->count();
                }
            }
        }

        $fix = array();
        foreach($arr as $r) {
            $date = Carbon::createFromFormat('Y-m-d', $r->upload_date)
                        ->format('d-m-Y');
            $time = $r->upload_time;

            $fix[] = array(
                "upload_id"    => $r->upload_id,
                "upload_date"  => $date,
                "upload_time"  => $time,
                "upload_type"  => $r->upload_type,
                "upload_name"  => $r->upload_name,
                "upload_size"  => $r->upload_size,
                "upload_file"  => $r->upload_file,
                "upload_path"  => $r->upload_path,
                "satker_id"    => $r->satker_id,
                "satker_name"  => $r->satker_name,
                "menu_id"      => $r->menu_id,
                "menu_name"    => $r->menu_name,
                "reff_id"      => $r->reff_id,
            );
        }

        $data = array(
            'total' => $cnt,
            'lists' => $fix,
        );

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getIntegrasiJdih(Request $request) {
        $page  = $request->page;
        $page  = (($page == "")?1:$page);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://jdih.kejaksaan.go.id/api/apiwcms.php?page='.$page,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'x-access-token: 38bf8bd945abb3df355a6d87c8a09102fa8076c076e5f917bac771407ff726b0',
                'Cookie: PHPSESSID=90835468c22ce97ccdb53e24b80ebc47; _csrf=46bbea38a53a69f494ea6191b3b17a7cf2c9df7134c51556b7c2149a4913336ca%3A2%3A%7Bi%3A0%3Bs%3A5%3A%22_csrf%22%3Bi%3A1%3Bs%3A32%3A%22hiHyS0fffFHezVu4-w0Dv8GDPMGCykoW%22%3B%7D'
            ),
        ));

        $response = curl_exec($curl);
        $resp = json_decode($response); 
        
        $data = array(
            'total' => $resp->total,
            'lists' => $resp->data,
        );

        return response()->json([
            'status'    => Init::responseStatus(1),
            'message'   => Init::responseMessage(1, 'View'),
            'data'      => $data],
            200
        );
    }


    public function setModule(Request $request) {
        $id       = $request->module_id;
        $position = $request->position;
        
        $validate = Init::initValidate(
            array('id', 'position'), 
            array($id, $position)
        );

        $rst = 1;
        if($validate == "") {
            $table = "tm_module";
            $list = DB::table($table)->where('module_position', '>=', $position)->where('module_parent', 0)->where('module_status', 1)->where('is_deleted', 0)->orderBy('module_position')->get();
            if($list != "[]") {
                $current = $position;
                foreach($list as $row) {
                    $module  = $row->module_id;
                    $current = $current + 1;
                    
                    dBase::dbSetFieldById($table, 'module_position', $current, 'module_id', $module);
                }
            } 

            $rst = dBase::dbSetFieldById($table, 'module_position', $position, 'module_id', $id);
        }
         
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'Update')),
            'data'      => array()],
            200
        );
    }

    public function getVisitor(Request $request) {
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
            
            $table = "tr_visitor";
            if($request->satker_id != "") {
                $satker = $request->satker_id;
            }
            else {
                $satker = dBase::dbGetFieldById('tm_user', 'satker_id', 'user_id', $user);
            }
            
            $cnt = 0;
            $tmp = array();
            if($satker != "") {
                $arrSatker = Dbase::memberSatker($satker);

                if($request->ip != "") {
                    $tmp = DB::table($table)->whereBetween('visitor_date', [$start, $end])->whereIn('satker_id', $arrSatker)->where('visitor_ip', 'like', '%'.$request->ip.'%')->take($limit)->skip($offset)->orderBy('visitor_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('visitor_date', [$start, $end])->whereIn('satker_id', $arrSatker)->where('visitor_ip', 'like', '%'.$request->ip.'%')->count();
                }
                else {
                    $tmp = DB::table($table)->whereBetween('visitor_date', [$start, $end])->whereIn('satker_id', $arrSatker)->take($limit)->skip($offset)->orderBy('visitor_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('visitor_date', [$start, $end])->whereIn('satker_id', $arrSatker)->count();
                }
            }
            else {
                if($request->ip != "") {
                    $tmp = DB::table($table)->whereBetween('visitor_date', [$start, $end])->where('visitor_ip', 'like', '%'.$request->ip.'%')->take($limit)->skip($offset)->orderBy('visitor_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('visitor_date', [$start, $end])->where('visitor_ip', 'like', '%'.$request->ip.'%')->count();
                }
                else {
                    $tmp = DB::table($table)->whereBetween('visitor_date', [$start, $end])->take($limit)->skip($offset)->orderBy('visitor_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('visitor_date', [$start, $end])->count();
                }
            }

            $arr = Init::initVisitor(1, $tmp);
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

    public function getRating(Request $request) {
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
            
            $table = "tp_rating";
            if($request->satker_id != "") {
                $satker = $request->satker_id;
            }
            else {
                $satker = dBase::dbGetFieldById('tm_user', 'satker_id', 'user_id', $user);
            }
            
            $cnt = 0;
            $tmp = array();
            if($satker != "") {
                $arrSatker = Dbase::memberSatker($satker);

                if($request->ip != "") {
                    $tmp = DB::table($table)->whereBetween('rating_date', [$start, $end])->whereIn('satker_id', $arrSatker)->where('rating_ip', 'like', '%'.$request->ip.'%')->take($limit)->skip($offset)->orderBy('rating_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('rating_date', [$start, $end])->whereIn('satker_id', $arrSatker)->where('rating_ip', 'like', '%'.$request->ip.'%')->count();
                }
                else {
                    $tmp = DB::table($table)->whereBetween('rating_date', [$start, $end])->whereIn('satker_id', $arrSatker)->take($limit)->skip($offset)->orderBy('rating_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('rating_date', [$start, $end])->whereIn('satker_id', $arrSatker)->count();
                }
            }
            else {
                if($request->ip != "") {
                    $tmp = DB::table($table)->whereBetween('rating_date', [$start, $end])->where('rating_ip', 'like', '%'.$request->ip.'%')->take($limit)->skip($offset)->orderBy('rating_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('rating_date', [$start, $end])->where('rating_ip', 'like', '%'.$request->ip.'%')->count();
                }
                else {
                    $tmp = DB::table($table)->whereBetween('rating_date', [$start, $end])->take($limit)->skip($offset)->orderBy('rating_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('rating_date', [$start, $end])->count();
                }
            }

            $arr = Init::initRating(1, $tmp);
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

    public function getContactus(Request $request) {
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
            
            $table = "tp_contactus";
            if($request->satker_id != "") {
                $satker = $request->satker_id;
            }
            else {
                $satker = dBase::dbGetFieldById('tm_user', 'satker_id', 'user_id', $user);
            }
            
            $cnt = 0;
            $tmp = array();
            if($satker != "") {
                $arrSatker = Dbase::memberSatker($satker);

                if($request->name != "") {
                    $tmp = DB::table($table)->whereBetween('contactus_date', [$start, $end])->whereIn('satker_id', $arrSatker)->where('contactus_name', 'like', '%'.$request->name.'%')->take($limit)->skip($offset)->orderBy('contactus_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('contactus_date', [$start, $end])->whereIn('satker_id', $arrSatker)->where('contactus_name', 'like', '%'.$request->name.'%')->count();
                }
                else {
                    $tmp = DB::table($table)->whereBetween('contactus_date', [$start, $end])->whereIn('satker_id', $arrSatker)->take($limit)->skip($offset)->orderBy('contactus_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('contactus_date', [$start, $end])->whereIn('satker_id', $arrSatker)->count();
                }
            }
            else {
                if($request->name != "") {
                    $tmp = DB::table($table)->whereBetween('contactus_date', [$start, $end])->where('contactus_name', 'like', '%'.$request->name.'%')->take($limit)->skip($offset)->orderBy('contactus_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('contactus_date', [$start, $end])->where('contactus_name', 'like', '%'.$request->name.'%')->count();
                }
                else {
                    $tmp = DB::table($table)->whereBetween('contactus_date', [$start, $end])->take($limit)->skip($offset)->orderBy('contactus_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('contactus_date', [$start, $end])->count();
                }
            }

            $arr = Init::initContactus(1, $tmp);
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

    public function getNewsletter(Request $request) {
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
            
            $table = "tp_newsletter";
            if($request->satker_id != "") {
                $satker = $request->satker_id;
            }
            else {
                $satker = dBase::dbGetFieldById('tm_user', 'satker_id', 'user_id', $user);
            }
            
            $cnt = 0;
            $tmp = array();
            if($satker != "") {
                $arrSatker = Dbase::memberSatker($satker);

                if($request->email != "") {
                    $tmp = DB::table($table)->whereBetween('newsletter_date', [$start, $end])->whereIn('satker_id', $arrSatker)->where('newsletter_email', 'like', '%'.$request->email.'%')->take($limit)->skip($offset)->orderBy('newsletter_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('newsletter_date', [$start, $end])->whereIn('satker_id', $arrSatker)->where('newsletter_email', 'like', '%'.$request->email.'%')->count();
                }
                else {
                    $tmp = DB::table($table)->whereBetween('newsletter_date', [$start, $end])->whereIn('satker_id', $arrSatker)->take($limit)->skip($offset)->orderBy('newsletter_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('newsletter_date', [$start, $end])->whereIn('satker_id', $arrSatker)->count();
                }
            }
            else {
                if($request->email != "") {
                    $tmp = DB::table($table)->whereBetween('newsletter_date', [$start, $end])->where('newsletter_email', 'like', '%'.$request->email.'%')->take($limit)->skip($offset)->orderBy('newsletter_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('newsletter_date', [$start, $end])->where('newsletter_email', 'like', '%'.$request->email.'%')->count();
                }
                else {
                    $tmp = DB::table($table)->whereBetween('newsletter_date', [$start, $end])->take($limit)->skip($offset)->orderBy('newsletter_id', 'DESC')->get();
                    $cnt = DB::table($table)->whereBetween('newsletter_date', [$start, $end])->count();
                }
            }

            $arr = Init::initNewsletter(1, $tmp);
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

    public function getArticle(Request $request) {
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

            $table = "tp_news";
            if($request->satker_id != "") {
                $satker = $request->satker_id;
            }
            else {
                $satker = dBase::dbGetFieldById('tm_user', 'satker_id', 'user_id', $user);
            }
            
            $cnt = 0;
            $tmp = array();
            if($satker != "") {
                $arrSatker = Dbase::memberSatker($satker);

                if($request->status != "") {
                    if(($request->title != "") && ($request->category != "")) {
                        $tmp = DB::table($table)->where('news_status', $request->status)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->whereIn('satker_id', $arrSatker)->where('news_title', 'like', '%'.$request->title.'%')->where('news_category', $request->category)->take($limit)->skip($offset)->orderBy('news_date', 'DESC')->get();
                        $cnt = DB::table($table)->where('news_status', $request->status)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->whereIn('satker_id', $arrSatker)->where('news_title', 'like', '%'.$request->title.'%')->where('news_category', $request->category)->count();
                    }
                    else if(($request->title != "") && ($request->category == "")) {
                        $tmp = DB::table($table)->where('news_status', $request->status)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->whereIn('satker_id', $arrSatker)->where('news_title', 'like', '%'.$request->title.'%')->take($limit)->skip($offset)->orderBy('news_date', 'DESC')->get();
                        $cnt = DB::table($table)->where('news_status', $request->status)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->whereIn('satker_id', $arrSatker)->where('news_title', 'like', '%'.$request->title.'%')->count();
                    }
                    else if(($request->title == "") && ($request->category != "")) {
                        $tmp = DB::table($table)->where('news_status', $request->status)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->whereIn('satker_id', $arrSatker)->where('news_category', $request->category)->take($limit)->skip($offset)->orderBy('news_date', 'DESC')->get();
                        $cnt = DB::table($table)->where('news_status', $request->status)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->whereIn('satker_id', $arrSatker)->where('news_category', $request->category)->count();
                    }
                    else {
                        $tmp = DB::table($table)->where('news_status', $request->status)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->whereIn('satker_id', $arrSatker)->take($limit)->skip($offset)->orderBy('news_date', 'DESC')->get();
                        $cnt = DB::table($table)->where('news_status', $request->status)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->whereIn('satker_id', $arrSatker)->count();
                    }
                }
                else {
                    if(($request->title != "") && ($request->category != "")) {
                        $tmp = DB::table($table)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->whereIn('satker_id', $arrSatker)->where('news_title', 'like', '%'.$request->title.'%')->where('news_category', $request->category)->take($limit)->skip($offset)->orderBy('news_date', 'DESC')->get();
                        $cnt = DB::table($table)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->whereIn('satker_id', $arrSatker)->where('news_title', 'like', '%'.$request->title.'%')->where('news_category', $request->category)->count();
                    }
                    else if(($request->title != "") && ($request->category == "")) {
                        $tmp = DB::table($table)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->whereIn('satker_id', $arrSatker)->where('news_title', 'like', '%'.$request->title.'%')->take($limit)->skip($offset)->orderBy('news_date', 'DESC')->get();
                        $cnt = DB::table($table)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->whereIn('satker_id', $arrSatker)->where('news_title', 'like', '%'.$request->title.'%')->count();
                    }
                    else if(($request->title == "") && ($request->category != "")) {
                        $tmp = DB::table($table)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->whereIn('satker_id', $arrSatker)->where('news_category', $request->category)->take($limit)->skip($offset)->orderBy('news_date', 'DESC')->get();
                        $cnt = DB::table($table)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->whereIn('satker_id', $arrSatker)->where('news_category', $request->category)->count();
                    }
                    else {
                        $tmp = DB::table($table)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->whereIn('satker_id', $arrSatker)->take($limit)->skip($offset)->orderBy('news_date', 'DESC')->get();
                        $cnt = DB::table($table)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->whereIn('satker_id', $arrSatker)->count();
                    }
                }
            }
            else {
                if($request->status != "") {
                    if(($request->title != "") && ($request->category != "")) {
                        $tmp = DB::table($table)->where('news_status', $request->status)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->where('news_title', 'like', '%'.$request->title.'%')->where('news_category', $request->category)->take($limit)->skip($offset)->orderBy('news_date', 'DESC')->get();
                        $cnt = DB::table($table)->where('news_status', $request->status)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->where('news_title', 'like', '%'.$request->title.'%')->where('news_category', $request->category)->count();
                    }
                    else if(($request->title != "") && ($request->category == "")) {
                        $tmp = DB::table($table)->where('news_status', $request->status)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->where('news_title', 'like', '%'.$request->title.'%')->take($limit)->skip($offset)->orderBy('news_date', 'DESC')->get();
                        $cnt = DB::table($table)->where('news_status', $request->status)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->where('news_title', 'like', '%'.$request->title.'%')->count();
                    }
                    else if(($request->title == "") && ($request->category != "")) {
                        $tmp = DB::table($table)->where('news_status', $request->status)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->where('news_category', $request->category)->take($limit)->skip($offset)->orderBy('news_date', 'DESC')->get();
                        $cnt = DB::table($table)->where('news_status', $request->status)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->where('news_category', $request->category)->count();
                    }
                    else {
                        $tmp = DB::table($table)->where('news_status', $request->status)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->take($limit)->skip($offset)->orderBy('news_date', 'DESC')->get();
                        $cnt = DB::table($table)->where('news_status', $request->status)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->count();
                    }
                }
                else {
                    if(($request->title != "") && ($request->category != "")) {
                        $tmp = DB::table($table)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->where('news_title', 'like', '%'.$request->title.'%')->where('news_category', $request->category)->take($limit)->skip($offset)->orderBy('news_date', 'DESC')->get();
                        $cnt = DB::table($table)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->where('news_title', 'like', '%'.$request->title.'%')->where('news_category', $request->category)->count();
                    }
                    else if(($request->title != "") && ($request->category == "")) {
                        $tmp = DB::table($table)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->where('news_title', 'like', '%'.$request->title.'%')->take($limit)->skip($offset)->orderBy('news_date', 'DESC')->get();
                        $cnt = DB::table($table)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->where('news_title', 'like', '%'.$request->title.'%')->count();
                    }
                    else if(($request->title == "") && ($request->category != "")) {
                        $tmp = DB::table($table)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->where('news_category', $request->category)->take($limit)->skip($offset)->orderBy('news_date', 'DESC')->get();
                        $cnt = DB::table($table)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->where('news_category', $request->category)->count();
                    }
                    else {
                        $tmp = DB::table($table)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->take($limit)->skip($offset)->orderBy('news_date', 'DESC')->get();
                        $cnt = DB::table($table)->where('is_deleted', 0)->whereBetween('news_date', [$start, $end])->count();
                    }
                }
            }

            $arr = Init::initConferenceNews(1, $tmp);
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

    public function getChartLine(Request $request) {
        $user = $request->user_id;
        $validate = Init::initValidate(
            array('user'), 
            array($user)
        );

        $arr = array();
        if($validate == "") {
            $rst = 1;
        
            $now = Carbon::now();
            $currYear  = Carbon::createFromFormat('Y-m-d H:i:s', $now)->format('Y');
            if($request->month == "") {
                $currMonth = Carbon::createFromFormat('Y-m-d H:i:s', $now)->format('m');
            }
            else {
                $currMonth = $request->month;
            }
            
            $currDay   = Carbon::createFromFormat('Y-m-d H:i:s', $now)->format('d');
            $myDate = $currYear .'-'. $currMonth .'-'. $currDay;
            
            $startOfMonth = Carbon::createFromFormat('Y-m-d', $myDate)
                ->firstOfMonth()
                ->format('d');

            $endOfMonth = Carbon::createFromFormat('Y-m-d', $myDate)
                ->endOfMonth()
                ->format('d');            
        
            $satker = dBase::dbGetFieldById('tm_user', 'satker_id', 'user_id', $user);
            for($i=$startOfMonth; $i <= $endOfMonth; $i++) {
                $dateOf    = $currYear .'-'. $currMonth .'-'. $i;
                $dateMonth = Carbon::createFromFormat('Y-m-d', $dateOf)->format('d-m-Y'); 

                if($satker != "") {
                    $count = dBase::dbGetCountByTwoId('tr_visitor', 'visitor_date', $dateOf, 'satker_id', $satker);
                }
                else {
                    $count = dBase::dbGetCountById('tr_visitor', 'visitor_date', $dateOf);
                }
                
                $arr[] = array(
                    'day'   => intval($i),
                    'title' => $dateMonth,
                    'count' => $count
                );
            }
        } 
        else {
            $rst = 0;
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => Init::responseMessage($rst, 'View'),
            'data'      => $arr],
            200
        );
    }

    public function getVisitorByDay(Request $request) {
        $user = $request->user_id;
        $validate = Init::initValidate(
            array('user'), 
            array($user)
        );

        $arr = array();
        if($validate == "") {
            $rst = 1;
        
            if($request->day == "") {
                $now = Carbon::now();
                $dateOf = Carbon::createFromFormat('Y-m-d H:i:s', $now)->format('Y-m-d');
            }
            else {
                $dateOf = $request->day;
            }

            $satker = dBase::dbGetFieldById('tm_user', 'satker_id', 'user_id', $user);
            for($i=0; $i <= 23; $i++) {
                $hour = ($i<10)?"0".$i:$i;
                if($satker != "") {
                    $count = DB::table('tr_visitor')
                        ->where('visitor_date', '=', $dateOf)
                        ->where('visitor_time', 'like', $hour.'%')
                        ->where('satker_id', '=', $satker)
                        ->count();
                }
                else {
                    $count = DB::table('tr_visitor')
                        ->where('visitor_date', '=', $dateOf)
                        ->where('visitor_time', 'like', $hour.'%')
                        ->count();
                }
                
                $arr[] = array(
                    'sort'  => $i,
                    'title' => strval($hour) .':00 s/d ' . strval($hour) .':59',
                    'count' => $count
                );
            }
        } 
        else {
            $rst = 0;
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => Init::responseMessage($rst, 'View'),
            'data'      => $arr],
            200
        );
    }

    public function getVisitorByMonth(Request $request) {
        $user = $request->user_id;
        $validate = Init::initValidate(
            array('user'), 
            array($user)
        );

        $arr = array();
        if($validate == "") {
            $rst = 1;
        
            $now = Carbon::now();
            $currYear  = Carbon::createFromFormat('Y-m-d H:i:s', $now)->format('Y');
            if($request->month == "") {
                $currMonth = Carbon::createFromFormat('Y-m-d H:i:s', $now)->format('m');
            }
            else {
                $tempMonth = $request->month ."-01";
                $currMonth = Carbon::createFromFormat('Y-m-d', $tempMonth)->format('m');
            }
            
            $currDay   = Carbon::createFromFormat('Y-m-d H:i:s', $now)->format('d');
            $myDate = $currYear .'-'. $currMonth .'-'. $currDay;
            
            $startOfMonth = Carbon::createFromFormat('Y-m-d', $myDate)
                ->firstOfMonth()
                ->format('d');

            $endOfMonth = Carbon::createFromFormat('Y-m-d', $myDate)
                ->endOfMonth()
                ->format('d'); 

            $satker = dBase::dbGetFieldById('tm_user', 'satker_id', 'user_id', $user);
            for($i=$startOfMonth; $i <= $endOfMonth; $i++) {
                if($i != "01") {
                    $date = ($i<10)?"0".$i:$i;
                }
                else {
                    $date = $i;
                }

                $dateOf    = $currYear .'-'. $currMonth .'-'. $i;
                
                if($satker != "") {
                    $count = dBase::dbGetCountByTwoId('tr_visitor', 'visitor_date', $dateOf, 'satker_id', $satker);
                }
                else {
                    $count = dBase::dbGetCountById('tr_visitor', 'visitor_date', $dateOf);
                }
                
                $arr[] = array(
                    'sort'  => $i,
                    'title' => strval($date),
                    'count' => $count
                );
            }
        } 
        else {
            $rst = 0;
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => Init::responseMessage($rst, 'View'),
            'data'      => $arr],
            200
        );
    }

    public function getVisitorByYear(Request $request) {
        $user = $request->user_id;
        $validate = Init::initValidate(
            array('user'), 
            array($user)
        );

        $arr = array();
        if($validate == "") {
            $rst = 1;
        
            if($request->year == "") {
                $now = Carbon::now();
                $year  = Carbon::createFromFormat('Y-m-d H:i:s', $now)->format('Y');
            }
            else {
                $year = $request->year;
            }
            
            $satker = dBase::dbGetFieldById('tm_user', 'satker_id', 'user_id', $user);
            for($i=1; $i <= 12; $i++) {
                $month = ($i<10)?"0".$i:$i;
                $tempDate = $year.'-'.$month;
                $monthName = Status::monthName($month);
                
                if($satker != "") {
                    $results = DB::select("SELECT COUNT(*) as total FROM `tr_visitor` WHERE `visitor_date` LIKE '". $tempDate ."%' AND `satker_id` = ". $satker .";");
                }
                else {
                    $results = DB::select("SELECT COUNT(*) as total FROM `tr_visitor` WHERE `visitor_date` LIKE '". $tempDate ."%';");
                }
                
                $count = $results[0]->total;
                $arr[] = array(
                    'sort'  => $i,
                    'title' => $monthName,
                    'count' => $count
                );
            }
        } 
        else {
            $rst = 0;
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => Init::responseMessage($rst, 'View'),
            'data'      => $arr],
            200
        );
    }

    public function getVisitorBySatker(Request $request) {
        $user = $request->user_id;
        $validate = Init::initValidate(
            array('user'), 
            array($user)
        );

        $arr = array();
        if($validate == "") {
            $rst = 1;
        
            $satker = dBase::dbGetFieldById('tm_user', 'satker_id', 'user_id', $user);
            if($satker != "") {
                if($satker == 100) {
                    $level0 = array();
                    $arrKejagung = DB::select("SELECT `satker_id`, `satker_code`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '0' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                    foreach($arrKejagung as $rKejagung) {
                        
                        $level1 = array();
                        $arrKejati = DB::select("SELECT `satker_id`, `satker_code`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '1' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                        
                        foreach($arrKejati as $rKejati) {
                            $level2 = array();
                            $tempCode  = $rKejati->satker_code;
                            $arrKejari = DB::select("SELECT `satker_id`, `satker_code`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '2' AND `satker_code` LIKE '". $tempCode ."%' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                            
                            foreach($arrKejari as $rKejari) {
                                $level3 = array();
                                $tempCode  = $rKejari->satker_code;

                                $arrCabjari = DB::select("SELECT `satker_id`, `satker_code`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '3' AND `satker_code` LIKE '". $tempCode ."%' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                                foreach($arrCabjari as $rCabjari) {
                                    $level3[] = array(
                                        'name'   => $rCabjari->satker_name,
                                        'count'  => dBase::dbGetCountById('tr_visitor', 'satker_id', $rCabjari->satker_id),
                                    );
                                }

                                $level2[] = array(
                                    'name'   => $rKejari->satker_name,
                                    'count'  => dBase::dbGetCountById('tr_visitor', 'satker_id', $rKejari->satker_id),
                                    'level3' => $level3,
                                );
                            }
                            
                            $level1[] = array(
                                'name'   => $rKejati->satker_name,
                                'count'  => dBase::dbGetCountById('tr_visitor', 'satker_id', $rKejati->satker_id),
                                'level2' => $level2,
                            );
                        }

                        $level0[] = array(
                            'name'   => $rKejagung->satker_name,
                            'count'  => dBase::dbGetCountById('tr_visitor', 'satker_id', $rKejagung->satker_id),
                            'level1' => $level1
                        );
                    }
    
                    $arrBadiklat = DB::select("SELECT `satker_id`, `satker_code`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '4' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                    foreach($arrBadiklat as $rBadiklat) {
                        $level0[] = array(
                            'name'   => $rBadiklat->satker_name,
                            'count'  => dBase::dbGetCountById('tr_visitor', 'satker_id', $rBadiklat->satker_id),
                            'level1' => array()
                        );
                    }

                    $arr = array(
                        'title'  => 'Total Keseluruhan',
                        'count'  => dBase::dbGetCount('tr_visitor'),
                        'level0' => $level0
                    );
                }
                else {
                    $satker_type = dBase::dbGetFieldById('tm_satker', 'satker_type', 'satker_id', $satker);
                    if($satker_type == 1) {
                        $level0 = array();
                        $arrKejati = DB::select("SELECT `satker_id`, `satker_code`, `satker_name` FROM `tm_satker` WHERE `satker_id` = '". $satker ."'");
                        foreach($arrKejati as $rKejati) {
                            $level1 = array();
                            $tempCode  = $rKejati->satker_code;
                            $arrKejari = DB::select("SELECT `satker_id`, `satker_code`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '2' AND `satker_code` LIKE '". $tempCode ."%' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                            
                            foreach($arrKejari as $rKejari) {
                                $level2 = array();
                                $tempCode  = $rKejari->satker_code;
    
                                $arrCabjari = DB::select("SELECT `satker_id`, `satker_code`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '3' AND `satker_code` LIKE '". $tempCode ."%' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                                foreach($arrCabjari as $rCabjari) {
                                    $level2[] = array(
                                        'name'   => $rCabjari->satker_name,
                                        'count'  => dBase::dbGetCountById('tr_visitor', 'satker_id', $rCabjari->satker_id),
                                        'level3' => array(),
                                    );
                                }
    
                                $level1[] = array(
                                    'name'   => $rKejari->satker_name,
                                    'count'  => dBase::dbGetCountById('tr_visitor', 'satker_id', $rKejari->satker_id),
                                    'level2' => $level2,
                                );
                            }

                            $level0[] = array(
                                'name'   => $rKejati->satker_name,
                                'count'  => dBase::dbGetCountById('tr_visitor', 'satker_id', $rKejati->satker_id),
                                'level1' => $level1
                            );
                        }

                        $arr = array(
                            'title'  => 'Total Keseluruhan',
                            'count'  => dBase::dbGetCount('tr_visitor'),
                            'level0' => $level0
                        );
                    }
                    else if($satker_type == 2) {
                        $level0 = array();
                        $arrKejari = DB::select("SELECT `satker_id`, `satker_code`, `satker_name` FROM `tm_satker` WHERE `satker_id` = '". $satker ."'");
                        
                        foreach($arrKejari as $rKejari) {
                            $level1 = array();
                            $tempCode  = $rKejari->satker_code;
                            $arrCabjari = DB::select("SELECT `satker_id`, `satker_code`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '3' AND `satker_code` LIKE '". $tempCode ."%' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                            
                            foreach($arrCabjari as $rCabjari) {
                                $level1[] = array(
                                    'name'   => $rCabjari->satker_name,
                                    'count'  => dBase::dbGetCountById('tr_visitor', 'satker_id', $rCabjari->satker_id),
                                    'level2' => array(),
                                );
                            }

                            $level0[] = array(
                                'name'   => $rKejari->satker_name,
                                'count'  => dBase::dbGetCountById('tr_visitor', 'satker_id', $rKejari->satker_id),
                                'level1' => $level1
                            );
                        }

                        $arr = array(
                            'title'  => 'Total Keseluruhan',
                            'count'  => dBase::dbGetCount('tr_visitor'),
                            'level0' => $level0
                        );
                    }
                    else if($satker_type == 3) {
                        $level0 = array();
                        $arrCabjari = DB::select("SELECT `satker_id`, `satker_code`, `satker_name` FROM `tm_satker` WHERE `satker_id` = '". $satker ."'");
                        
                        foreach($arrCabjari as $rCabjari) {
                            $level0[] = array(
                                'name'   => $rCabjari->satker_name,
                                'count'  => dBase::dbGetCountById('tr_visitor', 'satker_id', $rCabjari->satker_id),
                                'level1' => array(),
                            );
                        }

                        $arr = array(
                            'title'  => 'Total Keseluruhan',
                            'count'  => dBase::dbGetCount('tr_visitor'),
                            'level0' => $level0
                        );
                    }
                    else {
                        $arrBadiklat = DB::select("SELECT `satker_id`, `satker_code`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '4' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                        foreach($arrBadiklat as $rBadiklat) {
                            $level0[] = array(
                                'name'   => $rBadiklat->satker_name,
                                'count'  => dBase::dbGetCountById('tr_visitor', 'satker_id', $rBadiklat->satker_id),
                                'level1' => array()
                            );
                        }

                        $arr = array(
                            'title'  => 'Total Keseluruhan',
                            'count'  => dBase::dbGetCount('tr_visitor'),
                            'level0' => $level0
                        );
                    }
                }
            }
            else {
                $level0 = array();
                $arrKejagung = DB::select("SELECT `satker_id`, `satker_code`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '0' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                foreach($arrKejagung as $rKejagung) {
                    
                    $level1 = array();
                    $arrKejati = DB::select("SELECT `satker_id`, `satker_code`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '1' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                    
                    foreach($arrKejati as $rKejati) {
                        $level2 = array();
                        $tempCode  = $rKejati->satker_code;
                        $arrKejari = DB::select("SELECT `satker_id`, `satker_code`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '2' AND `satker_code` LIKE '". $tempCode ."%' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                        
                        foreach($arrKejari as $rKejari) {
                            $level3 = array();
                            $tempCode  = $rKejari->satker_code;

                            $arrCabjari = DB::select("SELECT `satker_id`, `satker_code`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '3' AND `satker_code` LIKE '". $tempCode ."%' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                            foreach($arrCabjari as $rCabjari) {
                                $level3[] = array(
                                    'name'   => $rCabjari->satker_name,
                                    'count'  => dBase::dbGetCountById('tr_visitor', 'satker_id', $rCabjari->satker_id),
                                );
                            }

                            $level2[] = array(
                                'name'   => $rKejari->satker_name,
                                'count'  => dBase::dbGetCountById('tr_visitor', 'satker_id', $rKejari->satker_id),
                                'level3' => $level3,
                            );
                        }
                        
                        $level1[] = array(
                            'name'   => $rKejati->satker_name,
                            'count'  => dBase::dbGetCountById('tr_visitor', 'satker_id', $rKejati->satker_id),
                            'level2' => $level2,
                        );
                    }

                    $level0[] = array(
                        'name'   => $rKejagung->satker_name,
                        'count'  => dBase::dbGetCountById('tr_visitor', 'satker_id', $rKejagung->satker_id),
                        'level1' => $level1
                    );
                }
 
                $arrBadiklat = DB::select("SELECT `satker_id`, `satker_code`, `satker_name` FROM `tm_satker` WHERE `satker_type` = '4' AND `satker_status` = '1' AND `satker_code` IS NOT NULL;");
                foreach($arrBadiklat as $rBadiklat) {
                    $level0[] = array(
                        'name'   => $rBadiklat->satker_name,
                        'count'  => dBase::dbGetCountById('tr_visitor', 'satker_id', $rBadiklat->satker_id),
                        'level1' => array()
                    );
                }

                $arr = array(
                    'title'  => 'Total Keseluruhan',
                    'count'  => dBase::dbGetCount('tr_visitor'),
                    'level0' => $level0
                );
            }
        } 
        else {
            $rst = 0;
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => Init::responseMessage($rst, 'View'),
            'data'      => $arr],
            200
        );
    }


    public function getSummary(Request $request) {
        $satker = $request->satker_id;
        
        $arr = array();
        $table = "tr_visitor";
        $arrSatker = Dbase::memberSatker($satker, 1);
        
        for($i=0; $i<count($arrSatker); $i++) {
            $satker_id = $arrSatker[$i]['satker_id'];
            $count = Dbase::dbGetCountById($table, 'satker_id', $satker_id);   
            
            $arr[] = array(
                'sort'  => $i,
                'title' => $arrSatker[$i]['satker_name'],
                'count' => $count
            );
        }
        
        return response()->json([
            'status'    => Init::responseStatus(1),
            'message'   => Init::responseMessage(1, 'View'),
            'data'      => $arr],
            200
        );
    }

    public function getAnnually(Request $request) {
        $satker = $request->satker_id;
        $year   = $request->year;

        $validate = Init::initValidate(
            array('tahun'), 
            array($year)
        );

        if($validate == "") {
            $rst = 1;

            $arr = array();
            $table = "tr_visitor";
            for($i=1; $i <= 12; $i++) {
                $month = ($i<10)?"0".$i:$i;
                $tempDate = $year.'-'.$month;
                $monthName = Status::monthName($month);
                
                if($satker != "") {
                    $results = DB::select("SELECT COUNT(*) as total FROM `tr_visitor` WHERE `visitor_date` LIKE '". $tempDate ."%' AND `satker_id` = ". $satker .";");
                }
                else {
                    $results = DB::select("SELECT COUNT(*) as total FROM `tr_visitor` WHERE `visitor_date` LIKE '". $tempDate ."%';");
                }
                
                $count = $results[0]->total;
                $arr[] = array(
                    'sort'  => intval($i),
                    'title' => $monthName,
                    'count' => $count
                );
            }
            
            return response()->json([
                'status'    => Init::responseStatus($rst),
                'message'   => Init::responseMessage($rst, 'View'),
                'data'      => $arr],
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

    public function getMonthly(Request $request) {
        $satker = $request->satker_id;
        $year   = $request->year;
        $month  = $request->month;

        $validate = Init::initValidate(
            array('tahun', 'bulan'), 
            array($year, $month)
        );

        if($validate == "") {
            $rst = 1;

            $arr = array();
            $table = "tr_visitor";
            
            $myDate = $year .'-'. $month .'-01';
            $startOfMonth = Carbon::createFromFormat('Y-m-d', $myDate)
                ->firstOfMonth()
                ->format('d');

            $endOfMonth = Carbon::createFromFormat('Y-m-d', $myDate)
                ->endOfMonth()
                ->format('d'); 

            for($i=$startOfMonth; $i <= $endOfMonth; $i++) {
                if($i != "01") {
                    $date = ($i<10)?"0".$i:$i;
                }
                else {
                    $date = $i;
                }

                $dateOf    = $year .'-'. $month .'-'. $date;
                if($satker != "") {
                    $count = dBase::dbGetCountByTwoId('tr_visitor', 'visitor_date', $dateOf, 'satker_id', $satker);
                }
                else {
                    $count = dBase::dbGetCountById('tr_visitor', 'visitor_date', $dateOf);
                }
                
                $arr[] = array(
                    'sort'  => intval($i),
                    'title' => strval($date),
                    'count' => $count
                );
            }
            
            return response()->json([
                'status'    => Init::responseStatus($rst),
                'message'   => Init::responseMessage($rst, 'View'),
                'data'      => $arr],
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

    public function getDaily(Request $request) {
        $satker = $request->satker_id;
        $year   = $request->year;
        $month  = $request->month;
        $day    = $request->day;

        $validate = Init::initValidate(
            array('tahun', 'bulan', 'tanggal'), 
            array($year, $month, $day)
        );

        if($validate == "") {
            $rst = 1;

            $arr = array();
            $table = "tr_visitor";
            $day = ((intval($day)<10)?"0".intval($day):$day);
            $dateOf = $year .'-'. $month .'-'. $day;

            for($i=0; $i <= 23; $i++) {
                $hour = ($i<10)?"0".$i:$i;
                if($satker != "") {
                    $count = DB::table('tr_visitor')
                        ->where('visitor_date', '=', $dateOf)
                        ->where('visitor_time', 'like', $hour.'%')
                        ->where('satker_id', '=', $satker)
                        ->count();
                }
                else {
                    $count = DB::table('tr_visitor')
                        ->where('visitor_date', '=', $dateOf)
                        ->where('visitor_time', 'like', $hour.'%')
                        ->count();
                }
                
                $arr[] = array(
                    'sort'  => intval($i),
                    'title' => strval($hour) .':00 s/d ' . strval($hour) .':59',
                    'count' => $count
                );
            }
            
            return response()->json([
                'status'    => Init::responseStatus($rst),
                'message'   => Init::responseMessage($rst, 'View'),
                'data'      => $arr],
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
}