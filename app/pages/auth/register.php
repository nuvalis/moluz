<div class="col-4">
    <?php

        $user = new MUser();

        if(isset($_POST["Register"])){

            $user->createUser($_POST["username"], $_POST["password"]);
            header("Location: login.php");
            die();

        }

    ?>  
</div>

<div class="col-4 center">
    <h2>Register Account</h2>
    
    <form method="post" action="">

        <input type="text" name="username" placeholder="Username">
        <input type="password" name="password" placeholder="Password">
        <input class="button-apply" type="submit" name="Register" value="Register">

    </form>
</div>

<div class="col-4"></div>