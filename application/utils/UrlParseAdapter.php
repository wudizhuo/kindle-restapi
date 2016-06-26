<?php

class UrlParseAdapter
{

    /**
     * 转换网页URL 有的网页经过转码 要进行转换
     *
     * @param unknown $url
     * @return string
     */
    public static function parse_url($url)
    {
        $url = trim($url);
        $pattern = '/^http\:\/\/t.uc.cn/';
        if (preg_match($pattern, $url)) {
            return self::UC_url($url);
        }

        if (self::is_zhzl($url)) {
            return self::zhzl_url($url);
        }

        $pattern = '/^http\:\/\/m.baidu.com\/news/';
        if (preg_match($pattern, $url)) {
            return self::baidu_url($url);
        }

        $patten = '/[\s]+?/';
        if (preg_match($patten, $url)) {
            return preg_split($patten, $url)[0];
        }

        return $url;
    }

    public static function UC_url($url)
    {
        $res = $url;
        $pattern_UC = '/<iframe src\s*=\s*[\"|\'](.*?)[\"|\'][\s\S]*?>/';
        $urlContent = file_get_contents("compress.zlib://" . $url);
        preg_match_all($pattern_UC, $urlContent, $match);
        if (!empty($match[1])) {
            $res = $match[1][0];
        }
        return $res;
    }

    /**
     * 知乎专栏 url
     * @param $url
     * @return int
     */
    public static function is_zhzl($url)
    {
        $pattern = '/^http\:\/\/zhuanlan.zhihu.com/';
        return preg_match($pattern, $url);
    }

    /**
     * 知乎 url
     * @param $url
     * @return int
     */
    public static function is_zh($url)
    {
        $pattern = '/^http\:\/\/www.zhihu.com\/question/';
        return preg_match($pattern, $url);
    }


    /**
     * 知乎专栏url
     * @param $url
     * @return mixed
     */
    public static function zhzl_url($url)
    {
        $res = $url;
        $regex = '/^http:\/\/([\w.]+)\/([\w]+)\/([\w]+)/i';
        if (preg_match($regex, $res, $matches)) {
            $res = 'http://' . $matches[1] . '/api/columns/' . $matches[2] . '/posts/' . $matches[3];
        }
        return $res;
    }

    public static function baidu_url($url)
    {
        $url = urldecode($url);
        // 把移动端百度新闻url街去掉
        $data = preg_split("/http\:\/\/m.baidu.com\/news\?fr\=mohome/", $url);
        if (empty($data[0])) {
            $url = explode("/", $data[1]);
            unset($url[0]);
            unset($url[1]);
            $url = array_splice($url, 0, -4);
            $url = implode("/", $url);
            return $url;
        } else {
            return $data[0];
        }
    }
}