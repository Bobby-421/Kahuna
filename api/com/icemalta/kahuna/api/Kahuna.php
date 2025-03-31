<?php
require 'com/icemalta/kahuna/util/ApiUtil.php';
require 'com/icemalta/kahuna/model/Product.php';
require 'com/icemalta/kahuna/model/User.php';
require 'com/icemalta/kahuna/model/AccessToken.php';

use com\icemalta\kahuna\util\ApiUtil;
use com\icemalta\kahuna\model\Product;
use com\icemalta\kahuna\model\User;
use com\icemalta\kahuna\model\AccessToken;

cors();

$endPoints = [];
$requestData = [];
header("Content-Type: application/json; charset=UTF-8");

$BASE_URI = '/kahuna/api/';

function sendResponse(mixed $data = null, int $code = 200, mixed $error = null): void
{
    http_response_code($code);
    $response = [];
    if (!is_null($data)) {
        $response['data'] = $data;
    }
    if (!is_null($error)) {
        $response['error'] = $error;
    }
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

$requestMethod = $_SERVER['REQUEST_METHOD'];
switch ($requestMethod) {
    case 'GET':
        $requestData = $_GET;
        break;
    case 'POST':
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? $_SERVER["CONTENT_TYPE"] : '';
        if (strpos($contentType, "application/json") !== false) {
            $json = file_get_contents('php://input');
            $requestData = json_decode($json, true) ?? [];
        } else {
            $requestData = $_POST;
        }
        break;
    case 'PATCH':
        parse_str(file_get_contents('php://input'), $requestData);
        ApiUtil::parse_raw_http_request($requestData);
        $requestData = is_array($requestData) ? $requestData : [];
        break;
    case 'DELETE':
        break;
    default:
        sendResponse(null, 405, 'Method not allowed.');
}

$parsedURI = parse_url($_SERVER["REQUEST_URI"]);
$path = explode('/', str_replace($BASE_URI, "", $parsedURI["path"]));
$endPoint = $path[0];
$requestData['dataId'] = isset($path[1]) ? $path[1] : null;
if (empty($endPoint)) {
    $endPoint = "/";
}

if (isset($_SERVER["HTTP_X_API_KEY"])) {
    $requestData["user"] = $_SERVER["HTTP_X_API_USER"];
}
if (isset($_SERVER["HTTP_X_API_KEY"])) {
    $requestData["token"] = $_SERVER["HTTP_X_API_KEY"];
}

$endpoints["/"] = function (string $requestMethod, array $requestData): void {
    sendResponse('Welcome to Kahuna API!');
};

$endpoints["404"] = function (string $requestMethod, array $requestData): void {
    sendResponse(null, 404, "Endpoint " . $requestData["endPoint"] . " not found.");
};

$endpoints["product"] = function (string $requestMethod, array $requestData): void {
    if ($requestMethod === 'GET') {
        $products = Product::load();
        sendResponse($products);
    } elseif ($requestMethod === 'POST') {
        $serial = $requestData['serial'];
        $name = $requestData['name'];
        $warrantyLength = $requestData['warrantyLength'];
        $product = new Product($serial, $name, $warrantyLength);
        $product = Product::save($product);
        sendResponse($product, 201);
    } else {
        sendResponse(null, 405, 'Method not allowed.');
    }
};

$endpoints["user"] = function (string $requestMethod, array $requestData): void {
    if ($requestMethod === 'POST') {
        $username = $requestData['username'];
        $email = $requestData['email'];
        $password = $requestData['password'];
        $user = new User($username, $email, $password);
        $user = User::signUp($user);
        sendResponse($user, 201);
    } else {
        sendResponse(null, 405, 'Method not allowed.');
    }
};

$endpoints["login"] = function (string $requestMethod, array $requestData): void { 
    if ($requestMethod === 'POST') { 
        $username = $requestData['username']; 
        $password = $requestData['password']; 
        $user = new User($username, "", $password); 
        $authenticatedUser = User::authenticate($user); 
        if ($authenticatedUser) { 
            $token = new AccessToken($authenticatedUser->getId()); 
            $token = AccessToken::save($token); 
            sendResponse([
                'user' => $authenticatedUser->getId(), 
                'token' => $token->getToken(),
                'username' => $authenticatedUser->getUsername(),
                'accessLevel' => $authenticatedUser->getAccessLevel()
            ]); 
        } else { 
            sendResponse(null, 401, 'Invalid username or password.'); 
        }
    } else { 
        sendResponse(null, 405, 'Method not allowed.');
    }
};

$endpoints["token"] = function (string $requestMethod, array $requestData): void {
    if ($requestMethod === 'GET') {
        if (checkToken($requestData)) {
            sendResponse(['valid' => true, 'token' => $requestData['token']]);
        } else {
            sendResponse(['valid' => false, 'token' => $requestData['token']]);
        }
    } else {
        sendResponse(null, 405, 'Method not allowed.');
    }
};

function cors()
{
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
    }

    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PATCH, DELETE");

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
    }
}

try {
    if (isset($endpoints[$endPoint])) {
        $endpoints[$endPoint]($requestMethod, $requestData);
    } else {
        $endpoints["404"]($requestMethod, array("endPoint" => $endPoint));
    }
} catch (Exception $e) {
    sendResponse(null, 500, $e->getMessage());
} catch (Error $e) {
    sendResponse(null, 500, $e->getMessage());
}