<?php
$root = __DIR__;
define('__ROOT__', $root);

$URI_BASE = $_SERVER['REQUEST_URI'];
define('__ROOT_URI__', $URI_BASE);

require_once __DIR__ . '/controllers/HomeController.php';
