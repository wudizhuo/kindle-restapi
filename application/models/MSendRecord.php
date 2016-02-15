<?php

class MSendRecord extends CI_Model
{

    protected $table = "send_record";

    function __construct()
    {
        parent::__construct($this->table);
    }

    function create_record($app_uid, $app_version, $sendHtmlEntity, $status = 0)
    {
        $info = array(
            'app_uid' => $app_uid,
            'curr_version' => $app_version,
            'created_time' => $this->input->server('REQUEST_TIME'),
            'from_email' => $sendHtmlEntity->getFromEmail(),
            'to_email' => $sendHtmlEntity->getToEmail(),
            'url' => $sendHtmlEntity->getUrl(),
            'title' => $sendHtmlEntity->getArticleTitle(),
            'status' => $status
        );
        $this->db->insert($this->table, $info);
    }

    function getLastMinUrlByUId($app_uid)
    {
        $query = $this->db->query('SELECT url FROM ' . $this->table . ' where app_uid = ' . $app_uid . ' and (created_time +300) > unix_timestamp()');

        $res = $query->result_array();
        if (isset($res[0])) {
            return $res[0]['url'];
        } else {
            return false;
        }
    }

    /**
     * URL去重 防止URL 重复发送 return true 表示URL已经在最近发送过，要做不再发送处理
     */
    function isUrlDuplicate($app_uid, $url)
    {
        $query = $this->db->query('SELECT created_time FROM ' . $this->table . ' where app_uid = ' . $app_uid . ' and url = "' . $url . '" order by created_time desc limit 1');

        $res = $query->result_array();
        if ((!empty($res)) && end($res)['created_time'] > (time() - 120)) {
            return true;
        } else {
            return false;
        }
    }
}
