<?php


class MTextFilter
{
    
    /**
     * Functions for filtering content.
     */

    /**
     * Call each filter.
     *
     * @param string $text the text to filter.
     * @param string $filter as comma separated list of filter.
     * @return string the formatted text.
     */
    function doFilter($text, $filter) {
      // Define all valid filters with their callback function.
      if(!empty($filter)){
      
          $valid = array(
            'bbcode'   => 'bbcode2html',
            'link'     => 'make_clickable',
            'markdown' => 'markdown',
            'nl2br'    => 'nl2br',  
          );

          // Make an array of the comma separated string $filter
          $filters = preg_replace('/\s/', '', explode(',', $filter));

          // For each filter, call its function with the $text as parameter.
          foreach($filters as $func) {
            if(isset($valid[$func])) {
              $text = $this->$valid[$func]($text);
            } 
            else {
              throw new Exception("The filter '$filter' is not a valid filter string.");
            }
          }

          return $text;
          
      }
    }


    function nl2br($string){

      return nl2br($string);

    }

    /**
     * Helper, BBCode formatting converting to HTML.
     *
     * @param string text The text to be converted.
     * @return string the formatted text.
     * @link http://dbwebb.se/coachen/reguljara-uttryck-i-php-ger-bbcode-formattering
     */
    function bbcode2html($text) {
      $search = array( 
        '/\[b\](.*?)\[\/b\]/is', 
        '/\[i\](.*?)\[\/i\]/is', 
        '/\[u\](.*?)\[\/u\]/is', 
        '/\[img\](https?.*?)\[\/img\]/is', 
        '/\[url\](https?.*?)\[\/url\]/is', 
        '/\[url=(https?.*?)\](.*?)\[\/url\]/is' 
        );   
      $replace = array( 
        '<strong>$1</strong>', 
        '<em>$1</em>', 
        '<u>$1</u>', 
        '<img src="$1" />', 
        '<a href="$1">$1</a>', 
        '<a href="$1">$2</a>' 
        );     
      return preg_replace($search, $replace, $text);
    }

    /**
     * Make clickable links from URLs in text.
     *
     * @param string $text the text that should be formatted.
     * @return string with formatted anchors.
     * @link http://dbwebb.se/coachen/lat-php-funktion-make-clickable-automatiskt-skapa-klickbara-lankar
     */
    function make_clickable($text) {
      return preg_replace_callback(
        '#\b(?<![href|src]=[\'"])https?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#',
        create_function(
          '$matches',
          'return "<a href=\'{$matches[0]}\'>{$matches[0]}</a>";'
        ),
        $text
      );
    }

    //use \Michelf\MarkdownExtra;
    /**
     * Format text according to Markdown syntax.
     *
     * @link http://dbwebb.se/coachen/skriv-for-webben-med-markdown-och-formattera-till-html-med-php
     * @param string $text the text that should be formatted.
     * @return string as the formatted html-text.
     */
    function markdown($text) {
       require_once(__DIR__ . '/Markdown/Michelf/MarkdownInterface.php');
       require_once(__DIR__ . '/Markdown/Michelf/Markdown.php');
       require_once(__DIR__ . '/Markdown/Michelf/MarkdownExtra.php');
      return \Michelf\MarkdownExtra::defaultTransform($text);
    }


}