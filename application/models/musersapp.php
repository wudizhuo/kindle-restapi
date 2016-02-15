<?php
class MUsersApp extends MY_Model {
    protected $table = "users_app";

    function __construct() {
        parent::__construct($this->table);
    }

    public function get_by_uid($users_app_uid){
        $this->db->select();
        $this->db->where('app_uid', $users_app_uid);
        $res = $this->db->get($this->table);
        return $res ? $res->first_row() : array();
    }

    function create_by_imei($app_id, $imei,$mac, $app_version){
        $user = $this->check_exists_by_imei($imei);
        //做逻辑判断 如果imei 存在就校验mac 
        
        if(count($user) > 0){
            $app_uid = $user[0]->app_uid;

            return $app_uid;
        }

        $info = array(
            'imei'         => $imei,
            'mac'         => $mac,
            'curr_version'  => $app_version,
            'status'        => 1,
            'created_time'  => $this->input->server('REQUEST_TIME'),
            'updated_time'  => $this->input->server('REQUEST_TIME')
        );
        $res = $this->db->insert($this->table, $info);
        if($res){
            //如果插入成功，则更新app_uid
            //app_uid和id一致
            $id = $this->db->insert_id();
            $this->db->where('app_uid',$id);
            $this->db->update($this->table,
                array(
                    'app_uid'=>$id,
                )
            );
        }
        return $id;
    }

    function update_session_key($app_uid,$app_uid,$session_id) {
        $this->db->where(array('app_uid'=>$app_uid,'app_uid'=>$app_uid));
        $this->db->update($this->table,array('secure_key'=>$session_id));
        return $this->db->affected_rows();
    }

    function update_channel($app_uid,$app_uid,$channel) {
        $this->db->where(array('app_uid'=>$app_uid,'app_uid'=>$app_uid));
        $this->db->update($this->table,array('channel'=>$channel));
        return $this->db->affected_rows();
    }

    function update_deviceimei($app_uid,$app_uid,$deviceimei) {
        $this->db->where(array('app_uid'=>$app_uid,'app_uid'=>$app_uid));
        $this->db->update($this->table,array('imei'=>$deviceimei));
        return $this->db->affected_rows();
    }

    function check_exists_by_imei($imei) {
        $this->db->where(array('imei' => $imei));
        $this->db->where('status',1) ;
        $this->db->from($this->table);
        $query = $this->db->get();

        return $query->result();
    }

    function check_channel_exists($app_uid,$app_uid) {
        $this->db->where(array('app_uid'=>$app_uid,'app_uid'=>$app_uid));
        $query = $this->db->get($this->table);

        $res = $query->result_array();
        if(count($res) > 0){
            if(strlen($res[0]['channel']) > 0){
                return TRUE;
            }
        }

        return FALSE;
    }

    function check_user_app_version($app_uid,$app_uid) {
        $this->db->where(array('app_uid'=>$app_uid,'app_uid'=>$app_uid));
        $query = $this->db->get($this->table);

        $res = $query->first_row();
        if($res){
            return $res->curr_version;
        }

        return '';
    }

    function update_user_app_version($app_uid, $app_uid, $app_version) {
        $this->db->where(array('app_uid'=> $app_uid,'app_uid'=> $app_uid));
        $this->db->update($this->table,array('curr_version'=> $app_version));
    }
}
