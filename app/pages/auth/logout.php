<div class="col-4">

<?php

    $content = new MContent();

    $auth->havePermission();

    if(isset($_POST["Logout"])){

        $auth->logout();

    }

    if(isset($_POST["Reset"])){

        $content->deleteTable();

    }

?>

</div>

<div class="col-4 center">
    <h3>Logout</h3>
    <form method="post" action="">

        <input class="button-apply" type="submit" name="Logout" value="Logout">
        <h3>Reset Content</h3>
        <input class="button-apply" type="submit" name="Reset" value="Reset Content">

    </form>
</div>
