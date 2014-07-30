<?php
require_once 'loader.php';
$app = new \VKPollsDataGrabber\Application();

$at = !empty($_GET['access_token']) ? $_GET['access_token'] : '';
if (strlen($at) > 0 && !preg_match('/[a-z0-9]/', $at)) {
    die('Invalid access token');
}

$app->setAccessToken($at);
?>
<!DOCTYPE html>
<html>
    <body>
        <script type="text/javascript">
            window.location.href = "/auth.php";
        </script>
    </body>
</html>