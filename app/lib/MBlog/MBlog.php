<?php

class MBlog
{
    private $rows;
    public $content;
    public $slug;
    public $pagination;

    function __construct()
    {
    
        $this->content = new MContent();
        
        if(isset($_GET["slug"])){
         
         $this->slug = $_GET["slug"];
         $this->rows = $this->getBlogPost();
         $this->pagination = "";
         
         
        } elseif(isset($_GET["id"])) {
        
         $this->slug = "";
         $this->rows = "";
         $this->pagination = "";
        
        } else {
        
         $this->rows = $this->getBlogAllPosts();
         $this->pagination = $this->content->pagination->getPageNavigation();
        
        }
       
    
    }
    
    function getRows()
    {
    
        return $this->rows;
    
    }
    
    function getBlogPost()
    {
       
        $slug = $this->content->findContentBySlug($this->slug); 
       
            if($slug){

                return $slug;

            } else {

                return false;

            }
                                  
    }
        
    function getBlogAllPosts()
    {
       
        $posts = $this->content->getAllContentByType("posts"); 
       
            if($posts){

                return $posts;

            } else {

                return false;

            }
                                  
    }
        
}