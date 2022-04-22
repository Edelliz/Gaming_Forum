<?php
header('Content-type: json/application');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization');

require_once "functions.php";
require_once "vendor.php";
require_once "constants.php";

function getDataFromRequest($method)
{
    if ($method === 'GET') return $_GET;
    if ($method === 'POST' && !empty($_POST)) return $_POST;

    $incomingData = file_get_contents('php://input');
    $decodedJSON = json_decode($incomingData);
    if ($decodedJSON) {
        $data = $decodedJSON;
    } else {
        $data = array();
        $exploded = explode('&', file_get_contents('php://input'));
        foreach ($exploded as $pair) {
            $item = explode('=', $pair);
            if (count($item) == 2) {
                $data[urldecode($item[0])] = urldecode($item[1]);
            }
        }
    }
    return $data;
}

global $connect;
$adminRoleName = ADMIN_ROLE_NAME;
$moderatorRoleName = MODERATOR_ROLE_NAME;
$userRoleName = USER_ROLE_NAME;

$adminRole = $connect->query("SELECT * FROM `role` WHERE `role`.`Name` = '$adminRoleName'");
if ($adminRole->num_rows == 0) {
    $connect->query("INSERT INTO `role` (`Id`, `Name`) VALUES (NULL, '$adminRoleName')");
} // Creating Admin role if it doesn't exist

$moderatorRole = $connect->query("SELECT * FROM `role` WHERE `role`.`Name` = '$moderatorRoleName'");
if ($moderatorRole->num_rows == 0) {
    $connect->query("INSERT INTO `role` (`Id`, `Name`) VALUES (NULL, '$moderatorRoleName')");
} // Creating Moderator role if it doesn't exist

$userRole = $connect->query("SELECT * FROM `role` WHERE `role`.`Name` = '$userRoleName'");
if ($userRole->num_rows == 0) {
    $connect->query("INSERT INTO `role` (`Id`, `Name`) VALUES (NULL, '$userRoleName')");
} // Creating User role if it doesn't exist


$adminRoleId = mysqli_fetch_array($connect->query("SELECT `Id` FROM `role` WHERE `role`.`Name` = '$adminRoleName'"));
$admin = $connect->query("SELECT * FROM `user` WHERE `user`.`RoleId` = '$adminRoleId[0]'");

if ($admin->num_rows == 0) {
    $token = generateToken();
    $connect->query("INSERT INTO `user` (`Id`, `Name`, `Surname`, `Password`, `Birthday`, `Avatar`, `Status`, `Username`, `Token`, `RoleId`) 
                               VALUES (NULL, 'Kek', 'Admin', '123456789', NULL, NULL, 'Want to die', 'Admin', '$token', '$adminRoleId[0]')");
}

$method = $_SERVER['REQUEST_METHOD'];
if ($method == "OPTIONS") { //обработка для первичного CORS-запроса
    http_response_code(200);
    exit();
}
$formData = getDataFromRequest($method);

if (isset($_GET['q'])) { // if local url isn't empty
    $localUrl = $_GET['q']; // Set a local url form $_GET
    $localUrl = rtrim($localUrl, '/'); // Delete last / , if exists
    $localUrlParts = explode('/', $localUrl); // Get all parts of local path
    $controller = $localUrlParts[0]; // Get controller from a local path
    $controllerData = array_slice($localUrlParts, 1); // Get a local path exclude a controller
} else {
    $localUrl = '';
}

if (isset($localUrlParts)) { // if local url isn't empty
    if (file_exists('Controllers/' . $controller . '.php')) {
        require_once 'Controllers/' . $controller . '.php';
        route($method, $controllerData, $formData);
    } else {
        http_response_code(404);
    }
} else {
    http_response_code(400);
}

