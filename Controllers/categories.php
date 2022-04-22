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
    global $connect;

    switch (sizeof($urlData)) {
        case 0:
            $request = "SELECT `category`.`Id`, `category`.`Title`, COUNT(`post`.`Id`) Posts FROM `category` LEFT JOIN `post` ON `category`.`Id` = `post`.`CategoryId` GROUP BY `category`.`Id`, `category`.`Title`";
            $categories = $connect->query($request);
            PrintJson($categories);
            break;
        case 1:
            if (is_numeric($urlData[0])) {
                $categoryId = $urlData[0];
                $request = "SELECT * FROM `category` WHERE `category`.`Id` = $categoryId";
                $categories = $connect->query($request);
                $posts = $connect->query("SELECT * FROM `post` WHERE `post`.`CategoryId` = $categoryId");

                $postss = array();
                if (!empty($posts)) {
                    while ($child_row = $posts->fetch_assoc()) {
                        array_push($postss, $child_row);
                    }
                }
                echo(json_encode($categories->fetch_assoc() + array("Posts" => $postss)));
            } else
                http_response_code(501);
            break;
        default:
            http_response_code(501);
            break;
    }
}

function Post($urlData, $formData)
{
    $headers = getallheaders();
    global $connect;

    if (isset($headers["Authorization"])) $generalAccessLevel = GetAccessLevel($headers["Authorization"]);
    else $generalAccessLevel = UNAUTHORIZED_ACCESS_LEVEL;

    if ($generalAccessLevel == ADMIN_ACCESS_LEVEL || $generalAccessLevel == MODERATOR_ACCESS_LEVEL) {
        switch (sizeof($urlData)) {
            case 0:
                $title = $formData->Title;
                $connect->query("INSERT INTO `category` (`Id`, `Title`) 
                               VALUES (NULL, '$title')");

                break;
            default:
                http_response_code(501);
                break;
        }

    } else {
        http_response_code(403);
        exit();
    }
}

function Patch($urlData, $formData)
{
    $headers = getallheaders();
    global $connect;
    if (isset($urlData[0]) && is_numeric($urlData[0])) {
        $categoryId = $urlData[0];
        $title = $formData->Title;

        if (isset($headers["Authorization"])) $generalAccessLevel = GetAccessLevel($headers["Authorization"]);
        else $generalAccessLevel = UNAUTHORIZED_ACCESS_LEVEL;

        if ($generalAccessLevel == ADMIN_ACCESS_LEVEL || $generalAccessLevel == MODERATOR_ACCESS_LEVEL) {

            $connect->query("UPDATE `category` SET `Title` = '$title' WHERE `category`.`Id` = '$categoryId'");

        } else {
            http_response_code(403);
            exit();
        }
    } else {
        http_response_code(501);
        exit();
    }

}

function Delete($urlData)
{
    $headers = getallheaders();
    global $connect;
    if (is_numeric($urlData[0])) {
        $categoryId = $urlData[0];

        if (isset($headers["Authorization"])) $generalAccessLevel = GetAccessLevel($headers["Authorization"]);
        else $generalAccessLevel = UNAUTHORIZED_ACCESS_LEVEL;

        if ($generalAccessLevel == ADMIN_ACCESS_LEVEL || $generalAccessLevel == MODERATOR_ACCESS_LEVEL) {
            $connect->query("DELETE FROM `category` WHERE Id = '$categoryId'");
            echo json_encode("success");
        } else {
            http_response_code(403);
            exit();
        }
    } else {
        http_response_code(501);
        exit();
    }
}
