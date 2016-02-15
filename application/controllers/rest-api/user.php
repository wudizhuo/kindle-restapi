<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(array('MUsersApp'));
    }

    public function create() {
        $res = array("status"=>-1,"msg"=>"");

        $code = file_get_contents("php://input");
        $j = json_decode($code,True);

        if($j == NULL){
            $res["msg"] = "decode post json failed";
            $this->_return($res);
        }

        if(!isset($j["imei"])){
            $res["msg"] = "post imei or device token not exist";
            $this->_return($res);
        }

        if(!isset($j["mac"])){
            $res["msg"] = "post mac or device token not exist";
            $this->_return($res);
        }

        $imei = trim($j["imei"]);

        $mac = trim($j["mac"]);
        //For Android,
        //IMEI:15~17 http://www.imeidb.com/imei-structure
        //MEID:14, for cdma
        //the IMEI for GSM and the MEID or ESN for CDMA phones
        //http://www.smartmobilephonesolutions.com/content/imei-vs-meid-vs-esn-purpose-of-a-device-identifier
        //For iOS,
        //Device Token
        if(strlen($imei) < 14){
            $res["msg"] = "imei/meid/device token length(14~17) error:".strlen($imei);
            $this->_return($res);
        }

        $app_id = $this->appsecurity->check_app_id();
        $app_version = $this->appsecurity->check_app_version();
        $app_uid = $this->MUsersApp->create_by_imei($app_id, $imei,$mac, $app_version);
        if($app_uid === false){
            $res["msg"] = "create by imei failed";
            $this->_return($res);
        }

        $res["app_uid"] = $app_uid;
        $res['status'] = 0;

        $this->_return($res);
    }
   public function update_email(){
        $j = $this->_get_input();
        $app_uid = $this->appsecurity->check_app_id();
        $res['status'] = -1;
        if (! $this->email->valid_email($j['email'])) {
            $res["msg"] = "请填写正确的邮箱";
            $this->_return($res);
        }
        $this->MUsersApp->update_by('app_uid', $app_uid, array('email' => $j['email']));
        $this->_return(array('status' => 0));
    }

}
