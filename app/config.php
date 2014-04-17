<?php


# Error Reporting
error_reporting(-1);              // Report all type of errors
ini_set('display_errors', 1);     // Display all errors 
ini_set('output_buffering', 0);   // Do not buffer outputs, write directly


# Buffer output to avoid header errors
ob_start();


# Start new session here
session_name(preg_replace('/[^a-z\d]/i', '', __DIR__));
session_start();


# Defining Paths
define('MOLUZ_INSTALL_PATH', __DIR__);
define('MOLUZ_THEME_PATH', MOLUZ_INSTALL_PATH . '/theme/render.php');
 
 
# Init Autoload and functions
include(MOLUZ_INSTALL_PATH . '/lib/bootstrap.php');
 
 
# Set Time Zone for date();
date_default_timezone_set('Europe/Paris');
 
 
# MDatabase Settings
if($_SERVER["HTTP_HOST"] === "localhost") {
    
        define("DB_HOST", "localhost");
        define("DB_USER", "root");
        define("DB_PASSWORD", "");
        define("DB_NAME", "sakila");
    
    }
    

# Init Moluz Variable
$moluz = array();
$moluz["version"] = "0.8";


# Moluz Standard Objects
$moluz["auth"]  = new MUser();
$moluz["flash"] = new MFlash();


# Moluz Settings
$moluz['lang']         = 'sv-se';
$moluz['title_append'] = ' | Moluz mini-framework';


$moluz["nav"] = GenerateMenu(
    array(

        'index.php' => array('text'=>'Index',  'url'=>'index.php'),
        
        //Insert links here

    ),  "center col-12 nav-menu"); # CSS Classes Standard grid.css 
 
 
# Stylesheets
$moluz['stylesheets'] = array(
                            
                            'public/css/main.css', # Main
                            'public/css/grid.css', # Grid System
                            
                            );
                            

#Favicon
$moluz['favicon']    = 'public/favicon.ico';


# Standard Snippets
$moluz['header'] = MOLUZ_INSTALL_PATH . '/pages/snippets/header.php';
$moluz['footer'] = MOLUZ_INSTALL_PATH . '/pages/snippets/footer.php';
$moluz['byline'] = MOLUZ_INSTALL_PATH . '/pages/snippets/byline.php';


# Show var dump at end of page
$moluz["dump"] = false;
