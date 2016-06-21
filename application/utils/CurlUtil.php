<?php

class CurlUtil
{

    // 抓取网页内容
    public static function curl($url, $timeout = 5)
    {
        $curl = self::getCurl($url, $timeout);
        $values = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($http_code != 200) {
            if ($http_code == 301 || $http_code == 302) {
                $values = self::curl_redir_exec($curl, $url);
            }
        }

        curl_close($curl);
        return $values;
    }

    /**
     * 获取网页内容
     *
     * @param
     *            $url
     * @return string
     */
    public static function get_contents_from_url($url)
    {
        $urlContent = file_get_contents("compress.zlib://" . $url);
        return $urlContent;
    }

    public static function curl_redir_exec($ch, $url)
    {
        static $curl_loops = 0;
        static $curl_max_loops = 20;
        if ($curl_loops++ >= $curl_max_loops) {
            $curl_loops = 0;
            return false;
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        if (empty($data)) {
            return;
        }
        $ret = $data;
        list ($header, $data) = explode("\r\n\r\n", $data, 2);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code == 301 || $http_code == 302) {
            $matches = array();
            $header = $header . "\n";
            preg_match('/Location:(.*?)\n/', $header, $matches);
            $url = @parse_url(trim(array_pop($matches)));
            if (!$url || !isset($url['scheme']) || !isset($url['host'])) {
                // couldn't process the url to redirect to
                $curl_loops = 0;
                return $data;
            }

            $last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
            $new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . (isset($url['query']) ? '?' . $url['query'] : '');
            $url = UrlParseAdapter::parse_url($new_url);
            return self::curl_redir_exec($ch, $url);

        } else {
            $curl_loops = 0;
            list ($header, $data) = explode("\r\n\r\n", $ret, 2);
            return $data;
        }
    }

    /**
     * 获取配置好的Curl
     *
     * @param string $url
     * @param number $timeout
     * @param string $is_has_head
     *            是否带header头信息 用于302跳转
     * @return resource
     */
    public static function getCurl($url, $timeout = 5, $is_has_head = FALSE)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, $is_has_head);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; SeaPort/1.2; Windows NT 5.1; SV1; InfoPath.2)");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        return $curl;
    }
}