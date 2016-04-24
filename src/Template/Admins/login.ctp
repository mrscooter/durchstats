<form class="my_form" action="<?= $this->Url->build(["controller" => "Admins", "action" => "login"]) ?>" method="post" accept-charset="UTF-8">
    <input type="text" name="login" value="login" />
    <input type="password" name="password" value="superheslo" />
    <input type="submit" name="submit" value="Prihlás ma!" />
</form>
<div class="input_error">
    <?php
        if(isset($errors['login']['match'])){
            echo $errors['login']['match'];
        }
    ?>
</div>