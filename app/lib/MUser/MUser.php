<?php

class MUser 
{
    
    private $password;
    private $username;
    
    private $hashCost = "12";
    private $db;
    private $MFlash;


    function __construct()
    {     
     
         $this->db = new MDatabase();
         $this->flash = new MFlash();

         $this->createTable();
     
    }
    
    function createTable()
    {

        $sql = "CREATE TABLE IF NOT EXISTS `users` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `username` varchar(128) NOT NULL,
          `password_digest` varchar(80) NOT NULL,
          `email` varchar(128) NOT NULL,
          `firstname` varchar(128) NOT NULL,
          `lastname` varchar(128) NOT NULL,
          `role` varchar(128) NOT NULL,

          PRIMARY KEY (`id`),
          UNIQUE KEY `email` (`email`),
          UNIQUE KEY `username` (`username`)

        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

        
        $this->db->executeAndBind($sql);

    }

    function login($username, $password)
    {
        
    $params["username"] = $username;
    
    $sql = "SELECT * FROM users 
            WHERE username = :username 
            LIMIT 1";
            
    $result = $this->db->executeAndFetchAll($sql, $params);
          
        if (!empty($result)){

            foreach($result as $user)
            {               
                    if ($this->validate_pw($password, $user["password_digest"]))
                    {
                        
                        $_SESSION['user_id'] = $user["id"];
                        $_SESSION['username'] = $user["username"];
                        $_SESSION['role'] = $user["role"];
                        
                        $this->flash->add("success", "You are now logged in.");
                        redirect("account.php");
                        
                    } else {
                        
                        $this->flash->add("error", 
                        "Username or Password didn't match.");
                        redirect("login.php");
                        
                    }
            }

        } else {
            
            $this->flash->add("error", "Username or Password didn't match.");
            redirect("login.php");

        }   
    
            
    
    
    }
    
    function logout($relocate = "index.php")
    {

        $this->destroySession();
        
        $this->flash->init()->add("success", "Succesfully logged out.");
        redirect($relocate);
    
    }
    
    function destroySession() {
      // Unset all of the session variables.
      $_SESSION = array();

      // If it's desired to kill the session, also delete the session cookie.
      // Note: This will destroy the session, and not just the session data!
      if (ini_get("session.use_cookies")) {
          $params = session_get_cookie_params();
          setcookie(session_name(), '', time() - 42000,
              $params["path"], $params["domain"],
              $params["secure"], $params["httponly"]
          );
      }

      // Finally, destroy the session.
      session_destroy();
    }

    function getUser($username)
    {

      $params["username"] = $username;
      $sql = "SELECT * FROM users WHERE username = :username LIMIT 1";

      $rows = $this->db->executeAndFetchAll($sql, $params);

      if(empty($rows)){
          return false;
      } else {
          return $rows[0]["username"];
      }

    }

    function getUserById($id)
    {

      $params["id"] = $id;
      $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";

      $rows = $this->db->executeAndFetchAll($sql, $params);

      if(empty($rows)){
          return false;
      } else {
          return $rows[0]["id"];
      }

    }

    function getEmail($email)
    {

      $params["email"] = $email;
      $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";

      $rows = $this->db->executeAndFetchAll($sql, $params);
      
      if(empty($rows)){
          return false;
      } else {
          return $rows[0]["email"];
      }

    }

    function uniqueUser($user)
    {
      if(!empty($user)){
        if ($this->getUser($user) === $user) {
          $this->flash->add("error", "Username exists.");
          $this->error = "error";
        }
      } else {
          $this->flash->add("error", "Username can't be empty!");
          $this->error = "error";
      }
    }

    function uniqueEmail($email)
    {

        if ($this->getEmail($email) === $email) {
          $this->flash->add("error", "Email exists.");
          $this->error = "error";
        }

    }

    function validateEmail($email)
    {

      $result = filter_var($email , FILTER_VALIDATE_EMAIL );

        if($result === false)
        {

          $this->flash->add("error", "Email is not valid.");
          $this->error = "error";  

        } else {

          return true;

        }

    }
    
    function createUser($role = "User")
    {
          $this->validateEmail($_POST["email"]);
          $this->uniqueEmail($_POST["email"]);
          $this->uniqueUser($_POST["username"]);


        if ($_POST["password"] != $_POST["password_confirm"]) {
          $this->flash->add("error", "Password didn't match.");
          $this->error = "error";
        } 

        if(empty($_POST["password"])){
          $this->flash->add("error", "Password can't be empty!");
          $this->error = "error";
        }

        if (isset($this->error)){
          $this->flash->show();
          unset($_POST["Register"]);
          return $_POST;
          redirect("register.php");
        }

        $params["username"] = $_POST["username"];
        $params["role"] = $role;
        $params["firstname"] = $_POST["firstname"];
        $params["lastname"] = $_POST["lastname"];
        $params["email"] = $_POST["email"];
    
        $username = $_POST["username"];
        $password = $_POST["password"];

        $hashed = $this->generate_hash($password, $this->hashCost);

        $sql = "INSERT INTO users (username, password_digest, firstname, lastname, email, role) 
                VALUES (:username, :password_digest, :firstname, :lastname, :email, :role)";

        $params["password_digest"] = $hashed;
        
        if($this->db->executeAndBind($sql, $params)){
          $this->flash->add("success", "Account $username has been created. ");
          redirect("index.php");
        } else {
          $this->flash->add("error", "Account has not been created. ");
          return false;
        }
        
        
        
    }
    
    function destroyUser($id)
    {
     
      $params["id"] = $id;
      $sql = "DELETE FROM users WHERE id = :id";

      return  $this->db->executeAndBind($sql, $params);

    }
    
    function isLoggedIn()
    {
    
        if (isset($_SESSION["user_id"]))
        {
            return true;           

        } else {
        
            return false;
        
        }
    }
    
    function havePermission($permission = "User")
    {

        if (isset($_SESSION["role"]))
        {
           

            if($_SESSION["role"] != "admin" AND $_SESSION["role"] != $permission ){
                $this->flash->add("error", "You don't have the rights to do this action! #1");
                redirect("index.php");
            }
                       

        } else {
        
            $this->flash->add("error", "You don't have the rights to do this action! #2");
            redirect("index.php");
        
        }
        
    }
    
    function getId()
    {
        
        $id = $_SESSION["user_id"];
        return $id;
        
    }
    
    function getUsername()
    {
    
        $username = $_SESSION["username"];
        return $username;
    
    }
    
    function getAllUsers($column, $order)
    {

      $sql = "SELECT * FROM users ORDER BY $column $order";

      $rows = $this->db->executeAndFetchAll($sql);

      return $rows;

    }

    /*
     * Example found at http://se2.php.net/manual/en/function.crypt.php
     *
     * This is modified! Do not use in production! This is only for TESTING!
     *
     * Generate a secure hash for a given password. The cost is passed
     * to the blowfish algorithm. Check the PHP manual page for crypt to
     * find more information about this setting.
     */
    function generate_hash($password, $cost=11){
            /* To generate the salt, first generate enough random bytes. Because
             * base64 returns one character for each 6 bits, the we should generate
             * at least 22*6/8=16.5 bytes, so we generate 17. Then we get the first
             * 22 base64 characters
             */
             
            $size = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CFB);
            $salt=substr(base64_encode(sha1(mcrypt_create_iv($size))),0,22);
            /* As blowfish takes a salt with the alphabet ./A-Za-z0-9 we have to
             * replace any '+' in the base64 string with '.'. We don't have to do
             * anything about the '=', as this only occurs when the b64 string is
             * padded, which is always after the first 22 characters.
             */
            $salt=str_replace("+",".",$salt);
            /* Next, create a string that will be passed to crypt, containing all
             * of the settings, separated by dollar signs
             */
            $param='$'.implode('$',array(
                    "2y", //select the most secure version of blowfish (>=PHP 5.3.7)
                    str_pad($cost,2,"0",STR_PAD_LEFT), //add the cost in two digits
                    $salt //add the salt
            ));

            //now do the actual hashing
            return crypt($password,$param);
    }

    /*
     * Check the password against a hash generated by the generate_hash
     * function.
     */
    function validate_pw($password, $hash){
            /* Regenerating the with an available hash as the options parameter should
             * produce the same hash if the same password is passed.
             */
            return crypt($password, $hash)==$hash;
    }



}