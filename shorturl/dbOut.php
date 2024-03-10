<?php
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

$servername = "localhost";
$dbname = "short_url";
$usernameDB = "root";
$passwordDB = "";

// Создаем подключение к базе данных
$conn = new PDO("mysql:host=$servername;dbname=$dbname", $usernameDB, $passwordDB);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Получаем текущее время
$time = time();

// Удаляем записи из таблицы 'db', где время меньше или равно текущему времени
$stmt_delete = $conn->prepare("DELETE FROM db WHERE time <= :time");
$stmt_delete->bindParam(':time', $time, PDO::PARAM_INT);
$stmt_delete->execute();

// Выбираем последнюю запись
$stmt_select = $conn->prepare("SELECT * FROM db");
if ($stmt_select->execute()) {
    $results = $stmt_select->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($results);
} else {
    echo "Error: Unable to execute the query";
}