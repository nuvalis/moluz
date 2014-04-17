<?php

class MFactory
{
    
    function __construct()
    {
    
        $this->db = new MDatabase();
    
    }
    
    function postsContent(){
    
    $mc = new MContent();
    
        for ($i = 1; $i <= 10; $i++) {
        
        $params["type"] = "posts";
        $params["filter"] = "bbcode";

        $params["title"] = "Lorem ipsum dölör sit ämet.";
        $params["content"] = "Lorem ipsum dölör sit ämet. Cönsectetur ådipisicing elit sed do. Eiusmod tempor incididunt ut låbore et dolöre. Mågnä åliquå condimentum in elementum phåreträ. Hac möllis suspendisse tincidunt, mörbi nulläm suspendisse. Söllicitudin nunc ipsum dölör. Däpibus sem eu iaculis. Tempus congue völutpåt porta tellus fåcilisis, pellentesque suspendisse. Felis ultricies dui eget. Amet semper ullamcorper etiam vulputåte. Ipsum åmet tempus lectus. Vulputåte åmet facilisis cömmodo sed etiam. Ultricies sägittis plåtea måuris.";
        
        $params["slug"] = $mc->slugify($params["title"]) . "-" . mt_rand();       

        $sql = "INSERT INTO content (title, slug, content, published, type, filter) VALUES (:title, :slug, :content, NOW(), :type, :filter)";
        
        $this->db->executeAndBind($sql, $params);
        
        }
    
    }


    function posters()
    {

        set_time_limit (0);
    
        $sql = "SELECT * FROM film";
        $rows = $this->db->executeAndFetchAll($sql);

        foreach ($rows as $row) {
            $params["id"] = $row["film_id"];
            $params["poster"] = "movies/posters/" . mt_rand(1, 20) . ".jpg";       

            $sql = "UPDATE film SET poster = :poster WHERE film_id = :id";
            
            $this->db->executeAndBind($sql, $params);
        }
    
    }
    
    function pagesContent(){
    
    $mc = new MContent();
    
        for ($i = 1; $i <= 10; $i++) {
        
        $params["type"] = "page";
        $params["filter"] = "bbcode";

        $params["title"] = "Cönsectetur ådipisicing elit! Lorem ipsum dölör sit ämet.";
        $params["content"] = "Lorem ipsum dölör sit ämet. Cönsectetur ådipisicing elit sed do. Eiusmod tempor incididunt ut låbore et dolöre. Mågnä åliquå condimentum in elementum phåreträ. Hac möllis suspendisse tincidunt, mörbi nulläm suspendisse. Söllicitudin nunc ipsum dölör. Däpibus sem eu iaculis. Tempus congue völutpåt porta tellus fåcilisis, pellentesque suspendisse. Felis ultricies dui eget. Amet semper ullamcorper etiam vulputåte. Ipsum åmet tempus lectus. Vulputåte åmet facilisis cömmodo sed etiam. Ultricies sägittis plåtea måuris.";
        
        $params["url"] = $mc->slugify($params["title"]) . "-" . mt_rand();       

        $sql = "INSERT INTO content (title, url, content, published, type, filter) VALUES (:title, :url, :content, NOW(), :type, :filter)";
        
        $this->db->executeAndBind($sql, $params);
        
        }
    
    }


}