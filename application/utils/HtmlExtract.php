<?
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class HtmlExtract
{

    public static function getReadabilityHtml($url)
    {
        $result = null;
        $html = self::get_contents_from_url($url);

        $CI = &get_instance();

        if (UrlParseAdapter::is_zhzl($url)) {

            $zhzl_json = json_decode($html, true);

            $data = array(
                'articleTitle' => $zhzl_json['title'],
                'articleContent' => $zhzl_json['content']
            );

            $CI->load->library('HtmlEntity', $data);

            $result = $CI->htmlentity;
        } else if (UrlParseAdapter::is_zh($url)) {
            $readabilityApi = "https://www.readability.com/api/content/v1/parser?url=" . $url . '&token=7ace6330e4dfcf6dfb2cacb8f11e5b4ee1a487d9';
            $urlContent = CurlUtil::get_contents_from_url($readabilityApi);

            $readability_json = json_decode($urlContent, true);

            $data = array(
                'articleTitle' => $readability_json['title'],
                'articleContent' => $readability_json['content']
            );
            $CI->load->library('HtmlEntity', $data);

            $result = $CI->htmlentity;
        } else {
            $result = self::get_cotent_from_readablitylib($url, $html);
        }

        return $result;
    }

    /**
     * 通过readablitylib 开源库获取html内容
     * @param $url
     * @param $html
     * @return null
     */
    public static function get_cotent_from_readablitylib($url, $html)
    {
        $data = array(
            'html' => $html,
            'url' => $url
        );

        $CI = &get_instance();

        $CI->load->library('Readability', $data);
        $readability = $CI->readability;
        // Note: PHP Readability expects UTF-8 encoded content.
        // If your content is not UTF-8 encoded, convert it
        // first before passing it to PHP Readability.
        // Both iconv() and mb_convert_encoding() can do this.

        // If we've got Tidy, let's clean up input.
        // This step is highly recommended - PHP's default HTML parser
        // often doesn't do a great job and results in strange output.
        if (function_exists('tidy_parse_string')) {
            $tidy = tidy_parse_string($html, array(), 'UTF8');
            $tidy->cleanRepair();
            $html = $tidy->value;
        }

        // give it to Readability
        // $readability = new Readability($html, $url);
        // print debug output?
        // useful to compare against Arc90's original JS version -
        // simply click the bookmarklet with FireBug's console window open
        $readability->debug = false;
        // convert links to footnotes?
        $readability->convertLinksToFootnotes = true;
        // process it
        $hasContent = $readability->init();
        // does it look like we found what we wanted?
        if ($hasContent) {
            $content = $readability->getContent()->innerHTML;
            // if we've got Tidy, let's clean it up for output
            if (function_exists('tidy_parse_string')) {
                $tidy = tidy_parse_string($content, array(
                    'indent' => true,
                    'show-body-only' => true
                ), 'UTF8');
                $tidy->cleanRepair();
                $content = $tidy->value;
            }

            $data = array(
                'articleTitle' => $readability->getTitle()->textContent,
                'articleContent' => $content
            );

            $CI->load->library('HtmlEntity', $data);

            $result = $CI->htmlentity;
        } else {
            $result = null;
        }
        return $result;
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
        $urlContent = CurlUtil::curl($url);
        $charset = self::get_charset($urlContent);
        if ($charset && $charset != 'utf-8') {
            $urlContent = iconv($charset, "UTF-8//IGNORE", $urlContent);
        }
        return $urlContent;
    }

    /**
     * 获取页面 <meta.+?charset=""> 的编码内容
     *
     * @param unknown $urlContent
     * @return string
     */
    public static function get_charset($urlContent)
    {
        return preg_match("/<meta.+?charset=[^\w]?([-\w]+)/i", $urlContent, $temp) ? strtolower($temp[1]) : "";
    }

}

?>