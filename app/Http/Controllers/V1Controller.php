<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Init;
use App\Helpers\Dbase;
use App\Helpers\Status;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class V1Controller extends Controller
{ 
    public function getSitemap(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );

            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);

                    $list = array();
                    $temp = DB::table('tr_navigation')
                        ->join('tm_menu', 'tm_menu.menu_id', '=', 'tr_navigation.menu_id')
                        ->select('tm_menu.menu_id', 'tm_menu.menu_name', 'tm_menu.menu_label', 'tm_menu.menu_icon', 'tm_menu.menu_url')
                        ->where('satker_id', $satker_id)
                        ->where('menu_url', '!=', '#')
                        ->orderBy('tm_menu.menu_id')
                        ->get();
                    
                    $list[] = array(
                        'menu_icon'     => 'fa-home',
                        'menu_title_in' => 'Beranda',
                        'menu_title_en' => 'Home',
                        'menu_url'      => Init::landingUrl() .$slug .'/home',
                    );

                    foreach($temp as $row) {
                        $list[] = array(
                            'menu_icon'     => $row->menu_icon,
                            'menu_title_in' => $row->menu_name,
                            'menu_title_en' => $row->menu_label,
                            'menu_url'      => Init::landingUrl() .$slug .'/'. $row->menu_url,
                        );
                    }

                    // $list[] = array(
                    //     'menu_icon'     => 'fa-circle-info',
                    //     'menu_title_in' => 'Kontak Kami',
                    //     'menu_title_en' => 'Contact Us',
                    //     'menu_url'      => Init::landingUrl() .$slug .'/contact-us',
                    // );

                    $data = array(
                        'info' => $info,
                        'list' => $list,
                    );
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }    
        }
        else {
            $rst = 0;
            $validate = json_encode($request->header());//"Kesalahan header token";  
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getAboutInfo(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $temp = DB::table('tp_info')->where('satker_id', $satker_id)->where('info_status', 1)->orderBy('info_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $list[] = array(
                            "text_in"  => (($row->info_text_in == null? "":$row->info_text_in)),
                            "text_en"  => (($row->info_text_en == null? "":$row->info_text_en)),
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'list' => $list,
                    );

                    $menu_id = 20;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getAboutStory(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $temp = DB::table('tp_story')->where('satker_id', $satker_id)->where('story_status', 1)->orderBy('story_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $list[] = array(
                            "text_in"  => (($row->story_text_in == null? "":$row->story_text_in)),
                            "text_en"  => (($row->story_text_en == null? "":$row->story_text_en)),
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'list' => $list,
                    );

                    $menu_id = 21;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }
    
    public function getAboutDoctrin(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $temp = DB::table('tp_doctrin')->where('satker_id', $satker_id)->where('doctrin_status', 1)->orderBy('doctrin_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $list[] = array(
                            "text_in"  => (($row->doctrin_text_in == null? "":$row->doctrin_text_in)),
                            "text_en"  => (($row->doctrin_text_en == null? "":$row->doctrin_text_en)),
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'list' => $list,
                    );

                    $menu_id = 22;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getAboutLogo(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $temp = DB::table('tp_logo')->where('satker_id', $satker_id)->where('logo_status', 1)->orderBy('logo_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $list[] = array(
                            "text_in"  => (($row->logo_text_in == null? "":$row->logo_text_in)),
                            "text_en"  => (($row->logo_text_en == null? "":$row->logo_text_en)),
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'list' => $list,
                    );

                    $menu_id = 23;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getAboutIad(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $temp = DB::table('tp_iad')->where('satker_id', $satker_id)->where('iad_status', 1)->orderBy('iad_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $list[] = array(
                            "text_in"  => (($row->iad_text_in == null? "":$row->iad_text_in)),
                            "text_en"  => (($row->iad_text_en == null? "":$row->iad_text_en)),
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'list' => $list,
                    );

                    $menu_id = 24;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getAboutIntro(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $temp = DB::table('tp_intro')->where('satker_id', $satker_id)->where('intro_status', 1)->orderBy('intro_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $list[] = array(
                            "text_in"  => (($row->intro_text_in == null? "":$row->intro_text_in)),
                            "text_en"  => (($row->intro_text_en == null? "":$row->intro_text_en)),
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'list' => $list,
                    );

                    $menu_id = 25;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getAboutVision(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $temp = DB::table('tp_vision')->where('satker_id', $satker_id)->where('vision_status', 1)->orderBy('vision_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $list[] = array(
                            "text_in"  => (($row->vision_text_in == null? "":$row->vision_text_in)),
                            "text_en"  => (($row->vision_text_en == null? "":$row->vision_text_en)),
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'list' => $list,
                    );

                    $menu_id = 26;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getAboutMision(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $temp = DB::table('tp_mision')->where('satker_id', $satker_id)->where('mision_status', 1)->orderBy('mission_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $list[] = array(
                            "text_in"  => (($row->mision_text_in == null? "":$row->mision_text_in)),
                            "text_en"  => (($row->mision_text_en == null? "":$row->mision_text_en)),
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'list' => $list,
                    );

                    $menu_id = 27;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getAboutProgram(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $temp = DB::table('tp_program')->where('satker_id', $satker_id)->where('program_status', 1)->orderBy('program_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $list[] = array(
                            "text_in"  => (($row->program_text_in == null? "":$row->program_text_in)),
                            "text_en"  => (($row->program_text_en == null? "":$row->program_text_en)),
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'list' => $list,
                    );

                    $menu_id = 28;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getAboutCommand(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $temp = DB::table('tp_command')->where('satker_id', $satker_id)->where('command_status', 1)->orderBy('command_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $list[] = array(
                            "text_in"  => (($row->command_text_in == null? "":$row->command_text_in)),
                            "text_en"  => (($row->command_text_en == null? "":$row->command_text_en)),
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'list' => $list,
                    );

                    $menu_id = 29;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }


    public function getIntegrityLegal(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $temp = DB::table('tp_legal')->where('satker_id', $satker_id)->where('legal_status', 1)->orderBy('legal_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $list[] = array(
                            "text_in"  => (($row->legal_text_in == null? "":$row->legal_text_in)),
                            "text_en"  => (($row->legal_text_en == null? "":$row->legal_text_en)),
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'list' => $list,
                    );

                    $menu_id = 61;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getIntegrityMechanism(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $temp = DB::table('tp_mechanism')->where('satker_id', $satker_id)->where('mechanism_status', 1)->orderBy('mechanism_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $list[] = array(
                            "text_in"  => (($row->mechanism_text_in == null? "":$row->mechanism_text_in)),
                            "text_en"  => (($row->mechanism_text_en == null? "":$row->mechanism_text_en)),
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'list' => $list,
                    );

                    $menu_id = 62;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getIntegrityArrangement(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $temp = DB::table('tp_arrangement')->where('satker_id', $satker_id)->where('arrangement_status', 1)->orderBy('arrangement_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $list[] = array(
                            "text_in"  => (($row->arrangement_text_in == null? "":$row->arrangement_text_in)),
                            "text_en"  => (($row->arrangement_text_en == null? "":$row->arrangement_text_en)),
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'list' => $list,
                    );

                    $menu_id = 63;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getIntegrityAccountability(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $temp = DB::table('tp_accountability')->where('satker_id', $satker_id)->where('accountability_status', 1)->orderBy('accountability_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $list[] = array(
                            "text_in"  => (($row->accountability_text_in == null? "":$row->accountability_text_in)),
                            "text_en"  => (($row->accountability_text_en == null? "":$row->accountability_text_en)),
                        );
                    }

                    $data = array(
                        'info' => $info,
                        'list' => $list,
                    );

                    $menu_id = 64;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getIntegrityProfessionalism(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $temp = DB::table('tp_professionalism')->where('satker_id', $satker_id)->where('professionalism_status', 1)->orderBy('professionalism_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $list[] = array(
                            "text_in"  => (($row->professionalism_text_in == null? "":$row->professionalism_text_in)),
                            "text_en"  => (($row->professionalism_text_en == null? "":$row->professionalism_text_en)),
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'list' => $list,
                    );

                    $menu_id = 65;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getIntegrityInnovation(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $temp = DB::table('tp_innovation')->where('satker_id', $satker_id)->where('innovation_status', 1)->orderBy('innovation_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $list[] = array(
                            "text_in"  => (($row->innovation_text_in == null? "":$row->innovation_text_in)),
                            "text_en"  => (($row->innovation_text_en == null? "":$row->innovation_text_en)),
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'list' => $list,
                    );

                    $menu_id = 66;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getIntegritySupervision(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $temp = DB::table('tp_supervision')->where('satker_id', $satker_id)->where('supervision_status', 1)->orderBy('supervision_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $list[] = array(
                            "text_in"  => (($row->supervision_text_in == null? "":$row->supervision_text_in)),
                            "text_en"  => (($row->supervision_text_en == null? "":$row->supervision_text_en)),
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'list' => $list,
                    );

                    $menu_id = 67;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    
    public function getInformationUnit(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            
            $limit  = $request->limit;
            $limit  = (($limit == "")?10:$limit);
            $offset = $request->offset;
            $offset = (($offset == "")?0:$offset);
            
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);

                    $list = array();
                    $cnts = DB::table('tp_unit')->where('satker_id', $satker_id)->where('unit_status', 1)->count();
                    $temp = DB::table('tp_unit')->where('satker_id', $satker_id)->where('unit_status', 1)->take($limit)->skip($offset)->orderBy('unit_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $url = Init::backendUrl() .'api/v1/read-information-unit/'. $info['profile']['satker_slug'] .'/@'. $row->unit_id .'&'. Status::str_url($row->unit_title);
                        $list[] = array(
                            "id"       => $row->unit_id,
                            "title"    => (($row->unit_title == null? "":$row->unit_title)),
                            "text_in"  => (($row->unit_text_in == null? "":$row->unit_text_in)),
                            "text_en"  => (($row->unit_text_en == null? "":$row->unit_text_en)),
                            "url"      => $url,
                        );
                    }
    
                    $data = array(
                        'info'  => $info,
                        'total' => $cnts,
                        'list'  => $list,
                    );

                    $menu_id = 31;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function readInformationUnit($slug, $id) {
        $data = array();
        $validate = Init::initValidate(
            array('slug', 'id'), 
            array($slug, $id)
        );

        if($validate == "") {
            $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
            if($satker != "[]") {
                $rst = 1;
                $satker_id = $satker[0]->satker_id;
                $info = Init::initSatkerInfo($satker_id, $satker);

                $id = Status::get_id_from_url($id);
                $temp = DB::table('tp_unit')->where('unit_id', $id)->where('unit_status', 1)->get();
                if($temp != "[]") {
                    foreach($temp as $row) {
                        $read = array(
                            "title"    => (($row->unit_title == null? "":$row->unit_title)),
                            "text_in"  => (($row->unit_text_in == null? "":$row->unit_text_in)),
                            "text_en"  => (($row->unit_text_en == null? "":$row->unit_text_en)),
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'read' => $read,
                    );
                }
                else {
                    $rst = 0;
                    $validate = "Data tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
                $validate = "Satua kerja tidak ditemukan";  
            }
        }
        else {
            $rst = 0;
        }  

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getInformationStructural(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;

            $limit  = $request->limit;
            $limit  = (($limit == "")?10:$limit);
            $offset = $request->offset;
            $offset = (($offset == "")?0:$offset);

            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $cnts = DB::table('tp_structural')->where('satker_id', $satker_id)->where('structural_status', 1)->count();
                    $temp = DB::table('tp_structural')->where('satker_id', $satker_id)->where('structural_status', 1)->take($limit)->skip($offset)->orderBy('structural_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $url = Init::backendUrl() .'api/v1/read-information-structural/'. $info['profile']['satker_slug'] .'/@'. $row->structural_id .'&'. Status::str_url($row->structural_name);
                        $list[] = array(
                            "id"            => $row->structural_id,
                            "position"      => (($row->structural_position == null? "1":$row->structural_position)),
                            "name"          => (($row->structural_name == null? "":$row->structural_name)),
                            "nip"           => (($row->structural_nip == null? "":$row->structural_nip)),
                            "title"         => (($row->structural_title == null? "":$row->structural_title)),
                            "information"   => (($row->structural_information == null? "":$row->structural_information)),
                            "size"          => (($row->structural_size == null)? 0:$row->structural_size),
                            "image"         => (($row->structural_image == null? "":$row->structural_image)),
                            "path"          => (($row->structural_path == null? Init::defaultImage():$row->structural_path)),
                            "url"           => $url,
                        );
                    }
    
                    $data = array(
                        'info'  => $info,
                        'total' => $cnts,
                        'list'  => $list,
                    );

                    $menu_id = 32;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }
    
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function readInformationStructural($slug, $id) {
        $data = array();
        $validate = Init::initValidate(
            array('slug', 'id'), 
            array($slug, $id)
        );

        if($validate == "") {
            $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
            if($satker != "[]") {
                $rst = 1;
                $satker_id = $satker[0]->satker_id;
                $info = Init::initSatkerInfo($satker_id, $satker);

                $id = Status::get_id_from_url($id);
                $temp = DB::table('tp_structural')->where('structural_id', $id)->where('structural_status', 1)->get();
                if($temp != "[]") {
                    foreach($temp as $row) {
                        $read = array(
                            "position"      => (($row->structural_position == null? "1":$row->structural_position)),
                            "name"          => (($row->structural_name == null? "":$row->structural_name)),
                            "nip"           => (($row->structural_nip == null? "":$row->structural_nip)),
                            "title"         => (($row->structural_title == null? "":$row->structural_title)),
                            "information"   => (($row->structural_information == null? "":$row->structural_information)),
                            "size"          => (($row->structural_size == null)? 0:$row->structural_size),
                            "image"         => (($row->structural_image == null? "":$row->structural_image)),
                            "path"          => (($row->structural_path == null? Init::defaultImage():$row->structural_path)),
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'read' => $read,
                    );
                }
                else {
                    $rst = 0;
                    $validate = "Data tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
                $validate = "Satua kerja tidak ditemukan";  
            }
        }
        else {
            $rst = 0;
        }  

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getInformationStructurals(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;

            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    
                    $arr_position1 = array();
                    $position1 = DB::table('tp_structural')->where('satker_id', $satker_id)->where('structural_position', 1)->where('structural_status', 1)->get();
                    foreach($position1 as $row) {
                        $url = Init::backendUrl() .'api/v1/read-information-structural/'. $info['profile']['satker_slug'] .'/@'. $row->structural_id .'&'. Status::str_url($row->structural_name);
                        $arr_position1[] = array(
                            "id"            => $row->structural_id,
                            "position"      => (($row->structural_position == null? "1":$row->structural_position)),
                            "name"          => (($row->structural_name == null? "":$row->structural_name)),
                            "nip"           => (($row->structural_nip == null? "":$row->structural_nip)),
                            "title"         => (($row->structural_title == null? "":$row->structural_title)),
                            "information"   => (($row->structural_information == null? "":$row->structural_information)),
                            "size"          => (($row->structural_size == null)? 0:$row->structural_size),
                            "image"         => (($row->structural_image == null? "":$row->structural_image)),
                            "path"          => (($row->structural_path == null? Init::defaultImage():$row->structural_path)),
                            "url"           => $url,
                        );
                    }

                    $arr_position2 = array();
                    $position2 = DB::table('tp_structural')->where('satker_id', $satker_id)->where('structural_position', 2)->where('structural_status', 1)->get();
                    foreach($position2 as $row) {
                        $url = Init::backendUrl() .'api/v1/read-information-structural/'. $info['profile']['satker_slug'] .'/@'. $row->structural_id .'&'. Status::str_url($row->structural_name);
                        $arr_position2[] = array(
                            "id"            => $row->structural_id,
                            "position"      => (($row->structural_position == null? "1":$row->structural_position)),
                            "name"          => (($row->structural_name == null? "":$row->structural_name)),
                            "nip"           => (($row->structural_nip == null? "":$row->structural_nip)),
                            "title"         => (($row->structural_title == null? "":$row->structural_title)),
                            "information"   => (($row->structural_information == null? "":$row->structural_information)),
                            "size"          => (($row->structural_size == null)? 0:$row->structural_size),
                            "image"         => (($row->structural_image == null? "":$row->structural_image)),
                            "path"          => (($row->structural_path == null? Init::defaultImage():$row->structural_path)),
                            "url"           => $url,
                        );
                    }

                    $arr_position3 = array();
                    $position3 = DB::table('tp_structural')->where('satker_id', $satker_id)->where('structural_position', 3)->where('structural_status', 1)->get();
                    foreach($position3 as $row) {
                        $url = Init::backendUrl() .'api/v1/read-information-structural/'. $info['profile']['satker_slug'] .'/@'. $row->structural_id .'&'. Status::str_url($row->structural_name);
                        $arr_position3[] = array(
                            "id"            => $row->structural_id,
                            "position"      => (($row->structural_position == null? "1":$row->structural_position)),
                            "name"          => (($row->structural_name == null? "":$row->structural_name)),
                            "nip"           => (($row->structural_nip == null? "":$row->structural_nip)),
                            "title"         => (($row->structural_title == null? "":$row->structural_title)),
                            "information"   => (($row->structural_information == null? "":$row->structural_information)),
                            "size"          => (($row->structural_size == null)? 0:$row->structural_size),
                            "image"         => (($row->structural_image == null? "":$row->structural_image)),
                            "path"          => (($row->structural_path == null? Init::defaultImage():$row->structural_path)),
                            "url"           => $url,
                        );
                    }

                    $arr_position4 = array();
                    $position4 = DB::table('tp_structural')->where('satker_id', $satker_id)->where('structural_position', 4)->where('structural_status', 1)->get();
                    foreach($position4 as $row) {
                        $url = Init::backendUrl() .'api/v1/read-information-structural/'. $info['profile']['satker_slug'] .'/@'. $row->structural_id .'&'. Status::str_url($row->structural_name);
                        $arr_position4[] = array(
                            "id"            => $row->structural_id,
                            "position"      => (($row->structural_position == null? "1":$row->structural_position)),
                            "name"          => (($row->structural_name == null? "":$row->structural_name)),
                            "nip"           => (($row->structural_nip == null? "":$row->structural_nip)),
                            "title"         => (($row->structural_title == null? "":$row->structural_title)),
                            "information"   => (($row->structural_information == null? "":$row->structural_information)),
                            "size"          => (($row->structural_size == null)? 0:$row->structural_size),
                            "image"         => (($row->structural_image == null? "":$row->structural_image)),
                            "path"          => (($row->structural_path == null? Init::defaultImage():$row->structural_path)),
                            "url"           => $url,
                        );
                    }

                    $arr_position5 = array();
                    $position5 = DB::table('tp_structural')->where('satker_id', $satker_id)->where('structural_position', 5)->where('structural_status', 1)->get();
                    foreach($position5 as $row) {
                        $url = Init::backendUrl() .'api/v1/read-information-structural/'. $info['profile']['satker_slug'] .'/@'. $row->structural_id .'&'. Status::str_url($row->structural_name);
                        $arr_position5[] = array(
                            "id"            => $row->structural_id,
                            "position"      => (($row->structural_position == null? "1":$row->structural_position)),
                            "name"          => (($row->structural_name == null? "":$row->structural_name)),
                            "nip"           => (($row->structural_nip == null? "":$row->structural_nip)),
                            "title"         => (($row->structural_title == null? "":$row->structural_title)),
                            "information"   => (($row->structural_information == null? "":$row->structural_information)),
                            "size"          => (($row->structural_size == null)? 0:$row->structural_size),
                            "image"         => (($row->structural_image == null? "":$row->structural_image)),
                            "path"          => (($row->structural_path == null? Init::defaultImage():$row->structural_path)),
                            "url"           => $url,
                        );
                    }


                    $list = array(
                        'position1'  => $arr_position1,
                        'position2'  => $arr_position2,
                        'position3'  => $arr_position3,
                        'position4'  => $arr_position4,
                        'position5'  => $arr_position5,
                    );
    
                    $data = array(
                        'info'  => $info,
                        'list'  => $list,
                    );

                    $menu_id = 32;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }
    
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getInformationInfrastructure(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            
            $limit  = $request->limit;
            $limit  = (($limit == "")?10:$limit);
            $offset = $request->offset;
            $offset = (($offset == "")?0:$offset);

            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $cnts = DB::table('tp_infrastructure')->where('satker_id', $satker_id)->where('infrastructure_status', 1)->count();
                    $temp = DB::table('tp_infrastructure')->where('satker_id', $satker_id)->where('infrastructure_status', 1)->take($limit)->skip($offset)->orderBy('infrastructure_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $url = Init::backendUrl() .'api/v1/read-information-infrastructure/'. $info['profile']['satker_slug'] .'/@'. $row->infrastructure_id .'&'. Status::str_url($row->infrastructure_name);
                        $list[] = array(
                            "id"            => $row->infrastructure_id,
                            "name"          => (($row->infrastructure_name == null? "":$row->infrastructure_name)),
                            "information"   => (($row->infrastructure_information == null? "":$row->infrastructure_information)),
                            "size"          => (($row->infrastructure_size == null)? 0:$row->infrastructure_size),
                            "image"         => (($row->infrastructure_image == null? "":$row->infrastructure_image)),
                            "path"          => (($row->infrastructure_path == null? Init::defaultImage():$row->infrastructure_path)),
                            "url"           => $url,
                        );
                    }
    
                    $data = array(
                        'info'  => $info,
                        'total' => $cnts,
                        'list'  => $list,
                    );

                    $menu_id = 33;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }
    
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function readInformationInfrastructure($slug, $id) {
        $data = array();
        $validate = Init::initValidate(
            array('slug', 'id'), 
            array($slug, $id)
        );

        if($validate == "") {
            $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
            if($satker != "[]") {
                $rst = 1;
                $satker_id = $satker[0]->satker_id;
                $info = Init::initSatkerInfo($satker_id, $satker);

                $id = Status::get_id_from_url($id);
                $temp = DB::table('tp_infrastructure')->where('infrastructure_id', $id)->where('infrastructure_status', 1)->get();
                if($temp != "[]") {
                    foreach($temp as $row) {
                        $read = array(
                            "name"          => (($row->infrastructure_name == null? "":$row->infrastructure_name)),
                            "information"   => (($row->infrastructure_information == null? "":$row->infrastructure_information)),
                            "size"          => (($row->infrastructure_size == null)? 0:$row->infrastructure_size),
                            "image"         => (($row->infrastructure_image == null? "":$row->infrastructure_image)),
                            "path"          => (($row->infrastructure_path == null? Init::defaultImage():$row->infrastructure_path)),
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'read' => $read,
                    );
                }
                else {
                    $rst = 0;
                    $validate = "Data tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
                $validate = "Satua kerja tidak ditemukan";  
            }
        }
        else {
            $rst = 0;
        }  

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getInformationDpo(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;

            $limit  = $request->limit;
            $limit  = (($limit == "")?10:$limit);
            $offset = $request->offset;
            $offset = (($offset == "")?0:$offset);

            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $cnts = DB::table('tp_dpo')->where('satker_id', $satker_id)->where('dpo_status', 1)->count();
                    $temp = DB::table('tp_dpo')->where('satker_id', $satker_id)->where('dpo_status', 1)->take($limit)->skip($offset)->orderBy('dpo_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $url = Init::backendUrl() .'api/v1/read-information-dpo/'. $info['profile']['satker_slug'] .'/@'. $row->dpo_id .'&'. Status::str_url($row->dpo_name);
                        $list[] = array(
                            "id"            => $row->dpo_id,
                            "name"          => (($row->dpo_name == null? "":$row->dpo_name)),
                            "information"   => (($row->dpo_information == null? "":$row->dpo_information)),
                            "size"          => (($row->dpo_size == null)? 0:$row->dpo_size),
                            "image"         => (($row->dpo_image == null? "":$row->dpo_image)),
                            "path"          => (($row->dpo_path == null? Init::defaultImage():$row->dpo_path)),
                            "url"           => $url,
                        );
                    }

                    $data = array(
                        'info'  => $info,
                        'total' => $cnts,
                        'list'  => $list,
                    );

                    $menu_id = 34;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }
    
    public function readInformationDpo($slug, $id) {
        $data = array();
        $validate = Init::initValidate(
            array('slug', 'id'), 
            array($slug, $id)
        );

        if($validate == "") {
            $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
            if($satker != "[]") {
                $rst = 1;
                $satker_id = $satker[0]->satker_id;
                $info = Init::initSatkerInfo($satker_id, $satker);

                $id = Status::get_id_from_url($id);
                $temp = DB::table('tp_dpo')->where('dpo_id', $id)->where('dpo_status', 1)->get();
                if($temp != "[]") {
                    foreach($temp as $row) {
                        $read = array(
                            "name"          => (($row->dpo_name == null? "":$row->dpo_name)),
                            "information"   => (($row->dpo_information == null? "":$row->dpo_information)),
                            "size"          => (($row->dpo_size == null)? 0:$row->dpo_size),
                            "image"         => (($row->dpo_image == null? "":$row->dpo_image)),
                            "path"          => (($row->dpo_path == null? Init::defaultImage():$row->dpo_path)),
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'read' => $read,
                    );
                }
                else {
                    $rst = 0;
                    $validate = "Data tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
                $validate = "Satua kerja tidak ditemukan";  
            }
        }
        else {
            $rst = 0;
        }  

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getInformationService(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;

            $limit  = $request->limit;
            $limit  = (($limit == "")?10:$limit);
            $offset = $request->offset;
            $offset = (($offset == "")?0:$offset);

            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
                    
                    $list = array();
                    $cnts = DB::table('tp_service')->where('satker_id', $satker_id)->where('service_status', 1)->count();
                    $temp = DB::table('tp_service')->where('satker_id', $satker_id)->where('service_status', 1)->take($limit)->skip($offset)->orderBy('service_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $url = Init::backendUrl() .'api/v1/read-information-service/'. $info['profile']['satker_slug'] .'/@'. $row->service_id .'&'. Status::str_url($row->service_title);
                        $list[] = array(
                            "id"           => $row->service_id,
                            "title"        => (($row->service_title == null? "":$row->service_title)),
                            "link"         => (($row->service_link == null? "":$row->service_link)),
                            "information"  => (($row->service_information == null? "":$row->service_information)),
                            "size"         => (($row->service_size == null)? 0:$row->service_size),
                            "image"        => (($row->service_image == null? "":$row->service_image)),
                            "path"         => (($row->service_path == null? Init::defaultImage():$row->service_path)),
                            "url"          => $url,
                        );
                    }
    
                    $data = array(
                        'info'  => $info,
                        'total' => $cnts,
                        'list'  => $list,
                    );

                    $menu_id = 35;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function readInformationService($slug, $id) {
        $data = array();
        $validate = Init::initValidate(
            array('slug', 'id'), 
            array($slug, $id)
        );

        if($validate == "") {
            $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
            if($satker != "[]") {
                $rst = 1;
                $satker_id = $satker[0]->satker_id;
                $info = Init::initSatkerInfo($satker_id, $satker);

                $id = Status::get_id_from_url($id);
                $temp = DB::table('tp_service')->where('service_id', $id)->where('service_status', 1)->get();
                if($temp != "[]") {
                    foreach($temp as $row) {
                        $read = array(
                            "title"        => (($row->service_title == null? "":$row->service_title)),
                            "link"         => (($row->service_link == null? "":$row->service_link)),
                            "information"  => (($row->service_information == null? "":$row->service_information)),
                            "size"         => (($row->service_size == null)? 0:$row->service_size),
                            "image"        => (($row->service_image == null? "":$row->service_image)),
                            "path"         => (($row->service_path == null? Init::defaultImage():$row->service_path)),
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'read' => $read,
                    );
                }
                else {
                    $rst = 0;
                    $validate = "Data tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
                $validate = "Satua kerja tidak ditemukan";  
            }
        }
        else {
            $rst = 0;
        }  

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }


    public function getConferenceNewsHeadline(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $id = $request->id;
            $slug = $request->slug;
            
            $limit  = $request->limit;
            $limit  = (($limit == "")?10:$limit);
            $offset = $request->offset;
            $offset = (($offset == "")?0:$offset);

            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    
                    $cnts = DB::table('tp_news')->where('satker_id', 100)->where('news_broadcast', 1)->where('news_status', 1)->whereNot('news_id', $id)->count();
                    $temp = DB::table('tp_news')->where('satker_id', 100)->where('news_broadcast', 1)->where('news_status', 1)->whereNot('news_id', $id)->take($limit)->skip($offset)->orderBy('news_date', 'DESC')->get();
                    
                    foreach($temp as $row) {
                        $date = Carbon::createFromFormat('Y-m-d', $row->news_date)
                            ->format('d-m-Y');

                        $url = Init::backendUrl() .'api/v1/read-conference-news/'. $info['profile']['satker_slug'] .'/@'. $row->news_id .'&'. Status::str_url($row->news_title);
                        $list[] = array(
                            "id"             => $row->news_id,
                            "date"           => $date,
                            "title"          => (($row->news_title == null? "":$row->news_title)),
                            "category"       => (($row->news_category == null? "":$row->news_category)),
                            "text_in"        => (($row->news_text_in == null? "":$row->news_text_in)),
                            "text_en"        => (($row->news_text_en == null? "":$row->news_text_en)),
                            "link_instagram" => (($row->news_link_instagram == null? "":$row->news_link_instagram)),
                            "link_youtube"   => (($row->news_link_youtube == null? "":$row->news_link_youtube)),
                            "size"           => (($row->news_size == null? 0:$row->news_size)),
                            "image"          => (($row->news_image == null? "":$row->news_image)),
                            "path"           => (($row->news_path == null? Init::defaultImage():$row->news_path)),
                            "view"           => $row->news_view,
                            "url"            => $url,
                        );
                    }
    
                    $data = array(
                        'info'  => $info,
                        'total' => $cnts,
                        'list'  => $list,
                    );

                    $menu_id = 41;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }
    
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getConferenceNewsRegional(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $id = $request->id;
            $slug = $request->slug;
            
            $limit  = $request->limit;
            $limit  = (($limit == "")?10:$limit);
            $offset = $request->offset;
            $offset = (($offset == "")?0:$offset);

            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    
                    $arrSatker = Dbase::memberSatker(104);
                    $cnts = DB::table('tp_news')->whereIn('satker_id', $arrSatker)->where('news_broadcast', 1)->where('news_status', 1)->whereNot('news_id', $id)->count();
                    $temp = DB::table('tp_news')->whereIn('satker_id', $arrSatker)->where('news_broadcast', 1)->where('news_status', 1)->whereNot('news_id', $id)->take($limit)->skip($offset)->orderBy('news_date', 'DESC')->get();
                    
                    foreach($temp as $row) {
                        $date = Carbon::createFromFormat('Y-m-d', $row->news_date)
                            ->format('d-m-Y');

                        $url = Init::backendUrl() .'api/v1/read-conference-news/'. $info['profile']['satker_slug'] .'/@'. $row->news_id .'&'. Status::str_url($row->news_title);
                        $list[] = array(
                            "id"             => $row->news_id,
                            "date"           => $date,
                            "title"          => (($row->news_title == null? "":$row->news_title)),
                            "category"       => (($row->news_category == null? "":$row->news_category)),
                            "text_in"        => (($row->news_text_in == null? "":$row->news_text_in)),
                            "text_en"        => (($row->news_text_en == null? "":$row->news_text_en)),
                            "link_instagram" => (($row->news_link_instagram == null? "":$row->news_link_instagram)),
                            "link_youtube"   => (($row->news_link_youtube == null? "":$row->news_link_youtube)),
                            "size"           => (($row->news_size == null? 0:$row->news_size)),
                            "image"          => (($row->news_image == null? "":$row->news_image)),
                            "path"           => (($row->news_path == null? Init::defaultImage():$row->news_path)),
                            "view"           => $row->news_view,
                            "url"            => $url,
                        );
                    }
    
                    $data = array(
                        'info'  => $info,
                        'total' => $cnts,
                        'list'  => $list,
                    );

                    $menu_id = 41;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }
    
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getConferenceNewsOther(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $id = $request->id;
            $slug = $request->slug;
            
            $category = Dbase::dbGetFieldById('tp_news', 'news_category', 'news_id', $id);

            $limit  = $request->limit;
            $limit  = (($limit == "")?10:$limit);
            $offset = $request->offset;
            $offset = (($offset == "")?0:$offset);

            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    
                    $cnts = DB::table('tp_news')->where('news_category', $category)->where('satker_id', $satker_id)->where('news_status', 1)->whereNot('news_id', $id)->count();
                    $temp = DB::table('tp_news')->where('news_category', $category)->where('satker_id', $satker_id)->where('news_status', 1)->whereNot('news_id', $id)->take($limit)->skip($offset)->inRandomOrder()->get();
                    
                    foreach($temp as $row) {
                        $date = Carbon::createFromFormat('Y-m-d', $row->news_date)
                            ->format('d-m-Y');

                        $url = Init::backendUrl() .'api/v1/read-conference-news/'. $info['profile']['satker_slug'] .'/@'. $row->news_id .'&'. Status::str_url($row->news_title);
                        $list[] = array(
                            "id"             => $row->news_id,
                            "date"           => $date,
                            "title"          => (($row->news_title == null? "":$row->news_title)),
                            "category"       => (($row->news_category == null? "":$row->news_category)),
                            "text_in"        => (($row->news_text_in == null? "":$row->news_text_in)),
                            "text_en"        => (($row->news_text_en == null? "":$row->news_text_en)),
                            "link_instagram" => (($row->news_link_instagram == null? "":$row->news_link_instagram)),
                            "link_youtube"   => (($row->news_link_youtube == null? "":$row->news_link_youtube)),
                            "size"           => (($row->news_size == null? 0:$row->news_size)),
                            "image"          => (($row->news_image == null? "":$row->news_image)),
                            "path"           => (($row->news_path == null? Init::defaultImage():$row->news_path)),
                            "view"           => $row->news_view,
                            "url"            => $url,
                        );
                    }
    
                    $data = array(
                        'info'  => $info,
                        'total' => $cnts,
                        'list'  => $list,
                    );

                    $menu_id = 41;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }
    
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getConferenceNews(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            $category = $request->category;

            $limit  = $request->limit;
            $limit  = (($limit == "")?10:$limit);
            $offset = $request->offset;
            $offset = (($offset == "")?0:$offset);

            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    
                    if($category != "") {
                        $cnts = DB::table('tp_news')->where('news_category', $category)->where('satker_id', $satker_id)->where('news_status', 1)->count();
                        $temp = DB::table('tp_news')->where('news_category', $category)->where('satker_id', $satker_id)->where('news_status', 1)->take($limit)->skip($offset)->orderBy('news_date', 'DESC')->get();
                    } 
                    else {
                        $cnts = DB::table('tp_news')->where('satker_id', $satker_id)->where('news_status', 1)->count();
                        $temp = DB::table('tp_news')->where('satker_id', $satker_id)->where('news_status', 1)->take($limit)->skip($offset)->orderBy('news_date', 'DESC')->get();
                    }
                    
                    foreach($temp as $row) {
                        $date = Carbon::createFromFormat('Y-m-d', $row->news_date)
                            ->format('d-m-Y');

                        $url = Init::backendUrl() .'api/v1/read-conference-news/'. $info['profile']['satker_slug'] .'/@'. $row->news_id .'&'. Status::str_url($row->news_title);
                        $list[] = array(
                            "id"             => $row->news_id,
                            "date"           => $date,
                            "title"          => (($row->news_title == null? "":$row->news_title)),
                            "category"       => (($row->news_category == null? "":$row->news_category)),
                            "text_in"        => (($row->news_text_in == null? "":$row->news_text_in)),
                            "text_en"        => (($row->news_text_en == null? "":$row->news_text_en)),
                            "link_instagram" => (($row->news_link_instagram == null? "":$row->news_link_instagram)),
                            "link_youtube"   => (($row->news_link_youtube == null? "":$row->news_link_youtube)),
                            "size"           => (($row->news_size == null? 0:$row->news_size)),
                            "image"          => (($row->news_image == null? "":$row->news_image)),
                            "path"           => (($row->news_path == null? Init::defaultImage():$row->news_path)),
                            "view"           => $row->news_view,
                            "url"            => $url,
                        );
                    }
    
                    $data = array(
                        'info'  => $info,
                        'total' => $cnts,
                        'list'  => $list,
                    );

                    $menu_id = 41;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }
    
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function readConferenceNews($slug, $id) {
        $data = array();
        $validate = Init::initValidate(
            array('slug', 'id'), 
            array($slug, $id)
        );

        if($validate == "") {
            $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
            if($satker != "[]") {
                $rst = 1;
                $satker_id = $satker[0]->satker_id;
                $info = Init::initSatkerInfo($satker_id, $satker);

                $id = Status::get_id_from_url($id);
                $temp = DB::table('tp_news')->where('news_id', $id)->where('news_status', 1)->get();
                if($temp != "[]") {
                    foreach($temp as $row) {
                        $date = Carbon::createFromFormat('Y-m-d', $row->news_date)
                            ->format('d-m-Y');

                        $read = array(
                            "date"           => $date,
                            "title"          => (($row->news_title == null? "":$row->news_title)),
                            "category"       => (($row->news_category == null? "":$row->news_category)),
                            "text_in"        => (($row->news_text_in == null? "":$row->news_text_in)),
                            "text_en"        => (($row->news_text_en == null? "":$row->news_text_en)),
                            "link_instagram" => (($row->news_link_instagram == null? "":$row->news_link_instagram)),
                            "link_youtube"   => (($row->news_link_youtube == null? "":$row->news_link_youtube)),
                            "size"           => (($row->news_size == null? 0:$row->news_size)),
                            "image"          => (($row->news_image == null? "":$row->news_image)),
                            "path"           => (($row->news_path == null? Init::defaultImage():$row->news_path)),
                            "view"           => $row->news_view,
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'read' => $read,
                    );

                    Dbase::processView('tp_news', 'news_view', 'news_id', $id);
                }
                else {
                    $rst = 0;
                    $validate = "Data tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
                $validate = "Satua kerja tidak ditemukan";  
            }
        }
        else {
            $rst = 0;
        }  

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getConferenceAnnouncement(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;

            $limit  = $request->limit;
            $limit  = (($limit == "")?10:$limit);
            $offset = $request->offset;
            $offset = (($offset == "")?0:$offset);

            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $cnts = DB::table('tp_announcement')->where('satker_id', $satker_id)->where('announcement_status', 1)->count();
                    $temp = DB::table('tp_announcement')->where('satker_id', $satker_id)->where('announcement_status', 1)->take($limit)->skip($offset)->orderBy('announcement_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $url = Init::backendUrl() .'api/v1/read-conference-announcement/'. $info['profile']['satker_slug'] .'/@'. $row->announcement_id .'&'. Status::str_url($row->announcement_title);
                        $list[] = array(
                            "id"         => $row->announcement_id,
                            "title"      => (($row->announcement_title == null? "":$row->announcement_title)),
                            "text_in"    => (($row->announcement_text_in == null? "":$row->announcement_text_in)),
                            "text_en"    => (($row->announcement_text_en == null? "":$row->announcement_text_en)),
                            "size"       => (($row->announcement_size == null? 0:$row->announcement_size)),
                            "image"      => (($row->announcement_image == null? "":$row->announcement_image)),
                            "path"       => (($row->announcement_path == null? Init::defaultImage():$row->announcement_path)),
                            "view"       => $row->announcement_view,
                            "url"        => $url,
                        );
                    }
    
                    $data = array(
                        'info'  => $info,
                        'total' => $cnts,
                        'list'  => $list,
                    );

                    $menu_id = 42;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }
    
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function readConferenceAnnouncement($slug, $id) {
        $data = array();
        $validate = Init::initValidate(
            array('slug', 'id'), 
            array($slug, $id)
        );

        if($validate == "") {
            $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
            if($satker != "[]") {
                $rst = 1;
                $satker_id = $satker[0]->satker_id;
                $info = Init::initSatkerInfo($satker_id, $satker);

                $id = Status::get_id_from_url($id);
                $temp = DB::table('tp_announcement')->where('announcement_id', $id)->where('announcement_status', 1)->get();
                if($temp != "[]") {
                    foreach($temp as $row) {
                        $read = array(
                            "title"      => (($row->announcement_title == null? "":$row->announcement_title)),
                            "text_in"    => (($row->announcement_text_in == null? "":$row->announcement_text_in)),
                            "text_en"    => (($row->announcement_text_en == null? "":$row->announcement_text_en)),
                            "size"       => (($row->announcement_size == null? 0:$row->announcement_size)),
                            "image"      => (($row->announcement_image == null? "":$row->announcement_image)),
                            "path"       => (($row->announcement_path == null? Init::defaultImage():$row->announcement_path)),
                            "view"       => $row->announcement_view,
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'read' => $read,
                    );

                    Dbase::processView('tp_announcement', 'announcement_view', 'announcement_id', $id);
                }
                else {
                    $rst = 0;
                    $validate = "Data tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
                $validate = "Satua kerja tidak ditemukan";  
            }
        }
        else {
            $rst = 0;
        }  

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getConferenceEvent(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;

            $limit  = $request->limit;
            $limit  = (($limit == "")?10:$limit);
            $offset = $request->offset;
            $offset = (($offset == "")?0:$offset);

            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $cnts = DB::table('tp_event')->where('satker_id', $satker_id)->where('event_status', 1)->count();
                    $temp = DB::table('tp_event')->where('satker_id', $satker_id)->where('event_status', 1)->take($limit)->skip($offset)->orderBy('event_date', 'DESC')->get();
                    foreach($temp as $row) {
                        $url = Init::backendUrl() .'api/v1/read-conference-event/'. $info['profile']['satker_slug'] .'/@'. $row->event_id .'&'. Status::str_url($row->event_title);
                        $list[] = array(
                            "id"         => $row->event_id,
                            "title"      => (($row->event_title == null? "":$row->event_title)),
                            "date"       => (($row->event_date == null? "":$row->event_date)),
                            "text_in"    => (($row->event_text_in == null? "":$row->event_text_in)),
                            "text_en"    => (($row->event_text_en == null? "":$row->event_text_en)),
                            "size"       => (($row->event_size == null? 0:$row->event_size)),
                            "image"      => (($row->event_image == null? "":$row->event_image)),
                            "path"       => (($row->event_path == null? Init::defaultImage():$row->event_path)),
                            "view"       => $row->event_view,
                            "url"        => $url,
                        );
                    }
    
                    $data = array(
                        'info'  => $info,
                        'total' => $cnts,
                        'list'  => $list,
                    );

                    $menu_id = 43;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }
    
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function readConferenceEvent($slug, $id) {
        $data = array();
        $validate = Init::initValidate(
            array('slug', 'id'), 
            array($slug, $id)
        );

        if($validate == "") {
            $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
            if($satker != "[]") {
                $rst = 1;
                $satker_id = $satker[0]->satker_id;
                $info = Init::initSatkerInfo($satker_id, $satker);

                $id = Status::get_id_from_url($id);
                $temp = DB::table('tp_event')->where('event_id', $id)->where('event_status', 1)->get();
                if($temp != "[]") {
                    foreach($temp as $row) {
                        $read = array(
                            "title"      => (($row->event_title == null? "":$row->event_title)),
                            "date"       => (($row->event_date == null? "":$row->event_date)),
                            "text_in"    => (($row->event_text_in == null? "":$row->event_text_in)),
                            "text_en"    => (($row->event_text_en == null? "":$row->event_text_en)),
                            "size"       => (($row->event_size == null? 0:$row->event_size)),
                            "image"      => (($row->event_image == null? "":$row->event_image)),
                            "path"       => (($row->event_path == null? Init::defaultImage():$row->event_path)),
                            "view"       => $row->event_view,
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'read' => $read,
                    );

                    Dbase::processView('tp_event', 'event_view', 'event_id', $id);
                }
                else {
                    $rst = 0;
                    $validate = "Data tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
                $validate = "Satua kerja tidak ditemukan";  
            }
        }
        else {
            $rst = 0;
        }  

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }


    public function getArchiveRegulation(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;

            $limit  = $request->limit;
            $limit  = (($limit == "")?10:$limit);
            $offset = $request->offset;
            $offset = (($offset == "")?0:$offset);

            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $cnts = DB::table('tp_regulation')->where('satker_id', $satker_id)->where('regulation_status', 1)->count();
                    $temp = DB::table('tp_regulation')->where('satker_id', $satker_id)->where('regulation_status', 1)->take($limit)->skip($offset)->orderBy('regulation_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $list[] = array(
                            "title"        => (($row->regulation_title == null? "":$row->regulation_title)),
                            "size"         => (($row->regulation_size == null? 0:$row->regulation_size)),
                            "file"         => (($row->regulation_file == null? "":$row->regulation_file)),
                            "path"         => (($row->regulation_path == null? "":$row->regulation_path)),
                            "description"  => (($row->regulation_description == null? "":$row->regulation_description)),
                        );
                    }
    
                    $data = array(
                        'info'  => $info,
                        'total' => $cnts,
                        'list'  => $list,
                    );

                    $menu_id = 51;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }
    
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }
    
    public function getArchivePhoto(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;

            $limit  = $request->limit;
            $limit  = (($limit == "")?10:$limit);
            $offset = $request->offset;
            $offset = (($offset == "")?0:$offset);

            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $cnts = DB::table('tp_photo')->where('satker_id', $satker_id)->where('photo_status', 1)->count();
                    $temp = DB::table('tp_photo')->where('satker_id', $satker_id)->where('photo_status', 1)->take($limit)->skip($offset)->orderBy('photo_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $list[] = array(
                            "title"        => (($row->photo_title == null? "":$row->photo_title)),
                            "size"         => (($row->photo_size == null? 0:$row->photo_size)),
                            "image"        => (($row->photo_image == null? "":$row->photo_image)),
                            "path"         => (($row->photo_path == null? Init::defaultImage():$row->photo_path)),
                            "description"  => (($row->photo_description == null? "":$row->photo_description)),
                        );
                    }
    
                    $data = array(
                        'info'  => $info,
                        'total' => $cnts,
                        'list'  => $list,
                    );

                    $menu_id = 52;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }
    
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getArchiveMovie(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;

            $limit  = $request->limit;
            $limit  = (($limit == "")?10:$limit);
            $offset = $request->offset;
            $offset = (($offset == "")?0:$offset);

            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $cnts = DB::table('tp_movie')->where('satker_id', $satker_id)->where('movie_status', 1)->count();
                    $temp = DB::table('tp_movie')->where('satker_id', $satker_id)->where('movie_status', 1)->take($limit)->skip($offset)->orderBy('movie_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $list[] = array(
                            "title"        => (($row->movie_title == null? "":$row->movie_title)),
                            "description"  => (($row->movie_description == null? "":$row->movie_description)),
                            "link"         => (($row->movie_link == null? "":$row->movie_link)),
                        );
                    }
    
                    $data = array(
                        'info'  => $info,
                        'total' => $cnts,
                        'list'  => $list,
                    );

                    $menu_id = 53;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }
    
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }


    public function getBanner(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $temp = DB::table('tp_banner')->where('satker_id', $satker_id)->where('banner_status', 1)->orderBy('banner_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $list[] = array(
                            "name"           => (($row->banner_name == null? "":$row->banner_name)),
                            "title_in"       => (($row->banner_title_in == null? "":$row->banner_title_in)),
                            "subtitle_in"    => (($row->banner_subtitle_in == null? "":$row->banner_subtitle_in)),
                            "title_en"       => (($row->banner_title_en == null? "":$row->banner_title_en)),
                            "subtitle_en"    => (($row->banner_subtitle_en == null? "":$row->banner_subtitle_en)),
                            "size"           => (($row->banner_size == null? 0:$row->banner_size)),
                            "image"          => (($row->banner_image == null? "":$row->banner_image)),
                            "path"           => (($row->banner_path == null? Init::defaultImage():$row->banner_path)),
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'list' => $list,
                    );
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }
    
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getInfografis(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $temp = DB::table('tp_infografis')->where('satker_id', $satker_id)->where('infografis_status', 1)->orderBy('infografis_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $list[] = array(
                            "name"       => (($row->infografis_name == null? "":$row->infografis_name)),
                            "link"       => (($row->infografis_link == null? "":$row->infografis_link)),
                            "size"       => (($row->infografis_size == null? 0:$row->infografis_size)),
                            "image"      => (($row->infografis_image == null? "":$row->infografis_image)),
                            "path"       => (($row->infografis_path == null? Init::defaultImage():$row->infografis_path)),
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'list' => $list,
                    );
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }
    
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getRelated(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $temp = DB::table('tp_related')->where('satker_id', $satker_id)->where('related_status', 1)->orderBy('related_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $list[] = array(
                            "name"  => (($row->related_name == null? "":$row->related_name)),
                            "link"  => (($row->related_link == null? "":$row->related_link)),
                            "size"  => (($row->related_size == null? 0:$row->related_size)),
                            "image" => (($row->related_image == null? "":$row->related_image)),
                            "path"  => (($row->related_path == null? Init::defaultImage():$row->related_path)),
                        );
                    }

                    $data = array(
                        'info' => $info,
                        'list' => $list,
                    );
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }
    
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getMedsos(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $temp = DB::table('tp_medsos')->where('satker_id', $satker_id)->where('medsos_status', 1)->orderBy('medsos_id', 'DESC')->get();
                    foreach($temp as $row) {
                        $list[] = array(
                            "type"   => (($row->medsos_type == null? "":Status::medsosName($row->medsos_type))),
                            "link"   => (($row->medsos_link == null? "":$row->medsos_link)),
                        );
                    }
    
                    $data = array(
                        'info' => $info,
                        'list' => $list,
                    );
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }
    
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getRating(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $listing = array();
                    $num = 0; $total = 0; $average = 0;
                    for($i=5; $i >= 1; $i--) {
                        $label = Status::statusRating($i);
            
                        $count = Dbase::dbGetCountByTwoId('tp_rating', 'satker_id', $satker_id, 'rating_value', $i);
                        $num   = $num + $count;
                        $listing[] = array(
                            'title' => $label,
                            'count' => $count
                        );
                    }

                    $total = Dbase::dbSumFieldById('tp_rating', 'rating_value', 'satker_id', $satker_id); 
                    if($num > 0) {
                        $average = doubleval($total) / doubleval($num);
                    }
                    else {
                        $average = 0;
                    }
    
                    $list = array(
                        'num'     => $num,
                        'total'   => $total,
                        'average' => doubleval(number_format($average, 2)),
                        'listing' => $listing
                    );

                    $data = array(
                        'info' => $info,
                        'list' => $list,
                    );
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }
    
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getVisitor(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    $now = Carbon::now();
                    
                    $dateOfYear = Carbon::createFromFormat('Y-m-d H:i:s', $now)->format('Y-m-d'); 
                    $monthOfYear = Carbon::createFromFormat('Y-m-d H:i:s', $now)->format('m'); 
                    $yearOfYear  = Carbon::createFromFormat('Y-m-d H:i:s', $now)->format('Y'); 
                    
                    $list[] = array(
                        'title' => 'Total',
                        'count' => DB::table('tr_visitor')->where('satker_id', $satker_id)->count()
                    );

                    $list[] = array(
                        'title' => $yearOfYear,
                        'count' => DB::table('tr_visitor')->where('satker_id', $satker_id)->where(DB::raw('YEAR(visitor_date)'), '=', $yearOfYear)->count()
                    );

                    $list[] = array(
                        'title' => Carbon::createFromFormat('Y-m-d H:i:s', $now)->format('M'),
                        'count' => DB::table('tr_visitor')->where('satker_id', $satker_id)->where('visitor_date', 'like', '%'.$yearOfYear .'-'. $monthOfYear.'%')->count()
                    );

                    $list[] = array(
                        'title' => Carbon::createFromFormat('Y-m-d H:i:s', $now)->format('d-m-Y'),
                        'count' => DB::table('tr_visitor')->where('satker_id', $satker_id)->where('visitor_date', $dateOfYear)->count()
                    );

                    $data = array(
                        'info' => $info,
                        'list' => $list,
                    );
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }
    
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }


    public function setRating(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug        = $request->slug;
            $ip          = $request->ip;
            $value       = $request->value;
            $description = $request->description;
            
            $validate = Init::initValidate(
                array('slug', 'Ip Address'), 
                array($slug, $ip)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker = $satker[0]->satker_id;
                    
                    $now = Carbon::now();
                    $date_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                            ->format('Y-m-d');
                    $time_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                            ->format('H:i:s');
                    
                    $rst = DB::table('tp_rating')
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
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }
    
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'Process')),
            'data'      => array()],
            200
        );
    }

    public function setNewsletter(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug   = $request->slug;
            $email  = $request->email;
            
            $validate = Init::initValidate(
                array('slug', 'Email Address'), 
                array($slug, $email)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker = $satker[0]->satker_id;
                    
                    $now = Carbon::now();
                    $date_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                            ->format('Y-m-d');
                    $time_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                            ->format('H:i:s');
                    
                    $rst = DB::table('tp_newsletter')
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
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }
    
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'Process')),
            'data'      => array()],
            200
        );
    }

    public function setContactus(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug    = $request->slug;
            $name    = $request->name;
            $email   = $request->email;
            $subject = $request->subject;
            $message = $request->message;
            
            $validate = Init::initValidate(
                array('slug', 'name', 'email'), 
                array($slug, $name, $email)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker = $satker[0]->satker_id;
                    
                    $now = Carbon::now();
                    $date_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                            ->format('Y-m-d');
                    $time_at = Carbon::createFromFormat('Y-m-d H:i:s', $now)
                            ->format('H:i:s');
                    
                    $rst = DB::table('tp_contactus')
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
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }
    
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'Contact')),
            'data'      => array()],
            200
        );
    }


    public function getContactus(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $data = array(
                        'info' => $info,
                    );

                    $menu_id = 7;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getHome(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug    = $request->slug;
            $version = $request->version;
            
            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    DB::table('tm_satker')
                        ->where('satker_slug', $slug)
                        ->update([
                            "satker_version"  => $version
                        ]);
                    
                    $banner = array();
                    $slider = DB::table('tp_banner')->where('satker_id', $satker_id)->where('banner_status', 1)->orderBy('banner_id', 'DESC')->get();
                    foreach($slider as $row) {
                        $banner[] = array(
                            "name"           => (($row->banner_name == null? "":$row->banner_name)),
                            "title_in"       => (($row->banner_title_in == null? "":$row->banner_title_in)),
                            "subtitle_in"    => (($row->banner_subtitle_in == null? "":$row->banner_subtitle_in)),
                            "title_en"       => (($row->banner_title_en == null? "":$row->banner_title_en)),
                            "subtitle_en"    => (($row->banner_subtitle_en == null? "":$row->banner_subtitle_en)),
                            "size"           => (($row->banner_size == null? 0:$row->banner_size)),
                            "image"          => (($row->banner_image == null? "":$row->banner_image)),
                            "path"           => (($row->banner_path == null? Init::defaultImage():$row->banner_path)),
                        );
                    }

                    $unit = array();
                    $organization = DB::table('tp_unit')->where('satker_id', $satker_id)->where('unit_status', 1)->orderBy('unit_id', 'DESC')->get();
                    foreach($organization as $row) {
                        $unit[] = array(
                            "id"       => $row->unit_id,
                            "title"    => (($row->unit_title == null? "":$row->unit_title)),
                            "text_in"  => (($row->unit_text_in == null? "":$row->unit_text_in)),
                            "text_en"  => (($row->unit_text_en == null? "":$row->unit_text_en)),
                        );
                    }

                    $infografis = array();
                    $grafis = DB::table('tp_infografis')->where('satker_id', $satker_id)->where('infografis_status', 1)->orderBy('infografis_id', 'DESC')->get();
                    foreach($grafis as $row) {
                        $infografis[] = array(
                            "name"           => (($row->infografis_name == null? "":$row->infografis_name)),
                            "link"           => (($row->infografis_link == null? "":$row->infografis_link)),
                            "size"           => (($row->infografis_size == null? 0:$row->infografis_size)),
                            "image"          => (($row->infografis_image == null? "":$row->infografis_image)),
                            "path"           => (($row->infografis_path == null? Init::defaultImage():$row->infografis_path)),
                        );
                    }

                    $service = array();
                    $offer = DB::table('tp_service')->where('satker_id', $satker_id)->where('service_status', 1)->orderBy('service_id')->get();
                    foreach($offer as $row) {
                        $service[] = array(
                            "title"          => (($row->service_title == null? "":$row->service_title)),
                            "link"           => (($row->service_link == null? "":$row->service_link)),
                            "size"           => (($row->service_size == null? 0:$row->service_size)),
                            "image"          => (($row->service_image == null? "":$row->service_image)),
                            "path"           => (($row->service_path == null? Init::defaultImage():$row->service_path)),
                        );
                    }

                    $news = array();
                    //$article = DB::table('tp_news')->where('satker_id', $satker_id)->where('news_category', 'Berita')->where('news_status', 1)->limit(4)->orderBy('news_date', 'DESC')->get();
                    $article = DB::table('tp_news')->where('satker_id', $satker_id)->where('news_category', 'Berita')->where('news_status', 1)->limit(2)->orderBy('news_date', 'DESC')->get();
                    foreach($article as $row) {
                        $satker_name = Dbase::dbGetFieldById('tm_satker', 'satker_name', 'satker_id', $row->satker_id);
                        $news[] = array(
                            "id"         => (($row->news_id == null? "":$row->news_id)),
                            "title"      => (($row->news_title == null? "":$row->news_title)),
                            "date"       => (($row->news_date == null? "":$row->news_date)),
                            "category"   => (($row->news_category == null? "":$row->news_category)),
                            "titile"     => (($row->news_title == null? "":$row->news_title)),
                            "text_in"    => (($row->news_text_in == null? "":$row->news_text_in)),
                            "text_en"    => (($row->news_text_en == null? "":$row->news_text_en)),
                            "size"       => (($row->news_size == null? 0:$row->news_size)),
                            "image"      => (($row->news_image == null? "":$row->news_image)),
                            "path"       => (($row->news_path == null? Init::defaultImage():$row->news_path)),
                            "view"       => $row->news_view,
                            "satker"     => $satker_name,
                        );
                    }

                    if($news != "") {
                        $article = DB::table('tp_news')->where('news_category', 'Berita')->where('news_status', 1)->where('news_broadcast', 1)->limit(4)->orderBy('news_date', 'DESC')->get();
                    }
                    else {
                        $article = DB::table('tp_news')->where('news_category', 'Berita')->where('news_status', 1)->where('news_broadcast', 1)->limit(6)->orderBy('news_date', 'DESC')->get();
                    }
                    
                    foreach($article as $row) {
                        $satker_name = Dbase::dbGetFieldById('tm_satker', 'satker_name', 'satker_id', $row->satker_id);
                        $news[] = array(
                            "id"         => (($row->news_id == null? "":$row->news_id)),
                            "title"      => (($row->news_title == null? "":$row->news_title)),
                            "date"       => (($row->news_date == null? "":$row->news_date)),
                            "category"   => (($row->news_category == null? "":$row->news_category)),
                            "titile"     => (($row->news_title == null? "":$row->news_title)),
                            "text_in"    => (($row->news_text_in == null? "":$row->news_text_in)),
                            "text_en"    => (($row->news_text_en == null? "":$row->news_text_en)),
                            "size"       => (($row->news_size == null? 0:$row->news_size)),
                            "image"      => (($row->news_image == null? "":$row->news_image)),
                            "path"       => (($row->news_path == null? Init::defaultImage():$row->news_path)),
                            "view"       => $row->news_view,
                            "satker"     => $satker_name,
                        );
                    }

                    $data = array(
                        'info'       => $info,
                        'banner'     => $banner,
                        'unit'       => $unit,
                        'infografis' => $infografis,
                        'service'    => $service,
                        'news'       => $news,
                    );

                    $menu_id = 1;
                    Dbase::processVisitor($request->ip, $satker_id, $menu_id);
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function getSearch(Request $request) {
        $data = array();
        if(Init::checkHeader($request)) {
            $slug = $request->slug;

            $limit  = $request->limit;
            $limit  = (($limit == "")?10:$limit);
            $offset = $request->offset;
            $offset = (($offset == "")?0:$offset);

            $validate = Init::initValidate(
                array('slug'), 
                array($slug)
            );
    
            if($validate == "") {
                $satker = DB::table('tm_satker')->where('satker_status', 1)->where('satker_slug', $slug)->get();
                if($satker != "[]") {
                    $rst = 1;
                    $satker_id = $satker[0]->satker_id;
                    $info = Init::initSatkerInfo($satker_id, $satker);
    
                    $list = array();
                    if($request->keyword != "") {
                        $temp = DB::table('tv_content')->where('satker_id', $satker_id)->where('content_text_in', 'like', '%'.$request->keyword.'%')->take($limit)->skip($offset)->orderBy('content_id', 'DESC')->get();
                        $cnts = DB::table('tv_content')->where('satker_id', $satker_id)->where('content_text_in', 'like', '%'.$request->keyword.'%')->count();
                    }
                    else {
                        $temp = DB::table('tv_content')->where('satker_id', $satker_id)->take($limit)->skip($offset)->orderBy('content_id', 'DESC')->get();
                        $cnts = DB::table('tv_content')->where('satker_id', $satker_id)->count();
                    }

                    foreach($temp as $row) {
                        $date = Carbon::createFromFormat('Y-m-d', $row->content_date)
                            ->format('d-m-Y');
                        $time = Carbon::createFromFormat('H:i:s', $row->content_time)
                            ->format('H:i:s');
                            
                        $list[] = array(
                            "id"       => $row->reff_id,
                            "date"     => $date,
                            "time"     => $time,
                            "text_in"  => (($row->content_text_in == null? "":$row->content_text_in)),
                            "text_en"  => (($row->content_text_en == null? "":$row->content_text_en)),
                            "menu_url" => $row->menu_url,
                            "menu_name"=> $row->menu_name,
                            "satker"   => $row->satker_name,
                        );
                    }
    
                    $data = array(
                        'info'  => $info,
                        'total' => $cnts,
                        'list'  => $list,
                    );
                }
                else {
                    $rst = 0;
                    $validate = "Satua kerja tidak ditemukan";  
                }
            }
            else {
                $rst = 0;
            }  
        }
        else {
            $rst = 0;
            $validate = "Kesalahan header token";  
        }

        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => (($validate != "")?$validate:Init::responseMessage($rst, 'View')),
            'data'      => $data],
            200
        );
    }

    public function menuNavigation(Request $request) {
        $slug = $request->slug;

        $menus = array();
        $menus[] = dBase::getParentNavigationHome();         
        $access = dBase::getAccesssNavigationMenu($slug);
        if(!empty($access)) {
            $menus = dBase::getParentNavigationMenu();
        }
        
        $parent = array();
        foreach($menus as $row) {
            $child = Dbase::getChildNavigationMenu($row->menu_id);
            $parent[] = array(
                "menu_id"           => $row->menu_id,
                "menu_name"         => $row->menu_name,
                "menu_label"        => $row->menu_label,
                "menu_description"  => $row->menu_description,
                "menu_icon"         => $row->menu_icon,
                "menu_url"          => $row->menu_url,
                "menu_nav"          => $row->menu_nav,
                "menu_active"       => $row->menu_active,
                "menu_parent"       => $row->menu_parent,
                "menu_status"       => $row->menu_status,
                "is_deleted"        => $row->is_deleted,
                "created_at"        => $row->created_at,
                "updated_at"        => $row->updated_at,
                "last_user"         => $row->last_user,
                'child'             => $child
            );
        }

        $is_mega = 0;
        $arr_not_mega = array(3,4,5,6);
        for($iu=0; $iu<count($access); $iu++) {
            if (in_array($access[$iu], $arr_not_mega)) {
                $is_mega = 1;
            }
        }

        $data = array(
            'access'  => $access,
            'parent'  => $parent,
            'is_mega' => $is_mega,
        );
        
        $rst = 1;
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => Init::responseMessage($rst, 'View'),
            'data'      => $data],
            200
        );
    }

    public function menuAccess(Request $request) {
        $slug = $request->slug;
        $data = dBase::getAccesssNavigationMenu($slug);
        
        $rst = 1;
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => Init::responseMessage($rst, 'View'),
            'data'      => $data],
            200
        );
    }

    public function resetUser($account) {
        $user_id = Dbase::dbGetFieldById('tm_user', 'user_id', 'user_account', $account);
        if($user_id != "") {
            $rst = DB::table('tm_user')
                ->where('user_id', $user_id)
                ->update([
                    "user_count"  => 0,
                    "user_status" => 1
                ]); 
        }
        else {
            $rst = 0;
        }
            
        return response()->json([
            'status'    => Init::responseStatus($rst),
            'message'   => Init::responseMessage($rst, 'Logout'),
            'data'      => array()],
            200
        );
    }
}
