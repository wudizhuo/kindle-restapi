<?php

class UrlUtil
{
    public static function valid_url($str)
    {
        return preg_match("/^http|https:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"])*$/", $str);
    }

    public static function get_content($url)
    {
        $snoopy = new Snoopy;
        $snoopy->fetch($url);
        return $snoopy->results;
    }

}