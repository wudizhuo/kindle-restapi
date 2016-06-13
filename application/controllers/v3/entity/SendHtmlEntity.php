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
        $htmlPath = './html_record/';
        if (!is_dir($htmlPath)) {
            mkdir($htmlPath, 0744);
        }

        $htmlPath = $htmlPath . date('y-m'). '/';
        if (!is_dir($htmlPath)) {
            mkdir($htmlPath, 0744);
        }

        $htmlPath = $htmlPath . date('d') . '/';
        if (!is_dir($htmlPath)) {
            mkdir($htmlPath, 0744);
        }

        // 匹配图片下载正则
        $pattern_src = '/<img[\s\S]*?src\s*=\s*[\"|\'](.*?)[\"|\'][\s\S]*?>/';

        preg_match_all($pattern_src, $this->toHtml(), $match);

        // 下载图片并保存
        foreach ($match[1] as $imgurl) {
            if (!is_int(strpos($imgurl, 'http'))) {
                continue;
            }
            // $img=file_get_contents($imgurl);
            $img = CurlUtil::curl($imgurl);
            if (!empty($img)) {
                // 保存图片 需要加后缀 如果有问题需要判断具体是jpg或png现在写固定为png
                $imgPath = $htmlPath . md5($imgurl) . '.jpg';
                $fp = @fopen($imgPath, 'wb');
                @fwrite($fp, $img);
                fclose($fp);
            }
        }

        // 正则替换图片
        $html = preg_replace_callback($pattern_src, "self::changeImgLocal", $this->toHtml());
        // Html文件保存
        $htmlPath = $htmlPath . $this->getArticleTitle() . '.html';
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
        return '<img src=' . md5($matches[1]) . '.jpg >';
    }
}