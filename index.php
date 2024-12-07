<?php

use App\Routing\Router;

require_once __DIR__.'/vendor/autoload.php';

session_start();

// Rediriger si l'URL est exactement index.php
if ($_SERVER['REQUEST_URI'] === '/index.php') {
    header('Location: /');
    exit();
}

$error = null;
$router = new Router($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
$data = $router->doAction();

if ($router->isReturnJson()) {
    echo $data;
} else {
    $error = $data['message'] ?? null;
    $page = 'app/View/'.$data['template'].'.php';
    $jsFile = '../asset/js/'.$data['template'].'.js';

    require_once 'app/View/base.php';
}