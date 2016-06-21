<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

//require_once('./application/libraries/REST_Controller.php');

include './application/libraries/REST_Controller.php';
include './application/controllers/v3/entity/SendHtmlEntity.php';
include './application/utils/CurlUtil.php';
include './application/utils/UrlParseAdapter.php';
include './application/utils/HtmlExtract.php';
include './application/utils/UrlUtil.php';
include './application/utils/Snoopy.class.php';

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
            $res["code"] = ERROR_CODE_FROM_EMAIL;
            $res["error"] = "请填写正确的发送邮箱  error" + $fromEmail;
            $this->response($res, 400);
        }

        $toEmail = $this->post('to_email');

        if (!$this->email->valid_email($toEmail)) {
            $res["code"] = ERROR_CODE_TO_EMAIL;
            $res["error"] = "请填写正确的接收邮箱";
            $this->response($res, 400);
        }

        $url = UrlParseAdapter::parse_url($this->post('url'));

        if (!UrlUtil::valid_url($url)) {
            $res["code"] = ERROR_CODE_INVALID_URL;
            $res["error"] = "请填写正确的网址";
            $this->createRecord($status = 2, $url, $fromEmail, $toEmail);
            $this->response($res, 400);
        }

        //TODO 配置数据库操作 code
        //TODO 当数据失败时候  数据库保存的status 值的修改

        //TODO code next code 获取app _id 的操作
        $app_id = 0;

        // $isUrlDuplicate = $this->MSendRecord->isUrlDuplicate($app_id, $url);

        // //1分钟内已经发送过 不再发送
        // if ($isUrlDuplicate) {
        //     $this->response(null, 201);
        // }

        $htmlModel = HtmlExtract::getReadabilityHtml($url);

        if (!$htmlModel) {
            $res["error"] = '没有找到要发送的内容';
            //TODO code create Record status change code
            $this->createRecord($status = 1, $url, $fromEmail, $toEmail);
            $this->response($res, 404);
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
            $this->response(null, 201);
        } else {
            $res["error"] = '发送失败,请联系作者';
            $this->response($res, 500);
        }
    }

    private function createRecord($status = 0, $url, $fromEmail, $toEmail, $title = "", $content = "")
    {
//        $app_id = $this->appsecurity->check_app_id();
        //TODO code next code 获取app _id 的操作
        $app_id = 0;
        //TODO code next code 获取version 的操作
//        $app_version = $this->appsecurity->check_app_version();
        $app_version = "";
        $sendHtmlEntity = new SendHtmlEntity($url, $fromEmail, $toEmail, $title, $content);
        // $this->MSendRecord->create_record($app_id, $app_version, $sendHtmlEntity, $status);
    }

    public function genMobi($htmlPath)
    {
        $htmlPathTmp = $htmlPath . ".html";
        rename($htmlPath, $htmlPathTmp);
        exec('kindlegen ' . "$htmlPathTmp", $log);
        return str_replace(".html", ".mobi", $htmlPathTmp);
    }

}