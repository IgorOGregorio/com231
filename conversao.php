<?php
include_once "connection.php";
header('content-type:text/html;charset=utf-8');
date_default_timezone_set('America/Sao_Paulo');

// $http_origin = $_SERVER['HTTP_ORIGIN'];

// if ($http_origin == "https://localhost:80") {
//     header("Access-Control-Allow-Origin: " . $http_origin);
//     header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
//     header('Access-Control-Max-Age: 1000');
//     header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
// }

header("Access-Control-Allow-Origin: * ");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 1000");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding");
header("Access-Control-Allow-Methods: PUT, POST, GET, OPTIONS, DELETE");
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'GET':
        //get coversao
        $moeda_base = $_REQUEST['moeda_base'];
        $moeda_base = str_replace("'", "", $moeda_base);

        $sql = "SELECT id, data, moeda_base, moeda_conversao, valor from conversao";

        if (isset($_REQUEST['moeda_base'])) {
            $sql .= " where moeda_base = '" . $moeda_base . "'";
        }
        $result = mysqli_query($conn, $sql);

        foreach ($result as $idx => $data) {

            $conversao[] = [
                "id" => $data['id'],
                "data" => $data['data'],
                "moeda_base" => $data['moeda_base'],
                "moeda_conversao" => $data['moeda_conversao'],
                "valor" => $data['valor'],
            ];
        }

        echo json_encode($conversao, JSON_PRETTY_PRINT);
        return $conversao;
        break;

    case 'POST':
        //insert conversoes
        $inicio = $_REQUEST['inicio'];
        $inicio = str_replace("'", "", $inicio);

        $fim = $_REQUEST['fim'];
        $fim = str_replace("'", "", $fim);

        $moeda = $_REQUEST['moeda'];
        $moeda = str_replace("'", "", $moeda);

        $begin = new DateTime($inicio);
        $end = new DateTime($fim);

        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($begin, $interval, $end);

        foreach ($period as $dt) {
            $api = 'https://cdn.jsdelivr.net/gh/fawazahmed0/currency-api@1/' . $dt->format("Y-m-d") . '/currencies/' . $moeda . '.json';
            // echo $api.'<br>';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $api);
            $result = curl_exec($ch);
            curl_close($ch);
            $obj = json_decode($result);
            // echo $result. "<br><br><br>";

            foreach ($obj as $key => $value) {

                if ($key == $moeda) {
                    foreach ($value as $x => $y) {
                        $sql = "insert into conversao values (null, '" . $dt->format("Y-m-d") . "', '" . $moeda . "', '" . $x . "', '" . $y . "')";
                        $result = mysqli_query($conn, $sql);
                    }
                }
            }
        }
        break;
}
