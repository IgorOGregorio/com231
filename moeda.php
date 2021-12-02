<?php
include_once "connection.php";
header('content-type:text/html;charset=utf-8');
date_default_timezone_set('America/Sao_Paulo');

$http_origin = $_SERVER['HTTP_ORIGIN'];

if ($http_origin == "https://localhost:80") {
    header("Access-Control-Allow-Origin: " . $http_origin);
    header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
    header('Access-Control-Max-Age: 1000');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
}

$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'GET':
        //get moeda
        $id = $_REQUEST['id'];

        $sql = "SELECT id, sigla, nome from moeda";

        if (isset($_REQUEST['id'])) {
            $sql .= " where id = " . $id;
        }
        $result = mysqli_query($conn, $sql);

        foreach ($result as $idx => $data) {
            $moeda[] = [
                "id" => $data['id'],
                "sigla" => $data['sigla'],
                "nome" => $data['nome'],
            ];
        }

        echo json_encode($moeda, JSON_PRETTY_PRINT);
        return $moeda;
        break;

    case 'POST':
        //insert moeda
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, 'https://cdn.jsdelivr.net/gh/fawazahmed0/currency-api@1/latest/currencies.json');
        $result = curl_exec($ch);
        curl_close($ch);
        $obj = json_decode($result);

        foreach ($obj as $key => $value) {
            $sql = "insert into moeda values (null, '" . $key . "', '" . $value . "')";
            $result = mysqli_query($conn, $sql);

            if ($result === false) {
                break;
            }
        }
        break;

    case 'DELETE':

        $delete_conversao = "delete from conversao";
        $result_conversao = mysqli_query($conn, $delete_conversao);
        if($result_conversao === TRUE){
            $delete_moeda = "delete from moeda";
            $result_moeda = mysqli_query($conn, $delete_moeda);
            if($result_moeda === TRUE){
                echo 1;
            }else{
                echo 0;
            }
        }


        break;
}
