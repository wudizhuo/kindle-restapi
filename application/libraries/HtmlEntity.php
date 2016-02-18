<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class HtmlEntity{

    private $articleTitle;

    private $articleContent;

    function __construct($data)
    {
    	$this->articleTitle = isset($data['articleTitle']) ? $data['articleTitle'] : null;
        $this->articleContent = isset($data['articleContent']) ? $data['articleContent'] : null;
        
        $this->CI =& get_instance();
    }
    
    /**
     * 转换为可以版本的html
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
        return $this->articleTitle;
    }

    /**
     *
     * @return the $articleContent
     */
    public function getArticleContent()
    {
        return $this->articleContent;
    }
}