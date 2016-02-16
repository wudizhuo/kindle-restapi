<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

//require_once('./application/libraries/REST_Controller.php');

include './application/libraries/REST_Controller.php';
include './application/controllers/rest-api/entity/SendHtmlEntity.php';
include './application/utils/CurlUtil.php';
include './application/utils/UrlParseAdapter.php';
include './application/utils/HtmlExtract.php';

class Send extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array(
            'MSendRecord'
        ));
    }

    public function index_post()
    {
        $fromEmail = $this->post('from_email');

        if (!$this->email->valid_email($fromEmail)) {
            $res["status"] = "3";
            $res["msg"] = "请填写正确的发送邮箱";
            $this->_return($res);
        }

        $toEmail = $this->post('to_email');

        if (!$this->email->valid_email($toEmail)) {
            $res["status"] = "3";
            $res["msg"] = "请填写正确的接收邮箱";
            $this->_return($res);
        }

        $url = UrlParseAdapter::parse_url($this->post('url'));

        if (!$this->valid_url($url)) {
            $res["status"] = "2";
            $res["msg"] = "请填写正确的网址";
            $this->createRecord($status = $res["status"], $url, $fromEmail, $toEmail);
            $this->_return($res);
        }

        $app_id = $this->appsecurity->check_app_id();

        $isUrlDuplicate = $this->MSendRecord->isUrlDuplicate($app_id, $url);

        //1分钟内已经发送过 不再发送
        if ($isUrlDuplicate) {
            $res["msg"] = '发送成功';
            $this->_return($res);
        }

        $htmlModel = HtmlExtract::getReadabilityHtml($url);

        if (!$htmlModel) {
            $res["status"] = "1";
            $res["msg"] = '没有找到要发送的内容';
            $this->createRecord($status = $res["status"], $url, $fromEmail, $toEmail);
            $this->_return($res);
        }

        $sendHtmlEntity = new SendHtmlEntity($url, $fromEmail, $toEmail, $htmlModel->getArticleTitle(), $htmlModel->getArticleContent());

        $this->createRecord(0, $url, $fromEmail, $toEmail, $htmlModel->getArticleTitle(), $htmlModel->getArticleContent());

        $readabilityHtml = $sendHtmlEntity->toHtml();

        $this->email->from($fromEmail, 'kindle_助手');

        $this->email->to($toEmail);

        $this->email->bcc('kindleassistant@126.com');

        $this->email->subject('convert');

        $mobiPath = $this->genMobi($sendHtmlEntity->saveHtml2Local());

        if (file_exists($mobiPath)) {
            $this->email->attach($mobiPath);
        } else {
            $this->email->attach_content($htmlModel->getArticleTitle(), $readabilityHtml);
        }

        if ($this->email->send()) {
            $res["status"] = 0;
        } else {
            $res["status"] = -1;
        }

        // $this->email->print_debugger();

        $this->_return($res);
    }

}