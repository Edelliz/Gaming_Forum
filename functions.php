<?php
require_once "vendor.php";
require_once "constants.php";

function generateToken(): String
{
    return bin2hex(random_bytes(20));
}

function GetAccessLevel($BearerToken): Int
{
    global $connect;

    if (isset($BearerToken)) {
        $token = str_replace("Bearer ", "", $BearerToken);

        $authorizedUser = mysqli_fetch_array($connect->query("SELECT * FROM `user` WHERE `user`.`token` = '$token'"));
        if (isset($authorizedUser)) {
            $Role_Id = mysqli_fetch_array($connect->query("SELECT RoleId FROM `user` WHERE `user`.`token` = '$token'"))[0];
            if (isset($Role_Id)) {
                $Role_Name = mysqli_fetch_array($connect->query("SELECT `Name` FROM `role` WHERE `role`.`id` = '$Role_Id'"))[0];
                if (ADMIN_ROLE_NAME === $Role_Name) {
                    return ADMIN_ACCESS_LEVEL;
                } else if (MODERATOR_ROLE_NAME === $Role_Name) {
                    return MODERATOR_ACCESS_LEVEL;
                } else if (USER_ROLE_NAME === $Role_Name) {
                    return USER_ACCESS_LEVEL;
                }
            }
            return USER_ACCESS_LEVEL; // Если вышло так, что не назначено роли, но пользователь существует
        } else {
            return UNAUTHORIZED_ACCESS_LEVEL; // Данного токена нет в БД
        }
    } else {
        return UNAUTHORIZED_ACCESS_LEVEL; // Header не содержит токен
    }
}

function UploadImage($userId, $postId)
{
    global $connect;

    $allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
    $detectedType = exif_imagetype($_FILES['File']['tmp_name']);
    if ($_FILES && $_FILES["File"]["error"] == UPLOAD_ERR_OK
        && in_array($detectedType, $allowedTypes)) {

        $name = htmlspecialchars(basename($_FILES["File"]["name"]));
        $path = "Uploads/Photos/" . time() . $name;
        if (move_uploaded_file($_FILES["File"]["tmp_name"], $path)) {
            $request = "INSERT INTO `photo`(`OwnerId`, `Link`, `PostId`) VALUES ($userId,'/$path', $postId)";
            $connect->query($request);
            $request = "SELECT * FROM `photo` WHERE Link = '/$path'";
            PrintJson($connect->query($request));
            exit();
        } else {
            echo 'ERROR';
        }
    }
}

function PrintJson($query_res)
{
    if (isset($query_res)) {
        $array = array();
        if ($query_res->num_rows > 0) {
            while ($row = $query_res->fetch_assoc()) {
                array_push($array, $row);
            }
        }
        echo json_encode($array);
    }
}