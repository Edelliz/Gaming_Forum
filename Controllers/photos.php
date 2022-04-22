<?php

require_once "vendor.php";
require_once "functions.php";

function route($method, $urlData, $formData)
{
    switch ($method) {
        case 'DELETE':
            Delete($urlData);
            break;
        default:
            http_response_code(501);
            break;
    }
}

function Delete($urlData)
{
    $headers = getallheaders();
    global $connect;
    if (is_numeric($urlData[0])) {
        $photoId = $urlData[0];

        if (isset($headers["Authorization"])) $generalAccessLevel = GetAccessLevel($headers["Authorization"]);
        else $generalAccessLevel = UNAUTHORIZED_ACCESS_LEVEL;

        if ($generalAccessLevel == ADMIN_ACCESS_LEVEL || $generalAccessLevel == MODERATOR_ACCESS_LEVEL) {
            $request = "DELETE FROM `photo` WHERE Id = '$photoId'";
            $connect->query($request);
            echo json_encode("success");
        } else if ($generalAccessLevel != UNAUTHORIZED_ACCESS_LEVEL) {
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $userId = mysqli_fetch_array($connect->query("SELECT Id FROM `user` WHERE `user`.`Token` = '$token'"))[0];
            $photoOwnerId = mysqli_fetch_array($connect->query("SELECT OwnerId FROM `photo` WHERE `photo`.`Id` = '$photoId'"))[0];

            if ($userId === $photoOwnerId) {
                $request = "DELETE FROM `photo` WHERE Id = '$photoId'";
                $connect->query($request);
                echo json_encode("success");
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