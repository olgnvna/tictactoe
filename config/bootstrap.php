<?php
require_once __DIR__ . '/database.php';

spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../controllers/' . $class . '.php',
        __DIR__ . '/../models/' . $class . '.php',
    ];
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            break;
        }
    }
});

$pdo = require __DIR__ . '/database.php';

$userController = new UserController($pdo);
$gameController = new GameController($pdo);

return [
    'userController' => $userController,
    'gameController' => $gameController,
    'pdo' => $pdo
];
