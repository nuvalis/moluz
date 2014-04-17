<?php

class MFlash 
{
    private $messages;
    
    function __construct()
    {
    
    }
    
    public function add($type, $message) 
    {

        if(!isset($_SESSION['flash'])) 
        {
            $_SESSION['flash'] = array();
            $_SESSION['flash']['uri'] = $_SERVER['REQUEST_URI'];
        }
        
      
      // sets the message
      $_SESSION['flash']['messages'][$type][] = $message;
      
    }
    
    function init()
    {
    
        if(!isset($_SESSION['flash'])) 
        {
            $_SESSION['flash'] = array();
            $_SESSION['flash']['uri'] = $_SERVER['REQUEST_URI'];
        }
        
        return $this;
    
    }
    
    public function remove() 
    {
        
        $_SESSION['flash'] = null;
        
    }

    public function show() 
    {
            
      if(isset($_SESSION['flash']['messages'])) {
        
        $this->messages = "<div class='flash-messages'>";

            if(isset($_SESSION['flash']['messages']["error"])){
        
                    foreach($_SESSION['flash']['messages']["error"] as $type => $msg)
                    {

                    $this->messages .= "<div class='flash flash-message error'>$msg</div>";

                    }
            }

            if(isset($_SESSION['flash']['messages']["success"])){

                foreach($_SESSION['flash']['messages']["success"] as $type => $msg)
                {

                $this->messages .= "<div class='flash flash-message success'>$msg</div>";

                }

            }

            if(isset($_SESSION['flash']['messages']["warning"])){

                foreach($_SESSION['flash']['messages']["warning"] as $type => $msg)
                {

                $this->messages .= "<div class='flash flash-message warning'>$msg</div>";

                }

            }
        
        $this->messages .= "</div>";
        
        echo $this->messages;
        $this->remove();       
        
      }

      return null;
      
    }

}