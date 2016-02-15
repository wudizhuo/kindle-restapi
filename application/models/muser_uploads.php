<?php
class MUser_Uploads extends MY_Model {
    protected $table = "user_uploads";

    function __construct() {
        parent::__construct($this->table);
    }

    public function get_by_uid($users_app_uid){
        $this->db->select();
        $this->db->where('app_uid', $users_app_uid);
        $res = $this->db->get($this->table);
        return $res ? $res->result_array() : array();
    }
}
