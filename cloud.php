<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('BASEURL','https://ote-api.swhosting.com/v1/');
define('TOKEN','YOUR-BEARER-TOKEN');

// POST /v1/clouds
function createCloud($data)
{
    $endpoint = 'clouds';
    $method = 'POST';

    return makeRequest($endpoint, $method, $data);
}

// PATCH /v1/clouds/{cloud_id}
function updateCloud($cloudId, $data)
{
    $endpoint = 'clouds/'.$cloudId;
    $method = 'PATCH';

    return makeRequest($endpoint, $method, $data);
}

// Manage REST requests with the Bearer Token
function makeRequest($endpoint, $method = 'GET', $data = null)
{
    $url = BASEURL . $endpoint;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer '.TOKEN,
            'Content-Type: application/json',
        ));
    if ($method != 'HEAD' && $method != 'GET') {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 100);

    $responseAPI = curl_exec($ch);
    if (curl_errno($ch)) {
       die('Connection Error: ' . curl_errno($ch) . ' - ' . curl_error($ch));
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $response = json_decode($responseAPI);

    if ($http_code < 200 || $http_code >= 300) {
        $message = (isset($response->message)) ? $response->message : '';
        if (is_object($message)) {
            $message = implode(" ",(array)$message);
        }

        die('Response Error: ' . $http_code . ' - ' . $message);
    }

    return $response;
}

// Main
try {

    // Example 1: Purchase a new Cloud
    $data = [
        'vcores' => 2,
        'ram' => 4,
        'disk_size' => 40,
        'level' => 'one', // one: Cloud One or plus: Cloud Plus
        'so' => '19-W01', // Debian Buster (GET /v1/clouds/distributions)
        'zone' => 'GDC-1', // GDC-1 localitzation (GET /v1/clouds/zones),
    ];
    $response = createCloud($data);
    var_dump($response);

    // Example 2: Update number of cores
    $cloudId = 'CGXXX'; // Cloud ID (GET /v1/clouds)
    $data = [
        'vcores' => 4
    ];
    $response = updateCloud($cloudId, $data);
    var_dump($response);

} catch (Exception $e) {
    echo 'ERROR: '.$e->getMessage();
}