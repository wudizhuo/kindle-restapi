<?

class UrlUtil
{
    public static function valid_url($str)
    {
        return preg_match("/^http|https:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"])*$/", $str);
    }
}

?>