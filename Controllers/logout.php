<?php

require_once "vendor.php";
require_once "functions.php";

function route($method, $urlData, $formData)
{
    if ($method == 'POST') {
        $headers = getallheaders();

        global $connect;

        if (isset($headers["Authorization"])) {
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $suchUser = mysqli_fetch_array($connect->query("SELECT * FROM `user` WHERE `user`.`Token` = '$token'"));

            if (isset($suchUser)) {
                $connect->query("UPDATE `user` SET `Token` = '' WHERE `user`.`Token` = '$token'");
            } else {
                http_response_code(401);
                exit();
            }
        } else {
            http_response_code(401);
            exit();
        }
    } else {
        http_response_code(501);
        exit();
    }
}

?>