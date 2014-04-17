<?php 
class MGallery
{
 
 public $galleryPath;
 public $baseUrl = "";
 public $thumbSize = 192;         //integer, amount of pixel
 public $displayWidth = 1024;     //integer, amount of pixel
 public $displayHeight = 800;     //integer, amount of pixel
 
 function __construct($galleryPath, $baseUrl = "")
 {
    
    $this->galleryPath = $galleryPath;
    $this->baseUrl = $baseUrl;
    
    //
    // Get incoming parameters
    //
    $this->path = isset($_GET['path']) ? $_GET['path'] : null;

    $this->pathToGallery = $this->galleryPath . $this->path;


    //
    // Validate incoming arguments
    //
    
    is_dir($this->galleryPath) or $this->errorMessage('The gallery dir is not a valid directory.');
    substr_compare($this->galleryPath, $this->pathToGallery, 0, strlen($this->galleryPath)) == 0 or $this->errorMessage('Security constraint: Source gallery is not directly below the directory $this->galleryPath.');
    
 }
  
  
  function galleryGenerator(){
  
      //
    // Read and present images in the current directory
    //
    if(is_dir($this->pathToGallery)) {
      $gallery = $this->readAllItemsInDir($this->pathToGallery);
    }
    else if(is_file($this->pathToGallery)) {
      $gallery = $this->readItem($this->pathToGallery);
    } else {
    
    $error = <<<HTML
<h1 class='notfound'>We could not find the image for you. Sorry.</h1><br>
<div class='nothappy'>:-(</div>
<h3><a class="center back-link" href="gallery.php">Return to Gallery</a></h3>

HTML;
    
     $this->errorMessage($error);
    
    }  
  
    echo $gallery;
  
  
  }
  
 
 
 
 /**
 * Display error message.
 *
 * @param string $message the error message to display.
 */
function errorMessage($message) {
  header("Status: 404 Not Found");
  die($message);
}



/**
 * Read directory and return all items in a ul/li list.
 *
 * @param string $path to the current gallery directory.
 * @param array $validImages to define extensions on what are considered to be valid images.
 * @return string html with ul/li to display the gallery.
 */
function readAllItemsInDir($path, $validImages = array('png', 'jpg', 'jpeg')) {
  $files = glob($path . '/*'); 
  $gallery = "<ul class='gallery'>\n";
  $len = strlen($this->galleryPath);

  foreach($files as $file) {
    $parts = pathinfo($file);

    // Is this an image or a directory
    if(is_file($file) && in_array($parts['extension'], $validImages)) {
      $item    = "<img src='img.php?src=" . $this->baseUrl . substr($file, $len + 1) . "&amp;width=$this->thumbSize&amp;height=$this->thumbSize&amp;crop-to-fit' alt=''/>";
      $caption = basename($file); 
    }
    elseif(is_dir($file)) {
      $item    = "<img src='public/folder.png' alt=''/>";
      $caption = basename($file) . '/';
    }
    else {
      continue;
    }

    // Avoid to long captions breaking layout
    $fullCaption = $caption;
    if(strlen($caption) > 18) {
      $caption = substr($caption, 0, 10) . '…' . substr($caption, -5);
    }

    $href = substr($file, $len);
    $gallery .= "<li><a href='?path={$href}' title='{$fullCaption}'><figure class='figure overview'>{$item}<figcaption>{$caption}</figcaption></figure></a></li>\n";
  }
  $gallery .= "</ul>\n";

  return $gallery;
}



/**
 * Read and return info on choosen item.
 *
 * @param string $path to the current gallery item.
 * @param array $validImages to define extensions on what are considered to be valid images.
 * @return string html to display the gallery item.
 */
function readItem($path, $validImages = array('png', 'jpg', 'jpeg')) {

  $parts = pathinfo($path);
  if(!(is_file($path) && in_array($parts['extension'], $validImages))) {
    return "<p>This is not a valid image for this gallery.";
  }

  // Get info on image
  $imgInfo = list($width, $height, $type, $attr) = getimagesize($path);
  $mime = $imgInfo['mime'];
  $gmdate = gmdate("D, d M Y H:i:s", filemtime($path));
  $filesize = round(filesize($path) / 1024); 

  // Get constraints to display original image
  $displayWidth  = $width > $this->displayWidth ? "&amp;width=$this->displayWidth" : null;
  $displayHeight = $height > $this->displayHeight ? "&amp;height=$this->displayHeight" : null;

  // Display details on image
  $len = strlen($this->galleryPath);
  $href = $this->baseUrl . substr($path, $len);
  $item = <<<EOD
<p><img src='img.php?src={$href}{$displayWidth}{$displayHeight}' alt=''/></p>
<p>Original image dimensions are {$width}x{$height} pixels. <a href='img.php?src={$href}'>View original image</a>.</p>
<p>File size is {$filesize}KBytes.</p>
<p>Image has mimetype: {$mime}.</p>
<p>Image was last modified: {$gmdate} GMT.</p>
EOD;

  return $item;
}



/**
 * Create a breadcrumb of the gallery query path.
 *
 * @param string $path to the current gallery directory.
 * @return string html with ul/li to display the thumbnail.
 */
function createBreadcrumb() {
  
  $path = $this->pathToGallery;
  
  $parts = explode('/', trim(substr($path, strlen($this->galleryPath)), '/'));
  $breadcrumb = "<ul class='breadcrumb'>\n<li><a href='?'>Hem</a> »</li>\n";

  if(!empty($parts[0])) {
    $combine = null;
    foreach($parts as $part) {
      $combine .= ($combine ? '/' : null) . $part;
      $breadcrumb .= "<li><a href='?path=/{$combine}'>$part</a> » </li>\n";
    }
  }

  $breadcrumb .= "</ul>\n";
  echo $breadcrumb;
}



}