<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class SendHtmlEntity
{

    private $articleTitle;

    private $articleContent;

    private $url;

    private $fromEmail;

    private $toEmail;

    function __construct($url, $fromEmail, $toEmail, $articleTitle, $articleContent)
    {
        $this->url = $url;
        $this->fromEmail = $fromEmail;
        $this->toEmail = $toEmail;
        $this->articleTitle = $articleTitle;
        $this->articleContent = $articleContent;

        $this->CI = &get_instance();
    }

    /**
     * 转换为可以版本的html
     *
     * @param unknown $articleTitle
     * @param unknown $articleContent
     * @return unknown
     */
    function toHtml()
    {
        $data = array(
            'articleTitle' => $this->articleTitle,
            'articleContent' => $this->articleContent
        );
        $result = $this->CI->load->view('readabliity_html', $data, true);
        return $result;
    }

    /**
     *
     * @return the $articleTitle
     */
    public function getArticleTitle()
    {
        if (empty($this->articleTitle)) {
            $this->articleTitle = "Kindle助手推送";
        } else
            if (strlen($this->articleContent) > 220) {
                $this->articleTitle = substr($this->articleTitle, 0, 220);
            }
        return str_replace('/', ' ', $this->articleTitle);
    }

    /**
     *
     * @return the $articleContent
     */
    public function getArticleContent()
    {
        return $this->articleContent;
    }

    /**
     *
     * @return the $url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     *
     * @return the $toEmail
     */
    public function getToEmail()
    {
        return $this->toEmail;
    }

    /**
     *
     * @return the $fromEmail
     */
    public function getFromEmail()
    {
        return $this->fromEmail;
    }

    public function saveHtml2Local()
    {
        $tmpHandle = tmpfile();
        $htmlPath = stream_get_meta_data($tmpHandle)['uri'];

        // 匹配图片下载正则
        $pattern_src = '/<img[\s\S]*?src\s*=\s*[\"|\'](.*?)[\"|\'][\s\S]*?>/';

        // 正则替换图片
        $html = preg_replace_callback($pattern_src, "self::changeImgLocal", $this->toHtml());

        @fwrite($tmpHandle, $html);
        @fseek($tmpHandle, 0);

        return $htmlPath;
    }

    /**
     * 改变img为本地img
     *
     * @param unknown $matches
     * @return string
     */
    private function changeImgLocal($matches)
    {
        $imgurl = $matches[1];
        if (!is_int(strpos($imgurl, 'http'))) {
            return;
        }
        // $img=file_get_contents($imgurl);
        $img = CurlUtil::curl($imgurl);
        if (!empty($img)) {
            // 保存图片 需要加后缀 如果有问题需要判断具体是jpg或png现在写固定为png
            $tmpHandle = tmpfile();
            $imgPath = stream_get_meta_data($tmpHandle)['uri'];
            @fwrite($tmpHandle, $img);
            @fseek($tmpHandle, 0);
        }

        return '<img src=' . $imgPath . '>';
    }
}