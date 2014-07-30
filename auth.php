<?php
require_once 'loader.php';
$app = new \VKPollsDataGrabber\Application();
$result = $app->auth();
?>
<!DOCTYPE html>
<html>
    <head>
        <title>VKPollsDataGrabber - Authorization</title>
    </head>
    <body>
        <?php if (is_string($result)): ?>
        <h3>Авторизация в ВКонтакте</h3>
        <form action="" method="get">
            <input type="button" value="Авторизоваться" onclick="window.location.href='<?php echo $result; ?>'">
        </form>
        <?php endif; ?>
        <h3>Токен для доступа к методам API ВКонтакте</h3>
        <form action="/store_access_token.php" method="get">
            <input type="text" name="access_token" placeholder="Ваш токен"<?php if ($result === true): ?> value="<?php echo $app->getAccessToken(); ?>"<?php endif; ?>>
            <input type="submit" value="Сохранить">
        </form>
    </body>
</html>
