<?php

require_once "vendor.php";
require_once "functions.php";

function route($method, $urlData, $formData)
{
    switch ($method) {
        case 'GET':
            Get($urlData, $formData);
            break;
        case 'POST':
            Post($urlData, $formData);
            break;
        case 'PATCH':
            Patch($urlData, $formData);
            break;
        case 'DELETE':
            Delete($urlData);
            break;
        default:
            http_response_code(501);
            break;
    }
}

function Get($urlData, $formData)
{
    switch (sizeof($urlData)) {
        case 0:
            GetAllUsers();
            break;
        case 1:
            if (is_numeric($urlData[0])) {
                GetUser($urlData);
            } else {
                http_response_code(501);
            }
            break;
        default:
            http_response_code(501);
            break;
    }
}

function GetAllUsers()
{
    $headers = getallheaders();
    global $connect;

    if (isset($headers["Authorization"])) $generalAccessLevel = GetAccessLevel($headers["Authorization"]);
    else $generalAccessLevel = UNAUTHORIZED_ACCESS_LEVEL;

    if ($generalAccessLevel == ADMIN_ACCESS_LEVEL) {
        $request = "SELECT `user`.Id, `user`.Name, Surname, Username, Birthday, Avatar, Status, `role`.Name as Role FROM user JOIN role ON `role`.Id = `user`.RoleId";
        $users = $connect->query($request);
        PrintJson($users);
    } else {
        $request = "SELECT `user`.Id, `user`.Name, Surname, Username, Avatar, Status FROM user";
        $users = $connect->query($request);
        PrintJson($users);
    }
}

function GetUser($urlData)
{
    $userId = $urlData[0];

    $headers = getallheaders();
    global $connect;

    if (isset($headers["Authorization"])) $generalAccessLevel = GetAccessLevel($headers["Authorization"]);
    else $generalAccessLevel = UNAUTHORIZED_ACCESS_LEVEL;

    if ($generalAccessLevel == ADMIN_ACCESS_LEVEL) {
        $request = "SELECT `user`.Id, `user`.Name, Surname, Username, Birthday, Avatar, Status, `role`.Name as Role FROM user JOIN role ON `role`.Id = `user`.RoleId WHERE `user`.Id = $userId";
        $users = $connect->query($request);
        PrintJson($users);
    } else {
        $request = "SELECT `user`.Id, `user`.Name, Surname, Username, Avatar, Status FROM user WHERE `user`.Id = $userId";
        $users = $connect->query($request);
        PrintJson($users);
    }
}

function Post($urlData, $formData)
{
    switch (sizeof($urlData)) {
        case 0:
            RegisterUser($formData);
            break;
        case 2:
            if ($urlData[1] == "avatar") {
                if (is_numeric($urlData[0]))
                    ChangeAvatar($urlData[0]);
            }
            break;
        default:
            http_response_code(501);
            break;
    }
}

function ChangeAvatar($userId)
{
    $headers = getallheaders();
    global $connect;

    if (isset($headers["Authorization"])) $generalAccessLevel = GetAccessLevel($headers["Authorization"]);
    else $generalAccessLevel = UNAUTHORIZED_ACCESS_LEVEL;

    if ($generalAccessLevel != UNAUTHORIZED_ACCESS_LEVEL) {
        $token = str_replace("Bearer ", "", $headers["Authorization"]);
        $currentUserId = mysqli_fetch_array($connect->query("SELECT Id FROM `user` WHERE `user`.`Token` = '$token'"))[0];

        if ($generalAccessLevel == ADMIN_ACCESS_LEVEL || $currentUserId == $userId) {

            $allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
            $detectedType = exif_imagetype($_FILES['File']['tmp_name']);
            if ($_FILES && $_FILES["File"]["error"] == UPLOAD_ERR_OK
                && in_array($detectedType, $allowedTypes)) {

                $name = htmlspecialchars(basename($_FILES["File"]["name"]));
                $path = "Uploads/Avatars/" . time() . $name;
                if (move_uploaded_file($_FILES["File"]["tmp_name"], $path)) {
                    $request = "SELECT Avatar FROM `user` WHERE `user`.Id = $userId";
                    $avatar = mysqli_fetch_array($connect->query($request))[0];

                    if ($avatar != null) {
                        if (!unlink(substr($avatar, 1))) {
                            echo 'Error while deleting old avatar from server.';
                        }
                    }
                    $request = "UPDATE `user` SET Avatar = '/$path' WHERE Id = $userId";
                    $connect->query($request);
                    $request = "SELECT `user`.Id, `user`.Name, Surname, Username, Birthday, Avatar, Status, `role`.Name as Role FROM user JOIN role ON `role`.Id = `user`.RoleId WHERE `user`.Id = $userId";
                    PrintJson($connect->query($request));
                    exit();
                } else {
                    echo 'ERROR';
                }
            }
        } else {
            http_response_code(401);
            exit();
        }
    } else {
        http_response_code(401);
        exit();
    }
}

function RegisterUser($formData)
{
    $headers = getallheaders();
    global $connect;

    if (isset($headers["Authorization"])) $generalAccessLevel = GetAccessLevel($headers["Authorization"]);
    else $generalAccessLevel = UNAUTHORIZED_ACCESS_LEVEL;

    $name = $formData->Name;
    $surname = $formData->Surname;
    $username = $formData->Username;
    $password = $formData->Password;
    $birthday = null;
    if (isset($formData->Birthday)) {
        $birthday = $formData->Birthday;
    }

    if ($generalAccessLevel == ADMIN_ACCESS_LEVEL || $generalAccessLevel == UNAUTHORIZED_ACCESS_LEVEL) {
        CheckForUniqueUsername($username);

        $connect->query("INSERT INTO `user` (`Id`, `Name`, `Surname`, `Password`, `Birthday`, `Avatar`, `Status`, `Username`, `Token`, `RoleId`)
                    VALUES (NULL, '$name', '$surname', '$password', '$birthday', NULL, NULL, '$username', NULL, 1)");
    }

    if ($generalAccessLevel == UNAUTHORIZED_ACCESS_LEVEL) {
        global $connect;
        $token = generateToken();
        $connect->query("UPDATE `user` SET `Token` = '$token' WHERE `user`.`Username` = '$username' AND `user`.`Password` = '$password'");

        echo json_encode($token);
    }
}

function CheckForUniqueUsername($username, $id = null)
{
    global $connect;

    $similarUser = $connect->query("SELECT * FROM `user` WHERE `user`.`Username` = '$username'");
    if ($similarUser->num_rows != 0) {
        if ($similarUser->num_rows == 1 && $id != NULL) {
            if ($similarUser->fetch_array()['Id'] == $id) {
                return;
            }
        }
        http_response_code(409);
        echo json_encode([
            'status' => false,
            'message' => 'Username already used'
        ]);
        exit();
    }
}

function Patch($urlData, $formData)
{
    switch (sizeof($urlData)) {
        case 1:
            if (is_numeric($urlData[0])) {
                EditUser($urlData, $formData);
            } else {
                http_response_code(400);
            }
            break;
        case 2:
            switch ($urlData[1]) {
                case "status":
                    SetUserStatus($urlData, $formData);
                    break;
                case "role":
                    SetUserRole($urlData, $formData);
                    break;
                default:
                    http_response_code(501);
                    break;
            }
            break;
        default:
            http_response_code(501);
            break;
    }
}

function EditUser($urlData, $formData)
{
    $headers = getallheaders();
    global $connect;

    $userId = $urlData[0];
    $name = $formData->Name;
    $surname = $formData->Surname;
    $username = $formData->Username;
    $password = $formData->Password;
    $birthday = null;
    if (isset($formData->Birthday)) {
        $birthday = $formData->Birthday;
    }
    $avatar = $formData->Avatar;

    if (isset($headers["Authorization"])) $generalAccessLevel = GetAccessLevel($headers["Authorization"]);
    else $generalAccessLevel = UNAUTHORIZED_ACCESS_LEVEL;

    if ($generalAccessLevel != UNAUTHORIZED_ACCESS_LEVEL) {
        $token = str_replace("Bearer ", "", $headers["Authorization"]);
        $currentUserId = mysqli_fetch_array($connect->query("SELECT Id FROM `user` WHERE `user`.`Token` = '$token'"))[0];

        if ($generalAccessLevel == ADMIN_ACCESS_LEVEL || $currentUserId == $userId) {


            $newRequest = 'UPDATE `user` SET ';
            if ($name != "") {
                $newRequest .= "Name='$name', ";
            }

            if ($surname != "") {
                $newRequest .= "Surname='$surname', ";
            }

            if ($username != "") {
                CheckForUniqueUsername($username);
                $newRequest .= "UserName='$username', ";
            }

            if ($password != "") {
                $newRequest .= "Password='$password', ";
            }

            if ($birthday != "") {
                $newRequest .= "Birthday='$birthday', ";
            }

            if ($avatar != "") {
                $newRequest .= "Avatar='$avatar' ";
            } else {
                mb_substr($newRequest, 0, -2);
            }


            $newRequest .= "WHERE Id=$userId";
            if (!$connect->query($newRequest)) {
                echo "U've mistaken";
            }

            $request = "SELECT * FROM user WHERE Id =$userId";

            $user = $connect->query($request);

            unset($user->Password);
            PrintJson($user);
        } else {
            http_response_code(401);
            exit();
        }
    } else {
        http_response_code(401);
        exit();
    }

}

function SetUserStatus($urlData, $formData)
{
    $headers = getallheaders();
    global $connect;

    $userId = $urlData[0];
    $status = $formData->status;

    if ($status == "Online" || $status == "Offline" || $status == "Do not disturb" || $status == "In panic" || $status == "Want to die") {

        if (isset($headers["Authorization"])) $generalAccessLevel = GetAccessLevel($headers["Authorization"]);
        else $generalAccessLevel = UNAUTHORIZED_ACCESS_LEVEL;

        if ($generalAccessLevel != UNAUTHORIZED_ACCESS_LEVEL) {
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $currentUserId = mysqli_fetch_array($connect->query("SELECT Id FROM `user` WHERE `user`.`Token` = '$token'"))[0];

            if ($generalAccessLevel == ADMIN_ACCESS_LEVEL || $currentUserId == $userId) {
                $request = "UPDATE `user` SET `Status` = '$status' WHERE `user`.Id = $userId";
                $connect->query($request);
            } else {
                http_response_code(401);
                exit();
            }

        } else {
            http_response_code(401);
            exit();
        }
    } else {
        http_response_code(400);
    }
}

function SetUserRole($urlData, $formData)
{
    $headers = getallheaders();
    global $connect;

    $userId = $urlData[0];
    $roleId = $formData->RoleID;

    if (isset($headers["Authorization"])) $generalAccessLevel = GetAccessLevel($headers["Authorization"]);
    else $generalAccessLevel = UNAUTHORIZED_ACCESS_LEVEL;

    if ($generalAccessLevel == ADMIN_ACCESS_LEVEL) {
        $request = "UPDATE `user` SET `RoleId` = $roleId WHERE `user`.Id = $userId";
        $connect->query($request);
    } else {
        http_response_code(401);
        exit();
    }
}

function Delete($urlData)
{
    $headers = getallheaders();
    global $connect;

    $userId = $urlData[0];

    if (isset($headers["Authorization"])) $generalAccessLevel = GetAccessLevel($headers["Authorization"]);
    else $generalAccessLevel = UNAUTHORIZED_ACCESS_LEVEL;

    if ($generalAccessLevel == ADMIN_ACCESS_LEVEL) {
        $request = "DELETE FROM `user` WHERE `user`.`Id` = $userId";
        $connect->query($request);
        echo json_encode("success");
    } else {
        http_response_code(401);
        exit();
    }
}

?>