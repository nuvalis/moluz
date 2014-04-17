<?php

require_once __DIR__ . "/MImage.php";

$img = new MImage();

$img->openOriginalFile();

#$img->calculateNewWidthHeigth(300, 300);

#$img->resizeImage();

#$img->sharpenImage();

#$img->sepia();

$img->createCacheFilename();

$img->saveAs();

$img->checkCache();

#$img->outputImage();

?>







