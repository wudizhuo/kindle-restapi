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
        $htmlPath = tempnam(sys_get_temp_dir(), 'kindle');

        // 匹配图片下载正则
        $pattern_src = '/<img[\s\S]*?src\s*=\s*[\"|\'](.*?)[\"|\'][\s\S]*?>/';

        // 正则替换图片
        $html = preg_replace_callback($pattern_src, "self::changeImgLocal", $this->toHtml());

        $fp = @fopen($htmlPath, "w"); // 以写方式打开文件
        @fwrite($fp, $html);
        fclose($fp);

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

        $img = UrlUtil::get_content($imgurl);
        if (!empty($img)) {
            $imgPath = tempnam(sys_get_temp_dir(), 'kindle_img');

            $fp = @fopen($imgPath, "w"); // 以写方式打开文件
            @fwrite($fp, $img);
            fclose($fp);
        }

        return '<img src=' . $imgPath . '>';
    }
}