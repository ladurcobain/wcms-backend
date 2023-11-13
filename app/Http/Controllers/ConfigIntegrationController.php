<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Init;
use App\Helpers\Dbase;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class ConfigIntegrationController extends Controller
{
    private $table = "tc_integration";
    private $field = "integration_id";

    public function getSingle(Request $request) {
        $id   = $request->integration_id;
        $data = DB::table($this->table)->where($this->field, $id)->get();
        $arr  = Init::initConfigIntegration($data);

        return Init::initResponse($arr, 'View');
    }

    public function updateData(Request $request) {
        $id         = $request->integration_id;
        $backend    = $request->backend;
        $last_user  = $request->last_user;
        
        $validate = Init::initValidate(
            array('id', 'backend'), 
            array($id, $backend)
        );

        if($validate == "") {
            $rst = DB::table($this->table)
                ->where($this->field, $id)
                ->update([
                    "integration_backend" => $backend,
                    "last_user"           => Dbase::dbGetFieldById('tm_user', 'user_fullname', 'user_id', $last_user),
                ]);          
        
            dBase::setLogActivity($rst, $last_user, Carbon::now(), 'Update', 'Ubah data config integration');    
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