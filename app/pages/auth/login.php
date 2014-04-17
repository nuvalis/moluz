<div class="col-4">
    <?php

    if(isset($_POST["Login"])){

        $auth->login($_POST["username"], $_POST["password"]);
        header("Location: logout.php");
        die();
        
    }
    
    if($auth->isLoggedIn()){
      header("Location: logout.php");
      die();
    }

    ?>  
</div>

<div class="col-4 center">
    <form method="post" action="">
    
        <h2>Login</h2>

        <input type="text" name="username" placeholder="Username">
        <input type="password" name="password" placeholder="Password">
        <input class="button-apply" type="submit" name="Login" value="Login">

    </form>
    <div class="col-12"><a href="register.php">Register new account</a></div>
</div>

<div class="col-4"><h3><a href="secure_test.php">Test Permission</a></h3></div>