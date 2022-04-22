<?php

require_once "vendor.php";
require_once "functions.php";

function route($method, $urlData, $formData)
{
    switch ($method) {
        case 'GET':
            Get($urlData);
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

function Get($urlData)
{
    switch (Count($urlData)) {
        case 0:
            PrintRoles();
            break;
        case 1:
            if (is_numeric($urlData[0])) {
                PrintRole($urlData[0]);
            } else {
                http_response_code(501);
            }
            break;
        default:
            http_response_code(501);
            break;
    }
}

function PrintRoles()
{
    global $connect;
    $roles = $connect->query("SELECT * FROM `role`");
    PrintJson($roles);
    exit();
}

function PrintRole($roleId)
{
    global $connect;
    $role = $connect->query("SELECT * FROM `role` WHERE `id` = $roleId");
    PrintJson($role);
    exit();
}


function Post($urlData, $formData)
{
    global $connect;

    $headers = getallheaders();
    if (isset($headers["Authorization"])) $generalAccessLevel = GetAccessLevel($headers["Authorization"]);
    else $generalAccessLevel = UNAUTHORIZED_ACCESS_LEVEL;

    if (Count($urlData) === 0) {
        if ($generalAccessLevel == ADMIN_ACCESS_LEVEL) {

            $name = $formData->Name;

            CheckRoleForExistence($name);

            $connect->query("INSERT INTO `role` (`Id`, `Name`) VALUES (NULL, '$name')");
        } else {
            http_response_code(403);
            echo json_encode([
                'status' => false,
                'message' => 'You are not admin'
            ]);
            exit();
        }
    }
}

function CheckRoleForExistence($name, $id = null)
{
    global $connect;

    $similarRole = $connect->query("SELECT * FROM `role` WHERE `role`.`Name` = '$name'");
    if ($similarRole->num_rows != 0) {
        if ($similarRole->num_rows == 1 && $id != NULL) {
            if ($similarRole->fetch_array()['Id'] == $id) {
                return;
            }
        }

        http_response_code(409);
        echo json_encode([
            'status' => false,
            'message' => 'Role with such Name already exists.'
        ]);
        exit();
    }
}

function Patch($urlData, $formData)
{
    global $connect;

    $headers = getallheaders();
    if (isset($headers["Authorization"])) $generalAccessLevel = GetAccessLevel($headers["Authorization"]);
    else $generalAccessLevel = UNAUTHORIZED_ACCESS_LEVEL;

    if (Count($urlData) === 1) {
        if (is_numeric($urlData[0])) {
            if ($generalAccessLevel == ADMIN_ACCESS_LEVEL) {

                $roleId = (int)$urlData[0];
                $role = $connect->query("SELECT * FROM `role` WHERE `role`.`Id` = '$roleId'");

                if ($role->num_rows != 1) {
                    http_response_code(404);
                    echo json_encode([
                        'status' => false,
                        'message' => 'No existing role'
                    ]);
                    exit();
                }

                $name = $formData->Name;

                CheckRoleForExistence($name);

                $connect->query("UPDATE `role` SET `Name` = '$name' WHERE `role`.`Id` = '$roleId'");
            } else {
                http_response_code(403);
                echo json_encode([
                    'status' => false,
                    'message' => 'You are not admin'
                ]);
                exit();
            }
        }
    }
}

function Delete($urlData)
{
    global $connect;

    $headers = getallheaders();
    if (isset($headers["Authorization"])) $generalAccessLevel = GetAccessLevel($headers["Authorization"]);
    else $generalAccessLevel = UNAUTHORIZED_ACCESS_LEVEL;

    if (Count($urlData) === 1) {
        if (is_numeric($urlData[0])) {
            if ($generalAccessLevel == ADMIN_ACCESS_LEVEL) {

                $roleId = (int)$urlData[0];

                $connect->query("DELETE FROM `role` WHERE `role`.`Id` = '$roleId'");
            } else {
                http_response_code(403);
                echo json_encode([
                    'status' => false,
                    'message' => 'You are not admin'
                ]);
                exit();
            }
        }
    }
}

?>