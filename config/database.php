<?php
$host = 'mysql'; 
$db = 'tictactoe';     
$user = 'user';      
$pass = 'password'; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    return $pdo;
} catch (PDOException $e) {
    error_log('Ошибка подключения к базе данных: ' . $e->getMessage());
    die('Ошибка подключения к базе данных.');
}
?>