<?php

class MPagination
{
    public $baseUrl;
    public $numberOfPages;
    
    private $totalRows;
    private $rowsPerPage = 10;
    private $pageStartAt = 1;
    private $limitName = "per";
    private $offsetName = "page";
    private $cssClass = "moluz-pagination";
    
    
    function __construct($cssClass = "moluz-pagination")
    {
        
        $this->db = new MDatabase();
        $this->baseUrl     = $_SERVER["PHP_SELF"];
        
        if(isset($_GET[$this->limitName]) AND 
        isset($_GET[$this->offsetName]))
        {
        
            $this->rowsPerPage = $_GET[$this->limitName];
            $this->currentPage = $_GET[$this->offsetName];
        
        }
        
        $this->cssClass    = $cssClass;        
        
    }
    
    public function sqlCount($sql, $params = array())
    {
        $this->totalRows = $this->db->numRows($sql, $params);
        
        $this->calculatePages();
    }
    
    public function getTotalRows()
    {
    
        return $this->totalRows;
    
    }
    
    private function calculatePages()
    {
        
        $this->numberOfPages = ceil($this->totalRows / $this->rowsPerPage);
        
        return $this;
        
    }
    
    public function offset()
    {
        $offset = intval($_GET[$this->limitName]) * 
        (intval($_GET[$this->offsetName]) - 1);
        
        if($offset < 0){
            $offset = 0;
        }
        
        return (int) $offset;
    }
    
    public function nextPageButton($next = "Next")
    {
        $max = $this->numberOfPages;
        
        echo "<a href='" . $this->getQueryString(array(
            'page' => ($this->currentPage < $max ? $this->currentPage + 1 : $max)
        )) . "'>Next &gt;</a> ";
        
    }
    
    public function previousPageButton($prev = "Previous")
    {
        $min = $this->pageStartAt;
        
        echo "<a href='" . $this->getQueryString(array(
            'page' => ($this->currentPage > $min ? $this->currentPage - 1 : $min)
        )) . "'>&lt; Previous</a> ";
    }
    
    function getPageNavigation()
    {
        $count = 0;
        $currentPage = $this->currentPage;
        $totalPages = $this->numberOfPages;
        $cssClass = $this->cssClass;
        
        $max = $this->numberOfPages;
        $min = $this->pageStartAt;
        
        $range = 3;
        
        $nav = "<div class='$cssClass paginationbox center'><a class='$cssClass first' href='" . $this->getQueryString(array(
            'page' => $min
        )) . "'>&lt;&lt; First</a> ";
        
        $nav .= "<a class='$cssClass previous' href='" . $this->getQueryString(array(
            'page' => ($currentPage > $min ? $currentPage - 1 : $min)
        )) . "'>&lt; Previous</a> ";
        
        for ($x = ($currentPage - $range); $x < (($currentPage + $range)  + 1); $x++)  
        {
               
               
               if (($x > 0) && ($x <= $totalPages)) {
                  // if we're on current page...
                  if ($x == $currentPage) {
                     // 'highlight' it but don't make a link
                     $count++;
                     $nav .= "<b class='middle'> ... $x  ... </b> ";
                  // if not current page...
                  } else {
                     // make it a link
                                 
                                 if ($count < $range +1) {
                                 $count++;
                                 $float = "link-left";
                                 } else {
                                 $count++;
                                 $float = "link-right";
                                 }                                 
                                 $nav .= "<a class='moluz-link $float' href='" . 
                                 $this->getQueryString(array('page' => $x)) . "'>$x</a> ";
                  } // end else
               } // end if
               
        }
        
        $nav .= "<a class='$cssClass next' href='" . $this->getQueryString(array(
            'page' => ($currentPage < $max ? $currentPage + 1 : $max)
        )) . "'>Next &gt;</a> ";
        
        $nav .= "<a class='$cssClass last' href='" . $this->getQueryString(array(
            'page' => $max
        )) . "'>Last &gt;&gt;</a> </div>";
        return $nav;
        
        
    }
    
    function getQueryString($options, $prepend = '?')
    {
        
        // parse query string into array
        $query = array();
        parse_str($_SERVER['QUERY_STRING'], $query);
        
        // Modify the existing query string with new options
        $query = array_merge($query, $options);
        
        // Return the modified querystring
        return $prepend . http_build_query($query);
        
    }
     
    
    function generatePerPageNav($integer = array(5, 10, 15))
    {    
        $nav = "<div class='perPage'>";
        
        foreach($integer as $int)
        {
                
                $nav .= "<a class='perPage-link' href='" . 
                $this->getQueryString(array('per' => $int, 'page' => 1)) . "'>$int</a> ";
                
        }
    
        $nav .= "</div>";
        
        return $nav;
        
    }
    
    function fixUrl()
    {
    
        if(!isset($_GET["page"]) AND !isset($_GET["per"])){
        
        header("Location: ". $this->getQueryString(array('per' => 10, 'page' => 1)));
        die();
        
        }
    
    }
    
}