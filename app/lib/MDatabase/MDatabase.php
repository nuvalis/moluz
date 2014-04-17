<?php

class MDatabase
{
    
    private $host = DB_HOST;
    private $user = DB_USER;
    private $password = DB_PASSWORD;
    private $dbname = DB_NAME;
    
    private static $numQueries = 0;
    private static $queries = array();
    private static $params = array();
    
    private $db;
    private $error;
    
    public function __construct()
    {
        // Set DSN
        $dsn     = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname;
        // Set options
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::FETCH_OBJ => true
        );
        // Create a new PDO instance
        try {
            $this->db = new PDO($dsn, $this->user, $this->password, $options);
        }
        // Catch any errors
        catch (PDOException $e) {
            //throw $e; // For debug purpose, shows all connection details
            throw new PDOException('Could not connect to database, hiding connection details.'); // Hide connection details.
        }
    }
    
    public function executeAndFetchAll($query, $params = array(), $debug = false)
    {
        
        self::$queries[] = $query;
        self::$params[]  = $params;
        self::$numQueries++;
        
        if ($debug) {
            echo "<p>Query = <br/><pre>{$query}</pre></p><p>Num query = " .
            self::$numQueries . "</p><p><pre>" . print_r($params, 1) . "</pre></p>";
        }
        
        $this->stmt = $this->db->prepare($query);
        
        # This takes the params and binds them to SQL statements placeholders
        $this->bindParams($params);
        
        if($this->stmt->execute()){
            return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return false;
        }

        
    }

    public function lastInsertId()
    {
        return $this->db->lastInsertId();
    }
    
    
    public function executeAndBind($query, $params = array(), $debug = false)
    {
        
        self::$queries[] = $query;
        self::$params[]  = $params;
        self::$numQueries++;
        
        if ($debug) {
            echo "<p>Query = <br/><pre>{$query}</pre></p><p>Num query = " .
            self::$numQueries . "</p><p><pre>" . print_r($params, 1) . "</pre></p>";
        }
        
        $this->stmt = $this->db->prepare($query);
        
        
        # This takes the params and binds them to SQL statements placeholders
        $this->bindParams($params);
        
        return $this->stmt->execute();
    }
    
    /**
     * Get a html representation of all queries made, for debugging and analysing purpose.
     * 
     * @return string with html.
     */
    public function dump()
    {
        $html = '<p><i>You have made ' . self::$numQueries .
         ' database queries.</i></p><pre>';
        foreach (self::$queries as $key => $val) {
            $params = empty(self::$params[$key]) ? null : htmlentities(print_r(self::$params[$key], 1)) . '<br/></br>';
            $html .= $val . '<br/></br>' . $params;
        }
        return $html . '</pre>';
    }
    
    public function getTable($table)
    {
        
        $sql        = "SELECT * FROM $table";
        $this->stmt = $this->db->prepare($sql);
        $this->stmt->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
        
        
    }
    
    public function numRows($sql, $params = array(), $debug = false)
    {
        $this->stmt = $this->db->prepare($sql, $params);
        
        # This takes the params and binds them to SQL statements placeholders
        $this->bindParams($params);
        
        $this->stmt->execute();
        $value = $this->stmt->fetchAll(PDO::FETCH_NUM);
        
        return  $value[0][0];
        
    }
    
    public function filterByMenu($table, $type, $nav = true)
    { 
    
        $sql = "SELECT $type FROM  $table GROUP BY $type";
        
        $this->stmt = $this->db->prepare($sql);
        $this->stmt->execute();
        $rows = $this->stmt->fetchAll();
        
        if($nav){
            $nav = "<ul class='filterby'>";
            
             $nav .= "<li><a class='filterby-link' href='" . $this->getQueryString(array(
                $type => "ALL"
            )) . "'> ALL </a></li> ";
            
            foreach($rows as $row){
            
            $nav .= "<li><a class='filterby' href='" . $this->getQueryString(array(
                $type => $row[$type]
            )) . "'> $row[$type] </a></li> ";
            
            }
            
            $nav .= "</ul>";
            
            return $nav;
            
        } else {

            return $rows;

        }
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
    
    function searchBy($table, $column, $request, $params = array())
    {
    
        $params["request"] = '%'.$request.'%';     

        $sql = "SELECT * FROM $table WHERE $column 
        LIKE :request LIMIT :limit OFFSET :offset";   

        return $this->executeAndFetchAll($sql, $params);
                    
    }
    
    function bindParams($params)
    {
    
        foreach ($params as $key => &$value) 
        {
            
            if (is_int($value)) {
                
                $paramType = PDO::PARAM_INT;
                
            } elseif (is_null($value)) {
                
                $paramType = PDO::PARAM_NULL;
                
            } elseif (is_bool($value)) {
                
                $paramType = PDO::PARAM_BOOL;
                
            } elseif (is_string($value)) {
                
                $paramType = PDO::PARAM_STR;
                
            }
            
            $this->stmt->bindParam($key, $value, $paramType);
            
        }
    
    }
    
}