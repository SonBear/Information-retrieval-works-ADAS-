<?php
$root = __DIR__;
define('__ROOT__', $root);

$URI_BASE = $_SERVER['REQUEST_URI'];
define('__ROOT_URI__', $URI_BASE);

$url = 'C:\Users\IGNITER\Desktop\test.txt';

# save document


$section = file_get_contents($url, FALSE);
$section = preg_replace('/[.,]/', '', $section);
foreach ((explode(' ', $section)) as $word) {
}
var_dump($section);


require_once __DIR__ . '/controllers/HomeController.php';
