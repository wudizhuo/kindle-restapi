<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class Uploads extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        header('Content-Type: text/html; charset=utf-8');
    }

    /**
     * 根据发送过来的URL 推送到对应的邮箱
     */
    function index()
    {
        $config['upload_path'] = './uploads/' . date('y-m');
        $config['allowed_types'] = 'gif|jpg|jpeg|png|bmp|txt|pdf|mobi|azw|rtf|html|doc|docx';
        $config['max_size'] = 0;//50兆 ;
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        $j = $this->input->post();
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0744);
        }
        if (!$this->upload->do_upload('file')) {
            $error = array('error' => $this->upload->display_errors());
            $this->load->model('MUser_Upload_error_logs');
            $insert = $this->MUser_Upload_error_logs->add(
                array(
                    'app_uid' => isset($j['app_uid']) ? $j['app_uid'] : '',
                    'error_reason' => $error['error'],
                    'from_email' => $j['from_email'],
                    'to_email' => $j['to_email'],
                    'file_name' => isset($j['file_name']) ? $j['file_name'] : '',
                    'created_time' => time(),
                )

            );
            $this->_return(array('status' => -1, 'msg' => $error['error']));

        } else {
            $this->load->model('MUser_Uploads');
            $data = $this->upload->data();
            $insert = $this->MUser_Uploads->add(
                array(
                    'app_uid' => isset($j['app_uid']) ? $j['app_uid'] : '',
                    'from_email' => $j['from_email'],
                    'to_email' => $j['to_email'],
                    'file_path' => $data['file_path'],
                    'full_path' => $data['full_path'],
                    'file_type' => $data['file_type'],
                    'file_name' => $data['file_name'],
                    'file_size' => $data['file_size'],
                    'created_time' => time(),
                )
            );
            $path = explode('/', $data['full_path']);
            unset($path[0]);
            unset($path[1]);
            unset($path[2]);
            $path = implode('/', $path);
            if ($insert > 0) {
                $this->email->from($j['from_email'], 'kindle_assistant');
                $this->email->to($j['to_email']);
                $this->email->bcc('kindleassistant@126.com');
                $this->email->attach($path);
                $this->email->subject($data['file_name'] . '__用户上传');
                $this->email->send();
                $this->_return(array('status' => 0, 'full_path' => $path, 'num' => $insert));
            } else {
                $this->_return(array('status' => -1));

            }
        }
    }
}
