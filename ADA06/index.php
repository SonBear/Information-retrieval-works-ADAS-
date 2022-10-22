<?php
define('_ROOT_', __DIR__);

require _ROOT_ . "/inc/bootstrap.php";

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

if (($uri[3] !== 'documents')) {
    header("HTTP/1.1 404 Not Found");
    exit();
}

require _ROOT_ . "/Controller/Api/DocumentController.php";

$objFeedController = new DocumentController();
$strMethodName = $uri[3] . 'Action';
$objFeedController->{$strMethodName}();
