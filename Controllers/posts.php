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
    $headers = getallheaders();
    global $connect;

    switch (sizeof($urlData)) {
        case 0:
            $request = "SELECT * FROM `post`";
            $posts = $connect->query($request);
            PrintJson($posts);
            break;
        case 1:
            if (is_numeric($urlData[0])) {
                $postId = $urlData[0];
                $request = "SELECT * FROM `post` WHERE `post`.`Id` = $postId";
                $posts = $connect->query($request);
                $posts = $posts->fetch_assoc();
                $postOwnerId = $posts["OwnerId"];
                $postOwnerUsername = mysqli_fetch_array($connect->query("SELECT `user`.`Username` FROM `user` WHERE `user`.`Id` = '$postOwnerId'"))[0];
                $photos = $connect->query("SELECT * FROM `photo` WHERE `photo`.`PostId` = $postId");
                $comments = $connect->query("SELECT `comment`.`Text`, `comment`.`Created`, `user`.`Name` User FROM `comment` LEFT JOIN `user` ON `comment`.`OwnerId` = `user`.`Id` WHERE `comment`.`PostId` = $postId GROUP BY `comment`.`Text`, `comment`.`Created` ORDER BY `comment`.`Created` DESC");

                $photoss = array();
                if (!empty($photos)) {
                    while ($child_row = $photos->fetch_assoc()) {
                        array_push($photoss, $child_row);
                    }
                }
                $commentss = array();
                if (!empty($comments)) {
                    while ($child_row = $comments->fetch_assoc()) {
                        array_push($commentss, $child_row);
                    }
                }

                if (isset($headers["Authorization"])) $generalAccessLevel = GetAccessLevel($headers["Authorization"]);
                else $generalAccessLevel = UNAUTHORIZED_ACCESS_LEVEL;

                $canEdit = false;
                if ($generalAccessLevel == ADMIN_ACCESS_LEVEL || $generalAccessLevel == MODERATOR_ACCESS_LEVEL) {
                    $canEdit = true;
                } else if ($generalAccessLevel != UNAUTHORIZED_ACCESS_LEVEL) {
                    $token = str_replace("Bearer ", "", $headers["Authorization"]);
                    $userId = mysqli_fetch_array($connect->query("SELECT `user`.`Id` FROM `user` WHERE `user`.`Token` = '$token'"))[0];
                    if ($userId == $postOwnerId) {
                        $canEdit = true;
                    }
                }

                $posts["CanEdit"] = $canEdit;
                $posts["OwnerUsername"] = $postOwnerUsername;

                echo(json_encode($posts + array("Photos" => $photoss) + array("Comments" => $commentss)));
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

    if (isset($headers["Authorization"])) {
        $token = str_replace("Bearer ", "", $headers["Authorization"]);
        $userId = mysqli_fetch_array($connect->query("SELECT Id FROM `user` WHERE `user`.`Token` = '$token'"))[0];

        if (isset($userId)) {
            switch (sizeof($urlData)) {
                case 0:
                    $title = $formData->Title;
                    $text = $formData->Text;
                    $categoryId = $formData->CategoryId;

                    $connect->query("INSERT INTO `post` (`Title`, `Text`, `OwnerId`, `CategoryId`) VALUES ('$title', '$text', $userId, $categoryId)");

                    break;
                case 2:
                    switch ($urlData[1]) {
                        case "comment":
                            AddComment($formData, $userId, $urlData[0]);
                            break;
                        case "photo":
                            UploadImage($userId, $urlData[0]);
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
        } else {
            http_response_code(401);
            exit();
        }
    } else {
        http_response_code(401);
        exit();
    }
}

function Patch($urlData, $formData)
{
    $headers = getallheaders();
    global $connect;
    if (isset($urlData[0]) && is_numeric($urlData[0])) {
        $postId = $urlData[0];
        $text = $formData->Text;
        $title = $formData->Title;

        if (isset($headers["Authorization"])) $generalAccessLevel = GetAccessLevel($headers["Authorization"]);
        else $generalAccessLevel = UNAUTHORIZED_ACCESS_LEVEL;

        if ($generalAccessLevel == ADMIN_ACCESS_LEVEL || $generalAccessLevel == MODERATOR_ACCESS_LEVEL) {
            if (isset($title)) {
                $connect->query("UPDATE `post` SET `Title` = '$title' WHERE `post`.`Id` = '$postId'");
            }
            if (isset($text)) {
                $connect->query("UPDATE `post` SET `Text` = '$text' WHERE `post`.`Id` = '$postId'");
            }
        } else if ($generalAccessLevel != UNAUTHORIZED_ACCESS_LEVEL) {
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $userId = mysqli_fetch_array($connect->query("SELECT Id FROM `user` WHERE `user`.`Token` = '$token'"))[0];
            $postOwnerId = mysqli_fetch_array($connect->query("SELECT `post`.`OwnerId` FROM `post` WHERE `post`.`Id` = '$postId'"))[0];

            if ($userId == $postOwnerId) {
                if (isset($title)) {
                    $connect->query("UPDATE `post` SET `Title` = '$title' WHERE `post`.`Id` = '$postId'");
                }
                if (isset($text)) {
                    $connect->query("UPDATE `post` SET `Text` = '$text' WHERE `post`.`Id` = '$postId'");
                }
            } else {
                http_response_code(403);
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

function Delete($urlData)
{
    $headers = getallheaders();
    global $connect;
    if (is_numeric($urlData[0])) {
        $postId = $urlData[0];

        if (isset($headers["Authorization"])) $generalAccessLevel = GetAccessLevel($headers["Authorization"]);
        else $generalAccessLevel = UNAUTHORIZED_ACCESS_LEVEL;

        if ($generalAccessLevel == ADMIN_ACCESS_LEVEL || $generalAccessLevel == MODERATOR_ACCESS_LEVEL) {
            $connect->query("DELETE FROM `post` WHERE Id = '$postId'");
            echo("success");
        } else if ($generalAccessLevel != UNAUTHORIZED_ACCESS_LEVEL) {
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $userId = mysqli_fetch_array($connect->query("SELECT Id FROM `user` WHERE `user`.`Token` = '$token'"))[0];
            $postOwnerId = mysqli_fetch_array($connect->query("SELECT `post`.`OwnerId` FROM `post` WHERE `post`.`Id` = '$postId'"))[0];

            if ($userId == $postOwnerId) {
                $connect->query("DELETE FROM `post` WHERE Id = '$postId'");
                echo json_encode("success");
            } else {
                http_response_code(403);
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

function AddPhoto($urlData, $formData)
{
    $headers = getallheaders();
    global $connect;
    if (isset($urlData[0]) && is_numeric($urlData[0])) {
        $postId = $urlData[0];
        $photoId = $formData->PhotoId;

        $generalAccessLevel = GetAccessLevel($headers["Authorization"]);

        if ($generalAccessLevel == ADMIN_ACCESS_LEVEL || $generalAccessLevel == MODERATOR_ACCESS_LEVEL) {
            $connect->query("UPDATE `photo` SET `PostId` = '$photoId' WHERE `photo`.`Id` = '$photoId'");
        } else if ($generalAccessLevel != UNAUTHORIZED_ACCESS_LEVEL) {
            $token = str_replace("Bearer ", "", $headers["Authorization"]);
            $userId = mysqli_fetch_array($connect->query("SELECT Id FROM `user` WHERE `user`.`Token` = '$token'"))[0];
            $postOwnerId = mysqli_fetch_array($connect->query("SELECT `post`.`OwnerId` FROM `post` WHERE `post`.`Id` = '$postId'"))[0];
            $photoOwnerId = mysqli_fetch_array($connect->query("SELECT `photo`.`OwnerId` FROM `photo` WHERE `photo`.`Id` = '$photoId'"))[0];

            if ($userId == $postOwnerId && $userId == $photoOwnerId) {
                $connect->query("UPDATE `photo` SET `PostId` = '$photoId' WHERE `photo`.`Id` = '$photoId'");
            } else {
                http_response_code(403);
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

function AddComment($formData, $userId, $postId)
{
    global $connect;
    $comment = $formData->Comment;
    $connect->query("INSERT INTO `comment` (`PostId`, `Text`, `OwnerId`) VALUES ($postId, '$comment', $userId)");
}
