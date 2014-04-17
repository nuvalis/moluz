<?php


class MPage
{
    private $rows;
    public $content;
    public $url;
    public $pagination;

    function __construct()
    {
    
        $this->content = new MContent();
        
        if(isset($_GET["url"])){
         
         $this->url = $_GET["url"];
         $this->rows = $this->getPage();
         $this->pagination = "";
         
         
        } elseif(isset($_GET["id"])) {
        
         $this->url = "";
         $this->rows = "";
         $this->pagination = "";
        
        } else {
        
         $this->rows = $this->getAllPages();
         $this->pagination = $this->content->pagination->getPageNavigation();
        
        }
       
    
    }
    
    function getRows()
    {
    
        return $this->rows;
    
    }
    
    function getPage()
    {
       
        $url = $this->content->findContentByUrl($this->url); 
       
            if($url){

                return $url;

            } else {

                return false;

            }
                                  
    }
        
    function getAllPages()
    {
       
        $page = $this->content->getAllContentByType("page"); 
       
            if($page){

                return $page;

            } else {

                return false;

            }
                                  
    }
        
}