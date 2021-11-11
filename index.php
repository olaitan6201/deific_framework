<?php include "config/autoload.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=env('APP_NAME')?></title>

    <link rel="stylesheet" href="<?=app_asset('css/main.css')?>">
</head>
<body>
    <?php 
    print_r(crud()->insert('users', 'name, email,id'));

    if(isset($_POST['submit'])) print_r($_REQUEST);
    
    ?>
    <form method="post">
        <input type="text" name="name">
        <input type="submit" name="submit">
    </form>

    <script src="<?=app_asset('js/main.js')?>"></script>
</body>
</html>