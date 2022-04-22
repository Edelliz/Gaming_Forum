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
            $request = "SELECT * FROM `announcement` ORDER BY `announcement`.`Created` DESC";
            $announcements = $connect->query($request);
            PrintJson($announcements);
            break;
        case 1:
            if (is_numeric($urlData[0])) {
                $announcementId = $urlData[0];
                $request = "SELECT * FROM `announcement` WHERE `announcement`.`Id` = $announcementId";
                $announcements = $connect->query($request);
                PrintJson($announcements);
            } else {
                http_response_code(501);
            }
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
                $text = $formData->Text;
                $connect->query("INSERT INTO `announcement` (`Title`, `Text`) 
                               VALUES ('$title', '$text')");

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
        $announcementId = $urlData[0];
        $title = $formData->Title;
        $text = $formData->Text;

        if (isset($headers["Authorization"])) $generalAccessLevel = GetAccessLevel($headers["Authorization"]);
        else $generalAccessLevel = UNAUTHORIZED_ACCESS_LEVEL;

        if ($generalAccessLevel == ADMIN_ACCESS_LEVEL || $generalAccessLevel == MODERATOR_ACCESS_LEVEL) {
            if (isset($title)) {
                $connect->query("UPDATE `announcement` SET `Title` = '$title' WHERE `announcement`.`Id` = '$announcementId'");
            }
            if (isset($text)) {
                $connect->query("UPDATE `announcement` SET `Text` = '$text' WHERE `announcement`.`Id` = '$announcementId'");
            }
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
        $announcementId = $urlData[0];

        if (isset($headers["Authorization"])) $generalAccessLevel = GetAccessLevel($headers["Authorization"]);
        else $generalAccessLevel = UNAUTHORIZED_ACCESS_LEVEL;

        if ($generalAccessLevel == ADMIN_ACCESS_LEVEL || $generalAccessLevel == MODERATOR_ACCESS_LEVEL) {
            $connect->query("DELETE FROM `announcement` WHERE Id = '$announcementId'");
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
