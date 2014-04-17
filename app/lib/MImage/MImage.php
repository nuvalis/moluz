<?php 

class MImage
{ 

    private static $maxWidth = 2000;
    private static $maxHeight = 2000;
    private $cachePath;
    private $imageDir;
    private $verbose = false;
    private $fileExtension;
    private $mime;
    private $imageResource = null;
    private $imgInfo; //Array
    private $ignoreCache = false;
    
    public $cropToFit = false;     // Default is False
    public $sharpen = null;       // Default is null
    public $cropHeight;
    public $cropWidth;
    public $crop_x;
    public $crop_y;
    public $quality = 90;         // Default Quality
    public $saveAs = "png";      // Default fileExtension
    public $height;
    public $width;
    public $newWidth;
    public $newHeight;
    public $pathToImage;
    public $src;
    
    //
    // Define some constant values, append slash
    // Use DIRECTORY_SEPARATOR to make it work on both windows and unix.
    //
    function __construct($imageDir, $cachePath)
    {    
        $this->cachePath = $cachePath;
        $this->imageDir = $imageDir;
        
        if(isset($_GET["src"])){
            $this->src = $_GET["src"];
        }
        
        $chopPath = $this->imageDir . $this->src;
        
        $this->pathToImage = $chopPath;
        
        $this->validateArgs();
        $this->getImageInfo();
    }

    /**
     * Display error message.
     *
     * @param string $message the error message to display.
     */
    function errorMessage($message) {
      header("Status: 404 Not Found");
      die('img.php says 404 - ' . htmlentities($message));
    }



    /**
     * Display log message.
     *
     * @param string $message the log message to display.
     */
    function verbose($message) {
      echo "<p>" . htmlentities($message) . "</p>";
    }



    /**
     * Output an image together with last modified header.
     *
     * @param string $file as path to the image.
     * @param boolean $this->verbose if verbose mode is on or off.
     */
    function outputImage($file) {
                                                    
      $info = getimagesize($file);
      !empty($info) or $this->errorMessage("The file doesn't seem to be an image.");
      $mime   = $info['mime'];

      $lastModified = filemtime($file);  
      $gmdate = gmdate("D, d M Y H:i:s", $lastModified);

      if($this->verbose) {
        $this->verbose("Memory peak: " . round(memory_get_peak_usage() /1024/1024) . "M");
        $this->verbose("Memory limit: " . ini_get('memory_limit'));
        $this->verbose("Time is {$gmdate} GMT.");
      }

      if(!$this->verbose) header('Last-Modified: ' . $gmdate . ' GMT');
      if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastModified){
        
        if($this->verbose) { $this->verbose("Would send header 304 Not Modified, but its verbose mode. $file"); exit; }
        
        header('HTTP/1.0 304 Not Modified');
        
      } else {  
        
        if($this->verbose) { $this->verbose("Would send header to deliver image with modified time: {$gmdate} GMT, but its verbose mode."); exit; }
        
        header('Content-type: ' . $mime);
        readfile($file);        
        
      }
      exit;
    }



    /**
     * Sharpen image as http://php.net/manual/en/ref.image.php#56144
     * http://loriweb.pair.com/8udf-sharpen.html
     *
     * @param resource $image the image to apply this filter on.
     * @return resource $image as the processed image.
     */
    function sharpenImage() {
      
      $this->sharpen = true;
      
      $matrix = array(
        array(-1,-1,-1,),
        array(-1,16,-1,),
        array(-1,-1,-1,)
      );
      $divisor = 8;
      $offset = 0;
      imageconvolution($this->imageResource, $matrix, $divisor, $offset);
      
      if($this->verbose) { $this->verbose("Sharpen Effect Applied"); }
      return $this->imageResource;
      
    }
    
    function sepia() {
    
        imagefilter($this->imageResource, IMG_FILTER_GRAYSCALE);
        
        imagefilter($this->imageResource, IMG_FILTER_BRIGHTNESS, -10);
        
        imagefilter($this->imageResource, IMG_FILTER_CONTRAST, 10);
        
        imagefilter($this->imageResource, IMG_FILTER_COLORIZE, 30, 10, 10);

        return $this->imageResource;
    
    }
    
     /**
     * Create new image and keep transparency
     *
     * @param resource $image the image to apply this filter on.
     * @return resource $image as the processed image.
     */
    function createImageKeepTransparency($width, $height) {
        $img = imagecreatetruecolor($width, $height);
        imagealphablending($img, false);
        imagesavealpha($img, true);  
        return $img;
    }


    //
    // Validate incoming arguments
    //
    function validateArgs()
    {
    
        is_dir($this->imageDir) or $this->errorMessage('The image dir is not a valid directory.');
        is_writable($this->cachePath) or $this->errorMessage('The cache dir is not a writable directory.');
        isset($this->src) or $this->errorMessage('Must set src-attribute.');
        
        preg_match('#^[a-z0-9A-Z-_\.\/]+$#', $this->src) 
        or $this->errorMessage('Filename contains invalid characters.');
        
        #substr_compare($this->imageDir, $this->pathToImage, 0, strlen($this->imageDir)) == 0 
        #or $this->errorMessage('Security constraint: Source image is not directly below the directory IMG_PATH.');
        
        is_null($this->saveAs) or in_array($this->saveAs, 
        array('png', 'jpg', 'jpeg')) or $this->errorMessage('Not a valid extension to save image as');
        
        is_null($this->quality) or (is_numeric($this->quality) and $this->quality > 0 and $this->quality <= 100)
        or $this->errorMessage('Quality out of range');
         
        is_null($this->newWidth) or (is_numeric($this->newWidth) and $this->newWidth > 0 and $this->newWidth <= $this->maxWidth)
        or $this->errorMessage('Width out of range');
        
        is_null($this->newHeight) or (is_numeric($this->newHeight) and $this->newHeight > 0 
        and $this->newHeight <= $this->maxHeight) or $this->errorMessage('Height out of range');
        
        #is_null($this->cropToFit) or ($this->cropToFit and $this->newWidth 
        #and $this->newHeight) or $this->errorMessage('Crop to fit needs both width and height to work');
    
    }


    //
    // Start displaying log if verbose mode & create url to current image
    //
    function displayVerbose()
    {

        if($this->verbose) {
          $query = array();
          parse_str($_SERVER['QUERY_STRING'], $query);
          unset($query['verbose']);
          $url = '?' . http_build_query($query);


        echo <<<EOD
        <html lang='en'>
        <meta charset='UTF-8'/>
        <title>img.php verbose mode</title>
        <h1>Verbose mode</h1>
        <p><a href=$url><code>$url</code></a><br>
        <img src='{$url}' /></p>
EOD;
        
        }
    
    }
    




    //
    // Get information on the image
    //
    
    function getImageInfo()
    {
    
        $this->imgInfo = list($this->width, $this->height, $this->type, $this->attr) = getimagesize($this->pathToImage);
        !empty($this->imgInfo) or $this->errorMessage("The file doesn't seem to be an image.");
        $this->mime = $this->imgInfo['mime'];
        
        $parts = pathinfo($this->pathToImage);
        $this->fileExtension  = $parts['extension'];

        if($this->verbose) {
          $this->fileSize = filesize($this->pathToImage);
          $this->verbose("Image file: {$this->pathToImage}");
          $this->verbose("Image information: " . print_r($this->imgInfo, true));
          $this->verbose("Image width x height (type): {$this->width} x {$this->height} ({$this->type}).");
          $this->verbose("Image file size: {$this->fileSize} bytes.");
          $this->verbose("Image mime type: {$this->mime}.");
        }
    
    }
    




    //
    // Calculate new width and height for the image
    //
    
    function calculateNewWidthHeigth($newWidth, $newHeight) 
    {
            
        $aspectRatio = $this->width / $this->height;

        if($this->cropToFit && $newWidth && $newHeight) {
          $targetRatio = $newWidth / $newHeight;
          $this->cropWidth   = $targetRatio > $aspectRatio ? $this->width : round($this->height * $targetRatio);
          $this->cropHeight  = $targetRatio > $aspectRatio ? round($this->width  / $targetRatio) : $this->height;
          $this->newWidth = $newWidth;
          $this->newHeight = $newHeight;
          
          if($this->verbose) { $this->verbose("Crop to fit into box of {$this->newWidth}x{$this->newHeight}. Cropping dimensions: {$this->cropWidth}x{$this->cropHeight}."); }
          
        }
        else if($newWidth && !$newHeight) {
          $this->newHeight = round($newWidth / $aspectRatio);
          if($this->verbose) { $this->verbose("New width is known {$newWidth}, height is calculated to {$newHeight}."); }
        }
        else if(!$newWidth && $newHeight) {
          $this->newWidth = round($newHeight * $aspectRatio);
          if($this->verbose) { $this->verbose("New height is known {$newHeight}, width is calculated to {$newWidth}."); }
        }
        else if($newWidth && $newHeight) {
          $ratioWidth  = $this->width  / $newWidth;
          $ratioHeight = $this->height / $newHeight;
          $ratio = ($ratioWidth > $ratioHeight) ? $ratioWidth : $ratioHeight;
          $this->newWidth  = round($this->width  / $ratio);
          $this->newHeight = round($this->height / $ratio);
          if($this->verbose) { $this->verbose("New width & height is requested, keeping aspect ratio results in {$newWidth}x{$newHeight}."); }
        }
        else {
          $this->newWidth = $this->width;
          $this->newHeight = $this->height;
          if($this->verbose) { $this->verbose("Keeping original width & heigth."); }
        }
        
    }


    //
    // Creating a filename for the cache
    //
    function createCacheFilename()
    {
    
        $parts          = pathinfo($this->pathToImage);
        $fileExtension  = $parts['extension'];
        $saveAs         = is_null($this->saveAs) ? $fileExtension : $this->saveAs;
        $quality_       = is_null($this->quality) ? null : "_q{$this->quality}";
        $cropToFit_     = is_null($this->cropToFit) ? null : "_cf";
        $sharpen_       = is_null($this->sharpen) ? null : "_s";
        $dirName        = $parts['dirname'];
        $cacheFileName = $this->cachePath . sha1("-{$dirName}-{$parts['filename']}_{$this->newWidth}_{$this->newHeight}{$quality_}{$cropToFit_}{$sharpen_}.{$saveAs}");
        
        
        $this->cacheFileName = $cacheFileName;

        if($this->verbose) { $this->verbose("Cache file is: {$cacheFileName}"); }
    
    }


    //
    // Is there already a valid image in the cache directory, then use it and exit
    //
    
    function checkCache()
    {
    
        $imageModifiedTime = filemtime($this->pathToImage);
        $cacheModifiedTime = is_file($this->cacheFileName) ? filemtime($this->cacheFileName) : null;

        // If cached image is valid, output it.
        if(!$this->ignoreCache && is_file($this->cacheFileName) && $imageModifiedTime < $cacheModifiedTime) {
          if($this->verbose) { $this->verbose("Cache file is valid, output it."); }
          $this->outputImage($this->cacheFileName);
        }

        if($this->verbose) { $this->verbose("Cache is not valid, process image and create a cached version of it."); }
    
    }
    



    //
    // Open up the original image from file
    //
    
    function openOriginalFile()
    {
        
        if($this->verbose) { $this->verbose("File extension is: {$this->fileExtension}"); }

        switch($this->fileExtension) {  
          case 'jpg':
          case 'jpeg': 
            $this->imageResource = imagecreatefromjpeg($this->pathToImage);
            if($this->verbose) { $this->verbose("Opened the image as a JPEG image."); }
            break;  

          case 'png':  
            $this->imageResource = imagecreatefrompng($this->pathToImage); 
            if($this->verbose) { $this->verbose("Opened the image as a PNG image."); }
            break;  

          default: $this->errorMessage('No support for this file extension.');
        }

    }

    //
    // Resize the image if needed
    //
    
    function resizeImage()
    {
            
        if($this->cropToFit) {
          if($this->verbose) { $this->verbose("Resizing, crop to fit."); }
          
          $cropX = round(($this->width - $this->cropWidth) / 2);  
          $cropY = round(($this->height - $this->cropHeight) / 2);    
                  
          $imageResized = imagecreatetruecolor($this->newWidth, $this->newHeight);
          
          imagecopyresampled($imageResized, $this->imageResource, 0, 0,
           $cropX, $cropY, $this->newWidth, $this->newHeight,
           $this->cropWidth, $this->cropHeight);
          
          $this->imageResource = $imageResized;
          $this->width = $this->newWidth;
          $this->height = $this->newHeight;
          
        }  else if(!($this->newWidth == $this->width &&
                   $this->newHeight == $this->height)) {           
           
          if($this->verbose) { $this->verbose("Resizing, new height and/or width."); }
          $imageResized = imagecreatetruecolor($this->newWidth, $this->newHeight);
          
          imagecopyresampled($imageResized, $this->imageResource,
          0, 0, 0, 0, $this->newWidth, $this->newHeight, $this->width, $this->height);
          
          $this->imageResource  = $imageResized;
          $this->width  = $this->newWidth;
          $this->height = $this->newHeight;
        }
        
    }

    //
    // Save the image
    //
    function saveAs() {

        switch($this->saveAs) {
          case 'jpeg':
          case 'jpg':
            if($this->verbose) { $this->verbose("Saving image as JPEG to cache using quality = {$this->quality}."); }
            imagejpeg($this->imageResource, $this->cacheFileName, $this->quality);
          break;  

          case 'png':  
            if($this->verbose) { $this->verbose("Saving image as PNG to cache."); }
            imagepng($this->imageResource, $this->cacheFileName);  
          break;  

          default:
            $this->errorMessage('No support to save as this file extension.');
          break;
        }

        if($this->verbose) { 
          clearstatcache();
          $cacheFilesize = filesize($this->cacheFileName);
          $this->verbose("File size of cached file: {$cacheFilesize} bytes."); 
          $this->verbose("Cache file has a file size of " . round($cacheFilesize/$this->fileSize*100) . "% of the original size.");
        }

    }




}