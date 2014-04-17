<?php


class MContent
{

    private $db;    

    function __construct()
    {
            
        $this->db = new MDatabase();
        $this->flash = new MFlash();
        $this->pagination = new MPagination();
        
        $this->createTable();
        $this->createCatTable();
    
    }
                
    function findContentById($id)
    {
        
        $params["id"] = $id;
    
        $sql = "SELECT * 
                FROM content
                LEFT JOIN content_category ON content.cat_id=content_category.cat_id
                WHERE id = :id 
                AND published <= NOW()
                LIMIT 1";

        return $this->db->executeAndFetchAll($sql, $params);
    
    }
    
    function findContentBySlug($slug)
    {
        
        $params["slug"] = $slug;
    
        $sql = "SELECT * 
                FROM content
                LEFT JOIN content_category ON content.cat_id=content_category.cat_id
                WHERE slug = :slug 
                AND published <= NOW()
                LIMIT 1";

        return $this->db->executeAndFetchAll($sql, $params);
    
    }
    
    function findContentByUrl($url)
    {
        
        $params["url"] = $url;
    
        $sql = "SELECT * 
                FROM content
                LEFT JOIN content_category ON content.cat_id=content_category.cat_id
                WHERE url = :url 
                AND published <= NOW()
                LIMIT 1";

        return $this->db->executeAndFetchAll($sql, $params);
    
    }
    
    function getAllContentByType($type)
    {
    
        $this->pagination->fixUrl();

        $params["type"] = $type;

        $sql = "SELECT COUNT(*) 
                FROM content
                LEFT JOIN content_category ON content.cat_id=content_category.cat_id
                WHERE type = :type
                AND published <= NOW()";

        $this->pagination->sqlCount($sql, $params);

        $sql = "SELECT * 
                FROM content 
                LEFT JOIN content_category ON content.cat_id=content_category.cat_id
                WHERE type = :type
                AND published <= NOW()
                ORDER BY published DESC
                LIMIT :limit OFFSET :offset";

        $params["limit"] = intval($_GET["per"]);
        $params["offset"] = $this->pagination->offset();     

        return $this->db->executeAndFetchAll($sql, $params);    
    }
    
//    HERE YOU WILL FIND -<URL>- FUNCTIONS
        
    function createContentWithUrl()
    {
    
        if(isset($_POST["PostDone"]))
        {             
        

                $params["type"] = strip_tags($_POST["type"]);
                $params["title"] = strip_tags($_POST["title"]);
                $params["url"] = strip_tags($_POST["url"]);
                $params["content"] = $_POST["content"];
                $params["published"] = strip_tags($_POST["published"]);
                $params["filter"] = $this->validateFilter($_POST["filter"]);
                
                if(isset($params["published"])){

                $sql = "INSERT INTO content (title, type, content, 
                        url, published, filter)
                        VALUES (:title, :type, :content, 
                        :url, :published, :filter)";

                $this->db->executeAndBind($sql, $params);
                header("Location: ?url=" . $params["url"]); 
                die(); 

                } else {

                    unset($params["published"]);

                    $sql = "INSERT INTO content (title, type, content, slug, url)
                            VALUES (:title, :type, :content, :slug, :url)";

                    $this->db->executeAndBind($sql, $params);      
                    header("Location: ?url=" . $params["url"]); 
                    die(); 
                } 

        }
    
    }
        
    
    function updateContentWithUrl()
    {
        
        if(isset($_POST["PostDone"]))
        {

            $params["id"] = $_POST["id"];
            $params["title"] = strip_tags($_POST["title"]);
            $params["url"] = strip_tags($_POST["url"]);
            $params["content"] = $_POST["content"];
            $params["published"] = strip_tags($_POST["published"]);
            $params["filter"] = $this->validateFilter($_POST["filter"]);
            
            $sql = "UPDATE content 
                    SET title = :title, content = :content,
                    url = :url, published = :published, filter = :filter
                    WHERE id = :id";

            return $this->db->executeAndBind($sql, $params);

    
        }
    }
    
    
//    HERE YOU WILL FIND -<SLUG>- FUNCTIONS
    
    function createContentWithSlug()
    {   

 
    
        if(isset($_POST["PostDone"]))
        {             
        
                $params["title"] = strip_tags($_POST["title"]);
                $params["type"] = strip_tags($_POST["type"]);
                $params["filter"] = $this->validateFilter($_POST["filter"]);
                $params["cat_id"] = strip_tags($_POST["cat_id"]);
                $params["content"] = $_POST["content"];
                $params["published"] = strip_tags($_POST["published"]);
                
                    if(empty($_POST["slug"])){
                    $params["slug"] = $this->slugify($params["title"] .
                     "-" . mt_rand());
                    } else {
                    $params["slug"] = $this->slugify(strip_tags($_POST["slug"]));
                    }

                if(isset($params["published"])){

                $sql = "INSERT INTO content (title, type, content, 
                        slug, published, filter, cat_id)
                        VALUES (:title, :type, :content, 
                        :slug, :published, :filter, :cat_id)";

                $this->db->executeAndBind($sql, $params);
                
                header("Location: ?slug=" . $params["slug"]);
                die();  

                } else {

                    unset($params["published"]);

                    $sql = "INSERT INTO content (title, type, content, slug, url, cat_id)
                            VALUES (:title, :type, :content, :slug, :url, :cat_id)";

                    $this->db->executeAndBind($sql, $params);
                    
                    header("Location: ?slug=" . $params["slug"]); 
                    die();  

                } 
            
        }
    
    }
    
    function updateContentWithSlug()
    {
                
        if(isset($_POST["PostDone"]))
        {
                
                $params["id"] = $_POST["id"];
                $params["title"] = strip_tags($_POST["title"]);
                $params["content"] = $_POST["content"];
                $params["cat_id"] = $_POST["cat_id"];
                $params["published"] = strip_tags($_POST["published"]);
            
                    if(empty($_POST["slug"])){
                    $params["slug"] = $this->slugify($params["title"] .
                     "-" . mt_rand());
                    } else {
                    $params["slug"] = $this->slugify(strip_tags($_POST["slug"]));
                    }
            
            $sql = "UPDATE content 
                    SET title = :title, content = :content, 
                    slug = :slug, published = :published, cat_id = :cat_id
                    WHERE id = :id";

            return $this->db->executeAndBind($sql, $params);

        
        }
    }
        
//    HERE STARTS GENERIC FUNCTIONS
        
    function disableContent($id)
    {
        
        if(isset($_POST["PostDone"]))
        {

            $params["id"] = $id;

            $sql = "UPDATE content 
                    SET deleted = NOW()
                    WHERE id = :id";

            return $this->db->executeAndBind($sql, $params);
        }
    }
    
    function createTable()
    {
    
        $sql = "CREATE TABLE IF NOT EXISTS `content` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `cat_id` int(11) NOT NULL,
          `title` varchar(128) NOT NULL,
          `type` varchar(64) NOT NULL,
          `content` text NOT NULL,
          `slug` varchar(256) NULL,
          `url` varchar(256) NULL,
          `filter` varchar(128) NOT NULL,
          `created` DATETIME NULL,
          `updated` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP,
          `published` DATETIME NULL,
          `deleted` DATETIME NULL,

          PRIMARY KEY (`id`),
          UNIQUE KEY `slug` (`slug`),
          UNIQUE KEY `url` (`url`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

        
        $this->db->executeAndBind($sql);

    
    }

    function createCatTable()
    {
    
        $sql = "CREATE TABLE IF NOT EXISTS `content_category` (
          `cat_id` int(11) NOT NULL AUTO_INCREMENT,
          `cat_name` varchar(128) NOT NULL,
          `created` DATETIME NULL,
          `updated` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP,

          PRIMARY KEY (`cat_id`)

        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

        
        $this->db->executeAndBind($sql);

    
    }
    
    // Delete Table function and CreateFakeData
    
    function deleteTable()
    {
    
        $sql = "DROP TABLE content";
        $this->db->executeAndBind($sql);
        
        $this->createTable();
        $this->createFakeData();
        
        $this->flash->add("warning","Hard Reset of Content");
        header("Location: index.php");
        die();
    
    }
    
    function createFakeData() 
    {
    
        $factory = new MFactory();

        $factory->postsContent();
        $factory->pagesContent();
    
    }
    
    // Delete by ID
    
    function deleteById($id)
    {
        $params["id"] = $id;
        
        $sql = "DELETE FROM content WHERE id = :id";

        $this->db->executeAndBind($sql, $params);
        
        $this->flash->add("success","Deleted ID" . $params["id"]);
        header("Location: index.php");
        die();
    
    }
    
    // Make Slug
    
    function slugify($str) 
    {
      
      $str = strtolower(trim($str));
      $str = str_replace(array('å','ä','ö'), array('a','a','o'), $str);
      $str = preg_replace('/[^a-z0-9-]/', '-', $str);
      $str = trim(preg_replace('/-+/', '-', $str), '-');
      return $str;
      
    }
    
    function validateFilter($filter)
    {

        if(!empty($filter)){

            if(is_array($filter)){

                $filter = implode(",", $filter);

                return $filter;

            } else {

                return $filter;

            }

        } 

    }
}