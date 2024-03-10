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
$conn = new PDO("mysql:host=$servername;dbname=$dbname", $usernameDB, $passwordDB);

try{$data  = json_decode(file_get_contents("php://input"));
    $url = $data->url; }
catch (Exception $e){}

// Проверка ссылки
function check_url($url)
{
    if(preg_match("@^http://@i",$url)) $url = preg_replace("@(http://)+@i",'http://',$url);
    else if (preg_match("@^https://@i",$url)) $url = preg_replace("@(https://)+@i",'https://',$url);
    else $url = 'http://'.$url;

    if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
        return false;
    }
    else return $url;
}
// Существование ссылки (URL)
function open_url($url)
{
    $url_c = parse_url($url);

    if (!empty($url_c['host']) and checkdnsrr($url_c['host'])) {
        // Ответ сервера
        if ($otvet = @get_headers($url)) {
            return substr($otvet[0], 9, 3);
        }
    }
    return false;
}
    if ($url = check_url($url)) {
        // ссылка корректная
        if ($o = open_url($url)) {
            echo "Ответ сервера " . $o;
            check_url_db($url, $conn);
        } else {
            echo "Сервер не отвечает";
        }
    } else echo "Некорректная ссылка";//Вызов проверки


//проверка существования url адреса в бд
function check_url_db($url, $conn){
    $stmt = $conn->prepare("SELECT * FROM db WHERE url = :url");
    $stmt->bindParam(':url', $url);
    $stmt->execute();

    $row_count = $stmt->rowCount();  // Получаем количество строк в результате запроса

    if ($row_count > 0) {
        $response = array( "message" => "URL already exists");
        echo json_encode($response);
    } else {
        $short_url = create_short_url($url, $conn);
        $response = array("url" => $short_url);
        echo json_encode($response);
    }
}
//создание короткой ссылки
function create_short_url($url, $conn){
    $half = substr(str_shuffle(implode(range('a', 'z'))), 0, 6);
    $short_url = "/".$half;
    $times = get_time();
    $time_stemp = $times['time_stemp'];

    // Подготовленный запрос для добавления записи в базу данных
    $stmt = $conn->prepare("INSERT INTO db (url, short_url, time) VALUES (:url, :short_url, :timestemp)");
    $stmt->bindParam(':url', $url);
    $stmt->bindParam(':short_url', $short_url);
    $stmt->bindParam(':timestemp', $time_stemp);
    $stmt->execute();

    return $short_url; // возвращаем короткую ссылку для использования
}
 function get_time(){
    $now_time = time();
    $time_stemp = $now_time + 300;
    return array('now_time' => $now_time, 'time_stemp' => $time_stemp);
}
?>
