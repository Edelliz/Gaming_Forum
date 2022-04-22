<?php

require_once "vendor.php";
require_once "functions.php";

function route($method, $urlData, $formData)
{
    if ($method == 'POST') {
        $headers = getallheaders();
        $username = $formData->Username;
        $password = $formData->Password;

        global $connect;
        $suchUser = mysqli_fetch_array($connect->query("SELECT * FROM `user` WHERE `user`.`Username` = '$username' AND `user`.`Password` = '$password'"));
        if (isset($suchUser)) {
            if (isset($suchUser["Token"]) && isset($headers["Authorization"])) {
                if ("Bearer " . $suchUser["Token"] == $headers["Authorization"]) {
                    http_response_code(405);
                    exit();
                }

                http_response_code(409);
            } else {
                $token = generateToken();
                $connect->query("UPDATE `user` SET `token` = '$token' WHERE `user`.`Username` = '$username' AND `user`.`Password` = '$password'");
                $role = GetAccessLevel("Bearer " . $token);

                echo json_encode(array('Token' => $token, 'Role' => $role, 'UserId' => (int)$suchUser["Id"]), JSON_FORCE_OBJECT);
            }
        } else {
            http_response_code(404);
        }

    } else {
        http_response_code(501);
    }
    exit();
}

?>