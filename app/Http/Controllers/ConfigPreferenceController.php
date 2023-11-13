<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Init;
use App\Helpers\Dbase;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class ConfigPreferenceController extends Controller
{
    private $table = "tc_preference";
    private $field = "preference_id";

    public function getSingle(Request $request) {
        $id   = $request->preference_id;
        $data = DB::table($this->table)->where($this->field, $id)->get();
        $arr  = Init::initConfigPreference($data);

        return Init::initResponse($arr, 'View');
    }

    public function updateData(Request $request) {
        $id           = $request->preference_id;
        $name         = $request->name;
        $icon         = $request->icon;
        $description  = $request->description;
        $last_user    = $request->last_user;
        
        $validate = Init::initValidate(
            array('id', 'name'), 
            array($id, $name)
        );
  
        if($validate == "") {
            $rst = DB::table($this->table)
                ->where($this->field, $id)
                ->update([
                    "preference_appname"        => $name,
                    "preference_appicon"        => (($icon == null)? "":$icon),
                    "preference_appdescription" => (($description == null)? "":nl2br($description)),
                    "last_user"                 => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]);          

            dBase::setLogActivity($rst, $last_user, Carbon::now(), 'Update', 'Ubah data config preference');    
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

}