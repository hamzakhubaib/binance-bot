<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "database_name";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error)
{
    die("Connection failed: " . $conn->connect_error);
}

$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method)
{
    case 'POST':
        place_order();
    break;
    default:
        // Invalid Request Method
        header("HTTP/1.0 405 Method Not Allowed");
    break;
}

function place_order()
{
    global $conn;

    $api_key = $_POST["api_key"];
    $secret_key = $_POST["secret_key"];
    $symbol = $_POST["symbol"];
    $side = $_POST["side"];
    $order_type = $_POST["order_type"];
    $price = $_POST["price"];
    $quantity = $_POST["quantity"];

    $url = "https://api.binance.com/api/v3/order";
    $header = ["X-MBX-APIKEY: $api_key"];
    $payload = ["symbol" => $symbol, "side" => $side, "type" => $order_type, "price" => $price, "quantity" => $quantity];

    $curl = curl_init();

    curl_setopt_array($curl, [CURLOPT_URL => $url, CURLOPT_POST => true, CURLOPT_HTTPHEADER => $header, CURLOPT_POSTFIELDS => http_build_query($payload) , CURLOPT_RETURNTRANSFER => true]);

    $response = curl_exec($curl);
    $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    if ($status_code == 200)
    {
        $cursor = $conn->prepare("INSERT INTO trades (symbol, side, price, quantity, date) VALUES (?, ?, ?, ?, now())");
        $cursor->bind_param("ssdd", $symbol, $side, $price, $quantity);
        $cursor->execute();
        $conn->commit();

        $result = ["status" => "success", "message" => "Order placed successfully."];
    }
    else
    {
        $result = ["status" => "error", "message" => "Error placing order. Status code: " . $status_code];
    }
    echo json_encode($result);
}
``
