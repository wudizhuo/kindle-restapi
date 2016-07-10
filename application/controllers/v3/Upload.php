<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

//require_once('./application/libraries/REST_Controller.php');

include './application/libraries/REST_Controller.php';
include './application/controllers/v3/entity/SendHtmlEntity.php';
include './application/utils/UrlParseAdapter.php';
include './application/utils/HtmlExtract.php';
include './application/utils/UrlUtil.php';

class Upload extends REST_Controller
{
    public function index_post()
    {
        $fromEmail = $this->post('from_email');
        if (!$this->email->valid_email($fromEmail)) {
            $res["code"] = ERROR_CODE_FROM_EMAIL;
            $res["error"] = "请填写正确的发送邮箱";
            $this->response($res, 400);
        }

        $toEmail = $this->post('to_email');

        if (!$this->email->valid_email($toEmail)) {
            $res["code"] = ERROR_CODE_TO_EMAIL;
            $res["error"] = "请填写正确的接收邮箱";
            $this->response($res, 400);
        }

        $fileName = $this->post('file_name');

        $config['upload_path'] = './uploads/' . date('y-m');
        $config['allowed_types'] = 'gif|jpg|jpeg|png|bmp|txt|pdf|mobi|azw|rtf|html|doc|docx';
        $config['max_size'] = 0;//50兆 ;
        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0744);
        }

        if (!$this->upload->do_upload('file')) {
            $error = array('error' => $this->upload->display_errors());
            // $this->load->model('MUser_Upload_error_logs');
            // $insert = $this->MUser_Upload_error_logs->add(
            //     array(
            //         'app_uid' => 0,
            //         'error_reason' => $error['error'],
            //         'from_email' => $fromEmail,
            //         'to_email' => $toEmail,
            //         'file_name' => $fileName,
            //         'created_time' => time(),
            //     )
            // );
            $res["error"] = '文件格式不支持,请检查文件格式';
            $res["msg"] = $error['error'];
            $this->response($res, 404);
        } else {
            $this->load->model('MUser_Uploads');
            $data = $this->upload->data();
            // $insert = $this->MUser_Uploads->add(
            //     array(
            //         'app_uid' => 0,
            //         'from_email' => $fromEmail,
            //         'to_email' => $toEmail,
            //         'file_path' => $data['file_path'],
            //         'full_path' => $data['full_path'],
            //         'file_type' => $data['file_type'],
            //         'file_name' => $data['file_name'],
            //         'file_size' => $data['file_size'],
            //         'created_time' => time(),
            //     )
            // );
            $path = $data['full_path'];
            $this->email->clear(TRUE);
            $this->email->from($fromEmail, 'kindle_assistant');
            $this->email->to($toEmail);
            $this->email->bcc('kindleassistant@126.com');
            $this->email->attach($path);
            $this->email->subject($data['file_name'] . '__用户上传');

            if ($this->email->send()) {
                $this->response(null, 201);
            } else {
                $res["error"] = '发送失败,请联系作者';
                $res["exception"] = $this->email->print_debugger(array('headers'));
                $this->response($res, 500);
            }
        }
    }
}