<?php

include "app/config.php";

$imageDir = __DIR__ . "/public/uploads/gallery/";
$cachePath = __DIR__ . "/public/cache/";

$img = new MImage($imageDir, $cachePath);
        
    if(isset($_GET["width"]) AND isset($_GET["height"]))
    {
        
        //Check if crop-to-fit is set
        if(isset($_GET["crop-to-fit"]))
        {
            $img->cropToFit = true;
            $img->newWidth = $_GET["width"];
            $img->newHeight = $_GET["height"];
            $img->sharpen = true;
            $img->createCacheFilename();
            $img->checkCache(); //Exit and Output 
            
            //Crop TO FIT if not found
            $img->openOriginalFile();
            $img->calculateNewWidthHeigth($_GET["width"], $_GET["height"]);
            $img->resizeImage();
            $img->sharpenImage();
            $img->createCacheFilename();
            $img->saveAs();
            $img->checkCache(); //Exit and Output 
        }        
        
        //Check Cache Again for standard Request
        $img->newWidth = $_GET["width"];
        $img->newHeight = $_GET["height"];
        $img->sharpen = true;
        $img->createCacheFilename();
        $img->checkCache(); //Exit and Output        
        
        //If checkCache failed create new image
        $img->openOriginalFile();
        $img->calculateNewWidthHeigth($_GET["width"], $_GET["height"]);
        $img->resizeImage();
        $img->sharpenImage();
        $img->createCacheFilename();
        $img->saveAs();
        $img->checkCache(); //Exit and Output   
    
    }
    
    // Original File
    $img->outputImage($img->pathToImage);








