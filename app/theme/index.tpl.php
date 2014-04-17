<!doctype html>
<html lang='<?=$lang?>'>
<head>
<meta charset='utf-8'/>
<title><?=get_title(ucfirst($title))?></title>
<?php if(isset($favicon)): ?><link rel='shortcut icon' href='<?=$favicon?>'/><?php endif; ?>
<link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
<?php foreach($stylesheets as $stylesheet): ?>
    <link rel='stylesheet' type='text/css' href='<?=$stylesheet?>'/>
<?php endforeach; ?>
</head>
<body>
  <div id='wrapper'>
                
    <header id='header'>
        <?php include $header ?>
        <?= $nav ?>
    </header>
    
    <main id='main'>
        <?php $flash->show(); ?>
        <?php include $main ?>
    </main>
    
    <footer id='footer'>
        <?php include $footer ?>
        <div class="dump">
        <?php if($dump){ echo dump($moluz); } ?>
        </div>
    </footer>
    
  </div>
</body>
</html>


