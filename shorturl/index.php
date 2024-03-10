<?php
require_once(__DIR__ . '/main.php');


// Определение маршрутов
$routes = [
    '/' => 'homePage',
];

// Получение запрошенного URL
$url = $_SERVER['REQUEST_URI'];

// Поиск соответствующего обработчика для запрошенного маршрута
if (array_key_exists($url, $routes)) {
    $handler = $routes[$url];
    call_user_func($handler);
} else {
    $servername = "localhost";
    $dbname = "short_url";
    $usernameDB = "root";
    $passwordDB = "";
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $usernameDB, $passwordDB);
    $check=check_short_url_db($url, $conn) ;
    if ($check){
        header("Location: $check");
    }
    else{
        header("Location: /page.html");
    }
}

// Обработчики маршрутов
function homePage()
{
    header("Location: /page.html");;//TODO вернуть page.html
}

function check_short_url_db($url, $conn){
    $time=time();
    $stmt = $conn->prepare("SELECT * FROM db WHERE short_url = :url AND time >= :time");
    $stmt->bindParam(':url', $url);
    $stmt->bindParam(':time', $time);
    $stmt->execute();
    $row_count = $stmt->rowCount();
    // Получаем количество строк в результате запроса
    if ($row_count > 0) {
        $long_url = $stmt->fetch();
       return $long_url['url'];
    } else {
        return False;
    }
}
?>