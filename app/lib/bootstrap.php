<?php


     /**
     * Bootstrapping functions, essential and needed for moluz to work together with some common helpers. 
     *
     */

    function dump($array) {
      echo "<pre>" . htmlentities(print_r($array, 1)) . "</pre>";
    }

    /**
     * Default exception handler.
     *
     */
    function myExceptionHandler($exception) {
      echo "Moluz: Uncaught exception: <p>" . $exception->getMessage() . "</p><pre>" . $exception->getTraceAsString(), "</pre>";
    }
    set_exception_handler('myExceptionHandler');


    /**
     * Autoloader for classes.
     *
     */
    function myAutoloader($class) {
      $path = MOLUZ_INSTALL_PATH . "/lib/{$class}/{$class}.php";
      if(is_file($path)) {
        include($path);
      }
      else {
        throw new Exception("Classfile '{$class}' does not exists.");
      }
    }
    spl_autoload_register('myAutoloader');

    function render_page($page, $moluz){

        $moluz["main"] = $page;

        include(MOLUZ_THEME_PATH);

    }

    function GenerateMenu($items, $class) {
        $html = "<nav class='$class'>\n";
        foreach($items as $key => $item) {

          if(basename(getCurrentUrl()) == $key){

              $active = "active";

          } else {

              $active = null;

          }

          #$active = (isset($_GET['page'])) && $_GET['page'] == $key ? 'active' : null; 
          $html .= "<a href='{$item['url']}' class='{$active}'>{$item['text']}</a>\n";
        }
        $html .= "</nav>\n";
        return $html;
      }


    function getCurrentUrl() 
    {

      $url = "http";
      $url .= (@$_SERVER["HTTPS"] == "on") ? 's' : '';
      $url .= "://";
      $serverPort = ($_SERVER["SERVER_PORT"] == "80") ? '' :
        (($_SERVER["SERVER_PORT"] == 443 && @$_SERVER["HTTPS"] == "on") ? '' : ":{$_SERVER['SERVER_PORT']}");
      $url .= $_SERVER["SERVER_NAME"] . $serverPort . htmlspecialchars($_SERVER["REQUEST_URI"]);
      return $url;

    }


    function redirect($location)
    {

      header("Location: " . $location);
      die();

    }

  function truncate($text, $chars = 250) 
  {
      $text = $text." ";
      $text = substr($text,0,$chars);
      $text = substr($text,0,strrpos($text,' '));
      $text = $text."...";
      return $text;
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