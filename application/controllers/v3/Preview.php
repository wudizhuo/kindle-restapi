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

class Preview extends REST_Controller
{
    public function index_post()
    {
        $url = UrlParseAdapter::parse_url($this->post('url'));

        if (!UrlUtil::valid_url($url)) {
            $res["error"] ="请填写正确的网址";
            $this->response($res, 400);
        }

        $htmlModel = HtmlExtract::getReadabilityHtml($url);

        if ($htmlModel) {
            $res['content'] = $htmlModel->toHtml();
            $this->response($res, 201);
        } else {
            $res["error"] ='没有找到要显示的内容';
            $this->response($res, 404);
        }

        $this->_return($res);
    }

}